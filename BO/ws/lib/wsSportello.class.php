<?php

error_reporting(E_ERROR);
require_once '../Config.sportello.inc.php';

if (!defined('ITA_FRONTOFFICE_PATH')) {
    die;
}

$cms = 'wp';
if (defined('ITA_FRONTOFFICE_CMS')) {
    $cms = ITA_FRONTOFFICE_CMS;
}

switch ($cms) {
    case 'wp':
    default:
        define('ITA_FRONTOFFICE_PLUGIN_DIR', ITA_FRONTOFFICE_PATH . '/wp-content/plugins');
        break;
}

require_once ITA_FRONTOFFICE_PLUGIN_DIR . '/itaFrontOffice/lib/nusoap/nusoap.php';

class wsSportello extends soap_server {

    public static $userCode = null;
    public static $userName = null;
    private static $server = null;

    public static function load() {
        define('ITA_FRONTOFFICE_PLUGIN', ITA_FRONTOFFICE_PLUGIN_DIR . '/itaFrontOffice');
        define('ITA_FRONTOFFICE_INCLUDES', ITA_FRONTOFFICE_PLUGIN . '/includes');

        //
        // Carico la configurazione del plug-in
        //

        if (!file_exists(ITA_FRONTOFFICE_PLUGIN . '/config.inc.php')) {
            die('Configurazione itaFrontOffice non trovata');
        }

        require_once ITA_FRONTOFFICE_PLUGIN . '/config.inc.php';

        require_once ITA_LIB_PATH . '/itaPHPCore/frontOfficeApp.class.php'; // Carico la classe base del FrameWork

        frontOfficeApp::load('soap');

        require_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';
    }

    public static function loadPlugins($DomainCode) {
        define('ITA_SUAP_PATH', ITA_FRONTOFFICE_PLUGIN_DIR . '/suap_italsoft/includes');
        define('ITA_PRATICHE_PATH', ITA_SUAP_PATH);

        require_once ITA_SUAP_PATH . '/SUAP_italsoft/suapApp.class.php';

        if (file_exists(ITA_SUAP_PATH . "/../config.inc.$DomainCode.php")) {
            require_once ITA_SUAP_PATH . "/../config.inc.$DomainCode.php";
        } else {
            /*
             * Cerco la configurazione nella cartella del SUE.
             */

            $sue_base_path = ITA_FRONTOFFICE_PLUGIN_DIR . '/sue_italsoft';

            if (!file_exists("$sue_base_path/config.inc.$DomainCode.php")) {
                return new nusoap_fault('ERR', '', "Configurazione per ente $DomainCode mancante", '');
            }

            require_once "$sue_base_path/config.inc.$DomainCode.php";
        }

        frontOfficeApp::setEnte($DomainCode);

        suapApp::load();
        require_once ITA_LIB_PATH . '/itaPHPCore/itaMailer.class.php';
        require_once ITA_LIB_PATH . '/QXml/QXml.class.php';
        require_once ITA_LIB_PATH . '/itaPHPDocs/itaDictionary.class.php';
        require_once ITA_LIB_PATH . '/dompdf-0.6/dompdf_config.inc.php';
        require_once ITA_LIB_PATH . '/zip/itaZip.class.php';
        require_once ITA_SUAP_PATH . '/SUAP_praMup/praMup.php';
    }

    public static function login($TokenKey, $DomainCode = '') {
        if ($TokenKey == '') {
            return new nusoap_fault('ERRTOKEN', '', 'Token Mancante', '');
        }

        if (!$DomainCode) {
            return new nusoap_fault('ERRTOKEN', '', 'Codice Ente/Organizzazione Mancante', '');
        }

        $tokenInfo = self::GetItaEngineContextTokenInfo($TokenKey, $DomainCode);

        if ($tokenInfo instanceof nusoap_fault) {
            return $tokenInfo;
        }

        self::$userCode = $tokenInfo['UserCode'];
        self::$userName = $tokenInfo['UserName'];

//        App::$utente->setStato(Utente::AUTENTICATO);
//        App::$utente->setKey('ditta', $DomainCode);
//        App::$utente->setKey('TOKEN', $TokenKey);
//        App::$utente->setKey('nomeUtente', $tokenInfo['TOKLOGNAME']);
//        App::$utente->setKey('idUtente', $tokenInfo['TOKUTE']);
//        App::$utente->setKey('TOKCOD', $tokenInfo['TOKCOD']);
//        App::$utente->setKey('tipoAccesso', 'validate');
//        App::$utente->setKey('referrerAccesso', '');
//        App::$utente->setKey('DataLavoro', date('Ymd'));
//        App::$utente->setKey('lingua', 'it');

        return true;
    }

    /**
     * 
     * @param type $wsName      nome del web server
     * @param type $namespace   name space
     * @param type $soapaction  the soapaction for the method or false
     * @param type $style       optional (rpc|document) or false Note: when 'document' is specified, parameter and return wrappers are created for you automatically
     * @param type $use         optional (encoded|literal) or false
     * @return boolean
     */
    public static function getWsSportelloInstance($wsName, $namespace, $soapaction = false, $style = false, $use = false) {
        try {
            if (self::$server == null) {
                self::$server = new wsSportello();
                self::$server->debug_flag = false;
                self::$server->configureWSDL($wsName, "urn:$wsName");
                self::$server->wsdl->schemaTargetNamespace = $namespace;

                self::$server->register('GetItaEngineContextToken', array(
                    'userName' => 'xsd:string',
                    'userPassword' => 'xsd:string',
                    'domainCode' => 'xsd:string'
                    ), array('return' => 'xsd:string'), $ns, $soapaction, $style, $use
                );

                self::$server->register('GetItaEngineContextTokenInfo', array(
                    'token' => 'xsd:string',
                    'domainCode' => 'xsd:string'
                    ), array('return' => 'xsd:string'), $ns, $soapaction, $style, $use
                );

                self::$server->register('CheckItaEngineContextToken', array(
                    'token' => 'xsd:string',
                    'domainCode' => 'xsd:string'
                    ), array('return' => 'xsd:string'), $ns, $soapaction, $style, $use
                );

                self::$server->register('DestroyItaEngineContextToken', array(
                    'token' => 'xsd:string',
                    'domainCode' => 'xsd:string'
                    ), array('return' => 'xsd:string'), $ns, $soapaction, $style, $use
                );
            }

            return self::$server;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function GetItaEngineContextToken($UserName, $UserPassword, $DomainCode) {
        if (!$UserName) {
            return 'Errore';
        }

        $restResult = self::wsRestCall('GetItaEngineContextToken', array(
                'UserName' => $UserName,
                'Password' => $UserPassword,
                'DomainCode' => $DomainCode
        ));

        if ($restResult instanceof nusoap_fault) {
            return $restResult;
        }

        return $restResult;
    }

    public static function GetItaEngineContextTokenInfo($TokenKey, $DomainCode) {
        if (!$TokenKey) {
            return new nusoap_fault('ERRTOKEN', '', 'Token Mancante', '');
        }

        if ($DomainCode == '') {
            list($token, $DomainCode) = explode('-', $TokenKey);
        }

        if (!$DomainCode) {
            return new nusoap_fault('ERRTOKEN', '', 'Codice Ente/Organizzazione Mancante', '');
        }

        $restResult = self::wsRestCall('GetItaEngineContextTokenInfo', array(
                'TokenKey' => $TokenKey,
                'DomainCode' => $DomainCode
        ));

        if ($restResult instanceof nusoap_fault) {
            return $restResult;
        }

        $result = json_decode($restResult, true);

        if ($result['return'] !== 'Valid') {
            return new nusoap_fault('ERRTOKEN', '', $result['return'], '');
        }

        return $result;
    }

    public static function CheckItaEngineContextToken($TokenKey, $DomainCode = '') {
        if (!$TokenKey) {
            return new nusoap_fault('ERRTOKEN', '', 'Token Mancante', '');
        }

        if ($DomainCode == '') {
            list($token, $DomainCode) = explode('-', $TokenKey);
        }

        if (!$DomainCode) {
            return new nusoap_fault('ERRTOKEN', '', 'Codice Ente/Organizzazione Mancante', '');
        }

        $restResult = self::wsRestCall('CheckItaEngineContextToken', array(
                'TokenKey' => $TokenKey,
                'DomainCode' => $DomainCode
        ));

        if ($restResult instanceof nusoap_fault) {
            return $restResult;
        }

        return json_decode($restResult, true);
    }

    public static function DestroyItaEngineContextToken($TokenKey, $DomainCode = '') {
        if (!$TokenKey) {
            return new nusoap_fault('ERRTOKEN', '', 'Token Mancante', '');
        }

        if ($DomainCode == '') {
            list($token, $DomainCode) = explode('-', $TokenKey);
        }

        if (!$DomainCode) {
            return new nusoap_fault('ERRTOKEN', '', 'Codice Ente/Organizzazione Mancante', '');
        }

        $restResult = self::wsRestCall('DestroyItaEngineContextToken', array(
                'TokenKey' => $TokenKey,
                'DomainCode' => $DomainCode
        ));

        if ($restResult instanceof nusoap_fault) {
            return $restResult;
        }

        return json_decode($restResult, true);
    }

    private static function wsRestCall($method, $data) {
        if (defined('ITA_WSREST_URI') && ITA_WSREST_URI) {
            $url = ITA_WSREST_URI;
        } else {
            $parts = explode('/ws', $_SERVER['REQUEST_URI']);
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https' : 'http';
            $url = $protocol . '://' . $_SERVER['HTTP_HOST'] . $parts[0] . '/wsrest/service.php';
        }

        $itaRestClient = new itaRestClient;
        $itaRestClient->setTimeout(10);
        $itaRestClient->setCurlopt_url($url . '/login/');
        $restCall = $itaRestClient->get($method, $data);
        $wsResult = $itaRestClient->getResult();

        if ($restCall) {
            if ($itaRestClient->getHttpStatus() !== 200) {
                return new nusoap_fault('ERRTOKEN', '', "Chiamata non riuscita (" . $itaRestClient->getHttpStatus() . ")\n$wsResult\n$method", '');
            }
        } else {
            return new nusoap_fault('ERRTOKEN', '', 'ws: ' . $itaRestClient->getErrMessage(), '');
        }

        return $wsResult;
    }

}

function GetItaEngineContextToken($UserName, $UserPassword, $DomainCode) {
    return wsSportello::GetItaEngineContextToken($UserName, $UserPassword, $DomainCode);
}

function GetItaEngineContextTokenInfo($TokenKey, $DomainCode) {
    return wsSportello::GetItaEngineContextTokenInfo($TokenKey, $DomainCode);
}

function CheckItaEngineContextToken($TokenKey, $DomainCode) {
    return wsSportello::CheckItaEngineContextToken($TokenKey, $DomainCode);
}

function DestroyItaEngineContextToken($TokenKey, $DomainCode) {
    return wsSportello::DestroyItaEngineContextToken($TokenKey, $DomainCode);
}
