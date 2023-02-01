<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

class cwbBorStoresService extends itaModelService {
    protected function preOperation($DB, &$data, $operationType) {
        if ($operationType === itaModelService::OPERATION_INSERT) {
            $data["PROGENTE"] = cwbParGen::getProgEnte();
            $data["PROGINT"] = cwbLibCalcoli::trovaProgressivo("PROGINT", "BOR_STORES", 'PROGENTE = '.$data["PROGENTE"],''); //DA CALCOLARE CON MAX (relativo ad ente)
            $data["PROGINTRES"] = 1; //Pare forzato ad 1, da calcolare?
            $data["IDSTORES"]  = cwbLibCalcoli::trovaProgressivo("IDSTORES", "BOR_STORES", false, ''); //DA CALCOLARE CON MAX (relativo ad ente)
        }
    }

}