<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaDefsu2() {
    $cwbBtaDefsu2 = new cwbBtaDefsu2();
    $cwbBtaDefsu2->parseEvent();
    return;
}

class cwbBtaDefsu2 extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaDefsu2';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 6;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_DEFSU2[DEFSUBN]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_DEFSU2[DEFSUBN]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_DEFSU2[DEFSUBN]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_DEFSU2[DEFSUBN]');
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DEFSUBN');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DEFSUBN');
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_DEFSU2[DEFSUBN]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_DEFSU2[DEFSUBN]', 'background-color', '#FFFFE0');
        Out::setFocus('', $this->nameForm . '_BTA_DEFSU2[DEFSUBN]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['DEFSUBN'] = trim($this->formData[$this->nameForm . '_DEFSUBN']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaDefsu2($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaDefsu2Chiave($index, $sqlParams);
    }

}

?>