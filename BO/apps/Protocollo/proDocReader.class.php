<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    Factory proDocReader
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    19.09.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

class proDocReader {

    const REL_CLASSE_PROGES = 'PROGES';
    const REL_CLASSE_PASSO = 'PASSO';
    //
    const CLASSE_PROGES = 'proDocReaderProges';
    const CLASSE_PASSO = 'proDocReaderPasso';

    public $errCode;
    public $errMessage;

    public static function getErrCode() {
        return $this->errCode;
    }

    public static function getErrMessage() {
        return $this->errMessage;
    }

    public static function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public static function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public static function getInstance($rowidAnapro, $anadocSave = false) {

        $proLib = new proLib();
        /*
         * Lettura ANADOC:
         * 
         */
        if ($anadocSave) {
            $Anadoc_rec = $proLib->GetAnadocSave($rowidAnapro, 'rowid');
        } else {
            $Anadoc_rec = $proLib->GetAnadoc($rowidAnapro, 'rowid');
        }
        if (!$Anadoc_rec) {
            $this->errCode = -1;
            $this->errMessage = 'Documento non trovato.';
            return false;
        }
        /*
         * Lettura classe 
         */
        switch ($Anadoc_rec['DOCRELCLASSE']) {
            case self::REL_CLASSE_PROGES:
                $className = self::CLASSE_PROGES;
                break;
            case self::REL_CLASSE_PASSO:
                $className = self::CLASSE_PASSO;
                break;

            default:
                return false;
                break;
        }

        include_once (ITA_BASE_PATH . "/apps/Protocollo/$className.class.php");

        try {
            $objManager = new $className($rowidAnapro);
        } catch (Exception $exc) {
            return false;
        }
        return $objManager;
    }

}
