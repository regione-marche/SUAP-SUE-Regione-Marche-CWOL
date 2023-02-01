<?php

require_once ITA_LIB_PATH . '/Cache/Cache.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaJSON.php';

class APCCache implements Cache {
    private $prefix;
    
    public function __construct($ente = null) {
        if(!isSet($ente) && isSet(App::$utente)){
            $this->prefix = App::$utente->getKey('ditta') ?: '';
        }
        else{
            $this->prefix = $ente;
        }
    }

    /**
     * Saves data to the cache. Anything that evaluates to false, null, '', boolean false, 0 will 
     * not be saved.
     * @param string $key An identifier for the data
     * @param mixed $data The data to save
     * @param int $ttl Seconds to store the data
     * @returns boolean True if the save was successful, false if it failed
     */
    public function set($key, $data = false, $ttl = 3600) {
        if (!extension_loaded('apc')) {
            $this->error = "Missing APC Extension";
            return false;            
        }
        if (!$key) {
            $this->error = "Invalid key";
            return false;
        }
        if (!$data) {
            $this->error = "Invalid data";
            return false;
        }
        return apc_store($this->prefix.'_'.$key, $data, $ttl);        
    }

    /**
     * Reads the data from the cache
     * @param string $key An identifier for the data
     * @returns mixed Data that was stored
     */
    public function get($key) {
        if (!extension_loaded('apc')) {
            $this->error = "Missing APC Extension";
            return false;            
        }
        if (!$key) {
            $this->error = "Invalid key";
            return false;
        }
        $data = apc_fetch($this->prefix.'_'.$key, $status);
        return ($status ? $data : null);
    }

   
    /**
     * Remove a key, regardless of it's expire time
     * @param string $key An identifier for the data
     */
    public function delete($key) {
        if (!extension_loaded('apc')) {
            $this->error = "Missing APC Extension";
            return false;            
        }
        if (!$key) {
            $this->error = "Invalid key";
            return false;
        }
        return (apc_exists($this->prefix.'_'.$key) ? apc_delete($this->prefix.'_'.$key) : true);
    }

    /**
     * Reads and clears the internal error
     * @returns string Text of the error raised by the last process
     */
    public function get_error() {
        $message = $this->error;
        $this->error = null;
        return $message;
    }

    /**
     * Can be used to inspect internal error
     * @returns boolean True if we have an error, false if we don't 
     */
    public function have_error() {
        return ($this->error !== null) ? true : false;
    }

    public function clear() {
        apc_clear_cache();
        apc_clear_cache('user');
        apc_clear_cache('opcode');
    }

    
    public function getInfo(){
        return apc_cache_info('user');
    }
}
