<?php

/**
 *
 * Classe per collegamento StarService services
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaSelect
 * @author     Simone Franchi 
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    06.03.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

class itaStarServiceClient {

    private $namespace = "http://webservices.jprotocollo.jente.infor.arezzo.it/";
    private $namespacePrefix = "star";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $timeout = "";
    private $max_execution_time = 600;
    private $result;
    private $error;
    private $fault;

   
    function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    public function setNamespacePrefix($namespacePrefix) {
        $this->namespacePrefix = $namespacePrefix;
    }

    public function setWebservices_uri($webservices_uri) {
        $this->webservices_uri = $webservices_uri;
    }

    public function setWebservices_wsdl($webservices_wsdl) {
        $this->webservices_wsdl = $webservices_wsdl;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setMax_execution_time($max_execution_time) {
        $this->max_execution_time = $max_execution_time;
    }

    function getUsername() {
        return $this->username;
    }

    function getPassword() {
        return $this->password;
    }


    public function getResult() {
        return $this->result;
    }

    public function getError() {
        return $this->error;
    }

    public function getFault() {
        return $this->fault;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }

    private function ws_call($operationName, $param = '') {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
        $client->timeout = 500;
        $client->response_timeout = 500;
        $client->soap_defencoding = 'UTF-8';
        $client->debugLevel = 0;

        $result = $client->call($operationName, $param, array(
            "soapenv" => "http://schemas.xmlsoap.org/soap/envelope/",
            $this->namespacePrefix => $this->namespace
                )
        );
        
        //Out::msgInfo("request" , htmlspecialchars($client->response));
        
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

    public function ws_GetPraticheNuove() {
        $param = array(
            'user' => $this->getUsername(),
            'pwd' => $this->getPassword(),
        );

        return $this->ws_call('GetPraticheNuove', $param);
    }

    public function ws_GetPratica($param1) {
        $login = array(
            'user' => $this->getUsername(),
            'pwd' => $this->getPassword(),
        );

        $param = array_merge($login, $param1);

        //Out::msgInfo("Array di collegamento: ", print_r($param, true));

        return $this->ws_call('GetPratica', $param);
    }

    public function ws_SetStatoPratica($param1) {
        $login = array(
            'user' => $this->getUsername(),
            'pwd' => $this->getPassword(),
        );

        $param = array_merge($login, $param1);
        
        return $this->ws_call('SetStatoPratica', $param);
    }

    public function ws_GetComunicazioniNuove($param) {
        return $this->ws_call('GetComunicazioniNuove', $param);
    }

    public function ws_GetComunicazione($param) {
        return $this->ws_call('GetComunicazione', $param);
    }

    public function ws_SetStatoComunicazione($param) {
        return $this->ws_call('SetStatoComunicazione', $param);
    }

    public function ws_InvioComunicazione($param) {
        return $this->ws_call('InvioComunicazione', $param);
    }
    
}