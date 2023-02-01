<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBtaRgrunaValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if ($data['PK'] < 20) { // TODO: per replicare il controllo come era in cityware, oltre a PK, va controllato anche iv_flag_default =0 (non so cosa sia questa flag)
                    $msg = "Si sta tendando di modificare un codice inferiore a 20. Questo codice è riservato alla ditta fornitrice";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
