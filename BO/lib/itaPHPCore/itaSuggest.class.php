<?php

class itaSuggest {

    static private $suggests = array();
    static private $limit = -1;
    static private $sentDataOnLimitOverflow = false;
    static private $limitMessage = '';
    static private $notFoundMessage = '';
    static private $keyMore = '__MORE__';

    /**
     * Imposta il comportamento adottato se i risultati superano il parametro $limit
     * @param boolean $sentDataOnLimitOverflow True invia comunque i risultati,
     * False invia solo il messaggio (default: false)
     */
    static public function setSentDataOnLimitOverflow($sentDataOnLimitOverflow) {
        self::$sentDataOnLimitOverflow = $sentDataOnLimitOverflow;
    }

    /**
     * Ritorna la stringa di ricerca
     * @return type
     */
    static public function getQuery() {
        return $_POST['q'];
    }

    /**
     * Imposta il limite di risultati da visualizzare
     * Default: -1 (non limitare)
     * @param type $limit
     */
    static public function setLimit($limit) {
        self::$limit = $limit;
    }

    /**
     * Ritorna il limite impostato
     * @return type
     */
    public static function getLimit() {
        return self::$limit;
    }

    /**
     * Imposta il messaggio da visualizzare se il numero di risultati
     * supera il limite
     * Default: vuoto
     * @param type $limitMessage
     */
    public static function setLimitMessage($limitMessage) {
        self::$limitMessage = $limitMessage;
    }

    /**
     * Imposta il messaggio da visualizzare se non ci sono risultati
     * @param type $notFoundMessage
     */
    public static function setNotFoundMessage($notFoundMessage) {
        self::$notFoundMessage = $notFoundMessage;
    }

    /**
     * Aggiunge un risultato alla ricerca
     * @param type $value Il valore che andrà sul campo suggest
     * @param type $extra Un array associativo dove la chiave rappresenta l'id
     * del campo da valorizzare ed il valore il contenuto con cui popolarlo
     */
    static public function addSuggest($value, $extra = array()) {
        $suggest_row = $value;

        foreach ($extra as $k => $v) {
            $suggest_row .= "|$k|$v";
        }

        self::$suggests[] = $suggest_row;
    }

    /**
     * Chiude la suggest
     */
    static public function sendSuggest() {
        ob_clean();

        header('Content-Type: text/plain; charset=ISO-8859-15');

        if (count(self::$suggests) > self::$limit && self::$limit > 0 && self::$sentDataOnLimitOverflow === false) {
            echo "\n" . self::$keyMore . "|" . self::$limitMessage;
            exit();
        }

        if (self::$limit > 0) {
            echo implode("\n", array_slice(self::$suggests, 0, self::$limit));
        } else {
            echo implode("\n", self::$suggests);
        }

        if (count(self::$suggests) > self::$limit && self::$limit > 0 && self::$sentDataOnLimitOverflow === true) {
            echo "\n" . self::$keyMore . "|" . self::$limitMessage;
        }

        if (count(self::$suggests) === 0 && self::$notFoundMessage) {
            echo "\n" . self::$keyMore . "|" . self::$notFoundMessage;
        }                
        
        exit();
    }

}
