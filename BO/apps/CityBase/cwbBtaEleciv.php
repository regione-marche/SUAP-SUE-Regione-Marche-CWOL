<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';


function cwbBtaEleciv() {
    $cwbBtaEleciv = new cwbBtaEleciv();
    $cwbBtaEleciv->parseEvent();
    return;
}

class cwbBtaEleciv extends cwbBpaGenTab {

    function initVars() {
        $this->libDB = new cwbLibDB_BTA(); 
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Stampa': // Visualizzo la form di ricerca
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
        $this->SQL = $this->libDB->getSqlLeggiBtaEleciv($filtri, true);        
        Out::setFocus("", $this->nameForm . '_DALLADATA');
    }
}

?>