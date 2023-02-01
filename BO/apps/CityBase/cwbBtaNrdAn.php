<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibDB_FTA.class.php';

function cwbBtaNrdAn() {
    $cwbBtaNrdAn = new cwbBtaNrdAn();
    $cwbBtaNrdAn->parseEvent();
    return;
}

class cwbBtaNrdAn extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaNrdAn';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 26;
        $this->setTABLE_VIEW("BTA_NRD_AN_V01");
        $this->libDB = new cwbLibDB_BTA();
        $this->libDB_FTA = new cwfLibDB_FTA();
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_NRD_AN[COD_NR_D]':
                        if ($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['COD_NR_D']) {
                            $this->decodBtaNrd($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['COD_NR_D'], ($this->nameForm . '_BGE_TESTI[COD_NR_D]'), null, ($this->nameForm . '_DES_NR_D_decod'));
                        } else {
                            Out::valore($this->nameForm . '_DES_NR_D_decod', '');
                            
                        }
                        break;
                    case $this->nameForm . '_DES_NR_D_decod':
                        $this->decodBtaNrd($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['COD_NR_D'], ($this->nameForm . '_BGE_TESTI[COD_NR_D]'), $this->formData[$this->nameForm . '_DES_NR_D_decod'], ($this->nameForm . '_DES_NR_D_decod'));
                        break;

                    case $this->nameForm . '_BTA_NRD_AN[SETT_IVA]':
                        if (cwbLibCheckInput::checkNumeric($this->formData[$this->nameForm . '_' . $this->TABLE_NAME]['SETT_IVA'], $this->nameForm . '_BTA_NRD_AN[SETT_IVA]', $this->nameForm . '_DES_SETIVA')) {
                            $this->decodSetiva($this->formData[$this->nameForm . '_' . $this->TABLE_NAME]['SETT_IVA'], $this->nameForm . '_BTA_NRD_AN[SETT_IVA]', $this->formData[$this->nameForm . '_DES_SETIVA_decod'], ($this->nameForm . '_DES_SETIVA_decod'), FALSE);
                        } else {
                            Out::valore($this->nameForm . '_DES_SETIVA_decod', '');
                        };
                        break;
                    case $this->nameForm . '_DES_SETIVA_decod':
                        $this->decodSetiva(null, ($this->nameForm . '_BTA_NRD_AN[SETT_IVA]'), $this->formData[$this->nameForm . '_DES_SETIVA_decod'], ($this->nameForm . '_DES_SETIVA_decod'));
                        break;
                    case $this->nameForm . '_BTA_NRD_AN[ANNOEMI]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['ANNOEMI'], $this->nameForm . '_BTA_NRD_AN[ANNOEMI]');
                        break;
                    case $this->nameForm . '_BTA_NRD_AN[NUMULTDOC]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['NUMULTDOC'], $this->nameForm . '_BTA_NRD_AN[NUMULTDOC]');
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DES_NR_D_decod_butt':
                        $this->decodBtaNrd($this->formData[$this->nameForm . '_' . $this->TABLE_NAME]['COD_NR_D'], ($this->nameForm . '_BGE_TESTI[COD_NR_D]'), $this->formData[$this->nameForm . '_DES_NR_D_decod'], ($this->nameForm . '_DES_NR_D_decod'), true);
                        break;
                    case $this->nameForm . '_DES_SETIVA_decod_butt':
                        $this->decodSetiva($this->formData[$this->nameForm . '_BTA_NRD_AN[SETT_IVA]'], ($this->nameForm . '_BTA_NRD_AN[SETT_IVA]'), $this->formData[$this->nameForm . '_DES_SETIVA_decod'], ($this->nameForm . '_DES_SETIVA_decod'), true);
                        break;
                    case $this->nameForm . '_ApriAnno':
                        cwbLib::apriFinestraRicerca('cwbBtaNrdAnno', $this->nameForm, 'returnFromBtaNrdAnno', $_POST['id'], true);
                        break;
                }
                break;
            case 'returnFromBtaNrd':
                switch ($this->elementId) {
                    case $this->nameForm . '_DES_NR_D_decod_butt':
                    case $this->nameForm . '_DES_NR_D_decod':
                        Out::valore($this->nameForm . '_BTA_NRD_AN[COD_NR_D]', $this->formData['returnData']['COD_NR_D']);
                        Out::valore($this->nameForm . '_DES_NR_D_decod', $this->formData['returnData']['DES_NR_D']);
                        break;
                }
                break;
            case 'returnFromFtaSetiva':
                switch ($this->elementId) {
                    case $this->nameForm . '_DES_SETIVA_decod_butt':
                    case $this->nameForm . '_DES_SETIVA_decod':
                        Out::valore($this->nameForm . '_BTA_NRD_AN[SETT_IVA]', $this->formData['returnData']['SETT_IVA']);
                        Out::valore($this->nameForm . '_DES_SETIVA_decod', $this->formData['returnData']['DES_SETIVA']);
                        break;
                }
                break;
        }
    }

    protected function postNuovo() {
        Out::hide($this->nameForm . '_ApriAnno');
        Out::attributo($this->nameForm . '_BTA_NRD_AN[COD_NR_D]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_NRD_AN[COD_NR_D]', 'background-color', '#FFFFFF');
        Out::attributo($this->nameForm . '_BTA_NRD_AN[ANNOEMI]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_NRD_AN[ANNOEMI]', 'background-color', '#FFFFFF');
        Out::attributo($this->nameForm . '_BTA_NRD_AN[SETT_IVA]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_NRD_AN[SETT_IVA]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_NRD_AN[COD_NR_D]');
        Out::show($this->nameForm . '_BTA_NRD_AN[SETT_IVA]_butt');
        Out::show($this->nameForm . '_BTA_NRD_AN[COD_NR_D]_butt');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_DES_NR_D_decod', '');
        Out::valore($this->nameForm . '_DES_SETIVA_decod', '');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_NRD_AN[COD_NR_D]');
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DES_NR_D');
    }

    protected function postAltraRicerca() {
        Out::hide($this->nameForm . '_ApriAnno');
        Out::setFocus("", $this->nameForm . '_DES_NR_D');
    }

    protected function postDettaglio($index) {
        Out::show($this->nameForm . '_ApriAnno');
        Out::attributo($this->nameForm . '_BTA_NRD_AN[COD_NR_D]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_NRD_AN[COD_NR_D]', 'background-color', '#FFFFE0');
        Out::attributo($this->nameForm . '_BTA_NRD_AN[ANNOEMI]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_NRD_AN[ANNOEMI]', 'background-color', '#FFFFE0');
        Out::attributo($this->nameForm . '_BTA_NRD_AN[SETT_IVA]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_NRD_AN[SETT_IVA]', 'background-color', '#FFFFE0');
        Out::hide($this->nameForm . '_BTA_NRD_AN[SETT_IVA]_butt');
        Out::hide($this->nameForm . '_BTA_NRD_AN[COD_NR_D]_butt');
        $this->decodBtaNrd($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['COD_NR_D'], ($this->nameForm . '_BTA_NRD_AN[COD_NR_D]'), ($this->nameForm . '_DES_NR_D_decod'));
        $this->decodSetiva($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['SETT_IVA'], ($this->nameForm . '_BTA_NRD_AN[SETT_IVA]'), ($this->nameForm . '_DES_SETIVA_decod'));
        Out::setFocus('', $this->nameForm . '_BTA_NRD_AN[NUMDOCBARA]');
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['ANNOEMI'] != '') {
            $this->gridFilters['ANNOEMI'] = $this->formData['ANNOEMI'];
        }
        if ($_POST['DES_NR_D'] != '') {
            $this->gridFilters['DES_NR_D'] = $this->formData['DES_NR_D'];
        }
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        Out::show($this->nameForm . '_ApriAnno');
        $filtri['ANNOEMI'] = trim($this->formData[$this->nameForm . '_ANNOEMI']);
        $filtri['DES_NR_D'] = trim($this->formData[$this->nameForm . '_DES_NR_D']);
        $filtri['COD_NR_D'] = trim($this->formData[$this->nameForm . '_COD_NR_D']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaNrdAn($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        list($ANNOEMI, $COD_NR_D, $SETT_IVA) = explode('|', $index);
        $this->SQL = $this->libDB->getSqlLeggiBtaNrdAnChiave($ANNOEMI, $COD_NR_D, $SETT_IVA, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            //Area competenza
            switch ($Result_tab[$key]['AREA_COMP']) {
                case 0:
                    $Result_tab[$key]['AREA_COMP'] = 'Generale';
                    break;
                case 1:
                    $Result_tab[$key]['AREA_COMP'] = 'Serv.Economici';
                    break;
                case 2:
                    $Result_tab[$key]['AREA_COMP'] = 'Tributi';
                    break;
                case 3:
                    $Result_tab[$key]['AREA_COMP'] = 'ICI';
                    break;
                case 4:
                    $Result_tab[$key]['AREA_COMP'] = 'Serv.Dom.Individ.';
                    break;
                case 5:
                    $Result_tab[$key]['AREA_COMP'] = 'Serv.Demografici';
                    break;
                case 6:
                    $Result_tab[$key]['AREA_COMP'] = 'Delibere/Determine';
                    break;
            }
            $Result_tab[$key]['COD_NR_D_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['COD_NR_D']);
        }
        return $Result_tab;
    }

    private function decodBtaNrd($codValue, $codField, $desValue, $desField, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaNrd", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "COD_NR_D", $desValue, $desField, "DES_NR_D", 'returnFromBtaNrd', $_POST['id'], $searchButton);
    }

    private function decodSetiva($codValue, $codField, $desValue, $desField, $searchButton = false) {
        cwbLib::decodificaLookup("cwfFtaSetiva", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "SETT_IVA", $desValue, $desField, "DES_SETIVA", 'returnFromFtaSetiva', $_POST['id'], $searchButton);
    }

}

?>