<?php
/**
 * SampleActivityUserPeerクラス
 *
 * activity_userのO-RMapper取り扱い用クラス
 *
 * @access public
 **/
class SampleActivityUserPeer
{
    public static function retrieveByPk($id, $shardId = null, PhateDBO $dbh = null)
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
        $params = array($id);
        $sql = 'SELECT * FROM activity_user WHERE id = ? ' . $forUpdateClause;
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleActivityUserOrm();
        $obj->hydrate($row);
        return $obj;
    }
}