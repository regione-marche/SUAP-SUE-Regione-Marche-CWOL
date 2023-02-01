<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of itaUtentePaleo
 *
 * @author Mario Mazza <mario.mazza@italsoft.eu>
 */
class itareqFindRubrica {

    private $Codice;
    private $Descrizione;
    private $IdFiscale;
    private $IstatComune;
    private $Tipo;

    function __construct($Codice="", $Descrizione="", $IdFiscale="", $IstatComune="", $Tipo="") {
        if ($Codice) {
            $this->Codice = $Codice;
        }
        if ($Descrizione) {
            $this->Descrizione = $Descrizione;
        }
        if ($IdFiscale) {
            $this->IdFiscale = $IdFiscale;
        }
        if ($IstatComune) {
            $this->IstatComune = $IstatComune;
        }
        if ($Tipo) {
            $this->Tipo = $Tipo;
        }

    }
//set()
    public function setCodice($Codice) {
        $this->Codice = $Codice;
    }
    public function setDescrizione($Descrizione) {
        $this->Descrizione = $Descrizione;
    }
    public function setIdFiscale($IdFiscale) {
        $this->IdFiscale = $IdFiscale;
    }
    public function setIstatComune($IstatComune) {
        $this->IstatComune = $IstatComune;
    }
    public function setTipo($Tipo) {
        $this->Tipo = $Tipo;
    }

//get()
    public function getCodice() {
        return $this->Codice;
    }
    public function getDescrizione() {
        return $this->Descrizione;
    }
    public function getIdFiscale() {
        return $this->IdFiscale;
    }
    public function getIstatComune() {
        return $this->IstatComune;
    }
    public function getTipo() {
        return $this->Tipo;
    }
}

?>
