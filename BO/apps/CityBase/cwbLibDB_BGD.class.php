<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_CITYWARE.class.php';

/**
 *
 * Utility DB Cityware (Modulo BGD)
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
class cwbLibDB_BGD extends cwbLibDB_CITYWARE {
    // BGD_PAROTT

    /**
     * Restituisce comando sql per lettura tabella BGD_PAROTT
     * @return string Comando sql
     */
    public function getSqlLeggiBgdParott() {
        $sql = "SELECT BGD_PAROTT.* FROM BGD_PAROTT WHERE PROG_KEY=1";

        return $sql;
    }

    /**
     * Restituisce dati tabella BGD_PAROTT
     * @return object Resultset
     */
    public function leggiBgdParott($filtri, $multpla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdParott($filtri, $sqlParams), $multpla, $sqlParams);
    }

    // BGD_TIPDOC

    /**
     * Restituisce comando sql per lettura tabella BGD_TIPDOC
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgdTipdoc($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGD_TIPDOC.* FROM BGD_TIPDOC";
        if ($filtri) {
            $where = 'WHERE';
        }

        if (array_key_exists('IDTIPDOC', $filtri) && $filtri['IDTIPDOC'] != null) {
            $this->addSqlParam($sqlParams, "IDTIPDOC", $filtri['IDTIPDOC'], PDO::PARAM_INT);
            $sql .= " $where IDTIPDOC=:IDTIPDOC";
            $where = 'AND';
        }
        if (array_key_exists('DESCRIZIONE', $filtri) && $filtri['DESCRIZIONE'] != null) {
            $this->addSqlParam($sqlParams, "DESCRIZIONE", "%" . strtoupper(trim($filtri['DESCRIZIONE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESCRIZIONE") . " LIKE :DESCRIZIONE";
            $where = 'AND';
        }
        if (array_key_exists('ALIAS', $filtri) && $filtri['ALIAS'] != null) {
            $this->addSqlParam($sqlParams, "ALIAS", "%" . strtoupper(trim($filtri['ALIAS'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("ALIAS") . " LIKE :ALIAS";
            $where = 'AND';
        }
        if (array_key_exists('AREA_ORIG', $filtri) && $filtri['AREA_ORIG'] != null) {
            $this->addSqlParam($sqlParams, "AREA_ORIG", "%" . strtoupper(trim($filtri['AREA_ORIG'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("AREA_ORIG") . " LIKE :AREA_ORIG";
            $where = 'AND';
        }
        if (array_key_exists('MODULO_ORIG', $filtri) && $filtri['MODULO_ORIG'] != null) {
            $this->addSqlParam($sqlParams, "MODULO_ORIG", "%" . strtoupper(trim($filtri['MODULO_ORIG'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("MODULO_ORIG") . " LIKE :MODULO_ORIG";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] != 0) {
            $sql .= "";
        } elseif ($filtri['IDTIPDOC'] == null) {
            $sql .= " $where " . " FLAG_DIS=0";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_ASPECT', $filtri) && $filtri['FLAG_ASPECT'] != 0) {
            $sql .= "";
        } elseif ($filtri['IDTIPDOC'] == null && $filtri['IDTIPDOC_IN'] == null) {
            $sql .= " $where FLAG_ASPECT=0";
            $where = 'AND';
        }

        // Esclude aspetti
        if (array_key_exists('escludeAspetti', $filtri) && $filtri['escludeAspetti'] == true) {
            $sql .= " $where FLAG_ASPECT=0";
            $where = 'AND';
        }

        // Esclude non esportabili
        if (array_key_exists('escludeNonEsportabili', $filtri) && $filtri['escludeNonEsportabili'] == true) {
            $sql .= " $where FLAG_EXPORT=1";
            $where = 'AND';
        }
        
        if (array_key_exists('IDTIPDOC_IN', $filtri) && $filtri['IDTIPDOC_IN'] != null && !empty($filtri['IDTIPDOC_IN'])) {
            $sql .= " $where IDTIPDOC IN (";
            for ($i = 0; $i < count($filtri['IDTIPDOC_IN']); $i++) {
                $this->addSqlParam($sqlParams, "IDTIPDOC" . $i, $filtri['IDTIPDOC_IN'][$i], PDO::PARAM_INT);

                if ($i > 0) {
                    $sql .= ',';
                }
                $sql .= ':IDTIPDOC' . $i;
            }
            $sql .= ")";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY DESCRIZIONE';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGD_TIPDOC
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgdTipdoc($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdTipdoc($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGD_TIPDOC
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBgdTipdocChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['IDTIPDOC'] = $cod;
        return self::getSqlLeggiBgdTipdoc($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGD_TIPDOC per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBgdTipdocChiave($cod) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdTipdocChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BGD_METDOC

    /**
     * Restituisce comando sql per lettura tabella BGD_METDOC
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgdMetdoc($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGD_METDOC.*,BGD_TIPDOC.ALIAS FROM BGD_METDOC "
                . "INNER JOIN BGD_TIPDOC ON BGD_METDOC.IDTIPDOC = BGD_TIPDOC.IDTIPDOC ";

        if ($filtri) {
            $where = 'WHERE ';
        }

        if (array_key_exists('IDMETDOC', $filtri) && $filtri['IDMETDOC'] != null) {
            $this->addSqlParam($sqlParams, "IDMETDOC", $filtri['IDMETDOC'], PDO::PARAM_INT);
            $sql .= " $where IDMETDOC=:IDMETDOC";
            $where = 'AND';
        }
        if (array_key_exists('IDTIPDOC', $filtri) && $filtri['IDTIPDOC'] != null) {
            $this->addSqlParam($sqlParams, "IDTIPDOC", $filtri['IDTIPDOC'], PDO::PARAM_INT);
            $sql .= " $where BGD_METDOC.IDTIPDOC=:IDTIPDOC";
            $where = 'AND';
        }
        if (array_key_exists('ALIAS', $filtri) && $filtri['ALIAS'] != null) {
            $this->addSqlParam($sqlParams, "ALIAS", $filtri['ALIAS'], PDO::PARAM_INT);
            $sql .= " $where BGD_TIPDOC.ALIAS=:ALIAS";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDMETDOC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGD_METDOC
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgdMetdoc($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdMetdoc($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGD_METDOC
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBgdMetdocChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['IDMETDOC'] = $cod;
        return self::getSqlLeggiBgdMetdoc($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGD_METDOC per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBgdMetdocChiave($cod) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdMetdocChiave($cod, $sqlParams), false, $sqlParams);
    }
    
    
    /**
     * Restituisce comando sql per lettura tabella BGD_METDOC
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgdMetdocAspetti($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = 'SELECT DISTINCT * FROM BGD_METDOC';
        $where = ' WHERE ';

        if(!empty($filtri['IDTIPDOC'])){
            $this->addSqlParam($sqlParams, "IDTIPDOC", $filtri['IDTIPDOC'], PDO::PARAM_INT);
            $this->addSqlParam($sqlParams, "IDTIPDOC_ASPETTI", $filtri['IDTIPDOC'], PDO::PARAM_INT);
            
            $sql .= $where.'(BGD_METDOC.IDTIPDOC = :IDTIPDOC OR BGD_METDOC.IDTIPDOC IN (SELECT BGD_ASPTDC.IDASPECT FROM BGD_ASPTDC WHERE BGD_ASPTDC.IDTIPDOC = :IDTIPDOC_ASPETTI))';
            $where = ' AND ';
        }
        if(!empty($filtri['CHIAVE'])){
            $this->addSqlParam($sqlParams, "CHIAVE", strtoupper(trim($filtri['CHIAVE'])), PDO::PARAM_STR);
            
            $sql .= $where.'BGD_METDOC.CHIAVE = :CHIAVE';
            $where = ' AND ';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDMETDOC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGD_METDOC
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgdMetdocAspetti($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdMetdocAspetti($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // BGD_ASPTDC

    /**
     * Restituisce stringa SQL per selezione aspetti di un tipo documento
     * @param int $idTipdoc Chiave della tabella BGD_TIPDOC
     * @return string Stringa SQL
     */
    public function getSqlLeggiBgdAsptdc($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGD_ASPTDC.*, BGD_TIPDOC.ALIAS AS ALIAS_ASP, BGD_TIPDOC.DESCRIZIONE AS DESCRIZIONE_ASP"
                . " FROM BGD_ASPTDC"
                . " INNER JOIN BGD_TIPDOC ON BGD_ASPTDC.IDASPECT=BGD_TIPDOC.IDTIPDOC"
                . " WHERE ";
        if (array_key_exists('IDTIPDOC', $filtri) && $filtri['IDTIPDOC'] != null) {
            $this->addSqlParam($sqlParams, "IDTIPDOC", strtoupper(trim($filtri['IDTIPDOC'])), PDO::PARAM_INT);
            $sql .= " $where BGD_ASPTDC.IDTIPDOC=:IDTIPDOC";
            $where = 'AND';
        }
        if (array_key_exists('IDASPECT', $filtri) && $filtri['IDASPECT'] != null) {
            $this->addSqlParam($sqlParams, "IDASPECT", strtoupper(trim($filtri['IDASPECT'])), PDO::PARAM_INT);
            $sql .= " $where BGD_ASPTDC.IDASPECT=:IDASPECT";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Legge aspetti di un tipo documento
     * @param int $idTipdoc Chiave della tabella BGD_TIPDOC
     * @return array Elenco aspetti del documento in esame
     */
    public function leggiBgdAsptdc($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdAsptdc($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Legge aspetti di un tipo documento
     * @param int $idTipdoc Chiave della tabella BGD_TIPDOC
     * @return array Elenco aspetti del documento in esame
     */
    public function leggiBgdAsptdcChiave($idaspect) {
        if (!$idaspect) {
            return null;
        }
        $filtri = array('IDASPECT' => $idaspect);
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdAsptdc($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // BGD_ADMCNF

    /**
     * Restituisce comando sql per lettura tabella BGD_ADMCNF
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBgdAdmcnf($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGD_ADMCNF.* FROM BGD_ADMCNF";
        $where = 'WHERE';
        if (array_key_exists('IDADMCNF', $filtri) && $filtri['IDADMCNF'] != null) {
            $this->addSqlParam($sqlParams, "IDADMCNF", $filtri['IDADMCNF'], PDO::PARAM_INT);
            $sql .= " $where IDADMCNF=:IDADMCNF";
            $where = 'AND';
        }
        if (array_key_exists('CODAREAMA', $filtri) && $filtri['CODAREAMA'] != null) {
            $this->addSqlParam($sqlParams, "CODAREAMA", $filtri['CODAREAMA'], PDO::PARAM_STR);
            $sql .= " $where CODAREAMA=:CODAREAMA";
            $where = 'AND';
        }
        if (array_key_exists('CODMODULO', $filtri) && $filtri['CODMODULO'] != null) {
            $this->addSqlParam($sqlParams, "CODMODULO", $filtri['CODMODULO'], PDO::PARAM_STR);
            $sql .= " $where CODMODULO=:CODMODULO";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDADMCNF';

        return $sql;
    }

    /**
     * Restituisce dati tabella BGD_ADMCNF
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgdAdmcnf($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdAdmcnf($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGD_ADMCNF
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBgdAdmcnfChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['IDADMCNF'] = $cod;
        return self::getSqlLeggiBgdAdmcnf($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGD_ADMCNF per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBgdAdmcnfChiave($cod) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdAdmcnfChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Legge elenco aree
     * @param array $filtri Filtri di selezione
     * @return array Elenco aree
     */
    public function getAreeBgdAdmcnf($filtri, $multipla = true) {
        $sql = "SELECT a.CODAREAMA, b.DESAREA FROM BGD_ADMCNF a INNER JOIN BOR_MASTER b ON a.CODAREAMA=b.CODAREAMA";
        $where = 'WHERE';
        if (array_key_exists('F_GESTDOC', $filtri) && $filtri['F_GESTDOC'] != null) {
            $this->addSqlParam($sqlParams, "F_GESTDOC", $filtri['F_GESTDOC'], PDO::PARAM_INT);
            $sql .= " $where F_GESTDOC=:F_GESTDOC";
            $where = 'AND';
        }
        $sql .= ' ORDER BY a.CODAREAMA';
        return ItaDB::DBQuery($this->getCitywareDB(), $sql, true, $sqlParams);
    }

    /**
     * Legge elenco moduli
     * @param array $filtri Filtri di selezione
     * @return array Elenco moduli
     */
    public function getModuliBgdAdmcnf($filtri, $multipla = true) {
        $sql = "SELECT a.CODMODULO, b.DESMODULO FROM BGD_ADMCNF a
                INNER JOIN BOR_MODULI b ON a.CODMODULO=b.CODMODULO";
        $where = 'WHERE';
        if (array_key_exists('F_GESTDOC', $filtri) && $filtri['F_GESTDOC'] != null) {
            $this->addSqlParam($sqlParams, "F_GESTDOC", $filtri['F_GESTDOC'], PDO::PARAM_INT);
            $sql .= " $where F_GESTDOC=:F_GESTDOC";
            $where = 'AND';
        }
        if (array_key_exists('CODAREAMA', $filtri) && $filtri['CODAREAMA'] != null) {
            $this->addSqlParam($sqlParams, "CODAREAMA", $filtri['CODAREAMA'], PDO::PARAM_STR);
            $sql .= " $where a.CODAREAMA=:CODAREAMA";
            $where = 'AND';
        }
        $sql .= ' ORDER BY a.CODMODULO';
        return ItaDB::DBQuery($this->getCitywareDB(), $sql, $multipla, $sqlParams);
    }

    // BGD_ADASSM

    /**
     * Restituisce comando sql per lettura tabella BGD_ADASSM
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param mixed $orderBy Se valorizzato, contiene il campo o i campi (array) di ordinamento
     * @return string Comando sql
     */
    public function getSqlLeggiBgdAdassm($filtri, $excludeOrderBy = false, $orderBy = '', &$sqlParams = array()) {
        $sql = "SELECT a.*, b.CODAREAMA, b.CODMODULO FROM BGD_ADASSM a INNER JOIN BGD_ADMCNF b ON a.IDADMCNF=b.IDADMCNF";
        $where = 'WHERE';
        if (array_key_exists('IDADASSM', $filtri) && $filtri['IDADASSM'] != null) {
            if (is_string($filtri['IDADASSM'])) {
                $this->addSqlParam($sqlParams, "IDADASSM", $filtri['IDADASSM'], PDO::PARAM_STR);
                $sql .= " $where IDADASSM=:IDADASSM";
            } else {
                $this->addSqlParam($sqlParams, "IDADASSM", $filtri['IDADASSM']['VALORE'], PDO::PARAM_STR);
                $sql .= " $where IDADASSM" . $filtri['IDADASSM']['OPERATORE'] . ":IDADASSM";
            }
            $where = 'AND';
        }
        if (array_key_exists('TIPO_DOC', $filtri) && $filtri['TIPO_DOC'] != null) {
            $this->addSqlParam($sqlParams, "TIPO_DOC", "%" . strtoupper(trim($filtri['TIPO_DOC'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("TIPO_DOC") . " LIKE :TIPO_DOC";
            $where = 'AND';
        }
        if (array_key_exists('CODAREAMA', $filtri) && $filtri['CODAREAMA'] != null) {
            $this->addSqlParam($sqlParams, "CODAREAMA", $filtri['CODAREAMA'], PDO::PARAM_STR);
            $sql .= " $where CODAREAMA=:CODAREAMA";
            $where = 'AND';
        }
        if (array_key_exists('CODMODULO', $filtri) && $filtri['CODMODULO'] != null) {
            $this->addSqlParam($sqlParams, "CODMODULO", $filtri['CODMODULO'], PDO::PARAM_STR);
            $sql .= " $where CODMODULO=:CODMODULO";
            $where = 'AND';
        }
        if (array_key_exists('F_TIPOGEST', $filtri) && $filtri['F_TIPOGEST'] != null && $filtri['F_TIPOGEST'] != 0) {
            $this->addSqlParam($sqlParams, "F_TIPOGEST", $filtri['F_TIPOGEST'], PDO::PARAM_STR);
            $sql .= " $where F_TIPOGEST=:F_TIPOGEST";
            $where = 'AND';
        }
        if (!$excludeOrderBy) {
            if ($orderBy == '') {
                $sql .= ' ORDER BY IDADASSM';
            } else {
                if (is_string($orderBy)) {
                    $sql .= " ORDER BY $orderBy";
                } else if (is_array($orderBy)) {
                    $orderByStr = implode(',', $orderBy);
                    $sql .= " ORDER BY $orderByStr";
                }
            }
        }

        return $sql;
    }

    /**
     * Restituisce dati tabella BGD_ADASSM
     * @param array $filtri Filtri di ricerca     
     * @param mixed $orderBy Se valorizzato, contiene il campo o i campi (array) di ordinamento
     * @return object Resultset
     */
    public function leggiBgdAdassm($filtri, $orderBy = '', $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdAdassm($filtri, false, $orderBy, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGD_ADASSM
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBgdAdassmChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['IDADASSM'] = $cod;
        return self::getSqlLeggiBgdAdassm($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGD_ADASSM per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBgdAdassmChiave($cod, &$sqlParams) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdAdassmChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BGD_DOC_AL

    /**
     * Restituisce comando sql per lettura tabella BGD_DOC_AL
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param mixed $orderBy Se valorizzato, contiene il campo o i campi (array) di ordinamento
     * @return string Comando sql
     */
    public function getSqlLeggiBgdDocAl($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGD_DOC_AL.* FROM BGD_DOC_AL";
        $where = 'WHERE';
        if (array_key_exists('PROGCOMU', $filtri) && $filtri['PROGCOMU'] != null) {
            $this->addSqlParam($sqlParams, "PROGCOMU", $filtri['PROGCOMU'], PDO::PARAM_INT);
            $sql .= " $where PROGCOMU=:PROGCOMU";
            $where = 'AND';
        }
        if (array_key_exists('IDDOCAL', $filtri) && $filtri['IDDOCAL'] != null) {
            $this->addSqlParam($sqlParams, "IDDOCAL", $filtri['IDDOCAL'], PDO::PARAM_INT);
            $sql .= " $where IDDOCAL=:IDDOCAL";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] !== null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGCOMU';

        return $sql;
    }

    /**
     * Restituisce dati tabella BGD_DOC_AL
     * @param array $filtri Filtri di ricerca     
     * @param mixed $orderBy Se valorizzato, contiene il campo o i campi (array) di ordinamento
     * @return object Resultset
     */
    public function leggiBgdDocAl($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdDocAl($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGD_DOC_AL
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBgdDocAlChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['IDDOCAL'] = $cod;
        return self::getSqlLeggiBgdDocAl($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGD_DOC_AL per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBgdDocAlChiave($cod, &$sqlParams) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdDocAlChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BGD_DOC_UD

    /**
     * Restituisce comando sql per lettura tabella BGD_DOC_UD
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param mixed $orderBy Se valorizzato, contiene il campo o i campi (array) di ordinamento
     * @return string Comando sql
     */
    public function getSqlLeggiBgdDocUd($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGD_DOC_UD.* FROM BGD_DOC_UD";
        $where = 'WHERE';
        if (array_key_exists('PROGCOMU', $filtri) && $filtri['PROGCOMU'] != null) {
            $this->addSqlParam($sqlParams, "PROGCOMU", $filtri['PROGCOMU'], PDO::PARAM_INT);
            $sql .= " $where PROGCOMU=:PROGCOMU";
            $where = 'AND';
        }
        
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGCOMU';

        return $sql;
    }

    /**
     * Restituisce dati tabella BGD_DOC_UD
     * @param array $filtri Filtri di ricerca     
     * @param mixed $orderBy Se valorizzato, contiene il campo o i campi (array) di ordinamento
     * @return object Resultset
     */
    public function leggiBgdDocUd($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdDocUd($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BGD_DOC_UD
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBgdDocUdChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['IDDOCAL'] = $cod;
        return self::getSqlLeggiBgdDocUd($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BGD_DOC_UD per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBgdDocUdChiave($cod, &$sqlParams) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdDocUdChiave($cod, $sqlParams), false, $sqlParams);
    }

    // join tra BTA_TIPCOM E BGD_TIPDOC

    /**
     * Restituisce join tra BTA_TIPCOM E BGD_TIPDOC
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiTipoDocumentoDaTipCom($idtipcom, &$sqlParams = array()) {
        $sql = "SELECT DISTINCT BGD_TIPDOC.* FROM BTA_TIPCOM "
                . " LEFT JOIN BGD_TIPDOC ON BGD_TIPDOC.IDTIPDOC = BTA_TIPCOM.IDTIPDOC"
                . " WHERE BTA_TIPCOM.TIPO_COM = :TIPOCOM AND BTA_TIPCOM.IDTIPDOC > :IDTIPDOC";
        $this->addSqlParam($sqlParams, "TIPOCOM", $idtipcom, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "IDTIPDOC", 0, PDO::PARAM_INT);
        return $sql;
    }

    public function leggiTipoDocumentoDaTipCom($idtipcom) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiTipoDocumentoDaTipCom($idtipcom, $sqlParams), false, $sqlParams);
    }

    public function calcolaMaxProgComuBgdDocUd() {
        $sql = 'SELECT MAX(PROGCOMU) AS MAXID FROM BGD_DOC_UD';
        $max = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, array());
        if (!$max['MAXID']) {
            return 1;
        }
        return $max['MAXID'] + 1;
    }

    /**
     * Cancella BGD_DOC_AL per $progcomu e not in($idDocAlInseriti) se not null.
     * Serve per cancellare tutti gli allegati di bgd_doc_al su db con progcomu= $progcomu e che non sono presenti nella grid in grafica ($idDocAlInseriti)
     */
    public function cancellaAllegatiDocAl($progcomu, $idDocAlInseriti = null) {
        if (!$progcomu) {
            return false;
        }
        $sql = "DELETE FROM BGD_DOC_AL WHERE PROGCOMU =:PROGCOMU";
        $this->addSqlParam($sqlParams, "PROGCOMU", $progcomu, PDO::PARAM_INT);
        if ($idDocAlInseriti) {
            $sql = $sql . " AND IDDOCAL NOT IN(" . implode(", ", $idDocAlInseriti) . ")";
        }

        return $this->getCitywareDB()->query($sql, false, $sqlParams);
    }
    
    //BGD_SOSDEF
    public function getSqlLeggiBgdSosdef($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT * FROM BGD_SOSDEF";
        $where = 'WHERE';
        
        if (!empty($filtri['IDSOSDEF'])) {
            $this->addSqlParam($sqlParams, 'IDSOSDEF', $filtri['IDSOSDEF'], PDO::PARAM_INT);
            $sql .= " $where IDSOSDEF=:IDSOSDEF";
            $where = 'AND';
        }
        if (!empty($filtri['DESCRIZIONE'])) {
            $this->addSqlParam($sqlParams, "DESCRIZIONE", "%" . strtoupper(trim($filtri['DESCRIZIONE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESCRIZIONE") . " LIKE :DESCRIZIONE";
            $where = 'AND';
        }
        if (!empty($filtri['TIPO_GEST'])) {
            $this->addSqlParam($sqlParams, 'TIPO_GEST', $filtri['TIPO_GEST'], PDO::PARAM_INT);
            $sql .= " $where TIPO_GEST=:TIPO_GEST";
            $where = 'AND';
        }
        if (!empty($filtri['VERSIONE'])) {
            $this->addSqlParam($sqlParams, 'VERSIONE', $filtri['VERSIONE'], PDO::PARAM_INT);
            $sql .= " $where VERSIONE=:VERSIONE";
            $where = 'AND';
        }
        if (!empty($filtri['CODUTE'])) {
            $this->addSqlParam($sqlParams, 'CODUTE', '%'.strtoupper(trim($filtri['CODUTE'])).'%', PDO::PARAM_STR);
            $sql .= " $where ".$this->getCitywareDB()->strUpper("CODUTE")." LIKE :CODUTE";
            $where = 'AND';
        }
        if (!empty($filtri['FLAG_DIS'])) {
            $this->addSqlParam($sqlParams, 'FLAG_DIS', $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDSOSDEF';
        return $sql;
    }

    public function leggiBgdSosdef($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdSosdef($filtri, false, $sqlParams), $multipla, $sqlParams);
    }
    
    public function getSqlLeggiBgdSosdefChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['IDSOSDEF'] = $cod;
        return self::getSqlLeggiBgdSosdef($filtri, true, $sqlParams);
    }

    public function leggiBgdSosdefChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdSosdefChiave($cod, $sqlParams), false, $sqlParams);
    }
    
    //BGD_SOSSEZ
    public function getSqlLeggiBgdSossez($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT * FROM BGD_SOSSEZ";
        $where = 'WHERE';
        
        if (!empty($filtri['IDSOSSEZ'])) {
            $this->addSqlParam($sqlParams, 'IDSOSSEZ', $filtri['IDSOSSEZ'], PDO::PARAM_INT);
            $sql .= " $where IDSOSSEZ=:IDSOSSEZ";
            $where = 'AND';
        }
        if (!empty($filtri['SEZIONE'])) {
            $this->addSqlParam($sqlParams, "SEZIONE", "%" . strtoupper(trim($filtri['SEZIONE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("SEZIONE") . " LIKE :SEZIONE";
            $where = 'AND';
        }
        
        if (!empty($filtri['CODUTE'])) {
            $this->addSqlParam($sqlParams, 'CODUTE', '%'.strtoupper(trim($filtri['CODUTE'])).'%', PDO::PARAM_STR);
            $sql .= " $where ".$this->getCitywareDB()->strUpper("CODUTE")." LIKE :CODUTE";
            $where = 'AND';
        }
        if (!empty($filtri['FLAG_DIS'])) {
            $this->addSqlParam($sqlParams, 'FLAG_DIS', $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDSOSSEZ';
        return $sql;
    }

    public function leggiBgdSossez($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdSossez($filtri, false, $sqlParams), $multipla, $sqlParams);
    }
    
    public function getSqlLeggiBgdSossezChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['IDSOSSEZ'] = $cod;
        return self::getSqlLeggiBgdSossez($filtri, true, $sqlParams);
    }

    public function leggiBgdSossezChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdSossezChiave($cod, $sqlParams), false, $sqlParams);
    }
    
    //BGD_SOSADS
    public function getSqlLeggiBgdSosadsLeftJoinBgdSossez($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGD_SOSADS.*, BGD_SOSSEZ.* FROM BGD_SOSADS "
                . "LEFT JOIN BGD_SOSSEZ ON BGD_SOSADS.IDSOSSEZ=BGD_SOSSEZ.IDSOSSEZ";
        $where = 'WHERE';
        
        if (!empty($filtri['IDSOSADS'])) {
            $this->addSqlParam($sqlParams, 'IDSOSADS', $filtri['IDSOSADS'], PDO::PARAM_INT);
            $sql .= " $where IDSOSADS=:IDSOSADS";
            $where = 'AND';
        }
        if (!empty($filtri['IDSOSSEZ'])) {
            $this->addSqlParam($sqlParams, 'IDSOSSEZ', $filtri['IDSOSSEZ'], PDO::PARAM_INT);
            $sql .= " $where IDSOSSEZ=:IDSOSSEZ";
            $where = 'AND';
        }
        if (!empty($filtri['IDSOSDEF'])) {
            $this->addSqlParam($sqlParams, 'IDSOSDEF', $filtri['IDSOSDEF'], PDO::PARAM_INT);
            $sql .= " $where IDSOSDEF=:IDSOSDEF";
            $where = 'AND';
        }
        if (!empty($filtri['CODUTE'])) {
            $this->addSqlParam($sqlParams, 'CODUTE', '%'.strtoupper(trim($filtri['CODUTE'])).'%', PDO::PARAM_STR);
            $sql .= " $where ".$this->getCitywareDB()->strUpper("CODUTE")." LIKE :CODUTE";
            $where = 'AND';
        }
        if (!empty($filtri['FLAG_DIS'])) {
            $this->addSqlParam($sqlParams, 'FLAG_DIS', $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY BGD_SOSADS.SEQUENZA';
        return $sql;
    }

    public function leggiBgdSosadsLeftJoinBgdSossez($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdSosadsLeftJoinBgdSossez($filtri, false, $sqlParams), $multipla, $sqlParams);
    }
    
    //BGD_SOSTAG
    public function getSqlLeggiBgdSostag($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT * FROM BGD_SOSTAG";
        $where = 'WHERE';
        
        if (!empty($filtri['IDSOSTAG'])) {
            $this->addSqlParam($sqlParams, 'IDSOSTAG', $filtri['IDSOSTAG'], PDO::PARAM_INT);
            $sql .= " $where IDSOSTAG=:IDSOSTAG";
            $where = 'AND';
        }
        if (!empty($filtri['IDSOSSEZ'])) {
            $this->addSqlParam($sqlParams, 'IDSOSSEZ', $filtri['IDSOSSEZ'], PDO::PARAM_INT);
            $sql .= " $where IDSOSSEZ=:IDSOSSEZ";
            $where = 'AND';
        }
        if (!empty($filtri['SEQUENZA'])) {
            $this->addSqlParam($sqlParams, 'SEQUENZA', $filtri['SEQUENZA'], PDO::PARAM_INT);
            $sql .= " $where SEQUENZA=:SEQUENZA";
            $where = 'AND';
        }
        if (!empty($filtri['NOME_TAG'])) {
            $this->addSqlParam($sqlParams, 'NOME_TAG', '%'.strtoupper(trim($filtri['NOME_TAG'])).'%', PDO::PARAM_STR);
            $sql .= " $where ".$this->getCitywareDB()->strUpper("NOME_TAG")." LIKE :NOME_TAG";
            $where = 'AND';
        }
        if (!empty($filtri['ID_PADRE'])) {
            $this->addSqlParam($sqlParams, 'ID_PADRE', $filtri['ID_PADRE'], PDO::PARAM_INT);
            $sql .= " $where ID_PADRE=:ID_PADRE";
            $where = 'AND';
        }
        if (!empty($filtri['NODO_FOGLIA'])) {
            $this->addSqlParam($sqlParams, 'NODO_FOGLIA', $filtri['NODO_FOGLIA']-1, PDO::PARAM_INT);
            $sql .= " $where NODO_FOGLIA=:NODO_FOGLIA";
            $where = 'AND';
        }
        if (!empty($filtri['FORMATO_TAG'])) {
            $this->addSqlParam($sqlParams, 'FORMATO', $filtri['FORMATO_TAG'], PDO::PARAM_INT);
            $sql .= " $where FORMATO=:FORMATO";
            $where = 'AND';
        }
        if (!empty($filtri['TAB_ALIAS'])) {
            $this->addSqlParam($sqlParams, 'TAB_ALIAS', '%'.strtoupper(trim($filtri['TAB_ALIAS'])).'%', PDO::PARAM_STR);
            $sql .= " $where ".$this->getCitywareDB()->strUpper("TAB_ALIAS")." LIKE :TAB_ALIAS";
            $where = 'AND';
        }
        if (!empty($filtri['FLAG_OBBL'])) {
            $this->addSqlParam($sqlParams, 'FLAG_OBBL', $filtri['FLAG_OBBL']-1, PDO::PARAM_INT);
            $sql .= " $where FLAG_OBBL=:FLAG_OBBL";
            $where = 'AND';
        }
        if (!empty($filtri['VALORE'])) {
            $this->addSqlParam($sqlParams, 'VALORE1', '%'.strtoupper(trim($filtri['VALORE'])).'%', PDO::PARAM_STR);
            $sql .= " $where ( ".$this->getCitywareDB()->strUpper("SRC_CAMPODB")." LIKE :VALORE1";
            $where = 'OR';
            $this->addSqlParam($sqlParams, 'VALORE2', '%'.strtoupper(trim($filtri['VALORE'])).'%', PDO::PARAM_STR);
            $sql .= " $where ".$this->getCitywareDB()->strUpper("SRC_EXPR")." LIKE :VALORE2";
            $where = 'OR';
            $this->addSqlParam($sqlParams, 'VALORE3', '%'.strtoupper(trim($filtri['VALORE'])).'%', PDO::PARAM_STR);
            $sql .= " $where ".$this->getCitywareDB()->strUpper("SRC_FISSO")." LIKE :VALORE3 ) ";
            $where = 'AND';
        }
        if (!empty($filtri['CODUTE'])) {
            $this->addSqlParam($sqlParams, 'CODUTE', '%'.strtoupper(trim($filtri['CODUTE'])).'%', PDO::PARAM_STR);
            $sql .= " $where ".$this->getCitywareDB()->strUpper("CODUTE")." LIKE :CODUTE";
            $where = 'AND';
        }
        if (!empty($filtri['FLAG_DIS'])) {
            $this->addSqlParam($sqlParams, 'FLAG_DIS', $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDSOSTAG, IDSOSSEZ, SEQUENZA';
        return $sql;
    }

    public function leggiBgdSostag($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdSostag($filtri, false, $sqlParams), $multipla, $sqlParams);
    }
    
    public function getSqlLeggiBgdSostagChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['IDSOSTAG'] = $cod;
        return self::getSqlLeggiBgdSostag($filtri, true, $sqlParams);
    }

    public function leggiBgdSostagChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdSostagChiave($cod, $sqlParams), false, $sqlParams);
    }
    
    //BGD_EXT
    public function getSqlLeggiBgdExt($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT * FROM BGD_EXT";
        $where = 'WHERE';
        
        if (!empty($filtri['PK_EXT'])) {
            $this->addSqlParam($sqlParams, 'PK_EXT', $filtri['PK_EXT'], PDO::PARAM_INT);
            $sql .= " $where PK_EXT=:PK_EXT";
            $where = 'AND';
        }
        if (!empty($filtri['SIGLA_EXT'])) {
            $this->addSqlParam($sqlParams, "SIGLA_EXT", "%" . strtoupper(trim($filtri['SIGLA_EXT'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("SIGLA_EXT") . " LIKE :SIGLA_EXT";
            $where = 'AND';
        }
        if (!empty($filtri['F_CTRL_EXT'])) {
            $this->addSqlParam($sqlParams, 'F_CTRL_EXT', $filtri['F_CTRL_EXT'], PDO::PARAM_INT);
            $sql .= " $where F_CTRL_EXT=:F_CTRL_EXT";
            $where = 'AND';
        }
        if (!empty($filtri['CODUTE'])) {
            $this->addSqlParam($sqlParams, 'CODUTE', '%'.strtoupper(trim($filtri['CODUTE'])).'%', PDO::PARAM_STR);
            $sql .= " $where ".$this->getCitywareDB()->strUpper("CODUTE")." LIKE :CODUTE";
            $where = 'AND';
        }
        if (!empty($filtri['FLAG_DIS'])) {
            $this->addSqlParam($sqlParams, 'FLAG_DIS', $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PK_EXT';
        return $sql;
    }

    public function leggiBgdExt($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdExt($filtri, false, $sqlParams), $multipla, $sqlParams);
    }
    
    public function getSqlLeggiBgdExtChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['PK_EXT'] = $cod;
        return self::getSqlLeggiBgdExt($filtri, true, $sqlParams);
    }

    public function leggiBgdExtChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgdExtChiave($cod, $sqlParams), false, $sqlParams);
    }
    
}

?>