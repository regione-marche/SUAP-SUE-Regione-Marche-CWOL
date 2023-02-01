<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

class cwbBtaViegcoValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData = null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            $libDB = new cwbLibDB_BTA();
            if (strlen($data['PROG_VCO']) === 0) {
                $msg = "Codice Via obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['DESVIA']) === 0) {
                $msg = "Descrizione Via obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['CAP']) === 0) {
                $msg = "CAP obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }

        if ($operation === itaModelService::OPERATION_INSERT) {
            $filtri['TOPONIMO'] = trim($data['TOPONIMO']);
            $filtri['DESVIA'] = trim($data['DESVIA']);
            $filtri['CODNAZPRO'] = trim($data['CODNAZPRO']);
            $filtri['CODLOCAL'] = trim($data['CODLOCAL']);
            $libDB = new cwbLibDB_BTA();
            $conta = count($libDB->leggiBtaViegcoDenominazione($filtri, true));
            if (intval($conta) > 0) {
                $msg = "Esiste già una via con la stessa denominazione.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
