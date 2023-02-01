<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// !!-- parametrizzare percorso --!! //
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';

function accAuditing() {
    $accAuditing = new accAuditing();
    $accAuditing->parseEvent();
    return;
}

class accAuditing extends itaModel {

    public $DBPARA_DB;
    public $nameForm = "accAuditing";
    public $gridAccessi = "accAuditing_gridAccessi";
    public $divGes = "accAuditing_divGestione";
    public $divRic = "accAuditing_divRicerca";//
    public $workDate;

    function __construct() {
        parent::__construct();
        $this->accLib = new accLib();
        // Apro il DB
        $this->DBPARA_DB = $this->accLib->getDBPARA();
        $this->workDate = date('Ymd');
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CreaCombo();
                $this->OpenRicerca();
                break;
            case 'exportTableToExcel':
                switch ($_POST['id']) {
                    case $this->gridAccessi:
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridAccessi,
                                        array(
                                            'sqlDB' => $this->DBPARA_DB,
                                            'sqlQuery' => $sql));
                        $ita_grid01->setSortIndex('OPEDAT');
                        $ita_grid01->exportXLS('', 'Operaz.xls');
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAccessi:
                        $sql = $this->CreaSql();
                        if ($sql != false) {
                            $ordinamento = $_POST['sidx'];
                            $ita_grid01 = new TableView($this->gridAccessi,
                                            array('sqlDB' => $this->DBPARA_DB,
                                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum($_POST['page']);
                            $ita_grid01->setPageRows($_POST['rows']);
                            $ita_grid01->setSortIndex($ordinamento);
                            $ita_grid01->setSortOrder($_POST['sord']);
                            $ita_grid01->getDataPage('json');
                            Out::setFocus('', $this->nameForm . '_AltraRicerca');
                        }
                        break;
                }
                break;
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridAccessi:
                        $Operaz_rec = $this->accLib->GetOperaz($_POST['rowid']);
                        if ($Operaz_rec['OPEEST']) {
                            Out::msgInfo('Descrizione:', $Operaz_rec['OPEEST']);
                        }
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $this->Elenca();
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->nascondi();
                        Out::show($this->divRic);
                        Out::show($this->nameForm . "_Elenca");
                        Out::setFocus('', $this->nameForm . '_Da_operazione');
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'printTableToHTML':
                include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
                $utiEnte = new utiEnte();
                $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $sql = $this->CreaSql();
                $parameters = array("Sql" => $sql, "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->DBPARA_DB, 'accAuditing', $parameters);
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

    function Elenca() {
        try {
            $sql = $this->CreaSql();
            if ($sql != false) {
                $ita_grid01 = new TableView($this->gridAccessi,
                                array(
                                    'sqlDB' => $this->DBPARA_DB,
                                    'sqlQuery' => $sql));
                $ita_grid01->setPageNum(1);
                $ita_grid01->setPageRows($_POST[$this->gridAccessi]['gridParam']['rowNum']);
                $ita_grid01->setSortIndex('OPEDAT');
                $ita_grid01->setSortOrder('asc');
                if (!$ita_grid01->getDataPage('json')) {
                    Out::msgStop("Selezione", "Nessun record trovato.");
                } else {   // Visualizzo la ricerca
                    $this->nascondi();
                    Out::show($this->divGes);
                    Out::show($this->nameForm . "_AltraRicerca");
                    TableView::enableEvents($this->gridAccessi);
                }
            }
        } catch (Exception $e) {
            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
            App::log($e->getMessage());
        }
    }

    function CreaSql() {
        $sql = "SELECT * FROM OPERAZ";
        $da = $_POST[$this->nameForm . "_Da_operazione"];
        if ($da == '') {
            $da = date('Y') . '0101';
        }
        $a = $_POST[$this->nameForm . "_A_operazione"];
        if ($a == '') {
            $a = $this->workDate;
        }
        $sql.=" WHERE (OPEDAT BETWEEN '$da' AND '$a')";
        if ($_POST[$this->nameForm . "_Opeuid"] <> '') {
            $sql.=" AND OPEUID='" . $_POST[$this->nameForm . "_Opeuid"] . "'";
        }
        if ($_POST[$this->nameForm . "_Opespidcode"] <> '') {
            $sql.=" AND OPESPIDCODE='" . $_POST[$this->nameForm . "_Opespidcode"] . "'";
        }
        if ($_POST[$this->nameForm . "_Opelog"] <> '') {
            $sql.=" AND OPELOG='" . $_POST[$this->nameForm . "_Opelog"] . "'";
        }
        if ($_POST[$this->nameForm . "_Opeiip"] <> '') {
            $sql.=" AND OPEIIP='" . $_POST[$this->nameForm . "_Opeiip"] . "'";
        }
        if ($_POST[$this->nameForm . "_Opedba"] <> '') {
            $sql.=" AND OPEDBA='" . $_POST[$this->nameForm . "_Opedba"] . "'";
        }
        if ($_POST[$this->nameForm . "_Opeprg"] <> '') {
            $sql.=" AND OPEPRG LIKE '%" . $_POST[$this->nameForm . "_Opeprg"] . "%'";
        }
        if ($_POST[$this->nameForm . "_Opedse"] <> '') {
            $sql.=" AND OPEDSE='" . $_POST[$this->nameForm . "_Opedse"] . "'";
        }
        if ($_POST[$this->nameForm . "_Opeest"] <> '') {
            $sql.=" AND OPEEST LIKE '%" . $_POST[$this->nameForm . "_Opeest"] . "%'";
        }
        if ($_POST[$this->nameForm . "_Opeope"] <> '') {
            $sql.=" AND OPEOPE='" . $_POST[$this->nameForm . "_Opeope"] . "'";
        }
        //filtro per nome ditta
        //$sql .= " AND OPEDIT = '" . App::$utente->getKey('ditta') . "'";
        return $sql;
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_Opeope', 1, "", "1", "TUTTI");
        Out::select($this->nameForm . '_Opeope', 1, "01", "0", "01 - ACCESSO");
        Out::select($this->nameForm . '_Opeope', 1, "02", "0", "02 - DBOPEN");
        Out::select($this->nameForm . '_Opeope', 1, "03", "0", "03 - DBCLOSE");
        Out::select($this->nameForm . '_Opeope', 1, "04", "0", "04 - DBPUT");
        Out::select($this->nameForm . '_Opeope', 1, "05", "0", "05 - DBDELETE");
        Out::select($this->nameForm . '_Opeope', 1, "06", "0", "06 - DBUPDATE");
        Out::select($this->nameForm . '_Opeope', 1, "07", "0", "07 - ERROR DBPUT");
        Out::select($this->nameForm . '_Opeope', 1, "08", "0", "08 - ERROR DBDELETE");
        Out::select($this->nameForm . '_Opeope', 1, "09", "0", "09 - ERROR DBUPDATE");
        Out::select($this->nameForm . '_Opeope', 1, "99", "0", "99 - SCARICO MAIL");
    }

    public function OpenRicerca() {
        Out::show($this->divRic, '', 200);
        TableView::disableEvents($this->gridAccessi);
        TableView::clearGrid($this->gridAccessi);
        $this->Nascondi();
        Out::show($this->divRic, 'slide', 100);
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '');
        Out::valore($this->nameForm . '_Da_operazione', date('Ymd', strtotime('-10 day', strtotime(date('Ymd')))));
        Out::setFocus('', $this->nameForm . '_Da_operazione');
    }

    public function Nascondi() {
        Out::hide($this->divRic);
        Out::hide($this->divGes);
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_AltraRicerca');
    }

}

?>
