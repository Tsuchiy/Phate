<?php
/**
 * SampleUserAuthDataPeerクラス
 *
 * user_auth_dataのO-RMapper取り扱い用クラス
 *
 * @access public
 **/
class SampleUserAuthDataPeer
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
        $sql = 'SELECT * FROM user_auth_data WHERE user_id = ? ' . $forUpdateClause;
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleUserAuthDataOrm();
        $obj->hydrate($row);
        return $obj;
    }
}