<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbAuthHelper.php';

class cwbBorUteorgService extends itaModelService {
    public function __construct() {
        parent::__construct();
        
        $behaviors = array();
        $behaviors["AUDIT"] = array(
            "function" => array(new cwbModelServiceHelper(), "manageAudit"));
        $this->setBehaviors($behaviors);
    }
    
    public function setBehaviors($behaviors) {
        //eliminata gestione dell'audit perche la chiave DELLA TABELLA è "CODUTE"
        unset($behaviors["AUDIT"]);
        $behaviors["AUDIT"] = array(
            "function" => array($this, "manageAudit"));

        parent::setBehaviors($behaviors);
    }

    public function manageAudit($operationType, $tableDef, $data) {
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');

        $data['DATAOPER'] = $currentDate;
        $data['TIMEOPER'] = $currentTime;

        return $data;
    }
    
    protected function preOperation($DB, &$data, $operationType) {
        if ($operationType === itaModelService::OPERATION_INSERT) {
            $data["PROGENTE"] = cwbParGen::getProgEnte();
        }
    }

    protected function customOperation($DB, $data, $operationType) {
        $authHelper = new cwbAuthHelper();
        $authHelper->clearAuthCache($data['CODUTE']);
    }
}