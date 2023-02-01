<?php

/**
 *
 * Factory per client Docer
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPDocer
 * @author     
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    19.10.2017
 * @link
 * @see
 * @since
 * */
class itaDocerClientFactory {

    const CLASSE_PARAMETRI_CONNESSSIONE = "DOCERWSCONNECTION";

    private static $dizionario_parametri_connessione = array(
        'DOCERWSENDPOINTIDENT',
        'DOCERWSENDPOINTGESDOC',
        'DOCERWSCONNECTIONTIMEOUT',
        'DOCERWSRESPONSETIMEOUT',
        'DOCERWSDEBUGLEVEL',
        'DOCERWSVERSION'
    );
    
    /**
     * Restituisce servizio gestione documentale di DocER
     * @param array $params
     * @return \class itaDocerGestDocClientInterface
     */
    public static function getIdentClient($params = array()) {
        if (!$params) {
            $params = self::readConfig();
        }
        require_once(ITA_LIB_PATH . '/itaPHPDocer/itaDocerIdentClient' . $params['DOCERWSVERSION'] . '.class.php');
        $class = 'itaDocerIdentClient' . $params['DOCERWSVERSION'];
        $client = new $class;
        $client->setWebservicesEndPoint($params['DOCERWSENDPOINTIDENT']);
        $client->setConnectionTimeout($params['DOCERWSCONNECTIONTIMEOUT']);
        $client->setResponseTimeout($params['DOCERWSCONNECTIONTIMEOUT']);
        $client->setDebugLevel($params['DOCERWSDEBUGLEVEL']);
        $client->setVersion($params['DOCERWSVERSION']);
        return $client;
    }    
    
    /**
     * Restituisce servizio gestione documentale di DocER
     * @param array $params
     * @return \class itaDocerGestDocClientInterface
     */
    public static function getGestDocClient($params = array()) {
        if (!$params) {
            $params = self::readConfig();
        }
        require_once(ITA_LIB_PATH . '/itaPHPDocer/itaDocerGestDocClient' . $params['DOCERWSVERSION'] . '.class.php');
        $class = 'itaDocerGestDocClient' . $params['DOCERWSVERSION'];
        $client = new $class;
        $client->setWebservicesEndPoint($params['DOCERWSENDPOINTGESDOC']);
        $client->setConnectionTimeout($params['DOCERWSCONNECTIONTIMEOUT']);
        $client->setResponseTimeout($params['DOCERWSCONNECTIONTIMEOUT']);
        $client->setDebugLevel($params['DOCERWSDEBUGLEVEL']);
        $client->setVersion($params['DOCERWSVERSION']);
        return $client;
    }    
    
    private static function readConfig() {
        $params = array();
        $devLib = new devLib();
        foreach (self::$dizionario_parametri_connessione as $chiave) {
            $param_rec = $devLib->getEnv_config(self::CLASSE_PARAMETRI_CONNESSSIONE, 'codice', $chiave, false);            
            if ($param_rec) {
                $params[$chiave] = $param_rec['CONFIG'];
            }
        }
        return $params;
    }

}
