<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

class cwbBgdSostagService extends itaModelService {
    protected function preOperation($DB, &$data, $operationType) {
        if ($operationType == itaModelService::OPERATION_INSERT) {
            $data["IDSOSTAG"] = cwbLibCalcoli::trovaProgressivo("IDSOSTAG", "BGD_SOSTAG");
        }
    }

}