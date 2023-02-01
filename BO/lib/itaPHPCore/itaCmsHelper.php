<?php

require_once(ITA_LIB_PATH . '/itaPHPCore/App.class.php');

/**
 *
 * helper che carica la giusta implementazione di cms, prendendolo da config.ini
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
function itaCmsHelper_importCms() {
    $type = App::getConf("cms.type");
    $import = App::getConf("cms.import");

    if (!$import || !$type) {
        return null;
    }
    if (file_exists($import) && is_readable($import)) {
        require_once $import;
        return true;
    } else {
        return false;
    }
}

?>