<?php
/**
 *
 * GESTIONE REPORT
 *
 * PHP Version 5
 *
 * @category
 * @package    Gestione elenco Report di Stampa
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    12.04.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once './apps/Sviluppo/devLib.class.php';

function devGestReport() {
    $devGestReport = new devGestReport();
    $devGestReport->parseEvent();
    return;
}

class devGestReport extends itaModel {
    public $devLib;
    public $ITALSOFT_DB;
    public $ITALWEB_DB;
    public $sistema=false;
    public $tabella=array();
    public $nameForm="devGestReport";
    public $gridElenco="devGestReport_gridElenco";
    public $gridElencoVis="devGestReport_gridElencoVis";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->devLib = new devLib();
            $this->ITALSOFT_DB=$this->devLib->getITALSOFTDB();
            $this->ITALWEB_DB=$this->devLib->getITALWEB();
            $this->tabella = App::$utente->getKey($this->nameForm . '_tabella');
            $this->sistema = App::$utente->getKey($this->nameForm . '_sistema');
        }catch(Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_tabella', $this->tabella);
            App::$utente->setKey($this->nameForm . '_sistema', $this->sistema);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                TableView::disableEvents($this->gridElenco);
                TableView::clearGrid($this->gridElenco);
                TableView::disableEvents($this->gridElencoVis);
                TableView::clearGrid($this->gridElencoVis);
                $this->sistema=$_POST['gestione'];
                App::log('$this->sistema');
                App::log($this->sistema);
                App::log($_POST);
                if ($this->sistema===true) {
                    $this->caricaElencoSistGest();
                }else {
                    $this->caricaElencoUtenteVis();
                    $this->caricaElencoUtenteGest();
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridElenco:
                        if ($this->sistema) {
                            $this->deleteRecord($this->ITALSOFT_DB, 'ita_gestreport', $this->tabella[$_POST['rowid']]['ROWID'], '', 'ROWID', false);
                            $this->caricaElencoSistGest();
                        }else {
                            $this->deleteRecord($this->ITALWEB_DB, 'REP_GEST', $this->tabella[$_POST['rowid']]['ROWID'], '', 'ROWID', false);
                            $this->caricaElencoUtenteGest();
                        }
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridElenco:
                        $tabella_rec = array();
                        if ($this->sistema) {
                            $this->insertRecord($this->ITALSOFT_DB, 'ita_gestreport', $tabella_rec, '', 'ROWID', false);
                            $this->caricaElencoSistGest();
                        }else {
                            $this->insertRecord($this->ITALWEB_DB, 'REP_GEST', $tabella_rec, '', 'ROWID', false);
                            $this->caricaElencoUtenteGest();
                        }
                        break;
                }
                break;
            case 'afterSaveCell':
                if ($_POST['value']!='undefined') {
                    switch ($_POST['id']) {
                        case $this->gridElenco:
                            $this->tabella[$_POST['rowid']][$_POST['cellname']] = $_POST['value'];
                            if ($this->sistema) {
                                $this->updateRecord($this->ITALSOFT_DB, 'ita_gestreport', $this->tabella[$_POST['rowid']], '', 'ROWID', false);
                                $this->caricaElencoSistGest();
                            }else {
                                $this->updateRecord($this->ITALWEB_DB, 'REP_GEST', $this->tabella[$_POST['rowid']], '', 'ROWID', false);
                                $this->caricaElencoUtenteGest();
                            }
                            break;
                    }
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close=true) {
        if ($close) $this->close();
        App::$utente->removeKey($this->nameForm . '_tabella');
        App::$utente->removeKey($this->nameForm . '_sistema');
    }

    public function caricaElencoSistGest() {
        $this->tabella = $this->devLib->GetIta_gestreport();
        $this->CaricaGriglia($this->gridElenco, $this->tabella);
        Out::hide($this->nameForm."_divElencoVis");
    }

    public function caricaElencoUtenteVis() {
        Out::show($this->nameForm."_divElencoVis");
        $itaGestreport = $this->devLib->GetIta_gestreport();
        $this->CaricaGriglia($this->gridElencoVis, $itaGestreport);
    }

    public function caricaElencoUtenteGest() {
        $this->tabella = $this->devLib->GetRepGest();
        $this->CaricaGriglia($this->gridElenco, $this->tabella);
    }

    function caricaGriglia($griglia, $appoggio) {
        $ita_grid01 = new TableView(
                $griglia,
                array('arrayTable' => $appoggio,
                        'rowIndex' => 'idx')
        );
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10000);
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }
}
?>
