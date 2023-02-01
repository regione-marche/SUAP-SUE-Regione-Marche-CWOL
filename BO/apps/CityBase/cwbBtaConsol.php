<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaConsol() {
    $cwbBtaConsol = new cwbBtaConsol();
    $cwbBtaConsol->parseEvent();
    return;
}

class cwbBtaConsol extends cwbBpaGenTab {

    protected function initVars() {
        $this->GRID_NAME = 'gridBtaConsol';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 2;
        $this->libDB = new cwbLibDB_BTA();
        $this->setTABLE_VIEW("BTA_CONSOL_V01");
        $this->errorOnEmpty = false;
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DESNAZI_decod_butt':
                        $this->decodNaz($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZI'], $this->nameForm . '_' . $this->TABLE_NAME . '[CODNAZI]', $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['DESNAZI'], $this->nameForm . '_' . $this->TABLE_NAME . '[DESNAZI]', true);
                        break;
                }
                break;
            case 'returnFromBtaNazion':
                switch ($this->elementId) {
                    case $this->nameForm . '_DESNAZI_decod_butt':
                    case $this->nameForm . '_DESNAZI_decod':
                    case $this->nameForm . '_BTA_CONSOL[CODNAZI]':
                        Out::valore($this->nameForm . '_BTA_CONSOL[CODNAZI]', $this->formData['returnData']['CODNAZI']);
                        Out::valore($this->nameForm . '_DESNAZI_decod', $this->formData['returnData']['DESNAZI']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_CONSOL[CODNAZI]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZI'], $this->nameForm . '_BTA_CONSOL[CODNAZI]', $this->nameForm . '_DESNAZI_decod')) {
                            $this->decodNaz($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZI'], $this->nameForm . '_' . $this->TABLE_NAME . '[CODNAZI]', null, $this->nameForm . '_DESNAZI_decod', false);
                        } else {
                            Out::valore($this->nameForm . '_DESNAZI_decod', '');
                        }
                        break;
                    case $this->nameForm . '_DESNAZI_decod':
                        $this->decodNaz(null, $this->nameForm . '_' . $this->TABLE_NAME . '[CODNAZI]', $_POST[$this->nameForm . '_DESNAZI_decod'], $this->nameForm . '_DESNAZI_decod', false);

                        break;
                    case $this->nameForm . '_CODCONSOL':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODCONSOL'], $this->nameForm . '_CODCONSOL');
                        break;
                    case $this->nameForm . '_BTA_CONSOL[CODCONSOL]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODCONSOL'], $this->nameForm . '_BTA_CONSOL[CODCONSOL]');
                        break;
                }
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODCONSOL_formatted'] != '') {
            $this->gridFilters['CODCONSOL'] = $this->formData['CODCONSOL_formatted'];
        }
        if ($_POST['DESCONSOL'] != '') {
            $this->gridFilters['DESCONSOL'] = $this->formData['DESCONSOL'];
        }
        if ($_POST['DESLOCAL'] != '') {
            $this->gridFilters['DESLOCAL'] = $this->formData['DESLOCAL'];
        }
        if ($_POST['DESNAZI'] != '') {
            $this->gridFilters['DESNAZI'] = $this->formData['DESNAZI'];
        }
        if ($_POST['INDIRCON1'] != '') {
            $this->gridFilters['INDIRCON1'] = $this->formData['INDIRCON1'];
        }
        if ($_POST['DATAINIZ'] != '') {
            $this->gridFilters['DATAINIZ'] = $this->formData['DATAINIZ'];
        }
        if ($_POST['DATAFINE'] != '') {
            $this->gridFilters['DATAFINE'] = $this->formData['DATAFINE'];
        }
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_CONSOL[CODCONSOL]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_CONSOL[CODCONSOL]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_CONSOL[CODCONSOL]');
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DESLOCAL');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DESLOCAL');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_CONSOL[CODCONSOL]');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_DESNAZI_decod', '');
    }

    protected function postDettaglio($index) {
        Out::valore($this->nameForm . '_BTA_CONSOL[DESCONSOL]', trim($this->CURRENT_RECORD['DESCONSOL']));
        Out::valore($this->nameForm . '_BTA_CONSOL[INDIRCON1]', trim($this->CURRENT_RECORD['INDIRCON1']));
        Out::valore($this->nameForm . '_BTA_CONSOL[INDIRCON2]', trim($this->CURRENT_RECORD['INDIRCON2']));
        Out::valore($this->nameForm . '_BTA_CONSOL[DESLOCAL]', trim($this->CURRENT_RECORD['DESLOCAL']));
        $this->decodNaz($this->CURRENT_RECORD['CODNAZI'], $this->nameForm . '_' . $this->TABLE_NAME . '[CODNAZI]', null, $this->nameForm . '_DESNAZI_decod', false);
        Out::attributo($this->nameForm . '_BTA_CONSOL[CODCONSOL]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_CONSOL[CODCONSOL]', 'background-color', '#FFFFE0');
        Out::setFocus('', $this->nameForm . '_BTA_CONSOL[DESCONSOL]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['CODCONSOL'] = trim($this->formData[$this->nameForm . '_CODCONSOL']);
        $filtri['DESCONSOL'] = trim($this->formData[$this->nameForm . '_DESCONSOL']);
        $filtri['DESNAZI'] = trim($this->formData[$this->nameForm . '_DESNAZI']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaConsol($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaConsolChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CODCONSOL_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CODCONSOL']);

            $fields = array();
            $fields[] = $Result_tab[$key]['INDIRCON1'];
            $fields[] = $Result_tab[$key]['INDIRCON2'];
            $Result_tab[$key]['INDIRCON1'] = cwbLibHtml::formatDataGridConcat($fields);
        }
        return $Result_tab;
    }

    private function decodNaz($codValue, $codField, $desValue, $desField, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaNazion", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "CODNAZI", $desValue, $desField, "DESNAZI", 'returnFromBtaNazion', $_POST['id'], $searchButton);
    }

}
