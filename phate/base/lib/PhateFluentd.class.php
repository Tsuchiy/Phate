<?php
// Fluentをインクルード
include_once PHATE_LIB_VENDOR_DIR . 'Fluent/Autoloader.php';
use Fluent\Logger;

/**
 * PhateFluentdクラス
 *
 * https://github.com/fluent/fluent-logger-php
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateFluentd
{
    private static $_fluent;
    
    /**
     * ロガーの初期化
     * 
     * @access public
     * @param void
     * @return void
     */
    private static function init()
    {
        $sysConf = PhateCore::getConfigure();
        if (!isset($sysConf['LOGGER']['load_yaml_file'])) {
            throw new PhateCommonException('no logger configure');
        }
        Fluent\Autoloader::register();
        $filename = PHATE_CONFIG_DIR . $sysConf['LOGGER']['load_yaml_file'];
        $config = PhateCommon::parseConfigYaml($filename);
        if (!isset($config['FLUENTD'])) {
            throw new PhateCommonException('no config for fluentd');
        }
        if (isset($config['FLUENTD']['SOCKET'])) {
            self::$_fluent = new Logger\FluentLogger($config['FLUENTD']['SOCKET']);
        } elseif (isset($config['FLUENTD']['HOST']) && isset($config['FLUENTD']['PORT'])) {
            self::$_fluent = new Logger\HttpLogger($config['FLUENTD']['HOST'], $config['FLUENTD']['PORT']);
        } else {
            throw new PhateCommonException('no config for fluentd');
        }
        
    }
    /**
     * Fluentロガーに出力
     * 
     * @param type $tag
     * @param array $data
     */
    public static function post($tag, array $data)
    {
        if (!self::$_fluent) {
            self::init();
        }
        self::$_fluent->post($tag, $data);
    }
}
