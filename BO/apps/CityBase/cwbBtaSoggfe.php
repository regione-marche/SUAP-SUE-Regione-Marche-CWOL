<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibDB_FES.class.php';

function cwbBtaSoggfe() {
    $cwbBtaSoggfe = new cwbBtaSoggfe();
    $cwbBtaSoggfe->parseEvent();
    return;
}

class cwbBtaSoggfe extends cwbBpaGenTab {
    private $isComponent;
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBtaSoggfe';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    function initVars() {
        $this->GRID_NAME = 'gridBtaSoggfe';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 19;
        $this->libDB = new cwbLibDB_BTA();
        $this->libDB_FES = new cwfLibDB_FES();

        $this->searchOpenElenco = true;
        $this->errorOnEmpty = false;
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
        
        $this->isComponent = cwbParGen::getFormSessionVar($this->nameForm, 'isComponent');
    }
    
    protected function preDestruct() {
        if(!$this->close){
            cwbParGen::setFormSessionVar($this->nameForm, 'isComponent', $this->isComponent);
        }
    }
    
    protected function initAuthenticator() {
        parent::initAuthenticator();
        if($this->authenticator->getLevel() == 'G'){
            $this->authenticator->setLevel('C');
        }
    }
    
    protected function preParseEvent() {
        if($this->isComponent){
            switch($_POST['event']){
                case 'viewRowInline':
                    $viewMode = true;
                case 'editRowInline':
                case 'dbClickRow':
                case 'editGridRow':
                    if(!isSet($viewMode)){
                        $viewMode = $this->viewMode;
                    }
                    
                    $model = cwbLib::apriFinestraDettaglioRecord('cwbBtaSoggfe', $this->nameFormOrig, 'returnEdit', '', $_POST['rowid'], false, $this->nameForm, '', array());
                    if(isSet($viewMode) && $viewMode === true){
                        $model->setViewMode($viewMode);
                    }
                    $model->parseEvent();
                    $model->lockSoggetto();
                    
                    $this->setBreakEvent(true);
                    break;
                case 'addGridRow':
                    $model = cwbLib::apriFinestraDettaglioRecord('cwbBtaSoggfe', $this->nameFormOrig, 'returnEdit', '', 'new', false, $this->nameForm, '', array());
                    $model->parseEvent();
                    $model->lockSoggetto($this->externalParams['PROGSOGG']['VALORE']);
                    
                    $this->setBreakEvent(true);
                    break;
            }
        }
    }
    
    protected function customParseEvent() {
        switch($_POST['event']){
            case 'openform':
                $this->initComboboxes();
                break;
            case 'onClick':
                switch($_POST['id']){
                    case $this->nameForm . '_BTA_SOGGFE[PROGSOGG]_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaSogg', $this->nameForm, 'returnBtaSogg', 'PROGSOGG', true, null, $this->nameFormOrig);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_SOGGFE[PROGSOGG]':
                        $value = trim($_POST[$this->nameForm . '_BTA_SOGGFE']['PROGSOGG']);
                        $this->loadSoggetto($value);
                        break;
                    case $this->nameForm . '_BTA_SOGGFE[TIPO_FORMATO]':
                        $value = trim($_POST[$this->nameForm . '_BTA_SOGGFE']['TIPO_FORMATO']);
                        $this->switchFormato($value);
                        break;
                }
                break;
            case 'returnBtaSogg':
                $this->loadSoggetto($this->formData['returnData']['PROGSOGG']);
                break;
        }
    }
    
    private function initComboboxes(){       
        Out::html($this->nameForm . '_BTA_SOGGFE[TIPO_FORMATO]','');
        Out::select($this->nameForm . '_BTA_SOGGFE[TIPO_FORMATO]', 1, 0, false, 'Verso Amministrazioni pubbliche (FPA12)');
        Out::select($this->nameForm . '_BTA_SOGGFE[TIPO_FORMATO]', 1, 1, false, 'Verso privati che hanno accreditato il canale (FPR12)');
        Out::select($this->nameForm . '_BTA_SOGGFE[TIPO_FORMATO]', 1, 2, false, 'Verso privati con pec (FPR12)');
        Out::select($this->nameForm . '_BTA_SOGGFE[TIPO_FORMATO]', 1, 3, false, 'Verso consumatori finali (Privi di canale e di pec)(FPR12)');
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

    protected function postNuovo() {
        Out::valore($this->nameForm . '_DES_SOGG', '');
        
        $externalParams = $this->getExternalParamsNormalyzed();
        if(isSet($externalParams['PROGSOGG']) && $externalParams['PROGSOGG']['VALORE'] > 0) {
            $this->loadSoggetto(trim($externalParams['PROGSOGG']['VALORE']));
        }
        
        $this->switchFormato(0);
        
        Out::enableField($this->nameForm . '_BTA_SOGGFE[PROGSOGG]');
    }
    
    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_ID_SOGGFE');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_ID_SOGGFE');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_SOGGFE[PROGSOGG]');
    }
    
    protected function postDettaglio() {
        if($this->CURRENT_RECORD['PROGSOGG']) {
            $this->loadSoggetto($this->CURRENT_RECORD['PROGSOGG']);
        }
        
        $this->disabilitaCampi();
        $this->switchFormato($this->CURRENT_RECORD['TIPO_FORMATO']);
        
        if($this->checkUsage($id_soggfe)){
            Out::disableField($this->nameForm . '_BTA_SOGGFE[PROGSOGG]');
        }
        else{
            Out::enableField($this->nameForm . '_BTA_SOGGFE[PROGSOGG]');
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
        $externalParams = $this->getExternalParamsNormalyzed();
        if(isSet($externalParams['PROGSOGG']) && $externalParams['PROGSOGG']['VALORE'] > 0){
            $filtri['PROGSOGG'] = trim($externalParams['PROGSOGG']['VALORE']);
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
            Out::valore($this->nameForm . '_DES_SOGG', $row['RAGSOC']);
        } else {
            Out::valore($this->nameForm . '_BTA_SOGGFE[PROGSOGG]', '');
            Out::valore($this->nameForm . '_DES_SOGG', '');
        }
    }
    
    public function initAsComponent(){
        Out::removeElement($this->nameForm . '_buttonBar');
        Out::gridForceResize($this->nameForm . '_' . $this->GRID_NAME);
        
        $this->isComponent = true;
        $this->setEvent('openform');
        $this->parseEvent();
    }
    
    public function setSogg($progsogg, $viewMode){
        $this->externalParams['PROGSOGG'] = array(
            'VALORE'=>$progsogg,
            'PERMANENTE'=>true
        );
        $this->viewMode = $viewMode;
        
        $this->elenca(false);
    }
    
    public function lockSoggetto($progsogg=null){
        Out::disableField($this->nameForm . '_BTA_SOGGFE[PROGSOGG]');
        if(!empty($progsogg)){
            $this->loadSoggetto($progsogg);
        }
    }
    
    private function disabilitaCampi() {
        if(!$this->viewMode) {
            $id_soggfe = $this->CURRENT_RECORD['ID_SOGGFE'];

            if($id_soggfe > 0) {
                $filtri = array();
                $filtri['ID_SOGGFE'] = $id_soggfe;
                $count = $this->libDB_FES->countFesDoctes($filtri);

                if($count > 0) {
                    Out::disableField($this->nameForm . '_BTA_SOGGFE[TIPO_FORMATO]');
                    Out::disableField($this->nameForm . '_BTA_SOGGFE[CODUFF_FE]');
                    Out::disableField($this->nameForm . '_BTA_SOGGFE[E_MAIL_PEC]');
                } else {
                    Out::enableField($this->nameForm . '_BTA_SOGGFE[TIPO_FORMATO]');
                    Out::enableField($this->nameForm . '_BTA_SOGGFE[CODUFF_FE]');
                    Out::enableField($this->nameForm . '_BTA_SOGGFE[E_MAIL_PEC]');
                }
            }
        }
    }
}

?>