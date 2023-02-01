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

class itaPaleoClient {

    private $namespace = "";
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

    function getWSSecurity($username, $password, $param=array()) {
        $timestamp = date('Y-m-d\TH:i:s') . ".123Z";
        $timestamp_expire = date('Y-m-d\TH:i:s',time()+600). ".123Z";
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
        $client = new nusoap_client($this->webservices_wsdl, true);
        $client->debugLevel = 0;
        //setting timeout
        $client->timeout = $this->timeout;
        $client->response_timeout = $this->timeout;
        //setting headers
        $client->setHeaders($this->getWSSecurity($this->username, $this->password));
        $client->soap_defencoding = 'UTF-8';
        $result = $client->call($operationName, $param);
//        file_put_contents("/tmp/request.xml", $client->request);
//        file_put_contents("/tmp/response.xml", $client->response);
        if ($client->fault) {
            $this->fault = $client->faultstring;
            //throw new Exception("Request Fault:" . $this->fault);
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
    public function ws_GetScadenzaPassword($utentePaleo) {
        $param = array(
                "userid" => $utentePaleo->getUserid(),
                "CodAmm" => $utentePaleo->getCodAmm()
        );
        return $this->ws_call('GetScadenzaPassword', $param);
    }

    public function ws_GetOperatori($OperatorePaleo) {
        $param = array(
                'operatore' => array(
                        "CodiceUO" => $OperatorePaleo->getCodiceUO(),
                        "Cognome" => $OperatorePaleo->getCognome(),
                        "Nome" => $OperatorePaleo->getNome(),
                        "Ruolo" => $OperatorePaleo->getRuolo()
                )
        );
        return $this->ws_call('GetOperatori', $param);
    }

    public function ws_GetRagioniTrasmissione($OperatorePaleo) {
        $param = array(
                'operatore' => array(
                        "CodiceUO" => $OperatorePaleo->getCodiceUO(),
                        "Cognome" => $OperatorePaleo->getCognome(),
                        "Nome" => $OperatorePaleo->getNome(),
                        "Ruolo" => $OperatorePaleo->getRuolo()
                )
        );
        return $this->ws_call('GetRagioniTrasmissione', $param);
    }

    public function ws_GetRegistri($OperatorePaleo) {
        $param = array(
                'operatore' => array(
                        "CodiceUO" => $OperatorePaleo->getCodiceUO(),
                        "Cognome" => $OperatorePaleo->getCognome(),
                        "Nome" => $OperatorePaleo->getNome(),
                        "Ruolo" => $OperatorePaleo->getRuolo()
                )
        );
        return $this->ws_call('GetRegistri', $param);
    }

    public function ws_GetTitolarioClassificazione($OperatorePaleo) {
        $param = array(
                'operatore' => array(
                        "CodiceUO" => $OperatorePaleo->getCodiceUO(),
                        "Cognome" => $OperatorePaleo->getCognome(),
                        "Nome" => $OperatorePaleo->getNome(),
                        "Ruolo" => $OperatorePaleo->getRuolo()
                )
        );
        return $this->ws_call('GetTitolarioClassificazione', $param);
    }

    public function ws_GetTipiDatiFascicoli($OperatorePaleo) {
        $param = array(
                'opp' => array(
                        "CodiceUO" => $OperatorePaleo->getCodiceUO(),
                        "Cognome" => $OperatorePaleo->getCognome(),
                        "Nome" => $OperatorePaleo->getNome(),
                        "Ruolo" => $OperatorePaleo->getRuolo()
                )
        );
        return $this->ws_call('GetTipiDatiFascicoli', $param);
    }

    public function ws_FindRubricaExt($OperatorePaleo,$reqFindRubrica) {
        $param = array(
                'operatore' => array(
                        "CodiceUO" => $OperatorePaleo->getCodiceUO(),
                        "Cognome" => $OperatorePaleo->getCognome(),
                        "Nome" => $OperatorePaleo->getNome(),
                        "Ruolo" => $OperatorePaleo->getRuolo()
                ),
                'richiesta' => array(
                        "Codice" => $reqFindRubrica->getCodice(),
                        "Descrizione" => $reqFindRubrica->getDescrizione(),
                        "IdFiscale" => $reqFindRubrica->getIdFiscale(),
                        "IstatComune" => $reqFindRubrica->getIstatComune(),
                        "Tipo" => $reqFindRubrica->getTipo()
                )
        );
        return $this->ws_call('FindRubricaExt', $param);
    }


    public function ws_SaveVoceRubrica($OperatorePaleo,$Rubrica) {
        $param = array(
                'operatore' => array(
                        "CodiceUO" => $OperatorePaleo->getCodiceUO(),
                        "Cognome" => $OperatorePaleo->getCognome(),
                        "Nome" => $OperatorePaleo->getNome(),
                        "Ruolo" => $OperatorePaleo->getRuolo()
                ),
                'voce' => array(
                        "MessaggioRisultato" => $Rubrica->getMessaggioRisultato(),
                        "Cognome" => $Rubrica->getCognome(),
                        "Email" => $Rubrica->getEmail(),
                        "IdFiscale" => $Rubrica->getIdFiscale(),
                        "IstatComune" => $Rubrica->getIstatComune(),
                        "Nome" => $Rubrica->getNome(),
                        "Tipo" => $Rubrica->getTipo(),
                        "Codice" => $Rubrica->getCodice()
                )
        );
        return $this->ws_call('SaveVoceRubrica', $param);
    }

    public function ws_ProtocollazioneEntrata($reqProtocolloArrivo) {
        $param = array(
                'richiesta' => array(
                        "Operatore" => $reqProtocolloArrivo->getOperatore(),
                        "CodiceRegistro" => $reqProtocolloArrivo->getCodiceRegistro(),
                        "Oggetto" => $reqProtocolloArrivo->getOggetto(),
                        "Privato" => $reqProtocolloArrivo->getPrivato(),
                        "DocumentoPrincipaleAcquisitoIntegralmente" => $reqProtocolloArrivo->getDPAI(),
                        "DataArrivo" => $reqProtocolloArrivo->getDataArrivo(),
                        "Mittente" => $reqProtocolloArrivo->getMittente(),
                        "Classificazioni" => $reqProtocolloArrivo->getClassificazioni(),
                        "DocumentoPrincipale" => $reqProtocolloArrivo->getDocumentoPrincipale(),
                        "DocumentiAllegati" => $reqProtocolloArrivo->getDocumentiAllegati(),
                /*,
                        "Trasmissione" => $reqProtocolloArrivo->getTrasmissione()/*,
                        "Emergenza" => $reqProtocolloArrivo->getEmergenza(),
                        "Trasmissione" => $reqProtocolloArrivo->getTrasmissione(),
                        "DocumentoPrincipale" => $reqProtocolloArrivo->getDocumentoPrincipale(),
                */
                )
        );
        return $this->ws_call('ProtocollazioneEntrata', $param);
    }

    public function ws_ProtocollazionePartenza($reqProtocolloPartenza) {
//        $Trasmissioni=array();
//        $Trasmissioni[]=$reqProtocolloArrivo->getTrasmissione();
//        $Trasmissioni[]=$reqProtocolloArrivo->getTrasmissione();
        $param = array(
                'richiesta' => array(
                        "Operatore" => $reqProtocolloPartenza->getOperatore(),
                        "CodiceRegistro" => $reqProtocolloPartenza->getCodiceRegistro(),
                        "Oggetto" => $reqProtocolloPartenza->getOggetto(),
                        "Privato" => $reqProtocolloPartenza->getPrivato(),
                        "DocumentoPrincipaleAcquisitoIntegralmente" => $reqProtocolloPartenza->getDPAI(),
                        //"DataArrivo" => $reqProtocolloPartenza->getDataArrivo(),
                        "Destinatari" => $reqProtocolloPartenza->getDestinatari(),
                        //"DestinatariCC" => $reqProtocolloArrivo->getDestinatariCC(),
                        "Classificazioni" => $reqProtocolloPartenza->getClassificazioni(),
                        "DocumentoPrincipale" => $reqProtocolloPartenza->getDocumentoPrincipale(),
                        "DocumentiAllegati" => $reqProtocolloPartenza->getDocumentiAllegati()/*,
                        "Trasmissione" => $reqProtocolloPartenza->getTrasmissione(),
                        "Emergenza" => $reqProtocolloArrivo->getEmergenza(),
                        "Trasmissione" => $reqProtocolloArrivo->getTrasmissione(),
                        */
                )
        );
        return $this->ws_call('ProtocollazionePartenza', $param);
    }

    public function ws_GetSerieArchivisticheFascicoli($OperatorePaleo) {
        $param = array(
                'opp' => array(
                        "CodiceUO" => $OperatorePaleo->getCodiceUO(),
                        "Cognome" => $OperatorePaleo->getCognome(),
                        "Nome" => $OperatorePaleo->getNome(),
                        "Ruolo" => $OperatorePaleo->getRuolo()
                )
        );
        return $this->ws_call('GetSerieArchivisticheFascicoli', $param);
    }

    public function ws_ApriRegistro($OperatorePaleo,$CodiceRegistro) {
        $param = array(
                'opp' => array(
                        "CodiceUO" => $OperatorePaleo->getCodiceUO(),
                        "Cognome" => $OperatorePaleo->getCognome(),
                        "Nome" => $OperatorePaleo->getNome(),
                        "Ruolo" => $OperatorePaleo->getRuolo()
                ),
                'CodiceRegistro' => $CodiceRegistro
        );
        return $this->ws_call('ApriRegistro', $param);
    }

    public function ws_ChiudiRegistro($OperatorePaleo,$CodiceRegistro) {
        $param = array(
                'opp' => array(
                        "CodiceUO" => $OperatorePaleo->getCodiceUO(),
                        "Cognome" => $OperatorePaleo->getCognome(),
                        "Nome" => $OperatorePaleo->getNome(),
                        "Ruolo" => $OperatorePaleo->getRuolo()
                ),
                'CodiceRegistro' => $CodiceRegistro
        );
        return $this->ws_call('ChiudiRegistro', $param);
    }

    public function ws_CercaDocumentoProtocollo($OperatorePaleo,$CercaDocumentoProtocollo) {
        $param = array(
                'richiesta' => array(
                        'DocNumber' => $CercaDocumentoProtocollo->getDocNumber(),
                        'Operatore' => array(
                                "CodiceUO" => $OperatorePaleo->getCodiceUO(),
                                "Cognome" => $OperatorePaleo->getCognome(),
                                "Nome" => $OperatorePaleo->getNome(),
                                "Ruolo" => $OperatorePaleo->getRuolo()
                        ),
                        'Segnatura' => $CercaDocumentoProtocollo->getSegnatura()
                )
        );
        return $this->ws_call('CercaDocumentoProtocollo', $param);
    }

}

?>
