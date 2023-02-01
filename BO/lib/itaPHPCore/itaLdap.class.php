<?php

require_once ITA_LIB_PATH . '/itaPHPLdap/itaLdapAuthenticator.class.php';   
require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

/**
 *
 * Interfaccia con LDAP
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
class itaLdap {
    
    /**
     * Restituisce oggetto authenticator per LDAP
     * @param array $parameters Parametri di configurazione
     * @return Oggetto authenticator per LDAP
     */
    public static function getLdapAuthenticator($parameters = array()) {
        if (!$parameters) {
            $parameters = self::getEnvParameters();
        }
        if (!$parameters) {
            return false;
        }
        return new itaLdapAuthenticator($parameters['LdapHost'], $parameters['LdapPort'], $parameters['LdapBaseDN']);
    }
    
    private static function getEnvParameters() {
        $devLib = new devLib();
        $configHost = $devLib->getEnv_config('LDAP', 'codice', 'LDAP_HOST', false);
        $configPort = $devLib->getEnv_config('LDAP', 'codice', 'LDAP_PORT', false);
        $configBaseDN = $devLib->getEnv_config('LDAP', 'codice', 'LDAP_BASE_DN', false);
        $parameters['LdapHost'] = $configHost['CONFIG'];
        $parameters['LdapPort'] = $configPort['CONFIG'];
        $parameters['LdapBaseDN'] = $configBaseDN['CONFIG'];
        return $parameters;
    }
    
}
