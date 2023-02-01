<?php

class SdiFtpUtil {

    const PARAMETRO_PATH = 'path';
    const PARAMETRO_SUPPORTNAME = 'supportname';
    const PARAMETRO_FILELIST = 'filelist';
    const PARAMETRO_SIGNKEYPATH = 'signkeypath';
    const PARAMETRO_CRYPTKEYPATH = 'cryptkeypath';
    const PARAMETRO_CRYPTCERT = 'cryptcertpath';
    const PARAMETRO_CERTPWD = 'certpwd';
    const PARAMETRO_SUPPORTPATH = "supportpath";
    const PARAMETRO_CACERT = "cacertpath";
    const PARAMETRO_SIMULATION = "simulation";
    const PARAMETRO_NOVERIFY = "noverify";
    const PARAMETRO_EXCLUDEZIP = "excludezip";
    const PARAMETRO_ENCODING = "encoding";
    const PARAMETRO_VALID_CRED_FILTER = "filter_valid_cred";
    const ERR_MSG_PARAMETRO_PATH_MANCANTE = "Parametro PATH mancante";
    const ERR_MSG_PARAMETRO_SUPPORTNAME_MANCANTE = "Parametro SUPPORTNAME mancante";
    const ERR_MSG_PARAMETRO_FILELIST_MANCANTE = "Parametro FILELIST mancante";
    const ERR_MSG_PARAMETRO_SIGNKEYPATH_MANCANTE = "Parametro SIGNKEYPATH mancante";
    const ERR_MSG_PARAMETRO_CRYPTKEYPATH_MANCANTE = "Parametro CRYPTKEYPATH mancante";
    const ERR_MSG_PARAMETRO_CRYPTCERT_MANCANTE = "Parametro CRYPTCERT mancante";
    const ERR_MSG_PARAMETRO_CERTPWD_MANCANTE = "Parametro CERTPWD mancante";
    const ERR_MSG_PARAMETRO_FILELIST_MULTIPLI = "Parametro FILELIST multiplo";
    const ERR_MSG_OPENSSL_CRYPT = "Errore di crittografia del file";
    const ERR_MSG_OPENSSL_SIGN = "Errore di firma del file";
    const ERR_MSG_PARAMETRO_SUPPORTPATH_MANCANTE = "Parametro SUPPORTPATH mancante";
    const ERR_MSG_PARAMETRO_CACERT_MANCANTE = "Parametro CACERT mancante";
    const ERR_MSG_OPENSSL_DECRYPT = "Errore di decrittografia del file";
    const ERR_MSG_OPENSSL_VERIFY = "Errore di verifica firma del file";

    public static function CreateSupport($params) {
        $toReturn = array(
            'CODICE' => 0,
            'MESSAGGIO' => ''
        );

        // Leggere parametri dal file di configurazione            
        $path = $params[self::PARAMETRO_PATH];
        $supportname = $params[self::PARAMETRO_SUPPORTNAME];
        $filelist = $params[self::PARAMETRO_FILELIST];
        $signkeypath = $params[self::PARAMETRO_SIGNKEYPATH];
        $cryptkeypath = $params[self::PARAMETRO_CRYPTKEYPATH];
        $cryptcert = $params[self::PARAMETRO_CRYPTCERT];
        $certpwd = $params[self::PARAMETRO_CERTPWD];

        // Controlla parametri obbligatori          
        if (empty($path)) {
            $toReturn['CODICE'] = 1;
            $toReturn['MESSAGGIO'] = self::ERR_MSG_PARAMETRO_PATH_MANCANTE;
            return $toReturn;
        }
        if (empty($supportname)) {
            $toReturn['CODICE'] = 1;
            $toReturn['MESSAGGIO'] = self::ERR_MSG_PARAMETRO_SUPPORTNAME_MANCANTE;
            return $toReturn;
        }
        if (empty($filelist)) {
            $toReturn['CODICE'] = 1;
            $toReturn['MESSAGGIO'] = self::ERR_MSG_PARAMETRO_FILELIST_MANCANTE;
            return $toReturn;
        }
        if (empty($signkeypath)) {
            $toReturn['CODICE'] = 1;
            $toReturn['MESSAGGIO'] = self::ERR_MSG_PARAMETRO_SIGNKEYPATH_MANCANTE;
            return $toReturn;
        }
        if (empty($cryptkeypath)) {
            $toReturn['CODICE'] = 1;
            $toReturn['MESSAGGIO'] = self::ERR_MSG_PARAMETRO_CRYPTKEYPATH_MANCANTE;
            return $toReturn;
        }
        if (empty($cryptcert)) {
            $toReturn['CODICE'] = 1;
            $toReturn['MESSAGGIO'] = self::ERR_MSG_PARAMETRO_CRYPTCERT_MANCANTE;
            return $toReturn;
        }
        if (empty($certpwd)) {
            $toReturn['CODICE'] = 1;
            $toReturn['MESSAGGIO'] = self::ERR_MSG_PARAMETRO_CERTPWD_MANCANTE;
            return $toReturn;
        }

        // Parametri opzionali
        $excludeZip = isset($params[self::PARAMETRO_EXCLUDEZIP]) ? $params[self::PARAMETRO_EXCLUDEZIP] : 0;
        $encoding = isset($params[self::PARAMETRO_ENCODING]) ? $params[self::PARAMETRO_ENCODING] : 0;
        $validCredFilter = isset($params[self::PARAMETRO_VALID_CRED_FILTER]) ? $params[self::PARAMETRO_VALID_CRED_FILTER] : 0;

        try {
            //Calcola nomi file e percorsi               
            if (!self::EndsWith($path, DIRECTORY_SEPARATOR)) {
                $path .= DIRECTORY_SEPARATOR;
            }

            $supportPath = $path . $supportname . DIRECTORY_SEPARATOR;

            // Aggiunta dell'estensione al supporto zip o xml
            $supportFilename = $path . $supportname . ".zip";

            $names = explode(';', $filelist);

            //Controllo se si tratta di un supporto (excludeZip==0) oppure di un esito da non zippare (excludeZip==1)
            if ($excludeZip == 0) {
                $signedFileName = "";

                $quadraturaFilename = $supportPath . $supportname . ".xml";

                //Cancello lo Zip se già esiste
                if (file_exists($supportFilename)) {
                    unlink($supportFilename);
                }

                // Crea zip da inviare, contenente tutti i file del supporto
                $zip = new \ZipArchive();
                $result = $zip->open($supportFilename, \ZipArchive::CREATE);

                foreach ($names as $name) {
                    $zip->addFile($supportPath . $name, $name);
                }

                //Aggiunge nello zip il file di quadratura
                $zip->addFile($quadraturaFilename, $supportname . ".xml");
                $zip->close();
            } else {
                //Se non esegui lo zip, il filelist deve contenere un solo file
                if (count($names) == 1) {
                    //Verifica se il file deve cambiare nome
                    if ($path . $names[0] != $supportFilename) {
                        copy($path . $names[0], $supportFilename);
                    }
                } else {
                    $toReturn['CODICE'] = 1;
                    $toReturn['MESSAGGIO'] = self::ERR_MSG_PARAMETRO_FILELIST_MULTIPLI;
                    return $toReturn;
                }
            }

            $fileSigned = $supportFilename . ".p7m";
            
            $openssl = self::getOpenSSLCommand();            
            $opensslCommand = "$openssl smime " .
                    "-sign " .
                    "-in \"" . $supportFilename . "\" " .
                    "-outform der " .
                    "-binary " .
                    "-nodetach " .
                    "-out \"" . $fileSigned . "\" " .
                    "-signer \"" . $signkeypath . "\" " .
                    "-passin pass:" . $certpwd;

            shell_exec($opensslCommand);

            if (!file_exists($fileSigned)) {
                $toReturn['CODICE'] = 1;
                $toReturn['MESSAGGIO'] = self::ERR_MSG_OPENSSL_SIGN;
                return $toReturn;
            }

            $fileCrypted = $fileSigned . ".enc";
                        
            $opensslCommand = "$openssl smime " .
                    "-encrypt " .
                    "-in \"" . $fileSigned . "\" " .
                    "-outform der " .
                    "-binary " .
                    "-des3 " .
                    "-out \"" . $fileCrypted . "\" " .
                    "\"" . $cryptcert . "\"";

            shell_exec($opensslCommand);

            if (!file_exists($fileCrypted)) {
                $toReturn['CODICE'] = 1;
                $toReturn['MESSAGGIO'] = self::ERR_MSG_OPENSSL_CRYPT;
                return $toReturn;
            }

            // Rinomina file da zip.p7m.enc a .zip 
            unlink($supportFilename);
            unlink($fileSigned);
            rename($fileCrypted, $supportFilename);
        } catch (\Exception $ex) {
            $toReturn['CODICE'] = 1;
            $toReturn['MESSAGGIO'] = $ex->getMessage();
            return $toReturn;
        }

        $toReturn['PATH'] = $supportFilename;
        return $toReturn;
    }

    public static function OpenSupport($params) {
        $toReturn = array(
            'CODICE' => 0,
            'MESSAGGIO' => ''
        );

        // Leggere parametri dal file di configurazione
        $supportpath = $params[self::PARAMETRO_SUPPORTPATH];
        $supportname = $params[self::PARAMETRO_SUPPORTNAME];
        $cryptkeypath = $params[self::PARAMETRO_CRYPTKEYPATH];
        $certpwd = $params[self::PARAMETRO_CERTPWD];
        $cacert = $params[self::PARAMETRO_CACERT];

        // Parametri opzionali
        $simulation = isset($params[self::PARAMETRO_SIMULATION]) ? $params[self::PARAMETRO_SIMULATION] : 0;
        $excludeZip = isset($params[self::PARAMETRO_EXCLUDEZIP]) ? $params[self::PARAMETRO_EXCLUDEZIP] : 0;
        $noverify = isset($params[self::PARAMETRO_NOVERIFY]) ? $params[self::PARAMETRO_NOVERIFY] : 0;

        if (empty($supportpath)) {
            $toReturn['CODICE'] = 1;
            $toReturn['MESSAGGIO'] = self::ERR_MSG_PARAMETRO_SUPPORTPATH_MANCANTE;
            return $toReturn;
        }
        if (empty($supportname)) {
            $toReturn['CODICE'] = 1;
            $toReturn['MESSAGGIO'] = self::ERR_MSG_PARAMETRO_SUPPORTNAME_MANCANTE;
            return $toReturn;
        }
        if (empty($cryptkeypath)) {
            $toReturn['CODICE'] = 1;
            $toReturn['MESSAGGIO'] = self::ERR_MSG_PARAMETRO_CRYPTKEYPATH_MANCANTE;
            return $toReturn;
        }
        if (empty($cacert)) {
            $toReturn['CODICE'] = 1;
            $toReturn['MESSAGGIO'] = self::ERR_MSG_PARAMETRO_CACERT_MANCANTE;
            return $toReturn;
        }
        if (empty($certpwd)) {
            $toReturn['CODICE'] = 1;
            $toReturn['MESSAGGIO'] = self::ERR_MSG_PARAMETRO_CERTPWD_MANCANTE;
            return $toReturn;
        }

        try {
            //Calcola nomi file e percorsi               
            if (!self::EndsWith($supportpathPath, DIRECTORY_SEPARATOR)) {
                $supportpath .= DIRECTORY_SEPARATOR;
            }

            $supportFilename = $supportpath . $supportname;

            if ($simulation == 0) {

                $fileSigned = "";

                $tmp = pathinfo($supportFilename);
                if ($tmp['extension'] == "enc") {
                    $index = strpos($supportFilename, ".enc");
                    $fileSigned = substr($supportFilename, 0, $index);
                }
                
                $openssl = self::getOpenSSLCommand();
                $opensslCommand = "$openssl smime " .
                        "-decrypt " .
                        "-in \"" . $supportFilename . "\" " .
                        "-inform der " .
                        "-binary " .
                        "-out \"" . $fileSigned . "\" " .
                        "-recip \"" . $cryptkeypath . "\" " .
                        "-passin pass:" . $certpwd;

                shell_exec($opensslCommand);

                if (!file_exists($fileSigned)) {
                    $toReturn['CODICE'] = 1;
                    $toReturn['MESSAGGIO'] = self::ERR_MSG_OPENSSL_DECRYPT;
                    return $toReturn;
                }

                //Chiamata Openssl per verifica firma
                $tmp = pathinfo($fileSigned);
                if ($tmp['extension'] == "p7m") {
                    $index = strpos($fileSigned, ".p7m");
                    $supportFilename = substr($fileSigned, 0, $index);
                }
                                
                $opensslCommand = "$openssl smime " .
                        "-verify " .
                        "-in \"" . $fileSigned . "\" " .
                        "-inform der " .
                        "-binary " .
                        "-nodetach " .
                        "-out \"" . $supportFilename . "\" " .
                        "-CAfile \"" . $cacert . "\" ";

                if ($noverify == 1) {
                    $opensslCommand .= "-noverify";
                }

                $result = shell_exec($opensslCommand);

                // Controlla se deve essere controllata la firma
                if (!file_exists($supportFilename)) {
                    $toReturn['CODICE'] = 1;
                    $toReturn['MESSAGGIO'] = self::ERR_MSG_OPENSSL_VERIFY;
                    return $toReturn;
                }
            }

            $supportDirectory = "";

            if ($excludeZip == 0) {
                $tmp = pathinfo($supportFilename);
                if ($tmp['extension'] == "zip") {
                    $supportDirectory = $tmp['filename'];
                }
                $supportDirectory = $supportpath . $supportDirectory . DIRECTORY_SEPARATOR;

                // Estrai tutti i file nella directory del supporto
                $zip = new \ZipArchive();
                if ($zip->open($supportFilename)) {
                    $zip->extractTo($supportDirectory);
                    $zip->close();
                }
            } else {
                $supportDirectory = $supportFilename;
            }

            return $supportDirectory;
        } catch (\Exception $ex) {
            $toReturn['CODICE'] = 1;
            $toReturn['MESSAGGIO'] = $ex->getMessage();
            return $toReturn;
        }

        $toReturn['PATH'] = $supportDirectory;

        return $toReturn;
    }

    private static function EndsWith($Haystack, $Needle) {
        return strrpos($Haystack, $Needle) === strlen($Haystack) - strlen($Needle);
    }
    
    /**
     * Restituisce il percorso di OpenSSL secondo questi criteri:
     * 1) Se presente in config.ini, chiave OpenSSL.OpenSSLPath
     * 2) Se S.O. Windows, prende  /lib/bin/openssl/openssl.exe
     * 3) Assume "openssl" registrata su path
     * @return string
     */
    public static function getOpenSSLCommand() {
        $openSSLPath = App::getConf("OpenSSL.OpenSSLPath");
        if ($openSSLPath !== null && strlen(trim($openSSLPath)) > 0) {
            $command = '"' . $openSSLPath . '"';
        } elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
            $openSSLPath = ITA_BASE_PATH . '/lib/bin/openssl/openssl.exe';
            $command = '"' . $openSSLPath . '"';
        } else {
            $command = 'openssl';
        }              
        return $command;
    }
    
}
