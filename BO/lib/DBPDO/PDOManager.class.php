<?php

/**
 *
 * Classe gestione database
 *
 * @author Massimo Biagioli - Lorenzo Pergolini
 *
 */
class PDOManager {

    /**
     * Contiene il percorso completo del driver necessario per gestire il tipo di DBMS utilizzato
     * @var string
     */
    private $dbms;

    /**
     * Istanza della classe da chiamare per accedere ai metodi del driver
     * @var string
     */
    private $driver;

    /**
     * Nome della connessione
     * @var string 
     */
    private $connectionName;

    /**
     * nome del database
     * @var string
     */
    private $dbName;

    /**
     * Costruttore della classe
     */
    function __construct($p = array(), $sqlparams = array()) {

        //precondizioni
        if (!(isset($p['dbms']))) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Parametro dbms non impostato");
        }
        if (!(isset($p['db']))) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Parametro db non impostato");
        }
        if (!(isset($p['host']))) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Parametro host non impostato");
        }
        if (!(isset($p['user']))) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Parametro user non impostato");
        }
        if (!(isset($p['pwd']))) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Parametro pwd non impostato");
        }
        if (!(isset($p['connectionName']))) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Parametro connectionName non impostato");
        }

        //inizializzazione
        $this->dbms = $p['dbms'];
        $this->dbName = $p['db'];
        $this->connectionName = $p['connectionName'];
        $host = $p['host'];
        $user = $p['user'];
        $pwd = $p['pwd'];
        $newlink = (isset($p['newlink'])) ? $p['newlink'] : false;

//        switch ($sqlparams['fieldskeycase']) {
//            case 'CASE_LOWER':
//                $sqlparams['fieldskeycase'] = CASE_LOWER;
//                break;
//            case 'CASE_UPPER':
//                $sqlparams['fieldskeycase'] = CASE_UPPER;
//                break;
//        }

        switch ($this->dbms) {
            case 'pgsql':
                $classe = 'PDOPostgres';
                $fileName = dirname(__FILE__) . "/PDOPostgres.class.php";
                break;
            case 'oracle':
                $classe = 'PDOOracle';
                $fileName = dirname(__FILE__) . "/PDOOracle.class.php";
                break;
            case 'mssqlserver':
                $classe = 'PDOMSSqlServer';
                $fileName = dirname(__FILE__) . "/PDOMSSqlServer.class.php";
                break;
            case 'mysql':
                $classe = 'PDOMySql';
                $fileName = dirname(__FILE__) . "/PDOMySql.class.php";
                break;
        }

        //caricamento driver DBMS
        if (!file_exists($fileName)) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Driver PDO del DBMS $classe non trovato");
        }

        include_once($fileName);
        $this->driver = new $classe($host, $this->dbName, $user, $pwd, $newlink, $sqlparams, $this->connectionName);
    }

    /**
     * La funzione restituisce una stringa con il carattere di backslash '\'
     * anteposto ai caratteri che richiedono il quoting nelle query dei database.
     * Questi caratteri sono: apici singoli ('), doppi apici ("), backslash (\)
     * e NULL (il byte NULL) 
     *
     * @param type $val
     * @return type
     */
    public function quote($val) {
        return $this->driver->quote($val);
    }

    public function setCustomAttribute($attr_key, $attr_value) {
        $this->driver->setCustomAttribute($attr_key, $attr_value);
    }

    public function setUseBufferedQuery($use) {
        $this->driver->setUseBufferedQuery($use);
    }

    /**
     * Creazione di una resource statement PDO
     * 
     * @param type $sql
     * @param type $params
     * @param type $infoBinaryFields
     * @param type $multipla
     * @param type $da
     * @param type $per
     * @return type
     * @throws ItaException
     * @throws type
     */
    public function queryPrepare($sql, $params = array(), &$infoBinaryFields = array(), $multipla = false, $da = '', $per = '') {
        return $this->driver->queryPrepare($sql, $params, $infoBinaryFields, $multipla, $da, $per);
    }

    /**
     * Fetch di un PDO Result da PDO Statement preparato precedentemente
     * 
     * @param type $statement
     * @param type $infoBinaryCallback
     * @param type $infoBinaryFields
     * @param type $indiciNumerici
     * @param type $multipla
     * @return type
     * @throws ItaException
     * @throws type
     */
    public function queryFetch($statement, $infoBinaryCallback = array(), $infoBinaryFields = array(), $indiciNumerici = false, $multipla = false) {
        return $this->driver->queryFetch($statement, $infoBinaryCallback, $infoBinaryFields, $indiciNumerici, $multipla);
    }

    /**
     * Elimina statement PDO
     * 
     * @param type $statement
     * @param type $closeConnection
     */
    public function queryRemove($statement, $closeConnection = false) {
        $this->driver->queryRemove($statement, $closeConnection);
    }

    /**
     * Esegue una query sul DB
     * @param string $sql Testo della query da eeguire
     * @param boolean $closeConnection Indica se si vuole chiudere o meno la connessione al db dopo l'esecuzione della query
     * @return array Un array associativo o non associativo o un singolo valore contenente il risultato della query
     */
    public function query($sql, $closeConnection = false, $params = array()) {
        return $this->driver->query($sql, $params, $closeConnection);
    }

    /**
     * Per ottenere uno o più campi di una o più righe
     * @param string $sql Comando sql
     * @param boolean $closeConnection Indica se si vuole chiudere o meno la connessione al db dopo l'esecuzione della query
     * @param array $params parametri da passare alla where  
     * @return int Numero elementi
     */
    public function queryCount($sql, $closeConnection = false, $params = array()) {
        return $this->driver->queryCount($sql, $closeConnection, $params);
    }

    /**
     * @param string $sql Comando sql
     * @param int $da OFFSET
     * @param int $per LIMIT
     * @param boolean $closeConnection Indica se si vuole chiudere o meno la connessione al db dopo l'esecuzione della query
     * @param array $params parametri da passare alla where  
     * @param array $infoBinaryCallback oggetto per il caricamento dei binary
     * @param array $infoFields descrizione dei campi binary
     * @return array Lista elementi
     */
    public function queryMultipla($sql, $da = '', $per = '', $closeConnection = false, $params = array(), $infoBinaryCallback = array(), $infoFields = array()) {
        return $this->driver->queryMultipla($sql, FALSE, $da, $per, $closeConnection, $params, $infoBinaryCallback, $infoFields, $closeConnection);
    }

    /**
     * Per ottenere uno o più campi di una specifica riga
     * @param string $sql Comando sql
     * @param array $params parametri da passare alla where  
     * @param array $infoBinaryCallback oggetto per il caricamento dei binary
     * @param array $infoFields descrizione dei campi binary
     * @param boolean $closeConnection Indica se si vuole chiudere o meno la connessione al db dopo l'esecuzione della query
     * @return array
     */
    public function queryRiga($sql, $params, $infoBinaryCallback = array(), $infoFields = array(), $closeConnection = false) {
        return $this->driver->queryRiga($sql, $params, $infoBinaryCallback, $infoFields, $closeConnection);
    }

    /**
     * Ritorna alias di preso da una stringa Sql 
     * @param string $sql Comando sql
     * @param string $tableName nome tabella
     * @return string
     */
    public function getSqlAlias($sql, $tableName) {
        return $this->driver->getSqlAlias($sql, $tableName);
    }

    /**
     * Permette di chiudere manualmente la connessione al DB
     * @return boolean TRUE se la connessione è stata chiusa correttamente, FALSE altrimenti
     */
    public function chiudiDB() {
        return $this->driver->chiudiDB();
    }

    /**
     * Ritorna l'SQL per una stringa vuota
     * @return type
     */
    public function blank() {
        return $this->driver->blank();
    }

    /**
     * Ritorna l'SQL per la comparazione con una stringa vuota
     * @return type
     */
    public function isBlank() {
        return $this->driver->isBlank();
    }

    /**
     * Ritorna l'SQL per la comparazione negata con una stringa vuota
     * @return type
     */
    public function isNotBlank() {
        return $this->driver->isNotBlank();
    }

    /**
     * Restituisce classe driver JDBC
     * @return string JDBC Class
     */
    public function getJdbcDriverClass() {
        return $this->driver->getJdbcDriverClass();
    }

    /**
     * Restituisce stringa di connessione JDBC
     * @return string Stringa di connessione JDBC
     */
    public function getJdbcConnectionUrl() {
        return $this->driver->getJdbcConnectionUrl();
    }

    /**
     * Restituisce utente connessione DB (Per JasperReports)
     * @return string Utente connessione DB
     */
    public function getUser() {
        return $this->driver->getUser();
    }

    /**
     * Restituisce password connessione DB (Per JasperReports)
     * @return string Password connessione DB
     */
    public function getPassword() {
        return $this->driver->getPassword();
    }

    /**
     * Ritorna l'SQL per il substring di un campo
     * @param type $value
     * @param type $start
     * @param type $len
     * @return type
     */
    public function subString($value, $start, $len) {
        return $this->driver->subString($value, $start, $len);
    }

    /**
     * Crea la string SQL per la concatenazione di elementi CHAR
     * @return string
     */
    public function strConcat() {
        $strArg = func_get_args();
        $strCount = func_num_args();
        return $this->driver->strConcat($strArg, $strCount);
    }

    /**
     * Ritorna l'SQL per il coalesce, che restituisce il primo argumento
     * non NULL della query
     * @return type
     */
    public function coalesce() {
        $coalesceArg = func_get_args();
        $coalesceCount = func_num_args();
        return $this->driver->coalesce($coalesceArg, $coalesceCount);
    }

    /**
     * Ritorna l'SQL per il NULLIF, che restituisce NULL se i valori sono uguali,
     * altrimenti ritorna il primo valore
     * @param type $var1
     * @param type $var2
     * @return type
     */
    public function nullIf($var1, $var2) {
        return $this->driver->nullIf($var1, $var2);
    }

    /**
     * Ritorna l'SQL per il DATEDIFF, che restituisce la differenza in giorni
     * tra due date nel formato 'Ymd'
     * @param type $beginDate
     * @param type $endDate
     * @return type
     */
    public function dateDiff($beginDate, $endDate) {
        return $this->driver->dateDiff($beginDate, $endDate);
    }

    /**
     * Effettua una conversione nel formato data del campo specificato
     * @param type $value Campo
     * @param type $format Formato
     * @return string sql
     */
    public function formatDate($value, $format = null) {
        //return $this->driver->dateDiff($value, $format);
        return $this->driver->formatDate($value, $format);
    }

    /**
     * Effettua una conversione nel formato stringa di una data 
     * @param type $value Campo
     * @param type $format Formato
     * @return string stringa formattata
     */
    public function dateToString($value, $format) {
        return $this->driver->dateToString($value, $format);
    }

    /**
     * Effettua un adattamento per la lettura dei campi binari per il database oracle su piattaforma linux
     * @param type $value
     * @return string stringa formattata
     */
    public function adapterBlob($value) {
        return $this->driver->adapterBlob($value);
    }

    /**
     * Ritorna il formato predefinito data e ora estesa
     */
    public function getFormatDateTime() {
        return $this->driver->getFormatDateTime();
    }

    /**
     * Ritorna l'SQL per il lowercase di una stringa
     * @param type $value
     * @return type
     */
    public function strLower($value) {
        return $this->driver->strLower($value);
    }

    /**
     * Ritorna l'SQL per l'uppercase di una stringa
     * @param type $value
     * @return type
     */
    public function strUpper($value) {
        return $this->driver->strUpper($value);
    }

    /**
     * Toglie spazi in testa ed in coda alla stringa
     * @param string $value 
     * @return type
     */
    public function strTrim($value) {
        return $this->driver->strTrim($value);
    }

    /**
     * Toglie spazi in testa alla stringa
     * @param string $value 
     * @return type
     */
    public function strLtrim($value) {
        return $this->driver->strLTrim($value);
    }

    /**
     * Toglie spazi in coda alla stringa
     * @param string $value 
     * @return type
     */
    public function strRtrim($value) {
        return $this->driver->strRTrim($value);
    }

    /**
     * Aggiunge in testa il carattere passato fino al raggiungimento della lunghezza specificata
     * @param string $value 
     * @param int $len 
     * @param string $padstr 
     * @return type
     */
    public function strLpad($value, $len, $padstr = ' ') {
        return $this->driver->strLPad($value, $len, $padstr);
    }

    /**
     * Aggiunge in coda il carattere passato fino al raggiungimento della lunghezza specificata
     * @param string $value 
     * @param int $len 
     * @param string $padstr 
     * @return type
     */
    public function strRpad($value, $len, $padstr = ' ') {
        return $this->driver->strRPad($value, $len, $padstr);
    }

    /**
     * Effettua il cast ad un determinato tipo (Es da stringa a intero)
     * @param string $value 
     * @param string $type 
     * @return type
     */
    public function strCast($value, $type) {
        return $this->driver->strCast($value, $type);
    }

    /**
     * Ritorna l'anno in formato "YYYY"  di una data 
     * @param string colonnaData 
     * @return Anno 
     */
    public function year($value) {
        return $this->driver->year($value);
    }

    /**
     * Ritorna  il mese in formato "MM"  di una data 
     * @param string colonnaData 
     * @return mese 
     */
    public function month($value) {
        return $this->driver->month($value);
    }

    /**
     * Ritorna il giorno in formato "DD"  di una data 
     * @param string colonnaData 
     * @return giorni 
     */
    public function day($value) {
        return $this->driver->day($value);
    }

    public function module($field, $import) {
        return $this->driver->module($field, $import);
    }

    /**
     * Ritorna l'SQL per il round di un numero
     * @param type $value
     * @param type $decimal
     * @return type
     */
    public function round($value, $decimal = 0) {
        return $this->driver->round($value, $decimal);
    }

    public function regExBoolean($value, $expression) {
        return $this->driver->regExBoolean($value, $expression);
    }

    /**
     * Restituisce il prossimo valore della sequenza indicata
     * dal precedente query INSERT
     * @return type
     */
    public function nextval($sequence) {
        return $this->driver->nextval($sequence);
    }

    /**
     * Ritorna l'SQL per ottenere il timestamp ottenuto dall'unione di una data e un'ora
     * @param string $dateField
     * @param string $timeField
     * @return string
     */
    public function unifyDateTime($dateField, $timeField) {
        return $this->driver->unifyDateTime($dateField, $timeField);
    }

    /**
     * Restituisce l'identificativo generato per il record appena inserito
     * @return type
     */
    public function getLastId() {
        return $this->driver->getLastId();
    }

    /**
     * Permette di controllare se la connessione al DB è disponibile
     * @return boolean TRUE se la connessione è disponibile, FALSE altrimenti
     */
    public function testConn() {
        return $this->driver->testConn();
    }

    /**
     * Controlla se il DB è presente o meno
     * @return boolean
     */
    public function exists() {
        return $this->driver->exists();
    }

    /**
     * 
     * @return type
     */
    public function version() {
        return $this->driver->version();
    }

    /**
     * Ritorna una lista delle tabelle nel DB
     * @return type
     */
    public function listTables() {
        return $this->driver->listTables();
    }

    /**
     * Ritorna le informazioni aggiuntive riguardanti le tabelle nel DB
     * @return type
     */
    public function getTablesInfo() {
        return $this->driver->getTablesInfo();
    }

    /**
     * Ritorna il numero di tabelle presenti all'interno del DB
     * @return type
     */
    public function countTables() {
        return $this->driver->countTables();
    }

    public function dbInsert($table, $data) {
        return $this->driver->dbInsert($table, $data);
    }

    public function dbUpdate($table, $data, $oldData) {
        return $this->driver->dbUpdate($table, $data, $oldData);
    }

    public function dbDelete($table, $pkValues) {
        return $this->driver->dbDelete($table, $pkValues);
    }

    public function getColumnsInfo($table) {
        return $this->driver->getColumnsInfo($table);
    }

    public function getPKs($table) {
        return $this->driver->getPKs($table);
    }

    public function getTableInfo($table) {
        return $this->driver->getTableInfo($table);
    }

    //1,2 Livello di isolamento READ COMMI, SERIALIZABLE' 
    public function beginTransaction($isolation = 1, $manual, $timeout = null) {
        return $this->driver->beginTransaction($isolation, $manual, $timeout);
    }

    public function commitTransaction($skip = false, $manual) {
        return $this->driver->commitTransaction($skip, $manual);
    }

    public function rollbackTransaction($skip = false, $manual) {
        return $this->driver->rollbackTransaction($skip, $manual);
    }

    public function countOpenTransaction() {
        return $this->driver->countOpenTransaction();
    }

    public function getDbmsBaseTypes() {
        return $this->driver->getDbmsBaseTypes();
    }

    public function getDBMS() {
        return $this->dbms;
    }

    public function getConnectionName() {
        return $this->connectionName;
    }

    public function getDB() {
        //return strtoupper($this->dbName);
        return $this->dbName;
    }

    public function getSqlparams() {
        return $this->driver->getSqlparams();
    }

    public function lockTable($tableName, $exclusive = false) {
        return $this->driver->lockTable($tableName, $exclusive);
    }

    public function lockRowTable($tableName, $params = array()) {
        return $this->driver->lockRowTable($tableName, $params);
    }

}

?>