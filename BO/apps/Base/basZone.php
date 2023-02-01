<?php

/**
 *
 * Archivio Vie
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    03.05.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once './apps/Base/basLib.class.php';

function basZone() {
    $basZone = new basZone();
    $basZone->parseEvent();
    return;
}

class basZone extends itaModel {

    public $BASE_DB;
    public $basLib;
    public $nameForm = "basZone";
    public $divGes = "basZone_divGestione";
    public $divRis = "basZone_divRisultato";
    public $divRic = "basZone_divRicerca";
    public $gridZone = "basZone_gridZone";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->basLib = new basLib();
            $this->BASE_DB = $this->basLib->getBASEDB();
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
                Out::valore($this->nameForm . '_ANA_COMUNE[ANACAT]', 'ZONE');
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridZone:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridZone:
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
                $ita_grid01 = new TableView($this->gridZone, array(
                    'sqlDB' => $this->BASE_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('ANADES');
                $ita_grid01->exportXLS('', 'zone.xls');
                break;
            case 'printTableToHTML':
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql() . ' ORDER BY ANADES', "Ente" => '', 'Colonna3' => 'Zona');
                $itaJR->runSQLReportPDF($this->BASE_DB, 'basZone', $parameters);
                break;
            case 'onClickTablePager':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->BASE_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $ita_grid01->getDataPage('json');
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridZone, array(
                            'sqlDB' => $this->BASE_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridZone]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('ANADES');
                        $ita_grid01->setSortOrder('asc');
                        if (!$ita_grid01->getDataPage('json')) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo il risultato
                            Out::hide($this->divGes);
                            Out::hide($this->divRic);
                            Out::show($this->divRis);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridZone);
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        Out::attributo($this->nameForm . '_ANA_COMUNE[ANACOD]', 'readonly', '1');
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_Progressivo');
                        Out::setFocus('', $this->nameForm . '_ANA_COMUNE[ANACOD]');
                        break;
                    case $this->nameForm . '_Progressivo':
                        for ($i = 1; $i <= 999999; $i++) {
                            $progressivo = str_pad($i, 6, "0", STR_PAD_LEFT);
                            $zone_rec = $this->basLib->getComana($_POST[$this->nameForm . '_ANA_COMUNE']['ANACAT'], 'codice', $progressivo);
                            if (!$zone_rec) {
                                Out::valore($this->nameForm . '_ANA_COMUNE[ANACOD]', $progressivo);
                                Out::setFocus('', $this->nameForm . '_ANA_COMUNE[ANADES]');
                                break;
                            }
                        }
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_ANA_COMUNE']['ANACOD'];
                        $zone_rec = $this->basLib->getComana($_POST[$this->nameForm . '_ANA_COMUNE']['ANACAT'], 'codice', $codice);
                        if (!$zone_rec) {
                            $zone_rec = $_POST[$this->nameForm . '_ANA_COMUNE'];
                            $insert_Info = 'Oggetto: ' . $zone_rec['ANACOD'] . " " . $zone_rec['ANADES'];
                            if ($this->insertRecord($this->BASE_DB, 'ANA_COMUNE', $zone_rec, $insert_Info)) {
                                $this->OpenRicerca();
                            }
                        } else {
                            Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_ANA_COMUNE[ANACOD]');
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $zone_rec = $_POST[$this->nameForm . '_ANA_COMUNE'];
                        $update_Info = 'Oggetto: ' . $zone_rec['ANACOD'] . " " . $zone_rec['ANADES'];
                        if ($this->updateRecord($this->BASE_DB, 'ANA_COMUNE', $zone_rec, $update_Info)) {
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
                        $zone_rec = $_POST[$this->nameForm . '_ANA_COMUNE'];
                        $delete_Info = 'Oggetto: ' . $zone_rec['ANACOD'] . " " . $zone_rec['ANADES'];
                        if ($this->deleteRecord($this->BASE_DB, 'ANA_COMUNE', $zone_rec['ROWID'], $delete_Info)) {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_Torna':
                        Out::hide($this->divGes);
                        Out::hide($this->divRic);
                        Out::show($this->divRis);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_Nuovo');
                        Out::setFocus('', $this->nameForm . '_Nuovo');
                        TableView::enableEvents($this->gridZone);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Codice':
                        $codice = $_POST[$this->nameForm . '_Codice'];
                        if ($codice != '') {
                            $codice = str_pad($codice, 6, "0", STR_PAD_LEFT);
                            $zone_rec = $this->basLib->getComana($_POST[$this->nameForm . '_ANA_COMUNE']['ANACAT'], 'codice', $codice);
                            if ($zone_rec) {
                                $this->Dettaglio($zone_rec['ROWID']);
                            }
                        }
                        break;
                    case $this->nameForm . '_ANA_COMUNE[ANACOD]':
                        $codice = $_POST[$this->nameForm . '_ANA_COMUNE']['ANACOD'];
                        if ($codice != '') {
                            $codice = str_pad($codice, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_ANA_COMUNE[ANACOD]', $codice);
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
    }

    function OpenRicerca() {
        Out::show($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Codice');
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::valore($this->nameForm . '_ANA_COMUNE[ROWID]', '');
        TableView::disableEvents($this->gridZone);
        TableView::clearGrid($this->gridZone);
    }

    function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Progressivo');
        Out::hide($this->nameForm . '_Torna');
    }

    function CreaSql() {
        $sql = "SELECT * FROM ANA_COMUNE WHERE ANACAT='" . $_POST[$this->nameForm . '_ANA_COMUNE']['ANACAT'] . "'";
        if ($_POST[$this->nameForm . '_Codice'] != "") {
            $sql .= " AND ANACOD = '" . $_POST[$this->nameForm . '_Codice'] . "'";
        }
        if ($_POST[$this->nameForm . '_Descrizione'] != "") {
            $sql .= " AND ANADES LIKE '%" . addslashes($_POST[$this->nameForm . '_Descrizione']) . "%'";
        }
        App::log($sql);
        return $sql;
    }

    function Dettaglio($indice) {
        $zone_rec = $this->basLib->getComana($indice, 'rowid');
        $open_Info = 'Oggetto: ' . $zone_rec['ANACOD'] . " " . $zone_rec['ANADES'];
        $this->openRecord($this->BASE_DB, 'COMANA', $open_Info);
        $this->Nascondi();
        Out::valori($zone_rec, $this->nameForm . '_ANA_COMUNE');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        Out::attributo($this->nameForm . '_ANA_COMUNE[ANACOD]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_ANA_COMUNE[ANADES]');
        TableView::disableEvents($this->gridZone);
        $styleDiv = "border:1px solid black; display:inline-block; width:12px; height:12px;";
        if ($zone_rec['ANAFI1__1'] != '') {
            $colore = "rgb(" . $zone_rec['ANAFI1__1'] . ")";
            Out::html($this->nameForm . '_divColore', "<div style=\" margin-left:10px; $styleDiv background-color:$colore \"></div>");
        } else {
            Out::html($this->nameForm . '_divColore', "<div style=\" margin-left:10px; $styleDiv background-color:#ffffff \"></div>");
        }
    }

}

?>