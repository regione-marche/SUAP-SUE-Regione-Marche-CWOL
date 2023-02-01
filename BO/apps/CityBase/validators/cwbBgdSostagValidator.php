<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBgdSostagValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation === itaModelService::OPERATION_INSERT || $operation === itaModelService::OPERATION_UPDATE) {
            if(strlen($data['NOME_TAG']) === 0){
                $msg = "Nome tag obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }
}
