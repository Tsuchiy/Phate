<?php
/**
 * SampleUserDeviceInfoPeerクラス
 *
 * user_device_infoのO-RMapper取り扱い用クラス
 *
 * @access public
 **/
class SampleUserDeviceInfoPeer
{
    public static function retrieveByPk($userId, PhateDBO $dbh = null)
    {
        if (is_null($dbh)) {
            $forUpdateClause = '';
            $dbh = PhateDB::getInstance('common_master');
        } else {
            $forUpdateClause = ' FOR UPDATE ';
        }
        $params = array($userId);
        $sql = 'SELECT * FROM user_device_info WHERE user_id = ? ' . $forUpdateClause;
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleUserDeviceInfoOrm();
        $obj->hydrate($row);
        return $obj;
    }
}