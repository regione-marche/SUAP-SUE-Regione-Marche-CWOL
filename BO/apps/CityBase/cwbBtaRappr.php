<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBtaRappr() {
    $cwbBtaRappr = new cwbBtaRappr();
    $cwbBtaRappr->parseEvent();
    return;
}

class cwbBtaRappr extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaRappr';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 2;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DESNAZI_decod_butt':
                        $this->decodNazion($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZI'], ($this->nameForm . '_BTA_RAPPR[CODNAZI]'), $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['DESNAZI_decod'], ($this->nameForm . '_DESNAZI_decod'), true);
                        break;
                }
                break;
            case 'returnFromBtaNazion':
                switch ($this->elementId) {
                    case $this->nameForm . '_DESNAZI_decod_butt':
                    case $this->nameForm . '_DESNAZI_decod':
                    case $this->nameForm . '_BTA_RAPPR[CODNAZI]':
                        Out::valore($this->nameForm . '_BTA_RAPPR[CODNAZI]', $this->formData['returnData']['CODNAZI']);
                        Out::valore($this->nameForm . '_DESNAZI_decod', $this->formData['returnData']['DESNAZI']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_RAPPR[CODNAZI]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZI'], $this->nameForm . '_BTA_RAPPR[CODNAZI]', $this->nameForm . '_DESNAZI_decod')) {
                            $this->decodNazion($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZI'], ($this->nameForm . '_BTA_RAPPR[CODNAZI]'), $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['DESNAZI_decod'], ($this->nameForm . '_DESNAZI_decod'));
                        } else {
                            Out::valore($this->nameForm . '_DESNAZI_decod', '');
                        }
                        break;
                    case $this->nameForm . '_DESNAZI_decod':
                        $this->decodNazion(null, ($this->nameForm . '_BTA_RAPPR[CODNAZI]'), $_POST[$this->nameForm . '_DESNAZI_decod'], ($this->nameForm . '_DESNAZI_decod'));

                        break;
                    case $this->nameForm . '_BTA_RAPPR[CODCONSOL]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODCONSOL'], $this->nameForm . '_BTA_RAPPR[CODCONSOL]');
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
        $progr = cwbLibCalcoli::trovaProgressivo("CODCONSOL", "BTA_RAPPR");
        Out::valore($this->nameForm . '_BTA_RAPPR[CODCONSOL]', $progr);
        Out::setFocus("", $this->nameForm . '_BTA_RAPPR[DESCONSOL]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_RAPPR[DESCONSOL]');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        $this->decodNazion($this->CURRENT_RECORD['CODNAZI'], ($this->nameForm . '_BTA_RAPPR[CODNAZI]'), null, ($this->nameForm . '_DESNAZI_decod'));

        Out::setFocus('', $this->nameForm . '_BTA_RAPPR[DESCONSOL]');
        Out::valore($this->nameForm . '_BTA_RAPPR[DESCONSOL]', trim($this->CURRENT_RECORD['DESCONSOL']));
        Out::valore($this->nameForm . '_BTA_RAPPR[INDIRCON1]', trim($this->CURRENT_RECORD['INDIRCON1']));
        Out::valore($this->nameForm . '_BTA_RAPPR[INDIRCON2]', trim($this->CURRENT_RECORD['INDIRCON2']));
        Out::valore($this->nameForm . '_BTA_RAPPR[DESLOCAL]', trim($this->CURRENT_RECORD['DESLOCAL']));
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['CODCONSOL'] = trim($this->formData[$this->nameForm . '_CODCONSOL']);
        $filtri['DESLOCAL'] = trim($this->formData[$this->nameForm . '_DESLOCAL']);
        $filtri['DESNAZI'] = trim($this->formData[$this->nameForm . '_DESNAZI']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaRappr($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaRapprChiave($index, $sqlParams);
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_DESNAZI_decod', '');
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

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DESNAZI');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DESNAZI');
    }

    private function decodNazion($codValue, $codField, $desValue, $desField, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaNazion", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "CODNAZI", $desValue, $desField, "DESNAZI", 'returnFromBtaNazion', $_POST['id'], $searchButton);
    }

}

