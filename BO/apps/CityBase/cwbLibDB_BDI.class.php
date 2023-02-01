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
 * @author     Lorenzo Pergolini Massimo Biagioli <m.biagioli@palinformatica.it>
 * 
 */
class cwbLibDB_BDI extends cwbLibDB_CITYWARE {

    /**
     * Legge descrizioni colonne di una tabella
     * @return array Descrizioni colonne tabella
     */
    public function leggiDescrizioniColonneTabella($nomeTabella) {
        $area = substr($nomeTabella, 0, 1);
        $modulo = substr($nomeTabella, 1, 2);
        $nome = substr($nomeTabella, 4);

        $sql = "SELECT CAMPO, DESCRI FROM BDI_FORMAT
                WHERE AREA='$area'
                AND MODULO='$modulo'
                AND NOMETABEL='$nome'
                ORDER BY SEQUENZA";

        return ItaDB::DBSQLSelect($this->getCitywareDB(), $sql, true);
    }

    /**
     * Legge le relazioni per il controllo integrità di una tabella
     * @return array Lista di relazioni
     */
    public function leggiRelazioni($nomeTabella) {
        $area = substr($nomeTabella, 0, 1);
        $modulo = substr($nomeTabella, 1, 2);
        $nome = substr($nomeTabella, 4);

        $sql = "SELECT * FROM BDI_REL 
                WHERE AREA_IND='$area'
                AND MODULO_IND='$modulo'
                AND NOMETA_IND='$nome'
                ORDER BY NOMERELAZ";

        $results = ItaDB::DBSQLSelect($this->getCitywareDB(), $sql, true);


        $list = array();
        foreach ($results as $row) {
            $nomeRelaz = trim($row['NOMERELAZ']);
            $areaDip = trim($row['AREA_DIP']);
            $moduloDip = trim($row['MODULO_DIP']);
            $tabellaDip = trim($row['NOMETA_DIP']);

            $sql = "SELECT 
                        AREA,MODULO,NOMETAB,SEQREL,CAMPO 
                    FROM 
                        BDI_RELC 
                    WHERE NOMERELAZ='$nomeRelaz'
                    ORDER BY 
                        NOMERELAZ,AREA,MODULO,NOMETAB,SEQREL";

            $fields = ItaDB::DBSQLSelect($this->getCitywareDB(), $sql);

            $list[$nomeRelaz] = array(
                'areaInd' => trim($row['AREA_IND']),
                'moduloInd' => trim($row['MODULO_IND']),
                'nometaInd' => trim($row['NOMETA_IND']),
                'areaDip' => trim($row['AREA_DIP']),
                'moduloDip' => trim($row['MODULO_DIP']),
                'nometaDip' => trim($row['NOMETA_DIP']),
                'cardinalita' => $row['CARDINALIT'],
                'operazioneRelazione' => $row['OPER_REL'],
                'condizioneWhere' => trim($row['COND_WHERE']),
                'note' => $row['NOTEREL']
            );

            $mappingfields = array();

            foreach ($fields as $field) {
                // se è dipendente 
                if ($field["AREA"] == $areaDip && $field["MODULO"] == $moduloDip && trim($field["NOMETAB"]) == $tabellaDip) {

                    $mappingfields[$field["SEQREL"]]["tabellaDip"] = trim("$areaDip$moduloDip" . "_" . $tabellaDip);
                    $mappingfields[$field["SEQREL"]]["campoDip"] = trim($field["CAMPO"]);
                } else {
                    $mappingfields[$field["SEQREL"]]["tabellaInd"] = trim("{$field["AREA"]}{$field["MODULO"]}_{$field["NOMETAB"]}");
                    $mappingfields[$field["SEQREL"]]["campoInd"] = trim($field["CAMPO"]);
                }
            }
            $list[$nomeRelaz]['fields'] = $mappingfields;
        }
        return $list;
    }

    public function verificaPresenzaRelazione($nomeTabella) {
        $area = substr($nomeTabella, 0, 1);
        $modulo = substr($nomeTabella, 1, 2);
        $nome = substr($nomeTabella, 4);

        $sql = "SELECT * FROM BDI_REL 
                WHERE NOMETA_DIP='$nome'
                AND MODULO_DIP='$modulo'
                AND AREA_DIP='$area'
                ORDER BY NOMERELAZ";

        $results = ItaDB::DBSQLSelect($this->getCitywareDB(), $sql, true);
        return $results;
    }

    /**
     * Restituisce comando sql per lettura tabella BTA_GRUNAZ
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBdiIndici($filtri, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BDI_INDICI.* FROM BDI_INDICI";
        $where = 'WHERE';
        if (array_key_exists('NOMETAB', $filtri) && $filtri['NOMETAB'] != null) {
            $this->addSqlParam($sqlParams, "NOMETAB", strtoupper(trim($filtri['NOMETAB'])), PDO::PARAM_STR);
            $sql .= " $where NOMETAB=:NOMETAB";
            $where = 'AND';
        }

        if (array_key_exists('TIPOINDICE', $filtri) && $filtri['TIPOINDICE'] != null) {
            $this->addSqlParam($sqlParams, "TIPOINDICE", $filtri['TIPOINDICE'], PDO::PARAM_INT);
            $sql .= " $where TIPOINDICE=:TIPOINDICE";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY NOMETAB, PROGINT';

        return $sql;
    }

    /**
     * Restituisce dati tabella BDI_INDICI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBdiIndici($filtri, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBdiIndici($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

}

?>