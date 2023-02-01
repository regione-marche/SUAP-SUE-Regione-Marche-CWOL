<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_CITYWARE.class.php';

/**
 *
 * Utility DB Cityware (Modulo BUE)
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Lorenzo Pergolini Massimo Biagioli <m.biagioli@palinformatica.it>
 * 
 */
class cwbLibDB_BUE extends cwbLibDB_CITYWARE {
    // BUE_UT_ELD

    /**
     * Restituisce comando sql per lettura tabella BUE_UT_ELD per funzione Rilevazione Soggetto
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueUtEldRilSogg($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT d.*,n.TOPONIMO AS TOPONIMO_DEC,n.DESVIA AS DESVIA_DEC,n.NUMCIV AS NUMCIV_DEC,n.SUBNCIV AS SUBNCIV_DEC, t.* FROM BUE_UT_ELD d LEFT JOIN BUE_UT_EL t ON d.PROG_UT_ES=t.PROG_UT_ES"
                . " LEFT JOIN BTA_NCIVI_V01 n ON d.PROGNCIV=n.PROGNCIV WHERE t.FLAG_DIS=0";
        $where = 'and';
        if (array_key_exists('PROGSOGG', $filtri) && intval($filtri['PROGSOGG']) > 0) {
            if (trim($filtri['CODFISCALE']) === '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $sql .= " $where d.PROGSOGG=:PROGSOGG";
                $where = 'AND';
            }
            if (trim($filtri['CODFISCALE']) <> '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where (d.PROGSOGG=:PROGSOGG OR d.CODFISCALE LIKE :CODFISCALE)";
                $where = 'AND';
            }
        } else {
            if (trim($filtri['CODFISCALE']) <> '' && trim($filtri['NOMINATIVO']) === '') {
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where d.CODFISCALE LIKE :CODFISCALE";
            }
        }
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV'] > 0) {
            $this->addSqlParam($sqlParams, "PROGNCIV", strtoupper(trim($filtri['PROGNCIV'])), PDO::PARAM_INT);
            $sql .= " $where d.PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES'] > 0) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", strtoupper(trim($filtri['PROG_UT_ES'])), PDO::PARAM_INT);
            $sql .= " $where d.PROG_UT_ES=:PROG_UT_ES";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY d.ANNORIF, d.MESIFATT DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_UT_ELD
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBueUtEldRilSogg($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueUtEldRilSogg($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // TBA_DTANNTASI

    /**
     * Restituisce comando sql per lettura tabella TBA_DTANNTASI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueUtEld($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BUE_UT_ELD.* FROM BUE_UT_ELD";
        $where = 'WHERE';
        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES'] != 0) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", trim($filtri['PROG_UT_ES']), PDO::PARAM_INT);
            $sql .= " $where PROG_UT_ES = :PROG_UT_ES";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROG_UT_ES';

        return $sql;
    }

    /**
     * Restituisce dati tabella TBA_DTANNTASI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBueUtEld($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueUtEld($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * 
     * Restituisce comando sql per lettura per chiave da tabella BUE_UT_ELD
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueUtEldChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_UT_ES'] = $cod;
        return self::getSqlLeggiBueUtEldRilSogg($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BUE_UT_ELD per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBueUtEldChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueUtEldChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BUE_UT_EL

    public function getSqlCountBueUtEl($filtri, &$sqlParams = array()) {
        $sql = "SELECT COUNT(*) AS CONTA FROM BUE_UT_EL";
        $where = 'WHERE';
        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES'] != 0) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", $filtri['PROG_UT_ES'], PDO::PARAM_INT);
            $sql .= " $where PROG_UT_ES = :PROG_UT_ES";
            $where = 'AND';
        }
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV']) {
            $this->addSqlParam($sqlParams, "PROGNCIV", $filtri['PROGNCIV'], PDO::PARAM_INT);
            $sql .= " $where PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri)) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }

        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_UT_EL
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function countBueUtEl($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlCountBueUtEl($filtri, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BUE_UT_EL
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueUtEl($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BUE_UT_EL.* FROM BUE_UT_EL";
        $where = 'WHERE';
        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES'] != 0) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", $filtri['PROG_UT_ES'], PDO::PARAM_INT);
            $sql .= " $where PROG_UT_ES = :PROG_UT_ES";
            $where = 'AND';
        }
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV']) {
            $this->addSqlParam($sqlParams, "PROGNCIV", $filtri['PROGNCIV'], PDO::PARAM_INT);
            $sql .= " $where PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri)) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROG_UT_ES';

        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_UT_EL
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBueUtEl($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueUtEl($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * 
     * Restituisce comando sql per lettura per chiave da tabella BUE_UT_EL
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueUtElChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_UT_ES'] = $cod;
        return self::getSqlLeggiBueUtEl($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BUE_UT_EL per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBueUtElChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueUtElChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BUE_GASD

    /**
     * Restituisce comando sql per lettura tabella BUE_GASD per funzione Rilevazione Soggetto
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueGasdRilSogg($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT d.*,n.TOPONIMO AS TOPONIMO_DEC,n.DESVIA AS DESVIA_DEC,n.NUMCIV AS NUMCIV_DEC,n.SUBNCIV AS SUBNCIV_DEC,n.DES_BREVE AS DESBREVE_DEC, t.* FROM BUE_GASD d LEFT JOIN BUE_GAS t ON d.PROG_UT_ES=t.PROG_UT_ES"
                . " LEFT JOIN BTA_NCIVI_V01 n ON d.PROGNCIV=n.PROGNCIV WHERE t.FLAG_DIS=0";
        $where = 'and';
        if (array_key_exists('PROGSOGG', $filtri) && intval($filtri['PROGSOGG']) > 0) {
            if (trim($filtri['CODFISCALE']) === '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $sql .= " $where d.PROGSOGG=:PROGSOGG";
                $where = 'AND';
            }
            if (trim($filtri['CODFISCALE']) <> '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where (d.PROGSOGG=:PROGSOGG OR d.CODFISCALE LIKE :CODFISCALE)";
                $where = 'AND';
            }
        } else {
            if (trim($filtri['CODFISCALE']) <> '' && trim($filtri['NOMINATIVO']) === '') {
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where d.CODFISCALE LIKE :CODFISCALE";
            }
        }
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV'] > 0) {
            $this->addSqlParam($sqlParams, "PROGNCIV", strtoupper(trim($filtri['PROGNCIV'])), PDO::PARAM_INT);
            $sql .= " $where d.PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES'] > 0) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", strtoupper(trim($filtri['PROG_UT_ES'])), PDO::PARAM_INT);
            $sql .= " $where d.PROG_UT_ES=:PROG_UT_ES";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY d.ANNORIF DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_GASD
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBueGasdRilSogg($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueGasdRilSogg($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /*
     * *
     * 
     * Restituisce comando sql per lettura per chiave da tabella BUE_GASD
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */

    public function getSqlLeggiBueGasdChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_UT_ES'] = $cod;
        return self::getSqlLeggiBueGasdRilSogg($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BUE_GASD per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBueGasdChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueGasdChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BUE_GAS    

    /**
     * Restituisce comando sql per lettura tabella BUE_GAS per funzione Rilevazione Soggetto
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlCountBueGas($filtri, &$sqlParams = array()) {
        $sql = "SELECT COUNT(*) AS CONTA FROM BUE_GAS ";
        $where = ' WHERE ';

        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV']) {
            $this->addSqlParam($sqlParams, "PROGNCIV", $filtri['PROGNCIV'], PDO::PARAM_INT);
            $sql .= " $where PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri)) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_GAS
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function countBueGas($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlCountBueGas($filtri, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BUE_GAS per funzione Rilevazione Soggetto
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueGas($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BUE_GAS.* FROM BUE_GAS ";
        $where = ' WHERE ';

        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV']) {
            $this->addSqlParam($sqlParams, "PROGNCIV", $filtri['PROGNCIV'], PDO::PARAM_INT);
            $sql .= " $where PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri)) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGNCIV DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_GAS
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBueGas($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueGas($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /*
     * *
     * 
     * Restituisce comando sql per lettura per chiave da tabella BUE_GAS
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */

    public function getSqlLeggiBueGasChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_UT_ES'] = $cod;
        return self::getSqlLeggiBueGas($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BUE_GAS per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBueGasChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueGasChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BUE_LOCAZD

    /**
     * Restituisce comando sql per lettura tabella BUE_LOCAZD per funzione Rilevazione Soggetto
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueLocazdRilSogg($filtri, $excludeOrderBy = false, &$sqlParams = array()) {

        $sql = "SELECT d.*,n.TOPONIMO AS TOPONIMO_DEC,n.DESVIA AS DESVIA_DEC,n.NUMCIV AS NUMCIV_DEC,n.SUBNCIV AS SUBNCIV_DEC, t.* FROM BUE_LOCAZD d LEFT JOIN BUE_LOCAZ t ON d.PROG_UT_ES=t.PROG_UT_ES"
                . " LEFT JOIN BTA_NCIVI_V01 n ON d.PROGNCIV=n.PROGNCIV where t.FLAG_DIS=0";
        $where = 'and';
        if (array_key_exists('PROGSOGG', $filtri) && intval($filtri['PROGSOGG']) > 0) {
            if (trim($filtri['CODFISCALE']) === '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $sql .= " $where d.PROGSOGG=:PROGSOGG";
                $where = 'AND';
            }
            if (trim($filtri['CODFISCALE']) <> '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALEP", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE2", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE3", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where (d.PROGSOGG=:PROGSOGG OR d.CODFISCALE LIKE :CODFISCALE OR d.CODFIS_P LIKE :CODFISCALEP OR d.CODFIS_P2 LIKE :CODFISCALE2 OR d.CODFIS_P3 LIKE :CODFISCALE3)";
                $where = 'AND';
            }
        } else {
            if (trim($filtri['CODFISCALE']) <> '') {
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALEP", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE2", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE3", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where (d.CODFISCALE LIKE :CODFISCALE OR d.CODFIS_P LIKE :CODFISCALEP OR d.CODFIS_P2 LIKE :CODFISCALE2 OR d.CODFIS_P3 LIKE :CODFISCALE3)";
            }
        }
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV'] > 0) {
            $this->addSqlParam($sqlParams, "PROGNCIV", strtoupper(trim($filtri['PROGNCIV'])), PDO::PARAM_INT);
            $sql .= " $where d.PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES'] > 0) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", strtoupper(trim($filtri['PROG_UT_ES'])), PDO::PARAM_INT);
            $sql .= " $where d.PROG_UT_ES=:PROG_UT_ES";
            $where = 'AND';
        }
        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES'] > 0) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", strtoupper(trim($filtri['PROG_UT_ES'])), PDO::PARAM_INT);
            $sql .= " $where d.PROG_UT_ES=:PROG_UT_ES";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY d.ANNOREG DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_LOCAZD
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBueLocazdRilSogg($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueLocazdRilSogg($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /*
     * 
     * Restituisce comando sql per lettura per chiave da tabella BUE_LOCAZD
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */

    public function getSqlLeggiBueLocazdRilSoggChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_UT_ES'] = $cod;
        return self::getSqlLeggiBueLocazdRilSogg($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BUE_LOCAZD per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBueLocazdRilSoggChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueLocazdRilSoggChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BUE_LOCAZ

    public function getSqlCountBueLocaz($filtri, &$sqlParams = array()) {
        $sql = "SELECT COUNT(*) AS CONTA FROM BUE_LOCAZ ";
        $where = ' WHERE ';

        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES']) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", strtoupper(trim($filtri['PROG_UT_ES'])), PDO::PARAM_INT);
            $sql .= " $where PROG_UT_ES=:PROG_UT_ES";
            $where = 'AND';
        }
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV']) {
            $this->addSqlParam($sqlParams, "PROGNCIV", $filtri['PROGNCIV'], PDO::PARAM_INT);
            $sql .= " $where PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri)) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }

        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_LOCAZ
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function countBueLocaz($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlCountBueLocaz($filtri, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BUE_LOCAZ per funzione Rilevazione Soggetto
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueLocaz($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BUE_LOCAZ.* FROM BUE_LOCAZ ";
        $where = ' WHERE ';

        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES']) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", strtoupper(trim($filtri['PROG_UT_ES'])), PDO::PARAM_INT);
            $sql .= " $where PROG_UT_ES=:PROG_UT_ES";
            $where = 'AND';
        }
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV']) {
            $this->addSqlParam($sqlParams, "PROGNCIV", $filtri['PROGNCIV'], PDO::PARAM_INT);
            $sql .= " $where PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri)) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROG_UT_ES DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_LOCAZ
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBueLocaz($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueLocaz($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /*
     * 
     * Restituisce comando sql per lettura per chiave da tabella BUE_LOCAZ
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */

    public function getSqlLeggiBueLocazChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_UT_ES'] = $cod;
        return self::getSqlLeggiBueLocaz($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BUE_LOCAZ per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBueLocazChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueLocazChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BUE_LOCUFD

    /**
     * Restituisce comando sql per lettura tabella BUE_LOCUFD per funzione Rilevazione Soggetto
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueLocufdRilSogg($filtri, $excludeOrderBy = false, &$sqlParams = array()) {

        $sql = "SELECT d.*, t.* FROM BUE_LOCUFD d LEFT JOIN BUE_LOCUFF t ON d.PROG_UT_ES=t.PROG_UT_ES"
                . " where t.FLAG_DIS=0";
        $where = 'AND';
        if (array_key_exists('PROGSOGG', $filtri) && intval($filtri['PROGSOGG']) > 0) {
            if (trim($filtri['CODFISCALE']) === '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $sql .= " $where d.PROGSOGG=:PROGSOGG";
                $where = 'AND';
            }
            if (trim($filtri['CODFISCALE']) <> '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALEP", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE2", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE3", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE4", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE5", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where (d.PROGSOGG=:PROGSOGG OR d.CODFISCALE LIKE :CODFISCALE OR d.CODFIS_P LIKE :CODFISCALEP OR d.CODFIS_P2 LIKE :CODFISCALE2 OR d.CODFIS_P3 LIKE :CODFISCALE3 OR d.CODFIS_P4 LIKE :CODFISCALE4 OR d.CODFIS_P5 LIKE :CODFISCALE5)";
                $where = 'AND';
            }
        } else {
            if (trim($filtri['CODFISCALE']) <> '') {
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALEP", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE2", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE3", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE4", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE5", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where (d.CODFISCALE LIKE :CODFISCALE OR d.CODFIS_P LIKE :CODFISCALEP OR d.CODFIS_P2 LIKE :CODFISCALE2 OR d.CODFIS_P3 LIKE :CODFISCALE3 OR d.CODFIS_P4 LIKE :CODFISCALE4 OR d.CODFIS_P5 LIKE :CODFISCALE5)";
            }
        }
        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES'] > 0) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", strtoupper(trim($filtri['PROG_UT_ES'])), PDO::PARAM_INT);
            $sql .= " $where d.PROG_UT_ES=:PROG_UT_ES";
            $where = 'AND';
        }
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV'] > 0) {
            $this->addSqlParam($sqlParams, "PROGNCIV", strtoupper(trim($filtri['PROGNCIV'])), PDO::PARAM_INT);
            $sql .= " $where t.PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY d.ANNOREG DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_LOCUFD
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBueLocufdRilSogg($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueLocufdRilSogg($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /*
     * *
     * 
     * Restituisce comando sql per lettura per chiave da tabella BUE_LOCUFD
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */

    public function getSqlLeggiBueLocufdRilSoggChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_UT_ES'] = $cod;
        return self::getSqlLeggiBueLocufdRilSogg($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BUE_LOCUFD per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBueLocufdRilSoggChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueLocufdRilSoggChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BUE_ATTILD

    /**
     * Restituisce comando sql per lettura tabella BUE_ATTILD per funzione Rilevazione Soggetto
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueAttildRilSogg($filtri, $excludeOrderBy = false, &$sqlParams = array()) {

        $sql = "SELECT d.*, t.* FROM BUE_ATTILD d LEFT JOIN BUE_ATTIL t ON d.PROG_UT_ES=t.PROG_UT_ES"
                . " where t.FLAG_DIS=0";
        $where = 'AND';
        if (array_key_exists('PROGSOGG', $filtri) && intval($filtri['PROGSOGG']) > 0) {
            if (trim($filtri['CODFISCALE']) === '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $sql .= " $where d.PROGSOGG=:PROGSOGG";
                $where = 'AND';
            }
            if (trim($filtri['CODFISCALE']) <> '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALEP", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE2", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE3", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE4", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE5", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where (d.PROGSOGG=:PROGSOGG OR d.CODFISCALE LIKE :CODFISCALE OR d.CODFIS_P LIKE :CODFISCALEP OR d.CODFIS_P2 LIKE :CODFISCALE2 OR d.CODFIS_P3 LIKE :CODFISCALE3 OR d.CODFIS_P4 LIKE :CODFISCALE4 OR d.CODFIS_P5 LIKE :CODFISCALE5)";
                $where = 'AND';
            }
        } else {
            if (trim($filtri['CODFISCALE']) <> '') {
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALEP", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE2", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE3", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE4", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "CODFISCALE5", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where (d.CODFISCALE LIKE :CODFISCALE OR d.CODFIS_P LIKE :CODFISCALEP OR d.CODFIS_P2 LIKE :CODFISCALE2 OR d.CODFIS_P3 LIKE :CODFISCALE3 OR d.CODFIS_P4 LIKE :CODFISCALE4 OR d.CODFIS_P5 LIKE :CODFISCALE5)";
            }
        }
        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES'] > 0) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", strtoupper(trim($filtri['PROG_UT_ES'])), PDO::PARAM_INT);
            $sql .= " $where d.PROG_UT_ES=:PROG_UT_ES";
            $where = 'AND';
        }
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV'] > 0) {
            $this->addSqlParam($sqlParams, "PROGNCIV", strtoupper(trim($filtri['PROGNCIV'])), PDO::PARAM_INT);
            $sql .= " $where t.PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY d.ANNOREG DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_ATTILD
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBueAttildRilSogg($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueAttildRilSogg($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /*
     * *
     * 
     * Restituisce comando sql per lettura per chiave da tabella BUE_LOCUFD
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */

    public function getSqlLeggiBueAttildRilSoggChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_UT_ES'] = $cod;
        return self::getSqlLeggiBueAttildRilSogg($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BUE_LOCUFD per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBueAttildRilSoggChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueAttildRilSoggChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BUE_SUCCED

    /**
     * Restituisce comando sql per lettura tabella BUE_SUCCED per funzione Rilevazione Soggetto
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueSuccedRilSogg($filtri, $excludeOrderBy = false, &$sqlParams = array()) {

        $sql = "SELECT d.*,n.TOPONIMO AS TOPONIMO_DEC,n.DESVIA AS DESVIA_DEC,n.NUMCIV AS NUMCIV_DEC,n.SUBNCIV AS SUBNCIV_DEC,t.* FROM BUE_SUCCED d LEFT JOIN BUE_SUCCES t ON d.PROG_UT_ES=t.PROG_UT_ES"
                . " LEFT JOIN BTA_NCIVI_V01 n ON d.PROGNCIV=n.PROGNCIV WHERE t.FLAG_DIS=0";
        $where = 'and';
        if (array_key_exists('PROGSOGG', $filtri) && intval($filtri['PROGSOGG']) > 0) {
            if (trim($filtri['CODFISCALE']) === '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $sql .= " $where d.PROGSOGG=:PROGSOGG";
                $where = 'AND';
            }
            if (trim($filtri['CODFISCALE']) <> '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where (d.PROGSOGG=:PROGSOGG OR d.CODFISCALE LIKE :CODFISCALE)";
                $where = 'AND';
            }
        } else {
            if (trim($filtri['CODFISCALE']) <> '' && trim($filtri['NOMINATIVO']) === '') {
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where d.CODFISCALE LIKE :CODFISCALE";
            }
        }
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV'] > 0) {
            $this->addSqlParam($sqlParams, "PROGNCIV", strtoupper(trim($filtri['PROGNCIV'])), PDO::PARAM_INT);
            $sql .= " $where d.PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES'] > 0) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", strtoupper(trim($filtri['PROG_UT_ES'])), PDO::PARAM_INT);
            $sql .= " $where d.PROG_UT_ES=:PROG_UT_ES";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY d.ANNOREG DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_GASD
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBueSuccedRilSogg($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueSuccedRilSogg($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /*
     * *
     * 
     * Restituisce comando sql per lettura per chiave da tabella BUE_SUCCED
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */

    public function getSqlLeggiBueSuccedRilSoggChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_UT_ES'] = $cod;
        return self::getSqlLeggiBueSuccedRilSogg($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BUE_SUCCED per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBueSuccedRilSoggChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueSuccedRilSoggChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BUE_SUCCES

    public function getSqlCountBueSucces($filtri, &$sqlParams = array()) {
        $sql = "SELECT COUNT(*) AS CONTA FROM BUE_SUCCES ";
        $where = ' WHERE ';

        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES']) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", $filtri['PROG_UT_ES'], PDO::PARAM_INT);
            $sql .= " $where PROG_UT_ES=:PROG_UT_ES";
            $where = 'AND';
        }
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV']) {
            $this->addSqlParam($sqlParams, "PROGNCIV", $filtri['PROGNCIV'], PDO::PARAM_INT);
            $sql .= " $where PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri)) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_SUCCES
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function countBueSucces($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlCountBueSucces($filtri, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BUE_SUCCES per funzione Rilevazione Soggetto
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueSucces($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BUE_SUCCES.* FROM BUE_SUCCES ";
        $where = ' WHERE ';

        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES']) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", $filtri['PROG_UT_ES'], PDO::PARAM_INT);
            $sql .= " $where PROG_UT_ES=:PROG_UT_ES";
            $where = 'AND';
        }
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV']) {
            $this->addSqlParam($sqlParams, "PROGNCIV", $filtri['PROGNCIV'], PDO::PARAM_INT);
            $sql .= " $where PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri)) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROG_UT_ES DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_SUCCES
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBueSucces($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueSucces($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /*
     * *
     * 
     * Restituisce comando sql per lettura per chiave da tabella BUE_SUCCES
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */

    public function getSqlLeggiBueSuccesChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_UT_ES'] = $cod;
        return self::getSqlLeggiBueSucces($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BUE_SUCCES per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBueSuccesChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueSuccesChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BUE_DICR

    /**
     * Restituisce comando sql per lettura tabella BUE_DICR per funzione Rilevazione Soggetto
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueDicrdRilSogg($filtri, $excludeOrderBy = false, &$sqlParams = array()) {

        $sql = "SELECT d.*, t.* FROM BUE_DICRD d LEFT JOIN BUE_DICR t ON d.PROG_UT_ES=t.PROG_UT_ES"
                . " WHERE t.FLAG_DIS=0";
        $where = 'and';
        if (array_key_exists('PROGSOGG', $filtri) && intval($filtri['PROGSOGG']) > 0) {
            if (trim($filtri['CODFISCALE']) === '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $sql .= " $where d.PROGSOGG=:PROGSOGG";
                $where = 'AND';
            }
            if (trim($filtri['CODFISCALE']) <> '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where (d.PROGSOGG=:PROGSOGG OR d.CODFISCALE LIKE :CODFISCALE)";
                $where = 'AND';
            }
        } else {
            if (trim($filtri['CODFISCALE']) <> '' && trim($filtri['NOMINATIVO']) === '') {
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where d.CODFISCALE LIKE :CODFISCALE";
            }
        }
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV'] > 0) {
            $this->addSqlParam($sqlParams, "PROGNCIV", strtoupper(trim($filtri['PROGNCIV'])), PDO::PARAM_INT);
            $sql .= " $where d.PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES'] > 0) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", strtoupper(trim($filtri['PROG_UT_ES'])), PDO::PARAM_INT);
            $sql .= " $where d.PROG_UT_ES=:PROG_UT_ES";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY d.ANNORIF DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_DICR
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBueDicrdRilSogg($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueDicrdRilSogg($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /*
     * 
     * Restituisce comando sql per lettura per chiave da tabella BUE_DICR
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */

    public function getSqlLeggiBueDicrdRilSoggChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_UT_ES'] = $cod;
        return self::getSqlLeggiBueDicrdRilSogg($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BUE_DICR per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBueDicrdRilSoggChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueDicrdRilSoggChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BUE_BONIFD

    /**
     * Restituisce comando sql per lettura tabella BUE_BONIFD per funzione Rilevazione Soggetto
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueBonifdRilSogg($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT d.*, t.* FROM BUE_BONIFD d LEFT JOIN BUE_BONIF t ON d.PROG_UT_ES=t.PROG_UT_ES"
                . " WHERE t.FLAG_DIS=0";
        $where = 'and';
        if (array_key_exists('PROGSOGG', $filtri) && intval($filtri['PROGSOGG']) > 0) {
            if (trim($filtri['CODFISCALE']) === '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $sql .= " $where d.PROGSOGG=:PROGSOGG";
                $where = 'AND';
            }
            if (trim($filtri['CODFISCALE']) <> '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where (d.PROGSOGG=:PROGSOGG OR d.CODFISCALE LIKE :CODFISCALE)";
                $where = 'AND';
            }
        } else {
            if (trim($filtri['CODFISCALE']) <> '') {
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where d.CODFISCALE LIKE :CODFISCALE";
            }
        }
        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES'] > 0) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", strtoupper(trim($filtri['PROG_UT_ES'])), PDO::PARAM_INT);
            $sql .= " $where d.PROG_UT_ES=:PROG_UT_ES";
            $where = 'AND';
        }
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV'] > 0) {
            $this->addSqlParam($sqlParams, "PROGNCIV", strtoupper(trim($filtri['PROGNCIV'])), PDO::PARAM_INT);
            $sql .= " $where t.PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY d.ANNORIF DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_BONIFD
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBueBonifdRilSogg($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueBonifdRilSogg($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /*
     * *
     * 
     * Restituisce comando sql per lettura per chiave da tabella BUE_GASD
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */

    public function getSqlLeggiBueBonifdRilSoggChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_UT_ES'] = $cod;
        return self::getSqlLeggiBueBonifdRilSogg($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BUE_GASD per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBueBonifdRilSoggChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueBonifdRilSoggChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BUE_IDRICD

    /**
     * Restituisce comando sql per lettura tabella BUE_DICR per funzione Rilevazione Soggetto
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueIdricdRilSogg($filtri, $excludeOrderBy = false, &$sqlParams = array()) {

        $sql = "SELECT d.*,n.TOPONIMO AS TOPONIMO_DEC,n.DESVIA AS DESVIA_DEC,n.NUMCIV AS NUMCIV_DEC,n.SUBNCIV AS SUBNCIV_DEC,n.DES_BREVE AS DESBREVE_DEC, t.* FROM BUE_IDRICD d LEFT JOIN BUE_IDRIC t ON d.PROG_UT_ES=t.PROG_UT_ES"
                . " LEFT JOIN BTA_NCIVI_V01 n ON d.PROGNCIV=n.PROGNCIV WHERE t.FLAG_DIS=0";
        $where = 'and';
        if (array_key_exists('PROGSOGG', $filtri) && intval($filtri['PROGSOGG']) > 0) {
            if (trim($filtri['CODFISCALE']) === '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $sql .= " $where d.PROGSOGG=:PROGSOGG";
                $where = 'AND';
            }
            if (trim($filtri['CODFISCALE']) <> '') {
                $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($filtri['PROGSOGG'])), PDO::PARAM_INT);
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where (d.PROGSOGG=:PROGSOGG OR d.CODFISCALE LIKE :CODFISCALE)";
                $where = 'AND';
            }
        } else {
            if (trim($filtri['CODFISCALE']) <> '' && trim($filtri['NOMINATIVO']) === '') {
                $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                $sql .= " $where d.CODFISCALE LIKE :CODFISCALE";
            }
        }
        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV'] > 0) {
            $this->addSqlParam($sqlParams, "PROGNCIV", strtoupper(trim($filtri['PROGNCIV'])), PDO::PARAM_INT);
            $sql .= " $where d.PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES'] > 0) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", strtoupper(trim($filtri['PROG_UT_ES'])), PDO::PARAM_INT);
            $sql .= " $where d.PROG_UT_ES=:PROG_UT_ES";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY d.ANNORIF DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_DICR
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBueIdricdRilSogg($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueIdricdRilSogg($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /*
     * *
     * 
     * Restituisce comando sql per lettura per chiave da tabella BUE_GASD
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */

    public function getSqlLeggiBueIdricdRilSoggChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_UT_ES'] = $cod;
        return self::getSqlLeggiBueIdricdRilSogg($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BUE_GASD per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBueUteIdrRilSoggChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueIdricdRilSoggChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BUE_IDRIC

    public function getSqlCountBueIdric($filtri, &$sqlParams = array()) {
        $sql = "SELECT COUNT(*) AS CONTA FROM BUE_IDRIC ";
        $where = ' WHERE ';

        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV']) {
            $this->addSqlParam($sqlParams, "PROGNCIV", $filtri['PROGNCIV'], PDO::PARAM_INT);
            $sql .= " $where PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri)) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }
        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES']) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", $filtri['PROG_UT_ES'], PDO::PARAM_INT);
            $sql .= " $where PROG_UT_ES=:PROG_UT_ES";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_IDRIC
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function countBueIdric($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlCountBueIdric($filtri, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BUE_IDRIC per funzione Rilevazione Soggetto
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueIdric($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BUE_IDRIC.* FROM BUE_IDRIC ";
        $where = ' WHERE ';

        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV']) {
            $this->addSqlParam($sqlParams, "PROGNCIV", $filtri['PROGNCIV'], PDO::PARAM_INT);
            $sql .= " $where PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri)) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }
        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES']) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", $filtri['PROG_UT_ES'], PDO::PARAM_INT);
            $sql .= " $where PROG_UT_ES=:PROG_UT_ES";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROG_UT_ES DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_IDRIC
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBueIdric($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueIdric($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /*
     * *
     * 
     * Restituisce comando sql per lettura per chiave da tabella BUE_IDRIC
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */

    public function getSqlLeggiBueIdricChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_UT_ES'] = $cod;
        return self::getSqlLeggiBueIdric($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BUE_IDRIC per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBueUteIdricChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueIdricChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BUE_LOCUFF

    private function filtriLocuff($filtri, &$sqlParams) {
        $sql .= "";
        $where = ' WHERE ';

        if (array_key_exists('PROGNCIV', $filtri) && $filtri['PROGNCIV']) {
            $this->addSqlParam($sqlParams, "PROGNCIV", $filtri['PROGNCIV'], PDO::PARAM_INT);
            $sql .= " $where PROGNCIV=:PROGNCIV";
            $where = 'AND';
        }
        if (array_key_exists('PROGINT', $filtri) && $filtri['PROGINT']) {
            $this->addSqlParam($sqlParams, "PROGINT", $filtri['PROGINT'], PDO::PARAM_INT);
            $sql .= " $where PROGINT=:PROGINT";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri)) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }
        if (array_key_exists('PROG_UT_ES', $filtri) && $filtri['PROG_UT_ES']) {
            $this->addSqlParam($sqlParams, "PROG_UT_ES", $filtri['PROG_UT_ES'], PDO::PARAM_INT);
            $sql .= " $where PROG_UT_ES=:PROG_UT_ES";
            $where = 'AND';
        }
        return $sql;
    }

    public function getSqlCountBueLocuff($filtri, &$sqlParams = array()) {
        $sql = "SELECT COUNT(*) AS CONTA FROM BUE_LOCUFF ";
        $sql .= $this->filtriLocuff($filtri, $sqlParams);
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_LOCUFF
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function countBueLocuff($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlCountBueLocuff($filtri, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BUE_LOCUFF per funzione Rilevazione Soggetto
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBueLocuff($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BUE_LOCUFF.* FROM BUE_LOCUFF ";
        $sql .= $this->filtriLocuff($filtri, $sqlParams);
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROG_UT_ES DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BUE_LOCUFF
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBueLocuff($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueLocuff($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /*
     * *
     * 
     * Restituisce comando sql per lettura per chiave da tabella BUE_LOCUFF
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */

    public function getSqlLeggiBueLocuffChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROG_UT_ES'] = $cod;
        return self::getSqlLeggiBueLocuff($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BUE_LOCUFF per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBueUteLocuffChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBueLocuffChiave($cod, $sqlParams), false, $sqlParams);
    }

}

?>