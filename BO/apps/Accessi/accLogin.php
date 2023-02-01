<?php

/**
 *
 * ANAGRAFICA FASCICOLI ELETTRONICI
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Accessi
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft sRL
 * @license
 * @version    01.12.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_LIB_PATH . '/itaPHPCore/itaCrypt.class.php';

function accLogin() {
    $accLogin = new accLogin();
    $accLogin->parseEvent();
    return;
}

class accLogin extends itaModel {

    public $nameForm = "accLogin";

    function __construct() {
        parent::__construct();
        $this->private = false;
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($this->event) {
            case 'openform':
                $this->openLoginForm();
                break;
            case 'onBlur':
                switch ($this->elementId) {
                    case $this->nameForm . '_ditta':
                        $this->login();
                        break;
                }
                break;
            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_Entra':
                        $this->login();
                        break;

                    case $this->nameForm . '_visualizzaPass':
                        Out::show($this->nameForm . '_nascondiPass');
                        Out::hide($this->nameForm . '_visualizzaPass');
                        Out::attributo($this->nameForm . '_password', 'type', 0, 'text');
                        break;

                    case $this->nameForm . '_nascondiPass':
                        Out::hide($this->nameForm . '_nascondiPass');
                        Out::show($this->nameForm . '_visualizzaPass');
                        Out::attributo($this->nameForm . '_password', 'type', 0, 'password');
                        break;

                    case 'close-portlet':
                        break;
                }
        }
    }

    function login() {
        if (($appLock = AppUtility::getApplicationLock())) {
            Out::msgInfo('Blocco applicativo', "L'applicativo è sotto blocco: {$appLock['msg']}<br>Impossibile proseguire.", 'auto', 'auto', 'desktop');
            return false;
        }

        $rdm = $_POST[$this->nameForm . '_ricordami'];
        if ($_POST[$this->nameForm . '_utente'] == '') {
            Out::msgInfo("Attenzione", "Accesso non Valido. Inserire il campo utente!", "auto", "auto", "");
            return false;
        }
        if ($_POST[$this->nameForm . '_ditta'] == '') {
            Out::msgInfo("Attenzione", "Selezionare un ente!", "auto", "auto", "");
            return false;
        }
        $utente_orig = trim($_POST[$this->nameForm . '_utente']);
        $ret_verpass = ita_verpass($_POST[$this->nameForm . '_ditta'], $utente_orig, $_POST[$this->nameForm . '_password']);
        if ($ret_verpass['status'] != 0 && $ret_verpass['status'] != '-99') {
            Out::msgStop("Errore di validazione", $ret_verpass['messaggio'], 'auto', 'auto', '');
            return false;
        }
        $expiredPassword = false;
        if ($ret_verpass['status'] == '-99') {
            $expiredPassword = true;
        }
        $cod_ute = $ret_verpass['codiceUtente'];
        $utente = $ret_verpass['nomeUtente'];
        $ret_token = ita_token('', $_POST[$this->nameForm . '_ditta'], $cod_ute, 1);
        if ($ret_token['status'] == '0') {
            $token = $ret_token['token'];
        } else {
            Out::msgStop("Errore Validazione", $ret_token['messaggio'], 'auto', 'auto', '');
            return false;
        }
        $this->initSession($token, $rdm);
        App::$utente->setStato(Utente::AUTENTICATO);
        App::$utente->setKey('ditta', $_POST[$this->nameForm . '_ditta']);
        App::$utente->setKey('TOKEN', $token);
        App::$utente->setKey('nomeUtente', $utente);
        App::$utente->setKey('idUtente', $cod_ute);
        App::$utente->setKey('TOKCOD', ita_token($token, $ditta, 0, 20));
        App::$utente->setKey('tipoAccesso', 'validate');
        App::$utente->setKey('referrerAccesso', "");
        App::$utente->setKey('DataLavoro', date('Ymd'));
        App::$utente->setKey('lingua', 'it');

        if ($rdm) {
            $cookieHash = md5($_SERVER['REMOTE_ADDR'] . "." . $_POST[$this->nameForm . '_utente'] . time());
            setcookie("ita-rdm", $cookieHash, time() + (60 * 60 * 24 * 3), '', '', App::isConnectionSecure(), true);
            $sessionUser = $_POST[$this->nameForm . '_utente'] . "@" . $_POST[$this->nameForm . '_ditta'];
            //$cookieHash=md5($_SERVER['REMOTE_ADDR']. "." . $_POST['accLogin_utente'] . microtime());
            $Accessi_rec = ItaDB::DBSQLSelect(App::$itaEngineDB, "SELECT * FROM ACCESSI WHERE USERSESSION='$sessionUser'", false);
            if (!$Accessi_rec) {
                $Accessi_rec = array();
                $Accessi_rec['USERSESSION'] = $sessionUser;
                $Accessi_rec['HASHSESSION'] = $cookieHash;
                $Accessi_rec['PWDSESSION'] = '';
                $Accessi_rec['SECSESSION'] = itaCrypt::encrypt($_POST[$this->nameForm . '_password']);
                $Accessi_rec['DOMAINSESSION'] = $_POST[$this->nameForm . '_ditta'];
                ItaDB::DBInsert(App::$itaEngineDB, "ACCESSI", "ROWID", $Accessi_rec);
            } else {
                $Accessi_rec['HASHSESSION'] = $cookieHash;
                $Accessi_rec['PWDSESSION'] = '';
                $Accessi_rec['SECSESSION'] = itaCrypt::encrypt($_POST[$this->nameForm . '_password']);
                $Accessi_rec['DOMAINSESSION'] = $_POST[$this->nameForm . '_ditta'];
                ItaDB::DBUpdate(App::$itaEngineDB, "ACCESSI", "ROWID", $Accessi_rec);
            }
        } else {
            setcookie("ita-rdm", false);
        }

        App::createPrivPath();
        itaLib::createAppsTempPath();
        //        Config::loadApps();
        Out::closeDialog('accLogin');

        Out::codice('token="' . $token . '";');

        /*
         *  qui sono loggato
         * 
         * 
         */
        if ($expiredPassword) {
            $_POST = array();
            $model = "accPassword";
            $_POST['event'] = 'openform';
            $_POST['modo'] = 'login';
            itaLib::openForm($model, '', true, '');
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            $phpURL = App::getConf('modelBackEnd.php');
            include_once $phpURL . '/' . $appRoute . '/' . $model . '.php';
            Out::enableBlockMsg();
            $model();
            return false;
        }

        App::openDesktop();
        if (method_exists('itaHooks', 'execute')) {
            itaHooks::execute('post_login');
        }
        App::autoExec();
        return true;
    }

    function initSession($token, $rdm = false) {
        if (App::$tmpToken) {
            App::clearSessionCookie();
            App::$tmpToken = null;
            Out::codice('tmpToken=null;');
        }

        $_POST['TOKEN'] = $token;
        $_SESSION = array();
        App::startSession("S-" . $token, true);
        $_SESSION['utente'] = new Utente();
        App::$utente = $_SESSION['utente'];
    }

    function openLoginForm() {
        Out::hide($this->nameForm . '_nascondiPass');
        Out::hide($this->nameForm . '_visualizzaPass');

        if (App::$clientEngine == 'itaMobile') {
            Out::show($this->nameForm . '_visualizzaPass');
        }

        $setDitta = false;
        Out::enableBlockMsg();
        $itaRDM = $_COOKIE['ita-rdm'];
        $Accessi_rec = ItaDB::DBSQLSelect(App::$itaEngineDB, "SELECT * FROM ACCESSI WHERE HASHSESSION='$itaRDM'", false);
        if ($Accessi_rec) {
            if ($Accessi_rec['PWDSESSION']) {
                $Accessi_rec['SECSESSION'] = itaCrypt::encrypt($Accessi_rec['PWDSESSION']);
                $Accessi_rec['PWDSESSION'] = '';
                ItaDB::DBUpdate(App::$itaEngineDB, 'ACCESSI', 'ROWID', $Accessi_rec);
            }

            list($defuser, $skip) = explode("@", $Accessi_rec['USERSESSION']);
            $defDitta = $Accessi_rec['DOMAINSESSION'];
            Out::valore($this->nameForm . "_utente", $defuser);
            Out::valore($this->nameForm . "_password", itaCrypt::decrypt($Accessi_rec['SECSESSION']));
            Out::valore($this->nameForm . "_ricordami", 1);
        } else {
            $defDitta = false;
            Out::valore($this->nameForm . "_utente", '');
            Out::valore($this->nameForm . "_password", '');
        }

        $enti = App::getEnti();
        $selected = '1';
        if (isset($_POST['ditta'])) {
            $setDitta = $_POST['ditta'];
        }
        if (count($enti) == 1) {
            $first = reset($enti);
            $setDitta = $first['codice'];
        }
        if (!$setDitta) {
            Out::select($this->nameForm . '_ditta', '1', "", $selected, "Seleziona......");
            $selected = '0';
        }
        foreach ($enti as $keyEnte => $propsEnte) {
            if ($propsEnte['riservato'] == "1" && $setDitta !== $propsEnte['codice']) {
                continue;
            }
            if (!$setDitta || $propsEnte['codice'] == $setDitta) {
                if ($propsEnte['codice'] == $defDitta && $defDitta) {
                    $selected = '1';
                }
                Out::select($this->nameForm . '_ditta', '1', $propsEnte['codice'], $selected, $keyEnte . " - " . $propsEnte['codice']);
                $selected = '0';
            }
        }
        if ($selected == "1") {
            Out::msgStop("Errore di Accesso", "Organizzazione $setDitta Inesistente", 'auto', 'auto', '');
            return false;
        }
        Out::show($this->nameForm . "_wrapper");
        Out::setFocus('', $this->nameForm . "_utente");
    }

}
