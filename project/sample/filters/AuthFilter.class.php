<?php
class AuthFilter extends PhateInputFilterBase
{
    public function execute() {
        // 認証除外モジュール
        $excludeModule = array('login', 'index');
        if (in_array(PhateHttpRequest::getCalledModule(), $excludeModule)) {
            return;
        }
        // Authトークン取得
        if (!(PhateHttpRequest::getHeaderParam('Auth-Token'))) {
            throw new PhateUnauthorizedException('Login Fail(Header Parameter)');
            exit();
        }
        $key = trim(PhateHttpRequest::getHeaderParam('Auth-Token'));
        if (($cache = PhateMemcached::get($key, 'login'))) {
            if ($cache->getUserId() == PhateHttpRequest::getHeaderParam('User-Id')) {
                PhateMemcached::set($key, $cache, NULL, 'login');
                PhateHttpRequest::setUserId($cache->getUserId());
                LoginUserInfo::setUserAuthData($cache);
                return;
            }
        }
        throw new PhateUnauthorizedException('Login Fail(Token)');
        exit();
    }
}
