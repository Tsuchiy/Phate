<?php
/**
 * SampleFriendUserOrmBaseクラス
 *
 * friend_userのO-RMapper基礎クラス
 *
 * @access  public
 **/
class SampleFriendUserOrmBase extends PhateORMapperBase
{
    protected $_tableName = 'friend_user';

    protected $_pkey = array(
        'user_id',
        'friend_user_id',
    );
    protected $_pkeyIsRowId = false;
    protected $_value = array(
        'user_id' => null,
        'friend_user_id' => null,
        'status' => null,
        'update_date' => '0000-00-00 00:00:00',
        'created' => '0000-00-00 00:00:00',
    );
    protected $_toSave = array(
        'user_id' => null,
        'friend_user_id' => null,
        'status' => null,
        'update_date' => '0000-00-00 00:00:00',
        'created' => '0000-00-00 00:00:00',
    );

    public function getUserId()
    {
        return $this->_toSave['user_id'];
    }
    
    public function setUserId($value)
    {
        if ($this->_value['user_id'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['user_id'] = $value;
    }
    
    public function getFriendUserId()
    {
        return $this->_toSave['friend_user_id'];
    }
    
    public function setFriendUserId($value)
    {
        if ($this->_value['friend_user_id'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['friend_user_id'] = $value;
    }
    
    public function getStatus()
    {
        return $this->_toSave['status'];
    }
    
    public function setStatus($value)
    {
        if ($this->_value['status'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['status'] = $value;
    }
    
    public function getUpdateDate()
    {
        return $this->_toSave['update_date'];
    }
    
    public function setUpdateDate($value)
    {
        if ($this->_value['update_date'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['update_date'] = $value;
    }
    
    public function getCreated()
    {
        return $this->_toSave['created'];
    }
    
    public function setCreated($value)
    {
        if ($this->_value['created'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['created'] = $value;
    }
    
}
