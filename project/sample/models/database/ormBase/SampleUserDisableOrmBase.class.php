<?php
/**
 * SampleUserDisableOrmBaseクラス
 *
 * user_disable_mのO-RMapper基礎クラス
 *
 * @access  public
 **/
class SampleUserDisableOrmBase extends PhateORMapperBase
{
    protected $_tableName = 'user_disable_m';

    protected $_pkey = array(
        'user_id',
    );
    protected $_pkeyIsRowId = false;
    protected $_value = array(
        'user_id' => null,
        'from_date' => '0000-00-00 00:00:00',
        'to_date' => '0000-00-00 00:00:00',
        'created' => '0000-00-00 00:00:00',
    );
    protected $_toSave = array(
        'user_id' => null,
        'from_date' => '0000-00-00 00:00:00',
        'to_date' => '0000-00-00 00:00:00',
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
    
    public function getFromDate()
    {
        return $this->_toSave['from_date'];
    }
    
    public function setFromDate($value)
    {
        if ($this->_value['from_date'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['from_date'] = $value;
    }
    
    public function getToDate()
    {
        return $this->_toSave['to_date'];
    }
    
    public function setToDate($value)
    {
        if ($this->_value['to_date'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['to_date'] = $value;
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
