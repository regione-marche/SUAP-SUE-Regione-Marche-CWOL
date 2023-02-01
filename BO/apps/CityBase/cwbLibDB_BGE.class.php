<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_CITYWARE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbEventiBat.class.php';

/**
 *
 * Utility DB Cityware (Modulo BGE)
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
class cwbLibDB_BGE extends cwbLibDB_CITYWARE {
    // BGE_TESTI

    /**
     * Restituisce comando sql per lettura tabella BGE_TESTI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeTesti($filtri, $excludeOrderBy = false, &$sqlParams, $orderBy = '') {
        $sql = "SELECT BGE_TESTI.* FROM BGE_TESTI";
        $where = 'WHERE';
        if (array_key_exists('CODTESTO', $filtri) && $filtri['CODTESTO'] != null) {
            $this->addSqlParam($sqlParams, "CODTESTO", $filtri['CODTESTO'], PDO::PARAM_INT);
            $sql .= " $where CODTESTO=:CODTESTO";
            $where = 'AND';
        }
        if (array_key_exists('PROGTESTIT', $filtri) && $filtri['PROGTESTIT'] != null) {
            $this->addSqlParam($sqlParams, "PROGTESTIT", $filtri['PROGTESTIT'], PDO::PARAM_INT);
            $sql .= " $where PROGTESTIT=:PROGTESTIT";
            $where = 'AND';
        }

        for ($i = 0; $i <= 9; $i++) {
            $name = "F_V_TES_$i";
            if (array_key_exists($name, $filtri) && $filtri[$name] != 0) {
                $sql .= " $where $name=:$name";
                $this->addSqlParam($sqlParams, $name, $filtri[$name], PDO::PARAM_INT);
                $where = 'AND';
            }
        }
        if (array_key_exists('PROGTESTIT_or', $filtri) && $filtri['PROGTESTIT_or'] != null) {
            $sql .= " $where";
            $sql .= "(";
            foreach ($filtri['PROGTESTIT_or'] as $key => $value) {
                $this->addSqlParam($sqlParams, "PROGTESTIT_or" . $key, $filtri['PROGTESTIT_or'][$key]['PROGTESTIT'], PDO::PARAM_INT);
                $sql .= " PROGTESTIT=:PROGTESTIT_or" . $key . " OR";
            }
            $sql = substr($sql, 0, -2);
            $sql .= ")";
            $where = 'AND';
        }

        if (array_key_exists('TESTOPROV', $filtri) && $filtri['TESTOPROV'] != null && $filtri['TESTOPROV'] != 0) {
            $this->addSqlParam($sqlParams, "TESTOPROV", $filtri['TESTOPROV'], PDO::PARAM_INT);
            $sql .= " $where TESTOPROV=:TESTOPROV";
            $where = 'AND';
        }
        if (array_key_exists('FORMATORTF', $filtri) && $filtri['FORMATORTF'] != null) {
            $this->addSqlParam($sqlParams, "FORMATORTF", $filtri['FORMATORTF'], PDO::PARAM_INT);
            $sql .= " $where FORMATORTF=:FORMATORTF";
            $where = 'AND';
        }
        if (array_key_exists('CODMODULO', $filtri) && $filtri['CODMODULO'] != null) {
            $this->addSqlParam($sqlParams, "CODMODULO", $filtri['CODMODULO'], PDO::PARAM_STR);
            $sql .= " $where CODMODULO=:CODMODULO";
            $where = 'AND';
        }
        if (empty($filtri['FLAG_DIS'])) {
            $sql .= " $where FLAG_DIS = 0";
            $where = 'AND';
        }
        if (array_key_exists('DESTESTO', $filtri) && $filtri['DESTESTO'] != null) {
            $this->addSqlParam($sqlParams, "DESTESTO", "%" . strtoupper(trim($filtri['DESTESTO'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESTESTO") . " LIKE :DESTESTO";
            $where = 'AND';
        }
        $sql .= strlen($orderBy) > 0 ? ' ORDER BY ' . $orderBy : ' ORDER BY CODTESTO';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_TESTI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeTesti($filtri, $orderBy = '') {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeTesti($filtri, false, $sqlParams, $orderBy), true, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGE_TESTI
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeTestiChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['CODTESTO'] = $cod;
        return self::getSqlLeggiBgeTesti($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_TESTI per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBgeTestiChiave($cod) {
        if ($cod < 1) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeTestiChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BGE_TESFIL

    /**
     * Restituisce comando sql per lettura tabella BGE_TESFIL
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeTesfil($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();

        $sql = "SELECT BGE_TESFIL_V01.* FROM BGE_TESFIL_V01";
        $where = 'WHERE';
        if (array_key_exists('CODTESTO', $filtri) && $filtri['CODTESTO'] != null) {
            $this->addSqlParam($sqlParams, "CODTESTO", $filtri['CODTESTO'], PDO::PARAM_INT);
            $sql .= " $where CODTESTO=:CODTESTO";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODTESTO';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_TESFIL
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeTesfil($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeTesfil($filtri, false, $sqlParams), true, $sqlParams);
    }

    // RECUPERA IUV DA BGE_AGID_SCADENZE

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_SCADENZE ritornando lo IUV
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiRecuperaIUV($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();
        $sql = "SELECT  BGE_AGID_SCADENZE.IUV,BGE_AGID_SCADENZE.CODRIFERIMENTO FROM BGE_AGID_SCADENZE";
        $where = 'WHERE';
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", $filtri['CODTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri)) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", $filtri['SUBTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('ANNORIF', $filtri)) {
            $this->addSqlParam($sqlParams, "ANNORIF", $filtri['ANNORIF'], PDO::PARAM_INT);
            $sql .= " $where ANNORIF=:ANNORIF";
            $where = 'AND';
        }
        if (array_key_exists('PROGCITYSC', $filtri) && $filtri['PROGCITYSC'] != null) {
            $this->addSqlParam($sqlParams, "PROGCITYSC", $filtri['PROGCITYSC'], PDO::PARAM_INT);
            $sql .= " $where PROGCITYSC=:PROGCITYSC";
            $where = 'AND';
        }
        if (array_key_exists('NUMRATA', $filtri)) {
            $this->addSqlParam($sqlParams, "NUMRATA", $filtri['NUMRATA'], PDO::PARAM_INT);
            $sql .= " $where NUMRATA=:NUMRATA";
            $where = 'AND';
        }
//        if (array_key_exists('STATOne7', $filtri)) {
//            $sql .= " $where STATO <> 7";
//            $where = 'AND';
//        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_TIPINT per capire l'intermediario che sto trattando
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiRecuperaIUV($filtri) {
        $res = ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiRecuperaIUV($filtri, false, $sqlParams), false, $sqlParams);
        if ($res) {
            return $res['IUV'];
        }
        return false;
    }

    // BGE_TESTIC

    /**
     * Restituisce comando sql per lettura tabella BGE_TESTIC
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeTestic($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();

        $sql = "SELECT CODTESTO ," . $this->getCitywareDB()->adapterBlob("CORPOTESTO") . " FROM BGE_TESTIC";
        $where = 'WHERE';
        if (array_key_exists('CODTESTO', $filtri) && $filtri['CODTESTO'] != null) {
            $this->addSqlParam($sqlParams, "CODTESTO", $filtri['CODTESTO'], PDO::PARAM_INT);
            $sql .= " $where CODTESTO=:CODTESTO";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODTESTO';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_TESTIC
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeTestic($filtri) {
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BGE", "leggiBgeTesticBinary"); //DONE
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeTestic($filtri, false, $sqlParams), true, $sqlParams, $infoBinaryCallback);
    }

    //risultato del caricamento
    public function leggiBgeTesticBinary($result = array()) {
        $sql = "SELECT " . $this->getCitywareDB()->adapterBlob("CORPOTESTO") . " FROM BGE_TESTIC WHERE CODTESTO = :CODTESTO";
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "CODTESTO", $result['CODTESTO'], PDO::PARAM_INT);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "CORPOTESTO", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['CORPOTESTO'] = $resultBin['CORPOTESTO'];

        return $result;
    }

    // BGE_TESTIL

    /**
     * Restituisce comando sql per lettura tabella BGE_TESTIL
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeTestil($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();
        $sql = "SELECT BGE_TESTIL.* FROM BGE_TESTIL";
        $where = 'WHERE';
        if (array_key_exists('CODTESTO', $filtri) && $filtri['CODTESTO'] != null) {
            $this->addSqlParam($sqlParams, "CODTESTO", $filtri['CODTESTO'], PDO::PARAM_INT);
            $sql .= " $where CODTESTO=:CODTESTO";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODTESTO';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_TESTIL
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeTestil($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeTestil($filtri, false, $sqlParams), true, $sqlParams);
    }

    // BGE_FILTRI

    /**
     * Restituisce comando sql per lettura tabella BGE_FILTRI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeFiltri($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();

        $sql = "SELECT BGE_FILTRI.* FROM BGE_FILTRI";
        $where = 'WHERE';
        if (array_key_exists('NOMELISTLG', $filtri) && $filtri['NOMELISTLG'] != null) {
            $this->addSqlParam($sqlParams, "NOMELISTLG", $filtri['NOMELISTLG'], PDO::PARAM_STR);
            $sql .= " $where NOMELISTLG=':NOMELISTLG'";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_FILTRI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeFiltri($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeFiltri($filtri, false, $sqlParams), true, $sqlParams);
    }

    // BGE_FUNZGIS

    /**
     * Restituisce comando sql per lettura tabella BGE_FUNZGIS
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeFunzgis($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_FUNZGIS.* FROM BGE_FUNZGIS";
        $where = 'WHERE';
        if (array_key_exists('IDFUNZ', $filtri) && $filtri['IDFUNZ'] != null) {
            $this->addSqlParam($sqlParams, "IDFUNZ", $filtri['IDFUNZ'], PDO::PARAM_INT);
            $sql .= " $where IDFUNZ=:IDFUNZ";
            $where = 'AND';
        }
        if (array_key_exists('FUNZIONE', $filtri) && $filtri['FUNZIONE'] != null) {
            $this->addSqlParam($sqlParams, "FUNZIONE", "%" . strtoupper(trim($filtri['FUNZIONE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("FUNZIONE") . " LIKE :FUNZIONE";
            $where = 'AND';
        }
        if (array_key_exists('DESCRIZ', $filtri) && $filtri['DESCRIZ'] != null) {
            $this->addSqlParam($sqlParams, "DESCRIZ", "%" . strtoupper(trim($filtri['DESCRIZ'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESCRIZ") . " LIKE :DESCRIZ";
            $where = 'AND';
        }
        if (array_key_exists('VALORE', $filtri) && $filtri['VALORE'] != null) {
            $this->addSqlParam($sqlParams, "VALORE", "%" . strtoupper(trim($filtri['DESCRIZ'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("VALORE") . " LIKE :VALORE";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDFUNZ';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_FUNZGIS
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeFunzgis($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeFunzgis($filtri, false, $sqlParams), true, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGE_FUNZGIS
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeFunzgisChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['IDFUNZ'] = $cod;
        return self::getSqlLeggiBgeFunzgis($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_FUNZGIS per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBgeFunzgisChiave($cod) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeFunzgisChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BGE_TESTIT

    /**
     * Restituisce comando sql per lettura tabella BGE_TESTIT
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeTestit($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();

        $sql = "SELECT BGE_TESTIT.* FROM BGE_TESTIT";
        $where = 'WHERE';
        if (array_key_exists('PROGTESTIT', $filtri) && $filtri['PROGTESTIT'] != null) {
            $this->addSqlParam($sqlParams, "PROGTESTIT", $filtri['PROGTESTIT'], PDO::PARAM_INT);
            $sql .= " $where PROGTESTIT=:PROGTESTIT";
            $where = 'AND';
        }
        if (array_key_exists('TESTIT', $filtri) && $filtri['TESTIT'] != null) {
            $this->addSqlParam($sqlParams, "TESTIT", "%" . strtoupper(trim($filtri['TESTIT'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("TESTIT") . " LIKE :TESTIT";
            $where = 'AND';
        }
        if (array_key_exists('TESTOPROV', $filtri) && ($filtri['TESTOPROV'] != null && $filtri['TESTOPROV'] != 0)) {
            $this->addSqlParam($sqlParams, "TESTOPROV", $filtri['TESTOPROV'], PDO::PARAM_INT);
            $sql .= " $where TESTOPROV=:TESTOPROV";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY TESTIT';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_TESTIT
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeTestit($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeTestit($filtri, false, $sqlParams), true, $sqlParams);
    }

    // BGE_TESTIT

    /**
     * Restituisce comando sql per lettura tabella BGE_TESTIT
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeTestitFiltroRicerca($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();

        $sql = "SELECT BGE_TESTIT.* FROM BGE_TESTIT";
        $where = 'WHERE';
        if (array_key_exists('PROGTESTIT', $filtri) && $filtri['PROGTESTIT'] != null) {
            $this->addSqlParam($sqlParams, "PROGTESTIT", $filtri['PROGTESTIT'], PDO::PARAM_INT);
            $sql .= " $where PROGTESTIT=:PROGTESTIT";
            $where = 'AND';
        }
        if (array_key_exists('TESTIT', $filtri) && $filtri['TESTIT'] != null) {
            $this->addSqlParam($sqlParams, "TESTIT", "%" . strtoupper(trim($filtri['TESTIT'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("TESTIT") . " LIKE :TESTIT";
            $where = 'AND';
        }
        if (array_key_exists('TESTOPROV', $filtri) && ($filtri['TESTOPROV'] != null && $filtri['TESTOPROV'] != 0)) {
            $this->addSqlParam($sqlParams, "TESTOPROV", $filtri['TESTOPROV'], PDO::PARAM_INT);
            $sql .= " $where TESTOPROV=:TESTOPROV";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY TESTIT';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_TESTIT
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeTestitFiltroRicerca($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeTestitFiltroRicerca($filtri, false, $sqlParams), true, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGE_TESTIT
     * @param string $cod Chiave    
     * @param array $sqlParams Parametri query 
     * @return string Comando sql
     */
    public function getSqlLeggiBgeTestitChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGTESTIT'] = $cod;
        return self::getSqlLeggiBgeTestit($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_TESTIT per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBgeTestitChiave($cod) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeTestitChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BGE_APPLI

    /**
     * Restituisce comando sql per lettura tabella BGE_APPLI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAppli($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_APPLI.* FROM BGE_APPLI WHERE BGE_APPLI.APPLI_KEY='AA'";
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_APPLI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAppli($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAppli($filtri, false, $sqlParams), true, $sqlParams);
    }

    // BGE_AGID_SCADENZE

    public function getSqlLeggiMaxIuv($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_AGID_SCADENZE.IUV FROM BGE_AGID_SCADENZE";
        $where = 'WHERE';
        if (array_key_exists('DATACREAZ', $filtri) && $filtri['DATACREAZ'] != null) {
            $this->addSqlParam($sqlParams, "DATACREAZ", $filtri['DATACREAZ'], PDO::PARAM_INT);
            $sql .= " $where DATACREAZ=:DATACREAZ";
            $where = 'AND';
        }
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != 0) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", $filtri['CODTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] !== null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", $filtri['SUBTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != 0) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB desc';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiMaxIuv($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiMaxIuv($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBgeAgidScadenze($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_AGID_SCADENZE.* FROM BGE_AGID_SCADENZE";
        $sql .= $this->addFilterAgidScadenze($filtri, $sqlParams);

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB asc';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenze($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenze($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGE_AGID_SCADENZE
     * @param string $cod Chiave    
     * @param array $sqlParams Parametri query 
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadenzeChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGKEYTAB'] = $cod;
        return self::getSqlLeggiBgeAgidScadenze($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBgeAgidScadenzeChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzeChiave($cod, $sqlParams), false, $sqlParams);
    }

    private function addFilterAgidScadenze($filtri, &$sqlParams) {
        $sql = "";
        $where = 'WHERE';
        if (array_key_exists('DATACREAZ', $filtri) && $filtri['DATACREAZ'] != null) {
            $this->addSqlParam($sqlParams, "DATACREAZ", $filtri['DATACREAZ'], PDO::PARAM_INT);
            $sql .= " $where DATACREAZ=:DATACREAZ";
            $where = 'AND';
        }
        if (array_key_exists('CODFISCALE', $filtri) && $filtri['CODFISCALE'] != null) {
            $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CODFISCALE") . " LIKE :CODFISCALE";
            $where = 'AND';
        }
        if (array_key_exists('PROGCITYSC', $filtri) && $filtri['PROGCITYSC'] != 0) {
            $this->addSqlParam($sqlParams, "PROGCITYSC", $filtri['PROGCITYSC'], PDO::PARAM_INT);
            $sql .= " $where PROGCITYSC=:PROGCITYSC";
            $where = 'AND';
        }
        if (array_key_exists('PROGCITYSCA', $filtri) && $filtri['PROGCITYSCA'] != '') {
            $this->addSqlParam($sqlParams, "PROGCITYSCA", addslashes(strtoupper(trim($filtri['PROGCITYSCA']))), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("PROGCITYSCA") . " = :PROGCITYSCA";
            $where = 'AND';
        }
        if (array_key_exists('IDPENDEN', $filtri) && $filtri['IDPENDEN'] != 0) {
            $this->addSqlParam($sqlParams, "IDPENDEN", $filtri['IDPENDEN'], PDO::PARAM_INT);
            $sql .= " $where IDPENDEN=:IDPENDEN";
            $where = 'AND';
        }
        if (array_key_exists('TIPOPENDEN', $filtri) && $filtri['TIPOPENDEN'] != 0) {
            $this->addSqlParam($sqlParams, "TIPOPENDEN", $filtri['TIPOPENDEN'], PDO::PARAM_INT);
            $sql .= " $where TIPOPENDEN=:TIPOPENDEN";
            $where = 'AND';
        }
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != 0) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", $filtri['CODTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", $filtri['SUBTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != 0) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('STATO', $filtri) && $filtri['STATO'] != 0) {
            $this->addSqlParam($sqlParams, "STATO", $filtri['STATO'], PDO::PARAM_INT);
            $sql .= " $where STATO=:STATO";
            $where = 'AND';
        }
        if (array_key_exists('STATO_maggiore', $filtri) && $filtri['STATO_maggiore'] != null) {
            $this->addSqlParam($sqlParams, "STATO_MAGGIORE", strtoupper(trim($filtri['STATO_maggiore'])), PDO::PARAM_INT);
            $sql .= " $where STATO>=:STATO_MAGGIORE and IUV = ' '";
            $where = 'AND';
        }
        if (array_key_exists('STATO_pubbl', $filtri) && $filtri['STATO_pubbl'] != null) {
            $this->addSqlParam($sqlParams, "STATO_PUBBL", strtoupper(trim($filtri['STATO_pubbl'])), PDO::PARAM_INT);
            $sql .= " $where STATO>=:STATO_PUBBL";
            $where = 'AND';
        }
        if (array_key_exists('STATO_minore', $filtri) && $filtri['STATO_minore'] != null) {
            $this->addSqlParam($sqlParams, "STATO_MINORE", strtoupper(trim($filtri['STATO_minore'])), PDO::PARAM_INT);
            $sql .= " $where STATO<:STATO_MINORE and IUV != null";
            $where = 'AND';
        }
        if (array_key_exists('STATO_or', $filtri) && $filtri['STATO_or'] != null) {
            $sql .= " $where";
            $sql .= "(";
            foreach ($filtri['STATO_or'] as $key => $value) {
                $this->addSqlParam($sqlParams, "STATO_OR" . $key, strtoupper(trim($filtri['STATO_or'][$key])), PDO::PARAM_INT);
                $sql .= " STATO=:STATO_OR" . $key . " OR";
            }
            $sql = substr($sql, 0, -2);
            $sql .= ")";
            $where = 'AND';
        }
        if (array_key_exists('ANNORIF', $filtri) && $filtri['ANNORIF'] != 0) {
            $this->addSqlParam($sqlParams, "ANNORIF", $filtri['ANNORIF'], PDO::PARAM_INT);
            $sql .= " $where ANNORIF=:ANNORIF";
            $where = 'AND';
        }
        if (array_key_exists('PROGINV', $filtri) && $filtri['PROGINV'] != 0) {
            $this->addSqlParam($sqlParams, "PROGINV", $filtri['PROGINV'], PDO::PARAM_INT);
            $sql .= " $where PROGINV=:PROGINV";
            $where = 'AND';
        }
        if (array_key_exists('PROGSOGG', $filtri) && $filtri['PROGSOGG'] != 0) {
            $this->addSqlParam($sqlParams, "PROGSOGG", $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where PROGSOGG=:PROGSOGG";
            $where = 'AND';
        }
        if (array_key_exists('NUMDOC', $filtri) && $filtri['NUMDOC'] != 0) {
            $this->addSqlParam($sqlParams, "NUMDOC", $filtri['NUMDOC'], PDO::PARAM_INT);
            $sql .= " $where NUMDOC=:NUMDOC";
            $where = 'AND';
        }
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] != 0) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " $where ANNOEMI=:ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] != 0) {
            $this->addSqlParam($sqlParams, "NUMEMI", $filtri['NUMEMI'], PDO::PARAM_INT);
            $sql .= " $where NUMEMI=:NUMEMI";
            $where = 'AND';
        }
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] != 0) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", $filtri['IDBOL_SERE'], PDO::PARAM_INT);
            $sql .= " $where IDBOL_SERE=:IDBOL_SERE";
            $where = 'AND';
        }
        if (array_key_exists('CODRIFERIMENTO', $filtri)) {
            $this->addSqlParam($sqlParams, "CODRIFERIMENTO", $filtri['CODRIFERIMENTO'], PDO::PARAM_STR);
            $sql .= " $where CODRIFERIMENTO=:CODRIFERIMENTO";
            $where = 'AND';
        }
        if (array_key_exists('IUV', $filtri) && $filtri['IUV'] != null && $filtri['IUV'] != '0') {
            $this->addSqlParam($sqlParams, "IUV", $filtri['IUV'], PDO::PARAM_STR);
            $sql .= " $where IUV=:IUV";
            $where = 'AND';
        }
        if (array_key_exists('MODPROVEN', $filtri) && $filtri['MODPROVEN'] != null) {
            $this->addSqlParam($sqlParams, "MODPROVEN", $filtri['MODPROVEN'], PDO::PARAM_STR);
            $sql .= " $where MODPROVEN=:MODPROVEN";
            $where = 'AND';
        }
        if (array_key_exists('NUMRATA', $filtri) && $filtri['NUMRATA'] !== null) {
            $this->addSqlParam($sqlParams, "NUMRATA", $filtri['NUMRATA'], PDO::PARAM_INT);
            $sql .= " $where NUMRATA=:NUMRATA";
            $where = 'AND';
        }
        if (array_key_exists('DESCRPEND', $filtri) && $filtri['DESCRPEND'] != null) {
            $this->addSqlParam($sqlParams, "DESCRPEND", "%" . strtoupper(trim($filtri['DESCRPEND'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESCRPEND") . " LIKE :DESCRPEND";
            $where = 'AND';
        }
        if (array_key_exists('DATAPAGAM', $filtri) && $filtri['DATAPAGAM'] != null) {
            $this->addSqlParam($sqlParams, "DATAPAGAM", $filtri['DATAPAGAM'], PDO::PARAM_STR);
            $sql .= " $where DATAPAGAM=:DATAPAGAM";
            $where = 'AND';
        } else if (array_key_exists('DATAPAGAM_DA', $filtri) && $filtri['DATAPAGAM_DA'] != null && array_key_exists('DATAPAGAM_A', $filtri) && $filtri['DATAPAGAM_A'] != null) {
            $this->addSqlParam($sqlParams, "DATAPAGAM_DA", $filtri['DATAPAGAM_DA'], PDO::PARAM_STR);
            $this->addSqlParam($sqlParams, "DATAPAGAM_A", $filtri['DATAPAGAM_A'], PDO::PARAM_STR);
            $sql .= " $where (DATAPAGAM>=:DATAPAGAM_DA AND DATAPAGAM<=:DATAPAGAM_A)";
            $where = 'AND';
        }

        return $sql;
    }

    // BGE_AGID_SCADENZE per Rendicontazione NSS

    public function getSqlLeggiBgeAgidScadenzeRendNSS($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_AGID_SCADENZE.* FROM BGE_AGID_SCADENZE";
        $sql .= $excludeOrderBy ? '' : ' ORDER BY DATAPUBBL ASC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzeRendNSS($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzeRendNSS($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_SCADENZE raggruppato per CODSERVIZIO
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadenzeGroupByServizio($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_AGID_SCADENZE.PROGINV,CODSERVIZIO,INTERMEDIARIO,BTA_SERVREND.CODTIPSCAD,BTA_SERVREND.SUBTIPSCAD,BTA_SERVREND.ANNOEMI,BTA_SERVREND.NUMEMI,BTA_SERVREND.IDBOL_SERE  FROM BTA_SERVREND


                INNER JOIN BTA_SERVRENDPPA ON BTA_SERVREND.PROGKEYTAB = BTA_SERVRENDPPA.IDSERVREND
                INNER JOIN BGE_AGID_SCADENZE ON BGE_AGID_SCADENZE.CODTIPSCAD=BTA_SERVREND.CODTIPSCAD 
                AND BGE_AGID_SCADENZE.SUBTIPSCAD=BTA_SERVREND.SUBTIPSCAD AND BGE_AGID_SCADENZE.ANNOEMI=BTA_SERVREND.ANNOEMI 
                AND BGE_AGID_SCADENZE.NUMEMI=BTA_SERVREND.NUMEMI AND BGE_AGID_SCADENZE.IDBOL_SERE=BTA_SERVREND.IDBOL_SERE";
        $where = 'WHERE';
        if (array_key_exists('perPubblicazione', $filtri) && $filtri['perPubblicazione'] !== null) {
            $sql .= " $where STATO IN (1,3)";
            $where = 'AND';
        }
        if (array_key_exists('INTERMEDIARIO', $filtri) && $filtri['INTERMEDIARIO'] !== null) {
            $this->addSqlParam($sqlParams, "INTERMEDIARIO", $filtri['INTERMEDIARIO'], PDO::PARAM_INT);
            $sql .= " AND BTA_SERVRENDPPA.INTERMEDIARIO=:INTERMEDIARIO";
            $where = 'AND';
        }
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] !== null) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " AND BTA_SERVREND.ANNOEMI=:ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] !== null) {
            $this->addSqlParam($sqlParams, "NUMEMI", $filtri['NUMEMI'], PDO::PARAM_INT);
            $sql .= " AND BTA_SERVREND.NUMEMI=:NUMEMI";
            $where = 'AND';
        }
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] !== null) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", $filtri['IDBOL_SERE'], PDO::PARAM_INT);
            $sql .= " AND BTA_SERVREND.IDBOL_SERE=:IDBOL_SERE";
            $where = 'AND';
        }
        $sql .= " GROUP BY  BGE_AGID_SCADENZE.PROGINV,CODSERVIZIO,INTERMEDIARIO,BTA_SERVREND.CODTIPSCAD,"
                . " BTA_SERVREND.SUBTIPSCAD,BTA_SERVREND.ANNOEMI,BTA_SERVREND.NUMEMI,BTA_SERVREND.IDBOL_SERE";
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzeGroupByServizio($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzeGroupByServizio($filtri, false, $sqlParams), true, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_SCADENZE raggruppato per CODSERVIZIO PER EFFETTUARE LA CANCELLAZIONE
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadenzeGroupByServizioCanc($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_AGID_SCADENZE.PROGINV,CODSERVIZIO,INTERMEDIARIO,BTA_SERVREND.CODTIPSCAD,BTA_SERVREND.SUBTIPSCAD,BTA_SERVREND.ANNOEMI,BTA_SERVREND.NUMEMI,BTA_SERVREND.IDBOL_SERE  FROM BTA_SERVREND
                INNER JOIN BTA_SERVRENDPPA ON BTA_SERVREND.PROGKEYTAB = BTA_SERVRENDPPA.IDSERVREND
                INNER JOIN BGE_AGID_SCADENZE ON BGE_AGID_SCADENZE.CODTIPSCAD=BTA_SERVREND.CODTIPSCAD
                AND BGE_AGID_SCADENZE.SUBTIPSCAD=BTA_SERVREND.SUBTIPSCAD AND BGE_AGID_SCADENZE.ANNOEMI=BTA_SERVREND.ANNOEMI 
                AND BGE_AGID_SCADENZE.NUMEMI=BTA_SERVREND.NUMEMI AND BGE_AGID_SCADENZE.IDBOL_SERE=BTA_SERVREND.IDBOL_SERE
                LEFT JOIN BWE_PENDEN ON BGE_AGID_SCADENZE.CODTIPSCAD = BWE_PENDEN.CODTIPSCAD
                AND BGE_AGID_SCADENZE.SUBTIPSCAD = BWE_PENDEN.SUBTIPSCAD 
                AND BGE_AGID_SCADENZE.PROGCITYSC = BWE_PENDEN.PROGCITYSC 
                AND BGE_AGID_SCADENZE.ANNORIF = BWE_PENDEN.ANNORIF 
                AND BGE_AGID_SCADENZE.NUMRATA = BWE_PENDEN.NUMRATA
                where BGE_AGID_SCADENZE.STATO <=5";
        $where = 'AND';
        if (array_key_exists('INTERMEDIARIO', $filtri) && $filtri['INTERMEDIARIO'] !== null) {
            $this->addSqlParam($sqlParams, "INTERMEDIARIO", $filtri['INTERMEDIARIO'], PDO::PARAM_INT);
            $sql .= " AND BTA_SERVRENDPPA.INTERMEDIARIO=:INTERMEDIARIO";
            $where = 'AND';
        }
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] !== null) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " AND BTA_SERVREND.ANNOEMI=:ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] !== null) {
            $this->addSqlParam($sqlParams, "NUMEMI", $filtri['NUMEMI'], PDO::PARAM_INT);
            $sql .= " AND BTA_SERVREND.NUMEMI=:NUMEMI";
            $where = 'AND';
        }
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] !== null) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", $filtri['IDBOL_SERE'], PDO::PARAM_INT);
            $sql .= " AND BTA_SERVREND.IDBOL_SERE=:IDBOL_SERE";
            $where = 'AND';
        }
        $sql .= " AND (BWE_PENDEN.PROGKEYTAB IS NULL or BWE_PENDEN.STATO >4) AND BGE_AGID_SCADENZE.TIP_INS <> 2 GROUP BY  BGE_AGID_SCADENZE.PROGINV,CODSERVIZIO,INTERMEDIARIO,BTA_SERVREND.CODTIPSCAD,"
                . " BTA_SERVREND.SUBTIPSCAD,BTA_SERVREND.ANNOEMI,BTA_SERVREND.NUMEMI,BTA_SERVREND.IDBOL_SERE";
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzeGroupByServizioCanc($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzeGroupByServizioCanc($filtri, false, $sqlParams), true, $sqlParams);
    }

    /**
     * Restituisce comando sql per verificare se, quando sto gestendo la ricevuta di Arricchimento, ci sono ancora SCADENZE con STATO = 4
     * legate all'invio
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadenzeArricchimento($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT COUNT(*) FROM BGE_AGID_SCADENZE WHERE";
        if (array_key_exists('PROGINV', $filtri) && $filtri['PROGINV'] !== null) {
            $this->addSqlParam($sqlParams, "PROGINV", $filtri['PROGINV'], PDO::PARAM_INT);
            $sql .= " PROGINV=:PROGINV";
        }
        $sql .= " AND STATO = 4";
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzeArricchimento($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzeArricchimento($filtri, false, $sqlParams), false, $sqlParams);
    }

    // BGE_AGID_SCADENZE da RIPRISTINARE (STATO=0)

    public function getSqlLeggiBgeAgidScadenzeDaRipristinare($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_AGID_SCADENZE.* FROM BGE_AGID_SCADENZE WHERE STATO = 0";
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB asc';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzeDaRipristinare($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzeDaRipristinare($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per verificare se, quando sto gestendo la ricevuta di Arricchimento, ci sono ancora SCADENZE con STATO = 4
     * legate all'invio
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadenzeArricchimentoNSS($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_AGID_SCADENZE.* FROM BGE_AGID_SCADENZE WHERE";
        if (array_key_exists('PROGINV', $filtri) && $filtri['PROGINV'] !== null) {
            $this->addSqlParam($sqlParams, "PROGINV", $filtri['PROGINV'], PDO::PARAM_INT);
            $sql .= " PROGINV=:PROGINV";
        }
        $sql .= " AND STATO = 4";
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzeArricchimentoNSS($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzeArricchimentoNSS($filtri, false, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql, quando sto creando una fornitura di Cancellazione, per verificare con quale STATO dovr� andare
     * ad aggioranre le scadenze da cancellare... STATO = 6 se CONTA = 0, STATO = 8 se CONTA > 0
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadenzeCountPerStato($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT COUNT(*) AS CONTA FROM BGE_AGID_SCADENZE";
        $where = ' WHERE ';
        if (array_key_exists('PROGCITYSC', $filtri) && $filtri['PROGCITYSC'] !== null) {
            $this->addSqlParam($sqlParams, "PROGCITYSC", $filtri['PROGCITYSC'], PDO::PARAM_INT);
            $sql .= " $where PROGCITYSCORI=:PROGCITYSC";
            $where = 'AND';
        }
        if (array_key_exists('PROGINV', $filtri) && $filtri['PROGINV'] !== null) {
            $this->addSqlParam($sqlParams, "PROGINV", $filtri['PROGINV'], PDO::PARAM_INT);
            $sql .= " $where PROGINV=:PROGINV";
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
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzeCountPerStato($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzeCountPerStato($filtri, false, $sqlParams), true, $sqlParams);
    }

    /**
     * Restituisce la lista delle emissioni da pubblicare
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiEmissioniDaPubblicare($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT DISTINCT CODSERVIZIO,INTERMEDIARIO,BTA_SERVREND.CODTIPSCAD,BTA_SERVREND.SUBTIPSCAD,
        BTA_SERVREND.ANNOEMI,BTA_SERVREND.NUMEMI,BTA_SERVREND.IDBOL_SERE FROM BWE_PENDEN 
        LEFT JOIN BGE_AGID_SCADENZE ON BGE_AGID_SCADENZE.CODTIPSCAD = BWE_PENDEN.CODTIPSCAD AND 
        BGE_AGID_SCADENZE.SUBTIPSCAD = BWE_PENDEN.SUBTIPSCAD AND BGE_AGID_SCADENZE.PROGCITYSC = BWE_PENDEN.PROGCITYSC AND 
        BGE_AGID_SCADENZE.ANNORIF = BWE_PENDEN.ANNORIF AND BGE_AGID_SCADENZE.NUMRATA = BWE_PENDEN.NUMRATA
        INNER JOIN BTA_SERVREND ON BTA_SERVREND.CODTIPSCAD=BWE_PENDEN.CODTIPSCAD AND 
        BTA_SERVREND.SUBTIPSCAD=BWE_PENDEN.SUBTIPSCAD AND 
        BTA_SERVREND.ANNOEMI=BWE_PENDEN.ANNOEMI AND BTA_SERVREND.NUMEMI=BWE_PENDEN.NUMEMI AND 
        BTA_SERVREND.IDBOL_SERE=BWE_PENDEN.IDBOL_SERE
        INNER JOIN BTA_SERVRENDPPA ON BTA_SERVREND.PROGKEYTAB = BTA_SERVRENDPPA.IDSERVREND 
        WHERE 
        FLAG_PUBBL>=3 ";

        if (array_key_exists('INTERMEDIARIO', $filtri) && $filtri['INTERMEDIARIO'] !== null) {
            $this->addSqlParam($sqlParams, "INTERMEDIARIO", $filtri['INTERMEDIARIO'], PDO::PARAM_INT);
            $sql .= " AND INTERMEDIARIO = :INTERMEDIARIO";
        }
        if (array_key_exists('FLAG_PUBBL_IN', $filtri)) {
            $sql .= " AND FLAG_PUBBL IN (" . implode(", ", $filtri['FLAG_PUBBL_IN']) . ")";
        }

        $sql .= " AND ((BWE_PENDEN.IMPPAGTOT>=0 AND BWE_PENDEN.STATO < 4) OR (BWE_PENDEN.IMPPAGTOT>0 AND 
        BWE_PENDEN.STATO = 1 AND BWE_PENDEN.TIPOPENDEN = 1)) 
        AND ((BGE_AGID_SCADENZE.CODTIPSCAD IS NULL AND BGE_AGID_SCADENZE.SUBTIPSCAD IS NULL AND BGE_AGID_SCADENZE.PROGCITYSC IS NULL AND BGE_AGID_SCADENZE.ANNORIF IS NULL AND BGE_AGID_SCADENZE.NUMRATA IS NULL) OR BGE_AGID_SCADENZE.STATO = 1) 
        GROUP BY CODSERVIZIO,INTERMEDIARIO,BTA_SERVREND.CODTIPSCAD,BTA_SERVREND.SUBTIPSCAD,
        BTA_SERVREND.ANNOEMI,BTA_SERVREND.NUMEMI,BTA_SERVREND.IDBOL_SERE";

        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiEmissioniDaPubblicare($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiEmissioniDaPubblicare($filtri, false, $sqlParams), true, $sqlParams);
    }

    // BGE_AGID_CONF_EFIL

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_CONF_EFIL
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidConfEfil($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT PROGKEYTAB,"
                . "AUXDIGIT,"
                . "IDGESTIONALE,"
                . "APPLCODE, "
                . "CODENTECRED,"
                . "GENERAIUV,"
                . "FILLER,"
                . "SFTPHOST,"
                . "SFTPUSER,"
                . "SFTPPASSWORD,"
                . $this->getCitywareDB()->adapterBlob("SFTPFILEKEY") . " , "
                . "SFTPCARTPUBBL,"
                . "SFTPCARTCANC,"
                . "SFTPCARTARRIC,"
                . "SFTPCARTREND,"
                . "SFTPCARTRT,"
                . "SFTPCARTRIC,"
                . "CODUTE,"
                . "DATAOPER,"
                . "TIMEOPER,"
                . "FLAG_DIS,"
                . "IDAPPLICAZIONE, "
                . "IDCHIAMANTE, "
                . "PSWCHIAMANTE, "
                . "CODICECONTRATTO, "
                . "IDFORNITORE, "
                . "IDAPPLICAZIONEF24ZZ, "
                . "PUNTOVENDITA, "
                . "SPORTELLO "
                . "FROM BGE_AGID_CONF_EFIL ";
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_CONF_EFIL
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidConfEfil($filtri = array()) {
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BGE", "leggiBgeAgidConfEfilBinary"); //DONE
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidConfEfil($filtri, false, $sqlParams), false, $sqlParams, $infoBinaryCallback);
    }

    //risultato del caricamento
    public function leggiBgeAgidConfEfilBinary($result = array()) {
        $sql = "SELECT " . $this->getCitywareDB()->adapterBlob("SFTPFILEKEY") . " FROM BGE_AGID_CONF_EFIL WHERE PROGKEYTAB = :PROGKEYTAB";
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "PROGKEYTAB", $result['PROGKEYTAB'], PDO::PARAM_INT);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "SFTPFILEKEY", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['SFTPFILEKEY'] = $resultBin['SFTPFILEKEY'];

        return $result;
    }

    /**
      //BGE_AGID_INVII
      /**
     * Restituisce comando sql per lettura MAX(PROGINT) da BGE_AGID_INVII 
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidInviiMaxProgint($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT MAX(PROGINT) AS MAXPROGINT FROM BGE_AGID_INVII";
        $where = ' WHERE ';
        if (array_key_exists('DATAINVIO', $filtri) && $filtri['DATAINVIO'] != null) {
            $this->addSqlParam($sqlParams, "DATAINVIO", strtoupper(trim($filtri['DATAINVIO'])), PDO::PARAM_STR);
            $sql .= " $where DATAINVIO=:DATAINVIO";
            $where = 'AND';
        }
        if (array_key_exists('CODSERVIZIO', $filtri) && $filtri['CODSERVIZIO'] != null) {
            $this->addSqlParam($sqlParams, "CODSERVIZIO", strtoupper(trim($filtri['CODSERVIZIO'])), PDO::PARAM_INT);
            $sql .= " $where CODSERVIZIO=:CODSERVIZIO";
            $where = 'AND';
        }
        if (array_key_exists('TIPO', $filtri) && $filtri['TIPO'] != null) {
            $this->addSqlParam($sqlParams, "TIPO", strtoupper(trim($filtri['TIPO'])), PDO::PARAM_INT);
            $sql .= " $where TIPO=:TIPO";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_INVII
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidInviiMaxProgint($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidInviiMaxProgint($filtri, false, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_INVII 
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidInviiRicevuta($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_AGID_INVII.* FROM BGE_AGID_INVII "
                . "INNER JOIN BGE_AGID_ALLEGATI ON BGE_AGID_ALLEGATI.IDINVRIC = BGE_AGID_INVII.PROGKEYTAB ";
        $where = ' WHERE ';
        if (array_key_exists('NOME_FILE', $filtri) && $filtri['NOME_FILE'] != null) {
            $this->addSqlParam($sqlParams, "NOME_FILE", strtoupper(trim($filtri['NOME_FILE'])), PDO::PARAM_STR);
            $sql .= " $where NOME_FILE=:NOME_FILE";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_INVII
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidInviiRicevuta($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidInviiRicevuta($filtri, false, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura NOME_FILE da BGE_AGID_INVII in JOIN con BGE_AGID_ALLEGATI.
     * Utilizzata per reperire la lista di File da trattare 
     * (es. nella gestione ricevuta_accettazione, ricevuta_pubblicazione ecc.) 
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidInviiNomeFile($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT NOME_FILE FROM BGE_AGID_INVII "
                . "INNER JOIN BGE_AGID_ALLEGATI ON BGE_AGID_ALLEGATI.IDINVRIC = BGE_AGID_INVII.PROGKEYTAB ";
        $where = ' WHERE ';
        if (array_key_exists('STATO', $filtri) && $filtri['STATO'] != null) {
            $this->addSqlParam($sqlParams, "STATO", strtoupper(trim($filtri['STATO'])), PDO::PARAM_INT);
            $sql .= " $where STATO=:STATO";
            $where = 'AND';
        }
        if (array_key_exists('INTERMEDIARIO', $filtri) && $filtri['INTERMEDIARIO'] != null) {
            $this->addSqlParam($sqlParams, "INTERMEDIARIO", strtoupper(trim($filtri['INTERMEDIARIO'])), PDO::PARAM_INT);
            $sql .= " $where INTERMEDIARIO=:INTERMEDIARIO";
            $where = 'AND';
        }
        if (array_key_exists('TIPO', $filtri) && $filtri['TIPO'] != null) {
            $this->addSqlParam($sqlParams, "TIPO", strtoupper(trim($filtri['TIPO'])), PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_ALLEGATI.TIPO=:TIPO";
            $where = 'AND';
        }
        if (array_key_exists('CODSERVIZIO', $filtri) && $filtri['CODSERVIZIO'] != null) {
            $this->addSqlParam($sqlParams, "CODSERVIZIO", strtoupper(trim($filtri['CODSERVIZIO'])), PDO::PARAM_STR);
            $sql .= " $where BGE_AGID_INVII.CODSERVIZIO=:CODSERVIZIO";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_INVII
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidInviiNomeFile($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidInviiNomeFile($filtri, false, $sqlParams), true, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_SCA_EFIL
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScaEfil($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT ID,"
                . "PROGKEYTAB,"
                . "NUMAVVISO,"
                . "PARAMPOS01,"
                . "PARAMPOS02,"
                . "PARAMPOS03,"
                . "PARAMPOS04,"
                . "PARAMPOS05,"
                . "PARAMPOS06,"
                . "ANADEBITORE,"
                . "DTINIVAL,"
                . "DTFINVAL,"
                . $this->getCitywareDB()->adapterBlob("REQUEST") . " , "
                . $this->getCitywareDB()->adapterBlob("RESPONSE") . " , "
                . "CODUTE,"
                . "DATAOPER,"
                . "TIMEOPER,"
                . "FLAG_DIS"
                . " FROM BGE_AGID_SCA_EFIL ";

        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", strtoupper(trim($filtri['PROGKEYTAB'])), PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB';

        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCA_EFIL
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScaEfil($filtri, $multipla = true) {
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BGE", "leggiBgeAgidScaEfilBinary"); //DONE
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScaEfil($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGE_AGID_SCA_EFIL
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScaEfilChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGKEYTAB'] = $cod;
        return self::getSqlLeggiBgeAgidScaEfil($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCA_EFIL per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBgeAgidScaEfilChiave($cod) {
        if (!$cod) {
            return null;
        }
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BGE", "leggiBgeAgidScaEfilBinary"); //DONE

        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScaEfilChiave($cod, $sqlParams), false, $sqlParams);
    }

    //risultato del caricamento
    public function leggiBgeAgidScaEfilBinary($result = array()) {
        $sql = "SELECT " . $this->getCitywareDB()->adapterBlob("REQUEST") . ", " . $this->getCitywareDB()->adapterBlob("RESPONSE") . " FROM BGE_AGID_SCA_EFIL WHERE PROGKEYTAB = :PROGKEYTAB";
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "PROGKEYTAB", $result['PROGKEYTAB'], PDO::PARAM_INT);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "REQUEST", 1);
        $this->addBinaryFieldsDescribe($sqlFields, "RESPONSE", 2);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['REQUEST'] = $resultBin['REQUEST'];
        $result['RESPONSE'] = $resultBin['RESPONSE'];

        return $result;
    }

    //BGE_AGID_ALLEGATI

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_ALLEGATI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidAllegati($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT PROGKEYTAB,"
                . "TIPO,"
                . "IDINVRIC,"
                . "DATA,"
                . "NOME_FILE,"
                . $this->getCitywareDB()->adapterBlob("ZIPFILE") . " , "
                . "CODUTE,"
                . "DATAOPER,"
                . "TIMEOPER,"
                . "FLAG_DIS"
                . " FROM BGE_AGID_ALLEGATI ";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", strtoupper(trim($filtri['PROGKEYTAB'])), PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('IDINVRIC', $filtri) && $filtri['IDINVRIC'] != null) {
            $this->addSqlParam($sqlParams, "IDINVRIC", strtoupper(trim($filtri['IDINVRIC'])), PDO::PARAM_INT);
            $sql .= " $where IDINVRIC=:IDINVRIC";
            $where = 'AND';
        }
        if (array_key_exists('TIPO', $filtri) && $filtri['TIPO'] != null) {
            $this->addSqlParam($sqlParams, "TIPO", strtoupper(trim($filtri['TIPO'])), PDO::PARAM_INT);
            $sql .= " $where TIPO=:TIPO";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB';

        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_ALLEGATI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidAllegati($filtri, $multipla = true) {
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BGE", "leggiBgeAgidAllegatiBinary"); //DONE
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidAllegati($filtri, false, $sqlParams), $multipla, $sqlParams, $infoBinaryCallback);
    }

    //BGE_AGID_ALLEGATI

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_ALLEGATI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidAllegatiRend($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT distinct nome_file from bge_agid_allegati";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", strtoupper(trim($filtri['PROGKEYTAB'])), PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('IDINVRIC', $filtri) && $filtri['IDINVRIC'] != null) {
            $this->addSqlParam($sqlParams, "IDINVRIC", strtoupper(trim($filtri['IDINVRIC'])), PDO::PARAM_INT);
            $sql .= " $where IDINVRIC=:IDINVRIC";
            $where = 'AND';
        }
        if (array_key_exists('TIPO', $filtri) && $filtri['TIPO'] != null) {
            $this->addSqlParam($sqlParams, "TIPO", strtoupper(trim($filtri['TIPO'])), PDO::PARAM_INT);
            $sql .= " $where TIPO=:TIPO";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_ALLEGATI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidAllegatiRend($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidAllegatiRend($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_ALLEGATI per la Console Nodo.
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidAllegatiConsoleNodo($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT * FROM BGE_AGID_ALLEGATI WHERE NOME_FILE in "
                . "(SELECT NOME_FILE FROM BGE_AGID_ALLEGATI ";
        //. " idinvric=[BGE_AGID_INVII.PROGKEYTAB] AND TIPO=[BGE_AGID_INVII.TIPO])";
        $where = 'WHERE';
        if (array_key_exists('TIPO', $filtri) && $filtri['TIPO'] != null) {
            $this->addSqlParam($sqlParams, "TIPO", strtoupper(trim($filtri['TIPO'])), PDO::PARAM_INT);
            $sql .= " $where TIPO=:TIPO";
            $where = 'AND';
        }
        if (array_key_exists('IDINVRIC', $filtri) && $filtri['IDINVRIC'] != null) {
            $this->addSqlParam($sqlParams, "IDINVRIC", strtoupper(trim($filtri['IDINVRIC'])), PDO::PARAM_INT);
            $sql .= " $where IDINVRIC=:IDINVRIC";
            $where = 'AND';
        }
        $sql .= ')';

        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_ALLEGATI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidAllegatiConsoleNodo($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidAllegatiConsoleNodo($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per comando BGE_AGID_ALLEGATI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlCountBgeAgidAllegati($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT COUNT (BGE_AGID_ALLEGATI.*) FROM BGE_AGID_ALLEGATI";
        $where = 'WHERE';
        if (array_key_exists('NOME_FILE', $filtri) && $filtri['NOME_FILE'] != null) {
            $this->addSqlParam($sqlParams, "NOME_FILE", strtoupper(trim($filtri['NOME_FILE'])), PDO::PARAM_STR);
            $sql .= " $where NOME_FILE=:NOME_FILE";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_ALLEGATI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function countBgeAgidAllegati($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlCountBgeAgidAllegati($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGE_AGID_ALLEGATI
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidAllegatiChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGKEYTAB'] = $cod;
        return self::getSqlLeggiBgeAgidAllegati($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_AGID_ALLEGATI per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBgeAgidAllegatiChiave($cod) {
        if (!$cod) {
            return null;
        }
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BGE", "leggiBgeAgidAllegatiBinary"); //DONE

        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidAllegatiChiave($cod, $sqlParams), false, $sqlParams, $infoBinaryCallback);
    }

    //risultato del caricamento
    public function leggiBgeAgidAllegatiBinary($result = array()) {
        $sql = "SELECT " . $this->getCitywareDB()->adapterBlob("ZIPFILE") . " FROM BGE_AGID_ALLEGATI WHERE PROGKEYTAB = :PROGKEYTAB";
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "PROGKEYTAB", $result['PROGKEYTAB'], PDO::PARAM_INT);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "ZIPFILE", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['ZIPFILE'] = $resultBin['ZIPFILE'];

        return $result;
    }

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_INTERM
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidInterm($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGE_AGID_INTERM.* FROM BGE_AGID_INTERM";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", strtoupper(trim($filtri['PROGKEYTAB'])), PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('INTERMEDIARIO', $filtri) && $filtri['INTERMEDIARIO'] != null) {
            $this->addSqlParam($sqlParams, "INTERMEDIARIO", strtoupper(trim($filtri['INTERMEDIARIO'])), PDO::PARAM_INT);
            $sql .= " $where INTERMEDIARIO=:INTERMEDIARIO";
            $where = 'AND';
        }
        if (array_key_exists('DESCRIZIONE', $filtri) && $filtri['DESCRIZIONE'] != null) {
            $this->addSqlParam($sqlParams, "DESCRIZIONE", "%" . strtoupper(trim($filtri['DESCRIZIONE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESCRIZIONE") . " LIKE :DESCRIZIONE";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB';

        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_INTERM
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidInterm($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidInterm($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGE_AGID_INTERM
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidIntermChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGKEYTAB'] = $cod;
        return self::getSqlLeggiBgeAgidInterm($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_AGID_INTERM per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBgeAgidIntermChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidIntermChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BGE_AGID_INVII

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_INVII
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidInvii($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGE_AGID_INVII.* FROM BGE_AGID_INVII";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", strtoupper(trim($filtri['PROGKEYTAB'])), PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('STATO', $filtri) && $filtri['STATO'] != null) {
            $this->addSqlParam($sqlParams, "STATO", strtoupper(trim($filtri['STATO'])), PDO::PARAM_INT);
            $sql .= " $where STATO=:STATO";
            $where = 'AND';
        }
        if (array_key_exists('STATO_diverso', $filtri) && $filtri['STATO_diverso'] != null) {
            $this->addSqlParam($sqlParams, "STATO_DIVERSO", strtoupper(trim($filtri['STATO_diverso'])), PDO::PARAM_INT);

            $sql .= " $where STATO<>:STATO_DIVERSO";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB';

        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_INVII
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidInvii($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidInvii($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGE_AGID_INVII
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidInviiChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGKEYTAB'] = $cod;
        return self::getSqlLeggiBgeAgidInvii($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_AGID_INVII per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBgeAgidInviiChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidInviiChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BGE_AGID_INVII

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_INVII
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidInviiIdRuolo($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT DISTINCT IDRUOLO FROM BGE_AGID_INVII"
                . " INNER JOIN BGE_AGID_SCADENZE ON BGE_AGID_INVII.PROGKEYTAB=BGE_AGID_SCADENZE.PROGINV"
                . " INNER JOIN BGE_AGID_SCA_NSS on BGE_AGID_SCADENZE.PROGKEYTAB = BGE_AGID_SCA_NSS.PROGKEYTAB";
        $where = 'WHERE';
        if (array_key_exists('STATO', $filtri) && $filtri['STATO'] != null) {
            $this->addSqlParam($sqlParams, "STATO", strtoupper(trim($filtri['STATO'])), PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_INVII.STATO=:STATO";
            $where = 'AND';
        }

        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_INVII
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidInviiIdRuolo($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidInviiIdRuolo($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // BGE_AGID_INVII

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_INVII
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidInviiDaIdRuolo($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGE_AGID_INVII.* FROM BGE_AGID_INVII"
                . " INNER JOIN BGE_AGID_SCADENZE ON BGE_AGID_INVII.PROGKEYTAB=BGE_AGID_SCADENZE.PROGINV"
                . " INNER JOIN BGE_AGID_SCA_NSS on BGE_AGID_SCADENZE.PROGKEYTAB = BGE_AGID_SCA_NSS.PROGKEYTAB";
        $where = 'WHERE';
        if (array_key_exists('IDRUOLO', $filtri) && $filtri['IDRUOLO'] != null) {
            $this->addSqlParam($sqlParams, "IDRUOLO", $filtri['IDRUOLO'], PDO::PARAM_INT);
            $sql .= " $where IDRUOLO=:IDRUOLO";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_INVII
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidInviiDaIdRuolo($filtri, $multipla = FALSE) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidInviiDaIdRuolo($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // BGE_AGID_INVII per tab INVII su "Console Situazione Nodo"

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_INVII per popolare il tab INVII nella "Console Situazione Nodo"
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidInviiConsoleNodo($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT DISTINCT BGE_AGID_SCADENZE.ANNOEMI,BGE_AGID_SCADENZE.NUMEMI,BGE_AGID_SCADENZE.IDBOL_SERE,DES_GE60 EMISSIONE,
                DES_SEREMI SERVIZIOEMITTENTE,COUNT (BGE_AGID_SCADENZE.PROGKEYTAB) NUMPOSIZIONI, BGE_AGID_INVII.STATO,BGE_AGID_INVII.PROGKEYTAB,
                BGE_AGID_INVII.TIPO, BGE_AGID_INVII.DATAINVIO, BGE_AGID_INVII.codservizio, BGE_AGID_INVII.intermediario,"
                . $this->getCitywareDB()->strConcat('BGE_AGID_INVII.PROGKEYTAB', "'|'", 'BGE_AGID_SCADENZE.ANNOEMI', "'|'", 'BGE_AGID_SCADENZE.NUMEMI', "'|'", 'BGE_AGID_SCADENZE.IDBOL_SERE') . " AS \"ROW_ID\" "
                . " FROM BGE_AGID_INVII
                INNER JOIN BGE_AGID_SCADENZE ON BGE_AGID_INVII.PROGKEYTAB=BGE_AGID_SCADENZE.PROGINV
                INNER JOIN BTA_EMI ON BGE_AGID_SCADENZE.ANNOEMI=BTA_EMI.ANNOEMI AND BGE_AGID_SCADENZE.NUMEMI=BTA_EMI.NUMEMI AND 
                BGE_AGID_SCADENZE.IDBOL_SERE=BTA_EMI.IDBOL_SERE
                INNER JOIN BOR_IDBOL ON BOR_IDBOL.IDBOL_SERE=BGE_AGID_SCADENZE.IDBOL_SERE
		GROUP BY BGE_AGID_SCADENZE.ANNOEMI,BGE_AGID_SCADENZE.NUMEMI,BGE_AGID_SCADENZE.IDBOL_SERE,DES_GE60,
                DES_SEREMI, BGE_AGID_INVII.PROGKEYTAB, BGE_AGID_INVII.TIPO, BGE_AGID_SCADENZE.PROGINV,BGE_AGID_INVII.STATO,BGE_AGID_INVII.DATAINVIO,
                BGE_AGID_INVII.codservizio, BGE_AGID_INVII.intermediario";
        $sql .= $excludeOrderBy ? '' : ' ORDER BY BGE_AGID_INVII.PROGKEYTAB DESC';

        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_INVII
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidInviiConsoleNodo($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidInviiConsoleNodo($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // BGE_AGID_SCADENZE per Console Nodo... tab Scadenze non Riconciliate

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadenzeNonRicon($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGE_AGID_SCADENZE.* FROM BGE_AGID_SCADENZE"
                . " INNER JOIN BGE_AGID_RISCO ON BGE_AGID_RISCO.IDSCADENZA=BGE_AGID_SCADENZE.PROGKEYTAB"
                . " WHERE BGE_AGID_SCADENZE.STATO BETWEEN 10 AND 11";
        $sql .= $excludeOrderBy ? '' : ' ORDER BY BGE_AGID_SCADENZE.PROGKEYTAB DESC';

        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE per Console Nodo... tab Riscossioni non Riconciliate
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzeNonRicon($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzeNonRicon($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // BGE_AGID_RISCO per Console Nodo... tab Riscossioni non collegate a scadenze

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_RISCO
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidRiscoScadenzaNonCollegata($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGE_AGID_RISCO.* FROM BGE_AGID_RISCO"
                . " WHERE BGE_AGID_RISCO.IDSCADENZA = 0";
        $sql .= $excludeOrderBy ? '' : ' ORDER BY BGE_AGID_RISCO.PROGKEYTAB DESC';

        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_RISCO per Console Nodo... tab Riscossioni non collegate a scadenze
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidRiscoScadenzaNonCollegata($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidRiscoScadenzaNonCollegata($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // BGE_AGID_RISCO_MPAY
    public function getSqlLeggiBgeRiscoMpayConDettaglio($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT
                    BGE_AGID_RISCO.*
                FROM BGE_AGID_RISCO
                LEFT JOIN BGE_AGID_RISCO_MPAY ON BGE_AGID_RISCO_MPAY.PROGKEYTAB = BGE_AGID_RISCO.PROGKEYTAB";
        $where = 'WHERE';
        if (array_key_exists('IUV', $filtri) && $filtri['IUV'] != null) {
            $this->addSqlParam($sqlParams, "IUV", $filtri['IUV'], PDO::PARAM_STR);
            $sql .= " $where BGE_AGID_RISCO.IUV=:IUV";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_RISCO_MPAY
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeRiscoMpayConDettaglio($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeRiscoMpayConDettaglio($filtri, true, $sqlParams), $flMultipla, $sqlParams);
    }

    // BGE_AGID_RICEZ

    public function getSqlLeggiBgeAgidRicez($filtri, $excludeOrderBy = true, &$sqlParams) {
        $sql = "SELECT BGE_AGID_RICEZ.* FROM BGE_AGID_RICEZ";
        $where = 'WHERE';
        if (array_key_exists('IDINV', $filtri) && $filtri['IDINV'] != null) {
            $this->addSqlParam($sqlParams, "IDINV", $filtri['IDINV'], PDO::PARAM_INT);
            $sql .= " $where IDINV=:IDINV";
            $where = 'AND';
        }
        if (array_key_exists('TIPO', $filtri) && $filtri['TIPO'] != null) {
            $this->addSqlParam($sqlParams, "TIPO", $filtri['TIPO'], PDO::PARAM_INT);
            $sql .= " $where TIPO=:TIPO";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY DATARICEZ DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_RICEZ
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidRicez($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidRicez($filtri, true, $sqlParams), true, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGE_AGID_RICEZ
     * @param string $cod Chiave    
     * @param array $sqlParams Parametri query 
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidRicezChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGKEYTAB'] = $cod;
        return self::getSqlLeggiBgeAgidRicez($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_AGID_RICEZ per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBgeAgidRicezChiave($cod) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidRicezChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BGE_AGID_RICEZ + BGE_AGID_ALLEGATI

    public function getSqlLeggiBgeAgidRicezAllegato($filtri, $excludeOrderBy = true, &$sqlParams) {
        $sql = "SELECT BGE_AGID_RICEZ.*,BGE_AGID_ALLEGATI.NOME_FILE ,BGE_AGID_ALLEGATI.ZIPFILE FROM BGE_AGID_RICEZ"
                . " INNER JOIN BGE_AGID_ALLEGATI ON BGE_AGID_RICEZ.PROGKEYTAB = BGE_AGID_ALLEGATI.IDINVRIC";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_RICEZ.PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY DATARICEZ DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_RICEZ
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidRicezAllegato($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidRicezAllegato($filtri, true, $sqlParams), false, $sqlParams);
    }

    // BGE_AGID_RISCO

    public function getSqlLeggiBgeAgidRisco($filtri, $excludeOrderBy = true, &$sqlParams) {
        $sql = "SELECT BGE_AGID_RISCO.*, "
                . $this->getCitywareDB()->strConcat('PROGKEYTAB', "'|'", 'IDSCADENZA') . " AS \"ROW_ID\" "
                . " FROM BGE_AGID_RISCO";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('PROGRIC', $filtri) && $filtri['PROGRIC'] != null) {
            $this->addSqlParam($sqlParams, "PROGRIC", $filtri['PROGRIC'], PDO::PARAM_INT);
            $sql .= " $where PROGRIC=:PROGRIC";
            $where = 'AND';
        }
        if (array_key_exists('IDSCADENZA_maggiore', $filtri) && $filtri['IDSCADENZA_maggiore'] != 0) {
            $sql .= " $where IDSCADENZA>0";
            $where = 'AND';
        }
        if (array_key_exists('IDSCADENZA', $filtri) && $filtri['IDSCADENZA'] != 0) {
            $sql .= " $where IDSCADENZA=:IDSCADENZA";
            $where = 'AND';
        }
        if (array_key_exists('IUV', $filtri) && $filtri['IUV'] != null) {
            $this->addSqlParam($sqlParams, "IUV", $filtri['IUV'], PDO::PARAM_STR);
            $sql .= " $where IUV=:IUV";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY DATAPAG DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_RISCO
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidRisco($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidRisco($filtri, true, $sqlParams), $multipla, $sqlParams);
    }

    // BGE_AGID_RISCO_MPAY

    public function getSqlLeggiBgeAgidRiscoMpay($filtri, $excludeOrderBy = true, &$sqlParams) {
        $sql = "SELECT BGE_AGID_RISCO_MPAY.* FROM BGE_AGID_RISCO_MPAY";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_RISCO_MPAY
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidRiscoMpay($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidRiscoMpay($filtri, true, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGE_AGID_RISCO
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidRiscoChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGKEYTAB'] = $cod;
        return self::getSqlLeggiBgeAgidRiscoStatoScadenza($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_AGID_RISCO
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidRiscoChiave($cod) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidRiscoStatoScadenza($cod, $sqlParams), $multipla, $sqlParams);
    }

    // BGE_AGID_RISCO_EFIL

    public function getSqlLeggiBgeAgidRiscoEfil($filtri, $excludeOrderBy = true, &$sqlParams) {
        $sql = "SELECT BGE_AGID_RISCO_EFIL.*, "
                . $this->getCitywareDB()->strConcat('ID', "'|'", 'PROGKEYTAB') . " AS \"ROW_ID\" "
                . " FROM BGE_AGID_RISCO_EFIL";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_RISCO
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidRiscoEfil($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidRiscoEfil($filtri, true, $sqlParams), $multipla, $sqlParams);
    }

    // BGE_AGID_RISCO per Rendicontazione NSS

    public function getSqlLeggiBgeAgidRiscoRendNSS($filtri, $excludeOrderBy = true, &$sqlParams) {
        $sql = "SELECT BGE_AGID_RISCO.* FROM BGE_AGID_RISCO";
        $sql .= $excludeOrderBy ? '' : ' ORDER BY DATAINS DESC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_RISCO
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidRiscoRendNSS($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidRiscoRendNSS($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // BGE_AGID_RISCO_AAR

    public function getSqlLeggiBgeAgidRiscoAAR($filtri, $excludeOrderBy = true, &$sqlParams) {
        $sql = "SELECT BGE_AGID_RISCO_AAR.* FROM BGE_AGID_RISCO_AAR";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_RISCO_AAR
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidRiscoAAR($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidRiscoAAR($filtri, true, $sqlParams), $multipla, $sqlParams);
    }

    // BGE_AGID_RISCO stato Scadenza

    public function getSqlLeggiBgeAgidRiscoStatoScadenza($filtri, $excludeOrderBy = true, &$sqlParams) {
        $sql = "SELECT BGE_AGID_RISCO.*,BGE_AGID_SCADENZE.STATO,"
                . $this->getCitywareDB()->strConcat('BGE_AGID_RISCO.PROGKEYTAB', "'|'", 'IDSCADENZA', "'|'", 'BGE_AGID_RISCO.IUV') . " AS \"ROW_ID\" "
                . " FROM BGE_AGID_RISCO"
                . " INNER JOIN BGE_AGID_SCADENZE ON BGE_AGID_SCADENZE.PROGKEYTAB = BGE_AGID_RISCO.IDSCADENZA";
        $where = 'WHERE';
        if (array_key_exists('PROGRIC', $filtri) && $filtri['PROGRIC'] != null) {
            $this->addSqlParam($sqlParams, "PROGRIC", $filtri['PROGRIC'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_RISCO.PROGRIC=:PROGRIC";
            $where = 'AND';
        }
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_RISCO.PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('IUV', $filtri) && $filtri['IUV'] != null) {
            $this->addSqlParam($sqlParams, "IUV", $filtri['IUV'], PDO::PARAM_STR);
            $sql .= " $where BGE_AGID_RISCO.IUV=:IUV";
            $where = 'AND';
        }
        if ($filtri['DATAPAG_da'] != null && $filtri['DATAPAG_a'] != null) {
            $this->addSqlParam($sqlParams, "DATAPAGDA", $filtri['DATAPAG_da'], PDO::PARAM_STR);
            $this->addSqlParam($sqlParams, "DATAPAGA", $filtri['DATAPAG_a'], PDO::PARAM_STR);
            $sql .= " $where (DATAPAG BETWEEN :DATAPAGDA AND :DATAPAGA)";
        }
        if ($filtri['DATAPAG_da'] && $filtri['DATAPAG_a'] == null) {
            $this->addSqlParam($sqlParams, "DATAPAGDA", $filtri['DATAPAG_da'], PDO::PARAM_STR);
            $sql .= " $where DATAPAG >=:DATAPAGDA";
        }
        if ($filtri['DATAPAG_da'] === null && $filtri['DATAPAG_a']) {
            $this->addSqlParam($sqlParams, "DATAPAGDA", $filtri['DATAPAG_a'], PDO::PARAM_STR);
            $sql .= " $where DATAPAG <=:DATAPAGA";
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_RISCO
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidRiscoStatoScadenza($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidRiscoStatoScadenza($filtri, true, $sqlParams), true, $sqlParams);
    }

    // BGE_MAILS

    /**
     * Restituisce comando sql per lettura tabella BGE_MAILS
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBgeMails($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGE_MAILS.* FROM BGE_MAILS";
        $where = 'WHERE';

        if (array_key_exists('PROGRECORD', $filtri) && $filtri['PROGRECORD'] != null) {
            $this->addSqlParam($sqlParams, "PROGRECORD", $filtri['PROGRECORD'], PDO::PARAM_INT);
            $sql .= " $where PROGRECORD = :PROGRECORD";
            $where = 'AND';
        }
        if (array_key_exists('SMTP_SERV', $filtri) && $filtri['SMTP_SERV'] != null) {
            $this->addSqlParam($sqlParams, "IDLIVELL", strtoupper(trim($filtri['SMTP_SERV'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("SMTP_SERV") . " = :SMTP_SERV";
            $where = 'AND';
        }
        if (array_key_exists('POP3_SERV', $filtri) && $filtri['POP3_SERV'] != null) {
            $this->addSqlParam($sqlParams, "POP3_SERV", strtoupper(trim($filtri['POP3_SERV'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("POP3_SERV") . " = :POP3_SERV";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGRECORD';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_MAILS
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeMails($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeMails($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGE_MAILS
     * @param string $cod Chiave     
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBgeMailsChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGRECORD'] = $cod;
        return self::getSqlLeggiBgeMails($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_MAILS per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBgeMailsChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeMailsChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BGE_AGID_SCADENZE 

    /**
     * Restituisce comando sql per verificare se ci sono state variazioni anagrafiche 
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadenzeVarAna($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT  DISTINCT bge_agid_scadenze.progkeytab, bwe_penden.DATASCADE,BTA_SOGG.TIPOPERS,BTA_SOGG.RAGSOC,BTA_SOGG.CODFISCALE, BTA_SOGG.PARTIVA 
                FROM BWE_PENDEN INNER JOIN BTA_SOGG ON BTA_SOGG.PROGSOGG = BWE_PENDEN.PROGSOGG
                LEFT JOIN BGE_AGID_SCADENZE ON BGE_AGID_SCADENZE.CODTIPSCAD = BWE_PENDEN.CODTIPSCAD AND BGE_AGID_SCADENZE.SUBTIPSCAD = BWE_PENDEN.SUBTIPSCAD AND 
                BGE_AGID_SCADENZE.PROGCITYSC = BWE_PENDEN.PROGCITYSC AND BGE_AGID_SCADENZE.ANNORIF = BWE_PENDEN.ANNORIF AND BGE_AGID_SCADENZE.NUMRATA = BWE_PENDEN.NUMRATA
                LEFT JOIN BGE_AGID_SCA_EFIL ON BGE_AGID_SCADENZE.PROGKEYTAB = BGE_AGID_SCA_EFIL.PROGKEYTAB
                WHERE FLAG_PUBBL>=3 and BGE_AGID_SCADENZE.stato =3 AND 
                ((BGE_AGID_SCADENZE.CODFISCALE<> BTA_SOGG.PARTIVA AND BGE_AGID_SCADENZE.CODFISCALE<> BTA_SOGG.CODFISCALE) OR 
                (BGE_AGID_SCADENZE.TIPOPERS<>BTA_SOGG.TIPOPERS OR BGE_AGID_SCA_EFIL.ANADEBITORE<>BTA_SOGG.RAGSOC) OR
                (BGE_AGID_SCADENZE.DATASCADE<>BWE_PENDEN.DATASCADE))
                AND (BWE_PENDEN.ANNORIF*1000000+BWE_PENDEN.PROGCITYSC IN (SELECT BWE_PENDEN.ANNORIF*1000000+BWE_PENDEN.PROGCITYSC FROM BWE_PENDEN WHERE BWE_PENDEN.TIPOPENDEN =2 
                GROUP BY BWE_PENDEN.ANNORIF*1000000+BWE_PENDEN.PROGCITYSC   HAVING COUNT(*) >1) 
                OR BWE_PENDEN.TIPOPENDEN=1)";
        $where = 'AND';

        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", $filtri['CODTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_SCADENZE.CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri)) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", $filtri['SUBTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_SCADENZE.SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('ANNORIF', $filtri)) {
            $this->addSqlParam($sqlParams, "ANNORIF", $filtri['ANNORIF'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_SCADENZE.ANNORIF=:ANNORIF";
            $where = 'AND';
        }
        if (array_key_exists('PROGCITYSC', $filtri) && $filtri['PROGCITYSC'] != null) {
            $this->addSqlParam($sqlParams, "PROGCITYSC", $filtri['PROGCITYSC'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_SCADENZE.PROGCITYSC=:PROGCITYSC";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_PENDEN
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzeVarAna($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzeVarAna($filtri, true, $sqlParams), $flMultipla, $sqlParams);
    }

    // BGE_AGID_LOG

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_LOG
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidLog($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGE_AGID_LOG.* FROM BGE_AGID_LOG";
        $where = 'WHERE';

        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB = :PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('LIVELLO', $filtri) && $filtri['LIVELLO'] != 0) {
            $this->addSqlParam($sqlParams, "LIVELLO", $filtri['LIVELLO'], PDO::PARAM_INT);
            $sql .= " $where LIVELLO = :LIVELLO";
            $where = 'AND';
        }
        if (array_key_exists('ESITO', $filtri) && $filtri['ESITO'] != 0) {
            $this->addSqlParam($sqlParams, "ESITO", $filtri['ESITO'], PDO::PARAM_INT);
            $sql .= " $where ESITO = :ESITO";
            $where = 'AND';
        }
        if (array_key_exists('OPERAZIONE', $filtri) && $filtri['OPERAZIONE'] != null) {
            $this->addSqlParam($sqlParams, "OPERAZIONE", $filtri['OPERAZIONE'], PDO::PARAM_INT);
            $sql .= " $where OPERAZIONE = :OPERAZIONE";
            $where = 'AND';
        }
        if ($filtri['DATAOPER_da'] != null && $filtri['DATAOPER_a'] != null) {
            $this->addSqlParam($sqlParams, "DATAOPERDA", $filtri['DATAOPER_da'], PDO::PARAM_STR);
            $this->addSqlParam($sqlParams, "DATAOPERA", $filtri['DATAOPER_a'], PDO::PARAM_STR);
            $sql .= " $where (DATAOPER BETWEEN :DATAOPERDA AND :DATAOPERA)";
        }
        if ($filtri['DATAOPER_da'] && $filtri['DATAOPER_a'] == null) {
            $this->addSqlParam($sqlParams, "DATAOPERDA", $filtri['DATAOPER_da'], PDO::PARAM_STR);
            $sql .= " $where DATAOPER >=:DATAOPERDA";
        }
        if ($filtri['DATAOPER_da'] === null && $filtri['DATAOPER_a']) {
            $this->addSqlParam($sqlParams, "DATAOPERA", $filtri['DATAOPER_a'], PDO::PARAM_STR);
            $sql .= " $where DATAOPER <=:DATAOPERA";
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_LOG
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidLog($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidLog($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGE_AGID_LOG
     * @param string $cod Chiave     
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidLogChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGRECORD'] = $cod;
        return self::getSqlLeggiBgeAgidLog($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_AGID_LOG per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBgeAgidLogChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidLogChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_CONF_NSS
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidConfNss($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_AGID_CONF_NSS.* FROM BGE_AGID_CONF_NSS";
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_CONF_NSS
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidConfNss($filtri = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidConfNss($filtri, false, $sqlParams), false, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_CONF_MPAY
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidConfMpay($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_AGID_CONF_MPAY.* "
                . "FROM BGE_AGID_CONF_MPAY ";
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_CONF_MPAY
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidConfMpay($filtri = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidConfMpay($filtri, false, $sqlParams), false, $sqlParams);
    }

//    //risultato del caricamento
//    public function leggiBgeAgidConfMpayBinary($result = array()) {
//        $sql = "SELECT " . $this->getCitywareDB()->adapterBlob("SFTPFILEKEY") . " FROM BGE_AGID_CONF_MPAY WHERE PROGKEYTAB = :PROGKEYTAB";
//        $sqlParams = array();
//        $this->addSqlParam($sqlParams, "PROGKEYTAB", $result['PROGKEYTAB'], PDO::PARAM_INT);
//
//        $sqlFields = array();
//        $this->addBinaryFieldsDescribe($sqlFields, "SFTPFILEKEY", 1);
//        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
//        $result['SFTPFILEKEY'] = $resultBin['SFTPFILEKEY'];
//
//        return $result;
//    }

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_CONF_AAR
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidConfAltoAdigeRisco($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_AGID_CONF_AAR.* "
                . "FROM BGE_AGID_CONF_AAR ";
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_CONF_MPAY
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidConfAltoAdigeRisco($filtri = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidConfAltoAdigeRisco($filtri, false, $sqlParams), false, $sqlParams);
    }

    // BGE_AGID_SCADET

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_SCADET
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadet($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGE_AGID_SCADET.* FROM BGE_AGID_SCADET";
        $where = 'WHERE';

        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB = :PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('IDSCADENZA', $filtri) && $filtri['IDSCADENZA'] != 0) {
            $this->addSqlParam($sqlParams, "IDSCADENZA", $filtri['IDSCADENZA'], PDO::PARAM_INT);
            $sql .= " $where IDSCADENZA = :IDSCADENZA";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADET
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadet($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadet($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // BGE_AGID_SCADENZE

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadenzePerEmissione($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT TOTALE.ANNOEMI,TOTALE.NUMEMI,TOTALE.IDBOL_SERE,COUNT(TOTALE.STATO) TOTALE,COUNT(INVIATO.STATO) INVIATO, 
                COUNT(SOSPESO.STATO) SOSPESO, COUNT(PUBBLICATO.STATO) PUBBLICATO , COUNT(CANCELLATO.STATO) CANCELLATO, COUNT(RENDICONTATO.STATO) RENDICONTATO,
                COUNT(RICONCILIATO.STATO) RICONCILIATO, COUNT(CANCFALL.STATO) CANCFALL,COUNT(RESTO.STATO) RESTO
                FROM BGE_AGID_SCADENZE TOTALE   
                LEFT JOIN  BGE_AGID_SCADENZE INVIATO ON TOTALE.PROGKEYTAB=INVIATO.PROGKEYTAB AND INVIATO.STATO=2
                LEFT JOIN  BGE_AGID_SCADENZE SOSPESO ON TOTALE.PROGKEYTAB=SOSPESO.PROGKEYTAB AND SOSPESO.STATO=3
                LEFT JOIN  BGE_AGID_SCADENZE PUBBLICATO ON TOTALE.PROGKEYTAB=PUBBLICATO.PROGKEYTAB 
                AND (PUBBLICATO.STATO=5 or PUBBLICATO.STATO=11 or PUBBLICATO.STATO=12 or PUBBLICATO.STATO=7 or PUBBLICATO.STATO=13)
                LEFT JOIN  BGE_AGID_SCADENZE RICONCILIATO ON TOTALE.PROGKEYTAB=RICONCILIATO.PROGKEYTAB AND RICONCILIATO.STATO=12
                LEFT JOIN  BGE_AGID_SCADENZE CANCELLATO ON TOTALE.PROGKEYTAB=CANCELLATO.PROGKEYTAB AND CANCELLATO.STATO=7
                LEFT JOIN  BGE_AGID_SCADENZE RENDICONTATO ON TOTALE.PROGKEYTAB=RENDICONTATO.PROGKEYTAB AND RENDICONTATO.STATO=11
                LEFT JOIN  BGE_AGID_SCADENZE CANCFALL ON TOTALE.PROGKEYTAB=CANCFALL.PROGKEYTAB AND CANCFALL.STATO=13
                LEFT JOIN  BGE_AGID_SCADENZE RESTO ON TOTALE.PROGKEYTAB=PUBBLICATO.PROGKEYTAB AND 
                (PUBBLICATO.STATO<>5 AND PUBBLICATO.STATO<>3 AND PUBBLICATO.STATO<>2 AND PUBBLICATO.STATO<>7 AND PUBBLICATO.STATO<>11 AND PUBBLICATO.STATO<>12 AND PUBBLICATO.STATO<>13)";
        $where = 'WHERE';

        if (array_key_exists('ANNOEMI', $filtri)) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " $where TOTALE.ANNOEMI = :ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('NUMEMI', $filtri)) {
            $this->addSqlParam($sqlParams, "NUMEMI", $filtri['NUMEMI'], PDO::PARAM_INT);
            $sql .= " $where TOTALE.NUMEMI = :NUMEMI";
            $where = 'AND';
        }
        if (array_key_exists('IDBOL_SERE', $filtri)) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", $filtri['IDBOL_SERE'], PDO::PARAM_INT);
            $sql .= " $where TOTALE.IDBOL_SERE = :IDBOL_SERE";
            $where = 'AND';
        }

        $sql .= " GROUP BY TOTALE.ANNOEMI,TOTALE.NUMEMI,TOTALE.IDBOL_SERE";
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzePerEmissione($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzePerEmissione($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per recuperare le scadenze da Cancellare
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadenzePerCancellazione($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT  BGE_AGID_SCADENZE.*, BTA_SERVRENDPPA.TIPORIFCRED FROM BGE_AGID_SCADENZE 
                LEFT JOIN BWE_PENDEN ON BGE_AGID_SCADENZE.CODTIPSCAD = BWE_PENDEN.CODTIPSCAD
                AND BGE_AGID_SCADENZE.SUBTIPSCAD = BWE_PENDEN.SUBTIPSCAD 
                AND BGE_AGID_SCADENZE.PROGCITYSC = BWE_PENDEN.PROGCITYSC 
                AND BGE_AGID_SCADENZE.ANNORIF = BWE_PENDEN.ANNORIF 
                AND BGE_AGID_SCADENZE.NUMRATA = BWE_PENDEN.NUMRATA
                INNER JOIN BTA_SERVREND ON BGE_AGID_SCADENZE.CODTIPSCAD=BTA_SERVREND.CODTIPSCAD 
                AND BGE_AGID_SCADENZE.SUBTIPSCAD=BTA_SERVREND.SUBTIPSCAD AND BGE_AGID_SCADENZE.ANNOEMI=BTA_SERVREND.ANNOEMI 
                AND BGE_AGID_SCADENZE.NUMEMI=BTA_SERVREND.NUMEMI AND BGE_AGID_SCADENZE.IDBOL_SERE=BTA_SERVREND.IDBOL_SERE
                INNER JOIN BTA_SERVRENDPPA ON BTA_SERVREND.PROGKEYTAB = BTA_SERVRENDPPA.IDSERVREND
                WHERE BGE_AGID_SCADENZE.STATO <=5 AND BGE_AGID_SCADENZE.STATO <> 2 AND BGE_AGID_SCADENZE.TIP_INS <> 2 ";

        if (array_key_exists('CODSERVIZIO', $filtri) && $filtri['CODSERVIZIO'] != null) {
            $this->addSqlParam($sqlParams, "CODSERVIZIO", $filtri['CODSERVIZIO'], PDO::PARAM_INT);
            $sql .= " AND CODSERVIZIO=:CODSERVIZIO";
        }
        if (array_key_exists('TIP_INS', $filtri) && $filtri['TIP_INS'] !== null) {
            $this->addSqlParam($sqlParams, "TIP_INS", $filtri['TIP_INS'], PDO::PARAM_INT);
            $sql .= " AND TIP_INS=:TIP_INS";
        }
        if (array_key_exists('TIP_INS_IN', $filtri) && $filtri['TIP_INS_IN'] !== null) {
            $sql .= " AND TIP_INS IN (" . implode(", ", $filtri['TIP_INS_IN']) . ")";
        }
        if (array_key_exists('FLAG_PUBBL_IN', $filtri)) {
            $sql .= " AND FLAG_PUBBL IN (" . implode(", ", $filtri['FLAG_PUBBL_IN']) . ")";
        }
        $sql .= ' AND ((BWE_PENDEN.CODTIPSCAD IS NULL AND BWE_PENDEN.SUBTIPSCAD IS NULL '
                . 'AND BWE_PENDEN.PROGCITYSC IS NULL AND BWE_PENDEN.ANNORIF IS NULL '
                . 'AND BWE_PENDEN.NUMRATA IS NULL) or BWE_PENDEN.STATO >4)';
        $sql .= ' ORDER BY BGE_AGID_SCADENZE.PROGSOGG';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzePerCancellazione($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzePerCancellazione($filtri, false, $sqlParams), true, $sqlParams);
    }

    // BGE_AGID_SCA_NSS

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_SCA_NSS
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScaNss($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGE_AGID_SCA_NSS.* FROM BGE_AGID_SCA_NSS";
        $where = 'WHERE';

        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB = :PROGKEYTAB";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCA_NSS
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScaNss($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScaNss($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // BGE_AGID_SCADETIVA

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_SCADETIVA
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadetiva($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGE_AGID_SCADETIVA.* FROM BGE_AGID_SCADETIVA";
        $where = 'WHERE';

        if (array_key_exists('IDSCADENZA', $filtri) && $filtri['IDSCADENZA'] != null) {
            $this->addSqlParam($sqlParams, "IDSCADENZA", $filtri['IDSCADENZA'], PDO::PARAM_INT);
            $sql .= " $where IDSCADENZA = :IDSCADENZA";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCA_NSS
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadetiva($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadetiva($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // BGE_AGID_SCADET 

    /**
     * Restituisce comando sql per verificare se ci sono state variazioni di accertamenti 
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadetVariazioni($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT  DISTINCT bwe_penddet.CODICE, bwe_penden.DATASCADE,BTA_SOGG.TIPOPERS,BTA_SOGG.RAGSOC,BTA_SOGG.CODFISCALE, BTA_SOGG.PARTIVA 
                FROM BWE_PENDEN INNER JOIN BTA_SOGG ON BTA_SOGG.PROGSOGG = BWE_PENDEN.PROGSOGG
                LEFT JOIN BWE_PENDEN ON BGE_AGID_SCADENZE.CODTIPSCAD = BWE_PENDEN.CODTIPSCAD
                AND BGE_AGID_SCADENZE.SUBTIPSCAD = BWE_PENDEN.SUBTIPSCAD 
                AND BGE_AGID_SCADENZE.PROGCITYSC = BWE_PENDEN.PROGCITYSC 
                AND BGE_AGID_SCADENZE.ANNORIF = BWE_PENDEN.ANNORIF";
        $where = 'AND';

        if (array_key_exists('IDSCADENZA', $filtri) && $filtri['IDSCADENZA'] != null) {
            $this->addSqlParam($sqlParams, "IDSCADENZA", $filtri['IDSCADENZA'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_SCADET.IDSCADENZA=:IDSCADENZA";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_PENDEN
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadetVariazioni($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadetVariazioni($filtri, true, $sqlParams), $flMultipla, $sqlParams);
    }

    // BGE_AGID_INVII 

    /**
     * Restituisce comando sql per reperire la DATAINVIO partendo dai dati di emissione 
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidInviiDatainvio($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT DATAINVIO FROM BGE_AGID_INVII WHERE PROGKEYTAB IN (SELECT MAX(PROGINV) ULTIMO_PROGINV FROM BGE_AGID_SCADENZE 
                INNER JOIN BGE_AGID_INVII ON BGE_AGID_INVII.PROGKEYTAB=BGE_AGID_SCADENZE.PROGINV";
        $where = 'WHERE';

        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] != 0) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " $where ANNOEMI=:ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] != 0) {
            $this->addSqlParam($sqlParams, "NUMEMI", $filtri['NUMEMI'], PDO::PARAM_INT);
            $sql .= " $where NUMEMI=:NUMEMI";
            $where = 'AND';
        }
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] != 0) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", $filtri['IDBOL_SERE'], PDO::PARAM_INT);
            $sql .= " $where IDBOL_SERE=:IDBOL_SERE";
            $where = 'AND';
        }
        $sql .= " GROUP BY ANNOEMI,NUMEMI,IDBOL_SERE)";

        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_PENDEN
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidInviiDatainvio($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidInviiDatainvio($filtri, true, $sqlParams), $flMultipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per reperire la DATAINVIO partendo dai dati di emissione 
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeFepa00Aoo($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT
                    BOR_AOO.*
                FROM BOR_AOO
                LEFT JOIN BGE_FEPA00 ON BGE_FEPA00.IDAOO = BOR_AOO.IDAOO
                ORDER BY BGE_FEPA00.IDFEPA00 ASC";
        $where = 'WHERE';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_PENDEN
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeFepa00Aoo($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeFepa00Aoo($filtri, true, $sqlParams), $flMultipla, $sqlParams);
    }

    // BGE_FEPA00

    /**
     * Restituisce row BGE_FEPA00 (Impostazioni fattura elettronica)
     * @return array Row BGE_FEPA00
     */
    public function leggiBgeFepa00($infAggiuntiveSdi = false) {
        $sqlParams = array();
        $sql = "SELECT BGE_FEPA00.*";
        if ($infAggiuntiveSdi) {
            $sql .= " ,FFE_CODSDI.CODICE_SDI";
        }
        $sql .= " FROM BGE_FEPA00 " . ($infAggiuntiveSdi ?
                " LEFT JOIN FFE_CODSDI ON BGE_FEPA00.PROGK_SDI1 = FFE_CODSDI.PROGKEYSDI " : "") . " WHERE IDFEPA00=1";

        return ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);
    }

    // BGE_FEPA01

    /**
     * Restituisce row BGE_FEPA01 (Binari fattura elettronica)
     * @return array Lista BGE_FEPA01
     */
    public function leggiBgeFepa01() {
        $sqlParams = array();
        $sql = "SELECT
                    BGE_FEPA01.*
                FROM BGE_FEPA01";
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BGE", "leggiBgeFepa01Binary");
        return ItaDB::DBQuery($this->getCitywareDB(), $sql, true, $sqlParams, $infoBinaryCallback);
    }

    //risultato del caricamento
    public function leggiBgeFepa01Binary($result = array()) {
        $sql = "SELECT " . $this->getCitywareDB()->adapterBlob("CONTENUTO") . " FROM BGE_FEPA01 WHERE IDFEPA01 = :IDFEPA01";
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "IDFEPA01", $result['IDFEPA01'], PDO::PARAM_INT);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "CONTENUTO", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['CONTENUTO'] = $resultBin['CONTENUTO'];

        return $result;
    }

    /**
     * Restituisce i parametri utente Cityware per un determinato contesto applicativo
     * @param string $codute Codice Utente
     * @param string $nomeSchema Nome schema
     * @param bool $unpack Se true, scompatta il CAMPOUNICO
     * @return array Dati utente (se $unpack = true, restituisce i valori scompattati, altrimenti i dati di BGE_OPZ_UT)
     */
    public function leggiBgeOpzUtSchema($codute, $nomeSchema, $unpack = true) {
        $sql = 'SELECT BGE_OPZ_UT.* FROM BGE_OPZ_UT' .
                ' WHERE CODUTE=:CODUTE' .
                ' AND NOMESCHEMA=:NOMESCHEMA';

        $sqlParams = array();
        $this->addSqlParam($sqlParams, "CODUTE", $codute, PDO::PARAM_STR);
        $this->addSqlParam($sqlParams, "NOMESCHEMA", $nomeSchema, PDO::PARAM_STR);

        $result = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);
        if ($unpack) {
            return $this->unpackBgeOpzUt($nomeSchema, $result['CAMPOUNICO']);
        }
        return $result;
    }

    private function unpackBgeOpzUt($nomeSchema, $campoUnico) {
        switch ($nomeSchema) {
            case 'S_PARAM_ANAG':
                $result = $this->unpackBgeOpzUt_Anag($campoUnico);
                break;
            default:
                $result = false;
        }
        return $result;
    }

    private function unpackBgeOpzUt_Anag($campoUnico) {
        $data = array(
            'flag_carica_startup' => '',
            'iv_anag_espandi' => '',
            'MOTIVO_C' => '',
            'MOTIVO_I' => '',
            'PROGSOGG' => '',
            'FAMIGLIA_T' => '',
            'FAMIGLIA' => '',
            'SESSO' => '',
            'NOME_RIC' => '',
            'GIORNO' => '',
            'MESE' => '',
            'ANNO' => '',
            'CODVIA' => '',
            'NUMCIV' => '',
            'SUBNCIV' => '',
            'iv_APR' => '',
            'iv_AIRE' => '',
            'iv_TEMP' => '',
            'iv_NRESI' => '',
            'SCALA' => '',
            'PIANO' => '',
            'INTERNO' => '',
            'CODFISCALE' => '',
            'CODNAZI' => '',
            'F_VISUAL_GENER' => '',
            'F_SCANNER' => '',
            'SCANNER_MODE' => ''
        );
        $this->packedToArray($campoUnico, $data);
        return $data;
    }

    private function packedToArray($campoUnico, &$data) {
        $keys = array_keys($data);
        $i = 0;
        while (strlen($campoUnico) > 0 && $i < count($data)) {
            $pos = strpos($campoUnico, chr(28));
            $data[$keys[$i++]] = substr($campoUnico, 0, $pos);
            $campoUnico = substr($campoUnico, $pos + 1, strlen($campoUnico) - $pos);
        }
    }

    // Lettura su BGE_AGID_STOSCADE per ripubblicazione

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_SCADENZE ritornando lo IUV
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidStoScadeRipubblicazione($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();
        $sql = "SELECT MAX(CODRIFERIMENTO) AS MAXCODRIFERIMENTO FROM BGE_AGID_STOSCADE";
        $where = 'WHERE';
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", $filtri['CODTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri)) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", $filtri['SUBTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('ANNORIF', $filtri)) {
            $this->addSqlParam($sqlParams, "ANNORIF", $filtri['ANNORIF'], PDO::PARAM_INT);
            $sql .= " $where ANNORIF=:ANNORIF";
            $where = 'AND';
        }
        if (array_key_exists('PROGCITYSC', $filtri) && $filtri['PROGCITYSC'] != null) {
            $this->addSqlParam($sqlParams, "PROGCITYSC", $filtri['PROGCITYSC'], PDO::PARAM_INT);
            $sql .= " $where PROGCITYSC=:PROGCITYSC";
            $where = 'AND';
        }
        if (array_key_exists('NUMRATA', $filtri)) {
            $this->addSqlParam($sqlParams, "NUMRATA", $filtri['NUMRATA'], PDO::PARAM_INT);
            $sql .= " $where NUMRATA=:NUMRATA";
            $where = 'AND';
        }
        if (array_key_exists('STATO', $filtri)) {
            $this->addSqlParam($sqlParams, "STATO", $filtri['STATO'], PDO::PARAM_INT);
            $sql .= " $where STATO=:STATO";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_STOSCADE per capire l'intermediario che sto trattando
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidStoScadeRipubblicazione($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidStoScadeRipubblicazione($filtri, false, $sqlParams), false, $sqlParams);
    }

    // BGE_AGID_SCADENZE

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadenzeNoBwePenden($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGE_AGID_SCADENZE.* FROM BGE_AGID_SCADENZE"
                . " LEFT JOIN BWE_PENDEN ON BGE_AGID_SCADENZE.CODTIPSCAD = BWE_PENDEN.CODTIPSCAD
                AND BGE_AGID_SCADENZE.SUBTIPSCAD = BWE_PENDEN.SUBTIPSCAD 
                AND BGE_AGID_SCADENZE.PROGCITYSC = BWE_PENDEN.PROGCITYSC 
                AND BGE_AGID_SCADENZE.ANNORIF = BWE_PENDEN.ANNORIF 
                AND BGE_AGID_SCADENZE.NUMRATA = BWE_PENDEN.NUMRATA ";
        $where = 'WHERE';
        if (array_key_exists('ANNOEMI', $filtri) && $filtri['ANNOEMI'] != 0) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_SCADENZE.ANNOEMI=:ANNOEMI";
            $where = 'AND';
        }
        if (array_key_exists('NUMEMI', $filtri) && $filtri['NUMEMI'] != 0) {
            $this->addSqlParam($sqlParams, "NUMEMI", $filtri['NUMEMI'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_SCADENZE.NUMEMI=:NUMEMI";
            $where = 'AND';
        }
        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] != 0) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", $filtri['IDBOL_SERE'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_SCADENZE.IDBOL_SERE=:IDBOL_SERE";
            $where = 'AND';
        }
        $sql .= " AND BGE_AGID_SCADENZE.STATO <> 7 AND BWE_PENDEN.PROGKEYTAB IS NULL";
        $sql .= $excludeOrderBy ? '' : ' ORDER BY BGE_AGID_SCADENZE.PROGKEYTAB';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCA_NSS
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzeNoBwePenden($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScadenzeNoBwePenden($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // BGE_EVENTI_BAT

    /**
     * Restituisce comando sql per lettura tabella BGE_EVENTI_BAT
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeEventiBatVediUltimo($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "select bge_eventi_bat.* from bge_eventi_bat where pk = (select max(pk) from bge_eventi_bat "
                . $this->addFilterBgeEventiBat($filtri, $sqlParams) . " )";
        if (!$excludeOrderBy) {
            $sql .= ' ORDER BY IDELAB DESC, PK DESC ';
        }

        return $sql;
    }

    public function getSqlLeggiBgeEventiBat($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_EVENTI_BAT.* FROM BGE_EVENTI_BAT";
        $sql .= $this->addFilterBgeEventiBat($filtri, $sqlParams);

        if (!$excludeOrderBy) {
            $sql .= ' ORDER BY IDELAB DESC, PK DESC ';
        }

        return $sql;
    }

    private function addFilterBgeEventiBat($filtri, &$sqlParams, $aggiungiWhere = true, $alias = '') {
        $random = rand(99, 9999);
        if ($aggiungiWhere) {
            $where = 'WHERE';
        } else {
            $where = 'AND';
        }
        if ($alias) {
            $alias .= '.';
        }
        if (array_key_exists('PK', $filtri) && $filtri['PK'] != null) {
            $this->addSqlParam($sqlParams, "PK" . $random, $filtri['PK'], PDO::PARAM_INT);
            $sql .= " $where " . $alias . "PK=:PK" . $random;
            $where = 'AND';
        }
        if (array_key_exists('IDELAB', $filtri) && $filtri['IDELAB'] != null) {
            $this->addSqlParam($sqlParams, "IDELAB" . $random, $filtri['IDELAB'], PDO::PARAM_INT);
            $sql .= " $where " . $alias . "IDELAB=:IDELAB" . $random;
            $where = 'AND';
        }
        if (array_key_exists('KEY_ALFA', $filtri) && $filtri['KEY_ALFA'] != null) {
            $this->addSqlParam($sqlParams, "KEY_ALFA" . $random, $filtri['KEY_ALFA'], PDO::PARAM_INT);
            $sql .= " $where " . $alias . "KEY_ALFA=:KEY_ALFA" . $random;
            $where = 'AND';
        }
        if (array_key_exists('TIPOELABORAZIONE', $filtri) && $filtri['TIPOELABORAZIONE'] != null) {
            $this->addSqlParam($sqlParams, "TIPOELABORAZIONE" . $random, $filtri['TIPOELABORAZIONE'], PDO::PARAM_INT);
            $sql .= " $where " . $alias . "TIPOELABORAZIONE=:TIPOELABORAZIONE" . $random;
            $where = 'AND';
        }
        if (array_key_exists('TIPOEVENTO', $filtri) && $filtri['TIPOEVENTO'] != null) {
            $this->addSqlParam($sqlParams, "TIPOEVENTO" . $random, $filtri['TIPOEVENTO'], PDO::PARAM_INT);
            $sql .= " $where " . $alias . "TIPOEVENTO=:TIPOEVENTO" . $random;
            $where = 'AND';
        }
        if (array_key_exists('TIPOEVENTO_DIVERSO', $filtri) && $filtri['TIPOEVENTO_DIVERSO'] != null) {
            $this->addSqlParam($sqlParams, "TIPOEVENTO_DIVERSO" . $random, $filtri['TIPOEVENTO_DIVERSO'], PDO::PARAM_INT);
            $sql .= " $where " . $alias . "TIPOEVENTO!=:TIPOEVENTO_DIVERSO" . $random;
            $where = 'AND';
        }
        if (array_key_exists('TIPOEVENTO_NOTIN', $filtri) && $filtri['TIPOEVENTO_NOTIN'] != null) {
            $sql .= " $where " . $alias . "TIPOEVENTO NOT IN ( '" . implode("',  '", $filtri['TIPOEVENTO_NOTIN']) . "')";
            $where = 'AND';
        }
        if (array_key_exists('NAMEFORM', $filtri) && $filtri['NAMEFORM'] != null) {
            $this->addSqlParam($sqlParams, "NAMEFORM" . $random, "%" . strtoupper(trim($filtri['NAMEFORM'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper($alias . "NAMEFORM") . " LIKE :NAMEFORM" . $random;
            $where = 'AND';
        }
        if (array_key_exists('DITTA', $filtri) && $filtri['DITTA'] != null) {
            $this->addSqlParam($sqlParams, "DITTA" . $random, "%" . strtoupper(trim($filtri['DITTA'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper($alias . "DITTA") . " LIKE :DITTA" . $random;
            $where = 'AND';
        }
        if (array_key_exists('CODUTERICH', $filtri) && $filtri['CODUTERICH'] != null) {
            $this->addSqlParam($sqlParams, "CODUTERICH" . $random, "%" . strtoupper(trim($filtri['CODUTERICH'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper($alias . "CODUTERICH") . " LIKE :CODUTERICH" . $random;
            $where = 'AND';
        }
        if (array_key_exists('DATAOPER', $filtri) && $filtri['DATAOPER'] != null) {
            $this->addSqlParam($sqlParams, "DATAOPER" . $random, strtoupper(trim($filtri['DATAOPER'])), PDO::PARAM_STR);
            $sql .= " $where " . $alias . "DATAOPER=:DATAOPER" . $random;
            $where = 'AND';
        }

        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_EVENTI_BAT
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeEventiBat($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeEventiBat($filtri, false, $sqlParams), $multipla, $sqlParams, $infoBinaryCallback);
    }

    public function leggiBgeEventiBatVediUltimo($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeEventiBatVediUltimo($filtri, false, $sqlParams), $multipla, $sqlParams, $infoBinaryCallback);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGE_EVENTI_BAT
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeEventiBatChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PK'] = $cod;
        return self::getSqlLeggiBgeEventiBat($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_EVENTI_BAT per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBgeEventiBatChiave($cod) {
        if ($cod < 1) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeEventiBatChiave($cod, $sqlParams), false, $sqlParams);
    }

    public function deleteBgeEventiBat($filtri) {
        return $this->getCitywareDB()->query($this->getSqlDeleteBgeEventiBat($filtri, $sqlParams), false, $sqlParams);
    }

    public function getSqlDeleteBgeEventiBat($filtri, &$sqlParams = array()) {
        $sql = "DELETE FROM BGE_EVENTI_BAT";
        $sql .= $this->addFilterBgeEventiBat($filtri, $sqlParams);

        return $sql;
    }

    public function countBgeEventiBat($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlcountBgeEventiBat($filtri, $sqlParams), false, $sqlParams);
    }

    public function getSqlcountBgeEventiBat($filtri, &$sqlParams = array()) {
        $sql = "SELECT COUNT(*) AS CONTA FROM BGE_EVENTI_BAT";
        $sql .= $this->addFilterBgeEventiBat($filtri, $sqlParams);

        return $sql;
    }

    public function countBgeEventiBatAttivi($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlcountBgeEventiBatAttivi($filtri, $sqlParams), false, $sqlParams);
    }

    public function getSqlcountBgeEventiBatAttivi($filtri, &$sqlParams = array()) {
        $sql = "SELECT COUNT(*) AS CONTA FROM BGE_EVENTI_BAT WHERE TIPOEVENTO = 1 " .
                $sql .= $this->addFilterBgeEventiBat($filtri, $sqlParams, false) . "
            AND " . $this->getCitywareDB()->strConcat('KEY_ALFA', 'IDELAB') . " NOT IN (
            SELECT DISTINCT " . $this->getCitywareDB()->strConcat('INIZIO.KEY_ALFA', 'INIZIO.IDELAB') . "  AS CHIAVE 
            FROM BGE_EVENTI_BAT INIZIO 
            LEFT JOIN BGE_EVENTI_BAT FINE ON INIZIO.KEY_ALFA=FINE.KEY_ALFA AND INIZIO.IDELAB=FINE.IDELAB
            WHERE INIZIO.TIPOEVENTO = 1 AND FINE.TIPOEVENTO IN (3,4) ";
        $sql .= $this->addFilterBgeEventiBat($filtri, $sqlParams, false, "FINE");
        $sql .= ")";
        return $sql;
    }

    public function leggiBgeEventiBatRiepiloghiAttivi($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlcountBgeEventiBatRiepiloghiAttivi($filtri, $sqlParams), true, $sqlParams);
    }

    public function getSqlcountBgeEventiBatRiepiloghiAttivi($filtri, &$sqlParams = array()) {
        $sql = "SELECT DISTINCT BGE_EVENTI_BAT.* FROM BGE_EVENTI_BAT WHERE TIPOEVENTO = 5 " .
                $sql .= $this->addFilterBgeEventiBat($filtri, $sqlParams, false) . "
            AND " . $this->getCitywareDB()->strConcat('KEY_ALFA', 'IDELAB') . " NOT IN (
            SELECT DISTINCT " . $this->getCitywareDB()->strConcat('INIZIO.KEY_ALFA', 'INIZIO.IDELAB') . "  AS CHIAVE 
            FROM BGE_EVENTI_BAT INIZIO 
            LEFT JOIN BGE_EVENTI_BAT FINE ON INIZIO.KEY_ALFA=FINE.KEY_ALFA AND INIZIO.IDELAB=FINE.IDELAB
            WHERE INIZIO.TIPOEVENTO = 1 AND FINE.TIPOEVENTO IN (3,4)  ";
        $sql .= $this->addFilterBgeEventiBat($filtri, $sqlParams, false, "FINE");
        $sql .= ")";
        return $sql;
    }

    // BGE_FLDAL

    /**
     * Restituisce comando sql per lettura tabella BGE_FLDAL
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeFldal($filtri, $excludeOrderBy = false, &$sqlParams, $orderBy = '') {
        $sql = "SELECT BGE_FLDAL.* FROM BGE_FLDAL";
        $where = 'WHERE';
        if (array_key_exists('CODAREAMA', $filtri) && $filtri['CODAREAMA'] != null) {
            $this->addSqlParam($sqlParams, "CODAREAMA", $filtri['CODAREAMA'], PDO::PARAM_STR);
            $sql .= " $where CODAREAMA=:CODAREAMA";
            $where = 'AND';
        }

        $sql .= strlen($orderBy) > 0 ? ' ORDER BY ' . $orderBy : ' ORDER BY CODAREAMA';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_FLDAL
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeFldal($filtri, $orderBy = '') {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeFldal($filtri, false, $sqlParams, $orderBy), true, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGE_FLDAL
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeFldalChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['CODAREAMA'] = $cod;
        return self::getSqlLeggiBgeFldal($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_FLDAL per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBgeFldalChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeFldalChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BGE_IMAGES

    /**
     * Legge logo Ente
     * @return binary Logo
     */
    public function leggiLogoEnte() {
        $sqlParams = array();
        $sql = "SELECT BGE_IMAGES.COD_IMAGE, " .
                $this->getCitywareDB()->adapterBlob("IMMAGINE") .
                " FROM BGE_IMAGES WHERE COD_IMAGE=1";
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BGE", "leggiImmagineBgeImages"); //DONE
        $result = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, $infoBinaryCallback);
        if (!$result) {
            return false;
        }
        return cwbLibOmnis::fromOmnisPicture($result['IMMAGINE']);
    }

    public function leggiImmagineBgeImages($result = array()) {
        $sql = 'SELECT ' . $this->getCitywareDB()->adapterBlob("IMMAGINE") . ' FROM BGE_IMAGES WHERE BGE_IMAGES.COD_IMAGE=:COD_IMAGE';
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "COD_IMAGE", $result['COD_IMAGE'], PDO::PARAM_INT);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "IMMAGINE", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['IMMAGINE'] = $resultBin['IMMAGINE'];

        return $result;
    }

    // BGE_MAILPARAMS

    /**
     * Restituisce comando sql per lettura tabella BGE_MAILPARAMS
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeMailparams($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();

        $sql = "SELECT BGE_MAILPARAMS.* FROM BGE_MAILPARAMS";
        $where = 'WHERE';
//        if (array_key_exists('IDMAILPARAMS', $filtri) && $filtri['IDMAILPARAMS'] != null) {
//            $this->addSqlParam($sqlParams, "IDMAILPARAMS", $filtri['IDMAILPARAMS'], PDO::PARAM_INT);
//            $sql .= " $where IDMAILPARAMS=:IDMAILPARAMS";
//            $where = 'AND';
//        }
        if (isset($filtri['IDMAILPARAMS']) && $filtri['IDMAILPARAMS'] != null) {
            $this->addSqlParam($sqlParams, "IDMAILPARAMS", $filtri['IDMAILPARAMS'], PDO::PARAM_INT);
            $sql .= " $where IDMAILPARAMS=:IDMAILPARAMS";
        }
        if (isset($filtri['CONTESTO_APP']) && $filtri['CONTESTO_APP'] != null) {
            $this->addSqlParam($sqlParams, "CONTESTO_APP", "%" . strtoupper(trim($filtri['CONTESTO_APP'])) . "%", PDO::PARAM_STR);
            $sql .= " $where CONTESTO_APP LIKE :CONTESTO_APP";
            $where = 'AND';
        }
        if (isset($filtri['METADATI']) && $filtri['METADATI'] != null) {
            $this->addSqlParam($sqlParams, "METADATI", "%" . strtoupper(trim($filtri['METADATI'])) . "%", PDO::PARAM_STR);
            $sql .= " $where METADATI LIKE :METADATI";
            $where = 'AND';
        }
        if (isset($filtri['KCODUTE']) && $filtri['KCODUTE'] != null) {
            $this->addSqlParam($sqlParams, "KCODUTE", "%" . strtoupper(trim($filtri['KCODUTE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where KCODUTE LIKE :KCODUTE";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDMAILPARAMS';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_MAILPARAMS
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeMailparamsFiltroRicerca($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeMailparamsFiltroRicerca($filtri, false, $sqlParams), true, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGE_MAILPARAMS
     * @param string $cod Chiave    
     * @param array $sqlParams Parametri query 
     * @return string Comando sql
     */
    public function getSqlLeggiBgeMailparamsChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['IDMAILPARAMS'] = $cod;
        return self::getSqlLeggiBgeMailparams($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_MAILPARAMS
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeMailparams($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeMailparams($filtri, false, $sqlParams), true, $sqlParams);
    }

// RECUPERA IUV DA BGE_AGID_SCADENZE di un determinato documento

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_SCADENZE ritornando lo IUV
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiListaIuvDocAgidScadenze($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();
        $sql = "SELECT BGE_AGID_SCADENZE.IUV, BGE_AGID_SCADENZE.ANNORIF, BGE_AGID_SCADENZE.PROGCITYSC, "
                . "BGE_AGID_SCADENZE.NUMRATA, BGE_AGID_SCADENZE.STATO FROM BGE_AGID_SCADENZE";
        $where = 'WHERE';
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", $filtri['CODTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri)) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", $filtri['SUBTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('ANNORIF', $filtri)) {
            $this->addSqlParam($sqlParams, "ANNORIF", $filtri['ANNORIF'], PDO::PARAM_INT);
            $sql .= " $where ANNORIF=:ANNORIF";
            $where = 'AND';
        }
        if (array_key_exists('PROGCITYSC', $filtri) && $filtri['PROGCITYSC'] != null) {
            $this->addSqlParam($sqlParams, "PROGCITYSC", $filtri['PROGCITYSC'], PDO::PARAM_INT);
            $sql .= " $where PROGCITYSC=:PROGCITYSC";
            $where = 'AND';
        }
        if (array_key_exists('NUMRATA', $filtri)) {
            $this->addSqlParam($sqlParams, "NUMRATA", $filtri['NUMRATA'], PDO::PARAM_INT);
            $sql .= " $where NUMRATA=:NUMRATA";
            $where = 'AND';
        }
//        if (array_key_exists('STATOne7', $filtri)) {
//            $sql .= " $where STATO <> 7";
//            $where = 'AND';
//        }
        return $sql;
    }

    /**
     * Restituisce gli IUV della tabella BGE_AGID_SCADENZE di un determinato documento finanziario
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiListaIuvDocAgidScadenze($filtri) {
        $res = ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiListaIuvDocAgidScadenze($filtri, false, $sqlParams), true, $sqlParams);
        if ($res) {
            return $res;
        }
        return false;
    }

// Leggi BGE_AGID_STOSCADE di un determinato documento

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_STOSCADE 
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidStoscade($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();
        $sql = "SELECT BGE_AGID_STOSCADE.* FROM BGE_AGID_STOSCADE";
        $where = 'WHERE';
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", $filtri['CODTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri)) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", $filtri['SUBTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('ANNORIF', $filtri)) {
            $this->addSqlParam($sqlParams, "ANNORIF", $filtri['ANNORIF'], PDO::PARAM_INT);
            $sql .= " $where ANNORIF=:ANNORIF";
            $where = 'AND';
        }
        if (array_key_exists('PROGCITYSC', $filtri) && $filtri['PROGCITYSC'] != null) {
            $this->addSqlParam($sqlParams, "PROGCITYSC", $filtri['PROGCITYSC'], PDO::PARAM_INT);
            $sql .= " $where PROGCITYSC=:PROGCITYSC";
            $where = 'AND';
        }
        if (array_key_exists('NUMRATA', $filtri)) {
            $this->addSqlParam($sqlParams, "NUMRATA", $filtri['NUMRATA'], PDO::PARAM_INT);
            $sql .= " $where NUMRATA=:NUMRATA";
            $where = 'AND';
        }
        if (array_key_exists('STATO', $filtri)) {
            $this->addSqlParam($sqlParams, "STATO", $filtri['STATO'], PDO::PARAM_INT);
            $sql .= " $where STATO=:STATO";
            $where = 'AND';
        }
        if (array_key_exists('IUV', $filtri) && $filtri['IUV'] != null && $filtri['IUV'] != '0') {
            $this->addSqlParam($sqlParams, "IUV", $filtri['IUV'], PDO::PARAM_STR);
            $sql .= " $where IUV=:IUV";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce gli IUV della tabella BGE_AGID_STOSCADE di un determinato documento finanziario
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidStoscade($filtri, $multipla = true) {
        $res = ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidStoscade($filtri, false, $sqlParams), $multipla, $sqlParams);
        if ($res) {
            return $res;
        }
        return false;
    }

    // Leggi BGE_AGID_STOSCADE di un determinato documento

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_SOGGETTI 
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidSoggetti($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();
        $sql = "SELECT BGE_AGID_SOGGETTI.*," . $this->getCitywareDB()->strConcat('BGE_AGID_SOGGETTI.COGNOME', "' '", 'BGE_AGID_SOGGETTI.NOME')
                . " RAGSOC  FROM BGE_AGID_SOGGETTI";
        $where = 'WHERE';
        if (array_key_exists('PROGSOGG', $filtri) && $filtri['PROGSOGG'] != null) {
            $this->addSqlParam($sqlParams, "PROGSOGG", $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where PROGSOGG=:PROGSOGG";
            $where = 'AND';
        }
        if (array_key_exists('CODFISCALE', $filtri) && $filtri['CODFISCALE'] != null) {
            $this->addSqlParam($sqlParams, "CODFISCALE", strtoupper(trim($filtri['CODFISCALE'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CODFISCALE") . " = :CODFISCALE";
            $where = 'AND';
        }
        if (array_key_exists('PARTIVA', $filtri) && $filtri['PARTIVA'] != null) {
            $this->addSqlParam($sqlParams, "PARTIVA", strtoupper(trim($filtri['PARTIVA'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("PARTIVA") . " = :PARTIVA";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGSOGG desc';
        return $sql;
    }

    /**
     * Restituisce gli IUV della tabella BGE_AGID_SOGGETTI di un determinato documento finanziario
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidSoggetti($filtri, $multipla = true) {
        $res = ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidSoggetti($filtri, false, $sqlParams), $multipla, $sqlParams);
        if ($res) {
            return $res;
        }
        return false;
    }

    // Leggi bge_agid_scadenze  e  bta_sogg

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_SOGGETTI 
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiSoggettoPagoPa($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();
        $sql = "select 
            coalesce(s.ragsoc," . $this->getCitywareDB()->strConcat('sx.cognome', "' '", 'sx.nome') . ") ragsoc,
            coalesce(s.giorno,sx.giorno) giorno,
            coalesce(s.mese,sx.mese) mese ,
            coalesce(s.anno,sx.anno) anno,
            coalesce(s.codfiscale,sx.codfiscale) codfiscale ,
            coalesce(s.partiva,sx.partiva) partiva,
            coalesce(l.deslocal,sx.luogonasc) luogonasc
            from bge_agid_scadenze 
            LEFT join bta_sogg s on s.progsogg=bge_agid_Scadenze.progsogg
            LEFT join bge_agid_soggetti sx on sx.progsogg=bge_agid_Scadenze.progsoggex
            left join bta_local l on s.CODNAZPRO=l.CODNAZPRO and s.CODLOCAL=l.CODLOCAL ";
        $where = 'WHERE';
        if (array_key_exists('PROGSOGG', $filtri) && $filtri['PROGSOGG'] != null) {
            $this->addSqlParam($sqlParams, "PROGSOGG", $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where s.PROGSOGG=:PROGSOGG";
            $where = 'AND';
        }
        if (array_key_exists('PROGSOGGEX', $filtri) && $filtri['PROGSOGGEX'] != null) {
            $this->addSqlParam($sqlParams, "PROGSOGGEX", $filtri['PROGSOGGEX'], PDO::PARAM_INT);
            $sql .= " $where PROGSOGGEX=:PROGSOGGEX";
            $where = 'AND';
        }
        if (array_key_exists('CODFISCALE', $filtri) && $filtri['CODFISCALE'] != null) {
            $this->addSqlParam($sqlParams, "CODFISCALE", strtoupper(trim($filtri['CODFISCALE'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CODFISCALE") . " = :CODFISCALE";
            $where = 'AND';
        }
        if (array_key_exists('PARTIVA', $filtri) && $filtri['PARTIVA'] != null) {
            $this->addSqlParam($sqlParams, "PARTIVA", strtoupper(trim($filtri['PARTIVA'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("PARTIVA") . " = :PARTIVA";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGSOGG desc';
        return $sql;
    }

    /**
     * Restituisce gli IUV della tabella BGE_AGID_SOGGETTI di un determinato documento finanziario
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiSoggettoPagoPa($filtri, $multipla = true) {
        $res = ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiSoggettoPagoPa($filtri, false, $sqlParams), $multipla, $sqlParams);
        if ($res) {
            return $res;
        }
        return false;
    }

    // BGE_FUNZGIS

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_SCAINFO
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScainfo($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BGE_AGID_SCAINFO.* FROM BGE_AGID_SCAINFO";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('IDSCADENZA', $filtri) && $filtri['IDSCADENZA'] != null) {
            $this->addSqlParam($sqlParams, "IDSCADENZA", $filtri['IDSCADENZA'], PDO::PARAM_INT);
            $sql .= " $where IDSCADENZA=:IDSCADENZA";
            $where = 'AND';
        }
        if (array_key_exists('VALORE', $filtri) && $filtri['VALORE'] != null) {
            $this->addSqlParam($sqlParams, "VALORE", strtoupper(trim($filtri['VALORE'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("VALORE") . " = :VALORE";
            $where = 'AND';
        }
        if (array_key_exists('CHIAVE', $filtri) && $filtri['CHIAVE'] != null) {
            $this->addSqlParam($sqlParams, "CHIAVE", strtoupper(trim($filtri['CHIAVE'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CHIAVE") . " = :CHIAVE";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCAINFO
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScainfo($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScainfo($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGE_AGID_SCAINFO
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScainfoChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGKEYTAB'] = $cod;
        return self::getSqlLeggiBgeAgidScainfo($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGE_AGID_SCAINFO per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBgeAgidScainfoChiave($cod) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeAgidScainfoChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BGE_AGID_SCADENZE

    /**
     * Restituisce comando sql per lettura tabella BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeAgidScadenzeDatiAggiuntivi($filtri, $excludeOrderBy = false, &$sqlParams, $orderBy = '') {
        $chiaviArray = array_keys($filtri);
        $arrayAlias = array();
        $sql = "SELECT * FROM BGE_AGID_SCADENZE WHERE PROGKEYTAB IN ("
                . " SELECT MAIN.IDSCADENZA FROM BGE_AGID_SCAINFO MAIN";
        $i = 0;
        $where = ' WHERE ';
        $whereSql = '';
        for ($index = 1; $index < count($chiaviArray); $index++) {
            $alias = "C" . $index;
            $arrayAlias[] = $alias;
            $sql .= " LEFT JOIN BGE_AGID_SCAINFO $alias ON MAIN.IDSCADENZA=$alias.IDSCADENZA ";
            $sql .= "  AND " . $this->getCitywareDB()->strUpper($alias . ".CHIAVE") . "=:CHIAVE" . $i;
            $sql .= "  AND " . $this->getCitywareDB()->strUpper($alias . ".VALORE") . "=:VALORE" . $i;
            $this->addSqlParam($sqlParams, "CHIAVE" . $i, strtoupper(trim($chiaviArray[$index])), PDO::PARAM_STR);
            $this->addSqlParam($sqlParams, "VALORE" . $i, strtoupper(trim($filtri[$chiaviArray[$index]])), PDO::PARAM_STR);
            $this->addSqlParam($sqlParams, "CHIAVEW" . $i, strtoupper(trim($chiaviArray[$index])), PDO::PARAM_STR);
            $this->addSqlParam($sqlParams, "VALOREW" . $i, strtoupper(trim($filtri[$chiaviArray[$index]])), PDO::PARAM_STR);
            $whereSql .= $where . $this->getCitywareDB()->strUpper($alias . ".CHIAVE") . "=:CHIAVEW" . $i . " AND " . $this->getCitywareDB()->strUpper($alias . ".VALORE") . "=:VALOREW" . $i;
            $where = ' AND ';
            $i++;
        }
        $this->addSqlParam($sqlParams, "CHIAVEW" . $i, strtoupper(trim($chiaviArray[0])), PDO::PARAM_STR);
        $this->addSqlParam($sqlParams, "VALOREW" . $i, strtoupper(trim($filtri[$chiaviArray[0]])), PDO::PARAM_STR);
        $whereSql .= $where . $this->getCitywareDB()->strUpper("MAIN.CHIAVE") . "=:CHIAVEW" . $i . " AND " . $this->getCitywareDB()->strUpper("MAIN.VALORE") . "=:VALOREW" . $i;
        $sql .= $whereSql;
        $sql .= " )";
        return $sql;
    }

    /**
     * Restituisce dati tabella bge_agid_scadenze
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeAgidScadenzeDatiAggiuntivi($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlleggiBgeAgidScadenzeDatiAggiuntivi($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    //BGE_WFPARAMS
    public function getSqlleggiBgeWfparams($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT
                BGE_WFPARAMS.* 
                FROM BGE_WFPARAMS";
        $where = " WHERE";

        if (!empty($filtri['IDWFPARAMS'])) {
            $this->addSqlParam($sqlParams, "IDWFPARAMS", trim($filtri['IDWFPARAMS']), PDO::PARAM_INT);
            $sql .= $where . " BGE_WFPARAMS.IDWFPARAMS = :IDWFPARAMS";
            $where = " AND";
        }
        if (isSet($filtri['CONTESTO_APP']) && trim($filtri['CONTESTO_APP']) != '') {
            $this->addSqlParam($sqlParams, "CONTESTO_APP", '%' . strtoupper(trim($filtri['CONTESTO_APP'])) . '%', PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('BGE_WFPARAMS.CONTESTO_APP') . " LIKE :CONTESTO_APP";
            $where = " AND";
        }
        if (isSet($filtri['AREA']) && trim($filtri['AREA']) != '') {
            $this->addSqlParam($sqlParams, "AREA", '%' . strtoupper(trim($filtri['AREA'])) . '%', PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('BGE_WFPARAMS.AREA') . " LIKE :AREA";
            $where = " AND";
        }
        if (isSet($filtri['GESCODPROC']) && trim($filtri['GESCODPROC']) != '') {
            $this->addSqlParam($sqlParams, "GESCODPROC", '%' . strtoupper(trim($filtri['GESCODPROC'])) . '%', PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('BGE_WFPARAMS.GESCODPROC') . " LIKE :GESCODPROC";
            $where = " AND";
        }
        if (isSet($filtri['GESPRO']) && trim($filtri['GESPRO']) != '') {
            $this->addSqlParam($sqlParams, 'GESPRO', intval($filtri['GESPRO']), PDO::PARAM_INT);
            $sql .= " $where BGE_WFPARAMS.GESPRO=:GESPRO";
            $where = " AND";
        }
        if (isSet($filtri['GESWFPRO']) && trim($filtri['GESWFPRO']) != '') {
            $this->addSqlParam($sqlParams, 'GESWFPRO', intval($filtri['GESWFPRO']), PDO::PARAM_INT);
            $sql .= " $where BGE_WFPARAMS.GESWFPRO=:GESWFPRO";
            $where = " AND";
        }
        if (isSet($filtri['ITEEVT']) && trim($filtri['ITEEVT']) != '') {
            $this->addSqlParam($sqlParams, 'ITEEVT', intval($filtri['ITEEVT']), PDO::PARAM_INT);
            $sql .= " $where BGE_WFPARAMS.ITEEVT=:ITEEVT";
            $where = " AND";
        }

        if (!$excludeOrderBy) {
            $sql .= " ORDER BY IDWFPARAMS";
        }

        return $sql;
    }

    public function leggiBgeWfparams($filtri = null, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlleggiBgeWfparams($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBgeWfparamsChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['IDWFPARAMS'] = $cod;
        return self::getSqlLeggiBgeWfparams($filtri, true, $sqlParams);
    }

}
