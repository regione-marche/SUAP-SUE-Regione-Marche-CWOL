<?php

/**
 *
 * CLASSE CONTROLLO CAMPI AGGIUNTIVI (MAIL)
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
class praidcCtr006 {

    private $codice;
    private $descrizione;
    private $dizionario;

    public function __construct() {
        $this->setCodice("006");
        $this->setDescrizione("Mail");
        $this->setDizionario(
                array(
                    array(
                        "NOMECAMPO" => "MAIL",
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