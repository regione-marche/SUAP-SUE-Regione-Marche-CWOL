<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';

/**
 * Per estendere la classe base di Cityware per aggiungere i validdatori logici 
 * il nome della classe deve essere esempio "cwbBtaGrunazValidator"
 */
class cwbBaseValidator extends PDOTableDefValidator {

    private $helper;

    public function __construct() {
        parent::__construct();
        $this->helper = new cwbModelServiceHelper();
    }

    public function initExcludeFields() {
        $this->addExcludeFieldsAudit();
    }
    
    protected function initValidationStrategies() {       
        $this->setValidationStrategies(array(
            'store' => 0,
            'delete' => 2
        ));
    }
    
    protected function addExcludeFieldsAudit() {
        $this->addExcludeField('CODUTE');
        $this->addExcludeField('DATAOPER');
        $this->addExcludeField('TIMEOPER');
        $this->addExcludeField('CODUTEINS');
        $this->addExcludeField('DATAINSER');
        $this->addExcludeField('TIMEINSER');
    }

    /**
     * Effettua il cariamento delle descrizioni delle colonne prese da tableDef
     * @param object $tableDef oggetto "$tableDef"
     */
    public function initModelProps($tableDef) {
        $modelProps = array();
        if($tableDef->getColDescriptions() !== null){
            foreach ($tableDef->getColDescriptions() as $value) {
                $modelProps[trim($value["CAMPO"])] = trim($value["DESCRI"]);
            }
        }
        $this->setModelProps($modelProps);
    }

    protected function getDeleteViolations($DB, $data, $rules, $operation, $tableDef, $keyMapping) {

        foreach ($rules as $rule) {
            // controllo se esistono delle referenze per evitare di cancellare il record  
            if ($rule['ruleType'] === itaModelValidator::RULE_TYPE_CHECK_RELAZION) {

                $ruleProps = $rule["ruleProps"];
                // se l'operazione è solo quella di controllo faccio il controllo integrità
                $relation = $ruleProps["relation"];
                if ($relation["operazioneRelazione"] == 0) {
                    $paramFields = array();
                    $tableDip = $relation["areaDip"] . $relation["moduloDip"] . "_" . $relation["nometaDip"];
                    $tableInd = $relation["areaInd"] . $relation["moduloInd"] . "_" . $relation["nometaInd"];
                    $where = $this->helper->getWhere($DB, $tableDef, $data, $relation["fields"], $paramFields);
                    $where = $where . ($ruleProps["condizioneWhere"] ? " AND " : $ruleProps["condizioneWhere"]);
                    $sqlString = "SELECT COUNT(*) AS CONTEGGIO FROM $tableDip WHERE $where ";
                    $result = ItaDB::DBQuery($DB, $sqlString, false, $paramFields);
                    if ($result["CONTEGGIO"] > 0) {
                        //aggiunta violazione record 
                        $message = "Impossibile effettuare la cancellazione del record della tabella '$tableInd' in quanto referenzato nella tabella '$tableDip' ";
                        $this->addViolation($tableDip, itaModelValidator::LEVEL_ERROR, $message);
                    }
                }
            }
        }
    }
    
    /**
     * Restituisce i dati relativi ad una tabella o a tutte le tabelle presenti nella relazione.
     * I dati vengono letti da DB e aggiornati con quanto presente in modifiedFormData
     * @param array $modifiedFormData Array modified form data passato al metodo validate
     * @param string $table Facoltativo. Indica se recuperare i dati di una specifica tabella. Se non passato si recuperano i dati di
     *                      tutte le tabelle presenti nella relazione.
     * @return array risultato in forma: array(
     *                                       'TABLE1'=>array(
     *                                           $table1Row1,
     *                                           $table1Row2,
     *                                           $table1Row3
     *                                       ),
     *                                       'TABLE2'=>array(
     *                                           $table2Row1,
     *                                           $table2Row2,
     *                                           $table2Row3
     *                                       )
     *                                   )
     * @throws ItaException
     */
    protected function getRelatedRecords($modifiedFormData=null, $table=null){
        if(empty($modifiedFormData['CURRENT_RECORD'])){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Non risultano relazioni');
        }
        
        $libDB = new cwbLibDB_GENERIC();
        $return = array();
        foreach($modifiedFormData as $key=>$data){
            $tableName = $data['tableName'];
            
            if(isSet($table) && $tableName != $table){
                continue;
            }
//            $modelService = itaModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableName), true, false);
//            $tableDef = $modelService->newTableDef($tableName, $db);
            
            $return[$tableName] = array();
            if($key == 'CURRENT_RECORD'){
                $return[$tableName][] = $data['tableData'];
            }
            else{
                $filtri = array();
                $skip = false;
                foreach($data['keyMapping'] as $from=>$to){
                    if(!empty($modifiedFormData['CURRENT_RECORD']['tableData'][$from])){
                        $filtri[$to] = $modifiedFormData['CURRENT_RECORD']['tableData'][$from];
                    }
                    else{
                        $skip = true;
                        break;
                    }
                }
                
                if($skip){
                    $return[$tableName] = array();
                    foreach($data['tableData'] as $row){
                        $return[$tableName][] = $row['data'];
                    }
                }
                else{
                    $oldData = $libDB->leggiGeneric($tableName, $filtri);
                    
                    $modelService = itaModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableName), true, false);
                    $tableDef = $modelService->newTableDef($tableName, $libDB->getCitywareDB());
                    $pks = $tableDef->getPks();
                    foreach($data['tableData'] as $row){
                        switch($row['operation']){
                            case itaModelService::OPERATION_INSERT:
                                $oldData[] = $row['data'];
                                break;
                            case itaModelService::OPERATION_UPDATE:
                                foreach($oldData as $k=>$oldRow){
                                    $modify = true;
                                    foreach($pks as $pk){
                                        if($row['data'][$pk] != $oldRow[$pk]){
                                            $modify = false;
                                            break;
                                        }
                                    }
                                    if($modify){
                                        $oldData[$k] = $row['data'];
                                    }
                                }
                                break;
                            case itaModelService::OPERATION_DELETE:
                                foreach($oldData as $k=>$oldRow){
                                    $delete = true;
                                    foreach($pks as $pk){
                                        if($row['data'][$pk] != $oldRow[$pk]){
                                            $delete = false;
                                            break;
                                        }
                                    }
                                    if($delete){
                                        unset($oldData[$k]);
                                    }
                                }
                                break;
                        }
                    }
                    
                    $return[$tableName] = $oldData;
                }
            }
        }
        
        return $return;
    }

}

