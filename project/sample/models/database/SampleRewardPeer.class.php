<?php
/**
 * SampleRewardPeerクラス
 *
 * reward_mのO-RMapper取り扱い用クラス(ReadOnly with memcache)
 *
 * @access public
 **/
class SampleRewardPeer
{
    public static function retrieveByPk($id)
    {
        $memcacheKey = __CLASS__ . ':' . $id;
        if ($res = PhateMemcached::get($memcacheKey, 'api')) {
            return $res;
        }
        $dbh = PhateDB::getInstance('common_slave');
        $params = array($id);
        $sql = 'SELECT * FROM reward_m WHERE id = ? ';
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleRewardOrm();
        $obj->hydrate($row);
        PhateMemcached::set($memcacheKey, $obj, 0, 'api');
        return $obj;
    }

    public static function retrieveByRewardId($rewardId)
    {
        $memcacheKey = __CLASS__ . ':rewardId:' . $rewardId;
        if ($res = PhateMemcached::get($memcacheKey, 'api')) {
            return $res;
        }
        $dbh = PhateDB::getInstance('common_slave');
        $params = array($rewardId);
        $sql = 'SELECT * FROM reward_m WHERE reward_id = ? ';
        if (($list = $dbh->getAll($sql, $params)) === false) {
            return false;
        }
        $rtn = array();
        foreach ($list as $row) {
            $obj = new SampleRewardOrm();
            $obj->hydrate($row);
            $rtn[] = $obj;
        }
        PhateMemcached::set($memcacheKey, $rtn, 0, 'api');
        return $rtn;
    }
    
}