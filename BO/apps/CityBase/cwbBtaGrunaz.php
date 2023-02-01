<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaGrunaz() {
    $cwbBtaGrunaz = new cwbBtaGrunaz();
    $cwbBtaGrunaz->parseEvent();
    return;
}

class cwbBtaGrunaz extends cwbBpaGenTab {

    protected function initVars() {
        $this->GRID_NAME = 'gridBtaGrunaz';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 4;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BTA(); 
        $this->elencaAutoAudit = true;
    }
    
    protected function initPrintStrategy() {
        $this->printStrategy = cwbBpaGenHelper::PRINT_STRATEGY_JASPER;
    }
    
    protected function postApriForm() {
        $this->initComboGestione();
        Out::setFocus("", $this->nameForm . '_DESGRNAZ');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DESGRNAZ');
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_GRUNAZ[CODGRNAZ]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_GRUNAZ[CODGRNAZ]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_GRUNAZ[CODGRNAZ]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_GRUNAZ[CODGRNAZ]');
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_GRUNAZ[CODGRNAZ]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_GRUNAZ[CODGRNAZ]', 'background-color', '#FFFFE0');
        Out::valore($this->nameForm . '_BTA_GRUNAZ[DESGRNAZ]', trim($this->CURRENT_RECORD['DESGRNAZ']));
        Out::setFocus('', $this->nameForm . '_BTA_GRUNAZ[DESGRNAZ]');            
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODGRNAZ_formatted'] != '') {
            $this->gridFilters['CODGRNAZ'] = $this->formData['CODGRNAZ_formatted'];
        }
        if ($_POST['DESGRNAZ'] != '') {
            $this->gridFilters['DESGRNAZ'] = $this->formData['DESGRNAZ'];
        }
        if ($_POST['CEENAZ'] != '') {
            $this->gridFilters['CEENAZ'] = $this->formData['CEENAZ'];
        }
        if ($_POST['CONTINENTE'] != '') {
            $this->gridFilters['CONTINENTE'] = $this->formData['CONTINENTE'];
        }
    }

    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['CODGRNAZ'] = trim($this->formData[$this->nameForm . '_CODGRNAZ']);
        $filtri['DESGRNAZ'] = trim($this->formData[$this->nameForm . '_DESGRNAZ']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaGrunaz($filtri, true, $sqlParams);
    }

    protected function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaGrunazChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CODGRNAZ_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CODGRNAZ']);
        }
        return $Result_tab;
    }
    
    protected function defineExportXLSHeaders() {
        $this->exportXlsHeaders = array(
            'CODGRNAZ' => 'string',
            'CONTINENTE' => 'integer'
        );        
    }
    
    private function initComboGestione() {
        // CEE
        Out::select($this->nameForm . '_BTA_GRUNAZ[CEENAZ]', 1, "C", 1, "CEE");
        Out::select($this->nameForm . '_BTA_GRUNAZ[CEENAZ]', 1, "E", 0, "Extra-CEE");

        // Continente
        Out::select($this->nameForm . '_BTA_GRUNAZ[CONTINENTE]', 1, "1", 1, "Europa");
        Out::select($this->nameForm . '_BTA_GRUNAZ[CONTINENTE]', 1, "2", 0, "Africa");
        Out::select($this->nameForm . '_BTA_GRUNAZ[CONTINENTE]', 1, "3", 0, "America");
        Out::select($this->nameForm . '_BTA_GRUNAZ[CONTINENTE]', 1, "4", 0, "Asia");
        Out::select($this->nameForm . '_BTA_GRUNAZ[CONTINENTE]', 1, "5", 0, "Oceania");
        Out::select($this->nameForm . '_BTA_GRUNAZ[CONTINENTE]', 1, "6", 0, "Apolide");
    }

}

?>