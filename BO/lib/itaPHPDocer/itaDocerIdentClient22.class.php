<?php

/**
 *
 * Classe per collegamento ws DOCER 22 - Servizio di Identificazione
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPDocer
 * @author     
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    19.10.2017
 * @link
 * @see
 * @since
 * */
require_once(ITA_LIB_PATH . '/itaPHPDocer/itaDocerClientSuper.class.php');
require_once(ITA_LIB_PATH . '/itaPHPDocer/itaDocerIdentClientInterface.php');

class itaDocerIdentClient22 extends itaDocerClientSuper implements itaDocerIdentClientInterface {
        
    protected function init() {
        $this->namespace = 'http://authentication.core.docer.kdm.it';
        $this->namespaces = array('aut' => 'http://authentication.core.docer.kdm.it');
    }
    
    public function ws_login($param) {
        $userIDSoapval = new soapval('aut:username', 'aut:username', $param['username'], false, false);
        $passwordSoapval = new soapval('aut:password', 'aut:password', $param['password'], false, false);
        $codiceEnteSoapval = new soapval('aut:codiceEnte', 'aut:codiceEnte', $param['codiceEnte'], false, false);
        $applicationSoapval = new soapval('aut:application', 'aut:application', $param['application'], false, false);
        $param = $userIDSoapval->serialize("literal") . $passwordSoapval->serialize("literal") . $codiceEnteSoapval->serialize("literal") . $applicationSoapval->serialize("literal");
        return $this->ws_call('login', $param, 'aut:');        
    }

    public function ws_logout($param) {
        $tokenSoapval = new soapval('aut:token', 'aut:token', $param['token'], false, false);
        $param = $tokenSoapval->serialize("literal");
        return $this->ws_call('logout', $param, 'aut:');        
    }

}
