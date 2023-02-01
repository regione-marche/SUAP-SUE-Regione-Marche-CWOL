<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/
include_once ITA_LIB_PATH . '/itaPHPCore/itaImg.class.php';
function utiLBox() {
    $utiLBox = new utiLBox();
    $utiLBox->parseEvent();
    return;
}

class utiLBox extends itaModel {
    public $nameForm="utiLBox";
    public $elencoFile;
    public $nElemento;

    function __construct() {
        parent::__construct();
        $this->elencoFile=App::$utente->getKey($this->nameForm.'_elencoFile');
        $this->nElemento=App::$utente->getKey($this->nameForm.'_nElemento');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm.'_elencoFile',$this->elencoFile);
            App::$utente->setKey($this->nameForm.'_nElemento',$this->nElemento);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->elencoFile=$_POST['elencoFile'];
                $this->visualizza($this->elencoFile,0);
                Out::setFocus('', $this->nameForm."_Successivo");
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm.'_Precedente':
                        $this->visualizza($this->elencoFile, $this->nElemento-1);
                        break;
                    case $this->nameForm.'_Successivo':
                        $this->visualizza($this->elencoFile, $this->nElemento+1);
                        break;
                    case $this->nameForm.'_Ingrandisci':
                        $filePath=$this->elencoFile[$this->nElemento]['FileName'];
                        $baseName=pathinfo($filePath,PATHINFO_BASENAME);
                       // $baseName=@basename($this->elencoFile[$this->nElemento]['FileName']);
                        $fileDest=realpath(App::$utente->getKey('privPath'))."/".App::$utente->getKey('TOKEN')."-".$baseName;
                        $url="http://".$_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT'].App::$utente->getKey('privUrl')."/"
                                .App::$utente->getKey('TOKEN')."-".$baseName;
                        if(@copy($filePath,$fileDest)) {
                            Out::openDocument($url);
                        } else {
                        }
                        break;

                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm.'_elencoFile');
        App::$utente->removeKey($this->nameForm.'_nElemento');
        $this->close=true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close=true) {
        if ($close) $this->close();
    }

    public function visualizza($elencoFile,$imgIdx) {
        if ($imgIdx < 0) {
            $imgIdx=count($this->elencoFile)-1;
        }
        if ($imgIdx+1 > count($this->elencoFile)) {
            $imgIdx=0;
        }
        $fileImg=$elencoFile[$imgIdx]['FileName'];
//        App::log($elencoFile);
        $src=itaImg::base64src($fileImg);
        Out::attributo($this->nameForm.'_immagine', 'src',0,$src);
        $this->nElemento=$imgIdx;
        $curr=$this->nElemento+1;
        $max=count($this->elencoFile);
        Out::valore($this->nameForm.'_Index',$curr.' di '.$max);
    }
}
?>