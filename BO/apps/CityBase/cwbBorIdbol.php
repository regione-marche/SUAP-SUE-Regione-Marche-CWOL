<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

function cwbBorIdbol() {
    $cwbBorIdbol = new cwbBorIdbol();
    $cwbBorIdbol->parseEvent();
    return;
}

class cwbBorIdbol extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBorIdbol';
        $this->AUTOR_MODULO = 'BOR';
        $this->AUTOR_NUMERO = 1;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BOR();
    }

    protected function postApriForm() {
        
    }

    protected function postDettaglio($index) {
        
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['IDBOL_SERE_formatted'] != '') {
            $this->gridFilters['IDBOL_SERE'] = $this->formData['IDBOL_SERE_formatted'];
        }
        if ($_POST['DES_SEREMI'] != '') {
            $this->gridFilters['DES_SEREMI'] = $this->formData['DES_SEREMI'];
        }
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['IDBOL_SERE'] = trim($this->formData[$this->nameForm . '_IDBOL_SERE']);
        $filtri['DES_SEREMI'] = trim($this->formData[$this->nameForm . '_DES_SEREMI']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBorIdbol($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBorIdbolChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['IDBOL_SERE_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['IDBOL_SERE']);
        }
        return $Result_tab;
    }

}

?>