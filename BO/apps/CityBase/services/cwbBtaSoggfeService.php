<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

class cwbBtaSoggfeService extends itaModelService {
    public function __construct() {
        parent::__construct();
        
        $behaviors = array();
        $behaviors["AUDIT"] = array(
            "function" => array(new cwbModelServiceHelper(), "manageAudit"));
        $this->setBehaviors($behaviors);
    }
    
    protected function preOperation($DB, &$data, $operationType) {
        if ($operationType == itaModelService::OPERATION_INSERT) {
            $data["ID_SOGGFE"]  = cwbLibCalcoli::trovaProgressivo("ID_SOGGFE", "BTA_SOGGFE");
        }
        
        if($data['TIPO_FORMATO'] == 2 || $data['TIPO_FORMATO'] == 3){
            $data['CODUFF_FE'] = '0000000';
        }
    }
}