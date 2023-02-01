<?php

/**
 *
 * Classe per collegamento jProtocollo services
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaInforWS
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    19.11.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

//require_once(ITA_LIB_PATH . '/nusoap/nusoap1.2.php');

class itaInforClient {

    private $namespace = "http://webservices.jprotocollo.jente.infor.arezzo.it/";
    private $namespacePrefix = "nsjp";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $max_execution_time = 600;
    private $result;
    private $error;
    private $fault;

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    public function setNamespacePrefix($namespacePrefix) {
        $this->namespacePrefix = $namespacePrefix;
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
        $client = new nusoap_client($this->webservices_uri, false);
        //setting timeout
        $client->timeout = 500;
        $client->response_timeout = 500;
        $client->soap_defencoding = 'UTF-8';
        $client->debugLevel = 0; //Mario 19.08.2013

        $result = $client->call($operationName, $param, array(
            "soapenv" => "http://schemas.xmlsoap.org/soap/envelope/",
            $this->namespacePrefix => $this->namespace
                )
        );
//        file_put_contents("/users/tmp/request_$operationName.xml", $client->request);
//        file_put_contents("/users/tmp/response_$operationName.xml", $client->response);
//        file_put_contents("/users/tmp/param_$operationName.xml", $param);
       
        if ($client->fault) {
            $this->fault = $client->faultstring;
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

    public function ws_leggiProtocollo($LeggiProtocollo) {
        return $this->ws_call($this->namespacePrefix . ":leggiProtocollo", $LeggiProtocollo->getRichiesta($this->namespacePrefix));
    }

    public function ws_inserisciArrivo($InserisciArrivo) {
        return $this->ws_call($this->namespacePrefix . ":inserisciArrivo", $InserisciArrivo->getRichiesta($this->namespacePrefix));
    }

    public function ws_inserisciPartenza($InserisciPartenza) {
        return $this->ws_call($this->namespacePrefix . ":inserisciPartenza", $InserisciPartenza->getRichiesta($this->namespacePrefix));
    }

    public function ws_inserisciInterno($InserisciInterno) {
        return $this->ws_call($this->namespacePrefix . ":inserisciInterno", $InserisciInterno->getRichiesta($this->namespacePrefix));
    }

    public function ws_allegaDocumento($AllegaDocumento) {
        return $this->ws_call($this->namespacePrefix . ":allegaDocumento", $AllegaDocumento->getRichiesta($this->namespacePrefix));
    }

    public function ws_confermaSegnatura($ConfermaSegnatura) {
        return $this->ws_call($this->namespacePrefix . ":confermaSegnatura", $ConfermaSegnatura->getRichiesta($this->namespacePrefix));
    }

    public function ws_inviaProtocollo($InviaProtocollo) {
        return $this->ws_call($this->namespacePrefix . ":inviaProtocollo", $InviaProtocollo->getRichiesta($this->namespacePrefix));
    }

    public function ws_leggiAllegato($LeggiAllegato) {
        return $this->ws_call($this->namespacePrefix . ":leggiAllegato", $LeggiAllegato->getRichiesta($this->namespacePrefix));
    }

}

?>
