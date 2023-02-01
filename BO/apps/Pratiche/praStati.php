<?php

/**
 *
 * ANAGRAFICA STATI PER PRATICHE/PROCEDIMENTI/PASSI/COMUNICAZIONI
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

function praStati() {
    $praStati = new praStati();
    $praStati->parseEvent();
    return;
}

class praStati extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $utiEnte;
    public $nameForm = "praStati";
    public $divGes = "praStati_divGestione";
    public $divRis = "praStati_divRisultato";
    public $divRic = "praStati_divRicerca";
    public $gridStati = "praStati_gridStati";

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
                    case $this->gridStati:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridStati:
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
                $ita_grid01 = new TableView($this->gridStati, array(
                    'sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('STPDES');
                $ita_grid01->exportXLS('', 'stati.xls');
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
                //$Result_tab=$this->elaboraRecord($Result_tab);
                $ita_grid01->getDataPageFromArray('json', $Result_tab);
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praStati', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_TornaElenco': 
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        // Importo l'ordinamento del filtro
                        $sql = $this->CreaSql();
                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridStati, array(
                                'sqlDB' => $this->PRAM_DB,
                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows(1000);
                            $ita_grid01->setSortIndex('STPDES');
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
                                TableView::enableEvents($this->gridStati);
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
                        Out::attributo($this->nameForm . '_ANASTP[ROWID]', 'readonly', '0');
                        Out::attributo($this->nameForm . "_inCorso", "checked", "0", "checked");
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANASTP[ROWID]');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $Anastp_ric = $_POST[$this->nameForm . '_ANASTP'];
                        $Anastp_ric['STPFLAG'] = $_POST[$this->nameForm . '_stato'];
                        try {
                            $insert_Info = 'Oggetto: ' . $Anastp_ric['ROWID'] . $Anastp_ric['STPDES'];
                            if ($this->insertRecord($this->PRAM_DB, 'ANASTP', $Anastp_ric, $insert_Info)) {
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Inserimento", $e->getMessage(), '600', '600');
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $Anastp_rec = $_POST[$this->nameForm . '_ANASTP'];
                        $Anastp_rec['STPFLAG'] = $_POST[$this->nameForm . '_stato'];
                        $update_Info = 'Oggetto: ' . $Anastp_rec['ROWID'] . $Anaset_rec['STPDES'];
                        if ($this->updateRecord($this->PRAM_DB, 'ANASTP', $Anastp_rec, $update_Info)) {
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
                        $Anastp_rec = $_POST[$this->nameForm . '_ANASTP'];
                        try {
                            $delete_Info = 'Oggetto: ' . $Anastp_rec['ROWID'] . $Anastp_rec['STPDES'];
                            if ($this->deleteRecord($this->PRAM_DB, 'ANASTP', $Anastp_rec['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA STATI", $e->getMessage());
                        }
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Stpcod':
                        $codice = $_POST[$this->nameForm . '_Stpcod'];
                        if (trim($codice) != "") {
                            $Anastp_rec = $this->praLib->getAnastp($codice);
                            if ($Anastp_rec) {
                                $this->Dettaglio($Anastp_rec['ROWID']);
                            }
                            Out::valore($this->nameForm . '_Stpcod', $codice);
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
        $sql = "SELECT * FROM ANASTP WHERE 1";

        if ($_POST[$this->nameForm . '_Stpcod'] != "") {
            $sql .= " AND ROWID = '" . $_POST[$this->nameForm . '_Stpcod'] . "'";
        }
        if ($_POST[$this->nameForm . '_Stpdes'] != "") {
            $sql .= " AND STPDES LIKE '%" . addslashes($_POST[$this->nameForm . '_Stpdes']) . "%'";
        }
        return $sql;
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridStati);
        TableView::clearGrid($this->gridStati);
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Stpcod');
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
        $Anastp_rec = $this->praLib->GetAnastp($Indice, 'rowid');
        $open_Info = 'Oggetto: ' . $Anastp_rec['ROWID'] . " " . $Anastp_rec['STPDES'];
        $this->openRecord($this->PRAM_DB, 'ANASET', $open_Info);
        $this->Nascondi();
        Out::valori($Anastp_rec, $this->nameForm . '_ANASTP');
        switch ($Anastp_rec['STPFLAG']) {
            case 'In corso':
                Out::attributo($this->nameForm . "_inCorso", "checked", "0", "checked");
                break;
            case 'Annullata':
                Out::attributo($this->nameForm . "_Annullata", "checked", "0", "checked");
                break;
            case 'Sospesa':
                Out::attributo($this->nameForm . "_Sospesa", "checked", "0", "checked");
                break;
            case 'Chiusa Positivamente':
                Out::attributo($this->nameForm . "_chiusaPos", "checked", "0", "checked");
                break;
            case 'Chiusa Negativamente':
                Out::attributo($this->nameForm . "_chiusaNeg", "checked", "0", "checked");
                break;

            default:
                break;
        }
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_TornaElenco');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::setFocus('', $this->nameForm . '_ANASTP[STPDES]');
        Out::attributo($this->nameForm . '_ANASTP[ROWID]', 'readonly', '0');
        TableView::disableEvents($this->gridStati);
    }

}

?>