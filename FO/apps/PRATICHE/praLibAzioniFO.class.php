<?php

class praLibAzioniFO {

    const CUSTOM_ROOT = "customClass";
    const AZIONE_POST_ISTANZIA_RICHIESTA = "POST_ISTANZIA_RICHIESTA";
    const AZIONE_PRE_ISTANZIA_RICHIESTA = "PRE_ISTANZIA_RICHIESTA";
    const AZIONE_PRE_INOLTRA_RICHIESTA = "PRE_INOLTRA_RICHIESTA";
    const AZIONE_PRE_PROTOCOLLAZIONE_RICHIESTA = "PRE_PROTOCOLLAZIONE_RICHIESTA";
    const AZIONE_POST_PROTOCOLLAZIONE_RICHIESTA = "POST_PROTOCOLLAZIONE_RICHIESTA";
    const AZIONE_POST_INOLTRA_RICHIESTA = "POST_INOLTRA_RICHIESTA";
    const AZIONE_POST_SUBMIT_RACCOLTA = "POST_SUBMIT_RACCOLTA";

    private static $praLibAzioniFO;
    private $praLib;
    private $objErr;
    private $errMessage;
    private $errCode;

    public static function getInstance($praLib, $objErr) {
        if (!isset(self::$praLibAzioniFO)) {
            $obj = new praLibAzioniFO();
            $obj->setPraLib($praLib);
            $obj->setObjErr($objErr);
            self::$praLibAzioniFO = $obj;
        }
        return self::$praLibAzioniFO;
    }

    public function getObjErr() {
        return $this->praLib;
    }

    public function setObjErr($objErr) {
        $this->objErr = $objErr;
    }

    public function getPraLib() {
        return $this->praLib;
    }

    public function setPraLib($praLib) {
        $this->praLib = $praLib;
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

    public function eseguiAzione($codiceAzione, $dati, $azione_passo = false) {
        /*
         * Clean errore status
         */
        $this->setErrCode(0);
        $this->setErrMessage('');

        $richiesta = $dati['Ricite_rec']['RICNUM'];


        if (!$codiceAzione) {
            $this->setErrMessage('Codice azione Mancante');
            return true;
        }

        $sql = "SELECT * FROM RICAZIONI WHERE RICNUM='$richiesta' AND CODICEAZIONE = '$codiceAzione'";

        if ($azione_passo) {
            $sql.= " AND ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "'";
        }


        $Ricazioni_rec = ItaDB::DBSQLSelect($dati['PRAM_DB'], $sql, false);
        if (!$Ricazioni_rec) {
            $this->setErrMessage('Classe e Metodo non definiti');
            return true;
        }
        $classe = $Ricazioni_rec['CLASSEAZIONE'];
        $metodo = $Ricazioni_rec['METODOAZIONE'];

        if (!$classe) {
            $this->setErrMessage('Classe non definita');
            return true;
        }

        if (!$metodo) {
            $this->setErrMessage('Metodo non definita');
            return true;
        }

        if (strpos($classe, "/") !== false) {
            list($customFolder, $customClass) = explode("/", $classe);
            $path = ITA_PRATICHE_PATH . "/PRATICHE_italsoft/" . self::CUSTOM_ROOT . "/" . $customFolder . "/" . $customClass . ".class.php";
        } else {
            $this->setErrMessage('Namespace  della classe definito');
            return true;
        }


        if (!file_exists($path)) {
            $this->setErrMessage('Il sorgente della classe non esiste.');
            return true;
        }

        require_once $path;

        $customObj = $customClass::getInstance($this->praLib);
        $customObj->setDati($dati);
        $retMetodo = $customObj->$metodo();
        //
        $this->setErrCode($customObj->getErrCode());
        $this->setErrMessage($customObj->getErrMessage());
        //
        if (!$retMetodo) {
            switch ($Ricazioni_rec['ERROREAZIONE']) {
                case "CONT":
                    return true;
                case "":
                case "ERR":
                    output::$html_out = $this->objErr->parseError(__FILE__, "EA-" . praLibAzioniFO::AZIONE_POST_ISTANZIA_RICHIESTA, "Errore Azione metodo:$metodo-->" . $customObj->getErrMessage(), __CLASS__);
                    return false;
                case "WARN":
                    output::$html_out = $this->objErr->parseError(__FILE__, "EA-" . praLibAzioniFO::AZIONE_POST_ISTANZIA_RICHIESTA, "Errore Azione metodo:$metodo-->" . $customObj->getErrMessage(), __CLASS__, "", false);
                    return true;
            }
        }

        return true;
        //return $retMetodo;
    }

}

?>
