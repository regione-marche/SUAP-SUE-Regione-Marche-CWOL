<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rsnParam
 *
 * @author michele
 */
class itaTecutParam {

    private $nameSpaces = array();
    private $namespace = "";
    private $wsDelibere_uri = "";
    private $wsDelibere_wsdl = "";
    private $wsDetermine_uri = "";
    private $wsDetermine_wsdl = "";
    private $username = "";
    private $password = "";
    private $timeout = 2400;
    private $reponseTimeout = 2400;
    private $debugLevel = 0;

    function __construct($externalParams = array()) {
        if (count($externalParams) == 0) {
            $this->getParamFromDB();
        } else {
            $this->getParamFromArray($externalParams);
        }
    }

    public function getNameSpaces() {
        return $this->nameSpaces;
    }

    public function getNamespace() {
        return $this->namespace;
    }

    public function getWsDelibere_uri() {
        return $this->wsDelibere_uri;
    }

    public function getWsDelibere_wsdl() {
        return $this->wsDelibere_wsdl;
    }

    public function getWsDetermine_uri() {
        return $this->wsDetermine_uri;
    }

    public function getWsDetermine_wsdl() {
        return $this->wsDetermine_wsdl;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getTimeout() {
        return $this->timeout;
    }

    public function getReponseTimeout() {
        return $this->reponseTimeout;
    }

    public function getDebugLevel() {
        return $this->debugLevel;
    }

    public function setNameSpaces($nameSpaces) {
        $this->nameSpaces = $nameSpaces;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    public function setWsDelibere_uri($wsDelibere_uri) {
        $this->wsDelibere_uri = $wsDelibere_uri;
    }

    public function setWsDelibere_wsdl($wsDelibere_wsdl) {
        $this->wsDelibere_wsdl = $wsDelibere_wsdl;
    }

    public function setWsDetermine_uri($wsDetermine_uri) {
        $this->wsDetermine_uri = $wsDetermine_uri;
    }

    public function setWsDetermine_wsdl($wsDetermine_wsdl) {
        $this->wsDetermine_wsdl = $wsDetermine_wsdl;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    public function setReponseTimeout($reponseTimeout) {
        $this->reponseTimeout = $reponseTimeout;
    }

    public function setDebugLevel($debugLevel) {
        $this->debugLevel = $debugLevel;
    }

    private function getParamFromDB() {
        require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
        $devLib = new devLib();
        $params = $devLib->getEnv_config('WSTECUT', 'codice', 'WSDELIBEREENDPOINT', false);
        $this->setWsDelibere_uri($params['CONFIG']);
        $params = $devLib->getEnv_config('WSTECUT', 'codice', 'WSDELIBEREWSDL', false);
        $this->setWsDelibere_wsdl($params['CONFIG']);
        $params = $devLib->getEnv_config('WSTECUT', 'codice', 'WSDETERMINEENDPOINT', false);
        $this->setWsDetermine_uri($params['CONFIG']);
        $params = $devLib->getEnv_config('WSTECUT', 'codice', 'WSDETERMINEWSDL', false);
        $this->setWsDetermine_wsdl($params['CONFIG']);
        $params = $devLib->getEnv_config('WSTECUT', 'codice', 'WSUTENTE', false);
        $this->setUsername($params['CONFIG']);
        $params = $devLib->getEnv_config('WSTECUT', 'codice', 'WSPASSWORD', false);
        $this->setPassword($params['CONFIG']);
        $params = $devLib->getEnv_config('WSTECUT', 'codice', 'WSCONNECTTIMEOUT', false);
        $this->setTimeout($params['CONFIG']);
        $params = $devLib->getEnv_config('WSTECUT', 'codice', 'WSRESPONSETIMEOUT', false);
        $this->setReponseTimeout($params['CONFIG']);
        $params = $devLib->getEnv_config('WSTECUT', 'codice', 'WSLOGLEVEL', false);
        $this->setDebugLevel($params['CONFIG']);
    }
    private function getParamFromArray($params) {
        $this->setWsDelibere_uri($params['wsDelibere_uri']);
        $this->setWsDelibere_wsdl($params['wsDelibere_wsdl']);
        $this->setWsDetermine_uri($params['wsDetermine_uri']);
        $this->setWsDetermine_wsdl($params['wsDetermine_wsdl']);
        $this->setPassword($params['password']);
        $this->setTimeout($params['timeout']);
        $this->setReponseTimeout($params['reponseTimeout']);
        $this->setDebugLevel($params['debuglevel']);
    }

}
