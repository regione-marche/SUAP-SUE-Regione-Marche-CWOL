<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaAuthenticatorFactory.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelHelper.class.php';

/**
 * Authenticator Factory specifica di Cityware
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class cwbAuthenticatorFactory extends itaAuthenticatorFactory {        
    
    public static function getAuthenticator($model, $params) {
        return itaModelHelper::findClassAuthenticator($model, $params);
    }

}

?>