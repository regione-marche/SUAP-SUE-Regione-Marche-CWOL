<?php

/**
 *
 * ANAGRAFICA TIPOLOGIE
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
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praTipo() {
    $praTipo = new praTipo();
    $praTipo->parseEvent();
    return;
}

class praTipo extends itaModel {

    public $praLib;
    public $PRAM_DB;
    public $utiEnte;
    public $ITALWEB_DB;
    public $nameForm = "praTipo";
    public $divGes = "praTipo_divGestione";
    public $divRis = "praTipo_divRisultato";
    public $divRic = "praTipo_divRicerca";
    public $gridTipo = "praTipo_gridTipo";

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
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridTipo:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridTipo:
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
                $ita_grid01 = new TableView($this->gridTipo,
                                array(
                                    'sqlDB' => $this->PRAM_DB,
                                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('TIPDES');
                $ita_grid01->exportXLS('', 'Anatip.xls');
                break;
            case 'onClickTablePager':
                $tableSortOrder = $_POST['sord'];
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $ita_grid01->getDataPage('json');
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praTipo', $parameters);
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridTipo,
                                        array(
                                            'sqlDB' => $this->PRAM_DB,
                                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows(1000);
                        $ita_grid01->setSortIndex('TIPDES');
                        $ita_grid01->setSortOrder('asc');
                        if (!$ita_grid01->getDataPage('json')) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo il risultato
                            Out::hide($this->divGes, '');
                            Out::hide($this->divRic, '');
                            Out::show($this->divRis, '');
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridTipo);
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        Out::attributo($this->nameForm . '_ANATIP[TIPCOD]', 'readonly', '1');
                        Out::hide($this->divRic, '');
                        Out::hide($this->divRis, '');
                        Out::show($this->divGes, '');
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANATIP[TIPCOD]');
                        break;

                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_ANATIP']['TIPCOD'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $_POST[$this->nameForm . '_ANATIP']['TIPCOD'] = $codice;
                        try {   // Effettuo la FIND
                            $Anatip_rec = $this->praLib->GetAnatip($codice);
                            if (!$Anatip_rec) {
                                $Anatip_rec = $_POST[$this->nameForm . '_ANATIP'];
                                $Anatip_rec = $this->praLib->SetMarcaturaTipologia($Anatip_rec, true);
                                $insert_Info = 'Oggetto: ' . $Anatip_rec['TIPCOD'] . " " . $Anatip_rec['TIPDES'];
                                if ($this->insertRecord($this->PRAM_DB, 'ANATIP', $Anatip_rec, $insert_Info)) {
                                    $this->OpenRicerca();
                                }
                            } else {
                                Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                                Out::setFocus('', $this->nameForm . '_ANATIP[TIPCOD]');
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Inserimento su ANAGRAFICA TIPOLOGIA.", $e->getMessage());
                            break;
                        }
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $Anatip_rec = $_POST[$this->nameForm . '_ANATIP'];
                        $codice = $Anatip_rec['TIPCOD'];
                        $Anatip_rec['TIPCOD'] = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $Anatip_rec = $this->praLib->SetMarcaturaTipologia($Anatip_rec);
                        $update_Info = 'Oggetto: ' . $Anatip_rec['TIPCOD'] . " " . $Anatip_rec['TIPDES'];
                        if ($this->updateRecord($this->PRAM_DB, 'ANATIP', $Anatip_rec, $update_Info)) {
                            $this->OpenRicerca();
                        }
                        break;

                    case $this->nameForm . '_Cancella':
                        $result_tab = $this->praLib->CheckUsage($_POST[$this->nameForm . '_ANATIP']['TIPCOD'], "TIPOLOGIA");
                        $msg = "";
                        if ($result_tab['Result_tab_anapra']) {
                            $msg .= "Ci sono " . count($result_tab['Result_tab_anapra']) . " procedimenti con codice tipologia " . $_POST[$this->nameForm . '_ANATIP']['TIPCOD'] . "<br>";
                        }
                        if ($result_tab['Result_tab_proges']) {
                            $msg .= "Ci sono " . count($result_tab['Result_tab_proges']) . " pratiche con codice tipologia " . $_POST[$this->nameForm . '_ANATIP']['TIPCOD'] . "<br>";
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
                        $Anatip_rec = $_POST[$this->nameForm . '_ANATIP'];
                        try {
                            $delete_Info = 'Oggetto: ' . $Anatip_rec['TIPCOD'] . " " . $Anatip_rec['TIPDES'];
                            if ($this->deleteRecord($this->PRAM_DB, 'ANATIP', $Anatip_rec['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA TIPOLOGIE", $e->getMessage());
                            break;
                        }
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Tipcod':
                        $codice = $_POST[$this->nameForm . '_Tipcod'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anatip_rec = $this->praLib->GetAnatip($codice);
                            if ($Anatip_rec) {
                                $this->Dettaglio($Anatip_rec['ROWID']);
                            }
                        }
                        break;
                    case $this->nameForm . '_ANATIP[TIPCOD]':
                        $codice = $_POST[$this->nameForm . '_ANATIP']['TIPCOD'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_ANATIP[TIPCOD]', $codice);
                        }
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_tipo');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    public function OpenRicerca() {
        Out::show($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::hide($this->divGes, '');
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Tipcod');
    }

    function AzzeraVariabili() {
        Out::html($this->nameForm . "_Editore", '&nbsp;');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridTipo);
        TableView::clearGrid($this->gridTipo);
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
    }

    public function CreaSql() {

        $sql = "SELECT * FROM ANATIP ";
        if ($_POST[$this->nameForm . '_Tipcod'] != "") {
            $sql.= " WHERE TIPCOD = '" . $_POST[$this->nameForm . '_Tipcod'] . "'";
        }
        if ($_POST[$this->nameForm . '_Tipdes'] != "") {
            $sql.= " WHERE TIPDES LIKE '" . addslashes($_POST[$this->nameForm . '_Tipdes']) . "%'";
        }

        return $sql;
    }

    public function Dettaglio($_Indice) {
        $Anatip_rec = $this->praLib->GetAnatip($_Indice, 'rowid');
        $open_Info = 'Oggetto: ' . $Anatip_rec['TIPCOD'] . " " . $Anatip_rec['TIPDES'];
        $this->openRecord($this->PRAM_DB, 'ANATIP', $open_Info);
        $this->visualizzaMarcatura($Anatip_rec);
        $this->Nascondi();
        Out::valori($Anatip_rec, $this->nameForm . '_ANATIP');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::attributo($this->nameForm . '_ANATIP[TIPCOD]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_ANATIP[TIPDES]');
        TableView::disableEvents($this->gridTipo);
    }

    public function visualizzaMarcatura($Anatip_rec) {
        Out::html($this->nameForm . '_Editore', '  Autore: <span style="font-weight:bold;color:darkgreen;">' . $Anatip_rec['TIPUPDEDITOR'] . '</span> Versione del: <span style="color:darkgreen;">' . date("d/m/Y", strtotime($Anatip_rec['TIPUPDDATE'])) . ' ' . $Anatip_rec['TIPUPDTIME'] . '  </span>');
    }

}

?>
