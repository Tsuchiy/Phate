<?php
/**
 * SampleLbonusPointRewardOrmBaseクラス
 *
 * lbonus_point_reward_mのO-RMapper基礎クラス
 *
 * @access  public
 **/
class SampleLbonusPointRewardOrmBase extends PhateORMapperBase
{
    protected $_tableName = 'lbonus_point_reward_m';

    protected $_pkey = array(
        'id',
    );
    protected $_pkeyIsRowId = false;
    protected $_value = array(
        'id' => null,
        'term_id' => null,
        'point' => null,
        'reward_id' => null,
        'created' => '0000-00-00 00:00:00',
    );
    protected $_toSave = array(
        'id' => null,
        'term_id' => null,
        'point' => null,
        'reward_id' => null,
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
    
    public function getTermId()
    {
        return $this->_toSave['term_id'];
    }
    
    public function setTermId($value)
    {
        if ($this->_value['term_id'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['term_id'] = $value;
    }
    
    public function getPoint()
    {
        return $this->_toSave['point'];
    }
    
    public function setPoint($value)
    {
        if ($this->_value['point'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['point'] = $value;
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
