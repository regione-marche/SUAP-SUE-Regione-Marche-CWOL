<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibAllegatiUtil.class.php';

function cwbBtaDurc() {
    $cwbBtaDurc = new cwbBtaDurc();
    $cwbBtaDurc->parseEvent();
    return;
}

class cwbBtaDurc extends cwbBpaGenTab {

    private $componentBorOrganDettaglioModel;
    private $componentBorOrganDettaglioAlias;
    private $rowId;
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBtaDurc';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    function initVars() {
        $this->GRID_NAME = 'gridBtaDurc';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 31;
        $this->libDB = new cwbLibDB_BTA();
        $this->libDB_BOR = new cwbLibDB_BOR();
        
        $this->searchOpenElenco = true;
        $this->errorOnEmpty = false;
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
        
        $this->actionAfterNew = self::GOTO_LIST;
        $this->actionAfterModify = self::GOTO_LIST;
        $this->actionAfterDelete = self::GOTO_LIST;
        
        $this->openDetailFlag = true;
        
        $this->componentBorOrganDettaglioAlias = cwbParGen::getFormSessionVar($this->nameForm, 'componentBorOrganDettaglioAlias');
        if($this->componentBorOrganDettaglioAlias != ''){
            $this->componentBorOrganDettaglioModel = itaFrontController::getInstance('cwbComponentBorOrgan', $this->componentBorOrganDettaglioAlias);
        }
        $this->rowId = cwbParGen::getFormSessionVar($this->nameForm, 'rowId');
    }
    
    protected function preDestruct() {
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, 'componentBorOrganDettaglioAlias', $this->componentBorOrganDettaglioAlias);
            cwbParGen::setFormSessionVar($this->nameForm, 'rowId', $this->rowId);
        }
    }
    
    protected function setGridFilters() {
        $this->gridFilters = array();
        
        if(!empty($_POST['PROG_DURC'])) {
            $this->gridFilters['PROG_DURC'] = $this->formData['PROG_DURC'];
        }
        if(!empty($_POST['FLAG_POSIC'])) {
            $this->gridFilters['FLAG_POSIC'] = $this->formData['FLAG_POSIC'];
        }
        if(!empty($_POST['DES_NOTE'])) {
            $this->gridFilters['DES_NOTE'] = $this->formData['DES_NOTE'];
        }
    }

    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        if(!isSet($filtri['PROGSOGG'])){
            $filtri['PROGSOGG'] = $this->formData[$this->nameForm . '_PROGSOGG'];
        }
        if(!isSet($filtri['RAGSOC'])){
            $filtri['RAGSOC'] = trim($this->formData[$this->nameForm . '_RAGSOC']);
        }
        if(!isSet($filtri['DATAFINE_SEARCH'])){
            $filtri['DATAFINE_SEARCH'] = trim($this->formData[$this->nameForm . '_DATAFINE_SEARCH']);
        }
        
        $this->compilaFiltri($filtri);
        
        $this->SQL = $this->libDB->getSqlLeggiBtaDurc($filtri, false, $sqlParams);
    }

    protected function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaDurc(array('PROG_DURC'=>$index), true, $sqlParams);
    }
    
    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'openform':
                $this->initComboboxes();
                $this->initComponents();
                break;
            case 'onSelectRow':
                $this->rowId = $this->formData['rowid'];
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_DURC[PROGSOGG]_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaSogg', $this->nameForm, 'returnBtaSogg', 'PROGSOGG', true, null, $this->nameFormOrig);
                        break;
                    case $this->nameForm . '_COD_CUP_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaCup', $this->nameForm, 'returnBtaCup', 'COD_CUP', true, null, $this->nameFormOrig);
                        break;
                    case $this->nameForm . '_COD_CIG_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaCig', $this->nameForm, 'returnBtaCig', 'COD_CIG', true, null, $this->nameFormOrig);
                        break;
                    
                    case $this->nameForm . '_btnAllegati':
                        $this->viewMode = false;
                        if (!empty($this->rowId)){  // determinato rowId quando ho selezionato la riga 'onSelectRow'
                            $progDurc = $this->rowId;
                            $chiaveTestata = array(
                                'PROG_DURC' => $progDurc
                            );
                            cwbLibAllegatiUtil::apriFinestraAllegati('BTA_DURCAL', $chiaveTestata, array(), array(),$this->viewMode);
                        }
                        break; 
                }
            break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_DURC[PROGSOGG]':
                        $value = trim($_POST[$this->nameForm . '_BTA_DURC']['PROGSOGG']);
                        $this->setSoggetto($value);
                        break;
                    case $this->nameForm . '_COD_CUP':
                        $value = trim($_POST[$this->nameForm . '_COD_CUP']);
                        $this->setCup($value);
                        break;
                    case $this->nameForm . '_COD_CIG':
                        $value = trim($_POST[$this->nameForm . '_COD_CIG']);
                        $this->setCig($value);
                        break;
                }
            break;
            case 'returnBtaSogg':
                $this->setSoggetto($this->formData['returnData']['PROGSOGG']);
                break;
            case 'returnBtaCup':
                $this->setCup($this->formData['returnData']['COD_CUP']);
                break;
            case 'returnBtaCig':
                $this->setCig($this->formData['returnData']['COD_CIG']);
                break;
        }
        
        $this->componentBorOrganDettaglioModel->parseEvent();
    }
    
    private function initComponents(){
        $this->componentBorOrganDettaglioAlias = $this->nameForm . '_BorOrganDettaglio_'.time().rand(0,1000);
        itaLib::openInner('cwbComponentBorOrgan', '', true, $this->nameForm . '_divDettaglioORGAN', '', '', $this->componentBorOrganDettaglioAlias);
        $this->componentBorOrganDettaglioModel = itaFrontController::getInstance('cwbComponentBorOrgan', $this->componentBorOrganDettaglioAlias);
        $this->componentBorOrganDettaglioModel->setReturnData(array(
                'L1ORG'=>$this->nameForm . '_BTA_DURC[L1ORG_RIC]',
                'L2ORG'=>$this->nameForm . '_BTA_DURC[L2ORG_RIC]',
                'L3ORG'=>$this->nameForm . '_BTA_DURC[L3ORG_RIC]',
                'L4ORG'=>$this->nameForm . '_BTA_DURC[L4ORG_RIC]',
                'IDORGAN'=>$this->nameForm . '_BTA_DURC[IDORGAN]'
            ));
        $this->componentBorOrganDettaglioModel->initSelector(true,$disable);
    }
    
    private function initComboboxes(){
        Out::html($this->nameForm . '_BTA_DURC[FLAG_POSIC]','');
        Out::select($this->nameForm . '_BTA_DURC[FLAG_POSIC]', 1, 0, false, 'Regolare');
        Out::select($this->nameForm . '_BTA_DURC[FLAG_POSIC]', 1, 1, false, 'Non regolare');
    }
    
    protected function elaboraRecords($Result_tab) {
        if(is_array($Result_tab)){
            foreach ($Result_tab as $key => $Result_rec) {

                // SOGGETTO
                $Result_tab[$key]['SOGGETTO'] = $Result_tab[$key]['COGNOME'] . ' ' . $Result_tab[$key]['NOME'];

                // SERVIZIO
                $filtri = array();
                $filtri['L1ORG'] = $Result_tab[$key]['L1ORG_RIC'];
                $filtri['L2ORG'] = $Result_tab[$key]['L2ORG_RIC'];
                $filtri['L3ORG'] = $Result_tab[$key]['L3ORG_RIC'];
                $filtri['L4ORG'] = $Result_tab[$key]['L4ORG_RIC'];
                $bor_organ = $this->libDB_BOR->leggiBorOrgan($filtri, false);

                $Result_tab[$key]['SERVIZIO'] = $Result_tab[$key]['L1ORG_RIC'] . ' ' . $Result_tab[$key]['L2ORG_RIC'] . ' ' .
                        $Result_tab[$key]['L3ORG_RIC'] . ' ' . $Result_tab[$key]['L4ORG_RIC'] . '<br/>' .
                        $bor_organ['DESPORG'];

                // CUP
                $bta_cup = $this->libDB->leggiBtaCupChiave($Result_tab[$key]['PROG_CUP']);
                $Result_tab[$key]['COD_CUP'] = $bta_cup['COD_CUP'];
                $Result_tab[$key]['DES_CUP'] = $bta_cup['DES_BREVE'];

                // CIG
                $bta_cig = $this->libDB->leggiBtaCigChiave($Result_tab[$key]['PROG_CIG']);
                $Result_tab[$key]['COD_CIG'] = $bta_cig['COD_CIG'];
                $Result_tab[$key]['DES_CIG'] = $bta_cig['DES_BREVE'];
            }
        }
        
        return $Result_tab;
    }
    
    protected function postNuovo() {
        Out::hide($this->nameForm.'_BTA_DURC[PROG_DURC]_lbl');
        Out::hide($this->nameForm.'_BTA_DURC[PROG_DURC]');
        
        Out::setFocus("", $this->nameForm . '_BTA_DURC[PROGSOGG]');
        
        $progsogg = $this->formData[$this->nameForm . '_PROGSOGG'];
        $this->setSoggetto($progsogg);
//        $this->setSoggetto('');
        $this->setCup('');
        $this->setCig('');
        
        $this->componentBorOrganDettaglioModel->setLxORG();
    }
    
    protected function postDettaglio() {
        Out::show($this->nameForm.'_BTA_DURC[PROG_DURC]');
        Out::disableField($this->nameForm.'_BTA_DURC[PROG_DURC]');
        
        Out::setFocus("", $this->nameForm . '_BTA_DURC[PROGSOGG]');
        
        $this->setSoggetto($this->CURRENT_RECORD['PROGSOGG']);
        
        $bta_cup = $this->libDB->leggiBtaCupChiave($this->CURRENT_RECORD['PROG_CUP']);
        $this->setCup($bta_cup['COD_CUP']);
        
        $bta_cig = $this->libDB->leggiBtaCigChiave($this->CURRENT_RECORD['PROG_CIG']);
        $this->setCig($bta_cig['COD_CIG']);
        
        $this->componentBorOrganDettaglioModel->setLxORG($this->CURRENT_RECORD['L1ORG_RIC'], $this->CURRENT_RECORD['L2ORG_RIC'], 
                $this->CURRENT_RECORD['L3ORG_RIC'], $this->CURRENT_RECORD['L4ORG_RIC']);
    }
    
    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_PROGSOGG');
        
        Out::valore($this->nameForm . '_DATAFINE_SEARCH', date('dmY'));
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_PROGSOGG');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_DURC[PROGSOGG]');
    }
    
    private function setSoggetto($progsogg) {
        
        Out::valore($this->nameForm . '_BTA_DURC[PROGSOGG]', '');
        Out::valore($this->nameForm . '_DES_SOGGETTO', '');
        
        if (strlen($progsogg) > 0) {
            $record = $this->libDB->leggiBtaSoggChiave($progsogg);
            
            Out::valore($this->nameForm . '_BTA_DURC[PROGSOGG]', $record['PROGSOGG']);
            Out::valore($this->nameForm . '_DES_SOGGETTO', $record['COGNOME'] . ' ' . $record['NOME']);
        }
    }
    
    private function setCup($codCup) {
        Out::valore($this->nameForm . '_COD_CUP', '');
        Out::valore($this->nameForm . '_DES_CUP', '');
        Out::valore($this->nameForm . '_BTA_DURC[PROG_CUP]', 0);
        
        if (strlen($codCup) > 0) {
            $filtri = array(
                'COD_CUP' => $codCup
            );
            
            $record = $this->libDB->leggiBtaCup($filtri, false);
            
            Out::valore($this->nameForm . '_BTA_DURC[PROG_CUP]', $record['PROG_CUP']);
            Out::valore($this->nameForm . '_COD_CUP', $record['COD_CUP']);
            Out::valore($this->nameForm . '_DES_CUP', $record['DES_CUP']);
        }
    }
    
    private function setCig($codCig) {
        Out::valore($this->nameForm . '_COD_CIG', '');
        Out::valore($this->nameForm . '_DES_CIG', '');
        Out::valore($this->nameForm . '_BTA_DURC[PROG_CIG]', 0);
        
        if (strlen($codCig) > 0) {
            $filtri = array(
                'COD_CIG' => $codCig
            );
            
            $record = $this->libDB->leggiBtaCig($filtri, false);
            
            Out::valore($this->nameForm . '_COD_CIG', $record['COD_CIG']);
            Out::valore($this->nameForm . '_DES_CIG', $record['DES_BREVE']);
            Out::valore($this->nameForm . '_BTA_DURC[PROG_CIG]', $record['PROG_CIG']);
        }
    }
   
}

?>