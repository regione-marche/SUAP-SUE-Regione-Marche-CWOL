<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
include_once (ITA_BASE_PATH . '/apps/Utility/utiCodiceFiscale.class.php');
include_once ITA_BASE_PATH . '/apps/Base/basLib.class.php';

function utiCreaCF() {
    $utiCreaCF = new utiCreaCF();
    $utiCreaCF->parseEvent();
    return;
}

class utiCreaCF extends itaModel {

    public $ITALWEB;
    public $docLib;
    public $basLib;
    public $nameForm = "utiCreaCF";
    public $returnModel;
    public $returnMethod;
    public $dati;

    function __construct() {
        parent::__construct();
        try {
            $this->docLib = new docLib();
            $this->basLib = new basLib();
            $this->ITALWEB = $this->docLib->getITALWEB();
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnMethod = App::$utente->getKey($this->nameForm . '_returnMethod');
            $this->dati = App::$utente->getKey($this->nameForm . '_dati');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnMethod', $this->returnMethod);
            App::$utente->setKey($this->nameForm . '_dati', $this->dati);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CreaCombo();
                $this->returnModel = $_POST[$this->nameForm . "_returnModel"];
                $this->returnMethod = $_POST[$this->nameForm . "_returnMethod"];
                $this->dati = $_POST[$this->nameForm . "_dati"];
                Out::clearFields($this->nameForm, $this->nameForm . '_divRicerca');
                Out::setFocus('', $this->nameForm . '_COGNOME');
                if ($this->dati) {
                    App::log($this->dati);
                    out::valore($this->nameForm . '_NOME', $this->dati['nome']);
                    out::valore($this->nameForm . '_COGNOME', $this->dati['cognome']);
                }
                break;
            case "onClick":
                switch ($_POST['id']) {
                    case $this->nameForm . '_Nuovo':
                        Out::clearFields($this->nameForm, $this->nameForm . '_divRicerca');
                        Out::setFocus('', $this->nameForm . '_COGNOME');
                        break;
                    case $this->nameForm . '_Calcola':

                        $uiCognome = $_POST[$this->nameForm . '_COGNOME'];

                        $uiNome = $_POST[$this->nameForm . '_NOME'];

                        $uiDataNascita = $_POST[$this->nameForm . '_DATANASCITA'];

                        $uiSesso = $_POST[$this->nameForm . '_SESSO'];

                        $uiComune = addslashes($_POST[$this->nameForm . '_LUOGO']);

                        $uiCodProvincia = $_POST[$this->nameForm . '_SIGLAPROV'];

                        $CodiceFiscale = new utiCodiceFiscale();
                        $CodFis = $CodiceFiscale->Calcola($uiCognome, $uiNome, $uiDataNascita, $uiSesso, $uiComune, $uiCodProvincia);
                        Out::valore($this->nameForm . '_CALCOLO', $CodFis);
                        if ($this->returnMethod != '') {
                            $this->returnToParent($CodFis);
                        }
                        break;
                }
                break;

            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_LUOGO':
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $Comuni_tab = $this->basLib->getGenericTab("SELECT * FROM COMUNI WHERE ".$this->ITALWEB->strUpper('COMUNE')." LIKE '"
                                        . addslashes(strtoupper(itaSuggest::getQuery())) . "%'", true, 'COMUNI');
                        foreach ($Comuni_tab as $Comuni_rec) {
                            itaSuggest::addSuggest($Comuni_rec['COMUNE'], array($this->nameForm . "_SIGLAPROV" => $Comuni_rec['PROVIN']));
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;

            case 'close-portlet':
                $this->returnToParent();
                break;
        }
    }

    public function close() {
        $this->close = true;
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnMethod');
        App::$utente->removeKey($this->nameForm . '_dati');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($CodFis, $close = true) {
        $_POST = array();
        $_POST['event'] = $this->returnMethod;
        $_POST['model'] = $this->returnModel;
        $_POST['CF'] = $CodFis;
        $phpURL = App::getConf('modelBackEnd.php');
        $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
        include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
        $returnModel = $this->returnModel;
        $returnModel();
        if ($close)
            $this->close();
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_SESSO', 1, " ", "0", " ");
        Out::select($this->nameForm . '_SESSO', 1, "F", "0", "F");
        Out::select($this->nameForm . '_SESSO', 1, "M", "0", "M");
    }

}

?>
