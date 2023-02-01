<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';

function utiPDOTester() {
    $utiPDOTester = new utiPDOTester();
    $utiPDOTester->parseEvent();
    return;
}

class utiPDOTester extends itaModel { 
    
    public function __construct() {
        parent::__construct();
    }
    
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case 'utiPDOTester_strTrim':
                        $this->strTrim();
                        break;
                    case 'utiPDOTester_strLTrim':
                        $this->strLTrim();
                        break;
                    case 'utiPDOTester_strRTrim':
                        $this->strRTrim();
                        break;  
                    case 'utiPDOTester_strLPad':
                        $this->strLPad();
                        break;    
                    case 'utiPDOTester_strRPad':
                        $this->strRPad();
                        break; 
                    case 'utiPDOTester_strCast':
                        $this->strCast();
                        break;   
                }
                break;
        }
    }
    
    public function close() {
        parent::close();
        Out::closeDialog('utiPDOTester');
    }
    
    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }
    
    private function strTrim() {
        $sql = "SELECT BTA_GRUNAZ.CODGRNAZ, " . $this->getDB()->strTrim('DESGRNAZ') . " FROM BTA_GRUNAZ";
        $result = ItaDB::DBQuery($this->getDB(), $sql, false, array());
        Out::msgInfo("Result", print_r($result, true));
    }
    
    private function strLTrim() {
        $sql = "SELECT BTA_GRUNAZ.CODGRNAZ, " . $this->getDB()->strLTrim('DESGRNAZ') . " FROM BTA_GRUNAZ";
        $result = ItaDB::DBQuery($this->getDB(), $sql, false, array());
        Out::msgInfo("Result", print_r($result, true));
    }
    
    private function strRTrim() {
        $sql = "SELECT BTA_GRUNAZ.CODGRNAZ, " . $this->getDB()->strRTrim('DESGRNAZ') . " FROM BTA_GRUNAZ";
        $result = ItaDB::DBQuery($this->getDB(), $sql, false, array());
        Out::msgInfo("Result", print_r($result, true));
    }
    
    private function strLPad() {
        $sql = "SELECT BTA_GRUNAZ.CODGRNAZ, " . $this->getDB()->strLPad('CODGRNAZ', 4, '*') . " FROM BTA_GRUNAZ";
        $result = ItaDB::DBQuery($this->getDB(), $sql, false, array());
        Out::msgInfo("Result", print_r($result, true));
    }
    
    private function strRPad() {
        $sql = "SELECT BTA_GRUNAZ.CODGRNAZ, " . $this->getDB()->strRPad('CODGRNAZ', 4, '*') . " FROM BTA_GRUNAZ";
        $result = ItaDB::DBQuery($this->getDB(), $sql, false, array());
        Out::msgInfo("Result", print_r($result, true));
    }
    
    private function strCAST() {
        $sql = "SELECT BTA_GRUNAZ.CODGRNAZ, " . $this->getDB()->strCast('CODGRNAZ', 'int') . " as CODINT FROM BTA_GRUNAZ";
        $result = ItaDB::DBQuery($this->getDB(), $sql, false, array());
        Out::msgInfo("Result", var_export($result, true));
    }
    
    private function getDB() {
        if (!$this->CITYWARE_DB) {
            try {
                $this->CITYWARE_DB = ItaDB::DBOpen(cwbLib::getCitywareConnectionName(), '');
            } catch (Exception $e) {
                Out::msgStop("Error", $e->getMessage());
            }
        }
        return $this->CITYWARE_DB;
    }
    
}
