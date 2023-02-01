<?php

/**
 *
 * Factory per creazione oggetto gestore dati FrontOffice
 *
 * PHP Version 5
 *
 * @category   extended library
 * @package    apps/Pratiche
 * @author    Franchi/Moscioni
 * @copyright
 * @license
 * @version    03.04.2018
 * @link
 * @see
 *
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeManager.class.php';

class praFrontOfficeFactory {

    /**
     * Restituisce il client specifico
     * @param array $parameters Parametri di configurazione (se non specificati, li legge da config.ini)
     * @return boolean|\class Oggetto updater client specifico
     */
    public static function getFrontOfficeManagerInstance($foType = '') {
        $parameters = self::getInitParameters();
        if (!$foType) {
            $foType = $parameters['frontOfficeType'];
        }

        if (!$foType) {
            return false;
        }

        $className = "praFrontOffice" . praFrontOfficeManager::$FRONT_OFFICE_TYPES[$foType];
        include_once ITA_BASE_PATH . "/apps/Pratiche/$className.class.php";
        try {
            /* @var $objClient praFrontOfficeManager */
            $objClient = new $className();
        } catch (Exception $ex) {
            return false;
        }
        $objClient->setFoTipo($foType);
        return $objClient;
    }

    private static function getInitParameters() {

        return array('frontOfficeType' => praFrontOfficeManager::TYPE_FO_STAR_WS);

        //TODO: Qui possibilità di inserire la lettura di paramentri specifici.
    }

}

?>