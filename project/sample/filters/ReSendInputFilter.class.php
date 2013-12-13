<?php
class ResendInputFilter extends PhateInputFilterBase
{
    public function execute() {
        if (PhateHttpRequest::getHeaderParam('User-Id') && PhateHttpRequest::getHeaderParam('Request-Id')) {
            $key = PhateHttpRequest::getHeaderParam('User-Id') . '_' . PhateHttpRequest::getHeaderParam('Request-Id');
            if ($cache = PhateMemcached::get($key, 'resend')) {
                echo $cache;
                throw new PhateKillException();
            }
        }
        return;
    }
}
