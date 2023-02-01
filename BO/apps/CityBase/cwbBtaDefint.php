<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaDefint() {
    $cwbBtaDefint = new cwbBtaDefint();
    $cwbBtaDefint->parseEvent();
    return;
}

class cwbBtaDefint extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaDefint';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 6;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['DEFINT'] != '') {
            $this->gridFilters['DEFINT'] = $this->formData['DEFINT'];
        }
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_DEFINT[DEFINT]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_DEFINT[DEFINT]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_DEFINT[DEFINT]');
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DEFINT');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DEFINT');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_DEFINT[DEFINT]');
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_DEFINT[DEFINT]', 'readonly');
        Out::css($this->nameForm . '_BTA_DEFINT[DEFINT]', 'background-color', '#FFFFE0');
        Out::setFocus('', $this->nameForm . '_BTA_DEFINT[DEFINT]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['DEFINT'] = trim($this->formData[$this->nameForm . '_DEFINT']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaDefint($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaDefintChiave($index, $sqlParams);
    }

}

?>