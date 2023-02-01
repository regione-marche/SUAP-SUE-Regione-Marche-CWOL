<?php
include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

class cwbBorStoresValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if($operation === itaModelService::OPERATION_INSERT || $operation === itaModelService::OPERATION_UPDATE){
            $libDB = new cwbLibDB_BOR();
            
            if(!isSet($data['DATAFINE']) || trim($data['DATAFINE']) == ''){
                $data['DATAFINE'] = null;
            }
            else{
                $data['DATAFINE'] = strtotime($data['DATAFINE']);
            }
            $data['DATAINIZ'] = strtotime($data['DATAINIZ']);
            
            if(isSet($data['DATAFINE']) && trim($data['DATAFINE']) != '' && $data['DATAINIZ'] >= $data['DATAFINE']){
                $msg = "La data di fine validità per " . $data['NOMERES'] . " deve essere maggiore della data di inizio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            
            $filtri = array(
                'IDRESPO'=>$data['IDRESPO']
            );
            $responsabile = $libDB->leggiBorRespo($filtri,false);
            
            if(!$responsabile){
                $msg = "Il responsabile non è valido.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            
            if(!isSet($responsabile['DATAFINE']) || trim($responsabile['DATAFINE']) == ''){
                $responsabile['DATAFINE'] = null;
            }
            else{
                $responsabile['DATAFINE'] = strtotime($responsabile['DATAFINE']);
            }
            $responsabile['DATAINIZ'] = strtotime($responsabile['DATAINIZ']);
            
            if($responsabile['DATAINIZ']>$data['DATAINIZ']){
                $msg = "La data di inizio di validità del responsabile ".$data['NOMERES']." è successiva alla sua data di inizio validità per la struttura organizzativa.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if(!empty($responsabile['DATAFINE']) && $responsabile['DATAFINE']<$data['DATAFINE']){
                $msg = "La data di fine di validità del responsabile ".$data['NOMERES']." è precedente alla sua data di fine validità per la struttura organizzativa.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            
        }
    }
}
