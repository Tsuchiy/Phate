<?php
/**
 * PhateAppleクラス
 *
 * 設定ファイル読んで、
 * AppleのAPIにホゲホゲするクラス
 * 未実装
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateApple
{
    private static $_config;
    
    /**
     * 設定ファイルよりappleの設定を取得
     */
    private static function setConfig()
    {
        $sysConf = PhateCore::getConfigure();
        if (!isset($sysConf['APPLE']['load_yaml_file'])) {
            throw new PhateCommonException('no apple configure');
        }
        $filename = PHATE_CONFIG_DIR . $sysConf['APPLE']['load_yaml_file'];
        self::$_config = PhateCommon::parseConfigYaml($filename);
    }

    /**
     * iOS6絵文字を含むか判定する
     * 参照用のjsonは https://github.com/punchdrunker/iOSEmoji より
     * 
     * @param string $string
     * @return boolean
     */
    public static function hasIos6Emoji($string)
    {
        $fileName = PHATE_LIB_VENDOR_DIR . '/iOSEmoji/table_html/emoji.json';
        $arr = json_decode(str_replace("'", '"', file_get_contents($fileName)), true);
        $pattern = '/';
        foreach ($arr as $v) {
            $pattern .= '(' . str_replace(' ' ,'', str_replace('0x' ,'', $v)) . ')|';
        }
        $pattern = strtolower(substr($pattern, 0, -1)) . '/';
        $chr = mb_convert_encoding($string, "UTF16", "UTF8");
        $escaped = '';
        for ($i = 0, $l = strlen($chr); $i < $l; $i++) {
            $escaped .= strtolower(sprintf("%02x", ord($chr[$i])));
        }
        return preg_match($pattern, $escaped);
    }
    /**
     * レシートが正当か確認する
     * @param string $receipt
     * @return array|boolean
     * @throws PhateCommonException
     */
    public static function verifyReceipt($receipt)
    {
        if (!self::$_config) {
            self::setConfig();
        }
        if (!(self::$_config['host']) || !(self::$_config['ca_path']) || !(self::$_config['ca_info'])
                || !(self::$_config['ssl_cert']) || !(self::$_config['ssl_cert_password'])) {
            throw new PhateCommonException('no apple verify host');
        }
        $postData = json_encode(array('receipt-data' => $receipt));
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
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/x-www-form-urlencoded',
                                                      'Content-Length' => strlen($postData)));
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
    /**
     * APNsとかも実装するようになるんじゃないでしょうか
     */
    
}
