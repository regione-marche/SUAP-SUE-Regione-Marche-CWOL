<?php

/**
 * AV_DriverDB
 * Interfaccia che ogni driver per DB deve implementare
 *
 * @author Andrea Vallorani <andrea.vallorani@email.it>
 * @author Carlo Iesari <carlo@iesari.me>
 * @version 30.10.15
 */
abstract class ITA_DriverDB {

    /**
     * Lista generale dei parametri per la connessione
     * @var array
     */
    protected $sqlparams;

    /**
     * Indirizzo del server sql
     * @var string
     */
    protected $sqlhost;

    /**
     * Nome utente per accesso al server
     * @var string
     */
    protected $sqluser;

    /**
     * Password utente per accesso al server
     * @var string
     */
    protected $sqlpass;

    /**
     * Database/Schema da selezionare
     * @var string
     */
    protected $sqldb;

    /**
     * Nuova connessione sempre 
     * @var integer
     */
    protected $newlink;

    /**
     * Mappa la relativa classe jdbc per la connessione
     * @var string
     */
    public $jdbcDriverClass;

    /**
     * Mappa la relativa connection url jdbc
     * @var string
     */
    public $jdbcConnectionUrl = "";

    /**
     * Identifica la connessione al server attiva
     * @var resource
     */
    public $linkid = FALSE;
    static public $utf8 = FALSE;

    function __construct($host, $db, $user, $pwd, $newlink, $sqlparams = array()) {
        $this->sqlhost = $host;
        $this->sqluser = $user;
        $this->sqlpass = $pwd;
        $this->sqldb = $db;
        $this->newlink = $newlink;
        $this->sqlparams = $sqlparams;
    }

    public function getSqlparams() {
        return $this->sqlparams;
    }

    public function setSqlparams($sqlparams) {
        $this->sqlparams = $sqlparams;
    }

    public function setDB($nome) {
        $this->sqldb = $nome;
    }

    public function setServer($nome) {
        $this->sqlhost = $nome;
    }

    public function setUser($nome) {
        $this->sqluser = $nome;
    }

    public function setPassword($nome) {
        $this->sqlpass = $nome;
    }

    public function getDB() {
        return $this->sqldb;
    }

    public function getServer() {
        return $this->sqlhost;
    }

    public function getUser() {
        return $this->sqluser;
    }

    public function getPassword() {
        return $this->sqlpass;
    }

    /**
     * Apre Connessione 
     * @return boolean
     */
    abstract protected function apriConn();

    /**
     * @param type $forzaApertura si connette all'apertura se la connessione non è ancora stabilita
     * @return boolean
     */
    abstract protected function apriDB();

    abstract protected function avvenutoErrore($cod);

    abstract protected function quote($val);

    abstract protected function query($txt);

    abstract protected function eliminaQuery($txt);

    abstract protected function queryCount($txt);

    abstract public function queryMultipla($txt);

    abstract public function queryRiga($txt);

    abstract public function queryValore($txt);

    abstract public function queryValori($txt);

    abstract public function update($txt);

    abstract public function chiudiConn();

    abstract public function blank();

    abstract public function isBlank();

    abstract public function isNotBlank();

    abstract public function getJdbcDriverClass();

    abstract public function getJdbcConnectionUrl();

    abstract public function subString($value, $start, $len);

    abstract public function strConcat($strArray, $strCount);

    abstract public function coalesce($coalesceArray, $coalesceCount);

    abstract public function nullIf($var1, $var2);

    abstract public function dateDiff($beginDate, $endDate);

    abstract public function strLower($value);

    abstract public function strUpper($value);
    
    abstract public function regExBoolean($value, $expression);

    abstract public function round($value, $decimal = 0);

    abstract public function nextval($sequence);

    abstract public function getLastId();

    abstract public function testConn();

    abstract public function exists();

    abstract public function create();

    abstract public function version();

    abstract public function listTables();

    abstract public function getTablesInfo();

    abstract public function getPrimaryKey($table);

    abstract public function getTableObject($table);

    abstract public function getColumnsInfo($table);

    abstract public function getIndexesInfo($table);

    abstract public function getNormalizedValue($fieldInfo, $value);

    abstract public function countTables();
}

/**
 * AV_DB
 *
 * Classe standard per la connessione ad un qualsiasi DBMS
 *
 * @author Andrea Vallorani <andrea.vallorani@email.it>
 *
 */
class ITA_DB {

    /**
     * Contiene il percorso completo del driver necessario per gestire il tipo di DBMS utilizzato
     * @var string
     */
    private $dbms;

    /**
     * Istanza della classe da chiamare per accedere ai metodi del driver
     * @var string
     */
    public $lettore;

    //parametri di configurazione
    const DEFAULT_HOST = 'localhost';
    const DEFAULT_USER = 'root';
    const DEFAULT_PASSWORD = 'password';
    const DEFAULT_DB = 'db';
    const DEFAULT_DBMS = 'mysql';
    const DEFAULT_NEWLINK = false;

    /**
     * Costruttore della classe
     */
    function __construct($p = array(), $sqlparams = array()) {
        //inizializzazione
        $this->dbms = (isset($p['dbms'])) ? $p['dbms'] : self::DEFAULT_DBMS;
        $db = (isset($p['db'])) ? $p['db'] : self::DEFAULT_DB;
        $host = (isset($p['host'])) ? $p['host'] : self::DEFAULT_HOST;
        $user = (isset($p['user'])) ? $p['user'] : self::DEFAULT_USER;
        $pwd = (isset($p['pwd'])) ? $p['pwd'] : self::DEFAULT_PASSWORD;
        $newlink = (isset($p['newlink'])) ? $p['newlink'] : self::DEFAULT_NEWLINK;

        if(isSet($sqlparams['fieldskeycase'])){
            switch ($sqlparams['fieldskeycase']) {
                case 'CASE_LOWER':
                    $sqlparams['fieldskeycase'] = CASE_LOWER;
                    break;
                case 'CASE_UPPER':
                    $sqlparams['fieldskeycase'] = CASE_UPPER;
                    break;
            }
        }
        $driver_dbms = dirname(__FILE__) . "/$this->dbms.php";
        $table_dbms = dirname(__FILE__) . "/TABLE.php";
        //caricamento driver DBMS
        if (!file_exists($driver_dbms))
            throw new Exception("Driver DBMS $driver_dbms non trovato");
        if (!file_exists($table_dbms))
            throw new Exception("Table class $table_dbms non trovato");
        include_once($driver_dbms);
        include_once($table_dbms);
        //$this->registraAzione("Caricato il driver $driver_dbms");
        $classe = "ITA_" . $this->dbms;
        $this->lettore = new $classe($host, $db, $user, $pwd, $newlink, $sqlparams);
    }

    /**
     * 
     * @return type
     */
    public function getDBMS() {
        return $this->dbms;
    }

    /**
     * 
     * @return type
     */
    public function getDB() {
        return $this->lettore->getDB();
    }

    /**
     * 
     * @return array
     */
    public function getSqlparams() {
        return $this->lettore->getSqlparams();
    }

    /**
     * 
     * @return type
     */
    public function getServer() {
        return $this->lettore->getServer();
    }

    /**
     * 
     * @return type
     */
    public function getUser() {
        return $this->lettore->getUser();
    }

    /**
     * 
     * @return type
     */
    public function getPassword() {
        return $this->lettore->getPassword();
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
        return $this->lettore->quote($val);
    }

    /**
     * Esegue una query sul DB
     * @param string $txt Testo della query da eeguire
     * @param boolean $chiudiConn Indica se si vuole chiudere o meno la connessione al db dopo l'esecuzione della query
     * @return array Un array associativo o non associativo o un singolo valore contenente il risultato della query
     */
    public function query($txt, $chiudiConn = FALSE) {
        return $this->lettore->query($txt, $chiudiConn);
    }

    /**
     * Per ottenere uno o più campi di una o più righe
     * @param type $txt
     * @return type
     */
    public function queryCount($txt) {
        return $this->lettore->queryCount($txt);
    }

    /**
     * 
     * @param type $txt
     * @param type $da
     * @param type $per
     * @param type $chiudiConn
     * @return type
     */
    public function queryMultipla($txt, $da = '', $per = '', $chiudiConn = FALSE) {
        return $this->lettore->queryMultipla($txt, FALSE, $da, $per, $chiudiConn);
    }

    /**
     * Versione di queryMultipla con indici numerici
     * @param type $txt
     * @param type $da
     * @param type $per
     * @param type $chiudiConn
     * @return type
     */
    public function queryMultiplaI($txt, $da = '', $per = '', $chiudiConn = FALSE) {
        return $this->lettore->queryMultipla($txt, TRUE, $da, $per, $chiudiConn);
    }

    /**
     * Per ottenere uno o più campi di una specifica riga
     * @param type $txt
     * @param type $chiudiConn
     * @return type
     */
    public function queryRiga($txt, $chiudiConn = FALSE) {
        return $this->lettore->queryRiga($txt, FALSE, $chiudiConn);
    }

    /**
     * Versione di queryRiga con indici numerici
     * @param type $txt
     * @param type $chiudiConn
     * @return type
     */
    public function queryRigaI($txt, $chiudiConn = FALSE) {
        return $this->lettore->queryRiga($txt, TRUE, $chiudiConn);
    }

    /**
     * Per ottenere un valore di una campo
     * @param type $txt
     * @param type $chiudiConn
     * @return type
     */
    public function queryValore($txt, $chiudiConn = FALSE) {
        return $this->lettore->queryValore($txt, $chiudiConn);
    }

    /**
     * Per ottenere più valori di un campo
     * @param type $txt
     * @param type $chiudiConn
     * @return type
     */
    public function queryValori($txt, $chiudiConn = FALSE) {
        return $this->lettore->queryValori($txt, $chiudiConn);
    }

    /**
     * Esegue una DELETE o una UPDATE o una INSERT
     * @param string $txt Testo della query
     * @return integer Numero delle righe modificate
     */
    public function update($txt) {
        return $this->lettore->update($txt);
    }

    /**
     * Permette di chiudere manualmente la connessione al DB
     * @return boolean TRUE se la connessione è stata chiusa correttamente, FALSE altrimenti
     */
    public function chiudiConn() {
        return $this->lettore->chiudiConn();
    }

    /**
     * Ritorna l'SQL per una stringa vuota
     * @return type
     */
    public function blank() {
        return $this->lettore->blank();
    }

    /**
     * Ritorna l'SQL per la comparazione con una stringa vuota
     * @return type
     */
    public function isBlank() {
        return $this->lettore->isBlank();
    }

    /**
     * Ritorna l'SQL per la comparazione negata con una stringa vuota
     * @return type
     */
    public function isNotBlank() {
        return $this->lettore->isNotBlank();
    }

    /**
     * 
     * @return type
     */
    public function getJdbcDriverClass() {
        return $this->lettore->getJdbcDriverClass();
    }

    /**
     * 
     * @return type
     */
    public function getJdbcConnectionUrl() {
        return $this->lettore->getJdbcConnectionUrl();
    }

    /**
     * Ritorna l'SQL per il substring di un campo
     * @param type $value
     * @param type $start
     * @param type $len
     * @return type
     */
    public function subString($value, $start, $len) {
        return $this->lettore->subString($value, $start, $len);
    }

    /**
     * Crea la string SQL per la concatenazione di elementi CHAR
     * @return string
     */
    public function strConcat() {
        $strArg = func_get_args();
        $strCount = func_num_args();
        return $this->lettore->strConcat($strArg, $strCount);
    }

    /**
     * Ritorna l'SQL per il coalesce, che restituisce il primo argumento
     * non NULL della query
     * @return type
     */
    public function coalesce() {
        $coalesceArg = func_get_args();
        $coalesceCount = func_num_args();
        return $this->lettore->coalesce($coalesceArg, $coalesceCount);
    }

    /**
     * Ritorna l'SQL per il NULLIF, che restituisce NULL se i valori sono uguali,
     * altrimenti ritorna il primo valore
     * @param type $var1
     * @param type $var2
     * @return type
     */
    public function nullIf($var1, $var2) {
        return $this->lettore->nullIf($var1, $var2);
    }

    /**
     * Ritorna l'SQL per il DATEDIFF, che restituisce la differenza in giorni
     * tra due date nel formato 'Ymd'
     * @param type $beginDate
     * @param type $endDate
     * @return type
     */
    public function dateDiff($beginDate, $endDate) {
        return $this->lettore->dateDiff($beginDate, $endDate);
    }

    /**
     * Ritorna l'SQL per il lowercase di una stringa
     * @param type $value
     * @return type
     */
    public function strLower($value) {
        return $this->lettore->strLower($value);
    }

    /**
     * Ritorna l'SQL per l'uppercase di una stringa
     * @param type $value
     * @return type
     */
    public function strUpper($value) {
        return $this->lettore->strUpper($value);
    }
    
    /**
     * Ritorna l'SQL per match bolleano con regEx
     * @param type $value
     * @param type $expression
     * @return type
     */
    public function regExBoolean($value, $expression) {
        return $this->lettore->regExBoolean($value, $expression);
    }

    /**
     * Ritorna l'SQL per il round di un numero
     * @param type $value
     * @param type $decimal
     * @return type
     */
    public function round($value, $decimal = 0) {
        return $this->lettore->round($value, $decimal);
    }

    /**
     * Restituisce il prossimo valore della sequenza indicata
     * dal precedente query INSERT
     * @return type
     */
    
    public function nextval($sequence) {
        return $this->nextval($sequence);
    }

    /**
     * Restituisce l'identificativo generato per una colonna AUTO_INCREMENT
     * dal precedente query INSERT
     * @return type
     */
    public function getLastId() {
        return $this->lettore->getLastId();
    }

    /**
     * Permette di controllare se la connessione al DB è disponibile
     * @return boolean TRUE se la connessione è disponibile, FALSE altrimenti
     */
    public function testConn() {
        return $this->lettore->testConn();
    }

    /**
     * Controlla se il DB è presente o meno
     * @return boolean
     */
    public function exists() {
        return $this->lettore->exists();
    }

    /**
     * 
     */
    public function create() {
        $this->lettore->create();
    }

    /**
     * 
     * @return type
     */
    public function version() {
        return $this->lettore->version();
    }

    /**
     * Ritorna una lista delle tabelle nel DB
     * @return type
     */
    public function listTables() {
        return $this->lettore->listTables();
    }

    /**
     * Ritorna le info riguardanti le tabelle nel DB
     * @return type
     */
    public function getTablesInfo() {
        return $this->lettore->getTablesInfo();
    }

    /**
     * Ritorna la chiave primaria della tabella indicata
     * @param type $table
     * @return type
     */
    public function getPrimaryKey($table) {
        return $this->lettore->getPrimaryKey($table);
    }

    /**
     * 
     * @param type $table
     * @return type
     */
    public function getTableObject($table) {
        return $this->lettore->getTableObject($table);
    }

    /**
     * Ritorna le info riguardanti le colonne della tabella indicata
     * @param type $table
     * @return type
     */
    public function getColumnsInfo($table) {
        return $this->lettore->getColumnsInfo($table);
    }

    /**
     * Ritorna le info riguardanti gli indici della tabella indicata
     * @param type $table
     * @return type
     */
    public function getIndexesInfo($table) {
        return $this->lettore->getIndexesInfo($table);
    }

    /**
     * 
     * @param type $fieldInfo
     * @param type $value
     * @return type
     */
    public function getNormalizedValue($fieldInfo, $value) {
        return $this->lettore->getNormalizedValue($fieldInfo, $value);
    }

    /**
     * Ritorna il numero di tabelle presenti all'interno del DB
     * @return type
     */
    public function countTables() {
        return $this->lettore->countTables();
    }

}

?>