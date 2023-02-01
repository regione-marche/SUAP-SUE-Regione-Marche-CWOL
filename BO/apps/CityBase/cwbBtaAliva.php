<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaAliva() {
    $cwbBtaAliva = new cwbBtaAliva();
    $cwbBtaAliva->parseEvent();
    return;
}

class cwbBtaAliva extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaAliva';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 14;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_ALIVA[ANNO]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_ALIVA[ANNO]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_ALIVA[ANNO]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_ALIVA[ANNO]');
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_ANNO');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_ANNO');
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_ALIVA[ANNO]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_ALIVA[ANNO]', 'background-color', '#FFFFE0');
        Out::setFocus('', $this->nameForm . '_BTA_ALIVA[IVAALIQ]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        if($this->externalParams['ANNO'] > 0) {
            $filtri['ANNO'] = $this->externalParams['ANNO'];
        } else {
            $filtri['ANNO'] = trim($this->formData[$this->nameForm . '_ANNO']);
        }
        
        $filtri['FLAG_DIS'] = trim($this->formData[$this->nameForm . '_FLAG_DIS']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaAliva($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        list($ANNO, $IVAALIQ) = explode('|', $index);
        $this->SQL = $this->libDB->getSqlLeggiBtaAlivaChiave($ANNO, $IVAALIQ, $sqlParams);
    }
}

?>