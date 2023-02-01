<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaArrot() {
    $cwbBtaArrot = new cwbBtaArrot();
    $cwbBtaArrot->parseEvent();
    return;
}

class cwbBtaArrot extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaArrot';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 18;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }
    
    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onChange':
                switch ($_POST['id']) {                    
                    case $this->nameForm . '_BTA_ARROT[PK]':                        
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PK'], $this->nameForm . '_BTA_ARROT[PK]');    
                        break;
                    case $this->nameForm . '_PK':                        
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_PK'], $this->nameForm . '_PK');    
                        break;
                }
                break;
        }
    }
    
    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODARROT_formatted'] != '') {
            $this->gridFilters['CODARROT'] = $this->formData['CODARROT_formatted'];
        }
        if ($_POST['DES_GE60'] != '') {
            $this->gridFilters['DES_GE60'] = $this->formData['DES_GE60'];
        }
    }

    protected function postNuovo() {        
        Out::attributo($this->nameForm . '_BTA_ARROT[CODARROT]', 'readonly', '1');
        Out::setFocus("", $this->nameForm . '_BTA_ARROT[CODARROT]');
        Out::css($this->nameForm . '_BTA_ARROT[CODARROT]', 'background-color', '#FFFFFF');
    }
    
    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_ARROT[CODARROT]');
    }
   
    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_ARROT[CODARROT]', 'readonly', '0');        
        Out::setFocus('', $this->nameForm . '_BTA_ARROT[DES_GE60]');  
        // toglie gli spazi del char
        Out::css($this->nameForm . '_BTA_ARROT[CODARROT]', 'background-color', '#FFFFE0');
        Out::valore($this->nameForm . '_BTA_ARROT[DES_GE60]', trim($this->CURRENT_RECORD['DES_GE60']));        
    }       
    
    protected function postApriForm() {
        $this->initComboTipArro();
        $this->initComboValore();
        Out::setFocus("", $this->nameForm . '_DES_ASS');
    }
        
    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DES_ASS');
    }
    
    public function postSqlElenca($filtri, &$sqlParams = array()) {        
        $filtri['CODARROT'] = trim($this->formData[$this->nameForm . '_CODARROT']) ? trim($this->formData[$this->nameForm . '_CODARROT']):null;        
        $filtri['DES_GE60'] = trim($this->formData[$this->nameForm . '_DES_GE60']);
        $filtri['FLAG_DIS'] = trim($this->formData[$this->nameForm . '_FLAG_DIS']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaArrot($filtri, true, $sqlParams);        
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaArrotChiave($index, $sqlParams);        
    }

    protected function elaboraRecords($Result_tab) {        
        foreach ($Result_tab as $key => $Result_rec) {                        
            $Result_tab[$key]['CODARROT_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CODARROT']);
        }
        return $Result_tab;
    }

    private function initComboTipArro() {
        Out::select($this->nameForm . '_BTA_ARROT[TIPOARROT]', 1, " ", 1, "Nessuno");
        Out::select($this->nameForm . '_BTA_ARROT[TIPOARROT]', 1, "N", 0, "Normale");
        Out::select($this->nameForm . '_BTA_ARROT[TIPOARROT]', 1, "D", 0, "Per difetto");
        Out::select($this->nameForm . '_BTA_ARROT[TIPOARROT]', 1, "E", 0, "Per eccesso");
    }
    private function initComboValore() {
        Out::select($this->nameForm . '_BTA_ARROT[VALARROT]', 1, "0.01000", 1, "0,01");
        Out::select($this->nameForm . '_BTA_ARROT[VALARROT]', 1, "0.05000", 0, "0,05");
        Out::select($this->nameForm . '_BTA_ARROT[VALARROT]', 1, "0.10000", 0, "0,10");
        Out::select($this->nameForm . '_BTA_ARROT[VALARROT]', 1, "0.20000", 0, "0,20");
        Out::select($this->nameForm . '_BTA_ARROT[VALARROT]', 1, "0.50000", 0, "0,50");
        Out::select($this->nameForm . '_BTA_ARROT[VALARROT]', 1, "1.00000", 0, "1,00");
        Out::select($this->nameForm . '_BTA_ARROT[VALARROT]', 1, "0.00000", 0, "0,00000");
    }
}
