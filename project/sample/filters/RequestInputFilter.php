<?php
class RequestInputFilter extends PhateInputFilterBase
{
    public function execute() {
        $request = file_get_contents("php://input");
        if ($request) {
            if (is_array(@msgpack_unserialize($request))) {
                $arr = msgpack_unserialize($request);
                foreach ($arr as $k => $v) {
                    DwHttpRequest::setRequestParam($k, $v);
                }
            }
        }
        
        return;
    }
}
