<?php
/**
 * PhateORMapperBaseクラス
 *
 * O-RMapperの先祖クラス。基礎パラメータと基礎メソッド群。
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateORMapperBase
{
    protected $_tableName;
    
    protected $_pkey = array();
    
    protected $_pkeyIsRowId = false;
    
    protected $_value = array();
    
    protected $_toSave = array();
    
    protected $_changeFlg = true;
    
    protected $_fromHydrateFlg = false;
    
    /**
     * プロパティ取得用汎用メソッド(予備用)
     * 
     * @access public
     * @param string $name
     * @return string
     */
    public function __get($name)
    {
        return $this->_toSave[$name];
    }
    
    /**
     * プロパティ設定用汎用メソッド(予備用)
     * 
     * @access public
     * @param string $name
     * @param string $value
     * @return void
     */
    public function __set($name, $value)
    {
        if ($this->_value[$name] != $value) {
            $this->_changeFlg = true;
        }
        $this->_toSave[$name] = $value;
    }

    /**
     * 行配列をオブジェクトに設定する
     * 
     * @access public
     * @param array $row
     * @return void
     */
    public function hydrate(array $row)
    {
        $this->_changeFlg = false;
        $this->_fromHydrateFlg = true;
        $this->_value = $row;
        $this->_toSave = $row;
    }

    /**
     * オブジェクトのプロパティを行配列の形にする
     * 
     * @access public
     * @param void
     * @return array
     */
    public function toArray()
    {
        return $this->_toSave;
    }
    
    /**
     * オブジェクトの状態をDBサーバに反映させるためにInsert/Update文を発行する
     * 
     * @access public
     * @param PhateDBO $dbh
     * @return boolean
     */
    public function save(PhateDBO $dbh)
    {
        // readonlyDBは処理禁止
        if ($dbh->isReadOnly()) {
            throw new PhateDatabaseException('this database is readonly');
        }
        // hydrate後に変更がない場合はなにもしない
        if ($this->_fromHydrateFlg && !$this->_changeFlg) {
            return false;
        }
        // "modified"カラムは特別扱い
        if (array_key_exists('modified', $this->_toSave)) {
            $this->_toSave['modified'] = PhateTimer::getDateTime();
        }
        // insertの場合
        if (!$this->_fromHydrateFlg) {
            // "created"カラムは特別扱い
            if (array_key_exists('created', $this->_toSave)) {
                $this->_toSave['created'] = PhateTimer::getDateTime();
            }
            // autoincrementに新規行を追加するとき
            $toSave = $this->_toSave;
            if ($this->_pkeyIsRowId) {
                $pkey = $this->_pkey[0];
                unset($toSave[$pkey]);
            }
            $columns = array_keys($toSave);
            $columnClause = '(' . implode(',', $columns) . ')';
            $placeClause = str_repeat('?,', count($toSave));
            $placeClause = '(' . substr($placeClause, 0, -1) . ')';
            $params = array_values($toSave);
            $sql = 'INSERT INTO ' .$this->_tableName . ' ' . $columnClause . ' VALUES ' . $placeClause;
            if ($dbh->executeSql($sql, $params) === false) {
                return false;
            }
            if ($this->_pkeyIsRowId) {
                $this->_toSave[$pkey] = $dbh->lastInsertId();
            }
        } else {
            // updateの場合
            $setClause = '';
            $setParam = array();
            foreach ($this->_toSave as $key => $value) {
                $setClause .= $setClause == '' ? ' SET ' : ' , ';
                $setClause .= $key .' = ? ';
                $setParam[] = $value;
            }
            $whereClause = '';
            $whereParam = array();
            foreach ($this->_pkey as $key) {
                $whereClause .= $whereClause == '' ? ' WHERE ' : ' AND ';
                $whereClause .= $key . ' = ? ';
                $whereParam[] = $this->_value[$key];
            }
            $sql = 'UPDATE ' . $this->_tableName . $setClause . $whereClause;
            $params = array_merge($setParam, $whereParam);
            if ($dbh->executeSql($sql, $params) === false) {
                return false;
            }
        }
        $this->_value = $this->_toSave;
        $this->_changeFlg = false;
        $this->_fromHydrateFlg = true;
        return true;
    }
    
    /**
     * オブジェクトに対応する行をDatabaseから削除する
     * 
     * @access public
     * @param PhateDBO $dbh
     * @return boolean
     */
    public function delete(PhateDBO $dbh)
    {
        // readonlyDBは処理禁止
        if ($dbh->isReadOnly()) {
            throw new PhateDatabaseException('this database is readonly');
        }
        // hydrate済みか確認
        if (!$this->_fromHydrateFlg) {
            return false;
        }
        $whereClause = '';
        $whereParam = array();
        foreach ($this->_pkey as $key) {
            $whereClause .= $whereClause == '' ? ' WHERE ' : ' AND ';
            $whereClause .= $key . ' = ?';
            $whereParam[] = $this->_value[$key];
        }
        $sql = 'DELETE FROM ' . $this->_tableName . $whereClause;
        if ($dbh->executeSql($sql, $whereParam) === false) {
            return false;
        }
        $this->_changeFlg = false;
        $this->_fromHydrateFlg = true;
        return true;
    }
    
    /**
     * オブジェクトに対応する行に論理削除的updateを発行する
     * 
     * @access public
     * @param PhateDBO $dbh
     * @return boolean
     */
    public function logicDelete(PhateDBO $dbh)
    {
        // readonlyDBは処理禁止
        if ($dbh->isReadOnly()) {
            throw new PhateDatabaseException('this database is readonly');
        }
        // deletedカラムの確認
        if (!array_key_exists('deleted', $this->_value) || ($this->_value['deleted'] == 1)) {
            return false;
        }
        // hydrate済みか確認
        if (!$this->_fromHydrateFlg) {
            return false;
        }
        $whereClause = '';
        $whereParam = array();
        foreach ($this->_pkey as $key) {
            $whereClause .= $whereClause == '' ? ' WHERE ' : ' AND ';
            $whereClause .= $key . ' = ?';
            $whereParam[] = $this->_value[$key];
        }
        $modifiedClause = '';
        if (array_key_exists('modified', $this->_value)) {
            $modifiedClause = ",modified = '" . PhateTimer::getDateTime() . "' ";
        }
        $sql = 'UPDATE ' . $this->_tableName . ' SET deleted = 1 ' . $modifiedClause . $whereClause;
        if ($dbh->executeSql($sql, $whereParam) === false) {
            return false;
        }
        $this->_changeFlg = false;
        $this->_fromHydrateFlg = true;
        $this->_value['deleted'] = 1;
        if (array_key_exists('modified', $this->_value)) {
            $this->_value['modified'] = PhateTimer::getDateTime();
        }
        $this->_toSave = $this->_value;
        return true;
    }
}
