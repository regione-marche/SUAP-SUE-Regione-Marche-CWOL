<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';

function cwbBgeFldal() {
    $cwbBgeFldal = new cwbBgeFldal();
    $cwbBgeFldal->parseEvent();
    return;
}

class cwbBgeFldal extends cwbBpaGenTab {

    function initVars() {
        $this->nameForm = 'cwbBgeFldal';
        $this->GRID_NAME = 'gridBgeFldal';
        $this->skipAuth = true;
        $this->libDB = new cwbLibDB_BGE();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
        $this->searchOpenElenco = true;
    }

    protected function postApriForm() {
        $this->initComboCodareama();
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['CODAREAMA'] = trim($this->formData[$this->nameForm . '_CODAREAMA']);
        $this->SQL = $this->libDB->getSqlLeggiBgeFldal($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBgeFldalChiave($index, $sqlParams);
    }

    private function initComboCodareama() {
        Out::select($this->nameForm . '_' . $this->TABLE_NAME . '[CODAREAMA]', 1, null, 1, "Selezionare..");
        Out::select($this->nameForm . '_CODAREAMA', 1, null, 1, "Selezionare..");

        foreach ($this->helper->getAreeCityware() as $keyArea => $valueArea) {
            Out::select($this->nameForm . '_' . $this->TABLE_NAME . '[CODAREAMA]', 1, $keyArea, 1, $valueArea);
            Out::select($this->nameForm . '_CODAREAMA', 1, $keyArea, 1, $valueArea);
        }
    }

}

?>