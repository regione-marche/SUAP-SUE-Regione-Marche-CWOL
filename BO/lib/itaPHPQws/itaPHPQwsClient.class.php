<?php

/**
 *
 * Classe per collegamento rest service
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPQws
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2019 Italsoft srl
 * @license
 * @version    13.02.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';

class itaPHPQwsClient {

    private $endpoint;
    private $password;
    private $timeout = 2400;
    private $errMessage;

    function getEndpoint() {
        return $this->endpoint;
    }

    function setEndpoint($endpoint) {
        $this->endpoint = $endpoint;
    }

    function getPassword() {
        return $this->codiceOrganigramma;
    }

    function setPassword($pwd) {
        $this->password = $pwd;
    }

    function getTimeout() {
        return $this->timeout;
    }

    function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function sendRequest($resource, $data) {

        $url = $this->endpoint . $resource;
        $itaRestClient = new itaRestClient();
        $itaRestClient->setDebugLevel(9);
        if (!$itaRestClient->post($url, false, array(), $data, 'application/json')) {
            Out::msgInfo("debug", print_r($itaRestClient->getDebug(), true));
            $this->setErrMessage("Errore nella richiesta.");
            return false;
        }
        return $itaRestClient->getResult();
    }

    public function getCatasto($param) {
        $request['alias'] = $param["alias"];
        $request['pwd'] = sha1($this->password);
        $request['parametri'] = $param['parametri'];
        return $this->sendRequest('/Get_Dati', json_encode($request));
    }

}
