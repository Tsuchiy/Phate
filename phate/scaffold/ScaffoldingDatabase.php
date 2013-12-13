<?php
/**
 * scaffoldingDatabaseクラス
 *
 * o-rmapperのscaffolfolding機能実装クラス
 *
 * @package PhateFramework scaffolding
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class scaffoldingDatabase
{
    /**
     * o-rmapper自動生成実行
     * 
     * @param type $config
     */
    public function execute($config)
    {
        $projectName = array_shift($config);
        $peerDirectory = PROJECT_ROOT . '/models/database/';
        if (!file_exists($peerDirectory)) {
            mkdir(PROJECT_ROOT . '/models/database');
        }
        $ormBaseDirectory = PROJECT_ROOT . '/models/database/ormBase/';
        if (!file_exists($ormBaseDirectory)) {
            mkdir(PROJECT_ROOT . '/models/database/ormBase');
        }
        foreach ($config as $databaseName => $tmp) {
            $slaveDatabaseName = $tmp['slave_name'];
            $tableArray = $tmp['tables'];
            echo "main  : " . $databaseName . " : \n";
            echo "slave : " . $slaveDatabaseName . " : \n";
            if ($tmp['sharding'] == true) {
                $dbh = PhateDB::getInstanceByShardId($databaseName, 0);
            } else {
                $dbh = PhateDB::getInstance($databaseName);
            }
            foreach ($tableArray as $table) {
                // テーブル情報取得
                $tableName = $table['table_name'];
                echo $tableName . " exporting ...";
                $readOnly = $table['read_only'];
                $className = ucfirst($projectName) . PhateCommon::pascalizeString($tableName);
                if (preg_match('/^.*[A-Z]$/', $className)) {
                    $className = substr($className, 0 , -1);
                }
                $sql = 'SHOW COLUMNS FROM ' . $tableName;
                if (!($columnStatus = $dbh->getAll($sql))) {
                    echo 'check your yaml (table_name:' . $tableName . ")\n";
                    exit();
                }
                $pkIsRowId = 'false';
                $pkeys = array();
                $pkeysCamel = array();
                $values = array();
                foreach ($columnStatus as $column) {
                    if (strstr($column['Extra'],'auto_increment') !== false) {
                        $pkIsRowId = 'true';
                    }
                    if (strstr($column['Key'], 'PRI') !== false) {
                        $pkeys[] = $column['Field'];
                        $pkeysCamel[] = PhateCommon::camelizeString($column['Field']);
                    }
                    $values[$column['Field']] = $column['Default'];
                }
                $whereClause = implode(' = ? AND ', $pkeys) . ' = ? ';
                $pkeysList = $pkeysCamel ? '$' . implode(', $', $pkeysCamel) : '';
                $pkeysArgList = $pkeysCamel ? '$' . implode(', $', $pkeysCamel) . ',' : '';
                $memkeyPkeys = '$' . implode(" . '_' . $", $pkeysCamel);
                // ormapperBaseClass
                if (!file_exists($ormBaseDirectory . $className . 'OrmBase.class.php')) {
                    touch($ormBaseDirectory . $className . 'OrmBase.class.php');
                }
                if ($tmp['sharding'] == true) {
                    $str = file_get_contents(PHATE_SCAFFOLD_DIR . 'database/ShardOrMapperBaseDesignBase');
                } else {
                    $str = file_get_contents(PHATE_SCAFFOLD_DIR . 'database/OrMapperBaseDesignBase');
                }
                $str = str_replace('%%className%%', $className, $str);
                $str = str_replace('%%tableName%%', $tableName, $str);
                $pkeyStatement = '';
                foreach ($pkeys as $pkey) {
                    $pkeyStatement .= "        '" . $pkey . "',\n";
                }
                $str = str_replace('%%pkey%%', $pkeyStatement, $str);
                $str = str_replace('%%pkeys%%', $pkeysList, $str);
                $str = str_replace('%%pkeysArg%%', $pkeysArgList, $str);
                $str = str_replace('%%pkIsRowId%%', $pkIsRowId, $str);
                $str = str_replace('%%slaveDatabaseName%%', $slaveDatabaseName, $str);
                $str = str_replace('%%pureTableName%%', $tableName, $str);
                $str = str_replace('%%pkeyWhere%%', $whereClause, $str);
                $valueStatement = '';
                $methodStatement = '';
                foreach ($values as $columnName => $defaultValue) {
                    $valueStatement .= "        '" . $columnName . "' => ";
                    if ((string)$defaultValue === '') {
                        $valueStatement .= "null,\n";
                    } else {
                        $valueStatement .= "'" . $defaultValue . "',\n";
                    }
                    
                    $methodStatement .= '    public function get' . PhateCommon::pascalizeString($columnName) ."()\n";
                    $methodStatement .= '    {' . "\n";
                    $methodStatement .= '        return $this->_toSave[\'' . $columnName . '\'];' . "\n";
                    $methodStatement .= '    }' . "\n";
                    $methodStatement .= '    ' . "\n";
                    $methodStatement .= '    public function set' . PhateCommon::pascalizeString($columnName) .'($value)' . "\n";
                    $methodStatement .= '    {' . "\n";
                    $methodStatement .= '        if ($this->_value[\'' . $columnName . '\'] != $value) {' . "\n";
                    $methodStatement .= '            $this->_changeFlg = true;' . "\n";
                    $methodStatement .= '        }' . "\n";
                    $methodStatement .= '        $this->_toSave[\'' . $columnName . '\'] = $value;' . "\n";
                    $methodStatement .= '    }' . "\n";
                    $methodStatement .= '    ' . "\n";
                }
                $str = str_replace('%%value%%', $valueStatement, $str);
                $str = "<?php\n" . $str . $methodStatement . '}' . "\n";
                file_put_contents($ormBaseDirectory . $className . 'OrmBase.class.php', $str);
                // ormapperClass
                if (!file_exists($peerDirectory . $className . 'Orm.class.php')) {
                    $str = file_get_contents(PHATE_SCAFFOLD_DIR . 'database/OrMapperDesignBase');
                    $str = str_replace('%%className%%', $className, $str);
                    $str = str_replace('%%tableName%%', $tableName, $str);
                    $oRMapperMethod = '';
                    if ($readOnly) {
                        $findStatement  = 'find(' . $pkeysArgList;
                        if ($tmp['sharding'] == true) {
                            $findStatement .= ' $shardId = null,';
                        }
                        $oRMapperMethod  = '    public function ' . $findStatement . ' PhateDBO $dbh = null)' . "\n";
                        $oRMapperMethod .= "    {\n";
                        $oRMapperMethod .= '        $memcacheKey = \'' . $className . 'Orm:row:\' . ' . $memkeyPkeys . ";\n";
                        $oRMapperMethod .= '        if (($row = PhateMemcached::get($memcacheKey, \'db\'))) {' . "\n";
                        $oRMapperMethod .= '            $this->hydrate($row);' . "\n";
                        $oRMapperMethod .= '            return true;' . "\n";
                        $oRMapperMethod .= '        }' . "\n";
                        if ($tmp['sharding'] == true) {
                            $oRMapperMethod .= '        if (is_null($dbh)) {' . "\n";
                            $oRMapperMethod .= '            if (is_null($shardId)) {' . "\n";
                            $oRMapperMethod .= '                throw new PhateDatabaseException(\'shardId empty\');' . "\n";
                            $oRMapperMethod .= '            }' . "\n";
                            $oRMapperMethod .= '            $dbh = PhateDB::getInstanceByShardId(\'' . $slaveDatabaseName . '\', $shardId);' . "\n";
                            $oRMapperMethod .= '        }' . "\n";
                        } else {
                            $oRMapperMethod .= '        $dbh = $dbh ? $dbh : PhateDB::getInstance(\'' . $slaveDatabaseName . '\');' . "\n";
                        }
                        $oRMapperMethod .= '        $params = array(' . $pkeysList . ');' . "\n";
                        $oRMapperMethod .= '        $sql = "SELECT * FROM ' . $tableName . ' WHERE ' . $whereClause . '";' . "\n";
                        $oRMapperMethod .= '        if (($row = $dbh->getRow($sql, $params)) === false) {' . "\n";
                        $oRMapperMethod .= '            return false;' . "\n";
                        $oRMapperMethod .= "        }\n";
                        $oRMapperMethod .= '        PhateMemcached::set($memcacheKey, $row, 0, \'db\');' . "\n";
                        $oRMapperMethod .= '        $this->hydrate($row);' . "\n";
                        $oRMapperMethod .= '        return true;' . "\n";
                        $oRMapperMethod .= "    }\n";
                    }
                    $str = "<?php\n" . str_replace('%%ORMapperMethod%%', $oRMapperMethod, $str);
                    file_put_contents($peerDirectory . $className . 'Orm.class.php', $str);
                }
                // peerClass
                if (!file_exists($peerDirectory . $className . 'Peer.class.php')) {
                    if ($tmp['sharding'] == true) {
                        if ($readOnly) {
                            $str = file_get_contents(PHATE_SCAFFOLD_DIR . 'database/ShardPeerRODesignBase');
                        } else {
                            $str = file_get_contents(PHATE_SCAFFOLD_DIR . 'database/ShardPeerDesignBase');
                        }
                    } else {
                        if ($readOnly) {
                            $str = file_get_contents(PHATE_SCAFFOLD_DIR . 'database/PeerRODesignBase');
                        } else {
                            $str = file_get_contents(PHATE_SCAFFOLD_DIR . 'database/PeerDesignBase');
                        }
                    }
                    $str = str_replace('%%tableName%%', $tableName, $str);
                    $str = str_replace('%%className%%', $className, $str);
                    $str = str_replace('%%pkeys%%', $pkeysList, $str);
                    $str = str_replace('%%pkeysArg%%', $pkeysArgList, $str);
                    $str = str_replace('%%databaseName%%', $databaseName, $str);
                    $str = str_replace('%%slaveDatabaseName%%', $slaveDatabaseName, $str);
                    $str = str_replace('%%pureTableName%%', $tableName, $str);
                    $str = str_replace('%%pkeyWhere%%', $whereClause, $str);
                    $str = str_replace('%%memkeyPkeys%%', $memkeyPkeys, $str);
                    $str = "<?php\n" . $str;
                    file_put_contents($peerDirectory . $className . 'Peer.class.php', $str);
                }
            echo " done \n";
            }
        }
    }
    
}