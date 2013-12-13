<?php
/**
 * PhateMsgPackRendererクラス
 *
 * MsgPackでシリアライズしたバイナリの出力を行うレンダラ
 *
 * @package PhateFramework
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateMsgPackRenderer {
    public function __construct() {
    }
    /**
     * 描画
     * 
     * @param mixed $value
     */
    public function render($value) {
        PhateHttpResponseHeader::setContentType('application/octet-stream');
        PhateHttpResponseHeader::sendResponseHeader();
        echo msgpack_serialize($value);
    }
}
