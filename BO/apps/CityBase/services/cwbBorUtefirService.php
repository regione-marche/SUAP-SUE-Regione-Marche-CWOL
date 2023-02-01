<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';

class cwbBorUtefirService extends itaModelService {

    public function setBehaviors($behaviors) {
        //eliminata gestione dell'audit perche la chiave DELLA TABELLA è "CODUTE"
        unset($behaviors["AUDIT"]);

        parent::setBehaviors($behaviors);
    }
}
