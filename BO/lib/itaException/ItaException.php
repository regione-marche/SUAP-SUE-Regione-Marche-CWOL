<?php

/*
 * Gestione delle eccezioni db php custom
 * @author Pergolini Lorenzo 0.0.1
 */

class ItaException extends Exception {

    const TYPE_ERROR_DB = 1;
    const TYPE_ERROR_PHP = 2;
    const TYPE_ERROR_CUSTOM = 3;

    public static $TYPE_ERROR_LIST = array(
        self::TYPE_ERROR_DB => 'Errore DBMS',
        self::TYPE_ERROR_PHP => 'Errore Generico',
        self::TYPE_ERROR_CUSTOM => 'Errore Personalizzato'
    );
    
    private $type;
    private $nativeErrorCode;
    private $nativeErroreDesc;
    
    public function __construct($message, $code = 0, $previous = null) {
        parent::__construct(strval($message), intval($code), $previous);
    }
    
    public static function newItaException($type, $code, $desc) {
       $exception = new ItaException($desc, $code);
       $exception->setType($type);
       $exception->setNativeErrorCode($code);
       $exception->setNativeErroreDesc($desc);
       return $exception;
   }
    
   public function getType() {
       return $this->type;
   }

   public function getNativeErrorCode() {
       return $this->nativeErrorCode;
   }

   public function getNativeErroreDesc() {
       return $this->nativeErroreDesc;
   }

   public function setType($type) {
       $this->type = $type;
   }

   public function setNativeErrorCode($nativeErrorCode) {
       $this->nativeErrorCode = $nativeErrorCode;
   }

   public function setNativeErroreDesc($nativeErroreDesc) {
       $this->nativeErroreDesc = $nativeErroreDesc;
   }
   
   public function getCompleteErrorMessage() {
       return "<b>" .$this->nativeErrorCode. "</b>". ' - ' . $this->nativeErroreDesc;
   }
   
   
   
}

?>
