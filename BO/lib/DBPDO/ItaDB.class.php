<?php

require_once ITA_LIB_PATH . '/itaException/ItaException.php';
require_once ITA_LIB_PATH . '/DBPDO/PDOFacade.class.php';
require_once ITA_LIB_PATH . '/DBPDO/ItaDBFacade.class.php';
require_once ITA_LIB_PATH . '/itaPHPLocker/itaLockerFactory.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaConnectionsPerRequest.class.php';
require_once ITA_BASE_PATH . '/lib/Cache/CacheFactory.class.php';

/**
 * Classe Proxy per accesso al database
 *
 * @author Massimo Biagioli
 */
class ItaDB {

    private $drivertype = array();
    public static $connectionPerRequest;

    private function __construct() {
        
    }

    /**
     * Get singleton object
     * @staticvar type $instance
     * @return \ItaDBProxy
     */
    public static function getInstance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new ItaDB();
        }
        return $instance;
    }

    public static function DBOpen($dbName, $dbSuffix = 'ditta', $connectionName = '') {
        $dbParm = self::listConnections();

        $driverType = array_key_exists('drivertype', $dbParm[$dbName]) ? $dbParm[$dbName]['drivertype'] : '';
        ItaDB::getInstance()->setDriverType($dbName, $driverType);

        if ($driverType === 'PDO') {
            return PDOFacade::DBOpen($dbName, $dbSuffix, $dbParm, $connectionName);
        } else {
            return ItaDBFacade::DBOpen($dbName, $dbSuffix, $dbParm);
        }
    }

    public static function DBNextval($db, $sequence) {
        if (self::usePDO($db)) {
            return PDOFacade::DBNextval($db, $sequence);
        } else {
            return ItaDBFacade::DBNextval($db, $sequence);
        }
    }

    public static function DBLastId($db) {
        if (self::usePDO($db)) {
            return PDOFacade::DBLastId($db);
        } else {
            return ItaDBFacade::DBLastId($db);
        }
    }

    public static function DBSelect($db, $table, $whereString = '', $flMultipla = true) {
        if (self::usePDO($db)) {
            return PDOFacade::DBSelect($db, $table, $whereString, $flMultipla);
        } else {
            return ItaDBFacade::DBSelect($db, $table, $whereString, $flMultipla);
        }
    }

    public static function DBSQLCount($db, $sqlString, $params = array()) {
        if (self::usePDO($db)) {
            return PDOFacade::DBSQLCount($db, $sqlString, $params);
        } else {
            if (isSet($params) && !empty($params)) {
                $search = array();
                $replace = array();
                foreach ($params as $key => $value) {
                    if (is_string($value)) {
                        $value = "'$value'";
                    }
                    $search[] = ':' . $key;
                    $replace[] = $value;
                }
                $sqlString = str_replace($search, $replace, $sqlString);
            }
            return ItaDBFacade::DBSQLCount($db, $sqlString);
        }
    }

    public static function DBSQLSelect($db, $sqlString, $flMultipla = true, $da = '', $per = '', $params = array()) {
        if (self::usePDO($db)) {
            return PDOFacade::DBSQLSelect($db, $sqlString, $flMultipla, $da, $per, $params);
        } else {
            if (isSet($params) && !empty($params)) {
                $search = array();
                $replace = array();
                foreach ($params as $value) {
                    if (is_string($value['value'])) {
                        $value['value'] = "'{$value['value']}'";
                    }
                    $search[] = ':' . $value['name'];
                    $replace[] = $value['value'];
                }
                $sqlString = str_replace($search, $replace, $sqlString);
            }
            return ItaDBFacade::DBSQLSelect($db, $sqlString, $flMultipla, $da, $per);
        }
    }

    public static function DBSetCustomAttribute($db, $attr_key, $attr_value) {
        if (self::usePDO($db)) {
            PDOFacade::DBSetCustomAttribute($db, $attr_key, $attr_value);
        } else {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo DBSetCustomAttribute non supportato');
        }
    }

    public static function DBSetUseBufferedQuery($db, $use = true) {
        if (self::usePDO($db)) {
            PDOFacade::DBSetUseBufferedQuery($db, $use);
        } else {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo DBSetUseBufferedQuery non supportato');
        }
    }

    public static function DBQueryPrepare($db, $sql, $params = array(), $multipla = false, &$infoBinaryFields = array(), $da = '', $per = '') {
        if (self::usePDO($db)) {
            return PDOFacade::DBQueryPrepare($db, $sql, $params, $multipla, $infoBinaryFields, $da, $per);
        } else {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo DBQueryPrepare non supportato');
        }
    }

    public static function DBQueryFetch($db, $statement, $multipla = false, $infoBinaryCallback = array(), $infoBinaryFields = array(), $indiciNumerici = false) {
        if (self::usePDO($db)) {
            return PDOFacade::DBQueryFetch($db, $statement, $multipla, $infoBinaryCallback, $infoBinaryFields, $indiciNumerici);
        } else {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo DBQueryFetch non supportato');
        }
    }

    public static function DBQueryRemove($db, $statement, $closeConnection = false) {
        if (self::usePDO($db)) {
            return PDOFacade::DBQueryRemove($db, $statement, $closeConnection = false);
        } else {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo DBQueryRemove non supportato');
        }
    }

    public static function DBQuery($db, $sqlString, $flMultipla = true, $params = array(), $infoBinaryCallback = array(), $fieldsBinary = array()) {
        if (self::usePDO($db)) {
            return PDOFacade::DBQuery($db, $sqlString, $flMultipla, $params, $infoBinaryCallback, $fieldsBinary);
        } else {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo DBQuery non supportato');
        }
    }

    public static function DBInsert($db, $table, $primaryKey, $rec_arr) {
        if (self::usePDO($db)) {
            return PDOFacade::DBInsert($db, $table, $rec_arr);
        } else {
            return ItaDBFacade::DBInsert($db, $table, $primaryKey, $rec_arr);
        }
    }

    public static function DBUpdate($db, $table, $primaryKey, $rec_arr, $oldRecord = array()) {
        if (self::usePDO($db)) {
            return PDOFacade::DBUpdate($db, $table, $rec_arr, $oldRecord);
        } else {
            return ItaDBFacade::DBUpdate($db, $table, $primaryKey, $rec_arr);
        }
    }

    public static function DBDelete($db, $table, $primaryKey, $primaryVal) {
        if (self::usePDO($db)) {
            if (is_string($primaryVal)) {
                $dataToDelete = array(
                    $primaryKey => $primaryVal
                );
            } else {
                $dataToDelete = $primaryVal;
            }
            return PDOFacade::DBDelete($db, $table, $dataToDelete);
        } else {
            return ItaDBFacade::DBDelete($db, $table, $primaryKey, $primaryVal);
        }
    }

    public static function DBGetTableObject($db, $table) {
        if (self::usePDO($db)) {
            return PDOFacade::DBGetTableObject($db, $table);
        } else {
            return ItaDBFacade::DBGetTableObject($db, $table);
        }
    }

    public static function DBSQLExec($db, $sqlString) {
        if (self::usePDO($db)) {
            return PDOFacade::DBSQLExec($db, $sqlString);
        } else {
            return ItaDBFacade::DBSQLExec($db, $sqlString);
        }
    }

    public static function DBLock($db, $table, $Record, $mode = "", $wait = 0, $duration = 1800) {
        $sqlParams = $db->getSqlparams();
        $locker = itaLockerFactory::getLockerManager(isset($sqlParams['recordLock']) ? $sqlParams['recordLock'] : null);
        return $locker->lock($db, $table, $Record, $mode, $wait, $duration);
    }

    public static function DBUnLock($lockID, $db = null) {
        if ($db != null) {
            $sqlParams = $db->getSqlparams();
        }
        $locker = itaLockerFactory::getLockerManager($sqlParams['recordLock']);
        return $locker->unlock($lockID, $db);
    }

    public static function DBUnLockForSession() {
        $lockerDefault = itaLockerFactory::getLockerManager('default');
        $lockerDefault->unlockForSession();

        if (itaHooks::isActive('citywareHook.php')) {
            $lockerCityware = itaLockerFactory::getLockerManager('cityware');
            $lockerCityware->unlockForSession();
        }
    }

    // se passo $manual in fase di apertura della connessione evito di fare la commit o rollback fino a quando nn mi passi 
    public static function DBBeginTransaction($db, $isolation = 1, $manual = false, $timeout = null) {
        if (self::usePDO($db)) {
            return PDOFacade::DBBeginTransaction($db, $isolation, $manual, $timeout);
        } else {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo DBBeginTransaction non supportato');
        }
    }

    // se passo $manual effettua la commit manule de.lla transazione 
    public static function DBCommitTransaction($db, $skip = 0, $manual = false) {
        if (self::usePDO($db)) {
            return PDOFacade::DBCommitTransaction($db, $skip, $manual);
        } else {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo DBCommitTransaction non supportato');
        }
    }

    // se passo $manual effettua la rollback manule della transazione 
    public static function DBRollbackTransaction($db, $skip = false, $manual = false) {
        if (self::usePDO($db)) {
            return PDOFacade::DBRollbackTransaction($db, $skip, $manual);
        } else {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo DBRollbackTransaction non supportato');
        }
    }

    public static function DBCountOpenTransaction($db) {
        if (self::usePDO($db)) {
            return PDOFacade::DBCountOpenTransaction($db);
        } else {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo DBCountOpenTransaction non supportato');
        }
    }

    // se passo $manual in fase di apertura della connessione evito di fare la commit o rollback fino a quando nn mi passi 
    public static function DBLockTable($db, $table, $exclusive = false) {
        if (self::usePDO($db)) {
            return PDOFacade::DBLockTable($db, $table, $exclusive);
        } else {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo DBLockTable non supportato ');
        }
    }

    public static function DBLockRowTable($db, $table, $params = array()) {
        if (self::usePDO($db)) {
            return PDOFacade::DBLockRowTable($db, $table, $params);
        } else {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Metodo DBLockTable non supportato ');
        }
    }

    public static function usePDO($db) {
        $sqlParams = $db->getSqlparams();
        return (isSet($sqlParams['drivertype']) && $sqlParams['drivertype'] === 'PDO');
    }

    public function getDriverType($dbName) {
        return $this->drivertype[$dbName];
    }

    public function setDriverType($dbName, $drivertype) {
        $this->drivertype[$dbName] = $drivertype;
    }

    public static function getTableDef($db, $table, &$cacheKeyRet=null, &$cacheValueRet=null) {
        $cache = CacheFactory::newCache();

        $cacheKey = 'PDOTableDef_' . $db->getDB() . ':' . $db->getConnectionName() . '_' . $table;
        $cacheValue = $cache->get($cacheKey);
        if (!$cacheValue) {
            $tableDef = new PDOTableDef($table, $db->getColumnsInfo($table), $db->getPKs($table), $db->getTableInfo($table));
            $cacheValue = array(
                'fields' => $tableDef->getFields(),
                'pks' => $tableDef->getPks(),
                'tableInfo' => $tableDef->getTableInfo()
            );
            /*
             *  Rinfresca oggetto in cache
             */
            $cache->set($cacheKey, $cacheValue, (3600 * 24));
        } else {
            $tableDef = new PDOTableDef($table, $cacheValue['fields'], $cacheValue['pks'], $cacheValue['tableInfo']);
        }
        $cacheKeyRet = $cacheKey;
        $cacheValueRet = $cacheValue;

        return $tableDef;
    }

    /*     * *******************************
     * GESTIONE CONNESSIONI
     * ******************************* */

    /**
     * Inizializzazione ConnectionPerRequest
     */
    public static function initConnectionPerRequest() {
        self::$connectionPerRequest = new itaConnectionsPerRequest();
    }

    /**
     * Flush ConnectionPerRequest
     */
    public static function flushConnectionPerRequest() {
        $connections = ItaDB::$connectionPerRequest->getConnections();

        foreach ($connections as $key => $connection) {

            //Controlla se esistono transazioni aperti per le connessioni
            $transactionInfo = $connection['transactionInfo'];
            if ($connection['transactionInfo'] != null && empty($transactionInfo) == false) {
                if ($transactionInfo['transactions'] === 1) {
                    $message = "Errore mancata chiusura tranzazione aperta per la sessione:" . $key;
                    App::log($message);
                }
            }
        }
    }

    /**
     * Elenco connessioni
     * @param string $connectionsPathFileName Nome file connessioni
     * @return array Lista connessioni
     */
    public static function listConnections($connectionsPathFileName = 'connections.ini') {
        $connections = parse_ini_file(ITA_CONFIG_PATH . '/' . $connectionsPathFileName, true);

        // Filtra connessioni di template
        $connectionTemplates = array_filter($connections, array(__CLASS__, 'filterConnectionTemplate'));
        $realConnections = array_diff_assoc($connections, $connectionTemplates);

        // Scorre le connessioni che non sono template
        // Per ognuna di esse, quando puntano a connection_template, reperisce le informazioni da quest'ultimo
        // in modo da restituire una struttura dati retrocompatibile
        foreach ($realConnections as $connectionName => $connectionAttrs) {
            if (isset($connectionAttrs['connection_template'])) {
                $connectionTemplate = $connectionTemplates[$connectionAttrs['connection_template']];
                foreach ($connectionTemplate as $k => $v) {
                    if ($k !== 'type') {
                        $realConnections[$connectionName][$k] = $v;
                    }
                }
            }
        }

        return $realConnections;
    }

    private static function filterConnectionTemplate($item) {
        return isset($item['type']) && $item['type'] === 'connection_template';
    }

}

?>
