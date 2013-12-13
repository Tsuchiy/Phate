<?php
/**
 * SampleApplicationInfoOrmBaseクラス
 *
 * application_info_mのO-RMapper基礎クラス
 *
 * @access  public
 **/
class SampleApplicationInfoOrmBase extends PhateORMapperBase
{
    protected $_tableName = 'application_info_m';

    protected $_pkey = array(
        'device_type_id',
    );
    protected $_pkeyIsRowId = false;
    protected $_value = array(
        'device_type_id' => null,
        'stable_version' => null,
        'lowest_version' => null,
    );
    protected $_toSave = array(
        'device_type_id' => null,
        'stable_version' => null,
        'lowest_version' => null,
    );

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
    
    public function getStableVersion()
    {
        return $this->_toSave['stable_version'];
    }
    
    public function setStableVersion($value)
    {
        if ($this->_value['stable_version'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['stable_version'] = $value;
    }
    
    public function getLowestVersion()
    {
        return $this->_toSave['lowest_version'];
    }
    
    public function setLowestVersion($value)
    {
        if ($this->_value['lowest_version'] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave['lowest_version'] = $value;
    }
    
}
