<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of itareqArchiviaDocInterno
 *
 * @author Andrea Bufarini <andrea.bufarini@italsoft.eu>
 */
class itareqArchiviaDocInterno {

    private $Operatore;
    private $CodiceRegistro;
    private $Oggetto;
    private $Privato;
    private $DPAI;
    private $DataArrivo;
    private $Mittente;
    private $Trasmissione;
    private $Classificazioni;
    private $Emergenza;
    private $DocumentoPrincipale;
    private $DocumentiAllegati;

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
    public function setOperatore($Operatore) {
        $this->Operatore = $Operatore;
    }
    public function setCodiceRegistro($CodiceRegistro) {
        $this->CodiceRegistro = $CodiceRegistro;
    }
    public function setOggetto($Oggetto) {
        $this->Oggetto = $Oggetto;
    }
    public function setPrivato($Privato) {
        $this->Privato = $Privato;
    }
    public function setDPAI($DPAI) {
        $this->DPAI = $DPAI;
    }
    public function setDataArrivo($DataArrivo) {
        $this->DataArrivo = $DataArrivo;
    }
    public function setMittente($Mittente) {
        $this->Mittente = $Mittente;
    }
    public function setTrasmissione($Trasmissione) {
        $this->Trasmissione = $Trasmissione;
    }
    public function setClassificazioni($Classificazioni) {
        $this->Classificazioni = $Classificazioni;
    }
    public function setEmergenza($Emergenza) {
        $this->Emergenza = $Emergenza;
    }
    public function setDocumentoPrincipale($DocumentoPrincipale) {
        $this->DocumentoPrincipale = $DocumentoPrincipale;
    }
    public function setDocumentiAllegati($DocumentiAllegati) {
        $this->DocumentiAllegati = $DocumentiAllegati;
    }

//    public function setDatiCorrispondente($DatiCorrispondente) {
//        $this->Descrizione = $DatiCorrispondente;
//    }

//get()
    public function getOperatore() {
        return $this->Operatore;
    }
    public function getCodiceRegistro() {
        return $this->CodiceRegistro;
    }
    public function getOggetto() {
        return $this->Oggetto;
    }
    public function getPrivato() {
        return $this->Privato;
    }
    public function getDPAI() {
        return $this->DPAI;
    }
    public function getDataArrivo() {
        return $this->DataArrivo;
    }
//    public function getMittente() {
//        return $this->Mittente;
//    }
    public function getTrasmissione() {
        return $this->Trasmissione;
    }
    public function getClassificazioni() {
        return $this->Classificazioni;
    }
//    public function getEmergenza() {
//        return $this->Emergenza;
//    }
    public function getDocumentoPrincipale() {
        return $this->DocumentoPrincipale;
    }
    public function getDocumentiAllegati() {
        return $this->DocumentiAllegati;
    }

//    public function getDatiCorrispondente() {
//        return $this->DatiCorrispondente;
//    }
}

?>
