<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbDBRequest.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';

/**
 *
 * Utility DB Cityware - Superclasse
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Massimo Biagioli <m.biagioli@palinformatica.it>
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
abstract class cwbLibDB_CITYWARE {

    const CONNECTION_NAME = 'CITYWARE';
    const CONNECTION_NAME_NUMERATORI = 'CITYWARE_NUMERATORI';
    const CONNECTION_NAME_LOCK = 'CITYWARE_LOCKER';

    private $CITYWARE_DB;
    private $errCode;
    private $errMsg;
    private static $ente;
    
    public function __construct($db=null, $ditta='ditta') {
        if(!empty($db)){
            if(is_string($db)){
                $this->setCitywareDB(ItaDB::DBOpen($db, $ditta));
            }
            elseif($db instanceof PDOManager){
                $this->setCitywareDB($db);
            }
        }
    }

    public function __call($name, $arguments) {

        // Filtra metodo (considera solamente quelli con la forma leggiXXXChiave)
        $matches = array();
        if (preg_match('/leggi(.*?)Chiave/', $name, $matches)) {
            // Estrae nome tabella dal metodo        
            $tableName = strtoupper(substr($matches[1], 0, 3) . '_' . substr($matches[1], 3));

            // Prepara array delle chiavi da passare al metodo getByPks
            $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableName));
            $tableDef = $modelService->newTableDef($tableName, $this->getCitywareDB());
            $pks = $tableDef->getPks();
            $values = array();
            foreach ($pks as $idx => $pk) {
                $values[$pk] = $arguments[$idx];
            }

            // Carica record per chiave
            return $modelService->getByPks($this->getCitywareDB(), $tableName, $values);
        } elseif (preg_match('/leggi([A-Z][a-z]{2}[A-Z][a-z]*$)/', $name, $matches) || preg_match('/leggi([A-Za-z0-9_]*)Qry$/', $name, $matches) || preg_match('/leggi([A-Z][a-z]{2}[A-Z][a-z]*_?V[0-9]{2}$)/', $name, $matches)) {
            $tableName = strtoupper(substr($matches[1], 0, 3) . '_' . substr($matches[1], 3));
            if (preg_match('/[A-Z](V[0-9]*$)/', $tableName) !== false) {
                $tableName = substr($tableName, 0, strlen($tableName) - 3) . '_' . substr($tableName, -3);
            }

            $filtri = ((isSet($arguments[0]) && is_array($arguments[0])) ? $arguments[0] : null);
            $multipla = ((isSet($arguments[1]) && $arguments[1] == false) ? false : true);

            return $this->leggiGeneric($tableName, $filtri, $multipla);
        } else {
            return;
        }
    }

    /**
     * Restituisce l'SQL e relativo array dei parametri in forma generale. Solo per query secca su table
     * @param <string> $table nome della tabella
     * @param <array> $filters Vedi definizione fields della funzione cwbLibDB_CITYWARE->setDefaultFilters
     * @param <array> $sqlParams sqlParams (reference)
     * @param <string> $select parte select della query (di default viene inserito *)
     * @param <string> $orderBy parte order by della query (di default non viene fatto l'order by)
     * @param <string> $groupBy parte group by della query (di default non viene fatto il group by)
     * @param <string> $from parte from della query (di default viene usata la tabella principale)
     * @return <string>
     */
    public function getSqlGeneric($table = null, $filters, &$sqlParams = array(), $select = '*', $orderBy = '', $groupBy = '', $from = '') {
        $sql = 'SELECT ' . $select . ' FROM ';
        $sql .= !empty($from) ? $from : $table;
        $sqlFilters = $this->setDefaultFilters($table, $filters, $sqlParams);
        if (trim($sqlFilters) != '') {
            $sql .= ' WHERE ' . $sqlFilters;
        }
        if (!empty($groupBy)) {
            $sql .= ' GROUP BY ';
            if (is_string($groupBy)) {
                $sql .= $groupBy;
            } elseif (is_array($groupBy)) {
                $groupString = array();
                foreach ($groupBy as $value) {
                    if(strpos($value, '.') !== false){
                        $groupString[] = $value;
                    }
                    else{
                        $groupString[] = $table . '.' . $value;
                    }
                }
                $sql .= implode(', ', $groupString);
            }
        }
        if (!empty($orderBy)) {
            $sql .= ' ORDER BY ';
            if (is_string($orderBy)) {
                $sql .= $orderBy;
            } elseif (is_array($orderBy)) {
                $orderString = array();
                foreach ($orderBy as $key => $value) {
                    if (stripos($value, 'ASC') !== false || stripos($value, 'DESC') !== false) {
                        if(strpos($key, '.') !== false){
                            $orderString[] = $key . ' ' . $value;
                        }
                        else{
                            $orderString[] = $table . '.' . $key . ' ' . $value;
                        }
                    } else {
                        if(strpos($value, '.') !== false){
                            $orderString[] = $table . '.' . $value;
                        }
                        else{
                            $orderString[] = $value;
                        }
                    }
                }
                $sql .= implode(', ', $orderString);
            }
        }
        return $sql;
    }

    /**
     * Restituisce i dati da una tabella generica
     * @param <string> $table nome della tabella
     * @param <array> $filters Vedi definizione fields della funzione cwbLibDB_CITYWARE->setDefaultFilters
     * @param <boolean> $multipla Se true restituisce più risultati in un array, sennò una sola riga
     * @param <string> $select
     * @param <string> $orderBy
     * @return <array>
     */
    public function leggiGeneric($table, $filters, $multipla = true, $select = '*', $orderBy = '', $groupBy = '', $da = '', $per = '', $from = '') {
        return ItaDB::DBSQLSelect($this->getCitywareDB(), $this->getSqlGeneric($table, $filters, $sqlParams, $select, $orderBy, $groupBy, $from), $multipla, $da, $per, $sqlParams);
    }

    /**
     * Crea l'sql per la WHERE e aggiunge i parametri della query in maniera automatica
     * @param <string> $table Nome della table su cui fare la query (opzionale)
     * @param <array> $fields Array dei parametri. L'array è in forma chiave=>valore
     *                          chiave è nella forma: NOME_CAMPO<_modificatore>*. Il nome del campo ammette solo [A-Z0-9_].
     *                          I possibili modificatori sono:
     *                              _and | _or: Indica se il campo va messo in AND o OR con il campo precedente. Se non viene specificato viene
     *                                          considerato _and.
     * 
     *                              _like | _notLike | _diff | _gt | _lt | _eq | _null | _notNull: Indica che il campo va ricercato come like, come differente, come maggiore di,
     *                                                               come minore di o come uguale al valore passato. _eq è utilizzabile insieme a
     *                                                               _gt e _lt. Se non passato viene considerato _eq. _null e _notNull indicano rispettivamente IS NULL e IS NOT NULL
     * 
     *                              _jollyBegin | _jollyEnd | _jollyNone: Solo se si usa _like: verrà ricercato con % all'inizio o alla fine fine del valore,
     *                                                       se non viene specificato nulla viene considerato % sia all'inizio che alla fine.
     * 
     *                              _arrand | _arror: Solo se il valore è un array, considera se mettere i valori dell'array in and o in or fra loro.
     *                                                Il valore di default varia a seconda che si stia cercando _eq, _diff, _like, etc.
     * 
     *                              _upper: Rende la ricerca case insensitive.
     * 
     *                              _ignoreEmpty: se il filtro ha un valore empty($filtro) = true viene ignorato.
     *                          E' possibile applicare una condizione grezza con la sintassi RAWCONDITION(n) => 'string', dove n rappresenta un identificativo univoco
     *                          di una rawcondition e 'string' è la condizione che viene incollata nell'sql. RAWCONDITION accetta i modificatori _and e _or
     *                          E' possibile usare, in una struttura ricorsiva, la sintassi GROUP(n) => array(), dove n rappresenta un identificativo univoco
     *                          di un raggruppamento e array() contiene una lista di filtri. n deve cominciare con una lettera
     * @param <string|array> $sqlParams Valore da cercare, se si passa un array verranno cercati tutti i valori in and o in or a seconda del modificatore
     *                                  _arror e _arrend
     * @return <string> Stringa sql del where generata.
     * @throws <itaException> In caso si stia passando un campo non valido.
     * 
     * ********************************************************************
     * Esempio d'uso:
     * ********************************************************************
     * $table = 'FES_IMP';
     * $fields = array(
     *      'E_S_upper'=>'E',
     *      'DES_IMP_like_jollyEnd_upper'=>'ACCERTAMENTI',
     *      'GROUP(G1)'=>array(
     *          'NUMEROIMP_gt_eq'=>100,
     *          'NUMEROIMP_lt'=>200
     *      ),
     *      'GROUP(G2)_or'=>array(
     *          'NUMEROIMP_arror'=>(202,205,207)
     *      ),
     *      'ANNORIF'=>2017,
     *      'ANNO_ESE_or_arror'=>array(2017,2018),
     *      'TIPODELIB_upper'=>'dd'
     * );
     * $result = $this->setDefaultFilters($table, $fields, $sqlParams);
     * 
     * echo $result; //upper(FES_IMP.E_S) = :E_S_upper  AND  upper(FES_IMP.DES_IMP) LIKE :DES_IMP_like_jollyEnd_upper  AND
     *               //(FES_IMP.NUMEROIMP >= :NUMEROIMP_gt_eq AND FES_IMP.NUMEROIMP < :NUMEROIMP_lt) OR
     *               //(FES_IMP.NUMEROIMP = :G1NUMEROIMP_arror0 OR FES_IMP.NUMEROIMP = :G1NUMEROIMP_arror1 OR FES_IMP.NUMEROIMP = :G1NUMEROIMP_arror2) AND
     *               //(FES_IMP.ANNORIF = :ANNORIF OR ( FES_IMP.ANNO_ESE = :ANNO_ESE_or_arror0 OR  FES_IMP.ANNO_ESE = :ANNO_ESE_or_arror1)) AND
     *               //upper(FES_IMP.TIPODELIB) = :TIPODELIB_upper
     * var_dump($sqlParams); //
     * *********************************************************************
     */
    public function setDefaultFilters($table = null, $fields, &$sqlParams = array(), $prefix='') {
        if (isSet($table)) {
            $table = trim(strtoupper($table));
        }

        $fieldsData = array();

        foreach ($fields as $field => $value) {
            if (!is_array($value) && !isset($value)) {               
                continue;
            }
            $sql = '';
            $field = trim($field);

            if (preg_match('/^GROUP\(([A-Za-z][A-Za-z0-9_]*)\)/', $field, $matches) === 1){
                $sql .= '('.$this->setDefaultFilters($table, $value, $sqlParams, $matches[1]).')';
                $modifier = explode('_', trim(str_replace($matches[0], '', $field), " \t\n\r\0\x0B_"));
                $modifier = array_flip($modifier);
                
                $fieldsData[] = array(
                    'fieldName' => $matches[0],
                    'fieldModifiers' => $modifier,
                    'result' => $sql
                );
            }
            elseif (preg_match('/^RAWCONDITION\(([A-z0-9]*)\)(?:_(.*?))?$/', $field, $matches) === 1){
                $modifier = explode('_', $matches[2]);
                $modifier = array_flip($modifier);
                $fieldsData[] = array(
                    'fieldName' => $matches[0],
                    'fieldModifiers' => $modifier,
                    'result' => $value
                );
            }
            elseif (preg_match('/^(?:([A-Z0-9_]*[A-Z0-9])\.)?([A-Z0-9_]*[A-Z0-9])(?:_[a-z][A-Za-z0-9]*)*$/', $field, $matches) !== 1) {
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Il campo " . $field . " risulta non avere un nome valido per un campo del DB di Cityware o un identificativo valido per un gruppo");
            }
            else{
                $fieldName = $matches[2];
                if (!empty($matches[1])){
                    $fieldName = $matches[1] . '.' . $fieldName;
                }
                elseif (isSet($table)) {
                    $fieldName = $table . '.' . $fieldName;
                }

                $field = preg_replace('/([^A-Za-z0-9_])/', '', $field);
                $modifier = explode('_', trim(str_replace($fieldName, '', $field), " \t\n\r\0\x0B_"));
                $modifier = array_flip($modifier);

                if (isSet($modifier['ignoreEmpty']) && empty($value)){
                    continue;
                }
                if (isSet($modifier['null'])) {
                    $sql .= $fieldName . ' IS NULL';
                } elseif (isSet($modifier['notNull'])) {
                    $sql .= $fieldName . ' IS NOT NULL';
                } elseif (isSet($modifier['like']) || isSet($modifier['notLike'])) {
                    $command = (isSet($modifier['like']) ? 'LIKE' : 'NOT LIKE');

                    if (is_array($value)) {
                        $sql .= ' (';
                        for ($i = 0; $i < count($value); $i++) {
                            if ($i > 0) {
                                if (isSet($modifier['arrand'])) {
                                    $sql .= ' AND ';
                                }
                                elseif (isSet($modifier['arror'])) {
                                    $sql .= ' OR ';
                                }
                                elseif (isSet($modifier['like'])){
                                    $sql .= ' OR ';
                                }
                                else{
                                    $sql .= ' AND ';
                                }
                            }

                            if (isSet($modifier['upper'])) {
                                $fieldName = $this->getCitywareDB()->strUpper($fieldName);
                                $value[$i] = strtoupper($value[$i]);
                            }
                            if (isSet($modifier['jollyBegin'])) {
                                $value[$i] = '%' . $value[$i];
                            }
                            if (isSet($modifier['jollyEnd'])) {
                                $value[$i] = $value[$i] . '%';
                            }
                            if (!isSet($modifier['jollyBegin']) && !isSet($modifier['jollyEnd']) && !isSet($modifier['jollyNone'])) {
                                $value[$i] = '%' . $value[$i] . '%';
                            }

                            $this->addSqlParam($sqlParams, $prefix . $field . $i, $value[$i], PDO::PARAM_STR);
                            $sql .= $fieldName . ' '.$command.' :' . $prefix . $field . $i;
                        }
                        $sql .= ') ';
                    } else {
                        if (isSet($modifier['upper'])) {
                            $fieldName = $this->getCitywareDB()->strUpper($fieldName);
                            $value = strtoupper($value);
                        }
                        if (isSet($modifier['jollyBegin'])) {
                            $value = '%' . $value;
                        }
                        if (isSet($modifier['jollyEnd'])) {
                            $value = $value . '%';
                        }
                        if (!isSet($modifier['jollyBegin']) && !isSet($modifier['jollyEnd'])) {
                            $value = '%' . $value . '%';
                        }

                        $this->addSqlParam($sqlParams, $prefix . $field, $value, PDO::PARAM_STR);
                        $sql .= ' ' . $fieldName . ' '.$command.' :' . $prefix . $field . ' ';
                    }
                } elseif (isSet($modifier['diff'])) {
                    if (is_array($value)) {
                        $sql .= ' (';
                        for ($i = 0; $i < count($value); $i++) {
                            if ($i > 0) {
                                if (isSet($modifier['arror'])) {
                                    $sql .= ' OR ';
                                } else {
                                    $sql .= ' AND ';
                                }
                            }

                            if (isSet($modifier['upper'])) {
                                $fieldName = $this->getCitywareDB()->strUpper($fieldName);
                                $value[$i] = strtoupper($value[$i]);
                            }

                            $this->addSqlParam($sqlParams, $prefix . $field . $i, $value[$i], PDO::PARAM_STR);
                            $sql .= $fieldName . ' <> :' . $prefix . $field . $i;
                        }
                        $sql .= ') ';
                    } else {
                        if (isSet($modifier['upper'])) {
                            $fieldName = $this->getCitywareDB()->strUpper($fieldName);
                            $value = strtoupper($value);
                        }

                        $this->addSqlParam($sqlParams, $prefix . $field, $value, PDO::PARAM_STR);
                        $sql .= ' ' . $fieldName . ' <> :' . $prefix . $field . ' ';
                    }
                } elseif (isSet($modifier['lt'])) {
                    if (is_array($value)) {
                        $sql .= ' (';
                        for ($i = 0; $i < count($value); $i++) {
                            if ($i > 0) {
                                if (isSet($modifier['arror'])) {
                                    $sql .= ' OR ';
                                } else {
                                    $sql .= ' AND ';
                                }
                            }

                            $this->addSqlParam($sqlParams, $prefix . $field . $i, $value[$i], PDO::PARAM_STR);
                            $sql .= ' ' . $fieldName . ' <';
                            if (isSet($modifier['eq'])) {
                                $sql .= '=';
                            }
                            $sql .= ' :' . $prefix . $field . $i;
                        }
                        $sql .= ') ';
                    } else {
                        $this->addSqlParam($sqlParams, $prefix . $field, $value, PDO::PARAM_STR);
                        $sql .= ' ' . $fieldName . ' <';
                        if (isSet($modifier['eq'])) {
                            $sql .= '=';
                        }
                        $sql .= ' :' . $prefix . $field . ' ';
                    }
                } elseif (isSet($modifier['gt'])) {
                    if (is_array($value)) {
                        $sql .= ' (';
                        for ($i = 0; $i < count($value); $i++) {
                            if ($i > 0) {
                                if (isSet($modifier['arror'])) {
                                    $sql .= ' OR ';
                                } else {
                                    $sql .= ' AND ';
                                }
                            }

                            $this->addSqlParam($sqlParams, $prefix . $field . $i, $value[$i], PDO::PARAM_STR);
                            $sql .= ' ' . $fieldName . ' >';
                            if (isSet($modifier['eq'])) {
                                $sql .= '=';
                            }
                            $sql .= ' :' . $prefix . $field . $i;
                        }
                        $sql .= ') ';
                    } else {
                        $this->addSqlParam($sqlParams, $prefix . $field, $value, PDO::PARAM_STR);
                        $sql .= ' ' . $fieldName . ' >';
                        if (isSet($modifier['eq'])) {
                            $sql .= '=';
                        }
                        $sql .= ' :' . $prefix . $field . ' ';
                    }
                } else {
                    if (is_array($value)) {
                        $sql .= ' (';
                        for ($i = 0; $i < count($value); $i++) {
                            if ($i > 0) {
                                if (isSet($modifier['arrand'])) {
                                    $sql .= ' AND ';
                                } else {
                                    $sql .= ' OR ';
                                }
                            }

                            if (isSet($modifier['upper'])) {
                                $fieldName = $this->getCitywareDB()->strUpper($fieldName);
                                $value[$i] = strtoupper($value[$i]);
                            }

                            $this->addSqlParam($sqlParams, $prefix . $field . $i, $value[$i], PDO::PARAM_STR);
                            $sql .= ' ' . $fieldName . ' = :' . $prefix . $field . $i;
                        }
                        $sql .= ') ';
                    } else {
                        if (isSet($modifier['upper'])) {
                            $fieldName = $this->getCitywareDB()->strUpper($fieldName);
                            $value = strtoupper($value);
                        }

                        $this->addSqlParam($sqlParams, $prefix . $field, $value, PDO::PARAM_STR);
                        $sql .= ' ' . $fieldName . ' = :' . $prefix . $field . ' ';
                    }
                }

                $fieldsData[] = array(
                    'fieldName' => $fieldName,
                    'fieldModifiers' => $modifier,
                    'result' => $sql
                );
            }
        }

        $return = '';
        $openPar = false;
        for ($i = 0; $i < count($fieldsData); $i++) {
            if ($i > 0) {
                if (isSet($fieldsData[$i]['fieldModifiers']['or'])) {
                    $return .= ' OR ';
                } else {
                    $return .= ' AND ';
                }
            }

            if (isSet($fieldsData[$i + 1]['fieldModifiers']['or']) && $openPar === false) {
                $openPar = true;
                $return .= ' (';
            }
            $return .= $fieldsData[$i]['result'];
            if (!isSet($fieldsData[$i + 1]['fieldModifiers']['or']) && $openPar === true) {
                $openPar = false;
                $return .= ')';
            }
        }

        return $return;
    }

    /**
     * Imposta connessione database Cityware
     * @param object $db Connessione db
     */
    public function setCitywareDB($db) {
        $this->CITYWARE_DB = $db;
    }

    /**
     * Restituisce connessione database Cityware
     * @return object Connessione database
     */
    public function getCitywareDB() {

//        //Forzo l'apertura di una nuova sessione (es. da usare per numeratori fuori transazione)
//        if ($bForce == false) {
//            // se c'è lo prende dall'oggetto dbHelper sennò lo riapre
//            //$this->CITYWARE_DB = cwbDBRequest::getInstance()->getCitywareDbSession();
//        }

        if (!$this->CITYWARE_DB) {
            try {
                $this->CITYWARE_DB = ItaDB::DBOpen(cwbLib::getCitywareConnectionName(false, self::$ente), '');
            } catch (Exception $e) {
                $this->setErrCode($e->getCode());
                $this->setErrMsg($e->getMessage());
            }
        }
        return $this->CITYWARE_DB;
    }

    /**
     * Restituisce connessione database Cityware per i numeratori 
     * @return object Connessione database
     */
    public function getCitywareNumeratoriDB() {
        $domainCode = App::$utente->getKey('ditta');
        return $this->getCitywareLibDB(null, cwbLib::getCitywareConnectionName(false, null, self::CONNECTION_NAME_NUMERATORI.$domainCode));
    }

    public function getCitywareLockDB() {
        $domainCode = App::$utente->getKey('ditta');
        return $this->getCitywareLibDB(null, cwbLib::getCitywareConnectionName(false, null, self::CONNECTION_NAME_LOCK.$domainCode));
    }

    public function getCitywareLibDB($libName, $connectionName = null) {
        if (!isset($connectionName)) {
            $domainCode = App::$utente->getKey('ditta');
            $connectionName = $libName . '_' . $domainCode;
        }
        if (!$this->CITYWARE_DB || $this->CITYWARE_DB->getConnectionName() !== $connectionName) {
            try {
                $this->CITYWARE_DB = ItaDB::DBOpen(cwbLib::getCitywareConnectionName(), '', $connectionName);
            } catch (Exception $e) {
                $this->setErrCode($e->getCode());
                $this->setErrMsg($e->getMessage());
            }
        }
        return $this->CITYWARE_DB;
    }
    
    public function getEnte(){
        return self::$ente;
    }

    public function setEnte($ente) {
        self::$ente = $ente;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMsg() {
        return $this->errMsg;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function setErrMsg($errMsg) {
        $this->errMsg = $errMsg;
    }

    /**
     * Restituisce un array con i dati dell'istruzione SQL
     * @param : Istruzione SQL
     * @param : carica come Row o Lista
     * @param : dati di ritorno
     * @return : vero o falso
     */
    public function leggi($sql, &$array, $bList = false, $sqlParams = array()) {
        if (empty($sqlParams)) {
            $array = ItaDB::DBSQLSelect($this->getCitywareDB(), $sql, $bList);
        } else {
            $array = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);
        }

        return true;
    }

    public function addSqlParam(&$sqlParams = array(), $name, $value, $type = PDO::PARAM_STR) {
        if (!isSet($sqlParams)) {
            $sqlParams = array();
        }
        if (!array_key_exists($name, $sqlParams)) {
            $sqlParams[] = array('name' => $name,
                'value' => $value,
                'type' => $type);
        } else {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "$name già utilizzato");
        }
    }

    public function addBinaryFieldsDescribe(&$sqlFields = array(), $name, $position) {
        if (!array_key_exists("fields", $sqlFields)) {
            $sqlFields["fields"] = array();
        }

        if (!array_key_exists($name, $sqlFields["fields"])) {
            $field = array(
                'name' => $name,
                'position' => $position,
                'type' => PDO::PARAM_LOB,
                'maxLenght' => 0
            );
            if(defined('PDO::SQLSRV_ENCODING_BINARY')){
                $field['driverData'] = PDO::SQLSRV_ENCODING_BINARY;
            }
            
            
            $sqlFields["fields"][] = $field;
            
        } else {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "$name già utilizzato");
        }
    }

    /* oggetto per il caricamento dei binary usato per risolvere problemi su oracle (linux) e  Mssql */

    public function addBinaryInfoCallback($callableClass, $callableMethod) {
        $infoBinary = array();
        $infoBinary['class'] = $callableClass;
        $infoBinary['method'] = $callableMethod;
        return $infoBinary;
    }

    public function defineFromSqlClass($model) {
        $modelService = itaModelServiceFactory::newModelService('');
        $row = $modelService->define($this->getCitywareDB(), $model);
        $this->setDefaultRow($model, $row); //Inizializzo la row con i valori consoni al tipo campo, ' ' o 0 o NULL
        return $row;
    }

    public function assignRow($model, $rowFrom, &$rowTo) {
        $modelService = itaModelServiceFactory::newModelService('');
        if (is_array($rowTo) == false) {
            $rowTo = $this->defineFromSqlClass($model);
        }
        $modelService->assignRow($this->getCitywareDB(), $model, $rowFrom, $rowTo);
    }

    public function insert($model, &$arrayInsert, &$errore) {
        $itaModel = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($model), false, cwbDBRequest::getInstance()->getStartedTransaction());
        $modelServiceData = new itaModelServiceData(new cwbModelHelper());
        $db = $this->getCitywareDB();
        try {
//            cwbDBRequest::getInstance()->startManualTransaction();
//            ItaDB::DBBeginTransaction($db);
            //Valorizzo la row da Inserire con i dati passati
            $itaModel->assignRow($db, $model, $arrayInsert, $row); //I campi non valorizzati restano a NULL
//            $this->check_null($model, $row);
//            $this->assignRow($model, $arrayInsert, $row); //Così valorizzo tutti i campi con il default prima di assegnarli, non lo usiamo, usiamo il default del db
            $modelServiceData->addMainRecord($model, $row);
            $itaModel->insertRecord($db, $model, $modelServiceData->getData(), 'Insert record  ' . $model);
            $data = $modelServiceData->getData();
        } catch (ItaException $e) {
            $errore = $e->getCompleteErrorMessage();
            return false;
        } catch (Exception $e) {
            $errore = $e->getMessage();
            return false;
//            cwbDBRequest::getInstance()->rollBackManualTransaction();
//            ItaDB::DBRollbackTransaction($db);
        }
//        cwbDBRequest::getInstance()->commitManualTransaction();
//        ItaDB::DBCommitTransaction($db);
        //Determino la chiave della tabella
        $id = $itaModel->getLastInsertId();
        $table = $itaModel->newTableDef($model, $db);
        $pks = $table->getPks();
        foreach ($pks as $pk) {
            if (is_array($id[0])) {
                $key = cwbLibCode::searchArrayMultiIndex($pk, 'KEY', $id);
                $arrayPK[$pk] = $id[$key]['VALUE'];
            } else {
                $arrayPK[$pk] = $id;
            }
        }

        //Mi leggo il record appena inserito
        $row = $itaModel->getByPks($db, $model, $arrayPK, false);
        if ($row) {
            $arrayInsert = $row;
        }

        return true;
    }

//    //non testato
    public function update($model, $pkVaule, &$arrayUpdate, &$errore) {
        $itaModel = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($model), true, cwbDBRequest::getInstance()->getStartedTransaction());
        $modelServiceData = new itaModelServiceData(new cwbModelHelper());
        $db = $this->getCitywareDB();
        $itaModel->assignRow($db, $model, $arrayUpdate, $row); //(*1)
        $pkString = $itaModel->calcPkString($db, $model, $row);
        try {
            $this->LOCK = $itaModel->lockRecord($db, $model, $pkString, "", 20);
            if ($this->LOCK['status'] != -1) { //se è OK
                $modelServiceData->addMainRecord($model, $row);
                $itaModel->updateRecord($db, $model, $modelServiceData->getData(), 'Update record  ' . $model);
                ItaDB::DBUnLock($this->LOCK['lockID'], $db);
            }
        } catch (ItaException $e) {
            $errore = $e->getCompleteErrorMessage();
            return false;
        } catch (Exception $e) {
            $errore = $e->getMessage();
            return false;
        }
        
        //Rileggo la chiave generata
        $table = $itaModel->newTableDef($model, $db);
        $pksTable = $table->getPks(); //Chiave composta
        $arrayPK = explode("|", $pkVaule); //Faccio lo split della chiave (valore) passato (es. AN|12938)
        foreach ($pksTable as $key => $value) {
            $PKsearch[$value] = $arrayPK[$key];
        }

        //Mi leggo il record appena inserito 
        $row = $itaModel->getByPks($db, $model, $PKsearch, false);
        if ($row) {
            $arrayUpdate = $row;
        }

        return true;
    }

    public function delete($model, &$arrayDelete, &$errore) {
        $itaModel = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($model), false, cwbDBRequest::getInstance()->getStartedTransaction());
        $modelServiceData = new itaModelServiceData(new cwbModelHelper());
        $db = $this->getCitywareDB();
        try {
            //Valorizzo la row da Inserire con i dati passati
            $itaModel->assignRow($db, $model, $arrayDelete, $row); //I campi non valorizzati restano a NULL
            $modelServiceData->addMainRecord($model, $row);
            $itaModel->deleteRecord($db, $model, $modelServiceData->getData(), 'Delete record  ' . $model);
            $data = $modelServiceData->getData();
        } catch (ItaException $e) {
            $errore = $e->getCompleteErrorMessage();
            return false;
        } catch (Exception $e) {
            $errore = $e->getMessage();
            return false;
        }
        return true;
    }

    //Inizializzo l'array con i valori di defualt
    public function setDefaultRow($tableName, &$row) {
        $modelService = itaModelServiceFactory::newModelService('');
        $tableDef = $modelService->newTableDef($tableName, $this->getCitywareDB());
        foreach ($tableDef->getFields() as $key => $value) {
            switch ($value['phpType']) {
                case 'char':
                    $row[$key] = '';
                    break;
                case 'numeric':
                    $row[$key] = 0;
                    break;
                default:
                    $row[$key] = null;
                    break;
            }
        }
    }

    //Passato il model, ne ottengo le chiavi
    public function getPKs($model) {
        //Determino se il modello ha chiave composta, nel caso strutturo l'array in multidimensione
        $itaModel = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($model), true, kfalse);
        $db = $this->getCitywareDB();
        $table = $itaModel->newTableDef($model, $db);
        $pksTable = $table->getPks(); //Determino la chiave composta
        return $pksTable;
    }

    //Inizializzo l'array con i valori di defualt
    public function check_null($tableName, &$row) {
        $modelService = itaModelServiceFactory::newModelService('');
        $tableDef = $modelService->newTableDef($tableName, $this->getCitywareDB());
        foreach ($tableDef->getFields() as $key => $value) {
            if (trim($row[$key] . ' ') == '') {
                switch ($value['phpType']) {
                    case 'char':
                        $row[$key] = '';
                        break;
                    case 'numeric':
                        $row[$key] = 0;
                        break;
                    default:
                        $row[$key] = null;
                        break;
                }
            }
        }
    }

}

