<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaCab() {
    $cwbBtaCab = new cwbBtaCab();
    $cwbBtaCab->parseEvent();
    return;
}

class cwbBtaCab extends cwbBpaGenTab {
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBtaCab';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    function initVars() {
        $this->GRID_NAME = 'gridBtaCab';
        $this->ALIAS = 'U';
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }
    
    protected function customParseEvent() {
        switch($_POST['event']){
            case 'onClick':
                switch($_POST['id']){
                    case $this->nameForm . '_BTA_CAB[COMUNEUBIC]_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaLocal', $this->nameForm, 'returnLocal', '', true, null, $this->nameFormOrig);
                        break;
                }
                break;
            case 'returnLocal':
                $this->setLocal($this->formData['returnData']);
                break;
        }
    }
    
    private function setLocal($data){
        Out::valore($this->nameForm . '_BTA_CAB[COMUNEUBIC]', $data['DESLOCAL']);
        Out::valore($this->nameForm . '_BTA_CAB[CAP]', $data['CAP']);
        Out::valore($this->nameForm . '_BTA_CAB[PROVINCIA]', $data['PROVINCIA']);
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_CAB[CAB]', 'readonly', '1');
        Out::setFocus("", $this->nameForm . '_BTA_CAB[CAB]');
        
        $externalParams = $this->getExternalParamsNormalyzed();
        if(isSet($externalParams['ABI']) && $externalParams['ABI']['PERMANENTE'] === true){
            Out::valore($this->nameForm . '_BTA_CAB[ABI]', $externalParams['ABI']['VALORE']);
        }
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_CAB[CAB]');
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_CAB[CAB]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_BTA_CAB[DESCAB]');
    }
    
    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CAB'] != '') {
            $this->gridFilters['CAB'] = $this->formData['CAB'];
        }
        if ($_POST['CAB_CIN'] != '') {
            $this->gridFilters['CAB_CIN'] = $this->formData['CAB_CIN'];
        }
        if ($_POST['DES_SPORT'] != '') {
            $this->gridFilters['DES_SPORT'] = $this->formData['DES_SPORT'];
        }
        if ($_POST['COMUNEUBIC'] != '') {
            $this->gridFilters['COMUNEUBIC'] = $this->formData['COMUNEUBIC'];
        }
        if ($_POST['LOCALSPORT'] != '') {
            $this->gridFilters['LOCALSPORT'] = $this->formData['LOCALSPORT'];
        }
        if ($_POST['INDIRSPORT'] != '') {
            $this->gridFilters['INDIRSPORT'] = $this->formData['INDIRSPORT'];
        }
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri = array();
        $filtri['CAB'] = trim($this->formData[$this->nameForm . '_CAB']);
        $filtri['DES_SPORT'] = trim($this->formData[$this->nameForm . '_DES_SPORT']);
        $filtri['FLAG_DIS'] = trim($this->formData[$this->nameForm . '_FLAG_DIS']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaCab($filtri, true, $sqlParams);
    }
    
    protected function postExternalFilter() {
        if(isSet($this->externalParams['ABI'])){
            if(!is_array($this->externalParams['ABI'])){
                $abi = $this->externalParams['ABI'];
            }
            else{
                $abi = $this->externalParams['ABI']['VALORE'];
            }
            
            $data = $this->libDB->leggiBtaAbi(array('ABI'=>$abi),false);
            
            Out::valore($this->nameForm . '_BTA_CAB[ABI]',$data['ABI']);
            Out::valore($this->nameForm . '_BTA_CAB[ABI_CIN]',$data['ABI_CIN']);
            Out::valore($this->nameForm . '_BTA_CAB[DESBANCA_2]',$data['DESBANCA']);
        }
        else{
            Out::hide($this->nameForm . '_divABI');
        }
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $index = explode('|',$index);
        $this->SQL = $this->libDB->getSqlLeggiBtaCabChiave($index[0], $index[1], $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CUSTOMKEY'] = $Result_rec['ABI'].'|'.$Result_rec['CAB'];
            $Result_tab[$key]['CAB'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CAB']);
        }
        return $Result_tab;
    }

}

?>