<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBgdDocAlValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {

        if ($operation !== itaModelService::OPERATION_DELETE) {
            if(!$data['TIPO_COM']){
                $msg = "Inserire tipo Comunicazione.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            } 
            // TODO VALUTARE QUESTO CONTROLLO
            // per convenzione l'allegato va appoggiato su pathAllegato per poi salvarlo sul documentale
            // quindi faccio una verifica anche su questo  (se c'è uuid sono in modifica e non ho pathAllegato)           
            if(!$data['pathAllegato'] && !$data['UUID_DOC']){
                $msg = "Inserire Allegato.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
