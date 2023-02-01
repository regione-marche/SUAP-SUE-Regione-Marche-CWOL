<?php
//@TODO: DA IMPLEMENTARE DOPO AVER PREPARATO INGRESSO PER SERVIZIO REST
class praCustomExplorerREST {

    private $PRAM_DB;
    private $errMessage;
    private $errCode;

    const TYPE_FILE = 'F';
    const TYPE_DIRECTORY = 'D';
    const ERR_FILE_EXISTS = 10;

    public function getPRAM_DB() {
        if (!$this->PRAM_DB) {
            try {
                $this->PRAM_DB = ItaDB::DBOpen('PRAM');
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage($e->getMessage());
            }
        }

        return $this->PRAM_DB;
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

    private function getCustomClassPath() {
        $sql = "SELECT * FROM ANAPAR WHERE PARKEY = 'CUSTOMCLASS_LOCAL_PATH'";
        $anapar_rec = ItaDB::DBSQLSelect($this->getPRAM_DB(), $sql, false);

        if (!$anapar_rec['PARVAL']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Parametro 'CUSTOMCLASS_LOCAL_PATH' non configurato");
            return false;
        }

        return $anapar_rec['PARVAL'];
    }

    public function getDirectory($path = '/') {
        return false;
    }

    public function getFile($path) {
        return false;
    }

    public function updateFile($path, $base64) {
        return false;
    }

    public function deleteElement($path) {
        return false;
    }

    public function insertFile($path, $base64 = false) {
        return false;
    }

    public function insertDirectory($path) {
        return false;
    }

}
