<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaOpeott() {
    $cwbBtaOpeott = new cwbBtaOpeott();
    $cwbBtaOpeott->parseEvent();
    return;
}

class cwbBtaOpeott extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaOpeott';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 18;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Raggruppamenti':
                        $this->apriRaggruppamenti();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_OPEOTT[PK]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PK'], $this->nameForm . '_BTA_OPEOTT[PK]');
                        break;
                    case $this->nameForm . '_PK':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_PK'], $this->nameForm . '_PK');
                        break;
                }
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODARROT_formatted'] != '') {
            $this->gridFilters['COD_OP_OTT'] = $this->formData['COD_OP_OTT_formatted'];
        }
        if ($_POST['DES_GE60'] != '') {
            $this->gridFilters['DES_150'] = $this->formData['DES_150'];
        }
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_OPEOTT[COD_OP_OTT]', 'readonly', '1');
        Out::setFocus("", $this->nameForm . '_BTA_OPEOTT[COD_OP_OTT]');
        Out::css($this->nameForm . '_BTA_OPEOTT[COD_OP_OTT]', 'background-color', '#FFFFFF');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_OPEOTT[COD_OP_OTT]');
    }

    protected function postDettaglio($index) {
        // toglie gli spazi del char
        Out::valore($this->nameForm . '_BTA_OPEOTT[DES_150]', trim($this->CURRENT_RECORD['DES_150']));
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DES_150');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DES_150');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['COD_OP_OTT'] = trim($this->formData[$this->nameForm . '_COD_OP_OTT']);
        $filtri['DES_150'] = trim($this->formData[$this->nameForm . '_DES_150']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaOpeott($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaOpeottChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['COD_OP_OTT_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['COD_OP_OTT']);
        }
        return $Result_tab;
    }
}

