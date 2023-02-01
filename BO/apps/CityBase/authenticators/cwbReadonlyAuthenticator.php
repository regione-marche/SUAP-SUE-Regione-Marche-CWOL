<?php

include_once ITA_BASE_PATH . '/apps/CityBase/authenticators/cwbBaseAuthenticator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

/**
 * Authenticator di sola lettura per tabelle di default Cityware
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class cwbReadonlyAuthenticator extends cwbBaseAuthenticator {
    
    public function isActionAllowed($actionType) {
        return $actionType === itaAuthenticator::ACTION_READ;
    }
    
}
