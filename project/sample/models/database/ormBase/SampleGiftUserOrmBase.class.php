<?php
/**
 * SampleGiftUserOrmBaseクラス
 *
 * gift_userのO-RMapper基礎クラス
 *
 * @access  public
 **/
class SampleGiftUserOrmBase extends PhateORMapperBase
{
    protected $_tableName = 'gift_user';

    protected $_pkey = array(
        'id',
    );
    protected $_pkeyIsRowId = true;
    protected $_value = array(
        'id' => null,
        'user_id' => null,
        'from_user_id' => null,
        'gift_type_id' => null,
        'gift_content_id' => null,
        'gift_value' => null,
        'opened_date' => null,
        'created' => '0000-00-00 00:00:00',
    );
    protected $_toSave = array(
        'id' => null,
        'user_id' => null,
        'from_user_id' => null,
        'gift_type_id' => null,
        'gift_content_id' => null,
        'gift_value' => null,
        'opened_date' => null,
        'created' => '0000-00-00 00:00:00',
    );

    public function getId()
    {
        return $this->_toSave['id'];
    }
    
    public function setId($value)
    {
        if ($this->_value['id'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['id'] = $value;
    }
    
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
    
    public function getFromUserId()
    {
        return $this->_toSave['from_user_id'];
    }
    
    public function setFromUserId($value)
    {
        if ($this->_value['from_user_id'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['from_user_id'] = $value;
    }
    
    public function getGiftTypeId()
    {
        return $this->_toSave['gift_type_id'];
    }
    
    public function setGiftTypeId($value)
    {
        if ($this->_value['gift_type_id'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['gift_type_id'] = $value;
    }
    
    public function getGiftContentId()
    {
        return $this->_toSave['gift_content_id'];
    }
    
    public function setGiftContentId($value)
    {
        if ($this->_value['gift_content_id'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['gift_content_id'] = $value;
    }
    
    public function getGiftValue()
    {
        return $this->_toSave['gift_value'];
    }
    
    public function setGiftValue($value)
    {
        if ($this->_value['gift_value'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['gift_value'] = $value;
    }
    
    public function getOpenedDate()
    {
        return $this->_toSave['opened_date'];
    }
    
    public function setOpenedDate($value)
    {
        if ($this->_value['opened_date'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['opened_date'] = $value;
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
