<?php

/**
 *
 * ANAGRAFICA SERVIZI
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    26.09.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';

function praServ() {
    $praServ = new praServ();
    $praServ->parseEvent();
    return;
}

class praServ extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $utiEnte;
    public $ITALWEB_DB;
    public $workDate;
    public $nameForm = "praServ";
    public $divGes = "praServ_divGestione";
    public $divRis = "praServ_divRisultato";
    public $divSto = "praServ_divStorico";
    public $divRic = "praServ_divRicerca";
    public $gridServ = "praServ_gridServ";
    public $gridStorico = "praServ_gridStorico";

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
        $data = App::$utente->getKey('DataLavoro');
        if ($data != '') {
            $this->workDate = $data;
        } else {
            $this->workDate = date('Ymd');
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridServ:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                    case $this->gridStorico:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridServ:
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
                $ita_grid01 = new TableView($this->gridServ,
                                array(
                                    'sqlDB' => $this->PRAM_DB,
                                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('UNIDES');
                $ita_grid01->exportXLS('', 'Servizi.xls');
                break;
            case 'onClickTablePager':
                $ordinamento = $_POST['sidx'];
                if ($ordinamento == 'DECODIFICA') {
                    $ordinamento = 'ANAUNI2.UNIDES';
                }
                if ($ordinamento == 'SETSER') {
                    $ordinamento = 'ANAUNI.UNISET';
                }
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($ordinamento);
                $ita_grid01->setSortOrder($_POST['sord']);
                $Result_tab = $ita_grid01->getDataArray();
                $Result_tab = $this->elaboraRecords($Result_tab);
                $ita_grid01->getDataPageFromArray('json', $Result_tab);
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praServ', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        // Importo l'ordinamento del filtro
                        $sql = $this->CreaSql();
                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridServ,
                                            array(
                                                'sqlDB' => $this->PRAM_DB,
                                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows(10);
                            $ita_grid01->setSortIndex('UNISET');
                            $Result_tab = $ita_grid01->getDataArray();
                            $Result_tab = $this->elaboraRecords($Result_tab);
                            if (!$ita_grid01->getDataPageFromArray('json', $Result_tab)) {
                                Out::msgStop("Selezione", "Nessun record trovato.");
                                $this->OpenRicerca();
                            } else {   // Visualizzo la ricerca
                                Out::hide($this->divGes, '');
                                Out::hide($this->divRic, '');
                                Out::show($this->divRis, '');
                                Out::hide($this->divSto, '');
                                $this->Nascondi();
                                Out::show($this->nameForm . '_AltraRicerca');
                                Out::show($this->nameForm . '_Nuovo');
                                Out::setFocus('', $this->nameForm . '_Nuovo');
                                TableView::enableEvents($this->gridServ);
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                        }
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->Nascondi();
                        Out::clearFields($this->nameForm, $this->divRic);
                        Out::clearFields($this->nameForm, $this->divGes);
                        Out::attributo($this->nameForm . '_ANAUNI[UNISER]', 'readonly', '1');
                        Out::attributo($this->nameForm . '_ANAUNI[UNIAPE]', 'readonly', '0');
                        Out::valore($this->nameForm . '_ANAUNI[UNIDAP]', $this->workDate);
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANAUNI[UNISET]');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_ANAUNI']['UNISER'];
                        $settore = $_POST[$this->nameForm . '_ANAUNI']['UNISET'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $_POST[$this->nameForm . '_ANAUNI']['UNISER'] = $codice;
                        $Anauni_ric = $this->praLib->GetAnauniServ($settore, $codice);
                        if (!$Anauni_ric) {
                            $Anauni_rec = $_POST[$this->nameForm . '_ANAUNI'];
                            try {
                                $insert_Info = 'Oggetto: ' . $Anauni_rec['UNISER'] . " " . $Anauni_rec['UNIDES'];
                                if ($this->insertRecord($this->PRAM_DB, 'ANAUNI', $Anauni_rec, $insert_Info)) {
                                    $this->OpenRicerca();
                                }
                            } catch (Exception $e) {
                                Out::msgStop("Errore in Inserimento", $e->getMessage(), '600', '600');
                            }
                        } else {
                            Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_ANAUNI[UNISET]');
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $Anauni_rec = $_POST[$this->nameForm . '_ANAUNI'];
                        $Ananom_rec = $this->praLib->getAnanom($Anauni_rec['UNIRES']);
                        $data = $Anauni_rec['UNIAPE'];
                        if ($data != "") {
                            $data = substr($data, 6, 2) . '/' . substr($data, 4, 2) . '/' . substr($data, 0, 4);
                            Out::msgQuestion("Chiusura Servizio", 'Confermando ' . $Ananom_rec["NOMCOG"] . ' ' . $Ananom_rec["NOMNOM"] . ' non sarà più responsabile del servizio <br>' . $Anauni_rec["UNIDES"] . ' in data ' . $data, array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaChiusura', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaChiusura', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                            break;
                        } else {
                            $codice = $Anauni_rec['UNISER'];
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anauni_rec['UNISER'] = $codice;
                            $update_Info = 'Oggetto: ' . $Anauni_rec['UNISER'] . " " . $Anauni_rec['UNIDES'];
                            if ($this->updateRecord($this->PRAM_DB, 'ANAUNI', $Anauni_rec, $update_Info)) {
                                $this->OpenRicerca();
                            }
                            break;
                        }
                    case $this->nameForm . '_ConfermaChiusura':
                        $Anauni_rec = $_POST[$this->nameForm . '_ANAUNI'];
                        $codice = $Anauni_rec['UNISER'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $Anauni_rec['UNISER'] = $codice;
                        $update_Info = 'Oggetto: ' . $Anauni_rec['UNISER'] . " " . $Anauni_rec['UNIDES'];
                        if ($this->updateRecord($this->PRAM_DB, 'ANAUNI', $Anauni_rec, $update_Info)) {
                            
                        }
                        $Anauni_rec['UNIRES'] = $Anauni_rec['UNIDAP'] = $Anauni_rec['UNIAPE'] = $Anauni_rec['UNIQUA'] = $Anauni_rec['UNIPRO'] = "";
                        $insert_Info = 'Oggetto: ' . $Anauni_rec['UNISER'] . " " . $Anauni_rec['UNIDES'];
                        if ($this->insertRecord($this->PRAM_DB, 'ANAUNI', $Anauni_rec, $insert_Info)) {
                            $this->OpenRicerca();
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
                        $Anauni_rec = $_POST[$this->nameForm . '_ANAUNI'];
                        try {
                            $delete_Info = 'Oggetto: ' . $Anauni_rec['UNISER'] . " " . $Anauni_rec['UNIDES'];
                            if ($this->deleteRecord($this->PRAM_DB, 'ANAUNI', $Anauni_rec['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA SERVIZI", $e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_Storico':
                        $sql = $this->CreaSqlSto();

                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridStorico,
                                            array(
                                                'sqlDB' => $this->PRAM_DB,
                                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows(10);
                            $ita_grid01->setSortIndex('NOMCOG');
                            if (!$ita_grid01->getDataPage('json')) {
                                Out::msgStop("Selezione", "Nessun record nello storico.");
                                $this->OpenRicerca();
                            } else {   // Visualizzo la ricerca
                                Out::hide($this->divGes, '');
                                Out::hide($this->divRic, '');
                                Out::show($this->divSto, '');
                                Out::hide($this->divRis, '');
                                $this->Nascondi();
                                Out::show($this->nameForm . '_AltraRicerca');
                                Out::hide($this->nameForm . '_Nuovo');
                                Out::setFocus('', $this->nameForm . '_AltraRicerca');
                                TableView::enableEvents($this->gridStorico);
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                        }

                        break;
                    case $this->nameForm . '_ANAUNI[UNIRES]_butt':
                        praRic::praRicAnanom($this->PRAM_DB, "praServ", "RICERCA DIPENDENTI", "returnUnires");
                        break;

                    case $this->nameForm . '_ANAUNI[UNISET]_butt':
                    case $this->nameForm . '_Uniset_butt':
                        praRic::praRicAnauni("praServ", "RICERCA SETTORI", "returnUniset");
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Uniset':
                        $codice = $_POST[$this->nameForm . '_Uniset'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anauni_rec = $this->praLib->getAnauni($codice);
                            Out::valore($this->nameForm . '_Uniset', $Anauni_rec["UNISET"]);
                            Out::valore($this->nameForm . '_Settore', $Anauni_rec["UNIDES"]);
                            Out::valore($this->nameForm . '_ANAUNI[UNISET]', $Anauni_rec['UNISET']);
                            Out::valore($this->nameForm . '_SETTORE', $Anauni_rec['UNIDES']);
                        }
                        break;
                    case $this->nameForm . '_Uniser':
                        $codice = $_POST[$this->nameForm . '_Uniset'];
                        $servizio = $_POST[$this->nameForm . '_Uniser'];
                        if (trim($codice) != "") {
                            $servizio = str_repeat("0", 6 - strlen(trim($servizio))) . trim($servizio);
                            $sql = "SELECT * FROM ANAUNI WHERE UNISET='" . $codice . "' AND UNISER='" . $servizio . "'";
                            $Anauni_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                            if ($Anauni_rec) {
                                $this->Dettaglio($Anauni_rec['ROWID']);
                            } else {
                                if (trim($servizio) != "") {
                                    Out::valore($this->nameForm . '_Uniser', $servizio);
                                }
                            }
                        } else {
                            if (trim($servizio) != "") {
                                $servizio = str_repeat("0", 6 - strlen(trim($servizio))) . trim($servizio);
                                Out::valore($this->nameForm . '_Uniser', $servizio);
                            }
                        }
                        break;
                    case $this->nameForm . '_ANAUNI[UNISET]':
                        $codice = $_POST[$this->nameForm . '_ANAUNI']['UNISET'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anauni_rec = $this->praLib->getAnauni($codice);
                            Out::valore($this->nameForm . '_Uniset', $Anauni_rec["UNISET"]);
                            Out::valore($this->nameForm . '_Settore', $Anauni_rec["UNIDES"]);
                            Out::valore($this->nameForm . '_ANAUNI[UNISET]', $Anauni_rec['UNISET']);
                            Out::valore($this->nameForm . '_SETTORE', $Anauni_rec['UNIDES']);
                        }
                        break;
                    case $this->nameForm . '_ANAUNI[UNISER]':
                        $codice = $_POST[$this->nameForm . '_ANAUNI']['UNISER'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_ANAUNI[UNISER]', $codice);
                        }
                        break;
                    case $this->nameForm . '_ANAUNI[UNIRES]':
                        $codice = $_POST[$this->nameForm . '_ANAUNI']['UNIRES'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Ananom_rec = $this->praLib->getAnanom($codice);
                            Out::valore($this->nameForm . '_ANAUNI[UNIRES]', $Ananom_rec["NOMRES"]);
                            Out::valore($this->nameForm . '_RESPONSABILE', $Ananom_rec["NOMCOG"] . ' ' . $Ananom_rec["NOMNOM"]);
                            $AnaarcQU_rec = $this->praLib->getAnaarc("QU" . $Ananom_rec['NOMQUA']);
                            Out::valore($this->nameForm . '_ANAUNI[UNIQUA]', $Ananom_rec['NOMQUA']);
                            Out::valore($this->nameForm . '_QUALIFICA', $AnaarcQU_rec['ARCDES']);
                            $AnaarcPP_rec = $this->praLib->getAnaarc("PP" . $Ananom_rec['NOMPRO']);
                            Out::valore($this->nameForm . '_ANAUNI[UNIPRO]', $Ananom_rec['NOMPRO']);
                            Out::valore($this->nameForm . '_PROFILO', $AnaarcPP_rec['ARCDES']);
//                            $Ananom_rec=$this->praLib->getAnanom($Anauni_rec['UNIRES']);
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
//                    Out::codice('closeCurrDialog();');
                } catch (Exception $e) {
                    Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Storico':
                        if ($_POST[$this->nameForm . '_Storico'] == 0) {
                            Out::valore($this->nameForm . '_Valido', '');
                        }
                        break;
                }
                break;
            case "returnAnaarc":
                switch ($_POST["retid"]) {
                    case "returnNomqua":
                        $Anaarc_rec = $this->praLib->GetAnaarc($_POST["retKey"], 'rowid');
                        if ($Anaarc_rec) {
                            Out::valore($this->nameForm . '_ANAUNI[UNIQUA]', substr($Anaarc_rec["ARCCOD"], 2));
                            Out::valore($this->nameForm . '_QUALIFICA', $Anaarc_rec["ARCDES"]);
                        }
                        break;
                    case "returnNompro":
                        $Anaarc_rec = $this->praLib->GetAnaarc($_POST["retKey"], 'rowid');
                        if ($Anaarc_rec) {
                            Out::valore($this->nameForm . '_ANAUNI[UNIPRO]', substr($Anaarc_rec["ARCCOD"], 2));
                            Out::valore($this->nameForm . '_PROFILO', $Anaarc_rec["ARCDES"]);
                        }
                        break;
                }
                break;
            case "returnUnires":
                $Ananom_rec = $this->praLib->GetAnanom($_POST["retKey"], 'rowid');
                if ($Ananom_rec) {
                    Out::valore($this->nameForm . '_ANAUNI[UNIRES]', $Ananom_rec["NOMRES"]);
                    Out::valore($this->nameForm . '_RESPONSABILE', $Ananom_rec["NOMCOG"] . ' ' . $Ananom_rec["NOMNOM"]);
                    $AnaarcQU_rec = $this->praLib->getAnaarc("QU" . $Ananom_rec['NOMQUA']);
                    Out::valore($this->nameForm . '_ANAUNI[UNIQUA]', $Ananom_rec['NOMQUA']);
                    Out::valore($this->nameForm . '_QUALIFICA', $AnaarcQU_rec['ARCDES']);
                    $AnaarcPP_rec = $this->praLib->getAnaarc("PP" . $Ananom_rec['NOMPRO']);
                    Out::valore($this->nameForm . '_ANAUNI[UNIPRO]', $Ananom_rec['NOMPRO']);
                    Out::valore($this->nameForm . '_PROFILO', $AnaarcPP_rec['ARCDES']);
                    $Ananom_rec = $this->praLib->getAnanom($Anauni_rec['UNIRES']);
                }
                break;
            case "returnUniset":
                $Anauni_rec = $this->praLib->GetAnauni($_POST["retKey"], 'rowid');
                if ($Anauni_rec) {
                    Out::valore($this->nameForm . '_Uniset', $Anauni_rec["UNISET"]);
                    Out::valore($this->nameForm . '_Settore', $Anauni_rec["UNIDES"]);
                    Out::valore($this->nameForm . '_ANAUNI[UNISET]', $Anauni_rec['UNISET']);
                    Out::valore($this->nameForm . '_SETTORE', $Anauni_rec['UNIDES']);
                }
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function CreaSql() {
        // Imposto il filtro di ricerca
        $sql = "SELECT ANAUNI.ROWID AS ROWID, ANAUNI.UNISET AS UNISET, ANAUNI.UNIRES AS UNIRES, ANAUNI.UNIDES AS UNIDES, ANAUNI.UNISER AS UNISER,
                ANAUNI2.UNIDES AS DECODIFICA, NOMCOG & ' ' & NOMNOM AS NOMCOG, ANAUNI.UNISET & ' - ' & ANAUNI.UNISER AS SETSER
                FROM ANAUNI ANAUNI LEFT OUTER JOIN ANANOM ANANOM ON ANAUNI.UNIRES=ANANOM.NOMRES
                LEFT OUTER JOIN ANAUNI ANAUNI2 ON ANAUNI.UNISET=ANAUNI2.UNISET AND ANAUNI2.UNISER=''
                WHERE ANAUNI.UNISET<>'' AND ANAUNI.UNISER<>'' AND ANAUNI.UNIOPE='' AND ANAUNI.UNIADD='' AND ANAUNI.UNIAPE=''";
//        $sql="SELECT ANAUNI.ROWID AS ROWID, UNISET, UNIDES, UNISER, ".
//                $this->PRAM_DB->strConcat("NOMCOG", "' '", "NOMNOM")." AS NOMCOG, ".
//                $this->PRAM_DB->strConcat("UNISET", "' - '", "UNISER")." AS SETSER
//            FROM ANAUNI ANAUNI LEFT OUTER JOIN ANANOM ANANOM
//            ON ANAUNI.UNIRES=ANANOM.NOMRES
//            WHERE UNISET<>'' AND UNISER<>'' AND UNIOPE='' AND UNIADD='' AND UNIAPE=''";
        if ($_POST[$this->nameForm . '_Uniset'] != "") {
            $sql .= " AND ANAUNI.UNISET = '" . $_POST[$this->nameForm . '_Uniset'] . "' AND ANAUNI.UNISER<>'' AND ANAUNI.UNIOPE='' AND ANAUNI.UNIAPE=''";
        }
        if ($_POST[$this->nameForm . '_Uniser'] != "") {
            $sql .= " AND ANAUNI.UNISER = '" . $_POST[$this->nameForm . '_Uniser'] . "'";
        }
        if ($_POST[$this->nameForm . '_Unides'] != "") {
            $sql .= " AND ANAUNI.UNIDES LIKE '%" . addslashes($_POST[$this->nameForm . '_Unides']) . "%' AND ANAUNI.UNISER<>'' AND ANAUNI.UNIAPE=''";
        }
        App::log($sql);
        return $sql;
    }

    function CreaSqlSto() {
        // Imposto il filtro di ricerca
        $sql = "SELECT ANAUNI.ROWID AS ROWID, UNIDES, UNIDAP, UNIAPE, " .
                $this->PRAM_DB->strConcat("NOMCOG", "' '", "NOMNOM") . " AS NOMCOG,
                FROM ANAUNI ANAUNI LEFT OUTER JOIN ANANOM ANANOM
                ON ANAUNI.UNIRES=ANANOM.NOMRES
                WHERE LENGTH(".$this->PRAM_DB->strConcat($this->PRAM_DB->strConcat('UNISET','UNISER'), $this->PRAM_DB->strConcat('UNIOPE','UNIADD')).")=6 AND UNIAPE!='' AND UNISET = '" . $_POST[$this->nameForm . '_ANAUNI']['UNISET'] . "'";

        return $sql;
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::hide($this->divSto, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridServ);
        TableView::clearGrid($this->gridServ);
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Uniset');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Storico');
    }

    public function Dettaglio($_Indice) {
        $Anauni_rec = $this->praLib->GetAnauni($_Indice, 'rowid');
        $open_Info = 'Oggetto: ' . $Anauni_rec['UNISER'] . " " . $Anauni_rec['UNIDES'];
        $this->openRecord($this->PRAM_DB, 'ANAUNI', $open_Info);
        $this->Nascondi();
        Out::valori($Anauni_rec, $this->nameForm . '_ANAUNI');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_Storico');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::hide($this->divSto, '');
        Out::show($this->divGes, '');
        Out::attributo($this->nameForm . '_ANAUNI[UNISER]', 'readonly', '0');
        $Ananom_rec = $this->praLib->getAnanom($Anauni_rec['UNIRES']);
        Out::valore($this->nameForm . '_ANAUNI[UNIRES]', $Ananom_rec["NOMRES"]);
        Out::valore($this->nameForm . '_RESPONSABILE', $Ananom_rec["NOMCOG"] . ' ' . $Ananom_rec["NOMNOM"]);
        $AnaarcQU_rec = $this->praLib->getAnaarc("QU" . $Ananom_rec['NOMQUA']);
        Out::valore($this->nameForm . '_ANAUNI[UNIQUA]', $Ananom_rec['NOMQUA']);
        Out::valore($this->nameForm . '_QUALIFICA', $AnaarcQU_rec['ARCDES']);
        $AnaarcPP_rec = $this->praLib->getAnaarc("PP" . $Ananom_rec['NOMPRO']);
        Out::valore($this->nameForm . '_ANAUNI[UNIPRO]', $Ananom_rec['NOMPRO']);
        Out::valore($this->nameForm . '_PROFILO', $AnaarcPP_rec['ARCDES']);
        $Anauni_rec = $this->praLib->getAnauni($Anauni_rec['UNISET']);
        Out::valore($this->nameForm . '_SETTORE', $Anauni_rec['UNIDES']);
        Out::setFocus('', $this->nameForm . '_ANAUNI[UNISET]');
        TableView::disableEvents($this->gridServ);
    }

    public function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
//            $sql="SELECT * FROM ANAUNI WHERE UNISET='".$Result_rec['UNISET']."'";
//            $Anauni_rec= ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            $Result_tab[$key]['SETSER'] = $Result_rec['UNISET'] . "/" . $Result_rec['UNISER'];
            $Ananom_rec = $this->praLib->GetAnanom($Result_rec['UNIRES']);
            $Result_tab[$key]['NOMCOG'] = $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM'];
        }
        return $Result_tab;
    }

}

?>