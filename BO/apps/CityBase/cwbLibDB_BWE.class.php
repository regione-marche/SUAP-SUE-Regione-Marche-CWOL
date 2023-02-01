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
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
class cwbLibDB_BWE extends cwbLibDB_CITYWARE {
    // BWE_FRMPAR

    /**
     * Restituisce comando sql per lettura tabella BWE_FRMPAR
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBweFrmpar($filtri, $excludeOrderBy = false, &$sqlParams, &$infoBinaryCallback) {
        $sql = "SELECT
                    ID,
                    MODELKEY,
                    KCODUTE,
                    " . $this->getCitywareDB()->adapterBlob("PARAMETRI") . ",
                    PAR_OW
                FROM BWE_FRMPAR";
        $where = 'WHERE';
        if (array_key_exists('MODELKEY', $filtri) && $filtri['MODELKEY'] != null) {
            $this->addSqlParam($sqlParams, "MODELKEY", $filtri['MODELKEY'], PDO::PARAM_STR);
            $sql .= " $where MODELKEY=:MODELKEY";
            $where = 'AND';
        }
        if (array_key_exists('KCODUTE', $filtri) && $filtri['KCODUTE'] != null) {
            $this->addSqlParam($sqlParams, "KCODUTE", $filtri['KCODUTE'], PDO::PARAM_STR);
            $sql .= " $where KCODUTE=:KCODUTE";
            $where = 'AND';
        }

        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BWE", "leggiParametriBweFrmpar");

        $sql .= $excludeOrderBy ? '' : ' ORDER BY KCODUTE';
        return $sql;
    }

    public function leggiParametriBweFrmpar(&$result = array()) {
        $sql = 'SELECT BWE_FRMPAR.PARAMETRI FROM BWE_FRMPAR WHERE ID=:ID';
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "ID", $result['ID'], PDO::PARAM_STR);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "PARAMETRI", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['PARAMETRI'] = $resultBin['PARAMETRI'];

        return $result;
    }

    /**
     * Restituisce dati tabella BWE_FRMPAR
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBweFrmpar($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweFrmpar($filtri, true, $sqlParams, $infoBinaryCallback), $flMultipla, $sqlParams, $infoBinaryCallback);
    }

    // BWE_PENDEN

    /**
     * Restituisce comando sql per lettura tabella BWE_PENDEN
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBwePendenScadenze($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT DISTINCT BWE_PENDEN.PROGKEYTAB, BWE_PENDEN.CODTIPSCAD, BWE_PENDEN.SUBTIPSCAD, BWE_PENDEN.DESCRPEND,
                BWE_PENDEN.TIPOPENDEN, BWE_PENDEN.MODPROVEN, BWE_PENDEN.PROGSOGG, BWE_PENDEN.ANNORIF, BWE_PENDEN.PROGCITYSC, BWE_PENDEN.PROGCITYSCA, BWE_PENDEN.NUMRATA,
                BWE_PENDEN.NUMDOC, BWE_PENDEN.DATASCADE, BWE_PENDEN.IMPDAPAGTO, BWE_PENDEN.IMPPAGTOT, BWE_PENDEN.DATAPAG,BWE_PENDEN.MODPAGAM,
                BWE_PENDEN.FLAG_PUBBL, BWE_PENDEN.ANNOEMI, BWE_PENDEN.NUMEMI, BWE_PENDEN.IDBOL_SERE 
                FROM BWE_PENDEN 
                LEFT JOIN BGE_AGID_SCADENZE ON BGE_AGID_SCADENZE.CODTIPSCAD = BWE_PENDEN.CODTIPSCAD AND 
                BGE_AGID_SCADENZE.SUBTIPSCAD = BWE_PENDEN.SUBTIPSCAD AND BGE_AGID_SCADENZE.PROGCITYSC = BWE_PENDEN.PROGCITYSC AND 
                BGE_AGID_SCADENZE.ANNORIF = BWE_PENDEN.ANNORIF AND BGE_AGID_SCADENZE.NUMRATA = BWE_PENDEN.NUMRATA
                INNER JOIN BTA_SERVREND ON BTA_SERVREND.CODTIPSCAD=BWE_PENDEN.CODTIPSCAD AND 
                BTA_SERVREND.SUBTIPSCAD=BWE_PENDEN.SUBTIPSCAD AND 
                BTA_SERVREND.ANNOEMI=BWE_PENDEN.ANNOEMI AND BTA_SERVREND.NUMEMI=BWE_PENDEN.NUMEMI AND 
                BTA_SERVREND.IDBOL_SERE=BWE_PENDEN.IDBOL_SERE
                INNER JOIN BTA_SERVRENDPPA ON BTA_SERVREND.PROGKEYTAB = BTA_SERVRENDPPA.IDSERVREND 
                WHERE FLAG_PUBBL>=3 AND ((BWE_PENDEN.IMPPAGTOT>=0 AND BWE_PENDEN.STATO < 4) OR (BWE_PENDEN.IMPPAGTOT>0 AND 
                BWE_PENDEN.STATO = 1 AND BWE_PENDEN.TIPOPENDEN = 1)) AND
                (BGE_AGID_SCADENZE.CODTIPSCAD IS NULL AND BGE_AGID_SCADENZE.SUBTIPSCAD IS NULL AND BGE_AGID_SCADENZE.PROGCITYSC IS NULL AND BGE_AGID_SCADENZE.ANNORIF IS NULL AND BGE_AGID_SCADENZE.NUMRATA IS NULL) 
                ";
        if (array_key_exists('ANNORIF', $filtri) && $filtri['ANNORIF'] != null) {
            $this->addSqlParam($sqlParams, "ANNORIF", strtoupper(trim($filtri['ANNORIF'])), PDO::PARAM_INT);
            $sql .= " AND BWE_PENDEN.ANNORIF=:ANNORIF";
        }
        if (array_key_exists('PROGCITYSC', $filtri) && $filtri['PROGCITYSC'] != null) {
            $this->addSqlParam($sqlParams, "PROGCITYSC", strtoupper(trim($filtri['PROGCITYSC'])), PDO::PARAM_INT);
            $sql .= " AND BWE_PENDEN.PROGCITYSC=:PROGCITYSC";
        }
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", strtoupper(trim($filtri['CODTIPSCAD'])), PDO::PARAM_INT);
            $sql .= " AND BWE_PENDEN.CODTIPSCAD=:CODTIPSCAD";
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] !== null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", strtoupper(trim($filtri['SUBTIPSCAD'])), PDO::PARAM_INT);
            $sql .= " AND BWE_PENDEN.SUBTIPSCAD=:SUBTIPSCAD";
        }
        if (array_key_exists('FLAG_PUBBL_IN', $filtri)) {
            $sql .= " AND FLAG_PUBBL IN (" . implode(", ", $filtri['FLAG_PUBBL_IN']) . ")";
        }
        if (array_key_exists('ANNOEMI', $filtri)) {
            $this->addSqlParam($sqlParams, "ANNOEMI", $filtri['ANNOEMI'], PDO::PARAM_INT);
            $sql .= " AND BWE_PENDEN.ANNOEMI = :ANNOEMI";
        }
        if (array_key_exists('NUMEMI', $filtri)) {
            $this->addSqlParam($sqlParams, "NUMEMI", $filtri['NUMEMI'], PDO::PARAM_INT);
            $sql .= " AND BWE_PENDEN.NUMEMI = :NUMEMI";
        }
        if (array_key_exists('IDBOL_SERE', $filtri)) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", $filtri['IDBOL_SERE'], PDO::PARAM_INT);
            $sql .= " AND BWE_PENDEN.IDBOL_SERE = :IDBOL_SERE";
        }
        if (array_key_exists('IUV', $filtri)) {
            $this->addSqlParam($sqlParams, "IUV", $filtri['IUV'], PDO::PARAM_INT);
            $sql .= " AND BGE_AGID_SCADENZE.IUV = :IUV";
        }
        if (array_key_exists('PROGKEYTAB', $filtri)) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " AND BWE_PENDEN.PROGKEYTAB = :PROGKEYTAB";
        }
        if (array_key_exists('INTERMEDIARIO', $filtri)) {
            $this->addSqlParam($sqlParams, "INTERMEDIARIO", $filtri['INTERMEDIARIO'], PDO::PARAM_INT);
            $sql .= " AND INTERMEDIARIO = :INTERMEDIARIO";
        }
        if (!$excludeOrderBy) {
            $sql .= ' ORDER BY BWE_PENDEN.PROGKEYTAB ';
        }
        
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_PENDEN
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBwePendenScadenze($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBwePendenScadenze($filtri, true, $sqlParams), $flMultipla, $sqlParams);
    }

    /**
     * Restituisce dati tabella BWE_PENDEN a blocchi
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBwePendenScadenzeBlocchi($filtri, $da, $per, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBSQLSelect($this->getCitywareDB(), $this->getSqlLeggiBwePendenScadenze($filtri, false, $sqlParams), $flMultipla, $da, $per, $sqlParams);
    }

    private function addBwePendenParams($filtri, &$sqlParams) {
        $where = 'WHERE';
        if (array_key_exists('ANNORIF', $filtri) && $filtri['ANNORIF'] != null) {
            $this->addSqlParam($sqlParams, "ANNORIF", strtoupper(trim($filtri['ANNORIF'])), PDO::PARAM_INT);
            $sql .= " $where ANNORIF=:ANNORIF";
            $where = 'AND';
        }
        if (array_key_exists('PROGCITYSC', $filtri) && $filtri['PROGCITYSC'] != null) {
            $this->addSqlParam($sqlParams, "PROGCITYSC", strtoupper(trim($filtri['PROGCITYSC'])), PDO::PARAM_INT);
            $sql .= " $where PROGCITYSC=:PROGCITYSC";
            $where = 'AND';
        }
        if (array_key_exists('PROGCITYSCA', $filtri) && $filtri['PROGCITYSCA'] != null) {
            $this->addSqlParam($sqlParams, "PROGCITYSCA", trim($filtri['PROGCITYSCA']), PDO::PARAM_STR);
            $sql .= " $where PROGCITYSCA=:PROGCITYSCA";
            $where = 'AND';
        }
        if (array_key_exists('TIPOPENDEN', $filtri) && $filtri['TIPOPENDEN'] != null) {
            $this->addSqlParam($sqlParams, "TIPOPENDEN", strtoupper(trim($filtri['TIPOPENDEN'])), PDO::PARAM_INT);
            $sql .= " $where TIPOPENDEN=:TIPOPENDEN";
            $where = 'AND';
        }
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", strtoupper(trim($filtri['CODTIPSCAD'])), PDO::PARAM_INT);
            $sql .= " $where CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] !== null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", strtoupper(trim($filtri['SUBTIPSCAD'])), PDO::PARAM_INT);
            $sql .= " $where SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('NUMRATA', $filtri) && $filtri['NUMRATA'] != null) {
            $this->addSqlParam($sqlParams, "NUMRATA", strtoupper(trim($filtri['NUMRATA'])), PDO::PARAM_INT);
            $sql .= " $where NUMRATA=:NUMRATA";
            $where = 'AND';
        }
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('PROGSOGG', $filtri) && $filtri['PROGSOGG'] != null) {
            $this->addSqlParam($sqlParams, "PROGSOGG", $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where PROGSOGG=:PROGSOGG";
            $where = 'AND';
        }
        if (array_key_exists('STATO', $filtri) && $filtri['STATO'] != null) {
            $this->addSqlParam($sqlParams, "STATO", $filtri['STATO'], PDO::PARAM_INT);
            $sql .= " $where STATO=:STATO";
            $where = 'AND';
        }
        if (array_key_exists('STATO_DIVERSO', $filtri) && $filtri['STATO_DIVERSO'] != null) {
            $this->addSqlParam($sqlParams, "STATO_DIVERSO", $filtri['STATO_DIVERSO'], PDO::PARAM_INT);
            $sql .= " $where STATO!=:STATO_DIVERSO";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_PUBBL_DIVERSO', $filtri) && $filtri['FLAG_PUBBL_DIVERSO'] != null) {
            $this->addSqlParam($sqlParams, "FLAG_PUBBL_DIVERSO", $filtri['FLAG_PUBBL_DIVERSO'], PDO::PARAM_INT);
            $sql .= " $where FLAG_PUBBL!=:FLAG_PUBBL_DIVERSO";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_PUBBL', $filtri) && $filtri['FLAG_PUBBL'] != null) {
            $this->addSqlParam($sqlParams, "FLAG_PUBBL", $filtri['FLAG_PUBBL'], PDO::PARAM_INT);
            $sql .= " $where FLAG_PUBBL=:FLAG_PUBBL";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_PUBBL_NOTIN', $filtri) && $filtri['FLAG_PUBBL_NOTIN'] != null) {
            $sql .= " $where FLAG_PUBBL NOT IN(" . implode(", ", $filtri['FLAG_PUBBL_NOTIN']) . ")";
            $where = 'AND';
        }
        return $sql;
    }

    public function getSqlLeggiBwePenden($filtri, $excludeOrderBy = false, &$sqlParams) {
 $sql = "SELECT PROGKEYTAB,"
                . "CODTIPSCAD,"
                . "SUBTIPSCAD,"
                . "DESCRPEND, "
                . "TIPOPENDEN,"
                . "MODPROVEN,"
                . "PROGSOGG,"
                . "ANNORIF,"
                . "PROGCITYSC,"
                . "NUMRATA,"
                . "NUMDOC,"
                . "DATACREAZ,"
                . "DATAULTMOD,"
                . "DATASTAMPA,"
                . "DATANOTIF,"
                . "DATASCADE,"
                . "IMPDAPAGTO,"
                . "IMPPAGTOT,"
                . "DATAPAG,"
                . "MODPAGAM,"
                . "FORMATODOC, "
                . "CODOTTBOLL, "
                . "STATO, "
                . "DESSTATO, "
                . "FDETTAGLIO, "
                . "FDOCUMPDF, "
                . "FSTAMPABOL, "
                . "FSTAMPAF24, "
                . "FPAGONLINE, "
                . "NOTA, "
                . "DATAINSER, "
                . "CODUTE, "
                . "DATAOPER, "
                . "TIMEOPER, "
                . "PATHDOC, "
                . "FLAG_PUBBL, "
                . "PROGCITYSCORI, "
                . "ANNOEMI, "
                . "NUMEMI, "
                . "IDBOL_SERE, "
                . "PROGCITYSCA, "
                . "PROGSCITYSCORIA "
                . "FROM BWE_PENDEN ";
        $sql .= $this->addBwePendenParams($filtri, $sqlParams);
        $sql .= ' order by progkeytab';
        return $sql;
    }

    public function leggiBwePenden($filtri, $flMultipla = true, &$sqlParams = array(), $showBinary = true) {
        if ($showBinary) {
            $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BWE", "leggiBwePendenBinary");
            return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBwePenden($filtri, true, $sqlParams), $flMultipla, $sqlParams, $infoBinaryCallback);
        } else {
            return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBwePenden($filtri, true, $sqlParams), $flMultipla, $sqlParams);
        }
    }

    public function leggiBwePendenBinary($result = array()) {
        $sql = "SELECT " . $this->getCitywareDB()->adapterBlob("DOCBINARY") . " FROM BWE_PENDEN WHERE PROGKEYTAB = :PROGKEYTAB";
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "PROGKEYTAB", $result['PROGKEYTAB'], PDO::PARAM_INT);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "DOCBINARY", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['DOCBINARY'] = $resultBin['DOCBINARY'];

        return $result;
    }

    public function leggiBwePendenChiave($progkeytab) {
        if (!$progkeytab) {
            return null;
        }
        $filtri = array();
        $filtri['PROGKEYTAB'] = $progkeytab;
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBwePenden($filtri, true, $sqlParams), false, $sqlParams);
    }

    // Lettura tabella BWE_PENDDET
    public function getSqlLeggiBwePenddet($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BWE_PENDDET.* FROM BWE_PENDDET";
        $where = 'WHERE';
        if (array_key_exists('IDPENDEN', $filtri) && $filtri['IDPENDEN'] != null) {
            $this->addSqlParam($sqlParams, "IDPENDEN", strtoupper(trim($filtri['IDPENDEN'])), PDO::PARAM_INT);
            $sql .= " $where IDPENDEN=:IDPENDEN";
            $where = 'AND';
        }
        $sql .= ' order by progkeytab';
        return $sql;
    }

    public function leggiBwePenddet($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBwePenddet($filtri, true, $sqlParams), $flMultipla, $sqlParams);
    }

    public function getSqlLeggiBwePendenCountRate($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT count(*) as COUNT FROM BWE_PENDEN";
        $where = 'WHERE';
        if (array_key_exists('ANNORIF', $filtri) && $filtri['ANNORIF'] != null) {
            $this->addSqlParam($sqlParams, "ANNORIF", strtoupper(trim($filtri['ANNORIF'])), PDO::PARAM_INT);
            $sql .= " $where ANNORIF=:ANNORIF";
            $where = 'AND';
        }
        if (array_key_exists('PROGCITYSC', $filtri) && $filtri['PROGCITYSC'] != null) {
            $this->addSqlParam($sqlParams, "PROGCITYSC", strtoupper(trim($filtri['PROGCITYSC'])), PDO::PARAM_INT);
            $sql .= " $where PROGCITYSC=:PROGCITYSC";
            $where = 'AND';
        }
        if (array_key_exists('TIPOPENDEN', $filtri) && $filtri['TIPOPENDEN'] != null) {
            $this->addSqlParam($sqlParams, "TIPOPENDEN", strtoupper(trim($filtri['TIPOPENDEN'])), PDO::PARAM_INT);
            $sql .= " $where TIPOPENDEN=:TIPOPENDEN";
            $where = 'AND';
        }
        $sql .= ' and IMPDAPAGTO>IMPPAGTOT';
        return $sql;
    }

    public function leggiBwePendenCountRate($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBwePendenCountRate($filtri, false, $sqlParams), false, $sqlParams);
    }

    // BWE_TIPPEN

    /**
     * Restituisce comando sql per lettura tabella BWE_TIPPEN
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBweTippenPPA($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT  BWE_BNKSRV.*,BWE_TIPPEN.* FROM BWE_TIPPEN 
        INNER JOIN BWE_TIPBNK ON BWE_TIPPEN.CODTIPSCAD=BWE_TIPBNK.CODTIPSCAD
        INNER JOIN BWE_BNKSRV ON BWE_BNKSRV.IDBNKSRV=BWE_TIPBNK.IDBNKSRV ";
        $where = ' WHERE ';
        if (array_key_exists('IDTIPPEN', $filtri) && $filtri['IDTIPPEN'] != null) {
            $this->addSqlParam($sqlParams, "IDTIPPEN", $filtri['IDTIPPEN'], PDO::PARAM_INT);
            $sql .= " $where BWE_TIPPEN.IDTIPPEN=:IDTIPPEN";
            $where = 'AND';
        }
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", $filtri['CODTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where BWE_TIPPEN.CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", $filtri['SUBTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where BWE_TIPPEN.SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('DESCRIZ', $filtri) && $filtri['DESCRIZ'] != null) {
            $this->addSqlParam($sqlParams, "DESCRIZ", "%" . strtoupper(trim($filtri['DESCRIZ'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BWE_TIPPEN.DESCRIZ") . " LIKE :DESCRIZ";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY BWE_TIPPEN.CODTIPSCAD,BWE_TIPPEN.SUBTIPSCAD ASC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_TIPPEN
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBweTippenPPA($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweTippenPPA($filtri, false, $sqlParams), $flMultipla, $sqlParams);
    }

    // BWE_TIPPEN

    /**
     * Restituisce comando sql per lettura tabella BWE_TIPPEN
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBweTippen($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BWE_TIPPEN.* FROM BWE_TIPPEN";
        $where = 'WHERE';
        if (array_key_exists('IDTIPPEN', $filtri) && $filtri['IDTIPPEN'] != null) {
            $this->addSqlParam($sqlParams, "IDTIPPEN", $filtri['IDTIPPEN'], PDO::PARAM_INT);
            $sql .= " $where IDTIPPEN=:IDTIPPEN";
            $where = 'AND';
        }
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", $filtri['CODTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", $filtri['SUBTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('DESCRIZ', $filtri) && $filtri['DESCRIZ'] != null) {
            $this->addSqlParam($sqlParams, "DESCRIZ", "%" . strtoupper(trim($filtri['DESCRIZ'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESCRIZ") . " LIKE :DESCRIZ";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODTIPSCAD,SUBTIPSCAD ASC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_TIPPEN
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBweTippen($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweTippen($filtri, false, $sqlParams), $flMultipla, $sqlParams);
    }

    /**
     * Restituisce dati tabella BWE_TIPPEN per chiave
     * @param string $annoemi Anno
     * @param string $cod_nr_d Codice 
     * @param string $sett_iva Settore IVA 
     */
    public function leggiBweTippenChiave($idtippen) {
        if (!$idtippen) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweTippenChiave($idtippen, $sqlParams), false, $sqlParams);
    }

    public function getSqlLeggiBweTippenChiave($index, &$sqlParams = array()) {
        $filtri = array();
        $filtri['IDTIPPEN'] = $index;
        return self::getSqlLeggiBweTippen($filtri, true, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BWE_PARAM
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBweParam() {
        $sql = "SELECT BWE_PARAM.* FROM BWE_PARAM";

        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_PARAM
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBweParam() {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweParam(), false, $sqlParams);
    }

    // Lettura per Riconciliazione E-Fil

    /**
     * Restituisce comando sql per lettura tabella BWE_PENDEN
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBwePendenPerRiconciliazione($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT bwe_penden.progkeytab, bwe_penden.codtipscad, bwe_penden.subtipscad, bwe_penden.descrpend, bwe_penden.tipopenden, bwe_penden.modproven, bwe_penden.progsogg,
            bwe_penden.annorif, bwe_penden.progcitysc, bwe_penden.numrata, bwe_penden.numdoc, bwe_penden.datacreaz, bwe_penden.dataultmod, bwe_penden.datastampa, 
               bwe_penden.datanotif, bwe_penden.datascade, bwe_penden.impdapagto, bwe_penden.imppagtot, bwe_penden.datapag, bwe_penden.modpagam, 
               bwe_penden.formatodoc, bwe_penden.codottboll, bwe_penden.stato, bwe_penden.desstato,
               bwe_penden.fdettaglio, bwe_penden.fdocumpdf, bwe_penden.fstampabol, bwe_penden.fstampaf24,
               bwe_penden.fpagonline, bwe_penden.nota, bwe_penden.pathdoc, bwe_penden.flag_pubbl, bwe_penden.progcityscori, 
               bwe_penden.annoemi, bwe_penden.numemi, bwe_penden.idbol_sere,
                bge_agid_risco.imppagato, bge_agid_risco.datapag as DATARISCO, bge_agid_risco_efil.canalepag,
                bge_agid_risco_efil.datareg, bge_agid_risco_efil.datavers, bge_agid_risco_efil.datarego, bge_agid_risco_efil.idflusso, bge_agid_scadenze.iuv
                FROM
                bwe_penden inner join bge_agid_scadenze on BGE_AGID_SCADENZE.CODTIPSCAD = BWE_PENDEN.CODTIPSCAD AND 
                BGE_AGID_SCADENZE.SUBTIPSCAD = BWE_PENDEN.SUBTIPSCAD AND BGE_AGID_SCADENZE.PROGCITYSC = BWE_PENDEN.PROGCITYSC AND
                BGE_AGID_SCADENZE.ANNORIF = BWE_PENDEN.ANNORIF AND BGE_AGID_SCADENZE.NUMRATA = BWE_PENDEN.NUMRATA
                inner join bge_agid_risco on bge_agid_scadenze.progkeytab = bge_agid_risco.idscadenza
                inner join bge_agid_risco_efil on bge_agid_risco_efil.progkeytab = bge_agid_risco.progkeytab
                WHERE bge_agid_scadenze.STATO = 10 or bge_agid_scadenze.STATO =11";
        $sql .= $excludeOrderBy ? '' : ' ORDER BY BWE_PENDEN.PROGKEYTAB';
        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_AGID_LOG
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBwePendenPerRiconciliazione($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBwePendenPerRiconciliazione($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    // BWE_PENDEN

    /**
     * Restituisce comando sql per lettura tabella BWE_PENDEN per verifica emissione in join con la BTA_SERVREND
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBwePendenBtaServrend($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT * FROM BWE_PENDEN 
                INNER JOIN BTA_SERVREND ON BTA_SERVREND.CODTIPSCAD = BWE_PENDEN.CODTIPSCAD AND
                BTA_SERVREND.SUBTIPSCAD = BWE_PENDEN.SUBTIPSCAD AND BTA_SERVREND.ANNOEMI = BWE_PENDEN.ANNOEMI AND
                BTA_SERVREND.NUMEMI = BWE_PENDEN.NUMEMI AND BTA_SERVREND.IDBOL_SERE = BWE_PENDEN.IDBOL_SERE";
        $where = 'WHERE';
        if (array_key_exists('PROGCITYSC', $filtri) && $filtri['PROGCITYSC'] != null) {
            $this->addSqlParam($sqlParams, "PROGCITYSC", $filtri['PROGCITYSC'], PDO::PARAM_INT);
            $sql .= " $where BWE_PENDEN.PROGCITYSC=:PROGCITYSC";
            $where = 'AND';
        }
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", $filtri['CODTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where BWE_PENDEN.CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", $filtri['SUBTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where BWE_PENDEN.SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('ANNORIF', $filtri) && $filtri['ANNORIF'] != null) {
            $this->addSqlParam($sqlParams, "ANNORIF", $filtri['ANNORIF'], PDO::PARAM_INT);
            $sql .= " $where BWE_PENDEN.ANNORIF=:ANNORIF";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY BWE_PENDEN.CODTIPSCAD,BWE_PENDEN.SUBTIPSCAD ASC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_TIPPEN
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBwePendenBtaServrend($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBwePendenBtaServrend($filtri, false, $sqlParams), $flMultipla, $sqlParams);
    }

    // BWE_PENDEN_NSS

    /**
     * Restituisce comando sql per lettura tabella BWE_PENDEN_NSS
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBwePendenNss($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BWE_PENDEN_NSS.* FROM BWE_PENDEN_NSS";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_PENDEN_NSS
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBwePendenNss($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBwePendenNss($filtri, true, $sqlParams), $flMultipla, $sqlParams);
    }

    // BWE_PENDDETIVA

    /**
     * Restituisce comando sql per lettura tabella BWE_PENDDETIVA
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBwePenddetiva($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BWE_PENDDETIVA.* FROM BWE_PENDDETIVA";
        $where = 'WHERE';
        if (array_key_exists('IDPENDEN', $filtri) && $filtri['IDPENDEN'] != null) {
            $this->addSqlParam($sqlParams, "IDPENDEN", $filtri['IDPENDEN'], PDO::PARAM_INT);
            $sql .= " $where IDPENDEN=:IDPENDEN";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_PENDDETIVA
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBwePenddetiva($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBwePenddetiva($filtri, true, $sqlParams), $flMultipla, $sqlParams);
    }

    public function leggiBweRecLck($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqLeggiBweRecLck($filtri, false, $sqlParams), $flMultipla, $sqlParams);
    }

    public function getSqLeggiBweRecLck($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT * FROM BWE_RECLCK";
        $where = 'WHERE';
        if (array_key_exists('ID_LOCK', $filtri) && $filtri['ID_LOCK'] != null) {
            $this->addSqlParam($sqlParams, "ID_LOCK", $filtri['ID_LOCK'], PDO::PARAM_INT);
            $sql .= " $where BWE_RECLCK.ID_LOCK=:ID_LOCK";
            $where = 'AND';
        }
        if (array_key_exists('ID_RECORD', $filtri) && $filtri['ID_RECORD'] != null) {
            $this->addSqlParam($sqlParams, "ID_RECORD", $filtri['ID_RECORD'], PDO::PARAM_STR);
            $sql .= " $where BWE_RECLCK.ID_RECORD=:ID_RECORD";
            $where = 'AND';
        }
        if (array_key_exists('CONN_ID_DIV', $filtri) && $filtri['CONN_ID_DIV'] != null) {
            $this->addSqlParam($sqlParams, "CONN_ID_DIV", $filtri['CONN_ID_DIV'], PDO::PARAM_STR);
            $sql .= " $where BWE_RECLCK.CONN_ID<>:CONN_ID_DIV";
            $where = 'AND';
        }
        if (array_key_exists('CONN_ID', $filtri) && $filtri['CONN_ID'] != null) {
            $this->addSqlParam($sqlParams, "CONN_ID", $filtri['CONN_ID'], PDO::PARAM_STR);
            $sql .= " $where BWE_RECLCK.CONN_ID=:CONN_ID";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY BWE_RECLCK.ID_LOCK ASC';
        return $sql;
    }

    public function getSqlLeggiBweRecLckChiave($idLock, &$sqlParams = array()) {
        $filtri = array();
        $filtri['ID_LOCK'] = $idLock;
        return self::getSqLeggiBweRecLck($filtri, true, $sqlParams);
    }

    public function leggiBweRecLckChiave($idLock) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweRecLckChiave($idLock, $sqlParams), false, $sqlParams);
    }

    // BWE_PENDDET

    /**
     * Restituisce comando sql per lettura tabella BWE_PENDDET partendo da BGE_AGID_SCADENZE
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBwePenddetScadenze($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BWE_PENDDET .* FROM BWE_PENDEN INNER JOIN BGE_AGID_SCADENZE ON BGE_AGID_SCADENZE.CODTIPSCAD = BWE_PENDEN.CODTIPSCAD
            AND BGE_AGID_SCADENZE.SUBTIPSCAD = BWE_PENDEN.SUBTIPSCAD AND BGE_AGID_SCADENZE.PROGCITYSC = BWE_PENDEN.PROGCITYSC AND 
            BGE_AGID_SCADENZE.ANNORIF = BWE_PENDEN.ANNORIF AND BGE_AGID_SCADENZE.NUMRATA = BWE_PENDEN.NUMRATA
            INNER JOIN BWE_PENDDET ON BWE_PENDDET.IDPENDEN=BWE_PENDEN.PROGKEYTAB";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_SCADENZE.PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_PENDDET
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBwePenddetScadenze($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBwePenddetScadenze($filtri, true, $sqlParams), $flMultipla, $sqlParams);
    }

    // BWE_PENDDETIVA

    /**
     * Restituisce comando sql per lettura tabella BWE_PENDDET partendo da BWE_PENDDETIVA
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBwePenddetivaScadenze($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BWE_PENDDETIVA .* FROM BWE_PENDEN INNER JOIN BGE_AGID_SCADENZE ON BGE_AGID_SCADENZE.CODTIPSCAD = BWE_PENDEN.CODTIPSCAD
            AND BGE_AGID_SCADENZE.SUBTIPSCAD = BWE_PENDEN.SUBTIPSCAD AND BGE_AGID_SCADENZE.PROGCITYSC = BWE_PENDEN.PROGCITYSC AND 
            BGE_AGID_SCADENZE.ANNORIF = BWE_PENDEN.ANNORIF AND BGE_AGID_SCADENZE.NUMRATA = BWE_PENDEN.NUMRATA
            INNER JOIN BWE_PENDDETIVA ON BWE_PENDDETIVA.IDPENDEN=BWE_PENDEN.PROGKEYTAB";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where BGE_AGID_SCADENZE.PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_PENDDETIVA
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBwePenddetivaScadenze($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBwePenddetivaScadenze($filtri, true, $sqlParams), $flMultipla, $sqlParams);
    }

    // BWE_UTENTI

    /**
     * Restituisce comando sql per lettura tabella BWE_UTENTI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBweUtenti($filtri, $excludeOrderBy = false, &$sqlParams = array(), $vediTipoutente = false) {
        $sql = "SELECT BWE_UTENTI.* ";
        if ($vediTipoutente) {
            $sql .= " ,COALESCE(BWE_TIPUTE.DESCRTIPO,BWE_TIPUTE.DESCRTIPO,'GENERICO') DESCRTIPO ";
        }
        $sql .= " FROM BWE_UTENTI";
        if ($vediTipoutente) {
            $sql .= " LEFT JOIN BWE_TIPUTE ON BWE_UTENTI.IDTIPOUTE=BWE_TIPUTE.ID ";
        }
        $where = 'WHERE';
        if (array_key_exists('PROGSOGWEB', $filtri) && $filtri['PROGSOGWEB'] != null) {
            $this->addSqlParam($sqlParams, "PROGSOGWEB", $filtri['PROGSOGWEB'], PDO::PARAM_INT);
            $sql .= " $where PROGSOGWEB=:PROGSOGWEB";
            $where = 'AND';
        }
        if (array_key_exists('DATASCACONV', $filtri) && $filtri['DATASCACONV'] != null) {
            $this->addSqlParam($sqlParams, "DATASCACONV", $filtri['DATASCACONV'], PDO::PARAM_STR);
            $sql .= " $where DATASCACONV = :DATASCACONV";
            $where = 'AND';
        }
        if (array_key_exists('DATASCACONV_MIN', $filtri) && $filtri['DATASCACONV_MIN'] != null) {
            $this->addSqlParam($sqlParams, "DATASCACONVMIN", $filtri['DATASCACONV_MIN'], PDO::PARAM_STR);
            $sql .= " $where DATASCACONV <= :DATASCACONVMIN";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGSOGWEB ';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_UTENTI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBweUtenti($filtri, $flMultipla = true, $vediTipoutente = false) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweUtenti($filtri, false, $sqlParams, $vediTipoutente), $flMultipla, $sqlParams);
    }

    /**
     * Restituisce dati tabella BWE_UTENTI per chiave
     * @param string $annoemi Anno
     * @param string $cod_nr_d Codice 
     * @param string $sett_iva Settore IVA 
     */
    public function leggiBweUtentiChiave($progsogweb, $vediTipoutente = false) {
        if (!$progsogweb) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweUtentiChiave($progsogweb, $sqlParams, $vediTipoutente), false, $sqlParams);
    }

    public function getSqlLeggiBweUtentiChiave($index, &$sqlParams = array(), $vediTipoutente = false) {
        $filtri = array();
        $filtri['PROGSOGWEB'] = $index;
        return self::getSqlLeggiBweUtenti($filtri, true, $sqlParams, $vediTipoutente);
    }

    // BWE_UTELOG_CONF

    /**
     * Restituisce comando sql per lettura tabella BWE_UTELOG_CONF
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBweUtelogConf($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BWE_UTELOG_CONF.* FROM BWE_UTELOG_CONF";
        $where = 'WHERE';
        if (array_key_exists('ID', $filtri) && $filtri['ID'] != null) {
            $this->addSqlParam($sqlParams, "ID", $filtri['ID'], PDO::PARAM_INT);
            $sql .= " $where ID=:ID";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY ID ';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_UTELOG_CONF
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBweUtelogConf($filtri, $flMultipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweUtelogConf($filtri, false, $sqlParams), $flMultipla, $sqlParams);
    }

    /**
     * Restituisce dati tabella BWE_UTELOG_CONF per chiave
     * @param string $annoemi Anno
     * @param string $cod_nr_d Codice 
     * @param string $sett_iva Settore IVA 
     */
    public function leggiBweUtelogConfChiave($idtippen) {
        if (!$idtippen) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweUtelogConfChiave($idtippen, $sqlParams), false, $sqlParams);
    }

    public function getSqlLeggiBweUtelogConfChiave($index, &$sqlParams = array()) {
        $filtri = array();
        $filtri['ID'] = $index;
        return self::getSqlLeggiBweUtelogConf($filtri, true, $sqlParams);
    }

    // BWE_UTELOG

    /**
     * Restituisce comando sql per lettura tabella BWE_UTELOG
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBweUtelog($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BWE_UTELOG.* FROM BWE_UTELOG";
        $where = 'WHERE';
        if (array_key_exists('ID', $filtri) && $filtri['ID'] != null) {
            $this->addSqlParam($sqlParams, "ID", $filtri['ID'], PDO::PARAM_INT);
            $sql .= " $where ID=:ID";
            $where = 'AND';
        }
        if (array_key_exists('DATAOPER', $filtri) && $filtri['DATAOPER'] != null) {
            $this->addSqlParam($sqlParams, "DATAOPER", $filtri['DATAOPER'], PDO::PARAM_STR);
            $sql .= " $where DATAOPER=:DATAOPER";
            $where = 'AND';
        }
        if (array_key_exists('ESITO', $filtri) && $filtri['ESITO'] != null) {
            $this->addSqlParam($sqlParams, "ESITO", $filtri['ESITO'], PDO::PARAM_INT);
            $sql .= " $where ESITO=:ESITO";
            $where = 'AND';
        }
        if (array_key_exists('TIPO_OPERAZIONE', $filtri) && $filtri['TIPO_OPERAZIONE'] != null) {
            $this->addSqlParam($sqlParams, "TIPO_OPERAZIONE", $filtri['TIPO_OPERAZIONE'], PDO::PARAM_INT);
            $sql .= " $where TIPO_OPERAZIONE=:TIPO_OPERAZIONE";
            $where = 'AND';
        }
        if (array_key_exists('DATAOPER_MAGG', $filtri) && $filtri['DATAOPER_MAGG'] != null) {
            $this->addSqlParam($sqlParams, "DATAOPER_MAGG", $filtri['DATAOPER_MAGG'], PDO::PARAM_STR);
            $sql .= " $where DATAOPER>=:DATAOPER_MAGG";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY ID ';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_UTELOG
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBweUtelog($filtri, $flMultipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweUtelog($filtri, false, $sqlParams), $flMultipla, $sqlParams);
    }

    /**
     * Restituisce dati tabella BWE_UTELOG per chiave
     * @param string $annoemi Anno
     * @param string $cod_nr_d Codice 
     * @param string $sett_iva Settore IVA 
     */
    public function leggiBweUtelogChiave($idtippen) {
        if (!$idtippen) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweUtelogChiave($idtippen, $sqlParams), false, $sqlParams);
    }

    public function getSqlLeggiBweUtelogChiave($index, &$sqlParams = array()) {
        $filtri = array();
        $filtri['ID'] = $index;
        return self::getSqlLeggiBweUtelog($filtri, true, $sqlParams);
    }

    // BWE_TPRA

    /**
     * Restituisce comando sql per lettura tabella BWE_TPRA
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBweTpraPraticheDaPrendereCarico($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT p.*,t.DESCRIZ DESCRIZ_PRAT,t.OPERAZ,s.COGNOME,s.NOME,s.CODFISCALE FROM BWE_TPRA p "
                . " LEFT JOIN BTA_SOGG s ON s.PROGSOGG=p.PROGSOGWEB "
                . " LEFT JOIN TIW_PRAT_TRIB t ON p.SUBTIPOPRATW=t.IDTIPPRA "
                . " WHERE TIPOPRATW=210 AND STATO=0 ";

        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_TPRA
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBweTpraPraticheDaPrendereCarico($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweTpraPraticheDaPrendereCarico($filtri, true, $sqlParams), $flMultipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BWE_TPRA
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBweTpra($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BWE_TPRA.* FROM BWE_TPRA";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('TIPOPRATW', $filtri) && $filtri['TIPOPRATW'] != null) {
            $this->addSqlParam($sqlParams, "TIPOPRATW", $filtri['TIPOPRATW'], PDO::PARAM_INT);
            $sql .= " $where TIPOPRATW=:TIPOPRATW";
            $where = 'AND';
        }
        if (array_key_exists('PROGSOGWEB', $filtri) && $filtri['PROGSOGWEB'] != null) {
            $this->addSqlParam($sqlParams, "PROGSOGWEB", $filtri['PROGSOGWEB'], PDO::PARAM_INT);
            $sql .= " $where PROGSOGWEB=:PROGSOGWEB";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB DESC ';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_TPRA
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBweTpra($filtri, $flMultipla = true) {
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BWE", "leggiBweTpraBinary");
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweTpra($filtri, false, $sqlParams), $flMultipla, $sqlParams, $infoBinaryCallback);
    }

    /**
     * Restituisce dati tabella BWE_TPRA per chiave
     * @param string $progkeytab    
     */
    public function leggiBweTpraChiave($progkeytab) {
        if (!$progkeytab) {
            return null;
        }
        $filtri = array(
            "PROGKEYTAB" => $progkeytab
        );
        return $this->leggiBweTpra($filtri, false);
    }

    public function leggiBweTpraBinary($result = array()) {
        $sql = "SELECT " . $this->getCitywareDB()->adapterBlob("XML") . " FROM BWE_TPRA WHERE PROGKEYTAB = :PROGKEYTAB";
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "PROGKEYTAB", $result['PROGKEYTAB'], PDO::PARAM_INT);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "XML", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['XML'] = $resultBin['XML'];

        return $result;
    }

    // BWE_DPRA

    /**
     * Restituisce comando sql per lettura tabella BWE_DPRA
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBweDpra($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BWE_DPRA.* FROM BWE_DPRA";
        $where = 'WHERE';
        if (array_key_exists('PROGKEYTAB', $filtri) && $filtri['PROGKEYTAB'] != null) {
            $this->addSqlParam($sqlParams, "PROGKEYTAB", $filtri['PROGKEYTAB'], PDO::PARAM_INT);
            $sql .= " $where PROGKEYTAB=:PROGKEYTAB";
            $where = 'AND';
        }
        if (array_key_exists('PKTESTATA', $filtri) && $filtri['PKTESTATA'] != null) {
            $this->addSqlParam($sqlParams, "PKTESTATA", $filtri['PKTESTATA'], PDO::PARAM_INT);
            $sql .= " $where PKTESTATA=:PKTESTATA";
            $where = 'AND';
        }
        if (array_key_exists('TIPO', $filtri) && $filtri['TIPO'] != null) {
            $this->addSqlParam($sqlParams, "TIPO", $filtri['TIPO'], PDO::PARAM_INT);
            $sql .= " $where TIPO=:TIPO";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGKEYTAB DESC ';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_DPRA
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBweDpra($filtri, $flMultipla = true) {
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BWE", "leggiBweDpraBinary");
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweDpra($filtri, false, $sqlParams), $flMultipla, $sqlParams, $infoBinaryCallback);
    }

    /**
     * Restituisce dati tabella BWE_DPRA per chiave
     * @param string $progkeytab    
     */
    public function leggiBweDpraChiave($progkeytab) {
        if (!$progkeytab) {
            return null;
        }
        $filtri = array(
            "PROGKEYTAB" => $progkeytab
        );
        return $this->leggiBweDpra($filtri, false);
    }

    public function leggiBweDpraBinary($result = array()) {
        $sql = "SELECT " . $this->getCitywareDB()->adapterBlob("BLOBDATA") . " FROM BWE_DPRA WHERE PROGKEYTAB = :PROGKEYTAB";
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "PROGKEYTAB", $result['PROGKEYTAB'], PDO::PARAM_INT);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "BLOBDATA", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['BLOBDATA'] = $resultBin['BLOBDATA'];

        return $result;
    }

    // BWE_AUTUTE

    /**
     * Restituisce comando sql per lettura tabella BWE_AUTUTE
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBweAutute($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BWE_AUTUTE.* FROM BWE_AUTUTE";
        $where = 'WHERE';
        if (array_key_exists('IDAUTUTE', $filtri) && $filtri['IDAUTUTE'] != null) {
            $this->addSqlParam($sqlParams, "IDAUTUTE", $filtri['IDAUTUTE'], PDO::PARAM_INT);
            $sql .= " $where IDAUTUTE=:IDAUTUTE";
            $where = 'AND';
        }
        if (array_key_exists('PROGSOGG', $filtri) && $filtri['PROGSOGG'] != null) {
            $this->addSqlParam($sqlParams, "PROGSOGG", $filtri['PROGSOGG'], PDO::PARAM_INT);
            $sql .= " $where PROGSOGG=:PROGSOGG";
            $where = 'AND';
        }
        if (array_key_exists('PROGSOGWEB', $filtri) && $filtri['PROGSOGWEB'] != null) {
            $this->addSqlParam($sqlParams, "PROGSOGWEB", $filtri['PROGSOGWEB'], PDO::PARAM_INT);
            $sql .= " $where PROGSOGWEB=:PROGSOGWEB";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDAUTUTE';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_AUTUTE
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBweAutute($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweAutute($filtri, false, $sqlParams), $flMultipla, $sqlParams);
    }

    /**
     * Restituisce dati tabella BWE_AUTUTE per chiave
     * @param string $annoemi Anno
     * @param string $cod_nr_d Codice 
     * @param string $sett_iva Settore IVA 
     */
    public function leggiBweAututeChiave($idtippen) {
        if (!$idtippen) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweAututeChiave($idtippen, $sqlParams), false, $sqlParams);
    }

    public function getSqlLeggiBweAututeChiave($index, &$sqlParams = array()) {
        $filtri = array();
        $filtri['IDAUTUTE'] = $index;
        return self::getSqlLeggiBweAutute($filtri, true, $sqlParams);
    }

    // BWE_BNKSRV

    /**
     * Restituisce comando sql per lettura tabella BWE_BNKSRV
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBweBnksrv($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BWE_BNKSRV.* FROM BWE_BNKSRV";
        $where = 'WHERE';
        if (array_key_exists('IDBNKSRV', $filtri) && $filtri['IDBNKSRV'] != null) {
            $this->addSqlParam($sqlParams, "IDBNKSRV", $filtri['IDBNKSRV'], PDO::PARAM_INT);
            $sql .= " $where IDBNKSRV=:IDBNKSRV";
            $where = 'AND';
        }
        if (array_key_exists('DESCRBANCA', $filtri) && $filtri['DESCRBANCA'] != null) {
            $this->addSqlParam($sqlParams, "DESCRBANCA", "%" . strtoupper(trim($filtri['DESCRBANCA'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESCRBANCA") . " LIKE :DESCRBANCA";
            $where = 'AND';
        }
        if (array_key_exists('NOMESERVIC', $filtri) && $filtri['NOMESERVIC'] != null) {
            $this->addSqlParam($sqlParams, "NOMESERVIC", "%" . strtoupper(trim($filtri['NOMESERVIC'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("NOMESERVIC") . " LIKE :NOMESERVIC";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDBNKSRV asc';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_TIPPEN
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBweBnksrv($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweBnksrv($filtri, false, $sqlParams), $flMultipla, $sqlParams);
    }

    /**
     * Restituisce dati tabella BWE_TIPPEN per chiave
     * @param string $annoemi Anno
     * @param string $cod_nr_d Codice 
     * @param string $sett_iva Settore IVA 
     */
    public function leggiBweBnksrvChiave($idbnksrv) {
        if (!$idbnksrv) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweBnksrvChiave($idbnksrv, $sqlParams), false, $sqlParams);
    }

    public function getSqlLeggiBweBnksrvChiave($index, &$sqlParams = array()) {
        $filtri = array();
        $filtri['IDBNKSRV'] = $index;
        return self::getSqlLeggiBweBnksrv($filtri, true, $sqlParams);
    }

    // BWE_TIPBNK

    /**
     * Restituisce comando sql per lettura tabella BWE_TIPBNK
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBweTipbnk($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sql = "SELECT BWE_TIPBNK.* FROM BWE_TIPBNK";
        $where = 'WHERE';
        if (array_key_exists('IDBNKSRV', $filtri) && $filtri['IDBNKSRV'] != null) {
            $this->addSqlParam($sqlParams, "IDBNKSRV", $filtri['IDBNKSRV'], PDO::PARAM_INT);
            $sql .= " $where IDBNKSRV=:IDBNKSRV";
            $where = 'AND';
        }
        if (array_key_exists('CODTIPSCAD', $filtri) && $filtri['CODTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "CODTIPSCAD", $filtri['CODTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where CODTIPSCAD=:CODTIPSCAD";
            $where = 'AND';
        }
        if (array_key_exists('SUBTIPSCAD', $filtri) && $filtri['SUBTIPSCAD'] != null) {
            $this->addSqlParam($sqlParams, "SUBTIPSCAD", $filtri['SUBTIPSCAD'], PDO::PARAM_INT);
            $sql .= " $where SUBTIPSCAD=:SUBTIPSCAD";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODTIPSCAD,SUBTIPSCAD ASC';
        return $sql;
    }

    /**
     * Restituisce dati tabella BWE_TIPBNK
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBweTipbnk($filtri, $flMultipla = true, &$sqlParams = array()) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBweTipbnk($filtri, false, $sqlParams), $flMultipla, $sqlParams);
    }

}

?>