<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBgdTipdocValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {

        if ($operation !== itaModelService::OPERATION_DELETE) {
           //Controlla campo descrizione
            if (strlen($data['DESCRIZIONE']) === 0) {
                $msg = "Inserire la descrizione.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            //Controlla campo ALIAS
            if (strlen($data['ALIAS']) === 0) {
                $msg = "Inserire l'Alias";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            //Controlla campo AREA_ORIG
            if (strlen($data['AREA_ORIG']) === 0) {
                $msg = "Inserire Area Origine.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            //Controlla campo MODULO_ORIG
            if (strlen($data['MODULO_ORIG']) === 0) {
                $msg = "Inserire Modulo Origine.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }
}
