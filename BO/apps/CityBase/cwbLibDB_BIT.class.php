<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_CITYWARE.class.php';

/**
 *
 * Utility DB Cityware (Modulo BDI)
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Siliva Rivi <s.rivi@palinformatica.it>
 * 
 */
class cwbLibDB_BIT extends cwbLibDB_CITYWARE {

    /**
     * Restituisce comando sql per lettura tabella BGE_TESTI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBitIterTesti($filtri, $excludeOrderBy = false, &$sqlParams, $orderBy = '') {
        /* Prima del 29-04-2019
         *       $sql = "SELECT pt.codtesto, t.destesto, t.flag_dis, i.coditer, ip.codpasso, 
         *          i.desiter FROM bit_iter i 
         * INNER  JOIN bit_iterp  ip  ON i.coditer = ip.coditer
         * INNER JOIN bit_iterpt pt ON i.coditer = pt.coditer AND ip.codpasso = pt.codpasso
         * INNER JOIN bge_testi t ON  pt.codtesto = t.codtesto ";
         */
        $sql = "SELECT  pt.codtesto, t.destesto, MIN(ip.seqpassi) as minseqpassi 
            FROM bit_iter i 
            INNER  JOIN bit_iterp  ip  ON i.coditer = ip.coditer 
            INNER JOIN bit_iterpt pt ON i.coditer = pt.coditer AND ip.codpasso = pt.codpasso 
            INNER JOIN bge_testi t ON  pt.codtesto = t.codtesto ";
        $where = ' WHERE ';
        if (array_key_exists('CODITER', $filtri) && $filtri['CODITER'] != null) {
            $this->addSqlParam($sqlParams, "CODITER", $filtri['CODITER'], PDO::PARAM_INT);
            $sql .= " $where i.CODITER=:CODITER";
            $where = 'AND';
        }

        if (isset($filtri['FLAG_DIS'])) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where t.FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }

        $sql .= " GROUP BY  pt.codtesto, t.destesto ";
        $sql .= strlen($orderBy) > 0 ? ' ORDER BY ' . $orderBy : ' ORDER BY  MIN(ip.seqpassi) ';
        return $sql;
    }

    /**
     * Restituisce dati tabella BIT* per la presentazione dei testi legati 
     * all'iter scelto per chiave CODITER
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBitIterTesti($filtri, $orderBy = '') {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBitIterTesti($filtri, false, $sqlParams, $orderBy), true, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura dati tabella BIT* per la presentazione dei testi legati 
     * all'iter scelto per chiave CODITER 
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBitIterTestiChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['CODITER'] = $cod;
        return self::getSqlLeggiBitIterTesti($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BIT_ITER
     * all'iter scelto per chiave CODITER
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBitIterTestiChiave($cod) {
        if ($cod < 1) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBitIterTestiChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BIT_ITER
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBitIter($filtri, $excludeOrderBy = false, &$sqlParams, $orderBy = '') {
        $sql = "SELECT  i.*  FROM bit_iter i ";
        $where = ' WHERE ';
        if (array_key_exists('CODITER', $filtri) && $filtri['CODITER'] != null) {
            $this->addSqlParam($sqlParams, "CODITER", $filtri['CODITER'], PDO::PARAM_INT);
            $sql .= " $where i.CODITER=:CODITER";
            $where = 'AND';
        }

        if (isset($filtri['FLAG_DIS'])) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where t.FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }
        
        $sql .= strlen($orderBy) > 0 ? ' ORDER BY ' . $orderBy : ' ORDER BY  CODITER, DESITER ';
        return $sql;
    }

    /**
     * Restituisce dati tabella BIT_ITER
     * all'iter scelto per chiave CODITER
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBitIter($filtri, $orderBy = '') {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBitIter($filtri, false, $sqlParams, $orderBy), true, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura dati tabella BIT_ITER
     * all'iter scelto per chiave CODITER 
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBitIterChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['CODITER'] = $cod;
        return self::getSqlLeggiBitIter($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BIT* per la presentazione dei testi legati 
     * all'iter scelto per chiave CODITER
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBitIterChiave($cod) {
        if ($cod < 1) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBitIterChiave($cod, $sqlParams), false, $sqlParams);
    }

}

?>