<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of itaBEBase
 *
 * @author Mario Mazza <mario.mazza@italsoft.eu>
 */
class itaBEBase {

    private $Descrizione;
    private $TipoRisultato;


    function __construct($Descrizione="", $TipoRisultato="") {
        if ($Descrizione) {
            $this->Descrizione = $Descrizione;
        }
        if ($TipoRisultato) {
            $this->TipoRisultato = $TipoRisultato;
        }
    }
//set()
    public function setDescrizione($Descrizione) {
        $this->Descrizione = $Descrizione;
    }
    public function setTipoRisultato($TipoRisultato) {
        $this->Descrizione = $TipoRisultato;
    }

//get()
    public function getDescrizione() {
        return $this->Descrizione;
    }
    public function getTipoRisultato() {
        return $this->TipoRisultato;
    }
}

?>
