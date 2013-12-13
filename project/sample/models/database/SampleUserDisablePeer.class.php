<?php
/**
 * SampleUserDisablePeerクラス
 *
 * user_disable_mのO-RMapper取り扱い用クラス(ReadOnly with memcache)
 *
 * @access public
 **/
class SampleUserDisablePeer
{
    public static function retrieveByPk($userId)
    {
        $memcacheKey = __CLASS__ . ':' . $userId;
        if ($res = PhateMemcached::get($memcacheKey, 'api')) {
            return $res;
        }
        $dbh = PhateDB::getInstance('common_slave');
        $params = array($userId);
        $sql = 'SELECT * FROM user_disable_m WHERE user_id = ? ';
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleUserDisableOrm();
        $obj->hydrate($row);
        PhateMemcached::set($memcacheKey, $obj, 0, 'api');
        return $obj;
    }
}