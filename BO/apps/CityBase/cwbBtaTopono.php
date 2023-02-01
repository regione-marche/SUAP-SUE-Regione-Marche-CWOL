<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaTopono() {
    $cwbBtaTopono = new cwbBtaTopono();
    $cwbBtaTopono->parseEvent();
    return;
}

class cwbBtaTopono extends cwbBpaGenTab {

    protected function initVars() {
        $this->GRID_NAME = 'gridBtaTopono';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 6;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_TOPONO[TOPONKES]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['TOPONKES'], $this->nameForm . '_BTA_TOPONO[TOPONKES]');
                        break;
                }
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['TOPONIMO'] != '') {
            $this->gridFilters['TOPONIMO'] = $this->formData['TOPONIMO'];
        }
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_TOPONIMO');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_TOPONIMO');
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_TOPONO[TOPONIMO]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_TOPONO[TOPONIMO]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_TOPONO[TOPONIMO]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_TOPONO[TOPONIMO]');
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_TOPONO[TOPONIMO]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_TOPONO[TOPONIMO]', 'background-color', '#FFFFE0');
        Out::setFocus('', $this->nameForm . '_BTA_TOPONO[TOPONKES]');
    }

    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['TOPONIMO'] = trim($this->formData[$this->nameForm . '_TOPONIMO']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaTopono($filtri, true, $sqlParams);
    }

    protected function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaToponoChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['TOPONIMO_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['TOPONIMO']);
        }
        return $Result_tab;
    }

}

?>