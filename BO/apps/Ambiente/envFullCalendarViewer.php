<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @author     Antimo Panetta 
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    21.10.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibCalendar.class.php';
include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/lib/itaPHPCore/CalendarView.class.php';

function envFullCalendarViewer() {
    $envFullCalendarViewer = new envFullCalendarViewer();
    $envFullCalendarViewer->parseEvent();
    return;
}

class envFullCalendarViewer extends itaModel {

    public $ITALWEB_DB;
    public $envLibCalendar;
    public $envLib;
    public $utiEnte;
    public $calendarioRowid;
    public $nameForm = "envFullCalendarViewer";
    public $divGes = "envFullCalendarViewer_divGestione";
    public $gridEventi = "envFullCalendarViewer_gridEventi";
    public $gridAttivita = "envFullCalendarViewer_gridAttivita";
    public $sql = array();

    function __construct() {
        parent::__construct();
        try {
            $this->envLibCalendar = new envLibCalendar();
            $this->envLib = new envLib();
            $this->utiEnte = new utiEnte();
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            $this->calendarioRowid = App::$utente->getKey($this->nameForm . '_calendarioRowid');
            $this->sql = App::$utente->getKey($this->nameForm . '_sql');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_calendarioRowid', $this->calendarioRowid);
            App::$utente->setKey($this->nameForm . '_sql', $this->sql);
        }
    }

    public function setRowid($rowid) {
        $this->calendarioRowid = $rowid;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->Open();
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CLASSIFICAZIONE_E':
                        $this->tabellaEventi();
                        break;

                    case $this->nameForm . '_CLASSIFICAZIONE_A':
                        $this->tabellaAttivita();
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'exportTableToExcel':
                switch ($_POST['id']) {
                    case $this->gridEventi:
                        $Dati = $this->arrayEventiAttivita('CAL_EVENTI');
                        if ($Dati) {
                            $this->envLib->ExportToExcel('StampaEventi', $Dati);
                        }
                        break;
                    case $this->gridAttivita:
                        $Dati = $this->arrayEventiAttivita('CAL_ATTIVITA');
                        if ($Dati) {
                            $this->envLib->ExportToExcel('StampaAttivita', $Dati);
                        }
                        break;
                }
                break;

            case 'printTableToHTML':
                switch ($_POST['id']) {
                    case $this->gridEventi:
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $utiEnte = new utiEnte();
                        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                        $this->arrayEventiAttivita('CAL_EVENTI');
                        $parameters = array("Sql" => $this->sql, "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                        $itaJR->runSQLReportPDF($this->ITALWEB_DB, 'envCalendarEventi', $parameters);
                        break;
                    case $this->gridAttivita:
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $utiEnte = new utiEnte();
                        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                        $this->arrayEventiAttivita('CAL_ATTIVITA');
                        $parameters = array("Sql" => $this->sql, "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                        $itaJR->runSQLReportPDF($this->ITALWEB_DB, 'envCalendarAttivita', $parameters);
                        break;
                }
                break;

            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAttivita:
                        $id = $_POST['rowid'];
                        if ($this->envLibCalendar->isTodoEditable($id)) {
                            $model = "envFullCalendarTodo";
                            itaLib::openDialog($model);
                            $formObj = itaModel::getInstance($model);
                            if (!$formObj) {
                                Out::msgStop("Errore", "Apertura dettaglio fallita");
                                break;
                            }
                            $formObj->setRowid($id);
                            $formObj->setEvent('openform');
                            $formObj->parseEvent();
                        } else {
                            Out::msgInfo("", "Non hai i permessi per modificare quest'attività.");
                        }
                        break;

                    case $this->gridEventi:
                        $id = $_POST['rowid'];
                        if ($this->envLibCalendar->isEventEditable($id)) {
                            $model = "envFullCalendarEvent";
                            itaLib::openDialog($model);
                            $formObj = itaModel::getInstance($model);
                            if (!$formObj) {
                                Out::msgStop("Errore", "Apertura dettaglio fallita");
                                break;
                            }
                            $formObj->setRowid($id);
                            $formObj->setEvent('openform');
                            $formObj->parseEvent();
                        } else {
                            Out::msgInfo("", "Non hai i permessi per modificare quest'attività.");
                        }
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridEventi:
                        $this->tabellaEventi();
                        break;
                    case $this->gridAttivita:
                        $this->tabellaAttivita();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_calendarioRowid');
        App::$utente->removeKey($this->nameForm . '_sql');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function Open() {
        $this->tabellaEventi();
        $this->tabellaAttivita();
        $this->selectClassificazione();
    }

    public function selectClassificazione() {
        $sql = "SELECT * FROM ENV_TIPI";
        $tipi_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);

        Out::select($this->nameForm . '_CLASSIFICAZIONE_E', 1, '', 1, '');
        Out::select($this->nameForm . '_CLASSIFICAZIONE_A', 1, '', 1, '');

        foreach ($tipi_tab as $tipi_rec) {
            Out::select($this->nameForm . '_CLASSIFICAZIONE_E', 1, $tipi_rec['CODICE'], 0, $tipi_rec['DESCRIZIONE']);
            Out::select($this->nameForm . '_CLASSIFICAZIONE_A', 1, $tipi_rec['CODICE'], 0, $tipi_rec['DESCRIZIONE']);
        }
    }

    public function tabellaEventi() {
        $pagina = 1;
        if ($_POST['page']) {
            $pagina = $_POST['page'];
        }
        $ita_grid01 = new TableView(
                $this->gridEventi, array('arrayTable' => $this->arrayEventiAttivita('CAL_EVENTI'), 'rowIndex' => 'idx')
        );
        $ita_grid01->setPageNum($pagina);
        $ita_grid01->setSortIndex('START');
        $ita_grid01->setSortOrder('desc');
        $ita_grid01->getDataPage('json', true);
        TableView::enableEvents($this->gridEventi);
    }

    public function tabellaAttivita() {
        $pagina = 1;
        if ($_POST['page']) {
            $pagina = $_POST['page'];
        }
        $ita_grid01 = new TableView(
                $this->gridAttivita, array('arrayTable' => $this->arrayEventiAttivita('CAL_ATTIVITA'), 'rowIndex' => 'idx')
        );
        $ita_grid01->setPageNum($pagina);
        $ita_grid01->setSortIndex('START');
        $ita_grid01->setSortOrder('desc');
        $ita_grid01->getDataPage('json', true);
        TableView::enableEvents($this->gridAttivita);
    }

    public function arrayEventiAttivita($tabella) {
        $sql = "SELECT * FROM $tabella WHERE ROWID_CALENDARIO = " . $this->calendarioRowid;
        if ($_POST['_search'] == 'true') {
            switch ($tabella) {
                case "CAL_EVENTI":
                    $titolo = $_POST['TITOLO_E'];
                    $descrizione = $_POST['DESCRIZIONE_E'];
                    $start = $_POST['START_E'];
                    $end = $_POST['END_E'];
                    break;
                case "CAL_ATTIVITA":
                    $titolo = $_POST['TITOLO_A'];
                    $descrizione = $_POST['DESCRIZIONE_A'];
                    $start = $_POST['START_A'];
                    $end = $_POST['END_A'];
                    break;
            }
            if ($titolo) {
                $sql .= " AND TITOLO LIKE '%" . $titolo . "%' ";
            }
            if ($descrizione) {
                $sql .= " AND DESCRIZIONE LIKE '%" . $descrizione . "%' ";
            }
            if ($start) {
                $sql .= " AND START LIKE '%" . substr($start, 6, 4) . substr($start, 3, 2) . substr($start, 0, 2) . "%' ";
            }
            if ($end) {
                $sql .= " AND END LIKE '%" . substr($end, 6, 4) . substr($end, 3, 2) . substr($end, 0, 2) . "%' ";
            }
        }

        switch ($tabella) {
            case "CAL_EVENTI":
                if ($_POST[$this->nameForm . '_CLASSIFICAZIONE_E']) {
                    $sql .= " AND CLASSEVENTO = '" . $_POST[$this->nameForm . '_CLASSIFICAZIONE_E'] . "'";
                }
                break;
            case "CAL_ATTIVITA":
                if ($_POST[$this->nameForm . '_CLASSIFICAZIONE_A']) {
                    $sql .= " AND CLASSEVENTO = '" . $_POST[$this->nameForm . '_CLASSIFICAZIONE_A'] . "'";
                }
                break;
        }
        $this->sql = $sql;
        $tabEventiAttivita = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);
        foreach ($tabEventiAttivita as $key => $evento) {
            switch ($tabella) {
                case "CAL_EVENTI":
                    $tabEventiAttivita[$key]['TITOLO_E'] = $evento['TITOLO'];
                    $tabEventiAttivita[$key]['DESCRIZIONE_E'] = $evento['DESCRIZIONE'];
                    $tabEventiAttivita[$key]['START_E'] = substr($evento['START'], 6, 2) . '/' . substr($evento['START'], 4, 2) . '/' . substr($evento['START'], 0, 4);
                    $tabEventiAttivita[$key]['END_E'] = substr($evento['END'], 6, 2) . '/' . substr($evento['END'], 4, 2) . '/' . substr($evento['END'], 0, 4);
                    break;
                case "CAL_ATTIVITA":
                    $tabEventiAttivita[$key]['TITOLO_A'] = $evento['TITOLO'];
                    $tabEventiAttivita[$key]['DESCRIZIONE_A'] = $evento['DESCRIZIONE'];
                    $tabEventiAttivita[$key]['START_A'] = substr($evento['START'], 6, 2) . '/' . substr($evento['START'], 4, 2) . '/' . substr($evento['START'], 0, 4);
                    $tabEventiAttivita[$key]['END_A'] = substr($evento['END'], 6, 2) . '/' . substr($evento['END'], 4, 2) . '/' . substr($evento['END'], 0, 4);
                    break;
            }
        }
        return$tabEventiAttivita;
    }

}

?>