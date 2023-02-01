<?php

require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

class praWsFrontOffice {

    private $namespace = "";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $debugLevel = 0;
    private $timeout = 120;
    private $response_timeout = 300;
    private $max_execution_time = 120;
    private $result;
    private $error;
    private $fault;
    private $debug;

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    public function setWebservices_uri($webservices_uri) {
        $this->webservices_uri = $webservices_uri;
    }

    public function setWebservices_wsdl($webservices_wsdl) {
        $this->webservices_wsdl = $webservices_wsdl;
    }

    public function setMax_execution_time($max_execution_time) {
        $this->max_execution_time = $max_execution_time;
    }

    public function setResponse_timeout($response_timeout) {
        $this->response_timeout = $response_timeout;
    }

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    public function setDebugLevel($debugLevel) {
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

    public function getDebug() {
        return $this->debug;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
        $this->debug = array();
    }

    private function ws_call($methodName, $params) {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_wsdl, true);
        $client->debugLevel = $this->debugLevel;
        $client->timeout = $this->timeout;
        $client->response_timeout = $this->response_timeout;
        $client->soap_defencoding = 'UTF-8';

        $result = $client->call($methodName, $params);

//        file_put_contents("/users/tmp/param_praWsFrontOffice_$methodName.xml", $params);
//        file_put_contents("/users/tmp/request_praWsFrontOffice_$methodName.xml", $client->request);
//        file_put_contents("/users/tmp/response_praWsFrontOffice_$methodName.xml", $client->response);
        $this->debug['REQUEST'] = $client->request;
        $this->debug['RESPONSE'] = $client->response . "\n\n" . $this->result;
        if ($this->debugLevel > 0) {
            $this->debug['DEBUG'] = $client->getDebug();
        }

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
     * @param type $param = array(
     *   'UserName',
     *   'UserPassword',
     *   'DomainCode'
     * )
     */
    public function ws_GetItaEngineContextToken($param) {
        return $this->ws_call('GetItaEngineContextToken', $param);
    }

    /**
     * 
     * @param type $param
     * @return type
     */
    public function ws_CheckItaEngineContextToken($param) {
        return $this->ws_call('CheckItaEngineContextToken', $param);
    }

    /**
     * @param type $param = array(
     *   'TokenKey',
     *   'DomainCode'
     * )
     */
    public function ws_DestroyItaEngineContextToken($param) {
        return $this->ws_call('DestroyItaEngineContextToken', $param);
    }

    /**
     * @param type $param array(
     *  'itaEngineContextToken',
     *  'domainCode'
     * )
     */
    public function ws_ctrRichieste($param) {
        return $this->ws_call('CtrRichieste', $param);
    }

    /**
     * @param type $param array(
     *  'itaEngineContextToken',
     *  'domainCode'
     * )
     */
    public function ws_setStatoAcquisizioneRichiesta($param) {
        return $this->ws_call('SetStatoAcquisizioneRichiesta', $param);
    }

    /**
     * @param type $param array(
     *  'itaEngineContextToken',
     *  'domainCode',
     *  'numeroRichiesta',
     *  'annoRichiesta'
     * )
     */
    public function ws_getRichiestaDati($param) {
        return $this->ws_call('GetRichiestaDati', $param);
    }

    /**
     * @param type $param array(
     *  'itaEngineContextToken',
     *  'domainCode',
     *  'rowid'
     * )
     */
    public function ws_getRichiestaAllegatoForRowid($param) {
        return $this->ws_call('GetRichiestaAllegatoForRowid', $param);
    }

    /**
     * @param type $param array(
     *  'itaEngineContextToken',
     *  'domainCode',
     *  'numeroRichiesta',
     *  'annoRichiesta'
     * )
     */
    public function ws_acquisisciRichiesta($param) {
        return $this->ws_call('AcquisisciRichiesta', $param);
    }

    /**
     * @param type $param array(
     *  'itaEngineContextToken',
     *  'domainCode',
     *  'numeroRichiesta',
     *  'annoRichiesta'
     * )
     */
    public function ws_getPraticaDati($param) {
        return $this->ws_call('GetPraticaDati', $param);
    }

    /**
     * @param type $param array(
     *  'itaEngineContextToken',
     *  'domainCode',
     *  'rowid'
     * )
     */
    public function ws_getPraticaAllegatoForRowid($param) {
        return $this->ws_call('GetPraticaAllegatoForRowid', $param);
    }

    /**
     * @param type $param array(
     *  'itaEngineContextToken',
     *  'domainCode',
     *  'numeroPratica',
     *  'annoPratica',
     *  'annotazione',
     *  'descrizioneTipoPasso',
     *  'descrizionePasso',
     *  'dataApertura',
     *  'statoApertura',
     *  'dataChiusura',
     *  'statoChiusura',
     *  'pubblicaStatoPasso',
     *  'pubblicaAllegati'
     * )
     */
    public function ws_appendPassoPraticaSimple($param) {
        return $this->ws_call('AppendPassoPraticaSimple', $param);
    }

    /**
     * @param type $param array(
     *  'itaEngineContextToken',
     *  'domainCode',
     *  'numeroPratica',
     *  'annoPratica',
     *  'descrizioneTipoPasso',
     *  'descrizionePasso',
     *  'pubblicaAllegatiArticolo',
     *  'utente',
     *  'password',
     *  'categoria',
     *  'titolo',
     *  'dadatapubbl',
     *  'daorapubbl',
     *  'adatapubbl',
     *  'aorapubbl',
     *  'corpo'
     * )
     */
    public function ws_appendPassoPraticaArticolo($param) {
        return $this->ws_call('AppendPassoPraticaArticolo', $param);
    }

    /**
     * @param type $param array(
     *  'itaEngineContextToken',
     *  'domainCode',
     *  'chiavePasso',
     *  'allegato',
     *  'pubblicato'
     * )
     */
    public function ws_putAllegatoPasso($param) {
        return $this->ws_call('PutAllegatoPasso', $param);
    }

    /**
     * @param type $param array(
     *  'itaEngineContextToken',
     *  'domainCode',
     *  'numeroPratica',
     *  'annoPratica'
     * )
     */
    public function ws_cancellaPratica($param) {
        return $this->ws_call('CancellaPratica', $param);
    }

    /**
     * @param type $param array(
     *  'itaEngineContextToken',
     *  'domainCode',
     *  'stato',
     *  'responsabile'
     * )
     */
    public function ws_getElencoPassi($param) {
        return $this->ws_call('GetElencoPassi', $param);
    }

    /**
     * @param type $param array(
     *  'itaEngineContextToken',
     *  'domainCode',
     *  'idPasso',
     *  'stato',
     *  'dataApertuta',
     *  'dataChiusura'
     * )
     */
    public function ws_aggiornaStatoPasso($param) {
        return $this->ws_call('AggiornaStatoPasso', $param);
    }

    /**
     * @param type $param array(
     *  'itaEngineContextToken',
     *  'domainCode',
     *  'chiave',
     *  'valore',
     * )
     */
    public function ws_getProric($param) {
        return $this->ws_call('GetPRORIC', $param);
    }

    /**
     * @param type $param array(
     *  'itaEngineContextToken',
     *  'domainCode',
     *  'chiave',
     *  'valore',
     * )
     */
    public function ws_getRicdoc($param) {
        return $this->ws_call('GetRICDOC', $param);
    }

    /**
     * @param type $param array(
     *  'itaEngineContextToken',
     *  'domainCode',
     *  'richiesta',
     * )
     */
    public function ws_getXmlInfoAccorpate($param) {
        return $this->ws_call('GetXMLINFOAccorpate', $param);
    }

    /**
     * @param type $param array(
     *  'itaEngineContextToken',
     *  'domainCode',
     *  'richiesta',
     * )
     */
    public function ws_getXmlInfo($param) {
        return $this->ws_call('GetXMLINFO', $param);
    }

    /**
     * 
     * @param type $param
     * @return type
     */
    public function ws_setMarcaturaRichiesta($param) {
        return $this->ws_call('SetMarcaturaRichiesta', $param);
    }

    /**
     * 
     * @param type $param
     * @return type
     */
    public function ws_setErroreProtocollazione($param) {
        return $this->ws_call('SetErroreProtocollazione', $param);
    }

    /**
     * @param array $param array(
     *   'token',
     *   'domainCode',
     *   'nomeFile',
     *   'stream'
     * )
     * @return array array(
     *   'id',
     *   'hash'
     * )
     */
    public function ws_insertDocumentoRichiesta($param) {
        return $this->ws_call('InsertDocumentoRichiesta', $param);
    }

    /**
     * @param array $param array(
     *   'token',
     *   'domainCode',
     *   'datiRichiesta' => array(
     *     'codiceSportello',
     *     'codiceProcedimento',
     *     'codiceEvento'
     *   ),
     *   'datiAggiuntivi' => array(
     *     'datoAggiuntivo' => array(
     *       array(
     *         'chiave',
     *         'valore'
     *       ),
     *       ...
     *     )
     *   ),
     *   'allegatiRichiesta' => array(
     *     'allegatoRichiesta' => array(
     *       array(
     *         'id',
     *         'hash',
     *         'nomeFile',
     *         'note'
     *       ),
     *       ...
     *     )
     *   ),
     *   'stream'
     * )
     * @return array array(
     *   'codiceRichiesta'
     * )
     */
    public function ws_putRichiesta($param) {
        return $this->ws_call('PutRichiesta', $param);
    }

}
