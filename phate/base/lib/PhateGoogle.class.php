<?php
/**
 * PhateGoogleクラス
 *
 * 設定ファイル読んで、
 * Googleにホゲホゲするクラス(OAuthとか使うのかしら？)
 * 未実装
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateGoogle
{
    private static $_config;
    private static $_applicationKey;
    private static $_secret;
    
    /*
     * 設定ファイルよりmemcacheの設定を取得
     */
    private static function setConfig()
    {
        $sysConf = PhateCore::getConfigure();
        if (!isset($sysConf['GOOGLE']['load_yaml_file'])) {
            throw new PhateCommonException('no mbga configure');
        }
        $filename = PHATE_CONFIG_DIR . $sysConf['GOOGLE']['load_yaml_file'];
        self::$_config = PhateCommon::parseConfigYaml($filename);
    }
    /* GCMとかは使うようになるんじゃないだろうか */
    /* paymentのreceiptもどきの正当性は署名ファイルからできるらしい openssl_verify */
    
}
