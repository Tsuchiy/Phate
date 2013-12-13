<?php
/**
 * SampleLbonusPointPeerクラス
 *
 * lbonus_point_mのO-RMapper取り扱い用クラス(ReadOnly with memcache)
 *
 * @access public
 **/
class SampleLbonusPointPeer
{
    public static function retrieveByPk($id)
    {
        $memcacheKey = __CLASS__ . ':' . $id;
        if ($res = PhateMemcached::get($memcacheKey, 'api')) {
            return $res;
        }
        $dbh = PhateDB::getInstance('common_slave');
        $params = array($id);
        $sql = 'SELECT * FROM lbonus_point_m WHERE id = ? ';
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleLbonusPointOrm();
        $obj->hydrate($row);
        PhateMemcached::set($memcacheKey, $obj, 0, 'api');
        return $obj;
    }
    
    public static function getNowLbonusPoint()
    {
        $memcacheKey = __CLASS__ . ':NOW';
        if ($res = PhateMemcached::get($memcacheKey, 'api')) {
            if (($res->getFromDate() <= PhateTimer::getDateTime()) && ($res->getToDate() >= PhateTimer::getDateTime())) {
                return $res;
            }
            PhateMemcached::delete($memcacheKey, 'api');
        }
        $dbh = PhateDB::getInstance('common_slave');
        $params = array(':NOW' => PhateTimer::getDateTime());
        $sql = 'SELECT * FROM lbonus_point_m WHERE from_date <= :NOW AND to_date >= :NOW';
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleLbonusPointOrm;
        $obj->hydrate($row);
        PhateMemcached::set($memcacheKey, $obj, 0, 'api');
        return $obj;
    }
}