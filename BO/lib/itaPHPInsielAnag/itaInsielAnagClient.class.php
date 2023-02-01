<?php

/**
 *
 * Classe per collegamento ws IRIDE
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPIride
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    10.02.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');
//require_once(ITA_LIB_PATH . '/itaPHPLinkNext/nusoap/nusoap.php');
//require_once(ITA_LIB_PATH . '/itaPHPLinkNext/itaPosizioneDebitoria.class.php');

//require_once(ITA_LIB_PATH . '/nusoap/nusoap1.2.php');

class itaInsielAnagClient {

    private $nameSpaces = array();
    private $namespace = "";
    private $namespacePrefix = "ent";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $idAccesso = "";
    private $token = "";
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

    public function setNameSpaces($nameSpaces) {
//        if ($tipo == 'foh') {
//            $nameSpaces = array("tem" => "http://tempuri.org/");
//        }
//        if ($tipo == 'sch') {
//            $nameSpaces = array("sch" => "http://wwwpa2k/Ulisse/iride/web_services/ws_tabelle/schema");
//        }
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

    public function getNamespace() {
        return $this->namespace;
    }

    public function getWebservices_uri() {
        return $this->webservices_uri;
    }

    public function getWebservices_wsdl() {
        return $this->webservices_wsdl;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getTimeout() {
        return $this->timeout;
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

    public function getNamespacePrefix() {
        return $this->namespacePrefix;
    }

    public function setNamespacePrefix($namespacePrefix) {
        $this->namespacePrefix = $namespacePrefix;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }

    private function getHeaders() {
        $header = "";
        return $header;
    }

    private function ws_call($operationName, $param, $soapAction) {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
        $client->debugLevel = 0;
        $client->timeout = $this->timeout > 0 ? $this->timeout : 120;
        $client->response_timeout = $this->timeout > 0 ? $this->timeout : 120;
//        $client->setHeaders($this->getHeaders());
        $client->soap_defencoding = 'UTF-8';
//        $soapAction = $this->namespace . "/" . $operationName;
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';
        $result = $client->call($operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
//        file_put_contents('/users/tmp/LNparam_' . $soapAction . '.xml', $param);
//        file_put_contents('/users/tmp/LNrequest_' . $soapAction . '.xml', $client->request);
//        file_put_contents('/users/tmp/LNresponse_' . $soapAction . '.xml', $client->response);
        $soapAction = pathinfo($soapAction, PATHINFO_BASENAME);
        file_put_contents('C:/tmp/LNparam_' . $soapAction . date('YmdHis') . '.xml', $param);
        file_put_contents('C:/tmp/LNrequest_' . $soapAction . date('YmdHis') . '.xml', $client->request);
        file_put_contents('C:/tmp/LNresponse_' . $soapAction . date('YmdHis') . '.xml', $client->response);
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

    /**
     * WS DatiPersonaEndPoint
     * 
     * @param type $param
     * @return type
     */
    public function ws_richiestaDatiPersona($param) {
        $NindSoapval = new soapval('Nind', 'Nind', $param['Nind'], false, false);
        $paramL = $NindSoapval->serialize('literal');
//        $paramL = "<Nind>" . $param['Nind'] . "</Nind>";
//        return $this->ws_call("dat:Richiesta", $paramL, "richiestaDatiPersona");
        $soapAction = "http://www.insiel.it/servizi/bevisureentiesterni/ws/datipersona"; //da parametrizzare dal file .ini??
        return $this->ws_call("dat:Richiesta", $paramL, $soapAction);
    }

    /**
     * WS FamiliariEndPoint
     * 
     * @param type $param
     * @return type
     */
    public function ws_richiestaFamiliari($param) {
        $NumeFamiSoapval = new soapval('NumeFami', 'NumeFami', $param['NumeFami'], false, false);
        $UsernameSoapval = new soapval('Username', 'Username', $param['Username'], false, false);
        $TipoFamiliariSoapval = new soapval('TipoFamiliari', 'TipoFamiliari', $param['TipoFamiliari'], false, false);
        $paramL = $NumeFamiSoapval->serialize('literal') . $UsernameSoapval->serialize('literal') . $TipoFamiliariSoapval->serialize('literal');
//        return $this->ws_call("fam:Richiesta", $paramL, "richiestaFamiliari");
        $soapAction = "http://www.insiel.it/servizi/bevisureentiesterni/ws/familiari";
        return $this->ws_call("fam:Richiesta", $paramL, $soapAction);
    }

    /**
     * WS PersonaEndPoint
     * 
     * @param type $param
     * @return type
     */
    public function ws_richiestaPersona($param) {
        /*
          <AnnoNascita>?</AnnoNascita>

         */

        /*
         * TipoRicerca può assumere i seguenti valori
         *          Maggiore
         *          Minore
         *          Uguale
         *          Percento
         */
        $TipoRicercaSoapval = new soapval('TipoRicerca', 'TipoRicerca', $param['TipoRicerca'], false, false);
        $paramL = $TipoRicercaSoapval->serialize('literal');
        if (isset($param['Cognome'])) {
            $CognomeSoapval = new soapval('Cognome', 'Cognome', $param['Cognome'], false, false);
            $paramL .= $CognomeSoapval->serialize('literal');
        }
        if (isset($param['Nome'])) {
            $NomeSoapval = new soapval('Nome', 'Nome', $param['Nome'], false, false);
            $paramL .= $NomeSoapval->serialize('literal');
        }
        if (isset($param['Sesso'])) {
            $SessoSoapval = new soapval('Sesso', 'Sesso', $param['Sesso'], false, false);
            $paramL .= $SessoSoapval->serialize('literal');
        }
        if (isset($param['StatoCivile'])) {
            $StatoCivileSoapval = new soapval('StatoCivile', 'StatoCivile', $param['StatoCivile'], false, false);
            $paramL .= $StatoCivileSoapval->serialize('literal');
        }
        if (isset($param['RelazioneParentela'])) {
            $RelazioneParentelaSoapval = new soapval('RelazioneParentela', 'RelazioneParentela', $param['RelazioneParentela'], false, false);
            $paramL .= $RelazioneParentelaSoapval->serialize('literal');
        }
        if (isset($param['RelazioneParentela'])) {
            $RelazioneParentelaSoapval = new soapval('RelazioneParentela', 'RelazioneParentela', $param['RelazioneParentela'], false, false);
            $paramL .= $RelazioneParentelaSoapval->serialize('literal');
        }
        if (isset($param['CodiceFiscale'])) {
            $CodiceFiscaleSoapval = new soapval('CodiceFiscale', 'CodiceFiscale', $param['CodiceFiscale'], false, false);
            $paramL .= $CodiceFiscaleSoapval->serialize('literal');
        }
        if (isset($param['Frazione'])) {
            $FrazioneSoapval = new soapval('Frazione', 'Frazione', $param['Frazione'], false, false);
            $paramL .= $FrazioneSoapval->serialize('literal');
        }
        if (isset($param['Via'])) {
            $ViaSoapval = new soapval('Via', 'Via', $param['Via'], false, false);
            $paramL .= $ViaSoapval->serialize('literal');
        }
        if (isset($param['NumeroCivico'])) {
            $NumeroCivicoSoapval = new soapval('NumeroCivico', 'NumeroCivico', $param['NumeroCivico'], false, false);
            $paramL .= $NumeroCivicoSoapval->serialize('literal');
        }
        if (isset($param['PosiAnag'])) {
            $PosiAnagSoapval = new soapval('PosiAnag', 'PosiAnag', $param['PosiAnag'], false, false);
            $paramL .= $PosiAnagSoapval->serialize('literal');
        }
        if (isset($param['Nind'])) {
            $NindSoapval = new soapval('Nind', 'Nind', $param['Nind'], false, false);
            $paramL .= $NindSoapval->serialize('literal');
        }
        if (isset($param['Nfam'])) {
            $NfamSoapval = new soapval('Nfam', 'Nfam', $param['Nfam'], false, false);
            $paramL .= $NfamSoapval->serialize('literal');
        }
        if (isset($param['Afam'])) {
            $AfamSoapval = new soapval('Afam', 'Afam', $param['Afam'], false, false);
            $paramL .= $AfamSoapval->serialize('literal');
        }
        if (isset($param['Tfam'])) {
            $TfamSoapval = new soapval('Tfam', 'Tfam', $param['Tfam'], false, false);
            $paramL .= $TfamSoapval->serialize('literal');
        }
        if (isset($param['Username'])) {
            $UsernameSoapval = new soapval('Username', 'Username', $param['Username'], false, false);
            $paramL .= $UsernameSoapval->serialize('literal');
        }
        if (isset($param['Profilo'])) {
            $ProfiloSoapval = new soapval('Profilo', 'Profilo', $param['Profilo'], false, false);
            $paramL .= $ProfiloSoapval->serialize('literal');
        }
        if (isset($param['GiornoNascita'])) {
            $GiornoNascitaSoapval = new soapval('GiornoNascita', 'GiornoNascita', $param['GiornoNascita'], false, false);
            $paramL .= $GiornoNascitaSoapval->serialize('literal');
        }
        if (isset($param['MeseNascita'])) {
            $MeseNascitaSoapval = new soapval('MeseNascita', 'MeseNascita', $param['MeseNascita'], false, false);
            $paramL .= $MeseNascitaSoapval->serialize('literal');
        }
        if (isset($param['AnnoNascita'])) {
            $AnnoNascitaSoapval = new soapval('AnnoNascita', 'AnnoNascita', $param['AnnoNascita'], false, false);
            $paramL .= $AnnoNascitaSoapval->serialize('literal');
        }
//        return $this->ws_call("fam:Richiesta", $paramL, "richiestaFamiliari");
        $soapAction = "http://www.insiel.it/servizi/bevisureentiesterni/ws/persona";
        return $this->ws_call("per:Richiesta", $paramL, $soapAction);
    }

}

?>
