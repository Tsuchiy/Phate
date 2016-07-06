<?php
namespace Phate;
/**
 * Commonクラス
 *
 * 基礎的な（フレームワークが動作するのに必要な）共通関数を配置するのに使っています。
 * 詳細な機能の共通関数はまた新しいクラスを用意します。
 *
 * @package PhateFramework
 * @access  public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class Common
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
        $apcCacheName = '';
        // キャッシュ試行
        if (function_exists('apc_store') && !Core::isDebug()) {
            $apcCacheName = basename($filename) . '_' . SERVER_ENV . '.cache';
            if ($rtn = apc_fetch($apcCacheName)) {
                return $rtn;
            }
        }
        $cacheFileName = PHATE_CACHE_DIR . basename($filename) . '_' . SERVER_ENV . '.cache';
        if (file_exists($cacheFileName) && !Core::isDebug()) {
            $rtn = self::unserialize(file_get_contents($cacheFileName));
            if ($apcCacheName) {
                apc_store($apcCacheName, $rtn, 0);
            }
            return $rtn;
        }
        // ファイル読み込み
        if (!file_exists($filename)) {
            throw new CommonException('yaml:file not find:' . $filename);
        }
        $arrayTemp = yaml_parse(str_replace(self::CONTEXT_ROOT_REPLACE, substr(PHATE_ROOT_DIR, 0, -1), file_get_contents($filename)));
        if (!array_key_exists('all', $arrayTemp)) {
            throw new CommonException('yaml:not find cardinary "all" :' . $filename);
        }
        $rtn = is_array($arrayTemp['all']) ? $arrayTemp['all'] : array();
        if (isset($arrayTemp[SERVER_ENV])) {
            $rtn = array_merge($rtn, $arrayTemp[SERVER_ENV]);
        }
        // キャッシュ保存
        file_put_contents($cacheFileName, self::serialize($rtn), LOCK_EX);
        if (substr(sprintf('%o', fileperms($cacheFileName)), -4) !=='0777') {
            chmod($cacheFileName, 0777);
        }
        if (function_exists('apc_store') && !Core::isDebug()) {
            apc_store($apcCacheName, $rtn, 0);
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
            return [$path];
        }
        $rtn = [];
        if (is_dir($path)) {
            $dh = opendir($path);
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $rtn = array_merge($rtn, self::getFileNameRecursive($path . DIRECTORY_SEPARATOR . $file));
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

    /**
     * アルゴリズムに優先を付けてシリアライズする
     * @param mixed $mixed
     * @return string
     */
    public static function serialize($mixed)
    {
        if (function_exists('msgpack_serialize')) {
            return msgpack_serialize($mixed);
        } elseif (function_exists('igbinary_serialize')) {
            return igbinary_serialize($mixed);
        } elseif (function_exists('fb_serialize')) {
            return fb_serialize($mixed);
        }
        
        return serialize($mixed);
    }
    
    /**
     * アルゴリズムに優先を付けてアンシリアライズする
     * @param string $string
     * @return mixed
     */
    public static function unserialize($string)
    {
        if (function_exists('msgpack_unserialize')) {
            return msgpack_unserialize($string);
        } elseif (function_exists('igbinary_unserialize')) {
            return igbinary_unserialize($string);
        } elseif (function_exists('fb_unserialize')) {
            $rtn = null;
            fb_unserialize($string, $rtn);
            return $rtn;
        }
        return unserialize($string);
    }
}
