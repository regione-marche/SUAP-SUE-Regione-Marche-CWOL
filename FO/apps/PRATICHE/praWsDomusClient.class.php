<?php

/**
 *
 * Classe per collegamento ws IRIDE
 *
 * PHP Version 5
 *
 * @category    
 * @package    
 * @author     Carlo Iesari <carlo.iesari@italsoft.eu>
 * @copyright  1987-2019 Italsoft srl
 * @license
 * @version    27.02.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once ITA_LIB_PATH . '/nusoap/nusoap.php';

class praWsDomusClient {

    private $nameSpaces = array();
    private $namespace = "";
    private $actionURI = "";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $timeout = 2400;
    private $result;
    private $error;
    private $fault;
    private $request;
    private $response;

    public function getNameSpaces() {
        return $this->nameSpaces;
    }

    public function setNameSpaces($nameSpaces) {
        $this->nameSpaces = $nameSpaces;
    }

    public function getActionURI() {
        return $this->actionURI;
    }

    public function setActionURI($actionURI) {
        $this->actionURI = $actionURI;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    public function setWebservices_uri($webservices_uri) {
        $this->webservices_uri = $webservices_uri;
    }

    public function setWebservices_wsdl($webservices_wsdl) {
        $this->webservices_wsdl = $webservices_wsdl;
    }

    public function setMax_execution_time($max_execution_time) {
        $this->max_execution_time = $max_execution_time;
    }

    public function setResponse_timeout($response_timeout) {
        $this->response_timeout = $response_timeout;
    }

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    public function setDebugLevel($debugLevel) {
        $this->debugLevel = $debugLevel;
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

    private function ws_call($methodName, $params) {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
        $client->debugLevel = $this->debugLevel;
        $client->timeout = $this->timeout > 0 ? $this->timeout : 120;
        $client->response_timeout = $this->timeout;
        $client->soap_defencoding = 'UTF-8';

        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';

        $result = $client->call("dom:" . $methodName, $params, $this->nameSpaces, $this->actionURI . '/' . $methodName, $headers, $rpcParams, $style, $use);

        $this->request = $client->request;
        $this->response = $client->response;

        file_put_contents("/tmp/request$methodName.log", print_r($this->request, true));
        file_put_contents("/tmp/response$methodName.log", print_r($this->response, true));
        
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

    public function ws_GetPratica($param) {
        $RequestString = "";
        $PasswordSoapval = new soapval('dom1:Password', 'dom1:Password', $param['Password'], false, false);
        $RequestString .= $PasswordSoapval->serialize('literal');
        $UtenteSoapval = new soapval('dom1:UserID', 'dom1:UserID', $param['UserID'], false, false);
        $RequestString .= $UtenteSoapval->serialize('literal');
        $NumeroPraticaSoapval = new soapval("dom1:NumeroPratica", "dom1:NumeroPratica", $param['NumeroPratica'], false, false);
        $RequestString .= $NumeroPraticaSoapval->serialize('literal');

        $RequestString = "<dom:request>" . $RequestString . "</dom:request>";
        return $this->ws_call("GetPratica", $RequestString);
    }

    public function ws_GetDocumentiFascicolo($param) {
        $RequestString = "";
        $PasswordSoapval = new soapval('dom1:Password', 'dom1:Password', $param['Password'], false, false);
        $RequestString .= $PasswordSoapval->serialize('literal');
        $UtenteSoapval = new soapval('dom1:UserID', 'dom1:UserID', $param['UserID'], false, false);
        $RequestString .= $UtenteSoapval->serialize('literal');
        $NumeroRichiestaSoapval = new soapval("dom1:NumeroRichiesta", "dom1:NumeroRichiesta", $param['NumeroRichiesta'], false, false);
        $RequestString .= $NumeroRichiestaSoapval->serialize('literal');

        $RequestString = "<dom:request>" . $RequestString . "</dom:request>";
        return $this->ws_call("GetDocumentiFascicolo", $RequestString);
    }

    public function ws_GetDocumentiProtocollo($param) {
        $RequestString = "";
        $PasswordSoapval = new soapval('dom1:Password', 'dom1:Password', $param['Password'], false, false);
        $RequestString .= $PasswordSoapval->serialize('literal');
        $UtenteSoapval = new soapval('dom1:UserID', 'dom1:UserID', $param['UserID'], false, false);
        $RequestString .= $UtenteSoapval->serialize('literal');
        $DocNumberSoapval = new soapval("dom1:DocNumber", "dom1:DocNumber", $param['DocNumber'], false, false);
        $RequestString .= $DocNumberSoapval->serialize('literal');
        $IstatComuneSoapval = new soapval("dom1:IstatComune", "dom1:IstatComune", $param['IstatComune'], false, false);
        $RequestString .= $IstatComuneSoapval->serialize('literal');

        $RequestString = "<dom:request>" . $RequestString . "</dom:request>";
        return $this->ws_call("GetDocumentiProtocollo", $RequestString);
    }

}
