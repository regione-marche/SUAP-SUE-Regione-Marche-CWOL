<?php

/**
 *
 * ANAGRAFICA SETTORI COMMERCIALI
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Tania Angeloni <tania.angeloni@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    08.05/2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Base/basLib.class.php';
include_once ITA_BASE_PATH . '/apps/Base/basRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function basDestUso() {
    $basDestUso = new basDestUso();
    $basDestUso->parseEvent();
    return;
}

class basDestUso extends itaModel {

    public $BASE_DB;
    public $basLib;
    public $utiEnte;
    public $nameForm = "basDestUso";
    public $divGes = "basDestUso_divGestione";
    public $divRis = "basDestUso_divRisultato";
    public $divRic = "basDestUso_divRicerca";
    public $gridDestinazione = "basDestUso_gridDestinazione";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->basLib = new basLib();
            $this->BASE_DB = $this->basLib->getBASEDB();
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
                    case $this->gridDestinazione:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridDestinazione:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm)
                                )
                        );
                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridDestinazione, array(
                    'sqlDB' => $this->BASE_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('DESC_DESTINAZIONE');
                $ita_grid01->exportXLS('', 'basDestUso.xls');
                break;
            case 'onClickTablePager':
                $ordinamento = $_POST['sidx'];
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->BASE_DB, 'sqlQuery' => $sql));
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
                $itaJR->runSQLReportPDF($this->BASE_DB, 'basDestUso', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_TornaElenco':
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        // Importo l'ordinamento del filtro
                        $sql = $this->CreaSql();
                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridDestinazione, array(
                                'sqlDB' => $this->BASE_DB,
                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows(1000);
                            $ita_grid01->setSortIndex('DESC_DESTINAZIONE');
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
                                TableView::enableEvents($this->gridDestinazione);
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
                        Out::attributo($this->nameForm . '_PRG_DESTINAZIONI_USO[ID_DESTINAZIONE]', 'readonly', '1');
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_PRG_DESTINAZIONI_USO[ID_DESTINAZIONE]');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $Dest_ric = $this->basLib->getDestinazioniUso($_POST[$this->nameForm . '_PRG_DESTINAZIONI_USO']['ID_DESTINAZIONE']);
                        if (!$Dest_ric) {
                            $insert_Info = 'Oggetto: ' . $Dest_ric['ID_DESTINAZIONE'] . $Dest_ric['DESC_DESTINAZIONE'];
                            if ($this->insertRecord($this->BASE_DB, 'PRG_DESTINAZIONI_USO', $_POST[$this->nameForm . '_PRG_DESTINAZIONI_USO'], $insert_Info)) {
                                $this->OpenRicerca();
                            }
                        } else {
                            Out::msgStop("Codice già  presente", "Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_PRG_DESTINAZIONI_USO[ID_DESTINAZIONE]');
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $update_Info = 'Oggetto: ' . $Dest_rec['ID_DESTINAZIONE'] . $Dest_rec['DESC_DESTINAZIONE'];
                        if ($this->updateRecord($this->BASE_DB, 'PRG_DESTINAZIONI_USO',$_POST[$this->nameForm . '_PRG_DESTINAZIONI_USO'], $update_Info, 'ROW_ID')) {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm)
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $delete_Info = 'Oggetto: ' . $Dest_rec['ID_DESTINAZIONE'] . $Dest_rec['DESC_DESTINAZIONE'];
                        if ($this->deleteRecord($this->BASE_DB, 'PRG_DESTINAZIONI_USO', $_POST[$this->nameForm . '_PRG_DESTINAZIONI_USO']['ROW_ID'], $delete_Info, 'ROW_ID')) {
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
                    case $this->nameForm . '_ID_DESTINAZIONE':
                        Out::setFocus( $_POST[$this->nameForm . '_PRG_DESTINAZIONI_USO']['DESC_DESTINAZIONE']);
                        break;
                    case $this->nameForm . '_Ruocod':
                          $Dest_rec = $this->basLib->getDestinazioniUso($_POST[$this->nameForm . '_Ruocod']);
                            if ($Dest_rec) {
                                $this->Dettaglio($Dest_rec['ROW_ID']);
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
        $sql = "SELECT * FROM PRG_DESTINAZIONI_USO WHERE 1 = 1";

        if ($_POST[$this->nameForm . '_Ruocod'] != "") {
            $sql .= " AND ID_DESTINAZIONE = '" . $_POST[$this->nameForm . '_Ruocod'] . "'";
        }
        if ($_POST[$this->nameForm . '_Ruodes'] != "") {
            $sql .= " AND DESC_DESTINAZIONE LIKE '%" . addslashes($_POST[$this->nameForm . '_Ruodes']) . "%'";
        }
        return $sql;
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridDestinazione);
        TableView::clearGrid($this->gridDestinazione);
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
        Out::hide($this->nameForm . '_TornaElenco');
    }

    public function Dettaglio($Indice) {
        $Destinazioni_rec = $this->basLib->getDestinazioniUso($Indice, 'rowid');
        $open_Info = 'Oggetto: ' . $Destinazioni_rec['ID_DESTINAZIONE'] . " " . $Destinazioni_rec['DESC_DESTINAZIONE'];
        $this->openRecord($this->BASE_DB, 'PRG_DESTINAZIONI_USO', $open_Info);
        $this->Nascondi();
        Out::valori($Destinazioni_rec, $this->nameForm . '_PRG_DESTINAZIONI_USO');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_TornaElenco');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::setFocus('', $this->nameForm . '_PRG_DESTINAZIONI_USO[DESC_DESTINAZIONE]');
        Out::attributo($this->nameForm . '_PRG_DESTINAZIONI_USO[ID_DESTINAZIONE]', 'readonly', '0');
        TableView::disableEvents($this->gridDestinazione);
    }
}