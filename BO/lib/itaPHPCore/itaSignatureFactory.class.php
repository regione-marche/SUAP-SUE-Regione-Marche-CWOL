<?php

require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
require_once(ITA_BASE_PATH . '/lib/itaPHPSignature/itaPHPSignatureProvider.php');
require_once(ITA_LIB_PATH . '/itaPHPSignature/itaPHPSignatureProviderPKNet.php');
require_once(ITA_LIB_PATH . '/itaPHPSignature/itaPHPSignatureProviderArubaOTP.php');

/**
 * Gestione della firma da pdf a p7m
 * @author l.pergolini
 */
class itaSignatureFactory {

    const CLASSE_FIRMA_PROVIDER_PKNET = 0;
    const CLASSE_FIRMA_PROVIDER_ARUBA = 1;
    const CLASSE_FIRMA_PROVIDER_ESTERNO = 2;

    public static function getSignature($parameters = array()) {
        if (!$parameters) {
            $parameters = self::getEnvParameters();
        }
        if (!$parameters) {
            return false;
        }
        switch ($parameters['provider']) {
            case self::CLASSE_FIRMA_PROVIDER_PKNET:
                $objfirma = new itaPHPSignatureProviderPKNet();
                break;
            case self::CLASSE_FIRMA_PROVIDER_ARUBA:
                $objfirma = new itaPHPSignatureProviderArubaOTP();
                break;
            case self::CLASSE_FIRMA_PROVIDER_ESTERNO:
              //  $objfirma = new itaPHPSignatureProviderExternal();
                break;
        }
        unset($parameters['provider']);
        if (is_array($parameters)) {
            $objfirma->setParameters($parameters);
        } elseif (is_string($parameters)) {
            $objfirma->setParametersFromJsonString($parameters);
        } else {
            return false;
        }
        return $objfirma;
    }

    private static function getEnvParameters() {
        $devLib = new devLib();
        $defaultProvider = $devLib->getEnv_config('FIRMAREMOTA', 'codice', 'FIRMA_PROVIDER', false);
        $parameters['provider'] = $defaultProvider['CONFIG'];
        switch ($parameters['provider']) {
            case self::CLASSE_FIRMA_PROVIDER_PKNET:
                self::getEnvParametersPkNet($parameters);
                break;

            case self::CLASSE_FIRMA_PROVIDER_ARUBA:
                self::getEnvParametersAruba($parameters);
                break;

            case self::CLASSE_FIRMA_PROVIDER_ESTERNO:
                self::getEnvParametersEsterno($parameters);
                break;

            default:
                break;
        }
        return $parameters;
    }

    private static function getEnvParametersPkNet(&$parameters) {
        $devLib = new devLib();
        $defaultPKnetSignMode = $devLib->getEnv_config('FIRMAREMOTAPKNET', 'codice', 'PKNET_SIGNMODE', false);
        $defaultPKnetEncoding = $devLib->getEnv_config('FIRMAREMOTAPKNET', 'codice', 'PKNET_ENCODING', false);
        $defaultPKnetFilterValidCred = $devLib->getEnv_config('FIRMAREMOTAPKNET', 'codice', 'PKNET_FILTER_VALID_CRED', false);
        $defaultMultiple = $devLib->getEnv_config('FIRMAREMOTAPKNET', 'codice', 'PKNET_MULTIPLE', false);

        $parameters['signMode'] = $defaultPKnetSignMode['CONFIG'];
        $parameters['encoding'] = $defaultPKnetEncoding['CONFIG'];
        $parameters['filterValidCred'] = $defaultPKnetFilterValidCred['CONFIG'];
        $parameters['multiple'] = $defaultMultiple['CONFIG'];
    }

    private static function getEnvParametersAruba(&$parameters) {
        
    }

    private static function getEnvParametersEsterno(&$parameters) {
        
    }

}

?>
