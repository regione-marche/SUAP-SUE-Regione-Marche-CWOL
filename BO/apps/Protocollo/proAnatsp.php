<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';

function proAnatsp() {
    $proAnatsp = new proAnatsp();
    $proAnatsp->parseEvent();
    return;
}

class proAnatsp extends itaModel {

    public $PROT_DB;
    public $nameForm = "proAnatsp";
    public $divGes = "proAnatsp_divGestione";
    public $divRis = "proAnatsp_divRisultato";
    public $divRic = "proAnatsp_divRicerca";
    public $gridAnatsp = "proAnatsp_gridAnatsp";
    public $proLib;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
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
                $this->CreaCombo();
                $this->OpenRicerca();
                TableView::disableEvents($this->gridAnatsp);
                break;
            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridAnatsp:
                        $Anatsp_rec = ItaDB::DBSQLSelect($this->PROT_DB, "SELECT * FROM ANATSP WHERE ROWID='" . $_POST['rowid'] . "'", false);
                        $this->Dettaglio($Anatsp_rec);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnatsp:
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
                $ita_grid01 = new TableView($this->gridAnatsp, array(
                    'sqlDB' => $this->PROT_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('TSPDES');
                $ita_grid01->exportXLS('', 'Anatsp.xls');
                break;

            case 'onClickTablePager':
                $tableSortOrder = $_POST['sord'];
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PROT_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $ita_grid01->getDataPage('json');
                break;
            case 'printTableToHTML':
                $Anaent_rec = $this->proLib->GetAnaent('2');
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $Anaent_rec['ENTDE1']);
                $itaJR->runSQLReportPDF($this->PROT_DB, 'proAnatsp', $parameters);
//                $where = $sql = "";
//                $trovato = 0;
//                // Importo l'ordinamento del filtro
//                $sql = $this->CreaSql();
//                $sql = $sql . " ORDER BY TSPDES";
//                try {
//                    $casuale = rand(100, 999);
//                    ini_set("include_path", ini_get("include_path") . ":./lib/phpreports/");
//                    include_once("PHPReportMaker.php");
//                    $oRpt = new PHPReportMaker();
//                    $oRpt->setXML("./apps/Protocollo/proAnatsp.xml");
//                    $oRpt->setUser("public");
//                    $oRpt->setPassword("");
//                    $oRpt->setConnection("PROT01");
//                    $oRpt->setDatabaseInterface("odbc");
//                    $oRpt->setSQL($sql);
//                    $oRpt->setDatabase("");
//                    $oRpt->setOutput('./' . App::$utente->getkey('privPath') . '/' . App::$utente->getKey('TOKEN') . "-$casuale-proAnatspEle.html");
//                    $oRpt->run();
//                    $filename = $oRpt->getOutput();
//                    $handle = fopen($filename, "r");
//                    $html = fread($handle, filesize($filename));
//                    fclose($handle);
//                    $urlFile = "http://" . $_SERVER['SERVER_ADDR'] . ":" . $_SERVER['SERVER_PORT'] . App::$utente->getKey('privUrl') . "/" . App::$utente->getKey('TOKEN') . "-$casuale-proAnatspEle.html";
//                    Out::openDocument($urlFile);
//                } catch (Exception $e) {
//                    Out::msgStop("Attenzione!", "Errore di Connessione al DB.");
//                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TornaElenco':
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridAnatsp, array(
                            'sqlDB' => $this->PROT_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridAnatsp]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('TSPDES');
                        if (!$ita_grid01->getDataPage('json')) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo la ricerca
                            Out::hide($this->divGes, '', 0);
                            Out::hide($this->divRic, '', 0);
                            Out::show($this->divRis, '', 0);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridAnatsp);
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        Out::attributo($this->nameForm . '_ANATSP[TSPCOD]', 'readonly', '1');
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->Azzera();
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::valore($this->nameForm . '_ANATSP[ROWID]', '');
                        Out::valore($this->nameForm . '_ANATSP[TSPCOD]', '');
                        Out::valore($this->nameForm . '_ANATSP[TSPDES]', '');
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::setFocus('', $this->nameForm . '_ANATSP[TSPCOD]');
                        break;

                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_ANATSP']['TSPCOD'];
                        if (is_numeric($codice)) {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                        } else {
                            $codice = str_repeat(" ", 4 - strlen(trim($codice))) . trim($codice);
                        }

                        $_POST[$this->nameForm . '_ANATSP']['TSPCOD'] = $codice;
                        $sql = "SELECT TSPCOD FROM ANATSP WHERE TSPCOD='" . $_POST[$this->nameForm . '_ANATSP']['TSPCOD'] . "'";
                        try {   // Effettuo la FIND
                            $Anatsp_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            if (count($Anatsp_tab) == 0) {
                                $Anatsp_rec = $_POST[$this->nameForm . '_ANATSP'];
                                $insert_Info = 'Oggetto: ' . $Anatsp_rec['TSPCOD'] . " " . $Anatsp_rec['TSPDES'];
                                if ($this->insertRecord($this->PROT_DB, 'ANATSP', $Anatsp_rec, $insert_Info)) {
                                    $this->OpenRicerca();
                                }
                            } else {
                                Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                                Out::setFocus('', $this->nameForm . '_ANATSP[TSPCOD]');
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Inserimento su ARCHIVIO TIPOLOGIA SPEDIZIONE.", $e->getMessage());
                        }
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $Anatsp_rec = $_POST[$this->nameForm . '_ANATSP'];
                        $update_Info = 'Oggetto: ' . $Anatsp_rec['TSPCOD'] . " " . $Anatsp_rec['TSPDES'];
                        if ($this->updateRecord($this->PROT_DB, 'ANATSP', $Anatsp_rec, $update_Info)) {
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


                        $Anatsp_rec = $_POST[$this->nameForm . '_ANATSP'];

                        $sql = "SELECT PROCAT FROM ANAPRO WHERE PROTSP = '" . $Anatsp_rec['TSPCOD'] . "'";
                        $Anapro_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                        if ($Anapro_tab != null) {
                            Out::msgStop("Attenzione!", 'Impossibile cancellare la Tipologia perché è assegnata a Protocolli.');
                        } else {
                            try {
                                $delete_Info = 'Oggetto: ' . $Anatsp_rec['TSPCOD'] . " " . $Anatsp_rec['TSPDES'];
                                if ($this->deleteRecord($this->PROT_DB, 'ANATSP', $Anatsp_rec['ROWID'], $delete_Info)) {
                                    $this->OpenRicerca();
                                }
                            } catch (Exception $e) {
                                Out::msgStop("Errore in Cancellazione su ARCHIVIO TIPOLOGIA SPEDIZIONE", $e->getMessage());
                            }
                        }
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Tspcod':
                        $codice = $_POST[$this->nameForm . '_Tspcod'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            } else {
                                $codice = str_repeat(" ", 4 - strlen(trim($codice))) . trim($codice);
                            }

                            Out::valore($this->nameForm . '_Tspcod', $codice);
                            $sql = "SELECT * FROM ANATSP WHERE TSPCOD='$codice'";
                            $Anatsp_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            if (count($Anatsp_tab) == 1) {
                                $this->Dettaglio($Anatsp_tab[0]);
                            }
                        }
                        break;
                    case $this->nameForm . '_ANATSP[TSPCOD]':
                        $codice = $_POST[$this->nameForm . '_ANATSP']['TSPCOD'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            } else {
                                $codice = str_repeat(" ", 4 - strlen(trim($codice))) . trim($codice);
                            }

                            Out::valore($this->nameForm . '_ANATSP[TSPCOD]', $codice);
                        }
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    public function OpenRicerca() {
        Out::hide($this->divRis, '', 0);
        Out::show($this->divRic, '', 200);
        Out::hide($this->divGes, '', 200);
        TableView::disableEvents($this->gridAnatsp);
        TableView::clearGrid($this->gridAnatsp);
        $this->Azzera();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '');
        Out::setFocus('', $this->nameForm . '_Tspcod');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_TornaElenco');
        Out::hide($this->nameForm . '_StampaElenco');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
    }

    public function Azzera() {
        Out::valore($this->nameForm . '_Tspcod', '');
        Out::valore($this->nameForm . '_Tspdes', '');
        Out::valore($this->nameForm . '_ANATSP[TSPCOD]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPDES]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPPES__1]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPTAR__1]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPPES__2]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPTAR__2]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPPES__3]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPTAR__3]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPPES__4]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPTAR__4]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPPES__5]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPTAR__5]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPPES__6]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPTAR__6]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPPES__7]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPTAR__7]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPPES__8]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPTAR__8]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPPES__9]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPTAR__9]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPPES__10]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPTAR__10]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPPES__11]', '');
        Out::valore($this->nameForm . '_ANATSP[TSPTAR__11]', '');
    }

    public function CreaSql() {
        $where = $sql = "";
        // Importo l'ordinamento del filtro
        $sql = "SELECT * FROM ANATSP WHERE TSPCOD=TSPCOD";
        if ($_POST[$this->nameForm . '_Tspcod'] != "") {
            $where .= " AND TSPCOD = '" . $_POST[$this->nameForm . '_Tspcod'] . "'";
        }
        if ($_POST[$this->nameForm . '_Tspdes'] != "") {
            $where .= " AND " . $this->PROT_DB->strUpper('TSPDES') . " LIKE '%" . addslashes(strtoupper($_POST[$this->nameForm . '_Tspdes'])) . "%'";
        }
        $sql .= $where;
        return $sql;
    }

    public function Dettaglio($Anatsp_rec) {
        $open_Info = 'Oggetto: ' . $Anatsp_rec['TSPCOD'] . " " . $Anatsp_rec['TSPDES'];
        $this->openRecord($this->PROT_DB, 'ANATSP', $open_Info);
        $this->Nascondi();
        //$this->Azzera();
        Out::valori($Anatsp_rec, $this->nameForm . '_ANATSP');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_TornaElenco');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->divRic, '', 0);
        Out::hide($this->divRis, '', 0);
        Out::show($this->divGes, '', 0);
        Out::attributo($this->nameForm . '_ANATSP[TSPCOD]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_ANATSP[TSPDES]');
        TableView::disableEvents($this->gridAnatsp);
    }

    public function CreaCombo() {
        foreach (proLib::$GetTipiSpedizione as $keySped => $DescSped) {
            Out::select($this->nameForm . '_ANATSP[TSPTIPO]', 1, $keySped, 0, $DescSped);
        }
    }

}

?>
