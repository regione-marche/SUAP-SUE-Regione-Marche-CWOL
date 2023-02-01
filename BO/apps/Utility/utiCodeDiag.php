<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function utiCodeDiag() {
    $utiCodeDiag = new utiCodeDiag();
    $utiCodeDiag->parseEvent();
    return;
}

class utiCodeDiag extends itaModel {

    public $nameForm = "utiCodeDiag";
    private $file = false;
    private $code;
    private $mode;
    protected $readOnly = false;

    function __construct() {
        parent::__construct();
        $this->file = App::$utente->getKey($this->nameForm . '_file');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_file', $this->file);
        }
    }

    public function setFile($file) {
        $this->file = $file;
    }

    public function setCode($code) {
        $this->code = $code;
    }

    public function setMode($mode) {
        $this->mode = $mode;
    }

    public function setReadOnly($readOnly) {
        $this->readOnly = $readOnly;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if ($this->readOnly === true) {
                    Out::hideLayoutPanel($this->nameForm . '_buttonBar');
                }

                if ($this->file && file_exists($this->file)) {
                    $this->code = file_get_contents($this->file);
                    $this->mode = pathinfo($this->file, PATHINFO_EXTENSION);
                }

                Out::valore($this->nameForm . '_Editor', $this->code);
                Out::codeEditorMode($this->nameForm . '_Editor', $this->mode);
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Salva':
                        $returnCode = $_POST[$this->nameForm . '_Editor'];

                        if ($this->file) {
                            file_put_contents($this->file, $returnCode);
                        }

                        $returnModel = $this->returnModel;
                        $formObj = itaModel::getInstance($returnModel);

                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura dettaglio fallita");
                            break;
                        }

                        $formObj->setEvent($this->returnEvent);
                        $formObj->setFormData(array('returnCode' => $returnCode));
                        $formObj->parseEvent();

                        $this->returnToParent();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        $this->close = true;
        App::$utente->removeKey($this->nameForm . '_file');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

}
