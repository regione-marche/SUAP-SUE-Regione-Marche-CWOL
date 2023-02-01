<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Mario Maza <mario.mazza@italsoft.eu>
 * @copyright  1987-2019 Italsoft srl
 * @license
 * @version    13.05.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPMPay/itaPHPMPay.class.php');
include_once(ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php');
include_once(ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php');

class praMPayManager {

    public $praLib;
    public $devLib;
    private $clientParam;
    public $PRAM_DB;
    private $errMessage;
    private $errCode;
    public $eqAudit;

    function __construct() {
        parent::__construct();
        $this->praLib = new praLib();
        $this->devLib = new devLib();
        $this->devLib->setITALWEB($this->praLib->getITALWEB());
        $this->PRAM_DB = $this->praLib->getPRAMDB();
    }

    public function getClientParam() {
        return $this->clientParam;
    }

    public function setClientParam($clientParam) {
        $this->clientParam = $clientParam;
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function getErrCode() {
        return $this->errCode;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo Italsoft
     *
     */
    private function setClientConfig($itaMpay) {
        $itaMpay->setUrlPID($this->clientParam['MPAY_URL_PID']);
        $itaMpay->setUrlRID($this->clientParam['MPAY_URL_RID']);
        $itaMpay->setCodicePortale($this->clientParam['MPAY_CODICE_PORTALE']);
        $itaMpay->setEncryptIV($this->clientParam['MPAY_ENCRYPT_IV']);
        $itaMpay->setEncryptKey($this->clientParam['MPAY_ENCRYPT_KEY']);
        $itaMpay->setTimeout($this->clientParam['MPAY_TIMEOUT']);
        $itaMpay->setDebug($this->clientParam['MPAY_DEBUG']);
    }

    public function RiceviPagamento($pid, $itekey = '', $ricnum = '') {
        $itaMpay = new itaPHPMPay();
        if ($itekey != '') {
//            $sql = "SELECT * FROM RICITE WHERE ITEKEY = '$itekey' AND RICNUM = '$ricnum'";
//            $ricite_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
            $ricite_rec = $this->praLib->GetRicite($itekey, "itekey", false, "", $ricnum);
            if (!$ricite_rec) {
                $this->setErrMessage("RICITE non trovato");
                return false;
            }
            //inserisco il PID
            $ricdag_recSeq = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), "SELECT MAX(DAGSEQ) AS SEQ FROM RICDAG WHERE DAGNUM = '" . $ricite_rec['RICNUM'] . "' AND ITEKEY = '" . $ricite_rec['ITEKEY'] . "'", false);
            $seq = $ricdag_recSeq['SEQ'] + 10;
            $ricdag_rec = array();
            $ricdag_rec['DAGNUM'] = $ricite_rec['RICNUM'];
            $ricdag_rec['ITECOD'] = $ricite_rec['ITEPRO'];
            $ricdag_rec['ITEKEY'] = $ricite_rec['ITEKEY'];
            $ricdag_rec['DAGDES'] = "MPAY_PID";
            $ricdag_rec['DAGSEQ'] = $seq;
            $ricdag_rec['DAGKEY'] = "MPAY_PID";
            $ricdag_rec['DAGSET'] = $ricite_rec['ITEKEY'] . "_01";
            //$ricdag_rec['RICDAT'] = $valore;
            //verifico esistenza del dato aggiuntivo, in caso aggiorno
            $sqlChk = "SELECT * FROM RICDAG WHERE DAGNUM = '" . $ricdag_rec['DAGNUM'] . "' AND ITEKEY = '" . $ricdag_rec['ITEKEY'] . "' AND DAGKEY = '" . $ricdag_rec['DAGKEY'] . "'";
            $check_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sqlChk, false);
            if ($check_rec) {
                $check_rec['RICDAT'] = $ricdag_rec['RICDAT'];
                try {
                    ItaDB::DBUpdate($this->praLib->getPRAMDB(), "RICDAG", 'ROWID', $check_rec);
                } catch (Exception $e) {
                    $this->setErrMessage($e->getMessage() . "<br>Aggiornamento dato aggiuntivo $chiave Pratica N. " . $ricite_rec['RICNUM'] . " fallito");
                    return false;
                }
            } else {
                try {
                    ItaDB::DBInsert($this->praLib->getPRAMDB(), "RICDAG", 'ROWID', $ricdag_rec);
                } catch (Exception $e) {
                    $this->setErrMessage($e->getMessage() . "<br>Inserimento dato aggiuntivo $chiave Pratica N. " . $ricite_rec['RICNUM'] . " fallito");
                    return false;
                }
            }
        } else {
            $ricite_rec = $this->getPassoFromPID($pid);
        }
        if (!$ricite_rec) {
            $this->setErrMessage("RICITE non trovato");
            return false;
        }
        file_put_contents("/tmp/ricite_rec.log", print_r($ricite_rec, true));
        $praclt_rec = $this->praLib->GetPraclt($ricite_rec['ITECLT'], 'codice');
        $metaDati = unserialize($praclt_rec['CLTMETA']);
        $clientParam = array();
        $url_pid_rec = $this->devLib->getEnv_config("PAGOPAMPAY_" . $metaDati['METAOPEFO']['ISTANZA_PAR'], 'codice', 'MPAY_URL_PID', false);
        $clientParam['MPAY_URL_PID'] = $url_pid_rec['CONFIG'];
        $itaMpay->setUrlPID($clientParam['MPAY_URL_PID']);
        $url_rid_rec = $this->devLib->getEnv_config("PAGOPAMPAY_" . $metaDati['METAOPEFO']['ISTANZA_PAR'], 'codice', 'MPAY_URL_RID', false);
        $clientParam['MPAY_URL_RID'] = $url_rid_rec['CONFIG'];
        $itaMpay->setUrlRID($clientParam['MPAY_URL_RID']);
        $codicePortale_rec = $this->devLib->getEnv_config("PAGOPAMPAY_" . $metaDati['METAOPEFO']['ISTANZA_PAR'], 'codice', 'MPAY_CODICE_PORTALE', false);
        $clientParam['MPAY_CODICE_PORTALE'] = $codicePortale_rec['CONFIG'];
        $itaMpay->setCodicePortale($clientParam['MPAY_CODICE_PORTALE']);
        $encryptIV_rec = $this->devLib->getEnv_config("PAGOPAMPAY_" . $metaDati['METAOPEFO']['ISTANZA_PAR'], 'codice', 'MPAY_ENCRYPT_IV', false);
        $clientParam['MPAY_ENCRYPT_IV'] = $encryptIV_rec['CONFIG'];
        $itaMpay->setEncryptIV($clientParam['MPAY_ENCRYPT_IV']);
        $encryptKey_rec = $this->devLib->getEnv_config("PAGOPAMPAY_" . $metaDati['METAOPEFO']['ISTANZA_PAR'], 'codice', 'MPAY_ENCRYPT_KEY', false);
        $clientParam['MPAY_ENCRYPT_KEY'] = $encryptKey_rec['CONFIG'];
        $itaMpay->setEncryptKey($clientParam['MPAY_ENCRYPT_KEY']);
        $timeout_rec = $this->devLib->getEnv_config("PAGOPAMPAY_" . $metaDati['METAOPEFO']['ISTANZA_PAR'], 'codice', 'MPAY_TIMEOUT', false);
        $clientParam['MPAY_TIMEOUT'] = $timeout_rec['CONFIG'];
        $itaMpay->setTimeout($clientParam['MPAY_TIMEOUT']);
        $debug_rec = $this->devLib->getEnv_config("PAGOPAMPAY_" . $metaDati['METAOPEFO']['ISTANZA_PAR'], 'codice', 'MPAY_DEBUG', false);
        $clientParam['MPAY_DEBUG'] = $debug_rec['CONFIG'];
        $itaMpay->setDebug($clientParam['MPAY_DEBUG']);

        $paymentData = $itaMpay->getPaymentData($pid);

        return $paymentData;
    }

    public function inserisciPagamento($pid, $buffer, $itekey, $ricnum) {
        if (!$pid) {
            $this->setErrMessage("PID non settato");
            return false;
        }
        if (!$buffer) {
            $this->setErrMessage("buffer non settato");
            return false;
        }
        /*
         * Leggo XML del Buffer Data
         */
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($buffer);
        if (!$retXml) {
            $this->setErrMessage("Impossibile impostare il buffer in XML");
            return false;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $this->setErrMessage("Impossibile convertire XML in Array");
            return false;
        }

        /*
         * Decodifico la stringa del Payment Data
         */
        $paymentData = base64_decode($arrayXml['BufferDati'][0]['@textNode']);
        if ($paymentData == "") {
            $this->setErrMessage("Impossibile decodificare il PaymentData");
            return false;
        }

        /*
         * Leggo XML del Payment Data
         */
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($paymentData);
        if (!$retXml) {
            $this->setErrMessage("Impossibile settare XML del Payment Data");
            return false;
        }
        $arrayXmlPaymentData = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXmlPaymentData) {
            $this->setErrMessage("Impossibile convertire XML Payment Data in Array");
            return false;
        }

        /*
         * Mi salvo i dati aggiuntivi del PaymentData
         */
        //$ricite_rec = $this->getPassoFromPID($pid);
        $ricite_rec = $this->praLib->GetRicite($itekey, "itekey", false, "", $ricnum);
        if (!$ricite_rec) {
            return false;
        }
        foreach ($arrayXmlPaymentData as $key => $value) {
            if (!$this->saveDatoAggiuntivo($ricite_rec, "MPAY_$key", $value[0]['@textNode'])) {
                return false;
            }
        }

        return $arrayXmlPaymentData['Esito'][0]['@textNode'];
    }

    public function getPassoFromPID($pid) {
        $sql = "SELECT * FROM RICDAG WHERE MPAY_PID = '" . addslashes($pid) . "' AND RICDAT <> ''";
        $ricdag_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
        return $this->praLib->GetRicite($ricdag_rec['ITEKEY'], 'itekey', false, '', $ricdag_rec['DAGNUM']);
    }

    public function getPortaleID() {
        $ricite_rec = $this->getPassoFromPID($pid);
        $praclt_rec = $this->praLib->GetPraclt($ricite_rec['ITECLT'], 'codice');
        $metaDati = unserialize($praclt_rec['CLTMETA']);
        $codicePortale_rec = $this->devLib->getEnv_config("PAGOPAMPAY_" . $metaDati['METAOPEFO']['ISTANZA_PAR'], 'MPAY_CODICE_PORTALE', false);
        return $codicePortale_rec['CONFIG'];
    }

    private function saveDatoAggiuntivo($ricite_rec, $chiave, $valore) {
        $ricdag_recSeq = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), "SELECT MAX(DAGSEQ) AS SEQ FROM RICDAG WHERE DAGNUM = '" . $ricite_rec['RICNUM'] . "' AND ITEKEY = '" . $ricite_rec['ITEKEY'] . "'", false);
        $seq = $ricdag_recSeq['SEQ'] + 10;
        $ricdag_rec = array();
        $ricdag_rec['DAGNUM'] = $ricite_rec['RICNUM'];
        $ricdag_rec['ITECOD'] = $ricite_rec['ITEPRO'];
        $ricdag_rec['ITEKEY'] = $ricite_rec['ITEKEY'];
        $ricdag_rec['DAGDES'] = $chiave;
        $ricdag_rec['DAGSEQ'] = $seq;
        $ricdag_rec['DAGKEY'] = $chiave;
        $ricdag_rec['DAGSET'] = $ricite_rec['ITEKEY'] . "_01";
        $ricdag_rec['RICDAT'] = $valore;
        //verifico esistenza del dato aggiuntivo, in caso aggiorno
        $sqlChk = "SELECT * FROM RICDAG WHERE DAGNUM = '" . $ricdag_rec['DAGNUM'] . "' AND ITEKEY = '" . $ricdag_rec['ITEKEY'] . "' AND DAGKEY = '" . $ricdag_rec['DAGKEY'] . "'";
        $check_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sqlChk, false);
        if ($check_rec) {
            $check_rec['RICDAT'] = $ricdag_rec['RICDAT'];
            try {
                ItaDB::DBUpdate($this->praLib->getPRAMDB(), "RICDAG", 'ROWID', $ricdag_rec);
            } catch (Exception $e) {
                $this->setErrMessage($e->getMessage() . "<br>Aggiornamento dato aggiuntivo $chiave Pratica N. " . $ricite_rec['RICNUM'] . " fallito");
                return false;
            }
            return true;
        }
        try {
            ItaDB::DBInsert($this->praLib->getPRAMDB(), "RICDAG", 'ROWID', $ricdag_rec);
        } catch (Exception $e) {
            $this->setErrMessage($e->getMessage() . "<br>Inserimento dato aggiuntivo $chiave Pratica N. " . $ricite_rec['RICNUM'] . " fallito");
            return false;
        }
        return true;
    }

    public function getDatoAggiuntivo($ricite_rec, $dato = 'NumeroOperazione') {
        switch ($dato) {
            case 'NumeroOperazione':
                $sql = "SELECT * FROM RICDAG WHERE DAGNUM = '" . $ricite_rec['RICNUM'] . "' AND DAGKEY = 'MPAY_NumeroOperazione' AND ITEKEY = '" . $ricite_rec['ITEKEY'] . "'";
                break;
            case 'IDOrdine':
                $sql = "SELECT * FROM RICDAG WHERE DAGNUM = '" . $ricite_rec['RICNUM'] . "' AND DAGKEY = 'MPAY_IDOrdine' AND ITEKEY = '" . $ricite_rec['ITEKEY'] . "'";
                break;

            default:
                break;
        }
        $record = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        return $record['RICDAT'];
    }

    public function getRicite($itekey, $ricnum) {
        return $this->praLib->GetRicite($itekey, 'itekey', false, '', $ricnum);
    }

}
