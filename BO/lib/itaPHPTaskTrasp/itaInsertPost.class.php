<?php

/**
 *
 * Classe per collegamento Task services
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaTask
 * @author     Paolo Rosati <paolo.rosati@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    21.02.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

class itaInsertPost {

    private $token;
    private $valori;

//set()    
    public function setToken($token) {
        $this->token = $token;
    }

    public function setValori($valori) {
        $this->valori = $valori;
    }

//get()
    public function getToken() {
        return $this->token;
    }

    public function getValori() {
        return $this->valori;
    }

    public function getRichiesta() {
        $TokenSoapval = new soapval('token', 'token', $this->token, false, false);
        $ValoriSoapval = new soapval('lista_valori', 'lista_valori', $this->valori, false, false);
        $paramL = $TokenSoapval->serialize() . $ValoriSoapval->serialize();
        return $paramL;
    }

}

?>
