<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaShellExec.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Menu/menLib.class.php';

function cwbLauncher() {
    $cwbLauncherMenu = new cwbLauncherMenu();
    $cwbLauncherMenu->parseEvent();
    return;
}

class cwbLauncherMenu extends itaFrontControllerCW {
    
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->apriCityware($_POST['menuItem']);
                break;
            case 'returnFromCityware':
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
    
    public static function execute($cmd) {
        try {
            $modelObj = new self();
            $_POST['menuItem'] = $cmd;
            $modelObj->parseEvent();            
        } catch (Exception $ex) {
            Out::msgStop("Errore", $ex->getMessage());
        }
    }
    
    private function apriCityware($params = '') {
        $devLib = new devLib();
        $shellOpenKeyCfg = $devLib->getEnv_config('CITYWARE', 'codice', 'SHELL_OPEN_KEY', false);
        $shellOpenKey = $shellOpenKeyCfg['CONFIG'];
        $cwModeCfg = $devLib->getEnv_config('CITYWARE', 'codice', 'CW_MODE', false);
        $cwMode = $cwModeCfg['CONFIG'];
        
        if (!$shellOpenKey) {
            Out::msgStop('CITYWARE', "Percorso di Cityware non impostato!");
            return;
        }

        $token = App::$utente->getKey('TOKEN');
        $usr = App::$utente->getKey('nomeUtente');
        $source = 'itaEngine';
        $cwToken = base64_encode("$usr|$source|$token|$params");
        $args = "/-XAUTH=$cwToken";

        //Aggiunta alias connessione cityware per multi tenant 
        //passaggio file ini per decidere a quale db connettermi sfruttando le connessioniIntelligenti di cityware 
        $cwConnectionAliasCfg = $devLib->getEnv_config('CITYWARE', 'codice', 'CONNECTION_ALIAS', false);
        $cwConnectionAlias = $cwConnectionAliasCfg['CONFIG'];

        if ($cwConnectionAlias) {
            $args.= "/-XCONNECTION=". $cwConnectionAlias;
        }                
        
        switch ($cwMode) {
            case 'r':
                itaShellExec::remoteAppExec($shellOpenKey, $args);
                break;
            case 'm':
                $options = array();
                $options['REMOTE_APP_PATH'] = $shellOpenKey;
                $options['REMOTE_APP_ARGS'] = $args;
                $myrtillePathCfg = $devLib->getEnv_config('CITYWARE', 'codice', 'MYRTILLE_PATH', false);
                $options['MYRTILLE_PATH'] = $myrtillePathCfg['CONFIG'];
                $myrtilleDomainCfg = $devLib->getEnv_config('CITYWARE', 'codice', 'MYRTILLE_DOMAIN', false);
                $options['MYRTILLE_DOMAIN'] = $myrtilleDomainCfg['CONFIG'];
                $myrtilleUserCfg = $devLib->getEnv_config('CITYWARE', 'codice', 'MYRTILLE_USER', false);
                $options['MYRTILLE_USER'] = $myrtilleUserCfg['CONFIG'];
                $myrtillePwdCfg = $devLib->getEnv_config('CITYWARE', 'codice', 'MYRTILLE_PWD', false);
                $options['MYRTILLE_PWD'] = $myrtillePwdCfg['CONFIG'];
                $myrtilleSrvCfg = $devLib->getEnv_config('CITYWARE', 'codice', 'MYRTILLE_SRV', false);
                $options['MYRTILLE_SRV'] = $myrtilleSrvCfg['CONFIG'];
                $myrtilleopenModeCfg = $devLib->getEnv_config('CITYWARE', 'codice', 'MYRTILLE_OPENMODE', false);
                $options['MYRTILLE_OPENMODE'] = $myrtilleopenModeCfg['CONFIG'];
                $options['MYRTILLE_TITLE'] = $this->getMenuTitle();
                itaShellExec::remoteAppMyrtilleExec($options);
                break;
            case 'd':
            default:
                itaShellExec::shellExec($shellOpenKey, $args, 'false');
                break;
        }
    }
    
    private function getMenuTitle() {
        $menLib = new menLib();
        $voci = $menLib->GetIta_puntimenu_ini($_POST['menu']);
        if (!$voci) {
            return 'Cityware';
        }
        foreach ($voci as $voce) {
            if ($voce['pm_voce'] === $_POST['prog']) {
                return $voce['pm_descrizione'];
            }
        }
        return 'Cityware';
    }
    
}
