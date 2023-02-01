<?php

/**
 * ITA_pgsql
 * Classe per la connessione ad un DB di tipo PostgreSQL
 *
 * @author Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author Carlo Iesari <carlo@iesari.me>
 * @version 30.10.15
 */
class ITA_pgsql extends ITA_DriverDB {

    protected function apriConn() {
        $newConnection = ($this->newlink) ? PGSQL_CONNECT_FORCE_NEW : PGSQL_CONNECTION_OK;
        list($host, $skip) = explode(":", $this->sqlparams['host']);
        $hostOpt = ($host) ? "host=$host" : "";
        list($skip, $port) = explode(":", $this->sqlparams['host']);
        $portOpt = ($port) ? "port=$port" : "";
        $dbname = $this->sqlparams['database'];
        $dbnameOpt = ($dbname) ? "dbname=$dbname" : "";
        $userOpt = "user={$this->sqlparams['user']}";
        $passwordOpt = "password={$this->sqlparams['pwd']}";
        $options = "options='--client_encoding=LATIN1'";
        $this->linkid = pg_connect("$hostOpt $portOpt $dbnameOpt $userOpt $passwordOpt");
        if (!is_resource($this->linkid))
            $this->avvenutoErrore(1);
        return true;
    }

    protected function apriDB($forzaApertura = FALSE) {
        if (!is_resource($this->linkid) or $forzaApertura) {
            $this->apriConn();
        }
        //list($skip, $schema) = explode("/", $this->sqldb);
        $schema = $this->sqlparams['dbRealName'];
        if (!pg_query($this->linkid, 'SET search_path TO "' . $schema . '"')) {
            $this->avvenutoErrore(2);
        }
        if (self::$utf8)
            pg_query("SET NAMES 'UTF8';");
        return true;
    }

    protected function avvenutoErrore($cod, $query = '') {
        $cod_err = '';
        if ($cod == 1) {
            $err_last = error_get_last();
            $txt_err = $err_last['message'];
        } else {
            $txt_err = pg_last_error($this->linkid);
        }
        throw new Exception("Errore postgres: $txt_err", $cod);
    }

    public function quote($val) {
        return pg_escape_string($val);
    }

    public function query($txt) {
        $this->apriDB();
        $result = pg_query($this->linkid, $txt);
        if (!$result) {
            $this->avvenutoErrore(3);
            return false;
        }
        return $result;
    }

    protected function eliminaQuery($query_id) {
        if (!pg_free_result($query_id))
            $this->avvenutoErrore(4);
        return true;
    }

    public function queryCount($txt) {
        $result = $this->queryRiga('SELECT COUNT(*) as ROWS FROM (' . $txt . ') AS SELEZIONE');
        return $result['ROWS'];
    }

    public function queryMultipla($txt, $indiciNumerici = false, $da = '', $per = '', $chiudiConn = FALSE) {
        if (is_numeric($da) and is_numeric($per)) {
            $offset = $da; // - 1;
            $txt .= " LIMIT $per OFFSET $offset";
        }
        if ($result = $this->query($txt)) {
            $risultati = array();
            //inserisco tutte le righe risultato in un array
            if ($indiciNumerici) {
                while ($trovato = pg_fetch_row($result))
                    $risultati[] = array_change_key_case($trovato, $this->sqlparams['fieldskeycase']);
            } else {
                while ($trovato = pg_fetch_assoc($result))
                    $risultati[] = array_change_key_case($trovato, $this->sqlparams['fieldskeycase']);
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

    public function queryRiga($txt, $indiciNumerici = false, $chiudiConn = false) {
        //eseguo la query
        $result = $this->query($txt);
        //se esiste ritorno la prima riga della query
        $riga = ($indiciNumerici) ? pg_fetch_row($result) : pg_fetch_assoc($result);
        $riga = array_change_key_case($riga, $this->sqlparams['fieldskeycase']);
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
            while ($riga = pg_fetch_row($result))
                $risultati[] = array_change_key_case($riga[0], $this->sqlparams['fieldskeycase']);
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
        //eseguo la query
        $result = $this->query($txt);
        if ($result === false) {
            $n = -1;
        } else {
            //restituisco il numero di righe aggiornate
            $n = pg_affected_rows($result);
        }
        return $n;
    }

    public function chiudiConn() {
        if (pg_close($this->linkid)) {
            $this->linkid = FALSE;
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
        return 'org.postgresql.Driver';
    }

    public function getJdbcConnectionUrl() {
        return 'jdbc:postgresql://' .
                $this->sqlparams['host'] . '/' . $this->sqlparams['database'] .
                '?user=' . $this->sqlparams['user'] .
                '&password=' . $this->sqlparams['pwd'] .
                '&currentSchema=' . $this->sqlparams['realname'];
    }

    public function subString($value, $start, $len) {
        return "substring($value form $start for $len)";
    }

    public function strConcat($strArray, $strCount) {
        if ($strCount != 0) {
            return implode(" || ", $strArray);
        } else {
            return "";
        }
    }

    public function coalesce($coalesceArray, $coalesceCount) {
        if ($coalesceCount != 0) {
            return "COALESCE(" . implode(" , ", $coalesceArray) . ")";
        } else {
            return "NULL";
        }
    }

    public function nullIf($var1, $var2) {
        return "NULLIF($var1, $var2)";
    }

    public function dateDiff($beginDate, $endDate) {
        return "to_date($beginDate,'YYYYMMDD') - to_date($endDate,'YYYYMMDD')";
    }

    public function strLower($value) {
        return "lower($value)";
    }

    public function strUpper($value) {
        return "upper($value)";
    }

    public function regExBoolean($value, $expression) {
        return "$value ~ '$expression'";
    }

    public function round($value, $decimal = 0) {
        return "round($value, $decimal)";
    }

    public function nextval($sequence) {
        return "nextval('$sequence')";
    }

    public function getLastId() {
        pg_send_query($this->linkid, "SELECT lastval();");
        $res = pg_get_result($this->linkid);
        $state = pg_result_error_field($res, PGSQL_DIAG_SQLSTATE);
        if ($state === null) {
            $insert_row = pg_fetch_row($res);
            return $insert_row[0];
        } else if ($state == 55000) {
            return 0;
        } else {
            $this->avvenutoErrore(6);
        }



//
// Vecchia versione da eliminare
//      
//        $insert_query = pg_query($this->linkid,"SELECT lastval();");
//        if (!$insert_query)
//            $this->avvenutoErrore(6);
//        $insert_row = pg_fetch_row($insert_query);
//        return $insert_row[0];
    }

    public function testConn() {
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
        if (!is_resource($this->linkid))
            $this->apriConn();
        if (!pg_query("CREATE DATABASE {$this->sqlparams['dbRealName']};", $this->linkid))
            $this->avvenutoErrore(6);
        $this->apriDB();
    }

    public function version() {
        if ($this->linkid === FALSE)
            $this->apriConn();
        return pg_version($this->linkid);
    }

    public function listTables() {
        $sql = "SELECT
                    *
                FROM
                    information_schema.tables
                WHERE
                    table_schema = '$this->sqlPa'";

        $result = $this->query($sql);

        while ($row = pg_fetch_assoc($result))
            $list[] = $row[0];

        $this->eliminaQuery($result);

        return $list;
    }

    public function getTablesInfo() {
        //$schema = substr($this->sqldb, strpos($this->sqldb, '/') + 1);
        $schema = $this->sqldb;

        $sql = "SELECT
                    *
                FROM
                    information_schema.tables
                WHERE
                    table_schema = '$schema'";

        $result = $this->query($sql);

        while ($row = pg_fetch_assoc($result))
            $list[] = $row;

        $this->eliminaQuery($result);

        return $list;
    }

    public function getTableObject($tabella) {
//        return new ITA_Table($this, $tabella, $this->getColumnsInfo($tabella), $this->getPrimaryKey($tabella));
    }

    public function getPrimaryKey($tabella) {
        $sql = "SELECT
                    a.attname, 
                    format_type(a.atttypid, a.atttypmod) AS data_type
                FROM
                    pg_index i
                JOIN
                    pg_attribute a
                ON
                    a.attrelid = i.indrelid AND
                    a.attnum = ANY(i.indkey)
                WHERE
                    i.indrelid = '$tabella'::regclass
                AND
                    i.indisprimary";
        $result = $this->query($sql);

        if ($result) {
            $row = pg_fetch_assoc($result);

            switch ($this->sqlparams['fieldskeycase']) {
                case CASE_LOWER :
                    $pkey = "..." . strtolower($row['attname']);
                    break;
                case CASE_UPPER :
                    $pkey = "---" . strtoupper($row['attname']);
                    break;
            }
        } else {
            $pkey = null;
        }

        $this->eliminaQuery($result);

        return $pkey;
    }

    public function getColumnsInfo($tabella) {
        //$schema = substr($this->sqldb, strpos($this->sqldb, '/') + 1);
        $schema = $this->sqldb;

        $sql = "SELECT
                    *
                FROM
                    information_schema.columns
                WHERE
                    table_schema = '$schema'
                AND
                    table_name = '$tabella'";

        $result = $this->query($sql);

        while ($row = pg_fetch_assoc($result))
            $list[] = $row;

        $this->eliminaQuery($result);

        return $list;
    }

    public function getIndexesInfo($tabella) {
        //$schema = substr($this->sqldb, strpos($this->sqldb, '/') + 1);
        $schema = $this->sqldb;

        $sql = "SELECT
                    a.attname, 
                    format_type(a.atttypid, a.atttypmod) AS data_type
                FROM
                    pg_index i
                JOIN
                    pg_attribute a
                ON
                    a.attrelid = i.indrelid AND
                    a.attnum = ANY(i.indkey)
                WHERE
                    i.indrelid = '\"$schema\".\"$tabella\"'::regclass";

        $result = $this->query($sql);

        while ($row = pg_fetch_assoc($result))
            $list[] = $row;

        $this->eliminaQuery($result);

        return $list;
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