<?php

require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');
require_once(ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php');
require_once(ITA_LIB_PATH . '/itaPHPTecut/itaTecutParam.class.php');

class itaTecutClient {
    /* @var $tecutParams itaTecutParam */

    private $tecutParams;
    private $result;
    private $error;
    private $fault;

    public function __construct($parameters) {
        $this->tecutParams = $parameters;
    }

    public function getTecutParams() {
        return $this->tecutParams;
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

    public function setTecutParams($tecutParams) {
        $this->tecutParams = $tecutParams;
    }

    public function setResult($result) {
        $this->result = $result;
    }

    public function setError($error) {
        $this->error = $error;
    }

    public function setFault($fault) {
        $this->fault = $fault;
    }

    /**
     * Chiamata a servizio wsDetermine action elenco
     * @param arry $param
     * @return boolean
     */
    public function wsDetermine_Elenco($param) {
        $wsdl = $this->tecutParams->getWsDetermine_wsdl();
        $operation = 'elenco';
        return $this->ws_call($wsdl, $operation, $param);
    }

    /**
     * Chiamata a servizio wsDetermine action archivio
     * @param arry $param
     * @return boolean
     */
    public function wsDetermine_Archivio($param) {
        $wsdl = $this->tecutParams->getWsDetermine_wsdl();
        $operation = 'archivio';
        return $this->ws_call($wsdl, $operation, $param);
    }

    /**
     * Chiamata a servizio wsDetermine action versione
     * (necessita di autorizzazione non ancora implementata)
     * 
     * @return type
     */
    public function wsDetermine_versione() {
        $wsdl = $this->tecutParams->getWsDetermine_wsdl();
        $operation = 'versione';
        return $this->ws_call($wsdl, $operation, '');
    }

    /**
     * Chiamata a servizio wsDelibere action archivio
     * @param arry $param
     * @return boolean
     */
    public function wsDelibere_Archivio($param) {
        $wsdl = $this->tecutParams->getWsDelibere_wsdl();
        $operation = 'archivio';
        return $this->ws_call($wsdl, $operation, $param);
    }

    /**
     * Chiamata al download del testo principale o allegato di un atto
     * @param string $linkTesto
     * @return boolean
     */
    public function getTesto($linkTesto) {
        return $this->getH($linkTesto);
    }

    /**
     * Chiamata al download del descrittore di un testo o allegato di un atto
     * @param string $linkTesto
     * @return boolean
     */
    public function getDescrittore($linkTesto) {
        return $this->getH($linkTesto, true);
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }

    private function getH($linkTesto, $descrittore = false) {
        $this->clearResult();
        $restClient = new itaRestClient();
        if ($descrittore) {
            $linkTesto .= "&x=1";
        }
        $retRest = $restClient->get($linkTesto);
        if (!$retRest) {
            $this->setError($restClient->getErrMessage());
            return false;
        }
        $this->setResult($restClient->getResult());
        return true;
    }

    /**
     * Chiamata generica a ws soap
     * 
     * @param String $wsdl
     * @param String $operationName
     * @param array / String $param
     * @return boolean
     */
    private function ws_call($wsdl, $operationName, $param) {
        $this->clearResult();
        $client = new nusoap_client($wsdl, true);
        $client->debugLevel = $this->tecutParams->getDebugLevel();
        $client->timeout = $this->tecutParams->getTimeout();
        $client->response_timeout = $this->tecutParams->getReponseTimeout();
        $client->soap_defencoding = 'UTF-8';
        $result = $client->call($operationName, $param);
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

}
