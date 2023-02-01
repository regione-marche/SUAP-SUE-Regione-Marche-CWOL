<?php
/**
 *
 * Abilita Protocollo
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    29.05.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

function proAbilitaProt() {
    $proAbilitaProt = new proAbilitaProt();
    $proAbilitaProt->parseEvent();
    return;
}

class proAbilitaProt extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $nameForm = "proAbilitaProt";
    public $divDettaglio = "proAbilitaProt_divDettaglio";
    public $gridAbilitaProt = "proAbilitaProt_gridAbilitaProt";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = $this->proLib->getPROTDB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->caricaTabella();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAbilitaProt:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridAbilitaProt:
                        $this->caricaTabella();
                        break;
                }
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAbilitaProt:
                        $this->deleteRecord($this->PROT_DB, 'ABILITAPROT', $_POST['rowid'], '', 'ROWID', false);
                        $this->caricaTabella();
                        break;
                }
                break;
            case 'exportTableToExcel':
                break;
            case 'printTableToHTML':
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAbilitaProt:
                        $ita_grid01 = new TableView($_POST['id'],array('sqlDB'=>$this->PROT_DB,'sqlQuery'=>"SELECT * FROM ABILITAPROT"));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPage('json');
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm.'_Salva':
                        $abilitaProt_rec=$_POST[$this->nameForm.'_ABILITAPROT'];
                        $insert_Info = 'Oggetto: '.$abilitaProt_rec['CODICE']." ".$abilitaProt_rec['SEZIONE'];
                        if ($abilitaProt_rec['ROWID']=='') {
                            $this->insertRecord($this->PROT_DB, 'ABILITAPROT', $abilitaProt_rec, $insert_Info);
                        }else {
                            $this->updateRecord($this->PROT_DB, 'ABILITAPROT', $abilitaProt_rec, $insert_Info);
                        }
                        $this->caricaTabella();
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close=true) {
        if ($close) $this->close();
    }

    function caricaTabella() {
     //   Out::show($this->nameForm);
        Out::clearFields($this->nameForm, $this->divDettaglio);
        Out::valore($this->nameForm . '_ABILITAPROT[ROWID]', '');
        $this->caricaGrid();
        Out::setFocus('',$this->nameForm.'_ABILITAPROT[CODICE]');
    }

    function Dettaglio($indice) {
        $abilitaProt_rec=$this->proLib->GetAbilitaProt($indice,'rowid');
        Out::clearFields($this->nameForm, $this->divDettaglio);
        Out::valori($abilitaProt_rec,$this->nameForm.'_ABILITAPROT');
        Out::setFocus('',$this->nameForm.'_ABILITAPROT[CODICE]');
    }

    function caricaGrid() {
        TableView::clearGrid($this->gridAbilitaProt);
        $ita_grid01 = new TableView($this->gridAbilitaProt,
                array(
                        'sqlDB'=>$this->PROT_DB,
                        'sqlQuery'=>"SELECT * FROM ABILITAPROT"));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10000);
        $ita_grid01->setSortIndex('SEZIONE');
        $ita_grid01->setSortOrder('asc');
        $ita_grid01->getDataPage('json');
    }
}
?>