<?php

require_once ITA_LIB_PATH . '/itaPHPLogger/MonologHandlers/EqAuditHandler.php';
require_once ITA_LIB_PATH . '/itaPHPLogger/MonologHandlers/WebHandler.php';
require_once ITA_LIB_PATH . '/itaPHPLogger/MonologHandlers/ExceptionHandler.php';
require_once ITA_LIB_PATH . '/itaPHPLogger/MonologHandlers/EmailHandler.php';

class itaPHPLogger extends Psr\Log\AbstractLogger {

    /**
     * Livelli di log, vedi Monolog\Logger.
     */
    const DEBUG = 100;
    const INFO = 200;
    const NOTICE = 250;
    const WARNING = 300;
    const ERROR = 400;
    const CRITICAL = 500;
    const ALERT = 550;
    const EMERGENCY = 600;

    const ROTATE_FILE_PER_DAY = Monolog\Handler\RotatingFileHandler::FILE_PER_DAY;
    const ROTATE_FILE_PER_MONTH = Monolog\Handler\RotatingFileHandler::FILE_PER_MONTH;
    const ROTATE_FILE_PER_YEAR = Monolog\Handler\RotatingFileHandler::FILE_PER_YEAR;
    
    protected $logger;

    /**
     * Instanzia una classe di itaPHPLogger.
     * @param string $name Nome univoco per distinguere il logger
     * @param boolean $catchPHPErrors 
     */
    public function __construct($name, $catchPHPErrors = true) {
        $this->logger = new Monolog\Logger($name);

        if ($catchPHPErrors) {
            $handler = Monolog\ErrorHandler::register($this->logger);
            $handler->registerErrorHandler(array(), false, -1, false);
        }
    }

    /**
     * Aggiunge l'handler di log su file.
     * @param string $stream  Percorso del file.
     * @param integer $level  Livello minimo dei log da registrare con questo handler.
     * @param boolean $bubble Se 'false', i log si fermeranno con questo handler.
     * @return \itaPHPLogger
     */
    public function pushFile($stream, $level = self::DEBUG, $bubble = true) {
        $this->logger->pushHandler(new Monolog\Handler\StreamHandler($stream, $level, $bubble));
        return $this;
    }

    /**
     * Aggiunge l'handler di log su file ruotato.
     * @param type $stream  Percorso del file.
     * @param type $maxFiles Numero massimo di files da mantenere
     * @param type $rotateDateFormat  Formato di rotasione vedi costanti
     * @param type $level   Livello minimo dei log da registrare con questo handler.
     * @param type $bubble  Se 'false', i log si fermeranno con questo handler.
     * @return \itaPHPLogger
     */
    public function pushRotatingFile($stream, $maxFiles = 2, $rotateDateFormat = self::ROTATE_FILE_PER_MONTH, $level = self::DEBUG, $bubble = true) {
        $rotatingFileAHandler = new Monolog\Handler\RotatingFileHandler($stream, $maxFiles, $level, $bubble);
        $rotatingFileAHandler->setFilenameFormat('{filename}-{date}', $rotateDateFormat);
        $this->logger->pushHandler($rotatingFileAHandler);
        return $this;
    }
    
    
    
    /**
     * Aggiunge l'handler di log tramite EqAudit.
     * @param \itaModel $model Model chiamante.
     * @param integer $level   Livello minimo dei log da registrare con questo handler.
     * @param boolean $bubble  Se 'false', i log si fermeranno con questo handler.
     * @return \itaPHPLogger
     */
    public function pushEqAudit($model, $level = self::DEBUG, $bubble = true) {
        $this->logger->pushHandler(new EqAuditHandler($model, $level, $bubble));
        return $this;
    }

    /**
     * Aggiunge l'handler di log tramite SysLog.
     * @param integer $level   Livello minimo dei log da registrare con questo handler.
     * @param boolean $bubble  Se 'false', i log si fermeranno con questo handler.
     * @return \itaPHPLogger
     */
    public function pushSysLog($level = self::DEBUG, $bubble = true) {
        $this->logger->pushHandler(new Monolog\Handler\SyslogHandler($this->logger->getName(), LOG_USER, $level, $bubble));
        return $this;
    }

    /**
     * Aggiunge l'handler di log tramite interfaccia web.
     * @param integer $level   Livello minimo dei log da registrare con questo handler.
     * @param boolean $bubble  Se 'false', i log si fermeranno con questo handler.
     * @return \itaPHPLogger
     */
    public function pushWeb($level = self::DEBUG, $bubble = true) {
        $this->logger->pushHandler(new WebHandler($level, $bubble));
        return $this;
    }

    /**
     * Aggiunge l'handler di log tramite Exception.
     * @param integer $level   Livello minimo dei log da registrare con questo handler.
     * @param boolean $bubble  Se 'false', i log si fermeranno con questo handler.
     * @return \itaPHPLogger
     */
    public function pushException($level = self::DEBUG, $bubble = true) {
        $this->logger->pushHandler(new ExceptionHandler($level, $bubble));
        return $this;
    }

    /**
     * Aggiunge l'handler di log tramite Email.
     * @param integer $level   Livello minimo dei log da registrare con questo handler.
     * @param boolean $bubble  Se 'false', i log si fermeranno con questo handler.
     * @return \itaPHPLogger
     */
    public function pushEmail($level = self::DEBUG, $bubble = true, $toAddress = '') {
        $this->logger->pushHandler(new EmailHandler($level, $bubble, $toAddress));
        return $this;
    }

    public function log($level, $message, array $context = array()) {
        $this->logger->log($level, $message, $context);
    }

}
