<?php

/**
 * Description of Cache
 *
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
interface Cache {
    
    /**
     * Imposta una nuova chiave in cache
     * @param string $key Chiave
     * @param string $data Valore
     * @param integer $ttl TTL
     * @return true/false
     */
    public function set($key, $data = false, $ttl = 3600);
    
    /**
     * Legge valore dalla cache
     * @param string $key Chiave
     * @return mixed Valore (false in caso di errore o chiave scaduta)
     */
    public function get($key);
    
    /**
     * Cancella una chiave dalla cache
     * @param string $key Chiave
     * @return true/false
     */
    public function delete($key);
    
    /**
     * Restituisce errore
     */
    public function get_error();
    
    /**
     * Restituisce true se sono presenti errori, altrimenti false
     */
    public function have_error();
    
    /**
     * Pulisce cache
     */
    public function clear();
    
}
