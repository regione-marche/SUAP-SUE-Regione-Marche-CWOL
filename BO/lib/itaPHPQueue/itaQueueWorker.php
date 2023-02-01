<?php

/**
 *
 * Interfaccia QueueWorker
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
interface itaQueueWorker {

    /**
     * Esecuzione di un messaggio
     * @param object $message Messaggio da elaborare
     * @return esito (da gestire all'interno del worker specifico)
     */
    public function execute($message);

    /**
     * Strategy per esecuzione di un messaggio singolo:
     *  1 = Reinserimento in coda
     *  2 = Cancellazione
     * @return int Strategy
     */
    public function getMessageExecuteStrategy();

    /**
     * Restituisce tipo di messaggio
     * @return tipo di messaggio
     */
    public function getMessageType();

    /**
     * Imposta tipo di messaggio
     * @param int $msgType Tipo di messaggio
     */
    public function setMessageType($msgType);

    /**
     * Numero massimo di tentativi in caso di errore 
     * @param object $message mesaggio in elaborazione
     */
    public function getMaxRetries($message);

    /**
     * @return restituisce l'oggetto manager 
     */
    public function getQueueManager();

    /**
     * @param object $queueManager l'oggetto queueManager
     */
    public function setQueueManager($queueManager);
}
