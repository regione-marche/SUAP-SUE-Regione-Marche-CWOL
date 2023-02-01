<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';

function cwbBtaRendAcc() {
    $cwbBtaRendAcc = new cwbBtaRendAcc();
    $cwbBtaRendAcc->parseEvent();
    return;
}

class cwbBtaRendAcc extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaRendAcc';
//        $this->AUTOR_MODULO = 'BTA';
//        $this->AUTOR_NUMERO = 3;
        $this->skipAuth = true;
        $this->searchOpenElenco = true;
    }

    protected function setGridFilters() {
        $this->gridFilters = array();

        if ($_POST['DES_IMP'] != '') {
            $this->gridFilters['DES_IMP'] = $this->formData['DES_IMP'];
        }
        if ($_POST['PROGIMPACC'] != '') {
            $this->gridFilters['PROGIMPACC'] = $this->formData['PROGIMPACC'];
        }
        if ($_POST['ANNORIF'] != '') {
            $this->gridFilters['ANNORIF'] = $this->formData['ANNORIF'];
        }
    }

    protected function preNuovo() {
        Out::hide($this->nameForm . "_divId");
    }

    protected function postNuovo() {
        Out::valore($this->nameForm . '_' . $this->TABLE_NAME . '[ANNORIF]', date("Y"));
    }

    protected function postDettaglio($index) {
        Out::show($this->nameForm . "_divIdFlagdis");
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        Out::show($this->nameForm . '_Enti');
        $filtri['PROGIMPACC'] = trim($this->formData[$this->nameForm . '_PROGIMPACC']);
        $filtri['ANNORIF'] = trim($this->formData[$this->nameForm . '_ANNORIF']);
        $filtri['DES_IMP'] = trim($this->formData[$this->nameForm . '_DES_IMP']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaRendAcc($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBtaRendAccChiave($index, $sqlParams);
    }

}

