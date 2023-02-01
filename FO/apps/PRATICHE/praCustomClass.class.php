<?php

class praCustomClass {

    public $PRAM_DB;

    /**
     * @var array Contiene tutti i dati riguardanti il procedimento nello stato
     * attuale
     */
    public $dati;

    /**
     * @var array Contiene i dati del disegno (Ricdag_rec, styleLblBO, styleFldBO,
     * defaultValue, classPosLabel, br, campoObl)
     */
    public $datiPasso;
    public $keyPasso;

    /**
     * @var array Array per risorse varie
     */
    public $risorse;
    protected $praLib;
    protected $errCode;
    protected $errMessage;
    protected static $customObj;

    public static function getInstance($customClass) {
        try {
            $obj = new $customClass();
            return $obj;
        } catch (Exception $exc) {
            return false;
        }
    }

    public function getDati() {
        return $this->dati;
    }

    public function setDati($dati) {
        $this->dati = $dati;
    }

    public function getDatiPasso() {
        return $this->datiPasso;
    }

    public function setDatiPasso($datiPasso) {
        $this->datiPasso = $datiPasso;
    }

    public function getKeyPasso() {
        return $this->keyPasso;
    }

    public function setKeyPasso($keyPasso) {
        $this->keyPasso = $keyPasso;
    }

    public function getRisorse() {
        return $this->risorse;
    }

    public function setRisorse($risorse) {
        $this->risorse = $risorse;
    }

    public function getPraLib() {
        return $this->praLib;
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
