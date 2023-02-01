<?php

require_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';

/**
 *
 * QueueMessage Model
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
class itaQueueMessage {

    // Costanti che definiscono il modo di esecuzione
    const EXECUTION_MODE_IMMEDIATE = 0; // Esecuzione immediata
    const EXECUTION_MODE_DEFERRED = 1;  // Esecuzione differita

    private $uuid;                      // UUID interno del messaggio
    private $alias;                     // Alias che identifica univocamente il messaggio
    private $data;                      // Dati del messaggio
    private $retries;                   // Numero tentativi
    private $executionMode;             // Modalità di esecuzione (immediata/differita)
    private $dateTimeDeferredExecution; // Data\Ora di esecuzione differita
    private $disabled;                  // Messaggio disabilitato
    private $username;                  // Nome utente utile per sapere quale user ha lanciato l'operazione sulla coda

    public function __construct($alias, $data = array(), $retries = 0, $dateTimeDeferredExecution = null, $disabled = 0) {
        $this->uuid = null;
        $this->alias = $alias;
        $this->data = $data;
        $this->retries = $retries;
        $this->dateTimeDeferredExecution = $dateTimeDeferredExecution;
        if ($this->getDateTimeDeferredExecution()) {
            $this->executionMode = self::EXECUTION_MODE_DEFERRED;
        } else {
            $this->executionMode = self::EXECUTION_MODE_IMMEDIATE;
        }
        $this->disabled = $disabled;
        $this->username = cwbParGen::getSessionVar('nomeUtente');
    }

    /**
     * Crea un nuovo oggetto Message a partire da un array
     * @param array $data dati con cui generare oggetto 'itaQueueMessage'
     */
    public static function fromArray($data) {
        return self::newInstance($data['message']);
    }

    private static function newInstance($arrayData) {
        $instance = new itaQueueMessage();
        $instance->setUuid($arrayData['uuid']);
        $instance->setAlias($arrayData['alias']);
        $instance->setData($arrayData['data']);
        $instance->setRetries($arrayData['retries']);
        $instance->setExecutionMode($arrayData['executionMode']);
        $instance->setDateTimeDeferredExecution($arrayData['dateTimeDeferredExecution']);
        $instance->setDisabled($arrayData['disabled']);
        $instance->setUsername($arrayData['username']);
        return $instance;
    }

    /**
     * Restituisce array che identifica il messaggio
     * @return object
     */
    public function toArray() {
        return array(
            'uuid' => $this->getUuid(),
            'alias' => $this->getAlias(),
            'data' => $this->getData(),
            'retries' => $this->getRetries(),
            'executionMode' => $this->getExecutionMode(),
            'dateTimeDeferredExecution' => $this->getDateTimeDeferredExecution(),
            'disabled' => $this->getDisabled(),
            'username' => $this->getUsername()
        );
    }

    public function getUuid() {
        return $this->uuid;
    }

    public function getAlias() {
        return $this->alias;
    }

    public function getData() {
        return $this->data;
    }

    public function getRetries() {
        return $this->retries;
    }

    public function setUuid($uuid) {
        $this->uuid = $uuid;
    }

    public function setAlias($alias) {
        $this->alias = $alias;
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function setRetries($retries) {
        $this->retries = $retries;
    }

    public function getExecutionMode() {
        return $this->executionMode;
    }

    public function getDelay() {
        return $this->delay;
    }

    public function getDateTimeDeferredExecution() {
        return $this->dateTimeDeferredExecution;
    }

    public function getDisabled() {
        return $this->disabled;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setExecutionMode($executionMode) {
        $this->executionMode = $executionMode;
    }

    public function setDelay($delay) {
        $this->delay = $delay;
    }

    public function setDateTimeDeferredExecution($dateTimeDeferredExecution) {
        $this->dateTimeDeferredExecution = $dateTimeDeferredExecution;
    }

    public function setDisabled($disabled) {
        $this->disabled = $disabled;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

}
