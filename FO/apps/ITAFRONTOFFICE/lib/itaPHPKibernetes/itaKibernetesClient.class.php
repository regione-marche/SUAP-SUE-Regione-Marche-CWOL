<?php

/**
 *
 * Classe per collegamento ws Kibernetes
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPKibernetes
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2018 Italsoft srl
 * @license
 * @version    19.07.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

class itaKibernetesClient {

    private $nameSpaces = array();
    private $namespace = "";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $CodiceUOPar = "";
    private $funzionarioPar = "";
    private $CodiceUOArr = "";
    private $funzionarioArr = "";
    private $istatAmministrazione = "";
    private $timeout = 2400;
    private $result;
    private $error;
    private $fault;
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

    public function setNameSpaces($tipo = 'urn') {
        if ($tipo == 'urn') {
            $nameSpaces = array("urn" => "urn:DefaultNamespace");
        }
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

    public function setCodiceUOPar($uoPar) {
        $this->CodiceUOPar = $uoPar;
    }

    public function setFunzionarioPar($funzPar) {
        $this->funzionarioPar = $funzPar;
    }

    function getCodiceUOArr() {
        return $this->CodiceUOArr;
    }

    function getFunzionarioArr() {
        return $this->funzionarioArr;
    }

    function setCodiceUOArr($CodiceUOArr) {
        $this->CodiceUOArr = $CodiceUOArr;
    }

    function setFunzionarioArr($funzionarioArr) {
        $this->funzionarioArr = $funzionarioArr;
    }

    function getIstatAmministrazione() {
        return $this->istatAmministrazione;
    }

    function setIstatAmministrazione($istatAmministrazione) {
        $this->istatAmministrazione = $istatAmministrazione;
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

    public function getResponse() {
        return $this->response;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }

    private function ws_call($operationName, $param, $ns = "urn:") {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
        $client->debugLevel = 0;
        $client->timeout = $this->timeout > 0 ? $this->timeout : 120;
        $client->response_timeout = $this->timeout;
        $client->setCredentials($this->username, $this->password, 'basic');
        $client->soap_defencoding = 'UTF-8';
        $soapAction = $operationName;
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';

        $result = $client->call($ns . $operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);

        $this->response = $client->response;
        
//        file_put_contents("F:/works/tmp/param_kibernetes_$operationName.xml", $param);
//        file_put_contents("F:/works/tmp/request_kibernetes_$operationName.xml", $client->request);
//        file_put_contents("F:/works/tmp/response_kibernetes_$operationName.xml", $client->response);

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

    public function ws_Set4ProtocolloEntrata($dati) {
        $IstatSoapval = new soapval('istatEnte', 'istatEnte', $this->istatAmministrazione, false, false);
        $MittenteSoapval = new soapval('mittente', 'mittente', $dati['Mittente'], false, false);
        $IndirizzoSoapval = new soapval('indirizzo', 'indirizzo', $dati['Indirizzo'], false, false);
        $OggettoSoapval = new soapval('oggetto', 'oggetto', $dati['Oggetto'], false, false);
        $UOSoapval = new soapval('UO', 'UO', $this->CodiceUOArr, false, false);
        $DestinatarioUOSoapval = new soapval('destinatarioUO', 'destinatarioUO', $this->CodiceUOArr, false, false);
        $DestinatarioFunzSoapval = new soapval('destinatarioFunz', 'destinatarioFunz', $dati['FunzionarioDest'], false, false);

        $DestinatarioUOSecSoapval = new soapval('destinatarioSecUO', 'destinatarioSecUO', $dati['UfficioSec'], false, false);
        $DestinatarioFunzSecSoapval = new soapval('destinatarioSecFunz', 'destinatarioSecFunz', $dati['FunzionarioDestSec'], false, false);
        $DestinatarioSecCCSoapval = new soapval('destinatarioSecCC', 'destinatarioSecCC', $dati['FunzionarioDestSecCC'], false, false);

        $FunzionarioSoapval = new soapval('funzionario', 'funzionario', $this->funzionarioArr, false, false);
        $AnnoPrecSoapval = new soapval('annoAntecedente', 'snnoAntecedente', $dati['AnnoPrec'], false, false);
        $ProtPrecSoapval = new soapval('numeroAntecedente', 'numeroAntecedente', $dati['ProtPrec'], false, false);

        $param = $IstatSoapval->serialize('literal') . $MittenteSoapval->serialize('literal') . $IndirizzoSoapval->serialize('literal') . $OggettoSoapval->serialize('literal') . $UOSoapval->serialize('literal') .
                $DestinatarioUOSoapval->serialize('literal') . $DestinatarioFunzSoapval->serialize('literal') . $DestinatarioUOSecSoapval->serialize('literal') . $DestinatarioFunzSecSoapval->serialize('literal') .
                $DestinatarioSecCCSoapval->serialize('literal') . $FunzionarioSoapval->serialize('literal') . $AnnoPrecSoapval->serialize('literal') . $ProtPrecSoapval->serialize('literal');

        return $this->ws_call('set4ProtocolloEntrata', $param);
    }

    public function ws_SetAllegato4Protocollo($dati) {
        $IstatSoapval = new soapval('istatEnte', 'istatEnte', $this->istatAmministrazione, false, false);
        $AnnoSoapval = new soapval('anno', 'anno', $dati['Anno'], false, false);
        $NumeroSoapval = new soapval('numero', 'numero', $dati['Numero'], false, false);
        $ImageSoapval = new soapval('file', 'file', $dati['Image'], false, false);
        $NomeFileSoapval = new soapval('nomeFile', 'nomeFile', $dati['Filename'], false, false);
        $DescSoapval = new soapval('descrizione', 'descrizione', $dati['Descrizione'], false, false);
        $PrincipaleFunzSoapval = new soapval('principale', 'principale', $dati['Principale'], false, false);

        $param = $IstatSoapval->serialize('literal') . $AnnoSoapval->serialize('literal') . $NumeroSoapval->serialize('literal') . $ImageSoapval->serialize('literal') .
                $NomeFileSoapval->serialize('literal') . $DescSoapval->serialize('literal') . $PrincipaleFunzSoapval->serialize('literal');

        return $this->ws_call('setAllegato4Protocollo', $param);
    }

}

?>
