<?php

/**
 *
 * CAMBI ORESPONSABILE PROCEDIMENTI
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    26.09.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';

function praCambioResp() {
    $praCambioResp = new praCambioResp();
    $praCambioResp->parseEvent();
    return;
}

class praCambioResp extends itaModel {

    public $praLib;
    public $PRAM_DB;
    public $nameForm = "praCambioResp";
    public $divRis = "praCambioResp_divRisultato";
    public $divRic = "praCambioResp_divRicerca";
    public $gridAnapra = "praCambioResp_gridAnapra";

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
        if ($this->close != true) {
            
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->OpenRicerca();
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAnapra:
                        $ordinamento = $_POST['sidx'];
                        if ($ordinamento == 'DESCRIZIONE') {
                            $ordinamento = 'PRADES__1';
                        }
                        if ($ordinamento == 'RESPONSABILE') {
                            $ordinamento = 'NOMCOG';
                        }
                        if ($ordinamento == '') {
                            $ordinamento = 'PRANUM';
                        }
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($ordinamento);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPage('json');

                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        Out::hide($this->divGes);
                        Out::hide($this->divRic);
                        Out::hide($this->divDup);
                        Out::show($this->divRis);
                        $this->Nascondi();
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridAnapra, array(
                            'sqlDB' => $this->PRAM_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows(14);
                        $ita_grid01->setSortIndex('PRANUM');
                        $ita_grid01->setSortOrder('asc');
                        if (!$ita_grid01->getDataPage('json')) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo il risultato
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Conferma');
                            Out::setFocus('', $this->nameForm . '_Conferma');
                            TableView::enableEvents($this->gridAnapra);
                            Out::html($this->nameForm . "_divSelect", $this->getHtmlRes());
                        }
                        break;
                    case $this->nameForm . '_Conferma':
                        $header = "<div style=\"font-size:1.2em;\" class=\"ita-box-highlight ui-widget-content ui-corner-all ui-state-highlight\"><b>Confermando Verrà Aggiornato il Responsabile in tutti i Procedimenti Selezionati.</b></div>";
                        Out::msgInput(
                                'Scelta Nuovo Responsabile', array(
                            array(
                                'id' => $this->nameForm . '_CodiceResp',
                                'name' => $this->nameForm . '_CodiceResp',
                                'class' => "ita-edit-lookup ita-readonly",
                                'size' => '7',
                                'maxlength' => '6'),
                            array(
                                'id' => $this->nameForm . '_Resp',
                                'name' => $this->nameForm . '_Resp',
                                'class' => "ita-readonly",
                                'size' => '50'),
                                ), array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaNuovoResp', 'model' => $this->nameForm, 'shortCut' => "f5")
                                ), $this->nameForm, "auto", "auto", true, $header
                        );

                        break;
                    case $this->nameForm . '_ConfermaNuovoResp':
                        if ($_POST[$this->nameForm . '_CodiceResp'] == "") {
                            Out::msgInfo("Attenzione", "Scegliere Un Nuovo Responsabile.");
                            break;
                        }

                        //
                        //Cambio il responsabile a tutti i procedimenti trovati
                        //
                        $Anapra_tab = $this->praLib->getGenericTab($this->CreaSql());
                        foreach ($Anapra_tab as $Anapra_rec) {
                            $err = false;
                            //TSPDES, EVTDESCR, TIPDES, SETDES, ATTDES,PRADVA, PRAAVA," .
                            unset($Anapra_rec['DESCRIZIONE']);
                            unset($Anapra_rec['RESPONSABILE']);
                            unset($Anapra_rec['TIPDES']);
                            unset($Anapra_rec['SETDES']);
                            unset($Anapra_rec['ATTDES']);
                            unset($Anapra_rec['TSPDES']);
                            unset($Anapra_rec['EVTDESCR']);
                            $update_Info = "Oggetto: Cambio Responsabile Proc " . $Anapra_rec['PRANUM'] . " da " . $Anapra_rec['PRARES'] . " a " . $_POST[$this->nameForm . "_CodiceResp"];
                            $Anapra_rec['PRARES'] = $_POST[$this->nameForm . "_CodiceResp"];
                            if (!$this->updateRecord($this->PRAM_DB, 'ANAPRA', $Anapra_rec, $update_Info)) {
                                $err = true;
                                break;
                            }
                        }
                        if ($err) {
                            Out::msgStop("Errore in Aggionamento", "Aggiornamento Responsabile Fallito nel procediemnto n. " . $Anapra_rec['PRANUM'] . ".");
                            break;
                        }

                        //
                        //Cambio il responsabile a tutti i passi dei procedimenti trovati
                        //
                        foreach ($Anapra_tab as $Anapra_rec) {
                            $itepas_tab = $this->praLib->GetItepas($Anapra_rec['PRANUM'], "codice", true);
                            foreach ($itepas_tab as $itepas_rec) {
                                $errPasso = false;
                                $update_Info = "Oggetto: Cambio Responsabile Passo Procedimento " . $Anapra_rec['PRANUM'] . " seq " . $itepas_rec['ITESEQ'];
                                $itepas_rec['ITERES'] = $_POST[$this->nameForm . "_CodiceResp"];
                                if (!$this->updateRecord($this->PRAM_DB, 'ITEPAS', $itepas_rec, $update_Info)) {
                                    $errPasso = true;
                                    break; //esco dal primo foreach
                                }
                            }
                            if ($errPasso) {
                                break; //esco dal secondo foreach
                            }
                        }
                        if ($errPasso) {
                            Out::msgStop("Errore in Aggionamento", "Aggiornamento Responsabile Fallito procedimento n. " . $Anapra_rec['PRANUM'] . " passo sequenza " . $itepas_rec['ITESEQ']);
                            break; //Se c'è l'erroe blocco la procedura
                        }
                        $this->OpenRicerca();
                        Out::msgBlock("", 3000, true, "Responsabile " . $Anapra_rec['PRARES'] . ": " . $_POST[$this->nameForm . "_Resp"] . " aggiornato correttamente su " . count($Anapra_tab) . " procedimenti");
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_pratip_butt':
                        praRic::praRicAnatip($this->PRAM_DB, $this->nameForm, "RICERCA CATEGORIE", '', "pratip");
                        break;
                    case $this->nameForm . '_prares_butt':
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "RICERCA DIPENDENTI", '', "prares");
                        break;
                    case $this->nameForm . '_CodiceResp_butt':
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "RICERCA DIPENDENTI", '', "NewResp");
                        break;
                    case $this->nameForm . '_prastt_butt':
                        praRic::praRicAnaset($this->nameForm, "", 'prastt');
                        break;
                    case $this->nameForm . '_praatt_butt':
                        if ($_POST[$this->nameForm . '_prastt']) {
                            $where = "AND ATTSET = '" . $_POST[$this->nameForm . '_prastt'] . "'";
                            praRic::praRicAnaatt($this->nameForm, $where, 'praatt');
                        } else {
                            Out::msgInfo("Attenzione!!!", "Scegliere prima un settore");
                        }
                        break;
                    case $this->nameForm . '_pratsp_butt':
                        praRic::praRicAnatsp($this->nameForm, '', 'pratsp');
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_prares':
                        $codice = $_POST[$this->nameForm . '_prares'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Ananom_rec = $this->praLib->GetAnanom($codice);
                            if ($Ananom_rec) {
                                Out::valore($this->nameForm . '_prares', $Ananom_rec["NOMRES"]);
                                Out::valore($this->nameForm . '_nome', $Ananom_rec["NOMCOG"] . ' ' . $Ananom_rec["NOMNOM"]);
                            }
                        }
                        break;
                    case $this->nameForm . '_pratip':
                        $codice = $_POST[$this->nameForm . '_pratip'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anatip_rec = $this->praLib->GetAnatip($codice);
                            if ($Anatip_rec) {
                                Out::valore($this->nameForm . '_pratip', $Anatip_rec['TIPCOD']);
                                Out::valore($this->nameForm . '_tipologia', $Anatip_rec['TIPDES']);
                            }
                        }
                        break;
                    case $this->nameForm . '_pratsp':
                        $codice = $_POST[$this->nameForm . '_pratsp'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anatsp_rec = $this->praLib->GetAnatsp($codice);
                            if ($Anatsp_rec) {
                                Out::valore($this->nameForm . '_pratsp', $Anatsp_rec['TSPCOD']);
                                Out::valore($this->nameForm . '_sportello', $Anatsp_rec['TSPDES']);
                            }
                        }
                        break;
                    case $this->nameForm . '_prastt':
                        $Anaset_rec = $this->praLib->GetAnaset($_POST[$this->nameForm . '_prastt']);
                        if ($Anaset_rec) {
                            Out::valore($this->nameForm . '_prastt', $Anaset_rec["SETCOD"]);
                            Out::valore($this->nameForm . '_settoreAttivita', $Anaset_rec["SETDES"]);
                        }
                        break;
                    case $this->nameForm . '_praatt':
                        $Anaatt_rec = $this->praLib->GetAnaatt($_POST[$this->nameForm . '_praatt'], 'condizionato', false, $_POST[$this->nameForm . '_prastt']);
                        if ($Anaatt_rec) {
                            Out::valore($this->nameForm . '_praatt', $Anaatt_rec["ATTCOD"]);
                            Out::valore($this->nameForm . '_attivita', $Anaatt_rec["ATTDES"]);
                        }
                        break;
                }
                break;
            case 'returnAnatip':
                if ($_POST['retid'] == 'pratip') {
                    $Anatip_rec = $this->praLib->GetAnatip($_POST['retKey'], 'rowid');
                    Out::valore($this->nameForm . '_pratip', $Anatip_rec['TIPCOD']);
                    Out::valore($this->nameForm . '_tipologia', $Anatip_rec['TIPDES']);
                }
                break;
            case 'returnAnaset':
                if ($_POST['retid'] == 'prastt') {
                    $Anaset_rec = $this->praLib->GetAnaset($_POST["retKey"], 'rowid');
                    if ($Anaset_rec) {
                        Out::valore($this->nameForm . '_prastt', $Anaset_rec["SETCOD"]);
                        Out::valore($this->nameForm . '_settoreAttivita', $Anaset_rec["SETDES"]);
                    }
                }
                break;
            case 'returnAnaatt':
                if ($_POST['retid'] == 'praatt') {
                    $Anaatt_rec = $this->praLib->GetAnaatt($_POST["retKey"], 'rowid');
                    if ($Anaatt_rec) {
                        Out::valore($this->nameForm . '_praatt', $Anaatt_rec["ATTCOD"]);
                        Out::valore($this->nameForm . '_attivita', $Anaatt_rec["ATTDES"]);
                    }
                }
                break;
            case 'returnUnires':
                $Ananom_rec = $this->praLib->GetAnanom($_POST["retKey"], 'rowid');
                if ($Ananom_rec) {
                    if ($_POST['retid'] == 'prares') {
                        Out::valore($this->nameForm . '_prares', $Ananom_rec["NOMRES"]);
                        Out::valore($this->nameForm . '_nome', $Ananom_rec["NOMCOG"] . ' ' . $Ananom_rec["NOMNOM"]);
                    } elseif ($_POST['retid'] == 'NewResp') {
                        Out::valore($this->nameForm . '_CodiceResp', $Ananom_rec["NOMRES"]);
                        Out::valore($this->nameForm . '_Resp', $Ananom_rec["NOMCOG"] . ' ' . $Ananom_rec["NOMNOM"]);
                    }
                }
                break;
            case "returnAnatsppratsp":
                $Anatsp_rec = $this->praLib->GetAnatsp($_POST["retKey"], 'rowid');
                if ($Anatsp_rec) {
                    Out::valore($this->nameForm . '_pratsp', $Anatsp_rec['TSPCOD']);
                    Out::valore($this->nameForm . '_sportello', $Anatsp_rec['TSPDES']);
                    Out::setFocus('', $this->nameForm . '_pratsp');
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_allegati');
        App::$utente->removeKey($this->nameForm . '_passi');
        App::$utente->removeKey($this->nameForm . '_passiSel');
        App::$utente->removeKey($this->nameForm . '_requisiti');
        App::$utente->removeKey($this->nameForm . '_normative');
        App::$utente->removeKey($this->nameForm . '_currPranum');
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_insertTo');
        App::$utente->removeKey($this->nameForm . '_page');
        App::$utente->removeKey($this->nameForm . '_autoSearch');
        App::$utente->removeKey($this->nameForm . '_autoDescr');
        $this->close = true;
        itaLib::deletePrivateUploadPath();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    public function OpenRicerca() {
        Out::show($this->divRic);
        Out::hide($this->divRis);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Elenca');
        Out::setFocus('', $this->nameForm . '_pranum');
    }

    function AzzeraVariabili($svuotaElenco = true) {
        Out::clearFields($this->nameForm, $this->divDup);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::html($this->nameForm . "_Editore", '&nbsp;');
        if ($svuotaElenco == true) {
            Out::clearFields($this->nameForm, $this->divRic);
            TableView::disableEvents($this->gridAnapra);
            TableView::clearGrid($this->gridAnapra);
        }
        TableView::disableEvents($this->gridAllegati);
        TableView::clearGrid($this->gridAllegati);
        itaLib::clearAppsTempPath();
        $this->allegati = array();
        $this->passi = array();
        $this->currPranum = '';
        $this->rowidAppoggio = '';
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Conferma');
        Out::hide($this->nameForm . '_Elenca');
    }

    function getHtmlRes() {
        App::log($_POST);
        $htmlRes = "<p style = \"padding:10px;font-size:2em;text-decoration:underline;\"><b>Riepilogo Selezione</b></p>";
        $htmlRes .= "<p style = \"padding:2px;font-size:1.5em;\">Sportello: " . $_POST[$this->nameForm . "_pratsp"] . " - " . $_POST[$this->nameForm . "_sportello"] . "</p>";
        $htmlRes .= "<p style = \"padding:2px;font-size:1.5em;\">Responsabile: " . $_POST[$this->nameForm . "_prares"] . " - " . $_POST[$this->nameForm . "_nome"] . "</p>";
        $htmlRes .= "<p style = \"padding:2px;font-size:1.5em;\">Tipologia: " . $_POST[$this->nameForm . "_pratip"] . " - " . $_POST[$this->nameForm . "_tipologia"] . "</p>";
        $htmlRes .= "<p style = \"padding:2px;font-size:1.5em;\">Settore: " . $_POST[$this->nameForm . "_prastt"] . " - " . $_POST[$this->nameForm . "_settoreAttivita"] . "</p>";
        $htmlRes .= "<p style = \"padding:2px;font-size:1.5em;\">Attività: " . $_POST[$this->nameForm . "_praatt"] . " - " . $_POST[$this->nameForm . "_attivita"] . "</p>";
        return $htmlRes;
    }

    public function CreaSql() {
        $sql = "SELECT ANAPRA.ROWID AS ROWID, PRANUM, PRARES, TSPDES, EVTDESCR, TIPDES, SETDES, ATTDES,PRADVA, PRAAVA," .
                $this->PRAM_DB->strConcat("PRADES__1", "' '", "PRADES__2") . " AS DESCRIZIONE," .
                $this->PRAM_DB->strConcat("NOMCOG", "' '", "NOMNOM") . " AS RESPONSABILE
            FROM ANAPRA ANAPRA 
            LEFT OUTER JOIN ANANOM ANANOM ON ANAPRA.PRARES=ANANOM.NOMRES
            LEFT OUTER JOIN ITEEVT ITEEVT ON ANAPRA.PRANUM=ITEEVT.ITEPRA
            LEFT OUTER JOIN ANAEVENTI ANAEVENTI ON ANAEVENTI.EVTCOD=ITEEVT.IEVCOD
            LEFT OUTER JOIN ANATSP ANATSP ON ITEEVT.IEVTSP=ANATSP.TSPCOD
            LEFT OUTER JOIN ANATIP ANATIP ON ITEEVT.IEVTIP=ANATIP.TIPCOD 
            LEFT OUTER JOIN ANASET ANASET ON ITEEVT.IEVSTT=ANASET.SETCOD
            LEFT OUTER JOIN ANAATT ANAATT ON ITEEVT.IEVATT=ANAATT.ATTCOD
            WHERE PRANUM = PRANUM";
        if ($_POST[$this->nameForm . '_pratsp'] != "") {
            $where .= " AND IEVTSP='" . $_POST[$this->nameForm . '_pratsp'] . "'";
        }
        if ($_POST[$this->nameForm . '_pratip'] != "") {
            $where .= " AND IEVTIP='" . $_POST[$this->nameForm . '_pratip'] . "'";
        }
        if ($_POST[$this->nameForm . '_prastt'] != "") {
            $where .= " AND IEVSTT='" . $_POST[$this->nameForm . '_prastt'] . "'";
        }
        if ($_POST[$this->nameForm . '_praatt'] != "") {
            $where .= " AND IEVATT='" . $_POST[$this->nameForm . '_praatt'] . "'";
        }

        if ($_POST[$this->nameForm . '_prares'] != "") {
            $where .= " AND PRARES='" . $_POST[$this->nameForm . '_prares'] . "'";
        }

        if ($_POST['_search'] == true) {
            if ($_POST['PRANUM']) {
                $where .= " AND PRANUM = '" . addslashes($_POST['PRANUM']) . "'";
            }
            if ($_POST['DESCRIZIONE']) {
                $where .= " AND ".$this->PRAM_DB->strUpper('PRADES__1')." LIKE '%" . strtoupper(addslashes($_POST['DESCRIZIONE'])) . "%'";
            }
            if ($_POST['TIPDES']) {
                $where .= " AND ".$this->PRAM_DB->strUpper('TIPDES')." LIKE '%" . strtoupper(addslashes($_POST['TIPDES'])) . "%'";
            }
            if ($_POST['SETDES']) {
                $where .= " AND ".$this->PRAM_DB->strUpper('SETDES')." LIKE '%" . strtoupper(addslashes($_POST['SETDES'])) . "%'";
            }
            if ($_POST['ATTDES']) {
                $where .= " AND ".$this->PRAM_DB->strUpper('ATTDES')." LIKE '%" . strtoupper(addslashes($_POST['ATTDES'])) . "%'";
            }
            if ($_POST['PRAGIO']) {
                $where .= " AND PRAGIO LIKE '%" . addslashes($_POST['PRAGIO']) . "%'";
            }
            if ($_POST['RESPONSABILE']) {
                $where .= " AND ".$this->PRAM_DB->strUpper('NOMCOG')." LIKE '%" . strtoupper(addslashes($_POST['RESPONSABILE'])) . "%' OR ".$this->PRAM_DB->strUpper('NOMNOM')." LIKE '%" . strtoupper(addslashes($_POST['RESPONSABILE'])) . "%'";
            }
        }
        return $sql . $where;
    }

}

?>
