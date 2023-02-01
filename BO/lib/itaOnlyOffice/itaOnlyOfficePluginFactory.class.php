<?php

/**
 * Factory per la gestione dei plugin di OnlyOffice
 *
 * @author m.biagioli
 */
class itaOnlyOfficePluginFactory {
    
    // Costanti che identificano i tipi di plugin
    const PLUGIN_TYPE_DICTIONARY = 'dictionary';
    
    /**
     * Mappatura plugin
     * @var array
     */
    private static $pluginMap = array(
        'dictionary' => 'itaOnlyOfficePluginDictionary'
    );
    
    /**
     * Restituisce plugin specifico
     * @param string $type Tipo di plugin
     * @return boolean|\className Plugin specifico, o false in caso di errore
     */
    public static function getPlugin($type) {
        if (!isset(self::$pluginMap[$type])) {
            return false;
        }        
        $className = self::$pluginMap[$type];
        require_once ITA_LIB_PATH . '/itaOnlyOffice/' . $className . '.class.php';
        return new $className;
    }
    
}