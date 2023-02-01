<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

class cwbBtaCupValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData = null) {
        if ($operation === itaModelService::OPERATION_INSERT || $operation === itaModelService::OPERATION_UPDATE) {
            if (strlen($data['COD_CUP']) === 0) {
                $msg = "Codice obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            } else {
                $filtri['COD_CUP'] = trim($data['COD_CUP']);

                $this->libDB = new cwbLibDB_BTA();
                $results = $this->libDB->leggiBtaCup($filtri);

                if (count($results) > 0) {
                    foreach ($results as $result) {
                        if ($result['COD_CUP'] == $data['COD_CUP']) {
                            if ($result['PROG_CUP'] != $data['PROG_CUP']) {
                                $msg = "Codice duplicato.";
                                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                                break;
                            }
                        }
                    }
                }
            }
            if (strlen($data['DES_BREVE']) === 0) {
                $msg = "Descrizione obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['DES_CUP']) === 0) {
                $msg = "Descrizione obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['DATAINIZ']) === 0) {
                $msg = "Data inizio obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['DATAINIZ']) > 0 && strlen($data['DATAFINE']) > 0) {
                $datainizio = new DateTime($data['DATAINIZ']);
                $datafine = new DateTime($data['DATAFINE']);

                if ($datafine < $datainizio) {
                    $msg = "Controllare la data fine";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
            if (strlen($data['IDORGAN']) === 0) {
                $msg = "Servizio Richiedente obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['CODUTE_RUP']) === 0) {
                $msg = "RUP Firma obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
            }
        }
    }

}
