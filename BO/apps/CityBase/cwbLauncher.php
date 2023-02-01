<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaShellExec.class.php';

function cwbLauncher() {
    $cwbLauncher = new cwbLauncher();
    $cwbLauncher->parseEvent();
    return;
}

class cwbLauncher extends itaFrontControllerCW {
         
    
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_btnApriOmnis':
                        $this->apriCitywareDev();
                        break;
                    case $this->nameForm . '_btnApriCityware':
                        $this->apriCitywareRt();
                        break;
                    case $this->nameForm . '_btnApriCityFinancingFinanziaria':
                        $this->apriCityFinancingFinanziaria();
                        break;
                    case $this->nameForm . '_btnApriCityFinancingIva':
                        $this->apriCityFinancingIva();
                        break;
                    case $this->nameForm . '_btnApriCityPeopleAnagrafe':
                        $this->apriCityPeopleAnagrafe();
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    private function apriCitywareDev() {
        $this->apriCityware("C:/Works/Cityware/OS431/omnis.exe");        
    }
    
    private function apriCitywareRt() {
        $this->apriCityware("C:/Program Files (x86)/Cityware/cityware.exe");
    }
    
    private function apriCityFinancingFinanziaria() {
        $params = "MENU:FINANCING_Finanziaria";
        $this->apriCityware("C:/Program Files (x86)/Cityware/cityware.exe", $params);
    }
    
    private function apriCityFinancingIva() {
        $params = "MENU:FINANCING_IVA";
        $this->apriCityware("C:/Program Files (x86)/Cityware/cityware.exe", $params);
    }
    
    private function apriCityPeopleAnagrafe() {
        $params = "MENU:PEOPLE_Anagrafe";
        $this->apriCityware("C:/Program Files (x86)/Cityware/cityware.exe", $params);
    }
    
    private function apriCityware($pathExecutable, $params = '') {
        $token = App::$utente->getKey('TOKEN'); 
        $usr = App::$utente->getKey('nomeUtente');
        $source = 'itaEngine';        
        $cwToken = base64_encode("$usr|$source|$token|$params");         
        $args = "/-XAUTH=$cwToken";
        itaShellExec::shellExec($pathExecutable, $args);
    }
    
}

?>