<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BDI.class.php';
/**
 * Classe Helper per Modelli di Cityware
 *
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class cwbModelHelper extends itaModelHelper{
    
    public static function modelNameByTableName($tableName) {
        return parent::modelNameByTableName($tableName, 'cw');
    }
    /**
     * Ritorna il nome della lib in base al nome del modello
     * @param string $modelName modello
     * @return nome della lib (utilizzata per il caricamento dei dati)  
     */
    public static function libNameByModelName($modelName) {
        return substr($modelName, 0, 3) . 'LibDB_' . strtoupper(substr($modelName, 3, 3));
    }

    /**
     * Ritorna il nome del metodo di caricamento (presente nella lib) in funzione del modello
     * @param string $modelName modello
     * @return nome del metodo di caricamento
     */
    public static function loadMethodNameByModelName($modelName) {
        $tableName = self::tableNameByModelName($modelName);
        return 'leggi' . ucfirst(strtolower(substr($tableName, 0, 3))) . ucfirst(strtolower(substr($tableName, 4)));
    }

    /**
     * Ritorna il nome del metodo che restituisce la stringa sql per il caricamento dei dati di un modello
     * @param string $modelName modello
     * @return nome del metodo di caricamento
     */
    public static function sqlLoadMethodNameByModelName($modelName) {
        $tableName = self::tableNameByModelName($modelName);
        return 'getSqlLeggi' . ucfirst(strtolower(substr($tableName, 0, 3))) . ucfirst(strtolower(substr($tableName, 4)));
    }

    /**
     * Ritorna il nome del metodo di caricamento per chiave (presente nella lib) in funzione del modello
     * @param string $modelName modello
     * @return nome del metodo di caricamento
     */
    public static function loadRowMethodNameByModelName($modelName) {
        $tableName = self::tableNameByModelName($modelName);
        return 'leggi' . ucfirst(strtolower(substr($tableName, 0, 3))) . ucfirst(strtolower(substr($tableName, 4))) . 'Chiave';
    }

    /**
     * Ritorna il nome del metodo che restituisce la stringa sql per il caricamento tramite chiave del modello
     * @param string $modelName modello
     * @return nome del metodo di caricamento
     * 
     * ===================================
     * N.B.: QUESTO METODO E' STATO DISATTIVATO in quanto i metodi getSqlLeggiXXXChiave non sono presenti in tutte le lib,
     * e non si riesce ad andare in fallback su __call di cwbLibDB_CITYWARE, in quanto il parametro $sqlParams viene utilizzato
     * per riferimento, e PHP non lo consente
     * ===================================
     * 
     */
//    public static function sqlLoadRowMethodNameByModelName($modelName) {
//        $tableName = self::tableNameByModelName($modelName);
//        return 'getSqlLeggi' . ucfirst(strtolower(substr($tableName, 0, 3))) . ucfirst(strtolower(substr($tableName, 4))) . 'Chiave';
//    }
            
    public static function calcPkString($db, $tableName, $record){
        $libDB = new cwbLibDB_BDI();
        
        $filtri = array(
            "NOMETAB" => $tableName,
            "TIPOINDICE" => 1
        );
        $indici = $libDB->leggiBdiIndici($filtri);
        
        $return = array();
        foreach($indici as $v){
            $return[] = $record[$v['NOMECAMPO']];
        }
        
        return implode('|', $return);
    }
    

}
