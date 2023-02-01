<?php

require_once(ITA_BASE_PATH . '/lib/itaPHPScanner/itaScannerClient.class.php');
require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

/**
 * Interfaccia con Scanner
 * @author l.pergolini
 */
class itaScanner {
    /**
     * Apre la finestra in una tab
     */
    const ITASCANNER_TAB = 0;
    /**
     * Apre la finestra in una modale
     */
    const ITASCANNER_MODAL = 1;
    /**
     * Non apre nessuna finestra, obbligatorio specificare il path di salvataggio del pdf
     */
    const ITASCANNER_NONE = 2;
    
    const ITASCANNER_DRIVER_WIA = 0;
    const ITASCANNER_DRIVER_TWAIN = 1;
    const ITASCANNER_DRIVER_ISIS = 2;
    
    public static function getScan($parameters = array()) {
        if (!$parameters) {
            $parameters = self::getEnvParameters();
        }
        if(!$parameters){
            return false;
        }
        try {
            $objScanner = new itaScannerClient();
        } catch (Exception $exc) {
            return false;
        }
        if (is_array($parameters)) {
            $objScanner->setParameters($parameters);
        } elseif (is_string($parameters)) {
            $objScanner->setParametersFromJsonString($parameters);   
         } else {
            return false;
        }
        return $objScanner;
    }
    
    private static function getEnvParameters() {
        $devLib = new devLib();
        $defaultProviderComunication = $devLib->getEnv_config('SCANNER', 'codice', 'SCANNER_PROVIDER', false);
        $defaultProtocolComunication = $devLib->getEnv_config('SCANNER', 'codice', 'SCANNER_PROTOCOLLO', false);
        $forcePdfDocument = $devLib->getEnv_config('SCANNER', 'codice', 'SCANNER_FORCEPDF', false);
        $quality = $devLib->getEnv_config('SCANNER', 'codice', 'SCANNER_QUALITY', false);
        $color = $devLib->getEnv_config('SCANNER', 'codice', 'SCANNER_COLOR', false);
        $forceClose = $devLib->getEnv_config('SCANNER', 'codice', 'SCANNER_FORCECLOSE', false);
        $showUi = $devLib->getEnv_config('SCANNER', 'codice', 'SCANNER_SHOWUI', false);
        $parameters['provider'] = $defaultProviderComunication['CONFIG'];
        $parameters['smartagent_protocol'] = $defaultProtocolComunication['CONFIG'];
        $parameters['smartagent_forcePdf'] = $forcePdfDocument['CONFIG'];
        $parameters['smartagent_color'] = $color['CONFIG'];
        $parameters['smartagent_quality'] = $quality['CONFIG'];
        $parameters['smartagent_forceClose'] = $forceClose['CONFIG'];
        $parameters['smartagent_showUi'] = $showUi['CONFIG'];
        return $parameters;
    }
    
    /**
     * Permette di aprire la finestra di dialogo dello scanner e ricevere la scansione come callback su un model a scelta
     * @param string $model Model a cui inviare la callback
     * @param string $event (facoltativo) evento da chiamare sul model
     * @param int $scannerDriver tipo di driver da usare (itaScanner::ITASCANNER_DRIVER_*)
     * @param array $parameters array dei parametri addizionali per lo scanner
     */
    public static function scanCallback($model, $event='scanCallback', $scannerDriver=null, $parameters=array()){
        $scanner = self::getScan();
        $scanner->setParameters($parameters);
        
        $return = array(
            'returnForm'=>$model,
            'returnId'=>'scan',
            'returnEvent'=>$event
        );
        $scanner->scan($return,$scannerDriver,1);
    }
    
    /**
     * Permette di interfacciarsi allo scanner usando l'utility scannerService
     * @param int $mode Modalità d'uso dello scanner:
     *                  itaScanner::ITASCANNER_TAB: apre in una nuova tab.
     *                  itaScanner::ITASCANNER_MODAL: apre in una nuova finestra modale.
     *                  itaScanner::ITASCANNER_NONE: non apre nessuna finestra e salva direttamente su disco il pdf (richiede $savePath valorizzato)
     * @param int $scannerDriver Driver con cui effettuare la scansione:
     *                  itaScanner::ITASCANNER_DRIVER_WIA
     *                  itaScanner::ITASCANNER_DRIVER_TWAIN
     *                  itaScanner::ITASCANNER_DRIVER_ISIS
     * @param array $parameters Parametri opzionali per l'uso dello scanner
     *                  'provider'=>
     *                  'smartagent_protocol=>
     *                  'smartagent_forcePdf=>
     *                  'smartagent_color=>
     *                  'smartagent_quality=>
     *                  'smartagent_forceClose=>
     *                  'smartagent_showUi'=>
     * @param string $savePath path dove salvare il file (completo di nome del file ed estensione, eg: 'C:\test.pdf')
     * @param boolean $allowDownload Flag che indica se l'utente può salvare o meno in locale il documento scannerizzato
     * @throws ItaException
     */
    public static function scannerService($mode=self::ITASCANNER_TAB, $scannerDriver=null, $parameters=array(), $savePath=null, $allowDownload=false){
        if($mode === self::ITASCANNER_NONE && !isSet($savePath)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Se non si apre una finestra di visualizzazione della scansione'
                    . 'effettuata risulta necessario specificare il path di salvataggio del file');
        }
        
        $model = 'utiScannerService';
        
        $modelObj = itaModel::getInstance($model);
        $scannerData = json_encode(array(
            'viewMode'=>$mode,
            'savePath'=>$savePath,
            'allowDownload'=>$allowDownload
        ));
        
        $scanner = self::getScan();
        $scanner->setParameters($parameters);
        
        $return = array(
            'returnForm'=>$model,
            'returnId'=>$scannerData,
            'returnEvent'=>'scanCallback'
        );
        $scanner->scan($return,$scannerDriver,1);

    }
}

?>
