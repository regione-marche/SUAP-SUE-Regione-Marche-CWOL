<?php

/**
 *
 * Interfaccia Gestione Documentale DocER - Servizio Identificazione
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPDocer
 * @author     
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    19.10.2017
 * @link
 * @see
 * @since
 * */
interface itaDocerIdentClientInterface {
    
    /**
     * login
     * @param array $param
     *                 - userId
     *                 - password
     *                 - codiceEnte
     *                 - application 
     * @return string Token di autenticazione
     */
    public function ws_login($param);
    
    /**
     * logout
     * @param array $param
     *                 - token
     * @return string Token di autenticazione
     */
    public function ws_logout($param);
    
}
