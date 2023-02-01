<?php

function envMigrationStep() {
    $envUpdater = new envMigrationStep();
    $envUpdater->parseEvent();
    return;
}

/**
 * Gestione di un passo di una migrations
 */
class envMigrationStep extends itaModel {

    const TYPE_METHOD = 'metodo';
    const TYPE_SCRIPT = 'script';

    public $nameForm = "envMigrationStep";
    public $returnModel;
    public $returnEvent;
    public $returnID;
    public $operation;
    public $type;
    public $lastSequence;
    public $gridRowId;
    private $migrationStepData;

    function __construct() {
        parent::__construct();
        $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
        $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
        $this->returnID = App::$utente->getKey($this->nameForm . '_returnID');
        $this->operation = App::$utente->getKey($this->nameForm . '_operation');
        $this->type = App::$utente->getKey($this->nameForm . '_type');
        $this->lastSequence = App::$utente->getKey($this->nameForm . '_lastseq');
        $this->gridRowId = App::$utente->getKey($this->nameForm . '_gridrowid');

        if (!$this->type) {
            $this->type = self::TYPE_METHOD;
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnEvent', $this->returnEvent);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnID', $this->returnID);
            App::$utente->setKey($this->nameForm . '_operation', $this->operation);
            App::$utente->setKey($this->nameForm . '_type', $this->type);
            App::$utente->setKey($this->nameForm . '_lastseq', $this->lastSequence);
            App::$utente->setKey($this->nameForm . '_gridrowid', $this->gridRowId);
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_returnEvent', $this->returnEvent);
        App::$utente->removeKey($this->nameForm . '_returnModel', $this->returnModel);
        App::$utente->removeKey($this->nameForm . '_returnID', $this->returnID);
        App::$utente->removeKey($this->nameForm . '_operation', $this->operation);
        App::$utente->removeKey($this->nameForm . '_type', $this->type);
        App::$utente->removeKey($this->nameForm . '_lastseq', $this->lastSequence);
        App::$utente->removeKey($this->nameForm . '_gridrowid', $this->gridRowId);
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->init();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_btnConferma':
                        $this->confirm();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TIPO':
                        $this->type = $this->formData[$this->nameForm . '_TIPO'];
                        $this->panelByType();
                        break;
                }
                break;
        }
    }

    private function init() {
        // Imposta combo        
        $this->type = self::TYPE_METHOD;
        Out::select($this->nameForm . '_TIPO', 1, self::TYPE_METHOD, 1, "Metodo");
        Out::select($this->nameForm . '_TIPO', 1, self::TYPE_SCRIPT, 0, "Script");

        // Imposta pannelli in funzione del tipo
        $this->panelByType();

        // Popola dati
        if (!$this->migrationStepData) {
            // Valori di default
            Out::valore($this->nameForm . '_SEQUENZA', $this->lastSequence + 10);
            Out::setFocus($this->nameForm, $this->nameForm . '_DESCRIZIONE');
            return;
        }
        Out::valore($this->nameForm . '_TIPO', $this->migrationStepData['TYPE']);
        Out::valore($this->nameForm . '_SEQUENZA', $this->migrationStepData['SEQUENCE']);
        Out::valore($this->nameForm . '_DESCRIZIONE', $this->migrationStepData['DESCRIPTION']);
        Out::valore($this->nameForm . '_MULTITENANT', $this->migrationStepData['MULTITENANT']);
        Out::valore($this->nameForm . '_METODO_EXECUTOR_UP', $this->migrationStepData['METHOD']['EXECUTOR_UP']);
        Out::valore($this->nameForm . '_METODO_EXECUTOR_DOWN', $this->migrationStepData['METHOD']['EXECUTOR_DOWN']);
        Out::valore($this->nameForm . '_SCRIPT_ALIAS_DB', $this->migrationStepData['SCRIPT']['ALIAS_DB']);
        Out::valore($this->nameForm . '_SCRIPT_SQL', $this->migrationStepData['SCRIPT']['SQL']);
    }

    private function panelByType() {
        switch ($this->type) {
            case self::TYPE_METHOD:
                Out::show($this->nameForm . '_divMethod');

                Out::hide($this->nameForm . '_divScript');
                //Imposto hide il campo down_executor
                Out::hide($this->nameForm . '_METODO_EXECUTOR_DOWN_lbl');
                Out::hide($this->nameForm . '_METODO_EXECUTOR_DOWN');
                break;
            case self::TYPE_SCRIPT:
                Out::show($this->nameForm . '_divScript');
                Out::hide($this->nameForm . '_divMethod');
                break;
        }
    }

    private function confirm() {
        // Precondizioni
        $errorMessage = '';
        if (!$this->formData[$this->nameForm . '_TIPO']) {
            $errorMessage = '- Tipo non valorizzato.';
        }
        if (!$this->formData[$this->nameForm . '_SEQUENZA']) {
            $errorMessage .= '- Sequenza non valorizzata.';
            if (strlen($errorMessage) > 0) {
                $errorMessage = "$errorMessage<br>";
            }
        }
        if (!$this->formData[$this->nameForm . '_DESCRIZIONE']) {
            $errorMessage .= '- Descrizione non valorizzata.';
            if (strlen($errorMessage) > 0) {
                $errorMessage = "$errorMessage<br>";
            }
        }

        // Precondizioni in funzione del tipo
        switch ($this->type) {
            case self::TYPE_METHOD:
                if (!$this->formData[$this->nameForm . '_METODO_EXECUTOR_UP']) {
                    $errorMessage .= '- Metodo UP non valorizzato.';
                    if (strlen($errorMessage) > 0) {
                        $errorMessage = "$errorMessage<br>";
                    }
                }
                if (!preg_match("/(\w+):(\w+):?(\w+)?/", $this->formData[$this->nameForm . '_METODO_EXECUTOR_UP'], $matches)) {
                    $errorMessage .= '- Metodo UP deve essere espresso nella forma: Classe:Metodo[:Parametri]';
                    if (strlen($errorMessage) > 0) {
                        $errorMessage = "$errorMessage<br>";
                    }
                }
                if (strlen($this->formData[$this->nameForm . '_METODO_EXECUTOR_DOWN'])) {
                    if (!preg_match("/(\w+):(\w+):?(\w+)?/", $this->formData[$this->nameForm . '_METODO_EXECUTOR_DOWN'], $matches)) {
                        $errorMessage .= '- Metodo DOWN deve essere espresso nella forma: Classe:Metodo[:Parametri]';
                        if (strlen($errorMessage) > 0) {
                            $errorMessage = "$errorMessage<br>";
                        }
                    }
                }
                break;
            case self::TYPE_SCRIPT:
                if (!$this->formData[$this->nameForm . '_SCRIPT_SQL']) {
                    $errorMessage .= '- Script non valorizzato.';
                    if (strlen($errorMessage) > 0) {
                        $errorMessage = "$errorMessage<br>";
                    }
                }
                break;
        }

        if (strlen($errorMessage) > 0) {
            $errorMessage = "Sono stati riscontrati i seguenti errori:<br>$errorMessage";
            Out::msgStop("Errore", $errorMessage);
            return;
        }

        // Prepara struttura dati
        $migrationStepData = array();
        $migrationStepData['TYPE'] = $this->formData[$this->nameForm . '_TIPO'];
        $migrationStepData['SEQUENCE'] = $this->formData[$this->nameForm . '_SEQUENZA'];
        $migrationStepData['DESCRIPTION'] = $this->formData[$this->nameForm . '_DESCRIZIONE'];
        $migrationStepData['MULTITENANT'] = $this->formData[$this->nameForm . '_MULTITENANT'];
        $migrationStepData['METHOD'] = array(
            'EXECUTOR_UP' => $this->formData[$this->nameForm . '_METODO_EXECUTOR_UP'],
            'EXECUTOR_DOWN' => $this->formData[$this->nameForm . '_METODO_EXECUTOR_DOWN'],
        );
        $migrationStepData['SCRIPT'] = array(
            'ALIAS_DB' => $this->formData[$this->nameForm . '_SCRIPT_ALIAS_DB'],
            'SQL' => $this->formData[$this->nameForm . '_SCRIPT_SQL'],
        );

        $returnObj = itaModel::getInstance($this->returnModel);
        $returnObj->setEvent($this->returnEvent);
        $returnObj->setElementID($this->returnID);
        $returnObj->setMigrationStepData($migrationStepData);
        $_POST['gridRowId'] = $this->gridRowId;
        $_POST['operation'] = $this->operation;
        $returnObj->parseEvent();
        Out::closeDialog($this->nameForm);
    }

    public function getMigrationStepData() {
        return $this->migrationStepData;
    }

    public function setMigrationStepData($migrationStepData) {
        $this->migrationStepData = $migrationStepData;
    }

    public function getReturnModel() {
        return $this->returnModel;
    }

    public function getReturnEvent() {
        return $this->returnEvent;
    }

    public function getReturnID() {
        return $this->returnID;
    }

    public function setReturnModel($returnModel) {
        $this->returnModel = $returnModel;
    }

    public function setReturnEvent($returnEvent) {
        $this->returnEvent = $returnEvent;
    }

    public function setReturnID($returnID) {
        $this->returnID = $returnID;
    }

    public function getOperation() {
        return $this->operation;
    }

    public function setOperation($operation) {
        $this->operation = $operation;
    }

    public function getLastSequence() {
        return $this->lastSequence;
    }

    public function setLastSequence($lastSequence) {
        $this->lastSequence = $lastSequence;
    }

    public function getGridRowId() {
        return $this->gridRowId;
    }

    public function setGridRowId($gridRowId) {
        $this->gridRowId = $gridRowId;
    }

}
