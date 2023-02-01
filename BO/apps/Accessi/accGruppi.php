<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';

function accGruppi() {
    $accGruppi = new accGruppi();
    $accGruppi->parseEvent();
    return;
}

class accGruppi extends itaModel {

    public $ITW_DB;
    public $nameForm = "accGruppi";
    public $divGes = "accGruppi_divGestione";
    public $divRis = "accGruppi_divRisultato";
    public $divRic = "accGruppi_divRicerca";
    public $gridRisultato = "accGruppi_gridGruppi";

    function __construct() {
        parent::__construct();
        $this->accLib = new accLib();
        $this->ITW_DB = $this->accLib->getITW();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->OpenRicerca();
                TableView::disableEvents($this->gridRisultato);
                break;
            case 'dbClickRow':
                $this->Dettaglio($_POST['rowid']);
                break;
            case 'onClickTablePager':
                $sql = $this->CreaSql($_POST[$this->nameForm . '_Codice']);
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->ITW_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $ita_grid01->getDataPage('json');
                break;
            case 'onChange':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Nuovo':
                        Out::attributo($this->nameForm . '_GRUPPI[GRUCOD]', 'readonly', '1');
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::valore($this->nameForm . '_GRUPPI[ROWID]', '');
                        Out::valore($this->nameForm . '_GRUPPI[GRUCOD]', '');
                        Out::valore($this->nameForm . '_GRUPPI[GRUDES]', '');
                        Out::show($this->nameForm . '_Aggiungi');
//                        Out::hide($this->nameForm . '_Aggiorna');
//                        Out::hide($this->nameForm . '_Cancella');
                        Out::setFocus('', $this->nameForm . '_GRUPPI[GRUCOD]');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        //$gruppi_rec = $_POST[$this->nameForm . '_GRUPPI'];
                        if ($_POST[$this->nameForm . '_GRUPPI']['GRUCOD'] == '') {
                            $sql = "SELECT MAX(GRUCOD) AS ULTIMO FROM GRUPPI";
                            $gruppi_rec = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);
                            if (!$gruppi_rec) {
                                $ultimo = "1";
                            } else {
                                $ultimo = $gruppi_rec['ULTIMO'] + 1;
                            }
                            $gruppi_rec = $_POST[$this->nameForm . '_GRUPPI'];
                            $gruppi_rec['GRUCOD'] = $ultimo;
                            try {
                                $insert_Info = 'Oggetto: ' . $gruppi_rec['GRUCOD'] . " " . $gruppi_rec['GRUDES'];
                                if ($this->insertRecord($this->ITW_DB, 'GRUPPI', $gruppi_rec, $insert_Info)) {
                                    $this->OpenRicerca();
                                }
                            } catch (Exception $e) {
                                Out::msgStop("Errore di Inserimento su ANAGRAFICA GRUPPI.", $e->getMessage());
                            }
                        } else {
                            $sql = "SELECT * FROM GRUPPI WHERE GRUCOD='" . $_POST[$this->nameForm . '_GRUPPI']['GRUCOD'] . "'";
                            try {   // Effettuo la FIND
                                $gruppi_tab = ItaDB::DBSQLSelect($this->ITW_DB, $sql);
                                if (count($gruppi_tab) == 0) {
                                    $gruppi_rec = $_POST[$this->nameForm . '_GRUPPI'];
                                    $insert_Info = 'Oggetto: ' . $gruppi_rec['GRUCOD'] . " " . $gruppi_rec['GRUDES'];
                                    if ($this->insertRecord($this->ITW_DB, 'GRUPPI', $gruppi_rec, $insert_Info)) {
                                        $this->OpenRicerca();
                                    }
                                } else {
                                    Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                                    Out::setFocus('', $this->nameForm . '_GRUPPI[GRUCOD]');
                                }
                            } catch (Exception $e) {
                                Out::msgStop("Errore di Inserimento su ANAGRAFICA GRUPPI.", $e->getMessage());
                            }
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $gruppi_rec = $_POST[$this->nameForm . '_GRUPPI'];
                        $update_Info = 'Oggetto: ' . $gruppi_rec['GRUCOD'] . " " . $gruppi_rec['GRUDES'];
                        if ($this->updateRecord($this->ITW_DB, 'GRUPPI', $gruppi_rec, $update_Info)) {
                            //$this->OpenRicerca();
                            $sql = $this->CreaSql($_POST[$this->nameForm . '_Codice']);
                            $this->CaricaGriglia($sql);
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
                        $gruppi_rec = $_POST[$this->nameForm . '_GRUPPI'];
                        try {
                            $sql = "SELECT * FROM GRUPPI WHERE GRUCOD = '" . $_POST[$this->nameForm . '_GRUPPI']['GRUCOD'] . "'";
                            $gruppo_rec = ItaDB::DBSQLSelect($this->ITW_DB, $sql);
                            $delete_Info = 'Oggetto: ' . $gruppo_rec['GRUCOD'] . " " . $gruppo_rec['GRUDES'];
                            if ($this->deleteRecord($this->ITW_DB, 'GRUPPI', $gruppi_rec['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA GRUPPI", $e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_Torna':
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql($_POST[$this->nameForm . '_Codice']);
                        $this->CaricaGriglia($sql);
                        break;
                        $ita_grid01 = new TableView($this->gridRisultato, array(
                            'sqlDB' => $this->ITW_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridRisultato]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('GRUDES');
                        $ita_grid01->setSortOrder('asc');
                        if (!$ita_grid01->getDataPage('json')) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {
                            Out::hide($this->divGes, '', 0);
                            Out::hide($this->divRic, '', 0);
                            Out::show($this->divRis, '', 0);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Codice');
                            TableView::enableEvents($this->gridRisultato);
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Codice':
                        if ($_POST[$this->nameForm . '_Codice'] != '') {
                            $sql = "SELECT * FROM GRUPPI WHERE GRUCOD = '" . $_POST[$this->nameForm . '_Codice'] . "'";
                            $gruppi_rec = ItaDB::DBSQLSelect($this->ITW_DB, $sql);
                            if ($gruppi_rec) {
                                $this->Dettaglio($gruppi_rec[0]['ROWID']);
                            }
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

    public function OpenRicerca() {
        Out::valore($this->nameForm . '_Codice', '');
        Out::valore($this->nameForm . '_Descrizione ', '');
        Out::show($this->divRic, '', 200);
        Out::hide($this->divRis, '', 0);
        Out::hide($this->divGes, '', 200);
        TableView::disableEvents($this->gridRisultato);
        TableView::clearGrid($this->gridRisultato);
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '');
        Out::setFocus('', $this->nameForm . '_Codice');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Torna');
    }

    public function CreaSql($codice) {
        if ($codice <> 0) {
            $sql = "SELECT * FROM GRUPPI WHERE GRUCOD= '$codice'";
        } else {
            $sql = "SELECT * FROM GRUPPI";
        }
        return $sql;
    }

    public function Dettaglio($_Indice) {
        $gruppi_rec = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT * FROM GRUPPI WHERE ROWID='$_Indice'", false);
        $this->Nascondi();
        Out::valori($gruppi_rec, $this->nameForm . '_GRUPPI');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_Torna');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->divRic, '', 0);
        Out::hide($this->divRis, '', 0);
        Out::show($this->divGes, '', 0);
        Out::attributo($this->nameForm . '_GRUPPI[GRUCOD]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_GRUPPI[GRUDES]');
        TableView::disableEvents($this->gridAnacat);
    }

    public function CaricaGriglia($sql) {
        $ita_grid01 = new TableView($this->gridRisultato, array(
            'sqlDB' => $this->ITW_DB,
            'sqlQuery' => $sql));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows($_POST[$this->gridRisultato]['gridParam']['rowNum']);
        $ita_grid01->setSortIndex('GRUDES');
        $ita_grid01->setSortOrder('asc');
        if (!$ita_grid01->getDataPage('json')) {
            Out::msgStop("Selezione", "Nessun record trovato.");
            $this->OpenRicerca();
        } else {
            Out::hide($this->divGes, '', 0);
            Out::hide($this->divRic, '', 0);
            Out::show($this->divRis, '', 0);
            $this->Nascondi();
            Out::show($this->nameForm . '_AltraRicerca');
            Out::show($this->nameForm . '_Nuovo');
            Out::setFocus('', $this->nameForm . '_Codice');
            TableView::enableEvents($this->gridRisultato);
        }
    }

}

?>
