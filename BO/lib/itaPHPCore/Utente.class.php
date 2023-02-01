<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Utenteclass
 *
 * @author utente
 */
class Utente {

    const NON_AUTENTICATO = 0;
    const IN_CORSO = 1;
    const IN_CORSO_ADMIN = 101;
    const AUTENTICATO_ADMIN = 102;
    const AUTENTICATO = 2;
    const IN_COSRO_JNET = 1001;
    const AUTENTICATO_JNET = 1002;
    const RET_CODE_OK = 0;
    const RET_CODE_FAILED_VALIDATION = 1;
    const RET_CODE_EMPTY_USER = 2;
    const RET_CODE_INVALID_TOKEN = 2;
    const KEY_CONNECTION_ARRAY = "CONNECTIONS";

    private $stato;
    private $dati = array();
    private $message;
    private $retCode;
    private $exipredPassword;

    function __construct() {
        $this->setStato(self::NON_AUTENTICATO);
    }

    /**
     *
     */
    function getStato() {
        return $this->stato;
    }

    /**
     *
     */
    function setStato($stato) {
        $this->stato = $stato;
    }

    /**
     *
     */
    function setKey($key, $value) {
        $this->dati[$key] = $value;
    }

    /**
     *
     * @param <type> $key 
     */
    function removeKey($key) {
        unset($this->dati[$key]);
    }

    /**
     *
     */
    function getKey($key, $value = '') {
        if (key_exists($key, $this->dati)) {
            return $this->dati[$key];
        } else {
            return null;
        }
    }

    /**
     * Restituice l'id Utente
     * 
     * @return type 
     */
    function getIdUtente() {
        return $this->getKey('idUtente');
    }

    private function setReturnCode($returnCode) {
        $this->returnCode = $returnCode;
    }

    public function getReturnCode() {
        return $this->returnCode;
    }

    private function setMessage($message) {
        $this->message = $message;
    }

    public function getMessage() {
        return $this->message;
    }

    private function setExpiredPassword($expired = true) {
        $this->expiredPassword = $expired;
    }

    public function getExpiredPassword() {
        return $this->expiredPassword;
    }

    /**
     *
     */
    function login($organization, $utente, $password) {
        if ($utente == '') {
            $this->setReturnCode(self::RET_CODE_EMPTY_USER);
            $this->setMessage("Accesso non Valido. Inserire il campo utente!");
            return false;
        }
        $ret_verpass = ita_verpass($organization, $utente, $password);
        if ($ret_verpass['status'] != 0 && $ret_verpass['status'] != '-99') {
            $this->setReturnCode(self::RET_CODE_FAILED_VALIDATION);
            $this->setMessage($ret_verpass['messaggio']);
            return false;
        }
        $this->setExpiredPassword(false);
        if ($ret_verpass['status'] == '-99') {
            $this->setExpiredPassword();
        }
        $this->setKey('ditta', $organization);
        $this->setKey('organization', $organization);
        $this->setKey('idUtente', $ret_verpass['codiceUtente']);
        $this->setKey('nomeUtente', $utente);
        $this->setKey('lingua', 'it');

        $ret_token = ita_token('', $this->getKey('organization'), $this->getKey('idUtente'), 1);
        if ($ret_token['status'] == '0') {
            $token = $ret_token['token'];
        } else {
            $this->setReturnCode(self::RET_CODE_INVALID_TOKEN);
            $this->setMessage($ret_token['messaggio']);
            return false;
        }
        $this->setStato(self::AUTENTICATO);
        $this->setKey('TOKEN', $token);
        $this->setKey('TOKCOD', ita_token($token, $organization, 0, 20));
        $this->setKey('DataLavoro', date('Ymd'));

        App::createPrivPath();
        itaLib::createAppsTempPath();

        return true;
    }

    /**
     *
     */
    function logout() {
        $this->dati = array();
        $this->setStato(self::NON_AUTENTICATO);
        $_POST = array();
        Out::codice('token="";');
    }

}

?>
