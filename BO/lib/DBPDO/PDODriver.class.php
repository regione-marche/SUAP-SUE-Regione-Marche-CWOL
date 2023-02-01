<?php
require_once ITA_LIB_PATH . '/itaPHPCore/itaLib.class.php';

/**
 *
 * Superclasse driver di interfaccia con il database
 *
 * @author Massimo Biagioli - Lorenzo Pergolini
 *
 */
abstract class PDODriver {

    const DEFAULT_TIMEOUT = 300;
    const DEFAULT_LOCK_TIMEOUT = 60; //espresso in secondi 
    const KEY_CONSTRAINT_UNIQUE_EXCEPTION = -98;
    const KEY_LOCK_EXCEPTION = -99;
    const OPERATION_INSERT = 1;
    const OPERATION_UPDATE = 2;
    const OPERATION_DELETE = 3;
    const KEY_CONNECTION_ARRAY = "CONNECTIONS";
    const ERROR_MANUAL_TRANSACTION = "E' già aperto una transazione per la connessione";
    const ERROR_FIELD_EXCLUDED = "Campo non previsto";
    const ERROR_LOCKTABLE = "E' necessario aprire una transazione per poter effettuare il blocco della tabella";
    const ERROR_LOCKROWTABLE = "E' necessario aprire una transazione per poter effettuare il blocco del record della tabella";
    const ERROR_LOCKROWTABLE_RECORD_MISSING = "E' necesario avere una chiave valida per effettuare il  blocco del record della tabella";
    const PRECONDITION_MISSING_HOST = "Parametro host non valorizzato";
    const PRECONDITION_MISSING_DBNAME = "Parametro dbname mancante";
    const PRECONDITION_MISSING_PORT = "Parametro id della port mancante";
    const PRECONDITION_MISSING_USER = "Parametro user mancante";
    const PRECONDITION_MISSING_PASSWORD = "Parametro password mancante";

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
    protected $jdbcDriverClass;

    /**
     * Mappa la relativa connection url jdbc
     * @var string
     */
    protected $jdbcConnectionUrl = "";

    /**
     * Identifica la connessione al server attiva
     * @var resource
     */
    protected $linkid = FALSE;

    /**
     * Nome connessione
     * @var string
     */
    protected $connectionName;

    /**
     * Indica se devono essere gestite le transazioni
     * @var bool 
     */
    private $attivaTransazioni;

    /**
     * Info transazioni
     * @var array 
     */
    protected $transactionInfo;

    /**
     * Contiene l'ultimo id inserito
     * @var mixed 
     */
    protected $lastId;

    /**
     * Contiene il timeout dello script php letto da php.ini (per ripristino dopo la transazione
     * @var int
     */
    private $oldTimeout;

    function __construct($host, $db, $user, $pwd, $newlink, $sqlparams = array(), $connectionName) {
        $this->sqlhost = $host;
        $this->sqluser = $user;
        $this->sqlpass = $pwd;
        $this->sqldb = $db;
        $this->newlink = $newlink;
        $this->sqlparams = $sqlparams;
        $this->connectionName = $connectionName;
    }

    /**
     * Apertura database
     * @return true/false
     * @throws ItaException
     */
    public function apriDB() {
        try {
//controllo host separato da # per multiple host

            if (strpos($this->sqlparams['host'], ' ')) {
                $hosts = explode(' ', $this->sqlparams['host']);
            }
            if (!empty($hosts)) {
                $host = array();
                $port = array();
                foreach ($hosts as $spitHost) {
                    list($tempH, $tempP) = explode(":", $spitHost);
                    $host[] = $tempH;
                    $port[] = $tempP;
                }
            } else {
//nel caso sia un monoHost
                list($host, $port) = explode(":", $this->sqlparams['host']);
            }

//precondizioni
            if (!$host) {
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_MISSING_HOST);
            }
            if (!$this->sqldb) {
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_MISSING_DBNAME);
            }

// Controlla se a livello di connessione devono essere attivate le transazioni
            //$this->attivaTransazioni = isset($this->sqlparams['enableTransactions']) ? $this->sqlparams['enableTransactions'] : Config::getConf('dbms.attivaTransazioni');

// crea nuovo oggetto connessione impostando le proprietà di base
            $this->linkid = $this->createPDOObject($this->sqlparams['database'], $host, $port, $this->sqlparams['user'], $this->sqlparams['pwd']);
            if (!$this->linkid) {
                $this->gestioneErrore();
            }
            $this->linkid->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->linkid->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->linkid->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
            $this->linkid->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);

// opzioni di connessione
            $this->initCustomDBAttributes();

            return true;
        } catch (ItaException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    private function saveConnectionPerRequest() {
        ItaDB::$connectionPerRequest->saveConnectionPerRequest($this->connectionName, $this->linkid);
    }

    /**
     * Rilascia risorsa relativa alla connessione
     */
    public function chiudiDB() {
        if ($this->isTransactionOpen()) {
            $this->commitTransaction();
        }
        $this->linkid = null;
    }

    public function setCustomAttribute($attr_key, $attr_value) {
        try {
            $this->safeOpenConnection();
            $this->linkid->setAttribute($attr_key, $attr_value);
        } catch (ItaException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    public function setUseBufferedQuery($use) {
    
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
        try {
            $this->safeOpenConnection();

            if ($multipla) {
                $this->preQueryMultipla($sql, $params);
            } else {
                $this->preQueryRiga($sql, $params);
            }
            if ($multipla === true && is_numeric($da) && is_numeric($per)) {
                $this->addLimitOffsetToQuery($sql, $da, $per);
            }
            $statement = $this->query($sql, $params, false, $infoBinaryFields);
            return $statement;
        } catch (ItaException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
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

        $fetchOnlyRow = !$multipla;

        try {
            if (!$infoBinaryFields) {
                if ($indiciNumerici) {
                    $result = $this->fetch($statement, $fetchOnlyRow, PDO::FETCH_BOTH);
                } else {
                    $result = $this->fetch($statement, $fetchOnlyRow);
                }
            } else {
//effettua la fetch dei binary su $infoBinaryFields
                $statement->fetch(PDO::FETCH_BOUND);
//scorro tutte le colonne string e le trasformo in binary
                foreach ($infoBinaryFields['results'] as &$infoBinaryField) {
                    $this->manageBinary($infoBinaryField);
                }

                $result = $infoBinaryFields['results'];
            }

            if ($result === false) {
                $this->gestioneErrore();
                return false;
            }

            $this->adaptResults($statement, $result, $multipla);

            if ($multipla) {
                $this->postQueryMultipla($result);
            } else {
                $this->postQueryRiga($result);
            }
            if ($result && $infoBinaryCallback) {
//effettua la callback per il caricamento   
                if ($multipla) {
                    foreach($result as $k=>$value){
                        $result[$k] = $this->postQueryMultiplaCallback($value, $infoBinaryCallback);
                    }
                } else {
                    $result = $this->postQueryRigaCallback($result, $infoBinaryCallback);
                }
            }
            return $result;
        } catch (ItaException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    /**
     * Elimina statement PDO
     * 
     * @param type $statement
     * @param type $closeConnection
     */
    public function queryRemove($statement, $closeConnection = false) {
        $this->eliminaQuery($statement);
        if ($closeConnection) {
            $this->chiudiDB();
        }
    }

    /**
     * Lettura di una row
     * @param string $sql Comando sql
     * @param array $params Array di parametri
     * @param array $infoBinaryCallback Array necessario per caricare i dati in binario tramite callback (mssql, oracle)
     * @param array $infoBinaryFields Informazioni necessarie per descrivere i campi binary
     * @param boolean $indiciNumerici True/False a seconda del modo di valorizzazione del Resultset (True=FETCH_BOTH; False=FETCH_ASSOC
     * @param boolean $closeConnection Se true, chiude la connessione dopo aver eseguito il comando SQL
     * @return array Risultati
     * @throws ItaException
     */
    public function queryRiga($sql, $params = array(), $infoBinaryCallback = array(), $infoBinaryFields = array(), $indiciNumerici = false, $closeConnection = false) {
        try {
            $statement = $this->queryPrepare($sql, $params, $infoBinaryFields);
            if (!$statement) {
                $this->gestioneErrore();
            }

            $result = $this->queryFetch($statement, $infoBinaryCallback, $infoBinaryFields, $indiciNumerici);

            $this->queryRemove($statement, $closeConnection);

            return $result;
        } catch (ItaException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    protected function manageBinary(&$input) {
        
    }

    /**
     * effettua la copia (duplicazione) di una tabella del Data Base  (? specializzato per ogni Data Base)
     * @param string $tableFrom Tabella di partenza
     * @param type $tableTo  Tabella di destinazione
     * @return boolean  esisto dell'operazione di copia 
     */
    public function copyTable($tableFrom, $tableTo) {
// prerequisiti 
        if (!$tableFrom || !$tableTo) {
            ItaException::newItaException(ItaException::TYPE_ERROR_PHP, "Prerequisiti non rispettati nel metodo copyTable");
            return false;
        }
        if ($this->tableExists($tableTo)) {
            ItaException::newItaException(ItaException::TYPE_ERROR_PHP, "Tabella di destinazione $tableTo già esistente nel database");
            return false;
        }

        $sqlCopy = $this->getSqlCopyTable($tableFrom, $tableTo);
        return $this->execDirect($sqlCopy, false);
    }

    /**
     * Lettura di una lista
     * @param string $sql Comando sql     
     * @param boolean $indiciNumerici True/False a seconda del modo di valorizzazione del Resultset (True=FETCH_BOTH; False=FETCH_ASSOC)
     * @param int $da Offset
     * @param int $per Limit
     * @param boolean $closeConnection Se true, chiude la connessione dopo aver eseguito il comando SQL
     * @param array $params Array di parametri
     * @param array $infoBinaryCallback Array necessario per caricare i dati in binario tramite callback (mssql, oracle)
     * @param array $infoBinaryFields Informazioni necessarie per descrivere i campi binary

     * @return array Risultati
     * @throws ItaException
     */
    public function queryMultipla($sql, $indiciNumerici = false, $da = '', $per = '', $closeConnection = false, $params = array(), $infoBinaryCallback = array(), $infoBinaryFields = array()) {
        try {
            $statement = $this->queryPrepare($sql, $params, $infoBinaryFields, true, $da, $per);
            if (!$statement) {
                $this->gestioneErrore();
            }

            $result = $this->queryFetch($statement, $infoBinaryCallback, $infoBinaryFields, $indiciNumerici, true);

            $this->queryRemove($statement, $closeConnection);

            return $result;
        } catch (ItaException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    /**
     * Esecuzione di un comando sql
     * @param string $sql Comando sql
     * @param array $params Array di parametri
     * @param boolean $closeConnection Se true, chiude la connessione dopo aver eseguito il comando SQL
     * @return object Statement
     * @throws ItaException
     */
    public function query($sql, $params = array(), $closeConnection = false, &$infoBinaryFields = array()) {
        try {
            $this->safeOpenConnection();

// Se l'array dei parametri è vuoto, significa che il comando sql è stato già costruito senza l'utilizzo dei placeholders,
// al contrario, invece, significa che si sta eseguento un'istruzione di insert/update/delete
// oppure una select con dei placeholders
            $statement = $this->linkid->prepare($sql);
            if (count($params) > 0) {
                foreach ($params as $param) {
                    $statement->bindParam($param['name'], $param['value'], $param['type']);
                }
            }

            if ($infoBinaryFields !== false && count($infoBinaryFields)) {
                $this->queryBinary($statement, $infoBinaryFields);
            }

            $statement->execute();

            if (!$statement) {
                $this->gestioneErrore();
            }

            if ($closeConnection) {
                $this->chiudiDB();
            }

            return $statement;
        } catch (ItaException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    protected function fetch($statement, $fetchOnlyRow = false, $fetchStyle = null, $transformerName = '') {
        if ($fetchOnlyRow) {
            if ($fetchStyle === null) {
                $result = $statement->fetch();
            } else {
                $result = $statement->fetch($fetchStyle);
            }
        } else {
            if ($fetchStyle === null) {
                $result = $statement->fetchAll();
            } else {
                $result = $statement->fetchAll($fetchStyle);
            }
        }

        $this->cleanSpaces($result);
        $this->postFetch($result);

        if ($transformerName !== '') {
            $result = array_map(array($this, $transformerName), $result);
        }

        return $result;
    }

    /**
     * Rilascia lo statement
     * @param object $statement Statement
     * @throws type newItaException
     */
    public function eliminaQuery($statement) {
        try {
            if (!$statement->closeCursor()) {
                $this->gestioneErrore();
            }
        } catch (ItaException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    /**
     * Esecuzione diretta di un comando sql
     * @param string $sql Comando sql

     * @return integer Numero di record coinvolti nell'operazione
     * @throws ItaException
     */
    public function execDirect($sql, $closeConnection = false) {
        try {

            $this->safeOpenConnection();

            $result = $this->linkid->exec($sql);

            if ($closeConnection) {
                $this->chiudiDB();
            }

            return $result;
        } catch (ItaException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    public function dbInsert($tableDef, $data) {
        try {
            return $this->dbExecute($tableDef, $data, self::OPERATION_INSERT);
        } catch (ItaException $ex) {
            if ($this->isErrorCodeConstraintUnique($ex)) {
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, self::KEY_CONSTRAINT_UNIQUE_EXCEPTION, "Violazione vincolo primario");
            } else {
                throw $ex;
            }
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    public function dbUpdate($tableDef, $data, $oldData) {
        try {
            return $this->dbExecute($tableDef, $data, self::OPERATION_UPDATE, $oldData);
        } catch (ItaException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    public function dbDelete($tableDef, $data) {
        try {
            return $this->dbExecute($tableDef, $data, self::OPERATION_DELETE);
        } catch (ItaException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

    private function checkForExcludeField($field, $tableDef, $operation, &$manualKeyValues, $data, $oldData = null) {
        $excludeFieldStrategy = $this->getExcludeFieldStrategy();

//Controllo di non star passando campi non presenti nella tabella da aggiornare
        if (!in_array($field, array_keys($tableDef->getFields()))) {
            if ($excludeFieldStrategy === 'error') {
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::ERROR_FIELD_EXCLUDED . ': ' . $field);
            } else {
                return true;
            }
        }

        if ($operation === self::OPERATION_INSERT) {
            if (in_array($field, $tableDef->getPks())) {
                if ($tableDef->isAutoKey($field)) {
                    return true;
                } else {
                    $manualKeyValues[] = array("KEY" => $field, "VALUE" => $data[$field]);
                }
            }
        } else if ($operation === self::OPERATION_UPDATE) {
            if (in_array($field, $tableDef->getPks())) {
                return true;
            }
            if (isSet($oldData) && is_array($oldData) && array_key_exists($field, $oldData) && itaModelValidator::checkEq($data[$field], $oldData[$field], false)) {
                return true;
            }
        }
        return false;
    }

    private function getExcludeFieldStrategy() {
        $excludeFieldStrategy = 'exclude';
        if (isset($this->sqlparams['excludeFieldStrategy'])) {
            $excludeFieldStrategy = strtolower($this->sqlparams['excludeFieldStrategy']);
            if (!in_array($excludeFieldStrategy, array('exclude', 'error'))) {
                $excludeFieldStrategy = 'exclude';
            }
        }
        return $excludeFieldStrategy;
    }

    private function dbExecute($tableDef, $data, $operation, $oldData = array()) {
        $paramsFields = array();

        if ($operation === self::OPERATION_UPDATE || $operation === self::OPERATION_INSERT) {
            $paramField[] = array();
            $fieldNamesAlias = array();
            $manualKeyValues = array();

            $this->prepareColumnsName($data);
            $blobInfo = "";

// Effettua il controllo sui campi null
            $dbmsTypes = $this->getDbmsBaseTypes();
            $tableFields = $tableDef->getFields();

            foreach (array_keys($data) as $field) {
                $tmpAdpaptBlobInfo = $this->adapterValueForSpecificDb($field, $data[$field], $tableFields[$field], $operation);

                if (!$this->checkForExcludeField($field, $tableDef, $operation, $manualKeyValues, $data, $oldData)) {
                    if (($operation === self::OPERATION_INSERT || $operation === self::OPERATION_UPDATE) &&
                            ($tableFields[$field]['null'] == 1) &&
                            empty($data[$field])) {
                        if ($operation == self::OPERATION_UPDATE) {
                            $fieldNames[$field] = "$field = NULL";
                        } elseif ($operation === self::OPERATION_INSERT) {
                            $fieldNames[$field] = $field;
                            array_push($fieldNamesAlias, "NULL");
                        }
                    } else {
                        if ($operation === self::OPERATION_UPDATE) {
                            $fieldNames[$field] = "$field=:$field";
                        } elseif ($operation === self::OPERATION_INSERT) {
                            $fieldNames[$field] = $field;
                            array_push($fieldNamesAlias, ":$field");
                        }

                        $data[$field] = PDOHelper::checkNull($dbmsTypes[$tableFields[$field]['type']]['php'], $data[$field], isset($this->sqlparams['defaultString']) ? $this->sqlparams['defaultString'] : 'empty');
                        if ($tmpAdpaptBlobInfo) {
                            $blobInfo = $tmpAdpaptBlobInfo;
                            $fieldNamesAlias[count($fieldNamesAlias) - 1] = "EMPTY_BLOB()";
                        }
// Aggiorna array dei parametri
                        $paramField = array(
                            'name' => $field,
                            'value' => $data[$field],
                            'type' => $dbmsTypes[$tableFields[$field]['type']]['pdo']
                        );
                        $paramsFields[] = $paramField;
                    }
                }
            }
        }

// Prepara comando sql
        if ($operation === self::OPERATION_UPDATE) {
            if (count($fieldNames) == 0) {
// riga da non aggiornare perchè tutti i campi sono uguale 
                return 1;
            }
            $sql = "UPDATE " . $tableDef->getName() . " SET " . implode(',', $fieldNames) . " WHERE " . PDOHelper::getSqlConditionsForUpdateOrDelete($this, $tableDef, $data, $paramsFields) . $blobInfo;
        } else if ($operation === self::OPERATION_INSERT) {
            $sql = "INSERT INTO " . $tableDef->getName() . "(" . implode(',', $fieldNames) . ") VALUES (" . implode(",", $fieldNamesAlias) . ") " . $blobInfo;
        } else {
            $sql = "DELETE FROM " . $tableDef->getName() . " WHERE " . PDOHelper::getSqlConditionsForUpdateOrDelete($this, $tableDef, $data, $paramsFields);
        }

// Esegue comando sql
        $statement = $this->query($sql, $paramsFields);
        if (!$statement) {
            $this->gestioneErrore();
        }
// Restituisce il numero di righe aggiornate
        $n = $statement->rowCount();

// Nel caso di una insert, imposta l'id (o gli id nel caso di chiavi multiple) dell'ultimo record inserito 
        if ($operation === self::OPERATION_INSERT) {
            $this->setLastId($tableDef, $manualKeyValues);
        }

        $this->queryRemove($statement);

        return $n;
    }

    /**
     * Effettua una BEGIN TRANSACTION
     * @param int $isolation Tipo di isolamento: 1=Serializable;  2=Read committed
     * @throws ItaException
     */
    public function beginTransaction($isolation = 1, $manual = false, $timeout = null) {
        try {
            if (!$this->getAttivaTransazioni() || $this->isTransactionOpen()) {
                return;
            }

            $this->safeOpenConnection();


            $this->preBeginTransaction($isolation);

// neal caso sto forzando l'apertura manuael della transazione ma c'è una già annidata 
            if ($manual == true && $this->isTransactionOpen()) {
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::ERROR_MANUAL_TRANSACTION);
            }

            if ($this->beginTransactionCommand()) {
                $this->addBeginTransaction($manual);
            } else {
                $this->gestioneErrore();
            }

            $this->oldTimeout = ini_get('max_execution_time');
            if (isSet($timeout)) {
                ini_set('max_execution_time', $timeout);
            } elseif (isSet($this->sqlparams['transactionTimeout'])) {
                ini_set('max_execution_time', $this->sqlparams['transactionTimeout']);
            } else {
                ini_set('max_execution_time', self::DEFAULT_TIMEOUT);
            }
            $this->postBeginTransaction($isolation);
        } catch (ItaException $ex) {
            ini_set('max_execution_time', (isSet($this->oldTimeout) ? $this->oldTimeout : 0));
            throw $ex;
        } catch (Exception $ex) {
            ini_set('max_execution_time', (isSet($this->oldTimeout) ? $this->oldTimeout : 0));
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

//Effettua il lock fisisco della tablella specificata
    public function lockTable($tableName, $exclusive = false) {
        $status = false;
        $this->transactionInfo = ItaDB::$connectionPerRequest->getTransactionInfo($this->connectionName);
//controllo se ho aperto la transazione altrimenti non faccio nulla 
        if ($this->transactionInfo['transactions'] == 1) {
            $sqlLockTable = $this->lockTableCommand($tableName, $exclusive);
            if (strlen($sqlLockTable) > 0) {
                $status = $this->execDirect($sqlLockTable, false);
            }
        } else {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::ERROR_LOCKTABLE);
        }
        return $status;
    }

//Effettua il lock fisisco della chiave della tablella specificata usa il wait di default self::DEFAULT_LOCK_TIMEOUT
    public function lockRowTable($tableName, $params = array()) {
        $recordLock = array();
        $this->transactionInfo = ItaDB::$connectionPerRequest->getTransactionInfo($this->connectionName);
//controllo se ho aperto la transazione altrimenti lancio eccezione 
        if ($this->transactionInfo['transactions'] == 1) {
            $sqlLockTable = $this->lockRowTableCommand($this->createSqlLockRowTable($tableName, $params));
            $recordLock = $this->locRowTableExecuteCommand($sqlLockTable, $params, $tableName);
        } else {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::ERROR_LOCKROWTABLE);
        }

        return $recordLock;
    }

    private function locRowTableExecuteCommand($sqlLockTable, $params, $tableName) {
        try {
            if (strlen($sqlLockTable) > 0) {
                $recordLock = $this->queryRiga($sqlLockTable, $params);
            }
        } catch (Exception $ex) {
            if ($this->isErrorCodelockRowTable($ex)) {
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, self::KEY_LOCK_EXCEPTION, "Record bloccato per la tabella $tableName ");
            } else {
                throw $ex;
            }
        }
        return $recordLock;
    }

    protected abstract function isErrorCodelockRowTable($ex);

    public abstract function isErrorCodeConstraintUnique($ex);

    private function createSqlLockRowTable($tableName, $params) {
        $baseSqlLock = " SELECT * FROM $tableName ";
        $bseSqlLockWhere = " WHERE ";
        $i = 1;
        foreach ($params as $value) {
            $bseSqlLockWhere .= $value["name"] . '=:' . $value["name"] . ($i !== count($params) ? " AND " : "");
            $i++;
        }
        return $baseSqlLock . $bseSqlLockWhere;
    }

//Estratte command beginTranasction  s
    protected function beginTransactionCommand() {
        return $this->linkid->beginTransaction();
    }

//Estratte command commitTranasction  
    protected function commitTransactionCommand() {
        return $this->linkid->commit();
    }

//Estratte command rollbackTranasction  
    protected function rollbackTransactionCommand() {
        return $this->linkid->rollback();
    }

    /**
     * Effettua una COMMIT TRANSACTION
     * @param bool $skip true=non effettua la commit
     * @throws ItaException
     */
    public function commitTransaction($skip = false, $manual = false) {
        try {
            if ($this->ignoreCommitRollback($skip)) {
                return;
            }
//Aggiungo controllo se transazione gestita in manuale la commit deve essere fatta in manuale 
            $transactionInfo = ItaDB::$connectionPerRequest->getTransactionInfo($this->connectionName);
            if ($transactionInfo["manual"] == true && $manual == false) {
                return;
            }

            $this->safeOpenConnection();

            $this->preCommitTransaction($skip);

            if ($this->commitTransactionCommand()) {
                $this->addCommitTransaction($manual);
            } else {
                $this->gestioneErrore();
            }

            $this->postCommitTransaction($skip);
            ini_set('max_execution_time', (isSet($this->oldTimeout) ? $this->oldTimeout : 0));
        } catch (Exception $ex) {
            ini_set('max_execution_time', (isSet($this->oldTimeout) ? $this->oldTimeout : 0));
            throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, $ex->getCode(), $ex->getMessage());
        }
    }

    /**
     * Controlla se deve essere ignorata la commit.
     * @param bool $skip true=non effettua la commit
     * @return bool true se la commit deve essere ignorata, altrimenti false
     */
    private function ignoreCommitRollback($skip = false) {
        return ($skip || !$this->getAttivaTransazioni() || !$this->isTransactionOpen());
    }

    /**
     * Effettua una ROLLBACK TRANSACTION
     * @param bool $skip true=non effettua la rollback
     * @throws ItaException
     */
    public function rollbackTransaction($skip = false, $manual = false) {
        try {
            if ($this->ignoreCommitRollback($skip)) {
                return;
            }

            $transactionInfo = ItaDB::$connectionPerRequest->getTransactionInfo($this->connectionName);
            if ($transactionInfo["manual"] == true && $manual == false) {
                return;
            }

            $this->safeOpenConnection();

            $this->preRollbackTransaction($skip);

            if ($this->rollbackTransactionCommand()) {
                $this->addRollbackTransaction();
            } else {
                $this->gestioneErrore();
            }

            $this->postRollbackTransaction($skip);
            ini_set('max_execution_time', (isSet($this->oldTimeout) ? $this->oldTimeout : 0));
        } catch (ItaException $ex) {
            ini_set('max_execution_time', (isSet($this->oldTimeout) ? $this->oldTimeout : 0));
            throw $ex;
        } catch (Exception $ex) {
            ini_set('max_execution_time', (isSet($this->oldTimeout) ? $this->oldTimeout : 0));
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }

// ---------------
// Metodi "PRE" e "POST" transazioni. Effettuare override nei driver specifici al bisogno.    
    protected function preBeginTransaction($isolation) {
        
    }

    protected function postBeginTransaction($isolation) {
        
    }

    protected function preCommitTransaction($skip) {
        
    }

    protected function postCommitTransaction($skip) {
        
    }

    protected function preRollbackTransaction($skip) {
        
    }

    protected function postRollbackTransaction($skip) {
        
    }

// Metodi "PRE" e "POST" fetch. Effettuare override nei driver specifici al bisogno.    
    protected function preFetch() {
        
    }

    protected function postFetch(&$result) {
        
    }

    protected function cleanSpaces(&$result) {
        if (is_array($result) && array_key_exists('stripspaces', $this->sqlparams) && $this->sqlparams["stripspaces"]) {
            $this->cleanSpacesExecute($result);
        }
    }

    private function cleanSpacesExecute(&$result) {
        array_walk_recursive($result, function(&$item, $key) {
            if (is_string($item)) {
                $item = trim($item);
            }
        });
    }

    protected function preQueryRiga(&$sql, &$params) {
        
    }

    protected function queryBinary(&$statement, &$infoBinaryFields) {
        
    }

//Effettua la callback per caricare singolarmente un binary 
//dopo aver usato queryRiga su mssql oppure sistema i dati su oracle per linux
    protected function postQueryRigaCallback($result, $infoBinaryCallback) {
        return $result;
    }

//Effettua la callback per caricare singolarmente un binary dopo aver usato queryMultipla
//oppure sistema i dati su oracle per linux
    protected function postQueryMultiplaCallback($results, $infoBinaryCallback) {
        return $results;
    }

    protected function postQueryRiga(&$result) {
        
    }

    protected function preQueryMultipla(&$sql, &$params) {
        
    }

    protected function postQueryMultipla(&$result) {
        
    }

    protected function adaptResults($statement, &$result) {
        
    }

    /**
     * Ritorna alias di preso da una stringa Sql 
     * @param string $sql Comando sql
     * @param string $tableName nome tabella
     * @return string
     */
    public function getSqlAlias($sql, $tableName) {
        $arrControlli = $this->getSqlToken();

        $sqlParse = self::parseSelect($sql);
        $posTableName = strpos($sqlParse["FROM"], $tableName);
//dove inizia la stringa 
        $strlen = strlen($sqlParse["FROM"]);
        $idx = $posTableName + strlen($tableName) + 1;
        for ($idx; $idx < $strlen; $idx++) {
            $char = substr($sqlParse["FROM"], $idx, 1);
            if ($char !== " ") {
                $token = $token . $char;
            } else {
// controllo se è un alias ed esco dal ciclo 
                if (!in_array($token, $arrControlli)) {
                    $alias = $token;
                } else {
                    $alias = $tableName;
                }
                return $alias;
            }
        }
        return $tableName;
    }

// ---------------

    /**
     * Effettua il parser di una stringa Sql 
     * @param $sql istruzione di cui effettuare il parse 
     * @return array composto dagli elementi 'SELECT,FROM,WHERE,ORSER,COLS')
     */
    public static function parseSelect($sql = '') {
//  inizializza la array che conterrà le parti della query       
        $arrayQuery = array("SELECT" => null,
            "FROM" => null,
            "UNION" => null,
            "WHERE" => null,
            "ORDER" => null,
            "COLS" => null);
// effettua la pulizia della stringa in ingresso  $SQL_clean_string
        $sql = trim($sql);
//converte i caratteri newline in spazi in modo che la query
//risulti come un'unica stringa SQL senza interruzioni di riga
        $sql = str_replace(chr(13) . chr(10), chr(10), $sql);
        $sql = str_replace(chr(13), chr(10), $sql);
        $sql = str_replace(chr(10), " ", $sql);
        $sql = str_replace(chr(9), " ", $sql);

// converte in maiuscolo le parti NON racchiuse tra apici e le variabili da sostituire sui parametri :PARAMETER
// onde evitare di modificare le condizioni di query (es. WHERE...)
        $temp = "";
        $quote = 0;
        $variabileParameter = 0;
        $i = 0;

        $strlen = strlen($sql);
        for ($i = 0; $i <= $strlen; $i++) {
            $char = substr($sql, $i, 1);
            if ($char === "'") {
                $quote ++;
                if ($quote > 1) {
//  reset flag al secondo apice
                    $quote = 0;
                };
            } else if ($char === ":") {
                $variabileParameter ++;
            }
            if ($variabileParameter === 1 && $char === " ") { //se ho finito la chiusura della variabile :VARIABILE
                $variabileParameter = 0;  //reset flag delle variabili
            } else if ($quote === 0 && $variabileParameter === 0) {
//  blocco non quotato o non è un parametro della query , converte in maiuscolo
                $char = strtoupper($char);
            }
            $temp = $temp . $char;
        }
        $sql = $temp;

//converte gli spazi racchiusi tra apici in caratteri di tabulazione
// per evitare di rimuovere gli spazi multipli racchiusi tra apici

        $query = PDODriver::replace_quoted($sql, ' ', chr(9));

//Elimina spazi in testa/coda
        $query = trim($query);
//la stringa $query SQL "ripulita" e "normalizzata" in modo da semplificare il successivo parsing della stessa
//elimina spazi multipli tra tokens (ma non quelli
//racchiusi tra apici)
//elimina spazi prima/dopo alcuni caratteri speciali
        while (strrpos($query, "  ") > 0) {
            $query = str_replace("  ", " ", $query);
        }

// caso particolare #1 - Parentesi tonde
        $query = str_replace('( ', '(', $query);
        $query = str_replace(' )', ')', $query);

// riconverte  i caratteri speciali nelle stringhe quotate (vedere sopra)
        $query = PDODriver::replace_quoted($query, chr(9), ' ');

//aggiunge un token finale fittizio per "chiudere" la query
//in modo da evitare controlli aggiuntivi nel loop di parsing
        $EOQ = " § ";
        $query = $query . $EOQ;

        $blocco_spec = 0; //tipo blocco speciale (parentesi o apice)
        $$token = ""; //Token attuale
        $isToken = false;
        $lastToken = ""; //Ultimo token analizzato
        $isActiveUnion = false;
        $buffer = "";
        $temp = "";
//cicla sulla stringa SQL e ne analizza le componenti
//prelevando un carattere alla volta in modo da
//poter gestire i delimitatori di blocco
        $lengthQuery = strlen($query);

        for ($idx = 0; $idx < $lengthQuery; $idx++) {
            $chr = substr($query, $idx, 1);
            if ($blocco_spec === 0) {
//verifica se il carattere è un marcatore di inizio blocco
                switch ($chr) {
                    case "'":
                        $blocco_spec = 1;
                        break;
                    case "(":
                        $blocco_spec = 2;
                        break;
                }
                if ($blocco_spec > 0) {
//  trovato inizio blocco, azzera contatore caratteri speciali
                    $car_spec = 0;
                }
            }

            if ($blocco_spec > 0) {
//siamo in un blocco, ignoriamo tutti i caratteri sino alla fine del blocco
//ma accodiamoli nel buffer di appoggio che verrà poi memorizzato
                $token = $token . $chr;

                switch ($blocco_spec) {
                    case 1: //singolo apice
                        if ($chr === "'") {
                            $car_spec ++;
                            if ($car_spec > 1) {
//caso speciale, l'apice apre e chiude il blocco
                                $car_spec = 0;
                            }
                        }
                        break;
                    case 2://parantesi_torda
                        if ($chr === "(") {
                            $car_spec ++;
                        } elseif ($chr === ")") {
//caso speciale, l'apice apre e chiude il blocco
                            $car_spec = $car_spec - 1;
                        }
                        break;
                }
                if ($car_spec < 1) {
//termine blocco, resetta indicatore blocco
                    $blocco_spec = 0;
                }
            } else {// di bloccoSpec
//siamo all'esterno di un qualsiasi blocco, procediamo
//con il parsing dei caratteri e l'estrazione dei tokens
                if ($chr === " ") {
//trovato spazio, esaminiamo il token attuale
//cercando le istruzioni che ci interessa valutare
//nel caso, accendiamo l'indicatore nuovo token
                    $temp = trim($token);

                    switch ($temp) {
                        case "SELECT":
                            IF ($isActiveUnion == FALSE) {
                                $isToken = True;
                            } else {
                                $isToken = false;
                            }
                            break;
                        case "FROM":
                            IF ($isActiveUnion == FALSE) {
                                $isToken = True;
                            } else {
                                $isToken = false;
                            }
                            break;
                        case "WHERE":
                            IF ($isActiveUnion == FALSE) {
                                $isToken = True;
                            } else {
                                $isToken = false;
                            }
                            break;
                        case "UNION":
                            $isToken = True;
                            $isActiveUnion = True;
//caso particolare, token in due parti, avanza l'indice caratteri...
                            $temp = "UNION ALL";
                            $idx = $idx + 3;
                            break;
                        case "ORDER":
//caso particolare, token in due parti, avanza l'indice caratteri...
                            $isToken = True;
                            $temp = "ORDER BY";
                            $idx = $idx + 2;
                            $isActiveUnion = false;
                            break;
                        case "§"; //marker fine query
                            $isToken = True;
                            break;
                        default :
                            $isToken = False;
                    }

                    if ($isToken === True) {
//trovato un nuovo token, se non è il primo, salviamo
//i valori relativi al token precedente nell'array di risultati
//usando la colonna relativa al token precedente; questo
//  è possibile dato che usiamo un token "fasullo" per indicare
//il termine della query string
                        if (strlen($lastToken) > 0) {
                            switch ($lastToken) {
                                case "SELECT":
                                    $arrayQuery['SELECT'] = trim($buffer);
                                    $buffer = "";
                                    $lastToken = $temp;
                                    $token = "";
                                    break;
                                case "FROM":
                                    $arrayQuery['FROM'] = trim($buffer);
                                    $buffer = "";
                                    $lastToken = $temp;
                                    $token = "";
                                    break;
                                case "WHERE";
                                    $arrayQuery['WHERE'] = trim($buffer);
                                    $buffer = "";
                                    $lastToken = $temp;
                                    $token = "";
                                    break;
                                case "ORDER BY":
                                    $arrayQuery['ORDER'] = trim($buffer);
                                    $buffer = "";
                                    $lastToken = $temp;
                                    $token = "";
                                    break;
                                case "UNION ALL":
// se c'è una UNION metto tutto insieme il buffer perchè non  mi interessa parsare la query collegata
                                    $arrayQuery['UNION'] = "UNION ALL " . trim($buffer);
                                    $buffer = "";
                                    $lastToken = $temp;
                                    $token = "";
                                    break;
                                default :
// qui, in "TEORIA", non dovrebbe entrare MAI !!!
                                    $buffer = $buffer . $chr . $token;
                                    $token = "";
                                    break;
                            }
                        } else {
                            $lastToken = $temp;
                            $token = "";
                        }
                    } else {
//non è un token, accodiamo tutto al buffer ed azzeriamo il buffer di analisi dei tokens
                        $buffer = $buffer . $chr . $token;
                        $token = "";
                    }
                } else {
//altro carattere (non spazio) accodiamo il carattere al token
                    $token = $token . $chr;
                }
            } //$bloco_spec
        } //for
// usa "*" invece dell'elenco colonne "grezzo" questa modifica
// serve ad evitare di dover ricostruire lo "stemming" nel caso
// di queries complesse/nidificate
        $arrayQuery['COLS'] = "*";
//termine parsing, ritorniamo la row popolata con i valori
//modifica per oracle ma valida anche per mssql
//nel caso che sulla selezione delle colonne uso * senza specificare la
//tabella aggiungo di default la tabella presa da
//source se esiset + di una tabella segnalo errore
        if ($arrayQuery['SELECT'] === '*') {
            $arrControll = explode(" ", $arrayQuery['FROM']);
            if (count($arrControll) === 1) {
                $arrayQuery['SELECT'] = $arrayQuery['FROM'] . '.*';
            } else if (in_array("INNER", $arrControll) || in_array("LEFT", $arrControll) || in_array("RIGHT", $arrControll)) {
                throw ItaException::newItaException(ItaException::TYPE_ERROR_CUSTOM, 9999, "parseSelect: attenzione stai usando come selezione delle colonna da estrarre '*' ma ci sono più entità in join !!");
            }
        }

        return $arrayQuery;
    }

    /**
     * Compone l'struzione Sql da eseguire utilizzando gli offset per la limit 
     * @param type $arraySQLParsed
     * @param int $da Offset
     * @param int $per Limit
     * @return String instruzione Sql da esegure per effettuare la query paginata
     */
    public static function getPageSql($arraySQLParsed, $da, $per) {
//controlli formali
        if (strlen($arraySQLParsed['SELECT']) < 1 ||
                strlen($arraySQLParsed['FROM']) < 1 || strlen($arraySQLParsed['COLS']) < 1) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_CUSTOM, 9999, "get_page_sql: parametri mancanti!! Errore parse select");
        }
        if (strlen($arraySQLParsed['ORDER']) < 1) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_CUSTOM, 9999, "get_page_sql: per l'uso della paginazione è obbligatorio fornire l'ordinamento");
        }
//compone la query di paginazione usando i parametri ricavati dalla row
//da notare che i limiti di paginazione (min/max) vengono impostati usando delle "macro"
//in tal modo per paginare su una data select sarà sufficiente usare la funzione
//$SQL_fill_paged_query che, rimpiazzando le macro con gli effettivi valori relativi ad
//una data pagina permette di trasformare la query per reperire la pagina desiderata
// evitando di dover effettuare il parsing della stessa query ad ogni cambio pagina
        $cols = $arraySQLParsed['COLS'];
        $order = $arraySQLParsed['ORDER'];
        $select = $arraySQLParsed['SELECT'];
        $from = $arraySQLParsed['FROM'];
        $union = $arraySQLParsed['UNION'];
        $where = (!empty($arraySQLParsed['WHERE']) ? ' WHERE ' . $arraySQLParsed['WHERE'] : '');

        $orderNew = preg_replace('/([A-Za-z0-9][A-Za-z0-9_]*)\.([A-Za-z0-9][A-Za-z0-9_]*)/', "pgi.$2", $order);

        $txtPageSql = "SELECT {$cols} FROM (
                            SELECT pgi.*, ROW_NUMBER() OVER(ORDER BY {$orderNew}) RECNO
                            FROM (SELECT {$select} FROM {$from} {$where} {$union} ) pgi) pge
                        WHERE RECNO BETWEEN (#RECMIN$) AND (#RECMAX$)";
        $txtPageSql = str_replace("#RECMAX$", $da + $per, $txtPageSql);
        if ($da != 0) {
            $da = $da + 1;
        }
        $txtPageSql = str_replace("#RECMIN$", $da, $txtPageSql);

        return $txtPageSql;
    }

//rimpiazza i caratteri "$find" all'interno di una stringa racchiusa
//tra apici con i caratteri "$replace", 
//stringhe quotate presenti in una query
    private static function replace_quoted($sql, $find, $replace) {

        return str_replace("'$find'", "'$replace'", $sql);

//        if (strlen($sql) < 1 || (strlen($find) < 1) || (strlen($replace) < 1)) {
//            return $sql;
//        }
//        $buff = $sql;
//        $result = $sql;
//
//        $pos = strpos($buff, chr(39));
//        while ($pos > 0) {
//            $buff = substr($buff, $pos + 1);
//            $pos = strpos($buff, chr(39));
//            if ($pos > 0) {
//                $temp = "'" . substr($buff, 1, $pos);
//                $buff = substr($buff, $pos + 1);
//                $repl = str_replace($find, $replace, $temp);
//                $result = str_replace($temp, $repl, $result);
//                $pos = strpos($buff, chr(39));
//            }
//        }
//        return $result;
    }

    public function quote($val) {
        return $this->linkid->quote($val);
    }

    public function blank() {
        return "''";
    }

    public function coalesce($coalesceArray, $coalesceCount) {
        if ($coalesceCount != 0) {
            return "COALESCE(" . implode(" , ", $coalesceArray) . ")";
        } else {
            return "NULL";
        }
    }

    public function isBlank() {
        return " = ''";
    }

    public function isNotBlank() {
        return " <> ''";
    }

    public function round($value, $decimal = 0) {
        return "round($value, $decimal)";
    }

    public function nullIf($var1, $var2) {
        return "NULLIF($var1, $var2)";
    }

    public function testConn() {
        return $this->apriDB();
    }

    public function exists() {
        return $this->apriDB();
    }

    public function getLastId() {

        return $this->lastId;
    }

    public function setLastId($tableDef, $manualKeyValues) {
        if ($tableDef->hasAutoKey()) {
            $this->lastId = $tableDef->getSequenceName() ? $this->linkid->lastInsertId($tableDef->getSequenceName()) : $this->lastId = $this->linkid->lastInsertId();
        } else {
            $this->lastId = (count($manualKeyValues) === 1 ? $manualKeyValues[0]['VALUE'] : $manualKeyValues);
        }
    }

    /**
     * aggiunge un filtro all'array dei parametri da passare qlla query
     */
    public function addSqlParam(&$sqlParams, $name, $value, $tipo = PDO::PARAM_STR) {
        if (!isSet($sqlParams)) {
            $sqlParams = array();
        }
        if (!array_key_exists($name, $sqlParams)) {
            $sqlParams[] = array('name' => $name,
                'value' => $value,
                'type' => $tipo);
        } else {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "$name già utilizzato");
        }
    }

    protected function getSqlToken() {
        return array("INNER", "CROSS", "LEFT", "RIGHT", "GROUP", "ORDER", "OVER");
    }

//controlla se il token passato è una parlo chiave sql 
    protected function isSqlToken($token) {
        switch ($token) {
            case "INNER" :
                return true;
            case "CROSS" :
                return true;
            case "LEFT" :
                return true;
            case "RIGHT" :
                return true;
            case "GROUP" :
                return true;
            case "ORDER" :
                return true;
            default:
                return false;
        }
    }

    public function countTables() {
        return count($this->listTables());
    }

    protected function getCodiceErrore() {
        return $this->linkid->errorCode();
    }

    protected function getDescrizioneErrore() {
        $messages = $this->linkid->errorInfo();
        return $messages[1] . ' - ' . $messages[2];
    }

    protected function getEsitoOperazione() {
        return $this->getCodiceErrore() === '00000';
    }

    protected function isWindows() {
        return itaLib::isWindows();
    }

    protected function getConnectionKey() {
        return 'PDO_' . $this->connectionName;
    }

//Incremento di 1 il contatore delle transazioni e azzero commit e begin 
//arriva da update, insert o delete
    private function addBeginTransaction($manual = false) {
        $this->transactionInfo = ItaDB::$connectionPerRequest->getTransactionInfo($this->connectionName);
        $this->initTransactionInfo();
        $this->transactionInfo['transactions'] ++;
        $this->transactionInfo['commits'] = 0;
        $this->transactionInfo['rollback'] = 0;
        $this->transactionInfo['manual'] = $manual;
        $this->addCustomBeginTransaction();
        ItaDB::$connectionPerRequest->saveTransactionInfo($this->connectionName, $this->transactionInfo);
    }

//Incremento di 1 il contatore delle commit e decremento quelle delle transazioni
    private function addCommitTransaction($manual = false) {
        $this->transactionInfo = ItaDB::$connectionPerRequest->getTransactionInfo($this->connectionName);
        $this->initTransactionInfo();
        $this->transactionInfo['commits'] ++;
        $this->transactionInfo['transactions'] --;
        $this->transactionInfo['manual'] = $manual;
        $this->addCustomCommitTransaction();
        ItaDB::$connectionPerRequest->saveTransactionInfo($this->connectionName, $this->transactionInfo);
    }

//Incremento di 1 il contatore delle rollback e decremento quelle delle transazioni
    private function addRollbackTransaction($manual = false) {
        $this->transactionInfo = ItaDB::$connectionPerRequest->getTransactionInfo($this->connectionName);
        $this->initTransactionInfo();
        $this->transactionInfo['rollback'] ++;
        $this->transactionInfo['transactions'] --;
        $this->transactionInfo['manual'] = $manual;
        $this->addCustomRollbackTransaction();
        ItaDB::$connectionPerRequest->saveTransactionInfo($this->connectionName, $this->transactionInfo);
    }

// Controlla se è stata aperta una transazione
    private function isTransactionOpen() {
        $this->transactionInfo = ItaDB::$connectionPerRequest->getTransactionInfo($this->connectionName);
        return !empty($this->transactionInfo) && $this->transactionInfo['transactions'] === 1;
    }

// Recupera le informazioni della transazione dalla sessione utente
    private function initTransactionInfo() {
        if (!$this->transactionInfo) {
            $this->transactionInfo = array('transactions' => 0,
                'commits' => 0,
                'rollback' => 0,
                'manual' => false); // in fase di add della transazione se viene attivata in manuale evita di fare sempre commit sul service
            $this->initCustomTransactionInfo();
        }
    }

// --- Inizializza dei valori custom su transactionInfo --------------------   
    protected function initCustomTransactionInfo() {
        
    }

    protected function addCustomBeginTransaction() {
        
    }

    protected function addCustomCommitTransaction() {
        
    }

    protected function addCustomRollbackTransaction() {
        
    }

// -------------------------------------------------------------------------

    protected function gestioneErrore() {
        if ($this->getEsitoOperazione() === false) {
            if ($this->isTransactionOpen()) {
                $this->rollbackTransaction();
            }
            $codErrore = $this->getCodiceErrore();
            $desErrore = $this->getDescrizioneErrore();
            $erroreGenerico = $this->checkError($codErrore);
            throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, ($erroreGenerico === null ? $codErrore : $erroreGenerico), $desErrore);
        }
    }

    protected function checkError($codErrore) {
        return ItaDbError::getDBErrorMessage($codErrore);
    }

//Adatta il tipo dati in base al db specifio (usato solo per oracle per sistemare i vecchi campi long raw)
    protected function adapterValueForSpecificDb($field, &$value, $typeDbSpecific, $operation) {
        
    }

// Restituisce il flag che indica se gestire o meno le transazioni
//true= le begintransaction e committransaction agiscono; false=non agiscono
    protected function getAttivaTransazioni() {
        $attivatransazioni = Config::getConf('dbms.attivaTransazioni');
        return isset($this->sqlparams['enableTransactions']) ? $this->sqlparams['enableTransactions'] : (isset($attivatransazioni) ? Config::getConf('dbms.attivaTransazioni'): true);
    }

//Controllare se è aperta la connessione 
    private function safeOpenConnection() {
        if (!$this->linkid) {
// se non ho trovato la connessione impostata per request effettuo la login 
            if (!ItaDB::$connectionPerRequest->checkIfExistConnectioname($this->connectionName)) {
                $this->apriDB();
                $this->saveConnectionPerRequest();
            } else {
                $this->linkid = false;
                $connection = ItaDB::$connectionPerRequest->getConnection($this->connectionName);
                $this->linkid = $connection['linkId'];                
            }
        }

        // Effettua istruzioni personalizzate per il driver
        $this->safeOpenConnectionCustom();
    }

// ------------
// Getter/Setter    
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

// ------------
// Funzioni di gestione  

    abstract protected function initCustomDBAttributes();

    protected function safeOpenConnectionCustom() {
// Implementare nelle sottoclassi, al bisogno
// (Ad esempio, per MySQL serve per effettuare la USE dello schema)
    }

    abstract protected function createPDOObject($dbname, $host, $port, $user, $password);

    /**
     * Ritorna true nel caso esista la tabella passata come parametro
     */
    abstract public function tableExists($tableName);

    abstract public function getSqlCopyTable($tableFrom, $tableTo);

    /**
     * Effettua una select count
     * @param string $sql Comando sql
     * @param boolean $closeConnection Se true, chiude la connessione dopo aver eseguito il comando SQL
     * @param array $params parametri da passare alla where  
     * @return int Conteggio record in funzione dell'istruzione sql specificata
     * @throws ItaException
     */
    abstract public function queryCount($sql, $closeConnection = true, $params = array());

    abstract protected function addLimitOffsetToQuery(&$sql, $da, $per);

// Funzioni di definizione    
    abstract public function version();

    abstract public function listTables();

    abstract public function getTablesInfo();

//array di primaryKey
    abstract public function getPKs($tableName);

//informazioni aggiuntive tabella  pk calcolata e nome sequence
    abstract public function getTableInfo($tableName);

    abstract public function getColumnsInfo($tableName);

    abstract public function getDbmsBaseTypes();

//Blocco tabella lato db 
    abstract public function lockTableCommand($tableName, $exclusive = false);

    abstract public function lockRowTableCommand($sqlLockRowTable);

// Utility SQL
    abstract public function subString($value, $start, $len);

    abstract public function strConcat($strArray, $strCount);

    abstract public function dateDiff($beginDate, $endDate);

    abstract public function formatDate($value, $format = null);

    abstract public function dateToString($value, $format = null);

    abstract public function adapterBlob($value = null);

    abstract public function getFormatDateTime();

    abstract public function strLower($value);

    abstract public function strUpper($value);

    abstract public function strTrim($value);

    abstract public function strLtrim($value);

    abstract public function strRtrim($value);

    abstract public function strLpad($value, $len, $padstr = ' ');

    abstract public function strRpad($value, $len, $padstr = ' ');

    abstract public function strCast($value, $type);

    abstract public function nextval($sequence);

    abstract public function year($value);

    abstract public function month($value);

    abstract public function day($value);

    abstract public function module($field, $import);

    public function unifyDateTime($dateField, $timeField) {
        return $this->formatDate(
                        $this->strConcat(array(
                            $this->dateToString($dateField, 'YYYY/MM/DD'),
                            "' '",
                            $timeField
                                ), 2), 'YYYY-MM-DD HH24:MI:SS'
        );
    }

// JDBC
    abstract public function getJdbcDriverClass();

    abstract public function getJdbcConnectionUrl();

    abstract public function prepareColumnsName(&$data);
}
