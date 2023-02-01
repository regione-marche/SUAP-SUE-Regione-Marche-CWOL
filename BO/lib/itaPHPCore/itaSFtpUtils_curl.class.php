<?php

require_once ITA_LIB_PATH . '/itaPHPCore/chilkat_9_5_0.php';

/**
 * Classe di utils per la gestione delle operazioni su sftp (usa curl) 
 *
 * @author Luca Cardinali
 */
class itaSFtpUtils {

    const TIMEOUT = 60;
    const CONNECTION_TIMEOUT = 60;
    const DEFAULT_FORMAT = 'UTF-8';

    private $server;
    private $user;
    private $password;
    private $certPath;
    private $certpassword;
    private $resultFormat; // default DEFAULT_FORMAT
    private $errCode;
    private $errMessage;
    private $result;

    /**
     * Carica un file su un sftp 
     * 
     * @param String $destinationPath Cartella su cui scrivere il file, se vuota va sulla root dell'sftp
     * @param String $fileToTrasferPath path da cui leggere il file da trasferire
     * @return boolean
     */
    function uploadFile($destinationPath, $fileToTrasferPath) {
        $this->cleanResult();

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

        $ch = curl_init();

        // credential
        $this->setCredential($ch, $destinationPath);

        // Certificate
        $this->setCertificate($ch);

        // Set file to upload
        $fp = fopen($fileToTrasferPath, 'r');
        curl_setopt($ch, CURLOPT_UPLOAD, true);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($fileToTrasferPath));

        // log
        $logPath = itaLib::getUploadPath() . "/" . time() . '.txt';
        curl_setopt($ch, CURLOPT_STDERR, $logPath);

        curl_exec($ch);
        $error_no = curl_errno($ch);
        $error_msg = curl_error($ch);
        curl_close($ch);
        fclose($fileToTrasferPath);

        if ($error_no == 0) {
            $msg = 'File caricato';
            $this->setResult($msg);
            unlink($logPath);
            return true;
        } else {
            $msgLog = file_get_contents($logPath);
            $msg = 'Errore Caricamento: ' . $error_msg . ' info: ' . $msgLog;
            $this->setErrCode($error_no);
            $this->setErrMessage($msg);
            unlink($logPath);
            return false;
        }
    }

    /**
     * Scarica un file da sftp
     * 
     * @param type $downloadFilePath path completo del file da scaricare
     * @return boolean risultato su getResult
     */
    function downloadFile($downloadFilePath) {
        $this->cleanResult();

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

        $ch = curl_init();

        // credential
        $this->setCredential($ch, $downloadFilePath);

        // Certificate
        $this->setCertificate($ch);

        // log
        $logPath = itaLib::getUploadPath() . "/" . time() . '.txt';
        curl_setopt($ch, CURLOPT_STDERR, $logPath);

        $upload_result = curl_exec($ch);
        $error_no = curl_errno($ch);
        $error_msg = curl_error($ch);
        curl_close($ch);

        if ($error_no == 0) {
            $this->setResult($upload_result);
            unlink($logPath);
            return true;
        } else {
            $msgLog = file_get_contents($logPath);
            $msg = 'Errore download: ' . $error_msg . ' info: ' . $msgLog;
            $this->setErrCode($error_no);
            $this->setErrMessage($msg);
            unlink($logPath);
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
        $this->cleanResult();

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

//        if (!$directoryFilePath) {
//            $this->setErrCode(-1);
//            $this->setErrMessage("Inserire il path del file da scaricare");
//            return false;
//        }

        $ch = curl_init();

        // credential
        $this->setCredential($ch, $directoryFilePath);

        // Certificate
        $this->setCertificate($ch);

        // log
        
        $logPath = itaLib::getUploadPath() . "/" . time() . '.txt';
        curl_setopt($ch, CURLOPT_STDERR, fopen($logPath, 'w+'));

        $upload_result = curl_exec($ch);
        $error_no = curl_errno($ch);
        $error_msg = curl_error($ch);
        curl_close($ch);

        if ($error_no == 0) {
            // il curl torna un txt con i file separati da new line
            $listFiles = explode("\n", $upload_result);

            $toReturn = array();
            // se si vuole cercare dei file in particolare, li filtro per prefiso
            if ($fileNamePrefix) {
                foreach ($listFiles as $file) {
                    // se contiene $fileNamePrefix lo aggiungo a quelli da ritornare
                    if (preg_match('/' . $fileNamePrefix . '/', $file)) {
                        // prendo solo la parte dal prefisso in poi perché davanti curl mette altre info
                        $pos = strpos($file, $fileNamePrefix);
                        $fileName = substr($file, $pos);
                        $toReturn[$fileName] = array(
                            'NOMEFILE' => $fileName,
                            'DATA' => $data,
                            'ORA' => $ora,
                        );
                    }
                }
            } else {
                foreach ($listFiles as $file) {
                    // se contiene $fileNamePrefix lo aggiungo a quelli da ritornare
                    // prendo solo la parte dal prefisso in poi perché davanti curl mette altre info
                    $fileName = substr($file, $pos);
                    $toReturn[$fileName] = array(
                        'NOMEFILE' => $fileName,
                        'DATA' => $data,
                        'ORA' => $ora,
                    );
                }
            }
            $this->setResult($toReturn);
            unlink($logPath);
            return true;
        } else {
            $msgLog = file_get_contents($logPath);
            $msg = 'Errore download: ' . $error_msg . ' info: ' . $msgLog;
            $this->setErrCode($error_no);
            $this->setErrMessage($msg);
            unlink($logPath);
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

    public function setParameters($server, $user, $password, $certPath = '', $certpassword = '') {
        $this->setServer($server);
        $this->setUser($user);
        $this->setPassword($password);
        $this->setCertPath($certPath);
        $this->setCertpassword($certpassword);
    }

    function getServer() {
        return $this->server;
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
        $this->errMessage = $errMessage;
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

?>
