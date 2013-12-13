<?php
/**
 * PhateStringクラス
 *
 * 文字列処理共通メソッド格納クラス
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateString
{
    /**
     * 単語にNGWORDが含まれているかのチェック
     * 
     * @access public
     * @param string 
     * @return boolean 
     */
    /*
     * 等、文字列処理系のメソッドを追加していくような気がします
     * 
     *     public static function hasNGWord($string)
    {
        // NGワードの辞書を取得
        $memcacheKey = __CLASS__ . ':' . __FUNCTION__;
        $ngWords = PhateMemcached::get($memcacheKey,'api');
        if (!($ngWords = false)) {
            $tmp = file_get_contents(PHATE_SOURCE_DIR . 'NGWORD.txt');
            $ngWords = explode("\n", $tmp);
            foreach ($ngWords as &$v) {
                $v = trim($v);
            }
            PhateMemcached::set($memcacheKey, $ngWords, 0, 'api');
        }
        
        // 汎用デリミタを加工して統一化
        $string = str_replace("\n", ' ', $string);
        $string = str_replace('　', ' ', $string);
        $str = explode(' ', $string);
        foreach ($str as $v) {
            if (in_array(trim($v), $ngWords)) {
                return true;
            }
        }
        
        return false;
    }
     * 
     */
    
}