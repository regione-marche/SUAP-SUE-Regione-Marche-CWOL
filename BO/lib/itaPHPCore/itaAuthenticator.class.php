<?php

/**
 * Authenticator 
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
abstract class itaAuthenticator {
    
    const ACTION_READ = 0;
    const ACTION_WRITE = 1;
    const ACTION_DELETE = 2;
        
    /**
     * Controlla se l'azione in ingresso è permessa
     * @param int $actionType Tipo di azione
     * @return boolean Azione permessa (true/false)
     */
    abstract function isActionAllowed($actionType);
    
}

?>
