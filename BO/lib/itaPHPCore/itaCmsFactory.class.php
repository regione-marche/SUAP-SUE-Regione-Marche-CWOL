<?php

require_once(ITA_LIB_PATH . '/itaPHPCore/App.class.php');
require_once(ITA_LIB_PATH . '/itaPHPCore/itaFileUtils.class.php'); 

/**
 *
 * factory che carica la giusta implementazione di cms, prendendolo da config.ini
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaCmsFactory
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * @copyright  
 * @license
 * @version    25.10.2016
 * @link
 * @see
 * 
 */
class itaCmsFactory {

    public static function getCms() {
        $type = App::getConf("cms.type");
        $import = App::getConf("cms.import");

        if (!$import || !$type || !file_exists($import)) {
            return null;
        }

        require_once $import;

        $method = 'itaCms' . ucwords($type);

        $cms = new $method();
        
        // Se si tratta di getSimpleCms, crea le risorse fuori-git
        if ($type === 'getSimpleCms') {
            $cmsBaseDir = ITA_BASE_PATH . '/cms';            
            $cmsDataDir = $cmsBaseDir . '/data';
            $cmsDataUsersDir = $cmsDataDir . '/users';
            $cmsSiteMapFile = $cmsBaseDir . '/sitemap.xml';            
            $cmsDataSampleDir = $cmsBaseDir . '/data.sample';
            $cmsSiteMapSampleFile = $cmsBaseDir . '/sitemap.sample.xml';            
            
            // Se la cartella /data è corrotta, la cancella
            // N.B.: Questo caso si verifica solo su ambiente di sviluppo
            if (!file_exists($cmsDataUsersDir)) {
                if (file_exists($cmsSiteMapFile)) {
                    unlink($cmsSiteMapFile);
                }                
                itaFileUtils::removeDir($cmsDataDir);
            }  
            
            // Crea dati da sample (se non esistenti)
            if (!file_exists($cmsDataDir)) {
                itaFileUtils::copyDir($cmsDataSampleDir, $cmsDataDir);                                
            }            
            if (!file_exists($cmsSiteMapFile)) {
                copy($cmsSiteMapSampleFile, $cmsSiteMapFile);
            }            
        }        
        
        return $cms;
    }

}

