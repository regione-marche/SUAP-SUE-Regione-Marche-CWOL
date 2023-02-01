<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBtaDefscaValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if(strlen($data['DEFSCALA']) === 0){
                $msg = "Codice obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }
}
