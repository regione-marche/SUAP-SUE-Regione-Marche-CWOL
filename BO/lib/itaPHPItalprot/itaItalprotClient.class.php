<?php

/**
 *
 * Classe per collegamento jProtocollo services
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaInforWS
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    20.01.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

//require_once(ITA_LIB_PATH . '/nusoap/nusoap1.2.php');

class itaItalprotClient {

    private $namespace = "";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $domain = "";
    private $timeout = 3600;
    private $response_timeout = 3600;
    private $max_execution_time = 3600;
    private $result;
    private $error;
    private $fault;

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
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

    private function ws_call($operationName, $param) {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_wsdl, true);
        $client->debugLevel = 0;
        //setting timeout
        $client->timeout = $this->timeout;
        $client->response_timeout = $this->response_timeout;
        $client->soap_defencoding = 'UTF-8';
        $result = $client->call($operationName, $param);
        //file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/param_$operationName.xml", $param);
        //file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/request_$operationName.xml", $client->request);
        //file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/response_$operationName.xml", $client->response);

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

    /**
     * 
     * @param type $param = array(
     * 'userName',
     * 'userPassword',
     * 'domainCode'
     * )
     * @return type
     */
    public function ws_GetItaEngineContextToken($param) {
        return $this->ws_call('GetItaEngineContextToken', $param);
    }

    /**
     * 
     * @param type $param = array(
     * 'token',
     * 'domainCode'
     * )
     * @return type
     */
    public function ws_DestroyItaEngineContextToken($param) {
        return $this->ws_call('DestroyItaEngineContextToken', $param);
    }

    /**
     * 
     * @param type $param array(
     * 'token',
     * 'anno',
     * 'numero',
     * 'tipo',
     * 'segnatura'
     * )
     * @return type
     */
    public function ws_getProtocollo($param) {
        return $this->ws_call('GetProtocollo', $param);
    }

    /**
     * 
     * @param type $param array(
     * 'token',
     * 'id',
     * 'blockSize',
     * 'part'
     * )
     * @return type
     */
    public function ws_getAllegato($param) {
        return $this->ws_call('GetAllegato', $param);
    }

    /**
     * 
     * @param type $param array(
     * 'token',
     * 'datiProtocollo'
     * )
     * @return type
     */
    public function ws_putProtocollo($param) {
        return $this->ws_call('PutProtocollo', $param);
    }

    public function ws_putAllegato($param) {
        return $this->ws_call('PutAllegato', $param);
    }

    public function ws_insertDocumento($param) {
        return $this->ws_call('InsertDocumento', $param);
    }

    /**
     * 
     * @param type $param
        $param['token'] = token;
        $param['anno'] = anno protocollo;
        $param['numero'] = numero protocollo;
        $param['tipo'] = tipo protocollo;
     * @return type
     */
    public function ws_NotificaMailProtocollo($param) {
        return $this->ws_call('NotificaMailProtocollo', $param);
    }

    /**
     * 
     * @param type $param
        $param['token'] = token;
        $param['anno'] = anno protocollo;
        $param['numero'] = numero protocollo;
        $param['tipo'] = tipo protocollo;
     * @return type
     */
    public function ws_GetNotificaMailProtocollo($param) {
        return $this->ws_call('GetNotificaMailProtocollo', $param);
    }

    
}

?>
