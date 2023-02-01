<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaAbi() {
    $cwbBtaAbi = new cwbBtaAbi();
    $cwbBtaAbi->parseEvent();
    return;
}

class cwbBtaAbi extends cwbBpaGenTab {
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBtaAbi';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }

    function initVars() {
        $this->GRID_NAME = 'gridBtaAbi';
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CAB':
                        $this->apriCab();
                        break;
                }
                break;
        }
    }

    private function apriCab() {
        $this->loadCurrentRecord($_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow']);
        
        $model = 'cwbBtaCab';
        $alias = 'cwbBtaCab_' . time() . '_' . rand();
        $externalParams = array();
        $externalParams['ABI'] = array();
        $externalParams['ABI']['VALORE'] = $this->CURRENT_RECORD['ABI'];
        $externalParams['ABI']['PERMANENTE'] = true;
        
        itaLib::openDialog($model, true, true, 'desktopBody', '', '', $alias);
        $objModel = itaFrontController::getInstance($model, $alias);
        $objModel->setExternalParams($externalParams);
        $objModel->setSearchOpenElenco(true);
        $objModel->setEvent('openform');
        $objModel->parseEvent();
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_ABI[ABI]', 'readonly', '1');
        Out::setFocus("", $this->nameForm . '_BTA_ABI[ABI]');
        Out::hide($this->nameForm . '_CAB');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_ABI[ABI]');
    }

    protected function postDettaglio($index) {
        Out::show($this->nameForm . '_CAB');
        Out::attributo($this->nameForm . '_BTA_ABI[ABI]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_BTA_ABI[DESABI]');
    }
    
    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['ABI_formatted'] != '') {
            $this->gridFilters['ABI'] = $this->formData['ABI_formatted'];
        }
        if ($_POST['ABI_CIN'] != '') {
            $this->gridFilters['ABI_CIN'] = $this->formData['ABI_CIN'];
        }
        if ($_POST['DESBANCA'] != '') {
            $this->gridFilters['DESBANCA'] = $this->formData['DESBANCA'];
        }
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri = array();
        $filtri['ABI'] = trim($this->formData[$this->nameForm . '_ABI']);
        $filtri['DESBANCA'] = trim($this->formData[$this->nameForm . '_DESBANCA']);
        $filtri['FLAG_DIS'] = trim($this->formData[$this->nameForm . '_FLAG_DIS']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaAbi($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaAbiChiave($index, $sqlParams);
    }

    protected function postElenca() {
        Out::show($this->nameForm . '_CAB');
    }

    protected function postApriForm() {
        Out::hide($this->nameForm . '_CAB');
    }

    protected function postAltraRicerca() {
        Out::hide($this->nameForm . '_CAB');
    }

    protected function postTornaElenco() {
        Out::show($this->nameForm . '_CAB');
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['ABI_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['ABI']);
        }
        return $Result_tab;
    }

}

?>