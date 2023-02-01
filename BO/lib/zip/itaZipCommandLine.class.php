<?php
class itaZipCommandLine{    
    public static function unzip($zipPath,$extractionPath=null){
        if(!file_exists($zipPath)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'File zip inesistente');
        }
        $zipPath = realpath($zipPath);
        
        if(!is_readable($zipPath)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Non posso leggere il file zip');
        }
        
        if(!isSet($extractionPath)){
            $extractionPath = dirname($zipPath);
        }
        
        if(!is_writable($extractionPath)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Non posso scrivere sul path di estrazione');
        }
        
        if(stristr(PHP_OS, "win")){
            $command = realpath(ITA_LIB_PATH) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . '7za.exe x -y "' . $zipPath . '" -o"' . $extractionPath . '"';
            exec($command,$out);
            if(in_array("Everything is Ok", $out)){
                return true;
            }
            else{
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Errore in fase di estrazione');
            }
        }
        else{
            $command = 'unzip -oq "' . $zipPath . '" -d "' . $extractionPath . '"';
            exec($command,$out);
            if(empty($out)){
                return true;
            }
            else{
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Errore in fase di estrazione');
            }
        }
        
    }
}
?>