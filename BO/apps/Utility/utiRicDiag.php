<?php

/**
 *
 * DIALOG DI RICERCA
 *
 * PHP Version 5
 *
 * @category
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    20.11.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaComponents.class.php';

function utiRicDiag() {
    $utiRicDiag = new utiRicDiag();
    $utiRicDiag->parseEvent();
    return;
}

class utiRicDiag extends itaModel {

    public $GRID_DB;
    public $nameForm = "utiRicDiag";
    public $nameGrid = "utiRicDiag_gridRis";
    public $gridOptions;
    public $currArrayTable;
    public $returnModel;
    public $returnModelOrig;
    public $returnEvent;
    public $returnKey;
    public $returnColumn;
    public $returnID;
    public $keynavButtonRefresh;
    public $keynavButtonAdd;
    public $apriForm;
    public $extraData;
    public $filterName;
    public $orderAlias;
    public $where;
    public $returnClosePortlet = false;
    public $selectedKeys = array();

    function __construct() {
        parent::__construct();
        $this->gridOptions = App::$utente->getKey('utiRicDiag_gridOptions');
        $this->currArrayTable = App::$utente->getKey('utiRicDiag_currArrayTable');
        $this->returnModel = App::$utente->getKey('utiRicDiag_returnModel');
        $this->returnModelOrig = App::$utente->getKey('utiRicDiag_returnModelOrig');
        $this->returnEvent = App::$utente->getKey('utiRicDiag_returnEvent');
        $this->returnKey = App::$utente->getKey('utiRicDiag_returnKey');
        $this->returnColumn = App::$utente->getKey('utiRicDiag_returnColumn');
        $this->returnID = App::$utente->getKey('utiRicDiag_returnID');
        $this->keynavButtonRefresh = App::$utente->getKey('utiRicDiag_keynavButtonRefresh');
        $this->keynavButtonAdd = App::$utente->getKey('utiRicDiag_keynavButtonAdd');
        $this->apriForm = App::$utente->getKey('utiRicDiag_apriForm');
        $this->extraData = App::$utente->getKey('utiRicDiag_extraData');
        $this->filterName = App::$utente->getKey('utiRicDiag_filterName');
        $this->orderAlias = App::$utente->getKey('utiRicDiag_orderAlias');
        $this->where = App::$utente->getKey('utiRicDiag_where');
        $this->returnClosePortlet = App::$utente->getKey('utiRicDiag_returnClosePortlet');
        $this->selectedKeys = App::$utente->getKey('utiRicDiag_selectedKeys');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey('utiRicDiag_gridOptions', $this->gridOptions);
            App::$utente->setKey('utiRicDiag_currArrayTable', $this->currArrayTable);
            App::$utente->setKey('utiRicDiag_returnModel', $this->returnModel);
            App::$utente->setKey('utiRicDiag_returnModelOrig', $this->returnModelOrig);
            App::$utente->setKey('utiRicDiag_returnEvent', $this->returnEvent);
            App::$utente->setKey('utiRicDiag_returnKey', $this->returnKey);
            App::$utente->setKey('utiRicDiag_returnColumn', $this->returnColumn);
            App::$utente->setKey('utiRicDiag_returnID', $this->returnID);
            App::$utente->setKey('utiRicDiag_keynavButtonRefresh', $this->keynavButtonRefresh);
            App::$utente->setKey('utiRicDiag_keynavButtonAdd', $this->keynavButtonAdd);
            App::$utente->setKey('utiRicDiag_apriForm', $this->apriForm);
            App::$utente->setKey('utiRicDiag_extraData', $this->extraData);
            App::$utente->setKey('utiRicDiag_filterName', $this->filterName);
            App::$utente->setKey('utiRicDiag_orderAlias', $this->orderAlias);
            App::$utente->setKey('utiRicDiag_where', $this->where);
            App::$utente->setKey('utiRicDiag_returnClosePortlet', $this->returnClosePortlet);
            App::$utente->setKey('utiRicDiag_selectedKeys', $this->selectedKeys);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->filterName = array();
                $this->selectedKeys = array();
                $this->gridOptions = $_POST['gridOptions'];
//                if (!isset($this->gridOptions['multiselect'])) {
//                    $this->gridOptions['multiselect'] = 'false';
//                }

                $this->returnModel = $_POST['returnModel'];

                if (is_array($this->returnModel)) {
                    $this->returnModelOrig = $this->returnModel['nameFormOrig'];
                    $this->returnModel = $this->returnModel['nameForm'];
                } else {
                    $this->returnModelOrig = $this->returnModel;
                }

                $this->returnEvent = $_POST['returnEvent'];

                $this->returnKey = $_POST['returnKey'];
                $this->returnID = $_POST['retid'];
                $this->returnClosePortlet = $_POST['returnClosePortlet'];
                if (isset($_POST['returnColumn'])) {
                    $this->returnColumn = ($_POST['returnColumn'] != '' ) ? $_POST['returnColumn'] : null;
                }
                $this->keynavButtonRefresh = 'true';
                $this->keynavButtonAdd = 'false';
                $this->apriForm = '';
                if ($_POST['keynavButtonRefresh'] != '') {
                    $this->keynavButtonRefresh = $_POST['keynavButtonRefresh'];
                }
                if ($_POST['keynavButtonAdd'] != '') {
                    if ($_POST['keynavButtonAdd'] == 'returnPadre') {
                        $this->keynavButtonAdd = 'true';
                    } else {
                        $this->keynavButtonAdd = 'true';
                        $this->apriForm = $_POST['keynavButtonAdd'];
                    }
                }
                if ($_POST['extraData']) {
                    $this->extraData = $_POST['extraData'];
                }
                if ($_POST['filterName']) {
                    $this->filterName = $_POST['filterName'];
                }
                if ($_POST['orderAlias']) {
                    $this->orderAlias = $_POST['orderAlias'];
                }
                if ($_POST['where']) {
                    $this->where = $_POST['where'];
                }

                $this->CaricaGriglia();
                if ($_POST['msgDetail']) {
                    Out::show($this->nameForm . '_msgDetail');
                    Out::html($this->nameForm . "_msgDetail", $_POST['msgDetail']);
                } else {
                    Out::hide($this->nameForm . '_msgDetail');
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        if ($this->returnClosePortlet === true) {
                            $returnObj = itaModel::getInstance($this->returnModelOrig, $this->returnModel);
                            $returnObj->setEvent($this->returnEvent);
                            $returnObj->parseEvent();
                        }
                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_Conferma':
                        Out::closeDialog($this->nameForm);
                        $rowid = $_POST[$this->nameGrid]['gridParam']['selarrrow'];

                        $_POST = array();
                        //@TODO: STD su itaModel/itaforntController
                        $_POST['retid'] = $this->returnID;
                        $_POST[$this->returnKey] = $rowid;

                        if ($this->gridOptions['arrayTable'] != '') {
                            if ($this->returnColumn) {
                                if ($this->gridOptions['multiselect']) {
                                    $this->reorderSelectedKeys();
                                    $_POST[$this->returnKey] = implode(',', $this->selectedKeys);

                                    if ($this->gridOptions['multiselectReturnColumn']) {
                                        $_POST[$this->returnColumn] = array();
                                        foreach ($this->selectedKeys as $idx) {
                                            if ($this->gridOptions['readerId']) {
                                                $idx = array_search($idx, array_column($this->gridOptions['arrayTable'], 'ROWID'));
                                            }
                                            $_POST[$this->returnColumn][$idx] = $this->gridOptions['arrayTable'][$idx][$this->returnColumn];
                                        }
                                    }
                                } else {
                                    $_POST[$this->returnColumn] = $this->gridOptions['arrayTable'][$rowid][$this->returnColumn];
                                }
                            } else {
                                if ($this->gridOptions['multiselect']) {
                                    $this->reorderSelectedKeys();
                                    $_POST[$this->returnKey] = implode(',', $this->selectedKeys);

                                    if ($this->gridOptions['multiselectReturnRowData']) {
                                        $_POST['rowData'] = array();
                                        foreach ($this->selectedKeys as $idx) {
                                            if ($this->gridOptions['readerId']) {
                                                $idx = array_search($idx, array_column($this->gridOptions['arrayTable'], 'ROWID'));
                                            }
                                            $_POST['rowData'][$idx] = $this->gridOptions['arrayTable'][$idx];
                                        }
                                    }
                                } else {
                                    $_POST['rowData'] = $this->gridOptions['arrayTable'][$rowid];
                                }
                            }
                        } else {
                            if ($this->gridOptions['multiselect']) {
                                $_POST[$this->returnKey] = implode(',', $this->selectedKeys);
                            }
                        }

                        $returnObj = itaModel::getInstance($this->returnModelOrig, $this->returnModel);
                        $returnObj->setEvent($this->returnEvent);
                        $returnObj->parseEvent();
                        $this->close = true;
                        break;
                }
                break;

            case 'sortRowUpdate':
                switch ($_POST['id']) {
                    case $this->nameGrid:
                        $start = ((int) $_POST['startRowIndex']) - 1;
                        $stop = ((int) $_POST['stopRowIndex']) - 1;

                        $elm = array_splice($this->gridOptions['arrayTable'], $start, 1);
                        $this->gridOptions['arrayTable'] = array_slice($this->gridOptions['arrayTable'], 0, $stop, true) + $elm + array_slice($this->gridOptions['arrayTable'], $stop, null, true);
                        break;
                }
                break;

            case 'onSelectCheckRow':
                switch ($_POST['id']) {
                    case $this->nameGrid:
                        if ($_POST['status'] == "true") {
                            if (!in_array($_POST['rowid'], $this->selectedKeys)) {
                                $this->selectedKeys[] = $_POST['rowid'];
                            }
                        } else {
                            unset($this->selectedKeys[array_search($_POST['rowid'], $this->selectedKeys)]);
                        }
                        break;
                }
                break;

            case 'onSelectCheckAll':
                switch ($_POST['id']) {
                    case $this->nameGrid:
                        if ($_POST['status'] == "true") {
                            foreach ($_POST['rowids'] as $idx) {
                                if (!in_array($idx, $this->selectedKeys)) {
                                    $this->selectedKeys[] = $idx;
                                }
                            }
                        } else {
                            foreach ($_POST['rowids'] as $idx) {
                                if (in_array($idx, $this->selectedKeys)) {
                                    unset($this->selectedKeys[array_search($idx, $this->selectedKeys)]);
                                }
                            }
                        }
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->nameGrid:
                        if ($this->apriForm != '') {
                            $_POST = array();
                            $model = $this->apriForm;
                            $_POST[$model . '_returnField'] = $this->nameForm . '_RicaricaFiltro';
                            $_POST['tipoProt'] = $this->tipoProt;
                            $_POST[$model . '_returnModel'] = $this->nameForm;
                            $_POST[$model . '_returnEvent'] = 'onClickTablePager';
                            $_POST[$model . '_returnId'] = 'utiRicDiag_gridRis';
                            $_POST['event'] = 'openform';
                            $_POST['page'] = 1;
                            $_POST['rows'] = $this->gridOptions['rowNum'];
                            $_POST['sidx'] = $this->gridOptions['sortname'];
                            $_POST['sord'] = $this->gridOptions['sortorder'];

                            if ($this->extraData) {
                                foreach ($this->extraData as $key => $value) {
                                    $_POST[$key] = $value;
                                }
                            }

                            Out::closeDialog($model);
                            itaLib::openDialog($model);
                            $apriObj = itaModel::getInstance($model);
                            $apriObj->parseEvent();
                        } else {
                            Out::closeDialog($this->nameForm);

                            $rowid = $_POST['rowid'];

                            $_POST = array();
                            $_POST['event'] = $this->returnEvent;
                            $_POST['retid'] = $this->returnID;
                            $_POST['dialogEvent'] = 'addGridRow';

                            if ($this->extraData) {
                                $_POST['extraData'] = $this->extraData;
                            }

                            $_POST[$this->returnKey] = $rowid;

                            if ($this->gridOptions['arrayTable'] != '') {
                                if ($this->returnColumn) {
                                    $_POST[$this->returnColumn] = $this->gridOptions['arrayTable'][$rowid][$this->returnColumn];
                                } else {
                                    $_POST['rowData'] = $this->gridOptions['arrayTable'][$rowid];
                                }
                            }

                            $returnObj = itaModel::getInstance($this->returnModelOrig, $this->returnModel);
                            $returnObj->parseEvent();
                            $this->close = true;
                        }
                        break;
                }
                break;

            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->nameGrid:
                        Out::closeDialog($this->nameForm);

                        $rowid = $_POST['rowid'];

                        $_POST = array();
                        $_POST['event'] = $this->returnEvent;
                        $_POST['retid'] = $this->returnID;
                        $_POST[$this->returnKey] = $rowid;

                        if ($this->gridOptions['arrayTable'] != '') {
                            if ($this->returnColumn) {
                                //$_POST[$this->returnColumn] = $this->gridOptions['arrayTable'][$rowid][$this->returnColumn];
                                $_POST[$this->returnColumn] = $this->currArrayTable[$rowid][$this->returnColumn];
                            } else {
                                //$_POST['rowData'] = $this->gridOptions['arrayTable'][$rowid];
                                $_POST['rowData'] = $this->currArrayTable[$rowid];
                            }
                        }

                        $returnObj = itaModel::getInstance($this->returnModelOrig, $this->returnModel);
                        $returnObj->parseEvent();
                        $this->close = true;
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->nameGrid:
                        if ($this->gridOptions['arrayTable'] == '') {
                            $this->RicaricaGriglia();
                        } else {
                            $this->RicaricaGrigliaArray();
                        }
                        foreach ($this->selectedKeys as $idx) {
                            TableView::setSelection($this->nameGrid, $idx);
                        }
                        break;
                }

                break;

            case 'printTableToHTML':
                switch ($_POST['id']) {
                    case $this->nameGrid:
                        if (isset($this->gridOptions['arrayTable'])) {
                            $utiEnte = new utiEnte();
                            $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                            include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                            $itaJR = new itaJasperReport();

                            // Texts
                            $header = '|';
                            $body = '|';

//							$jasper = isset( $this->gridOptions['printTableCharactersWidth'] ) ? $this->gridOptions['printTableCharactersWidth'] : 83;
//							$gridTotal = 0;
//							$printWidths = array();
//							foreach ( $this->gridOptions['colModel'] as $field ) { $gridTotal += intval($field['width']); }
//							$tmp = $jasper / $gridTotal;
//							foreach ( $this->gridOptions['colModel'] as $field ) { array_push( $printWidths, intval( $tmp * $field['width'] ) ); }
//							while ( array_sum( $printWidths ) <> $jasper ) {
//								if ( array_sum( $printWidths) > $jasper ) { $printWidths[ count( $printWidths ) - 1 ]--; }
//								else { $printWidths[ count( $printWidths ) - 1 ]++; }
//							}
//							foreach ( $this->gridOptions['arrayTable'] as $key => $row ) {
//								if ( $key > 0 ) { $body .= "<br>|"; }
//								foreach ( $this->gridOptions['colModel'] as $n => $field ) {
//									$length = $printWidths[$n] - 3;
//									if ( $n == 0 ) { $length--; }
//									if ( $key == 0 ) { $header .= "&nbsp;" . str_replace( " ", "&nbsp;", str_pad( substr($this->gridOptions['colNames'][$n], 0, $length), $length, " " ) ) . "&nbsp;|"; }
//									$body .= "&nbsp;" . str_replace(" ", "&nbsp;", str_pad( substr($row[$field['name']], 0, $length) , $length, " ")) . "&nbsp;|";
//								}
//							}
                            // Widths
                            $jasperCharacters = isset($this->gridOptions['printTableCharactersWidth']) ? $this->gridOptions['printTableCharactersWidth'] : 83;
                            $gridWidth = 0;

                            foreach ($this->gridOptions['colModel'] as $field) {
                                $gridWidth += intval($field['width']);
                            }
                            $ratio = $jasperCharacters / $gridWidth;

                            foreach ($this->gridOptions['arrayTable'] as $key => $row) {
                                if ($key > 0) {
                                    $body .= "<br>|";
                                }
                                foreach ($this->gridOptions['colModel'] as $n => $field) {
                                    $length = intval($ratio * $field['width']) - 3;
                                    if ($key == 0) {
                                        $header .= "&nbsp;" . str_replace(" ", "&nbsp;", str_pad(substr($this->gridOptions['colNames'][$n], 0, $length), $length, " ")) . "&nbsp;|";
                                    }
                                    $body .= "&nbsp;" . str_replace(" ", "&nbsp;", str_pad(substr($row[$field['name']], 0, $length), $length, " ")) . "&nbsp;|";
                                }
                            }
                            $parameters = array("Header" => $header, "Body" => $body, "Ente" => $ParametriEnte_rec['DENOMINAZIONE'], "Sql" => "SELECT * FROM DOMAINS LIMIT 1", "Titolo" => $this->gridOptions['Caption']);
                            $itaJR->runSQLReportPDF(App::$itaEngineDB, 'utiRicDiag', $parameters);
                        }

                        break;
                }

                break;

            case 'returntoform':
                switch ($_POST['retField']) {
                    case $this->nameForm . '_RicaricaFiltro':
                        $this->CaricaGriglia();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey('uti_RicDiag_gridOptions');
        App::$utente->removeKey('uti_RicDiag_currArrayTable');
        App::$utente->removeKey('utiRicDiag_keynavButtonRefresh');
        App::$utente->removeKey('utiRicDiag_keynavButtonAdd');
        App::$utente->removeKey('utiRicDiag_returnModel');
        App::$utente->removeKey('utiRicDiag_returnModelOrig');
        App::$utente->removeKey('utiRicDiag_returnEvent');
        App::$utente->removeKey('utiRicDiag_returnKey');
        App::$utente->removeKey('utiRicDiag_returnColumn');
        App::$utente->removeKey('utiRicDiag_returnID');
        App::$utente->removeKey('utiRicDiag_apriForm');
        App::$utente->removeKey('utiRicDiag_filterName');
        App::$utente->removeKey('utiRicDiag_orderAlias');
        App::$utente->removeKey('utiRicDiag_pgbuttons');
        App::$utente->removeKey('utiRicDiag_pginput');
        App::$utente->removeKey('utiRicDiag_where');
        App::$utente->removeKey('utiRicDiag_returnClosePortlet');
        App::$utente->removeKey('utiRicDiag_selectedKeys');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    private function reorderSelectedKeys() {
        $reorderedArray = array();

        $rid = false;

        if ($this->gridOptions['readerId']) {
            $rid = $this->gridOptions['readerId'];
        }

        foreach ($this->gridOptions['colModel'] as $colModel) {
            if ($colModel['key']) {
                $rid = $colModel['name'];
                break;
            }
        }

        if ($rid) {
            foreach ($this->gridOptions['arrayTable'] as $record) {
                if (in_array($record[$rid], $this->selectedKeys)) {
                    $reorderedArray[] = $record[$rid];
                }
            }
        } else {
            foreach (array_keys($this->gridOptions['arrayTable']) as $idx) {
                if (in_array($idx, $this->selectedKeys)) {
                    $reorderedArray[] = $idx;
                }
            }
        }

        $this->selectedKeys = $reorderedArray;

        return true;
    }

    function RicaricaGrigliaArray() {
        $appoggio = array();
        $filtrato = false;
        $direction = SORT_ASC;
        if ($_POST['sord'] == 'desc') {
            $direction = SORT_DESC;
        }
        $this->array_sort_by_column($this->gridOptions['arrayTable'], $_POST['sidx'], $direction, SORT_STRING);
        if ($this->filterName) {

            foreach ($this->gridOptions['arrayTable'] as $keyArr => $value) {
                $positivo = 0;
                $nFiltro = 0;
                foreach ($this->filterName as $name) {
                    if ($_POST[$name] != "") {
                        $filtrato = true;
                        $nFiltro = $nFiltro + 1;
                        if (strpos(strtolower($value[$name]), strtolower($_POST[$name])) !== false) {
                            $positivo = $positivo + 1;
                        }
                    }
                }

                if ($positivo == $nFiltro) {
                    $appoggio[$keyArr] = $value;
                }
            }
        }

        $ordinamento = $_POST['sidx'];
//        if ($this->orderAlias) {
//            foreach ($this->orderAlias as $order) {
//                if ($order["alias"] == $_POST['sidx']) {
//                    $ordinamento = $order["campo"];
//                    break;
//                }
//            }
//        }
        //if ($appoggio) {
        if ($filtrato) {
            $arrayResult = $this->currArrayTable = $appoggio;
        } else {
            $arrayResult = $this->currArrayTable = $this->gridOptions['arrayTable'];
        }
        $gridRic = new TableView(
                $this->nameGrid, array('arrayTable' => $arrayResult,
            'rowIndex' => 'idx')
        );
        $gridRic->setPageNum($_POST['page']);
        $gridRic->setPageRows($_POST['rows']);
        $gridRic->setSortIndex($ordinamento);
        $gridRic->setSortOrder($_POST['sord']);
        TableView::clearGrid($this->nameGrid);
        $gridRic->getDataPage('json');
        if (!$this->gridOptions['multiselect'] || $this->gridOptions['multiselect'] == 'false') {
            TableView::setSelection($this->nameGrid, 0, 'sequence');
            Out::setFocus('', $this->nameGrid);
        }
    }

    function RicaricaGriglia() {

        try { // Apro il DB
            if (isset($this->gridOptions['dataSource']['dbSuffix'])) {
                $this->GRID_DB = ItaDB::DBOpen($this->gridOptions['dataSource']['sqlDB'], $this->gridOptions['dataSource']['dbSuffix']);
            } else {
                $this->GRID_DB = ItaDB::DBOpen($this->gridOptions['dataSource']['sqlDB']);
            }
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return;
        }

        TableView::clearGrid($this->nameGrid);
        $sql = $this->gridOptions['dataSource']['sqlQuery'];
        if ($this->filterName) {

            //
            //Controllo se nella query c'è un GROUP BY e lo isolo dalla query
            //
            $posGruop = strpos($sql, " GROUP ");
            if ($posGruop !== false) {
                $groupBy = substr($sql, $posGruop);
                $sql = substr($sql, 0, $posGruop);
            }

            //
            //Controllo se nella quere c'è una WHERE e la isolo dalla query
            //
            $posWhere = strpos($sql, "WHERE");
            if (strpos($sql, "WHERE") !== false) {
                $where = substr($sql, $posWhere);
                $sql = substr($sql, 0, $posWhere);
            } else {
                $where = " WHERE 1=1";
            }
            foreach ($this->filterName as $name) {
                $partName = explode('.', $name);
                if ($_POST[$name]) {
                    if (strtoupper($_POST[$name]) == "VUOTO") {
                        $where .= " AND " . $this->GRID_DB->strUpper($name) . " = '' ";
                    } elseif (strtoupper($_POST[$name]) == "NON VUOTO") {
                        $where .= " AND " . $this->GRID_DB->strUpper($name) . " <> '' ";
                    } else {
                        $where .= " AND " . $this->GRID_DB->strUpper($name) . " LIKE '%" . addslashes(strtoupper($_POST[$name])) . "%'";
                    }
                } else if ($_POST[$partName[1]]) {
                    $where .= " AND " . $this->GRID_DB->strUpper($name) . " LIKE '%" . addslashes(strtoupper($_POST[$partName[1]])) . "%'";
                }
            }
        }
        App::log($sql . $where . $groupBy);
        $gridRic = new TableView(
                $this->nameGrid, array(
            'sqlDB' => $this->GRID_DB,
            'sqlQuery' => $sql . $where . $groupBy // Ricompongo la query
                )
        );
        $gridRic->setPageNum($_POST['page']);
        $gridRic->setPageRows($_POST['rows']);
        $gridRic->setSortIndex($_POST['sidx']);
        $gridRic->setSortOrder($_POST['sord']);
        $gridRic->getDataPage('json');
        if (!$this->gridOptions['multiselect'] || $this->gridOptions['multiselect'] == 'false') {
            TableView::setSelection($this->nameGrid, 0, 'sequence');
        }
        Out::setFocus('', $this->nameGrid);
    }

    function CaricaGriglia() {
        $htmlGrid = itaComponents::getHtmlJqGrid($this->nameForm, $this->nameGrid, $this->gridOptions, $this->keynavButtonAdd, $this->keynavButtonRefresh);

        Out::html($this->nameForm . "_divRisultato", $htmlGrid);

        if ($this->gridOptions['arrayTable'] != '') {
            $this->currArrayTable = $this->gridOptions['arrayTable'];
            $gridRic = new TableView(
                $this->nameGrid, array('arrayTable' => $this->gridOptions['arrayTable'],
                'rowIndex' => 'idx')
            );
        } else {
            try { // Apro il DB
                if (isset($this->gridOptions['dataSource']['dbSuffix'])) {
                    $this->GRID_DB = ItaDB::DBOpen($this->gridOptions['dataSource']['sqlDB'], $this->gridOptions['dataSource']['dbSuffix']);
                } else {
                    $this->GRID_DB = ItaDB::DBOpen($this->gridOptions['dataSource']['sqlDB']);
                }
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
                return;
            }

            $gridRic = new TableView(
                    $this->nameGrid, array(
                'sqlDB' => $this->GRID_DB,
                'sqlQuery' => $this->gridOptions['dataSource']['sqlQuery']//.$this->where
                    )
            );
        }
        $gridRic->setPageNum(1);
        $gridRic->setPageRows($this->gridOptions['rowNum']);
        if ($this->gridOptions['arrayTable'] == '') {
            $gridRic->setSortIndex($this->gridOptions['sortname']);
            $gridRic->setSortOrder($this->gridOptions['sortorder']);
        }
        if (!$gridRic->getDataPage('json')) {
            Out::msgInfo($this->gridOptions['Caption'], "Nessun record trovato.");
        } else {
            if (!$this->gridOptions['multiselect'] || $this->gridOptions['multiselect'] == 'false') {
                TableView::setSelection($this->nameGrid, 0, 'sequence');
            }
            TableView::enableEvents($this->nameGrid);
            Out::setFocus('', $this->nameGrid);
        }
    }

    public function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
        $sort_col = array();
        foreach ($arr as $key => $row) {
            $sort_col[$key] = $row[$col];
        }
        array_multisort($sort_col, $dir, $arr);
    }

}
