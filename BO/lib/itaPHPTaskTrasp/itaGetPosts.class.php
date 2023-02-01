<?php

/**
 *
 * Classe per collegamento Task services
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaTask
 * @author     Antimo Panetta <andimo.panetta@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    22.05.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

class itaGetPosts {

    private $token;
    private $postMeta;

//set()    
    public function setToken($token) {
        $this->token = $token;
    }

    public function setPostMeta($postMeta) {
        $this->postMeta = $postMeta;
    }

//get()
    public function getToken() {
        return $this->token;
    }

    public function getPostMeta() {
        return $this->postMeta;
    }

    public function getRichiesta() {
        $TokenSoapval = new soapval('token', 'token', $this->token, false, false);
        $PostIdSoapval = new soapval('lista_valori', 'lista_valori', $this->postMeta, false, false);
        $paramL = $TokenSoapval->serialize() . $PostIdSoapval->serialize();
        return $paramL;
    }

}

?>
