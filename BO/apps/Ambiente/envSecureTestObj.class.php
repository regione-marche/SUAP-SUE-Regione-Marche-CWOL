<?php

class envSecureTestObj extends envSecureObj{

    private $testvar;

    public function getTestvar() {
        return $this->testvar;
    }

    public function setTestvar($testvar) {
        $this->testvar = $testvar;
    }

    
    function __construct() {
        parent::__construct();
        $this->setObjClass('envTest');
        $this->setObjContext('secContextTest');
    }

    function __destruct() {
        parent::__destruct();
    }
    
    public function testMethod(){
        return true;
    }
    
}
