<?php
/**
 * SampleApplicationInfoPeerクラス
 *
 * application_info_mのO-RMapper取り扱い用クラス(ReadOnly with memcache)
 *
 * @access public
 **/
class SampleApplicationInfoPeer
{
    public static function retrieveByPk($deviceTypeId)
    {
        $memcacheKey = __CLASS__ . ':' . $deviceTypeId;
        if ($res = PhateMemcached::get($memcacheKey, 'api')) {
            return $res;
        }
        $dbh = PhateDB::getInstance('common_slave');
        $params = array($deviceTypeId);
        $sql = 'SELECT * FROM application_info_m WHERE device_type_id = ? ';
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleApplicationInfoOrm();
        $obj->hydrate($row);
        PhateMemcached::set($memcacheKey, $obj, 0, 'api');
        return $obj;
    }
}