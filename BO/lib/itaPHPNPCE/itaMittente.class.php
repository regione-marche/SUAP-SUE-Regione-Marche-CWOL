<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaDestinatari
 *
 * @author Mario Mazza <mario.mazza@italsoft.eu>
 */
//require_once(ITA_LIB_PATH . '/nusoap/nusoap1.2.php');

class itaMittente {

    private $Nominativo = array();
    private $InviaStampa;

    function __construct($Nominativo = array()) {
        if ($Nominativo) {
            $this->Nominativo = $Nominativo;
        }
    }

//set()
    public function setNominativo($Nominativo) {
        $this->Nominativo = $Nominativo;
    }
    
    public function setInviaStampa($InviaStampa) {
        $this->InviaStampa = $InviaStampa;
    }

//get()
    public function getNominativo() {
        return $this->Nominativo;
    }

    public function getInviaStampa() {
        return $this->InviaStampa;
    }

    public function getSoapValRequest() {
        //NOMINATIVO
        $Indirizzo = "";
        $Indirizzo = new soapval('com1:Indirizzo', 'com1:Indirizzo', "", false, false, $this->Nominativo['Indirizzo']['Attributi']);
        $Nominativo = new soapval('com1:Nominativo', 'com1:Nominativo', $Indirizzo, false, false, $this->Nominativo['Attributi']);
        $attr = array();
        if ($this->InviaStampa) {
            $attr['InviaStampa'] = $this->InviaStampa;
        }
        $MittenteSoapval = new soapval('com:Mittente', 'com:Mittente', $Nominativo, false, false, $attr);
        return $MittenteSoapval;
    }

}

?>
