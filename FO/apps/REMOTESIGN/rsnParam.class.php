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
class rsnParam {

    private $uri;
    private $nameSpace;
    private $timeout;
    private $debugLevel;
    private $defaultOptAuth;

    function __construct() {
        $this->getParamFromDB();
    }

    public function getUri() {
        return $this->uri;
    }

    public function setUri($uri) {
        $this->uri = $uri;
    }

    public function getNameSpace() {
        return $this->nameSpace;
    }

    public function setNameSpace($nameSpace) {
        $this->nameSpace = $nameSpace;
    }

    public function getTimeout() {
        return $this->timeout;
    }

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    public function getDebugLevel() {
        return $this->debugLevel;
    }

    public function setDebugLevel($debugLevel) {
        $this->debugLevel = $debugLevel;
    }
    function getDefaultOptAuth() {
        return $this->defaultOptAuth;
    }

    function setDefaultOptAuth($defaultOptAuth) {
        $this->defaultOptAuth = $defaultOptAuth;
    }

        public function getParamFromDB() {
        require_once './apps/Sviluppo/devLib.class.php';
        $devLib = new devLib();
        $this->setUri($devLib->getEnv_config('FIRMAREMOTAARSS', 'codice', 'WSARSSENDPOINT', false));
        $this->setNameSpace($devLib->getEnv_config('FIRMAREMOTAARSS', 'codice', 'WSARSSNAMESPACE', false));
        $this->setTimeout($devLib->getEnv_config('FIRMAREMOTAARSS', 'codice', 'WSARSSTIMEOUT', false));
        $this->setDebugLevel($devLib->getEnv_config('FIRMAREMOTAARSS', 'codice', 'WSARRSCALLDEBUGLEVEL', false));
        $this->setDefaultOptAuth($devLib->getEnv_config('FIRMAREMOTAARSS', 'codice', 'DEFAULTOTPAUTH', false));
    }

}

?>
