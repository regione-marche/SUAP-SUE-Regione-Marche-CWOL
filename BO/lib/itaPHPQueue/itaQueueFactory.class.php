<?php

/**
 *
 * Factory per creazione oggetto QueueManager
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPQueue
 * @author     Biagioli/Pergolini
 * @copyright  
 * @license
 * @version    31.03.2017
 * @link
 * @see
 * 
 */
class itaQueueFactory {
    
    private static $queueTypes = array(
        'cache' => 'itaQueueManagerCache'
        //'php' => 'itaQueueManagerPhp'
        //'rabbitmq' => 'itaQueueManagerRabbitMQ'
    );
    
    /**
     * Restituisce oggetto QueueManager specifico
     * @param string $type Tipo (se non specificato, lo legge dal config.ini)
     * @return boolean|\className
     */
    public static function getQueueManager($type = null) {
        if (!$type) {
            $type = Config::getConf('queue.queueType');
        }
        
        try {
            $className = self::$queueTypes[$type];
            require_once(ITA_BASE_PATH . "/lib/itaPHPQueue/$className.class.php");
            $instance = new $className();             
        } catch (Exception $ex) {
            return false;
        }
        return $instance;
    }    
    
}

?>