<?php

/**
 * Authenticator Factory
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
abstract class itaAuthenticatorFactory {
    
    /**
     * Restituisce un itaAuthenticator
     * @params string $model Model
     * @params array Parametri per generare Authenticator
     */
    abstract static function getAuthenticator($model, $params);

}

?>
