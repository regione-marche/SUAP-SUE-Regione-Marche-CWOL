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

    private $cap;
    private $citta;
    private $codice; //obbligatorio!
    private $codiceFiscale;
    private $cognome;
    private $dataNascita;
    private $email;
    private $fax;
    private $indirizzo;
    private $nome;
    private $partitaIva;
    private $prov;
    private $ragioneSociale;
    private $telefono;
    private $jdbc;
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
    public function setCap($cap) {
        $this->cap = $cap;
    }
    public function setCitta($citta) {
        $this->citta = $citta;
    }
    public function setCodice($codice) {
        $this->codice = $codice;
    }
    public function setCodiceFiscale($codiceFiscale) {
        $this->codiceFiscale = $codiceFiscale;
    }
    public function setCognome($cognome) {
        $this->cognome = $cognome;
    }
    public function setDataNascita($dataNascita) {
        $this->dataNascita = $dataNascita;
    }
    public function setEmail($email) {
        $this->email = $email;
    }
    public function setFax($fax) {
        $this->fax = $fax;
    }
    public function setIndirizzo($indirizzo) {
        $this->indirizzo = $indirizzo;
    }
    public function setNome($nome) {
        $this->nome = $nome;
    }
    public function setPartitaIva($partitaIva) {
        $this->partitaIva = $partitaIva;
    }
    public function setProv($prov) {
        $this->prov = $prov;
    }
    public function setRagioneSociale($ragioneSociale) {
        $this->ragioneSociale = $ragioneSociale;
    }
    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }
    public function setJDBC($jdbc) {
        $this->jdbc = $jdbc;
    }    

//    public function setDatiCorrispondente($DatiCorrispondente) {
//        $this->Descrizione = $DatiCorrispondente;
//    }

//get()
    public function getCap() {
        return $this->cap;
    }
    public function getCitta() {
        return $this->citta;
    }
    public function getCodice() {
        return $this->codice;
    }
    public function getCodiceFiscale() {
        return $this->codiceFiscale;
    }
    public function getCognome() {
        return $this->cognome;
    }
    public function getDataNascita() {
        return $this->dataNascita;
    }
    public function getEmail() {
        return $this->email;
    }
    public function getFax() {
        return $this->fax;
    }
    public function getIndirizzo() {
        return $this->indirizzo;
    }
    public function getNome() {
        return $this->nome;
    }
    public function getPartitaIva() {
        return $this->partitaIva;
    }
    public function getProv() {
        return $this->prov;
    }
    public function getRagioneSociale() {
        return $this->ragioneSociale;
    }
    public function getTelefono() {
        return $this->telefono;
    }
    public function getJDBC() {
        return $this->jdbc;
    }    
    
}

?>
