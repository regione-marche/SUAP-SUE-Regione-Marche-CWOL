<?php

/**
 * Driver PDO per gestione database Access 
 *
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class PDOAccess extends PDODriver {

    static $dbmsBaseTypes = array(
        'Testo' => array('pdo' => PDO::PARAM_STR, 'php' => 'char'),
        'Numerico' => array('pdo' => PDO::PARAM_STR, 'php' => 'char')
    );

    public function getDbmsBaseTypes() {
        return self::$dbmsBaseTypes;
    }

    protected function createPDOObject($dbname, $host, $port = 1521, $user, $password) {
        $fileAccess = $this->sqlparams['locationFile'];
        if (!$fileAccess) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Parametro file non valorizzato");
        }
        return new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=" . $fileAccess . ";Uid=$user; Pwd=$password;");
    }

    public function queryCount($sql, $closeConnection = false, $params = array()) {
        try {
            $result = $this->queryRiga('SELECT COUNT(*)as ROWS FROM (' . $sql . ') AS SELEZIONE ', $params, $closeConnection);
            return $result['ROWS'];
        } catch (ItaException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    protected function addLimitOffsetToQuery(&$sql, $da, $per) {
        return $sql;
    }

    protected function preBeginTransaction($isolation) {
        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo beginTranasction non supportato per il database Access');
    }

    public function getTablesInfo() {
        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo "getTablesInfo" non supportato per il database Access');
    }

    public function getColumnsInfo($tableName) {
        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo "getColumnsInfo" non supportato per il database Access');
    }

    public function getPKs($tableName) {
        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo "getPKs" non supportato per il database Access');
    }

    public function getSqlCopyTable($tableFrom, $tableTo) {
        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo getSqlCopyTable non supportato per il database Access');
    }

    public function version() {
        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo "version" non supportato per il database Access');
    }

    public function nextval($sequence) {
        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo "nextval" non supportato per il database Access');
    }

    public function strConcat($strArray, $strCount) {
        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo "strConcat" non supportato per il database Access');
    }

    public function strLower($value) {
        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo "strLower" non supportato per il database Access');
    }

    public function strUpper($value) {
        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo "strUpper" non supportato per il database Access');
    }

    public function subString($value, $start, $len) {
        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo "subString" non supportato per il database Access');
    }

    public function dateDiff($beginDate, $endDate) {
        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo "dateDiff" non supportato per il database Access');
    }

    public function formatDate($value, $format = null) {
        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo "formatDate" non supportato per il database Access');
    }

    public function getJdbcDriverClass() {
        return '';
    }

    public function getJdbcConnectionUrl() {
        return '';
    }

    public function getTableInfo($tableName) {
        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo "getTableInfo" non supportato per il database Access');
    }

    public function tableExists($tableName) {
        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo "tableExists" non supportato per il database Access');
    }

    public function listTables() {
        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo "listTables" non supportato per il database Access');
    }

    protected function initCustomDBAttributes() {
        
    }

    protected function lockTableCommand($tableName, $exclusive = false) {
        return "";
    }
    
    

    protected function isErrorCodelockRowTable($ex) {
        
    }

    public function adapterBlob($value = null) {
        
    }

    public function dateToString($value, $format = null) {
        
    }

    public function day($value) {
        
    }

    public function getFormatDateTime() {
        
    }

    public function isErrorCodeConstraintUnique($ex) {
        
    }

    public function lockRowTableCommand($sqlLockRowTable) {
        
    }

    public function module($field, $import) {
        
    }

    public function month($value) {
        
    }

    public function prepareColumnsName(&$data) {
        
    }

    public function strCast($value, $type) {
        
    }

    public function strLpad($value, $len, $padstr = ' ') {
        
    }

    public function strLtrim($value) {
        
    }

    public function strRpad($value, $len, $padstr = ' ') {
        
    }

    public function strRtrim($value) {
        
    }

    public function strTrim($value) {
        
    }

    public function year($value) {
        
    }

}
