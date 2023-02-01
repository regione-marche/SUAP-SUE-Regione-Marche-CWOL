<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaUtentePaleo
 *
 * @author michele
 */
class itaCercaDocumentoProtocollo {

    private $DocNumber;
    private $Segnatura;


    public function setDocNumber($DocNumber) {
        $this->DocNumber = $DocNumber;
    }

    public function setSegnatura($Segnatura) {
        $this->Segnatura = $Segnatura;
    }

    public function getDocNumber() {
        return $this->DocNumber;
    }

    public function getSegnatura() {
        return $this->Segnatura;
    }

}

?>
