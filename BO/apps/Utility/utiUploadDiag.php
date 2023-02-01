<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function utiUploadDiag() {
    $utiUploadDiag = new utiUploadDiag();
    $utiUploadDiag->parseEvent();
    return;
}

class utiUploadDiag extends itaModel {

    public $nameForm = "utiUploadDiag";
    private $returnOnClose;

    function __construct() {
        parent::__construct();
        $this->returnOnClose = App::$utente->getKey($this->nameForm . '_returnOnClose');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnOnClose', $this->returnOnClose);
        }
    }

    public function getReturnOnClose() {
        return $this->returnOnClose;
    }

    public function setReturnOnClose($returnOnClose) {
        $this->returnOnClose = $returnOnClose;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if ($_POST[$this->nameForm . "_returnModel"])
                    $this->returnModel = $_POST[$this->nameForm . "_returnModel"];
                if ($_POST[$this->nameForm . "_returnEvent"])
                    $this->returnEvent = $_POST[$this->nameForm . "_returnEvent"];
                Out::valore($this->nameForm . "_fileUpload", "Scegli un file da caricare...");
                if ($_POST["messagge"]) {
                    Out::html($this->nameForm . "_divMessagge", $_POST["messagge"]);
                }
                if ($_POST["returnOnClose"]) {
                    $this->returnOnClose = true;
                }
                Out::show($this->nameForm);
                /* Aggiunto check per itaMobile - Carlo 17.06.15 */
                if (App::$clientEngine == 'itaEngine' && Conf::ITA_ENGINE_VERSION > '1.0') {
                    Out::codice("pluploadActivate('" . $this->nameForm . "_fileUpload_upld_uploader');");
                }
                if (!@is_dir(itaLib::getPrivateUploadPath())) {
                    if (!itaLib::createPrivateUploadPath()) {
                        Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                        $this->returnToParent();
                    }
                }

                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_fileUpload_upld':
                        $responso = $_POST['response'];
                        if ($responso == 'success') {
                            $sourceFile = App::getPath('temporary.uploadPath') . '/' . App::$utente->getKey('TOKEN') . '-' . $_POST['file'];
                            $originalFile = $_POST['file'];
                            $randName = md5(rand() * time()) . "." . pathinfo($sourceFile, PATHINFO_EXTENSION);
                            $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
                            if (!@rename($sourceFile, $destFile)) {
                                Out::msgStop("Errore", "Upload File: File non salvato!");
                            } else {
                                if (file_exists($destFile)) {
                                    $_POST = array();
                                    $_POST['event'] = $this->returnEvent;
                                    $_POST['model'] = $this->returnModel;
                                    $_POST['uploadedFile'] = $destFile;
                                    $_POST['file'] = $originalFile;
                                    $returnObj = $this->returnModelOrig ? itaModel::getInstance($this->returnModelOrig, $this->returnModel) : itaModel::getInstance($this->returnModel);
                                    $returnObj->parseEvent();

                                    $this->returnToParent();
                                    break;
                                } else {
                                    Out::msgStop("Errore", "Upload file:" . $_POST['file'] . " non a buon fine. Controllare il nome del file.");
                                }
                            } ////                            
                        } else {
                            Out::msgStop("Errore", "Upload file:" . $_POST['file'] . " non a buon fine. Controllare<br>$responso");
                        }
                        $this->returnToParent();
                        break;
                    case 'close-portlet':
                        if ($this->returnOnClose) {
                            $_POST = array();
                            $_POST['event'] = $this->returnEvent;
                            $_POST['model'] = $this->returnModel;
                            $_POST['returnId'] = "close-portlet";
                            $_POST['uploadedFile'] = '';
                            $_POST['file'] = '';
                            $returnObj = $this->returnModelOrig ? itaModel::getInstance($this->returnModelOrig, $this->returnModel) : itaModel::getInstance($this->returnModel);
                            $returnObj->parseEvent();
                            $this->returnToParent();
                        }
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        $this->close = true;
        App::$utente->removeKey($this->nameForm . '_returnOnClose');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

}
