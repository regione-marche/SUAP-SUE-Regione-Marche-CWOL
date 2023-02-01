<?php

require_once(ITA_BASE_PATH . '/lib/itaPHPFirmaGrafometricaNamirial/itaFirmaGrafometricaNamirial.class.php');
require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

/**
 * Interfaccia con Firma Grafometrica
 *
 * @author m.biagioli
 */
class itaFirmaGrafometrica {
    
    public static function getFirmaGrafometrica($parameters = array()) {
        if (!$parameters) {
            $parameters = self::getEnvParameters();
        }
        if(!$parameters){
            return false;
        }
        try {
            $objFirma = new itaFirmaGrafometricaNamirial();
        } catch (Exception $exc) {
            return false;
        }
        if (is_array($parameters)) {
            $objFirma->setParameters($parameters);
        } else {
            return false;
        }
        return $objFirma;
    }
    
    private static function getEnvParameters() {
        $devLib = new devLib();
        $defaultDevice = $devLib->getEnv_config('FIRMA_GRAFOMETRICA', 'codice', 'FGRAFOM_DEFAULT_DEVICE', false);
        $biometricData = $devLib->getEnv_config('FIRMA_GRAFOMETRICA', 'codice', 'FGRAFOM_BIOMETRIC_DATA', false);
        $noPdfSignInfo = $devLib->getEnv_config('FIRMA_GRAFOMETRICA', 'codice', 'FGRAFOM_NO_PDF_SIGN_INFO', false);
        $makePdfOriginal = $devLib->getEnv_config('FIRMA_GRAFOMETRICA', 'codice', 'FGRAFOM_MAKE_PDF_ORIGINAL', false);
        $saveInSameFolder = $devLib->getEnv_config('FIRMA_GRAFOMETRICA', 'codice', 'FGRAFOM_SAVE_IN_SAME_FOLDER', false);
        $forceOverwrite = $devLib->getEnv_config('FIRMA_GRAFOMETRICA', 'codice', 'FGRAFOM_FORCE_OVERWRITE', false);
        $parameters['device'] = $defaultDevice['CONFIG'];
        $parameters['biometricData'] = $biometricData['CONFIG'];
        $parameters['noPdfSignInfo'] = $noPdfSignInfo['CONFIG'];
        $parameters['makePdfOriginal'] = $makePdfOriginal['CONFIG'];
        $parameters['saveInSameFolder'] = $saveInSameFolder['CONFIG'];
        $parameters['forceOverwrite'] = $forceOverwrite['CONFIG'];
        return $parameters;
    }
    
}

?>
