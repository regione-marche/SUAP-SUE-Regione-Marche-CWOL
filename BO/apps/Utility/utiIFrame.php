<?php

function utiIFrame() {
    $utiIFrame = new utiIFrame();
    $utiIFrame->parseEvent();
    return;
}

class utiIFrame extends itaModel {

    public $nameForm = "utiIFrame";
    public $divIFrame = "utiIFrame_frameDiv";
    public $returnModel;
    public $returnEvent;
    public $returnKey;
    public $returnID;
    public $apriForm;
    public $extraData;

    function __construct() {
        parent::__construct();
        $this->returnModel = App::$utente->getKey('utiIFrame_returnModel');
        $this->returnEvent = App::$utente->getKey('utiIFrame_returnEvent');
        $this->returnID = App::$utente->getKey('utiIFrame_returnID');
        $this->apriForm = App::$utente->getKey('utiIFrame_apriForm');
        $this->extraData = App::$utente->getKey('utiIFrame_extraData');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey('utiIFrame_returnModel', $this->returnModel);
            App::$utente->setKey('utiIFrame_returnEvent', $this->returnEvent);
            App::$utente->setKey('utiIFrame_returnID', $this->returnID);
            App::$utente->setKey('utiIFrame_apriForm', $this->apriForm);
            App::$utente->setKey('utiIFrame_extraData', $this->extraData);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->returnModel = $_POST['returnModel'];
                $this->returnEvent = $_POST['returnEvent'];
                $this->returnKey = $_POST['returnKey'];
                $this->returnID = $_POST['retid'];
                $this->apriForm = '';
                if ($_POST['extraData']) {
                    $this->extraData = $_POST['extraData'];
                }
                if ($_POST['title']) {
                    Out::setDialogTitle($this->nameForm, $_POST['title']);
                }
                $src = $_POST['src_frame'];                
                if ($_POST['fullscreen'] == 1) {
                    $html = '<iframe id="utiIFrame_frame" class="ita-frame" frameborder="0" width="100%" height="99%" src="' . $src . '"></iframe>';
                    Out::html($this->nameForm, $html);
                } else {
                    $html = '<iframe id="utiIFrame_frame" class="ita-frame" frameborder="0" width="100%" height="400px" src="' . $src . '"></iframe>';
                    Out::html($this->divIFrame, $html);
                }                                
                break;
            case 'returntoform':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $model = $this->returnModel;
//                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
//                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//                        $_POST = array();
//                        $_POST['event'] = $this->returnEvent;
//                        $_POST['retid'] = $this->returnID;
//                        $model();
//                        $this->returnToParent();
                        $_POST = array();
                        $_POST['retid'] = $this->returnID;
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setEvent($this->returnEvent);
                        $modelObj->parseEvent();
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey('utiIFrame_returnModel');
        App::$utente->removeKey('utiIFrame_returnEvent');
        App::$utente->removeKey('utiIFrame_returnID');
        App::$utente->removeKey('utiIFrame_apriForm');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

}

?>
