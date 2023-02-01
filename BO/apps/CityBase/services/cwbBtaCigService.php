<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

class cwbBtaCigService extends itaModelService {
    protected function preOperation($DB, &$data, $operationType) {
        if ($operationType === itaModelService::OPERATION_INSERT) {
            $data["PROG_CIG"]  = cwbLibCalcoli::trovaProgressivo("PROG_CIG", "BTA_CIG");
        }
    }
}