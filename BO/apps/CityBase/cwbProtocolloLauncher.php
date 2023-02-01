<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';

function cwbProtocolloLauncher() {
    $cwbProtocolloLauncher = new cwbProtocolloLauncher();
    $cwbProtocolloLauncher->parseEvent();
    return;
}

class cwbProtocolloLauncher extends itaFrontControllerCW {

    public function parseEvent() {
        $this->apriProtocollo();
    }

    private function apriProtocollo() {
        $devLib = new devLib();
        $urlCfg = $devLib->getEnv_config('PROTOCOLLO_LAUNCHER', 'codice', 'URL_PRLA', false);
        $url = $urlCfg['CONFIG'];
        $ssoCfg = $devLib->getEnv_config('PROTOCOLLO_LAUNCHER', 'codice', 'SSO_PRLA', false);
        $sso = $ssoCfg['CONFIG'];

        if ($sso == 1) {
            $params = '?utente=' . cwbParGen::getUtente() . '&token=???';
            
            $url .= $params;
        }
        
        Out::codice("window.open('" . $url . "','_Blank')");
    }

}

