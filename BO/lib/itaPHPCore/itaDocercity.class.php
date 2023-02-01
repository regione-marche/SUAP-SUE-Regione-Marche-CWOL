<?php

require_once(ITA_BASE_PATH . '/lib/itaPHPDocercity/itaDocercityClient.class.php');
require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

class itaDocercity {

    public static function getDocercityClient($parameters = array()) {
        if (!$parameters) {
            $parameters = self::getEnvParameters();
        }
        if (!$parameters) {
            return false;
        }
        try {
            $DocercityObj = new itaDocercityClient();
        } catch (Exception $exc) {
            return false;
        }
        if (is_array($parameters)) {
            $DocercityObj->setParameters($parameters);
        } elseif (is_string($parameters)) {
            $DocercityObj->setParametersFromJsonString($parameters);
        } else {
            return false;
        }
        return $DocercityObj;
    }

    private static function getEnvParameters() {
        $devLib = new devLib();
        $configHost = $devLib->getEnv_config('DOCERCITY', 'codice', 'DOCERCITY_HOST', false);
        $configPath = $devLib->getEnv_config('DOCERCITY', 'codice', 'DOCERCITY_PATH', false);
        $parameters['docercityHost'] = $configHost['CONFIG'];
        $parameters['docercityPath'] = $configPath['CONFIG'];
        return $parameters;
    }

}

?>
