<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGD.class.php';

function cwbBgdExt() {
    $cwbBgdExt = new cwbBgdExt();
    $cwbBgdExt->parseEvent();
    return;
}

class cwbBgdExt extends cwbBpaGenTab {
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBgdExt';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    protected function initVars() {
        $this->GRID_NAME = 'gridBgdExt';
        $this->AUTOR_MODULO = 'BGD';
        $this->AUTOR_NUMERO = 1;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BGD();
        
        $this->actionAfterNew = self::GOTO_LIST;
        $this->actionAfterModify = self::GOTO_LIST;
        $this->actionAfterDelete = self::GOTO_LIST;
        
        $this->openDetailFlag = true;
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }
    
    protected function setGridFilters() {
        $this->gridFilters = array();
        if(!empty($_POST['PK_EXT'])) {
            $this->gridFilters['PK_EXT'] = $this->formData['PK_EXT'];
        }
        if(!empty($_POST['SIGLA_EXT'])) {
            $this->gridFilters['SIGLA_EXT'] = $this->formData['SIGLA_EXT'];
        }
        if(!empty($_POST['F_CTRL_EXT'])) {
            $this->gridFilters['F_CTRL_EXT'] = $this->formData['F_CTRL_EXT'];
        }
    }

    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        if(!isSet($filtri['PK_EXT'])){
            $filtri['PK_EXT'] = trim($this->formData[$this->nameForm . '_PK_EXT']);
        }
        if(!isSet($filtri['SIGLA_EXT'])){
            $filtri['SIGLA_EXT'] = trim($this->formData[$this->nameForm . '_SIGLA_EXT']);
        }
        
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBgdExt($filtri, false, $sqlParams);
    }

    protected function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBgdExt(array('PK_EXT'=>$index), false, $sqlParams);
    }
    
    
    protected function elaboraRecords($Result_tab) {
        foreach($Result_tab as $key => $record) {
            switch ($Result_tab[$key]['F_CTRL_EXT']) {
                case 1:
                    $Result_tab[$key]['F_CTRL_EXT'] = 'Ammessa';
                    break;
                case 2:
                    $Result_tab[$key]['F_CTRL_EXT'] = 'Non ammessa';
                    break;
                case 3:
                    $Result_tab[$key]['F_CTRL_EXT'] = 'Richiesta forzatura';
                    break;
            }
        }
        
        return $Result_tab;
    }
    
    protected function postNuovo() {
        Out::hide($this->nameForm.'_BGD_EXT[PK_EXT]_lbl');
        Out::hide($this->nameForm.'_BGD_EXT[PK_EXT]');
        
        Out::setFocus("", $this->nameForm . '_BGD_EXT[SIGLA_EXT]');
    }
    
    protected function postDettaglio() {
        Out::show($this->nameForm.'_BGD_EXT[PK_EXT]_lbl');
        Out::show($this->nameForm.'_BGD_EXT[PK_EXT]');
        Out::disableField($this->nameForm.'_BGD_EXT[PK_EXT]');
        
        Out::setFocus("", $this->nameForm . '_BGD_EXT[SIGLA_EXT]');
    }
    
    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_PK_EXT');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_PK_EXT');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BGD_EXT[SIGLA_EXT]');
    }
    
    protected function preParseEvent() {
        switch($_POST['event']){
            case 'openform':
                $this->initComponents();
                break;
        }
    }
    
    private function initComponents() {
        Out::html($this->nameForm . '_BGD_EXT[F_CTRL_EXT]', '');
        Out::select($this->nameForm . '_BGD_EXT[F_CTRL_EXT]', 1, 1, false, 'Ammessa');
        Out::select($this->nameForm . '_BGD_EXT[F_CTRL_EXT]', 1, 2, false, 'Non ammessa');
        Out::select($this->nameForm . '_BGD_EXT[F_CTRL_EXT]', 1, 3, false, 'Richiesta forzatura');
    }
    
    
}

?>