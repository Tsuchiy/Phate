<?php
/**
 * PhateTwigRendererクラス
 *
 * Twigを使ってレンダリングする
 * http://twig.sensiolabs.org/
 *
 * @package PhateFramework
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/11/01
 **/
class PhateTwigRenderer {
    
    protected $_header = '';
    protected $_footer = '';

    protected $_cacheDir = '/tmp';
            
    public function __construct() {
        require PHATE_LIB_VENDOR_DIR . 'Twig/Autoloader.php';
        Twig_Autoloader::register();
        $sysconfig = PhateCore::getConfigure();
        if (isset($sysconfig['TEMPLATE']['cache_dir'])) {
            $this->_cacheDir = $sysconfig['TEMPLATE']['cache_dir'];
        }
    }
    
    protected function _getRenderingResult($templateFile, $param) {
        $path = dirname($templateFile);
        $fileName = basename($templateFile);
        
        $loader = new Twig_Loader_Filesystem($path);
        $twig = new Twig_Environment($loader, array(
            'debug' => PhateCore::isDebug(),
            'cache' => $this->_cacheDir,
            ));
        
        return $twig->render($fileName, $param);
    }
    
    public function setHeader($templateFile, $param) {
        $this->_header = $this->_getRenderingResult($templateFile, $param);
    }
    
    public function setFooter($templateFile, $param) {
        $this->_footer = $this->_getRenderingResult($templateFile, $param);
    }
    
    public function render($templateFile, $param) {
        $body = $this->_getRenderingResult($templateFile, $param);
        
        echo $this->_header . $body . $this->_footer;
    }
    
    public function compile($templateFile, $param) {
        return $this->_getRenderingResult($templateFile, $param);
    }
}
