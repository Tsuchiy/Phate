<?php
/**
 * SampleUserDataPeerクラス
 *
 * user_dataのO-RMapper取り扱い用クラス
 *
 * @access public
 **/
class SampleUserDataPeer
{
    public static function retrieveByPk($userId, $shardId = null, PhateDBO $dbh = null)
    {
        if (is_null($dbh)) {
            if (is_null($shardId)) {
                throw new PhateDatabaseException('shardId empty');
            }
            $forUpdateClause = '';
            $dbh = PhateDB::getInstanceByShardId('shard_user_master', $shardId);
        } else {
            $forUpdateClause = ' FOR UPDATE ';
        }
        $params = array($userId);
        $sql = 'SELECT * FROM user_data WHERE user_id = ? ' . $forUpdateClause;
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleUserDataOrm();
        $obj->hydrate($row);
        return $obj;
    }
}