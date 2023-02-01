<?php

/**
 *
 * Classe per collegamento ws SICI Studio K
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPIride
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    30.05.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
class itaMittenteDestinatario {

    private $Email;
    private $Denominazione;
    private $CodiceAmministrazione;
    private $CodiceAOO;
    private $CognomeNome;
    private $CodiceFiscale;
    private $Dug;
    private $Toponimo;
    private $Civico;
    private $Cap;
    private $Comune;
    private $Provincia;
    private $Nazione;
    private $Flusso;

    function __construct($CodiceFiscale = "", $CognomeNome = "") {
        if ($CodiceFiscale) {
            $this->CodiceFiscale = $CodiceFiscale;
        }
        if ($CognomeNome) {
            $this->CognomeNome = $CognomeNome;
        }
    }

    public function getEmail() {
        return $this->Email;
    }

    public function setEmail($Email) {
        $this->Email = $Email;
    }

    public function getDenominazione() {
        return $this->Denominazione;
    }

    public function setDenominazione($Denominazione) {
        $this->Denominazione = $Denominazione;
    }

    public function getCodiceAmministrazione() {
        return $this->CodiceAmministrazione;
    }

    public function setCodiceAmministrazione($CodiceAmministrazione) {
        $this->CodiceAmministrazione = $CodiceAmministrazione;
    }

    public function getCodiceAOO() {
        return $this->CodiceAOO;
    }

    public function setCodiceAOO($CodiceAOO) {
        $this->CodiceAOO = $CodiceAOO;
    }

    public function getCognomeNome() {
        return $this->CognomeNome;
    }

    public function setCognomeNome($CognomeNome) {
        $this->CognomeNome = $CognomeNome;
    }

    public function getCodiceFiscale() {
        return $this->CodiceFiscale;
    }

    public function setCodiceFiscale($CodiceFiscale) {
        $this->CodiceFiscale = $CodiceFiscale;
    }

    public function getDug() {
        return $this->Dug;
    }

    public function setDug($Dug) {
        $this->Dug = $Dug;
    }

    public function getToponimo() {
        return $this->Toponimo;
    }

    public function setToponimo($Toponimo) {
        $this->Toponimo = $Toponimo;
    }

    public function getCivico() {
        return $this->Civico;
    }

    public function setCivico($Civico) {
        $this->Civico = $Civico;
    }

    public function getCap() {
        return $this->Cap;
    }

    public function setCap($Cap) {
        $this->Cap = $Cap;
    }

    public function getComune() {
        return $this->Comune;
    }

    public function setComune($Comune) {
        $this->Comune = $Comune;
    }

    public function getProvincia() {
        return $this->Provincia;
    }

    public function setProvincia($Provincia) {
        $this->Provincia = $Provincia;
    }

    public function getNazione() {
        return $this->Nazione;
    }

    public function setNazione($Nazione) {
        $this->Nazione = $Nazione;
    }

    public function getFlusso() {
        return $this->Flusso;
    }

    public function setFlusso($Flusso) {
        $this->Flusso = $Flusso;
    }

    public function getSoapValRequest($ns = 'tem:') {
        $soapvalArr = array();
        $soapvalArr[] = new soapval("{$ns}IndirizzoTelematico", "{$ns}IndirizzoTelematico", $this->Email, false, false);
        $soapvalArr[] = new soapval("{$ns}Denominazione", "{$ns}Denominazione", $this->Denominazione, false, false);
        $soapvalArr[] = new soapval("{$ns}CodiceAmministrazione", "{$ns}CodiceAmministrazione", $this->CodiceAmministrazione, false, false);
        $soapvalArr[] = new soapval("{$ns}CodiceAOO", "{$ns}CodiceAOO", $this->CodiceAOO, false, false);
        //
        $PersonaSoapvalArr = array();
        $PersonaSoapvalArr[] = new soapval("{$ns}Denominazione", "{$ns}Denominazione", $this->CognomeNome, false, false);
        $PersonaSoapvalArr[] = new soapval("{$ns}CodiceFiscale", "{$ns}CodiceFiscale", $this->CodiceFiscale, false, false);
        $soapvalArr[] = new soapval("{$ns}Persona", "{$ns}Persona", $PersonaSoapvalArr, false, false);
        //
        $IndirizzoPostaleSoapvalArr = array();
        $IndirizzoPostaleSoapvalArr[] = new soapval("{$ns}Dug", "{$ns}Dug", $this->Dug, false, false);
        $IndirizzoPostaleSoapvalArr[] = new soapval("{$ns}Toponimo", "{$ns}Toponimo", $this->Toponimo, false, false);
        $IndirizzoPostaleSoapvalArr[] = new soapval("{$ns}Civico", "{$ns}Civico", $this->Civico, false, false);
        $IndirizzoPostaleSoapvalArr[] = new soapval("{$ns}CAP", "{$ns}CAP", $this->Cap, false, false);
        $IndirizzoPostaleSoapvalArr[] = new soapval("{$ns}Comune", "{$ns}Comune", $this->Comune, false, false);
        $IndirizzoPostaleSoapvalArr[] = new soapval("{$ns}Provincia", "{$ns}Provincia", $this->Provincia, false, false);
        $IndirizzoPostaleSoapvalArr[] = new soapval("{$ns}Nazione", "{$ns}Nazione", $this->Nazione, false, false);//Codice Nazione(IT)
        $soapvalArr[] = new soapval("{$ns}IndirizzoPostale", "{$ns}IndirizzoPostale", $IndirizzoPostaleSoapvalArr, false, false);
        //
        if($this->Flusso == "E"){
            $mittDest = "Mittente";
        }else{
            $mittDest = "Destinatario";
        }
//        if (count($soapvalArr) == 1) {
//            $MittenteDestinatarioSoapval = new soapval("{$ns}$mittDest", "{$ns}$mittDest", $soapvalArr[0], false, false);
//        } else {
//            $MittenteDestinatarioSoapval = new soapval("{$ns}$mittDest", "{$ns}$mittDest", $soapvalArr, false, false);
//        }
        $MittenteDestinatarioSoapval = new soapval("{$ns}$mittDest", "{$ns}$mittDest", $soapvalArr, false, false);
        return $MittenteDestinatarioSoapval;
    }

}

?>
