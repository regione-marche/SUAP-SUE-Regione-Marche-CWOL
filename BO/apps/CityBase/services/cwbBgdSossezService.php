<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

class cwbBgdSossezService extends itaModelService {
    protected function preOperation($DB, &$data, $operationType) {
        if ($operationType == itaModelService::OPERATION_INSERT) {
            $data["IDSOSSEZ"] = cwbLibCalcoli::trovaProgressivo("IDSOSSEZ", "BGD_SOSSEZ");
        }
    }

}