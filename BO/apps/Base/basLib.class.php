<?php

/**
 *
 * LIBRERIA PER APPLICATIVO GAFIERE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Base
 * @author     Marilungo Alessandro <alessandro.marilungo@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    20.08.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
class basLib {

    /**
     * Libreria di funzioni Generiche e Utility per Pratiche
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private static $basLib = array();
    private $errMessage;
    private $errCode;
    public $BASE_DB;
    public $COMUNI_DB;
    public $ITW_DB;

    public static function getInstance($ditta = '') {
        if (!$ditta) {
            $ditta = App::$utente->getKey('ditta');
        }
        if (!isset(self::$basLib[$ditta])) {
            try {
                self::$basLib[$ditta] = new basLib();
            } catch (Exception $exc) {
                $this->setErrMessage($exc->getMessage());
                return false;
            }
        }
        return self::$basLib[$ditta];
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function setBASEDB($BASE_DB) {
        $this->BASE_DB = $BASE_DB;
    }

    public function setCOMUNIDB($COMUNI_DB) {
        $this->COMUNI_DB = $COMUNI_DB;
    }

    public function setITWDB($ITW_DB) {
        $this->ITW_DB = $ITW_DB;
    }

    public function getBASEDB() {
        if (!$this->BASE_DB) {
            try {
                $this->BASE_DB = ItaDB::DBOpen('ITALWEB');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->BASE_DB;
    }

    public function getCOMUNIDB() {
        if (!$this->COMUNI_DB) {
            try {
                $this->COMUNI_DB = ItaDB::DBOpen('COMUNI', '');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->COMUNI_DB;
    }

    public function getITWDB() {
        if (!$this->ITW_DB) {
            try {
                $this->ITW_DB = ItaDB::DBOpen('ITW');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITW_DB;
    }

    public function getGenericTab($sql, $multi = true, $tipoDB = 'ITALWEB') {
        if ($tipoDB == 'ITALWEB') {
            $tabella_tab = ItaDB::DBSQLSelect($this->getBASEDB(), $sql, $multi);
        } elseif ($tipoDB == 'COMUNI') {
            $tabella_tab = ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, $multi);
        }
        return $tabella_tab;
    }

    public function getComana($codice, $tipo = 'anacat', $anacod = '') {
        $multi = false;
        if ($tipo == 'anacat') {
            $sql = "SELECT * FROM ANA_COMUNE WHERE ANACAT='$codice'";
            $multi = true;
        } else if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANA_COMUNE WHERE ANACAT='$codice' AND ANACOD='$anacod'";
        } else if ($tipo == 'descrizione') {
            $sql = "SELECT * FROM ANA_COMUNE WHERE ANACAT='$codice' AND ANADES='$anacod'";
        } else {
            $sql = "SELECT * FROM ANA_COMUNE WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getBASEDB(), $sql, $multi);
    }

    public function getNewAnacodComana($anacat = "VIE") {
        if (!$anacat)
            return false;
        $sql = "SELECT MAX(ANACOD) AS MASSIMO FROM `ANA_COMUNE` WHERE ANACAT = '$anacat'";
        $max_rec = ItaDB::DBSQLSelect($this->getBASEDB(), $sql, false);
        $codice = (int) $max_rec['MASSIMO'] + 1;
        $new_anacod = str_pad($codice, 6, "0", STR_PAD_LEFT);
        return $new_anacod;
    }

    public function getComuni($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM COMUNI WHERE COMUNE='" . addslashes($codice) . "'";
        } elseif ($tipo == 'nascit') {
            $sql = "SELECT * FROM COMUNI WHERE NASCIT = '" . $codice . "'";
        } elseif ($tipo == 'coavpo') {
            $sql = "SELECT * FROM COMUNI WHERE COAVPO = '" . $codice . "'";
        } else {
            $sql = "SELECT * FROM COMUNI WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, false);
    }

    public function getComuniEsteri($naz, $codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM COMUNIESTERI WHERE CODICEONU = '" . addslashes($naz) . "' AND COMUNE='" . addslashes($codice) . "'";
        } elseif ($tipo == 'coavpo') {
            $sql = "SELECT * FROM COMUNIESTERI WHERE CODICEONU = '" . addslashes($naz) . "' AND COAVPO = '" . $codice . "'";
        } else {
            $sql = "SELECT * FROM COMUNIESTERI WHERE ROW_ID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, false);
    }

    public function getNazioni($codice, $tipo = 'codice') {
        if ($codice == '') {
            return false;
        }
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM NAZIONI WHERE DESCRIZIONE='" . addslashes($codice) . "'";
        } elseif ($tipo == 'onu') {
            $sql = "SELECT * FROM NAZIONI WHERE CODICEONU= '" . $codice . "'";
        } else {
            $sql = "SELECT * FROM NAZIONI WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, false);
    }

    public function getAreaLingua($codice, $tipo = 'codice') {
        if ($codice == '') {
            return false;
        }
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM AREELINGUISTICHE WHERE CODICE = '" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM AREELINGUISTICHE WHERE ROW_ID = $codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, false);
    }

    public function getRegioni($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM REGIONI WHERE REGIONE='" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM REGIONI WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, false);
    }

    public function getProvince($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM PROVINCE WHERE PROVINCIA='" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM PROVINCE WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, false);
    }

    public function getCittadinanze($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM CITTADINANZA WHERE CITTADINANZA='" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM CITTADINANZA WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, false);
    }

    public function getAmministrazioni($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM AMMINISTRAZIONI WHERE COD_AMM = '" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM AMMINISTRAZIONI WHERE ROWID = $codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, $multi);
    }

    public function getAoo($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM AOO WHERE COD_AOO = '" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM AOO WHERE ROWID = $codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, $multi);
    }

    public function getUo($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM UO WHERE COD_OU = '" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM UO WHERE ROWID = $codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, $multi);
    }

    public function getPec($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM PEC WHERE COD_AMM = '" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM PEC WHERE ROWID = $codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, $multi);
    }

    public function getRuolo($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANA_RUOLI WHERE RUOCOD = '" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM ANA_RUOLI WHERE ROWID = $codice";
        }
        return ItaDB::DBSQLSelect($this->getBASEDB(), $sql, $multi);
    }

    public function getDestinazioniUrbanistiche($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM PRG_DESTINAZIONI_URBANISTICHE WHERE ID_DESTINAZIONE = '" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM PRG_DESTINAZIONI_URBANISTICHE WHERE ROW_ID = $codice";
        }
        return ItaDB::DBSQLSelect($this->getBASEDB(), $sql, $multi);
    }

    public function getDestinazioniUso($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM PRG_DESTINAZIONI_USO WHERE ID_DESTINAZIONE = '" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM PRG_DESTINAZIONI_USO WHERE ROW_ID = $codice";
        }
        return ItaDB::DBSQLSelect($this->getBASEDB(), $sql, $multi);
    }

    public function SetMarcaturaRuolo($Anaruo_rec, $fl_ins = false) {
//        $marcatura = $this->GetMarcaturaModifiche();
        if ($fl_ins) {
//            $Anaruo_rec['RUOINSEDITOR'] = $marcatura['EDITOR'];
            $Anaruo_rec['RUOINSDATE'] = date('Ymd');
            $Anaruo_rec['RUOINSTIME'] = date('H:i:s');
        }
//        $Anaruo_rec['RUOUPDEDITOR'] = $marcatura['EDITOR'];
        $Anaruo_rec['RUOUPDDATE'] = date('Ymd');
        $Anaruo_rec['RUOUPDTIME'] = date('H:i:s');
        return $Anaruo_rec;
    }

    /**
     * Metodo per effettuare una generica chiamata CURL passando un set di CURL_OPTION
     * 
     * @param string $domain <p>Dominio o url che verrà settato dal curl_init.</p>
     * @param array $curl_opt <p>array di opzioni CURL composto da chiave valore:<br>ES:<br>array('CURLOPT_TIMEOUT' => 5, 'CURLOPT_HEADER' => false, 'CURLOPT_NOBODY' => true, 'CURLOPT_RETURNTRANSFER' => true)</p>
     * @return string
     */
    public function callCurl($domain, $curl_opt = array()) {
        $curlInit = curl_init($domain);
        foreach ($curl_opt as $option => $value) {
            curl_setopt($curlInit, $option, $value);
        }
        $response = curl_exec($curlInit);
        $response = curl_getinfo($curlInit, CURLINFO_HTTP_CODE);
        $response2 = curl_getinfo($curlInit, CURLINFO_HTTP_CONNECTCODE);
        $type = curl_getinfo($curlInit, CURLINFO_CONTENT_TYPE);
        curl_close($curlInit);
        return $response;
    }

    /**
     * Metodo per la ricerca dei cap per città con multicap (Ancona, Roma, Milano...)
     * basato sulla tabella COMUNI.CAPCITY
     * 
     * @param type $citta
     * @param type $indirizzo
     * 
     * @return string
     */
    public function getCAP($citta, $indirizzo) {
        if (!$citta) {
            return false;
        }
        if (!$indirizzo) {
            return false;
        }
        
        $via = '';
        //tolgo il numero civico
        if (strpos($indirizzo, ",") !== false) {
            list($via, $civico) = explode(",", $indirizzo);
        } else {
            $ar_ind = explode(" ", $indirizzo);
            $removed = array_pop($ar_ind);
            $via = implode(" ", $ar_ind);
        }
        $sqlCAP = "SELECT * FROM CAPCITY WHERE CITTA = '" . addslashes(($citta)) . "' AND ZONA <> '' AND ZONA = '" . addslashes(($via)) . "' ";
        $cap_rec = ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sqlCAP, false);
        if ($cap_rec) {
            return $cap_rec['CAP'];
        }
        //cerco parti parziali di via per trovare VIA MANZONI se dentro CAPCITY è scritta come VIA ALESSANDRO MANZONI
        foreach ($ar_ind as $nk => $parte) {
            if ($nk == 0) {
                continue; //escludo la prima parola (via, piazza, corso, contrada ecc)
            }
            if (strlen($parte) > 6) {
                //parola di almeno 7 caratteri
                //escludo parole troppo corte (si perderebbero VIA ROSSI, ma si evitano ambiguità per VIA DELLA... CORSO DELLA...)
                $sqlCAP = "SELECT * FROM CAPCITY WHERE CITTA = '" . addslashes(($citta)) . "' AND ZONA <> '' AND ZONA LIKE '%" . addslashes(($parte)) . "%' ";
                $cap_tab = ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sqlCAP, true);
                if (count($cap_tab) == 1) {
                    //vengono scartati risultati multipli come ad es. ANCONA - %ALESSANDRO% (via A. ORSI e via A. MAGGINI)
                    return $cap_tab[0]['CAP'];
                }
            }
        }
        return false;
    }

}
