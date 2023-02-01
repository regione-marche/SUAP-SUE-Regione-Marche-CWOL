<?php

/**
 *
 * ANAGRAFICA CLASSIFICAZIONI
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    21.06.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function praClassificazioni() {
    $praClassificazioni = new praClassificazioni();
    $praClassificazioni->parseEvent();
    return;
}

class praClassificazioni extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $utiEnte;
    public $nameForm = "praClassificazioni";
    public $divGes = "praClassificazioni_divGestione";
    public $divRis = "praClassificazioni_divRisultato";
    public $divRic = "praClassificazioni_divRicerca";
    public $gridClassificazioni = "praClassificazioni_gridClassificazioni";
    private $gridFilters = array();

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->utiEnte = new utiEnte();
            $this->utiEnte->getITALWEB_DB();
            $this->gridFilters = App::$utente->getKey($this->nameForm . '_gridFilters');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_gridFilters', $this->gridFilters);
        }
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->CreaCombo();
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridClassificazioni:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridClassificazioni:
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
                $ita_grid01 = new TableView($this->gridClassificazioni, array(
                    'sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('CLADES');
                $ita_grid01->exportXLS('', 'classificazioni.xls');
                break;
            case 'onClickTablePager':
                $this->setGridFilters();
                $ordinamento = $_POST['sidx'];
                if ($ordinamento == "TSPDES") {
                    $ordinamento = "CLASPO";
                }
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($ordinamento);
                $ita_grid01->setSortOrder($_POST['sord']);
//                $Result_tab = $this->elaboraRecord($ita_grid01->getDataArray());
//                $ita_grid01->getDataPageFromArray('json', $Result_tab);
                $ita_grid01->getDataPage('json');
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praClassificazioni', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        // Importo l'ordinamento del filtro
                        $sql = $this->CreaSql();
                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridClassificazioni, array(
                                'sqlDB' => $this->PRAM_DB,
                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows(1000);
                            $ita_grid01->setSortIndex('CLADES');
                            //$Result_tab = $this->elaboraRecord($ita_grid01->getDataArray());
                            //if (!$ita_grid01->getDataPageFromArray('json', $Result_tab)) {
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
                                TableView::enableEvents($this->gridClassificazioni);
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
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANACLA[CLACOD]');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_ANACLA']['CLACOD'];
                        $Anacla_ric = $this->praLib->GetAnacla($codice);
                        if (!$Anacla_ric) {
                            $Anacla_rec = $_POST[$this->nameForm . '_ANACLA'];
                            $insert_Info = 'Oggetto: Inserisco Class.' . $Anacla_rec['CLACOD'] . " - " . $Anacla_rec['CLADES'];
                            if (!$this->insertRecord($this->PRAM_DB, 'ANACLA', $Anacla_rec, $insert_Info)) {
                                Out::msgStop("Attenzione!!!", "Errore inserimento classificazione " . $Anacla_rec['CLACOD'] . " - " . $Anacla_rec['CLADES']);
                                break;
                            }
                            $this->OpenRicerca();
                        } else {
                            Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_ANACLA[CLACOD]');
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $Anacla_rec = $_POST[$this->nameForm . '_ANACLA'];
                        $update_Info = 'Oggetto: Inserisco Class.' . $Anacla_rec['CLACOD'] . " - " . $Anacla_rec['CLADES'];
                        if (!$this->updateRecord($this->PRAM_DB, 'ANACLA', $Anacla_rec, $update_Info)) {
                            Out::msgStop("Attenzione!!!", "Errore aggiornamento classificazione " . $Anacla_rec['CLACOD'] . " - " . $Anacla_rec['CLADES']);
                            break;
                        }
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $Anacla_rec = $_POST[$this->nameForm . '_ANACLA'];
                        $delete_Info = 'Oggetto: Cancello classificazione ' . $Anacla_rec['CLACOD'] . " - " . $Anacla_rec['CLADES'];
                        if (!$this->deleteRecord($this->PRAM_DB, 'ANACLA', $Anacla_rec['ROWID'], $delete_Info)) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA SETTORI", $e->getMessage());
                        }
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_SvuotaExt':
                        Out::valore($this->nameForm . '_ANACLA[CLAEXT]', '');
                        break;
                    case $this->nameForm . '_ANACLA[CLASPO]_butt':
                        praRic::praRicAnatsp($this->nameForm);
                        break;
                    case $this->nameForm . '_ANACLA[CLAPDR]_butt':
                        $where1 = $where2 = "";
                        if ($_POST[$this->nameForm . "_ANACLA"]['CLACOD']) {
                            $where1 = " AND CLACOD<>'" . $_POST[$this->nameForm . "_ANACLA"]['CLACOD'] . "'";
                        }
                        if ($_POST[$this->nameForm . "_ANACLA"]['CLASPO']) {
                            $where2 = " AND CLASPO = " . $_POST[$this->nameForm . "_ANACLA"]['CLASPO'];
                        }
                        praRic::praRicAnacla($this->nameForm, $where1 . $where2);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Clacod':
                        $codice = $_POST[$this->nameForm . '_Clacod'];
                        if (trim($codice) != "") {
                            $Anacla_rec = $this->praLib->getAnacla($codice);
                            if ($Anacla_rec) {
                                $this->Dettaglio($Anacla_rec['ROWID']);
                            }
                            Out::valore($this->nameForm . '_Clacod', $codice);
                        }
                        break;
                    case $this->nameForm . '_ANACLA[CLASPO]':
                        $codice = $_POST[$this->nameForm . '_ANACLA']['CLASPO'];
                        if ($codice) {
                            $this->DecodAnatsp($codice);
                        }
                        break;
                    case $this->nameForm . '_estensione':
                        if ($_POST[$this->nameForm . '_estensione'] != '') {
                            $posi = strpos($_POST[$this->nameForm . '_ANACLA']['CLAEXT'], '|' . strtolower($_POST[$this->nameForm . '_estensione']) . '|');
                            
                            if ($posi !== false) {
                                $claext = str_replace('|' . strtolower($_POST[$this->nameForm . '_estensione']) . '|', '', $_POST[$this->nameForm . '_ANACLA']['CLAEXT']);
                            } else {
                                $claext = $_POST[$this->nameForm . '_ANACLA']['CLAEXT'] . '|' . strtolower($_POST[$this->nameForm . '_estensione']) . '|';
                            }

                            $this->visualizzaVerificaFirma($claext);
                            Out::valore($this->nameForm . '_ANACLA[CLAEXT]', $claext);
                            Out::valore($this->nameForm . '_estensione', '');
                            Out::setFocus('', $this->nameForm . '_estensione');
                        }
                        break;
                }
                break;
            case "returnAnatsp":
                $this->DecodAnatsp($_POST["retKey"], "rowid");
                break;
            case "returnAnacla":
                $this->DecodAnacla($_POST["retKey"], "rowid");
                break;
        }
    }

    public function close() {
        $this->gridFilters = App::$utente->removeKey($this->nameForm . '_gridFilters');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function CreaSql() {
        // Imposto il filtro di ricerca
        $sql = "SELECT
                   ANACLA.ROWID,
                   CLACOD,
                   CLADES,
                   TSPDES,
                   CLAPDR,
                   CLAEXT,
                   CLATIP
                FROM
                   ANACLA
                LEFT OUTER JOIN
                  ANATSP
                ON
                  ANACLA.CLASPO=ANATSP.TSPCOD
                WHERE
                  ANACLA.ROWID = ANACLA.ROWID";

        if ($_POST[$this->nameForm . '_Clacod'] != "") {
            $sql .= " AND CLACOD = '" . $_POST[$this->nameForm . '_Clacod'] . "'";
        }
        if ($_POST[$this->nameForm . '_Clades'] != "") {
            $sql .= " AND " . $this->PRAM_DB->strLower('CLADES') . " LIKE '%" . addslashes(strtolower($_POST[$this->nameForm . '_Clades'])) . "%'";
        }


        if ($this->gridFilters) {
            foreach ($this->gridFilters as $key => $value) {
                switch ($key) {
                    case 'CLACOD':
                        $sql .= " AND CLACOD = '$value'";
                        break;
                    case 'CLADES':
                        $sql .= " AND " . $this->PRAM_DB->strLower('CLADES') . " LIKE '%" . addslashes(strtolower($value)) . "%'";
                        break;
                    case 'TSPDES':
                        $sql .= " AND CLASPO = '$value'";
                        break;
                    case 'CLAPDR':
                        $sql .= " AND CLAPDR = '$value'";
                        break;
                }
            }
        }
        return $sql;
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridClassificazioni);
        TableView::clearGrid($this->gridClassificazioni);
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Clacod');
        $this->gridFilters = array();
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
    }

    public function Dettaglio($Indice) {
        $Anacla_rec = $this->praLib->GetAnacla($Indice, 'rowid');
        $open_Info = 'Oggetto: ' . $Anacla_rec['CLACOD'] . " " . $Anacla_rec['CLADES'];
        $this->openRecord($this->PRAM_DB, 'ANACLA', $open_Info);
        $this->Nascondi();
        Out::valori($Anacla_rec, $this->nameForm . '_ANACLA');
        $this->DecodAnatsp($Anacla_rec['CLASPO']);
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::setFocus('', $this->nameForm . '_ANACLA[CLACOD]');
        TableView::disableEvents($this->gridClassificazioni);
        $this->visualizzaVerificaFirma($Anacla_rec['CLAEXT']);
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_ANACLA[CLATIP]', 1, "", "1", "");
        Out::select($this->nameForm . '_ANACLA[CLATIP]', 1, "00", "0", "Nulla Osta");
        Out::select($this->nameForm . '_ANACLA[CLATIP]', 1, "01", "0", "Domanda per Nulla Osta");
        Out::select($this->nameForm . '_ANACLA[CLATIP]', 1, "AL", "0", "Allegato");

        /*
         * Crea Combo Filtro Sportello
         */
        $anatsp_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANATSP", true);
        TableView::tableSetFilterSelect($this->gridClassificazioni, "TSPDES", 1, "", "0", "");
        foreach ($anatsp_tab as $anatsp_rec) {
            TableView::tableSetFilterSelect($this->gridClassificazioni, "TSPDES", 1, $anatsp_rec['TSPCOD'], '0', $anatsp_rec['TSPDES']);
        }

        /*
         * Crea Combo Filtro Codice Padre
         */
        $anacla_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANACLA", true);
        TableView::tableSetFilterSelect($this->gridClassificazioni, "CLAPDR", 1, "", "0", "");
        foreach ($anacla_tab as $anacla_rec) {
            TableView::tableSetFilterSelect($this->gridClassificazioni, "CLAPDR", 1, $anacla_rec['CLACOD'], '0', $anacla_rec['CLACOD']);
        }
    }

    function DecodAnatsp($Codice, $tipoRic = 'codice') {
        $anatsp_rec = $this->praLib->GetAnatsp($Codice, $tipoRic);
        if ($anatsp_rec) {
            Out::valore($this->nameForm . '_ANACLA[CLASPO]', $anatsp_rec['TSPCOD']);
            Out::valore($this->nameForm . '_DESC_SPORTELLO', $anatsp_rec['TSPDES']);
        } else {
            Out::valore($this->nameForm . '_ANACLA[CLASPO]', "");
            Out::valore($this->nameForm . '_DESC_SPORTELLO', "");
        }
    }

    function DecodAnacla($Codice, $tipoRic = 'codice') {
        $anacla_rec = $this->praLib->GetAnacla($Codice, $tipoRic);
        if ($anacla_rec) {
            Out::valore($this->nameForm . '_ANACLA[CLAPDR]', $anacla_rec['CLACOD']);
        } else {
            Out::valore($this->nameForm . '_ANACLA[CLAPDR]', "");
        }
    }

    private function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CLACOD'] != '') {
            $this->gridFilters['CLACOD'] = $_POST['CLACOD'];
        }
        if ($_POST['CLADES'] != '') {
            $this->gridFilters['CLADES'] = $_POST['CLADES'];
        }
        if ($_POST['TSPDES'] != '') {
            $this->gridFilters['TSPDES'] = $_POST['TSPDES'];
        }
        if ($_POST['CLAPDR'] != '') {
            $this->gridFilters['CLAPDR'] = $_POST['CLAPDR'];
        }
    }

    private function visualizzaVerificaFirma($ext) {
        if (strpos($ext, 'p7m') !== false) {
            Out::show($this->nameForm . '_divValidazioneFirma');
        } else {
            Out::hide($this->nameForm . '_divValidazioneFirma');
        }
    }

}
