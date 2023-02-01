<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praDatoTipizzato.class.php';

function praPassoProcAgg() {
    $praPassoProcAgg = new praPassoProcAgg();
    $praPassoProcAgg->parseEvent();
    return;
}

class praPassoProcAgg extends itaModel {

    public $PRAM_DB;
    public $COMM_DB;
    public $praLib;
    public $utiEnte;
    public $ITALWEB_DB;
    public $nameForm = "praPassoProcAgg";
    public $divDettaglio = "praPassoProcAgg_divDettaglio";
    public $divDati = "praPassoProcAgg_divDati";
    public $divRadio = "praPassoProcAgg_divRadio";
    public $divControllo = "praPassoProcAgg_divControllo";
    public $gridExprOut = "praPassoProcAgg_gridEspressioniOut";
    public $gridParamsTipoDato = "praPassoProcAgg_gridParamsTipoDato";
    public $exprOut;
    public $datoAggiuntivo;
    public $returnModel;
    public $returnEvent;
    public $chiamante;
    public $filePdf;
    public $idxDati;
    public $daAnagrafica;
    private $paramsTipoDato;
    private $rowidCancellazione;
    /*
     * Nuova Modalità con getter e setter
     */
    private $datiAggiuntivi;

    function __construct() {
        parent::__construct();
// Apro il DB
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
            $this->datoAggiuntivo = App::$utente->getKey($this->nameForm . '_datoAggiuntivo');
            $this->idxDati = App::$utente->getKey($this->nameForm . '_idxDati');
            $this->datiAggiuntivi = App::$utente->getKey($this->nameForm . '_datiAggiuntivi');
            $this->chiamante = App::$utente->getKey($this->nameForm . '_chiamante');
            $this->filePdf = App::$utente->getKey($this->nameForm . '_filePdf');
            $this->exprOut = App::$utente->getKey($this->nameForm . '_exprOut');
            $this->daAnagrafica = App::$utente->getKey($this->nameForm . '_daAnagrafica');
            $this->paramsTipoDato = App::$utente->getKey($this->nameForm . '_paramsTipoDato');
            $this->rowidCancellazione = App::$utente->getKey($this->nameForm . '_rowidCancellazione');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnEvent', $this->returnEvent);
            App::$utente->setKey($this->nameForm . '_datoAggiuntivo', $this->datoAggiuntivo);
            App::$utente->setKey($this->nameForm . '_idxDati', $this->idxDati);
            App::$utente->setKey($this->nameForm . '_datiAggiuntivi', $this->datiAggiuntivi);
            App::$utente->setKey($this->nameForm . '_chiamante', $this->chiamante);
            App::$utente->setKey($this->nameForm . '_filePdf', $this->filePdf);
            App::$utente->setKey($this->nameForm . '_exprOut', $this->exprOut);
            App::$utente->setKey($this->nameForm . '_daAnagrafica', $this->daAnagrafica);
            App::$utente->setKey($this->nameForm . '_paramsTipoDato', $this->paramsTipoDato);
            App::$utente->setKey($this->nameForm . '_rowidCancellazione', $this->rowidCancellazione);
        }
    }

    public function getDatiAggiuntivi() {
        return $this->datiAggiuntivi;
    }

    public function setDatiAggiuntivi($datiAggiuntivi) {
        $this->datiAggiuntivi = $datiAggiuntivi;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->idxDati = "";
                $this->daAnagrafica = false;
                $this->returnModel = $_POST[$this->nameForm . '_returnModel'] ?: $this->returnModel;
                $this->returnEvent = $_POST[$this->nameForm . '_returnEvent'] ?: $this->returnEvent;
                $this->datoAggiuntivo = $_POST['datoAgg'];
                $this->chiamante = $_POST['chiamante'];
                $this->filePdf = $_POST['filePdf'];
                $this->exprOut = array();
                if ($this->datoAggiuntivo['ITDEXPROUT']) {
                    $this->exprOut = unserialize($this->datoAggiuntivo['ITDEXPROUT']);
                }
                $this->creaComboTipo();
                $this->creaComboRadioGroupRef();
                $this->praLib->creaComboCondizioni($this->nameForm . '_Condizione');

                Out::hide($this->nameForm . '_divParamsTipoDato');

                if ($_POST['idxDati'] != "") {
                    $this->idxDati = $_POST['idxDati'];
                    $this->Dettaglio();
                } else {
                    $this->OpenNuovo();
                }

                break;
            case 'dbClickRow':
            case 'editRow':
                switch ($_POST['id']) {
                    case $this->gridExprOut :
                        $rowid = $_POST['rowid'];
                        $Itepas_rec = $this->praLib->GetItepas($this->chiamante, 'rowid');
                        $model = 'praDagExprOut';
                        $_POST = array();
                        $_POST['dati'] = array(
                            'TITOLO' => "Espressione per controllare l'aspetto del campo:",
                            'TABELLA' => 'ITEDAG',
                            'ITECOD' => $Itepas_rec['ITECOD'],
                            'ITEKEY' => $Itepas_rec['ITEKEY'],
                            'IDESPRESSIONE' => $rowid,
                            'RADIOGROUPREF' => $this->getRadioGroupRef(),
                            'ESPRESSIONE' => $this->exprOut[$rowid]
                        );
                        $_POST['event'] = 'openform';
                        $_POST['perms'] = $this->perms;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnExprOut';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridExprOut :
                        $rowid = $_POST['rowid'];
                        unset($this->exprOut[$rowid]);
//
//Dopo la cancellazione riordino le chiavi dell'array partendo da 0
//altrimenti poi non corrispondono piu il rowid della tabella con la chiave dell'array
//
                        $this->exprOut = array_values($this->exprOut);
//
                        $this->CaricaGrigliaExprOut();
                        break;

                    case $this->gridParamsTipoDato:
                        $this->rowidCancellazione = $_POST['rowid'];
                        $campo = $this->paramsTipoDato[$this->rowidCancellazione]['CAMPO'];

                        Out::msgQuestion("Cancellazione", "Confermi la cancellazione del parametro '$campo'?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaParametro', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaParametro', 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                        );
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridExprOut :
                        $Itepas_rec = $this->praLib->GetItepas($this->chiamante, 'rowid');
                        $model = 'praDagExprOut';
                        $_POST = array();
                        $_POST['dati'] = array(
                            'TITOLO' => "Espressione per controllare l'aspetto del campo:",
                            'TABELLA' => 'ITEDAG',
                            'ITECOD' => $Itepas_rec['ITECOD'],
                            'ITEKEY' => $Itepas_rec['ITEKEY'],
                            'IDESPRESSIONE' => -1,
                            'RADIOGROUPREF' => $this->getRadioGroupRef(),
                            'ESPRESSIONE' => array()
                        );
                        $_POST['event'] = 'openform';
                        $_POST['perms'] = $this->perms;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnExprOut';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case $this->gridParamsTipoDato:
                        $campi[] = array(
                            'label' => array(
                                'value' => 'CAMPO',
                                'style' => 'width: 100px; display: block; float: left; padding: 0 5px 0 0; text-align: right;'
                            ),
                            'id' => $this->nameForm . '_campo',
                            'name' => $this->nameForm . '_campo',
                            'type' => 'text',
                            'size' => '40'
                        );

                        $campi[] = array(
                            'label' => array(
                                'value' => 'VALORE',
                                'style' => 'width: 100px; display: block; float: left; padding: 0 5px 0 0; text-align: right;'
                            ),
                            'id' => $this->nameForm . '_valore',
                            'name' => $this->nameForm . '_valore',
                            'type' => 'text',
                            'size' => '40'
                        );

                        Out::msgInput('Aggiungi Parametro', $campi, array(
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaAggiungiParametro', 'model' => $this->nameForm)
                            ), $this->nameForm . '_workSpace');
                        break;
                }
                break;

            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->gridParamsTipoDato:
                        $codice = $_POST['rowid'];
                        foreach ($this->paramsTipoDato as $key => $param) {
                            if ($key == $codice) {
                                $this->paramsTipoDato[$key][$_POST['cellname']] = $_POST['value'];
                            }
                        }

                        $this->caricaGrigliaParamsTipoDato();
                        break;
                }
                break;

            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiorna':
                        $msg = $this->controlli();
                        if (!$msg) {
                            $this->datoAggiuntivo['ITDKEY'] = $_POST[$this->nameForm . '_Codice'];
                            $this->datoAggiuntivo['ITDALIAS'] = $_POST[$this->nameForm . '_Alias'];
                            $this->datoAggiuntivo['ITDDES'] = $_POST[$this->nameForm . '_Descrizione'];
                            $this->datoAggiuntivo['ITDTIP'] = $_POST[$this->nameForm . '_Tipo'];
                            if ($_POST[$this->nameForm . '_ValoreCampo'] == 'T') {
                                $this->datoAggiuntivo['ITDVAL'] = $_POST[$this->nameForm . '_ValoreTextArea'];
                            } else {
                                $this->datoAggiuntivo['ITDVAL'] = $_POST[$this->nameForm . '_Valore'];
                            }
                            $this->datoAggiuntivo['ITDCTR'] = $_POST[$this->nameForm . '_ctrSerializzato'];
                            $this->datoAggiuntivo['ITDEXPROUT'] = serialize($this->exprOut);
                            $this->datoAggiuntivo['ITDNOT'] = $_POST[$this->nameForm . '_Note'];
                            $this->datoAggiuntivo['ITDSEQ'] = $_POST[$this->nameForm . '_Sequenza'];
                            if ($this->datoAggiuntivo['ITDSEQ'] == 0 || $this->datoAggiuntivo['ITDSEQ'] == '') {
                                $this->datoAggiuntivo['ITDSEQ'] = 99999;
                            }
                            $this->datoAggiuntivo['ITDLAB'] = $_POST[$this->nameForm . '_Etichetta'];
                            $this->datoAggiuntivo['ITDTIC'] = $_POST[$this->nameForm . '_TipoInput'];
                            $this->datoAggiuntivo['ITDROL'] = $_POST[$this->nameForm . '_ReadOnly'];
                            $this->datoAggiuntivo['ITDVCA'] = $_POST[$this->nameForm . '_Valida'];
                            $this->datoAggiuntivo['ITDREV'] = $_POST[$this->nameForm . '_RegExpr'];
                            $this->datoAggiuntivo['ITDLEN'] = $_POST[$this->nameForm . '_MaxLength'];
                            $this->datoAggiuntivo['ITDDIM'] = $_POST[$this->nameForm . '_Size'];
                            $this->datoAggiuntivo['ITDDIZ'] = $_POST[$this->nameForm . '_ValoreCampo'];
                            $this->datoAggiuntivo['ITDACA'] = $_POST[$this->nameForm . '_Acapo'];
                            $this->datoAggiuntivo['ITDPOS'] = $_POST[$this->nameForm . '_Posizione'];
                            $this->datoAggiuntivo['ITDLABSTYLE'] = $_POST[$this->nameForm . '_StyleLabel'];
                            $this->datoAggiuntivo['ITDFIELDSTYLE'] = $_POST[$this->nameForm . '_StyleCampo'];
                            $this->datoAggiuntivo['ITDFIELDCLASS'] = $_POST[$this->nameForm . '_ClassCampo'];
                            $this->datoAggiuntivo['ROWID'] = $_POST[$this->nameForm . '_ITEDAG']['ROWID'];
                            $this->datoAggiuntivo['ITDCLASSE'] = $_POST[$this->nameForm . '_ITEDAG']['ITDCLASSE'];
                            $this->datoAggiuntivo['ITDMETODO'] = $_POST[$this->nameForm . '_ITEDAG']['ITDMETODO'];
                            $this->datoAggiuntivo['ITDMETA'] = "";
                            if ($_POST[$this->nameForm . '_TipoInput'] == "Html") {
                                $this->datoAggiuntivo['ITDDIZ'] = "H";
                                $this->datoAggiuntivo['ITDROL'] = 1;
                                if ($_POST[$this->nameForm . '_htmlPosition']) {
                                    $this->datoAggiuntivo['ITDMETA']['HTMLPOS'] = $_POST[$this->nameForm . '_htmlPosition'];
                                }
                            } elseif ($_POST[$this->nameForm . '_TipoInput'] == "CheckBox") {
                                $this->datoAggiuntivo['ITDMETA']['ATTRIBUTICAMPO']['RETURNVALUES'] = $_POST[$this->nameForm . '_valueCheck'];
                            } elseif ($_POST[$this->nameForm . '_TipoInput'] == "TextArea") {
                                $this->datoAggiuntivo['ITDMETA']['ATTRIBUTICAMPO']['COLS'] = $_POST[$this->nameForm . '_cols'];
                                $this->datoAggiuntivo['ITDMETA']['ATTRIBUTICAMPO']['ROWS'] = $_POST[$this->nameForm . '_rows'];
                            } elseif ($_POST[$this->nameForm . '_TipoInput'] == "RadioButton") {
                                $this->datoAggiuntivo['ITDMETA']['ATTRIBUTICAMPO']['NAME'] = $_POST[$this->nameForm . '_RadioGroupRef'];
                                $this->datoAggiuntivo['ITDMETA']['ATTRIBUTICAMPO']['RETURNVALUE'] = $_POST[$this->nameForm . '_valueRitorno'];
                            } elseif ($_POST[$this->nameForm . '_TipoInput'] == 'Button') {
                                $this->datoAggiuntivo['ITDMETA']['ATTRIBUTICAMPO']['RETURNVALUE'] = $_POST[$this->nameForm . '_valueRitorno'];
                            }

                            if ($_POST[$this->nameForm . '_ONCHANGE_CLASS']) {
                                $this->datoAggiuntivo['ITDMETA']['CUSTOMEVENT']['ONCHANGE']['CLASS'] = $_POST[$this->nameForm . '_ONCHANGE_CLASS'];
                            }

                            if ($_POST[$this->nameForm . '_ONCHANGE_METHOD']) {
                                $this->datoAggiuntivo['ITDMETA']['CUSTOMEVENT']['ONCHANGE']['METHOD'] = $_POST[$this->nameForm . '_ONCHANGE_METHOD'];
                            }

                            foreach ($this->paramsTipoDato as $param) {
                                if (!isset($this->datoAggiuntivo['ITDMETA']['PARAMSTIPODATO'])) {
                                    $this->datoAggiuntivo['ITDMETA']['PARAMSTIPODATO'] = array();
                                }

                                $this->datoAggiuntivo['ITDMETA']['PARAMSTIPODATO'][$param['CAMPO']] = $param['VALORE'];
                            }

                            $this->returnToParent();
                        } else {
                            Out::msgStop("ATTENZIONE", $msg);
                        }
                        break;
                    case $this->nameForm . '_CancellaControllo':
                        Out::hide($this->divControllo, '');
                        Out::unBlock($this->nameForm . '_divDati');
                        Out::valore($this->nameForm . '_Controllo', '');
                        Out::valore($this->nameForm . '_ctrSerializzato', '');
                        break;
                    case $this->nameForm . '_ApriControllo':
                        $model = 'praCondizioni';
                        itaLib::openForm($model);
                        $praCondizioni = itaModel::getInstance($model);
                        $praCondizioni->setEvent('openform');
                        $praCondizioni->setReturnModel($this->nameForm);

                        if ($_POST[$this->nameForm . '_ctrSerializzato'] && unserialize($_POST[$this->nameForm . '_ctrSerializzato'])) {
                            $praCondizioni->setArrayEspressioni(unserialize($_POST[$this->nameForm . '_ctrSerializzato']));
                        }

                        $Itepas_rec = $this->praLib->GetItepas($this->chiamante, 'rowid');

                        $praCondizioni->setCampoCondizione('ITDCTR');
                        $praCondizioni->setCodiceProcedimento($Itepas_rec['ITECOD']);
                        $praCondizioni->setCodicePasso($Itepas_rec['ITEKEY']);

                        $praCondizioni->parseEvent();
                        break;
                    case $this->nameForm . '_Codice_butt':
                        praRic::praRicPraidc($this->nameForm, "returnPraidc", "", "", true, array('openMode' => 'newFromPasso', 'datiUtente' => array()));
                        break;
                    case $this->nameForm . '_Alias_butt':
                        praRic::praCampiPdf($this->filePdf, $this->nameForm, 'returnCampiPdf');
                        break;

                    case $this->nameForm . '_Valore_butt':
                        if ($this->daAnagrafica == false) {
                            $praLibVar = new praLibVariabili();
                            if ($this->datoAggiuntivo['ITEPUB'] == 1) {
                                $praLibVar->setFrontOfficeFlag(true);
                            }
                            $praLibVar->setCodiceProcedimento($this->datoAggiuntivo['ITECOD']);
                            $praLibVar->setChiavePasso($this->datoAggiuntivo['ITEKEY']);
                            $arrayLegenda = $praLibVar->getLegendaProcedimento('adjacency', 'none');
                            praRic::ricVariabili($arrayLegenda, $this->nameForm, 'returnVariabili', true);
                        } else {
                            praRic::praRicPraidc($this->nameForm, "returnPraidcAnag");
                        }
                        break;
                    case $this->nameForm . '_ValoreTextArea_butt':
                        $praLibVar = new praLibVariabili();
                        if ($this->datoAggiuntivo['ITEPUB'] == 1) {
                            $praLibVar->setFrontOfficeFlag(true);
                        }
                        $praLibVar->setCodiceProcedimento($this->datoAggiuntivo['ITECOD']);
                        $praLibVar->setChiavePasso($this->datoAggiuntivo['ITEKEY']);
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

                    case $this->nameForm . '_ConfermaAggiungiParametro':
                        $campo = $this->formData[$this->nameForm . '_campo'];
                        $valore = $this->formData[$this->nameForm . '_valore'];
                        foreach ($this->paramsTipoDato as $param) {
                            if ($param['CAMPO'] == $campo) {
                                Out::msgStop("Attenzione", "Il campo $campo è già presente");
                                break 2;
                            }
                        }

                        $this->paramsTipoDato[] = array("CAMPO" => $campo, "VALORE" => $valore);
                        $this->caricaGrigliaParamsTipoDato();
                        break;

                    case $this->nameForm . '_ConfermaCancellaParametro':
                        foreach ($this->paramsTipoDato as $key => $param) {
                            if ($key == $this->rowidCancellazione) {
                                unset($this->paramsTipoDato[$key]);
                                break;
                            }
                        }

                        $this->rowidCancellazione = null;

                        $this->caricaGrigliaParamsTipoDato();
                        break;

                    case 'close-portlet':
                        $this->close();
                        break;
                }
                break;
            case 'returnVariabili':
                Out::valore($this->nameForm . '_Valore', $_POST['rowData']['markupkey']);
                break;
            case 'returnVariabiliTextArea':
                Out::codice("$('#" . $this->nameForm . '_ValoreTextArea' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case "returnEditDiag":
                $this->datoAggiuntivo['ITDVAL'] = $_POST['returnText'];
                Out::valore($this->nameForm . '_Valore', $this->datoAggiuntivo['ITDVAL']);
                break;
            case "returnExprOut":
                if ($_POST['dati']['IDESPRESSIONE'] == -1) {
                    $this->exprOut[] = $_POST['dati']['ESPRESSIONE'];
                } else {
                    $this->exprOut[$_POST['dati']['IDESPRESSIONE']] = $_POST['dati']['ESPRESSIONE'];
                }
                $this->CaricaGrigliaExprOut();
                break;
            case 'returnPraCondizioni':
                $strExpr = $_POST['returnCondizione'];
                Out::valore($this->nameForm . '_ctrSerializzato', $strExpr);
                Out::valore($this->nameForm . '_Controllo', $this->praLib->DecodificaControllo($strExpr));
                Out::clearFields($this->nameForm, $this->divControllo);
                break;
            case 'onBlur': // Evento OnBlur
                switch ($_POST['id']) {
                    case $this->nameForm . '_Codice':
                        $Praidc_rec = $this->praLib->GetPraidc($_POST[$this->nameForm . '_Codice']);
                        if ($Praidc_rec) {
                            Out::valore($this->nameForm . "_Codice", $Praidc_rec['IDCKEY']);
                            Out::valore($this->nameForm . "_Descrizione", $Praidc_rec['IDCDES']);
                        }
                        break;
                }
                break;
            case 'onChange': // Evento OnChange
                switch ($_POST['id']) {
                    case $this->nameForm . '_Obbligatorio':
                        if ($_POST[$this->nameForm . '_Obbligatorio'] == 1) {
                            $arrExpr[] = array(
                                "CAMPO" => $_POST[$this->nameForm . '_Codice'],
                                "CONDIZIONE" => '==',
                                "VALORE" => '',
                                "OPERATORE" => ''
                            );
                            $strExpr = serialize($arrExpr);
                            $arrExpr = unserialize($_POST[$this->nameForm . '_ctrSerializzato']);
                            Out::valore($this->nameForm . '_ctrSerializzato', $strExpr);
                            Out::valore($this->nameForm . '_Controllo', '');
                            Out::hide($this->nameForm . "_ApriControllo");
                            Out::hide($this->nameForm . "_CancellaControllo");
                        } else {
                            Out::valore($this->nameForm . '_ctrSerializzato', '');
                            Out::show($this->nameForm . "_ApriControllo");
                            Out::show($this->nameForm . "_CancellaControllo");
                        }
                        break;
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
                        Out::setInputTooltip($this->nameForm . '_valueRitorno', '');
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
                        } else if ($_POST[$this->nameForm . '_TipoInput'] == 'Button') {
                            Out::hide($this->nameForm . "_Size_field", '');
                            Out::hide($this->nameForm . "_divHtml", '');
                            Out::show($this->nameForm . "_divValore", '');
                            Out::hide($this->nameForm . "_rows_field", '');
                            Out::hide($this->nameForm . "_cols_field", '');
                            Out::show($this->nameForm . "_valueRitorno_field", '');
                            Out::setInputTooltip($this->nameForm . '_valueRitorno', 'Imposta il nome della chiave di ritorno.');
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
                        $this->daAnagrafica = false;
                        Out::show($this->nameForm . "_Valore_field", '');
                        Out::hide($this->nameForm . "_ValoreTextArea_field", '');
                        Out::show($this->nameForm . "_Valore_butt", '');
                        Out::attributo($this->nameForm . '_Valore', 'readonly', '0');
                        break;
                    case $this->nameForm . '_Template':
                        $this->daAnagrafica = false;
                        Out::hide($this->nameForm . "_Valore_field", '');
                        Out::show($this->nameForm . "_ValoreTextArea_field", '');
                        Out::attributo($this->nameForm . '_Valore', 'readonly', '1');
                        break;
                    case $this->nameForm . '_Anagrafica':
                        $this->daAnagrafica = true;
                        Out::show($this->nameForm . "_Valore_field", '');
                        Out::hide($this->nameForm . "_ValoreTextArea_field", '');
                        Out::show($this->nameForm . "_Valore_butt", '');
                        Out::attributo($this->nameForm . '_Valore', 'readonly', '0');
                        break;
                    case $this->nameForm . '_Tipo':
                        $this->decodeVisualizzazioneTipizzato($_POST[$this->nameForm . '_Tipo']);
                        break;
                    case $this->nameForm . '_Valida':
                        if ($_POST[$this->nameForm . '_Valida'] != "RegularExpression") {
                            Out::hide($this->nameForm . "_RegExpr", '');
                            Out::hide($this->nameForm . "_RegExpr_lbl", '');
                        } else {
                            Out::show($this->nameForm . "_RegExpr", '');
                            Out::show($this->nameForm . "_RegExpr_lbl", '');
                        }
                        break;
                }

                break;
            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Codice':
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $Praidc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRAIDC WHERE " . $this->PRAM_DB->strLower('IDCKEY') . " LIKE '%"
                                . addslashes(strtolower(itaSuggest::getQuery())) . "%'", true);
                        foreach ($Praidc_tab as $Praidc_rec) {
                            itaSuggest::addSuggest($Praidc_rec['IDCKEY']);
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;
            case 'returnPraidc':
                $Praidc_rec = $this->praLib->GetPraidc($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . "_Codice", $Praidc_rec['IDCKEY']);
                Out::valore($this->nameForm . "_Descrizione", $Praidc_rec['IDCDES']);
                break;
            case 'returnPraidcAnag':
                $Praidc_rec = $this->praLib->GetPraidc($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . "_Valore", $Praidc_rec['IDCKEY']);
                break;
            case 'returnCampiPdf':
                Out::valore($this->nameForm . "_Alias", $_POST['rowData']['Campo']);
//Out::valore($this->nameForm . "_Valore", $_POST['rowData']['Valore']);
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_datoAggiuntivo');
        App::$utente->removeKey($this->nameForm . '_idxDati');
        App::$utente->removeKey($this->nameForm . '_datiAggiuntivi');
        App::$utente->removeKey($this->nameForm . '_chiamante');
        App::$utente->removeKey($this->nameForm . '_filePdf');
        App::$utente->removeKey($this->nameForm . '_exprOut');
        App::$utente->removeKey($this->nameForm . '_daAnagrafica');
        App::$utente->removeKey($this->nameForm . '_paramsTipoDato');
        App::$utente->removeKey($this->nameForm . '_rowidCancellazione');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();

        if ($this->returnModel != '') {
            $model = $nameform = $this->returnModel;

            if ($this->returnModelOrig) {
                $model = $this->returnModelOrig;
            }

            $returnModel = itaModel::getInstance($model, $nameform);

            $returnModel->setEvent($this->returnEvent);

            $_POST = array();
            $_POST['event'] = $this->returnEvent;
            $_POST['datoAggiuntivo'] = $this->datoAggiuntivo;
            $_POST['idxDati'] = $this->idxDati;
//$_POST['rowidChiamante'] = $this->chiamante;

            $returnModel->parseEvent();

            Out::closeDialog($this->nameForm);
        } else {
            Out::show($this->return);
        }
    }

    function controlli() {
        $msgBase = "Si sono verificati i seguenti errori:<br>";
        $msg = "";
        if ($_POST[$this->nameForm . '_Tipo'] != "") {
            $Itepas_rec = $this->praLib->GetItepas($this->chiamante, 'rowid');
            $where = "AND ITDTIP<>''";
            $Itedag_tab = $this->praLib->GetItedag($Itepas_rec['ITECOD'], 'codice', true, $where);
            $trovato = false;
            foreach ($Itedag_tab as $Itedag_rec) {
                if ($Itedag_rec['ROWID'] == $this->datoAggiuntivo['ROWID'])
                    continue;
//                if ($Itedag_rec['ITDTIP'] == $_POST[$this->nameForm . '_Tipo']) {
//                    $trovato = true;
//                    break;
//                }
            }

            if ($trovato == true) {
                $msg = "- Il tipo selezionato è gia usato in un altro passo.<br>Si prega di cambiare tipo o cambiarlo nell'altro passo<br>";
            }
        }
//        if ($_POST[$this->nameForm . '_Valida'] != "" && $_POST[$this->nameForm . '_Valore'] != "") {
//            switch ($_POST[$this->nameForm . '_Valida']) {
//                case "CodiceFiscale":
//                    $regExp = "/^[a-zA-Z]{6}[0-9]{2}[a-zA-Z][0-9]{2}[a-zA-Z][0-9]{3}[a-zA-Z]$/";
//                    break;
//                case "PartitaIva":
//                    $regExp = "/^[0-9]{11}$/";
//                    break;
//                case "email":
//                    $regExp = "/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i";
//                    break;
//                case "RegularExpression":
//                    $regExp = $_POST[$this->nameForm . '_RegExpr'];
//                    break;
//                case "Numeri":
//                    $regExp = "/^[0-9]+$/";
//                    break;
//                case "Lettere":
//                    $regExp = "/^[A-Za-z\à\è\ì\ò\ù]+$/";
//                    break;
//                case "Data":
//                    $regExp = "/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}/";
//                    break;
//                case "Importo":
//                    $regExp = "/^(0|[1-9]\d*)([\.|,]\d{1,2})?$/";
//                    break;
//                case "Iban":
//                    $regExp = "^IT\d{2}[ ][a-zA-Z]\d{3}[ ]\d{4}[ ]\d{4}[ ]\d{4}[ ]\d{4}[ ]\d{3}$|^IT\d{2}[a-zA-Z]\d{22}$";
//                    break;
//                default:
//                    break;
//            }
//            if (!preg_match($regExp, $_POST[$this->nameForm . '_Valore'])) {
//                $msg .= "- Il valore immesso non è conforme con il controllo selezionato";
//            }
//        }
        if ($msg) {
            $msg = $msgBase . $msg;
        }
        return $msg;
    }

    function OpenNuovo() {
        Out::show($this->divDettaglio, '');
        Out::show($this->divDati, '');
        Out::hide($this->divControllo, '');
        Out::clearFields($this->nameForm, $this->divDettaglio);
        Out::clearFields($this->nameForm, $this->divDati);
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm);


        Out::show($this->nameForm . "_MaxLength_field", '');
        Out::show($this->nameForm . "_Size_field", '');
        Out::hide($this->nameForm . "_divHtml", '');
        Out::show($this->nameForm . "_divValore", '');
        Out::hide($this->nameForm . "_valueCheck_field", '');
        Out::hide($this->nameForm . "_valueRitorno_field", '');
        Out::hide($this->nameForm . "_rows_field", '');
        Out::hide($this->nameForm . "_cols_field", '');
        Out::attributo($this->nameForm . "_Costante", "checked", "0", "checked");
        Out::attributo($this->nameForm . '_Valore', 'readonly', '1');
        Out::show($this->nameForm . "_Valore_field", '');
        Out::hide($this->nameForm . "_ValoreTextArea_field", '');
        Out::hide($this->nameForm . "_RadioGroupRef_field", '');
        Out::hide($this->nameForm . "_Valore_butt", '');
        $this->decodeVisualizzazioneTipizzato('');

        Out::setFocus('', $this->nameForm . '_Codice');
    }

    public function Dettaglio() {
        if ($this->datoAggiuntivo['ITDDIZ'] == "")
            $this->datoAggiuntivo['ITDDIZ'] = "C";
        $Itepas_rec = $this->praLib->GetItepas($this->chiamante, 'rowid');
        Out::valore($this->nameForm . '_Codice', $this->datoAggiuntivo['ITDKEY']);
        Out::valore($this->nameForm . '_Alias', $this->datoAggiuntivo['ITDALIAS']);
        Out::valore($this->nameForm . '_Descrizione', $this->datoAggiuntivo['ITDDES']);
        Out::valore($this->nameForm . '_Tipo', $this->datoAggiuntivo['ITDTIP']);
        Out::valore($this->nameForm . '_Controllo', $this->praLib->DecodificaControllo($this->datoAggiuntivo['ITDCTR']));
        Out::valore($this->nameForm . '_Note', $this->datoAggiuntivo['ITDNOT']);
        Out::valore($this->nameForm . '_ctrSerializzato', $this->datoAggiuntivo['ITDCTR']);
        Out::valore($this->nameForm . '_exprOutSerializzato', $this->datoAggiuntivo['ITDEXPROUT']);
        Out::valore($this->nameForm . '_Sequenza', $this->datoAggiuntivo['ITDSEQ']);

        Out::valore($this->nameForm . '_Etichetta', $this->datoAggiuntivo['ITDLAB']);
        Out::valore($this->nameForm . '_TipoInput', $this->datoAggiuntivo['ITDTIC']);
        Out::valore($this->nameForm . '_ReadOnly', $this->datoAggiuntivo['ITDROL']);
        Out::valore($this->nameForm . '_Valida', $this->datoAggiuntivo['ITDVCA']);
        Out::valore($this->nameForm . '_RegExpr', $this->datoAggiuntivo['ITDREV']);
        Out::valore($this->nameForm . '_MaxLength', $this->datoAggiuntivo['ITDLEN']);
        Out::valore($this->nameForm . '_Size', $this->datoAggiuntivo['ITDDIM']);
        Out::valore($this->nameForm . '_Acapo', $this->datoAggiuntivo['ITDACA']);
        Out::valore($this->nameForm . '_Posizione', $this->datoAggiuntivo['ITDPOS']);
        Out::valore($this->nameForm . '_StyleLabel', $this->datoAggiuntivo['ITDLABSTYLE']);
        Out::valore($this->nameForm . '_StyleCampo', $this->datoAggiuntivo['ITDFIELDSTYLE']);
        Out::valore($this->nameForm . '_ClassCampo', $this->datoAggiuntivo['ITDFIELDCLASS']);
        Out::valore($this->nameForm . '_ITEDAG[ROWID]', $this->datoAggiuntivo['ROWID']);
        Out::valore($this->nameForm . '_ITEDAG[ITDCLASSE]', $this->datoAggiuntivo['ITDCLASSE']);
        Out::valore($this->nameForm . '_ITEDAG[ITDMETODO]', $this->datoAggiuntivo['ITDMETODO']);

        if (is_array($this->datoAggiuntivo['ITDMETA'])) {
//HTML
            Out::valore($this->nameForm . '_htmlPosition', $this->datoAggiuntivo['ITDMETA']['HTMLPOS']);
//CHECKBOX
            Out::valore($this->nameForm . '_valueCheck', $this->datoAggiuntivo['ITDMETA']['ATTRIBUTICAMPO']['RETURNVALUES']);
//TEXTAREA
            Out::valore($this->nameForm . '_cols', $this->datoAggiuntivo['ITDMETA']['ATTRIBUTICAMPO']['COLS']);
            Out::valore($this->nameForm . '_rows', $this->datoAggiuntivo['ITDMETA']['ATTRIBUTICAMPO']['ROWS']);

            Out::valore($this->nameForm . '_ONCHANGE_CLASS', $this->datoAggiuntivo['ITDMETA']['CUSTOMEVENT']['ONCHANGE']['CLASS']);
            Out::valore($this->nameForm . '_ONCHANGE_METHOD', $this->datoAggiuntivo['ITDMETA']['CUSTOMEVENT']['ONCHANGE']['METHOD']);
//RADIO BUTTON
            if ($this->datoAggiuntivo['ITDTIC'] == 'RadioButton') {
                Out::valore($this->nameForm . '_RadioGroupRef', $this->datoAggiuntivo['ITDMETA']['ATTRIBUTICAMPO']['NAME']);
                Out::valore($this->nameForm . '_valueRitorno', $this->datoAggiuntivo['ITDMETA']['ATTRIBUTICAMPO']['RETURNVALUE']);
            }
            if ($this->datoAggiuntivo['ITDTIC'] == 'CheckBox') {
                Out::valore($this->nameForm . '_valueCheck', $this->datoAggiuntivo['ITDMETA']['ATTRIBUTICAMPO']['RETURNVALUES']);
            }
            if ($this->datoAggiuntivo['ITDTIC'] == 'Button') {
                Out::valore($this->nameForm . '_valueRitorno', $this->datoAggiuntivo['ITDMETA']['ATTRIBUTICAMPO']['RETURNVALUE']);
            }
        } else {
            $meta = unserialize($this->datoAggiuntivo['ITDMETA']);
//HTML
            if ($meta) {
                Out::valore($this->nameForm . '_htmlPosition', $meta['HTMLPOS']);
//CHECKBOX
                Out::valore($this->nameForm . '_valueCheck', $meta['ATTRIBUTICAMPO']['RETURNVALUES']);
//TEXTAREA
                Out::valore($this->nameForm . '_cols', $meta['ATTRIBUTICAMPO']['COLS']);
                Out::valore($this->nameForm . '_rows', $meta['ATTRIBUTICAMPO']['ROWS']);

                Out::valore($this->nameForm . '_ONCHANGE_CLASS', $meta['CUSTOMEVENT']['ONCHANGE']['CLASS']);
                Out::valore($this->nameForm . '_ONCHANGE_METHOD', $meta['CUSTOMEVENT']['ONCHANGE']['METHOD']);
//RADIO BUTTON
                if ($this->datoAggiuntivo['ITDTIC'] == 'RadioButton') {
                    Out::valore($this->nameForm . '_RadioGroupRef', $meta['ATTRIBUTICAMPO']['NAME']);
                    Out::valore($this->nameForm . '_valueRitorno', $meta['ATTRIBUTICAMPO']['RETURNVALUE']);
                }
                if ($this->datoAggiuntivo['ITDTIC'] == 'Button') {
                    Out::valore($this->nameForm . '_valueRitorno', $meta['ATTRIBUTICAMPO']['RETURNVALUE']);
                }
            }
        }

        if ($this->datoAggiuntivo['ITDDIZ'] == "D") {
            $this->daAnagrafica = false;
            Out::attributo($this->nameForm . "_Dizionario", "checked", "0", "checked");
            Out::attributo($this->nameForm . '_Valore', 'readonly', '0');
            Out::show($this->nameForm . "_Valore_field", '');
            Out::hide($this->nameForm . "_ValoreTextArea_field", '');
            Out::show($this->nameForm . "_Valore_butt", '');
            Out::valore($this->nameForm . '_Valore', $this->datoAggiuntivo['ITDVAL']);
        } else if ($this->datoAggiuntivo['ITDDIZ'] == "C") {
            Out::attributo($this->nameForm . "_Costante", "checked", "0", "checked");
            Out::attributo($this->nameForm . '_Valore', 'readonly', '1');
            Out::show($this->nameForm . "_Valore_field", '');
            Out::hide($this->nameForm . "_ValoreTextArea_field", '');
            Out::hide($this->nameForm . "_Valore_butt", '');
            Out::valore($this->nameForm . '_Valore', $this->datoAggiuntivo['ITDVAL']);
        } else if ($this->datoAggiuntivo['ITDDIZ'] == "T") {
            $this->daAnagrafica = false;
            Out::attributo($this->nameForm . "_Template", "checked", "0", "checked");
            Out::attributo($this->nameForm . '_Valore', 'readonly', '1');
            Out::hide($this->nameForm . "_Valore_field", '');
            Out::show($this->nameForm . "_ValoreTextArea_field", '');
            Out::valore($this->nameForm . '_ValoreTextArea', $this->datoAggiuntivo['ITDVAL']);
        } elseif ($this->datoAggiuntivo['ITDDIZ'] == "A") {
            $this->daAnagrafica = true;
            Out::attributo($this->nameForm . "_Anagrafica", "checked", "0", "checked");
            Out::attributo($this->nameForm . '_Valore', 'readonly', '0');
            Out::show($this->nameForm . "_Valore_field", '');
            Out::hide($this->nameForm . "_ValoreTextArea_field", '');
            Out::show($this->nameForm . "_Valore_butt", '');
            Out::valore($this->nameForm . '_Valore', $this->datoAggiuntivo['ITDVAL']);
        } else if ($this->datoAggiuntivo['ITDDIZ'] == "H") {
            $this->daAnagrafica = false;
            Out::valore($this->nameForm . '_Valore', $this->datoAggiuntivo['ITDVAL']);
        } else if ($this->datoAggiuntivo['ITDDIZ'] == "F") {
            Out::attributo($this->nameForm . "_FoglioCalcolo", "checked", "0", "checked");
            Out::attributo($this->nameForm . '_Valore', 'readonly', '1');
            Out::show($this->nameForm . "_Valore_field");
            Out::hide($this->nameForm . "_ValoreTextArea_field");
            Out::hide($this->nameForm . "_Valore_butt");
            Out::valore($this->nameForm . '_Valore', $this->datoAggiuntivo['ITDVAL']);
        }

        $posObbligatorio = strpos($this->datoAggiuntivo['ITDCTR'], $this->datoAggiuntivo['ITDKEY']);
        if ($posObbligatorio) {
            Out::hide($this->nameForm . "_ApriControllo");
            Out::valore($this->nameForm . '_Obbligatorio', '1');
            Out::valore($this->nameForm . '_Controllo', '');
        }
        Out::hide($this->nameForm . "_divControllo");
        Out::hide($this->nameForm . "_RadioGroupRef_field", '');
        Out::hide($this->nameForm . "_valueCheck_field", '');
        Out::hide($this->nameForm . "_valueRitorno_field", '');
        Out::setInputTooltip($this->nameForm . '_valueRitorno', '');
        if ($this->datoAggiuntivo['ITDTIC'] == "Text" || $this->datoAggiuntivo['ITDTIC'] == "Password" || $this->datoAggiuntivo['ITDTIC'] == "") {
            Out::show($this->nameForm . "_MaxLength_field", '');
            Out::show($this->nameForm . "_Size_field", '');
            Out::hide($this->nameForm . "_divHtml", '');
            Out::show($this->nameForm . "_divValore", '');
            Out::hide($this->nameForm . "_rows_field", '');
            Out::hide($this->nameForm . "_cols_field", '');
        } else if ($this->datoAggiuntivo['ITDTIC'] == "TextArea") {
            Out::show($this->nameForm . "_MaxLength_field", '');
            Out::hide($this->nameForm . "_Size_field", '');
            Out::hide($this->nameForm . "_divHtml", '');
            Out::show($this->nameForm . "_divValore", '');
            Out::show($this->nameForm . "_rows_field", '');
            Out::show($this->nameForm . "_cols_field", '');
        } else if ($this->datoAggiuntivo['ITDTIC'] == "CheckBox") {
            Out::hide($this->nameForm . "_MaxLength_field", '');
            Out::hide($this->nameForm . "_Size_field", '');
            Out::hide($this->nameForm . "_divHtml", '');
            Out::show($this->nameForm . "_divValore", '');
            Out::hide($this->nameForm . "_rows_field", '');
            Out::hide($this->nameForm . "_cols_field", '');
            Out::show($this->nameForm . "_valueCheck_field", '');
        } else if ($this->datoAggiuntivo['ITDTIC'] == "RadioGroup") {
            Out::hide($this->nameForm . "_divHtml", '');
            Out::hide($this->nameForm . "_Template_field", '');
            Out::show($this->nameForm . "_Acapo_field", '');
            Out::hide($this->nameForm . "_Size_field", '');
            Out::hide($this->nameForm . "_cols_field", '');
            Out::hide($this->nameForm . "_rows_field", '');
            Out::hide($this->nameForm . "_StyleCampo_field", '');
            Out::hide($this->nameForm . "_rows_field", '');
            Out::hide($this->nameForm . "_cols_field", '');
        } else if ($this->datoAggiuntivo['ITDTIC'] == "RadioButton") {
            Out::hide($this->nameForm . "_Template_field", '');
            Out::hide($this->nameForm . "_MaxLength_field", '');
            Out::hide($this->nameForm . "_Size_field", '');
            Out::show($this->nameForm . "_RadioGroupRef_field", '');
            Out::show($this->nameForm . "_valueRitorno_field", '');
            Out::hide($this->nameForm . "_divHtml", '');
            Out::show($this->nameForm . "_divValore", '');
            Out::hide($this->nameForm . "_rows_field", '');
            Out::hide($this->nameForm . "_cols_field", '');
        } else if ($this->datoAggiuntivo['ITDTIC'] == "Html") {
            Out::show($this->nameForm . "_divHtml", '');
            Out::hide($this->nameForm . "_divValore", '');
            Out::hide($this->nameForm . "_StyleCampo_field", '');
            Out::hide($this->nameForm . "_rows_field", '');
            Out::show($this->nameForm . "_Acapo_field", '');
            Out::hide($this->nameForm . "_Size_field", '');
            Out::hide($this->nameForm . "_cols_field", '');
        } else if ($this->datoAggiuntivo['ITDTIC'] == 'Button') {
            Out::hide($this->nameForm . "_MaxLength_field", '');
            Out::hide($this->nameForm . "_Size_field", '');
            Out::hide($this->nameForm . "_divHtml", '');
            Out::show($this->nameForm . "_divValore", '');
            Out::hide($this->nameForm . "_rows_field", '');
            Out::hide($this->nameForm . "_cols_field", '');
            Out::show($this->nameForm . "_valueRitorno_field", '');
            Out::setInputTooltip($this->nameForm . '_valueRitorno', 'Imposta il nome della chiave di ritorno.');
        } else {
            Out::hide($this->nameForm . "_MaxLength_field", '');
            Out::hide($this->nameForm . "_Size_field", '');
            Out::hide($this->nameForm . "_divHtml", '');
            Out::show($this->nameForm . "_divValore", '');
            Out::hide($this->nameForm . "_rows_field", '');
            Out::hide($this->nameForm . "_cols_field", '');
        }
        if ($this->datoAggiuntivo['ITDVCA'] != "RegularExpression") {
            Out::hide($this->nameForm . "_RegExpr", '');
            Out::hide($this->nameForm . "_RegExpr_lbl", '');
        }
        if ($this->datoAggiuntivo['ITDTIP'] == "Sportello_Aggregato" || $this->datoAggiuntivo['ITDTIP'] == "Denom_Fiera" || $this->datoAggiuntivo['ITDTIP'] == "Posteggi_fiera") {
            Out::hide($this->nameForm . "_divInput", '');
        }

        $this->paramsTipoDato = array();
        if ($this->datoAggiuntivo['ITDTIP']) {
            Out::show($this->nameForm . '_divParamsTipoDato');

            $metaData = is_array($this->datoAggiuntivo['ITDMETA']) ? $this->datoAggiuntivo['ITDMETA'] : unserialize($this->datoAggiuntivo['ITDMETA']);
            if ($metaData && isset($metaData['PARAMSTIPODATO'])) {
                foreach ($metaData['PARAMSTIPODATO'] as $campo => $valore) {
                    $this->paramsTipoDato[] = array('CAMPO' => $campo, 'VALORE' => $valore);
                }
            }
        }

        $this->decodeVisualizzazioneTipizzato($this->datoAggiuntivo['ITDTIP']);
        $this->CaricaGrigliaExprOut();
        $this->caricaGrigliaParamsTipoDato();

        Out::setFocus('', $this->nameForm . '_Codice');
        Out::clearFields($this->nameForm, $this->divControllo);
    }

    public function creaComboTipo() {
        $this->praLib->CreaComboTipiCampi($this->nameForm . '_Tipo');
        $this->praLib->CreaComboTipiInput($this->nameForm . '_TipoInput');
        $this->praLib->CreaComboTipiValida($this->nameForm . '_Valida');
        $this->praLib->CreaComboTipiAzioniValida($this->nameForm . '_AzioneVal');
        $this->praLib->CreaComboTipiPosizione($this->nameForm . '_Posizione');
        $this->praLib->CreaComboTipiHtmlPosition($this->nameForm . '_htmlPosition');
        $this->praLib->CreaComboTipiValueCheck($this->nameForm . '_valueCheck');

//        Out::select($this->nameForm . '_TipoInput', 1, "Text", "0", "Text");
//        Out::select($this->nameForm . '_TipoInput', 1, "Data", "0", "Data");
//        Out::select($this->nameForm . '_TipoInput', 1, "TextArea", "0", "Text Area");
//        Out::select($this->nameForm . '_TipoInput', 1, "Select", "0", "Select");
//        Out::select($this->nameForm . '_TipoInput', 1, "Password", "0", "Password");
//        Out::select($this->nameForm . '_TipoInput', 1, "CheckBox", "0", "CheckBox");
//        Out::select($this->nameForm . '_TipoInput', 1, "RadioGroup", "0", "RadioGroup");
//        Out::select($this->nameForm . '_TipoInput', 1, "RadioButton", "0", "RadioButton");
//        Out::select($this->nameForm . '_TipoInput', 1, "Html", "0", "Html");
//        Out::select($this->nameForm . '_Valida', 1, "", "1", "");
//        Out::select($this->nameForm . '_Valida', 1, "email", "0", "e-mail");
//        Out::select($this->nameForm . '_Valida', 1, "Numeri", "0", "Solo Numeri");
//        Out::select($this->nameForm . '_Valida', 1, "Data", "0", "Data");
//        Out::select($this->nameForm . '_Valida', 1, "Lettere", "0", "Solo Lettere");
//        Out::select($this->nameForm . '_Valida', 1, "CodiceFiscale", "0", "Codice Fiscale");
//        Out::select($this->nameForm . '_Valida', 1, "PartitaIva", "0", "Partita Iva");
//        Out::select($this->nameForm . '_Valida', 1, "Importo", "0", "Importo");
//        Out::select($this->nameForm . '_Valida', 1, "Iban", "0", "Iban");
//        Out::select($this->nameForm . '_Valida', 1, "RegularExpression", "0", "Espressione Regolare");
//        Out::select($this->nameForm . '_Posizione', 1, "Sinistra", "1", "Sinistra");
//        Out::select($this->nameForm . '_Posizione', 1, "Destra", "0", "Destra");
//        Out::select($this->nameForm . '_Posizione', 1, "Sopra", "0", "Sopra");
//        Out::select($this->nameForm . '_Posizione', 1, "Sotto", "0", "Sotto");
//        Out::select($this->nameForm . '_htmlPosition', 1, "", "1", "Seleziona la posizione");
//        Out::select($this->nameForm . '_htmlPosition', 1, "Inizio", "0", "Inizio Raccolta");
//        Out::select($this->nameForm . '_htmlPosition', 1, "Default", "0", "Nella Raccolta");
//        Out::select($this->nameForm . '_htmlPosition', 1, "Fine", "0", "Fine Raccolta");
//        Out::select($this->nameForm . '_valueCheck', 1, "", "1", "Seleziona un valore");
//        Out::select($this->nameForm . '_valueCheck', 1, "1/0", "0", "1/0");
//        Out::select($this->nameForm . '_valueCheck', 1, "On/Off", "0", "on/off");
//        Out::select($this->nameForm . '_valueCheck', 1, "Si/No", "0", "si/no");
    }

    public function creaComboCampi($elencoCampi, $escludi) {
        foreach ($elencoCampi as $key => $campo) {
            if ($campo['ITDKEY'] != $this->datoAggiuntivo['ITDKEY']) {
                Out::select($this->nameForm . '_Campi', 1, $campo['ITDKEY'], "0", $campo['ITDKEY']);
            }
        }
    }

    public function creaComboRadioGroupRef() {
        foreach ($this->getRadioGroupRef() as $campo) {
            Out::select($this->nameForm . '_RadioGroupRef', 1, $campo['ITDKEY'], "0", $campo['ITDKEY']);
        }
    }

    public function getRadioGroupRef() {
        $radioGroupRef = array();
        $datiAggRadioGroup = $this->getDatiAggiuntivi();
        foreach ($datiAggRadioGroup as $campo) {
            if ($campo['ITDTIC'] == 'RadioGroup') {
                $radioGroupRef[] = $campo;
            }
        }

        return $radioGroupRef;
    }

    public function clearComboRadioGroupRef() {
        Out::html($this->nameForm . '_RadioGroupRef', '');
    }

//    public function creaComboCondizioni() {
//        Out::select($this->nameForm . '_Condizione', 1, "uguale", "1", "Uguale a");
//        Out::select($this->nameForm . '_Condizione', 1, "diverso", "0", "Diverso da");
//        Out::select($this->nameForm . '_Condizione', 1, "maggiore", "0", "Maggiore a");
//        Out::select($this->nameForm . '_Condizione', 1, "minore", "0", "Minore a");
//        Out::select($this->nameForm . '_Condizione', 1, "maggiore-uguale", "0", "Maggiore/Uguale a");
//        Out::select($this->nameForm . '_Condizione', 1, "minore-uguale", "0", "Minore/Uguale a");
//    }
//    public function DecodificaControllo($ctr) {
//        $msgCtr = '';
//        if ($ctr) {
//            $controlli = unserialize($ctr);
//            foreach ($controlli as $key => $campo) {
//                switch ($campo['CONDIZIONE']) {
//                    case '==':
//                        $condizione = "uguale a ";
//                        break;
//                    case '!=':
//                        $condizione = "diverso da ";
//                        break;
//                    case '>':
//                        $condizione = "maggiore a ";
//                        break;
//                    case '<':
//                        $condizione = "minore a ";
//                        break;
//                    case '>=':
//                        $condizione = "maggiore-uguale a ";
//                        break;
//                    case '<=':
//                        $condizione = "minore-uguale a ";
//                }
//                if ($campo['VALORE'] == '') {
//                    $valore = "vuoto";
//                } else {
//                    $valore = $campo['VALORE'];
//                }
//                switch ($campo['OPERATORE']) {
//                    case 'AND':
//                        $operatore = 'e ';
//                        break;
//                    case 'OR':
//                        $operatore = 'oppure ';
//                }
//                $msgCtr = $msgCtr . $operatore . 'il campo ' . $campo['CAMPO'] . ' è ' . $condizione . $valore . chr(10);
//            }
//        }
//        return $msgCtr;
//    }

    function CaricaGrigliaExprOut() {
        $_appoggio = array();
        foreach ($this->exprOut as $key => $value) {
            $_appoggio_rec = array();
            if ($value['EXPCTR']) {
                $_appoggio_rec['ESPRESSIONEOUT'] = $this->praLib->DecodificaControllo($value['EXPCTR']);
            }
            $_appoggio_rec['AZIONICOUNT'] = "1";
            $_appoggio[] = $_appoggio_rec;
        }
        $gridExprOut = new TableView(
            $this->gridExprOut, array('arrayTable' => $_appoggio,
            'rowIndex' => 'idx')
        );
        $gridExprOut->setPageNum(1);
        $gridExprOut->setPageRows(10000);
        TableView::enableEvents($this->gridExprOut);
        TableView::clearGrid($this->gridExprOut);
        $gridExprOut->getDataPage('json');
        return;
    }

    private function caricaGrigliaParamsTipoDato() {
        $griglia = new TableView($this->gridParamsTipoDato, array('arrayTable' => $this->paramsTipoDato, 'rowIndex' => 'idx'));

        $griglia->setPageNum(1);
        $griglia->setPageRows('99999');

        TableView::enableEvents($this->gridParamsTipoDato);
        TableView::clearGrid($this->gridParamsTipoDato);

        $griglia->getDataPage('json');
        return;
    }

    private function decodeVisualizzazioneTipizzato($valoreTipizzato) {
        if (
            $valoreTipizzato == "Sportello_Aggregato" ||
            $valoreTipizzato == "Denom_Fiera" ||
            $valoreTipizzato == "Posteggi_fiera" ||
            $valoreTipizzato == 'Tabella_Generica'
        ) {
            Out::tabDisable($this->nameForm . "_tabCampo", $this->nameForm . "_paneAspetto");
            Out::tabDisable($this->nameForm . "_tabCampo", $this->nameForm . "_paneRegole");
        } else {
            Out::tabEnable($this->nameForm . "_tabCampo", $this->nameForm . "_paneAspetto");
            Out::tabEnable($this->nameForm . "_tabCampo", $this->nameForm . "_paneRegole");
        }

        if ($valoreTipizzato) {
            Out::show($this->nameForm . '_divParamsTipoDato');
        } else {
            Out::hide($this->nameForm . '_divParamsTipoDato');
        }

        $infoTooltip = '';
        if (isset(praDatoTipizzato::$PARAMETRI_TIPIZZATO[$valoreTipizzato])) {
            $infoTooltip = '<table style="border-spacing: 10px; border-collapse: separate;"><tr><th colspan="2">Parametri disponibili</th></tr>';
            foreach (praDatoTipizzato::$PARAMETRI_TIPIZZATO[$valoreTipizzato] as $chiave => $descrizione) {
                $infoTooltip .= "<tr><th valign=\"top\">$chiave</th><td>$descrizione</td></tr>";
            }
            $infoTooltip .= "</table>";
        }

        Out::setInputTooltip($this->nameForm . '_Tipo', $infoTooltip);
    }

}
