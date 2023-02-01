<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

class cwbBorModorgValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        $libDB = new cwbLibDB_BOR();

        if ($operation !== itaModelService::OPERATION_DELETE) {
            if (strlen($data['DESCRIZ']) === 0) {
                $msg = "Descrizione Modello obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['DATAINIZ']) === 0) {
                $msg = "Data inizio obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if ($data['DATAFINE'] != '' && $data['DATAINIZ'] > $data['DATAFINE']) {
                $msg = "La data fine non può essere maggiore della data inizio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            //Controllo che le date non si sovrappongano
            $filtri['IDMODORG'] = (trim($data['IDMODORG'])=='' ? null : trim($data['IDMODORG']));
            $filtri['DATAINIZ'] = trim($data['DATAINIZ']);
            $filtri['DATAFINE'] = (trim($data['DATAFINE'])=='' ? null : trim($data['DATAFINE']));
            $filtri['PROGENTE'] = trim($data['PROGENTE']);
            
            $row = $libDB->leggiBorModorgCtrlDate($filtri, false, $operation);
            
            if (isSet($row['IDMODORG']) > 0) {
                $msg = "Attenzione! Esiste una sovrapposizione di date.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
