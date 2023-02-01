<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praEstensioni() {
    $praEstensioni = new praEstensioni();
    $praEstensioni->parseEvent();
    return;
}

class praEstensioni extends itaModel {
    public $PRAM_DB;
    public $praLib;
    public $nameForm="praEstensioni";
    public $divGes="praEstensioni_divGestione";
    public $gridEstensioni="praEstensioni_gridEstensioni";
    public $returnEvent;
    public $returnModel;
    public $itekey;
    public $ext = array();


    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->ext = App::$utente->getKey($this->nameForm . '_ext');
            $this->itekey = App::$utente->getKey($this->nameForm . '_itekey');
        }catch(Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnEvent', $this->returnEvent);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_ext', $this->ext);
            App::$utente->setKey($this->nameForm . '_itekey', $this->itekey);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'elencaExt':
                $this->returnEvent = $_POST[$this->nameForm. '_returnEvent'];
                $this->returnModel = $_POST[$this->nameForm. '_returnModel'];
                $this->ext = $_POST['ext'];
                $this->itekey = $_POST['itekey'];
                Out::attributo('gs_SEL','readonly','0');
               
                $this->CaricaGriglia($this->gridEstensioni, $this->ext);
                break;
             case 'delGridRow':
                if (array_key_exists($_POST['rowid'], $this->ext) === true) {
                    unset($this->ext[$_POST['rowid']]);
                }
                $this->CaricaGriglia($this->gridEstensioni, $this->ext);
                break;
            case 'onClickTablePager':
                if(strlen($_POST['EXT']) > 4) {
                    Out::msgStop("Errore.", "La lunghezza dell'estensione non può superare i 4 caratteri");
                    break;
                }
                $esiste = false;
                foreach ($this->ext as $key => $est) {
                    if($_POST['EXT'] == $est['EXT']) {
                        $esiste = true;
                        break;
                    }
                }
                if($esiste === true) {
                    Out::msgStop("Errore.", "Estensione già presente");
                    break;
                }
                $this->ext[] = array('EXT' => $_POST['EXT'], 'SEL' => 0);
                $this->CaricaGriglia($this->gridEstensioni, $this->ext);
                Out::valore("gs_EXT", "");
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm. '_Conferma':
                            App::log($_POST);
                    break;
                        $conta = array();
                        $_POST = array();
                        $_POST['event'] = $this->returnEvent;
                        $_POST['model'] = $this->returnModel;
                        $_POST['ext'] = $this->ext;
                        $_POST['itekey'] = $this->itekey;
                        $phpURL = App::getConf('modelBackEnd.php');
                        $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
                        include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
                        $returnModel = $this->returnModel;
                        $returnModel();
                        $this->returnToParent();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->gridEstensioni:
                        $this->ext[$_POST['rowid']]['SEL'] = $_POST['value'];
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm. '_Tutti':
                        if($_POST[$this->nameForm. '_Tutti']) {
                            $Tutti = 1;
                        }else {
                            $Tutti = 0;
                        }
                        foreach ($this->ext as $key=>$passo) {
                            $this->ext[$key]['SEL'] = $Tutti;
                        }
                        $this->CaricaGriglia($this->gridEstensioni, $this->ext);
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_ext');
        App::$utente->removeKey($this->nameForm . '_itekey');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close=true) {
        if ($close) $this->close();
        Out::show('menuapp');
    }


    function CaricaGriglia($griglia, $appoggio, $tipo='1', $pageRows='14') {
        $ita_grid01 = new TableView(
                $griglia,
                array('arrayTable' => $appoggio,
                        'rowIndex' => 'idx')
        );
        if ($tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(count($appoggio));
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

}
?>