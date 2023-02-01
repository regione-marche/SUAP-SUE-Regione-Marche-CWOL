<?php

/**
 *
 * Expression Editor per Attivazione passo
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    14.10.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';

function praPassoProcExpr() {
    $praPassoProcExpr = new praPassoProcExpr();
    $praPassoProcExpr->parseEvent();
    return;
}

class praPassoProcExpr extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $nameForm = "praPassoProcExpr";
    public $divDettaglio = "praPassoProcExpr_divDettaglio";
    public $divDati = "praPassoProcExpr_divDati";
    public $divControllo = "praPassoProcExpr_divControllo";
    public $gridEspressione = "praPassoProcExpr_gridEspressione";
    public $dati = array();
    public $arrExpr = array();
    public $returnModel;
    public $returnEvent;
    public $rowidAppoggio;
    public $tipo;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
            $this->dati = App::$utente->getKey($this->nameForm . '_dati');
            $this->arrExpr = App::$utente->getKey($this->nameForm . '_arrExpr');
            $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
            $this->tipo = App::$utente->getKey($this->nameForm . '_tipo');
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
            App::$utente->setKey($this->nameForm . '_arrExpr', $this->arrExpr);
            App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
            App::$utente->setKey($this->nameForm . '_tipo', $this->tipo);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->rowidAppoggio = "";
                $this->returnModel = $_POST[$this->nameForm . '_returnModel'];
                $this->returnEvent = $_POST[$this->nameForm . '_returnEvent'];
                $this->dati = $_POST['dati'];
                $this->tipo = $_POST['tipo'];
                $this->arrExpr['praPassoProcExpr_returnModel'] = $this->dati['praPassoProcExpr_returnModel'];
                if ($this->dati['TITOLO']) {
                    Out::setDialogTitle($this->nameForm, $this->dati['TITOLO']);
                }
                if ($this->dati['SOTTOTITOLO']) {
                    Out::html($this->nameForm . "_hd1", $this->dati['SOTTOTITOLO'], '');
                }
                if ($this->dati['SPEGNIDUPLICA'] === true) {
                    Out::hide($this->nameForm . '_DuplicaExp');
                }
                $this->creaCombo();
                $this->creaComboCampi($this->dati['TABELLA']);
                $this->praLib->creaComboCondizioni($this->nameForm . '_Condizione');
                $this->Dettaglio();
                break;
            case 'delGridRow': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->gridEspressione:
                        if (array_key_exists($_POST['rowid'], $this->arrExpr)) {
                            if ($this->arrExpr[$_POST['rowid']]['OPERATORE'] == "" && count($this->arrExpr) > 1) {
                                $this->arrExpr[$_POST['rowid'] + 1]['OPERATORE'] = "";
                            }
                            unset($this->arrExpr[$_POST['rowid']]);
                        }
                        $ita_grid01 = new TableView(
                            $this->gridEspressione, array('arrayTable' => $this->arrExpr,
                            'rowIndex' => 'idx')
                        );
                        TableView::enableEvents($this->gridEspressione);
                        $ita_grid01->getDataPage('json', true);
                        //
                        $strExpr = "";
                        if ($this->arrExpr) {
                            $this->arrExpr = array_values($this->arrExpr);
                            $strExpr = serialize($this->arrExpr);
                        }
                        $this->dati['ITEATE'] = $strExpr;
                        $this->dettaglio();
                        break;
                }
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiorna':
                        $this->dati['ITEATE'] = $_POST[$this->nameForm . '_ctrSerializzato'];

                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_ApriControllo':
                        $model = 'praCondizioni';
                        itaLib::openForm($model);
                        $praCondizioni = itaModel::getInstance($model);
                        $praCondizioni->setEvent('openform');
                        $praCondizioni->setReturnModel($this->nameForm);

                        if ($this->dati['ITEATE'] && unserialize($this->dati['ITEATE'])) {
                            $praCondizioni->setArrayEspressioni(unserialize($this->dati['ITEATE']));
                        }
                        
                        $praCondizioni->setCodiceProcedimento($this->dati['ITECOD']);
                        $praCondizioni->setCodicePasso($this->dati['ITEKEY']);

                        $praCondizioni->parseEvent();
                        break;

                    case $this->nameForm . '_CancellaControllo':
                        $this->dati['ITEATE'] = '';
                        $this->dettaglio();
                        break;

                    case $this->nameForm . '_CreaCtr':
                        switch ($_POST[$this->nameForm . '_Condizione']) {
                            case 'uguale':
                                $simbolo = "==";
                                break;
                            case 'diverso':
                                $simbolo = "!=";
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
                            $arrExpr[] = array(
                                "CAMPO" => $_POST[$this->nameForm . '_Campi'],
                                "CONDIZIONE" => $simbolo,
                                "VALORE" => $_POST[$this->nameForm . '_ValoreCtr'],
                                "OPERATORE" => $operatore
                            );
                            $strExpr = serialize($arrExpr);
                            $this->dati['ITEATE'] = $strExpr;
                            $this->dettaglio();
                        }
                        break;
                    case $this->nameForm . '_DuplicaExp':
                        $whereTipo = "AND $this->tipo <>''";
                        if ($this->tipo == "ITECONTROLLI") {
                            $whereTipo = "";
                        }
                        if ($this->dati['MODEL'] != '' && $this->dati['MODEL'] == 'praPassoRich') {
                            $where = " WHERE RICNUM = '" . $this->dati['RICNUM'] . "' AND ITEPUB = 1 $whereTipo";
                            praRic::praRicItepas($this->nameForm, 'RICITE', $where, '', 'Scegliere il passo da dove duplicare la condizione');
                        } else {
                            $where = " WHERE ITECOD = '" . $this->dati['ITECOD'] . "' AND ITEPUB = 1 $whereTipo";
                            praRic::praRicItepas($this->nameForm, 'ITEPAS', $where, '', 'Scegliere il passo da dove duplicare la condizione');
                        }
                        break;
                    case $this->nameForm . '_CancellaCtr':
                        $this->dati['ITEATE'] = '';
                        $this->dettaglio();
                        break;
                    case $this->nameForm . '_ConfermaAccoda':
                        Out::msgQuestion("ATTENZIONE!", "Con quale operaratore vuoi accodare la condizione?", array(
                            'F8-E' => array('id' => $this->nameForm . '_ConfermaAND', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Oppure' => array('id' => $this->nameForm . '_ConfermaOR', 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                        );
                        break;
                    case $this->nameForm . '_ConfermaAND':
                        $itepas_rec = $this->praLib->GetItepas($this->rowidAppoggio, "rowid");
                        $arrExpr1 = unserialize($this->dati['ITEATE']);
                        $arrExpr2 = unserialize($itepas_rec[$this->tipo]);
                        foreach ($arrExpr2 as $key => $condiz) {
                            if ($condiz['OPERATORE'] == "") {
                                $arrExpr2[$key]['OPERATORE'] = "AND";
                                break;
                            }
                        }
                        $this->arrExpr = array_merge($arrExpr1, $arrExpr2);
                        $strExpr = serialize($this->arrExpr);
                        $this->dati['ITEATE'] = $strExpr;
                        $this->dettaglio();
                        break;
                    case $this->nameForm . '_ConfermaOR':
                        $itepas_rec = $this->praLib->GetItepas($this->rowidAppoggio, "rowid");
                        $arrExpr1 = unserialize($this->dati['ITEATE']);
                        $arrExpr2 = unserialize($itepas_rec[$this->tipo]);
                        foreach ($arrExpr2 as $key => $condiz) {
                            if ($condiz['OPERATORE'] == "") {
                                $arrExpr2[$key]['OPERATORE'] = "OR";
                                break;
                            }
                        }
                        $this->arrExpr = array_merge($arrExpr1, $arrExpr2);
                        $strExpr = serialize($this->arrExpr);
                        $this->dati['ITEATE'] = $strExpr;
                        $this->dettaglio();
                        break;
                    case $this->nameForm . '_ConfermaSovrascrivi':
                        $itepas_rec = $this->praLib->GetItepas($this->rowidAppoggio, "rowid");
                        $this->arrExpr = unserialize($itepas_rec[$this->tipo]);
                        $strExpr = serialize($this->arrExpr);
                        $this->dati['ITEATE'] = $strExpr;
                        $this->dettaglio();
                        break;
                    case $this->nameForm . '_Campi_butt':
                        $praLibVar = new praLibVariabili();
//                        if ($this->dati[$this->idxDati]['ITEPUB'] == 1) {
//                            $praLibVar->setFrontOfficeFlag(true);
//                        }
                        $praLibVar->setFrontOfficeFlag(true);
                        $praLibVar->setCodiceProcedimento($this->dati['ITECOD']);
                        $praLibVar->setChiavePasso($this->dati['ITEKEY']);
                        $arrayLegenda = $praLibVar->getLegendaProcedimento('adjacency', 'none');
                        praRic::ricVariabili($arrayLegenda, $this->nameForm, 'returnVariabili', true);
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->gridEspressione:
                        $chiave = $_POST['rowid'];
                        $colname = $_POST['cellname'];
                        $colvalue = $_POST['value'];
                        $this->arrExpr[$chiave][$colname] = $colvalue;
                        $strExpr = serialize($this->arrExpr);
                        $this->dati['ITEATE'] = $strExpr;
                        $this->dettaglio();
                        break;
                }
            case 'returnVariabili':
                Out::valore($this->nameForm . '_Campi', $_POST['rowData']['markupkey']);
                break;
            case "returnItepas":
                // Out::msgInfo("verifico model",  $this->dati['MODEL']);
                if ($this->arrExpr) {
                    Out::msgQuestion("ATTENZIONE!", "E' già stata trovata una condizione, vuoi sovrascriverla o accodarla?", array(
                        'F8-Accoda' => array('id' => $this->nameForm . '_ConfermaAccoda', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Sovrascrivi' => array('id' => $this->nameForm . '_ConfermaSovrascrivi', 'model' => $this->nameForm, 'shortCut' => "f5")
                        )
                    );
                    $this->rowidAppoggio = $_POST['retKey'];
                    break;
                }
                if ($this->dati['MODEL'] != 'praPassoRich') {
                    $itepas_rec = $this->praLib->GetItepas($_POST['retKey'], "rowid");
                } else {
                    $codice = $_POST['retKey'];
                    $itepas_rec = $this->praLib->GetRicite($codice, 'rowid');
                }

                $this->arrExpr = unserialize($itepas_rec[$this->tipo]);
                $strExpr = serialize($this->arrExpr);
                $this->dati['ITEATE'] = $strExpr;
                $this->dettaglio();

                break;

            case 'returnPraCondizioni':
                $this->dati['ITEATE'] = $_POST['returnCondizione'];
                $this->Dettaglio();
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_dati');
        App::$utente->removeKey($this->nameForm . '_arrExpr');
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_tipo');
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

//    function OpenRicerca() {
//        Out::show($this->divDettaglio, '');
//        Out::show($this->divDati, '');
//        Out::show($this->divControllo, '');
//        Out::clearFields($this->nameForm, $this->divDettaglio);
//        Out::clearFields($this->nameForm, $this->divDati);
//        Out::clearFields($this->nameForm, $this->divControllo);
//        Out::show($this->nameForm . '_Aggiorna');
//        Out::show($this->nameForm);
//    }

    public function Dettaglio() {
        Out::valore($this->nameForm . '_Espressione', $this->praLib->DecodificaControllo($this->dati['ITEATE']));
        $this->arrExpr = array();
        if ($this->dati['ITEATE']) {
            $griglia = $this->nameForm . "_gridEspressione";
            $this->arrExpr = unserialize($this->dati['ITEATE']);
            $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $this->arrExpr,
                'rowIndex' => 'idx')
            );
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(1000);
            TableView::enableEvents($griglia);
            $ita_grid01->getDataPage('json', true);
        }
        Out::valore($this->nameForm . '_ctrSerializzato', $this->dati['ITEATE']);
        Out::clearFields($this->nameForm, $this->divControllo);
        Out::show($this->nameForm . "_divElenco");
        Out::show($this->nameForm . "_divEspressione");
        if ($this->dati['ITEATE'] == '') {
            Out::hide($this->divRadio, '');
        } else {
            Out::show($this->divRadio, '');
            Out::attributo($this->nameForm . "_FlagAnd", "checked", "0", "checked");
        }
    }

    public function creaCombo() {
        Out::select($this->nameForm . '_AzioneCtr', 1, 1, "1", 'Continua');
        Out::select($this->nameForm . '_AzioneCtr', 1, 2, "0", 'Blocca');
    }

    public function creaComboCampi($dataset) {
        return;
        switch ($dataset) {
            case "ITEDAG":
                $sql = "
            SELECT
                ITEDAG.ITEKEY AS ITEKEY,
                ITEDAG.ITECOD AS ITECOD,
                ITEDAG.ITDKEY AS ITDKEY,
                ITEDAG.ITDTIP AS ITDTIP
            FROM
                ITEPAS ITEPAS
            LEFT OUTER JOIN
                ITEDAG ITEDAG
            ON
                ITEPAS.ITEKEY=ITEDAG.ITEKEY
            WHERE ITEPAS.ITECOD='" . $this->dati['ITECOD'] . "' AND ITEPAS.ITEDAT = 1 ";
                $Itedag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                if ($Itedag_tab) {
                    foreach ($Itedag_tab as $key => $Itedag_rec) {
                        Out::select($this->nameForm . '_Campi', 1, $Itedag_rec['ITDKEY'], "0", $Itedag_rec['ITDKEY']);
                    }
                }
                break;
            case "RICDAG":
                $sql = "
            SELECT
                RICDAG.ITEKEY AS ITEKEY,
                RICDAG.ITECOD AS ITECOD,
                RICDAG.DAGKEY AS DAGKEY,
                RICDAG.DAGTIP AS DAGTIP,
                RICDAG.DAGNUM AS DAGNUM
            FROM
                RICITE RICITE
            LEFT OUTER JOIN
                RICDAG RICDAG
            ON
                RICITE.ITEKEY=RICDAG.ITEKEY AND RICITE.RICNUM = RICDAG.DAGNUM
            WHERE RICITE.RICNUM = '" . $this->dati['RICNUM'] . "' AND RICITE.ITECOD='" . $this->dati['ITECOD'] . "' AND RICITE.ITEDAT = 1 ";
                $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                if ($Ricdag_tab) {
                    foreach ($Ricdag_tab as $key => $Ricdag_rec) {
                        Out::select($this->nameForm . '_Campi', 1, $Ricdag_rec['DAGKEY'], "0", $Ricdag_rec['DAGKEY']);
                    }
                }
                break;
            default:
                break;
        }
    }

//    public function creaComboCondizioni() {
//        Out::select($this->nameForm . '_Condizione', 1, "uguale", "1", "Uguale a");
//        Out::select($this->nameForm . '_Condizione', 1, "diverso", "0", "Diverso da");
//        Out::select($this->nameForm . '_Condizione', 1, "maggiore", "0", "Maggiore a");
//        Out::select($this->nameForm . '_Condizione', 1, "minore", "0", "Minore a");
//        Out::select($this->nameForm . '_Condizione', 1, "maggiore-uguale", "0", "Maggiore/Uguale a");
//        Out::select($this->nameForm . '_Condizione', 1, "minore-uguale", "0", "Minore/Uguale a");
//    }

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
