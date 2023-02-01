<?php

require_once ITA_LIB_PATH . '/Cache/FileCache.class.php';
require_once ITA_LIB_PATH . '/Cache/APCCache.class.php';

/**
 * Factory per cache
 *
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class CacheFactory {

    const TYPE_APC = 'APC';
    const TYPE_FILE = 'FILE';

    public static function newCache($type = NULL, $cacheRoot = NULL, $ente = null) {
        if (!$type) {
            $type = Config::getConf('cache.type');
        }
        switch ($type) {
            case self::TYPE_APC:
                // Se non presente l'estensione 'apc', oppure si sta lavorando in CLI va in fallback su FileCache
                if (!extension_loaded('apc') || (isSet(App::$isCli) && App::$isCli === true)) {
                    return self::newFileCache($cacheRoot, $ente);
                }
                return self::newAPCCache($ente);
            case self::TYPE_FILE:
            default:
                return self::newFileCache($cacheRoot, $ente);
        }
    }

    private static function newFileCache($root = NULL, $ente = NULL) {
        if (!$root) {
            $root = Config::getConf('cache.root');
        }
        // Se non definita la root, o se la cartella definita non è scrivibile, 
        // crea una sottocartella in 'var/'
        if (!$root || !is_writable($root) || !is_dir($root)) {
            $root = ITA_BASE_PATH . '/var/cache/';            
        }
        if (substr($root, -1) !== '/') {
            $root .= '/';
        }
        if (!file_exists($root)) {
            mkdir($root, 0777, true);
        }
        if (!is_writable($root)) {
            return false;
        }
        return new FileCache($root, $ente);
    }

    private static function newAPCCache($ente = NULL) {
        return new APCCache($ente);
    }
    
    

}
