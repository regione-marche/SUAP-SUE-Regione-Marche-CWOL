<?php

/**
 *
 * GESTIONE Informazioni per il Front Office
 *
 * PHP Version 5
 *
 * @category
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    08.04.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docRic.class.php';

function praInfoFO() {
    $praInfoFO = new praInfoFO();
    $praInfoFO->parseEvent();
    return;
}

class praInfoFO extends itaModel {

    public $PRAM_DB;
    public $nameForm = "praInfoFO";
    public $divDettaglio = "praGestDest_divDettaglio";
    public $praLib;
    public $returnModel;
    public $returnEvent;
    public $info;
    public $mode;
    public $rowidInfo;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
            $this->mode = App::$utente->getKey($this->nameForm . '_mode');
            $this->info = App::$utente->getKey($this->nameForm . '_info');
            $this->rowidInfo = App::$utente->getKey($this->nameForm . '_rowidInfo');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnEvent', $this->returnEvent);
            App::$utente->setKey($this->nameForm . '_mode', $this->mode);
            App::$utente->setKey($this->nameForm . '_info', $this->info);
            App::$utente->setKey($this->nameForm . '_rowidInfo', $this->rowidInfo);
        }
    }

    public function setMode($mode) {
        $this->mode = $mode;
    }

    public function setInfo($info) {
        $this->info = $info;
    }

    public function setRowidInfo($rowidInfo) {
        $this->rowidInfo = $rowidInfo;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::show($this->nameForm);
                $this->rowidAlle = $_POST['rowidAlle'];
                Out::codice('tinyActivate("' . $this->nameForm . '_CONTENUTO");');
                switch ($this->mode) {
                    case "new":
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        break;
                    case "edit":
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiorna');
                        Out::valore($this->nameForm . "_CODICE", $this->info['CODICE']);
                        Out::valore($this->nameForm . "_DESCRIZIONE", $this->info['DESCRIZIONE']);
                        Out::valore($this->nameForm . "_CONTENUTO", $this->info['CONTENUTO']);
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiungi':
                        $this->info['CODICE'] = $_POST[$this->nameForm . '_CODICE'];
                        $this->info['DESCRIZIONE'] = $_POST[$this->nameForm . '_DESCRIZIONE'];
                        $this->info['CONTENUTO'] = $_POST[$this->nameForm . '_CONTENUTO'];
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_Aggiorna':
                        if ($this->destinatario['ROWID'] == 0) {
                            $nome = "<span style = \"color:orange;\">" . $_POST[$this->nameForm . '_PRAMITDEST']['NOME'] . "</span>";
                        }
                        $this->info['CODICE'] = $_POST[$this->nameForm . '_CODICE'];
                        $this->info['DESCRIZIONE'] = $_POST[$this->nameForm . '_DESCRIZIONE'];
                        $this->info['CONTENUTO'] = $_POST[$this->nameForm . '_CONTENUTO'];
                        $this->returnToParent();
                        break;
                }
                break;

            case 'embedVars':
                include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
                include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
                $praLibVar = new praLibVariabili();
                $dictionaryLegend = $praLibVar->getLegendaProcedimentoFO('adjacency', 'smarty');
                docRic::ricVariabili($dictionaryLegend, $this->nameForm, "returnContenuto", true);
                break;

            case 'returnContenuto':
                Out::codice('tinyInsertContent("' . $this->nameForm . '_CONTENUTO","' . $_POST["rowData"]['markupkey'] . '");');
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_mode');
        App::$utente->removeKey($this->nameForm . '_info');
        App::$utente->removeKey($this->nameForm . '_rowidInfo');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        $returnModelObj = itaModel::getInstance($this->returnModel);
        if ($returnModelObj == false) {
            return;
        }
        $_POST = array();
        $_POST['event'] = $this->returnEvent;
        $_POST['info'] = $this->info;
        $_POST['rowid'] = $this->rowidInfo;
        $returnModelObj->parseEvent();
        $this->close();
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
    }

    function CaricaGriglia($griglia, $appoggio) {
        if (is_null($appoggio))
            $appoggio = array();
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows($_POST[$griglia]['gridParam']['rowNum']);
        TableView::enableEvents($griglia);
        $ita_grid01->getDataPage('json', true);
        return;
    }

}

?>