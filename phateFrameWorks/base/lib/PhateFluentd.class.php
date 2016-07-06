<?php
namespace Phate;

// Fluentをインクルード
include_once PHATE_LIB_VENDOR_DIR . 'Fluent/Autoloader.php';
use Fluent\Logger;

/**
 * Fluentdクラス
 *
 * https://github.com/fluent/fluent-logger-php
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class Fluentd
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
        if (!($fileName = Core::getConfigure('logger_config_file'))) {
            throw new CommonException('no logger configure');
        }
        if (!($config = Common::parseConfigYaml(PHATE_CONFIG_DIR . $fileName))) {
            throw new CommonException('no logger configure');
        }
        if (!isset($config['fluentd'])) {
            throw new CommonException('no config for fluentd');
        }
        Fluent\Autoloader::register();
        if (isset($config['fluentd']['socket'])) {
            self::$_fluent = new Logger\FluentLogger($config['fluentd']['socket']);
        } elseif (isset($config['fluentd']['host']) && isset($config['fluentd']['port'])) {
            self::$_fluent = new Logger\HttpLogger($config['fluentd']['host'], $config['fluentd']['port']);
        } else {
            throw new CommonException('no config for fluentd');
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
