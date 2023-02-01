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

class proLibSoggetti {

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
     * 
     * Prenota un progressivo per la Tabella ANAMED (Soggetti)
     * 
     * @param boolean $recoverGap   Fornisce il primo progressivo libero se vi sono dei codici inutilizzati
     * @return boolean|string
     */
    public function getProgANAMED($recoverGap = false) {
        $progressivo = false;
        if ($recoverGap) {
            for ($i = 1; $i <= 999999; $i++) {
                $codice = str_pad($i, 6, '0', STR_PAD_LEFT);
                $anamed_rec = $this->proLib->GetAnamed($codice, 'codice', 'si', false, false);
                if (!$anamed_rec) {
                    $progressivo = $codice;
                    break;
                }
            }
        } else {
            $PROT_DB = $this->proLib->getPROTDB();
            $sql = "SELECT MAX(MEDCOD) AS ULT_MEDCOD FROM ANAMED WHERE " . $PROT_DB->regExBoolean('MEDCOD', "'^[0-9]+$'");
            $ult_medcod_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
            if (!$ult_medcod_rec) {
                $codice_int = (int) 1;
            } else {
                $codice_int = intval($ult_medcod_rec['ULT_MEDCOD']) + 1;
            }
            if ($codice_int <= 999999) {
                $progressivo = str_pad($codice_int, 6, '0', STR_PAD_LEFT);
            }
        }
        if ($progressivo) {
            return $progressivo;
        }
        $this->setErrCode(-1);
        $this->setErrMessage("Prenotazione Progressivo Fallita");
        return false;
    }

}
