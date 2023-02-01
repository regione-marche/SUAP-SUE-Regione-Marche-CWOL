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
    private $max_execution_time = 120;
    private $SOAPHeader;
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

    private function ws_call($operationName, $param,$wsdl=true) {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_wsdl, $wsdl);
        $client->soap_defencoding = 'UTF-8';
        $result = $client->call($operationName, $param);
//        Out::msgInfo("FAULT", "<pre>" . print_r($client->request,true) . "</pre>");
//        App::log('request');
//        App::log($client->request);
        if ($client->fault) {
            $this->fault = $client->faultstring;
            //throw new Exception("Request Fault:" . $this->fault);
            Out::msgInfo("FAULT", "<pre>" . $client->request . "</pre>");
//            print_r("<pre>");
//            print_r("fault");
//            $client->request;
//            print_r("</pre>");
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
    public function ws_getSoggetto($codiceFiscale) {
       $param = array(
                "codiceFiscale" => $codiceFiscale
        );

        return $this->ws_call('getSoggetto', $param,false);
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


}

?>
