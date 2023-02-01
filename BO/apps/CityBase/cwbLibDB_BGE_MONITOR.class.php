<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_CITYWARE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBgeMonitorHelper.class.php';

// tabella BGE_LOG su db monitor (10.0.0.10 MONITOR)
class cwbLibDB_BGE_MONITOR extends cwbLibDB_CITYWARE {

    const CONNECTION_NAME = 'CW_MONITOR_CLIENTI';

    private $MONITOR_DB;

    /**
     * Restituisce comando sql per lettura tabella BGE_LOG su db monitor
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeLog($filters, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGE_LOG.* FROM BGE_LOG";

        $where = ' WHERE ';
        if (array_key_exists('DESENTE', $filters) && $filters['DESENTE'] != null) {
            $this->addSqlParam($sqlParams, "DESENTE", "%" . strtoupper(trim($filters['DESENTE'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESENTE") . " LIKE :DESENTE";
            $where = ' AND ';
        }
        if (array_key_exists('ENTE', $filters) && $filters['ENTE'] != null) {
            $this->addSqlParam($sqlParams, "ENTE", $filters['ENTE'], PDO::PARAM_INT);
            $sql .= $where . " ENTE = :ENTE ";
            $where = ' AND ';
        }
        if (array_key_exists('FILE_NAME', $filters) && $filters['FILE_NAME'] != null) {
            $this->addSqlParam($sqlParams, "FILE_NAME", "%" . strtoupper(trim($filters['FILE_NAME'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("FILE_NAME") . " LIKE :FILE_NAME";
            $where = ' AND ';
        }
        if (array_key_exists('ID', $filters) && $filters['ID'] != null) {
            $this->addSqlParam($sqlParams, "ID", $filters['ID'], PDO::PARAM_INT);
            $sql .= $where . " ID = :ID ";
            $where = ' AND ';
        }

        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_LOG su db monitor
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeLog($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeLog($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce dati tabella BTA_GRUNAZ per chiave
     * @param array $cod Chiave   
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return object Record
     */
    public function leggiBgeLogChiave($id) {
        if (!$id) {
            return null;
        }

        $filtri = array();
        $filtri['ID'] = $id;
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeLog($filtri, false, $sqlParams), false, $sqlParams);
    }

    public function getSqlLeggiEntiBgeLog($filters, &$sqlParams) {
        $sql = "SELECT DISTINCT BGE_LOG.ENTE,BGE_LOG.DESENTE FROM BGE_LOG";

        $where = 'WHERE';
        if (array_key_exists('DESENTE', $filters) && $filters['DESENTE'] != null) {
            $this->addSqlParam($sqlParams, "DESENTE", "%" . strtoupper(trim($filters['DESENTE'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESENTE") . " LIKE :DESENTE";
            $where = 'AND';
        }

        return $sql;
    }

    public function leggiEntiBgeLog($filters) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiEntiBgeLog($filters, $sqlParams), true, $sqlParams);
    }

    public function getSqlCountScartatiPerEnteBgeLog($ente, &$sqlParams = array()) {
        $sql = "SELECT COUNT(BGE_LOG.*) FROM BGE_LOG WHERE SCARTATI = :SCARTATI AND ENTE = :ENTE ";

        $this->addSqlParam($sqlParams, "SCARTATI", 1, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "ENTE", $ente, PDO::PARAM_INT);

        return $sql;
    }

    public function countScartatiPerEnteBgeLog($ente) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlCountScartatiPerEnteBgeLog($ente, $sqlParams), true, $sqlParams);
    }

    public function getSqlRecordPerEnteBgeLog($ente, $ambito, &$sqlParams = array()) {
        $sql = "SELECT BGE_LOG.* FROM BGE_LOG WHERE ENTE = :ENTE AND AMBITO = :AMBITO ";
        $sql .= " AND  DATA = (SELECT MAX(log2.DATA) FROM BGE_LOG log2 WHERE log2.ENTE = :ENTE AND log2.AMBITO = :AMBITO)";
        $sql .= " AND  ORA = (SELECT MAX(log2.ORA) FROM BGE_LOG log2 WHERE log2.ENTE = :ENTE AND log2.AMBITO = :AMBITO)";

        $this->addSqlParam($sqlParams, "ENTE", $ente, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "AMBITO", $ambito, PDO::PARAM_INT);

        return $sql;
    }

    public function recordPerEnteBgeLog($ente, $ambito) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlRecordPerEnteBgeLog($ente, $ambito, $sqlParams), true, $sqlParams);
    }

    public function getSqlDettaglioSchedulazione($ente, &$sqlParams = array()) {
        $sql = "SELECT log2.* FROM BGE_LOG log2, "
                . "(SELECT log1.ORIGINE,log1.NOMEMETODO,"
                . " MAX("
                . $this->getCitywareDB()->formatDate(
                        $this->getCitywareDB()->strConcat("(" .
                                $this->getCitywareDB()->dateToString('log1.DATA', 'YYYY/MM/DD'), "' '", "log1.ORA") . ")", $this->getCitywareDB()->getFormatDateTime()) . ") as dataora "
                . " FROM BGE_LOG log1 WHERE log1.ENTE = :ENTE AND log1.AMBITO = :AMBITO "
                . " group by log1.origine,log1.nomemetodo ) sottoquery "
                . " where sottoquery.origine = log2.origine and log2.ente = :ENTE2 "
                . " and  sottoquery.nomemetodo = log2.nomemetodo "
                . " and sottoquery.dataora =  "
                . $this->getCitywareDB()->formatDate(
                        $this->getCitywareDB()->strConcat("(" .
                                $this->getCitywareDB()->dateToString('log2.DATA', 'YYYY/MM/DD'), "' '", "log2.ORA") . ")", $this->getCitywareDB()->getFormatDateTime());

        $this->addSqlParam($sqlParams, "ENTE", $ente, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "ENTE2", $ente, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "AMBITO", cwbBgeMonitorHelper::AMBITO_SCHEDULAZIONE, PDO::PARAM_INT);

        return $sql;
    }

    public function dettaglioSchedulazione($ente) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlDettaglioSchedulazione($ente, $sqlParams), true, $sqlParams);
    }

    // torna il record di bge_log relativo alla fatturazione con data e ora maggiore
    public function getSqlDettaglioFatturazione($ente, &$sqlParams = array()) {
        $sql = "SELECT log2.* FROM BGE_LOG log2, "
                . "(SELECT log1.ORIGINE,"
                . " MAX("
                . $this->getCitywareDB()->formatDate(
                        $this->getCitywareDB()->strConcat("(" .
                                $this->getCitywareDB()->dateToString('log1.DATA', 'YYYY/MM/DD'), "' '", "log1.ORA") . ")", $this->getCitywareDB()->getFormatDateTime()) . ") as dataora "
                . " FROM BGE_LOG log1 WHERE log1.ENTE = :ENTE AND log1.AMBITO = :AMBITO "
                . "group by log1.origine) sottoquery "
                . " where sottoquery.origine = log2.origine and log2.ente = :ENTE2 "
                . " and sottoquery.dataora =  "
                . $this->getCitywareDB()->formatDate(
                        $this->getCitywareDB()->strConcat("(" .
                                $this->getCitywareDB()->dateToString('log2.DATA', 'YYYY/MM/DD'), "' '", "log2.ORA") . ")", $this->getCitywareDB()->getFormatDateTime());

        $this->addSqlParam($sqlParams, "ENTE", $ente, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "ENTE2", $ente, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "AMBITO", cwbBgeMonitorHelper::AMBITO_FATTURAZIONE, PDO::PARAM_INT);

        return $sql;
    }

    public function dettaglioFatturazione($ente) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlDettaglioFatturazione($ente, $sqlParams), true, $sqlParams);
    }

    public function getSqlDettaglioPagoPa($ente, &$sqlParams = array()) {
//        $sql = "SELECT log2.* FROM BGE_LOG log2, "
//                . "(SELECT log1.ORIGINE,"
//                . " MAX("
//                . $this->getCitywareDB()->formatDate(
//                        $this->getCitywareDB()->strConcat("(" .
//                                $this->getCitywareDB()->dateToString('log1.DATA', 'YYYY/MM/DD'), "' '", "log1.ORA") . ")", $this->getCitywareDB()->getFormatDateTime()) . ") as dataora "
//                . " FROM BGE_LOG log1 WHERE log1.ENTE = :ENTE AND log1.AMBITO = :AMBITO "
//                . "group by log1.origine) as sottoquery "
//                . " where sottoquery.origine = log2.origine and log2.ente = :ENTE2 "
//                . " and sottoquery.dataora =  "
//                . $this->getCitywareDB()->formatDate(
//                        $this->getCitywareDB()->strConcat("(" .
//                                $this->getCitywareDB()->dateToString('log2.DATA', 'YYYY/MM/DD'), "' '", "log2.ORA") . ")", $this->getCitywareDB()->getFormatDateTime());
//
//        $this->addSqlParam($sqlParams, "ENTE", $ente, PDO::PARAM_INT);
//        $this->addSqlParam($sqlParams, "ENTE2", $ente, PDO::PARAM_INT);
//        $this->addSqlParam($sqlParams, "AMBITO", cwbBgeMonitorHelper::AMBITO_PAGOPA, PDO::PARAM_INT);
        
        $sql = 'SELECT
                    res.*
                FROM BGE_LOG res 
                JOIN (  SELECT
                           tlog.ORIGINE,
                            MAX(tlog.ID) as ID
                        FROM BGE_LOG tlog
                        WHERE tlog.ENTE = :ENTE
                            AND tlog.AMBITO = :AMBITO
                        GROUP BY tlog.ORIGINE) MySearch ON res.ID = MySearch.ID';
        $this->addSqlParam($sqlParams, "ENTE", $ente, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "AMBITO", cwbBgeMonitorHelper::AMBITO_PAGOPA, PDO::PARAM_INT);
        
        

        return $sql;
    }

    public function dettaglioPagoPa($ente) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlDettaglioPagoPa($ente, $sqlParams), true, $sqlParams);
    }

    public function getSqlDettaglioUpdater($ente, &$sqlParams = array()) {
//        $sql = "SELECT log2.* FROM BGE_LOG log2, "
//                . "(SELECT log1.ORIGINE,"
//                . " MAX("
//                . $this->getCitywareDB()->formatDate(
//                        $this->getCitywareDB()->strConcat("(" .
//                                $this->getCitywareDB()->dateToString('log1.DATA', 'YYYY/MM/DD'), "' '", "log1.ORA") . ")", $this->getCitywareDB()->getFormatDateTime()) . ") as dataora "
//                . " FROM BGE_LOG log1 WHERE log1.ENTE = :ENTE AND log1.AMBITO = :AMBITO "
//                . "group by log1.origine) as sottoquery "
//                . " where sottoquery.origine = log2.origine and log2.ente = :ENTE2 "
//                . " and sottoquery.dataora =  "
//                . $this->getCitywareDB()->formatDate(
//                        $this->getCitywareDB()->strConcat("(" .
//                                $this->getCitywareDB()->dateToString('log2.DATA', 'YYYY/MM/DD'), "' '", "log2.ORA") . ")", $this->getCitywareDB()->getFormatDateTime());
//
//        $this->addSqlParam($sqlParams, "ENTE", $ente, PDO::PARAM_INT);
//        $this->addSqlParam($sqlParams, "ENTE2", $ente, PDO::PARAM_INT);
//        $this->addSqlParam($sqlParams, "AMBITO", cwbBgeMonitorHelper::AMBITO_UPDATER, PDO::PARAM_INT);
        
        $sql = 'SELECT
                    res.*
                FROM BGE_LOG res 
                JOIN (  SELECT
                           tlog.ORIGINE,
                            MAX(tlog.ID) as ID
                        FROM BGE_LOG tlog
                        WHERE tlog.ENTE = :ENTE
                            AND tlog.AMBITO = :AMBITO
                        GROUP BY tlog.ORIGINE) MySearch ON res.ID = MySearch.ID';
        $this->addSqlParam($sqlParams, "ENTE", $ente, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "AMBITO", cwbBgeMonitorHelper::AMBITO_UPDATER, PDO::PARAM_INT);
        
        return $sql;
    }

    public function dettaglioUpdater($ente) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlDettaglioUpdater($ente, $sqlParams), true, $sqlParams);
    }

    public function getSqlDettaglioSiopePlus($ente, &$sqlParams = array()) {
        $sql = "SELECT * FROM BGE_LOG WHERE ENTE = :ENTE AND AMBITO=:AMBITO AND PRESA_CARICO = 0 order by id desc";

        $this->addSqlParam($sqlParams, "ENTE", $ente, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "AMBITO", cwbBgeMonitorHelper::AMBITO_SIOPE_PLUS, PDO::PARAM_INT);

        return $sql;
    }

    public function dettaglioSiopePlus($ente) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlDettaglioSiopePlus($ente, $sqlParams), true, $sqlParams);
    }

    public function getSqlDettaglioUpdateSiopePlusVisualizzazione($ente, $id, &$sqlParams = array()) {
        $sql = "UPDATE BGE_LOG SET PRESA_CARICO = 1 WHERE ENTE = :ENTE AND AMBITO=:AMBITO AND ID<=:ID";

        $this->addSqlParam($sqlParams, "ENTE", $ente, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "ID", $id, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "AMBITO", cwbBgeMonitorHelper::AMBITO_SIOPE_PLUS, PDO::PARAM_INT);

        return $sql;
    }

    public function updateSiopePlusVisualizzazione($ente, $id) {
        if (!$ente || !$id) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlDettaglioUpdateSiopePlusVisualizzazione($ente, $id, $sqlParams), true, $sqlParams);
    }

    // torna i dati di collegamento per ente
    public function getSqlCollegamento($ente, &$sqlParams = array()) {
        $sql = "SELECT BGE_COLLEGAMENTI.* FROM BGE_COLLEGAMENTI "
                . " WHERE ENTE = :ENTE";

        $this->addSqlParam($sqlParams, "ENTE", $ente, PDO::PARAM_INT);

        return $sql;
    }

    public function getSqlCollegamentoChiave($id, &$sqlParams = array()) {
        $sql = "SELECT BGE_COLLEGAMENTI.* FROM BGE_COLLEGAMENTI "
                . " WHERE ID = :ID";

        $this->addSqlParam($sqlParams, "ID", $id, PDO::PARAM_INT);

        return $sql;
    }

    public function leggiCollegamento($ente) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlCollegamento($ente, $sqlParams), true, $sqlParams);
    }

    public function leggiCollegamentoChiave($id) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlCollegamentoChiave($id, $sqlParams), false, $sqlParams);
    }

    public function getSqlBgeEnti($filters, &$sqlParams = array()) {
        $sql = "SELECT BGE_ENTI.* FROM BGE_ENTI ";

        $where = 'WHERE';
        if (array_key_exists('ENTE', $filters) && $filters['ENTE'] != null) {
            $this->addSqlParam($sqlParams, "ENTE", trim($filters['ENTE']), PDO::PARAM_INT);
            $sql .= " $where ENTE = :ENTE";
            $where = 'AND';
        }
        if (array_key_exists('ATTIVO', $filters) && $filters['ATTIVO'] !== null) {
            $this->addSqlParam($sqlParams, "ATTIVO", trim($filters['ATTIVO']), PDO::PARAM_INT);
            $sql .= " $where ATTIVO = :ATTIVO";
            $where = 'AND';
        }
        if (array_key_exists('DESENTE', $filters) && $filters['DESENTE'] != null) {
            $this->addSqlParam($sqlParams, "DESENTE", "%" . strtoupper(trim($filters['DESENTE'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESENTE") . " LIKE :DESENTE";
            $where = 'AND';
        }
        return $sql;
    }

    public function leggiBgeEnti($filters) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlBgeEnti($filters, $sqlParams), true, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura tabella BGE_PARAMS su db monitor
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiBgeParams($filters, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BGE_PARAMS.* FROM BGE_PARAMS";

        $where = ' WHERE ';
        if (array_key_exists('CHIAVE', $filters) && $filters['CHIAVE'] != null) {
            $this->addSqlParam($sqlParams, "CHIAVE", strtoupper(trim($filters['CHIAVE'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CHIAVE") . " = CHIAVE";
            $where = ' AND ';
        }

        return $sql;
    }

    /**
     * Restituisce dati tabella BGE_PARAMS su db monitor
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBgeParams($filtri, $multipla = true) {
        $params = ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBgeParams($filtri, false, $sqlParams), $multipla, $sqlParams);

        $toReturn = array();
        foreach ($params as $param) {
            $toReturn[$param['CHIAVE']] = $param['VALORE'];
        }
        return $toReturn;
    }

    /**
     * Cancella i record di bge_log per uno specifico ente e ambito. 
     * Serve a disattivare un ambito di un ente che non è più attivo
     */
    public function cancellaBgeLog($codente, $ambito) {
        if (!$codente || !$ambito) {
            return false;
        }
        $sql = "DELETE FROM BGE_LOG WHERE ENTE = :ENTE AND AMBITO = :AMBITO";

        $this->addSqlParam($sqlParams, "ENTE", $codente, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "AMBITO", $ambito, PDO::PARAM_INT);

        return $this->getCitywareDB()->query($sql, false, $sqlParams);
    }

    /**
     * Cancella i record di bge_log a partire da un mese prima di oggi
     * Serve a disattivare un ambito di un ente che non è più attivo
     */
    public function cancellaVecchiBgeLog() {
        $date = date("Y-n-j", strtotime("first day of previous month"));
        $sql = "DELETE FROM BGE_LOG WHERE DATA<:DATA";
        $this->addSqlParam($sqlParams, "DATA", $date, PDO::PARAM_STR);

        return $this->getCitywareDB()->query($sql, false, $sqlParams);
    }

    /**
     * Restituisce data ultima modifica
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri query
     * @return string Comando sql
     */
    public function getSqlLeggiDataUltimaModificaLog() {
        $sql = " SELECT DATA,ORA FROM BGE_LOG WHERE ID = (SELECT MAX(ID) FROM BGE_LOG) ";

        return $sql;
    }

    /**
     * Restituisce data ultima modifica
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiDataUltimaModificaLog() {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiDataUltimaModificaLog(), false, $sqlParams);
    }

    // DB MONITOR

    public function setMonitorDB($db) {
        $this->MONITOR_DB = $db;
    }

    public function getCitywareDB() {

        if (!$this->MONITOR_DB) {
            try {
                $this->MONITOR_DB = ItaDB::DBOpen(self::CONNECTION_NAME, '');
            } catch (Exception $e) {
                $this->setErrCode($e->getCode());
                $this->setErrMsg($e->getMessage());
            }
        }
        return $this->MONITOR_DB;
    }

}

?>