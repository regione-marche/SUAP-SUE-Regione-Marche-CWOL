<?php

/**
 *
 * Expression Editor per disegno dato aggiuntivo e default
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    20.09.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';

function praDagExprOut() {
    $praDagExprOut = new praDagExprOut();
    $praDagExprOut->parseEvent();
    return;
}

class praDagExprOut extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $nameForm = "praDagExprOut";
    public $divDettaglio = "praDagExprOut_divDettaglio";
    public $divDati = "praDagExprOut_divDati";
    public $divControllo = "praDagExprOut_divControllo";
    public $gridEspressione = "praDagExprOut_gridEspressione";
    public $dati = array();
    public $arrExpr = array();
    public $returnModel;
    public $returnEvent;
    public $rowid_itepas;
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
            $this->rowid_itepas = App::$utente->getKey($this->nameForm . '_rowid_itepas');
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
            App::$utente->setKey($this->nameForm . '_rowid_itepas', $this->rowid_itepas);
            App::$utente->setKey($this->nameForm . '_tipo', $this->tipo);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->rowid_itepas = "";
                $this->returnModel = $_POST[$this->nameForm . '_returnModel'];
                $this->returnEvent = $_POST[$this->nameForm . '_returnEvent'];
                $this->dati = $_POST['dati'];
                $this->tipo = $_POST['tipo'];
                if ($this->dati['TITOLO']) {
                    Out::setDialogTitle($this->nameForm, $this->dati['TITOLO']);
                }
                $this->creaComboRadioGroupRef();
                $this->praLib->CreaComboTipiPosizione($this->nameForm . '_Posizione');
                $this->praLib->CreaComboTipiInput($this->nameForm . '_TipoInput');
                $this->praLib->CreaComboTipiValueCheck($this->nameForm . '_valueCheck');
                $this->praLib->CreaComboTipiHtmlPosition($this->nameForm . '_htmlPosition');
                Out::attributo($this->nameForm . "_Costante", "checked", "0", "checked");
                Out::attributo($this->nameForm . '_Valore', 'readonly', '1');
                Out::show($this->nameForm . "_Valore_field", '');
                Out::hide($this->nameForm . "_ValoreTextArea_field", '');
                Out::hide($this->nameForm . "_Valore_butt", '');
                $this->Dettaglio();
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiorna':
                        if (!$_POST[$this->nameForm . '_ctrSerializzato']) {
                            Out::msgStop("Errore", "Espressione di controllo mancante.");
                            Out::setFocus($this->nameForm, $this->nameForm . '_ValoreCtr');
                            break;
                        }
                        if ($_POST[$this->nameForm . '_ValoreCampo'] == 'T') {
                            $this->dati['ESPRESSIONE']['ITDVAL'] = $_POST[$this->nameForm . '_ValoreTextArea'];
                        } else {
                            $this->dati['ESPRESSIONE']['ITDVAL'] = $_POST[$this->nameForm . '_Valore'];
                        }
                        $this->dati['ESPRESSIONE']['ITDEXPROUT'] = serialize($this->exprOut);
                        $this->dati['ESPRESSIONE']['ITDLAB'] = $_POST[$this->nameForm . '_Etichetta'];
                        $this->dati['ESPRESSIONE']['ITDTIC'] = $_POST[$this->nameForm . '_TipoInput'];
                        $this->dati['ESPRESSIONE']['ITDROL'] = $_POST[$this->nameForm . '_ReadOnly'];
                        $this->dati['ESPRESSIONE']['ITDVCA'] = $_POST[$this->nameForm . '_Valida'];
                        $this->dati['ESPRESSIONE']['ITDREV'] = $_POST[$this->nameForm . '_RegExpr'];
                        $this->dati['ESPRESSIONE']['ITDLEN'] = $_POST[$this->nameForm . '_MaxLength'];
                        $this->dati['ESPRESSIONE']['ITDDIM'] = $_POST[$this->nameForm . '_Size'];
                        $this->dati['ESPRESSIONE']['ITDDIZ'] = $_POST[$this->nameForm . '_ValoreCampo'];
                        $this->dati['ESPRESSIONE']['ITDACA'] = $_POST[$this->nameForm . '_Acapo'];
                        $this->dati['ESPRESSIONE']['ITDPOS'] = $_POST[$this->nameForm . '_Posizione'];
                        $this->dati['ESPRESSIONE']['ITDLABSTYLE'] = $_POST[$this->nameForm . '_StyleLabel'];
                        $this->dati['ESPRESSIONE']['ITDFIELDSTYLE'] = $_POST[$this->nameForm . '_StyleCampo'];
                        $this->dati['ESPRESSIONE']['ITDFIELDCLASS'] = $_POST[$this->nameForm . '_ClassCampo'];
                        if ($_POST[$this->nameForm . '_TipoInput'] == "Html") {
                            $this->dati['ESPRESSIONE']['ITDDIZ'] = "H";
                            $this->dati['ESPRESSIONE']['ITDROL'] = 1;
                            if ($_POST[$this->nameForm . '_htmlPosition']) {
                                $this->dati['ESPRESSIONE']['ITDMETA']['HTMLPOS'] = $_POST[$this->nameForm . '_htmlPosition'];
                            }
                        } elseif ($_POST[$this->nameForm . '_TipoInput'] == "CheckBox") {
                            $this->dati['ESPRESSIONE']['ITDMETA']['ATTRIBUTICAMPO']['RETURNVALUES'] = $_POST[$this->nameForm . '_valueCheck'];
                        } elseif ($_POST[$this->nameForm . '_TipoInput'] == "TextArea") {
                            $this->dati['ESPRESSIONE']['ITDMETA']['ATTRIBUTICAMPO']['COLS'] = $_POST[$this->nameForm . '_cols'];
                            $this->dati['ESPRESSIONE']['ITDMETA']['ATTRIBUTICAMPO']['ROWS'] = $_POST[$this->nameForm . '_rows'];
                        } elseif ($_POST[$this->nameForm . '_TipoInput'] == "RadioButton") {
                            $this->dati['ESPRESSIONE']['ITDMETA']['ATTRIBUTICAMPO']['NAME'] = $_POST[$this->nameForm . '_RadioGroupRef'];
                            $this->dati['ESPRESSIONE']['ITDMETA']['ATTRIBUTICAMPO']['RETURNVALUE'] = $_POST[$this->nameForm . '_valueRitorno'];
                        }
                        $this->dati['ESPRESSIONE']['EXPCTR'] = $_POST[$this->nameForm . '_ctrSerializzato'];
//                        $this->dati['ESPRESSIONE']['ITDDIZ'] = $_POST[$this->nameForm . '_ValoreCampo'];
//                        if ($_POST[$this->nameForm . '_ValoreCampo'] == 'T') {
//                            $this->dati['ESPRESSIONE']['ITDVAL'] = $_POST[$this->nameForm . '_ValoreTextArea'];
//                        } else {
//                            $this->dati['ESPRESSIONE']['ITDVAL'] = $_POST[$this->nameForm . '_Valore'];
//                        }
//                        $this->dati['ESPRESSIONE']['ITDROL'] = $_POST[$this->nameForm . '_ReadOnly'];

                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_DuplicaExp':
                        $where = " WHERE ITECOD = '" . $this->dati['ITECOD'] . "' AND ITEPUB = 1 AND $this->tipo <>''";
                        praRic::praRicItepas($this->nameForm, 'ITEPAS', $where, '', 'Scegliere il passo da dove duplicare la condizione');
                        break;

                    case $this->nameForm . '_ApriControllo':
                        $model = 'praCondizioni';
                        itaLib::openForm($model);
                        $praCondizioni = itaModel::getInstance($model);
                        $praCondizioni->setEvent('openform');
                        $praCondizioni->setReturnModel($this->nameForm);

                        if ($this->dati['ESPRESSIONE']['EXPCTR'] && unserialize($this->dati['ESPRESSIONE']['EXPCTR'])) {
                            $praCondizioni->setArrayEspressioni(unserialize($this->dati['ESPRESSIONE']['EXPCTR']));
                        }
                        
                        $praCondizioni->setCodiceProcedimento($this->dati['ITECOD']);
                        $praCondizioni->setCodicePasso($this->dati['ITEKEY']);

                        $praCondizioni->parseEvent();
                        break;

                    case $this->nameForm . '_CancellaControllo':
                        $this->dati['ESPRESSIONE']['EXPCTR'] = '';
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
                        $itepas_rec = $this->praLib->GetItepas($this->rowid_itepas, "rowid");
                        $arrExpr1 = unserialize($this->dati['ESPRESSIONE']['EXPCTR']);
                        $arrExpr2 = unserialize($itepas_rec[$this->tipo]);
                        foreach ($arrExpr2 as $key => $condiz) {
                            if ($condiz['OPERATORE'] == "") {
                                $arrExpr2[$key]['OPERATORE'] = "AND";
                                break;
                            }
                        }
                        $this->arrExpr = array_merge($arrExpr1, $arrExpr2);
                        $strExpr = serialize($this->arrExpr);
                        $this->dati['ESPRESSIONE']['EXPCTR'] = $strExpr;
                        $this->dettaglio();
                        break;
                    case $this->nameForm . '_ConfermaOR':
                        $itepas_rec = $this->praLib->GetItepas($this->rowid_itepas, "rowid");
                        $arrExpr1 = unserialize($this->dati['ESPRESSIONE']['EXPCTR']);
                        $arrExpr2 = unserialize($itepas_rec[$this->tipo]);
                        foreach ($arrExpr2 as $key => $condiz) {
                            if ($condiz['OPERATORE'] == "") {
                                $arrExpr2[$key]['OPERATORE'] = "OR";
                                break;
                            }
                        }
                        $this->arrExpr = array_merge($arrExpr1, $arrExpr2);
                        $strExpr = serialize($this->arrExpr);
                        $this->dati['ESPRESSIONE']['EXPCTR'] = $strExpr;
                        $this->dettaglio();
                        break;
                    case $this->nameForm . '_ConfermaSovrascrivi':
                        $itepas_rec = $this->praLib->GetItepas($this->rowid_itepas, "rowid");
                        $this->arrExpr = unserialize($itepas_rec[$this->tipo]);
                        $strExpr = serialize($this->arrExpr);
                        $this->dati['ESPRESSIONE']['EXPCTR'] = $strExpr;
                        $this->dettaglio();
                        break;
                    case $this->nameForm . '_Campi_butt':
                        $praLibVar = new praLibVariabili();
                        $praLibVar->setFrontOfficeFlag(true);
                        $praLibVar->setCodiceProcedimento($this->dati['ITECOD']);
                        $praLibVar->setChiavePasso($this->dati['ITEKEY']);
                        $arrayLegenda = $praLibVar->getLegendaProcedimento('adjacency', 'none');
                        praRic::ricVariabili($arrayLegenda, $this->nameForm, 'returnVariabili', true);
                        break;
                    case $this->nameForm . '_Valore_butt':
                        $praLibVar = new praLibVariabili();
                        $praLibVar->setFrontOfficeFlag(true);
                        $praLibVar->setCodiceProcedimento($this->dati['ITECOD']);
                        $praLibVar->setChiavePasso($this->dati['ITEKEY']);
                        $arrayLegenda = $praLibVar->getLegendaProcedimento('adjacency', 'none');
                        praRic::ricVariabili($arrayLegenda, $this->nameForm, 'returnVariabiliValore', true);
                        break;
                    case $this->nameForm . '_ValoreTextArea_butt':
                        $praLibVar = new praLibVariabili();
                        $praLibVar->setFrontOfficeFlag(true);
                        $praLibVar->setCodiceProcedimento($this->dati['ITECOD']);
                        $praLibVar->setChiavePasso($this->dati['ITEKEY']);
                        $arrayLegenda = $praLibVar->getLegendaProcedimento('adjacency');
                        praRic::ricVariabili($arrayLegenda, $this->nameForm, 'returnVariabiliTextArea', true);
                        break;
                    case $this->nameForm . '_htmlButton':
                        $praLibVar = new praLibVariabili();
                        if ($this->datoAggiuntivo['ITEPUB'] == 1) {
                            $praLibVar->setFrontOfficeFlag(true);
                        }
                        $praLibVar->setCodiceProcedimento($this->datoAggiuntivo['ITECOD']);
                        $praLibVar->setChiavePasso($this->datoAggiuntivo['ITEKEY']);
                        $dictionaryLegend = $praLibVar->getLegendaProcedimento('adjacency', 'smarty');

                        $model = 'utiEditDiag';
                        $valore = $_POST[$this->nameForm . '_Valore'];

                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['edit_text'] = $valore;
                        $_POST['returnModel'] = $this->nameForm;
                        $_POST['returnEvent'] = 'returnEditDiag';
                        $_POST['returnField'] = $this->nameForm . '_pdfTestoBase';
                        $_POST['dictionaryLegend'] = $dictionaryLegend;
                        $_POST['readonly'] = false;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case 'close-portlet':
                        $this->close();
                        break;
                }
                break;

            case 'returnPraCondizioni':
                $this->dati['ESPRESSIONE']['EXPCTR'] = $_POST['returnCondizione'];
                $this->Dettaglio();
                break;

            case 'onChange': // Evento OnChange
                switch ($_POST['id']) {
                    case $this->nameForm . '_TipoInput':
                        Out::show($this->nameForm . "_Acapo_field", '');
                        Out::show($this->nameForm . "_Template_field", '');
                        Out::show($this->nameForm . "_Size_field", '');
                        Out::show($this->nameForm . "_cols_field", '');
                        Out::show($this->nameForm . "_rows_field", '');
                        Out::show($this->nameForm . "_StyleCampo_field", '');
                        Out::hide($this->nameForm . "_RadioGroupRef_field", '');
                        Out::hide($this->nameForm . "_valueRitorno_field", '');
                        Out::hide($this->nameForm . "_valueCheck_field", '');
                        if ($_POST[$this->nameForm . '_TipoInput'] == "Text" || $_POST[$this->nameForm . '_TipoInput'] == "Password") {
                            Out::show($this->nameForm . "_MaxLength_field", '');
                            Out::show($this->nameForm . "_Size_field", '');
                            Out::hide($this->nameForm . "_divHtml", '');
                            Out::show($this->nameForm . "_divValore", '');
                            Out::hide($this->nameForm . "_rows_field", '');
                            Out::hide($this->nameForm . "_cols_field", '');
                        } else if ($_POST[$this->nameForm . '_TipoInput'] == "TextArea") {
                            Out::show($this->nameForm . "_MaxLength_field", '');
                            Out::show($this->nameForm . "_rows_field", '');
                            Out::show($this->nameForm . "_cols_field", '');
                            Out::hide($this->nameForm . "_Size_field", '');
                            Out::hide($this->nameForm . "_divHtml", '');
                            Out::show($this->nameForm . "_divValore", '');
                        } else if ($_POST[$this->nameForm . '_TipoInput'] == "CheckBox") {
                            Out::hide($this->nameForm . "_MaxLength_field", '');
                            Out::hide($this->nameForm . "_Size_field", '');
                            Out::hide($this->nameForm . "_divHtml", '');
                            Out::show($this->nameForm . "_divValore", '');
                            Out::show($this->nameForm . "_valueCheck_field", '');
                            Out::hide($this->nameForm . "_rows_field", '');
                            Out::hide($this->nameForm . "_cols_field", '');
                        } else if ($_POST[$this->nameForm . '_TipoInput'] == "RadioButton") {
                            Out::hide($this->nameForm . "_Template_field", '');
                            Out::hide($this->nameForm . "_MaxLength_field", '');
                            Out::hide($this->nameForm . "_Size_field", '');
                            Out::show($this->nameForm . "_RadioGroupRef_field", '');
                            Out::show($this->nameForm . "_valueRitorno_field", '');
                            Out::hide($this->nameForm . "_divHtml", '');
                            Out::show($this->nameForm . "_divValore", '');
                            Out::hide($this->nameForm . "_rows_field", '');
                            Out::hide($this->nameForm . "_cols_field", '');
                        } else if ($_POST[$this->nameForm . '_TipoInput'] == "Html") {
                            Out::hide($this->nameForm . "_divValore", '');
                            Out::show($this->nameForm . "_divHtml", '');
                            Out::show($this->nameForm . "_Acapo_field", '');
                            Out::hide($this->nameForm . "_Size_field", '');
                            Out::hide($this->nameForm . "_cols_field", '');
                            Out::hide($this->nameForm . "_rows_field", '');
                            Out::hide($this->nameForm . "_StyleCampo_field", '');
                            Out::hide($this->nameForm . "_rows_field", '');
                            Out::hide($this->nameForm . "_cols_field", '');
                        } else if ($_POST[$this->nameForm . '_TipoInput'] == "RadioGroup") {
                            Out::hide($this->nameForm . "_divHtml", '');
                            Out::hide($this->nameForm . "_Template_field", '');
                            Out::show($this->nameForm . "_Acapo_field", '');
                            Out::hide($this->nameForm . "_Size_field", '');
                            Out::hide($this->nameForm . "_cols_field", '');
                            Out::hide($this->nameForm . "_rows_field", '');
                            Out::hide($this->nameForm . "_StyleCampo_field", '');
                            Out::hide($this->nameForm . "_rows_field", '');
                            Out::hide($this->nameForm . "_cols_field", '');
                        } else {
                            Out::hide($this->nameForm . "_MaxLength_field", '');
                            Out::hide($this->nameForm . "_Size_field", '');
                            Out::hide($this->nameForm . "_divHtml", '');
                            Out::show($this->nameForm . "_divValore", '');
                            Out::hide($this->nameForm . "_rows_field", '');
                            Out::hide($this->nameForm . "_cols_field", '');
                        }
                        break;

                    case $this->nameForm . '_Costante':
                        Out::show($this->nameForm . "_Valore_field", '');
                        Out::hide($this->nameForm . "_ValoreTextArea_field", '');
                        Out::hide($this->nameForm . "_Valore_butt", '');
                        Out::attributo($this->nameForm . '_Valore', 'readonly', '1');
                        break;
                    case $this->nameForm . '_Dizionario':
                        Out::show($this->nameForm . "_Valore_field", '');
                        Out::hide($this->nameForm . "_ValoreTextArea_field", '');
                        Out::show($this->nameForm . "_Valore_butt", '');
                        Out::attributo($this->nameForm . '_Valore', 'readonly', '0');
                        break;
                    case $this->nameForm . '_Template':
                        Out::hide($this->nameForm . "_Valore_field", '');
                        Out::show($this->nameForm . "_ValoreTextArea_field", '');
                        Out::attributo($this->nameForm . '_Valore', 'readonly', '1');
                        break;
                }
                break;

            case 'returnVariabili':
                Out::valore($this->nameForm . '_Campi', $_POST['rowData']['markupkey']);
                break;

            case 'returnVariabiliValore':
                Out::valore($this->nameForm . '_Valore', $_POST['rowData']['markupkey']);
                break;

            case 'returnVariabiliTextArea':
                Out::codice("$('#" . $this->nameForm . '_ValoreTextArea' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;

            case "returnItepas":
                if ($this->arrExpr) {
                    Out::msgQuestion("ATTENZIONE!", "E' già stata trovata una condizione, vuoi sovrascriverla o accodarla?", array(
                        'F8-Accoda' => array('id' => $this->nameForm . '_ConfermaAccoda', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Sovrascrivi' => array('id' => $this->nameForm . '_ConfermaSovrascrivi', 'model' => $this->nameForm, 'shortCut' => "f5")
                        )
                    );
                    $this->rowid_itepas = $_POST['retKey'];
                    break;
                }
                $itepas_rec = $this->praLib->GetItepas($_POST['retKey'], "rowid");
                $this->arrExpr = unserialize($itepas_rec[$this->tipo]);
                $strExpr = serialize($this->arrExpr);
                $this->dati['ESPRESSIONE']['EXPCTR'] = $strExpr;
                $this->dettaglio();
                break;

            case "returnEditDiag":
                $this->dati['ESPRESSIONE']['ITDVAL'] = $_POST['returnText'];
                Out::valore($this->nameForm . '_Valore', $this->dati['ESPRESSIONE']['ITDVAL']);
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_dati');
        App::$utente->removeKey($this->nameForm . '_arrExpr');
        App::$utente->removeKey($this->nameForm . '_rowid_itepas');
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
        }
    }

    public function creaComboRadioGroupRef() {
        foreach ($this->dati['RADIOGROUPREF'] as $campo) {
            Out::select($this->nameForm . '_RadioGroupRef', 1, $campo['ITDKEY'], "0", $campo['ITDKEY']);
        }
    }

    public function Dettaglio() {
        Out::valore($this->nameForm . '_Espressione', $this->praLib->DecodificaControllo($this->dati['ESPRESSIONE']['EXPCTR']));
        $this->arrExpr = array();
        if ($this->dati['ESPRESSIONE']['EXPCTR']) {
            $griglia = $this->nameForm . "_gridEspressione";
            $this->arrExpr = unserialize($this->dati['ESPRESSIONE']['EXPCTR']);
            $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $this->arrExpr,
                'rowIndex' => 'idx')
            );
            TableView::enableEvents($griglia);
            $ita_grid01->getDataPage('json', true);
        }
        Out::valore($this->nameForm . '_ctrSerializzato', $this->dati['ESPRESSIONE']['EXPCTR']);
        Out::clearFields($this->nameForm, $this->divControllo);
        //Out::hide($this->nameForm . "_divElenco");
        Out::show($this->nameForm . "_divEspressione");
        if ($this->dati['ESPRESSIONE']['EXPCTR'] == '') {
            Out::hide($this->divRadio, '');
        } else {
            Out::show($this->divRadio, '');
            Out::attributo($this->nameForm . "_FlagAnd", "checked", "0", "checked");
        }

        Out::hide($this->nameForm . "_RadioGroupRef_field", '');
        Out::hide($this->nameForm . "_valueCheck_field", '');
        Out::hide($this->nameForm . "_valueRitorno_field", '');

        if ($this->dati['ESPRESSIONE']['ITDTIC'] == "Text" || $this->dati['ESPRESSIONE']['ITDTIC'] == "Password" || $this->dati['ESPRESSIONE']['ITDTIC'] == "") {
            Out::show($this->nameForm . "_MaxLength_field", '');
            Out::show($this->nameForm . "_Size_field", '');
            Out::hide($this->nameForm . "_divHtml", '');
            Out::show($this->nameForm . "_divValore", '');
            Out::hide($this->nameForm . "_rows_field", '');
            Out::hide($this->nameForm . "_cols_field", '');
        } else if ($this->dati['ESPRESSIONE']['ITDTIC'] == "TextArea") {
            Out::show($this->nameForm . "_MaxLength_field", '');
            Out::hide($this->nameForm . "_Size_field", '');
            Out::hide($this->nameForm . "_divHtml", '');
            Out::show($this->nameForm . "_divValore", '');
            Out::show($this->nameForm . "_rows_field", '');
            Out::show($this->nameForm . "_cols_field", '');
        } else if ($this->dati['ESPRESSIONE']['ITDTIC'] == "CheckBox") {
            Out::hide($this->nameForm . "_MaxLength_field", '');
            Out::hide($this->nameForm . "_Size_field", '');
            Out::hide($this->nameForm . "_divHtml", '');
            Out::show($this->nameForm . "_divValore", '');
            Out::hide($this->nameForm . "_rows_field", '');
            Out::hide($this->nameForm . "_cols_field", '');
            Out::show($this->nameForm . "_valueCheck_field", '');
            Out::valore($this->nameForm . '_valueCheck', $this->dati['ESPRESSIONE']['ITDMETA']['ATTRIBUTICAMPO']['RETURNVALUES']);
        } else if ($this->dati['ESPRESSIONE']['ITDTIC'] == "RadioGroup") {
            Out::hide($this->nameForm . "_divHtml", '');
            Out::hide($this->nameForm . "_Template_field", '');
            Out::show($this->nameForm . "_Acapo_field", '');
            Out::hide($this->nameForm . "_Size_field", '');
            Out::hide($this->nameForm . "_cols_field", '');
            Out::hide($this->nameForm . "_rows_field", '');
            Out::hide($this->nameForm . "_StyleCampo_field", '');
            Out::hide($this->nameForm . "_rows_field", '');
            Out::hide($this->nameForm . "_cols_field", '');
        } else if ($this->dati['ESPRESSIONE']['ITDTIC'] == "RadioButton") {
            Out::hide($this->nameForm . "_Template_field", '');
            Out::hide($this->nameForm . "_MaxLength_field", '');
            Out::hide($this->nameForm . "_Size_field", '');
            Out::show($this->nameForm . "_RadioGroupRef_field", '');
            Out::show($this->nameForm . "_valueRitorno_field", '');
            Out::hide($this->nameForm . "_divHtml", '');
            Out::show($this->nameForm . "_divValore", '');
            Out::hide($this->nameForm . "_rows_field", '');
            Out::hide($this->nameForm . "_cols_field", '');
            Out::valore($this->nameForm . '_valueRitorno', $this->dati['ESPRESSIONE']['ITDMETA']['ATTRIBUTICAMPO']['RETURNVALUE']);
        } else if ($this->dati['ESPRESSIONE']['ITDTIC'] == "Html") {
            Out::show($this->nameForm . "_divHtml", '');
            Out::hide($this->nameForm . "_divValore", '');
            Out::hide($this->nameForm . "_StyleCampo_field", '');
            Out::hide($this->nameForm . "_rows_field", '');
            Out::show($this->nameForm . "_Acapo_field", '');
            Out::hide($this->nameForm . "_Size_field", '');
            Out::hide($this->nameForm . "_cols_field", '');
        } else {
            Out::hide($this->nameForm . "_MaxLength_field", '');
            Out::hide($this->nameForm . "_Size_field", '');
            Out::hide($this->nameForm . "_divHtml", '');
            Out::show($this->nameForm . "_divValore", '');
            Out::hide($this->nameForm . "_rows_field", '');
            Out::hide($this->nameForm . "_cols_field", '');
        }
        if ($this->dati['ESPRESSIONE']['ITDVCA'] != "RegularExpression") {
            Out::hide($this->nameForm . "_RegExpr", '');
            Out::hide($this->nameForm . "_RegExpr_lbl", '');
        }
        Out::valore($this->nameForm . '_Etichetta', $this->dati['ESPRESSIONE']['ITDLAB']);
        Out::valore($this->nameForm . '_TipoInput', $this->dati['ESPRESSIONE']['ITDTIC']);
        Out::valore($this->nameForm . '_ReadOnly', $this->dati['ESPRESSIONE']['ITDROL']);
        Out::valore($this->nameForm . '_Valida', $this->dati['ESPRESSIONE']['ITDVCA']);
        Out::valore($this->nameForm . '_RegExpr', $this->dati['ESPRESSIONE']['ITDREV']);
        Out::valore($this->nameForm . '_MaxLength', $this->dati['ESPRESSIONE']['ITDLEN']);
        Out::valore($this->nameForm . '_Size', $this->dati['ESPRESSIONE']['ITDDIM']);
        Out::valore($this->nameForm . '_Acapo', $this->dati['ESPRESSIONE']['ITDACA']);
        Out::valore($this->nameForm . '_Posizione', $this->dati['ESPRESSIONE']['ITDPOS']);
        Out::valore($this->nameForm . '_StyleLabel', $this->dati['ESPRESSIONE']['ITDLABSTYLE']);
        Out::valore($this->nameForm . '_StyleCampo', $this->dati['ESPRESSIONE']['ITDFIELDSTYLE']);
        Out::valore($this->nameForm . '_ClassCampo', $this->dati['ESPRESSIONE']['ITDFIELDCLASS']);
        Out::valore($this->nameForm . '_cols', $this->dati['ESPRESSIONE']['ITDMETA']['ATTRIBUTICAMPO']['COLS']);
        Out::valore($this->nameForm . '_rows', $this->dati['ESPRESSIONE']['ITDMETA']['ATTRIBUTICAMPO']['ROWS']);
        Out::valore($this->nameForm . '_htmlPosition', $this->dati['ESPRESSIONE']['ITDMETA']['HTMLPOS']);



        if ($this->dati['ESPRESSIONE']['ITDDIZ'] == "D") {
            Out::attributo($this->nameForm . "_Dizionario", "checked", "0", "checked");
            Out::attributo($this->nameForm . '_Valore', 'readonly', '0');
            Out::show($this->nameForm . "_Valore_field", '');
            Out::hide($this->nameForm . "_ValoreTextArea_field", '');
            Out::show($this->nameForm . "_Valore_butt", '');
            Out::valore($this->nameForm . '_Valore', $this->dati['ESPRESSIONE']['ITDVAL']);
        } else if ($this->dati['ESPRESSIONE']['ITDDIZ'] == "C") {
            Out::attributo($this->nameForm . "_Costante", "checked", "0", "checked");
            Out::attributo($this->nameForm . '_Valore', 'readonly', '1');
            Out::show($this->nameForm . "_Valore_field", '');
            Out::hide($this->nameForm . "_ValoreTextArea_field", '');
            Out::hide($this->nameForm . "_Valore_butt", '');
            Out::valore($this->nameForm . '_Valore', $this->dati['ESPRESSIONE']['ITDVAL']);
        } else if ($this->dati['ESPRESSIONE']['ITDDIZ'] == "T") {
            Out::attributo($this->nameForm . "_Template", "checked", "0", "checked");
            Out::attributo($this->nameForm . '_Valore', 'readonly', '1');
            Out::hide($this->nameForm . "_Valore_field", '');
            Out::show($this->nameForm . "_ValoreTextArea_field", '');
            Out::valore($this->nameForm . '_ValoreTextArea', $this->dati['ESPRESSIONE']['ITDVAL']);
        } else if ($this->dati['ESPRESSIONE']['ITDDIZ'] == "H") {
            Out::valore($this->nameForm . '_Valore', $this->dati['ESPRESSIONE']['ITDVAL']);
        }
        Out::valore($this->nameForm . '_ReadOnly', $this->dati['ESPRESSIONE']['ITDROL']);
        Out::setFocus('', $this->nameForm . '_Campi');
    }

//    public function creaComboCampi($dataset) {
//        return;
//        switch ($dataset) {
//            case "ITEDAG":
//                $sql = "
//            SELECT
//                ITEDAG.ITEKEY AS ITEKEY,
//                ITEDAG.ITECOD AS ITECOD,
//                ITEDAG.ITDKEY AS ITDKEY,
//                ITEDAG.ITDTIP AS ITDTIP
//            FROM
//                ITEPAS ITEPAS
//            LEFT OUTER JOIN
//                ITEDAG ITEDAG
//            ON
//                ITEPAS.ITEKEY=ITEDAG.ITEKEY
//            WHERE ITEPAS.ITECOD='" . $this->dati['ITECOD'] . "' AND ITEPAS.ITEDAT = 1 ";
//                $Itedag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
//                if ($Itedag_tab) {
//                    foreach ($Itedag_tab as $key => $Itedag_rec) {
//                        Out::select($this->nameForm . '_Campi', 1, $Itedag_rec['ITDKEY'], "0", $Itedag_rec['ITDKEY']);
//                    }
//                }
//                break;
//            case "RICDAG":
//                $sql = "
//            SELECT
//                RICDAG.ITEKEY AS ITEKEY,
//                RICDAG.ITECOD AS ITECOD,
//                RICDAG.DAGKEY AS DAGKEY,
//                RICDAG.DAGTIP AS DAGTIP,
//                RICDAG.DAGNUM AS DAGNUM
//            FROM
//                RICITE RICITE
//            LEFT OUTER JOIN
//                RICDAG RICDAG
//            ON
//                RICITE.ITEKEY=RICDAG.ITEKEY AND RICITE.RICNUM = RICDAG.DAGNUM
//            WHERE RICITE.RICNUM = '" . $this->dati['RICNUM'] . "' AND RICITE.ITECOD='" . $this->dati['ITECOD'] . "' AND RICITE.ITEDAT = 1 ";
//                $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
//                if ($Ricdag_tab) {
//                    foreach ($Ricdag_tab as $key => $Ricdag_rec) {
//                        Out::select($this->nameForm . '_Campi', 1, $Ricdag_rec['DAGKEY'], "0", $Ricdag_rec['DAGKEY']);
//                    }
//                }
//                break;
//            default:
//                break;
//        }
//    }
//    public function creaComboCondizioni() {
//        Out::select($this->nameForm . '_Condizione', 1, "uguale", "1", "Uguale a");
//        Out::select($this->nameForm . '_Condizione', 1, "diverso", "0", "Diverso da");
//        Out::select($this->nameForm . '_Condizione', 1, "maggiore", "0", "Maggiore a");
//        Out::select($this->nameForm . '_Condizione', 1, "minore", "0", "Minore a");
//        Out::select($this->nameForm . '_Condizione', 1, "maggiore-uguale", "0", "Maggiore/Uguale a");
//        Out::select($this->nameForm . '_Condizione', 1, "minore-uguale", "0", "Minore/Uguale a");
//    }
}
