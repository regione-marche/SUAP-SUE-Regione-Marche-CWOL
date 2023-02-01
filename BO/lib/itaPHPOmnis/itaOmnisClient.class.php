<?php

require_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaLib.class.php';

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
    private $owsEnabled;
    private $errCode;
    private $errMessage;
    private $httpStatus;
    private $xmlResponse;

    public function __construct() {
        $this->restClient = new itaRestClient();
    }

    public function callExecute($objectName, $methodName, $methodArgs, $lbs = null, $interattivo = false) {
        $this->resetStatusError();
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
            if (!is_array($methodArgs[$i])) {
                $nodeParams->addChild('COL' . ($i + 1), utf8_encode($methodArgs[$i]));
            } else {
                $nodeCol = $nodeParams->addChild('COL' . ($i + 1));
                $this->buildXmlParametersForExecuteParams($nodeCol, $methodArgs[$i]);
            }
        }
        return $xml->asXML();
    }

    private function buildXmlParametersForExecuteParams(&$nodeParams, $methodArg) {
        foreach ($methodArg as $mak => $mav) {
            if (!is_array($mav)) {
                $nodeParams->addChild($mak, $mav);
            } else {
                $nodeKey = $nodeParams->addChild('ROW' . ($mak + 1));
                $this->buildXmlParametersForExecuteParams($nodeKey, $mav);
            }
        }
    }

    private function call($args, $lbs, $interattivo = false, $rawResponse = false) {
        if (empty($this->protocol)) {
            $this->setParametersStatic();
        }

        // Se Omnis non abilitato, esce
        if (!$this->getOwsEnabled()) {
            return null;
        }

        $url = $this->protocol . '://' . $this->webServerUrl;
        $par = $this->parHexFormat ? array_shift(unpack('H*', $args)) : $args;
        $data = array(
            'OmnisServer' => $this->appServerUrl,
            'OmnisLibrary' => $lbs,
            'OmnisClass' => $interattivo ? $this->remoteTaskInt : $this->remoteTask,
            'Debug' => 0,
            'par' => $par,
        );

        $this->restClient->setCurlopt_url($url);
        if ($this->restClient->post($this->omnisCGI, $data)) {
            $xmlstr = $this->restClient->getResult();
            $this->setXmlResponse($xmlstr);
            //$result = new SimpleXMLElement(trim($xmlstr));
            $result = trim($xmlstr);
            //Verifico se voglio la risposta formattata          
            $temp = itaLib::stringXmlToArray($result);
            if (!isSet($temp['RESULT']['EXITCODE']) || $temp['RESULT']['EXITCODE'] == "N") {
                $this->setErrCode(-1);
                $this->setErrMessage($temp['RESULT']['MESSAGE']);
            }
            if ($rawResponse == false) {
                $result = $temp;
            }
        } else {
            //Errore del restClient 
            $this->setErrCode($this->restClient->getErrCode());
            $this->setErrMessage($this->restClient->getErrMessage());
            $result = null;
        }
        $this->setHttpStatus($this->restClient->getHttpStatus());
        return $result;
    }

    public function setParametersStatic() {
        require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

        $devLib = new devLib();
        $configProtocol = $devLib->getEnv_config('OMNIS', 'codice', 'PROTOCOL', false);
        $configWsUrl = $devLib->getEnv_config('OMNIS', 'codice', 'WEB_SERVER_URL', false);
        $configAppServerUrl = $devLib->getEnv_config('OMNIS', 'codice', 'APP_SERVER_URL', false);
        $configOmnisCGI = $devLib->getEnv_config('OMNIS', 'codice', 'OMNIS_CGI', false);
        $configDefaultLib = $devLib->getEnv_config('OMNIS', 'codice', 'DEFAULT_LIBRARY', false);
        $configRemoteTask = $devLib->getEnv_config('OMNIS', 'codice', 'REMOTE_TASK', false);
        $configRemoteTaskInt = $devLib->getEnv_config('OMNIS', 'codice', 'REMOTE_TASK_INT', false);
        $configParHexFormat = $devLib->getEnv_config('OMNIS', 'codice', 'PAR_HEX_FORMAT', false);
        $owsEnabled = $devLib->getEnv_config('OMNIS', 'codice', 'OWS_ENABLED', false);
        $this->protocol = $configProtocol['CONFIG'];
        $this->webServerUrl = $configWsUrl['CONFIG'];
        $this->appServerUrl = $configAppServerUrl['CONFIG'];
        $this->omnisCGI = $configOmnisCGI['CONFIG'];
        $this->defaultLibrary = $configDefaultLib['CONFIG'];
        $this->remoteTask = $configRemoteTask['CONFIG'];
        $this->remoteTaskInt = $configRemoteTaskInt['CONFIG'];
        $this->parHexFormat = $configParHexFormat['CONFIG'];
        $this->owsEnabled = $owsEnabled['CONFIG'];
    }

    public function setParameters($parameters = array()) {
        $this->protocol = $parameters['protocol'];
        $this->webServerUrl = $parameters['webServerUrl'];
        $this->appServerUrl = $parameters['appServerUrl'];
        $this->omnisCGI = $parameters['omnisCGI'];
        $this->defaultLibrary = $parameters['defaultLibrary'];
        $this->remoteTask = $parameters['remoteTask'];
        $this->remoteTaskInt = $parameters['remoteTaskInt'];
        $this->parHexFormat = $parameters['parHexFormat'];
        $this->owsEnabled = $parameters['owsEnabled'];
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

    //Usare questo metodo quando da Omnis ci si aspetta una stampa di ritorno come un report
    public function getResponseFileFromOmnis($result, &$files) {
        if ($result['RESULT']['EXITCODE'] == 'S') {
            //Converto il file da esadecimale a binario
            $attachments = itaLib::hex2BinDecode($result['RESULT']['LIST']['ROW']['FILE']);
            $pathInfoResult = pathinfo($result['RESULT']['MESSAGE']);
            $fileName = str_replace(' ', '_', $pathInfoResult['basename']);
            $fileName = preg_replace('/^.*\\\s*/', '', $fileName);

            //Verifico che esistano le sottocartelle
            $pathDest = App::getPath('temporary.appsPath');
            if (!file_exists($pathDest)) {
                mkdir($pathDest, 0777, true);
            }
            $pathDest = App::getPath('temporary.appsPath') . '/STAMPE/';
            if (!file_exists($pathDest)) {
                mkdir($pathDest, 0777, true);
            }
            $pathDest = App::getPath('temporary.appsPath') . '/STAMPE/' . $fileName;

            //Creo il file
            $myfile = fopen($pathDest, "w");
            fwrite($myfile, $attachments);
            fclose($myfile);

            $files[count($files)] = array('NOME' => $pathDest);
            return true;
        } else {
            return false;
        }
    }

    //genera un xml contenente tutti i parametri passati. Lato omnis viene interpretato come una row.
    // serve quando si devono passare tanti parametri e non si vogliono passare singolarmente
    public function getXmlParams($params) {
        $xml = '<PARAMETRI>';
        foreach ($params as $key => $value) {
            $xml .= '<' . $key . '>' . $value . '</' . $key . '>';
        }
        $xml .= '</PARAMETRI>';
        return $xml;
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

    function getOwsEnabled() {
        return $this->owsEnabled;
    }

    function setOwsEnabled($owsEnabled) {
        $this->owsEnabled = $owsEnabled;
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

    public function resetStatusError() {
        $this->setErrcode(0);
        $this->setErrMessage('');
    }
    
    function getXmlResponse() {
        return $this->xmlResponse;
    }

    function setXmlResponse($xmlResponse) {
        $this->xmlResponse = $xmlResponse;
    }
}

?>