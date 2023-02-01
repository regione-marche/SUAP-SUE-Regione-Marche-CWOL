<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_CITYWARE.class.php';

/**
 *
 * Utility DB Cityware (Modulo BTA)
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Massimo Biagioli <m.biagioli@palinformatica.it>
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
class cwbLibDB_BTA extends cwbLibDB_CITYWARE {

// BTA_GRUNAZ
    protected $SortFieldLocal;
    protected $TypeFieldLocal;

    /**
     * Restituisce comando sql per lettura tabella BTA_GRUNAZ
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaGrunaz($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_GRUNAZ.* FROM BTA_GRUNAZ";
        $where = 'WHERE';
        if (array_key_exists('CODGRNAZ', $filtri) && $filtri['CODGRNAZ'] != null) {
            $this->addSqlParam($sqlParams, "CODGRNAZ", strtoupper(trim($filtri['CODGRNAZ'])), PDO::PARAM_STR);
            $sql .= " $where CODGRNAZ=:CODGRNAZ";
            $where = 'AND';
        }
        if (array_key_exists('DESGRNAZ', $filtri) && $filtri['DESGRNAZ'] != null) {
            $this->addSqlParam($sqlParams, "DESGRNAZ", "%" . strtoupper(trim($filtri['DESGRNAZ'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESGRNAZ") . " LIKE :DESGRNAZ";
            $where = 'AND';
        }
        if (array_key_exists('CONTINENTE', $filtri) && $filtri['CONTINENTE'] != null) {
            $this->addSqlParam($sqlParams, "CONTINENTE", $filtri['CONTINENTE'], PDO::PARAM_STR);
            $sql .= " $where CONTINENTE=:CONTINENTE ";
            $where = 'AND';
        }
        if (array_key_exists('CEENAZ', $filtri) && $filtri['CEENAZ'] != null) {
            $this->addSqlParam($sqlParams, "CEENAZ", strtoupper(trim($filtri['CEENAZ'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CEENAZ") . " LIKE :CEENAZ";
            $where = 'AND';
        }

        if (array_key_exists('DATAOPER', $filtri) && $filtri['DATAOPER'] != null) {
            $this->addSqlParam($sqlParams, "DATAOPER", $filtri['DATAOPER'], PDO::PARAM_STR);
            $sql .= " $where DATAOPER<:DATAOPER";
            $where = 'AND';
        }


        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODGRNAZ';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_GRUNAZ
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaGrunaz($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaGrunaz($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_GRUNAZ
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaGrunazChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['CODGRNAZ'] = $cod;
        return self::getSqlLeggiBtaGrunaz($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_GRUNAZ per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaGrunazChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaGrunazChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_NAZION

    /**
     * Restituisce comando sql per lettura tabella BTA_NAZION
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNazion($filtri, $excludeOrderBy = false, &$sqlParams = array(), $decodGrunaz = false) {
        $sql = "SELECT BTA_NAZION.* ";
        if ($decodGrunaz == true) {
            $sql = $sql . ", BTA_GRUNAZ.DESGRNAZ ";
        }
        $sql = $sql . " FROM BTA_NAZION BTA_NAZION ";

        if ($decodGrunaz == true) {
            $sql = $sql . " LEFT JOIN BTA_GRUNAZ BTA_GRUNAZ ON BTA_NAZION.CODGRNAZ = BTA_GRUNAZ.CODGRNAZ ";
        }
        $where = 'WHERE';
        if (array_key_exists('CODNAZI', $filtri) && $filtri['CODNAZI'] != null) {
            $this->addSqlParam($sqlParams, "CODNAZI", $filtri['CODNAZI'], PDO::PARAM_INT);
            $sql .= " $where CODNAZI=:CODNAZI";
            $where = 'AND';
        }
        if (array_key_exists('CODNA_IST', $filtri) && $filtri['CODNA_IST'] != null) {
            $this->addSqlParam($sqlParams, "CODNA_IST", $filtri['CODNA_IST'], PDO::PARAM_INT);
            $sql .= " $where CODNA_IST=:CODNA_IST";
            $where = 'AND';
        }
        if (array_key_exists('DESNAZI', $filtri) && $filtri['DESNAZI'] != null) {
            $this->addSqlParam($sqlParams, "DESNAZI", "%" . strtoupper(trim($filtri['DESNAZI'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESNAZI") . " LIKE :DESNAZI";
            $where = 'AND';
        }
        if (array_key_exists('DESNAZION', $filtri) && $filtri['DESNAZION'] != null) {
            $this->addSqlParam($sqlParams, "DESNAZION", "%" . strtoupper(trim($filtri['DESNAZION'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESNAZION") . " LIKE :DESNAZION";
            $where = 'AND';
        }
        if (array_key_exists('SIGLANAZ', $filtri) && $filtri['SIGLANAZ'] != null) {
            $this->addSqlParam($sqlParams, "SIGLANAZ", "%" . strtoupper(trim($filtri['SIGLANAZ'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("SIGLANAZ") . " LIKE :SIGLANAZ";
            $where = 'AND';
        }
        if (array_key_exists('ISO3166_A2', $filtri) && $filtri['ISO3166_A2'] != null) {
            $this->addSqlParam($sqlParams, "ISO3166_A2", "%" . strtoupper(trim($filtri['ISO3166_A2'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("ISO3166_A2") . " LIKE :ISO3166_A2";
            $where = 'AND';
        }
        if (array_key_exists('CODNAZIMC', $filtri) && $filtri['CODNAZIMC'] != null) {
            $this->addSqlParam($sqlParams, "CODNAZIMC", "%" . strtoupper(trim($filtri['CODNAZIMC'])) . "%", PDO::PARAM_STR);
            $sql .= " $where CODNAZIMC=:CODNAZIMC";
            $where = 'AND';
        }
        if (array_key_exists('CODNAZICO', $filtri) && $filtri['CODNAZICO'] != null) {
            $this->addSqlParam($sqlParams, "CODNAZICO", "%" . strtoupper(trim($filtri['CODNAZICO'])) . "%", PDO::PARAM_STR);
            $sql .= " $where CODNAZICO=:CODNAZICO";
            $where = 'AND';
        }
        if (array_key_exists('CODGOVE', $filtri) && $filtri['CODGOVE'] != null) {
            $this->addSqlParam($sqlParams, "CODGOVE", "%" . strtoupper(trim($filtri['CODGOVE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where CODGOVE=:CODGOVE";
            $where = 'AND';
        }
        if (array_key_exists('DATAINIZ', $filtri) && $filtri['DATAINIZ'] != null) {
            $this->addSqlParam($sqlParams, "DATAINIZ", $filtri['DATAINIZ'], PDO::PARAM_STR);
            $sql .= " $where DATAINIZ=:DATAINIZ";
            $where = 'AND';
        }
        if (array_key_exists('DATAFINE', $filtri) && $filtri['DATAFINE'] != null) {
            $this->addSqlParam($sqlParams, "DATAFINE", $filtri['DATAFINE'], PDO::PARAM_STR);
            $sql .= " $where DATAFINE=:DATAFINE";
            $where = 'AND';
        }
        if (array_key_exists('CODGRNAZ', $filtri) && $filtri['CODGRNAZ'] != null) {
            $this->addSqlParam($sqlParams, "CODGRNAZ", strtoupper(trim($filtri['CODGRNAZ'])), PDO::PARAM_STR);
            $sql .= " $where BTA_NAZION.CODGRNAZ=:CODGRNAZ";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY BTA_NAZION.CODNAZI';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_NAZION
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaNazion($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNazion($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_NAZION
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNazionChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['CODNAZI'] = $cod;
        return self::getSqlLeggiBtaNazion($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_NAZION per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaNazionChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNazionChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_NAZION per chiave 
     * in join con bta_grnaz
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaNazionGrunazChiave($cod) {
        if (!$cod) {
            return null;
        }

        $filtri = array();
        $filtri['CODNAZI'] = $cod;
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNazion($filtri, false, $sqlParams, true), false, $sqlParams);
    }

// BTA_RGRUNA

    /**
     * Restituisce comando sql per lettura tabella BTA_RGRUNA
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRgruna($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_RGRUNA.* FROM BTA_RGRUNA";
        $where = 'WHERE';
        if (array_key_exists('PK', $filtri) && $filtri['PK'] != null) {
            $this->addSqlParam($sqlParams, "PK", $filtri['PK'], PDO::PARAM_INT);
            $sql .= " $where PK=:PK";
            $where = 'AND';
        }
        if (array_key_exists('DES_RAGGR', $filtri) && $filtri['DES_RAGGR'] != null) {
            $this->addSqlParam($sqlParams, "DES_RAGGR", strtoupper(trim($filtri['DES_RAGGR'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DES_RAGGR") . " LIKE :DES_RAGGR";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PK';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_RGRUNA
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRgruna($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRgruna($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per progressivo successivo tabella BTA_RGRUNA
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRgrunaMax($filtri, &$sqlParams = array()) {
        $sql = "SELECT MAX(PK) AS MAX FROM BTA_RGRUNA";
        return $sql;
    }

    /**
     * Restituisce progressivo successivo BTA_RGRUNA
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRgrunaMax($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRgrunaMax($filtri, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_RGRUNA
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRgrunaChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PK'] = $cod;
        return self::getSqlLeggiBtaRgruna($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_RGRUNA per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaRgrunaChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRgrunaChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_NAZGRU

    /**
     * Restituisce comando sql per lettura tabella BTA_NAZGRU
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNazgru($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT U.*, R.DES_RAGGR, Z.DESGRNAZ, C.DESNAZI, "
                . $this->getCitywareDB()->strConcat('U.PK', "'|'", 'U.CODGRNAZ', "'|'", 'U.CODNAZI') . " AS \"ROW_ID\" " // CHIAVE COMPOSTA
                . " FROM BTA_NAZGRU U "
                . "LEFT JOIN BTA_RGRUNA R on R.PK=U.PK "
                . "LEFT JOIN BTA_GRUNAZ Z on U.CODGRNAZ=Z.CODGRNAZ "
                . "LEFT JOIN BTA_NAZION C on U.CODNAZI=C.CODNAZI";
        $where = 'WHERE';

        if (array_key_exists('PK', $filtri) && $filtri['PK'] != null) {
            $this->addSqlParam($sqlParams, "PK", $filtri['PK'], PDO::PARAM_INT);
            $sql .= " $where U.PK=:PK";
            $where = 'AND';
        }
        if (array_key_exists('CODGRNAZ', $filtri) && $filtri['CODGRNAZ'] != null) {
            $this->addSqlParam($sqlParams, "CODGRNAZ", $filtri['CODGRNAZ'], PDO::PARAM_STR);
            $sql .= " $where U.CODGRNAZ=:CODGRNAZ";
            $where = 'AND';
        }
        if (array_key_exists('CODNAZI', $filtri) && $filtri['CODNAZI'] != null) {
            $this->addSqlParam($sqlParams, "CODNAZI", $filtri['CODNAZI'], PDO::PARAM_INT);
            $sql .= " $where U.CODNAZI=:CODNAZI";
            $where = 'AND';
        }
        if (array_key_exists('DESGRNAZ', $filtri) && $filtri['DESGRNAZ'] != null) {
            $this->addSqlParam($sqlParams, "DESGRNAZ", "%" . strtoupper(trim($filtri['DESGRNAZ'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESGRNAZ") . " LIKE :DESGRNAZ";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY U.PK';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_NAZGRU
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaNazgru($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNazgru($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per progressivo successivo tabella BTA_NAZGRU
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNazgruMax($filtri, &$sqlParams = array()) {
        $sql = "SELECT MAX(PK) AS MAX FROM BTA_NAZGRU";
        return $sql;
    }

    /**
     * Restituisce progressivo successivo BTA_NAZGRU
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaNazgruMax($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNazgruMax($filtri, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_NAZGRU
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNazgruChiave($pk, $codgrnaz, $codnazi, &$sqlParams) {
        $filtri = array();
        $filtri['PK'] = $pk;
        $filtri['CODGRNAZ'] = $codgrnaz;
        $filtri['CODNAZI'] = $codnazi;
        return self::getSqlLeggiBtaNazgru($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_NAZGRU per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaNazgruChiave($pk, $codgrnaz, $codnazi) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNazgruChiave($pk, $codgrnaz, $codnazi), false, $sqlParams);
    }

// BTA_CONSOL

    /**
     * Restituisce comando sql per lettura tabella BTA_CONSOL
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaConsol($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_CONSOL_V01.* FROM BTA_CONSOL_V01";
        $where = 'WHERE';
        if (array_key_exists('CODCONSOL', $filtri) && $filtri['CODCONSOL'] != null) {
            $this->addSqlParam($sqlParams, "CODCONSOL", $filtri['CODCONSOL'], PDO::PARAM_INT);
            $sql .= " $where CODCONSOL=:CODCONSOL";
            $where = 'AND';
        }
        if (array_key_exists('DESCONSOL', $filtri) && $filtri['DESCONSOL'] != null) {
            $this->addSqlParam($sqlParams, "DESCONSOL", "%" . strtoupper(trim($filtri['DESCONSOL'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESCONSOL") . " LIKE :DESCONSOL";
            $where = 'AND';
        }
        if (array_key_exists('DESLOCAL', $filtri) && $filtri['DESLOCAL'] != null) {
            $this->addSqlParam($sqlParams, "DESLOCAL", "%" . strtoupper(trim($filtri['DESLOCAL'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESLOCAL") . " LIKE :DESLOCAL";
            $where = 'AND';
        }
        if (array_key_exists('DESNAZI', $filtri) && $filtri['DESNAZI'] != null) {
            $this->addSqlParam($sqlParams, "DESNAZI", "%" . strtoupper(trim($filtri['DESNAZI'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESNAZI") . " LIKE :DESNAZI";
            $where = 'AND';
        }
        if (array_key_exists('INDIRCON1', $filtri) && $filtri['INDIRCON1'] != null) {
            $this->addSqlParam($sqlParams, "INDIRCON1", "%" . strtoupper(trim($filtri['INDIRCON1'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("INDIRCON1") . " LIKE :INDIRCON1";
            $where = 'AND';
        }
        if (array_key_exists('DATAINIZ', $filtri) && $filtri['DATAINIZ'] != null) {
            $this->addSqlParam($sqlParams, "DATAINIZ", $filtri['DATAINIZ'], PDO::PARAM_STR);
            $sql .= " $where DATAINIZ=:DATAINIZ";
            $where = 'AND';
        }
        if (array_key_exists('DATAFINE', $filtri) && $filtri['DATAFINE'] != null) {
            $this->addSqlParam($sqlParams, "DATAFINE", $filtri['DATAFINE'], PDO::PARAM_STR);
            $sql .= " $where DATAFINE=:DATAFINE";
            $where = 'AND';
        }
        if (!empty($filtri['CODUTE'])) {
            $this->addSqlParam($sqlParams, 'CODUTE', '%' . strtoupper(trim($filtri['CODUTE'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CODUTE") . " LIKE :CODUTE";
            $where = 'AND';
        }
        if (isSet($filtri['FLAG_DIS'])) {
            $this->addSqlParam($sqlParams, 'FLAG_DIS', $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS = :FLAG_DIS";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODCONSOL';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_CONSOL
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaConsol($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaConsol($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_CONSOL
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaConsolChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['CODCONSOL'] = $cod;
        return self::getSqlLeggiBtaConsol($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_CONSOL per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaConsolChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaConsolChiave($cod, $sqlParams), false, $sqlParams);
    }

//BTA_RAPPR

    /**
     * Restituisce comando sql per lettura tabella BTA_RAPPR
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRappr($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT r.*, naz.DESNAZI"
                . " FROM BTA_RAPPR r "
                . " LEFT OUTER JOIN BTA_NAZION naz ON r.CODNAZI=naz.CODNAZI";

        $where = 'WHERE';
        if (array_key_exists('CODCONSOL', $filtri) && $filtri['CODCONSOL'] != null) {
            $this->addSqlParam($sqlParams, "CODCONSOL", $filtri['CODCONSOL'], PDO::PARAM_INT);
            $sql .= " $where CODCONSOL=:CODCONSOL";
            $where = 'AND';
        }
        if (array_key_exists('DESCONSOL', $filtri) && $filtri['DESCONSOL'] != null) {
            $this->addSqlParam($sqlParams, "DESCONSOL", "%" . strtoupper(trim($filtri['DESCONSOL'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESCONSOL") . " LIKE :DESCONSOL";
            $where = 'AND';
        }
        if (array_key_exists('DESLOCAL', $filtri) && $filtri['DESLOCAL'] != null) {
            $this->addSqlParam($sqlParams, "DESLOCAL", "%" . strtoupper(trim($filtri['DESLOCAL'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESLOCAL") . " LIKE :DESLOCAL";
            $where = 'AND';
        }
        if (array_key_exists('DESNAZI', $filtri) && $filtri['DESNAZI'] != null) {
            $this->addSqlParam($sqlParams, "DESNAZI", "%" . strtoupper(trim($filtri['DESNAZI'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESNAZI") . " LIKE :DESNAZI";
            $where = 'AND';
        }


        if (array_key_exists('INDIRCON1', $filtri) && $filtri['INDIRCON1'] != null) {
            $this->addSqlParam($sqlParams, "INDIRCON1", "%" . strtoupper(trim($filtri['INDIRCON1'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("INDIRCON1") . " LIKE :INDIRCON1";
            $where = 'AND';
        }
        if (array_key_exists('DATAINIZ', $filtri) && $filtri['DATAINIZ'] != null) {
            $this->addSqlParam($sqlParams, "DATAINIZ", $filtri['DATAINIZ'], PDO::PARAM_STR);
            $sql .= " $where DATAINIZ=:DATAINIZ";
            $where = 'AND';
        }
        if (array_key_exists('DATAFINE', $filtri) && $filtri['DATAFINE'] != null) {
            $this->addSqlParam($sqlParams, "DATAFINE", $filtri['DATAFINE'], PDO::PARAM_STR);
            $sql .= " $where DATAFINE=:DATAFINE";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY r.CODCONSOL';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_RAPPR
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRappr($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRappr($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_RAPPR
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRapprChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['CODCONSOL'] = $cod;
        return self::getSqlLeggiBtaRappr($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_RAPPR per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaRapprChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRapprChiave($cod, $sqlParams), false, $sqlParams);
    }

//BTA_TRIBU

    /**
     * Restituisce comando sql per lettura tabella BTA_TRIBU
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaTribu($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_TRIBU.* FROM BTA_TRIBU";
        $where = 'WHERE';
        if (array_key_exists('CODTRIBUN', $filtri) && $filtri['CODTRIBUN'] != null) {
            $this->addSqlParam($sqlParams, "CODTRIBUN", $filtri['CODTRIBUN'], PDO::PARAM_INT);
            $sql .= " $where CODTRIBUN=:CODTRIBUN";
            $where = 'AND';
        }
        if (array_key_exists('CAP', $filtri) && $filtri['CAP'] != null) {
            $this->addSqlParam($sqlParams, "CAP", $filtri['CAP'], PDO::PARAM_STR);
            $sql .= " $where CAP=:CAP";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] != null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }
        if (array_key_exists('INDITRIBU', $filtri) && $filtri['INDITRIBU'] != null) {
            $this->addSqlParam($sqlParams, "INDITRIBU", "%" . strtoupper(trim($filtri['INDITRIBU'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("INDITRIBU") . " LIKE :INDITRIBU";
            $where = 'AND';
        }
        if (array_key_exists('PROVINCIA', $filtri) && $filtri['PROVINCIA'] != null) {
            $this->addSqlParam($sqlParams, "PROVINCIA", "%" . strtoupper(trim($filtri['PROVINCIA'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("PROVINCIA") . " LIKE :PROVINCIA";
            $where = 'AND';
        }
        if (array_key_exists('DESTRIBU', $filtri) && $filtri['DESTRIBU'] != null) {
            $this->addSqlParam($sqlParams, "DESTRIBU", "%" . strtoupper(trim($filtri['DESTRIBU'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESTRIBU") . " LIKE :DESTRIBU";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODTRIBUN';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_TRIBU
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaTribu($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaTribu($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_TRIBU
     * @param string $cod Chiave
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaTribuChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['CODTRIBUN'] = $cod;
        return self::getSqlLeggiBtaTribu($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_TRIBU per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaTribuChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaTribuChiave($cod, $sqlParams), false, $sqlParams);
    }

//BTA_LOCAL

    /**
     * Restituisce comando sql per lettura tabella BTA_LOCAL
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaLocal($filtri, $excludeOrderBy = false, &$sqlParams = array(), $validAnpr = false, &$SortOrderField = '', &$TypeOrderField = '') {
        $rowCount = $this->leggiValidLocalAnpr();
        if ($rowCount['CONTA'] > 5000) {
            $validAnpr = true;
        }
        $sql = "SELECT BTA_LOCAL.*, BTA_NAZION.DESNAZI, "
                . $this->getCitywareDB()->strConcat('BTA_LOCAL.CODNAZPRO', "'|'", 'BTA_LOCAL.CODLOCAL') . " AS \"ROW_ID\" ";
        if ($validAnpr == true) {
            $sql = $sql . ",BTA_LOCAL_ANPR.ID_COMUANPR, BTA_LOCAL_ANPR.DATAVERIF ";
        }
        if ($filtri['flagComuniSubentratiAnpr'] == 1) {
            $sql = $sql . ",case when (SUBENTRATI.IDANPRSUBENTRATI>0) then 1 else 0 end as f_subentrato, SUBENTRATI.datasubentro ";
        }
        $sql = $sql .= " FROM BTA_LOCAL BTA_LOCAL ";
        if ($validAnpr == true) {
            $sql = $sql . " left join BTA_LOCAL_ANPR BTA_LOCAL_ANPR on BTA_LOCAL.codnazpro=BTA_LOCAL_ANPR.codnazpro and BTA_LOCAL.codlocal = BTA_LOCAL_ANPR.codlocal ";
        }
        if ($filtri['flagComuniSubentratiAnpr'] == true) {
            $sql = $sql . " left join BTA_ANPR_SUBENTRATI SUBENTRATI on SUBENTRATI.istnazpro = BTA_LOCAL.istnazpro and SUBENTRATI.istlocal=BTA_LOCAL.istlocal ";
        }
        $sql = $sql . " LEFT JOIN BTA_NAZION BTA_NAZION ON BTA_LOCAL.ISTNAZPRO=BTA_NAZION.CODNA_IST ";
        $where = 'WHERE';
        if (array_key_exists('CODNAZPRO', $filtri) && $filtri['CODNAZPRO'] != null) {
            $this->addSqlParam($sqlParams, "CODNAZPRO", $filtri['CODNAZPRO'], PDO::PARAM_INT);
            $sql .= " $where BTA_LOCAL.CODNAZPRO=:CODNAZPRO";
            $where = 'AND';
        }
        if (array_key_exists('ISTNAZPRO', $filtri) && $filtri['ISTNAZPRO'] != null) {
            $this->addSqlParam($sqlParams, "ISTNAZPRO", $filtri['ISTNAZPRO'], PDO::PARAM_INT);
            $sql .= " $where BTA_LOCAL.ISTNAZPRO=:ISTNAZPRO";
            $where = 'AND';
        }
        if (array_key_exists('CODNAZPRO_da', $filtri) && $filtri['CODNAZPRO_da'] != null) {
            $this->addSqlParam($sqlParams, "CODNAZPRO_DA", $filtri['CODNAZPRO_da'], PDO::PARAM_INT);
            $this->addSqlParam($sqlParams, "CODNAZPRO_A", $filtri['CODNAZPRO_a'], PDO::PARAM_INT);
            $sql .= " $where (BTA_LOCAL.CODNAZPRO BETWEEN :CODNAZPRO_DA AND :CODNAZPRO_A )";
            $where = 'AND';
        }
        if (array_key_exists('DESLOCAL', $filtri) && $filtri['DESLOCAL'] != null) {
            $this->addSqlParam($sqlParams, "DESLOCAL", strtoupper(trim($filtri['DESLOCAL'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_LOCAL.DESLOCAL") . " LIKE :DESLOCAL";
            $where = 'AND';
        }
        if (array_key_exists('CODLOCAL', $filtri) && intval($filtri['CODLOCAL']) > -1 && is_numeric($filtri['CODLOCAL'])) {
            $this->addSqlParam($sqlParams, "CODLOCAL", $filtri['CODLOCAL'], PDO::PARAM_INT);
            $sql .= " $where BTA_LOCAL.CODLOCAL=:CODLOCAL";
            $where = 'AND';
        }
        if (array_key_exists('PROVINCIA', $filtri) && $filtri['PROVINCIA'] != null) {
            $this->addSqlParam($sqlParams, "PROVINCIA", "%" . strtoupper(trim($filtri['PROVINCIA'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_LOCAL.PROVINCIA") . " LIKE :PROVINCIA";
            $where = 'AND';
        }
        if (array_key_exists('CODBELFI', $filtri) && $filtri['CODBELFI'] != null) {
            $this->addSqlParam($sqlParams, "CODBELFI", strtoupper(trim($filtri['CODBELFI'])), PDO::PARAM_STR);
            $sql .= " $where BTA_LOCAL.CODBELFI=:CODBELFI";
            $where = 'AND';
        }
        if (array_key_exists('NREGIONE', $filtri) && $filtri['NREGIONE'] != null) {
            $this->addSqlParam($sqlParams, "NREGIONE", strtoupper(trim($filtri['NREGIONE'])), PDO::PARAM_STR);
            $sql .= " $where BTA_LOCAL.NREGIONE=:NREGIONE";
            $where = 'AND';
        }
        if (array_key_exists('CODCNC', $filtri) && $filtri['CODCNC'] != null) {
            $this->addSqlParam($sqlParams, "CODCNC", $filtri['CODCNC'], PDO::PARAM_INT);
            $sql .= " $where BTA_LOCAL.CODCNC>=:CODCNC";
            $where = 'AND';
        }
        if (array_key_exists('CODCATASTO', $filtri) && $filtri['CODCATASTO'] != null) {
            $this->addSqlParam($sqlParams, "CODCATASTO", strtoupper(trim($filtri['CODCATASTO'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_LOCAL.CODCATASTO") . "=:CODCATASTO";
            $where = 'AND';
        }
        if (array_key_exists('CODCONSOL', $filtri) && $filtri['CODCONSOL'] != null) {
            $this->addSqlParam($sqlParams, "CODCONSOL", strtoupper(trim($filtri['CODCONSOL'])), PDO::PARAM_STR);
            $sql .= " $where BTA_LOCAL.CODCONSOL=:CODCONSOL";
            $where = 'AND';
        }
        if (array_key_exists('CODNAZPRO_KEY', $filtri) && $filtri['CODNAZPRO_KEY'] != null) {
            $this->addSqlParam($sqlParams, "CODNAZPRO_KEY", strtoupper(trim($filtri['CODNAZPRO_KEY'])), PDO::PARAM_INT);
            $sql .= " $where BTA_LOCAL.CODNAZPRO=:CODNAZPRO_KEY";
            $where = 'AND';
        }
        if (array_key_exists('CODLOCAL_KEY', $filtri) && intval($filtri['CODLOCAL_KEY']) >= 0 && is_numeric($filtri['CODLOCAL_KEY'])) {
            $this->addSqlParam($sqlParams, "CODLOCAL_KEY", strtoupper(trim($filtri['CODLOCAL_KEY'])), PDO::PARAM_INT);
            $sql .= " $where BTA_LOCAL.CODLOCAL=:CODLOCAL_KEY";
            $where = 'AND';
        }
        if (array_key_exists('CODLOCAL_000', $filtri)) {
//            $codLocal000 = "CODLOCAL_000";
            $this->addSqlParam($sqlParams, "CODLOCAL_000", 000, PDO::PARAM_INT);
            $sql .= " $where BTA_LOCAL.CODLOCAL=:CODLOCAL_000";
            $where = 'AND';
        }

        if (array_key_exists('DESLOCAL_NOTLIKE', $filtri) && $filtri['DESLOCAL_NOTLIKE'] != null) {
            $this->addSqlParam($sqlParams, "DESLOCAL_NOTLIKE", strtoupper(trim($filtri['DESLOCAL_NOTLIKE'])), PDO::PARAM_STR);
            $sql .= " $where BTA_LOCAL.DESLOCAL=:DESLOCAL_NOTLIKE";
            $where = 'AND';
        }

        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] !== null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where BTA_LOCAL.FLAG_DIS =:FLAG_DIS";
            $where = 'AND';
        }

        //gESTIONE CHECK A VIDEO
        if (((array_key_exists('ricComuniItaliani', $filtri)) || (array_key_exists('ricLuogoEccezionale', $filtri)) || array_key_exists('ricLocEstere', $filtri))) {
            if (!(intval($filtri['ricComuniItaliani'] == 0) && intval($filtri['ricLocEstere'] == 0) && intval($filtri['ricLuogoEccezionale']) == 0)) {
                $sql = $sql . ' ' . $where . ' (';
                $where = 'AND';
                $whereOr = '';
                if (array_key_exists('ricComuniItaliani', $filtri) && intval($filtri['ricComuniItaliani'] != 0)) {
                    $sql .= " (BTA_LOCAL.F_ITA_EST=0) ";
                    $whereOr = 'OR';
                }
                if (array_key_exists('ricLocEstere', $filtri) && intval($filtri['ricLocEstere'] != 0)) {
                    if ($whereOr == 'OR') {
                        $sql = $sql . $whereOr;
                    }
                    $sql .= "(BTA_LOCAL.F_ITA_EST=1 AND BTA_LOCAL.F_ECCEZIONALE=0)";
                    $whereOr = 'OR';
                }
                if (array_key_exists('ricLuogoEccezionale', $filtri) && intval($filtri['ricLuogoEccezionale'] != 0)) {
                    if ($whereOr == 'OR') {
                        $sql = $sql . ' ' . $whereOr;
                    }
                    $sql .= " BTA_LOCAL.F_ECCEZIONALE=1 ";
                }
                $sql = $sql . ')';
            }
        }
        if (array_key_exists('ricSoloAttive', $filtri) && $filtri['ricSoloAttive'] != 0) {
            $sql .= " $where BTA_LOCAL.FLAG_DIS=0 AND BTA_LOCAL.DATAFINE IS NULL";
        }

        if ((array_key_exists('ricSoloValidatiAnpr', $filtri) && $filtri['ricSoloValidatiAnpr'] != 0) && $validAnpr == true) {

            $sql .= " $where ( BTA_LOCAL_ANPR.ID_COMUANPR>0 AND BTA_LOCAL_ANPR.DATAVERIF IS NOT NULL AND BTA_LOCAL.F_ITA_EST=0 OR BTA_LOCAL.F_ITA_EST=1) ";
        }

        if ($excludeOrderBy == false) {
            if ($filtri['DESLOCAL']) {
                $sql = $sql . ' ORDER BY BTA_LOCAL.DESLOCAL, BTA_LOCAL.DATAINIZ DESC';
                $SortOrderField = 'DESLOCAL, DATAINIZ';
                $TypeOrderField = 'DESC';
            } else {
                $sql = $sql . ' ORDER BY BTA_LOCAL.ISTNAZPRO, BTA_LOCAL.ISTLOCAL, BTA_LOCAL.CODNAZPRO, BTA_LOCAL.CODLOCAL';
                $SortOrderField = 'ISTNAZPRO, ISTLOCAL, CODNAZPRO, CODLOCAL';
            }
        }
//        $sql .= $excludeOrderBy ? '' : ' ORDER BY BTA_LOCAL.CODNAZPRO, BTA_LOCAL.CODLOCAL, BTA_LOCAL.DESLOCAL';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_LOCAL
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaLocal($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaLocal($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_LOCAL
     * @param string $codnazpro Codice nazione
     * @param string $codlocal Codice localita
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaLocalChiave($codnazpro, $codlocal, &$sqlParams = array()) {
        $filtri = array();
        $filtri['CODNAZPRO_KEY'] = $codnazpro;
        $filtri['CODLOCAL_KEY'] = $codlocal;
        return self::getSqlLeggiBtaLocal($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_LOCAL
     * @param array $codnazpro, $codlocal     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Resultset
     */
    public function getSqlLeggiBtaLocalProgrStato($codnazpro, $codlocal, &$sqlParams = array()) {
        $sql = "SELECT COUNT(*) AS CONTA FROM BTA_LOCAL";
        $where = 'WHERE';
        if ($codnazpro != null) {
            $this->addSqlParam($sqlParams, "CODNAZPRO", $codnazpro, PDO::PARAM_INT);
            $sql .= " $where CODNAZPRO=:CODNAZPRO";
            $where = 'AND';
        }
        if ($codlocal != null) {
            $this->addSqlParam($sqlParams, "CODLOCAL", $codlocal, PDO::PARAM_INT);
            $sql .= " $where CODLOCAL=:CODLOCAL";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_LOCAL
     * @param string $codnazpro Codice nazione
     * @param string $codlocal Codice località
     * @return string Comando sql
     */
    public function getSqlLeggiBtaLocalDescr($deslocal) {
        $filtri = array();
        $filtri['DESLOCAL'] = $deslocal;
        return self::getSqlLeggiBtaLocal($filtri, true);
    }

    /**
     * Restituisce comando sql per lettura per codice ISTAT da tabella BTA_LOCAL
     * @param string $istnazpro Codice ISTAT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaLocalIstat($istnazpro, $codlocal, &$sqlParams = array()) {
        $filtri = array();
        $filtri['ISTNAZPRO'] = $istnazpro;
        $filtri['CODLOCAL_000'] = 000; // nel vecchio gli passava codlocal in questo modo. metodo get_defaults_progr_local_000
        return self::getSqlLeggiBtaLocal($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_LOCAL per chiave
     * @param string $codnazpro Codice nazione
     * @param string $codlocal Codice località
     * @return object Record
     */
    public function leggiBtaLocalChiave($codnazpro, $codlocal = 0) {
        if (!$codnazpro && !$codlocal) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaLocalChiave($codnazpro, $codlocal, $sqlParams), false, $sqlParams);
    }

// BTA_VIE

    /**
     * Restituisce comando sql per lettura tabella BTA_VIE
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaVie($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_VIE_V02.* FROM BTA_VIE_V02";
        $where = 'WHERE';
        if (array_key_exists('CODVIA', $filtri) && $filtri['CODVIA'] != null) {
            $this->addSqlParam($sqlParams, "CODVIA", $filtri['CODVIA'], PDO::PARAM_INT);
            $sql .= " $where CODVIA=:CODVIA";
            $where = 'AND';
        }
        if (array_key_exists('CODVIA_da', $filtri) && $filtri['CODVIA_a'] != null) {
            $this->addSqlParam($sqlParams, "CODVIA_DA", $filtri['CODVIA_da'], PDO::PARAM_INT);
            $this->addSqlParam($sqlParams, "CODVIA_A", $filtri['CODVIA_a'], PDO::PARAM_INT);
            $sql .= " $where (CODVIA BETWEEN :CODVIA_DA AND :CODVIA_A )";
            $where = 'AND';
        }

        if (array_key_exists('TOPONIMO', $filtri) && $filtri['TOPONIMO'] != null) {
            $this->addSqlParam($sqlParams, "TOPONIMO", $filtri['TOPONIMO'], PDO::PARAM_STR);
            $sql .= " $where TOPONIMO = :TOPONIMO";
            $where = 'AND';
        }
        if (array_key_exists('PROGENTE', $filtri) && $filtri['PROGENTE'] != null) {
            $this->addSqlParam($sqlParams, "PROGENTE", $filtri['PROGENTE'], PDO::PARAM_INT);
            $sql .= " $where PROGENTE =:PROGENTE";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] !== null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS =:FLAG_DIS";
            $where = 'AND';
        }
        if (array_key_exists('DESVIA', $filtri) && $filtri['DESVIA'] != null) {
            $this->addSqlParam($sqlParams, "DESVIA", "%" . strtoupper(trim($filtri['DESVIA'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESVIA") . " LIKE :DESVIA";
            $where = 'AND';
        }
        if (array_key_exists('DESVIAUFF', $filtri) && $filtri['DESVIAUFF'] != null) {
            $this->addSqlParam($sqlParams, "DESVIAUFF", "%" . strtoupper(trim($filtri['DESVIAUFF'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESVIAUFF") . " LIKE :DESVIAUFF";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODVIA';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_VIE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaVie($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaVie($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_VIE
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaVieChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['CODVIA'] = $cod;
        return self::getSqlLeggiBtaVie($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_VIE per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaVieChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaVieChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BTA_VIE
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaVieDenominazione($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT COUNT (*) AS CONTA FROM BTA_VIE";
        $where = 'WHERE';
        if (array_key_exists('DESVIA', $filtri) && $filtri['DESVIA'] != null) {
            $this->addSqlParam($sqlParams, "DESVIA", strtoupper(trim($filtri['DESVIA'])), PDO::PARAM_STR);
            $sql .= " $where DESVIA =:DESVIA";
            $where = 'AND';
        }
        if (array_key_exists('TOPONIMO', $filtri) && $filtri['TOPONIMO'] != null) {
            $this->addSqlParam($sqlParams, "TOPONIMO", strtoupper(trim($filtri['TOPONIMO'])), PDO::PARAM_STR);
            $sql .= " $where TOPONIMO = :TOPONIMO";
            $where = 'AND';
        }
        if (array_key_exists('PROGENTE', $filtri) && $filtri['PROGENTE'] != null) {
            $this->addSqlParam($sqlParams, "PROGENTE", $filtri['PROGENTE'], PDO::PARAM_INT);
            $sql .= " $where PROGENTE =:PROGENTE";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODVIA';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_VIE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaVieDenominazione($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaVieDenominazione($filtri, false, $sqlParams), false, $sqlParams);
    }

// BTA_TOPONO

    /**
     * Restituisce comando sql per lettura tabella BTA_TOPONO
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaTopono($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_TOPONO.* FROM BTA_TOPONO";
        $where = 'WHERE';
        if (array_key_exists('TOPONIMO', $filtri) && $filtri['TOPONIMO'] != null) {
            $this->addSqlParam($sqlParams, "TOPONIMO", strtoupper(trim($filtri['TOPONIMO'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("TOPONIMO") . " LIKE :TOPONIMO";
            $where = 'AND';
        }
        if (array_key_exists('TOPONIMO_controllo', $filtri) && $filtri['TOPONIMO_controllo'] != null) {
            $this->addSqlParam($sqlParams, "TOPONIMO_controllo", $filtri['TOPONIMO_controllo'], PDO::PARAM_STR);
            $sql .= " $where TOPONIMO =:TOPONIMO_controllo";
            $where = 'AND';
        }
        if (array_key_exists('TOPONKES', $filtri) && $filtri['TOPONKES'] != null) {
            $this->addSqlParam($sqlParams, "TOPONKES", $filtri['TOPONKES'], PDO::PARAM_INT);
            $sql .= " $where TOPONKES =:TOPONKES";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY TOPONIMO';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_TOPONO
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaTopono($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaTopono($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_TOPONO
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaToponoChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['TOPONIMO'] = $cod;
        return self::getSqlLeggiBtaTopono($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_TOPONO per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaToponoChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaToponoChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_NCIVI

    /**
     * Restituisce comando sql per lettura tabella BTA_NCIVI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNcivi($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT C.*, " . $this->getCitywareDB()->coalesce("(SELECT DISTINCT 1 FROM BTA_CIVINT WHERE PROGNCIV = C.PROGNCIV AND DATAFINE IS NULL)", 0)
                . " AS ICONA_INTERNO FROM BTA_NCIVI_V01 C ";
        $where = 'WHERE';

        $existCodViaFilter = false;
        if (array_key_exists('TIPONCIV', $filtri) && $filtri['TIPONCIV'] != null) {
            $this->addSqlParam($sqlParams, 'TIPONCIV', $filtri['TIPONCIV'], PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strLower("T.DESTIPCIV") . " LIKE " . $this->getCitywareDB()->strLower($this->getCitywareDB()->strConcat("'%'", ":TIPONCIV", "'%'"));
            $where = 'AND';
        }
        if (array_key_exists('CODVIA', $filtri) && $filtri['CODVIA'] != null) {
            $this->addSqlParam($sqlParams, "CODVIA", $filtri['CODVIA'], PDO::PARAM_INT);
            $sql .= " $where CODVIA =:CODVIA";
            $where = 'AND';
            $existCodViaFilter = true;
        }
        if (array_key_exists('CODVIA_DA', $filtri) && $filtri['CODVIA_A'] != null) {
            $this->addSqlParam($sqlParams, "CODVIA_DA", $filtri['CODVIA_DA'], PDO::PARAM_INT);
            $this->addSqlParam($sqlParams, "CODVIA_A", $filtri['CODVIA_A'], PDO::PARAM_INT);
            $sql .= " $where(CODVIA BETWEEN :CODVIA_DA AND :CODVIA_A)";
            $where = 'AND';
            $existCodViaFilter = true;
        }

        if (!$existCodViaFilter) {
            $sql .= " $where CODVIA > 0";
            $where = 'AND';
        }

        if (array_key_exists('RIC_ATTIVI', $filtri) && $filtri['RIC_ATTIVI'] == 1) {
            $sql .= " $where DATAFINE IS NULL";
            $where = 'AND';
        }

        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV'] != null) {
            $this->addSqlParam($sqlParams, "PROGNCIV", $filtri['PROGNCIV'], PDO::PARAM_INT);
            $sql .= " $where PROGNCIV =:PROGNCIV";
            $where = 'AND';
        }

        if (array_key_exists('PROGNCIV_DA', $filtri) && $filtri['PROGNCIV_DA'] != null) {
            $this->addSqlParam($sqlParams, "PROGNCIV_DA", $filtri['PROGNCIV_DA'], PDO::PARAM_INT);
            $this->addSqlParam($sqlParams, "PROGNCIV_A", $filtri['PROGNCIV_A'], PDO::PARAM_INT);
            $sql .= " $where(PROGNCIV BETWEEN :PROGNCIV_DA AND :PROGNCIV_A )";
            $where = 'AND';
        } else {
            $sql .= " $where PROGNCIV > 0";
            $where = 'AND';
        }

        if (array_key_exists('NUMCIV', $filtri) && $filtri['NUMCIV'] != null) {
            $this->addSqlParam($sqlParams, "NUMCIV", $filtri['NUMCIV'], PDO::PARAM_INT);
            $sql .= " $where NUMCIV =:NUMCIV";
            $where = 'AND';
        }

        if (array_key_exists('NUMCIV_DA', $filtri) && $filtri['NUMCIV_DA'] != null) {
            $this->addSqlParam($sqlParams, "NUMCIV_DA", $filtri['NUMCIV_DA'], PDO::PARAM_INT);
            $this->addSqlParam($sqlParams, "NUMCIV_A", $filtri['NUMCIV_A'], PDO::PARAM_INT);
            $sql .= " $where(NUMCIV BETWEEN :NUMCIV_DA  AND :NUMCIV_A )";
            $where = 'AND';
        }

        if (array_key_exists('SUBNCIV', $filtri) && $filtri['SUBNCIV'] != null) {
            $this->addSqlParam($sqlParams, "SUBNCIV", $filtri['SUBNCIV'], PDO::PARAM_STR);
            $sql .= " $where SUBNCIV = :SUBNCIV";
            $where = 'AND';
        }

        if ($filtri['SUBNCIV_EMPTY']) {
            $sql .= " $where SUBNCIV = ''";
            $where = 'AND';
        }

        if (array_key_exists('SUBNCIV_DA', $filtri) && $filtri['SUBNCIV_DA'] != null) {
            $this->addSqlParam($sqlParams, "SUBNCIV_DA", $filtri['SUBNCIV_DA'], PDO::PARAM_STR);
            $this->addSqlParam($sqlParams, "SUBNCIV_A", $filtri['SUBNCIV_A'], PDO::PARAM_STR);
            $sql .= " $where(SUBNCIV BETWEEN :SUBNCIV_DA AND :SUBNCIV_A) ";
            $where = 'AND';
        }

        if (array_key_exists('TOPONIMO', $filtri) && $filtri['TOPONIMO'] != null) {
            $this->addSqlParam($sqlParams, "TOPONIMO", "%" . strtoupper(trim($filtri['TOPONIMO'])) . "%", PDO::PARAM_STR);
            $sql .= " AND " . $this->getCitywareDB()->strUpper("TOPONIMO") . " LIKE :TOPONIMO";
        }

        if (array_key_exists('COD_IMMOBI', $filtri) && $filtri['COD_IMMOBI'] != null) {
            $this->addSqlParam($sqlParams, "COD_IMMOBI", "%" . strtoupper(trim($filtri['COD_IMMOBI'])) . "%", PDO::PARAM_STR);
            $sql .= " AND " . $this->getCitywareDB()->strUpper("COD_IMMOBI") . " LIKE :COD_IMMOBI";
        }

        if (array_key_exists('DESVIA', $filtri) && $filtri['DESVIA'] != null) {
            $this->addSqlParam($sqlParams, "DESVIA", "%" . strtoupper(trim($filtri['DESVIA'])) . "%", PDO::PARAM_STR);
            $sql .= " AND " . $this->getCitywareDB()->strUpper("DESVIA") . " LIKE :DESVIA";
        }

        if (array_key_exists('PROGENTE', $filtri) && $filtri['PROGENTE'] != null) {
            $this->addSqlParam($sqlParams, "PROGENTE", $filtri['PROGENTE'], PDO::PARAM_INT);
            $sql .= " AND PROGENTE =:PROGENTE";
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODVIA, NUMCIV, SUBNCIV';
        file_put_contents('C:/tmp/error.txt', $sql);        
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_NCIVI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaNcivi($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNcivi($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BTA_NCIVI 
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNciviCodviaNumciv($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_NCIVI.* FROM BTA_NCIVI";
        $where = 'WHERE';
        if (array_key_exists('CODVIA', $filtri) && $filtri['CODVIA'] != null) {
            $this->addSqlParam($sqlParams, "CODVIA", $filtri['CODVIA'], PDO::PARAM_INT);
            $sql .= " $where CODVIA =:CODVIA";
            $where = 'AND';
        }
        if (array_key_exists('NUMCIV', $filtri) && $filtri['NUMCIV'] != null) {
            $this->addSqlParam($sqlParams, "NUMCIV", $filtri['NUMCIV'], PDO::PARAM_INT);
            $sql .= " $where NUMCIV = :NUMCIV";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_NCIVI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaNciviCodviaNumciv($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNciviCodviaNumciv($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_NCIVI
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNciviChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['PROGNCIV'] = $cod;
        return self::getSqlLeggiBtaNcivi($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_NCIVI per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaNciviChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNciviChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BTA_NCIVI; utilizzata all'interno della function getIndirizzo (cwbLibCalcoli) 
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNciviGetIndirizzoEsterno($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_NCIVI.PROGNCIV, BTA_NCIVI.CODVIA, BTA_NCIVI.NUMCIV, BTA_NCIVI.SUBNCIV, BTA_NCIVI.TIPONCIV,"
                . "BTA_VIE.TOPONIMO, BTA_VIE.DESVIA, BTA_VIE.DATAINIZ"
                . " FROM BTA_VIE INNER JOIN BTA_NCIVI ON BTA_VIE.CODVIA = BTA_NCIVI.CODVIA";
        $where = 'WHERE';
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV'] != null) {
            $this->addSqlParam($sqlParams, "PROGNCIV", $filtri['PROGNCIV'], PDO::PARAM_INT);
            $sql .= " $where BTA_NCIVI.PROGNCIV =:PROGNCIV";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_NCIVI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaNciviGetIndirizzoEsterno($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNciviGetIndirizzoEsterno($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_NCIVI
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNciviGetIndirizzoEsternoChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['PROGNCIV'] = $cod;
        return self::getSqlLeggiBtaNciviGetIndirizzoEsterno($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_NCIVI per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaNciviGetIndirizzoEsternoChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNciviGetIndirizzoEsternoChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BTA_NCIVI; utilizzata all'interno della function getIndirizzo (cwbLibCalcoli) 
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNciviGetIndirizzoInterno($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_NCIVI.PROGNCIV, BTA_CIVINT.PROGINT, BTA_VIE.DESVIA,"
                . "BTA_VIE.TOPONIMO, BTA_CIVINT.SCALA, BTA_CIVINT.INTERNO, BTA_CIVINT.PIANO, BTA_NCIVI.NUMCIV,"
                . "BTA_NCIVI.SUBNCIV"
                . " FROM BTA_NCIVI INNER JOIN BTA_CIVINT ON BTA_NCIVI.PROGNCIV = BTA_CIVINT.PROGNCIV INNER JOIN"
                . " BTA_VIE ON BTA_NCIVI.CODVIA = BTA_VIE.CODVIA";
        $where = 'WHERE';
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV'] != null) {
            $this->addSqlParam($sqlParams, "PROGNCIV", $filtri['PROGNCIV'], PDO::PARAM_INT);
            $sql .= " $where BTA_NCIVI.PROGNCIV =:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('PROGINT', $filtri) && $filtri['PROGINT'] != null) {
            $this->addSqlParam($sqlParams, "PROGINT", $filtri['PROGINT'], PDO::PARAM_INT);
            $sql .= " $where BTA_CIVINT.PROGINT =:PROGINT";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_NCIVI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaNciviGetIndirizzoInterno($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNciviGetIndirizzoInterno($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_NCIVI
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNciviGetIndirizzoInternoChiave($prognciv, $progint, &$sqlParams = array()) {
        $filtri = array();
        $filtri['PROGNCIV'] = $prognciv;
        $filtri['PROGINT'] = $progint;
        return self::getSqlLeggiBtaNciviGetIndirizzoInterno($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_NCIVI per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaNciviGetIndirizzoInternoChiave($prognciv, $progint) {
        if (!$prognciv) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNciviGetIndirizzoInternoChiave($prognciv, $progint, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BTA_NCIVI; utilizzata all'interno della function getIndirizzo (cwbLibCalcoli) 
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNciviUbicazione($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_NCIVI.* ";
        if (array_key_exists('ANCHE_INTERNI', $filtri) && $filtri['ANCHE_INTERNI'] != null) {
            $sql .= " ,BTA_CIVINT.SCALA,BTA_CIVINT.PIANO,BTA_CIVINT.INTERNO,BTA_CIVINT.PROGINT  ";
        }
        $sql .= " FROM BTA_NCIVI  ";
        if (array_key_exists('ANCHE_INTERNI', $filtri) && $filtri['ANCHE_INTERNI'] != null) {
            $sql .= " LEFT JOIN BTA_CIVINT ON BTA_NCIVI.PROGNCIV = BTA_CIVINT.PROGNCIV ";
        }

        $where = 'WHERE';
        if (array_key_exists('CODVIA', $filtri) && $filtri['CODVIA'] != null) {
            $this->addSqlParam($sqlParams, "CODVIA", $filtri['CODVIA'], PDO::PARAM_INT);
            $sql .= " $where BTA_NCIVI.CODVIA =:CODVIA";
            $where = 'AND';
        }
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV'] != null) {
            $this->addSqlParam($sqlParams, "PROGNCIV", $filtri['PROGNCIV'], PDO::PARAM_INT);
            $sql .= " $where BTA_NCIVI.PROGNCIV =:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('NUMCIVI_DA', $filtri) && $filtri['NUMCIVI_DA'] != null) {
            $this->addSqlParam($sqlParams, "NUMCIVI_DA", $filtri['NUMCIVI_DA'], PDO::PARAM_INT);
            if (array_key_exists('SUBNCIVI_DA', $filtri) && $filtri['SUBNCIVI_DA'] != null) {
                $this->addSqlParam($sqlParams, "SUBNCIVI_DA", $filtri['SUBNCIVI_DA'], PDO::PARAM_INT);
                $sql .= " $where  (BTA_NCIVI.NUMCIV >= :NUMCIVI_DA AND  BTA_NCIVI.SUBNCIV >= :SUBNCIVI_DA)";
            } else {
                $sql .= " $where BTA_NCIVI.NUMCIV >= :NUMCIVI_DA";
            }

            $where = 'AND';
        }
        if (array_key_exists('NUMCIVI_A', $filtri) && $filtri['NUMCIVI_A'] != null) {
            $this->addSqlParam($sqlParams, "NUMCIVI_A", $filtri['NUMCIVI_A'], PDO::PARAM_INT);
            if (array_key_exists('SUBNCIVI_A', $filtri) && $filtri['SUBNCIVI_A'] != null) {
                $this->addSqlParam($sqlParams, "SUBNCIVI_A", $filtri['SUBNCIVI_A'], PDO::PARAM_INT);
                $sql .= " $where  (BTA_NCIVI.NUMCIV <= :NUMCIVI_A AND  BTA_NCIVI.SUBNCIV <= :SUBNCIVI_A)";
            } else {
                $sql .= " $where BTA_NCIVI.NUMCIV <= :NUMCIVI_A";
            }
            $where = 'AND';
        }
        if (array_key_exists('PARI', $filtri) && $filtri['PARI'] != null) {
            $sql .= " $where " . $this->getCitywareDB()->module('BTA_NCIVI.NUMCIV', 2) . " = 0 ";
            $where = 'AND';
        } else if (array_key_exists('DISPARI', $filtri) && $filtri['DISPARI'] != null) {
            $sql .= " $where " . $this->getCitywareDB()->module('BTA_NCIVI.NUMCIV', 2) . " = 1 ";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODVIA, NUMCIV, SUBNCIV';


        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_NCIVI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaNciviUbicazione($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNciviUbicazione($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

// BTA_CIVINT

    /**
     * Restituisce comando sql per lettura tabella BTA_CIVINT
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaCivint($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_CIVINT_V01.*, "
                . $this->getCitywareDB()->strConcat('PROGNCIV', "'|'", 'PROGINT') . " AS \"ROW_ID\" "
                . "FROM BTA_CIVINT_V01";
        $where = 'WHERE';
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV'] != null) {
            $this->addSqlParam($sqlParams, "PROGNCIV", $filtri['PROGNCIV'], PDO::PARAM_INT);
            $sql .= " $where PROGNCIV =:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('PROGINT', $filtri) && $filtri['PROGINT'] != null) {
            $this->addSqlParam($sqlParams, "PROGINT", $filtri['PROGINT'], PDO::PARAM_INT);
//            $sql .= " $where PROGINT = " . $filtri['PROGINT'];  ERRORE !!!!
            $sql .= " $where PROGINT =:PROGINT";
            $where = 'AND';
        }
//           Tolto controllo "$filtri['SCALA'] != null" perch� nel caso in cui il campo scala fosse vuoto, mi selezionava il record sbagliato
        if (array_key_exists('SCALA', $filtri)) {
            $this->addSqlParam($sqlParams, "SCALA", $filtri['SCALA'], PDO::PARAM_STR);
            $sql .= " $where SCALA = :SCALA";
            $where = 'AND';
        }
        if (array_key_exists('INTERNO', $filtri) && $filtri['INTERNO'] != null) {
            $this->addSqlParam($sqlParams, "INTERNO", $filtri['INTERNO'], PDO::PARAM_STR);
            $sql .= " $where INTERNO = :INTERNO";
            $where = 'AND';
        }
        if (array_key_exists('PIANO', $filtri) && $filtri['PIANO'] != null) {
            $this->addSqlParam($sqlParams, "PIANO", $filtri['PIANO'], PDO::PARAM_STR);
            $sql .= " $where PIANO = :PIANO";
            $where = 'AND';
        }
        if (array_key_exists('TIPONCIV', $filtri) && $filtri['TIPONCIV'] != null) {
            $this->addSqlParam($sqlParams, "TIPONCIV", $filtri['TIPONCIV'], PDO::PARAM_INT);
            $sql .= " $where TIPONCIV = :TIPONCIV";
            $where = 'AND';
        }
        if (array_key_exists('DATAINIZ', $filtri) && $filtri['DATAINIZ'] != null) {
            $this->addSqlParam($sqlParams, "DATAINIZ", $filtri['DATAINIZ'], PDO::PARAM_STR);
            $sql .= " $where DATAINIZ = :DATAINIZ";
            $where = 'AND';
        }
        if (array_key_exists('DATAFINE', $filtri) && $filtri['DATAFINE'] != null) {
            $this->addSqlParam($sqlParams, "DATAFINE", $filtri['DATAFINE'], PDO::PARAM_STR);
            $sql .= " $where DATAFINE = :DATAFINE";
            $where = 'AND';
        }
        if (array_key_exists('PROGNCIV_KEY', $filtri) && $filtri['PROGNCIV_KEY'] != null) {
            $this->addSqlParam($sqlParams, "PROGNCIV_KEY", $filtri['PROGNCIV_KEY'], PDO::PARAM_INT);
            $sql .= " $where PROGNCIV = :PROGNCIV_KEY";
            $where = 'AND';
        }
        if (array_key_exists('PROGINT_KEY', $filtri) && $filtri['PROGINT_KEY'] != null) {
            $this->addSqlParam($sqlParams, "PROGINT_KEY", $filtri['PROGINT_KEY'], PDO::PARAM_INT);
            $sql .= " $where PROGINT = :PROGINT_KEY";
            $where = 'AND';
        }
        if (array_key_exists('RIC_ATTIVI', $filtri) && $filtri['RIC_ATTIVI'] == 1) {
            $sql .= " $where DATAFINE IS NULL";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGINT';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_CIVINT
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaCivint($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaCivint($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_CIVINT
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaCivintChiave($prognciv, $progint = null, &$sqlParams = array()) {
        $filtri = array();
        $filtri['PROGNCIV_KEY'] = $prognciv;
        $filtri['PROGINT_KEY'] = $progint;
        return self::getSqlLeggiBtaCivint($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_CIVINT per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaCivintChiave($prognciv, $progint = null) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaCivintChiave($prognciv, $progint, $sqlParams), false, $sqlParams);
    }

// BTA_DEFSU1

    /**
     * Restituisce comando sql per lettura tabella BTA_DEFSU1
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaDefsu1($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_DEFSU1.* FROM BTA_DEFSU1";
        $where = 'WHERE';
        if (array_key_exists('DEFSUBN', $filtri) && $filtri['DEFSUBN'] != null) {
            $this->addSqlParam($sqlParams, "DEFSUBN", $filtri['DEFSUBN'], PDO::PARAM_STR);
            $sql .= " $where DEFSUBN =:DEFSUBN";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY DEFSUBN';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_DEFSU1
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaDefsu1($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaDefsu1($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_DEFSU1
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaDefsu1Chiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['DEFSUBN'] = $cod;
        return self::getSqlLeggiBtaDefsu1($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_DEFSU1 per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaDefsu1Chiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaDefsu1Chiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_DEFSU2

    /**
     * Restituisce comando sql per lettura tabella BTA_DEFSU2
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaDefsu2($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_DEFSU2.* FROM BTA_DEFSU2";
        $where = 'WHERE';
        if (array_key_exists('DEFSUBN', $filtri) && $filtri['DEFSUBN'] != null) {
            $this->addSqlParam($sqlParams, "DEFSUBN", $filtri['DEFSUBN'], PDO::PARAM_STR);
            $sql .= " $where DEFSUBN = :DEFSUBN";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY DEFSUBN';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_DEFSU2
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaDefsu2($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaDefsu2($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_DEFSU2
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaDefsu2Chiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['DEFSUBN'] = $cod;
        return self::getSqlLeggiBtaDefsu2($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_DEFSU2 per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaDefsu2Chiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaDefsu2Chiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_DEFSU3

    /**
     * Restituisce comando sql per lettura tabella BTA_DEFSU3
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaDefsu3($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_DEFSU3.* FROM BTA_DEFSU3";
        $where = 'WHERE';
        if (array_key_exists('DEFSUBN', $filtri) && $filtri['DEFSUBN'] != null) {
            $this->addSqlParam($sqlParams, "DEFSUBN", $filtri['DEFSUBN'], PDO::PARAM_STR);
            $sql .= " $where DEFSUBN = :DEFSUBN";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY DEFSUBN';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_DEFSU3
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaDefsu3($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaDefsu3($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_DEFSU3
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaDefsu3Chiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['DEFSUBN'] = $cod;
        return self::getSqlLeggiBtaDefsu3($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_DEFSU3 per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaDefsu3Chiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaDefsu3Chiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_DEFSCA

    /**
     * Restituisce comando sql per lettura tabella BTA_DEFSCA
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaDefsca($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_DEFSCA.* FROM BTA_DEFSCA";
        $where = 'WHERE';
        if (array_key_exists('DEFSCALA', $filtri) && $filtri['DEFSCALA'] != null) {
            $this->addSqlParam($sqlParams, "DEFSCALA", "%" . strtoupper(trim($filtri['DEFSCALA'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DEFSCALA") . " LIKE :DEFSCALA";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY DEFSCALA';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_DEFSCA
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaDefsca($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaDefsca($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_DEFSCA
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaDefscaChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['DEFSCALA'] = $cod;
        return self::getSqlLeggiBtaDefsca($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_DEFSCA per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaDefscaChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaDefscaChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_DEFPIA

    /**
     * Restituisce comando sql per lettura tabella BTA_DEFPIA
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @$sqlParams
     * @return string Comando sql
     */
    public function getSqlLeggiBtaDefpia($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_DEFPIA.* FROM BTA_DEFPIA";
        $where = 'WHERE';
        if (array_key_exists('DEFPIA', $filtri) && $filtri['DEFPIA'] != null) {
            $this->addSqlParam($sqlParams, "DEFPIA", strtoupper(trim($filtri['DEFPIA'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DEFPIA") . " =:DEFPIA";
            $where = 'AND';
        }
        if (array_key_exists('DESPIAN', $filtri) && $filtri['DESPIAN'] != null) {
            $this->addSqlParam($sqlParams, "DESPIAN", "%" . strtoupper(trim($filtri['DESPIAN'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESPIAN") . " LIKE :DESPIAN";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY DEFPIA';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_DEFPIA
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaDefpia($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaDefpia($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_DEFPIA
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaDefpiaChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['DEFPIA'] = $cod;
        return self::getSqlLeggiBtaDefpia($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_DEFPIA per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaDefpiaChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaDefpiaChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_DEFINT

    /**
     * Restituisce comando sql per lettura tabella BTA_DEFINT
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaDefint($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_DEFINT.* FROM BTA_DEFINT";
        $where = 'WHERE';
        if (array_key_exists('DEFINT', $filtri) && $filtri['DEFINT'] != null) {
            $this->addSqlParam($sqlParams, "DEFINT", $filtri['DEFINT'], PDO::PARAM_STR);
            $sql .= " $where DEFINT = :DEFINT";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY DEFINT';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_DEFINT
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaDefint($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaDefint($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_DEFINT
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaDefintChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['DEFINT'] = $cod;
        return self::getSqlLeggiBtaDefint($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_DEFINT per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaDefintChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaDefintChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_VIEGCO

    /**
     * Restituisce comando sql per lettura tabella BTA_VIEGCO
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaViegco($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_VIEGCO_V01.* FROM BTA_VIEGCO_v01";
        $where = 'WHERE';
        if (array_key_exists('PROG_VCO', $filtri) && $filtri['PROG_VCO'] != null) {
            $this->addSqlParam($sqlParams, "PROG_VCO", $filtri['PROG_VCO'], PDO::PARAM_INT);
            $sql .= " $where PROG_VCO=:PROG_VCO";
            $where = 'AND';
        }
        if (array_key_exists('DESVIA', $filtri) && $filtri['DESVIA'] != null) {
            $this->addSqlParam($sqlParams, "DESVIA", "%" . strtoupper(trim($filtri['DESVIA'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESVIA") . " LIKE :DESVIA";
            $where = 'AND';
        }
        if (array_key_exists('DESCR_1', $filtri) && $filtri['DESCR_1'] != null) {
            $this->addSqlParam($sqlParams, "DESCR_1", "%" . strtoupper(trim($filtri['DESCR_1'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESCR_1") . " LIKE :DESCR_1";
            $where = 'AND';
        }
        if (array_key_exists('DESCR_2', $filtri) && $filtri['DESCR_2'] != null) {
            $this->addSqlParam($sqlParams, "DESCR_2", "%" . strtoupper(trim($filtri['DESCR_2'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESCR_2") . " LIKE :DESCR_2";
            $where = 'AND';
        }
        if (array_key_exists('PARI_DISP', $filtri) && $filtri['PARI_DISP'] != null) {
            $this->addSqlParam($sqlParams, "PARI_DISP", "%" . strtoupper(trim($filtri['PARI_DISP'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("PARI_DISP") . " LIKE :PARI_DISP";
            $where = 'AND';
        }
        if (array_key_exists('CODNAZPRO', $filtri) && ($filtri['CODNAZPRO'] != null && $filtri['CODNAZPRO'] != 000)) {
            $this->addSqlParam($sqlParams, "CODNAZPRO", $filtri['CODNAZPRO'], PDO::PARAM_INT);
            $sql .= " $where CODNAZPRO = :CODNAZPRO";
            $where = 'AND';
        }
        if (array_key_exists('CODLOCAL', $filtri) && ($filtri['CODLOCAL'] != null && $filtri['CODLOCAL'] != 000)) {
            $this->addSqlParam($sqlParams, "CODLOCAL", $filtri['CODLOCAL'], PDO::PARAM_INT);
            $sql .= " $where CODLOCAL = :CODLOCAL";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY DESVIA';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_VIEGCO
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaViegco($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaViegco($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_VIEGCO
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaViegcoChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_VCO'] = $cod;
        return self::getSqlLeggiBtaViegco($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_VIEGCO per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaViegcoChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaViegcoChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BTA_VIEGCO
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaViegcoDenominazione($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_VIEGCO.* FROM BTA_VIEGCO";
        $where = 'WHERE';
        if (array_key_exists('DESVIA', $filtri) && $filtri['DESVIA'] != null) {
            $this->addSqlParam($sqlParams, "DESVIA", $filtri['DESVIA'], PDO::PARAM_STR);
            $sql .= " $where DESVIA = :DESVIA";
            $where = 'AND';
        }
        if (array_key_exists('TOPONIMO', $filtri) && $filtri['TOPONIMO'] != null) {
            $this->addSqlParam($sqlParams, "TOPONIMO", $filtri['TOPONIMO'], PDO::PARAM_STR);
            $sql .= " $where TOPONIMO = :TOPONIMO";
            $where = 'AND';
        }
        if (array_key_exists('CODNAZPRO', $filtri) && $filtri['CODNAZPRO'] != null) {
            $this->addSqlParam($sqlParams, "CODNAZPRO", $filtri['CODNAZPRO'], PDO::PARAM_INT);
            $sql .= " $where CODNAZPRO =:CODNAZPRO";
            $where = 'AND';
        }
        if (array_key_exists('CODLOCAL', $filtri) && $filtri['CODLOCAL'] != null) {
            $this->addSqlParam($sqlParams, "CODLOCAL", $filtri['CODLOCAL'], PDO::PARAM_INT);
            $sql .= " $where CODLOCAL = :CODLOCAL";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_VIEGCO
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaViegcoDenominazione($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaViegcoDenominazione($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

// BTA_TIPCIV

    /**
     * Restituisce comando sql per lettura tabella BTA_TIPCIV
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaTipciv($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_TIPCIV.* FROM BTA_TIPCIV";
        $where = 'WHERE';
        if (array_key_exists('TIPONCIV', $filtri) && $filtri['TIPONCIV'] != null) {
            $this->addSqlParam($sqlParams, "TIPONCIV", $filtri['TIPONCIV'], PDO::PARAM_INT);
            $sql .= " $where TIPONCIV = :TIPONCIV";
            $where = 'AND';
        }
        if (array_key_exists('DESTIPCIV', $filtri) && $filtri['DESTIPCIV'] != null) {
            $this->addSqlParam($sqlParams, "DESTIPCIV", "%" . strtoupper(trim($filtri['DESTIPCIV'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESTIPCIV") . " LIKE :DESTIPCIV";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY TIPONCIV';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_TIPCIV
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaTipciv($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaTipciv($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_TIPCIV
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaTipcivChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['TIPONCIV'] = $cod;
        return self::getSqlLeggiBtaTipciv($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_TIPCIV per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaTipcivChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaTipcivChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_ATECO7

    /**
     * Restituisce comando sql per lettura tabella BTA_ATECO7
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaAteco7($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_ATECO7.* FROM BTA_ATECO7";
        $where = 'WHERE';
        if (array_key_exists('CODATECO7', $filtri) && $filtri['CODATECO7'] != null) {
            $this->addSqlParam($sqlParams, "CODATECO7", $filtri['CODATECO7'], PDO::PARAM_STR);
            $sql .= " $where CODATECO7=:CODATECO7";
            $where = 'AND';
        }
        if (array_key_exists('CODATTIVEC', $filtri) && $filtri['CODATTIVEC'] != null) {
            $this->addSqlParam($sqlParams, "CODATTIVEC", $filtri['CODATTIVEC'], PDO::PARAM_STR);
            $sql .= " $where CODATTIVEC = :CODATTIVEC";
            $where = 'AND';
        }
        if (array_key_exists('SETATECO', $filtri) && $filtri['SETATECO'] != null) {
            $this->addSqlParam($sqlParams, "SETATECO", $filtri['SETATECO'], PDO::PARAM_STR);
            $sql .= " $where SETATECO = :SETATECO";
            $where = 'AND';
        }
        if (array_key_exists('DESATTECIV', $filtri) && $filtri['DESATTECIV'] != null) {
            $this->addSqlParam($sqlParams, "DESATTECIV", "%" . strtoupper(trim($filtri['DESATTECIV'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESATTECIV") . " LIKE :DESATTECIV";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] != null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS = :FLAG_DIS";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODATECO7';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_ATECO7
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaAteco7($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaAteco7($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_ATECO7
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaAteco7Chiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['CODATECO7'] = $cod;
        return self::getSqlLeggiBtaAteco7($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_ATECO7 per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaAteco7Chiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaAteco7Chiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_ATTECO

    /**
     * Restituisce comando sql per lettura tabella BTA_ATTECO
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaAtteco($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_ATTECO.* FROM BTA_ATTECO";
        $where = 'WHERE';
        if (array_key_exists('CODATTIVEC', $filtri) && $filtri['CODATTIVEC'] != null) {
            $this->addSqlParam($sqlParams, "CODATTIVEC", $filtri['CODATTIVEC'], PDO::PARAM_STR);
            $sql .= " $where CODATTIVEC = :CODATTIVEC";
            $where = 'AND';
        }
        if (array_key_exists('GRUP_ATTEC', $filtri) && $filtri['GRUP_ATTEC'] != null) {
            $this->addSqlParam($sqlParams, "GRUP_ATTEC", $filtri['GRUP_ATTEC'], PDO::PARAM_STR);
            $sql .= " $where GRUP_ATTEC = :GRUP_ATTEC";
            $where = 'AND';
        }
        if (array_key_exists('CODATTIVIS', $filtri) && $filtri['CODATTIVIS'] != null) {
            $this->addSqlParam($sqlParams, "CODATTIVIS", $filtri['CODATTIVIS'], PDO::PARAM_STR);
            $sql .= " $where CODATTIVIS = :CODATTIVIS";
            $where = 'AND';
        }
        if (array_key_exists('DESATTECIV', $filtri) && $filtri['DESATTECIV'] != null) {
            $this->addSqlParam($sqlParams, "DESATTECIV", "%" . strtoupper(trim($filtri['DESATTECIV'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESATTECIV") . " LIKE :DESATTECIV";
            $where = 'AND';
        }
        if (array_key_exists('DESATTECIS', $filtri) && $filtri['DESATTECIS'] != null) {
            $this->addSqlParam($sqlParams, "DESATTECIS", "%" . strtoupper(trim($filtri['DESATTECIS'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESATTECIS") . " LIKE :DESATTECIS";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] != null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS = :FLAG_DIS";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODATTIVEC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_ATTECO
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaAtteco($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaAtteco($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_ATTECO
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaAttecoChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['CODATTIVEC'] = $cod;
        return self::getSqlLeggiBtaAtteco($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_ATTECO per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaAttecoChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaAttecoChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_PROF

    /**
     * Restituisce comando sql per lettura tabella BTA_PROF
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaProf($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_PROF.* FROM BTA_PROF";
        $where = 'WHERE';
        if (array_key_exists('CODPROF', $filtri) && $filtri['CODPROF'] != null) {
            $this->addSqlParam($sqlParams, "CODPROF", $filtri['CODPROF'], PDO::PARAM_INT);
            $sql .= " $where CODPROF>=:CODPROF";
            $where = 'AND';
        }
        if (array_key_exists('CODPROF_IS', $filtri) && $filtri['CODPROF_IS'] != null) {
            $this->addSqlParam($sqlParams, "CODPROF_IS", $filtri['CODPROF_IS'], PDO::PARAM_INT);
            $sql .= " $where CODPROF_IS = :CODPROF_IS";
            $where = 'AND';
        }
        if (array_key_exists('CODPROF_KEY', $filtri) && $filtri['CODPROF_KEY'] != null) {
            $this->addSqlParam($sqlParams, "CODPROF_KEY", $filtri['CODPROF_KEY'], PDO::PARAM_INT);
            $sql .= " $where CODPROF = :CODPROF_KEY";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] != null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS = :FLAG_DIS";
            $where = 'AND';
        }
        if (array_key_exists('DESPROF_IS', $filtri) && $filtri['DESPROF_IS'] != null) {
            $this->addSqlParam($sqlParams, "DESPROF_IS", "%" . strtoupper(trim($filtri['DESPROF_IS'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESPROF_IS") . " LIKE :DESPROF_IS";
            $where = 'AND';
        }
        if (array_key_exists('DESPROF', $filtri) && $filtri['DESPROF'] != null) {
            $this->addSqlParam($sqlParams, "DESPROF", "%" . strtoupper(trim($filtri['DESPROF'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESPROF") . " LIKE :DESPROF";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODPROF';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_PROF
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaProf($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaProf($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_PROF
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaProfChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['CODPROF_KEY'] = $cod;
        return self::getSqlLeggiBtaProf($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_PROF per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaProfChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaProfChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_GRNOTE

    /**
     * Restituisce comando sql per lettura tabella BTA_GRNOTE
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaGrnote($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_GRNOTE.* FROM BTA_GRNOTE";
        $where = 'WHERE';
        if (array_key_exists('IDGRNOTE', $filtri) && $filtri['IDGRNOTE'] != null) {
            $this->addSqlParam($sqlParams, "IDGRNOTE", $filtri['IDGRNOTE'], PDO::PARAM_INT);
            $sql .= " $where IDGRNOTE = :IDGRNOTE";
            $where = 'AND';
        }
        if (array_key_exists('DESGRUPPO', $filtri) && $filtri['DESGRUPPO'] != null) {
            $this->addSqlParam($sqlParams, "DESGRUPPO", "%" . strtoupper(trim($filtri['DESGRUPPO'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESGRUPPO") . " LIKE :DESGRUPPO";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDGRNOTE';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_GRNOTE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaGrnote($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaGrnote($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_GRNOTE
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaGrnoteChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['IDGRNOTE'] = $cod;
        return self::getSqlLeggiBtaGrnote($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_GRNOTE per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaGrnoteChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaGrnoteChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_TIPCOM

    /**
     * Restituisce comando sql per lettura tabella BTA_TIPCOM
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaTipcom($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_TIPCOM.* FROM BTA_TIPCOM";
        $where = 'WHERE';
        if (array_key_exists('TIPO_COM', $filtri) && $filtri['TIPO_COM'] != null) {
            $this->addSqlParam($sqlParams, "TIPO_COM", $filtri['TIPO_COM'], PDO::PARAM_INT);
            $sql .= " $where TIPO_COM =:TIPO_COM";
            $where = 'AND';
        }
        if (array_key_exists('DES_TIPCOM', $filtri) && $filtri['DES_TIPCOM'] != null) {
            $this->addSqlParam($sqlParams, "DES_TIPCOM", "%" . strtoupper(trim($filtri['DES_TIPCOM'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DES_TIPCOM") . " LIKE :DES_TIPCOM";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] != null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS =:FLAG_DIS";
            $where = 'AND';
        }
        if (array_key_exists('COMPROV', $filtri) && $filtri['COMPROV'] != null) {
            $this->addSqlParam($sqlParams, "COMPROV", $filtri['COMPROV'], PDO::PARAM_INT);
            $sql .= " $where COMPROV =:COMPROV";
            $where = 'AND';
        }
        if (array_key_exists('COMPROV_ALLORSEL', $filtri) && $filtri['COMPROV_ALLORSEL'] != null) {
            $this->addSqlParam($sqlParams, "COMPROV", $filtri['COMPROV_ALLORSEL'], PDO::PARAM_INT);
            $sql .= " $where (COMPROV = 1 OR COMPROV=:COMPROV)";  // 1=Tutti o Codice passato: 5 x Serv.Economici
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY FLAG_TIPCO, TIPO_COM';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_TIPCOM
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaTipcom($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaTipcom($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_TIPCOM
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaTipcomChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['TIPO_COM'] = $cod;
        return self::getSqlLeggiBtaTipcom($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_TIPCOM per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaTipcomChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaTipcomChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_IPARUB

    /**
     * Restituisce comando sql per lettura tabella BTA_IPARUB
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaIparub($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT RUB.*, AMM.IPA_DESAMM, DEST.IPA_UFFDES"
                . " FROM BTA_IPARUB RUB LEFT JOIN BTA_IPADES DEST on RUB.IPA_CODDES = DEST.IPA_CODDES"
                . " LEFT JOIN BTA_IPAAMM AMM on RUB.IPA_CODAMM = AMM.IPA_CODAMM";
        $where = 'WHERE';

        if (array_key_exists('IPA_CODAMM', $filtri) && $filtri['IPA_CODAMM'] != null) {
            $this->addSqlParam($sqlParams, "IPA_CODAMM", "%" . strtoupper(trim($filtri['IPA_CODAMM'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("AMM.IPA_CODAMM") . " LIKE :IPA_CODAMM";
            $where = 'AND';
        }
        if (array_key_exists('PK', $filtri) && $filtri['PK'] != null) {
            $this->addSqlParam($sqlParams, "IPA_CODAMM", $filtri['PK'], PDO::PARAM_INT);
            $sql .= " $where  " . $this->getCitywareDB()->strUpper("RUB.IPA_CODAMM") . " =:IPA_CODAMM";
            $where = 'AND';
        }
        if (array_key_exists('PK_KEY', $filtri) && $filtri['PK_KEY'] != null) {
            $this->addSqlParam($sqlParams, "PK_KEY", $filtri['PK_KEY'], PDO::PARAM_STR);
            $sql .= " $where PK =:PK_KEY";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PK';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_IPARUB
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaIparub($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaIparub($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_IPARUB
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaIparubChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['PK_KEY'] = $cod;
        return self::getSqlLeggiBtaIparub($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_IPARUB per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaIparubChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaIparubChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_IPADES

    /**
     * Restituisce comando sql per lettura tabella BTA_IPADES
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaIpades($filtri, $excludeOrderBy = false, $sqlParams = true) {
        $sql = "SELECT BTA_IPADES.* FROM BTA_IPADES";
        if (array_key_exists('IPA_UFFDES', $filtri) && $filtri['IPA_UFFDES'] != null) {
            $this->addSqlParam($sqlParams, "IPA_UFFDES", "%" . strtoupper(trim($filtri['IPA_UFFDES'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("IPA_UFFDES") . " LIKE :IPA_UFFDES";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY IPA_CODDES';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_IPADES
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaIpades($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaAttecoChiave($filtri, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_IPADES
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaIpadesChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['IPA_CODDES'] = $cod;
        return self::getSqlLeggiBtaIpades($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_IPADES per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaIpadesChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaIpadesChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_ABI

    /**
     * Restituisce comando sql per lettura tabella BTA_ABI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaAbi($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_ABI.* FROM BTA_ABI";
        $where = 'WHERE';
        if (array_key_exists('ABI', $filtri) && $filtri['ABI'] != null) {
            $this->addSqlParam($sqlParams, "ABI", $filtri['ABI'], PDO::PARAM_INT);
            $sql .= " $where ABI = :ABI";
            $where = 'AND';
        }
        if (array_key_exists('ABI_CIN', $filtri) && $filtri['ABI_CIN'] != null) {
            $this->addSqlParam($sqlParams, "ABI_CIN", $filtri['ABI_CIN'], PDO::PARAM_STR);
            $sql .= " $where ABI_CIN = :ABI_CIN";
            $where = 'AND';
        }
        if (array_key_exists('DESBANCA', $filtri) && $filtri['DESBANCA'] != null) {
            $this->addSqlParam($sqlParams, "DESBANCA", "%" . strtoupper(trim($filtri['DESBANCA'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESBANCA") . " LIKE :DESBANCA";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] != null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS = :FLAG_DIS";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY ABI';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_ABI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaAbi($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaAbi($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_ABI
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaAbiChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['ABI'] = $cod;
        return self::getSqlLeggiBtaAbi($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_ABI per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaAbiChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaAbiChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_CAB

    /**
     * Restituisce comando sql per lettura tabella BTA_CAB
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaCab($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_CAB.* FROM BTA_CAB";
        $where = 'WHERE';
        if (array_key_exists('ABI', $filtri) && $filtri['ABI'] != null) {
            $this->addSqlParam($sqlParams, "ABI", $filtri['ABI'], PDO::PARAM_INT);
            $sql .= " $where ABI = :ABI";
            $where = 'AND';
        }
        if (array_key_exists('CAB', $filtri) && $filtri['CAB'] != null) {
            $this->addSqlParam($sqlParams, "CAB", $filtri['CAB'], PDO::PARAM_INT);
            $sql .= " $where CAB = :CAB";
            $where = 'AND';
        }
        if (array_key_exists('CAB_CIN', $filtri) && $filtri['CAB_CIN'] != null) {
            $this->addSqlParam($sqlParams, "CAB_CIN", $filtri['CAB_CIN'], PDO::PARAM_STR);
            $sql .= " $where CAB_CIN = :CAB_CIN";
            $where = 'AND';
        }
        if (array_key_exists('DES_SPORT', $filtri) && $filtri['DES_SPORT'] != null) {
            $this->addSqlParam($sqlParams, "DES_SPORT", "%" . strtoupper(trim($filtri['DES_SPORT'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DES_SPORT") . " LIKE :DES_SPORT";
            $where = 'AND';
        }
        if (array_key_exists('COMUNEUBIC', $filtri) && $filtri['COMUNEUBIC'] != null) {
            $this->addSqlParam($sqlParams, "COMUNEUBIC", "%" . strtoupper(trim($filtri['COMUNEUBIC'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("COMUNEUBIC") . " LIKE :COMUNEUBIC";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] != null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS = :FLAG_DIS";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY ABI';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_CAB
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaCab($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaCab($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_CAB
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaCabChiave($abi, $cab, &$sqlParams = array()) {
        $filtri = array();
        $filtri['ABI'] = $abi;
        $filtri['CAB'] = $cab;
        return self::getSqlLeggiBtaCab($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_CAB per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaCabChiave($abi, $cab) {
        if (empty($abi) || empty($cab)) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaCabChiave($abi, $cab, $sqlParams), false, $sqlParams);
    }

// BTA_ELECIV

    /**
     * Restituisce comando sql per lettura tabella BTA_NCIVI_V01
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaEleciv($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_NCIVI_V01.* FROM BTA_NCIVI_V01";
        $where = 'WHERE';
        $this->betweenValidateDate($filtri, $where, $sqlParams);
        $sql .= $excludeOrderBy ? '' : ' ORDER BY DESVIA, NUMCIV, SUBNCIV';
        return $sql;
    }

// BTA_ELEVIE

    /**
     * Restituisce comando sql per lettura tabella BTA_VIE
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaElevie($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_VIE.* FROM BTA_VIE";
        $where = 'WHERE';
        $this->betweenValidateDate($filtri, $where, $sqlParams);
        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODVIA_O, CODVIA';
        return $sql;
    }

// BTA_RINCIV

    /**
     * Restituisce comando sql per lettura tabella BTA_NCIVI_V01
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRinciv($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_NCIVI_V01.* FROM BTA_NCIVI_V01";
        $where = 'WHERE';
        $this->betweenValidateDate($filtri, $where, $sqlParams);
        $sql .= $excludeOrderBy ? '' : ' ORDER BY ORDER BY DESVIA, NUMCIV, SUBNCIV';
        return $sql;
    }

    private function betweenValidateDate($filtri, &$where, &$sqlParams) {
        if ($filtri['DALLADATA'] != null && $filtri['ALLADATA'] != null) {
            $this->addSqlParam($sqlParams, "DALLADATA", $filtri['DALLADATA'], PDO::PARAM_STR);
            $this->addSqlParam($sqlParams, "ALLADATA", $filtri['ALLADATA'], PDO::PARAM_STR);
            $sql .= " $where (DATAINIZ BETWEEN :DALLADATA AND :ALLADATA )" . " OR " . "(DATAFINE BETWEEN :DALLADATA AND :ALLADATA )";
        }
        if ($filtri['DALLADATA'] && $filtri['ALLADATA'] == null) {
            $this->addSqlParam($sqlParams, "DALLADATA", $filtri['DALLADATA'], PDO::PARAM_STR);
            $sql .= " $where (DATAINIZ >=:DALLADATA OR DATAFINE >=DALLADATA)";
        }
        if ($filtri['DALLADATA'] == null && $filtri['ALLADATA']) {
            $this->addSqlParam($sqlParams, "ALLADATA", $filtri['ALLADATA'], PDO::PARAM_STR);
            $sql .= " $where (DATAINIZ <=:ALLADATA OR DATAFINE <=:ALLADATA)";
        }
    }

// BTA_NOTE

    /**
     * Restituisce comando sql per lettura tabella BTA_NOTE
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNote($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT note.*, ntnote.TABLENOTE, ntnote.IDGRNOTE FROM BTA_NOTE note "
                . " LEFT JOIN BTA_NTNOTE ntnote ON note.NATURANOTA = ntnote.NATURANOTA AND ntnote.TABLENOTE = 'BTA_NOTE' ";
        $where = 'WHERE';
        if (array_key_exists('PROGNOTE', $filtri) && $filtri['PROGNOTE'] != null) {
            $this->addSqlParam($sqlParams, "PROGNOTE", $filtri['PROGNOTE'], PDO::PARAM_INT);
            $sql .= " $where PROGNOTE =:PROGNOTE";
            $where = 'AND';
        }
        if (array_key_exists('RIGANOTA', $filtri) && $filtri['RIGANOTA'] != null) {
            $this->addSqlParam($sqlParams, "RIGANOTA", $filtri['RIGANOTA'], PDO::PARAM_INT);
            $sql .= " $where RIGANOTA = :RIGANOTA";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGNOTE';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_NOTE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaNote($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNote($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBtaNoteComponent($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT
                    BTA_NOTE.*,
                    BTA_SOGG.PROGSOGG
                FROM BTA_NOTE
                LEFT JOIN BTA_SOGG ON BTA_NOTE.PROGNOTE = BTA_SOGG.PROGNOTE";
        $where = 'WHERE';

        if (!empty($filtri['PROGSOGG'])) {
            $this->addSqlParam($sqlParams, 'PROGSOGG', $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where BTA_SOGG.PROGSOGG=:PROGSOGG";
            $where = 'AND';
        }
        if (!empty($filtri['PROGNOTE'])) {
            $this->addSqlParam($sqlParams, 'PROGNOTE', $filtri['PROGNOTE'], PDO::PARAM_INT);
            $sql .= " $where BTA_NOTE.PROGNOTE=:PROGNOTE";
            $where = 'AND';
        }
        if (!empty($filtri['RIGANOTA'])) {
            $this->addSqlParam($sqlParams, 'RIGANOTA', $filtri['RIGANOTA'], PDO::PARAM_INT);
            $sql .= " $where BTA_NOTE.RIGANOTA=:RIGANOTA";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY BTA_NOTE.PROGNOTE, BTA_NOTE.RIGANOTA';
        return $sql;
    }

    public function leggiBtaNoteComponent($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNoteComponent($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_NOTE
     * @param array $prognote Chiave 
     * @param array $riganota Chiave 
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNoteChiave($prognote, $riganota, &$sqlParams = array()) {
        $filtri = array();
        $filtri['PROGNOTE'] = $prognote;
        $filtri['RIGANOTA'] = $riganota;
        return self::getSqlLeggiBtaNote($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_NOTE per chiave
     * @param array $prognote Chiave 
     * @param array $riganota Chiave  
     * @return object Record
     */
    public function leggiBtaNoteChiave($prognote, $riganota, $sqlParams) {
        if (!$prognote || !$riganota) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNoteChiave($prognote, $riganota, $sqlParams), FALSE, $sqlParams);
    }

    /**
     * Cancella BTA_NOTE per prognote e not in(righe)
     * @param String $prognote Chiave   
     * @param array $righe righe da non cancellare   
     * @return object Record
     */
    public function deleteBtaNote($prognote, $righe) {
        if (!$prognote) {
            return null;
        }
        $sql = "DELETE FROM BTA_NOTE WHERE PROGNOTE =:PROGNOTE";
        $this->addSqlParam($sqlParams, "PROGNOTE", $prognote, PDO::PARAM_INT);
        if ($righe) {
            $sql = $sql . " AND RIGANOTA NOT IN(" . implode(", ", $righe) . ")";
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $sql, FALSE, $sqlParams);
    }

// BTA_NTNOTE

    /**
     * Restituisce comando sql per lettura tabella BTA_NTNOTE
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNtnote($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_NTNOTE.*, "
                . $this->getCitywareDB()->strConcat('TABLENOTE', "'|'", 'NATURANOTA') . " AS \"ROW_ID\" "
                . " FROM BTA_NTNOTE";
        $where = 'WHERE';

        if (array_key_exists('IDGRNOTE', $filtri) && $filtri['IDGRNOTE'] != null) {
            $this->addSqlParam($sqlParams, "IDGRNOTE", $filtri['IDGRNOTE'], PDO::PARAM_INT);
            $sql .= " $where IDGRNOTE = :IDGRNOTE";
            $where = 'AND';
        }
        if (array_key_exists('TABLENOTE', $filtri) && $filtri['TABLENOTE'] != null) {
            $this->addSqlParam($sqlParams, "TABLENOTE", $filtri['TABLENOTE'], PDO::PARAM_STR);
            $sql .= " $where TABLENOTE = :TABLENOTE";
            $where = 'AND';
        }
        if (array_key_exists('NATURANOTA', $filtri) && $filtri['NATURANOTA'] != null) {
            $this->addSqlParam($sqlParams, "NATURANOTA", $filtri['NATURANOTA'], PDO::PARAM_STR);
            $sql .= " $where NATURANOTA = :NATURANOTA";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] != null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri["FLAG_DIS"], PDO::PARAM_BOOL);
            $sql .= " $where FLAG_DIS = :FLAG_DIS";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY TABLENOTE';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_NTNOTE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaNtnote($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNtnote($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_NTNOTE
     * @param string $table nome tabella note 
     * @param string $natura natura nota  
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNtnoteChiave($table, $natura, &$sqlParams = array()) {
        $filtri = array();
        $filtri['TABLENOTE'] = $table;
        $filtri['NATURANOTA'] = $natura;
        return self::getSqlLeggiBtaNtnote($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_NTNOTE per chiave
     * @param string $table nome tabella note 
     * @param string $natura natura nota   
     * @return object Record
     */
    public function leggiBtaNtnoteChiave($table, $natura) {
        if (!$table || !$natura) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNtnoteChiave($table, $natura, $sqlParams), false, $sqlParams);
    }

// BTA_ELEME

    /**
     * Restituisce comando sql per lettura tabella BTA_ELEME
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaEleme($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_ELEME.* FROM BTA_ELEME";
        $where = 'WHERE';
        if (array_key_exists('CODELEMEN', $filtri) && $filtri['CODELEMEN'] != null) {
            $this->addSqlParam($sqlParams, "CODELEMEN", $filtri['CODELEMEN'], PDO::PARAM_INT);
            $sql .= " $where CODELEMEN = :CODELEMEN";
            $where = 'AND';
        }
        if (array_key_exists('DESELEMEN', $filtri) && $filtri['DESELEMEN'] != null) {
            $this->addSqlParam($sqlParams, "DESELEMEN", strtoupper(trim($filtri['DESELEMEN'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESELEMEN") . " LIKE :DESELEMEN";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODELEMEN, DESELEMEN';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_ELEME
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaEleme($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaEleme($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_ELEME
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaElemeChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['CODELEMEN'] = $cod;
        return self::getSqlLeggiBtaEleme($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_ELEME per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaElemeChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaElemeChiave($cod, $sqlParams), FALSE, $sqlParams);
    }

// BTA_VIAVOC

    /**
     * Restituisce comando sql per lettura tabella BTA_VIAVOC
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaViavoc($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_VIAVOC_V01.* FROM BTA_VIAVOC_V01";
        $where = 'WHERE';
        if (array_key_exists('PROGVIAVOC', $filtri) && $filtri['PROGVIAVOC'] != null) {
            $this->addSqlParam($sqlParams, "PROGVIAVOC", $filtri['PROGVIAVOC'], PDO::PARAM_INT);
            $sql .= " $where PROGVIAVOC = :PROGVIAVOC";
            $where = 'AND';
        }
        if (array_key_exists('CODELEMEN', $filtri) && $filtri['CODELEMEN'] != null) {
            $this->addSqlParam($sqlParams, "CODELEMEN", $filtri['CODELEMEN'], PDO::PARAM_INT);
            $sql .= " $where CODELEMEN = :CODELEMEN";
            $where = 'AND';
        }
        if (array_key_exists('CODVIA_DA', $filtri) && $filtri['CODVIA_A'] != null) {
            $this->addSqlParam($sqlParams, "CODVIA_DA", $filtri['CODVIA_DA'], PDO::PARAM_STR);
            $this->addSqlParam($sqlParams, "CODVIA_A", $filtri['CODVIA_A'], PDO::PARAM_STR);
            $sql .= " $where(CODVIA BETWEEN :CODVIA_DA AND :CODVIA_A)";
            $where = 'AND';
        }
        if (array_key_exists('CODVOCEL', $filtri) && $filtri['CODVOCEL'] != null) {
            $this->addSqlParam($sqlParams, "CODVOCEL", $filtri['CODVOCEL'], PDO::PARAM_INT);
            $sql .= " $where CODVOCEL = :CODVOCEL";
            $where = 'AND';
        }
        if (array_key_exists('CODVIA', $filtri) && $filtri['CODVIA'] != null) {
            $this->addSqlParam($sqlParams, "CODVIA", $filtri['CODVIA'], PDO::PARAM_INT);
            $sql .= " $where CODVIA = :CODVIA";
            $where = 'AND';
        }
        if (array_key_exists('DESVIA', $filtri) && $filtri['DESVIA'] != null) {
            $this->addSqlParam($sqlParams, "DESVIA", "%" . strtoupper(trim($filtri['DESVIA'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESVIA") . " LIKE :DESVIA";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODVIA, NUMCIV, CODVOCEL';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_VIAVOC per controllo civici
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaViavocContrCivici($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaViavocContrCivici($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BTA_VIAVOC per controllo civici
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaViavocContrCivici($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_VIAVOC.* FROM BTA_VIAVOC";
        $where = 'WHERE';
        if (array_key_exists('CODELEMEN', $filtri) && $filtri['CODELEMEN'] != null) {
            $this->addSqlParam($sqlParams, "CODELEMEN", $filtri["CODELEMEN"], PDO::PARAM_INT);
            $sql .= " $where CODELEMEN = :CODELEMEN";
            $where = 'AND';
        }
        if (array_key_exists('CODVIA', $filtri) && $filtri['CODVIA'] != null) {
            $this->addSqlParam($sqlParams, "CODVIA", $filtri["CODVIA"], PDO::PARAM_INT);
            $sql .= " $where CODVIA =:CODVIA";
            $where = 'AND';
        }
        if (array_key_exists('PROGVIAVOC', $filtri) && $filtri['PROGVIAVOC'] != null) {
            $this->addSqlParam($sqlParams, "PROGVIAVOC", $filtri["PROGVIAVOC"], PDO::PARAM_INT);
            $sql .= " $where PROGVIAVOC <>:PROGVIAVOC";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_VIAVOC
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaViavoc($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaViavoc($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_VIAVOC
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaViavocChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['PROGVIAVOC'] = $cod;
        return self::getSqlLeggiBtaViavoc($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_VIAVOC per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaViavocChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaViavocChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * 
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaViavocVoci($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT DISTINCT OV.PARI_DISP,OV.NUMCIV,OV.SUBNCIV,OV.NUMCIV_F, OV.SUBNCIV_F,CI.CODVOCEL,CI.DESVOCEEL 
                FROM BTA_VIAVOC OV left join BTA_VOCI CI on OV.CODELEMEN=CI.CODELEMEN AND OV.CODVOCEL = CI.CODVOCEL";
        $where = 'WHERE';
        if (array_key_exists('CODELEMEN', $filtri) && $filtri['CODELEMEN'] != null) {
            $this->addSqlParam($sqlParams, "CODELEMEN", $filtri["CODELEMEN"], PDO::PARAM_INT);
            $sql .= " $where OV.CODELEMEN = :CODELEMEN";
            $where = 'AND';
        }
        if (array_key_exists('CODVIA', $filtri) && $filtri['CODVIA'] != null) {
            $this->addSqlParam($sqlParams, "CODVIA", $filtri["CODVIA"], PDO::PARAM_INT);
            $sql .= " $where OV.CODVIA =:CODVIA";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODVOCEL';

        return $sql;
    }

    /*
     * ricerca via per elemento
     */

    public function leggiBtaViavocElement($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaViavocElement($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /* Seleziono la suddivisione della via relativa all'elemento.
     * Filtri obbligatori
     * CODELEMEN,codvia,data,pari_disp
     */

    public function getSqlLeggiBtaViavocElement($filtri, $excludeOrderBy = false, &$sqlParams = array()) {

        include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

        $this->addSqlParam($sqlParams, "CODELEMEN", $filtri["CODELEMEN"], PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "CODVIA", $filtri["CODVIA"], PDO::PARAM_INT);
//$this->addSqlParam($sqlParams, "DATA", $this->getCitywareDB()->formatDate($filtri["DATA"]), PDO::PARAM_STR);
        $this->addSqlParam($sqlParams, "DATA", cwbLibCalcoli::dataInvertita($filtri["DATA"]), PDO::PARAM_STR);
        $this->addSqlParam($sqlParams, "PARI_DISP", $filtri["PARI_DISP"], PDO::PARAM_STR);
        $this->addSqlParam($sqlParams, "NUMCIV", $filtri["NUMCIV"], PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "SUBNCIV", $filtri["SUBNCIV"], PDO::PARAM_STR);

        $loc_sql .= 'SELECT vv.* FROM BTA_VIAVOC vv';
        $loc_sql .= ' WHERE  vv.CODELEMEN=:CODELEMEN and ';
        $loc_sql .= ' vv.codvia = :CODVIA AND  ';
        $loc_sql .= " (vv.DATAINIZ IS NULL OR vv.DATAINIZ<=:DATA) AND ";
        $loc_sql .= " (vv.DATAFINE IS NULL OR vv.DATAFINE>=:DATA) AND ";
        $loc_sql .= " (vv.pari_disp = 'T' OR ";
        $loc_sql .= '';
        $loc_sql .= " ( vv.pari_disp = :PARI_DISP AND ";
        $loc_sql .= '';
        $loc_sql .= ' (( :NUMCIV  > vv.numciv AND :NUMCIV < vv.numciv_f) OR ';
        $loc_sql .= '';
        $loc_sql .= ' (:NUMCIV = vv.numciv AND :SUBNCIV < vv.subnciv_f ';
        $loc_sql .= " AND (vv.f_subnciv = 0 OR :SUBNCIV >= vv.subnciv) ) OR ";
        $loc_sql .= '';
        $loc_sql .= ' (:NUMCIV = vv.numciv AND :SUBNCIV = vv.subnciv_f ';
        $loc_sql .= " AND (vv.f_subnciv = 0 OR :SUBNCIV >= vv.subnciv) ";
        $loc_sql .= " AND (vv.f_subncivf = 0 OR :SUBNCIV <= vv.subnciv_f ) ) OR ";
        $loc_sql .= '';
        $loc_sql .= ' (:NUMCIV > vv.numciv AND :SUBNCIV = vv.subnciv_f ';
        $loc_sql .= " AND (vv.f_subncivf = 0 OR :SUBNCIV <= vv.subnciv_f) ) ) ) ) ";
        $loc_sql .= '';
        $loc_sql .= ' and vv.CODELEMEN=:CODELEMEN';
        return $loc_sql;
    }

// BTA_VOCI

    /**
     * Restituisce comando sql per lettura tabella BTA_VOCI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaVoci($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_VOCI_V01.*, "
                . $this->getCitywareDB()->strConcat('CODELEMEN', "'|'", 'CODVOCEL') . " AS \"ROW_ID\" "
                . " FROM BTA_VOCI_V01";
        $where = 'WHERE';
        if (array_key_exists('CODELEMEN', $filtri) && $filtri['CODELEMEN'] != null) {
            $this->addSqlParam($sqlParams, "CODELEMEN", $filtri["CODELEMEN"], PDO::PARAM_INT);
            $sql .= " $where CODELEMEN = :CODELEMEN";
            $where = 'AND';
        }
        if (array_key_exists('CODVOCEL', $filtri) && $filtri['CODVOCEL'] != null) {
            $this->addSqlParam($sqlParams, "CODVOCEL", $filtri["CODVOCEL"], PDO::PARAM_INT);
            $sql .= " $where CODVOCEL = :CODVOCEL";
            $where = 'AND';
        }

        if (array_key_exists('DESVOCEEL', $filtri) && $filtri['DESVOCEEL'] != null) {
            $this->addSqlParam($sqlParams, "DESVOCEEL", strtoupper(trim($filtri['DESVOCEEL'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESVOCEEL") . " LIKE :DESVOCEEL";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODELEMEN, CODVOCEL';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_VOCI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaVoci($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaVoci($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_VOCI
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaVociChiave($codelemen, $codvocel, &$sqlParams = array()) {
        $filtri = array();
        $filtri['CODELEMEN'] = $codelemen;
        $filtri['CODVOCEL'] = $codvocel;
        return self::getSqlLeggiBtaVoci($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_VOCI per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaVociChiave($codelemen, $codvocel) {
        if (!$codelemen || !$codvocel) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaVociChiave($codelemen, $codvocel, $sqlParams), false, $sqlParams);
    }

// BTA_NRD

    /**
     * Restituisce comando sql per lettura tabella BTA_NRD
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNrd($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_NRD.* FROM BTA_NRD";
        $where = 'WHERE';
        if (array_key_exists('COD_NR_D', $filtri) && $filtri['COD_NR_D'] != null) {
            $this->addSqlParam($sqlParams, "COD_NR_D", $filtri["COD_NR_D"], PDO::PARAM_STR);
            $sql .= " $where COD_NR_D = :COD_NR_D";
            $where = 'AND';
        }
        if (array_key_exists('COD_NR_D_like', $filtri) && $filtri['COD_NR_D_like'] != null) {
            $this->addSqlParam($sqlParams, "COD_NR_D_like", '%' . strtoupper(trim($filtri["COD_NR_D_like"])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("COD_NR_D") . " LIKE :COD_NR_D_like";
            $where = 'AND';
        }
        if (array_key_exists('DES_NR_D', $filtri) && $filtri['DES_NR_D'] != null) {
            $this->addSqlParam($sqlParams, "DES_NR_D", "%" . strtoupper(trim($filtri['DES_NR_D'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DES_NR_D") . " LIKE :DES_NR_D";
            $where = 'AND';
        }
        if (array_key_exists('COD_REGIVA', $filtri) && $filtri['COD_REGIVA'] != null) {
            $this->addSqlParam($sqlParams, "COD_REGIVA", $filtri["COD_REGIVA"], PDO::PARAM_STR);
            $sql .= " $where COD_REGIVA = :COD_REGIVA";
            $where = 'AND';
        }
        if (isSet($filtri['F_TP_NR_D'])) {
            $this->addSqlParam($sqlParams, "F_TP_NR_D", $filtri["F_TP_NR_D"], PDO::PARAM_STR);
            $sql .= " $where F_TP_NR_D = :F_TP_NR_D";
            $where = 'AND';
        }
        if (array_key_exists('F_TP_NR_D_DIVERSO', $filtri) && $filtri['F_TP_NR_D_DIVERSO'] != null) {
            $this->addSqlParam($sqlParams, "F_TP_NR_D_DIVERSO", $filtri["F_TP_NR_D_DIVERSO"], PDO::PARAM_STR);
            $sql .= " $where F_TP_NR_D <> :F_TP_NR_D_DIVERSO";
            $where = 'AND';
        }


        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] != null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri["FLAG_DIS"], PDO::PARAM_BOOL);
            $sql .= " $where FLAG_DIS = :FLAG_DIS";
            $where = 'AND';
        }
//Mostro anche i generici
        if (array_key_exists('AREA_COMP', $filtri) && $filtri['AREA_COMP'] != null && $filtri['GENERICI'] != 0) {
            $this->addSqlParam($sqlParams, "AREA_COMP", $filtri["AREA_COMP"], PDO::PARAM_INT);
            $sql .= " $where (AREA_COMP = 0 OR AREA_COMP = :AREA_COMP )";
            $where = 'AND';
        }
// Non mostro i generici
        if (array_key_exists('AREA_COMP', $filtri) && $filtri['AREA_COMP'] != null && $filtri['GENERICI'] == 0) {
            $this->addSqlParam($sqlParams, "AREA_COMP", $filtri["AREA_COMP"], PDO::PARAM_INT);
            $sql .= " $where AREA_COMP = :AREA_COMP";
            $where = 'AND';
        }

        $customFilters = array();
        if (!empty($filtri['AREA_COMP_grid'])) {
            $customFilters['AREA_COMP_arror'] = $filtri['AREA_COMP_grid'];
        }
        if (!empty($filtri['F_TP_NR_D_grid'])) {
            $customFilters['F_TP_NR_D_arror'] = $filtri['F_TP_NR_D_grid'];
        }
        if (!empty($customFilters)) {
            $sql .= " $where " . $this->setDefaultFilters('BTA_NRD', $customFilters, $sqlParams);
            $where = 'AND';
        }


        $sql .= $excludeOrderBy ? '' : ' ORDER BY COD_NR_D';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_NRD
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaNrd($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNrd($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_NRD
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNrdChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['COD_NR_D'] = $cod;
        return self::getSqlLeggiBtaNrd($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_NRD per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaNrdChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNrdChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_ALIVA

    /**
     * Restituisce comando sql per lettura tabella BTA_ALIVA
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaAliva($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_ALIVA.*, "
                . $this->getCitywareDB()->strConcat('ANNO', "'|'", 'IVAALIQ') . " AS \"ROW_ID\" "
                . " FROM BTA_ALIVA";
        $where = 'WHERE';
        if (array_key_exists('ANNO', $filtri) && $filtri['ANNO'] != null) {
            $this->addSqlParam($sqlParams, "ANNO", $filtri["ANNO"], PDO::PARAM_STR);
            $sql .= " $where ANNO = :ANNO";
            $where = 'AND';
        }
        if (array_key_exists('IVAALIQ', $filtri) && $filtri['IVAALIQ'] != null) {
            $this->addSqlParam($sqlParams, "IVAALIQ", $filtri["IVAALIQ"], PDO::PARAM_STR);
            $sql .= " $where IVAALIQ =:IVAALIQ";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] != null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS =:FLAG_DIS";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY ANNO, IVAALIQ';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_ALIVA
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaAliva($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaAliva($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_ALIVA
     * @param string $anno Anno
     * @param string $ivaaliq Aliquota�
     * @return string Comando sql
     */
    public function getSqlLeggiBtaAlivaChiave($anno, $ivaaliq, &$sqlParams = array()) {
        $filtri = array();
        $filtri['ANNO'] = $anno;
        $filtri['IVAALIQ'] = $ivaaliq;
        return self::getSqlLeggiBtaAliva($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_ALIVA per chiave
     * @param string $anno Anno
     * @param string $ivaaliq Aliquota�
     * @return object Record
     */
    public function leggiBtaAlivaChiave($anno, $ivaaliq) {
        if (!$anno || !$ivaaliq) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaAlivaChiave($anno, $ivaaliq, $sqlParams), FALSE, $sqlParams);
    }

// BTA_NRD_AN

    /**
     * Restituisce comando sql per lettura tabella BTA_NRD_AN
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNrdAn($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_NRD_AN_V01.*, "
                . $this->getCitywareDB()->strConcat('ANNOEMI', "'|'", 'COD_NR_D', "'|'", 'SETT_IVA') . " AS \"ROW_ID\" "
                . " FROM BTA_NRD_AN_V01";
        $where = 'WHERE';
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] != null) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " $where ANNOEMI =:ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('COD_NR_D', $filtri) && $filtri['COD_NR_D'] != null) {
            $this->addSqlParam($sqlParams, "COD_NR_D", $filtri['COD_NR_D'], PDO::PARAM_STR);
            $sql .= " $where COD_NR_D = :COD_NR_D";
            $where = 'AND';
        }
        if (array_key_exists('SETT_IVA', $filtri) && $filtri['SETT_IVA'] != null) {
            $this->addSqlParam($sqlParams, "SETT_IVA", $filtri['SETT_IVA'], PDO::PARAM_STR);
            $sql .= " $where SETT_IVA = :SETT_IVA";
            $where = 'AND';
        }
        if (array_key_exists('DES_NR_D', $filtri) && $filtri['DES_NR_D'] != null) {
            $this->addSqlParam($sqlParams, "DES_NR_D", "%" . strtoupper(trim($filtri['DES_NR_D'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DES_NR_D") . " LIKE :DES_NR_D";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY COD_NR_D, ANNOEMI';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_NRD_AN
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaNrdAn($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNrdAn($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_NRD_AN
     * @param string $annoemi Anno
     * @param string $cod_nr_d Codice�
     * @param string $sett_iva Settore IVA�
     * @return string Comando sql
     */
    public function getSqlLeggiBtaNrdAnChiave($annoemi, $cod_nr_d, $sett_iva, &$sqlParams = array()) {
        $filtri = array();
        $filtri['ANNOEMI'] = $annoemi;
        $filtri['COD_NR_D'] = $cod_nr_d;
        $filtri['SETT_IVA'] = $sett_iva;
        return self::getSqlLeggiBtaNrdAn($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_NRD_AN per chiave
     * @param string $annoemi Anno
     * @param string $cod_nr_d Codice�
     * @param string $sett_iva Settore IVA�
     */
    public function leggiBtaNrdAnChiave($annoemi, $cod_nr_d, $sett_iva) {
        if (!$annoemi || !$cod_nr_d || !$sett_iva) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaNrdAnChiave($annoemi, $cod_nr_d, $sett_iva, $sqlParams), false, $sqlParams);
    }

// BTA_ARROT

    /**
     * Restituisce comando sql per lettura tabella BTA_ARROT
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaArrot($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_ARROT.* FROM BTA_ARROT";
        $where = 'WHERE';
        if (array_key_exists('CODARROT', $filtri) && $filtri['CODARROT'] !== null) {
            $this->addSqlParam($sqlParams, "CODARROT", $filtri['CODARROT'], PDO::PARAM_INT);
            $sql .= " $where CODARROT = :CODARROT";
            $where = 'AND';
        }
        if (array_key_exists('DES_GE60', $filtri) && $filtri['DES_GE60'] != null) {
            $this->addSqlParam($sqlParams, "DES_GE60", "%" . strtoupper(trim($filtri['DES_GE60'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DES_GE60") . " LIKE :DES_GE60";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] != null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS =:FLAG_DIS";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODARROT';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_ARROT
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaArrot($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaArrot($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_ARROT
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaArrotChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['CODARROT'] = $cod;
        return self::getSqlLeggiBtaArrot($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_ARROT per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaArrotChiave($cod) {
        if ($cod === null) {
// su db c'� chiave 0
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaArrotChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_ASSIVA

    /**
     * Restituisce comando sql per lettura tabella BTA_ASSIVA
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaAssiva($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_ASSIVA.*, CODICE_SDI FROM BTA_ASSIVA LEFT JOIN FFE_CODSDI ON BTA_ASSIVA.PROGK_SDI5 = FFE_CODSDI.PROGKEYSDI";
        $where = 'WHERE';
        if (array_key_exists('IVAASSOG', $filtri) && $filtri['IVAASSOG'] != null) {
            $this->addSqlParam($sqlParams, "IVAASSOG", $filtri['IVAASSOG'], PDO::PARAM_STR);
            $sql .= " $where IVAASSOG>=:IVAASSOG";
            $where = 'AND';
        }
        if (array_key_exists('IVAASSOG_KEY', $filtri) && $filtri['IVAASSOG_KEY'] != null) {
            $this->addSqlParam($sqlParams, "IVAASSOG_KEY", $filtri['IVAASSOG_KEY'], PDO::PARAM_STR);
            $sql .= " $where IVAASSOG = :IVAASSOG_KEY";
            $where = 'AND';
        }
        if (array_key_exists('DES_ASS', $filtri) && $filtri['DES_ASS'] != null) {
            $this->addSqlParam($sqlParams, "DES_ASS", "%" . strtoupper(trim($filtri['DES_ASS'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DES_ASS") . " LIKE :DES_ASS";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY IVAASSOG';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_ASSIVA
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaAssiva($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaAssiva($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_ASSIVA
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaAssivaChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['IVAASSOG_KEY'] = $cod;
        return self::getSqlLeggiBtaAssiva($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_ASSIVA per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaAssivaChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaAssivaChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BTA_ASSIVA e basta
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaAssivaBase($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT * FROM BTA_ASSIVA ";
        $where = 'WHERE';
        if (array_key_exists('IVAASSOG', $filtri) && $filtri['IVAASSOG'] != null) {
            $this->addSqlParam($sqlParams, "IVAASSOG", $filtri['IVAASSOG'], PDO::PARAM_STR);
            $sql .= " $where IVAASSOG = :IVAASSOG";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY IVAASSOG';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_ASSIVA solo questa tabella
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaAssivaBase($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaAssivaBase($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

// BTA_OPEOTT

    /**
     * Restituisce comando sql per lettura tabella BTA_OPEOTT
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaOpeott($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_OPEOTT.* FROM BTA_OPEOTT";
        $where = 'WHERE';
        if (array_key_exists('COD_OP_OTT', $filtri) && $filtri['COD_OP_OTT'] != null) {
            $this->addSqlParam($sqlParams, "COD_OP_OTT", $filtri['COD_OP_OTT'], PDO::PARAM_INT);
            $sql .= " $where COD_OP_OTT>=:COD_OP_OTT";
            $where = 'AND';
        }
        if (array_key_exists('COD_OP_OTT_KEY', $filtri) && $filtri['COD_OP_OTT_KEY'] != null) {
            $this->addSqlParam($sqlParams, "COD_OP_OTT_KEY", $filtri['COD_OP_OTT_KEY'], PDO::PARAM_INT);
            $sql .= " $where COD_OP_OTT = :COD_OP_OTT_KEY";
            $where = 'AND';
        }
        if (array_key_exists('DES_150', $filtri) && $filtri['DES_150'] != null) {
            $this->addSqlParam($sqlParams, "DES_150", "%" . strtoupper(trim($filtri['DES_150'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DES_150") . " LIKE :DES_150";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY COD_OP_OTT';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_OPEOTT
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaOpeott($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaOpeott($cod, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_OPEOTT
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaOpeottChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['COD_OP_OTT_KEY'] = $cod;
        return self::getSqlLeggiBtaOpeott($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_OPEOTT per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaOpeottChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaOpeottChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_IPAAMM

    /**
     * Restituisce comando sql per lettura tabella BTA_IPAAMM
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaIpaamm($condizione, &$sqlParams) {
        $sql = "SELECT DISTINCT 'AMM' as IPA_TPFILE, AMM.IPA_CODAMM, AMM.IPA_DESAMM, AMM.IPA_COMUNE"
                . ", AMM.IPA_PROV, COUNT(RUB.PK) as RUBRICA, PROGRECORD"
                . " FROM BTA_IPAAMM AMM LEFT JOIN BTA_IPARUB RUB on AMM.IPA_CODAMM=RUB.IPA_CODAMM WHERE 1=1";

        $i = 0;
        foreach ($condizione as $key => $value) {
            $i++;
            $this->addSqlParam($sqlParams, 'STRING_RIC' . $i, "%" . $value . "%", PDO::PARAM_STR);

            $sql = $sql . ' ' . "AND upper(AMM.STRING_RIC) LIKE :STRING_RIC" . $i;
        }
        $sql = $sql . ' ' . "GROUP BY IPA_TIPOAM, AMM.IPA_CODAMM, AMM.IPA_DESAMM, AMM.IPA_COMUNE, AMM.IPA_PROV, AMM.PROGRECORD"
                . " UNION" . ' '
                . "SELECT DISTINCT PEC.IPA_TPFILE, PEC.IPA_CODAMM, PEC.IPA_DESPEC as IPA_DESAMM"
                . ", PEC.IPA_COMUNE, PEC.IPA_PROV, 0 as RUBRICA, PROGRECORD"
                . " FROM BTA_IPAPEC PEC"
                . " WHERE 1=1";
        $i = 0;
        foreach ($condizione as $key => $value) {
            $i++;
            $this->addSqlParam($sqlParams, 'PEC_STRING_RIC' . $i, "%" . $value . "%", PDO::PARAM_STR);

            $sql = $sql . ' ' . "AND upper(PEC.STRING_RIC) LIKE :PEC_STRING_RIC" . $i;
        }
        $sql = $sql . ' ' . "ORDER BY PROGRECORD";
        return $sql;
    }

// BTA_IPAPEC

    /**
     * Restituisce comando sql per lettura tabella BTA_IPAPEC
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaIpaPec($codAmm, $des, &$sqlParams) {
        $sql = "SELECT * FROM BTA_IPAPEC WHERE"
                . " IPA_CODAMM=:IPA_CODAMM";

        $this->addSqlParam($sqlParams, "IPA_CODAMM", $codAmm, PDO::PARAM_STR);


        if ($cod == 'AMM') {
            $sql = $sql . " UNION"
                    . " SELECT 1000000+pk AS PROGRECORD, IPA_CODAMM, IPA_UFFDES AS IPA_DESPEC, 'RUBRICA'"
                    . " AS IPA_TPFILE, ' ', note_v, ' ', IPA_NFAX, IPA_TPMAIL, DATAOPER, ' '"
                    . " FROM BTA_IPARUB_V01 WHERE IPA_CODAMM=:IPA_CODAMM2";

            $this->addSqlParam($sqlParams, "IPA_CODAMM2", $codAmm, PDO::PARAM_STR);
        }
        $this->addSqlParam($sqlParams, "IPA_DESPEC", $des, PDO::PARAM_STR);
        $sql = $sql . " AND IPA_DESPEC=:IPA_DESPEC"
                . " ORDER BY IPA_TPFILE, IPA_DESPEC";

        return sql;
    }

// BTA_EMI

    /**
     * Restituisce comando sql per lettura tabella BTA_EMI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaEmi($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_EMI.*, "
                . $this->getCitywareDB()->strConcat('IDBOL_SERE', "'|'", 'ANNOEMI', "'|'", 'NUMEMI') . " AS \"ROW_ID\" "
                . " FROM BTA_EMI";
        $where = 'WHERE';
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] != null) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " $where ANNOEMI =:ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('TIPOEMI', $filtri) && $filtri['TIPOEMI'] != null) {
            $this->addSqlParam($sqlParams, "TIPOEMI", $filtri['TIPOEMI'], PDO::PARAM_INT);
            $sql .= " $where TIPOEMI =:TIPOEMI";
            $where = 'AND';
        }
        if (array_key_exists('TIPOEMI_or', $filtri) && $filtri['TIPOEMI_or'] != null) {
            $sql .= " $where";
            $sql .= "(";
            foreach ($filtri['TIPOEMI_or'] as $key => $value) {
                $this->addSqlParam($sqlParams, "TIPOEMI_or" . $key, $filtri['TIPOEMI_or'][$key], PDO::PARAM_INT);
                $sql .= " TIPOEMI=:TIPOEMI_or" . $key . " OR";
            }
            $sql = substr($sql, 0, -2);
            $sql .= ")";
            $where = 'AND';
        }
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] != null) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", $filtri['IDBOL_SERE'], PDO::PARAM_INT);
            $sql .= " $where IDBOL_SERE = :IDBOL_SERE";
            $where = 'AND';
        }
        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] != null) {
            $this->addSqlParam($sqlParams, "NUMEMI", $filtri['NUMEMI'], PDO::PARAM_INT);
            $sql .= " $where NUMEMI = :NUMEMI";
            $where = 'AND';
        }
        if (array_key_exists('DES_GE60', $filtri) && $filtri['DES_GE60'] != null) {
            $this->addSqlParam($sqlParams, "DES_GE60", "%" . strtoupper(trim($filtri['DES_GE60'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DES_GE60") . " LIKE :DES_GE60";
            $where = 'AND';
        }
        $sql .= " $where FLAG_DIS = 0";
        $sql .= $excludeOrderBy ? '' : ' ORDER BY ANNOEMI desc, NUMEMI desc, IDBOL_SERE desc';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_EMI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaEmi($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaEmi($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_EMI
     * @param string $annoemi Anno
     * @param string $cod_nr_d Codice�
     * @param string $sett_iva Settore IVA�
     * @return string Comando sql
     */
    public function getSqlLeggiBtaEmiChiave($idbol_sere, $annoemi, $numemi, &$sqlParams = array()) {
        $filtri = array();
        $filtri['IDBOL_SERE'] = $idbol_sere;
        $filtri['ANNOEMI'] = $annoemi;
        $filtri['NUMEMI'] = $numemi;
        return self::getSqlLeggiBtaEmi($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_EMI per chiave
     * @param string $annoemi Anno
     * @param string $cod_nr_d Codice�
     * @param string $sett_iva Settore IVA�
     */
    public function leggiBtaEmiChiave($idbol_sere, $annoemi, $numemi) {
        if (!$idbol_sere || !$annoemi || !$numemi) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaEmiChiave($idbol_sere, $annoemi, $numemi, $sqlParams), false, $sqlParams);
    }

// EmissioneRendicontazioneContabile

    /**
     * Restituisce comando sql per lettura tabella BTA_SERVREND per Emissione Rendicontazione Contabile
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiEmissioneRendicontazioneContabile($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_SERVREND.*, BWE_TIPPEN.DESCRIZ,BTA_EMI.DES_GE60, "
                . $this->getCitywareDB()->strConcat('BTA_SERVREND.PROGKEYTAB', "'|'", 'BTA_SERVREND.ANNOEMI', "'|'", 'BTA_SERVREND.NUMEMI', "'|'", 'BTA_SERVREND.IDBOL_SERE') . " AS \"ROW_ID\" " // CHIAVE COMPOSTA
                . " FROM BTA_SERVREND 
            INNER JOIN BWE_TIPPEN ON BTA_SERVREND.CODTIPSCAD = BWE_TIPPEN.CODTIPSCAD AND BTA_SERVREND.SUBTIPSCAD = BWE_TIPPEN.SUBTIPSCAD
            INNER JOIN BTA_EMI ON BTA_SERVREND.IDBOL_SERE = BTA_EMI.IDBOL_SERE AND BTA_SERVREND.ANNOEMI = BTA_EMI.ANNOEMI AND
            BTA_SERVREND.NUMEMI = BTA_EMI.NUMEMI";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", strtoupper(trim($filtri['PROGKEYTAB'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != 0) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", strtoupper(trim($filtri['CODTIPSCAD'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", strtoupper(trim($filtri['SUBTIPSCAD'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] != 0) {
            $this->addSqlParam($sqlParams, "ANNOEMI", strtoupper(trim($filtri['ANNOEMI'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.ANNOEMI=:ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] != 0) {
            $this->addSqlParam($sqlParams, "NUMEMI", strtoupper(trim($filtri['NUMEMI'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.NUMEMI=:NUMEMI";
            $where = 'AND';
        }
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] != 0) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", strtoupper(trim($filtri['IDBOL_SERE'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.IDBOL_SERE=:IDBOL_SERE";
            $where = 'AND';
        }
        if (array_key_exists('DES_GE60', $filtri)) {
            $this->addSqlParam($sqlParams, 'DES_GE60', '%' . strtoupper(trim($filtri['DES_GE60'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DES_GE60") . " LIKE :DES_GE60";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY BTA_SERVREND.ANNOEMI DESC';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SERVREND
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiEmissioneRendicontazioneContabile($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaServrend($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_SERVREND
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiEmissioneRendicontazioneContabileChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGKEYTAB'] = $cod;
        return self::getSqlLeggiBtaServrend($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_SERVREND per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiEmissioneRendicontazioneContabileChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaServrendChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_SERVREND

    /**
     * Restituisce comando sql per lettura tabella BTA_SERVREND
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaServrendTabella($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_SERVREND.* FROM BTA_SERVREND ";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", strtoupper(trim($filtri['PROGKEYTAB'])), PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != 0) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", strtoupper(trim($filtri['CODTIPSCAD'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", strtoupper(trim($filtri['SUBTIPSCAD'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] != 0) {
            $this->addSqlParam($sqlParams, "ANNOEMI", strtoupper(trim($filtri['ANNOEMI'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.ANNOEMI=:ANNOEMI";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB DESC';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SERVREND
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaServrendTabella($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaServrendTabella($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BTA_SERVREND
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaServrend($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_SERVREND.*, BTA_SERVRENDPPA.INTERMEDIARIO, BTA_SERVRENDPPA.CODSERVIZIO, BTA_SERVRENDPPA.TIPORIFCRED, BWE_TIPPEN.DESCRIZ,BTA_EMI.DES_GE60, "
                . $this->getCitywareDB()->strConcat('BTA_SERVREND.PROGKEYTAB', "'|'", 'BTA_SERVREND.ANNOEMI', "'|'", 'BTA_SERVREND.NUMEMI', "'|'", 'BTA_SERVREND.IDBOL_SERE') . " AS \"ROW_ID\" " // CHIAVE COMPOSTA
                . " FROM BTA_SERVREND INNER JOIN BTA_SERVRENDPPA ON BTA_SERVREND.PROGKEYTAB = BTA_SERVRENDPPA.IDSERVREND
            INNER JOIN BWE_TIPPEN ON BTA_SERVREND.CODTIPSCAD = BWE_TIPPEN.CODTIPSCAD AND BTA_SERVREND.SUBTIPSCAD = BWE_TIPPEN.SUBTIPSCAD
            INNER JOIN BTA_EMI ON BTA_SERVREND.IDBOL_SERE = BTA_EMI.IDBOL_SERE AND BTA_SERVREND.ANNOEMI = BTA_EMI.ANNOEMI AND
            BTA_SERVREND.NUMEMI = BTA_EMI.NUMEMI";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", strtoupper(trim($filtri['PROGKEYTAB'])), PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != 0) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", strtoupper(trim($filtri['CODTIPSCAD'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", strtoupper(trim($filtri['SUBTIPSCAD'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] != 0) {
            $this->addSqlParam($sqlParams, "ANNOEMI", strtoupper(trim($filtri['ANNOEMI'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.ANNOEMI=:ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] != 0) {
            $this->addSqlParam($sqlParams, "NUMEMI", strtoupper(trim($filtri['NUMEMI'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.NUMEMI=:NUMEMI";
            $where = 'AND';
        }
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] != 0) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", strtoupper(trim($filtri['IDBOL_SERE'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.IDBOL_SERE=:IDBOL_SERE";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY BTA_SERVREND.PROGKEYTAB DESC';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SERVREND
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaServrend($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaServrend($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_SERVREND
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaServrendChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGKEYTAB'] = $cod;
        return self::getSqlLeggiBtaServrend($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_SERVREND per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaServrendChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaServrendChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_SERVREND
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaServrendRendicontazioneChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGKEYTAB'] = $cod;
        return self::getSqlLeggiEmissioneRendicontazioneContabile($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_SERVREND per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaServrendRendicontazioneChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaServrendRendicontazioneChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_SERVREND

    /**
     * Restituisce comando sql per lettura tabella BTA_SERVREND
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaServrendAccertamentiEmissione($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "select distinct bta_servrenddet.* from bge_agid_scadenze
                INNER JOIN BTA_SERVREND ON BTA_SERVREND.CODTIPSCAD=BGE_AGID_SCADENZE.CODTIPSCAD AND 
                BTA_SERVREND.SUBTIPSCAD=BGE_AGID_SCADENZE.SUBTIPSCAD AND 
                BTA_SERVREND.ANNOEMI=BGE_AGID_SCADENZE.ANNOEMI AND BTA_SERVREND.NUMEMI=BGE_AGID_SCADENZE.NUMEMI AND 
                BTA_SERVREND.IDBOL_SERE=BGE_AGID_SCADENZE.IDBOL_SERE 
                INNER JOIN bta_servrenddet ON BTA_SERVREND.PROGKEYTAB = BTA_SERVRENDDET.IDSERVREND";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", strtoupper(trim($filtri['PROGKEYTAB'])), PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_SCADENZE.PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] != null) {
            $this->addSqlParam($sqlParams, "ANNOEMI", strtoupper(trim($filtri['ANNOEMI'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.ANNOEMI=:ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] != null) {
            $this->addSqlParam($sqlParams, "NUMEMI", strtoupper(trim($filtri['NUMEMI'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.NUMEMI=:NUMEMI";
            $where = 'AND';
        }
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] != null) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", strtoupper(trim($filtri['IDBOL_SERE'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.IDBOL_SERE=:IDBOL_SERE";
            $where = 'AND';
        }
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", strtoupper(trim($filtri['CODTIPSCAD'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", strtoupper(trim($filtri['SUBTIPSCAD'])), PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY bta_servrenddet.PROGKEYTAB';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SERVREND
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaServrendAccertamentiEmissione($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaServrendAccertamentiEmissione($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

// BTA_SERVRENDPPA

    /**
     * Restituisce comando sql per lettura tabella BTA_SERVRENDPPA
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaServrendppa($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_SERVRENDPPA.* FROM BTA_SERVRENDPPA";
        $where = 'WHERE';
        if (array_key_exists('IDSERVREND', $filtri) && $filtri['IDSERVREND'] != null) {
            $this->addSqlParam($sqlParams, "IDSERVREND", strtoupper(trim($filtri['IDSERVREND'])), PDO::PARAM_INT);
            $sql .= " $where IDSERVREND=:IDSERVREND";
            $where = 'AND';
        }
        if (array_key_exists('INTERMEDIARIO', $filtri) && $filtri['INTERMEDIARIO'] != null) {
            $this->addSqlParam($sqlParams, "INTERMEDIARIO", $filtri['INTERMEDIARIO'], PDO::PARAM_INT);
            $sql .= " $where INTERMEDIARIO=:INTERMEDIARIO";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDSERVREND';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SERVRENDPPA
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaServrendppa($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaServrendppa($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

// BTA_SERVRENDPPA

    /**
     * Restituisce comando sql per lettura tabella BTA_SERVRENDPPA
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaServrendppaServizio($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT DISTINCT CODSERVIZIO FROM BTA_SERVRENDPPA WHERE CODSERVIZIO>'0' OR CODSERVIZIO <>' '";
        $sql .= ' ORDER BY CODSERVIZIO';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SERVRENDPPA
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaServrendppaServizio($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaServrendppaServizio($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_SERVRENDPPA
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaServrendppaChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['IDSERVREND'] = $cod;
        return self::getSqlLeggiBtaServrendppa($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_SERVRENDPPA per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaServrendppaChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaServrendppaChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_SERVRENDDET

    /**
     * Restituisce comando sql per lettura tabella BTA_SERVRENDDET
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaServrenddet($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_SERVRENDDET.* FROM BTA_SERVRENDDET";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", strtoupper(trim($filtri['PROGKEYTAB'])), PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('IDSERVREND', $filtri) && $filtri['IDSERVREND'] != null) {
            $this->addSqlParam($sqlParams, "IDSERVREND", strtoupper(trim($filtri['IDSERVREND'])), PDO::PARAM_INT);
            $sql .= " $where IDSERVREND=:IDSERVREND";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SERVRENDDET
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaServrenddet($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaServrenddet($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_SERVREND
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaServrenddetChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGKEYTAB'] = $cod;
        return self::getSqlLeggiBtaServrenddet($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_SERVREND per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaServrenddetChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaServrenddetChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BTA_RENDLOG

    public function getSqlLeggiBtaServrenddetRendLogCollegato($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT DISTINCT BTA_SERVRENDDET.ANNORIF,ID_RENDSEL FROM BTA_SERVRENDDET"
                . " INNER JOIN BTA_RENDD on BTA_SERVRENDDET.PROGKEYTAB=BTA_RENDD.ID_SERVRENDDET ";

        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('IDSERVREND', $filtri) && $filtri['IDSERVREND'] != null) {
            $this->addSqlParam($sqlParams, "IDSERVREND", $filtri['IDSERVREND'], PDO::PARAM_INT);
            $sql .= " $where IDSERVREND=:IDSERVREND";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY ANNORIF,ID_RENDSEL';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_RENDLOG
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaServrenddetRendLogCollegato($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaServrenddetRendLogCollegato($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBtaRendLogDataOper($filtri, &$sqlParams = array()) {
        $sql = "SELECT MAX(DATAOPER) AS DATAOPER FROM BTA_REND_LOG ";

        $where = 'WHERE';
        if (array_key_exists('ID_MIN', $filtri) && $filtri['ID_MIN'] != null) {
            $this->addSqlParam($sqlParams, "IDMIN", $filtri['ID_MIN'], PDO::PARAM_INT);
            $sql .= " $where ID>=:IDMIN";
            $where = 'AND';
        }
        if (array_key_exists('ID_MAX', $filtri) && $filtri['ID_MAX'] != null) {
            $this->addSqlParam($sqlParams, "IDMAX", $filtri['ID_MAX'], PDO::PARAM_INT);
            $sql .= " $where ID<=:IDMAX";
            $where = 'AND';
        }

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_RENDLOG
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRendLogDataOper($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendLogDataOper($filtri, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBtaRendLogMaxDataFine($filtri, &$sqlParams = array()) {
        $sql = "SELECT MAX(DATAFINE) AS DATAFINE FROM BTA_REND_LOG ";

        return $sql;
    }

    /**
     * Restituisce la datafine massima della tabella BTA_RENDLOG
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRendLogMaxDataFine($filtri, $multipla = false) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendLogMaxDataFine($filtri, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBtaRendLogMaxIdConvalidato($filtri, &$sqlParams = array()) {
        $sql = "SELECT MAX(ID) FROM BTA_REND_LOG WHERE CONVALIDA = :CONVALIDA AND DATAFINE <= :DATAFINE ";
        $this->addSqlParam($sqlParams, "CONVALIDA", 1, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "DATAFINE", $filtri['DATAFINE'], PDO::PARAM_STR);

        return $sql;
    }

    /**
     * Restituisce la datafine massima della tabella BTA_RENDLOG
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRendLogMaxIdConvalidato($filtri, $multipla = false) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendLogMaxIdConvalidato($filtri, $sqlParams), $multipla, $sqlParams);
    }

    // TROVA INTERMEDIARIO PAGOPA CON CODTIPSCAD, SUBTIPSCAD, ANNOEMI, NUMEMI, IDBOL_SERE

    /**
     * Restituisce comando sql per lettura tabella bta_servrend per capire l'intermediario che sto trattando
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiGetIntermediario($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();
        $sql = "select distinct intermediario,codservizio, tiporifcred from bta_servrend
            inner join bta_servrendppa on bta_servrend.progkeytab=bta_servrendppa.idservrend";
        $where = 'WHERE';
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", $filtri['CODTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] !== null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", $filtri['SUBTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] !== null) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " $where ANNOEMI=:ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] !== null) {
            $this->addSqlParam($sqlParams, "NUMEMI", $filtri['NUMEMI'], PDO::PARAM_INT);
            $sql .= " $where NUMEMI=:NUMEMI";
            $where = 'AND';
        }
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] !== null) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", $filtri['IDBOL_SERE'], PDO::PARAM_INT);
            $sql .= " $where IDBOL_SERE=:IDBOL_SERE";
            $where = 'AND';
        }
        if (array_key_exists('INTERMEDIARIO', $filtri) && $filtri['INTERMEDIARIO'] !== null) {
            $this->addSqlParam($sqlParams, "INTERMEDIARIO", $filtri['INTERMEDIARIO'], PDO::PARAM_INT);
            $sql .= " $where INTERMEDIARIO=:INTERMEDIARIO";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SERVREND per capire l'intermediario che sto trattando
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiGetIntermediario($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiGetIntermediario($filtri, false, $sqlParams), true, $sqlParams);
    }

// TROVA INTERMEDIARIO PAGOPA CON CODTIPSCAD, SUBTIPSCAD, ANNORIF, PROGCITYSC

    /**
     * Restituisce comando sql per lettura tabella bta_servrend per capire l'intermediario che sto trattando
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiGetCodiceIntermediarioDaEmissione($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();
        $sql = "select distinct intermediario,codservizio, tiporifcred from bta_servrend
            inner join bta_servrendppa on bta_servrend.progkeytab=bta_servrendppa.idservrend
            inner join bwe_penden on bwe_penden.codtipscad = bta_servrend.codtipscad and bwe_penden.subtipscad = bta_servrend.subtipscad
            and bwe_penden.annoemi = bta_servrend.annoemi and bwe_penden.numemi = bta_servrend.numemi 
            and bwe_penden.idbol_sere = bta_servrend.idbol_sere";
        $where = 'WHERE';
        if (array_key_exists('ANNORIF', $filtri) && $filtri['ANNORIF'] != null) {
            $this->addSqlParam($sqlParams, "ANNORIF", $filtri['ANNORIF'], PDO::PARAM_INT);
            $sql .= " $where bwe_penden.ANNORIF=:ANNORIF";
            $where = 'AND';
        }
        if (array_key_exists('PROGCITYSC', $filtri) && $filtri['PROGCITYSC'] != null) {
            $this->addSqlParam($sqlParams, "PROGCITYSC", $filtri['PROGCITYSC'], PDO::PARAM_INT);
            $sql .= " $where bwe_penden.PROGCITYSC=:PROGCITYSC";
            $where = 'AND';
        }
        if (array_key_exists('PROGCITYSCA', $filtri) && $filtri['PROGCITYSCA'] != null) {
            $this->addSqlParam($sqlParams, "PROGCITYSCA", $filtri['PROGCITYSCA'], PDO::PARAM_INT);
            $sql .= " $where bwe_penden.PROGCITYSCA=:PROGCITYSCA";
            $where = 'AND';
        }
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", $filtri['CODTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where bwe_penden.CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", $filtri['SUBTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where bwe_penden.SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SERVREND per capire l'intermediario che sto trattando
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiGetCodiceIntermediarioDaEmissione($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiGetCodiceIntermediarioDaEmissione($filtri, false, $sqlParams), false, $sqlParams);
    }

// TROVA INTERMEDIARIO PAGOPA TRAMITE IDRUOLO

    /**
     * Restituisce comando sql per lettura tabella BTA_SERVREND per capire l'intermediario che sto trattando
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiIntermediarioDaIdRuolo($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();
        $sql = "select BTA_SERVREND.*,bta_servrendppa.* from bta_servrend 
                INNER JOIN BTA_SERVRENDPPA ON BTA_SERVREND.PROGKEYTAB = BTA_SERVRENDPPA.IDSERVREND
                INNER JOIN BGE_AGID_SCADENZE ON BGE_AGID_SCADENZE.CODTIPSCAD=BTA_SERVREND.CODTIPSCAD 
                AND BGE_AGID_SCADENZE.SUBTIPSCAD=BTA_SERVREND.SUBTIPSCAD AND BGE_AGID_SCADENZE.ANNOEMI=BTA_SERVREND.ANNOEMI 
                AND BGE_AGID_SCADENZE.NUMEMI=BTA_SERVREND.NUMEMI AND BGE_AGID_SCADENZE.IDBOL_SERE=BTA_SERVREND.IDBOL_SERE
                INNER JOIN BGE_AGID_SCA_NSS ON BGE_AGID_SCA_NSS.PROGKEYTAB = BGE_AGID_SCADENZE.PROGKEYTAB";
        $where = 'WHERE';
        if (array_key_exists('IDRUOLO', $filtri) && $filtri['IDRUOLO'] != null) {
            $this->addSqlParam($sqlParams, "IDRUOLO", $filtri['IDRUOLO'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_SCA_NSS.IDRUOLO=:IDRUOLO";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SERVREND per capire l'intermediario che sto trattando
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiGetIntermediarioDaIdRuolo($filtri) {
        $res = ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiIntermediarioDaIdRuolo($filtri, false, $sqlParams), false, $sqlParams);
        if ($res) {
            return $res['INTERMEDIARIO'];
        }

        return false;
    }

// TROVA INTERMEDIARIO PAGOPA TRAMITE PROGCITYSC

    /**
     * Restituisce comando sql per lettura tabella BTA_SERVREND per capire l'intermediario che sto trattando
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiIntermediarioDaRifChiaveEsterna($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();
        $sql = "select BTA_SERVREND.*,bta_servrendppa.* from bta_servrend 
                INNER JOIN BTA_SERVRENDPPA ON BTA_SERVREND.PROGKEYTAB = BTA_SERVRENDPPA.IDSERVREND
                INNER JOIN BGE_AGID_SCADENZE ON BGE_AGID_SCADENZE.CODTIPSCAD=BTA_SERVREND.CODTIPSCAD 
                AND BGE_AGID_SCADENZE.SUBTIPSCAD=BTA_SERVREND.SUBTIPSCAD AND BGE_AGID_SCADENZE.ANNOEMI=BTA_SERVREND.ANNOEMI 
                AND BGE_AGID_SCADENZE.NUMEMI=BTA_SERVREND.NUMEMI AND BGE_AGID_SCADENZE.IDBOL_SERE=BTA_SERVREND.IDBOL_SERE";
        $where = 'WHERE';
        if (array_key_exists('PROGCITYSC', $filtri) && $filtri['PROGCITYSC'] != null) {
            $this->addSqlParam($sqlParams, "PROGCITYSC", $filtri['PROGCITYSC'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_SCADENZE.PROGCITYSC=:PROGCITYSC";
            $where = 'AND';
        }
        if (array_key_exists('CODRIFERIMENTO', $filtri) && $filtri['CODRIFERIMENTO'] != null) {
            $this->addSqlParam($sqlParams, "CODRIFERIMENTO", $filtri['CODRIFERIMENTO'], PDO::PARAM_STR);
            $sql .= " $where BGE_AGID_SCADENZE.CODRIFERIMENTO=:CODRIFERIMENTO";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SERVREND per capire l'intermediario che sto trattando
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiGetIntermediarioDaRifChiaveEsterna($filtri) {
        $res = ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiIntermediarioDaRifChiaveEsterna($filtri, false, $sqlParams), false, $sqlParams);
        if ($res) {
            return $res['INTERMEDIARIO'];
        }

        return false;
    }

// TROVA INTERMEDIARIO PAGOPA TRAMITE CODICE IUV

    /**
     * Restituisce comando sql per lettura tabella BTA_SERVREND per capire l'intermediario che sto trattando
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiGetIntermediarioDaIUV($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();
        $sql = "select BTA_SERVREND.*,bta_servrendppa.* from bta_servrend 
                INNER JOIN BTA_SERVRENDPPA ON BTA_SERVREND.PROGKEYTAB = BTA_SERVRENDPPA.IDSERVREND
                INNER JOIN BGE_AGID_SCADENZE ON BGE_AGID_SCADENZE.CODTIPSCAD=BTA_SERVREND.CODTIPSCAD 
                AND BGE_AGID_SCADENZE.SUBTIPSCAD=BTA_SERVREND.SUBTIPSCAD AND BGE_AGID_SCADENZE.ANNOEMI=BTA_SERVREND.ANNOEMI 
                AND BGE_AGID_SCADENZE.NUMEMI=BTA_SERVREND.NUMEMI AND BGE_AGID_SCADENZE.IDBOL_SERE=BTA_SERVREND.IDBOL_SERE";
        $where = 'WHERE';
        if (array_key_exists('IUV', $filtri) && $filtri['IUV'] != null) {
            $this->addSqlParam($sqlParams, "IUV", $filtri['IUV'], PDO::PARAM_STR);
            $sql .= " $where IUV=:IUV";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SERVREND per capire l'intermediario che sto trattando
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiGetIntermediarioDaIUV($filtri) {
        $res = ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiGetIntermediarioDaIUV($filtri, false, $sqlParams), false, $sqlParams);
        if ($res) {
            return $res['INTERMEDIARIO'];
        }

        return false;
    }

    /**
     * Restituisce dati tabella BGE_AGID_TIPINT per capire l'intermediario che sto trattando
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiInfoGetIntermediarioDaIUV($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiGetIntermediarioDaIUV($filtri, false, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_AGID_TIPINT per capire l'intermediario che sto trattando
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiGetEmissioneDaIUV($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiGetIntermediarioDaIUV($filtri, false, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per recuperare le scadenze da Pubblicare per un determinato servizio
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadenzePerPubblicazioniEfil($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_AGID_SCADENZE.*,
                BTA_SERVRENDPPA.TIPORIFCRED,BTA_SERVRENDPPA.CODSERVIZIO,
                BGE_AGID_SCA_EFIL.NUMAVVISO,BGE_AGID_SCA_EFIL.ANADEBITORE,BGE_AGID_SCA_EFIL.DTFINVAL,BGE_AGID_SCA_EFIL.DTINIVAL
                FROM BTA_SERVREND                
                INNER JOIN BTA_SERVRENDPPA ON BTA_SERVREND.PROGKEYTAB = BTA_SERVRENDPPA.IDSERVREND
                INNER JOIN BGE_AGID_SCADENZE ON BGE_AGID_SCADENZE.CODTIPSCAD=BTA_SERVREND.CODTIPSCAD 
                AND BGE_AGID_SCADENZE.SUBTIPSCAD=BTA_SERVREND.SUBTIPSCAD AND BGE_AGID_SCADENZE.ANNOEMI=BTA_SERVREND.ANNOEMI 
                AND BGE_AGID_SCADENZE.NUMEMI=BTA_SERVREND.NUMEMI AND BGE_AGID_SCADENZE.IDBOL_SERE=BTA_SERVREND.IDBOL_SERE
                INNER JOIN BGE_AGID_SCA_EFIL ON BGE_AGID_SCADENZE.PROGKEYTAB=BGE_AGID_SCA_EFIL.PROGKEYTAB ";
        $where = 'WHERE';
        if (array_key_exists('CODSERVIZIO', $filtri) && $filtri['CODSERVIZIO'] != null) {
            $this->addSqlParam($sqlParams, "CODSERVIZIO", $filtri['CODSERVIZIO'], PDO::PARAM_STR);
            $sql .= " $where BTA_SERVRENDPPA.CODSERVIZIO=:CODSERVIZIO";
            $where = 'AND';
        }
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", $filtri['CODTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", $filtri['SUBTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] != null) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.ANNOEMI=:ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] != null) {
            $this->addSqlParam($sqlParams, "NUMEMI", $filtri['NUMEMI'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.NUMEMI=:NUMEMI";
            $where = 'AND';
        }
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] != null) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", $filtri['IDBOL_SERE'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.IDBOL_SERE=:IDBOL_SERE";
            $where = 'AND';
        }
        if (array_key_exists('STATO', $filtri) && $filtri['STATO'] != null) {
            $this->addSqlParam($sqlParams, "STATO", $filtri['STATO'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_SCADENZE.STATO=:STATO";
            $where = 'AND';
        }
        if (array_key_exists('PROGKEYTAB_SCADENZA_IN', $filtri) && $filtri['PROGKEYTAB_SCADENZA_IN'] != null) {
            $sql .= " $where BGE_AGID_SCADENZE.PROGKEYTAB IN(" . implode(", ", $filtri['PROGKEYTAB_SCADENZA_IN']) . ")";
            ;
            $where = 'AND';
        }

        $sql .= ' ORDER BY BGE_AGID_SCADENZE.PROGKEYTAB';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzePerPubblicazioniEfil($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzePerPubblicazioniEfil($filtri, false, $sqlParams), true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzePerPubblicazioniEfilBlocchi($filtri, $da, $per) {
        return ItaDB::DBSQLSelect($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzePerPubblicazioniEfil($filtri, false, $sqlParams), true, $da, $per, $sqlParams);
    }

    /**
     * Restituisce comando sql per recuperare le scadenze da Pubblicare per un determinato servizio
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadenzePerPubblicazioniNSS($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_AGID_SCADENZE.*
                FROM BTA_SERVREND                
                INNER JOIN BTA_SERVRENDPPA ON BTA_SERVREND.PROGKEYTAB = BTA_SERVRENDPPA.IDSERVREND
                INNER JOIN BGE_AGID_SCADENZE ON BGE_AGID_SCADENZE.CODTIPSCAD=BTA_SERVREND.CODTIPSCAD 
                AND BGE_AGID_SCADENZE.SUBTIPSCAD=BTA_SERVREND.SUBTIPSCAD AND BGE_AGID_SCADENZE.ANNOEMI=BTA_SERVREND.ANNOEMI 
                AND BGE_AGID_SCADENZE.NUMEMI=BTA_SERVREND.NUMEMI AND BGE_AGID_SCADENZE.IDBOL_SERE=BTA_SERVREND.IDBOL_SERE
                WHERE BGE_AGID_SCADENZE.STATO=1 
                AND ";
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", $filtri['CODTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", $filtri['SUBTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] != null) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.ANNOEMI=:ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] != null) {
            $this->addSqlParam($sqlParams, "NUMEMI", $filtri['NUMEMI'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.NUMEMI=:NUMEMI";
            $where = 'AND';
        }
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] != null) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", $filtri['IDBOL_SERE'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.IDBOL_SERE=:IDBOL_SERE";
            $where = 'AND';
        }
        if (array_key_exists('STATO', $filtri) && $filtri['STATO'] != null) {
            $this->addSqlParam($sqlParams, "STATO", $filtri['STATO'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_SCADENZE.STATO=:STATO";
            $where = 'AND';
        }
        if (array_key_exists('PROGKEYTAB_SCADENZA_IN', $filtri) && $filtri['PROGKEYTAB_SCADENZA_IN'] != null) {
            $sql .= " $where BGE_AGID_SCADENZE.PROGKEYTAB IN(" . implode(", ", $filtri['PROGKEYTAB_SCADENZA_IN']) . ")";

            $where = 'AND';
        }

        $sql .= ' ORDER BY BGE_AGID_SCADENZE.PROGKEYTAB';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzePerPubblicazioniNSS($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzePerPubblicazioniNSS($filtri, false, $sqlParams), true, $sqlParams);
    }

    /**
     * Restituisce comando sql per recuperare le scadenze da Pubblicare per un determinato servizio
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadenzePerPubblicazioniMPay($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_AGID_SCADENZE.*
                FROM BTA_SERVREND                
                INNER JOIN BTA_SERVRENDPPA ON BTA_SERVREND.PROGKEYTAB = BTA_SERVRENDPPA.IDSERVREND
                INNER JOIN BGE_AGID_SCADENZE ON BGE_AGID_SCADENZE.CODTIPSCAD=BTA_SERVREND.CODTIPSCAD 
                AND BGE_AGID_SCADENZE.SUBTIPSCAD=BTA_SERVREND.SUBTIPSCAD AND BGE_AGID_SCADENZE.ANNOEMI=BTA_SERVREND.ANNOEMI 
                AND BGE_AGID_SCADENZE.NUMEMI=BTA_SERVREND.NUMEMI AND BGE_AGID_SCADENZE.IDBOL_SERE=BTA_SERVREND.IDBOL_SERE
                 ";
        $where = 'WHERE';
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", $filtri['CODTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", $filtri['SUBTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] != null) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.ANNOEMI=:ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] != null) {
            $this->addSqlParam($sqlParams, "NUMEMI", $filtri['NUMEMI'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.NUMEMI=:NUMEMI";
            $where = 'AND';
        }
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] != null) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", $filtri['IDBOL_SERE'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.IDBOL_SERE=:IDBOL_SERE";
            $where = 'AND';
        }
        if (array_key_exists('STATO', $filtri) && $filtri['STATO'] != null) {
            $this->addSqlParam($sqlParams, "STATO", $filtri['STATO'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_SCADENZE.STATO=:STATO";
            $where = 'AND';
        }
        if (array_key_exists('PROGKEYTAB_SCADENZA_IN', $filtri) && $filtri['PROGKEYTAB_SCADENZA_IN'] != null) {
            $sql .= " $where BGE_AGID_SCADENZE.PROGKEYTAB IN(" . implode(", ", $filtri['PROGKEYTAB_SCADENZA_IN']) . ")";

            $where = 'AND';
        }

        $sql .= ' ORDER BY BGE_AGID_SCADENZE.PROGKEYTAB';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzePerPubblicazioniMPay($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzePerPubblicazioniMPay($filtri, false, $sqlParams), true, $sqlParams);
    }

    public function leggiBgeAgidScadenzePerPubblicazioniMPayBlocchi($filtri, $da, $per) {
        return ItaDB::DBSQLSelect($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzePerPubblicazioniMPay($filtri, false, $sqlParams), true, $da, $per, $sqlParams);
    }

    /**
     * Restituisce comando sql per recuperare le scadenze da Pubblicare per un determinato servizio
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadenzePerPubblicazioniAltoAdigeRisco($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_AGID_SCADENZE.*
                FROM BTA_SERVREND                
                INNER JOIN BTA_SERVRENDPPA ON BTA_SERVREND.PROGKEYTAB = BTA_SERVRENDPPA.IDSERVREND
                INNER JOIN BGE_AGID_SCADENZE ON BGE_AGID_SCADENZE.CODTIPSCAD=BTA_SERVREND.CODTIPSCAD 
                AND BGE_AGID_SCADENZE.SUBTIPSCAD=BTA_SERVREND.SUBTIPSCAD AND BGE_AGID_SCADENZE.ANNOEMI=BTA_SERVREND.ANNOEMI 
                AND BGE_AGID_SCADENZE.NUMEMI=BTA_SERVREND.NUMEMI AND BGE_AGID_SCADENZE.IDBOL_SERE=BTA_SERVREND.IDBOL_SERE
                 ";
        $where = 'WHERE';
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", $filtri['CODTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", $filtri['SUBTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] != null) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.ANNOEMI=:ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] != null) {
            $this->addSqlParam($sqlParams, "NUMEMI", $filtri['NUMEMI'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.NUMEMI=:NUMEMI";
            $where = 'AND';
        }
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] != null) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", $filtri['IDBOL_SERE'], PDO::PARAM_INT);
            $sql .= " $where BTA_SERVREND.IDBOL_SERE=:IDBOL_SERE";
            $where = 'AND';
        }
        if (array_key_exists('STATO', $filtri) && $filtri['STATO'] != null) {
            $this->addSqlParam($sqlParams, "STATO", $filtri['STATO'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_SCADENZE.STATO=:STATO";
            $where = 'AND';
        }
        if (array_key_exists('PROGKEYTAB_SCADENZA_IN', $filtri) && $filtri['PROGKEYTAB_SCADENZA_IN'] != null) {
            $sql .= " $where BGE_AGID_SCADENZE.PROGKEYTAB IN(" . implode(", ", $filtri['PROGKEYTAB_SCADENZA_IN']) . ")";

            $where = 'AND';
        }

        $sql .= ' ORDER BY BGE_AGID_SCADENZE.PROGKEYTAB';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzePerPubblicazioniAltoAdigeRisco($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzePerPubblicazioniAltoAdigeRisco($filtri, false, $sqlParams), true, $sqlParams);
    }

    public function leggiBgeAgidScadenzePerPubblicazioniAltoAdigeRiscoBlocchi($filtri, $da, $per) {
        return ItaDB::DBSQLSelect($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzePerPubblicazioniAltoAdigeRisco($filtri, false, $sqlParams), true, $da, $per, $sqlParams);
    }

// BTA_SERVREND

    /**
     * Restituisce tutti gli intermediari attivi
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaServrendIntermediari($excludeOrderBy = false) {
        $sqlParams = array();

        $sql = "SELECT DISTINCT BTA_SERVRENDPPA.INTERMEDIARIO FROM BTA_SERVRENDPPA"
                . " INNER JOIN BTA_SERVREND ON BTA_SERVREND.PROGKEYTAB = BTA_SERVRENDPPA.IDSERVREND AND BTA_SERVREND.PROGKEYTAB > 0"
                . " AND BTA_SERVRENDPPA.INTERMEDIARIO > 0";

        $sql .= $excludeOrderBy ? '' : ' ORDER BY INTERMEDIARIO';
        return $sql;
    }

    /**
     * Restituisce tutti gli intermediari attivi
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaServrendIntermediari() {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaServrendIntermediari());
    }

    public function getSqlLeggiBtaCig($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT "
                . "BTA_CIG.*, BOR_UTENTI.NOMEUTE, "
                . "BOR_ORGAN.DESPORG, BOR_ORGAN.L1ORG, BOR_ORGAN.L2ORG, BOR_ORGAN.L3ORG, BOR_ORGAN.L4ORG "
                . ",BTA_SOGG.COGNOME,BTA_SOGG.NOME,BTA_SOGG.RAGSOC "
                . " FROM BTA_CIG "
                . " LEFT JOIN BOR_ORGAN ON BTA_CIG.IDORGAN = BOR_ORGAN.IDORGAN "
                . " LEFT JOIN BOR_UTENTI ON BTA_CIG.CODUTE_RUP = BOR_UTENTI.CODUTE "
                . " LEFT JOIN BTA_SOGG ON BTA_CIG.PROGSOGG = BTA_SOGG.PROGSOGG ";
        $where = 'WHERE';

        if (isSet($filtri['PROG_CIG'])) {
            $this->addSqlParam($sqlParams, 'PROG_CIG', $filtri['PROG_CIG'], PDO::PARAM_INT);
            $sql .= " $where BTA_CIG.PROG_CIG=:PROG_CIG";
            $where = 'AND';
        }
        if (isSet($filtri['PROG_CIG_in'])) {
            $sql .= " $where BTA_CIG.PROG_CIG IN (";
            for ($i = 0; $i < count($filtri['PROG_CIG_in']); $i++) {
                $this->addSqlParam($sqlParams, "PROG_CIG" . $i, $filtri['PROG_CIG_in'][$i], PDO::PARAM_INT);

                if ($i > 0) {
                    $sql .= ',';
                }
                $sql .= ':PROG_CIG' . $i;
            }
            $sql .= ")";
            $where = 'AND';
        }
        if (isSet($filtri['COD_CIG'])) {
            $this->addSqlParam($sqlParams, 'COD_CIG', '%' . strtoupper(trim($filtri['COD_CIG'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_CIG.COD_CIG") . " LIKE :COD_CIG";
            $where = 'AND';
        }
        if (isSet($filtri['COD_CIG_in'])) {
            $sql .= " $where BTA_CIG.COD_CIG IN (";
            for ($i = 0; $i < count($filtri['COD_CIG_in']); $i++) {
                $this->addSqlParam($sqlParams, "COD_CIG" . $i, $filtri['COD_CIG_in'][$i], PDO::PARAM_STR);

                if ($i > 0) {
                    $sql .= ',';
                }
                $sql .= ':COD_CIG' . $i;
            }
            $sql .= ")";
            $where = 'AND';
        }
        if (isset($filtri['DES_BREVE'])) {
            $this->addSqlParam($sqlParams, 'DES_BREVE', '%' . strtoupper(trim($filtri['DES_BREVE'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_CIG.DES_BREVE") . " LIKE :DES_BREVE";
            $where = 'AND';
        }
        if (isSet($filtri['DES_CIG'])) {
            $this->addSqlParam($sqlParams, 'DES_CIG', '%' . strtoupper(trim($filtri['DES_CIG'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_CIG.DES_CIG") . " LIKE :DES_CIG";
            $where = 'AND';
        }
        if (isSet($filtri['IDORGAN'])) {
            $this->addSqlParam($sqlParams, 'IDORGAN', $filtri['IDORGAN'], PDO::PARAM_INT);
            $sql .= " $where BTA_CIG.IDORGAN=:IDORGAN";
            $where = 'AND';
        }
        if (isSet($filtri['L1ORG'])) {
            $this->addSqlParam($sqlParams, 'L1ORG', strtoupper(trim($filtri['L1ORG'])), PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.L1ORG = :L1ORG";
            $where = 'AND';
        }
        if (isSet($filtri['L2ORG'])) {
            $this->addSqlParam($sqlParams, 'L2ORG', strtoupper(trim($filtri['L2ORG'])), PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.L2ORG = :L2ORG";
            $where = 'AND';
        }
        if (isSet($filtri['L3ORG'])) {
            $this->addSqlParam($sqlParams, 'L3ORG', strtoupper(trim($filtri['L3ORG'])), PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.L3ORG = :L3ORG";
            $where = 'AND';
        }
        if (isSet($filtri['L4ORG'])) {
            $this->addSqlParam($sqlParams, 'L4ORG', strtoupper(trim($filtri['L4ORG'])), PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.L4ORG = :L4ORG";
            $where = 'AND';
        }
        if (!empty($filtri['DESPORG'])) {
            $this->addSqlParam($sqlParams, 'DESPORG', '%' . strtoupper(trim($filtri['DESPORG'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BOR_ORGAN.DESPORG") . " LIKE :DESPORG";
            $where = ' AND ';
        }
        // Lista di Strutture Organizzative a cui si e' Abilitatao
        if (!empty($filtri['LISTA_LXORG'])) {
            $sql .= " $where ("; // Inizio Filtro Assegnatari
            $or_main = '';
            for ($i = 0; $i < count($filtri['LISTA_LXORG']); $i++) {
                $assegnatario = $filtri['LISTA_LXORG'][$i];
                $sql .= $or_main." (";
                // Sono 4 posizioni possibili per gli Assegnatari
                $and_assegn = '';
                for ($nass=1; $nass<=4; $nass++) {
                    if (isSet($assegnatario['L'.$nass.'ORG']) && !empty($assegnatario['L'.$nass.'ORG']) && $assegnatario['L'.$nass.'ORG'] != '00') {
                        $this->addSqlParam($sqlParams, 'L'.$nass.'ORG_ASSE' . $i, $assegnatario['L'.$nass.'ORG'], PDO::PARAM_STR); 
                        $sql .= $and_assegn." BOR_ORGAN.L".$nass."ORG=:L".$nass."ORG_ASSE" . $i;
                        $and_assegn = ' AND ';
                    }
                }
                $sql .= ")";
                $or_main = ' OR ';
            }
            $sql .= ")";  // Fine Filtro Assegnatari
            $where = 'AND';
        }
        // Se Operatitivita' su Ciclo PASSIVO=1 e non Utente Globale
        // Lista di Strutture Organizzative a cui si e' Abilitato solo 
        // per le Entrate mentre per le Uscite puo' vedere Tutti
        if (!empty($filtri['LISTA_LXORG_UTILIZZO_E_S'])) {
            $sql .= " $where ("; // Inizio Filtro Assegnatari / Utilizzo E/S
            $sql .= " BTA_CIG.UTILIZZO_E_S = 0 "; // Uscite sempre Tutte
            $sql .= " OR (BTA_CIG.UTILIZZO_E_S = 1 AND ("; // Entrate condizionate dalla Lista Assegnatari 
            $or_main = '';
            for ($i = 0; $i < count($filtri['LISTA_LXORG_UTILIZZO_E_S']); $i++) {
                $assegnatario = $filtri['LISTA_LXORG_UTILIZZO_E_S'][$i];
                $sql .= $or_main." (";
                // Sono 4 posizioni possibili per gli Assegnatari
                $and_assegn = '';
                for ($nass=1; $nass<=4; $nass++) {
                    if (isSet($assegnatario['L'.$nass.'ORG']) && !empty($assegnatario['L'.$nass.'ORG']) && $assegnatario['L'.$nass.'ORG'] != '00') {
                        $this->addSqlParam($sqlParams, 'L'.$nass.'ORG_ASSE' . $i, $assegnatario['L'.$nass.'ORG'], PDO::PARAM_STR); 
                        $sql .= $and_assegn." BOR_ORGAN.L".$nass."ORG=:L".$nass."ORG_ASSE" . $i;
                        $and_assegn = ' AND ';
                    }
                }
                $sql .= ")";
                $or_main = ' OR ';
            }
            $sql .= ")";  // Fine Filtro Utilizzo Entrate condizionate
            $sql .= ")";  // Fine Filtro Utilizzo E/S
            $sql .= ")";  // Fine Filtro Assegnatari
            $where = 'AND';
        }
        
        // Controllo sul RUP (Tutti i Tipi E/S)
        if (isSet($filtri['CODUTE_RUP'])) {
            $this->addSqlParam($sqlParams, 'CODUTE_RUP', strtoupper(trim($filtri['CODUTE_RUP'])) , PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_CIG.CODUTE_RUP") . " = :CODUTE_RUP";
            $where = 'AND';
        }
        // Controllo sul RUP (Per le Entrate non va Controllato)
        if (isSet($filtri['CODUTE_RUP_UTILIZZO_E_S'])) {
            $this->addSqlParam($sqlParams, 'CODUTE_RUP_UTILIZZO_E_S', strtoupper(trim($filtri['CODUTE_RUP_UTILIZZO_E_S'])) , PDO::PARAM_STR);
            
            $sql .= " $where (BTA_CIG.UTILIZZO_E_S = 1 "; // Entrate sempre Tutte
            $sql .= " OR (BTA_CIG.UTILIZZO_E_S = 0 AND " . $this->getCitywareDB()->strUpper("BTA_CIG.CODUTE_RUP") . " = :CODUTE_RUP_UTILIZZO_E_S)"; // Uscite condizionate dall'Utente Rup 
            $sql .= ")";  // Fine Filtro Rup
            $where = 'AND';
        }
        
        if (isSet($filtri['CODUTE'])) {
            $this->addSqlParam($sqlParams, 'CODUTE', '%' . strtoupper(trim($filtri['CODUTE'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_CIG.CODUTE") . " LIKE :CODUTE";
            $where = 'AND';
        }
        if (!empty($filtri['PROGSOGG'])) {
            $this->addSqlParam($sqlParams, 'PROGSOGG', $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where BTA_CIG.PROGSOGG=:PROGSOGG";
            $where = 'AND';
        }
        if (!empty($filtri['RAGSOC'])) {
            $this->addSqlParam($sqlParams, 'RAGSOC', '%' . strtoupper(trim($filtri['RAGSOC'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_SOGG.RAGSOC") . " LIKE :RAGSOC";
            $where = ' AND ';
        }
        if (array_key_exists('DATAINIZ', $filtri) && $filtri['DATAINIZ'] != null) {
            $this->addSqlParam($sqlParams, "DATAINIZ", $filtri['DATAINIZ'], PDO::PARAM_STR);
            $sql .= " $where BTA_CIG.DATAINIZ=:DATAINIZ";
            $where = 'AND';
        }
        if (array_key_exists('DATAINIZIODAL', $filtri) && $filtri['DATAINIZIODAL'] != null) {
            $this->addSqlParam($sqlParams, "DATAINIZIODAL", $filtri['DATAINIZIODAL'], PDO::PARAM_STR);
            $sql .= " $where BTA_CIG.DATAINIZ>=:DATAINIZIODAL";
            $where = 'AND';
        }
        if (array_key_exists('DATAFINE', $filtri) && $filtri['DATAFINE'] != null) {
            $this->addSqlParam($sqlParams, "DATAFINE", $filtri['DATAFINE'], PDO::PARAM_STR);
            $sql .= " $where BTA_CIG.DATAFINE=:DATAFINE";
            $where = 'AND';
        }
        if (array_key_exists('CESSATIPRIMADEL', $filtri) && $filtri['CESSATIPRIMADEL'] != null) {
            $this->addSqlParam($sqlParams, "CESSATIPRIMADEL", $filtri['CESSATIPRIMADEL'], PDO::PARAM_STR);
            $sql .= " $where (BTA_CIG.DATAFINE>=:CESSATIPRIMADEL OR BTA_CIG.DATAFINE IS NULL)";
            $where = 'AND';
        }
        if (isSet($filtri['UTILIZZO_E_S'])) {
            $this->addSqlParam($sqlParams, 'UTILIZZO_E_S', $filtri['UTILIZZO_E_S'], PDO::PARAM_INT);
            $sql .= " $where BTA_CIG.UTILIZZO_E_S=:UTILIZZO_E_S";
            $where = 'AND';
        }
        if (isSet($filtri['FLAG_DIS'])) {
            $this->addSqlParam($sqlParams, 'FLAG_DIS', $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where BTA_CIG.FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY BTA_CIG.COD_CIG';
        return $sql;
    }

    public function leggiBtaCig($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaCig($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBtaCigChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_CIG'] = $cod;
        return self::getSqlLeggiBtaCig($filtri, true, $sqlParams);
    }

    public function leggiBtaCigChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaCigChiave($cod, $sqlParams), false, $sqlParams);
    }

    public function getSqlLeggiListaRupBtaCig($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_CIG.CODUTE_RUP, BOR_UTENTI.NOMEUTE
                    FROM BTA_CIG 
                        LEFT JOIN BOR_UTENTI ON BTA_CIG.CODUTE_RUP = BOR_UTENTI.CODUTE
                    WHERE BTA_CIG.CODUTE_RUP IS NOT NULL"; // Solo se RUP Caricato

        $where = 'AND';

        if (array_key_exists('DATAINIZ', $filtri) && $filtri['DATAINIZ'] != null) {
            $this->addSqlParam($sqlParams, "DATAINIZ", $filtri['DATAINIZ'], PDO::PARAM_STR);
            $sql .= " $where BTA_CIG.DATAINIZ=:DATAINIZ";
            $where = 'AND';
        }
        if (array_key_exists('DATAINIZIODAL', $filtri) && $filtri['DATAINIZIODAL'] != null) {
            $this->addSqlParam($sqlParams, "DATAINIZIODAL", $filtri['DATAINIZIODAL'], PDO::PARAM_STR);
            $sql .= " $where BTA_CIG.DATAINIZ>=:DATAINIZIODAL";
            $where = 'AND';
        }
        if (array_key_exists('DATAFINE', $filtri) && $filtri['DATAFINE'] != null) {
            $this->addSqlParam($sqlParams, "DATAFINE", $filtri['DATAFINE'], PDO::PARAM_STR);
            $sql .= " $where BTA_CIG.DATAFINE=:DATAFINE";
            $where = 'AND';
        }
        if (array_key_exists('CESSATIPRIMADEL', $filtri) && $filtri['CESSATIPRIMADEL'] != null) {
            $this->addSqlParam($sqlParams, "CESSATIPRIMADEL", $filtri['CESSATIPRIMADEL'], PDO::PARAM_STR);
            $sql .= " $where (BTA_CIG.DATAFINE>=:CESSATIPRIMADEL OR BTA_CIG.DATAFINE IS NULL)";
            $where = 'AND';
        }
        if (!empty($filtri['FLAG_DIS'])) {
            $this->addSqlParam($sqlParams, 'FLAG_DIS', $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where BTA_CIG.FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }

        $sql .= " GROUP BY BTA_CIG.CODUTE_RUP, BOR_UTENTI.NOMEUTE";

        return $sql;
    }

    public function leggiListaRupBtaCig($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiListaRupBtaCig($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBtaCup($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT "
                . "BTA_CUP.*, BOR_UTENTI.NOMEUTE, "
                . "BOR_ORGAN.DESPORG, BOR_ORGAN.L1ORG, BOR_ORGAN.L2ORG, BOR_ORGAN.L3ORG, BOR_ORGAN.L4ORG "
                . " FROM BTA_CUP "
                . " LEFT JOIN BOR_ORGAN ON BTA_CUP.IDORGAN = BOR_ORGAN.IDORGAN "
                . " LEFT JOIN BOR_UTENTI ON BTA_CUP.CODUTE_RUP = BOR_UTENTI.CODUTE ";
        $where = 'WHERE';

        if (!empty($filtri['PROG_CUP'])) {
            $this->addSqlParam($sqlParams, 'PROG_CUP', $filtri['PROG_CUP'], PDO::PARAM_INT);
            $sql .= " $where BTA_CUP.PROG_CUP=:PROG_CUP";
            $where = 'AND';
        }
        if (!empty($filtri['PROG_CUP_in'])) {
            $sql .= " $where BTA_CUP.PROG_CUP IN (";
            for ($i = 0; $i < count($filtri['PROG_CUP_in']); $i++) {
                $this->addSqlParam($sqlParams, "PROG_CUP" . $i, $filtri['PROG_CUP_in'][$i], PDO::PARAM_INT);

                if ($i > 0) {
                    $sql .= ',';
                }
                $sql .= ':PROG_CUP' . $i;
            }
            $sql .= ")";
            $where = 'AND';
        }
        if (!empty($filtri['COD_CUP'])) {
            $this->addSqlParam($sqlParams, 'COD_CUP', '%' . strtoupper(trim($filtri['COD_CUP'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_CUP.COD_CUP") . " LIKE :COD_CUP";
            $where = 'AND';
        }
        if (!empty($filtri['COD_CUP_in'])) {
            $sql .= " $where BTA_CUP.COD_CUP IN (";
            for ($i = 0; $i < count($filtri['COD_CUP_in']); $i++) {
                $this->addSqlParam($sqlParams, "COD_CUP" . $i, $filtri['COD_CUP_in'][$i], PDO::PARAM_STR);

                if ($i > 0) {
                    $sql .= ',';
                }
                $sql .= ':COD_CUP' . $i;
            }
            $sql .= ")";
            $where = 'AND';
        }
        if (isset($filtri['DES_BREVE'])) {
            $this->addSqlParam($sqlParams, 'DES_BREVE', '%' . strtoupper(trim($filtri['DES_BREVE'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_CUP.DES_BREVE") . " LIKE :DES_BREVE";
            $where = 'AND';
        }
        if (isSet($filtri['DES_CUP'])) {
            $this->addSqlParam($sqlParams, 'DES_CUP', '%' . strtoupper(trim($filtri['DES_CUP'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_CUP.DES_CUP") . " LIKE :DES_CUP";
            $where = 'AND';
        }
        if (isSet($filtri['IDORGAN'])) {
            $this->addSqlParam($sqlParams, 'IDORGAN', $filtri['IDORGAN'], PDO::PARAM_INT);
            $sql .= " $where BTA_CIG.IDORGAN=:IDORGAN";
            $where = 'AND';
        }
        if (isSet($filtri['L1ORG'])) {
            $this->addSqlParam($sqlParams, 'L1ORG', strtoupper(trim($filtri['L1ORG'])), PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.L1ORG = :L1ORG";
            $where = 'AND';
        }
        if (isSet($filtri['L2ORG'])) {
            $this->addSqlParam($sqlParams, 'L2ORG', strtoupper(trim($filtri['L2ORG'])), PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.L2ORG = :L2ORG";
            $where = 'AND';
        }
        if (isSet($filtri['L3ORG'])) {
            $this->addSqlParam($sqlParams, 'L3ORG', strtoupper(trim($filtri['L3ORG'])), PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.L3ORG = :L3ORG";
            $where = 'AND';
        }
        if (isSet($filtri['L4ORG'])) {
            $this->addSqlParam($sqlParams, 'L4ORG', strtoupper(trim($filtri['L4ORG'])), PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.L4ORG = :L4ORG";
            $where = 'AND';
        }
        // Lista di Strutture Organizzative a cui si e' Abilitatao
        if (!empty($filtri['LISTA_LXORG'])) {
            $sql .= " $where ("; // Inizio Filtro Assegnatari
            $or_main = '';
            for ($i = 0; $i < count($filtri['LISTA_LXORG']); $i++) {
                $assegnatario = $filtri['LISTA_LXORG'][$i];
                $sql .= $or_main." (";
                // Sono 4 posizioni possibili per gli Assegnatari
                $and_assegn = '';
                for ($nass=1; $nass<=4; $nass++) {
                    if (isSet($assegnatario['L'.$nass.'ORG']) && !empty($assegnatario['L'.$nass.'ORG']) && $assegnatario['L'.$nass.'ORG'] != '00') {
                        $this->addSqlParam($sqlParams, 'L'.$nass.'ORG_ASSE' . $i, $assegnatario['L'.$nass.'ORG'], PDO::PARAM_STR); 
                        $sql .= $and_assegn." BOR_ORGAN.L".$nass."ORG=:L".$nass."ORG_ASSE" . $i;
                        $and_assegn = ' AND ';
                    }
                }
                $sql .= ")";
                $or_main = ' OR ';
            }
            $sql .= ")";  // Fine Filtro Assegnatari
            $where = 'AND';
        }
        
        if (!empty($filtri['DESPORG'])) {
            $this->addSqlParam($sqlParams, 'DESPORG', '%' . strtoupper(trim($filtri['DESPORG'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BOR_ORGAN.DESPORG") . " LIKE :DESPORG";
            $where = ' AND ';
        }
        if (array_key_exists('DATAINIZ', $filtri) && $filtri['DATAINIZ'] != null) {
            $this->addSqlParam($sqlParams, "DATAINIZ", $filtri['DATAINIZ'], PDO::PARAM_STR);
            $sql .= " $where BTA_CUP.DATAINIZ=:DATAINIZ";
            $where = 'AND';
        }
        if (array_key_exists('DATAFINE', $filtri) && $filtri['DATAFINE'] != null) {
            $this->addSqlParam($sqlParams, "DATAFINE", $filtri['DATAFINE'], PDO::PARAM_STR);
            $sql .= " $where BTA_CUP.DATAFINE=:DATAFINE";
            $where = 'AND';
        }
        if (array_key_exists('CESSATIPRIMADEL', $filtri) && $filtri['CESSATIPRIMADEL'] != null) {
            $this->addSqlParam($sqlParams, "CESSATIPRIMADEL", $filtri['CESSATIPRIMADEL'], PDO::PARAM_STR);
            $sql .= " $where (BTA_CUP.DATAFINE>=:CESSATIPRIMADEL OR BTA_CUP.DATAFINE IS NULL)";
            $where = 'AND';
        }
        if (isSet($filtri['CODUTE'])) {
            $this->addSqlParam($sqlParams, 'CODUTE', '%' . strtoupper(trim($filtri['CODUTE'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_CUP.CODUTE") . " LIKE :CODUTE";
            $where = 'AND';
        }
        if (isSet($filtri['CODUTE_RUP'])) {
            $this->addSqlParam($sqlParams, 'CODUTE_RUP', strtoupper(trim($filtri['CODUTE_RUP'])) , PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_CUP.CODUTE_RUP") . " = :CODUTE_RUP";
            $where = 'AND';
        }
        if (isSet($filtri['FLAG_DIS'])) {
            $this->addSqlParam($sqlParams, 'FLAG_DIS', $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where BTA_CUP.FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY BTA_CUP.COD_CUP';
        return $sql;
    }

    public function leggiBtaCup($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaCup($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBtaCupChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_CUP'] = $cod;
        return self::getSqlLeggiBtaCup($filtri, true, $sqlParams);
    }

    public function leggiBtaCupChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaCupChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_REND_CONF

    /**
     * Restituisce comando sql per lettura tabella BTA_REND_CONF
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendConf($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_REND_CONF.* FROM BTA_REND_CONF";
        $where = 'WHERE';
        if ($filtri) {
            if (array_key_exists('DATAINIZREND', $filtri) && $filtri['DATAINIZREND'] != null) {
                $this->addSqlParam($sqlParams, "DATAINIZREND", $filtri['DATAINIZREND'], PDO::PARAM_STR);
                $sql .= " $where DATAINIZREND=:DATAINIZREND";
                $where = 'AND';
            }

            if (array_key_exists('INTERVALLO', $filtri) && $filtri['INTERVALLO'] != 0) {
                $this->addSqlParam($sqlParams, "INTERVALLO", strtoupper(trim($filtri['INTERVALLO'])), PDO::PARAM_INT);
                $sql .= " $where INTERVALLO=:INTERVALLO";
                $where = 'AND';
            }
            if (array_key_exists('ID', $filtri) && $filtri['ID'] != null) {
                $this->addSqlParam($sqlParams, "ID", strtoupper(trim($filtri['ID'])), PDO::PARAM_INT);
                $sql .= " $where ID=:ID";
                $where = 'AND';
            }
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY DATAINIZREND DESC';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_REND_CONF
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRendConf($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendConf($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_REND_CONF
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendConfChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['ID'] = $cod;
        return self::getSqlLeggiBtaRendConf($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_REND_CONF per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaRendConfChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendConfChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_REND_LOG

    /**
     * Restituisce comando sql per lettura tabella BTA_REND_LOG
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendLog($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_REND_LOG.* FROM BTA_REND_LOG";
        if ($filtri) {
            $where = 'WHERE';
            if (array_key_exists('DATAINIZIO', $filtri) && $filtri['DATAINIZIO'] != null && array_key_exists('DATAFINE', $filtri) && $filtri['DATAFINE'] != null) {
                $this->addSqlParam($sqlParams, "DATAINIZIO", $filtri['DATAINIZIO'], PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "DATAFINE", $filtri['DATAFINE'], PDO::PARAM_STR);
                $sql .= " $where DATAINIZIO>=:DATAINIZIO AND DATAFINE<=:DATAFINE";
                $where = 'AND';
            } else if (array_key_exists('DATAINIZIO', $filtri) && $filtri['DATAINIZIO'] != null) {
                $this->addSqlParam($sqlParams, "DATAINIZIO", $filtri['DATAINIZIO'], PDO::PARAM_STR);
                $sql .= " $where DATAINIZIO=:DATAINIZIO";
                $where = 'AND';
            } else if (array_key_exists('DATAFINE', $filtri) && $filtri['DATAFINE'] != null) {
                $this->addSqlParam($sqlParams, "DATAFINE", $filtri['DATAFINE'], PDO::PARAM_STR);
                $sql .= " $where DATAFINE=:DATAFINE";
                $where = 'AND';
            } else if (array_key_exists('DATAINIZIO_MIN', $filtri) && $filtri['DATAINIZIO_MIN'] != null && array_key_exists('DATAFINE_MAG', $filtri) && $filtri['DATAFINE_MAG'] != null) {
                $this->addSqlParam($sqlParams, "DATAINIZIOMIN", $filtri['DATAINIZIO_MIN'], PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "DATAFINEMAG", $filtri['DATAFINE_MAG'], PDO::PARAM_STR);
                $sql .= " $where (DATAINIZIO<=:DATAINIZIOMIN AND DATAFINE>=:DATAFINEMAG)";
                $where = 'AND';
            } else if (array_key_exists('DATAINIZIO_DIVERSO', $filtri) && $filtri['DATAINIZIO_DIVERSO'] != null) {
                $this->addSqlParam($sqlParams, "DATAINIZDIVERSO", $filtri['DATAINIZIO_DIVERSO'], PDO::PARAM_STR);
                $sql .= " $where DATAINIZIO!=:DATAINIZDIVERSO";
                $where = 'AND';
            } else if (array_key_exists('DATAINIZIO_MAGGIORE', $filtri) && $filtri['DATAINIZIO_MAGGIORE'] != null) {
                $this->addSqlParam($sqlParams, "DATAINIZMAGGIORE", $filtri['DATAINIZIO_MAGGIORE'], PDO::PARAM_STR);
                $sql .= " $where DATAINIZIO>:DATAINIZMAGGIORE";
                $where = 'AND';
            } else if (array_key_exists('DATAINIZIO_MAGGIOREUGUALE', $filtri) && $filtri['DATAINIZIO_MAGGIOREUGUALE'] != null) {
                $this->addSqlParam($sqlParams, "DATAINIZIOMAGGIOREUGUALE", $filtri['DATAINIZIO_MAGGIOREUGUALE'], PDO::PARAM_STR);
                $sql .= " $where DATAINIZIO>=:DATAINIZIOMAGGIOREUGUALE";
                $where = 'AND';
            }
            if (array_key_exists('ID', $filtri) && $filtri['ID'] != null) {
                $this->addSqlParam($sqlParams, "ID", strtoupper(trim($filtri['ID'])), PDO::PARAM_INT);
                $sql .= " $where ID=:ID";
                $where = 'AND';
            }
            if (array_key_exists('CONVALIDA', $filtri) && $filtri['CONVALIDA'] !== null) {
                $this->addSqlParam($sqlParams, "CONVALIDA", strtoupper(trim($filtri['CONVALIDA'])), PDO::PARAM_INT);
                $sql .= " $where CONVALIDA=:CONVALIDA";
                $where = 'AND';
            }
            if (array_key_exists('ESITO', $filtri) && $filtri['ESITO'] !== null) {
                $this->addSqlParam($sqlParams, "ESITO", strtoupper(trim($filtri['ESITO'])), PDO::PARAM_INT);
                $sql .= " $where ESITO=:ESITO";
                $where = 'AND';
            }
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY ID DESC';

        return $sql;
    }

    public function getSqlLeggiBtaRendLogMaxRendSel($excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_REND_LOG.* FROM BTA_REND_LOG ";
        $sql .= 'WHERE DATAFINE = (SELECT MAX(DATAFINE) FROM BTA_REND_LOG WHERE CONVALIDA = :CONVALIDA)';
        $this->addSqlParam($sqlParams, "CONVALIDA", 1, PDO::PARAM_INT);

        $sql .= $excludeOrderBy ? '' : ' ORDER BY ID ASC';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_REND_LOG
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRendLog($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendLog($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_REND_LOGD
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendLogChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['ID'] = $cod;
        return self::getSqlLeggiBtaRendLog($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_REND_LOGD per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaRendLogChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendLogChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce l'ultimo BTA_REND_LOG in tabella (data piu alta)
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRendLogMaxRendSel() {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendLogMaxRendSel(false, $sqlParams), false, $sqlParams);
    }

// BTA_REND_LOGD

    /**
     * Restituisce comando sql per lettura tabella BTA_REND_LOGD
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendLogd($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_REND_LOGD.* FROM BTA_REND_LOGD";
        $where = 'WHERE';
        if (array_key_exists('ID_RENDLOG', $filtri) && $filtri['ID_RENDLOG'] != null) {
            $this->addSqlParam($sqlParams, "ID_RENDLOG", strtoupper(trim($filtri['ID_RENDLOG'])), PDO::PARAM_INT);
            $sql .= " $where ID_RENDLOG=:ID_RENDLOG";
            $where = 'AND';
        }
        if (array_key_exists('ID', $filtri) && $filtri['ID'] != null) {
            $this->addSqlParam($sqlParams, "ID", strtoupper(trim($filtri['ID'])), PDO::PARAM_INT);
            $sql .= " $where ID=:ID";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_REND_LOGD
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRendLogd($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendLogd($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

// BTA_REND_ACC_STO

    /**
     * Restituisce comando sql per lettura tabella BTA_REND_ACC_STO
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendAccSto($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT ID,"
                . "ID_RENDL,"
                . $this->getCitywareDB()->adapterBlob("ALLEGATO") . " , "
                . "EMAIL,"
                . "CODUTE,"
                . "DATAOPER,"
                . "TIMEOPER"
                . " FROM BTA_REND_ACC_STO ";
        $where = 'WHERE';

        if (array_key_exists('ID', $filtri) && $filtri['ID'] != null) {
            $this->addSqlParam($sqlParams, "ID", strtoupper(trim($filtri['ID'])), PDO::PARAM_INT);
            $sql .= " $where ID=:ID";
            $where = 'AND';
        }
        if (array_key_exists('ID_RENDL', $filtri) && $filtri['ID_RENDL'] != null) {
            $this->addSqlParam($sqlParams, "ID_RENDL", strtoupper(trim($filtri['ID_RENDL'])), PDO::PARAM_INT);
            $sql .= " $where ID_RENDL=:ID_RENDL";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY ID DESC';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_REND_ACC_STO
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRendAccSto($filtri, $multipla = true) {
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BTA", "leggiBtaRendAccStoBinary");
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendAccSto($filtri, false, $sqlParams), false, $sqlParams, $infoBinaryCallback);
    }

//risultato del caricamento
    public function leggiBtaRendAccStoBinary($result = array()) {
        $sql = "SELECT " . $this->getCitywareDB()->adapterBlob("ALLEGATO") . " FROM BTA_REND_ACC_STO WHERE ID = :ID";
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "ID", $result['ID'], PDO::PARAM_INT);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "ALLEGATO", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['ALLEGATO'] = $resultBin['ALLEGATO'];

        return $result;
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_REND_ACC_STO
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendAccStoChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['ID'] = $cod;
        return self::getSqlLeggiBtaRendAccSto($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_REND_ACC_STO per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaRendAccStoChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendAccStoChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_SOGG

    /**
     * Restituisce comando sql per lettura tabella BTA_PROF
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaSogg($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_SOGG.* FROM BTA_SOGG ";
        $where = 'WHERE';
        if (array_key_exists('PROGSOGG', $filtri) && $filtri['PROGSOGG'] != null) {
            $this->addSqlParam($sqlParams, "PROGSOGG", $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where PROGSOGG=:PROGSOGG";
            $where = ' AND ';
        }
        if (array_key_exists('CODFISCALE', $filtri) && $filtri['CODFISCALE'] != null) {
            $this->addSqlParam($sqlParams, "CODFISCALE", strtoupper(trim($filtri['CODFISCALE'])), PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('CODFISCALE') . " = :CODFISCALE";
            $where = ' AND ';
        }
        if (array_key_exists('PARTIVA', $filtri) && $filtri['PARTIVA'] != null) {
            $this->addSqlParam($sqlParams, "PARTIVA", strtoupper(trim($filtri['PARTIVA'])), PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('PARTIVA') . " = :PARTIVA";
            $where = ' AND ';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY COGNOME,NOME ';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SOGG
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaSogg($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaSogg($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_SOGG
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaSoggChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['PROGSOGG'] = $cod;
        return self::getSqlLeggiBtaSogg($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_SOGG per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaSoggChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaSoggChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_ANPR_SUBENTRATI

    /**
     * Restituisce comando sql per lettura tabella BTA_ANPR_SUBENTRATI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlBtaANPRSubentrati($filtri, $excludeOrderBy = false, &$sqlParams = array(), $exclude_join = false) {
        $sql = "SELECT BTA_ANPR_SUBENTRATI.* FROM BTA_ANPR_SUBENTRATI";
        if ($exclude_join == false) {
            $sql .= " LEFT JOIN BTA_LOCAL BTA_LOCAL on BTA_ANPR_SUBENTRATI.istnazpro = BTA_LOCAL.istnazpro and BTA_ANPR_SUBENTRATI.istlocal=BTA_LOCAL.istlocal";
        }
        $sql .= ' WHERE 1=1 ';
        if (array_key_exists('CODNAZPRO', $filtri) && $filtri['CODNAZPRO'] != null) {
            $this->addSqlParam($sqlParams, "CODNAZPRO", $filtri['CODNAZPRO'], PDO::PARAM_INT);
            $sql .= " and BTA_LOCAL.CODNAZPRO=:CODNAZPRO";
        }
        if (array_key_exists('CODLOCAL', $filtri) && $filtri['CODLOCAL'] != null) {
            $this->addSqlParam($sqlParams, "CODLOCAL", $filtri['CODLOCAL'], PDO::PARAM_INT);
            $sql .= " and BTA_LOCAL.CODLOCAL=:CODLOCAL";
        }
        if (array_key_exists('IDANPRSUBENTRATI', $filtri) && $filtri['IDANPRSUBENTRATI'] != null) {
            $this->addSqlParam($sqlParams, "IDANPRSUBENTRATI", $filtri['IDANPRSUBENTRATI'], PDO::PARAM_INT);
            $sql .= " and IDANPRSUBENTRATI=:IDANPRSUBENTRATI";
        }
        if (array_key_exists('ISTNAZPRO', $filtri) && $filtri['ISTNAZPRO'] != null) {
            $this->addSqlParam($sqlParams, "ISTNAZPRO", $filtri['ISTNAZPRO'], PDO::PARAM_INT);
            $sql .= " and BTA_ANPR_SUBENTRATI.ISTNAZPRO=:ISTNAZPRO";
        }
        if (array_key_exists('ISTLOCAL', $filtri) && $filtri['ISTLOCAL'] != null) {
            $this->addSqlParam($sqlParams, "ISTLOCAL", $filtri['ISTLOCAL'], PDO::PARAM_INT);
            $sql .= " and BTA_ANPR_SUBENTRATI.ISTLOCAL=:ISTLOCAL";
        }
        if (array_key_exists('COMUNE', $filtri) && $filtri['COMUNE'] != null) {
            $this->addSqlParam($sqlParams, "COMUNE", strtoupper(trim($filtri['COMUNE'])), PDO::PARAM_STR);
            $sql .= " and " . $this->getCitywareDB()->strUpper('BTA_ANPR_SUBENTRATI.COMUNE') . " = :COMUNE";
        }
        if (array_key_exists('COMUNE_RIC', $filtri) && $filtri['COMUNE_RIC'] != null) {
            $this->addSqlParam($sqlParams, "COMUNE", strtoupper(trim($filtri['COMUNE_RIC'])) . "%", PDO::PARAM_STR);
            $sql .= " and " . $this->getCitywareDB()->strUpper('BTA_ANPR_SUBENTRATI.COMUNE') . " LIKE :COMUNE";
        }
        if (array_key_exists('PROVINCIA', $filtri) && $filtri['PROVINCIA'] != null) {
            $this->addSqlParam($sqlParams, "PROVINCIA", strtoupper(trim($filtri['PROVINCIA'])), PDO::PARAM_STR);
            $sql .= " and " . $this->getCitywareDB()->strUpper('BTA_ANPR_SUBENTRATI.PROVINCIA') . " = :PROVINCIA";
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY BTA_ANPR_SUBENTRATI.COMUNE ';
        return $sql;
    }

    /**
     * Restituisce dati tabella BtaANPRSubentrati
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaANPRSubentrati($filtri, $multipla = true, $exclude_join = false) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlBtaANPRSubentrati($filtri, false, $sqlParams, $exclude_join), $multipla, $sqlParams);
    }

// BTA_PRESOG

    /**
     * Restituisce comando sql per lettura tabella BTA_PRESOG
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaPresogQuadri($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT M.PROGSOGG, M.CODUTE, M.PROGTES, T.PROGTES, T.FLAG_DIS, T.FLAG_SN_MATRBON FROM BTA_PRESOG M INNER JOIN BTA_PRETES T ON M.PROGTES = T.PROGTES ";
        $where = 'WHERE';
        if (array_key_exists('PROGSOGG', $filtri) && $filtri['PROGSOGG'] != null) {
            $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
            $sql .= " $where M.PROGSOGG = :PROGSOGG";
            $where = 'AND';
        }
        $sql .= ' AND T.FLAG_DIS = 0 AND T.FLAG_SN_MATRBON > 0';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_PRESOG
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaPresogQuadri($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaPresogQuadri($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

// BTA_RENDSS

    /**
     * Restituisce comando sql per lettura tabella BTA_RENDSS
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendss($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT 
                    ID,
                    ANNOCOMPETENZA,
                    ID_RENDSEL_DA,
                    ID_RENDSEL_A,
                    " . $this->getCitywareDB()->adapterBlob("ZIP") . ",
                    TIPODATO,
                    CODUTE,
                    DATAOPER,
                    TIMEOPER
                FROM BTA_RENDSS";
        $sql .= $this->addFiltersBtaRendss($filtri, $excludeOrderBy, $sqlParams);

        return $sql;
    }

    /**
     * Restituisce comando sql per count tabella BTA_RENDSS
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendssCount($filtri, &$sqlParams = array()) {
        $sql = "SELECT COUNT(*) AS CONTA FROM BTA_RENDSS";
        $sql .= $this->addFiltersBtaRendss($filtri, true, $sqlParams);

        return $sql;
    }

    public function getSqlDeleteBtaRendss($filtri, &$sqlParams = array()) {
        $sql = "DELETE FROM BTA_RENDSS";
        $sql .= $this->addFiltersBtaRendss($filtri, true, $sqlParams);

        return $sql;
    }

    private function addFiltersBtaRendss($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $where = 'WHERE';

        if (array_key_exists('ID', $filtri) && $filtri['ID'] != null) {
            $this->addSqlParam($sqlParams, "ID", strtoupper(trim($filtri['ID'])), PDO::PARAM_INT);
            $sql .= " $where ID = :ID";
            $where = 'AND';
        } else {
            if (array_key_exists('ID_MIN', $filtri) && $filtri['ID_MIN'] != null) {
                $this->addSqlParam($sqlParams, "ID_MIN", strtoupper(trim($filtri['ID_MIN'])), PDO::PARAM_INT);
                $sql .= " $where ID >= :ID_MIN";
                $where = 'AND';
            }
            if (array_key_exists('ID_MAX', $filtri) && $filtri['ID_MAX'] != null) {
                $this->addSqlParam($sqlParams, "ID_MAX", strtoupper(trim($filtri['ID_MAX'])), PDO::PARAM_INT);
                $sql .= " $where ID <= :ID_MAX";
                $where = 'AND';
            }
        }

        if (array_key_exists('ID_RENDSEL_DA', $filtri) && $filtri['ID_RENDSEL_DA'] != null) {
            $this->addSqlParam($sqlParams, "ID_RENDSEL_DA", strtoupper(trim($filtri['ID_RENDSEL_DA'])), PDO::PARAM_INT);
            $sql .= " $where ID_RENDSEL_DA = :ID_RENDSEL_DA ";
            $where = 'AND';
        }
        if (array_key_exists('ID_RENDSEL_A', $filtri) && $filtri['ID_RENDSEL_A'] != null) {
            $this->addSqlParam($sqlParams, "ID_RENDSEL_A", strtoupper(trim($filtri['ID_RENDSEL_A'])), PDO::PARAM_INT);
            $sql .= " $where ID_RENDSEL_A = :ID_RENDSEL_A ";
            $where = 'AND';
        }
        if (array_key_exists('TIPODATO', $filtri) && $filtri['TIPODATO'] != null) {
            $this->addSqlParam($sqlParams, "TIPODATO", strtoupper(trim($filtri['TIPODATO'])), PDO::PARAM_INT);
            $sql .= " $where TIPODATO = :TIPODATO";
            $where = 'AND';
        }
        if (array_key_exists('TIPODATO_IN', $filtri) && $filtri['TIPODATO_IN'] != null) {
            $sql .= " $where TIPODATO IN(" . implode(", ", $filtri['TIPODATO_IN']) . ")";
            $where = 'AND';
        }
        if (array_key_exists('ANNOCOMPETENZA', $filtri) && $filtri['ANNOCOMPETENZA'] !== null) {
            $this->addSqlParam($sqlParams, "ANNOCOMPETENZA", strtoupper(trim($filtri['ANNOCOMPETENZA'])), PDO::PARAM_INT);
            $sql .= " $where ANNOCOMPETENZA = :ANNOCOMPETENZA";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY ID DESC';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_RENDSS
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRendss($filtri, $multipla = true) {
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BTA", "leggiBtaRendssBinary");
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendss($filtri, false, $sqlParams), $multipla, $sqlParams, $infoBinaryCallback);
    }

//risultato del caricamento
    public function leggiBtaRendssBinary($result = array()) {
        $sql = "SELECT " . $this->getCitywareDB()->adapterBlob("ZIP") . " FROM BTA_RENDSS WHERE ID = :ID";
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "ID", $result['ID'], PDO::PARAM_INT);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "ZIP", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['ZIP'] = $resultBin['ZIP'];

        return $result;
    }

    public function getSqlLeggiBtaRendssDataOper($filtri, &$sqlParams = array()) {
        $sql = "SELECT MAX(DATAOPER) as DATAOPER,ID FROM BTA_RENDSS ";
        $sql .= $this->addFiltersBtaRendss($filtri, true, $sqlParams);
        $sql .= " GROUP BY ID,DATAOPER ";

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_RENDSS
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRendssDataOper($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendssDataOper($filtri, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce count BTA_RENDSS
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function countBtaRendss($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendssCount($filtri, $sqlParams), false, $sqlParams);
    }

    public function deleteBtaRendss($filtri) {
        $sql = $this->getSqlDeleteBtaRendss($filtri, $sqlParams);
        return $this->getCitywareDB()->query($sql, false, $sqlParams);
    }

    public function deleteBtaRendssDaA($filtri) {
        $sql = $this->getSqlDeleteBtaRendssDaA($filtri, $sqlParams);
        return $this->getCitywareDB()->query($sql, false, $sqlParams);
    }

    public function getSqlDeleteBtaRendssDaA($filtri, &$sqlParams = array()) {
        $sql = "DELETE FROM BTA_RENDSS WHERE ";

        if (array_key_exists('ID_RENDSEL_DA_MINOREUGUALE', $filtri) && $filtri['ID_RENDSEL_DA_MINOREUGUALE'] != null && array_key_exists('ID_RENDSEL_DA_MINOREUGUALE', $filtri) && $filtri['ID_RENDSEL_DA_MINOREUGUALE'] != null) {
            $this->addSqlParam($sqlParams, "ID_RENDSEL_DA_MINOREUGUALE", strtoupper(trim($filtri['ID_RENDSEL_DA_MINOREUGUALE'])), PDO::PARAM_INT);
            $this->addSqlParam($sqlParams, "ID_RENDSEL_A_MAGGIOREUGUALE", strtoupper(trim($filtri['ID_RENDSEL_A_MAGGIOREUGUALE'])), PDO::PARAM_INT);

            $sql .= " (ID_RENDSEL_DA <= :ID_RENDSEL_DA_MINOREUGUALE AND ID_RENDSEL_A >= :ID_RENDSEL_A_MAGGIOREUGUALE ) ";
            $or = ' OR ';
        } else {
            $or = ' ';
        }

        if (array_key_exists('ANNOCOMPETENZA_OR', $filtri) && $filtri['ANNOCOMPETENZA_OR'] !== null) {
            $this->addSqlParam($sqlParams, "ANNOCOMPETENZA_OR", strtoupper(trim($filtri['ANNOCOMPETENZA_OR'])), PDO::PARAM_INT);
            $sql .= $or . " ANNOCOMPETENZA = :ANNOCOMPETENZA_OR";
        }

        return $sql;
//return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlDeleteBtaRendss($filtri, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_RENDSS
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendssChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['ID'] = $cod;
        return self::getSqlLeggiBtaRendss($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_RENDSS per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaRendssChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendssChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_RENDT

    /**
     * Restituisce comando sql per lettura tabella BTA_RENDT
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendt($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_RENDT.* FROM BTA_RENDT";
        $sql .= $this->addFiltersBtaRendt($filtri, $excludeOrderBy, $sqlParams);

        return $sql;
    }

    /**
     * Restituisce comando sql per count tabella BTA_RENDT
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendtCount($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT COUNT(*) AS CONTA FROM BTA_RENDT";
        $sql .= $this->addFiltersBtaRendt($filtri, $excludeOrderBy, $sqlParams);

        return $sql;
    }

    public function getSqlLeggiBtaRendtAnni($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT DISTINCT BTA_RENDT.ANNOEMI FROM BTA_RENDT";
        $sql .= $this->addFiltersBtaRendt($filtri, $excludeOrderBy, $sqlParams);

        return $sql;
    }

    private function addFiltersBtaRendt($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $where = 'WHERE';

        if (array_key_exists('ID_RENDT', $filtri) && $filtri['ID_RENDT'] != null) {
            $this->addSqlParam($sqlParams, "ID_RENDT", strtoupper(trim($filtri['ID_RENDT'])), PDO::PARAM_INT);
            $sql .= " $where ID_RENDT = :ID_RENDT";
            $where = 'AND';
        }

        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] !== null) {
            $this->addSqlParam($sqlParams, "ANNOEMI", strtoupper(trim($filtri['ANNOEMI'])), PDO::PARAM_INT);
            $sql .= " $where ANNOEMI = :ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('NOMETAB_ORI', $filtri) && $filtri['NOMETAB_ORI'] !== null) {
            $this->addSqlParam($sqlParams, "NOMETAB_ORI", strtoupper(trim($filtri['NOMETAB_ORI'])), PDO::PARAM_STR);
            $sql .= " $where NOMETAB_ORI = :NOMETAB_ORI";
            $where = 'AND';
        }
        if (array_key_exists('KEY_PROG', $filtri) && $filtri['KEY_PROG'] !== null) {
            $this->addSqlParam($sqlParams, "KEY_PROG", strtoupper(trim($filtri['KEY_PROG'])), PDO::PARAM_INT);
            $sql .= " $where KEY_PROG = :KEY_PROG";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY ANNOEMI DESC';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_RENDT
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRendt($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendt($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function leggiBtaRendtAnni($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendtAnni($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce count BTA_RENDT
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function countBtaRendt($filtri, $excludeOrderBy = false) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendtCount($filtri, $excludeOrderBy, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_RENDT
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendtChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['ID_RENDT'] = $cod;
        return self::getSqlLeggiBtaRendt($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_RENDT per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaRendtChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendtChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_REND_ACC

    /**
     * Restituisce comando sql per lettura tabella BTA_REND_ACC
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendAcc($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_REND_ACC.* FROM BTA_REND_ACC";
        $where = 'WHERE';
        if (array_key_exists('PROGIMPACC', $filtri) && $filtri['PROGIMPACC'] != null) {
            $this->addSqlParam($sqlParams, "PROGIMPACC", $filtri['PROGIMPACC'], PDO::PARAM_INT);
            $sql .= " $where PROGIMPACC = :PROGIMPACC";
            $where = 'AND';
        }
        if (array_key_exists('DES_IMP', $filtri) && $filtri['DES_IMP'] != null) {
            $this->addSqlParam($sqlParams, "DESGRNAZ", "%" . strtoupper(trim($filtri['DES_IMP'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DES_IMP") . " LIKE :DES_IMP";
            $where = 'AND';
        }
        if (array_key_exists('ANNORIF', $filtri) && $filtri['ANNORIF'] > 0) {
            $this->addSqlParam($sqlParams, "ANNORIF", $filtri['ANNORIF'], PDO::PARAM_INT);
            $sql .= " $where ANNORIF = :ANNORIF";
            $where = 'AND';
        }
        if (array_key_exists('NUMEROIMP', $filtri) && $filtri['NUMEROIMP'] != null) {
            $this->addSqlParam($sqlParams, "NUMEROIMP", $filtri['NUMEROIMP'], PDO::PARAM_INT);
            $sql .= " $where NUMEROIMP = :NUMEROIMP";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY ANNORIF';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_REND_ACC
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRendAcc($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendAcc($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_REND_ACC
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendAccChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGIMPACC'] = $cod;
        return self::getSqlLeggiBtaRendAcc($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_REND_ACC per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaRendAccChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendAccChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_PRETES

    /**
     * Restituisce comando sql per lettura tabella BTA_PRETES
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaPretes($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_PRETES.* FROM BTA_PRETES";
        $where = 'WHERE';
        if (array_key_exists('PROGTES', $filtri) && $filtri['PROGTES'] != null) {
            $this->addSqlParam($sqlParams, "PROGTES", $filtri['PROGTES'], PDO::PARAM_INT);
            $sql .= " $where PROGTES = :PROGTES";
            $where = 'AND';
        }
        if (array_key_exists('DESTES', $filtri) && $filtri['DESTES'] != null) {
            $percento = strpos($filtri['DESTES'], '%');
            if ($percento == 0 || $percento === false) {
                $this->addSqlParam($sqlParams, "DESTES", trim($filtri['DESTES']), PDO::PARAM_STR);
            } else {
                $this->addSqlParam($sqlParams, "DESTES", trim($filtri['DESTES']) . "%", PDO::PARAM_STR);
            }
            $sql .= " $where DESTES LIKE :DESTES";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGTES desc';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_PRETES
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaPretes($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaPretes($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_PRETES
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaPretesChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGTES'] = $cod;
        return self::getSqlLeggiBtaPretes($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_PRETES per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaPretesChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaPretesChiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_PRESOG

    /**
     * Restituisce comando sql per lettura tabella BTA_PRESOG
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaPresogV01($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_PRESOG_V01.* FROM BTA_PRESOG_V01";
        $where = 'WHERE';
        if (array_key_exists('PROGSOGG', $filtri) && $filtri['PROGSOGG'] != null) {
            $this->addSqlParam($sqlParams, "PROGSOGG", $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where PROGSOGG >= :PROGSOGG";
            $where = 'AND';
        }
        if (array_key_exists('PROGPSO', $filtri) && $filtri['PROGPSO'] != null) {
            $this->addSqlParam($sqlParams, "PROGPSO", $filtri['PROGPSO'], PDO::PARAM_INT);
            $sql .= " $where PROGPSO = :PROGPSO";
            $where = 'AND';
        }
        if (array_key_exists('PROGTES', $filtri) && $filtri['PROGTES'] != null) {
            $this->addSqlParam($sqlParams, "PROGTES", $filtri['PROGTES'], PDO::PARAM_INT);
            $sql .= " $where PROGTES = :PROGTES";
            $where = 'AND';
        }
        if (array_key_exists('NOMINATIVO', $filtri) && $filtri['NOMINATIVO'] != null) {
            $this->addSqlParam($sqlParams, "NOMINATIVO", trim($filtri['NOMINATIVO']) . "%", PDO::PARAM_STR);
            $sql .= " $where NOME_RIC LIKE :NOMINATIVO";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY BTA_PRESOG_V01.PROGSOGG';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_PRESOG
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaPresogV01($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaPresogV01($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_PRESOG
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaPresogV01Chiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGPSO'] = $cod;
        return self::getSqlLeggiBtaPresogV01($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_PRESOG per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaPresogV01Chiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaPresogV01Chiave($cod, $sqlParams), false, $sqlParams);
    }

// BTA_EMI_V01

    /**
     * Restituisce comando sql per lettura tabella BTA_EMI_V01
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaEmiV01($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_EMI_V01.* FROM BTA_EMI_V01";
        $where = 'WHERE';
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] != null) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", $filtri['IDBOL_SERE'], PDO::PARAM_INT);
            $sql .= " $where IDBOL_SERE = :IDBOL_SERE";
            $where = 'AND';
        }
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] != null) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " $where ANNOEMI = :ANNOEMI";
            $where = 'AND';
        }

        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] != null) {
            $this->addSqlParam($sqlParams, "NUMEMI", $filtri['NUMEMI'], PDO::PARAM_INT);
            $sql .= " $where NUMEMI = :NUMEMI";
            $where = 'AND';
        }
        if (array_key_exists('TIPOEMI', $filtri) && $filtri['TIPOEMI'] != null) {
            $this->addSqlParam($sqlParams, "TIPOEMI", $filtri['TIPOEMI'], PDO::PARAM_INT);
            $sql .= " $where TIPOEMI = :TIPOEMI";
            $where = 'AND';
        }
        if (array_key_exists('CODMODULO', $filtri) && $filtri['CODMODULO'] != null) {
            $this->addSqlParam($sqlParams, 'CODMODULO', strtoupper(trim($filtri['CODMODULO'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CODMODULO") . " LIKE :CODMODULO";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDBOL_SERE, ANNOEMI , NUMEMI  DESC';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_EMI_V01
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaEmiV01($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaEmiV01($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

// BTA_EMI_RU

    /**
     * Restituisce comando sql per lettura tabella BTA_EMI_RU
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaEmiRu($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_EMI_RU.* FROM BTA_EMI_RU";
        $where = 'WHERE';
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] != null) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", $filtri['IDBOL_SERE'], PDO::PARAM_INT);
            $sql .= " $where IDBOL_SERE = :IDBOL_SERE";
            $where = 'AND';
        }
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] != null) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " $where ANNOEMI = :ANNOEMI";
            $where = 'AND';
        }

        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] != null) {
            $this->addSqlParam($sqlParams, "NUMEMI", $filtri['NUMEMI'], PDO::PARAM_INT);
            $sql .= " $where NUMEMI = :NUMEMI";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_EMI_RU
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaEmiRu($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaEmiRu($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

//BTA_DURC
    public function getSqlLeggiBtaDurc($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT "
                . "BTA_DURC.*, "
                . "BTA_SOGG.COGNOME, BTA_SOGG.NOME "
                . "FROM BTA_DURC "
                . "LEFT JOIN BTA_SOGG ON BTA_DURC.PROGSOGG = BTA_SOGG.PROGSOGG";
        $where = 'WHERE';

        if (!empty($filtri['PROG_DURC'])) {
            $this->addSqlParam($sqlParams, 'PROG_DURC', $filtri['PROG_DURC'], PDO::PARAM_INT);
            $sql .= " $where BTA_DURC.PROG_DURC = :PROG_DURC";
            $where = 'AND';
        }
        if (!empty($filtri['FLAG_POSIC'])) {
            $this->addSqlParam($sqlParams, 'FLAG_POSIC', $filtri['FLAG_POSIC'], PDO::PARAM_INT);
            $sql .= " $where BTA_DURC.FLAG_POSIC = :FLAG_POSIC";
            $where = 'AND';
        }
        if (!empty($filtri['DES_NOTE'])) {
            $this->addSqlParam($sqlParams, 'DES_NOTE', '%' . strtoupper(trim($filtri['DES_NOTE'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_DURC.DES_NOTE") . " LIKE :DES_NOTE";
            $where = 'AND';
        }
        if (!empty($filtri['PROGSOGG'])) {
            $this->addSqlParam($sqlParams, 'PROGSOGG', $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where BTA_SOGG.PROGSOGG = :PROGSOGG";
            $where = 'AND';
        }
        if (!empty($filtri['RAGSOC'])) {
            $this->addSqlParam($sqlParams, 'RAGSOC', '%' . strtoupper(trim($filtri['RAGSOC'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_SOGG.RAGSOC") . " LIKE :RAGSOC";
            $where = 'AND';
        }
        if (!empty($filtri['DATAFINE_SEARCH'])) {
            $this->addSqlParam($sqlParams, 'DATAFINE', $filtri['DATAFINE_SEARCH'], PDO::PARAM_STR);
            $sql .= " $where BTA_DURC.DATAFINE>:DATAFINE";
            $where = 'AND';
        }
        if (!empty($filtri['CODUTE'])) {
            $this->addSqlParam($sqlParams, 'CODUTE', '%' . strtoupper(trim($filtri['CODUTE'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_DURC.CODUTE") . " LIKE :CODUTE";
            $where = 'AND';
        }
        if (!empty($filtri['FLAG_DIS'])) {
            $this->addSqlParam($sqlParams, 'FLAG_DIS', $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where BTA_DURC.FLAG_DIS = :FLAG_DIS";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY BTA_DURC.PROG_DURC';
        return $sql;
    }

    public function leggiBtaDurc($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaDurc($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBtaDurcChiave($prog, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_DURC'] = $prog;
        return self::getSqlLeggiBtaDurc($filtri, true, $sqlParams);
    }

    public function leggiBtaDurcChiave($prog) {
        if (!$prog) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaDurcChiave($prog, $sqlParams), false, $sqlParams);
    }

// BTA_SOGG

    /**
     * Restituisce comando sql per lettura tabella BTA_SOGG_v04
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaSoggV04($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_SOGG_V04.* FROM BTA_SOGG_V04";
        $where = 'WHERE';
        if (array_key_exists('PROGSOGG', $filtri) && $filtri['PROGSOGG'] != null) {
            $this->addSqlParam($sqlParams, "PROGSOGG", $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where PROGSOGG = :PROGSOGG";
            $where = 'AND';
        }
        if (!empty($filtri['SESSO'])) {
            $this->addSqlParam($sqlParams, "SESSO", $filtri['SESSO'], PDO::PARAM_STR);
            $sql .= " $where SESSO = :SESSO";
            $where = 'AND';
        }
        if (!empty($filtri['TIPOPERS'])) {
            $this->addSqlParam($sqlParams, "TIPOPERS", $filtri['TIPOPERS'], PDO::PARAM_STR);
            $sql .= " $where TIPOPERS = :TIPOPERS";
            $where = 'AND';
        }
        if (array_key_exists('NOME_RIC', $filtri) && $filtri['NOME_RIC'] != null) {
            $this->addSqlParam($sqlParams, "NOME_RIC", strtoupper(trim($filtri['NOME_RIC'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("NOME_RIC") . " LIKE :NOME_RIC ";
            $where = 'AND';
        }
        if (!empty($filtri['CODFISCALE'])) {
            $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CODFISCALE") . " LIKE :CODFISCALE";
            $where = 'AND';
        }
        if (array_key_exists('GIORNO', $filtri) && $filtri['GIORNO'] != null) {
            $this->addSqlParam($sqlParams, "GIORNO", $filtri['GIORNO'], PDO::PARAM_INT);
            $sql .= " $where GIORNO = :GIORNO";
            $where = 'AND';
        }
        if (array_key_exists('MESE', $filtri) && $filtri['MESE'] != null) {
            $this->addSqlParam($sqlParams, "MESE", $filtri['MESE'], PDO::PARAM_INT);
            $sql .= " $where MESE = :MESE";
            $where = 'AND';
        }
        if (array_key_exists('ANNO', $filtri) && $filtri['ANNO'] != null) {
            $this->addSqlParam($sqlParams, "ANNO", $filtri['ANNO'], PDO::PARAM_INT);
            $sql .= " $where ANNO = :ANNO";
            $where = 'AND';
        }
        if (array_key_exists('COGNOME', $filtri) && $filtri['COGNOME'] != null) {
            $this->addSqlParam($sqlParams, "COGNOME", strtoupper(trim($filtri['COGNOME'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("COGNOME") . " LIKE :COGNOME ";
            $where = 'AND';
        }
        if (array_key_exists('NOME', $filtri) && $filtri['NOME'] != null) {
            $this->addSqlParam($sqlParams, "NOME", strtoupper(trim($filtri['NOME'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("NOME") . " LIKE :NOME ";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY COGNOME,NOME ';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SOGG_v04
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaSoggV04($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaSoggV04($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_SOGG_v04
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaSoggV04Chiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['PROGSOGG'] = $cod;
        return self::getSqlLeggiBtaSoggV04($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_SOGG_v04 per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaSoggV04Chiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaSoggV04Chiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce dati tabella utile per ANPR per chiave IDANPRSUBENTRATI
     * @param $cod Chiave 
     * @return query
     */
    public function getSqlleggiBtaAnprSubentratiChiave($cod, &$sqlParams) {
        if (!$cod) {
            return null;
        }
        $filtri = array();
        $filtri['IDANPRSUBENTRATI'] = $cod; //chiave Tabella
        return self::getSqlBtaANPRSubentrati($filtri, $sqlParams);
    }

    /**
     * Restituisce dati tabella utile per ANPR per chiave $cod
     * @param $cod Chiave 
     * @return object Record
     */
    public function leggiBtaAnprSubentratiChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlleggiBtaAnprSubentratiChiave($cod, $sqlParams), false, $sqlParams
        );
    }

// BTA_REND_QUA

    /**
     * Restituisce comando sql per lettura tabella BTA_REND_QUA
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendQua($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT 
                    ID,
                    ID_RENDSEL,
                    " . $this->getCitywareDB()->adapterBlob("ZIP") . ",
                    TIPODATO,
                    ELABORATO,
                    CODUTE,
                    DATAOPER,
                    TIMEOPER
                FROM BTA_REND_QUA";
        $where = 'WHERE';
        if (array_key_exists('ID', $filtri) && $filtri['ID'] != null) {
            $this->addSqlParam($sqlParams, "ID", strtoupper(trim($filtri['ID'])), PDO::PARAM_INT);
            $sql .= " $where ID=:ID";
            $where = 'AND';
        }
        if (array_key_exists('ELABORATO', $filtri) && $filtri['ELABORATO'] !== null) {
            $this->addSqlParam($sqlParams, "ELABORATO", strtoupper(trim($filtri['ELABORATO'])), PDO::PARAM_INT);
            $sql .= " $where ELABORATO=:ELABORATO";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY ID desc';

        return $sql;
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_REND_QUA
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendQuaChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['ID'] = $cod;
        return self::getSqlLeggiBtaRendQua($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_REND_QUA per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaRendQuaChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendQuaChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_REND_QUA
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRendQua($filtri, $multipla = true) {
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BTA", "leggiBtaRendQuaBinary");
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendQua($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

//
//    //risultato del caricamento
    public function leggiBtaRendQuaBinary($result = array()) {
        $sql = "SELECT BTA_REND_QUA.ZIP FROM BTA_REND_QUA WHERE ID = :ID";
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "ID", $result['ID'], PDO::PARAM_INT);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "ZIP", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['ZIP'] = $resultBin['ZIP'];

        return $result;
    }

//    
// BTA_SPRIDD

    /**
     * Restituisce comando sql per lettura tabella BTA_SPRIDD
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaSpriddEmiDati($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT d.*,t.MET_CALC,t.CODTRIBUTO from BTA_SPRIDD d "
                . "left join BTA_SPRIDT t on d.GRUPPO=t.GRUPPO ";
        $where = 'WHERE';
        if (array_key_exists('GRUPPO', $filtri) && $filtri['GRUPPO'] != null) {
            $this->addSqlParam($sqlParams, "GRUPPO", strtoupper(trim($filtri['GRUPPO'])), PDO::PARAM_INT);
            $sql .= " $where d.GRUPPO=:GRUPPO";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SPRIDD
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaSpriddEmiDati($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaSpriddEmiDati($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

// BTA_EMI_CO

    /**
     * Restituisce comando sql per lettura tabella BTA_EMI_CO
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaEmiCo($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_EMI_CO.* FROM BTA_EMI_CO";
        $where = 'WHERE';
        if (array_key_exists('TIPO_EMICO', $filtri) && $filtri['TIPO_EMICO'] != null) {
            $this->addSqlParam($sqlParams, "TIPO_EMICO", $filtri['TIPO_EMICO'], PDO::PARAM_INT);
            $sql .= " $where TIPO_EMICO = :TIPO_EMICO";
            $where = 'AND';
        }
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] != null) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", $filtri['IDBOL_SERE'], PDO::PARAM_INT);
            $sql .= " $where IDBOL_SERE = :IDBOL_SERE";
            $where = 'AND';
        }
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] != null) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " $where ANNOEMI = :ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] != null) {
            $this->addSqlParam($sqlParams, "NUMEMI", $filtri['NUMEMI'], PDO::PARAM_INT);
            $sql .= " $where NUMEMI = :NUMEMI";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_EMI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaEmiCo($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaEmiCo($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BTA_CAB_V01
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaCabV01($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_CAB_V01.* FROM BTA_CAB_V01";
        $where = 'WHERE';
        if (array_key_exists('ABI', $filtri) && $filtri['ABI'] != null) {
            $this->addSqlParam($sqlParams, "ABI", $filtri['ABI'], PDO::PARAM_INT);
            $sql .= " $where ABI = :ABI";
            $where = 'AND';
        }
        if (array_key_exists('CAB', $filtri) && $filtri['CAB'] != null) {
            $this->addSqlParam($sqlParams, "CAB", $filtri['CAB'], PDO::PARAM_INT);
            $sql .= " $where CAB = :CAB";
            $where = 'AND';
        }
        if (array_key_exists('CAB_CIN', $filtri) && $filtri['CAB_CIN'] != null) {
            $this->addSqlParam($sqlParams, "CAB_CIN", $filtri['CAB_CIN'], PDO::PARAM_STR);
            $sql .= " $where CAB_CIN = :CAB_CIN";
            $where = 'AND';
        }
        if (array_key_exists('DES_SPORT', $filtri) && $filtri['DES_SPORT'] != null) {
            $this->addSqlParam($sqlParams, "DES_SPORT", "%" . strtoupper(trim($filtri['DES_SPORT'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DES_SPORT") . " LIKE :DES_SPORT";
            $where = 'AND';
        }
        if (array_key_exists('COMUNEUBIC', $filtri) && $filtri['COMUNEUBIC'] != null) {
            $this->addSqlParam($sqlParams, "COMUNEUBIC", "%" . strtoupper(trim($filtri['COMUNEUBIC'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("COMUNEUBIC") . " LIKE :COMUNEUBIC";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] != null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS = :FLAG_DIS";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY ABI';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_CAB
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaCabV01($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaCabV01($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiValidLocalAnpr($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT COUNT(*) AS CONTA FROM BTA_LOCAL_ANPR";
        return $sql;
    }

// metodo che serve per verificare se � stata fatta la validazione delle localit� da ANPR
    public function leggiValidLocalAnpr() {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiValidLocalAnpr($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // BTA_SOGGFE
    public function getSqlLeggiBtaSoggfe($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT * "
                . "FROM BTA_SOGGFE ";
        $where = 'WHERE';

        if (!empty($filtri['ID_SOGGFE'])) {
            $this->addSqlParam($sqlParams, 'ID_SOGGFE', $filtri['ID_SOGGFE'], PDO::PARAM_INT);
            $sql .= " $where ID_SOGGFE=:ID_SOGGFE";
            $where = 'AND';
        }
        if (!empty($filtri['PROGSOGG'])) {
            $this->addSqlParam($sqlParams, 'PROGSOGG', $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where PROGSOGG=:PROGSOGG";
            $where = 'AND';
        }
        if (!empty($filtri['CODUFF_FE'])) {
            $this->addSqlParam($sqlParams, 'CODUFF_FE', '%' . strtoupper(trim($filtri['CODUFF_FE'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CODUFF_FE") . " LIKE :CODUFF_FE";
            $where = 'AND';
        }
        if (!empty($filtri['DESCRIZIONE'])) {
            $this->addSqlParam($sqlParams, 'DESCRIZIONE', '%' . strtoupper(trim($filtri['DESCRIZIONE'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESCRIZIONE") . " LIKE :DESCRIZIONE";
            $where = 'AND';
        }
        if (!empty($filtri['TIPO_FORMATO'])) {
            $this->addSqlParam($sqlParams, 'TIPO_FORMATO', $filtri['TIPO_FORMATO'], PDO::PARAM_INT);
            $sql .= " $where TIPO_FORMATO=:TIPO_FORMATO";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_01', $filtri)) {
            $this->addSqlParam($sqlParams, 'FLAG_01', $filtri['FLAG_01'], PDO::PARAM_INT);
            $sql .= " $where FLAG_01=:FLAG_01";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_02', $filtri)) {
            $this->addSqlParam($sqlParams, 'FLAG_02', $filtri['FLAG_02'], PDO::PARAM_INT);
            $sql .= " $where FLAG_02=:FLAG_02";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_03', $filtri)) {
            $this->addSqlParam($sqlParams, 'FLAG_03', $filtri['FLAG_03'], PDO::PARAM_INT);
            $sql .= " $where FLAG_03=:FLAG_03";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_04', $filtri)) {
            $this->addSqlParam($sqlParams, 'FLAG_04', $filtri['FLAG_04'], PDO::PARAM_INT);
            $sql .= " $where FLAG_04=:FLAG_04";
            $where = 'AND';
        }
        if (!empty($filtri['E_MAIL_PEC'])) {
            $this->addSqlParam($sqlParams, 'E_MAIL_PEC', '%' . strtoupper(trim($filtri['E_MAIL_PEC'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("E_MAIL_PEC") . " LIKE :E_MAIL_PEC";
            $where = 'AND';
        }
        if (!empty($filtri['CODUTE'])) {
            $this->addSqlParam($sqlParams, 'CODUTE', '%' . strtoupper(trim($filtri['CODUTE'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CODUTE") . " LIKE :CODUTE";
            $where = 'AND';
        }
        if (!empty($filtri['FLAG_DIS'])) {
            $this->addSqlParam($sqlParams, 'FLAG_DIS', $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY ID_SOGGFE';
        return $sql;
    }

    public function leggiBtaSoggfe($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaSoggfe($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBtaSoggfeChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['ID_SOGGFE'] = $cod;
        return self::getSqlLeggiBtaSoggfe($filtri, true, $sqlParams);
    }

    public function leggiBtaSoggfeChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaSoggfeChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_LOCAL_V01
     * @param string $filtri comprende Codice istat da ANPR
     * @param array $multipla  default impostato a true quindi restituisce la lista, altrimenti row
     * @return string Comando sql
     */
    public function LeggiBtaLocalV01($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaLocalV01($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BTA_LOCAL_v01
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaLocalV01($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_LOCAL.*, " . $this->getCitywareDB()->strConcat('BTA_LOCAL.CODNAZPRO', "'|'", 'BTA_LOCAL.CODLOCAL') . " AS \"ROW_ID\" ";
        $sql .= " FROM BTA_LOCAL_V01 BTA_LOCAL ";
        $where = 'WHERE';
        if (array_key_exists('ANPR_ISTAT', $filtri) && $filtri['ANPR_ISTAT'] != null) {
            $this->addSqlParam($sqlParams, "ANPR_ISTAT", $filtri['ANPR_ISTAT'], PDO::PARAM_INT);
            $sql .= " $where " . "BTA_LOCAL.ANPR_ISTAT=:ANPR_ISTAT";
            $where = 'AND';
        }
        if (array_key_exists('CODNAZPRO', $filtri) && intval($filtri['CODNAZPRO']) > -1 && is_numeric($filtri['CODNAZPRO'])) {
            $this->addSqlParam($sqlParams, "CODNAZPRO", $filtri['CODNAZPRO'], PDO::PARAM_INT);
            $sql .= " $where BTA_LOCAL.CODNAZPRO=:CODNAZPRO";
            $where = 'AND';
        }
        if (array_key_exists('CODLOCAL', $filtri) && intval($filtri['CODLOCAL']) > -1 && is_numeric($filtri['CODLOCAL'])) {
            $this->addSqlParam($sqlParams, "CODLOCAL", $filtri['CODLOCAL'], PDO::PARAM_INT);
            $sql .= " $where BTA_LOCAL.CODLOCAL=:CODLOCAL";
            $where = 'AND';
        }
        if (array_key_exists('DESLOCAL', $filtri) && $filtri['DESLOCAL'] != null) {
            $this->addSqlParam($sqlParams, "DESLOCAL", strtoupper(trim($filtri['DESLOCAL'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_LOCAL.DESLOCAL") . " LIKE :DESLOCAL";
            $where = 'AND';
        }
        if (array_key_exists('PROVINCIA', $filtri) && $filtri['PROVINCIA'] != null) {
            $this->addSqlParam($sqlParams, "PROVINCIA", "%" . strtoupper(trim($filtri['PROVINCIA'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_LOCAL.PROVINCIA") . " LIKE :PROVINCIA";
            $where = 'AND';
        }
        if (array_key_exists('CODBELFI', $filtri) && $filtri['CODBELFI'] != null) {
            $this->addSqlParam($sqlParams, "CODBELFI", strtoupper(trim($filtri['CODBELFI'])), PDO::PARAM_STR);
            $sql .= " $where BTA_LOCAL.CODBELFI=:CODBELFI";
            $where = 'AND';
        }

        if ((array_key_exists('DATAFINE', $filtri) && $filtri['DATAFINE'] != null) && (array_key_exists('DATAINIZ', $filtri) && $filtri['DATAINIZ'] != null)) {
            $this->addSqlParam($sqlParams, "DATAINIZ", $filtri['DATAINIZ'], PDO::PARAM_STR);
            $this->addSqlParam($sqlParams, "DATAFINE", $filtri['DATAFINE'], PDO::PARAM_STR);
            $sql .= " $where (DATAINIZ<=:DATAINIZ AND (DATAFINE>=:DATAFINE OR DATAFINE IS NULL))";
            $where = 'AND';
        }

        //gESTIONE CHECK A VIDEO
        if (((array_key_exists('ricComuniItaliani', $filtri)) || (array_key_exists('ricLuogoEccezionale', $filtri)) || array_key_exists('ricLocEstere', $filtri))) {
            if (!(intval($filtri['ricComuniItaliani'] == 0) && intval($filtri['ricLocEstere'] == 0) && intval($filtri['ricLuogoEccezionale']) == 0)) {
                $sql = $sql . ' ' . $where . ' (';
                $where = 'AND';
                $whereOr = '';
                if (array_key_exists('ricComuniItaliani', $filtri) && intval($filtri['ricComuniItaliani'] != 0)) {
                    $sql .= " (BTA_LOCAL.F_ITA_EST=0) ";
                    $whereOr = 'OR';
                }
                if (array_key_exists('ricLocEstere', $filtri) && intval($filtri['ricLocEstere'] != 0)) {
                    if ($whereOr == 'OR') {
                        $sql = $sql . $whereOr;
                    }
                    $sql .= "(BTA_LOCAL.F_ITA_EST=1 AND BTA_LOCAL.F_ECCEZIONALE=0)";
                    $whereOr = 'OR';
                }
                if (array_key_exists('ricLuogoEccezionale', $filtri) && intval($filtri['ricLuogoEccezionale'] != 0)) {
                    if ($whereOr == 'OR') {
                        $sql = $sql . ' ' . $whereOr;
                    }
                    $sql .= " BTA_LOCAL.F_ECCEZIONALE=1 ";
                }
                $sql = $sql . ')';
            }
        }
        if (array_key_exists('ricSoloAttive', $filtri) && $filtri['ricSoloAttive'] != 0) {
            $sql .= " $where BTA_LOCAL.FLAG_DIS=0 AND BTA_LOCAL.DATAFINE IS NULL";
            $where = 'AND';
        }

        if ((array_key_exists('ricSoloValidatiAnpr', $filtri) && $filtri['ricSoloValidatiAnpr'] != 0)) {
            $sql .= " $where BTA_LOCAL.ANPR_VERIFICATO=1 ";
        }

        if ($excludeOrderBy == false) {
            if ($filtri['DESLOCAL']) {
                $sql = $sql . ' ORDER BY BTA_LOCAL.DESLOCAL, BTA_LOCAL.DATAINIZ DESC';
                $SortOrderField = 'DESLOCAL, DATAINIZ';
                $TypeOrderField = 'DESC';
            } else {
                $sql = $sql . ' ORDER BY BTA_LOCAL.ANPR_ISTAT, BTA_LOCAL.CODNAZPRO, BTA_LOCAL.CODLOCAL';
                $SortOrderField = 'ANPR_ISTAT, CODNAZPRO, CODLOCAL';
            }
        }
        return $sql;
    }

    public function getSqlBtaANPRStati($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT * FROM BTA_ANPR_STATI ";
        $sql .= ' WHERE 1=1 ';
        if (array_key_exists('NOME', $filtri) && $filtri['NOME'] != null) {
            $this->addSqlParam($sqlParams, "NOME", strtoupper(trim($filtri['NOME'])), PDO::PARAM_STR);
            $sql .= " and " . $this->getCitywareDB()->strUpper('NOME') . " = :NOME";
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY NOME ';
        return $sql;
    }

    /**
     * Restituisce dati tabella BtaANPRSubentrati
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaANPRStati($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlBtaANPRStati($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce row per lettura per vai campi da notifiche N010 
     * @param string $filtri comprende DESLOCAL, ISTNAZPRO, ISTLOCAL, PROVINCIA, dataDecorrenza
     * @param array $multipla  default impostato a false quindi restituisce la row
     * @return array
     */
    public function LeggiBtaLocalperNotif($filtri, $multipla = false) {
        $sql = "SELECT * FROM BTA_LOCAL ";
        $where = 'WHERE';
        if (array_key_exists('ISTNAZPRO', $filtri) && intval($filtri['ISTNAZPRO']) > -1 && is_numeric($filtri['ISTNAZPRO'])) {
            $this->addSqlParam($sqlParams, "ISTNAZPRO", $filtri['ISTNAZPRO'], PDO::PARAM_INT);
            $sql .= " $where ISTNAZPRO=:ISTNAZPRO";
            $where = 'AND';
        }
        if (array_key_exists('ISTLOCAL', $filtri) && intval($filtri['ISTLOCAL']) > -1 && is_numeric($filtri['ISTLOCAL'])) {
            $this->addSqlParam($sqlParams, "ISTLOCAL", $filtri['ISTLOCAL'], PDO::PARAM_INT);
            $sql .= " $where ISTLOCAL=:ISTLOCAL";
            $where = 'AND';
        }
        if (array_key_exists('DESLOCAL', $filtri) && $filtri['DESLOCAL'] != null) {
            $this->addSqlParam($sqlParams, "DESLOCAL", strtoupper(trim($filtri['DESLOCAL'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESLOCAL") . " =:DESLOCAL";
            $where = 'AND';
        }
        if (array_key_exists('PROVINCIA', $filtri) && $filtri['PROVINCIA'] != null) {
            $this->addSqlParam($sqlParams, "PROVINCIA", strtoupper(trim($filtri['PROVINCIA'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("PROVINCIA") . " =:PROVINCIA";
        }
        if (array_key_exists('DATADEC', $filtri) && $filtri['DATADEC'] != null) {
            $sql .= " AND (datainiz IS NULL OR datainiz <= '" . $filtri['DATADEC']
                    . "') AND (datafine IS NULL OR datafine >=  '" . $filtri['DATADEC'] . "') ";
        }

        return ItaDB::DBQuery($this->getCitywareDB(), $sql, $multipla, $sqlParams);
    }

    // BTA_REND_GEST

    /**
     * Restituisce comando sql per lettura tabella BTA_REND_GEST
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendGest($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_REND_GEST.* FROM BTA_REND_GEST";
        $where = 'WHERE';

        if (array_key_exists('ANNORIF', $filtri) && $filtri['ANNORIF'] != null) {
            $this->addSqlParam($sqlParams, "ANNORIF", $filtri['ANNORIF'], PDO::PARAM_INT);
            $sql .= " $where ANNORIF=:ANNORIF";
            $where = 'AND';
        }
        if (array_key_exists('ANNO_CONTO', $filtri) && $filtri['ANNO_CONTO'] != null) {
            $this->addSqlParam($sqlParams, "ANNOCONTO", $filtri['ANNO_CONTO'], PDO::PARAM_INT);
            $sql .= " $where ANNO_CONTO=:ANNOCONTO";
            $where = 'AND';
        }
        if (array_key_exists('ORDEVA', $filtri) && $filtri['ORDEVA'] != null) {
            $this->addSqlParam($sqlParams, "ORDEVA", $filtri['ORDEVA'], PDO::PARAM_INT);
            $sql .= " $where ORDEVA=:ORDEVA";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY ID';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_REND_GEST
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRendGest($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendGest($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_REND_GEST
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendGestChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['ID'] = $cod;
        return self::getSqlLeggiBtaRendGest($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_REND_GEST per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaRendGestChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendGestChiave($cod, $sqlParams), false, $sqlParams);
    }

    public function leggiUltimoAnnoEmissione($codEmissione) {
        if (!$codEmissione) {
            return null;
        }

        $sql = "SELECT ANNOEMI,NUMEMI FROM BTA_EMI WHERE IDBOL_SERE = $codEmissione ORDER BY DATAOPER DESC,TIMEOPER DESC LIMIT 1";

        return ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);
    }

    public function leggiUltimoNumeratore($annoEmissione, $numeroEmissione, $codiceEmissione) {
        if (!$codiceEmissione || !$annoEmissione || !$numeroEmissione) {
            return null;
        }

        $sql = "SELECT DATACREAZ,bta_emi.COD_NR_D,F_TP_NR_D FROM BTA_EMI INNER JOIN BTA_NRD ON BTA_NRD.COD_NR_D=bta_emi.COD_NR_D 
                WHERE ANNOEMI = $annoEmissione  AND NUMEMI = $numeroEmissione AND IDBOL_SERE = $codiceEmissione";


        return ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);
    }

    public function leggiUltimoDocumento($annoEmissione, $codiceEmissione, $rowEmissione, $settoreIva) {
        if (!$codiceEmissione || !$annoEmissione) {
            return null;
        }

        $sql = "select max (NRD.NUMULTDOC) as MASSIMO from BTA_NRD_AN NRD inner join BOR_IDBOL BOR on NRD.COD_NR_D=BOR.COD_NR_D
                WHERE NRD.ANNOEMI=$annoEmissione and BOR.IDBOL_SERE=$codiceEmissione";

        if ($rowEmissione == 1) {
            $sql .= " AND NRD.SETT_IVA='" . $settoreIva . "'";
        } else {
            $sql .= " AND NRD.SETT_IVA='00'";
        }

        return ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);
    }

    public function contaNumeratore($annoEmissione, $codiceEmissione, $rowEmissione, $settoreIva) {
        if (!$codiceEmissione || !$annoEmissione) {
            return null;
        }

        $sql = "select count(*) as CONTA from BTA_NRD_AN NRD inner join BOR_IDBOL BOR on NRD.COD_NR_D=BOR.COD_NR_D
                WHERE NRD.ANNOEMI=$annoEmissione and BOR.IDBOL_SERE=$codiceEmissione";

        if ($rowEmissione == 1) {
            $sql .= " AND NRD.SETT_IVA='" . $settoreIva . "'";
        } else {
            $sql .= " AND NRD.SETT_IVA='00'";
        }

        return ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);
    }

    public function leggiEmissioniServSociali($codiceEmissione) {
        if (!$codiceEmissione) {
            return null;
        }
        $sql = "SELECT * FROM BTA_EMI_V01 WHERE IDBOL_SERE=$codiceEmissione AND upper(CODMODULO) LIKE 'SBO%' ORDER BY IDBOL_SERE, ANNOEMI  DESC, NUMEMI  DESC";
        return ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);
    }

    // BTA_EMI_RU

    /**
     * Restituisce comando sql per lettura tabella BTA_EMI_RU
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaEmiRuPosContr($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT E.TIPOEMI ,RU.* FROM BTA_EMI_RU RU inner join BTA_EMI E on RU.IDBOL_SERE=E.IDBOL_SERE AND RU.ANNOEMI=E.ANNOEMI AND RU.NUMEMI=E.NUMEMI  where 1=1";
        $where = 'and';
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] != null) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", $filtri['IDBOL_SERE'], PDO::PARAM_INT);
            $sql .= " $where RU.IDBOL_SERE = :IDBOL_SERE";
            $where = 'AND';
        }
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] != null) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " $where RU.ANNOEMI = :ANNOEMI";
            $where = 'AND';
        }

        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] != null) {
            $this->addSqlParam($sqlParams, "NUMEMI", $filtri['NUMEMI'], PDO::PARAM_INT);
            $sql .= " $where RU.NUMEMI = :NUMEMI";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY NUMRUOL DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_EMI_RU
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaEmiRuPosContr($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaEmiRuPosContr($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // BTA_SOGINV (COMUNICAZIONI INVIATE AI SOGGETTI)
    public function getSqlLeggiBtaSoginv($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_SOGINV.*, BTA_SOGG.RAGSOC, BTA_NTNOTE.DESNATURA 
                 FROM BTA_SOGINV
                  LEFT JOIN BTA_SOGG ON BTA_SOGG.PROGSOGG = BTA_SOGINV.PROGSOGG
                  LEFT JOIN BTA_NTNOTE ON BTA_NTNOTE.NATURANOTA = BTA_SOGINV.NATURACOMU";

        $where = 'WHERE';

        if (!empty($filtri['TABLENOTE'])) {
            $this->addSqlParam($sqlParams, 'TABLENOTE', trim($filtri['TABLENOTE']), PDO::PARAM_STR);
            $sql .= " $where BTA_NTNOTE.TABLENOTE=:TABLENOTE";
            $where = 'AND';
        }

        if (!empty($filtri['ID_SOGINV'])) {
            $this->addSqlParam($sqlParams, 'ID_SOGINV', $filtri['ID_SOGINV'], PDO::PARAM_INT);
            $sql .= " $where BTA_SOGINV.ID_SOGINV=:ID_SOGINV";
            $where = 'AND';
        }
        if (!empty($filtri['PROGSOGG'])) {
            $this->addSqlParam($sqlParams, 'PROGSOGG', $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where BTA_SOGINV.PROGSOGG=:PROGSOGG";
            $where = 'AND';
        }
        if (!empty($filtri['RAGSOC'])) {
            $this->addSqlParam($sqlParams, 'RAGSOC', '%' . strtoupper(trim($filtri['RAGSOC'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_SOGG.RAGSOC") . " LIKE :RAGSOC";
            $where = 'AND';
        }
        if (!empty($filtri['NATURACOMU'])) {
            $this->addSqlParam($sqlParams, 'NATURACOMU', strtoupper(trim($filtri['NATURACOMU'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_SOGINV.NATURACOMU") . "=:NATURACOMU";
            $where = 'AND';
        }
        if (isSet($filtri['TIPO_INVIO'])) {
            $this->addSqlParam($sqlParams, "TIPO_INVIO", $filtri['TIPO_INVIO'], PDO::PARAM_INT);
            $sql .= " $where BTA_SOGINV.TIPO_INVIO=:TIPO_INVIO";
            $where = 'AND';
        }
        // DATA INSERT: E' UTILIZZATA ANCHE COME DATA INVIO:
        if (!empty($filtri['DATAINSER_DA'])) {
            $this->addSqlParam($sqlParams, 'DATAINSER_DA', trim($filtri['DATAINSER_DA']), PDO::PARAM_STR);
            $sql .= " $where BTA_SOGINV.DATAINSER >= :DATAINSER_DA";
            $where = 'AND';
        }
        if (!empty($filtri['DATAINSER_A'])) {
            $this->addSqlParam($sqlParams, 'DATAINSER_A', trim($filtri['DATAINSER_A']), PDO::PARAM_STR);
            $sql .= " $where BTA_SOGINV.DATAINSER <= :DATAINSER_A";
            $where = 'AND';
        }

        if (isSet($filtri['FLAG_DIS'])) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where BTA_SOGINV.FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY DATAINSER DESC, TIMEINSER DESC'; // Ordini per data invio(=insert) ex: , PROGSOGG, ID_SOGINV
        return $sql;
    }

    public function leggiBtaSoginv($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaSoginv($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function leggiBtaProvi($filtri, $multipla = false) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaProvi($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBtaProvi($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_PROVI.* FROM BTA_PROVI";
        $where = 'WHERE';
        if (array_key_exists('COD_PR_IST', $filtri) && $filtri['COD_PR_IST'] != null) {
            $this->addSqlParam($sqlParams, "COD_PR_IST", $filtri['COD_PR_IST'], PDO::PARAM_INT);
            $sql .= " $where COD_PR_IST =:COD_PR_IST";
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY COD_PR_IST';
        return $sql;
    }

    // BTA_COMCON

    /**
     * Restituisce comando sql per lettura tabella BTA_COMCON
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaComcon($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_COMCON.*, "
                . $this->getCitywareDB()->strConcat('PROGCOMU', "'|'", 'PROG_INT_C') . " AS \"ROW_ID\" "
                . " FROM BTA_COMCON";
        $where = 'WHERE';
        if (array_key_exists('PROGCOMU', $filtri) && $filtri['PROGCOMU'] != null) {
            $this->addSqlParam($sqlParams, "PROGCOMU", strtoupper(trim($filtri['PROGCOMU'])), PDO::PARAM_INT);
            $sql .= " $where PROGCOMU=:PROGCOMU";
            $where = 'AND';
        }
        if (array_key_exists('PROG_INT_C', $filtri) && $filtri['PROG_INT_C'] != null) {
            $this->addSqlParam($sqlParams, "PROG_INT_C", strtoupper(trim($filtri['PROG_INT_C'])), PDO::PARAM_INT);
            $sql .= " $where PROG_INT_C=:PROG_INT_C";
            $where = 'AND';
        }
        if (array_key_exists('TIPO_COM', $filtri) && $filtri['TIPO_COM'] != null) {
            $this->addSqlParam($sqlParams, "TIPO_COM", strtoupper(trim($filtri['TIPO_COM'])), PDO::PARAM_INT);
            $sql .= " $where TIPO_COM=:TIPO_COM";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGCOMU DESC ,PROG_INT_C ASC';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_COMCON
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaComcon($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaComcon($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_COMCON
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaComconChiave($progcomu, $progint, &$sqlParams) {
        $filtri = array();
        $filtri['PROGCOMU'] = $progcomu;
        $filtri['PROG_INT_C'] = $progint;
        return self::getSqlLeggiBtaComcon($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_COMCON per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaComconChiave($cod) {
        if (!$cod) {
            return null;
        }
        $keys = explode("|", $cod);
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaComconChiave($keys[0], $keys[1], $sqlParams), false, $sqlParams);
    }

    // BTA_COM_IN

    /**
     * Restituisce comando sql per lettura tabella BTA_COM_IN
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaComIn($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_COM_IN.*, "
                . $this->getCitywareDB()->strConcat('PROGCOMU', "'|'", 'PROG_INT_C') . " AS \"ROW_ID\" "
                . " FROM BTA_COM_IN";
        $where = 'WHERE';
        if (array_key_exists('PROGCOMU', $filtri) && $filtri['PROGCOMU'] != null) {
            $this->addSqlParam($sqlParams, "PROGCOMU", strtoupper(trim($filtri['PROGCOMU'])), PDO::PARAM_INT);
            $sql .= " $where PROGCOMU=:PROGCOMU";
            $where = 'AND';
        }
        if (array_key_exists('PROG_INT_C', $filtri) && $filtri['PROG_INT_C'] != null) {
            $this->addSqlParam($sqlParams, "PROG_INT_C", strtoupper(trim($filtri['PROG_INT_C'])), PDO::PARAM_INT);
            $sql .= " $where PROG_INT_C=:PROG_INT_C";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGCOMU DESC ,PROG_INT_C ASC';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_COM_IN
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaComIn($filtri = array(), $multipla = true) {
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BTA", "leggiBtaComInBinary");
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaComIn($filtri, false, $sqlParams), $multipla, $sqlParams, $infoBinaryCallback);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_COM_IN
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaComInChiave($progcomu, $progint, &$sqlParams) {
        $filtri = array();
        $filtri['PROGCOMU'] = $progcomu;
        $filtri['PROG_INT_C'] = $progint;
        return self::getSqlLeggiBtaComIn($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_COM_IN per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaComInChiave($cod) {
        if (!$cod) {
            return null;
        }

        $keys = explode("|", $cod);
        $filtri = array();
        $filtri['PROGCOMU'] = $keys[0];
        $filtri['PROG_INT_C'] = $keys[1];
        return $this->leggiBtaComIn($filtri, false);
    }

    public function leggiBtaComInBinary($result = array()) {
        $sql = "SELECT " . $this->getCitywareDB()->adapterBlob("CORPOTESTO") . " FROM BTA_COM_IN WHERE PROGCOMU = :PROGCOMU AND PROG_INT_C = :PROG_INT_C";
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "PROGCOMU", $result['PROGCOMU'], PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "PROG_INT_C", $result['PROG_INT_C'], PDO::PARAM_INT);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "CORPOTESTO", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['CORPOTESTO'] = $resultBin['CORPOTESTO'];

        return $result;
    }

    // BTA_COM_IN

    /**
     * Restituisce comando sql per lettura tabella BTA_COM_RI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaComRi($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_COM_RI.*, "
                . $this->getCitywareDB()->strConcat('PROGCOMU', "'|'", 'PROG_INT_C') . " AS \"ROW_ID\" "
                . " FROM BTA_COM_RI";
        $where = 'WHERE';
        if (array_key_exists('PROGCOMU', $filtri) && $filtri['PROGCOMU'] != null) {
            $this->addSqlParam($sqlParams, "PROGCOMU", strtoupper(trim($filtri['PROGCOMU'])), PDO::PARAM_INT);
            $sql .= " $where PROGCOMU=:PROGCOMU";
            $where = 'AND';
        }
        if (array_key_exists('PROG_INT_C', $filtri) && $filtri['PROG_INT_C'] != null) {
            $this->addSqlParam($sqlParams, "PROG_INT_C", strtoupper(trim($filtri['PROG_INT_C'])), PDO::PARAM_INT);
            $sql .= " $where PROG_INT_C=:PROG_INT_C";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGCOMU DESC ,PROG_INT_C ASC';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_COM_RI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaComRi($filtri = array(), $multipla = true) {
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BTA", "leggiBtaComRiBinary");

        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaComRi($filtri, false, $sqlParams), $multipla, $sqlParams, $infoBinaryCallback);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_COM_RI
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaComRiChiave($progcomu, $progint, &$sqlParams) {
        $filtri = array();
        $filtri['PROGCOMU'] = $progcomu;
        $filtri['PROG_INT_C'] = $progint;
        return self::getSqlLeggiBtaComRi($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_COM_RI per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaComRiChiave($cod) {
        if (!$cod) {
            return null;
        }
        $keys = explode("|", $cod);
        $filtri = array();
        $filtri['PROGCOMU'] = $keys[0];
        $filtri['PROG_INT_C'] = $keys[1];
        return $this->leggiBtaComRi($filtri, false);
    }

    public function leggiBtaComRiBinary($result = array()) {
        $sql = "SELECT " . $this->getCitywareDB()->adapterBlob("CORPOTESTO") . " FROM BTA_COM_RI WHERE PROGCOMU = :PROGCOMU AND PROG_INT_C = :PROG_INT_C";
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "PROGCOMU", $result['PROGCOMU'], PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "PROG_INT_C", $result['PROG_INT_C'], PDO::PARAM_INT);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "CORPOTESTO", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['CORPOTESTO'] = $resultBin['CORPOTESTO'];

        return $result;
    }

//  --------------------------------------------------  
//  Legge tabella allegati DURC: BTA_DURCAL
//  --------------------------------------------------
    public function leggiBtaDurcal($filtri, $multipla = true) {
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BTA", "leggiBtaDurcalBinary");
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiCountDeleteBtaDurcal($filtri, false, false, false, $sqlParams), $multipla, $sqlParams, $infoBinaryCallback);
    }

    //risultato del caricamento
    public function leggiBtaDurcalBinary($result = array()) {
        $sql = "SELECT " . $this->getCitywareDB()->adapterBlob("TESTOATTO") . " FROM BTA_DURCAL WHERE PROG_DURC = :PROG_DURC"
                . " AND RIGA_DURC = :RIGA_DURC ";
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "PROG_DURC", $result['PROG_DURC'], PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "RIGA_DURC", $result['RIGA_DURC'], PDO::PARAM_INT);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "TESTOATTO", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['TESTOATTO'] = $resultBin['TESTOATTO'];

        return $result;
    }

//  Query particolare per Gestione Soggetti x vedere se ci sono degli allegati
    public function getSqlLeggiCountDeleteBtaDurcal($filtri, $count = false, $delete = false, $excludeOrderBy = false, &$sqlParams = array()) {

        if ($delete) {
            $sql = 'DELETE ';
        } else {
            $sql = 'SELECT ';

            if ($count) {
                $sql .= 'COUNT(*) AS NUM_ALLEG ';
            } else {
                $sql .= 'PROG_DURC, RIGA_DURC, DES_ALLEG, QUALIFICA, NOME_ALLEG, '
                        . 'CODUTEINS, DATAINSER, TIMEINSER, CODUTE, DATAOPER, TIMEOPER, FLAG_DIS, UUID, '
                        . $this->getCitywareDB()->adapterBlob("TESTOATTO") . ' ';
            }
        }

        $sql .= 'FROM BTA_DURCAL ';

        $where = 'WHERE';
        if (!empty($filtri['PROG_DURC'])) {
            $this->addSqlParam($sqlParams, 'PROG_DURC', $filtri['PROG_DURC'], PDO::PARAM_INT);
            $sql .= " $where BTA_DURCAL.PROG_DURC = :PROG_DURC";
            $where = "AND";
        }
        if (!empty($filtri['RIGA_DURC'])) {
            $this->addSqlParam($sqlParams, 'RIGA_DURC', $filtri['RIGA_DURC'], PDO::PARAM_INT);
            $sql .= " $where BTA_DURCAL.RIGA_DURC = :RIGA_DURC";
            $where = "AND";
        }
        if (!empty($filtri['UUID'])) {
            $this->addSqlParam($sqlParams, 'UUID', strtoupper(trim($filtri['UUID'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper('BTA_DURCAL.UUID') . " = :UUID";
            $where = 'AND';
        }

        return $sql;
    }

    public function getSqlBtaLocalAllaData($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_ANPR_COMUNI.* FROM BTA_ANPR_COMUNI";
        $sql .= " LEFT JOIN BTA_LOCAL_ANPR BTA_LOCAL_ANPR on BTA_ANPR_COMUNI.id_comuanpr = BTA_LOCAL_ANPR.id_comuanpr "
                . " LEFT JOIN BTA_LOCAL BTA_LOCAL ON BTA_LOCAL_ANPR.CODNAZPRO = BTA_LOCAL.CODNAZPRO AND BTA_LOCAL_ANPR.CODLOCAL = BTA_LOCAL.CODLOCAL ";
        $sql .= ' WHERE BTA_ANPR_COMUNI.ID_COMUANPR>0  ';
        if (array_key_exists('CODNAZPRO', $filtri) && $filtri['CODNAZPRO'] != null) {
            $this->addSqlParam($sqlParams, "CODNAZPRO", $filtri['CODNAZPRO'], PDO::PARAM_INT);
            $sql .= " and BTA_LOCAL.CODNAZPRO=:CODNAZPRO";
        }
        if (array_key_exists('CODLOCAL', $filtri) && $filtri['CODLOCAL'] != null) {
            $this->addSqlParam($sqlParams, "CODLOCAL", $filtri['CODLOCAL'], PDO::PARAM_INT);
            $sql .= " and BTA_LOCAL.CODLOCAL=:CODLOCAL";
        }
        if (array_key_exists('DATA', $filtri) && $filtri['DATA'] != null) {
            $this->addSqlParam($sqlParams, "DATA", $filtri['DATA'], PDO::PARAM_STR);
            $sql .= " and :DATA BETWEEN BTA_ANPR_COMUNI.DATAINIZ AND BTA_ANPR_COMUNI.DATAFINE";
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY BTA_ANPR_COMUNI.NOME_IT ';
        return $sql;
    }

    /**
     * Restituisce dati tabella BtaANPRSubentrati
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaLocalAllaData($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlBtaLocalAllaData($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // BTA_RENDD

    /**
     * Restituisce comando sql per lettura tabella BTA_RENDD
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRendd($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_RENDD.* FROM BTA_RENDD";
        $sql .= $this->addFiltersBtaRendd($filtri, $sqlParams);
        $sql .= $excludeOrderBy ? '' : ' ORDER BY ANNORIF DESC';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_RENDD
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRendd($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRendd($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_RENDD
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRenddChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['ID_RENDD'] = $cod;
        return self::getSqlLeggiBtaRendd($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_RENDD per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBtaRenddChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRenddChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BTA_RENDD
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaRenddAnnirif($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT DISTINCT BTA_RENDD.ANNORIF FROM BTA_RENDD";
        $sql .= $this->addFiltersBtaRendd($filtri, $sqlParams);
        $sql .= $excludeOrderBy ? '' : ' ORDER BY ANNORIF DESC';

        return $sql;
    }

    private function addFiltersBtaRendd($filtri, &$sqlParams = array()) {
        $where = 'WHERE';
        $sql = '';
        if (array_key_exists('ID_RENDD', $filtri) && $filtri['ID_RENDD'] != null) {
            $this->addSqlParam($sqlParams, "ID_RENDD", $filtri['ID_RENDD'], PDO::PARAM_INT);
            $sql .= " $where ID_RENDD=:ID_RENDD";
            $where = 'AND';
        }
        if (array_key_exists('ID_RENDT', $filtri) && $filtri['ID_RENDT'] != null) {
            $this->addSqlParam($sqlParams, "ID_RENDT", $filtri['ID_RENDT'], PDO::PARAM_INT);
            $sql .= " $where ID_RENDT=:ID_RENDT";
            $where = 'AND';
        }
        if (array_key_exists('ANNORIF', $filtri) && $filtri['ANNORIF'] != null) {
            $this->addSqlParam($sqlParams, "ANNORIF", $filtri['ANNORIF'], PDO::PARAM_INT);
            $sql .= " $where ANNORIF=:ANNORIF";
            $where = 'AND';
        } else if (array_key_exists('ANNORIF_MAGUG', $filtri) && $filtri['ANNORIF_MAGUG'] != null) {
            $this->addSqlParam($sqlParams, "ANNORIF_MAGUG", $filtri['ANNORIF_MAGUG'], PDO::PARAM_INT);
            $sql .= " $where ANNORIF>=:ANNORIF_MAGUG";
            $where = 'AND';
        }

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_RENDD
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaRenddAnnirif($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaRenddAnnirif($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function leggiBtaLocalWS($filtri, $multipla = true, $da = '', $per = '') {
        return ItaDB::DBSQLSelect($this->getCitywareDB(), $this->getSqlLeggiBtaLocal($filtri, false, $sqlParams), $multipla, $da, $per, $sqlParams);
    }

    public function getSqlLeggiBtaNciviWS($filtri, $multipla = true, $da = '', $per = '') {
        return ItaDB::DBSQLSelect($this->getCitywareDB(), $this->getSqlLeggiBtaNcivi($filtri, false, $sqlParams), $multipla, $da, $per, $sqlParams);
    }

    // BTA_SOGG

    /**
     * Restituisce comando sql per lettura tabella BTA_SOGG formattata per fare match con la tabella bge_agid_soggetti
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBtaSoggPPA($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_SOGG_V01.PROGSOGG,BTA_SOGG_V01.TIPOPERS,BTA_SOGG_V01.NOME,BTA_SOGG_V01.COGNOME,"
                . "BTA_SOGG_V01.GIORNO,BTA_SOGG_V01.MESE,BTA_SOGG_V01.ANNO,BTA_SOGG_V01.CODFISCALE,"
                . "BTA_SOGG_V01.PARTIVA,BTA_SOGG_V01.DESLOCAL LUOGONASC,"
                . $this->getCitywareDB()->strConcat('BTA_SOGG_V01.COGNOME', "' '", 'BTA_SOGG_V01.NOME') . " RAGSOC "
//                . " BTA_SOGG_V01.CAP_RES CAPRESID,"
//                . " (" . $this->getCitywareDB()->strConcat($this->getCitywareDB()->strTrim('BTA_SOGG_V01.DESVIA_RES'), "' '", 'BTA_SOGG_V01.NUMCIV_RES') . ") INDIRIZZORESID, "
//                . ",BTA_SOGG_V01.DESLOCAL_RES COMUNERESID ,BTA_SOGG_V01.PROVINCIA_RES PROVINCIARESID"
                . " FROM BTA_SOGG_V01 ";
        $where = 'WHERE';
        if (array_key_exists('PROGSOGG', $filtri) && $filtri['PROGSOGG'] != null) {
            $this->addSqlParam($sqlParams, "PROGSOGG", $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where PROGSOGG=:PROGSOGG";
            $where = ' AND ';
        }
        if (array_key_exists('CODFISCALE', $filtri) && $filtri['CODFISCALE'] != null) {
            $this->addSqlParam($sqlParams, "CODFISCALE", strtoupper(trim($filtri['CODFISCALE'])), PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('CODFISCALE') . " = :CODFISCALE";
            $where = ' AND ';
        }
        if (array_key_exists('PARTIVA', $filtri) && $filtri['PARTIVA'] != null) {
            $this->addSqlParam($sqlParams, "PARTIVA", strtoupper(trim($filtri['PARTIVA'])), PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('PARTIVA') . " = :PARTIVA";
            $where = ' AND ';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY COGNOME,NOME ';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SOGG
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaSoggPPA($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaSoggPPA($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_SOGG
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaSoggPPAChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['PROGSOGG'] = $cod;
        return self::getSqlLeggiBtaSoggPPA($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_SOGG per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaSoggPPAChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaSoggPPAChiave($cod, $sqlParams), false, $sqlParams);
    }

}
