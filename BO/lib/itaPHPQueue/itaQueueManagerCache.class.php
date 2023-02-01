<?php

require_once ITA_LIB_PATH . '/itaPHPQueue/itaQueueManager.php';
require_once ITA_LIB_PATH . '/itaPHPQueue/itaQueueManagerBase.class.php';
require_once ITA_BASE_PATH . '/lib/Cache/CacheFactory.class.php';
require_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
require_once ITA_LIB_PATH . '/itaPHPQueue/itaQueueMessage.class.php';

/**
 *
 * Implementazione QueueManager per PHP
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
class itaQueueManagerCache extends itaQueueManagerBase implements itaQueueManager {

    const QUEUE_PREFIX = 'QUEUE_';
    const MAX_RETRIES = 5; //numero di tentativi 
    const MM_SLEEP_RETRIES = 100000; // millesecondi di attesa dopo ogni tentativo di acquisizione del blocco

    private $cache;
    private $envLib;

    public function __construct() {
        parent::__construct();
        $this->cache = CacheFactory::newCache(CacheFactory::TYPE_FILE, Config::getConf('queue.cacheRoot'));
        $this->envLib = new envLib();
    }

    public function createQueueCustom($queueId) {
        $this->resetErrorMessage();
        $cacheKey = self::QUEUE_PREFIX . App::$utente->getKey('ditta') . '_' . $queueId;
        $cacheNode = $this->cache->get($cacheKey);
        $created = true;

        if (!$cacheNode) {
            $cacheNode = array(
                'id' => $queueId,
                'status' => array(
                    'lastMessageInserted' => array(
                        'uuid' => NULL,
                        'alias' => NULL,
                        'timestamp' => NULL,
                    ),
                    'lastMessageProcessed' => array(
                        'uuid' => NULL,
                        'alias' => NULL,
                        'timestamp' => NULL,
                        'errorCode' => NULL,
                        'errorDescription' => NULL
                    ),
                    'lastQueueModifyDateTime' => time(),
                    'messagesToProcess' => 0,
                    'customAttributes' => array()
                ),
                'messages' => array()
            );
            $esitoSemaforo = false;
            $n = 0;
            while ($n++ < self::MAX_RETRIES && !$esitoSemaforo) {
                $esitoSemaforo = $this->blocca($queueId);
                usleep(self::MM_SLEEP_RETRIES); //sleep 
            }
            if ($esitoSemaforo) {
                $result = $this->cache->set($cacheKey, $cacheNode, 0);

                if (!$result) {
                    $this->setErrorMessage($this->cache->get_error());
                    $created = false;
                }
                $this->sblocca($queueId);
            } else {
                $this->setErrorMessage("Blocco per chiave :$queue non riuscita");
                $created = false;
            }
        }
        return $created;
    }

    public function destroyQueueCustom($queueId) {
        $this->resetErrorMessage();
        $cacheKey = self::QUEUE_PREFIX . App::$utente->getKey('ditta') . '_' . $queueId;
        $cacheNode = $this->cache->get($cacheKey);
        $result = false;
        if (!$cacheNode) {
            return false;
        }

        $esitoSemaforo = false;
        $n = 0;
        while ($n++ < self::MAX_RETRIES && !$esitoSemaforo) {
            $esitoSemaforo = $this->blocca($queueId);
            usleep(self::MM_SLEEP_RETRIES); //sleep 
        }
        if ($esitoSemaforo) {
            $result = $this->cache->delete($cacheKey);
            if (!$result) {
                $this->setErrorMessage($this->cache->get_error());
            }
            $this->sblocca($queueId);
        } else {
            $this->setErrorMessage("Blocco per chiave :$queueId non riuscita");
        }

        return $result;
    }

    public function addMessageCustom($queueId, $message) {
        $this->resetErrorMessage();
        $cacheKey = self::QUEUE_PREFIX . App::$utente->getKey('ditta') . '_' . $queueId;
        $result = false;
        $currentTimestamp = time();

        // Aggiorna messaggio con il campo uuid calcolato
        if (!$message->getUuid()) {
            $uuid = uniqid();
            $message->setUuid($uuid);
        }

        // Controllo esistenza coda
        $cacheNode = $this->cache->get($cacheKey);
        if (!$cacheNode) {
            return false;
        }

        $esitoSemaforo = false;
        $n = 0;
        while ($n++ < self::MAX_RETRIES && !$esitoSemaforo) {
            $esitoSemaforo = $this->blocca($queueId);
            usleep(self::MM_SLEEP_RETRIES); //sleep 
        }

        if ($esitoSemaforo) {


            // Aggiunge messaggio in coda, aggiungendo le info
            $cacheNode['messages'][] = array(
                'info' => array(
                    'type' => $queueId,
                    'insertedTimestamp' => $currentTimestamp,
                    'receivedTimestamp' => NULL,
                    'lastProcessResults' => NULL
                ),
                'message' => $message->toArray()
            );

            // Aggiorna informazioni coda
            $cacheNode['status']['lastMessageInserted'] = array(
                'uuid' => is_array($message) ? $message[0]->getUuid() : $message->getUuid(),
                'alias' => is_array($message) ? $message[0]->getAlias() : $message->getAlias(),
                'timestamp' => $currentTimestamp,
            );
            $cacheNode['status']['lastQueueModifyDateTime'] = $currentTimestamp;
            $cacheNode['status']['messagesToProcess'] ++;

            // Aggiorna cache
            $result = $this->cache->set($cacheKey, $cacheNode, 0);
            if (!$result) {
                $this->setErrorMessage($this->cache->get_error());
            }

            $this->sblocca($queueId);
        } else {
            $this->setErrorMessage("Blocco per chiave: $queueId non riuscito");
        }
        return $result;
    }

    public function updateMessageCustom($queueId, $message) {
        $this->resetErrorMessage();
        $cacheKey = self::QUEUE_PREFIX . App::$utente->getKey('ditta') . '_' . $queueId;
        $result = false;
        $currentTimestamp = time();

        // Controllo esistenza coda
        $cacheNode = $this->cache->get($cacheKey);
        if (!$cacheNode) {
            return false;
        }

        $esitoSemaforo = false;
        $n = 0;
        while ($n++ < self::MAX_RETRIES && !$esitoSemaforo) {
            $esitoSemaforo = $this->blocca($queueId);
            usleep(self::MM_SLEEP_RETRIES); //sleep 
        }

        if ($esitoSemaforo) {
            //mesaggio da aggiornare 
            //$updateMessage = array_search($message["uuid"], array_column($cacheNode['messages']['message'], 'uuid'));
            // scorro i messaggi della cache per aggiornare i valori in base a quello passato 
            $toUpdate = FALSE;
            foreach ($cacheNode['messages'] as &$msgToUpdate) {
                // se ho trovato il messaggio da aggiornare
                if ($msgToUpdate['message']['uuid'] == $message->getUuid()) {
                    //controllo tutte le proprietà che stanno nella mappa 
                    foreach ($this->getPropertiesUpdatable() as $props) {
                        if (array_key_exists($props, $msgToUpdate['message'])) {
                            $method = "get" . ucfirst($props);
                            $msgToUpdate['message'][$props] = $message->$method();
                            $toUpdate = TRUE;
                        }
                    }
                    if ($toUpdate) {
                        //se è defered azzero i tentativi e rimetto 
                        if ($msgToUpdate["info"]["type"] == itaQueueMessage::EXECUTION_MODE_DEFERRED) {
                            //aggiungere il controllo se è scaduto altrimenti non aggiorna data esecuzione e retries

                            $dateTimeDeferredExecution = date("Y-m-d h:i:s");
                            $dateTimeDeferredExecution = strtotime($dateTimeDeferredExecution);
                            $formatdateTimeDeferredExecution = date('Y-m-d h:i:s', $currentDateTime);
                            // se è scaduto imposto la data di esecuzione in currentime  
                            if ($msgToUpdate['message']['dateTimeDeferredExecution'] < $formatdateTimeDeferredExecution) {
                                $msgToUpdate['message']['retries'] = 0; //azzero anche i tentativi 
                                $msgToUpdate['message']['dateTimeDeferredExecution'] = $formatdateTimeDeferredExecution;
                            }
                        }
                    }
                }
            }

            //aggiorno la data di modifica della coda 
            $cacheNode['status']['lastQueueModifyDateTime'] = $currentTimestamp;
            // Aggiorna cache
            $result = $this->cache->set($cacheKey, $cacheNode, 0);
            if (!$result) {
                $this->setErrorMessage($this->cache->get_error());
            }

            $this->sblocca($queueId);
        } else {
            $this->setErrorMessage("Blocco per chiave: $queueId non riuscito");
        }
        return $result;
    }

    protected function getMessageCustom($queueId) {
        $this->resetErrorMessage();
        $cacheKey = self::QUEUE_PREFIX . App::$utente->getKey('ditta') . '_' . $queueId;
        // Controllo esistenza coda
        $cacheNode = $this->cache->get($cacheKey);
        $currentTimestamp = time();
        if (!$cacheNode) {
            return false;
        }
        //Esclusi messggi disabilitati in fase di contegggio elementi 
        $activeMessages = array_filter($cacheNode['messages'], function($activeMessage) {
            return $activeMessage['message']['disabled'] == 0;
        });

        if (count($activeMessages) == 0) {
            return false;
        } else {
            $messageFound = array_shift($activeMessages);
            //torna la chiave del  elemento attivo
            $foundKey = $messageFound["message"]["uuid"];
        }

        $esitoSemaforo = false;
        $n = 0;
        while ($n++ < self::MAX_RETRIES && !$esitoSemaforo) {
            $esitoSemaforo = $this->blocca($queueId);
            usleep(self::MM_SLEEP_RETRIES); //sleep 
        }

        if ($esitoSemaforo) {

            $scroll = true;
            // array_shift fino a quando non trovo il primo attivo.
            // il controllo a monte esclude che tutti i messsage siano disabiltiati 
            while ($scroll) {
                $message = array_shift($cacheNode['messages']);
                if (( $message["message"]["uuid"]) == $foundKey) {
                    $scroll = false;
                }
            }

            // Aggiorna informazioni coda
            $cacheNode['status']['lastQueueModifyDateTime'] = $currentTimestamp;
            $cacheNode['status']['messagesToProcess'] --;
            // Aggiorna cache
            $result = $this->cache->set($cacheKey, $cacheNode, 0);
            if (!$result) {
                $this->setErrorMessage($this->cache->get_error());
            }
            $this->sblocca($queueId);
        } else {
            $this->setErrorMessage("Blocco per chiave :$queueId non riuscita");
        }
        return itaQueueMessage::fromArray($message);
    }

    protected function findCustom($queueId, $filters = array(), $arrayFormat) {
        $this->resetErrorMessage();
        $cacheKey = self::QUEUE_PREFIX . App::$utente->getKey('ditta') . '_' . $queueId;
        // Controllo esistenza coda
        $cacheNode = $this->cache->get($cacheKey);
        $currentTimestamp = time();
        if (!$cacheNode) {
            return false;
        }
        if (count($filters) != 0) {
            //Esclude i  messaggi che non soddisfano nessun criterio di ricerca 
            $messages = array();
            //applicare i filtri in or  
            $inputMessages = $cacheNode['messages'];
            foreach ($filters as $filter) {
                $key = $filter['key'];
                $value = $filter['value'];
                $bol = false;
                foreach ($inputMessages as $inputMessage) {
                    $bol = $this->find_key_value($inputMessage['message'], $key, $value);
                    if ($bol) {
                        $messages[] = $inputMessage;
                        break;
                    }
                }
            }
        } else {
            $messages = $cacheNode['messages'];
        }
        $messagesObj = array();
        if (!$arrayFormat) {
            //Trasforma una array in una lista di messages(
            foreach ($messages as $message) {
                $messagesObj[] = itaQueueMessage::fromArray($message);
            }
        } else {
            foreach ($messages as $message) {
                $messagesObj[] = $message['message'];
            }
        }
        return $messagesObj;
    }

    //filtro solo sui message 

    function find_key_value($array, $key, $val) {
        if ($array[$key] == $val) {
            return true;
        } else {
            return false;
        }
    }

    public function queueExistsCustom($queueId) {
        $this->resetErrorMessage();
        $cacheKey = self::QUEUE_PREFIX . App::$utente->getKey('ditta') . '_' . $queueId;
        $cacheNode = $this->cache->get($cacheKey);
        if (!$cacheNode) {
            return false;
        } else {
            return true;
        }
    }

    public function queueStatusCustom($queueId) {
        $this->resetErrorMessage();
        $cacheKey = self::QUEUE_PREFIX . App::$utente->getKey('ditta') . '_' . $queueId;
        $cacheNode = $this->cache->get($cacheKey);
        if (!$cacheNode) {
            $this->setErrorMessage($this->cache->get_error());
            return false;
        }
        return $cacheNode['status'];
    }

    private function sblocca($queueId) {
        $esitoSemaforo = $this->envLib->Semaforo(envLib::SEMAFORO_SBLOCCA, $queueId, NULL, envLib::TIPO_BLOCCO_CHIAVE);
        if ($esitoSemaforo === false) {
            $this->setErrorMessage("Sblocco per chiave :$queueId non riuscita");
        }
    }

    private function blocca($queueId) {
        return $this->envLib->Semaforo(envLib::SEMAFORO_BLOCCA, $queueId, NULL, envLib::TIPO_BLOCCO_CHIAVE);
    }

    public function updateLastMessageProcessed($queueId, $message, $errorCode, $errorDescription) {
        $this->resetErrorMessage();
        $cacheKey = self::QUEUE_PREFIX . App::$utente->getKey('ditta') . '_' . $queueId;
        $cacheNode = $this->cache->get($cacheKey);
        $created = true;
        $currentTimestamp = time();

        if ($cacheNode) {
            $cacheNode['status']['lastMessageProcessed'] = array(
                'uuid' => $message->getUuid(),
                'alias' => $message->getAlias(),
                'timestamp' => $currentTimestamp,
                'errorCode' => $errorCode,
                'errorDescription' => $errorDescription
            );

            $esitoSemaforo = false;
            $n = 0;
            while ($n++ < self::MAX_RETRIES && !$esitoSemaforo) {
                $esitoSemaforo = $this->blocca($queueId);
                usleep(self::MM_SLEEP_RETRIES); //sleep 
            }
            if ($esitoSemaforo) {
                $result = $this->cache->set($cacheKey, $cacheNode, 0);

                if (!$result) {
                    $this->setErrorMessage($this->cache->get_error());
                    $created = false;
                }
                $this->sblocca($queueId);
            } else {
                $this->setErrorMessage("Blocco per chiave :$queue non riuscita");
                $created = false;
            }
        }
    }

}

?>