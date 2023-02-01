<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

class cwbBtaCupService extends itaModelService {
    protected function preOperation($DB, &$data, $operationType) {
        if ($operationType === itaModelService::OPERATION_INSERT) {
            $data["PROG_CUP"]  = cwbLibCalcoli::trovaProgressivo("PROG_CUP", "BTA_CUP");
        }
    }
}