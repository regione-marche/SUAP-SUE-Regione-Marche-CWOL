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
class itaARSSParam {

    private $nameSpace = "http://arubasignservice.arubapec.it/";
    private $uri = "https://pteasb.actalis.it/ArubaSignService/ArubaSignService";
    private $paperTokenBackend = "";
    private $defaultOtpAuth = "";
    private $timeout = 2400;
    private $debugLevel = 0;
    private $flAbilitato = 1;

    function __construct() {
        $this->getParamFromDB();
    }

    public function getUri() {
        return $this->uri;
    }

    public function setUri($uri) {
        $this->uri = $uri;
    }

    public function getPaperTokenBackend() {
        return $this->paperTokenBackend;
    }

    public function setPaperTokenBackend($paperTokenBackend) {
        $this->paperTokenBackend = $paperTokenBackend;
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

    public function getFlAbilitato() {
        return $this->flAbilitato;
    }

    public function setFlAbilitato($flAbilitato) {
        $this->flAbilitato = $flAbilitato;
    }

    function getDefaultOtpAuth() {
        return $this->defaultOtpAuth;
    }

    function setDefaultOtpAuth($defaultOtpAuth) {
        $this->defaultOtpAuth = $defaultOtpAuth;
    }

    public function getParamFromDB() {
        require_once './apps/Sviluppo/devLib.class.php';
        $devLib = new devLib();
        $global_params = $devLib->getEnv_config_global_ini('FIRMAREMOTAARSS');
        if ($global_params) {
            $this->setUri($global_params['WSARSSENDPOINT']['CONFIG']);
            $this->setNameSpace($global_params['WSARSSNAMESPACE']['CONFIG']);
            $this->setTimeout($global_params['WSARSSTIMEOUT']['CONFIG']);
            $this->setDebugLevel($global_params['WSARRSCALLDEBUGLEVEL']['CONFIG']);
            $this->setFlAbilitato($global_params['WSARRSABILITATO']['CONFIG']);
            $this->setPaperTokenBackend($global_params['PAPERTOKENBACKEND']['CONFIG']);
        } else {
            $params = $devLib->getEnv_config('FIRMAREMOTAARSS', 'codice', 'WSARSSENDPOINT', false);
            $this->setUri($params['CONFIG']);
            $params = $devLib->getEnv_config('FIRMAREMOTAARSS', 'codice', 'WSARSSNAMESPACE', false);
            $this->setNameSpace($params['CONFIG']);
            $params = $devLib->getEnv_config('FIRMAREMOTAARSS', 'codice', 'WSARSSTIMEOUT', false);
            $this->setTimeout($params['CONFIG']);
            $params = $devLib->getEnv_config('FIRMAREMOTAARSS', 'codice', 'WSARRSCALLDEBUGLEVEL', false);
            $this->setDebugLevel($params['CONFIG']);
            $params = $devLib->getEnv_config('FIRMAREMOTAARSS', 'codice', 'WSARRSABILITATO', false);
            $this->setFlAbilitato($params['CONFIG']);
            $params = $devLib->getEnv_config('FIRMAREMOTAARSS', 'codice', 'PAPERTOKENBACKEND', false);
            $this->setPaperTokenBackend($params['CONFIG']);
            $params = $devLib->getEnv_config('FIRMAREMOTAARSS', 'codice', 'DEFAULTOTPAUTH', false);
            $this->setDefaultOtpAuth($params['CONFIG']);
        }
    }

}

?>
