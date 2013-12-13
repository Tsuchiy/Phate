<?php
class LoginUserInfoManager extends PhateModelBase
{
    protected static $_userAuthData;
    
    public static function setUserAuthData(SampleUserAuthDataOrm $orm)
    {
        self::$_userAuthData = $orm;
    }
    
    public static function getUserAuthData()
    {
        return self::$_userAuthData;
    }
    
    public static function getUserId()
    {
        if (self::$_userAuthData instanceof SampleUserAuthDataOrm) {
            return self::$_userAuthData->getUserId();
        }
        return false;
    }
    
}