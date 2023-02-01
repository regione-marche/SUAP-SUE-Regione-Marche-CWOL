<?php

/*
 * Helper per gestire db nel caso la transazione sia gestita manualmente 
 * (l'istanza muore con la request)
 */

class cwbDBRequest {

    private $startedTransaction = false; //Per non avere il null come valore
    private $citywareDbSession;

    public static function getInstance() {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    protected function __construct() {
        
    }

    private function __clone() {
        
    }

    private function __wakeup() {
        
    }

    function startManualTransaction($dbName, $db = null) {
        if (!$db) {
            if (!$dbName) {
                $dbName = cwbLib::getCitywareConnectionName();
            }
            $db = ItaDB::DBOpen($dbName, '');
        }
        ItaDB::DBBeginTransaction($db, 1, true);
        cwbDBRequest::getInstance()->setCitywareDbSession($db);
        cwbDBRequest::getInstance()->setStartedTransaction(true);
    }

    function commitManualTransaction() {
        ItaDB::DBCommitTransaction(self::getInstance()->getCitywareDbSession(), false, true);
        cwbDBRequest::getInstance()->setStartedTransaction(false);
    }

    function rollBackManualTransaction() {
        ItaDB::DBRollbackTransaction(self::getInstance()->getCitywareDbSession(), false, true);
        cwbDBRequest::getInstance()->setStartedTransaction(false);
    }

    function getStartedTransaction() {
        return $this->startedTransaction;
    }

    function getCitywareDbSession() {
        return $this->citywareDbSession;
    }

    function setStartedTransaction($startedTransaction) {
        $this->startedTransaction = $startedTransaction;
    }

    function setCitywareDbSession($citywareDbSession) {
        $this->citywareDbSession = $citywareDbSession;
    }

}

?>