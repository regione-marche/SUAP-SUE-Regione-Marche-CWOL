<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of envLibAmministrazioenTrasparente
 *
 * @author s.bianchini
 */
class envLibAmministrazioneTrasparente {

    public $ITALWEB_DB;

    public function setITALWEB_DB($ITALWEB_DB) {
        $this->ITALWEB_DB = $ITALWEB_DB;
    }

    public function getITALWEB_DB() {
        if (!$this->ITALWEB_DB) {
            try {
                $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALWEB_DB;
    }

    public function inGroup($id) {
        $sql = "SELECT COUNT(*) AS CONTO FROM FNG_MEMBRI WHERE TRASHED = 0 AND IDGRUPPO=" . $id . " AND USERNAME = '" . App::$utente->getKey('nomeUtente') . "'";
        $tab = ItaDB::DBSQLSelect($this->getITALWEB_DB(), $sql, false);
        return $tab['CONTO'];
    }

}
