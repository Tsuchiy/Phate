<?php
/**
 * SampleLbonusPointRewardPeerクラス
 *
 * lbonus_point_reward_mのO-RMapper取り扱い用クラス(ReadOnly with memcache)
 *
 * @access public
 **/
class SampleLbonusPointRewardPeer
{
    public static function retrieveByPk($id)
    {
        $memcacheKey = __CLASS__ . ':' . $id;
        if ($res = PhateMemcached::get($memcacheKey, 'api')) {
            return $res;
        }
        $dbh = PhateDB::getInstance('common_slave');
        $params = array($id);
        $sql = 'SELECT * FROM lbonus_point_reward_m WHERE id = ? ';
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleLbonusPointRewardOrm();
        $obj->hydrate($row);
        PhateMemcached::set($memcacheKey, $obj, 0, 'api');
        return $obj;
    }

    public static function retrieveByTermId($termId)
    {
        $memcacheKey = __CLASS__ . ':termId:' . $termId;
        if ($res = PhateMemcached::get($memcacheKey, 'api')) {
            return $res;
        }
        $dbh = PhateDB::getInstance('common_slave');
        $params = array($termId);
        $sql = 'SELECT * FROM lbonus_point_reward_m WHERE term_id = ? ORDER BY point';
        if (($list = $dbh->getAll($sql, $params)) === false) {
            return false;
        }
        $rtn = array();
        foreach ($list as $row) {
            $obj = new SampleLbonusPointRewardOrm();
            $obj->hydrate($row);
            $rtn[$row['point']] = $obj;
        }
        PhateMemcached::set($memcacheKey, $rtn, 0, 'api');
        return $rtn;
    }
    
}