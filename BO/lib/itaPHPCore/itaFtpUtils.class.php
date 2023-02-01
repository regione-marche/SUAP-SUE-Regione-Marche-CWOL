<?php

/**
 * Classe di utils per la gestione delle operazioni su ftp
 *
 * @author Luca Cardinali
 */
class itaFtpUtils {

    /**
     * Apre la connessione ftp
     * 
     * @param String $host
     * @param String $user
     * @param String $password
     * @return boolean
     */
    static function openFtpConnection($host, $user, $password, $passivo = false) {
        $ftp_conn = ftp_connect($host) or die("Could not connect to $host");
        if (!ftp_login($ftp_conn, $user, $password)) {
            return false;
        }
        if($passivo){
            ftp_pasv($ftp_conn, true);
        }
        return $ftp_conn;
    }

    /**
     * Torna la lista di file
     * 
     * @param String $ftp_conn oggetto connessione
     * @param String $directory path cartella 
     * @return type
     */
    static function getFilesList($ftp_conn, $directory) {
        return ftp_nlist($ftp_conn, $directory);
    }

    /**
     * chiude la connessione
     * 
     * @param Object $ftp_conn oggetto connessione
     * @return type
     */
    static function closeConnection($ftp_conn) {
        return ftp_close($ftp_conn);
    }

    /**
     * sposta un file
     * 
     * @param Object $ftp_conn oggetto connessione
     * @param String $oldFile path vecchio file
     * @param String $newFile path nuovo file
     * @return type
     */
    static function moveFile($ftp_conn, $oldFile, $newFile) {
        return ftp_rename($ftp_conn, $oldFile, $newFile);
    }

    /**
     * Legge un binario da ftp
     * 
     * @param Object $ftp_conn oggetto connessione
     * @param String $fileToRead path file da leggere
     * 
     * @return boolean
     */
    static function getBinaryFileFromFtp($ftp_conn, $fileToRead) {
        $tempFile = itaLib::getAppsTempPath() . DIRECTORY_SEPARATOR . uniqid();

        if (!ftp_get($ftp_conn, $tempFile, $fileToRead, FTP_BINARY)) {
            return false;
        } else {
            $content = file_get_contents($tempFile);
            try {
                // cancella il file temp
                unlink($tempFile);
            } catch (Exception $ex) {
                
            }

            return $content;
        }
    }

    /**
     * Scrive un file su ftp a partire da un binario
     * 
     * @param Object $ftp_conn oggetto connessione
     * @param String $remoteFile path sull'ftp
     * @param byte[] $content binario
     * @return boolean
     */
    static function writeFileFromBinary($ftp_conn, $remoteFile, $content) {
        $tempFile = itaLib::getUploadPath() . DIRECTORY_SEPARATOR . uniqid();
        if (!file_put_contents($tempFile, $content)) {  // appoggio il file su disco e poi lo rimuove alla fine
            return false;
        }
        return self::writeFile($ftp_conn, $remoteFile, $tempFile);
    }

    /**
     * Scrive un file su ftp a partire da un path locale
     * 
     * @param Object $ftp_conn oggetto connessione
     * @param String $remoteFile path sull'ftp
     * @param String $localFile path locale del file
     * @param boolean $removeLocalFile se $localFile deve essere rimosso oppure no, default false
     * @return boolean
     */
    static function writeFileFromPath($ftp_conn, $remoteFile, $localFile, $removeLocalFile = false) {
        return self::writeFile($ftp_conn, $remoteFile, $localFile, $removeLocalFile);
    }

    /**
     * Crea una cartella
     * 
     * @param String $ftp_conn oggetto connessione
     * @param String $directory path cartella 
     * @return type
     */
    static function makeDirectory($ftp_conn, $directory) {
        return ftp_mkdir($ftp_conn, $directory);
    }

    private static function writeFile($ftp_conn, $remoteFile, $localFile, $removeLocalFile = true) {
        if (!ftp_put($ftp_conn, $remoteFile, $localFile, FTP_BINARY)) {
            $result = false;
        } else {
            $result = true;
        }
        if ($removeLocalFile) { // rimuove il file
            unlink($localFile);
        }
        return $result;
    }

}

?>
