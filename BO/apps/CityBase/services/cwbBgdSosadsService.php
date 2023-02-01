<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

class cwbBgdSosadsService extends itaModelService {
    protected function preOperation($DB, &$data, $operationType) {
        if ($operationType == itaModelService::OPERATION_INSERT) {
            $data["IDSOSADS"] = cwbLibCalcoli::trovaProgressivo("IDSOSADS", "BGD_SOSADS");
        }
    }

}