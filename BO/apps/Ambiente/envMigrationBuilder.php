<?php

include_once ITA_BASE_PATH . '/updater/itaMigration.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';

function envMigrationBuilder() {
    $envUpdater = new envMigrationBuilder();
    $envUpdater->parseEvent();
    return;
}

class envMigrationBuilder extends itaModel {

    const OPERATION_ADD = 1;
    const OPERATION_EDIT = 2;

    public $nameForm = "envMigrationBuilder";
    private $gridSteps;
    private $gridStepsData = array();
    private $migrationHandler;
    private $ITALWEB;
    private $migrationStepData;

    function __construct() {
        parent::__construct();

        $this->gridSteps = $this->nameForm . '_gridSteps';
        $this->gridStepsData = App::$utente->getKey($this->nameForm . '_gridStepsData');

        $this->migrationHandler = itaMigration::newInstance();
        if (!$this->migrationHandler) {
            Out::msgStop('ERRORE', 'Errore creazione gestore migrations');
            $this->close();
            return;
        }
        try {
            $this->docLib = new docLib();
            $this->ITALWEB = $this->docLib->getITALWEB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_gridStepsData', $this->gridStepsData);
        }
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_gridStepsData');
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->init();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Crea':
                        $this->createMigration();
                        break;
                    case $this->nameForm . '_BUILD_TAG_butt':
                        $this->lookupTag();
                        break;
                    case $this->nameForm . '_CONTEXT_butt':
                        $this->lookupContext();
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onLookupTag':
                switch ($_POST['retid']) {
                    case $this->nameForm . '_BUILD_TAG_butt':
                        $buildTag = $_POST['rowData']['versione'];
                        Out::valore($this->nameForm . '_BUILD_TAG', $buildTag);
                        $this->calcSequence($buildTag);
                        break;
                }
                break;
            case 'onLookupContext':
                switch ($_POST['retid']) {
                    case $this->nameForm . '_CONTEXT_butt':
                        $this->updateContext($_POST['rowData']['ctx']);
                        break;
                }
                break;
            case 'onCreateNewStep':
                $this->addStepToMigration();
                break;
            case 'onUpdateStep':
                $this->updateMigrationStep();
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridSteps:
                        $this->addMigrationStep();
                        break;
                }
                break;
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridSteps:
                        $this->editMigrationStep($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridSteps':
                        $this->removeStepFromMigration($_POST['rowid']);
                        break;
                }
                break;
        }
    }

    private function init() {
        // All'apertura della form, svuota array passi
        $this->gridStepsData = array();

        // Controlla repository dei sorgenti
        $this->downloadSources();

        // Inizializza valori di default
        $this->initDefaultValues();
    }

    private function initDefaultValues() {
        Out::valore($this->nameForm . '_BUILD_TAG', $this->migrationHandler->getCurrentBuildTag());
        Out::valore($this->nameForm . '_AUTORE', App::$utente->getKey('nomeUtente'));
        $this->calcSequence();
        Out::setFocus($this->nameForm, $this->nameForm . '_DESCRIZIONE');
    }

    private function calcSequence($buildTag = null) {
        if (!$buildTag) {
            $buildTag = $this->migrationHandler->getCurrentBuildTag();
        }
        $sequence = $this->migrationHandler->getLastSequenceByTag($buildTag);
        Out::valore($this->nameForm . '_SEQUENZA', $sequence + 10);
    }

    private function downloadSources() {
        $result = $this->migrationHandler->downloadSources();
        if (!$result) {
            Out::msgStop('ERRORE', $this->migrationHandler->getErrorMessage());
            Out::closeDialog($this->nameForm);
        }
    }

    private function createMigration() {
        $migrationDefinitionData = array(
            'BUILD_TAG' => $this->formData[$this->nameForm . '_BUILD_TAG'],
            'AUTHOR' => $this->formData[$this->nameForm . '_AUTORE'],
            'DESCRIPTION' => $this->formData[$this->nameForm . '_DESCRIZIONE'],
            'CONTEXT' => $this->formData[$this->nameForm . '_CONTEXT'],
            'NOTES' => $this->formData[$this->nameForm . '_NOTE'],
            'SEQUENCE' => $this->formData[$this->nameForm . '_SEQUENZA'],
            'MULTITENANT' => $this->formData[$this->nameForm . '_MULTITENANT']
        );

        $result = $this->migrationHandler->createMigration($this->gridStepsData, $migrationDefinitionData);
        if (!$result) {
            Out::msgStop('ERRORE', $this->migrationHandler->getErrorMessage());
        } else {
            Out::msgInfo("INFO", $this->migrationHandler->getInfoMessage());

            // Aggiunge evento            
            $this->insertAudit($this->ITALWEB, '', $this->migrationHandler->getInfoMessage());

            // Per evitare di creare la stessa migration più volte, chiude la form            
            Out::closeDialog($this->nameForm);
        }
    }

    private function lookupTag() {
        // Prende gli ultimi 5 tag in ordine cronologico inverso
        $allTags = array_slice(array_reverse($this->migrationHandler->getAllTags()), 0, 5);
        if (!$allTags) {
            Out::msgStop("ERRORE", "Impossibile recuperare i tag!");
            return;
        }

        $tagsArray = array();
        foreach ($allTags as $tag) {
            $tagsArray[] = array("versione" => $tag);
        }

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco delle build',
            "width" => '350',
            "height" => '350',
            "rowNum" => '20',
            "rowList" => '[]',
            "arrayTable" => $tagsArray,
            "colNames" => array(
                "Versione"
            ),
            "colModel" => array(
                array("name" => 'versione', "width" => 350)
            ),
            "pgbuttons" => 'false',
            "pginput" => 'false',
            'navButtonEdit' => 'false'
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['returnModel'] = $this->nameForm;
        $_POST['returnEvent'] = 'onLookupTag';
        $_POST['retid'] = $this->nameForm . '_BUILD_TAG_butt';
        $_POST['gridOptions'] = $gridOptions;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        Out::setDialogTitle("utiRicDiag", "Build History");
        $model();
    }

    private function lookupContext() {
        $contextList = $this->migrationHandler->getContextList();
        $tagsArray = array();
        foreach ($contextList as $ctx) {
            $tagsArray[] = array("ctx" => $ctx);
        }

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Seleziona un ambito',
            "width" => '350',
            "height" => '350',
            "rowNum" => '999',
            "rowList" => '[]',
            "arrayTable" => $tagsArray,
            "colNames" => array(
                "Ambito"
            ),
            "colModel" => array(
                array("name" => 'ctx', "width" => 350)
            ),
            "pgbuttons" => 'false',
            "pginput" => 'false',
            'navButtonEdit' => 'false'
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['returnModel'] = $this->nameForm;
        $_POST['returnEvent'] = 'onLookupContext';
        $_POST['retid'] = $this->nameForm . '_CONTEXT_butt';
        $_POST['gridOptions'] = $gridOptions;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        Out::setDialogTitle("utiRicDiag", "Ambiti");
        $model();
    }

    private function updateContext($newContext) {
        $context = $this->formData[$this->nameForm . '_CONTEXT'];
        if ($context) {
            $context .= ';';
        }
        $context .= $newContext;
        Out::valore($this->nameForm . '_CONTEXT', $context);
    }

    private function openDialogMigrationStep($operation, $migrationStepData, $sequence, $returnEvent, $gridRowId = null) {
        $model = 'envMigrationStep';
        itaLib::openDialog('envMigrationStep');
        $objModel = itaModel::getInstance($model);
        $objModel->setEvent('openform');
        $objModel->setMigrationStepData($migrationStepData);
        $objModel->setLastSequence($sequence);
        $objModel->setReturnModel('envMigrationBuilder');
        $objModel->setReturnEvent($returnEvent);
        $objModel->setReturnID($_POST['id']);
        $objModel->setGridRowId($gridRowId);
        $objModel->setOperation($operation); // 1=Add  2=Edit
        $objModel->parseEvent();
    }

    private function addMigrationStep() {
        if (count($this->gridStepsData) == 1) {
            Out::msgInfo("Informazione", "La gestione multistep della migration non é stata ancora implementata");
        } else {
            $this->openDialogMigrationStep(self::OPERATION_ADD, null, $this->calcStepSequence(), 'onCreateNewStep');
        }
    }

    private function calcStepSequence() {
        $max = 0;
        foreach ($this->gridStepsData as $step) {
            if ($step['SEQUENCE'] > $max) {
                $max = $step['SEQUENCE'];
            }
        }
        return $max;
    }

    private function addStepToMigration() {
        $this->gridStepsData[] = $this->migrationStepData;

        // Ricarica grid        
        $this->reloadGridSteps();
    }

    private function editMigrationStep($gridRowId) {
        $oldMigrationStepData = $this->gridStepsData[$gridRowId];
        $this->openDialogMigrationStep(self::OPERATION_EDIT, $oldMigrationStepData, $oldMigrationStepData['SEQUENCE'], 'onUpdateStep', $gridRowId);
    }

    private function updateMigrationStep() {
        $this->gridStepsData[$_POST['gridRowId']] = $this->migrationStepData;

        // Ricarica grid        
        $this->reloadGridSteps();
    }

    private function removeStepFromMigration($gridRowId) {
        // Rimuove elemento da array
        unset($this->gridStepsData[$gridRowId]);

        // Ricarica grid        
        $this->reloadGridSteps();
    }

    private function reloadGridSteps() {
        $ita_grid01 = new TableView($this->gridSteps, array(
            'arrayTable' => $this->gridStepsData,
            'rowIndex' => 'idx'
        ));

        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10000);

        TableView::enableEvents($this->gridSteps);
        TableView::clearGrid($this->gridSteps);

        $ita_grid01->getDataPage('json');
    }

    public function getMigrationStepData() {
        return $this->migrationStepData;
    }

    public function setMigrationStepData($migrationStepData) {
        $this->migrationStepData = $migrationStepData;
    }

}
