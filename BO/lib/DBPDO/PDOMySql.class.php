<?php

/**
 * Driver PDO Postgres
 *
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class PDOMySql extends PDODriver {

    static $dbmsBaseTypes = array(
        'bigint' => array('pdo' => PDO::PARAM_INT, 'php' => 'numeric'),
        'tinyint' => array('pdo' => PDO::PARAM_INT, 'php' => 'numeric'),
        'smallint' => array('pdo' => PDO::PARAM_INT, 'php' => 'numeric'),
        'mediumint' => array('pdo' => PDO::PARAM_INT, 'php' => 'numeric'),
        'int' => array('pdo' => PDO::PARAM_INT, 'php' => 'numeric'),
        'double' => array('pdo' => PDO::PARAM_STR, 'php' => 'numeric'),
        'float' => array('pdo' => PDO::PARAM_STR, 'php' => 'numeric'),
        'decimal' => array('pdo' => PDO::PARAM_STR, 'php' => 'numeric'),
        'char' => array('pdo' => PDO::PARAM_STR, 'php' => 'char'),
        'varchar' => array('pdo' => PDO::PARAM_STR, 'php' => 'char'),
        'text' => array('pdo' => PDO::PARAM_STR, 'php' => 'char'),
        'longtext' => array('pdo' => PDO::PARAM_STR, 'php' => 'char'),
        'tinytext' => array('pdo' => PDO::PARAM_STR, 'php' => 'char'),
        'mediumtext' => array('pdo' => PDO::PARAM_STR, 'php' => 'char'),
        'binary' => array('pdo' => PDO::PARAM_LOB, 'php' => 'binary'),
        'date' => array('pdo' => PDO::PARAM_STR, 'php' => 'date'),
        'datetime' => array('pdo' => PDO::PARAM_STR, 'php' => 'date'),
        'time' => array('pdo' => PDO::PARAM_STR, 'php' => 'date'),
        'timestamp' => array('pdo' => PDO::PARAM_STR, 'php' => 'date')
            /*
             * Aggiungere tutti i tipi vedi documentazione
             */
    );
    static $castTypes = array(
        'int' => 'int',
        'varchar' => 'varchar',
        'double' => 'double'
    );

    public function getDbmsBaseTypes() {
        return self::$dbmsBaseTypes;
    }

    protected function createPDOObject($dbname, $host, $port, $user, $password) {
        $port = $port === NULL ? 3306 : $port;
        if (!$port) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_MISSING_PORT);
        }
        if (!$user) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_MISSING_USER);
        }
        if (!$password) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_MISSING_PASSWORD);
        }
        return new PDO("mysql:host=$host;dbname=$dbname;port=$port", $user, $password);
    }

    protected function initCustomDBAttributes() {
        //Timeout connessione in secondi non valido per sql Server 
        if (array_key_exists('timeout', $this->sqlparams)) {
            $this->linkid->setAttribute(PDO::ATTR_TIMEOUT, $this->sqlparams["timeout"]);
        }
        
        if (array_key_exists('dbRealName', $this->sqlparams)) {
            $this->linkid->exec("USE " . $this->sqlparams['dbRealName']);
        }

        if (array_key_exists('charset', $this->sqlparams)) {
            //$this->linkid->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES $this->sqlparams['charset']");
            $this->linkid->exec("set names $this->sqlparams['charset']");
        } else {
            //$this->linkid->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES 'latin1'");
            $this->linkid->exec("set names latin1");
        }
    }
    
    public function setUseBufferedQuery($use) {
        $this->setCustomAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $use);
    }
    
    protected function safeOpenConnectionCustom() {
        if (array_key_exists('dbRealName', $this->sqlparams)) {
            $this->linkid->exec("USE " . $this->sqlparams['dbRealName']);
        }
    }

    public function queryCount($sql, $closeConnection = false, $params = array()) {
        try {
            $result = $this->queryRiga('SELECT COUNT(*) as ROWS FROM (' . $sql . ') AS SELEZIONE', $params, $closeConnection);
            return $result['ROWS'];
        } catch (ItaException $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, $ex->getNativeErrorCode(), $ex->getNativeErroreDesc());
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    protected function addLimitOffsetToQuery(&$sql, $da, $per) {
        $offset = $da; // - 1;
        $sql .= " LIMIT $offset, $per";
    }

    protected function preBeginTransaction($isolation) {
        switch ($isolation) {
            case 1:
                $sql = 'SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE"';
                break;
            case 2:
                $sql = 'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED';
                break;
        }
        if ($isolation > 0) {
            $this->execDirect($sql, false);
        }
    }

    public function getTablesInfo() {
        try {
            $sql = "SHOW TABLE STATUS FROM {$this->sqldb}";
            $statement = $this->query($sql);

            while ($row = PDO::FETCH_ASSOC($statement)) {
                $list[$row['Name']] = array(
                    'type' => $row['Engine'],
                    'rows' => $row['Rows'],
                    'create' => $row['Create_time'],
                    'update' => $row['Update_time'],
                    'size' => $row['Data_length'] + $row['Index_length']
                );
            }

            $this->eliminaQuery($statement);

            return $list;
        } catch (ItaException $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, $ex->getNativeErrorCode(), $ex->getNativeErroreDesc());
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    public function getColumnsInfo($tableName) {
        $sql = "SHOW COLUMNS FROM $tableName";

        $statement = $this->query($sql);
        $result = $this->fetch($statement, false, null);

        $dbmsTypes = $this->getDbmsBaseTypes();
        $list = array();
        foreach ($result as $row) {
            list($type, $len) = explode("(", $row['Type']);
            list($len, $skip) = explode(")", $len);
            $list[$row['Field']] = array(
                'type' => $type,
                'phpType' => $dbmsTypes[$type]['php'],
                'len' => $len,
                'null' => $row['Null'],
                'key' => $row['Key'],
                'default' => $row['Default'],
                'extra' => $row['Extra']
            );
        }

        $this->eliminaQuery($statement);

        return $list;
    }

    public function getTableInfo($tableName) {
        $list = $this->getColumnsInfo($tableName);
        $autoInc = FALSE;
        foreach ($list as $row) {
            if (strtolower($row['extra']) === 'auto_increment') {
                $autoInc = TRUE;
                break;
            }
        }
        if (!$autoInc) {
            $tableInfo = array('auto' => 0, 'sequenceName' => '');
        } else {
            $tableInfo = array('auto' => 1, 'sequenceName' => $row['Field']);   // Valorizza con il nome del campo autoincrement
        }

        return $tableInfo;
    }

    public function getPKs($tableName) {
        $sql = "SHOW KEYS FROM $tableName WHERE Key_name = 'PRIMARY'";

        $statement = $this->query($sql);

        $result = $this->fetch($statement, false, null);

        $pks = array();
        foreach ($result as $row) {
            $pks[] = $row['Column_name'];
        }

        $this->eliminaQuery($statement);

        return $pks;
    }

    public function listTables() {
        try {
            return $this->listTableExecute($this->sqldb);
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, $ex->getCode(), $ex->getMessage());
        }
    }

    function tableExists($tableName) {
//        $this->addSqlParam($sqlParams, 'SCHEMA', $this->sqldb);
//        $this->addSqlParam($sqlParams, 'TABLE_NAME', $tableName);
        return count($this->listTableExecute($this->sqldb, $tableName)) > 0;
    }

    private function listTableExecute($schema, $tableName = null) {
        $sql = "SHOW TABLES FROM $schema";
        if ($tableName) {
            $sql .= " LIKE $tableName";
        }
        $table_list = $this->queryMultipla($sql, true);
        array_walk($table_list, function(&$row, $key) {
            $row = $row[0];
        });
        return $table_list;
//        $list=array();
//        foreach ($table_list as $key => $value) {
//            $list[] = $value[0];
//        }
//        return $list;
    }

    public function getSqlCopyTable($tableFrom, $tableTo) {
        // https://dev.mysql.com/doc/refman/5.7/en/ansi-diff-select-into-table.html
        return "INSERT INTO $tableTo SELECT * FROM $tableFrom";
    }

    public function version() {
        try {
            $version = $this->queryRiga('SELECT version() as VERSION');
            return $version['VERSION'];
        } catch (ItaException $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, $ex->getNativeErrorCode(), $ex->getNativeErroreDesc());
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    public function nextval($sequence) {
        return 'itanextval(' . $sequence . ')';
    }

    public function strConcat($strArray, $strCount) {
        if ($strCount != 0) {
            return "CONCAT(" . implode(" , ", $strArray) . ")";
        } else {
            return "";
        }
    }

    public function strLower($value) {
        return "LOWER($value)";
    }

    public function strUpper($value) {
        return "UPPER($value)";
    }

    public function subString($value, $start, $len) {
        return "SUBSTR($value,$start,$len)";
    }

    public function strTrim($value) {
        return "TRIM($value)";
    }

    public function strLtrim($value) {
        return "LTRIM($value)";
    }

    public function strRtrim($value) {
        return "RTRIM($value)";
    }

    public function strLpad($value, $len, $padstr = ' ') {
        return "LPAD($value, $len, '$padstr')";
    }

    public function strRpad($value, $len, $padstr = ' ') {
        return "RPAD($value, $len, '$padstr')";
    }

    public function strCast($value, $type) {
        return "CAST($value AS " . self::$castTypes[$type] . ")";
    }

    public function dateDiff($beginDate, $endDate) {
        return "DATEDIFF(STR_TO_DATE($beginDate,'%Y%m%d'), STR_TO_DATE($endDate,'%Y%m%d'))";
    }

    public function year($value) {
        return "YEAR(" . $value . ")";
    }

    public function month($value) {
        return "MONTH(" . $value . ")";
    }

    public function day($value) {
        return "DAY(" . $value . ")";
    }

    public function module($field, $import) {
        return "mod(" . $field . ", " . $import . " )";
    }

    public function formatDate($value, $format = null) {
        if (!$format) {
            if (array_key_exists('dateFormat', $this->sqlparams)) {
                $format = $this->sqlparams['dateFormat'];
            } else {
                $format = 'DD-MM-YYYY';
            }
        }
        return "STR_TO_DATE($value, '$format')";
    }

    public function dateToString($value, $format = null) {
        if (!$format) {
            $format = 'YYYY-MM-DD';
        }
        return "DATE_FORMAT($value, '$format')";
    }

    public function getJdbcDriverClass() {
        return 'com.mysql.jdbc.Driver';
    }

    public function getJdbcConnectionUrl() {
        return "jdbc:mysql://" . $this->getServer() . "/" . $this->getDB();
    }

    public function prepareColumnsName(&$data) {
        // Nessuna operazione
    }

    public function getFormatDateTime() {
        return 'YYYY/MM/DD HH24:MI:SS';
    }

    public function adapterBlob($value = null) {
        return $value;
    }

    public function lockTableCommand($tableName, $exclusive = false) {
        If ($exclusive) {
            return "LOCK TABLES $tableName WRITE ";
        } else {
            return "LOCK TABLES $tableName READ ";
        }
    }

    public function lockRowTableCommand($sqlLockRowTable) {
        return $sqlLockRowTable;
    }

    protected function isErrorCodelockRowTable($ex) {
        return false;
    }

    public function isErrorCodeConstraintUnique($ex) {
        return false;
    }

}
