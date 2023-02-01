<?php

require_once('RestController.class.php');
require_once(ITA_LIB_PATH . '/itaPHPCore/itaHooks.class.php');

class loginController extends RestController {

    public function GetItaEngineContextToken($params) {
        $this->resetLastError();
        if (!$params['UserName']) {
            $this->setLastErrorCode(-1);
            $this->setLastErrorDescription('Parametro UserName non presente');
            return false;
        }

        $ret_verpass = ita_verpass($params['DomainCode'], $params['UserName'], $params['Password']);
        if (!$ret_verpass) {
            $this->setLastErrorCode(-1);
            $this->setLastErrorDescription('Autenticazione annullata');
            return false;
        }

        if ($ret_verpass['status'] != 0 && $ret_verpass['status'] != '-99') {
            $this->setLastErrorCode(-1);
            $this->setLastErrorDescription($ret_verpass['status'] . ' - ' . $ret_verpass['messaggio']);
            return false;
        }
        if ($ret_verpass['status'] == '-99') {
            $this->setLastErrorCode(-1);
            $this->setLastErrorDescription($ret_verpass['status'] . ' - ' . $ret_verpass['messaggio'] . ' - Errore generale');
            return false;
        }

        $cod_ute = $ret_verpass['codiceUtente'];

        $itaToken = new ItaToken($params['DomainCode']);

        $ret_token = $itaToken->createToken($cod_ute);
        if ($ret_token['status'] == '0') {
            return $itaToken->getTokenKey();
        } else {
            $this->setLastErrorCode(-1);
            $this->setLastErrorDescription($ret_token['status'] . ' - ' . $ret_token['messaggio']);
            return false;
        }
    }

    public function CheckItaEngineContextToken($params) {
        $this->resetLastError();
        if (!$params['TokenKey']) {
            $this->setLastErrorCode(-1);
            $this->setLastErrorDescription('Parametro TokenKey non presente');
            return false;
        }
        //estrazione DomainCode dal token
        if (!array_key_exists('DomainCode', $params) || $params['DomainCode'] == '') {
            list($token, $DomainCode) = explode("-", $params['TokenKey']);
        }else{
            $DomainCode=$params['DomainCode'];
        }
        if (!$DomainCode) {
            $this->setLastErrorCode(-1);
            $this->setLastErrorDescription('Codice Ente/Organizzazione Mancante');
            return false;
        }

        $itaToken = new ItaToken($DomainCode);
        $itaToken->setTokenKey($params['TokenKey']);
        $ret_token = $itaToken->checkToken();
        if ($ret_token['status'] == '0') {
            $utenti_rec = $itaToken->getUtentiRec();
            //@TODO: STANDARDIZZARE
            App::$utente->setKey('ditta', $DomainCode);
            App::$utente->setKey('TOKEN', $params['TokenKey']);
            App::$utente->setKey('nomeUtente', $utenti_rec['UTELOG']);
            App::$utente->setKey('idUtente', $utenti_rec['UTECOD']);

            return "Valid";
        } else {
            $this->setLastErrorCode(-1);
            $this->setLastErrorDescription($ret_token['status'] . ' - ' . $ret_token['messaggio']);
            return false;
        }
    }

    public function GetItaEngineContextTokenInfo($params) {
        $this->resetLastError();

        $resultArray = array(
            'UserName' => '',
            'UserCode' => ''
        );

        if (!$params['TokenKey']) {
            $resultArray['return'] = 'Valid';
            return $resultArray;
        }

        /*
         * Estrazione DomainCode dal token
         */

        if (!array_key_exists('DomainCode', $params) || $params['DomainCode'] == '') {
            list($token, $DomainCode) = explode('-', $params['TokenKey']);
        } else {
            $DomainCode = $params['DomainCode'];
        }
        
        if (!$DomainCode) {
            $resultArray['return'] = 'Codice Ente/Organizzazione Mancante';
            return $resultArray;
        }

        $itaToken = new ItaToken($DomainCode);
        $itaToken->setTokenKey($params['TokenKey']);

        $tokenInfo = $itaToken->getInfo();
        $tokenCheck = $itaToken->checkToken();

        $resultArray['UserName'] = $tokenInfo['TOKLOGNAME'];
        $resultArray['UserCode'] = $tokenInfo['TOKUTE'];

        if ($tokenCheck['status'] == '0') {
            $resultArray['return'] = 'Valid';
        } else {
            $resultArray['return'] = 'Errore: ' . $tokenCheck['messaggio'];
        }

        return $resultArray;
    }

    public function DestroyItaEngineContextToken($params) {
        $this->resetLastError();
        if (!$params['TokenKey']) {
            $this->setLastErrorCode(-1);
            $this->setLastErrorDescription('Parametro TokenKey non presente');
            return false;
        }
        //estrazione DomainCode dal token
        if (!array_key_exists('DomainCode', $params) || $params['DomainCode'] == '') {
            list($token, $DomainCode) = explode("-", $params['TokenKey']);
        }else{
            $DomainCode = $params['DomainCode'];
        }
        if (!$DomainCode) {
            $this->setLastErrorCode(-1);
            $this->setLastErrorDescription('Codice Ente/Organizzazione Mancante');
            return false;
        }

        $itaToken = new ItaToken($DomainCode);
        $itaToken->setTokenKey($params['TokenKey']);
        $ret_token = $itaToken->destroyToken();
        if ($ret_token['status'] == '0') {
            return "Success";
        } else {
            $this->setLastErrorCode(-1);
            $this->setLastErrorDescription($ret_token['status'] . ' - ' . $ret_token['messaggio']);
            return false;
        }
    }

    public function login($params) {
        $this->resetLastError();
        if (!$params['TokenKey']) {
            $this->setLastErrorCode(-1);
            $this->setLastErrorDescription('Parametro TokenKey non presente');
            return false;
        }
        //estrazione DomainCode dal token
        if (!array_key_exists('DomainCode', $params) || $params['DomainCode'] == '') {
            list($token, $DomainCode) = explode("-", $params['TokenKey']);
        }
        if (!$DomainCode) {
            $this->setLastErrorCode(-1);
            $this->setLastErrorDescription('Codice Ente/Organizzazione Mancante');
            return false;
        }

        $itaToken = new ItaToken($DomainCode);
        $itaToken->setTokenKey($params['TokenKey']);
        $ret_token = $itaToken->checkToken();
        if ($ret_token['status'] !== '0') {
            $this->setLastErrorCode(-1);
            $this->setLastErrorDescription($ret_token['status'] . ' - ' . $ret_token['messaggio']);
            return false;
        }

        $tokenInfo = $itaToken->getInfo();
        App::$utente->setStato(Utente::AUTENTICATO);
        App::$utente->setKey('ditta', $DomainCode);
        App::$utente->setKey('TOKEN', $params['TokenKey']);
        App::$utente->setKey('nomeUtente', $tokenInfo['TOKLOGNAME']);
        App::$utente->setKey('idUtente', $tokenInfo['TOKUTE']);
        App::$utente->setKey('TOKCOD', $tokenInfo['TOKCOD']);
        App::$utente->setKey('tipoAccesso', 'validate');
        App::$utente->setKey('referrerAccesso', "");
        App::$utente->setKey('DataLavoro', date('Ymd'));
        App::$utente->setKey('lingua', 'it');

        //Aggiunto post login per caricare il context da servizio 
        if (method_exists('itaHooks', 'execute')) {
            itaHooks::execute('post_login');
        }
        
        return true;
    }

}

?>