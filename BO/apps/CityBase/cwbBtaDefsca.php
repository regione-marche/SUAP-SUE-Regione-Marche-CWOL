<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaDefsca() {
    $cwbBtaDefsca = new cwbBtaDefsca();
    $cwbBtaDefsca->parseEvent();
    return;
}

class cwbBtaDefsca extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaDefsca';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 6;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['DEFSCALA'] != '') {
            $this->gridFilters['DEFSCALA'] = $this->formData['DEFSCALA'];
        }
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_DEFSCA[DEFSCALA]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_DEFSCA[DEFSCALA]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_DEFSCA[DEFSCALA]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_DEFSCA[DEFSCALA]');
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DEFSCALA');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DEFSCALA');
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_DEFSCA[DEFSCALA]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_DEFSCA[DEFSCALA]', 'background-color', '#FFFFE0');
        Out::setFocus('', $this->nameForm . '_BTA_DEFSCA[DEFSCALA]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['DEFSCALA'] = trim($this->formData[$this->nameForm . '_DEFSCALA']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaDefsca($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaDefscaChiave($index, $sqlParams);
    }

}

?>