<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

class cwbBtaCivintService extends itaModelService {
    public function calcSequence($DB, $tableDef, &$toSave) {
        $toSave["PROGINT"] = cwbLibCalcoli::trovaProgressivo("PROGINT", "BTA_CIVINT", 'PROGNCIV = '.$toSave["PROGNCIV"]);
    }

}