<?php

include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaCrypt.class.php';

class accLibExternal {

    private $accLib;
    private $errCode;
    private $errMessage;

    /**
     * Tipologia autenticazione
     */
    const ACCESS_TYPE_CWOL = 'token';
    const ACCESS_TYPE_CITYPORTAL = 'cityportal';
    const ACCESS_TYPE_COHESION = 'cohesion';
    const ACCESS_TYPE_FEDERA = 'federa';
    const ACCESS_TYPE_MAGGIOLI_SPID = 'maggioli-spid';

    /**
     * Comportamento per nuovi utenti
     */
    const ACCESS_NEW_USER_REFUSE = 'refuse';
    const ACCESS_NEW_USER_INSERT = 'insert';

    /**
     * Tempo massimo di validità di una request (secondi)
     */
    const ACCESS_TIMEOUT = 30;

    public function __construct() {
        $this->accLib = new accLib;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function externalLogin($directPost) {
        $jsonParams = itaCrypt::decrypt($directPost['externaltoken']);
        $directParams = json_decode($jsonParams, true);

        if (!$jsonParams || !$directParams) {
            $this->errCode = -1;
            $this->errMessage = 'Errore in lettura parametri.';
            return false;
        }

        if (!$directParams['ts']) {
            $this->errCode = -1;
            $this->errMessage = 'Accesso non valido.';
            return false;
        }

        if ((time() - $directParams['ts'] ) > self::ACCESS_TIMEOUT) {
            $this->errCode = -1;
            $this->errMessage = 'Sessione scaduta.';
            return false;
        }

        if (!in_array($directParams['accesstype'], $this->getLoginTypes())) {
            $this->errCode = -1;
            $this->errMessage = 'Tipologia di login non valida.';
            return false;
        }

        $loginToken = $this->getLoginToken($directParams['accesstype'], $directParams['accessdata'], $directParams['accessorg'], $directParams['accessnew']);
        if ($loginToken === false) {
            return false;
        }

        $directParams['accesstoken'] = $loginToken;

        return array_merge($directPost, $directParams);
    }

    public function getLoginTypes() {
        $loginTypes = array();
        $rfc = new ReflectionClass('accLibExternal');
        foreach ($rfc->getConstants() as $key => $value) {
            if (strpos($key, 'ACCESS_TYPE') === 0) {
                $loginTypes[] = $value;
            }
        }

        return $loginTypes;
    }

    public function getLoginToken($loginType, $loginData, $codiceEnte, $newUser = self::ACCESS_NEW_USER_REFUSE) {
        $createUser = $newUser === self::ACCESS_NEW_USER_INSERT;

        switch ($loginType) {
            case self::ACCESS_TYPE_CWOL:
                return $loginData['token'];

            default:
                $result = $this->accLib->getTokenFromCF($loginData['codiceFiscale'], $codiceEnte, $createUser);
                if ($result['status'] != 0) {
                    $this->errCode = -1;
                    $this->errMessage = $result['messaggio'];
                    return false;
                }

                return $result['token'];
        }
    }

}
