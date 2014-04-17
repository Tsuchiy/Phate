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
    private static $_instancePool4Set;
    private static $_getDisable = false;
    
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
            $m->setOption(Memcached::OPT_CONNECT_TIMEOUT, 1000);
            $m->setOption(Memcached::OPT_SEND_TIMEOUT, 1000);
            $m->setOption(Memcached::OPT_RECV_TIMEOUT, 1000);
            $m->addServer($host, $port);
            // 疎通確認
            $m->getVersion();
            if ($m->getResultCode() !== Memcached::RES_SUCCESS) {
                throw new PhateMemcachedConnectFailException();
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
                throw new PhateMemcachedConnectFailException('cant resolve namespace on memcache');
            }
            $instance = null;
            $instance4Set = array();
            // レプリケーション対応
            foreach (self::$_config[$namespace]['server'] as $serverConfig) {
                try {
                    $tmpInstance = self::getRealInstance($serverConfig['host'], $serverConfig['port']);
                    if (is_null($instance)) {
                        $instance = $tmpInstance;
                    }
                    $instance4Set[] = $tmpInstance;
                } catch (PhateMemcachedConnectFailException $e) {
                    continue;
                }
            }
            // 全部に接続確立できてない
            if (is_null($instance)) {
                throw new PhateMemcachedConnectFailException();
            }
            self::$_instancePool[$namespace] = $instance;
            self::$_instancePool4Set[$namespace] = $instance4Set;
        }
        return self::$_instancePool[$namespace];
    }
    
    /**
     * 接続名の全てのインスタンスを返す
     * 
     * @param string $namespace
     * @return array
     */
    private static function getInstance4Set($namespace)
    {
        if (!isset(self::$_instancePool4Set[$namespace])) {
            self::getInstance($namespace);
        }
        return self::$_instancePool4Set[$namespace];
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
     * バックアップサーバ対策？のためにキーを全サーバに保存しに行く
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
        $memcachedList = self::getInstance4Set($namespace);
        if (is_null($expiration)) {
            $expiration = self::$_config[$namespace]['default_expire'];
        }
        $rtn = true;
        foreach ($memcachedList as $memcached) {
            if (($memcached->set(self::$_config[$namespace]['default_prefix'] . $key, $value, $expiration)) === false) {
                $rtn = false;
            }
        }
        return $rtn;
    }
    
    /**
     * memcacheに値を複数格納
     * バックアップサーバ対策？のためにキーを全サーバに保存しに行く
     * 
     * @param array $items
     * @param integer $expiration
     * @param string $namespace
     * @return boolean
     */
    public static function setMulti(array $items, $expiration = NULL, $namespace = 'default')
    {
        if (!isset(self::$_config)) {
            self::setConfig();
        }
        if (is_null($expiration)) {
            $expiration = self::$_config[$namespace]['default_expire'];
        }
        $realItems = array();
        foreach ($items as $key => $value) {
            $realItems[self::$_config[$namespace]['default_prefix'] . $key] = $value;
        }
        $memcachedList = self::getInstance4Set($namespace);
        $rtn = true;
        foreach ($memcachedList as $memcached) {
            if (($memcached->setMulti($realItems, $expiration)) === false) {
                $rtn = false;
            }
        }
        return $rtn;
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
        if (self::$_getDisable) {
            return false;
        }
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
        if (self::$_getDisable) {
            $rtn = array();
            foreach ($keys as $key) {
                $rtn[$key] = false;
            }
            return $rtn;
        }
        if (!isset(self::$_config)) {
            self::setConfig();
        }
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
     * バックアップ対策？のためにキーを全サーバに削除しに行く
     * 
     * @access public
     * @param string $key
     * @param string $namespace
     * @return boolean
     */
    public static function delete($key, $namespace = 'default')
    {
        $memcachedList = self::getInstance4Set($namespace);
        $rtn = true;
        foreach ($memcachedList as $memcached) {
            if (($memcached->delete(self::$_config[$namespace]['default_prefix'] . $key)) === false) {
                if ($memcached->getResultCode() != Memcached::RES_NOTFOUND) {
                    $rtn = false;
                }
            }
        }
        return $rtn;
    }
    /**
     * memcacheより値を配列で消去
     * バックアップ対策？のためにキーを全サーバに削除しに行く
     * 
     * 
     * @access public
     * @param array $keys
     * @param string $namespace
     * @return boolean
     */
    public static function deleteMulti(array $keys, $namespace = 'default')
    {
        if (!isset(self::$_config)) {
            self::setConfig();
        }
        foreach ($keys as &$key) {
            $key = self::$_config[$namespace]['default_prefix'] . $key;
        }
        $memcachedList = self::getInstance4Set($namespace);
        $rtn = true;
        foreach ($memcachedList as $memcached) {
            if (($memcached->deleteMulti($keys)) === false) {
                if ($memcached->getResultCode() != Memcached::RES_NOTFOUND) {
                    $rtn = false;
                }
            }
        }
        return $rtn;
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
     * バックアップ対策？のために全サーバに更新処理をかけているので、
     * 必ずしも意図した値は保証されないかもしれない
     * 
     * @access public
     * @param string $namespace
     * @return integer
     */
    public static function getResultCode($namespace = 'default')
    {
        return self::getInstance($namespace)->getResultCode();
    }
    
    /**
     * memcache機能の無効化を行う
     * debug時用
     * 
     * @access public
     * @param string $namespace
     * @return integer
     */
    public static function setGetDisable($disable = true)
    {
        self::$_getDisable = $disable;
    }
}

/**
 * memcacheサーバ接続エラー
 */

class PhateMemcachedConnectFailException extends PhateCommonException
{
    
}
