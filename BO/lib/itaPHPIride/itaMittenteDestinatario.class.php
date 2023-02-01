<?php

/**
 *
 * Classe per collegamento ws IRIDE
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPIride
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    16.01.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
class itaMittenteDestinatario {

    private $CodiceFiscale;
    private $CognomeNome;
    private $Nome;
    private $Indirizzo;
    private $Localita;
    private $CodiceComuneResidenza;
    private $DataNascita;
    private $CodiceComuneNascita;
    private $Nazionalita;
    private $DataInvio_DataProt;
    private $Spese_NProt;
    private $Mezzo;
    private $DataRicevimento;
    private $TipoSogg;
    private $TipoPersona;
    private $Recapiti;

    function __construct($CodiceFiscale = "", $CognomeNome = "") {
        if ($CodiceFiscale) {
            $this->CodiceFiscale = $CodiceFiscale;
        }
        if ($CognomeNome) {
            $this->CognomeNome = $CognomeNome;
        }
    }

    public function getCodiceFiscale() {
        return $this->CodiceFiscale;
    }

    public function setCodiceFiscale($CodiceFiscale) {
        $this->CodiceFiscale = $CodiceFiscale;
    }

    public function getCognomeNome() {
        return $this->CognomeNome;
    }

    public function setCognomeNome($CognomeNome) {
        $this->CognomeNome = $CognomeNome;
    }

    public function getNome() {
        return $this->Nome;
    }

    public function setNome($Nome) {
        $this->Nome = $Nome;
    }

    public function getIndirizzo() {
        return $this->Indirizzo;
    }

    public function setIndirizzo($Indirizzo) {
        $this->Indirizzo = $Indirizzo;
    }

    public function getLocalita() {
        return $this->Localita;
    }

    public function setLocalita($Localita) {
        $this->Localita = $Localita;
    }

    public function getCodiceComuneResidenza() {
        return $this->CodiceComuneResidenza;
    }

    public function setCodiceComuneResidenza($CodiceComuneResidenza) {
        $this->CodiceComuneResidenza = $CodiceComuneResidenza;
    }

    public function getDataNascita() {
        return $this->DataNascita;
    }

    public function setDataNascita($DataNascita) {
        $this->DataNascita = $DataNascita;
    }

    public function getCodiceComuneNascita() {
        return $this->CodiceComuneNascita;
    }

    public function setCodiceComuneNascita($CodiceComuneNascita) {
        $this->CodiceComuneNascita = $CodiceComuneNascita;
    }

    public function getNazionalita() {
        return $this->Nazionalita;
    }

    public function setNazionalita($Nazionalita) {
        $this->Nazionalita = $Nazionalita;
    }

    public function getDataInvio_DataProt() {
        return $this->DataInvio_DataProt;
    }

    public function setDataInvio_DataProt($DataInvio_DataProt) {
        $this->DataInvio_DataProt = $DataInvio_DataProt;
    }

    public function getSpese_NProt() {
        return $this->Spese_NProt;
    }

    public function setSpese_NProt($Spese_NProt) {
        $this->Spese_NProt = $Spese_NProt;
    }

    public function getMezzo() {
        return $this->Mezzo;
    }

    public function setMezzo($Mezzo) {
        $this->Mezzo = $Mezzo;
    }

    public function getDataRicevimento() {
        return $this->DataRicevimento;
    }

    public function setDataRicevimento($DataRicevimento) {
        $this->DataRicevimento = $DataRicevimento;
    }

    public function getTipoSogg() {
        return $this->TipoSogg;
    }

    public function setTipoSogg($TipoSogg) {
        $this->TipoSogg = $TipoSogg;
    }

    public function getTipoPersona() {
        return $this->TipoPersona;
    }

    public function setTipoPersona($TipoPersona) {
        $this->TipoPersona = $TipoPersona;
    }

    public function getRecapiti() {
        return $this->Recapiti;
    }

    public function setRecapiti($Recapiti) {
        $this->Recapiti = $Recapiti;
    }

    public function getSoapValRequest($ns = 'tem:', $emptyTags = false) {
        $soapvalArr = array();
        $soapvalArr[] = new soapval("{$ns}CodiceFiscale", "{$ns}CodiceFiscale", $this->CodiceFiscale, false, false);

        $soapvalArr[] = new soapval("{$ns}CognomeNome", "{$ns}CognomeNome", $this->CognomeNome, false, false);
        if ($this->Nome || $emptyTags) {
            $soapvalArr[] = new soapval("{$ns}Nome", "{$ns}Nome", $this->Nome, false, false);
        }else{
            $soapvalArr[] = new soapval("{$ns}Nome", "{$ns}Nome", " ", false, false);
        }
        if ($this->Indirizzo || $emptyTags) {
            $soapvalArr[] = new soapval("{$ns}Indirizzo", "{$ns}Indirizzo", $this->Indirizzo, false, false);
        }
        if ($this->Localita || $emptyTags) {
            $soapvalArr[] = new soapval("{$ns}Localita", "{$ns}Localita", $this->Localita, false, false);
        }
        if ($this->CodiceComuneResidenza || $emptyTags) {
            $soapvalArr[] = new soapval("{$ns}CodiceComuneResidenza", "{$ns}CodiceComuneResidenza", $this->CodiceComuneResidenza, false, false);
        }
        if ($this->DataNascita || $emptyTags) {
            $soapvalArr[] = new soapval("{$ns}DataNascita", "{$ns}DataNascita", $this->DataNascita, false, false);
        }
        if ($this->CodiceComuneNascita || $emptyTags) {
            $soapvalArr[] = new soapval("{$ns}CodiceComuneNascita", "{$ns}CodiceComuneNascita", $this->CodiceComuneNascita, false, false);
        }
        if ($this->Nazionalita || $emptyTags) {
            $soapvalArr[] = new soapval("{$ns}Nazionalita", "{$ns}Nazionalita", $this->Nazionalita, false, false);
        }
        if ($this->DataInvio_DataProt || $emptyTags) {
            $soapvalArr[] = new soapval("{$ns}DataInvio_DataProt", "{$ns}DataInvio_DataProt", $this->DataInvio_DataProt, false, false);
        }
        if ($this->Spese_NProt || $emptyTags) {
            $soapvalArr[] = new soapval("{$ns}Spese_NProt", "{$ns}Spese_NProt", $this->Spese_NProt, false, false);
        }
        if ($this->Mezzo || $emptyTags) {
            $soapvalArr[] = new soapval("{$ns}Mezzo", "{$ns}Mezzo", $this->Mezzo, false, false);
        }
        if ($this->DataRicevimento || $emptyTags) {
            $soapvalArr[] = new soapval("{$ns}DataRicevimento", "{$ns}DataRicevimento", $this->DataRicevimento, false, false);
        }
        if ($this->TipoSogg || $emptyTags) {
            $soapvalArr[] = new soapval("{$ns}TipoSogg", "{$ns}TipoSogg", $this->TipoSogg, false, false);
        }
        if ($this->TipoPersona || $emptyTags) {
            $soapvalArr[] = new soapval("{$ns}TipoPersona", "{$ns}TipoPersona", $this->TipoPersona, false, false);
        }
        if ($this->Recapiti) {
            $RecapitiSoapvalArr = array();
            foreach ($this->Recapiti as $recapito) {
                $RecapitoSoapvalArr = array();
                if (isset($recapito['TipoRecapito'])) {
                    $RecapitoSoapvalArr[] = new soapval("{$ns}TipoRecapito", "{$ns}TipoRecapito", $recapito['TipoRecapito'], false, false);
                }
                if (isset($recapito['ValoreRecapito'])) {
                    $RecapitoSoapvalArr[] = new soapval("{$ns}ValoreRecapito", "{$ns}ValoreRecapito", $recapito['ValoreRecapito'], false, false);
                }
                $RecapitiSoapvalArr[] = new soapval("{$ns}Recapito", "{$ns}Recapito", $RecapitoSoapvalArr, false, false);
            }
            $soapvalArr[] = new soapval("{$ns}Recapiti", "{$ns}Recapiti", $RecapitiSoapvalArr, false, false);
        } else {
            if ($emptyTags) {
                $RecapitiSoapvalArr = array();
                $soapvalArr[] = new soapval("{$ns}Recapiti", "{$ns}Recapiti", $RecapitiSoapvalArr, false, false);                
            }
        }
        if (count($soapvalArr) == 1) {
            $MittenteDestinatarioSoapval = new soapval("{$ns}MittenteDestinatario", "{$ns}MittenteDestinatario", $soapvalArr[0], false, false);
        } else {
            $MittenteDestinatarioSoapval = new soapval("{$ns}MittenteDestinatario", "{$ns}MittenteDestinatario", $soapvalArr, false, false);
        }
        return $MittenteDestinatarioSoapval;
    }

}

?>
