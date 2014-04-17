<?php
/**
 * PhateRedisクラス
 *
 * 設定ファイルより接続済みRedisクラス取得クラス
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateRedis
{
    private static $_config;
    private static $_instancePool;
    private static $_realInstancePool;
    
    /**
     * 設定ファイルよりredisの設定を取得
     * 
     * @access private
     * @param void
     * @return void
     */
    private static function setConfig()
    {
        $sysConf = PhateCore::getConfigure();
        if (!isset($sysConf['REDIS']['load_yaml_file'])) {
            throw new PhateCommonException('no redis configure');
        }
        $filename = PHATE_CONFIG_DIR . $sysConf['REDIS']['load_yaml_file'];
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
            $redis = new Redis();
            $redis->connect($host, $port);
            self::$_realInstancePool[$host][$port] = $redis;
        }
        return self::$_realInstancePool[$host][$port];
    }
    
    
    /**
     * 接続名のインスタンスを返す
     * 
     * @access public
     * @param string $namespace
     * @return Redis
     */
    public static function getInstance($namespace)
    {
        if (!isset(self::$_instancePool[$namespace])) {
            if (!isset(self::$_config)) {
                self::setConfig();
            }
            if (!isset(self::$_config[$namespace])) {
                throw new PhateRedisConnectFailException('cant resolve namespace on redis');
            }
            $instance = null;
            // レプリケーション対応
            foreach (self::$_config[$namespace] as $serverConfig) {
                try {
                    $instance = self::getRealInstance(self::$_config[$namespace]['host'], self::$_config[$namespace]['port']);
                    break;
                } catch(Exception $e) {
                    continue;
                }
            }
            // 全部に接続確立できてない
            if (is_null($instance)) {
                throw new PhateRedisConnectFailException();
            }
            self::$_instancePool[$namespace] = $instance;
        }
        return self::$_instancePool[$namespace];
    }
    
    /**
     * 接続中のインスタンスを全て明示的に切断する
     * 
     * @return void
     */
    public static function disconnect()
    {
        if (!self::$_realInstancePool || !is_array(self::$_realInstancePool)) {
            return;
        }
        foreach (self::$_realInstancePool as $instance) {
            $instance->close();
        }
        unset(self::$_realInstancePool);
        if (!self::$_instancePool) {
            return;
        }
        unset(self::$_instancePool);
        return;
    }
    
}
/**
 * PhateRedisConnectFailException
 *
 * Redis接続失敗例外
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateRedisConnectFailException extends PhateCommonException
{
    
}
