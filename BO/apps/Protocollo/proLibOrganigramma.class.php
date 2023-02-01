<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author   Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2016 Italsoft snc
 * @license
 * @version    01.12.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

class proLibOrganigramma {

    public $proLib;

    private static $lastErrCode;
    private static $lastErrMessage;

    private $errCode;
    private $errMessage;

    function __construct() {
        $this->proLib = new proLib();
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        self::$lastErrCode = $errCode;
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        self::$lastErrMessage = $errMessage;
        $this->errMessage = $errMessage;
    }

    /**
     * Prenota un progressivo per la Tabella ANAUFF (Nodi Organigramma)
     * 
     * @return boolean|string codice prpgressivo in caso di successo o false in caso di errore
     */
    public function getProgANAUFF() {
        $progressivo = '';
        for ($i = 1; $i <= 9999; $i++) {
            $codice = str_repeat("0", 4 - strlen(trim($i))) . trim($i);
            $anamed_rec = $this->proLib->GetAnauff($codice);
            if (!$anamed_rec) {
                $progressivo = $codice;
                return $progressivo;
            }
        }
        $this->setErrCode(-1);
        $this->setErrMessage("Prenotazione Progressivo Fallita");
        return false;
    }

}
