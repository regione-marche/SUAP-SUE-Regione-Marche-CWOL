<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

function cwbBorDistr() {
    $cwbBorDistr = new cwbBorDistr();
    $cwbBorDistr->parseEvent();
    return;
}

class cwbBorDistr extends cwbBpaGenTab {
        
    protected function initVars() {
        $this->GRID_NAME = 'gridBorDistr';
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BOR(); 
    } 

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['PROGDITTAD_formatted'] != '') {
            $this->gridFilters['PROGDITTAD'] = $this->formData['PROGDITTAD_formatted'];
        }
        if ($_POST['DESENTE'] != '') {
            $this->gridFilters['DESENTE'] = $this->formData['DESENTE'];
        }
        if ($_POST['DESLOCAL'] != '') {
            $this->gridFilters['DESLOCAL'] = $this->formData['DESLOCAL'];
        }
        if ($_POST['DATAINIZ'] != '') {
            $this->gridFilters['DATAINIZ'] = $this->formData['DATAINIZ'];
        }
        if ($_POST['DATAFINE'] != '') {
            $this->gridFilters['DATAFINE'] = $this->formData['DATAFINE'];
        }
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BOR_DISTR[PROGDITTAD]', 'readonly', '1');        
        Out::setFocus("", $this->nameForm . '_BOR_DISTR[PROGDITTAD]');
    }
    
    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BOR_DISTR[PROGDITTAD]');
    }
    
    protected function postAltraRicerca() {
        Out::setFocus('', $this->nameForm . '_DESENTE');  
    }

    protected function postApriForm() {
        Out::setFocus('', $this->nameForm . '_DESENTE');  
    }
    
    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BOR_DISTR[PROGDITTAD]', 'readonly', '0');        
        Out::attributo($this->nameForm . '_BOR_DISTR[ANNOTAZ]', 'readonly', '0');        
        Out::setFocus('', $this->nameForm . '_BOR_DISTR[DESENTE]');        
    }        
      
    public function postSqlElenca($filtri,&$sqlParams=array()) {        
        $filtri['PROGDITTAD'] = trim($this->formData[$this->nameForm . '_PROGDITTAD']);        
        $filtri['DESENTE'] = trim($this->formData[$this->nameForm . '_DESENTE']);                        
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBorDistr($filtri, true,$sqlParams);        
    }
    
    protected function elaboraRecords($Result_tab) {        
        foreach ($Result_tab as $key => $Result_rec) {                        
            $Result_tab[$key]['PROGDITTAD_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGDITTAD']);
        }
        return $Result_tab;
    }

    public function sqlDettaglio($index, &$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBorDistrChiave($index, $sqlParams);        
    }

}
?>