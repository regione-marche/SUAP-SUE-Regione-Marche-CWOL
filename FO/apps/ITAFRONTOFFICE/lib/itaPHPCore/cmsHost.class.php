<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cmsHost
 *
 * @author michele
 */
require_once ITA_LIB_PATH . '/itaPHPCore/cmsHostInterface.class.php';

class cmsHost {

    static public function getInstance($cms = 'si') {
        $driver_cms = dirname(__FILE__) . "/cmsHost.$cms.class.php";

        if (!file_exists($driver_cms)) {
            throw new Exception("Driver cms $driver_cms non trovato");
        }

        require_once $driver_cms;

        $classe = 'cmsHost_' . $cms;
        return new $classe();
    }

}
