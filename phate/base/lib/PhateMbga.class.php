<?php
/**
 * PhateMbgaクラス
 *
 * 設定ファイル読んで、エンドポイントやキー情報を取得して
 * OAuth使ってMBGAのAPIにホゲホゲするクラス
 * 未実装
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/10/15
 **/
class PhateMbga
{
    private static $_config;
    
    /*
     * 設定ファイルよりmbgaの設定を取得
     */
    private static function setConfig()
    {
        $sysConf = PhateCore::getConfigure();
        if (!isset($sysConf['MBGA']['load_yaml_file'])) {
            throw new PhateCommonException('no mbga configure');
        }
        $filename = PHATE_CONFIG_DIR . $sysConf['MBGA']['load_yaml_file'];
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
            throw new PhateCommonException('no mbga configure application id');
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
            throw new PhateCommonException('no mbga configure consumer key');
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
            throw new PhateCommonException('no mbga configure consumer secret');
        }
        return trim(self::$_config['consumer_secret']);
    }

    /**
     * mbga側のユーザIDを取得する
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
                throw new PhateMbgaException('unknown device for using mbga api');
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
                throw new PhateMbgaException('unknown device for using mbga api');
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
            throw new PhateMbgaException('mbga restful API connection fail');
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
     * スマフォwebでガジェットサーバ経由でのProxy通信
     * @param string $url
     * @param string $method
     * @param string $postData
     * @return array
     * @throws PhateMbgaException
     */
    protected static function phoneProxyRequest($url, $method='GET', $postData=null)
    {
        $consumerKey    = self::getConsumerKey();
        $consumerSecret = self::getConsumerSecret();
        $consumer = new OAuthConsumer($consumerKey, $consumerSecret, NULL);
        $requestFromGadget = OAuthRequest::from_request(NULL, NULL, NULL);
        $tokenFromGadget = new OAuthToken($requestFromGadget->get_parameter('oauth_token'), $requestFromGadget->get_parameter('oauth_token_secret'));
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
            throw new PhateMbgaException($statusCode . ' mbga restful API connection fail');
        }
        $obj = $response ? json_decode($response, true) : NULL;
        return $obj;
    }
    
    /**
     * アプリでのProxy通信(サンプルコードから適当に実装、未確認)
     * @param string $url
     * @param string $method
     * @param string $postData
     * @return type
     * @throws PhateMbgaException
     */
    protected static function appliProxyRequest($url, $method='GET', $postData=null)
    {
        $key    = self::getConsumerKey();
        $secret = self::getConsumerSecret();
        $sig_method = new OAuthSignatureMethod_HMAC_SHA1(); // signature method
        $consumer = new OAuthConsumer($key, $secret, NULL); // Consumer
        
        // generate Authentication Header
        $params = array(
            "oauth_callback" => "oob",
        );
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
            throw new PhateMbgaException('mbga restful API connection fail');
        }
        $obj = $response ? json_decode($response, true) : NULL;
        return $obj;
    }
    
    /**
     * pcからの場合のProxy通信
     * @param string $url
     * @param string $method
     * @param array $postData
     * @return array
     * @throws PhateMbgaException
     */
    protected static function pcProxyRequest($url, $method='GET', $postData=null)
    {
        $consumerKey    = self::getConsumerKey();
        $consumerSecret = self::getConsumerSecret();
        $consumer = new OAuthConsumer($consumerKey, $consumerSecret, NULL);
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
            throw new PhateMbgaException('mbga restful API connection fail');
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
     * @throws PhateMbgaException
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
            throw new PhateMbgaException($statusCode . ' mbga restful API connection fail(trusted)');
        }
        $obj = $response ? json_decode($response, true) : NULL;
        return $obj;
    }
    
    /**
     * textDataGroupを作成する
     * @param string $textDataGroupName
     * @return type
     */
    public static function makeTextDataGroup($textDataGroupName)
    {
        $url  = self::getApiBaseUrl();
        $url .= '/textdata/@app/@all';
        $send = array('name' => $textDataGroupName);
        $postData = json_encode($send);
        
        return self::trustedRequest($url, 'POST', $postData);
    }
    
    /**
     * textDataGroupを削除する
     * @param string $textDataGroupName
     * @return type
     */
    public static function deleteTextDataGroup($textDataGroupName)
    {
        $url  = self::getApiBaseUrl();
        $url .= '/textdata/@app/' . $textDataGroupName . '/@self';
        
        return self::trustedRequest($url, 'DELETE');
    }
    
    /**
     * TextDataAPIよりJSONを取得する
     * @param array $ids
     * @param string $textDataGroup
     * @return array
     */
    public static function getTextDataArray(array $ids, $textDataGroup)
    {
        $url  = self::getApiBaseUrl();
        $url .= '/textdata/@app/' . $textDataGroup . '/@all/' . implode(';', $ids) . '?format=json';
        
        return self::proxyRequest($url);
    }

    /**
     * TextDataAPIよりJSONを取得する
     * @param array $ids
     * @param string $textDataGroup
     * @return array
     */
    public static function getTextData(array $ids, $textDataGroup)
    {
        $res = self::getTextDataArray($ids, $textDataGroup);

        if (!isset($res["entry"])) {
            throw new PhateMbgaException;
        }
        $rtn = array();
        foreach ($res["entry"] as $value) {
            $rtn[$value["id"]] = $value["data"];
        }
        return $rtn;
    }
    
    
    /**
     * TextDataAPIにテキストをsetし、JSONを取得する
     * @param string $textData
     * @param string $textDataGroup
     * @return array
     */
    public static function setTextData($textData, $textDataGroup)
    {
        $url  = self::getApiBaseUrl();
        $url .= '/textdata/@app/' . $textDataGroup . '/@all/';
        $send = array('data' => $textData);
        $postData = json_encode($send);
        
        return self::proxyRequest($url, 'POST', $postData);
        
    }
    
    /**
     * TextDataAPIにテキストをdeleteし、JSONを取得する
     * @param array $ids
     * @param string $textDataGroup
     * @return boolean
     */
    public static function deleteTextData(array $ids, $textDataGroup)
    {
        $url  = self::getApiBaseUrl();
        $url .= '/textdata/@app/' . $textDataGroup . '/@all/' . implode(';', $ids);
        
        self::proxyRequest($url, 'DELETE');
        return true;
    }
    
    /**
     * NGWordAPIでテキストをチェックする
     * @param type $textData
     * @return boolean
     */
    public static function hasNGWord($textData)
    {
        $url  = self::getApiBaseUrl();
        $url .= '/ngword?_method=check&format=json';
        $send = array('data' => $textData);
        $postData = json_encode($send);

        $rtn = self::proxyRequest($url, 'POST', $postData);
        if (!isset($rtn["ngword"]["valid"])) {
            throw new PhateMbgaException('response is illegal');
        }
        
        return !$rtn["ngword"]["valid"];
    }
    
    /**
     * 指定したopen_social_viewer_idのpeople情報を取得(省略時はログインユーザ)
     * @param string $id
     * @return array
     */
    public static function getPeople($userId = null)
    {
        $url  = self::getApiBaseUrl();
        $url .= '/people/';
        $url .= is_null($userId) ? '@me' : $userId;
        $url .= '/@self?format=json';
        
        $rtn = self::proxyRequest($url);
        return $rtn["person"];
    }

    /**
     * 指定したopen_social_viewer_idのフレンドのpeople情報を取得
     * @param string $id
     * @return array
     */
    public static function getFriends($count = null, $statIndex = null)
    {
        $url  = self::getApiBaseUrl();
        $url .= '/people/@me/@friends?format=json';
        if (!is_null($count)) {
            $url .= '&count=' . $count;
        }
        if (!is_null($statIndex)) {
            $url .= '&startIndex=' . $statIndex;
        }
        
        $rtn = self::proxyRequest($url);
        return $rtn["entry"];
    }
    
    const AVATAR_SIZE_SMALL  = "small";
    const AVATAR_SIZE_MEDIUM = "medium";
    const AVATAR_SIZE_LARGE  = "large";
    const AVATAR_VIEW_ENTIRE = "entire";
    const AVATAR_VIEW_UPPER  = "upper";
    
    /**
     * AvatarAPIをコールしてavatarのURLを取得する
     * @param array $idArray
     * @param string $size
     * @param string $view
     * @return array
     */
    public static function getAvatar(array $idArray = null, $size = self::AVATAR_SIZE_SMALL, $view=self::AVATAR_VIEW_UPPER)
    {
        $guid = is_null($idArray) ? '@me' : implode(';', $idArray);
        $url  = self::getApiBaseUrl();
        $url .= '/avatar/' . $guid . '/@self/';
        $url .= 'size=' . $size . ';';
        $url .= 'view=' . $view . ';';
        $url .= 'type=image;';
        $url .= 'extension=png;';
        
        $res = self::proxyRequest($url);
        $rtn = array();
        if (!array_key_exists("entry", $res)) {
            $rtn[] = $res["avatar"]["url"];
        } else {
            foreach ($res["entry"] as $userId => $value) {
                $id = preg_replace('/^(.*):[0-9]*$', '$2', $userId);
                $rtn[$id] = $value['url'];
            }
        }
        return $rtn;
    }
    
    /**
     * BlacklistAPIをコールしてblacklistに乗っているかを取得する
     * @param string $fromUserId
     * @param string $toUserId
     * @return boolean
     */
    public static function isOnBlackList($fromUserId, $toUserId)
    {
        $url  = self::getApiBaseUrl();
        $url .= '/blacklist/' . $fromUserId . '/@all/' . $toUserId . '?format=json';
        
        try {
            self::proxyRequest($url);
        } catch (PhateMbgaException $e) {
            return false;
        }
        return true;
    }
    
    /**
     * payment登録を行う
     * @param string $callbackUrl
     * @param string $finishUrl
     * @param PhateMbgaPaymentEntryObject $obj
     * @return array
     * @throws PhateMbgaException
     */
    public static function registerPayment($callbackUrl, $finishUrl, PhateMbgaPaymentEntryObject $obj)
    {
        $url = self::getApiBaseUrl();
        $url .= '/payment/@me/@self/@app';
        
        $postData = json_encode(
            array(
                "callbackUrl" => $callbackUrl,
                "finishUrl" => $finishUrl,
                "entry" => array(
                    "itemId" => $obj->getItemId(),
                    "name" => $obj->getName(),
                    "unitPrice" => $obj->getUnitPrice(),
                    "amount" => $obj->getAmount(),
                    "description" => $obj->getDescription(),
                    "imageUrl"=> $obj->getImageUrl(),
                    )
                )
        );
        $rtn = self::proxyRequest($url, 'POST', $postData);
        if (!isset($rtn['payment']['endpointUrl'])) {
            throw new PhateMbgaException('response is illegal');
        }
        return array(
            'url' => $rtn['payment']['endpointUrl'],
            'paymentId' => $rtn['payment']['id'],
            );
    }
    
    const LIFE_CYCLE_EVENT_TYPE_ADD_APP = 'event.addapp'; // アプリ利用開始
    const LIFE_CYCLE_EVENT_TYPE_REMOVE_APP = 'event.removeapp'; // アプリ削除
    const LIFE_CYCLE_EVENT_TYPE_JOIN_GROUP = 'event.joingroup'; // 公式サークル入会
    const LIFE_CYCLE_EVENT_TYPE_LEAVE_GROUP = 'event.leavegroup'; // 公式サークル脱退
    const LIFE_CYCLE_EVENT_TYPE_POST_DIARY = 'event.postdiary'; // サイト経由日記投函
    
    /**
     * リクエストパラメータからライフサイクルイベント情報を取得する
     * @return array
     * @throws PhateMbgaException
     */
    public static function getLifeCycleInfo() {
        if (!self::gadgetValidate()) {
            throw new PhateMbgaException('mbga lifecycle API connection fail');
        }
        $request = OAuthRequest::from_request(NULL, NULL, NULL);
        if ($request->get_parameter('opensocial_app_id') != self::getApplicationId()) {
            throw new PhateMbgaException('mbga lifecycle API connection fail');
        }
        $rtn = array();
        switch ($request->get_parameter('eventtype')) {
            case self::LIFE_CYCLE_EVENT_TYPE_ADD_APP:
                $rtn = array(
                    'eventType' => $request->get_parameter('eventtype'),
                    'userId' => $request->get_parameter('id'),
                    'inviteUserId' => $request->get_parameter('mbga_invite_from'),
                    'isIntroduced' => $request->get_parameter('mbga_is_introduced'),
                    'isShellapp' => $request->get_parameter('mbga_is_shellapp'),
                );
                break;
            case self::LIFE_CYCLE_EVENT_TYPE_REMOVE_APP:
                $rtn = array(
                    'eventType' => $request->get_parameter('eventtype'),
                    'userId' => $request->get_parameter('id'),
                );
                break;
            case self::LIFE_CYCLE_EVENT_TYPE_JOIN_GROUP:
                $rtn = array(
                    'eventType' => $request->get_parameter('eventtype'),
                    'userId' => $request->get_parameter('id'),
                );
                break;
            case self::LIFE_CYCLE_EVENT_TYPE_LEAVE_GROUP:
                $rtn = array(
                    'eventType' => $request->get_parameter('eventtype'),
                    'userId' => $request->get_parameter('id'),
                );
                break;
            case self::LIFE_CYCLE_EVENT_TYPE_POST_DIARY:
                $rtn = array(
                    'eventType' => $request->get_parameter('eventtype'),
                    'userId' => $request->get_parameter('id'),
                    'diaryId' => $request->get_parameter('mbga_diary_id'),
                );
                break;
            default:
                throw new PhateMbgaException('mbga lifecycle unknown type');
        }
        return $rtn;
    }
    
}

class PhateMbgaException extends Exception
{
}

