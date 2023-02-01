<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function ita_verpass($ditta, $utente, $password) {
    $ret = array();
    try {
        $ITW_DB = ItaDB::DBOpen('ITW', $ditta);
        $ITALWEB_DB = ItaDB::DBOpen('ITALWEB', $ditta);
    } catch (Exception $e) {
//Out::msgStop("Errore", $e->getMessage());
        $ret['status'] = -1;
        $ret['messaggio'] = "Errore Apertura DB Sicurezza " . $e->getMessage();
        $ret['codiceUtente'] = 0;
        return $ret;
    }

    $sso = App::getConf('security.sso');
    $isActiveLDAP = App::getConf('security.ldap');

    if ($sso === 'dbcityware') {
        $sql = "SELECT * FROM UTENTI WHERE " . $ITW_DB->strupper('UTELOG') . "='" . addslashes(strtoupper($utente)) . "'";
    } else {
        $sql = "SELECT * FROM UTENTI WHERE UTELOG='" . addslashes($utente) . "'";
    }
    $Utenti_rec = ItaDB::DBSQLSelect($ITW_DB, $sql, false);

    if (!$Utenti_rec) {
        if ($isActiveLDAP) {
            /*
             * Cerco per UTELDAP
             */
            $sql = "SELECT * FROM UTENTI WHERE " . $ITW_DB->strupper('UTELDAP') . " = " . $ITW_DB->strupper("'$utente'") . "";
            $Utenti_rec = ItaDB::DBSQLSelect($ITW_DB, $sql, false);
        }

        if (!$Utenti_rec) {
            $ret['status'] = -2;
            $ret['messaggio'] = "Utente non valido";
            $ret['codiceUtente'] = 0;
            return $ret;
        }
    }

    if (strtoupper($Utenti_rec['UTELOG']) !== strtoupper($utente)) {
        if ($isActiveLDAP) {
            /*
             * Check per UTELDAP
             */
            if (strtoupper($Utenti_rec['UTELDAP']) !== strtoupper($utente)) {
                $ret['status'] = -2;
                $ret['messaggio'] = "Utente non valido";
                $ret['codiceUtente'] = 0;
                return $ret;
            }
        } else {
            $ret['status'] = -2;
            $ret['messaggio'] = "Utente non valido";
            $ret['codiceUtente'] = 0;
            return $ret;
        }
    }

    $utente = $Utenti_rec['UTELOG'];

    /*
     * Controllo data validità
     */
    if ($Utenti_rec['DATAINIZ'] != '' && $Utenti_rec['DATAINIZ'] > date('Ymd')) {
        $ret['status'] = -2;
        $ret['messaggio'] = "Utente disattivato";
        $ret['codiceUtente'] = $Utenti_rec['UTECOD'];
        return $ret;
    }

    if ($Utenti_rec['DATAFINE'] != '' && $Utenti_rec['DATAFINE'] < date('Ymd')) {
        $ret['status'] = -2;
        $ret['messaggio'] = "Utente disattivato";
        $ret['codiceUtente'] = $Utenti_rec['UTECOD'];
        return $ret;
    }

    require_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
    $accLib = new accLib();
    $accLib->setITW($ITW_DB);
    $accLib->setITALWEB($ITALWEB_DB);

    if ($isActiveLDAP) {
        /*
         * LDAP è attivo, avvio autenticazione tramite esso.
         */

        require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
        require_once ITA_LIB_PATH . '/itaPHPCore/itaLdap.class.php';

        $userCanSkipLDAPErrors = false;
        foreach (array('ITALSOFT', 'ADMIN') as $gruppoDesc) {
            $userCanSkipLDAPErrors = $userCanSkipLDAPErrors || $accLib->utentePresenteInGruppo($Utenti_rec['UTECOD'], $accLib->getCodiceGruppo($gruppoDesc));
        }

        $ldapParams = array();
        $devLib = new devLib();
        $devLib->setITALWEB(ItaDB::DBOpen('ITALWEB', $ditta));
        foreach ($devLib->getEnv_config('LDAP') as $env) {
            $ldapParams[$env['CHIAVE']] = $env['CONFIG'];
        }

        $ldap = itaLdap::getLdapAuthenticator(array(
                "LdapHost" => $ldapParams['LDAP_HOST'],
                "LdapPort" => $ldapParams['LDAP_PORT'],
                "LdapBaseDN" => $ldapParams['LDAP_BASE_DN']
        ));

        if ($ldapParams['LDAP_SEARCH_USER']) {
            $authResult = $ldap->authenticate($ldapParams['LDAP_SEARCH_USER'], $ldapParams['LDAP_SEARCH_PASS']);

            if (!$authResult && !$userCanSkipLDAPErrors) {
                $ret['status'] = -4;
                $ret['messaggio'] = 'Errore LDAP: ricerca fallita ' . $ldap->getLastErrorMessage();
                return $ret;
            }
        }

        $utenteLDAP = $utente;
        if ($Utenti_rec['UTELDAP']) {
            $utenteLDAP = $Utenti_rec['UTELDAP'];
        }

        $attrRicerca = $ldapParams['LDAP_LOGNAME_ATTR'] ?: 'uid';
        $searchResult = $ldap->search("($attrRicerca=$utenteLDAP)", array($attrRicerca));

        if (!$searchResult && !$userCanSkipLDAPErrors) {
            $ret['status'] = -4;
            $ret['messaggio'] = 'Errore LDAP: ' . $ldap->getLastErrorMessage();
            $ret['codiceUtente'] = $Utenti_rec['UTECOD'];
            return $ret;
        }

        if ($searchResult['count'] > 0) {
            $ret['status'] = 0;
            $ret['messaggio'] = "";
            $ret['codiceUtente'] = $Utenti_rec['UTECOD'];
            $ret['nomeUtente'] = $Utenti_rec['UTELOG'];

            $authResult = $ldap->authenticate($searchResult[0]['dn'], $password);

            if (!$authResult) {
                $ret['status'] = -4;
                $ret['messaggio'] = 'Errore LDAP: ' . $ldap->getLastErrorMessage();
                return $ret;
            }

            $ret_upd = $accLib->updateUserLastAccess($ITW_DB, $Utenti_rec, $ditta);
            if ($ret_upd !== true) {
                $ret = $ret_upd;
            }

            return $ret;
        }

        /*
         * Se non ci sono entry corrispondenti al nome utente, continuo con
         * la normale routine di login.
         */
    }

    if ($Utenti_rec['UTEPAS'] == '' && $password == '') {
        $ret['status'] = 0;
        $ret['messaggio'] = "";
        $ret['codiceUtente'] = $Utenti_rec['UTECOD'];
        $ret['nomeUtente'] = $Utenti_rec['UTELOG'];

        $ret_upd = $accLib->updateUserLastAccess($ITW_DB, $Utenti_rec, $ditta);
        if ($ret_upd !== true) {
            $ret = $ret_upd;
        }


        return $ret;
    }

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
                $ret['status'] = -3;
                $ret['messaggio'] = "Errore di accesso a secure method eq";
                $ret['codiceUtente'] = $Utenti_rec['UTECOD'];

                return $ret;
            } else {
                $encryptedPassword = $result;
            }
            break;
        case 'md5':
            $encryptedPassword = md5($password);
            break;
        case 'sha1':
            $encryptedPassword = sha1($password);
            break;
        case 'sha256':
            $encryptedPassword = hash('sha256', $password);
            break;

        default:
            $encryptedPassword = $password;
            break;
    }
    if ($encryptedPassword == $Utenti_rec['UTEPAS']) {
        $workDate = date('Ymd');
        if ($workDate >= $Utenti_rec['UTESPA']) {
            $flagResetPassword = $accLib->GetEnv_Utemeta($Utenti_rec['UTECOD'], 'codice', 'FlagResetPassword');
            if ($flagResetPassword) {
                $ret['status'] = '-99';
                $ret['messaggio'] = "Password annullata.";
                $ret['codiceUtente'] = $Utenti_rec['UTECOD'];
                $ret['nomeUtente'] = $Utenti_rec['UTELOG'];
                return $ret;
            }

            if ($Utenti_rec['UTEDPA'] != '9999') {
                $ret['status'] = '-99';
                $ret['messaggio'] = "Password scaduta.";
                $ret['codiceUtente'] = $Utenti_rec['UTECOD'];
                $ret['nomeUtente'] = $Utenti_rec['UTELOG'];
                return $ret;
            }
        }

        $ret['status'] = 0;
        $ret['messaggio'] = "";
        $ret['codiceUtente'] = $Utenti_rec['UTECOD'];
        $ret['nomeUtente'] = $Utenti_rec['UTELOG'];

        $ret_upd = $accLib->updateUserLastAccess($ITW_DB, $Utenti_rec, $ditta);
        if ($ret_upd !== true) {
            $ret = $ret_upd;
        }
        return $ret;
    } else {
        $ret['status'] = -2;
        $ret['messaggio'] = "Password errata";
        $ret['codiceUtente'] = $Utenti_rec['UTECOD'];
        $ret['nomeUtente'] = $Utenti_rec['UTELOG'];
        return $ret;
    }
}

function ita_token($token, $ditta, $cod_ute, $modo) {
    $ret = array();
    $ret['token'] = "0";
    $ret['status'] = '';
    $ret['messaggio'] = '';
    try {
        $ITW_DB = ItaDB::DBOpen('ITW', $ditta);
    } catch (Exception $e) {
        Out::msgStop("Errore", $e->getMessage());
        $ret['status'] = '-4';
        $ret['messaggio'] = "Errore Aperutra DB Sicurezza";
        return $ret;
    }
    switch ($modo) {
        case 1 :
            $utenti_rec = ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM UTENTI WHERE UTECOD='" . $cod_ute . "'", false);
            if (!$utenti_rec) {
                $ret['status'] = "-1";
                $ret['messaggio'] = "Errore in lettura dati utente";
                return $ret;
            }

            $max_acces = $utenti_rec['UTEFIL__1'];
            $max_min = $utenti_rec['UTEFIL__2'];
            if ($max_min == 0) {
                $max_min = 5;
            }
// ESTRAGGO TUTTI I TOKEN DELL'UTENTE
            $key_token = str_pad($cod_ute, 6, "0", STR_PAD_LEFT);
            $token_tab = ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM TOKEN WHERE TOKCOD LIKE '" . $key_token . "%'");
// CANCELLO I TOKEN SCADUTI O NON PIU VALIDI
            foreach ($token_tab as $key => $token_rec) {
                $elaps_time = (float) (time() / 60) - (float) $token_rec['TOKFIL__1'];
                if ($elaps_time > $max_min || $token_rec['TOKNUL'] != 0) {
                    ItaDB::DBDelete($ITW_DB, 'TOKEN', 'ROWID', $token_rec['ROWID']);
                }
            }

// ESTRAGGO TUTTI I TOKEN DELL'UTENTE ORA SONO SOLO QUELLI VALIDI
            $token_tab = ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM TOKEN WHERE TOKCOD LIKE '" . $key_token . "%' ORDER BY TOKCOD DESC");
            $token_rec = $token_tab[0];
            ob_start();
            (int) $number = count($token_tab);
            ob_clean();
            if ($number >= $max_acces) {
                $ret['status'] = "-2";
                $ret['messaggio'] = "È stato superato il numero massimo di accessi contemporanei per utente.\nUtilizzare o chiudere le altre eventuali schede aperte.";
                return $ret;
            }
            $n_sessio_int = (int) substr($token_rec['TOKCOD'], 6, 3) + 1;
            $n_sessio = str_pad($n_sessio_int, 3, "0", STR_PAD_LEFT);
            $n_casuale = mt_rand(1, 9999999999);
            $rec_insert = array();
            $rec_insert['TOKCOD'] = $key_token . $n_sessio;
            $rec_insert['TOKFIL__2'] = $n_casuale;
            $rec_insert['TOKFIL__3'] = 0;
            $rec_insert['TOKORA'] = date('Hi');
            $rec_insert['TOKDAT'] = date('dmY');
            $rec_insert['TOKFIA__2'] = date('dmY');
            $rec_insert['TOKFIL__1'] = (float) (time() / 60);
            $rec_insert['TOKNUL'] = 0;
            $rec_insert['TOKUTE'] = $cod_ute;
            try {
                $nRows = ItaDB::DBInsert($ITW_DB, 'TOKEN', 'ROWID', $rec_insert);
                $ret['token'] = $key_token . $n_sessio . $n_casuale . '-' . $ditta;
                $ret['status'] = '0';
                $ret['messaggio'] = "";
                return $ret;
            } catch (Exception $e) {
                Out::msgStop("Errore in Inserimento", $e->getMessage(), '600', '600');
                $ret['ststus'] = '-3';
                $ret['messaggio'] = "Errore in assegnazione sessione";
                return $ret;
            }
            break;
        case 2 :
            IF ($token == '') {
                $ret['token'] = $token;
                $ret['status'] = '-5';
                $ret['messaggio'] = "Sessione da chiudere indefinita";
                return $ret;
            }
            if (closeToken($ITW_DB, $token)) {
                $ret['token'] = $token;
                $ret['status'] = '0';
            } else {
                $ret['token'] = '';
                $ret['status'] = '-7';
                $ret['messaggio'] = "Errore cancellazione sessione";
            }
            return $ret;
            break;
        case 3 :
        case 103 :
            IF ($token == '') {
                $ret['token'] = $token;
                $ret['status'] = '-5';
                $ret['messaggio'] = "Sessione indefinita";
                return $ret;
            }
            $cod_ute = (int) substr($token, 0, 6);
            $utenti_rec = ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM UTENTI WHERE UTECOD='" . $cod_ute . "'", false);
            if ($utenti_rec == false) {
                $ret['status'] = "-1";
                $ret['messaggio'] = "Errore in lettura dati utente";
                return $ret;
            }
            $max_acces = $utenti_rec['UTEFIL__1'];
            $max_min = $utenti_rec['UTEFIL__2'];
            if ($max_min == 0) {
                $max_min = 5;
            }
            $nomeUtente = $utenti_rec['UTELOG'];

            $key_token = substr($token, 0, 9);
            $token_rec = ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM TOKEN WHERE TOKCOD ='" . $key_token . "'", false);
            $elaps_time = (int) (time() / 60) - (int) $token_rec['TOKFIL__1'];
            if ($elaps_time < $max_min && $token_rec['TOKNUL'] != 1) {
                $token_rec['TOKFIL__1'] = (int) time() / 60;
                try {
                    if ($modo == 3) {
                        $nRows = ItaDB::DBUpdate($ITW_DB, 'TOKEN', 'ROWID', $token_rec);
                    }
                    $ret['token'] = $token;
                    $ret['nomeUtente'] = $nomeUtente;
                    $ret['codiceUtente'] = $cod_ute;
                    $ret['status'] = '0';
                } catch (Exception $e) {
                    Out::msgStop("Errore in Aggiornamento su TOKEN", $e->getMessage());
                    $ret['status'] = "-8";
                    $ret['messaggio'] = "Errore in aggiornamento sessione";
                    return $ret;
                }
            } else {
                $ret['token'] = $token;
                $ret['status'] = '-6';
                $ret['messaggio'] = "Sessione scaduta";
            }
            return $ret;
            break;
        CASE 20 :
            return substr($token, 0, 9);
    }
}

function closeToken($ITW_DB, $token) {
    $key_token = substr($token, 0, 9);
    $token_rec = ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM TOKEN WHERE TOKCOD ='" . $key_token . "'", false);
    if (!$token_rec) {
        return false;
    } else {
        $nRows = ItaDB::DBDelete($ITW_DB, 'TOKEN', 'ROWID', $token_rec['ROWID']);
        if ($nRows != -1) {
            $ret_del = eqUtil::delEqSession($token);
            return true;
        } else {
            return false;
        }
    }
}
