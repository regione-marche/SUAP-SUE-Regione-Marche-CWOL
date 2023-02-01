<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

class cwbBtaDurcalService extends itaModelService {
    
    public function __construct() {
        parent::__construct();
        
        $behaviors = array();
        $behaviors["AUDIT"] = array(
            "function" => array(new cwbModelServiceHelper(), "manageAudit"));
        $this->setBehaviors($behaviors);
    }
    
    protected function preOperation($DB, &$data, $operationType) {
        if ($operationType === itaModelService::OPERATION_INSERT) {
            $data['RIGA_DURC'] = cwbLibCalcoli::trovaProgressivo('RIGA_DURC', 'BTA_DURCAL', 'PROG_DURC = ' . $data['PROG_DURC']);
        }
    }
}