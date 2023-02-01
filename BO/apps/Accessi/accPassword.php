<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLibCityWare.class.php';

function accPassword() {
    $accPassword = new accPassword();
    $accPassword->parseEvent();
    return;
}

class accPassword extends itaModel {

    public $ITW_DB;
    public $nameForm = "accPassword";
    public $divGes = "accPassword_divGestione";
    public $parametriPwd = array();
    public $codiceUtenteGestito;
    public $nomeUtenteGestito;
    public $modo;
    public $returnUtecod;
    public $returnPassword;
    public $returnEncPassword;
    public $accLib;
    public $accLibCityWare;

    function __construct() {
        parent::__construct();
        $this->accLib = new accLib();
        $this->accLibCityWare = new accLibCityWare();
        // Apro il DB
        try {
            $this->ITW_DB = ItaDB::DBOpen('ITW');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->modo = App::$utente->getKey($this->nameForm . '_modo');
        $this->codiceUtenteGestito = App::$utente->getKey($this->nameForm . '_codiceUtenteGestito');
        $this->nomeUtenteGestito = App::$utente->getKey($this->nameForm . '_nomeUtenteGestito');
        $this->returnUtecod = App::$utente->getKey($this->nameForm . '_returnUtecod');
        $this->returnPassword = App::$utente->getKey($this->nameForm . '_returnPassword');
        $this->returnEncPassword = App::$utente->getKey($this->nameForm . '_returnEncPassword');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_modo', $this->modo);
            App::$utente->setKey($this->nameForm . '_codiceUtenteGestito', $this->codiceUtenteGestito);
            App::$utente->setKey($this->nameForm . '_nomeUtenteGestito', $this->nomeUtenteGestito);
            App::$utente->setKey($this->nameForm . '_returnUtecod', $this->returnUtecod);
            App::$utente->setKey($this->nameForm . '_returnPassword', $this->returnPassword);
            App::$utente->setKey($this->nameForm . '_returnEncPassword', $this->returnEncPassword);
        }
    }

    public function setModo($modo) {
        $this->modo = $modo;
    }

    public function setCodiceUtenteGestito($codiceUtenteGestito) {
        $this->codiceUtenteGestito = $codiceUtenteGestito;
    }

    public function setNomeUtenteGestito($nomeUtenteGestito) {
        $this->nomeUtenteGestito = $nomeUtenteGestito;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if (!$this->modo) {
                    $this->modo = '';
                    if (isset($_POST['modo'])) {
                        $this->modo = $_POST['modo'];
                    }
                }
                switch ($this->modo) {
                    case '':
                        $this->codiceUtenteGestito = App::$utente->getKey('idUtente');
                        $this->nomeUtenteGestito = App::$utente->getKey('nomeUtente');
                        break;
                    case 'nuovo':
                    case 'reset':
                    case 'gestione':
                        $this->codiceUtenteGestito = isset($this->codiceUtenteGestito) ? $this->codiceUtenteGestito : $_POST['UTECOD'];
                        $this->nomeUtenteGestito = isset($this->nomeUtenteGestito) ? $this->nomeUtenteGestito : $_POST['UTELOG'];
                        break;
                    case 'modifica':
                    default:
                        $this->codiceUtenteGestito = App::$utente->getKey('idUtente');
                        $this->nomeUtenteGestito = App::$utente->getKey('nomeUtente');
                        break;
                }
                $this->OpenGestione();
                break;
            case 'dbClickRow':
                break;
            case 'printTableToHTML':
                break;
            case 'exportTableToExcel':
                break;
            case 'onClickTablePager':
                break;
            case 'onChange':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . "_Continua":
                        if ($this->modo == 'login') {
                            $Utenti_rec = $this->accLib->GetUtenti($this->codiceUtenteGestito);
                            if ($Utenti_rec) {
                                $this->accLib->updateUserLastAccess($this->ITW_DB, $Utenti_rec);
                            }
                            $this->close();
                            App::openDesktop();
                            if (method_exists('itaHooks', 'execute')) {
                                itaHooks::execute('post_login');
                            }
                            App::autoExec();
                        } else {
                            $this->returnToParent();
                        }

                        break;

                    case $this->nameForm . '_Aggiorna':
                        $Newpassword = $_POST[$this->nameForm . "_Newpassword"];
                        $Confpassword = $_POST[$this->nameForm . "_Confpassword"];

                        if ($Newpassword != $Confpassword) {
                            Out::msgStop("Attenzione", "La due password non coincidono.");
                            Out::valore($this->nameForm . "_Newpassword", '');
                            Out::valore($this->nameForm . "_Confpassword", '');
                            Out::setFocus('', $this->nameForm . '_Newpassword');
                            break;
                        }

                        // LEGGO I PARAMETRI DI CONTROLLO E COMPOSIZIONE DELLA PASSWORD
                        $this->parametriPwd();
                        //  ESEGUO I CONTROLLI IN BASE AI PARAMETRI
                        $ctrPwd = $this->controlloPwd($Confpassword);

                        if ($ctrPwd != '') {
                            Out::msgStop("Attenzione", $ctrPwd);
                            Out::valore($this->nameForm . "_Newpassword", '');
                            Out::valore($this->nameForm . "_Confpassword", '');
                            Out::setFocus('', $this->nameForm . '_Newpassword');
                            break;
                        }

                        $encryptedPassword = $this->accLib->getEncryptedPassword($Confpassword);
                        if ($encryptedPassword === false) {
                            Out::msgStop('Errore', $this->accLib->getErrMessage());
                            break;
                        }

                        $utenti_rec = $this->accLib->GetUtenti($this->codiceUtenteGestito);
                        $utenti_rec['UTEPAS'] = $encryptedPassword;
                        $utenti_rec['UTEUPA'] = date("Ymd");
                        $giorni = 0;
                        $infoUtente = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT UTEDPA FROM UTENTI WHERE UTECOD={$this->codiceUtenteGestito}", false);
                        if ($infoUtente) {
                            $giorni = $infoUtente['UTEDPA'];
                        }
                        if (!$giorni) {
                            $giorni = $this->parametriPwd[2];
                        }

                        $dataScadenza = new DateTime();
                        $dataScadenza->add(new DateInterval('P' . $giorni . 'D'));

                        $utenti_rec['UTESPA'] = $dataScadenza->format('Ymd');
                        $utenti_rec['UTECOD'] = $this->codiceUtenteGestito;
                        switch ($this->modo) {
                            case 'reset':
                            case 'nuovo':
                                //case 'nuovo':case 'gestione':case 'reset':
                                $utenti_rec['UTEUPA'] = date("Ymd");
                                $utenti_rec['UTESPA'] = date("Ymd");

                                /*
                                 * Salvo il flag 'FlagResetPassword'
                                 */

                                $env_utemeta_rec = $this->accLib->GetEnv_Utemeta($this->codiceUtenteGestito, 'codice', 'FlagResetPassword');
                                if (!$env_utemeta_rec) {
                                    $env_utemeta_rec = array();
                                    $env_utemeta_rec['UTECOD'] = $this->codiceUtenteGestito;
                                    $env_utemeta_rec['METAKEY'] = 'FlagResetPassword';
                                    $env_utemeta_rec['METAVALUE'] = '1';

                                    $this->insertRecord($this->accLib->getITALWEB(), 'ENV_UTEMETA', $env_utemeta_rec, '');
                                }
                                break;

                            case 'modifica':
                            case 'login':
                                /*
                                 * Se impostato, rimuovo il flag 'FlagResetPassword'
                                 */

                                $env_utemeta_rec = $this->accLib->GetEnv_Utemeta($this->codiceUtenteGestito, 'codice', 'FlagResetPassword');
                                if ($env_utemeta_rec) {
                                    $this->deleteRecord($this->accLib->getITALWEB(), 'ENV_UTEMETA', $env_utemeta_rec['ROWID'], '');
                                }
                                break;
                        }

                        try {
                            $nRows = ItaDB::DBUpdate($this->ITW_DB, 'UTENTI', 'UTECOD', $utenti_rec);
                            $this->returnUtecod = $utenti_rec['UTECOD'];
                            $this->returnPassword = $Confpassword;
                            $this->returnEncPassword = $encryptedPassword;

                            /*
                             * Aggiornamento Dati CityWare
                             */

                            if ($this->accLib->isSSOCitywareEnabled()) {
                                $utelog_rec = itaDB::DBSQLSelect($this->ITW_DB, "SELECT UTELOG FROM UTENTI WHERE UTECOD = '{$utenti_rec['UTECOD']}'", false);

                                if (!$this->accLibCityWare->updateUserPassword($utelog_rec['UTELOG'], $Confpassword)) {
                                    Out::msgStop('Errore', $this->accLibCityWare->getErrMessage());
                                    break;
                                }
                            }

                            Out::msgQuestion("Aggiornato", "Password Aggiornata", array(
                                'Continua' => array('id' => $this->nameForm . "_Continua", 'model' => $this->nameForm))
                            );

                            break;
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Aggiornamento su ANAGRAFICA Utenti", $e->getMessage());
                            break;
                        }
                        break;

                    case 'close-portlet':
                        switch ($this->modo) {
                            case 'nuovo':
                            case 'gestione':
                            case 'reset':
                            case 'modifica':
                                $this->returnToParent();
                                break;
                            case 'login':
                                Out::codice('location.reload();');
                                break;
                            default:
                                App::openDesktop();
                                App::autoExec();
                                break;
                        }
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_modo');
        App::$utente->removeKey($this->nameForm . '_codiceUtenteGestito');
        App::$utente->removeKey($this->nameForm . '_nomeUtenteGestito');
        App::$utente->removeKey($this->nameForm . '_returnUtecod');
        App::$utente->removeKey($this->nameForm . '_returnPassword');
        App::$utente->removeKey($this->nameForm . '_returnEncPassword');

        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($this->returnModel) {
            $returnModelObj = itaModel::getInstance($this->returnModel);
            if ($returnModelObj == false) {
                return;
            }
            $_POST['returnUtecod'] = $this->returnUtecod;
            $_POST['returnPassword'] = $this->returnPassword;
            $_POST['returnEncPassword'] = $this->returnEncPassword;

            $returnModelObj->setEvent($this->returnEvent);
            $returnModelObj->parseEvent();
        }
        if ($close) {

            $this->close();
        }
    }

    public function OpenGestione() {
        Out::show($this->divGes, '');
        Out::show($this->nameForm . '');
        Out::setFocus('', $this->nameForm . '_Newpassword');
        $this->parametriPwd();
        $min = 8;
        $max = $this->parametriPwd[0];
        switch ($this->parametriPwd[1]) {
            case '1' :
                $cont = 'solo lettere';
                break;
            case '2':
                $cont = 'solo numeri';
                break;
            case '3':
                $cont = 'sia lettere che numeri';
                break;
        }
        $messaggio = '';
        $style1 = '"font-size:1.2em;color:red;font-weight:bold;"';
        $style2 = '"font-weight:bold;text-decoration:underline;"';
        switch ($this->modo) {
            case 'nuovo':
                $messaggio = '<strong style=' . $style1 . '>Inserire la password di sicurezza iniziale.<br>La password dovrà essere cambiata dall\'utente al primo accesso.</strong>';
                break;
            case 'gestione':
                $messaggio = '<strong style=' . $style1 . '>Password di sicurezza iniziale mancante. Inserire.<br>La password dovrà essere cambiata dall\'utente al primo accesso.</strong>';
                break;
            case 'reset':
                $messaggio = '<strong style=' . $style1 . '>Inserire una nuova password di sicurezza.<br>La password dovrà essere cambiata dall\'utente al primo accesso.</strong>';
                break;
            case 'login':
            case 'modifica':
                $messaggio = '<strong style=' . $style1 . '>Inserire la nuova password di sicurezza.</strong>';
                break;
            default:
                $messaggio = '<strong style=' . $style1 . '>Inserire una nuova password di sicurezza.<br>La password dovrà essere cambiata dall\'utente al primo accesso.</strong>';
                break;
        }

        $regolePassword = '<span style=' . $style1 . '>La password che viene richiesta ha le seguenti regole:</span><br>';
        $regolePassword .= '<br><span style=' . $style2 . '>Lunghezza minima ' . $min . '</span>';
        $regolePassword .= '<br><span style=' . $style2 . '>Lunghezza massima ' . $max . '</span>';
        $regolePassword .= '<br><span style=' . $style2 . '>Può contenere soltanto i simboli</span> ' . implode(' ', str_split(accLibCityWare::PASSWORD_SYMBOLS)) . '</span>';
        $regolePassword .= '<br><span style=' . $style2 . '>Deve contenere ' . $cont . '</span>';

        Out::html($this->nameForm . '_boxMsg', '<center><strong><span style="font-size:1.5em;font-weight:bold;">' . $this->nomeUtenteGestito . '</span></strong></center><br>' . $messaggio . '<br>' . $regolePassword . '<br><br>');
    }

    public function parametriPwd() {
        $secureBackEnd = App::getConf('security.secure-BackEnd');
        switch ($secureBackEnd) {
            case 'eq' :
                //
                //  Tabcod = 2      Lunghezza Massima della password
                //  Tabcod = 3      Password con: 1=solo lettere 2=solo numeri 3=lettere e numeri
                //  Tabcod = 5      Giorni di validità della Password (default 180 giorni)
                //
                $tabpar_tab = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT * FROM TABPAR WHERE TABCOD IN (2,3,5)");
                foreach ($tabpar_tab as $tabpar_rec) {
                    $this->parametriPwd[] = $tabpar_rec['TABPAR'];
                }
                return $this->parametriPwd;

            default:
                $this->parametriPwd[0] = '16';         // lunghezza password
                $this->parametriPwd[1] = '3';         // password con lettere e numeri
                $this->parametriPwd[2] = '180';       // giorni validità password
                break;
        }
    }

    public function controlloPwd($pwd) {
        $ctrPwd = '';

        $pwd = strtolower($pwd);

        if (strlen($pwd) < 8 || strlen($pwd) > $this->parametriPwd[0]) {
            $ctrPwd = 'La password ha una lunghezza diversa da quella consentita.<br>';
        }

        switch ($this->parametriPwd[1]) {
            case '1' :      // Password con solo lettere
                if (preg_match('/[0-9]/', $pwd)) {
                    $ctrPwd .= 'La password non può contenere numeri.<br>';
                }
                break;

            case '2':      // Password con solo numeri
                if (preg_match('/[a-z]/i', $pwd)) {
                    $ctrPwd .= 'La password non può contenere lettere.<br>';
                }
                break;

            case '3':      // Password con lettere e numeri
                if (!preg_match('/[0-9]/', $pwd) || !preg_match('/[a-z]/i', $pwd)) {
                    $ctrPwd .= 'La password deve contenere lettere e numeri.<br>';
                }
                break;
        }

        if (preg_match('/[^0-9a-z' . preg_quote(accLibCityWare::PASSWORD_SYMBOLS, '/') . ']/', $pwd)) {
            $ctrPwd .= 'La password contiene caratteri non consentiti.<br>';
        }

        return $ctrPwd;
    }

}

?>