<?php
/**
 * SampleUserAuthDataOrmBaseクラス
 *
 * user_auth_dataのO-RMapper基礎クラス
 *
 * @access  public
 **/
class SampleUserAuthDataOrmBase extends PhateORMapperBase
{
    protected $_tableName = 'user_auth_data';

    protected $_pkey = array(
        'user_id',
    );
    protected $_pkeyIsRowId = true;
    protected $_value = array(
        'user_id' => null,
        'password' => null,
        'shard_id' => null,
        'show_user_id' => null,
        'tutorial_state' => '0',
        'last_login_date' => '0000-00-00 00:00:00',
        'created' => '0000-00-00 00:00:00',
    );
    protected $_toSave = array(
        'user_id' => null,
        'password' => null,
        'shard_id' => null,
        'show_user_id' => null,
        'tutorial_state' => '0',
        'last_login_date' => '0000-00-00 00:00:00',
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
    
    public function getPassword()
    {
        return $this->_toSave['password'];
    }
    
    public function setPassword($value)
    {
        if ($this->_value['password'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['password'] = $value;
    }
    
    public function getShardId()
    {
        return $this->_toSave['shard_id'];
    }
    
    public function setShardId($value)
    {
        if ($this->_value['shard_id'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['shard_id'] = $value;
    }
    
    public function getShowUserId()
    {
        return $this->_toSave['show_user_id'];
    }
    
    public function setShowUserId($value)
    {
        if ($this->_value['show_user_id'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['show_user_id'] = $value;
    }
    
    public function getTutorialState()
    {
        return $this->_toSave['tutorial_state'];
    }
    
    public function setTutorialState($value)
    {
        if ($this->_value['tutorial_state'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['tutorial_state'] = $value;
    }
    
    public function getLastLoginDate()
    {
        return $this->_toSave['last_login_date'];
    }
    
    public function setLastLoginDate($value)
    {
        if ($this->_value['last_login_date'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['last_login_date'] = $value;
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
