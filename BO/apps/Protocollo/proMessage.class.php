<?php

/**
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Protocollo
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    11.12.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

class proMessage extends emlMessage {

    /**
     * Libreria di funzioni Generiche per Email del Protocollo estesa a emlMessage.class
     */
    
    function checkOggettoInterno($emlMessage) {
        if (!is_object($emlMessage)) {
            return false;
        }
//        if (!isset($this->elementoLocale)) {
//            $datiElemento = $this->elemento;
//        } else {
//            $datiElemento = $this->dettagliFile[$this->elementoLocale];
//            $datiElemento['SUBJECT'] = $datiElemento['OGGETTO'];
//        }
        $retDecode = $emlMessage->getStruct();
        if (!isset($retDecode['Subject'])) {
            return false;
        }
        $oggetto = $retDecode['Subject'];
        if (strpos(strtoupper($oggetto), 'PROTOCOLLO IN ') === false) {
            return false;
        }
        $segnatura = trim(substr($oggetto, strpos($oggetto, '-') + 1));
        if ($segnatura == '') {
            return false;
        }
        $proLib = new proLib();
        $datiSegnatura = $proLib->getGenericTab("SELECT * FROM ANAPRO WHERE PROSEG='$segnatura'", false);
        if (strlen($datiSegnatura['PRONUM']) < 5) {
            return false;
        }
        $datiSegnatura['SUBJECT'] = $retDecode['Subject'];
        return $datiSegnatura;
    }


}

?>