<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_CITYWARE.class.php';

/**
 *
 * Utility DB Cityportal. Alcuni metodi usano la connection di default in quanto sono 
 * installazioni di cwol con db di cityportal sulla connection cityware. Altri invece usano la connection
 * cityportal in quando sono installazioni cwol classiche con l'aggiunta di una connection aggiuntiva cityportal
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Luca Cardinali
 * 
 */
class cwbLibDB_Cityportal extends cwbLibDB_CITYWARE {

    private $CITYPORTAL_DB;

    const CONNECTION_CITYPORTAL_NAME = 'CITYPORTAL';

    /**
     * Restituisce comando sql per lettura tabella BPO_PARAMS
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBpoParams($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BPOPARAMS.* FROM BPOPARAMS";
        $where = 'WHERE';
        if (array_key_exists('PARAMETRO', $filtri) && $filtri['PARAMETRO'] != null) {
            $this->addSqlParam($sqlParams, "PARAMETRO", trim($filtri['PARAMETRO']), PDO::PARAM_STR);
            $sql .= " $where PARAMETRO=:PARAMETRO";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY ID';

        return $sql;
    }

    /**
     * Restituisce dati tabella BPO_PARAMS
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBpoParams($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBpoParams($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BPO_PARAMS
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBpoParamsChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['ID'] = $cod;
        return self::getSqlLeggiBpoParams($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BPO_PARAMS per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBpoParamsChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBpoParamsChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce dati tabella BPOUTENTI per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBpoUtenti($filtri, $multipla = false) {
        $sql = $this->getSqlLeggiBpoUtenti($filtri, $sqlParams);
        return ItaDB::DBQuery($this->getCityportalDb(), $sql, $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BPO_PARAMS
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBpoUtenti($filtri, &$sqlParams = array()) {
        $sql = "SELECT b.* FROM Bpoutenti b ";
        $where = 'WHERE ';
        if (array_key_exists('ID', $filtri) && $filtri['ID'] != null) {
            $this->addSqlParam($sqlParams, "ID", $filtri['ID'], PDO::PARAM_INT);
            $sql .= $where . " ID=:ID";
            $where = ' AND ';
        }
        if (array_key_exists('PROGSOGG', $filtri) && $filtri['PROGSOGG'] != null) {
            $this->addSqlParam($sqlParams, "PROGSOGG", $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= $where . " PROGSOGG=:PROGSOGG";
            $where = ' AND ';
        }
        if (array_key_exists('IDUTEWEB', $filtri) && $filtri['IDUTEWEB'] != null) {
            $this->addSqlParam($sqlParams, "IDUTEWEB", trim($filtri['IDUTEWEB']), PDO::PARAM_STR);
            $sql .= $where . " IDUTEWEB=:IDUTEWEB";
            $where = ' AND ';
        }

        //$sql .= $excludeOrderBy ? '' : ' ORDER BY ID';

        return $sql;
    }

    /**
     * Restituisce le autorizzazioni per utente
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiAutorizzazionexUtente($filtri) {
        $sql = strtoupper($this->getSqlleggiAutorizzazionexUtente($filtri, $sqlParams));
        return ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura delle autorizzazioni per utente
     * @param array $filtri Filtri di ricerca
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlleggiAutorizzazionexUtente($filtri, &$sqlParams = array()) {
        $sql = "select u.id, u.progsogg, u.iduteweb, u.tipoutente, u.proven"
                . ", g.alias, f.funzione "
                . " ,a.idfunzione, a.autute, a.valore  "
                . " from bpoutenti u  ";
//        $sql .= "left join bposogg s on u.progsogg=s.progsogg ";
        $sql .= "left join bpoautute a on a.idutente=u.id ";
        $sql .= "left join bpofunzio f on f.id=a.idfunzione ";
        $sql .= "left join bpoautor t on t.id=f.idautoriz ";
        $sql .= "left join bpogruaut g on g.id=t.idgruppo ";
//        $sql .= "left join dpoanagra d on d.progsogg=s.progsogg ";
        $sql .= 'WHERE 1=1 ';
        if (array_key_exists('ID', $filtri) && $filtri['ID'] != null) {
            $this->addSqlParam($sqlParams, "ID", strtolower(trim($filtri['ID'])), PDO::PARAM_INT);
            $sql .= " and  u.ID=:ID";
        }
//        if (array_key_exists('PROGSOGG', $filtri) && $filtri['PROGSOGG'] != null) {
//            $this->addSqlParam($sqlParams, "PROGSOGG", strtolower(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
//            $sql .= " and  s.PROGSOGG=:PROGSOGG";
//        }
//        if (array_key_exists('CODFISCALE', $filtri) && $filtri['CODFISCALE'] != null) {
//            $this->addSqlParam($sqlParams, "CODFISCALE", trim($filtri['CODFISCALE']), PDO::PARAM_STR);
//            $sql .= " and  s.CODFISCALE=:CODFISCALE";
//        }
        if (array_key_exists('IDUTEWEB', $filtri) && $filtri['IDUTEWEB'] != null) {
            $this->addSqlParam($sqlParams, "IDUTEWEB", trim($filtri['IDUTEWEB']), PDO::PARAM_STR);
            $sql .= " and  u.IDUTEWEB=:IDUTEWEB";
        }

        return $sql;
    }

    /**
     * Restituisce comando sql per lettura tabella APOATTI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiApoAtti($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT APOATTI.* FROM APOATTI";
        $where = 'WHERE';

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGATTO DESC';

        return $sql;
    }

    /**
     * Restituisce dati tabella APOATTI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiApoAtti($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCityportalDb(), $this->getSqlLeggiApoAtti($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella APOATTIIT
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiApoAttiit($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT APOATTIIT.* FROM APOATTIIT";
        $where = ' WHERE ';
        if (array_key_exists('PROGATTO', $filtri) && $filtri['PROGATTO'] != null) {
            $this->addSqlParam($sqlParams, "PROGATTO", trim($filtri['PROGATTO']), PDO::PARAM_INT);
            $sql .= $where . " PROGATTO=:PROGATTO";
            $where = ' AND ';
        }
        if (array_key_exists('RIGAITER', $filtri) && $filtri['RIGAITER'] != null) {
            $this->addSqlParam($sqlParams, "RIGAITER", trim($filtri['RIGAITER']), PDO::PARAM_INT);
            $sql .= $where . " RIGAITER=:RIGAITER";
            $where = ' AND ';
        }
        if (array_key_exists('PROGRIGA', $filtri) && $filtri['PROGRIGA'] != null) {
            $this->addSqlParam($sqlParams, "PROGRIGA", trim($filtri['PROGRIGA']), PDO::PARAM_INT);
            $sql .= $where . " PROGRIGA=:PROGRIGA";
            $where = ' AND ';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGATTO DESC';

        return $sql;
    }

    /**
     * Restituisce dati tabella APOATTIIT
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiApoAttiit($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCityportalDb(), $this->getSqlLeggiApoAttiit($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella APOATTITE
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiApoAttite($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT APOATTITE.* FROM APOATTITE";
        $where = ' WHERE ';
        if (array_key_exists('PROGATTO', $filtri) && $filtri['PROGATTO'] != null) {
            $this->addSqlParam($sqlParams, "PROGATTO", trim($filtri['PROGATTO']), PDO::PARAM_INT);
            $sql .= $where . " PROGATTO=:PROGATTO";
            $where = ' AND ';
        }
        if (array_key_exists('RIGATESTO', $filtri) && $filtri['RIGATESTO'] != null) {
            $this->addSqlParam($sqlParams, "RIGATESTO", trim($filtri['RIGATESTO']), PDO::PARAM_INT);
            $sql .= $where . " RIGATESTO=:RIGATESTO";
            $where = ' AND ';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGATTO DESC';

        return $sql;
    }

    /**
     * Restituisce dati tabella APOATTITE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiApoAttite($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCityportalDb(), $this->getSqlLeggiApoAttite($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella APOALLEG
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiApoAlleg($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT APOALLEG.* FROM APOALLEG";
        $where = ' WHERE ';
        if (array_key_exists('PROGATTO', $filtri) && $filtri['PROGATTO'] != null) {
            $this->addSqlParam($sqlParams, "PROGATTO", trim($filtri['PROGATTO']), PDO::PARAM_INT);
            $sql .= $where . " PROGATTO=:PROGATTO";
            $where = ' AND ';
        }
        if (array_key_exists('RIGATESTO', $filtri) && $filtri['RIGATESTO'] != null) {
            $this->addSqlParam($sqlParams, "RIGATESTO", trim($filtri['RIGATESTO']), PDO::PARAM_INT);
            $sql .= $where . " RIGATESTO=:RIGATESTO";
            $where = ' AND ';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGATTO DESC';

        return $sql;
    }

    /**
     * Restituisce dati tabella APOALLEG
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiApoAlleg($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCityportalDb(), $this->getSqlLeggiApoAlleg($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlDeleteDatiUte($filtri, &$sqlParams = array()) {
        if (!$filtri['ID']) {
            return;
        }
        $sql = "delete from Bpodatiute where ID=:ID ";

        if (array_key_exists('ID', $filtri) && $filtri['ID'] != null) {
            $this->addSqlParam($sqlParams, "ID", trim($filtri['ID']), PDO::PARAM_INT);
        }

        return $sql;
    }

    public function deleteDatiUte($filtri) {
        return ItaDB::DBQuery($this->getCityportalDb(), $this->getSqlDeleteDatiUte($filtri, $sqlParams), FALSE, $sqlParams);
    }

    public function getSqlDeleteDatiUteAllinea($filtri, &$sqlParams = array()) {
        $sql = "delete  from bpodatiute where bpodatiute.id in 
            (select bpodatiute.id from bpodatiute join bpoutenti on bpoutenti.id=bpodatiute.id
            where ((bpoutenti.progmod > bpodatiute.progmod) 
            or (bpoutenti.progmod > 0 and bpodatiute.progmod is null))) and stato=0 AND ID=:ID ";

        if (array_key_exists('ID', $filtri) && $filtri['ID'] != null) {
            $this->addSqlParam($sqlParams, "ID", trim($filtri['ID']), PDO::PARAM_INT);
        }

        return $sql;
    }

    public function deleteDatiUteAllinea($filtri) {
        return ItaDB::DBQuery($this->getCityportalDb(), $this->getSqlDeleteDatiUteAllinea($filtri, $sqlParams), FALSE, $sqlParams);
    }

    public function getSqlDeleteAututeAllinea($filtri, &$sqlParams = array()) {
        if (!$filtri['IDUTENTE']) {
            return;
        }
        $sql = "delete from Bpoautute where IDUTENTE = :IDUTENTE ";

        if (array_key_exists('IDUTENTE', $filtri) && $filtri['IDUTENTE'] != null) {
            $this->addSqlParam($sqlParams, "IDUTENTE", $filtri['IDUTENTE'], PDO::PARAM_INT);
        }

        return $sql;
    }

    public function deleteAututeAllinea($filtri) {
        return ItaDB::DBQuery($this->getCityportalDb(), $this->getSqlDeleteAututeAllinea($filtri, $sqlParams), FALSE, $sqlParams);
    }

    public function getSqlDeleteBpoUtenteAllinea($filtri, &$sqlParams = array()) {
        if (!$filtri['ID']) {
            return;
        }
        $sql = "delete from Bpoutenti where ID = :ID ";

        if (array_key_exists('ID', $filtri) && $filtri['ID'] != null) {
            $this->addSqlParam($sqlParams, "ID", $filtri['ID'], PDO::PARAM_INT);
        }

        return $sql;
    }

    public function deleteBpoUtenteAllinea($filtri) {
        return ItaDB::DBQuery($this->getCityportalDb(), $this->getSqlDeleteBpoUtenteAllinea($filtri, $sqlParams), FALSE, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BPO_PARAMS
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBpoParam($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BPOPARAM.* FROM BPOPARAM";

        $sql .= $excludeOrderBy ? '' : ' ORDER BY ID';

        return $sql;
    }

    /**
     * Restituisce dati tabella BPO_PARAMS
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBpoParam($filtri, $multipla = false) {
        return ItaDB::DBQuery($this->getCityportalDb(), $this->getSqlLeggiBpoParam($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // questa lib è usata sia per le installazioni di cwol con connection cityportal su tag cityware, 
    // sia per installazioni classiche di cwol che hanno db cityware sotto il tag cityware e db cityportal sotto
    // il tag cityportal
    public function getCityportalDb() {
        if (!$this->CITYPORTAL_DB) {
            try {
                $this->CITYPORTAL_DB = ItaDB::DBOpen(self::CONNECTION_CITYPORTAL_NAME, '');
            } catch (Exception $e) {
                $this->setErrCode($e->getCode());
                $this->setErrMsg($e->getMessage());
            }
        }
        return $this->CITYPORTAL_DB;
    }

}

?>