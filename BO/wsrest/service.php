<?php

function serviceRestErrorHandler($errno, $errstr, $errfile, $errline) {  
    return;
    if ($errno === E_STRICT || $errno === E_DEPRECATED || $errno === E_NOTICE) {
        return;
    }
    
    header("HTTP/1.0 500 Errore di inizializzazione ($errstr)");
    die();                    
}
set_error_handler("serviceRestErrorHandler");

require_once('lib/wsServerRest.class.php');
ob_start();
$server = new wsServerRest();
$server->dispatch();


?>