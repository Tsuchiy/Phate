<?php
/**
 * SampleActivityUserOrmBaseクラス
 *
 * activity_userのO-RMapper基礎クラス
 *
 * @access  public
 **/
class SampleActivityUserOrmBase extends PhateORMapperBase
{
    protected $_tableName = 'activity_user';

    protected $_pkey = array(
        'id',
    );
    protected $_pkeyIsRowId = true;
    protected $_value = array(
        'id' => null,
        'user_id' => null,
        'activity_type_id' => null,
        'target_id1' => null,
        'target_id2' => null,
        'created' => '0000-00-00 00:00:00',
    );
    protected $_toSave = array(
        'id' => null,
        'user_id' => null,
        'activity_type_id' => null,
        'target_id1' => null,
        'target_id2' => null,
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
    
    public function getActivityTypeId()
    {
        return $this->_toSave['activity_type_id'];
    }
    
    public function setActivityTypeId($value)
    {
        if ($this->_value['activity_type_id'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['activity_type_id'] = $value;
    }
    
    public function getTargetId1()
    {
        return $this->_toSave['target_id1'];
    }
    
    public function setTargetId1($value)
    {
        if ($this->_value['target_id1'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['target_id1'] = $value;
    }
    
    public function getTargetId2()
    {
        return $this->_toSave['target_id2'];
    }
    
    public function setTargetId2($value)
    {
        if ($this->_value['target_id2'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['target_id2'] = $value;
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
