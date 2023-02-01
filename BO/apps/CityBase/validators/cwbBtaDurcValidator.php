<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

class cwbBtaDurcValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation === itaModelService::OPERATION_INSERT || $operation === itaModelService::OPERATION_UPDATE) {
            if(strlen($data['PROGSOGG']) === 0){
                $msg = "Soggetto obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if(strlen($data['DATARILA']) === 0){
                $msg = "Data rilascio obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if(strlen($data['DATAFINE']) === 0){
                $msg = "Data cessazione obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }
}
