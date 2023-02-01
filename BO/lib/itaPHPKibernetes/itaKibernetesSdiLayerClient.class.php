<?php

/**
 *
 * Classe per collegamento ws Kibernetes
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaKibernetes
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    01.03.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
//require_once(dirname(__FILE__) . "/lib/nusoap/nusoap.php");
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

class itaKibernetesSdiLayerClient {

    private $params;
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $result;
    private $error;
    private $fault;
    private $xmlResponso;

    function __construct($params) {
        $this->params = $params;
    }

    public function getWebservices_wsdl() {
        return $this->webservices_wsdl;
    }

    public function setWebservices_wsdl($webservices_wsdl) {
        $this->webservices_wsdl = $webservices_wsdl;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getResult() {
        return $this->result;
    }

    public function setResult($result) {
        $this->result = $result;
    }

    public function getError() {
        return $this->error;
    }

    public function setError($error) {
        $this->error = $error;
    }

    public function getFault() {
        return $this->fault;
    }

    public function setFault($fault) {
        $this->fault = $fault;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }

    public function getXmlResponso() {
        return $this->xmlResponso;
    }

    public function setXmlResponso($xmlResponso) {
        $this->xmlResponso = $xmlResponso;
    }

//    private function ws_call($operationName, $soapAction, $param) {
    private function ws_call($operationName, $param) {
        $this->clearResult();

        if (!$this->params->getUri() || !$this->params->getNameSpace()) {
            $this->error = "Servizio non configurato contattare l'assistenza per la sua attivazione.";
            return false;
        }
        /*
         * Con wsdl
         */
        $client = new nusoap_client($this->params->getWsdlUri(), true);
        /*
         * Senza Wsdl
         */
        //$client = new nusoap_client($this->params->getUri(), false);

        $client->debugLevel = $this->params->getDebugLevel();
        $client->timeout = $this->params->getTimeout();
        $client->response_timeout = $this->params->getTimeout();
        $client->soap_defencoding = 'UTF-8';

        /*
         * Chiama complessa
         */
//        $headers = false;
//        $rpcParams = null;
//        $style = 'rpc';
//        $use = 'literal';
//        $result = $client->call($operationName, $param, $this->params->getNameSpace(), $soapAction, $headers, $rpcParams, $style, $use);

        /*
         * Chiamata semplice
         */
        $result = $client->call($operationName, $param);

        if ($client->fault) {
            $this->fault = $client->faultstring;
            return false;
        } else {
            $err = $client->getError();
            if ($err) {
                $this->error = $err;
                return false;
            }
        }
        $this->result = $result;
        return true;
    }

    public function ws_caricaFatturaWithArgs($paramsFattura) {
        return $this->ws_call('CaricaFatturaWithArgs', $paramsFattura);
    }

}

?>
