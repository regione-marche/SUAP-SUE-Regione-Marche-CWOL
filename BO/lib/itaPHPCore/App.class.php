<?php

/**
 *
 * LIBRERIA APPLICATIVA ITAENGINE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    lib
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2015 Italsoft srl
 * @license
 * @version    14.01.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
class App {

    static private $version = 5.0;
    static private $firephp = '';
    static private $lastErrMessage = '';
    static public $utente;
    static public $tmpToken;
    static public $italsoftDB;
    static public $itaEngineDB;
    static public $server;
    static public $debugFile = "";
    static public $isCli = false;
    static public $clientEngine = 'itaEngine';
//    static public $cache;

    static function setDebugFile($file) {
        self::$debugFile = $file;
    }

    static function getDebugFile() {
        return self::$debugFile;
    }

    /**
     * Ritorna il codice versione di itaEngine Framework base
     * @return String
     */
    static function getVersion() {
        return self::$version;
    }

    /**
     *
     * Inizializza le risorse applicative.
     */
    static function load($cli = false) {
        self::$isCli = $cli;
        self::$lastErrMessage = '';

// LIBRERIE ESTERNE //
        require_once(ITA_LIB_PATH . '/itaException/ItaSecuredException.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaPHPLegacyCode.php');
        require_once(ITA_LIB_PATH . '/JSON/JSON.php');
        require_once(ITA_LIB_PATH . '/snoopy/Snoopy.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaSmarty3.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaPHP.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/eqUtil.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaLib.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaRep.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaPrnt.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/Config.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/Out.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/ItaToken.class.php');
//require_once(ITA_LIB_PATH . '/itaPHPCore/PDFRW.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaModelHelper.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaModel.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/eqAudit.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaBrowser.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaServer.class.php');
//        require_once ITA_BASE_PATH . '/lib/Cache/CacheFactory.class.php';
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaSecurity.class.php');
        /*
         * Hooks manager
         */
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaHooks.class.php');

        Config::loadConfig();
        if (Config::getConf('security.filterinput')) {
            /*
             * Filtro paorle non valide
             * 
             */
            foreach ($_POST as $key => $value) {
                $nToken = itaSecurity::search_injection_patterns($value);
                if ($nToken >= 1) {
                    Out::msgStop('Attenzione', "Sono stati rilevati dati potenzialmente pericolosi nel valore in ingresso \"$key\".<br> La richiesta non può essere elaborata.<br>Verifica attentamente i termini usati nei campi di inserimento.");
                    echo Out::get('xml');
                    exit();
                }
            }
        }
        
        /*
         * Lettura Strategy dbengine
         */
        $dbengine = '';
        if (Config::loadConfig()) {
            $dbengine = Config::getConf('dbms.dbengine');
        }
        
//        self::$cache = CacheFactory::newCache();
      
       
        if (!$dbengine) {
            $dbengine = 'DB';
        }


        if ($dbengine == 'DBPDO') {
            require_once(ITA_LIB_PATH . '/DBPDO/ItaDB.class.php');
            ItaDB::initConnectionPerRequest();
        } else {
            require_once(ITA_LIB_PATH . '/DB/DB.php');
            require_once(ITA_LIB_PATH . '/DB/ItaDB.class.php');
        }

        require_once(ITA_LIB_PATH . '/itaPHPCore/TableView.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaSuggest.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaGenerator.class.php');

        self::$server = new Server();

        if (isset($_POST['clientEngine'])) {
            self::$clientEngine = $_POST['clientEngine'];
        }

        /* carico le configurazioni di base di itaEngine */
        if (!Config::load()) {
            self::$lastErrMessage = 'Caricamento delle configurazioni fallito.';
            return false;
        }
        
        /* setto il timezone dell'applicato */
        $default_timezone = (self::getConf('general.default_timezone')) ? self::getConf('general.default_timezone') : 'Europe/Rome';
        date_default_timezone_set($default_timezone);

        $default_lc_monetary = (self::getConf('general.default_lc_monetary')) ? self::getConf('general.default_lc_monetary') : 'it_IT';
        setlocale(LC_MONETARY, $default_lc_monetary);

        /* creao la path per gli upload */
        if (!itaLib::getUploadPath(true)) {
            self::$lastErrMessage = 'Impossibile creare la cartella per gli uploads.';
            return false;
        }
        /* carico la classe utente */
        require_once(ITA_LIB_PATH . '/itaPHPCore/Utente.class.php');
        if (self::$isCli) {
            self::$utente = new Utente();
        } else {
            if ($_POST['TOKEN']) {
                self::startSession("S-" . $_POST['TOKEN']);
            } else {
                if ($_POST['tmpToken']) {
                    self::startSession($_POST['tmpToken']);
                } else {
                    self::$tmpToken = "S-TMP-" . md5(rand() * time());
                    self::startSession(self::$tmpToken);
                    Out::codice('tmpToken="' . self::$tmpToken . '";');
                    $_SESSION['utente'] = new Utente();
                    $_SESSION['tmpToken'] = self::$tmpToken;
                }
            }
            if (is_object($_SESSION['utente'])) {
                self::$utente = $_SESSION['utente'];
                self::$tmpToken = (key_exists('tmpToken', $_SESSION) == true) ? $_SESSION['tmpToken'] : '';
//Config::loadApps();
            } else {
                self::$lastErrMessage = 'Errore di accesso alla sessione utente.';
                return false;
            }
        }
        if (!self::$itaEngineDB) {
            try {
                self::$itaEngineDB = ItaDB::DBOpen('ITALWEBDB', '');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
                self::close(false);
            }
        }

        if (!self::$italsoftDB) {
            try {
                self::$italsoftDB = ItaDB::DBOpen('italsoft', '');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
                self::close(false);
            }
        }

        /*
         * Scan e caricamento degli hooks
         */
        itaHooks::scan();        

        return true;
    }

    static function getLastErrMessage() {
        return self::$lastErrMessage;
    }

    static function unloadAdmin() {
        self::$utente->logout();
        self::clearSessionCookie();
        $_SESSION = array();
        session_destroy();
    }

    static function unload() {
        if (self::$utente->getKey('TOKEN')) {
            self::clearPrivPath();
            itaLib::deleteAppsTempPath();
            itaLib::deletePrivateUploadPath();
            self::$utente->logout();
        }

        self::clearSessionCookie();
        $_SESSION = array();
        session_destroy();
    }

    static function session_write_close() {
        session_write_close();
    }

    static function session_write_reopen() {
        self::startSession("S-" . self::$utente->getKey('TOKEN'));
        $_SESSION['utente'] = self::$utente;
    }

    static function outBanner() {
        if (self::getConf('general.loginBanner')) {
            if (file_exists(self::getConf('general.loginBanner'))) {
                $strBanner = file_get_contents(self::getConf('general.loginBanner'));
                Out::html('desktop', $strBanner, 'prepend');
            }
        }
    }

    static function openAdmin() {
        self::$utente->logout();
        if (isset($_POST['TOKEN']) && $_POST['TOKEN'] != '') {
            Out::msgStop("Errore di Accesso", "Accesso non Valido. Tipo sessione Errato");
            Return false;
        }
        self::$utente->setStato(Utente::IN_CORSO_ADMIN);
        self::outBanner();
        $model = 'accLoginAdmin';
        itaLib::openForm($model, true, true, '');
        $_POST = array();
        $_POST['event'] = 'openform';
        $phpURL = self::getConf('modelBackEnd.php');
        $appRouteProg = self::getPath('appRoute.' . substr($model, 0, 3));
        require_once $phpURL . '/' . $appRouteProg . '/' . $model . '.php';
        Out::enableBlockMsg();

        $modelObj = new $model();
        $modelObj->setModelData($_POST);
        $modelObj->parseEvent();

        $model();
    }

    static function openMobile() {
        $ditta = $_POST['ditta'];
        self::$utente->logout();
        if ($_POST['TOKEN'] != '') {
            Out::msgStop("Errore di Accesso", "Accesso non Valido. Tipo sessione Errato");
            Return false;
        }
        self::$utente->setStato(Utente::IN_CORSO);

        $ita_login = 'basic';

        if (defined('ITA_LOGIN') && defined('ITA_LOGIN_FORM')) {
            if (ITA_LOGIN) {
                $ita_login = ITA_LOGIN;
            }
        }

        if ($ita_login == 'basic') {
            itaLib::openForm(ITA_LOGIN_FORM, false, true, 'body');
            $modelObj = itaModel::getInstance(ITA_LOGIN_FORM);
            if ($ditta) {
                $_POST['ditta'] = $ditta;
            }
            $modelObj->setModelData($_POST);
            $modelObj->setEvent('openform');
            $modelObj->parseEvent();
        }

        if ($ita_login == 'advanced') {
            $class = 'accValidate.class.php';
            require_once App::getAppFolder($class) . '/' . $class;
            $advancedLogin = accValidate::getInstance(ITA_LOGIN_FORM);
            if ($advancedLogin) {
                $advancedLogin->create();
            }
        }
    }

    static function open() {
        $ditta = isset($_POST['ditta']) ? $_POST['ditta'] : '';
        self::$utente->logout();
        if (isset($_POST['TOKEN']) && $_POST['TOKEN'] != '') {
            Out::msgStop("Errore di Accesso", "Accesso non Valido. Tipo sessione Errato");
            Return false;
        }
        self::$utente->setStato(Utente::IN_CORSO);

        $ita_login = 'basic';
        if (defined('ITA_LOGIN') && defined('ITA_LOGIN_FORM')) {
            if (ITA_LOGIN) {
                $ita_login = ITA_LOGIN;
            }
        }
        //
        // Basic
        //
        if ($ita_login == 'basic') {
            self::outBanner();
            $model = 'accLogin';
            $phpURL = self::getConf('modelBackEnd.php');
            $appRouteProg = self::getPath('appRoute.' . substr($model, 0, 3));
            if (file_exists($phpURL . '/' . $appRouteProg . '/' . $model . '.php')) {
                itaLib::openForm($model, true, true, '');
                $_POST = array();
                $_POST['event'] = 'openform';
                if ($ditta) {
                    $_POST['ditta'] = $ditta;
                }
                require_once $phpURL . '/' . $appRouteProg . '/' . $model . '.php';
                Out::enableBlockMsg();
                $modelObj = new $model();
                $modelObj->setModelData($_POST);
                $modelObj->parseEvent();
            } else {
                Out::msgStop("Errore", "Impossibile caricare $model.php: file non accessibile.");
            }
        }

        //
        // Advanced
        //
        if ($ita_login == 'advanced') {
            $_POST = array();
            $_POST['event'] = 'openform';
            if ($ditta) {
                $_POST['ditta'] = $ditta;
            }
            $class = 'accValidate.class.php';
            require_once App::getAppFolder($class) . '/' . $class;
            $advancedLogin = accValidate::getInstance(ITA_LOGIN_FORM);
            if ($advancedLogin) {
                $advancedLogin->create();
            }
        }
    }

    static function openDesktopAdmin($desktopParam = array()) {
        $_POST = array();
        $_POST['event'] = 'callmodel';
        $_POST['desktopParam'] = $desktopParam;
        $model = 'envDesktopAdmin';
        $phpURL = self::getConf('modelBackEnd.php');
        $appRouteProg = self::getPath('appRoute.' . substr($model, 0, 3));
        require_once $phpURL . '/' . $appRouteProg . '/' . $model . '.php';
        Out::enableBlockMsg();
        $model();
    }

    static function openDesktop($desktopParam = array()) {

        // Rimuovo gli elementi propri del login Advanced
        if (defined('ITA_LOGIN')) {
            if (ITA_LOGIN == 'advanced') {
                $class = 'accValidate.class.php';
                require_once App::getAppFolder($class) . '/' . $class;
                accValidate::destroy();
            }
        }

        $_POST = array();
        $_POST['event'] = 'callmodel';
        $_POST['desktopParam'] = $desktopParam;
        list($model, $rootMenu) = explode(':', ITA_DESKTOP);
//        $model = ITA_DESKTOP;
        $phpURL = self::getConf('modelBackEnd.php');
        $appRouteProg = self::getPath('appRoute.' . substr($model, 0, 3));
        require_once $phpURL . '/' . $appRouteProg . '/' . $model . '.php';
        Out::enableBlockMsg();

        if (isset($rootMenu) && !empty($rootMenu)) {
            $model($rootMenu);
        } else {
            $model();
        }
    }

    static function autoExec() {
        if (ITA_DESKTOP == 'menDesktop') {
            $_POST = array();
            $_POST['event'] = 'callmodel';
            $_POST['menu'] = 'TI_MEN';
            $model = 'menGes';
            $phpURL = self::getConf('modelBackEnd.php');
            $appRouteProg = self::getPath('appRoute.' . substr($model, 0, 3));
            require_once $phpURL . '/' . $appRouteProg . '/' . $model . '.php';
            Out::enableBlockMsg();
            $model();
        } else {
//            $_POST = array();
//            $_POST['event'] = 'openButton';
//            $_POST['menuId'] = 'menu1';
//            $_POST['rootMenu'] = 'TI_MEN';            
//            $model = 'menButton';
//            $phpURL = self::getConf('modelBackEnd.php');
//            $appRouteProg = self::getPath('appRoute.' . substr($model, 0, 3));
//            require_once $phpURL . '/' . $appRouteProg . '/' . $model . '.php';
//            $model();
        }
    }

    static function ctrToken($renew = true) {
        $ditta = App::$utente->getKey('ditta');
        $token = self::$utente->getKey('TOKEN');
        if ($renew) {
            $ret_token = ita_token($token, $ditta, 0, 3);
        } else {
            $ret_token = ita_token($token, $ditta, 0, 103);
        }
        if ($ret_token['status'] == '0') {
            $token = $ret_token['token'];
            return true;
        } else {
            Out::msgStop("Chiusura Sessione di Lavoro.", "<br>" . $ret_token['messaggio']);
            self::close();
            Out::codice('setTimeout(\'reload()\',10000);');
            return false;
        }
    }

    static function removeDesktop() {
        out::codice("removeDesktop();");
    }

    static function closeAdmin($verbose = false) {
        if ($verbose) {
            Out::msgInfo("Chiusura Sessione di Lavoro.", "<br><br><h1>Grazie per aver scelto ITALSOFT</H1>");
        }
        $token = self::$utente->getKey('TOKEN');
        self::removeDesktop();
        self::unload();
        return true;
    }

    static function close($verbose = false) {
        if ($verbose) {
            Out::msgInfo("Chiusura Sessione di Lavoro.", "<br><br><h1>Grazie per aver scelto ITALSOFT</H1>");
        }
        $ditta = self::$utente->getKey('ditta');
        $token = self::$utente->getKey('TOKEN');
        if (!$ditta && !$token) {
            self::removeDesktop();
            self::unload();
            return true;
        }

        if (file_exists(ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php')) {
            require_once (ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php');
            /* @var $envLib envLib */
            $envLib = new envLib();
            if (method_exists($envLib, 'resetSemaforiToken')) {
                $envLib->resetSemaforiToken($token);
            }
            ItaDB::DBUnLockForSession();
        }
        $ret_token = ita_token($token, $ditta, 0, 2);

        self::removeDesktop();
        self::unload();
        return true;
    }

    /**
     *  Invia un log formato firephp
     *  @param mixed $object oggetto da trasmettere alla console firephp
     */
    static function log($object, $suppressNL = false, $debugToFile = false) {
        if (self::getConf('log.active') == 1) {
            if (self::$debugFile && $debugToFile) {
                file_put_contents(self::$debugFile, print_r($object, true), FILE_APPEND);
                if (!$suppressNL) {
                    file_put_contents(self::$debugFile, "\n", FILE_APPEND);
                }
            }
        }
    }

    /**
     *  Crea la directory privata dell'utente
     *
     */
    static function createPrivPath() {
        if (self::getPath('temporary.privatePath') != '') {
            $privPath = self::getPath('temporary.privatePath') . '/organization_' . self::$utente->getKey('ditta') . '/' . self::$utente->getKey('nomeUtente'); // *** AGGIUNGO LA DITTA
            self::$utente->setKey('privPath', $privPath);
            $privUrl = self::getPath('temporary.privateUrl') . '/organization_' . self::$utente->getKey('ditta') . '/' . self::$utente->getKey('nomeUtente');
            self::$utente->setKey('privUrl', $privUrl);
            if (!file_exists($privPath)) {
                if (!mkdir($privPath, 0777, true)) {
                    self::$utente->setKey('privPath', '');
                    self::$utente->setKey('privUrl', '');
                }
            }
        }
    }

    /**
     *  Pulisce la directory privata dell'utente
     *
     */
    static function clearPrivPath() {
        if (self::$utente->getKey('privPath') != '') {
            $privPath = self::$utente->getKey('privPath');
            $fileList = scandir($privPath);
            foreach ($fileList as $file) {
                if (is_file("$privPath/$file") && $file != '.' & $file != '..') {
                    unlink("$privPath/$file");
                }
            }
        }
    }

    static function getAppFolder($model) {
        $phpURL = self::getConf('modelBackEnd.php');
        return $phpURL . "/" . self::getPath('appRoute.' . substr($model, 0, 3));
    }

    static function requireModel($model) {
        try {
            require_once self::getAppFolder($model) . '/' . $model . '.php';
            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    /**
     *  Restituice la configurazione applicativo
     *
     * @param string $key Chiave per l'estrazione del valore Es.: (path.path)
     */
    static function getConf($key) {
        return Config::getConf($key);
    }

    /**
     *  Restituice la path delle applicazioni.
     *
     * @param string $key Chiave per l'estrazione del valore Es.: (path.path)
     */
    static function getPath($key) {
//        $tmp=explode('.', $key);
//        return self::$itaPath[$tmp[0]][$tmp[1]];
        return Config::getPath($key);
    }

    static function getEnti() {
//        return self::$enti;
        return Config::getEnti();
    }

    static function directAccess() {
        $directPost = $_POST;

        if (isset($directPost['externaltoken'])) {
            require_once ITA_BASE_PATH . '/apps/Accessi/accLibExternal.class.php';
            $accLibExternal = new accLibExternal();
            $directPost = $accLibExternal->externalLogin($directPost);
            if (!$directPost) {
                Out::msgStop('Errore di validazione', $accLibExternal->getErrMessage());
                return;
            }
        }

        $ditta = $directPost['accessorg'];
        $token = $directPost['accesstoken'];
        if ($token == 'nobody') {
            require_once(ITA_LIB_PATH . '/itaPHPCore/ItaToken.class.php');
            $ret_verpass = ita_verpass($ditta, 'nobody', 'nobody12');
            if (!$ret_verpass) {
                Out::msgStop("Errore di validazione", $ret_verpass['messaggio']);
                return;
            }

            if ($ret_verpass['status'] != 0 && $ret_verpass['status'] != '-99') {
                Out::msgStop("Errore di validazione", $ret_verpass['messaggio']);
                return;
            }
            if ($ret_verpass['status'] == '-99') {
                Out::msgStop("Errore di validazione", $ret_verpass['messaggio']);
                return;
            }
            $cod_ute = $ret_verpass['codiceUtente'];
            $itaToken = new ItaToken($ditta);
            $ret_token = $itaToken->createToken($cod_ute);
            if ($ret_token['status'] == '0') {
                $token = $itaToken->getTokenKey();
            } else {
                Out::msgStop("Errore di validazione", $ret_token['messaggio']);
                return;
            }
        }

        $topbar = $directPost['topbar'];
        $homepage = $directPost['homepage'];
        $referrer = isset($directPost['accessreturn']) ? $directPost['accessreturn'] : "";
        $tipologiaAccesso = isset($directPost['accesstype']) ? $directPost['accesstype'] : '';
        $datiAccesso = isset($directPost['accessdata']) ? $directPost['accessdata'] : '';

        $ret_token = ita_token($token, $ditta, 0, 3);
        if ($ret_token['status'] == '0') {
            $token = $ret_token['token'];
        } else {
            Out::msgStop("Errore di validazione", $ret_token['messaggio']);
            return;
        }

        $_SESSION = array();
        self::startSession("S-" . $token, true);
        $_SESSION['utente'] = new Utente();
        self::$utente = $_SESSION['utente'];
        self::$utente->setStato(Utente::AUTENTICATO_JNET);
        self::$utente->setKey('ditta', $ditta);
        self::$utente->setKey('TOKEN', $token);
        self::$utente->setKey('nomeUtente', $ret_token['nomeUtente']);
        self::$utente->setKey('idUtente', $ret_token['codiceUtente']);
        self::$utente->setKey('DataLavoro', date('Ymd'));
        self::$utente->setKey('tipoAccesso', 'direct');
        self::$utente->setKey('referrerAccesso', $referrer);
        self::$utente->setKey('tipologiaAccesso', $tipologiaAccesso);
        self::$utente->setKey('datiAccesso', $datiAccesso);
        self::createPrivPath();
        Out::codice('token="' . $token . '";ita_silentClose=true;');

        self::openDesktop(array(
            'topbar' => $topbar,
            'homepage' => $homepage
        ));
        unset($directPost['accesstoken']);
        unset($directPost['accessorg']);
        unset($directPost['accessreturn']);
        unset($directPost['access']);
        unset($directPost['externaltoken']);
        unset($directPost['accesstype']);
        unset($directPost['accessdata']);

        if ($tipologiaAccesso) {
            $eqParams = array(
                'Estremi' => "Accesso diretto con '$tipologiaAccesso'",
                'Operazione' => eqAudit::OP_MISC_AUDIT,
            );

            if (isset($datiAccesso['spidCode'])) {
                $eqParams['SpidCode'] = $datiAccesso['spidCode'];
            }

            $eqAudit = new eqAudit;
            $eqAudit->logEqEvent(self, $eqParams);
        }

        if (method_exists('itaHooks', 'execute')) {
            itaHooks::execute('post_login');
        }

        if ($directPost['model']) {
            $_POST = array();
            $model = $directPost['model'];
            unset($directPost['model']);
            foreach ($directPost as $key => $value) {
                $_POST[$key] = $value;
            }
            $_POST['event'] = 'openform';
            itaLib::openForm($model, true, true, 'desktopBody');
            $appRoute = self::getPath('appRoute.' . substr($model, 0, 3));
            $phpURL = self::getConf('modelBackEnd.php');
            require_once $phpURL . '/' . $appRoute . '/' . $model . '.php';
            $model();
        }
    }

    static function jnetAccess() {
// parametri base di sicurezza //
        $ditta = $_POST['ditta'];
        if (!$_POST['accesstoken']) {
            $_POST['accesstoken'] = $_POST['TOKEN'];
        }
        $token = $_POST['accesstoken']; //MM
        $ret_token = ita_token($token, $ditta, 0, 3);
        if ($ret_token['status'] == '0') {
            $token = $ret_token['token'];
        } else {
            Out::msgStop("Errore di validazione", $ret_token['messaggio'], 'desktop');
            return;
        }
        $_SESSION = array();
        self::startSession("S-" . $token, true);
        $_SESSION['utente'] = new Utente();
        self::$utente = $_SESSION['utente'];
        self::$utente->setStato(Utente::AUTENTICATO_JNET);
        self::$utente->setKey('ditta', $_POST['ditta']);
        self::$utente->setKey('TOKEN', $token);
        self::$utente->setKey('nomeUtente', $ret_token['nomeUtente']);
        self::$utente->setKey('idUtente', $ret_token['codiceUtente']);
        self::$utente->setKey('tipoAccesso', 'j-net');
        self::$utente->setKey('referrerAccesso', "");
        self::$utente->setKey('DataLavoro', date('Ymd'));
        self::createPrivPath();
// ---------------- Da qui in poi sono loggato ----------------

        $Sessio_values = eqUtil::getEqSession($token, 'jnet-post-', false);
        if (!$Sessio_values) {
            Out::msgStop("Errore di Accesso", "Modalità di accesso alla procedura errato.<br><br>Parametri Mancanti.");
            return;
        }
        Out::codice('token="' . $token . '";ita_silentClose=true;');
        self::openDesktop();


// --------------- Qui simulo un evento del browser -------------------
        $_POST = array();
        foreach ($Sessio_values as $Sessio_key => $Sessio_value) {
            $_POST[substr($Sessio_key, 10)] = $Sessio_value;
        }
        if (!eqUtil::delEqSession($token, 'jnet-post-', false)) {
            Out::msgStop("Errore", "Cancellazione Sessione Eq fallita");
        }
        $model = $_POST['model'];
        unset($_POST['model']);
        if ($_POST['event'] == 'openform') {
            itaLib::openForm($model);
        }
        $appRoute = self::getPath('appRoute.' . substr($model, 0, 3));
        $phpURL = self::getConf('modelBackEnd.php');
        require_once $phpURL . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function clearSessionCookie() {
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
            unset($_COOKIE[session_name()]);
        }
    }
    
    /**
     * Termina request
     */
    public static function endRequest() {
        /*
         * Lettura Strategy dbengine
         */
        $dbengine = '';
        if (Config::loadConfig()) {
            $dbengine = Config::getConf('dbms.dbengine');
        }
        if (!$dbengine) {
            $dbengine = 'DB';
        }        
        if ($dbengine == 'DBPDO') {            
            ItaDB::flushConnectionPerRequest();
        }
        
        // Registrazione Hook end_request
        if (method_exists('itaHooks', 'execute')) {
            itaHooks::execute('end_request');
        }
    }

    public static function startSession($sessionName = false, $sessionDestroy = false) {
        if ($sessionDestroy) {
            if ((function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE) || session_id()) {
                session_destroy();
            }
        }

        if ($sessionName) {
            session_name($sessionName);
        }

        $currentCookieParams = session_get_cookie_params();
        session_set_cookie_params(
            $currentCookieParams['lifetime'], '/', '', self::isConnectionSecure(), true
        );

        session_start();
    }

    public static function isConnectionSecure() {
        return isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] !== 'off');
    }

}

/**
 * Caricamento Composer + errorHandler
 */
error_reporting(0);
include ITA_BASE_PATH . '/vendor/autoload.php';
if (file_exists(ITA_BASE_PATH . '/vendor560/vendor/autoload.php')) {
    include ITA_BASE_PATH . '/vendor560/vendor/autoload.php';
}
require_once ITA_LIB_PATH . '/itaPHPCore/itaErrorHandler.class.php';
itaErrorHandler::register();
