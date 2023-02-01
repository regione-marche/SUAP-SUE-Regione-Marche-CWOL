<?php

/**
 *
 * Classe per collegamento ws IRIDE
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPIride
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2018 Italsoft srl
 * @license
 * @version    04.05.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

class itaAdrierClient {

    private $nameSpaces = array();
    private $namespace = "";
    private $namespacePrefix = "ent";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
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

    public function setNameSpaces($nameSpaces) {
//            $nameSpaces = array("adr" => "http://adrigate.lepida.it");
        $this->nameSpaces = $nameSpaces;
    }

    public function setWebservices_uri($webservices_uri) {
        $this->webservices_uri = $webservices_uri;
    }

    public function setWebservices_wsdl($webservices_wsdl) {
        $this->webservices_wsdl = $webservices_wsdl;
    }

    public function setUsername($username) {
        $this->username = trim($username);
    }

    public function setPassword($password) {
        $this->password = trim($password);
    }

    public function getNamespace() {
        return $this->namespace;
    }

    public function getWebservices_uri() {
        return $this->webservices_uri;
    }

    public function getWebservices_wsdl() {
        return $this->webservices_wsdl;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getTimeout() {
        return $this->timeout;
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

    public function getNamespacePrefix() {
        return $this->namespacePrefix;
    }

    public function setNamespacePrefix($namespacePrefix) {
        $this->namespacePrefix = $namespacePrefix;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }

    private function getHeaders() {
        $header = "";
        return $header;
    }

    private function ws_call($operationName, $param, $soapAction) {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
        $client->debugLevel = 0;
        $client->timeout = $this->timeout > 0 ? $this->timeout : 120;
        $client->response_timeout = $this->timeout;
//        $client->setHeaders($this->getHeaders());
        $client->soap_defencoding = 'UTF-8';
//        $soapAction = $this->namespace . "/" . $operationName;
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';
        $result = $client->call($operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
        //$result = $client->call($operationName, $param, $nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
//        file_put_contents('/users/tmp/LNparam_' . $soapAction . '.xml', $param);
//        file_put_contents('/users/tmp/LNrequest_' . $soapAction . '.xml', $client->request);
//        file_put_contents('/users/tmp/LNresponse_' . $soapAction . '.xml', $client->response);
//        file_put_contents('C:/tmp/Adrier_param_' . $soapAction . date('YmdHis') . '.xml', $param);
//        file_put_contents('C:/tmp/Adrier_request_' . $soapAction . date('YmdHis') . '.xml', $client->request);
//        file_put_contents('C:/tmp/Adrier_response_' . $soapAction . date('YmdHis') . '.xml', $client->response);
        $time = time();
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

    public function ws_RicercaImpresePerCodiceFiscale($param) {
        $RequestString = "";
        $codice_fiscaleSoapVal = new soapval('codice_fiscale', 'codice_fiscale', $param['codice_fiscale'], false, false);
        $RequestString .= $codice_fiscaleSoapVal->serialize('literal');
        $userSoapVal = new soapval('user', 'user', $this->username, false, false);
        $RequestString .= $userSoapVal->serialize('literal');
        $passwordSoapVal = new soapval('password', 'password', $this->password, false, false);
        $RequestString .= $passwordSoapVal->serialize('literal');

        return $this->ws_call("adr:RicercaImpresePerCodiceFiscale", $RequestString, "");
    }

    public function ws_RicercaImpreseNonCessatePerCodiceFiscale($param) {
        $RequestString = "";
        $codice_fiscaleSoapVal = new soapval('codice_fiscale', 'codice_fiscale', $param['codice_fiscale'], false, false);
        $RequestString .= $codice_fiscaleSoapVal->serialize('literal');
        $userSoapVal = new soapval('user', 'user', $this->username, false, false);
        $RequestString .= $userSoapVal->serialize('literal');
        $passwordSoapVal = new soapval('password', 'password', $this->password, false, false);
        $RequestString .= $passwordSoapVal->serialize('literal');

        return $this->ws_call("adr:RicercaImpreseNonCessatePerCodiceFiscale", $RequestString, "");
    }

    public function ws_RicercaImpreseNRea($param) {
        $RequestString = "";
        $sgl_prv_sedeSoapVal = new soapval('sgl_prv_sede', 'sgl_prv_sede', $param['sgl_prv_sede'], false, false);
        $RequestString .= $sgl_prv_sedeSoapVal->serialize('literal');
        $n_rea_sedeSoapVal = new soapval('n_rea_sede', 'n_rea_sede', $param['n_rea_sede'], false, false);
        $RequestString .= $n_rea_sedeSoapVal->serialize('literal');
        $userSoapVal = new soapval('user', 'user', $this->username, false, false);
        $RequestString .= $userSoapVal->serialize('literal');
        $passwordSoapVal = new soapval('password', 'password', $this->password, false, false);
        $RequestString .= $passwordSoapVal->serialize('literal');

        return $this->ws_call("adr:RicercaImpreseNRea", $RequestString, "");
    }

    public function ws_RicercaImpresePerDenominazione($param) {
        $RequestString = "";
        $denominazione_sedeSoapVal = new soapval('denominazione_sede', 'denominazione_sede', $param['denominazione_sede'], false, false);
        $RequestString .= $denominazione_sedeSoapVal->serialize('literal');
        $userSoapVal = new soapval('user', 'user', $this->username, false, false);
        $RequestString .= $userSoapVal->serialize('literal');
        $passwordSoapVal = new soapval('password', 'password', $this->password, false, false);
        $RequestString .= $passwordSoapVal->serialize('literal');

        return $this->ws_call("adr:RicercaImpresePerDenominazione", $RequestString, "");
    }

    public function ws_RicercaImpreseNonCessatePerDenominazione($param) {
        $RequestString = "";
        $denominazione_sedeSoapVal = new soapval('denominazione_sede', 'denominazione_sede', $param['denominazione_sede'], false, false);
        $RequestString .= $denominazione_sedeSoapVal->serialize('literal');
        $userSoapVal = new soapval('user', 'user', $this->username, false, false);
        $RequestString .= $userSoapVal->serialize('literal');
        $passwordSoapVal = new soapval('password', 'password', $this->password, false, false);
        $RequestString .= $passwordSoapVal->serialize('literal');

        return $this->ws_call("adr:RicercaImpreseNonCessatePerDenominazione", $RequestString, "");
    }

    public function ws_DettaglioCompletoImpresa($param) {
        $RequestString = "";
        $sgl_prv_sedeSoapVal = new soapval('sgl_prv_sede', 'sgl_prv_sede', $param['sgl_prv_sede'], false, false);
        $RequestString .= $sgl_prv_sedeSoapVal->serialize('literal');
        $n_rea_sedeSoapVal = new soapval('n_rea_sede', 'n_rea_sede', $param['n_rea_sede'], false, false);
        $RequestString .= $n_rea_sedeSoapVal->serialize('literal');
        $userSoapVal = new soapval('user', 'user', $this->username, false, false);
        $RequestString .= $userSoapVal->serialize('literal');
        $passwordSoapVal = new soapval('password', 'password', $this->password, false, false);
        $RequestString .= $passwordSoapVal->serialize('literal');

        return $this->ws_call("adr:DettaglioCompletoImpresa", $RequestString, "");
    }
    
    public function ws_DettaglioImpresaNRea($param) {
        $RequestString = "";
        $sgl_prv_sedeSoapVal = new soapval('sgl_prv_sede', 'sgl_prv_sede', $param['sgl_prv_sede'], false, false);
        $RequestString .= $sgl_prv_sedeSoapVal->serialize('literal');
        $n_rea_sedeSoapVal = new soapval('n_rea_sede', 'n_rea_sede', $param['n_rea_sede'], false, false);
        $RequestString .= $n_rea_sedeSoapVal->serialize('literal');
        $userSoapVal = new soapval('user', 'user', $this->username, false, false);
        $RequestString .= $userSoapVal->serialize('literal');
        $passwordSoapVal = new soapval('password', 'password', $this->password, false, false);
        $RequestString .= $passwordSoapVal->serialize('literal');

        return $this->ws_call("adr:DettaglioImpresaNRea", $RequestString, "");
    }
    
    public function ws_DettaglioRidottoImpresa($param) {
        $RequestString = "";
        $sgl_prv_sedeSoapVal = new soapval('sgl_prv_sede', 'sgl_prv_sede', $param['sgl_prv_sede'], false, false);
        $RequestString .= $sgl_prv_sedeSoapVal->serialize('literal');
        $n_rea_sedeSoapVal = new soapval('n_rea_sede', 'n_rea_sede', $param['n_rea_sede'], false, false);
        $RequestString .= $n_rea_sedeSoapVal->serialize('literal');
        $userSoapVal = new soapval('user', 'user', $this->username, false, false);
        $RequestString .= $userSoapVal->serialize('literal');
        $passwordSoapVal = new soapval('password', 'password', $this->password, false, false);
        $RequestString .= $passwordSoapVal->serialize('literal');

        return $this->ws_call("adr:DettaglioRidottoImpresa", $RequestString, "");
    }
    
    public function ws_DettaglioPersoneConCarica($param) {
        $RequestString = "";
        $fk_pri_cciaa_regzSoapVal = new soapval('fk_pri_cciaa_regz', 'fk_pri_cciaa_regz', $param['fk_pri_cciaa_regz'], false, false);
        $RequestString .= $fk_pri_cciaa_regzSoapVal->serialize('literal');
        $fk_pri_n_reaSoapVal = new soapval('fk_pri_n_rea', 'fk_pri_n_rea', $param['fk_pri_n_rea'], false, false);
        $RequestString .= $fk_pri_n_reaSoapVal->serialize('literal');
        $userSoapVal = new soapval('user', 'user', $this->username, false, false);
        $RequestString .= $userSoapVal->serialize('literal');
        $passwordSoapVal = new soapval('password', 'password', $this->password, false, false);
        $RequestString .= $passwordSoapVal->serialize('literal');

        return $this->ws_call("adr:DettaglioPersoneConCarica", $RequestString, "");
    }

}
