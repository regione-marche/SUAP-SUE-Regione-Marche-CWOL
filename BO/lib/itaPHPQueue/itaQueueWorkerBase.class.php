<?php

require_once ITA_LIB_PATH . '/itaPHPQueue/itaQueueWorker.php';
require_once ITA_LIB_PATH . '/itaPHPQueue/itaQueueMessage.class.php';
require_once ITA_LIB_PATH . '/itaException/ItaException.php';

/**
 *
 * Superclasse QueueWorker
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPQueue
 * @author     Biagioli/Pergolini
 * @copyright  
 * @license
 * @version    04.04.2017
 * @link
 * @see
 * 
 */
abstract class itaQueueWorkerBase implements itaQueueWorker {
    /*
     * Costanti che indicano la Strategy da applicare in fase di esecuzione,
     * in caso di fallimento dell'esecuzione di un messaggio
     */

    const STRATEGY_REINSERT = 1;
    const STRATEGY_DELETE = 2;

    /**
     * Tipo di messaggio
     * @var int Tipo di messaggio 
     */
    private $messageType;
    private $queueManager;

    /**
     *
     * @var int in caso di errore indica i minuti dopo cui si può riprovare l'esecuzione del messaggio
     */
    private $delay;

    /**
     * @var int CodiceErrore elaborazione del messaggio <0 Errore 
     */
    private $errorCode;

    /**
     * @var string Descrizione elaborazione <0 Errore 
     */
    private $errorDescription;

    /**
     * @var mixed Risposta specifica al message
     */
    private $resultData;

    public function execute($message) {
        //execute processa solo i messaggi attivi
        $this->resetErrorMessage();


        $excutable = $this->isExecutable($message);

        if ($excutable) {

            try {
                $currentTime = time();
                $this->executeMessage($message);
            } catch (Exception $ex) {
                $this->setErrorCode(-1);
                $this->setErrorDescription($ex->getCode() . " " . $ex->getMessage());
                $this->setResultData(NULL);
            }

            $this->getQueueManager()->updateLastMessageProcessed($this->getMessageType(), $message, $this->getErrorCode(), $this->getErrorDescription());

            // In caso di errore, controlla la strategy da applicare:
            // Se deve effettuare un nuovo reinserimento in coda, effettua prima un controllo 
            // sul numero di tentativi effettuati e sul tempo trascorso dall'ultimo tentativo fallito.
            // Se la strategy invece implica la cancellazione, non effettua nessuna operazione,
            // in quanto il messaggio è stato già rimosso in fase di prelevamento
            if ($this->getErrorCode() < 0) {
                if ($this->getMessageExecuteStrategy() == self::STRATEGY_REINSERT) {
                    if ($message->getRetries() < ($this->getMaxRetries($message) - 1)) {

                        //se ho impostato il tempo di delay aggiorno la data di esecuzione   
                        // Out::systemEcho("Delay:" . $this->getDelay() . "\n", true);
                        $message->setRetries($message->getRetries() + 1);
                        if ($this->getDelay()) {

                            $dateTimeDeferredExecution = date("Y-m-d h:i:s");
                            $dateTimeDeferredExecution = strtotime($dateTimeDeferredExecution);
                            $dateTimeDeferredExecution += $this->getDelay() * 60;
                            $formatdateTimeDeferredExecution = date('Y-m-d h:i:s', $dateTimeDeferredExecution);

                            $message->setDateTimeDeferredExecution($formatdateTimeDeferredExecution);
                            $message->setExecutionMode(itaQueueMessage::EXECUTION_MODE_DEFERRED);

                            $messageOut = "Elaborazione messaggio uuid: " . $message->getUuid() . " - alias: " . $message->getAlias() .
                                    " - Errore: " . $this->getErrorCode() . " " . $this->getErrorDescription() .
                                    " - Tentativi: " . $message->getRetries() . " di " . $this->getMaxRetries() .
                                    " - Prossimo tentativo " . $message->getDateTimeDeferredExecution();

                            Out::systemEcho($messageOut . " \n", true);
                            App::log(date("d/m/Y H:i:s") . " " . $messageOut, false, true);
                            $this->resetErrorMessage();
                        }

                        $result = $this->getQueueManager()->addMessage($this->getMessageType(), $message);
                        if (!$result) {
                            $this->setErrorCode(-1);
                            $this->setErrorDescription("Errore messaggio " . $message->getAlias() . " in coda non aggiunto");
                            $this->setResultData(NULL);
                            $messageOut = "Elaborazione messaggio uuid: " . $message->getUuid() . " - alias: " . $message->getAlias() . " " . $this->getErrorDescription();
                        } else {
                            $messageOut = "Elaborazione messaggio uuid: " . $message->getUuid() . " - alias: " . $message->getAlias() . " avvenuta con successo";
                        }
                    } else {
                        $this->resetErrorMessage();
                        $messageOut = "Elaborazione messaggio uuid: " . $message->getUuid() . " - alias: " . $message->getAlias() . " numero di tentativi esauriti";
                    }
                } else if ($this->getMessageExecuteStrategy() == self::STRATEGY_DELETE) {
                    // TODO   
                }
            } else {
                $messageOut = "Elaborazione messaggio uuid: " . $message->getUuid() . " - alias: " . $message->getAlias() . " avvenuta con successo";
            }
            Out::systemEcho($messageOut . " \n", true);
            App::log(date("d/m/Y H:i:s") . " " . $messageOut, false, true);
            //se il messagio è stato eseguindo invio la notifica all'utente inserito 
            $result = $this->getQueueManager()->notificationMessage($message, "Esecuzione messaggio in coda", $messageOut);
            if (!$result) {
                $this->setErrorCode(-2);
                $this->setErrorDescription($this->getQueueManager()->getLastError());
            }
        }
    }

    private function resetErrorMessage() {
        $this->setErrorCode(0);
        $this->setErrorDescription("Esito Positivo");
    }

    public function isExecutable($message) {
        //return TRUE;
        if ($message->getExecutionMode() == itaQueueMessage::EXECUTION_MODE_IMMEDIATE) {
            return TRUE;
        } else {
            // tempo corrente
            $currentDateTime = date("Y-m-d h:i:s");
            $currentDateTime = strtotime($currentDateTime);
            $startRange = $currentDateTime;
            $startDateTime = date('Y-m-d h:i:s', $startRange);

            if ($message->getDateTimeDeferredExecution() < $startDateTime) {
                $messageOut = "Messaggio uuid: " . $message->getUuid() . " - alias: " . $message->getAlias() . " prelevato dalla coda";
                App::log(date("d/m/Y H:i:s") . " " . $messageOut, false, true);
                Out::systemEcho($messageOut . "\n", true);
                return TRUE;
            }

            // se non rientra nel range rimetto il mesasggio in coda 
            $result = $this->getQueueManager()->addMessage($this->getMessageType(), $message);
            if (!$result) {
                $this->setErrorCode(-1);
                $this->setErrorDescription("Errore messaggio differito " . $message->getAlias() . " non aggiunto in coda ");
                $this->setResultData(NULL);
            }
            //Out::systemEcho("Non rientro nel range di esecuzione \n", true);
            return FALSE;
        }
    }

    /**
     * Esecuzione di un messaggio singolo
     * @param object $messge Messaggio da eseguire
     */
    protected abstract function executeMessage($message);

    public function getMessageType() {
        return $this->messageType;
    }

    public function setMessageType($msgType) {
        $this->messageType = $msgType;
    }

    public function getErrorCode() {
        return $this->errorCode;
    }

    public function getErrorDescription() {
        return $this->errorDescription;
    }

    public function getResultData() {
        return $this->resultData;
    }

    public function setErrorCode($errorCode) {
        $this->errorCode = $errorCode;
    }

    public function setErrorDescription($errorDescription) {
        $this->errorDescription = $errorDescription;
    }

    public function setResultData($resultData) {
        $this->resultData = $resultData;
    }

    public function getQueueManager() {
        return $this->queueManager;
    }

    public function setQueueManager($queueManager) {
        $this->queueManager = $queueManager;
    }

    public function getDelay() {
        return $this->delay;
    }

    public function setDelay($delay) {
        $this->delay = $delay;
    }

}
