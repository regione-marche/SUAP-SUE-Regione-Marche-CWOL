<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_CITYWARE.class.php';

/**
 *
 * Utility DB Cityware (Modulo BTA_SOGG)
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
class cwbLibDB_BTA_SOGG extends cwbLibDB_CITYWARE {
    // BTA_SOGG

    /**
     * Restituisce comando sql per lettura tabella BTA_SOGG
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaSogg($filtri, $excludeOrderBy = false, $soloAttuali = 1, &$sqlParams = array()) {
        if (!$soloAttuali) {
            $sql = "SELECT
                        s.*,
                        BTA_SOGG.RAGSOC,
                        BTA_SOGG.RAGSOC_RIC
                    FROM BTA_SOGG_V04 s
                    JOIN BTA_SOGG ON s.PROGSOGG = BTA_SOGG.PROGSOGG
                    LEFT JOIN FTA_CLFOR ON s.PROGSOGG = FTA_CLFOR.PROGSOGG";
        }
        if ($soloAttuali == 1) {
            $sql = "SELECT 
                        s.* FROM BTA_SOGG_V01 s 
                    LEFT JOIN FTA_CLFOR ON s.PROGSOGG = FTA_CLFOR.PROGSOGG";
        }
        if ($soloAttuali > 1) {
            $sql = "SELECT
                        s.*,
                        t.PROGINT AS PROGINT_ST,
                        t.DATAVARIAZ AS DATAVARIAZ_ST,
                        t.TIPOPERS AS TIPOPERS_ST,
                        t.SESSO AS SESSO_ST,
                        t.COGNOME AS COGNOME_ST,
                        t.NOME AS NOME_ST,
                        t.NOME_RIC AS NOME_RIC_ST,
                        t.RAGSOC AS RAGSOC_ST,
                        t.RAGSOC_RIC AS RAGSOC_RIC_ST,
                        t.DITTAINDIV AS DITTAINDIV_ST,
                        t.CODFISCALE AS CODFISCALE_ST,
                        t.PARTIVA AS PARTIVA_ST,
                        t.CODNAZPRO AS CODNAZPRO_ST,
                        t.CODLOCAL AS CODLOCAL_ST,
                        BTA_LOCAL.DESLOCAL AS DESLOCAL_ST,
                        t.GIORNO AS GIORNO_ST,
                        t.MESE AS MESE_ST,
                        t.ANNO AS ANNO_ST,
                        " . $this->getCitywareDB()->strConcat('s.PROGSOGG', "'|'", "COALESCE(t.DATAVARIAZ,'1900-01-01')") . " AS ROW_ID
                    FROM BTA_SOGG_V01 s
                    LEFT JOIN BTA_SOGGST t ON s.PROGSOGG = t.PROGSOGG
                    LEFT JOIN BTA_LOCAL ON t.CODNAZPRO = BTA_LOCAL.CODNAZPRO
                                        AND t.CODLOCAL = BTA_LOCAL.CODLOCAL
                    LEFT JOIN FTA_CLFOR ON s.PROGSOGG = FTA_CLFOR.PROGSOGG";
        }
        $where = 'WHERE ';

        switch ($soloAttuali) {
            case 0:
            case 1: // RICERCA "SOLO DATI ATTUALI"
                if (!empty($filtri['SESSO'])) {
                    $this->addSqlParam($sqlParams, "SESSO", $filtri['SESSO'], PDO::PARAM_STR);
                    $sql .= " $where SESSO=:SESSO";
                    $where = 'AND';
                }
                if (!empty($filtri['TIPOPERS'])) {
                    $this->addSqlParam($sqlParams, "TIPOPERS", $filtri['TIPOPERS'], PDO::PARAM_STR);
                    $sql .= " $where TIPOPERS=:TIPOPERS";
                    $where = 'AND';
                }
                if (array_key_exists('NOME_RIC', $filtri) && $filtri['NOME_RIC'] != null) {
                    $this->addSqlParam($sqlParams, "NOME_RIC", strtoupper(trim($filtri['NOME_RIC'])) . "%", PDO::PARAM_STR);  //SG 22.10.2017 Tolto il % prima del nome come su CW
                    $sql .= " $where " . $this->getCitywareDB()->strUpper("s.NOME_RIC") . " LIKE :NOME_RIC";
                    $where = 'AND';
                }
                if (array_key_exists('NOME_RIC_OR_RAGSOC_RIC', $filtri) && $filtri['NOME_RIC_OR_RAGSOC_RIC'] != null) { // NUOVO 25-09-2019
                    $this->addSqlParam($sqlParams, "NOME_RIC_OR_RAGSOC_RIC1", strtoupper(trim($filtri['NOME_RIC_OR_RAGSOC_RIC'])) . "%", PDO::PARAM_STR);
                    $this->addSqlParam($sqlParams, "NOME_RIC_OR_RAGSOC_RIC2", strtoupper(trim($filtri['NOME_RIC_OR_RAGSOC_RIC'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where (" . $this->getCitywareDB()->strUpper("s.NOME_RIC") . " LIKE :NOME_RIC_OR_RAGSOC_RIC1";
                    $sql .= " OR " . $this->getCitywareDB()->strUpper("s.RAGSOC_RIC") . " LIKE :NOME_RIC_OR_RAGSOC_RIC2)";
                    $where = 'AND';
                }
                if (!empty($filtri['RAGSOC_RIC'])) {
                    $this->addSqlParam($sqlParams, "RAGSOC_RIC", "%" . strtoupper(trim($filtri['RAGSOC_RIC'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where " . $this->getCitywareDB()->strUpper("s.RAGSOC_RIC") . " LIKE :RAGSOC_RIC";
                    $where = 'AND';
                }
                if (!empty($filtri['CODFISCALE'])) {
                    $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where " . $this->getCitywareDB()->strUpper("CODFISCALE") . " LIKE :CODFISCALE";
                    $where = 'AND';
                }
                if (!empty($filtri['PARTIVA'])) {
                    $this->addSqlParam($sqlParams, "PARTIVA", "%" . strtoupper(trim($filtri['PARTIVA'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where " . $this->getCitywareDB()->strUpper("PARTIVA") . " LIKE :PARTIVA";
                    $where = 'AND';
                }
                if (!empty($filtri['CODFISCALEORPARTIVA'])) {
                    $this->addSqlParam($sqlParams, "CODFISCALEOR", "%" . strtoupper(trim($filtri['CODFISCALEORPARTIVA'])) . "%", PDO::PARAM_STR);
                    $this->addSqlParam($sqlParams, "PARTIVAOR", "%" . strtoupper(trim($filtri['CODFISCALEORPARTIVA'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where (" . $this->getCitywareDB()->strUpper("CODFISCALE") . " LIKE :CODFISCALEOR";
                    $sql .= " OR " . $this->getCitywareDB()->strUpper("PARTIVA") . " LIKE :PARTIVAOR ) ";
                    $where = 'AND';
                }
                if (!empty($filtri['DESLOCAL'])) {
                    $this->addSqlParam($sqlParams, "DESLOCAL", "%" . strtoupper(trim($filtri['DESLOCAL'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where (" . $this->getCitywareDB()->strUpper("DESLOCAL") . " LIKE :DESLOCAL)";
                    $where = 'AND';
                }
                if (isSet($filtri['DITTAINDIV'])) {
                    $this->addSqlParam($sqlParams, "DITTAINDIV", $filtri['DITTAINDIV'], PDO::PARAM_INT);
                    $sql .= " $where DITTAINDIV=:DITTAINDIV";
                    $where = 'AND';
                }
                $sql = $this->presenteIn($filtri, $sql, $where);
                break;
            case 2: // RICERCA "DATI ATTUALI + STORICO"
                if (!empty($filtri['SESSO'])) {
                    $this->addSqlParam($sqlParams, "SESSO", $filtri['SESSO'], PDO::PARAM_STR);
                    $this->addSqlParam($sqlParams, "SESSO_ST", $filtri['SESSO'], PDO::PARAM_STR);
                    $sql .= " $where (s.SESSO=:SESSO" . " OR " . " t.SESSO=:SESSO_ST)";
                    $where = 'AND';
                }
                if (!empty($filtri['TIPOPERS'])) {
                    $this->addSqlParam($sqlParams, "TIPOPERS", $filtri['TIPOPERS'], PDO::PARAM_STR);
                    $this->addSqlParam($sqlParams, "TIPOPERS_ST", $filtri['TIPOPERS'], PDO::PARAM_STR);
                    $sql .= " $where (s.TIPOPERS=:TIPOPERS" . " OR " . " t.TIPOPERS=:TIPOPERS_ST)";
                    $where = 'AND';
                }
                if (!empty($filtri['NOME_RIC'])) {
                    $this->addSqlParam($sqlParams, "NOME_RIC", "%" . strtoupper(trim($filtri['NOME_RIC'])) . "%", PDO::PARAM_STR);
                    $this->addSqlParam($sqlParams, "NOME_RIC_ST", "%" . strtoupper(trim($filtri['NOME_RIC'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where (" . $this->getCitywareDB()->strUpper("s.NOME_RIC") . " LIKE :NOME_RIC" . " OR "
                            . $this->getCitywareDB()->strUpper("t.NOME_RIC") . " LIKE :NOME_RIC_ST)";
                    $where = 'AND';
                }
                if (array_key_exists('NOME_RIC_OR_RAGSOC_RIC', $filtri) && $filtri['NOME_RIC_OR_RAGSOC_RIC'] != null) { // NUOVO 25-09-2019
                    $this->addSqlParam($sqlParams, "NOME_RIC_OR_RAGSOC_RIC1", strtoupper(trim($filtri['NOME_RIC_OR_RAGSOC_RIC'])) . "%", PDO::PARAM_STR);
                    $this->addSqlParam($sqlParams, "NOME_RIC_OR_RAGSOC_RIC2", strtoupper(trim($filtri['NOME_RIC_OR_RAGSOC_RIC'])) . "%", PDO::PARAM_STR);
                    $this->addSqlParam($sqlParams, "NOME_RIC_OR_RAGSOC_RIC3", strtoupper(trim($filtri['NOME_RIC_OR_RAGSOC_RIC'])) . "%", PDO::PARAM_STR);
                    $this->addSqlParam($sqlParams, "NOME_RIC_OR_RAGSOC_RIC4", strtoupper(trim($filtri['NOME_RIC_OR_RAGSOC_RIC'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where (" . $this->getCitywareDB()->strUpper("s.NOME_RIC") . " LIKE :NOME_RIC_OR_RAGSOC_RIC1";
                    $sql .= " OR " . $this->getCitywareDB()->strUpper("s.RAGSOC_RIC") . " LIKE :NOME_RIC_OR_RAGSOC_RIC2";
                    $sql .= " OR " . $this->getCitywareDB()->strUpper("t.NOME_RIC") . " LIKE :NOME_RIC_OR_RAGSOC_RIC3";
                    $sql .= " OR " . $this->getCitywareDB()->strUpper("t.RAGSOC_RIC") . " LIKE :NOME_RIC_OR_RAGSOC_RIC4)";
                    $where = 'AND';
                }
                if (!empty($filtri['RAGSOC_RIC'])) {
                    $this->addSqlParam($sqlParams, "RAGSOC_RIC", "%" . strtoupper(trim($filtri['RAGSOC_RIC'])) . "%", PDO::PARAM_STR);
                    $this->addSqlParam($sqlParams, "RAGSOC_RIC_ST", "%" . strtoupper(trim($filtri['RAGSOC_RIC'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where (" . $this->getCitywareDB()->strUpper("s.RAGSOC_RIC") . " LIKE :RAGSOC_RIC" . " OR "
                            . $this->getCitywareDB()->strUpper("t.RAGSOC_RIC") . " LIKE :RAGSOC_RIC_ST)";
                    $where = 'AND';
                }
                if (!empty($filtri['CODFISCALE'])) {
                    $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                    $this->addSqlParam($sqlParams, "CODFISCALE_ST", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where (" . $this->getCitywareDB()->strUpper("s.CODFISCALE") . " LIKE :CODFISCALE" . " OR "
                            . $this->getCitywareDB()->strUpper("t.CODFISCALE") . " LIKE :CODFISCALE_ST)";
                    $where = 'AND';
                }
                if (!empty($filtri['PARTIVA'])) {
                    $this->addSqlParam($sqlParams, "PARTIVA", "%" . strtoupper(trim($filtri['PARTIVA'])) . "%", PDO::PARAM_STR);
                    $this->addSqlParam($sqlParams, "PARTIVA_ST", "%" . strtoupper(trim($filtri['PARTIVA'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where (" . $this->getCitywareDB()->strUpper("s.PARTIVA") . " LIKE :PARTIVA" . " OR "
                            . $this->getCitywareDB()->strUpper("t.PARTIVA") . " LIKE :PARTIVA_ST)";
                    $where = 'AND';
                }
                if (!empty($filtri['DESLOCAL'])) {
                    $this->addSqlParam($sqlParams, "DESLOCAL", "%" . strtoupper(trim($filtri['DESLOCAL'])) . "%", PDO::PARAM_STR);
                    $this->addSqlParam($sqlParams, "DESLOCAL_ST", "%" . strtoupper(trim($filtri['DESLOCAL'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where (" . $this->getCitywareDB()->strUpper("s.DESLOCAL") . " LIKE :DESLOCAL OR "
                            . $this->getCitywareDB()->strUpper("BTA_LOCAL.DESLOCAL") . " LIKE :DESLOCAL_ST)";
                    $where = 'AND';
                }
                if (isSet($filtri['DITTAINDIV'])) {
                    $this->addSqlParam($sqlParams, "DITTAINDIV", $filtri['DITTAINDIV'], PDO::PARAM_INT);
                    $this->addSqlParam($sqlParams, "DITTAINDIV_ST", $filtri['DITTAINDIV'], PDO::PARAM_INT);
                    $sql .= " $where (s.DITTAINDIV=:DITTAINDIV AND t.DITTAINDIV=:DITTAINDIV_ST)";
                    $where = 'AND';
                }
                $sql = $this->presenteIn($filtri, $sql, $where);
                break;

            case 3: // RICERCA "SOLO STORICO"
                if (!empty($filtri['SESSO'])) {
                    $this->addSqlParam($sqlParams, "SESSO", $filtri['SESSO'], PDO::PARAM_STR);
                    $sql .= " $where SESSO_ST=:SESSO";
                    $where = 'AND';
                }
                if (!empty($filtri['TIPOPERS'])) {
                    $this->addSqlParam($sqlParams, "TIPOPERS", $filtri['TIPOPERS'], PDO::PARAM_STR);
                    $sql .= " $where t.TIPOPERS=:TIPOPERS";
                    $where = 'AND';
                }
                if (!empty($filtri['NOME_RIC'])) {
                    $this->addSqlParam($sqlParams, "NOME_RIC", "%" . strtoupper(trim($filtri['NOME_RIC'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where " . $this->getCitywareDB()->strUpper("t.NOME_RIC") . " LIKE :NOME_RIC";
                    $where = 'AND';
                }
                if (array_key_exists('NOME_RIC_OR_RAGSOC_RIC', $filtri) && $filtri['NOME_RIC_OR_RAGSOC_RIC'] != null) { // NUOVO 25-09-2019
                    $this->addSqlParam($sqlParams, "NOME_RIC_OR_RAGSOC_RIC1", strtoupper(trim($filtri['NOME_RIC_OR_RAGSOC_RIC'])) . "%", PDO::PARAM_STR);
                    $this->addSqlParam($sqlParams, "NOME_RIC_OR_RAGSOC_RIC2", strtoupper(trim($filtri['NOME_RIC_OR_RAGSOC_RIC'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where (" . $this->getCitywareDB()->strUpper("t.NOME_RIC") . " LIKE :NOME_RIC_OR_RAGSOC_RIC1";
                    $sql .= " OR " . $this->getCitywareDB()->strUpper("t.RAGSOC_RIC") . " LIKE :NOME_RIC_OR_RAGSOC_RIC2)";
                    $where = 'AND';
                }
                if (!empty($filtri['RAGSOC_RIC'])) {
                    $this->addSqlParam($sqlParams, "RAGSOC_RIC", "%" . strtoupper(trim($filtri['RAGSOC_RIC'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where " . $this->getCitywareDB()->strUpper("RAGSOC_RIC_ST") . " LIKE :RAGSOC_RIC";
                    $where = 'AND';
                }
                if (!empty($filtri['CODFISCALE'])) {
                    $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where " . $this->getCitywareDB()->strUpper("CODFISCALE_ST") . " LIKE :CODFISCALE";
                    $where = 'AND';
                }
                if (!empty($filtri['PARTIVA'])) {
                    $this->addSqlParam($sqlParams, "PARTIVA", "%" . strtoupper(trim($filtri['PARTIVA'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where " . $this->getCitywareDB()->strUpper("PARTIVA_ST") . " LIKE :PARTIVA";
                    $where = 'AND';
                }
                if (!empty($filtri['DESLOCAL'])) {
                    $this->addSqlParam($sqlParams, "DESLOCAL", "%" . strtoupper(trim($filtri['DESLOCAL'])) . "%", PDO::PARAM_STR);
                    $sql .= " $where " . $this->getCitywareDB()->strUpper("BTA_LOCAL.DESLOCAL") . " LIKE :DESLOCAL";
                    $where = 'AND';
                }
                if (isSet($filtri['DITTAINDIV'])) {
                    $this->addSqlParam($sqlParams, "DITTAINDIV", $filtri['DITTAINDIV'], PDO::PARAM_INT);
                    $sql .= " $where t.DITTAINDIV=:DITTAINDIV";
                    $where = 'AND';
                }
                $sql = $this->presenteIn($filtri, $sql, $where);
                break;
        }
        if (!empty($filtri['PROGSOGG'])) {
            $this->addSqlParam($sqlParams, "PROGSOGG", $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where s.PROGSOGG=:PROGSOGG";
            $where = 'AND';
        }
        if (!empty($filtri['PROGSOGG_diff'])) {
            $this->addSqlParam($sqlParams, "PROGSOGG_diff", $filtri['PROGSOGG_diff'], PDO::PARAM_INT);
            $sql .= " $where s.PROGSOGG<>:PROGSOGG_diff";
            $where = 'AND';
        }
        if (!empty($filtri['PROGSOGG_key'])) {
            $this->addSqlParam($sqlParams, "PROGSOGG_key", $filtri['PROGSOGG_key'], PDO::PARAM_INT);
            $sql .= " $where s.PROGSOGG=:PROGSOGG_key";
            $where = 'AND';
        }
        if (!empty($filtri['DATAVARIAZ_ST_key'])) {
            $this->addSqlParam($sqlParams, "DATAVARIAZ_ST_key", $filtri['DATAVARIAZ_ST_key'], PDO::PARAM_INT);
            $sql .= " $where t.DATAVARIAZ=:DATAVARIAZ_ST_key";
            $where = 'AND';
        }
        if (!empty($filtri['DATAMORTE'])) {
            $sql .= " $where s.DATAMORTE IS NULL ";
            $where = 'AND';
        }
//        switch (true) {
//            case !cwbLibCheckInput::IsNBZ($filtri['CODFISCALE']):
//                $sql .= '  ORDER BY CODFISCALE';
//                break;
//            case !cwbLibCheckInput::IsNBZ($filtri['PARTIVA']):
//                $sql .= '  ORDER BY PARTIVA';
//                break;
//            default:
//                if ($filtri['ORDIN'] == 1) {
//                    $sql .= '  ORDER BY NOME_RIC';
//                } else {
//                    $sql .= '  ORDER BY COGNOME,NOME';
//                }
//                break;
//        }
//End Switch
        if (!empty($filtri['GIORNO'])) {
            $this->addSqlParam($sqlParams, "GIORNO", $filtri['GIORNO'], PDO::PARAM_INT);
            $sql .= " $where s.GIORNO=:GIORNO";
            $where = 'AND';
        }
        if (!empty($filtri['MESE'])) {
            $this->addSqlParam($sqlParams, "MESE", $filtri['MESE'], PDO::PARAM_INT);
            $sql .= " $where s.MESE=:MESE";
            $where = 'AND';
        }
        if (!empty($filtri['ANNO'])) {
            $this->addSqlParam($sqlParams, "ANNO", $filtri['ANNO'], PDO::PARAM_INT);
            $sql .= " $where s.ANNO=:ANNO";
            $where = 'AND';
        }
        if (isSet($filtri['FL_DATAFINE'])) {
            if ($filtri['FL_DATAFINE'] == 0) {
                $sql .= " $where FTA_CLFOR.DATAFINE IS NULL ";
                $where = 'AND';                
            }
        }
        //add filtro WHERE fissi
        if (key_exists('WHERE', $filtri)) {
            $sql .= " $where 1=1 ";
            $this->getExtraWhereBtaSogg($sql, $filtri['WHERE']);
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY s.COGNOME, s.NOME, s.PROGSOGG';
        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SOGG
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaSogg($filtri, $multipla = true, $soloAttuali = 1) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaSogg($filtri, false, $soloAttuali, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_SOGG
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaSoggChiave($progsogg, &$sqlParams) {
        $filtri = array();
        $filtri['PROGSOGG'] = $progsogg;
        return self::getSqlLeggiBtaSogg($filtri, true, 1, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_SOGG per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaSoggChiave($progsogg) {
        if (!$progsogg) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaSoggChiave($progsogg, $sqlParams), false, $sqlParams);
    }

    // BTA_SOGG_ST

    /**
     * Restituisce comando sql per lettura tabella BTA_SOGG_ST
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBtaSoggst($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BTA_SOGGST.* FROM BTA_SOGGST";
        $where = 'WHERE';
        if (array_key_exists('PROGSOGG', $filtri) && $filtri['PROGSOGG'] != null) {
            $this->addSqlParam($sqlParams, "PROGSOGG", $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where PROGSOGG=:PROGSOGG";
            $where = 'AND';
        }
        if (array_key_exists('PROGINT', $filtri) && $filtri['PROGINT'] != null) {
            $this->addSqlParam($sqlParams, "PROGINT", $filtri['PROGINT'], PDO::PARAM_INT);
            $sql .= " $where PROGINT=:PROGINT";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGSOGG';

        return $sql;
    }

    /**
     * Restituisce dati tabella BTA_SOGG_ST
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBtaSoggst($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaSoggst($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BTA_SOGG_ST
     * @param string $cod Chiave     
     * @return string Comando sql
     */
    public function getSqlLeggiBtaSoggstChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGINT'] = $cod;
        return self::getSqlLeggiBtaSoggst($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_SOGG_ST per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaSoggstChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaSoggstChiave($cod, $sqlParams), false, $sqlParams);
    }

    // DAN_ANAGRA

    /**
     * Restituisce comando sql per lettura tabella DAN_ANAGRA passandogli il PROGRESSIVO DEL SOGGETTO
     * @param  $progsogg Progressivo Soggeto     
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiDanAnagra($progsogg, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT DAN_ANAGRA.* FROM DAN_ANAGRA";
        $where = 'WHERE';
        if ($progsogg != null) {
            $this->addSqlParam($sqlParams, "PROGSOGG", strtoupper(trim($progsogg)), PDO::PARAM_INT);
            $sql .= " $where PROGSOGG=:PROGSOGG";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella DAN_ANAGRA
     * @param  $progsogg Progressivo Soggeto     
     * @return object Resultset
     */
    public function leggiDanAnagra($progsogg, $multipla = false) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiDanAnagra($progsogg, false, $sqlParams), $multipla, $sqlParams);
    }

    // DTA_FLAGAN
    /**
     * Restituisce comando sql per lettura tabella DTA_FLAGAN passandogli valore della famiglia_t e valore del motivo_c
     * @param array $famiglia_t, $motivo_c
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiDtaFlagan($famiglia_t, $motivo_c, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT DTA_FLAGAN.* FROM DTA_FLAGAN";
        $where = 'WHERE';
        if ($famiglia_t != null) {
            $this->addSqlParam($sqlParams, "FAMIGLIA_T", strtoupper(trim($famiglia_t)), PDO::PARAM_STR);
            $sql .= " $where FAMIGLIA_T=:FAMIGLIA_T";
            $where = 'AND';
        }
        if ($motivo_c != null) {
            $this->addSqlParam($sqlParams, "MOTIVO_C", strtoupper(trim($motivo_c)), PDO::PARAM_STR);
            $sql .= " $where MOTIVO_C=:MOTIVO_C";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce dati tabella DTA_FLAGAN
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiDtaFlagan($famiglia_t, $motivo_c, $multipla = false) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiDtaFlagan($famiglia_t, $motivo_c, false, $sqlParams), $multipla, $sqlParams);
    }

    public function presenteIn($filtri, $sql, &$where) {
        if (array_key_exists('ANAGRA', $filtri) && $filtri['ANAGRA'] != null) {
            $sql .= " $where ANAGRA IS NOT NULL";
            $where = 'AND';
        }
        if (array_key_exists('CONTRIB', $filtri) && $filtri['CONTRIB'] != null) {
            $sql .= " $where CONTRIB IS NOT NULL";
            $where = 'AND';
        }
        if (array_key_exists('FORNIT', $filtri) && $filtri['FORNIT'] != null) {
            $sql .= " $where FORNIT IS NOT NULL";
            $where = 'AND';
        }
        if (array_key_exists('SOGGISCR', $filtri) && $filtri['SOGGISCR'] != null) {
            $sql .= " $where SOGGISCR IS NOT NULL";
            $where = 'AND';
        }
        if (array_key_exists('AMMINISTR', $filtri) && $filtri['AMMINISTR'] != null) {
            $sql .= " $where AMMINISTR IS NOT NULL";
            $where = 'AND';
        }
        return $sql;
    }

    /**
     * Restituisce true se sono presenti documenti collegati per un certo soggetto, false in caso contrario
     * @param integer $progsogg Progressivo del soggetto (da BTA_SOGG)
     * @param integer $dinv Se il soggetto è ditta individuale (1) o meno (0)
     * @return boolean
     */
    public function documentiCollegatiSoggetto($progsogg = null, $dinv = 1) {
        if (empty($progsogg)) {
            return false;
        }

        $sqlParams = array();
        $sql = 'SELECT * FROM FES_DOCTES WHERE FLAG_DINV = :DINV AND PROGSOGG = :PROGSOGG';
        $this->addSqlParam($sqlParams, "PROGSOGG", $progsogg, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "DINV", $dinv, PDO::PARAM_INT);
        if (ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams)) {
            return true;
        }

        $sqlParams = array();
        $sql = 'SELECT * FROM FES_DOCLIQ WHERE FLAG_DINV = :DINV AND LIQ_PROGBE = :PROGSOGG';
        $this->addSqlParam($sqlParams, "PROGSOGG", $progsogg, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "DINV", $dinv, PDO::PARAM_INT);
        if (ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams)) {
            return true;
        }

        $sqlParams = array();
        $sql = 'SELECT * FROM FEC_MOVCA WHERE FLAG_DINV = :DINV AND MCE_PROGBE = :PROGSOGG';
        $this->addSqlParam($sqlParams, "PROGSOGG", $progsogg, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "DINV", $dinv, PDO::PARAM_INT);
        if (ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams)) {
            return true;
        }

        $sqlParams = array();
        $sql = 'SELECT * FROM FAC_DETFOR WHERE FLAG_DINV = :DINV AND PROGSOGG = :PROGSOGG';
        $this->addSqlParam($sqlParams, "PROGSOGG", $progsogg, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "DINV", $dinv, PDO::PARAM_INT);
        if (ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams)) {
            return true;
        }

        $sqlParams = array();
        $sql = 'SELECT * FROM FFA_TIPMOV WHERE FLAG_DINV = :DINV AND PROG_CLIE = :PROGSOGG';
        $this->addSqlParam($sqlParams, "PROGSOGG", $progsogg, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "DINV", $dinv, PDO::PARAM_INT);
        if (ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams)) {
            return true;
        }

        $sqlParams = array();
        $sql = 'SELECT * FROM FES_DETMCA WHERE FLAG_DINV = :DINV AND PROGSOGG = :PROGSOGG';
        $this->addSqlParam($sqlParams, "PROGSOGG", $progsogg, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "DINV", $dinv, PDO::PARAM_INT);
        if (ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams)) {
            return true;
        }

        $sqlParams = array();
        $sql = 'SELECT * FROM FES_DETMCA WHERE FLAG_DINVB = :DINV AND PROG_BEN = :PROGSOGG';
        $this->addSqlParam($sqlParams, "PROGSOGG", $progsogg, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "DINV", $dinv, PDO::PARAM_INT);
        if (ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams)) {
            return true;
        }

        $sqlParams = array();
        $sql = 'SELECT * FROM FES_PROVTD WHERE FLAG_DINV = :DINV AND PROGSOGG = :PROGSOGG';
        $this->addSqlParam($sqlParams, "PROGSOGG", $progsogg, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "DINV", $dinv, PDO::PARAM_INT);
        if (ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams)) {
            return true;
        }

        $sqlParams = array();
        $sql = 'SELECT * FROM FES_PROVTD WHERE FLAG_DINVB = :DINV AND PROG_BEN = :PROGSOGG';
        $this->addSqlParam($sqlParams, "PROGSOGG", $progsogg, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "DINV", $dinv, PDO::PARAM_INT);
        if (ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams)) {
            return true;
        }

        return false;
    }

    /*
     * Aggiunge alla where la stringa 
     * @$sql = stringa SQL come field Reference
     * @$case = Tipo di where condition da aggiungere alla where
     */

    public function getExtraWhereBtaSogg(&$sql, $case = 0) {
        switch ($case) {
            case 1://Prendo solo i morti
                $where = "MOTIVO_C='M'";
                break;
            case 2: //Tutti i soggetti tranne i morti
                $where = " (MOTIVO_C<>'M' or MOTIVO_C is null) and s.DATAMORTE is null";
                break;
            case 3: //Tutti i soggetti irreperibili
                $where = "MOTIVO_C='I'";
                break;

            default: //Non faccio niente
                $where = '1=1';
                break;
        }

        //Sostituisco la condizione 1=1 con la stringa ottenuta dal case
        $sql = str_replace('1=1', $where, $sql);
    }

    /**
     * Restituisce dati tabella BTA_SOGGFE per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBtaSoggFeChiave($idSoggfe) {
        if (!$idSoggfe) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaSoggFeChiave($idSoggfe, $sqlParams), false, $sqlParams);
    }

    public function getSqlLeggiBtaSoggFeChiave($idSoggfe, &$sqlParams) {
        $filtri = array();
        $filtri['ID_SOGGFE'] = $idSoggfe;
        return self::getSqlLeggiBtaSoggFe($filtri, true, $sqlParams);
    }

    public function getSqlLeggiBtaSoggFe($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT * FROM BTA_SOGGFE";
        $where = 'WHERE ';
        if (!empty($filtri['ID_SOGGFE'])) {
            $this->addSqlParam($sqlParams, "ID_SOGGFE", $filtri['ID_SOGGFE'], PDO::PARAM_INT);
            $sql .= " $where ID_SOGGFE=:ID_SOGGFE";
            $where = 'AND';
        }
        if (!empty($filtri['NOT_ID_SOGGFE'])) {
            $this->addSqlParam($sqlParams, "NOT_ID_SOGGFE", $filtri['NOT_ID_SOGGFE'], PDO::PARAM_INT);
            $sql .= " $where ID_SOGGFE<>:NOT_ID_SOGGFE";
            $where = 'AND';
        }
        if (!empty($filtri['PROGSOGG'])) {
            $this->addSqlParam($sqlParams, "PROGSOGG", $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where PROGSOGG=:PROGSOGG";
            $where = 'AND';
        }
        if (!empty($filtri['FLAG_01'])) {
            $this->addSqlParam($sqlParams, "FLAG_01", $filtri['FLAG_01'], PDO::PARAM_INT);
            $sql .= " $where FLAG_01=:FLAG_01";
            $where = 'AND';
        }
        if (!empty($filtri['FLAG_02'])) {
            $this->addSqlParam($sqlParams, "FLAG_02", $filtri['FLAG_02'], PDO::PARAM_INT);
            $sql .= " $where FLAG_02=:FLAG_02";
            $where = 'AND';
        }
        if (!empty($filtri['FLAG_03'])) {
            $this->addSqlParam($sqlParams, "FLAG_03", $filtri['FLAG_03'], PDO::PARAM_INT);
            $sql .= " $where FLAG_03=:FLAG_03";
            $where = 'AND';
        }
        if (!empty($filtri['FLAG_04'])) {
            $this->addSqlParam($sqlParams, "FLAG_04", $filtri['FLAG_04'], PDO::PARAM_INT);
            $sql .= " $where FLAG_04=:FLAG_04";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGSOGG';

        return $sql;
    }
    
    public function leggiBtaSoggfe($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBtaSoggFe($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function leggiBtaSoggal($filtri, $multipla = true) {
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BTA_SOGG", "leggiBtaSoggalBinary");
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiCountDeleteBtaSoggal($filtri, false, false, false, $sqlParams), $multipla, $sqlParams, $infoBinaryCallback);
    }
    
    //risultato del caricamento
    public function leggiBtaSoggalBinary($result = array()) {
        $sql = "SELECT " . $this->getCitywareDB()->adapterBlob("TESTOATTO") . " FROM BTA_SOGGAL WHERE PROGSOGG = :PROGSOGG"
                . " AND RIGA_ALLEG = :RIGA_ALLEG ";
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "PROGSOGG", $result['PROGSOGG'], PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "RIGA_ALLEG", $result['RIGA_ALLEG'], PDO::PARAM_INT);
        
        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "TESTOATTO", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['TESTOATTO'] = $resultBin['TESTOATTO'];

        return $result;
    }
    
//  Query particolare per Gestione Soggetti x vedere se ci sono degli allegati
    public function getSqlLeggiCountDeleteBtaSoggal($filtri, $count = false, $delete = false, $excludeOrderBy = false, &$sqlParams = array()) {

        if ($delete) {
            $sql = 'DELETE ';
        } else {
            $sql = 'SELECT ';

            if ($count) {
                $sql .= 'COUNT(*) AS NUM_ALLEG ';
            } else {
                $sql .= 'PROGSOGG, RIGA_ALLEG, DES_ALLEG, NATURANOTA, NOME_ALLEG, '
                        . 'CODUTEINS, DATAINSER, TIMEINSER, CODUTE, DATAOPER, TIMEOPER, FLAG_DIS, UUID, '
                        . $this->getCitywareDB()->adapterBlob("TESTOATTO") . ' ';
            }
        }

        $sql .= 'FROM BTA_SOGGAL ';

        $where = 'WHERE';
        if (!empty($filtri['PROGSOGG'])) {
            $this->addSqlParam($sqlParams, 'PROGSOGG', $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where BTA_SOGGAL.PROGSOGG = :PROGSOGG";
            $where = "AND";
        }
        if (!empty($filtri['RIGA_ALLEG'])) {
            $this->addSqlParam($sqlParams, 'RIGA_ALLEG', $filtri['RIGA_ALLEG'], PDO::PARAM_INT);
            $sql .= " $where BTA_SOGGAL.RIGA_ALLEG = :RIGA_ALLEG";
            $where = "AND";
        }
        if (!empty($filtri['UUID'])) {
            $this->addSqlParam($sqlParams, 'UUID', strtoupper(trim($filtri['UUID'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper('BTA_SOGGAL.UUID') . " = :UUID";
            $where = 'AND';
        }

        return $sql;
    }    
    
    
}

?>