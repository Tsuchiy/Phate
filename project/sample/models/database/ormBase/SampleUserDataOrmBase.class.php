<?php
/**
 * SampleUserDataOrmBaseクラス
 *
 * user_dataのO-RMapper基礎クラス
 *
 * @access  public
 **/
class SampleUserDataOrmBase extends PhateORMapperBase
{
    protected $_tableName = 'user_data';

    protected $_pkey = array(
        'user_id',
    );
    protected $_pkeyIsRowId = false;
    protected $_value = array(
        'user_id' => null,
        'user_name' => null,
        'user_level' => '1',
        'exp' => '0',
        'title_id' => null,
        'gold' => '0',
        'friend_point' => '0',
        'user_comment' => null,
        'hp' => null,
        'hp_max' => null,
        'last_hp_recover' => null,
        'created' => '0000-00-00 00:00:00',
        'modified' => '0000-00-00 00:00:00',
    );
    protected $_toSave = array(
        'user_id' => null,
        'user_name' => null,
        'user_level' => '1',
        'exp' => '0',
        'title_id' => null,
        'gold' => '0',
        'friend_point' => '0',
        'user_comment' => null,
        'hp' => null,
        'hp_max' => null,
        'last_hp_recover' => null,
        'created' => '0000-00-00 00:00:00',
        'modified' => '0000-00-00 00:00:00',
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
    
    public function getUserName()
    {
        return $this->_toSave['user_name'];
    }
    
    public function setUserName($value)
    {
        if ($this->_value['user_name'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['user_name'] = $value;
    }
    
    public function getUserLevel()
    {
        return $this->_toSave['user_level'];
    }
    
    public function setUserLevel($value)
    {
        if ($this->_value['user_level'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['user_level'] = $value;
    }
    
    public function getExp()
    {
        return $this->_toSave['exp'];
    }
    
    public function setExp($value)
    {
        if ($this->_value['exp'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['exp'] = $value;
    }
    
    public function getTitleId()
    {
        return $this->_toSave['title_id'];
    }
    
    public function setTitleId($value)
    {
        if ($this->_value['title_id'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['title_id'] = $value;
    }
    
    public function getGold()
    {
        return $this->_toSave['gold'];
    }
    
    public function setGold($value)
    {
        if ($this->_value['gold'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['gold'] = $value;
    }
    
    public function getFriendPoint()
    {
        return $this->_toSave['friend_point'];
    }
    
    public function setFriendPoint($value)
    {
        if ($this->_value['friend_point'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['friend_point'] = $value;
    }
    
    public function getUserComment()
    {
        return $this->_toSave['user_comment'];
    }
    
    public function setUserComment($value)
    {
        if ($this->_value['user_comment'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['user_comment'] = $value;
    }
    
    public function getHp()
    {
        return $this->_toSave['hp'];
    }
    
    public function setHp($value)
    {
        if ($this->_value['hp'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['hp'] = $value;
    }
    
    public function getHpMax()
    {
        return $this->_toSave['hp_max'];
    }
    
    public function setHpMax($value)
    {
        if ($this->_value['hp_max'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['hp_max'] = $value;
    }
    
    public function getLastHpRecover()
    {
        return $this->_toSave['last_hp_recover'];
    }
    
    public function setLastHpRecover($value)
    {
        if ($this->_value['last_hp_recover'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['last_hp_recover'] = $value;
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
    
    public function getModified()
    {
        return $this->_toSave['modified'];
    }
    
    public function setModified($value)
    {
        if ($this->_value['modified'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['modified'] = $value;
    }
    
}
