<?php
/**
 * PhateDBクラス
 *
 * 設定ファイルを元にDBへの接続済みのDBOを作成するクラス
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateDB
{
    private static $_config;
    private static $_shardConfig;
    private static $_instancePool;
    
    /**
     * 設定ファイルよりdatabaseの設定を取得
     * @return void
     */
    private static function setConfig()
    {
        $sysConf = PhateCore::getConfigure();
        if (!isset($sysConf['DATABASE']['load_yaml_file'])) {
            throw new PhateCommonException('no database configure');
        }
        $filename = PHATE_CONFIG_DIR . $sysConf['DATABASE']['load_yaml_file'];
        self::$_config = PhateCommon::parseConfigYaml($filename);
        self::$_shardConfig = array();
        foreach (self::$_config as $key => $value) {
            if ($value['sharding'] === true) {
                self::$_config = array_merge(self::$_config, $value['servers']);
                self::$_shardConfig[$key] = array();
                foreach ($value['servers'] as $k => $v) {
                    self::$_shardConfig[$key][] = $k;
                }
                unset(self::$_config[$key]);
            }
        }
    }
    
    /**
     * 接続名のPDOインスタンスを返す
     * @param string $name
     * @return PhateDBO DBObject
     */
    public static function getInstance($namespace)
    {
        if (!isset(self::$_instancePool[$namespace])) {
            if (!isset(self::$_config)) {
                self::setConfig();
            }
            if (!isset(self::$_config[$namespace])) {
                throw new PhateDatabaseException('cant resolv namespace');
            }
            $dsn  = 'mysql:';
            $dsn .= 'host=' . self::$_config[$namespace]['host'] . ';';
            $dsn .= 'port=' . self::$_config[$namespace]['port'] . ';';
            $dsn .= 'dbname=' . self::$_config[$namespace]['dbname'] . ';';
            $dsn .= 'charset=utf8';
            $user = self::$_config[$namespace]['user'];
            $password = self::$_config[$namespace]['password'];
            $attr = array(
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            );
            $persistent = false;
            if (array_key_exists('persistent', self::$_config[$namespace]) && (self::$_config[$namespace]['persistent'] == true)) {
                $attr[PDO::ATTR_PERSISTENT] = true;
                $persistent = true;
            }
            $instance = new PhateDBO($dsn, $user, $password, $attr);
            $instance->setReadOnly(self::$_config[$namespace]['read_only']);
            $instance->setPersistent($persistent);
            self::$_instancePool[$namespace] = $instance;
        }
        return self::$_instancePool[$namespace];
    }

    /**
     * shardのDBOを取得
     * @param string $name
     * @param int $shardId
     * @return PhateDBO DBObject
     */
    public static function getInstanceByShardId($name, $shardId)
    {
        if (!isset(self::$_shardConfig)) {
            self::setConfig();
        }
        if (!isset(self::$_shardConfig[$name][$shardId])) {
            throw new PhateDatabaseException('cant resolv namespace');
        }
        $databaseName = self::$_shardConfig[$name][$shardId];
        return self::getInstance($databaseName);
    }
    
    /**
     * shardの分割数を取得
     * @param string $name
     * @return int
     */
    public static function getNumberOfShard($name)
    {
        if (!isset(self::$_shardConfig)) {
            self::setConfig();
        }
        if (!array_key_exists($name, self::$_shardConfig) || !is_array(self::$_shardConfig[$name])) {
            throw new PhateDatabaseException('cant resolv namespace');
        }
        return count(self::$_shardConfig[$name]);
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
            if (!self::$_instancePool[$key]->isPersistent()) {
                unset(self::$_instancePool[$key]);
            }
        }
    }
}

/**
 * PhateDBOクラス
 *
 * PDOクラスにPearライクなメソッドを幾つか追加したDBObject
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateDBO extends PDO
{
    protected $_transactionLevel = 0;
    protected $_isReadOnly;
    protected $_isPersistent = false;
    
    /**
     * databaseがreadonlyかセットする
     * @param boolean $flg
     */
    public function setReadOnly($flg) {
        $this->_isReadOnly = is_bool($flg) ? $flg : false;
    }
    
    /**
     * このインスタンスがreadonlyかを返す
     * @return boolean
     */
    public function isReadOnly() {
        return $this->_isReadOnly;
    }
    /**
     * persistent接続かセットする
     * @param boolean $flg
     */
    public function setPersistent($flg) {
        $this->_isPersistent = is_bool($flg) ? $flg : false;
    }
    
    /**
     * このインスタンスがpersistentかを返す
     * @return boolean
     */
    public function isPersistent() {
        return $this->_isPersistent;
    }
    
    /**
     * 多重トランザクション対応
     * @return boolean
     */
    public function beginTransaction() {
        if ($this->_transactionLevel < 0) {
            throw new PhateDatabaseException('begin transaction exception');
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
    public function commit() {
        if (--$this->_transactionLevel === 0) {
            return parent::commit();
        } elseif ($this->_transactionLevel < 0) {
            throw new PhateDatabaseException('commit,in not toransaction');
        }
        return true;
    }
    
    /**
     * 多重トランザクション対応
     * @return boolean
     */
    public function rollBack() {
        if (--$this->_transactionLevel === 0) {
            return parent::rollBack();
        } elseif ($this->_transactionLevel < 0) {
            throw new PhateDatabaseException('rollback,in not toransaction');
        }
        return true;
    }

    /**
     * SQLの実行
     * @param string $sql
     * @param array $params
     * @return boolean
     */
    public function executeSql($sql, array $params)
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
    public function getRow($sql, array $params = array())
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
    public function getAll($sql, array $params = array())
    {
        if (($stmt = $this->prepare($sql)) === false) {
            return false;
        }
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
    public function getOne($sql, array $params = array())
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
    public function getCol($sql, $columnName, array $params = array())
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
        $rtn = array();
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
    public function replace($tableName, array $keyParams, array $params = array())
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
}

/**
 * PhateDatabaseException例外
 *
 * データベース関連の例外
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateDatabaseException extends PDOException
{
}

/**
 * PhateDatabaseSQLException例外
 *
 * データベース実行時のSQLでの例外
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
 class PhateDatabaseSQLException extends PhateDatabaseException
{
}
