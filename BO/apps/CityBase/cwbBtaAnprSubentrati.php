<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaAnprSubentrati() {
    $cwbBtaAnprSubentrati = new cwbBtaAnprSubentrati();
    $cwbBtaAnprSubentrati->parseEvent();
    return;
}

class cwbBtaAnprSubentrati extends cwbBpaGenTab {

    protected function initVars() {
        $this->GRID_NAME = 'gridBtaAnprSubentrati';
        $this->libDB_BTA = new cwbLibDB_BTA();
        $this->searchOpenElenco = true;
        $this->errorOnEmpty = false;
    }

    public function preParseEvent() {
        switch ($_POST['event']) {
            case 'dbClickRow':
                if ($this->flagSearch == false) {
                    $this->setBreakEvent(true);
                    break;
                }
        }
    }

    public function parseEvent() {
        switch ($_POST['event']) {
            case 'openform':
                $this->elenca(true);
                break;
        }
        parent::parseEvent();
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['COMUNE'] != '') {
            $this->gridFilters['COMUNE_RIC'] = $this->formData['COMUNE'];
        }
        if ($_POST['PROVINCIA'] != '') {
            $this->gridFilters['PROVINCIA'] = $this->formData['PROVINCIA'];
        }
    }

    protected function postAltraRicerca() {
        Out::setFocus('', $this->nameForm . '_COMUNE');
    }

    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['COMUNE_RIC'] = $this->formData['COMUNE'];
        $filtri['PROVINCIA'] = $this->formData['PROVINCIA'];
        $filtri['COMUNE_RIC'] = trim($this->formData[$this->nameForm . '_COMUNE']);
        $filtri['PROVINCIA'] = trim($this->formData[$this->nameForm . '_PROVINCIA']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB_BTA->getSqlBtaANPRSubentrati($filtri, false, $sqlParams, true);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CODISTAT'] = str_pad($Result_tab[$key]['ISTNAZPRO'], 3, "00", STR_PAD_LEFT) . str_pad($Result_tab[$key]['ISTLOCAL'], 3, "00", STR_PAD_LEFT);
        }
        return $Result_tab;
    }

}
