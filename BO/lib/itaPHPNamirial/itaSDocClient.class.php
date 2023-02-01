<?php

require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');
require_once(ITA_LIB_PATH . '/nusoap/nusoapmime.php');
require_once ITA_LIB_PATH . '/itaPHPCore/itaSOAP.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaUUID.class.php';

class SDoc {

    private $nameSpaces = array();
    private $namespace = "";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $timeout = 2400;
    private $connectionTimeout = 2400;
    private $debugLevel;
    private $result;
    private $response;
    private $error;
    private $fault;
    private $tempns = 'tem';

    public function getNameSpaces() {
        return $this->nameSpaces;
    }

    public function getUsername() {
        return $this->username;
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

    public function getResult() {
        return $this->result;
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

    public function setResult($result) {
        $this->result = $result;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getTimeout() {
        return $this->timeout;
    }

    public function getDebugLevel() {
        return $this->debugLevel;
    }

    public function getError() {
        return $this->error;
    }

    public function getFault() {
        return $this->fault;
    }

    public function setNameSpaces($nameSpaces) {
        $this->nameSpaces = $nameSpaces;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    public function setDebugLevel($debugLevel) {
        $this->debugLevel = $debugLevel;
    }

    public function setError($error) {
        $this->error = $error;
    }

    public function setFault($fault) {
        $this->fault = $fault;
    }

    public function setResponse($response) {
        $this->response = $response;
    }

    public function getResponse() {
        return $this->response;
    }

    protected function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }

    public function getTempns() {
        return $this->tempns;
    }

    public function setTempns($tempns) {
        $this->tempns = $tempns;
    }

    /**
     * Chiamata generica a ws soap
     * 
     * @param String $wsdl
     * @param String $operationName
     * @param array / String $param
     * @return boolean
     */
    protected function ws_call($operationName, $param = array(), $soapAction = '', $additionalSoapHeaders = array(), $attachments = array()) {

        $this->clearResult();
        ///* @var $client nusoap_client_mime */
        $client = new nusoap_client_mime($this->webservices_uri, false);
        $client->debugLevel = $this->debugLevel;
        $client->timeout = $this->timeout;
        $client->soap_defencoding = 'utf-8';
        $client->http_encoding = 'gzip, deflate';
        $AddHeaderString = '';
        if ($additionalSoapHeaders) {
            $AddHeaderString = $this->createAdditionaSoapHeadersFromParam($additionalSoapHeaders);
        }
        $wsse = $this->getWSSecurity($this->username, $this->password);
        //$client->setHeaders($this->getWSSecurity($this->username, $this->password));
        $client->setHeaders($wsse . $AddHeaderString);

        $nameSpaces = array(
            'u' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd',
            'tem' => 'http://tempuri.org/',
            'xop' => 'http://www.w3.org/2004/08/xop/include'
        );
        $ns = $this->tempns . ':';
        // $soapAction = $soapActionPath . $operationName;
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';

        foreach ($attachments as $attachment) {
            $client->addAttachment(
                    $attachment['data'], $attachment['filename'], $attachment['contenttype'], $attachment['cid']
            );
        }

        /*
         * param
         */
        if (is_array($param)) {
            $param = $this->CheckNameSpaces($param);
        }

        $result = $client->call($ns . $operationName, $param, $nameSpaces, $soapAction, $headers, $rpcParams, $style, $use, false, $soapMessage);
        $this->response = $client->response;
      //  Out::msgInfo("Request", print_r(htmlspecialchars($client->request), true));
      //  Out::msgInfo("Response", print_r(htmlspecialchars($client->response), true));
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

    private function createWSSecurityTimestamp($wsID, $ns = 'u:', $duration = 300) {
        $created = gmdate('Y-m-d\TH:i:s\Z', time());
        $expires = gmdate('Y-m-d\TH:i:s\Z', time() + $duration);
        $str = '<' . $ns . 'Timestamp ' . $ns . 'Id="' . $wsID . '">';
        $str .= '   <' . $ns . 'Created>' . $created . '</' . $ns . 'Created>';
        $str .= '   <' . $ns . 'Expires>' . $expires . '</' . $ns . 'Expires>';
        $str .= '</' . $ns . 'Timestamp>';
        return $str;
    }

    private function getWSSecurity($username, $password, $param = array()) {
        $userNameTokenId = 'uuid-' . itaUUID::getV4();
        return '
            <o:Security SOAP-ENV:mustUnderstand="1" xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
               ' . $this->createWSSecurityTimestamp('_0') . '
                <o:UsernameToken u:Id="' . $userNameTokenId . '">
                    <o:Username>' . $username . '</o:Username>
                    <o:Password o:Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' . $password . '</o:Password>
                </o:UsernameToken>
            </o:Security>';
    }

    private function createAdditionaSoapHeadersFromParam($params) {
        $HeaderString = '';
        foreach ($params as $key => $param) {
            $HeaderString .= "<$key>$param</$key>";
        }

        return $HeaderString;
    }

    private function CheckNameSpaces($params) {
        $CheckedParam = array();
        foreach ($params as $key => $param) {
            /*
             * Controllo Chiave:
             */
            if (strpos($key, $this->tempns . ':') === false) {
                $key = $this->tempns . ':' . $key;
            }
            if (is_array($param)) {
                $param = $this->CheckNameSpaces($param);
            }
            $CheckedParam[$key] = $param;
        }
        return $CheckedParam;
    }

}
