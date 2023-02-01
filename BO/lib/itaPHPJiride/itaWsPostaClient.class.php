<?php

/**
 *
 * Classe per collegamento ws IRIDE
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPIride
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2015 Italsoft srl
 * @license
 * @version    30.06.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

//require_once(ITA_LIB_PATH . '/nusoap/nusoap1.2.php');

class itaWsPostaClient {

    private $nameSpaces = array();
    private $namespace = "";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $utente = "";
    private $ruolo = "";
    private $tipoDocumento = "";
    private $invioInteroperabile = "";
    private $CodiceAmministrazione = "";
    private $CodiceAOO = "";
    private $timeout = 2400;
    private $SOAPHeader;
    private $result;
    private $error;
    private $fault;

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    public function getNameSpaces() {
        return $this->nameSpaces;
    }

    public function setNameSpaces($tipo = 'tem') {
        if ($tipo == 'tem') {
            $nameSpaces = array("tem" => "http://tempuri.org/");
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

    public function getTipoDocumento() {
        return $this->tipoDocumento;
    }

    public function setTipoDocumento($tipoDocumento) {
        $this->tipoDocumento = $tipoDocumento;
    }

    public function getInvioInteroperabile() {
        return $this->invioInteroperabile;
    }

    public function setInvioInteroperabile($invioInteroperabile) {
        if ($invioInteroperabile) {
            $this->invioInteroperabile = $invioInteroperabile;
        } else {
            $this->invioInteroperabile = "F";
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
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
        $client->debugLevel = 0;
        $client->timeout = $this->timeout > 0 ? $this->timeout : 120;
        $client->response_timeout = $this->timeout;
        $client->setCredentials($this->username, $this->password, 'basic');
        $client->soap_defencoding = 'UTF-8';
        $soapAction = $this->namespace . "/" . $operationName;
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';
        $result = $client->call($ns . $operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
        //$result = $client->call($operationName, $param, $nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
        file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/param_$operationName.xml", $param);
        file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/request_$operationName.xml", $client->request);
        file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/response_$operationName.xml", $client->response);

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

    public function ws_InviaMail($dati) {
        $paramArr = array();
        if (isset($dati['annoProt'])) {
            $annoProtSoapval = new soapval('annoProt', 'annoProt', $dati['annoProt'], false, false);
            $paramArr[] = $annoProtSoapval;
        }
        if (isset($dati['numProt'])) {
            $numProtSoapval = new soapval('numProt', 'numProt', $dati['numProt'], false, false);
            $paramArr[] = $numProtSoapval;
        }
        $docIdSoapval = new soapval('docId', 'docId', $dati['docId'], false, false);
        $paramArr[] = $docIdSoapval;
        if (isset($dati['oggettoMail'])) {
            $oggettoMailSoapval = new soapval('oggettoMail', 'oggettoMail', $dati['oggettoMail'], false, false);
            $paramArr[] = $oggettoMailSoapval;
        }
        if (isset($dati['testoMail'])) {
            $testoMailSoapval = new soapval('testoMail', 'testoMail', $dati['testoMail'], false, false);
            $paramArr[] = $testoMailSoapval;
        }
        if (isset($dati['mittenteMail'])) {
            $mittenteMailSoapval = new soapval('mittenteMail', 'mittenteMail', $dati['mittenteMail'], false, false);
            $paramArr[] = $mittenteMailSoapval;
        }

        if (is_array($dati['destinatariMail'])) {
            $DMSoapvalArr = array();
            foreach ($dati['destinatariMail'] as $destinatarioMail) {
                $destinatarioMailSoapval = new soapval('destinatarioMail', 'destinatarioMail', $destinatarioMail, false, false);
                $DMSoapvalArr[] = $destinatarioMailSoapval;
            }
            $destinatariMailSoapval = new soapval('destinatariMail', 'destinatariMail', $DMSoapvalArr, false, false);
            $paramArr[] = $destinatariMailSoapval;
        }
        if (is_array($dati['destinatariCCMail'])) {
            $DCCMSoapvalArr = array();
            foreach ($dati['destinatariCCMail'] as $destinatarioCCMail) {
                $destinatarioCCMailSoapval = new soapval('destinatarioCCMail', 'destinatarioCCMail', $destinatarioCCMail, false, false);
                $DCCMSoapvalArr[] = $destinatarioCCMailSoapval;
            }
            $destinatariCCMailSoapval = new soapval('destinatariCCMail', 'destinatariCCMail', $DCCMSoapvalArr, false, false);
            $paramArr[] = $destinatariCCMailSoapval;
        }

        $utenteSoapval = new soapval('utente', 'utente', $this->utente, false, false);
        $paramArr[] = $utenteSoapval;
        $ruoloSoapval = new soapval('ruolo', 'ruolo', $this->ruolo, false, false);
        $paramArr[] = $ruoloSoapval;
        if (!isset($dati['InvioInteroperabile'])) {
            $dati['InvioInteroperabile'] = $this->invioInteroperabile;
        }
        $InvioInteroperabileSoapval = new soapval('InvioInteroperabile', 'InvioInteroperabile', $dati['InvioInteroperabile'], false, false);
        $paramArr[] = $InvioInteroperabileSoapval;


        //CREO CDATA
        $strXML = "<tem:strXML><![CDATA[<messaggioIn>";
        foreach ($paramArr as $parametro) {
            $strXML .= $parametro->serialize('literal');
        }
        $strXML .= "</messaggioIn>]]></tem:strXML>";
        $param = $strXML;

        if (isset($dati['CodiceAmministrazione'])) {
            $CodiceAmministrazioneSoapval = new soapval('tem:CodiceAmministrazione', 'tem:CodiceAmministrazione', $dati['CodiceAmministrazione'], false, false);
        } else {
            $CodiceAmministrazioneSoapval = new soapval('tem:CodiceAmministrazione', 'tem:CodiceAmministrazione', $this->CodiceAmministrazione, false, false);
        }
        if (isset($dati['CodiceAOO'])) {
            $CodiceAOOSoapval = new soapval('tem:CodiceAOO', 'tem:CodiceAOO', $dati['CodiceAmministrazione'], false, false);
        } else {
            $CodiceAOOSoapval = new soapval('tem:CodiceAOO', 'tem:CodiceAOO', $this->CodiceAOO, false, false);
        }
        if ($CodiceAmministrazioneSoapval) {
            $param .= $CodiceAmministrazioneSoapval->serialize('literal');
        }
        if ($CodiceAOOSoapval) {
            $param .= $CodiceAOOSoapval->serialize('literal');
        }
        return $this->ws_call('InviaMail', $param);
    }

    public function ws_VerificaInvio($dati) {
        $paramArr = array();
        if (isset($dati['annoProt'])) {
            $annoProtSoapval = new soapval('annoProt', 'annoProt', $dati['annoProt'], false, false);
            $paramArr[] = $annoProtSoapval;
        }
        if (isset($dati['numProt'])) {
            $numProtSoapval = new soapval('numProt', 'numProt', $dati['numProt'], false, false);
            $paramArr[] = $numProtSoapval;
        }
        $docIdSoapval = new soapval('docId', 'docId', $dati['docId'], false, false);
        $paramArr[] = $docIdSoapval;

        $utenteSoapval = new soapval('utente', 'utente', $this->utente, false, false);
        $paramArr[] = $utenteSoapval;
        $ruoloSoapval = new soapval('ruolo', 'ruolo', $this->ruolo, false, false);
        $paramArr[] = $ruoloSoapval;

        //CREO CDATA
        $strXML = "<tem:strXML><![CDATA[<messaggioIn>";

        foreach ($paramArr as $parametro) {
            $strXML .= $parametro->serialize('literal');
        }
        $strXML .= "</messaggioIn>]]></tem:strXML>";
        $param = $strXML;

        if (isset($dati['CodiceAmministrazione'])) {
            $CodiceAmministrazioneSoapval = new soapval('tem:CodiceAmministrazione', 'tem:CodiceAmministrazione', $dati['CodiceAmministrazione'], false, false);
        } else {
            $CodiceAmministrazioneSoapval = new soapval('tem:CodiceAmministrazione', 'tem:CodiceAmministrazione', $this->CodiceAmministrazione, false, false);
        }
        if (isset($dati['CodiceAOO'])) {
            $CodiceAOOSoapval = new soapval('tem:CodiceAOO', 'tem:CodiceAOO', $dati['CodiceAOO'], false, false);
        } else {
            $CodiceAOOSoapval = new soapval('tem:CodiceAOO', 'tem:CodiceAOO', $this->CodiceAOO, false, false);
        }
        if ($CodiceAmministrazioneSoapval) {
            $param .= $CodiceAmministrazioneSoapval->serialize('literal');
        }
        if ($CodiceAOOSoapval) {
            $param .= $CodiceAOOSoapval->serialize('literal');
        }
        return $this->ws_call('VerificaInvio', $param);
    }

}

?>
