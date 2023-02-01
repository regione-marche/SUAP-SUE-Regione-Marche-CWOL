<?php

/**
 *
 * Classe per collegamento ws IRIDE
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPIride
 * @author     Mario Mazza <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2018 Italsoft srl
 * @license
 * @version    20.03.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

//require_once(ITA_LIB_PATH . '/nusoap/nusoap1.2.php');

class itaWsTabelleClient {

    private $nameSpaces = array();
    private $namespace = "";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $utente = "";
    private $ruolo = "";
    private $CodiceAmministrazione = "";
    private $CodiceAOO = "";
    private $timeout = 2400;
    private $SOAPHeader;
    private $result;
    private $error;
    private $fault;

//    public function setTimeout($timeout) {
//        $this->timeout = $timeout;
//    }
//
//    public function setNamespace($namespace) {
//        $this->namespace = $namespace;
//    }

    public function getNameSpaces() {
        return $this->nameSpaces;
    }

    public function setNameSpaces($tipo = 'tem') {
        if ($tipo == 'tem') {
            $nameSpaces = array("tem" => "http://tempuri.org/");
        }
        if ($tipo == 'sch') {
            $nameSpaces = array("sch" => "http://wwwpa2k/Ulisse/iride/web_services/ws_tabelle/schema");
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

    public function getUtente() {
        return $this->utente;
    }

    public function setUtente($utente) {
        $this->utente = $utente;
    }

    public function getRuolo() {
        return $this->ruolo;
    }

    public function setRuolo($ruolo) {
        $this->ruolo = $ruolo;
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

    public function getCodiceAmministrazione() {
        return $this->CodiceAmministrazione;
    }

    public function getCodiceAOO() {
        return $this->CodiceAOO;
    }

    public function setCodiceAmministrazione($CodiceAmministrazione) {
        $this->CodiceAmministrazione = $CodiceAmministrazione;
    }

    public function setCodiceAOO($CodiceAOO) {
        $this->CodiceAOO = $CodiceAOO;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }

    private function ws_call($operationName, $param, $ns = "tem:") {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
        $client->debugLevel = 0;
        $client->timeout = $this->timeout > 0 ? $this->timeout : 120;
        $client->response_timeout = $this->timeout;
        $client->setCredentials($this->username, $this->password, 'basic');
        $client->soap_defencoding = 'UTF-8';
        //$soapAction = $this->namespace . "/" . $operationName;
        $soapAction = $this->nameSpaces['sch'] . "/" . $operationName;
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
//        $style = 'document';
        $use = 'literal';
        $result = $client->call($ns . $operationName , $param , $this->nameSpaces , $soapAction , $headers , $rpcParams , $style , $use);
        //$result = $client->call($operationName, $param, $nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
        file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/param_$operationName.xml" , $param);
        file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/request_$operationName.xml" , $client->request);
        file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/response_$operationName.xml" , $client->response);
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

    public function wm_tipiDocumento(

        $filtro = "") {
        $param = "";
        //if ($filtro != "") {
        $filtroSoapval = new soapval('sch:filtro', 'sch:filtro', $filtro, false, false);
        $param = $filtroSoapval->serialize('literal');
        //}
        return     $this->ws_call('wm_tipiDocumento', $param, 'sch:');
    }

    public function wm_struttura(

        $filtro = "") {
        $param = "";
        //if ($filtro != "") {
        $filtroSoapval = new soapval('sch:filtro', 'sch:filtro', $filtro, false, false);
        $param = $filtroSoapval->serialize('literal');
        //}
        return     $this->ws_call('wm_struttura', $param, 'sch:');
    }

    public function wm_classifiche(

        $filtro = "") {
        $param = "";
        //if ($filtro != "") {
        $filtroSoapval = new soapval('sch:filtro', 'sch:filtro', $filtro, false, false);
        $param = $filtroSoapval->serialize('literal');
        //}
        return     $this->ws_call('wm_classifiche', $param, 'sch:');
    }

}

?>
