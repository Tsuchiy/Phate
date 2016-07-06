<?php
namespace Phate;

/**
 * ORMapperBaseクラス
 *
 * O-RMapperの先祖クラス。基礎パラメータと基礎メソッド群。
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class ORMapperBase
{
    protected $_tableName;
    
    protected $_pkey = [];
    
    protected $_pkeyIsRowId = false;
    
    protected $_value = [];
    
    protected $_type = [];
    
    protected $_toSave = [];
    
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
        if (!array_key_exists($name, $this->_toSave)) {
            throw new DatabaseException('column not found');
        }
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
        if (!array_key_exists($name, $this->_value)) {
            throw new DatabaseException('column not found');
        }
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
        foreach ($this->_value as $column => $value) {
            if (array_key_exists($column, $row)) {
                $this->_value[$column] = $row[$column];
                $this->_toSave[$column] = $row[$column];
            }
        }
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
     * @param DBO $dbh
     * @return boolean
     */
    public function save(DBO $dbh)
    {
        // readonlyDBは処理禁止 -> cloneでsegfault起こすのでここの処理は省く
        //if ($dbh->isReadOnly()) {
        //    throw new DatabaseException('this database is readonly');
        //}
        // hydrate後に変更がない場合はなにもしない
        if ($this->_fromHydrateFlg && !$this->_changeFlg) {
            return false;
        }
        // "modified"カラムは特別扱い
        if (array_key_exists('modified', $this->_toSave) && ($this->_toSave['modified'] === $this->_value['modified'])) {
            $this->_toSave['modified'] = Timer::getDateTime();
        }
        // "updated"カラムは特別扱い
        if (array_key_exists('updated', $this->_toSave) && ($this->_toSave['updated'] === $this->_value['updated'])) {
            $this->_toSave['updated'] = Timer::getDateTime();
        }
        // "updated_at"カラムは特別扱い
        if (array_key_exists('updated_at', $this->_toSave) && ($this->_toSave['updated_at'] === $this->_value['updated_at'])) {
            $this->_toSave['updated_at'] = Timer::getDateTime();
        }
        // insertの場合
        if (!$this->_fromHydrateFlg) {
            // "created"カラムは特別扱い
            if (array_key_exists('created', $this->_toSave) && is_null($this->_toSave['created'])) {
                $this->_toSave['created'] = Timer::getDateTime();
            }
            // "inserted"カラムは特別扱い
            if (array_key_exists('inserted', $this->_toSave) && is_null($this->_toSave['inserted'])) {
                $this->_toSave['inserted'] = Timer::getDateTime();
            }
            // "created_at"カラムは特別扱い
            if (array_key_exists('created_at', $this->_toSave) && is_null($this->_toSave['created_at'])) {
                $this->_toSave['created_at'] = Timer::getDateTime();
            }
            // "inserted"カラムは特別扱い
            if (array_key_exists('inserted_at', $this->_toSave) && is_null($this->_toSave['inserted_at'])) {
                $this->_toSave['inserted_at'] = Timer::getDateTime();
            }
            // autoincrementに新規行を追加するとき
            $toSave = $this->_toSave;
            if ($this->_pkeyIsRowId && is_null($this->_toSave[$this->_pkey[0]])) {
                $pkey = $this->_pkey[0];
                unset($toSave[$pkey]);
            }
            $columns = array_keys($toSave);
            $columnClause = '(' . implode(',', $columns) . ')';
            $placeClause = str_repeat('?,', count($toSave));
            $placeClause = '(' . substr($placeClause, 0, -1) . ')';
            $sql = 'INSERT INTO ' .$this->_tableName . ' ' . $columnClause . ' VALUES ' . $placeClause;
            $sth = $dbh->prepare($sql);
            $i = 0;
            foreach ($columns as $column) {
                $value = $this->_toSave[$column];
                if (isset($this->_type[$column])) {
                    $sth->bindValue(++$i, $value, $this->_type[$column]);
                } else {
                    $sth->bindValue(++$i, $value, \PDO::PARAM_STR);
                }
            }
            if ($sth->execute() === false) {
                return false;
            }
            if ($this->_pkeyIsRowId && is_null($this->_toSave[$this->_pkey[0]])) {
                $this->_toSave[$pkey] = $dbh->lastInsertId();
            }
        } else {
            // updateの場合
            $setClause = '';
            $setParam = [];
            foreach ($this->_toSave as $key => $value) {
                $setClause .= $setClause == '' ? ' SET ' : ' , ';
                $setClause .= $key .' = ? ';
                $setParam[$key] = $value;
            }
            $whereClause = '';
            $whereParam = [];
            foreach ($this->_pkey as $key) {
                $whereClause .= $whereClause == '' ? ' WHERE ' : ' AND ';
                $whereClause .= $key . ' = ? ';
                $whereParam[$key] = $this->_value[$key];
            }
            $sql = 'UPDATE ' . $this->_tableName . $setClause . $whereClause;
            $sth = $dbh->prepare($sql);
            $i = 0;
            foreach ($setParam as $column => $value) {
                if (isset($this->_type[$column])) {
                    $sth->bindValue(++$i, $value, $this->_type[$column]);
                } else {
                    $sth->bindValue(++$i, $value, \PDO::PARAM_STR);
                }
            }
            foreach ($whereParam as $column => $value) {
                if (isset($this->_type[$column])) {
                    $sth->bindValue(++$i, $value, $this->_type[$column]);
                } else {
                    $sth->bindValue(++$i, $value, \PDO::PARAM_STR);
                }
            }
            if ($sth->execute() === false) {
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
     * @param DBO $dbh
     * @return boolean
     */
    public function delete(DBO $dbh)
    {
        // readonlyDBは処理禁止
        if ($dbh->isReadOnly()) {
            throw new DatabaseException('this database is readonly');
        }
        // hydrate済みか確認
        if (!$this->_fromHydrateFlg) {
            return false;
        }
        $whereClause = '';
        foreach ($this->_pkey as $key) {
            $whereClause .= $whereClause == '' ? ' WHERE ' : ' AND ';
            $whereClause .= $key . ' = ?';
        }
        $sql = 'DELETE FROM ' . $this->_tableName . $whereClause;
        $sth = $dbh->prepare($sql);
        $i = 0;
        foreach ($this->_pkey as $column) {
            if (isset($this->_type[$column])) {
                $sth->bindValue(++$i, $this->_value[$column], $this->_type[$column]);
            } else {
                $sth->bindValue(++$i, $this->_value[$column], \PDO::PARAM_STR);
            }
        }
        if ($sth->execute() === false) {
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
     * @param DBO $dbh
     * @return boolean
     */
    public function logicDelete(DBO $dbh)
    {
        // readonlyDBは処理禁止
        if ($dbh->isReadOnly()) {
            throw new DatabaseException('this database is readonly');
        }
        // deleted,delete_flgカラムの確認
        if (!array_key_exists('delete_flg', $this->_value) && !array_key_exists('deleted', $this->_value)) {
            return false;
        }
        if ((array_key_exists('delete_flg', $this->_value) && $this->_value['delete_flg'] == 1)
                && (array_key_exists('deleted', $this->_value) && $this->_value['deleted'] == 1)) {
            return false;
        }
                
        // hydrate済みか確認
        if (!$this->_fromHydrateFlg) {
            return false;
        }
        $whereClause = '';
        foreach ($this->_pkey as $key) {
            $whereClause .= $whereClause == '' ? ' WHERE ' : ' AND ';
            $whereClause .= $key . ' = ?';
        }
        $modifiedClause = '';

        // "modified"カラムは特別扱い
        if (array_key_exists('modified', $this->_value)) {
            $modifiedClause = ",modified = '" . Timer::getDateTime() . "' ";
        }
        // "updated"カラムは特別扱い
        if (array_key_exists('updated', $this->_value)) {
            $modifiedClause = ",updated = '" . Timer::getDateTime() . "' ";
        }
        // "updated_at"カラムは特別扱い
        if (array_key_exists('updated_at', $this->_value)) {
            $modifiedClause = ",updated_at = '" . Timer::getDateTime() . "' ";
        }
        // delete部分SQL
        $tmpArr=[];
        if (array_key_exists('deleted', $this->_value)) {
            $tmpArr[] = 'deleted';
        }
        if (array_key_exists('delete_flg', $this->_value)) {
            $tmpArr[] = 'delete_flg';
        }
        $deleteClause = implode('= 1 AND ', $tmpArr) . ' = 1 ';
        
        $sql = 'UPDATE ' . $this->_tableName . ' SET ' . $deleteClause . $modifiedClause . $whereClause;
        Logger::info($sql);
        $sth = $dbh->prepare($sql);
        $i = 0;
        foreach ($this->_pkey as $column) {
            if (isset($this->_type[$column])) {
                $sth->bindValue(++$i, $this->_value[$column], $this->_type[$column]);
            } else {
                $sth->bindValue(++$i, $this->_value[$column], \PDO::PARAM_STR);
            }
        }
        if ($sth->execute() === false) {
            return false;
        }

        // 更新後処理
        $this->_value['deleted'] = 1;
        // "modified"カラムは特別扱い
        if (array_key_exists('modified', $this->_value)) {
            $this->_value['modified'] = Timer::getDateTime();
        }
        // "updated"カラムは特別扱い
        if (array_key_exists('updated', $this->_value)) {
            $this->_value['updated'] = Timer::getDateTime();
        }
        // "updated_at"カラムは特別扱い
        if (array_key_exists('updated_at', $this->_value)) {
            $this->_value['updated_at'] = Timer::getDateTime();
        }
        
        $this->_changeFlg = false;
        $this->_fromHydrateFlg = true;
        $this->_toSave = $this->_value;
        return true;
    }
}
