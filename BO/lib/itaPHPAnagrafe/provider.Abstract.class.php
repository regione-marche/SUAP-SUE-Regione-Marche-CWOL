<?php

class provider_Abstract {

    protected $lastExitCode;
    protected $lastMessage;
    protected $returnModel;

    function __construct() {
        
    }

    function getLastExitCode() {
        return $this->lastExitCode;
    }

    function getLastMessage() {
        return $this->lastMessage;
    }

    public function getReturnModel() {
        return $this->returnModel;
    }

    public function setReturnModel($returnModel) {
        $this->returnModel = $returnModel;
    }

    function getProviderType() {
        
    }

    public function getVie() {
        
    }

    public function getCittadiniLista($ricParam) {
        
    }

    function getCittadinoFamiliari($param) {
        
    }

    function getCittadinoVariazioni($param) {
        
    }

}

?>
