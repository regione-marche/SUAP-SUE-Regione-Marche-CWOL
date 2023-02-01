<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

function utiIframeDiag() {
    $utiIframeDiag = new utiIframeDiag();
    $utiIframeDiag->parseEvent();
    return;
}

class utiIframeDiag extends itaModel {
    public $nameForm="utiIframeDiag";
    public $returnModel;
    public $returnEvent;

    function __construct() {
        parent::__construct();
        $this->returnEvent=App::$utente->getKey($this->nameForm.'_returnEvent');
        $this->returnModel=App::$utente->getKey($this->nameForm.'_returnModel');
    }
    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm.'_returnModel',$this->returnModel);
            App::$utente->setKey($this->nameForm.'_returnEvent',$this->returnEvent);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->returnModel=$_POST[$this->nameForm."_returnModel"];
                $this->returnEvent=$_POST[$this->nameForm."_returnEvent"];
                $title = $_POST[$this->nameForm."_title"];
                $url = $_POST[$this->nameForm."_url"];
                Out::addContainer('desktopBody', 'testIframe_wrapper');
                Out::html('testIframe_wrapper', '
                            <div id="testIframe" class="ita-dialog {title:\'' . $title . '\',width:900,height:600,modal:true}">
                                <iframe id="practr_ifctr" style="overflow:auto;" frameborder="0" width="900" height="600" src="' . $url . '">
                                </iframe>
                            </div>');

                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm.'_returnModel');
        App::$utente->removeKey($this->nameForm.'_returnEvent');
        $this->close=true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close=true) {
        if ($close) $this->close();
    }
}
?>
