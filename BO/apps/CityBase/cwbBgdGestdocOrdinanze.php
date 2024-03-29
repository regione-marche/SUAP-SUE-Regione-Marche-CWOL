<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBgdGestdocBase.class.php';

/**
 * Gestione documentale per "Documento Master Cityware": 
 *
 * @author m.biagioli
 */
class cwbBgdGestdocOrdinanze extends cwbBgdGestdocBase {    
    
    public function getSqlCarica($filtri) {
        $sql = "SELECT * FROM fta_clabpl"; 
        
        $where = 'WHERE';
        if (array_key_exists('F_NO_UUID', $filtri) && $filtri['F_NO_UUID'] == true) {
            $sql .= " $where uuid IS NULL";
            $where = 'AND';
        }
        if (array_key_exists('DATA_ARCH_DA', $filtri) && $filtri['DATA_ARCH_DA'] != null) {
            $sql .= " $where datacreazdoc >= '" . $filtri['DATA_ARCH_DA'] . "'";
            $where = 'AND';
        }
        if (array_key_exists('DATA_ARCH_A', $filtri) && $filtri['DATA_ARCH_A'] != null) {
            $sql .= " $where datacreazdoc <= '" . $filtri['DATA_ARCH_A'] . "'";
            $where = 'AND';
        }
        if (array_key_exists('anno_ese', $filtri) && $filtri['anno_ese'] != null) {
            $sql .= " $where anno_ese = '" . $filtri['anno_ese'] . "'";
            $where = 'AND';
        }
        if (array_key_exists('progr_bpl', $filtri) && $filtri['progr_bpl'] != null) {
            $sql .= " $where progr_bpl = '" . $filtri['progr_bpl'] . "'";
            $where = 'AND';
        }
        if (array_key_exists('proge_bpl', $filtri) && $filtri['proge_bpl'] != null) {
            $sql .= " $where proge_bpl = '" . $filtri['proge_bpl'] . "'";
            $where = 'AND';
        }
        if (array_key_exists('prges1_bpl', $filtri) && $filtri['prges1_bpl'] != null) {
            $sql .= " $where prges1_bpl = '" . $filtri['prges1_bpl'] . "'";
            $where = 'AND';
        }
        
        $sql .= " ORDER BY anno_ese, progr_bpl, proge_bpl, prges1_bpl";        
        
        return $sql;
    }

    public function elaboraRecords($results) {
        foreach ($results as $key => $rec) { 
            $strid = $rec['anno_ese'] . '.' . $rec['progr_bpl'] . '.' . (strlen(trim($rec['proge_bpl']))>0 ? $rec['proge_bpl'] : '00') . '.' . (strlen(trim($rec['prges1_bpl']))>0 ? $rec['prges1_bpl'] : '00');
            $results[$key]['rowid'] = $strid;
            $results[$key]['NOME'] = $rec['des_clbpl'];
            $results[$key]['UUID'] = $rec['uuid'];
            $results[$key]['DATAARCH'] = $rec['datacreazdoc'];
            $results[$key]['INFO'] = $strid;
        }
        return $results;
    }

    public function caricaRecord($key) {  
        $this->apriDB();
        $res = $this->leggiDaChiave($key);
        return $res;
    }
    
     private function leggiDaChiave($cod) {
        if(!$cod){
            return null;
        }
        list($anno_ese,$progr_bpl,$proge_bpl,$prges1_bpl) = explode(".", $cod);
        $proge_bpl = ($proge_bpl == '00' ? ' ' : $proge_bpl);
        $prges1_bpl = ($prges1_bpl == '00' ? ' ' : $prges1_bpl);
        
        $filtri = array(
            'anno_ese' => $anno_ese,
            'progr_bpl' => $progr_bpl,
            'proge_bpl' => $proge_bpl,
            'prges1_bpl' => $prges1_bpl
        );
        
        return ItaDB::DBSQLSelect($this->DB, $this->getSqlCarica($filtri), false);        
    }
        
    public function setDataInsert($record) {
        
    }

    public function customSetProps(&$data) {
        
    }

    public function updateDBFields($uuid, $rec) {
        
    }

}

?>