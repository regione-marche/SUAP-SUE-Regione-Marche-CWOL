<?php

require_once(ITA_BASE_PATH . '/lib/itaPHPAlfcity/itaAlfcityClient.class.php');
require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

class itaAlfcity {

    public static function getAlfcityClient($parameters = array()) {
        if (!$parameters) {
            $parameters = self::getEnvParameters();
        }
        if (!$parameters) {
            return false;
        }
        try {
            $AlfcityObj = new itaAlfCityClient();
        } catch (Exception $exc) {
            return false;
        }
        if (is_array($parameters)) {
            $AlfcityObj->setParameters($parameters);
        } elseif (is_string($parameters)) {
            $AlfcityObj->setParametersFromJsonString($parameters);
        } else {
            return false;
        }
        return $AlfcityObj;
    }

    private static function getEnvParameters() {
        $devLib = new devLib();
        $configHost = $devLib->getEnv_config('ALFCITY', 'codice', 'ALFCITY_HOST', false);
        $configPath = $devLib->getEnv_config('ALFCITY', 'codice', 'ALFCITY_PATH', false);
        $parameters['alfcityHost'] = $configHost['CONFIG'];
        $parameters['alfcityPath'] = $configPath['CONFIG'];
        return $parameters;
    }

}

?>
