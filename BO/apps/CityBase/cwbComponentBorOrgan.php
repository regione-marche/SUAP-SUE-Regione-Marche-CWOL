<?php
/**
 * SELETTORE PER BOR_ORGAN
 */
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibBor.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfAuthHelper.php';

function cwbComponentBorOrgan() {
    $cwbComponentBorOrgan = new cwbComponentBorOrgan();
    $cwbComponentBorOrgan->parseEvent();
    return;
}

class cwbComponentBorOrgan extends itaFrontControllerCW {
    private $dbLib;
    private $returnData;
    private $progEnte;
    private $options;
    private $LxORGData;
    private $prefix;
    private $callbackFunction;
    private $externalFilters;
    
    public function __construct($nameFormOrig=null, $nameForm=null) {
        if(!isSet($nameForm) || !isSet($nameFormOrig)){
            $nameFormOrig = 'cwbComponentBorOrgan';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
        
        $this->dbLib = new cwbLibDB_BOR();
        $this->progEnte = cwbParGen::getProgEnte();
        $this->options = cwbParGen::getFormSessionVar($this->nameForm, 'options');
        if(isSet($this->options['compact']) && $this->options['compact'] === true){
            $this->prefix = $this->nameForm . '_selector_c_';
        }
        else{
            $this->prefix = $this->nameForm . '_selector_s_';
        }
        $this->returnData = cwbParGen::getFormSessionVar($this->nameForm, 'returnData');
        if($this->returnData == ''){
            $this->returnData = array();
        }
        $this->callbackFunction = cwbParGen::getFormSessionVar($this->nameForm, 'callbackFunction');
        $this->externalFilters = cwbParGen::getFormSessionVar($this->nameForm, 'externalFilters');
        if($this->externalFilters == ''){
            $this->externalFilters = array();
        }
    }
    
    public function __destruct() {
        if(!$this->close){
            cwbParGen::setFormSessionVar($this->nameForm, 'options', $this->options);
            cwbParGen::setFormSessionVar($this->nameForm, 'returnData', $this->returnData);
            cwbParGen::setFormSessionVar($this->nameForm, 'callbackFunction', $this->callbackFunction);
            cwbParGen::setFormSessionVar($this->nameForm, 'externalFilters', $this->externalFilters);
        }
        parent::__destruct();
    }
    
    public function parseEvent() {
        parent::parseEvent();
        switch($_POST['event']) {
            case 'openform':
                if(isSet($_POST['init'])){
                    $this->initSelector(true);
                }
                break;
            case 'onClick':
                switch($_POST['id']){
                    case $this->prefix . 'searchBtn':
                        $externalFilter = array();
                        if(isSet($_POST[$this->prefix . 'L1ORG']) && trim($_POST[$this->prefix . 'L1ORG']) != ''){
                            $externalFilter['L1ORG'] = array();
                            $externalFilter['L1ORG']['PERMANENTE'] = false;
                            $externalFilter['L1ORG']['VALORE'] = trim($_POST[$this->prefix . 'L1ORG']);
                        }
                        if(isSet($_POST[$this->prefix . 'L2ORG']) && trim($_POST[$this->prefix . 'L2ORG']) != ''){
                            $externalFilter['L2ORG'] = array();
                            $externalFilter['L2ORG']['PERMANENTE'] = false;
                            $externalFilter['L2ORG']['VALORE'] = trim($_POST[$this->prefix . 'L2ORG']);
                        }
                        if(isSet($_POST[$this->prefix . 'L3ORG']) && trim($_POST[$this->prefix . 'L3ORG']) != ''){
                            $externalFilter['L3ORG'] = array();
                            $externalFilter['L3ORG']['PERMANENTE'] = false;
                            $externalFilter['L3ORG']['VALORE'] = trim($_POST[$this->prefix . 'L3ORG']);
                        }
                        if(isSet($_POST[$this->prefix . 'L4ORG']) && trim($_POST[$this->prefix . 'L4ORG']) != ''){
                            $externalFilter['L4ORG'] = array();
                            $externalFilter['L4ORG']['PERMANENTE'] = false;
                            $externalFilter['L4ORG']['VALORE'] = trim($_POST[$this->prefix . 'L4ORG']);
                        }
                        $kruolo = cwbParGen::getRuolo();
                        if(!empty($kruolo)){
                            $externalFilter['KRUOLO'] = array();
                            $externalFilter['KRUOLO']['PERMANENTE'] = true;
                            $externalFilter['KRUOLO']['VALORE'] = $kruolo;
                        }
                        $externalFilter['CODUTE_UTEORG'] = array();
                        $externalFilter['CODUTE_UTEORG']['PERMANENTE'] = true;
                        $externalFilter['CODUTE_UTEORG']['VALORE'] = cwbParGen::getUtente();
                        
                        $externalFilter = array_merge($this->externalFilters, $externalFilter);
                        cwbLib::apriFinestraRicerca('cwbBorOrgan', $this->nameForm, 'returnOrgan', '', true, $externalFilter, $this->nameFormOrig);
                        break;
                    case $this->nameForm . '_clearButton':
                        $this->setLxORG();
                        break;
                }
                break;
            case 'onChange':
                switch($_POST['id']){
                    case $this->prefix . 'L1ORG':
                        $l1 = (isSet($_POST[$this->prefix . 'L1ORG']) && trim($_POST[$this->prefix . 'L1ORG']) != '' ? trim($_POST[$this->prefix . 'L1ORG']) : null);
                        $this->setLxORG($l1, null, null, null, true);
                        break;
                    case $this->prefix . 'L2ORG':
                        $l1 = (isSet($_POST[$this->prefix . 'L1ORG']) && trim($_POST[$this->prefix . 'L1ORG']) != '' ? trim($_POST[$this->prefix . 'L1ORG']) : null);
                        $l2 = (isSet($_POST[$this->prefix . 'L2ORG']) && trim($_POST[$this->prefix . 'L2ORG']) != '' ? trim($_POST[$this->prefix . 'L2ORG']) : null);
                        $this->setLxORG($l1, $l2, null, null, true);
                        break;
                    case $this->prefix . 'L3ORG':
                        $l1 = (isSet($_POST[$this->prefix . 'L1ORG']) && trim($_POST[$this->prefix . 'L1ORG']) != '' ? trim($_POST[$this->prefix . 'L1ORG']) : null);
                        $l2 = (isSet($_POST[$this->prefix . 'L2ORG']) && trim($_POST[$this->prefix . 'L2ORG']) != '' ? trim($_POST[$this->prefix . 'L2ORG']) : null);
                        $l3 = (isSet($_POST[$this->prefix . 'L3ORG']) && trim($_POST[$this->prefix . 'L3ORG']) != '' ? trim($_POST[$this->prefix . 'L3ORG']) : null);
                        $this->setLxORG($l1, $l2, $l3, null, true);
                        break;
                    case $this->prefix . 'L4ORG':
                        $l1 = (isSet($_POST[$this->prefix . 'L1ORG']) && trim($_POST[$this->prefix . 'L1ORG']) != '' ? trim($_POST[$this->prefix . 'L1ORG']) : null);
                        $l2 = (isSet($_POST[$this->prefix . 'L2ORG']) && trim($_POST[$this->prefix . 'L2ORG']) != '' ? trim($_POST[$this->prefix . 'L2ORG']) : null);
                        $l3 = (isSet($_POST[$this->prefix . 'L3ORG']) && trim($_POST[$this->prefix . 'L3ORG']) != '' ? trim($_POST[$this->prefix . 'L3ORG']) : null);
                        $l4 = (isSet($_POST[$this->prefix . 'L4ORG']) && trim($_POST[$this->prefix . 'L4ORG']) != '' ? trim($_POST[$this->prefix . 'L4ORG']) : null);
                        $this->setLxORG($l1, $l2, $l3, $l4, true);
                        break;
                }
                break;
            case 'returnOrgan':
                $l1 = (trim($this->formData['returnData']['L1ORG']) != '00' ? trim($this->formData['returnData']['L1ORG']) : null);
                $l2 = (trim($this->formData['returnData']['L2ORG']) != '00' ? trim($this->formData['returnData']['L2ORG']) : null);
                $l3 = (trim($this->formData['returnData']['L3ORG']) != '00' ? trim($this->formData['returnData']['L3ORG']) : null);
                $l4 = (trim($this->formData['returnData']['L4ORG']) != '00' ? trim($this->formData['returnData']['L4ORG']) : null);
                $this->setLxORG($l1, $l2, $l3, $l4, true, true);
                break;
            
        }
    }
    
    public function initSelector($compact=false,$disable=false,$showClearButton=false,$ignoreAuth=false,$authSource=cwfAuthHelper::SOURCE_BASE){
        $this->options = array();
        $this->options['compact'] = $compact;
        $this->options['showClearButton'] = $showClearButton;
        $this->options['useAuth'] = !$ignoreAuth;
        $this->options['authSource'] = $authSource;
        
        if($this->options['compact']){
            $this->prefix = $this->nameForm . '_selector_c_';
            
            Out::hide($this->nameForm . '_selector_select');
            Out::show($this->nameForm . '_selector_compact');
            Out::hideElementFromClass($this->nameForm, 'ita-br');
        }
        else{
            $this->prefix = $this->nameForm . '_selector_s_';
            
            Out::show($this->nameForm . '_selector_select');
            Out::hide($this->nameForm . '_selector_compact');
        }
        
        $this->LxORGData = cwbLibBor::getLxORGData(false, cwbParGen::getProgEnte(), $this->externalFilters, $this->options['useAuth']);
        $this->initLxORG();
        
        if($disable){
            $this->disableFields();
        }
        if(!$showClearButton){
            $this->hideClearButton();
        }
    }
    
    public function setDescriptionWidth($width){
        if($this->options['compact'] == true){
            if($width == 0){
                Out::hide($this->prefix . 'DESPORG');
            }
            else{
                Out::css($this->prefix . 'DESPORG', 'width', $width.'px');
                Out::show($this->prefix . 'DESPORG');
            }
        }
        else{
            Out::css($this->prefix . 'L1ORG', 'width', $width.'px');
            Out::css($this->prefix . 'L2ORG', 'width', $width.'px');
            Out::css($this->prefix . 'L3ORG', 'width', $width.'px');
            Out::css($this->prefix . 'L4ORG', 'width', $width.'px');
        }
        
    }
    
    public function setLabel($label=null,$width=null){
        if($this->options['compact'] == true){
            if(isSet($label)){
                Out::setSpanAsLabel($this->prefix . 'label', $label);
            }
            if(isSet($width)){
                Out::css($this->prefix . 'label', 'width', $width.'px');
                Out::css($this->prefix . 'label', 'margin-right', '4px');
            }
        }
        else{
            if(isSet($width)){
                Out::css($this->prefix . 'L1ORG_lbl', 'width', $width.'px');
                Out::css($this->prefix . 'L2ORG_lbl', 'width', $width.'px');
                Out::css($this->prefix . 'L3ORG_lbl', 'width', $width.'px');
                Out::css($this->prefix . 'L4ORG_lbl', 'width', $width.'px');
            }
        }
    }
    
    public function setExternalFilters($externalFilters){
        $this->externalFilters = $externalFilters;
        $auth = isSet($this->options['useAuth']) ? $this->options['useAuth'] : true;
        $this->LxORGData = cwbLibBor::getLxORGData(false, cwbParGen::getProgEnte(), $this->externalFilters, $auth);
    }
    
    public function setCallbackFunction($functionName,$parentModel,$parentName=null){
        $this->callbackFunction = array();
        $this->callbackFunction['functionName'] = $functionName;
        $this->callbackFunction['parentModel'] = $parentModel;
        $this->callbackFunction['parentName'] = (isSet($parentName) ? $parentName : $parentModel);
    }
    
    private function callbackFunction($data){
        if(is_array($this->callbackFunction) && !empty($this->callbackFunction['functionName'])){
            $parent = itaFrontController::getInstance($this->callbackFunction['parentModel'], $this->callbackFunction['parentName']);
            $function = $this->callbackFunction['functionName'];
            $parent->$function($data);
        }
    }
    
    public function setReturnData($returnData=array()){
        $this->returnData = $returnData;
    }
    
    private function initLxORG(){
        if($this->options['compact'] == false){
            $codiciL1 = array_keys($this->LxORGData);
            $search = array_search(0, $codiciL1);
            if($search !== false){
                unset($codiciL1[$search]);
            }
            array_unshift($codiciL1, '');

            Out::html($this->prefix . 'L1ORG', '');
            foreach($codiciL1 as $codice){
                if($this->options['compact']){
                    $desc = ($codice == '' ? '--' : $codice . ' - ' . $this->LxORGData[$codice][0]);
                }
                else{
                    $desc = ($codice == '' ? '--- TUTTI ---' : $codice . ' - ' . $this->LxORGData[$codice][0]);
                }

                Out::select($this->prefix . 'L1ORG', 1, $codice, false, $desc);
            }

            if($this->options['compact']){
                $desc = '--';
            }
            else{
                $desc = '--- TUTTI ---';
            }

            Out::html($this->prefix   . 'L2ORG', '');
            Out::select($this->prefix . 'L2ORG', 1, '', false, $desc);
            Out::html($this->prefix   . 'L3ORG', '');
            Out::select($this->prefix . 'L3ORG', 1, '', false, $desc);
            Out::html($this->prefix   . 'L4ORG', '');
            Out::select($this->prefix . 'L4ORG', 1, '', false, $desc);

            Out::disableField($this->prefix . 'L2ORG');
            Out::disableField($this->prefix . 'L3ORG');
            Out::disableField($this->prefix . 'L4ORG');

            Out::valore($this->prefix . 'L2ORG','');
            Out::valore($this->prefix . 'L3ORG','');
            Out::valore($this->prefix . 'L4ORG','');
        }
    }
    
    public function setLxORG($l1=null, $l2=null, $l3=null, $l4=null, $checkAuth=false, $outMsgError=false){
        if($this->options['compact'] == true){
            $this->setLxORGCompact($l1, $l2, $l3, $l4, $checkAuth, $outMsgError);
        }
        else{
            $this->setLxORGExtended($l1, $l2, $l3, $l4);
        }
    }
    
    private function setLxORGCompact($l1=null, $l2=null, $l3=null, $l4=null, $checkAuth=false, $outMsgError=false){
        $l1 = str_pad(trim($l1), 2, '0', STR_PAD_LEFT);
        $l2 = str_pad(trim($l2), 2, '0', STR_PAD_LEFT);
        $l3 = str_pad(trim($l3), 2, '0', STR_PAD_LEFT);
        $l4 = str_pad(trim($l4), 2, '0', STR_PAD_LEFT);
        
        $filtri = array(
            'PROGENTE'=>$this->progEnte,
            'L1ORG'=>$l1,
            'L2ORG'=>$l2,
            'L3ORG'=>$l3,
            'L4ORG'=>$l4,
            'ATTIVO'=>true
        );
        $borOrgan = $toParent = $this->dbLib->leggiBorOrgan($filtri,false);
        
        if($checkAuth){
            $libAuth = new cwfAuthHelper();
            if($libAuth->checkMixedAuth($borOrgan, null, $this->options['authSource']) === false){
                $toParent = false;
                
                if($outMsgError){
                    $borOrgan = false;
                    Out::msgInfo('Errore', 'Non si è autorizzati ad usare la struttura organizzativa selezionata');
                }
                else{
                    $borOrgan = array(
                        'L1ORG'=>$l1,
                        'L2ORG'=>$l2,
                        'L3ORG'=>$l3,
                        'L4ORG'=>$l4,
                        'DESPORG'=>'Non si è autorizzati ad usare la struttura organizzativa selezionata'
                    );
                }
            }
        }
        
        Out::valore($this->prefix . 'L1ORG', $borOrgan['L1ORG']);
        Out::valore($this->prefix . 'L2ORG', $borOrgan['L2ORG']);
        Out::valore($this->prefix . 'L3ORG', $borOrgan['L3ORG']);
        Out::valore($this->prefix . 'L4ORG', $borOrgan['L4ORG']);
        Out::valore($this->prefix . 'DESPORG', $borOrgan['DESPORG']);
        
        $this->decodeToParentData(null, null, null, null, $toParent);
    }
    
    private function setLxORGExtended($l1=null, $l2=null, $l3=null, $l4=null){
        $this->LxORGData = cwbLibBor::getLxORGData(false, cwbParGen::getProgEnte(), $this->externalFilters, $this->options['useAuth']);
        
        if(trim($l4) == '00'){
            $l4=null;
        }
        if(trim($l3) == '00'){
            $l3=null;
        }
        if(trim($l2) == '00'){
            $l2=null;
        }
        if(trim($l1) == '00'){
            $l1=null;
        }
        
        if(!isSet($l1) || trim($l1) == ''){
            $l1=$l2=$l3=$l4=null;
        }
        elseif(!isSet($l2) || trim($l2) == ''){
            $l2=$l3=$l4=null;
        }
        elseif(!isSet($l3) || trim($l3) == ''){
            $l3=$l4=null;
        }
        elseif(!isSet($l4) || trim($l4) == ''){
            $l4=null;
        }
        
        if(isSet($l4)){
            Out::valore($this->nameForm . '_selector_c_DESPORG', $this->LxORGData[$l1][$l2][$l3][$l4][0]);
            Out::attributo($this->nameForm . '_selector_c_DESPORG', 'title', 0, $this->LxORGData[$l1][$l2][$l3][$l4][0]);
        }
        elseif(isSet($l3)){
            Out::valore($this->nameForm . '_selector_c_DESPORG', $this->LxORGData[$l1][$l2][$l3][0]);
            Out::attributo($this->nameForm . '_selector_c_DESPORG', 'title', 0, $this->LxORGData[$l1][$l2][$l3][0]);
        }
        elseif(isSet($l2)){
            Out::valore($this->nameForm . '_selector_c_DESPORG', $this->LxORGData[$l1][$l2][0]);
            Out::attributo($this->nameForm . '_selector_c_DESPORG', 'title', 0, $this->LxORGData[$l1][$l2][0]);
        }
        elseif(isSet($l1)){
            Out::valore($this->nameForm . '_selector_c_DESPORG', $this->LxORGData[$l1][0]);
            Out::attributo($this->nameForm . '_selector_c_DESPORG', 'title', 0, $this->LxORGData[$l1][0]);
        }
        else{
            Out::valore($this->nameForm . '_selector_c_DESPORG', '');
            Out::attributo($this->nameForm . '_selector_c_DESPORG', 'title', 0, '');
        }
            
        
        
        if(!isSet($l1) || $l1 === ''){ //L1 non è impostato
            Out::valore($this->prefix . 'L1ORG', '');
            Out::disableField($this->prefix . 'L2ORG');
            Out::disableField($this->prefix . 'L3ORG');
            Out::disableField($this->prefix . 'L4ORG');
        }
        else{ //L1 è impostato
            Out::valore($this->prefix . 'L1ORG',$l1);
            Out::enableField($this->prefix . 'L2ORG');
            
            $codiciL2 = array_keys($this->LxORGData[$l1]);
            $search = array_search(0, $codiciL2);
            if($search !== false){
                unset($codiciL2[$search]);
            }
            array_unshift($codiciL2, '');
            
            Out::html($this->prefix . 'L2ORG', '');
            foreach($codiciL2 as $codice){
                if($this->options['compact']){
                    $desc = ($codice == '' ? '--' : $codice . ' - ' . $this->LxORGData[$l1][$codice][0]);
                }
                else{
                    $desc = ($codice == '' ? '--- TUTTI ---' : $codice . ' - ' . $this->LxORGData[$l1][$codice][0]);
                }
                
                Out::select($this->prefix . 'L2ORG', 1, $codice, $codice == $l2, $desc);
            }
        }
        
        if(!isSet($l2) || $l2 === ''){ //L2 non è impostato
            Out::valore($this->prefix . 'L2ORG', '');
            Out::disableField($this->prefix . 'L3ORG');
            Out::disableField($this->prefix . 'L4ORG');
        }
        else{ //L2 è impostato
            Out::enableField($this->prefix . 'L3ORG');
            
            $codiciL3 = array_keys($this->LxORGData[$l1][$l2]);
            $search = array_search(0, $codiciL3);
            if($search !== false){
                unset($codiciL3[$search]);
            }
            array_unshift($codiciL3, '');
            
            Out::html($this->prefix . 'L3ORG', '');
            foreach($codiciL3 as $codice){
                if($this->options['compact']){
                    $desc = ($codice == '' ? '--' : $codice . ' - ' . $this->LxORGData[$l1][$l2][$codice][0]);
                }
                else{
                    $desc = ($codice == '' ? '--- TUTTI ---' : $codice . ' - ' . $this->LxORGData[$l1][$l2][$codice][0]);
                }
                
                Out::select($this->prefix . 'L3ORG', 1, $codice, $codice == $l3, $desc);
            }
        }
        
        if(!isSet($l3) || $l3 === ''){ //L3 non è impostato
            Out::valore($this->prefix . 'L3ORG', '');
            Out::disableField($this->prefix . 'L4ORG');
        }
        else{ //L3 è impostato
            Out::enableField($this->prefix . 'L4ORG');
            
            $codiciL4 = array_keys($this->LxORGData[$l1][$l2][$l3]);
            $search = array_search(0, $codiciL4);
            if($search !== false){
                unset($codiciL4[$search]);
            }
            array_unshift($codiciL4, '');
            
            Out::html($this->prefix . 'L4ORG', '');
            foreach($codiciL4 as $codice){
                if($this->options['compact']){
                    $desc = ($codice == '' ? '--' : $codice . ' - ' . $this->LxORGData[$l1][$l2][$l3][$codice][0]);
                }
                else{
                    $desc = ($codice == '' ? '--- TUTTI ---' : $codice . ' - ' . $this->LxORGData[$l1][$l2][$l3][$codice][0]);
                }
                Out::select($this->prefix . 'L4ORG', 1, $codice, $codice == $l4, $desc);
            }
        }
        
        if(!isSet($l4) || $l4 === ''){ //L4 non è impostato
            Out::valore($this->prefix . 'L4ORG', '');
        }
        
        $this->decodeToParentData($l1, $l2, $l3, $l4);
    }
    
    public function setIdorgan($idorgan=null){
        if(empty($idorgan)){
            $this->setLxORG();
        }
        else{
            $filtri = array(
                'IDORGAN'=>$idorgan
            );
            $data = $this->dbLib->leggiBorOrgan($filtri, false);
            $this->setLxORG($data['L1ORG'], $data['L2ORG'], $data['L3ORG'], $data['L4ORG']);
        }
    }
    
    public function disableFields($l1org=true, $l2org=true, $l3org=true, $l4org=true){
        if($l1org) Out::disableField($this->prefix .'L1ORG');
        if($l2org) Out::disableField($this->prefix .'L2ORG');
        if($l3org) Out::disableField($this->prefix .'L3ORG');
        if($l4org) Out::disableField($this->prefix .'L4ORG');
        if($l1org && $l2org && $l3org && $l4org) Out::hide($this->prefix .'searchBtn');
        
        $this->hideClearButton();
    }
    
    public function enableFields($l1org=true, $l2org=true, $l3org=true, $l4org=true){
        if($l1org) Out::enableField($this->prefix .'L1ORG');
        if($l2org) Out::enableField($this->prefix .'L2ORG');
        if($l3org) Out::enableField($this->prefix .'L3ORG');
        if($l4org) Out::enableField($this->prefix .'L4ORG');
        if($l1org || $l2org || $l3org || $l4org) Out::show($this->prefix .'searchBtn');
        
        if($this->options['showClearButton']){
            $this->showClearButton();
        }
    }
    
    public function hideClearButton(){
        Out::hide($this->nameForm . '_clearButton');
    }
    
    public function showClearButton(){
        Out::show($this->nameForm . '_clearButton');
    }
    
    private function setParentData($data){
        foreach($this->returnData as $field=>$formElement){
            if(isSet($data[$field])){
                Out::valore($formElement, $data[$field]);
            }
            else{
                Out::valore($formElement, null);
            }
        }
    }
    
    private function decodeToParentData($l1=null,$l2=null,$l3=null,$l4=null,$borOrgan=null){
        if(!empty($borOrgan)){
            $result = $borOrgan;
        }
        elseif((!empty($this->returnData) || !empty($this->callbackFunction)) && !empty($l1)){
            if(!isSet($l2) || trim($l2) == ''){
                $l2=$l3=$l4='00';
            }
            elseif(!isSet($l3) || trim($l3) == ''){
                $l3=$l4='00';
            }
            elseif(!isSet($l4) || trim($l4) == ''){
                $l4='00';
            }

            $filtri = array(
                'PROGENTE'=>$this->progEnte,
                'L1ORG'=>$l1,
                'L2ORG'=>$l2,
                'L3ORG'=>$l3,
                'L4ORG'=>$l4,
                'ATTIVO'=>true
            );
            $result = $this->dbLib->leggiBorOrgan($filtri,false);
        }
        else{
            $result = null;
        }
        
        if(!empty($this->returnData)){
            $this->setParentData($result);
        }
        if(!empty($this->callbackFunction)){
            $this->callbackFunction($result);
        }
    }
}