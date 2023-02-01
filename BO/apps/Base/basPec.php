<?php

/**
 *
 * Archivio Pec
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    01.07.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Base/basLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function basPec() {
    $basPec = new basPec();
    $basPec->parseEvent();
    return;
}

class basPec extends itaModel {

    public $COMUNI_DB;
    public $basLib;
    public $nameForm = "basPec";
    public $divGes = "basPec_divGestione";
    public $divRis = "basPec_divRisultato";
    public $divRic = "basPec_divRicerca";
    public $gridPec = "basPec_gridPec";
    public $file;
    public $gridFilters = array();

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->basLib = new basLib();
            $this->COMUNI_DB = $this->basLib->getCOMUNIDB();
            $this->file = App::$utente->getKey($this->nameForm . '_file');
            $this->gridFilters = App::$utente->getKey($this->nameForm . '_gridFilters');
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
                    case $this->gridPec:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                break;
            case 'exportTableToExcel':
                $this->setGridFilters();
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridPec,
                                array(
                                    'sqlDB' => $this->COMUNI_DB,
                                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->exportXLS('', 'pec.xls');
                break;
            case 'printTableToHTML':
                $this->setGridFilters();
                $utiEnte = new utiEnte();
                $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql() . ' ORDER BY DESCRIZIONE', "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->COMUNI_DB, 'basPec', $parameters);
                break;
            case 'onClickTablePager':
                TableView::clearGrid($this->gridPec);
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
                        TableView::clearGrid($this->gridPec);
                        $this->setGridFilters();
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridPec,
                                        array(
                                            'sqlDB' => $this->COMUNI_DB,
                                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridPec]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('DESCRIZIONE');
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
                            TableView::enableEvents($this->gridPec);
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Importa':
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUpload";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();

                        break;

                    case $this->nameForm . '_ConfermaImport':
                        if ($this->file != '') {
                            $sourceFile = $this->file;
                        } else {
                            Out::msgStop('Attenzione!', 'Selezionare il File.');
                            break;
                        }
                        $this->VuotaPec();
                        $contents = file($sourceFile);
                        $c = 0;
                        $t0 = time();
                        foreach ($contents as $riga) {
                            $c++;
                            if ($c == 1) {
                                continue;   //record di testa, non inserisco
                            }
                            $dati = array();
                            $dati = explode(chr(9), $riga);
                            $Pec_rec = array();
                            $Pec_rec['COD_AMM'] = $dati[0] == 'null' ? '' : trim($dati[0]);
                            $Pec_rec['DESCRIZIONE'] = $dati[1] == 'null' ? '' : trim($dati[1]);
                            $Pec_rec['TIPO'] = $dati[2] == 'null' ? '' : trim($dati[2]);
                            $Pec_rec['TIPOLOGIA_AMMINISTRAZIONE'] = $dati[3] == 'null' ? '' : trim($dati[3]);
                            $Pec_rec['REGIONE'] = $dati[4] == 'null' ? '' : trim($dati[4]);
                            $Pec_rec['PROVINCIA'] = $dati[5] == 'null' ? '' : trim($dati[5]);
                            $Pec_rec['COMUNE'] = $dati[6] == 'null' ? '' : trim($dati[6]);
                            $Pec_rec['MAIL'] = $dati[7] == 'null' ? '' : trim($dati[7]);
                            $Pec_rec['TIPO_MAIL'] = trim($dati[8]) == 'null' ? '' : trim($dati[8]);
                            $this->insertRecord($this->COMUNI_DB, 'PEC', $Pec_rec, 'importazione ipa');
                        }
                        $t1 = time();
//                        Out::msgInfo('Fatto!', 'Importati ' . $c . ' record in ' . ($t1 - $t0) . ' secondi.');
                        Out::msgInfo('Fatto!', 'Importati con successo ' . $c . ' record in ' . ($t1 - $t0) . ' secondi.');
                        break;
                    case $this->nameForm . '_Torna':
                        Out::hide($this->divGes);
                        Out::hide($this->divRic);
                        Out::show($this->divRis);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_Nuovo');
                        TableView::enableEvents($this->gridPec);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'returnUpload':
                if ($_POST['file'] != 'pec.txt') {
                    Out::msgStop("Attenzione!", "Il file non corrisponde al tracciato delle Pec.\n\r Caricare il file pec.txt");
                    break;
                }
                $this->file = $_POST['uploadedFile'];
                if (file_exists($this->file)) {
                    Out::msgQuestion("IMPORTAZIONE", "File caricato. Vuoi aggiornare il database delle Pec?\n\rTutti i record presenti saranno sostituiti.", array(
                        'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaImport', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaImport', 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                    );
                } else {
                    Out::msgStop("Errore", "Procedura di importazione interrotta per mancanza del file.");
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
    }

    function OpenRicerca() {
        Out::show($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '_Importa');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Codice');
    }

    function AzzeraVariabili() {
        $this->gridFilters = array();
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::valore($this->nameForm . '_UO[ROWID]', '');
        TableView::disableEvents($this->gridPec);
        TableView::clearGrid($this->gridPec);
        TableView::clearToolbar($this->gridPec);
    }

    function Nascondi() {
        Out::hide($this->nameForm . '_Importa');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Torna');
    }

    function CreaSql() {
        $sql = "SELECT * FROM PEC WHERE COD_AMM = COD_AMM";
        if ($_POST[$this->nameForm . '_Codice'] != "") {
            $sql .= " AND COD_AMM = '" . $_POST[$this->nameForm . '_Codice'] . "'";
        }
        if ($_POST[$this->nameForm . '_Descrizione'] != "") {
            //$sql .= " AND (LOWER(DESCRIZIONE) LIKE '%" . addslashes($_POST[$this->nameForm . '_Descrizione']) . "%' OR UPPER(DESCRIZIONE) LIKE '%" . addslashes($_POST[$this->nameForm . '_Descrizione']) . "%')";
            $sql .= " AND (".$this->COMUNI_DB->strLower('DESCRIZIONE')." LIKE '%" . addslashes($_POST[$this->nameForm . '_Descrizione']) . "%' OR ".$this->COMUNI_DB->strUpper('DESCRIZIONE')." LIKE '%" . addslashes($_POST[$this->nameForm . '_Descrizione']) . "%')";
        }
        if ($this->gridFilters) {
            foreach ($this->gridFilters as $key => $value) {
                if ($key == 'COD_AMM' || $key == 'DESCRIZIONE' || $key == 'TIPOLOGIA_AMMINISTRAZIONE' || $key == 'COMUNE' || $key == 'PROVINCIA' || $key == 'MAIL') {
                    $value = str_replace("'", "\'", $value);
                    $sql.= " AND ".$this->COMUNI_DB->strUpper($key)." LIKE '%" . strtoupper($value) . "%' ";
                } else {
                    if (is_numeric($value)) {
                        $sql.= " AND $key = $value";
                    } else {
                        Out::msgInfo("Attenzione", "Sono accettati solo valori numerici in questo campo.");
                    }
                }
            }
        }
        App::log($sql);
        return $sql;
    }

    function Dettaglio($indice) {
        $pec_rec = $this->basLib->getPec($indice, 'rowid');
        $open_Info = 'Oggetto: ' . $pec_rec['COD_OU'] . " " . $pec_rec['DESCRIZIONE'];
        $this->openRecord($this->COMUNI_DB, 'PEC', $open_Info);
        $this->Nascondi();
        Out::valori($pec_rec, $this->nameForm . '_PEC');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
//        Out::block($this->nameForm . "_divGestione");

        TableView::disableEvents($this->gridPec);
    }

    function VuotaPec() {
        $sql = "TRUNCATE PEC";
        ItaDB::DBSQLExec($this->COMUNI_DB, $sql);
    }

    public function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['COD_AMM'] != '') {
            $this->gridFilters['COD_AMM'] = $_POST['COD_AMM'];
        }
        if ($_POST['DESCRIZIONE'] != '') {
            $this->gridFilters['DESCRIZIONE'] = $_POST['DESCRIZIONE'];
        }
        if ($_POST['TIPOLOGIA_AMMINISTRAZIONE'] != '') {
            $this->gridFilters['TIPOLOGIA_AMMINISTRAZIONE'] = $_POST['TIPOLOGIA_AMMINISTRAZIONE'];
        }
        if ($_POST['COMUNE'] != '') {
            $this->gridFilters['COMUNE'] = $_POST['COMUNE'];
        }
        if ($_POST['PROVINCIA'] != '') {
            $this->gridFilters['PROVINCIA'] = $_POST['PROVINCIA'];
        }
        if ($_POST['MAIL'] != '') {
            $this->gridFilters['MAIL'] = $_POST['MAIL'];
        }
    }

}

?>