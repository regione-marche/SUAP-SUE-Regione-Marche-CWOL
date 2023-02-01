<?php

/**
 *
 * Classe per collegamento ws Paleo
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaPHPHWS
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    07.06.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
//require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');
//require_once(ITA_LIB_PATH . '/nusoap/nusoap1.2.php');
require_once(ITA_LIB_PATH . '/itaPHPHWS/lib/utility.php');
require_once(ITA_LIB_PATH . '/itaPHPHWS/lib/dump.php');
require_once(ITA_LIB_PATH . '/itaPHPHWS/lib/class.csoapclient.php');

class itaHWSClient {

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

    function getWSSecurity($username, $password, $param = array()) {
        $timestamp = date('Y-m-d\TH:i:s') . ".123Z";
        $timestamp_expire = date('Y-m-d\TH:i:s', time() + 600) . ".123Z";
        $nonce = mt_rand();
//        $wsse = '
//                <wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
//                    <wsse:UsernameToken>
//                        <wsse:Username>' . $username . '</wsse:Username>
//                        <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' . $password . '</wsse:Password>
//                        <wsse:Nonce>' . base64_encode(pack('H*', $nonce)) . '</wsse:Nonce>
//                        <wsu:Created>' . $timestamp . '</wsu:Created>
//                    </wsse:UsernameToken>
//                </wsse:Security>';

        $wsse = '
            <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" soap:mustUnderstand="1">
                <wsse:UsernameToken xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                    <wsse:Username>' . $username . '</wsse:Username>
                    <wsse:Password>' . $password . '</wsse:Password>
                    <wsse:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">' . base64_encode(pack('H*', $nonce)) . '</wsse:Nonce>
                    <wsu:Created xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">' . $timestamp . '</wsu:Created>
                </wsse:UsernameToken>
            </wsse:Security>';
//                <wsse:UsernameToken xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" wsu:Id="UsernameToken-15">
//<wsse:Password xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' . $password . '</wsse:Password>
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
        App::log($operationName);
        App::log($param);
        $this->clearResult();
        //$client = new nusoap_client($this->webservices_wsdl, true);
        $parametri = array(
            "trace" => true,
            "exception" => true,
            //'encoding' => 'UTF-8',
            'encoding' => 'ISO-8859-1',
            //'soap_version'=>SOAP_1_1,
            'soap_version' => SOAP_1_2,
            //'wsdl_local_copy'=>true,
            //'login'          => 'protocolloU',
            'login' => $this->username,
            //'password'       => 'protocolloP'
            'password' => $this->password
        );

        
        $client = new CSoapClient($this->webservices_wsdl, $parametri);

        //setting timeout
        //$client->timeout = 500; //parametro epr nusoap
        //$client->response_timeout = 500;
        //setting headers
        //$client->setHeaders($this->getWSSecurity($this->username, $this->password));
        //$client->setCredentials($this->username, $this->password);
        //$client->soap_defencoding = 'UTF-8';
        //$client->soap_defencoding = 'US-ASCII';
        $client->soap_defencoding = 'ISO-8859-1';

        //$client->xml_encoding = "US-ASCII";
        $client->decode_utf8 = false;
        $client->debugLevel = 0;

        //$result = $client->call($operationName, $param);
        try {
            $result = $client->__soapCall($operationName, $param);
            $this->result = $result;
        } catch (Exception $e) {
            //echo 'Trovata eccezione: ',  $e->getMessage(), "\n";
            //Out::msgStop("ECCEZIONE!", $e->getMessage());
//            app::log('request');
//            app::log($client->__getLastRequest());
            $this->error = $e->getMessage();
            app::log('result EXCEPTION');
            app::log($this->error);
            app::log($client->__getLastRequest());
            app::log($client->__getLastResponse());
        }

        //is_soap_fault serve se viene settato 'trace' = false
        if (is_soap_fault($result)) {
            App::log('fault');
            $this->fault = trigger_error("SOAP Fault: (faultcode: {$result->faultcode}, faultstring: {$result->faultstring})", E_USER_ERROR);
        }
//        if ($client->fault) {
//            app::log('fault call');
//            $this->fault = $client->faultstring;
//            //throw new Exception("Request Fault:" . $this->fault);
//            return false;
//        } else {
//            $err = $client->getError();
//            if ($err) {
//                app::log('error call');
//                $this->error = $err;
//                throw new Exception("Client SOAP Error: " . $err);
//                return false;
//            }
//        }
        //Out::msgInfo("Risultato",var_dump($result));
        return true;
    }

    public function ws_CercaRubrica($itaCercaRubrica) {
        $param = array(
            'CercaRubrica' => array(
                'dbcpName' => $itaCercaRubrica->getJDBC(),
                'reqParametri' => array(
                    "descrizione" => $itaCercaRubrica->getDescrizione(),
                    "idfiscale" => $itaCercaRubrica->getIdFiscale()
                )
            )
        );
        return $this->ws_call('CercaRubrica', $param);
    }

    public function ws_SalvaVoceRubrica($Rubrica) {
        $param = array(
            'SalvaVoceRubrica' => array(
                'dbcpName' => $Rubrica->getJDBC(),
                'entry' => array(
                    "cap" => $Rubrica->getCap(),
                    "citta" => $Rubrica->getCitta(),
                    "codice" => $Rubrica->getCodice(),
                    "codiceFiscale" => $Rubrica->getCodiceFiscale(),
                    "cognome" => $Rubrica->getCognome(),
                    "dataNascita" => $Rubrica->getDataNascita(),
                    "email" => $Rubrica->getEmail(),
                    "fax" => $Rubrica->getFax(),
                    "indirizzo" => $Rubrica->getIndirizzo(),
                    "nome" => $Rubrica->getNome(),
                    "partitaIva" => $Rubrica->getPartitaIva(),
                    "prov" => $Rubrica->getProv(),
                    "ragioneSociale" => $Rubrica->getRagioneSociale(),
                    "telefono" => $Rubrica->getTelefono(),
                )
            )
        );
        return $this->ws_call('SalvaVoceRubrica', $param);
    }

    public function ws_ProtocollazioneIngresso($ProtocolloIngresso) {
        $datiProtocolloIngresso = array();
        $datiProtocolloIngresso['accesso'] = $ProtocolloIngresso->getAccesso();  //obbligatorio
        $datiProtocolloIngresso['anno'] = $ProtocolloIngresso->getAnno();  //obbligatorio
        $datiProtocolloIngresso['aoo'] = $ProtocolloIngresso->getAoo();  //obbligatorio
        $datiProtocolloIngresso['classificazione'] = $ProtocolloIngresso->getClassificazione();  //obbligatorio
        $datiProtocolloIngresso['codice'] = $ProtocolloIngresso->getCodice();  //obbligatorio
        $datiProtocolloIngresso['codiceOperatore'] = $ProtocolloIngresso->getCodiceOperatore();  //obbligatorio
        $datiProtocolloIngresso['codiceSpedizione'] = $ProtocolloIngresso->getCodiceSpedizione();  //obbligatorio
        $datiProtocolloIngresso['comunicazioneInterna'] = $ProtocolloIngresso->getComunicazioneInterna();  //obbligatorio
        $datiProtocolloIngresso['corrispondente'] = $ProtocolloIngresso->getCorrispondente();  //obbligatorio
        if ($ProtocolloIngresso->getDataDocumento()) {
            $datiProtocolloIngresso['dataDocumento'] = $ProtocolloIngresso->getDataDocumento();  //opzionale
        }
        if ($ProtocolloIngresso->getDataRegistrazione()) {
            $datiProtocolloIngresso['dataRegistrazione'] = $ProtocolloIngresso->getDataRegistrazione();  //opzionale
        }
        if ($ProtocolloIngresso->getDataScadenza()) {
            $datiProtocolloIngresso['dataScadenza'] = $ProtocolloIngresso->getDataScadenza();  //opzionale
        }
        $fascicoli = $ProtocolloIngresso->getFascicoli();
        if ($fascicoli['idFascicolo'] || $fascicoli['nuovoFascicolo']) {
            $datiProtocolloIngresso['fascicoli'] = $ProtocolloIngresso->getFascicoli();  //opzionale
        }
        $datiProtocolloIngresso['flagCartaceo'] = $ProtocolloIngresso->getFlagCartaceo();  //obbligatorio
        $datiProtocolloIngresso['flagInArchivio'] = $ProtocolloIngresso->getFlagInArchivio();  //obbligatorio
        if ($ProtocolloIngresso->getNote() != "") {
            $datiProtocolloIngresso['note'] = $ProtocolloIngresso->getNote();  //opzionale
        }
        $datiProtocolloIngresso['numero'] = $ProtocolloIngresso->getNumero();  //obbligatorio
        if ($ProtocolloIngresso->getOggetto() != "") {
            $datiProtocolloIngresso['oggetto'] = $ProtocolloIngresso->getOggetto();  //opzionale
        }
        $datiProtocolloIngresso['protocolloCollegato'] = $ProtocolloIngresso->getProtocolloCollegato();  //obbligatorio
        $datiProtocolloIngresso['protocolloEmergenza'] = $ProtocolloIngresso->getProtocolloEmergenza();  //obbligatorio
        $datiProtocolloIngresso['protocolloRiscontro'] = $ProtocolloIngresso->getProtocolloRiscontro();  //obbligatorio
        if ($ProtocolloIngresso->getSegnatura() != "") {
            $datiProtocolloIngresso['segnatura'] = $ProtocolloIngresso->getSegnatura();  //opzionale
        }
        $datiProtocolloIngresso['statoPratica'] = $ProtocolloIngresso->getStatoPratica();  //obbligatorio
        $datiProtocolloIngresso['statoProtocollo'] = $ProtocolloIngresso->getStatoProtocollo();  //obbligatorio
        $datiProtocolloIngresso['tipo'] = $ProtocolloIngresso->getTipo();  //obbligatorio
        $datiProtocolloIngresso['ufficio'] = $ProtocolloIngresso->getUfficio();  //obbligatorio
        $datiProtocolloIngresso['casellaMittente'] = $ProtocolloIngresso->getCasellaMittente();  //obbligatorio
        if ($ProtocolloIngresso->getDataArrivo()) {
            $datiProtocolloIngresso['dataArrivo'] = $ProtocolloIngresso->getDataArrivo();  //opzionale
        }
        if ($ProtocolloIngresso->getDataProtocolloMittente()) {
            $datiProtocolloIngresso['dataProtocolloMittente'] = $ProtocolloIngresso->getDataProtocolloMittente();  //opzionale
        }
        if ($ProtocolloIngresso->getNumeroProtocolloMittente()) {
            $datiProtocolloIngresso['numeroProtocolloMittente'] = $ProtocolloIngresso->getNumeroProtocolloMittente();  //opzionale
        }
        if ($ProtocolloIngresso->getDocumentoPrincipale()) {
            $datiProtocolloIngresso['documentoPrincipale'] = $ProtocolloIngresso->getDocumentoPrincipale();  //opzionale
        }
        if ($ProtocolloIngresso->getAllegati()) {
            $datiProtocolloIngresso['allegati'] = $ProtocolloIngresso->getAllegati();  //opzionale
        }

        $param = array(
            'ProtocollazioneIngresso' => array(
                'dbcpName' => $ProtocolloIngresso->getJDBC(),
                'reqProtocollo' => $datiProtocolloIngresso
            )
        );
        return $this->ws_call('ProtocollazioneIngresso', $param);
    }

    public function ws_ProtocollazioneUscita($ProtocolloUscita) {
        $datiProtocolloUscita = array();
        $datiProtocolloUscita['accesso'] = $ProtocolloUscita->getAccesso();  //obbligatorio
        $datiProtocolloUscita['anno'] = $ProtocolloUscita->getAnno();  //obbligatorio
        $datiProtocolloUscita['aoo'] = $ProtocolloUscita->getAoo();  //obbligatorio
        $datiProtocolloUscita['classificazione'] = $ProtocolloUscita->getClassificazione();  //obbligatorio
        $datiProtocolloUscita['codice'] = $ProtocolloUscita->getCodice();  //obbligatorio
        $datiProtocolloUscita['codiceOperatore'] = $ProtocolloUscita->getCodiceOperatore();  //obbligatorio
        $datiProtocolloUscita['codiceSpedizione'] = $ProtocolloUscita->getCodiceSpedizione();  //obbligatorio
        $datiProtocolloUscita['comunicazioneInterna'] = $ProtocolloUscita->getComunicazioneInterna();  //obbligatorio
        $datiProtocolloUscita['corrispondente'] = $ProtocolloUscita->getCorrispondente();  //obbligatorio
        if ($ProtocolloUscita->getDataDocumento()) {
            $datiProtocolloUscita['dataDocumento'] = $ProtocolloUscita->getDataDocumento();  //opzionale
        }
        if ($ProtocolloUscita->getDataRegistrazione()) {
            $datiProtocolloUscita['dataRegistrazione'] = $ProtocolloUscita->getDataRegistrazione();  //opzionale
        }
        if ($ProtocolloUscita->getDataScadenza()) {
            $datiProtocolloUscita['dataScadenza'] = $ProtocolloUscita->getDataScadenza();  //opzionale
        }
//        $fascicoli=$ProtocolloUscita->getFascicoli();
//        if ($fascicoli['idFascicolo'] || $fascicoli['nuovoFascicolo']) {
//            app::log('qua');
//            $datiProtocolloUscita['fascicoli']=$ProtocolloUscita->getFascicoli();  //opzionale
//        }
        $fascicoli = $ProtocolloUscita->getFascicoli();
        if ($fascicoli['idFascicolo'] || $fascicoli['nuovoFascicolo']) {
            $datiProtocolloUscita['fascicoli'] = $ProtocolloUscita->getFascicoli();  //opzionale
        }

        $datiProtocolloUscita['flagCartaceo'] = $ProtocolloUscita->getFlagCartaceo();  //obbligatorio
        $datiProtocolloUscita['flagInArchivio'] = $ProtocolloUscita->getFlagInArchivio();  //obbligatorio
        if ($ProtocolloUscita->getNote() != "") {
            $datiProtocolloUscita['note'] = $ProtocolloUscita->getNote();  //opzionale
        }
        $datiProtocolloUscita['numero'] = $ProtocolloUscita->getNumero();  //obbligatorio
        if ($ProtocolloUscita->getOggetto() != "") {
            $datiProtocolloUscita['oggetto'] = $ProtocolloUscita->getOggetto();  //opzionale
        }
        $datiProtocolloUscita['protocolloCollegato'] = $ProtocolloUscita->getProtocolloCollegato();  //obbligatorio
        $datiProtocolloUscita['protocolloEmergenza'] = $ProtocolloUscita->getProtocolloEmergenza();  //obbligatorio
        $datiProtocolloUscita['protocolloRiscontro'] = $ProtocolloUscita->getProtocolloRiscontro();  //obbligatorio
        if ($ProtocolloUscita->getSegnatura() != "") {
            $datiProtocolloUscita['segnatura'] = $ProtocolloUscita->getSegnatura();  //opzionale
        }
        $datiProtocolloUscita['statoPratica'] = $ProtocolloUscita->getStatoPratica();  //obbligatorio
        $datiProtocolloUscita['statoProtocollo'] = $ProtocolloUscita->getStatoProtocollo();  //obbligatorio
        $datiProtocolloUscita['tipo'] = $ProtocolloUscita->getTipo();  //obbligatorio
        $datiProtocolloUscita['ufficio'] = $ProtocolloUscita->getUfficio();  //obbligatorio
        if ($ProtocolloUscita->getDocumentoPrincipale()) {
            $datiProtocolloUscita['documentoPrincipale'] = $ProtocolloUscita->getDocumentoPrincipale();  //opzionale
        }
        if ($ProtocolloUscita->getAllegati()) {
            $datiProtocolloUscita['allegati'] = $ProtocolloUscita->getAllegati();  //opzionale
        }
        $param = array(
            'ProtocollazioneUscita' => array(
                'dbcpName' => $ProtocolloUscita->getJDBC(),
                'reqProtocollo' => $datiProtocolloUscita
            )
        );
        return $this->ws_call('ProtocollazioneUscita', $param);
    }

    public function ws_CercaDocumentoProtocollo($CercaDocumentoProtocollo) {
        $param = array(
            'CercaDocumentoProtocollo' => array(
                'dbcpName' => $CercaDocumentoProtocollo->getJDBC(),
                'richiesta' => array(
                    'annoCompetenza' => $CercaDocumentoProtocollo->getAnnoCompetenza(),
                    'aoo' => $CercaDocumentoProtocollo->getAoo(),
                    'numeroDocumento' => $CercaDocumentoProtocollo->getNumeroDocumento(),
                    'segnatura' => $CercaDocumentoProtocollo->getSegnatura(),
                    'tipoProtocollo' => $CercaDocumentoProtocollo->getTipoProtocollo()
                )
            )
        );
        App::log('parametri');
        App::log($param);
        return $this->ws_call('CercaDocumentoProtocollo', $param);
    }

    public function ws_CercaTitolario($CercaTitolario) {
        $param = array(
            'CercaTitolario' => array(
                'dbcpName' => $CercaTitolario->getJDBC(),
                'reqParametri' => array(
                    'categoria' => $CercaTitolario->getCategoria(),
                    'classe' => $CercaTitolario->getClasse(),
                    'fascicolo' => $CercaTitolario->getFascicolo(),
                    'sottofascicolo' => $CercaTitolario->getSottofascicolo(),
                    'sottofascicolo2' => $CercaTitolario->getSottofascicolo2()
                )
            )
        );
        return $this->ws_call('CercaTitolario', $param);
    }

}

?>
