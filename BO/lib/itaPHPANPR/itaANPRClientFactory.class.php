<?php
require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClient1000.class.php');
require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClient2000.class.php');
require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClient3000.class.php');
require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClient4000.class.php');
require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClient5000.class.php');
require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClient6001.class.php');
require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClient7001.class.php');
require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClientA000.class.php');
require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClientN000.class.php');
require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClientS001.class.php');

/**
 *
 * Factory per client ANPR
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPDocer
 * @author     Massimo Biagioli
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    28.05.2018
 * @link
 * @see
 * @since
 * */
class itaANPRClientFactory {
    
    private static $serviceMap = array(
        '1000' => 'itaANPRClient1000',
        '2000' => 'itaANPRClient2000',
        '3000' => 'itaANPRClient3000',
        '4000' => 'itaANPRClient4000',
        '5000' => 'itaANPRClient5000',
        '6001' => 'itaANPRClient6001',
        '7001' => 'itaANPRClient7001',
        'A000' => 'itaANPRClientA000',
        'N000' => 'itaANPRClientN000',
        'S001' => 'itaANPRClientS001',
    );
    
    /**
     * Restituisce il client di ws specifico per il servizio richiesto
     * @param string $type Tipo servizio
     * @return Classe gestione servizi specifica se tipo servizio esiste, altrimenti false
     */
    public static function getClient($type) {
        if (!array_key_exists($type, self::$serviceMap)) {
            return false;
        }
        $className = self::$serviceMap[$type];
        $instance = new $className();                
        return $instance;
    }
    
}
