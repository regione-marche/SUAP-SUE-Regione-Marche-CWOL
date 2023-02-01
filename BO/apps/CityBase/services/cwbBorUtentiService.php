<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

class cwbBorUtentiService extends itaModelService {

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
        if ($operationType == itaModelService::OPERATION_INSERT) {
            $data["IDUTENTE"] = cwbLibCalcoli::trovaProgressivo("IDUTENTE", "BOR_UTENTI");
        }
    }

}
