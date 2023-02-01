<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';

class cwbBorOrganRicbiValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData = null) {
        if ($operation === itaModelService::OPERATION_UPDATE) {
            if ($data['FLAG_RICBI'] == 1) {
//                $msg = "Le richieste di bilancio verranno bloccate anche per i livelli inferiori";
                $msg = "Le richieste di bilancio verranno bloccate per il servizio: " . $data['L1ORG'] . "." . $data['L2ORG'] . "." . $data['L3ORG'] . "." . $data['L4ORG'] . " '" . $data['DESPORG'] . "'";
                $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
            } else {
                $msg = "Le richieste di bilancio verranno attivate per il servizio: " . $data['L1ORG'] . "." . $data['L2ORG'] . "." . $data['L3ORG'] . "." . $data['L4ORG'] . " '" . $data['DESPORG'] . "'";
                $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
            }
        }
    }

}
