<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

function utiSetDate() {
    $utiSetDate = new utiSetDate();
    $utiSetDate->parseEvent();
    return;
}

class utiSetDate extends itaModel {
    public $nameForm="utiSetDate";

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $data=App::$utente->getKey('DataLavoro');
                if ($data!='') {
                    Out::valore($this->nameForm.'_Data', $data);
                }else {
                    Out::valore($this->nameForm.'_Data', date('Ymd'));
                }
                Out::setFocus('',$this->nameForm.'_Data');
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm.'_Conferma':
                        $data=$_POST[$this->nameForm.'_Data'];
                        if($data>date('Ymd')) {
                            Out::msgStop("ATTENZIONE!","La Data inserita è Superiore alla data di Sistema.");
                        }
                        App::$utente->setKey('DataLavoro',$data);
                        Out::html('desktopHeaderInfoDate', ' - '.substr($data, 6).'/'.substr($data, 4, 2).'/'.substr($data, 0,4));
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close=true) {
        if ($close) $this->close();
    }
}
?>
