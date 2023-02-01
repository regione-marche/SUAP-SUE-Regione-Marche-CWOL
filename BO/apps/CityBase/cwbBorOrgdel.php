<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBtaNumeratori.class.php';

function cwbBorOrgdel() {
    $cwbBorOrgdel = new cwbBorOrgdel();
    $cwbBorOrgdel->parseEvent();
    return;
}

class cwbBorOrgdel extends cwbBpaGenTab {
    private $anno;
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBorOrgdel';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    protected function initVars() {
        $this->GRID_NAME = 'gridBorOrgdel';
        $this->AUTOR_MODULO = 'BOR';
        $this->AUTOR_NUMERO = 4;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BOR();
        
        $this->actionAfterNew = self::GOTO_LIST;
        $this->actionAfterModify = self::GOTO_LIST;
        $this->actionAfterDelete = self::GOTO_LIST;
        
        $this->openDetailFlag = true;
        
        $this->anno = cwbParGen::getAnnoContabile();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }
    

    protected function preParseEvent() {
        switch($_POST['event']){
            case 'openform':
                // Gestione per Regione Marche (vedi cwfBilancioHelper->e_regione() )
                $dati_ente = cwbParGen::getBorClient();  // Dati dell'Ente
                // Codice Ente per Ditta
                switch($dati_ente['PROGCLIENT']) {
                    case 383:
                        // E' la Regione Marche
                        Out::attributo($this->nameForm . '_BOR_ORGDEL[TIPODELIB]', 'maxlength', 0, 15);
                        break;
                    default:
                        break;
                }
                break;
            default:
                break;
        }
    }
    
    protected function customParseEvent() {
        switch($_POST['event']){
            case 'onClick':
                switch($_POST['id']){
                    case $this->nameForm . '_BTN_NR_DS':
                        cwbLib::apriFinestraRicerca('cwbBtaNrd', $this->nameForm, 'returnNumDS', 'NR_DI', true, null, $this->nameFormOrig);
                        break;
                    case $this->nameForm . '_BTN_NR_DE':
                        cwbLib::apriFinestraRicerca('cwbBtaNrd', $this->nameForm, 'returnNumDE', 'NR_DI', true, null, $this->nameFormOrig);
                        break;
                }
                break;
            case 'onChange':
                switch($_POST['id']){
                    case $this->nameForm . '_BOR_ORGDEL[FLAG_NALS]':
                        $this->toggleNumeratore($this->nameForm . '_DS_DivNumeratoreAutomatico',$this->formData[$this->nameForm . '_BOR_ORGDEL']['FLAG_NALS']==1);
                        break;
                    case $this->nameForm . '_BOR_ORGDEL[FLAG_NALR]':
                        $this->toggleNumeratore($this->nameForm . '_DE_DivNumeratoreAutomatico',$this->formData[$this->nameForm . '_BOR_ORGDEL']['FLAG_NALR']==1);
                        break;
                }
                break;
            case 'returnNumDS':
                $this->setDSValue($this->formData['returnData']);
                break;
            case 'returnNumDE':
                $this->setDEValue($this->formData['returnData']);
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if(!empty($_POST['TIPODELIB'])){
            $this->gridFilters['TIPODELIB'] = $this->formData['TIPODELIB'];
        }
        if(!empty($_POST['DES_ORDE'])){
            $this->gridFilters['DES_ORDE'] = $this->formData['DES_ORDE'];
        }
        if(!empty($_POST['FLAG_NALS'])){
            $this->gridFilters['FLAG_NALS'] = $this->formData['FLAG_NALS']-1;
        }
        if(!empty($_POST['FLAG_NALR'])){
            $this->gridFilters['FLAG_NALR'] = $this->formData['FLAG_NALR']-1;
        }
        if(!empty($_POST['COD_NR_DS'])){
            $this->gridFilters['COD_NR_DS'] = $this->formData['COD_NR_DS'];
        }
        if(!empty($_POST['COD_NR_DE'])){
            $this->gridFilters['COD_NR_DE'] = $this->formData['COD_NR_DE'];
        }
        if(!empty($_POST['FLAG_TPORG'])){
           $this->gridFilters['FLAG_TPORG'] = $this->formData['FLAG_TPORG']-1;
        }
        if(!empty($_POST['CODUTE'])){
           $this->gridFilters['CODUTE'] = $this->formData['CODUTE'];
        }
        if(!empty($_POST['FLAG_DIS'])){
           $this->gridFilters['FLAG_DIS'] = $this->formData['FLAG_DIS']-1;
        }
    }

    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        if(empty($filtri['TIPODELIB'])){
            $filtri['TIPODELIB'] = trim($this->formData[$this->nameForm . '_TIPODELIB']);
        }
        if(empty($filtri['DES_ORDE'])){
            $filtri['DES_ORDE'] = trim($this->formData[$this->nameForm . '_DES_ORDE']);
        }
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlleggiBorOrgdel($filtri, false, $sqlParams);
    }

    protected function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlleggiBorOrgdel(array('TIPODELIB'=>$index), false, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        if(is_array($Result_tab)){
            foreach ($Result_tab as &$row) {
                switch($row['FLAG_NALS']){
                    case 0:
                        $row['FLAG_NALS'] = 'Numerazione automatica non gestita';
                        break;
                    case 1:
                        $row['FLAG_NALS'] = 'Numerazione unica per tipo atto';
                        break;
                    case 2:
                        $row['FLAG_NALS'] = 'Numerazione per servizio da pianta organica';
                        break;
                    case 3:
                        $row['FLAG_NALS'] = 'Numerazione automatica non gestita (No bloccante)';
                        break;
                    default:
                        $row['FLAG_NALS'] = '';
                        break;
                }
                switch($row['FLAG_NALR']){
                    case 0:
                        $row['FLAG_NALR'] = 'Numerazione automatica non gestita';
                        break;
                    case 1:
                        $row['FLAG_NALR'] = 'Numerazione unica per tipo atto';
                        break;
                    case 2:
                        $row['FLAG_NALR'] = 'Numerazione per servizio da pianta organica';
                        break;
                    case 3:
                        $row['FLAG_NALR'] = 'Numerazione automatica non gestita (No bloccante)';
                        break;
                    default:
                        $row['FLAG_NALR'] = '';
                        break;
                }
                switch($row['FLAG_TPORG']){
                    case 0:
                        $row['FLAG_TPORG'] = 'Non Specificato';
                        break;
                    case 1:
                        $row['FLAG_TPORG'] = 'Consiglio';
                        break;
                    case 2:
                        $row['FLAG_TPORG'] = 'Giunta';
                        break;
                    case 3:
                        $row['FLAG_TPORG'] = 'Atto del dirigente';
                        break;
                    default:
                        $row['FLAG_NALR'] = '';
                        break;
                }
                $date = strtotime($row['DATAINIZ']);
                $row['CUSTOMKEY'] = $row['CODUTE'].'|'.date('Y-m-d',$date);
            }
        }
        return $Result_tab;
    }
    
    protected function preNuovo() {
        $this->popolaSelect();
        
        $this->toggleNumeratore($this->nameForm . '_DS_DivNumeratoreAutomatico',false);
        Out::valore($this->nameForm . '_DES_NR_DS','');
        Out::valore($this->nameForm . '_NUM_NR_DS','');
        
        $this->toggleNumeratore($this->nameForm . '_DE_DivNumeratoreAutomatico',false);
        Out::valore($this->nameForm . '_DES_NR_DR','');
        Out::valore($this->nameForm . '_NUM_NR_DR','');
        
    }
    
    protected function preDettaglio($index, &$sqlDettaglio = null) {
        $this->popolaSelect();
        
        Out::valore($this->nameForm . '_DES_NR_DS','');
        Out::valore($this->nameForm . '_NUM_NR_DS','');
        Out::valore($this->nameForm . '_DES_NR_DR','');
        Out::valore($this->nameForm . '_NUM_NR_DR','');
    }
    
    protected function postDettaglio($index, &$sqlDettaglio = null) {
        $libDB_BTA = new cwbLibDB_BTA();
        $this->toggleNumeratore($this->nameForm . '_DS_DivNumeratoreAutomatico',$this->CURRENT_RECORD['FLAG_NALS']==1);
        if($this->CURRENT_RECORD['FLAG_NALS']==1){
            $filtri = array(
                'COD_NR_D' => $this->CURRENT_RECORD['COD_NR_DS']
            );
            $numeratoreData = $libDB_BTA->leggiBtaNrd($filtri, false);
            $this->setDSValue($numeratoreData);
        }
        
        $this->toggleNumeratore($this->nameForm . '_DE_DivNumeratoreAutomatico',$this->CURRENT_RECORD['FLAG_NALR']==1);
        if($this->CURRENT_RECORD['FLAG_NALR']==1){
            $filtri = array(
                'COD_NR_D' => $this->CURRENT_RECORD['COD_NR_DE']
            );
            $numeratoreData = $libDB_BTA->leggiBtaNrd($filtri, false);
            $this->setDEValue($numeratoreData);
        }
    }

    /*
     * Tratto i Parametri Esterni passati con externalParams (Standard es. ApriFinestra)
     */
    protected function postExternalFilter() {
        $filters = $this->getExternalParamsNormalyzed();
        foreach($filters as $key=>$value){
            switch($key){
                case 'TIPODELIB':
                    Out::valore($this->nameForm . '_TIPODELIB', $value['VALORE']);
                    if ($value['PERMANENTE'] === true){
                        Out::disableField($this->nameForm . '_TIPODELIB');
                    }
                    break;
                default:
                    break;
            }
        }
    }
    
    private function popolaSelect(){
        Out::html($this->nameForm . '_BOR_ORGDEL[FLAG_TPORG]', '');
        Out::select($this->nameForm . '_BOR_ORGDEL[FLAG_TPORG]', 1, 0, false, 'Non Specificato');
        Out::select($this->nameForm . '_BOR_ORGDEL[FLAG_TPORG]', 1, 1, false, 'Consiglio');
        Out::select($this->nameForm . '_BOR_ORGDEL[FLAG_TPORG]', 1, 2, false, 'Giunta');
        Out::select($this->nameForm . '_BOR_ORGDEL[FLAG_TPORG]', 1, 3, false, 'Atto del dirigente');
        
        Out::html($this->nameForm . '_BOR_ORGDEL[FLAG_NALS]', '');
        Out::select($this->nameForm . '_BOR_ORGDEL[FLAG_NALS]', 1, 0, false, 'Numerazione automatica non gestita');
        Out::select($this->nameForm . '_BOR_ORGDEL[FLAG_NALS]', 1, 1, false, 'Numerazione unica per tipo atto');
        Out::select($this->nameForm . '_BOR_ORGDEL[FLAG_NALS]', 1, 2, false, 'Numerazione per servizio da pianta organica');
        Out::select($this->nameForm . '_BOR_ORGDEL[FLAG_NALS]', 1, 3, false, 'Numerazione automatica non gestita (No bloccante)');
        
        Out::html($this->nameForm . '_BOR_ORGDEL[FLAG_NALR]', '');
        Out::select($this->nameForm . '_BOR_ORGDEL[FLAG_NALR]', 1, 0, false, 'Numerazione automatica non gestita');
        Out::select($this->nameForm . '_BOR_ORGDEL[FLAG_NALR]', 1, 1, false, 'Numerazione unica per tipo atto');
        Out::select($this->nameForm . '_BOR_ORGDEL[FLAG_NALR]', 1, 2, false, 'Numerazione per servizio da pianta organica');
        Out::select($this->nameForm . '_BOR_ORGDEL[FLAG_NALR]', 1, 3, false, 'Numerazione automatica non gestita (No bloccante)');
    }
    
    private function toggleNumeratore($target,$show){
        if($show){
            Out::show($target);
        }
        else{
            Out::hide($target);
        }
    }
    
    private function setDSValue($numeratoreData=null){
        if(isSet($numeratoreData)){
            $calcoloNumeratori = new cwbBtaNumeratori();
            $nrDS = $calcoloNumeratori->calcolaNumeratore($this->anno, $numeratoreData['COD_NR_D'], $numeratoreData['SETT_IVA'], false, false, true);

            Out::valore($this->nameForm . '_BOR_ORGDEL[COD_NR_DS]',$numeratoreData['COD_NR_D']);
            Out::valore($this->nameForm . '_DES_NR_DS',$numeratoreData['DES_NR_D']);
            Out::valore($this->nameForm . '_NUM_NR_DS',$nrDS['NUMULTDOC']);
        }
        else{
            Out::valore($this->nameForm . '_BOR_ORGDEL[COD_NR_DS]','');
            Out::valore($this->nameForm . '_DES_NR_DS','');
            Out::valore($this->nameForm . '_NUM_NR_DS','');
        }
    }
    
    private function setDEValue($numeratoreData=null){
        if(isSet($numeratoreData)){
            $calcoloNumeratori = new cwbBtaNumeratori();
            $nrDS = $calcoloNumeratori->calcolaNumeratore($this->anno, $numeratoreData['COD_NR_D'], $numeratoreData['SETT_IVA'], false, false, true);

            Out::valore($this->nameForm . '_BOR_ORGDEL[COD_NR_DE]',$numeratoreData['COD_NR_D']);
            Out::valore($this->nameForm . '_DES_NR_DE',$numeratoreData['DES_NR_D']);
            Out::valore($this->nameForm . '_NUM_NR_DE',$nrDS['NUMULTDOC']);
        }
        else{
            Out::valore($this->nameForm . '_BOR_ORGDEL[COD_NR_DE]','');
            Out::valore($this->nameForm . '_DES_NR_DE','');
            Out::valore($this->nameForm . '_NUM_NR_DE','');
        }
    }
    
    protected function preAltraRicerca() {
        Out::gridCleanFilters($this->nameForm, $this->GRID_NAME);
//        Out::valore('gs_TIPODELIB', '');
//        Out::valore('gs_DES_ORDE', '');
//        Out::valore('gs_FLAG_NALS', '');
//        Out::valore('gs_FLAG_NALR', '');
//        Out::valore('gs_COD_NR_DS', '');
//        Out::valore('gs_COD_NR_DE', '');
//        Out::valore('gs_FLAG_TPORG', '');
//        Out::valore('gs_CODUTE', '');
//        Out::valore('gs_FLAG_DIS', '');
    }
}

?>