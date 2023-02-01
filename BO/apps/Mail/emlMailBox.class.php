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
 * @version    23.06.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once (ITA_BASE_PATH . '/apps/Mail/emlLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Mail/emlRemoteMailBox.class.php');
include_once (ITA_BASE_PATH . '/apps/Mail/emlOutgoingMessage.class.php');
include_once (ITA_BASE_PATH . '/apps/Mail/emlDbMailBox.class.php');
include_once (ITA_BASE_PATH . '/apps/Mail/emlDate.class.php');
include_once (ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php');
include_once (ITA_LIB_PATH . '/itaPHPMail/itaMime.class.php');
include_once (ITA_LIB_PATH . '/itaPHPCore/itaMailer.class.php');
include_once (ITA_LIB_PATH . '/QXml/QXml.class.php');

class emlMailBox {

    private $account;
    private $userOutgoingAccount = false;
    private $account_name;
    private $account_user;
    private $account_pass;
    private $account_delay;
    private $pop3_host;
    private $pop3_port;
    private $pop3_secure;
    private $pop3_realm;
    private $pop3_authm;
    private $pop3_workst;
    private $pop3_delmsg;
    private $pop3_delwait;
    private $smtp_host;
    private $smtp_port;
    private $smtp_secure;
    private $customHeaders = array();
    private $isPEC;
    private $emlLib;
    private $lastExitCode;
    private $lastMessage;
    private $MAIL_DB;
    private $tempPath;
    private $state;

    /**
     * Restituisce l'oggetto emlMailBox in funzione dell'account fornito
     * 
     * @param string $account Indirizzo mail account da istanziare
     * @return boolean|\emlMailBox false se errore, oggetto emlMailbox se ok
     */
    public static function getInstance($account = null) {
        try {
            $emlMailBox = new emlMailBox($account, true);
        } catch (Exception $e) {
            return false;
        }
        try {
            $emlMailBox->emlLib = new emlLib();
            $emlMailBox->MAIL_DB = $emlMailBox->emlLib->getITALWEB();
        } catch (Exception $e) {
            return false;
        }
        if ($account) {
            if (!$emlMailBox->setAccount($account)) {
                return false;
            }
        }
        return $emlMailBox;
    }

    /**
     * Restituisce l'oggetto emlMailBox in base all'account definito per il profilo utente
     * 
     * @return boolean|\emlMailBox false se errore, oggetto emlMailbox se ok
     */
    public static function getUserAccountInstance() {
        include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
        $accLib = new accLib();
        $parametriUser = $accLib->GetParamentriMail();
        $account = $parametriUser['FROM'];
        if (!$account) {
            return false;
        }

        try {
            $emlMailBox = new emlMailBox($account, true);
        } catch (Exception $e) {
            return false;
        }
        try {
            $emlMailBox->emlLib = new emlLib();
            $emlMailBox->MAIL_DB = $emlMailBox->emlLib->getITALWEB();
        } catch (Exception $e) {
            return false;
        }
        $Mail_account_rec = $emlMailBox->emlLib->getMailAccount($account);
        if ($Mail_account_rec) {
            if (!$emlMailBox->setAccount($account)) {
                return false;
            }
        } else {
            if (
                    $parametriUser['FROM'] == '' ||
                    $parametriUser['NAME'] == '' ||
                    $parametriUser['HOST'] == '' ||
                    $parametriUser['USERNAME'] == '' ||
                    //$parametriUser['PASSWORD'] == '' ||
                    $parametriUser['PORT'] == ''
            ) {
                return false;
            } else {
                $emlMailBox->setUserOutgoingAccount(true);
                $emlMailBox->account = $account;
                $emlMailBox->account_name = $parametriUser['NAME'];
                $emlMailBox->account_user = $parametriUser['USERNAME'];
                $emlMailBox->account_pass = $parametriUser['PASSWORD'];
                $emlMailBox->pop3_host = null;
                $emlMailBox->pop3_port = null;
                $emlMailBox->pop3_secure = null;
                $emlMailBox->pop3_realm = null;
                $emlMailBox->pop3_authm = null;
                $emlMailBox->pop3_workst = null;
                $emlMailBox->pop3_delmsg = null;
                $emlMailBox->pop3_delwait = null;

                $emlMailBox->smtp_host = $parametriUser['HOST'];
                $emlMailBox->smtp_port = $parametriUser['PORT'];
                $emlMailBox->smtp_secure = $parametriUser['SMTPSECURE'];
                $emlMailBox->customHeaders = null;
                $emlMailBox->isPEC = null;
                $emlMailBox->tempPath = null;
                //return true;
            }
        }
        return $emlMailBox;
    }

    public function __construct($account = null, $getInstance = false) {
        if ($getInstance === false) {
            try {
                $this->emlLib = new emlLib();
                $this->MAIL_DB = $this->emlLib->getITALWEB();
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
            if ($account) {
                $this->setAccount($account);
            }
        }
    }

    public function newEmlOutgoingMessage() {
        try {
            $OutgoingMsg = new emlOutgoingMessage();

            if (!$this->account) {
                $this->setLastExitCode(-1);
                $this->setLastMessage("Account per i pramatri di invio non configurato");
                return false;
            }

            $OutgoingMsg->setFrom($this->getAccount());
            $OutgoingMsg->setFromName($this->account_name);
            $OutgoingMsg->setCustomHeaders($this->customHeaders);
        } catch (Exception $exc) {
            $this->setLastExitCode(-1);
            $this->setLastMessage("Errore in creazine messaggio in uscita");
            return false;
        }
        return $OutgoingMsg;
    }

    public function setLastExitCode($lastExitCode) {
        $this->lastExitCode = $lastExitCode;
    }

    public function setLastMessage($lastMessage) {
        $this->lastMessage = $lastMessage;
    }

    public function getAccount_name() {
        return $this->account_name;
    }

    public function getUserOutgoingAccount() {
        return $this->userOutgoingAccount;
    }

    public function setUserOutgoingAccount($userOutgoingAccount) {
        $this->userOutgoingAccount = $userOutgoingAccount;
    }

    public function setAccount_name($account_name) {
        $this->account_name = $account_name;
    }

    public function getAccount_user() {
        return $this->account_user;
    }

    public function setAccount_user($account_user) {
        $this->account_user = $account_user;
    }

    public function getAccount_pass() {
        return $this->account_pass;
    }

    public function setAccount_pass($account_pass) {
        $this->account_pass = $account_pass;
    }

    public function getAccount_delay() {
        return $this->account_delay;
    }

    public function setAccount_delay($account_delay) {
        $this->account_delay = $account_delay;
    }

    public function getPop3_host() {
        return $this->pop3_host;
    }

    public function setPop3_host($pop3_host) {
        $this->pop3_host = $pop3_host;
    }

    public function getPop3_port() {
        return $this->pop3_port;
    }

    public function setPop3_port($pop3_port) {
        $this->pop3_port = $pop3_port;
    }

    public function getPop3_secure() {
        return $this->pop3_secure;
    }

    public function setPop3_secure($pop3_secure) {
        $this->pop3_secure = $pop3_secure;
    }

    public function getPop3_realm() {
        return $this->pop3_realm;
    }

    public function setPop3_realm($pop3_realm) {
        $this->pop3_realm = $pop3_realm;
    }

    public function getPop3_authm() {
        return $this->pop3_authm;
    }

    public function setPop3_authm($pop3_authm) {
        $this->pop3_authm = $pop3_authm;
    }

    public function getPop3_workst() {
        return $this->pop3_workst;
    }

    public function setPop3_workst($pop3_workst) {
        $this->pop3_workst = $pop3_workst;
    }

    public function getPop3_delmsg() {
        return $this->pop3_delmsg;
    }

    public function setPop3_delmsg($pop3_delmsg) {
        $this->pop3_delmsg = $pop3_delmsg;
    }

    public function getPop3_delwait() {
        return $this->pop3_delwait;
    }

    public function setPop3_delwait($pop3_delwait) {
        $this->pop3_delwait = $pop3_delwait;
    }

    public function getSmtp_host() {
        return $this->smtp_host;
    }

    public function setSmtp_host($smtp_host) {
        $this->smtp_host = $smtp_host;
    }

    public function getSmtp_port() {
        return $this->smtp_port;
    }

    public function setSmtp_port($smtp_port) {
        $this->smtp_port = $smtp_port;
    }

    public function getSmtp_secure() {
        return $this->smtp_secure;
    }

    public function setSmtp_secure($smtp_secure) {
        $this->smtp_secure = $smtp_secure;
    }

    public function getCustomHeaders() {
        return $this->customHeaders;
    }

    public function setCustomHeaders($customHeaders) {
        $this->customHeaders = $customHeaders;
    }

    /**
     * 
     * @param type $key
     * @param type $value
     */
    public function setCustomHeader($key, $value) {
        if (!$this->customHeaders) {
            $this->customHeaders = array();
        }
        if ($value) {
            $this->customHeaders[$key] = $value;
        } else {
            unset($this->customHeaders[$key]);
        }
    }

    public function getIsPEC() {
        return $this->isPEC;
    }

    public function setIsPEC($isPEC) {
        $this->isPEC = $isPEC;
    }

    public function getEmlLib() {
        return $this->emlLib;
    }

    public function setEmlLib($emlLib) {
        $this->emlLib = $emlLib;
    }

    public function getMAIL_DB() {
        return $this->MAIL_DB;
    }

    public function setMAIL_DB($MAIL_DB) {
        $this->MAIL_DB = $MAIL_DB;
    }

    public function getTempPath() {
        return $this->tempPath;
    }

    public function setTempPath($tempPath) {
        $this->tempPath = $tempPath;
    }

    public function getState() {
        return $this->state;
    }

    public function setState($state) {
        $this->state = $state;
    }

    /**
     * Assegna il nome account e tutti i parametri da anagrafica account
     * @param type $account Nome Account
     * @return boolean true account assegnato, false errore assgnazione
     */
    public function setAccount($account) {
        $Mail_account_rec = $this->emlLib->getMailAccount($account);
        if ($Mail_account_rec) {
            $this->userOutgoingAccount = false;
            $this->account = $account;
            $this->account_name = $Mail_account_rec['NAME'];
            $this->account_user = $Mail_account_rec['USER'];
            $this->account_pass = $Mail_account_rec['PASSWORD'];
            $this->account_delay = $Mail_account_rec['SPOOLSENDDELAY'];
            $this->pop3_host = $Mail_account_rec['POP3HOST'];
            $this->pop3_port = $Mail_account_rec['POP3PORT'];
            $this->pop3_secure = $Mail_account_rec['POP3SECURE'];
            $this->pop3_realm = $Mail_account_rec['POP3REALM'];
            $this->pop3_authm = $Mail_account_rec['POP3AUTHM'];
            if (!$this->pop3_authm) {
                $this->pop3_authm = "USER";
            }
            $this->pop3_workst = $Mail_account_rec['WORKST'];
            $this->pop3_delmsg = $Mail_account_rec['DELMSG'];
            $this->pop3_delwait = $Mail_account_rec['DELWAIT'];

            $this->smtp_host = $Mail_account_rec['SMTPHOST'];
            $this->smtp_port = $Mail_account_rec['SMTPPORT'];
            $this->smtp_secure = $Mail_account_rec['SMTPSECURE'];
            $this->customHeaders = unserialize($Mail_account_rec['CUSTOMHEADERS']);
            $this->isPEC = $Mail_account_rec['ISPEC'];

            $this->tempPath = itaLib::getAppsTempPath($this->account);
            $retDel = itaLib::deleteDirRecursive($this->tempPath);
            if ($retDel == false) {
                $this->setLastMessage("Attenzione: " . $this->tempPath . " Non cancellata");
            }
            $this->tempPath = itaLib::createAppsTempPath($this->account);
            return true;
        } else {
            $this->account = null;
            $this->account_user = null;
            $this->account_pass = null;
            $this->account_delay = null;
            $this->pop3_host = null;
            $this->pop3_port = null;
            $this->pop3_secure = null;
            $this->pop3_realm = null;
            $this->pop3_authm = null;
            $this->pop3_workst = null;
            $this->pop3_delmsg = $Mail_account_rec['DELMSG'];
            $this->pop3_delwait = $Mail_account_rec['DELWAIT'];
            $this->smtp_host = null;
            $this->smtp_port = null;
            $this->smtp_secure = null;
            $this->setLastExitCode(-1);
            $this->setLastMessage("Account mail inesistente");
            return false;
        }
    }

    public function getAccount() {
        return $this->account;
    }

    public function getLastExitCode() {
        return $this->lastExitCode;
    }

    public function getLastMessage() {
        return $this->lastMessage;
    }

    /**
     * 
     * @param emlOutgoingMessage $outgoingMessage
     * @param type $parametri
     * @return boolean
     */
    public function sendMessage($outgoingMessage, $parametri = false, $saveSent = true) {
        if ($parametri == false) {
            $parametri['FROM'] = $this->account;
            $parametri['NAME'] = $this->account_name;
            $parametri['HOST'] = $this->smtp_host;
            $parametri['USERNAME'] = $this->account_user;
            $parametri['PASSWORD'] = $this->account_pass;
            $parametri['DELAY'] = $this->account_delay;
            $parametri['PORT'] = $this->smtp_port;
            $parametri['SMTPSECURE'] = $this->smtp_secure;
            $parametri['CUSTOMHEADERS_ARRAY'] = $this->customHeaders;
        } else {
            $this->account = $parametri['FROM'];
        }
        if (is_a($outgoingMessage, 'emlOutgoingMessage')) {
            $messageArray = $outgoingMessage->getMessageArray();
        } else {
            $messageArray = $outgoingMessage;
        }

        $itaMailer = new itaMailer();
        $itaMailer->FromName = $messageArray['FromName'];
        $itaMailer->Subject = $messageArray['Subject'];
        $itaMailer->From = $messageArray['From'];
        $customHeaders = array_merge((array) $parametri['CUSTOMHEADERS_ARRAY'], (array) $messageArray['CustomHeaders']);
        if ($customHeaders) {
            foreach ($customHeaders as $key => $customHeader) {
                $itaMailer->addCustomHeader($key . ": " . $customHeader);
            }
        }

        $itaMailer->IsHTML();
        $itaMailer->Body = $messageArray['Body'];

        $itaMailer->itaAddAddress($messageArray['Email']);
        $itaMailer->itaAddCCAddress($messageArray['CCAddresses']);
        $itaMailer->itaAddBCCAddress($messageArray['BCCAddresses']);
        $allegati = $messageArray['Attachments'];
        foreach ($allegati as $allegato) {
            $fileOrig = $allegato['FILEORIG'];
            $itaMailer->AddAttachment($allegato['FILEPATH'], "$fileOrig");
        }
        if ($parametri['DELAY']) {
            sleep($parametri['DELAY']);
        }
        if ($itaMailer->Send($parametri)) {
            if (!$saveSent) {
                $this->lastExitCode = 0;
                $this->lastMessage = "";
                return true;
            }

            $fileEml = $this->saveSentMessage($itaMailer->GetSentMIMEMessage());
            if ($fileEml) {
                $dbMailBox = emlDbMailBox::getDbMailBoxInstance($this->account);
                if (!is_object($dbMailBox)) {
                    $this->lastExitCode = -1;
                    $this->lastMessage = "Apertura mail box locale su db per l'account: " . $this->account . " fallita.";
                    return false;
                }
                if (!$dbMailBox->insertMessageFromEml($fileEml, "", "", "S")) {
                    $this->lastExitCode = -2;
                    $this->lastMessage = $dbMailBox->getLastMessage();
                    return false;
                }
                unlink($fileEml);
                $this->lastExitCode = 0;
                $this->lastMessage = "";
                return $dbMailBox->getInsertedRec();
            } else {
                return false;
            }
        } else {
            $this->lastExitCode = -1;
            $this->lastMessage = $itaMailer->ErrorInfo;
            return false;
        }
    }

    /**
     * Scarica la posta dalla mail box dell'account e sincronizza l'archivio itaEngine
     * 
     * @param string $class classifica il messaggio in arrivo per le applicazioni al livello superiore.
     * @return boolean
     */
    public function syncronizeAccount($class = '') {
        /*
         * Istanzio Oggetto Audit di itaEngine
         */
        $audit = new eqAudit();
        $audit->logEqEvent($this, array(
            'Operazione' => '99',
            'DB' => '',
            'DSet' => '',
            'Estremi' => $this->account . ": controllo nuovi messaggi."
        ));
        /*
         *  Apro la remote Malbox per l'account
         */
        //$remoteMailBox = emlRemoteMailBox::getRemoteMailBoxInstance($this->account);
        try {
            $remoteMailBox = emlRemoteMailBox::getRemoteMailBoxInstance($this->account);
        } catch (Exception $exc) {
            $this->lastExitCode = -1;
            $this->lastMessage = "Prima Apertura per l'account {$this->account} MailBox: " . $exc->getMessage();
            return false;
        }
        if (!is_object($remoteMailBox)) {
            $this->lastExitCode = -1;
            $this->lastMessage = "Prima Apertura MailBox: remota per l'account: " . $this->account . " fallita.";
            return false;
        }
        /*
         *  Istanzio il gestore della mailbox locale su db per l'account
         */
        $dbMailBox = emlDbMailBox::getDbMailBoxInstance($this->account);
        if (!is_object($dbMailBox)) {
            $this->lastExitCode = -1;
            $this->lastMessage = "Apertura mail box locale su db per l'account: " . $this->account . " fallita.";
            return false;
        }
        /*
         *  Estraggo dati per comparazione mailbox locale e remota
         */
        $downloadUIDList = array();
        $remoteUIDList = array();
        $dbUIDList = array();
        $remoteUIDList = $remoteMailBox->getUIDList();
        if ($remoteUIDList === false) {
            $this->lastExitCode = -1;
            $this->lastMessage = $remoteMailBox->getLastMessage();
            return false;
        }
        $dbUIDList = $dbMailBox->getUIDList();
        if ($dbUIDList === false) {
            $this->lastExitCode = -1;
            $this->lastMessage = $dbUIDList->getLastMessage();
            return false;
        }
        /*
         *  Sincronizzo: scarico messaggi non presenti in locale da maildrop remota 
         */
        $downloadUIDList = array_diff($remoteUIDList, $dbUIDList);
        foreach ($downloadUIDList as $key => $messageUID) {
            $fileEml = $remoteMailBox->getMessagefromIntID($key);
            if ($fileEml === false) {
                $this->lastExitCode = -1;
                $this->lastMessage = $remoteMailBox->getLastMessage();
                return false;
            }
            if (!$dbMailBox->insertMessageFromEml($fileEml, $messageUID, $class, "R")) {
                $this->lastExitCode = -1;
                $this->lastMessage = $dbMailBox->getLastMessage();
                return false;
            }

            $lastInsertedRec = $dbMailBox->getInsertedRec();
            $lastInsertedEml = $dbMailBox->getInsertedEml();

            /*
             *  Azioni di filtro/abbinamento base
             */
            if ($lastInsertedEml->isPEC()) {
                switch ($lastInsertedEml->getCertificazione('tipo')) {
                    case emlMessage::PEC_TIPO_ACCETTAZIONE:
                    case emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA:
                    case emlMessage::PEC_TIPO_ERRORE_CONSEGNA:
                    case emlMessage::PEC_TIPO_NON_ACCETTAZIONE:
                    case emlMessage::PEC_TIPO_PREAVVISO_ERRORE_CONSEGNA:
                    case emlMessage::PEC_TIPO_RILEVAZIONE_VIRUS:
                    case emlMessage::PEC_TIPO_PRESA_IN_CARICO:
                        $lastMsgId = $lastInsertedEml->getCertificazione("msgid");
                        $parentMailArchivio = $this->emlLib->getMailArchivio($lastMsgId, 'msgid');
                        if ($parentMailArchivio) {
                            /*
                             *  Rileggo per prendere il rowid
                             */
                            $lastInsertedRec = $this->emlLib->getMailArchivio($lastInsertedRec['IDMAIL']);
                            if (!$dbMailBox->updatelParentForRowId($lastInsertedRec['ROWID'], $parentMailArchivio['IDMAIL'])) {
                                $this->lastExitCode = -1;
                                $this->lastMessage = $dbMailBox->getLastMessage();
                                return false;
                            }
                        }
                        break;
                }
            }
        }
        /*
         *   Sincronizzo: cancello UID LOCALI MANCATI Su MAILDROP REMOTA possibili cancellazioni estranee a itaEngine
         *   Es. web mail
         */
        $dbUIDList = array();
        $dbUIDList = $dbMailBox->getUIDList();
        if ($dbUIDList === false) {
            $this->lastExitCode = -1;
            $this->lastMessage = $dbUIDList->getLastMessage();
            return false;
        }
        $removedUIDList = array_diff($dbUIDList, $remoteUIDList);
        foreach ($removedUIDList as $key => $messageUID) {
            if (!$dbMailBox->deleteListUID($messageUID, $this->account)) {
                $this->lastExitCode = -1;
                $this->lastMessage = $dbMailBox->getLastMessage() . " Canc. Fase 1";
                return false;
            }
        }
        $audit->logEqEvent($this, array(
            'Operazione' => '99',
            'DB' => '',
            'DSet' => '',
            'Estremi' => $this->account . ": " . count($downloadUIDList) . ' nuovi messaggi scaricati con successo.'
        ));
        /*
         *  Estraggo da db UID LOCALI DA ELIMINARE PER SCADENZA GIORNI ATTESA CANCELLAZIONE
         */
        if ($this->pop3_delmsg == 1) {
            $audit->logEqEvent($this, array(
                'Operazione' => '99',
                'DB' => '',
                'DSet' => '',
                'Estremi' => $this->account . ": " . ' Inizio verifica Messaggi da cancellare su server.'
            ));
            $deleteIndex = $dbMailBox->getDeleteIndex($this->pop3_delwait);
            if ($deleteIndex == false) {
                $this->lastExitCode = -1;
                $this->lastMessage = $dbMailBox->getLastMessage();
                $remoteMailBox->closeMailBox();
                return false;
            }
            $remoteDeleteUIDList = array_diff($remoteUIDList, $deleteIndex['PACKEDLIST']);
            /*
             *  Cancello su MAILBOX remota
             */
            foreach ($remoteDeleteUIDList as $key => $UID) {
                if (!$remoteMailBox->deleteMessagefromIntId(array($key))) {
                    $this->lastExitCode = -1;
                    $this->lastMessage = $remoteMailBox->getLastMessage();
                    return false;
                }
                $audit->logEqEvent($this, array(
                    'Operazione' => '99',
                    'DB' => '',
                    'DSet' => '',
                    'Estremi' => $this->account . ": Cancellazione Messaggio Remoto " . $UID
                ));
            }
            /* }
             * Chiudo la mail box per effettuare il commit delle cancellazioni
             */
            $remoteMailBox->closeMailBox();

            /*
             *  Riapro la mail box per risincronizzare dopo le cancellazioni
             */
            $remoteMailBox = null;
            try {
                $remoteMailBox = emlRemoteMailBox::getRemoteMailBoxInstance($this->account);
            } catch (Exception $exc) {
                $this->lastExitCode = -1;
                $this->lastMessage = $exc->getMessage();
                return false;
            }
            if (!is_object($remoteMailBox)) {
                $this->lastExitCode = -1;
                $this->lastMessage = "Apertura mail box remota per l'account: " . $this->account . " fallita.";
                return false;
            }
            $remoteUIDList = array();
            $remoteUIDList = $remoteMailBox->getUIDList();
            if ($remoteUIDList === false) {
                $this->lastExitCode = -1;
                $this->lastMessage = $remoteMailBox->getLastMessage();
                return false;
            }
            $dbUIDList = $dbMailBox->getUIDList();
            if ($dbUIDList === false) {
                $this->lastExitCode = -1;
                $this->lastMessage = $dbUIDList->getLastMessage();
                return false;
            }
            /*
             *  Estrapolo UID LOCALI Mancanti
             */
            $removedUIDList = array_diff($dbUIDList, $remoteUIDList);
            foreach ($removedUIDList as $key => $messageUID) {
                if (!$dbMailBox->deleteListUID($messageUID, $this->account)) {
                    $this->lastExitCode = -1;
                    $this->lastMessage = $dbMailBox->getLastMessage();
                    return false;
                }
            }
            $audit->logEqEvent($this, array(
                'Operazione' => '99',
                'DB' => '',
                'DSet' => '',
                'Estremi' => $this->account . ": Cancellati" . count($remoteDeleteUIDList) . ' messaggi su server.'
            ));
        }
        $remoteMailBox->closeMailBox();

        $this->lastExitCode = 0;
        $this->lastMessage = "";
        return $downloadUIDList;
    }

    /**
     * 
     * @param type $UID STRUID Univoco del messaggio (vedi anche MAIL_ACCLIST)
     */
    public function deleteMessageFromServer($UID) {
        if (!$UID) {
            $this->lastExitCode = -1;
            $this->lastMessage = "UID Da rimuovere non indicato.";
            return false;
        }

        $dbMailBox = emlDbMailBox::getDbMailBoxInstance($this->account);
        if (!is_object($dbMailBox)) {
            $this->lastExitCode = -1;
            $this->lastMessage = "Apertura mail box locale su db per l'account: " . $this->account . " fallita.";
            return false;
        }

        $remoteMailBox = emlRemoteMailBox::getRemoteMailBoxInstance($this->account);
        if (!is_object($remoteMailBox)) {
            $this->lastExitCode = -1;
            $this->lastMessage = "Apertura mail box remota per l'account: " . $this->account . " fallita.";
            return false;
        }

        $remoteUIDList = $remoteMailBox->getUIDList();
        if ($remoteUIDList === false) {
            $this->lastExitCode = -1;
            $this->lastMessage = $remoteMailBox->getLastMessage();
            $remoteMailBox->closeMailBox();
            return false;
        }
        $id = array_search($UID, $remoteUIDList);
        if (!$id) {
            $remoteMailBox->closeMailBox();
            return true;
        }
        if (!$remoteMailBox->deleteMessagefromIntId(array($id))) {
            $this->lastExitCode = -1;
            $this->lastMessage = $remoteMailBox->getLastMessage();
            $remoteMailBox->closeMailBox();
            return false;
        }
        /*
         * Chiudo la mail box per effettuare il commit della cancellazione
         */
        $remoteMailBox->closeMailBox();
        /*
         * Cancello MAIL_ACCLIST LOCALE
         */
        if (!$dbMailBox->deleteListUID($UID, $this->account)) {
            $this->lastExitCode = -1;
            $this->lastMessage = $dbMailBox->getLastMessage();
            return false;
        }
        return true;
    }

    /**
     * 
     * @param type $rowid
     * @return \emlMessage
     */
    public function getMessageFromDb($rowid) {
        $dbMailBox = emlDbMailBox::getDbMailBoxInstance();
        //TODO:GESTIONE ERRORI
        $file = $dbMailBox->getEmlForROWId($rowid);
        $message = new emlMessage();
        $message->setEmlFile($file);
        return $message;
    }

    private function deleteTempAttachPath() {
        return itaLib::deleteDirRecursive($this->tempPath);
    }

    public function close() {
        return $this->deleteTempAttachPath();
    }

    public function saveSentMessage($mimeMessage) {
        $percorsoTmp = itaLib::getPrivateUploadPath();
        if (!@is_dir($percorsoTmp)) {
            if (!itaLib::createPrivateUploadPath()) {
//                App::log("Creazione ambiente di lavoro temporaneo fallita.");
                $this->lastExitCode = -3;
                $this->lastMessage = "Creazione ambiente di lavoro temporaneo fallita.";
                return false;
            }
        }
        $randName = md5(rand() * time()) . ".eml";
        if (file_put_contents($percorsoTmp . '/' . $randName, $mimeMessage)) {
            $this->lastExitCode = 0;
            $this->lastMessage = "";
            return $percorsoTmp . '/' . $randName;
        } else {
//            App::log("Copia della Email temporanea su ambiente di lavoro fallita.");
            $this->lastExitCode = -4;
            $this->lastMessage = "Copia della Email temporanea su ambiente di lavoro fallita.";
            return false;
        }
    }

    //
    // Custom header helpers

    //

    /**
     * Imposta il tipo di ricevuta di consegna PEC com breve
     * Non ritornato gli allegati originali ma l'HASH del file binario.
     * 
     */
    public function setPECRicvutaBreve() {
        $this->setCustomHeader(emlLib::CUST_HEAD_PEC_TIPO_RICEVUTA, emlLib::CUST_HEAD_PEC_TIPO_RICEVUTA_BREVE);
    }

}
