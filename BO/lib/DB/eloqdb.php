<?php

/**
 * ITA_eloqdb
 * Classe per la connessione ad un DB di tipo eloqdb
 *
 * @author Andrea Vallorani <andrea.vallorani@email.it>
 * @author Carlo Iesari <carlo@iesari.me>
 * @version 16.03.2016
 */
class ITA_eloqdb extends ITA_DriverDB {

    protected function apriConn() {
        $this->linkid = odbc_connect($this->sqldb, $this->sqluser, $this->sqlpass);
        if (!is_resource($this->linkid))
            $this->avvenutoErrore(1, "errore apertura:" . $this->sqldb);
        return TRUE;
    }

    protected function apriDB($forzaApertura = FALSE) {
        if (!is_resource($this->linkid) or $forzaApertura)
            $this->apriConn();
        return TRUE;
    }

    protected function avvenutoErrore($cod, $query = '') {
        $cod_err = $cod;
        $txt_err = htmlspecialchars(odbc_error($this->linkid) . ' --- ' . odbc_errormsg($this->linkid));
        throw new Exception("Errore eloqdb n.$cod_err : $txt_err <br>$query", $cod);
    }

    public function quote($val) {
        return addcslashes($val, "'");
    }

    public function query($txt) {
        $this->apriDB();
        $result = odbc_exec($this->linkid, $txt);
        if (!$result) {
            $this->avvenutoErrore(3, $txt);
        }
        return $result;
    }

    protected function eliminaQuery($query_id) {

        if (!odbc_free_result($query_id))
            $this->avvenutoErrore(4);
        //$this->registraAzione("Liberata query con id: $query_id");
        return TRUE;
    }

    public function queryCount($txt) {
        $sqlString1 = explode('SELECT', $txt, 2);
        $sqlString2 = explode('FROM', $txt, 2);
        $sqlString = $sqlString1[0] . 'SELECT COUNT(*) AS ROWS FROM ' . $sqlString2[1];
        $result = $this->queryRiga($sqlString);
        return $result['ROWS'];
    }

    public function queryMultipla($txt, $indiciNumerici = FALSE, $da = 'X', $per = 'Y', $chiudiConn = FALSE) {

        if ($result = $this->query($txt)) {
            $risultati = array();
            //inserisco tutte le righe risultato in un array
            if (is_numeric($da) and is_numeric($per)) {
                $fino = $da + $per - 1;
                for ($cur_rec = 0; $cur_rec <= $fino; $cur_rec++) {
                    if ($cur_rec >= $da && $cur_rec <= $fino) {
                        if (!$risultato = odbc_fetch_array($result)) {
                            break;
                        }
                        $risultati[] = $risultato;
                    } else {
                        odbc_fetch_row($result);
                    }
                }
            } else {
                while ($trovato = odbc_fetch_array($result)) {
                    $risultati[] = $trovato;
                }
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
        $riga = odbc_fetch_array($result);
        //rilascio le risorse
        $this->eliminaQuery($result);
        //chiudo la connessione se richiesto
        if ($chiudiConn)
            $this->chiudiConn();
        //$this->registraAzione("Query riga ha salvato la query: ".count($riga)." valori presenti");
        //ritorno la riga
        return $riga;
    }

    public function queryValore($txt, $chiudiConn = FALSE) {
        //eseguo la query
        $result = $this->queryRiga($txt, $chiudiConn);
        //se esiste ritorno il valore
        if (is_array($result)) {
            //$this->registraAzione("Query valore ha salvato la query: ritorna il valore ".current($result));
            //ritorno il singolo valore
            return current($result);
        } else
            return 0;
    }

    public function queryValori($txt, $chiudiConn = FALSE) {
        //eseguo la query
        $result = $this->query($txt);
        //se � stata eseguita correttamente
        if ($result) {
            $risultati = array();
            //inserisco tutte le righe risultato in un array
            while ($riga = odbc_fetch_array($result))
                $risultati[] = current($riga);
            //rilascio le risorse
            $this->eliminaQuery($result);
            //chiudo la connessione se richiesto
            if ($chiudiConn)
                $this->chiudiConn();
            //else $this->registraAzione("Connessione lasciata aperta");
            //$this->registraAzione("Query valori ha salvato la query: ".count($risultati)." valori presenti");
            //ritorno l'array
            return $risultati;
        }
        //altrimenti ritorno 0
        return 0;
    }

    public function update($txt) {
        $result = $this->query($txt);
        $n = odbc_num_rows($result);
        if ($n == 0) {
            $n == -1;
        }
        return $n;
    }

    public function chiudiConn() {
        if (odbc_close($this->linkid)) {
            //$this->registraAzione("Chiusa connessione con id $this->linkid");
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
        return "sun.jdbc.odbc.JdbcOdbcDriver";
    }

    public function getJdbcConnectionUrl() {
        return "jdbc:odbc:" . $this->getDB();
    }

    public function subString($value, $start, $len) {
//        if($start >1 )
//            $start = $start-1;
        return "SUBSTRING($value, $start, $len)";
    }

    public function strConcat($strArray, $strCount) {
        if ($strCount != 0) {
            return implode(" & ", $strArray);
        } else {
            return "";
        }
    }

    public function coalesce($coalesceArray, $coalesceCount) {
        return null;
    }

    public function nullIf($var1, $var2) {
        return null;
    }

    public function dateDiff($beginDate, $endDate) {
        return null;
    }

    public function strLower($value) {
        return "LCASE($value)";
    }

    public function strUpper($value) {
        return "UCASE($value)";
    }

    public function regExBoolean($value, $expression) {
        return null;
    }

    public function round($value, $decimal = 0) {
        $decimal = $decimal * -1;
        return "@ROUND($value, $decimal)";
    }

    public function getLastId() {
        $this->avvenutoErrore(101);
    }

    public function nextval($sequence) {
        $this->avvenutoErrore(101);
    }

    public function testConn() {
        return $this->apriDB();
    }

    public function exists() {
        try {
            $this->apriConn();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function create() {
        $this->avvenutoErrore(102);
    }

    public function version() {
        // SQLR non funziona
        return null;

//        if ($this->linkid === FALSE)
//            $this->apriConn();
//        $this->avvenutoErrore(103);
//        return mysql_get_server_info($this->linkid);
    }

    public function listTables() {

        // SQLR non funziona
        $list = null;
        /*
          $result = odbc_tables($this->linkid, "%", "%", "%", "TABLE");
          $tables = array();
          while (odbc_fetch_row($result)) {

          if (odbc_result($result, "TABLE_TYPE") == "TABLE")
          $list[] = odbc_result($result, "TABLE_NAME");
          }

          }
         */
        return $list;
    }

    public function getTablesInfo() {

        // SQLR non funziona
        $list = null;


//        $this->avvenutoErrore(105);
//
//        $sql = "SHOW TABLE STATUS FROM $this->sqldb";
//        $result = $this->query($sql);
//        while ($row = mysql_fetch_assoc($result)) {
//            $list[$row['Name']] = array(
//                'type' => $row['Engine'],
//                'rows' => $row['Rows'],
//                'create' => $row['Create_time'],
//                'update' => $row['Update_time'],
//                'size' => $row['Data_length'] + $row['Index_length']
//            );
//        }
//        $this->eliminaQuery($result);

        return $list;
    }

    public function getTableObject($tabella) {
        return $tabella;
    }

    public function getPrimaryKey($tabella) {
        return null;
    }

    public function getColumnsInfo($tabella) {

        // SQLR non funziona
        $list = null;
//
//        
//        $this->avvenutoErrore(106);
//
//        $sql = "SHOW COLUMNS FROM $tabella";
//        $result = $this->query($sql);
//        while ($row = mysql_fetch_assoc($result)) {
//            $list[$row['Field']] = array(
//                'type' => $row['Type'],
//                'null' => $row['Null'],
//                'key' => $row['Key'],
//                'default' => $row['Default'],
//                'extra' => $row['Extra']
//            );
//        }
//        $this->eliminaQuery($result);

        return $list;
    }

    public function getIndexesInfo($tabella) {
        return null;
    }

    public function getNormalizedValue($fieldInfo, $value) {
        if ($value != '') {
            return "'" . $db->quote($value) . "'";
        } else {
            return "NULL";
        }
    }

    public function countTables() {

        // SQLR non funziona
        return null;


//        $this->avvenutoErrore(107);
//
//        return count($this->listTables());
    }

}

?>