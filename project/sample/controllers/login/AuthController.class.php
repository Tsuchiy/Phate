<?php
class AuthController extends CommonController
{
    public function action()
    {
        $userId = PhateHttpRequest::getRequestParam('userId');
        $passwd = PhateHttpRequest::getRequestParam('password');
        if (!($userAuth = LoginManager::auth($userId, $passwd))) {
            throw new PhateUnauthorizedException('wrong password');
        }
        $token = LoginManager::makeAuthToken($userAuth);
        $rtn = array(
            'userId' => $userId,
            'authToken' => $token,
            'todayFirst' => PhateTimer::getApplicationDate(PhateTimer::getTimeStamp($userAuth->getLastLoginDate())) < PhateTimer::getApplicationDate(),
        );
        $this->_renderer->render($rtn);
    }
    
    
}