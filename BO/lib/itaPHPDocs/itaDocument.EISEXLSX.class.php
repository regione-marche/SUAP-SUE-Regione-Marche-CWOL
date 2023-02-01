<?php

/**
 * Implementazione xlsx con libreria eisexlsx   
 * Packagist Url: https://packagist.org/packages/easyise/eisexlsx
 */
class itaDocumentEISEXLSX {
    private $xlsx;
    private $message;
    
    public function loadContent($path) {
        $this->xlsx = new eiseXLSX($path);        
        if (!$this->xlsx) {
            $this->message = 'Impossibile istanziare classe xlsx';
            return false;
        }
        return true;
    }
    
    public function setVarContent($cell, $value) {
        if (!$this->xlsx) {
            $this->message = 'Oggetto xlsx non valorizzato';
            return false;
        }
        $this->xlsx->data($cell, $value);
        return true;
    }
    
    public function saveContent($path) {
        if (!$this->xlsx) {
            $this->message = 'Oggetto xlsx non valorizzato';
            return false;
        }
        $this->xlsx->Output($path, 'F');
        return true;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
}
