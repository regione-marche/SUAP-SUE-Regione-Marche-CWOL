<?php

/*
 * Gestione delle eccezioni db php custom
 * @author Pergolini Lorenzo 0.0.1
 */

require_once ITA_LIB_PATH . '/itaException/ItaException.php';

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

        if (defined('ITA_FRONTOFFICE_LOG')) {
            $log_folder = ITA_FRONTOFFICE_LOG;
            $log_file = $log_folder . DIRECTORY_SEPARATOR . 'itaSecuredException.txt';

            if (class_exists('Monolog\Logger')) {
                require_once ITA_LIB_PATH . '/itaPHPLogger/itaPHPLogger.php';
                $exception->logger = new itaPHPLogger('itaSecuredException', false);
                $exception->logger->pushRotatingFile($log_file, 2, itaPHPLogger::ROTATE_FILE_PER_MONTH);
            }

            $exception->log();
        }

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
        $this->exceptionUser = frontOfficeApp::$cmsHost->getUserName();
    }

    public function setExceptionDomain() {
        $this->exceptionDomain = frontOfficeApp::getEnte();
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
        $errorString = "[Exception id: " . $this->getExceptionId() . "]"
            . "[Client Ip Adress: {$this->getExceptionClientIpAdress()}]"
            . "[Utente: {$this->exceptionUser}@{$this->getExceptionDomain()}]"
            . "[File: {$this->file}]"
            . "[Linea: {$this->line}]"
            . "[Messaggio: {$this->getSecuredMessage()}]";

        if (!is_null($this->logger)) {
            $this->logger->log(itaPHPLogger::ERROR, $errorString);
        } else {
            $log_folder = ITA_FRONTOFFICE_LOG;
            $log_file = $log_folder . DIRECTORY_SEPARATOR . 'itaSecuredException.txt';
            file_put_contents($log_file, $errorString . "\n", FILE_APPEND);
        }
    }

}
