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

class itaGetMetaValue {

    private $token;
    private $meta;

//set()    
    public function setToken($token) {
        $this->token = $token;
    }

    public function setMeta($meta) {
        $this->meta = $meta;
    }

//get()
    public function getToken() {
        return $this->token;
    }

    public function getMeta() {
        return $this->meta;
    }

    public function getRichiesta() {
        $TokenSoapval = new soapval('token', 'token', $this->token, false, false);
        $MetaSoapval = new soapval('meta_key', 'meta_key', $this->meta, false, false);
        $paramL = $TokenSoapval->serialize() . $MetaSoapval->serialize();
        return $paramL;
    }

}

?>
