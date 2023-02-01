<?php

require_once ITA_LIB_PATH . '/itaPHPCore/itaModelHelper.class.php';

/**
 * Motore di validazione
 *
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class itaModelValidator {

    const LEVEL_ERROR = 1;
    const LEVEL_WARNING = 2;
    const RULE_TYPE_NULL = 1;
    const RULE_TYPE_LEN = 2;
    const RULE_TYPE_CHECK_RELAZION = 3;
    const VALIDATION_STRATEGY_MANUAL = 0;
    const VALIDATION_STRATEGY_DB = 1;
    const VALIDATION_STRATEGY_DICTIONARY = 2;

    /**
     * Effettua validazione
     * @param object $DB Oggetto Database
     * @param string $modelName Nome model
     * @param object $tableDef Oggetto PDOTableDef
     * @param boolean $silent TRUE=ignora warning
     * @param array $data Dati con cui effettuare la validazione
     * @param int $operation Tipo operazione (Aggiungi/Modifica/Cancella)     
     * @param array $oldData Vecchio record
     * @param array $keyMapping mappa delle chiavi esterne con il record principale 
     * @return array con le regole di validazione
     */
    public static function validate($DB, $modelName, $tableDef, $silent, $data, $operation, $oldData, $keyMapping = array(), $modifiedData=null, $inMemory=false) {

        // cerca XML delle regole di validazione
        if (self::findXMLRules($modelName)) {
            return array();
        }

        // Se non trova il file XML, cerca la classe Resolver dove deve essere scritta la logica di validazione
        $resolver = itaModelHelper::findClassValidatorResolver($modelName);
        if ($resolver) {
            return $resolver->validate($DB, $data, self::loadRules($tableDef, $resolver, $operation, $resolver->getValidationStrategies()), $operation, $oldData, $tableDef, $keyMapping, $modifiedData, $inMemory);
        } else {
            return array();
        }
    }

    private static function findXMLRules($modelName) {
        return false;
    }

    private static function loadRules($tableDef, $resolver, $operation, $validationStrategies) {
        if ($operation === itaModelService::OPERATION_DELETE) {
            if ($validationStrategies['delete'] === self::VALIDATION_STRATEGY_DICTIONARY) {
                return self::loadRulesByTableDef($tableDef, $resolver, $operation);
            } else {
                return array();
            }
        } else {
            if ($validationStrategies['store'] === self::VALIDATION_STRATEGY_DB) {
                self::loadRulesByTableDef($tableDef, $resolver, $operation);
            } elseif ($validationStrategies['store'] === self::VALIDATION_STRATEGY_DICTIONARY) {
                // TODO caricare regole da dizionario ...
            } else {
                return array();
            }
        }
    }

    private static function loadRulesByTableDef($tableDef, $resolver, $operation) {
        $rules = array();
        //carica le regole di validazione sintattica per le operazioni di inserti\updatde 
        if ($operation === itaModelService::OPERATION_DELETE) {
            $relations = $tableDef->getRelations();

            if (count($relations) > 0) {
                foreach ($relations as $relation) {
                    self::addRule($rules, "*", self::RULE_TYPE_CHECK_RELAZION, array("relation" => $relation));
                }
            }
        } else {
            foreach ($tableDef->getFields() as $key => $attrs) {
                if (!in_array($key, $resolver->getExcludeFields()) && !$tableDef->isAutoKey($key)) {
                    if ($attrs['null'] == 0) {
                        self::addRule($rules, $key, self::RULE_TYPE_NULL, array(), $attrs['phpType']);
                    }
                    if ($attrs['phpType'] === 'char' && $attrs['len'] !== 0) {
                        self::addRule($rules, $key, self::RULE_TYPE_LEN, array('maxlen' => $attrs['len']), $attrs['phpType']);
                    }
                }
            }
        }
        return $rules;
    }

    private static function addRule(&$rules, $field, $ruleType, $ruleProps, $phpType=null) {
        $rules[] = array(
            'field' => $field,
            'ruleType' => $ruleType,
            'ruleProps' => $ruleProps,
            'phpType' => $phpType
        );
    }

    // --- Validatori Generici --------

    public static function checkEmpty($value) {
        if (is_string($value)) {
            return ($value === null) || (strlen(trim($value)) === 0);
        } elseif (is_numeric($value)) {
            return ($value === null) || ($value === 0);
        } else {
            return $value === null;
        }
    }

    public static function checkNotEmpty($value) {
        return !self::checkEmpty($value);
    }

    public static function checkEq($value, $ref, $ignoreCase = true) {
        // Se si tratta di resource confronto con il contenuto dello
        // stream non con il valore della risorsa (sarebbe sempre false)
        if (is_resource($ref)){
            $ref = stream_get_contents($ref);
        }
        
        if($value === null){
            $value = '';
        }
        if($ref === null){
            $ref = '';
        }
        
        if (is_string($value)) {
            $formatDate = self::isDate($value);

            if ($formatDate) {
                $formatRef = self::isDate($ref);
                return self::checkEqDate($value, $ref, $formatDate, $formatRef);
            }
            return ($ignoreCase ? strtoupper(trim($value)) === strtoupper(trim($ref)) : trim($value) === trim($ref));
        } else {
            return $value == $ref;
        }
    }

    public static function checkEqDate($value, $ref, $formatValue = null, $formatRef = null) {
        if (!$value&&$ref){
            return false;
        } else if ($value&&!$ref){
            return false;
        } else if (!$value&&$ref){
            return true;
        }
        
        $dateFormattedValue = DateTime::createFromFormat(($formatValue == null ? self::isDate($value) : $formatValue), $value);
        $dateFormattedRef = DateTime::createFromFormat(($formatRef == null ? self::isDate($ref) : $formatRef), $ref);
        return $dateFormattedValue->format("Ymd") === $dateFormattedRef->format("Ymd");
    }

    //controllo se la stringa è data
    private static function isDate($value) {
        $formatDateArray = array(
            "Y-m-d",
            "Y/m/d",
            "d-m-Y",
            "d/m/Y",
            "Y-m-d H:i:s",
            "Y-m-d H:i:s",
            "Y/m/d H:i:s",
            "Ymd H:i:s",
            "dmY H:i:s",
            "d-m-Y H:i:s",
            "d/m/Y H:i:s",
            "Y-m-d H:i:s.u",
            "Y-m-d H:i:s.u",
            "Y/m/d H:i:s.u",
            "d-m-Y H:i:s.u",
            "d/m/Y H:i:s.u");

        foreach ($formatDateArray as $format) {

            if (self::isValidDateTimeString($value, $format)) {
                return $format;
            }
        }
        return false;
    }

    private static function isValidDateTimeString($value, $format) {
        $date = DateTime::createFromFormat($format, $value);
        return ($date === false ? false : true);
    }

    public static function checkNotEq($value, $ref, $ignoreCase = true) {
        return !self::checkEq($value, $ref, $ignoreCase);
    }

// --- Validatori Stringhe --------

    public static function checkStrMinSize($value, $size) {
        return strlen(trim($value)) < $size;
    }

    public static function checkStrMaxSize($value, $size) {
        return strlen(trim($value)) > $size;
    }

// --- Validatori Numerici --------

    public static function checkNumMinValue($value, $ref) {
        return $value < $ref;
    }

    public static function checkNumMaxValue($value, $ref) {
        return $value > $ref;
    }

    public static function checkNumRange($value, $min, $max, $includeLimits) {
        return ($includeLimits ? ($value >= $min && $value <= $max) : ($value > $min && $value < $max));
    }

// --- Validatori Date --------

    public static function checkDateMinValue($value, $ref) {
        return $value < $ref;
    }

    public static function checkDateMaxValue($value, $ref) {
        return $value > $ref;
    }

    public static function checkDateRange($value, $min, $max, $includeLimits) {
        return ($includeLimits ? ($value >= $min && $value <= $max) : ($value > $min && $value < $max));
    }

    public static function checkDatePast($value) {
        return new DateTime($value) < new DateTime();
    }

    public static function checkDateFuture($value) {
        return new DateTime($value) > new DateTime();
    }

// --- Validatori Array --------

    public static function checkArraySize($value, $size) {
        return count($value) === $size;
    }

    public static function checkArrayEmpty($value) {
        return count($value) === 0;
    }

    public static function checkArrayNotEmpty($value) {
        return count($value) !== 0;
    }

}
