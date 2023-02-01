<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    12.05.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibCalendar.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

class envLib {

    /**
     * Libreria di funzioni Generiche e Utility per Ambiente
     */
    CONST TIPO_BLOCCO_CHIAVE = 'CHIAVE';
    CONST TIPO_BLOCCO_TABELLA = 'CHIAVE-TABELLE';
    CONST SEMAFORO_BLOCCA = "BLOCCA";
    CONST SEMAFORO_SBLOCCA = "SBLOCCA";
    CONST SEMAFORO_CONTROLLA = "CONTROLLA";
    CONST ERROR_CODE_SEMAPHORE_LOCK = -10;
    CONST ERROR_CODE_SEMAPHORE_UNLOCK = -11;
    CONST ERROR_CODE_SEMAPHORE_INSERT = -12;
    CONST ERROR_CODE_SEMAPHORE_DELETE = -13;
    CONST ERROR_CODE_SEMAPHORE_SELECT = -14;
    CONST ERROR_CODE_SEMAPHORE_EXIST_MINE = -20; ///semaforo prensete e accesso da me 
    CONST ERROR_CODE_SEMAPHORE_EXIST_OTHER = -30; //semaforo prensete e accesso da altri utenti
    CONST DEFAULT_MAX_SENDATTEMPTS = 5; //Default Numero Massimo di tentatifi invio  Mail
    CONST CHIAVE_PARAM_CLASS_DESC = "PARAM_CLASS_DESC";

    public $ITALWEB_DB;
    private $errMessage;
    private $errCode;
    private $sendMail = false;
    private $maxSendAttempts;

    function __construct() {
        $this->sendMail = $this->getAttivazioneMail();
        $this->maxSendAttempts = $this->getMaxSendAttempts();
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function setITALWEB_DB($ITALWEB_DB) {
        $this->ITALWEB_DB = $ITALWEB_DB;
    }

    public function getITALWEB_DB() {
        if (!$this->ITALWEB_DB) {
            try {
                $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALWEB_DB;
    }

    public function getGenericTab($sql, $multi = true) {
        return ItaDB::DBSQLSelect($this->getITALWEB_DB(), $sql, $multi);
    }

    public function getIstanze_global_ini($codice, $tipo = "class", $multi = true) {
        if ($tipo == "class") {
            $globalPattern = ITA_CONFIG_PATH . "/env_config/{$codice}_*_GLOBAL.ini";
            $lista = glob($globalPattern);
            $arrIstanze = array();
            foreach ($lista as $file) {
                list($class, $skip) = explode('_GLOBAL.ini', pathinfo($file, PATHINFO_BASENAME));
                $arrIstanze[] = $class;
            }
        }
        return $arrIstanze;
    }

    public function getIstanze($codice, $tipo = "class", $multi = true) {
        if ($tipo == "class") {
            $sql = "  
                    SELECT
                        DISTINCT CLASSE,
                        (SELECT CONFIG FROM ENV_CONFIG B WHERE B.CLASSE=A.CLASSE AND B.CHIAVE='" . self::CHIAVE_PARAM_CLASS_DESC . "') AS DESCRIZIONE_ISTANZA
                    FROM ENV_CONFIG A
                    WHERE A.CLASSE LIKE '$codice%'";
        }
        return ItaDB::DBSQLSelect($this->getITALWEB_DB(), $sql, $multi);
    }

    public function notificheDaLeggere($utente) {
        $sql = "SELECT COUNT(ROWID) AS DALEGGERE FROM ENV_NOTIFICHE WHERE UTEDEST='$utente' AND DATAVIEW=''";
        $env_notifiche_daleggere = itaDB::DBSQLSelect($this->getITALWEB_DB(), $sql, false);
        $conta = $env_notifiche_daleggere['DALEGGERE'];

        
        $showFullCounterDesktops = array('envDesktopCityWare','envDesktopItaEngine');

        if (!in_array(ITA_DESKTOP, $showFullCounterDesktops) && (int) $conta > 99) {
            $conta = '...';
        }

        return $conta;
    }

    public function checkUnsentMail($utente) {
        return true;
    }

    public function setNoticeCounter($utente) {
        Out::html('itaNotice_lbl', '<span style="color:red">' . $this->notificheDaLeggere($utente) . '</span>');
        Out::attributo('itaNotice', 'title', 0, $this->notificheDaLeggere($utente) . " avvisi da leggere.");
    }

    public function Semaforo($tipo, $chiave, $procedura, $tipoblocco = 'CHIAVE') {
        /*
          $ditta = App::$utente->getKey('ditta');
          $token = self::$utente->getKey('TOKEN');
          $ret_token = ita_token($token, $ditta, 0, 3);
         */
        $s_utente = App::$utente->getKey('nomeUtente');
        $s_token = App::$utente->getKey('TOKEN');

        if (!$this->controllaTokenSemaforo($chiave, $procedura, $tipoblocco)) {
            return false;
        }
        /*
         * A seconda del tipo blocco creo l'sql
         */
        switch ($tipoblocco) {
            case self::TIPO_BLOCCO_CHIAVE:
                $sql = "SELECT * FROM ENV_SEMAFORI WHERE CHIAVE = '$chiave' ";
                break;

            case self::TIPO_BLOCCO_TABELLA:
                $sql = "SELECT * FROM ENV_SEMAFORI WHERE CHIAVE = '$chiave' AND TABELLA = '$procedura' ";
                break;
        }

        switch ($tipo) {
            case self::SEMAFORO_BLOCCA:
                $retLock = $this->lockEnvSemafori();
                if ($retLock === false) {
                    return false;
                }
                $EnvSemafori_rec = ItaDB::DBSQLSelect($this->getITALWEB_DB(), $sql, false);
// se è presente il semaforo 
                if ($EnvSemafori_rec) {
                    $Data = date("d/m/Y", strtotime($EnvSemafori_rec['DATASEM']));
                    $Ora = $EnvSemafori_rec['ORASEM'];
                    if ($EnvSemafori_rec['TOKEN'] == $s_token) {
                        $this->setErrCode(self::ERROR_CODE_SEMAPHORE_EXIST_MINE);
                        $this->setErrMessage("Questo utente ha ancora la sessione bloccata per la procedura $procedura, avviata il $Data alle ore $Ora.");
                    } else {
                        $this->setErrCode(self::ERROR_CODE_SEMAPHORE_EXIST_OTHER);
                        $this->setErrMessage("L'utente " . $EnvSemafori_rec['UTENTE'] . " sta ancora eseguendo la prcedura $procedura, avviata il " . $Data . " alle ore " . $Ora . ".");
                    }
                    $this->unlockEnvSemafori($retLock);
                    return false;
                } else {
                    $EnvSemafori_rec['CHIAVE'] = $chiave;
                    $EnvSemafori_rec['TABELLA'] = $procedura;
                    $EnvSemafori_rec['UTENTE'] = $s_utente;
                    $EnvSemafori_rec['TOKEN'] = $s_token;
                    $EnvSemafori_rec['DATASEM'] = date('Ymd');
                    $EnvSemafori_rec['ORASEM'] = date('H:i:s');
                    try {
                        ItaDB::DBInsert($this->getITALWEB_DB(), 'ENV_SEMAFORI', 'ROWID', $EnvSemafori_rec);
                    } catch (Exception $exc) {
                        $this->setErrCode(self::ERROR_CODE_SEMAPHORE_INSERT);
                        $this->setErrMessage("Errore in inserimento semaforo<br>" . $exc->getMessage());
                        $this->unlockEnvSemafori($retLock);
                        return false;
                    }
                }
                $this->unlockEnvSemafori($retLock);
                break;

            case self::SEMAFORO_SBLOCCA:
                $retLock = $this->lockEnvSemafori();
                if ($retLock === false) {
                    return false;
                }
                $EnvSemafori_rec = ItaDB::DBSQLSelect($this->getITALWEB_DB(), $sql, false);
                if ($EnvSemafori_rec) {
                    $Data = date("d/m/Y", strtotime($EnvSemafori_rec['DATASEM']));
                    $Ora = $EnvSemafori_rec['ORASEM'];
                    if ($EnvSemafori_rec['TOKEN'] == $s_token) {
                        try {
                            ItaDB::DBDelete($this->getITALWEB_DB(), 'ENV_SEMAFORI', 'ROWID', $EnvSemafori_rec['ROWID']);
                        } catch (Exception $exc) {
                            $this->setErrCode(self::ERROR_CODE_SEMAPHORE_DELETE);
                            $this->setErrMessage("Errore durante la cancellazione del semaforo.<br>" . $exc->getMessage());
                            $this->unlockEnvSemafori($retLock);
                            return false;
                        }
                    } else {
                        $this->setErrCode(self::ERROR_CODE_SEMAPHORE_EXIST_OTHER);
                        $this->setErrMessage("L'utente " . $EnvSemafori_rec['UTENTE'] . " sta ancora eseguendo la prcedura $procedura, avviata il " . $Data . " alle ore " . $Ora . ".");
                        $this->unlockEnvSemafori($retLock);
                        return false;
                    }
                }
                $this->unlockEnvSemafori($retLock);
                break;
// @TODO ancora utilizzato ?!
            case self::SEMAFORO_CONTROLLA:
                $retLock = $this->lockEnvSemafori();
                if ($retLock === false) {
                    return false;
                }
                $EnvSemafori_rec = ItaDB::DBSQLSelect($this->getITALWEB_DB(), $sql, false);
                if ($EnvSemafori_rec) {
                    $Data = date("d/m/Y", strtotime($EnvSemafori_rec['DATASEM']));
                    $Ora = $EnvSemafori_rec['ORASEM'];
                    $this->setErrCode(self::ERROR_CODE_SEMAPHORE_EXIST_MINE);
                    $this->setErrMessage("Procedura $procedura bloccata da " . $EnvSemafori_rec['UTENTE'] . " in data " . $Data . " e ora " . $Ora . ".");
                    return false;
                }
                $this->unlockEnvSemafori($retLock);
                break;
        }
        return true;
    }

    private function controllaTokenSemaforo($chiave, $procedura, $tipoblocco = 'CHIAVE') {
        switch ($tipoblocco) {
            case self::TIPO_BLOCCO_CHIAVE:
                $sql = "SELECT * FROM ENV_SEMAFORI WHERE CHIAVE = '$chiave' ";
                break;

            case self::TIPO_BLOCCO_TABELLA:
                $sql = "SELECT * FROM ENV_SEMAFORI WHERE CHIAVE = '$chiave' AND TABELLA = '$procedura' ";
                break;
        }
        $EnvSemafori_rec = ItaDB::DBSQLSelect($this->getITALWEB_DB(), $sql, false);
        if ($EnvSemafori_rec) {

            $ditta = App::$utente->getKey('ditta');
            $ret_token = ita_token($EnvSemafori_rec['TOKEN'], $ditta, 0, 103);
            if ($ret_token['status'] == '-6') {
                try {
                    ItaDB::DBDelete($this->getITALWEB_DB(), 'ENV_SEMAFORI', 'ROWID', $EnvSemafori_rec['ROWID']);
                } catch (Exception $exc) {
                    $this->setErrCode(self::ERROR_CODE_SEMAPHORE_DELETE);
                    $this->setErrMessage($exc->getMessage());
                    return false;
                }
            }
        } else {
            $this->setErrCode(self::ERROR_CODE_SEMAPHORE_SELECT);
            $this->setErrMessage("Errore lettura semaforo.<br>" . $this->getErrMessage());
        }
        return true;
    }

    public function resetSemaforiToken($token) {
        if (!$token) {
            return true;
        }
        $sql = "SELECT * FROM ENV_SEMAFORI WHERE TOKEN='$token'";
        $EnvSemafori_tab = ItaDB::DBSQLSelect($this->getITALWEB_DB(), $sql, true);
//        file_put_contents('/users/pc/dos2ux/sem.log', print_r($EnvSemafori_tab, true));
        if ($EnvSemafori_tab) {
            foreach ($EnvSemafori_tab as $EnvSemafori_rec) {
                ItaDB::DBDelete($this->getITALWEB_DB(), 'ENV_SEMAFORI', 'ROWID', $EnvSemafori_rec['ROWID']);
            }
        }
        return true;
    }

    private function lockEnvSemafori() {
        $retLock = ItaDB::DBLock($this->getITALWEB_DB(), "ENV_SEMAFORI", "", " ", 10, 120);
        if ($retLock['status'] != 0) {
            $this->setErrCode(self::ERROR_CODE_SEMAPHORE_LOCK);
            $this->setErrMessage('Blocco Tabella ENV_SEMAFORI non Riuscito.');
            return false;
        }
        return $retLock;
    }

    private function unlockEnvSemafori($retLock = '') {
        if ($retLock)
            $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
        if ($retUnlock['status'] != 0) {
            $this->setErrCode(self::ERROR_CODE_SEMAPHORE_UNLOCK);
            $this->setErrMessage($this->getErrMessage() . '<br>Sblocco Tabella ENV_SEMAFORI non Riuscito.');
        }
    }

    /**
     * 
     * @param type $form Form d'inserimento
     * @param type $oggetto Oggetto della notifica
     * @param type $testo Testo della notifica
     * @param type $utedest Utente destinatario (UTELOG o UTECOD)
     * @param type $data Altri dati (di ENV_NOTIFICHE)
     * @return boolean
     */
    public function inserisciNotifica($form, $oggetto, $testo, $utedest, $data = array()) {
        $accLib = new accLib();
        if (is_numeric($utedest)) {
            $utenti_rec = $accLib->GetUtenti($utedest);
            if ($utenti_rec && $utenti_rec['UTELOG']) {
                $utedest = $utenti_rec['UTELOG'];
            }
        } else {
            $utenti_rec = $accLib->GetUtenti($utedest, 'utelog');
        }

        /*
         * Verifico la necessita di invio avviso via mail
         */
        $toSend = 0;
        $ParmNotifiche = $this->getEnvUtemeta('ParmNotifiche', $utenti_rec['UTECOD']);
        if ($ParmNotifiche['Notifiche']['NotMail'] && $this->sendMail === true) {
            $toSend = 1;
        }
        $this->setITALWEB_DB($this->getITALWEB_DB());
        $env_notifiche = $data;
        $env_notifiche['OGGETTO'] = $oggetto;
        $env_notifiche['TESTO'] = $testo;
        $env_notifiche['UTEINS'] = App::$utente->getKey('nomeUtente');
        $env_notifiche['MODELINS'] = $form;
        $env_notifiche['DATAINS'] = date("Ymd");
        $env_notifiche['ORAINS'] = date("H:i:s");
        $env_notifiche['UTEDEST'] = $utedest;
        $env_notifiche['MAILTOSEND'] = $toSend;
        $env_notifiche['MAILSENDATTEMPT'] = 0;
        $env_notifiche['MAILDEST'] = '';
        $env_notifiche['MAILDATE'] = '';
        $env_notifiche['MAILTIME'] = '';
        $env_notifiche['MAILSENDMSG'] = '';
        $env_notifiche['MAILSENDERR'] = 0;
        if (!ItaDB::DBInsert($this->ITALWEB_DB, 'ENV_NOTIFICHE', 'ROWID', $env_notifiche)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Impossibile creare la notifica');
            return false;
        }
        $retId = $this->ITALWEB_DB->getLastId();
        $env_notifiche_rec = $this->getEnv_Notifiche($retId);

        if (!$this->inviaEmlNotifica($env_notifiche_rec)) {
            return false;
        }
        return true;
    }

    public function inviaEmlNotifica($env_notifiche_rec) {
        if ($this->sendMail == false || $env_notifiche_rec['MAILTOSEND'] == 0 || $env_notifiche_rec['MAILSENDERR']) {
            return true;
        }

        $accLib = new accLib();
        $utenti_rec = $accLib->GetUtenti($env_notifiche_rec['UTEDEST'], 'utelog');
        $richut_rec = $accLib->GetRichut($utenti_rec['UTECOD']);
        $mailDest = $richut_rec['RICMAI'];

        if (!$mailDest) {
            $this->setErrCode(-1);
            $this->setErrMessage("Account destinatario per l'invio della notifica all'utente: {$utenti_rec['UTELOG']}, non configurato. Invio Avviso Annullato.");
            $env_notifiche_rec['MAILSENDERR'] = 1;
            $env_notifiche_rec['MAILSENDMSG'] = $this->getErrMessage();
            $this->updateStatusInviaEmlNotifica($env_notifiche_rec);
            return false;
        }

        /*
         * Invia
         */
        $Account = '';
        $devLib = new devLib();
        $ItaEngine_mail_rec = $devLib->getEnv_config('ITAENGINE_EMAIL', 'codice', 'ACCOUNT', false);
        if ($ItaEngine_mail_rec) {
            $Account = $ItaEngine_mail_rec['CONFIG'];
        }
        if (!$Account) {
            $this->setErrCode(-1);
            $this->setErrMessage("Account mittente per l'invio della notifica non configurato. Invio Avviso Annullato.");
            $env_notifiche_rec['MAILSENDERR'] = 1;
            $env_notifiche_rec['MAILSENDMSG'] = $this->getErrMessage();
            $this->updateStatusInviaEmlNotifica($env_notifiche_rec);
            return false;
        }

        include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
        $emlMailBox = emlMailBox::getInstance($Account);
        if (!$emlMailBox) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile accedere alle funzioni dell'account: $Account");
            $env_notifiche_rec['MAILSENDATTEMPT'] += 1;
            $env_notifiche_rec['MAILSENDMSG'] = $this->getErrMessage();
            $this->updateStatusInviaEmlNotifica($env_notifiche_rec);
            return false;
        }

        $outgoingMessage = $emlMailBox->newEmlOutgoingMessage();
        if (!$outgoingMessage) {
            $this->setErrCode(-1);
            $this->setErrMessage('Impossibile creare un nuovo messaggio in uscita');
            $env_notifiche_rec['MAILSENDATTEMPT'] += 1;
            $env_notifiche_rec['MAILSENDMSG'] = $this->getErrMessage();
            $this->updateStatusInviaEmlNotifica($env_notifiche_rec);
            return false;
        }

        $outgoingMessage->setSubject('Avviso Notifica.');
        $outgoingMessage->setBody(
                "<pre>"
                . "Oggetto   : " . $env_notifiche_rec['OGGETTO'] . "\n"
                . "Testo     : " . $env_notifiche_rec['TESTO'] . "\n"
                . "Utente    : " . $env_notifiche_rec['UTEINS'] . "\n"
                . "Data Ins. : " . date('d/m/Y', strtotime($env_notifiche_rec['DATAINS']))
                . "</pre>"
        );
        $outgoingMessage->setEmail($mailDest);
        $mailSent = $emlMailBox->sendMessage($outgoingMessage, false, false);
        if (!$mailSent) {
            $this->setErrCode(-1);
            $this->setErrMessage($emlMailBox->getLastMessage());
            $env_notifiche_rec['MAILSENDATTEMPT'] += 1;
            $env_notifiche_rec['MAILSENDMSG'] = $this->getErrMessage();
            $this->updateStatusInviaEmlNotifica($env_notifiche_rec);
            return false;
        } else {
            $env_notifiche_rec['MAILDEST'] = $mailDest;
            $env_notifiche_rec['MAILDATE'] = date('Ymd');
            $env_notifiche_rec['MAILTIME'] = date('H:i:s');
            $env_notifiche_rec['MAILSENDMSG'] = "Mail di Avviso inviata con sucesso.";
            $this->updateStatusInviaEmlNotifica($env_notifiche_rec);
            return true;
        }
    }

    private function updateStatusInviaEmlNotifica($env_notifiche_rec) {
        if ($env_notifiche_rec['MAILSENDATTEMPT'] > $this->maxSendAttempts) {
            $env_notifiche_rec['MAILSENDERR'] = 1;
            $env_notifiche_rec['MAILSENDMSG'] .= "Invio Avviso Annullato per superamento numero massimo di {$this->maxSendAttempts} tentativi.";
        }
        try {
            $nrow = ItaDB::DBUpdate($this->getITALWEB_DB(), 'ENV_NOTIFICHE', 'ROWID', $env_notifiche_rec);
            if ($nrow == -1) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function getNoticeToSend() {
        $env_notice_tab = array();
        $sql = "SELECT * FROM ENV_NOTIFICHE WHERE MAILTOSEND = 1 AND MAILSENDERR=0 AND MAILDATE=''";
        return ItaDB::DBSQLSelect($this->getITALWEB_DB(), $sql, true);
    }

    public function getEnv_Notifiche($rowid) {
        $sql = "SELECT * FROM ENV_NOTIFICHE WHERE ROWID=$rowid";
        return ItaDB::DBSQLSelect($this->getITALWEB_DB(), $sql, false);
    }

    /**
     * Getter per key di ENV_UTEMETA
     * @param type $key Chiave
     * @param type $utente Id utente. Di default prende l'operatore
     * @param type $unserialize Determina se ritornare il valore utilizzando unserialize o meno
     * @return string METAVALUE
     */
    public function getEnvUtemeta($key = '', $utente = null, $unserialize = true) {
        if (!$utente) {
            $utente = App::$utente->getKey('idUtente');
        }
        $sql = "SELECT * FROM ENV_UTEMETA WHERE UTECOD = $utente";
        if ($key) {
            $sql .= " AND METAKEY = '$key'";
        }


        $utemeta_rec = ItaDB::DBSQLSelect($this->getITALWEB_DB(), $sql, false);
        if (!$utemeta_rec) {
            return false;
        }
        return $unserialize ? unserialize($utemeta_rec['METAVALUE']) : $utemeta_rec['METAVALUE'];
    }

    /**
     * Setter per key di ENV_UTEMETA. Se il record combinato di key+utente non esiste,
     * viene creato.
     * @param type $key
     * @param type $value
     * @param type $utente
     * @param type $serialize
     * @return boolean
     */
    public function setEnvUtemeta($key, $value, $utente = null, $serialize = true) {
        if (!$utente) {
            $utente = App::$utente->getKey('idUtente');
        }

        $sql = "SELECT * FROM ENV_UTEMETA WHERE UTECOD = " . $utente . " AND METAKEY = '$key'";
        $utemeta_rec = ItaDB::DBSQLSelect($this->getITALWEB_DB(), $sql, false);

        try {
            $mv = $serialize ? serialize($value) : $value;
            if ($utemeta_rec) {
                $utemeta_rec['METAVALUE'] = $mv;
                ItaDB::DBUpdate($this->getITALWEB_DB(), 'ENV_UTEMETA', 'ROWID', $utemeta_rec);
            } else {
                $utemeta_rec = array('UTECOD' => $utente, 'METAKEY' => $key, 'METAVALUE' => $mv);
                ItaDB::DBInsert($this->getITALWEB_DB(), 'ENV_UTEMETA', 'ROWID', $utemeta_rec);
            }
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage($e->getMessage());
            return false;
        }

        return true;
    }

    public function getClassEvento($codice, $tipo = 'rowid', $multi = false) {
        switch ($tipo) {
            case 'rowid':
                $sql = "SELECT * FROM ENV_TIPI WHERE ROWID=$codice";
                break;
            case 'codice':
                $sql = "SELECT * FROM ENV_TIPI WHERE CODICE='$codice'";
                break;
        }

        return ItaDB::DBSQLSelect($this->getITALWEB_DB(), $sql, $multi);
    }

    public function getEventoCAL_EVENTI($codice) {
        $sql = "SELECT * FROM CAL_EVENTI WHERE CLASSEVENTO='$codice'";
        return ItaDB::DBSQLSelect($this->getITALWEB_DB(), $sql, true);
    }

    public function getEventoCAL_ATTIVITA($codice) {
        $sql = "SELECT * FROM CAL_ATTIVITA WHERE CLASSEVENTO='$codice'";
        return ItaDB::DBSQLSelect($this->getITALWEB_DB(), $sql, true);
    }

    public function ExportToExcel($nome, $dati) {
        $DaFormattare = array();
        switch ($nome) {
            case 'StampaEventi':
                $CampiUtilizzati = array('TITOLO_E' => 'Titolo', 'DESCRIZIONE_E' => 'Descrizione', 'START_E' => 'Inizio', 'END_E' => 'Fine');
                break;
            case 'StampaAttivita':
                $CampiUtilizzati = array('TITOLO_A' => 'Titolo', 'DESCRIZIONE_A' => 'Descrizione', 'START_A' => 'Inizio', 'END_A' => 'Fine');
                break;
        }
        $ValoriTabella = array();
        $i = 0;
        foreach ($dati as $riga) {
            foreach ($CampiUtilizzati as $chiave => $valore) {
                if ($DaFormattare[$chiave] && $riga[$chiave]) {
                    $NewVal = $this->FormattaValoreExport($riga[$chiave], $DaFormattare[$chiave]);
                    $ValoriTabella[$i][$valore] = $NewVal;
                } else {
                    $ValoriTabella[$i][$valore] = $riga[$chiave];
                }
            }
            $i++;
        }
        $ita_grid01 = new TableView('griglia', array('arrayTable' => $ValoriTabella,
            'rowIndex' => 'idx'));
        $ita_grid01->setSortOrder('NOMINATIVO');
        $ita_grid01->setPageRows('10000');
        $ita_grid01->setPageNum(1);
        $ita_grid01->exportXLS('', $nome . '.xls');
    }

    public function FormattaValoreExport($Valore, $Formato) {
        switch ($Formato) {
            case 'DATA':
                $NewValore = substr($Valore, 6, 2) . '/' . substr($Valore, 4, 2) . '/' . substr($Valore, 0, 4);
                break;
            case 'IMPORTO':
                $NewValore = str_replace('.', ',', $Valore);
                break;
            case 'CHECK':
                $NewValore = 'X';
                break;
        }

        return $NewValore;
    }

    public static function getAttivazioneMail() {
        include_once (ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php');
        $result = array();
        $devLib = new devLib();
        $env_config_rec = $devLib->getEnv_config('ENVNOTIFICHE', 'codice', 'ATTIVA_MAIL', false);
        if (!$env_config_rec || !$env_config_rec['CONFIG']) {
            return false;
        }
        $attivamail = $env_config_rec['CONFIG'];
        if (strtoupper(trim($attivamail)) === "SI") {
            return true;
        } else {
            return false;
        }
    }

    public static function getMaxSendAttempts() {
        include_once (ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php');
        $result = array();
        $devLib = new devLib();
        $env_config_rec = $devLib->getEnv_config('ENVNOTIFICHE', 'codice', 'MAX_TENTATIVI_MAIL', false);
        if (!$env_config_rec || !$env_config_rec['CONFIG']) {
            $maxTentativi = self::DEFAULT_MAX_SENDATTEMPTS;
        } else {
            $maxTentativi = $env_config_rec['CONFIG'];
        }
        return $maxTentativi;
    }

    public function sendSystemMail($msg, $subject = null) {
        $Account = '';
        $devLib = new devLib();
        /*
         * Account Mittente
         */
        $ItaEngine_mail_rec = $devLib->getEnv_config('ITAENGINE_EMAIL', 'codice', 'ACCOUNT', false);
        if ($ItaEngine_mail_rec) {
            $Account = $ItaEngine_mail_rec['CONFIG'];
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage('Account invio email non definito.');
            return false;
        }
        /*
         * Account destinatario
         */
        $ItaEngine_mail_rec = $devLib->getEnv_config('ITAENGINE_EMAIL', 'codice', 'ADDRESS', false);
        if ($ItaEngine_mail_rec) {
            $Address = $ItaEngine_mail_rec['CONFIG'];
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage('Indirizzo destinatario amministrazione sistema non definito.');
            return false;
        }
        include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
        $emlMailBox = emlMailBox::getInstance($Account);
        if (!$emlMailBox) {
            $this->setErrCode(-1);
            $this->setErrMessage('Istanza mail ' . $Account . ' non riuscita.');
            return false;
        }
        //
        $outgoingMessage = $emlMailBox->newEmlOutgoingMessage();
        if (!$outgoingMessage) {
            $this->setErrCode($emlMailBox->getLastExitCode());
            $this->setErrMessage($emlMailBox->getLastMessage());
            return false;
        }
        $outgoingMessage->setSubject($subject);
        $outgoingMessage->setBody($msg);
        $outgoingMessage->setEmail($Address);
        $mailSent = $emlMailBox->sendMessage($outgoingMessage, false, false);
        if ($mailSent) {
            return true;
        } else {
            $this->setErrCode($emlMailBox->getLastExitCode());
            $this->setErrMessage($emlMailBox->getLastMessage());
            return false;
        }
    }

}
