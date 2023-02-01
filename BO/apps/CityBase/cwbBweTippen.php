<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BWE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';

function cwbBweTippen() {
    $cwbBweTippen = new cwbBweTippen();
    $cwbBweTippen->parseEvent();
    return;
}

class cwbBweTippen extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBweTippen';
        $this->searchOpenElenco = true;
        $this->AUTOR_MODULO = 'BWE';
        $this->AUTOR_NUMERO = 1;
        $this->libDB = new cwbLibDB_BWE();
    }
    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['IDTIPPEN_formatted'] != '') {
            $this->gridFilters['IDTIPPEN'] = $this->formData['IDTIPPEN_formatted'];
        }
        if ($_POST['DESCRIZ'] != '') {
            $this->gridFilters['DESCRIZ'] = $this->formData['DESCRIZ'];
        }
        if ($_POST['CODTIPSCAD'] != '') {
            $this->gridFilters['CODTIPSCAD'] = $this->formData['CODTIPSCAD'];
        }
        if ($_POST['SUBTIPSCAD'] != '') {
            $this->gridFilters['SUBTIPSCAD'] = $this->formData['SUBTIPSCAD'];
        }
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['IDTIPPEN'] = trim($this->formData[$this->nameForm . '_IDTIPPEN']);
        $filtri['DESCRIZ'] = trim($this->formData[$this->nameForm . '_DESCRIZ']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBweTippen($filtri, false, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBweTippenChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['IDTIPPEN_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['IDTIPPEN']);
        }
        return $Result_tab;
    }
}

?>