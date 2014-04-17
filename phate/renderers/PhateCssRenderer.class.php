<?php
/**
 * PhateCssRendererクラス
 *
 * CSSのヘッダを付け描画をするレンダラ
 *
 * @package PhateFramework
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateCssRenderer
{
    public function __construct()
    {
    }
    
    /**
     * 描画
     * 
     * @param mixed $value
     */
    public function render($value)
    {
        PhateHttpResponseHeader::setContentType('text/css');
        print_r($value);
    }
}
