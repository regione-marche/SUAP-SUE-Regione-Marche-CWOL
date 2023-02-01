<?php

/**
 * Definizione errori generici database 
 * @author l.pergolini
 */
define ("ERRORE_DB_APERTURA_CONNESSIONE",1,FALSE);
define ("ERRORE_DB_CAMPO_NULL",2,FALSE);
define ("ERRORE_DB_VIOLAZIONE_CHIAVE_PRIMARIA",3,FALSE);
define ("ERRORE_DB_APERTURA_TRANSAZIONE_MANCANTE_PER_COMMIT",4,FALSE);
define ("ERRORE_DB_TABELLA_DA_ELIMINARE_INESISTENTE",5,FALSE);
define ("ERRORE_DB_OGGETTO_MANCANTE",6,FALSE);
define ("ERRORE_DB_SINTASSI_NON_CORRETTA",7,FALSE);
define ("ERRORE_DB_STATEMENT_NON_PRONTO",8,FALSE);
define ("ERRORE_DB_CONSTRAINT_DA_ELIMINARE_INESISTENTE",9,FALSE);
define ("ERRORE_DB_INDICE_DA_ELIMINARE_ERRATO",10,FALSE);
define ("ERRORE_DB_APERTURA_TRANSAZIONE_MANCANTE_PER_ROLLBACK",11,FALSE);
define ("ERRORE_DB_COLLEGAMENTO_DURANTE_COMUNICAZIONE",12,FALSE);
define ("ERRORE_DB_COLLEGAMENTO_DURANTE_LOGIN",13,FALSE);
define ("ERRORE_DB_CREAZIONE_INDICE_UNIVOCO",14,FALSE);
define ("ERRORE_DB_DEFINIZIONE_CHIAVE_PRIMARIA_NULL",15,FALSE);

/**
 * Classe che contiene delle funzioni di utilità per i messggi di errore
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class ItaDbError {
    
    /**
     * Array che contiene le costanti degli errori generici
     */
    private $dbMessages = array();
    
    public static function getDBErrorMessage($cod) {
        if (array_key_exists($cod, $this->dbMessages)) {
            return $this->dbMessages[$cod];
        } else {
            return null;
        }
    }
    
}

?>