<?php
/*
 * 最低限の共通処理部
 * 
 */

// 各ディレクトリ定数宣言
define('PHATE_ROOT_DIR', realpath(dirname(__FILE__).'/../../') . DIRECTORY_SEPARATOR);
define('PHATE_BASE_DIR', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('PHATE_LIB_DIR', PHATE_ROOT_DIR . 'phate/');
define('PHATE_LIB_VENDOR_DIR', PHATE_LIB_DIR . 'vendor/');
define('PHATE_CONFIG_DIR', PHATE_ROOT_DIR . 'config/');
define('PHATE_CACHE_DIR', PHATE_ROOT_DIR . 'cache/');
define('PHATE_SOURCE_DIR', PHATE_ROOT_DIR . 'source/');
define('PHATE_MAINTENANCE_DIR', PHATE_ROOT_DIR . 'maintenance/');
define('PHATE_PROJECT_DIR', PHATE_ROOT_DIR . 'project/');

// サーバ環境取得
if (!($serverEnv = getenv(PROJECT_NAME . '_env'))) {
    if (!($serverEnv = getenv(strtoupper(PROJECT_NAME . '_ENV')))) {
        if (!($serverEnv = trim(file_get_contents(PHATE_ROOT_DIR . 'serverEnv/status.conf')))) {
            throw new Exception('Server environment file is empty');
        }
    }
}
define('SERVER_ENV', $serverEnv);

// フレームワーク基底部の読み込み
$dh = opendir(PHATE_BASE_DIR);
while (($file = readdir($dh)) !== false) {
    if (is_file(PHATE_BASE_DIR . $file) && preg_match('/(.*)\.class\.php/', $file)) {
        if ($file == basename(__FILE__)) {
            continue;
        }
        include_once PHATE_BASE_DIR . $file;
    }
}
closedir($dh);

//----------------------------------------------------------

/**
 * PhateCoreクラス
 *
 * Frameworkを実行する中心部分となります。
 * web経由での展開はdispatch、バッチの実行はdoBatchを用いてください。
 *
 * @package PhateFramework
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateCore
{
    private static $_instance;
    private static $_appName;
    private static $_isDebug;
    private static $_conf;
    private static $_includeClassList;
    
    /**
     * コンストラクタ
     * 
     * @access private
     * @param string $appName
     * @param boolean $debug1
     * @return void
     */
    private function __construct($appName, $debug)
    {
        self::$_appName = $appName;
        self::$_isDebug = $debug;
        // 実行時間の初期化
        PhateTimer::init();
        // 基礎configの読み込み
        self::$_conf = PhateCommon::parseConfigYaml(PHATE_CONFIG_DIR . $appName . '.yml');
        // debugモード上書き
        if (isset(self::$_conf['DEBUG'])) {
            self::$_isDebug = self::$_conf['DEBUG'];
        }
        if (self::$_isDebug) {
            ini_set('display_errors', 1);
            if (function_exists('xdebug_enable')) {
                ini_set('xdebug.default_enable', 1);
            }
        } else {
            ini_set('display_errors', 0);
            if (function_exists('xdebug_enable')) {
                ini_set('xdebug.default_enable', 0);
            }
        }
        // autoloaderの設定
        spl_autoload_register(array($this, 'classLoader'));
        if (file_exists(PHATE_LIB_VENDOR_DIR.'PhateVendorAutoLoader.class.php')) {
            include_once PHATE_LIB_VENDOR_DIR.'PhateVendorAutoLoader.class.php';
            if (method_exists('PhateVendorAutoLoader', 'loader')) {
                spl_autoload_register(array('PhateVendorAutoLoader', 'loader'));
            }
        }
        // ロガーを初期化
        PhateLogger::init();
        // エラーハンドルにロガーをセット
        set_error_handler(array('PhateLogger', 'fatal'));
        // プロジェクトroot定数宣言
        define('PROJECT_ROOT', PHATE_PROJECT_DIR . $appName . DIRECTORY_SEPARATOR);
    }
    
    /**
     * singleton取得
     * 
     * @access public
     * @param string $appName
     * @param boolean $debug
     * @return PhateCore
     */
    public static function getInstance($appName=NULL, $debug=false)
    {
        if (!isset(self::$_instance)) {
            if (is_null($appName)) {
                throw new Exception('no appName');
            }
            self::$_instance = new PhateCore($appName, $debug);
        }
        return self::$_instance;
    }
    
    /**
     * オートロード対象リスト取得
     * 
     * @access private
     * @param void
     * @return array
     */
    private function getIncludeClassList()
    {
        // 取得済み確認
        if (isset(self::$_includeClassList)) {
            return self::$_includeClassList;
        }
        // キャッシュ確認
        $cacheFileName = PHATE_CACHE_DIR . self::$_appName . '_autoload_' . SERVER_ENV . '.cache';
        if(file_exists($cacheFileName) && !$this->isDebug()) {
            self::$_includeClassList = msgpack_unserialize(file_get_contents($cacheFileName));
            return self::$_includeClassList;
        }
        // オートロードロジック展開
        // 対象ディレクトリ生成
        // フレームワークライブラリ
        $dirArray = array(PHATE_BASE_DIR . 'lib');
        // config設定ディレクトリ
        if (isset(self::$_conf['AUTOLOAD']) && is_array(self::$_conf['AUTOLOAD'])) {
            $dirArray = array_merge($dirArray, self::$_conf['AUTOLOAD']);
        }
        // ディレクトリ展開
        $fileNames = array();
        foreach ($dirArray as $line) {
            if (file_exists($line)) {
                $fileNames = array_merge($fileNames, PhateCommon::getFileNameRecursive($line));
            }
        }
        $rtn = array();
        foreach ($fileNames as $value) {
            if (preg_match('/^.*\.class\.php$/', $value)) {
                $rtn[substr(basename($value),0,-10)] = $value;
            }
        }
        // キャッシュ保存
        file_put_contents($cacheFileName, msgpack_serialize($rtn), LOCK_EX);
        if (substr(sprintf('%o', fileperms($cacheFileName)), -4) !=='0777') {
            chmod($cacheFileName, 0777);
        }
        
        self::$_includeClassList = $rtn;
        return self::$_includeClassList;
    }
    
    /**
     * オートローダ用メソッド
     * 
     * @access public
     * @param className
     * @return void
     */
    public function classLoader($className)
    {
        $classList = $this->getIncludeClassList();
        if (isset($classList[$className])) {
            include_once $classList[$className];
        }
    }
    /**
     * アプリ名取得
     * 
     * @access public
     * @param void
     * @return string
     */
    public static function getAppName()
    {
        return self::$_appName;
    }
    
    /**
     * デバッグモード取得
     * 
     * @access public
     * @param void
     * @return boolean
     */
    public static function isDebug()
    {
        return self::$_isDebug;
    }
    
    /**
     * メイン設定取得
     * 
     * @access public
     * @param void
     * @return array
     */
    public static function getConfigure()
    {
        return self::$_conf;
    }
    
    public static function getBaseUri()
    {
        return (isset(self::$_conf['BASE_URI']) && self::$_conf['BASE_URI']) ? self::$_conf['BASE_URI'] : NULL;
    }
    
    /**
     * HTTPリクエスト展開実行
     * 
     * @access public
     * @param void
     * @return void
     */
    public function dispatch()
    {
        // httpリクエストの初期化・整理
        PhateHttpRequest::initialize();
        try {
            // InputFilter
            if (isset(self::$_conf['FILTER']['INPUT']) && is_array(self::$_conf['FILTER']['INPUT'])) {
                foreach (self::$_conf['FILTER']['INPUT'] as $filter) {
                    $fileName = PROJECT_ROOT . 'filters/' . $filter . '.class.php';
                    if (file_exists($fileName)) {
                        include $fileName;
                        $filterClass = new $filter;
                        $filterClass->execute();
                    }
                }
            }
            ob_start();
            // Controller実行
            include PROJECT_ROOT . 'controllers/CommonController.class.php';
            $controllerFile = PROJECT_ROOT . 'controllers/' . PhateHttpRequest::getCalledModule() . DIRECTORY_SEPARATOR . PhateHttpRequest::getController() . '.class.php';
            if (!file_exists($controllerFile)) {
                throw new Phate404Exception('controller file not found');
            }
            include $controllerFile;
            $className = PhateHttpRequest::getController();
            $controller = new $className;
            PhateControllerExecuter::execute($controller);
            $content = ob_get_contents();
            ob_end_clean();
            // OutputFilter
            if (isset(self::$_conf['FILTER']['OUTPUT']) && is_array(self::$_conf['FILTER']['OUTPUT'])) {
                foreach (self::$_conf['FILTER']['OUTPUT'] as $filter) {
                    $fileName = PROJECT_ROOT . 'filters/' . $filter . '.class.php';
                    if (file_exists($fileName)) {
                        include $fileName;
                        $filterClass = new $filter;
                        $filterClass->execute($content);
                    }
                }
            }
            // 一応Content-Lengthの設定もしておく
            PhateHttpResponseHeader::setResponseHeader('Content-Length', strlen($content));
            // レスポンスヘッダ設定
            PhateHttpResponseHeader::sendResponseHeader();
            // 画面出力
            echo $content;
            return;
        } catch (Phate404Exception $e) {
            ob_end_clean();
            PhateHttpResponseHeader::setHttpStatus(PhateHttpResponseHeader::HTTP_NOT_FOUND);
            PhateHttpResponseHeader::sendResponseHeader();
            if(self::$_isDebug) {
                var_dump($e);
            }
            exit();
        } catch (PhateKillException $e) {
            ob_end_flush();
            exit();
        } catch (PhateRedirectException $e) {
            ob_end_clean();
            try {
                PhateHttpResponseHeader::sendResponseHeader();
            } catch (PhateKillException $e) {
                exit();
            } catch (Exception $e) {
                PhateHttpResponseHeader::setHttpStatus(PhateHttpResponseHeader::HTTP_INTERNAL_SERVER_ERROR);
                PhateHttpResponseHeader::sendResponseHeader();
                var_dump($e);
                exit();
            }
        } catch (Exception $e) {
            $body = ob_get_contents();
            ob_end_clean();
            if (file_exists(PROJECT_ROOT . 'exception/ThrownException.class.php')) {
                include PROJECT_ROOT . 'exception/ThrownException.class.php';
                $thrownExceptionClass = new ThrownException;
                $thrownExceptionClass->execute($e);
                exit();
            }
            if ($e instanceof PhateUnauthorizedException) {
                PhateHttpResponseHeader::setHttpStatus(PhateHttpResponseHeader::HTTP_UNAUTHORIZED);
            } else {
                PhateHttpResponseHeader::setHttpStatus(PhateHttpResponseHeader::HTTP_INTERNAL_SERVER_ERROR);
            }
            PhateHttpResponseHeader::sendResponseHeader();
            if (self::$_isDebug) {
                echo $body;
                var_dump($e);
            }
            exit();
        }
        return;
    }

    /**
     * バッチ実行
     * 
     * @access public
     * @param string $classname
     * @return void
     */
    public function doBatch($classname)
    {
        try {
            // batch実行
            include PROJECT_ROOT . 'batches/CommonBatch.class.php';
            $batchFile = PROJECT_ROOT . 'batches/' . $classname . '.class.php';
            if (!file_exists($batchFile)) {
                throw new Phate404Exception('batch file not found');
            }
            include $batchFile;
            $controller = new $classname;
            $controller->initialize();
            $controller->execute();
            return;
        } catch (PhateKillException $e) {
            exit();
        } catch (Exception $e) {
            PhateLogger::error('batch throw exception');
            ob_start();
            var_dump($e);
            $dump = ob_get_contents();
            ob_end_clean();
            PhateLogger::error("exception dump : \n" . $dump);
            if (self::$_isDebug) {
                echo $dump;
            }
            exit();
        }
        return;
    }
    
    /**
     * デストラクタ
     * 
     */
    public function __destruct()
    {
        if (class_exists('PhateMemcached')) {
            PhateMemcached::disconnect();
        }
        if (class_exists('PhateDB')) {
            PhateDB::disconnect();
        }
    }
}
