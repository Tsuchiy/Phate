<?php
class IndexController extends CommonController
{
    public function action() {
        $rtn = "Hello world";
        $renderer = new PhatePureRenderer();
        $renderer->render($rtn);
    }
    
}