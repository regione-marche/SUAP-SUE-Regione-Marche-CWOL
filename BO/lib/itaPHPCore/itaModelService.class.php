<?php
require_once ITA_LIB_PATH . '/itaPHPCore/itaModelValidator.class.php';
require_once ITA_LIB_PATH . '/itaException/ItaException.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaModelServiceData.class.php';
//require_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BDI.class.php';
require_once ITA_BASE_PATH . '/lib/Cache/CacheFactory.class.php';

/**
 * Model Service
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class itaModelService {

    const OPERATION_INSERT = 1;
    const OPERATION_UPDATE = 2;
    const OPERATION_DELETE = 3;
    const OPERATION_OPENRECORD = 4;
    const BEHAVIOR_COLUMN_DESCRIPTIONS = "COLUMN_DESCRIPTIONS";
    const BEHAVIOR_RELATIONS = "RELATIONS";

    private $modelName;
    private $modelHelper;
    private $silent;
    private $eqAudit;
    private $lastInsertId;
    private $behaviors; //comportamento da passare alla "table"
    private $startedTransaction; // se true il service non effettua la commit\Rollback delle transazioni per la connessione corrente  
//    private $libDB_BDI;

    // se viene gestita in manuale la transazione (attributo quando si apra la begin) lo skip viene ignorato
    // e la chiusura della commit o rollbacjk va effettuata manaulmente utilizzando true su manual 

    public function __construct() {
        $this->behaviors = array();
//        $this->libDB_BDI = new cwbLibDB_BDI();
    }

    /**
     * Gestione del audit. Effettua l'inserimento del audit 
     * @param object $DB Oggetto Database
     * @param object $tableDef Oggetto table
     * @param array $audit_Info dati da salvare
     * @param type $recordKey 
     * @param type $opCode
     * @deprecated
     */
    public function insertAudit($DB, $tableDef, $audit_Info, $recordKey = '', $opCode = '99') {
        try {
            $this->insertLogEvent($this, $DB, $tableDef->getTableName(), $opCode, $recordInfo);
        } catch (ItaException $ex) {
            ItaDB::DBRollbackTransaction($DB, $this->getStartedTransaction());
            throw $ex;
        } catch (Exception $ex) {
            ItaDB::DBRollbackTransaction($DB, $this->getStartedTransaction());
            throw $ex;
        }
    }

    /**
     * 
     * @param object $DB Oggetto Database
     * @param $table TableName
     * @param type $recordInfo
     * @throws ItaException
     * @throws type
     */
    public function openRecord($DB, $table, $recordInfo) {
        $table = $this->newTableDef($table, $DB);
        try {
            $this->insertLogEvent($this, $DB, $table, 02, $recordInfo);
        } catch (ItaException $ex) {
            ItaDB::DBRollbackTransaction($DB, $this->getStartedTransaction());
            throw $ex;
        } catch (Exception $ex) {
            ItaDB::DBRollbackTransaction($DB, $this->getStartedTransaction());
            throw $ex;
        }
    }

    /**
     * Effettua validazione del record
     * @param object $DB Oggetto Database
     * @param string $tableName Nome tabella
     * @param array $data Dati da validare\gestire
     * @param int $operation Tipo operazione
     * @param array $oldData Vecchio record
     * @param array $keyMapping mappa chiave tabella esterna con tabella principale 
     * @return array Dati esito validazione
     */
    public function validate($DB, $tableName, $data, $operation, $oldData, $keyMapping = array(), $modifiedData = null, $inMemory=false) {
        return itaModelValidator::validate($DB, $this->getModelName(), $this->newTableDef($tableName, $DB), $this->silent, $data, $operation, $oldData, $keyMapping, $modifiedData, $inMemory);
    }

    /**
     * Inserimento record     
     * @param object $DB Oggetto Database
     * @param String $table  Tabella del database modificata
     * @param array $data Dati da inserire\aggiornare
     * @param String $recordInfo   Stringa di log che si vuole inserire
     */
    public function insertRecord($DB, $table, $data, $recordInfo) {
        $table = $this->newTableDef($table, $DB);
        try {
            ItaDB::DBBeginTransaction($DB);

            $toSave = $this->initData($data);

            $this->preOperation($DB, $toSave, self::OPERATION_INSERT);

            $this->calcSequence($DB, $table, $toSave);

            if (array_key_exists("AUDIT", $this->behaviors)) {
                $toSave = call_user_func($this->behaviors["AUDIT"]["function"], self::OPERATION_INSERT, $table, $toSave);
            }

            $this->normalizeData($data, $toSave);

            $nRows = ItaDB::DBInsert($DB, $table, '', $toSave);
            if ($nRows == -1) {
                $this->insertLogEvent($this, $DB, $table, '07', $recordInfo);
                throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, -1, "Inserimento su: " . $table->getName() . " non avvenuto: " . $e->getMessage());
            } else {
                $this->setLastInsertId(itaDB::DBLastId($DB));
                $this->insertLogEvent($this, $DB, $table, '04', $recordInfo);
            }
            $this->saveRelations($DB, $data, self::OPERATION_INSERT);
            $this->customOperation($DB, $data, self::OPERATION_INSERT);
            ItaDB::DBCommitTransaction($DB, $this->getStartedTransaction());
            if(!$this->getStartedTransaction()){
                $this->postCommit($DB, $data, self::OPERATION_INSERT);
            }
        } catch (Exception $ex) {
            if(!$this->getStartedTransaction()){
                $this->preRollback($DB, $data, self::OPERATION_INSERT, $ex);
            }
            
            ItaDB::DBRollbackTransaction($DB, $this->getStartedTransaction());
            
            if(!$this->getStartedTransaction()){
                $this->postRollback($DB, $data, self::OPERATION_INSERT, $ex);
            }
            throw $ex;
        }
    }

    /**
     * Aggiornamento record     
     * @param object $DB Oggetto Database
     * @param String $table Nome tabella del database modificata
     * @param array $data Dati da inserire\aggiornare\cancellare 
     * @param String $recordInfo   Stringa di log che si vuole inserire
     * @param array $oldCurrentRecord OldData caricati dall'esterno 
     */
    public function updateRecord($DB, $table, $data, $recordInfo, $oldCurrentRecord = null) {
        $table = $this->newTableDef($table, $DB);

        $toSave = $this->initData($data);
        $this->preOperation($DB, $toSave, self::OPERATION_UPDATE);

        if (!$oldCurrentRecord) {
            $oldCurrentRecord = $this->getByPks($DB, $table->getName(), $toSave);
        }

        if (array_key_exists("AUDIT", $this->behaviors)) {
            $toSave = call_user_func($this->behaviors["AUDIT"]["function"], self::OPERATION_UPDATE, $table, $toSave);
        }

        $this->normalizeData($data, $toSave);

        try {
            ItaDB::DBBeginTransaction($DB);

            $nRows = ItaDB::DBUpdate($DB, $table, '', $toSave, $oldCurrentRecord);
            if ($nRows == -1) {
                $this->insertLogEvent($this, $DB, $table, '09', $recordInfo);
                throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, -1, "Aggiornamento su: " . $table->getName() . " non avvenuto: " . $e->getMessage());
            } else {
                $this->saveRelations($DB, $data, self::OPERATION_UPDATE);
                $this->customOperation($DB, $data, self::OPERATION_UPDATE);
                ItaDB::DBCommitTransaction($DB, $this->getStartedTransaction());
                if(!$this->getStartedTransaction()){
                    $this->postCommit($DB, $data, self::OPERATION_UPDATE);
                }
                $this->insertLogEvent($this, $DB, $table, '06', $recordInfo);
            }
        } catch (Exception $ex) {
            if(!$this->getStartedTransaction()){
                $this->preRollback($DB, $data, self::OPERATION_UPDATE, $ex);
            }
            
            ItaDB::DBRollbackTransaction($DB, $this->getStartedTransaction());
            
            if(!$this->getStartedTransaction()){
                $this->postRollback($DB, $data, self::OPERATION_UPDATE, $ex);
            }
            throw $ex;
        }
    }

    /**
     * Aggiornamento record     
     * @param object $DB Oggetto Database
     * @param String $sqlString Stringa sql da eseguire
     * @param String $table Tabella principale coinvolta nella query (facoltativa)
     * @param String $recordInfo   Stringa di log che si vuole inserire
     * @param boolean $flMultipla True=Flag query multipla
     * @param array $params Array dei parametri da passare alla stringa sql
     * @param array $infoBinaryCallback Callback per popolamento campi binari (solo MS Sql Server)
     * @param array $fieldsBinary Array dei campi binari da popolare tramite la callback (solo MS Sql Server)
     * @param array $params Parametri da passare alla query
     */
    public function execute($DB, $sqlString, $table, $recordInfo, $flMultipla = true, $params = array(), $infoBinaryCallback = array(), $fieldsBinary = array()) {
        try {
            ItaDB::DBBeginTransaction($DB);
            $nRows = ItaDB::DBQuery($DB, $sqlString, $flMultipla, $params, $infoBinaryCallback, $fieldsBinary);
            if ($nRows == -1) {
                $this->insertLogEvent($this, $DB, $table, '13', $recordInfo);
                throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, -1, "Esecuzione comando sql fallita: " . $sqlString);
            } else {
                ItaDB::DBCommitTransaction($DB, $this->getStartedTransaction());
                $this->insertLogEvent($this, $DB, $table, '12', $recordInfo);
            }
        } catch (Exception $ex) {
            if(!$this->getStartedTransaction()){
                $this->preRollback($DB, $data, null, $ex);
            }
            
            ItaDB::DBRollbackTransaction($DB, $this->getStartedTransaction());
            
            if(!$this->getStartedTransaction()){
                $this->postRollback($DB, $data, null, $ex);
            }
            throw $ex;
        }
    }

    /**
     * Cancellazione record     
     * @param object $DB Oggetto Database
     * @param String $table Nome tabella del database
     * @param array $data Dati collegati da cancellare 
     * @param String $recordInfo Stringa di log che si vuole inserire
     * @return type
     */
    public function deleteRecord($DB, $table, $data, $recordInfo) {
        $table = $this->newTableDef($table, $DB);
        try {
            ItaDB::DBBeginTransaction($DB);

            $toDelete = $this->initData($data);

            $this->preOperation($DB, $toDelete, self::OPERATION_DELETE);

            //cancellazione tramite integrità referenziale letta dal db
            if (array_key_exists("DELETE_CASCADE", $this->behaviors)) {
                call_user_func($this->behaviors["DELETE_CASCADE"]["function"], $DB, $table, $data);
            }

            $this->normalizeData($data, $toDelete);

//            $lock = $this->lockRecord($DB, $table, $index, "", 20);
            $nRows = ItaDB::DBDelete($DB, $table, '', $toDelete);
            if ($nRows == -1 || $nRows == 0) {
                $this->insertLogEvent($this, $DB, $table, '08', $recordInfo);
                throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, -1, "Cancellazione su: " . $table->getName() . " non avvenuta.");
            } else {
                $this->deleteRelations($DB, $data, self::OPERATION_DELETE);
                $this->customOperation($DB, $data, self::OPERATION_DELETE);
                ItaDB::DBCommitTransaction($DB, $this->getStartedTransaction());
                if(!$this->getStartedTransaction()){
                    $this->postCommit($DB, $data, self::OPERATION_DELETE);
                }
                $this->insertLogEvent($this, $DB, $table, '05', $recordInfo);
            }
        } catch (Exception $ex) {
            if(!$this->getStartedTransaction()){
                $this->preRollback($DB, $data, self::OPERATION_DELETE, $ex);
            }
            
            ItaDB::DBRollbackTransaction($DB, $this->getStartedTransaction());
            
            
            if(!$this->getStartedTransaction()){
                $this->postRollback($DB, $data, self::OPERATION_DELETE, $ex);
            }
            throw $ex;
        }
    }

    /**
     * @param type itadb $db Oggetto db di riferimento
     * @param type string $table Tabella di riferimento
     * @param type mixed $Record id record di riferimento
     * @param type string $mode unused
     * @param type int $wait tempo di attesa se refereza già in lock (secondi)
     * @param type int $duration tempo di vita della lock prima che diventi una dead lock
     * @return type boolean 
     */
    public function lockRecord($DB, $table, $index, $mode = '', $wait = 0, $duration = 300) {
        return ItaDB::DBLock($DB, $table, $index, $mode, $wait, $duration);
    }

    /**
     * Sblocca un record
     * @param type $lockID identificativo restituito da lockRecord
     * @param type $DB Oggetto db di riferimento
     * @return type
     */
    public function unlockRecord($lockID, $DB) {
        return ItaDB::DBUnLock($lockID, $DB);
    }

    /**
     * Imposta la sequence su CURRENT_RECORD
     */
    public function calcSequence($DB, $tableDef, &$toSave) {
        $dbParams = $DB->getSqlparams();
        if (isset($dbParams['serial']) && strtolower($dbParams['serial']) === 'auto') {
            return;
        }
        if (strlen($tableDef->getSequenceName()) > 0) {
            $pks = $tableDef->getPKs();
            $toSave[$pks[0]] = ItaDB::DBNextval($DB, $tableDef->getSequenceName());
        }
    }

    /**
     * @param object $DB Oggetto Database
     * @param String $tableName Nome tabella del database
     * @return array con le chiavi primarie. wrapper del metodo su tableDef get Pk.
     */
    public function getPks($DB, $tableName) {
        return $this->newTableDef($tableName, $DB)->getPks();
    }
    
    /**
     * Restituisce la chiave primaria sotto forma di stringa
     * @param DBPdo $DB oggetto database
     * @param string $tableName nome tabella
     * @param array $record
     * @return string in forma k1|k2|..|kn
     */
    public function calcPkString($DB, $tableName, $record){
        return call_user_func($this->getModelHelper() . '::calcPkString', $DB, $tableName, $record);
    }

    /**
     * Ritorna la struttura dei campi del modello in ingresso 
     * @param object $DB Oggetto Database
     * @param string $tableName Nome tabella
     */
    public function define($DB, $tableName) {
        if (!$DB) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Prerequisiti non rispettati nel metodo define: Obbligatorio il passaggio del db");
        }
        if (!$tableName) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Prerequisiti non rispettati nel metodo define: Obbligatorio il passaggio del tableName");
        }
        $tableDef = $this->newTableDef($tableName, $DB);
        $modelBase = array();
        foreach ($tableDef->getFields() as $key => $value) {
            $modelBase[$key] = null;
        }
        return $modelBase;
    }

    /**
     * Effettua l'assegnazione valori in ingresso nel modello 
     * @param object $DB Oggetto Database
     * @param string $tableName Nome tabella 
     * @param array $dataSource dati da assegnare al modello 
     * @param array $model array di assegnazione null viene creato nuovo modello tramite la tabella
     * @param boolaen $byName assegnazione avviene per nome e non per posizione 
     */
    public function assignRow($DB, $tableName, $dataSource, &$model = null, $byName = true) {
        if (!$DB) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Prerequisiti non rispettati nel metodo assignRow: Obbligatorio il passaggio del db");
        }
        if (!$tableName) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Prerequisiti non rispettati nel metodo assignRow: Obbligatorio il passaggio del tableName");
        }
        if (is_array($dataSource) == false) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Prerequisiti non rispettati nel metodo assignRow: Obbligatorio il passaggio array da cui copiare i dati ");
        }

        // se non è definito l'array effettua la define per 
        if (is_array($model) == false) {
            $model = $this->define($DB, $tableName);
        }
        // se assegnazione tramite nome 
        if ($byName) {
            foreach ($dataSource as $key => $value) {
                if (array_key_exists($key, $model)) {
                    $model[$key] = $value;
                }
            }
        } else {
            //se assegnazione tramite posizione
            for ($index = 0; $i < count($dataSource); $index++) {
                $model[$index] = $dataSource[$index];
            }
        }
    }

    /**
     * 
     * @param object $DB Oggetto Database
     * @param array $model array in cui viene effettuata la clear
     */
//    public function clearRow($DB, &$model = null) {
//        if (!$model) {
//            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Prerequisiti non rispettati nel metodo clearRow: Obbligatorio il passaggio del model");
//        }
//        foreach ($model as $i => $value) {
//            unset($model[$i]);
//        }
//    }

    /**
     * Lettura tabella in funzione della primaryKey
     * @param object $DB Oggetto Database
     * @param string $tableName Nome tabella
     * @param array $values valori delle chiavi primarie
     */
    public function getByPks($DB, $tableName, $values, $multipla = false) {
        $tableDef = $this->newTableDef($tableName, $DB);
        $pks = $tableDef->getPks();
        if (!is_array($pks)) {
            $pks = array($pks);
        }
//        foreach ($pks as $pk) {
//            if (empty($values[$pk])) {
//                return false;
//            }
//        }

        // Seleziona tutti i campi, scartando i binary
        $sqlString = "SELECT ";
        $i = 0;
        $binField = null;
        foreach ($tableDef->getFields() as $key => $value) {
            if ($i++ > 0) {
                $sqlString .= ',';
            }
            $field = $tableName . '.' . $key;
            if ($value['phpType'] === 'binary') {
                $binField = $key;
            }
            $sqlString .= ($value['phpType'] !== 'binary') ? $field : $DB->adapterBlob($binField);
        }

        $where = PDOHelper::getSqlConditionsForUpdateOrDelete($DB, $tableDef, $values, $paramsFields);
        $sqlString .= " FROM $tableName WHERE " . $where;
        $infoBinaryCallback = array();
        if ($binField !== null) {
            $infoBinaryCallback['class'] = 'PDOHelper';
            $infoBinaryCallback['method'] = 'getByPksInfoBinaryCallback';

            $infoBinaryCallback['additionalInfo'] = array();
            $infoBinaryCallback['additionalInfo']['DB'] = $DB;
            $infoBinaryCallback['additionalInfo']['tableDef'] = $tableDef;
            $infoBinaryCallback['additionalInfo']['tableName'] = $tableName;
            $infoBinaryCallback['additionalInfo']['binField'] = $binField;
            $infoBinaryCallback['additionalInfo']['pkValues'] = $values;
        }
        return ItaDB::DBQuery($DB, $sqlString, $multipla, $paramsFields, $infoBinaryCallback);
    }

    /**
     * Metodo da overridere nel service specifico.Serve per gestire le tabelle aggiuntive. Attenzione alle transazioni del database
     * @param object $DB Oggetto Database
     * @param array $data Dati da validare\gestire
     * @param type $operationType operazione record principale 
     */
    protected function customOperation($DB, $data, $operationType) {
        
    }
    

    /**
     * Metodo da overridere nel service specifico.Serve per gestire le operazioni da far in seguito al commit.
     * @param object $DB Oggetto Database
     * @param array $data Dati da validare\gestire
     * @param type $operationType operazione record principale 
     */
    protected function postCommit($DB, $data, $operationType){
        
    }

    /**
     * Metodo da overridare nel service specifico.
     * Serve per manipolare il data e gestire operazioni particolari da fare prima della insert
     * 
     * @param object $DB Oggetto Database
     * @param array &$data Dati da validare\gestire 
     * @param type $operationType operazione record principale 
     */
    protected function preOperation($DB, &$data, $operationType) {
        
    }
    
    protected function preRollback($DB, $data, $operationType, $ex){
        
    }
    
    protected function postRollback($DB, $data, $operationType, $ex){
        
    }

    //aggiunge un il record sul db di Sistema dei Log (table Operaz)
    private function insertLogEvent(&$model, &$db, &$table, $operazione, $recordInfo) {
        if (is_string($table)) {
            $Dset = $table;
            $haslogEvent = true;
        } else {
            $Dset = $table->getName();
            $haslogEvent = $table->hasLogEvent();
        }
        if ($haslogEvent) {
            $this->eqAudit->logEqEvent($model, array(
                'DB' => $db->getDB(),
                'DSet' => $Dset,
                'Operazione' => $operazione,
                'Estremi' => $recordInfo,
            ));
        }
    }
    
    /**
     * Creazione nuovo oggetto TableDef
     * @param mixed $table TableDef/nome tabella
     * @param object $db Oggetto PDOManager
     * @return PDOTableDef
     */
    public function newTableDef($table, $db) {
        
        // Se è già un oggetto TableDef, lo restituisce senza far nulla
        if (!is_string($table)) {
            return $table;
        }
        
        $cache = CacheFactory::newCache();
        
        $cacheKey = 'ModelServiceTableDef_' . $db->getDB() . ':' . $db->getConnectionName() . '_' . $table;
        $tableDef = $cache->get($cacheKey);
        if(empty($tableDef)){
            // Ricava oggetto TableDef dal nome della tabella
            $tableDef = ItaDB::getTableDef($db, $table);

            // Aggiuge comportamenti alla tabledef
            if (count($this->behaviors) > 0) {
                foreach ($this->behaviors as $key => $behavior) {
                    if ($behavior != null) {
                        switch ($key) {
                            case self::BEHAVIOR_COLUMN_DESCRIPTIONS :                            
                                //Aggiunto controllo per evitare di portarsi nei test le descrizioni dei campi 
                                $columnDescriptions = call_user_func($behavior["function"], $table);
                                if ($columnDescriptions) {
                                    $tableDef->setColDescriptions($columnDescriptions);
                                }                                                     
                                break;
                            case self::BEHAVIOR_RELATIONS:                            
                                $relations = call_user_func($behavior["function"], $table);
                                if ($relations) {
                                    $tableDef->setRelations($relations);
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }

                $cache->set($cacheKey, $tableDef, (3600 * 24));       
            }
        }
        
        return $tableDef;
    }
    
    /**
     * Gestione delle relazioni aggiuntive 
     * @param object $DB Oggetto Database
     * @param array $data Dati da validare\gestire
     * @param type $operationType operazione record principale 
     */
    private function saveRelations($DB, $data, $operationType) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // è già stato salvato il record corrente quindi lo escludo dal salvataggio 
                if ($key !== "CURRENT_RECORD" && $key !== "CHILD") {
                    // attenzione se il campo del modello da salvare si chiama relationType ed ha valore 1/2/3 ci sarà un problema
                    if (key_exists("relationType", $value)) {
                        switch ($value["relationType"]) {
                            case itaModelServiceData::RELATION_TYPE_ONE_TO_ONE:
                                $this->manageRelationOneToOne($DB, $data["CURRENT_RECORD"], $value, $operationType);
                                break;
                            case itaModelServiceData::RELATION_TYPE_ONE_TO_MANY:
                                $this->manageRelationOneToMany($DB, $data["CURRENT_RECORD"], $value, $operationType);
                                break;
//                            case itaModelServiceData::RELATION_TYPE_MANY_TO_ONE:
//                                $this->manageRelationManyToOne($DB, $data["CURRENT_RECORD"], $value, $operationType);
//                                break;
                            default:
                                break;
                        }
                    }
                } elseif ($key === 'CHILD') {
                    $this->setStartedTransaction(true);
                    foreach ($data["CHILD"]["tableData"] as $child) {
                        $this->updateRecord($DB, $data["CHILD"]["tableName"], $child, null);
                    }
                    $this->setStartedTransaction(false);
                } else if (count($data) > 1) {
                    if($operationType == self::OPERATION_INSERT){
                        $tableDef = $this->newTableDef($data["CURRENT_RECORD"]["tableName"], $DB);
                        if ($tableDef->hasAutoKey()) {
                            $lastInsertId = $this->getLastInsertId();
                            
                            if(is_array($lastInsertId)){
                                foreach($lastInsertId as $value){
                                    $data["CURRENT_RECORD"]["tableData"][$value['KEY']] = $value['VALUE'];
                                }
                            }
                            else{
                                $pks = $tableDef->getPks();
                                $data["CURRENT_RECORD"]["tableData"][$pks[0]] = $lastInsertId;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Cancellazione delle relazioni aggiuntive 
     * @param object $DB Oggetto Database
     * @param array $data Dati da validare\gestire
     * @param type $operationType operazione record principale 
     */
    private function deleteRelations($DB, $data, $operationType) {
        foreach ($data as $key => $value) {
            if (!is_array($value)){
                break;
            }
            if ($key !== "CURRENT_RECORD") {
                switch ($value["relationType"]) {
                    case itaModelServiceData::RELATION_TYPE_ONE_TO_ONE:
                        $this->manageRelationOneToOne($DB, $data["CURRENT_RECORD"], $value, $operationType);
                        break;
                    case itaModelServiceData::RELATION_TYPE_ONE_TO_MANY:
                        $this->manageRelationOneToMany($DB, $data["CURRENT_RECORD"], $value, $operationType);
                        break;
//                    case itaModelServiceData::RELATION_TYPE_MANY_TO_ONE:
//                        $this->manageRelationManyToOne($DB, $data["CURRENT_RECORD"], $value, $operationType);
//                        break;

                    default:
                        break;
                }
            }
        }
    }

    //Effettua il salvataggio delle relezaione oneToOne. L'inserimento o update viene gestito tramite il controllo 
    //sulle primaryKey della tabella da aggiornare
    private function manageRelationOneToOne($DB, $mainRecord, $value, $operationType) {
        $this->manageRelationOneToMany($DB, $mainRecord, $value, $operationType);
    }
 
   //Effettua il salvataggio della relazione oneToMany 
    private function manageRelationOneToMany($DB, $mainRecord, $value, $operationType) {
        // Controlla se il service deve essere istanziato utilizzando una factory diversa
        if ($value["modelServiceFactory"]) {
            $modelService = call_user_func(array($value["modelServiceFactory"]['classname'], $value["modelServiceFactory"]['method']), $value["modelName"], $this->silent, true);
        } else {
            $modelService = itaModelServiceFactory::newModelService($value["modelName"], $this->silent, true);
        }

        // controllo se la chiave primaria è valorizzata in maniera automatica
        $tableDef = $this->newTableDef($value["tableName"], $DB);

        foreach ($value["tableData"] as $currentRecord) {
            switch ($currentRecord["operation"]) {
                case itaModelService::OPERATION_INSERT:
                    foreach ($value["keyMapping"] as $pk => $fpk) {
                        $currentRecord["data"][$fpk] = $mainRecord["tableData"][$pk];
                    }
                    $recordInfo = "Inserimento relazione tabella: " . $value["tableName"];
                    $modelService->insertRecord($DB, $tableDef, $currentRecord, $recordInfo);

                    break;
                case itaModelService::OPERATION_UPDATE;
                    //valorizzo nel current_record le chiavi esterne della tabella relazionata no in 
                    //delete perchè cancella per chiave primaria
                    foreach ($value["keyMapping"] as $pk => $fpk) {
                        $currentRecord["data"][$fpk] = $mainRecord["tableData"][$pk];
                    }
                    $recordInfo = "aggiornamento relazione tabella: " . $value["tableName"];
                    $pks = $tableDef->getPks();                    
                    $oldCurrentRecord = $this->getByPks($DB, $tableDef->getName(), $currentRecord["data"]);
                    if ($oldCurrentRecord) {
                        $modelService->updateRecord($DB, $tableDef, $currentRecord, $recordInfo, $oldCurrentRecord);
                    } else {
                        $modelService->insertRecord($DB, $tableDef, $currentRecord, $recordInfo);
                    }
                    break;
                case itaModelService::OPERATION_DELETE:
                    $recordInfo = "Cancellazione relazione tabella: " . $value["tableName"];
                    $modelService->deleteRecord($DB, $tableDef, $currentRecord, $recordInfo);
                    break;
            }
        }
    }

    //Effettua il salvataggio della relazione m-n tabella centrale chiave tabella A e chiave Tabella B
    private function manageRelationManyToOne($DB, $mainRecord, $value, $operationType) {
        $this->manageRelationOneToMany($DB, $mainRecord, $value, $operationType);
    }

    //Prende array corrente delle informazioni da inserire\aggiornare\cancellare 
    protected function initData($data) {
        if (array_key_exists("CURRENT_RECORD", $data)) {
            $toManage = $data['CURRENT_RECORD']['tableData'];
        } else if (!array_key_exists("data", $data)) {
            $toManage = $data;
        } else {
            $toManage = $data['data'];
        }
        return $toManage;
    }

    // in caso di override sui service specifici del metodo preOperation occorre alliniare i dati da inserire\aggiornare\cancellare 
    protected function normalizeData(&$data, $toSave) {
        if (array_key_exists("CURRENT_RECORD", $data)) {
            $data['CURRENT_RECORD']['tableData'] = $toSave;
        } else if (!array_key_exists("data", $data)) {
            $data = $toSave;
        } else {
            $data['data'] = $toSave;
        }
    }

    // Getter and Setter
    public function getModelHelper(){
        return $this->modelHelper;
    }
    
    public function setModelHelper($modelHelper){
        $this->modelHelper = $modelHelper;
    }
    
    public function getModelName() {
        return $this->modelName;
    }

    public function setModelName($modelName) {
        $this->modelName = $modelName;
    }

    public function getSilent() {
        return $this->silent;
    }

    public function setSilent($silent) {
        $this->silent = $silent;
    }

    public function getEqAudit() {
        return $this->eqAudit;
    }

    public function setEqAudit($eqAudit) {
        $this->eqAudit = $eqAudit;
    }

    public function getLastInsertId() {
        return $this->lastInsertId;
    }

    public function setLastInsertId($lastInsertId) {
        $this->lastInsertId = $lastInsertId;
    }

    public function getBehaviors() {
        return $this->behaviors;
    }

    public function setBehaviors($behaviors) {
        $this->behaviors = $behaviors;
    }

    public function getStartedTransaction() {
        return $this->startedTransaction;
    }

    public function setStartedTransaction($startTranasction) {
        $this->startedTransaction = $startTranasction;
    }

}