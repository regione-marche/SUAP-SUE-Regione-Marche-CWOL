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

class itaDestinatario {

    private $Nominativo = array();
    private $Bollettino = array();
    private $Bollettini = array();
    private $DettaglioCover = array();
    private $IdRicevuta;
    private $IdDestinatario;

    function __construct($Nominativo = array(), $Bollettino = array(), $Bollettini = array(), $DettaglioCover = array()) {
        if ($Nominativo) {
            $this->Nominativo = $Nominativo;
        }
        if ($Bollettino) {
            $this->Bollettino = $Bollettino;
        }
        if ($Bollettini) {
            $this->Bollettini = $Bollettini;
        }
        if ($DettaglioCover) {
            $this->DettaglioCover = $DettaglioCover;
        }
    }

//set()
    public function setNominativo($Nominativo) {
        $this->Nominativo = $Nominativo;
    }

    public function setBollettino($Bollettino) {
        $this->Bollettino = $Bollettino;
    }

    public function setBollettini($Bollettini) {
        $this->Bollettini = $Bollettini;
    }

    public function setDettaglioCover($DettaglioCover) {
        $this->DettaglioCover = $DettaglioCover;
    }

    public function setIdRicevuta($IdRicevuta) {
        $this->IdRicevuta = $IdRicevuta;
    }

    public function setIdDestinatario($IdDestinatario) {
        $this->IdDestinatario = $IdDestinatario;
    }

//get()
    public function getNominativo() {
        return $this->Nominativo;
    }

    public function getBollettino() {
        return $this->Bollettino;
    }

    public function getBollettini() {
        return $this->Bollettini;
    }

    public function getDettaglioCover() {
        return $this->DettaglioCover;
    }

    public function getIdRicevuta() {
        return $this->IdRicevuta;
    }

    public function getIdDestinatario() {
        return $this->IdDestinatario;
    }

    public function getSoapValRequest() {
        $soapvalArr = array();
        //NOMINATIVO
        if ($this->Nominativo) {
            $Indirizzo = "";
            $Indirizzo = new soapval('com1:Indirizzo', 'com1:Indirizzo', "", false, false, $this->Nominativo['Indirizzo']['Attributi']);
            $Nominativo = new soapval('com1:Nominativo', 'com1:Nominativo', $Indirizzo, false, false, $this->Nominativo['Attributi']);
            $soapvalArr[] = $Nominativo;
        }
        //BOLLETTINO
        if ($this->Bollettino) {
            $Bollettino = new soapval('com1:Bollettino', 'com1:Bollettino', "", false, false);
            $soapvalArr[] = $Bollettino;
        }
        //BOLLETTINI
        if ($this->Bollettini) {
            $Bollettini = new soapval('com1:Bollettini', 'Bollettini', "", false, false);
            $soapvalArr[] = $Bollettini;
        }
        //DETTAGLIO COVER
        if ($this->DettaglioCover) {
            $DettaglioCover = new soapval('com1:DettaglioCover', 'com1:DettaglioCover', "", false, false);
            $soapvalArr[] = $DettaglioCover;
        }
        $attr = array();
        //if ($this->IdDestinatario){
            $attr['IdDestinatario'] = $this->IdDestinatario;
        //}
        if ($this->IdRicevuta){
            $attr['IdRicevuta'] = $this->IdRicevuta;
        }
        if (count($soapvalArr) == 1) {
            $DestinatarioSoapval = new soapval('com:Destinatario', 'com:Destinatario', $soapvalArr[0], false, false, $attr);
        } else {
            $DestinatarioSoapval = new soapval('com:Destinatario', 'com:Destinatario', $soapvalArr, false, false, $attr);
        }
        return $DestinatarioSoapval;
    }

}

?>
