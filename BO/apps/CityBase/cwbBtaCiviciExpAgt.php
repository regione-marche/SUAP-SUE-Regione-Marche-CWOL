<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';

function cwbBtaCiviciExpAgt() {
    $cwbBtaCiviciExpAgt = new cwbBtaCiviciExpAgt();
    $cwbBtaCiviciExpAgt->parseEvent();
    return;
}

class cwbBtaCiviciExpAgt extends itaFrontControllerCW {
    
    public $CITYWARE_DB;
    public $divGes = "_divGestione";

    function postItaFrontControllerCostruct() {
        try {
            $this->MAIN_DB = ItaDB::DBOpen(cwbLib::getCitywareConnectionName()); 
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                Out::show($this->nameForm . $this->divGes);
                Out::setFocus('', $this->nameForm . '_PERCORSO');        
                break;
            }
    }    

    public function close(){
        parent::close();
    }
}
?>