<?php
class LoginManager extends PhateModelBase
{
    public static function auth ($userId, $passwd)
    {
        if ($userAuthData =SampleUserAuthDataPeer::retrieveByPk($userId)) {
            if ($userAuthData->getPassword() == $passwd) {
                return $userAuthData;
            }
        }
        return false;
    }
    
    public static function makeAuthToken (SampleUserAuthDataOrm $userAuthData)
    {
        do {
            $data = $userAuthData->getUserId() . ' ' . microtime(true) . ' ' . mt_rand(0,10000);
            $key =hash('md5', $data);
        } while (PhateMemcached::get($key, 'login'));
        PhateMemcached::set($key, $userAuthData, NULL, 'login');
        return $key;
    }
    
    public static function startupUser ($password)
    {
        $dbh = PhateDB::getInstance('common_master');
        $dbh->beginTransaction();
        try{
            // userAuthData作成
            $userAuthData = new SampleUserAuthDataOrm;
            $userAuthData->setPassword($password);
            $userAuthData->setShardId(0);
            $userAuthData->setShowUserId(' ');
            $userAuthData->setTutorialState(0);
            $userAuthData->save($dbh);
            $userId = $userAuthData->getUserId();
            $shardId = ShardDBHandlerManager::createShardId($userId);
            $userAuthData->setShardId($shardId);
            $userAuthData->setShowUserId(self::createShowUserId($userId));
            $userAuthData->save($dbh);
            $userDeviceInfo = new SampleUserDeviceInfoOrm;
            $userDeviceInfo->setUserId($userId);
            $userDeviceInfo->setDeviceTypeId(PhateHttpRequest::getHeaderParam('Platform-Id'));
            $userDeviceInfo->save($dbh);
            
            $level = SampleUserLevelPeer::retrieveByPk(1);
            $dbhShard = PhateDB::getInstanceByShardId('shard_user_master', $shardId);
            $dbhShard->beginTransaction();
            try {
                $userData = new SampleUserDataOrm;
                $userData->setUserId($userId);
                $userData->setHpMax($level->getMaxHp());
                $userData->setHp($level->getMaxHp());
                $userData->save($dbhShard);
                $dbhShard->commit();
            } catch(Exception $e) {
                $dbhShard->rollBack();
                throw $e;
            }
            //処理終了
            $dbh->commit();
        } catch (Exception $e) {
            $dbh->rollback();
            throw $e;
        }
        return $userAuthData;
    }
    
    public static function createShowUserId($userId) {
        return $userId;
    }
}
