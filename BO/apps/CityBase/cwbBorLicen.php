<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

function cwbBorLicen() {
    $cwbBorLicen = new cwbBorLicen();
    $cwbBorLicen->parseEvent();
    return;
}

class cwbBorLicen extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBorLicen';
        $this->libDB = new cwbLibDB_BOR(); 
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }
      
    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODAREAMA_formatted'] != '') {
            $this->gridFilters['CODAREAMA'] = $this->formData['CODAREAMA_formatted'];
        }
        if ($_POST['CODLICEN'] != '') {
            $this->gridFilters['CODLICEN'] = $this->formData['CODLICEN'];
        }
        if ($_POST['PROGLICEN'] != '') {
            $this->gridFilters['PROGLICEN'] = $this->formData['PROGLICEN'];
        }
        if ($_POST['DESLICEN'] != '') {
            $this->gridFilters['DESLICEN'] = $this->formData['DESLICEN'];
        }
        if ($_POST['DATARILA'] != '') {
            $this->gridFilters['DATARILA'] = $this->formData['DATARILA'];
        }
        if ($_POST['CODMODULO'] != '') {
            $this->gridFilters['CODMODULO'] = $this->formData['CODMODULO'];
        }
        if ($_POST['SOFTHOUSE'] != '') {
            $this->gridFilters['SOFTHOUSE'] = $this->formData['SOFTHOUSE'];
        }
    }
    
    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onChange':    
                switch ($_POST['id']) {
                    case $this->nameForm . '_CODAREAMA':                        
                        $this->initComboRicercaModuli($_POST[$this->nameForm . '_CODAREAMA']);
                        break;
                }
                break;
        }
    }
    
    protected function postNuovo() {        
        Out::attributo($this->nameForm . '_BOR_LICEN[CODAREAMA]', 'readonly', '1');
        Out::setFocus("", $this->nameForm . '_BOR_LICEN[CODAREAMA]');
    }
    
    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BOR_LICEN[CODAREAMA]');
    }
    
    protected function elaboraRecords($Result_tab) {        
        foreach ($Result_tab as $key => $Result_rec) {                        
            $Result_tab[$key]['CODAREAMA_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CODAREAMA']);
        }
        return $Result_tab;
    }
    
    protected function postAltraRicerca() {
        Out::setFocus('', $this->nameForm . '_CODLICEN');  
    }

    protected function postApriForm() {
        $this->initComboRicerca();
        Out::setFocus('', $this->nameForm . '_CODLICEN');  
    }
    
    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        $this->decodArea($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODAREAMA'], ($this->nameForm . '_BOR_LICEN[CODAREAMA]'), ($this->nameForm . '_DESAREA_decod'));
        $this->decodModulo($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODMODULO'], ($this->nameForm . '_BOR_LICEN[CODMODULO]'), ($this->nameForm . '_DESMODULO_decod'));

        Out::setFocus('', $this->nameForm . '_BOR_LICEN[DESLICEN]');  
        // toglie gli spazi del char
        Out::valore($this->nameForm . '_BOR_LICEN[DESLICEN]', trim($this->CURRENT_RECORD['DESLICEN']));        
    }       

    protected function pulisciCampi() {
        Out::valore($this->nameForm . '_DESAREA_decod', '');
        Out::valore($this->nameForm . '_DESMODULO_decod', '');
    }
    
    public function postSqlElenca($filtri,&$sqlParams=array()) {        
        $filtri['CODAREAMA'] = trim($this->formData[$this->nameForm . '_CODAREAMA']);
        $filtri['CODMODULO'] = trim($this->formData[$this->nameForm . '_CODMODULO']);
        $filtri['DESLICEN'] = trim($this->formData[$this->nameForm . '_DESLICEN']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBorLicen($filtri, true, $sqlParams);        
    }

    public function sqlDettaglio($index,&$sqlParams) {
        list($codareama, $codlicen, $proglicen) = explode('|', $index);        
        $this->SQL = $this->libDB->getSqlLeggiBorLicenChiave($codareama, $codlicen, $proglicen,$sqlParams);        
    }
        
    private function decodArea($cod, $codField, $desField){
        $row = $this->libDB->leggiBorMasterChiave($cod);
        if($row){
            Out::valore($codField, $row['CODAREAMA']);
            Out::valore($desField, $row['DESAREA']);
        }else{
            Out::valore($codField, '');
            Out::valore($desField, '');
        }       
    }
    
    private function decodModulo($cod, $codField, $desField){
        $row = $this->libDB->leggiBorModuliChiave($cod);
        if($row){
            Out::valore($codField, $row['CODMODULO']);
            Out::valore($desField, $row['DESMODULO']);
        }else{
            Out::valore($codField, '');
            Out::valore($desField, '');
        }       
    }
    
    private function initComboRicerca() {        
        $this->initComboRicercaAree();
        $this->initComboRicercaModuli('');
    }
    
    private function initComboRicercaAree() {
        
        // Azzera combo
        Out::html($this->nameForm . '_CODAREAMA', '');
        
        // Carica lista aree
        $aree = $this->libDB->leggiBorMaster(array());
        
        // Popola combo in funzione dei dati caricati da db
        Out::select($this->nameForm . '_CODAREAMA', 1, '', 1, "--- TUTTE ---");                
        foreach ($aree as $area) {
            Out::select($this->nameForm . '_CODAREAMA', 1, $area['CODAREAMA'], 0, trim($area['CODAREAMA'] . ' - ' . $area['DESAREA']));        
        }                
    }
    
    private function initComboRicercaModuli($area) {
                
        // Azzera combo
        Out::html($this->nameForm . '_CODMODULO', '');
        
        // Aggiungi voce 'TUTTI'
        Out::select($this->nameForm . '_CODMODULO', 1, '', 1, "--- TUTTI ---");
        
        // Se area corrente non valorizzata, esce
        if ($area == '') {
            return;
        }
        
        // Carica lista moduli
        $filtri = array();
        $filtri['CODAREAMA'] = $area;
        $moduli = $this->libDB->leggiBorModuli($filtri);
        
        // Popola combo in funzione dei dati caricati da db        
        foreach ($moduli as $modulo) {
            Out::select($this->nameForm . '_CODMODULO', 1, $modulo['CODMODULO'], 0, trim($modulo['CODMODULO'] . ' - ' . $modulo['DESMODULO']));            
        }                
    }
       
}
