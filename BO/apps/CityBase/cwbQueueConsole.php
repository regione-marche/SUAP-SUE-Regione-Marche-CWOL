<?php

/**
 * cwbQueueConsole 
 * console delle code 
 * @author l.pergolini
 */
//include_once ITA_BASE_PATH . '/apps/CityBase/cwbQueueConsoleHelper.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_LIB_PATH . '/itaPHPQueue/itaQueueFactory.class.php';
include_once ITA_LIB_PATH . '/itaPHPQueue/itaQueueMessage.class.php';

function cwbQueueConsole() {
    $cwbQueueConsole = new cwbQueueConsole();
    $cwbQueueConsole->parseEvent();
    return;
}

class cwbQueueConsole extends itaFrontControllerCW {
    private $consoleHelper;
    private $qm; //Manageer delle code
    
    const GRID_NAME = 'gridQueueConsole';

    function __construct($nameFormOrig, $nameForm) {
        parent::__construct($nameFormOrig, $nameForm);
        try {
//            $this->nameForm = 'cwbQueueConsole';
            $this->GRID_NAME = SELF::GRID_NAME;
            $this->noCrud = true;
            $this->qm = itaQueueFactory::getQueueManager();
                        
            //TODO DA ELIMINARE
            $this->helper->setNameForm($this->nameForm);
            $this->helper->setGridName($this->GRID_NAME);
            //ENDTODO
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->initForm();
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_selectTypeQueue':
                        $this->loadInformationQueue($_POST[$this->nameForm . '_selectTypeQueue']);
                        break;
                }
                break;
            case 'dbClickRow':
                $this->getDetail($_POST[$this->nameForm . '_selectTypeQueue'],$_POST['rowid']);
                break;
            case 'onClick':
                switch($_POST['id']){
                    case $this->nameForm . '_Attiva':
                        $this->toggleDisable($_POST[$this->nameForm . '_selectTypeQueue'],$_POST[$this->nameForm . '_detailUuid']);
                        break;
                    case $this->nameForm . '_Disattiva':
                        $this->toggleDisable($_POST[$this->nameForm . '_selectTypeQueue'],$_POST[$this->nameForm . '_detailUuid']);
                        break;
                    case $this->nameForm . '_Torna':
                        $this->loadInformationQueue($_POST[$this->nameForm . '_selectTypeQueue']);
                        break;
                }
                break;
            case 'ontimer':
                if($_POST['id'] == $this->nameForm . '_divTimer_controller' && $_POST[$this->nameForm . '_selectTypeQueue'] !== ''){
                    $this->loadInformationQueue($_POST[$this->nameForm . '_selectTypeQueue']);
                }
                break;
        }
    }

    private function loadInformationQueue($queueId) {
        //controllo esistenza della coda 
        $this->clearData();
        if($queueId != ''){
            $result = $this->qm->queueExists($queueId);
            //se esite la coda 
            if($result){
                $this->refreshStatusInfo($queueId);
                $this->refreshMessages($queueId);
                $this->disableTimer($queueId);
                $this->enableTimer();
            }
            else{
                $this->clearData();
            }
        }
        $this->showQueue();
        if($queueId == '' || !$result){
            $this->disableTimer();
        }

    }

    private function clearData() {
        Out::valore($this->nameForm . "_statusLastMessageInsertedUuid", NULL);
        Out::valore($this->nameForm . "_statusLastMessageInsertedAlias", NULL);
        Out::valore($this->nameForm . "_statusLastMessageInsertedTimestampFormatted", NULL);
        Out::valore($this->nameForm . "_statusLastMessageProcessedUuid", NULL);
        Out::valore($this->nameForm . "_statusLastMessageProcessedAlias", NULL);
        Out::valore($this->nameForm . "_statusLastMessageProcessedErrorCode", NULL);
        Out::valore($this->nameForm . "_statusLastMessageProcessedErrorDescription", NULL);
        Out::valore($this->nameForm . "_lastQueueModifyDateTimeFormatted", NULL);
        Out::valore($this->nameForm . "_messagesToProcess", NULL);

        TableView::clearGrid($this->nameForm . '_' . $this->GRID_NAME);
    }

    private function refreshStatusInfo($queueId) {
        $status = $this->qm->queueStatus($queueId);
        Out::valore($this->nameForm . "_statusLastMessageInsertedUuid", $status['lastMessageInserted']['uuid']);
        Out::valore($this->nameForm . "_statusLastMessageInsertedAlias", $status['lastMessageInserted']['alias']);
        Out::valore($this->nameForm . "_statusLastMessageInsertedTimestampFormatted", $this->formatTimestamp($status['lastMessageInserted']['timestamp']));
        Out::valore($this->nameForm . "_statusLastMessageProcessedUuid", $status['lastMessageProcessed']['uuid']);
        Out::valore($this->nameForm . "_statusLastMessageProcessedAlias", $status['lastMessageProcessed']['alias']);
        Out::valore($this->nameForm . "_statusLastMessageProcessedErrorCode", $status['lastMessageProcessed']['errorCode']);
        Out::valore($this->nameForm . "_statusLastMessageProcessedErrorDescription", $status['lastMessagelastMessageProcessed']['errorDescription']);
        Out::valore($this->nameForm . "_lastQueueModifyDateTimeFormatted", $this->formatTimestamp($status['lastQueueModifyDateTime']));
        Out::valore($this->nameForm . "_messagesToProcess", $status['messagesToProcess']);
        Out::innerHtml($this->nameForm . "_divTimer_lastUpdate", date('H:i:s - d/m/Y'));
    }

    private function refreshMessages($queueId) {
        $messages = $this->qm->findMessages($queueId, null, true);
        foreach($messages as &$message){
            $message['disabled'] = $message['disabled']==0;
            if($message['executionMode'] != itaQueueMessage::EXECUTION_MODE_DEFERRED){
                $message['dateTimeDeferredExecution'] = '';
            }
        }
        //formattazione in base alla datatable specifica
        $ita_grid01 = $this->helper->initializeTableArray($messages);
        $this->helper->getDataPage($ita_grid01);
        TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME);
        Out::show($this->nameForm . "_buttonBar");
    }

    private function initForm() {
        Out::select($this->nameForm . '_selectTypeQueue', 1, "", 1, "Seleziona");
        Out::select($this->nameForm . '_selectTypeQueue', 1, "1", 0, "01 - Coda Anpr");
        Out::select($this->nameForm . '_selectTypeQueue', 1, "2", 0, "02 - Coda Stampa");
        
        $this->showQueue();
    }

    protected function postApriForm() {
        $this->initComboSelectTypeQueue();
    }

    public function getConsoleHelper() {
        return $this->consoleHelper;
    }

    public function setConsoleHelper($consoleHelper) {
        $this->consoleHelper = $consoleHelper;
    }

    private function formatTimestamp($timestamp) {
        return date('Y-m-d H:i:s', $timestamp);
    }
    
    private function showDetail(){
        Out::hide($this->nameForm . '_divQueue');
        Out::show($this->nameForm . '_divDetail');
        
        Out::show($this->nameForm . '_Torna');
        Out::show($this->nameForm . '_Attiva');
        Out::show($this->nameForm . '_Disattiva');
        
        Out::hide($this->nameForm . '_divTimer');
        
        $this->disableTimer();
    }
    
    private function showQueue(){
        Out::show($this->nameForm . '_divQueue');
        Out::hide($this->nameForm . '_divDetail');
        
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_Attiva');
        Out::hide($this->nameForm . '_Disattiva');
        
        Out::show($this->nameForm . '_divTimer');
    }
    
    private function getDetail($queue,$id){
        $filter = array();
        $filter[] = array("key"=>"uuid","value"=>$id);
        $message = $this->qm->findMessages($queue);
        
        Out::valore($this->nameForm . '_detailUuid', $message[0]->getUuid());
        Out::valore($this->nameForm . '_detailAlias', $message[0]->getAlias());
        Out::valore($this->nameForm . '_detailRetries', $message[0]->getRetries());
        
        $mode = (($message[0]->getExecutionMode()==itaQueueMessage::EXECUTION_MODE_IMMEDIATE)?'Immediato':'Asincrono');
        Out::valore($this->nameForm . '_detailMode', $mode);
        
        if($message[0]->getExecutionMode()==itaQueueMessage::EXECUTION_MODE_DEFERRED){
            Out::show($this->nameForm . '_detailDeferredExecution_field');
            
            $deferredDate = $this->formatTimestamp($message[0]->getDateTimeDeferredExecution());
            Out::valore($this->nameForm . '_detailDeferredExecution', $deferredDate);
        }
        else{
            Out::hide($this->nameForm . '_detailDeferredExecution_field');
        }
        
        $status = (($message[0]->getDisabled()==0)?'Attivo':'Disattivo');
        Out::valore($this->nameForm . '_detailDisabled', $status);
        
        $data = var_export($message[0]->getData(),true);
        Out::valore($this->nameForm . '_detailData', $data);
        
        $this->disableTimer();
        $this->showDetail();
        
        if($message[0]->getDisabled()==0){
            Out::hide($this->nameForm . '_Attiva');
            Out::show($this->nameForm . '_Disattiva');
        }
        else{
            Out::show($this->nameForm . '_Attiva');
            Out::hide($this->nameForm . '_Disattiva');
        }
    }
    
    private function toggleDisable($queue,$id){
        $filter = array();
        $filter[] = array("key"=>"uuid","value"=>$id);
        $message = $this->qm->findMessages($queue);
        
        $status = $message[0]->getDisabled()==0;
        $message[0]->setDisabled($status);
        
        $this->qm->updateMessage($queue, $message[0]);
        
        $this->getDetail($queue,$id);
    }
    
    private function enableTimer(){
        Out::addTimer($this->nameForm . '_divTimer_controller',15,null,true,false);
    }
    
    private function disableTimer(){
        Out::removeTimer($this->nameForm . '_divTimer_controller');
    }
}
?>