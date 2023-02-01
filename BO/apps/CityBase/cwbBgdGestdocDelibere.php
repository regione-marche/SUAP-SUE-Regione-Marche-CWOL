<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBgdGestdocBase.class.php';
include_once ITA_BASE_PATH . '/apps/CityMedia/cwaLibDB_ADE.class.php';

/**
 * Gestione documentale per ATTO_CC, ATTO_GC, ATTO_CP: 
 *
 * @author m.biagioli
 */
class cwbBgdGestdocDelibere extends cwbBgdGestdocBase {
    
    private $libDB_ADE;
    
    public function __construct() {
        parent::__construct();
        $this->libDB_ADE = new cwaLibDB_ADE();
    }
    
    public function getSqlCarica($filtri) {
        return $this->libDB_ADE->getSqlLeggiAdeFirdoc($filtri);      
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
        $master = $this->libDB_ADE->leggiAdeFirdocChiave($key);
        
        $iter = $this->libDB_ADE->leggiAdeFirdocIterAtto($master['PROG_ATTO']);
        
        foreach ($iter as $value) {
            $master["ITERA_DATA_" . $value['TIPO_PASSO']] = $value['ITERA_DATA'];
        }       
        
        return $master;
    }

    public function setDataInsert($record) {        
        $keys = array(
            'PROG_ATTO' => $record['PROG_ATTO'],
            'RIGA_ITER' => $record['RIGA_ITER'],
            'PROG_RIGA' => $record['PROG_RIGA'],
            'TIPO_TESTO' => $record['TIPO_TESTO']
        );
        $this->setFileContent($this->libDB_ADE->leggiAdeFirmeTesto($keys));
        $this->setFileMimetype('application/pdf');
        $this->setFileName($record['PROG_ATTO'] . '-' . $record['RIGA_ITER'] . '-' . $record['PROG_RIGA'] . '-' . $record['TIPO_TESTO']);
    }

    public function customSetProps(&$data) {
        
    }

    public function updateDBFields($uuid, $rec) {
        
    }

}

?>