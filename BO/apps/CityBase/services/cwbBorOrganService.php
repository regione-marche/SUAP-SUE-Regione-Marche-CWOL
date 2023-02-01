<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibOrganigrammaSync.php';

class cwbBorOrganService extends itaModelService {

    protected function preOperation($DB, &$data, $operationType) {
        if ($operationType == itaModelService::OPERATION_INSERT) {
            $data["PROGINT"] = cwbLibCalcoli::trovaProgressivo("PROGINT", "BOR_ORGAN", 'PROGENTE = ' . $data["PROGENTE"]); //DA CALCOLARE CON MAX (relativo ad ente)
            $data["IDORGAN"] = cwbLibCalcoli::trovaProgressivo("IDORGAN", "BOR_ORGAN"); //DA CALCOLARE CON MAX (relativo ad ente)
            if (itaHooks::isActive('citywareHook.php')) {
                $data["ID_MODORG"] = 1;
                $data["IDBORAOO"] = 1;
            }
        }
    }

    protected function postCommit($DB, $data, $operationType) {
        $devLib = new devLib();
        $organSync = $devLib->getEnv_config('ORGAN_SYNC', 'codice', 'AUTO_SYNC', false);
        
        if(!empty($organSync['CONFIG'])){
            $data = $this->initData($data);
            
            $organigrammaSync = new cwbLibOrganigrammaSync();
            $organigrammaSync->syncStrutturaCWtoProt($data["IDORGAN"], $operationType);
        }
    }
}
