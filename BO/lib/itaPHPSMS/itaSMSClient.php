<?php

/**
 *
 * Interfaccia SMS client
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPSMS
 * @author     Moscioni/Mazza
 * @copyright  
 * @license
 * @version    05.01.2018
 * @link
 * @see
 * 
 */
interface itaSMSClient {

    /**
     * Restituisce i parametri
     * @return array di parametri
     *      - Tipo (string)
     *      - Mittente (string)
     *      - Destinatari (array di stringhe contenente i numeri dei destinatari)
     *      - Messaggio (string)
     *      - ID
     */
    public function getParameters();
    
    /**
     * Setta i parametri
     * @param type $parameters  array di parametri
     *      - Tipo (string)
     *      - Mittente (string)
     *      - Destinatari (array di stringhe contenente i numeri dei destinatari)
     *      - Messaggio (string)
     *      - ID
     */
    public function setParameters($parameters);

    /**
     * Funzione per l'invio di un SMS
     * @return array Struttura dati con le informazioni sull'invio
     *      - Esito: Esito dell'invio (OK, Errore)
     *      - ID: Id del messaggio se a buon fine
     */
    public function sendSMS();

    /**
     * Restituisce lo stato del messaggio
     * @param id del messaggio inviato
     * @return string Stato (testo)
     */
    public function getSMSStatus($id);

    /**
     * Valida la correttezza di un messaggio da inviare
     * @return bool true/false
     */
    public function validate();

    /**
     * Restituisce il credito residuo per tipologia di sms
     * @return array Struttura dati con le informazioni sull'invio
     *      - Tipo: Tipologia di messaggi
     *      - Credito: credito residuo
     */
    public function getCredit();

    /**
     * Restituisce l'ultimo errore
     * @return string ultimo errore
     */
    public function getLastError();

    /**
     * Imposta l'ultimo errore
     * @param object $exc Eccezione
     */
    public function setLastError($exc);
}
