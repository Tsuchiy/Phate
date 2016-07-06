<?php
namespace Phate;

/**
 * Googleクラス
 *
 * 設定ファイル読んで、
 * Googleに関する処理を行うクラス(未完)
 * 未実装
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class Google
{
    private static $_config;
    private static $_applicationKey;
    private static $_secret;
    
    /*
     * 設定ファイルよりgoogleの設定を取得
     */
    private static function setConfig()
    {
        $sysConf = Core::getConfigure();
        if (!isset($sysConf['GOOGLE']['load_yaml_file'])) {
            throw new CommonException('no mbga configure');
        }
        $filename = PHATE_CONFIG_DIR . $sysConf['GOOGLE']['load_yaml_file'];
        self::$_config = Common::parseConfigYaml($filename);
    }
    /* GCMとかは使うようになるんじゃないだろうか */
    /* paymentの正当性は署名ファイルからできるらしい openssl_verify */
    
}
