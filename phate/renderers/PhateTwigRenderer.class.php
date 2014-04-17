<?php
/**
 * PhateTwigRendererクラス
 *
 * Twigを使ってレンダリングする
 * twigはこちら → http://twig.sensiolabs.org/
 *
 * @package PhateFramework
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/11/01
 **/
class PhateTwigRenderer {
    
    protected $_header = '';
    protected $_footer = '';

    protected $_loader;
    protected $_cacheDir = '/tmp';
            
    /**
     * コンストラクタ
     */
    public function __construct()
    {
        if (!class_exists('Twig_Autoloader')) {
            require PHATE_LIB_VENDOR_DIR . 'Twig/Autoloader.php';
            Twig_Autoloader::register();
        }
        $sysconfig = PhateCore::getConfigure();
        if (PhateCore::isDebug()) {
            $this->_cacheDir = false;
        } elseif (isset($sysconfig['TEMPLATE']['cache_dir'])) {
            $this->_cacheDir = $sysconfig['TEMPLATE']['cache_dir'];
        }
    }
    
    /**
     * Twig_Loader_Filesystemを取得する
     * @param string $path
     * @return Twig_Loader_Filesystem
     * @throws PhateCommonException
     */
    public function getLoader($path = null)
    {
        if (!$this->_loader) {
            if (!$path) {
                throw new PhateCommonException('Twig Renderer Error');
            }
            $this->_loader = new Twig_Loader_Filesystem($path);
        }
        return $this->_loader;
    }
    
    /**
     * 描画結果の文字列を返す
     * @param string $templateFile
     * @param array $param
     * @return string
     */
    protected function _getRenderingResult($templateFile, $param)
    {
        if (!($loader = $this->_loader)) {
            $loader = $this->getLoader(dirname($templateFile));
        }
        $fileName = basename($templateFile);
        
        $twig = new Twig_Environment($loader, array(
            'debug' => PhateCore::isDebug(),
            'cache' => $this->_cacheDir,
            ));
        
        return $twig->render($fileName, $param);
    }
    
    /**
     * 共通ヘッダファイルを設定する
     * @param string $templateFile
     * @param array $param
     */
    public function setHeader($templateFile, $param)
    {
        $this->_header = $this->_getRenderingResult($templateFile, $param);
    }
    
    /**
     * 共通フッタファイルを設定する
     * @param string $templateFile
     * @param array $param
     */
    public function setFooter($templateFile, $param)
    {
        $this->_footer = $this->_getRenderingResult($templateFile, $param);
    }
    
    /**
     * ヘッダ・フッタ付きでテンプレートの描画を行う
     * @param string $templateFile
     * @param array $param
     */
    public function render($templateFile, $param)
    {
        $body = $this->_getRenderingResult($templateFile, $param);
        
        echo $this->_header . $body . $this->_footer;
    }
    
    /**
     * テンプレートの描画結果文字列を返す
     * @param string $templateFile
     * @param array $param
     * @return string
     */
    public function compile($templateFile, $param)
    {
        return $this->_getRenderingResult($templateFile, $param);
    }
}
