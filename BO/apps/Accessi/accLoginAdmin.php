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
 * @version    24.01.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
function accLoginAdmin() {
    $accLoginAdmin = new accLoginAdmin();
    $accLoginAdmin->parseEvent();
    return;
}

class accLoginAdmin extends itaModel {

    public $nameForm = "accLoginAdmin";

    function __construct() {
        parent::__construct();
        $this->private = false;
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->openLoginForm();
                break;
            case 'onBlur':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'accLoginAdmin_Entra':
                        $this->loginAdmin();
                        break;
                    case 'close-portlet':
                        break;
                }
        }
    }

    function loginAdmin() {
//        if ($_POST['accLoginAdmin_utente'] == '') {
//            Out::msgInfo("Attenzione", "Accesso per l'amministrazione del sistema non Valido. Inserire il campo utente!", "auto", "auto", "");
//            return false;
//        }
//        
// verifica password si config.ini        
//        
        $password = App::getConf("security.admin_password");

        $token = md5($_POST['accLoginAdmin_password'] . (rand() * time()));
        if ($password !== $_POST['accLoginAdmin_password']) {
            Out::msgStop("Errore Validazione", 'Password mancante o errata', 'auto', 'auto', '');
            return false;
        }


        $this->initSession($token);
        App::$utente->setStato(Utente::AUTENTICATO_ADMIN);
        App::$utente->setKey('TOKEN', $token);
        App::$utente->setKey('nomeUtente', 'admin');
        App::$utente->setKey('idUtente', 999999999999);
        App::$utente->setKey('tipoAccesso', 'admin');
        App::$utente->setKey('referrerAccesso', "");
        App::$utente->setKey('DataLavoro', date('Ymd'));
        App::$utente->setKey('lingua', 'it');
        App::createPrivPath();
        Out::closeDialog('accLoginAdmin');
        Out::codice('token="' . $token . '";');
        // qui sono loggato
        App::openDesktopAdmin();
    }

    function initSession($token) {
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
        Out::enableBlockMsg();
        Out::valore($this->nameForm . "_utente", '');
        Out::show($this->nameForm . "_wrapper");
        Out::setFocus('', $this->nameForm . "_password");
    }

}

?>
