<?php
namespace Phate;

/**
 * Stringクラス
 *
 * 文字列処理共通メソッド格納クラス
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class String
{
    /**
     * trimの拡張関数
     * 
     * @param string $str
     * @return string
     */
    public static function mb_trim($str)
    {
        if (preg_match('/^(\s*)$/us', $str)) {
            return '';
        }
        return preg_replace('/^(.*[^\s])(\s*)$/us', '$1', preg_replace('/^(\s*)([^\s].*)$/us', '$2', $str));
    }

    /**
     * 再帰的に配列内をmb_convert_encodingする
     * 
     * @param array|string $arg
     * @param string $to_encoding
     * @param string $from_encoding
     * @return array|string
     */
    public static function mb_convert_encoding_array($arg, $to_encoding, $from_encoding = null)
    {
        if (!is_array($arg)) {
            return mb_convert_encoding($arg, $to_encoding, $from_encoding);
        }
        
        foreach ($arg as &$v) {
            $v = self::mb_convert_encoding_array($v, $to_encoding, $from_encoding);
        }
        return $arg;
    }
    
    /**
     *  UTF-8文字列をUnicodeエスケープする。ただし英数字と記号はエスケープしない。
     * 
     * @param string $str
     * @return string
     */
    public static function unicode_decode($str)
    {
        return preg_replace_callback("/((?:[^\x09\x0A\x0D\x20-\x7E]{3})+)/", 
            function ($matches) {
                $char = mb_convert_encoding($matches[1], "UTF-16", "UTF-8");
                $escaped = "";
                for ($i = 0, $l = strlen($char); $i < $l; $i += 2) {
                    $escaped .=  "\u" . sprintf("%02x%02x", ord($char[$i]), ord($char[$i+1]));
                }
                return $escaped;
            }
            , $str);
    }

    /**
     *  Unicodeエスケープされた文字列をUTF-8文字列に戻す
     * 
     * @param string $str
     * @return string
     */
    public static function unicode_encode($str)
    {
        return preg_replace_callback("/\\\\u([0-9a-zA-Z]{4})/", 
            function ($matches) {
                return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UTF-16");
            }
            , $str);
    }
    
    /**
     * Unicode外字を持つかを判定
     */
    public static function hasUnicodeEmoji($str)
    {
        $len = mb_strlen($str);
        for ($i=0; $i < $len; ++$i) {
            $chr = mb_substr($str, $i, 1);
            if (preg_match('/^(\xEE[\x80-\xBF])|(\xEF[\x80-\xA3])|(\xF3[\xB0-\xBF])|(\xF4[\x80-\x8F])/', $chr)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 4byte以上の文字を持つかを判定
     */
    public static function has4byteMoreCharacter($str)
    {
        $len = mb_strlen($str);
        for ($i=0; $i < $len; ++$i) {
            if(strlen(mb_substr($str, $i, 1)) > 3) {
                return true;
            }
        }
        return false;
    }
    
}
