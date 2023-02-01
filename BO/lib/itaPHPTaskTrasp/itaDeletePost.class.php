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

class itaDeletePost {

    private $token;
    private $id;

//set()    
    public function setToken($token) {
        $this->token = $token;
    }

    public function setPostId($postId) {
        $this->postId = $postId;
    }

//get()
    public function getToken() {
        return $this->token;
    }

    public function getPostId() {
        return $this->postId;
    }

    public function getRichiesta() {
        $TokenSoapval = new soapval('token', 'token', $this->token, false, false);
        $IdSoapval = new soapval('postid', 'postid', $this->postId, false, false);
        $paramL = $TokenSoapval->serialize() . $IdSoapval->serialize();
        return $paramL;
    }

}

?>
