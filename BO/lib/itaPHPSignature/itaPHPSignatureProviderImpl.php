<?php

/**
 * Superclasse getione firma remota 
 * @author l.pergolini
 */
require_once(ITA_LIB_PATH . '/itaPHPSignature/itaPHPSignatureProvider.php');

abstract class itaPHPSignatureProviderImpl {

    private $errorCode;
    private $errorDescription;
    
    public function getErrorCode() {
        return $this->errorCode;
    }

    public function getErrorDescription() {
        return $this->errorDescription;
    }
    
    public function setError($errCode, $errDescription) {
        $this->errorCode = $errCode;
        $this->errorDescription = $errDescription;
    }
    



}
