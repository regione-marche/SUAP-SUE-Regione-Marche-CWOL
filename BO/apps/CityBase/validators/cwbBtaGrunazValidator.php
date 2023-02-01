<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBtaGrunazValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if (strlen($data['CODGRNAZ']) === 0) {
                $msg = "Indicare il codice Gruppo Nazionalità.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if (strlen($data['DESGRNAZ']) === 0) {
                $msg = "Indicare la descrizione Gruppo Nazionalità.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }
}
