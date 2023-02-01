<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';

class cwbBtaTipcomValidator extends cwbBaseValidator {
    private $libDB;
    
    public function __construct() {
        parent::__construct();
        
        $this->libDB = new cwbLibDB_GENERIC();
    }
    
    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if(strlen($data['TIPO_COM']) === 0){
                $msg = "Codice obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if(strlen($data['DES_TIPCOM']) === 0){
                $msg = "Descrizione obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if ($operation === itaModelService::OPERATION_UPDATE) {
                if ($oldCurrentRecord['FLAG_TIPCO'] != $data['FLAG_TIPCO']){
                    $filtri = array();
                    $filtri['TIPO_COM'] = $data['TIPO_COM'];
                    $btaSoginv = $this->libDB->leggiGeneric('BTA_SOGINV', $filtri);
                    
                    if (!empty($btaSoginv) && count($btaSoginv)>0){
                        $msg = "Non è possibile modificare la tipologia della comunicazione, comunicazione già utilizzata.";
                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                    }
                }   //  if ($oldCurrentRecord['FLAG_TIPCO'] != $data['FLAG_TIPCO']){
            }
        }
    }
}
