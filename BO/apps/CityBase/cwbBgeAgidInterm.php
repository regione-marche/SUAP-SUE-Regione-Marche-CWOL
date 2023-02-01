<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';

function cwbBgeAgidInterm() {
    $cwbBgeAgidInterm = new cwbBgeAgidInterm();
    $cwbBgeAgidInterm->parseEvent();
    return;
}

class cwbBgeAgidInterm extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBgeAgidInterm';
        $this->libDB = new cwbLibDB_BGE();
        $this->searchOpenElenco = true;
        $this->skipAuth = true;
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    protected function preConstruct() {
        parent::preConstruct();
    }

    public function __destruct() {
        $this->preDestruct();
        parent::__destruct();
    }

    protected function preDestruct() {
        if ($this->close != true) {
            
        }
    }

    protected function postApriForm() {
        $this->initComboIntermediario();
        Out::disableField($this->nameForm . '_INTERMEDIARIO');
        Out::disableField($this->nameForm . '_BGE_AGID_INTERM[FLAG_DIS]');
    }

    protected function postTornaElenco() {
        parent::elenca($reload);
    }

    protected function postAggiorna() {
        $this->setVisControlli(true, false, false, false, false, true, false, false, false, true);
    }

    protected function postDettaglio($index) {
        Out::valore($this->nameForm . '_INTERMEDIARIO', $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['INTERMEDIARIO']);
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBgeAgidInterm($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBgeAgidIntermChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PROGKEYTAB_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGKEYTAB']);
        }
        return $Result_tab;
    }

    protected function initComboIntermediario() {
        // Combo Intermediario
        Out::html($this->nameForm . '_INTERMEDIARIO', ''); // svuoto combo

        Out::select($this->nameForm . '_INTERMEDIARIO', 1, "1", 1, "E-Fil");
        Out::select($this->nameForm . '_INTERMEDIARIO', 1, "2", 0, "Next Step Solution");
    }

}

?>