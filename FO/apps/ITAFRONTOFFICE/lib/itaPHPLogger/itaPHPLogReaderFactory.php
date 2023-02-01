<?php
class itaPHPLogReaderFactory{
    const LOG_PROVIDER = 'monologFile';
    
    /**
     * Restituisce una classe che soddisfa l'interfaccia itaPHPLogReaderInterface
     * @param string $log Riferimento del log da leggere
     * @return boolean|\itaPHPLogReaderInterface 
     */
    public static function getInstance($log,$logProvider=null){
        if(!isSet($logProvider)) $logProvider = self::LOG_PROVIDER;
        
        switch($logProvider){
            case 'monologFile':
                require_once ITA_LIB_PATH . '/itaPHPLogger/itaPHPLogReader/itaPHPLogReaderMonologFile.class.php';
                return new itaPHPLogReaderMonologFile($log);
            default:
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Log provider non gestito. Controllare il campo log_provider in config.ini');
        }
    }
}
