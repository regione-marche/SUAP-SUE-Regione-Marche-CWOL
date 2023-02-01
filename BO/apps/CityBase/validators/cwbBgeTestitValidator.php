<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBgeTestitValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {

        if ($operation !== itaModelService::OPERATION_DELETE) {
           //Controlla campo TESTOPROV
            if ($data['TESTOPROV'] === 0) {
                $msg = "Inserire la provenienza del testo.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            //Controlla campo TESTIT
            if (strlen($data['TESTIT']) === 0) {
                $msg = "Inserire la Tipologia Testo";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }
}
