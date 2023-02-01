<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBgeFunzgis() {
    $cwbBgeFunzgis = new cwbBgeFunzgis();
    $cwbBgeFunzgis->parseEvent();
    return;
}

class cwbBgeFunzgis extends cwbBpaGenTab {
        
    protected function initVars() {
        $this->GRID_NAME = 'gridBgeFunzgis';
        $this->AUTOR_MODULO = 'BGE';
        $this->AUTOR_NUMERO = 1;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BGE();
        $this->elencaAutoAudit = true;
    } 
        
    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['IDFUNZ_formatted'] != '') {
            $this->gridFilters['IDFUNZ'] = $this->formData['IDFUNZ_formatted'];
        }
        if ($_POST['FUNZIONE'] != '') {
            $this->gridFilters['FUNZIONE'] = $this->formData['FUNZIONE'];
        }
        if ($_POST['DESCRIZ'] != '') {
            $this->gridFilters['DESCRIZ'] = $this->formData['DESCRIZ'];
        }
    }

    protected function postApriForm() {                           
        Out::setFocus("", $this->nameForm . '_IDFUNZ');
    }
    
    protected function postAltraRicerca() {                           
        Out::setFocus("", $this->nameForm . '_IDFUNZ');
    }

    protected function postNuovo() {
        Out::hide($this->nameForm . '_BGE_FUNZGIS[IDFUNZ]_field');
        $progr = cwbLibCalcoli::trovaProgressivo("IDFUNZ", "BGE_FUNZGIS"); 
        Out::valore($this->nameForm . '_BGE_FUNZGIS[IDFUNZ]', $progr);
        Out::attributo($this->nameForm . '_BGE_FUNZGIS[IDFUNZ]', 'readonly', '1');        
        Out::setFocus("", $this->nameForm . '_BGE_FUNZGIS[FUNZIONE]');
    }
        
    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BGE_FUNZGIS[CODTESTO]');
    }
    
    protected function postDettaglio($index) {
        Out::hide($this->nameForm . '_BGE_FUNZGIS[IDFUNZ]_field');
        Out::setFocus('', $this->nameForm . '_BGE_FUNZGIS[FUNZIONE]');        
    }        
      
    public function postSqlElenca($filtri, &$sqlParams) {        
        $filtri['IDFUNZ'] = trim($this->formData[$this->nameForm . '_IDFUNZ']);        
        $filtri['FUNZIONE'] = trim($this->formData[$this->nameForm . '_FUNZIONE']);                        
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBgeFunzgis($filtri, true, $sqlParams);        
    }

    public function sqlDettaglio($index,&$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBgeFunzgisChiave($index,$sqlParams);        
    }
    
    protected function elaboraRecords($Result_tab) {        
        foreach ($Result_tab as $key => $Result_rec) {                        
            $Result_tab[$key]['IDFUNZ_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['IDFUNZ']);
        }
        return $Result_tab;
    }
    
}
