<?php
include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

class cwbBorUtelivValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {

        if($operation === itaModelService::OPERATION_INSERT || $operation === itaModelService::OPERATION_UPDATE){
            if(!isSet($data['DATAINIZ']) || trim($data['DATAINIZ']) == ''){
                $msg = "E' necessario valorizzare la data di inizio validità.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            $data['DATAINIZ'] = preg_replace("/[^0-9]/","",$data['DATAINIZ']);
            
            if(!isSet($data['DATAFINE']) || trim($data['DATAFINE']) == ''){
                $data['DATAFINE'] = null;
            }
            else{
                $data['DATAFINE'] = preg_replace("/[^0-9]/","",$data['DATAFINE']);
            }
            
            if(isSet($data['DATAFINE']) && $data['DATAFINE'] <= $data['DATAINIZ']){
                $msg = "La data di inizio validità non può essere maggiore o uguale della data di fine validità.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            
            if(!isSet($data['IDLIVELL']) || $data['IDLIVELL'] == 0){
                $msg = "E' necessario specificare un livello retributivo.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }
}
