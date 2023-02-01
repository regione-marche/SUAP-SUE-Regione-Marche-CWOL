<?php

/**
 *
 * Classe per collegamento ws ARSS Aruba
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaPHPARSS
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    01.03.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(dirname(__FILE__) . "/lib/nusoap/nusoap.php");

class itaARSSClient {

    private $params;
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $SOAPHeader;
    private $result;
    private $error;
    private $fault;

    function __construct($params) {
        $this->params = $params;
    }

    public function getWebservices_wsdl() {
        return $this->webservices_wsdl;
    }

    public function setWebservices_wsdl($webservices_wsdl) {
        $this->webservices_wsdl = $webservices_wsdl;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getSOAPHeader() {
        return $this->SOAPHeader;
    }

    public function setSOAPHeader($SOAPHeader) {
        $this->SOAPHeader = $SOAPHeader;
    }

    public function getResult() {
        return $this->result;
    }

    public function setResult($result) {
        $this->result = $result;
    }

    public function getError() {
        return $this->error;
    }

    public function setError($error) {
        $this->error = $error;
    }

    public function getFault() {
        return $this->fault;
    }

    public function setFault($fault) {
        $this->fault = $fault;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }

    private function ws_call($operationName, $soapAction, $param) {
        $this->clearResult();

        if (!$this->params->getUri() || !$this->params->getNameSpace()) {
            $this->error = "Servizio non configurato contattare l'assistenza per la sua attivazione.";
            return false;
        }

        $client = new nusoap_client($this->params->getUri(), false);
        $client->debugLevel = $this->params->getDebugLevel();
        $client->timeout = $this->params->getTimeout();
        $client->response_timeout = $this->params->getTimeout();
        $client->soap_defencoding = 'UTF-8';
            
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';
        $result = $client->call($operationName, $param, $this->params->getNameSpace(), $soapAction, $headers, $rpcParams, $style, $use);
        file_put_contents("c:\{$operationName}.log", $client->request) ;
        //"Request", print_r(htmlspecialchars($client->request),true));
        
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

    public function ws_addpkcs7sign($SignRequestV2) {
        $param = array();
        $param['SignRequestV2'] = $this->toArray($SignRequestV2);
        if (!$param['SignRequestV2']['session_id']) {
            unset($param['SignRequestV2']['session_id']);
        }
//        $param['detached'] = $detached;
        return $this->ws_call('addpkcs7sign', 'addpkcs7sign', $param);
    }

    public function ws_pkcs7signV2($SignRequestV2, $detached = false) {
        $param = array();
        $param['SignRequestV2'] = $this->toArray($SignRequestV2);
        if (!$param['SignRequestV2']['session_id']) {
            unset($param['SignRequestV2']['session_id']);
        }
        $param['detached'] = $detached;
        return $this->ws_call('pkcs7signV2', 'pkcs7signV2', $param);
    }

    public function ws_xmlSignature($SignRequestV2, $XmlSignParameter) {
        $param = array();
        $param['SignRequestV2'] = $this->toArray($SignRequestV2);
        if (!$param['SignRequestV2']['session_id']) {
            unset($param['SignRequestV2']['session_id']);
        }
        $param['parameter'] = $this->toArray($XmlSignParameter);
        return $this->ws_call('xmlsignature', 'xmlsignature', $param);
    }

    public function ws_verify($VerifyRequest){
        $param = array();
        $param['request'] = $this->toArray($VerifyRequest);
        return $this->ws_call('verify', 'verify', $param);
        
    }
    
    public function ws_retriveCredential($Identity, $type) {
        $param = array();
        $param['identity'] = $this->toArray($Identity);
        $param['type'] = $type;
        return $this->ws_call('retriveCredential', 'retriveCredential', $param);
    }

    public function ws_opensession($identity) {
        $param = array();
        $param['Identity'] = $this->toArray($identity);
        return $this->ws_call('opensession', 'opensession', $param);
    }

    public function ws_closesession($identity, $Sessionid) {
        $param = array();
        $param['Identity'] = $this->toArray($identity);
        $param['sessionid'] = $this->toArray($Sessionid);
        return $this->ws_call('closesession', 'closesession', $param);
    }

    private function toArray($obj) {
        if (is_object($obj))
            $obj = get_object_vars($obj);
        if (is_array($obj)) {
            $new = array();
            foreach ($obj as $key => $val) {
                $new[$key] = $this->toArray($val);
            }
        } else {
            $new = $obj;
        }
        return $new;
    }

}

?>
