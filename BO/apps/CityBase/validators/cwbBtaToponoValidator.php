<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

class cwbBtaToponoValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            $filtri['TOPONKES'] = trim($data['TOPONKES']);
            $filtri['TOPONIMO'] = trim($data['TOPONIMO']);
            $libDB = new cwbLibDB_BTA();
            $conta = $libDB->leggiBtaTopono($filtri, true);
            // se sono in modifica conta trova me stesso quindi lo devo escludere       
            if (($operation === itaModelService::OPERATION_UPDATE && count($conta)>1) || ($operation === itaModelService::OPERATION_INSERT && $conta)) {
                $msg = "Esiste un altro toponimo" . ' (' . trim($progr['TOPONIMO']) . ") con la stessa chiave catasto.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
