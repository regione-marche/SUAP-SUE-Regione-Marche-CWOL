<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of macCosmariPesaClient
 *
 * @author Paolo
 */
require_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';

class itaEcosEOneClient {

    private $user;
    private $pwd;
    private $url;
    private $errCode;
    private $errMessage;

    public function __construct($parameters) {
        $this->user = $parameters['users'];
        $this->pwd = $parameters['pwd'];
        $this->url = $parameters['url'];
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

    function getUser() {
        return $this->user;
    }

    function getPwd() {
        return $this->pwd;
    }

    function getDsn() {
        return $this->dsn;
    }

    function getUrl() {
        return $this->url;
    }

    function setUser($user) {
        $this->user = $user;
    }

    function setPwd($pwd) {
        $this->pwd = $pwd;
    }

    function setDsn($dsn) {
        $this->dsn = $dsn;
    }

    function setUrl($url) {
        $this->url = $url;
    }

    public static function leggiParametri() {
        require_once (ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php');
        $result = array();
        $devLib = new devLib();
        $result['user'] = $devLib->getEnv_config('ECOSREST', 'codice', 'USERECO', false);
        $result['pwd'] = $devLib->getEnv_config('ECOSREST', 'codice', 'PWDECO', false);
        $result['dsn'] = $devLib->getEnv_config('ECOSREST', 'codice', 'DSNECO', false);
        $url = $devLib->getEnv_config('ECOSREST', 'codice', 'RESTURLECO', false);
        $result['url'] = $url['CONFIG'];
        return $result;
    }

    public function getPesate($daData, $aData) {
        $restClient = new itaRestClient();
        $restClient->setTimeout(10);
        $restClient->setCurlopt_url($this->url);
        $path = "/ecosEOneLeggiPesa";
        $param = array(
            "DAL" => $daData,
            "AL" => $aData
        );
        if ($restClient->get($path, $param)) {
            if ($restClient->getHttpStatus() !== 200) {
                $this->setErrCode(-1);
                $this->setErrMessage("Chiamata non riuscita Status:" . $restClient->getHttpStatus());
                return false;
            }
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage($restClient->getErrMessage());
            return false;
        }
        return $restClient->getResult();
    }

}
