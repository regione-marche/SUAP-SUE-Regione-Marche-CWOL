<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

class cwbBtaNciviValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData = null) {
        $libDB = new cwbLibDB_BTA();
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if ($data['NUMCIV'] === 0 && $data['SUBNCI_3'] = ' ') {
                if ($data['SUBNCIV'] === 0) {
                    $msg = "N°Civico obbligatorio, il valore 0 è ammesso solo se indicato il sottonumero (es. SNC)";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
            $filtri['CODVIA'] = trim($data['CODVIA']);
            $filtri['NUMCIV'] = trim($data['NUMCIV']);
            $conta = $libDB->leggiBtaNciviCodviaNumciv($filtri, true);
            if (is_array($conta) && !empty($conta)) {
                if ($conta[0]['NUMCIV'] > 0) {
                    $msg = "Il numero civico indicato esiste già";
                } else if ($conta[0]['NUMCIV'] == 0) {
                    $msg = "Non è ammessa l'esistenza di più di un numero civico  a 0 per la stessa via";
                }
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
