<?php
class Index2Controller extends CommonController
{
    public function action() {
        $post = file_get_contents('php://input');
        $data = msgpack_unpack($post);
        $this->_renderer->render($data);
    }
    
}