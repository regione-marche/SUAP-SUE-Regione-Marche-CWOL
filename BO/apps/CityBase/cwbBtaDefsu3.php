<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaDefsu3() {
    $cwbBtaDefsu3 = new cwbBtaDefsu3();
    $cwbBtaDefsu3->parseEvent();
    return;
}

class cwbBtaDefsu3 extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaDefsu3';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 6;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DEFSUBN');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DEFSUBN');
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_DEFSU3[DEFSUBN]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_DEFSU3[DEFSUBN]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_DEFSU3[DEFSUBN]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_DEFSU3[DEFSUBN]');
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_DEFSU3[DEFSUBN]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_DEFSU3[DEFSUBN]', 'background-color', '#FFFFE0');
        Out::setFocus('', $this->nameForm . '_BTA_DEFSU3[DEFSUBN]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['DEFSUBN'] = trim($this->formData[$this->nameForm . '_DEFSUBN']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaDefsu3($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaDefsu3Chiave($index, $sqlParams);
    }

}

?>