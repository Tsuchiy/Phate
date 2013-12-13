<?php
/**
 * SampleLbonusTermOrmBaseクラス
 *
 * lbonus_term_mのO-RMapper基礎クラス
 *
 * @access  public
 **/
class SampleLbonusTermOrmBase extends PhateORMapperBase
{
    protected $_tableName = 'lbonus_term_m';

    protected $_pkey = array(
        'term_id',
    );
    protected $_pkeyIsRowId = true;
    protected $_value = array(
        'term_id' => null,
        'from_date' => '0000-00-00 00:00:00',
        'to_date' => '0000-00-00 00:00:00',
        'max_point' => '1',
        'max_cycle' => '1',
        'default_point' => '1',
        'created' => '0000-00-00 00:00:00',
    );
    protected $_toSave = array(
        'term_id' => null,
        'from_date' => '0000-00-00 00:00:00',
        'to_date' => '0000-00-00 00:00:00',
        'max_point' => '1',
        'max_cycle' => '1',
        'default_point' => '1',
        'created' => '0000-00-00 00:00:00',
    );

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
    
    public function getMaxPoint()
    {
        return $this->_toSave['max_point'];
    }
    
    public function setMaxPoint($value)
    {
        if ($this->_value['max_point'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['max_point'] = $value;
    }
    
    public function getMaxCycle()
    {
        return $this->_toSave['max_cycle'];
    }
    
    public function setMaxCycle($value)
    {
        if ($this->_value['max_cycle'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['max_cycle'] = $value;
    }
    
    public function getDefaultPoint()
    {
        return $this->_toSave['default_point'];
    }
    
    public function setDefaultPoint($value)
    {
        if ($this->_value['default_point'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['default_point'] = $value;
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
