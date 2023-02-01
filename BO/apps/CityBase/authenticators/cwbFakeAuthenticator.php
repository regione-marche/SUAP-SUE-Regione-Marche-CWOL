<?php

include_once ITA_BASE_PATH . '/apps/CityBase/authenticators/cwbBaseAuthenticator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

/**
 * Authenticator che ritorna true per tutte le azioni
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class cwbFakeAuthenticator extends cwbBaseAuthenticator {
    
    public function isActionAllowed($actionType) {
        return true;
    }
    
}
