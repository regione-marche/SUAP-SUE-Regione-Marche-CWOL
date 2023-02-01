<?php

/**
 *
 * ANAGRAFICA HEP FO
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    26.11.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function praHelp() {
    $praHelp = new praHelp();
    $praHelp->parseEvent();
    return;
}

class praHelp extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $utiEnte;
    public $nameForm = "praHelp";
    public $divGes = "praHelp_divGestione";
    public $divRis = "praHelp_divRisultato";
    public $divRic = "praHelp_divRicerca";
    public $gridHelp = "praHelp_gridHelp";

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
                    case $this->gridHelp:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridHelp:
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
                $ita_grid01 = new TableView($this->gridHelp,
                                array(
                                    'sqlDB' => $this->PRAM_DB,
                                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('HELPDES');
                $ita_grid01->exportXLS('', 'help.xls');
                break;
            case 'onClickTablePager':
                $ordinamento = $_POST['sidx'];
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($ordinamento);
                $ita_grid01->setSortOrder($_POST['sord']);
//                $Result_tab = $ita_grid01->getDataArray();
//                $Result_tab = $this->elaboraRecord($Result_tab);
//                $ita_grid01->getDataPageFromArray('json', $Result_tab);
                $ita_grid01->getDataPage('json');
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praHelp', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        // Importo l'ordinamento del filtro
                        $sql = $this->CreaSql();
                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridHelp,
                                            array(
                                                'sqlDB' => $this->PRAM_DB,
                                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows(1000);
                            $ita_grid01->setSortIndex('HELPDES');
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
                                TableView::enableEvents($this->gridHelp);
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
                        Out::attributo($this->nameForm . '_ANAHELP[HELPCOD]', 'readonly', '1');
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANAHELP[HELPCOD]');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_ANAHELP']['HELPCOD'];
                        $Anahelp_ric = $this->praLib->GetAnahelp($codice);
                        if (!$Anahelp_ric) {
                            $Anahelp_ric = $_POST[$this->nameForm . '_ANAHELP'];
                            //$Anahelp_ric = $this->praLib->SetMarcaturaSettore($Anahelp_ric, true);
                            try {
                                $insert_Info = 'Oggetto: Inserisco Help codice ' . $Anahelp_ric['HELPCOD'] . $Anahelp_ric['HELPDES'];
                                if ($this->insertRecord($this->PRAM_DB, 'ANAHELP', $Anahelp_ric, $insert_Info)) {
                                    $this->OpenRicerca();
                                }
                            } catch (Exception $e) {
                                Out::msgStop("Errore in Inserimento", $e->getMessage(), '600', '600');
                            }
                        } else {
                            Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_ANAHELP[HELPCOD]');
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $Anahelp_rec = $_POST[$this->nameForm . '_ANAHELP'];
                        //$Anaset_rec = $this->praLib->SetMarcaturaSettore($Anaset_rec);
                        $update_Info = 'Oggetto: Aggiorno Help codice ' . $Anahelp_rec['HELPCOD'] . $Anahelp_rec['HELPDES'];
                        if ($this->updateRecord($this->PRAM_DB, 'ANAHELP', $Anahelp_rec, $update_Info)) {
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
                        $Anahelp_rec = $_POST[$this->nameForm . '_ANAHELP'];
                        $Itepas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ITEPAS WHERE ITEHELP = " . $Anahelp_rec['HELPCOD'], true);
                        if ($Itepas_tab) {
                            Out::msgInfo("Attenzione!!", "Impossibile Cancellare.<br>Sono presenti passi con help codice " . $Anahelp_rec['HELPCOD']);
                            break;
                        }
                        try {
                            $delete_Info = 'Oggetto: Cancello Help codice ' . $Anahelp_rec['HELPCOD'] . $Anahelp_rec['HELPDES'];
                            if ($this->deleteRecord($this->PRAM_DB, 'ANAHELP', $Anahelp_rec['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA HELP", $e->getMessage());
                        }
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Helpcod':
                        $codice = $_POST[$this->nameForm . '_Helpcod'];
                        if (trim($codice) != "") {
                            $Anahelp_rec = $this->praLib->GetAnahelp($codice);
                            if ($Anahelp_rec) {
                                $this->Dettaglio($Anahelp_rec['ROWID']);
                            }
                            Out::valore($this->nameForm . '_Helpcod', $codice);
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
        $sql = "SELECT * FROM ANAHELP WHERE ROWID = ROWID";

        if ($_POST[$this->nameForm . '_Helpcod'] != "") {
            //$sql .= " AND HELPCOD = '" . $_POST[$this->nameForm . '_Helpcod'] . "'";
            $sql .= " AND HELPCOD LIKE '%" . $_POST[$this->nameForm . '_Helpcod'] . "%'";
        }
        if ($_POST[$this->nameForm . '_Helpdes'] != "") {
            $sql .= " AND HELPDES LIKE '%" . addslashes($_POST[$this->nameForm . '_Helpdes']) . "%'";
        }
        return $sql;
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridHelp);
        TableView::clearGrid($this->gridHelp);
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Helpcod');
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
        $Anahelp_rec = $this->praLib->GetAnahelp($Indice, 'rowid');
        $open_Info = 'Oggetto: Apro Help ' . $Anahelp_rec['HELPCOD'] . " " . $Anahelp_rec['HELPDES'];
        $this->openRecord($this->PRAM_DB, 'ANAHELP', $open_Info);
        //$this->visualizzaMarcatura($Anahelp_rec);
        $this->Nascondi();
        Out::valori($Anahelp_rec, $this->nameForm . '_ANAHELP');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::setFocus('', $this->nameForm . '_ANAHELP[HELPDES]');
        Out::attributo($this->nameForm . '_ANAHELP[HELPCOD]', 'readonly', '0');
        TableView::disableEvents($this->gridHelp);
    }

    public function visualizzaMarcatura($Anaset_rec) {
        Out::html($this->nameForm . '_Editore', '  Autore: <span style="font-weight:bold;color:darkgreen;">' . $Anaset_rec['SETUPDEDITOR'] . '</span> Versione del: <span style="color:darkgreen;">' . date("d/m/Y", strtotime($Anaset_rec['SETUPDDATE'])) . ' ' . $Anaset_rec['SETUPDTIME'] . '  </span>');
    }


}

?>