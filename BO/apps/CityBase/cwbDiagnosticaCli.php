<?php

include_once dirname(__FILE__) . '/../../lib/itaPHPUnit/itaPHPUnit.class.php';

class cwbDiagnosticaCli {

    const APCU_MIN_VERSION = '4.0.11';
    const OPENSSL_MIN_VERSION = 'OpenSSL/1.0.1e';

    private $root;
    private $paths;
    private $configFiles;
    private $tempDirFields;
    private $cacheFields;

    public function __construct() {
        //ROOT DI INSTALLAZIONE
        $this->root = realpath(dirname(__FILE__) . "/../../");

        //ARRAY DEI PATH DA CONTROLLARE
        $this->paths = array(
            $this->root . DIRECTORY_SEPARATOR . 'config',
            $this->root . DIRECTORY_SEPARATOR . 'wsrest' . DIRECTORY_SEPARATOR . 'routes'
        );

        //ARRAY DEI FILE DI CONFIGURAZIONE DA CONTROLLARE
        $this->configFiles = array(
            $this->root . DIRECTORY_SEPARATOR . 'Config.inc.php',
            $this->root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.ini',
            $this->root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'connections.ini',
            $this->root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'itaPath.ini',
            $this->root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'enti_save.ini',
            $this->root . DIRECTORY_SEPARATOR . 'wsrest' . DIRECTORY_SEPARATOR . 'Config.inc.php'
        );

        //ARRAY DEI PATH TEMPORANEI VALORIZZATI ALL'INTERNO DI FILE DI CONFIGURAZIONE
        $this->tempDirFields = array(
            array(
                "File" => $this->root . DIRECTORY_SEPARATOR . 'Config.inc.php',
                "Type" => "Constant",
                "Field" => "ITA_BASE_PATH"
            ),
            array(
                "File" => $this->root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.ini',
                "Type" => "ini",
                "Field" => "localPath"
            )
        );

        //POSIZIONE DEI DATI DI CACHE
        $this->cacheFields = array(
            "File" => $this->root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.ini',
            "CacheType" => "type",
            "CachePath" => "root"
        );
    }

    private function checkPaths() {
        $return = array();
        $return['Result'] = true;
        $return['Paths'] = array();

        $phpUnit = new itaPHPUnit(ITA_BASE_PATH . '/phpunitupdate.xml');
        $moduleResult = $phpUnit->executeTest('ModuleLoadTest');

        foreach ($this->paths as $path) {
            if (file_exists($path) && is_dir($path)) {
                $return['Paths'][$path] = "OK";
            } else {
                $return['Result'] = false;
                $return['Paths'][$path] = "KO - Non esiste o non e' una directory";
            }
        }
        return $return;
    }

    private function checkConfigFiles() {
        $return = array();
        $return['Result'] = true;
        $return['Files'] = array();

        error_reporting(E_ALL & ~E_NOTICE);
        foreach ($this->configFiles as $configFile) {
            if (file_exists($configFile)) {
                if (strtolower(substr($configFile, -3, 3)) === 'ini') {
                    if (parse_ini_file($configFile) !== false) {
                        $return['Files'][$configFile] = "OK";
                    } else {
                        $return['Result'] = false;
                        $return['Files'][$configFile] = "KO - Impossibile leggere del file ini";
                    }
                } elseif (strtolower(substr($configFile, -3, 3)) === 'php') {
                    if (include_once($configFile)) {
                        $return['Files'][$configFile] = "OK";
                    } else {
                        $return['Result'] = false;
                        $return['Files'][$configFile] = "KO - Impossibile importare il file php";
                    }
                }
            } else {
                $return['Result'] = false;
                $return['Files'][$configFile] = "KO - File inesistente";
            }
        }
        error_reporting(E_ALL);

        return $return;
    }

    private function checkTempDirs() {
        $return = array();
        $return['Result'] = true;
        $return['Paths'] = array();
        foreach ($this->tempDirFields as $field) {
            if (file_exists($field['File']) && is_file($field['File'])) {
                switch ($field['Type']) {
                    case "Constant":
                        if (!include_once($field['File'])) {
                            $return['Result'] = false;
                            $return['Paths'][$field['File'] . " > " . $field['Field']] = "KO - Non riesco ad importare il file di configurazione";
                            $tempDir = false;
                        } else {
                            $tempDir = constant($field['Field']);
                        }
                        break;
                    case "ini":
                        $confFile = parse_ini_file($field['File']);
                        if ($confFile === false) {
                            $return['Result'] = false;
                            $return['Paths'][$field['File'] . " > " . $field['Field']] = "KO - Non riesco ad importare il file di configurazione";
                            $tempDir = false;
                        } else {
                            $tempDir = $confFile[$field['Field']];
                        }
                        break;
                    default:
                        $return['Result'] = false;
                        $return['Paths'][$field['File'] . " > " . $field['Field']] = "KO - Non so come leggere il campo";
                        $tempDir = false;
                        break;
                }

                if (file_exists($tempDir) && is_dir($tempDir)) {
                    if (is_writable($tempDir)) {
                        $return['Paths'][$field['File'] . " > " . $field['Field']] = "OK - $tempDir";
                    } else {
                        $return['Result'] = false;
                        $return['Paths'][$field['File'] . " > " . $field['Field']] = "KO - Non si hanno i permessi di scrittura su $tempDir";
                    }
                } elseif ($tempDir !== false) {
                    $return['Result'] = false;
                    $return['Paths'][$field['File'] . " > " . $field['Field']] = "KO - La directory $tempDir non esiste";
                }
            } else {
                $return['Result'] = false;
                $return['Paths'][$field['File'] . " > " . $field['Field']] = "KO - Il file di configurazione non esiste";
            }
        }
        return $return;
    }

    private function checkCache() {
        $return = array();
        $return['Result'] = true;
        $return['Status'] = "OK";
        if (file_exists($this->cacheFields['File']) && is_file($this->cacheFields['File'])) {
            $confFile = parse_ini_file($this->cacheFields['File']);
            $cacheType = $confFile[$this->cacheFields['CacheType']];
            $cachePath = $confFile[$this->cacheFields['CachePath']];

            if ($cacheType == 'FILE') {
                if (file_exists($cachePath) && is_dir($cachePath)) {
                    if (!is_writable($cachePath)) {
                        $return['Result'] = false;
                        $return['Status'] = "KO - La directory della cache non e' scrivibile";
                    }
                } else {
                    $return['Result'] = false;
                    $return['Status'] = "KO - La directory della cache non esiste";
                }
            } else {
                if (!function_exists('apc_cache_info')) {
                    $return['Result'] = false;
                    $return['Status'] = "KO - APC non risulta essere attivo";
                }
            }
        } else {
            $return['Result'] = false;
            $return['Status'] = "KO - Il file di configurazione non esiste";
        }
        return $return;
    }

    private function checkLoadedModules() {
        $return = array();
        $return['Result'] = true;
        $return['Status'] = "OK";
        $phpUnit = new itaPHPUnit(ITA_BASE_PATH . '/phpunitupdate.xml');
        $pathResult = $phpUnit->executeTest('PathTest');

        if (count($pathResult['failed']) > 0) {
            $return['Result'] = false;
            $return['Status'] = "KO - Path non validi: ";
            foreach ($pathResult['failed'] as $pathKO) {
                $return['Status'] .= "\n$$pathKO";
            }
        }
        return $return;
    }

    private function checkApcuVersion() {
        $return = array();
        $return['Result'] = true;
        $return['Status'] = "OK";

        $apcuVersion = phpversion('apcu');
        if (version_compare($apcuVersion, self::APCU_MIN_VERSION, '<')) {
            $return['Result'] = false;
            $return['Status'] = "KO - Versione di apcu errata: $apcuVersion  (Richiesta versione: " . self::APCU_MIN_VERSION . ')';
        } else {
            $return['Status'] = "OK (Versione apcu: $apcuVersion)";
        }

        return $return;
    }

    private function checkCurlVersion() {
        $return = array();
        $return['Result'] = true;
        $return['Status'] = "OK";

        $curlInfo = curl_version();
        $curlVersion = $curlInfo['version'];
        $sslVersion = $curlInfo['ssl_version'];
        if (version_compare($sslVersion, self::OPENSSL_MIN_VERSION, '<')) {
            $return['Result'] = false;
            $return['Status'] = "KO - Versione di OpenSSL errata: $sslVersion  (Richiesta versione: " . self::OPENSSL_MIN_VERSION . ')';
        } else {
            $return['Status'] = "OK (Versione cURL: $curlVersion - Versione OpenSSL: $sslVersion)";
        }

        return $return;
    }

    public function callCli() {
        echo "Controllo dei path predefiniti:\r\n";
        $paths = $this->checkPaths();
        foreach ($paths['Paths'] as $path => $result) {
            echo str_pad($path . ":\t", 70, '.', STR_PAD_LEFT) . $result . "\r\n";
        }

        echo "\r\n\r\nControllo dei file di configurazione:\r\n";
        $files = $this->checkConfigFiles();
        foreach ($files['Files'] as $file => $result) {
            echo str_pad($file . ":\t", 70, '.', STR_PAD_LEFT) . $result . "\r\n";
        }

        echo "\r\n\r\nControllo delle directory temporanee impostate nei file di configurazione:\r\n";
        $dirs = $this->checkTempDirs();
        foreach ($dirs['Paths'] as $dir => $result) {
            echo str_pad($dir . ":\t", 70, '.', STR_PAD_LEFT) . $result . "\r\n";
        }

        echo "\r\n\r\nControllo moduli caricati: ";
        $loadedModules = $this->checkLoadedModules();
        echo $loadedModules['Status'] . "\r\n";

        echo "\r\n\r\nVerifica version estensione apcu: ";
        $apcuVersion = $this->checkApcuVersion();
        echo $apcuVersion['Status'] . "\r\n";

        echo "\r\n\r\nVerifica versione OpenSSL: ";
        $curlVersion = $this->checkCurlVersion();
        echo $curlVersion['Status'] . "\r\n";

        echo "\r\n\r\nControllo se il sistema di caching e' attivo: ";
        $cache = $this->checkCache();
        echo $cache['Status'] . "\r\n";

        echo "\r\n\r\nStato complessivo controllo di primo livello: ";
        if ($paths['Result'] && $files['Result'] && $dirs['Result'] && $cache['Result']) {
            echo "OK";
        } else {
            echo "KO";
        }
        echo str_repeat("\r\n", 3);
    }

}

?>