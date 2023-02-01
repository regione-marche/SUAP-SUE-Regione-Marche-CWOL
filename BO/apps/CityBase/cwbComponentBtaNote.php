<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbComponentBtaNote() {
    $cwbComponentBtaNote = new cwbComponentBtaNote();
    $cwbComponentBtaNote->parseEvent();
    return;
}

class cwbComponentBtaNote extends itaFrontControllerCW {
    private $fatherModelName;
    private $fatherName;
    private $fatherModel;
    private $operations;
    private $isComponent;
    private $progsogg;
    private $dataArray;
    private $libDB;
    private $authenticator;
    private $skipAuth;
    
    private $readOnly;
    
    protected function initVars() {
        $this->TABLE_NAME = 'BTA_NOTE';
        $this->GRID_NAME = 'gridBtaNote';
        //$this->AUTOR_MODULO = 'BTA';
        //$this->AUTOR_NUMERO = 11;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BTA();
               
        $this->operations = cwbParGen::getFormSessionVar($this->nameForm, 'operations');
        if($this->operations == ''){
            $this->operations = array();
        }
        $this->fatherModelName = cwbParGen::getFormSessionVar($this->nameForm, 'fatherModelName');
        $this->fatherName = cwbParGen::getFormSessionVar($this->nameForm, 'fatherName');
        $this->isComponent = cwbParGen::getFormSessionVar($this->nameForm, 'isComponent');
        $this->progsogg = cwbParGen::getFormSessionVar($this->nameForm, 'progsogg');
//        if($this->isComponent === true){
            $this->dataArray = cwbParGen::getFormSessionVar($this->nameForm, 'dataArray');
            $this->readOnly = cwbParGen::getFormSessionVar($this->nameForm, 'readOnly');
//        }
    }
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbComponentBtaNote';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        
//        $this->TABLE_NAME = 'BTA_NOTE';
//        $this->GRID_NAME = 'gridBtaNote';
//        //$this->AUTOR_MODULO = 'BTA';
//        //$this->AUTOR_NUMERO = 11;
//        $this->searchOpenElenco = true;
//        $this->libDB = new cwbLibDB_BTA();
        
        parent::__construct($nameFormOrig, $nameForm);
        
//        $this->operations = cwbParGen::getFormSessionVar($this->nameForm, 'operations');
//        if($this->operations == ''){
//            $this->operations = array();
//        }
//        $this->fatherModelName = cwbParGen::getFormSessionVar($this->nameForm, 'fatherModelName');
//        $this->fatherName = cwbParGen::getFormSessionVar($this->nameForm, 'fatherName');
//        $this->isComponent = cwbParGen::getFormSessionVar($this->nameForm, 'isComponent');
//        $this->progsogg = cwbParGen::getFormSessionVar($this->nameForm, 'progsogg');
//        if($this->isComponent === true){
//            $this->dataArray = cwbParGen::getFormSessionVar($this->nameForm, 'dataArray');
//            $this->readOnly = cwbParGen::getFormSessionVar($this->nameForm, 'readOnly');
//        }
        
        $this->initAuthenticator();
        $this->checkAutor();
    }
    
    protected function initAuthenticator() {
        if ($this->skipAuth != true) {
            $autorLevel = cwbParGen::getSessionVar($this->nameForm);
            $autorLevel = isSet($autorLevel['_autorLevel']) ? $autorLevel['_autorLevel'] : null;
            $this->authenticator = cwbAuthenticatorFactory::getAuthenticator($this->nameFormOrig, $this->prepareAuthenticatorParams($autorLevel));
        }
    }
    
    private function prepareAuthenticatorParams($autorLevel) {
        return array(
            'username' => cwbParGen::getSessionVar('nomeUtente'),
            'modulo' => $this->AUTOR_MODULO,
            'num' => $this->AUTOR_NUMERO,
            'level' => $autorLevel
        );
    }
    
    private function checkAutor() {
        if ($this->skipAuth == true || $this->authenticator->getLevel() !== null || ($this->params['modulo'] == null && $this->AUTOR_MODULO == null)) {
            return;
        }

        if ($this->authenticator->missingAuthentication()) {
            Out::msgStop("Autorizzazioni mancanti", $this->authenticator->getMissingAuthenticationMessage());
            $this->close();
        }
    }
    
    public function __destruct() {
        if(!$this->close){
            cwbParGen::setFormSessionVar($this->nameForm, 'operations', $this->operations);
            cwbParGen::setFormSessionVar($this->nameForm, 'fatherModelName', $this->fatherModelName);
            cwbParGen::setFormSessionVar($this->nameForm, 'fatherName', $this->fatherName);
            cwbParGen::setFormSessionVar($this->nameForm, 'isComponent', $this->isComponent);
            cwbParGen::setFormSessionVar($this->nameForm, 'progsogg', $this->progsogg);
            if($this->isComponent === true){
                cwbParGen::setFormSessionVar($this->nameForm, 'dataArray', $this->dataArray);
                cwbParGen::setFormSessionVar($this->nameForm, 'readOnly', $this->readOnly);
            }
        }
        parent::__destruct();
    }
    
    public function parseEvent() {
        parent::parseEvent();
        
        switch($_POST['event']){
            case 'onClickTablePager':
                if($_POST['id'] == $this->nameForm . '_' . $this->GRID_NAME){
                    $this->elenca();
                }
                break;
            case 'addGridRow':
                if($_POST['id'] == $this->nameForm . '_' . $this->GRID_NAME){
                    $this->insertData(array(
                        'PROGNOTE' => null,
                        'RIGANOTA' => null,
                        'TIPONOTA' => null,
                        'NATURANOTA' => null,
                        'ANNOTAZ' => '',
                        'NOTA_EV' => null,
                        'NOTA_UR' => null,
                        'PROGSOGG' => $this->progsogg
                    ));
                    $this->elenca();
                }
                break;
            case 'delGridRow':
                if($_POST['id'] == $this->nameForm . '_' . $this->GRID_NAME){
                    $this->deleteData($_POST['rowid']);
                    $this->elenca();
                }
                break;
            case 'afterSaveCell':
                if(preg_match('/'.$this->nameForm.'_(.*)_([0-9]*)$/', $_POST['id'], $matches)){
                    $field = $matches[1];
                    $id = $matches[2];

                    if($field == 'NOTA_EV'){
                        $value = ($this->dataArray[$id]['NOTA_EV'] + 1) % 2;
                    }
                    else{
                        $value = $_POST['value'];
                    }
                    
                    $data = $this->dataArray[$id];
                    $data[$field] = $value;
                    $this->modifyData($id, $data);
                }
                break;
        }
    }
    
    private function elenca(){
        $records = $this->elaboraNoteRecords($this->dataArray);

        $ita_grid01 = $this->helper->initializeTableArray($records);
        $ita_grid01->getDataPage('json');

        TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME);
        cwbLibHtml::attivaJSElemento($this->nameForm . '_' . $this->GRID_NAME);
    }
    
    private function elaboraNoteRecords($records) {
        $htmlRecords = array();
        
        //$disable = (($this->authenticator->getLevel() !== 'C' && !$this->authenticator->getLevel() !== 'G') || $this->viewMode);
        $disable = $this->viewMode;
        $natura = $this->libDB->leggiBtaNtnote(array('TABLENOTE'=>'BTA_NOTE'));

        foreach ($records as $value) {
            $key = $value['CUSTOMKEY'];
            
            $htmlRecords[$key] = $value;

            $htmlRecords[$key]['CUSTOMKEY'] = $key;
            
            $htmlRecords[$key]['RIGANOTA'] = $value['RIGANOTA'];

            $component = array(
                'id' => 'TIPONOTA',
                'type' => 'ita-select',
                'model' => $this->nameForm,
                'rowKey' => $key,
                'onChangeEvent' => true,
                'options' => array(
                    array(
                        'value'=>0,
                        'text'=>''
                    ),
                    array(
                        'value'=>1,
                        'text'=>'Nota visibile e stampabile'
                    ),
                    array(
                        'value'=>2,
                        'text'=>'Nota non stampabile'
                    )
                )
            );
            if(isSet($component['options'][$value['TIPONOTA']])){
                $component['options'][$value['TIPONOTA']]['selected'] = 1;
            }
            
            if($disable){
                $component['properties'] = array();
                $component['properties']['disabled'] = '';
            }
            $htmlRecords[$key]['TIPONOTA'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            
            $component = array(
                'id' => 'NATURANOTA',
                'type' => 'ita-select',
                'model' => $this->nameForm,
                'rowKey' => $key,
                'onChangeEvent' => true,
                'options' => array(
                    array(
                        'value'=>0,
                        'text'=>''
                    )
                )
            );
            foreach($natura as $row){
                $option = array(
                    'value'=>$row['NATURANOTA'],
                    'text'=>$row['DESNATURA']
                );
                if($row['NATURANOTA'] == $value['NATURANOTA']){
                    $option['selected'] = 1;
                }
                $component['options'][] = $option;
            }
            if($disable){
                $component['properties'] = array();
                $component['properties']['disabled'] = '';
            }
            $htmlRecords[$key]['NATURANOTA'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            
            $component = array(
                'id' => 'NOTA_EV',
                'type' => 'ita-checkbox',
                'model' => $this->nameForm,
                'rowKey' => $key,
                'onChangeEvent' => true,
                'properties' => array()
            );
            if($value['NOTA_EV'] == 1){
                $component['properties']['checked'] = 1;
            }
            if($disable){
                $component['properties']['disabled'] = '';
            }
            $htmlRecords[$key]['NOTA_EV'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            
            $component = array(
                'id' => 'ANNOTAZ',
                'type' => 'ita-edit',
                'model' => $this->nameForm,
                'rowKey' => $key,
                'onChangeEvent' => true,
                'properties' => array(
                    'value' => $value['ANNOTAZ'],
                    'style' => 'width: 95%;'
                )
            );
            if($disable){
                $component['type'] = 'ita-readonly';
            }
            $htmlRecords[$key]['ANNOTAZ'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
        }

        return $htmlRecords;
    }
    /*
     * INIZIO GESTIONE COMPONENTE INTEGRATO
     */
    public function initComponent($fatherModelName,$fatherName){
        $this->fatherModelName = $fatherModelName;
        $this->fatherName = $fatherName;
        $this->isComponent = true;
        Out::removeElement($this->nameForm . '_buttonBar');
        
//        $codice =  'itaGetLib("libs/elementResize/jquery.resize.js");
//                    
//                    function '.$this->nameForm.'Resize(){
//                        $("#'.$this->nameForm.'_wrapper").height($("#'.$fatherName.'_tabDettagli").height()-70);
//                        $("#'.$this->nameForm.'_'.$this->GRID_NAME.'").trigger("resize");
//                    }
//                    
//                    $("#'.$fatherName.'_tabDettagli").resize('.$this->nameForm.'Resize);
//                    '.$this->nameForm.'Resize();';
//        Out::codice($codice);
    }
    
    private function getForeignKey(){
        switch($this->fatherModelName){
            case 'cwbBtaSogg': return array('PROGNOTE' => 'PROGNOTE');
        }
        return null;
    }
    
    private function getRelationType(){
        switch($this->fatherModelName){
            case 'cwbBtaSogg': return itaModelServiceData::RELATION_TYPE_ONE_TO_MANY;
        }
        return null;
    }
    
    public function getRelation(){
        return array(
                'table'=>$this->TABLE_NAME,
                'foreignKey'=>$this->getForeignKey(),
                'type'=>$this->getRelationType(),
                'alias'=>$this->nameForm
            );
    }
    
    public function getOperations(){
        ksort($this->operations);
        return $this->operations;
    }  
    
    private function resetOperations(){
        $this->operations = array();
    }
    
    private function insertData($data){
        end($this->operations);
        end($this->dataArray);
        $key = max(array_merge(array_keys($this->operations), array_keys($this->dataArray), array(-1)))+1;
        
        $data['CUSTOMKEY'] = $key;
        
        $this->dataArray[$key] = $data;
        $this->operations[$key] = array(
            'table'=>$this->TABLE_NAME,
            'operation'=>itaModelService::OPERATION_INSERT,
            'alias'=>$this->nameForm
        );
    }
    
    private function modifyData($key,$data){
        $customKey = $this->dataArray[$key]['CUSTOMKEY'];
        $this->dataArray[$key] = $data;
        $this->dataArray[$key]['CUSTOMKEY'] = $customKey;
        
        if(!isSet($this->operations[$key]) || $this->operations[$key]['operation'] !== itaModelService::OPERATION_INSERT){
            $operation = array();
            $operation['table'] = $this->TABLE_NAME;
            $operation['operation'] = itaModelService::OPERATION_UPDATE;
            $operation['keys'] = array();
            foreach($this->getForeignKey() as $fatherKey=>$localKey){
                $operation['keys'][$fatherKey] = $data[$localKey];
            }
            $operation['alias'] = $this->nameForm;
        
            $this->operations[$key] = $operation;
        }
    }
    
    private function deleteData($key){
        if(isSet($this->operations[$key]) && $this->operations[$key]['operation'] === itaModelService::OPERATION_INSERT){
            unset($this->operations[$key]);
        }
        else{
            $operation = array();
            $operation['table'] = $this->TABLE_NAME;
            $operation['operation'] = itaModelService::OPERATION_DELETE;
            $operation['keys'] = array();
            $operation['keys']['PROGNOTE'] = $this->dataArray[$key]['PROGNOTE'];
            $operation['keys']['RIGANOTA'] = $this->dataArray[$key]['RIGANOTA'];
            $operation['alias'] = $this->nameForm;
            
            $this->operations[$key] = $operation;
        }
        unset($this->dataArray[$key]);
    }
    
    public function getData(){
        $return = array();
        foreach($this->operations as $key=>$operation){
            if($operation['operation'] !== itaModelService::OPERATION_DELETE){
                $return[$key] = $this->dataArray[$key];
                unset($return[$key]['CUSTOMKEY']);
            }
        }
        ksort($return);
        return $return;
    }
    
    public function openNuovo(){
        $this->resetData();
        $this->elenca();
    }
    
    public function openDettaglio($data,$vis){
        $this->resetOperations();
        $this->readOnly = $vis;
        $this->progsogg = $data['PROGSOGG'];
        
        $this->dataArray = array();
        $filtri = array(
            'PROGSOGG'=>$data['PROGSOGG']
        );
        $this->dataArray = $this->libDB->leggiBtaNoteComponent($filtri,true);
        foreach(array_keys($this->dataArray) as $key){
            $this->dataArray[$key]['CUSTOMKEY'] = $key;
        }
        
        $this->elenca();
    }
    
    public function resetData(){
        $this->resetOperations();
        
        $this->dataArray = array();
    }
    
    public function unlockRecord(){
    }
    
    private function getBtaSoggData(){
        if(!isSet($this->fatherModel)){
            $this->fatherModel = itaFrontController::getInstance($this->fatherModelName, $this->fatherName);
        }
        return $this->fatherModel->getBtaSoggData();
    }
    
    public function getDataFromDB(){
        $return = array();
        $return['alias'] = $this->nameForm;
        
        $return['data'] = $this->libDB->leggiBtaNoteComponent(array('PROGSOGG'=>$this->progsogg));
        
        return $return;
    }
    
    public function eventShow(){
        Out::gridForceResize($this->nameForm . '_' . $this->GRID_NAME);
    }
    /*
     * FINE GESTIONE COMPONENTE INTEGRATO
     */
}
?>