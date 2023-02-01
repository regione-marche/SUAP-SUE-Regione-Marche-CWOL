<?php

require_once ITA_LIB_PATH . '/itaException/ItaException.php';

/**
 * Gestisce le connessioni per request
 * @author Lorenzo Pergolini
 */
class itaConnectionsPerRequest {

    const PRECONDITION_ERROR_CONNECTIONNAME = "Parametro connectionname mancante";
    const PRECONDITION_ERROR_LINKID = "Parametro oggetto resourceDb mancante";
    const PRECONDITION_ERROR_TRANSACTIONINFO = "Parametro array transactionInfo mancante";
    
    //Array delle connessioni attive per ogni request 
    private $connections;

    /**
     * Controlla se la connessione esiste nell'array "$connections"
     * @param string $connectionName nome della connessione
     * @return bool  esiste\non esiste
     */
    public function checkIfExistConnectioname($connectionName = "") {
        if (!$connectionName) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_CONNECTIONNAME);
        }
        $this->connections = $this->getConnections();
        return array_key_exists($connectionName, $this->connections);
    }

    /**
     * Ritorna array della connesione 
     * @param string $connectionName nome della connessione
     * @return array array connessione
     */
    public function getConnection($connectionName = "") {
        if (!$connectionName) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_CONNECTIONNAME);
        }
        $this->connections = $this->getConnections();
        return $this->connections[$connectionName];
    }

    /**
     * Effettua il salvattaggio delle connessone nella classe corrente (statica) 
     * @param string $connectionName nome della connessione
     * @param object $linkId oggetto resources db
     */
    public function saveConnectionPerRequest($connectionName, $linkId) {
        if (!$linkId) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_LINKID);
        }
        if ($this->checkIfExistConnectioname($connectionName) == false) {
            $this->connections = $this->getConnections();
            $this->connections[$connectionName] = array(
                'linkId' => $linkId,
                'transactionInfo' => array()
            );
        }
    }

    /**
     * Effettua il salvattaggio delle informazioni di transazione 
     * @param string $connectionName nome della connessione
     * @param array $transactionInfo  array delle transazioni 
     */
    public function saveTransactionInfo($connectionName, $transactionInfo = array()) {
        if (!$transactionInfo) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, self::PRECONDITION_ERROR_TRANSACTIONINFO);
        }
        if ($this->checkIfExistConnectioname($connectionName) == true) {
            $this->connections = $this->getConnections();
            $this->connections[$connectionName]['transactionInfo'] = $transactionInfo;
        }
    }

    /**
     * getter del array delle transazioni 
     * @param type $connectionName
     * @return array
     */
    public function getTransactionInfo($connectionName) {
        if ($this->checkIfExistConnectioname($connectionName) == true) {
            $this->connections = $this->getConnections();
            return $this->connections[$connectionName]['transactionInfo'];
        }
    }

    /**
     * getter delle connessioni per request
     * @return array 
     */
    public function getConnections() {
        if (!is_array($this->connections)) {
            $this->connections = array();
        }
        return $this->connections;
    }

}

?>
