<?php

/**
 *
 * Classe gestione form
 *
 *  * PHP Version 5
 *
 * @category   CORE
 * @package    itaPHPCore
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    11.02.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/itaPHPCore/itaProc.class.php');
require_once ITA_LIB_PATH . '/itaPHPCore/itaFrontController.class.php';

class itaModel {

    const ITAMODEL = 0;
    const ITAFRONTCONTROLLER = 1;

    protected $private = true;
    protected $perms;
    protected $close;
    protected $eqAudit;
    private $modelConfig;
    protected $nameForm;
    protected $nameFormOrig;
    protected $formData;
    protected $event;
    protected $elementId;
    protected $returnModel;
    protected $returnModelOrig;
    protected $returnEvent;
    protected $returnId;
    protected $fromModel;
    protected $lastInsertId;
    protected $procObj;
    protected $customRule;
    protected $readOnly;
    protected $externalRef;

    function __construct() {
        $this->externalRef = App::$utente->getKey($this->nameForm . '_externalRef');
        $this->perms = App::$utente->getKey($this->nameForm . '_perms');
        $this->formData = App::$utente->getKey($this->nameForm . '_formData');
        $this->modelConfig = App::$utente->getKey($this->nameForm . '_modelConfig');
        $this->returnModel = App::$utente->getKey($this->nameForm . "_returnModel");
        $this->returnModelOrig = App::$utente->getKey($this->nameForm . "_returnModelOrig");
        $this->returnEvent = App::$utente->getKey($this->nameForm . "_returnEvent");
        $this->returnId = App::$utente->getKey($this->nameForm . "_returnId");
        $this->procObj = unserialize(App::$utente->getKey($this->nameForm . '_procObj'));
        $this->eqAudit = new eqAudit();
        $this->readOnly = App::$utente->getKey($this->nameForm . '_readOnly');

        if (isset($_POST['event'])) {
            $this->event = $_POST['event'];
        }

//        if (isset($_POST['nameform']) && $_POST['nameform']) {
//            $this->origForm = $this->nameForm;
//            $this->nameForm = $_POST['nameform'];
//            unset($_POST['nameform']);
//        }
    }

    function __destruct() {
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_externalRef', $this->externalRef);
            App::$utente->setKey($this->nameForm . '_perms', $this->perms);
            App::$utente->setKey($this->nameForm . '_formData', $this->formData);
            App::$utente->setKey($this->nameForm . '_modelConfig', $this->modelConfig);
            App::$utente->setKey($this->nameForm . "_returnModel", $this->returnModel);
            App::$utente->setKey($this->nameForm . "_returnModelOrig", $this->returnModelOrig);
            App::$utente->setKey($this->nameForm . "_returnEvent", $this->returnEvent);
            App::$utente->setKey($this->nameForm . "_returnId", $this->returnId);
            App::$utente->setKey($this->nameForm . '_procObj', serialize($this->procObj));
            App::$utente->setKey($this->nameForm . '_readOnly', $this->readOnly);
        }
    }

    public static function getClazz() {
        return self::ITAMODEL;
    }

    public static function getInstance($model, $nameform = '') {

        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        $modelSrc = App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';

        if (file_exists($modelSrc)) {
            require_once $modelSrc;
        } else {
            return false;
        }

        if (call_user_func($model . '::getClazz') == constant($model . '::ITAFRONTCONTROLLER')) {
            return itaFrontController::getInstance($model, $nameform);
        }

        if ($nameform) {
            $_POST['nameform'] = $nameform;
        } else if ($_POST['nameform']) {
            $nameform = $_POST['nameform'];
            $_POST['nameform'] = '';
        }

        try {
            $instance = new $model();

            if ($nameform) {
                $instance->setNameForm($nameform);
                $instance->setNameFormOrig($model);
            }

            $instance->postInstance();

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

    protected function postInstance() {
        /*
         * Ricarico le proprietà interne nel caso in cui la form sia
         * richiamata con un alias tramite getInstance.
         */

        $this->externalRef = App::$utente->getKey($this->nameForm . '_externalRef');
        $this->perms = App::$utente->getKey($this->nameForm . '_perms');
        $this->formData = App::$utente->getKey($this->nameForm . '_formData');
        $this->modelConfig = App::$utente->getKey($this->nameForm . '_modelConfig');
        $this->returnModel = App::$utente->getKey($this->nameForm . "_returnModel");
        $this->returnModelOrig = App::$utente->getKey($this->nameForm . "_returnModelOrig");
        $this->returnEvent = App::$utente->getKey($this->nameForm . "_returnEvent");
        $this->returnId = App::$utente->getKey($this->nameForm . "_returnId");
        $this->procObj = unserialize(App::$utente->getKey($this->nameForm . '_procObj'));
        $this->readOnly = App::$utente->getKey($this->nameForm . '_readOnly');
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

    public function getLastInsertId() {
        return $this->lastInsertId;
    }

    public function setLastInsertId($lastInsertId) {
        $this->lastInsertId = $lastInsertId;
    }

    public function getPerms() {
        return $this->perms;
    }

    public function setPerms($perms) {
        $this->perms = $perms;
    }
    
    public function getReadOnly() {
        return $this->readOnly;
    }

    public function setReadOnly($readOnly) {
        $this->readOnly = $readOnly;
    }

    public function getNameForm() {
        return $this->nameForm;
    }

    public function setNameForm($nameForm) {
        $this->nameForm = $nameForm;
    }

    public function getNameFormOrig() {
        if(!$this->nameFormOrig){
            return $this->nameForm;
        }
        return $this->nameFormOrig;
    }

    public function setNameFormOrig($nameFormOrig) {
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

    public function getReturnModelOrig() {
        if(!$this->returnModelOrig){
            return $this->returnModel;
        }
        return $this->returnModelOrig;
    }

    public function setReturnModelOrig($returnModelOrig) {
        $this->returnModelOrig = $returnModelOrig;
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

    public function getPrivate() {
        return $this->private;
    }

    public function parseEvent() {
        switch ($_POST['event']) {
            case 'openform':
                $this->loadModelConfig();
                $this->eqAudit->logEqEvent($this, array('Operazione' => '01'));
//                $this->perms = $_POST['perms'];
                Out::checkDataButton($this->nameForm, $this->perms);

                if ($_POST['customRule']) {
                    $this->setCustomRule($_POST['customRule']);
                }

                require_once ITA_LIB_PATH . '/itaPHPCore/itaCustomRules.class.php';
                itaCustomRules::applyCustomRule($this->nameForm, $this->getCustomRule());
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

    protected function close() {
        $this->saveModelConfig();
        App::$utente->removeKey($this->nameForm . '_externalRef');
        App::$utente->removeKey($this->nameForm . '_formData');
        App::$utente->removeKey($this->nameForm . '_perms');
        App::$utente->removeKey($this->nameForm . '_modelConfig');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnModelOrig');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_returnId');
        App::$utente->removeKey($this->nameForm . '_procObj');
        App::$utente->removeKey($this->nameForm . '_readOnly');
        $this->close = true;
    }

    public function insertAudit($DB, $table, $audit_Info, $recordKey = '', $opCode = '99') {
        if (is_string($table)) {
            $Dset = $table;
        } else {
            $Dset = $table->getName();
        }
        try {
            $this->eqAudit->logEqEvent($this, array(
                'DB' => $DB->getDB(),
                'DSet' => $Dset,
                'Operazione' => $opCode,
                'Estremi' => $audit_Info,
                'Key' => $recordKey
            ));
            return true;
        } catch (Exception $e) {
            Out::msgStop("Errore in Apertura", $e->getMessage(), '600', '600');
        }
    }

    public function openRecord($DB, $table, $open_Info, $recordKey = '') {
        if (is_string($table)) {
            $Dset = $table;
        } else {
            $Dset = $table->getName();
        }
        try {
            $this->eqAudit->logEqEvent($this, array(
                'DB' => $DB->getDB(),
                'DSet' => $Dset,
                'Operazione' => '02',
                'Estremi' => $open_Info,
                'Key' => $recordKey
            ));
            return true;
        } catch (Exception $e) {
            Out::msgStop("Errore in Apertura", $e->getMessage(), '600', '600');
        }
    }

    public function insertRecord($DB, $table, $insert_rec, $insert_Info, $primaryKey = 'ROWID', $audit = true, $recordKey = '') {
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

    /**
     *  Aggiorna il log degli eventi con la modifica effettuata
     *
     * @param type $DB
     * @param String $table  Tabella del database modificata
     * @param Array $update_rec   Array contentente tutte le informazioni
     * @param String $update_Info   Stringa di log che si vuole inserire
     * @return type
     */
    public function updateRecord($DB, $table, $update_rec, $update_Info, $primaryKey = 'ROWID', $audit = true, $recordKey = '') {
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

    public function deleteRecord($DB, $table, $rowid, $delete_Info, $primaryKey = 'ROWID', $audit = true, $recordKey = '') {
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

    private function setGUIConfig($data) {
        $arr_path = explode("/", $path);
        $tmp = &$this->modelConfig['CONFIG_STRUCT']['GUI_CONFIG'];
        foreach ($arr_path as $key => $value) {
            $tmp = &$tmp[$value];
        }
        $tmp = $data;
    }

    private function getGUIConfig($path) {
        $arr_path = explode("/", $path);
        $tmp = &$this->modelConfig['CONFIG_STRUCT']['GUI_CONFIG'];
        foreach ($arr_path as $key => $value) {
            $tmp = &$tmp[$value];
        }
        return $tmp;
    }

    public function loadModelConfig() {
        $this->modelConfig = array();
        $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        $utente = App::$utente->getKey('idUtente');
        // non sono loggato come utente non devo fare il controllo come profilo
        if ($utente !== null) {
            $sqlString = "SELECT * FROM ENV_PROFILI WHERE UTECOD=$utente AND ELEMENTO='$this->nameForm'";
            try {
                $env_profili = ItaDB::DBSQLSelect($ITALWEB_DB, $sqlString, false);
                if ($env_profili) {
                    $this->modelConfig['CONFIG_STRUCT'] = unserialize($env_profili['CONFIG']);
                    $this->modelConfig['ROWID'] = $env_profili['ROWID'];
                    if ($this->modelConfig['CONFIG_STRUCT'] === false) {
                        $this->modelConfig['CONFIG_STRUCT'] = array();
                    }
                }
            } catch (Exception $exc) {
// do nothing
            }
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
                $Env_profili_rec['ELEMENTO'] = $this->nameForm;
                $Env_profili_rec['CONFIG'] = serialize($this->modelConfig['CONFIG_STRUCT']);
                ItaDB::DBInsert($ITALWEB_DB, "ENV_PROFILI", "ROWID", $Env_profili_rec);
            }
        } catch (Exception $e) {
            //
        }
    }

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

    public function getCustomRule() {
        return $this->customRule;
    }

    public function setCustomRule($customRule) {
        $this->customRule = $customRule;
    }

}
