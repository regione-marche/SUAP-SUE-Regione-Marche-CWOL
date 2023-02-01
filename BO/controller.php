<?php

ob_start();
if (get_magic_quotes_gpc()) {
    $_GET = array_map('itaRemove_magic_quotes_gpc', $_GET);
    $_POST = array_map('itaRemove_magic_quotes_gpc', $_POST);
    $_COOKIE = array_map('itaRemove_magic_quotes_gpc', $_COOKIE);
}

if ($_POST['event'] == 'onload' && $_POST['access'] == 'j-net') { //MM
    $_POST['accesstoken'] = $_POST['TOKEN'];
    unset($_POST['TOKEN']);
}

require_once 'ConfigLoader.php';

require_once(ITA_LIB_PATH . '/itaPHPCore/App.class.php');
require_once(ITA_LIB_PATH . '/itaPHPCore/AppUtility.class.php');
require_once(ITA_LIB_PATH . '/itaPHPCore/Out.class.php');

if (!App::load()) {
    if (!is_object(App::$utente)) {
        ob_clean();
        Out::clean();
        Out::msgStop('Errore', App::getLastErrMessage());
        Out::codice('setTimeout(\'reload()\',10000);');
        echo Out::get('xml');
        exit();
    }
}

if (isset($_GET['test'])) {
    App::$utente->setKey('environment_test', $_GET['test']);
}

$Controller = new Controller();
$Controller->parseEvent();

// Termina Request
App::endRequest();

class Controller {

    function __construct() {
        
    }

    function parseEvent() {
        try {
            switch ($_POST['event']) {
                case 'onload':
                    switch ($_POST['access']) {
                        case 'admin':
                            App::openAdmin();
                            break;
                        case 'validate':
                            App::open();
                            break;
                        case 'validatemobile':
                            App::openMobile();
                            break;
                        case 'j-net':
                            App::jnetAccess();
                            break;
                        case 'direct':
                            App::directAccess();
                            break;
                    }
                    break;
                case 'onunload': case 'closeapp':
                    $save_stato = App::$utente->getStato();
                    if (App::$utente->getKey('tipoAccesso') == 'admin') {
                        App::closeAdmin();
                        break;
                    }
                    App::close();
                    break;
                default:
                    $modelLogin = 'accLogin';
                    switch (App::$utente->getStato()) {
                        case Utente::IN_CORSO_ADMIN :
                            $modelLogin = 'accLoginAdmin';
                        case Utente::IN_CORSO :
                            if ($_POST['TOKEN'] != '') {
                                Out::msgStop("Errore Validazione", "Accesso non Valido. Tipo sessione Errato");
                                break;
                            }
                            $model = ($_POST['model']) ? $_POST['model'] : $modelLogin;
                            /* @var $modelObj itaModel */
                            $modelObj = itaModel::getInstance($model);
                            if (!$modelObj) {
                                Out::msgStop("Errore", "Accesso al model $model Fallito.");
                                break;
                            }

                            $modelObj->setModelData($_POST);
                            $modelObj->setEvent($_POST['event']);
                            $modelObj->setElementId($_POST['id']);
                            $modelObj->parseEvent();
                            break;
                        case Utente::AUTENTICATO : case Utente::AUTENTICATO_JNET :
                            if (AppUtility::enforceApplicationLock()) {
                                break;
                            }

                            $renew = ($_POST['event'] == 'ontimer') ? false : true;
                            if (!App::ctrToken($renew)) {
                                break;
                            }
                            App::createPrivPath();
                            $model = $_POST['model'];
                            unset($_POST['model']);

                            if ($_POST['event'] == 'openform') {
                                itaLib::openForm($model);
                            }
                            /* @var $modelObj itaModel */

                            $modelObj = itaModel::getInstance($model);
                            if (!$modelObj) {
                                Out::msgStop("Errore", "Accesso al model $model Fallito.");
                                break;
                            }

                            $modelObj->setModelData($_POST);
                            $modelObj->setEvent($_POST['event']);
                            $modelObj->setElementId($_POST['id']);
                            $modelObj->parseEvent();
                            break;
                        case Utente::AUTENTICATO_ADMIN :
                            $model = $_POST['model'];
                            unset($_POST['model']);
                            if ($_POST['event'] == 'openform') {
                                itaLib::openForm($model);
                            }
                            /* @var $modelObj itaModel */
                            $modelObj = itaModel::getInstance($model);
                            if (!$modelObj) {
                                Out::msgStop("Errore", "Accesso al model $model Fallito.");
                                break;
                            }

                            $modelObj->setModelData($_POST);
                            $modelObj->setEvent($_POST['event']);
                            $modelObj->setElementId($_POST['id']);
                            $modelObj->parseEvent();
                            break;
                        default:
                            Out::msgStop("Errore", "Stato Utente non Valido:" . App::$utente->getStato());
                            App::unload();
                            break;
                    }
                    break;
            }
            if (App::$utente->getKey('envShell')) {
                $modelMap = App::$utente->getKey('modelMap');
                if (!$modelMap[App::$utente->getKey('envShell')]) {
                    $save_stato = App::$utente->getStato();
                    App::close();
                }
            }
            ob_clean();
            echo Out::get('xml');
        } catch (Exception $e) {
            ob_clean();
            if ($e instanceof ItaSecuredException) {
                $codice = $e->getCode();
                $descrizione = $e->getMessage();
                $exceptionDescr = $descrizione  
                        . "<br> Identificativo: <b>" . $e->getExceptionId() . "</b>"
                        . "<br> Per segnalare l'incoveniente annotare l'identivicativo di errore sopra riportato" ;
                Out::msgError(ItaException::$TYPE_ERROR_LIST[$e->getType()], $exceptionDescr);
            } else if ($e instanceof ItaException) {
                $codice = $e->getNativeErrorCode();
                $descrizione = $e->getNativeErroreDesc();
                Out::msgStop('Errore Generale', 'Errore: ' . $codice . '<br> ' . $descrizione . '<br> File: ' . $e->getFile() . '<br>Linea: ' . $e->getLine() . '<br>Trace:' . $e->getTraceAsString());
            } else {
                $codice = $e->getCode();
                $descrizione = $e->getMessage();
                Out::msgStop('Errore Generale', 'Errore: ' . $codice . '<br> ' . $descrizione . '<br> File: ' . $e->getFile() . '<br>Linea: ' . $e->getLine() . '<br>Trace:' . $e->getTraceAsString());
            }

            echo Out::get('xml');
        }
    }

}

function itaRemove_magic_quotes_gpc($value) {
    return is_array($value) ? array_map('itaRemove_magic_quotes_gpc', $value) : stripslashes($value);
}
