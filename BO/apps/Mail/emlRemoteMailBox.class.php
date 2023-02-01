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
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    28.09.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once dirname(__FILE__) . '/emlLib.class.php';
include_once(ITA_LIB_PATH . '/itaPHPMail/itaPOP3.class.php');

class emlRemoteMailBox {

    private $account;
    private $account_user;
    private $account_pass;
    private $pop3_conn = null;
    private $pop3_host;
    private $pop3_port;
    private $pop3_secure;
    private $pop3_realm;
    private $pop3_authm;
    private $pop3_workst;
    private $emlLib;
    private $lastExitCode;
    private $lastMessage;

    public function __construct($account = null) {
        try {
            $this->emlLib = new emlLib();
        } catch (Exception $e) {
            throw new Exception("Mail box remota: errore in caricamento risorse (emlLib.class.php)");
        }
        if ($account) {
            if ($this->setAccount($account)) {
                if (!$this->openMailBox()) {
                    throw new Exception($this->lastMessage);
                }
            } else {
                throw new Exception($this->lastMessage);
            }
        } else {
            throw new Exception($this->lastMessage);
        }
    }

    public static function getRemoteMailBoxInstance($account) {
        return new emlRemoteMailBox($account);
    }

    /**
     * Assegna il nome account e tutti i parametri da anagrafica account 
     * @param type $account Nome Account
     * @return boolean true account assegnato, false errore assgnazione
     */
    public function setAccount($account) {
        $Mail_account_rec = $this->emlLib->getMailAccount($account);
        if ($Mail_account_rec) {
            $this->account = $account;
            $this->account_user = $Mail_account_rec['USER'];
            $this->account_pass = $Mail_account_rec['PASSWORD'];
            $this->pop3_host = $Mail_account_rec['POP3HOST'];
            $this->pop3_port = $Mail_account_rec['POP3PORT'];
            $this->pop3_secure = $Mail_account_rec['POP3SECURE'];
            $this->pop3_realm = $Mail_account_rec['POP3REALM'];
            $this->pop3_authm = $Mail_account_rec['POP3AUTHM'];
            if (!$this->pop3_authm) {
                $this->pop3_authm = "USER";
            }
            $this->pop3_workst = $Mail_account_rec['WORKST'];
            return true;
        } else {
            $this->account = null;
            $this->account_user = null;
            $this->account_pass = null;
            $this->pop3_host = null;
            $this->pop3_port = null;
            $this->pop3_secure = null;
            $this->pop3_realm = null;
            $this->pop3_authm = null;
            $this->pop3_workst = null;
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
     * @return \itaPOP3|bool
     */
    private function openMailBox() {
        $this->pop3_conn = new itaPOP3(array(
            'hostname' => $this->pop3_host,
            'port' => $this->pop3_port,
            /*
             * Rimane per compatibilità con versione pre 07/2019
             */
            'tls' => $this->pop3_secure,
            /*
             * Nuova Chiave per versione 07/2019
             */
            'pop3_secure' => $this->pop3_secure,
            'realm' => $this->pop3_realm,
            'workstation' => $this->pop3_workst,
            'authentication_mechanism' => $this->pop3_authm,
            'join_continuation_header_lines' => 1
        ));
        if (!$this->pop3_conn->open()) {
            $this->lastMessage = $this->pop3_conn->getMessage();
            return false;
        }
        if (!$this->pop3_conn->login($this->account_user, $this->account_pass)) {
            $this->lastMessage = $this->pop3_conn->getMessage();
            return false;
        }
        return $this->pop3_conn;
    }

    private function getPop3_conn() {
        return $this->pop3_conn;
    }

    /**
     * 
     * @param \itaPOP3 $pop3
     */
    public function closeMailBox() {
        $this->pop3_conn->close();
        $this->pop3_conn = null;
    }

    /**
     * Scarica un messaggio dal server mail
     * 
     * @param type $id id univoco nel server
     * @return boolean|string
     */
    public function getMessagefromIntID($id) {
        $pop3 = $this->pop3_conn;
        //if ($pop3->login($this->account_user, $this->account_pass)) {
        if ($pop3->OpenMessage($id, -1) == false) {
            $this->lastExitCode = -3;
            $this->lastMessage = "getMessagefromIntID:" . $pop3->getMessage();
            return false;
        } else {
            $mail = $pop3->GetMessageData();
            $tempPath = itaLib::createAppsTempPath($this->account) . "/";
            $EmlName = $tempPath . md5(rand() * time()) . ".eml";
            $retWrite = file_put_contents($EmlName, $mail['message']);
            if ($retWrite === false || $retWrite === 0) {
                $this->lastExitCode = -4;
                $last_error = error_get_last();
                $this->lastMessage = "getMessagefromIntID Errore in salvataggio file eml tempraneo: " . $last_error['message'];
                return false;
            }
            $this->lastExitCode = 0;
            $this->lastMessage = "";
            return $EmlName;
        }
    }

    public function getUIDList() {
        $pop3 = $this->pop3_conn;
        $list = $pop3->listMessages("", true);
        if ($list !== false) {
            $this->lastExitCode = 0;
            $this->lastMessage = "";
            return $list;
        } else {
            $this->lastExitCode = -2;
            $this->lastMessage = "emlRemoteMailBox: " . $pop3->getMessage();
            return false;
        }
    }

    /**
     * 
     * @param array $arr_id
     * @return boolean
     */
    public function deleteMessagefromIntId($arr_id) {
        $pop3 = $this->pop3_conn;
        $deleted = 0;
        foreach ($arr_id as $key => $id) {
            if ($pop3->deleteMessage($id) == false) {
                $pop3->resetDeleteMessage();
                $this->lastExitCode = -6;
                $this->lastMessage = $pop3->getMessage();
                //$pop3->close();
                return false;
            }
            $deleted += 1;
        }
        $this->lastExitCode = 0;
        $this->lastMessage = "";
        return true;
    }

    public function resetDeleteMessage() {
        $pop3 = $this->pop3_conn;
        $ret = $pop3->resetDeleteMessage();
        if ($ret === false) {
            $this->lastExitCode = -1;
            $this->lastMessage = $pop3->lastMessage;
            return false;
        }
        $this->lastExitCode = 0;
        $this->lastMessage = "";
        return true;
    }

}

?>