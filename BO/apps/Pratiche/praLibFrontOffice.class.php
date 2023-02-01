<?php

/**
 * Description of praLibFrontoffice
 *
 * @author michele
 */
class praLibFrontOffice {
   
    private $praLib;
    
    function __construct() {
        $this->praLib = new praLib();
        /**
         * Apro il DB
         */
        try {
            $this->ITAFRONTOFFICE_DB = $this->praLib->getITAFODB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    public function getEnv_config($codice,$tipo = 'codice', $chiave = '', $multi = true) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ENV_CONFIG WHERE CLASSE='$codice'";
            if ($chiave != '') {
                $sql .= " AND CHIAVE='$chiave'";
            }
        } else {
            $sql = "SELECT * FROM ENV_CONFIG WHERE ROWID=$codice";
            $multi = false;
        }
        return ItaDB::DBSQLSelect($this->ITAFRONTOFFICE_DB, $sql, $multi);
    }

}
