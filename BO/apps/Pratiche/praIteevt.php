<?php

/**
 *
 * Template minimo per sviluppo model
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Utility
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license 
 * @version    06.10.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';

function praIteevt() {
    $praIteevt = new praIteevt();
    $praIteevt->parseEvent();
    return;
}

class praIteevt extends itaModel {

    public $praLib;
    public $nameForm = "praIteevt";
    private $openData;
    public $PraNum;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->PraNum = App::$utente->getKey($this->nameForm . '_PraNum');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        App::$utente->setKey($this->nameForm . '_PraNum', $this->PraNum);
        parent::__destruct();
    }

    public function setOpenData($openData) {
        $this->openData = $openData;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->PraNum = $_POST['PRANUM'];
                $this->OpenGestione();
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_RestProcedimarche':
                        $model = "praProcedimarcheRest";
                        $_POST['PRANUM'] = $this->PraNum;
                        $_POST['ITEROW'] = $_POST['praIteevt_ITEEVT']['ROWID'];
                        itaLib::openDialog($model);
                        $objBdaLavori = itaModel::getInstance($model);
                        $objBdaLavori->setReturnModel($this->nameForm);
                        $objBdaLavori->setReturnEvent('ReturnProcedimarche');
                        if (!$objBdaLavori) {
                            break;
                        }
                        $objBdaLavori->setEvent('openform');
                        $objBdaLavori->parseEvent();

                        break;

                    case $this->nameForm . '_Aggiungi':
                        $_POST[$this->returnModel . '_ITEEVT'] = $_POST[$this->nameForm . '_ITEEVT'];
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $_POST[$this->returnModel . '_ITEEVT'] = $_POST[$this->nameForm . '_ITEEVT'];
                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_ITEEVT[IEVCOD]_butt':
                        praRic::ricAnaeventi($this->nameForm);
                        break;
                    case $this->nameForm . '_ITEEVT[IEVTSP]_butt':
                        praRic::praRicAnatsp($this->nameForm);
                        break;
                    case $this->nameForm . '_ITEEVT[IEVTIP]_butt':
                        praRic::praRicAnatip($this->PRAM_DB, $this->nameForm, "RICERCA CATEGORIE");
                        break;
                    case $this->nameForm . '_ITEEVT[IEVSTT]_butt':
                        praRic::praRicAnaset($this->nameForm);
                        break;
                    case $this->nameForm . '_ITEEVT[IEVATT]_butt':
                        if ($_POST[$this->nameForm . '_ITEEVT']['IEVSTT']) {
                            $where = "AND ATTSET = '" . $_POST[$this->nameForm . '_ITEEVT']['IEVSTT'] . "'";
                            praRic::praRicAnaatt($this->nameForm, $where);
                        } else {
                            Out::msgInfo("Attenzione!!!", "Scegliere prima un settore");
                        }
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ITEEVT[IEVTSP]':
                        $codice = $_POST[$this->nameForm . '_ITEEVT']['IEVTSP'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $this->DecodeSportello($codice);
                        }
                        break;
                    case $this->nameForm . '_ITEEVT[IEVTIP]':
                        $codice = $_POST[$this->nameForm . '_ITEEVT']['IEVTIP'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $this->DecodeTipologia($codice);
                        }
                        break;
                    case $this->nameForm . '_ITEEVT[IEVSTT]':
                        $this->DecodeSettore($_POST[$this->nameForm . '_ITEEVT']['IEVSTT']);
                        break;
                    case $this->nameForm . '_ITEEVT[IEVATT]':
                        $this->DecodeAttivita($_POST[$this->nameForm . '_ITEEVT']['IEVATT'], 'condizionato', false, $_POST[$this->nameForm . '_ITEEVT']['IEVSTT']);
                        break;
                }
                break;
            case "returnAnaeventi":
                $anaeventi_rec = $this->praLib->GetAnaeventi($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_ITEEVT[IEVCOD]', $anaeventi_rec['EVTCOD']);
                Out::valore($this->nameForm . '_ANAEVT[EVTDESCR]', $anaeventi_rec['EVTDESCR']);
                Out::setFocus($this->nameForm, $this->nameForm . '_ITEEVT[IEVDESCR]');
                break;
            case "returnAnatsp":
                $this->DecodeSportello($_POST["retKey"], 'rowid');
                break;
            case 'returnAnatip':
                $this->DecodeTipologia($_POST["retKey"], 'rowid');
                break;
            case 'returnAnaset':
                $this->DecodeSettore($_POST["retKey"], 'rowid');
                break;
            case 'returnAnaatt':
                $this->DecodeAttivita($_POST["retKey"], 'rowid');
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($this->returnModel) {
            /* @var $returnModel itaModel */
            $returnModel = itaModel::getInstance($this->returnModel);
            if ($returnModel) {
                $returnModel->setReturnId($this->returnId);
                $returnModel->setEvent($this->returnEvent);
                $returnModel->parseEvent();
            }
        }

        if ($close)
            $this->close();
    }

    public function mostraForm($div) {
        Out::hide($this->nameForm . '_divGestione');
        Out::show($this->nameForm . '_' . $div);
    }

    public function mostraButtonBar($buttons = array()) {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        foreach ($buttons as $button) {
            Out::show($this->nameForm . '_' . $button);
        }
    }

    public function OpenGestione() {
        $this->mostraForm('divGestione');
        Out::clearFields($this->nameForm . '_divGestione');
        Out::setFocus($this->nameForm, $this->nameForm . '_ITEEVT[IEVDESCR]');
        if ($this->openData) {
            $this->mostraButtonBar(array('Aggiorna'));
            Out::valori($this->openData, $this->nameForm . '_ITEEVT');
            $anaevt_rec = $this->praLib->GetAnaeventi($this->openData['IEVCOD']);
            $anatsp_rec = $this->praLib->GetAnatsp($this->openData['IEVTSP']);
            $anatip_rec = $this->praLib->GetAnatip($this->openData['IEVTIP']);
            $anastt_rec = $this->praLib->GetAnaset($this->openData['IEVSTT']);
            $anaatt_rec = $this->praLib->GetAnaatt($this->openData['IEVATT']);
            Out::valori($anaevt_rec, $this->nameForm . '_ANAEVT');
            Out::valore($this->nameForm . '_Sportello', $anatsp_rec['TSPDES']);
            Out::valore($this->nameForm . '_Tipologia', $anatip_rec['TIPDES']);
            Out::valore($this->nameForm . '_SettoreAttivita', $anastt_rec['SETDES']);
            Out::valore($this->nameForm . '_Attivita', $anaatt_rec['ATTDES']);
        } else {
            $this->mostraButtonBar(array('Aggiungi'));
            praRic::ricAnaeventi($this->nameForm);
        }

        $enteMaster = $this->praLib->GetEnteMaster();
        if (!$enteMaster) {
            Out::hide($this->nameForm . "_ITEEVT[PEREVT]_field");
        }
    }

    function DecodeSportello($codice, $tipo = "codice") {
        $Anatsp_rec = $this->praLib->GetAnatsp($codice, $tipo);
        Out::valore($this->nameForm . '_ITEEVT[IEVTSP]', "");
        Out::valore($this->nameForm . '_Sportello', "");
        if ($Anatsp_rec) {
            Out::valore($this->nameForm . '_ITEEVT[IEVTSP]', $Anatsp_rec['TSPCOD']);
            Out::valore($this->nameForm . '_Sportello', $Anatsp_rec['TSPDES']);
        }
    }

    function DecodeTipologia($codice, $tipo = "codice") {
        $Anatip_rec = $this->praLib->GetAnatip($codice, $tipo);
        Out::valore($this->nameForm . '_ITEEVT[IEVTIP]', "");
        Out::valore($this->nameForm . '_Tipologia', "");
        if ($Anatip_rec) {
            Out::valore($this->nameForm . '_ITEEVT[IEVTIP]', $Anatip_rec['TIPCOD']);
            Out::valore($this->nameForm . '_Tipologia', $Anatip_rec['TIPDES']);
        }
    }

    function DecodeSettore($codice, $tipo = "codice") {
        $Anaset_rec = $this->praLib->GetAnaset($codice, $tipo);
        Out::valore($this->nameForm . '_ITEEVT[IEVSTT]', "");
        Out::valore($this->nameForm . '_SettoreAttivita', "");
        if ($Anaset_rec) {
            Out::valore($this->nameForm . '_ITEEVT[IEVSTT]', $Anaset_rec["SETCOD"]);
            Out::valore($this->nameForm . '_SettoreAttivita', $Anaset_rec["SETDES"]);
        }
    }

    function DecodeAttivita($codice, $tipo = "codice", $multi = false, $settore = "") {
        $Anaatt_rec = $this->praLib->GetAnaatt($codice, $tipo, $multi, $settore);
        Out::valore($this->nameForm . '_ITEEVT[IEVATT]', "");
        Out::valore($this->nameForm . '_Attivita', "");
        if ($Anaatt_rec) {
            Out::valore($this->nameForm . '_ITEEVT[IEVATT]', $Anaatt_rec["ATTCOD"]);
            Out::valore($this->nameForm . '_Attivita', $Anaatt_rec["ATTDES"]);
        }
    }

}
