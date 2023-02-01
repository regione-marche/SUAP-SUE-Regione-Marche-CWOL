<?php

/* * 
 *
 * GESTIONE PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    14.04.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSegnatura.class.php';

function proCheckMailInterni() {
    $proCheckMailInterni = new proCheckMailInterni();
    $proCheckMailInterni->parseEvent();
    return;
}

class proCheckMailInterni extends itaModel {

    public $PROT_DB;
    public $ITALWEB_DB;
    public $nameForm = "proCheckMailInterni";
    public $proLib;
    public $proLibFascicolo;
    public $accLib;
    public $emlLib;
    public $AnaproImportati = array();
    public $ArrAnomalie = array();
    public $fileLogInterni;

    function __construct() {
        parent::__construct();
        $this->proLib = new proLib();
        $this->accLib = new accLib();
        $this->emlLib = new emlLib();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->ITALWEB_DB = $this->accLib->getITALWEB();
        $this->fileLogInterni = App::$utente->getKey($this->nameForm . '_fileLogInterni');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_fileLogInterni', $this->fileLogInterni);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        if ($this->proLib->checkStatoManutenzione()) {
            Out::msgStop("ATTENZIONE!", "<br>Il protocollo è nello stato di manutenzione.<br>Attendere la riabilitazione o contattare l'assistenza.<br>");
            return;
        }
        switch ($this->event) {
            case 'openform':
                switch ($_POST['test']) {
                    case "I":
                        $anno = '2019';
                        $archivia = false;
                        $this->CheckMailAnades($anno, $archivia);
                        break;
                    case "PC":
                        $this->CheckPostaCert();
                        break;
                    case "MS":
                        $this->CheckMailScartate();
                        break;
                    case "VM":
                        $this->CheckStatoMail();
                        break;
                    default:
                        break;
                }

                break;
            case 'editGridRow':
            case 'dbClickRow':
                break;
            case 'onClick':
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
        App::$utente->removeKey($this->nameForm . '_FileCSV');
        App::$utente->removeKey($this->nameForm . '_AnaproImportati');
        App::$utente->removeKey($this->nameForm . '_ArrAnomalie');
        App::$utente->removeKey($this->nameForm . '_fileLogInterni');
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function CheckMailAnades($anno, $archivia = null) {
        if ($archivia === null) {
            return false;
        }
        $this->fileLogInterni = sys_get_temp_dir() . "/proCheckMailInterni_log_" . time() . ".csv";
        $italwebdb = $this->accLib->getITALWEB()->getDB();

        $sql = "SELECT
                    MAIL_ARCHIVIO.*,
                    ANADES.DESIDMAIL,
                    ANADES.ROWID AS ROWID_ANADES
                FROM ANADES
                LEFT OUTER JOIN $italwebdb.MAIL_ARCHIVIO MAIL_ARCHIVIO ON MAIL_ARCHIVIO.IDMAIL=ANADES.DESIDMAIL 
                WHERE DESTIPO LIKE 'T' AND DESIDMAIL != ''  AND DESNUM LIKE '$anno%' 
                AND STATOEML = 0 ";
        $MailArchivio_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);

        /*
         * Conteggio Size
         */
        $TotSize = 0;
        $ElencoIdMailMancanti = array();

        /*
         * Dati Log
         * 
         */
        $arrlog = array();
        $arrlog[] = 'DESNUM';
        $arrlog[] = 'DESPAR';
        $arrlog[] = 'SENDREC';
        $arrlog[] = 'ACCOUNT';
        $arrlog[] = 'FROMADDR';
        $arrlog[] = 'TOADDR';
        $arrlog[] = 'DATAFILE';
        $arrlog[] = 'FILEPATH';
        $arrlog[] = 'SIZE';
        if ($archivia === true) {
            $arrlog[] = 'DATA ARCHIVIA';
            $arrlog[] = 'ORA ARCHIVIA';
            $arrlog[] = 'HASH EML';
            $arrlog[] = 'STATO ARCHIVIA';
            $arrlog[] = 'MSG ARCHIVIA';
        }
        $testoLog = implode("\t", $arrlog);
        $this->scriviLog($this->fileLogInterni, $testoLog, true);

        foreach ($MailArchivio_tab as $MailArchivio_join) {
            // Controllo Record non trovato su Mail Archivio.
            if (!$MailArchivio_join['IDMAIL']) {
                $ElencoIdMailMancanti[] = $MailArchivio_join['DESIDMAIL'];
                continue;
            }

            $MailArchivio_rec = $this->emlLib->getMailArchivio($MailArchivio_join['ROWID'], 'rowid');
            $Anades_rec = $this->proLib->GetAnades($MailArchivio_join['ROWID_ANADES'], 'rowid');

            $PathMail = $this->emlLib->SetDirectory($MailArchivio_rec['ACCOUNT']);
            $FilePath = $PathMail . $MailArchivio_rec['DATAFILE'];
            $Size = filesize($FilePath);

            $arrlog = array();
            $arrlog[] = $Anades_rec['DESNUM'];
            $arrlog[] = $Anades_rec['DESPAR'];
            $arrlog[] = $MailArchivio_rec['SENDREC'];
            $arrlog[] = $MailArchivio_rec['ACCOUNT'];
            $arrlog[] = $MailArchivio_rec['FROMADDR'];
            $arrlog[] = $MailArchivio_rec['TOADDR'];
            $arrlog[] = $MailArchivio_rec['DATAFILE'];
            $arrlog[] = $FilePath;
            $arrlog[] = $Size;
            $Ret_MailArchivio_rec = array();
            if ($archivia === true) {
                $Ret_Archivia = $this->archiviaEmlInterni($MailArchivio_rec, $FilePath);
                if ($Ret_Archivia['status'] === false) {
                    $arrlog[] = "";
                    $arrlog[] = "";
                    $arrlog[] = "";
                    $arrlog[] = "9";
                    $arrlog[] = $Ret_Archivia['message'];
                } else {
                    $Ret_MailArchivio_rec = $Ret_Archivia['Ret_MailArchivio_rec'];
                    $arrlog[] = $Ret_MailArchivio_rec['DATEARCEML'];
                    $arrlog[] = $Ret_MailArchivio_rec['TIMEARCEML'];
                    $arrlog[] = $Ret_MailArchivio_rec['HASHARCEML'];
                    $arrlog[] = $Ret_MailArchivio_rec['STATOEML'];
                    $arrlog[] = $Ret_Archivia['message'];
                }
            }

            $testoLog = implode("\t", $arrlog);
            $this->scriviLog($this->fileLogInterni, $testoLog, true);

            $TotSize = $TotSize + $Size;
        }
        $ConverTotalSize = $this->FileSizeConvert($TotSize);
        $MsgRiepilogo = "";
        $MsgRiepilogo .= " Totale dimensioni occupate: " . $ConverTotalSize . "<br><br>";
        $MsgRiepilogo .= "IDMAIL non trovati in Archivio: " . count($ElencoIdMailMancanti) . ".<br> Elenco: " . print_r($ElencoIdMailMancanti, true);
        $MsgRiepilogo .= "<br>File di Log:" . $this->fileLogInterni;
        Out::msgInfo("Check Mail", $MsgRiepilogo);
    }

    public function archiviaEmlInterni($MailArchivio_rec, $FilePath) {
        $ret = array(
            'status' => null,
            'message' => null,
            'Ret_MailArchivio_rec' => array()
        );

        if (!file_exists($FilePath)) {
            $ret['status'] = false;
            $ret['message'] = "File eml: $FilePath non esistente o non acecssibile.";
            return $ret;
        }

        $baseNameEml = pathinfo($MailArchivio_rec['DATAFILE'], PATHINFO_BASENAME);

        if ($baseNameEml !== $MailArchivio_rec['DATAFILE']) {
            $ret['status'] = false;
            $ret['message'] = "File eml: $FilePath Incongruenza con datafile su record MAIL_ARCHIVIO.";
            return $ret;
        }

        try {
            $hashEml = hash_file('sha256', $FilePath);
            if (!$hashEml) {
                $ret['status'] = false;
                $ret['message'] = "Calcolo Hash File eml: $FilePath non riuscito.";
                return $ret;
            }
        } catch (Exception $ex) {
            $ret['status'] = false;
            $ret['message'] = "Eccezione in Calcolo Hash File eml: $FilePath. " . $ex->getMessage();
            return $ret;
        }

        $dateArc = date("Ymd");
        $timeArc = date("H:i:s");

        /*
         * Qui Update
         * 
         * 
         */
        $Upd_MailArchivio_rec = array();
        try {
            $Upd_MailArchivio_rec['ROWID'] = $MailArchivio_rec['ROWID'];
            $Upd_MailArchivio_rec['DATEARCEML'] = $dateArc;
            $Upd_MailArchivio_rec['TIMEARCEML'] = $timeArc;
            $Upd_MailArchivio_rec['HASHARCEML'] = $hashEml;
            $Upd_MailArchivio_rec['STATOEML'] = 2;
            ItaDB::DBUpdate($this->ITALWEB_DB, 'MAIL_ARCHIVIO', 'ROWID', $Upd_MailArchivio_rec);
        } catch (Exception $e) {
            $ret['status'] = false;
            $ret['message'] = "Eccezione in Aggiornamento MAIL_ARCHIVIO ROWID: {$Upd_MailArchivio_rec['ROWID']}. " . $e->getMessage();
            return $ret;
        }

        /**
         * 
         * Qui cancellazione
         */
        $ret_delete = unlink($FilePath);
        if ($ret_delete === false) {
            $ret['status'] = false;
            $ret['message'] = "Errore in cancellazione file eml $FilePath";
            return $ret;
        }

        $ret['status'] = true;
        $ret['message'] = "[$dateArc][$timeArc]: Archivizione Eseguita.";
        $ret['Ret_MailArchivio_rec'] = $Upd_MailArchivio_rec;
        return $ret;
    }

    public function CheckMailScartate() {
        $italwebdb = $this->accLib->getITALWEB()->getDB();

        $sql = "SELECT
                MAIL_ARCHIVIO.*
                    FROM MAIL_ARCHIVIO
                WHERE CLASS LIKE '%SCARTATO%' OR CLASS LIKE '%INOLTR%'";
        $MailArchivio_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);

        /*
         * Conteggio Size
         */
        $TotSize = 0;
        foreach ($MailArchivio_tab as $MailArchivio_rec) {
            // Controllo Record non trovato su Mail Archivio.
            $PathMail = $this->emlLib->SetDirectory($MailArchivio_rec['ACCOUNT']);
            $FilePath = $PathMail . $MailArchivio_rec['DATAFILE'];
            $Size = filesize($FilePath);
            $TotSize = $TotSize + $Size;
        }
        $ConverTotalSize = $this->FileSizeConvert($TotSize);
        $MsgRiepilogo = "";
        $MsgRiepilogo .= " Totale dimensioni occupate mail scartate: " . $ConverTotalSize . "<br><br>";
        Out::msgInfo("Check Mail Scartate", $MsgRiepilogo);
    }

    public function CheckPostaCert() {
        $sql = "SELECT
                    *
                FROM ANADOC
                WHERE DOCNAME='postacert.eml' AND DOCIDMAIL<>''";
        $Anadoc_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);

        /*
         * Conteggio Size
         */
        $TotSize = 0;
        foreach ($Anadoc_tab as $Anadoc_rec) {
            $PathMail = $this->proLib->SetDirectory($Anadoc_rec['DOCNUM'], $Anadoc_rec['DOCPAR']);
            $FilePath = $PathMail . "/" . $Anadoc_rec['DOCFIL'];
            $Size = filesize($FilePath);
            $TotSize = $TotSize + $Size;
        }
        $ConverTotalSize = $this->FileSizeConvert($TotSize);
        $MsgRiepilogo = "";
        $MsgRiepilogo .= " Totale dimensioni occupate da postacert.eml: " . $ConverTotalSize . "<br><br>";
        Out::msgInfo("Check Postacert.eml", $MsgRiepilogo);
    }

    public function CheckStatoMail() {

        $this->fileLogInterni = sys_get_temp_dir() . "/proCheckMailInterni_log_" . time() . ".csv";

        $sql = "SELECT ROWID,"
                . " IDMAIL, "
                . " SENDREC,"
                . " ACCOUNT,"
                . " FROMADDR,"
                . " TOADDR,"
                . " DATAFILE,"
                . " STATOEML,"
                . " STATOEML "
                . " FROM MAIL_ARCHIVIO ";
        $MailArchivio_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);

        /*
         * Conteggio Size
         */

        $ElencoIdMailMancanti = array();

        /*
         * Dati Log
         * 
         */
        $arrlog = array();
        $arrlog[] = 'SENDREC';
        $arrlog[] = 'ACCOUNT';
        $arrlog[] = 'FROMADDR';
        $arrlog[] = 'TOADDR';
        $arrlog[] = 'DATAFILE';
        $arrlog[] = 'FILEPATH';
        $arrlog[] = 'STATOEML';
        $arrlog[] = 'EXIST';
        $arrlog[] = 'MESSAGGIO';

        $testoLog = implode("\t", $arrlog);
        $this->scriviLog($this->fileLogInterni, $testoLog, true);

        foreach ($MailArchivio_tab as $MailArchivio_rec) {
            // Controllo Record non trovato su Mail Archivio.
            if (!$MailArchivio_rec['IDMAIL']) {
                $ElencoIdMailMancanti[] = $MailArchivio_rec['DESIDMAIL'];
                continue;
            }

            $Exist = '1';
            $Messaggio = 'Trovato';
            $PathMail = $this->emlLib->SetDirectory($MailArchivio_rec['ACCOUNT']);
            $FilePath = $PathMail . $MailArchivio_rec['DATAFILE'];
            if (!file_exists($FilePath)) {
                $Exist = '0';
                $Messaggio = 'File non trovato';
            }

            $arrlog = array();
            $arrlog[] = $MailArchivio_rec['SENDREC'];
            $arrlog[] = $MailArchivio_rec['ACCOUNT'];
            $arrlog[] = $MailArchivio_rec['FROMADDR'];
            $arrlog[] = $MailArchivio_rec['TOADDR'];
            $arrlog[] = $MailArchivio_rec['DATAFILE'];
            $arrlog[] = $FilePath;
            $arrlog[] = $MailArchivio_rec['STATOEML'];
            $arrlog[] = $Exist;
            $arrlog[] = $Messaggio;

            $testoLog = implode("\t", $arrlog);
            $this->scriviLog($this->fileLogInterni, $testoLog, true);
        }

        $MsgRiepilogo = "Controllo Stato Archiviazione EML Terminato.";
        $MsgRiepilogo .= "<br>File di Log:" . $this->fileLogInterni;
        Out::msgInfo("Check Mail", $MsgRiepilogo);
    }

    private function FileSizeConvert($bytes) {
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach ($arBytes as $arItem) {
            if ($bytes >= $arItem["VALUE"]) {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", ",", strval(round($result, 2))) . " " . $arItem["UNIT"];
                break;
            }
        }
        return $result;
    }

    private function scriviLog($file, $testo, $flAppend = true, $nl = "\n") {
        if ($flAppend) {
            file_put_contents($file, "$testo$nl", FILE_APPEND);
        } else {
            file_put_contents($file, "$testo$nl");
        }
    }

}
