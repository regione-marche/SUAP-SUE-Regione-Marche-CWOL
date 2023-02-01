<?php
/**
 * Dati da passare al ModelService
 *
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class itaModelServiceData {

    const RELATION_TYPE_NONE = 0;
    const RELATION_TYPE_ONE_TO_ONE = 1;
    const RELATION_TYPE_ONE_TO_MANY = 2;
//    const RELATION_TYPE_MANY_TO_ONE = 3;
    const KEY_CURRENT_RECORD="CURRENT_RECORD";
    const KEY_CHILD="CHILD";

    private $data;
    private $helper;

    public function __construct($helper) {
        $this->helper = $helper;
    }
    
    /**
     * Aggiunge record principale
     * @param string $tableName Nome tabella
     * @param array $tableData Dati record
     */
    public function addMainRecord($tableName, $tableData) {
        $this->add(self::KEY_CURRENT_RECORD, $tableName, $tableData, true, self::RELATION_TYPE_NONE);
    }
    
    public function addChildrenRecords($tableName, $tableData){
        if(isSet($tableData) && is_array($tableData) && count($tableData) > 0){
            $this->add(self::KEY_CHILD, $tableName, $tableData, false, self::RELATION_TYPE_NONE);
        }
    }

    /**
     * Aggiunge dati aggiuntivi (non relazionati)
     * @param string $tableName Nome tabella
     * @param array $tableData Dati aggiuntivi
     * @param string $modelName Nome modello
     * @param array $keyMapping Mappatura chiave tabella principale con tabelle relazionate
     * @param string $relName  Nome della relazione da associare alla chiave 
     * dati aggiuntivi . Necessario per salvare più di una volta lo stesso modello 
     */
    public function addAdditionalData($tableName, $tableData, $modelName, $keyMapping,$relName=null) {
        $this->add(($relName !== null ? $relName :$modelName) , $tableName, $tableData, false, self::RELATION_TYPE_NONE, $modelName, $keyMapping);
    }

    /**
     * Aggiunge relazione one-to-one
     * @param string $tableName Nome tabella
     * @param array $tableData Dati relazione
     * @param string $modelName Nome modello
     * @param array $keyMapping Mappatura chiave tabella principale con tabelle relazionate
     * @param string $relName  Nome della relazione da associare alla chiave 
     * @param array $modelServiceFactory Factory utilizzata per creare il modelService
     * dati aggiuntivi . Necessario per salvare più di una volta lo stesso modello
     */
    public function addRelationOneToOne($tableName, $tableData, $modelName, $keyMapping, $relName = null, $modelServiceFactory = null) {
        $this->add(($relName !== null ? $relName :$tableName) , $tableName, $tableData, false, self::RELATION_TYPE_ONE_TO_ONE, $modelName, $keyMapping, $modelServiceFactory);
    }

    /**
     * Aggiunge relazione one-to-many
     * @param string $tableName Nome tabella
     * @param array $tableData Dati relazione
     * @param string $modelName Nome modello
     * @param array $keyMapping Mappatura chiave tabella principale con tabelle relazionate
     * @param string $relName  Nome della relazione da associare alla chiave 
     * @param array $modelServiceFactory Factory utilizzata per creare il modelService
     * dati aggiuntivi . Necessario per salvare più di una volta lo stesso modello
     */
    public function addRelationOneToMany($tableName, $tableData, $modelName, $keyMapping, $relName = null, $modelServiceFactory = null) {
        $this->add(($relName !== null ? $relName :$tableName) , $tableName, $tableData, false, self::RELATION_TYPE_ONE_TO_MANY, $modelName, $keyMapping, $modelServiceFactory);
    }

    /**
     * Aggiunge relazione many-to-many
     * @param string $tableName Nome tabella
     * @param array $tableData Dati relazione
     * @param string $modelName Nome modello
     * @param array $keyMapping Mappatura chiave tabella principale con tabelle relazionate
     * @param string $relName  Nome della relazione da associare alla chiave 
     * @param array $modelServiceFactory Factory utilizzata per creare il modelService
     * dati aggiuntivi . Necessario per salvare più di una volta lo stesso modello
     */
//    public function addRelationManyToOne($tableName, $tableData, $modelName, $keyMapping, $relName = null, $modelServiceFactory = null) {
//        $this->add(($relName !== null ? $relName :$tableName) , $tableName, $tableData, false, self::RELATION_TYPE_MANY_TO_ONE, $modelName, $keyMapping, $modelServiceFactory);
//    }

    private function add($key, $tableName, $tableData, $isMainRecord, $relationType, $modelName = NULL, $keyMapping = array(), $modelServiceFactory = null) {
        if(!isSet($modelName) || trim($modelName) == ''){
            $modelName = cwbModelHelper::modelNameByTableName($tableName);
        }
        $this->data[$key] = array(
            'tableName' => $tableName,
            'tableData' => $tableData,
            'isMainRecord' => $isMainRecord,
            'relationType' => $relationType,
            'modelName' => $modelName,
            'keyMapping' => $keyMapping,
            'modelServiceFactory' => $modelServiceFactory);
    }

    public function getData() {
        return $this->data;
    }

    public function setData($data) {
        $this->data = $data;
    }

}
