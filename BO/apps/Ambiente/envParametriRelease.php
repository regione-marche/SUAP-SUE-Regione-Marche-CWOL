<?php

/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Documenti
 * @author     Carlo Iesari <carlo.iesari@italsoft.eu>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 */

include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

function envParametriRelease() {
    $envParametriRelease = new envParametriRelease();
    $envParametriRelease->parseEvent();
    return;
}

class envParametriRelease extends itaModel {

    public $ITALWEB_DB;
    public $nameForm = 'envParametriRelease';
    private $envKey = 'DEPLOY';
    private $releaseBranch = 'sviluppo-cw-';

    function __construct() {
        parent::__construct();

        $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->caricaParametri();
                Out::setFocus($this->nameForm . '_PARAM[depCurrentRelease]');
                break;

            case 'onChange':
                $param_tab = $_POST[$this->nameForm . '_PARAM'];
                $this->checkReleaseNumber($param_tab);
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiorna':
                        $param_tab = $_POST[$this->nameForm . '_PARAM'];

                        if (!$this->checkParametri($param_tab)) {
                            break;
                        }

                        if (!$this->salvaParametri($param_tab)) {
                            break;
                        }

                        Out::msgBlock($this->nameForm, 1200, false, 'Aggiornamento eseguito con successo.');
                        break;

                    case $this->nameForm . '_VisualizzaReleasePrecedenti':
                        $configFilepath = $this->getParametro('depPathConfigBuild');
                        $config = $this->readConfigFile($configFilepath);
                        if (!$config) {
                            break;
                        }

                        if (!$releaseBranches = $this->getReleasePrecedenti($config['DistFolder'])) {
                            break;
                        }

                        Out::msgInfo("Rami di release", implode("\n", $releaseBranches));
                        break;

                    case $this->nameForm . '_PARAM[depPathConfigBuild]_butt':
                        $configFilepath = $this->getParametro('depPathConfigBuild');
                        $configContent = file_get_contents($configFilepath);
                        Out::msgInfo('File di configurazione', "<pre>$configContent</pre>");
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_params');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function getParametri() {
        $return_tab = array();

        $devLib = new devLib();
        $envconfig_tab = $devLib->getEnv_config($this->envKey, 'codice');
        foreach ($envconfig_tab as $envconfig_rec) {
            $return_tab[$envconfig_rec['CHIAVE']] = $envconfig_rec['CONFIG'];
        }

        return $return_tab;
    }

    public function getParametro($key) {
        $envconfig_tab = $this->getParametri();
        return $envconfig_tab[$key];
    }

    public function caricaParametri() {
        $envconfig_tab = $this->getParametri();
        foreach ($envconfig_tab as $key => $value) {
            Out::valore($this->nameForm . "_PARAM[$key]", $value);
        }

        $this->checkReleaseNumber($envconfig_tab);
    }

    private function salvaParametri($param_tab) {
        $devLib = new devLib();

        foreach ($param_tab as $paramKey => $paramValue) {
            $param_rec = array();
            $param_rec['CHIAVE'] = $paramKey;
            $param_rec['CONFIG'] = $paramValue;
            $param_rec['CLASSE'] = $this->envKey;

            $envconfig_rec = $devLib->getEnv_config($this->envKey, 'codice', $paramKey, false);

            if ($envconfig_rec) {
                /*
                 * Update record.
                 */

                $param_rec['ROWID'] = $envconfig_rec['ROWID'];
                $update_info = sprintf('Oggetto: aggiornamento parametro %s.%s => %s.', $envconfig_rec['CLASSE'], $paramKey, $paramValue);
                if (!$this->updateRecord($this->ITALWEB_DB, 'ENV_CONFIG', $param_rec, $update_info)) {
                    return false;
                }
            } else {
                /*
                 * Insert record.
                 */

                $insert_Info = sprintf('Oggetto: inserimento nuovo parametro %s.%s => %s.', $envconfig_rec['CLASSE'], $paramKey, $paramValue);
                if (!$this->insertRecord($this->ITALWEB_DB, 'ENV_CONFIG', $param_rec, $insert_Info)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function readConfigFile($configFilepath) {
        if (!file_exists($configFilepath)) {
            Out::msgStop('Errore', 'Il file di configurazione non esiste.');
            return false;
        }

        $config = parse_ini_file($configFilepath);

        if (!isset($config['DistFolder']) || !$config['DistFolder']) {
            Out::msgStop('Errore', 'DistFolder non impostata nel file di configurazione.');
            return false;
        }

        if (!file_exists($config['DistFolder']) || !is_dir($config['DistFolder'])) {
            Out::msgStop('Errore', "La DistFolder impostata nella configurazione non esiste.\n<i>{$config['DistFolder']}</i>");
            return false;
        }

        return $config;
    }

    public function checkReleaseNumber($param_tab) {
        $configFilepath = $param_tab['depPathConfigBuild'];
        $releaseNumber = $param_tab['depCurrentRelease'];

        Out::delClass($this->nameForm . '_InfoRelease', 'ui-state-highlight ui-state-error');
        Out::hide($this->nameForm . '_InfoRelease');

        if (!$configFilepath || !$releaseNumber) {
            return true;
        }

        /*
         * Leggo il file di configurazione.
         */

        $config = $this->readConfigFile($configFilepath);
        if (!$config) {
            return false;
        }

        $releaseBranches = $this->getReleasePrecedenti($config['DistFolder']);
        if ($releaseBranches === false) {
            return false;
        }

        foreach ($releaseBranches as $branch) {
            if (strpos($branch, $this->releaseBranch . $releaseNumber) !== false) {
                Out::addClass($this->nameForm . '_InfoRelease', 'ui-state-error');
                Out::show($this->nameForm . '_InfoRelease');
                Out::html($this->nameForm . '_InfoRelease', "<span class=\"ui-icon ui-icon-circle-notice\" style=\"vertical-align: top;\"></span> Il numero di release corrente non può essere uguale al numero di release di un ramo già fissato! ({$this->releaseBranch}{$releaseNumber})");
                return false;
            }
        }

        sort($releaseBranches);

        $lastRelease = end($releaseBranches);
        $intLastRelease = (int) preg_replace('/[^\d]+/', '', $lastRelease);
        $intReleaseNumber = (int) preg_replace('/[^\d]+/', '', $releaseNumber);

        if (($intReleaseNumber - $intLastRelease) != 1) {
            Out::addClass($this->nameForm . '_InfoRelease', 'ui-state-highlight');
            Out::show($this->nameForm . '_InfoRelease');
            Out::html($this->nameForm . '_InfoRelease', '<span class="ui-icon ui-icon-circle-info" style="vertical-align: top;"></span> Il numero di release non è immediatamente successivo all\'ultima release!' . " ($lastRelease)");
        }

        return true;
    }

    public function checkParametri($param_tab) {
        $scriptFilepath = $param_tab['depPathScriptBuild'];
        $configFilepath = $param_tab['depPathConfigBuild'];
        $releaseNumber = $param_tab['depCurrentRelease'];

        /*
         * Controllo la presenza dello script.
         */

        if ($scriptFilepath && !file_exists($scriptFilepath)) {
            Out::msgStop('Errore', 'Lo script di lancio build non esiste.');
            return false;
        }

        if ($configFilepath && $releaseNumber) {
            if (!$this->checkReleaseNumber($param_tab)) {
                Out::msgStop('Errore', 'Verifica del numero di release fallita.');
                return false;
            }
        }

        return true;
    }

    public function getReleasePrecedenti($distFolder) {
        include_once ITA_BASE_PATH . '/lib/itaPHPGit/itaGit.class.php';

        $git = new itaGit(array(
            'gitBinPath' => Config::getConf('updater.gitBinPath'),
            'workingDir' => $distFolder
        ));

        $git->fetchOnly();

        $releaseBranches = array();

        try {
            $branches = $git->getRemoteBranches();
        } catch (Exception $e) {
            Out::msgStop('Errore', "Errore durante la lettura dei rami, repository '<b>$distFolder</b>'.<br>" . trim($e->getMessage()));
            return false;
        }

        foreach ($branches as $branch) {
            if (preg_match("/{$this->releaseBranch}[\d.]+/", $branch)) {
                $releaseBranches[] = $branch;
            }
        }

        return array_unique($releaseBranches);
    }

}
