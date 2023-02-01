<?php

/**
 *
 * Classe per collegamento ws IRIDE
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPIride
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    16.01.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
class itaInputStructInsertProtocolloGenerale {

    private $protocollo;
    private $mittente;
    private $destinatario;

    function __construct($protocollo = "", $mittente = "", $destinatario = "") {
        if ($protocollo) {
            $this->protocollo = $protocollo;
        }
        if ($mittente) {
            $this->mittente = $mittente;
        }
        if ($destinatario) {
            $this->destinatario = $destinatario;
        }
    }

    public function getProtocollo() {
        return $this->protocollo;
    }

    public function getMittente() {
        return $this->mittente;
    }

    public function getDestinatario() {
        return $this->destinatario;
    }

    public function setProtocollo($protocollo) {
        $this->protocollo = $protocollo;
    }

    public function setMittente($mittente) {
        $this->mittente = $mittente;
    }

    public function setDestinatario($destinatario) {
        $this->destinatario = $destinatario;
    }

    public function getSoapValRequest($ns = 'tem:', $emptyTags = false) {
        $soapvalArr = array();
        /*
         * STRUTTURA
         * 
         * <protocolli>
         *  <protocollo>
         *  <mittente>
         *  <destinatario>
         * </protocolli>
         */
        $soapvalArr[] = new soapval("{$ns}protocollo", "{$ns}protocollo", $this->protocollo, false, false);
        $soapvalArr[] = new soapval("{$ns}mittente", "{$ns}mittente", $this->mittente, false, false);
        $soapvalArr[] = new soapval("{$ns}destinatario", "{$ns}destinatario", $this->destinatario, false, false);

        if (count($soapvalArr) == 1) {
            $dsDatiSoapval = new soapval("{$ns}dsDati", "{$ns}dsDati", $soapvalArr[0], false, false);
        } else {
            $dsDatiSoapval = new soapval("{$ns}dsDati", "{$ns}dsDati", $soapvalArr, false, false);
        }
        return $dsDatiSoapval;
    }

    public function getXmlRequest($ns = 'tem:', $emptyTags = false) {
        $dsDatiXml = "<![CDATA[<?xml version='1.0' encoding='UTF-8'?><tem:protocolli>";
        //protocollo
        if ($this->protocollo) {
            $dsDatiXml .= "<protocollo>";
            $protocolloStr = "";
            if ($this->protocollo['tipologia']) {
                $protocolloStr .= "<tipologia>" . $this->protocollo['tipologia'] . "</tipologia>";
            }
            if ($this->protocollo['oggetto']) {
                $protocolloStr .= "<oggetto>" . $this->protocollo['oggetto'] . "</oggetto>";
            }
            if ($this->protocollo['classificazione']) {
                $protocolloStr .= "<classificazione>" . $this->protocollo['classificazione'] . "</classificazione>";
            }
            if ($this->protocollo['classificazione']) {
                $protocolloStr .= "<classificazione>" . $this->protocollo['classificazione'] . "</classificazione>";
            }
            $dsDatiXml .= "</protocollo>";
        }
        $dsDatiXml .= "</protocolli>]]>";
    }

}

?>
