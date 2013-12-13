<?php
/**
 * PhatePureRendererクラス
 *
 * パラメータをダンプ出力するレンダラ
 *
 * @package PhateFramework
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhatePureRenderer {
    public function __construct() {
        
    }
    
    /**
     * 描画
     * 
     * @param mixed $value
     */
    public function render($value) {
        print_r($value);
    }
}
