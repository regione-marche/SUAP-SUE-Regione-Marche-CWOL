<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

function cwbBorModuli() {
    $cwbBorModuli = new cwbBorModuli();
    $cwbBorModuli->parseEvent();
    return;
}

class cwbBorModuli extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBorModuli';
        $this->libDB = new cwbLibDB_BOR();
        $this->setTABLE_VIEW("BOR_MODULI_V01");
        $this->elencaAutoFlagDis = true;
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DESAREA_2_decod_butt':
                        $this->decodArea($this->formData[$this->nameForm . '_CODAREAMA'], ($this->nameForm . '_CODAREAMA'), $this->formData[$this->nameForm . '_DESAREA_2_decod'], ($this->nameForm . '_DESAREA_2_decod'), true);
                        break;
                }
                break;
            case 'returnFromBorMaster':
                switch ($this->elementId) {
                    case $this->nameForm . '_DESAREA_2_decod_butt':
                    case $this->nameForm . '_CODAREAMA':
                    case $this->nameForm . '_DESAREA_2_decod':
                        Out::valore($this->nameForm . '_CODAREAMA', $this->formData['returnData']['CODAREAMA']);
                        Out::valore($this->nameForm . '_DESAREA_2_decod', $this->formData['returnData']['DESAREA']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CODAREAMA':
                        $this->decodArea($this->formData[$this->nameForm . '_CODAREAMA'], ($this->nameForm . '_CODAREAMA'), null, ($this->nameForm . '_DESAREA_2_decod'));
                        break;
                    case $this->nameForm . '_DESAREA_2_decod':
                        $this->decodArea(null, ($this->nameForm . '_CODAREAMA'), $this->formData[$this->nameForm . '_DESAREA_2_decod'], ($this->nameForm . '_DESAREA_2_decod'));
                        break;
                }
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODMODULO_formatted'] != '') {
            $this->gridFilters['CODMODULO'] = $this->formData['CODMODULO_formatted'];
        }
        if ($_POST['CODAREAMA'] != '') {
            $this->gridFilters['CODAREAMA'] = $this->formData['CODAREAMA'];
        }
        if ($_POST['DESAREA'] != '') {
            $this->gridFilters['DESAREA'] = $this->formData['DESAREA'];
        }
        if ($_POST['DESMODULO'] != '') {
            $this->gridFilters['DESMODULO'] = $this->formData['DESMODULO'];
        }
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BOR_MODULI[CODMODULO]', 'readonly', '1');
        Out::setFocus("", $this->nameForm . '_BOR_MODULI[CODMODULO]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BOR_MODULI[CODMODULO]');
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DESMODULO');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DESMODULO');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
//        $this->decodArea($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODAREAMA'], ($this->nameForm . '_BOR_MODULI[CODAREAMA]'), ($this->nameForm . '_DESAREA_decod'));
        Out::attributo($this->nameForm . '_BOR_MODULI[CODMODULO]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_BOR_MODULI[DESMODULO]');
        // toglie gli spazi del char
        Out::valore($this->nameForm . '_BOR_MODULI[DESMODULO]', trim($this->CURRENT_RECORD['DESMODULO']));
    }

    private function decodArea($codValue, $codField, $desValue, $desField, $search = false) {
        cwbLib::decodificaLookup("cwbBorMaster", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "CODAREAMA", $desValue, $desField, "DESAREA", "returnFromBorMaster", $_POST['id'], $search);
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        if ($this->masterRecord != null) {
            $filtri['CODAREAMA'] = $this->masterRecord['CODAREAMA'];
            Out::disableContainerFields($this->nameForm . "_CODAREAMA_field");
            $this->decodArea($this->masterRecord['CODAREAMA'], $this->nameForm . "_CODAREAMA",null, $this->nameForm . "_DESAREA_2_decod");
        } else {
            $filtri['CODMODULO'] = trim($this->formData[$this->nameForm . '_CODMODULO']);
            $filtri['CODAREAMA'] = trim($this->formData[$this->nameForm . '_CODAREAMA']);
            $filtri['DESMODULO'] = trim($this->formData[$this->nameForm . '_DESMODULO']);
            $filtri['FLAG_DIS'] = trim($this->formData[$this->nameForm . '_FLAG_DIS']);
        }
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBorModuli($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBorModuliChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CODMODULO_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CODMODULO']);
        }
        return $Result_tab;
    }

}

