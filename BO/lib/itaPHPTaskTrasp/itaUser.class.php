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

class itaUser {

    private $token;
    private $email;

//set()    
    public function setToken($token) {
        $this->token = $token;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

//get()
    public function getToken() {
        return $this->token;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getRichiesta() {
        $TokenSoapval = new soapval('token', 'token', $this->token, false, false);
        $EmailSoapval = new soapval('email', 'email', $this->email, false, false);
        $paramL = $TokenSoapval->serialize() . $EmailSoapval->serialize();
        return $paramL;
    }

}

?>
