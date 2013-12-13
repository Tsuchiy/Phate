<?php
class StartupController extends CommonController {

    public function action()
    {
        $password = PhateHttpRequest::getRequestParam('password');
        $userAuthData = LoginManager::startupUser($password);
        $token = LoginManager::makeAuthToken($userAuthData);
        
        $rtn = array(
            'userId' => $userAuthData->getUserId(),
            'authToken' => $token,
        );
        $this->_renderer->render($rtn);
    }

    public function validate()
    {
        $validator = PhateValidator::getInstance();
        $validator->setValidator('password', 'noblank');
        return $validator->execute();
    }
    
}
