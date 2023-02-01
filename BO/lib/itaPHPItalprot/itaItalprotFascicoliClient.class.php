<?php

/**
 *
 * Classe per collegamento itaItalprotFascicoliClient services
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaInforWS
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    04.01.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

//require_once(ITA_LIB_PATH . '/nusoap/nusoap1.2.php');

class itaItalprotFascicoliClient {

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
        //$client->response_timeout = $this->timeout;
        $client->soap_defencoding = 'UTF-8';
        $result = $client->call($operationName, $param);
        file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/param_$operationName.xml", $param);
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
     * 'datiFascicolo'
     *      array(
     *  'ufficioOperatore' 
     *  'titolario'
     *  'descrizione' 
     *  'natura' (0=Digitale 1=Cartaceo 2=Ibrido)
     *  'responsabile'
     *  'ufficioResponsabile'
     *  'codiceSerie'
     *  'progressivoSerie'
     *      )
     * )
     * @return type
     */
    public function ws_CreaFascicolo($param) {
        return $this->ws_call('CreaFascicolo', $param);
    }

    /**
     * 
     * @param type $param array(
     * 'token',
     * 'annoProtocollo',
     * 'numeroProtocollo',
     * 'tipoProtocollo',
     * 'codiceFascicolo',
     * 'codiceSottoFascicolo'
     * )
     * @return type
     */
    public function ws_FascicolaProtocollo($param) {
        return $this->ws_call('FascicolaProtocollo', $param);
    }

    /**
     * 
     * @param type $param array(
     * 'token',
     * 'annoProtocollo',
     * 'numeroProtocollo',
     * 'tipoProtocollo'
     * )
     * @return type
     */
    public function ws_GetFascicoliProtocollo($param) {
        return $this->ws_call('GetFascicoliProtocollo', $param);
    }

    /**
     * 
     * @param type $param array(
     * 'token',
     * 'chiave',
     * 'valore',
     * )
     * @return type
     */
    public function ws_GetElencoFascicoli($param) {
        return $this->ws_call('GetElencoFascicoli', $param);
    }

}