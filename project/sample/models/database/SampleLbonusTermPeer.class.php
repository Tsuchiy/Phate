<?php
/**
 * SampleLbonusTermPeerクラス
 *
 * lbonus_term_mのO-RMapper取り扱い用クラス(ReadOnly with memcache)
 *
 * @access public
 **/
class SampleLbonusTermPeer
{
    public static function retrieveByPk($termId)
    {
        $memcacheKey = __CLASS__ . ':' . $termId;
        if ($res = PhateMemcached::get($memcacheKey, 'api')) {
            return $res;
        }
        $dbh = PhateDB::getInstance('common_slave');
        $params = array($termId);
        $sql = 'SELECT * FROM lbonus_term_m WHERE term_id = ? ';
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleLbonusTermOrm();
        $obj->hydrate($row);
        PhateMemcached::set($memcacheKey, $obj, 0, 'api');
        return $obj;
    }
    
    public static function retrieveAll()
    {
        $memcacheKey = __CLASS__ . ':ALL';
        if ($res = PhateMemcached::get($memcacheKey, 'api')) {
            return $res;
        }
        $dbh = PhateDB::getInstance('common_slave');
        $params = array();
        $sql = 'SELECT * FROM lbonus_term_m ORDER BY from_date';
        if (($list = $dbh->getAll($sql, $params)) === false) {
            return false;
        }
        $rtn = aray();
        foreach($list as $row) {
            $obj = new SampleLbonusTermOrm();
            $obj->hydrate($row);
            $rtn[] = $obj;
        }
        PhateMemcached::set($memcacheKey, $rtn, 0, 'api');
        return $rtn;
    }
    
    public static function getNowLbonusTerm()
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
        $sql = 'SELECT * FROM lbonus_term_m WHERE from_date <= :NOW AND to_date >= :NOW';
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleLbonusTermOrm;
        $obj->hydrate($row);
        PhateMemcached::set($memcacheKey, $obj, 0, 'api');
        return $obj;
    }
}