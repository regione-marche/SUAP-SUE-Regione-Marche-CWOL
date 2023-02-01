<?php

/**
 *
 * Classe per collegamento ws Paleo
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaPHPPaleo
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    20.02.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

//require_once(ITA_LIB_PATH . '/nusoap/nusoap1.2.php');

class itaWSProcedimarcheClient {

    private $nameSpaces = array();
    private $mainNamespace = "";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $cf_ente = "";
    private $tipoDocumento = "";
    private $aggiornaAnagrafiche = "";
    private $CodiceAmministrazione = "";
    private $CodiceAOO = "";
    private $timeout = 2400;
    private $SOAPHeader;
    private $result;
    private $error;
    private $fault;
    private $namespaceTem;
    private $namespaceWcf;

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    public function setMainNamespace($namespace) {
        $this->mainNamespace = $namespace;
    }

    public function getNameSpaces() {
        return $this->nameSpaces;
    }

    public function getNamespaceTem() {
        return $this->namespaceTem;
    }

    public function getNamespaceWcf() {
        return $this->namespaceWcf;
    }

    public function setNamespaceTem($namespaceTem) {
        App::log('setto tem');
        App::log($namespaceTem);
        $this->namespaceTem = $namespaceTem;
    }

    public function setNamespaceWcf($namespaceWcf) {
        App::log('setto wcf');
        App::log($namespaceWcf);
        $this->namespaceWcf = $namespaceWcf;
    }

    public function setNameSpaces($tipo = 'tem') {
        if ($tipo == 'tem') {
            $nameSpaces = array(
                "tem" => $this->namespaceTem,
                "wcf" => $this->namespaceWcf);
        }
        $this->nameSpaces = $nameSpaces;
        App::log('settati namespaces');
        App::log($this->nameSpaces);
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

    public function getCF() {
        return $this->cf_ente;
    }

    public function setCF($cf_ente) {
        $this->cf_ente = $cf_ente;
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

    public function getTipoDocumento() {
        return $this->tipoDocumento;
    }

    public function setTipoDocumento($tipoDocumento) {
        $this->tipoDocumento = $tipoDocumento;
    }

    public function getAggiornaAnagrafiche() {
        return $this->aggiornaAnagrafiche;
    }

    public function setAggiornaAnagrafiche($aggiornaAnagrafiche) {
        if ($aggiornaAnagrafiche) {
            $this->aggiornaAnagrafiche = $aggiornaAnagrafiche;
        } else {
            $this->aggiornaAnagrafiche = "F";
        }
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
        App::log('param');
        App::log($param);
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
        $client->debugLevel = 0;
        $client->timeout = $this->timeout > 0 ? $this->timeout : 120;
        $client->response_timeout = $this->timeout;
//        $client->setCredentials($this->username, $this->password, 'basic');
        $client->soap_defencoding = 'UTF-8';
        $soapAction = $this->mainNamespace . "/" . $operationName;
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';
        $result = $client->call($ns . $operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
        //$result = $client->call($operationName, $param, $nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
        file_put_contents("/users/pc/dos2ux/DOCUMENTI/wsProcedimarche/request.xml", $client->request);
        file_put_contents("/users/pc/dos2ux/DOCUMENTI/wsProcedimarche/response.xml", $client->response);

        App::log('request');
        App::log($client->request);
        App::log('response');
        App::log($client->response);
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
                //Out::msgStop("REQUEST", print_r($client->request, true));
                //throw new Exception("Client SOAP Error: " . $err);
                return false;
            }
        }
        $this->result = $result;
        return true;
    }

    /**
     * 
     * @param type $param array("id", "passwrod")
     * @return type
     */
    public function ws_GetTipiProcedimentoCompleti() {
        $idSoapval = new soapval('tem:id', 'tem:id', $this->username, false, false);
        $passwordSoapval = new soapval('tem:password', 'tem:password', $this->password, false, false);
        $param = $idSoapval->serialize('literal') . $passwordSoapval->serialize('literal');
        return $this->ws_call('GetTipiProcedimentoCompleti', $param);
    }

    /**
     * 
     * @param type $param array("id", "passwrod")
     * @return type
     */
    public function ws_GetEntiAbilitati() {
        $idSoapval = new soapval('tem:user', 'tem:user', $this->username, false, false);
        $passwordSoapval = new soapval('tem:password', 'tem:password', $this->password, false, false);
        $param = $idSoapval->serialize('literal') . $passwordSoapval->serialize('literal');
        return $this->ws_call('GetEntiAbilitati', $param);
    }

    /**
     * 
     * @param type $param array("id", "passwrod")
     * @return type
     */
    public function ws_GetTipiFascicolo() {
        $idSoapval = new soapval('tem:id', 'tem:id', $this->username, false, false);
        $passwordSoapval = new soapval('tem:password', 'tem:password', $this->password, false, false);
        $param = $idSoapval->serialize('literal') . $passwordSoapval->serialize('literal');
        return $this->ws_call('GetTipiFascicolo', $param);
    }

    /**
     * 
     * @param type $param array("id", "passwrod")
     * @return type
     */
    public function ws_IsValidCredentials() {
        $idSoapval = new soapval('tem:id', 'tem:id', $this->username, false, false);
        $passwordSoapval = new soapval('tem:password', 'tem:password', $this->password, false, false);
        $param = $idSoapval->serialize('literal') . $passwordSoapval->serialize('literal');
        return $this->ws_call('IsValidCredentials', $param);
    }

    /**
     * 
     * @param type $param array("cf_ente", "id", "passwrod", "arrayTipoProEnte")
     * @return type
     */
    public function ws_SaveTipiProcedimentoEnte($dati) {
        $paramArr = array();
        $param = "";
        if (is_array($dati['arrayTipoProEnte'])) {
            $MyTipoProEnteSoapvalArr = array();
            foreach ($dati['arrayTipoProEnte'] as $MyTipoProEnte) {
                if ($MyTipoProEnte instanceof itaMyTipoProEnte) {
                    $MyTipoProEnteSoapvalArr[] = $MyTipoProEnte->getSoapValRequest();
                } else {
                    $this->error = "Oggetto MyTipoProEnte non della classe itaMyTipoProEnte";
                    return false;
                }
            }
            $MyTipoProEnteSoapval = new soapval('tem:arrayTipoProEnte', 'tem:arrayTipoProEnte', $MyTipoProEnteSoapvalArr, false, false);
            $paramArr[] = $MyTipoProEnteSoapval;
        }
        if (isset($dati['cf_ente'])) {
            $paramArr[] = new soapval('tem:cf_ente', 'tem:cf_ente', $dati['cf_ente'], false, false);
        }
        if (isset($dati['id'])) {
            $paramArr[] = new soapval('tem:id', 'tem:id', $dati['id'], false, false);
        } else {
            $paramArr[] = new soapval('tem:id', 'tem:id', $this->username, false, false);
        }
        if (isset($dati['password'])) {
            $paramArr[] = new soapval('tem:password', 'tem:password', $dati['password'], false, false);
        } else {
            $paramArr[] = new soapval('tem:password', 'tem:password', $this->password, false, false);
        }
        foreach ($paramArr as $parametro) {
            $param .= $parametro->serialize('literal');
        }
        App::log('log chiamata insert tipo procedimento');
        App::log($param);
        return $this->ws_call('SaveTipiProcedimentoEnte', $param);
    }

}

?>
