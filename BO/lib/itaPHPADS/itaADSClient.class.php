<?php

/**
 *
 * Classe per collegamento jProtocollo services
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaInforWS
 * @author     Antimo Panetta <andimo.panetta@italsoft.eu>
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    20.02.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

class itaADSClient {

    private $nameSpaces = array("scu" => "http://scuolaMaterna.tr4gregr.finmatica.it");
    private $namespace = "";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $domain = "";
    private $timeout = 120;
    private $response_timeout = 300;
    private $max_execution_time = 120;
    private $result;
    private $error;
    private $fault;

    public function setNamespace($nameSpaces) {
        $this->namespace = $nameSpaces;
    }

    public function setWebservices_uri($webservices_uri) {
        $this->webservices_uri = $webservices_uri;
    }

    public function setWebservices_wsdl($webservices_wsdl) {
        $this->webservices_wsdl = $webservices_wsdl;
    }

    public function setDomain($domain) {
        $this->domain = $domain;
    }

    public function getDomain() {
        return $this->domain;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setpassword($password) {
        $this->password = $password;
    }

    public function getpassword() {
        return $this->password;
    }

    public function setMax_execution_time($max_execution_time) {
        $this->max_execution_time = $max_execution_time;
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

    private function ws_call($operationName, $param, $ns = "scu:") {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
        $client->debugLevel = 0;
        $client->timeout = $this->timeout;
        $client->soap_defencoding = 'UTF-8';
        //$soapAction = 'http://gw.comune.pesaro.pu.it:8989/axis/services/IscrizioneMaterna' . "/" . $operationName;
        $soapAction = '';
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';
        $result = $client->call($ns . $operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
        
        file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/param_$operationName.txt", print_r($param,true));
        file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/request_$operationName.xml", $client->request);
        file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/response_$operationName.xml", $client->response);

        if ($client->fault) {
            $this->fault = $client->faultstring;
            throw new Exception("Request Fault:" . $this->fault);
            return false;
        } else {
            $err = $client->getError();
            if ($err) {
                $this->error = $err;
                throw new Exception("Client SOAP Error: " . $err);
                return false;
            }
        }
        $this->result = $result;
        return true;
    }

    public function ws_sendIscrizione($fileXML) {
        
//        Out::msgInfo('wsdl', $this->webservices_wsdl);
//        Out::msgInfo('url', $this->webservices_uri);
//        Out::msgInfo('time', $this->max_execution_time);
//        Out::msgInfo('ns', $this->namespace);
        
        $param = '<sInput xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">' . $fileXML .'</sInput>';     
        return $this->ws_call('process', $param);
    }

}

?>

