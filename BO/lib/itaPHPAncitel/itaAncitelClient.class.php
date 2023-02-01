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
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    18.04.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

class itaAncitelClient {

    private $nameSpaces = array();
    private $namespace = "";
    private $namespacePrefix = "vtt";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $canale = "ITALSOFT";
    private $utente = "";
    private $timeout = 2400;
    private $SOAPHeader;
    private $result;
    private $error;
    private $fault;
    private $request;
    private $response;

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
//        if ($tipo == 'vtt') {
//            $nameSpaces = array("vtt" => "http://aci.ancitel.it/ws/visure/schema/beans/vtt");
//        }
        $this->nameSpaces = $nameSpaces;
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

    public function getRequest() {
        return $this->request;
    }

    public function getResponse() {
        return $this->response;
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

    private function ws_call($operationName, $param) {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
//        $client = new nusoap_client($this->webservices_wsdl, true); //nmon ho una uri per questo wsdl! ( o è la stessa)
        $client->debugLevel = 0;
        $client->timeout = $this->timeout > 0 ? $this->timeout : 120;
        $client->response_timeout = $this->timeout;
        $client->soap_defencoding = 'UTF-8';
//        $soapAction = $this->namespace . "/" . $operationName;
        $soapAction = "";
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';
//        $result = $client->call("vtt:".$operationName . "-request", $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
        $result = $client->call($operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
        //$result = $client->call($operationName, $param, $nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
//        file_put_contents('/users/tmp/AncitelParam_' . $soapAction . '.xml', $param);
//        file_put_contents('/users/tmp/AncitelRequest_' . $soapAction . '.xml', $client->request);
//        file_put_contents('/users/tmp/AncitelResponse_' . $soapAction . '.xml', $client->response);
        file_put_contents('C:/tmp/AncitelParam_' . $soapAction . date('YmdHis') . '.xml', $param);
        file_put_contents('C:/tmp/AncitelRequest_' . $soapAction . date('YmdHis') . '.xml', $client->request);
        file_put_contents('C:/tmp/AncitelResponse_' . $soapAction . date('YmdHis') . '.xml', $client->response);
        $this->request = $client->request;
        $this->response = $client->response;

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

    public function ws_VisuraTargaTelaio($param) {
        $RequestString = "";

        $usernameSoapVal = new soapval('username', 'username', $this->username, false, false);
        $RequestString .= $usernameSoapVal->serialize('literal');
        $passwordSoapVal = new soapval('password', 'password', $this->password, false, false);
        $RequestString .= $passwordSoapVal->serialize('literal');

        $RequestDatiRichiesteString = "";
        foreach ($param['DatiRichieste'] as $k => $DatiRichiesta) {
            $RequestDatiRichiesteString .= "<DatiRichiesta>";
            $DataRichiestaSoapVal = new soapval('DataRichiesta', 'DataRichiesta', $DatiRichiesta['DataRichiesta'], false, false);
            $RequestDatiRichiesteString .= $DataRichiestaSoapVal->serialize('literal');
            $TipoRichiestaSoapVal = new soapval('TipoRichiesta', 'TipoRichiesta', $DatiRichiesta['TipoRichiesta'], false, false);
            $RequestDatiRichiesteString .= $TipoRichiestaSoapVal->serialize('literal');
            if ($DatiRichiesta['SerieTarga']) {
                $SerieTargaSoapVal = new soapval('SerieTarga', 'SerieTarga', $DatiRichiesta['SerieTarga'], false, false);
                $RequestDatiRichiesteString .= $SerieTargaSoapVal->serialize('literal');
            }
            if ($DatiRichiesta['Targa']) {
                $TargaSoapVal = new soapval('Targa', 'Targa', $DatiRichiesta['Targa'], false, false);
                $RequestDatiRichiesteString .= $TargaSoapVal->serialize('literal');
            }
            if ($DatiRichiesta['Telaio']) {
                $TelaioSoapVal = new soapval('Telaio', 'Telaio', $DatiRichiesta['Telaio'], false, false);
                $RequestDatiRichiesteString .= $TelaioSoapVal->serialize('literal');
            }
            if ($DatiRichiesta['AltriDati']) {
                $AltriDatiSoapVal = new soapval('AltriDati', 'AltriDati', $DatiRichiesta['AltriDati'], false, false);
                $RequestDatiRichiesteString .= $AltriDatiSoapVal->serialize();
            }
            $RequestDatiRichiesteString .= "</DatiRichiesta>";
        }
        $RequestString .= $RequestDatiRichiesteString;

        if (!isset($this->canale) || $this->canale == '') {
            $this->canale = 'ITALSOFT';
        }
        $canaleSoapVal = new soapval('canale', 'canale', $this->canale, false, false);
        $RequestString .= $canaleSoapVal->serialize('literal');
//        $RequestString = "<vtt:visura-targa-telaio-request>". $RequestString . "</vtt:visura-targa-telaio-request>";
        //per evitare di fare 300 visure torno un array già precompilato



        return $this->ws_call("vtt:visura-targa-telaio-request", $RequestString);
    }

}

?>
