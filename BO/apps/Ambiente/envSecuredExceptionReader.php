<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function envSecuredExceptionReader() {
    $envSecuredExceptionReader = new envSecuredExceptionReader();
    $envSecuredExceptionReader->parseEvent();
    return;
}

class envSecuredExceptionReader extends itaModel {

    public $nameForm = "envSecuredExceptionReader";

    public function __construct() {
        parent::__construct();
        try {
            
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    public function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::hide($this->nameForm . '_FileCaricato');
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_File':
                        $logPath = $this->getLogDirectory() . '/*.txt';
                        $FileList = glob($logPath);
                        $Array = array();
                        foreach ($FileList as $File) {
                            $Array[]['FILE'] = basename($File);
                        }
                        $this->ElencoFile($this->nameForm, $Array);
                        break;
                }
                break;
            case 'onChange':
                break;

            case 'returnlog':
                $filePath = $this->getLogDirectory() . '/' . $_POST['rowData']['FILE'];
                Out::show($this->nameForm . '_FileCaricato');
                Out::html($this->nameForm . '_FileCaricato', $filePath);
                $objModel = itaFrontController::getInstance('utiLogViewerText');
                $objModel->setLogFile($filePath);
                $objModel->setNameForm($this->nameForm);
                $objModel->setEvent('openform');
                $objModel->parseEvent();
                break;
        }
    }

    public function getLogDirectory() {
        $logPath = Config::getConf('log.log_folder');
        if (!$logPath) {
            $logPath = ITA_BASE_PATH . '/var/log';
        }

        return "$logPath/itaSecuredException";
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function Nascondi() {
        
    }

    public function ElencoFile($returnModel, $matrice, $returnEvent = 'returnlog') {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco File',
            "width" => '500',
            "height" => '300',
            "sortname" => 'FILE',
            "rowNum" => '10000000',
            "rowList" => '[]',
            "navGrid" => 'false',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "colNames" => array(
                "File",
            ),
            "colModel" => array(
                array("name" => 'FILE', "width" => 490, "sortable" => 'false')
            ),
            "arrayTable" => $matrice,
            "filterToolbar" => 'false'
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['msgDetail'] = "Directory<br><b>{$this->getLogDirectory()}</b>";
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

}

?>
