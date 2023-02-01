<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

function utiInput() {
    $utiInput = new utiInput();
    $utiInput->parseEvent();
    return;
}

class utiInput extends itaModel {

    public $nameForm = "utiInput";
    public $returnModel;
    public $returnEvent;
    public $obbligatori;

    function __construct() {
        parent::__construct();
        $this->returnModel = App::$utente->getKey($this->nameForm.'_returnModel');
        $this->returnEvent = App::$utente->getKey($this->nameForm.'_returnEvent');
        $this->obbligatori = App::$utente->getKey($this->nameForm.'_obbligatori');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm.'_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm.'_returnEvent', $this->returnEvent);
            App::$utente->setKey($this->nameForm.'_obbligatori', $this->obbligatori);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::hide($this->nameForm."_div1");
                Out::hide($this->nameForm."_div2");
                Out::hide($this->nameForm."_div3");
                Out::hide($this->nameForm."_div4");
                Out::hide($this->nameForm."_div5");
                $this->returnModel = $_POST['returnModel'];
                $this->returnEvent = $_POST['returnEvent'];
                $this->obbligatori = $_POST['obbligatori'];
                $i=0;
                foreach ($_POST['elementi'] as $elementi) {
                    $i++;
                    Out::html($this->nameForm."_campoInput".$i."_lbl", $elementi['label']);
                    Out::valore($this->nameForm."_campoInput".$i, $elementi['default']);
                    Out::show($this->nameForm."_div".$i);
                }
                Out::html($this->nameForm . '_Conferma', $_POST['button']);
                Out::setAppTitle($this->nameForm,$_POST['title']);
                Out::setFocus('',$this->nameForm.'_campoInput1');
                break;
            case "onClick":
                switch ($_POST['id']) {
                    case $this->nameForm . '_Conferma':
                            $continua=true;
                        foreach ($this->obbligatori as $obbligatorio) {
                            if ($obbligatorio!='') {
                                if ($_POST[$this->nameForm."_campoInput$obbligatorio"]=='') {
                                    Out::msgStop("Attenzione!", "Completare i campi obbligatori!");
                                    Out::setFocus('',$this->nameForm."_campoInput$obbligatorio");
                                    $continua=false;
                                    break;
                                }
                            }
                        }
                        if ($continua==false) break;
                        $model = $this->returnModel;
                        $valore=array(
                                $_POST[$this->nameForm . '_campoInput1'],
                                $_POST[$this->nameForm . '_campoInput2'],
                                $_POST[$this->nameForm . '_campoInput3'],
                                $_POST[$this->nameForm . '_campoInput4'],
                                $_POST[$this->nameForm . '_campoInput5']
                        );
                        $_POST = array();
                        $_POST['event'] = $this->returnEvent;
                        $_POST['valore'] = $valore;
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        Out::show($model);
                        $this->returnToParent();
                        break;
                }
                break;
            case 'close-portlet':
                $this->returnToParent();
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm.'_returnModel');
        App::$utente->removeKey($this->nameForm.'_returnEvent');
        App::$utente->removeKey($this->nameForm.'_obbligatori');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent() {
        $this->close();
    }

}

?>
