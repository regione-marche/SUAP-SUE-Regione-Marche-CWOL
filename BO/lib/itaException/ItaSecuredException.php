<?php

/*
 * Gestione delle eccezioni db php custom
 * @author Pergolini Lorenzo 0.0.1
 */

require_once ITA_LIB_PATH . '/itaException/ItaException.php';
require_once ITA_LIB_PATH . '/itaPHPLogger/itaPHPLogger.php';

class ItaSecuredException extends ItaException {
    private $securedMessage;
    private $exceptionClientIpAdress;
    private $exceptionUser;
    private $exceptionDomain;
    private $exceptionId;
    private $logger;

    public static function newItaSecuredException($type, $code, $message, $securedMessage = null) {
        $exception = new ItaSecuredException($message, $code);
        $exception->setType($type);
        $exception->setNativeErrorCode($code);
        $exception->setNativeErroreDesc($message);
        $exception->setSecuredMessage($securedMessage);
        $exception->setExceptionClientIpAdress();
        $exception->setExceptionUser();
        $exception->setExceptionDomain();
        $exception->setExceptionId();

        $log_folder = itaLib::getLogPath('itaSecuredException',true);
        $log_file = $log_folder . DIRECTORY_SEPARATOR . 'itaSecuredException.txt';
      
        $exception->logger = new itaPHPLogger('itaSecuredException', false);
        $exception->logger->pushRotatingFile($log_file,2,itaPHPLogger::ROTATE_FILE_PER_MONTH);
        $exception->log();
        return $exception;
    }

    public function __construct($message = "", $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function getSecuredMessage() {
        return $this->securedMessage;
    }

    public function setSecuredMessage($securedMessage) {
        $this->securedMessage = $securedMessage;
    }

    private function setExceptionId() {
        $this->exceptionId = hash('sha256', uniqid($this->exceptionClientIpAdress . "_" . $this->exceptionUser . "_" . microtime(true), true));
    }

    private function setExceptionClientIpAdress() {
        $this->exceptionClientIpAdress = $_SERVER['REMOTE_ADDR'];
    }

    private function setExceptionUser() {
        $this->exceptionUser = App::$utente->getKey('nomeUtente');
    }

    public function setExceptionDomain() {
        $this->exceptionDomain = App::$utente->getKey('ditta');
    }

    public function getCompleteSecuredMessage() {
        return "<b>" . $this->nativeErrorCode . "</b>" . ' - ' . $this->securedMessage;
    }

    public function getExceptionClientIpAdress() {
        return $this->exceptionClientIpAdress;
    }

    public function getExceptionUser() {
        return $this->exceptionUser;
    }

    public function getExceptionId() {
        return $this->exceptionId;
    }

    public function getExceptionDomain() {
        return $this->exceptionDomain;
    }

    public function log() {
        $this->logger->log(
                itaPHPLogger::ERROR, "[Exception id: " . $this->getExceptionId() . "]"
                . "[Client Ip Adress: {$this->getExceptionClientIpAdress()}]"
                . "[Utente: {$this->exceptionUser}@{$this->getExceptionDomain()}]"
                . "[File: {$this->file}]"
                . "[Linea: {$this->line}]"
                . "[Messaggio: {$this->getSecuredMessage()}]"
        );
    }

}
