<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbComponentBtaSoggfe() {
    $cwbComponentBtaSoggfe = new cwbComponentBtaSoggfe();
    $cwbComponentBtaSoggfe->parseEvent();
    return;
}

class cwbComponentBtaSoggfe extends cwbBpaGenTab {
    private $fatherModelName;
    private $fatherName;
    private $operations;
    private $isComponent;
    private $progsogg;
    private $dataArray;
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbComponentBtaSoggfe';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    function initVars() {
        $this->GRID_NAME = 'gridBtaSoggfe';
        $this->AUTOR_MODULO = 'FTA';
        $this->AUTOR_NUMERO = 11;
        $this->libDB = new cwbLibDB_BTA();
        
        $this->TABLE_NAME = 'BTA_SOGGFE';
        $this->noCrud = true;
        
        $this->actionAfterNew = self::GOTO_LIST;
        $this->actionAfterModify = self::GOTO_LIST;
        $this->actionAfterDelete = self::GOTO_LIST;

        $this->searchOpenElenco = true;
        $this->errorOnEmpty = false;
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
        $this->errorOnEmpty = false;
        
        $this->operations = cwbParGen::getFormSessionVar($this->nameForm, 'operations');
        if($this->operations == ''){
            $this->operations = array();
        }
        $this->fatherModelName = cwbParGen::getFormSessionVar($this->nameForm, 'fatherModelName');
        $this->fatherName = cwbParGen::getFormSessionVar($this->nameForm, 'fatherName');
        $this->isComponent = cwbParGen::getFormSessionVar($this->nameForm, 'isComponent');
        $this->progsogg = cwbParGen::getFormSessionVar($this->nameFormOrig, 'progsogg');
        $this->dataArray = cwbParGen::getFormSessionVar($this->nameForm, 'dataArray');
    }
    
    protected function postConstruct() {
        $this->PK = $this->getModelService()->newTableDef($this->TABLE_NAME, $this->MAIN_DB)->getPks(true);
        if (!$this->getTABLE_VIEW()) {
            $this->TABLE_VIEW = $this->TABLE_NAME;
        }
        
        $this->noCrud = false;
    }
    
    protected function preDestruct() {
        if(!$this->close){
            cwbParGen::setFormSessionVar($this->nameForm, 'operations', $this->operations);
            cwbParGen::setFormSessionVar($this->nameForm, 'fatherModelName', $this->fatherModelName);
            cwbParGen::setFormSessionVar($this->nameForm, 'fatherName', $this->fatherName);
            cwbParGen::setFormSessionVar($this->nameForm, 'isComponent', $this->isComponent);
            cwbParGen::setFormSessionVar($this->nameFormOrig, 'progsogg', $this->progsogg);
            cwbParGen::setFormSessionVar($this->nameForm, 'dataArray', $this->dataArray);
        }
    }
    
    protected function initAuthenticator() {
        parent::initAuthenticator();
        if($this->authenticator->getLevel() == 'G'){
            $this->authenticator->setLevel('C');
        }
    }
    
    protected function preParseEvent() {
        switch($_POST['event']){
            case 'onClick':
                switch($_POST['id']){
                    case $this->nameForm . '_Aggiungi':
                        $data = $_POST[$this->nameForm . '_BTA_SOGGFE'];
                        $check = $this->checkData($data, itaModelService::OPERATION_INSERT);
                        if(!empty($check['error'])){
                            Out::msgStop("Errore di Inserimento.", implode('<br>',$check['error']));
                            $this->setBreakEvent(true);
                        }
                        elseif(!empty($check['warning'])){
                            Out::msgQuestion("Attenzione",
                                             "Sono stati riscontrati i seguenti errori:<br>".implode('<br>',$check['warning'])."<br>Proseguire?",
                                             array(
                                                'Annulla' => array('id' => $this->nameForm . '_AnnullaAggiungi', 'model' => $this->nameForm),
                                                'Conferma' => array('id' => $this->nameForm . '_AggiungiForce', 'model' => $this->nameForm)
                                             ));
                            $this->setBreakEvent(true);
                        }
                        break;
                    case $this->nameForm . '_AggiungiForce':
                        $this->aggiungi();
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $data = $_POST[$this->nameForm . '_BTA_SOGGFE'];
                        $check = $this->checkData($data, itaModelService::OPERATION_UPDATE);
                        if(!empty($check['error'])){
                            Out::msgStop("Errore aggiornamento record", implode('<br>',$check['error']));
                            $this->setBreakEvent(true);
                        }
                        elseif(!empty($check['warning'])){
                            Out::msgQuestion("Attenzione",
                                             "Sono stati riscontrati i seguenti errori:<br>".implode('<br>',$check['warning'])."<br>Proseguire?",
                                             array(
                                                'Annulla' => array('id' => $this->nameForm . '_AnnullaAggiorna', 'model' => $this->nameForm),
                                                'Conferma' => array('id' => $this->nameForm . '_AggiornaForce', 'model' => $this->nameForm)
                                             ));
                            $this->setBreakEvent(true);
                        }
                        break;
                    case $this->nameForm . '_AggiornaForce':
                        $this->aggiorna();
                        break;
                }
                break;
        }
        
        if($this->isComponent){
            switch($_POST['event']){
                case 'onClick':
                    switch($_POST['id']){
                        case $this->nameForm . '_ConfermaCancella':
                            $this->deleteData($_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow']);
                            $this->elenca(false);
                            $this->setBreakEvent(true);
                            break;
                    }
                    break;
                case 'viewRowInline':
                    $viewMode = true;
                case 'editRowInline':
                case 'dbClickRow':
                case 'editGridRow':
                    if(!isSet($viewMode)){
                        $viewMode = $this->viewMode;
                    }
                    
                    $model = cwbLib::apriFinestraDettaglioRecord('cwbComponentBtaSoggfe', $this->nameForm, 'returnEdit', $this->nameForm.'_returnEdit_'.$_POST['rowid'], $_POST['rowid'], true, $this->nameFormOrig, '', array());
                    $model->setDataArray($this->dataArray);
                    if(isSet($viewMode) && $viewMode === true){
                        $model->setViewMode($viewMode);
                    }
                    $model->parseEvent();
                    
                    $this->setBreakEvent(true);
                    break;
                case 'addGridRow':
                    $model = cwbLib::apriFinestraDettaglioRecord('cwbComponentBtaSoggfe', $this->nameForm, 'returnAdd', $this->nameForm.'_returnAdd', 'new', true, $this->nameFormOrig, '', array());
                    $model->setDataArray($this->dataArray);
                    $model->parseEvent();
                    
                    $this->setBreakEvent(true);
                    break;
            }
        }
    }
    
    protected function customParseEvent() {
        switch($_POST['event']){
            case 'openform':
                $this->initCheckboxes();
                break;
            case 'onChange':
                switch($_POST['id']){
                    case $this->nameForm . '_BTA_SOGGFE[TIPO_FORMATO]':
                        $value = trim($_POST[$this->nameForm . '_BTA_SOGGFE']['TIPO_FORMATO']);
                        $this->switchFormato($value);
                        break;
                }
                break;
            case 'returnEdit':
                if(preg_match('/'.$this->nameForm.'_returnEdit_([0-9]*)/', $_POST['id'], $matches)){
                    $key = $matches[1];
                    $data = $this->formData['returnData'];
                    $this->modifyData($key, $data);
                    $this->elenca(false);
                }
                break;
            case 'returnAdd':
                if(preg_match('/'.$this->nameForm.'_returnAdd/', $_POST['id'])){
                    $data = $this->formData['returnData'];
                    $this->insertData($data);
                    $this->elenca(false);
                }
                break;
        }
    }
    
    private function switchFormato($formato){
        switch($formato){
            case 0:
                Out::attributo($this->nameForm . '_BTA_SOGGFE[CODUFF_FE]', 'maxlength', '0', 6);
                Out::show($this->nameForm . '_BTA_SOGGFE[CODUFF_FE]_field');
                Out::hide($this->nameForm . '_BTA_SOGGFE[E_MAIL_PEC]_field');
                break;
            case 1:
                Out::attributo($this->nameForm . '_BTA_SOGGFE[CODUFF_FE]', 'maxlength', '0', 7);
                Out::show($this->nameForm . '_BTA_SOGGFE[CODUFF_FE]_field');
                Out::hide($this->nameForm . '_BTA_SOGGFE[E_MAIL_PEC]_field');
                break;
            case 2:
                Out::attributo($this->nameForm . '_BTA_SOGGFE[CODUFF_FE]', 'maxlength', '0', 7);
                Out::valore($this->nameForm . '_BTA_SOGGFE[CODUFF_FE]', '0000000');
                Out::hide($this->nameForm . '_BTA_SOGGFE[CODUFF_FE]_field');
                Out::show($this->nameForm . '_BTA_SOGGFE[E_MAIL_PEC]_field');
                break;
            case 3:
                Out::attributo($this->nameForm . '_BTA_SOGGFE[CODUFF_FE]', 'maxlength', '0', 7);
                Out::valore($this->nameForm . '_BTA_SOGGFE[CODUFF_FE]', '0000000');
                Out::hide($this->nameForm . '_BTA_SOGGFE[CODUFF_FE]_field');
                Out::valore($this->nameForm . '_BTA_SOGGFE[E_MAIL_PEC]', '');
                Out::hide($this->nameForm . '_BTA_SOGGFE[E_MAIL_PEC]_field');
                break;
        }
    }
    
    private function checkData($data, $operation){
        $return = array();
        
        $modelService = $this->getModelService();
        $validate = $modelService->validate($this->MAIN_DB, $this->TABLE_NAME, $data, $operation, null);
        
        $return = array(
            'error'=>array(),
            'warning'=>array()
        );
        
        foreach($validate as $row){
            if($row['level'] == itaModelValidator::LEVEL_WARNING){
                $return['warning'][] = $row['msg'];
            }
            elseif($row['msg'] != 'Soggetto non specificato'){
                $return['error'][] = $row['msg'];
            }
        }
        
        return $return;
    }

    protected function postNuovo() {
        Out::valore($this->nameForm . '_BTA_SOGGFE[PROGSOGG]', $this->progsogg);
        Out::valore($this->nameForm . '_DES_SOGG', '');
        $this->switchFormato(0);
        
//        Out::enableField($this->nameForm . '_BTA_SOGGFE[PROGSOGG]');
    }
    
    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_ID_SOGGFE');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_ID_SOGGFE');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_SOGGFE[DESCRIZIONE]');
    }
    
    protected function postDettaglio() {
        if($this->CURRENT_RECORD['PROGSOGG']) {
            $this->loadSoggetto($this->CURRENT_RECORD['PROGSOGG']);
        }
        $this->switchFormato($this->CURRENT_RECORD['TIPO_FORMATO']);
        
        if($this->checkUsage($id_soggfe)){
//            Out::disableField($this->nameForm . '_BTA_SOGGFE[PROGSOGG]');
        }
        else{
//            Out::enableField($this->nameForm . '_BTA_SOGGFE[PROGSOGG]');
        }
    }
    
    private function checkUsage($id_soggfe){
        $filtri = array(
            'FLAG_DIS'=>0,
            'ID_SOGGFE'=>$id_soggfe
        );
        $cnt = $this->libDB->leggiGeneric('FES_DOCTES', $filtri, false, 'count(*) CNT');
        return $cnt > 0;
    }

    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        if(!isSet($filtri['ID_SOGGFE'])){
            $filtri['ID_SOGGFE'] = trim($this->formData[$this->nameForm . '_ID_SOGGFE']);
        }
        
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaSoggfe($filtri, false, $sqlParams);
    }
    
    protected function setGridFilters() {
        $this->gridFilters = array();
        if(!empty($_POST['ID_SOGGFE'])) {
            $this->gridFilters['ID_SOGGFE'] = $this->formData['ID_SOGGFE'];
        }
        if(!empty($_POST['CODUFF_FE'])) {
            $this->gridFilters['CODUFF_FE'] = $this->formData['CODUFF_FE'];
        }
        if(!empty($_POST['DESCRIZIONE'])) {
            $this->gridFilters['DESCRIZIONE'] = $this->formData['DESCRIZIONE'];
        }
        if(!empty($_POST['FORMATO'])) {
            $this->gridFilters['TIPO_FORMATO'] = $this->formData['FORMATO']-1;
        }
        if(!empty($_POST['FLAG_01'])) {
            $this->gridFilters['FLAG_01'] = $this->formData['FLAG_01']-1;
        }
        if(!empty($_POST['FLAG_02'])) {
            $this->gridFilters['FLAG_02'] = $this->formData['FLAG_02']-1;
        }
        if(!empty($_POST['FLAG_03'])) {
            $this->gridFilters['FLAG_03'] = $this->formData['FLAG_03']-1;
        }
        if(!empty($_POST['FLAG_04'])) {
            $this->gridFilters['FLAG_04'] = $this->formData['FLAG_04']-1;
        }
        if(!empty($_POST['E_MAIL_PEC'])) {
            $this->gridFilters['E_MAIL_PEC'] = $this->formData['E_MAIL_PEC'];
        }
        if(!empty($_POST['CODUTE'])) {
            $this->gridFilters['CODUTE'] = $this->formData['CODUTE'];
        }
        if(!empty($_POST['FLAG_DIS'])) {
            $this->gridFilters['FLAG_DIS'] = $this->formData['FLAG_DIS']-1;
        }
    }

    protected function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaSoggfe(array('ID_SOGGFE'=>$index), false, $sqlParams);
    }
    
    private function initCheckboxes(){       
        Out::html($this->nameForm . '_BTA_SOGGFE[TIPO_FORMATO]','');
        Out::select($this->nameForm . '_BTA_SOGGFE[TIPO_FORMATO]', 1, 0, false, 'Verso Amministrazioni pubbliche (FPA12)');
        Out::select($this->nameForm . '_BTA_SOGGFE[TIPO_FORMATO]', 1, 1, false, 'Verso privati che hanno accreditato il canale (FPR12)');
        Out::select($this->nameForm . '_BTA_SOGGFE[TIPO_FORMATO]', 1, 2, false, 'Verso privati con pec (FPR12)');
        Out::select($this->nameForm . '_BTA_SOGGFE[TIPO_FORMATO]', 1, 3, false, 'Verso consumatori finali (Privi di canale e di pec)(FPR12)');
    }
    
    protected function elaboraRecords($Result_tab) {
        if(is_array($Result_tab)){
            foreach ($Result_tab as $key => $Result_rec) {

                $bta_sogg = $this->libDB->leggiBtaSoggChiave($Result_tab[$key]['PROGSOGG']);

                $soggetto = '';
                if (!empty($bta_sogg['CODFISCALE'])) {
                    $soggetto = $bta_sogg['CODFISCALE'];
                } else {
                    $soggetto = $bta_sogg['COGNOME'] . ' ' . $bta_sogg['NOME'];
                }

                $Result_tab[$key]['SOGGETTO'] = $bta_sogg['TIPOPERS'] == 'F' ? $bta_sogg['COGNOME'] . ' ' . $bta_sogg['NOME'] : $bta_sogg['RAGSOC'];

                switch($Result_tab[$key]['TIPO_FORMATO']) {
                    case 0:
                        $Result_tab[$key]['FORMATO'] = 'Verso Amministrazioni pubbliche (FPA12)';
                        break;
                    case 1:
                        $Result_tab[$key]['FORMATO'] = 'Verso privati che hanno accreditato il canale (FPR12)';
                        break;
                    case 2:
                        $Result_tab[$key]['FORMATO'] = 'Verso privati con pec (FPR12)';
                        break;
                    case 3:
                        $Result_tab[$key]['FORMATO'] = 'Verso consumatori finali (Privi di canale e di pec)(FPR12)';
                        break;
                }

            }
        }
        
        return $Result_tab;
    }
    
    private function loadSoggetto($progsogg) {
        $row = $this->libDB->leggiBtaSoggChiave($progsogg);
        if ($row) {
            Out::valore($this->nameForm . '_BTA_SOGGFE[PROGSOGG]', $row['PROGSOGG']);
            Out::valore($this->nameForm . '_DES_SOGG', $row['NOME_RIC']);
        } else {
            Out::valore($this->nameForm . '_BTA_SOGGFE[PROGSOGG]', '');
            Out::valore($this->nameForm . '_DES_SOGG', '');
        }
    }
    
    protected function initializeTable($sqlParams, $sortIndex, $sortOrder) {
        if($this->isComponent === true || (isSet($this->apriDettaglioIndex) && isSet($this->dataArray))){
            return $this->helper->initializeTableArray($this->dataArray);
        }
        else{
            return parent::initializeTable($sqlParams, $sortIndex, $sortOrder);
        }
    }
    
    protected function setSortParameter($ita_grid01, $order = cwbLib::ORDER_ASC) {
        if(!$this->isComponent){
            parent::setSortParameter($ita_grid01, $order);
        }
    }
    
    protected function loadCurrentRecord($index) {
        if($this->isComponent === true || (isSet($this->apriDettaglioIndex) && is_array($this->dataArray))){
            $this->CURRENT_RECORD = $this->dataArray[$index];
        }
        else{
            parent::loadCurrentRecord($index);
        }
    }
    
    protected function customElaboraAutor() {
        if($this->viewMode === true){
            Out::gridHideCol($this->nameForm . '_' . $this->GRID_NAME, 'EDITROW');
            Out::gridHideCol($this->nameForm . '_' . $this->GRID_NAME, 'DELETEROW');
            Out::hide($this->nameForm . "_" . $this->GRID_NAME . "_addGridRow");
            Out::hide($this->nameForm . "_" . $this->GRID_NAME . "_editGridRow");
            Out::hide($this->nameForm . "_" . $this->GRID_NAME . "_delGridRow");
            Out::hide($this->nameForm . '_Nuovo');
            Out::hide($this->nameForm . '_Cancella');
            Out::hide($this->nameForm . '_Aggiungi');
            Out::hide($this->nameForm . '_Aggiorna');
        }
    }
    
    protected function initModelService() {
        $this->setModelService(cwbModelServiceFactory::newModelService('cwbBtaSoggfe'));
    }
    
    /*
     * INIZIO GESTIONE COMPONENTE INTEGRATO
     */
    public function initComponent($fatherModelName,$fatherName){
        $this->fatherModelName = $fatherModelName;
        $this->fatherName = $fatherName;
        $this->isComponent = true;
        Out::removeElement($this->nameForm . '_buttonBar');
        $this->initCheckboxes();
    }
    
    private function getForeignKey(){
        switch($this->fatherModelName){
            case 'cwbBtaSogg': return array('PROGSOGG' => 'PROGSOGG');
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
            $operation['keys']['ID_SOGGFE'] = $this->dataArray[$key]['ID_SOGGFE'];
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
        $this->elenca(false);
    }
    
    public function openDettaglio($data,$vis){
        $this->resetOperations();
        $this->viewMode = $vis;
        $this->progsogg = $data['PROGSOGG'];
        
        $this->dataArray = array();
        $filtri = array(
            'PROGSOGG'=>$data['PROGSOGG']
        );
        $this->compilaFiltri($filtri);
        $this->dataArray = $this->libDB->leggiBtaSoggfe($filtri);
        foreach(array_keys($this->dataArray) as $key){
            $this->dataArray[$key]['CUSTOMKEY'] = $key;
        }
        
        $this->elenca(false);
    }
    
    public function resetData(){
        $this->resetOperations();
        foreach(array_keys($_POST[$this->nameForm . '_BTA_SOGGFE']) as $field){
            Out::valore($this->nameForm . '_BTA_SOGGFE['.$field.']','');
            $_POST[$this->nameForm . '_BTA_SOGGFE'][$field] = '';
        }
        
        $this->dataArray = array();
    }
    
    public function unlockRecord(){
        $this->sbloccaChiave();
    }
    
    public function getDataFromDB(){
        $return = array();
        $return['alias'] = $this->nameForm;
        
        $return['data'] = $this->libDB->leggiBtaSoggfe(array('PROGSOGG'=>$this->progsogg));
        return $return;
    }
    
    public function setProgsogg($progsogg){
        $this->progsogg = $progsogg;
    }
    
    public function setDataArray($data){
        $this->dataArray = $data;
    }
    
    public function hideSidebar(){
//        Out::hide($this->nameForm . '_buttonBar');
        Out::hideLayoutPanel($this->nameForm . '_buttonBar');
    }
    
    public function showSidebar(){
//        Out::show($this->nameForm . '_buttonBar');
        Out::showLayoutPanel($this->nameForm . '_buttonBar');
    }
    
    /**
     * Lock tabella per chiave
     * @param any $index Chiave univoca per blocco record
     */
    protected function bloccaChiave($currentRecord) {
        if ($this->viewMode) {
            return;
        }

        try {
            if($this->isComponent){
                $this->LOCK = array('status'=>0);
            }
            else{
                $modelService = $this->getModelService();
                $pkString = $modelService->calcPkString($this->MAIN_DB, 'BTA_SOGG', $currentRecord);

                $this->LOCK = $modelService->lockRecord($this->MAIN_DB, 'BTA_SOGG', $pkString, "", 0);
            }
            cwbParGen::setFormSessionVar($this->nameFormOrig, 'lock' . $this->TABLE_NAME, $this->LOCK);
        } catch (ItaException $e) {
            Out::msgStop("Errore blocco record", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore blocco record", $e->getMessage(), '600', '600');
        }
    }

    /**
     * Sblocca record precedentemente bloccato
     */
    protected function sbloccaChiave() {
        if ($this->viewMode) {
            return;
        }

        try {
            if(!$this->isComponent){
                $this->LOCK = cwbParGen::getFormSessionVar($this->nameFormOrig, 'lock' . $this->TABLE_NAME);
                if ($this->LOCK) {
                    $this->getModelService()->unlockRecord($this->LOCK['lockID'], $this->MAIN_DB);
                }
            }
        } catch (ItaException $e) {
            Out::msgStop("Errore sblocco record", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore sblocco record", $e->getMessage(), '600', '600');
        }
    }
    /*
     * FINE GESTIONE COMPONENTE INTEGRATO
     */
}

?>