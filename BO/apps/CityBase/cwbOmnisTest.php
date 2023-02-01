<?php

include_once ITA_LIB_PATH . '/itaPHPOmnis/itaOmnisClient.class.php';

function cwbOmnisTest(){
    $cwbOmnisTest = new cwbOmnisTest();
    $cwbOmnisTest->parseEvent();
    return;
}

class cwbOmnisTest extends itaModel{    
    
    function __construct() {
        parent::__construct();
        try {
            $this->nameForm = 'cwbOmnisTest';                
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }
    
    public function parseEvent() {                
        $methodArgs = array();
        $methodArgs[0] = 'F';
        $omnisClient = new itaOmnisClient();
        $result = $omnisClient->callExecute('OBJ_DWE_PORTAL', 'dta_relpar', $methodArgs);
        Out::msgInfo("dump", print_r($result, true));
    }
   
}

