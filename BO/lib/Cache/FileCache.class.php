<?php

require_once ITA_LIB_PATH . '/Cache/Cache.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaJSON.php';

// Requires the native JSON library
if (!function_exists('json_decode') || !function_exists('json_encode')) {
    throw new Exception('Cache needs the JSON PHP extensions.');
}

class FileCache implements Cache {

    /**
     * Value is pre-pended to the cache, should be the full path to the directory
     */
    protected $root = null;

    /**
     * For holding any error messages that may have been raised
     */
    protected $error = null;
    
    private $prefix;

    /**
     * @param string $root The root of the file cache. 
     */
    function __construct($root = '/tmp/', $ente = null) {
        $this->root = $root;
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
        if (!$key) {
            $this->error = "Invalid key";
            return false;
        }
        if (!$data) {
            $this->error = "Invalid data";
            return false;
        }
        $key = $this->_make_file_key($this->prefix.'_'.$key);
        $store = array(
            'data' => $data,
            'ttl' => ($ttl == 0 ? -1 : time() + $ttl),
        );
        $status = false;
        try {
            $status= file_put_contents($key, itaJSON::json_encode($store), LOCK_EX);
        } catch (Exception $e) {
            $this->error = "Exception caught: " . $e->getMessage();
            return false;
        }
        return $status>0;
    }

    /**
     * Reads the data from the cache
     * @param string $key An identifier for the data
     * @returns mixed Data that was stored
     */
    public function get($key) {
        if (!$key) {
            $this->error = "Invalid key";
            return false;
        }

        $key = $this->_make_file_key($this->prefix.'_'.$key);
        $file_content = null;

        // Get the data from the file
        try {
            if(!file_exists($key)){
                return null;
            }
            $file_content = @file_get_contents($key);
            if($file_content === false){
                throw new Exception("File $key not found.");
            }
        } catch (Exception $e) {
            $this->error = "Exception caught: " . $e->getMessage();
            return false;
        }

        // Assuming we got something back..
        if ($file_content) {
            $store = itaJSON::json_decode($file_content);
            if ($store['ttl'] != -1 && $store['ttl'] < time()) {
                unlink($key); // remove the file
                $this->error = "Data expired";
                return false;
            }
        }
        return $store['data'];
    }

    /**
     * Remove a key, regardless of it's expire time
     * @param string $key An identifier for the data
     */
    public function delete($key) {
        if (!$key) {
            $this->error = "Invalid key";
            return false;
        }

        $key = $this->_make_file_key($this->prefix.'_'.$key);

        try {
            unlink($key); // remove the file
        } catch (Exception $e) {
            $this->error = "Exception caught: " . $e->getMessage();
            return false;
        }

        return true;
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

    /**
     * Create a key for the cache
     * @todo Beef up the cleansing of the file.
     * @param string $key The key to create
     * @returns string The full path and filename to access
     */
    private function _make_file_key($key) {
        $safe_key = str_replace(array('.', '/', ':', '\''), array('_', '-', '-', '-'), trim($key));
        return $this->root . $safe_key;
    }

    public function clear() {    
        $files = glob($this->root . '/*');
        foreach($files as $file) { 
        if(is_file($file))
            unlink($file);
        }
    }
    
    /**
     * 
     * @return array (da implementare)
     */
    public function getInfo(){
        return array();
    }

}
