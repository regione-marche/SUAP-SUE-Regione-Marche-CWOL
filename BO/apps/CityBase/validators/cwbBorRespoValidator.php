<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

class cwbBorRespoValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if (strlen($data['NOMERES']) === 0) {
                $msg = "Descrizione Responsabile obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (isSet($data['DATACESS']) && trim($data['DATACESS']) != '' && $data['DATAINIZ'] > $data['DATACESS']) {
                $msg = "La data iniziale non può essere maggiore della data cessazione.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            
            $dbLib = new cwbLibDB_BOR();
            $filtri = array('NOMERES'=>$data['NOMERES']);
            if($operation == itaModelService::OPERATION_UPDATE){
                $filtri['IDRESPO_diff'] = $data['IDRESPO'];
            }
            $respo = $dbLib->leggiGeneric('BOR_RESPO', $filtri);
            if(count($respo)>0){
                $msg = "Un responsabile con il nominativo " . $data['NOMERES'] . " è già presente.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            
            if(empty($data['CODUTE_RESP'])){
                $msg = "Non è stato definito un codice utente legato al responsabile.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
