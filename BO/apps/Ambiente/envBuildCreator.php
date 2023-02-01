<?php

include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once(ITA_LIB_PATH . '/itaPHPCore/itaSysExec.class.php');

function envBuildCreator() {
    $envUpdater = new envBuildCreator();
    $envUpdater->parseEvent();
    return;
}

class envBuildCreator extends itaModel {

    const SCRIPT_TIMEOUT = 600;

    public $nameForm = "envBuildCreator";
    private $errMessage;
    private $ITALWEB;

    function __construct() {
        parent::__construct();

        try {
            $this->docLib = new docLib();
            $this->ITALWEB = $this->docLib->getITALWEB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->init();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Crea':
                        Out::msgQuestion('Crea Build', 'Confermi la creazione della build?', array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCrea', 'model' => $this->nameForm, 'shortCut' => 'f8'),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCrea', 'model' => $this->nameForm, 'shortCut' => 'f5')
                            )
                        );
                        break;

                    case $this->nameForm . '_ConfermaCrea':
                        if ($this->createBuild() === false) {
                            $this->insertAudit($this->ITALWEB, '', 'Creazione build lanciata con ERRORE: ' . $this->errMessage);
                            Out::msgStop('Errore', $this->errMessage);
                            break;
                        }

                        $this->insertAudit($this->ITALWEB, '', 'Creazione build lanciata con SUCCESSO.');
                        break;
                }
                break;
        }
    }

    private function init() {
        // Valorizza autore
        Out::valore($this->nameForm . '_AUTORE', App::$utente->getKey('nomeUtente'));

        // Legge parametri
        $devLib = new devLib();
        $config = $devLib->getEnv_config('DEPLOY', 'codice', 'depPathScriptBuild', false);
        Out::valore($this->nameForm . '_PATH_SCRIPT', $config['CONFIG']);

        $config = $devLib->getEnv_config('DEPLOY', 'codice', 'depPathConfigBuild', false);
        Out::valore($this->nameForm . '_PATH_CONFIG', $config['CONFIG']);

        $config = $devLib->getEnv_config('DEPLOY', 'codice', 'depCurrentRelease', false);
        Out::valore($this->nameForm . '_BUILD_VERSION', $config['CONFIG']);
    }

    private function createBuild() {
        // Controlla che il path dello script sia stato impostato nei parametri
        $pathScript = $this->formData[$this->nameForm . '_PATH_SCRIPT'];
        $pathConfig = $this->formData[$this->nameForm . '_PATH_CONFIG'];
        $releaseVersion = $this->formData[$this->nameForm . '_BUILD_VERSION'];

        if (!$pathScript) {
            $this->errMessage = "Path script non impostato!";
            return false;
        }

        // Controlla esistenza path script
        if (!file_exists($pathScript)) {
            $this->errMessage = "Path: '$pathScript' non esistente!";
            return false;
        }

        // Esegue script
        $itaExecuter = new itaSysExec();
        $pathParts = pathinfo($pathScript);
        $cwd = getcwd();
        if (!chdir($pathParts['dirname'])) {
            $this->errMessage = "Impossibile cambiare directory di lavoro: " . $pathParts['dirname'];
            return false;
        }

        $cmd = './' . $pathParts['basename'];
        $cmd .= " -c $pathConfig";
        $cmd .= " -r $releaseVersion";

        $ret = $itaExecuter->execute($cmd, null, $stdout, $stderr, self::SCRIPT_TIMEOUT);

        chdir($cwd);

        // Aggiorna info box
        if ($stdout) {
            $msg = "<pre>$stdout</pre>";
        }

        if ($stderr) {
            if ($stdout) {
                $msg .= '<br>';
            }
            $msg .= "<pre>$stderr</pre>";
        }

        Out::html($this->nameForm . '_infoBox', $msg);

        if (!$ret) {
            $this->errMessage = "Errore esecuzione script";
            return false;
        }

        return true;
    }

}
