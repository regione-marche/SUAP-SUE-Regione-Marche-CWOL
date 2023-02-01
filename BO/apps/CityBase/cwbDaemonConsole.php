<?php
include_once ITA_LIB_PATH  . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_BASE_PATH . '/daemon/lib/itaDaemonManager.class.php';

function cwbDaemonConsole() {
    $cwbDaemonConsole = new cwbDaemonConsole();
    $cwbDaemonConsole->parseEvent();
    return;
}

class cwbDaemonConsole extends itaFrontControllerCW {
    private $daemonManager;
    private $daemons;
    
    function __construct($nameFormOrig=null, $nameForm=null){
        if(!isSet($nameForm) || !isSet($nameFormOrig)){
            $nameFormOrig = 'cwbDaemonConsole';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
        
        $this->daemonManager = new itaDaemonManager();
        $this->daemons = $this->daemonManager->getDaemons();
    }
    
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->initialize();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_testAll':
                        $this->testAll();
                        break;
                    default:
                        if(preg_match('/.*_(.*)_action_start/', $_POST['id'], $matches) === 1){
                            $daemon = $matches[1];
                            $this->start($daemon);
                            break;
                        }
                        if(preg_match('/.*_(.*)_action_stop/', $_POST['id'], $matches) === 1){
                            $daemon = $matches[1];
                            $this->stop($daemon);
                            break;
                        }
                        if(preg_match('/.*_(.*)_action_resume/', $_POST['id'], $matches) === 1){
                            $daemon = $matches[1];
                            $this->resume($daemon);
                            break;
                        }
                        if(preg_match('/.*_(.*)_action_pause/', $_POST['id'], $matches) === 1){
                            $daemon = $matches[1];
                            $this->pause($daemon);
                            break;
                        }
                        break;
                }
                break;
            case 'ontimer':
                if($_POST['nameform'] == $this->nameForm){
                    $this->refreshView();
                }
                break;

        }
    }
    
    private function initialize(){
        foreach($this->daemons as $daemon){
            $this->buildRow($daemon);
            $this->updateDaemonStatus($daemon['name']);
        }
        Out::addTimer($this->nameForm . '_workSpace', 10, null, false, true);
    }
    
    private function buildRow($daemon){
        $name = $daemon['name'];
        $label = $daemon['label'] ? $daemon['label'] : $name;
        $autostart = $daemon['autostart'] === true ? 'Automatico' : 'Manuale';
        
        itaLib::openInner('cwbDaemonDetail', '', true, $this->nameForm . '_workSpace', '', '', 'cwbDaemonDetail_'.$name);
        Out::html('cwbDaemonDetail_'.$name.'_name', '<span class="ita-header-content">'.$label.'</span>');
        Out::html('cwbDaemonDetail_'.$name.'_start_value', $autostart);
    }
    
    private function refreshView(){
        foreach($this->daemons as $daemon){
            $this->updateDaemonStatus($daemon['name']);
        }
    }
    
    private function updateDaemonStatus($daemon){
        $daemonStatus = $this->daemonManager->getDaemonStatus($daemon);
        
        switch($daemonStatus['state']){
            case itaDaemonCOM::DAEMON_STATUS_NEW:
            case itaDaemonCOM::DAEMON_STATUS_STOP:
                $status = 'Fermo';
                $icon = 'ita-icon-bullet-green-24x24-old';
                break;
            case itaDaemonCOM::DAEMON_STATUS_RUN:
                switch($daemonStatus['warning']){
                    case itaDaemonManager::OK:
                        $status = 'In esecuzione';
                        $icon = 'ita-icon-bullet-green-24x24-old';
                        break;
                    case itaDaemonManager::WARNING:
                        $status = 'In esecuzione';
                        $icon = 'ita-icon-bullet-yellow-24x24';
                        break;
                    case itaDaemonManager::ERROR:
                        $status = 'Fermo';
                        $icon = 'ita-icon-bullet-red-24x24-old';
                        break;
                }
                break;
            case itaDaemonCOM::DAEMON_STATUS_PAUSE:
                switch($daemonStatus['warning']){
                    case itaDaemonManager::OK:
                        $status = 'In pausa';
                        $icon = 'ita-icon-bullet-green-24x24-old';
                        break;
                    case itaDaemonManager::WARNING:
                        $status = 'In pausa';
                        $icon = 'ita-icon-bullet-yellow-24x24';
                        break;
                    case itaDaemonManager::ERROR:
                        $status = 'Fermo';
                        $icon = 'ita-icon-bullet-red-24x24-old';
                        break;
                }
                break;
            case itaDaemonCOM::DAEMON_STATUS_CRASH:
                $status = 'Fermo';
                $icon = 'ita-icon-bullet-red-24x24-old';
                break;
        }
        
        $timestamp = trim($daemonStatus['lastPing']) != '' ? date('H:i:s - d/m/Y', $daemonStatus['lastPing']) : 'Mai';
        $msg = $daemonStatus['daemonMsg'];
        
        Out::html('cwbDaemonDetail_'.$daemon.'_status_value', $status);
        
        Out::delClass('cwbDaemonDetail_'.$daemon.'_status_icon', 'ita-icon-bullet-green-24x24-old');
        Out::delClass('cwbDaemonDetail_'.$daemon.'_status_icon', 'ita-icon-bullet-yellow-24x24');
        Out::delClass('cwbDaemonDetail_'.$daemon.'_status_icon', 'ita-icon-bullet-red-24x24-old');
        Out::addClass('cwbDaemonDetail_'.$daemon.'_status_icon', $icon);
        
        Out::html('cwbDaemonDetail_'.$daemon.'_ping_value', $timestamp);
        
        if(trim($msg) == ''){
            Out::hide('cwbDaemonDetail_'.$daemon.'_daemonMessage');
        }
        else{
            Out::show('cwbDaemonDetail_'.$daemon.'_daemonMessage');
            Out::html('cwbDaemonDetail_'.$daemon.'_msg_value', $msg);
        }
        
        switch($status){
            case 'In esecuzione':
                Out::hide('cwbDaemonDetail_'.$daemon.'_action_start');
                Out::show('cwbDaemonDetail_'.$daemon.'_action_stop');
                Out::hide('cwbDaemonDetail_'.$daemon.'_action_resume');
                Out::show('cwbDaemonDetail_'.$daemon.'_action_pause');
                break;
            case 'In pausa':
                Out::hide('cwbDaemonDetail_'.$daemon.'_action_start');
                Out::show('cwbDaemonDetail_'.$daemon.'_action_stop');
                Out::show('cwbDaemonDetail_'.$daemon.'_action_resume');
                Out::hide('cwbDaemonDetail_'.$daemon.'_action_pause');
                break;
            case 'Fermo':
                Out::show('cwbDaemonDetail_'.$daemon.'_action_start');
                Out::hide('cwbDaemonDetail_'.$daemon.'_action_stop');
                Out::hide('cwbDaemonDetail_'.$daemon.'_action_resume');
                Out::hide('cwbDaemonDetail_'.$daemon.'_action_pause');
                break;
        }
    }
    
    private function start($daemon){
        try{
            $this->daemonManager->startDaemon($daemon);
        }
        catch(ItaException $e){
            Out::msgStop("Errore", $e->getNativeErroreDesc());
        }
        catch(Exception $e){
            Out::msgStop("Errore", $e->getMessage());
        }
    }
    
    private function stop($daemon){
        try{
            $this->daemonManager->stopDaemon($daemon);
        }
        catch(ItaException $e){
            Out::msgStop("Errore", $e->getNativeErroreDesc());
        }
        catch(Exception $e){
            Out::msgStop("Errore", $e->getMessage());
        }
    }
    
    private function resume($daemon){
        try{
            $this->daemonManager->resumeDaemon($daemon);
        }
        catch(ItaException $e){
            Out::msgStop("Errore", $e->getNativeErroreDesc());
        }
        catch(Exception $e){
            Out::msgStop("Errore", $e->getMessage());
        }
    }
    
    private function pause($daemon){
        try{
            $this->daemonManager->pauseDaemon($daemon);
        }
        catch(ItaException $e){
            Out::msgStop("Errore", $e->getNativeErroreDesc());
        }
        catch(Exception $e){
            Out::msgStop("Errore", $e->getMessage());
        }
    }
}
?>