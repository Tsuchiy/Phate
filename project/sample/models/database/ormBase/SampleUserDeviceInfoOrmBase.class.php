<?php
/**
 * SampleUserDeviceInfoOrmBaseクラス
 *
 * user_device_infoのO-RMapper基礎クラス
 *
 * @access  public
 **/
class SampleUserDeviceInfoOrmBase extends PhateORMapperBase
{
    protected $_tableName = 'user_device_info';

    protected $_pkey = array(
        'user_id',
    );
    protected $_pkeyIsRowId = false;
    protected $_value = array(
        'user_id' => null,
        'device_type_id' => null,
        'device_token' => null,
        'created' => '0000-00-00 00:00:00',
    );
    protected $_toSave = array(
        'user_id' => null,
        'device_type_id' => null,
        'device_token' => null,
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
    
    public function getDeviceTypeId()
    {
        return $this->_toSave['device_type_id'];
    }
    
    public function setDeviceTypeId($value)
    {
        if ($this->_value['device_type_id'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['device_type_id'] = $value;
    }
    
    public function getDeviceToken()
    {
        return $this->_toSave['device_token'];
    }
    
    public function setDeviceToken($value)
    {
        if ($this->_value['device_token'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['device_token'] = $value;
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
