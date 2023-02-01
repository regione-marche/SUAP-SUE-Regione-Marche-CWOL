<?php

include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';

class envLibStrutture {

    const CONTEXT_AMT = 'amt';
    const CONTEXT_BDAP = 'bdap';
    const CONTEXT_ALL = '';

    public static $CONTEXT_LIST = array(
        array('CODICE' => self::CONTEXT_AMT, 'DESCRIZIONE' => 'Amministrazione Trasparente'),
        array('CODICE' => self::CONTEXT_BDAP, 'DESCRIZIONE' => 'Monitoraggio Opere Pubbliche'),
        array('CODICE' => self::CONTEXT_ALL, 'DESCRIZIONE' => 'Tutti i contesti')
    );
    public $envLib;
    private $errMessage;
    private $errCode;

    function __construct() {
        try {
            $this->envLib = new envLib();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
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

    public function getITALWEB_DB() {
        return $this->envLib->getITALWEB_DB();
    }

    public function getConstants() {
        $reflectionClass = new ReflectionClass($this);
        return $reflectionClass->getConstants();
    }

    static function getCONTEXT_LIST() {
        return self::$CONTEXT_LIST;
    }

}
