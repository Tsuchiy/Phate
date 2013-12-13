<?php
/**
 * SampleRewardOrmBaseクラス
 *
 * reward_mのO-RMapper基礎クラス
 *
 * @access  public
 **/
class SampleRewardOrmBase extends PhateORMapperBase
{
    protected $_tableName = 'reward_m';

    protected $_pkey = array(
        'id',
    );
    protected $_pkeyIsRowId = true;
    protected $_value = array(
        'id' => null,
        'reward_id' => null,
        'reward_type_id' => null,
        'reward_content_id' => null,
        'reward_value' => null,
        'created' => '0000-00-00 00:00:00',
    );
    protected $_toSave = array(
        'id' => null,
        'reward_id' => null,
        'reward_type_id' => null,
        'reward_content_id' => null,
        'reward_value' => null,
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
    
    public function getRewardId()
    {
        return $this->_toSave['reward_id'];
    }
    
    public function setRewardId($value)
    {
        if ($this->_value['reward_id'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['reward_id'] = $value;
    }
    
    public function getRewardTypeId()
    {
        return $this->_toSave['reward_type_id'];
    }
    
    public function setRewardTypeId($value)
    {
        if ($this->_value['reward_type_id'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['reward_type_id'] = $value;
    }
    
    public function getRewardContentId()
    {
        return $this->_toSave['reward_content_id'];
    }
    
    public function setRewardContentId($value)
    {
        if ($this->_value['reward_content_id'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['reward_content_id'] = $value;
    }
    
    public function getRewardValue()
    {
        return $this->_toSave['reward_value'];
    }
    
    public function setRewardValue($value)
    {
        if ($this->_value['reward_value'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['reward_value'] = $value;
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
