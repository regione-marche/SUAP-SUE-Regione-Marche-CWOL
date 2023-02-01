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

class itaGetType {

    private $token;
    private $type;
    private $def;

//set()    
    public function setToken($token) {
        $this->token = $token;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function setDef($def) {
        $this->def = $def;
    }

//get()
    public function getToken() {
        return $this->token;
    }

    public function getType() {
        return $this->type;
    }

    public function getDef() {
        return $this->def;
    }

    public function getRichiesta() {
        $TokenSoapval = new soapval('token', 'token', $this->token, false, false);
        $TypeSoapval = new soapval('type', 'type', $this->type, false, false);
        $DefSoapval = new soapval('def', 'def', $this->def, false, false);
        $paramL = $TokenSoapval->serialize() . $TypeSoapval->serialize() . $DefSoapval->serialize();
        return $paramL;
    }

}

?>
