<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaAteco7() {
    $cwbBtaAteco7 = new cwbBtaAteco7();
    $cwbBtaAteco7->parseEvent();
    return;
}

class cwbBtaAteco7 extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaAteco7';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 24;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODATECO7_formatted'] != '') {
            $this->gridFilters['CODATECO7'] = $this->formData['CODATECO7_formatted'];
        }
        if ($_POST['CODATTIVEC'] != '') {
            $this->gridFilters['CODATTIVEC'] = $this->formData['CODATTIVEC'];
        }
        if ($_POST['SETATECO'] != '') {
            $this->gridFilters['SETATECO'] = $this->formData['SETATECO'];
        }
        if ($_POST['DESATTECIV'] != '') {
            $this->gridFilters['DESATTECIV'] = $this->formData['DESATTECIV'];
        }
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CODATTIVEC_decod_butt':
                        $this->decodCodice($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODATTIVEC'], ($this->nameForm . '_BTA_ATECO7[CODATTIVEC]'), $_POST[$this->nameForm . 'DESATTIVEC_decod'], ($this->nameForm . 'DESATTIVEC_decod'), true);
                        break;
                }
                break;
            case 'returnFromBtaAtteco':
                switch ($this->elementId) {
                    case $this->nameForm . '_CODATTIVEC_decod':
                    case $this->nameForm . '_CODATTIVEC_decod_butt':
                        Out::valore($this->nameForm . '_BTA_ATECO7[CODATTIVEC]', $this->formData['returnData']['CODATTIVEC']);
                        Out::valore($this->nameForm . '_CODATTIVEC_decod', $this->formData['returnData']['DESATTECIV']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_ATECO7[CODATTIVEC]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODATTIVEC'], $this->nameForm . '_BTA_ATECO7[CODATTIVEC]', $this->nameForm . '_CODATTIVEC_decod')) {
                            $this->decodCodice($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODATTIVEC'], ($this->nameForm . '_BTA_ATECO7[CODATTIVEC]'), NULL,($this->nameForm . '_CODATTIVEC_decod'));
                        } else {
                            Out::valore($this->nameForm . '_CODATTIVEC_decod');
                        }
                        break;
                    case $this->nameForm . '_CODATTIVEC_decod':
                          $this->decodCodice(null, ($this->nameForm . '_BTA_ATECO7[CODATTIVEC]'), $_POST[$this->nameForm . '_CODATTIVEC_decod'],($this->nameForm . '_CODATTIVEC_decod'));
                        break;
                    case $this->nameForm . '_CODATECO7':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODATECO7'], $this->nameForm . '_CODATECO7');
                        break;
                }
                break;
        }
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_ATECO7[CODATECO7]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_ATECO7[CODATECO7]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_ATECO7[CODATECO7]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_ATECO7[CODATECO7]');
    }

    protected function preDettaglio($index) {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_ATECO7[CODATECO7]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_ATECO7[CODATECO7]', 'background-color', '#FFFFE0');
        Out::setFocus('', $this->nameForm . '_BTA_ATECO7[DESATTECIV]');
        // toglie gli spazi del char
        Out::valore($this->nameForm . '_BTA_ATECO7[DESATTECIV]', trim($this->CURRENT_RECORD['DESATTECIV']));
        $this->decodCodice($this->CURRENT_RECORD['CODATTIVEC'], ($this->nameForm . '_BTA_ATECO7[CODATTIVEC]'), ($this->nameForm . '_CODATTIVEC_decod'));
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_CODATTIVEC_decod', '');
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DESATTECIV');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DESATTECIV');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['CODATECO7'] = trim($this->formData[$this->nameForm . '_CODATECO7']);
        $filtri['DESATTECIV'] = trim($this->formData[$this->nameForm . '_DESATTECIV']);
        $filtri['FLAG_DIS'] = trim($this->formData[$this->nameForm . '_FLAG_DIS']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaAteco7($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaAteco7Chiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CODATECO7_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CODATECO7']);
        }
        return $Result_tab;
    }

    private function decodCodice($codValue, $codField, $desValue, $desField, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaAtteco", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "CODATTIVEC", $desValue, $desField, "DESATTECIV", 'returnFromBtaAtteco', $_POST['id'], $searchButton);
    }

}

