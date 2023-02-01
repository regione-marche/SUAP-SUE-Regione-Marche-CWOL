<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBtaGrnote() {
    $cwbBtaGrnote = new cwbBtaGrnote();
    $cwbBtaGrnote->parseEvent();
    return;
}

class cwbBtaGrnote extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaGrnote';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 27;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_GRNOTE[IDGRNOTE]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['IDGRNOTE'], $this->nameForm . '_BTA_GRNOTE[IDGRNOTE]');
                        break;
                }
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['IDGRNOTE_formatted'] != '') {
            $this->gridFilters['IDGRNOTE'] = $this->formData['IDGRNOTE_formatted'];
        }
        if ($_POST['DESGRUPPO'] != '') {
            $this->gridFilters['DESGRUPPO'] = $this->formData['DESGRUPPO'];
        }
    }

    protected function postNuovo() {
        $progr = cwbLibCalcoli::trovaProgressivo("IDGRNOTE", "BTA_GRNOTE");
        Out::valore($this->nameForm . '_BTA_GRNOTE[IDGRNOTE]', $progr);
        Out::setFocus("", $this->nameForm . '_BTA_GRNOTE[DESGRUPPO]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_GRNOTE[DESGRUPPO]');
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DESGRUPPO');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DESGRUPPO');
    }

    protected function postDettaglio($index) {
        Out::setFocus('', $this->nameForm . '_BTA_GRNOTE[DESGRUPPO]');
        // toglie gli spazi del char
        Out::valore($this->nameForm . '_BTA_GRNOTE[DESGRUPPO]', trim($this->CURRENT_RECORD['DESGRUPPO']));
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['IDGRNOTE'] = trim($this->formData[$this->nameForm . '_IDGRNOTE']);
        $filtri['DESGRUPPO'] = trim($this->formData[$this->nameForm . '_DESGRUPPO']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaGrnote($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaGrnoteChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['IDGRNOTE_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['IDGRNOTE']);
        }
        return $Result_tab;
    }

}

