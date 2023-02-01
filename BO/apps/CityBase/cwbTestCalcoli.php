<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbTestCalcoli() {
    $cwbTestCalcoli = new cwbTestCalcoli();
    $cwbTestCalcoli->parseEvent();
    return;
}

class cwbTestCalcoli extends itaModel {    
       
    function __construct() {      
        parent::__construct();
    } 
    
    public function parseEvent() {     
        $this->testConvertYear();
    }
    
    private function testConvertYear() {
        
        // test con data
        //$date = DateTime::createFromFormat("Y-m-d", "1901-06-15");        
        
        // test con stringa
        //$date = "1901-06-15";
        $date = '';
        
        $risultato = cwbLibCalcoli::ConvertYear($date);        
        Out::msgInfo("ConvertYear", $risultato == null ? 'null' : print_r($risultato, true));
    }
    
}
        
?>