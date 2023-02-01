<?php

/**
 *
 * GESTIONE Destinazioni da passo
 *
 * PHP Version 5
 *
 * @category
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    27.08.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';

function praGestDest() {
    $praGestDest = new praGestDest();
    $praGestDest->parseEvent();
    return;
}

class praGestDest extends itaModel {

    public $PRAM_DB;
    public $nameForm = "praGestDest";
    public $divDettaglio = "praGestDest_divDettaglio";
    public $gridDest = "praGestDest_gridDest";
    public $praLib;
    public $returnModel;
    public $returnEvent;
    public $destinazioni = array();
    public $arrayDest = array();
    public $rowidAlle;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
            $this->destinazioni = App::$utente->getKey($this->nameForm . '_destinazioni');
            $this->arrayDest = App::$utente->getKey($this->nameForm . '_arrayDest');
            $this->rowidAlle = App::$utente->getKey($this->nameForm . '_rowidAlle');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnEvent', $this->returnEvent);
            App::$utente->setKey($this->nameForm . '_destinazioni', $this->destinazioni);
            App::$utente->setKey($this->nameForm . '_arrayDest', $this->arrayDest);
            App::$utente->setKey($this->nameForm . '_rowidAlle', $this->rowidAlle);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::show($this->nameForm);
                $this->returnModel = $_POST[$this->nameForm . '_returnModel'];
                $this->returnEvent = $_POST[$this->nameForm . '_returnEvent'];
                $this->destinazioni = unserialize($_POST['destinazioni']);
//                Out::msgInfo("POST", print_r($_POST,true));
//                Out::msgInfo("Destinazioni", print_r($this->destinazioni,true));
                $this->rowidAlle = $_POST['rowidAlle'];
                $this->arrayDest = array();
                if (is_array($this->destinazioni)) {
                    foreach ($this->destinazioni as $dest) {
                        $Anaddo_rec = $this->praLib->GetAnaddo($dest);
                        $this->arrayDest[] = array(
                            "DDOFIS" => $Anaddo_rec['DDOFIS'],
                            "DDONOM" => $Anaddo_rec['DDONOM'],
                            "DDOEMA" => $Anaddo_rec['DDOEMA'],
                            "DDOCOD" => $Anaddo_rec['DDOCOD'],
                        );
                    }
                    $this->CaricaGriglia($this->gridDest, $this->arrayDest);
                }
                break;
            case 'delGridRow':
                //unset($this->arrayDest[$_POST["rowid"]]);
                $destSel = explode(",", $_POST[$this->gridDest]['gridParam']['selarrrow']);
                foreach ($destSel as $dest) {
                    unset($this->arrayDest[$dest]);
                }
                $this->arrayDest = array_values($this->arrayDest);
                $this->CaricaGriglia($this->gridDest, $this->arrayDest);
                break;
            case 'addGridRow':
                praRic::praRicAnaddo($this->nameForm);
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Conferma':
                        $this->destinazioni = array();
                        foreach ($this->arrayDest as $dest) {
                            $this->destinazioni[] = $dest['DDOCOD'];
                        }
                        $this->returnToParent();
                        break;
                }
                break;
            case "returnAnaddo":
                //$Anaddo_rec = $this->praLib->GetAnaddo($_POST['retKey'], "rowid");
                $destSel = explode(",", $_POST['retKey']);
                foreach ($destSel as $dest) {
                    $Anaddo_rec = $this->praLib->GetAnaddo($dest, "rowid");
                    if (!$this->CtrDestinazione($Anaddo_rec)) {
                        Out::msgInfo("Attenzione!!!", "Il nominativo <b>" . $Anaddo_rec['DDONOM'] . "</b> è già presente.");
                        break;
                    }
                    $this->arrayDest[] = array(
                        "DDOFIS" => $Anaddo_rec['DDOFIS'],
                        "DDONOM" => $Anaddo_rec['DDONOM'],
                        "DDOEMA" => $Anaddo_rec['DDOEMA'],
                        "DDOCOD" => $Anaddo_rec['DDOCOD'],
                    );
                }

                $this->CaricaGriglia($this->gridDest, $this->arrayDest);
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_destinazioni');
        App::$utente->removeKey($this->nameForm . '_arrayDest');
        App::$utente->removeKey($this->nameForm . '_rowidAlle');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        $_POST = array();
        $_POST['destinazioni'] = $this->destinazioni;
        $_POST['rowidAlle'] = $this->rowidAlle;
        $objModel = itaModel::getInstance($this->returnModel);
        $objModel->setEvent($this->returnEvent);
        $objModel->parseEvent();
        if ($close)
            $this->close();
    }

    function CtrDestinazione($Anaddo_rec) {
        foreach ($this->arrayDest as $dest) {
            if ($dest['DDOCOD'] == $Anaddo_rec['DDOCOD']) {
                return false;
            }
        }
        return true;
    }

    function CaricaGriglia($griglia, $appoggio) {
        if (is_null($appoggio))
            $appoggio = array();
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows($_POST[$griglia]['gridParam']['rowNum']);
        TableView::enableEvents($griglia);
        $ita_grid01->getDataPage('json', true);
        return;
    }

}

?>