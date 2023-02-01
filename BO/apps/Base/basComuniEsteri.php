<?php

/**
 *
 * Archivio Comuni
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    28.08.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once './apps/Base/basLib.class.php';
include_once './apps/Base/basRic.class.php';
include_once './apps/Utility/utiEnte.class.php';

function basComuniEsteri() {
    $basComuniEsteri = new basComuniEsteri();
    $basComuniEsteri->parseEvent();
    return;
}

class basComuniEsteri extends itaModel {

    public $COMUNI_DB;
    public $basLib;
    public $nameForm = "basComuniEsteri";
    public $divGes = "basComuniEsteri_divGestione";
    public $divRis = "basComuniEsteri_divRisultato";
    public $divRic = "basComuniEsteri_divRicerca";
    public $gridComune = "basComuniEsteri_gridComune";
    private $gridFilters = array();
    public $file;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->basLib = new basLib();
            $this->COMUNI_DB = $this->basLib->getCOMUNIDB();
            $this->gridFilters = App::$utente->getKey($this->nameForm . '_gridFilters');
            $this->file = App::$utente->getKey($this->nameForm . '_file');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_file', $this->file);
            App::$utente->setKey($this->nameForm . '_gridFilters', $this->gridFilters);
        }
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
                    case $this->gridComune:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridComune:
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
                $this->setGridFilters();
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridComune, array(
                    'sqlDB' => $this->COMUNI_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('COMUNE');
                $ita_grid01->exportXLS('', 'comuni.xls');
                break;
            case 'printTableToHTML':
                $utiEnte = new utiEnte();
                $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql() . ' ORDER BY COMUNE', "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->COMUNI_DB, 'basComuni', $parameters);
                break;
            case 'onClickTablePager':
                TableView::clearGrid($this->gridComune);
                $this->setGridFilters();
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->COMUNI_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $ita_grid01->getDataPage('json');
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        TableView::clearGrid($this->gridComune);
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridComune, array(
                            'sqlDB' => $this->COMUNI_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridComune]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('COMUNE');
                        $ita_grid01->setSortOrder('asc');
                        if (!$ita_grid01->getDataPage('json')) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo il risultato
                            Out::hide($this->divGes);
                            Out::hide($this->divRic);
                            Out::show($this->divRis);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridComune);
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_COMUNI[COMUNE]');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $naz = $_POST[$this->nameForm . '_COMUNI']['CODICEONU'];
                        $codice = $_POST[$this->nameForm . '_COMUNI']['COMUNE'];
                        $comune_rec = $this->basLib->getComuniEsteri($naz, $codice);
                        if (!$comune_rec) {
                            $comune_rec = $_POST[$this->nameForm . '_COMUNI'];
                            $insert_Info = 'Aggiunto Comune Estero: ' . $comune_rec['COMUNE'];
                            if ($this->insertRecord($this->COMUNI_DB, 'COMUNIESTERI', $comune_rec, $insert_Info, 'ROW_ID')) {
                                $this->OpenRicerca();
                            }
                        } else {
                            Out::msgInfo("Comune già  presente", "Inserire un nuovo comune per la nazione $naz.");
                            Out::setFocus('', $this->nameForm . '_COMUNI[COMUNE]');
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $comune_rec = $_POST[$this->nameForm . '_COMUNI'];
                        $update_Info = 'Aggiornato Comune Estero: ' . $comune_rec['COMUNE'];
                        if ($this->updateRecord($this->COMUNI_DB, 'COMUNIESTERI', $comune_rec, $update_Info, 'ROW_ID')) {
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
                        $comune_rec = $_POST[$this->nameForm . '_COMUNI'];
                        $delete_Info = 'Cancellazione Comune Estero: ' . $comune_rec['COMUNE'];
                        if ($this->deleteRecord($this->COMUNI_DB, 'COMUNIESTERI', $comune_rec['ROW_ID'], $delete_Info, 'ROW_ID')) {
                            $this->OpenRicerca();
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
                        TableView::enableEvents($this->gridComune);
                        break;
                    case $this->nameForm . '_Naz_butt':
                        basRic::basRicNazioni($this->nameForm, '', 'returnNazRicerca');
                        break;
                    case $this->nameForm . '_COMUNI[CODICEONU]_butt':
                        basRic::basRicNazioni($this->nameForm, '', 'returnNazDettaglio');
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'returnNazRicerca':
                $Naz_rec = $this->basLib->getNazioni($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_Naz', $Naz_rec['CODICEONU']);
                break;
            case 'returnNazDettaglio':
                $Naz_rec = $this->basLib->getNazioni($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_COMUNI[CODICEONU]', $Naz_rec['CODICEONU']);
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    function OpenRicerca() {
        Out::show($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '_Importa');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Codice');
    }

    function AzzeraVariabili() {
        $this->gridFilters = array();
        Out::clearFields($this->nameForm, '');
        Out::valore($this->nameForm . '_COMUNI[ROW_ID]', '');
        TableView::disableEvents($this->gridComune);
        TableView::clearGrid($this->gridComune);
        TableView::clearToolbar($this->gridComune);
    }

    function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_Importa');
    }

    function CreaSql() {
        $sql = "SELECT * FROM COMUNIESTERI WHERE 1";
        if ($_POST[$this->nameForm . '_Naz'] != "") {
            $sql .= " AND CODICEONU = '" . addslashes($_POST[$this->nameForm . '_Naz']) . "'";
        }
        if ($_POST[$this->nameForm . '_Codice'] != "") {
            $sql .= " AND COMUNE LIKE '%" . addslashes(strtoupper($_POST[$this->nameForm . '_Codice'])) . "%'";
        }
        $Msg_Err = false;
        if ($this->gridFilters) {
            foreach ($this->gridFilters as $key => $value) {
                switch ($key) {
                    case 'COMUNE':
                    case 'NASCIT':
                    case 'CISTAT':
                    case 'COAVPO':
                    case 'PROVIN':
                    case 'REGIONE':
                        $value = str_replace("'", "\'", $value);
                        $sql .= " AND " . $this->COMUNI_DB->strUpper($key) . " LIKE '%" . strtoupper($value) . "%' ";
                        break;
                    default:
                        if (is_numeric($value)) {
                            $sql .= " AND $key = $value";
                        } else {
                            if ($Msg_Err == false) {
                                $Msg_Err = true;
                                Out::msgInfo("Attenzione", "Sono accettati solo valori numerici.");
                            }
                        }
                        break;
                }
            }
        }
        return $sql;
    }

    function Dettaglio($indice) {
        $comune_rec = $this->basLib->getComuniEsteri('', $indice, 'rowid');
        $open_Info = 'Apertura Comune Estero: ' . $comune_rec['COMUNE'];
        $this->openRecord($this->COMUNI_DB, 'COMUNIESTERI', $open_Info);
        $this->Nascondi();
        Out::valori($comune_rec, $this->nameForm . '_COMUNI');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        Out::setFocus('', $this->nameForm . '_COMUNI[COMUNE]');
        TableView::disableEvents($this->gridComune);
    }

    private function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['COMUNE'] != '') {
            $this->gridFilters['COMUNE'] = $_POST['COMUNE'];
        }
        if ($_POST['NASCIT'] != '') {
            $this->gridFilters['NASCIT'] = $_POST['NASCIT'];
        }
        if ($_POST['CISTAT'] != '') {
            $this->gridFilters['CISTAT'] = $_POST['CISTAT'];
        }
        if ($_POST['COAVPO'] != '') {
            $this->gridFilters['COAVPO'] = $_POST['COAVPO'];
        }
        if ($_POST['PROVIN'] != '') {
            $this->gridFilters['PROVIN'] = $_POST['PROVIN'];
        }
        if ($_POST['REGIONE'] != '') {
            $this->gridFilters['REGIONE'] = $_POST['REGIONE'];
        }
    }

}
