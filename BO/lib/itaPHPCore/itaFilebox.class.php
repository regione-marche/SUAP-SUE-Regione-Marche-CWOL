<?php
require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

/**
 * Gestione Filebox
 *
 * @author Massimo Biagioli
 */
class itaFilebox {
    
    /**
     * Effettua upload file
     * @param string $fileName Nome file
     * @param string $fileContent Base64
     * @return array Esito operazione
     */
    public static function upload($fileName, $fileContent) {                
        // Inizializza valore di ritorno
        $toReturn = array(
            'ESITO' => 0,
            'MESSAGGIO' => ''
        );
        
        // Estrae parametri
        if (!$fileName) {
            $toReturn['ESITO'] = 1;
            $toReturn['MESSAGGIO'] = "Il parametro fileName non è stato specificato";
            return $toReturn;     
        }    

        /*
         * Sanitize filename
         * Da verificare con dei test
         *  
         */
        $fileName = urldecode($fileName);        
        /*
         * 
         * Controllo filnames doppi
         * 
         */
        if(file_exists($fileName)){
            // $fileName = formula con timestamp
        }
        
        if (strlen(trim($fileContent)) === 0) {
            $toReturn['ESITO'] = 1;
            $toReturn['MESSAGGIO'] = "Il contenuto da caricare è vuoto";
            return $toReturn;     
        }    
        
        $fileContent = str_replace('+', '%2B', $fileContent);
        $fileContent = urldecode($fileContent);
        if (!self::isValidBase64($fileContent)) {           
            $toReturn['ESITO'] = 1;
            $toReturn['MESSAGGIO'] = "Il parametro fileContent non è un base64 valido";
            return $toReturn;    
        }
        $fileContent = base64_decode($fileContent);
        
        // Ricava path salvataggio
        $serverPath = self::getStoragePath(true);
        if ($serverPath === false) {
            $toReturn['ESITO'] = 2;
            $toReturn['MESSAGGIO'] = "Creazione cartella archiviazione filebox fallita";
            return $toReturn;   
        }        
        
        // Salva file sul server
        $path = $serverPath . '/' . $fileName;        
        if (!file_put_contents($path, $fileContent)) {
            $toReturn['ESITO'] = 3;
            $toReturn['MESSAGGIO'] = "Errore salvataggio file su filebox";
            return $toReturn;   
        }
        
        return $toReturn;
    }
    
    /**
     * Restituisce percorso fisico memorizzazione files sul server
     * @param boolean $createIfNotExists Se true, crea la cartella se non esiste
     * @return boolean|string False in caso di errore, altrimenti path
     */
    public static function getStoragePath($createIfNotExists = false) {
        $devLib = new devLib();
        $params = $devLib->getEnv_config('FILEBOX', 'codice', 'FILEBOX_SRV_FOLDER', false);        
        $serverPath = $params['CONFIG'];
        if(!(strlen($serverPath) > 0)) {
            $serverPath = ITA_BASE_PATH . '/var/filebox';
            $serverPath .= '/' . App::$utente->getKey('ditta');
        }        
        $serverPath .= '/' . strtolower(App::$utente->getKey('nomeUtente'));
        if($createIfNotExists && !file_exists($serverPath)) {
            if(!mkdir($serverPath, 0777, true)) {                
                return false;
            }
        }
        return $serverPath;
    }
    
    /**
     * Restituisce elenco files
     * @return array Elenco files
     */
    public static function getFiles() {
        $serverPath = self::getStoragePath(true);
        $files = glob($serverPath . '/*.*');
        
        $i = 0;
        $fileList = array();
        foreach ($files as $file) {
            $fileListEntry = array();
            $fileListEntry['TABLEKEY'] = ++$i;
            $fileListEntry['FILENAME'] = basename($file);
            $fileListEntry['DATA'] = date ("d-m-Y H:i:s", filemtime($file));
            $fileListEntry['PATH'] = $file;
            $fileList[] = $fileListEntry;            
        }
        return $fileList;
    }
    
    /**
     * Upload file
     * @param string $srcPath Path del file origine
     * @param string $fileName Nome file origine
     * @return boolean Esito (true/false)
     */
    public static function uploadFile($srcPath, $fileName) {
        $destPath = self::getStoragePath(true) . '/' . $fileName;
        return copy($srcPath, $destPath);        
    } 
    
    private static function isValidBase64($data) {        
        return base64_encode(base64_decode($data, true)) === $data;
    }        
    
}