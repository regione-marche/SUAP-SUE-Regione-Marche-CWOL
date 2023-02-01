<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

function cwbBorLivell() {
    $cwbBorLivell = new cwbBorLivell();
    $cwbBorLivell->parseEvent();
    return;
}

class cwbBorLivell extends cwbBpaGenTab {
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBorLivell';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    protected function initVars() {
        $this->GRID_NAME = 'gridBorLivell';
        $this->AUTOR_MODULO = 'BOR';
        $this->AUTOR_NUMERO = 5;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BOR();
        
        $this->actionAfterNew = self::GOTO_LIST;
        $this->actionAfterModify = self::GOTO_LIST;
        $this->actionAfterDelete = self::GOTO_LIST;
        
        $this->openDetailFlag = true;
        $this->elencaAutoAudit = true;
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if(!empty($_POST['IDLIVELL'])){
            $this->gridFilters['IDLIVELL'] = $this->formData['IDLIVELL'];
        }
        if(!empty($_POST['DES_LIVELL'])){
            $this->gridFilters['DES_LIVELL'] = $this->formData['DES_LIVELL'];
        }
    }

    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        if(empty($filtri['IDLIVELL'])){
            $filtri['IDLIVELL'] = trim($this->formData[$this->nameForm . '_IDLIVELL']);
        }
        if(empty($filtri['DES_LIVELL'])){
            $filtri['DES_LIVELL'] = trim($this->formData[$this->nameForm . '_DES_LIVELL']);
        }
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBorLivell($filtri, false, $sqlParams);
    }

    protected function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBorLivell(array('IDLIVELL'=>$index), false, $sqlParams);
    }

    protected function preAltraRicerca() {
        Out::gridCleanFilters($this->nameForm, $this->GRID_NAME);
//        Out::valore('gs_IDLIVELL','');
//        Out::valore('gs_DES_LIVELL','');
//        Out::valore('gs_CODUTE','');
//        Out::valore('gs_FLAG_DIS','');
    }
}

?>