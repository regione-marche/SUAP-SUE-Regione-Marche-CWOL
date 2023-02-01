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
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    20.03.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRuolo.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function praRuoli() {
    $praRuoli = new praRuoli();
    $praRuoli->parseEvent();
    return;
}

class praRuoli extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $utiEnte;
    public $nameForm = "praRuoli";
    public $divGes = "praRuoli_divGestione";
    public $divRis = "praRuoli_divRisultato";
    public $divRic = "praRuoli_divRicerca";
    public $gridRuoli = "praRuoli_gridRuoli";

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
                if (!praRuolo::initSistemSubjectRoles($this->praLib)) {
                    Out::msgStop("Attenzione!!!", "Errore inizializzazione ruoli");
                    break;
                }
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridRuoli:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridRuoli:
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
                $ita_grid01 = new TableView($this->gridRuoli,
                                array(
                                    'sqlDB' => $this->PRAM_DB,
                                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('DESDES');
                $ita_grid01->exportXLS('', 'ruoli.xls');
                break;
            case 'onClickTablePager':
                $ordinamento = $_POST['sidx'];
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($ordinamento);
                $ita_grid01->setSortOrder($_POST['sord']);
                $Result_tab = $ita_grid01->getDataArray();
                $ita_grid01->getDataPage('json');
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praRuoli', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        // Importo l'ordinamento del filtro
                        $sql = $this->CreaSql();
                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridRuoli,
                                            array(
                                                'sqlDB' => $this->PRAM_DB,
                                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows(1000);
                            $ita_grid01->setSortIndex('RUODES');
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
                                TableView::enableEvents($this->gridRuoli);
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
                        Out::attributo($this->nameForm . '_ANARUO[RUOCOD]', 'readonly', '1');
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANARUO[RUOCOD]');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $codice = str_pad($_POST[$this->nameForm . '_ANARUO']['RUOCOD'], 4, "0", STR_PAD_LEFT);
                        if(!praRuolo::isConfigurable($codice)){
                            Out::msgInfo("Attenzione!!", "il codice $codice non può essere utilizzato.<br>utilizzare un codice compreso tra ".praRuolo::$CONFIGURABLE_ROLES_FROM_CODE." e ".praRuolo::$CONFIGURABLE_ROLES_TO_CODE."");
                            break;
                        }
                        $Anaruo_ric = $this->praLib->GetAnaruo($codice);
                        if (!$Anaruo_ric) {
                            $Anaruo_ric = $this->praLib->SetMarcaturaRuolo($_POST[$this->nameForm . '_ANARUO'], true);
                            $insert_Info = 'Oggetto: ' . $Anaruo_ric['RUOCOD'] . $Anaruo_ric['RUODES'];
                            if ($this->insertRecord($this->PRAM_DB, 'ANARUO', $Anaruo_ric, $insert_Info)) {
                                $this->OpenRicerca();
                            }
                        } else {
                            Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_ANARUO[RUOCOD]');
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $Anaruo_rec = $this->praLib->SetMarcaturaRuolo($_POST[$this->nameForm . '_ANARUO']);
                        $update_Info = 'Oggetto: ' . $Anaruo_rec['RUOCOD'] . $Anaruo_rec['RUODES'];
                        if ($this->updateRecord($this->PRAM_DB, 'ANARUO', $Anaruo_rec, $update_Info)) {
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
                        $Anaruo_rec = $_POST[$this->nameForm . '_ANARUO'];
                        $delete_Info = 'Oggetto: ' . $Anaruo_rec['RUOCOD'] . $Anaruo_rec['RUODES'];
                        if ($this->deleteRecord($this->PRAM_DB, 'ANARUO', $Anaruo_rec['ROWID'], $delete_Info)) {
                            $this->OpenRicerca();
                        }
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Ruocod':
                        $codice = $_POST[$this->nameForm . '_Ruocod'];
                        if (trim($codice) != "") {
                            $codice = str_pad($codice, 4, "0", STR_PAD_LEFT);
                            $Anaruo_rec = $this->praLib->getAnaruo($codice);
                            if ($Anaruo_rec) {
                                $this->Dettaglio($Anaruo_rec['ROWID']);
                            }
                            Out::valore($this->nameForm . '_Ruocod', $codice);
                        }
                        break;
                    case $this->nameForm . '_ANARUO[RUOCOD]':
                        $codice = $_POST[$this->nameForm . '_ANARUO']['RUOCOD'];
                        if (trim($codice) != "") {
                            $codice = str_pad($codice, 4, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_ANARUO[RUOCOD]', $codice);
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
        $sql = "SELECT * FROM ANARUO WHERE ROWID = ROWID";

        if ($_POST[$this->nameForm . '_Ruood'] != "") {
            $sql .= " AND RUOCOD = '" . $_POST[$this->nameForm . '_Ruocod'] . "'";
        }
        if ($_POST[$this->nameForm . '_Ruodes'] != "") {
            $sql .= " AND RUODES LIKE '%" . addslashes($_POST[$this->nameForm . '_Ruodes']) . "%'";
        }
        return $sql;
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridRuoli);
        TableView::clearGrid($this->gridRuoli);
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Ruocod');
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
        $Anaruo_rec = $this->praLib->GetAnaruo($Indice, 'rowid');
        $open_Info = 'Oggetto: ' . $Anaruo_rec['RUOCOD'] . " " . $Anaruo_rec['RUODES'];
        $this->openRecord($this->PRAM_DB, 'ANARUO', $open_Info);
        $this->visualizzaMarcatura($Anaruo_rec);
        $this->Nascondi();
        Out::valori($Anaruo_rec, $this->nameForm . '_ANARUO');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::setFocus('', $this->nameForm . '_ANARUO[RUODES]');
        Out::attributo($this->nameForm . '_ANARUO[RUOCOD]', 'readonly', '0');
        TableView::disableEvents($this->gridRuoli);
    }

    public function visualizzaMarcatura($Anaruo_rec) {
        Out::html($this->nameForm . '_Editore', '  Autore: <span style="font-weight:bold;color:darkgreen;">' . $Anaruo_rec['RUOUPDEDITOR'] . '</span> Versione del: <span style="color:darkgreen;">' . date("d/m/Y", strtotime($Anaruo_rec['RUOUPDDATE'])) . ' ' . $Anaset_rec['RUOUPDTIME'] . '  </span>');
    }

}

?>