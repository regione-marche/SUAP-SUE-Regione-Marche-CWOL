<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class frontOfficeApp {

    static public $cmsHost;
    static public $importscript = false;
    static public $importCSS = false;
    static private $ente;
    static private $firephp = '';
    static private $CRYPT_SECRET = 'change-me';
    static private $filteredInputs = array();

    static function load($cms = "si") {
        if (file_exists(ITA_BASE_PATH . '/vendor/autoload.php')) {
            include_once ITA_BASE_PATH . '/vendor/autoload.php';
        }

        if (file_exists(ITA_BASE_PATH . '/vendor560/vendor/autoload.php')) {
            include_once ITA_BASE_PATH . '/vendor560/vendor/autoload.php';
        }

        require_once(ITA_LIB_PATH . '/itaException/ItaSecuredException.php');
        require_once(ITA_LIB_PATH . '/DB/DB.php');                          // DATABASES
        require_once(ITA_LIB_PATH . '/DB/ItaDB.class.php');                 // DATABASES END USER
        error_reporting(E_ERROR);
        require_once(ITA_LIB_PATH . '/itaPHPCore/eqAudit.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaBrowser.class.php');    // BROWSER INFO
        require_once(ITA_LIB_PATH . '/FirePHPCore/FirePHP.class.php');      // DEBUG FIREBUG E FIREPHP   
        require_once(ITA_LIB_PATH . '/itaPHPPDFA/itaPDFA.class.php');       // ANALISI E CONVERSIONE PDF/A
        require_once(ITA_LIB_PATH . '/itaPHPVersign/itaP7m.class.php');     // VERIFICA FIRMA DIGITALE     
        require_once(ITA_LIB_PATH . "/itaPHPCore/ItaUrlUtilInterface.class.php"); // Interfaccia per ItaUrlUtil
        require_once(ITA_LIB_PATH . "/itaPHPCore/ItaUrlUtil.$cms.class.php");   // UTILITY CMS OSPITE
        require_once(ITA_LIB_PATH . "/itaPHPCore/cmsHost.class.php");           // Oggetto CMS OSPITE
        require_once(ITA_LIB_PATH . '/itaPHPCore/frontOfficeErr.class.php');    // Gestione Errori    
        require_once(ITA_LIB_PATH . '/itaPHPCore/html.class.php');              // Utilit� html
        require_once(ITA_LIB_PATH . '/itaPHPCore/output.class.php');            // Standard output
        require_once(ITA_LIB_PATH . "/itaPHPCore/itaImg.class.php");   // 
        require_once(ITA_LIB_PATH . "/itaPHPCore/frontOfficeLib.class.php");   // 
        require_once(ITA_LIB_PATH . "/itaPHPCore/itaTableSorter.class.php");  // html del tablesorter
        require_once(ITA_LIB_PATH . "/itaPHPCore/itaModelFO.class.php");
        require_once(ITA_LIB_PATH . "/itaPHPCore/itaFrontOfficeResourceConf.class.php");
        require_once ITA_LIB_PATH . '/itaPHPCore/itaCrypt.class.php';

        if (defined('ITA_FRONTOFFICE_SECURITY_FILTER_INPUT') && ITA_FRONTOFFICE_SECURITY_FILTER_INPUT) {
            require_once ITA_LIB_PATH . '/itaPHPCore/itaSecurity.class.php';
            $_GET = self::filterInput($_GET);
            $_POST = self::filterInput($_POST);
            $_REQUEST = self::filterInput($_REQUEST);
            self::$filteredInputs = array_unique(self::$filteredInputs);
        }

        self::$cmsHost = cmsHost::getInstance($cms);

        if (defined('ITA_ERROR_HANDLER')) {
            set_error_handler(array('frontOfficeApp', 'errorHandler'), E_ALL);
            register_shutdown_function(array('frontOfficeApp', 'shutdownHandler'));
        }

        /* setto il timezone dell'applicato */
        if (!defined('ITA_FRONTOFFICE_TIMEZONE')) {
            $default_timezone = 'Europe/Rome';
        } else {
            $default_timezone = (ITA_FRONTOFFICE_TIMEZONE) ? ITA_FRONTOFFICE_TIMEZONE : 'Europe/Rome';
        }
        date_default_timezone_set($default_timezone);
    }

    static function log($object) {
        if (self::$firephp) {
            self::$firephp->log($object);
        }
    }

    static function encrypt($str) {
        $timestamp = time();
        $str .= "|$timestamp";
        $mcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(self::$CRYPT_SECRET), utf8_encode($str), MCRYPT_MODE_CBC, md5(self::$CRYPT_SECRET));
        return rtrim(strtr(base64_encode($mcrypt), '+/', '-_'), '=');
    }

    static function decrypt($encrypt) {
        $base64 = base64_decode(str_pad(strtr($encrypt, '-_', '+/'), strlen($encrypt) % 4, '=', STR_PAD_RIGHT));
        $decriptedStr = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(self::$CRYPT_SECRET), $base64, MCRYPT_MODE_CBC, md5(self::$CRYPT_SECRET)));
        list($key, $timestamp) = explode("|", $decriptedStr);
        if (!$timestamp) {
            return false;
        }
        if (time() > $timestamp + (60 * 30)) {
            return false;
        }
        return $key;
    }

    static function getBrowser() {
        return '';
    }

    static function addCSSFrontOffice($blocco = null) {
        $include_uri = ItaUrlUtil::UrlInc();

        if (self::$importCSS == false) {
            self::$cmsHost->addCSS($include_uri . '/vendor/jquery-ui/' . itaFrontOfficeReosurceConf::JQUERY_UI_VERSION . '/jquery-ui.min.css', $blocco);
            self::$cmsHost->addCSS($include_uri . '/vendor/jquery-ui/' . itaFrontOfficeReosurceConf::JQUERY_UI_VERSION . '/jquery-ui.theme.css', $blocco);

            self::$cmsHost->addCSS($include_uri . '/vendor/tablesorter/' . itaFrontOfficeReosurceConf::JQUERY_TABLESORTER_VERSION . '/css/theme.blue.min.css', $blocco);

            self::$cmsHost->addCSS($include_uri . '/vendor/ionicons/2.0.0/css/ionicons.min.css', $blocco);

            self::$cmsHost->addCSS($include_uri . '/vendor/simplegrid/simplegrid.css', $blocco);

            self::$cmsHost->addCSS($include_uri . '/css/style.css?t=' . time(), $blocco);
            self::$cmsHost->addCSS($include_uri . '/css/responsive.css', $blocco);
            self::$cmsHost->addCSSPrint($include_uri . '/css/print.css', $blocco);
        }

        self::$importCSS = true;
    }

    static function addJsFrontOffice($blocco = null) {
        $include_uri = ItaUrlUtil::UrlInc();

        if (self::$importscript == false) {
//            self::$cmsHost->addJs($include_uri . '/vendor/jquery/' . itaFrontOfficeReosurceConf::JQUERY_VERSION . '/jquery.min.js', $blocco);

            self::$cmsHost->addJs($include_uri . '/vendor/jquery-ui/' . itaFrontOfficeReosurceConf::JQUERY_UI_VERSION . '/jquery-ui.min.js', $blocco);
//            self::$cmsHost->addJs($include_uri . '/vendor/jquery-ui/' . itaFrontOfficeReosurceConf::JQUERY_UI_VERSION . '/i18n/jquery.ui.datepicker-it.js', $blocco);

            self::$cmsHost->addJs($include_uri . '/vendor/tablesorter/' . itaFrontOfficeReosurceConf::JQUERY_TABLESORTER_VERSION . '/js/jquery.tablesorter.min.js', $blocco);
            self::$cmsHost->addJs($include_uri . '/vendor/tablesorter/' . itaFrontOfficeReosurceConf::JQUERY_TABLESORTER_VERSION . '/js/extras/jquery.metadata.min.js', $blocco);
            self::$cmsHost->addJs($include_uri . '/vendor/tablesorter/' . itaFrontOfficeReosurceConf::JQUERY_TABLESORTER_VERSION . '/js/extras/jquery.tablesorter.pager.min.js', $blocco);
            self::$cmsHost->addJs($include_uri . '/vendor/tablesorter/' . itaFrontOfficeReosurceConf::JQUERY_TABLESORTER_VERSION . '/js/widgets/widget-reflow.min.js', $blocco);
            self::$cmsHost->addJs($include_uri . '/vendor/tablesorter/' . itaFrontOfficeReosurceConf::JQUERY_TABLESORTER_VERSION . '/js/widgets/widget-uitheme.min.js', $blocco);
            self::$cmsHost->addJs($include_uri . '/vendor/tablesorter/' . itaFrontOfficeReosurceConf::JQUERY_TABLESORTER_VERSION . '/js/widgets/widget-cssStickyHeaders.min.js', $blocco);
            self::$cmsHost->addJs($include_uri . '/vendor/tablesorter/' . itaFrontOfficeReosurceConf::JQUERY_TABLESORTER_VERSION . '/js/jquery.tablesorter.widgets.js', $blocco);

            foreach (itaFrontOfficeReosurceConf::$jquery_plugin as $k => $v) {
                self::$cmsHost->addJs($include_uri . '/vendor/jquery.plugins/' . $k . '.' . $v . '.js', $blocco);
            }

            self::$cmsHost->addJs($include_uri . '/js/itaFrontOffice.js?t=' . time(), $blocco);
            self::$cmsHost->addJs($include_uri . '/js/ieutils.js', $blocco);
        }

        self::$importscript = true;
    }

    static function addCSSFrontOfficeAdmin($blocco = null) {
        $include_uri = ItaUrlUtil::UrlInc();

        if (self::$importCSS == false) {
            self::$cmsHost->addCSS($include_uri . '/vendor/codemirror/lib/codemirror.css', $blocco);
        }

        self::$importCSS = true;
    }

    static function addJsFrontOfficeAdmin($blocco = null) {
        $include_uri = ItaUrlUtil::UrlInc();

        if (self::$importscript == false) {
            self::$cmsHost->addJs($include_uri . '/vendor/codemirror/lib/codemirror.js', $blocco);
            self::$cmsHost->addJs($include_uri . '/vendor/codemirror/mode/clike/clike.js', $blocco);
            self::$cmsHost->addJs($include_uri . '/vendor/codemirror/mode/php/php.js', $blocco);
        }

        self::$importscript = true;
    }

    static function setEnte($ente) {
        self::$ente = $ente;
    }

    static function getEnte() {
        if (!is_null(self::$ente)) {
            return self::$ente;
        }

        if (defined('ITA_DB_SUFFIX')) {
            return ITA_DB_SUFFIX;
        }

        return false;
    }

    static function shutdownHandler() {
        $error = error_get_last();
        if ($error !== NULL) {
            self::errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    static function errorHandler($errno, $errstr, $errfile, $errline) {
        if (!defined('ITA_ERROR_HANDLER_OUTPUT')) {
            return false;
        }

        $errors = E_PARSE | E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;
        $warnings = $errors | E_USER_WARNING | E_COMPILE_WARNING | E_CORE_WARNING | E_WARNING;

        switch (ITA_ERROR_HANDLER) {
            case 1:
                if (!($errno & $errors)) {
                    return false;
                }
                break;

            case 2:
                if (!($errno & $warnings)) {
                    return false;
                }
                break;

            case 3:
                break;

            default:
                return false;
        }

        $handler_output = (int) ITA_ERROR_HANDLER_OUTPUT;

        $errtype = frontOfficeLib::friendlyErrorType($errno);

        switch ($handler_output) {
            case 1:
                $html = new html();

                $alertType = 'info';
                if ($errno & $errors) {
                    $alertType = 'error';
                } else if ($errno & $warnings) {
                    $alertType = 'warning';
                }

                echo $html->getAlert("$errstr on $errfile:$errline", "Errore ($errtype)", $alertType);
                break;

            case 2:
                switch ($errno) {
                    case E_ERROR:
                        $logpriority = LOG_ERR;
                        break;

                    case E_WARNING:
                        $logpriority = LOG_WARNING;
                        break;

                    case E_NOTICE:
                        $logpriority = LOG_NOTICE;
                        break;

                    case E_DEPRECATED:
                    case E_STRICT:
                    default:
                        $logpriority = LOG_INFO;
                        break;
                }

                syslog($logpriority, date('[d/M/y:H:i:sO]') . " $errstr on $errfile:$errline\n");
                break;

            case 3:
                if (defined('ITA_ERROR_HANDLER_FILE')) {
                    file_put_contents(ITA_ERROR_HANDLER_FILE, date('[d/M/y:H:i:sO]') . " ($errtype) $errstr on $errfile:$errline\n", FILE_APPEND);
                } else {
                    return false;
                }
                break;

            default:
                return false;
        }

        if ($errno & $errors) {
            exit;
        }

        return true;
    }

    /**
     * Filtro parole non valide
     * @param array $request
     */
    private static function filterInput($request) {
        foreach ($request as $key => $value) {
            if (is_array($value)) {
                $request[$key] = self::filterInput($value);
            } else {
                $nToken = itaSecurity::search_injection_patterns($value);
                if ($nToken >= 1) {
                    $request[$key] = '';
                    self::$filteredInputs[] = $key;
                }
            }
        }

        return $request;
    }

    static public function getFilteredInputs() {
        return self::$filteredInputs;
    }

}
