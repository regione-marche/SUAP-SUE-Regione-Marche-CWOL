<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

class cwbBorAooService extends itaModelService {
    protected function preOperation($DB, &$data, $operationType) {
        if ($operationType === itaModelService::OPERATION_INSERT) {
            $data["PROGENTE"] = cwbParGen::getProgEnte();
            $data["IDAOO"] = cwbLibCalcoli::trovaProgressivo("IDAOO", "BOR_AOO");
        }
    }

}