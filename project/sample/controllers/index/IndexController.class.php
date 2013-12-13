<?php
class IndexController extends CommonController
{
    public function action() {
        /*
        $a = $b['ccc'];
        $rtn = PhateHttpRequest::getHeaderParam();
        PhateFluentd::post('debug.test', array('a'=>'b'));
        $this->_renderer->render('ok');
         * 
         */
        $filename = PHATE_ROOT_DIR . 'logs/hoge.bin';
        $data = msgpack_pack(PhateHttpRequest::getHeaderParam());
        file_put_contents($filename, $data);
        $this->_renderer->render($filename);
    }
    
}