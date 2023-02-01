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
class itaCercaTitolarioHWS {

    private $categoria;    //optional
    private $classe;    //optional
    private $fascicolo;   //optional
    private $sottofascicolo;   //optional
    private $sottofascicolo2; //optional
    private $jdbc;

//set()
    public function setCategoria($categoria) {
        $this->categoria = $categoria;
    }
    public function setClasse($classe) {
        $this->classe = $classe;
    }
    public function setFascicolo($fascicolo) {
        $this->fascicolo = $fascicolo;
    }
    public function setSottofascicolo($sottofascicolo) {
        $this->sottofascicolo = $sottofascicolo;
    }
    public function setSottofascicolo2($sottofascicolo2) {
        $this->sottofascicolo2 = $sottofascicolo2;
    }
    public function setJDBC($jdbc) {
        $this->jdbc = $jdbc;
    }    

//get()
    public function getCategoria() {
        return $this->categoria;
    }
    public function getClasse() {
        return $this->classe;
    }
    public function getFascicolo() {
        return $this->fascicolo;
    }
    public function getSottofascicolo() {
        return $this->sottofascicolo;
    }
    public function getSottofascicolo2() {
        return $this->sottofascicolo2;
    }
    public function getJDBC() {
        return $this->jdbc;
    }    

}

?>
