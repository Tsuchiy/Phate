<?php
namespace Phate;
/*
 * 最低限の共通処理部
 * 
 */
// フレームワーク情報
define('PHATE_FRAMEWORK_VERSION', 'v2.0rc');


// 各ディレクトリ定数宣言
define('PHATE_ROOT_DIR', realpath(dirname(__FILE__).'/../../') . DIRECTORY_SEPARATOR);
define('PHATE_FRAMEWORK_DIR', realpath(dirname(__FILE__).'/../') . DIRECTORY_SEPARATOR);
define('PHATE_BASE_DIR', PHATE_FRAMEWORK_DIR . 'base/');
define('PHATE_LIB_VENDOR_DIR', PHATE_FRAMEWORK_DIR . 'vendor/');
define('PHATE_CONFIG_DIR', PHATE_ROOT_DIR . 'configs/');
define('PHATE_CACHE_DIR', PHATE_ROOT_DIR . 'cache/');
define('PHATE_PROJECT_DIR', PHATE_ROOT_DIR . 'projects/');

// サーバ環境取得
if (!defined('SERVER_ENV')) {
    if (!($serverEnv = getenv('SERVER_ENV'))) {
        if (!($serverEnv = trim(file_get_contents(PHATE_ROOT_DIR . 'serverEnv/status.conf')))) {
            throw new Exception('Server environment file is empty');
        }
    }
    define('SERVER_ENV', $serverEnv);
}

// フレームワーク基底部の読み込み
$dh = opendir(PHATE_BASE_DIR);
while (($file = readdir($dh)) !== false) {
    if (is_file(PHATE_BASE_DIR . $file) && preg_match('/^.*\.class\.php$/', $file)) {
        if ($file == basename(__FILE__)) {
            continue;
        }
        include PHATE_BASE_DIR . $file;
    }
}
closedir($dh);

//----------------------------------------------------------

/**
 * Coreクラス
 *
 * Frameworkを実行する中心部分となります。
 * web経由での展開はdispatch、バッチの実行はdoBatchを用いてください。
 *
 * @package PhateFramework
 * @access  public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class Core
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
     * @param boolean $isDebug
     * @return void
     */
    private function __construct($appName, $isDebug)
    {
        // プロジェクト定数宣言
        define('PROJECT_ROOT', PHATE_PROJECT_DIR . $appName . DIRECTORY_SEPARATOR);
        define('PROJECT_MODELS_DIR', PROJECT_ROOT .'models' . DIRECTORY_SEPARATOR);
        define('PROJECT_LIBS_DIR', PROJECT_ROOT .'libs' . DIRECTORY_SEPARATOR);
        define('PROJECT_DATABASE_DIR', PROJECT_ROOT .'database' . DIRECTORY_SEPARATOR);
        define('PROJECT_CONTROLLERS_DIR', PROJECT_ROOT .'controllers' . DIRECTORY_SEPARATOR);
        define('PROJECT_VIEWS_DIR', PROJECT_ROOT .'views' . DIRECTORY_SEPARATOR);
        // 初期値
        self::$_appName = $appName;
        self::$_isDebug = $isDebug;
        // 基礎configの読み込み
        self::$_conf = Common::parseConfigYaml(PHATE_CONFIG_DIR . $appName . '.yml');
        // 実行時間の初期化
        Timer::init();
        // debugモード上書き
        if (array_key_exists('debug', self::$_conf)) {
            self::$_isDebug = self::$_conf['debug'];
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
        // UTF-8限定にしておく
        mb_language("Ja");
        mb_internal_encoding('UTF-8');
        // autoloaderの設定
        spl_autoload_register([$this, 'classLoader']);
        if (file_exists(PHATE_LIB_VENDOR_DIR.'PhateVendorAutoLoader.class.php')) {
            include PHATE_LIB_VENDOR_DIR.'PhateVendorAutoLoader.class.php';
            if (method_exists('\Phate\VendorAutoLoader', 'registLoader')) {
                spl_autoload_register(['\Phate\VendorAutoLoader', 'registLoader']);
            }
        }
        // ロガーを初期化
        Logger::init();
        // エラーハンドルにロガーをセット
        set_error_handler(['\Phate\Logger', 'fatal']);
    }
    
    /**
     * singleton取得
     * 
     * @access public
     * @param string $appName
     * @param boolean $isDebug
     * @return Core
     */
    public static function getInstance($appName=NULL, $isDebug=false)
    {
        if (!isset(self::$_instance)) {
            if (is_null($appName)) {
                throw new Exception('no appName');
            }
            self::$_instance = new Core($appName, $isDebug);
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
        if (self::$_includeClassList) {
            return self::$_includeClassList;
        }
        // キャッシュ確認
        if (function_exists('apc_store') && !self::$_isDebug) {
            $apcCacheName = self::$_appName . '_autoload_' . SERVER_ENV . '.cache';
            if ($rtn = apc_fetch($apcCacheName)) {
                self::$_includeClassList = $rtn;
                return self::$_includeClassList;
            }
        }
        $cacheFileName = PHATE_CACHE_DIR . self::$_appName . '_autoload_' . SERVER_ENV . '.cache';
        if (file_exists($cacheFileName) && !self::$_isDebug) {
            self::$_includeClassList = Common::unserialize(file_get_contents($cacheFileName));
            if (function_exists('apc_store')) {
                apc_store($apcCacheName, self::$_includeClassList, 0);
            }
            return self::$_includeClassList;
        }
        
        $rtn = [
            'Phate' => [],
            PROJECT_NAME => [],
        ];
        // オートロードロジック展開
        // フレームワークライブラリ
        // 対象ディレクトリ生成
        // ディレクトリ展開
        $fileNames = array_merge(
                [], 
                Common::getFileNameRecursive(PHATE_BASE_DIR . 'lib'),
                Common::getFileNameRecursive(PHATE_FRAMEWORK_DIR . 'renderers')
                );
        // 配列保存
        foreach ($fileNames as $value) {
            if (preg_match('/^.*\.class\.php$/', $value)) {
                $rtn['Phate'][substr(substr(basename($value),0,-10),5)] = $value;
            }
        }
        // プロジェクトデータベースライブラリ
        // 対象ディレクトリ生成
        // config設定ディレクトリ
        $dirArray = [PROJECT_MODELS_DIR, PROJECT_LIBS_DIR, PROJECT_DATABASE_DIR];
        if (array_key_exists('autoload', self::$_conf) && is_array(self::$_conf['autoload'])) {
            $dirArray = array_merge($dirArray, self::$_conf['autoload']);
        }
        // ディレクトリ展開
        $fileNames = [];
        foreach ($dirArray as $line) {
            if (file_exists($line)) {
                $fileNames = array_merge($fileNames, Common::getFileNameRecursive($line));
            }
        }
        foreach ($fileNames as $value) {
            if (preg_match('/^.*\.class\.php$/', $value)) {
                $rtn[PROJECT_NAME][substr(basename($value),0,-10)] = $value;
            }
        }
        // キャッシュ保存
        file_put_contents($cacheFileName, Common::serialize($rtn), LOCK_EX);
        if (substr(sprintf('%o', fileperms($cacheFileName)), -4) !=='0777') {
            chmod($cacheFileName, 0777);
        }
        if (function_exists('apc_store') && !self::isDebug()) {
            apc_store($apcCacheName, $rtn, 0);
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
        // namespace対応
        $names = [];
        if (preg_match('/^(.*)\\\\(.+)$/', $className, $names)) {
            if ($names[1] == '\\Phate' || $names[1] == 'Phate') {
                if (array_key_exists($names[2], $classList['Phate'])) {
                    include_once $classList['Phate'][$names[2]];
                    return;
                }
            }
            if ($names[1] == '\\' . PROJECT_NAME || $names[1] == PROJECT_NAME) {
                if (array_key_exists($names[2], $classList[PROJECT_NAME])) {
                    include_once $classList[PROJECT_NAME][$names[2]];
                    return;
                }
            }
        }
    }
    
    /**
     * バージョン取得
     * 
     * @access public
     * @param void
     * @return string
     */
    public static function getVersion()
    {
        return PHATE_FRAMEWORK_VERSION;
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
    public static function getConfigure($key = NULL)
    {
        if (is_null($key)) {
            return self::$_conf;
        }
        if (array_key_exists($key, self::$_conf)) {
            return self::$_conf[$key];
        } else {
            return NULL;
        }
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
        Request::init();
        // 対象の存在確認
        $controllerFile = PROJECT_ROOT . 'controllers/' . Request::getCalledModule() . DIRECTORY_SEPARATOR . Request::getController() . '.class.php';
        if (!file_exists($controllerFile)) {
            Response::setHttpStatus(Response::HTTP_NOT_FOUND);
            Response::sendHeader();
            exit();
        }
        try {
            // load filter config
            $beforeFilters = [];
            $afterFilters = [];
            if (array_key_exists('filter_config_file', self::$_conf)) {
                $filterConfig = Common::parseConfigYaml(PHATE_CONFIG_DIR . self::$_conf['filter_config_file']);
                if (array_key_exists('before', $filterConfig) && is_array($filterConfig['before'])) {
                    $beforeFilters = $filterConfig['before'];
                }
                if (array_key_exists('after', $filterConfig) && is_array($filterConfig['after'])) {
                    $afterFilters = $filterConfig['after'];
                }
            }
            // beforeFilter
            if ($beforeFilters) {
                foreach ($beforeFilters as $filter) {
                    $fileName = PROJECT_ROOT . 'filters/' . $filter . '.class.php';
                    if (file_exists($fileName)) {
                        include $fileName;
                        $filterName = '\\' . PROJECT_NAME . '\\' . $filter;
                        $filterClass = new $filterName;
                        $filterClass->execute();
                    }
                }
            }
            ob_start();
            // Controller実行
            if (!file_exists(PROJECT_ROOT . 'controllers/CommonController.class.php')) {
                throw new CommonException('CommonController file not found');
            }
            require PROJECT_ROOT . 'controllers/CommonController.class.php';
            require $controllerFile;
            $className = '\\' . PROJECT_NAME . '\\' . Request::getController();
            $controller = new $className;
            ControllerExecuter::execute($controller);
            $content = ob_get_contents();
            ob_end_clean();
            ob_start();
            // afterFilter
            if ($afterFilters) {
                foreach ($afterFilters as $filter) {
                    $fileName = PROJECT_ROOT . 'filters/' . $filter . '.class.php';
                    if (file_exists($fileName)) {
                        include $fileName;
                        $filterName = '\\' . PROJECT_NAME . '\\' . $filter;
                        $filterClass = new $filterName;
                        $filterClass->execute($content);
                    }
                }
            }
        } catch (NotFoundException $e) {
            ob_end_clean();
            Response::setHttpStatus(Response::HTTP_NOT_FOUND);
            Response::sendHeader();
            if (self::$_isDebug) {
                var_dump($e);
            }
            exit();
        } catch (KillException $e) {
            ob_end_flush();
            exit();
        } catch (RedirectException $e) {
            ob_end_clean();
            try {
                Response::sendHeader();
            } catch (KillException $e) {
                exit();
            } catch (\Exception $e) {
                Response::setHttpStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
                Response::sendHeader();
                var_dump($e);
                exit();
            }
        } catch (\Exception $e) {
            $body = ob_get_contents();
            ob_end_clean();
            if (file_exists(PROJECT_ROOT . 'exception/ThrownException.class.php')) {
                include PROJECT_ROOT . 'exception/ThrownException.class.php';
                $className = '\\' . PROJECT_NAME . '\\ThrownException';
                $thrownExceptionClass = new $className;
                $thrownExceptionClass->execute($e);
                exit();
            }
            if ($e instanceof UnauthorizedException) {
                Response::setHttpStatus(Response::HTTP_UNAUTHORIZED);
            } else {
                Response::setHttpStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            Response::sendHeader();
            if (self::$_isDebug) {
                echo $body;
                var_dump($e);
            }
            exit();
        }
        // 一応Content-Lengthの設定もしておく
        Response::setHeader('Content-Length', strlen($content));
        // レスポンスヘッダ設定
        Response::sendHeader();
        // 画面出力
        echo $content;
        ob_end_flush();
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
                throw new NotFoundException('batch file not found');
            }
            include $batchFile;
            $classNameWithSpace = '\\' . PROJECT_NAME . '\\' . $classname;
            $controller = new $classNameWithSpace;
            $controller->initialize();
            $controller->execute();
            return;
        } catch (KillException $e) {
            exit();
        } catch (Exception $e) {
            Logger::error('batch throw exception');
            ob_start();
            var_dump($e);
            $dump = ob_get_contents();
            ob_end_clean();
            Logger::error("exception dump : \n" . $dump);
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
        if (class_exists('\Phate\Redis')) {
            Redis::disconnect();
        }
        if (class_exists('\Phate\Memcached')) {
            Memcached::disconnect();
        }
        if (class_exists('\Phate\DB')) {
            DB::disconnect();
        }
    }
}
