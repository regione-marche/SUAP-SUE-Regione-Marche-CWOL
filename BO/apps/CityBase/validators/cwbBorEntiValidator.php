<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBorEntiValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($tableName == "BOR_ENTI") {
            if ($operation !== itaModelService::OPERATION_DELETE) {
                if ($data['NAT_ENTE'] === 1 && (!$data['CODENTE'] || $data['CODENTE'] <> 6)) {
                    $msg = "Codice località non immesso o immesso con meno di 6 cifre!";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
                if (!$data['DESENTE']) {
                    $msg = "Descrizione obbligatoria.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
                if ($data['PROGENTE'] === 0) {
                    $msg = "Codice Ente obbligatorio.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
                if (!$data['DES_BREVE']) {
                    $msg = "Descrizione breve obbligatoria.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
                if (!$data['DATAINIZ']) {
                    $msg = "Data inizio obbligatoria.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
        } else if ($tableName == "BOR_ENTEDC") {
            if ($operation !== itaModelService::OPERATION_DELETE) {
                if (!$data['ENTE_DOCER']) {
                    $msg = "Nome ente DOCER obbligatorio.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
                if (!$data['UTENTE_DOCER']) {
                    $msg = "Codice utente DOCER obbligatorio.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
                if (!$data['PWDUTE_DOCER']) {
                    $msg = "Password utente DOCER obbligatoria.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
        }
    }

}
