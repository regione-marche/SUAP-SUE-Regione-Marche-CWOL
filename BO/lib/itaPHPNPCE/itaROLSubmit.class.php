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

class itaROLSubmit {

    private $Mittente = array();
    private $DatiRicevuta = array();
    private $Destinatari = array();
    private $NumeroDestinatari;
    private $Documenti = array();
    private $TestataCover = array();
    private $Opzioni = array();
    private $ServiziAggiuntivi = array();
    private $com;
    private $com1;

    function __construct($Mittente = array(), $DatiRicevuta = array(), $Destinatari = array(), $NumeroDestinatari = "", $Documenti = array(), $TestataCover = array(), $Opzioni = array(), $ServiziAggiuntivi = array()) {
        if ($Mittente) {
            $this->Mittente = $Mittente;
        }
        if ($DatiRicevuta) {
            $this->DatiRicevuta = $DatiRicevuta;
        }
        if ($Destinatari) {
            $this->Destinatari = $Destinatari;
        }
        if ($NumeroDestinatari) {
            $this->NumeroDestinatari = $NumeroDestinatari;
        }
        if ($Documenti) {
            $this->Documenti = $Documenti;
        }
        if ($TestataCover) {
            $this->TestataCover = $TestataCover;
        }
        if ($Opzioni) {
            $this->Opzioni = $Opzioni;
        }
        if ($ServiziAggiuntivi) {
            $this->ServiziAggiuntivi = $ServiziAggiuntivi;
        }
    }

//set()
    public function setMittente($Mittente) {
        $this->Mittente = $Mittente;
    }

    public function setDatiRicevuta($DatiRicevuta) {
        $this->DatiRicevuta = $DatiRicevuta;
    }

    public function setDestinatari($Destinatari) {
        $this->Destinatari = $Destinatari;
    }

    public function setNumeroDestinatari($NumeroDestinatari) {
        $this->NumeroDestinatari = $NumeroDestinatari;
    }

    public function setDocumenti($Documenti) {
        $this->Documenti = $Documenti;
    }

    public function setTestataCover($TestataCover) {
        $this->TestataCover = $TestataCover;
    }

    public function setOpzioni($Opzioni) {
        $this->Opzioni = $Opzioni;
    }

    public function setServiziAggiuntivi($ServiziAggiuntivi) {
        $this->ServiziAggiuntivi = $ServiziAggiuntivi;
    }

    public function setNSCom($com) {
        $this->com = $com;
    }

    public function setNSCom1($com1) {
        $this->com1 = $com1;
    }

//get()
    public function getMittente() {
        return $this->Mittente;
    }

    public function getDatiRicevuta() {
        return $this->DatiRicevuta;
    }

    public function getDestinatari() {
        return $this->Destinatari;
    }

    public function getNumeroDestinatari() {
        return $this->NumeroDestinatari;
    }

    public function getDocumenti() {
        return $this->Documenti;
    }

    public function getTestataCover() {
        return $this->TestataCover;
    }

    public function getOpzioni() {
        return $this->Opzioni;
    }

    public function getServiziAggiuntivi() {
        return $this->ServiziAggiuntivi;
    }

    public function getNSCom() {
        return $this->com;
    }

    public function getNSCom1() {
        return $this->com1;
    }

    public function getSoapValRequest() {
        $soapvalArr = array();
        //MITTENTE - Optional
        if ($this->Mittente) {
            if ($this->Mittente instanceof itaMittente) {
                //bisognerebbe sempre passare un oggetto itaMittente alal classe itaROLSubmit
                $soapvalArr[] = $this->Mittente->getSoapValRequest();
            } else {
                //si potrebbe togliere, la chiamata dovrebbe essere sempre con un'istanza di itaMittente
                $Indirizzo = "";
                $Indirizzo = new soapval('com1:Indirizzo', 'com1:Indirizzo', "", false, false, $this->Mittente['Indirizzo']['Attributi']);
                $Nominativo = new soapval('com1:Nominativo', 'com1:Nominativo', $Indirizzo, false, false, $this->Mittente['Attributi']);
                $soapvalArr[] = new soapval('com:Mittente', 'com:Mittente', $Nominativo, false, false);
            }
        }
        //DATI RICEVUTA - Optional
        if ($this->DatiRicevuta) {
            App::log('dati ricevuta');
            App::log($this->DatiRicevuta);
            $soapvalArr[] = $this->DatiRicevuta->getSoapValRequest();
        }
        //DESTINATARI - Optional
        $Destinatari = array();
        if ($this->Destinatari) {
            if (is_array($this->Destinatari)) {
                foreach ($this->Destinatari as $Destinatario) {
//                    if ($Destinatario instanceof itaDestinatario) {
                    $Destinatari[] = $Destinatario->getSoapValRequest();
//                    }
                }
            }
            $Destinatari = new soapval('com:Destinatari', 'com:Destinatari', $Destinatari, false, false);
            $soapvalArr[] = $Destinatari;
        }
        //NUMERO DESTINATARI - Obbligatorio
        if ($this->NumeroDestinatari) {
            $NumeroDestinatari = new soapval('com:NumeroDestinatari', 'com:NumeroDestinatari', $this->NumeroDestinatari, false, false);
            $soapvalArr[] = $NumeroDestinatari;
        }
        //DOCUMENTO - Zero or more
        if ($this->Documenti) {
            foreach ($this->Documenti as $Documento) {
                $Immagine = new soapval('com1:Immagine', 'com1:Immagine', $Documento['Immagine'], false, false);
                $MD5 = new soapval('com1:MD5', 'com1:MD5', $Documento['MD5'], false, false);
                $DocumentoSoapval = new soapval('com:Documento', 'com:Documento', array($Immagine, $MD5), false, false, $Documento['Attributi']);
                $soapvalArr[] = $DocumentoSoapval;
            }
//            $Immagine = new soapval('com1:Immagine', 'com1:Immagine', $this->Documento['Immagine'], false, false);
//            $MD5 = new soapval('com1:MD5', 'com1:MD5', $this->Documento['MD5'], false, false);
//            $Documento = new soapval('com:Documento', 'com:Documento', array($Immagine, $MD5), false, false, $this->Documento['Attributi']);
//            $soapvalArr[] = $Documento;
        }
        //TESTATA COVER - Optional
        if ($this->TestataCover) {
            $TestataCover = new soapval('com:TestataCover', 'com:TestataCover', $this->TestataCover, false, false);
            $soapvalArr[] = $TestataCover;
        }
        //OPZIONI - Optional
        if ($this->Opzioni) {
            $OpzionidiStampaSoapval = new soapval('com:OpzionidiStampa', 'com:OpzionidiStampa', "", false, false, $this->Opzioni['OpzionidiStampa']['Attributi']);
            $OpzioniSoapval = new soapval('com:Opzioni', 'com:Opzioni', $OpzionidiStampaSoapval, false, false, $this->Opzioni['Attributi']);
            $soapvalArr[] = $OpzioniSoapval;
        }
        //SERVIZI AGGIUNTIVI - Optional
        if ($this->ServiziAggiuntivi) {
            $ServiziAggiuntivi = new soapval('com:ServiziAggiuntivi', 'com:ServiziAggiuntivi', $this->ServiziAggiuntivi, false, false);
            $soapvalArr[] = $ServiziAggiuntivi;
        }
        if (count($soapvalArr) == 1) {
            $ROLSubmitSoapval = new soapval('com:ROLSubmit', 'com:ROLSubmit', $soapvalArr[0], false, false);
        } else {
            $ROLSubmitSoapval = new soapval('com:ROLSubmit', 'com:ROLSubmit', $soapvalArr, false, false, array());
        }
        return $ROLSubmitSoapval;
    }

}

?>
