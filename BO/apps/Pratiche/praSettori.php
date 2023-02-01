<?php

/**
 *
 * ANAGRAFICA SETTORI COMMERCIALI
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
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function praSettori() {
    $praSettori = new praSettori();
    $praSettori->parseEvent();
    return;
}

class praSettori extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $utiEnte;
    public $nameForm = "praSettori";
    public $divGes = "praSettori_divGestione";
    public $divRis = "praSettori_divRisultato";
    public $divRic = "praSettori_divRicerca";
    public $gridSettori = "praSettori_gridSettori";

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
                    case $this->gridSettori:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridSettori:
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
                $ita_grid01->setSortIndex('SETDES');
                $ita_grid01->exportXLS('', 'settori.xls');
                break;
            case 'onClickTablePager':
                $ordinamento = $_POST['sidx'];
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
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praSettori', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        // Importo l'ordinamento del filtro
                        $sql = $this->CreaSql();
                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridSettori,
                                            array(
                                                'sqlDB' => $this->PRAM_DB,
                                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows(1000);
                            $ita_grid01->setSortIndex('SETDES');
                            $Result_tab = $ita_grid01->getDataArray();
                            //$Result_tab=$this->elaboraRecord($Result_tab);
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
                                TableView::enableEvents($this->gridSettori);
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
                        Out::attributo($this->nameForm . '_ANASET[SETCOD]', 'readonly', '1');
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANASET[SETCOD]');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_ANASET']['SETCOD'];
                        if ($codice == '') {
                            $Anaset_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANASET ORDER BY SETCOD DESC", false, 1, 1);
                            $codice = $Anaset_rec['SETCOD'] + 1;
                        }
                        $_POST[$this->nameForm . '_ANASET']['SETCOD'] = $codice;
                        $Anaset_ric = $this->praLib->GetAnaset($codice);
                        if (!$Anaset_ric) {
                            $Anaset_ric = $_POST[$this->nameForm . '_ANASET'];
                            $Anaset_ric = $this->praLib->SetMarcaturaSettore($Anaset_ric, true);
                            try {
                                $insert_Info = 'Oggetto: ' . $Anaset_ric['ANASET'] . $Anaset_ric['SETDES'];
                                if ($this->insertRecord($this->PRAM_DB, 'ANASET', $Anaset_ric, $insert_Info)) {
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
                        $Anaset_rec = $_POST[$this->nameForm . '_ANASET'];
                        $Anaset_rec = $this->praLib->SetMarcaturaSettore($Anaset_rec);
                        $update_Info = 'Oggetto: ' . $Anaset_rec['SETCOD'] . $Anaset_rec['SETDES'];
                        if ($this->updateRecord($this->PRAM_DB, 'ANASET', $Anaset_rec, $update_Info)) {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_Cancella':
                        $result_tab = $this->praLib->CheckUsage($_POST[$this->nameForm . '_ANASET']['SETCOD'], "SETTORE");
                        $msg = "";
                        if ($result_tab['Result_tab_anapra']) {
                            $msg .= "Ci sono " . count($result_tab['Result_tab_anapra']) . " procedimenti con codice settore " . $_POST[$this->nameForm . '_ANASET']['SETCOD'] . "<br>";
                        }
                        if ($result_tab['Result_tab_proges']) {
                            $msg .= "Ci sono " . count($result_tab['Result_tab_proges']) . " pratiche con codice settore " . $_POST[$this->nameForm . '_ANASET']['SETCOD'] . "<br>";
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
                        $Anaset_rec = $_POST[$this->nameForm . '_ANASET'];
                        $Anaatt_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANAATT WHERE ATTSET = '" . $Anaset_rec['SETCOD'] . "'", true);
                        if ($Anaatt_tab) {
                            Out::msgInfo("Attenzione!!", "Impossibile Cancellare<br>Sono presenti attività con settore n. " . $Anaset_rec['SETCOD']);
                            break;
                        }
                        try {
                            $delete_Info = 'Oggetto: ' . $Anaset_rec['SETCOD'] . $Anaset_rec['SETDES'];
                            if ($this->deleteRecord($this->PRAM_DB, 'ANASET', $Anaset_rec['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA SETTORI", $e->getMessage());
                        }
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Setcod':
                        $codice = $_POST[$this->nameForm . '_Setcod'];
                        if (trim($codice) != "") {
                            $Anaset_rec = $this->praLib->getAnaset($codice);
                            if ($Anaset_rec) {
                                $this->Dettaglio($Anaset_rec['ROWID']);
                            }
                            Out::valore($this->nameForm . '_Setcod', $codice);
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
        $sql = "SELECT * FROM ANASET WHERE ROWID = ROWID";

        if ($_POST[$this->nameForm . '_Setcod'] != "") {
            $sql .= " AND SETCOD = '" . $_POST[$this->nameForm . '_Setcod'] . "'";
        }
        if ($_POST[$this->nameForm . '_Setdes'] != "") {
            $sql .= " AND SETDES LIKE '%" . addslashes($_POST[$this->nameForm . '_Setdes']) . "%'";
        }
        return $sql;
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::html($this->nameForm . "_Editore", '&nbsp;');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridSettori);
        TableView::clearGrid($this->gridSettori);
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
        $Anaset_rec = $this->praLib->GetAnaset($Indice, 'rowid');
        $open_Info = 'Oggetto: ' . $Anaset_rec['SETCOD'] . " " . $Anaset_rec['SETDES'];
        $this->openRecord($this->PRAM_DB, 'ANASET', $open_Info);
        $this->visualizzaMarcatura($Anaset_rec);
        $this->Nascondi();
        Out::valori($Anaset_rec, $this->nameForm . '_ANASET');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::setFocus('', $this->nameForm . '_ANASET[SETDES]');
        Out::attributo($this->nameForm . '_ANASET[SETCOD]', 'readonly', '0');
        TableView::disableEvents($this->gridSettori);
    }

    public function visualizzaMarcatura($Anaset_rec) {
        Out::html($this->nameForm . '_Editore', '  Autore: <span style="font-weight:bold;color:darkgreen;">' . $Anaset_rec['SETUPDEDITOR'] . '</span> Versione del: <span style="color:darkgreen;">' . date("d/m/Y", strtotime($Anaset_rec['SETUPDDATE'])) . ' ' . $Anaset_rec['SETUPDTIME'] . '  </span>');
    }

    public function elaboraRecord($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $sql = "SELECT * FROM ANAUNI WHERE UNISET='" . $Result_rec['UNISET'] . "'";
            $Anauni_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            $set = $Anauni_rec['UNISET'];
            $Result_tab[$key]['DESSET'] = $Anauni_rec['UNIDES'];
            $sql = "SELECT * FROM ANAUNI WHERE UNISER='" . $Result_rec['UNISER'] . "' AND UNISET='" . $set . "'";
            $Anauni_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            $ser = $Anauni_rec['UNISER'];
            $Result_tab[$key]['DESSER'] = $Anauni_rec['UNIDES'];
            $sql = "SELECT * FROM ANAUNI WHERE UNIOPE='" . $Result_rec['UNIOPE'] . "' AND UNISER='" . $ser . "'";
            $Anauni_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            $Result_tab[$key]['DESOPE'] = $Anauni_rec['UNIDES'];
        }
        return $Result_tab;
    }

}

?>