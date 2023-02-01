<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBtaProfValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if(strlen($data['CODPROF']) === 0){
                $msg = "Codice obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if(strlen($data['DESPROF']) === 0){
                $msg = "Descrizione obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }
}
