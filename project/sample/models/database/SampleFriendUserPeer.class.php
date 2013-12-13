<?php
/**
 * SampleFriendUserPeerクラス
 *
 * friend_userのO-RMapper取り扱い用クラス
 *
 * @access public
 **/
class SampleFriendUserPeer
{
    public static function retrieveByPk($userId, $friendUserId, $shardId = null, PhateDBO $dbh = null)
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
        $params = array($userId, $friendUserId);
        $sql = 'SELECT * FROM friend_user WHERE user_id = ? AND friend_user_id = ? ' . $forUpdateClause;
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleFriendUserOrm();
        $obj->hydrate($row);
        return $obj;
    }
}