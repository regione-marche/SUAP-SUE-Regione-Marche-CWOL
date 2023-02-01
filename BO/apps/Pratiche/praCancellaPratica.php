<?php

/**
 *
 * CANCELLAZIONE PRATICA
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    06.12.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praImmobili.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praSoggetti.class.php';

function praCancellaPratica() {
    $praCancellaPratica = new praCancellaPratica();
    $praCancellaPratica->parseEvent();
    return;
}

class praCancellaPratica extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $praImmobili;
    public $praSoggetti;
    public $nameForm = "praCancellaPratica";
    public $divRic = "praCancellaPratica_divRicerca";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->OpenRicerca();
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Conferma':
                        $pratica = str_repeat("0", 6 - strlen(trim($_POST[$this->nameForm . "_Pratica"]))) . trim($_POST[$this->nameForm . "_Pratica"]);
                        $this->GetMsgConferma($_POST[$this->nameForm . "_Anno"] . $pratica);
                        break;
                    case $this->nameForm . '_Anno_butt':
                        $where = $this->praLib->GetWhereVisibilitaSportello();
//                        $retVisibilta = $this->praLib->GetVisibiltaSportello();
//                        $where = "";
//                        if ($retVisibilta['SPORTELLO'] != 0 && $retVisibilta['SPORTELLO'] != 0) {
//                            $where.=" AND GESTSP = " . $retVisibilta['SPORTELLO'];
//                        }
//                        if ($retVisibilta['AGGREGATO'] && $retVisibilta['AGGREGATO'] != 0) {
//                            $where.=" AND GESSPA = " . $retVisibilta['AGGREGATO'];
//                        }
                        praRic::praRicProges($this->nameForm, $where);
                        break;
                    case $this->nameForm . "_returnPasswordCANCELLA":
                        if (!$this->praLib->CtrPassword($_POST[$this->nameForm . '_password'])) {
                            Out::msgStop("Errore", "Password non corretta");
                            break;
                        }

                        /*
                         * Assegno variabili numero e anno pratica
                         */
                        $NumeroPratica = $_POST[$this->nameForm . "_Pratica"];
                        $AnnoPratica = $_POST[$this->nameForm . "_Anno"];

                        /*
                         * Istanzio praLibPratica
                         */
                        include_once(ITA_BASE_PATH . '/apps/Pratiche/praLibPratica.class.php');
                        $praLibPratica = praLibPratica::getInstance();

                        /*
                         * Cancella Pratica
                         */
                        $ret_cancella = $praLibPratica->cancella($this, $NumeroPratica, $AnnoPratica);
                        if ($ret_cancella !== false) {
                            Out::msgInfo("Cancellazione Pratica", "pratica n. $NumeroPratica/$AnnoPratica cancellata correttamente");
                        } else {
                            Out::msgStop("Cancellazione Pratica", $praLibPratica->getErrMessage());
                        }

                        Out::clearFields($this->nameForm, $this->divRic);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Pratica':
                        $Dal_num = $_POST[$this->nameForm . '_Pratica'];
                        if ($Dal_num) {
                            $Dal_num = str_pad($Dal_num, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Pratica', $Dal_num);
                        }
                        break;
                }
                break;
            case 'returnProges':
                $this->DecodProges($_POST['rowData']['ROWID'], 'rowid');
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function OpenRicerca() {
        Out::show($this->divRic, '');
        $this->Nascondi();
        Out::show($this->nameForm . '_Conferma');
        Out::setFocus('', $this->nameForm . '_Pratica');
        $retVisibilta = $this->praLib->GetVisibiltaSportello();
        Out::html($this->nameForm . "_divInfo", "Sportelli On-line Visibili: <span style=\"font-weight:bold;\">" . $retVisibilta['SPORTELLO_DESC'] . "</span> Aggregati Visibili: <span style=\"font-weight:bold;\">" . $retVisibilta['AGGREGATO_DESC'] . "</span>");
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Conferma');
    }

    function GetMsgConferma($pratica) {
        $proges_rec = $this->praLib->GetProges($pratica);
        if (!$proges_rec) {
            Out::msgStop("Attenzione", "Pratica non trovata");
            return false;
        }

        $ret = $this->praLib->checkVisibilitaSportello(array('SPORTELLO' => $proges_rec['GESTSP'], 'AGGREGATO' => $proges_rec['GESSPA']), $this->praLib->GetVisibiltaSportello());
        if (!$ret) {
            Out::msgStop("Attenzione", "Pratica non Visibile.<br>Controllare le impostazioni di visibilita nella scheda Pianta Organica ---> Dipendenti");
            return false;
        }

        $html = $this->praLib->GetHtmlCancellaPratica($proges_rec);
        $this->praLib->GetMsgInputPassword($this->nameForm, "Cancellazione Pratica", "CANCELLA", $html);
    }

    function DecodProges($Codice, $tipoRic = 'codice') {
        $proges_rec = $this->praLib->GetProges($Codice, $tipoRic);
        Out::valore($this->nameForm . "_Pratica", substr($proges_rec['GESNUM'], 4));
        Out::valore($this->nameForm . "_Anno", substr($proges_rec['GESNUM'], 0, 4));
    }

}

