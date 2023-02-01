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

function basUo() {
    $basUo = new basUo();
    $basUo->parseEvent();
    return;
}

class basUo extends itaModel {

    public $COMUNI_DB;
    public $basLib;
    public $nameForm = "basUo";
    public $divGes = "basUo_divGestione";
    public $divRis = "basUo_divRisultato";
    public $divRic = "basUo_divRicerca";
    public $gridUo = "basUo_gridUo";
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
                    case $this->gridUo:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                break;
            case 'exportTableToExcel':
                $this->setGridFilters();
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridUo,
                                array(
                                    'sqlDB' => $this->COMUNI_DB,
                                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->exportXLS('', 'uo.xls');
                break;
            case 'printTableToHTML':
                $this->setGridFilters();
                $utiEnte = new utiEnte();
                $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql() . ' ORDER BY DES_OU', "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->COMUNI_DB, 'basUo', $parameters);
                break;
            case 'onClickTablePager':
                TableView::clearGrid($this->gridUo);
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
                        TableView::clearGrid($this->gridUo);
                        $this->setGridFilters();
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridUo,
                                        array(
                                            'sqlDB' => $this->COMUNI_DB,
                                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridUo]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('DES_OU');
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
                            TableView::enableEvents($this->gridUo);
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
                        $this->VuotaUo();
                        $contents = file($sourceFile);
                        $c = 0;
                        $t0 = time();
                        foreach ($contents as $riga) {
                            $c++;
                            $dati = array();
                            $dati = explode(chr(9), $riga);
                            $Uo_rec = array();
                            $Uo_rec['COD_OU'] = $dati[0] == 'null' ? '' : trim($dati[0]);
                            $Uo_rec['COD_AOO'] = $dati[1] == 'null' ? '' : trim($dati[1]);
                            $Uo_rec['DES_OU'] = $dati[2] == 'null' ? '' : trim($dati[2]);
                            $Uo_rec['COMUNE'] = $dati[3] == 'null' ? '' : trim($dati[3]);
                            $Uo_rec['CAP'] = $dati[4] == 'null' ? '' : trim($dati[4]);
                            $Uo_rec['PROVINCIA'] = $dati[5] == 'null' ? '' : trim($dati[5]);
                            $Uo_rec['REGIONE'] = $dati[6] == 'null' ? '' : trim($dati[6]);
                            $Uo_rec['INDIRIZZO'] = $dati[7] == 'null' ? '' : trim($dati[7]);
                            $Uo_rec['TEL'] = $dati[8] == 'null' ? '' : trim($dati[8]);
                            $Uo_rec['NOME_RESP'] = $dati[9] == 'null' ? '' : trim($dati[9]);
                            $Uo_rec['COGNOME_RESP'] = $dati[10] == 'null' ? '' : trim($dati[10]);
                            $Uo_rec['MAIL_RESP'] = $dati[11] == 'null' ? '' : trim($dati[11]);
                            $Uo_rec['TEL_RESP'] = $dati[12] == 'null' ? '' : trim($dati[12]);
                            $Uo_rec['COD_AMM'] = $dati[13] == 'null' ? '' : trim($dati[13]);
                            $Uo_rec['COD_OU_PADRE'] = $dati[14] == 'null' ? '' : trim($dati[14]);
                            $Uo_rec['FAX'] = $dati[15] == 'null' ? '' : trim($dati[15]);
                            $Uo_rec['COD_UNI_OU'] = $dati[16] == 'null' ? '' : trim($dati[16]);
                            $Uo_rec['MAIL1'] = $dati[17] == 'null' ? '' : trim($dati[17]);
                            $Uo_rec['TIPO_MAIL1'] = $dati[18] == 'null' ? '' : trim($dati[18]);
                            $Uo_rec['MAIL2'] = $dati[19] == 'null' ? '' : trim($dati[19]);
                            $Uo_rec['TIPO_MAIL2'] = $dati[20] == 'null' ? '' : trim($dati[20]);
                            $Uo_rec['MAIL3'] = $dati[21] == 'null' ? '' : trim($dati[21]);
                            $Uo_rec['TIPO_MAIL3'] = trim($dati[22]) == 'null' ? '' : trim($dati[22]);
                            $this->insertRecord($this->COMUNI_DB, 'UO', $Uo_rec, 'importazione ipa');
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
                        TableView::enableEvents($this->gridUo);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'returnUpload':
                if ($_POST['file'] != 'ou.txt') {
                    Out::msgStop("Attenzione!", "Il file non corrisponde al tracciato delle UO.\n\r Caricare il file ou.txt");
                    break;
                }
                $this->file = $_POST['uploadedFile'];
                if (file_exists($this->file)) {
                    Out::msgQuestion("IMPORTAZIONE", "File caricato. Vuoi aggiornare il database delle UO?\n\rTutti i record presenti saranno sostituiti.", array(
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
        Out::valore($this->nameForm . '_UO[ROWID]', '');
        TableView::disableEvents($this->gridUo);
        TableView::clearGrid($this->gridUo);
    }

    function Nascondi() {
        Out::hide($this->nameForm . '_Importa');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Torna');
    }

    function CreaSql() {
        $sql = "SELECT * FROM UO WHERE COD_OU = COD_OU";
        if ($_POST[$this->nameForm . '_Codice'] != "") {
            $sql .= " AND COD_OU = '" . $_POST[$this->nameForm . '_Codice'] . "'";
        }
        if ($_POST[$this->nameForm . '_Descrizione'] != "") {
            $sql .= " AND (".$this->COMUNI_DB->strLower('DES_OU')." LIKE '%" . addslashes($_POST[$this->nameForm . '_Descrizione']) . "%' OR ".$this->COMUNI_DB->strUpper('DES_OU')." LIKE '%" . addslashes($_POST[$this->nameForm . '_Descrizione']) . "%')";
           // $sql .= " AND (LOWER(DES_OU) LIKE '%" . addslashes($_POST[$this->nameForm . '_Descrizione']) . "%' OR UPPER(DES_OU) LIKE '%" . addslashes($_POST[$this->nameForm . '_Descrizione']) . "%')";
        }
        if ($this->gridFilters) {
            foreach ($this->gridFilters as $key => $value) {
                if ($key == 'COD_OU' || $key == 'COD_AOO' || $key == 'DES_OU' || $key == 'COMUNE' || $key == 'PROVINCIA' || $key == 'MAIL1') {
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
        $uo_rec = $this->basLib->getUo($indice, 'rowid');
        $open_Info = 'Oggetto: ' . $uo_rec['COD_OU'] . " " . $uo_rec['DES_OU'];
        $this->openRecord($this->COMUNI_DB, 'UO', $open_Info);
        $this->Nascondi();
        Out::valori($uo_rec, $this->nameForm . '_UO');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
//        Out::block($this->nameForm . "_divGestione");

        TableView::disableEvents($this->gridUo);
    }

    function VuotaUo() {
        $sql = "TRUNCATE UO";
        ItaDB::DBSQLExec($this->COMUNI_DB, $sql);
    }

    public function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['COD_OU'] != '') {
            $this->gridFilters['COD_OU'] = $_POST['COD_OU'];
        }
        if ($_POST['COD_AOO'] != '') {
            $this->gridFilters['COD_AOO'] = $_POST['COD_AOO'];
        }
        if ($_POST['DES_OU'] != '') {
            $this->gridFilters['DES_OU'] = $_POST['DES_OU'];
        }
        if ($_POST['COMUNE'] != '') {
            $this->gridFilters['COMUNE'] = $_POST['COMUNE'];
        }
        if ($_POST['PROVINCIA'] != '') {
            $this->gridFilters['PROVINCIA'] = $_POST['PROVINCIA'];
        }
        if ($_POST['MAIL1'] != '') {
            $this->gridFilters['MAIL1'] = $_POST['MAIL1'];
        }
    }

}

?>