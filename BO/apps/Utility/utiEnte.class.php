<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/
class utiEnte {
    /**
     * Libreria di funzioni Generiche e Utility per gestione dati Ente
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    public $ITALWEB_DB;

    public function setITALWEB_DB($ITALWEB_DB) {
        $this->ITALWEB_DB=$ITALWEB_DB;
    }

    public function getITALWEB_DB() {
        if (!$this->ITALWEB_DB) {
            try {
                $this->ITALWEB_DB=ItaDB::DBOpen('ITALWEBDB', false);
            }catch(Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALWEB_DB;
    }

    public function GetParametriEnte() {
        $ParametriEnte_rec = ItaDB::DBSQLSelect($this->getITALWEB_DB(),
                "SELECT * FROM PARAMETRIENTE WHERE CODICE='" . App::$utente->getKey('ditta') . "'", false);
        return $ParametriEnte_rec;
    }
}
?>