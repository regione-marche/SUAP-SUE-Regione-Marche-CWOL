<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

class cwbBorRuoliValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord) {
        if ($operation === itaModelService::OPERATION_INSERT) {
            if (strlen($data['KRUOLO']) > 0) {
                $filtri['KRUOLO'] = trim($data['KRUOLO']);

                $this->libDB = new cwbLibDB_BOR();
                $results = $this->libDB->leggiGeneric('BOR_RUOLI', $filtri);

                if (count($results) > 0) {
                    $msg = "Codice duplicato.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
        }
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if (strlen($data['DES_RUOLO']) === 0) {
                $msg = "Ruolo obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['ALIAS']) === 0) {
                $msg = "Alias obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
        if ($operation === itaModelService::OPERATION_DELETE) {
            $filtrivalid['KRUOLO'] = $data['KRUOLO'];
            $this->libDB = new cwbLibDB_BOR();
            $read = $this->libDB->leggiGeneric('BOR_UTEORG', $filtrivalid);
            if ($read != null) {
                $msg = "Ruolo associato ad uno o più utenti.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
