<?php
namespace Phate;

/**
 * Apnsクラス
 *
 * 設定ファイル読んで、
 * Apnsするクラス
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2015/03/26
 **/
class Apns
{
    const TRY_CONECTION_TIMES = 3;
    const TRY_CONECTION_TIMEOUT = 3;
    const STREAM_CONECTION_TIMEOUT = 86400;
    
    private $_config;
    private $_fp;
    private $_sendSize;
    private $_connectTimes = 0;
    
    /**
     * 設定ファイルよりappleの設定を取得
     */
    public function __construct()
    {
        // config取得
        $sysConf = Core::getConfigure();
        if (!isset($sysConf['apple_config_file'])) {
            throw new CommonException('no apple configure');
        }
        $filename = PHATE_CONFIG_DIR . $sysConf['apple_config_file'];
        $this->_config = Common::parseConfigYaml($filename);
    }
    
    /**
     * 設定のホストにコネクトする
     * @throws CommonException
     */
    private function connect()
    {
        $this->_sendSize = 0;
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->_config['push_ssl_pem']);
        if (array_key_exists('push_pem_passphrase', $this->_config) && $this->_config['push_pem_passphrase']) {
            stream_context_set_option($ctx, 'ssl', 'passphrase', $this->_config['push_pem_passphrase']);
        }
        $i = 0;
        do {
            ++$i;
            if (++$this->_connectTimes > 0) {
                sleep(1);
            }
            $this->_fp = stream_socket_client(
                    $this->_config['push_ssl_host'],
                    $errno,
                    $errstr,
                    self::TRY_CONECTION_TIMEOUT,
                    STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT,
                    $ctx);
        } while ((!$this->isConnected()) && ($i <= self::TRY_CONECTION_TIMES));
        if (!$this->_fp) {
            throw new CommonException('APNS connect error : ' . $errno . ' : ' . $errstr);
        }
        stream_set_timeout($this->_fp, self::STREAM_CONECTION_TIMEOUT);
    }
    
    /**
     * 現在コネクト中かを返す
     * @return type
     */
    private function isConnected()
    {
        return (bool)$this->_fp;
    }
    
    /**
     * push通知を送信する
     * @param string $deviceToken
     * @param ApnsMessage $apnsMessage
     * @param string $identifier
     * @return boolean
     */
    public function sendNotification($deviceToken, ApnsMessage $apnsMessage, $identifier = null)
    {
        if (!$this->isConnected()) {
            $this->connect();
        }
        // PUSH内容を作成
        $payload = $apnsMessage->getPayload();
        $frameData = '';
        
        // 1 Device token               32 bytes
        $frameData .= chr(1) . pack('n', 32) . pack('H*', $deviceToken); 
        
        // 2 Payload                    variable length, less than or equal to 2 kilobytes
        $payloadLength = strlen($payload);
        $frameData .= chr(2) . pack('n', $payloadLength) . $payload;
        
        // 3 Notification identifier    4 bytes
        if (!is_null($identifier) && strlen($identifier) <= 4) {
            $frameData .= chr(3) . pack('n', 4) . $identifier; 
        }
        
        // 4 Expiration date            4 bytes
        $frameData .= chr(4) . pack('n', 4) . pack('N', time() + 600); 
        
        // 5 Priority                   1 byte
        //      10 The push message is sent immediately.
        //      5 The push message is sent at a time that conserves power on the device receiving it.
        $frameData .= chr(5) . pack('n', 1) . chr(10); 
        
        $frameLength = strlen($frameData);
        // メッセージ文字列完成
        $msg = chr(2) .  pack('N', $frameLength) . $frameData;
        // と、メッセージ長を取得
        $msgSize = strlen($msg);
        try{
            fwrite($this->_fp, $msg, $msgSize);
            echo fgets($this->_fp, 7);
            /*
             * $result = fread($this->_fp, 7);
            if (!$result){
                Logger::error("Apns failure: deviceToken : " . $deviceToken);
                Logger::error('APNSResLog : ' . strlen($result));
                $this->disconnect();
                return false;
            }
             * 
             */
            $this->_sendSize += $msgSize;
            if($this->_sendSize >= 5120){
                $this->disconnect();
            }
        } catch (Exception $e){
            $this->disconnect();
            Logger::error("Apns : " . $e->getCode() . " : " . $e->getMessage());
        }
        return true;
    }
    
    /**
     * 切断する
     */
    private function disconnect()
    {
        Logger::debug("Apns connection times : " . $this->_connectTimes);
        if (!$this->isConnected()) {
            fclose($this->_fp);
        }
    }
    
    /**
     * destruct時も切断を仕込んでおく
     */
    public function __destruct()
    {
        $this->disconnect();
    }
    
}

/**
 * ApnsMessageクラス
 *
 * Apnsする際に、メッセージの内容を設定するクラス構造体
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2015/03/26
 **/

class ApnsMessage
{
    private $_title = null;
    private $_body = null;
    private $_badge = null;
    private $_sound = null;
    private $_contentAvailable = null;
    private $_category = null;
    private $_customProperty =[];
    private $_launchImage = null;
    private $_titleLocKey = null;
    private $_titleLocArgs = null;
    private $_actionLocKey = null;
    private $_locKey = null;
    private $_locArgs = null;

    public function setTitle($title) {
        $this->_title = $title;
    }
    
    public function setBody($body) {
        $this->_body = $body;
    }
    
    public function setBadge($badge) {
        $this->_badge = $badge;
    }
    
    public function setSound($sound = 'default') {
        $this->_sound = $sound;
    }

    public function setCategory($category = '') {
        $this->_category = $category;
    }
    
    public function setContentAvailable($contentAvailable = true) {
        $this->_contentAvailable = $contentAvailable;
    }
    
    public function addCustomProperty($key, $value) {
        $this->_customProperty[$key]= $value;
    }

    public function setLaunchImage($imageFileName) {
        $this->_launchImage = $imageFileName;
    }
    
    public function setTitleLoc($key, $args = null) {
        $this->_titleLocKey = $key;
        $this->_titleLocArgs = $args;
    }
    
    public function setActionLocKey($key) {
        $this->_actionLocKey = $key;
        
    }
    
    public function setmessageLoc($key, $args = null) {
        $this->_locKey = $key;
        $this->_locArgs = $args;
    }
    
    
    public function getPayload() {
        if (is_null($this->_body)) {
            throw new Exception('push message is null');
        }

        $arr = ["aps" => []];
        if (is_null($this->_title) && 
            is_null($this->_titleLocKey) && 
            is_null($this->_actionLocKey) && 
            is_null($this->_locKey) &&
            is_null($this->_launchImage)) {
            $arr["aps"]["alert"] = $this->_body;
        } else {
            $arr["aps"]["alert"] = ["body" => $this->_body];
            if (!is_null($this->_title)) {
                $arr["aps"]["alert"]["title"] = $this->_title;
            }
            if (!is_null($this->_actionLocKey)) {
                $arr["aps"]["alert"]["action-loc-key"] = $this->_actionLocKey;
            }
            if (!is_null($this->_titleLocKey)) {
                $arr["aps"]["alert"]["title-loc-key"] = $this->_titleLocKey;
                $arr["aps"]["alert"]["title-loc-args"] = $this->_titleLocArgs;
            }
            if (!is_null($this->_locKey)) {
                $arr["aps"]["alert"]["loc-key"] = $this->_locKey;
                $arr["aps"]["alert"]["loc-args"] = $this->_locArgs;
            }
            if (!is_null($this->_launchImage)) {
                $arr["aps"]["alert"]["launch-image"] = $this->_launchImage;
            }
        }
        if (!is_null($this->_badge)) {
            $arr["aps"]["badge"] = $this->_badge;
        }
        if (!is_null($this->_sound)) {
            $arr["aps"]["sound"] = $this->_sound;
        }
        if (!is_null($this->_contentAvailable)) {
            $arr["aps"]["content-available"] = $this->_contentAvailable;
        }
        if (!is_null($this->_category)) {
            $arr["aps"]["category"] = $this->_category;
        }
        
        if ($this->_customProperty) {
            foreach ($this->_customProperty as $key => $value) {
                $arr[$key] = $value;
            }
        }
        $json = json_encode($arr);
        return $json;
    }
    
    
}
