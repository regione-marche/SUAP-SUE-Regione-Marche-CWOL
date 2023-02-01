<?php

/**
 *
 * Integrazione con LDAP
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPDocercity
 * @author     Massimo Biagioli <m.biagioli@palinformatica.it>
 * @copyright  
 * @license
 * @version    30.09.2016
 * @link
 * @see
 * 
 */
class itaLdapAuthenticator {

    private $host;
    private $port;
    private $baseDN;
    private $ldap;
    private $protocolVersion;
    private $lastErrorMessage;

    /**
     * Costruttore
     * @param string $host Host 
     * @param int $port Porta
     * @param string $baseDN Stringa DN di partenza
     * @param int $protocolVersion Protocollo LDAP
     */
    public function __construct($host, $port, $baseDN, $protocolVersion = 3) {
        $this->host = $host;
        $this->port = $port;
        $this->baseDN = $baseDN;
        $this->protocolVersion = $protocolVersion;
        $this->lastErrorMessage = "";
    }

    private function connect() {
        if ($this->ldap) {
            return true;
        }

        $this->ldap = ldap_connect($this->host, $this->port);
        if (!$this->ldap) {
            $this->lastErrorMessage = ldap_error($this->ldap);
            return false;
        }

        return true;
    }

    /**
     * Effettua autenticazione tramite LDAP
     * @param string $username
     * @param string $password
     * @return true se autenticato, false in caso di errore di autenticazione
     */
    public function authenticate($username, $password) {
        $this->lastErrorMessage = "";

        if (!$username) {
            $this->lastErrorMessage = 'Parametro \'username\' mancante.';
            return false;
        }

        if (!$password) {
            $this->lastErrorMessage = 'Parametro \'password\' mancante.';
            return false;
        }

        if (!$this->connect()) {
            return false;
        }

        ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, $this->protocolVersion);

        if (strpos($username, '=')) {            
            $dn = strpos(strtolower($username), 'dc=') === false ? "$username," . $this->baseDN : $username;
        } else {
            $dn = "uid=$username," . $this->baseDN;
        }

        $result = ldap_bind($this->ldap, $dn, utf8_encode($password));

        if (!$result) {
            $this->lastErrorMessage = ldap_error($this->ldap);
        }

        return $result;
    }

    /**
     * Effettua una ricerca LDAP. Per i parametri, vedere la
     * funzione ldap_search.
     * http://php.net/manual/en/function.ldap-search.php
     * 
     * @param string $filter Filtri della ricerca.
     * @param array $attributes Array di attributi da tornare.
     * @param int $attrsonly Flag per ritornare gli attributi senza rispettivi valori.
     * @param int $sizelimit Limite di risultati.
     * @param int $timelimit Limite di tempo per la query in secondi.
     * @param int $deref Flag per la gestione degli alias.
     * @return mixed Risultati della ricerca o <b>false</b> in caso di errore.
     */
    public function search($filter, $attributes = array(), $attrsonly = 0, $sizelimit = 0, $timelimit = 0, $deref = LDAP_DEREF_NEVER) {
        $this->lastErrorMessage = '';

        if (!$this->connect()) {
            return false;
        }

        ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, $this->protocolVersion);

        $sr = ldap_search($this->ldap, $this->baseDN, $filter, $attributes, $attrsonly, $sizelimit, $timelimit, $deref);
        if (!$sr) {
            $this->lastErrorMessage = ldap_error($this->ldap);
            return false;
        }

        $results = ldap_get_entries($this->ldap, $sr);

        return $results;
    }

    public function getHost() {
        return $this->host;
    }

    public function getPort() {
        return $this->port;
    }

    public function getBaseDN() {
        return $this->baseDN;
    }

    public function setHost($host) {
        $this->host = $host;
    }

    public function setPort($port) {
        $this->port = $port;
    }

    public function setBaseDN($baseDN) {
        $this->baseDN = $baseDN;
    }

    public function getLastErrorMessage() {
        return $this->lastErrorMessage;
    }

    public function setLastErrorMessage($lastErrorMessage) {
        $this->lastErrorMessage = $lastErrorMessage;
    }

}
