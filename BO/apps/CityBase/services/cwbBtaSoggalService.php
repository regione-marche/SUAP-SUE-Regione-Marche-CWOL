<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

class cwbBtaSoggalService extends itaModelService {
    
    public function __construct() {
        parent::__construct();
        
        $behaviors = array();
        $behaviors["AUDIT"] = array(
            "function" => array(new cwbModelServiceHelper(), "manageAudit"));
        $this->setBehaviors($behaviors);
    }
    
    protected function preOperation($DB, &$data, $operationType) {
        if ($operationType === itaModelService::OPERATION_INSERT) {
            $data['RIGA_ALLEG'] = cwbLibCalcoli::trovaProgressivo('RIGA_ALLEG', 'BTA_SOGGAL', 'PROGSOGG = ' . $data['PROGSOGG']);
        }
    }
}