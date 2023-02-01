<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

class cwbBpologService extends itaModelService {
    protected function preOperation($DB, &$data, $operationType) {
        if ($operationType == itaModelService::OPERATION_INSERT) {
            $data["ID"] = cwbLibCalcoli::trovaProgressivo("ID", "BPOLOG"); //DA CALCOLARE CON MAX 
            $data["DATAEVENTO"]  = cwbLibCode::getCurrentDate(1);
            $data["ORA"]  = date('H:i:s');
        }
    }

}