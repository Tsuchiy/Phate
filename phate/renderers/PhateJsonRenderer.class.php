<?php
/**
 * PhateJsonRendererクラス
 *
 * json_encodeしたtextの出力を行うレンダラ
 *
 * @package PhateFramework
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateJsonRenderer
{
    public function __construct()
    {
    }
    /**
     * 描画
     * 
     * @param mixed $value
     */
    public function render(array $value)
    {
        if (!($rtn = json_encode($value))) {
            throw new PhateCommonException('cant json encode parameter');
        }
        PhateHttpResponseHeader::setContentType('application/json');
        echo $rtn;
    }
}
