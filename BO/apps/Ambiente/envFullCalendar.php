<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @author     Carlo Iesari <carlo@iesari.me>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    06.10.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibCalendar.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/lib/itaPHPCore/CalendarView.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

function envFullCalendar() {
    $envFullCalendar = new envFullCalendar();
    $envFullCalendar->parseEvent();
    return;
}

class envFullCalendar extends itaModel {

    public $ITALWEB_DB;
    public $utiEnte;
    public $envLibCalendar;
    public $nameForm = "envFullCalendar";
    public $calendarId = "envFullCalendar_fullCalendar";
    public $gridTodo = "envFullCalendar_gridTodo";

    function __construct() {
        parent::__construct();
        try {
            $this->utiEnte = new utiEnte();
            $this->envLibCalendar = new envLibCalendar();
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
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
            case 'openportlet':
                itaLib::openForm('envFullCalendar', '', true, $container = $_POST['context'] . "-content");
                Out::delContainer($_POST['context'] . "-wait");

            case 'openportletapp':
                //$this->popupRepeats();
                $this->checkRepeats();
                $prom_tab = $this->envLibCalendar->checkPromemoriaCalendarioPopup();
                $this->envLibCalendar->chiamaCalendarPopup($prom_tab);

                $this->clearGoogleCalendars();

                $this->loadModelConfig();
                $viewconf = $this->getCustomConfig('CALENDARIO/VIEW');
                if ($viewconf['view'] == 'todo') {
                    $this->OpenTodo();
                } else {
                    $this->OpenCalendar();
                }

                Out::codice('setItaTimer({element:"' . ($this->nameForm . '_timerCalendar') . '",delay: ' . (15 * 60 * 1000) . ', model:"' . $this->nameForm . '",async:true});');
                break;

            case 'ontimer':
                $id = $_POST['id'];
                switch ($id) {
                    case $this->nameForm . '_timerCalendar':
                        $itaCalendar = new CalendarView($this->calendarId);
                        $itaCalendar->refresh();
                        TableView::reload($this->gridTodo);
                        break;
                }
                break;

            case 'onCalendarSelect':
                //Out::msgInfo("onCalendarSelect", print_r($_POST['calendarParam'], true));
                break;

            case 'onCalendarChange':
                //Out::msgInfo("onCalendarChange", print_r($_POST['calendarParam'], true));
                $this->setCustomConfig("CALENDARIO/VIEW", array(
                    'view' => $_POST['calendarParam']['view'],
                    'lastCalView' => $_POST['calendarParam']['view']
                ));
                $this->saveModelConfig();
                break;

            case 'onCalendarFetch':
                //Out::msgInfo("onCalendarFetch", print_r($_POST['calendarParam'], true));
                $from = date('YmdHis', strtotime($_POST['calendarParam']['start']));
                $to = date('YmdHis', strtotime($_POST['calendarParam']['end'])) + 1000000;
                $itaCalendar = new CalendarView($this->calendarId);

                $selectedCalendars = $this->envLibCalendar->getSelectedCalendars();
                if ($selectedCalendars['GOOGLE']) {
                    foreach ($selectedCalendars['GOOGLE'] as $gCalendar) {
                        if ($gCalendar['visible'] && $gCalendar['open']) {
                            Out::codice("fullCalendarGoogle('" . $this->calendarId . "', " . json_encode($gCalendar) . ", '" . $_POST['calendarParam']['start'] . "', '" . $_POST['calendarParam']['end'] . "' );");
                        }
                    }
                }

                foreach ($selectedCalendars as $k => $cal) {
                    if (!$cal['visible']) {
                        unset($selectedCalendars[$k]);
                    }
                }
                unset($selectedCalendars['GOOGLE']);
                if ($selectedCalendars) {
                    $events = array();
                    foreach ($this->envLibCalendar->selectEvents($selectedCalendars, $from, $to) as $event) {
                        $event['color'] = $selectedCalendars[$event['calendar']]['color'];
                        array_push($events, $event);
                    }

                    $itaCalendar->addEventsArray($events);
                }

                break;

            case 'onCalendarEventChange':
                //Out::msgInfo("onCalendarEventChange", print_r($_POST['calendarParam'], true));
                if ($this->envLibCalendar->isEventEditable($_POST['calendarParam']['event']['id'])) {
                    $eventi_rec = array(
                        'ROWID' => $_POST['calendarParam']['event']['id'],
                        'START' => date('YmdHis', strtotime($_POST['calendarParam']['event']['start'])),
                        'END' => $_POST['calendarParam']['event']['end'] ? date('YmdHis', strtotime($_POST['calendarParam']['event']['end'])) : '',
                        'ALLDAY' => $_POST['calendarParam']['event']['allDay'] == 'true' ? 1 : 0
                    );
                    ItaDB::DBUpdate($this->ITALWEB_DB, 'CAL_EVENTI', 'ROWID', $eventi_rec);
                    $this->envLibCalendar->refreshPromemoria($eventi_rec['ROWID']);
                }
                $itaCalendar = new CalendarView($this->calendarId);
                $itaCalendar->refresh();
                break;

            case 'onCalendarEventClick':
                //Out::msgInfo("onCalendarEventClick", print_r($_POST['calendarParam'], true));
                if (is_numeric($_POST['calendarParam']['event']['id'])) {
                    if ($this->envLibCalendar->isEventEditable($_POST['calendarParam']['event']['id'])) {
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
                        $formObj->setRowid($_POST['calendarParam']['event']['id']);
                        $formObj->setAtCloseExe('');
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                    }
                }
                break;

            case 'onGoogleCalendarList':
                //Out::msgInfo("onGoogleCalendarList", print_r($_POST, true));

                $selectedCalendars = $this->envLibCalendar->getSelectedCalendars();
                if (!$selectedCalendars['GOOGLE']) {
                    $selectedCalendars['GOOGLE'] = Array();
                }
                foreach ($_POST['calendarList']['items'] as $gCalendar) {
                    $id = md5($gCalendar['id']);
                    if (!$selectedCalendars['GOOGLE'][$id]) {
                        $selectedCalendars['GOOGLE'][$id] = Array(
                            'id' => $id,
                            'gid' => $gCalendar['id'],
                            'name' => $gCalendar['summary'],
                            'visible' => true,
                            'color' => $gCalendar['backgroundColor'],
                            'icon' => '<span class="ui-icon ui-icon-google ui-icon-white" style="float: left;"></span>'
                        );
                    }
                    $selectedCalendars['GOOGLE'][$id]['open'] = true;

                    if ($selectedCalendars['GOOGLE'][$id]['visible'] == true) {
                        Out::codice("fullCalendarGoogle('" . $this->calendarId . "', " . json_encode($selectedCalendars['GOOGLE'][$id]) . " );");
                    }
                }
                $this->envLibCalendar->setSelectedCalendars($selectedCalendars);

                $itaCalendar = new CalendarView($this->calendarId);
                $itaCalendar->refresh();

                break;

            case 'onGoogleSignout':
                $this->clearGoogleCalendars();
                $itaCalendar = new CalendarView($this->calendarId);
                $itaCalendar->refresh();
                break;

            case 'broadcastMsg':
                switch ($_POST['message']) {
                    case 'AGGIORNACALENDARIO':
                        $itaCalendar = new CalendarView($this->calendarId);
                        $itaCalendar->refresh();
                        break;
                    case 'AGGIORNATODO':
                        TableView::reload($this->gridTodo);
                        break;
                    case 'AGGIORNADAPOPUP':
                        $prom_tab = $this->envLibCalendar->checkPromemoriaCalendarioPopup();
                        $this->envLibCalendar->chiamaCalendarPopup($prom_tab);
                        break;
                }
                break;

            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridTodo:
                        switch ($_POST['colName']) {
                            case 'COMPLETATO':
                                $id = $_POST['rowid'];
                                $todo_record = $this->envLibCalendar->getTodo($id);
                                if ($todo_record['COMPLETATO'] == '0') {
                                    $todo_record['COMPLETATO'] = '1';
                                } else {
                                    $todo_record['COMPLETATO'] = '0';
                                }
                                ItaDB::DBUpdate($this->ITALWEB_DB, 'CAL_ATTIVITA', 'ROWID', $todo_record);
                                TableView::reload($this->gridTodo);
                                //$this->creaGridTodo();
                                break;
                        }
                        break;
                }
                break;

            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridTodo:
                        $id = $_POST['rowid'];
                        if ($this->envLibCalendar->isTodoEditable($id)) {
                            $model = "envFullCalendarTodo";
                            itaLib::openDialog($model);
                            $formObj = itaModel::getInstance($model);
                            if (!$formObj) {
                                Out::msgStop("Errore", "apertura dettaglio fallita");
                                break;
                            }
                            $formObj->setReturnModel($this->nameForm);
                            $formObj->setReturnEvent('returnCalendarTodo');
                            $formObj->setRowid($id);
                            $formObj->setAtCloseExe('');
                            $formObj->setReturnId('');
                            $formObj->setEvent('openform');
                            $formObj->parseEvent();
                        } else {
                            Out::msgInfo("", "Non hai i permessi per modificare quest'attività.");
                        }
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridTodo:
                        $id = $_POST['rowid'];
                        if ($this->envLibCalendar->isTodoDeletable($id)) {
                            $model = "envFullCalendarTodo";
                            itaLib::openDialog($model);
                            $formObj = itaModel::getInstance($model);
                            if (!$formObj) {
                                Out::msgStop("Errore", "apertura dettaglio fallita");
                                break;
                            }
                            $formObj->setReturnModel($this->nameForm);
                            $formObj->setReturnEvent('returnCalendarTodo');
                            $formObj->setRowid($id);
                            $formObj->setAtCloseExe('');
                            $formObj->setReturnId('');
                            $formObj->setEvent('openform');
                            $formObj->parseEvent();

                            Out::msgQuestion("Elimina", "Confermi la Cancellazione?", array(
                                'F8-Annulla' => array('id' => $model . '_AnnullaCancella', 'model' => $model, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $model . '_ConfermaElimina', 'model' => $model, 'shortCut' => "f5")
                            ));
                        } else {
                            Out::msgStop("Errore", "Non hai i permessi per eliminare questa attività");
                        }
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridTodo:
                        $model = "envFullCalendarTodo";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "apertura dettaglio fallita");
                            break;
                        }
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnCalendarTodo');
                        $formObj->setAtCloseExe('');
                        $formObj->setReturnId('');
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                }
                break;

            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => ($this->CreaSql() . 'ORDER BY START ASC'), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->ITALWEB_DB, 'envFullCalendarTodo', $parameters);
                break;

            case 'exportTableToExcel':
                $sql = $this->creaSql();
                $ita_grid01 = new TableView($this->gridTodo, array('sqlDB' => $this->ITALWEB_DB, 'sqlQuery' => $sql));
                $ita_grid01->exportXLS('', 'Todolist.xls');
                break;

            case 'onClickTablePager':
                TableView::clearGrid($this->gridTodo);
                $sql = $this->creaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->ITALWEB_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $ita_grid01->getDataPageFromArray('json', $this->elaboraRecords($ita_grid01->getDataArray()));
                break;

            case 'onClick':
                switch ($_POST['id']) {

                    case $this->nameForm . '_openScadenze':
                        $prom_tab = $this->envLibCalendar->checkPromemoriaCalendarioPopup();
                        $this->envLibCalendar->chiamaCalendarPopup($prom_tab);
                        break;

                    case $this->nameForm . '_calendarNew':
                        $model = "envFullCalendarEvent";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "apertura dettaglio fallita");
                            break;
                        }
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnCalendarEvent');
                        $formObj->setAtCloseExe('');
                        $formObj->setReturnId('');
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;

                    case $this->nameForm . '_calendarSettings':
                        $model = "envFullCalendarSettings";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "apertura dettaglio fallita");
                            break;
                        }
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnCalendarSettings');
                        $formObj->setReturnId('');
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;

                    case $this->nameForm . '_Filtra':
                        $prio_arr = array();
                        if ($_POST[$this->nameForm . '_PRIO_1'])
                            array_push($prio_arr, '1');
                        if ($_POST[$this->nameForm . '_PRIO_2'])
                            array_push($prio_arr, '2');
                        if ($_POST[$this->nameForm . '_PRIO_3'])
                            array_push($prio_arr, '3');
                        $done = $_POST[$this->nameForm . '_COMPL'] ? true : false;

                        $this->setCustomConfig("CALENDARIO/TODOFILTERS", array(
                            'PRIOR' => $prio_arr,
                            'COMPL' => $done
                        ));
                        $this->saveModelConfig();
                        TableView::reload($this->gridTodo);
                        //$this->creaGridTodo();
                        break;

                    case $this->nameForm . '_openAttivita':
                        $this->loadModelConfig();
                        $viewconf = $this->getCustomConfig('CALENDARIO/VIEW');
                        $this->setCustomConfig("CALENDARIO/VIEW", array(
                            'view' => 'todo',
                            'lastCalView' => $viewconf['lastCalView']
                        ));
                        $this->saveModelConfig();
                        $this->OpenTodo();
                        break;

                    case $this->nameForm . '_Torna':
                        $this->OpenCalendar();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function clearGoogleCalendars() {
        $selectedCalendars = $this->envLibCalendar->getSelectedCalendars();
        if ($selectedCalendars['GOOGLE'])
            foreach ($selectedCalendars['GOOGLE'] as &$gCalendar) {
                $gCalendar['open'] = false;
            }
        $this->envLibCalendar->setSelectedCalendars($selectedCalendars);
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

    public function hide() {
        Out::hide($this->nameForm . '_fullCalendar');
        Out::hide($this->nameForm . '_todoList');
    }

    public function OpenTodo() {
        $this->hide();
        Out::show($this->nameForm . '_todoList');

        $this->loadModelConfig();
        $viewconf = $this->getCustomConfig('CALENDARIO/TODOFILTERS');
        if (!$viewconf['PRIOR'] && !$viewconf['COMPL']) {
            $viewconf = array(
                'PRIOR' => array(1, 2, 3),
                'COMPL' => false
            );
            $this->setCustomConfig("CALENDARIO/TODOFILTERS", $viewconf);
            $this->saveModelConfig();
        }

        Out::valore($this->nameForm . '_PRIO_1', '0');
        Out::valore($this->nameForm . '_PRIO_2', '0');
        Out::valore($this->nameForm . '_PRIO_3', '0');
        Out::valore($this->nameForm . '_COMPL', '0');
        foreach ($viewconf['PRIOR'] as $prior) {
            Out::valore($this->nameForm . "_PRIO_$prior", '1');
        }
        if ($viewconf['COMPL'])
            Out::valore($this->nameForm . '_COMPL', '1');

        //$this->creaGridTodo();
        TableView::enableEvents($this->gridTodo);
        TableView::reload($this->gridTodo);
    }

    public function creaGridTodo() {

        TableView::clearGrid($this->gridTodo);
        $sql = $this->creaSql();
        $ita_grid01 = new TableView($this->gridTodo, array(
            'sqlDB' => $this->ITALWEB_DB,
            'sqlQuery' => $sql
        ));
        $ita_grid01->setPageNum($_POST['page']);
        $ita_grid01->setPageRows($_POST[$this->gridTodo]['gridParam']['rowNum']);
        $ita_grid01->setSortIndex('START');
        $ita_grid01->setSortOrder('asc');
        $ita_grid01->getDataPageFromArray('json', $this->elaboraRecords($ita_grid01->getDataArray()));
    }

    private function creaSql() {
        $this->loadModelConfig();
        $viewconf = $this->getCustomConfig('CALENDARIO/TODOFILTERS');

        $sql = $this->envLibCalendar->getAllTodo($this->envLibCalendar->getSelectedCalendars(), $viewconf['PRIOR'], $viewconf['COMPL']);

        if ($_POST['_search'] == true) {
            if ($_POST['TITOLO']) {
                $sql .= " AND " . $this->ITALWEB_DB->strUpper('CAL_ATTIVITA.TITOLO') . " LIKE '%" . addslashes(strtoupper($_POST['TITOLO'])) . "%'";
            }

            if ($_POST['START']) {
                $str = preg_replace("/[^0-9]/", "", $_POST['START']);
                $sql .= " AND " . $this->ITALWEB_DB->strConcat($this->ITALWEB_DB->subString('START', 7, 2), $this->ITALWEB_DB->subString('START', 5, 2), $this->ITALWEB_DB->subString('START', 1, 4), $this->ITALWEB_DB->subString('START', 9, 6)) . " LIKE '%$str%'";
            }
        }

        return $sql;
    }

    function elaboraRecords($Result_tab) {
        $personalCalendars = $this->envLibCalendar->getSelectedCalendars();

        foreach ($Result_tab as &$Result_rec) {
            $tmp = substr_replace(substr_replace(substr_replace(substr_replace(substr_replace($Result_rec['START'], '/', 4, 0), '/', 7, 0), ' ', 10, 0), ':', 13, 0), ':', 16, 0);
            $Result_rec['START'] = date('d/m/Y H:i', strtotime($tmp));

            $Result_rec['ICONA'] = '<span class="ui-icon ui-icon-calendar"></span>';
            if ($Result_rec['UTENTE'] == App::$utente->getIdUtente()) {
                $Result_rec['ICONA'] = '<span class="ui-icon ui-icon-home"></span>';
            } else if (in_array($Result_rec['GRUPPO'], $this->envLibCalendar->sqlUsersGroups()) && substr($Result_rec['GRUPPI'], 0, 1) == '1') {
                $Result_rec['ICONA'] = '<span class="ui-icon ui-icon-person"></span>';
            }

            $Result_rec['COMPLETATO'] = $Result_rec['COMPLETATO'] == '1' ? 'Si' : 'No';

            $Result_rec['CALENDARIO'] = '<span style="margin: 0 8px 0 5px; width: 20px; height: 7px; display: inline-block; background-color: ' . $personalCalendars[$Result_rec['ROWID_CALENDARIO']]['color'] . ';"></span>' . $Result_rec['CALENDARIO'];

            $Result_rec['TITOLO'] = '<div class="ita-html"><span class="ita-tooltip" title="' . htmlspecialchars($Result_rec['DESCRIZIONE']) . '">' . htmlspecialchars($Result_rec['TITOLO']) . '</span></div>';

            switch ($Result_rec['PRIORITA']) {
                case '1':
                    $Result_rec['PRIORITA'] = 'bassa';
                    break;
                case '2':
                    $Result_rec['PRIORITA'] = 'normale';
                    break;
                case '3':
                    $Result_rec['PRIORITA'] = 'alta';
                    break;
            }

            foreach ($this->envLibCalendar->getPromemoriaFromTodo($Result_rec['ROWID']) as $prom) {
                if ($prom['INVIATO'] == 0)
                    $Result_rec['PROM'] = '<span class="ui-icon ui-icon-clock"></span>';
            }
        }
        return $Result_tab;
    }

    public function OpenCalendar() {
        $this->hide();
        Out::show($this->nameForm . '_fullCalendar');

        $this->loadModelConfig();
        $viewconf = $this->getCustomConfig('CALENDARIO/VIEW');
        $itaCalendar = new CalendarView($this->calendarId);
        $itaCalendar->changeView($viewconf['lastCalView']);
        $itaCalendar->refresh();
    }

    public function popupRepeats() {
        $selectedCalendars = $this->envLibCalendar->getSelectedCalendars();
        $calendars = array();

        foreach ($selectedCalendars as $k => $cal) {
            if (isset($cal['visible']) && $cal['visible']) {
                array_push($calendars, $k);
            }
        }

        $popups = array();

        $tables = array('CAL_EVENTI', 'CAL_ATTIVITA');

        foreach ($tables as $table) {
            $sql = "SELECT
                        $table.*,
                        CAL_CALENDARI.TITOLO AS CALENDARIO
                    FROM
                        $table
                    LEFT OUTER JOIN
                        CAL_CALENDARI
                    ON
                        CAL_CALENDARI.ROWID = $table.ROWID_CALENDARIO
                    WHERE
                        RIPETI = 1
                    AND
                        (
                            ( END != '' AND START <= " . date('YmdHis') . " AND END >= " . date('YmdHis') . " )
                        OR
                            ( END = '' AND START LIKE '" . date('Ymd') . "%' )
                        )
                    AND
                        ROWID_CALENDARIO IN ( " . implode(', ', $calendars) . " )";

            $repeat_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);

            $popups = array_merge($popups, $repeat_tab);
        }

        foreach ($popups as $popup) {
            $this->envLibCalendar->popupEventoAttivita($popup);
        }
    }

    public function checkRepeats() {
        $tables = array('CAL_EVENTI', 'CAL_ATTIVITA');

        foreach ($tables as $table) {
            $sql = "SELECT
                        *
                    FROM
                        $table
                    WHERE
                        RIPETI = 1
                    AND
                        (
                            ( END != '' AND " . $this->ITALWEB_DB->subString("END", 1, 8) . " < " . date('Ymd') . ")
                        OR
                            ( END = '' AND " . $this->ITALWEB_DB->subString("START", 1, 8) . " < " . date('Ymd') . ")
                    )";

            $repeat_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);

            foreach ($repeat_tab as $repeat_rec) {
                if ($repeat_rec['UNITA'] == '2592000') {
                    $new_start = $this->addMonthToDate($repeat_rec['START'], $repeat_rec['TEMPO']);
                    $new_end = '';

                    if ($repeat_rec['END']) {
                        $new_end = $this->addMonthToDate($repeat_rec['END'], $repeat_rec['TEMPO']);
                    }

                    if ($repeat_rec['TERMINA'] && substr($new_start, 0, 8) >= $repeat_rec['TERMINA']) {
                        $repeat_rec['RIPETI'] = 0;
                    } else {
                        $repeat_rec['START'] = $new_start;
                        $repeat_rec['END'] = $new_end;
                    }

                    try {
                        ItaDB::DBUpdate($this->ITALWEB_DB, $table, 'ROWID', $repeat_rec);
                    } catch (Exception $e) {
                        Out::msgStop("Errore", $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Aggiunge x mesi ad una data
     * @param type $date Formato YmdHis
     * @param type $mesi Numero di mesi
     * @return type Nuova data in formato YmdHis
     */
    public function addMonthToDate($data, $mesi) {
        $anno = substr($data, 0, 4);
        $mese = substr($data, 4, 2) + $mesi;
        $giorno = substr($data, 6);

        while ($mese > 12) {
            $mese -= 12;
            $anno++;
        }

        return str_pad($anno, 4, "0", STR_PAD_LEFT) . str_pad($mese, 2, "0", STR_PAD_LEFT) . $giorno;
    }

}

?>