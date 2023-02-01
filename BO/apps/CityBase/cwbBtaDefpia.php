<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaDefpia() {
    $cwbBtaDefpia = new cwbBtaDefpia();
    $cwbBtaDefpia->parseEvent();
    return;
}

class cwbBtaDefpia extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaDefpia';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 6;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['DEFPIA'] != '') {
            $this->gridFilters['DEFPIA'] = $this->formData['DEFPIA'];
        }
        if ($_POST['DESPIAN'] != '') {
            $this->gridFilters['DESPIAN'] = $this->formData['DESPIAN'];
        }
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_DEFPIA[DEFPIA]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_DEFPIA[DEFPIA]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_DEFPIA[DEFPIA]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_DEFPIA[DEFPIA]');
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_DEFPIA[DEFPIA]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_DEFPIA[DEFPIA]', 'background-color', '#FFFFE0');
        Out::setFocus('', $this->nameForm . '_BTA_DEFPIA[DESPIAN]');
        Out::valore($this->nameForm . '_BTA_DEFPIA[DESPIAN]', trim($this->CURRENT_RECORD['DESPIAN']));
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DEFPIA');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DEFPIA');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['DEFPIA'] = trim($this->formData[$this->nameForm . '_DEFPIA']);
        $filtri['DESPIAN'] = trim($this->formData[$this->nameForm . '_DESPIAN']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaDefpia($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaDefpiaChiave($index, $sqlParams);
    }

}

?>