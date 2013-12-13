<?php
class UserController extends CommonController
{
    public function action() {
        $userId = 1;

        $dbh = PhateDB::getInstance('common_master');
        
        $user = SampleUserAuthDataPeer::retrieveByPk($userId);
        $user->setLastLoginDate(PhateTimer::getDateTime());
        $user->save($dbh);
        
        $userData = SampleUserDataPeer::retrieveByPk($user->getUserId(), $user->getShardId());
        
        $this->_renderer->render($userData->toArray());
    }
    
}