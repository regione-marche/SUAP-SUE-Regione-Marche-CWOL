<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

class cwbBgeExceldService extends itaModelService {
    public function __construct() {
        parent::__construct();
        
        $behaviors = array();
        $behaviors["AUDIT"] = array(
            "function" => array(new cwbModelServiceHelper(), "manageAudit"));
        $this->setBehaviors($behaviors);
    }
    
    protected function preOperation($DB, &$data, $operationType) {
        if ($operationType == itaModelService::OPERATION_INSERT && empty($data['PROGINT'])) {
            $data["PROGINT"]  = cwbLibCalcoli::trovaProgressivo("PROGINT", "BGE_EXCELD");
        }
    }
}