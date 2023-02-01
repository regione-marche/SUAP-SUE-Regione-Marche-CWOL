<?php

/**
 *
 * Classe per collegamento ws halley E-Lios
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPELios
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2016 Italsoft srl
 * @license
 * @version    06.07.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

class itaELiosDizionarioClient {

    private $nameSpaces = array();
    private $namespace = "";
    private $webservices_uri = "";
    private $webservices_uriDizionario = "";
    private $webservices_wsdl = "";
    private $webservices_wsdlDizionario = "";
    private $username = "";
    private $password = "";
    private $CodiceAOO = "";
    private $CodiceDitta = "";
    private $timeout = 2400;
    private $SOAPHeader;
    private $result;
    private $error;
    private $fault;

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    public function getNameSpaces() {
        return $this->nameSpaces;
    }

    public function setNameSpaces($tipo = 'tem') {
        if ($tipo == 'tem') {
            $nameSpaces = array("tem" => "http://tempuri.org/");
        }
        $this->nameSpaces = $nameSpaces;
    }

    public function setWebservices_uri($webservices_uri) {
        $this->webservices_uri = $webservices_uri;
    }

    public function setWebservices_wsdl($webservices_wsdl) {
        $this->webservices_wsdl = $webservices_wsdl;
    }

    public function setWebservices_uriDizionario($webservices_uriDiz) {
        $this->webservices_uriDizionario = $webservices_uriDiz;
    }

    public function setWebservices_wsdlDizionario($webservices_wsdlDiz) {
        $this->webservices_wsdlDizionario = $webservices_wsdlDiz;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getCodiceAOO() {
        return $this->CodiceAOO;
    }

    public function setCodiceAOO($CodiceAOO) {
        $this->CodiceAOO = $CodiceAOO;
    }

    public function setCodiceDitta($CodiceDitta) {
        $this->CodiceDitta = $CodiceDitta;
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

    private function ws_call($operationName, $param, $ns = "tem:") {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uriDizionario, false);
//        $client = new nusoap_client($this->webservices_wsdl, true);
        $client->debugLevel = 0;
        $client->timeout = $this->timeout > 0 ? $this->timeout : 120;
        $client->response_timeout = $this->timeout;
        $client->soap_defencoding = 'UTF-8';
        $soapAction = $this->namespace . "/" . $operationName;
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';
        $result = $client->call($ns . $operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
        //$result = $client->call($operationName, $param, $nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
        file_put_contents("/users/tmp/param_$operationName.xml", $param);
        file_put_contents("/users/tmp/request_$operationName.xml", $client->request);
        file_put_contents("/users/tmp/response_$operationName.xml", $client->response);
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

    public function ws_Login($param) {
        $strCodEnteSoapval = new soapval('tem:strCodEnte', 'tem:strCodEnte', $param['strCodEnte'], false, false);
        $strUserNameSoapval = new soapval('tem:strUserName', 'tem:strUserName', $param['strUserName'], false, false);
        $strPasswordSoapval = new soapval('tem:strPassword', 'tem:strPassword', $param['strPassword'], false, false);
        $param = $strCodEnteSoapval->serialize("literal") . $strUserNameSoapval->serialize("literal") . $strPasswordSoapval->serialize("literal");
        return $this->ws_call('LoginRequest', $param);
    }

    public function ws_SearchFascicoli($param) {
        $strUserNameSoapval = new soapval('tem:strUserName', 'tem:strUserName', $param['strUserName'], false, false);
        $strTokenSoapval = new soapval('tem:strDST', 'tem:strDST', $param['strDST'], false, false);
        $strCodiceAOOSoapval = new soapval('tem:codiceAOO', 'tem:codiceAOO', $param['codiceAOO'], false, false);
        $strNumProtSoapval = new soapval('tem:numeroProtocollo', 'tem:numeroProtocollo', $param['numeroProtocollo'], false, false);
        $strAnnoProtSoapval = new soapval('tem:annoProtocollo', 'tem:annoProtocollo', $param['annoProtocollo'], false, false);
        $param = $strUserNameSoapval->serialize("literal") . $strTokenSoapval->serialize("literal") . $strCodiceAOOSoapval->serialize("literal") . $strNumProtSoapval->serialize("literal") . $strAnnoProtSoapval->serialize("literal");
        return $this->ws_call('SearchFascicoliRequest', $param);
    }

}

?>
