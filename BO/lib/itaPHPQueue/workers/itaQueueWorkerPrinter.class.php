<?php

require_once ITA_LIB_PATH . '/itaPHPQueue/itaQueueWorker.php';
require_once ITA_LIB_PATH . '/itaPHPQueue/itaQueueWorkerBase.class.php';

/**
 *
 * Worker specifico per stampe
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPQueue/workers
 * 
 */
class itaQueueWorkerPrinter extends itaQueueWorkerBase implements itaQueueWorker {

    public function getMessageExecuteStrategy() {
        return itaQueueWorkerBase::STRATEGY_REINSERT;
    }

    protected function executeMessage($message) {
        $this->setErrorCode(0);
        $this->setErrorDescription("");

        $data = $message->getData();

        require_once $data['REQUIRE'];
        $class = $data['CLASS'];

        $instance = new $class();
        $instance->executePrint($data['PARAMS']);

        if ($instance->getResult() == false) {
            $this->setErrorCode(-1);
            $this->setErrorDescription($instance->getErrMessage());
        }

        $toReturn = 'Errore Stampa';

        if ($instance->getResult()) {
            $toReturn = $instance->getResult();
        }

        return $toReturn;
    }

    public function getMaxRetries($message) {
        // il "get" va valorizzato in base al errore e al tipo di messaggio 
        return 2;
    }

}
