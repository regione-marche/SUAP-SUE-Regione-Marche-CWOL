<?php
include_once ITA_LIB_PATH  . '/itaPHPCore/itaFrontController.class.php';
include_once ITA_LIB_PATH  . '/itaPHPCore/itaComponents.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';


function utiJqGridCustom() {
    $utiJqGridCustom = new utiJqGridCustom();
    $utiJqGridCustom->parseEvent();
    return;
}


class utiJqGridCustom extends itaFrontController{
    const DATA_SOURCE_ARRAY = 1;
    const DATA_SOURCE_DB = 2;
    
    private $tabHelper;
    private $GRID_CONTAINER;
    
    private $colModel;
    private $metadata;
    private $dataSource;
    private $data;
    private $db;
    private $dbData;
    private $sql;
    private $sqlParams;
    private $eventsMap;
    private $selectedValues;
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        parent::__construct($nameFormOrig, $nameForm);
        
        $this->GRID_CONTAINER = 'divContainer';
        $this->GRID_NAME = 'divGrid';
        
        $this->tabHelper = new cwbBpaGenHelper();
        $this->tabHelper->setNameForm($this->nameForm);
        $this->tabHelper->setModelName($this->nameFormOrig);
        $this->tabHelper->setGridName($this->GRID_NAME);
        
        $this->colModel = cwbParGen::getFormSessionVar($this->nameForm, 'colModel');
        $this->metadata = cwbParGen::getFormSessionVar($this->nameForm, 'metadata');
        $this->dataSource = cwbParGen::getFormSessionVar($this->nameForm, 'dataSource');
        $this->data = cwbParGen::getFormSessionVar($this->nameForm, 'data');
        $this->dbData = cwbParGen::getFormSessionVar($this->nameForm, 'dbData');
        if(!empty($this->dbData)){
            $this->db = ItaDB::DBOpen($this->dbData['dbName'], $this->dbData['dbSuffix'], $this->dbData['connectionName']);
        }
        $this->sql = cwbParGen::getFormSessionVar($this->nameForm, 'sql');
        $this->sqlParams = cwbParGen::getFormSessionVar($this->nameForm, 'sqlParams');
        $this->eventsMap = cwbParGen::getFormSessionVar($this->nameForm, 'eventsMap');
        $this->selectedValues = cwbParGen::getFormSessionVar($this->nameForm, 'selectedValues');
        if($this->selectedValues == ''){
            $this->selectedValues = array();
        }
    }
    
    public function __destruct() {
        parent::__destruct();
        if(!$this->close){
            cwbParGen::setFormSessionVar($this->nameForm, 'colModel', $this->colModel);
            cwbParGen::setFormSessionVar($this->nameForm, 'metadata', $this->metadata);
            cwbParGen::setFormSessionVar($this->nameForm, 'dataSource', $this->dataSource);
            cwbParGen::setFormSessionVar($this->nameForm, 'data', $this->data);
            cwbParGen::setFormSessionVar($this->nameForm, 'dbData', $this->dbData);
            cwbParGen::setFormSessionVar($this->nameForm, 'sql', $this->sql);
            cwbParGen::setFormSessionVar($this->nameForm, 'sqlParams', $this->sqlParams);
            cwbParGen::setFormSessionVar($this->nameForm, 'eventsMap', $this->eventsMap);
            cwbParGen::setFormSessionVar($this->nameForm, 'selectedValues', $this->selectedValues);
        }
    }
    
    public function parseEvent() {
        parent::parseEvent();
        switch($_POST['event']){
            case 'onClickTablePager':
                $this->getData();
                break;
            case 'viewRowInline':
                $this->returnViewEvent();
                break;
            case 'dbClickRow':
                $this->returnDbClickEvent();
                break;
            case 'onSelectRow':
                $this->returnSelectEvent();
                break;
            case 'onSelectCheckRow':
                $this->updateSelectedRow($_POST['rowid']);
                $this->returnSelectMultiEvent();
                break;
            case 'onSelectCheckAll':
                $this->updateSelectAll($_POST['rowids']);
                $this->updateSelectedRow();
                break;
            case 'addGridRow':
                $this->returnAddEvent();
                break;
            case 'editRowInline':
            case 'editGridRow':
                $this->returnDetailEvent();
                break;
            case 'delRowInline':
            case 'delGridRow':
                $this->returnDeleteEvent();
                break;
            case 'exportTableToExcel':
                $this->returnExportToExcelEvent();
                break;
            case 'printTableToHTML':
                $this->returnExportToPdfEvent();
                break;
        }
    }

// <editor-fold defaultstate="expanded" desc="INTERFACCIA PUBBLICA">
    /**
     * Permette di settare il modello della grid
     * @param array $colModel array di array contenente il nome della colonna, il titolo ed eventuali metadati
     *                        array(
     *                          array(
     *                            "name"=>'COL1',
     *                            "title"=>'Colonna 1',
     *                            "class"=>'{align:\'center\'}',
     *                            "width"=>100
     *                          ),
     *                          ....
     *                        )
     * @param array $metadata metadati della grid
     *                        array(
     *                          "caption"=>'Griglia di test',
     *                          "resizeToParent"=>true,
     *                          ...
     *                        )
     */
    public function setJqGridModel($colModel, $metadata=null){
        $this->colModel = $colModel;
        $this->metadata = $metadata;
        
        $this->setIndexCol();
        $this->setIndexValue();
        $this->initFlagDisFilter();
    }
    
    /**
     * Setta i dati da mostrare nella grid a partire da un array
     * @param array $data array di array in forma chiave=>valore
     *                        array(
     *                          array(
     *                            "COL1"=>"Dato 1",
     *                            "COL2"=>"Dato 2",
     *                            ...
     *                          ),
     *                          ...
     *                        )
     */
    public function setJqGridDataArray($data){
        if(empty($this->colModel)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Valorizzare prima il model della jqGrid');
        }
        
        $this->dataSource = self::DATA_SOURCE_ARRAY;
        $this->data = $data;
        
        $this->setIndexCol();
        $this->setIndexValue();
    }
    
    /**
     * Setta i dati da mostrare nella grid a partire da una query sql
     * @param <string> $sql
     * @param <string> $dbName
     * @param <array> $params
     * @param <string> $dbSuffix
     * @param <string> $connectionName
     * @throws type
     */
    public function setJqGridDataDB($sql, $dbName, $params=array(), $dbSuffix='ditta', $connectionName=''){
        if(empty($this->colModel)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Valorizzare prima il model della jqGrid');
        }
        
        $this->dataSource = self::DATA_SOURCE_DB;
        $this->dbData = array(
            'dbName'=>$dbName,
            'dbSuffix'=>$dbSuffix,
            'connectionName'=>$connectionName
        );
        $this->db = ItaDB::DBOpen($this->dbData['dbName'], $this->dbData['dbSuffix'], $this->dbData['connectionName']);
        $this->sql = $sql;
        $this->sqlParams = $params;
    }
    
    /**
     * Setta i dati da mostrare nella grid a partire da una query sql ma li tratta in blocco come un array.
     * Necessario se si desidera ricevere indietro i dati e veloce ma fa uso intensivo della memoria.
     * @param ItaDB $db
     * @param string $sql
     * @param array $params
     */
    public function setJqGridDataDbToArray($db, $sql, $params=array()){
        if(empty($this->colModel)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Valorizzare prima il model della jqGrid');
        }
        
        $this->dataSource = self::DATA_SOURCE_ARRAY;
        $this->data = ItaDB::DBSQLSelect($db, $sql, true, '', '', $params);
        
        $this->setIndexCol();
        $this->setIndexValue();
    }
    
    /**
     * Nome dell'evento da richiamare sul parent allo scatenarsi di un dato evento. Se non valorizzato non viene richiamato il parent. 
     * @param string $view          Evento scatenato all'apertura di un record in sola visualizzazione (restituisce in $this->formData['returnData'] il record selezionato)
     * @param string $dbClickRow    Evento scatenato al doppio click (di default selezione o apertura dettaglio) (restituisce in $this->formData['returnData'] il record selezionato)
     * @param string $select        Evento scatenato alla selezione di un record (restituisce in $this->formData['returnData'] il record selezionato)
     * @param string $multiselect   Evento scatenato alla selezione di un record con selezione multipla (restituisce in $this->formData['returnData'] l'array dei record selezionati)
     * @param string $selectAll     Evento scatenato alla selezione di tutti i record con selezione multipla (restituisce in $this->formData['returnData'] l'array dei record selezionati)
     * @param string $detail        Evento scatenato al click del tasto di apertura dettaglio (restituisce in $this->formData['returnData'] il record selezionato)
     * @param string $delete        Evento scatenato al click del tasto di delete (restituisce in $this->formData['returnData'] il record selezionato)
     * @param string $printPdf      Evento scatenato al click del tasto di stampa in PDF
     * @param string $printXslx     Evento scatenato al click del tasto di stampa in XSLX
     * @param string $add           Evento scatenato al click del tasto di inserimento nuovo record.
     * @param string $closePortlet  Evento scatenato alla chiusura della finestra
     */
    public function setReturnEvents($view=null, $dbClickRow=null, $select=null, $multiselect=null, $detail=null, $delete=null, $printPdf=null, $printXslx=null, $add=null, $closePortlet=null){
        $this->eventsMap = array();
        if(!empty($view)){
            $this->eventsMap['view'] = $view;
        }
        if(!empty($dbClickRow)){
            $this->eventsMap['dbClickRow'] = $dbClickRow;
        }
        if(!empty($select)){
            $this->eventsMap['select'] = $select;
        }
        if(!empty($multiselect)){
            $this->eventsMap['multiselect'] = $multiselect;
        }
        if(!empty($detail)){
            $this->eventsMap['detail'] = $detail;
        }
        if(!empty($add)){
            $this->eventsMap['add'] = $add;
        }
        if(!empty($delete)){
            $this->eventsMap['delete'] = $delete;
        }
        if(!empty($printPdf)){
            $this->eventsMap['printPdf'] = $printPdf;
        }
        if(!empty($printXslx)){
            $this->eventsMap['printXslx'] = $printXslx;
        }
        if(!empty($closePortlet)){
            $this->eventsMap['closePortlet'] = $closePortlet;
        }
    }
    
    /**
     * Renderizza la griglia con i dati caricati
     * @throws type
     */
    public function render(){
        if(empty($this->colModel) || empty($this->metadata)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Non sono stati valorizzati i metadati o il modello della tabella');
        }
        
        
        
        Out::html($this->nameForm . '_' . $this->GRID_CONTAINER, itaComponents::getHtmlJqGridComponent($this->nameForm, $this->GRID_NAME, $this->colModel, $this->metadata));
        $this->initGridPager();
        
        TableView::enableEvents($this->nameForm . '_'. $this->GRID_NAME);
        Out::gridForceResize($this->nameForm . '_'. $this->GRID_NAME);
        
        if(isSet($this->metadata['reloadOnResize']) && $this->metadata['reloadOnResize'] == false){
            TableView::reload($this->nameForm . '_'. $this->GRID_NAME);
        }
    }
    
    /**
     * Lancia il reload della tableview
     */
    public function refresh(){
        TableView::reload($this->nameForm . '_'. $this->GRID_NAME);
    }
    
    public function setTitle($title){
        Out::setDialogTitle($this->nameForm, $title);
    }
    
    public function closeGrid($closeDialog = true){
        $this->close($closeDialog);
    }
// </editor-fold>
    
// <editor-fold defaultstate="collapsed" desc=" Funzioni private, DND">
    protected function close($closeDialog = true) {
        if($closeDialog){
            $this->returnClosePortletEvent();
        }
        parent::close($closeDialog);
    }
    
    private function setIndexCol(){
        if(!empty($this->metadata) && !empty($this->data)){
            if(!isSet($this->metadata['readerId'])){
                $colModel = array(
                    'name'=>'IDX',
                    'title'=>'',
                    'class'=>'{hidden: true}'
                );

                $this->metadata['readerId'] = 'IDX';
                $this->colModel[] = $colModel;
            }
        }
    }
    
    private function setIndexValue(){
        if(!empty($this->metadata) && !empty($this->data)){
            if(isSet($this->metadata['readerId']) && isSet($this->data[0]) && !isSet($this->data[0][$this->metadata['readerId']])){
                foreach($this->data as $k=>&$v){
                    $v[$this->metadata['readerId']] = $k;
                }
            }
        }
    }
    
    private function initFlagDisFilter(){
        if($this->metadata['showRecordStatus'] === true){
            Out::gridSetColumnFilterValue($this->nameForm, $this->GRID_NAME, 'FLAG_DIS', 'A');
        }
    }
    
    private function initGridPager(){
        $codice = '$("#gbox_'.$this->nameForm.'_'.$this->GRID_NAME.' .ui-pg-selbox").closest("td").before("<td dir=\'ltr\'>Righe per pagina:</td>");';
        Out::codice($codice);
        
        $codice = '$("#gbox_'.$this->nameForm.'_'.$this->GRID_NAME.' .ui-pg-selbox option[value=\'Tutte\']").val(999999999999);';
        Out::codice($codice);
    }
    
    private function getData(){
        switch($this->dataSource){
            case self::DATA_SOURCE_ARRAY:
                $data = $this->applyFiltersData();
                $jqGrid = $this->tabHelper->initializeTableArray($data, $_POST['sidx'], $_POST['sord']);
                break;
            case self::DATA_SOURCE_DB:
                $sql = $this->applyFiltersSql();
                $jqGrid = $this->tabHelper->initializeTableSql($sql, $this->db, $this->sqlParams, $_POST['sidx'], $_POST['sord']);
                break;
        }

        $jqGrid->setPageNum($_POST['page']);
        $jqGrid->setPageRows($_POST['rows']);
        
        $data = $this->elaboraRecords($jqGrid->getDataArray());
        $this->tabHelper->getDataPage($jqGrid, $data);
        
        $this->renderSelect();
    }
    
    private function applyFiltersData(){
        $data = $this->data;
        foreach($this->colModel as $column){
            if(empty($data)){
                break;
            }
            
            $columnName = $column['name'];
            $filterValue = trim($_POST[$columnName] ?: '');
            
            if(!empty($filterValue)){
                foreach($data as $k=>$row){
                    if(stripos($row[$columnName], $filterValue) === false){
                        unset($data[$k]);
                    }
                }
            }
        }
        if($this->metadata['showAuditColumns'] === true && !empty($_POST['CODUTE']) && empty($data)){
            $filterValue = trim($_POST['CODUTE'] ?: '');
            
            foreach($data as $k=>$row){
                if(stripos($row['CODUTE'], $filterValue) === false){
                    unset($data[$k]);
                }
            }
        }
        if($this->metadata['showRecordStatus'] === true && !empty($_POST['FLAG_DIS']) && empty($data)){
            $filterValue = $_POST['CODUTE'] == 'A' ? '0' : '1';
            
            foreach($data as $k=>$row){
                if($row['FLAG_DIS'] != $filterValue){
                    unset($data[$k]);
                }
            }
        }
        
        return $data;
    }
    
    private function applyFiltersSql(){
        $sql = 'SELECT MyQuery.* FROM ('.$this->sql.') MyQuery';
        $where = 'WHERE';
        
        foreach($this->colModel as $column){            
            $columnName = $column['name'];
            $filterValue = trim($_POST[$columnName] ?: '');
            
            if(!empty($filterValue)){
                $sql .= ' '.$where.' UPPER(MyQuery.'.$columnName.') LIKE \'%'.addslashes(strtoupper($filterValue)).'%\'';
                $where = 'AND';
            }
        }
        if($this->metadata['showAuditColumns'] === true && !empty($_POST['CODUTE'])){
            $filterValue = trim($_POST['CODUTE'] ?: '');
            
            $sql .= ' '.$where.' UPPER(MyQuery.CODUTE) LIKE \'%'.addslashes(strtoupper($filterValue)).'%\'';
            $where = 'AND';
        }
        if($this->metadata['showRecordStatus'] === true && !empty($_POST['FLAG_DIS'])){
            $filterValue = $_POST['FLAG_DIS'] == 'A' ? '0' : '1';
            
            $sql .= ' '.$where.' MyQuery.FLAG_DIS = '.$filterValue;
            $where = 'AND';
        }
        
        return $sql;
    }
    
    private function elaboraRecords($data){
        if(is_array($data)){
            if($this->metadata['showAuditColumns'] === true && !isSet($row['DATATIMEOPER'])){
                foreach($data as &$row){
                    $timeOper = (!empty($row['TIMEOPER']) ? trim($row['TIMEOPER']) : '');
                    $dataOper = (!empty($row['DATAOPER']) ? new DateTime($row['DATAOPER']) : '');
                    $datatimeoper = $timeOper . (!empty($timeOper) && !empty($dataOper) ? ' - ' : '') . (!empty($dataOper) ? $dataOper->format('d/m/Y') : '');

                    $row['CODUTE'] = '<div style="font-style: italic;">' . $row['CODUTE'] . '</div>';
                    $row['DATATIMEOPER'] = '<div style="font-style: italic;">' . $datatimeoper . '</div>';
                }
            }
        }
        
        return $data;
    }

    /**
     * Attiva/Disattiva la selezione su una riga a seconda di quanto impostato sulla frontend
     * @param <string> $rowid
     */
    private function updateSelectedRow($rowid) {
        if ($_POST['jqg_' . $this->nameForm . '_' . $this->GRID_NAME . '_' . $rowid] == 1) {
            $this->selectedValues[$rowid] = true;
        } else {
            unset($this->selectedValues[$rowid]);
        }
    }

    /**
     * Seleziona/Deseleziona tutte le righe passate a seconda di quanto impostato sulla frontend
     * @param <array> $rowids
     */
    private function updateSelectAll($rowids) {
        foreach ($rowids as $rowid) {
            $this->updateSelectedRow($rowid);
        }
    }

    private function renderSelect() {
        foreach (array_keys($this->selectedValues) as $rowid) {
            TableView::setSelection($this->nameForm . "_" . $this->GRID_NAME, $rowid, 'id');
        }
    }
    
    private function getSelected() {
        return array_keys($this->selectedValues);
    }
    
    private function getDataFromRowid($rowid){
        switch($this->dataSource){
            case self::DATA_SOURCE_ARRAY:
                $return = null;
                foreach($this->data as $row){
                    if($row[$this->metadata['readerId']] == $rowid){
                        $return = $row;
                        break;
                    }
                }
                
                return $return;
            case self::DATA_SOURCE_DB:
                if(!isSet($this->metadata['readerId'])){
                    return false;
                }

                $sql = 'SELECT * FROM ('.$this->sql.') MySelect WHERE MySelect.'.$this->metadata['readerId'].' = \''.addslashes($rowid).'\'';
                return ItaDB::DBSQLSelect($this->db, $sql, false, '', '', $this->sqlParams);
        }
    }
    
    private function returnViewEvent(){
        if(isSet($this->eventsMap['view']) && !empty($this->returnModel)){
            $returnData = $this->getDataFromRowid($_POST['rowid']);
            
            if(!empty($returnData)){
                cwbLib::ricercaEsterna($this->returnModel, $this->eventsMap['view'], $this->nameForm . '_' . $this->GRID_NAME, $returnData, $this->nameForm, $this->returnNameForm, false);
            }
        }
    }
    
    private function returnDbClickEvent(){
        if(isSet($this->eventsMap['dbClickRow']) && !empty($this->returnModel)){
            $returnData = $this->getDataFromRowid($_POST['rowid']);
            
            if(!empty($returnData)){
                cwbLib::ricercaEsterna($this->returnModel, $this->eventsMap['dbClickRow'], $this->nameForm . '_' . $this->GRID_NAME, $returnData, $this->nameForm, $this->returnNameForm, false);
            }
        }
    }
    
    private function returnSelectEvent(){
        if(isSet($this->eventsMap['select']) && !empty($this->returnModel)){
            $returnData = $this->getDataFromRowid($_POST['rowid']);
            
            if(!empty($returnData)){
                cwbLib::ricercaEsterna($this->returnModel, $this->eventsMap['select'], $this->nameForm . '_' . $this->GRID_NAME, $returnData, $this->nameForm, $this->returnNameForm, false);
            }
        }
    }
    
    private function returnSelectMultiEvent(){
        if(isSet($this->eventsMap['multiselect']) && !empty($this->returnModel)){
            $returnData = array();
            foreach($this->getSelected() as $row){
                $returnData[] = $this->getDataFromRowid($row);
            }
            
            if(self::DATA_SOURCE_ARRAY || isSet($this->metadata['readerId'])){
                cwbLib::ricercaEsterna($this->returnModel, $this->eventsMap['multiselect'], $this->nameForm . '_' . $this->GRID_NAME, $returnData, $this->nameForm, $this->returnNameForm, false);
            }
        }
    }
    
    private function returnDetailEvent(){
        if(isSet($this->eventsMap['detail']) && !empty($this->returnModel)){
            $returnData = $this->getDataFromRowid($_POST['rowid']);
            
            if(!empty($returnData)){
                cwbLib::ricercaEsterna($this->returnModel, $this->eventsMap['detail'], $this->nameForm . '_' . $this->GRID_NAME, $returnData, $this->nameForm, $this->returnNameForm, false);
            }
        }
    }
    
    private function returnAddEvent(){
        if(isSet($this->eventsMap['add']) && !empty($this->returnModel)){
            cwbLib::ricercaEsterna($this->returnModel, $this->eventsMap['add'], $this->nameForm . '_' . $this->GRID_NAME, array(), $this->nameForm, $this->returnNameForm, false);
        }
    }
    
    private function returnDeleteEvent(){
        if(isSet($this->eventsMap['delete']) && !empty($this->returnModel)){
            $returnData = $this->getDataFromRowid($_POST['rowid']);
            
            if(!empty($returnData)){
                cwbLib::ricercaEsterna($this->returnModel, $this->eventsMap['delete'], $this->nameForm . '_' . $this->GRID_NAME, $returnData, $this->nameForm, $this->returnNameForm, false);
            }
        }
    }
    
    private function returnExportToExcelEvent(){
        if(isSet($this->eventsMap['printPdf']) && !empty($this->returnModel)){
            cwbLib::ricercaEsterna($this->returnModel, $this->eventsMap['printPdf'], $this->nameForm . '_' . $this->GRID_NAME, array(), $this->nameForm, $this->returnNameForm, false);
        }
    }
    
    private function returnExportToPdfEvent(){
        if(isSet($this->eventsMap['printXslx']) && !empty($this->returnModel)){
            cwbLib::ricercaEsterna($this->returnModel, $this->eventsMap['printXslx'], $this->nameForm . '_' . $this->GRID_NAME, array(), $this->nameForm, $this->returnNameForm, false);
        }
    }
    
    private function returnClosePortletEvent(){
        if(isSet($this->eventsMap['closePortlet']) && !empty($this->returnModel)){
            cwbLib::ricercaEsterna($this->returnModel, $this->eventsMap['closePortlet'], $this->nameForm . '_' . $this->GRID_NAME, array(), $this->nameForm, $this->returnNameForm, false);
        }
    }
// </editor-fold>
}
