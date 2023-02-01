<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBtaPretes() {
    $cwbBtaPretes = new cwbBtaPretes();
    $cwbBtaPretes->parseEvent();
    return;
}

class cwbBtaPretes extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaPretes';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 1;
        $this->libDB = new cwbLibDB_BTA();
        $this->searchOpenElenco = true;
        $this->skipAuth = true;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    //visualizza matricole
                    case $this->nameForm . '_Matricole':
                        $this->matricole();
                        break;
                }
                break;
        }
    }

    protected function elaboraCurrentRecord($operation) {
        // trovaProgressivo qui
        if ($operation === itaModelService::OPERATION_INSERT) {
            $id = cwbLibCalcoli::trovaProgressivo('PROGTES', 'BTA_PRETES');
            $this->CURRENT_RECORD['PROGTES'] = $id;
        }
    }

    protected function postApriForm() {
        Out::hide($this->nameForm . '_divId');
    }

    protected function postNuovo() {
        Out::setFocus("", $this->nameForm . '_DESTES');
    }

    protected function postAltraRicerca() {
        Out::hide($this->nameForm . '_Matricole');
        Out::setFocus("", $this->nameForm . '_DESTES');
    }

    protected function postTornaElenco() {
        Out::show($this->nameForm . '_Matricole');
    }

    protected function postAggiungi() {
        Out::setFocus('', $this->nameForm . '_BTA_PRETES[DESTES]');
    }

    protected function postDettaglio($index) {
        Out::show($this->nameForm . '_Matricole');
        Out::setFocus('', $this->nameForm . '_BTA_PRETES[DESTES]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        Out::show($this->nameForm . '_Matricole');
        $filtri['DESTES'] = trim($this->formData[$this->nameForm . '_DESTES']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaPretes($filtri, false, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaPretesChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PROGTES_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGTES']);
        }
        return $Result_tab;
    }

    private function matricole() {
        $recordSelect = $_POST[$this->nameForm . '_gridBtaPretes']['gridParam']['selarrrow'];

        if (!$recordSelect) {
            Out::msgStop("Errore", "Selezionare almeno un record");
            return;
        }
        cwbLib::apriFinestraDettaglio('cwbBtaPresog', $this->nameForm, 'returnFromBtaPresog', $_POST['id'],$recordSelect,$recordSelect);
    }

}

?>