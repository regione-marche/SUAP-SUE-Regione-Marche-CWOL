<?php

/**
 *
 * Classe per protocollo folium di dedagroup
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaFoliumClient
 * @author     Luca Cardinali <l.cardinali@apra.it>
 * @version    12.11.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

class itaFoliumClient {

    const PROTOCOLLA = 'protocolla';
    const INSERIMENTO_ALLEGATO = 'inserisciAllegato';
    const INSERIMENTO_DOC_PRINCIP = 'inserisciContenuto';
    const WS_TIMEOUT_DEFAULT = 60;
    const NAMESPACES_URN = 'urn:ProtocolloWebService';
    const NAMESPACES_SOAPENC = 'http://schemas.xmlsoap.org/soap/encoding/';
    const NAMESPACES_SOAPENV = 'http://schemas.xmlsoap.org/soap/envelope/';

    private $namespaces = array();
    private $namespacePrefix = "urn"; // prefisso del namespace
    private $timeout;
    private $result;
    private $error;
    private $request;
    private $response;
    private $codiceAOO;
    private $codiceEnte;
    private $nomeApplicativo;
    private $username;
    private $password;
    private $codTitolario;
    private $registro;
    private $mezzoSpedizioneDefault;
    private $codiceUfficioDefault;

    /**
     * metodo inserisciContenuto (da fare dopo aver protocollato)
     * 
     * @param array $params deve contenere un array con i parametri richiesti dal wsdl
     * 
     * @return string
     */
    public function ws_inserisciDocumentoPrincipale($idProtocollo, $nomeFile, $contenuto) {
        $xml = $this->generaAuthXml();
        $xml .= '<in1 xsi:type="type:ImmagineDocumentale" xmlns:type="http://type.ws.folium.agora">';
        $xml .= '<id>' . $idProtocollo . '</id>';
        $xml .= '<nomeFile>' . $nomeFile . '</nomeFile>';
        $xml .= '<contenuto>' . $contenuto . '</contenuto>';
        $xml .= '</in1>';
        $xml .= '<in2>false</in2>'; // timbro false
        
        return $this->eseguiOperazione(self::INSERIMENTO_DOC_PRINCIP, $xml);
    }

    /**
     * metodo inserisciAllegato (da fare dopo aver protocollato)
     * 
     * @param array $params deve contenere un array con i parametri richiesti dal wsdl
     * 
     * @return string
     */
    public function ws_inserisciAllegato($idProtocollo, $nomeFile, $contenuto) {
        $xml = $this->generaAuthXml();
        $xml .= '<in1 xsi:type="type:Allegato" xmlns:type="http://type.ws.folium.agora">';
        $xml .= '<idProfilo>' . $idProtocollo . '</idProfilo>';
        $xml .= '<nomeFile>' . $nomeFile . '</nomeFile>';
        $xml .= '<descrizione>' . $nomeFile . '</descrizione>';
        $xml .= '<contenuto>' . $contenuto . '</contenuto>';
        $xml .= '</in1>';

        return $this->eseguiOperazione(self::INSERIMENTO_ALLEGATO, $xml);
    }

    private function generaAuthXml() {
        $xml = '<in0 xsi:type="ws:WSAuthentication" xmlns:ws="http://ws.folium.agora">';
        $xml .= '<aoo>' . $this->codiceAOO . '</aoo>';
        $xml .= '<applicazione>' . $this->nomeApplicativo . '</applicazione>';
        $xml .= '<ente>' . $this->codiceEnte . '</ente>';
        $xml .= '<password>' . $this->password . '</password>';
        $xml .= '<username>' . $this->username . '</username>';
        $xml .= '</in0>';

        return $xml;
    }

    /**
     * metodo protocollazione
     * 
     * @param array $params deve contenere un array con i parametri richiesti dal wsdl
     * 
     * @return string
     */
    public function ws_protocolla($elementi) {
        $tipoProtocollo = $elementi['tipo'];

        $xml = $this->generaAuthXml();

        $xml .= '<in1 xsi:type="type:DocumentoProtocollato" xmlns:type="http://type.ws.folium.agora">';
        $xml .= '<oggetto>' . $elementi['dati']['Oggetto'] . '</oggetto>';
        $xml .= '<registro>' . $this->registro . '</registro>';
        $xml .= '<tipoProtocollo>' . $tipoProtocollo . '</tipoProtocollo>';
        $xml .= '<mittentiDestinatari xsi:type="urn:ArrayOf_tns3_MittenteDestinatario" SOAP-ENC:arrayType="type:MittenteDestinatario[]">';

        if ($tipoProtocollo == 'U') {
            if ($elementi['dati']['destinatari']) {
                foreach ($elementi['dati']['destinatari'] as $destinatario) {
                    $xml .= '<MittenteDestinatario>';
                    if ($destinatario['Denominazione']) {
                        $xml .= '<denominazione>' . $destinatario['Denominazione'] . '</denominazione>';
                    } else if ($destinatario['Cognome']) {
                        $xml .= '<denominazione>' . $destinatario['Cognome'] . ' ' . $destinatario['Nome'] . '</denominazione>';
                    }
                    $xml .= '<email>' . $destinatario['Email'] . '</email>';
//                    if ($destinatario['Tipo']) {
//                        $tipoDes = $destinatario['Tipo'] === 'Persona' ? "F" : "G";
//                        $xml .= '<tipo>' . $tipoDes . '</tipo>';
//                    }
                    if ($this->mezzoSpedizioneDefault) {
                        $xml .= '<codiceMezzoSpedizione>' . $this->mezzoSpedizioneDefault . '</codiceMezzoSpedizione>';
                    }

                    $xml .= '</MittenteDestinatario>';
                }
            }
        } else {
            if ($elementi['dati']['MittDest']) {
                $xml .= '<MittenteDestinatario>';
                if ($elementi['dati']['MittDest']['Denominazione']) {
                    $xml .= '<denominazione>' . $elementi['dati']['MittDest']['Denominazione'] . '</denominazione>';
                } else if ($destinatario['Cognome']) {
                    $xml .= '<denominazione>' . $elementi['dati']['MittDest']['Cognome'] . ' ' . $elementi['dati']['MittDest']['Nome'] . '</denominazione>';
                }
                $xml .= '<email>' . $elementi['dati']['MittDest']['Email'] . '</email>';

                $xml .= '</MittenteDestinatario>';
            }
        }
        $xml .= '</mittentiDestinatari>';

        $nomeDocPrinc = $elementi['dati']['DocumentoPrincipale']['Nome'];
        if ($nomeDocPrinc) {
            $xml .= '<isContenuto>true</isContenuto>';
            $xml .= '<nomeFileContenuto>' . $nomeDocPrinc . '</nomeFileContenuto>';
        } else {
            $xml .= '<isContenuto>false</isContenuto>';
        }
        if ($this->codiceUfficio) {
            $xml .= '<ufficioCompetente>' . $this->codiceUfficio . '</ufficioCompetente>';
        }
        if ($this->getCodTitolario()) {
            $xml .= '<vociTitolario>' . $this->getCodTitolario() . '</vociTitolario>';
        }
        $xml .= '</in1>';

        return $this->eseguiOperazione(self::PROTOCOLLA, $xml);
    }

    private function initTemNamespace() {
        $this->setNamespaces(array(
            "urn" => self::NAMESPACES_URN,
            "SOAP-EN" => self::NAMESPACES_SOAPENC,
            "SOAP-ENV" => self::NAMESPACES_SOAPENV,
        ));
    }

    private function eseguiOperazione($nomeMetodo, $params) {
        $this->clearResult();
        $this->initTemNamespace();
        if (is_array($params)) {
            // se params è array, aggiungo i prefissi e lo passo a nusoap, 
            // altrimenti se è già xml non devo aggiugnere i prefissi che ci sono già e passo a nusoap la stringa
            $params = $this->aggiungiPrefisso($params);

            if (!$params) {
                $this->handleError("Inserire i parametri");
                return false;
            }
        }

        if (!$this->getWebservices_uri()) {
            $this->handleError("Inserire l'endpoint");
            return false;
        }

        return $this->ws_call($nomeMetodo, $params);
    }

    private function ws_call($operationName, $params, $headers = null) {
        $this->setRequest(null);
        $this->setResponse(null);
        $client = new nusoap_client($this->webservices_uri, false);
        $client->debugLevel = 0;
        //setting timeout
        $client->timeout = $this->timeout > 0 ? $this->timeout : self::WS_TIMEOUT_DEFAULT;
        $client->response_timeout = $client->timeout;
        //setting headers
        $client->setHeaders($headers);
        $client->soap_defencoding = 'UTF-8';
        $soapAction = $this->getNamespacePrefix() . ':' . $operationName;
        $result = $client->call(($this->getNamespacePrefix() . ':' . $operationName), $params, $this->getNamespaces(), $soapAction, false, null, 'rpc', 'literal');
        $this->setRequest($client->request);
        $this->setResponse($client->response);
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

        $this->result = $result;
        return true;
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

    function getRegistro() {
        return $this->registro;
    }

    function setRegistro($registro) {
        $this->registro = $registro;
    }

    function getNamespaces() {
        return $this->namespaces;
    }

    function setNamespaces($namespaces) {
        $this->namespaces = $namespaces;
    }

    function getMezzoSpedizioneDefault() {
        return $this->mezzoSpedizioneDefault;
    }

    function setMezzoSpedizioneDefault($mezzoSpedizione) {
        $this->mezzoSpedizioneDefault = $mezzoSpedizione;
    }

    function setCodiceUfficio($codiceUfficio) {
        $this->codiceUfficio = $codiceUfficio;
    }

    function getCodiceUfficio() {
        return $this->codiceUfficio;
    }

}

?>
