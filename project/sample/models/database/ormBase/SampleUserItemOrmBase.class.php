<?php
/**
 * SampleUserItemOrmBaseクラス
 *
 * user_itemのO-RMapper基礎クラス
 *
 * @access  public
 **/
class SampleUserItemOrmBase extends PhateORMapperBase
{
    protected $_tableName = 'user_item';

    protected $_pkey = array(
        'user_id',
        'item_id',
    );
    protected $_pkeyIsRowId = false;
    protected $_value = array(
        'user_id' => null,
        'item_id' => null,
        'amount' => '0',
        'modified' => null,
        'created' => null,
    );
    protected $_toSave = array(
        'user_id' => null,
        'item_id' => null,
        'amount' => '0',
        'modified' => null,
        'created' => null,
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
    
    public function getItemId()
    {
        return $this->_toSave['item_id'];
    }
    
    public function setItemId($value)
    {
        if ($this->_value['item_id'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['item_id'] = $value;
    }
    
    public function getAmount()
    {
        return $this->_toSave['amount'];
    }
    
    public function setAmount($value)
    {
        if ($this->_value['amount'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['amount'] = $value;
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
