<?php

/**
 *
 * ANAGRAFICA ATTIVITA' COMMERCIALI
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
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function praAttivita() {
    $praAttivita = new praAttivita();
    $praAttivita->parseEvent();
    return;
}

class praAttivita extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $utiEnte;
    public $nameForm = "praAttivita";
    public $divGes = "praAttivita_divGestione";
    public $divRis = "praAttivita_divRisultato";
    public $divRic = "praAttivita_divRicerca";
    public $gridAttivita = "praAttivita_gridAttivita";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->utiEnte = new utiEnte();
            $this->utiEnte->getITALWEB_DB();
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
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAttivita:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAttivita:
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
                $ita_grid01 = new TableView($this->gridSettori,
                                array(
                                    'sqlDB' => $this->PRAM_DB,
                                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('ATTDES');
                $ita_grid01->exportXLS('', 'attivita.xls');
                break;
            case 'onClickTablePager':
                $ordinamento = $_POST['sidx'];
                if ($ordinamento == 'SETTORE') {
                    $ordinamento = 'ATTSET';
                }
                $tableSortOrder = $_POST['sord'];
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($ordinamento);
                $ita_grid01->setSortOrder($_POST['sord']);
                $Result_tab = $ita_grid01->getDataArray();
                $Result_tab = $this->elaboraRecord($Result_tab);
                $ita_grid01->getDataPageFromArray('json', $Result_tab);
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praAttivita', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        // Importo l'ordinamento del filtro
                        $sql = $this->CreaSql();
                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridAttivita,
                                            array(
                                                'sqlDB' => $this->PRAM_DB,
                                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows(1000);
                            //$ita_grid01->setSortIndex('ATTCOD');
                            $Result_tab = $ita_grid01->getDataArray();
                            $Result_tab = $this->elaboraRecord($Result_tab);
                            if (!$ita_grid01->getDataPageFromArray('json', $Result_tab)) {
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
                                TableView::enableEvents($this->gridAttivita);
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
                        Out::attributo($this->nameForm . '_ANAATT[ATTCOD]', 'readonly', '1');
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANAATT[ATTCOD]');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_ANAATT']['ATTCOD'];
                        if ($codice == '') {
                            $Anaatt_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANAATT ORDER BY ATTCOD DESC", false, 1, 1);
                            $codice = $Anaatt_rec['ATTCOD'] + 1;
                        }
                        $_POST[$this->nameForm . '_ANAATT']['ATTCOD'] = $codice;
                        $Anaatt_ric = $this->praLib->GetAnaatt($codice);
                        if (!$Anaatt_ric) {
                            $Anaatt_ric = $_POST[$this->nameForm . '_ANAATT'];
                            $Anaatt_ric = $this->praLib->SetMarcaturaAttivita($Anaatt_ric, true);
                            try {
                                $insert_Info = 'Oggetto: ' . $Anaatt_ric['ATTCOD'] . $Anaatt_ric['ATTDES'];
                                if ($this->insertRecord($this->PRAM_DB, 'ANAATT', $Anaatt_ric, $insert_Info)) {
                                    $this->OpenRicerca();
                                }
                            } catch (Exception $e) {
                                Out::msgStop("Errore in Inserimento", $e->getMessage(), '600', '600');
                            }
                        } else {
                            Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_ANASET[SETCOD]');
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $Anaatt_rec = $_POST[$this->nameForm . '_ANAATT'];
                        $Anaatt_rec = $this->praLib->SetMarcaturaAttivita($Anaatt_rec);
                        $update_Info = 'Oggetto: ' . $Anaatt_rec['ATTCOD'] . $Anaatt_rec['ATTDES'];
                        if ($this->updateRecord($this->PRAM_DB, 'ANAATT', $Anaatt_rec, $update_Info)) {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_Cancella':
                        $result_tab = $this->praLib->CheckUsage($_POST[$this->nameForm . '_ANAATT']['ATTCOD'], "ATTIVITA");
                        $msg = "";
                        if ($result_tab['Result_tab_anapra']) {
                            $msg .= "Ci sono " . count($result_tab['Result_tab_anapra']) . " procedimenti con codice attività " . $_POST[$this->nameForm . '_ANAATT']['ATTCOD'] . "<br>";
                        }
                        if ($result_tab['Result_tab_proges']) {
                            $msg .= "Ci sono " . count($result_tab['Result_tab_proges']) . " pratiche con codice attività " . $_POST[$this->nameForm . '_ANAATT']['ATTCOD'] . "<br>";
                        }
                        if ($msg) {
                            Out::msgInfo("Impossibile Cancellare", $msg);
                            break;
                        }

                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $Anaatt_rec = $_POST[$this->nameForm . '_ANAATT'];
                        try {
                            $delete_Info = 'Oggetto: ' . $Anaatt_rec['ATTCOD'] . $Anaatt_rec['ATTDES'];
                            if ($this->deleteRecord($this->PRAM_DB, 'ANAATT', $Anaatt_rec['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA SETTORI", $e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_Attset_butt':
                    case $this->nameForm . '_ANAATT[ATTSET]_butt':
                        praRic::praRicAnaset("praAttivita", "");
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Attcod':
                        $codice = $_POST[$this->nameForm . '_Attcod'];
                        if ($codice != "") {
                            $Anaatt_rec = $this->praLib->getAnaatt($codice);
                            if ($Anaatt_rec) {
                                $this->Dettaglio($Anaatt_rec['ROWID']);
                            }
                            Out::valore($this->nameForm . '_Attcod', $codice);
                        }
                        break;
                    case $this->nameForm . '_ANAATT[ATTSET]':
                        $codice = $_POST[$this->nameForm . '_ANAATT']['ATTSET'];
                        if ($codice != "") {
                            $Anaset_rec = $this->praLib->getAnaset($codice);
                            Out::valore($this->nameForm . '_ANAATT[ATTSET]', $Anaset_rec["SETCOD"]);
                            Out::valore($this->nameForm . '_SETTORE', $Anaset_rec["SETDES"]);
                        }
                        break;

                    case $this->nameForm . '_Attset':
                        $codice = $_POST[$this->nameForm . '_Attset'];
                        if ($codice != "") {
                            $Anaset_rec = $this->praLib->getAnaset($codice);
                            Out::valore($this->nameForm . '_Attset', $Anaset_rec["SETCOD"]);
                            Out::valore($this->nameForm . '_Desc_sett', $Anaset_rec["SETDES"]);
                        }
                        break;
                    case $this->nameForm . '_ANASET[SETCOD]':
                        $codice = $_POST[$this->nameForm . '_ANASET']['SETCOD'];
                        if (trim($codice) != "") {
                            Out::valore($this->nameForm . '_ANASET[SETCOD]', $codice);
                        }
                        break;
                }
                break;
            case 'returnAnaset':
                $Anaset_rec = $this->praLib->GetAnaset($_POST["retKey"], 'rowid');
                if ($Anaset_rec) {
                    Out::valore($this->nameForm . '_Attset', $Anaset_rec["SETCOD"]);
                    Out::valore($this->nameForm . '_Desc_sett', $Anaset_rec["SETDES"]);
                    Out::valore($this->nameForm . '_ANAATT[ATTSET]', $Anaset_rec["SETCOD"]);
                    Out::valore($this->nameForm . '_SETTORE', $Anaset_rec["SETDES"]);
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
        //$sql="SELECT * FROM ANAATT WHERE ROWID = ROWID";
        $sql = "SELECT ANAATT.ROWID AS ROWID, ANAATT.ATTCOD AS ATTCOD, ANAATT.ATTDES AS ATTDES, ANASET.SETDES AS SETDES, ANAATT.ATTCLA AS ATTCLA, ANAATT.ATTSET AS ATTSET, ANASET.SETCOD AS SETCOD, ANASET.SETCLA AS SETCLA 
            FROM ANAATT ANAATT
            LEFT OUTER JOIN ANASET ANASET ON ANAATT.ATTSET = ANASET.SETCOD
            WHERE ANAATT.ROWID = ANAATT.ROWID";

        if ($_POST[$this->nameForm . '_Attcod'] != "") {
            $sql .= " AND ATTCOD = '" . $_POST[$this->nameForm . '_Attcod'] . "'";
        }
        if ($_POST[$this->nameForm . '_Attset'] != "") {
            $sql .= " AND ATTSET = '" . $_POST[$this->nameForm . '_Attset'] . "'";
        }
        if ($_POST[$this->nameForm . '_Attdes'] != "") {
            $sql .= " AND ATTDES LIKE '%" . addslashes($_POST[$this->nameForm . '_Attdes']) . "%'";
        }
        //$sql .= " ORDER BY ANAATT.ATTSET,ANAATT.ATTCOD";
        return $sql;
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::html($this->nameForm . "_Editore", '&nbsp;');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridAttivita);
        TableView::clearGrid($this->gridAttivita);
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Setcod');
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
        $Anaatt_rec = $this->praLib->GetAnaatt($Indice, 'rowid');
        $Anaset_rec = $this->praLib->GetAnaset($Anaatt_rec['ATTSET']);
        $open_Info = 'Oggetto: ' . $Anaatt_rec['ATTCOD'] . " " . $Anaatt_rec['ATTDES'];
        $this->openRecord($this->PRAM_DB, 'ANAATT', $open_Info);
        $this->visualizzaMarcatura($Anaatt_rec);
        $this->Nascondi();
        Out::valori($Anaatt_rec, $this->nameForm . '_ANAATT');
        Out::valore($this->nameForm . '_SETTORE', $Anaset_rec["SETDES"]);
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::setFocus('', $this->nameForm . '_ANAATT[ATTDES]');
        Out::attributo($this->nameForm . '_ANAATT[ATTCOD]', 'readonly', '0');
        TableView::disableEvents($this->gridAttivita);
    }

    public function visualizzaMarcatura($Anaatt_rec) {
        Out::html($this->nameForm . '_Editore', '  Autore: <span style="font-weight:bold;color:darkgreen;">' . $Anaatt_rec['ATTUPDEDITOR'] . '</span> Versione del: <span style="color:darkgreen;">' . date("d/m/Y", strtotime($Anaatt_rec['ATTUPDDATE'])) . ' ' . $Anaatt_rec['ATTUPDTIME'] . '  </span>');
    }

    public function elaboraRecord($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $sql = "SELECT * FROM ANASET WHERE SETCOD='" . $Result_rec['ATTSET'] . "'";
            $Anaset_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            $set = $Anauni_rec['UNISET'];
            $Result_tab[$key]['SETTORE'] = $Anaset_rec['SETCOD'] . " - " . $Anaset_rec['SETDES'];
        }
        return $Result_tab;
    }

}

?>