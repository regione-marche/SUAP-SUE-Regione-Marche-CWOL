<?php

/**
 * Description of itaUtentePaleo
 *
 * @author Mario Mazza <mario.mazza@italsoft.eu>
 */
class itaCercaRubrica {

    private $descrizione;
    private $idfiscale;
    private $jdbc;

    function __construct($Descrizione="", $IdFiscale="") {
        if ($Descrizione) {
            $this->descrizione = $Descrizione;
        }
        if ($IdFiscale) {
            $this->idfiscale = $IdFiscale;
        }
    }
//set()
    public function setDescrizione($Descrizione) {
        $this->descrizione = $Descrizione;
    }
    public function setIdFiscale($IdFiscale) {
        $this->idfiscale = $IdFiscale;
    }
    public function setJDBC($jdbc) {
        $this->jdbc = $jdbc;
    }

//get()
    public function getDescrizione() {
        return $this->descrizione;
    }
    public function getIdFiscale() {
        return $this->idfiscale;
    }
    public function getJDBC() {
        return $this->jdbc;
    }
}

?>
