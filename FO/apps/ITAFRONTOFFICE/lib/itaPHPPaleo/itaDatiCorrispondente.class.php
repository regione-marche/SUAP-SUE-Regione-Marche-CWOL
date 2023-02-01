<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of itaDatiCorrispondente
 *
 * @author Mario Mazza <mario.mazza@italsoft.eu>
 */

require_once(ITA_LIB_PATH . '/itaPHPPaleo/itaBABase.class.php');

class itaDatiCorrispondente {

    private $MessaggioRisultato; //oggetto dellaclasse BEBase
    private $Cognome;
    private $Email;
    private $IdFiscale;
    private $IstatComune;
    private $Nome;
    private $Tipo; //stringa del tipo TIPO VOCE RUBRICA


    function __construct($MessaggioRisultato=array('Descrizione'=>"",'TipoRisultato'=>""), $Cognome="", $Email="", $IdFiscale="", $IstatComune="", $Nome="", $Tipo="") {
        //if ($MessaggioRisultato) {
        $this->MessaggioRisultato = new BEBase();
        if ($MessaggioRisultato['Descrizione']!='') {
            $this->MessaggioRisultato->setDescrizione($MessaggioRisultato['Descrizione']);
        }
        if ($MessaggioRisultato['TipoRisultato']!='') {
            $this->MessaggioRisultato->setTipoRisultato($MessaggioRisultato['TipoRisultato']);
        }
        //}
        if ($Cognome) {
            $this->Cognome = $Cognome;
        }
        if ($Email) {
            $this->Email = $Email;
        }
        if ($IdFiscale) {
            $this->IdFiscale = $IdFiscale;
        }
        if ($IstatComune) {
            $this->IstatComune = $IstatComune;
        }
        if ($Nome) {
            $this->Nome = $Nome;
        }
        if ($Tipo) {
            $this->Tipo = $Tipo;
        }
    }
//set()
//    public function setMessaggioRisultato($MessaggioRisultato=array('Descrizione'=>"",'TipoRisultato'=>"")) {
//        if ($MessaggioRisultato['Descrizione']!='') {
//            $this->MessaggioRisultato->setDescrizione($MessaggioRisultato['Descrizione']);
//        }
//        if ($MessaggioRisultato['TipoRisultato']!='') {
//            $this->MessaggioRisultato->setTipoRisultato($MessaggioRisultato['TipoRisultato']);
//        }
//    }
    public function setCognome($Cognome) {
        $this->Cognome = $Cognome;
    }
    public function setEmail($Email) {
        $this->Email = $Email;
    }
    public function setIdFiscale($IdFiscale) {
        $this->IdFiscale = $IdFiscale;
    }
    public function setIstatComune($IstatComune) {
        $this->IstatComune = $IstatComune;
    }
    public function setNome($Nome) {
        $this->Nome = $Nome;
    }
    public function setTipo($Tipo) {
        $this->Tipo = $Tipo;
    }

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
}

?>
