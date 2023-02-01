<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaDefsu1() {
    $cwbBtaDefsu1 = new cwbBtaDefsu1();
    $cwbBtaDefsu1->parseEvent();
    return;
}

class cwbBtaDefsu1 extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaDefsu1';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 6;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['DEFSUBN'] != '') {
            $this->gridFilters['DEFSUBN'] = $this->formData['DEFSUBN'];
        }
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_DEFSU1[DEFSUBN]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_DEFSU1[DEFSUBN]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_DEFSU1[DEFSUBN]');
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DEFSUBN');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DEFSUBN');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_DEFSU1[DEFSUBN]');
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_DEFSU1[DEFSUBN]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_DEFSU1[DEFSUBN]', 'background-color', '#FFFFE0');
        Out::setFocus('', $this->nameForm . '_BTA_DEFSU1[DEFSUBN]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['DEFSUBN'] = trim($this->formData[$this->nameForm . '_DEFSUBN']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaDefsu1($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaDefsu1Chiave($index, $sqlParams);
    }

}

?>