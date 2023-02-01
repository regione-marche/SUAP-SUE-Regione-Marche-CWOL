<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @author     Carlo Iesari <carlo@iesari.me>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    13.10.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibCalendar.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/lib/itaPHPCore/CalendarView.class.php';
include_once ITA_BASE_PATH . '/lib/itaPHPCore/itaDate.class.php';

function envFullCalendarPopUp() {
    $envFullCalendarPopUp = new envFullCalendarPopUp();
    $envFullCalendarPopUp->parseEvent();
    return;
}

class envFullCalendarPopUp extends itaModel {

    public $ITALWEB_DB;
    public $envLibCalendar;
    public $utiEnte;
    public $eventi;
    public $nameForm = "envFullCalendarPopUp";
    public $divGes = "envFullCalendarPopUp_divGestione";
    public $gridEventi = "envFullCalendarPopUp_gridEventi";
    public $itaDate;

    function __construct() {
        parent::__construct();
        try {
            $this->envLibCalendar = new envLibCalendar();
            $this->itaDate = new itaDate();
            $this->utiEnte = new utiEnte();
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            $this->eventi = App::$utente->getKey($this->nameForm . '_eventi');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_eventi', $this->eventi);
        }
    }

    public function setArray($eventi) {
        $this->eventi = $eventi;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->Open();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'dbClickRow':
                foreach ($this->eventi as $evento) {
                    if ($evento['ROWID'] == $_POST['rowid']){
                        switch ($evento['TAB_GENITORE']){
                            case 'CAL_EVENTI':
                                $model = "envFullCalendarEvent";
                                itaLib::openDialog($model);
                                $formObj = itaModel::getInstance($model);
                                if (!$formObj) {
                                    Out::msgStop("Errore", "apertura evento fallita");
                                    break;
                                }
                                $formObj->setReturnModel($this->nameForm);
                                $formObj->setReturnEvent('returnCalendarEvent');
                                $formObj->setReturnId('');
                                $formObj->setRowid($evento['ROWID_GENITORE']);
                                $formObj->setAtCloseExe('AGGIORNADAPOPUP');
                                $formObj->setEvent('openform');
                                $formObj->parseEvent();
                                break;
                            case 'CAL_ATTIVITA':
                                $model = "envFullCalendarTodo";
                                itaLib::openDialog($model);
                                $formObj = itaModel::getInstance($model);
                                if (!$formObj) {
                                    Out::msgStop("Errore", "apertura dettaglio fallita");
                                    break;
                                }
                                $formObj->setReturnModel($this->nameForm);
                                $formObj->setReturnEvent('returnCalendarTodo');
                                $formObj->setRowid($evento['ROWID_GENITORE']);
                                $formObj->setAtCloseExe('AGGIORNADAPOPUP');
                                $formObj->setReturnId('');
                                $formObj->setEvent('openform');
                                $formObj->parseEvent();
                                break;
                        }
                    }
                }
                
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->envLibCalendar->checkPromemoriaCalendarioQuery('popup','sql'), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->ITALWEB_DB, 'envFullCalendarPopup', $parameters);
                break;
            
            case 'exportTableToExcel':
                $sql = $this->envLibCalendar->checkPromemoriaCalendarioQuery('popup','sql');
                $ita_grid01 = new TableView($this->gridTodo, array('sqlDB' => $this->ITALWEB_DB, 'sqlQuery' => $sql));
                $ita_grid01->exportXLS('', 'ElencoScadenze.xls');
                break;
            
                
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_eventi');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function Open() {
        $this->elaboraRecord();
        
        $ita_grid01 = new TableView(
                        $this->gridEventi, array('arrayTable' => $this->eventi, 'rowIndex' => 'idx')
        );
        $ita_grid01->setPageRows(1000);
        $ita_grid01->setSortIndex('GIORNI');
        $ita_grid01->setSortOrder('desc');
        $ita_grid01->getDataPage('json', true);
        TableView::enableEvents($this->gridEventi);

        foreach ($ita_grid01->getDataArray() as $scadenza) {
            TableView::setCellValue($this->gridEventi, $scadenza['ROWID'], "CALENDARIO", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
            TableView::setCellValue($this->gridEventi, $scadenza['ROWID'], "START", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
            TableView::setCellValue($this->gridEventi, $scadenza['ROWID'], "END", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
            TableView::setCellValue($this->gridEventi, $scadenza['ROWID'], "TITOLO", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
            TableView::setCellValue($this->gridEventi, $scadenza['ROWID'], "DESCRIZIONE", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
        }
    }
    
    public function elaboraRecord(){
        foreach ($this->eventi as $key => $evento) {
            $gg = $evento['NUMEROGIORNI'];
            $opacity1 = (($gg >= 10) ? (100 / $gg) : 100) / 100;
            $opacity = "background:rgba(255,0,0,$opacity1);";
            $this->eventi[$key]['GIORNI'] = '<div style="height:100%;padding-left:2px;text-align:center;' . $opacity . '"><span style="vertical-align:middle;opacity:1.00;">' . $gg . '</span></div>';
            if ($evento['START'] != ''){
                $this->eventi[$key]['START'] = substr($evento['START'], 6, 2) . '/' . substr($evento['START'], 4, 2) . '/' . substr($evento['START'], 0, 4);
            }
            if ($evento['END'] != ''){
                $this->eventi[$key]['END'] = substr($evento['END'], 6, 2) . '/' . substr($evento['END'], 4, 2) . '/' . substr($evento['END'], 0, 4);
            }
            $this->eventi[$key]['DESCRIZIONE'] = "<div style =\"height:65px;overflow:auto;\" class=\"ita-Wordwrap\"><div>" . $evento['DESCRIZIONE'] . "</div></div>";
        }
    }
    

}

?>