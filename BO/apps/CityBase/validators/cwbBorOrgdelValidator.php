<?php
include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBorOrgdelValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if($operation === itaModelService::OPERATION_INSERT || $operation === itaModelService::OPERATION_UPDATE){
            if(empty($data['TIPODELIB'])){
                $msg = "E' necessario valorizzare il tipo di delibera.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if(empty($data['DES_ORDE'])){
                $msg = "E' necessario valorizzare la descrizione.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            
            if($data['FLAG_NALS'] == 1 && empty($data['COD_NR_DS'])){
                $msg = "Se l'incremento automatico del numero di liquidazione è impostato con numerazione unica per tipo atto risulta necessario specificare un numeratore.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if($data['FLAG_NALR'] == 1 && empty($data['COD_NR_DE'])){
                $msg = "Se l'incremento automatico del numero di riscossione è impostato con numerazione unica per tipo atto risulta necessario specificare un numeratore.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            
//            if(!empty($data['COD_NR_DS']) && $data['COD_NR_DS'] == $data['COD_NR_DE']){
//                $msg = "Non può essere utilizzato lo stesso numeratore sia per gli atti di liquidazione e di riscossioni.";
//                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
//            }
        }
    }
}
