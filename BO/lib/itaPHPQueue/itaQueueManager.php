<?php

/**
 *
 * Interfaccia per creazione oggetto QueueManager
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
interface itaQueueManager {

    /**
     * Crea una nuova coda
     * @param int $queueId Identificativo coda
     * @return bool true/false coda creata 
     */
    public function createQueue($queueId);

    /**
     * Distruggi coda
     * @param Int $queueId Identificativo della coda
     * @return esito (true/false)
     */
    public function destroyQueue($queueId);

    /**
     * Aggiunge un nuovo messaggio alla coda
     * @param Int $queueId Identificativo della coda
     * @param $message Messaggio
     * @return esito (true/false)
     */
    public function addMessage($queueId, $message);

    /**
     * Ritorna il messaggio da prelevare dalla coda
     * @param Int $queueId Identificativo della coda
     * @return oggetto Messagge
     */
    public function getMessage($queueId);

    /**
     * Ritorna i messaggi della coda 
     * @param Int $queueId Identificativo della coda
     * @param array $filters possibilita di filtrare per chiave valore 
     * @param bool $arrayFormat ritorna un json 
     * @return array ogetto Messaggi 
     */
    public function findMessages($queueId, $filters = array(), $arrayFormat = false);

    /**
     * Aggiorna messaggio 
     * @param Int $queueId Identificativo della coda
     * @param $message Messaggio da aggiornare 
     */
    public function updateMessage($queueId, $message);

    /**
     * Effettua la notifica dell'avvenuta esecuzione del messasagio
     * @param object $message Mesasggio processato
     * @param string $messageSubject Oggetto messaggio
     * @param string $messageText Testo messaggio
     */
    public function notificationMessage($message, $messageSubject, $messageText);

    /**
     * Verifica esistenza coda
     * @param int $queueId Identificativo coda
     * @return true se esiste, altrimenti false
     */
    public function queueExists($queueId);

    /**
     * Restituisce stato della coda
     * @param int $queueId Identificativo della coda
     * @return array con delle informazioni sullo stato della cosa
     */
    public function queueStatus($queueId);

    /**
     * Restituisce ultimo errore
     * @result Ultimo errore avvenuto
     */
    public function getLastError();

    /**
     * Effettua l'aggiornamento della coda con l'esecuzione del messaggio appena eseguito
     * @param int $queueId Identificativo coda
     * @param object $message Mesasggio processato
     * @param string $errorCode Codice risposta del mesaggio appena processato
     * @param string $errorDescription Descrizione risposta del mesaggio appena processato
     */
    public function updateLastMessageProcessed($queueId, $message, $errorCode, $errorDescription);
}

?>