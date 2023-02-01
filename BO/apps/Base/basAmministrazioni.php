<?php

/**
 *
 * Archivio Stati
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    19.09.2014
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

function basAmministrazioni() {
    $basAmministrazioni = new basAmministrazioni();
    $basAmministrazioni->parseEvent();
    return;
}

class basAmministrazioni extends itaModel {

    public $COMUNI_DB;
    public $basLib;
    public $nameForm = "basAmministrazioni";
    public $divGes = "basAmministrazioni_divGestione";
    public $divRis = "basAmministrazioni_divRisultato";
    public $divRic = "basAmministrazioni_divRicerca";
    public $gridAmm = "basAmministrazioni_gridAmm";
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
                    case $this->gridAmm:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                break;
            case 'exportTableToExcel':
                $this->setGridFilters();
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridAmm,
                                array(
                                    'sqlDB' => $this->COMUNI_DB,
                                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->exportXLS('', 'amministrazioni.xls');
                break;
            case 'printTableToHTML':
                TableView::clearGrid($this->gridAmm);
                $this->setGridFilters();
                $utiEnte = new utiEnte();
                $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql() . ' ORDER BY DES_AMM', "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->COMUNI_DB, 'basAmministrazioni', $parameters);
                break;
            case 'onClickTablePager':
                TableView::clearGrid($this->gridAmm);
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
                        TableView::clearGrid($this->gridAmm);
                        $this->setGridFilters();
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridAmm,
                                        array(
                                            'sqlDB' => $this->COMUNI_DB,
                                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridAmm]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('DES_AMM');
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
                            TableView::enableEvents($this->gridAmm);
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
                        $this->VuotaAmministrazioni();
                        $contents = file($sourceFile);
                        $c = 0;
                        $t0 = time();
                        foreach ($contents as $riga) {
                            $c++;
                            $dati = array();
                            $dati = explode(chr(9), $riga);
                            $tipo = trim($dati[11]);
                            if (1 != 1) { // Temporaneamente bloccato
                                if ($tipo != "Comuni"
                                        && $tipo != "Comunita' Montane"
                                        && $tipo != "Regioni e Province Autonome"
                                        && $tipo != "Regioni e Province Autonome"
                                        && $tipo != "Province"
                                        && $tipo != "Unioni di Comuni"
                                        && $tipo != "Altre Amministrazioni Locali"
                                        && $tipo != "Aziende Sanitarie Locali"
                                        && $tipo != "Comuni e loro Consorzi e Associazioni"
                                        && $tipo != "Camere di Commercio, Industria, Artigianato e Agricoltura e Unioni Regionali") {
                                    continue;
                                }
                            }
                            $Amm_rec = array();
                            $Amm_rec['COD_AMM'] = $dati[0] == 'null' ? '' : trim($dati[0]);
                            $Amm_rec['DES_AMM'] = $dati[1] == 'null' ? '' : trim($dati[1]);
                            $Amm_rec['COMUNE'] = $dati[2] == 'null' ? '' : trim($dati[2]);
                            $Amm_rec['NOME_RESP'] = $dati[3] == 'null' ? '' : trim($dati[3]);
                            $Amm_rec['COGNOME_RESP'] = $dati[4] == 'null' ? '' : trim($dati[4]);
                            $Amm_rec['CAP'] = $dati[5] == 'null' ? '' : trim($dati[5]);
                            $Amm_rec['PROVINCIA'] = $dati[6] == 'null' ? '' : trim($dati[6]);
                            $Amm_rec['REGIONE'] = $dati[7] == 'null' ? '' : trim($dati[7]);
                            $Amm_rec['SITO_ISTITUZIONALE'] = $dati[8] == 'null' ? '' : trim($dati[8]);
                            $Amm_rec['INDIRIZZO'] = $dati[9] == 'null' ? '' : trim($dati[9]);
                            $Amm_rec['TITOLO_RESP'] = $dati[10] == 'null' ? '' : trim($dati[10]);
                            $Amm_rec['TIPOLOGIA_ISTAT'] = $dati[11] == 'null' ? '' : trim($dati[11]);
                            $Amm_rec['TIPOLOGIA_AMMINISTRAZIONE'] = $dati[12] == 'null' ? '' : trim($dati[12]);
                            $Amm_rec['ACRONIMO'] = $dati[13] == 'null' ? '' : trim($dati[13]);
                            $Amm_rec['CF_VALIDATO'] = $dati[14] == 'null' ? '' : trim($dati[14]);
                            $Amm_rec['CF'] = $dati[15] == 'null' ? '' : trim($dati[15]);
                            $Amm_rec['MAIL1'] = $dati[16] == 'null' ? '' : trim($dati[16]);
                            $Amm_rec['TIPO_MAIL1'] = $dati[17] == 'null' ? '' : trim($dati[17]);
                            $Amm_rec['MAIL2'] = $dati[18] == 'null' ? '' : trim($dati[18]);
                            $Amm_rec['TIPO_MAIL2'] = $dati[19] == 'null' ? '' : trim($dati[19]);
                            $Amm_rec['MAIL3'] = $dati[20] == 'null' ? '' : trim($dati[20]);
                            $Amm_rec['TIPO_MAIL3'] = $dati[21] == 'null' ? '' : trim($dati[21]);
                            $Amm_rec['MAIL4'] = $dati[22] == 'null' ? '' : trim($dati[22]);
                            $Amm_rec['TIPO_MAIL4'] = $dati[23] == 'null' ? '' : trim($dati[23]);
                            $Amm_rec['MAIL5'] = $dati[24] == 'null' ? '' : trim($dati[24]);
                            $Amm_rec['TIPO_MAIL5'] = $dati[25] == 'null' ? '' : trim($dati[25]);
                            $Amm_rec['URL_FACEBOOK'] = $dati[26] == 'null' ? '' : trim($dati[26]);
                            $Amm_rec['URL_TWITTER'] = $dati[27] == 'null' ? '' : trim($dati[27]);
                            $Amm_rec['URL_GOOGLEPLUS'] = $dati[28] == 'null' ? '' : trim($dati[28]);
                            $Amm_rec['URL_YOUTUBE'] = $dati[29] == 'null' ? '' : trim($dati[29]);
                            $Amm_rec['LIV_ACCESSIBILI'] = trim($dati[30]) == 'null' ? '' : trim($dati[30]);
                            $this->insertRecord($this->COMUNI_DB, 'AMMINISTRAZIONI', $Amm_rec, 'importazione ipa');
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
                        Out::setFocus('', $this->nameForm . '_Nuovo');
                        TableView::enableEvents($this->gridAmm);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'returnUpload':
                if ($_POST['file'] != 'amministrazioni.txt') {
                    Out::msgStop("Attenzione!", "Il file non corrisponde al tracciato delle amministrazioni.\n\r Caricare il file amministrazioni.txt");
                    break;
                }
                $this->file = $_POST['uploadedFile'];
                if (file_exists($this->file)) {
                    Out::msgQuestion("IMPORTAZIONE", "File caricato. Vuoi aggiornare il database delle Amministrazioni?\n\rTutti i record presenti saranno sostituiti.", array(
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
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::valore($this->nameForm . '_AMMINISTRAZIONI[ROWID]', '');
        TableView::disableEvents($this->gridAmm);
        TableView::clearGrid($this->gridAmm);
        TableView::clearToolbar($this->gridAmm);
    }

    function Nascondi() {
        Out::hide($this->nameForm . '_Importa');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Torna');
    }

    function CreaSql() {
        $sql = "SELECT * FROM AMMINISTRAZIONI WHERE COD_AMM=COD_AMM";
        if ($_POST[$this->nameForm . '_Codice'] != "") {
            $sql .= " AND COD_AMM = '" . $_POST[$this->nameForm . '_Codice'] . "'";
        }
        if ($_POST[$this->nameForm . '_Descrizione'] != "") {
            $sql .= " AND (".$this->COMUNI_DB->strLower('DES_AMM')." LIKE '%" . strtolower(addslashes($_POST[$this->nameForm . '_Descrizione'])) . "%' OR ".$this->COMUNI_DB->strUpper('DES_AMM')." LIKE '%" . strtoupper(addslashes($_POST[$this->nameForm . '_Descrizione'])) . "%')";
           // $sql .= " AND (LOWER(DES_AMM) LIKE '%" . strtolower(addslashes($_POST[$this->nameForm . '_Descrizione'])) . "%' OR UPPER(DES_AMM) LIKE '%" . strtoupper(addslashes($_POST[$this->nameForm . '_Descrizione'])) . "%')";
        }
        if ($this->gridFilters) {
            foreach ($this->gridFilters as $key => $value) {
                if ($key == 'COD_AMM' || $key == 'DES_AMM' || $key == 'COMUNE' || $key == 'PROVINCIA' || $key == 'TIPOLOGIA_ISTAT' || $key == 'MAIL1') {
                    $value = str_replace("'", "\'", $value);
                    $sql.= " AND ".$this->COMUNI_DB->strLower($key)." LIKE '%" . strtolower($value) . "%' ";
                   // $sql.= " AND LOWER($key) LIKE '%" . strtolower($value) . "%' ";
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
        $amm_rec = $this->basLib->getAmministrazioni($indice, 'rowid');
        $open_Info = 'Oggetto: ' . $amm_rec['COD_AMM'] . " " . $amm_rec['DES_AMM'];
        $this->openRecord($this->COMUNI_DB, 'AMMINISTRAZIONI', $open_Info);
        $this->Nascondi();
        Out::valori($amm_rec, $this->nameForm . '_AMMINISTRAZIONI');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        TableView::disableEvents($this->gridAmm);
    }

    function VuotaAmministrazioni() {
        $sql = "TRUNCATE AMMINISTRAZIONI";
        ItaDB::DBSQLExec($this->COMUNI_DB, $sql);
    }

    public function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['COD_AMM'] != '') {
            $this->gridFilters['COD_AMM'] = $_POST['COD_AMM'];
        }
        if ($_POST['DES_AMM'] != '') {
            $this->gridFilters['DES_AMM'] = $_POST['DES_AMM'];
        }
        if ($_POST['COMUNE'] != '') {
            $this->gridFilters['COMUNE'] = $_POST['COMUNE'];
        }
        if ($_POST['PROVINCIA'] != '') {
            $this->gridFilters['PROVINCIA'] = $_POST['PROVINCIA'];
        }
        if ($_POST['TIPOLOGIA_ISTAT'] != '') {
            $this->gridFilters['TIPOLOGIA_ISTAT'] = $_POST['TIPOLOGIA_ISTAT'];
        }
        if ($_POST['MAIL1'] != '') {
            $this->gridFilters['MAIL1'] = $_POST['MAIL1'];
        }
    }

}

?>