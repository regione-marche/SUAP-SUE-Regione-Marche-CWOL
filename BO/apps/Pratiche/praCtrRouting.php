<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';

function praCtrRouting() {
    $praCtrRouting = new praCtrRouting();
    $praCtrRouting->parseEvent();
    return;
}

class praCtrRouting extends itaModel {

    public $PRAM_DB;
    public $nameForm = "praCtrRouting";
    public $divGes = "praCtrRouting_divGestione";
    public $currPramod;
    public $currPranum;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->currPranum = App::$utente->getKey($this->nameForm . '_currPranum');
            $this->currPramod = App::$utente->getKey($this->nameForm . '_currPramod');
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnMethod = App::$utente->getKey($this->nameForm . '_returnMethod');
            $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
            $this->allegatiAppoggio = App::$utente->getKey($this->nameForm . '_allegatiAppoggio');
            $this->keyPasso = App::$utente->getKey($this->nameForm . '_keyPasso');
            $this->allegatiComunicazione = App::$utente->getKey($this->nameForm . '_allegatiComunicazione');
            $data = App::$utente->getKey('DataLavoro');
            if ($data != '') {
                $this->workDate = $data;
            } else {
                $this->workDate = date('Ymd');
            }
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_currPranum', $this->currPranum);
            App::$utente->setKey($this->nameForm . '_currPranum', $this->currPramod);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnMethod', $this->returnMethod);
            App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
            App::$utente->setKey($this->nameForm . '_allegatiAppoggio', $this->allegatiAppoggio);
            App::$utente->setKey($this->nameForm . '_keyPasso', $this->keyPasso);
            App::$utente->setKey($this->nameForm . '_allegatiComunicazione', $this->allegatiComunicazione);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->returnModel = $_POST[$this->nameForm . "_returnModel"];
                $this->returnMethod = $_POST[$this->nameForm . "_returnMethod"];
                Out::setDialogTitle($this->nameForm, 'title', $_POST[$this->nameForm . "_title"]);
                switch ($_POST['modo']) {
                    case "edit" :
                        if ($_POST['rowid']) {
                            $this->dettaglio($_POST['rowid'], 'rowid');
                        }
                        break;
                    case "add" :
                        if ($_POST['procedimento']) {
                            $this->currPranum = $_POST['PRANUM'];
                            $this->currPramod = $_POST['PRAMOD'];
                            $this->apriInserimento($this->currPranum);
                        }
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiungi':
                        try {
                            if ($_POST[$this->nameForm . '_PROPAS']['PROQST'] == 1 && $_POST[$this->nameForm . '_PROPAS']['PROVPA'] == "") {
                                Out::msgStop("Errore.", 'Flag domanda presente!!<br>Scegliere il passo successivo');
                                break;
                            }
                            $Proges_rec = $this->praLib->GetProges($this->currGesnum);
                            $Propas_rec = $_POST[$this->nameForm . '_PROPAS'];
                            $Propas_rec['PRONUM'] = $this->currGesnum;
                            $Propas_rec['PROPRO'] = $Proges_rec['GESPRO'];
                            if ($Propas_rec['PROSEQ'] == 0 || $Propas_rec['PROSEQ'] == '') {
                                $Propas_rec['PROSEQ'] = 99999;
                            }
                            $Propas_rec['PROPAK'] = $this->praLib->PropakGenerator($this->currGesnum);
                            $pracom_rec = $_POST[$this->nameForm . '_PRACOM'];
                            $pracom_rec['COMPAK'] = $Propas_rec['PROPAK'];

                            $insert_Info = 'Oggetto : Inserisco comunicazione del passso ' . $pracom_rec['COMPAK'];
                            if (!$this->insertRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $insert_Info)) {
                                Out::msgStop("ATTENZIONE!", "Errore di Inserimento su Comunicazione.");
                                break;
                            }

//                            $nrow = ItaDB::DBInsert($this->PRAM_DB, 'PRACOM', 'ROWID', $pracom_rec);
//                            if ($nrow == 0) {
//                                Out::msgStop("ATTENZIONE!", "Errore di Inserimento su Comunicazione.");
//                                break;
//                            }
                            $insert_Info = 'Oggetto: ' . $Propas_rec['PROPAK'];
                            if ($this->insertRecord($this->PRAM_DB, 'PROPAS', $Propas_rec, $insert_Info)) {
//                                $this->RegistraAllegati($Propas_rec['PROPAK']);
                                if (!$this->RegistraAllegati($Propas_rec['PROPAK'])) {
                                    Out::msgStop("ERRORE", "Aggiornamento Allegati fallito");
                                }
                                if (!$this->RegistraAltriDati($Propas_rec['PROPAK'], $Propas_rec)) {
                                    Out::msgStop("ERRORE", "Aggiornamento Atri Dati fallito");
                                }
                                if(!$this->praLib->ordinaPassi($this->currGesnum)){
                                    Out::msgStop("Errore", $this->praLib->getErrMessage());
                                }
                                if (!$this->praLib->sincronizzaStato($this->currGesnum)) {
                                    Out::msgStop("Errore", "Aggiornamento stato pratica <br>" . $this->praLib->getErrMessage());
                                }
                                $this->returnToParent();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Inserimento su Gestione Passi.", $e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $this->chiudiForm = true;
                        if ($this->AggiornaRecord()) {
                            if ($this->AggiornaPartenzaDaPost()) {
                                $this->AggiornaArrivo();
                            }
                        } else {
                            $this->chiudiForm = false;
                        }
                        itaLib::deletePrivateUploadPath();
                        if ($this->chiudiForm == true)
                            $this->returnToParent();
                        break;
                    case $this->nameForm . '_PROPAS[PROCDR]_butt':
                        proRic::proRicAnamed($this->nameForm, $where, 'proAnamed', '1');
                        break;
                }
            case 'returnanamed':
                switch ($_POST['retid']) {
                    case '1':
                        $this->DecodAnamed($_POST['retKey'], 'rowid');
                        break;
                    case '2':
                        $this->DecodAnamedComP($_POST['retKey'], 'rowid');
                        break;
                    case '3':
                        $this->DecodAnamedComA($_POST['retKey'], 'rowid');
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_currPranum');
        App::$utente->removeKey($this->nameForm . '_currPramod');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnMethod');
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_$allegati');
        App::$utente->removeKey($this->nameForm . '_keyPasso');
        App::$utente->removeKey($this->nameForm . '_allegatiComunicazione');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParentClose($close = true) {
        if ($close)
            $this->close();
        Out::show($this->returnModel);
    }

    public function returnToParent($close = true) {
        $_POST = array();
        $_POST['event'] = $this->returnMethod;
        $_POST['model'] = $this->returnModel;
        $phpURL = App::getConf('modelBackEnd.php');
        $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
        include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
        $returnModel = $this->returnModel;
        $returnModel();
        if ($close)
            $this->close();
    }

    public function Dettaglio($rowid) {
        $Ctrrtn_rec = $this->praLib->GetCtrRtn($rowid, 'rowid', false);
        $this->currPranum = $Ctrrtn_rec['CTRPRO'];
        $open_Info = 'Oggetto: ' . $Ctrrtn_rec['CTRKEY'] . "/" . $Ctrrtn_rec['CTRCOD'];
        $this->openRecord($this->PRAM_DB, 'CTRRTN', $open_Info);
        Out::valore($this->nameForm . '_RESPONSABILE', $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM']);
        Out::valore($this->nameForm . "_PROPAS[PROSET]", $Propas_rec['PROSET']);
        Out::setFocus('', $this->nameForm . '_CTRRTN[Appkey]');
    }

    public function apriInserimento($procedimento) {
        Out::valore($this->nameForm . "_Controller", $this->currC);
        Out::valore($this->nameForm . "_Procedimento", $procedimento);
        Out::setFocus('', $this->nameForm . '_CTRRTN[Appkey]');
    }

}

?>
