<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';

class cwbBorAututeService extends itaModelService {
    public function __construct() {
        parent::__construct();
        
        $behaviors = array();
        $behaviors["AUDIT"] = array(
            "function" => array(new cwbModelServiceHelper(), "manageAudit"));
        $this->setBehaviors($behaviors);
    }
    
    public function setBehaviors($behaviors) {
        // Eliminata gestione dell'audit perchè la chiave DELLA TABELLA è "CODUTE"
        unset($behaviors["AUDIT"]);
        $behaviors["AUDIT"] = array(
            "function" => array($this, "manageAudit"));

        parent::setBehaviors($behaviors);
    }

    public function manageAudit($operationType, $tableDef, $data) {
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');

        $data['CODUTEOPER'] = cwbParGen::getSessionVar('nomeUtente');
        $data['DATAOPER'] = $currentDate;
        $data['TIMEOPER'] = $currentTime;

        return $data;
    }

}