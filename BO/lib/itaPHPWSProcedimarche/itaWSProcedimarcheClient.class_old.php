<?php

/**
 *
 * Classe per collegamento ws Poste Italiane
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaPHPNPCE
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    05.11.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
//require_once(ITA_LIB_PATH . '/nusoap/nusoap1.2.php');
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

class itaWSProcedimarcheClient {

    private $mainNamespace = "";
    private $nameSpaces = array();
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

    public function setMainNamespace($mainNamespace) {
        $this->mainNamespace = $mainNamespace;
    }

    public function getMainNamespace() {
        return $this->mainNamespace;
    }

    public function setNamespaces() {
        $this->com != "" ? $com = $this->com : $com = "http://ComunicazioniElettroniche.ROL.WS";
        $this->com1 != "" ? $com1 = $this->com1 : $com1 = "http://ComunicazioniElettroniche.XOL";
        $nameSpaces = array(
            "soapenv" => "http://schemas.xmlsoap.org/soap/envelope/",
            "com" => $com,
            "com1" => $com1
        );
        $this->nameSpaces = $nameSpaces;
    }

    public function getNamespaces() {
        return $this->nameSpaces;
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

    function getWSSecurity($username, $password, $param = array()) {
        $timestamp = date('Y-m-d\TH:i:s') . ".123Z";
        $timestamp_expire = date('Y-m-d\TH:i:s', time() + 600) . ".123Z";
        $nonce = mt_rand();
        $wsse = '
                <wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                    <wsse:UsernameToken>
                        <wsse:Username>' . $username . '</wsse:Username>
                        <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' . $password . '</wsse:Password>
                        <wsse:Nonce>' . base64_encode(pack('H*', $nonce)) . '</wsse:Nonce>
                        <wsu:Created>' . $timestamp . '</wsu:Created>
                    </wsse:UsernameToken>
                </wsse:Security>';
//        $wsse = '
//                <wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
//                    <wsse:UsernameToken>
//                        <wsse:Username>' . $username . '</wsse:Username>
//                        <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' . $password . '</wsse:Password>
//                        <wsu:Created>' . $timestamp . '</wsu:Created>
//                    </wsse:UsernameToken>
//                </wsse:Security>';
        //<wsu:Expires>' . $timestamp_expire . '</wsu:Expires>
        return $wsse;
    }

    private function ws_call($operationName, $param = array()) {

        //require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');
        App::log('1');
        App::log($operationName);
        //$client = new nusoap_client("http://wsprocedimenti.regione.marche.it/ServiceProcedimenti.svc?wsdl", true);
        $client = new SoapClient(
                "http://wsprocedimenti.regione.marche.it/ServiceProcedimenti.svc?singleWsdl", array(
            'encoding' => 'UTF-8',
            'soap_version' => SOAP_1_2,
            'trace' => 1,
            'exceptions' => 0
                )
        );
        App::log('2');
        $actionHeader = new SoapHeader('http://www.w3.org/2005/08/addressing','Action','https://wsprocedimenti.regione.marche.it/WsProcedimenti/testCredentials',true);
        App::log('3');
        $client->__setSoapHeaders($actionHeader);
        App::log('4');
        $retval = $client->__soapCall('testCredentials',array());
        App::log('retval');
        App::log($retval);
        Out::msgInfo("RET", print_r($retval, true));
        return;
//        $client = new SoapClient(
//                NULL, array(
//            'login' => '1000',
//            'password' => 'pwd1000',
//            'soap_version' => SOAP_1_2,
//            'location' => "https://wsprocedimenti.regione.marche.it/ServiceProcedimenti.svc",
//            'uri' => 'https://wsprocedimenti.regione.marche.it/',
//            'trace' => TRUE,
//            'local_cert' => ITA_LIB_PATH . '/itaPHPWSProcedimarche/wsprocedimenti.cer'
//                )
//        );
//        $client = new SoapClient(
//                "https://wsprocedimenti.regione.marche.it/ServiceProcedimenti.svc?singleWsdl", array(
//            'trace' => true,
//            'soap_version' => SOAP_1_2
//                )
//        );
//        $client = new SoapClient(
//                "http://wsprocedimenti.regione.marche.it/ServiceProcedimenti.svc?singleWsdl", array(
//            'trace' => true,
//            'soap_version' => SOAP_1_2,
//            'connection_timeout' => 60
//                )
//        );
        App::log('2');
        App::log($operationName);
//        $funzioni = $client->__getFunctions();
//        Out::msgInfo("FUNZIONI", print_r($funzioni, true));
//        return;

        App::log('3');
//        $header = new SoapHeader('https://wsprocedimenti.regione.marche.it/', 
//                            'headerRequest',
//                            $this->getWSSecurity($this->username, $this->password));
//        $client->__setSoapHeaders($header);
        App::log('4');
        try {
            $data = $client->__soapCall($operationName, array());
        } catch (Exception $e) {
            App::log('eccezione');
            Out::msgStop("Eccezione", $e->getMessage());
        }
        App::log('5');
        Out::msgInfo("request", $client->__getLastRequest());
        file_put_contents("/users/pc/dos2ux/Mario/request.xml", $client->__getLastRequest());
        App::log('data');
        App::log(print_r($data, true));
        //                        $client->setCredentials("1000", "pwd1000", "certificate", array(
        //                            'sslcertfile' => '/users/pc/dos2ux/DOCUMENTI/wsprocedimenti/wsprocedimenti.pem',
        //                            'sslkeyfile' => '/users/pc/dos2ux/DOCUMENTI/wsprocedimenti/wsprocedimenti.pem',
        //                            'passphrase' => '',
        //                            'verifypeer' => FALSE,
        //                            'verifyhost' => FALSE
        //                        ));
//        $param = array(
//            'arrayTipoProEnte' => array(
//                'TipoProEnte' => array()
//            )
//        );
//        $client->soap_defencoding = 'UTF-8';
//        $client->decode_utf8 = false;
//        $ret = $client->call("insertTipoProcedimentiEnte", $param);
        if ($client->fault) {
            app::log('fault call');
            App::log($client->request);
            $this->fault = $client->faultstring;
            //throw new Exception("Request Fault:" . $this->fault);
            return false;
        } else {
            $err = $client->getError();
            if ($err) {
                app::log('error call');
                App::log($client->request);
                $this->error = $err;
                Out::msgStop("RISPOSTA", print_r($client->response, true));
                throw new Exception("Client SOAP Error: " . $err);
                return false;
            }
        }
        $risultato = print_r($ret, true);
        App::log('ret');
        App::log($ret);
        App::log('risultato');
        App::log($risultato);
        App::log('5');
        Out::msgInfo("insertTipoProcedimentiEnte Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');
        break;



//        $this->clearResult();
//        $client = new nusoap_client($this->webservices_uri, false);
//        $client->debugLevel = 0;
//        $client->timeout = $this->timeout;
//        $client->response_timeout = $this->timeout;
//        $client->setCredentials($this->username, $this->password, 'basic');
//        $client->soap_defencoding = 'UTF-8';
//        $soapAction = $this->mainNamespace . "/" . $operationName;
//        $headers = false;
//        $rpcParams = null;
//        $style = 'rpc';
//        $use = 'literal';
//        $result = $client->call("com:" . $operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
//        App::log('request');
//        App::log($client->request);
////        file_put_contents("/users/pc/dos2ux/Mario/request.xml", $client->request);
//        //$result = $client->call($operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
//        if ($client->fault) {
//            app::log('fault call');
//            App::log($client->request);
//            $this->fault = $client->faultstring;
//            //throw new Exception("Request Fault:" . $this->fault);
//            return false;
//        } else {
//            $err = $client->getError();
//            if ($err) {
//                app::log('error call');
//                App::log($client->request);
//                $this->error = $err;
//                //Out::msgStop("REQUEST", print_r($client->request, true));
//                throw new Exception("Client SOAP Error: " . $err);
//                return false;
//            }
//        }
        $this->result = $result;
        return true;
    }

    public function ws_getAllCompleteTipoProcedimento() {
        return $this->ws_call('getAllCompleteTipoProcedimento');
    }

    public function ws_getEntiAbilitati() {
        return $this->ws_call('getEntiAbilitati');
    }

    /**
     * 
     * @param type parametri chiamata
     * @return type
     */
    public function ws_insertTipoProcedimentiEnte($param) {
        return $this->ws_call('insertTipoProcedimentiEnte', $param);
    }

    public function ws_testCredentials() {
        return $this->ws_call('testCredentials');
    }

}

?>
