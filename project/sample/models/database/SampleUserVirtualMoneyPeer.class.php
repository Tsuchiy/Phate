<?php
/**
 * SampleUserVirtualMoneyPeerクラス
 *
 * user_virtual_moneyのO-RMapper取り扱い用クラス
 *
 * @access public
 **/
class SampleUserVirtualMoneyPeer
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
        $sql = 'SELECT * FROM user_virtual_money WHERE id = ? ' . $forUpdateClause;
        if (($row = $dbh->getRow($sql, $params)) === false) {
            return false;
        }
        $obj = new SampleUserVirtualMoneyOrm();
        $obj->hydrate($row);
        return $obj;
    }
}