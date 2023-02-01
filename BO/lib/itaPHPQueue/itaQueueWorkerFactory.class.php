<?php

/**
 *
 * Factory per creazione oggetto QueueWorker
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPQueue
 * @author     Biagioli/Pergolini
 * @copyright  
 * @license
 * @version    04.04.2017
 * @link
 * @see
 * 
 */
class itaQueueWorkerFactory {
    
    private static $workerTypes = array(
        1 => 'itaQueueWorkerANPR',
        2 => 'itaQueueWorkerPrinter'
    );
    
    /**
     * Restituisce oggetto QueueWorker specifico
     * @param string $type Tipo
     * @return boolean|\className
     */
    public static function getWorker($type) {        
        try {
            $className = self::$workerTypes[$type];
            require_once(ITA_BASE_PATH . "/lib/itaPHPQueue/workers/$className.class.php");
            $worker = new $className();    
            $worker->setMessageType($type); 
        } catch (Exception $ex) {
            return false;
        }
        return $worker;
    }
    
}
