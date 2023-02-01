<?php

/**
 *
 * ANAGRAFICA CAMPI AGGIUNTIVI
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    23.09.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docRic.class.php';

function praDati() {
    $praDati = new praDati();
    $praDati->parseEvent();
    return;
}

class praDati extends itaModel {

    public $PRAM_DB;
    public $COMM_DB;
    public $praLib;
    public $utiEnte;
    public $ITALWEB_DB;
    public $nameForm = "praDati";
    public $divGes = "praDati_divGestione";
    public $divRis = "praDati_divRisultato";
    public $divRic = "praDati_divRicerca";
    public $gridDati = "praDati_gridDati";
    public $gridCampiDizionario = "praDati_gridCampiDizionario";
    public $openMode;
    public $returnModel;
    public $returnEvent;
    public $returnId;
    public $page;
    public $rows;
    public $sidx;
    public $sord;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->utiEnte = new utiEnte();
            $this->ITALWEB_DB = $this->utiEnte->getITALWEB_DB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->openMode = App::$utente->getKey($this->nameForm . '_openMode');
        $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
        $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
        $this->returnId = App::$utente->getKey($this->nameForm . '_returnId');
        $this->page = App::$utente->getKey($this->nameForm . '_page');
        $this->rows = App::$utente->getKey($this->nameForm . '_rows');
        $this->sidx = App::$utente->getKey($this->nameForm . '_sidx');
        $this->sord = App::$utente->getKey($this->nameForm . '_sord');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_openMode', $this->openMode);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnEvent', $this->returnEvent);
            App::$utente->setKey($this->nameForm . '_returnId', $this->returnId);
            App::$utente->setKey($this->nameForm . '_page', $this->page);
            App::$utente->setKey($this->nameForm . '_rows', $this->rows);
            App::$utente->setKey($this->nameForm . '_sidx', $this->sidx);
            App::$utente->setKey($this->nameForm . '_sord', $this->sord);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->CreaCombo();
                $this->praLib->CreaComboTipiCampi($this->nameForm . "_PRAIDC[IDCTIP]");
                switch ($_POST['openMode']) {
                    case 'newFromPasso':
                        $this->openMode = $_POST['openMode'];
                        $this->returnModel = $_POST[$this->nameForm . '_returnModel'];
                        $this->returnEvent = $_POST[$this->nameForm . '_returnEvent'];
                        $this->returnId = $_POST[$this->nameForm . '_returnId'];
                        $this->page = $_POST['page'];
                        $this->rows = $_POST['rows'];
                        $this->sidx = $_POST['sidx'];
                        $this->sord = $_POST['sord'];
                        $this->OpenNuovo();
                        break;
                    default:
                        $this->openMode = "";
                        $this->OpenRicerca();
                        break;
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridDati:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridDati:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridDati, array(
                    'sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('IDCDES');
                $ita_grid01->exportXLS('', 'Idldes.xls');
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridDati:
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPage('json');
                        break;
                }
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praDati', $parameters);
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Idckey':
                        $codice = $_POST[$this->nameForm . '_Idckey'];
                        if ($codice != "") {
                            $Praidc_rec = $this->praLib->getPraidc($codice);
                            if ($Praidc_rec) {
                                $this->Dettaglio($Praidc_rec['ROWID']);
                            }
                            Out::valore($this->nameForm . '_Idckey', $codice);
                        }
                        break;
                    case $this->nameForm . '_PRAIDC[IDCKEY]':
                        $codice = $_POST[$this->nameForm . '_PRAIDC']['IDCKEY'];
                        if (trim($codice) != "") {
                            Out::valore($this->nameForm . '_PRAIDC[IDCKEY]', $codice);
                        }
                        break;
                    case $this->nameForm . '_ANANOM[NOMQUA]':
                        $codice = $_POST[$this->nameForm . '_ANANOM']['NOMQUA'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anaarc_rec = $this->praLib->getAnaarc("QU" . $codice);
                            Out::valore($this->nameForm . '_ANANOM[NOMQUA]', substr($Anaarc_rec['ARCCOD'], 2));
                            Out::valore($this->nameForm . '_QUALIFICA', $Anaarc_rec['ARCDES']);
                        }
                        break;
                    case $this->nameForm . '_ANANOM[NOMPRO]':
                        $codice = $_POST[$this->nameForm . '_ANANOM']['NOMPRO'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anaarc_rec = $this->praLib->getAnaarc("PP" . $codice);
                            Out::valore($this->nameForm . '_ANANOM[NOMPRO]', substr($Anaarc_rec['ARCCOD'], 2));
                            Out::valore($this->nameForm . '_PROFILO', $Anaarc_rec['ARCDES']);
                        }
                        break;
                    case $this->nameForm . '_ANANOM[NOMDEP]':
                        $codice = $_POST[$this->nameForm . '_ANANOM']['NOMDEP'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $Anamed_rec = $this->proLib->getAnamed($codice);
                            Out::valore($this->nameForm . '_ANANOM[NOMDEP]', $Anamed_rec['MEDCOD']);
                            Out::valore($this->nameForm . '_DESTINATARIO', $Anamed_rec['MEDNOM']);
                        }
                        break;
                }
                break;
            case 'returncat':
                $sql = "SELECT CATCOD, CATDES FROM ANACAT WHERE ROWID='" . $_POST['retKey'] . "'";
                try {   // Effettuo la FIND
                    $Anacat_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                    if (count($Anacat_tab) != 0) {
                        Out::valore($this->nameForm . '_Catcod', $Anacat_tab[0]['CATCOD']);
                        Out::valore($this->nameForm . '_Catdes', $Anacat_tab[0]['CATDES']);
                        Out::valore($this->nameForm . '_ANACLA[CLACAT]', $Anacat_tab[0]['CATCOD']);
                        Out::valore($this->nameForm . '_CATDES', $Anacat_tab[0]['CATDES']);
                    }
                } catch (Exception $e) {
                    Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                }
                break;
            case 'returnValore':
                Out::codice("$('#" . $this->nameForm . '_valoreDefault' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Storico':
                        if ($_POST[$this->nameForm . '_Storico'] == 0) {
                            Out::valore($this->nameForm . '_Valido', '');
                        }
                        break;
                    case $this->nameForm . '_PRAIDC[IDCCTR]':
                        //if ($_POST[$this->nameForm . '_PRAIDC']['IDCCTR'] == "REGEXP") {
                        if ($_POST[$this->nameForm . '_PRAIDC']['IDCCTR'] == "008") {
                            Out::show($this->nameForm . '_PRAIDC[IDCEXPR]_field');
                        } else {
                            //$dizionario = array();
                            Out::hide($this->nameForm . '_PRAIDC[IDCEXPR]_field');
                            $this->CaricaGridParam($_POST[$this->nameForm . '_PRAIDC']['IDCCTR'], $_POST[$this->nameForm . '_PRAIDC']['IDCKEY']);
                            //
                            /*
                              include_once ITA_BASE_PATH . "/apps/Pratiche/praidcCtr." . $_POST[$this->nameForm . '_PRAIDC']['IDCCTR'] . ".class.php";
                              $class = "praidcCtr" . $_POST[$this->nameForm . '_PRAIDC']['IDCCTR'];
                              $praidcCtr = new $class();
                              $dizionario = $praidcCtr->GetDizionario();
                              if (count($dizionario) == 1) {
                              $dizionario[0]['VARIABILE'] = $_POST[$this->nameForm . '_PRAIDC']['IDCKEY'];
                              }
                              $this->CaricaGriglia($this->gridCampiDizionario, $dizionario);
                             * */
                        }
                        break;
                }
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        // Importo l'ordinamento del filtro
                        $sql = $this->CreaSql();
                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridDati, array(
                                'sqlDB' => $this->PRAM_DB,
                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows(1000);
                            $ita_grid01->setSortIndex('IDCDES');
                            if (!$ita_grid01->getDataPage('json')) {
                                Out::msgStop("Selezione", "Nessun record trovato.");
                                $this->OpenRicerca();
                            } else {   // Visualizzo la ricerca
                                Out::hide($this->divGes, '');
                                Out::hide($this->divRic, '');
                                Out::show($this->divRis, '');
                                $this->Nascondi();
                                Out::show($this->nameForm . '_AltraRicerca');
                                Out::show($this->nameForm . '_Nuovo');
                                Out::setFocus('', $this->nameForm . '_Nuovo');
                                TableView::enableEvents($this->gridDati);
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                        }
                        break;

                    case $this->nameForm . '_Torna':
                        Out::hide($this->divGes);
                        Out::hide($this->divRic);
                        Out::show($this->divRis);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_Nuovo');
                        Out::setFocus('', $this->nameForm . '_Nuovo');
                        TableView::enableEvents($this->gridDati);
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        $this->OpenNuovo();
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_PRAIDC']['IDCKEY'];
                        $_POST[$this->nameForm . '_PRAIDC']['IDCKEY'] = $codice;
                        $Praidc_ric = $this->praLib->GetPraidc($codice);
                        if (!$Praidc_ric) {
                            $Praidc_rec = $_POST[$this->nameForm . '_PRAIDC'];
                            $Praidc_rec['IDCDEF'] = $_POST[$this->nameForm . '_valoreDefault'];
                            $insert_Info = 'Oggetto: ' . $Praidc_rec['IDCKEY'] . " " . $Praidc_rec['IDCDES'];
                            if (!$this->insertRecord($this->PRAM_DB, 'PRAIDC', $Praidc_rec, $insert_Info)) {
                                Out::msgStop("Errore in Inserimento Dato Aggiuntivo");
                                break;
                            }
                            Out::msgInfo("Inserimento", "Inserito Dato aggiuntivo <b>$codice</b>");
                            if ($this->returnModel) {
                                $model = $this->returnModel;
                                $_POST = array();
                                $_POST['event'] = $this->returnEvent;
                                $_POST['id'] = $this->returnId;
                                $_POST['page'] = $this->page;
                                $_POST['rows'] = $this->rows;
                                $_POST['sidx'] = $this->sidx;
                                $_POST['sord'] = $this->sord;
                                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                $model();
                                Out::closeDialog($this->nameForm);
                            } else {
                                $this->OpenRicerca();
                            }
                        } else {
                            Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_PRAIDC[IDCKEY]');
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $Praidc_rec = $_POST[$this->nameForm . '_PRAIDC'];
                        $Praidc_rec['IDCDEF'] = $_POST[$this->nameForm . '_valoreDefault'];
                        $codice = $Praidc_rec['IDCKEY'];
                        $update_Info = 'Oggetto: ' . $Praidc_rec['IDCKEY'] . " " . $Praidc_rec['IDCDES'];
                        if ($this->updateRecord($this->PRAM_DB, 'PRAIDC', $Praidc_rec, $update_Info)) {
                            //$this->OpenRicerca();
                            $this->Dettaglio($Praidc_rec['ROWID']);
                        }
                        break;
                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $Praidc_rec = $_POST[$this->nameForm . '_PRAIDC'];
                        try {
                            $delete_Info = 'Oggetto: ' . $Praidc_rec['IDCKEY'] . " " . $Praidc_rec['IDCDES'];
                            if ($this->deleteRecord($this->PRAM_DB, 'PRAIDC', $Praidc_rec['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA CAMPI AGGIUNTIVI", $e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_buttDiz':
                        $this->embedVars("returnValore");
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_openMode');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_returnId');
        App::$utente->removeKey($this->nameForm . '_page');
        App::$utente->removeKey($this->nameForm . '_rows');
        App::$utente->removeKey($this->nameForm . '_sidx');
        App::$utente->removeKey($this->nameForm . '_sord');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function CreaSql() {
        // Imposto il filtro di ricerca
        $sql = "";
        // Importo l'ordinamento del filtro
        $sql = "SELECT * FROM PRAIDC WHERE ROWID=ROWID";

        if ($_POST[$this->nameForm . '_Idckey'] != "") {
            //$sql .= " AND IDCKEY LIKE '%" . addslashes($_POST[$this->nameForm . '_Idckey']) . "%'";
            $sql .= " AND IDCKEY LIKE '%" . strtoupper($_POST[$this->nameForm . '_Idckey']) . "%'";
        }

        if ($_POST[$this->nameForm . '_Idcdes'] != "") {
            //$sql .= " AND IDCDES LIKE '%" . addslashes($_POST[$this->nameForm . '_Idcdes']) . "%'";
            $sql .= " AND IDCDES LIKE '%" . strtoupper($_POST[$this->nameForm . '_Idcdes']) . "%'";
        }
        return $sql;
    }

    function OpenNuovo() {
        Out::attributo($this->nameForm . '_PRAIDC[IDCKEY]', 'readonly', '1');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        $this->Nascondi();
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::show($this->nameForm . '_Aggiungi');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::setFocus('', $this->nameForm . '_PRAIDC[IDCKEY]');
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridDati);
        TableView::clearGrid($this->gridDati);
        TableView::disableEvents($this->gridCampiDizionario);
        TableView::clearGrid($this->gridCampiDizionario);
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Idckey');
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_PRAIDC[IDCCTR]', 1, "", "1", "");
        foreach (glob(ITA_BASE_PATH . "/apps/Pratiche/praidcCtr.*.class.php") as $file) {
            include_once $file;
            $arrFile = explode(".", $file);
            $class = "praidcCtr" . $arrFile[1];
            $praidcCtr = new $class();
            Out::select($this->nameForm . '_PRAIDC[IDCCTR]', 1, $praidcCtr->getCodice(), "0", $praidcCtr->getDescrizione());
        }
        //
        Out::select($this->nameForm . '_PRAIDC[IDCFIA__1]', 1, "D", "1", "Disattivato");
        Out::select($this->nameForm . '_PRAIDC[IDCFIA__1]', 1, "E", "0", "Errore");
        Out::select($this->nameForm . '_PRAIDC[IDCFIA__1]', 1, "W", "0", "Warning");
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Torna');
    }

    public function Dettaglio($_Indice) {
        $Praidc_rec = $this->praLib->GetPraidc($_Indice, 'rowid');
        $open_Info = 'Oggetto: ' . $Praidc_rec['IDCKEY'] . " " . $Praidc_rec['IDCDES'];
        $this->openRecord($this->PRAM_DB, 'PRAIDC', $open_Info);
        $this->Nascondi();
        Out::valori($Praidc_rec, $this->nameForm . '_PRAIDC');
        Out::valore($this->nameForm . '_valoreDefault', $Praidc_rec['IDCDEF']);

        if ($Praidc_rec['IDCCTR'] == "REGEXP") {
            Out::show($this->nameForm . '_PRAIDC[IDCEXPR]_field');
        } else {
            Out::hide($this->nameForm . '_PRAIDC[IDCEXPR]_field');
        }
        //
        $this->CaricaGridParam($Praidc_rec['IDCCTR'], $Praidc_rec['IDCKEY']);
        //
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::attributo($this->nameForm . '_PRAIDC[IDCKEY]', 'readonly', '0');

//        $AnaarcQU_rec=$this->praLib->getAnaarc("QU".$Ananom_rec['NOMQUA']);
//        Out::valore($this->nameForm.'_QUALIFICA',$AnaarcQU_rec['ARCDES']);
//        $AnaarcPP_rec=$this->praLib->getAnaarc("PP".$Ananom_rec['NOMPRO']);
//        Out::valore($this->nameForm.'_PROFILO',$AnaarcPP_rec['ARCDES']);
//        $Anamed_rec=$this->proLib->getAnamed($Ananom_rec['NOMDEP']);
//        Out::valore($this->nameForm.'_DESTINATARIO',$Anamed_rec['MEDNOM']);
        Out::setFocus('', $this->nameForm . '_PRAIDC[IDCKEY]');
        TableView::disableEvents($this->gridDati);
    }

    function GetAnacla($_Cond, $_Codice) {
        $sql = "SELECT ROWID FROM ANACLA WHERE $_Cond AND CLACCA='$_Codice'";
        $Anacla_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        return $Anacla_tab;
    }

    function CaricaGridParam($codice, $idckey) {
        if (!$codice) {
            return;
        }
        $dizionario = array();
        include_once ITA_BASE_PATH . "/apps/Pratiche/praidcCtr.$codice.class.php";
        $class = "praidcCtr$codice";
        $praidcCtr = new $class();
        $dizionario = $praidcCtr->GetDizionario();
        if (count($dizionario) == 1) {
            $dizionario[0]['VARIABILE'] = $idckey;
        }
        $this->CaricaGriglia($this->gridCampiDizionario, $dizionario);
    }

    function CaricaGriglia($griglia, $appoggio, $pageRows = '20') {
        $arrayGrid = array();
        foreach ($appoggio as $arrayRow) {
            unset($arrayRow['PASMETA']);
            $arrayGrid[] = $arrayRow;
        }
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $arrayGrid,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows($pageRows);
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    function embedVars($ritorno) {
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
        $praLibVar = new praLibVariabili();
        $dictionaryLegend = $praLibVar->getLegendaPratica('adjacency', 'smarty');
        docRic::ricVariabili($dictionaryLegend, $this->nameForm, $ritorno, true);
        return true;
    }

}

?>