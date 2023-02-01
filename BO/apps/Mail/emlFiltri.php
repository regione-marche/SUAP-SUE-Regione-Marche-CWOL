<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// CARICO LE LIBRERIE NECESSARIE
include_once './apps/Utility/utiEnte.class.php';
include_once './apps/Mail/emlLib.class.php';

function emlFiltri() {
    $emlFiltri = new emlFiltri();
    $emlFiltri->parseEvent();
    return;
}

class emlFiltri extends itaModel {

    public $ITALWEB;
    public $emlLib;
    public $nameForm = "emlFiltri";
    public $divDettaglio = "emlFiltri_divDettaglio";
    public $divDati = "emlFiltri_divDati";
    public $divControllo = "emlFiltri_divControllo";
    public $dati = array();
    public $returnModel;
    public $returnEvent;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->emlLib = new emlLib();
            $this->ITALWEB = $this->emlLib->getITALWEB();
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
            $this->dati = App::$utente->getKey($this->nameForm . '_dati');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnEvent', $this->returnEvent);
            App::$utente->setKey($this->nameForm . '_dati', $this->dati);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->returnModel = $_POST[$this->nameForm . '_returnModel'];
                $this->returnEvent = $_POST[$this->nameForm . '_returnEvent'];
                $this->dati = $_POST['dati'];
                $this->creaComboCampi($this->dati['CAMPI']);
                $this->creaComboCondizioni($this->dati['OPERATORI']);
                $this->Dettaglio();
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiorna':
                        $this->dati['METADATA'] = $_POST[$this->nameForm . '_ctrSerializzato'];
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_CreaCtr':
                        switch ($_POST[$this->nameForm . '_Condizione']) {
                            case 'uguale':
                                $simbolo = "==";
                                break;
                            case 'diverso':
                                $simbolo = "!=";
                                break;
                            case 'contiene':
                                $simbolo = "LIKE";
                                break;
                            case 'maggiore':
                                $simbolo = ">";
                                break;
                            case 'minore':
                                $simbolo = "<";
                                break;
                            case 'maggiore-uguale':
                                $simbolo = ">=";
                                break;
                            case 'minore-uguale':
                                $simbolo = "<=";
                        }
                         if (!$_POST[$this->nameForm . '_Campi'] == '' && !$simbolo == '') {
                            $arrExpr = array();
                            $arrExpr = unserialize($_POST[$this->nameForm . '_ctrSerializzato']);
                            $operatore = '';
                            if ($_POST[$this->nameForm . '_Operatore']) {
                                $operatore = $_POST[$this->nameForm . '_Operatore'];
                            }
                            $arrExpr['CONDIZIONI'][] = array(
                                "CAMPO" => $_POST[$this->nameForm . '_Campi'],
                                "CONDIZIONE" => $simbolo,
                                "VALORE" => $_POST[$this->nameForm . '_ValoreCtr'],
                                "OPERATORE" => $operatore
                            );
                            $strExpr = serialize($arrExpr);
                            $this->dati['METADATA'] = $strExpr;
                            $this->dettaglio();
                        }
                        break;
                    case $this->nameForm . '_CancellaCtr':
                        $this->dati['METADATA'] = '';
                        $this->dettaglio();
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
// **** da sfruttare in seguito ***** //
//            case 'onChange': // Evento OnChange
//                switch ($_POST['id']) {
//                    case $this->nameForm . '_Obbligatorio':
//                        break;
//                }
//                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_dati');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        if ($this->returnModel != '') {
            $model = $this->returnModel;
            $_POST = array();
            $_POST['event'] = $this->returnEvent;
            $_POST['dati'] = $this->dati;
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $model();
            Out::closeDialog($this->nameForm);
        } else {
            Out::show($this->return);
        }
    }

    public function Dettaglio() {
        Out::valore($this->nameForm . '_Espressione', $this->emlLib->DecodificaControllo($this->dati['METADATA']));
        Out::valore($this->nameForm . '_ctrSerializzato', $this->dati['METADATA']);
        Out::clearFields($this->nameForm, $this->divControllo);
        if ($this->dati['METADATA'] == '') {
            Out::hide($this->divRadio, '');
        } else {
            Out::show($this->divRadio, '');
            Out::attributo($this->nameForm . "_FlagAnd", "checked", "0", "checked");
        }
    }

    public function creaComboCampi($arrayCampi) {
        foreach ($arrayCampi as $key => $campo) {
            Out::select($this->nameForm . '_Campi', 1, $key, "0", $campo);
        }
    }

    public function creaComboCondizioni($arrayOperatori) {
        foreach ($arrayOperatori as $key => $operatore) {
            Out::select($this->nameForm . '_Condizione', 1, $key, "0", $operatore);
        }
    }

    public function DecodificaControllo($ctr) {
        $msgCtr = '';
        if ($ctr) {
            $controlli = unserialize($ctr);
            foreach ($controlli as $key => $campo) {
                switch ($campo['CONDIZIONE']) {
                    case '==':
                        $condizione = "uguale a ";
                        break;
                    case '!=':
                        $condizione = "diverso da ";
                        break;
                    case '>':
                        $condizione = "maggiore a ";
                        break;
                    case '<':
                        $condizione = "minore a ";
                        break;
                    case '>=':
                        $condizione = "maggiore-uguale a ";
                        break;
                    case '<=':
                        $condizione = "minore-uguale a ";
                }
                if ($campo['VALORE'] == '') {
                    $valore = "vuoto";
                } else {
                    $valore = $campo['VALORE'];
                }
                switch ($campo['OPERATORE']) {
                    case 'AND':
                        $operatore = 'e ';
                        break;
                    case 'OR':
                        $operatore = 'oppure ';
                }
                $msgCtr = $msgCtr . $operatore . 'il campo ' . $campo['CAMPO'] . ' è ' . $condizione . $valore . chr(10);
            }
        }
        return $msgCtr;
    }

}

?>
