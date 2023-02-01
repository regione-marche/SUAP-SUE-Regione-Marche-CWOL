<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';


function cwbBtaElevie() {
    $cwbBtaElevie = new cwbBtaElevie();
    $cwbBtaElevie->parseEvent();
    return;
}

class cwbBtaElevie extends cwbBpaGenTab {

    function initVars() {
        $this->libDB = new cwbLibDB_BTA(); 
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Stampa': 
                        $this->Stampa();
                        break;
                }
                break;
        }
    }
   
    protected function postApriForm() {
        Out::show($this->nameForm . '_divGestione');
        Out::setFocus("", $this->nameForm . '_DALLADATA');
    }
    
    public function Stampa(){
        $filtri = array();
        $filtri['DALLADATA'] = trim($this->formData[$this->nameForm . '_DALLADATA']);        
        if($filtri['DALLADATA']){
            $filtri['DALLADATA'] = date('Y-m-d', strtotime($filtri['DALLADATA']));
        }else{
            $filtri['DALLADATA'] = null;
        }
        $filtri['ALLADATA'] = trim($this->formData[$this->nameForm . '_ALLADATA']);
        if($filtri['ALLADATA']){
            $filtri['ALLADATA'] = date('Y-m-d', strtotime($filtri['ALLADATA']));
        }else{
            $filtri['ALLADATA'] = null;
        }
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaElevie($filtri, true);        
        $itaJR = new itaJasperReport();        
        $itaJR->runSQLReportPDF($this->MAIN_DB, $this->nameForm, $this->initParametriStampaElenco());
        Out::setFocus("", $this->nameForm . '_DALLADATA');
    }
}

?>