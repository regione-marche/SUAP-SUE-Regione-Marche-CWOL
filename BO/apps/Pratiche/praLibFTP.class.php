<?php

/**
 *
 * LIBRERIA PER GESTIONE METODI SFTP NELL'APPLICATIVO PRATICHE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2019 Italsoft srl
 * @license
 * @version    03.06.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */

/**
 * @property $praLib praLib
 */
class praLibFTP {

    private static $praLibFTP;
    private $praLib;
    private $errMessage;
    private $errCode;

    public static function getInstance($praLib) {
        if (!isset(self::$praLibFTP)) {
            $obj = new praLibFTP();
            $obj->setPraLib($praLib);

            self::$praLibFTP = $obj;
        }
        return self::$praLibFTP;
    }

    public function getPraLib() {
        return $this->praLib;
    }

    public function setPraLib($praLib) {
        $this->praLib = $praLib;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    /**
     * 
     * @param type $ftpParm array()
     * @return string
     */
    public function deleteFile($ftpParm) {
        if ($ftpParm["RemoteFile"] == '') {
            $this->setErrMessage("File Remoto non configurato nei parametri");
            return "Error";
        }
        if ($ftpParm['SERVER'] == '') {
            $this->setErrMessage("Server FTP non configurato ");
            return "Inactive";
        }
        $Remote_File = $ftpParm["RemoteFile"];
        $file = $ftpParm['ROOTPATH'] . "/" . $ftpParm["RemoteFile"]; //
        if (strpos($ftpParm['ROOTPATH'], "/") === false) {
            $file = $ftpParm['ROOTPATH'] . "/" . $ftpParm["RemoteFile"];
        }
        if (strpos($ftpParm['ROOTPATH'], "/") == strlen($ftpParm['ROOTPATH']) - 1) {
            $file = $ftpParm['ROOTPATH'] . $ftpParm["RemoteFile"];
        }
        $ftp_server = $ftpParm['SERVER'];
        list($server, $porta) = explode(":", $ftp_server);
        if ($ftpParm['TIPOCONNESSIONE'] == 'SFTP') {
            include_once (ITA_LIB_PATH . '/itaPHPCore/itaSFtpUtils.class.php');
            $sftp = new itaSFtpUtils();
            $sftp->setParameters($ftp_server, $ftpParm['UTENTE'], $ftpParm['PASSWORD']);
            $esitoDeleteSFTP = $sftp->deleteFile($Remote_File);
            if (!$esitoDeleteSFTP) {
                $this->setErrMessage("Errore nella cancellazione del file " . $Remote_File . "<br/>" . $sftp->getErrMessage());
                return "Error";
            }
            return "Ok";
        } else {
            $ftp_conn = ftp_connect($server, $porta) or die("Could not connect to $ftp_server");
            if (!ftp_login($ftp_conn, $ftpParm['UTENTE'], $ftpParm['PASSWORD'])) {
                $this->setErrMessage("Impossibile connettersi con l'utente FTP " . $ftpParm['UTENTE']);
                return "Error";
            }
            ftp_pasv($ftp_conn, true);
            if (!ftp_delete($ftp_conn, $file)) {
                $this->setErrMessage("Could not delete $file");
                return false;
            }
            ftp_close($ftp_conn);
        }
        return "Ok";
    }

    /**
     * La funzione ritorna l'elenco completo dei files presenti in una directory
     * 
     * @param type $ftpParm
     * @return string|boolean
     */
    public function dir($ftpParm, &$list) {
        if (!isset($ftpParm["DirArg"])) {
            $ftpParm["DirArg"] = '';
        }
        $dir_arg = $ftpParm["DirArg"];

        if ($ftpParm['SERVER'] == '') {
            $this->setErrMessage("Server FTP non configurato");
            return "Inactive";
        }

        $dir_search = $dir_arg;
        if ($ftpParm['ROOTPATH'] != '') {
            $dir_search = $ftpParm['ROOTPATH'] . "/" . $dir_arg;
        }
        $ftp_server = $ftpParm['SERVER'];
        list($server, $porta) = explode(":", $ftp_server);
        if ($ftpParm['TIPOCONNESSIONE'] == 'SFTP') {
            include_once (ITA_LIB_PATH . '/itaPHPCore/itaSFtpUtils.class.php');
            $sftp = new itaSFtpUtils();
            $sftp->setParameters($ftp_server, $ftpParm['UTENTE'], $ftpParm['PASSWORD']);
            $esitoListFile = $sftp->listOfFiles($dir_search);
            if (!$esitoListFile) {
                $this->setErrMessage("Errore nella connessione alla directory " . $dir_search . "<br/>" . $sftp->getErrMessage());
                return "Error";
            }
            $list = $sftp->getResult();
            return "Ok";
        } else {
            $ftp_conn = ftp_connect($server, $porta) or die("Could not connect to $ftp_server");
            if (!ftp_login($ftp_conn, $ftpParm['UTENTE'], $ftpParm['PASSWORD'])) {
                $this->setErrMessage("Impossibile connettersi con l'utente FTP " . $ftpParm['UTENTE']);
                return "Error";
            }
            ftp_pasv($ftp_conn, true);
            $list = ftp_nlist($ftp_conn, $dir_search);
        }

        if (!$list) {
            $this->setErrMessage("Nessun file trovato");
            return true;
        }
        ftp_close($ftp_conn);
        return "Ok";
    }

    public function getFile($ftpParm) {
        if ($ftpParm["RemoteFile"] == '') {
            $this->setErrMessage("File Remoto non configurato nei parametri");
            return "Error";
        }
        $Remote_File = $ftpParm["RemoteFile"];

        if ($ftpParm["LocalFile"] == '') {
            $this->setErrMessage("File Locale non configurato nei parametri");
            return "Error";
        }
        $Local_File = $ftpParm["LocalFile"];

        if ($ftpParm['SERVER'] == '') {
            $this->setErrMessage("Server FTP non configurato nei parametri");
            return "Inactive";
        }
        if (isset($ftpParm['ROOTPATH']) && $ftpParm['ROOTPATH']) {
            $Remote_File = $ftpParm['ROOTPATH'] . "/" . $ftpParm["RemoteFile"]; //default
            if (strpos($ftpParm['ROOTPATH'], "/") === false) {
                $Remote_File = $ftpParm['ROOTPATH'] . "/" . $ftpParm["RemoteFile"];
            }
            if (strpos($ftpParm['ROOTPATH'], "/") == strlen($ftpParm['ROOTPATH']) - 1) {
                $Remote_File = $ftpParm['ROOTPATH'] . $ftpParm["RemoteFile"];
            }
        }

        $ftp_server = $ftpParm['SERVER'];
        list($server, $porta) = explode(":", $ftp_server);
        if ($ftpParm['TIPOCONNESSIONE'] == 'SFTP') {
            include_once (ITA_LIB_PATH . '/itaPHPCore/itaSFtpUtils.class.php');
            $sftp = new itaSFtpUtils();
            $sftp->setParameters($ftp_server, $ftpParm['UTENTE'], $ftpParm['PASSWORD']);
            $esitoGetFile = $sftp->downloadFile($Remote_File);
            if (!$esitoGetFile) {
                $this->setErrMessage("Errore nello scarico del file " . $Remote_File . "<br/>" . $sftp->getErrMessage());
                return "Error";
            }
            file_put_contents($Local_File, $sftp->getResult());
            return "Ok";
        } else {
            $ftp_conn = ftp_connect($server, $porta) or die("Could not connect to $ftp_server");
            if (!ftp_login($ftp_conn, $ftpParm['UTENTE'], $ftpParm['PASSWORD'])) {
                $this->setErrMessage("Impossibile connettersi con l'utente FTP " . $ftpParm['UTENTE']);
                return "Error";
            }
            ftp_pasv($ftp_conn, true);
            if (!ftp_get($ftp_conn, $Local_File, $Remote_File, FTP_BINARY)) {
                $list = ftp_nlist($ftp_conn, ".");
                if (!$list) {
                    return "file n/a";
                }
                if (!array_search($Remote_File, $list)) {
                    return "file n/a";
                }
                $this->setErrMessage("Errore nel download del file " . $Remote_File);
                return "Error";
            }
            ftp_close($ftp_conn);
        }
        return "Ok";
    }

    public function getFileSize($ftpParm) {
        if ($ftpParm["RemoteFile"] == '') {
            $this->setErrMessage("File Remoto non configurato nei parametri");
            return "Error";
        }
        $Remote_File = $ftpParm["RemoteFile"];

        if ($ftpParm['SERVER'] == '') {
            $this->setErrMessage("Server FTP non configurato nei parametri");
            return "Inactive";
        }

//        if (strpos($ftpParm['ROOTPATH'], "/") === false) {
//            $Remote_File = $ftpParm['ROOTPATH'] . "/" . $ftpParm["RemoteFile"];
//        }
//        if (strpos($ftpParm['ROOTPATH'], "/") == strlen($ftpParm['ROOTPATH']) - 1) {
//            $Remote_File = $ftpParm['ROOTPATH'] . $ftpParm["RemoteFile"];
//        }
        $ftp_server = $ftpParm['SERVER'];
        list($server, $porta) = explode(":", $ftp_server);
        if ($ftpParm['TIPOCONNESSIONE'] == 'SFTP') {
            include_once (ITA_LIB_PATH . '/itaPHPCore/itaSFtpUtils.class.php');
            $sftp = new itaSFtpUtils();
            $sftp->setParameters($ftp_server, $ftpParm['UTENTE'], $ftpParm['PASSWORD']);
            $size = $sftp->getFileSize($Remote_File);
            if (!$size) {
                $this->setErrMessage("Errore nel leggere la dimensione del file " . $Remote_File . "<br/>" . $sftp->getErrMessage());
                return "Error";
            }
        } else {
            $ftp_conn = ftp_connect($server, $porta) or die("Could not connect to $ftp_server");
            if (!ftp_login($ftp_conn, $ftpParm['UTENTE'], $ftpParm['PASSWORD'])) {
                $this->setErrMessage("Impossibile connettersi con l'utente FTP " . $ftpParm['UTENTE']);
                return "Error";
            }
            ftp_pasv($ftp_conn, true);
            $size = ftp_size($ftp_conn, $Remote_File);
            ftp_close($ftp_conn);
        }
        if ($size < 0) {
            $this->setErrMessage("Errore nella lettura della dimensione del file trasferito " . $Remote_File);
            return "Error";
        }
        return $size;
    }

    public function putFile($ftpParm, $chunk = false) {
        if ($ftpParm["RemoteFile"] == '') {
            $this->setErrMessage("File Remoto non configurato nei parametri");
            return "Error";
        }
        $Remote_File = $ftpParm["RemoteFile"];
        if ($ftpParm["LocalFile"] == '') {
            $this->setErrMessage("File Locale non configurato nei parametri");
            return "Error";
        }
        $Local_File = $ftpParm["LocalFile"];

        if ($ftpParm['SERVER'] == '') {
            $this->setErrMessage("Server FTP non configurato nei parametri");
            return "Inactive";
        }

        //controllo eventuale roothpath e formato in cui sono scritti path e file
        if ($ftpParm['ROOTPATH'] != "") {
            if (substr($ftpParm['ROOTPATH'], 0, 1) != "/") {
                $ftpParm['ROOTPATH'] = "/" . $ftpParm['ROOTPATH'];
            }
            if (substr($ftpParm['ROOTPATH'], -1, 1) != "/") {
                $ftpParm['ROOTPATH'] = $ftpParm['ROOTPATH'] . "/";
            }
            //a questo punto la path ha di sicuro il formato /path/
            $Remote_File = str_replace("/", "", $Remote_File); //elimino i separatori nel nome del file
            $Remote_File = $ftpParm['ROOTPATH'] . $Remote_File;
        }

        $ftp_server = $ftpParm['SERVER'];
        list($server, $porta) = explode(":", $ftp_server);
        if ($ftpParm['TIPOCONNESSIONE'] != 'SFTP') {
            $ftp_conn = ftp_connect($server, $porta) or die("Could not connect to $ftp_server");
            if (!ftp_login($ftp_conn, $ftpParm['UTENTE'], $ftpParm['PASSWORD'])) {
                $this->setErrMessage("Impossibile connettersi con l'utente FTP " . $ftpParm['UTENTE']);
                return "Error";
            }
            ftp_pasv($ftp_conn, true);
        }

        include_once (ITA_LIB_PATH . '/itaPHPCore/itaSFtpUtils.class.php');
        $sftp = new itaSFtpUtils();
        $sftp->setParameters($ftp_server, $ftpParm['UTENTE'], $ftpParm['PASSWORD']);

        if (!$chunk) {
            if ($ftpParm['TIPOCONNESSIONE'] == 'SFTP') {
                $esitoInvioSFTP = $sftp->uploadFile($Remote_File, $Local_File);
                if (!$esitoInvioSFTP) {
                    $this->setErrMessage("Errore nell'upload del file " . $Remote_File . "<br/>" . $sftp->getErrMessage());
                    return "Error";
                }
                return "Ok";
            } else {
                if (!ftp_put($ftp_conn, $Remote_File, $Local_File, FTP_BINARY)) {
                    $this->setErrMessage("Errore nell'upload del file " . $Remote_File);
                    return "Error";
                }
            }
        } else {
            $startpos = 0;
            $i = 0;
            $size = filesize($Local_File);
            $handle = @fopen($Local_File, "r");
            if (!$handle) {
                $this->setErrMessage("Errore nell'apertura del file " . $ftpParm["LocalFile"]);
                return false;
            }
            if ($ftpParm['TIPOCONNESSIONE'] == 'SFTP') {
                while (!feof($handle)) {
                    $buffer = fread($handle, $chunk);
                    $esitoInvioSFTP = $sftp->uploadFileChunk($Remote_File, $buffer, $startpos);
                    if (!$esitoInvioSFTP) {
                        $this->setErrMessage("$i - Errore nell'upload del file " . $Remote_File . "<br/>" . $sftp->getErrMessage());
                        return false;
                    }
                    $startpos += $chunk;
                    $i++;
                    if ($startpos > $size) {
                        break; //controllo di sicurezza per evitare loop
                    }
                }
            } else {
                while (!feof($handle)) {
                    $buffer = fread($handle, $chunk);
                    $ftp_call = 'ftp://' . $ftpParm['UTENTE'] . ':' . $ftpParm['PASSWORD'] . '@' . $ftp_server . '/' . $Remote_File;
                    if (!file_put_contents($ftp_call, $buffer, FILE_APPEND)) {
                        $this->setErrMessage("Errore nell'upload del file " . $Remote_File);
                        return "Error";
                    }
                    $startpos += $chunk;
                    $i++;

                    if ($startpos > $size) {
                        break;
                    }
                }
            }
            fclose($handle);
        }
        if ($ftpParm['TIPOCONNESSIONE'] != 'SFTP') {
            ftp_close($ftp_conn);
        }
        return "Ok";
    }

}
