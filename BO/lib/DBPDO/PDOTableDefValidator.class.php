<?php

/**
 * Validatore basato su PDOTableDef
 *
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
abstract class PDOTableDefValidator {

    private $modelProps;
    private $excludeFields;
    private $validationInfo;
    private $validationStrategies;

    public function __construct() {
        $this->excludeFields = array();
        $this->modelProps = array();
        $this->initValidationStrategies();
        $this->initExcludeFields();
    }

    /**
     * Effettua validazione del record
     * @param object $DB Oggetto Database
     * @param array $data Record con i dati da validare
     * @param array $rules Regole di validazione
     * @param int $operation Tipo di operazione (INSERT/UPDATE/DELETE)
     * @param object $tableDef Table Object 
     * @param array $oldData Vecchio record
     * @param array $keyMapping array per associare il nome  della 
     * chiave primaria con la chiave esterna della tabella relazionata key:PK->value=FPk
     * @return array Dati di violazione regole
     */
    public function validate($DB, $data, $rules, $operation, $oldData, $tableDef, $keyMapping = array(), $modifiedData=null, $inMemory=false) {

        $this->initModelProps($tableDef);

        $this->validationInfo = array();

        if ($operation !== itaModelService::OPERATION_DELETE) {
            $this->getStoreViolations($DB, $data, $rules, $operation, $tableDef, $keyMapping);
        } else {
            $this->getDeleteViolations($DB, $data, $rules, $operation, $tableDef, $keyMapping);
        }

        // esegue i validatori personalizzati
        $this->customValidate($data, $rules, $operation, $keyMapping,$tableDef->getName(), $oldData, $modifiedData, $inMemory);

        return $this->validationInfo;
    }    

    protected function getStoreViolations($DB, $data, $rules, $operation, $tableDef, $keyMapping) {
        $this->getDefaultStoreViolations($DB, $data, $rules, $operation, $tableDef, $keyMapping);
    }

    protected function getDeleteViolations($DB, $data, $rules, $operation, $tableDef, $keyMapping) {
        
    }

    private function getDefaultStoreViolations($DB, $data, $rules, $operation, $tableDef, $keyMapping) {

        // esegue i validatori generici
        foreach ($rules as $rule) {
            // se la il campo è nel keyMapping non devo controllare le regole perchè è una chiave esterna 
            if (!in_array($rule['field'], $keyMapping)) {

                if (array_key_exists($rule['field'], $this->modelProps)) {
                    $descField = $this->modelProps[$rule['field']];
                } else {
                    $descField = $rule['field'];
                }

                // controllo nullable
                if ($rule['ruleType'] === itaModelValidator::RULE_TYPE_NULL) {
                    if (itaModelValidator::checkEmpty($data[$rule['field']])) {
                        $this->addViolation($rule['field'], itaModelValidator::LEVEL_ERROR, 'Il campo ' . $descField . ' deve essere valorizzato.');
                    }
                }

                // controllo sulla lunghezza dei campi
                if ($rule['ruleType'] === itaModelValidator::RULE_TYPE_LEN) {
                    if (itaModelValidator::checkStrMaxSize($data[$rule['field']], $rule['ruleProps']['maxlen'])) {
                        $this->addViolation($rule['field'], itaModelValidator::LEVEL_ERROR, 'Il campo ' . $descField . ' deve avere lunghezza massima di ' . $rule['ruleProps']['maxlen'] . 'caratteri.');
                    }
                }
            }
        }
    }

    /**
     * Aggiunge una violazione
     * @param string $field Nome campo
     * @param int $level Livello di violazione (ERROR/WARNING)
     * @param string $msg Messaggio di violazione
     */
    public function addViolation($field, $level, $msg) {
        $this->validationInfo[] = array(
            'field' => $field,
            'level' => $level,
            'msg' => $msg
        );
    }

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldData, $modifiedData=null, $inMemory=false) {
        
    }

    /**
     * Inizializza descrizioni dei campi per generazione messaggi di errore
     */
    public abstract function initModelProps($tableDef);

    /**
     * Inizializza array dei campi escludi dalla validazione automatica
     */
    public function addExcludeField($field) {
        $this->excludeFields[] = $field;
    }

    /**
     * Inizializza strategie di validazione
     * N.B.: Il metodo può essere sovrascritto nelle sottoclassi
     */
    protected function initValidationStrategies() {

        // Legge dal file di configurazione la strategy utilizzata per la validazione in salvataggio
        $storeValidationStrategy = Config::getConf('dbms.storeValidationStrategy');
        if (!$storeValidationStrategy) {
            $storeValidationStrategy = itaModelValidator::VALIDATION_STRATEGY_MANUAL;
        }

        // Legge dal file di configurazione la strategy utilizzata per la validazione in cancellazione
        $deleteValidationStrategy = Config::getConf('dbms.deleteValidationStrategy');
        if (!$deleteValidationStrategy) {
            $deleteValidationStrategy = itaModelValidator::VALIDATION_STRATEGY_MANUAL;
        }

        $validationStrategies = array(
            'store' => $storeValidationStrategy,
            'delete' => $deleteValidationStrategy
        );

        $this->setValidationStrategies($validationStrategies);
    }

    /**
     * Inizializza i campi esclusi dalla validazione automatica
     */
    public function initExcludeFields() {
        
    }

    public function getModelProps() {
        return $this->modelProps;
    }

    public function setModelProps($modelProps) {
        $this->modelProps = $modelProps;
    }

    public function getExcludeFields() {
        return $this->excludeFields;
    }

    public function setExcludeFields($excludeFields) {
        $this->excludeFields = $excludeFields;
    }

    public function getValidationStrategies() {
        return $this->validationStrategies;
    }

    public function setValidationStrategies($validationStrategies) {
        $this->validationStrategies = $validationStrategies;
    }

}
