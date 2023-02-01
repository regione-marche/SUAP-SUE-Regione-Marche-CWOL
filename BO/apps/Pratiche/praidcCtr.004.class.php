<?php

/**
 *
 * CLASSE CONTROLLO CAMPI AGGIUNTIVI (CODICE FISCALE)
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Michele Moscioni <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    23.10.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
class praidcCtr004 {

    private $codice;
    private $descrizione;
    private $dizionario;

    public function __construct() {
        $this->setCodice("004");
        $this->setDescrizione("Fiscale");
        $this->setDizionario(
                array(
                    array(
                        "NOMECAMPO" => "FISCALE",
                        "DESCRIZIONECAMPO" => $this->getDescrizione(),
                        "VARIABILE" => "",
                    )
                )
        );
    }

    public function getCodice() {
        return $this->codice;
    }

    public function setCodice($codice) {
        $this->codice = $codice;
    }

    public function getDescrizione() {
        return $this->descrizione;
    }

    public function setDescrizione($desc) {
        $this->descrizione = $desc;
    }

    public function getDizionario() {
        return $this->dizionario;
    }

    public function setDizionario($dizionario) {
        $this->dizionario = $dizionario;
    }

}

?>