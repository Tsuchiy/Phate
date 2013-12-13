<?php
/**
 * SampleUserVirtualMoneyOrmBaseクラス
 *
 * user_virtual_moneyのO-RMapper基礎クラス
 *
 * @access  public
 **/
class SampleUserVirtualMoneyOrmBase extends PhateORMapperBase
{
    protected $_tableName = 'user_virtual_money';

    protected $_pkey = array(
        'id',
    );
    protected $_pkeyIsRowId = true;
    protected $_value = array(
        'id' => null,
        'user_id' => null,
        'add_amount' => '0',
        'rest_amount' => '0',
        'is_sold' => '0',
        'modified' => null,
        'created' => null,
    );
    protected $_toSave = array(
        'id' => null,
        'user_id' => null,
        'add_amount' => '0',
        'rest_amount' => '0',
        'is_sold' => '0',
        'modified' => null,
        'created' => null,
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
    
    public function getAddAmount()
    {
        return $this->_toSave['add_amount'];
    }
    
    public function setAddAmount($value)
    {
        if ($this->_value['add_amount'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['add_amount'] = $value;
    }
    
    public function getRestAmount()
    {
        return $this->_toSave['rest_amount'];
    }
    
    public function setRestAmount($value)
    {
        if ($this->_value['rest_amount'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['rest_amount'] = $value;
    }
    
    public function getIsSold()
    {
        return $this->_toSave['is_sold'];
    }
    
    public function setIsSold($value)
    {
        if ($this->_value['is_sold'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['is_sold'] = $value;
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
