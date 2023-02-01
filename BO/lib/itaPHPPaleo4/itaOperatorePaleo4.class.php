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
class itaOperatorePaleo4 {

    private $CodiceUO;
    private $Cognome;
    private $Nome;
    private $Ruolo;

    function __construct($CodiceUO="", $Cognome="", $Nome="", $Ruolo="") {
        if ($CodiceUO) {
            $this->CodiceUO = $CodiceUO;
        }
        if ($Cognome) {
            $this->Cognome = $Cognome;
        }
        if ($Nome) {
            $this->Nome = $Nome;
        }
        if ($Ruolo) {
            $this->Ruolo = $Ruolo;
        }

        }

 
//set()
    public function setCodiceUO($CodiceUO) {
        $this->CodiceUO = $CodiceUO;
    }

    public function setCognome($Cognome) {
        $this->Cognome = $Cognome;
    }

    public function setNome($Nome) {
        $this->Nome = $Nome;
    }

    public function setRuolo($Ruolo) {
        $this->Ruolo = $Ruolo;
    }

//get()
    public function getCodiceUO() {
        return $this->CodiceUO;
    }

    public function getCognome() {
        return $this->Cognome;
    }

    public function getNome() {
        return $this->Nome;
    }

    public function getRuolo() {
        return $this->Ruolo;
    }

    public function toArray() {
        return array(
            'CodiceUO' => $this->getCodiceUO(),
            'Cognome' => $this->getCognome(),
            'Nome' => $this->getNome(),
            'Ruolo' => $this->getRuolo()
        );
    }

}