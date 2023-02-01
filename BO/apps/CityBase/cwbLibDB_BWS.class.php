<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_CITYWARE.class.php';

/**
 *
 * Utility DB Cityware (Modulo BWE)
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Stefano Guidetti <s.guidetti@palinformatica.it>
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
class cwbLibDB_BWS extends cwbLibDB_CITYWARE {
    // BWS_ANPR_CERTI

    /**
     * Restituisce comando sql per lettura tabella BWE_FRMPAR
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBwsAnprCerti($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT * FROM BWS_ANPR_CERTI";
        $sql .= ' WHERE 1=1 ';
        if (array_key_exists('IDANPR_CERTI', $filtri) && $filtri['IDANPR_CERTI'] != null) {
            $this->addSqlParam($sqlParams, "IDANPR_CERTI", $filtri['IDANPR_CERTI'], PDO::PARAM_INT);
            $sql .= " and  IDANPR_CERTI=:IDANPR_CERTI";
        }
        if (array_key_exists('TERMINALE', $filtri) && $filtri['TERMINALE'] != null) {
            $this->addSqlParam($sqlParams, "TERMINALE", $filtri['TERMINALE'], PDO::PARAM_STR);
            $sql .= " and  TERMINALE=:TERMINALE";
        }
        if (array_key_exists('CODUTE', $filtri) && $filtri['CODUTE'] != null) {
            $this->addSqlParam($sqlParams, "CODUTE", strtoupper(trim($filtri['CODUTE'])), PDO::PARAM_STR);
            $sql .= " and  " . $this->getCitywareDB()->strUpper("CODUTE") . "=:CODUTE";
        }
        if (array_key_exists('PATHDOC', $filtri) && $filtri['PATHDOC'] != null) {
            $this->addSqlParam($sqlParams, "PATHDOC", $filtri['PATHDOC'], PDO::PARAM_STR);
            $sql .= " and  PATHDOC=:PATHDOC";
        }
        if (array_key_exists('DATAINIZ', $filtri) && $filtri['DATAINIZ'] != null) {
            $this->addSqlParam($sqlParams, "DATAINIZ", $filtri['DATAINIZ'], PDO::PARAM_STR);
            $sql .= " and  DATAINIZ<=:DATAINIZ";
        }
        if (array_key_exists('DATAFINE', $filtri) && $filtri['DATAFINE'] != null) {
            $this->addSqlParam($sqlParams, "DATAFINE", $filtri['DATAFINE'], PDO::PARAM_STR);
            $sql .= " and  DATAFINE>=:DATAFINE";
        }
        if (array_key_exists('F_CERT_SRV', $filtri) && $filtri['F_CERT_SRV'] != null) {
            $this->addSqlParam($sqlParams, "F_CERT_SRV", $filtri['F_CERT_SRV'], PDO::PARAM_INT);
            $sql .= " and  F_CERT_SRV=:F_CERT_SRV";
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] == 0) { // 
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " and  FLAG_DIS=:FLAG_DIS";
        }
        if (array_key_exists('F_ANPR_AMBIENTE', $filtri) && $filtri['F_ANPR_AMBIENTE'] != null) {
            $this->addSqlParam($sqlParams, "F_ANPR_AMBIENTE", $filtri['F_ANPR_AMBIENTE'], PDO::PARAM_INT);
            $sql .= " and  F_ANPR_AMBIENTE=:F_ANPR_AMBIENTE";
        }
        if (array_key_exists('F_ANPR_AMBIENTE_TEST', $filtri) && $filtri['F_ANPR_AMBIENTE_TEST'] != null) {
            $sql .= " and  F_ANPR_AMBIENTE<3";
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDANPR_CERTI';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWS_ANPR_CERTI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBwsAnprCerti($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBwsAnprCerti($filtri, true, $sqlParams), $flMultipla, $sqlParams);
    }

    public function getSqlLeggiBwsAnprCertiChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['IDANPR_CERTI'] = $cod;
        return self::getSqlLeggiBwsAnprCerti($filtri, true, $sqlParams);
    }

    public function leggiBwsAnprCertiChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBwsAnprCertiChiave($cod, $sqlParams), false, $sqlParams);
    }

    public function leggiBwsLogChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBwsLogChiave($cod, $sqlParams), false, $sqlParams);
    }

    public function getSqlLeggiBwsLogChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['KLOG'] = $cod;
        return self::getSqlLeggiBwsLog($filtri, true, $sqlParams);
    }

    public function getSqlLeggiBwsLog($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT * FROM BWS_LOG";
        $sql .= ' WHERE 1=1 ';
        if (array_key_exists('KLOG', $filtri) && $filtri['KLOG'] != null) {
            $this->addSqlParam($sqlParams, "KLOG", $filtri['KLOG'], PDO::PARAM_INT);
            $sql .= "and KLOG=:KLOG";
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY KLOG';
        return $sql;
    }

    public function leggiBwsProtextChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBwsProtextChiave($cod, $sqlParams), false, $sqlParams);
    }

    public function getSqlLeggiBwsProtextChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['ID'] = $cod;
        return self::getSqlLeggiBwsProtext($filtri, true, $sqlParams);
    }

    public function getSqlLeggiBwsProtext($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BWS_PROTEXT.* FROM BWS_PROTEXT";
        $where = ' WHERE ';

        if (array_key_exists('PROGENTE', $filters) && $filters['PROGENTE'] != null) {
            $this->addSqlParam($sqlParams, "PROGENTE", $filters['PROGENTE'], PDO::PARAM_INT);
            $sql .= $where . " PROGENTE = :PROGENTE ";
            $where = ' AND ';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY ID';
        return $sql;
    }

}

?>