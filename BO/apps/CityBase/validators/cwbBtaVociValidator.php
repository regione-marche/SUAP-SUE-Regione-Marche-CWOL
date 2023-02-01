<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBtaVociValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if(strlen($data['CODVOCEL']) === 0){
                $msg = "Codice obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if(strlen($data['DESVOCEEL']) === 0){
                $msg = "Descrizione obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }
}
