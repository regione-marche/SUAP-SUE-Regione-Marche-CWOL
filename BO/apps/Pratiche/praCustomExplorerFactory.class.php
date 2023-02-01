<?php
/**
 * Description of praCustmExplorerFactory
 *
 * @author michele
 */

class praCustomExplorerFactory {
    
    private static $explorerTypes = array(
        'local' => 'praCustmExplorerLocal',
        'rest' => 'praCustmExplorerREST'
    );
    
    /**
     * Restituisce oggetto Custom Explorer specifico
     * @param string $type Tipo (se non specificato, lo legge dal config.ini)
     * @return boolean|\className
     */
    public static function getPraCustomExplorer($type = null) {
        if (!$type) {
            return false;
        }
        
        try {
            $className = self::$explorerTypes[$type];
            include_once ITA_BASE_PATH . "/apps/Pratiche/$className.class.php";
            $instance = new $className();             
        } catch (Exception $ex) {
            return false;
        }
        return $instance;
    }    
    
}
