<?php

class praLibCustomClass {

    const CUSTOM_ROOT = "customClass";

    /*
     * Azioni
     */
    const AZIONE_PRE_ISTANZIA_RICHIESTA = "PRE_ISTANZIA_RICHIESTA";
    const AZIONE_POST_ISTANZIA_RICHIESTA = "POST_ISTANZIA_RICHIESTA";
    const AZIONE_PRE_INOLTRA_RICHIESTA = "PRE_INOLTRA_RICHIESTA";
    const AZIONE_POST_INOLTRA_RICHIESTA = "POST_INOLTRA_RICHIESTA";
    const AZIONE_PRE_PROTOCOLLAZIONE_RICHIESTA = "PRE_PROTOCOLLAZIONE_RICHIESTA";
    const AZIONE_POST_PROTOCOLLAZIONE_RICHIESTA = "POST_PROTOCOLLAZIONE_RICHIESTA";
    const AZIONE_PRE_RENDER_PASSO = "PRE_RENDER_PASSO";
    const AZIONE_POST_RENDER_PASSO = "POST_RENDER_PASSO";
    const AZIONE_PRE_RENDER_INFO_RICHIESTA = "PRE_RENDER_INFO_RICHIESTA";
    const AZIONE_POST_RENDER_INFO_RICHIESTA_NUMERO = "POST_RENDER_INFO_RICHIESTA_NUMERO";
    const AZIONE_POST_RENDER_INFO_RICHIESTA_OGGETTO = "POST_RENDER_INFO_RICHIESTA_OGGETTO";
    const AZIONE_POST_RENDER_INFO_RICHIESTA = "POST_RENDER_INFO_RICHIESTA";

    /*
     * Azioni Passo
     */
    const AZIONE_PRE_SUBMIT_RACCOLTA = 'PRE_SUBMIT_RACCOLTA';
    const AZIONE_POST_SUBMIT_RACCOLTA = 'POST_SUBMIT_RACCOLTA';
    const AZIONE_PRE_RENDER_RACCOLTA = 'PRE_RENDER_RACCOLTA';
    const AZIONE_POST_RENDER_RACCOLTA = 'POST_RENDER_RACCOLTA';
    const AZIONE_PRE_UPLOAD_ALLEGATO = 'PRE_UPLOAD_ALLEGATO';
    const AZIONE_POST_UPLOAD_ALLEGATO = 'POST_UPLOAD_ALLEGATO';

    /*
     * Risultato Azioni
     */
    const AZIONE_RESULT_CONTINUE = 'CONT';
    const AZIONE_RESULT_WARNING = 'WARN';
    const AZIONE_RESULT_ERROR = 'ERR';
    const AZIONE_RESULT_INVALID = 'INV';

    private $praLib;
    private $errCode;
    private $errMessage;
    private $customResult;
    private static $customObj;

    public static function getInstance($praLib) {
        // if (!isset(self::$customObj)) {
        $obj = new praLibCustomClass();
        $obj->setPraLib($praLib);
        //    self::$customObj = $obj;
        //}
        //return self::$customObj;
        return $obj;
    }

    public function setPraLib($praLib) {
        $this->praLib = $praLib;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getCustomResult() {
        return $this->customResult;
    }

    private function istanziaCustomClass($classe) {
        if (!$classe) {
            $this->setErrMessage('Classe non definita');
            return false;
        }

        if (strpos($classe, '/') === false) {
            $this->setErrMessage('Namespace della classe definito');
            return false;
        }

        list($customFolder, $customClass) = explode('/', $classe);
        $path = ITA_PRATICHE_PATH . "/PRATICHE_italsoft/" . self::CUSTOM_ROOT . "/" . $customFolder . "/" . $customClass . ".class.php";

        if (!file_exists($path)) {
            $this->setErrMessage('Il sorgente della classe non esiste.');
            return false;
        }

        require_once $path;

        return $customClass::getInstance($customClass);
    }

    public function eseguiAzione($codiceAzione, $dati, $azione_passo = false, $risorse = array()) {
        /*
         * Clean errore status
         */
        $this->setErrCode(0);
        $this->setErrMessage('');

        $richiesta = $dati['Proric_rec']['RICNUM'];
        $sportello = $dati['Proric_rec']['RICTSP'];

        if (!$codiceAzione) {
            $this->setErrMessage('Codice azione Mancante');
            return false;
        }

        $sql = "SELECT *
                FROM RICAZIONI
                WHERE
                    CLASSEAZIONE != '' AND
                    METODOAZIONE != '' AND
                    RICNUM = '$richiesta' AND
                    CODICEAZIONE = '$codiceAzione' AND
                    PRATSP = 0";

        if ($azione_passo) {
            $sql .= " AND ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "'";
        }

        $Ricazioni_rec = ItaDB::DBSQLSelect($dati['PRAM_DB'], $sql, false);

        if (!$Ricazioni_rec) {
            $sql = "SELECT *
                    FROM RICAZIONI
                    WHERE
                        CLASSEAZIONE != '' AND
                        METODOAZIONE != '' AND
                        RICNUM = '$richiesta' AND
                        CODICEAZIONE = '$codiceAzione' AND
                        PRATSP = '$sportello'";

            $Ricazioni_rec = ItaDB::DBSQLSelect($dati['PRAM_DB'], $sql, false);

            if (!$Ricazioni_rec) {
                $this->setErrMessage('Classe e Metodo non definiti');
                return false;
            }
        }

        $classe = $Ricazioni_rec['CLASSEAZIONE'];
        $metodo = $Ricazioni_rec['METODOAZIONE'];

        if (!$metodo) {
            $this->setErrMessage('Metodo non definito');
            return false;
        }

        $customObj = $this->istanziaCustomClass($classe);
        if (!$customObj) {
            return false;
        }

        $customObj->setPraLib($this->praLib);
        $customObj->setDati($dati);
        $customObj->setKeyPasso($dati['Ricite_rec']['ITEKEY']);
        $customObj->setRisorse($risorse);
        $customRes = $customObj->$metodo();

        $this->customResult = $customRes;

        if (!$customRes) {
            $retResult = $Ricazioni_rec['ERROREAZIONE'];

            if (!$retResult) {
                $retResult = self::AZIONE_RESULT_ERROR;
            }

            $this->setErrCode($customObj->getErrCode());
            $this->setErrMessage($customObj->getErrMessage());
            return $retResult;
        }

        return true;
    }

    public function eseguiDisegnoCampo($dati, $datiPasso) {
        /*
         * Clean errore status
         */
        $this->setErrCode(0);
        $this->setErrMessage('');

        $Ricdag_rec = $datiPasso['Ricdag_rec'];

        $classe = $Ricdag_rec['DAGCLASSE'];
        $metodo = $Ricdag_rec['DAGMETODO'];

        if (!$metodo) {
            $this->setErrMessage('Metodo non definito');
            return false;
        }

        $customObj = $this->istanziaCustomClass($classe);
        if (!$customObj) {
            return false;
        }

        $customObj->setPraLib($this->praLib);
        $customObj->setDati($dati);
        $customObj->setDatiPasso($datiPasso);
        $customRes = $customObj->$metodo();

        $this->customResult = $customRes;

        $this->setErrCode($customObj->getErrCode());
        $this->setErrMessage($customObj->getErrMessage());

        return $customRes;
    }

    public function checkEseguiEvento($evento, $Ricdag_rec) {
        $meta = unserialize($Ricdag_rec['DAGMETA']);
        return (boolean) $meta['CUSTOMEVENT'][$evento];
    }

    public function eseguiEvento($evento, $dati, $Ricdag_rec, $request) {
        /*
         * Clean errore status
         */
        $this->setErrCode(0);
        $this->setErrMessage('');

        $meta = unserialize($Ricdag_rec['DAGMETA']);

        if (!$meta['CUSTOMEVENT'][$evento]) {
            $this->setErrMessage('Evento non presente');
            return false;
        }

        $classe = $meta['CUSTOMEVENT'][$evento]['CLASS'];
        $metodo = $meta['CUSTOMEVENT'][$evento]['METHOD'];

        if (!$metodo) {
            $this->setErrMessage('Metodo non definito');
            return false;
        }

        $customObj = $this->istanziaCustomClass($classe);
        if (!$customObj) {
            return false;
        }

        $customObj->setPraLib($this->praLib);
        $customObj->setDati($dati);
        $customObj->setDatiPasso($datiPasso);
        $customRes = $customObj->$metodo($request);

        $this->customResult = $customRes;

        $this->setErrCode($customObj->getErrCode());
        $this->setErrMessage($customObj->getErrMessage());

        return $customRes;
    }

}
