<?php
namespace Phate;
/**
 * HttpRequestクラス
 *
 * Httpリクエストで取得できる値を格納しておくクラス
 * コード内部から直接グローバル変数へアクセスすることを防ぐ
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class Request
{
    private static $_server;
    private static $_remoteAddr;
    private static $_method;
    private static $_requestParam;
    private static $_getParam;
    private static $_postParam;
    private static $_rawPostData;
    
    private static $_deviceCode;
    
    const DEVICE_PC_PLAIN = 0;
    const DEVICE_FP_DOCOMO = 1;
    const DEVICE_FP_AU = 2;
    const DEVICE_FP_SOFTBANK = 3;
    const DEVICE_FP_WILLCOM = 4;
    const DEVICE_FP_EMOBILE = 5;
    const DEVICE_SP_IOS = 11;
    const DEVICE_SP_ANDROID = 12;
    const DEVICE_SP_WINDOWS = 13;
    const DEVICE_APPLI_IOS = 21;
    const DEVICE_APPLI_ANDROID = 22;
    const DEVICE_APPLI_SWF = 23;
    const DEVICE_APPLI_PC = 24;
    const DEVICE_UNKNOWN = 99;
    
    private static $_headerParam;
    
    private static $_calledModuleName;
    private static $_calledControllerName;
    
    private static $_userId = null;
    
    /**
     * HTTPリクエストからリクエスト情報をオブジェクトにセットする
     * 
     * @access public
     * @param void
     * @return void
     * @throws NotFoundException
     */
    public static function init()
    {
        // スーパーグローバルの退避
        self::$_server = $_SERVER;
        self::$_requestParam = $_REQUEST ? $_REQUEST : [];
        self::$_getParam = $_GET ? $_GET : [];
        self::$_postParam = $_POST ? $_POST : [];
        // リクエストメソッド
        self::$_method = array_key_exists('REQUEST_METHOD', self::$_server) ? self::$_server['REQUEST_METHOD'] : 'GET';

        // クライアントからのヘッダ情報
        self::$_headerParam = self::getallheaders();
        
        // リモートアドレス取得
        self::$_remoteAddr = array_key_exists('REMOTE_ADDR', self::$_server) ? self::$_server['REMOTE_ADDR'] : null;
        
        // リクエストパラメータを変数に代入＆処理
        if (isset(self::$_requestParam['module'])) {
            $moduleString = trim(self::$_requestParam['module']);
            unset(self::$_requestParam['module']);
        } else {
            $moduleString = 'index';
            $controllerString = 'Index';
        }
        if (isset(self::$_requestParam['controller'])) {
            $controllerString = trim(self::$_requestParam['controller'], " /");
            unset(self::$_requestParam['controller']);
        } else {
            $controllerString = 'Index';
        }
        // OAuthクラス用にグローバル(?!)の書き換え
        if (array_key_exists('QUERY_STRING', self::$_server)) {
            parse_str(self::$_server['QUERY_STRING'], $queryArray);
            if (isset($queryArray['module'])) {
                unset($queryArray['module']);
            }
            if (isset($queryArray['controller'])) {
                unset($queryArray['controller']);
            }
            $_SERVER['QUERY_STRING'] = http_build_query($queryArray);
            self::$_server['QUERY_STRING'] = http_build_query($queryArray);
        }
        // コントローラ情報
        if (empty($moduleString)) {
            throw new NotFoundException();
        }
        self::$_calledModuleName = $moduleString;
        self::$_calledControllerName = $controllerString;
        // ユーザエージェントからdevice判定
        self::$_deviceCode = self::checkUserAgent();
    }
    
    /**
     * 相手のRemoteAddressを取得する
     * 
     * @access public
     * @param void
     * @return string
     */
    public static function getRemoteAddr()
    {
        return self::$_remoteAddr;
    }
    
    /**
     * リクエストメソッドを取得する
     * 
     * @access public
     * @param void
     * @return string
     */
    public static function getMethod()
    {
        return self::$_method;
    }

    /**
     * サーバーパラメータ($_SERVER)を取得する
     * 
     * @access public
     * @param string $key (null時は全配列)
     * @param string $default
     * @return mixed|array
     */
    public static function getServerParam($key = null, $default = null)
    {
        if (is_null($key)) {
            return self::$_server;
        } else {
            return array_key_exists($key, self::$_server) ? self::$_server[$key] : $default;
        }
    }

    /**
     * リクエストパラメータ(GET/POST)を取得する
     * 
     * @access public
     * @param string $key (null時は全配列)
     * @param string $default
     * @return mixed|array
     */
    public static function getRequestParam($key = null, $default = null)
    {
        if (is_null($key)) {
            return self::$_requestParam;
        } else {
            return array_key_exists($key, self::$_requestParam) ? self::$_requestParam[$key] : $default;
        }
    }

    /**
     * リクエストパラメータ(GET/POST)を設定する
     * 
     * @access public
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function setRequestParam($key, $value)
    {
        self::$_requestParam[$key] = $value;
    }

    /**
     * GETパラメータを取得する
     * 
     * @access public
     * @param string $key (null時は全配列)
     * @return string|array
     */
    public static function getGetParam($key = null, $default = null)
    {
        if (is_null($key)) {
            return self::$_getParam;
        } else {
            return array_key_exists($key, self::$_getParam) ? self::$_getParam[$key] : $default;
        }
    }

    /**
     * POSTパラメータを取得する
     * 
     * @access public
     * @param string $key (null時は全配列)
     * @return string|array
     */
    public static function getPostParam($key = null, $default = null)
    {
        if (is_null($key)) {
            return self::$_postParam;
        } else {
            return array_key_exists($key, self::$_postParam) ? self::$_postParam[$key] : $default;
        }
    }

    /**
     * 生のPOSTデータを取得する
     * 
     * @access public
     * @param void
     * @return string
     */
    public static function getRawPostData()
    {
        if (!self::$_rawPostData) {
            self::$_rawPostData = file_get_contents("php://input");
        }
        return self::$_rawPostData;
    }

    /**
     * リクエストヘッダパラメータを取得する
     * 
     * @access public
     * @param string $key/null時は全配列
     * @param string $default
     * @return mixed
     */
    public static function getHeaderParam($key = null, $default = null)
    {
        if (is_null($key)) {
            return self::$_headerParam;
        } else {
            return array_key_exists($key, self::$_headerParam) ? self::$_headerParam[$key] : $default;
        }
    }
    
    /**
     * リクエスト時にコールされたModule名を取得する
     * 
     * @access public
     * @param void
     * @return string
     */
    public static function getCalledModule()
    {
        return self::$_calledModuleName;
    }
    
    /**
     * リクエスト時にコールされたController名を取得する
     * 
     * @access public
     * @param void
     * @return string
     */
    public static function getController()
    {
        return self::$_calledControllerName . 'Controller';
    }
    
    /**
     * コールされたリクエストのDeviceコードを設定する
     * (アプリなどロジックによる判別時に上書きする目的)
     * 
     * @access public
     * @param, string
     * @return void
     */
    public static function setDeviceCode($code)
    {
        self::$_deviceCode = $code;
    }
    
    /**
     * Deviceコードを取得する
     * 
     * @access public
     * @param void
     * @return string
     */
    public static function getDeviceCode()
    {
        return self::$_deviceCode;
    }
    
    /**
     * ユーザIDをセットする
     * 
     * @access public
     * @param integer $userId
     * @return void
     */
    public static function setUserId($userId)
    {
        self::$_userId = $userId;
    }
    
    /**
     * ユーザIDを取得する
     * 
     * @access public
     * @param void
     * @return string
     */
    public static function getUserId()
    {
        return self::$_userId;
    }
    
    /**
     * nginxでもgetallheadersを使うために拡張
     */
    public static function getallheaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }
        
        $headers = []; 
        foreach (self::$_server as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }
    /**
     * ユーザーエージェントからデバイスを判定する
     * @return int
     */
    public static function checkUserAgent()
    {
        $rtn = self::DEVICE_UNKNOWN;
        if (!array_key_exists('HTTP_USER_AGENT', self::$_server) || !($userAgent = self::$_server['HTTP_USER_AGENT'])) {
            return $rtn;
        }
        
        // キャリアチェック
        if (strpos($userAgent,'DoCoMo') !== false) {
            // DoCoMo
            $rtn = self::DEVICE_FP_DOCOMO;
        } elseif (strpos($userAgent,'UP.Browser') !== false) {
            // au
            $rtn = self::DEVICE_FP_AU;
        } elseif ((strpos($userAgent,'SoftBank') !== false) || (strpos($userAgent, 'Vodafone') !== false) || (strpos($userAgent,'J-PHONE') !== false) || (strpos($userAgent,'SMOT') !== false)) {
            // SoftBank
            $rtn = self::DEVICE_FP_SOFTBANK;
        } elseif (strpos($userAgent,'WILLCOM') !== false) {
            // WILLCOM
            $rtn = self::DEVICE_FP_WILLCOM;
        } elseif (strpos($userAgent,'emobile') !== false) {
            // e-mobile
            $rtn = self::DEVICE_FP_EMOBILE;
        }
        
        // スマホチェック
        if (strpos($userAgent,'iPhone') !== false) {
            // iPhone
            return self::DEVICE_SP_IOS;
        } elseif (strpos($userAgent,'iPad') !== false) {
            // iPad
            return self::DEVICE_SP_IOS;
        } elseif ((strpos($userAgent,'Android') !== false) && (strpos($userAgent, 'Mobile') !== false)) {
            // Android
            return self::DEVICE_SP_ANDROID;
        } elseif (strpos($userAgent,'Android') !== false) {
            // Android(tablet)
            return self::DEVICE_SP_ANDROID;
        } elseif (strpos($userAgent,'Windows Phone') !== false) {
            // Windows Phone
            return self::DEVICE_SP_WINDOWS;
        } elseif ((strpos($userAgent,'Windows') !== false) && (strpos($userAgent,'ARM') !== false)) {
            // Windows RT
            return self::DEVICE_SP_WINDOWS;
        } elseif ($rtn != self::DEVICE_UNKNOWN) {
            // フィーチャフォン
            return $rtn;
        }
        
        return self::DEVICE_UNKNOWN;
    }
    
}
