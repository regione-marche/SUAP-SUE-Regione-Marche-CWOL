<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

function cwbBorModorg() {
    $cwbBorModorg = new cwbBorModorg();
    $cwbBorModorg->parseEvent();
    return;
}

class cwbBorModorg extends cwbBpaGenTab {
        
    protected function initVars() {
        $this->GRID_NAME = 'gridBorModorg';
        $this->searchOpenElenco = true;
        $this->hasSequence;
        $this->libDB = new cwbLibDB_BOR(); 
        $this->AUTOR_MODULO = 'BOR';
        $this->AUTOR_NUMERO = 4;
        $this->progEnte = cwbParGen::getProgEnte();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    } 

    protected function setGridFilters() {
        $this->gridFilters = array();
        $this->gridFilters['PROGENTE'] = $this->progEnte;
        if(!empty($_POST['DESCRIZ'])) {
            $this->gridFilters['DESCRIZ'] = $this->formData['DESCRIZ'];
        }
        if(!empty($_POST['CODUTE'])) {
            $this->gridFilters['CODUTE'] = $this->formData['CODUTE'];
        }
        if(!empty($_POST['FLAG_DIS'])) {
            $this->gridFilters['FLAG_DIS'] = $this->formData['FLAG_DIS']-1;
        }
    }

    protected function postNuovo() {
        Out::valore($this->nameForm . '_BOR_MODORG[PROGENTE]', $this->progEnte);
        Out::hide($this->nameForm . '_BOR_MODORG[IDMODORG]_field');
        Out::setFocus("", $this->nameForm . '_BOR_MODORG[DESCRIZ]');
    }
    
    protected function postAggiungi() {
        Out::valore($this->nameForm . '_BOR_MODORG[PROGENTE]', $this->progEnte);
        Out::setFocus("", $this->nameForm . '_BOR_MODORG[IDMODORG]');
    }
    
    protected function postAltraRicerca() {
        Out::setFocus('', $this->nameForm . '_DESCRIZ');  
    }

    protected function postApriForm() {
        //$this->initComboEnti();
        Out::valore($this->nameForm . '_BOR_MODORG[PROGENTE]', $this->progEnte);
        Out::setFocus('', $this->nameForm . '_DESCRIZ');  
    }
    
    protected function postDettaglio($index) {
        Out::hide($this->nameForm . '_BOR_MODORG[IDMODORG]_field');
        Out::setFocus('', $this->nameForm . '_BOR_MODORG[DESCRIZ]');        
    }        
    
    public function sqlDettaglio($index, &$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBorModorgChiave($index, $sqlParams);        
    }
    
    protected function elaboraRecords($Result_tab) {        
        foreach ($Result_tab as $key => $Result_rec) {                        
            $ente = $this->libDB->leggiBorEntiChiave($Result_tab[$key]['PROGENTE']);
            $Result_tab[$key]['PROGENTE'] = cwbLibHtml::formatDataGridCod($ente['DESENTE']);
        }
        return $Result_tab;
    }

    public function postSqlElenca($filtri,&$sqlParams=array()) {     
        if(empty($filtri['DESCRIZ'])){
            $filtri['DESCRIZ'] = trim($this->formData[$this->nameForm . '_DESCRIZ']);
        }
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBorModorg($filtri, true,$sqlParams);        
    }
    
    protected function elenca($reload) {
        if(!isSet($_POST["sidx"]) || trim($_POST["sidx"]) == ''){
            $_POST["sidx"] = 'DATAINIZ';
            $_POST["sord"] = 'desc';
        }
        
        parent::elenca($reload);
    }
    
    protected function preAltraRicerca() {
        Out::gridCleanFilters($this->nameForm, $this->GRID_NAME);
//        Out::valore('gs_DESCRIZ', '');
//        Out::valore('gs_CODUTE', '');
//        Out::valore('gs_FLAG_DIS', '');
    }
}
