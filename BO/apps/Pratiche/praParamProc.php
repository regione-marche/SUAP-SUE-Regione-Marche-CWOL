<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';

function praParamProc() {
    $praParamProc = new praParamProc();
    $praParamProc->parseEvent();
    return;
}

class praParamProc extends itaModel {
    public $PRAM_DB;
    public $praLib;
    public $nameForm="praParamProc";
    public $divGes="praParamProc_divGestione";
    public $returnModel;
    public $rowidChiamante;
    public $rowidParam;
    public $returnEvent;
    public $returnId;
    public $tipo;
    public $parametri = array();

    function __construct() {
        parent::__construct();
        // Apro il DB
        $this->praLib = new praLib();
        $this->PRAM_DB = $this->praLib->getPRAMDB();
        $this->returnModel=App::$utente->getKey($this->nameForm.'_returnModel');
        $this->rowidChiamante=App::$utente->getKey($this->nameForm.'_rowidChiamante');
        $this->rowidParam=App::$utente->getKey($this->nameForm.'_rowidParam');
        $this->returnEvent=App::$utente->getKey($this->nameForm.'_returnEvent');
        $this->returnId=App::$utente->getKey($this->nameForm.'_returnId');
        $this->parametri=App::$utente->getKey($this->nameForm.'_parametri');
        $this->tipo=App::$utente->getKey($this->nameForm.'_tipo');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm.'_returnModel',$this->returnModel);
            App::$utente->setKey($this->nameForm.'_rowidChiamante',$this->rowidChiamante);
            App::$utente->setKey($this->nameForm.'_rowidParam',$this->rowidParam);
            App::$utente->setKey($this->nameForm.'_returnEvent',$this->returnEvent);
            App::$utente->setKey($this->nameForm.'_returnId',$this->returnId);
            App::$utente->setKey($this->nameForm.'_parametri',$this->parametri);
            App::$utente->setKey($this->nameForm.'_tipo',$this->tipo);
        }
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->rowidChiamante=$_POST[$this->nameForm.'_rowidChiamante'];
                $this->returnModel=$_POST[$this->nameForm.'_returnModel'];
                $this->returnEvent=$_POST[$this->nameForm.'_returnEvent'];
                $this->returnId=$_POST[$this->nameForm.'_returnId'];
                $this->parametri=$_POST[$this->nameForm.'_parametri'];
                $this->OpenRicerca();
                break;
            case 'modifica': // Visualizzo dettaglio
                $this->rowidChiamante=$_POST[$this->nameForm.'_rowidChiamante'];
                $this->returnModel=$_POST[$this->nameForm.'_returnModel'];
                $this->returnEvent=$_POST[$this->nameForm.'_returnEvent'];
                $this->returnId=$_POST[$this->nameForm.'_returnId'];
                $this->parametri=$_POST[$this->nameForm.'_parametri'];
                $this->rowidParam=$_POST[$this->nameForm.'_rowidParam'];
                $this->tipo=$_POST[$this->nameForm.'_tipo'];
                $this->OpenModifica();
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm.'_Aggiungi':
                        $arrayParam = array();
                        $arrayParam['PARAMETRO'] = $_POST[$this->nameForm.'_Parametro'];
                        $arrayParam['VALORE'] = $_POST[$this->nameForm.'_Valore'];
                        $this->parametri[] = $arrayParam;
                        $model = $this->returnModel;
                        $_POST = array();
                        $_POST['event'] = $this->returnEvent;
                        $_POST['rowid'] = $this->rowidChiamante;
                        $_POST['id'] = $this->returnId;
                        $_POST['parametri'] = $this->parametri;
                        $_POST['return'] = 'parametri';
                        $phpURL=App::getConf('modelBackEnd.php');
                        $appRouteProg=App::getPath('appRoute.'.substr($model,0,3));
                        include_once $phpURL.'/'.$appRouteProg.'/'.$model.'.php';
                        $model();
                        $this->returnToParent();
                        break;
                    case $this->nameForm.'_Aggiorna':
                        $arrayParam['PARAMETRO'] = $_POST[$this->nameForm.'_Parametro'];
                        $arrayParam['VALORE'] = $_POST[$this->nameForm.'_Valore'];
                        $arrayParam['PREDEFINITO'] = $this->parametri[$this->rowidParam]['PREDEFINITO'];
                        $this->parametri[$this->rowidParam] = $arrayParam;
                        $model = $this->returnModel;
                        $_POST = array();
                        $_POST['event'] = $this->returnEvent;
                        $_POST['rowid'] = $this->rowidChiamante;
                        $_POST['id'] = $this->returnId;
                        $_POST['parametri'] = $this->parametri;
                        $_POST['return'] = 'parametri';
                        $phpURL=App::getConf('modelBackEnd.php');
                        $appRouteProg=App::getPath('appRoute.'.substr($model,0,3));
                        include_once $phpURL.'/'.$appRouteProg.'/'.$model.'.php';
                        $model();
                        $this->returnToParent();
                        break;
                    case $this->nameForm.'_Cancella':
                        if($this->parametri[$this->rowidParam]['PREDEFINITO']) {
                            out::msgInfo('ATTENZIONE IMPOSSIBILE CANCELLARE!', 'Il parametro '.$this->parametri[$this->rowidParam]['PARAMETRO'].'
                                è un parametro predefinito');
                            break;
                        }

                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione del parametro <b>".$this->parametri[$this->rowidParam]['PARAMETRO']."</b>?",
                                array(
                                'F8-Annulla'=>array('id'=>$this->nameForm.'_AnnullaCancella','model'=>$this->nameForm,'shortCut'=>"f8"),
                                'F5-Conferma'=>array('id'=>$this->nameForm.'_ConfermaCancella','model'=>$this->nameForm,'shortCut'=>"f5")
                                )
                        );
                        break;
                    case $this->nameForm.'_ConfermaCancella':
                        unset ($this->parametri[$this->rowidParam]);
                        $model = $this->returnModel;
                        $_POST = array();
                        $_POST['event'] = $this->returnEvent;
                        $_POST['rowid'] = $this->rowidChiamante;
                        $_POST['id'] = $this->returnId;
                        $_POST['parametri'] = $this->parametri;
                        $_POST['return'] = 'parametri';
                        $phpURL=App::getConf('modelBackEnd.php');
                        $appRouteProg=App::getPath('appRoute.'.substr($model,0,3));
                        include_once $phpURL.'/'.$appRouteProg.'/'.$model.'.php';
                        $model();
                        $this->returnToParent();
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm.'_returnModel');
        App::$utente->removeKey($this->nameForm.'_rowidChiamante');
        App::$utente->removeKey($this->nameForm.'_rowidParam');
        App::$utente->removeKey($this->nameForm.'_returnEvent');
        App::$utente->removeKey($this->nameForm.'_returnId');
        App::$utente->removeKey($this->nameForm.'_parametri');
        App::$utente->removeKey($this->nameForm.'_tipo');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close=true) {
        if ($close) $this->close();
        Out::show($this->returnModel);
    }

    function OpenRicerca() {
        Out::show($this->divGes, '');
        Out::clearFields($this->nameForm, $this->divGes);
        $this->Nascondi();
        Out::show($this->nameForm.'_Aggiungi');
        Out::show($this->nameForm);
        Out::setFocus('',$this->nameForm.'_Parametro');
    }

    function OpenModifica() {
        App::log($this->tipo);
        Out::show($this->divGes, '');
        Out::valore($this->nameForm.'_Parametro', $this->parametri[$this->rowidParam]['PARAMETRO']);
        Out::valore($this->nameForm.'_Valore', $this->parametri[$this->rowidParam]['VALORE']);
        $this->Nascondi();
        Out::show($this->nameForm.'_Aggiorna');
        if($this->tipo == "Embed") Out::show($this->nameForm.'_Cancella');
        Out::show($this->nameForm);
        Out::attributo($this->nameForm . '_Parametro', 'readonly', '0');
        Out::setFocus('',$this->nameForm.'_Parametro');
    }

    public function Nascondi() {
        Out::hide($this->nameForm.'_Aggiungi');
        Out::hide($this->nameForm.'_Aggiorna');
        Out::hide($this->nameForm.'_Cancella');
    }


}
?>