<?php

/**
 *
 * Classe per collegamento ws Paleo
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaPHPSolWS
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    05.09.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

class itaSolClient {

    private $namespace = "";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $max_execution_time = 2400;
    private $SOAPHeader;
    private $result;
    private $error;
    private $fault;

    private $provider;
    private $providerObj;

    
    public function setNamespace($namespace) {
        $this->namespace = $namespace;
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

    public function setpassword($password) {
        $this->password = $password;
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

    private function ws_call($operationName, $param, $wsdl=true) {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_wsdl, $wsdl);
        $client->debugLevel = 0;
        $client->timeout = 120;
        $client->response_timeout = 120;        
        $client->soap_defencoding = 'UTF-8';
        $result = $client->call($operationName, $param, array("soapenv" => "http://schemas.xmlsoap.org/soap/envelope"));

        file_put_contents("/users/pc/dos2ux/solWSRequest_$operationName.txt", $client->request);
        file_put_contents("/users/pc/dos2ux/solWSResponse_$operationName.txt", $client->response);
        
        if ($client->fault) {
            $this->fault = $client->faultstring;
            //Out::msgInfo("FAULT", "<pre>" . $client->request . "</pre>");
            print_r("FAULT", "<pre>" . $client->request . "</pre>");
            return false;

        } else {
            $err = $client->getError();
            if ($err) {
                $this->error = $err;
                print_r("<pre>");
                print_r("error");
                print_r("</pre>");
                throw new Exception("Client Error:" . $err);
                return false;
            }
        }
        $this->result = $result;
        return true;
    }

    public function ws_getDatiAnagrafici($codiceFiscale) {
        $param = array(
                "codiceFiscale" => $codiceFiscale
        );

        return $this->ws_call('getDatiAnagrafici', $param);
    }

    /**
     * Legge Dati soggetto per codice fiscale
     * @param type $codiceFiscale
     * @return type
     */
    public function ws_getSoggettoCompleto($codiceFiscale) {
       $param = array(
                "codiceFiscale" => $codiceFiscale
        );

        return $this->ws_call('getSoggettoCompleto', $param, false);
    }
    
    /**
     * Legge dati soggetto per Cognome e Nome 
     * @param type $cognome
     * @param type $nome
     * @return type
     */
    public function ws_getSoggetto1($cognome = '', $nome = '' ) {
        $param = array(
                "cognome" => $cognome,
                "nome" => $nome
        );
        return $this->ws_call('getSoggetto', $param,false);
    }

    /**
     * Legge Dati componenti
     * @param type $codiceFiscale
     * @param array $tipoApp
     * @return type
     */
    public function ws_getComponenti($codiceFiscale, $tipoApp) {
       $param = array(
                "codiceFiscale" => $codiceFiscale,
                "tipiApp" => $tipoApp
        );
        return $this->ws_call('getComponenti', $param, false);
    }
    
    

    function getVie(){
        return $this->providerObj->getVie();
    }

    function getCittadinoVariazioni(){
        return $this->providerObj->getCittadinoVariazioni();
    }
    
    
}

?>
