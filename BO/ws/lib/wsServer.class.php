<?php

//
// Include generici
//
error_reporting(E_ERROR);
require_once('../Config.inc.php');
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

class wsServer extends soap_server {

    private static $server = null;

    public static function load() {
        require_once('../lib/wsModel.class.php');  
/*
 * Test Caricamento centralizzato
 * 
 */        
        require_once(ITA_LIB_PATH . '/itaPHPCore/App.class.php');
        App::load(true);
        App::$clientEngine = 'cli';        
    }

    public static function login($TokenKey, $DomainCode = '') {
        if ($TokenKey == '') {
            return new nusoap_fault('ERRTOKEN', '', "Token Mancante", '');
        }

        if (!$DomainCode) {
            return new nusoap_fault('ERRTOKEN', '', "Codice Ente/Organizzazione Mancante", '');
        }

        $itaToken = new ItaToken($DomainCode);
        $itaToken->setTokenKey($TokenKey);
        $ret_token = $itaToken->checkToken();
        if ($ret_token['status'] !== '0') {
            return new nusoap_fault('ERRTOKEN' . $ret_token['status'], '', $ret_token['messaggio'], '');
        }
        $tokenInfo = $itaToken->getInfo();
        App::$utente->setStato(Utente::AUTENTICATO);
        App::$utente->setKey('ditta', $DomainCode);
        App::$utente->setKey('TOKEN', $TokenKey);
        App::$utente->setKey('nomeUtente', $tokenInfo['TOKLOGNAME']);
        App::$utente->setKey('idUtente', $tokenInfo['TOKUTE']);
        App::$utente->setKey('TOKCOD', $tokenInfo['TOKCOD']);
        App::$utente->setKey('tipoAccesso', 'validate');
        App::$utente->setKey('referrerAccesso', "");
        App::$utente->setKey('DataLavoro', date('Ymd'));
        App::$utente->setKey('lingua', 'it');
        /*
         * Execute Post Login con test static methos exists
         */
        //Aggiunto post login per caricare il context da servizio 
        if (method_exists('itaHooks', 'execute')) {
            itaHooks::execute('post_login');
        }
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
    public static function getWsServerInstance($wsName, $namespace, $soapaction = false, $style = false, $use = false) {
        try {

            if (self::$server == null) {
                self::$server = new wsServer();
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
                    ), array(
                    'UserName' => 'xsd:string',
                    'UserCode' => 'xsd:string'
                    ), $ns, $soapaction, $style, $use
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
        } catch (Exception $exc) {
            return false;
        }
    }

    public static function GetItaEngineContextToken($UserName, $UserPassword, $DomainCode) {

        if (!$UserName) {
            return "Errore";
        }

        $ret_verpass = ita_verpass($DomainCode, $UserName, $UserPassword);


        if (!$ret_verpass) {
            return new nusoap_fault('ERRLOGINGEN', '', 'Autenticazione Annullata', '');
        }

        if ($ret_verpass['status'] != 0 && $ret_verpass['status'] != '-99') {
            return new nusoap_fault('ERRLOGIN' . $ret_verpass['status'], '', $ret_verpass['messaggio'], '');
        }
        if ($ret_verpass['status'] == '-99') {
            return new nusoap_fault('ERRLOGIN' . $ret_verpass['status'], '', "Errore generale", '');
        }

        $cod_ute = $ret_verpass['codiceUtente'];

        $itaToken = new ItaToken($DomainCode);

        $ret_token = $itaToken->createToken($cod_ute);
        if ($ret_token['status'] == '0') {
            return $itaToken->getTokenKey();
        } else {
            return new nusoap_fault('ERRTOKEN' . $ret_token['status'], '', $ret_token['messaggio'], '');
        }
    }

    public static function GetItaEngineContextTokenInfo($TokenKey, $DomainCode = '') {
        $resultArray = array(
            'UserName' => '',
            'UserCode' => ''
        );

        if (!$TokenKey) {
            return $resultArray;
        }

        $itaToken = new ItaToken($DomainCode);
        $itaToken->setTokenKey($TokenKey);

        $tokenInfo = $itaToken->getInfo();
        $tokenCheck = $itaToken->checkToken();

        $resultArray['UserName'] = $tokenInfo['TOKLOGNAME'];
        $resultArray['UserCode'] = $tokenInfo['TOKUTE'];

        if ($tokenCheck['status'] !== '0') {
            return new nusoap_fault('ERRTOKEN' . $tokenCheck['status'], '', $tokenCheck['messaggio'], '');
        }

        return $resultArray;
    }

    public static function CheckItaEngineContextToken($TokenKey, $DomainCode = '') {
        if (!$TokenKey) {
            return new nusoap_fault('ERRTOKEN', '', "Token Mancante", '');
        }
        //estrazione DomainCode dal token
        if ($DomainCode == '') {
            list($token, $DomainCode) = explode("-", $TokenKey);
        }
        if (!$DomainCode) {
            return new nusoap_fault('ERRTOKEN', '', "Codice Ente/Organizzazione Mancante", '');
        }

        $itaToken = new ItaToken($DomainCode);
        $itaToken->setTokenKey($TokenKey);
        $ret_token = $itaToken->checkToken();
        if ($ret_token['status'] == '0') {
            $utenti_rec = $itaToken->getUtentiRec();
            //@TODO: STANDARDIZZARE
            App::$utente->setKey('ditta', $DomainCode);
            App::$utente->setKey('TOKEN', $TokenKey);
            App::$utente->setKey('nomeUtente', $utenti_rec['UTELOG']);
            App::$utente->setKey('idUtente', $utenti_rec['UTECOD']);
            return "Valid";
        } else {
            return new nusoap_fault('ERRTOKEN' . $ret_token['status'], '', $ret_token['messaggio'], '');
        }
    }

    public static function DestroyItaEngineContextToken($TokenKey, $DomainCode = '') {
        if (!$TokenKey) {
            return new nusoap_fault('ERRTOKEN', '', "Token Mancante", '');
        }
        //estrazione DomainCode dal token
        if ($DomainCode == '') {
            list($token, $DomainCode) = explode("-", $TokenKey);
        }

        if (!$DomainCode) {
            return new nusoap_fault('ERRTOKEN', '', "Codice Ente/Organizzazione Mancante", '');
        }

        $itaToken = new ItaToken($DomainCode);
        $itaToken->setTokenKey($TokenKey);
        $ret_token = $itaToken->destroyToken();
        if ($ret_token['status'] == '0') {
            return "Success";
        } else {
            return new nusoap_fault('ERRTOKEN' . $ret_token['status'], '', $ret_token['messaggio'], '');
        }
    }
}

function GetItaEngineContextToken($UserName, $UserPassword, $DomainCode) {
    return wsServer::GetItaEngineContextToken($UserName, $UserPassword, $DomainCode);
}

function GetItaEngineContextTokenInfo($TokenKey, $DomainCode) {
    return wsServer::GetItaEngineContextTokenInfo($TokenKey, $DomainCode);
}

function CheckItaEngineContextToken($TokenKey, $DomainCode) {
    return wsServer::CheckItaEngineContextToken($TokenKey, $DomainCode);
}

function DestroyItaEngineContextToken($TokenKey, $DomainCode) {
    return wsServer::DestroyItaEngineContextToken($TokenKey, $DomainCode);
}

?>