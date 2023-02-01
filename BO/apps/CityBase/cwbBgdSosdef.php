<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGD.class.php';

function cwbBgdSosdef() {
    $cwbBgdSosdef = new cwbBgdSosdef();
    $cwbBgdSosdef->parseEvent();
    return;
}

class cwbBgdSosdef extends cwbBpaGenTab {
    
    private $gridBgdSosads;
    private $GRID_NAME_ASSOCIAZIONI;
    private $bgdSosadsTableName;
    private $bgdSosadsRecords;
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBgdSosdef';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    protected function initVars() {
        $this->GRID_NAME = 'gridBgdSosdef';
        $this->AUTOR_MODULO = 'BGD';
        $this->AUTOR_NUMERO = 1;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BGD();
        
        $this->actionAfterNew = self::GOTO_LIST;
        $this->actionAfterModify = self::GOTO_LIST;
        $this->actionAfterDelete = self::GOTO_LIST;
        
        $this->openDetailFlag = true;
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
        
        $this->GRID_NAME_ASSOCIAZIONI = 'gridBgdSosads';
        $this->bgdSosadsTableName = 'BGD_SOSADS';
        
        $this->addDescribeRelation($this->bgdSosadsTableName, array('IDSOSDEF' => 'IDSOSDEF'), itaModelServiceData::RELATION_TYPE_ONE_TO_MANY);
        
        $this->gridBgdSosads = cwbParGen::getFormSessionVar($this->nameForm, $this->GRID_NAME_ASSOCIAZIONI);
        if ($this->gridBgdSosads == '') {
            $this->gridBgdSosads = array();
        }
        
        $this->bgdSosadsRecords = cwbParGen::getFormSessionVar($this->nameForm, 'bgdSosadsRecords');
        if ($this->bgdSosadsRecords == '') {
            $this->bgdSosadsRecords = array();
        }
    }
    
    protected function preDestruct() {
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, $this->GRID_NAME_ASSOCIAZIONI, $this->gridBgdSosads);
            cwbParGen::setFormSessionVar($this->nameForm, 'bgdSosadsRecords', $this->bgdSosadsRecords);
        }
    }
    
    protected function setGridFilters() {
        $this->gridFilters = array();
        if(!empty($_POST['IDSOSDEF'])) {
            $this->gridFilters['IDSOSDEF'] = $this->formData['IDSOSDEF'];
        }
        if(!empty($_POST['DESCRIZIONE'])) {
            $this->gridFilters['DESCRIZIONE'] = $this->formData['DESCRIZIONE'];
        }
        if(!empty($_POST['CONSERVATORE'])) {
            $this->gridFilters['TIPO_GEST'] = $this->formData['CONSERVATORE'];
        }
        if(!empty($_POST['CODUTE'])){
           $this->gridFilters['CODUTE'] = $this->formData['CODUTE'];
        }
        if(!empty($_POST['FLAG_DIS'])){
           $this->gridFilters['FLAG_DIS'] = $this->formData['FLAG_DIS']-1;
        }
    }

    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        if(!isSet($filtri['IDSOSDEF'])){
            $filtri['IDSOSDEF'] = trim($this->formData[$this->nameForm . '_IDSOSDEF']);
        }
        if(!isSet($filtri['DESCRIZIONE'])){
            $filtri['DESCRIZIONE'] = trim($this->formData[$this->nameForm . '_DESCRIZIONE']);
        }
        
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBgdSosdef($filtri, false, $sqlParams);
    }

    protected function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBgdSosdef(array('IDSOSDEF'=>$index), false, $sqlParams);
    }
    
    protected function elaboraRecords($Result_tab) {
        
        foreach($Result_tab as $key => $record) {
            switch ($Result_tab[$key]['TIPO_GEST']) {
                case 1:
                    $Result_tab[$key]['CONSERVATORE'] = 'PARER';
                    break;
                case 2:
                    $Result_tab[$key]['CONSERVATORE'] = 'MARCHE';
                    break;
                case 3:
                    $Result_tab[$key]['CONSERVATORE'] = 'NAMIRIAL';
                    break;
                case 4:
                    $Result_tab[$key]['CONSERVATORE'] = 'ARUBA';
                    break;
                case 5:
                    $Result_tab[$key]['CONSERVATORE'] = 'DOCER';
                    break;
            }
        }
        
        return $Result_tab;
    }
    
    protected function postNuovo() {
        Out::hide($this->nameForm.'_BGD_SOSDEF[IDSOSDEF]_lbl');
        Out::hide($this->nameForm.'_BGD_SOSDEF[IDSOSDEF]');
        
        Out::setFocus("", $this->nameForm . '_BGD_SOSDEF[DESCRIZIONE]');
    }
    
    protected function postDettaglio() {
        Out::show($this->nameForm.'_BGD_SOSDEF[IDSOSDEF]_lbl');
        Out::show($this->nameForm.'_BGD_SOSDEF[IDSOSDEF]');
        
        Out::setFocus("", $this->nameForm . '_BGD_SOSDEF[DESCRIZIONE]');
        
        $idsosdef = $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['IDSOSDEF'];
        
        $filtri = array();
        $filtri['IDSOSDEF'] = $idsosdef;
        
        $this->bgdSosadsRecords = $this->libDB->leggiBgdSosadsLeftJoinBgdSossez($filtri);
        
        $this->loadBgdSosads();
    }
    
    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_IDSOSDEF');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_IDSOSDEF');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BGD_SOSDEF[IDSOSDEF]');
    }
    
    protected function postTornaElenco() {
        $this->bgdSosadsRecords = '';
    }
    
    protected function postAggiorna() {
        $this->bgdSosadsRecords = '';
    }
    
    protected function preParseEvent() {
        switch($_POST['event']){
            case 'openform':
                $this->initComponents();
                break;
        }
    }
    
    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_AltraRicerca':
                        $this->bgdSosadsRecords = '';
                        break;
                }
                if (preg_match('/' . $this->nameForm . '_SEARCH_([0-9]*)/', $_POST['id'], $matches)) {
                    $rowId = $matches[1] - 1;
                    cwbLib::apriFinestraRicerca('cwbBgdSossez', $this->nameForm, 'returnBgdSossez', $rowId, true, null, $this->nameFormOrig);
                }
                if(preg_match('/'.$this->nameForm.'_OKBUTTON_([0-9]*)/',$_POST['id'],$matches)){
                    $rowId = $matches[1] - 1;
                    $this->setBgdSosads($rowId, $this->formData, false);
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case ($this->nameForm.'_'.$this->GRID_NAME_ASSOCIAZIONI):
                        $this->loadBgdSosads();
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case ($this->nameForm.'_'.$this->GRID_NAME_ASSOCIAZIONI):
                        $this->aggiungiBgdSosads();
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME_ASSOCIAZIONI:
                        $rowId = $_POST['rowid'] - 1;
                        $this->cancellaBgdSosads($rowId);
                        break;
                }
                break;
            case 'returnBgdSossez':
                $rowId = $this->elementId;
                $this->setBgdSosads($rowId, $this->formData, true);
                break;
        }
    }
    
    private function initComponents() {
        Out::html($this->nameForm . '_BGD_SOSDEF[TIPO_GEST]', '');
        Out::select($this->nameForm . '_BGD_SOSDEF[TIPO_GEST]', 1, null, false, '');
        Out::select($this->nameForm . '_BGD_SOSDEF[TIPO_GEST]', 1, 1, false, 'PARER');
        Out::select($this->nameForm . '_BGD_SOSDEF[TIPO_GEST]', 1, 2, false, 'Regione Marche');
        Out::select($this->nameForm . '_BGD_SOSDEF[TIPO_GEST]', 1, 3, false, 'Namirial');
        Out::select($this->nameForm . '_BGD_SOSDEF[TIPO_GEST]', 1, 4, false, 'Aruba');
        Out::select($this->nameForm . '_BGD_SOSDEF[TIPO_GEST]', 1, 5, false, 'Docer');
        
        Out::html($this->nameForm . '_BGD_SOSDEF[SRC_META]', '');
        Out::select($this->nameForm . '_BGD_SOSDEF[SRC_META]', 1, null, false, '');
        Out::select($this->nameForm . '_BGD_SOSDEF[SRC_META]', 1, 1, false, 'Gestione Documentale');
        Out::select($this->nameForm . '_BGD_SOSDEF[SRC_META]', 1, 2, false, 'Database');
    }
    
    private function loadBgdSosads() {
        $tabHelper = new cwbBpaGenHelper();
        $tabHelper->setNameForm($this->nameForm);
        $tabHelper->setModelName($this->nameFormOrig);
        $tabHelper->setGridName($this->GRID_NAME_ASSOCIAZIONI);
        $tabHelper->setDb($this->MAIN_DB);

        $this->bgdSosadsRecords = array_values($this->bgdSosadsRecords);
        
        $data = $this->bgdSosadsRecords;

        $data = $this->htmlForAssociazioniGrid($data);
        
        $this->gridBgdSosads = $data;

        $ita_grid01 = $tabHelper->initializeTableArray($data);
        $tabHelper->getDataPage($ita_grid01);

        TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME_ASSOCIAZIONI);
        cwbLibHtml::attivaJSElemento($this->nameForm . '_' . $this->GRID_NAME_ASSOCIAZIONI);
    }
    
    private function htmlForAssociazioniGrid($records){
        $htmlRecords = array();
        
        $disable = (($this->authenticator->getLevel() !== 'C' && !$this->authenticator->getLevel() !== 'G') || $this->viewMode);

        foreach ($records as $key => $value) {
            $component = array(
                'id' => 'SEARCH',
                'type' => 'ita-button',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                'onClickEvent' => false,
                'icon' => 'ui-icon-search',
                'properties' => array(
                    'style' => 'width: 20px; height: 20px;'
                )
            );
            if($disable){
                $component['properties']['disabled'] = 'true';
            }
            $htmlRecords[$key]['SEARCH'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            
            $component = array(
                'id' => 'IDSOSADS',
                'type' => 'ita-readonly',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                'properties' => array(
                    'value' => $records[$key]['IDSOSADS'],
                    'style' => 'width: 95%;text-align: center;'
                )
            );
            $htmlRecords[$key]['IDSOSADS'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            
            $component = array(
                'id' => 'SEQUENZA',
                'type' => 'ita-edit',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                'properties' => array(
                    'value' => $records[$key]['SEQUENZA'],
                    'style' => 'width: 95%;text-align: center;'
                )
            );
            $htmlRecords[$key]['SEQUENZA'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            
            $component = array(
                'id' => 'IDSOSSEZ',
                'type' => 'ita-readonly',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                'properties' => array(
                    'value' => $records[$key]['IDSOSSEZ'],
                    'style' => 'width: 95%;text-align: center;'
                )
            );
            $htmlRecords[$key]['IDSOSSEZ'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            
            $component = array(
                'id' => 'SEZIONE',
                'type' => 'ita-readonly',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                'properties' => array(
                    'value' => $records[$key]['SEZIONE'],
                    'style' => 'width: 50%;text-align: center !important;'
                )
            );
            $htmlRecords[$key]['SEZIONE'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            
            $component = array(
                'id' => 'VERSIONE_DA',
                'type' => 'ita-edit',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                'properties' => array(
                    'value' => $records[$key]['VERSIONE_DA'],
                    'style' => 'width: 95%;text-align: center;'
                )
            );
            $htmlRecords[$key]['VERSIONE_DA'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            
            $component = array(
                'id' => 'VERSIONE_A',
                'type' => 'ita-edit',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                'properties' => array(
                    'value' => $records[$key]['VERSIONE_A'],
                    'style' => 'width: 95%;text-align: center;'
                )
            );
            $htmlRecords[$key]['VERSIONE_A'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            
            $component = array(
                'id' => 'OKBUTTON',
                'type' => 'ita-button',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                'onClickEvent' => false,
                'icon' => 'ui-icon-disk',
                'properties' => array(
                    'style' => 'width: 100%; height: 20px;',
                    'value'=> 'Salva riga'
                )
            );
            if($disable){
                $component['properties']['disabled'] = 'true';
            }
            $htmlRecords[$key]['OKBUTTON'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            
            $htmlRecords[$key]['KEY'] = $key + 1;
        }

        return $htmlRecords;
    }
    
    private function aggiungiBgdSosads() {
        $this->addInsertOperation($this->bgdSosadsTableName);
        $bgdSosadsRecord = $this->getModelService()->define($this->MAIN_DB, 'BGD_SOSADS');
        $bgdSosadsRecord['VERSIONE_DA'] = 1;
        $bgdSosadsRecord['VERSIONE_A'] = 0;
        
        $sequenza = $this->getNextSequenza();
        $bgdSosadsRecord['SEQUENZA'] = $sequenza;

        $this->bgdSosadsRecords[] = $bgdSosadsRecord;

        $this->loadBgdSosads();
    }
    
    private function setBgdSosads($key,$data, $rData){
        $idsosads = $this->bgdSosadsRecords[$key]['IDSOSADS'];
        $idsosdef = $this->bgdSosadsRecords[$key]['IDSOSDEF'];
        $sequenza = $this->bgdSosadsRecords[$key]['SEQUENZA'];
        $versioneDa = $this->bgdSosadsRecords[$key]['VERSIONE_DA'];
        $versioneA = $this->bgdSosadsRecords[$key]['VERSIONE_A'];
        
        $this->bgdSosadsRecords[$key] = $data['returnData'];
        $this->bgdSosadsRecords[$key]['IDSOSADS'] = $idsosads;
        $this->bgdSosadsRecords[$key]['IDSOSDEF'] = $idsosdef;
        $this->bgdSosadsRecords[$key]['SEQUENZA'] = $sequenza;
        $this->bgdSosadsRecords[$key]['VERSIONE_DA'] = $versioneDa;
        $this->bgdSosadsRecords[$key]['VERSIONE_A'] = $versioneA;
        
        if($rData) {
            Out::valore($this->nameForm . '_IDSOSSEZ_' . ($key+1), $data['returnData']['IDSOSSEZ']);
            $this->bgdSosadsRecords[$key]['IDSOSSEZ'] = $data['returnData']['IDSOSSEZ'];
            Out::valore($this->nameForm . '_SEZIONE_' . ($key+1), $data['returnData']['SEZIONE']);
            $this->bgdSosadsRecords[$key]['SEZIONE'] = $data['returnData']['SEZIONE'];
            $this->bgdSosadsRecords[$key]['SEQUENZA'] = $sequenza;
            $this->bgdSosadsRecords[$key]['VERSIONE_DA'] = $versioneDa;
            $this->bgdSosadsRecords[$key]['VERSIONE_A'] = $versioneA;
        } else {
            $this->bgdSosadsRecords[$key]['IDSOSSEZ'] = $data[$this->nameForm . '_IDSOSSEZ_' . ($key + 1)];
            $this->bgdSosadsRecords[$key]['SEZIONE'] = $data[$this->nameForm . '_SEZIONE_' . ($key + 1)];
            $this->bgdSosadsRecords[$key]['SEQUENZA'] = $data[$this->nameForm . '_SEQUENZA_' . ($key + 1)];
            $this->bgdSosadsRecords[$key]['VERSIONE_DA'] = $data[$this->nameForm . '_VERSIONE_DA_' . ($key + 1)];
            $this->bgdSosadsRecords[$key]['VERSIONE_A'] = $data[$this->nameForm . '_VERSIONE_A_' . ($key + 1)];
        }
        
        if(!$idsosads) {
            $pk = array('IDSOSADS' => $idsosads);
            
            $this->addUpdateOperation($this->bgdSosadsTableName, $pk);
        }
        
        $this->loadBgdSosads();
    }
    
    private function cancellaBgdSosads($rowId) {
        $bgdSosadsToDelete = array(
            'IDSOSADS' => $this->bgdSosadsRecords[$rowId]['IDSOSADS']
        );
        
        $this->addDeleteOperation($this->bgdSosadsTableName, $bgdSosadsToDelete);
        unset($this->bgdSosadsRecords[$rowId]);

        $this->loadBgdSosads();
    }
    
    private function getNextSequenza() {
        $sequenzaMax = 0;
        foreach ($this->bgdSosadsRecords as $record) {
            if($record['SEQUENZA'] > $sequenzaMax) {
                $sequenzaMax = $record['SEQUENZA'];
            }
        }
        
        return $sequenzaMax + 10;
    }
    
    protected function getDataRelationView($tableName, $alias = null) {
        $key = $alias ? $alias : $tableName;
        $results = array();

        switch ($key) {
            case $this->bgdSosadsTableName:
                $results = $this->bgdSosadsRecords;
                break;
            default:
                break;
        }

        return $results;
    }

    protected function getDataRelation($operation = null) {
        $valueArray = array();
        
        $valueArray[$this->bgdSosadsTableName] = $this->bgdSosadsRecords;
        
        return $valueArray;
    }
}

?>