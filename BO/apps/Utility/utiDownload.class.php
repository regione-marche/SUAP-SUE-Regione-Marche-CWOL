<?php
include_once ITA_LIB_PATH . '/Cache/CacheFactory.class.php';

class utiDownload {
    /**
     * La funzione restituisce un url valido per un unico download di un file.
     * @param string $filename Nome del file
     * @param string $filepath Path dove si trova il file (Nome Completo del File)
     * @param boolean $forceDownload Se true, Forza il download
     * @param boolean $deleteFile Se true, Cancella il file appena scaricato
     * @param returnCompleteUrl Se true, restituisce l'url completo, altrimenti quello relativo 
     * @return string Path relativo a Start.php per il recupero del file
     * @throws ItaException nel caso il file passato non esista.
     */
    public static function getOTR($filename,$filepath,$forceDownload=true,$deleteFile=false,$returnCompleteUrl=false){
        if(!file_exists(($filepath))){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP,-1,"Il file $filepath non esiste");
        }
        
        $array = array();
        $array['TOKEN'] = App::$utente->getKey('TOKEN');
        $array['Filename'] = $filename;
        $array['Filepath'] = $filepath;
        $array['ForceDownload'] = $forceDownload;
        $array['DeleteFile'] = $deleteFile;
        
        $cache = CacheFactory::newCache(CacheFactory::TYPE_FILE, null, '');
        $key = sha1(microtime().rand(0,1000));
        while($cache->get($key)){
            $key = sha1(microtime());
        }
        $cache->set($key,$array,300);
        $url = '';
        if ($returnCompleteUrl) {
            $url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].str_replace('controller.php', '', $_SERVER['REQUEST_URI']);
        } 
        $url .= "wsrest/service.php/storageService/getOTR?resourceToken=".$key;
        return $url;
    }

    /**
     *
     * @param <string> $file_name nome del file destino da salvare o visualizzare
     * @param <string> $file_path nome del file sorgente comprensivo di path da inviare
     * @return <string> $url per la richiesta al sistema itaEngine
     */
    public static function getUrl($file_name, $file_path, $force_download = false, $utf8decode = false, $headers = true) {
        $downloadKey = md5(rand() * time());
        App::$utente->setKey($downloadKey . "_DATAFILE", $file_path);
        App::$utente->setKey($downloadKey . "_FILENAME", $file_name);

        $test = '';
        if (App::$utente->getKey('environment_test')) {
            $test = '&test=' . App::$utente->getKey('environment_test');
        }

        $url = "download.php?TOKEN=" . App::$utente->getKey('TOKEN') . $test . "&key=" . $downloadKey . "&forceDownload=" . $force_download . "&utf8decode=" . $utf8decode . "&headers=" . $headers;
        return $url;
    }

}