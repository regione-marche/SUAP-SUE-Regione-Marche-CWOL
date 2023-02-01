<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGD.class.php';

function cwbBgdSostag() {
    $cwbBgdSostag = new cwbBgdSostag();
    $cwbBgdSostag->parseEvent();
    return;
}

class cwbBgdSostag extends cwbBpaGenTab {
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBgdSostag';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    protected function initVars() {
        $this->GRID_NAME = 'gridBgdSostag';
        $this->AUTOR_MODULO = 'BGD';
        $this->AUTOR_NUMERO = 1;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BGD();
        
        $this->actionAfterNew = self::GOTO_LIST;
        $this->actionAfterModify = self::GOTO_LIST;
        $this->actionAfterDelete = self::GOTO_LIST;
        
        $this->openDetailFlag = true;
    }
    
    protected function preParseEvent() {
        switch($_POST['event']){
            case 'openform':
                $this->initComponents();
                break;
        }
    }
    
    private function initComponents() {
        Out::html($this->nameForm . '_BGD_SOSTAG[FLAG_OBBL]','');
        Out::select($this->nameForm . '_BGD_SOSTAG[FLAG_OBBL]', 1, 0, false, 'Non obbligatorio');
        Out::select($this->nameForm . '_BGD_SOSTAG[FLAG_OBBL]', 1, 1, false, 'Obbligatorio');
        Out::select($this->nameForm . '_BGD_SOSTAG[FLAG_OBBL]', 1, 2, false, 'Forzabile');
        
        Out::html($this->nameForm . '_BGD_SOSTAG[FORMATO]','');
        Out::select($this->nameForm . '_BGD_SOSTAG[FORMATO]', 1, 1, false, 'Stringa');
        Out::select($this->nameForm . '_BGD_SOSTAG[FORMATO]', 1, 2, false, 'Intero');
        Out::select($this->nameForm . '_BGD_SOSTAG[FORMATO]', 1, 3, false, 'Data(yyyy-MM-dd)');
        Out::select($this->nameForm . '_BGD_SOSTAG[FORMATO]', 1, 4, false, 'Data/Ora(yyyy-MM-ddTHH:NN:SS)');
        Out::select($this->nameForm . '_BGD_SOSTAG[FORMATO]', 1, 5, false, 'Boolean');
        Out::select($this->nameForm . '_BGD_SOSTAG[FORMATO]', 1, 6, false, 'Importo 2DP');
        Out::select($this->nameForm . '_BGD_SOSTAG[FORMATO]', 1, 7, false, 'Importo 5DP');
        Out::select($this->nameForm . '_BGD_SOSTAG[FORMATO]', 1, 8, false, 'Data(dd/MM/yyyy)');
        
        Out::html($this->nameForm . '_BGD_SOSTAG[SRC_DATI]','');
        Out::select($this->nameForm . '_BGD_SOSTAG[SRC_DATI]', 1, 1, false, 'Lista guida');
        Out::select($this->nameForm . '_BGD_SOSTAG[SRC_DATI]', 1, 2, false, 'Lista sezione');
    }
    
    protected function customParseEvent() {
        switch($_POST['event']){
            case 'onClick':
                switch($_POST['id']){
                    case $this->nameForm . '_BGD_SOSTAG[ID_PADRE]_butt':
                        $externalParams['IDSOSSEZ'] = $this->formData[$this->nameForm . '_BGD_SOSTAG']['IDSOSSEZ'];
                        cwbLib::apriFinestraRicerca('cwbBgdSostag', $this->nameForm, 'returnSostag', 'IDSOSTAG', true, $externalParams, $this->nameFormOrig);
                        break;
                    case $this->nameForm . '_BGD_SOSTAG[IDSOSSEZ]_butt':
                        cwbLib::apriFinestraRicerca('cwbBgdSossez', $this->nameForm, 'returnSossez', 'IDSOSSEZ', true, null, $this->nameFormOrig);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BGD_SOSTAG[ID_PADRE]':
                        $value = trim($_POST[$this->nameForm . '_BGD_SOSTAG']['ID_PADRE']);
                        $this->loadSostagPadre($value);
                        break;
                    case $this->nameForm . '_BGD_SOSTAG[IDSOSSEZ]':
                        $value = trim($_POST[$this->nameForm . '_BGD_SOSTAG']['IDSOSSEZ']);
                        $this->loadSossez($value);
                        break;
                    case $this->nameForm . '_BGD_SOSTAG[NODO_FOGLIA]':
                        $this->onChangeNodoFoglia();
                        break;
                }
                break;
            case 'returnSostag':
                $this->loadSostagPadre($this->formData['returnData']['IDSOSTAG']);
                break;
            case 'returnSossez':
                $this->loadSossez($this->formData['returnData']['IDSOSSEZ']);
                break;
        }
    }
    
    protected function setGridFilters() {
        $this->gridFilters = array();
        if(!empty($_POST['IDSOSTAG'])) {
            $this->gridFilters['IDSOSTAG'] = $this->formData['IDSOSTAG'];
        }
        if(!empty($_POST['IDSOSSEZ'])) {
            $this->gridFilters['IDSOSSEZ'] = $this->formData['IDSOSSEZ'];
        }
        if(!empty($_POST['SEQUENZA'])) {
            $this->gridFilters['SEQUENZA'] = $this->formData['SEQUENZA'];
        }
        if(!empty($_POST['NOME_TAG'])) {
            $this->gridFilters['NOME_TAG'] = $this->formData['NOME_TAG'];
        }
        if(!empty($_POST['ID_PADRE'])) {
            $this->gridFilters['ID_PADRE'] = $this->formData['ID_PADRE'];
        }
        if(!empty($_POST['NODO_FOGLIA'])) {
            $this->gridFilters['NODO_FOGLIA'] = $this->formData['NODO_FOGLIA'];
        }
        if(!empty($_POST['FORMATO_TAG'])) {
            $this->gridFilters['FORMATO_TAG'] = $this->formData['FORMATO_TAG'];
        }
        if(!empty($_POST['TAB_ALIAS'])) {
            $this->gridFilters['TAB_ALIAS'] = $this->formData['TAB_ALIAS'];
        }
        if(!empty($_POST['FLAG_OBBL'])) {
            $this->gridFilters['FLAG_OBBL'] = $this->formData['FLAG_OBBL'];
        }
        if(!empty($_POST['VALORE'])) {
            $this->gridFilters['VALORE'] = $this->formData['VALORE'];
        }
        if(!empty($_POST['CODUTE'])){
           $this->gridFilters['CODUTE'] = $this->formData['CODUTE'];
        }
        if(!empty($_POST['FLAG_DIS'])){
           $this->gridFilters['FLAG_DIS'] = $this->formData['FLAG_DIS']-1;
        }
    }

    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        if(!isSet($filtri['IDSOSTAG'])){
            $filtri['IDSOSTAG'] = trim($this->formData[$this->nameForm . '_IDSOSTAG']);
        }
        if(!isSet($filtri['NOME_TAG'])){
            $filtri['NOME_TAG'] = trim($this->formData[$this->nameForm . '_NOME_TAG']);
        }
        if(!isSet($filtri['DESCRIZIONE'])){
            $filtri['DESCRIZIONE'] = trim($this->formData[$this->nameForm . '_DESCRIZIONE']);
        }
        
        if($this->externalParams['IDSOSSEZ'] > 0) {
            $filtri['IDSOSSEZ'] = $this->externalParams['IDSOSSEZ'];
        }
        
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBgdSostag($filtri, false, $sqlParams);
    }

    protected function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBgdSostag(array('IDSOSTAG'=>$index), false, $sqlParams);
    }
    
    protected function elaboraRecords($Result_tab) {
        
        foreach($Result_tab as $key => $record) {
            switch ($Result_tab[$key]['FORMATO']) {
                case 1:
                    $Result_tab[$key]['FORMATO_TAG'] = 'STRINGA';
                    break;
                case 2:
                    $Result_tab[$key]['FORMATO_TAG'] = 'INTERO';
                    break;
                case 3:
                    $Result_tab[$key]['FORMATO_TAG'] = 'DATA (yyyy-MM-dd)';
                    break;
                case 4:
                    $Result_tab[$key]['FORMATO_TAG'] = 'DATA/ORA (yyyy-MM-ddTHH:NN:SS)';
                    break;
                case 5:
                    $Result_tab[$key]['FORMATO_TAG'] = 'BOOLEANO';
                    break;
                case 6:
                    $Result_tab[$key]['FORMATO_TAG'] = 'IMPORTO 2DP';
                    break;
                case 7:
                    $Result_tab[$key]['FORMATO_TAG'] = 'IMPORTO 5DP';
                    break;
                case 8:
                    $Result_tab[$key]['FORMATO_TAG'] = 'DATA (dd/MM/yyyy)';
                    break;
            }
            
            if($Result_tab[$key]['SRC_METDOC']) {
                $Result_tab[$key]['VALORE'] = $Result_tab[$key]['SRC_METDOC'];
            } else if($Result_tab[$key]['SRC_CAMPODB']) {
                $Result_tab[$key]['VALORE'] = $Result_tab[$key]['SRC_CAMPODB'];
            } else if($Result_tab[$key]['SRC_EXPR']) {
                $Result_tab[$key]['VALORE'] = $Result_tab[$key]['SRC_EXPR'];
            } else if($Result_tab[$key]['SRC_FISSO']) {
                $Result_tab[$key]['VALORE'] = $Result_tab[$key]['SRC_FISSO'];
            }
            
            $Result_tab[$key]['VERSIONE'] = 'Da ' . $Result_tab[$key]['VERSIONE_DA'] . ' a ' . $Result_tab[$key]['VERSIONE_A'];
        }
        
        return $Result_tab;
    }
    
    protected function postNuovo() {
        Out::hide($this->nameForm.'_BGD_SOSTAG[IDSOSTAG]');
        Out::hide($this->nameForm.'_BGD_SOSTAG[IDSOSTAG]_lbl');
        
        Out::setFocus("", $this->nameForm . '_BGD_SOSTAG[NOME_TAG]');
        
        Out::valore($this->nameForm . '_NOME_PADRE', '');
        Out::valore($this->nameForm . '_NOME_SEZIONE', '');
    }
    
    protected function postDettaglio() {
        Out::show($this->nameForm.'_BGD_SOSTAG[IDSOSTAG]');
        Out::show($this->nameForm.'_BGD_SOSTAG[IDSOSTAG]_lbl');
        Out::disableField($this->nameForm.'_BGD_SOSTAG[IDSOSTAG]');
        
        Out::setFocus("", $this->nameForm . '_BGD_SOSTAG[NOME_TAG]');
        
        $this->loadSostagPadre($this->CURRENT_RECORD['ID_PADRE']);
        
        $this->loadSossez($this->CURRENT_RECORD['IDSOSSEZ']);
        
        $this->onChangeNodoFoglia($this->CURRENT_RECORD['NODO_FOGLIA']);
    }
    
    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_IDSOSTAG');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_IDSOSTAG');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BGD_SOSTAG[IDSOSTAG]');
    }
    
    private function loadSostagPadre($idsostag) {
        if($idsostag) {
            $bgd_sostag = $this->libDB->leggiBgdSostagChiave($idsostag);
            
            Out::valore($this->nameForm . '_BGD_SOSTAG[ID_PADRE]', $bgd_sostag['IDSOSTAG']);
            Out::valore($this->nameForm . '_NOME_PADRE', $bgd_sostag['NOME_TAG']);
        } else {
            Out::valore($this->nameForm . '_BGD_SOSTAG[ID_PADRE]', '');
            Out::valore($this->nameForm . '_NOME_PADRE', '');
        }
    }
    
    private function loadSossez($idsossez) {
        if($idsossez) {
            $bgd_sossez = $this->libDB->leggiBgdSossezChiave($idsossez);
            
            Out::valore($this->nameForm . '_BGD_SOSTAG[IDSOSSEZ]', $bgd_sossez['IDSOSSEZ']);
            Out::valore($this->nameForm . '_NOME_SEZIONE', $bgd_sossez['SEZIONE']);
        } else {
            Out::valore($this->nameForm . '_BGD_SOSTAG[IDSOSSEZ]', '');
            Out::valore($this->nameForm . '_NOME_SEZIONE', '');
        }
    }
    
    private function onChangeNodoFoglia($nodo_foglia) {
        if(empty($nodo_foglia)) {
            $nodo_foglia = $this->formData[$this->nameForm . '_BGD_SOSTAG']['NODO_FOGLIA'];
        }
        
        if($nodo_foglia) {
            Out::disableField($this->nameForm.'_BGD_SOSTAG[FLAG_OBBL]');
            Out::disableField($this->nameForm.'_BGD_SOSTAG[FORMATO]');
            Out::disableField($this->nameForm.'_BGD_SOSTAG[LUNG_CAMPO]');
            Out::disableField($this->nameForm.'_BGD_SOSTAG[SRC_DATI]');
            Out::disableField($this->nameForm.'_BGD_SOSTAG[TAB_ALIAS]');
            Out::disableField($this->nameForm.'_BGD_SOSTAG[SRC_METDOC]');
            Out::disableField($this->nameForm.'_BGD_SOSTAG[SRC_CAMPODB]');
            Out::disableField($this->nameForm.'_BGD_SOSTAG[SRC_EXPR]');
            Out::disableField($this->nameForm.'_BGD_SOSTAG[SRC_FISSO]');
            
            Out::valore($this->nameForm.'_BGD_SOSTAG[FLAG_OBBL]', '');
            Out::valore($this->nameForm.'_BGD_SOSTAG[FORMATO]', '');
            Out::valore($this->nameForm.'_BGD_SOSTAG[LUNG_CAMPO]', '');
            Out::valore($this->nameForm.'_BGD_SOSTAG[SRC_DATI]', '');
            Out::valore($this->nameForm.'_BGD_SOSTAG[TAB_ALIAS]', '');
            Out::valore($this->nameForm.'_BGD_SOSTAG[SRC_METDOC]', '');
            Out::valore($this->nameForm.'_BGD_SOSTAG[SRC_CAMPODB]', '');
            Out::valore($this->nameForm.'_BGD_SOSTAG[SRC_EXPR]', '');
            Out::valore($this->nameForm.'_BGD_SOSTAG[SRC_FISSO]', '');
        } else {
            Out::enableField($this->nameForm.'_BGD_SOSTAG[FLAG_OBBL]');
            Out::enableField($this->nameForm.'_BGD_SOSTAG[FORMATO]');
            Out::enableField($this->nameForm.'_BGD_SOSTAG[LUNG_CAMPO]');
            Out::enableField($this->nameForm.'_BGD_SOSTAG[SRC_DATI]');
            Out::enableField($this->nameForm.'_BGD_SOSTAG[TAB_ALIAS]');
            Out::enableField($this->nameForm.'_BGD_SOSTAG[SRC_METDOC]');
            Out::enableField($this->nameForm.'_BGD_SOSTAG[SRC_CAMPODB]');
            Out::enableField($this->nameForm.'_BGD_SOSTAG[SRC_EXPR]');
            Out::enableField($this->nameForm.'_BGD_SOSTAG[SRC_FISSO]');
        }
    }

}

?>