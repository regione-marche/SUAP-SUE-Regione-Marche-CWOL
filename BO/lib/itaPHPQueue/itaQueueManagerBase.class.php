<?php

require_once ITA_LIB_PATH . '/itaPHPQueue/itaQueueManager.php';
require_once ITA_LIB_PATH . '/itaException/ItaException.php';
require_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';

/**
 *
 * Superclasse QueueManager
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPQueue
 * @author     Biagioli/Pergolini
 * @copyright  
 * @license
 * @version    31.03.2017
 * @link
 * @see
 * 
 */
abstract class itaQueueManagerBase implements itaQueueManager {
    /*
     * Costanti che identificano il tipo di messaggio
     */

    const MESSAGE_TYPE_ANPR = 1;
    const MESSAGE_TYPE_PRINT = 2;
    const PRECONDITION_ERROR_MISSING_QUEUEID = "Parametro queueId mancante";
    const PRECONDITION_ERROR_MISSING_MSG = "Parametro message mancante";
    const PRECONDITION_ERROR_MISSING_SUBJECT = "Parametro oggetto messaggio mancante";
    const PRECONDITION_ERROR_MISSING_TEXT = "Parametro testo messaggio mancante";
    const PRECONDITION_ERROR_MISSING_USER = "Destinatario della notifica mancante ";
    const PRECONDITION_ERROR_TYPE_MESSAGE = "Parametro messaggio errato. Deve essere un ItaQueueMessage";

    /**
     * Array delle code attive
     * @var array
     */
    private $queues;
    private $errorMessage;

    /**
     * Prorietà aggiornabili in fase di update del messaggio 
     * @var array
     */
    private $propertiesUpdatable;
    private $envLib;

    public function __construct() {
        $this->queues = array();
        // aggiungere le proprietà che possono essere aggiornare durente update del messaggio 
        $this->propertiesUpdatable = array();
        $this->propertiesUpdatable = array('disabled', "prova");
        $this->envLib = new envLib();
    }

    public function createQueue($queueId) {
        $this->createQueuePreconditions($queueId);
        return $this->createQueueCustom($queueId);
    }

    protected abstract function createQueueCustom($queueId);

    public function destroyQueue($queueId) {
        $this->destroyQueuePreconditions($queueId);
        return $this->destroyQueueCustom($queueId);
    }

    protected abstract function destroyQueueCustom($queueId);

    public function addMessage($queueId, $message) {
        $this->addMessagePreconditions($queueId, $message);
        return $this->addMessageCustom($queueId, $message);
    }

    protected abstract function addMessageCustom($queueId, $message);

    public function updateMessage($queueId, $message) {
        $this->updateMessagePreconditions($queueId, $message);
        return $this->updateMessageCustom($queueId, $message);
    }

    protected abstract function updateMessageCustom($queueId, $message);

    public function getMessage($queueId) {
        $this->getMessagePreconditions($queueId);
        return $this->getMessageCustom($queueId);
    }

    protected abstract function getMessageCustom($queueId);

    public function notificationMessage($message, $messageSubject, $messageText) {
        $this->notificationMessagePreconditions($message, $messageSubject, $messageText);
        return $this->notificationMessageExecutor($message, $messageSubject, $messageText);
    }

    public function findMessages($queueId, $filters = array(), $arrayFormat = false) {
        $this->findPreconditions($queueId);
        return $this->findCustom($queueId, $filters, $arrayFormat);
    }

    protected abstract function findCustom($queueId, $filters, $arrayFormat);

    public function queueExists($queueId) {
        $this->queueExistsPreconditions($queueId);
        return $this->queueExistsCustom($queueId);
    }

    protected abstract function queueExistsCustom($queueId);

    public function queueStatus($queueId) {
        $this->queueStatusPreconditions($queueId);
        return $this->queueStatusCustom($queueId);
    }

    protected abstract function queueStatusCustom($queueId);

    /*     * *
     * Aggiunge la notifica all'utente che ha richiesto l'elaborazione
     */

    public function notificationMessageExecutor($message, $messageSubject, $messageText) {
        //Efettuare l'inserimento del messaggio 
        $nameForm = "ntfQueeMessageData";
        $esito = $this->envLib->inserisciNotifica("cwbItaQueueMesssageData", $messageSubject, $messageText, $message->getUsername(), array(
            'ACTIONMODEL' => $nameForm,
            'ACTIONPARAM' => serialize(
                    array(
                        'message' => $message->toArray()
                    )
            )
        ));
        if (!$esito) {
            $this->setErrorMessage($this->envLib->getErrCode() . $this->envLib->getErrMessage());
        }
        return $esito;
    }

    /**
     * Aggiunge una nuova coda all'array delle code
     * @param int $queueId Identificativo coda
     */
    public function addQueue($queueId) {
        $this->queues[] = $queueId;
    }

    /**
     * Rimuove la coda specificata dall'array delle code
     * @param int $queueId Identificativo coda
     */
    public function removeQueue($queueId) {
        unset($this->queues[$queueId]);
    }

    //getter/setter
    public function getQueues() {
        return $this->queues;
    }

    public function setQueues($queues) {
        $this->queues = $queues;
    }

    public function getPropertiesUpdatable() {
        return $this->propertiesUpdatable;
    }

    //preCondition
    protected function createQueuePreconditions($queueId) {
        if (!$queueId) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_MISSING_QUEUEID);
        }
    }

    protected function destroyQueuePreconditions($queueId) {
        if (!$queueId) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_MISSING_QUEUEID);
        }
    }

    protected function addMessagePreconditions($queueId, $message) {
        if (!$queueId) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_MISSING_QUEUEID);
        }
        if (!$message) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_MISSING_MSG);
        }
        if (!is_object($message)) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_TYPE_MESSAGE);
        }
    }

    protected function notificationMessagePreconditions($message, $messageSubject, $messageText) {
        if (!$message) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_MISSING_MSG);
        }
        if (!is_object($message)) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_TYPE_MESSAGE);
        }
        if (!$messageSubject) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_MISSING_SUBJECT);
        }
        if (!$messageText) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_MISSING_TEXT);
        }


        //controllo se esiste un utente a cui notitica 
        if (!$message->getUsername()) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_MISSING_USER);
        }
    }

    protected function updateMessagePreconditions($queueId, $message) {
        if (!$queueId) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_MISSING_QUEUEID);
        }
        if (!$message) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_MISSING_MSG);
        }
        if (!is_object($message)) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_TYPE_MESSAGE);
        }
    }

    protected function getMessagePreconditions($queueId) {
        if (!$queueId) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_MISSING_QUEUEID);
        }
    }

    protected function findPreconditions($queueId, $arrayFormat) {
        if (!$queueId) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_MISSING_QUEUEID);
        }
    }

    protected function queueExistsPreconditions($queueId) {
        if (!$queueId) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_MISSING_QUEUEID);
        }
    }

    protected function queueStatusPreconditions($queueId) {
        if (!$queueId) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_MISSING_QUEUEID);
        }
    }

    public function getLastError() {
        return $this->errorMessage;
    }

    public function resetErrorMessage() {
        $this->errorMessage = '';
    }

    public function setErrorMessage($errorMessage) {
        $this->errorMessage = $errorMessage;
    }

}

?>