<?php
/**
 * SampleLbonusPointOrmBaseクラス
 *
 * lbonus_point_mのO-RMapper基礎クラス
 *
 * @access  public
 **/
class SampleLbonusPointOrmBase extends PhateORMapperBase
{
    protected $_tableName = 'lbonus_point_m';

    protected $_pkey = array(
        'id',
    );
    protected $_pkeyIsRowId = false;
    protected $_value = array(
        'id' => null,
        'from_date' => '0000-00-00 00:00:00',
        'to_date' => '0000-00-00 00:00:00',
        'point' => '1',
        'created' => '0000-00-00 00:00:00',
    );
    protected $_toSave = array(
        'id' => null,
        'from_date' => '0000-00-00 00:00:00',
        'to_date' => '0000-00-00 00:00:00',
        'point' => '1',
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
