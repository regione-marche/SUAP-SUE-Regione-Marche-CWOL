<?php

require_once ITA_LIB_PATH . '/DBPDO/ItaDBError.class.php';
require_once ITA_LIB_PATH . '/DBPDO/PDOManager.class.php';
require_once ITA_LIB_PATH . '/DBPDO/PDODriver.class.php';
require_once ITA_LIB_PATH . '/DBPDO/PDOTableDef.class.php';
require_once ITA_LIB_PATH . '/DBPDO/PDOHelper.class.php';
require_once ITA_LIB_PATH . '/DBPDO/PDOTableDefValidator.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaModelValidator.class.php';
require_once ITA_LIB_PATH . '/Cache/CacheFactory.class.php';

/**
 * Facade per interfaccia con DB tramite PDO
 *
 * @author Massimo Biagioli
 */
class PDOFacade {

    /**
     * Apri DataBase
     *
     * @param string $dbName Nome data base
     * @param string $dbSuffix Suffisso opzionale al Data base
     * @param array $dbParm Array parametri
     * @param string $connectionName Nome connessione (se valorizzato, gestisce una sessione a parte)
     */
    static function DBOpen($dbName, $dbSuffix = 'ditta', $dbParm = null, $connectionName = '') {
        // Se la funzione DBOpen viene invocata tramite PDO, $dbParm viene letto da quest'ultimo
        if ($dbParm === null) {
            $dbParm = ItaDB::listConnections();
        }

        $dbBaseName = $dbName;
        if (isSet($dbParm[$dbBaseName]['connection_template'])) {
            $connectionName = $dbParm[$dbBaseName]['connection_template'];
        }
        if (!isSet($connectionName) || trim($connectionName) == '') {
            $connectionName = $dbBaseName;
        }

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
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'dbms non definito');
        }

        $newlink = (key_exists('newlink', $dbParm[$dbBaseName]) == true) ? $dbParm[$dbBaseName]['newlink'] : 0;
        $host = (key_exists('host', $dbParm[$dbBaseName]) == true) ? $dbParm[$dbBaseName]['host'] : '';
        $user = $dbParm[$dbBaseName]['user'];
        $pwd = $dbParm[$dbBaseName]['pwd'];
        try {
            $db = new PDOManager(array('host' => $host, 'dbms' => $dbms, 'db' => $dbRealName, 'user' => $user, 'pwd' => $pwd, 'newlink' => $newlink, 'connectionName' => $connectionName), $dbParm[$dbBaseName]);
        } catch (ItaException $ex) {
            throw $ex;
        }

        return $db;
    }

    static function DBNextval($db, $sequence) {
        $ResultSet = $db->queryRiga("SELECT {$db->nextval($sequence)} ", array());
        return $ResultSet['NEXTVAL'];
    }

    static function DBLastId($db) {
        return $db->getLastId();
    }

    static function DBSelect($db, $table, $whereString = '', $flMultipla = true) {
        if ($flMultipla) {
            $ResultSet = $db->queryMultipla("SELECT * FROM $table $whereString");
        } else {
            $ResultSet = $db->queryRiga("SELECT * FROM $table $whereString", array(), true, false);
        }
        return $ResultSet;
    }

    static function getSqlAlias($db, $sql, $tableName) {
        return $db->getSqlAlias("$sql", $tableName);
    }

    /**
     * Esegue una SELECT nel database
     * @param Object $db            Istanza del database
     * @param String $sqlString     Stringa per query sql
     * @param Boolean $flMultipla   Se true restituisce un array a due livelli, primo livello = record, secondo livello = campi-, se false restituisce solo un array di campi
     * @param Stringa $da           Limite inferiore numero record
     * @param Stringa $per          Limite superiore numero record
     * @param Array $params         parametri da passare alla where  
     * @return                      Restituisce dati della query in un array associativo
     */
    static function DBSQLSelect($db, $sqlString, $flMultipla = true, $da = '', $per = '', $params = array()) {
        if ($da != '' && $da > 0) {
            $da = $da - 1;
        }
        if ($flMultipla) {
            $ResultSet = $db->queryMultipla($sqlString, $da, $per, false, $params);
        } else {
            $ResultSet = $db->queryRiga($sqlString, $params);
        }
        return $ResultSet;
    }

    static function DBSQLCount($db, $sqlString, $params = array()) {
        $ResultSet = $db->queryCount($sqlString, false, $params);
        return $ResultSet;
    }

    static function DBSetCustomAttribute($db, $attr_key, $attr_value) {
        $db->setCustomAttribute($attr_key, $attr_value);
    }

    public static function DBSetUseBufferedQuery($db, $use = true) {
        $db->setUseBufferedQuery($use);
    }

    static function DBQueryPrepare($db, $sql, $params = array(), $multipla = false, &$infoBinaryFields = array(), $da = '', $per = '') {
        return $db->queryPrepare($sql, $params, $infoBinaryFields, $multipla, $da, $per);
    }

    static function DBQueryFetch($db, $statement, $multipla = false, $infoBinaryCallback = array(), $infoBinaryFields = array(), $indiciNumerici = false) {
        return $db->queryFetch($statement, $infoBinaryCallback, $infoBinaryFields, $indiciNumerici, $multipla);
    }

    static function DBQueryRemove($db, $statement, $closeConnection = false) {
        $db->queryRemove($statement, $closeConnection);
    }

    static function DBQuery($db, $sqlString, $flMultipla = true, $params = array(), $infoBinaryCallback = array(), $fieldsBinary = array()) {
        if ($flMultipla) {
            $ResultSet = $db->queryMultipla($sqlString, '', '', false, $params, $infoBinaryCallback, $fieldsBinary);
        } else {
            $ResultSet = $db->queryRiga($sqlString, $params, $infoBinaryCallback, $fieldsBinary);
        }
        return $ResultSet;
    }

    /**
     * Esegue l'istruzione SQL di INSERT
     * @param Object $db        Istanza del database
     * @param String $table     Tabella dove si inserira il record
     * @param Array $rec_arr    Array con i dati/record da inserire
     * @return type 
     */
    static function DBInsert($db, $table, $rec_arr) {
        if (is_string($table)) {
            $ObjTable = ItaDB::getTableDef($db, $table, $cacheKey, $cacheValue);
        } else {
            $ObjTable = $table;
        }
        return $db->dbInsert($ObjTable, $rec_arr);
    }

    /**
     * Esegue l'istruzione SQL di UPDATE
     * @param Object $db Istanza del database
     * @param String $table Tabella di riferimento
     * @param Array $rec_arr Array da utilizzare come riferimento per le modifiche
     * @param array $oldRecord Array contenenti i dati del record non modificati dalla pagina   
     * @return Int Numero di righe modificate 
     */
    static function DBUpdate($db, $table, $rec_arr, $oldRecord = array()) {
        if (is_string($table)) {
            $ObjTable = ItaDB::getTableDef($db, $table, $cacheKey, $cacheValue);
        } else {
            $ObjTable = $table;
        }
        return $db->dbUpdate($ObjTable, $rec_arr, $oldRecord);
    }

    /**
     * Esegue l'istruzione SQL di DELETE
     * @param Object $db         Istanza del database
     * @param String $table      Tabella di riferimento     
     * @param Array $primaryVal    Valore della chiave/Array con i valori della chiave
     * @return Int               Numero di righe cancellate 
     */
    static function DBDelete($db, $table, $primaryVal) {
        if (is_string($table)) {
            $ObjTable = ItaDB::getTableDef($db, $table, $cacheKey, $cacheValue);
        } else {
            $ObjTable = $table;
        }
        return $db->dbDelete($ObjTable, $primaryVal);
    }

    /*
     * Sostutuito getTableDef
     */

    static function DBGetTableObject($db, $table) {
        return ItaDB::getTableDef($db, $table, $cacheKey, $cacheValue);
    }

    static function DBSQLExec($db, $sqlString) {
        return $db->query($sqlString, false, array());
    }

    static function DBBeginTransaction($db, $isolation = 1, $manual = false, $timeout = null) {
        return $db->beginTransaction($isolation, $manual, $timeout);
    }

    static function DBCommitTransaction($db, $skip = false, $manual = false) {
        return $db->commitTransaction($skip, $manual);
    }

    static function DBRollbackTransaction($db, $skip = false, $manual = false) {
        return $db->rollbackTransaction($skip, $manual);
    }

    static function DBCountOpenTransaction($db) {
        return $db->countOpenTransaction();
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
            $nRows = self::DBInsert(App::$itaEngineDB, 'LOCKTAB', $Locktab_rec);
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
            $nRows = self::DBDelete(App::$itaEngineDB, 'LOCKTAB', array('ROWID' => $lockID));
            $ret['status'] = 0;
        } catch (Exception $e) {
            Out::msgStop("Errore in Cancellazione", $e->getMessage(), '600', '600');
            $ret['status'] = -1;
        }
        return $ret;
    }

    /*
     * EFfettua il lock di un intera tabella 
     * @param Object $db        Istanza del database
     * @param String $table     Tabella di cui effettuare il blocco 
     * @param type $exclusive True = Non permette la lettura , false = Permette la lettura
     * @return bool Esisto dell'operazione 
     */

    static function DBLockTable($db, $table, $exclusive = false) {
        return $db->lockTable($table, $exclusive);
    }

    /**
     * EFfettua il lock di un record di una tabella 
     * @param Object $db        Istanza del database
     * @param String $table     Tabella di cui effettuare il blocco 
     * @param array $params Filtri a appllicare alla select 
     * @return bool Esisto dell'operazione 
     */
    static function DBLockRowTable($db, $table, $params = array()) {
        return $db->lockRowTable($table, $params);
    }

}
