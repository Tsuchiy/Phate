<?php
/**
 * PhateCommonクラス
 *
 * 基礎的な（フレームワークが動作するのに必要な）共通関数を配置するのに使っています。
 * 詳細な機能の共通関数はまた新しいクラスを用意します。
 *
 * @package PhateFramework
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateCommon
{
    const CONTEXT_ROOT_REPLACE = '%%CONTEXT_ROOT%%';
    /**
     * サーバ環境に合わせたyamlの読み込みと配列化 with キャッシュ
     * 
     * @access public
     * @param string $filename
     * @return array
     */
    public static function parseConfigYaml($filename)
    {
        // キャッシュ試行
        $cacheFileName = PHATE_CACHE_DIR . basename($filename) . '_' . SERVER_ENV . '.cache';
        if (file_exists($cacheFileName) && !PhateCore::isDebug()) {
            return msgpack_unserialize(file_get_contents($cacheFileName));
        }
        // ファイル読み込み
        if (!file_exists($filename)) {
            throw new PhateCommonException('yaml:file not find:' . $filename);
        }
        $arrayTemp = yaml_parse(str_replace(self::CONTEXT_ROOT_REPLACE, substr(PHATE_ROOT_DIR, 0, -1), file_get_contents($filename)));
        if (!array_key_exists('all', $arrayTemp)) {
            throw new PhateCommonException('yaml:not find cardinary "all" :' . $filename);
        }
        $rtn = $arrayTemp['all'];
        if (isset($arrayTemp[SERVER_ENV])) {
            $rtn = array_merge($rtn, $arrayTemp[SERVER_ENV]);
        }
        // キャッシュ保存
        file_put_contents($cacheFileName, msgpack_serialize($rtn), LOCK_EX);
        if (substr(sprintf('%o', fileperms($cacheFileName)), -4) !=='0777') {
            chmod($cacheFileName, 0777);
        }
        
        return $rtn;
    }
    
    /**
     * 特定のパスから配下のファイルを再帰的に取得
     * 
     * @access public
     * @param string $path
     * @return array 
     */
    public static function getFileNameRecursive($path)
    {
        if (is_file($path)) {
            return array($path);
        }
        $rtn = array();
        if (is_dir($path)) {
            $dh = opendir($path);
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_file($path . DIRECTORY_SEPARATOR . $file)) {
                    $rtn[] = $path . DIRECTORY_SEPARATOR . $file;
                }
                if (is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                    $tmp = self::getFileNameRecursive($path . DIRECTORY_SEPARATOR . $file);
                    $rtn = array_merge($rtn, $tmp);
                }
            }
            closedir($dh);
        }
        return $rtn;
    }
    
    /**
     * snake等の文字列をpascalに置換する
     * 
     * @access public
     * @param string 
     * @return string 
     */
    public static function pascalizeString($string)
    {
        $string = strtolower($string);
        $string = str_replace('_', ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);
        return $string;
    }

    /**
     * snake等の文字列をcamelに置換する
     * 
     * @access public
     * @param string 
     * @return string 
     */
    public static function camelizeString($string)
    {
        return lcfirst(self::pascalizeString($string));
    }

    /**
     * pascal等の文字列をsnake_caseに置換する
     * 
     * @access public
     * @param string 
     * @return string 
     */
    public static function toSnakeCaseString($string)
    {
        $string = lcfirst($string);
        $string = preg_replace('/([A-Z])/', '_$1', $string);
        return strtolower($string);
    }
    
}