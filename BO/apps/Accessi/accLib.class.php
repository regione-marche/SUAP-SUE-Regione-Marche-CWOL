<?php

class accLib {

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    public $DBPARA_DB;
    public $ITW_DB;
    public $ITALWEB;
    private $errCode;
    private $errMessage;

    const RICHIESTA_NUOVA = '01';
    const RICHIESTA_RESET_PASSWORD = 'E1';
    const RICHIESTA_EVASA = '02';

    public function setDBPARA($DBPARA_DB) {
        $this->DBPARA_DB = $DBPARA_DB;
    }

    public function setITW($ITW_DB) {
        $this->ITW_DB = $ITW_DB;
    }

    public function setITALWEB($ITALWEB) {
        $this->ITALWEB = $ITALWEB;
    }

    public function getDBPARA() {
        if (!$this->DBPARA_DB) {
            try {
                $this->DBPARA_DB = ItaDB::DBOpen('DBPARA', false);
            } catch (Exception $e) {
                App::log($e->getMessage());
            }
        }
        return $this->DBPARA_DB;
    }

    public function getITW() {
        if (!$this->ITW_DB) {
            try {
                $this->ITW_DB = ItaDB::DBOpen('ITW');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITW_DB;
    }

    public function getITALWEB() {
        if (!$this->ITALWEB) {
            try {
                $this->ITALWEB = ItaDB::DBOpen('ITALWEB');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALWEB;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    function __construct() {
        
    }

    public function GetTipo() {
        $tipo = Array('01 - ACCESSO', '02 - DBOPEN', '03 - DBCLOSE'
            , '04 - DBPUT', '05 - DBUPDATE (modo 1)', '06 - DBUPDATE (modo 2)');
        return $tipo;
    }

    public function GetOperaz($rowid) {
        $sql = "SELECT * FROM OPERAZ WHERE ROWID='$rowid'";
        $Operaz_rec = ItaDB::DBSQLSelect($this->DBPARA_DB, $sql, false);
        return $Operaz_rec;
    }

    public function GetUtenti($codice, $tipo = 'codice',$multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM UTENTI WHERE UTECOD=$codice";
        } else if ($tipo == 'utelog') {
            $sql = "SELECT * FROM UTENTI WHERE UTELOG='" . addslashes($codice) . "'";
        } else if ($tipo == 'uteana3') {
            $sql = "SELECT * FROM UTENTI WHERE UTEANA__3='$codice'";
        } else if ($tipo == 'uteana1') {
            $sql = "SELECT * FROM UTENTI WHERE UTEANA__1='$codice'";
        } else if ($tipo == 'codicefiscale') {
            $sql = "SELECT * FROM UTENTI WHERE UTEFIS='$codice'";
        } else if ($tipo == 'tutti') {
            $sql = "SELECT * FROM UTENTI";
        }
        else {
            $sql = "SELECT * FROM UTENTI WHERE ROWID=$codice";
        }
        $utenti_rec = ItaDB::DBSQLSelect($this->getITW(), $sql, $multi);
        return $utenti_rec;
    }

    public function GetRichut($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM RICHUT WHERE RICCOD=$codice";
        } else {
            $sql = "SELECT * FROM RICHUT WHERE ROWID=$codice";
        }
        $richut_rec = ItaDB::DBSQLSelect($this->getITW(), $sql, false);
        return $richut_rec;
    }

    public function GetGruppi($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM GRUPPI WHERE GRUCOD=$codice";
        } elseif ($tipo == 'desc') {
            $sql = "SELECT * FROM GRUPPI WHERE " . $this->getITW()->strUpper('GRUDES') . " = " . $this->getITW()->strUpper("'" . addslashes($codice) . "'");
        } else {
            $sql = "SELECT * FROM GRUPPI WHERE ROWID=$codice";
        }
        $gruppi_rec = ItaDB::DBSQLSelect($this->getITW(), $sql, false);
        return $gruppi_rec;
    }

    public function GetEnv_Utemeta($codice, $tipo = 'codice', $tipoMeta = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ENV_UTEMETA WHERE UTECOD=$codice";
        } else {
            $sql = "SELECT * FROM ENV_UTEMETA WHERE ROWID=$codice";
        }
        if ($tipoMeta != '') {
            $sql .= " AND METAKEY = '" . $tipoMeta . "'";
        }
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, false);
    }

    public function GetMetadati($codice, $tipo = 'codice', $tipoMeta = '') {
        $env_utemeta_rec = $this->GetEnv_Utemeta($codice, $tipo, $tipoMeta);
        $metavalue = unserialize($env_utemeta_rec['METAVALUE']);

        return $metavalue;
    }

    public function GetParmfascicolo($codice, $tipo = 'codice', $tipoMeta = 'ParmFascicolo') {
        $Metadati = $this->GetMetadati($codice, $tipo, $tipoMeta);
        return $Metadati;
    }

    public function GetFirmaRemota($codice, $tipo = 'codice', $tipoMeta = 'ParmFirmaRemota') {
        $Metadati = $this->GetMetadati($codice, $tipo, $tipoMeta);
        return $Metadati['FirmaRemota'];
    }

    public function GetOperatorePaleo($codice, $tipo = 'codice', $tipoMeta = 'ParmPaleo') {
        $Metadati = $this->GetMetadati($codice, $tipo);
        return $Metadati['OperatorePaleo'];
    }

    public function GetDatiInfor($codice, $tipo = 'codice', $tipoMeta = 'ParmInfor') {
        $Metadati = $this->GetMetadati($codice, $tipo, $tipoMeta);
        return $Metadati['DatiInfor'];
    }

    public function GetUtenteProtRemoto($codice, $tipo = 'codice', $tipoMeta = 'ParmProtRemoto') {
        $Metadati = $this->GetMetadati($codice, $tipo, $tipoMeta);
        return $Metadati['ProtRemoto']['User'];
    }

    public function CheckParametriMail() {
        $utenti_rec = $this->GetUtenti(App::$utente->getKey('nomeUtente'), 'utelog');
        $richut_rec = $this->GetRichut($utenti_rec['UTECOD']);
        if ($richut_rec['RICMAI'] == "" || $richut_rec['RICCOG'] == "" || $richut_rec['RICNOM'] == "" || $richut_rec['RICHSM'] == "" || $richut_rec['RICUSM'] == "" || $richut_rec['RICPWM'] == "") {
            return false;
        }
        return true;
    }

    public function GetSegrAbilitati($codice, $tipo = 'codice', $tipoMeta = 'SEGR_ABILITATI') {
        $env_utemeta_rec = $this->GetEnv_Utemeta($codice, $tipo, $tipoMeta);
        return $env_utemeta_rec['METAVALUE'];
    }

    public function GetSegrVisibilita($codice, $tipo = 'codice', $tipoMeta = 'VIS_SEGRETERIA') {
        $env_utemeta_rec = $this->GetEnv_Utemeta($codice, $tipo, $tipoMeta);
        return $env_utemeta_rec['METAVALUE'];
    }

    public function GetLivelloAnagrafe($codice, $tipo = 'codice', $tipoMeta = 'LIVELLO_ANAGRAFE') {
        $env_utemeta_rec = $this->GetEnv_Utemeta($codice, $tipo, $tipoMeta);
        return $env_utemeta_rec['METAVALUE'];
    }

    public function GetFascicoloAbilitati($codice, $tipo = 'codice', $tipoMeta = 'FASCICOLO_ABILITATI') {
        $env_utemeta_rec = $this->GetEnv_Utemeta($codice, $tipo, $tipoMeta);
        return $env_utemeta_rec['METAVALUE'];
    }

    public function GetFascicoloVisibilita($codice, $tipo = 'codice', $tipoMeta = 'VIS_FASCICOLO') {
        $env_utemeta_rec = $this->GetEnv_Utemeta($codice, $tipo, $tipoMeta);
        return $env_utemeta_rec['METAVALUE'];
    }

    public function GetProtocolloOggRis($codice, $tipo = 'codice', $tipoMeta = 'PROTO_OGGRIS') {
        $env_utemeta_rec = $this->GetEnv_Utemeta($codice, $tipo, $tipoMeta);
        return $env_utemeta_rec['METAVALUE'];
    }

    public function GetProtocolloEticUte($codice, $tipo = 'codice', $tipoMeta = 'PROTO_ETICUTE') {
        $env_utemeta_rec = $this->GetEnv_Utemeta($codice, $tipo, $tipoMeta);
        return $env_utemeta_rec['METAVALUE'];
    }

    public function GetParamentriMail($uteCod = '') {
        if (!$uteCod) {
            $utenti_rec = $this->GetUtenti(App::$utente->getKey('nomeUtente'), 'utelog');
            $uteCod = $utenti_rec['UTECOD'];
        }

        $richut_rec = $this->GetRichut($uteCod);
        $parametri = array(
            'FROM' => $richut_rec['RICMAI'],
            'NAME' => $richut_rec['RICFROM'],
            'HOST' => $richut_rec['RICHSM'],
            'USERNAME' => $richut_rec['RICUSM'],
            'PASSWORD' => $richut_rec['RICPWM'],
            'PORT' => $richut_rec['RICPRT'],
            'SMTPSECURE' => $richut_rec['RICSMT']
        );
        return $parametri;
    }

    public function SetGruppoUtente($LogName, $Gruppo) {
        $this->ITW_DB = $this->getITW(); // inutile perchè usa GetUtenti e viene già valorizzato?
        $Utente_tab = $this->GetUtenti($LogName, 'utelog');
        $PrimoGruppoLibero = '';
        for ($i = 1; $i <= 9; $i++) {
            if ($Utente_tab['UTEGEX__' . $i] == $Gruppo) {
                $PrimoGruppoLibero = '';
                break;
            }
            if (!$Utente_tab['UTEGEX__' . $i] && $PrimoGruppoLibero == '') {
                $PrimoGruppoLibero = 'UTEGEX__' . $i;
            }
        }
        //Se non trovo PrimoGruppoLibero
        if ($PrimoGruppoLibero != '') {
            $Utente_tab[$PrimoGruppoLibero] = $Gruppo;
            //Aggiorno Tabella Utenti
            try {
                ItaDB::DBUpdate($this->ITW_DB, 'UTENTI', 'ROWID', $Utente_tab);
            } catch (Exception $exc) {
                Out::msgStop("Errore", "Errore in Aggiornamento UTENTI." . $exc->getMessage());
                return false;
            }
        }
        if ($PrimoGruppoLibero == '' && $i == 9) {
            Out::msgStop("Attenzione", "Numero massimo gruppi raggiungo, non è stato possibile assegnare il gruppo $Gruppo .");
            return false;
        }

        return true;
    }

    public function getCodiceGruppo($grudes) {
        $gruppi_rec = $this->GetGruppi($grudes, 'desc');
        return $gruppi_rec['GRUCOD'];
    }

    public function utentePresenteInGruppo($utecod, $grucod) {
        if (!$utecod || !$grucod) {
            return false;
        }

        $utenti_rec = $this->GetUtenti($utecod);

        if (!$utenti_rec) {
            return false;
        }

        if ($utenti_rec['UTEGRU'] == $grucod) {
            return true;
        }

        for ($i = 1; $i <= 9; $i++) {
            if ($utenti_rec['UTEGEX__' . $i] == $grucod) {
                return true;
            }
        }

        return false;
    }

    public function SqlRichiesteUtenti() {
        $sql = "SELECT
                    RICHUT.*, UTENTI.UTELOG
                FROM
                    RICHUT
                LEFT OUTER JOIN
                    UTENTI
                ON
                    RICHUT.RICCOD = UTENTI.UTECOD
                WHERE
                    ( RICSTA = '" . accLib::RICHIESTA_NUOVA . "' )
                OR
                    ( RICSTA = '" . accLib::RICHIESTA_EVASA . "' AND RICTIP = '1' )
                OR
                    ( RICSTA = '" . accLib::RICHIESTA_RESET_PASSWORD . "' )";

        return $sql;
    }

    public function inviaMailRichiestaUtente($richut_rec, $ente) {
        include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
        include_once (ITA_LIB_PATH . '/itaPHPCore/itaMailer.class.php');

        $this->setITALWEB(ItaDB::DBOpen('ITALWEB', $ente));
        $this->setITW(ItaDB::DBOpen('ITW', $ente));

        /* @var $devLib devLib */
        $devLib = new devLib();
        $devLib->setITALWEB($this->getITALWEB());

        /* @var $emlLib emlLib */
        $emlLib = new emlLib();
        $emlLib->setITALWEB($this->getITALWEB());

        $env_config_rec = $devLib->getEnv_config('ITAENGINE_EMAIL', 'codice', 'ACCOUNT', false);
        $mailSender = $env_config_rec['CONFIG'];
        $env_config_rec = $devLib->getEnv_config('ITAENGINE_EMAIL', 'codice', 'ADDRESS', false);
        $mailReceiver = $env_config_rec['CONFIG'];

        if (!$mailSender || !$mailReceiver) {
            return false;
        }

        $mailAccount = $emlLib->getMailAccount($mailSender);

        if (!$mailAccount) {
            return false;
        }

        $parametri = array();
        $parametri['FROM'] = $mailSender;
        $parametri['NAME'] = $mailAccount['NAME'];
        $parametri['HOST'] = $mailAccount['SMTPHOST'];
        $parametri['USERNAME'] = $mailAccount['USER'];
        $parametri['PASSWORD'] = $mailAccount['PASSWORD'];
        $parametri['PORT'] = $mailAccount['SMTPPORT'];
        $parametri['SMTPSECURE'] = $mailAccount['SMTPSECURE'];
        $parametri['CUSTOMHEADERS_ARRAY'] = $mailAccount['CUSTOMHEADERS'];

        $itaMailer = new itaMailer();
        $itaMailer->FromName = $mailSender;
        $itaMailer->From = $mailSender;
        $itaMailer->Subject = '[itaEngine] Notifica Richiesta Utente';
        $customHeaders = (array) $parametri['CUSTOMHEADERS_ARRAY'];
        if ($customHeaders) {
            foreach ($customHeaders as $key => $customHeader) {
                $itaMailer->addCustomHeader($key . ": " . $customHeader);
            }
        }
        $itaMailer->IsHTML();

        $body = '';
        switch ($richut_rec['RICSTA']) {
            case accLib::RICHIESTA_NUOVA:
                $body = "E' stata richiesta l'attivazione di un nuovo account.<br/>";
                $body .= "Controllare nella gestione delle richieste.<br/>";
                $body .= "<b>Dati utente</b><br/>";
                break;
            case accLib::RICHIESTA_RESET_PASSWORD:
                $body = "E' stato richiesto il reset della password.<br/>";
                $body .= "Controllare nella gestione delle richieste.<br/>";

                $utenti_rec = $this->GetUtenti($richut_rec['RICCOD']);
                $body .= "<b>Dati utente</b><br/>";
                $body .= "Logname: " . $utenti_rec['UTELOG'] . "<br/>";
                break;
        }
        $body .= "Nome: " . $richut_rec['RICNOM'] . "<br/>";
        $body .= "Cognome: " . $richut_rec['RICCOG'] . "<br/>";
        $body .= "Mail: " . $richut_rec['RICMAI'];
        $itaMailer->Body = $body;

        $itaMailer->itaAddAddress($mailReceiver);

        if (!$itaMailer->Send($parametri)) {
            Out::msgStop('Errore Mail', $itaMailer->ErrorInfo, "auto", "auto", "");
        }
    }

    public function updateUserLastAccess($ITW, $utenti_rec, $ditta = null) {
        $ret = array();

        $utenti_rec['UTEDATAULUSO'] = date('Ymd');

        try {
            ItaDB::DBUpdate($ITW, 'UTENTI', 'ROWID', $utenti_rec);
        } catch (Exception $e) {
            $ret['status'] = -8;
            $ret['messaggio'] = 'Errore aggiornamento ultimo accesso: ' . $e->getMessage();
            $ret['codiceUtente'] = $utenti_rec['UTECOD'];
            return $ret;
        }

        if ($this->isSSOCitywareEnabled()) {
            include_once ITA_BASE_PATH . '/apps/Accessi/accLibCityWare.class.php';
            $accLibCityWare = new accLibCityWare($ditta);

            if (!$accLibCityWare->updateUserLastAccess($utenti_rec['UTELOG'], date('Y-m-d'))) {
                $ret['status'] = -8;
                $ret['messaggio'] = "Aggiornamento ultimo accesso CityWare: " . $accLibCityWare->getErrMessage();
                $ret['codiceUtente'] = $utenti_rec['UTECOD'];
                return $ret;
            }
        }

        return true;
    }

    public function getTokenFromCF($codiceFiscale, $codiceEnte, $createUser = false) {
        $return = array(
            'status' => 0,
            'token' => '',
            'messaggio' => ''
        );

        $this->setITW(ItaDB::DBOpen('ITW', $codiceEnte));

        $utenti_rec = $this->GetUtenti($codiceFiscale, 'codicefiscale');

        if (!$utenti_rec) {
            if (!$createUser) {
                $return['status'] = -1;
                $return['messaggio'] = 'Utente non trovato';
                return $return;
            }

            /*
             * @TODO centralizzare registrazione (automatica) utente?
             */

            $utenti_rec = ItaDB::DBSQLSelect($this->getITW(), "SELECT MAX(UTECOD) AS ULTIMO FROM UTENTI", false);
            $codiceUtente = $utenti_rec ? $utenti_rec['ULTIMO'] + 1 : 1;

            ItaDB::DBInsert($this->getITW(), 'UTENTI', 'ROWID', array(
                'UTECOD' => $codiceUtente,
                'UTELOG' => $codiceFiscale,
                'UTEFIS' => $codiceFiscale,
                'UTEUPA' => date('Ymd'),
                'UTEDPA' => '9999',
                'UTESPA' => date('Ymd'),
                'UTEFIL__1' => 20,
                'UTEFIL__2' => 30
            ));
        } else {
            $codiceUtente = $utenti_rec['UTECOD'];
        }

        $ret_token = ita_token('', $codiceEnte, $codiceUtente, 1);

        if ($ret_token['status'] != '0') {
            $return['status'] = -2;
            $return['messaggio'] = $ret_token['messaggio'];
            return $return;
        }

        $return['token'] = $ret_token['token'];
        return $return;
    }

    public function getTokenListFromUsername($username, $codiceEnte) {
        $utenti_rec = ItaDB::DBSQLSelect(ItaDB::DBOpen('ITW', $codiceEnte), "SELECT UTELOG, UTECOD FROM UTENTI WHERE UTELOG = '" . addslashes($username) . "'", false);
        if (!$utenti_rec) {
            return false;
        }
        $codiceUtente = $utenti_rec['UTECOD'];
        $sqlT = "SELECT * FROM TOKEN WHERE TOKUTE = '$codiceUtente'";
        $tokenList = ItaDB::DBSQLSelect(ItaDB::DBOpen('ITW', $codiceEnte), $sqlT, true);
        return $tokenList;
    }

    public function spLogin($codiceFiscale, $codiceEnte) {
        $ITW = ItaDB::DBOpen('ITW', $codiceEnte);

        $utenti_rec = ItaDB::DBSQLSelect($ITW, "SELECT * FROM UTENTI WHERE UTENTI.UTEFIS = '$codiceFiscale'", false);
        if (!$utenti_rec) {
            throw new Exception('Utente non trovato');
        }

        $codiceUtente = $utenti_rec['UTECOD'];
        $utente = $utenti_rec['UTELOG'];
        $ret_token = ita_token('', $codiceEnte, $codiceUtente, 1);

        if ($ret_token['status'] == '0') {
            $token = $ret_token['token'];
        } else {
            throw new Exception('Errore di apertura sessione: ' . $ret_token['messaggio']);
        }

        if (App::$tmpToken) {
            App::clearSessionCookie();
            App::$tmpToken = null;
            Out::codice('tmpToken = null;');
        }

        $_POST['TOKEN'] = $token;
        $_SESSION = array();
        App::startSession('S-' . $token, true);
        $_SESSION['utente'] = new Utente();
        App::$utente = $_SESSION['utente'];
        App::$utente->setStato(Utente::AUTENTICATO);
        App::$utente->setKey('ditta', $codiceEnte);
        App::$utente->setKey('TOKEN', $token);
        App::$utente->setKey('nomeUtente', $utente);
        App::$utente->setKey('idUtente', $codiceUtente);
        App::$utente->setKey('TOKCOD', ita_token($token, $codiceEnte, 0, 20));
        App::$utente->setKey('tipoAccesso', 'validate');
        App::$utente->setKey('referrerAccesso', '');
        App::$utente->setKey('DataLavoro', date('Ymd'));
        App::$utente->setKey('lingua', 'it');

        $this->updateUserLastAccess($ITW, $utenti_rec);

        App::createPrivPath();
        itaLib::createAppsTempPath();
        Out::closeDialog('accLogin');

        Out::codice('token="' . $token . '";');

        App::openDesktop();

        if (method_exists('itaHooks', 'execute')) {
            itaHooks::execute('post_login');
        }

        App::autoExec();

        return true;
    }

    /**
     * Ritorna la path per le risorse in apps/Accessi/resources.custom o
     * apps/Accessi/resource se non presenti in custom.
     * 
     * @param string $name Nome file della risorsa.
     * @return string Path completa alla risorsa.
     */
    public function getResourcePath($name) {
        if (file_exists(ITA_BASE_PATH . '/apps/Accessi/resources.custom/' . $name)) {
            return ITA_BASE_PATH . '/apps/Accessi/resources.custom/' . $name;
        } else {
            return ITA_BASE_PATH . '/apps/Accessi/resources/' . $name;
        }
    }

    public function setUserNameCase($utelog) {
        switch (strtoupper(App::getConf('security.insert-user-case'))) {
            case "UPPER":
                $caseUtelog = strtoupper(trim($utelog));
                break;
            case "LOWER":
                $caseUtelog = strtolower(trim($utelog));
                break;
            case "NONE":
            default:
                $caseUtelog = trim($utelog);
                break;
        }
        return $caseUtelog;
    }

    public function getEncryptedPassword($password) {
        $secureMethod = App::getConf('security.secure-password');

        switch ($secureMethod) {
            case 'eq' :
                $url = App::getConf('modelBackEnd.eq') . '/UX_WCRYP';
                $myPost['mode'] = 'encrypt';
                $myPost['inputData'] = $password;
                $fp = new Snoopy;
                $fp->submit($url, $myPost);
                $result = $fp->results;

                if ($result == '') {
                    $this->setErrCode(-1);
                    $this->setErrMessage('Errore di accesso a secure method eq.');
                    return false;
                }

                return $result;

            case 'md5' :
                return md5($password);

            case 'sha1' :
                return sha1($password);

            case 'sha256' :
                return hash('sha256', $password);

            default:
                return $password;
        }
    }

    public function isSSOCitywareEnabled() {
        return App::getConf('security.sso') === 'dbcityware';
    }

}
