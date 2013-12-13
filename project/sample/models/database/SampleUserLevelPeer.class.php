<?php
/**
 * SampleUserLevelPeerクラス
 *
 * user_level_mのO-RMapper取り扱い用クラス(ReadOnly with memcache)
 *
 * @access public
 **/
class SampleUserLevelPeer
{
    public static function retrieveByPk($level)
    {
        $memcacheKey = __CLASS__ . ':' . $level;
        if ($res = PhateMemcached::get($memcacheKey, 'api')) {
            return $res;
        }
        $dbh = PhateDB::getInstance('common_slave');
        $params = array($level);
        $sql = 'SELECT * FROM user_level_m WHERE level = ? ';
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleUserLevelOrm();
        $obj->hydrate($row);
        PhateMemcached::set($memcacheKey, $obj, 0, 'api');
        return $obj;
    }
}