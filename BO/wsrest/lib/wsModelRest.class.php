<?php

require_once ITA_LIB_PATH . '/itaPHPCore/itaModelServiceFactory.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaModelServiceData.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaAuthenticator.class.php';

/**
 * Classe astratta per gestione modelRest
 */
abstract class wsModelRest {

    const SOURCE = "Web Service";

    private $modelData;
    private $modelService;
    private $DB;

    /**
     * Effetta la "describe" del modello (Non necessità di autorizzazione per la describe) 
     * @param String $model Model
     * @param boolean  $infoRelation riorna le informazione dei campi aggiuntivi 
     * @return Array Row tabella
     */
    public function define($model, $infoRelation = false) {
        
        // Ricava modelService
        $this->initModelService($model);

        // imposta DB
        $this->impostaDB($model);
        
        $result = $this->defineByModel($model, true);
        $this->postdefineByModel($result);
        return $this->preparaRisposta(1, "Esito positivo", array(
                    "RESULTS" => $result
        ));
    }

    public function load($model, $pkValues) {

        // controllo azione tramite authenticator
        if (!$this->getAuthenticator($model)->isActionAllowed(itaAuthenticator::ACTION_READ)) {
            return array(
                "ESITO" => -2,
                "MESSAGGIO" => "Utente non autorizzato all'operazione"
            );
        }

        $result = $this->loadByPkValues($model, $pkValues);
        $this->postLoad($result);
        return $this->preparaRisposta(1, "Esito positivo", array(
                    "RESULTS" => $result
        ));
    }

    abstract function getAuthenticator($model);

    /**
     * Operazioni da effettuare dopo il caricamento dei dati
     * @param array $result Dati caricati
     */
    public function postLoad(&$result) {
        
    }

    abstract function loadByPkValues($model, $pkValues);

    abstract function defineByModel($model, $infoRelation);

    /**
     * Operazioni da effettuare dopo la define del modello 
     * @param array $result Dati caricati
     */
    public function postdefineByModel(&$result) {
        
    }

    /**
     * Effetta il conteggio dei record di una tabella in funzione dei filtri in ingresso
     * @param String $model Model
     * @param Array $params Parametri di ricerca
     * @return Array Conteggio record
     */
    public function count($model, $params) {

        // controllo azione tramite authenticator
        if (!$this->getAuthenticator($model)->isActionAllowed(itaAuthenticator::ACTION_READ)) {
            return array(
                "ESITO" => -2,
                "MESSAGGIO" => "Utente non autorizzato all'operazione"
            );
        }

        $result = $this->countByParams($model, $params);
        return $this->preparaRisposta(1, "Esito positivo", $result);
    }

    abstract function countByParams($model, $params);

    /**
     * Effetta il caricamento dei record di una tabella in funzione dei filtri in ingresso
     * @param String $model Model
     * @param Array $params Parametri di ricerca
     * @return Array Lista dati
     */
    public function query($model, $params, $from, $to) {

        // controllo azione tramite authenticator
        if (!$this->getAuthenticator($model)->isActionAllowed(itaAuthenticator::ACTION_READ)) {
            return array(
                "ESITO" => -2,
                "MESSAGGIO" => "Utente non autorizzato all'operazione"
            );
        }

        $result = $this->queryByParams($model, $params, $from, $to);
        $this->postQuery($result);
        return $this->preparaRisposta(1, "Esito positivo", array(
                    "RESULTS" => $result
        ));
    }

    /**
     * Operazioni da effettuare dopo il caricamento dei dati
     * @param array $result Dati caricati
     */
    public function postQuery(&$result) {
        
    }

    abstract function queryByParams($model, $params, $from, $to);

    /**
     * Effetta inserimento
     * @param String $model Model
     * @param Array $data Dati da inserire in tabella
     * @return Array Dati inseriti
     */
    public function insert($model, $data) {

        // controllo azione tramite authenticator
        if (!$this->getAuthenticator($model)->isActionAllowed(itaAuthenticator::ACTION_WRITE)) {
            return array(
                "ESITO" => -2,
                "MESSAGGIO" => "Utente non autorizzato all'operazione"
            );
        }

        // Ricava modelService
        $this->initModelService($model);

        // effettua il caricamento del record principale
        $this->caricaRecordPrincipale($model, $data);

        // imposta DB
        $this->impostaDB($model);

        // effettua validazione
        if (!$this->valida(itaModelService::OPERATION_INSERT, $msg)) {
            return array(
                "ESITO" => -1,
                "MESSAGGIO" => "ERORE DI VALIDAZIONE: $msg"
            );
        }

        // imposta informazioni record        
        $modelData = $this->getModelData()->getData();
        $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_INSERT, self::SOURCE, $modelData['CURRENT_RECORD']['tableData']);

        // effettua inserimento su DB
        $this->modelService->insertRecord($this->getDB(), $modelData['CURRENT_RECORD']['tableName'], $modelData, $recordInfo);

        // ricarica il record                
        return $this->load($model, $this->getPkValues($modelData['CURRENT_RECORD']['tableName'], $this->modelService->getLastInsertId()));
    }

    /**
     * Restituisce i valori delle PK da utilizzare per ricaricare il record dopo l'inserimento/modifica
     * @param string $tableName Nome tabella
     * @param mixed $lastInsertId valore/i chiave
     * @return array Dati aggiornati
     */
    abstract function getPkValues($tableName, $lastInsertId);

    /**
     * Effetta aggiornamento
     * @param String $model Model
     * @param Array $data Dati da inserire in tabella
     * @return Array Esito/Dati
     */
    public function update($model, $data) {

        // controllo azione tramite authenticator
        if (!$this->getAuthenticator($model)->isActionAllowed(itaAuthenticator::ACTION_WRITE)) {
            return array(
                "ESITO" => -2,
                "MESSAGGIO" => "Utente non autorizzato all'operazione"
            );
        }

        // Ricava modelService
        $this->initModelService($model);

        // effettua il caricamento del record principale
        $this->caricaRecordPrincipale($model, $data);

        // imposta DB
        $this->impostaDB($model);

        // effettua validazione
        if (!$this->valida(itaModelService::OPERATION_UPDATE, $msg)) {
            return array(
                "ESITO" => -1,
                "MESSAGGIO" => "ERORE DI VALIDAZIONE: $msg"
            );
        }

        // imposta informazioni record      
        $modelData = $this->getModelData()->getData();
        $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_UPDATE, self::SOURCE, $modelData['CURRENT_RECORD']['tableData']);

        // effettua aggiornamento su DB
        $this->modelService->updateRecord($this->getDB(), $modelData['CURRENT_RECORD']['tableName'], $modelData, $recordInfo);

        // ricarica il record                
        $updated = $this->getModelService()->getByPks($this->getDB(), $modelData['CURRENT_RECORD']['tableName'], $modelData['CURRENT_RECORD']['tableData']);
        return $this->preparaRisposta(1, "Esito positivo", array(
                    "RESULTS" => $updated
        ));
    }

    /**
     * Effetta cancellazione
     * @param String $model Model
     * @param Array $data Dati da inserire in tabella
     * @return Array Esito/Dati
     */
    public function delete($model, $data) {

        // controllo azione tramite authenticator
        if (!$this->getAuthenticator($model)->isActionAllowed(itaAuthenticator::ACTION_DELETE)) {
            return array(
                "ESITO" => -2,
                "MESSAGGIO" => "Utente non autorizzato all'operazione"
            );
        }

        // Ricava modelService
        $this->initModelService($model);

        // effettua il caricamento del record principale
        $this->caricaRecordPrincipale($model, $data);

        // imposta DB
        $this->impostaDB($model);

        // effettua validazione
        if (!$this->valida(itaModelService::OPERATION_DELETE, $msg)) {
            return array(
                "ESITO" => -1,
                "MESSAGGIO" => "ERORE DI VALIDAZIONE: $msg"
            );
        }

        // imposta informazioni record    
        $modelData = $this->getModelData()->getData();
        $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_DELETE, self::SOURCE, $modelData['CURRENT_RECORD']['tableData']);

        // effettua cancellazione su DB
        $this->modelService->deleteRecord($this->getDB(), $modelData['CURRENT_RECORD']['tableName'], $modelData, $recordInfo);

        // ricarica il record                        
        return $this->preparaRisposta(1, "Esito positivo", array(
                    "RESULTS" => $modelData['CURRENT_RECORD']['tableData']
        ));
    }

    /**
     * Chiamata metodo Custom
     * @param String $method Nome metodo
     * @param Array $params Parametri del metodo
     * @return Array Esito/Dati
     */
    public function custom($method, $params) {

        // controllo azione tramite authenticator
        if (!$this->isActionAllowedCustom($method, $params)) {
            return array(
                "ESITO" => -2,
                "MESSAGGIO" => "Utente non autorizzato all'operazione"
            );
        }

        // Il metodo deve essere previsto nella sottoclasse, e deve restituire un valore di ritorno        
        $result = call_user_func_array(array($this, $method), $params);

        return $this->preparaRisposta(1, "Esito positivo", array(
                    "RESULTS" => $result
        ));
    }

    /**
     * Controlla se possibile eseguire l'azione specifica
     * @param String $method Nome metodo
     * @param Array $params Parametri del metodo
     * @return boolean True se l'utente è abilitato ad eseguire l'azione, altrimenti false
     */
    abstract function isActionAllowedCustom($method, $params);

    /**
     * Imposta oggetto ModelService per operazioni di inserimento/modifica/cancellazione
     * @param $model Nome modello
     */
    abstract function initModelService($model);

    /**
     * Imposta oggetto DB per accesso ai dati
     * @param $model Nome modello
     */
    abstract function impostaDB($model);

    /**
     * Effettua il caricamento del record principale nella struttura modelData
     * @param String $modelName Nome modello
     * @param Array $data Dati in ingresso dal servizio
     */
    abstract function caricaRecordPrincipale($modelName, $data);

    /**
     * Validazione record prima di effettuare operazioni su database
     */
    private function valida($tipoOperazione, &$msg) {
        $msg = '';

        $validationInfo = array();
        foreach ($this->getModelData()->getData() as $current) {
            $this->validaRecord($current['tableName'], $current['tableData'], $validationInfo, $msg, '', $tipoOperazione, $current['keyMapping']);
        }

        return count($validationInfo) === 0;
    }

    private function validaRecord($tableName, $data, &$validationInfo, &$msg, $line = 0, $tipoOperazione, $keyMapping = array()) {
        if (array_key_exists("data", $data)) {
            $toValidate = $data['data'];
        } else {
            $toValidate = $data;
        }

        if (is_array($toValidate[0])) {
            $riga = 1;
            foreach ($toValidate as $record) {
                $this->validaRecord($tableName, $record, $validationInfo, $msg, $riga++, $tipoOperazione, $keyMapping);
            }
        } else {
            $oldCurrentRecord = $this->getModelService()->getByPks($this->getDB(), $tableName, $toValidate);
            
            $validationInfo = $this->getModelService()->validate($this->getDB(), $tableName, $toValidate, $tipoOperazione, $oldCurrentRecord, $keyMapping);
            if (count($validationInfo) > 0) {
                foreach ($validationInfo as $currentInfo) {
                    if ($currentInfo['level'] === itaModelValidator::LEVEL_ERROR) {
                        $msg .= "Tabella: $tableName - ";
                        $msg .= ($line != 0 ? "Riga: $line - " : "");
                        $msg .= $currentInfo['msg'] . '<br/>';
                    }
                }
            }
        }
    }

    private function preparaRisposta($esito, $messaggio, $dati) {
        return array(
            "ESITO" => $esito,
            "MESSAGGIO" => $messaggio,
            "DATI" => $dati
        );
    }

    public function getModelData() {
        return $this->modelData;
    }

    public function getModelService() {
        return $this->modelService;
    }

    public function getDB() {
        return $this->DB;
    }

    public function setModelData($modelData) {
        $this->modelData = $modelData;
    }

    public function setModelService($modelService) {
        $this->modelService = $modelService;
    }

    public function setDB($DB) {
        $this->DB = $DB;
    }

}

?>
