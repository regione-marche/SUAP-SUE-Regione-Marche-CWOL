<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';

function cwbBtaNazion() {
    $cwbBtaNazion = new cwbBtaNazion();
    $cwbBtaNazion->parseEvent();
    return;
}

class cwbBtaNazion extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaNazion';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 1;
        $this->libDB = new cwbLibDB_BTA();
        $this->errorOnEmpty = false;
        $this->elencaAutoAudit = true;
    }

    protected function initPrintStrategy() {
        $this->printStrategy = cwbBpaGenHelper::PRINT_STRATEGY_JASPER;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    //dettaglio gruppo nazionalita
                    case $this->nameForm . '_DESGRNAZ_decod_butt':
                        $this->decodGruppo($this->formData[$this->nameForm . '_BTA_NAZION[CODGRNAZ]'], ($this->nameForm . '_BTA_NAZION[CODGRNAZ]'), $this->formData[$this->nameForm . '_DESGRNAZ_decod'], ($this->nameForm . '_DESGRNAZ_decod'), true);
                        break;
                    //Ricerca gruppo nazionalita
                    case $this->nameForm . '_DESGRNAZ_butt':
                        $this->decodGruppo($this->formData[$this->nameForm . '_CODGRNAZ'], $this->nameForm . '_CODGRNAZ', $this->formData[$this->nameForm . '_DESGRNAZ'], ($this->nameForm . '_DESGRNAZ'), true);
                        break;
                }
                break;
            case 'returnFromBtaGrunaz':
                switch ($this->elementId) {
                    //Ricerca gruppo nazionalita
                    case $this->nameForm . '_DESGRNAZ_butt':
                    case $this->nameForm . '_DESGRNAZ':
                        Out::valore($this->nameForm . '_CODGRNAZ', $this->formData['returnData']['CODGRNAZ']);
                        Out::valore($this->nameForm . '_DESGRNAZ', $this->formData['returnData']['DESGRNAZ']);
                        break;
                    //dettaglio gruppo nazionalita
                    case $this->nameForm . '_DESGRNAZ_decod_butt':
                    case $this->nameForm . '_DESGRNAZ_decod':
                        Out::valore($this->nameForm . '_BTA_NAZION[CODGRNAZ]', $this->formData['returnData']['CODGRNAZ']);
                        Out::valore($this->nameForm . '_DESGRNAZ_decod', $this->formData['returnData']['DESGRNAZ']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    //ricerca gruppo nazionalita
                    case $this->nameForm . '_CODGRNAZ':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODGRNAZ'], $this->nameForm . '_CODGRNAZ', $this->nameForm . '_DESGRNAZ')) {
                            $this->decodGruppo($this->formData[$this->nameForm . '_CODGRNAZ'], ($this->nameForm . '_CODGRNAZ'), null, ($this->nameForm . '_DESGRNAZ'));
                        } else {
                            Out::valore($this->nameForm . '_DESGRNAZ', '');
                        }
                        break;
                    //rircerca gruppo nazionalita
                    case $this->nameForm . '_DESGRNAZ':
                        $this->decodGruppo(null, ($this->nameForm . '_CODGRNAZ'), $this->formData[$this->nameForm . '_DESGRNAZ'], ($this->nameForm . '_DESGRNAZ'));
                        break;
                    //dettaglio gruppo nazionalita
                    case $this->nameForm . '_BTA_NAZION[CODGRNAZ]':
                        $this->decodGruppo($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODGRNAZ'], ($this->nameForm . '_BTA_NAZION[CODGRNAZ]'), null, ($this->nameForm . '_DESGRNAZ_decod'));
                        break;
                    //dettaglio gruppo nazionalita
                    case $this->nameForm . '_DESGRNAZ_decod':
                        $this->decodGruppo(null, ($this->nameForm . '_BTA_NAZION[CODGRNAZ]'), $_POST[$this->nameForm . '_DESGRNAZ_decod'], ($this->nameForm . '_DESGRNAZ_decod'));
                        break;
                    case $this->nameForm . '_CODNAZI':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODNAZI'], $this->nameForm . '_CODNAZI');
                        break;
                    case $this->nameForm . '_BTA_NAZION[CODNA_IST]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNA_IST'], $this->nameForm . '_BTA_NAZION[CODNA_IST]');
                        break;
                    case $this->nameForm . '_BTA_NAZION[ISO3166_N3]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['ISO3166_N3'], $this->nameForm . '_BTA_NAZION[ISO3166_N3]');
                        break;
                    case $this->nameForm . '_BTA_NAZION[LLPIVANAZ]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['LLPIVANAZ'], $this->nameForm . '_BTA_NAZION[LLPIVANAZ]');
                        break;
                    case $this->nameForm . '_BTA_NAZION[CODEST_770]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODEST_770'], $this->nameForm . '_BTA_NAZION[CODEST_770]');
                        break;
                }
                break;
        }
    }

    protected function preNuovo() {
        $this->pulisciCampi();
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_NAZION[CODNAZI]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_NAZION[CODNAZI]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_NAZION[CODNAZI]');
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DESNAZI');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DESNAZI');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_NAZION[CODNAZI]');
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODNAZI_formatted'] != '') {
            $this->gridFilters['CODNAZI'] = $this->formData['CODNAZI_formatted'];
        }
        if ($_POST['CODGRNAZ'] != '') {
            $this->gridFilters['CODGRNAZ'] = $this->formData['CODGRNAZ'];
        }
        if ($_POST['DESNAZI'] != '') {
            $this->gridFilters['DESNAZI'] = $this->formData['DESNAZI'];
        }
        if ($_POST['DESNAZION'] != '') {
            $this->gridFilters['DESNAZION'] = $this->formData['DESNAZION'];
        }
        if ($_POST['SIGLANAZ'] != '') {
            $this->gridFilters['SIGLANAZ'] = $this->formData['SIGLANAZ'];
        }
        if ($_POST['ISO3166_A2'] != '') {
            $this->gridFilters['ISO3166_A2'] = $this->formData['ISO3166_A2'];
        }
        if ($_POST['CODNAZIMC'] != '') {
            $this->gridFilters['CODNAZIMC'] = $this->formData['CODNAZIMC'];
        }
        if ($_POST['CODNAZICO'] != '') {
            $this->gridFilters['CODNAZICO'] = $this->formData['CODNAZICO'];
        }
        if ($_POST['CODGOVE'] != '') {
            $this->gridFilters['CODGOVE'] = $this->formData['CODGOVE'];
        }
        if ($_POST['DATAINIZ'] != '') {
            $this->gridFilters['DATAINIZ'] = $this->formData['DATAINIZ'];
        }
        if ($_POST['DATAFINE'] != '') {
            $this->gridFilters['DATAFINE'] = $this->formData['DATAFINE'];
        }
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_DESGRNAZ_decod', '');
    }

    protected function postDettaglio($index) {
        $this->decodGruppo($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODGRNAZ'], ($this->nameForm . '_BTA_NAZION[CODGRNAZ]'), null, ($this->nameForm . '_DESGRNAZ_decod'));
        Out::attributo($this->nameForm . '_BTA_NAZION[CODNAZI]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_NAZION[CODNAZI]', 'background-color', '#FFFFE0');
        Out::valore($this->nameForm . '_BTA_NAZION[DESNAZI]', trim($this->CURRENT_RECORD['DESNAZI']));
        Out::valore($this->nameForm . '_BTA_NAZION[DESNAZION]', trim($this->CURRENT_RECORD['DESNAZION']));
        Out::setFocus('', $this->nameForm . '_BTA_NAZION[CODNA_IST]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['CODNAZI'] = trim($this->formData[$this->nameForm . '_CODNAZI']);
        $filtri['DESNAZI'] = trim($this->formData[$this->nameForm . '_DESNAZI']);
        $filtri['CODGRNAZ'] = trim($this->formData[$this->nameForm . '_CODGRNAZ']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaNazion($filtri, false, $sqlParams, true);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaNazionChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CODNAZI_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CODNAZI']);
            $Result_tab[$key]['DESNAZI'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['DESNAZI']);
            $Result_tab[$key]['SIGLANAZ'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['SIGLANAZ']);
        }
        return $Result_tab;
    }

    private function decodGruppo($codValue, $codField, $desValue, $desField, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaGrunaz", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "CODGRNAZ", $desValue, $desField, "DESGRNAZ", 'returnFromBtaGrunaz', $_POST['id'], $searchButton);
    }

}

?>