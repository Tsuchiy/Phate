<?php
class GetResController extends CommonController
{
    public function action() {
        $id = PhateHttpRequest::getRequestParam('id');
        $url = "http://www.google.co.jp/logos/doodles/2013/doodle-4-google-2013-japan-winner-6433880336760832.2-hp.png";
        PhateHttpResponseHeader::setRedirectUrl($url);
        throw new PhateRedirectException;
    }
    
}