<?php

/**
 * Libreria per la gestione degli utenti da/a DB CityWare
 *
 * @author Carlo <carlo.iesari@italsoft.eu>
 */
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR_UTENTI.class.php';

class accLibCityWare {

    private $errCode;
    private $errMessage;
    private $ITW_DB;
    private $ITALWEB;
    private $libBorUtenti;
    private $accLib;

    const PASSWORD_SYMBOLS = '!$%&/()=?-_*';

    public function __construct($ditta = null) {
        $this->libBorUtenti = new cwbLibDB_BOR_UTENTI();
        $this->accLib = new accLib();

        if (!is_null($ditta)) {
            $this->libBorUtenti->setEnte($ditta);
        }
    }

    public function setITW($ITW_DB) {
        $this->ITW_DB = $ITW_DB;
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

    public function setITALWEB($ITALWEB) {
        $this->ITALWEB = $ITALWEB;
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

    private function dateAddDashes($date) {
        return strlen($date) === 8 ? substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2) : '';
    }

    public function convertUserCW2IE($user_rec, $insert_utecod = false, $case = '') {
        $Utenti_rec = array();
        $Richut_rec = array();
        $Env_utemeta_tab = array();

//        $Utenti_rec['UTECOD'] = $UTECOD;
        switch (strtolower($case)) {
            case 'upper':
                $Utenti_rec['UTELOG'] = strtoupper($user_rec['BOR_UTENTI']['CODUTE']);
                break;
            case 'lower':
                $Utenti_rec['UTELOG'] = strtolower($user_rec['BOR_UTENTI']['CODUTE']);
                break;
            default:
                $Utenti_rec['UTELOG'] = $user_rec['BOR_UTENTI']['CODUTE'];
                break;
        }

        $encryptedPassword = $this->accLib->getEncryptedPassword($user_rec['BOR_UTENTI']['PWDUTE']);
        if ($encryptedPassword === false) {
            throw new Exception('Errore getEncryptedPassword: ' . $this->accLib->getErrMessage());
        }

        $Utenti_rec['UTEPAS'] = $encryptedPassword;
        $Utenti_rec['UTEDATAULUSO'] = str_replace('-', '', $user_rec['BOR_UTENTI']['DATAULUSO']);
        $Utenti_rec['DATAINIZ'] = str_replace('-', '', $user_rec['BOR_UTENTI']['DATAINIZ']);
        $Utenti_rec['DATAFINE'] = str_replace('-', '', $user_rec['BOR_UTENTI']['DATAFINE']);
        $Utenti_rec['UTEUPA'] = str_replace('-', '', $user_rec['BOR_UTENTI']['DTASSPWD']);
        $Utenti_rec['UTEDPA'] = $user_rec['BOR_UTENTI']['GGVALPWD'];
        $Utenti_rec['UTEFLADMIN'] = $user_rec['BOR_UTENTI']['FLAG_ADMIN'];
        $Utenti_rec['UTEFIS'] = $user_rec['BOR_UTENTI']['CODFISCALE'];
        $Utenti_rec['UTELDAP'] = $user_rec['BOR_UTEFIR']['LDAP_USER'];

        $Denominazione = explode(' ', $user_rec['BOR_UTENTI']['NOMEUTE'], 2);
        $Richut_rec['RICDEN'] = $user_rec['BOR_UTENTI']['NOMEUTE'];
        $Richut_rec['RICCOG'] = $Denominazione[1];
        $Richut_rec['RICNOM'] = $Denominazione[0];
        $Richut_rec['RICMAI'] = $user_rec['BOR_UTENTI']['E_MAIL'];
        $Richut_rec['RICUSM'] = $user_rec['BOR_UTENTI']['UTENTE'];
        $Richut_rec['RICPWM'] = $user_rec['BOR_UTENTI']['PASSW'];

        $ParmFirmaRemota = array(
            'METAKEY' => 'ParmFirmaRemota',
            'METAVALUE' => serialize(
                array(
                    'FirmaRemota' => array(
                        'Utente' => trim($user_rec['BOR_FRMUTE']['UTENTE']),
                        'Password' => trim($user_rec['BOR_FRMUTE']['PASSW'])
                    )
                )
            )
        );

        $Env_utemeta_tab[] = $ParmFirmaRemota;

        $Utenti_rec = array_map('trim', $Utenti_rec);
        $Richut_rec = array_map('trim', $Richut_rec);

        if ($insert_utecod) {
            $Utenti_rec['UTECOD'] = $insert_utecod;
            $Richut_rec['RICCOD'] = $insert_utecod;

            foreach ($Env_utemeta_tab as &$Env_utemeta_rec) {
                $Env_utemeta_rec['UTECOD'] = $insert_utecod;
            }
        }

        return array(
            'UTENTI' => $Utenti_rec,
            'RICHUT' => $Richut_rec,
            'ENV_UTEMETA' => $Env_utemeta_tab
        );
    }

    public function convertUserIE2CW($user_rec, $case = 'upper') {
        $Bor_utenti_rec = array();
        $Bor_utefir_rec = array();
        $Bor_frmute_rec = array();

        switch (strtolower($case)) {
            case 'upper':
                strtoupper($Bor_utenti_rec['CODUTE'] = $user_rec['UTENTI']['UTELOG']);
                break;
            case 'lower':
                strtolower($Bor_utenti_rec['CODUTE'] = $user_rec['UTENTI']['UTELOG']);
                break;
            default:
                $Bor_utenti_rec['CODUTE'] = $user_rec['UTENTI']['UTELOG'];
                break;
        }

        $Bor_utenti_rec['PWDUTE'] = $user_rec['UTENTI']['UTEPAS'];
        $Bor_utenti_rec['DATAULUSO'] = $this->dateAddDashes($user_rec['UTENTI']['UTEDATAULUSO']);
        $Bor_utenti_rec['DATAINIZ'] = $this->dateAddDashes($user_rec['UTENTI']['DATAINIZ']);
        $Bor_utenti_rec['DATAFINE'] = $this->dateAddDashes($user_rec['UTENTI']['DATAFINE']);
        $Bor_utenti_rec['DTASSPWD'] = $this->dateAddDashes($user_rec['UTENTI']['UTEUPA']);
        $Bor_utenti_rec['GGVALPWD'] = $user_rec['UTENTI']['UTEDPA'];
        $Bor_utenti_rec['FLAG_ADMIN'] = $user_rec['UTENTI']['UTEFLADMIN'];
        $Bor_utenti_rec['CODFISCALE'] = $user_rec['UTENTI']['UTEFIS'];

//        $Bor_utenti_rec['NOMEUTE'] = $user_rec['RICHUT']['RICDEN'];
        $Bor_utenti_rec['NOMEUTE'] = $user_rec['RICHUT']['RICNOM'] . ' ' . $user_rec['RICHUT']['RICCOG'];
        if (!trim($Bor_utenti_rec['NOMEUTE'])) {
            $Bor_utenti_rec['NOMEUTE'] = $user_rec['UTENTI']['UTELOG'];
        }

        $Bor_utenti_rec['E_MAIL'] = $user_rec['RICHUT']['RICMAI'];
        $Bor_utenti_rec['UTENTE'] = $user_rec['RICHUT']['RICUSM'];
        $Bor_utenti_rec['PASSW'] = $user_rec['RICHUT']['RICPWM'];

        $Bor_utefir_rec['CODUTE'] = $Bor_utenti_rec['CODUTE'];
        $Bor_utefir_rec['LDAP_USER'] = $user_rec['UTENTI']['UTELDAP'];

        $Bor_frmute_rec['CODUTE'] = $user_rec['UTENTI']['UTELOG'];
        $Bor_frmute_rec['UTENTE'] = '';
        $Bor_frmute_rec['PASSW'] = '';

        if (!isset($user_rec['ENV_UTEMETA'])) {
            $user_rec['ENV_UTEMETA'] = ItaDB::DBSelect($this->getITALWEB(), 'ENV_UTEMETA', "WHERE UTECOD = '{$user_rec['UTENTI']['UTECOD']}'");
        }

        foreach ($user_rec['ENV_UTEMETA'] as $Env_utemeta_rec) {
            switch ($Env_utemeta_rec['METAKEY']) {
                case 'ParmFirmaRemota':
                    $metavalue = unserialize($Env_utemeta_rec['METAVALUE']);
                    $Bor_frmute_rec['UTENTE'] = $metavalue['FirmaRemota']['Utente'];
                    $Bor_frmute_rec['PASSW'] = $metavalue['FirmaRemota']['Password'];
                    break;
            }
        }

        return array(
            'BOR_UTENTI' => $Bor_utenti_rec,
            'BOR_UTEFIR' => $Bor_utefir_rec,
            'BOR_FRMUTE' => $Bor_frmute_rec,
            'BOR_UTELIV' => array()
        );
    }

    /**
     * Aggiorna il campo CityWare DATAULUSO per l'utente $utelog.
     * 
     * @param string $utelog Nome utente
     * @param string $datauluso Data ultimo accesso in formato Y-m-d
     * @return boolean
     */
    public function updateUserLastAccess($utelog, $datauluso) {
        $libBorUtenti_rec = $this->libBorUtenti->leggiUtenti($utelog);

        if (!count($libBorUtenti_rec)) {
            return true;
        }

        $libBorUtenti_rec[strtoupper($utelog)]['BOR_UTENTI']['DATAULUSO'] = $datauluso;

        $errorMessages = false;
        $this->libBorUtenti->aggiornaUtente(strtoupper($utelog), $libBorUtenti_rec, $errorMessages);

        if ($errorMessages) {
            $this->errCode = -1;
            $this->errMessage = $errorMessages;
            return false;
        }

        return true;
    }

    /**
     * Aggiorna la password CityWare per l'utente $utelog.
     * 
     * @param string $utelog Nome utente
     * @param string $password Password
     * @param string $data Data in formato Y-m-d. Se omessa viene utilizzata la data odierna.
     * @return boolean
     */
    public function updateUserPassword($utelog, $password, $data = null) {
        if (is_null($data)) {
            $data = date('Y-m-d');
        }

        $libBorUtenti_rec = $this->libBorUtenti->leggiUtenti($utelog);

        if (!count($libBorUtenti_rec)) {
            return true;
        }

        $libBorUtenti_rec[strtoupper($utelog)]['BOR_UTENTI']['PWDUTE'] = $password;
        $libBorUtenti_rec[strtoupper($utelog)]['BOR_UTENTI']['DTASSPWD'] = $data;

        $errorMessages = false;
        $this->libBorUtenti->aggiornaUtente(strtoupper($utelog), $libBorUtenti_rec, $errorMessages);

        if ($errorMessages) {
            $this->errCode = -1;
            $this->errMessage = $errorMessages;
            return false;
        }

        return true;
    }

    public function getTempPassword() {
        return substr(sha1(rand()), 0, 16);
    }

}
