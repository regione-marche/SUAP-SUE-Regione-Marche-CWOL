<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';

class cwbBorFrmuteService extends itaModelService {

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

        $data['UTEOPER'] = strtoupper(trim(cwbParGen::getUtente()));
        $data['DATAOPER'] = $currentDate;
        $data['TIMEOPER'] = $currentTime;

        return $data;
    }

}
