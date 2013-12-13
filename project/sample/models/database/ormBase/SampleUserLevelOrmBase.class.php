<?php
/**
 * SampleUserLevelOrmBaseクラス
 *
 * user_level_mのO-RMapper基礎クラス
 *
 * @access  public
 **/
class SampleUserLevelOrmBase extends PhateORMapperBase
{
    protected $_tableName = 'user_level_m';

    protected $_pkey = array(
        'level',
    );
    protected $_pkeyIsRowId = false;
    protected $_value = array(
        'level' => null,
        'max_hp' => null,
        'start_exp' => null,
        'next_exp' => null,
        'created' => null,
    );
    protected $_toSave = array(
        'level' => null,
        'max_hp' => null,
        'start_exp' => null,
        'next_exp' => null,
        'created' => null,
    );

    public function getLevel()
    {
        return $this->_toSave['level'];
    }
    
    public function setLevel($value)
    {
        if ($this->_value['level'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['level'] = $value;
    }
    
    public function getMaxHp()
    {
        return $this->_toSave['max_hp'];
    }
    
    public function setMaxHp($value)
    {
        if ($this->_value['max_hp'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['max_hp'] = $value;
    }
    
    public function getStartExp()
    {
        return $this->_toSave['start_exp'];
    }
    
    public function setStartExp($value)
    {
        if ($this->_value['start_exp'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['start_exp'] = $value;
    }
    
    public function getNextExp()
    {
        return $this->_toSave['next_exp'];
    }
    
    public function setNextExp($value)
    {
        if ($this->_value['next_exp'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['next_exp'] = $value;
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
