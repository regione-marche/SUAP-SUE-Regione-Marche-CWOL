<?php

/**
 *
 * CLASSE CONTROLLO CAMPI AGGIUNTIVI (PROVINCIA)
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
class praidcCtr002 {

    private $codice;
    private $descrizione;
    private $dizionario;

    public function __construct() {
        $this->setCodice("002");
        $this->setDescrizione("Provincia");
        $this->setDizionario(
                array(
                    array(
                        "NOMECAMPO" => "PROVINCIA",
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