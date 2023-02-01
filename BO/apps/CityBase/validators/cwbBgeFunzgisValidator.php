<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';

class cwbBgeFunzgisValidator extends cwbBaseValidator {
        
    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        $libDB = new cwbLibDB_BGE(); 
        if ($operation !== itaModelService::OPERATION_DELETE) {
           //Controlla campo descrizione
            if (strlen($data['FUNZIONE']) === 0) {
                $msg = "Inserire il nome funzione.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            $filtri['FUNZIONE'] = $data['FUNZIONE'];
            $trovato = $libDB->leggiBgeFunzgis($filtri, true);
            if ($trovato) {
                $msg = "Esiste già una definizione con stesso nome funzione.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            if (strlen($data['DESCRIZ']) === 0) {
                $msg = "Inserire la descrizione";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }
}
