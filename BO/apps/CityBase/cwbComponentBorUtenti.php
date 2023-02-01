<?php
/**
 * SELETTORE PER BOR_UTENTI
 */
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

function cwbComponentBorUtenti() {
    $cwbComponentBorUtenti = new cwbComponentBorUtenti();
    $cwbComponentBorUtenti->parseEvent();
    return;
}

class cwbComponentBorUtenti extends itaFrontControllerCW {
    private $returnData;
    private $callbackFunction;
    private $dbLib;
    
    public function __construct($nameFormOrig=null, $nameForm=null) {
        if(!isSet($nameForm) || !isSet($nameFormOrig)){
            $nameFormOrig = 'cwbComponentBorUtenti';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
        
        $this->dbLib = new cwbLibDB_BOR();
        $this->returnData = cwbParGen::getFormSessionVar($this->nameForm, 'returnData');
        if($this->returnData == ''){
            $this->returnData = array();
        }
        $this->callbackFunction = cwbParGen::getFormSessionVar($this->nameForm, 'callbackData');
        if($this->callbackFunction == ''){
            $this->callbackFunction = array();
        }
    }
    
    public function __destruct() {
        if(!$this->close){
            cwbParGen::setFormSessionVar($this->nameForm, 'returnData', $this->returnData);
            cwbParGen::setFormSessionVar($this->nameForm, 'callbackData', $this->callbackFunction);
        }
        parent::__destruct();
    }
    
    public function parseEvent() {
        parent::parseEvent();
        switch($_POST['event']) {
            case 'onClick':
                switch($_POST['id']){
                    case $this->nameForm . '_CODUTE_butt':
                        $this->openLookup();
                        break;
                }
                break;
            case 'onChange':
                switch($_POST['id']){
                    case $this->nameForm . '_CODUTE':
                        $this->updateUtente($_POST[$this->nameForm . '_CODUTE']);
                        break;
                }
                break;
            case 'returnCODUTE':
                $this->setUtente($this->formData['returnData']);
                $this->closeJqGrid();
                break;
        }
    }
    
    public function setDescriptionWidth($width){
        Out::css($this->nameForm . '_NOMEUTE', 'width', $width.'px');
    }
    
    public function setLabel($label=null,$width=null){
        if(isSet($label)){
            Out::setLabel($this->nameForm . '_CODUTE', $label);
        }
        if(isSet($width)){
            Out::css($this->nameForm . '_CODUTE_lbl', 'width', $width.'px');
        }
    }
    
    public function setReturnData($returnData=array()){
        $this->returnData = $returnData;
    }
    
    public function setCallbackFunction($functionName,$parentModel,$parentName=null){
        $this->callbackFunction = array();
        $this->callbackFunction['functionName'] = $functionName;
        $this->callbackFunction['parentModel'] = $parentModel;
        $this->callbackFunction['parentName'] = (isSet($parentName) ? $parentName : $parentModel);
    }
    
    private function openLookup(){
        $alias = 'utiJqGridCustom'.time().rand(0,1000);
        $model = cwbLib::apriFinestra('utiJqGridCustom', $this->nameForm, null, null, null, $this->nameFormOrig, $alias);
        
        cwbParGen::setFormSessionVar($this->nameForm, 'utiJqGridCustomAlias', $alias);
        
        $dbName = 'ITW';
        $sql = 'SELECT
                    UTENTI.ROWID AS ROWID,
                    UTENTI.UTECOD AS UTECOD,
                    UPPER(UTENTI.UTELOG) AS UTELOG,
                    GRUPPI.GRUDES AS GRUDES,
                    RICHUT.RICCOG AS RICCOG,
                    RICHUT.RICNOM AS RICNOM,
                    UTENTI.UTEUPA AS UTEUPA,
                    UTENTI.UTEDPA AS UTEDPA,
                    UTENTI.UTESPA AS UTESPA,
                    UTENTI.UTEFIL__1 AS UTEFIL__1,
                    UTENTI.UTEFIL__2 AS UTEFIL__2,
                    UTENTI.UTEFIA__1 AS UTEFIA__1,
                    UTENTI.UTEFIS AS UTEFIS
                FROM UTENTI 
                LEFT OUTER JOIN GRUPPI ON UTENTI.UTEGRU=GRUPPI.GRUCOD
                LEFT OUTER JOIN RICHUT ON UTENTI.UTECOD=RICHUT.RICCOD';
        
        $colModel = array(
            array('name'=>'UTELOG', 'title'=>'Login', 'width'=>'270'),
            array('name'=>'GRUDES', 'title'=>'Gruppo', 'width'=>'150'),
            array('name'=>'RICNOM', 'title'=>'Nome', 'width'=>'100'),
            array('name'=>'RICCOG', 'title'=>'Cognome', 'width'=>'100'),
            array('name'=>'UTEFIS', 'title'=>'C.F.', 'width'=>'140'),
            array('name'=>'UTEUPA', 'title'=>'Ultima Password', 'width'=>'80', 'class'=>'{formatter: eqdate, search: false}'),
            array('name'=>'UTEDPA', 'title'=>'Durata', 'width'=>'40', 'class'=>'{search: false}'),
            array('name'=>'UTESPA', 'title'=>'Scadenza', 'width'=>'80', 'class'=>'{formatter: eqdate, search: false}'),
            array('name'=>'UTEFIL__1', 'title'=>'N.Accessi', 'width'=>'40', 'class'=>'{search: false}'),
            array('name'=>'UTEFIL__2', 'title'=>'Minuti Inattivi', 'width'=>'40', 'class'=>'{search: false}'),
            array('name'=>'UTEFIA__1', 'title'=>'Indirizzo IP', 'width'=>'100'),
        );
        $metadata = array(
            'caption'=>'Utenti',
            'shrinkToFit'=>false,
            'width'=>1000,
            'readerId'=>'UTELOG',
            'sortname'=>'UTELOG',
            'navGrid'=>true,
            'navButtonDel'=>false,
            'navButtonAdd'=>false,
            'navButtonEdit'=>false,
            'navButtonExcel'=>false,
            'navButtonPrint'=>false,
            'filterToolbar'=>true,
            'navButtonRefresh'=>true,
            'resizeToParent'=>true,
            'showInlineButtons'=>'{view: false, edit: false, delete: false}',
            'showAuditColumns'=>false,
            'showRecordStatus'=>false,
//            'onSelectRow'=>true,
            'multiselect'=>false,
            'multiselectEvents'=>false,
            'navButtonExcel'=>false,
            'navButtonPrint'=>false,
            'rowNum'=>25,
            'rowList'=>'[25, 50, 100, 200, \'Tutte\']',
            'reloadOnResize'=>false
        );
                
        $model->setJqGridModel($colModel, $metadata);
        $model->setJqGridDataDB($sql, $dbName);
        $model->setReturnEvents(null, 'returnCODUTE');
        $model->setTitle('Utenti');
        $model->render();
        
//        cwbLib::apriFinestraRicerca('cwbBorUtenti', $this->nameForm, 'returnCODUTE', '', true, null, $this->nameFormOrig);
    }
    
    private function closeJqGrid(){
        $model = itaModel::getInstance('utiJqGridCustom', cwbParGen::getFormSessionVar($this->nameForm, 'utiJqGridCustomAlias'));
        $model->closeGrid();
    }
    
    private function setUtente($data){
        Out::valore($this->nameForm . '_CODUTE', $data['UTELOG']);
        Out::valore($this->nameForm . '_NOMEUTE', $data['RICCOG'].' '.$data['RICNOM']);
        
        foreach($this->returnData as $field=>$formElement){
            if(isSet($data[$field])){
                Out::valore($formElement, $data[$field]);
            }
        }
        
        $this->callbackFunction($data);
    }
    
    public function updateUtente($codute){
        if(empty($codute)){
            $utente = null;
        }
        else{
            $db = ItaDB::DBOpen('ITW');
            $sql = 'SELECT
                    UTENTI.ROWID AS ROWID,
                    UTENTI.UTECOD AS UTECOD,
                    UPPER(UTENTI.UTELOG) AS UTELOG,
                    GRUPPI.GRUDES AS GRUDES,
                    RICHUT.RICCOG AS RICCOG,
                    RICHUT.RICNOM AS RICNOM,
                    UTENTI.UTEUPA AS UTEUPA,
                    UTENTI.UTEDPA AS UTEDPA,
                    UTENTI.UTESPA AS UTESPA,
                    UTENTI.UTEFIL__1 AS UTEFIL__1,
                    UTENTI.UTEFIL__2 AS UTEFIL__2,
                    UTENTI.UTEFIA__1 AS UTEFIA__1,
                    UTENTI.UTEFIS AS UTEFIS
                FROM UTENTI 
                LEFT OUTER JOIN GRUPPI ON UTENTI.UTEGRU=GRUPPI.GRUCOD
                LEFT OUTER JOIN RICHUT ON UTENTI.UTECOD=RICHUT.RICCOD
                WHERE UPPER(UTELOG) = \''.strtoupper(trim(addslashes($codute))).'\'';
            $utente = ItaDB::DBSQLSelect($db, $sql, false);
        }
        
        if($utente){
            $this->setUtente($utente);
        }
        else{
            Out::valore($this->nameForm . '_CODUTE', '');
            Out::valore($this->nameForm . '_NOMEUTE', '');
            foreach($this->returnData as $formElement){
                Out::valore($formElement, '');
            }
        }
    }
    
    private function callbackFunction($data){
        if(is_array($this->callbackFunction) && !empty($this->callbackFunction['functionName'])){
            $parent = itaFrontController::getInstance($this->callbackFunction['parentModel'], $this->callbackFunction['parentName']);
            $function = $this->callbackFunction['functionName'];
            $parent->$function($data);
        }
    }
    
    public function disableFields(){
        Out::disableField($this->nameForm . '_CODUTE');
    }
    
    public function enableFields(){
        Out::enableField($this->nameForm . '_CODUTE');
    }
}