<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Config
 *
 * @author michele
 */
class Config {

    static public $itaPath = '';
    static public $config = '';
    static public $enti = '';
    static public $printers = '';
    static public $apps = '';
    static public $models = array();

    static function load() {
        // Carico i percorsi dei file di configurazione
        self::$itaPath = parse_ini_file(ITA_CONFIG_PATH . '/itaPath.ini', true);
        if (!self::$itaPath) {
            Out::msgStop("Errore", "Lettura itaPath.ini fallita.");
            return false;
        }

        /*
         * Caricamento AppDefinitions standard
         */
        $appDefinitions = include ITA_LIB_PATH . '/AppDefinitions/AppDefinitions.php';
        if ($appDefinitions && isset(self::$itaPath['primary'])) {
            foreach ($appDefinitions as $appRoute => $appDir) {
                self::$itaPath['appRoute'][$appRoute] = self::$itaPath['primary']['appRoute'] . "/$appDir";
                self::$itaPath['formRoute'][$appRoute] = self::$itaPath['primary']['formRoute'] . "/$appDir";
                self::$itaPath['reportRoute'][$appRoute] = self::$itaPath['primary']['reportRoute'] . "/$appDir";
            }
        }

        /*
         * Caricamento AppDefinitions custom
         */
        if (isset(self::$itaPath['appDefinitions'])) {
            foreach (self::$itaPath['appDefinitions'] as $appRoute => $appDir) {
                self::$itaPath['appRoute'][$appRoute] = self::$itaPath['primary']['appRoute'] . "/$appDir";
                self::$itaPath['formRoute'][$appRoute] = self::$itaPath['primary']['formRoute'] . "/$appDir";
                self::$itaPath['reportRoute'][$appRoute] = self::$itaPath['primary']['reportRoute'] . "/$appDir";
            }
        }

        self::$config = parse_ini_file(ITA_CONFIG_PATH . '/config.ini', true);
        if (!self::$config) {
            Out::msgStop("Errore", "Lettura Config.ini fallita.");
            return false;
        }
        /*
         * Caricamento nuovi valori di default
         */

        $configDefaults = array(
            'renderBackEnd' => array(
                'generator' => 'local/xml',
                'localPath' => ITA_BASE_PATH,
                'altGenerator' => 'dbms/table'
            ),
            'modelBackEnd' => array(
                'php' => ITA_BASE_PATH
            )
        );

        foreach ($configDefaults as $defaultSection => $defaultValues) {
            if (!isset(self::$config[$defaultSection])) {
                self::$config[$defaultSection] = $defaultValues;
                continue;
            }

            foreach ($defaultValues as $paramKey => $paramValue) {
                if (!isset(self::$config[$defaultSection][$paramKey])) {
                    self::$config[$defaultSection][$paramKey] = $paramValue;
                }
            }
        }

        if (file_exists(ITA_CONFIG_PATH . '/enti.ini')) {

            self::$enti = parse_ini_file(ITA_CONFIG_PATH . '/enti.ini', true);
            if (!self::$enti) {
                Out::msgStop("Errore", "Lettura enti.ini fallita.");
                return false;
            }
        } else {
            self::$enti = array();
            try {
               
                $itaEngineDB = ItaDB::DBOpen('ITALWEBDB', '');
                 
                $sql = "SELECT * FROM DOMAINS ORDER BY SEQUENZA";
                $Domains_tab = ItaDB::DBSQLSelect($itaEngineDB, $sql, true);

                if ($Domains_tab) {
                    foreach ($Domains_tab as $key => $Domains_rec) {
                        self::$enti[$Domains_rec['DESCRIZIONE']] = array(
                            'codice' => $Domains_rec['CODICE'],
                            'riservato' => $Domains_rec['RISERVATO']
                        );
                    }
                }
            } catch (Exception $e) {
                            
            }
        }
        

        self::$printers = parse_ini_file(ITA_CONFIG_PATH . '/printers.ini', true);
        if (!self::$printers) {
            Out::msgStop("Errore", "Lettura printers.ini fallita.");
            return false;
        }
        return true;
    }

    /*
     * Lettura puntuale di config.ini
     * 
     */

    public static function loadConfig() {
        self::$config = parse_ini_file(ITA_CONFIG_PATH . '/config.ini', true);
        if (!self::$config) {
            return false;
        }
        return true;
    }

    static function getConf($key) {
        $tmp = explode('.', $key);
        return (isSet(self::$config[$tmp[0]][$tmp[1]]) ? self::$config[$tmp[0]][$tmp[1]] : null);
    }

    /**
     *  Restituice la path delle applicazioni.
     *
     * @param string $key Chiave per l'estrazione del valore Es.: (path.path)
     */
    static function getPath($key) {
        $tmp = explode('.', $key);
        return self::$itaPath[$tmp[0]][$tmp[1]];
    }

    /**
     * Restituisce una sezione di itaPath
     * @param string $key Chiave
     * @return array 
     */
    static function getPathSection($key) {
        return self::$itaPath[$key];
    }

    static function getEnti() {
        return self::$enti;
    }

    static function getEnte($key) {
        $tmp = explode('.', $key);
        return self::$enti[$tmp[0]][$tmp[1]];
    }

    static function getPrinters() {
        return self::$printers;
    }

    static function getPrinter($key) {
        $tmp = explode('.', $key);
        return self::$printers[$tmp[0]][$tmp[1]];
    }
}
