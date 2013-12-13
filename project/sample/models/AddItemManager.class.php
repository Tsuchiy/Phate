<?php
class AddItemManager extends PhateModelBase
{
    const ITEM_TYPE_GOLD = 1;
    const ITEM_TYPE_ITEM = 10;
    const ITEM_TYPE_FRIEND_POINT = 20;
    const ITEM_TYPE_VIRTUAL_MONEY = 50;
    
    public static function addItem($userId, $itemType, $id, $amount, $option, $dbhShard = null, $dbhLog = null)
    {
        $userAuthData = SampleUserAuthDataPeer::retrieveByPk($userId);
        $dbhShard = $dbhShard ? $dbhShard : PhateDB::getInstanceByShardId('shard_user_master', $userAuthData->getShardId());
        $dbhShard->beginTransaction();
        try {
            switch($itemType) {
                case self::ITEM_TYPE_GOLD:
                    $userData = SampleUserDataPeer::retrieveByPk($userId);
                    $userData->setGold($userData->getGold() + $amount);
                    $userData->save($dbhShard);
                    break;
                case self::ITEM_TYPE_ITEM:
                    if (!($userItem = SampleUserItemPeer::retrieveByPk($userId, $id))) {
                        $userItem = new SampleUserItemOrm;
                        $userItem->setUserId($userId);
                        $userItem->setItemId($id);
                        $userItem->setAmount(0);
                    }
                    $userItem->setAmount($userItem->getAmount() + $amount);
                    $userItem->save($dbhShard);
                    break;
                case self::ITEM_TYPE_FRIEND_POINT:
                    $userData = SampleUserDataPeer::retrieveByPk($userId);
                    $userData->setFriendPoint($userData->getFriendPoint() + $amount);
                    $userData->save($dbhShard);
                    break;
                case self::ITEM_TYPE_VIRTUAL_MONEY:
                    $userVirtualMoney = new SampleUserVirtualMoneyOrm;
                    $userVirtualMoney->setUserId($userId);
                    $userVirtualMoney->setAddAmount($amount);
                    $userVirtualMoney->setRestAmount($amount);
                    if (isset($option['isSold'])) {
                        $userVirtualMoney->setIsSold($option['isSold']);
                    }
                    $userVirtualMoney->save($dbhShard);
                    break;
                default:
                    throw new PhateCommonException('wrong item type');
            }
        } catch(Exceptin $e) {
            $dbhShard->rollBack();
            throw $e;
        }
        return;
    }
}
