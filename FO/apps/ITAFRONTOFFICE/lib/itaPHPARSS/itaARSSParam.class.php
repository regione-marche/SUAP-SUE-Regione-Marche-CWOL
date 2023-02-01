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
        try {
            $PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
        } catch (Exception $e) {
            return false;
        }
        
        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLib.class.php');
        $praLib = new praLib();
        $anapar_rec1 = $praLib->GetAnapar("ENDPOINT_ARSS", "parkey", $PRAM_DB, false);
        $anapar_rec2 = $praLib->GetAnapar("NAMESPACE_ARSS", "parkey", $PRAM_DB, false);
        $anapar_rec3 = $praLib->GetAnapar("TIMEOUT_ARSS", "parkey", $PRAM_DB, false);
        $anapar_rec4 = $praLib->GetAnapar("DEBUGLEVEL_ARSS", "parkey", $PRAM_DB, false);
        $anapar_rec5 = $praLib->GetAnapar("DECODEURLPAPERTOKEN_ARSS", "parkey", $PRAM_DB, false);
        $anapar_rec6 = $praLib->GetAnapar("DOMINIODEFAULT_ARSS", "parkey", $PRAM_DB, false);


        $this->setUri($anapar_rec1['PARVAL']);
        $this->setNameSpace($anapar_rec2['PARVAL']);
        $this->setTimeout($anapar_rec3['PARVAL']);
        $this->setDebugLevel($anapar_rec4['PARVAL']);
        $this->setDefaultOtpAuth($anapar_rec6['PARVAL']);
        $this->setPaperTokenBackend($anapar_rec5['PARVAL']);
        //$this->setFlAbilitato();
    }

}

?>
