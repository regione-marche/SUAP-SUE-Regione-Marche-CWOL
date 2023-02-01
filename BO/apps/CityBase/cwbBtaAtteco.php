<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaAtteco() {
    $cwbBtaAtteco = new cwbBtaAtteco();
    $cwbBtaAtteco->parseEvent();
    return;
}

class cwbBtaAtteco extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaAtteco';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 24;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODATTIVEC_formatted'] != '') {
            $this->gridFilters['CODATTIVEC'] = $this->formData['CODATTIVEC_formatted'];
        }
        if ($_POST['GRUP_ATTEC'] != '') {
            $this->gridFilters['GRUP_ATTEC'] = $this->formData['GRUP_ATTEC'];
        }
        if ($_POST['CODATTIVIS'] != '') {
            $this->gridFilters['CODATTIVIS'] = $this->formData['CODATTIVIS'];
        }
        if ($_POST['DESATTECIV'] != '') {
            $this->gridFilters['DESATTECIV'] = $this->formData['DESATTECIV'];
        }
        if ($_POST['DESATTECIS'] != '') {
            $this->gridFilters['DESATTECIS'] = $this->formData['DESATTECIS'];
        }
    }

    protected function postApriForm() {
        $this->initComboGestione();
        Out::setFocus("", $this->nameForm . '_DESATTECIV');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DESATTECIV');
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_ATTECO[CODATTIVEC]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_ATTECO[CODATTIVEC]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_ATTECO[CODATTIVEC]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_ATTECO[CODATTIVEC]');
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_ATTECO[CODATTIVEC]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_BTA_ATTECO[DESATTECIV]');
        // toglie gli spazi del char
        Out::css($this->nameForm . '_BTA_ATTECO[CODATTIVEC]', 'background-color', '#FFFFE0');
        Out::valore($this->nameForm . '_BTA_ATTECO[DESATTECIV]', trim($this->CURRENT_RECORD['DESATTECIV']));
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['CODATTIVEC'] = trim($this->formData[$this->nameForm . '_CODATTIVEC']);
        $filtri['DESATTECIV'] = trim($this->formData[$this->nameForm . '_DESATTECIV']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaAtteco($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaAttecoChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CODATTIVEC_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CODATTIVEC']);
        }
        return $Result_tab;
    }

    private function initComboGestione() {
        for ($i = 1; $i <= 4; $i++) {
            $this->popolaComboSettore($i);
        }
    }

    private function popolaComboSettore($numeroSettore) {
        for ($i = 0; $i <= 9; $i++) {
            Out::select($this->nameForm . '_BTA_ATTECO[SET' . $numeroSettore . '_ATTEC]', 1, "0$i", ($i == 0 ? 1 : 0), "0$i");
        }
    }

}

