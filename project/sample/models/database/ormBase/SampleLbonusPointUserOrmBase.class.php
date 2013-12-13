<?php
/**
 * SampleLbonusPointUserOrmBaseクラス
 *
 * lbonus_point_userのO-RMapper基礎クラス
 *
 * @access  public
 **/
class SampleLbonusPointUserOrmBase extends PhateORMapperBase
{
    protected $_tableName = 'lbonus_point_user';

    protected $_pkey = array(
        'user_id',
        'term_id',
    );
    protected $_pkeyIsRowId = false;
    protected $_value = array(
        'user_id' => null,
        'term_id' => null,
        'point' => null,
        'cycle' => null,
        'modified' => null,
        'created' => null,
    );
    protected $_toSave = array(
        'user_id' => null,
        'term_id' => null,
        'point' => null,
        'cycle' => null,
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
    
    public function getCycle()
    {
        return $this->_toSave['cycle'];
    }
    
    public function setCycle($value)
    {
        if ($this->_value['cycle'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['cycle'] = $value;
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
