<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBtaGrnoteValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if(strlen($data['DESGRUPPO']) === 0){
                $msg = "Descrizione obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }
}
