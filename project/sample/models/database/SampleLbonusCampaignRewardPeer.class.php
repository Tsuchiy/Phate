<?php
/**
 * SampleLbonusCampaignRewardPeerクラス
 *
 * lbonus_campaign_reward_mのO-RMapper取り扱い用クラス(ReadOnly with memcache)
 *
 * @access public
 **/
class SampleLbonusCampaignRewardPeer
{
    public static function retrieveByPk($id)
    {
        $memcacheKey = __CLASS__ . ':' . $id;
        if ($res = PhateMemcached::get($memcacheKey, 'api')) {
            return $res;
        }
        $dbh = PhateDB::getInstance('common_slave');
        $params = array($id);
        $sql = 'SELECT * FROM lbonus_campaign_reward_m WHERE id = ? ';
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleLbonusCampaignRewardOrm();
        $obj->hydrate($row);
        PhateMemcached::set($memcacheKey, $obj, 0, 'api');
        return $obj;
    }

    public static function getNowLbonusCampaign()
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
        $sql = 'SELECT * FROM lbonus_campaign_reward_m WHERE from_date <= :NOW AND to_date >= :NOW';
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleLbonusCampaignRewardOrm;
        $obj->hydrate($row);
        PhateMemcached::set($memcacheKey, $obj, 0, 'api');
        return $obj;
    }
    
}