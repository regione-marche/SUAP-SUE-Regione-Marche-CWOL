<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaPOP3
 *
 * @author michele
 * @author marco
 * @version    17.09.2012
 */
require_once(ITA_LIB_PATH . '/pop3/pop3.php');

class itaPOP3 {

    private $returnCode;
    private $message;
    public $pop3provider;
    public $hostname = "";                          /* POP 3 server host name                      */
    public $port = 110;                             /* POP 3 server host port,                     */
    public $tls = 0;                                /* Establish secure connections using TLS      */
    public $authentication_mechanism = "USER";      /* SASL authentication mechanism               */
    public $realm = "";                             /* Authentication realm or domain              */
    public $workstation = "";                       /* Workstation for NTLM authentication         */
    public $join_continuation_header_lines = 1;     /* Concatenate headers split in multiple lines */
    public $debug = 0;                              /* Output debug information                    */
    public $html_debug = 0;                         /* Debug information is in HTML                */
    public $quit_handshake = 1;
    public $error = "";
    public $connectionTimeout = 180;

    /**
     * Imposta i parametri della casella di posta
     * @param type $parms
     */
    public function __construct($parms = array()) {
        $this->pop3provider = new pop3_class;
        $this->pop3provider->hostname = $parms['hostname'];
        $this->pop3provider->port = $parms['port'];
        $this->pop3provider->tls = 0;
        $this->pop3provider->pop3_secure=$parms['pop3_secure'];
        $this->pop3provider->realm = $parms['realm'];
        $this->pop3provider->workstation = $parms['workstation'];
        $this->pop3provider->authentication_mechanism = $parms['authentication_mechanism'];
        if (isset($parms['debug'])) {
            $this->pop3provider->debug = $parms['debug'];
        }
        if (isset($parms['html_debug'])) {
            $this->pop3provider->html_debug = $parms['html_debug'];
        }
        if (isset($parms['join_continuation_header_lines'])) {
            $this->pop3provider->join_continuation_header_lines = $parms['join_continuation_header_lines'];
        }
        if (isset($parms['tls'])) {
            switch ($parms['tls']) {
                case 'ssl':
                case 'tls':
                    $this->pop3provider->tls = 1;
                    break;
                default:
                    $this->pop3provider->tls = 0;
                    break;
            }
        }
        if (isset($parms['connection_timeout'])) {
            $this->connectionTimeout = $parms['connection_timeout'];
        }
    }

    public function __destruct() {
        ;
    }

    /**
     * Apre il collegamento alla casella di posta
     * @return boolean
     */
    public function open() {
        ini_set("default_socket_timeout", $this->connectionTimeout);
        $ret = $this->pop3provider->Open();
        if ($ret == "") {
            $this->returnCode = 0;
            $this->message = "";
            return true;
        }
        $this->returnCode = -1;
        $this->message = $ret;
        return false;
    }

    /**
     * Chiude il collegamento alla casella di posta
     * @return boolean
     */
    public function close() {
        $ret = $this->pop3provider->Close();
        if ($ret == "") {
            $this->returnCode = 0;
            $this->message = "";
            return true;
        }
        $this->returnCode = -2;
        $this->message = $ret;
        return false;
    }

    /**
     * Effettua il login alla casella di posta
     * @param type $user
     * @param type $password
     * @param type $apop
     * @return boolean
     */
    public function login($user, $password, $apop = 0) {
        $ret = $this->pop3provider->Login($user, $password, $apop);
        if ($ret == "") {
            $this->returnCode = 0;
            $this->message = "Loged in";
            return true;
        }
        $this->returnCode = -3;
        $this->message = $ret;
        return false;
    }

    /**
     * Ritorna il numero di messaggi nella casella di posta e la dimensione
     * @return boolean
     */
    public function statistics() {
        $ret = $this->pop3provider->Statistics($messages, $size);
        if ($ret == "") {
            $this->returnCode = 0;
            $this->message = "";
            return array(
                'messages' => $messages,
                'size' => $size
            );
        }
        $this->returnCode = -4;
        $this->message = $ret;
        return false;
    }

    /**
     * Elenco dei messaggi di posta
     * @param type $message
     * @param type $unique_id
     * @return boolean 
     */
    public function listMessages($message, $unique_id) {
        $this->pop3provider->SetError("");
        $ret = $this->pop3provider->ListMessages($message, $unique_id);
        if ($this->pop3provider->error == "") {
            $this->returnCode = 0;
            $this->message = "";
            return $ret;
        } else {
            $this->returnCode = -4;
            $this->message = $this->pop3provider->error;
            return false;
        }
    }

    /**
     * Seleziona i messaggi da eliminare una volta chiuso il collegamento 
     * @param type $message
     * @return boolean
     */
    public function deleteMessage($message) {
        $ret = $this->pop3provider->DeleteMessage($message);
        if ($ret == "") {
            $this->returnCode = 0;
            $this->message = "";
            return true;
        }
        $this->returnCode = -6;
        $this->message = $ret;
        return false;
    }

    /**
     * Ripristina i messaggi selezionati come 'da eliminare'
     * @return boolean
     */
    public function resetDeleteMessage() {
        $ret = $this->pop3provider->ResetDeletedMessages();
        if ($ret == "") {
            $this->returnCode = 0;
            $this->message = "";
            return true;
        }
        $this->returnCode = -7;
        $this->message = $ret;
        return false;
    }

    /**
     * Ritorna l'URL utilizzabile per il flusso POP3
     * @return boolean
     */
    public function GetConnectionName() {
        $ret = $this->pop3provider->GetConnectionName($connection_name);
        if ($ret == "") {
            $this->returnCode = 0;
            $this->message = $connection_name;
            return true;
        }
        $this->returnCode = -8;
        $this->message = $this->pop3provider->error;
        return false;
    }

    /**
     * $message indica il numero di messaggio da aprire
     * $lines il numero di righe da leggere
     * $lines == -1 recupera l'intero messaggio
     * @param type $message
     * @param type $lines
     * @return boolean
     */
    Function OpenMessage($message, $lines = -1) {
        $ret = $this->pop3provider->OpenMessage($message, $lines);
        if ($ret == "") {
            $this->returnCode = 0;
            $this->message = "";
            return true;
        }
        $this->returnCode = -9;
        $this->message = $ret;
        return false;
    }

    /**
     * 
     * @param type $count : indica il numero di byte da leggere
     * @param type $message : ritorna il messaggio letto
     * @param type $end_of_message : indica se è stata raggiunta la fine del messaggio nella lettura
     * @return boolean
     */
    Function GetMessageData($message, $end_of_message, $count = 9999999999) {
        $ret = $this->pop3provider->GetMessage($count, $message, $end_of_message);
        if ($ret == "") {
            $this->returnCode = 0;
            $this->message = "";
            return array(
                'message' => $message,
                'end_of_message' => $end_of_message
            );
        }
        $this->returnCode = -10;
        $this->message = $ret;
        return false;
    }

    /**
     * Ritorna il codice relativo allo stato dell'ultima funzione chiamata
     * @return type
     */
    public function getReturnCode() {
        return $this->returnCode;
    }

    /**
     * Ritorna il messagio relativo allo stato dell'ultima funzione chiamata
     * @return type
     */
    public function getMessage() {
        return $this->message;
    }

    function getConnectionTimeout() {
        return $this->connectionTimeout;
    }

    function setConnectionTimeout($connectionTimeout) {
        $this->connectionTimeout = $connectionTimeout;
    }

}

?>
