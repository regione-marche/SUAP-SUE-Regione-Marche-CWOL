<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBtaServrendValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if (strlen($data['PROGKEYTAB']) === 0) {
                $msg = "Id obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if ($data['ANNOEMI'] === 0) {
                $msg = "Anno Emissione obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if ($data['NUMEMI'] === 0) {
                $msg = "Numero Emissione obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['IDBOL_SERE']) === 0) {
                $msg = "Servizio Emittente obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if ($data['CODTIPSCAD'] === 0) {
                $msg = "Codice Tipo Scadenza obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['SUBTIPSCAD']) === 0) {
                $msg = "Sotto Tipo Scadenza obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
