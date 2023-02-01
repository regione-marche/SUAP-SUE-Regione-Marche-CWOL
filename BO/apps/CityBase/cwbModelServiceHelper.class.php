<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BDI.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';

class cwbModelServiceHelper {

    private $lib_bdi;

    public function __construct() {
        $this->lib_bdi = new cwbLibDB_BDI();
    }

    public function initBehaviors($behaviors='*') {
        $return = array();
        
        if($behaviors == '*' || in_array('COLUMN_DESCRIPTION', $behaviors)){
            $return["COLUMN_DESCRIPTIONS"] = array("function" => array($this, "loadColsDescriptions"));
        }
        
        if($behaviors == '*' || in_array('RELATIONS', $behaviors)){
            $return["RELATIONS"] = array("function" => array($this, "loadRelations"));
        }
        
        if($behaviors == '*' || in_array('DELETE_CASCADE', $behaviors)){
            $return["DELETE_CASCADE"] = array("function" => array($this, "deleteCascade"));
        }
        
        if($behaviors == '*' || in_array('AUDIT', $behaviors)){
            $return["AUDIT"] = array("function" => array($this, "manageAudit"));
        }

        return $return;
    }

    public function loadColsDescriptions($tableName) {
        return $this->lib_bdi->leggiDescrizioniColonneTabella($tableName);
    }

    public function loadRelations($tableName) {
        return $this->lib_bdi->leggiRelazioni($tableName);
    }

    public function deleteCascade($DB, $tableDef, $data) {
        $relations = $this->lib_bdi->leggiRelazioni($tableDef->getName());

        foreach ($relations as $relation) {
            // se è prevista la cancellazione del record relazionato
            if ($relation["operazioneRelazione"] == 2 || $relation["operazioneRelazione"] == 3) {

                $tableDip = $relation["areaDip"] . $relation["moduloDip"] . "_" . $relation["nometaDip"];
                //Non vengono applicati i behaviors
                $modelServiceRel = itaModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableDip), true, true);
                $modelServiceRel->setBehaviors($this->initBehaviors());
                //caricare i dati per chiave esterna e lanciare la delete del service
                $where = $this->getWhere($DB, $tableDef, $data, $relation["fields"], $paramFields);
                $where .= ($relation["condizioneWhere"] ? " AND " . $relation["condizioneWhere"] : "");
                $sqlString = "SELECT $tableDip.*  FROM $tableDip WHERE $where ";
                $results = ItaDB::DBQuery($DB, $sqlString, TRUE, $paramFields);

                //effettua cancellazione di tutti i record relazionati al principale 

                foreach ($results as $result) {
                    $recordInfo = "Cancellazione a cascata delle tabella $tableDip";

                    $keyMapping = array();
                    //caricametno delle chiavi tra record principale e la sua relazionata 
                    foreach ($relation["fields"] as $field) {
                        $keyMapping[] = array($field["campoInd"] => $field["campoDip"]);
                    }
                    $oldCurrentRecord = $modelServiceRel->getByPks($DB, $tableDip, $data);

                    $validationInfo = $modelServiceRel->validate($DB, $tableDip, $result, itaModelService::OPERATION_DELETE, $oldCurrentRecord, $keyMapping);
                    if (count($validationInfo) > 0) {
                        foreach ($validationInfo as $currentInfo) {
                            if ($currentInfo['level'] === itaModelValidator::LEVEL_ERROR) {
                                $msg .= $tableDip . " - ";
                                $msg .= $currentInfo['msg'] . '<br/>';
                            }
                        }
                        throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, -1, $msg);
                    } else {
                        $modelServiceRel->deleteRecord($DB, $tableDip, $result, $recordInfo);
                    }
                }
            }
        }
    }

    public function getWhere($DB, $tableDef, $tableData, $fields, &$paramFields) {
        $sqlWhere = '';
        $paramFields = array();

        $tableData = $this->initData($tableData);

        foreach ($fields as $field) {
            $fieldNameInd = $field["campoInd"];
            $fieldNameDip = $field["campoDip"];
            if (strlen($sqlWhere) == 0) {
                $sqlWhere.=" $fieldNameDip = :$fieldNameDip";
            } else {
                $sqlWhere.=" AND $fieldNameDip = :$fieldNameDip";
            }
            $dbmsTypes = $DB->getDbmsBaseTypes();
            $tableFields = $tableDef->getFields();
            $paramFields[] = array('name' => $fieldNameDip,
                'value' => $tableData[$fieldNameInd],
                'type' => $dbmsTypes[$tableFields[$fieldNameInd]['type']]['pdo']);
        }
        return $sqlWhere;
    }

    /**
     * Valorizza campi di audit
     * @param string $operationType Tipo operazione
     */
    public function manageAudit($operationType, $tableDef, $data) {
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');

        // Inserimento
        $tableFields = $tableDef->getFields();
        if ($operationType === itaModelService::OPERATION_INSERT) {

            if (array_key_exists("CODUTEINS", $tableFields)) {
                $data['CODUTEINS'] = cwbParGen::getSessionVar('nomeUtente');
            }
            if (array_key_exists("DATAINSER", $tableFields)) {
                $data['DATAINSER'] = $currentDate;
            }
            if (array_key_exists("TIMEINSER", $tableFields)) {
                $data['TIMEINSER'] = $currentTime;
            }
            if (!array_key_exists("FLAG_DIS", $tableFields)) {
                $data['FLAG_DIS'] = 0;
            }
        }

        // Ultima modifica
        if (array_key_exists("CODUTE", $tableFields)) {
            $data['CODUTE'] = cwbParGen::getSessionVar('nomeUtente');
        }
        if (array_key_exists("DATAOPER", $tableFields)) {
            $data['DATAOPER'] = $currentDate;
        }
        if (array_key_exists("TIMEOPER", $tableFields)) {
            $data['TIMEOPER'] = $currentTime;
        }

        return $data;
    }

    private function initData($data) {
        if (array_key_exists("CURRENT_RECORD", $data)) {
            $toManage = $data['CURRENT_RECORD']['tableData'];
        } else if (!array_key_exists("data", $data)) {
            $toManage = $data;
        } else {
            $toManage = $data['data'];
        }
        return $toManage;
    }

}

?>