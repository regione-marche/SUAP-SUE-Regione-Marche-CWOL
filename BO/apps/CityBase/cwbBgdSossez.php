<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGD.class.php';

function cwbBgdSossez() {
    $cwbBgdSossez = new cwbBgdSossez();
    $cwbBgdSossez->parseEvent();
    return;
}

class cwbBgdSossez extends cwbBpaGenTab {
    
    private $gridBgdSostag;
    private $GRID_NAME_TAG;
    private $bgdSostagTableName;
    private $bgdSostagRecords;
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBgdSossez';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    protected function initVars() {
        $this->GRID_NAME = 'gridBgdSossez';
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
        
        $this->GRID_NAME_TAG = 'gridBgdSostag';
        $this->bgdSostagTableName = 'BGD_SOSTAG';
        
        $this->addDescribeRelation($this->bgdSostagTableName, array('IDSOSSEZ' => 'IDSOSSEZ'), itaModelServiceData::RELATION_TYPE_ONE_TO_MANY);
        
        $this->gridBgdSostag = cwbParGen::getFormSessionVar($this->nameForm, $this->GRID_NAME_TAG);
        if ($this->gridBgdSostag == '') {
            $this->gridBgdSostag = array();
        }
        
        $this->bgdSostagRecords = cwbParGen::getFormSessionVar($this->nameForm, 'bgdSostagRecords');
        if ($this->bgdSostagRecords == '') {
            $this->bgdSostagRecords = array();
        }
    }
    
    protected function preDestruct() {
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, $this->GRID_NAME_TAG, $this->gridBgdSostag);
            cwbParGen::setFormSessionVar($this->nameForm, 'bgdSostagRecords', $this->bgdSostagRecords);
        }
    }
    
    protected function setGridFilters() {
        $this->gridFilters = array();
        if(!empty($_POST['IDSOSSEZ'])) {
            $this->gridFilters['IDSOSSEZ'] = $this->formData['IDSOSSEZ'];
        }
        if(!empty($_POST['SEZIONE'])) {
            $this->gridFilters['SEZIONE'] = $this->formData['SEZIONE'];
        }
        if(!empty($_POST['CODUTE'])){
           $this->gridFilters['CODUTE'] = $this->formData['CODUTE'];
        }
        if(!empty($_POST['FLAG_DIS'])){
           $this->gridFilters['FLAG_DIS'] = $this->formData['FLAG_DIS']-1;
        }
    }

    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        if(!isSet($filtri['IDSOSSEZ'])){
            $filtri['IDSOSSEZ'] = trim($this->formData[$this->nameForm . '_IDSOSSEZ']);
        }
        if(!isSet($filtri['SEZIONE'])){
            $filtri['SEZIONE'] = trim($this->formData[$this->nameForm . '_SEZIONE']);
        }
        
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBgdSossez($filtri, false, $sqlParams);
    }

    protected function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBgdSossez(array('IDSOSSEZ'=>$index), false, $sqlParams);
    }
    
    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_AltraRicerca':
                        $this->bgdSostagRecords = '';
                        break;
                }
                if (preg_match('/' . $this->nameForm . '_SEARCH_([0-9]*)/', $_POST['id'], $matches)) {
                    $rowId = $matches[1] - 1;
                    cwbLib::apriFinestraRicerca('cwbBgdSostag', $this->nameForm, 'returnBgdSostag', $rowId, true, null, $this->nameFormOrig);
                }
                if(preg_match('/'.$this->nameForm.'_OKBUTTON_([0-9]*)/',$_POST['id'],$matches)){
                    $rowId = $matches[1] - 1;
                    $this->setBgdSostag($rowId, $this->formData, false);
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case ($this->nameForm.'_'.$this->GRID_NAME_TAG):
                        $this->loadBgdSostag();
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case ($this->nameForm.'_'.$this->GRID_NAME_TAG):
                        $this->aggiungiBgdSostag();
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME_TAG:
                        $rowId = $_POST['rowid'] - 1;
                        $this->cancellaBgdSostag($rowId);
                        break;
                }
                break;
            case 'returnBgdSostag':
                $rowId = $this->elementId;
                $this->setBgdSostag($rowId, $this->formData, true);
                break;
        }
    }
    
    protected function postNuovo() {
        Out::hide($this->nameForm.'_BGD_SOSSEZ[IDSOSSEZ]');
        Out::hide($this->nameForm.'_BGD_SOSSEZ[IDSOSSEZ]_lbl');
        
        Out::setFocus("", $this->nameForm . '_BGD_SOSSEZ[SEZIONE]');
        
        Out::delAllRow($this->nameForm . '_' . $this->GRID_NAME_TAG);
    }
    
    protected function postDettaglio() {
        Out::show($this->nameForm.'_BGD_SOSSEZ[IDSOSSEZ]');
        Out::show($this->nameForm.'_BGD_SOSSEZ[IDSOSSEZ]_lbl');
        Out::disableField($this->nameForm.'_BGD_SOSSEZ[IDSOSSEZ]');
        
        Out::setFocus("", $this->nameForm . '_BGD_SOSSEZ[SEZIONE]');
        
        $idsossez = $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['IDSOSSEZ'];
        
        $filtri = array();
        $filtri['IDSOSSEZ'] = $idsossez;
        
        $this->bgdSostagRecords = $this->libDB->leggiBgdSostag($filtri);
        
        $this->loadBgdSostag();
    }
    
    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_IDSOSSEZ');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_IDSOSSEZ');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BGD_SOSSEZ[IDSOSSEZ]');
    }
    
    private function loadBgdSostag() {
        $tabHelper = new cwbBpaGenHelper();
        $tabHelper->setNameForm($this->nameForm);
        $tabHelper->setModelName($this->nameFormOrig);
        $tabHelper->setGridName($this->GRID_NAME_TAG);
        $tabHelper->setDb($this->MAIN_DB);

        $this->bgdSostagRecords = array_values($this->bgdSostagRecords);
        
        $data = $this->bgdSostagRecords;

        $data = $this->htmlForTagGrid($data);
        
        $this->gridBgdSostag = $data;

        $ita_grid01 = $tabHelper->initializeTableArray($data);
        $tabHelper->getDataPage($ita_grid01);

        TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME_TAG);
        cwbLibHtml::attivaJSElemento($this->nameForm . '_' . $this->GRID_NAME_TAG);
    }
    
    private function htmlForTagGrid($records){
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
                'id' => 'SEQUENZA',
                'type' => 'ita-readonly',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                'properties' => array(
                    'value' => $records[$key]['SEQUENZA'],
                    'style' => 'width: 95%;text-align: center;'
                )
            );
            $htmlRecords[$key]['SEQUENZA'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            
            $component = array(
                'id' => 'NOME_TAG',
                'type' => 'ita-readonly',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                'properties' => array(
                    'value' => $records[$key]['NOME_TAG'],
                    'style' => 'width: 95%;text-align: center;'
                )
            );
            $htmlRecords[$key]['NOME_TAG'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            
            $htmlRecords[$key]['KEY'] = $key + 1;
        }

        return $htmlRecords;
    }
    
    private function aggiungiBgdSostag() {
        //$this->addInsertOperation($this->bgdSostagTableName);
        $bgd_sostag = $this->getModelService()->define($this->MAIN_DB, 'BGD_SOSTAG');
        
        $sequenza = $this->getNextSequenza();
        $bgd_sostag['SEQUENZA'] = $sequenza;
        
        $this->bgdSostagRecords[] = $bgd_sostag;

        $this->loadBgdSostag();
    }
    
    private function cancellaBgdSostag($rowId) {
        $tagToDelete = array(
            'IDSOSTAG' => $this->bgdSostagRecords[$rowId]['IDSOSTAG']
        );
        
        $this->addDeleteOperation($this->bgdSostagTableName, $tagToDelete);
        unset($this->bgdSostagRecords[$rowId]);

        $this->loadBgdSostag();
    }
    
    private function setBgdSostag($key,$data){
        
        $sequenza = $this->bgdSostagRecords[$key]['SEQUENZA'];
        
        $pk = array('IDSOSTAG' => $data['returnData']['IDSOSTAG']);
            
        $this->addUpdateOperation($this->bgdSostagTableName, $pk);
        
        $this->bgdSostagRecords[$key] = $data['returnData'];
        $this->bgdSostagRecords[$key]['SEQUENZA'] = $sequenza;
        
        $this->loadBgdSostag();
    }
    
    protected function getDataRelationView($tableName, $alias = null) {
        $key = $alias ? $alias : $tableName;
        $results = array();

        switch ($key) {
            case $this->bgdSostagTableName:
                $results = $this->bgdSostagRecords;
                break;
            default:
                break;
        }

        return $results;
    }

    protected function getDataRelation($operation = null) {
        $valueArray = array();
        
        $valueArray[$this->bgdSostagTableName] = $this->bgdSostagRecords;
        
        return $valueArray;
    }
    
    private function getNextSequenza() {
        $max = 0;
        foreach ($this->bgdSostagRecords as $key => $value) {
            if($this->bgdSostagRecords[$key]['SEQUENZA'] > $max) {
                $max = $this->bgdSostagRecords[$key]['SEQUENZA'];
            }
        }
        
        return $max + 10;
    }

}

?>