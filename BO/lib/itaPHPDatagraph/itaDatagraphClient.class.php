<?php

/**
 *
 * Classe per protocollo datagraph
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaDatagraphClient
 * @author     Luca Cardinali <l.cardinali@apra.it>
 * @version    04.12.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once('SOAP/Client.php');

class itaDatagraphClient {

    const LOGIN = 'Login';
    const INVIO_MAIL = 'InviaMail2';
    const PROTOCOLLA = 'Protocollazione';
    const AGGIUNGI_DOCUMENTO = 'AggiungiDocumento';
    const INSERIMENTO_DOCUMENTO = 'Inserimento';
    const NAMESPACES_TEM = 'http://tempuri.org/';
    const WS_TIMEOUT_DEFAULT = 60;

    private $namespaces = array();
    private $namespacePrefix = "tem"; // prefisso del namespace
    private $webservices_uri = "";
    private $soapActionPrefix = "";
    private $customNamespacePrefix = array(); // se ci sono dei campi che non hanno $namespacePrefix ma ne hanno 
    //uno custom, va passato in questo array (key=nomeCampo su $params, value= prefisso)
    private $timeout;
    private $result;
    private $error;
    private $request;
    private $response;
    private $codiceAOO;
    private $codiceEnte;
    private $unitaOrganizzativa;
    private $nomeApplicativo;
    private $username;
    private $password;
    private $codTitolario;

    /**
     * Login
     * 
     * @param array $params deve contenere un array con i parametri richiesti dal wsdl
     * 
     * @return string
     */
    public function ws_login() {
        $params = array(
            'strUserName' => $this->username,
            'strPassword' => $this->password,
            'strCodEnte' => $this->codiceAOO
        );
        $this->initTemNamespace();
        return $this->eseguiOperazione(self::LOGIN, $params);
    }

    /**
     * metodo protocollazione
     * 
     * @param array $params deve contenere un array con i parametri richiesti dal wsdl
     * 
     * @return string
     */
    public function ws_protocolla($token, $elementi) {
        if (!$this->username) {
            $this->handleError("Username mancante");
            return false;
        }
        if (!$elementi) {
            $this->handleError("Dati protocollo mancanti");
            return false;
        }
        if (!$token) {
            $this->handleError("Token mancante");
            return false;
        }

        $wsParams = array(
            'strUserName' => $this->username,
            'strDST' => $token
        );

        $this->initTemNamespace();
        $attachments = array(
            0 => array(
                'body' => $this->generaSegnatura($elementi),
                'cid' => rand(100, 1000),
                'content_type' => "text/xml"
            )
        );

        return $this->eseguiOperazione(self::PROTOCOLLA, $wsParams, $attachments);
    }

    /**
     * metodo invio mail
     * 
     * @param array $elementi deve contenere un array con i parametri richiesti dal wsdl
     * 
     * @return string
     */
    public function ws_inviaMail($token, $anno, $numero) {
        if (!$anno || !$numero) {
            $this->handleError("Dati mancanti");
            return false;
        }
        if (!$token) {
            $this->handleError("Token mancante");
            return false;
        }

        $wsParams = array(
            'Anno' => $anno,
            'Numero' => $numero,
//            'Oggetto' => $oggetto,
//            'TestoMail' => $testoMail,
            'WithSegnaturaXML' => 1,
//            'AccountMailMittente' => null,
            'strDST' => $token
        );

        $this->initTemNamespace();

        return $this->eseguiOperazione(self::INVIO_MAIL, $wsParams);
    }
 
    private function generaSegnatura($elementi) {
        $tipoProtocollo = $elementi['tipo'];
        $codAmministrazione = $this->codiceEnte;
        $codAoo = "P_CWOL";
        $numeroRegistrazione = 0;
        $dataRegistrazione = 0;

        $segnatura = '<Segnatura>';
        $segnatura .= '<Intestazione>';

        $segnatura .= '<Oggetto>' . $elementi['dati']['Oggetto'];
        $segnatura .= '</Oggetto>';

        $segnatura .= '<Identificatore>';
        $segnatura .= '<CodiceAmministrazione>' . $codAmministrazione;
        $segnatura .= '</CodiceAmministrazione>';
        $segnatura .= '<CodiceAOO>' . $codAoo;
        $segnatura .= '</CodiceAOO>';
        $segnatura .= '<NumeroRegistrazione>' . $numeroRegistrazione;
        $segnatura .= '</NumeroRegistrazione>';
        $segnatura .= '<DataRegistrazione>' . $dataRegistrazione;
        $segnatura .= '</DataRegistrazione>';
        $segnatura .= '<Flusso>' . $tipoProtocollo;
        $segnatura .= '</Flusso>';
        $segnatura .= '</Identificatore>';

        $segnatura .= '<Mittente>';
        if ($elementi['dati']['MittDest']['Tipo'] == 'Persona') {
            $segnatura .= '<Persona id="RSSARD87S43E344C">';
            $segnatura .= '<Nome><![CDATA[' . $elementi['dati']['MittDest']['Nome'];
            $segnatura .= ']]></Nome>';
            $segnatura .= '<Cognome><![CDATA[' . $elementi['dati']['MittDest']['Cognome'];
            $segnatura .= ']]></Cognome>';
            $segnatura .= '</Persona>';
        } else {
            $segnatura .= '<Amministrazione>';
            $segnatura .= '<Denominazione><![CDATA[' . $elementi['dati']['MittDest']['Denominazione'];
            $segnatura .= ']]></Denominazione>';
            $segnatura .= '<CodiceAmministrazione>' . $codAmministrazione;
            $segnatura .= '</CodiceAmministrazione>';
            $segnatura .= '<IndirizzoTelematico tipo="smtp">' . $elementi['dati']['MittDest']['Email'];
            $segnatura .= '</IndirizzoTelematico>';
            $segnatura .= '<UnitaOrganizzativa id="' . $this->getUnitaOrganizzativa() . '">';
            $segnatura .= '</UnitaOrganizzativa>';
            $segnatura .= '</Amministrazione>';
            $segnatura .= '<AOO>';
            $segnatura .= '<CodiceAOO>' . $this->codiceAOO;
            $segnatura .= '</CodiceAOO>';
            $segnatura .= '</AOO>';
        }
        $segnatura .= '</Mittente>';

        if ($elementi['dati']['destinatari']) {
            foreach ($elementi['dati']['destinatari'] as $destinatario) {
                $segnatura .= '<Destinatario>';
                if ($destinatario['Tipo'] == 'Persona') {
                    $segnatura .= '<Persona id="' . $destinatario['CF'] . '">';
                    $segnatura .= '<Nome><![CDATA[' . $destinatario['Nome'];
                    $segnatura .= ']]></Nome>';
                    $segnatura .= '<Cognome><![CDATA[' . $destinatario['Cognome'];
                    $segnatura .= ']]></Cognome>';
                    $segnatura .= '<Denominazione>Cittadino';
                    $segnatura .= '</Denominazione>';
                    $segnatura .= '</Persona>';
                } else {
                    $segnatura .= '<Amministrazione>';
                    $segnatura .= '<Denominazione><![CDATA[' . $destinatario['Denominazione'];
                    $segnatura .= ']]></Denominazione>';
                    $segnatura .= '<CodiceAmministrazione>' . $destinatario['CodiceAmministrazione'];
                    $segnatura .= '</CodiceAmministrazione>';
                    $segnatura .= '<IndirizzoTelematico tipo="smtp">' . $destinatario['Email'];
                    $segnatura .= '</IndirizzoTelematico>';
                    $segnatura .= '</Amministrazione>';
                    $segnatura .= '<AOO>';
                    $segnatura .= '<CodiceAOO>' . $destinatario['CodiceAOO'];
                    $segnatura .= '</CodiceAOO>';
                    $segnatura .= '</AOO>';
                }
                $segnatura .= '</Destinatario>';
            }
        }

        if ($this->getCodTitolario()) {
            $segnatura .= '<Classifica>';
            $segnatura .= '<CodiceAmministrazione>' . $this->codiceEnte;
            $segnatura .= '</CodiceAmministrazione>';
            $segnatura .= '<CodiceAOO>' . $this->codiceAOO;
            $segnatura .= '</CodiceAOO>';
            $segnatura .= '<CodiceTitolario>' . $this->getCodTitolario();
            $segnatura .= '</CodiceTitolario>';
            $segnatura .= '</Classifica>';
        }
        if ($elementi['dati']['Fascicolazione']) {
            $segnatura .= '<Fascicolo numero="' . $elementi['dati']['Fascicolazione']['Numero'] . '" anno="' . $elementi['dati']['Fascicolazione']['Anno'] . '">';
            $segnatura .= $elementi['dati']['Fascicolazione']['Oggetto'];
            $segnatura .= '</Fascicolo>';
        }
        $segnatura .= '</Intestazione>';
        $segnatura .= '<Descrizione>';
        if ($elementi['dati']['DocumentoPrincipale']) {
            $segnatura .= '<Documento id="' . $elementi['dati']['DocumentoPrincipale']['Id'] . '" nome="' . $elementi['dati']['DocumentoPrincipale']['Nome'] . '">';
            $segnatura .= '</Documento>';
        }
        if ($elementi['dati']['DocumentiAllegati']) {
            $segnatura .= '<Allegati>';
            foreach ($elementi['dati']['DocumentiAllegati'] as $value) {
                $segnatura .= '<Documento id="' . $value['Id'] . '" nome="' . $value['Nome'] . '">';
                $segnatura .= '</Documento>';
            }
            $segnatura .= '</Allegati>';
        }
        $segnatura .= '</Descrizione>';
        $segnatura .= '<ApplicativoProtocollo nome="' . $this->nomeApplicativo . '">';
        $segnatura .= '<Parametro nome="tipoSmistamento" valore="COMPETENZA"></Parametro>';
        if ($this->unitaOrganizzativa) {
            $segnatura .= '<Parametro nome="uo" valore="' . $this->unitaOrganizzativa . '"></Parametro>';
        }
        $segnatura .= '</ApplicativoProtocollo>';

        $segnatura .= '</Segnatura>';

        return $segnatura;
    }

    /**
     * Metodo aggiungiDocumento, permette di inserire un allegato ad un protocollo in un secondo momento
     * 
     * @param array $params deve contenere un array con i parametri richiesti dal wsdl      
     * 
     * @return string
     */
    public function ws_aggiungiDocumento($params) {
        return $this->eseguiOperazione(self::AGGIUNGI_DOCUMENTO, $params);
    }

    /**
     * Metodo inserimento, permette di inserire un allegato ad un protocollo al momento della protocollazione.
     * Va chiamato questo metodo prima del protocolla e sul protocolla vanno passate le chiavi degli allegati inseriti da qui
     * 
     * @param array $params deve contenere un array con i parametri richiesti dal wsdl      
     * 
     * @return string
     */
    public function ws_inserisciDocumento($token, $nomeAllegato, $allegato) {
        if (!$this->username) {
            $this->handleError("Username mancante");
            return false;
        }
        if (!$nomeAllegato) {
            $this->handleError("Nome Allegato mancante");
            return false;
        }
        if (!$allegato) {
            $this->handleError("Corpo Allegato mancante");
            return false;
        }
        if (!$token) {
            $this->handleError("Token mancante");
            return false;
        }

        $wsParams = array(
            'strUserName' => $this->username,
            'strDST' => $token
        );

        $attachments = array(
            0 => array(
                'body' => $allegato,
                'cid' => rand(100, 1000),
                'content_type' => itaMimeTypeUtils::estraiEstensione($nomeAllegato)
            )
        );
        $this->initTemNamespace();
        return $this->eseguiOperazione(self::INSERIMENTO_DOCUMENTO, $wsParams, $attachments);
    }

    private function eseguiOperazione($nomeMetodo, $params, $attachments = array()) {
        $this->clearResult();

        if (!$params) {
            $this->handleError("Inserire i parametri");
            return false;
        }

        if (!$this->getWebservices_uri()) {
            $this->handleError("Inserire l'endpoint");
            return false;
        }

        return $this->ws_call($nomeMetodo, $params, $attachments);
    }

    private function ws_call($operationName, $params, $attachments = array()) {
        $this->setRequest(null);
        $this->setResponse(null);
        $client = new SOAP_client($this->webservices_uri, false, false);
        $client->_options['use'] = 'literal';
        $client->_options['timeout'] = self::WS_TIMEOUT_DEFAULT;
        if ($attachments) {
            $client->_options['attachments'] = 'Dime';
            $client->_attachments = $attachments;
        }
        $soapAction = $this->getSoapActionPrefix() . $operationName;

        $result = $client->call(($this->getNamespacePrefix() . ':' . $operationName), $params, $this->getNamespaces(), $soapAction);
        $this->setRequest($client->request);
        $this->setResponse($client->response);
        if (!$result) {
            if ($client->fault) {
                $this->handleError("Errore: " . $client->faultstring["!"]);
                return false;
            } else {
                $err = $client->getError();
                if ($err) {
                    $this->handleError("Errore: " . $err);
                    return false;
                }
            }
        }

        $this->result = $result;
        return true;
    }

    private function initTemNamespace() {
//        $this->setNamespacePrefix('ns4');
        $this->setSoapActionPrefix(self::NAMESPACES_TEM);
        $this->setNamespaces(self::NAMESPACES_TEM);
    }

    function getNamespacePrefix() {
        return $this->namespacePrefix;
    }

    function getWebservices_uri() {
        return $this->webservices_uri;
    }

    function getTimeout() {
        return $this->timeout;
    }

    function getResult() {
        return $this->result;
    }

    function getError() {
        return $this->error;
    }

    function setNamespacePrefix($namespacePrefix) {
        $this->namespacePrefix = $namespacePrefix;
    }

    function setWebservices_uri($webservices_uri) {
        $this->webservices_uri = $webservices_uri;
    }

    function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    function setResult($result) {
        $this->result = $result;
    }

    function setError($error) {
        $this->error = $error;
    }

    public function handleError($err) {
        $this->result = null;
        $this->error = $err;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
    }

    function getNamespaces() {
        return $this->namespaces;
    }

    function setNamespaces($namespaces) {
        $this->namespaces = $namespaces;
    }

    function getCustomNamespacePrefix() {
        return $this->customNamespacePrefix;
    }

    function setCustomNamespacePrefix($customNamespacePrefix) {
        $this->customNamespacePrefix = $customNamespacePrefix;
    }

    function getSoapActionPrefix() {
        return $this->soapActionPrefix;
    }

    function setSoapActionPrefix($soapActionPrefix) {
        $this->soapActionPrefix = $soapActionPrefix;
    }

    function getRequest() {
        return $this->request;
    }

    function setRequest($request) {
        $this->request = $request;
    }

    function getResponse() {
        return $this->response;
    }

    function setResponse($response) {
        $this->response = $response;
    }

    function getCodiceAOO() {
        return $this->codiceAOO;
    }

    function getUsername() {
        return $this->username;
    }

    function getPassword() {
        return $this->password;
    }

    function getUnitaOrganizzativa() {
        return $this->unitaOrganizzativa;
    }

    function setCodiceAOO($codiceAOO) {
        $this->codiceAOO = $codiceAOO;
    }

    function setUsername($username) {
        $this->username = $username;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function getCodiceEnte() {
        return $this->codiceEnte;
    }

    function setCodiceEnte($codiceEnte) {
        $this->codiceEnte = $codiceEnte;
    }

    function setUnitaOrganizzativa($unitaOrganizzativa) {
        $this->unitaOrganizzativa = $unitaOrganizzativa;
    }

    function getNomeApplicativo() {
        return $this->nomeApplicativo;
    }

    function setNomeApplicativo($nomeApplicativo) {
        $this->nomeApplicativo = $nomeApplicativo;
    }

    function getCodTitolario() {
        return $this->codTitolario;
    }

    function setCodTitolario($codTitolario) {
        $this->codTitolario = $codTitolario;
    }


}

?>
