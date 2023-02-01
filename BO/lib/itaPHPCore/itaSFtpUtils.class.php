<?php

//function require_recursive() {
//    $directories = array(
//        ITA_LIB_PATH . '/itaPHPSecLib/Crypt/',
//        ITA_LIB_PATH . '/itaPHPSecLib/Net/',
//    );
//    foreach ($directories as $directory) {
//        foreach (glob($directory . "*.php") as $class) {
//            require_once  $class;
//        }
//    }
//}
//require_recursive();
require_once ITA_LIB_PATH . '/itaPHPCore/chilkat_9_5_0.php';

/**
 * Classe di utils per la gestione delle operazioni su sftp (usa libreria PHPSECLIB) 
 *
 * @author Luca Cardinali
 */
class itaSFtpUtils {

    const TIMEOUT = 60;
    const CONNECTION_TIMEOUT = 60;
    const DEFAULT_FORMAT = 'UTF-8';

    private $server;
    private $port = 22;
    private $user;
    private $password;
    private $certPath;
    private $certpassword;
    private $resultFormat; // default DEFAULT_FORMAT
    private $errCode;
    private $errMessage;
    private $result;
    private $sftp;
    private $key;

    /**
     * Carica un file su un sftp 
     * 
     * @param String $destinationPath Cartella su cui scrivere il file, se vuota va sulla root dell'sftp
     * @param String $fileToTrasferPath path da cui leggere il file da trasferire
     * @return boolean
     */
    function uploadFile($destinationPath, $fileToTrasferPath) {
        // precondition
        if (!$this->getServer()) {
            $this->setErrCode(-1);
            $this->setErrMessage('Inserire il server sftp');
            return false;
        }

        if (!$this->getUser()) {
            $this->setErrCode(-1);
            $this->setErrMessage("Inserire l'utente sftp");
            return false;
        }

        if (!$fileToTrasferPath) {
            $this->setErrCode(-1);
            $this->setErrMessage("Inserire il path del file da trasferire");
            return false;
        }

        if ($this->connection()) {
            if ($this->sftp->put($destinationPath, $fileToTrasferPath, phpseclib\Net\SFTP::SOURCE_LOCAL_FILE)) {
                $msg = 'File caricato';
                $this->setResult($msg);
                return true;
            } else {
                $this->setErrMessage($this->sftp->getSFTPErrors());
                return false;
            }
        } else {
            $this->setErrMessage("Connessione a sFTP KO");
            return false;
        }
    }

    /**
     * Carica un chunk di dati del file su un sftp 
     * 
     * @param String $destinationPath Cartella su cui scrivere il file, se vuota va sulla root dell'sftp
     * @param String $chunk bytestream di dati da aggiungere al file 
     * @param String $start start point da cui aggiungere i byte in coda - equivale al trasferito fino a questo punto
     * @return boolean
     */
    function uploadFileChunk($destinationPath, $chunk, $start = 0) {
        // precondition
        if (!$this->getServer()) {
            $this->setErrCode(-1);
            $this->setErrMessage('Inserire il server sftp');
            return false;
        }

        if (!$this->getUser()) {
            $this->setErrCode(-1);
            $this->setErrMessage("Inserire l'utente sftp");
            return false;
        }

        if (!$chunk) {
            $this->setErrCode(-1);
            $this->setErrMessage("Inserire il chunk di dati da trasferire");
            return false;
        }

        if ($this->connection()) {
            if ($this->sftp->put($destinationPath, $chunk, phpseclib\Net\SFTP::SOURCE_STRING, $start)) {
                $msg = 'File caricato';
                $this->setResult($msg);
                return true;
            } else {
                $this->setErrMessage($this->sftp->getSFTPErrors());
                return false;
            }
        } else {
            $this->setErrMessage("Connessione a SFTP KO: " . $this->getErrMessage());
            return false;
        }
    }

    /**
     * Scarica un file da sftp.
     * 
     * @param type $downloadFilePath path completo del file da scaricare
     * @return boolean risultato su getResult.
     */
    function downloadFile($downloadFilePath) {
        // precondition
        if (!$this->getServer()) {
            $this->setErrCode(-1);
            $this->setErrMessage('Inserire il server sftp');
            return false;
        }

        if (!$this->getUser()) {
            $this->setErrCode(-1);
            $this->setErrMessage("Inserire l'utente sftp");
            return false;
        }

        if (!$downloadFilePath) {
            $this->setErrCode(-1);
            $this->setErrMessage("Inserire il path del file da scaricare");
            return false;
        }
        if ($this->connection()) {
            $upload_result = $this->sftp->get($downloadFilePath);
            if ($upload_result) {
                $this->setResult($upload_result);
                return true;
            } else {
                $this->setErrMessage($this->sftp->getSFTPErrors());
                return false;
            }
        } else {
            $this->setErrMessage("Connessione a sFTP KO");
            return false;
        }
    }

    /**
     * Restituisce la dimensione di un file da sftp senza farne il download.
     * 
     * @param type $remoteFile nome completo del file
     * @return type $size dimensione in byte.
     */
    function getFileSize($remoteFile) {
        // precondition
        if (!$this->getServer()) {
            $this->setErrCode(-1);
            $this->setErrMessage('Inserire il server sftp');
            return false;
        }

        if (!$this->getUser()) {
            $this->setErrCode(-1);
            $this->setErrMessage("Inserire l'utente sftp");
            return false;
        }

        if (!$remoteFile) {
            $this->setErrCode(-1);
            $this->setErrMessage("Inserire il file.");
            return false;
        }
        if ($this->connection()) {
            $size = $this->sftp->size($remoteFile);
            if ($size > 0) {
                return $size;
            } else {
                $this->setErrMessage($this->sftp->getSFTPErrors());
                return false;
            }
        } else {
            $this->setErrMessage("Connessione a sFTP KO");
            return false;
        }
    }

    /**
     * Cancella un file da sftp.
     * 
     * @param type $deleteFilePath path completo del file da cancellare
     * @return boolean risultato su getResult.
     */
    function deleteFile($deleteFilePath) {
        // precondition
        if (!$this->getServer()) {
            $this->setErrCode(-1);
            $this->setErrMessage('Inserire il server sftp');
            return false;
        }

        if (!$this->getUser()) {
            $this->setErrCode(-1);
            $this->setErrMessage("Inserire l'utente sftp");
            return false;
        }

        if (!$deleteFilePath) {
            $this->setErrCode(-1);
            $this->setErrMessage("Inserire il path del file da cancellare");
            return false;
        }
        if ($this->connection()) {
            $upload_result = $this->sftp->delete($deleteFilePath);
            if ($upload_result) {
                $this->setResult($upload_result);
                return true;
            } else {
                $this->setErrMessage($this->sftp->getSFTPErrors());
                return false;
            }
        } else {
            $this->setErrMessage("Connessione a sFTP KO");
            return false;
        }
    }

    /**
     * Torna la lista dei file di una directory
     * 
     * @param String $directoryFilePath il path della cartella
     * @param String $fileNamePrefix Il prefisso del nome del file se si vogliono paginare solo i file che iniziano per $fileNamePrefix
     * @return boolean su getResult la lista di file
     */
    function listOfFiles($directoryFilePath, $fileNamePrefix = null) {
        //$this->cleanResult();
        // precondition
        if (!$this->getServer()) {
            $this->setErrCode(-1);
            $this->setErrMessage('Inserire il server sftp');
            return false;
        }

        if (!$this->getUser()) {
            $this->setErrCode(-1);
            $this->setErrMessage("Inserire l'utente sftp");
            return false;
        }

        if ($this->connection() && $this->sftp->nlist($directoryFilePath)) {
            $this->setResult($this->sftp->nlist($directoryFilePath));
            return true;
        } else {
            $this->setErrMessage($this->sftp->getSFTPErrors());
            return false;
        }
    }

    /**
     * Converte un certificato ppk in certificato openssh.
     * 
     * Scaricare dll e aggiungere estensione su php.ini
     * https://www.example-code.com/php/ssh_ppk_to_pem.asp     
     * 
     * @param String $ppkCertPathIN Il path da cui caricare il certificato ppk
     * @param String $ppkPassword La password del certificato ppk
     * @param String $pemPassword La password del certificato pem con cui eseguire il crypt (se criptato)
     * @param boolean $criptato se si vuole generare un pem criptato oppure no
     * 
     * @return boolean true/false + il certificato su getResult oppure l'errore su getErrMessage
     * 
     */
    public function convertPpkToPem($ppkCertPathIN, $ppkPassword, $pemPassword = '', $criptato = false) {
        $this->cleanResult();

        $key = new CkSshKey();
        $key->put_Password($ppkPassword);

        $keyStr = $key->loadText($ppkCertPathIN);

        $success = $key->FromPuttyPrivateKey($keyStr);
        if ($success != true) {
            $this->setErrCode(-1);
            $this->setErrMessage($key->lastErrorText());
            return false;
        }

        if ($criptato) {
//  Save to an encrypted OpenSSH PEM file:
            $key->put_Password($pemPassword);
            $encryptedKeyStr = $key->toOpenSshPrivateKey(true);
            if ($encryptedKeyStr) {
                $this->setResult($encryptedKeyStr);
                return true;
            }
//   $success = $key->SaveText($encryptedKeyStr, $pemCertPathOut);
        } else {
//  Save to an unencrypted OpenSSH PEM file:
            $unencryptedKeyStr = $key->toOpenSshPrivateKey(false);
            if ($unencryptedKeyStr) {
                $this->setResult($unencryptedKeyStr);
                return true;
            }
//    $success = $key->SaveText($unencryptedKeyStr, $pemCertPathOut);
        }

        $this->setErrCode(-1);
        $this->setErrMessage($key->lastErrorText());
        return false;
    }

    private function setCredential($ch, $path = '') {
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_URL, 'sftp://' . $this->getUser() . ':' . $this->getPassword() . '@' . $this->getServer() . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECTION_TIMEOUT);
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
    }

    private function setCertificate($ch) {
        if ($this->getCertPath()) {
            curl_setopt($ch, CURLOPT_SSH_AUTH_TYPES, CURLSSH_AUTH_PUBLICKEY);
            curl_setopt($ch, CURLOPT_SSH_PRIVATE_KEYFILE, $this->getCertPath());
            if ($this->getCertpassword()) {
                curl_setopt($ch, CURLOPT_KEYPASSWD, $this->getCertpassword());
            }
        }
    }

    private function cleanResult() {
        $this->setResult('');
        $this->setErrCode('');
        $this->setErrMessage('');
    }

    private function connection() {
        $this->sftp = new phpseclib\Net\SFTP($this->getServer(), $this->getPort());
        if ($this->getCertpassword()) {
            $this->key = new phpseclib\Crypt\RSA();
            $this->key->setPassword($this->getCertpassword());
            $this->key->loadKey(file_get_contents($this->getCertPath()));
        } else {
            $this->key = $this->getPassword();
        }
        if (!$this->sftp->login($this->getUser(), $this->key)) {
            $this->setErrMessage('Login Failed!');
            return false;
        } else {
            return true;
        }
    }

    public function setParameters($server, $user, $password, $certPath = '', $certpassword = '') {
        if (strpos($server, ":") !== false) {
            list($server, $port) = explode(":", $server);
            $this->setPort($port);
        }
        $this->setServer($server);
        $this->setUser($user);
        $this->setPassword($password);
        $this->setCertPath($certPath);
        $this->setCertpassword($certpassword);
    }

    function getServer() {
        return $this->server;
    }

    function getPort() {
        return $this->port;
    }

    function getUser() {
        return $this->user;
    }

    function getPassword() {
        return $this->password;
    }

    function getCertPath() {
        return $this->certPath;
    }

    function getCertpassword() {
        return $this->certpassword;
    }

    function setServer($server) {
        $this->server = $server;
    }

    function setPort($port) {
        $this->port = $port;
    }

    function setUser($user) {
        $this->user = $user;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function setCertPath($certPath) {
        $this->certPath = $certPath;
    }

    function setCertpassword($certpassword) {
        $this->certpassword = $certpassword;
    }

    function getErrCode() {
        return $this->errCode;
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function getResult() {
        return $this->result;
    }

    function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    function setErrMessage($errMessage) {
        if (is_array($errMessage)) {
            $this->errMessage = $errMessage[0];
        } else {
            $this->errMessage = $errMessage;
        }
    }

    function setResult($result) {
        $this->result = $result;
    }

    function getResultFormat() {
        return $this->resultFormat;
    }

    function setResultFormat($resultFormat) {
        $this->resultFormat = $resultFormat;
    }

}
