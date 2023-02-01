<?php

/**
 * Driver PDO per Oracle
 *
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class PDOMSSqlServer extends PDODriver {

    const ERROR_NATIVE_LOCK_ROW_TABLE = "richiesta di blocco";
    const ERROR_NATIVE_CONSTRAINT_UNIQUE = "23000";

    private $childTrans = array();
    private $cTransID;
    static $dbmsBaseTypes = array(
        'tinyint' => array('pdo' => PDO::PARAM_INT, 'php' => 'numeric'),
        'smallint' => array('pdo' => PDO::PARAM_INT, 'php' => 'numeric'),
        'int' => array('pdo' => PDO::PARAM_INT, 'php' => 'numeric'),
        'decimal' => array('pdo' => PDO::PARAM_INT, 'php' => 'numeric'),
        'numeric' => array('pdo' => PDO::PARAM_INT, 'php' => 'numeric'),
        'char' => array('pdo' => PDO::PARAM_STR, 'php' => 'char'),
        'varchar' => array('pdo' => PDO::PARAM_STR, 'php' => 'char'),
        'text' => array('pdo' => PDO::PARAM_STR, 'php' => 'char'),
        'nchar' => array('pdo' => PDO::PARAM_STR, 'php' => 'char'),
        'nvarchar' => array('pdo' => PDO::PARAM_STR, 'php' => 'char'),
        'date' => array('pdo' => PDO::PARAM_STR, 'php' => 'date'),
        'datetime' => array('pdo' => PDO::PARAM_STR, 'php' => 'date'),
        'image' => array('pdo' => PDO::PARAM_LOB, 'php' => 'binary')
    );
    static $castTypes = array(
        'int' => 'int',
        'varchar' => 'varchar',
        'double' => 'numeric'
    );

    public function getDbmsBaseTypes() {
        return self::$dbmsBaseTypes;
    }

    protected function createPDOObject($dbname, $host, $port, $user, $password) {
        $options = array();
        if ($this->isWindows()) {
            $options = array(PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_SYSTEM);
            $lib = "sqlsrv";
            $lhost = "Server";
            $ldatabase = "Database";
            $lMultipleActiveResultSets = 'MultipleActiveResultSets';
            $multipleActiveResultSetsValue = false;
        } else {
            $lib = "dblib";
            $lhost = "host";
            $ldatabase = "dbname";
        }
        if (array_key_exists('timeout', $this->sqlparams)) {
            $options[] = array(PDO::ATTR_TIMEOUT => $this->sqlparams["timeout"]);
        }
        if ($port) {
            $host = $host . ':' . $port;
        };
        return new PDO("$lib:$lhost=$host;$ldatabase=$dbname;$lMultipleActiveResultSets=$multipleActiveResultSetsValue ", $user, $password, $options);
    }

//Estratte command beginTranasction  
    protected function beginTransactionCommand() {
        if ($this->isWindows()) {
            $status = $this->linkid->beginTransaction();
        } else {
//IL DRIVER DBLIB PER LINUX PER PHP 5.3 NON SUPPORTA LE TRANSAZIONI 
            $cAlphanum = "AaBbCc0Dd1EeF2fG3gH4hI5iJ6jK7kLlM8mN9nOoPpQqRrSsTtUuVvWwXxYyZz";
            $this->cTransID = "T" . substr(str_shuffle($cAlphanum), 0, 7);

            array_unshift($this->childTrans, $this->cTransID);

            $this->execDirect("BEGIN TRAN [$this->cTransID];", false);
        }
        return $status;
    }

//Estratte command commitTranasction  
    protected function commitTransactionCommand() {
        if ($this->isWindows()) {
            $status = $this->linkid->commit();
        } else {
//IL DRIVER DBLIB PER LINUX PER PHP 5.3 NON SUPPORTA LE TRANSAZIONI 
            while (count($this->childTrans) > 0) {
                $cTmp = array_shift($this->childTrans);
                $this->execDirect("COMMIT TRAN [$cTmp]", false);
            }
        }
        return $status;
    }

//Estratte command rollbackTranasction  
    protected function rollbackTransactionCommand() {
        if ($this->isWindows()) {
            $status = $this->linkid->rollback();
        } else {
//IL DRIVER DBLIB PER LINUX PER PHP 5.3 NON SUPPORTA LE TRANSAZIONI 
            while (count($this->childTrans) > 0) {
                $cTmp = array_shift($this->childTrans);
                $this->execDirect("ROLLBACK TRAN [$cTmp]", false);
                $stmt->execute();
            }
        }
        return $status;
    }

    protected function initCustomDBAttributes() {
        if ($this->isWindows()) {
            $this->linkid->setAttribute(PDO::SQLSRV_ATTR_DIRECT_QUERY, true);
        }
        if (array_key_exists('dateFormat', $this->sqlparams)) {
            $this->linkid->exec("SET DATEFORMAT '" . $this->sqlparams['dateFormat'] . "'");
        }
    }

    public function queryCount($sql, $closeConnection = false, $params = array()) {
        try {
            $SQLParsed = $this->parseSelect($sql);
            $SQLParsed["FROM"] = $this->formatCmdNoLock($SQLParsed["FROM"]);
            $sql = 'SELECT ' . $SQLParsed['SELECT'] . ' FROM ' . $SQLParsed['FROM'] . (!empty($SQLParsed['WHERE']) ? ' WHERE ' . $SQLParsed['WHERE'] : '');

            if (isset($SQLParsed['UNION'])) {
                $sql .= isset($SQLParsed['UNION']) ? ' ' . $SQLParsed['UNION'] : '';
            }

            $result = $this->queryRiga('SELECT COUNT(*)as ROWS FROM (' . $sql . ') AS SELEZIONE ', $params, $closeConnection);
            return $result['ROWS'];
        } catch (ItaException $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, $ex->getNativeErrorCode(), $ex->getNativeErroreDesc());
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    protected function addLimitOffsetToQuery(&$sql, $da, $per) {
        $arraySQLParsed = $this->parseSelect($sql);

        $strFrom = $this->formatCmdNoLock($arraySQLParsed["FROM"]);
        $arraySQLParsed["FROM"] = $strFrom;

        if (strlen($arraySQLParsed["UNION"])) {
            $strFrom = $this->formatCmdNoLock($arraySQLParsed["UNION"]);
            $arraySQLParsed["UNION"] = $strFrom;
        }

// costruisce la stringa SQL per la paginazione 
        $sql = $this->getPageSql($arraySQLParsed, $da, $per);
    }

    private function formatCmdNoLock($sqlFrom) {
        if ((array_key_exists('isolationLevelMode', $this->sqlparams) & $this->sqlparams['isolationLevelMode'] == "OLD")) {
//  se esiste una "from" è una query annidata 
            $addStr = FALSE;
            if (strpos($sqlFrom, "FROM")) {
                if (substr($sqlFrom, 0, 1) === '(') {
                    $sqlFrom = substr($sqlFrom, 1, 99999);
                    $addStr = TRUE;
                }
                $arrayFromSQLParsed = $this->parseSelect($sqlFrom);
//$fromAnnidata = $arrayFromSQLParsed["FROM"];       
                $this->parseFrom($arrayFromSQLParsed["FROM"]);
                if ($addStr) {
                    $sqlFrom = "(";
                } else {
                    $sqlFrom = "";
                }
                $sqlFrom = $sqlFrom . "SELECT " . $arrayFromSQLParsed['SELECT'] . " FROM " . $arrayFromSQLParsed['FROM'];
                if (array_key_exists('WHERE', $arrayFromSQLParsed) && strlen($arrayFromSQLParsed['WHERE']) > 0) {
                    $sqlFrom = $sqlFrom . " WHERE " . $arrayFromSQLParsed['WHERE'];
                }
                $sqlFrom = $sqlFrom . $arrayFromSQLParsed['ORDER'];
            } else {
                $this->parseFrom($sqlFrom);
            }
        }
        return $sqlFrom;
    }

//efefttua il parse della from per aggiungere il token ' WITH(NOLOCK)'
    private function parseFrom(&$sqlFrom) {
        $strNoLock = ' WITH(NOLOCK) ';
        $positions = array();
        $needle = " ON ";
// se esiste uno spazio ed ho almeno una join 
        if (strpos($sqlFrom, ' ') !== false && strpos($sqlFrom, $needle) > 0) {
            $positions[] = strpos($sqlFrom, ' ');

            $separator = ' ';
            $token = $this->getTokenToVerify($sqlFrom, $positions[0] + 1, $separator);

            if (!$this->isSqlToken($token)) {
                $positions[0] = $positions[0] + 1 + strlen($token);
            }
// aggiungere un metodo per controllare se è un alias
// da char al prossimo spazio deve essere una parola compresa tra 'INNER , LEFT ,RIGHT ,CROSS',group, having , order ,
            $sqlFrom = substr_replace($sqlFrom, $strNoLock, $positions[0], 0);

            $lastPos = 0;
// azzero le position perche aggiungo solo quelle che hanno 'ON' nel from 
            $positions = array();
            while (($lastPos = strpos($sqlFrom, $needle, $lastPos)) !== false) {
                if (count($positions) === 0) {
                    $positions[] = $lastPos;
                } else {
                    $positions[] = $lastPos + strlen($strNoLock);
                }
                $lastPos = $lastPos + strlen($needle);
            }

// aggiungo alla stringa alle posizioni precalcolate 'position' la stringa $strNoLock
            foreach ($positions as $position) {
                $sqlFrom = substr_replace($sqlFrom, $strNoLock, $position, 0);
            }
// se c'è un alias sulla sotto query 
        } else if (strpos($sqlFrom, ')')) {
//non ci sono join ma la from è sun sottoquery già scomposta 'TABELLA') AS 'X' 
            $sqlFrom = substr_replace($sqlFrom, $strNoLock, strpos($sqlFrom, ')'), 0);
        } else {
// se non ci sono join 
            $sqlFrom = $sqlFrom . $strNoLock;
        }
    }

//calcola il token per virificare se è un Alias 
    private function getTokenToVerify($sqlFrom, $start, $separator) {
        $lengthQuery = strlen($sqlFrom);
        $token = '';
        for ($start; $start < $lengthQuery; $start++) {
            $chr = substr($sqlFrom, $start, 1);
            if ($chr === ' ') {
                break;
            } else {
                $token = $token . $chr;
            }
        }

        return $token;
    }

    protected function preQueryMultipla(&$sql, &$params) {
        $this->preQuery($sql, $params);
    }

    protected function preQueryRiga(&$sql, &$params) {
        $this->preQuery($sql, $params);
    }

    protected function preQuery(&$sql, &$params) {
        $arraySQLParsed = $this->parseSelect($sql);
        $strFrom = $this->formatCmdNoLock($arraySQLParsed["FROM"]);
// ricostruisce la stringa appena formattata con 'WITH(NOLOCK)' sulla 'FROM'

        $arraySQLParsed["FROM"] = $strFrom;
        $sql = "SELECT " . $arraySQLParsed['SELECT'] . " FROM " . $arraySQLParsed['FROM'];
        if (array_key_exists('WHERE', $arraySQLParsed) && strlen($arraySQLParsed['WHERE']) > 0) {
            $sql = $sql . " WHERE " . $arraySQLParsed['WHERE'];
        }
        if (array_key_exists('UNION', $arraySQLParsed) && strlen($arraySQLParsed['UNION']) > 0) {
            $sql = $sql . " " . $arraySQLParsed['UNION'];
        }
        if (array_key_exists('ORDER', $arraySQLParsed) && strlen($arraySQLParsed['ORDER']) > 0) {
            $sql = $sql . ' ORDER BY ' . $arraySQLParsed['ORDER'];
        }

// GESTIONE PARAMETRI MULTIPLI CON LO STESSO SEGNAPOSTO PER SQL SERVER
        if (!empty($params)) {
            $paramsCopy = $params;  //copia dei parametri (da non modificare)
            $paramNames = array();  //nome dei vari parametri (per scartare il nome nel caso sia già in uso)
            foreach ($params as $row) {
                $paramNames[] = $row['name'];
            }

            foreach ($paramsCopy as $param) { //Ciclo tutti i parametri inseriti
                if ($this->sqlparams['defaultString'] === 'blank' && ($param['type'] === PDO::PARAM_STR) && ($param['value'] === '')) {
                    $param['value'] = ' ';
                }

                $parCount = preg_match_all("/:(" . $param['name'] . ")(?:[^A-Za-z0-9_]|$)/", $sql); //Conto il numero di occorrenze
                for ($i = 1; $i < $parCount; $i++) {
                    $replace = $param['name'] . rand(0, 99999);     //aggiungo un suffisso random assicurandomi che non sia già stato usato
                    while (in_array($replace, $paramNames)) {
                        $replace = $param['name'] . rand(0, 99999);
                    }

                    $sql = preg_replace("/:(" . $param['name'] . ")([^A-Za-z0-9_]|$)/", ":" . $replace . "$2", $sql, 1);    //Sostituisco il segnaposto modificato con il suffisso

                    $paramNames[] = $replace;   //Aggiungo il nome del parametro modificato a quelli da scartare.
                    $params[] = array(
                        'name' => $replace,
                        'value' => $param['value'],
                        'type' => $param['type']
                    );     //Aggiungo la copia del parametro con il nome modificato
                }
            }
        }
//FINE GESTIONE PARAMETRI MULTIPLI PER SQL SERVER
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

            $sql = "SELECT 
                        t.NAME AS tableName,s.Name AS schemaName, p.rows AS rowCounts,min(t.create_date) AS createDate,min(t.modify_date) as modifydate,SUM(a.total_pages) * 8 AS totalSpaceKB
                    FROM 
                        sys.tables t 
                    INNER JOIN      
                        sys.indexes i ON t.OBJECT_ID = i.object_id
                    INNER JOIN 
                        sys.partitions p ON i.object_id = p.OBJECT_ID AND i.index_id = p.index_id
                    INNER JOIN 
                        sys.allocation_units a ON p.partition_id = a.container_id
                    LEFT OUTER JOIN 
                        sys.schemas s ON t.schema_id = s.schema_id
                    WHERE 
                        t.is_ms_shipped = 0 AND i.OBJECT_ID > 255 GROUP BY t.Name, s.Name, p.Rows ORDER BY  t.Name ";

            $statement = $this->query($sql);

            while ($row = PDO::FETCH_ASSOC($statement)) {
                $list[$row['tableName']] = array(
                    'type' => $row['schemaName'],
                    'rows' => $row['rowCounts'],
                    'create' => $row['createDate'],
                    'update' => $row['totalSpaceKB'],
                    'size' => $row['KB']
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

    public function getSqlCopyTable($tableFrom, $tableTo) {
        return "SELECT * INTO $tableTo FROM $tableFrom";
    }

    public function getColumnsInfo($tableName) {
        $this->addSqlParam($sqlParams, 'TABLE_NAME', $tableName);
        $sql = "SELECT 
                    c.name AS columnName,t.name AS typeName,c.is_nullable AS nullable,c.max_length as maxLength ,object_definition(c.default_object_id) AS defaultValue, c.PRECISION as precision ,c.scale  as scale
                FROM 
                    sys.columns AS c
                INNER JOIN
                    sys.types AS t ON c.user_type_id=t.user_type_id   
                WHERE 
                    Object_ID = Object_ID(:TABLE_NAME) 
                ORDER BY 
                    c.column_id ";

        $statement = $this->query($sql, $sqlParams);
        $result = $this->fetch($statement, false, null);

        $dbmsTypes = $this->getDbmsBaseTypes();
        $list = array();
        foreach ($result as $row) {
            $list[$row['columnName']] = array(
                'type' => $row['typeName'],
                'phpType' => $dbmsTypes[$row['typeName']]['php'],
                'len' => $row['maxLength'],
                'null' => $row['nullable'],
                'key' => '',
                'default' => $row['defaultValue'],
                'precision' => $row['precision'],
                'scale' => $row['scale']
            );
        }

        $this->eliminaQuery($statement);

        return $list;
    }

    public function getPKs($tableName) {
        $this->addSqlParam($sqlParams, 'TABLE_NAME', $tableName);
        $sql = "SELECT
                    COLUMN_NAME As columnName
                FROM 
                    INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE 
                   OBJECTPROPERTY(OBJECT_ID(CONSTRAINT_SCHEMA + '.' + CONSTRAINT_NAME), 'IsPrimaryKey') = 1 AND TABLE_NAME = :TABLE_NAME 
                ORDER BY 
                    ORDINAL_POSITION";

        $statement = $this->query($sql, $sqlParams);

        $result = $this->fetch($statement, false, null);

        $pks = array();
        foreach ($result as $row) {
            $pks[] = $row['columnName'];
        }

        $this->eliminaQuery($statement);

        return $pks;
    }

    public function listTables() {
        try {
            $this->addSqlParam($sqlParams, 'TABLE_CATALOG', $this->sqlparams["database"]);
            return $this->listTableExecute($sqlParams);
        } catch (ItaException $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, $ex->getNativeErrorCode(), $ex->getNativeErroreDesc());
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    public function tableExists($tableName) {
        $this->addSqlParam($sqlParams, 'TABLE_CATALOG', $this->sqlparams["database"]);
        $this->addSqlParam($sqlParams, 'TABLE_NAME', $tableName);
        return count($this->listTableExecute($sqlParams)) > 0;
    }

    private function listTableExecute($params = array()) {
        $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE LTRIM(TABLE_TYPE) = 'BASE TABLE' AND LTRIM(TABLE_CATALOG) = :TABLE_CATALOG ";
        if (array_key_exists('TABLE_NAME', $params)) {
            $sql .= " AND TABLE_NAME = :TABLE_NAME ";
        } else {
            $sql .= " ORDER BY TABLE_NAME ";
        }
        $table_list = $this->queryMultipla($sql, true, '', '', false, $params);
        array_walk($table_list, function(&$row, $key) {
            $row = $row[0];
        });
        return $table_list;
    }

    public function version() {
        try {
            $statement = $this->query("SELECT @@VERSION");
            return $statement->fetch(PDO::FETCH_COLUMN);
        } catch (ItaException $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, $ex->getNativeErrorCode(), $ex->getNativeErroreDesc());
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    public function nextval($sequence) {
        return $sequence . ".NEXTVAL FROM DUAL ";
    }

    public function strConcat($strArray, $strCount) {
        if ($strCount != 0) {
            $i = 0;
            foreach ($strArray as $value) {
                if ($i <> 0) {
                    $stringArray .= "+";
                }
                $stringArray .= " cast($value as varchar(255))";

                $i++;
            }
            return $stringArray;
        } else {
            return "";
        }
    }

// nel caso facciamo un installazione di sqlserver caseSensitive dobbiamo impostare la chiave caseSensetive nella mappa 'this->sqlparams'
// presa dal connection.ini
    public function strLower($value) {
        if (array_key_exists("caseSensitive", $this->sqlparams)) {
            return "LOWER($value)";
        }
        return $value;
    }

    public function strUpper($value) {
        if (array_key_exists("caseSensitive", $this->sqlparams)) {
            return "UPPER($value)";
        }
        return $value;
    }

    public function subString($value, $start, $len) {
        return "SUBSTRING($value,$start,$len)";
    }

    public function strTrim($value) {
        return "LTRIM(RTRIM($value))";
    }

    public function strLtrim($value) {
        return "LTRIM($value)";
    }

    public function strRtrim($value) {
        return "RTRIM($value)";
    }

    public function strLpad($value, $len, $padstr = ' ') {
        return "SUBSTRING(REPLICATE('$padstr', $len),1,$len - LEN($value)) + $value";
    }

    public function strRpad($value, $len, $padstr = ' ') {
        return "$value + SUBSTRING(REPLICATE('$padstr', $len),1,$len - LEN($value))";
    }

    public function strCast($value, $type) {
        return "CAST($value AS " . self::$castTypes[$type] . ")";
    }

    public function dateDiff($beginDate, $endDate) {
        if (is_string($beginDate) && is_string($endDate)) {
            return "DATEDIFF(day,convert(varchar," + $beginDate + " ,102),convert(varchar," + $beginDate + " ,102)" + ")";
        } else {
            return "DATEDIFF(day," + $beginDate + "," + $endDate + ")";
        }
    }

    public function year($value) {
        return "datepart(YEAR," . $value . ")";
    }

    public function month($value) {
        return "datepart(MONTH," . $value . ")";
    }

    public function day($value) {
        return "datepart(DAY," . $value . ")";
    }

    public function module($field, $import) {
        return $field . "%" . $import;
    }

    /**
     * @param string $value data espressa nel formato YYYYMMDD
     * @param string $format facolatativo formato di output della data 
     * @return istruzione sql da eseguire per parsare la data 
     */
    public function formatDate($value, $format = null) {
        if (!$format) {
            $format = 'YYYY-MM-DD'; // formato di default yyyy-mm-dd
        }
        if (strlen($format) <= 10) {
            $value = str_replace('-', '/', $value);
        }

        return "convert(datetime, '$value', " . $this->getFormatMssqlType($format) . ")";
    }

    private function getFormatMssqlType($format) {
        $type = null;
        switch ($format) {
            case "DD-MM-YYYY":
            case "DD/MM/YYYY":
                $type = 103;
                break;
            case "YYYYMMDD":
                $type = 112;
                break;
            case "YYYY.MM.DD":
                $type = 102;
                break;
            case "YYYY-MM-DD":
            case "YYYY/MM/DD":
                $type = 111;
                break;
            case "YYYY-MM-DD HH:MM:SS":
            case "YYYY-MM-DD HH:MI:SS":
            case "YYYY-MM-DD HH24:MM:SS":
            case "YYYY-MM-DD HH24:MI:SS":
                $type = 120;
                break;
        }
        return $type;
    }

    public function getJdbcDriverClass() {
        return '';
    }

    public function getJdbcConnectionUrl() {
        return '';
    }

    public function getTableInfo($tableName) {
        $this->addSqlParam($sqlParams, 'TABLE_NAME', $tableName);
        $sql = "SELECT 
                    COLUMN_NAME as columnName
                FROM 
                    INFORMATION_SCHEMA.COLUMNS
                WHERE 
                    TABLE_SCHEMA = 'dbo' and TABLE_NAME =:TABLE_NAME and COLUMNPROPERTY(object_id(TABLE_NAME), COLUMN_NAME, 'IsIdentity') = 1
                ORDER BY 
                     COLUMN_NAME";

        $statement = $this->query($sql, $sqlParams);
        $result = $this->fetch($statement, true, null);

        $this->eliminaQuery($statement);
        if (!$result) {
            $tableInfo = array('auto' => 0, 'columnName' => '');
        } else {
            $tableInfo = array('auto' => 1, 'columnName' => $result["columnName"]);
        }
        return $tableInfo;
    }

    public function prepareColumnsName(&$data) {
        
    }

    public function regExBoolean($value, $expression) {
        return "$value LIKE $expression";
    }

    
    public function dateToString($value, $format = null) {
        if (!$format) {
            $format = 'YYYY-MM-DD';
        }
        return "CONVERT(VARCHAR(20), $value, " . $this->getFormatMssqlType($format) . ")";
    }

    public function getFormatDateTime() {
        return 'YYYY-MM-DD HH:MM:SS';
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

        return $result;
    }

    protected function postQueryRigaCallback($result, $infoBinaryCallback = null) {
        return $this->postQueryCallback($result, $infoBinaryCallback);
    }

    protected function postQueryMultiplaCallback($result, $infoBinaryCallback = null) {
        return $this->postQueryCallback($result, $infoBinaryCallback);
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

    public function adapterBlob($value = null) {
        return $value;
    }

    protected function manageBinary(&$input) {
// se $input = empty imposto a null il binario 
        if (empty($input)) {
            $input = null;
            return;
        }
//        $stream = fopen('data://text/plain;base64,' . base64_encode($input), 'r');
        $stream = fopen('php://temp', 'x+');
        fwrite($stream, $input);
        rewind($stream);

        $input = $stream;
    }

    protected function adapterValueForSpecificDb($field, &$value, $typeDbSpecific, $operation) {
        if (self::$dbmsBaseTypes[$typeDbSpecific['type']]['php'] == 'date') {
            if (preg_match('/^([12][0-9]{3})-(0[0-9]|1[0-2])-([0-2][0-9]|3[01])$/', $value, $matches)) {
                $value = $matches[1] . $matches[2] . $matches[3];
            }
        }
    }

    protected function adaptResults($statement, &$result, $queryMultipla = true) {
// Se presenti dati e presente il flag "forceDatetimeToDate" adatta il resultset 
        if (!isset($this->sqlparams['forceDatetimeToDate']) || !$this->sqlparams['forceDatetimeToDate']) {
            return;
        }
        if (count($result) == 0) {
            return;
        }

// Analizza colonne Datetime
        $dtCols = array();
        $i = 0;
        $count = $queryMultipla ? count($result[0]) : count($result);
        for ($i = 0; $i < $count; $i++) {
            $columnMeta = $statement->getColumnMeta($i);
            if (strtolower($columnMeta['sqlsrv:decl_type']) === 'datetime') {
                $dtCols[] = $columnMeta['name'];
            }
        }

// Adatta resultset
        if (count($dtCols) > 0) {
            if ($queryMultipla) {
                foreach ($result as &$row) {
                    foreach ($dtCols as $dtCol) {
                        if ($row[$dtCol]) {
                            $date = new DateTime($row[$dtCol]);
                            $row[$dtCol] = $date->format('Y-m-d');
                        }
//                        $row[$dtCol] = $row[$dtCol] ? date('Y-m-d', strtotime($row[$dtCol])) : $row[$dtCol];
                    }
                }
            } else {
                foreach ($dtCols as $dtCol) {
                    if ($result[$dtCol]) {
                        $date = new DateTime($result[$dtCol]);
                        $result[$dtCol] = $date->format('Y-m-d');
                    }
//                    $result[$dtCol] = $result[$dtCol] ? date('Y-m-d', strtotime($result[$dtCol])) : $result[$dtCol];
                }
            }
        }
    }

    public function lockTableCommand($tableName, $exclusive = false) {
        If ($exclusive) {
            return "SELECT count(*) FROM $tableName WITH(TABLOCKX HOLDLOCK) WHERE 1 = 2 ";
        } else {
            return "SELECT count(*) FROM $tableName WITH(TABLOCK HOLDLOCK) WHERE 1 = 2 ";
        }
    }

    public function lockRowTableCommand($sqlLockRowTable) {
        $this->setLockTimeot();
        $sqlLockSpecific = "";
        $pos = stripos($sqlLockRowTable, 'WHERE');
        if ($pos) {
            $str1 = substr($sqlLockRowTable, 1, $pos - 1);
            $str2 = substr($sqlLockRowTable, $pos);
            $sqlLockSpecific = $str1 . " WITH(UPDLOCK) " . $str2;
        }
        return $sqlLockSpecific;
    }

    private function setLockTimeot() {
        $sec = self::DEFAULT_LOCK_TIMEOUT * 1000;
        $sqlLockTimeout = "SET LOCK_TIMEOUT " . $sec;
        return $this->execDirect($sqlLockTimeout, false);
    }

    protected function isErrorCodelockRowTable($ex) {
        return strpos($ex->getNativeErroreDesc(), self::ERROR_NATIVE_LOCK_ROW_TABLE);
    }

    public function isErrorCodeConstraintUnique($ex) {
        return $ex->getNativeErrorCode() == self::ERROR_NATIVE_CONSTRAINT_UNIQUE;
    }

    public function setLastId($tableDef, $manualKeyValues) {
        if ($tableDef->hasAutoKey()) {
            $pks = $tableDef->getPks();
            if (count($pks) > 1) {
                foreach ($manualKeyValues as $k => $v) {
                    if ($v['KEY'] == $pks[0]) {
                        unset($manualKeyValues[$k]);
                        break;
                    }
                }
                $field = array(
                    'KEY' => $pks[0],
                    'VALUE' => $this->linkid->lastInsertId()
                );
                array_unshift($manualKeyValues, $field);

                $this->lastId = $manualKeyValues;
            } else {
                $this->lastId = $this->linkid->lastInsertId();
            }
        } else {
            $this->lastId = (count($manualKeyValues) === 1 ? $manualKeyValues[0]['VALUE'] : $manualKeyValues);
        }
    }

}
