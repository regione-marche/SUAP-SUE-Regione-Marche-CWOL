<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Pratiche/praDizionario.class.php';

function praTemplateMail() {
    $praTemplateMail = new praTemplateMail();
    $praTemplateMail->parseEvent();
    return;
}

class praTemplateMail extends itaModel {

    public $nameForm = "praTemplateMail";
    public $divGes = "praTemplateMail_divGestione";
    public $returnModel;
    public $returnMethod;
    public $rigaMail;
    public $praDizionario;

    function __construct() {
        parent::__construct();
        try {
            $this->praDizionario = new praDizionario();
            $this->returnModel = App::$utente->getKey($this->nameForm . "_returnModel");
            $this->returnMethod = App::$utente->getKey($this->nameForm . "_returnMethod");
            $this->rigaMail = App::$utente->getKey($this->nameForm . "_rigaMail");
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_returnModel", $this->returnModel);
            App::$utente->setKey($this->nameForm . "_returnMethod", $this->returnMethod);
            App::$utente->setKey($this->nameForm . "_rigaMail", $this->rigaMail);
        }
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                $this->returnModel = $_POST['returnModel'];
                $this->returnMethod = $_POST['returnMethod'];
                $this->rigaMail = $_POST['RIGAMAIL'];
                $tipoMail = $_POST['TIPOMAIL'];
                $oggettoMail = $_POST['OGGETTOMAIL'];
                $bodyMail = $_POST['BODYMAIL'];
                $this->testoMail($tipoMail, $oggettoMail, $bodyMail);
                break;
            case 'dbClickRow':
            case 'editGridRow':
                break;
            case 'delGridRow':
                break;
            case 'printTableToHTML':
                break;
            case 'exportTableToExcel':
                break;
            case 'onClickTablePager':
                break;
            case 'onChange':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiorna':
                        $bodyMail = $_POST[$this->nameForm . '_bodyMail'];
                        $oggettoMail = $_POST[$this->nameForm . '_oggetto'];
                        $_POST['event'] = $this->returnMethod;
                        $_POST['RIGAMAIL'] = $this->rigaMail;
                        $_POST['OGGETTOMAIL'] = $oggettoMail;
                        $_POST['BODYMAIL'] = $bodyMail;
                        $phpURL = App::getConf('modelBackEnd.php');
                        $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
                        include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
                        $returnModel = itaModel::getInstance($this->returnModel);
                        $returnModel->parseEvent();
                        break;

                    case $this->nameForm . '_oggetto_butt':
                        $contenuto = $_POST[$this->nameForm . '_oggetto'];
                        $Dictionary = $this->praDizionario->GetDictionary();
                        $this->praDizionario->CaricaDizionario($Dictionary, 'praTemplateMail', 'returnOggetto', $contenuto);
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            
            case 'returnOggetto':
                $Dictionary = $this->praDizionario->GetDictionary();
                Out::codice("$('#" . $this->nameForm . '_oggetto' . "').replaceSelection('" . $Dictionary[$_POST['retKey']]['chiave'] . "', true);");
                break;

            case 'returnBody':
                $Dictionary = $this->praDizionario->GetDictionary();
                Out::codice('tinyInsertContent("' . $this->nameForm . '_bodyMail","' . $Dictionary[$_POST['retKey']]['chiave'] . '");');
                break;
            
            case 'embedVars':
                $contenuto = $_POST[$this->nameForm . '_bodyMail'];
                $Dictionary = $this->praDizionario->GetDictionary();
                $this->praDizionario->CaricaDizionario($Dictionary, 'praTemplateMail', 'returnBody', $contenuto);
                break;
            
            case 'onBlur':
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnMethod');
        App::$utente->removeKey($this->nameForm . '_rigaMail');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    public function testoMail($tipoMail, $oggettoMail, $bodyMail) {
        Out::valore($this->nameForm . '_tipoMail', $tipoMail);
        Out::valore($this->nameForm . '_oggetto', $oggettoMail);
        Out::valore($this->nameForm . '_bodyMail', $bodyMail);
        Out::codice('tinyActivate("' . $this->nameForm . '_bodyMail");');
    }

}
?>

