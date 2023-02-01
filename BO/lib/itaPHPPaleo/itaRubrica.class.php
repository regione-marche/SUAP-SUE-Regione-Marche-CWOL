<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of itaRubrica
 *
 * @author Mario Mazza <mario.mazza@italsoft.eu>
 */
class itaRubrica {

    private $MessaggioRisultato; //array con parametri Descrizione, TipoRisultato
    private $Codice; //codice voce rubrica
    private $Cognome; //DatiCorrispondente
    private $Email; //DatiCorrispondente
    private $IdFiscale; //DatiCorrispondente
    private $IstatComune; //DatiCorrispondente
    private $Nome; //DatiCorrispondente
    private $Tipo; //DatiCorrispondente
    //private $DatiCorrispondente;
    /*
    function __construct($Codice="", $DatiCorrispondente=array()) {
        if ($Codice) {
            $this->Codice = $Codice;
        }
        if ($DatiCorrispondente) {
            $this->DatiCorrispondente = new itaDatiCorrispondente();
            
            $this->DatiCorrispondente = $DatiCorrispondente;
        }
    }
 *
    */

//set()
    public function setMessaggioRisultato($MessaggioRisultato) {
        if (($MessaggioRisultato['Descrizione']!='')&&($MessaggioRisultato['TipoRisultato']!='')) {
            $this->MessaggioRisultato = array(
                    'Descrizione'=>$MessaggioRisultato['Descrizione'],
                    'TipoRisultato'=>$MessaggioRisultato['TipoRisultato']
            );
        } else {
            $this->MessaggioRisultato = array(
                    'Descrizione'=>"",
                    'TipoRisultato'=>""
            );
        }
    }
    public function setCognome($Cognome) {
        $this->Cognome = $Cognome;
    }
    public function setEmail($Email) {
        $this->Email = $Email;
    }
    public function setIdFiscale($IdFiscale) {
        $this->IdFiscale = $Idiscale;
    }
    public function setIstatComune($IstatComune) {
        $this->Istatcomune = $IstatComune;
    }
    public function setNome($Nome) {
        $this->Nome = $Nome;
    }
    public function setTipo($Tipo) {
        $this->Tipo = $Tipo;
    }
    public function setCodice($Codice) {
        $this->Codice = $Codice;
    }

//    public function setDatiCorrispondente($DatiCorrispondente) {
//        $this->Descrizione = $DatiCorrispondente;
//    }

//get()
    public function getMessaggioRisultato() {
        return $this->MessaggioRisultato;
    }
    public function getCognome() {
        return $this->Cognome;
    }
    public function getEmail() {
        return $this->Email;
    }
    public function getIdFiscale() {
        return $this->IdFiscale;
    }
    public function getIstatComune() {
        return $this->IstatComune;
    }
    public function getNome() {
        return $this->Nome;
    }
    public function getTipo() {
        return $this->Tipo;
    }
    public function getCodice() {
        return $this->Codice;
    }
//    public function getDatiCorrispondente() {
//        return $this->DatiCorrispondente;
//    }
}

?>
