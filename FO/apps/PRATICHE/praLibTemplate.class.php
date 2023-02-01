<?php

class praLibTemplate {

    const CUSTOM_ROOT = "customClass";

    private static $praLibTemplate;
    private $praLib;
    private $errMessage;
    private $errCode;
    public $dati;
    public $Ricdag_rec;
    public $keyPasso;
    public $styleLblBO;
    public $styleFldBO;
    public $defaultValue;
    public $classPosLabel;
    public $br;
    public $campoObl;
    public $risorse;

    public static function getInstance($praLib) {
        if (!isset(self::$praLibTemplate)) {
            $obj = new praLibTemplate();
            $obj->setPraLib($praLib);
            self::$praLibTemplate = $obj;
        }
        return self::$praLibTemplate;
    }

    public function getPraLib() {
        return $this->praLib;
    }

    public function setPraLib($praLib) {
        $this->praLib = $praLib;
    }

    public function getDati() {
        return $this->dati;
    }

    public function getClassPosLabel() {
        return $this->classPosLabel;
    }

    public function setDati($dati) {
        $this->dati = $dati;
    }

    public function setClassPosLabel($classPosLabel) {
        $this->classPosLabel = $classPosLabel;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getRicdag_rec() {
        return $this->Ricdag_rec;
    }

    public function getKeyPasso() {
        return $this->keyPasso;
    }

    public function getStyleLblBO() {
        return $this->styleLblBO;
    }

    public function getStyleFldBO() {
        return $this->styleFldBO;
    }

    public function getDefaultValue() {
        return $this->defaultValue;
    }

    public function getBr() {
        return $this->br;
    }

    public function getCampoObl() {
        return $this->campoObl;
    }

    public function setRicdag_rec($Ricdag_rec) {
        $this->Ricdag_rec = $Ricdag_rec;
    }

    public function setKeyPasso($keyPasso) {
        $this->keyPasso = $keyPasso;
    }

    public function setStyleLblBO($styleLblBO) {
        $this->styleLblBO = $styleLblBO;
    }

    function setStyleFldBO($styleFldBO) {
        $this->styleFldBO = $styleFldBO;
    }

    public function setDefaultValue($defaultValue) {
        $this->defaultValue = $defaultValue;
    }

    public function setBr($br) {
        $this->br = $br;
    }

    public function setCampoObl($campoObl) {
        $this->campoObl = $campoObl;
    }

    public function disegnaCampo($dati) {

        /*
         * Clean errore status
         */
        $this->setErrCode(0);
        $this->setErrMessage('');

        $Ricdag_rec = $this->Ricdag_rec;



        $classe = $Ricdag_rec['DAGCLASSE'];
        $metodo = $Ricdag_rec['DAGMETODO'];

        if (!$classe) {
            $this->setErrMessage('Classe non definita');
            return false;
        }

        if (!$metodo) {
            $this->setErrMessage('Metodo non definito');
            return false;
        }

        if (strpos($classe, "/") !== false) {
            list($customFolder, $customClass) = explode("/", $classe);
            $path = ITA_PRATICHE_PATH . "/PRATICHE_italsoft/" . self::CUSTOM_ROOT . "/" . $customFolder . "/" . $customClass . ".class.php";
        } else {
            $this->setErrMessage('Namespace  della classe definito');
            return false;
        }

        if (!file_exists($path)) {
            $this->setErrMessage('Il sorgente della classe non esiste.');
            return false;
        }
        require_once $path;
        $customObj = $customClass::getInstance($this->praLib);
        $customObj->setDati($dati);
        $customObj->setRicdag_rec($Ricdag_rec);
        $customObj->setStyleLblBO($this->styleLblBO);
        $customObj->setStyleFldBO($this->styleFldBO);
        $customObj->setDefaultValue($this->defaultValue);
        $customObj->setBr($br);
        $customObj->setCampoObl($this->campoObl);
        $retMetodo = $customObj->$metodo();
        //
        $this->setErrCode($customObj->getErrCode());
        $this->setErrMessage($customObj->getErrMessage());
        //
        if ($retMetodo === false) {
            return false;
        }
        return $retMetodo;
    }

}

?>
