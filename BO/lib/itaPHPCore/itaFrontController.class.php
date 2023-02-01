<?php

/**
 * Front Controller
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
require_once(ITA_LIB_PATH . '/itaPHPCore/itaProc.class.php');
require_once(ITA_LIB_PATH . '/itaPHPCore/itaModelServiceFactory.class.php');
require_once(ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php');
require_once(ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php');
require_once(ITA_LIB_PATH . '/itaPHPCore/itaModelHelper.class.php');

class itaFrontController {

    const ITAMODEL = 0;
    const ITAFRONTCONTROLLER = 1;

    protected $private = true;
    protected $perms;
    protected $close;
    protected $eqAudit;
    private $modelConfig;
    protected $nameForm;                     // nome grafico della form, può essere sostituito con un alias nel caso di finestre che richiamano se stesso (es btaVieOriginali)
    protected $nameFormOrig;                 // Nome form originale, da usare per accedere al modello (es btaVie)
    protected $formData;
    protected $event;
    protected $elementId;
    protected $returnModel;    // modello di rientro dai lookup
    protected $returnNameForm; // nameform di rientro dai lookup (in caso di alias è diverso da model)
    protected $returnEvent;
    protected $returnId;
    protected $fromModel;
    protected $procObj;
    protected $TABLE_NAME;                  // Nome tabella 
    protected $TABLE_VIEW;                  // Nome vista usata per la ricerca
    private $modelService;
    protected $helper;                      // Helper
    protected $MAIN_DB;                 // Connessione al database Cityware   
    protected $GRID_NAME;                   // Nome jqGrid
    protected $viewMode;                    // Modalità di visualizzazione (true = Visualizza - No Lock)
    protected $customRule;
    protected $externalRef;

    function __construct($nameFormOrig = null, $nameForm = null) {
        if (empty($nameFormOrig)) {
            $nameFormOrig = get_class($this);
        }
        if (empty($nameForm)) {
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }

        $this->nameForm = $nameForm;
        $this->nameFormOrig = $nameFormOrig;

        $this->externalRef = App::$utente->getKey($this->nameForm . '_externalRef');
        $this->viewMode = App::$utente->getKey($this->nameForm . '_viewMode');
        $this->perms = App::$utente->getKey($this->nameForm . '_perms');
        $this->formData = App::$utente->getKey($this->nameForm . '_formData');
        $this->modelConfig = App::$utente->getKey($this->nameForm . '_modelConfig');
        $this->returnModel = App::$utente->getKey($this->nameForm . "_returnModel");
        $this->returnNameForm = App::$utente->getKey($this->nameForm . "_returnNameForm");

        $this->returnEvent = App::$utente->getKey($this->nameForm . "_returnEvent");
        $this->returnId = App::$utente->getKey($this->nameForm . "_returnId");
        $this->procObj = unserialize(App::$utente->getKey($this->nameForm . '_procObj'));
        $this->eqAudit = new eqAudit();
//        $this->viewMode = false;
        
        // Inizializza variabili specifiche della pagina
        $this->TABLE_NAME = itaModelHelper::tableNameByModelName($this->nameFormOrig);
        $this->initBehaviours(substr($nameFormOrig, 0, 3));
        $this->initVars();

        $this->initModelService();

        if (isset($_POST['event'])) {
            $this->event = $_POST['event'];
        }

        $this->postItaFrontControllerCostruct();

        // Istanzia helper per gestione funzioni comuni
        $this->initHelper();
    }

    public function initHelper() {
        
    }

    function __destruct() {
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_externalRef', $this->externalRef);
            App::$utente->setKey($this->nameForm . '_viewMode', $this->viewMode);
            App::$utente->setKey($this->nameForm . '_perms', $this->perms);
            App::$utente->setKey($this->nameForm . '_formData', $this->formData);
            App::$utente->setKey($this->nameForm . '_modelConfig', $this->modelConfig);
            App::$utente->setKey($this->nameForm . "_returnModel", $this->returnModel);
            App::$utente->setKey($this->nameForm . "_returnEvent", $this->returnEvent);
            App::$utente->setKey($this->nameForm . "_returnId", $this->returnId);
            App::$utente->setKey($this->nameForm . '_procObj', serialize($this->procObj));
            App::$utente->setKey($this->nameForm . '_returnNameForm', $this->returnNameForm);
        }
    }

    public static function getClazz() {
        return self::ITAFRONTCONTROLLER;
    }

    public static function getInstance($model, $nameform = '') {
        if ($nameform) {
            $_POST['nameform'] = $nameform;
        } else {
            if ($_POST['nameform']) {
                $nameform = $_POST['nameform'];
            } else {
                $nameform = $model;
            }
        }

        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        $modelSrc = App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';

        if (file_exists($modelSrc)) {
            require_once $modelSrc;
        } else {
            return false;
        }

        try {
            if (call_user_func($model . '::getClazz') == constant($model . '::ITAMODEL')) {
                return itaModel::getInstance($model, $nameform);
            }

            $instance = new $model($model, $nameform);
            if ($instance->getPrivate() == true) {
                if (App::$utente->getStato() == Utente::AUTENTICATO ||
                        App::$utente->getStato() == Utente::AUTENTICATO_JNET ||
                        App::$utente->getStato() == Utente::AUTENTICATO_ADMIN) {
                    return $instance;
                } else {
                    return false;
                }
            } else {
                // App::log('public');
                return $instance;
            }
            // return new $model();
        } catch (Exception $exc) {
            return false;
        }
    }
    
    protected function initBehaviours($module){
        
    }
    
    protected function initVars(){
        
    }

    public function getExternalRef() {
        return $this->externalRef;
    }

    public function setExternalRef($externalRef) {
        $this->externalRef = $externalRef;
    }
    
    public function getExternalRefKey($key){
        return $this->externalRef[$key] ?: false;
    }
    
    public function setExternalRefKey($key, $data){
        if(!is_array($this->externalRef)){
            $this->externalRef = array();
        }
        $this->externalRef[$key] = $data;
    }

    public function getPerms() {
        return $this->perms;
    }

    public function setPerms($perms) {
        $this->perms = $perms;
    }

    public function getNameForm() {
        return $this->nameForm;
    }

    public function setNameForm($nameForm) {
        $this->nameForm = $nameForm;
    }

    function getNameFormOrig() {
        return $this->nameFormOrig;
    }

    function setNameFormOrig($nameFormOrig) {
        $this->nameFormOrig = $nameFormOrig;
    }

    public function getFormData() {
        return $this->formData;
    }

    public function setFormData($formData) {
        $this->formData = $formData;
    }

    public function getReturnModel() {
        return $this->returnModel;
    }

    public function getFromModel() {
        return $this->fromModel;
    }

    public function setFromModel($fromModel) {
        $this->fromModel = $fromModel;
    }

    /**
     * 
     * @param String $returnModel Model di ritorno
     */
    public function setReturnModel($returnModel) {
        $this->returnModel = $returnModel;
    }

    public function getReturnEvent() {
        return $this->returnEvent;
    }

    /**
     * 
     * @param String $returnEvent Evento da Elaborare
     */
    public function setReturnEvent($returnEvent) {
        $this->returnEvent = $returnEvent;
    }

    public function getEvent() {
        return $this->event;
    }

    public function setEvent($event) {
        $this->event = $event;
        $_POST['event'] = $event; // temporaneo per compatibilità
    }

    public function getElementId() {
        return $this->elementId;
    }

    public function setElementId($elementId) {
        $this->elementId = $elementId;
        $_POST['id'] = $elementId;
    }

    public function getReturnId() {
        return $this->returnId;
    }

    public function setReturnId($returnId) {
        $this->returnId = $returnId;
    }

    public function parseEvent() {
        switch ($_POST['event']) {
            case 'openform':
                $this->loadModelConfig();
                $this->eqAudit->logEqEvent($this, array('Operazione' => '01'));
//                $this->perms = $_POST['perms'];
                Out::checkDataButton($this->nameForm, $this->perms);

                break;
            case 'onClick':
                switch($_POST['id']){
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'openportlet':
                Out::delContainer($_POST['context'] . "-wait");
                break;
            case 'startProcess':
                $retCbFunc = $this->procObj->processStartApply();
                if ($retCbFunc) {
                    call_user_func(array($this, $retCbFunc));
                }
                break;
            case 'endProcess':
                break;

            case 'broadcastMsg':
                break;
            case 'refreshProcess':
//                if ($this->procObj->getRefreshCallBackFunc()) {
//                    call_user_func(array($this, $this->procObj->getRefreshCallBackFunc()));
//                } else {
                $this->processRefresh();
//                }
                break;
        }
    }

    protected function close($closeDialog = true) {
        $this->saveModelConfig();
        
        App::$utente->removeKey($this->nameForm . '_externalRef');
        App::$utente->removeKey($this->nameForm . '_viewMode');
        App::$utente->removeKey($this->nameForm . '_perms');
        App::$utente->removeKey($this->nameForm . '_formData');
        App::$utente->removeKey($this->nameForm . '_modelConfig');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_returnId');
        App::$utente->removeKey($this->nameForm . '_procObj');
        App::$utente->removeKey($this->nameForm . '_returnNameForm');
        
        if($closeDialog){
            Out::closeDialog($this->nameForm);
            cwbParGen::removeFormSessionVars($this->nameForm);
        }
        $this->close = true;
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    // ---------------
    // CRUD

    public function insertAudit($DB, $tableName, $data, $opCode = '99') {
        try {
            return $this->modelService->insertAudit($DB, $tableName, $data, $opCode);
        } catch (ItaException $e) {
            Out::msgStop("Errore in inserimento audit", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore in inserimento audit", $e->getMessage(), '600', '600');
        }
    }

    public function openRecord($DB, $tableName, $data) {
        try {
            return $this->modelService->openRecord($DB, $tableName, $data);
        } catch (ItaException $e) {
            Out::msgStop("Errore in apertura", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore in apertura", $e->getMessage(), '600', '600');
        }
    }

    /**
     * Inserimento record, da primaryKey in poi le variabili sono solo per gestione legacy
     */
    public function insertRecord($DB, $tableName, $data, $record_Info, $primaryKey = 'ROWID', $audit = true, $recordKey = '') {
        if ($DB instanceof PDOManager) {
            try {
                $this->modelService->insertRecord($DB, $tableName, $data, $record_Info);
                return true;
            } catch (ItaException $e) {
                Out::msgStop("Errore in inserimento", $e->getCompleteErrorMessage(), '600', '600');
                return false;
            } catch (Exception $e) {
                Out::msgStop("Errore in inserimento", $e->getMessage(), '600', '600');
                return false;
            }
        } else {
            require_once ITA_LIB_PATH . '/DB/ItaDB.class.php';
            $table = $tableName;
            $insert_rec = $data;
            $insert_Info = $record_Info;

            if (is_string($table)) {
                $Dset = $table;
            } else {
                $Dset = $table->getName();
            }
            try {
                $nRows = ItaDB::DBInsert($DB, $table, $primaryKey, $insert_rec);
                if ($nRows == -1) {
                    Out::msgStop("Inserimento", "Inserimento su: " . $Dset . " non avvenuto.");
                    if ($audit === true) {
                        $this->eqAudit->logEqEvent($this, array(
                            'DB' => $DB->getDB(),
                            'DSet' => $Dset,
                            'Operazione' => '07',
                            'Estremi' => $insert_Info,
                            'Key' => $recordKey
                        ));
                    }
                    return false;
                } else {
                    $this->setLastInsertId(itaDB::DBLastId($DB));
                    if ($audit === true) {
                        $this->eqAudit->logEqEvent($this, array(
                            'DB' => $DB->getDB(),
                            'DSet' => $Dset,
                            'Operazione' => '04',
                            'Estremi' => $insert_Info,
                            'Key' => $recordKey
                        ));
                    }
                    return true;
                }
            } catch (Exception $e) {
                Out::msgStop("Errore in Inserimento", $e->getMessage(), '600', '600');
                return false;
            }
        }
    }

    /**
     * Aggiornamento record, da primaryKey in poi le variabili sono solo per gestione legacy
     */
    public function updateRecord($DB, $tableName, $data, $record_Info, $primaryKey = 'ROWID', $audit = true, $recordKey = '', $oldRecord = null) {
        if ($DB instanceof PDOManager) {
            try {
                $this->modelService->updateRecord($DB, $tableName, $data, $record_Info, $oldRecord);
                return true;
            } catch (ItaException $e) {
                Out::msgStop("Errore in aggiornamento", $e->getCompleteErrorMessage(), '600', '600');
                return false;
            } catch (Exception $e) {
                Out::msgStop("Errore in aggiornamento", $e->getMessage(), '600', '600');
                return false;
            }
        } else {
            require_once ITA_LIB_PATH . '/DB/ItaDB.class.php';
            $table = $tableName;
            $update_rec = $data;
            $update_Info = $record_Info;

            if (is_string($table)) {
                $Dset = $table;
            } else {
                $Dset = $table->getName();
            }
            try {
                $nRows = ItaDB::DBUpdate($DB, $table, $primaryKey, $update_rec);
                if ($nRows == -1) {
                    Out::msgStop("Aggiornamento", "Aggiornamento su: " . $Dset . " non avvenuto.");
                    if ($audit === true) {
                        $this->eqAudit->logEqEvent($this, array(
                            'DB' => $DB->getDB(),
                            'DSet' => $Dset,
                            'Operazione' => '09',
                            'Estremi' => $update_Info,
                            'Key' => $recordKey
                        ));
                    }
                    return false;
                } else {
                    if ($audit === true) {
                        $this->eqAudit->logEqEvent($this, array(
                            'DB' => $DB->getDB(),
                            'DSet' => $Dset,
                            'Operazione' => '06',
                            'Estremi' => $update_Info,
                            'Key' => $recordKey
                        ));
                    }
                    return true;
                }
            } catch (Exception $e) {
                Out::msgStop("Errore in Aggiornamento", $e->getMessage(), '600', '600');
                return false;
            }
        }
    }

    /**
     * Cancellazione record, da primaryKey in poi le variabili sono solo per gestione legacy
     */
    public function deleteRecord($DB, $tableName, $dataPk, $record_Info, $primaryKey = 'ROWID', $audit = true, $recordKey = '') {
        if ($DB instanceof PDOManager) {
            try {
                $this->modelService->deleteRecord($DB, $tableName, $dataPk, $record_Info);
                return true;
            } catch (ItaException $e) {
                Out::msgStop("Errore in cancellazione", $e->getCompleteErrorMessage(), '600', '600');
                return false;
            } catch (Exception $e) {
                Out::msgStop("Errore in cancellazione", $e->getMessage(), '600', '600');
                return false;
            }
        } else {
            require_once ITA_LIB_PATH . '/DB/ItaDB.class.php';
            $table = $tableName;
            $rowid = $dataPk;
            $delete_Info = $record_Info;

            if (is_string($table)) {
                $Dset = $table;
            } else {
                $Dset = $table->getName();
            }
            try {
                $nRows = ItaDB::DBDelete($DB, $table, $primaryKey, $rowid);
                if ($nRows == -1 || $nRows == 0) {
                    Out::msgStop("Cancellazione", "Cancellazione su: " . $Dset . " non avvenuto.");
                    if ($audit === true) {
                        $this->eqAudit->logEqEvent($this, array(
                            'DB' => $DB->getDB(),
                            'DSet' => $Dset,
                            'Operazione' => '08',
                            'Estremi' => $delete_Info,
                            'Key' => $recordKey
                        ));
                    }
                    return false;
                } else {
                    if ($audit === true) {
                        $this->eqAudit->logEqEvent($this, array(
                            'DB' => $DB->getDB(),
                            'DSet' => $Dset,
                            'Operazione' => '05',
                            'Estremi' => $delete_Info,
                            'Key' => $recordKey
                        ));
                    }
                    return true;
                }
            } catch (Exception $e) {
                Out::msgStop("Errore in Cancellazione Record", $e->getMessage());
                return false;
            }
        }
    }

    public function getLastInsertId() {
        return $this->modelService->getLastInsertId();
    }

    public function setLastInsertId($lastInsertId) {
        $this->modelService->setLastInsertId($lastInsertId);
    }

    protected function initModelService() {
        $this->modelService = itaModelServiceFactory::newModelService(itaModelHelper::modelNameByTableName($this->TABLE_NAME, $this->nameFormOrig));
    }

    // ---------------

    public function setModelData($data) {
        if ($_POST['event'] != 'broadcastMsg') {
            $this->formData = $data;
        }
    }

    public function setCustomConfig($path, $data) {
        $arr_path = explode("/", $path);
        $tmp = &$this->modelConfig['CONFIG_STRUCT']['CUSTOM_CONFIG'];
        foreach ($arr_path as $key => $value) {
            $tmp = &$tmp[$value];
        }
        $tmp = $data;
    }

    public function getCustomConfig($path) {
        $arr_path = explode("/", $path);
        $tmp = &$this->modelConfig['CONFIG_STRUCT']['CUSTOM_CONFIG'];
        foreach ($arr_path as $key => $value) {
            $tmp = &$tmp[$value];
        }
        return $tmp;
    }

    public function loadModelConfig() {
        try {
            $this->modelConfig = array();
            $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            $utente = App::$utente->getKey('idUtente');
            $sqlString = "SELECT * FROM ENV_PROFILI WHERE UTECOD=$utente AND ELEMENTO='$this->nameFormOrig'";
            $env_profili = ItaDB::DBSQLSelect($ITALWEB_DB, $sqlString, false);
            if ($env_profili) {
                $this->modelConfig['CONFIG_STRUCT'] = unserialize($env_profili['CONFIG']);
                $this->modelConfig['ROWID'] = $env_profili['ROWID'];
                if ($this->modelConfig['CONFIG_STRUCT'] === false) {
                    $this->modelConfig['CONFIG_STRUCT'] = array();
                }
            }
        } catch (ItaException $e) {
            Out::msgStop("Errore caricamento configurazione", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore caricamento configurazione", $e->getMessage(), '600', '600');
        }
    }

    public function saveModelConfig() {
        $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        try {
            if (isset($this->modelConfig['ROWID'])) {
                // UPDATE
                $Env_profili_rec = array();
                $Env_profili_rec['ROWID'] = $this->modelConfig['ROWID'];
                $Env_profili_rec['CONFIG'] = serialize($this->modelConfig['CONFIG_STRUCT']);
                ItaDB::DBUpdate($ITALWEB_DB, "ENV_PROFILI", "ROWID", $Env_profili_rec);
            } else {
                $Env_profili_rec = array();
                $Env_profili_rec['UTECOD'] = App::$utente->getKey('idUtente');
                $Env_profili_rec['ELEMENTO'] = $this->nameFormOrig;
                $Env_profili_rec['CONFIG'] = serialize($this->modelConfig['CONFIG_STRUCT']);
                ItaDB::DBInsert($ITALWEB_DB, "ENV_PROFILI", "ROWID", $Env_profili_rec);
            }
        } catch (ItaException $e) {
            Out::msgStop("Errore salvataggio configurazione", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore salvataggio configurazione", $e->getMessage(), '600', '600');
        }
    }

    // ATTENZIONE la progress bar non funziona correttamente se c'è il parametro dell'xdebug 
    // (?XDEBUG_SESSION_START=netbeans-xdebug) anche se non ci sono break attivi
    public function processInit($callBackFunc = '', $max = 100, $delay = 1, $firstval = 0, $createLabel = '', $completeLabel = '') {
        try {
            $this->procObj = new itaProc();
        } catch (Exception $exc) {
            return false;
        }
        $this->procObj->setCallBackFunc($callBackFunc);
        $this->procObj->setProgressMax($max);
        $this->procObj->setRefreshDelay($delay);
        $this->procObj->setModel($this->nameForm);
        $this->procObj->setCreateLabel($createLabel);
        $this->procObj->setCompleteLabel($completeLabel);
        $this->procObj->setProgressVal($firstval);
        $this->procObj->setProcessToDB();
        itaLib::getAppsTempPath($subpath);

        return $this->procObj;
    }

    /*
     * sovrascritto da genmodel, se su estende genmodel non sovrascrivere
     */

    protected function postItaFrontControllerCostruct() {
        
    }

    public function processMax($max) {
        $this->procObj->setProgressMax($max);
        $this->procObj->setProcessToDB();
    }

    public function processStart($titolo, $height = 'auto', $width = 'auto', $closeButton = true, $header = '', $trailer = '') {
        $this->procObj->processStart($titolo, $height, $width, $closeButton, $header, $trailer);
    }

    public function processProgress($index, $label = '') {
        if ($label) {
            $this->procObj->setProgressLabel($label);
        }
        $this->procObj->setProgressVal($index);
        $this->procObj->setProcessToDB();
    }

    public function setRefreshSrc($src = 'auto') {
        $this->procObj->setRefreshSrc($src);
        $this->procObj->setProcessToDB();
    }

    public function processRefresh() {
        if (is_object($this->procObj)) {
            $this->procObj->refresh();
        }
    }

    public function getProcessRefreshExternalPath() {
        return $this->procObj->getRefreshExternalPath();
    }

    public function processEnd() {
        $this->procObj->processEnd();
    }

    public function getPrivate() {
        return $this->private;
    }

    public function getModelService() {
        return $this->modelService;
    }

    public function setModelService($modelService) {
        $this->modelService = $modelService;
    }

    public function getTABLE_NAME() {
        return $this->TABLE_NAME;
    }

    public function setTABLE_NAME($TABLE_NAME) {
        $this->TABLE_NAME = $TABLE_NAME;
    }

    public function getTABLE_VIEW() {
        return $this->TABLE_VIEW;
    }

    public function setTABLE_VIEW($TABLE_VIEW) {
        $this->TABLE_VIEW = $TABLE_VIEW;
    }

    function getReturnNameForm() {
        return $this->returnNameForm;
    }

    function setReturnNameForm($returnNameForm) {
        $this->returnNameForm = $returnNameForm;
    }

    public function getCustomRule() {
        return $this->customRule;
    }

    public function setCustomRule($customRule) {
        $this->customRule = $customRule;
    }

    public function setViewMode($viewMode) {
        $this->viewMode = $viewMode;
    }

}
