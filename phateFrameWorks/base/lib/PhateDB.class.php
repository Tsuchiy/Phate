<?php
namespace Phate;
/**
 * DBクラス
 *
 * 設定ファイルを元にDBへの接続済みのDBOを作成するクラス
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class DB
{
    private static $_shardConfig;
    private static $_instanceConfig;
    private static $_instancePool;
    public static $instanceReadOnly;
    public static $instancePersistent;
    
    /**
     * 設定ファイルよりdatabaseの設定を取得
     * @return void
     */
    protected static function setConfig()
    {
        if (!($fileName = Core::getConfigure('database_config_file'))) {
            throw new CommonException('no database configure');
        }
        if (!($config = Common::parseConfigYaml(PHATE_CONFIG_DIR . $fileName))) {
            throw new CommonException('no database configure');
        }
        
        foreach ($config as $key => $arr) {
            self::developConfig($key, $arr);
        }
    }
    /**
     * 設定の階層を展開
     * 
     * @param string $key
     * @param array $value
     * @return void
     */
    private static function developConfig($key, array $value) {
        if (array_key_exists('servers', $value)) {
            self::$_shardConfig[$key] = array_keys($value['servers']);
            foreach ($value['servers'] as $k => $v) {
                self::developConfig($k, $v);
            }
        } else {
            self::$_instanceConfig[$key] = $value;
        }
        return;
    }
    
    /**
     * 接続名のPDOインスタンスを返す
     * @param string $namespace
     * @return DBO DBObject
     */
    public static function getInstance($namespace)
    {
        if (!self::$_instanceConfig) {
            self::setConfig();
        }
        if (!in_array($namespace, array_keys(self::$_instanceConfig))) {
            // シャーディング（というよりデュプリケートスレーブ）の場合は任意のDBに
            if (in_array($namespace, array_keys(self::$_shardConfig))) {

                $shardId = mt_rand(0, self::getNumberOfShard($namespace) - 1);
                return self::getInstanceByShardId($namespace, $shardId);
            }
            throw new DatabaseException('no database configure for namespace"' . $namespace . '"');
        }
        if (!isset(self::$_instancePool[$namespace])) {
            $dsn  = 'mysql:';
            $dsn .= 'host=' . self::$_instanceConfig[$namespace]['host'] . ';';
            $dsn .= 'port=' . self::$_instanceConfig[$namespace]['port'] . ';';
            $dsn .= 'dbname=' . self::$_instanceConfig[$namespace]['dbname'] . ';';
            $dsn .= 'charset=utf8';
            $user = self::$_instanceConfig[$namespace]['user'];
            $password = self::$_instanceConfig[$namespace]['password'];
            $attr = [
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ];
            $persistent = false;
            if (array_key_exists('persistent', self::$_instanceConfig[$namespace]) && (self::$_instanceConfig[$namespace]['persistent'] == true)) {
                $attr[\PDO::ATTR_PERSISTENT] = true;
                $persistent = true;
            }
            $instance = new DBO($dsn, $user, $password, $attr);
            /** cloneがsegfault起こすのでコメントアウト
             * self::$_instancePool[$namespace] = $instance->getNameSpace() ? clone $instance : $instance;
             */
            self::$_instancePool[$namespace] = $instance;
            self::$_instancePool[$namespace]->setNamespace($namespace);
            self::$instancePersistent[$namespace] = $persistent;
            self::$instanceReadOnly[$namespace] = self::$_instanceConfig[$namespace]['read_only'];
        }
        return self::$_instancePool[$namespace];
    }

    /**
     * shardのDBOを取得
     * @param string $namespace
     * @param int $shardId
     * @return DBO DBObject
     */
    public static function getInstanceByShardId($namespace, $shardId)
    {
        if (!self::$_shardConfig) {
            self::setConfig();
        }
        if (!in_array($namespace, array_keys(self::$_shardConfig))) {
            throw new DatabaseException('no database configure for namespace"' . $namespace . '"');
        }
        if (!in_array($shardId, array_keys(self::$_shardConfig[$namespace]))) {
            throw new DatabaseException('no shard ID " ' . $shardId . ' on ' . $namespace . '"');
        }
        $databaseName = self::$_shardConfig[$namespace][$shardId];
        return self::getInstance($databaseName);
    }
    
    /**
     * shardの分割数を取得
     * @param string $namespace
     * @return int
     */
    public static function getNumberOfShard($namespace)
    {
        if (!self::$_shardConfig) {
            self::setConfig();
        }
        if (!in_array($namespace, array_keys(self::$_shardConfig))) {
            throw new DatabaseException('no database configure for namespace"' . $namespace . '"');
        }
        return count(self::$_shardConfig[$namespace]);
    }
    
    /**
     * インスタンスプールのコネクトを切断する
     * @return void
     */
    public static function disconnect()
    {
        if (!isset(self::$_instancePool) || !is_array(self::$_instancePool)) {
            return;
        }
        foreach (self::$_instancePool as $key => $instance) {
            if (!self::$instancePersistent[$key]) {
                unset(self::$_instancePool[$key]);
            }
        }
        unset($instance);
    }
}

/**
 * DBOクラス
 *
 * PDOクラスにPearライクなメソッドを幾つか追加したDBObject
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class DBO extends \PDO
{
    protected $_namespace = '';
    protected $_transactionLevel = 0;
    protected $_rollbackFlg = false;
    
    /**
     * namespaceをセットする
     * @param String $namespace
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
    }
    
    /**
     * namespaceをゲットする
     * @return String $namespace
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }
    
    /**
     * このインスタンスがreadonlyかを返す
     * @return boolean
     */
    public function isReadOnly()
    {
        return DB::$instanceReadOnly[$this->getNamespace()];
    }
    
    /**
     * このインスタンスがpersistentかを返す
     * @return boolean
     */
    public function isPersistent()
    {
        return DB::$instancePersistent[$this->getNamespace()];
    }
    
    /**
     * 多重トランザクション対応
     * @return boolean
     */
    public function beginTransaction()
    {
        if ($this->_transactionLevel < 0) {
            throw new DatabaseException('begin transaction exception');
        }
        if ($this->_transactionLevel === 0) {
            if (parent::beginTransaction() === true) {
                ++$this->_transactionLevel;
                return true;
            }
            return false;
        }
        ++$this->_transactionLevel;
        return true;
    }

    /**
     * 多重トランザクション対応
     * @return boolean
     */
    public function commit()
    {
        if (--$this->_transactionLevel === 0) {
            if ($this->_rollbackFlg) {
                throw new DatabaseException('rollback called before commit (multi transaction)');
            }
            return parent::commit();
        } elseif ($this->_transactionLevel < 0) {
            throw new DatabaseException('commit,in not toransaction');
        }
        return true;
    }
    
    /**
     * 多重トランザクション対応
     * @return boolean
     */
    public function rollBack()
    {
        if (--$this->_transactionLevel === 0) {
            $this->_rollbackFlg = false;
            return parent::rollBack();
        } elseif ($this->_transactionLevel < 0) {
            throw new DatabaseException('rollback,in not toransaction');
        }
        $this->_rollbackFlg = true;
        return true;
    }

    /**
     * SQLの実行
     * @param string $sql
     * @param array $params
     * @return boolean
     */
    public function executeSql($sql, array $params = [])
    {
        if (($stmt = $this->prepare($sql)) === false) {
            return false;
        }
        return $stmt->execute($params);
    }
    
    /**
     * SQLを実行し結果を一行取得する
     * @param type $sql
     * @param array $params
     * @return boolean/array
     */
    public function getRow($sql, array $params = [])
    {
        if (($stmt = $this->prepare($sql)) === false) {
            return false;
        }
        if ($stmt->execute($params) === false) {
            return false;
        }
        return $stmt->fetch();
    }
    
    /**
     * SQLを実行し、全行取得する
     * @param string $sql
     * @param array $params
     * @return boolean/array
     */
    public function getAll($sql, array $params = [])
    {
        if (($stmt = $this->prepare($sql)) === false) {
            return false;
        }
        //echo $sql;
        if ($stmt->execute($params) === false) {
            return false;
        }
        return $stmt->fetchAll();
    }
    
    /**
     * SQLを実行し、最初の1カラムを取得する
     * @param string $sql
     * @param array $params
     * @return boolean/string
     */
    public function getOne($sql, array $params = [])
    {
        if (($stmt = $this->prepare($sql)) === false) {
            return false;
        }
        if ($stmt->execute($params) === false) {
            return false;
        }
        return $stmt->fetchColumn();
    }
    
    /**
     * SQLを実行し、指定したカラムを配列として取得する
     * @param stiring $sql
     * @param string $columnName
     * @param array $params
     * @return boolean/array
     */
    public function getCol($sql, $columnName, array $params = [])
    {
        if (($stmt = $this->prepare($sql)) === false) {
            return false;
        }
        if ($stmt->execute($params) === false) {
            return false;
        }
        $all = $stmt->fetchAll();
        if ($all === false) {
            return false;
        }
        $rtn = [];
        foreach ($all as $v) {
            $rtn[] = $v[$columnName];
        }
        return $rtn;
    }
    
    /**
     * replace into 文を擬似実行する
     * @param string $tableName
     * @param array $keyParams
     * @param array $params
     * @return boolean
     */
    public function replace($tableName, array $keyParams, array $params = [])
    {
        if ($keyParams) {
            $whereClause = ' WHERE ';
            $bindKeyValues = array();
            foreach ($keyParams as $k => $v) {
                $whereClause .= count($bindKeyValues) == 0 ? '' : ' AND ';
                $whereClause .= $k . ' = ? ';
                $bindKeyValues[] = $v;
            }
            $sql = 'SELECT count(1) as cnt FROM ' . $tableName . $whereClause;
            $cnt = $this->getOne($sql, $keyParams);
            $bindValues = array();
        } else {
            $cnt = 0;
        }
        if ($cnt > 0) {
            // update
            $sql = 
            $paramClause = '';
            foreach ($params as $k => $v) {
                $paramClause .= $paramClause == '' ? ' SET ' : ' , ';
                $paramClause .= $k . ' = ? ';
                $bindValues[]  = $v;
            }
            $sql = 'UPDATE ' . $tableName . $paramClause . $whereClause;
            $bindValues = array_merge($bindValues, $bindKeyValues);
        } else {
            // insert
            $columnClause = '';
            $bindClause = '';
            foreach ($keyParams as $k => $v) {
                $columnClause .= $columnClause ? ' , ' : ' ( ';
                $bindClause .= $bindClause ? ' , ' : ' VALUES ( ';
                $columnClause .= $k;
                $bindClause .= '?';
                $bindValues[] = $v;
            }
            foreach ($params as $k => $v) {
                $columnClause .= ',' . $k;
                $bindClause .= ' , ?';
                $bindValues[] = $v;
            }
            $columnClause .= ' ) ';
            $bindClause .= ' ) ';
            $sql = 'INSERT INTO ' . $tableName . $columnClause . $bindClause;
        }
        return $this->executeSql($sql, $bindValues);
    }
    
    /**
     * 
     * MySQLでmultipul insertを行う
     * 
     * @param string $tableName
     * @param array $dataArray
     * @param array $columnList
     * @return boolean
     * @throws DatabaseSQLException
     */
    public function multipulInsert($tableName, array $dataArray, array $columnList = [])
    {
        $param = [];
        if ($columnList) {
            $valueSql = '';
            $tmpArray = array_pad([], count($columnList), '?');
            foreach ($dataArray as $dataRow) {
                $valueSql .=',(' . implode(',', $tmpArray) . ')';
                foreach ($columnList as $column) {
                    if (($param[] = array_shift($dataRow)) === false) {
                        throw new DatabaseSQLException('illegal data array');
                    }
                }
            }
            $sql = 'INSERT INTO ' . $tableName . ' (`' . implode('`,`', $columnList) . '`) VALUES ' . substr($valueSql, 1);
        } else {
            $columns = [];
            foreach ($dataArray as $dataRow) {
                $columns = array_unique(array_merge($columns, array_keys($dataRow)));
            }
            $valueSql = '';
            $tmpArray = array_pad([], count($columns), '?');
            foreach ($dataArray as $dataRow) {
                $valueSql .=',(' . implode(',', $tmpArray) . ')';
                foreach ($columns as $column) {
                    $param[] = isset($dataRow[$column]) ? $dataRow[$column] : null;
                }
            }
            $sql = 'INSERT INTO ' . $tableName . ' (`' . implode('`,`', $columns) . '`) VALUES ' . substr($valueSql, 1);
        }
        
        return $this->executeSql($sql, $param);
    }
}

/**
 * DatabaseSQLException例外
 *
 * データベース実行時のSQLでの例外
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
 class DatabaseSQLException extends DatabaseException
{
}
