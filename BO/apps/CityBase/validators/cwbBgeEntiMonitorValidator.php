<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE_MONITOR.class.php';

class cwbBgeEntiMonitorValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if (!$data['ENTE']) {
            $msg = "Ente Obbligatorio!";
            $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
        } else {
            $lib = new cwbLibDB_BGE_MONITOR();
            $ente = $lib->leggiBgeEnti(array('ENTE' => $data['ENTE']));
            if ($ente) {
                $msg = "Ente Già Inserito!";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
        if (!$data['DESENTE']) {
            $msg = "Descrizione Ente Obbligatoria!";
            $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
        }
    }

}
