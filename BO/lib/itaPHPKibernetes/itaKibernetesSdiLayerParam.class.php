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
class itaKibernetesSdiLayerParam {

    private $nameSpace = "";
    private $uri = "";
    private $wsdlUri = "";
    private $timeout = 2400;
    private $debugLevel = 0;
    private $flAbilitato = 1;
    private $utente = "";
    private $password = "";
    private $istat = "";

    function __construct() {
        $this->getParamFromDB();
    }

    public function getUri() {
        return $this->uri;
    }

    public function setUri($uri) {
        $this->uri = $uri;
    }

    public function getWsdlUri() {
        return $this->wsdlUri;
    }

    public function setWsdlUri($wsdlUri) {
        $this->wsdlUri = $wsdlUri;
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

    public function getUtente() {
        return $this->utente;
    }

    public function setUtente($utente) {
        $this->utente = $utente;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getIstat() {
        return $this->istat;
    }

    public function setIstat($istat) {
        $this->istat = $istat;
    }

    public function getParamFromDB() {
        require_once './apps/Sviluppo/devLib.class.php';
        $devLib = new devLib();
        $global_params = $devLib->getEnv_config_global_ini('PROWS_KIBERNETESSDILAYER');
        if ($global_params) {
            $this->setUri($global_params['PROWSKSL_URI']['CONFIG']);
            $this->setWsdlUri($global_params['PROWSKSL_WSDLURI']['CONFIG']);
            $this->setNameSpace($global_params['PROWSKSL_NAMESPACE']['CONFIG']);
            $this->setTimeout($global_params['PROWSKSL_TIMEOUT']['CONFIG']);
            $this->setTimeout($global_params['PROWSKSL_TIMEOUT']['CONFIG']);
            $this->setUtente($global_params['PROWSKSL_UTENTE']['CONFIG']);
            $this->setPassword($global_params['PROWSKSL_PASSWORD']['CONFIG']);
            $this->setIstat($global_params['PROWSKSL_ISTAT']['CONFIG']);
            // UTENTE,PASSWORD,ISTAT NECESSARI ? 
        } else {
            $params = $devLib->getEnv_config('PROWS_KIBERNETESSDILAYER', 'codice', 'PROWSKSL_URI', false);
            $this->setUri($params['CONFIG']);
            $params = $devLib->getEnv_config('PROWS_KIBERNETESSDILAYER', 'codice', 'PROWSKSL_WSDLURI', false);
            $this->setWsdlUri($params['CONFIG']);
            $params = $devLib->getEnv_config('PROWS_KIBERNETESSDILAYER', 'codice', 'PROWSKSL_NAMESPACE', false);
            $this->setNameSpace($params['CONFIG']);
            $params = $devLib->getEnv_config('PROWS_KIBERNETESSDILAYER', 'codice', 'PROWSKSL_TIMEOUT', false);
            $this->setTimeout($params['CONFIG']);
            $params = $devLib->getEnv_config('PROWS_KIBERNETESSDILAYER', 'codice', 'PROWSKSL_UTENTE', false);
            $this->setUtente($params['CONFIG']);
            $params = $devLib->getEnv_config('PROWS_KIBERNETESSDILAYER', 'codice', 'PROWSKSL_PASSWORD', false);
            $this->setPassword($params['CONFIG']);
            $params = $devLib->getEnv_config('PROWS_KIBERNETESSDILAYER', 'codice', 'PROWSKSL_ISTAT', false);
            $this->setIstat($params['CONFIG']);
        }
    }

}

?>
