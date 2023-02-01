<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';

class cwbBgeWfparamsValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord) {
        if ($operation === itaModelService::OPERATION_INSERT) {
            if (strlen($data['CONTESTO_APP']) > 0) {
                $filtri['CONTESTO_APP'] = trim($data['CONTESTO_APP']);

                $this->libDB = new cwbLibDB_BGE();
                $results = $this->libDB->leggiGeneric('BGE_WFPARAMS', $filtri);

                if (count($results) > 0) {
                    $msg = "Codice duplicato.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
        }
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if (strlen($data['CONTESTO_APP']) === 0) {
                $msg = "Contesto applicativo non valorizzato.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['GESWFPRO']) === 0) {
                $msg = "Codice procedimento workflow non valorizzato.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
