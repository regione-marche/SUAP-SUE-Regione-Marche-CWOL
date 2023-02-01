<?php

/**
 *
 * LIBRERIA EMAIL
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Email
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    06.12.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlDate.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php';
include_once ITA_LIB_PATH . '/itaPHPMail/itaMime.class.php';

class emlDbMailBox {

    private $account;
    private $emlLib;
    private $lastExitCode;
    private $lastMessage;
    private $MAIL_DB;
    private $insertStack;
    private $eqAudit;
    private $insertedRec;
    private $insertedEml;

    public function __construct($account = null) {
        try {
            $this->emlLib = new emlLib();
            $this->MAIL_DB = $this->emlLib->getITALWEB();
            $this->eqAudit = new eqAudit();
        } catch (Exception $e) {
            throw new Exception("Mail box locale su db: errore in caricamento risorse");
        }
        if ($account) {
            $this->setAccount($account);
        }
    }

    /**
     * Restituisce una istanza di emlDBMailbox
     * @param type $account nome account di riferimente (facoltativo)
     * @return boolean|\emlDbMailBox 
     */
    public static function getDbMailBoxInstance($account = '') {
        try {
            return new emlDbMailBox($account);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 
     * @param string $account
     */
    public function setAccount($account) {
        $this->account = $account;
    }

    /**
     * 
     * @return string
     */
    public function getAccount() {
        return $this->account;
    }

    /**
     * 
     * @return integer
     */
    public function getLastExitCode() {
        return $this->lastExitCode;
    }

    /**
     * 
     * @return string
     */
    public function getLastMessage() {
        return $this->lastMessage;
    }

    /**
     * 
     * @return string
     */
    public function getInsertedRec() {
        return $this->insertedRec;
    }

    /**
     * 
     * @return type
     */
    public function getInsertedEml() {
        return $this->insertedEml;
    }

    /**
     * Analizza i messaggi da eliminare per scadenza giorni permanenza su casella remota
     * @param integer $waitDays giorni di attesa
     * @return boolean | array 
     * <pre>
     * ritorna un array con due sotto array che identificano:
     * ['PACKEDLIST'] lista di UID senza elementi da cancellare
     * ['DELETELIST'] lista di UID da cancellare su db locale (chiave = UID,  valore = rowid)
     * </pre>
     */
    public function getDeleteIndex($waitDays) {
        if (!$this->account) {
            $this->lastExitCode = -1;
            $this->lastMessage = "Account non definito";
            return false;
        }
        $sql = "SELECT * FROM MAIL_ACCLIST WHERE ACCOUNT ='" . $this->account . "'";
        $Mail_acclist_tab = array();
        $packedUIDList = array();
        $deleteUIDList = array();
        $returnArray = array();
        try {
            $Mail_acclist_tab = itaDB::DBSQLSelect($this->emlLib->getITALWEB(), $sql, true);
            if ($Mail_acclist_tab) {
                for ($j = 0; $j < count($Mail_acclist_tab); $j++) {
                    $cancDate = date('Ymd', strtotime($Mail_acclist_tab[$j]['RECDATE']) + ($waitDays * 60 * 60 * 24));
                    if (date('Ymd') < $cancDate) {
                        $packedDUIDList[] = $Mail_acclist_tab[$j]['STRUID'];
                    } else {
                        $deleteUIDList[$Mail_acclist_tab[$j]['STRUID']] = $Mail_acclist_tab[$j]['ROWID'];
                    }
                }
            }
            $this->lastExitCode = 0;
            $this->lastMessage = "";
            return array(
                'PACKEDLIST' => $packedDUIDList,
                'DELETELIST' => $deleteUIDList
            );
        } catch (Exception $e) {
            $this->lastExitCode = -1;
            $this->lastMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * Ritorna la lista di UID presenti ne db per l'account corrente
     * @return boolean
     */
    public function getUIDList() {
        if (!$this->account) {
            $this->lastExitCode = -1;
            $this->lastMessage = "Account non definito";
            return false;
        }

        $sql = "SELECT STRUID AS STRUID FROM MAIL_ACCLIST WHERE ACCOUNT ='" . $this->account . "'";

        $Mail_acclist_tab = array();
        $UIDList = array();
        try {
            $Mail_acclist_tab = itaDB::DBSQLSelect($this->emlLib->getITALWEB(), $sql, true);
            if ($Mail_acclist_tab) {
                foreach ($Mail_acclist_tab as $key => $Mail_acclist_rec) {
                    $UIDList[] = $Mail_acclist_rec['STRUID'];
                }
            }
            unset($Mail_acclist_tab);
            $this->lastExitCode = 0;
            $this->lastMessage = "";
            return $UIDList;
        } catch (Exception $e) {
            $this->lastExitCode = -1;
            $this->lastMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * 
     * @param int $rowid
     * @return array
     */
    public function getMailArchivioForRowid($rowid) {
        return $this->emlLib->getMailArchivio($rowid, "ROWID");
    }

    /**
     * path file eml dato un rowid della tabella mail_archivio
     * @param integer $rowid
     * @return string path del data file 
     */
    public function getEmlForROWId($rowid) {
        $Mail_archivio_rec = $this->emlLib->getMailArchivio($rowid, "ROWID");
        return $this->emlLib->SetDirectory($Mail_archivio_rec['ACCOUNT']) . $Mail_archivio_rec['DATAFILE'];
    }

    /**
     * 
     * @param type $fileEml
     * @param type $messageUID
     * @param type $class
     * @param type $sendrec
     * @return boolean
     */
    public function insertMessageFromEml($fileEml, $messageUID = '', $class = '', $sendrec = 'X') {

        $this->insertedRec = null;
        $this->insertedEml = null;

        if (!$this->account) {
            $this->lastExitCode = -1;
            $this->lastMessage = "Account non definito";
            return false;
        }
        $messageObj = new emlMessage();
        $messageObj->setEmlFile($fileEml);
        $retParse = $messageObj->parseEmlFileDeep();
        if ($retParse === false) {
            $this->lastExitCode = -2;
            $this->lastMessage = $messageObj->getLastMessage();
            return false;
        }

        $emlStruct = $messageObj->getStruct();

        $this->initInsertStack();

        $Mail_archivio_rec = array();
        $Mail_archivio_rec['IDMAIL'] = $this->account . "-" . $emlStruct['FromAddress'] . "-" . $emlStruct['Date'] . "-" . $emlStruct['Message-Id'] . "-" . $sendrec;
        $Mail_archivio_rec['ACCOUNT'] = $this->account;
        $Mail_archivio_rec['MSGID'] = $emlStruct['Message-Id'];
        $Mail_archivio_rec['STRUID'] = $messageUID;
        $Mail_archivio_rec['FROMADDR'] = $emlStruct['FromAddress'];

        $temp_to = array();
        foreach ($emlStruct['To'] as $To) {
            $temp_to[] = $To['address'];
        }
        $Mail_archivio_rec['TOADDR'] = implode("|", $temp_to);

        $temp_cc = array();
        foreach ($emlStruct['Cc'] as $Cc) {
            $temp_cc[] = $Cc['address'];
        }
        $Mail_archivio_rec['CCADDR'] = implode("|", $temp_cc);

        $temp_bcc = array();
        foreach ($emlStruct['Bcc'] as $Bcc) {
            $temp_bcc[] = $Bcc['address'];
        }
        $Mail_archivio_rec['BCCADDR'] = implode("|", $temp_bcc);

        $Mail_archivio_rec['SUBJECT'] = $emlStruct['Subject'];
        $decodedDate = emlDate::eDate2Date($emlStruct['Date']);
        $Mail_archivio_rec['MSGDATE'] = $decodedDate['date'] . " " . $decodedDate['time'];
        $Mail_archivio_rec['CLASS'] = $class;

        $destFile = md5($Mail_archivio_rec['IDMAIL']) . ".eml";
        $Mail_archivio_rec['DATAFILE'] = $destFile;
        $Mail_archivio_rec['METADATA'] = "";
        $temp_attachments = array();
        foreach ($emlStruct['Attachments'] as $Attachment) {
            $temp_attachments[] = $Attachment['FileName'];
        }
        $Mail_archivio_rec['ATTACHMENTS'] = implode("|", $temp_attachments);

        $Mail_archivio_rec['BODYTEXT'] = $emlStruct['Data'];
        $Mail_archivio_rec['SENDREC'] = $sendrec;
        $Mail_archivio_rec['READED'] = false;

        $Mail_archivio_rec['INTEROPERABILE'] = 0;
        $Mail_archivio_rec['TIPOINTEROPERABILE'] = '';
        $Mail_archivio_rec['PECTIPO'] = "";
        $Mail_archivio_rec['PECERRORE'] = "";
        $Mail_archivio_rec['IDMAILPADRE'] = "";
        $Mail_archivio_rec['METADATA'] = "";
        $AllegatiCtr = array();
        $FlInterop = 0;
        if ($emlStruct['ita_PEC_info'] != 'N/A') {
            $Mail_archivio_rec['PECTIPO'] = $emlStruct['ita_PEC_info']['dati_certificazione']['tipo'];
            if (isset($emlStruct['ita_PEC_info']['dati_certificazione']['errore'])) {
                $Mail_archivio_rec['PECERRORE'] = $emlStruct['ita_PEC_info']['dati_certificazione']['errore'];
            }
            if (isset($emlStruct['ita_PEC_info']['dati_certificazione']['errore-esteso'])) {
                $Mail_archivio_rec['PECERROREESTESO'] = $emlStruct['ita_PEC_info']['dati_certificazione']['errore-esteso'];
            }
            if (isset($emlStruct['ita_PEC_info']['messaggio_originale']['ParsedFile']['Attachments'])) {
                $AllegatiCtr = $emlStruct['ita_PEC_info']['messaggio_originale']['ParsedFile']['Attachments'];
                $FlInterop = 2;
            }
        } else {
            if (isset($emlStruct['Attachments'])) {
                $AllegatiCtr = $emlStruct['Attachments'];
                $FlInterop = 1;
            }
        }
        /*
         * Controllo Tipi Messaggi Interoperabili
         */
        if ($Mail_archivio_rec['PECTIPO'] == '' || $Mail_archivio_rec['PECTIPO'] == emlMessage::PEC_TIPO_POSTA_CERTIFICATA) {
            foreach ($AllegatiCtr as $Allegato) {
                switch (strtolower($Allegato['FileName'])) {
                    case 'segnatura.xml':
                        $Mail_archivio_rec['INTEROPERABILE'] = $FlInterop;
                        $Mail_archivio_rec['TIPOINTEROPERABILE'] = emlLib::TIPOMSG_SEGNATURA;
                        break;
                    case 'conferma.xml':
                        $Mail_archivio_rec['INTEROPERABILE'] = $FlInterop;
                        $Mail_archivio_rec['TIPOINTEROPERABILE'] = emlLib::TIPOMSG_CONFERMA;
                        break;
                    case 'aggiornamento.xml':
                        $Mail_archivio_rec['INTEROPERABILE'] = $FlInterop;
                        $Mail_archivio_rec['TIPOINTEROPERABILE'] = emlLib::TIPOMSG_AGGIORNAMENTO;
                        break;
                    case 'eccezione.xml':
                        $Mail_archivio_rec['INTEROPERABILE'] = $FlInterop;
                        $Mail_archivio_rec['TIPOINTEROPERABILE'] = emlLib::TIPOMSG_ECCEZIONE;
                        break;
                    case 'annullamento.xml':
                        $Mail_archivio_rec['INTEROPERABILE'] = $FlInterop;
                        $Mail_archivio_rec['TIPOINTEROPERABILE'] = emlLib::TIPOMSG_ANNULLAMENTO;
                        break;
                    default :
                        break;
                }
            }
        }

        $Mail_archivio_rec['METADATA'] = serialize(array("emlStruct" => $emlStruct));
        // Ora e data di Ricezione
        $Mail_archivio_rec['SENDRECDATE'] = date('Ymd');
        $Mail_archivio_rec['SENDRECTIME'] = date('H:i:s');

        $messageObj->cleanData();

        try {
            $nrows = itaDB::DBInsert($this->emlLib->getITALWEB(), "MAIL_ARCHIVIO", "ROWID", $Mail_archivio_rec);
            if ($nrows <= 0) {
                $this->lastExitCode = -2;
                $this->lastMessage = "Inserimento Messaggio fallito";
                $this->undoInsertMessage($Mail_archivio_rec['IDMAIL'], $this->lastMessage);
                return false;
            }
        } catch (Exception $e) {
            $this->undoInsertMessage();
            $this->lastExitCode = -2;
            $this->lastMessage = $e->getMessage();
            return false;
        }
        $Mail_archivio_rec['ROWID'] = ItaDB::DBLastId($this->emlLib->getITALWEB());
        $this->pushInsertStack('MAIL_ARCHIVIO', $Mail_archivio_rec['IDMAIL']);
        $fileDest = $this->emlLib->SetDirectory($Mail_archivio_rec['ACCOUNT']) . $Mail_archivio_rec['DATAFILE'];
        if (!@copy($fileEml, $fileDest)) {
            $this->undoInsertMessage($Mail_archivio_rec['IDMAIL']);
            $this->lastExitCode = -5;
            $this->lastMessage = "Salvataggio dati messaggio Fallito. $fileEml -> $fileDest ";
            return false;
        }
        $this->pushInsertStack('COPY_EML', $this->emlLib->SetDirectory($Mail_archivio_rec['ACCOUNT']) . $Mail_archivio_rec['DATAFILE']);

        if ($messageUID) {
            if (!$this->insertListUID($messageUID)) {
                $this->undoInsertMessage($Mail_archivio_rec['IDMAIL']);
                $this->lastExitCode = -4;
                $this->lastMessage = "Inserimento UID mailbox fallito.";
                return false;
            }
            $this->pushInsertStack('UID_SYNC', $messageUID);
        }
        $this->eqAudit->logEqEvent($this, array(
            'Operazione' => eqAudit::OP_INS_RECORD,
            'DB' => $this->MAIL_DB->getDB(),
            'DSet' => 'MAIL_ARCHIVIO',
            'Estremi' => $this->account . ": Tipo: $sendrec - Inserito messaggio: " . $Mail_archivio_rec['IDMAIL']
        ));
        $this->insertedRec = $Mail_archivio_rec;
        $this->insertedEml = $messageObj;
        $this->lastExitCode = 0;
        $this->lastMessage = "";
        return true;
    }

    /**
     * Aggiorna la classificazione di un record della tabella mail_archivio
     * @param type $rowid
     * @param type $class
     * @return boolean
     */
    public function updateClassForRowId($rowid, $class) {
        $Mail_archivio_rec = array();
        $Mail_archivio_rec['ROWID'] = $rowid;
        $Mail_archivio_rec['CLASS'] = $class;
        return $this->updateMailArchivio($Mail_archivio_rec);
    }

    /**
     * Aggiorna id mail padre di un record della tabella mail_archivio
     * @param type $rowid
     * @param type $idmailpadre
     * @return boolean
     */
    public function updatelParentForRowId($rowid, $idmailpadre) {
        $Mail_archivio_rec = array();
        $Mail_archivio_rec['ROWID'] = $rowid;
        $Mail_archivio_rec['IDMAILPADRE'] = $idmailpadre;
        return $this->updateMailArchivio($Mail_archivio_rec);
    }

    private function updateMailArchivio($Mail_archivio_rec) {
        try {
            $nrows = itaDb::DBUpdate($this->MAIL_DB, "MAIL_ARCHIVIO", "ROWID", $Mail_archivio_rec);
            if ($nrows === -1) {
                $this->eqAudit->logEqEvent($this, array(
                    'Operazione' => eqAudit::OP_UPD_RECORD_FAILED,
                    'DB' => $this->MAIL_DB->getDB(),
                    'DSet' => 'MAIL_ARCHIVIO',
                    'Estremi' => 'Aggiornamento messagio mail fallito: rowid ' . $Mail_archivio_rec['ROWID']
                ));
                $this->lastExitCode = -1;
                $this->lastMessage = "Aggiornamento messaggio fallito";
                return false;
            } else {
                $this->eqAudit->logEqEvent($this, array(
                    'Operazione' => eqAudit::OP_UPD_RECORD,
                    'DB' => $this->MAIL_DB->getDB(),
                    'DSet' => 'MAIL_ARCHIVIO',
                    'Estremi' => 'Aggiornamento messagio mail eseguito: rowid ' . $Mail_archivio_rec['ROWID']
                ));
                $this->lastExitCode = 0;
                $this->lastMessage = "";
                return true;
            }
        } catch (Exception $e) {
            $this->eqAudit->logEqEvent($this, array(
                'Operazione' => eqAudit::OP_UPD_RECORD_FAILED,
                'DB' => $this->MAIL_DB->getDB(),
                'DSet' => 'MAIL_ARCHIVIO',
                'Estremi' => 'Aggiornamento messagio mail fallito: rowid ' . $Mail_archivio_rec['ROWID']
            ));
            $this->lastExitCode = -1;
            $this->lastMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * Cancella un messaggio dalla tabella mail-archivio dalla cartella dati dato un rowid
     * @param type $messageId
     * @return boolean
     */
    private function deleteMessageForId($messageId) {
        $Mail_archivio_rec = itaDB::DBSQLSelect($this->MAIL_DB, "SELECT * FROM MAIL_ARCHIVIO WHERE IDMAIL = '$messageId'", false);
        if ($Mail_archivio_rec) {
            try {
                $nrows = itaDB::DBDelete($this->MAIL_DB, "MAIL_ARCHIVIO", "ROWID", $Mail_archivio_rec['ROWID']);
                if ($nrows <= 0) {
                    $this->lastExitCode = -1;
                    $this->lastMessage = "Eliminazione Messaggio fallito";
                    return false;
                }
            } catch (Exception $e) {
                $this->lastExitCode = -1;
                $this->lastMessage = $e->getMessage();
                return false;
            }
        } else {
            $this->lastExitCode = -1;
            $this->lastMessage = "Messaggio da cancellare non esistente.";
            return false;
        }
        $this->lastExitCode = 0;
        $this->lastMessage = "";
        return true;
    }

    /**
     * Inserisce un mail UID nell'archivio MAIL_ACCLIST
     * @param string $messageUID
     * @return boolean
     */
    public function insertListUID($messageUID, $recDate = '') {
        if (!$this->account) {
            $this->lastExitCode = -1;
            $this->lastMessage = "Account non definito";
            return false;
        }
        if ($recDate == '') {
            $recDate = date('Ymd');
        }
        $Mail_acclist_rec = array();
        $Mail_acclist_rec['ACCOUNT'] = $this->account;
        $Mail_acclist_rec['STRUID'] = $messageUID;
        $Mail_acclist_rec['RECDATE'] = $recDate;
        try {
            $nrows = itaDB::DBInsert($this->emlLib->getITALWEB(), "MAIL_ACCLIST", "ROWID", $Mail_acclist_rec);
            if ($nrows <= 0) {
                $this->lastExitCode = -2;
                $this->lastMessage = "Inseriemnto UID Messaggio fallito<br>" . print_r($Mail_acclist_rec, true);
                return false;
            }
        } catch (Exception $e) {
            $this->lastExitCode = -2;
            $this->lastMessage = $e->getMessage();
            return false;
        }
        $this->lastExitCode = 0;
        $this->lastMessage = "";
        return true;
    }

    /**
     * Cancella un mail UID nell'archivio MAIL_ACCLIST
     * @param  string  $messageUID
     * @return boolean
     */
    public function deleteListUID($messageUID, $account = '') {
        $Mail_acclist_rec = itaDB::DBSQLSelect($this->MAIL_DB, "SELECT * FROM MAIL_ACCLIST WHERE STRUID = '$messageUID' AND ACCOUNT = '$account' ", false);
        if ($Mail_acclist_rec) {
            try {
                $nrows = itaDB::DBDelete($this->MAIL_DB, "MAIL_ACCLIST", "ROWID", $Mail_acclist_rec['ROWID']);
                if ($nrows <= 0) {
                    $this->lastExitCode = -1;
                    $this->lastMessage = "Eliminiazione UID Messaggio fallito";
                    return false;
                }
            } catch (Exception $e) {
                $this->lastExitCode = -1;
                $this->lastMessage = $e->getMessage();
                return false;
            }
        } else {
            $this->lastExitCode = -1;
            $this->lastMessage = "UID da cancellare non esistente.";
            return false;
        }
        $this->lastExitCode = 0;
        $this->lastMessage = "";
        return true;
    }

    private function initInsertStack() {
        $this->insertStack = array();
    }

    private function pushInsertStack($key, $value) {
        $this->insertStack[] = array(
            'key' => $key,
            'value' => $value);
    }

    private function undoInsertMessage($messageId) {
        $this->eqAudit->logEqEvent($this, array(
            'Operazione' => eqAudit::OP_MISC_AUDIT,
            'DB' => '',
            'DSet' => '',
            'Estremi' => 'Annullamento transazione di inserimento mail: ' . $messageId
        ));
        foreach (array_reverse($this->insertStack) as $record) {
            switch ($record['key']) {
                case 'UID_SYNC':
                    $ret_del = $this->deleteListUID($record['value']);
                    if (!$ret_del) {
                        return false;
                    }
                    break;
                case 'COPY_EML':
                    if (!@unlink($record['value'])) {
                        return false;
                    }
                    return true;
                    break;
                case 'MAIL_ARCHIVIO':
                    $ret_del_msg = $this->deleteMessageForId($record['value']);
                    if (!$ret_del_msg) {
                        return false;
                    }
                    break;
            }
        }
        $this->eqAudit->logEqEvent($this, array(
            'Operazione' => eqAudit::OP_MISC_AUDIT,
            'DB' => '',
            'DSet' => '',
            'Estremi' => 'Annullamento transazione di inserimento mail: ' . $messageId . 'eseguita con successo'
        ));
        return true;
    }

}

?>