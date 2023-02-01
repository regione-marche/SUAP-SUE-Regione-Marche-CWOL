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

class itaNPCEClient {

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
    private $com;
    private $com1;

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

    public function setCom($com) {
        $this->com = $com;
    }

    public function getCom() {
        return $this->com;
    }

    public function setCom1($com1) {
        $this->com1 = $com1;
    }

    public function getCom1() {
        return $this->com1;
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

    private function ws_call($operationName, $param = array()) {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
        $client->debugLevel = 0;
        $client->timeout = $this->timeout;
        $client->response_timeout = $this->timeout;
        $client->setCredentials($this->username, $this->password, 'basic');
        $client->soap_defencoding = 'UTF-8';
        $soapAction = $this->mainNamespace . "/" . $operationName;
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';
        $result = $client->call("com:" . $operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
        file_put_contents("/tmp/request_xol.xml", $client->request);
        file_put_contents("/tmp/response_xol.xml", $client->response);
        //$result = $client->call($operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
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
                throw new Exception("Client SOAP Error: " . $err);
                return false;
            }
        }
        $this->result = $result;
        return true;
    }

    private function ws_send($request_xml, $soapAction) {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
        $client->debugLevel = 0;
        $client->timeout = $this->timeout;
        $client->response_timeout = $this->timeout;
        $client->setCredentials('H2HSTG93', 'Cestg093', 'basic');
        $client->soap_defencoding = 'UTF-8';
        $result = $client->send($request_xml, $soapAction, '');
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

    /**
     *
     * @param type $Userid
     * @param type $CodAmm
     */
    public function ws_RecuperaIdRichiesta() {
        return $this->ws_call('RecuperaIdRichiesta');
    }

    /**
     * 
     * @param type $idRichiesta String id Richiesta
     * @param type $Destinatari array di oggetti itaDestinatario
     * @return boolean
     */
    public function ws_ValidaDestinatari($idRichiesta, $Destinatari) {
        if (is_array($Destinatari)) {
            foreach ($Destinatari as $Destinatario) {
                $DestinatariSoapvalArr[] = $Destinatario->getSoapValRequest();
            }
            $DestinatariSoapval = new soapval('com:Destinatari', 'com:Destinatari', $DestinatariSoapvalArr, false, false);
            $idRichiestaSoapval = new soapval('com:IDRichiesta', 'com:IDRichiesta', $idRichiesta, false, false);
            $param = $idRichiestaSoapval->serialize('literal') . $DestinatariSoapval->serialize('literal');
            App::log('$param');
            App::log($param);
            return $this->ws_call("ValidaDestinatari", $param);
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $idRichiesta String id Richiesta
     * @param type $Cliente String, opzionale
     * @param type $ROLSubmit oggetto della classe itaROLSubmit
     * @return boolean
     */
    public function ws_Invio($idRichiesta, $Cliente, $ROLSubmit) {
        $idRichiestaSoapval = new soapval('com:IDRichiesta', 'com:IDRichiesta', $idRichiesta, false, false);
        $ClienteSoapval = new soapval('com:Cliente', 'com:Cliente', $Cliente, false, false);
        $ROLSubmitSoapval = $ROLSubmit->getSoapValRequest();
        $param = $idRichiestaSoapval->serialize('literal') . $ClienteSoapval->serialize() . $ROLSubmitSoapval->serialize('literal');
        return $this->ws_call("Invio", $param);
    }

    /**
     * 
     * @param type $Richieste array([0] => array('IDRichiesta','GuidUtente'),[1]...)
     * @return type
     */
    public function ws_Valorizza($Richieste) {
        $RichiesteSoapval = array();
        foreach ($Richieste as $Richiesta) {
            $IDRichiestaSoapval = new soapval('com1:IDRichiesta', 'com1:IDRichiesta', $Richiesta['IDRichiesta'], false, false);
            $GuidUtenteSoapval = new soapval('com1:GuidUtente', 'com1:GuidUtente', $Richiesta['GuidUtente'], false, false);
            $RichiesteSoapval[] = new soapval('com:Richieste', 'com:Richieste', array($IDRichiestaSoapval, $GuidUtenteSoapval), false, false);
        }
        $param = "";
        foreach ($RichiesteSoapval as $RichiestaSoapval) {
            $param .= $RichiestaSoapval->serialize('literal');
        }
        return $this->ws_call("Valorizza", $param);
    }

    /**
     * 
     * @param type $idRichiesta
     * @param type $guidUtente
     * @param type $retDoc boolean per scegliere se scaricare anche il documento
     * @return type
     */
    public function ws_ValorizzaSingle($idRichiesta, $guidUtente, $retDoc = false) {
        $IDRichiestaSoapval = new soapval('com1:IDRichiesta', 'com1:IDRichiesta', $idRichiesta, false, false);
        $GuidUtenteSoapval = new soapval('com1:GuidUtente', 'com1:GuidUtente', $guidUtente, false, false);
        $RichiestaSoapval = new soapval('com:Richiesta', 'com:Richiesta', array($IDRichiestaSoapval, $GuidUtenteSoapval), false, false);
        $RestituisciDocumentoSoapval = new soapval('com:RestituisciDocumento', 'com:RestituisciDocumento', $retDoc, false, false);
        $ServizioEnquirySoapval = new soapval('com:ServizioEnquiry', 'com:ServizioEnquiry', array($RichiestaSoapval, $RestituisciDocumentoSoapval), false, false);
        $param = $ServizioEnquirySoapval->serialize('literal');
        return $this->ws_call("ValorizzaSingle", $param);
    }

    /**
     * 
     * @param type $IdOrdine            String
     * @param type $OpzionePagamento    array('PostFatturazione', 'DescrizioneTipoPagamento', 'IdTipoPagamento')
     * @param type $DatiTransazione     array('Attributi' => array('IdTransazione', 'CodiceAutorizzazione', 'DataAutorizzazione'))
     * @return type
     */
    public function ws_Conferma($IdOrdine, $OpzionePagamento = array(), $DatiTransazione = array()) {
        $IdOrdineSoapval = new soapval('com:IdOrdine', 'com:IdOrdine', $IdOrdine, false, false);
        $OpzioneArr = array();
        if ($OpzionePagamento) {
            //obbligatorio
            $OpzioneArr[] = new soapval('com1:PostFatturazione', 'com1:PostFatturazione', $OpzionePagamento['PostFatturazione'], false, false);
        }
        if ($OpzionePagamento['DescrizioneTipoPagamento']) {
            $OpzioneArr[] = new soapval('com1:DescrizioneTipoPagamento', 'com1:DescrizioneTipoPagamento', $OpzionePagamento['DescrizioneTipoPagamento'], false, false);
        }
        if ($OpzionePagamento['IdTipoPagamento']) {
            $OpzioneArr[] = new soapval('com1:IdTipoPagamento', 'com1:IdTipoPagamento', $OpzionePagamento['IdTipoPagamento'], false, false);
        }
        $OpzionePagamentoSoapval = new soapval('com:OpzionePagamento', 'com:OpzionePagamento', $OpzioneArr, false, false);
        $DatiTransazioneSoapval = new soapval('com:DatiTransazione', 'com:DatiTransazione', "", false, false, $DatiTransazione['Attributi']);
        $param = $IdOrdineSoapval->serialize() . $OpzionePagamentoSoapval->serialize('literal') . $DatiTransazioneSoapval->serialize('literal');
        return $this->ws_call("Conferma", $param);
    }

    /**
     * 
     * @param type $idRichiesta     String
     * @return type
     */
    public function ws_RecuperaStatoIdRichiesta($idRichiesta) {
        $idRichiestaSoapval = new soapval('com:IdRichiesta', 'com:IdRichiesta', $idRichiesta, false, false);
        $param = $idRichiestaSoapval->serialize('literal');
        return $this->ws_call("RecuperaStatoIdRichiesta", $param);
    }

    /**
     * 
     * @param type $Richieste   array([0] => array('IDRichiesta', 'GuidUtente'),[1]...)
     * @return type
     */
    public function ws_Annulla($Richieste) {
        $param = "";
        foreach ($Richieste as $Richiesta) {
            $IDRichiestaSoapval = new soapval('com1:IDRichiesta', 'com1:IDRichiesta', $Richiesta['IDRichiesta'], false, false);
            $GuidUtenteSoapval = new soapval('com1:GuidUtente', 'com1:GuidUtente', $Richiesta['GuidUtente'], false, false);
            $RichiesteSoapval = new soapval('com:Richieste', 'com:Richieste', array($IDRichiestaSoapval, $GuidUtenteSoapval), false, false);
            $param .= $RichiesteSoapval->serialize('literal');
        }
        return $this->ws_call("Annulla", $param);
    }

    /**
     * 
     * @param type $idRichiesta
     * @param type $guidUtente
     * @return type
     */
    public function ws_RecuperaDCS($idRichiesta, $guidUtente = "") {
        $IDRichiestaSoapval = new soapval('com1:IDRichiesta', 'com1:IDRichiesta', $idRichiesta, false, false);
        $ArrayParam[] = $IDRichiestaSoapval;
        if ($guidUtente != "") {
            $GuidUtenteSoapval = new soapval('com1:GuidUtente', 'com1:GuidUtente', $guidUtente, false, false);
            $ArrayParam[] = $GuidUtenteSoapval;
        }
        $RichiestaSoapval = new soapval('com:Richiesta', 'com:Richiesta', $ArrayParam, false, false);
        $param = $RichiestaSoapval->serialize('literal');
        return $this->ws_call("RecuperaDCS", $param);
    }

    /**
     * 
     * @param type $idRichiesta
     * @param type $guidUtente
     * @return type
     */
    public function ws_RecuperaDestinatari($idRichiesta, $guidUtente) {
        $IDRichiestaSoapval = new soapval('com1:IDRichiesta', 'com1:IDRichiesta', $idRichiesta, false, false);
        $GuidUtenteSoapval = new soapval('com1:GuidUtente', 'com1:GuidUtente', $guidUtente, false, false);
        $RichiestaSoapval = new soapval('com:Richiesta', 'com:Richiesta', array($IDRichiestaSoapval, $GuidUtenteSoapval), false, false);
        $param = $RichiestaSoapval->serialize('literal');
        return $this->ws_call("RecuperaDestinatari", $param);
    }

    /**
     * 
     * @param type $Richieste       array([0] => array('IDRichiesta', 'GuidUtente'), [1]...)
     * @param type $autoConferma
     * @return type
     */
    public function ws_PreConferma($Richieste, $autoConferma = false) {
        $param = "";
        $RichiestaArr = array();
        foreach ($Richieste as $Richiesta) {
            $RichiestaArr[] = new soapval('com1:IDRichiesta', 'com1:IDRichiesta', $Richiesta['IDRichiesta'], false, false);
            if ($Richiesta['GuidUtente']) {
                $RichiestaArr[] = new soapval('com1:GuidUtente', 'com1:GuidUtente', $Richiesta['GuidUtente'], false, false);
            }
            $RichiesteSoapval = new soapval('com:Richieste', 'com:Richieste', $RichiestaArr, false, false);
            $param .= $RichiesteSoapval->serialize('literal');
        }
        $autoConfermaSoapval = new soapval('com:autoConferma', 'com:autoConferma', $autoConferma, false, false);
        $param .= $autoConfermaSoapval->serialize('literal');
        return $this->ws_call("PreConferma", $param);
    }

    /**
     * 
     * @param type $Richieste array([0] => array('IDRichiesta', 'GuidUtente'), [1]...)
     * @return type
     */
    public function ws_StatoInviiPerID($Richieste) {
        $param = "";
        $RichiestaArr = array();
        foreach ($Richieste as $Richiesta) {
            $RichiestaArr[] = new soapval('com1:IDRichiesta', 'com1:IDRichiesta', $Richiesta['IDRichiesta'], false, false);
            if ($Richiesta['GuidUtente']) {
                $RichiestaArr[] = new soapval('com1:GuidUtente', 'com1:GuidUtente', $Richiesta['GuidUtente'], false, false);
            }
            $RichiesteSoapval = new soapval('com:Richieste', 'com:Richieste', $RichiestaArr, false, false);
            $param .= $RichiesteSoapval->serialize('literal');
        }
        return $this->ws_call("StatoInviiPerID", $param);
    }

}

?>
