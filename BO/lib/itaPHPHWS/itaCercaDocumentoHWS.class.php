<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaCercaDocumentoProtocollo
 *
 * @author michele
 */
class itaCercaDocumentoHWS {

    private $annoCompetenza;    //optional
    private $aoo;   //optional
    private $numeroDocumento;   //optional
    private $segnatura; //optional
    private $tipoProtocollo;    //optional
    private $jdbc;

//set()
    public function setAnnoCompetenza($annoCompetenza) {
        $this->annoCompetenza = $annoCompetenza;
    }
    public function setAoo($aoo) {
        $this->aoo = $aoo;
    }
    public function setNumeroDocumento($numeroDocumento) {
        $this->numeroDocumento = $numeroDocumento;
    }
    public function setSegnatura($segnatura) {
        $this->segnatura = $segnatura;
    }
    public function setTipoProtocollo($tipoProtocollo) {
        $this->tipoProtocollo= $tipoProtocollo;
    }
    public function setJDBC($jdbc) {
        $this->jdbc = $jdbc;
    }    

//get()
    public function getAnnoCompetenza() {
        return $this->annoCompetenza;
    }
    public function getAoo() {
        return $this->aoo;
    }
    public function getNumeroDocumento() {
        return $this->numeroDocumento;
    }
    public function getSegnatura() {
        return $this->segnatura;
    }
    public function getTipoProtocollo() {
        return $this->tipoProtocollo;
    }
    public function getJDBC() {
        return $this->jdbc;
    }    

}

?>
