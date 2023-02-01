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

    public function getComuni($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM COMUNI WHERE COMUNE='" . addslashes(strtoupper($codice)) . "'";
        } elseif ($tipo == 'nascit') {
            $sql = "SELECT * FROM COMUNI WHERE NASCIT = '" . $codice . "'";
        } elseif ($tipo == 'cap') {
            $sql = "SELECT * FROM COMUNI WHERE COAVPO = '" . strtoupper($codice) . "'";
        } elseif ($tipo == 'provincia') {
            $sql = "SELECT * FROM COMUNI WHERE PROVIN = '" . strtoupper($codice) . "'";
        } else {
            $sql = "SELECT * FROM COMUNI WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, false);
    }

    public function getNazioni($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM NAZIONI WHERE DESCRIZIONE='" . addslashes($codice) . "'";
        } elseif ($tipo == 'onu') {
            $sql = "SELECT * FROM NAZIONI WHERE CODICEONU= '" . $codice . "'";
        } else {
            $sql = "SELECT * FROM NAZIONI WHERE ROWID=$codice";
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

}

?>