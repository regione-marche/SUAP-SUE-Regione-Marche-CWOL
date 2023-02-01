<?php

/**
 * Driver PDO per Oracle
 *
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class PDOOracle extends PDODriver {

    const PRECONDITION_MISSING_SID_SERVICE = " Parametro sid\service non valorizzato. Almeno uno dei due deve essere configurato";
    //Mapping errori specifici
    const ERROR_NATIVE_LOCK_ROW_TABLE = "ORA-30006";
    const ERROR_NATIVE_CONSTRAINT_UNIQUE = "ORA-00001";

    static $dbmsBaseTypes = array(
        'INTEGER' => array('pdo' => PDO::PARAM_INT, 'php' => 'numeric'),
        'BLOB' => array('pdo' => PDO::PARAM_LOB, 'php' => 'binary'),
        'VARCHAR2' => array('pdo' => PDO::PARAM_STR, 'php' => 'char'),
        'TEXT varying' => array('pdo' => PDO::PARAM_STR, 'php' => 'char'),
        'DATE' => array('pdo' => PDO::PARAM_STR, 'php' => 'date'),
        'NUMBER' => array('pdo' => PDO::PARAM_STR, 'php' => 'numeric'),
        'LONG RAW' => array('pdo' => PDO::PARAM_LOB, 'php' => 'char')
    );
    static $castTypes = array(
        'int' => 'INTEGER',
        'varchar' => 'VARCHAR2',
        'double' => 'NUMBER'
    );

    public function getDbmsBaseTypes() {
        return self::$dbmsBaseTypes;
    }

    protected function createPDOObject($dbname, $host, $port = 1521, $user, $password) {
        $sid = $this->sqlparams['sid'];
        $servicename = $this->sqlparams['servicename'];

        if (!$sid && !$servicename) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_MISSING_SID_SERVICE);
        }
        if (!$port) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_MISSING_PORT);
        }
        if (!$user) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_MISSING_USER);
        }
        if (!$password) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_MISSING_PASSWORD);
        }
        $hostProperties = '';
        if (is_array($host)) {
            // valido solo nel caso che si utilizzi un cluster di oracle
            $hostProperties = isset($this->sqlparams['hostProperties']) ? $this->sqlparams['hostProperties'] : null;
            if (!$hostProperties) {
                $hostProperties = "(FAILOVER=ON)";
            } else {
                //sostituisce i ":" con "=" perchè non file ini è uin carfatterre non consentito 
                //Aggiunge le parentesi 
                $hostProperties = str_replace(":", "=", $hostProperties);
                $properties = explode(' ', $hostProperties);
                $hostProperties = "";
                foreach ($properties as $prop) {
                    $hostProperties .= "(" . $prop . ")";
                }
            }
            $index = 0;
            foreach ($host as $ihost) {
                $hostStr .= "(ADDRESS=(PROTOCOL= TCP)(HOST=$ihost)(PORT=$port[$index]))";
            }
        } else {
            $hostStr = "(ADDRESS=(PROTOCOL= TCP)(HOST=$host)(PORT=$port))";
        }
        $tns = "(DESCRIPTION=(ADDRESS_LIST = $hostProperties  $hostStr )";
        if ($sid) {
            $tns .= "(CONNECT_DATA=(SID=$sid))";
        } else {
            $tns .= "(CONNECT_DATA=(SERVICE_NAME=$servicename))";
        }
        $tns .= ")";
        $connStr = "oci:dbname=" . $tns;
        $charset = null;

        /// il charset va passato dopo user charset:"uf8"        
        if (array_key_exists('charset', $this->sqlparams)) {
            $charset = "charset={$this->sqlparams['charset']}";
            $connStr .= ";$charset";
        }
        return new PDO($connStr, $user, $password);
    }

    protected function initCustomDBAttributes() {
        if (array_key_exists('timeout', $this->sqlparams)) {
            $this->linkid->setAttribute(PDO::ATTR_TIMEOUT, $this->sqlparams["timeout"]);
        }
        $this->linkid->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
        if (array_key_exists('dateFormat', $this->sqlparams)) {
            $this->linkid->exec("ALTER SESSION SET nls_date_format ='" . $this->sqlparams['dateFormat'] . "'");
        }
        if (array_key_exists('numericCharacters', $this->sqlparams)) {
            $numericCharacters = $this->sqlparams['numericCharacters'];
        } else {
            $numericCharacters = '.,';
        }
        $this->linkid->exec("ALTER SESSION SET nls_numeric_characters ='" . $numericCharacters . "'");
    }

    public function queryCount($sql, $closeConnection = false, $params = array()) {
        try {
            $result = $this->queryRiga('SELECT COUNT(*) AS "ROWS" FROM (' . $sql . ') ', $params, $closeConnection);
            return $result['ROWS'];
        } catch (ItaException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    protected function addLimitOffsetToQuery(&$sql, $da, $per) {
        $arraySQLParsed = $this->parseSelect($sql);
        // costruisce la stringa SQL per la paginazione 
        $sql = $this->getPageSql($arraySQLParsed, $da, $per);
    }

    protected function preBeginTransaction($isolation) {
        switch ($isolation) {
            case 1:
                $sql = 'SET TRANSACTION ISOLATION LEVEL READ COMMITTED';
                break;
            case 2:
                $sql = 'SET TRANSACTION ISOLATION LEVEL SERIALIZABLE';
                break;
        }
        if ($isolation > 0) {
            $this->execDirect($sql, false);
        }
    }

    public function getTablesInfo() {
        try {
            $this->addSqlParam($sqlParams, 'SCHEMA', strtoupper($this->sqlparams["database"]));
            $sql = "SELECT 
                        ALL_OBJECTS.OBJECT_NAME,ALL_OBJECTS.CREATED,ALL_OBJECTS.LAST_DDL_TIME, num_rows , round((num_rows*avg_row_len)/(1024)) KB 
                    FROM 
                        ALL_OBJECTS inner join  ALL_TABLES ON ALL_OBJECTS.OBJECT_NAME = all_tables.TABLE_NAME
                    WHERE 
                        OBJECT_TYPE = 'TABLE' AND ALL_OBJECTS.OWNER =:SCHEMA ORDER BY ALL_OBJECTS.OBJECT_NAME ";

            $statement = $this->query($sql, $sqlParams);

            while ($row = PDO::FETCH_ASSOC($statement)) {
                $list[$row['OBJECT_NAME']] = array(
                    'type' => '',
                    'rows' => $row['NUM_ROWS'],
                    'create' => $row['CREATED'],
                    'update' => $row['LAST_DDL_TIME'],
                    'size' => $row['KB']
                );
            }

            $this->eliminaQuery($statement);

            return $list;
        } catch (ItaException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    public function getColumnsInfo($tableName) {
        $this->addSqlParam($sqlParams, 'TABLE_NAME', $tableName);
        $sql = "SELECT 
                    COLUMN_NAME,DATA_TYPE, case NULLABLE WHEN 'N' THEN 0 WHEN 'Y' THEN 1 END as NULLABLE, DATA_LENGTH, DATA_PRECISION, DATA_SCALE 
                FROM 
                    USER_TAB_COLUMNS 
                WHERE 
                    TABLE_NAME=:TABLE_NAME 
                ORDER BY 
                    COLUMN_ID ";

        $statement = $this->query($sql, $sqlParams);
        $result = $this->fetch($statement, false, null);

        $dbmsTypes = $this->getDbmsBaseTypes();
        $list = array();
        foreach ($result as $row) {
            $list[$row['COLUMN_NAME']] = array(
                'type' => $row['DATA_TYPE'],
                'phpType' => $dbmsTypes[$row['DATA_TYPE']]['php'],
                'len' => $row['DATA_LENGTH'],
                'null' => $row['NULLABLE'],
                'key' => '',
                'default' => '',
                'precision' => $row['DATA_PRECISION'],
                'scale' => $row['DATA_SCALE']
            );
        }

        $this->eliminaQuery($statement);

        return $list;
    }

    public function getPKs($tableName) {
        $sqlParams = array();
        $this->addSqlParam($sqlParams, 'TABLE_NAME', $tableName);
        $this->addSqlParam($sqlParams, 'OWNER', strtoupper($this->sqlparams["database"]));
        $sql = "SELECT
                    cols.column_name 
                FROM 
                    all_constraints cons, all_cons_columns cols 
                WHERE 
                    cols.table_name = :TABLE_NAME AND cons.constraint_type = 'P'
                    AND cons.constraint_name = cols.constraint_name  
                    AND cons.owner = cols.owner 
                    AND upper(cons.owner) = :OWNER
                ORDER BY 
                    cols.position ,cols.table_name  ";

        $statement = $this->query($sql, $sqlParams);

        $result = $this->fetch($statement, false, null);

        $pks = array();
        foreach ($result as $row) {
            $pks[] = $row['COLUMN_NAME'];
        }

        $this->eliminaQuery($statement);

        return $pks;
    }

    public function version() {
        try {
            $this->addSqlParam($sqlParams, "BANNER", "Oracle%", PDO::PARAM_STR);
            $version = $this->queryRiga('SELECT * FROM v$version WHERE BANNER LIKE :BANNER ', $sqlParams);
            return $version['BANNER'];
        } catch (ItaException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    public function nextval($sequence) {
        return $sequence . ".NEXTVAL FROM DUAL NEXTVAL";
    }

    public function strConcat($strArray, $strCount) {
        if ($strCount != 0) {
            $result = "CONCAT(" . $strArray[0] . ",";
            for ($i = 1; $i < count($strArray); $i++) {
                if (count($strArray) === $i + 1) {
                    $result .= ($strArray[$i] . ")");
                } else {
                    $result .= ("CONCAT(" . $strArray[$i] . ",");
                }
            }
            for ($j = 0; $j < count($strArray) - 2; $j++) {
                $result .= ")";
            }
            return $result;
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
        if (is_string($beginDate) && is_string($endDate)) {
            return "@DATEDIFF('DD' ,(TO_DATE(" . $beginDate . " ,'" . $this->sqlparams['dateFormat'] . "')),(TO_DATE(" . $endDate . " ,'" . $this->sqlparams['dateFormat'] . "')))";
        } else {
            return "@DATEDIFF ('DD'," + $beginDate + "," + $endDate + ")";
        }
    }

    public function year($value) {
        return "to_char(" . $value . ",'YYYY')";
    }

    public function month($value) {
        return "to_char(" . $value . ",'MM')";
    }

    public function day($value) {
        return "to_char(" . $value . ",'DD')";
    }

    public function module($field, $import) {
        return "mod(" . $field . ", " . $import . " )";
    }

    public function formatDate($value, $format = null) {
        if (!$format) {
            if (array_key_exists('dateFormat', $this->sqlparams)) {
                $format = $this->sqlparams['dateFormat'];
            } else {
                $format = 'YYYY-MM-DDDD';
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

    public function getJdbcDriverClass() {
        return '';
    }

    public function getJdbcConnectionUrl() {
        return '';
    }

    public function getSqlCopyTable($tableFrom, $tableTo) {
        return "CREATE TABLE $tableTo AS SELECT * FROM $tableFrom";
    }

    public function tableExists($tableName) {
        $this->addSqlParam($sqlParams, 'SCHEMA', strtoupper($this->sqlparams["database"]));
        $this->addSqlParam($sqlParams, 'TABLE_NAME', $tableName);
        return count($this->listTableExecute($sqlParams)) > 0;
    }

    public function listTables() {
        try {
            $this->addSqlParam($sqlParams, 'SCHEMA', strtoupper($this->sqlparams["database"]));
            return $this->listTableExecute($sqlParams);
        } catch (ItaException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    private function listTableExecute($params = array()) {
        $sql = "SELECT DISTINCT OBJECT_NAME FROM ALL_OBJECTS WHERE OBJECT_TYPE = 'TABLE' AND OWNER =:SCHEMA";
        if (array_key_exists('SCHEMA', $params)) {
            $sql .= " AND OBJECT_NAME = :TABLE_NAME ";
        } else {
            $sql .= " ORDER BY OBJECT_NAME ";
        }
        $table_list = $this->queryMultipla($sql, true, '', '', false, $params);
        array_walk($table_list, function(&$row, $key) {
            $row = $row[0];
        });
        return $table_list;
    }

    public function getTableInfo($tableName) {
        $this->addSqlParam($sqlParams, 'SEQUENCE_NAME', "SQ_" . $tableName);
        $sql = "SELECT SEQUENCE_NAME FROM user_sequences WHERE SEQUENCE_NAME =:SEQUENCE_NAME";

        $statement = $this->query($sql, $sqlParams);
        $result = $this->fetch($statement, true, null);

        $this->eliminaQuery($statement);
        if (!$result) {
            $tableInfo = array('auto' => 0, 'sequenceName' => '');
        } else {
            $tableInfo = array('auto' => 1, 'sequenceName' => $result["SEQUENCE_NAME"]);
        }
        return $tableInfo;
    }

    public function prepareColumnsName(&$data) {
        
    }

    public function regExBoolean($value, $expression) {

        return "$value REGEXP_LIKE $expression";
       
    }
    
    public function adapterBlob($value = null) {
        if (strpos($value, '.') !== false) {
            return "COALESCE($value, EMPTY_BLOB()) AS " . substr($value, strpos($value, '.') + 1);
        } else {
            return "COALESCE($value, EMPTY_BLOB()) AS " . $value;
        }
    }

    public function dateToString($value, $format = null) {
        if (!$format) {
            $format = 'YYYY-MM-DD';
        }
        return "to_char($value, '$format')";
    }

    public function getFormatDateTime() {
        return 'YYYY/MM/DD HH24:MI:SS';
    }

    protected function adapterValueForSpecificDb($field, &$value, $typeDbSpecific, $operation) {
        $blobInfo = "";
        if (($typeDbSpecific['type'] == 'VARCHAR2' || $typeDbSpecific['type'] == 'TEXT varying' ) && !is_string($value)) {
            $value = (string) $value;
        } elseif ($typeDbSpecific["type"] == "LONG RAW") {
            $value = $this->strToHex($value);
        } elseif ($typeDbSpecific["type"] == "BLOB") {
            if (empty($value)) {
                $value = null;
            } elseif (!is_resource($value)) {
                $res = fopen('php://memory', 'r+');
                fwrite($res, $value);
                rewind($res);

                $value = $res;
            }

            $blobInfo = " RETURNING $field INTO :$field ";
        }
        return $blobInfo;
    }

    function strToHex($string) {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0' . $hexCode, -2);
        }
        return strToUpper($hex);
    }

    private function postQueryCallback($result, $infoBinaryCallback) {
        if ($infoBinaryCallback != null) {
            $classCallbackLoadBinary = new $infoBinaryCallback['class']();
            if (isset($infoBinaryCallback['additionalInfo']) == null) {
                $result = $classCallbackLoadBinary->$infoBinaryCallback['method']($result);
            } else {
                $result = $classCallbackLoadBinary->$infoBinaryCallback['method']($result, $infoBinaryCallback['additionalInfo']);
            }
        }
        //if (!$this->isWindows()) {
        $result = $this->verifySizeResources($result);
        //}

        return $result;
    }

    protected function queryBinary(&$statement, &$infoBinaryFields) {
        $infoBinaryFields['results'] = array();
        foreach ($infoBinaryFields['fields'] as $field) {
            //aggiunge su result il nome della colonna 
            $nameCols = $field['name'];
            $infoBinaryFields['results'][$nameCols] = null;
            $statement->bindColumn($field['position'], $infoBinaryFields['results'][$nameCols], PDO::PARAM_LOB, $field["maxLenght"], $field["driverData"]);
        }
    }

    protected function postQueryRigaCallback($result, $infoBinaryCallback) {
        return $this->postQueryCallback($result, $infoBinaryCallback);
    }

    protected function postQueryMultiplaCallback($result, $infoBinaryCallback) {
        return $this->postQueryCallback($result, $infoBinaryCallback);
    }

    private function verifySizeResources($result) {
        foreach ($result as $key => $value) {
            if (is_resource($value) && get_resource_type($value) == "stream") {
                $buf = fread($value, 1);
                if (strlen($buf) == 0) {
                    $result[$key] = null;
                } else {
                    $result[$key] = fopen('php://temp', 'x+');
                    fwrite($result[$key], $buf);
                    while (fwrite($result[$key], fread($value, 1024)));
                    fclose($value);
                    rewind($result[$key]);
                }
            }
        }

        return $result;
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

    public function lockTableCommand($tableName, $exclusive = false) {
        If ($exclusive) {
            return "LOCK TABLE $tableName IN ACCESS EXCLUSIVE MODE ";
        } else {
            return "LOCK TABLE $tableName IN SHARE MODE ";
        }
    }

    public function lockRowTableCommand($sqlLockRowTable) {
        return $sqlLockRowTable . " FOR UPDATE WAIT " . self::DEFAULT_LOCK_TIMEOUT;
    }

    protected function isErrorCodelockRowTable($ex) {
        return strpos($ex->getNativeErroreDesc(), self::ERROR_NATIVE_LOCK_ROW_TABLE) > 0;
    }

    public function isErrorCodeConstraintUnique($ex) {
        return strpos($ex->getNativeErroreDesc(), self::ERROR_NATIVE_CONSTRAINT_UNIQUE) > 0;
    }

}
