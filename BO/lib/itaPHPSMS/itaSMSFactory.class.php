<?php

/**
 *
 * Factory per creazione SDK SMS
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPCore
 * @author     Biagioli/Moscioni
 * @copyright  
 * @license
 * @version    30.03.2017
 * @link
 * @see
 * 
 */
class itaSMSFactory {

    static private $sdkTypes = array(
        'ArubaRsSdk' => 'itaClientArubaRsSdk'
    );

    /**
     * Restituisce il client specifico
     * @param array $parameters Parametri di configurazione (se non specificati, li legge da config.ini)
     * @return boolean|\class Oggetto client specifico
     */
    public static function getClient($parameters = array()) {
        $SMSClient = App::getConf("itaSMS.SMS_client");
        if ($SMSClient == null) {
            $SMSClient = "ArubaRsSdk";
        }
//        if (!$parameters) {
//            return false;
//        }

        $className = self::$sdkTypes[$SMSClient];
        require_once(ITA_LIB_PATH . "/itaPHPSMS/$className.class.php");
        try {
            $objClient = new $className($parameters);
        } catch (Exception $ex) {
            return false;
        }
        return $objClient;
    }

}
