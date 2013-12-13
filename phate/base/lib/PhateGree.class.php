<?php
/**
 * PhateGreeクラス
 *
 * 設定ファイル読んで、エンドポイントやキー情報を取得して
 * OAuth使ってGREEのAPIにホゲホゲするクラス
 * 基本trusted通信かも
 * https://docs.developer.gree.net/ja/globaltechnicalspecs/
 * 
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/10/15
 **/
class PhateGree
{
    private static $_config;
    
    /**
     * 設定ファイルよりgreeの設定を取得
     */
    private static function setConfig()
    {
        $sysConf = PhateCore::getConfigure();
        if (!isset($sysConf['GREE']['load_yaml_file'])) {
            throw new PhateCommonException('no gree configure');
        }
        $filename = PHATE_CONFIG_DIR . $sysConf['GREE']['load_yaml_file'];
        self::$_config = PhateCommon::parseConfigYaml($filename);
    }
    
    /**
     * 設定ファイルよりApplicationIDを取得
     * @return int
     * @throws PhateCommonException
     */
    public static function getApplicationId()
    {
        if (!self::$_config) {
            self::setConfig();
        }
        if (!isset(self::$_config['application_id'])) {
            throw new PhateCommonException('no gree configure application id');
        }
        return self::$_config['application_id'];
    }

    /**
     * 設定ファイルよりConsumerKeyを取得
     * @return string
     * @throws PhateCommonException
     */
    public static function getConsumerKey()
    {
        if (!self::$_config) {
            self::setConfig();
        }
        if (!isset(self::$_config['consumer_key'])) {
            throw new PhateCommonException('no gree configure consumer key');
        }
        return trim(self::$_config['consumer_key']);
    }
    
    /**
     * 設定ファイルよりConsumerSecretを取得
     * @return string
     * @throws PhateCommonException
     */
    public static function getConsumerSecret()
    {
        if (!self::$_config) {
            self::setConfig();
        }
        if (!isset(self::$_config['consumer_secret'])) {
            throw new PhateCommonException('no gree configure consumer secret');
        }
        return trim(self::$_config['consumer_secret']);
    }

    /**
     * gree側のユーザIDを取得する
     * @return int
     */
    public static function getUserId()
    {
        // Request from Gadget Server
        $request_from_gadget = OAuthRequest::from_request(NULL, NULL, NULL);
        // opensocial_viewer_id from Gadget Server
        return $request_from_gadget->get_parameter('opensocial_viewer_id');
    }
    
    /**
     * 設定ファイルからエンドポイントを取得する
     * @param boolean $isSocial
     * @return string
     * @throws Exception
     */
    protected static function getApiBaseUrl($isSocial = false)
    {
        if (!self::$_config) {
            self::setConfig();
        }
        switch (PhateHttpRequest::getDeviceCode()) {
            case PhateHttpRequest::DEVICE_FP_DOCOMO:
            case PhateHttpRequest::DEVICE_FP_AU:
            case PhateHttpRequest::DEVICE_FP_SOFTBANK:
            case PhateHttpRequest::DEVICE_FP_WILLCOM:
            case PhateHttpRequest::DEVICE_FP_EMOBILE:
                return $isSocial ? self::$_config['fp_social_baseurl'] : self::$_config['fp_api_baseurl'];
                break;
            case PhateHttpRequest::DEVICE_SP_IOS:
            case PhateHttpRequest::DEVICE_SP_ANDROID:
            case PhateHttpRequest::DEVICE_SP_WINDOWS:
                return $isSocial ? self::$_config['sp_social_baseurl'] : self::$_config['sp_api_baseurl'];
                break;
            case PhateHttpRequest::DEVICE_APPLI_IOS:
            case PhateHttpRequest::DEVICE_APPLI_ANDROID:
            case PhateHttpRequest::DEVICE_APPLI_SWF:
                return $isSocial ? self::$_config['sp_social_baseurl'] : self::$_config['sp_api_baseurl'];
                break;
            case PhateHttpRequest::DEVICE_PC_PLAIN:
            case PhateHttpRequest::DEVICE_APPLI_PC:
            case PhateHttpRequest::DEVICE_UNKNOWN:
                return $isSocial ? self::$_config['pc_social_baseurl'] : self::$_config['pc_api_baseurl'];
                break;
            default:
                throw new PhateGreeException('unknown device for using gree api');
                break;
        }
        
    }
    
    /**
     * ガジェットサーバのURLを取得する
     * @return string
     * @throws Exception
     */
    public static function getLinkBaseUrl()
    {
        if (!self::$_config) {
            self::setConfig();
        }
        switch (PhateHttpRequest::getDeviceCode()) {
            case PhateHttpRequest::DEVICE_FP_DOCOMO:
            case PhateHttpRequest::DEVICE_FP_AU:
            case PhateHttpRequest::DEVICE_FP_SOFTBANK:
            case PhateHttpRequest::DEVICE_FP_WILLCOM:
            case PhateHttpRequest::DEVICE_FP_EMOBILE:
                return self::$_config['fp_url_baseurl'];
                break;
            case PhateHttpRequest::DEVICE_SP_IOS:
            case PhateHttpRequest::DEVICE_SP_ANDROID:
            case PhateHttpRequest::DEVICE_SP_WINDOWS:
                return self::$_config['sp_url_baseurl'];
                break;
            case PhateHttpRequest::DEVICE_APPLI_IOS:
            case PhateHttpRequest::DEVICE_APPLI_ANDROID:
            case PhateHttpRequest::DEVICE_APPLI_SWF:
                return self::$_config['sp_url_baseurl'];
                break;
            case PhateHttpRequest::DEVICE_PC_PLAIN:
            case PhateHttpRequest::DEVICE_APPLI_PC:
            case PhateHttpRequest::DEVICE_UNKNOWN:
                return self::$_config['pc_url_baseurl'];
                break;
            default:
                throw new PhateGreeException('unknown device for using gree api');
                break;
        }
    }

    /**
     * ガジェットサーバ越しのURLに加工する
     * @param string $url
     * @return string
     */
    public static function makeLinkUrl($url)
    {
        $s = $url[0] == '/' ? '' : DIRECTORY_SEPARATOR;
        $serverUrl = PhateCore::getBaseUri() . $s . $url;
        return self::getLinkBaseUrl() . '?url=' . urlencode($serverUrl);
    }

    
    /**
     * ユーザのデバイスからリクエスト方法を選択するラッピングメソッド
     * @param boolean $isSocial
     * @return string
     * @throws Exception
     */
    protected static function proxyRequest($url, $method='GET', $postData=null)
    {
        if (!self::gadgetValidate()) {
            throw new PhateGreeException('gree restful API connection fail');
        }
        switch (PhateHttpRequest::getDeviceCode()) {
            case PhateHttpRequest::DEVICE_FP_DOCOMO:
            case PhateHttpRequest::DEVICE_FP_AU:
            case PhateHttpRequest::DEVICE_FP_SOFTBANK:
            case PhateHttpRequest::DEVICE_FP_WILLCOM:
            case PhateHttpRequest::DEVICE_FP_EMOBILE:
            case PhateHttpRequest::DEVICE_SP_IOS:
            case PhateHttpRequest::DEVICE_SP_ANDROID:
            case PhateHttpRequest::DEVICE_SP_WINDOWS:
                return self::phoneProxyRequest($url, $method, $postData);
                break;
            case PhateHttpRequest::DEVICE_APPLI_IOS:
            case PhateHttpRequest::DEVICE_APPLI_ANDROID:
            case PhateHttpRequest::DEVICE_APPLI_SWF:
                return self::appliProxyRequest($url, $method, $postData);
                break;
            case PhateHttpRequest::DEVICE_PC_PLAIN:
            case PhateHttpRequest::DEVICE_APPLI_PC:
            case PhateHttpRequest::DEVICE_UNKNOWN:
                return self::pcProxyRequest($url, $method, $postData);
                break;
            default:
                throw new Exception('unknown device for user');
                break;
        }
        
    }
    
    /**
     * gadgetXML経由通信かの確認
     * @return boolean
     */
    public static function gadgetValidate() {
        // Consumer Key from DevPortal
        $consumer_key = self::getConsumerKey();
        // Consumer Secret from DevPortal
        $consumer_secret = self::getConsumerSecret();
        
        // OAuth Consumer
        $consumer = new OAuthConsumer($consumer_key, $consumer_secret, NULL);
        // Request from Gadget Server
        $request = OAuthRequest::from_request(NULL, NULL, NULL);
        // Token from Gadget Server
        $token = new OAuthToken($request->get_parameter('oauth_token'), $request->get_parameter('oauth_token_secret'));
        // Signature from Gadget Server
        $signature = $request->get_parameter('oauth_signature');
        // Signature Method (HMAC-SHA1)
        $sign_method = new OAuthSignatureMethod_HMAC_SHA1();
        // Validate Request from Gadget Server
        $valid = $sign_method->check_signature($request, $consumer, $token, $signature);
        return $valid;
    }

    /**
     * スマフォwebでガジェットサーバ経由でのProxy通信(Proxy通信があるか不明)
     * @param string $url
     * @param string $method
     * @param string $postData
     * @return array
     * @throws PhateGreeException
     */
    protected static function phoneProxyRequest($url, $method='GET', $postData=null)
    {
        $consumerKey    = self::getConsumerKey();
        $consumerSecret = self::getConsumerSecret();
        $consumer = new OAuthConsumer($consumerKey, $consumerSecret, NULL);
        $requestFromGadget = OAuthRequest::from_request(NULL, NULL, NULL);
        $tokenFromGadget = new OAuthToken($requestFromGadget->get_parameter('oauth_token'), $requestFromGadget->get_parameter('oauth_token_secret'));
        $url .= strpos($url, '?') === false ? '?' : '&';
        $url .= 'xoauth_requestor_id=' . $requestFromGadget->get_parameter('opensocial_viewer_id');
        $params = array();
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        $requestToAPI = OAuthRequest::from_consumer_and_token($consumer, $tokenFromGadget, $method, $url, $params);
        $requestToAPI->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $tokenFromGadget);
        $authHeader = array($requestToAPI->to_header());
        
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_ENCODING , "gzip");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $authHeader);
        if (($method === 'POST') || ($method === 'PUT')) {
            curl_setopt ($curl, CURLOPT_POST, true);
            curl_setopt ($curl, CURLOPT_POSTFIELDS, $postData);
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (!preg_match('/^2.*/', $statusCode)) {
            throw new PhateGreeException($statusCode . ' gree restful API connection fail');
        }
        $obj = $response ? json_decode($response, true) : NULL;
        return $obj;
    }
    
    /**
     * アプリでのProxy通信(Proxy通信があるか不明)
     * @param string $url
     * @param string $method
     * @param string $postData
     * @return type
     * @throws PhateGreeException
     */
    protected static function appliProxyRequest($url, $method='GET', $postData=null)
    {
        $key    = self::getConsumerKey();
        $secret = self::getConsumerSecret();
        $sig_method = new OAuthSignatureMethod_HMAC_SHA1(); // signature method
        $consumer = new OAuthConsumer($key, $secret, NULL); // Consumer
        $requestFromGadget = OAuthRequest::from_request(NULL, NULL, NULL);
        $url .= strpos($url, '?') === false ? '?' : '&';
        $url .= 'xoauth_requestor_id=' . $requestFromGadget->get_parameter('opensocial_viewer_id');
        $params = array();
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        $request = OAuthRequest::from_consumer_and_token($consumer, NULL, $method, $url, $params);
        $request->sign_request($sig_method, $consumer, NULL);
        $auth_header = array($request->to_header(""));

        // access to platform server
        $curl = curl_init($url);
        if (($method === 'POST') || ($method === 'PUT')) {
            curl_setopt ($curl, CURLOPT_POST, true);
            curl_setopt ($curl, CURLOPT_POSTFIELDS, $postData);
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HEADER, true); // result is only returned in HTTP header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_ENCODING , "gzip");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $auth_header);
        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (!preg_match('/^2.*/', $statusCode)) {
            throw new PhateGreeException('gree restful API connection fail');
        }
        $obj = $response ? json_decode($response, true) : NULL;
        return $obj;
    }
    
    /**
     * pcからの場合のProxy通信(Proxy通信があるか不明)
     * @param string $url
     * @param string $method
     * @param array $postData
     * @return array
     * @throws PhateGreeException
     */
    protected static function pcProxyRequest($url, $method='GET', $postData=null)
    {
        $consumerKey    = self::getConsumerKey();
        $consumerSecret = self::getConsumerSecret();
        $consumer = new OAuthConsumer($consumerKey, $consumerSecret, NULL);
        $requestFromGadget = OAuthRequest::from_request(NULL, NULL, NULL);
        $url .= strpos($url, '?') === false ? '?' : '&';
        $url .= 'xoauth_requestor_id=' . $requestFromGadget->get_parameter('opensocial_viewer_id');
        $params = array();
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        $request = OAuthRequest::from_consumer_and_token($consumer, NULL, $method, $url, $params);
        $request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, NULL);
        $auth_header = array($request->to_header());
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_ENCODING , "gzip");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $auth_header);
        if (($method === 'POST') || ($method === 'PUT')) {
            curl_setopt ($curl, CURLOPT_POST, true);
            curl_setopt ($curl, CURLOPT_POSTFIELDS, $postData);
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (!preg_match('/^2.*/', $statusCode)) {
            throw new PhateGreeException('gree restful API connection fail');
        }
        $obj = $response ? json_decode($response, true) : NULL;
        return $obj;
    }
    
    /**
     * trusted通信をする
     * @param string $url
     * @param string $method
     * @param array $postData
     * @return array
     * @throws PhateGreeException
     */
    protected static function trustedRequest($url, $method='GET', $postData=null)
    {
        $applicationId  = self::getApplicationId();
        $url .= strpos($url, '?') === false ? '?' : '&';
        $url .= 'xoauth_requestor_id=' . $applicationId;
        $consumerKey    = self::getConsumerKey();
        $consumerSecret = self::getConsumerSecret();
        $consumer = new OAuthConsumer($consumerKey, $consumerSecret, NULL);
        
        $params = array();
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        $requestToAPI = OAuthRequest::from_consumer_and_token($consumer, NULL, $method, $url, $params);
        $requestToAPI->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, NULL);
        $authHeader = array($requestToAPI->to_header());
        
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_ENCODING , "gzip");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $authHeader);
        if (($method === 'POST') || ($method === 'PUT')) {
            curl_setopt ($curl, CURLOPT_POST, true);
            curl_setopt ($curl, CURLOPT_POSTFIELDS, $postData);
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (!preg_match('/^2.*/', $statusCode)) {
            throw new PhateGreeException($statusCode . 'gree restful API connection fail(trusted)');
        }
        $obj = $response ? json_decode($response, true) : NULL;
        return $obj;
    }
    
    
    const LIFE_CYCLE_EVENT_TYPE_ADD_APP         = 'event.addapp'; // アプリ利用開始
    const LIFE_CYCLE_EVENT_TYPE_SUSPEND_APP     = 'event.suspendapp'; // アプリ中断
    const LIFE_CYCLE_EVENT_TYPE_RESUME_APP      = 'event.resumeapp'; // アプリ再開
    const LIFE_CYCLE_EVENT_TYPE_UPGRADE_USER    = 'event.upgradeuser'; // アップグレード完了
    const LIFE_CYCLE_EVENT_TYPE_REMOVE_APP      = 'event.removeapp'; // アプリ利用完了
    const LIFE_CYCLE_EVENT_TYPE_JOIN_COMMUNITY  = 'gree.join_community'; // 公式サークル入会
    const LIFE_CYCLE_EVENT_TYPE_LEAVE_COMMUNITY = 'gree.leave_community'; // 公式サークル脱退
    
    /**
     * リクエストパラメータからライフサイクルイベント情報を取得する
     * @return array
     * @throws PhateGreeException
     */
    public static function getLifeCycleInfo() {
        if (!self::gadgetValidate()) {
            throw new PhateGreeException('gree lifecycle API connection fail');
        }
        $request = OAuthRequest::from_request(NULL, NULL, NULL);
        if ($request->get_parameter('opensocial_app_id') != self::getApplicationId()) {
            throw new PhateGreeException('gree lifecycle API connection fail');
        }
        $rtn = array();
        switch ($request->get_parameter('eventtype')) {
            case self::LIFE_CYCLE_EVENT_TYPE_ADD_APP:
                $rtn = array(
                    'userId' => $request->get_parameter('id'),
                    'inviteUserId' => $request->get_parameter('invite_from_id') ? $request->get_parameter('invite_from_id') : null,
                    'device' => $request->get_parameter('device'),
                    );
                break;
            case self::LIFE_CYCLE_EVENT_TYPE_SUSPEND_APP:
                $rtn = array(
                    'userId' => $request->get_parameter('id'),
                    );
                break;
            case self::LIFE_CYCLE_EVENT_TYPE_RESUME_APP:
                $rtn = array(
                    'userId' => $request->get_parameter('id'),
                    );
                break;
                break;
            case self::LIFE_CYCLE_EVENT_TYPE_UPGRADE_USER:
                $rtn = array(
                    'userId' => $request->get_parameter('id'),
                    'afterGrade' => $request->get_parameter('grade'),
                    );
                break;
                break;
            case self::LIFE_CYCLE_EVENT_TYPE_REMOVE_APP:
                $rtn = array(
                    'userId' => $request->get_parameter('id'),
                    'device' => $request->get_parameter('device'),
                    );
                break;
            case self::LIFE_CYCLE_EVENT_TYPE_JOIN_COMMUNITY:
                $rtn = array(
                    'userId' => $request->get_parameter('id'),
                    'communityId' => $request->get_parameter('community_id'),
                    'actedTime' => $request->get_parameter('acted_time'),
                );
                break;
            case self::LIFE_CYCLE_EVENT_TYPE_LEAVE_COMMUNITY:
                $rtn = array(
                    'userId' => $request->get_parameter('id'),
                    'communityId' => $request->get_parameter('community_id'),
                    'actedTime' => $request->get_parameter('acted_time'),
                );
                break;
            default:
                throw new PhateGreeException('gree lifecycle unknown type');
        }
        return $rtn;
    }
    
}

class PhateGreeException extends Exception
{
}

