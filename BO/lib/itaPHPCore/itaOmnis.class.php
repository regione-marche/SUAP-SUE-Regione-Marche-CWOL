<?php

require_once(ITA_BASE_PATH . '/lib/itaPHPOmnis/itaOmnisClient.class.php');
require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

class itaOmnis {

    public static function getOmnisClient($parameters = array()) {
        if (!$parameters) {
            $parameters = self::getEnvParameters();
        }
        if(!$parameters){
            return false;
        }
        try {
            $OmnisObj = new itaOmnisClient();
        } catch (Exception $exc) {
            return false;
        }
        if (is_array($parameters)) {
            $OmnisObj->setParameters($parameters);
        } elseif (is_string($parameters)) {
            $OmnisObj->setParametersFromJsonString($parameters);
        } else {
            return false;
        }
        return $OmnisObj;
    }

    private static function getEnvParameters() {
        $devLib = new devLib();
        $configProtocol = $devLib->getEnv_config('OMNIS', 'codice', 'PROTOCOL', false);
        $configWsUrl = $devLib->getEnv_config('OMNIS', 'codice', 'WEB_SERVER_URL', false);
        $configAppServerUrl = $devLib->getEnv_config('OMNIS', 'codice', 'APP_SERVER_URL', false);
        $configOmnisCGI = $devLib->getEnv_config('OMNIS', 'codice', 'OMNIS_CGI', false);
        $configDefaultLib = $devLib->getEnv_config('OMNIS', 'codice', 'DEFAULT_LIBRARY', false);
        $configRemoteTask = $devLib->getEnv_config('OMNIS', 'codice', 'REMOTE_TASK', false);
        $configRemoteTaskInt = $devLib->getEnv_config('OMNIS', 'codice', 'REMOTE_TASK_INT', false);
        $configParHexFormat = $devLib->getEnv_config('OMNIS', 'codice', 'PAR_HEX_FORMAT', false);
        $configOwsEnabled = $devLib->getEnv_config('OMNIS', 'codice', 'OWS_ENABLED', false);
        $parameters['protocol'] = $configProtocol['CONFIG'];
        $parameters['webServerUrl'] = $configWsUrl['CONFIG'];
        $parameters['appServerUrl'] = $configAppServerUrl['CONFIG'];
        $parameters['omnisCGI'] = $configOmnisCGI['CONFIG'];
        $parameters['defaultLibrary'] = $configDefaultLib['CONFIG'];
        $parameters['remoteTask'] = $configRemoteTask['CONFIG'];
        $parameters['remoteTaskInt'] = $configRemoteTaskInt['CONFIG'];
        $parameters['parHexFormat'] = $configParHexFormat['CONFIG'];
        $parameters['owsEnabled'] = $configOwsEnabled['CONFIG'];
        return $parameters;
    }

}

?>
