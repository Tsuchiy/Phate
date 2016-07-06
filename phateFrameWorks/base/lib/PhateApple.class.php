<?php
namespace Phate;

/**
 * Appleクラス
 *
 * 設定ファイル読んで、
 * AppleのAPIにホゲホゲするクラス
 * 未実装
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class Apple
{
    private static $_config;
    
    /**
     * 設定ファイルよりappleの設定を取得
     */
    private static function setConfig()
    {
        $sysConf = Core::getConfigure();
        if (!isset($sysConf['APPLE']['load_yaml_file'])) {
            throw new CommonException('no apple configure');
        }
        $filename = PHATE_CONFIG_DIR . $sysConf['APPLE']['load_yaml_file'];
        self::$_config = Common::parseConfigYaml($filename);
    }

    /**
     * レシートが正当か確認する
     * @param string $receipt
     * @return array|boolean
     * @throws CommonException
     */
    public static function verifyReceipt($receipt)
    {
        if (!self::$_config) {
            self::setConfig();
        }
        if (!(self::$_config['host']) || !(self::$_config['ca_path']) || !(self::$_config['ca_info'])
                || !(self::$_config['ssl_cert']) || !(self::$_config['ssl_cert_password'])) {
            throw new CommonException('no apple verify host');
        }
        $postData = json_encode(['receipt-data' => $receipt]);
        $curl = curl_init(self::$_config['host'] . 'verifyReceipt');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_CAPATH, self::$_config['ca_path']);
        curl_setopt($curl, CURLOPT_CAINFO, self::$_config['ca_info']);
        curl_setopt($curl, CURLOPT_SSLCERT, self::$_config['ssl_cert']);
        curl_setopt($curl, CURLOPT_SSLCERTPASSWD, self::$_config['ssl_cert_password']);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type' => 'application/x-www-form-urlencoded',
                                                'Content-Length' => strlen($postData)]);
        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (!preg_match('/^2.*/', $statusCode)) {
            throw new Exception($statusCode . 'apple verify server connection fail');
        }
        if (!($data = json_decode($response, true))) {
            throw new Exception('apple verify server wrong response');
        }
        if (!isset($data['status']) || ($data['status'] != 0)) {
            return false;
        }
        return $data['receipt'];
        
    }
}
