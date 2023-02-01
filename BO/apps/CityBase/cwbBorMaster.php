<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

function cwbBorMaster() {
    $cwbBorMaster = new cwbBorMaster();
    $cwbBorMaster->parseEvent();
    return;
}

class cwbBorMaster extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBorMaster';
        $this->CODAREAMA = "CODAREAMA";
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BOR();
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Moduli':
                        $this->apriModuli();
                        break;
                }
                break;
        }
    }

    protected function setVisRisultato() {
        parent::setVisRisultato();
        Out::show($this->nameForm . '_Moduli');
    }

    protected function setVisRicerca() {
        parent::setVisRicerca();
        Out::hide($this->nameForm . '_Moduli');
    }

    protected function setVisNuovo() {
        parent::setVisNuovo();
        Out::hide($this->nameForm . '_Moduli');
    }

    protected function setVisDettaglio() {
        parent::setVisDettaglio();
        Out::show($this->nameForm . '_Moduli');
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODAREAMA_formatted'] != '') {
            $this->gridFilters['CODAREAMA'] = $this->formData['CODAREAMA_formatted'];
        }
        if ($_POST['DESAREA'] != '') {
            $this->gridFilters['DESAREA'] = $this->formData['DESAREA'];
        }
        if ($_POST['CODSTAMPA'] != '') {
            $this->gridFilters['CODSTAMPA'] = $this->formData['CODSTAMPA'];
        }
    }

//    private function apriModuli(){
//        if($this->getDetailView()){
//            $this->formDataToCurrentRecord();
//        }else{
//            $this->loadCurrentRecord($_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow']);
//        }
//        cwbLib::apriFinestraDettaglio('cwbBorModuli', $this->nameForm, 'returnFromBorModuli', $_POST['id'], $this->CURRENT_RECORD);       
//    }    


    private function apriModuli() {
        $externalParams = array("CODNAZPRO_da" => 
            array("VALORE"=>3,"PERMANENTE"=>FALSE)
            , "CODNAZPRO_a" => 3);

        cwbLib::apriFinestraDettaglio('cwbBorModuli', $this->nameForm, 'returnFromBorModuli', $_POST['id'], $externalParams, $externalParams);
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DESAREA');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DESAREA');
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BOR_MASTER[CODAREAMA]', 'readonly', '1');
        Out::setFocus("", $this->nameForm . '_BOR_MASTER[CODAREAMA]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BOR_MASTER[CODAREAMA]');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        Out::show($this->nameForm . '_Moduli');
        Out::attributo($this->nameForm . '_BOR_MASTER[CODAREAMA]', 'readonly', '0');
        Out::attributo($this->nameForm . '_BOR_MASTER[CODAREAMA]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_BOR_MASTER[DESAREA]');
        // toglie gli spazi del char
        Out::valore($this->nameForm . '_BOR_MASTER[DESAREA]', trim($this->CURRENT_RECORD['DESAREA']));
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['CODAREAMA'] = trim($this->formData[$this->nameForm . '_CODAREAMA']);
        $filtri['DESAREA'] = trim($this->formData[$this->nameForm . '_DESAREA']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBorMaster($filtri, true, $sqlParams);
        Out::show($this->nameForm . '_Moduli');
    }

    public function sqlDettaglio($index, &$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBorMasterChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CODAREAMA_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CODAREAMA']);
        }
        return $Result_tab;
    }

}

