<?php

/**
 *
 * ANAGRAFICA RUOLI
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    07.05.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

function proAnaruoli() {
    $proAnaruoli = new proAnaruoli();
    $proAnaruoli->parseEvent();
    return;
}

class proAnaruoli extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $nameForm = "proAnaruoli";
    public $divGes = "proAnaruoli_divGestione";
    public $divRis = "proAnaruoli_divRisultato";
    public $divRic = "proAnaruoli_divRicerca";
    public $gridAnaruoli = "proAnaruoli_gridAnaruoli";

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
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
                    case $this->gridAnaruoli:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnaruoli:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );

                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnaruoli:
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANARUOLI[RUOCOD]');
                        break;
                }
                break;

            case 'printTableToHTML':
                $Anaent_rec = $this->proLib->GetAnaent('2');
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $Anaent_rec['ENTDE1']);
                $itaJR->runSQLReportPDF($this->PROT_DB, 'proAnaruoli', $parameters);
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridAnaruoli,
                                array(
                                    'sqlDB' => $this->PROT_DB,
                                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('RUODES');
                $ita_grid01->exportXLS('', 'Anaruoli.xls');
                break;
            case 'onClickTablePager':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PROT_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $ita_grid01->getDataPage('json');
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TornaElenco':
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridAnaruoli,
                                        array(
                                            'sqlDB' => $this->PROT_DB,
                                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridAnaruoli]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('RUODES');
                        $ita_grid01->setSortOrder('asc');
                        if (!$ita_grid01->getDataPage('json')) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo la ricerca
                            Out::hide($this->divGes);
                            Out::hide($this->divRic);
                            Out::show($this->divRis);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridAnaruoli);
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::attributo($this->nameForm . '_ANARUOLI[RUOCOD]', 'readonly', '1');
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANARUOLI[RUOCOD]');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $anaruoli_rec = $this->proLib->getAnaruoli($_POST[$this->nameForm . '_ANARUOLI']['RUOCOD']);
                        if (!$anaruoli_rec) {
                            $anaruoli_rec = $_POST[$this->nameForm . '_ANARUOLI'];
                            $insert_Info = 'Oggetto: ' . $anaruoli_rec['RUOCOD'] . " " . $anaruoli_rec['RUODES'];
                            if ($this->insertRecord($this->PROT_DB, 'ANARUOLI', $anaruoli_rec, $insert_Info)) {
                                Out::msgInfo("Registrazione Ruolo.", "Ruolo registrato correttamente.");
                                $anaruoli_rec = $this->proLib->getAnaruoli($anaruoli_rec['RUOCOD']);
                                $this->Dettaglio($anaruoli_rec['ROWID']);
                            }
                        } else {
                            Out::msgInfo("Attenzione!", "Codice già  presente. Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_ANARUOLI[RUOCOD]');
                        }
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $anaruoli_rec = $_POST[$this->nameForm . '_ANARUOLI'];
                        $update_Info = 'Oggetto: ' . $anaruoli_rec['RUOCOD'] . " " . $anaruoli_rec['RUODES'];
                        if ($this->updateRecord($this->PROT_DB, 'ANARUOLI', $anaruoli_rec, $update_Info)) {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_Cancella':
                        $codice = $_POST[$this->nameForm . '_ANARUOLI']['RUOCOD'];
                        $sql = "SELECT UFFFI1__2 FROM UFFDES WHERE UFFFI1__2='$codice'";
                        $uffdes_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);

                        // Controllo se è usato in altre anagrafiche e nelle procedure
                        if ($uffdes_tab != null) {
                            Out::msgStop("Attenzione!", 'Impossibile cancellare il Ruolo perché è assegnata ad altre Anagrafiche o Procedure.');
                        } else {
                            Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_ConfermaCancella':

                        $codice = $_POST[$this->nameForm . '_ANARUOLI']['RUOCOD'];
                        $sql = "SELECT UFFFI1__2 FROM UFFDES WHERE UFFFI1__2='$codice'";
                        $uffdes_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);

                        // Controllo se è usato in altre anagrafiche e nelle procedure
                        if ($uffdes_tab != null) {
                            Out::msgStop("Attenzione!", 'Impossibile cancellare il Ruolo perché è assegnata ad altre Anagrafiche o Procedure.');
                        } else {
                            $delete_Info = 'Oggetto: ' . $_POST[$this->nameForm . '_ANARUOLI']['RUOCOD'] . " " . $_POST[$this->nameForm . '_ANARUOLI']['RUODES'];
                            if ($this->deleteRecord($this->PROT_DB, 'ANARUOLI', $_POST[$this->nameForm . '_ANARUOLI']['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
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
                    case $this->nameForm . '_Codice':
                        $codice = $_POST[$this->nameForm . '_Codice'];
                        $anaruoli_rec = $this->proLib->getAnaruoli($codice);
                        if ($anaruoli_rec) {
                            $this->Dettaglio($anaruoli_rec['ROWID']);
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
        if ($close) {
            $this->close();
        }
    }

    public function OpenRicerca() {
        Out::show($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Codice');
        TableView::disableEvents($this->gridAnaruoli);
        TableView::clearGrid($this->gridAnaruoli);
    }

    function AzzeraVariabili() {
        Out::valore($this->nameForm . '_ANARUOLI[ROWID]', '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
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

    public function CreaSql() {
        // Importo l'ordinamento del filtro
        $sql = "SELECT * FROM ANARUOLI WHERE ROWID=ROWID";
        if ($_POST[$this->nameForm . '_Codice'] != "") {
            $sql .= " AND ".$this->PROT_DB->strUpper('RUOCOD')." LIKE '%" . strtoupper($_POST[$this->nameForm . '_Codice']) . "%'";
        }
        if ($_POST[$this->nameForm . '_Descrizione'] != "") {
            $sql .= " AND ".$this->PROT_DB->strUpper('RUODES')." LIKE '%" . strtoupper(addslashes($_POST[$this->nameForm . '_Descrizione'])) . "%'";
        }
        return $sql;
    }

    public function Dettaglio($rowid) {
        $anaruoli_rec = $this->proLib->getAnaruoli($rowid, 'rowid');
        $open_Info = 'Oggetto: ' . $anaruoli_rec['RUOCOD'] . " " . $anaruoli_rec['RUODES'];
        $this->openRecord($this->PROT_DB, 'ANARUOLI', $open_Info);
        $this->Nascondi();
        Out::valori($anaruoli_rec, $this->nameForm . '_ANARUOLI');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_TornaElenco');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        Out::attributo($this->nameForm . '_ANARUOLI[RUOCOD]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_ANARUOLI[RUODES]');
        TableView::disableEvents($this->gridAnaruoli);
    }

}

?>
