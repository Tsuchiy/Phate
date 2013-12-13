<?php
/**
 * PhateSwfRendererクラス
 *
 * バイナリ文字列を受け取り、swfとしてのヘッダを付けて出力する
 * 未実装
 *
 * @package PhateFramework
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateSwfRenderer {
    public function __construct() {
    }
    
    public function render($filename, $value) {
        PhateHttpResponseHeader::setContentType('Content-type: application/x-shockwave-flash');
        $output = $value;
        /*
         * いろいろswfを読み込んだり
         * パラメータを書き換えたりするのでしょうなぁ
         */
        echo $output;
    }
}
