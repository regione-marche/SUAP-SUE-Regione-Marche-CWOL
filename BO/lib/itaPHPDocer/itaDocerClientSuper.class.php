<?php

/**
 *
 * Classe per collegamento ws DOCER 22 - Superclasse servizi
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPDocer
 * @author     
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    19.10.2017
 * @link
 * @see
 * @since
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');
require_once(ITA_LIB_PATH . '/nusoap/nusoapmime.php');

abstract class itaDocerClientSuper {

    protected $namespace;
    protected $namespaces;
    protected $webservicesEndPoint = '';
    protected $connectionTimeout = 2400;
    protected $responseTimeout = 2400;
    protected $debugLevel;
    protected $version;
    protected $result;
    protected $error;
    protected $fault;
    protected $attachments = array();
    protected $responseAttachments = array();

    public function __construct() {
        $this->init();
    }

    /**
     * Inizializzazione namespace, ecc...
     */
    protected abstract function init();

    public function clearAttachments() {
        $this->attachments = array();
    }

    public function addAttachment($data) {
        $this->attachments[] = $data;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    function setNamespaces($namespaces) {
        $this->namespaces = $namespaces;
    }

    public function setWebservicesEndPoint($webservices_uri) {
        $this->webservices_uri = $webservices_uri;
    }

    public function setConnectionTimeout($connectionTimeout) {
        $this->connectionTimeout = $connectionTimeout;
    }

    public function setResponseTimeout($responseTimeout) {
        $this->responseTimeout = $responseTimeout;
    }

    function setDebugLevel($debugLevel) {
        $this->debugLevel = $debugLevel;
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

    function setVersion($version) {
        $this->version = $version;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }

    protected function ws_call($operationName, $param, $ns = "web:") {
        $this->clearResult();
        /*
         * Istanza del client
         */
        $client = new nusoap_client_mime($this->webservices_uri, false);
        /*
         * Configurazioni di base
         */
        $client->debugLevel = 0;
        $client->timeout = $this->connectionTimeout > 0 ? $this->connectionTimeout : 120;
        $client->response_timeout = $this->responseTimeout;
        $client->soap_defencoding = 'UTF-8';

        /*
         * Configurazione envelope SOAP
         */
        $soapAction = $this->namespace . "/" . $operationName;
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';

        /*
         * Gestione attachments
         */
        foreach ($this->attachments as $attachment) {
            $client->addAttachment($attachment['data'], $attachment['filename'], $attachment['contenttype'], $attachment['cid']);
        }

        /*
         * Chiamata
         */
        $result = $client->call($ns . $operationName, $param, $this->namespaces, $soapAction, $headers, $rpcParams, $style, $use);

        /**
         * Legge gli allegati della risposta
         */
        $this->responseAttachments = $client->responseAttachments;

        /*
         * Controllo del risultato
         */
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

    public function getResponseAttachments() {
        return $this->responseAttachments;
    }
    
    /**
     * Restituisce il livello di ACL per utente e tipo documento specificati
     * @param string $user Utente
     * @param string $documentType Tipo documento
     * @return int Livello ACL:
     *      0 = Full Access
     *      1 = Normal Access
     *      2 = Read only
     */
    public function getACL($user, $documentType) {
        return 0;
    }
    
}
