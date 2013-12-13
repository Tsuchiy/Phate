<?php
/**
 * SampleItemUserPeerクラス
 *
 * item_userのO-RMapper取り扱い用クラス
 *
 * @access public
 **/
class SampleItemUserPeer
{
    public static function retrieveByPk($userId, $itemId, $shardId = null, PhateDBO $dbh = null)
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
        $params = array($userId, $itemId);
        $sql = 'SELECT * FROM item_user WHERE user_id = ? AND item_id = ? ' . $forUpdateClause;
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleItemUserOrm();
        $obj->hydrate($row);
        return $obj;
    }
}