<?php
/**
 * SampleLbonusCampaignRewardOrmBaseクラス
 *
 * lbonus_campaign_reward_mのO-RMapper基礎クラス
 *
 * @access  public
 **/
class SampleLbonusCampaignRewardOrmBase extends PhateORMapperBase
{
    protected $_tableName = 'lbonus_campaign_reward_m';

    protected $_pkey = array(
        'id',
    );
    protected $_pkeyIsRowId = false;
    protected $_value = array(
        'id' => null,
        'from_date' => '0000-00-00 00:00:00',
        'to_date' => '0000-00-00 00:00:00',
        'reward_id' => null,
        'one_time' => '0',
        'created' => '0000-00-00 00:00:00',
    );
    protected $_toSave = array(
        'id' => null,
        'from_date' => '0000-00-00 00:00:00',
        'to_date' => '0000-00-00 00:00:00',
        'reward_id' => null,
        'one_time' => '0',
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
    
    public function getOneTime()
    {
        return $this->_toSave['one_time'];
    }
    
    public function setOneTime($value)
    {
        if ($this->_value['one_time'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['one_time'] = $value;
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
