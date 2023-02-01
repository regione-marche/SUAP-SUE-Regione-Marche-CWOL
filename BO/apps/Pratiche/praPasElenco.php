<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praPasElenco() {
    $praPasElenco = new praPasElenco();
    $praPasElenco->parseEvent();
    return;
}

class praPasElenco extends itaModel {
    public $PRAM_DB;
    public $praLib;

    public $nameForm="praPasElenco";
    public $divGes="praPasElenco_divGestione";
    public $gridPassi="praPasElenco_gridPassi";
    public $returnEvent;
    public $returnModel;
    public $caption;
    public $passi = array();


    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->passi = App::$utente->getKey($this->nameForm . '_passi');
            $this->caption = App::$utente->getKey($this->nameForm . '_caption');
        }catch(Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnEvent', $this->returnEvent);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_passi', $this->passi);
            App::$utente->setKey($this->nameForm . '_caption', $this->caption);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'elencaPassi':
                $this->returnEvent=$_POST[$this->nameForm. '_returnEvent'];
                $this->returnModel=$_POST[$this->nameForm. '_returnModel'];
                $this->passi = $_POST['passi'];
                $this->caption = $_POST['caption'];
                Out::setDialogTitle($this->nameForm, $this->caption);
                foreach ($this->passi as $key=>$passo) {
                    $this->passi[$key]['SEL'] = 0;
                }
                $this->CaricaGriglia($this->gridPassi, $this->passi);
                break;
            case 'dbClickRow':
                foreach ($this->passi as $key=>$passo) {
                    if ($passo['ROWID'] == $_POST['rowid']) {
                        if ($this->passi[$key]['SEL'] == 0) {
                            $this->passi[$key]['SEL'] = 1;
                        } else {
                            $this->passi[$key]['SEL'] = 0;
                        }
                        break;
                    }
                }
                $this->CaricaGriglia($this->gridPassi, $this->passi);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm. '_Conferma':
                        foreach ($this->passi as $key=>$passo) {
                            if ($passo['SEL'] == 1) {
                                $conta[] = $passo;
                            }
                        }
                        if (count($conta)) {
                            if(strpos($this->caption, "cancellare") !== false) {
                                $msgCancella = "<br><span style=\"color:red;font-weight: bold;font-size:1.3em;\">I passi selezionati verranno cancellati</span>";
                            }
                            Out::msgQuestion("Attenzione!!", "Hai selezionato " . count($conta) . " passi. Vuoi Continuare?.$msgCancella", array(
                                    'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaSel', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                    'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaSel', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;
                    case $this->nameForm. '_ConfermaSel':
                        $conta = array();
                        foreach ($this->passi as $key=>$passo) {
                            if ($passo['SEL'] == 0) {
                                unset ($this->passi[$key]);
                            }
                        }
                        foreach ($this->passi as $key=>$passo) {
                            unset ($this->passi[$key]['SEL']);
                        }
                        $_POST = array();
                        $_POST['event'] = $this->returnEvent;
                        $_POST['model'] = $this->returnModel;
                        $_POST['passiSel'] = $this->passi;
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
                    case $this->gridPassi:
                        foreach ($this->passi as $key=>$passo) {
                            if ($passo['ROWID'] == $_POST['rowid']) {
                                $this->passi[$key]['SEL'] = $_POST['value'];
                                break;
                            }
                        }
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
                        foreach ($this->passi as $key=>$passo) {
                            $this->passi[$key]['SEL'] = $Tutti;
                        }
                        $this->CaricaGriglia($this->gridPassi, $this->passi);
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_passi');
        App::$utente->removeKey($this->nameForm . '_caption');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close=true) {
        if ($close) $this->close();
        Out::show('menuapp');
    }

    function CaricaGriglia($griglia, $appoggio, $tipo='1', $pageRows='14') {
        //Out::codice("$('#$griglia').setCaption('$this->caption');");
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