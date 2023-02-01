<?php
/**
 *
 * Classe per collegamento REST Procedimnarche
 *
 * PHP Version 5
 *
 * @category
 * @package    test REST
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    11.05.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php');

class itaRESTProcedimarcheClient {

    private $webservices_uri = "";
    private $username = "";
    private $password = "";
    private $cf_ente = "";
    private $CodiceAmministrazione = "";
    private $CodiceAOO = "";
    private $debugLevel = false;
    private $debugStr = '';
    private $httpStatus = '';
    private $timeout = 2400;
    private $result;
    private $error;
    private $fault;

    function getHttpStatus() {
        return $this->httpStatus;
    }

    function setHttpStatus($httpStatus) {
        $this->httpStatus = $httpStatus;
    }

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    public function setWebservices_uri($webservices_uri) {
        $this->webservices_uri = $webservices_uri;
    }

    public function getWebservices_uri() {
        return $this->webservices_uri;
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

    public function getCodiceAmministrazione() {
        return $this->CodiceAmministrazione;
    }

    public function getCodiceAOO() {
        return $this->CodiceAOO;
    }

    private function getBase64EncodedCredentials() {
        return base64_encode($this->username . ":" . $this->password);
    }

    private function clearResult() {
        $this->debugStr = null;
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }
    
    /**
     * Legge la versione con verbo GET
     * 
     * @return boolean
     */
    public function rest_Version() {
        $this->clearResult();
        $restClient = new itaRestClient();
        $restClient->setDebugLevel($this->debugLevel);
        $restClient->setCurlopt_url($this->webservices_uri . "/api/version");
        $restClient->setTimeout($this->timeout);
        $headers = array(
            'Authorization: Basic ' . $this->getBase64EncodedCredentials()
        );
        if ($restClient->get('', '', $headers)) {
            $this->debugStr = $restClient->getDebug();
            $this->result = $restClient->getResult();
            $this->httpStatus = $restClient->getHttpStatus();
            return true;
        } else {
            $this->debugStr = $restClient->getDebug();
            $this->error = $restClient->getErrMessage();
            $this->httpStatus = $restClient->getHttpStatus();
            return false;
        }
    }

    /**
     * Legge porprieta utente collegato verbo GET
     * 
     * @return boolean
     */
    public function rest_Azienda() {
        $this->clearResult();
        $restClient = new itaRestClient();
        $restClient->setDebugLevel($this->debugLevel);
        $restClient->setCurlopt_url($this->webservices_uri . "/api/azienda");
        $restClient->setTimeout($this->timeout);
        $headers = array(
            'Authorization: Basic ' . $this->getBase64EncodedCredentials()
        );
        if ($restClient->get('', '', $headers)) {
            $this->debugStr = $restClient->getDebug();
            $this->result = $restClient->getResult();
            $this->httpStatus = $restClient->getHttpStatus();
            return true;
        } else {
            $this->debugStr = $restClient->getDebug();
            $this->error = $restClient->getErrMessage();
            $this->httpStatus = $restClient->getHttpStatus();
            return false;
        }
    }

    /**
     * Legge Anagrafica Serie Archivistica verbo GET
     * 
     * @param type $id
     * @return boolean
     */
    public function rest_SerieArchivistica($id = '') {
        $this->clearResult();
        $restClient = new itaRestClient();
        $restClient->setDebugLevel($this->debugLevel);
        $restClient->setCurlopt_url($this->webservices_uri . "/api/seriearchivistica" . ($id ? "/$id" : ""));
        $restClient->setTimeout($this->timeout);
        $headers = array(
            'Authorization: Basic ' . $this->getBase64EncodedCredentials()
        );
        if ($restClient->get('', '', $headers)) {
            $this->debugStr = $restClient->getDebug();
            $this->result = $restClient->getResult();
            $this->httpStatus = $restClient->getHttpStatus();
            return true;
        } else {
            $this->debugStr = $restClient->getDebug();
            $this->error = $restClient->getErrMessage();
            $this->httpStatus = $restClient->getHttpStatus();
            return false;
        }
    }

    /**
     * Legge Anagarfica fascicoli verbo GET
     * 
     * @param type $id
     * @return boolean
     */
    public function rest_TipoFascicolo($id = '') {
        $this->clearResult();
        $restClient = new itaRestClient();
        $restClient->setDebugLevel($this->debugLevel);
        $restClient->setCurlopt_url($this->webservices_uri . "/api/tipofascicolo" . ($id ? "/$id" : ""));
        $restClient->setTimeout($this->timeout);
        $headers = array(
            'Authorization: Basic ' . $this->getBase64EncodedCredentials()
        );
        if ($restClient->get('', '', $headers)) {
            $this->debugStr = $restClient->getDebug();
            $this->result = $restClient->getResult();
            $this->httpStatus = $restClient->getHttpStatus();
            return true;
        } else {
            $this->debugStr = $restClient->getDebug();
            $this->error = $restClient->getErrMessage();
            $this->httpStatus = $restClient->getHttpStatus();
            return false;
        }
    }

    /**
     * Legge tipo procedimento Generale verbo get
     * 
     * @param type $id
     * @return boolean
     */
    public function rest_TipoProcedimentoGenerale($id = '') {
        $this->clearResult();
        $restClient = new itaRestClient();
        $restClient->setDebugLevel($this->debugLevel);
        $restClient->setCurlopt_url($this->webservices_uri . "/api/TipoProcedimentoGenerale" . ($id ? "/$id" : ""));
        $restClient->setTimeout($this->timeout);
        $headers = array(
            'Authorization: Basic ' . $this->getBase64EncodedCredentials()
        );
        if ($restClient->get('', '', $headers)) {
            $this->debugStr = $restClient->getDebug();
            $this->result = $restClient->getResult();
            $this->httpStatus = $restClient->getHttpStatus();
            return true;
        } else {
            $this->debugStr = $restClient->getDebug();
            $this->error = $restClient->getErrMessage();
            $this->httpStatus = $restClient->getHttpStatus();
            return false;
        }
    }

    /**
     * Inserisce metadati psecifici per procedimento verbo POST
     * 
     * @param type $jsondati
     * @return boolean
     */
    public function rest_TipoProcedimentoSpecificoInsert($jsondati) {
        $this->clearResult();
        $restClient = new itaRestClient();
        $restClient->setDebugLevel($this->debugLevel);
        $restClient->setCurlopt_url($this->webservices_uri);
        $restClient->setTimeout($this->timeout);

        $path = "/api/TipoProcedimentoSpecifico";
        $headers = array(
            'Authorization: Basic ' . $this->getBase64EncodedCredentials(),
            'Accept: application/json',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsondati)
        );
        if ($restClient->post($path, '', $headers, $jsondati)) {
            $this->debugStr = $restClient->getDebug();
            $this->result = $restClient->getResult();
            $this->httpStatus = $restClient->getHttpStatus();
            return true;
        } else {
            $this->debugStr = $restClient->getDebug();
            $this->error = $restClient->getErrMessage();
            $this->httpStatus = $restClient->getHttpStatus();
            return false;
        }
    }

    /**
     * Aggiorna Metadati procedimento specifico verbo PUT
     * 
     * @param type $idSpecifico
     * @param type $jsondati
     * @return boolean
     */
    public function rest_TipoProcedimentoSpecificoUpdate($idSpecifico, $jsondati) {
        $this->clearResult();
        $restClient = new itaRestClient();
        $restClient->setDebugLevel($this->debugLevel);
        $restClient->setCurlopt_url($this->webservices_uri);
        $restClient->setTimeout($this->timeout);

        $path = "/api/TipoProcedimentoSpecifico/$idSpecifico";
        $headers = array(
            'Authorization: Basic ' . $this->getBase64EncodedCredentials(),
            'Accept: application/json',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsondati)
        );
        if ($restClient->put($path, '', $headers, $jsondati)) {
            $this->debugStr = $restClient->getDebug();
            $this->result = $restClient->getResult();
            $this->httpStatus = $restClient->getHttpStatus();
            return true;
        } else {
            $this->debugStr = $restClient->getDebug();
            $this->error = $restClient->getErrMessage();
            $this->httpStatus = $restClient->getHttpStatus();
            return false;
        }
    }

    /**
     * Legge Anagrafica Metadati verbo GET
     * @return boolean
     */
    public function rest_TipoProcedimentoCompleto() {
        $this->clearResult();
        $restClient = new itaRestClient();
        $restClient->setDebugLevel($this->debugLevel);
        $restClient->setCurlopt_url($this->webservices_uri . "/api/TipoProcedimentoCompleto");
        $restClient->setTimeout($this->timeout);
        $headers = array(
            'Authorization: Basic ' . $this->getBase64EncodedCredentials()
        );
        if ($restClient->get('', '', $headers)) {
            $this->debugStr = $restClient->getDebug();
            $this->result = $restClient->getResult();
            $this->httpStatus = $restClient->getHttpStatus();
            return true;
        } else {
            $this->debugStr = $restClient->getDebug();
            $this->error = $restClient->getErrMessage();
            $this->httpStatus = $restClient->getHttpStatus();
            return false;
        }
    }
}
?>