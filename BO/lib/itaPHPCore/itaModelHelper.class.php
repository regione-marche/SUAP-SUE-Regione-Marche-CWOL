<?php

/**
 * Classe Helper per Model
 *
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class itaModelHelper {

    
    
    /**
     * Restituisce la path della cartella applicativa di un file in base al nome del file 
     * @param type $name nome del file
     * @return string
     */
     public static function getAppPathByFileName($name) {
        $appRoute = App::getPath('appRoute.' . substr($name, 0, 3));
        $fileSrc = App::getConf('modelBackEnd.php') . '/' . $appRoute;
        return $fileSrc;
    }
    
    /**
     * Restituisce la path completa di un file php nella sual cartella apps in base al  nome del file false se non trovata 
     * @param type $name nome del file senza estensione
     * @return string
     */
     public static function getAppFilePathByName($name) {
        $fileSrc = self::getAppPathByFileName($name) . '/' . $name . '.php';
        return $fileSrc;
    }


    /**
     * Esegue la require di un file php nella cartella apps in base al  nome del file 
     * @param type $name nome del file senza estensione
     * @return boolean true=require eseguita, false require non possibile
     */
    public static function requireAppFileByName($name) {
        $fileSrc = self::getAppFilePathByName($name);
        if (file_exists($fileSrc)) {
            require_once $fileSrc;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Restituisce la classe modelRest specifica (se non trovata, istanza quella base)
     * @param string $modelName Nome model
     * @return object Instanza
     */
    public static function findClassModelRest($modelName) {
        return self::findClassByModelName($modelName, 'rest', 'ModelRest');
    }

    /**
     * Restituisce la classe Validator specifica (se non trovata, istanza quella base)
     * @param string $modelName Nome model
     * @return object Instanza
     */
    public static function findClassValidatorResolver($modelName) {
        return self::findClassByModelName($modelName, 'validators', 'Validator');
    }

    /**
     * Restituisce la classe Validator specifica (se non trovata, istanza quella base)
     * @param string $modelName Nome model
     * @params array $params Parametri da passare al costruttore della classe
     * @return object Instanza
     */
    public static function findClassAuthenticator($modelName, $params) {
        return self::findClassByModelName($modelName, 'authenticators', 'Authenticator', $params);
    }

    /**
     * Imposta stringa informazioni record per log
     * @param int $operation Tipo operazione
     * @param string $source Sorgente
     * @param array $data Dati record
     * @return string Stringa informazioni record
     */
    public static function impostaRecordInfo($operation, $source, $data) {
        switch ($operation) {
            case itaModelService::OPERATION_INSERT:
                $descOp = "Inserimento";
                break;
            case itaModelService::OPERATION_UPDATE:
                $descOp = "Aggiornamento";
                break;
            case itaModelService::OPERATION_DELETE:
                $descOp = "Cancellazione";
                break;
            case itaModelService::OPERATION_OPENRECORD:
                $descOp = "Apertura record";
                break;
        }
        return "$descOp su {$data['tableName']} da $source - Dati: " . json_encode($data);
    }

    private static function findClassByModelName($modelName, $folder, $suffix, $params = null) {
        $appRoute = App::getPath('appRoute.' . substr($modelName, 0, 3));
        $modelSrc = App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $folder . '/' . $modelName . $suffix . '.php';

        // Se non presente il validator specifico, prende quello generico
        if (file_exists($modelSrc)) {
            $className = $modelName . $suffix;
        } else {
            $className = substr($modelName, 0, 3) . 'Base' . $suffix;
            $modelSrc = App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $folder . '/' . $className . '.php';
        }
        require_once $modelSrc;

        if ($params) {
            $clazz = new $className($params);
        } else {
            $clazz = new $className();
        }

        return $clazz;
    }
    
    /**
     * Ritorna il nome della tabella in base al nome del modello (_ prima dei caratteri upper)
     * @param string $modelName modello
     * @return nome della tabella  
     */
    public static function tableNameByModelName($modelName) {
        if(substr($modelName, 0, 2) == 'cw'){
            return strtoupper(preg_replace('/(.)([A-Z])/', '$1_$2', substr($modelName, 3)));
        }
        else{
            return strtoupper(preg_replace('/(.)([A-Z])/', '$1_$2', $modelName));
        }
    }
    
    /**
     * Ritorna il modello collegato alla tableName
     * @param string $tableName tabella
     * @return nome del modello
     */
    public static function modelNameByTableName($tableName, $nameFormOrig) {
        if(substr($nameFormOrig, 0, 2) == 'cw'){
            return "cw" . strtolower(substr($tableName, 0, 1)) . str_replace(' ', '', ucwords(strtolower(str_replace('_', ' ', $tableName))));
        }
        else{
            $modelName = str_replace(' ', '', ucwords(strtolower(str_replace('_', ' ', $tableName))));
            return strtolower(substr($modelName, 0, 1)) . substr($modelName, 1);
        }
    }
    
    /**
     * return il nome del area dal prefisso passato 
     */
    public static function moduleByModelName($modelName) {
        $prefix = strtolower(substr($modelName, 0, 3));
        return App::getPath('appRoute.' . $prefix);
    }
    
    public static function calcPkString($db, $tableName, $record){
        $tableDef = ItaDB::getTableDef($db, $tableName);
        $pks = $tableDef->getPks();
        
        $return = array();
        foreach($pks as $pk){
            $return[] = $record[$pk];
        }
        return implode('|', $return);
    }
}
