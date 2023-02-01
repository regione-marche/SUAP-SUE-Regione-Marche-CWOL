<?php

require_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';
//require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

/**
 *
 * Classe per collegamento Omnis
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPOmnisClient
 * @author     Massimo Biagioli <m.biagioli@palinformatica.it>
 * @copyright  
 * @license
 * @version    25.11.2015
 * @link
 * @see
 * 
 */
class itaOmnisClient {

    private $restClient;
    private $protocol;
    private $webServerUrl;
    private $appServerUrl;
    private $omnisCGI;
    private $defaultLibrary;
    private $remoteTask;
    private $remoteTaskInt;
    private $parHexFormat;
    private $errCode;
    private $errMessage;
    private $httpStatus;

    public function __construct() {
        $this->restClient = new itaRestClient();
        //$this->setParameters();
    }

    public function callExecute($objectName, $methodName, $methodArgs, $lbs = null, $interattivo = false) {
        $args = $this->buildXmlParametersForExecute($objectName, $methodName, $methodArgs);
        $result = $this->call($args, $lbs !== null ? $lbs : $this->defaultLibrary, $interattivo);

        return $result;
    }

    private function buildXmlParametersForExecute($objectName, $methodName, $methodArgs) {
        $xml = new SimpleXMLElement('<EXECUTIONPARAMS/>');
        $nodeFunction = $xml->addChild('FUNCTION');
        $nodeFunction->addChild('FUNCTIONNAME', 'EXECUTE');
        $nodeFunction->addChild('OBJECTNAME', $objectName);
        $nodeFunction->addChild('METHODNAME', $methodName);
        $nodeParams = $xml->addChild('PARAMS');
        for ($i = 0; $i < count($methodArgs); $i++) {
            $nodeParams->addChild('COL' . ($i + 1), $methodArgs[$i]);
        }

        return $xml->asXML();
    }

    private function call($args, $lbs, $interattivo = false) {
        $url = $this->protocol . '://' . $this->webServerUrl;
        $par = $this->parHexFormat ? array_shift(unpack('H*', $args)) : $args;
        $data = array(
            'OmnisServer' => $this->appServerUrl,
            'OmnisLibrary' => $lbs,
            'OmnisClass' => $interattivo ? $this->remoteTaskInt : $this->remoteTask,
            'par' => $par,
        );

        $this->restClient->setCurlopt_url($url);
        if ($this->restClient->post($this->omnisCGI, $data)) {
            $xmlstr = $this->restClient->getResult();
            //$result = new SimpleXMLElement(trim($xmlstr));
            $result = trim($xmlstr);
            $this->setErrCode(0);
            $this->setErrMessage('');
        } else {
            $this->setErrCode($this->restClient->getErrCode());
            $this->setErrMessage($this->restClient->getErrMessage());
            $result = null;
        }
        $this->setHttpStatus($this->restClient->getHttpStatus());
        return $result;
    }
   
    /*
      private function setParameters() {
      $devLib = new devLib();
      $this->protocol = $devLib->getEnv_config('OMNIS', 'codice', 'PROTOCOL', false)['CONFIG'];
      $this->webServerUrl = $devLib->getEnv_config('OMNIS', 'codice', 'WEB_SERVER_URL', false)['CONFIG'];
      $this->appServerUrl = $devLib->getEnv_config('OMNIS', 'codice', 'APP_SERVER_URL', false)['CONFIG'];
      $this->omnisCGI = $devLib->getEnv_config('OMNIS', 'codice', 'OMNIS_CGI', false)['CONFIG'];
      $this->defaultLibrary = $devLib->getEnv_config('OMNIS', 'codice', 'DEFAULT_LIBRARY', false)['CONFIG'];
      $this->remoteTask = $devLib->getEnv_config('OMNIS', 'codice', 'REMOTE_TASK', false)['CONFIG'];
      $this->remoteTaskInt = $devLib->getEnv_config('OMNIS', 'codice', 'REMOTE_TASK_INT', false)['CONFIG'];
      $this->parHexFormat = $devLib->getEnv_config('OMNIS', 'codice', 'PAR_HEX_FORMAT', false)['CONFIG'];
      }
     */

    public function setParameters($parameters = array()) {
        $this->protocol = $parameters['protocol'];
        $this->webServerUrl = $parameters['webServerUrl'];
        $this->appServerUrl = $parameters['appServerUrl'];
        $this->omnisCGI = $parameters['omnisCGI'];
        $this->defaultLibrary = $parameters['defaultLibrary'];
        $this->remoteTask = $parameters['remoteTask'];
        $this->remoteTaskInt = $parameters['remoteTaskInt'];
        $this->parHexFormat = $parameters['parHexFormat'];
    }

    public function setParametersFromJsonString($json) {
        try {
            $parameters = json_decode($json, true);
            if (is_array($parameters)) {
                $this->setParameters($parameters);
            } else {
                return false;
            }
            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    public function getRestClient() {
        return $this->restClient;
    }

    public function getProtocol() {
        return $this->protocol;
    }

    public function getWebServerUrl() {
        return $this->webServerUrl;
    }

    public function getAppServerUrl() {
        return $this->appServerUrl;
    }

    public function getOmnisCGI() {
        return $this->omnisCGI;
    }

    public function getDefaultLibrary() {
        return $this->defaultLibrary;
    }

    public function getRemoteTask() {
        return $this->remoteTask;
    }

    public function getRemoteTaskInt() {
        return $this->remoteTaskInt;
    }

    public function getParHexFormat() {
        return $this->parHexFormat;
    }

    public function setRestClient($restClient) {
        $this->restClient = $restClient;
    }

    public function setProtocol($protocol) {
        $this->protocol = $protocol;
    }

    public function setWebServerUrl($webServerUrl) {
        $this->webServerUrl = $webServerUrl;
    }

    public function setAppServerUrl($appServerUrl) {
        $this->appServerUrl = $appServerUrl;
    }

    public function setOmnisCGI($omnisCGI) {
        $this->omnisCGI = $omnisCGI;
    }

    public function setDefaultLibrary($defaultLibrary) {
        $this->defaultLibrary = $defaultLibrary;
    }

    public function setRemoteTask($remoteTask) {
        $this->remoteTask = $remoteTask;
    }

    public function setRemoteTaskInt($remoteTaskInt) {
        $this->remoteTaskInt = $remoteTaskInt;
    }

    public function setParHexFormat($parHexFormat) {
        $this->parHexFormat = $parHexFormat;
    }

    function getErrCode() {
        return $this->errCode;
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    function getHttpStatus() {
        return $this->httpStatus;
    }

    function setHttpStatus($httpStatus) {
        $this->httpStatus = $httpStatus;
    }

}

?>