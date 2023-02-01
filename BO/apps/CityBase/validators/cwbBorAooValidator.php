<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

class cwbBorAooValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($tableName == "BOR_AOO") {
            if($operation === itaModelService::OPERATION_DELETE){
                $libDB = new cwbLibDB_BOR();
                $filtri = array(
                    'IDBORAOO'=>$data['IDAOO']
                );
                $data = $libDB->leggiBorOrgan($filtri, false);
                if(!empty($data)){
                    $msg = "Il codice AOO è in uso nelle strutture organizzative.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
            if ($operation !== itaModelService::OPERATION_DELETE) {
                if (strlen($data['CODAOOIPA']) === 0) {
                    $msg = "Codice obbligatorio.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
                if (strlen($data['DESAOO']) === 0) {
                    $msg = "Descrizione obbligatoria.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
                if ($data['NATURA'] === 0) {
                    $msg = "Natura obbligatoria.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
        } else if ($tableName == "BOR_AOODC") {
            if (strlen($data['AOO_DOCER']) === 0) {
                $msg = "Codice AOO Docer obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
