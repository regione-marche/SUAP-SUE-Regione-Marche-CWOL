<?php

/**
 * Driver PDO per PostgreSQL
 *
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class PDOPostgres extends PDODriver {

    CONST ERROR_NATIVE_LOCK_ROW_TABLE = "55P03";
    CONST ERROR_NATIVE_CONSTRAINT_UNIQUE = "23505";
    CONST SERVER_VERSION_SETTING_LOCK_TIMEOUT = "9.3";

    static $dbmsBaseTypes = array(
        'bigint' => array('pdo' => PDO::PARAM_INT, 'php' => 'numeric'),
        'bit' => array('pdo' => PDO::PARAM_INT, 'php' => 'numeric'),
        'bit varying' => array('pdo' => PDO::PARAM_INT, 'php' => 'numeric'),
        'boolean' => array('pdo' => PDO::PARAM_BOOL, 'php' => 'boolean'),
        'bytea' => array('pdo' => PDO::PARAM_LOB, 'php' => 'binary'),
        'character' => array('pdo' => PDO::PARAM_STR, 'php' => 'char'),
        'character varying' => array('pdo' => PDO::PARAM_STR, 'php' => 'char'),
        'date' => array('pdo' => PDO::PARAM_STR, 'php' => 'date'),
        'double precision' => array('pdo' => PDO::PARAM_STR, 'php' => 'numeric'),
        'integer' => array('pdo' => PDO::PARAM_INT, 'php' => 'numeric'),
        'numeric' => array('pdo' => PDO::PARAM_STR, 'php' => 'numeric'),
        'real' => array('pdo' => PDO::PARAM_STR, 'php' => 'numeric'),
        'smallint' => array('pdo' => PDO::PARAM_INT, 'php' => 'numeric'),
        'text' => array('pdo' => PDO::PARAM_STR, 'php' => 'char'),
        'timestamp' => array('pdo' => PDO::PARAM_STR, 'php' => 'date')
    );
    static $castTypes = array(
        'int' => 'integer',
        'varchar' => 'character varying',
        'double' => 'double precision'
    );

    public function getDbmsBaseTypes() {
        return self::$dbmsBaseTypes;
    }

    protected function createPDOObject($dbname, $host, $port, $user, $password) {
        $portOpt = "port= " . ($port ? $port : 5432);
        if (!$portOpt) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_MISSING_PORT);
        }
        if (!$user) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_MISSING_USER);
        }
        if (!$password) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_MISSING_PASSWORD);
        }
        $hostOpt = "host=$host";
        $dbnameOpt = "dbname=$dbname";
        $userOpt = "user=$user";
        $passwordOpt = "password=$password";
        return new PDO("pgsql:$dbnameOpt;$hostOpt;$portOpt;$userOpt;$passwordOpt");
    }

    protected function initCustomDBAttributes() {
        //Timeout connessione in secondi non valido per sql Server 
        if (array_key_exists('timeout', $this->sqlparams)) {
            $this->linkid->setAttribute(PDO::ATTR_TIMEOUT, $this->sqlparams["timeout"]);
        }
        $this->linkid->exec("SET SESSION CHARACTERISTICS AS TRANSACTION ISOLATION LEVEL READ COMMITTED");

        if (array_key_exists('dateFormat', $this->sqlparams)) {
            $this->linkid->exec("SET dateStyle TO '" . $this->sqlparams['dateFormat'] . "'");
        }
        if (array_key_exists('dbRealName', $this->sqlparams)) {
            $this->linkid->exec("SET search_path TO  '" . $this->sqlparams['dbRealName'] . "'");
        }
        if (array_key_exists('fieldskeycase', $this->sqlparams) && strtoupper($this->sqlparams['fieldskeycase']) === 'UPPER') {
            $this->linkid->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER);
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
        $sql .= " LIMIT $per OFFSET $offset";
    }

    public function getTablesInfo() {
        try {
            $this->addSqlParam($sqlParams, 'SCHEMA', $this->sqldb);

            $schema = $this->sqldb;

            $sql = "SELECT
                    relname,
                    reltuples
                FROM
                    pg_class C
                LEFT JOIN
                    pg_namespace N
                ON
                    (N.oid = C.relnamespace)
                WHERE
                    nspname NOT IN ('pg_catalog', 'information_schema') AND
                    relkind='r'  AND
                    nspname = :SCHEMA
                ORDER BY
                    relname";

            $statement = $this->query($sql, $sqlParams);

            while ($row = PDO::FETCH_ASSOC($statement)) {
                $list[$row['relname']] = array(
                    'type' => '',
                    'rows' => $row['reltuples'],
                    'create' => '',
                    'update' => '',
                    'size' => ''
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
        $this->addSqlParam($sqlParams, 'SCHEMA', $this->sqldb);
        $this->addSqlParam($sqlParams, 'TABLE_NAME', strtolower($tableName));

        $sql = "SELECT
                    *
                FROM
                    information_schema.columns
                WHERE
                    table_schema =:SCHEMA
                AND
                    table_name =:TABLE_NAME 
                ORDER BY 
                    ordinal_position";

        $statement = $this->query($sql, $sqlParams);
        $result = $this->fetch($statement, false, null);

        $list = array();
        foreach ($result as $row) {
            switch ($this->sqlparams['fieldskeycase']) {
                case 'LOWER':
                    $row['COLUMN_NAME'] = strtolower($row['COLUMN_NAME']);
                    break;
                case 'UPPER':
                    $row['COLUMN_NAME'] = strtoupper($row['COLUMN_NAME']);
                    break;
            }

            $dbmsTypes = $this->getDbmsBaseTypes();
            $list[$row['COLUMN_NAME']] = array(
                'type' => $row['DATA_TYPE'],
                'phpType' => $dbmsTypes[$row['DATA_TYPE']]['php'],
                'len' => $row['CHARACTER_MAXIMUM_LENGTH'],
                'null' => $row['IS_NULLABLE'] == 'YES' ? '1' : '0',
                'key' => '',
                'default' => $row['COLUMN_DEFAULT'],
                'precision' => '',
                'scale' => ''
            );
        }

        $this->eliminaQuery($statement);

        return $list;
    }

    public function getTableInfo($tableName) {
        $sequenceName = "sq_" . strtolower($tableName);
        $this->addSqlParam($sqlParams, 'SEQUENCE_NAME', $sequenceName);

        $sql = "SELECT 
                    c.relname 
                FROM 
                    pg_class c  
                WHERE 
                    c.relkind = 'S' and c.relname = :SEQUENCE_NAME";
        $statement = $this->query($sql, $sqlParams);
        $result = $this->fetch($statement, true, null);

        $this->eliminaQuery($statement);
        if (!$result) {
            $tableInfo = array('auto' => 0, 'sequenceName' => '');
        } else {
            $tableInfo = array('auto' => 1, 'sequenceName' => $result["RELNAME"]);
        }

        return $tableInfo;
    }

    public function getPKs($tableName) {
        $this->addSqlParam($sqlParams, 'TABLENAME', $tableName);

        $sql = " SELECT 
                    a.attname AS pk 
              FROM
                    pg_attribute a
              JOIN (SELECT *, GENERATE_SUBSCRIPTS(indkey, 1) AS indkey_subscript FROM pg_index) AS i ON
                    i.indisprimary
                    AND i.indrelid = a.attrelid
                    AND a.attnum = i.indkey[i.indkey_subscript]
              WHERE  a.attrelid = :TABLENAME::regclass 
              ORDER BY i.indkey_subscript ";

        $statement = $this->query($sql, $sqlParams);
        $result = $this->fetch($statement, false, null);

        $pks = array();
        foreach ($result as $row) {
            switch ($this->sqlparams['fieldskeycase']) {
                case 'LOWER' :
                    $row['PK'] = strtolower($row['PK']);
                    break;
                case 'UPPER' :
                    $row['PK'] = strtoupper($row['PK']);
                    break;
            }
            $pks[] = $row['PK'];
        }

        $this->eliminaQuery($statement);

        return $pks;
    }

    public function listTables() {
        try {
            $this->addSqlParam($sqlParams, 'SCHEMA', $this->sqldb);
            return $this->listTableExecute($sqlParams);
        } catch (ItaException $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, $ex->getNativeErrorCode(), $ex->getNativeErroreDesc());
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    public function tableExists($tableName) {
        $this->addSqlParam($sqlParams, 'SCHEMA', $this->sqldb);
        $this->addSqlParam($sqlParams, 'TABLE_NAME', $tableName);
        return count($this->listTableExecute($sqlParams)) > 0;
    }

    private function listTableExecute($params = array()) {
        $sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = :SCHEMA";
        if (array_key_exists('TABLE_NAME', $params)) {
            $sql .= "and table_name =:TABLE_NAME";
        } else {
            $sql .= " ORDER BY table_name ";
        }
        $table_list = $this->queryMultipla($sql, true, '', '', false, $params);
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
        return "SELECT * INTO $tableTo FROM $tableFrom";
    }

    public function version() {
        try {
            $version = $this->queryRiga('SELECT version()', array());
            return $version["VERSION"];
        } catch (ItaException $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, $ex->getNativeErrorCode(), $ex->getNativeErroreDesc());
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    private function tagVersion() {
        try {
            return $this->queryRiga('SHOW server_version', array());
        } catch (ItaException $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, $ex->getNativeErrorCode(), $ex->getNativeErroreDesc());
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    public function nextval($sequence) {
        return "nextval('" . $sequence . "') AS NEXTVAL";
    }

    public function strConcat($strArray, $strCount) {
        if ($strCount != 0) {
            return implode(" || ", $strArray);
        } else {
            return "";
        }
    }

    public function strLower($value) {
        return "lower($value)";
    }

    public function strUpper($value) {
        return "upper($value)";
    }

    public function subString($value, $start, $len) {
        return "substring($value from $start for $len)";
    }

    public function strTrim($value) {
        return "trim($value)";
    }

    public function strLtrim($value) {
        return "ltrim($value)";
    }

    public function strRtrim($value) {
        return "rtrim($value)";
    }

    public function strLpad($value, $len, $padstr = ' ') {
        return "lpad($value, $len, '$padstr')";
    }

    public function strRpad($value, $len, $padstr = ' ') {
        return "rpad($value, $len, '$padstr')";
    }

    public function strCast($value, $type) {
        return "CAST($value AS " . self::$castTypes[$type] . ")";
    }

    public function dateDiff($beginDate, $endDate) {
        return "to_date($beginDate,'YYYYMMDD') - to_date($endDate,'YYYYMMDD')";
    }

    public function year($value) {
        return "to_char(" . $value . ",'YYYY' )";
    }

    public function month($value) {
        return "to_char(" . $value . ",'MM' )";
    }

    public function day($value) {
        return "to_char(" . $value . ",'DD' )";
    }

    public function module($field, $import) {
        return "mod(" . $field . ", " . $import . " )";
    }

    /**
     * @param string $value data espressa nel formato YYYYMMDD
     * @param string $format facolatativo formato di output della data 
     * @return istruzione sql da eseguire per parsare la data 
     */
    public function formatDate($value, $format = null) {
        if (!$format) {
            if (array_key_exists('dateFormat', $this->sqlparams)) {
                $format = $this->sqlparams['dateFormat'];
                // se il formato è una dta iso 
                if (strtoupper($format) == 'ISO,YMD') {
                    $format = 'YYYY-MM-DD';
                }
            } else {
                $format = 'YYYY-MM-DD';
            }
            return "to_date('$value', '$format')";
        } else {
            if (strlen($format) > 10) {
                return "to_timestamp($value, '$format')";
            } else {
                return "to_date('$value', '$format')";
            }
        }
    }

    public function dateToString($value, $format = null) {
        if (!$format) {
            $format = 'YYYY-MM-DD';
        }
        return "to_char($value, '$format')";
    }

    public function getJdbcDriverClass() {
        return 'org.postgresql.Driver';
    }

    public function getJdbcConnectionUrl() {
        return 'jdbc:postgresql://' .
                $this->sqlparams['host'] . '/' . $this->sqlparams['database'] .
                '?user=' . $this->sqlparams['user'] .
                '&password=' . $this->sqlparams['pwd'] .
                '&currentSchema=' . $this->sqlparams['realname'];
    }

    // --- SAVEPOINT ----------------------------------------
    // (Riferimento al manuale: http://php.net/manual/en/pdo.begintransaction.php)

    private function savepoint() {
        $this->execDirect("SAVEPOINT {$this->getSavepointName()}", false);
    }

    private function releaseSavepoint() {
        $this->execDirect("RELEASE SAVEPOINT {$this->getSavepointName()}", false);
    }

    private function rollbackToSavepoint() {
        $this->execDirect("ROLLBACK TO {$this->getSavepointName()}", false);
    }

    // Predisposto per poter innestare i savepoint.
    // Attualmente ne viene gestito solamente uno, seguendo la logica delle transazioni
    private function getSavepointName() {
        return 'trans_' . $this->transactionInfo['savepoints'];
    }

    protected function postBeginTransaction($isolation) {
        $this->savepoint();
    }

    protected function preCommitTransaction($skip) {
        $this->releaseSavepoint();
    }

    protected function preRollbackTransaction($skip) {
        $this->rollbackToSavepoint();
    }

    protected function initCustomTransactionInfo() {
        $this->transactionInfo['savepoints'] = 0;
    }

    protected function addCustomBeginTransaction() {
        $this->transactionInfo['savepoints'] ++;
    }

    protected function addCustomCommitTransaction() {
        $this->transactionInfo['savepoints'] --;
    }

    protected function addCustomRollbackTransaction() {
        $this->transactionInfo['savepoints'] --;
    }

    public function prepareColumnsName(&$data) {
        if (array_key_exists('fieldskeycase', $this->sqlparams) && strtoupper($this->sqlparams['fieldskeycase']) === 'UPPER') {
            $data = array_change_key_case($data, CASE_UPPER);
        } else if (array_key_exists('fieldskeycase', $this->sqlparams) && strtoupper($this->sqlparams['fieldskeycase']) === 'LOWER') {
            $data = array_change_key_case($data, CASE_LOWER);
        }
    }

    public function regExBoolean($value, $expression) {
        return "$value ~ $expression";
    }

    public function getFormatDateTime() {
        return 'YYYY/MM/DD HH24:MI:SS';
    }

    public function adapterBlob($value = null) {
        return $value;
    }

    public function lockTableCommand($tableName, $exclusive = false) {
        If ($exclusive) {
            return "LOCK TABLE $tableName IN ACCESS EXCLUSIVE MODE ";
        } else {
            return "LOCK TABLE $tableName IN SHARE MODE ";
        }
    }

    public function lockRowTableCommand($sqlLockRowTable) {
        $version = $this->tagVersion();
        if (version_compare($version["SERVER_VERSION"], self::SERVER_VERSION_SETTING_LOCK_TIMEOUT) >= 0) {
            $this->setLockTimeot();
        }
        return $sqlLockRowTable . " FOR UPDATE";
    }

    private function setLockTimeot() {
        $sec = "'" . self::DEFAULT_LOCK_TIMEOUT . "s'";
        $sqlLockTimeout = "SET LOCK_TIMEOUT = " . $sec;
        return $this->execDirect($sqlLockTimeout, false);
    }

    protected function isErrorCodelockRowTable($ex) {
        return $ex->getNativeErrorCode() == self::ERROR_NATIVE_LOCK_ROW_TABLE;
    }

    public function isErrorCodeConstraintUnique($ex) {
        return $ex->getNativeErrorCode() == self::ERROR_NATIVE_CONSTRAINT_UNIQUE;
    }

    protected function preQueryRiga(&$sql, &$params) {
        $this->preQuery($sql, $params);
    }

    protected function preQueryMultipla(&$sql, &$params) {
        $this->preQuery($sql, $params);
    }

    private function preQuery(&$sql, &$params) {
        if (is_array($params)) {
            foreach ($params as &$param) {
                if ($this->sqlparams['defaultString'] === 'blank' && ($param['type'] === PDO::PARAM_STR) && ($param['value'] === '')) {
                    $param['value'] = ' ';
                }
            }
        }
    }

}
