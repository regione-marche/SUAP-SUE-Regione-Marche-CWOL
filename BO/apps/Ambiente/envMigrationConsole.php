<?php

include_once ITA_BASE_PATH . '/updater/itaMigration.class.php';


function envMigrationConsole() {
    $envUpdater = new envMigrationConsole();
    $envUpdater->parseEvent();
    return;
}

class envMigrationConsole extends itaModel {

    public $nameForm = "envMigrationConsole";
    private $migrationGrid;
    private $migrationData;
    private $migrationFiles;
    private $migrationHandler;
    private $selectedMigration;
    private $filterMigrationToApply;

    public function __construct() {
        parent::__construct();
        $this->migrationGrid = $this->nameForm . '_migration_grid';
        $this->migrationHandler = itaMigration::newInstance();
        $this->migrationData = App::$utente->getKey($this->nameForm . '_migrationData');
        $this->migrationFiles = App::$utente->getKey($this->nameForm . '_migrationFiles');
        $this->selectedMigration = App::$utente->getKey($this->nameForm . '_selectedMigration');
        $this->filterMigrationToApply = App::$utente->getKey($this->nameForm . '_filterMigrationToApply') ? 1 : 0;
        if (!$this->migrationHandler) {
            Out::msgStop('ERRORE', 'Errore creazione gestore migrations');
            $this->close();
            return;
        }
    }

    public function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_migrationData', $this->migrationData);
            App::$utente->setKey($this->nameForm . '_migrationFiles', $this->migrationFiles);
            App::$utente->setKey($this->nameForm . '_selectedMigration', $this->selectedMigration);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->init();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_VisualizzaDettaglioMigration':
                        $this->showMigrationDetail();
                        break;
                    case $this->nameForm . '_MarcaMigrationComeEseguita':
                        $this->markMigrationAsExecuted();
                        break;
                    case $this->nameForm . '_EseguiMigration':
                        $this->executeMigration();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_migrationFilterToapply':
                        $this->filterMigrationToApply = $_POST[$this->nameForm . '_migrationFilterToapply'];
                        $this->initMigrations();
                        break;
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->migrationGrid:
                        switch ($_POST['colName']) {
                            case 'CTXMENU':
                                $this->openContextMenuMigrationById($_POST['rowid']);
                                break;
                        }
                        break;
                }
                break;
        }
    }

    private function init() {
        $this->initMigrations();
    }

    private function initMigrations() {
        // Carica migrations
        $this->migrationFiles = $this->migrationHandler->loadMigrations($this->filterMigrationToApply);

        // Carica struttura dati per grid
        $this->migrationData = array();
        foreach ($this->migrationFiles as $migrationFile) {
            $data = json_decode(file_get_contents($migrationFile), true);
            $this->migrationData[] = array(
                'ID' => $data['MIGRATION_DEFINITION']['ID'],
                'AUTHOR' => $data['MIGRATION_DEFINITION']['AUTHOR'],
                'DESCRIPTION' => $data['MIGRATION_DEFINITION']['DESCRIPTION'],
                'CONTEXT' => $data['MIGRATION_DEFINITION']['CONTEXT'],
                'CTXMENU' => '<div align="center"><span style="display:inline-block;" class="ita-icon ita-icon-ingranaggio-24x24" title="Menu" funzioni=""></span></div>'
            );
        }

        // Prepara grid        
        TableView::clearGrid($this->migrationGrid);
        $ita_grid01 = new TableView($this->migrationGrid, array(
            'arrayTable' => $this->migrationData,
            'rowIndex' => 'idx'
        ));

        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10000);
        $ita_grid01->getDataPage('json');
        TableView::enableEvents($this->migrationGrid);
    }

    private function openContextMenuMigrationById($Id) {
        $this->selectedMigration = $this->getMigrationById($Id);
        if (!$this->selectedMigration) {
            App::$utente->setKey($this->nameForm . '_selectedMigration', false);
            return;
        }

        App::$utente->setKey($this->nameForm . '_selectedMigration', $this->selecteMigration);

        $arrayAzioni = array();
        $arrayAzioni['Visualizza dettaglio'] = array('id' => $this->nameForm . '_VisualizzaDettaglioMigration', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-view-tree-32x32'", 'model' => $this->nameForm);
        //Aggiungo il controllo per eseguire e marcare come eseguito tutte le migration da eseguire
        if ($this->selectedMigration["MIGRATION_DEFINITION"]["EXECUTED"] == false) {
            $arrayAzioni['Marca come eseguita'] = array('id' => $this->nameForm . '_MarcaMigrationComeEseguita', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-check-green-32x32'", 'model' => $this->nameForm);
            $arrayAzioni['Esegui'] = array('id' => $this->nameForm . '_EseguiMigration', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-arrow-green-dx-32x32'", 'model' => $this->nameForm);
        }
        Out::msgQuestion("Seleziona azione", "", $arrayAzioni, 'auto', 'auto', 'true', false, true, true);
    }

    private function getMigrationById($Id) {
        foreach ($this->migrationFiles as $migrationFile) {
            $data = json_decode(file_get_contents($migrationFile), true);
            if ($data['MIGRATION_DEFINITION']['ID'] === $Id) {
                //aggiungo attribuo executed per verificare se la migration è stata eseguita
                $data["MIGRATION_DEFINITION"]["EXECUTED"] = $this->migrationHandler->isExecutedMigration($data["MIGRATION_DEFINITION"]["ID"]);

                return $data;
            }
        }
        return false;
    }

    private function showMigrationDetail() {
        $model = 'envMigrationDetail';
        itaLib::openDialog($model);
        $objModel = itaModel::getInstance($model);
        $objModel->setEvent('openform');
        $objModel->setMigrationData($this->selectedMigration);
        $objModel->parseEvent();
    }

    private function markMigrationAsExecuted() {
        $ret = $this->migrationHandler->markMigrationAsExecuted($this->selectedMigration);
        $this->updateLog($ret['MSG']);

        // Ricarica grid migrations
        $this->initMigrations();
    }

    private function executeMigration() {
        $ret = $this->migrationHandler->executeMigration($this->selectedMigration);
        $this->updateLog($ret['MSG']);

        // Ricarica grid migrations
        $this->initMigrations();
    }

    private function updateLog($msg) {
        Out::html($this->nameForm . '_migrationInfoBox', $msg);
    }

}
