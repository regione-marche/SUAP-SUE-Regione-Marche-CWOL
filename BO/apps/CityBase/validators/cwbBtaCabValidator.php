<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBtaCabValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if (strlen($data['CAB']) === 0) {
                $msg = "Codice obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['DES_SPORT']) === 0) {
                $msg = "Descrizione obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['INDIRSPORT']) === 0) {
                $msg = "Indirizzo obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['COMUNEUBIC']) === 0) {
                $msg = "Comune obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['CAP']) === 0) {
                $msg = "CAP obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
