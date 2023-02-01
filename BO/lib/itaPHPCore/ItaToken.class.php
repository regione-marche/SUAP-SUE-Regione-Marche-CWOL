<?php
/**
 * Description of itaToken
 *
 * @author michele
 * 
 * @version    28.06.2016
 */
class ItaToken {

    private $ITW_DB;
    private $ditta;
    private $tokenKey;
    private $lastErrormessage;
    private $lastFunctionStatus;

    function __construct($ditta) {
        try {
            $this->ITW_DB = ItaDB::DBOpen('ITW', $ditta);
        } catch (Exception $e) {
            return false;
        }
        $this->ditta = $ditta;
    }

    public function getTokenKey() {
        return $this->tokenKey;
    }

    public function setTokenKey($tokenKey) {
        $this->tokenKey = $tokenKey;
    }

    public function getLastErrorMessage() {
        return $this->lastErrormessage;
    }

    public function getLastFunctionStatus() {
        return $this->lastFunctionStatus;
    }

    public function getInfo() {
        $key_token = substr($this->tokenKey, 0, 9);
        if (!$key_token) {
            return false;
        }
        $token_rec = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT * FROM TOKEN WHERE TOKCOD ='" . $key_token . "'", false);
        if (!$token_rec) {
            return false;
        } else {
            $Utenti_rec = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT * FROM UTENTI WHERE UTECOD={$token_rec['TOKUTE']}", false);
            if ($Utenti_rec) {
                $token_rec['TOKLOGNAME'] = $Utenti_rec['UTELOG'];
            } else {
                return false;
            }
            return $token_rec;
        }
    }

    public function createToken($cod_ute) {
        $utenti_rec = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT * FROM UTENTI WHERE UTECOD='" . $cod_ute . "'", false);
        if (!$utenti_rec) {
            $ret['status'] = "-1";
            $ret['messaggio'] = "Errore in lettura dati utente";
            $this->lastFunctionStatus = "-1";
            $this->lastErrormessage = "Errore in lettura dati utente";
            return $ret;
        }

        $max_acces = $utenti_rec['UTEFIL__1'];
        $max_min = $utenti_rec['UTEFIL__2'];
        if ($max_min == 0) {
            $max_min = 5;
        }
// ESTRAGGO TUTTI I TOKEN DELL'UTENTE
        $key_token = str_pad($cod_ute, 6, "0", STR_PAD_LEFT);
        $token_tab = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT * FROM TOKEN WHERE TOKCOD LIKE '" . $key_token . "%'");
// CANCELLO I TOKEN SCADUTI O NON PIU VALIDI
        foreach ($token_tab as $key => $token_rec) {
            $elaps_time = (float) (time() / 60) - (float) $token_rec['TOKFIL__1'];
            if ($elaps_time > $max_min || $token_rec['TOKNUL'] != 0) {
                ItaDB::DBDelete($this->ITW_DB, 'TOKEN', 'ROWID', $token_rec['ROWID']);
            }
        }

// ESTRAGGO TUTTI I TOKEN DELL'UTENTE ORA SONO SOLO QUELLI VALIDI
        $token_tab = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT * FROM TOKEN WHERE TOKCOD LIKE '" . $key_token . "%' ORDER BY TOKCOD DESC");
        $token_rec = $token_tab[0];
        //ob_start();
        (int) $number = count($token_tab);
        //ob_clean();
        if ($number >= $max_acces) {
            $ret['status'] = "-2";
            $ret['messaggio'] = "E' stato superato il numero massimo di accessi contemporanei per utente";
            $this->lastFunctionStatus = "-2";
            $this->lastErrormessage = "E' stato superato il numero massimo di accessi contemporanei per utente";
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
            $nRows = ItaDB::DBInsert($this->ITW_DB, 'TOKEN', 'ROWID', $rec_insert);
            $ret['token'] = $key_token . $n_sessio . $n_casuale . '-' . $this->ditta;
            $ret['status'] = '0';
            $ret['messaggio'] = "";
            $this->tokenKey = $ret['token'];
            $this->lastFunctionStatus = "0";
            $this->lastErrormessage = "";
            return $ret;
        } catch (Exception $e) {
            $ret['ststus'] = '-3';
            $ret['messaggio'] = "Errore in assegnazione sessione: " . $e->getMessage();
            $this->lastFunctionStatus = "-3";
            $this->lastErrormessage = "Errore in assegnazione sessione: " . $e->getMessage();
            return $ret;
        }
    }

    public function getUtentiRec() {
        $cod_ute = (int) substr($this->tokenKey, 0, 6);
        $utenti_rec = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT * FROM UTENTI WHERE UTECOD='" . $cod_ute . "'", false);
        if ($utenti_rec == false) {
            $ret['status'] = "-1";
            $ret['messaggio'] = "Errore in lettura dati token";
            $this->lastFunctionStatus = "-1";
            $this->lastErrormessage = "Errore in lettura dati token";
            return $ret;
        }
        return $utenti_rec;
    }

    public function checkToken() {
        if ($this->tokenKey == '') {
            $ret['token'] = $this->tokenKey;
            $ret['status'] = '-5';
            $ret['messaggio'] = "Sessione da controllare indefinita";
            $this->lastFunctionStatus = "-5";
            $this->lastErrormessage = "Sessione da controllare indefinita";
            return $ret;
        }
        $cod_ute = (int) substr($this->tokenKey, 0, 6);
        $utenti_rec = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT * FROM UTENTI WHERE UTECOD='" . $cod_ute . "'", false);
        if ($utenti_rec == false) {
            $ret['status'] = "-1";
            $ret['messaggio'] = "Errore in lettura dati utente";
            $this->lastFunctionStatus = "-1";
            $this->lastErrormessage = "Errore in lettura dati utente";
            return $ret;
        }
        $max_acces = $utenti_rec['UTEFIL__1'];
        $max_min = $utenti_rec['UTEFIL__2'];
        if ($max_min == 0) {
            $max_min = 5;
        }
        $nomeUtente = $utenti_rec['UTELOG'];

        $key_token = substr($this->tokenKey, 0, 9);
        $tail_token = substr($this->tokenKey, 9);
        list($n_casuale, $ditta) = explode("-", $tail_token);
        /*
         * Nuovo controllo con chiave estesa token
         */
        $token_rec = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT * FROM TOKEN WHERE TOKCOD ='" . $key_token . "' AND TOKFIL__2='" . $n_casuale . "'", false);
        //$token_rec = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT * FROM TOKEN WHERE TOKCOD ='" . $key_token . "'" , false);
        /*
         * Nuovo controllo errore lettura token
         */
        if (!$token_rec) {
            $ret['status'] = "-10";
            $ret['messaggio'] = "Errore in lettura sessione. ";
            $this->lastFunctionStatus = "-10";
            $this->lastErrormessage = "Errore in lettura sessione.";
            return $ret;
        }
        $iniTime = (float) (time() / 60);
        $finTime = (float) $token_rec['TOKFIL__1'];
        //$elaps_time = (float) (time() / 60) - (float) $token_rec['TOKFIL__1'];
        $elaps_time = $iniTime - $finTime;
        if ($elaps_time < $max_min && $token_rec['TOKNUL'] != 1) {
            $token_rec['TOKFIL__1'] = (float) time() / 60;
            try {
                $nRows = ItaDB::DBUpdate($this->ITW_DB, 'TOKEN', 'ROWID', $token_rec);
                $ret['token'] = $this->tokenKey;
                $ret['nomeUtente'] = $nomeUtente;
                $ret['codiceUtente'] = $cod_ute;
                $ret['status'] = '0';
                $this->lastFunctionStatus = "0";
                $this->lastErrormessage = "";
            } catch (Exception $e) {
                $ret['status'] = "-8";
                $ret['messaggio'] = "Errore in aggiornamento sessione: " . $e->getMessage();
                $this->lastFunctionStatus = "-8";
                $this->lastErrormessage = "Errore in aggiornamento sessione: " . $e->getMessage();
                return $ret;
            }
        } else {
            $ret['token'] = '';
            $ret['status'] = '-6';
            $ret['messaggio'] = "Sessione scaduta";
            $this->tokenKey = '';
            $this->lastFunctionStatus = "-6";
            $this->lastErrormessage = "Sessione scaduta";
        }
        return $ret;
    }

    public function destroyToken() {
        IF ($this->tokenKey == '') {
            $ret['token'] = $this->tokenKey;
            $ret['status'] = '-5';
            $ret['messaggio'] = "Sessione da chiudere indefinita";
            $this->lastFunctionStatus = "-5";
            $this->lastErrormessage = "Sessione da chiudere indefinita.";
            return $ret;
        }
        if ($this->closeToken()) {
            $this->lastFunctionStatus = "0";
            $this->lastErrormessage = "";

            $ret['token'] = $token;
            $ret['status'] = '0';
        } else {
            $this->lastFunctionStatus = "-7";
            $this->lastErrormessage = "Errore cancellazione sessione";
            $ret['token'] = '';
            $ret['status'] = '-7';
            $ret['messaggio'] = "Errore cancellazione sessione";
        }
        return $ret;
    }

    private function closeToken() {
        $key_token = substr($this->tokenKey, 0, 9);
        $token_rec = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT * FROM TOKEN WHERE TOKCOD ='" . $key_token . "'", false);
        if (!$token_rec) {
            return false;
        } else {
            $nRows = ItaDB::DBDelete($this->ITW_DB, 'TOKEN', 'ROWID', $token_rec['ROWID']);
            if ($nRows != -1) {
                return true;
            } else {
                return false;
            }
        }
    }
}

?>
