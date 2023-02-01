<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    26.03.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
class devLib {

    /**
     * Libreria di funzioni Generiche e Utility per Sviluppo
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    public $ITALSOFT_DB;

    public function setITALSOFTDB($ITALSOFT_DB) {
        $this->ITALSOFT_DB = $ITALSOFT_DB;
    }

    public function getITALSOFTDB() {
        if (!$this->ITALSOFT_DB) {
            try {
                $this->ITALSOFT_DB = ItaDB::DBOpen('italsoft', '');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALSOFT_DB;
    }

    public function setITALWEB($ITALWEB) {
        $this->ITALWEB = $ITALWEB;
    }

    public function getITALWEB() {
        if (!isSet($this->ITALWEB)) {
            try {
                $this->ITALWEB = ItaDB::DBOpen('ITALWEB');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALWEB;
    }

    public function getIta_config($codice, $tipo = 'codice', $multi = true) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ita_config WHERE CLASSE='$codice'";
        } else if ($tipo == 'tutti') {
            $sql = "SELECT * FROM ita_config";
        } else {
            $sql = "SELECT * FROM ita_config WHERE ROWID=$codice";
            $multi = false;
        }
        $tabella_tab = ItaDB::DBSQLSelect($this->getITALSOFTDB(), $sql, $multi);
        return $tabella_tab;
    }

    public function getIta_config_ini($codice, $tipo = 'codice', $multi = true) {
        $ita_config_tab = array();
        if ($tipo == 'codice') {
            if (!file_exists(ITA_BASE_PATH . "/apps/Ambiente/resources/$codice.ini")) {
                return false;
            }
            $ita_config_tab[$codice] = parse_ini_file(ITA_BASE_PATH . "/apps/Ambiente/resources/$codice.ini", true);
        } else if ($tipo == 'tutti') {
            foreach (glob(ITA_BASE_PATH . "/apps/Ambiente/resources/*.ini") as $file) {
                $ita_config_tab[basename($file, '.ini')] = parse_ini_file($file, true);
            }
        }

        $tabella_tab = array();

        foreach ($ita_config_tab as $classe => $ita_config_rec) {
            $result = array(
                'CLASSE' => $classe,
                'PARAMETRI' => array()
            );
            $result['PARAMETRI'][]= array(
                    "CHIAVE" => envLib::CHIAVE_PARAM_CLASS_DESC,
                    "DESC" => "DESCRIZIONE ISTANZA PARAMETRO",
                    "INPUT_TYPE" => "text",
                    "REQUIRED" => "1",
                    "DEFAULT" => ""
            );
            foreach ($ita_config_rec as $node => $value) {
                if ($node == 'Config') {
                    $result['DESCRIZIONE'] = $value['DESCRIZIONE'];
                    $result['DATACREA'] = $value['DATACREA'];
                    $result['DATAMOD'] = $value['DATAMOD'];
                    $result['DATAANN'] = $value['DATAANN'];
                    $result['MULTIISTANZA'] = $value['MULTIISTANZA'];
                } else {
                    $result['PARAMETRI'][] = array(
                        'CHIAVE' => $node,
                        'DESC' => $value['DESC'],
                        'INPUT_TYPE' => $value['INPUT_TYPE'],
                        'DEFAULT' => $value['DEFAULT']
                    );
                }
            }
            $result['PARAMETRI'] = serialize($result['PARAMETRI']);
            $tabella_tab[] = $result;
        }

        return $multi ? $tabella_tab : $tabella_tab[0];
    }

    public function setIta_config_ini($ita_config_rec) {
        $ini = array(
            'Config' => array(
                'DESCRIZIONE' => $ita_config_rec['DESCRIZIONE'],
                'DATACREA' => $ita_config_rec['DATACREA'],
                'DATAMOD' => $ita_config_rec['DATAMOD'],
                'DATAANN' => $ita_config_rec['DATAANN']
            )
        );

        $parametri_tab = unserialize($ita_config_rec['PARAMETRI']);

        foreach ($parametri_tab as $parametri_rec) {
            $ini[$parametri_rec['CHIAVE']] = array(
                'DESC' => $parametri_rec['DESC'],
                'DEFAULT' => $parametri_rec['DEFAULT']
            );
        }

        itaLib::writeIniFile(ITA_BASE_PATH . "/apps/Ambiente/resources/{$ita_config_rec['CLASSE']}.ini", $ini);
    }

    public function getEnv_config($codice, $tipo = 'codice', $chiave = '', $multi = true) {
        
        // Se presenti le configurazioni per ambiente (chiave 'test' passata in GET),
        // controlla se sono presenti delle configurazioni locali, altrimenti va in fallback sulle condizioni sotto
        if (isset($_GET['test']) && $_GET['test']) {
            $localConfig = ITA_BASE_PATH . '/config.' . $_GET['test'] . "/params.local/$codice.ini";
            if (file_exists($localConfig)) {
                $result = parse_ini_file($localConfig, true);
                if (array_key_exists($chiave, $result)) {
                    return $result[$chiave];
                }            
            }
        }
        
        // Se presenti le configurazioni locali per 'ditta', prende quelle,
        // altrimenti va in fallback sulle condizioni sotto
        $localConfig = ITA_BASE_PATH . "/config.params.local/" . App::$utente->getKey('ditta') . "/$codice.ini";
        if (file_exists($localConfig)) {
            $result = parse_ini_file($localConfig, true);
            if (array_key_exists($chiave, $result)) {
                return $result[$chiave];
            }            
        }
        
        // Se presenti le configurazioni locali, prende quelle, altrimenti le legge dal DB        
        $localConfig = ITA_BASE_PATH . "/config.params.local/$codice.ini";
        if (file_exists($localConfig)) {
            $result = parse_ini_file($localConfig, true);
            if (array_key_exists($chiave, $result)) {
                return $result[$chiave];
            }            
        }
        
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ENV_CONFIG WHERE CLASSE='$codice'";
            if ($chiave != '') {
                $sql.=" AND CHIAVE='$chiave'";
            }
        } else {
            $sql = "SELECT * FROM ENV_CONFIG WHERE ROWID=$codice";
            $multi = false;
        }
        $tabella_tab = ItaDB::DBSQLSelect($this->getITALWEB(), $sql, $multi);
        return $tabella_tab;
    }

    public function getEnv_config_global_ini($classe, $chiave = '') {
        $ita_config_global_ini_tab = array();
        $global_file = ITA_CONFIG_PATH . "/env_config/{$classe}_GLOBAL.ini";
        if (!file_exists($global_file)) {
            return false;
        }
        $ita_config_global_ini_tab = parse_ini_file($global_file, true);
        return $ita_config_global_ini_tab;
    }

    public function GetIta_gestreport($codice = '', $tipo = '', $multi = true) {
        $sql = "SELECT * FROM ita_gestreport ORDER BY CATEGORIA ASC, SEQUENZA ASC";
        if ($tipo == 'rowid') {
            $sql = "SELECT * FROM ita_gestreport WHERE ROWID = $codice";
            $multi = false;
        } else if ($tipo == 'codice') {
            $sql = "SELECT * FROM ita_gestreport WHERE CODICE = '$codice'";
        } else if ($tipo == 'categoria') {
            $sql = "SELECT * FROM ita_gestreport WHERE CATEGORIA = '$codice'";
        }
        $tabella_tab = ItaDB::DBSQLSelect($this->getITALSOFTDB(), $sql, $multi);
        return $tabella_tab;
    }

    public function GetRepGest($codice = '', $tipo = '', $multi = true) {
        $sql = "SELECT * FROM REP_GEST ORDER BY CATEGORIA ASC, SEQUENZA ASC";
        if ($tipo == 'rowid') {
            $sql = "SELECT * FROM REP_GEST WHERE ROWID = $codice";
            $multi = false;
        } else if ($tipo == 'codice') {
            $sql = "SELECT * FROM REP_GEST WHERE CODICE = '$codice'";
        }
        $tabella_tab = ItaDB::DBSQLSelect($this->getITALWEB(), $sql, $multi);
        return $tabella_tab;
    }

    public function getGenericTab($sql, $tipoDB = 'ITALSOFTDB', $multi = true) {
        if ($tipoDB == 'ITALSOFTDB') {
            $tabella_tab = ItaDB::DBSQLSelect($this->getITALSOFTDB(), $sql, $multi);
        } else if ($tipoDB == 'ITALWEB') {
            $tabella_tab = ItaDB::DBSQLSelect($this->getITALWEB(), $sql, $multi);
        }
        return $tabella_tab;
    }

    public function array_sort($array, $on, $order = SORT_ASC) {
        $new_array = array();
        $sortable_array = array();
        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }
            switch ($order) {
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
                default:
                    asort($sortable_array);
                    break;
            }
            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }
        return $new_array;
    }

}

?>