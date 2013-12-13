<?php
/**
 * PhateMemcachedクラス
 *
 * 設定ファイル読んで、Memcacheに接続したmemcachedのインスタンスを操作するクラス
 * 名前空間毎にprefixの処理なんかもする
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateMemcached {
    
    private static $_config;
    private static $_realInstancePool;
    private static $_instancePool;
    
    /**
     * 設定ファイルよりmemcacheの設定を取得
     * 
     * @access private
     * @param void
     * @return void
     */
    private static function setConfig()
    {
        $sysConf = PhateCore::getConfigure();
        if (!isset($sysConf['MEMCACHE']['load_yaml_file'])) {
            throw new PhateCommonException('no memcache configure');
        }
        $filename = PHATE_CONFIG_DIR . $sysConf['MEMCACHE']['load_yaml_file'];
        self::$_config = PhateCommon::parseConfigYaml($filename);
    }
    
    /**
     * 接続先別のインスタンスを生成
     * 
     * @access private
     * @param string $host
     * @param integer $port
     * @return Memcached
     */
    private static function getRealInstance($host, $port)
    {
        if (!isset(self::$_realInstancePool[$host][$port])) {
            $m = new Memcached;
            $m->addServer($host, $port);
            // 疎通確認
            $m->getVersion();
            if ($m->getResultCode() !== Memcached::RES_SUCCESS) {
                throw new PhateMemcachedConnectFailException();
                return null;
            }
            self::$_realInstancePool[$host][$port] = $m;
        }
        return self::$_realInstancePool[$host][$port];
    }
    
    /**
     * 接続名のインスタンスを返す
     * 
     * @access private
     * @param string $namespace
     * @return Memcached
     */
    private static function getInstance($namespace)
    {
        if (!isset(self::$_instancePool[$namespace])) {
            if (!isset(self::$_config)) {
                self::setConfig();
            }
            if (!isset(self::$_config[$namespace])) {
                throw new PhateMemcachedConnectFailException('cant resolv namespace');
            }
            $instance = null;
            // レプリケーション対応
            foreach (self::$_config[$namespace]['server'] as $serverConfig) {
                try {
                    $instance = self::getRealInstance($serverConfig['host'], $serverConfig['port']);
                    break;
                } catch (PhateMemcachedConnectFailException $e) {
                    continue;
                }
            }
            // 全部に接続確立できてない
            if (is_null($instance)) {
                throw new PhateMemcachedConnectFailException();
            }
            self::$_instancePool[$namespace] = $instance;
        }
        return self::$_instancePool[$namespace];
    }
    
    /**
     * インスタンスプールにあるmemcachedオブジェクトを全て明示的に切断する
     * 
     * @access public
     * @param void
     * @return void
     */
    public static function disconnect()
    {
        if (!is_array(self::$_realInstancePool)) {
            return;
        }
        // 存在するインスタンスを全部切断する
        foreach (self::$_realInstancePool as $host => $tmp) {
            foreach ($tmp as $port => $v) {
                self::$_realInstancePool[$host][$port]->quit();
                unset(self::$_realInstancePool[$host][$port]);
            }
        }
    }

    /**
     * memcacheに値を格納
     * 
     * @access public
     * @param string $key
     * @param mixed $value
     * @param integer $expire
     * @param string $namespace
     * @return boolean
     */
    public static function set($key, $value, $expiration = NULL, $namespace = 'default')
    {
        $memcached = self::getInstance($namespace);
        if (is_null($expiration)) {
            $expiration = self::$_config[$namespace]['default_expire'];
        }
        return $memcached->set(self::$_config[$namespace]['default_prefix'] . $key, $value, $expiration);
    }
    
    /**
     * memcacheに値を複数格納
     * @param array $items
     * @param integer $expiration
     * @param string $namespace
     * @return boolean
     */
    public static function setMulti(array $items, $expiration = NULL, $namespace = 'default')
    {
        $memcached = self::getInstance($namespace);
        if (is_null($expiration)) {
            $expiration = self::$_config[$namespace]['default_expire'];
        }
        $realItems = array();
        foreach ($items as $key => $value) {
            $realItems[self::$_config[$namespace]['default_prefix'] . $key] = $value;
        }
        return $memcached->setMulti($realItems, $expiration);
    }
    /**
     * memcacheより値を取得
     * 
     * @access public
     * @param string $key
     * @param string $namespace
     * @return mixed/false
     */
    public static function get($key, $namespace = 'default')
    {
        return self::getInstance($namespace)->get(self::$_config[$namespace]['default_prefix'] . $key);
    }
    /**
     * memcacheより値を配列で取得
     * 
     * @access public
     * @param string $key
     * @param string $namespace
     * @return mixed/false
     */
    public static function getMulti(array $keys, $namespace = 'default')
    {
        foreach ($keys as &$key) {
            $key = self::$_config[$namespace]['default_prefix'] . $key;
        }
        if (!($res = self::getInstance($namespace)->getMulti($keys))) {
            return $res;
        }
        $rtn = array();
        $pattern = '/^' . preg_quote(self::$_config[$namespace]['default_prefix']) . '(.*)$/';
        foreach ($res as $key => $value) {
            $newKey = preg_replace($pattern, '$1', $key);
            $rtn[$newKey] = $value;
        }
        return $rtn;
    }
    /**
     * memcacheより値を消去
     * 
     * @access public
     * @param string $key
     * @param string $namespace
     * @return boolean
     */
    public static function delete($key, $namespace = 'default')
    {
        if (self::get($key, $namespace) === false) {
            return true;
        }
        return self::getInstance($namespace)->delete(self::$_config[$namespace]['default_prefix'] . $key);
    }
    /**
     * memcacheより値を配列で消去
     * 
     * @access public
     * @param array $keys
     * @param string $namespace
     * @return boolean
     */
    public static function deleteMulti(array $keys, $namespace = 'default')
    {
        foreach ($keys as &$key) {
            $key = self::$_config[$namespace]['default_prefix'] . $key;
        }
        return self::getInstance($namespace)->deleteMulti($keys);
    }
    
    /**
     * memcacheより全てのキー一覧を取得（ただし保証はされない）
     * 
     * @access public
     * @param string $namespace
     * @return array
     */
    public static function getAllKeys($namespace = 'default')
    {
        $realKeys = self::getInstance($namespace)->getAllKeys();
        $rtn = array();
        $pattern = '/^' . preg_quote(self::$_config[$namespace]['default_prefix']) . '(.*)$/';
        foreach ($realKeys as $realKey) {
            if (preg_match($pattern, $realKey)) {
                $rtn[] = preg_replace($pattern, '$1', $realKey);
            }
        }
        return $rtn;
    }
    /**
     * 直前のmemcached結果コードを取得
     * 
     * @access public
     * @param string $namespace
     * @return integer
     */
    public static function getResultCode($namespace = 'default')
    {
        return self::getInstance($namespace)->getResultCode();
    }
    
}

/**
 * memcacheサーバ接続エラー
 */

class PhateMemcachedConnectFailException extends PhateCommonException
{
    
}
