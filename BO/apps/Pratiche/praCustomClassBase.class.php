<?php

class praCustomClass {

    public $PRAM_DB;
    public $datiPasso;
    public $datoAggiuntivo;
    public $datiAggiuntivi;
    public $dizionario;
    public $callerForm;
    protected $praLib;
    protected $errCode;
    protected $errMessage;
    protected static $customObj;

    public static function getInstance($customClass) {
        try {
            $obj = new $customClass();
            return $obj;
        } catch (Exception $e) {
            return false;
        }
    }

    public function setPRAM_DB($PRAM_DB) {
        $this->PRAM_DB = $PRAM_DB;
    }

    public function setDatiPasso($datiPasso) {
        $this->datiPasso = $datiPasso;
    }

    public function setDatoAggiuntivo($datoAggiuntivo) {
        $this->datoAggiuntivo = $datoAggiuntivo;
    }

    public function setDatiAggiuntivi($datiAggiuntivi) {
        $this->datiAggiuntivi = $datiAggiuntivi;
    }

    public function setDizionario($dizionario) {
        $this->dizionario = $dizionario;
    }

    public function setCallerForm($callerForm) {
        $this->callerForm = $callerForm;
    }

    public function setPraLib($praLib) {
        $this->praLib = $praLib;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

}
