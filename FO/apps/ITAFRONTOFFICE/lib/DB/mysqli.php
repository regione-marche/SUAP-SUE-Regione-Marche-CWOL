<?php

/**
 * ITA_mysqli
 * Classe per la connessione ad un DB di tipo Mysql
 *
 * @author Andrea Vallorani <andrea.vallorani@email.it>
 * @author Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author Carlo Iesari <carlo@iesari.me>
 * @version 30.10.15
 */
class ITA_mysqli extends ITA_DriverDB {

    protected function apriConn() {
        //$persistent = ($this->newlink) ? '' : 'p:';
        $persistent = '';
        $this->linkid = mysqli_connect($persistent . $this->sqlhost, $this->sqluser, $this->sqlpass);
        if (mysqli_connect_error())
            $this->avvenutoErrore(1);

        if (function_exists('mysql_set_charset')) {
            mysqli_set_charset($this->linkid, 'latin1');
        }
        mysqli_query($this->linkid, "SET SESSION sql_mode = ''");
        return true;
    }

    protected function apriDB($forzaApertura = false) {
        if (!is_object($this->linkid) or $forzaApertura) {
            $this->apriConn();
        } else {
            // Modificato per errore su assegnazione linkid
            if (!mysqli_thread_id($this->linkid)) {
                $this->apriConn();
            }
        }
        if (!mysqli_select_db($this->linkid, $this->sqldb)) {
            $this->avvenutoErrore(2);
        }
        if (self::$utf8)
            mysql_query("SET NAMES utf8");
        return true;
    }

    protected function avvenutoErrore($cod, $query = '') {
        if ($cod == 1) {
            $cod_err = mysqli_connect_errno();
            $txt_err = mysqli_connect_error();
        } else {
            $cod_err = mysqli_errno($this->linkid);
            $txt_err = mysqli_error($this->linkid);
        }
        if ($cod_err == 2013) {
            $this->apriDB(true);
            return $cod_err;
        } else {
            throw ItaSecuredException::newItaSecuredException(
                    ItaException::TYPE_ERROR_DB, $cod, "Errore dbms n.$cod_err", "Errore MySQLi n.$cod_err : $txt_err"
            );
            //throw new Exception("Errore MySQLi n.$cod_err : $txt_err", $cod);
        }
    }

    public function quote($val) {
        return addslashes($val);
    }

    public function query($txt) {
        $this->apriDB();
        $result = mysqli_query($this->linkid, $txt);
        if (!$result)
            $this->avvenutoErrore(3);
        return $result;
    }

    protected function eliminaQuery($query_id) {
        mysqli_free_result($query_id);
        return true;
    }

    public function queryCount($txt) {
        $result = $this->queryRiga('SELECT COUNT(*) as ROWS FROM (' . $txt . ') AS SELEZIONE');
        return $result['ROWS'];
    }

    public function queryMultipla($txt, $indiciNumerici = FALSE, $da = '', $per = '', $chiudiConn = FALSE) {
        if (is_numeric($da) and is_numeric($per))
            $txt .= " LIMIT $da , $per";

        if ($result = $this->query($txt)) {
            $risultati = array();
            //inserisco tutte le righe risultato in un array
            if ($indiciNumerici) {
                while ($trovato = $result->fetch_row())
                    $risultati[] = $trovato;
            } else {
                while ($trovato = $result->fetch_assoc())
                    $risultati[] = $trovato;
            }
            //rilascio le risorse
            $this->eliminaQuery($result);
            //chiudo la connessione se richiesto
            if ($chiudiConn)
                $this->chiudiConn();
            //ritorno l'array
            return $risultati;
        }
        //altrimenti ritorno 0        
        return 0;
    }

    public function queryRiga($txt, $indiciNumerici = FALSE, $chiudiConn = FALSE) {
        //eseguo la query
        $result = $this->query($txt);
        //se esiste ritorno la prima riga della query
        $riga = ($indiciNumerici) ? mysqli_fetch_row($result) : mysqli_fetch_assoc($result);
        //rilascio le risorse
        $this->eliminaQuery($result);
        //chiudo la connessione se richiesto
        if ($chiudiConn)
            $this->chiudiConn();
        return $riga;
    }

    public function queryValore($txt, $chiudiConn = FALSE) {
        //eseguo la query
        $result = $this->queryRiga($txt, $chiudiConn);
        //se esiste ritorno il valore
        if (is_array($result)) {
            //ritorno il singolo valore
            return current($result);
        } else
            return 0;
    }

    public function queryValori($txt, $chiudiConn = FALSE) {
        //eseguo la query
        $result = $this->query($txt);
        //se è stata eseguita correttamente
        if ($result) {
            $risultati = array();
            //inserisco tutte le righe risultato in un array
            while ($riga = mysqli_fetch_row($result))
                $risultati[] = $riga[0];
            //rilascio le risorse
            $this->eliminaQuery($result);
            //chiudo la connessione se richiesto
            if ($chiudiConn)
                $this->chiudiConn();
            return $risultati;
        }
        //altrimenti ritorno 0
        return 0;
    }

    public function update($txt) {
        //eseguo update
        $this->query($txt);
        //restituisco il numero di righe aggiornate
        $n = mysqli_affected_rows($this->linkid);
        return $n;
    }

    public function chiudiConn() {
        if (mysqli_close($this->linkid)) {
            $this->linkid = false;
        } else
            $this->avvenutoErrore(5);
    }

    public function blank() {
        return "''";
    }

    public function isBlank() {
        return " = ''";
    }

    public function isNotBlank() {
        return " <> ''";
    }

    public function getJdbcDriverClass() {
        return 'com.mysql.jdbc.Driver';
    }

    public function getJdbcConnectionUrl() {
        return "jdbc:mysql://" . $this->getServer() . "/" . $this->getDB();
    }

    public function subString($value, $start, $len) {
        return "SUBSTR($value,$start,$len)";
    }

    public function strConcat($strArray, $strCount) {
        if ($strCount != 0) {
            return "concat(" . implode(" , ", $strArray) . ")";
        } else {
            return "";
        }
    }

    public function coalesce($coalesceArray, $coalesceCount) {
        if ($coalesceCount != 0) {
            return "coalesce(" . implode(" , ", $coalesceArray) . ")";
        } else {
            return "NULL";
        }
    }

    public function nullIf($var1, $var2) {
        return "NULLIF($var1, $var2)";
    }

    public function dateDiff($beginDate, $endDate) {
        return "DATEDIFF(STR_TO_DATE($beginDate,'%Y%m%d'), STR_TO_DATE($endDate,'%Y%m%d'))";
    }

    public function strLower($value) {
        return "LOWER($value)";
    }

    public function strUpper($value) {
        return "UPPER($value)";
    }

    public function regExBoolean($value, $expression) {
        return "$value REGEXP '$expression'";
    }

    public function round($value, $decimal = 0) {
        return "ROUND($value,$decimal)";
    }

    public function nextval($sequence) {
        return 'itanextval(' . $sequence . ')';
    }

    public function getLastId() {
        return mysqli_insert_id($this->linkid);
    }

    public function testConn() {
        //@TODO: VERIFICARE ATTENTAMENTE
        return $this->apriDB();
    }

    public function exists() {
        try {
            $this->apriDB();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function create() {
        if (!is_object($this->linkid))
            $this->apriConn();
        if (!mysqli_query($this->linkid, "CREATE DATABASE $this->sqldb;"))
            $this->avvenutoErrore(6);
        $this->apriDB();
    }

    public function version() {
        if ($this->linkid === false)
            $this->apriConn();
        return mysqli_get_server_info($this->linkid);
    }

    public function listTables() {
        $sql = "SHOW TABLES FROM $this->sqldb";
        $result = $this->query($sql);
        while ($row = mysqli_fetch_row($result))
            $list[] = $row[0];
        $this->eliminaQuery($result);
        return $list;
    }

    public function getTablesInfo() {
        $sql = "SHOW TABLE STATUS FROM $this->sqldb";
        $result = $this->query($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $list[$row['Name']] = array(
                'type' => $row['Engine'],
                'rows' => $row['Rows'],
                'create' => $row['Create_time'],
                'update' => $row['Update_time'],
                'size' => $row['Data_length'] + $row['Index_length']
            );
        }
        $this->eliminaQuery($result);
        return $list;
    }

    public function getTableObject($tabella) {
        return new ITA_Table($this, $tabella, $this->getColumnsInfo($tabella), $this->getPrimaryKey($tabella));
    }

    public function getPrimaryKey($tabella) {
        $sql = "SHOW KEYS FROM $tabella WHERE Key_name = 'PRIMARY'";
        $result = $this->query($sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $pkey = $row['Column_name'];
        } else {
            $pkey = null;
        }
        $this->eliminaQuery($result);
        return $pkey;
    }

    public function getColumnsInfo($tabella) {
        $sql = "SHOW COLUMNS FROM $tabella";
        $result = $this->query($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $list[$row['Field']] = array(
                'type' => $row['Type'],
                'null' => $row['Null'],
                'key' => $row['Key'],
                'default' => $row['Default'],
                'extra' => $row['Extra']
            );
        }
        $this->eliminaQuery($result);
        return $list;
    }

    public function getIndexesInfo($tabella) {
        $keys = array();
        $sql = "SHOW KEYS FROM $tabella";
        $result = $this->query($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $keys[] = array(
                'name' => $row['Key_name'],
                'field' => $row['Column_name'],
                'order' => $row['Seq_in_index'],
                'unique' => $row['Non_unique'] == '1' ? '0' : '1'
            );
        }
        $this->eliminaQuery($result);
        return $keys;
    }

    public function getNormalizedValue($fieldInfo, $value) {
        if (strpos($fieldInfo['type'], 'varchar') !== false) {
            return "'" . $this->quote($value) . "'";
        } elseif (strpos($fieldInfo['type'], 'char') !== false) {
            return "'" . $this->quote($value) . "'";
        } elseif (strpos($fieldInfo['type'], 'text') !== false) {
            return "'" . $this->quote($value) . "'";
        } elseif (strpos($fieldInfo['type'], 'int') !== false) {
            return ($value == '') ? '0' : $value;
        } elseif (strpos($fieldInfo['type'], 'smallint') !== false) {
            return ($value == '') ? '0' : $value;
        } elseif (strpos($fieldInfo['type'], 'bigint') !== false) {
            return ($value == '') ? '0' : $value;
        } elseif (strpos($fieldInfo['type'], 'mediumint') !== false) {
            return ($value == '') ? '0' : $value;
        } elseif (strpos($fieldInfo['type'], 'integer') !== false) {
            return ($value == '') ? '0' : $value;
        } elseif (strpos($fieldInfo['type'], 'float') !== false) {
            return ($value == '') ? '0' : $value;
        } elseif (strpos($fieldInfo['type'], 'double') !== false) {
            return ($value == '') ? '0' : $value;
        } elseif (strpos($fieldInfo['type'], 'decimal') !== false) {
            return ($value == '') ? '0' : $value;
        } elseif (strpos($fieldInfo['type'], 'smallint') !== false) {
            return ($value == '') ? '0' : $value;
        } else {
            return ($value == '') ? "''" : "'" . $this->quote($value) . "'";
        }
    }

    public function countTables() {
        return count($this->listTables());
    }

}

?>
