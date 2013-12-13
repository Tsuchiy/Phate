<?php
/**
 * SampleLbonusPointUserPeerクラス
 *
 * lbonus_point_userのO-RMapper取り扱い用クラス
 *
 * @access public
 **/
class SampleLbonusPointUserPeer
{
    public static function retrieveByPk($userId, $termId, $shardId = null, PhateDBO $dbh = null)
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
        $params = array($userId, $termId);
        $sql = 'SELECT * FROM lbonus_point_user WHERE user_id = ? AND term_id = ? ' . $forUpdateClause;
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleLbonusPointUserOrm();
        $obj->hydrate($row);
        return $obj;
    }
}