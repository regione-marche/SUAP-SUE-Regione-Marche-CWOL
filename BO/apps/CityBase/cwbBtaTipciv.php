<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBtaTipciv() {
    $cwbBtaTipciv = new cwbBtaTipciv();
    $cwbBtaTipciv->parseEvent();
    return;
}

class cwbBtaTipciv extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaTipciv';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 8;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TIPONCIV':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_TIPONCIV'], $this->nameForm . '_TIPONCIV');
                        break;
                    case $this->nameForm . '_BTA_TIPCIV[TIPONCIV]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['TIPONCIV'], $this->nameForm . '_BTA_TIPCIV[TIPONCIV]');
                        break;
                }
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['TIPONCIV_formatted'] != '') {
            $this->gridFilters['TIPONCIV'] = $this->formData['TIPONCIV_formatted'];
        }
        if ($_POST['DESTIPCIV'] != '') {
            $this->gridFilters['DESTIPCIV'] = $this->formData['DESTIPCIV'];
        }
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DESTIPCIV');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DESTIPCIV');
    }

    protected function postNuovo() {
        //TODO: nel vecchio faceva questo controllo "If (iv_flag_default)"
        // va replicato. Per adesso gestisco iv_flag_default come fosse sempre 0
        if ($iv_flag_default) {
            $progr = cwbLibCalcoli::Trova_Prog_Min_Diecimila("TIPONCIV", "BTA_TIPCIV", $this->MAIN_DB, 100);
        } else {
            $progr = cwbLibCalcoli::Trova_Prog_Diecimila("TIPONCIV", "BTA_TIPCIV", $this->MAIN_DB, 100);
        }
        Out::valore($this->nameForm . '_BTA_TIPCIV[TIPONCIV]', $progr);
        Out::attributo($this->nameForm . '_BTA_TIPCIV[TIPONCIV]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_TIPCIV[TIPONCIV]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_TIPCIV[TIPONCIV]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_TIPCIV[TIPONCIV]');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_TIPCIV[TIPONCIV]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_TIPCIV[TIPONCIV]', 'background-color', '#FFFFE0');
        Out::setFocus('', $this->nameForm . '_BTA_TIPCIV[DESTIPCIV]');
        Out::valore($this->nameForm . '_BTA_TIPCIV[DESTIPCIV]', trim($this->CURRENT_RECORD['DESTIPCIV']));
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['TIPONCIV'] = trim($this->formData[$this->nameForm . '_TIPONCIV']);
        $filtri['DESTIPCIV'] = trim($this->formData[$this->nameForm . '_DESTIPCIV']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaTipciv($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaTipcivChiave($index, $sqlParams);
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_FLAG_DIS', '');
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['TIPONCIV_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['TIPONCIV']);
        }
        return $Result_tab;
    }

}

