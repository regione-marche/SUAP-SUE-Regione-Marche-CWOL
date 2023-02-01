<?php

include_once ITA_BASE_PATH . '/apps/Pratiche/praCustomClassBase.class.php';

class praLibCustomClass {

    private $praLib;
    private $errCode;
    private $errMessage;

    /*
     * Risultato Azioni
     */

    const AZIONE_RESULT_CONTINUE = 'CONT';
    const AZIONE_RESULT_WARNING = 'WARN';
    const AZIONE_RESULT_ERROR = 'ERR';
    const AZIONE_RESULT_INVALID = 'INV';

    public function __construct($praLib) {
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
        $path = ITA_BASE_PATH . '/apps/Pratiche/customClass/' . $customFolder . '/' . $customClass . '.class.php';

        if (!file_exists($path)) {
            $this->setErrMessage('Il sorgente della classe non esiste.');
            return false;
        }

        include_once $path;

        return $customClass::getInstance($customClass);
    }

    public function checkEseguiAzione($codiceAzione, $numeroRichiesta, $chiavePasso = false) {
        if (!$codiceAzione || !$numeroRichiesta) {
            return false;
        }

        /*
         * Verifico flag modello centralizzato
         */

        $PRAM_DB = $this->praLib->getPRAMDB();

        $Anapra_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT PRASLAVE FROM ANAPRA WHERE PRANUM = '$numeroRichiesta'", false);
        if ($Anapra_rec['PRASLAVE'] == 1 && ($MASTER = $this->praLib->GetEnteMaster())) {
            $PRAM_DB = ItaDB::DBOpen('PRAM', $MASTER);
        }

        $sql = "SELECT * FROM PRAAZIONI WHERE PRANUM = '$numeroRichiesta' AND CODICEAZIONE = '$codiceAzione'";

        if ($chiavePasso) {
            $sql .= " AND ITEKEY = '" . $chiavePasso . "'";
        }

        $Ricazioni_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);

        if (!$Ricazioni_rec) {
            return false;
        }

        $classe = $Ricazioni_rec['CLASSEAZIONE'];
        $metodo = $Ricazioni_rec['METODOAZIONE'];

        if (!$classe || !$metodo) {
            return false;
        }

        return true;
    }

    public function eseguiAzione($codiceAzione, $dati, $azione_passo = false) {
        /*
         * Clean errore status
         */
        $this->setErrCode(0);
        $this->setErrMessage('');

        $richiesta = $dati['Passo']['PROPRO'];

        if (!$codiceAzione) {
            $this->setErrMessage('Codice azione Mancante');
            return false;
        }

        $PRAM_DB = $dati['PRAM_DB'];

        /*
         * Verifico flag modello centralizzato
         */
        $Anapra_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT PRASLAVE FROM ANAPRA WHERE PRANUM = '$richiesta'", false);
        if ($Anapra_rec['PRASLAVE'] == 1 && ($MASTER = $this->praLib->GetEnteMaster())) {
            $PRAM_DB = ItaDB::DBOpen('PRAM', $MASTER);
        }

        $sql = "SELECT * FROM PRAAZIONI WHERE PRANUM = '$richiesta' AND CODICEAZIONE = '$codiceAzione'";

        if ($azione_passo) {
            $sql .= " AND ITEKEY = '" . $dati['Passo']['PROITK'] . "'";
        }

        $Ricazioni_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);

        if (!$Ricazioni_rec) {
            $this->setErrMessage('Classe e Metodo non definiti');
            return false;
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

        $customObj = $this->setCustomObjectData($customObj, $dati);
        $customRes = $customObj->$metodo();

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

    public function checkEseguiDisegnoCampo($datoAggiuntivo) {
        if (!$datoAggiuntivo['DAGCLASSE'] || !$datoAggiuntivo['DAGMETODO']) {
            return false;
        }

        return true;
    }

    public function eseguiDisegnoCampo($dati) {
        /*
         * Clean errore status
         */
        $this->setErrCode(0);
        $this->setErrMessage('');

        $DatoAggiuntivo = $dati['DatoAggiuntivo'];

        $classe = $DatoAggiuntivo['DAGCLASSE'];
        $metodo = $DatoAggiuntivo['DAGMETODO'];

        if (!$metodo) {
            $this->setErrMessage('Metodo non definito');
            return false;
        }

        $customObj = $this->istanziaCustomClass($classe);
        if (!$customObj) {
            return false;
        }

        $customObj = $this->setCustomObjectData($customObj, $dati);
        $customRes = $customObj->$metodo();

        $this->setErrCode($customObj->getErrCode());
        $this->setErrMessage($customObj->getErrMessage());

        return $customRes;
    }

    public function checkEseguiEvento($evento, $datoAggiuntivo) {
        $meta = unserialize($datoAggiuntivo['DAGMETA']);
        return (boolean) $meta['CUSTOMEVENT'][$evento];
    }

    public function eseguiEvento($evento, $dati) {
        /*
         * Clean errore status
         */
        $this->setErrCode(0);
        $this->setErrMessage('');

        $DatoAggiuntivo = $dati['DatoAggiuntivo'];

        $meta = unserialize($DatoAggiuntivo['DAGMETA']);

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

        /* @var $customObj praCustomClass */
        $customObj = $this->istanziaCustomClass($classe);
        if (!$customObj) {
            return false;
        }

        $customObj = $this->setCustomObjectData($customObj, $dati);
        $customRes = $customObj->$metodo();

        $this->setErrCode($customObj->getErrCode());
        $this->setErrMessage($customObj->getErrMessage());

        return $customRes;
    }

    private function setCustomObjectData($customObj, $dati) {
        $customObj->setPraLib($this->praLib);

        if ($dati['DatoAggiuntivo']) {
            $customObj->setDatoAggiuntivo($dati['DatoAggiuntivo']);
        }

        if ($dati['DatiAggiuntivi']) {
            $customObj->setDatiAggiuntivi($dati['DatiAggiuntivi']);
        }

        if ($dati['Passo']) {
            $customObj->setDatiPasso($dati['Passo']);
        }

        if ($dati['Dizionario']) {
            $customObj->setDizionario($dati['Dizionario']);
        }

        if ($dati['CallerForm']) {
            $customObj->setCallerForm($dati['CallerForm']);
        }

        if ($dati['PRAM_DB']) {
            $customObj->setPRAM_DB($dati['PRAM_DB']);
        }

        return $customObj;
    }

}
