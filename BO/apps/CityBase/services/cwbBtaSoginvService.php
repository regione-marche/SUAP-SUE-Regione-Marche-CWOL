<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

class cwbBtaSoginvService extends itaModelService {
    public function __construct() {
        parent::__construct();
        
        $behaviors = array();
        $behaviors["AUDIT"] = array(
            "function" => array(new cwbModelServiceHelper(), "manageAudit"));
        $this->setBehaviors($behaviors);
    }
    
    protected function preOperation($DB, &$data, $operationType) {
        
        // Se non Insert leggo i valori di questa Variazione prima della Modifica
        if($operationType !== itaModelService::OPERATION_INSERT) {
        }
        
        if($operationType === itaModelService::OPERATION_INSERT) {
            // Nuovo progressivo su Tabella
            $data['ID_SOGINV'] = cwbLibCalcoli::trovaProgressivo('ID_SOGINV', 'BTA_SOGINV');
        }

        if($operationType === itaModelService::OPERATION_UPDATE){
        }

        if($operationType === itaModelService::OPERATION_DELETE){
        }
        
    }

}