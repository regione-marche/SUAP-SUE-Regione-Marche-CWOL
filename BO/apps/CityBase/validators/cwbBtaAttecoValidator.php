<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBtaAttecoValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if (strlen($data['CODATTIVEC']) === 0) {
                $msg = "Codice obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['DESATTECIV']) === 0) {
                $msg = "Descrizione obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['CODATTIVIS']) != 0) {
                if (strlen($data['DESATTECIS']) == 0) {
                    $msg = "Descrizione att.econ.ISTAT obbligatoria";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
        }
    }

}
