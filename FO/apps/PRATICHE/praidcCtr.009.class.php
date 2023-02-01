<?php

/**
 *
 * CLASSE CONTROLLO CAMPI AGGIUNTIVI (ESTREMI RESIDENZA DICHIARANTE)
 *
 * */
require_once ITA_SUAP_PATH . "/BASE_italsoft/basLib.class.php";

class praidcCtr009 {

    private $codice;
    private $descrizione;
    private $dizionario;
    private $basLib;
    private $errorCode;
    private $msgError;

    public function __construct() {
        $this->basLib = new basLib();
        $this->setCodice("009");
        $this->setDescrizione("Cap");
        $this->setDizionario(
                array(
                    array(
                        "NOMECAMPO" => "DICHIARANTE_RESIDENZACAP_CAP",
                        "DESCRIZIONECAMPO" => "Cap Residenza Dichiarante",
                        "VARIABILE" => "",
                    ),
                    array(
                        "NOMECAMPO" => "DICHIARANTE_RESIDENZACOMUNE",
                        "DESCRIZIONECAMPO" => "Comune Residenza Dichiarante",
                        "VARIABILE" => "",
                    ),
                    array(
                        "NOMECAMPO" => "DICHIARANTE_RESIDENZAPROVINCIA_PV",
                        "DESCRIZIONECAMPO" => "Provincia Residenza Dichiarante",
                        "VARIABILE" => "",
                    )
                )
        );
    }

    public function getMsgError() {
        return $this->msgError;
    }

    public function setMsgError($msgError) {
        $this->msgError = $msgError;
    }

    public function getErrorCode() {
        return $this->errorCode;
    }

    public function setErrorCode($errorCodce) {
        $this->errorCode = $errorCodce;
    }

    public function getCodice() {
        return $this->codice;
    }

    public function setCodice($codice) {
        $this->codice = $codice;
    }

    public function getDescrizione() {
        return $this->descrizione;
    }

    public function setDescrizione($desc) {
        $this->descrizione = $desc;
    }

    public function getDizionario() {
        return $this->dizionario;
    }

    public function setDizionario($dizionario) {
        $this->dizionario = $dizionario;
    }

    public function Controlla($valore, $descCampo) {
        $comuni_rec = $this->basLib->getComuni($valore, "cap");
        if (!$comuni_rec) {
            $this->setErrorCode("-1");
            $this->setMsgError("$descCampo non valida<br>");
            return false;
        }
        return true;
    }

}

?>