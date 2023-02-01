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

class itaTaxonomy {

    private $token;
    private $taxonomy;

//set()    
    public function setToken($token) {
        $this->token = $token;
    }

    public function setTaxonomy($taxonomy) {
        $this->taxonomy = $taxonomy;
    }

//get()
    public function getToken() {
        return $this->token;
    }

    public function getTaxonomy() {
        return $this->taxonomy;
    }

    public function getRichiesta() {
        $TokenSoapval = new soapval('token', 'token', $this->token, false, false);
        $TaxonomySoapval = new soapval('taxonomy', 'taxonomy', $this->taxonomy, false, false);
        $paramL = $TokenSoapval->serialize() . $TaxonomySoapval->serialize();
        return $paramL;
    }

}

?>
