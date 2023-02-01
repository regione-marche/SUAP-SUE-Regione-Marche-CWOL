<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_LIB_PATH . '/itaPHPCore/itaImg.class.php';

function utiImgBrowser() {
    $utiImgBrowser = new utiImgBrowser();
    $utiImgBrowser->parseEvent();
    return;
}

class utiImgBrowser extends itaModel {

    public $nameForm = "utiImgBrowser";
    public $id;

    function __construct() {
        parent::__construct();
        $this->id = App::$utente->getKey($this->nameForm . '_id');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_id', $this->id);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
//        App::log($_POST);
        switch ($_POST['event']) {
            case 'openform':
                $model = 'utiUploadDiag';
                $this->id = $_POST['id'];
                $_POST = Array();
                $_POST['event'] = 'openform';
                $_POST[$model . '_returnModel'] = $this->nameForm;
                $_POST[$model . '_returnEvent'] = "returnUploadFile";
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
//                $fileImg = "/users/pc/dos2ux/tmp/michele/a.jpg";
//                $fileInfo = pathinfo($fileImg);
//                $fileIco = str_replace('.' . $fileInfo['extension'], '-ico.' . $fileInfo['extension'], $fileImg);
//                itaImg::imageResize($fileImg, 75, 75, $fileIco, true);
//                $risposta = '<img src="' . itaImg::base64src($fileIco) . '"></img>';
//                //Out::codice('tinyInsertContent("' . $_POST['id']","'. $Dictionary[$_POST['retKey']]['chiave'].'");');                
//                Out::codice('tinyInsertRawHTML("' . $_POST['id'] . '",\'' . $risposta . '\');');

                break;
            case "returnUploadFile":
//                app::log($_POST);
                $origFile = $_POST['uploadedFile'];
                $fileInfo = pathinfo($origFile);
                //$fileIco = str_replace('.' . $fileInfo['extension'], '-ico.' . $fileInfo['extension'], $origFile);
                //itaImg::imageResize($origFile, 75, 75, $fileIco, true);
                $risposta = '<img src="' . itaImg::base64src($origFile) . '"></img>';
                //Out::codice('tinyInsertContent("' . $_POST['id']","'. $Dictionary[$_POST['retKey']]['chiave'].'");');                
                Out::codice('tinyInsertRawHTML("' . $this->id . '",\'' . $risposta . '\');');
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_id');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

}

?>
