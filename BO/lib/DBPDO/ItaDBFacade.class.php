<?php

require_once ITA_LIB_PATH . '/DB/DB.php';

/**
 * Description of DBHelperclass
 *
 * @author utente
 */
class ItaDBFacade {

    /**
     * Apri DataBase
     *
     * @param <type> $dbName Nome data base
     * @param <type> $dbSuffix Suffisso opzionale al Data base
     */
    static function DBOpen($dbName, $dbSuffix = 'ditta') {
        $dbParm = ItaDB::listConnections();
        $dbBaseName = $dbName;

        if (isset($dbParm[$dbBaseName]['realname'])) {
            $dbRealName = $dbParm[$dbBaseName]['realname'];
        } else {
            $dbRealName = $dbName;
        }

        switch ($dbSuffix) {
            case 'ditta':
                $dbName .= App::$utente->getKey('ditta');
                $dbRealName .= App::$utente->getKey('ditta');
                break;
            case '' :
                $dbName .= '';
                $dbRealName .= '';
                break;
            default:
                $dbName .= $dbSuffix;
                $dbRealName .= $dbSuffix;
                break;
        }
        $dbParm[$dbBaseName]['dbRealName'] = $dbRealName;
        $dbms = $dbParm[$dbBaseName]['dbms'];
        if (!$dbms) {
            throw new Exception($dbName . ': dbms non definito');
        }

        $newlink = (key_exists('newlink', $dbParm[$dbBaseName]) == true) ? $dbParm[$dbBaseName]['newlink'] : 0;
        $host = (key_exists('host', $dbParm[$dbBaseName]) == true) ? $dbParm[$dbBaseName]['host'] : '';
        $user = $dbParm[$dbBaseName]['user'];
        $pwd = $dbParm[$dbBaseName]['pwd'];
        $db = new ITA_DB(array('host' => $host, 'dbms' => $dbms, 'db' => $dbRealName, 'user' => $user, 'pwd' => $pwd, 'newlink' => $newlink), $dbParm[$dbBaseName]);
        return $db;
    }

    static function DBNextval($db, $sequence) {
        $ResultSet = $db->queryRiga("SELECT {$db->nextval($sequence)} AS nextval");
        return $ResultSet['nextval'];
    }

    static function DBLastId($db) {
        return $db->getLastId();
    }

    static function DBSelect($db, $table, $whereString = '', $flMultipla = true) {
        if ($flMultipla) {
            $ResultSet = $db->queryMultipla("SELECT * FROM $table $whereString");
        } else {
            $ResultSet = $db->queryRiga("SELECT * FROM $table $whereString");
        }
        return $ResultSet;
    }

    static function DBSQLCount($db, $sqlString) {
        $ResultSet = $db->queryCount($sqlString);
        return $ResultSet;
    }

    /**
     *  Esegue una SELECT nel database
     * @param Object $db            Istanza del database
     * @param String $sqlString     Stringa per query sql
     * @param Boolean $flMultipla   Se true restituisce un array a due livelli, primo livello = record, secondo livello = campi-, se false restituisce solo un array di campi
     * @param Stringa $da           Limite inferiore numero record
     * @param Stringa $per          Limite superiore numero record
     * @return                      Restituisce dati della query in un array associativo
     */
    static function DBSQLSelect($db, $sqlString, $flMultipla = true, $da = '', $per = '') {
        if ($da != '' && $da > 0) {
            $da = $da - 1;
        }
        if ($flMultipla) {
            $ResultSet = $db->queryMultipla($sqlString, $da, $per);
        } else {
            $ResultSet = $db->queryRiga($sqlString);
        }
        return $ResultSet;
    }

    static function DBSetCustomAttribute($db, $attr_key, $attr_value) {
        return false;
    }

    public static function DBSetUseBufferedQuery($db, $use = true) {
        return false;
    }

    static function DBQueryPrepare($db, $sql, $params = array(), $multipla = false, &$infoBinaryFields = array(), $da = '', $per = '') {
        return false;
    }

    static function DBQueryFetch($db, $statement, $multipla = false, $infoBinaryCallback = array(), $infoBinaryFields = array(), $indiciNumerici = false) {
        return false;
    }

    static function DBQueryRemove($db, $statement, $closeConnection = false) {
        return false;
    }

    /**
     *  Esegue l'istruzione SQL di INSERT
     * @param Object $db        Istanza del database
     * @param String $table     Tabella dove si inserira il record
     * @param type $primaryKey
     * @param Array $rec_arr    Array con i dati/record da inserire
     * @return type 
     */
    static function DBInsert($db, $table, $primaryKey, $rec_arr) {
        if (is_string($table)) {
            $table_name = $table;
        } else {
            $table_name = $table->getName();
        }
        foreach ($rec_arr as $field => $value) {
            if ($field != $primaryKey) {
                $campi[] = $field;
                if (is_string($table)) {
                    /*
                     * Controllo con !== per correttezza e non perdere valori 0
                     * 23/02/2016
                     */
                    //if ($value != '') {
                    if ($value !== '') {
                        $valori[] = "'" . $db->quote($value) . "'";
                    } else {
                        $valori[] = "''";
                    }
                } else {
                    $valori[] = $table->getNormalizedValue($field, $value);
                }
            }
        }
        $sql_string = "INSERT INTO " . $table_name . "(" . implode(',', $campi) . ") VALUES (" . implode(",", $valori) . ")";
        $nRows = $db->update($sql_string);
        return $nRows;
    }

    /**
     *   Esegue l'istruzione SQL di UPDATE
     * @param Object $db         Istanza del database
     * @param String $table      Tabella di riferimento
     * @param type $primaryKey   Chiave primaria
     * @param Array $rec_arr     Array da utilizzare come riferimento per le modifiche
     * @return Int               Numero di righe modificate 
     */
    static function DBUpdate($db, $table, $primaryKey, $rec_arr) {
        foreach ($rec_arr as $field => $value) {
            if ($field != $primaryKey) {
                /*
                 * Controllo con === per correttezza e non perdere valori 0
                 * 23/02/2016
                 */
                //if ($value == '') {
                if ($value === '') {
                    $campi[$field] = "$field=''";
                } else {
                    $campi[$field] = "$field='" . $db->quote($value) . "'";
                }
            }
        }
        $sql_string = "UPDATE " . $table . " SET " . implode(',', $campi) . " WHERE " . $primaryKey . "='" . $rec_arr[$primaryKey] . "'";
        $nRow = $db->update($sql_string);
        return $nRow;
    }

    static function DBDelete($db, $table, $primaryKey, $primaryVal) {
        if (!$primaryKey) {
            return 0;
        }
        if ($primaryVal == '*') {
            return 0;
        }
        if (!$primaryVal) {
            return 0;
        }

        $sql_string = "DELETE FROM " . $table . " WHERE " . $primaryKey . "='" . $db->quote($primaryVal) . "'";
        $nRows = $db->update($sql_string);
        return $nRows;
    }

    /**
     *  Esegue una istruzione SQL
     * @param Object $db         Istanza del database
     * @param String $sqlString  Stringa SQL da eseguire
     * @return type 
     */
    static function DBSQLExec($db, $sqlString) {
        return $db->query($sqlString);
    }

    /**
     * 
     * @param type itadb $db Oggetto db di riferimento
     * @param type string $table Tabella di riferimento
     * @param type mixed $Record id record di riferimento
     * @param type string $mode unused
     * @param type int $wait tempo di attesa se refereza già in lock (secondi)
     * @param type int $duration tempo di vita della lock prima che diventi una dead lock
     * @return type boolean 
     */
    static function DBLock($db, $table, $Record, $mode = "", $wait = 0, $duration = 300) {
        $entryTime = time();
        $currTime = $entryTime;
        $exitTime = $entryTime + $wait;
        $interval = 200000;
        $ret = array();
        $Locktab_rec = array();
        $Locktab_rec['LOCKRECID'] = $db->getDB() . "." . $table . "." . $Record;
        $Locktab_rec['LOCKTOKEN'] = App::$utente->getKey('TOKEN');
        $Locktab_rec['LOCKTIME'] = time();
        $Locktab_rec['LOCKEXP'] = $Locktab_rec['LOCKTIME'] + $duration;
        try {
            $nRows = self::DBInsert(App::$itaEngineDB, 'LOCKTAB', 'ROWID', $Locktab_rec);
        } catch (Exception $e) {
            Out::msgStop("Errore in Inserimento", $e->getMessage(), '600', '600');
            $ret['status'] = -2;
            return $ret;
        }
        $LastID = self::DBLastId(App::$itaEngineDB);

        while (true) {
            $Locktab_rec = array();
            $Locktab_tab = array();
            $sql = "SELECT * FROM LOCKTAB
                    WHERE LOCKRECID = '" . $db->getDB() . "." . $table . "." . $Record . "'
                    AND LOCKTOKEN <> '" . App::$utente->getKey('TOKEN') . "'
                    AND LOCKEXP >= " . time() . "
                    AND ROWID < " . $LastID . "
                    ORDER BY ROWID";

            $Locktab_tab = ItaDB::DBSQLSelect(App::$itaEngineDB, $sql, true);

            if (!$Locktab_tab) {
                $ret['status'] = 0;
                $ret['lockID'] = $LastID;
                return $ret;
            } else {
                if ($exitTime <= time() || $exitTime == $entryTime) {
                    $retUnlock = self::DBUnLock($LastID);
                    $ret['status'] = -1;
                    $ret['token'] = $Locktab_tab[0]['LOCKTOKEN'];
                    return $ret;
                } else {
                    usleep($interval);
                    if ($interval <= 1000000) {
                        $interval = $interval * 2;
                    }
                }
            }
        }
    }

    static function DBUnLock($lockID) {
        App::log($lockID);
        $ret = array();
        try {
            $nRows = ItaDB::DBDelete(App::$itaEngineDB, 'LOCKTAB', 'ROWID', $lockID);
            $ret['status'] = 0;
        } catch (Exception $e) {
            Out::msgStop("Errore in Cancellazione", $e->getMessage(), '600', '600');
            $ret['status'] = -1;
        }
        return $ret;
    }

    static function DBUnLockForSession() {
        $ret = array();
        $ret['status'] = 0;
        $sql = "SELECT * FROM LOCKTAB WHERE LOCKTOKEN = '" . App::$utente->getKey('TOKEN') . "'";
        $Locktab_tab = ItaDB::DBSQLSelect(App::$itaEngineDB, $sql, true);
        if ($Locktab_tab) {
            foreach ($Locktab_tab as $Locktab_rec) {
                try {
                    $nRows = ItaDB::DBDelete(App::$itaEngineDB, 'LOCKTAB', 'ROWID', $Locktab_rec['ROWID']);
                } catch (Exception $e) {
                    $ret['status'] = -1;
                }
            }
        }
        return $ret;
    }

    static function DBGetTableObject($db, $table) {
        return $db->getTableObject($table);
    }

}
