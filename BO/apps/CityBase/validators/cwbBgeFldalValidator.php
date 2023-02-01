<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';

class cwbBgeFldalValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData = null) {
        $libDB = new cwbLibDB_BGE();
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if (!$data['CODAREAMA']) {
                $msg = "Codice Area obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            } else {
                $conta = $libDB->leggiBgeFldal(array('CODAREAMA' => $data['CODAREAMA']));
                // se sono in modifica conta trova me stesso quindi lo devo escludere       
                if (($operation === itaModelService::OPERATION_UPDATE && count($conta) > 1) || ($operation === itaModelService::OPERATION_INSERT && $conta)) {
                    $msg = "Codice area già usato.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
            if (!$data['CARTELLA']) {
                $msg = "Cartella Condivisa Obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
