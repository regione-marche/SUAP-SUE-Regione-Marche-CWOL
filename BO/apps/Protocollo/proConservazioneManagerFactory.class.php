<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    Factory Conservazione
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    16.05.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */

include_once ITA_BASE_PATH . '/apps/Protocollo/proConservazioneManagerHelper.class.php';


//include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
//include_once ITA_BASE_PATH . '/apps/Protocollo/proConservazioneManager.class.php';
//include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
//include_once ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php';
//include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
//include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';
//include_once ITA_BASE_PATH . "/lib/itaPHPLogger/itaPHPLogger.php";

class proConservazioneManagerFactory {

    public static function getManager($parametri = array()) {
        /*
         * Lettura Parametri
         */
        if (!$parametri) {
            $parametri = proConservazioneManagerHelper::getParametriConservazione();
        }



        /*
         *  Precondizioni di controllo:
         */
        if (!$parametri) {
            return false;
        }

        /*
         * Lettura classe di conservazione
         */
        switch ($parametri['TIPOCONSERVAZIONE']) {
            case proConservazioneManagerHelper::MANAGER_DIGIP:
                $className = proConservazioneManagerHelper::CLASS_DIGIP;
                break;
            case proConservazioneManagerHelper::MANAGER_ASMEZDOC:
                $className = proConservazioneManagerHelper::CLASS_ASMEZDOC;
                break;
            case proConservazioneManagerHelper::MANAGER_NAMIRIAL:
                $className = proConservazioneManagerHelper::CLASS_NAMIRIAL;
                break;

            default:
                return false;
                break;
        }

        include_once (ITA_BASE_PATH . "/apps/Protocollo/$className.class.php");

        try {
            $objManager = new $className($parametri);
        } catch (Exception $exc) {          
            return false;
        }
        return $objManager;
    }

}