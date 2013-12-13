<?php
class ResendOutputFilter extends PhateOutputFilterBase
{
    public function execute(&$contents) {
        $headers = PhateHttpRequest::getHeaderParam();
        if (PhateHttpRequest::getHeaderParam('User-Id') && PhateHttpRequest::getHeaderParam('Request-Id')) {
            $key = PhateHttpRequest::getHeaderParam('User-Id') . '_' . PhateHttpRequest::getHeaderParam('Request-Id');
            PhateMemcached::set($key, $contents, 300, 'resend');
        }
        return;
    }
}
