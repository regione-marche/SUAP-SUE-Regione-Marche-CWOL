<?php

/**
 *
 * DETTAGLIO PASSO DA PROCEDIMENTO
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Marco Camilleti <marco.camilletti@italsoft.eu>
 * @author     Panetta Andimo <adimo.panetta@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>* 
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    05.03.2014
 * @link
 * @see
 * @sincend 
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segRic.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docRic.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praTipiAllegato.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praDatiAggiuntivi.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praEspressioni.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praAzioniFO.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibRiservato.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFormHelper.class.php';

function praPassoProc() {
    $praPassoProc = new praPassoProc();
    $praPassoProc->parseEvent();
    return;
}

class praPassoProc extends itaModel {

    public $praLib;
    public $proLib;
    public $praTipi;
    public $utiEnte;
    public $docLib;
    public $praLibRiservato;
    public $PRAM_DB;
    public $nameForm = "praPassoProc";
    public $divGes = "praPassoProc_divGestione";
    public $divCtrCampi = "praPassoProc_divCtrCampi";
    public $DivUpload = "praPassoProc_DivUpload";
    public $divMail = "praPassoProc_DivMail";
    public $gridAllegati = "praPassoProc_gridAllegati";
    public $gridEspressioni = "praPassoProc_gridEspressioniOut";
    public $gridAzioniFO = "praPassoProc_gridAzioniFO";
    public $gridDestinatari = "praPassoProc_gridDestinatari";
    public $allegati = array();
    public $currPranum;
    public $returnModel;
    public $returnMethod;
    public $rowidAppoggio;
    public $datiAppoggio;
    public $page;
    public $ext;
    public $selRow;
    public $enteMaster;
    public $currXHTML;
    public $currMAILTemplate;
    public $currXHTMLDis;
    public $currDescBox;
    public $praEspressioni = array();
    public $azioniFO = array();
    public $praProcDatiAggiuntiviFormname;
    public $iteVpaDettTab;
    public $destinatari = array();

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            //$this->PRAM_DB = $this->praLib->getPRAMDB();
            try {
                if ($_POST["enteMaster"]) {
                    $this->PRAM_DB = ItaDB::DBOpen('PRAM', $_POST["enteMaster"]);
                    $this->praLib = new praLib($_POST["enteMaster"]);
                } else {
                    $this->PRAM_DB = ItaDB::DBOpen('PRAM');
                    $this->praLib = new praLib();
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
            $this->docLib = new docLib();
            $this->proLib = new proLib();
            $this->praTipi = new praTipiAllegato();
            $this->utiEnte = new utiEnte();
            $this->praLibRiservato = new praLibRiservato();
            $this->allegati = App::$utente->getKey($this->nameForm . '_allegati');
            $this->currPranum = App::$utente->getKey($this->nameForm . '_currPranum');
            $this->currXHTML = App::$utente->getKey($this->nameForm . '_currXHTML');
            $this->currMAILTemplate = App::$utente->getKey($this->nameForm . '_currMAILTemplate');
            $this->currXHTMLDis = App::$utente->getKey($this->nameForm . '_currXHTMLDis');
            $this->currDescBox = App::$utente->getKey($this->nameForm . '_currDescBox');
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnMethod = App::$utente->getKey($this->nameForm . '_returnMethod');
            $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
            $this->datiAppoggio = App::$utente->getKey($this->nameForm . '_datiAppoggio');
            $this->page = App::$utente->getKey($this->nameForm . '_page');
            $this->ext = App::$utente->getKey($this->nameForm . '_ext');
            $this->selRow = App::$utente->getKey($this->nameForm . '_selRow');
            $this->enteMaster = App::$utente->getKey($this->nameForm . '_enteMaster');
            $this->praEspressioni = unserialize(App::$utente->getKey($this->nameForm . '_praEspressioni'));
            $this->praProcDatiAggiuntiviFormname = App::$utente->getKey($this->nameForm . '_praProcDatiAggiuntiviFormname');
            $this->azioniFO = App::$utente->getKey($this->nameForm . '_azioniFO');
            $this->iteVpaDettTab = App::$utente->getKey($this->nameForm . '_iteVpaDettTab');
            $this->destinatari = App::$utente->getKey($this->nameForm . '_destinatari');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_allegati', $this->allegati);
            App::$utente->setKey($this->nameForm . '_currPranum', $this->currPranum);
            App::$utente->setKey($this->nameForm . '_currMAILTemplate', $this->currMAILTemplate);
            App::$utente->setKey($this->nameForm . '_currXHTML', $this->currXHTML);
            App::$utente->setKey($this->nameForm . '_currXHTMLDis', $this->currXHTMLDis);
            App::$utente->setKey($this->nameForm . '_currDescBox', $this->currDescBox);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnMethod', $this->returnMethod);
            App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
            App::$utente->setKey($this->nameForm . '_datiAppoggio', $this->datiAppoggio);
            App::$utente->setKey($this->nameForm . '_page', $this->page);
            App::$utente->setKey($this->nameForm . '_ext', $this->ext);
            App::$utente->setKey($this->nameForm . '_selRow', $this->selRow);
            App::$utente->setKey($this->nameForm . '_enteMaster', $this->enteMaster);
            App::$utente->setKey($this->nameForm . '_praEspressioni', serialize($this->praEspressioni));
            App::$utente->setKey($this->nameForm . '_azioniFO', $this->azioniFO);
            App::$utente->setKey($this->nameForm . '_praProcDatiAggiuntiviFormname', $this->praProcDatiAggiuntiviFormname);
            App::$utente->setKey($this->nameForm . '_iteVpaDettTab', $this->iteVpaDettTab);
            App::$utente->setKey($this->nameForm . '_destinatari', $this->destinatari);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::hide($this->nameForm . "_ITEPAS[ITECDE]_field");
                Out::hide($this->nameForm . "_ITEPAS[ITEINT]_field");
                Out::hide($this->nameForm . "_DESTINATARIO_field");
                $this->allegati = array();
                $this->returnModel = $_POST[$this->nameForm . "_returnModel"];
                $this->returnMethod = $_POST[$this->nameForm . "_returnMethod"];
                $this->page = $_POST["page"];
                $this->selRow = $_POST["selRow"];
                $this->enteMaster = $_POST["enteMaster"];
                Out::setDialogOption($this->nameForm, 'title', "'" . $_POST[$this->nameForm . "_title"] . "'");
                $this->CreaCombo();
                $this->creaComboAssegato();
                $this->praLib->creaComboCondizioni($this->nameForm . '_Condizione');
                $this->praLibRiservato->creaComboRiservato($this->nameForm . '_ITEPAS[ITEFLRISERVATO]');
                $this->caricaSubForms();
                $filent_passoBO = $this->praLib->GetFilent(27);
                if ($filent_passoBO['FILDE1'] == 0) {
                    Out::hide($this->nameForm . "_divCaricaBO");
                }
                $this->destinatari = array();
                switch ($_POST['modo']) {
                    case "edit" :
                        if ($_POST['rowid']) {
                            $this->dettaglio($_POST['rowid'], 'rowid');
                        }
                        break;
                    case "add" :
                        if ($_POST['procedimento']) {
                            $this->currPranum = $_POST['procedimento'];
                            $this->apriInserimento();
                        }
                        break;
                }
                Out::hide($this->nameForm . '_gridAllegati');
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiungi':
                        if ($this->Controlli() !== false) {
                            try {
                                $fl_compseq = false;
                                $sql = "SELECT ROWID FROM ITEPAS WHERE ITECOD ='{$Itepas_rec['ITECOD']}' AND ITEIDR<>0 AND ITECOMPSEQ<>0 ";
                                $Itepas_tab_comp_seq = itaDB::DBSQLSelect($this->PRAM_DB, $sql);
                                if ($Itepas_tab_comp_seq) {
                                    $fl_compseq = true;
                                }
                                $Itepas_rec = $_POST[$this->nameForm . '_ITEPAS'];
                                $Itepas_rec['ITECOD'] = $this->currPranum;
                                if ($Itepas_rec['ITESEQ'] == 0 || $Itepas_rec['ITESEQ'] == '') {
                                    $Itepas_rec['ITESEQ'] = 99999;
                                }
                                if ($Itepas_rec['ITEPUB'] == 1 || ($Itepas_rec['ITEPUB'] == 0 && $Itepas_rec['ITECOM'] == 0)) {
                                    $Itepas_rec['ITECDE'] = "";
                                }
                                if ($Itepas_rec['ITERIF'] == 0) {
                                    $Itepas_rec['ITEPROC'] = $Itepas_rec['ITEDAP'] = $Itepas_rec['ITEALP'] = "";
                                }

                                $Itepas_rec['ITEKEY'] = $this->praLib->keyGenerator($Itepas_rec['ITECOD']);
                                $insert_Info = 'Oggetto: Inserimento passo seq ' . $Itepas_rec['ITESEQ'] . ' - ' . $Itepas_rec['ITEKEY'];
                                if ($this->insertRecord($this->PRAM_DB, 'ITEPAS', $Itepas_rec, $insert_Info)) {
                                    $rowid_itepas = $this->getLastInsertId();
                                    $this->praLib->ordinaPassiProc($this->currPranum);
                                    $this->praLib->AggiornaMarcaturaProcedimento($this->currPranum, 'codice');
                                    if ($fl_compseq && $Itepas_rec['ITEIDR']) {
                                        Out::msgInfo("Aggiungi Passo", "Il passo è stato aggiunto con successo.<br>
                                                 Il Passo è accorpato al rapporto completo.<br>
                                                 E' presente una sequenza speciale di accorpamento,<br>
                                                 posiziona il nuovo passo nel rapporto completo."
                                        );
                                    } else {
                                        Out::msgBlock("", 1000, true, "Passo Aggiunto con successo.");
                                    }
                                    //$this->Dettaglio($rowid_itepas, 'rowid');
                                    $this->SalvaAzioniFO($Itepas_rec['ITEKEY']);
                                    $this->returnToParent();
                                }
                            } catch (Exception $e) {
                                Out::msgStop("Errore di Inserimento su Tipi di Passi.", $e->getMessage());
                                break;
                            }
                        }
                        break;

                    case $this->nameForm . '_rapportoConfig':
                        $itekey = $_POST[$this->nameForm . '_ITEPAS']['ITEKEY'];
                        $Itepas_rec = $this->praLib->GetItepas($itekey, 'itekey');
                        itaLib::openForm('praRapportoConfig');
                        $objModel = itaModel::getInstance('praRapportoConfig');
                        $objModel->setEvent('openform');
                        $objModel->setReturnModel($this->nameForm);
                        $objModel->setReturnEvent("returnFromRapportoConfig");
                        $objModel->setReturnId($this->nameForm . '_rapportoConfig');
                        $objModel->setProcedimento($Itepas_rec['ITECOD']);
                        $objModel->parseEvent();
                        break;
                    case $this->nameForm . '_ApriObblExpr':
                        $model = 'praPassoProcExpr';
                        $itekey = $_POST[$this->nameForm . '_ITEPAS']['ITEKEY'];
                        $iteobe = $_POST[$this->nameForm . '_ITEPAS']['ITEOBE'];
                        $Itepas_rec = $this->praLib->GetItepas($itekey, 'itekey');
                        $_POST = array();
                        $_POST['dati'] = array(
                            'TITOLO' => 'Espressione per rendere obbligatorio il passo:',
                            'ITEKEY' => $itekey,
                            'ITECOD' => $Itepas_rec['ITECOD'],
                            //'ITEATE' => $Itepas_rec['ITEOBE'],
                            'ITEATE' => $iteobe,
                            'TABELLA' => "ITEDAG"
                        );
                        $_POST['event'] = 'openform';
                        $_POST['perms'] = $this->perms;
                        $_POST['tipo'] = "ITEOBE";
                        $_POST['ITEKEY'] = $itekey;
                        $_POST['ITECOD'] = $Itepas_rec['ITECOD'];
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnObblExpr';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case $this->nameForm . '_ApriAttivaExpr':
                        $model = 'praPassoProcExpr';
                        $itekey = $_POST[$this->nameForm . '_ITEPAS']['ITEKEY'];
                        $iteate = $_POST[$this->nameForm . '_ITEPAS']['ITEATE'];
                        $Itepas_rec = $this->praLib->GetItepas($itekey, 'itekey');
                        $_POST = array();
                        $_POST['dati'] = array(
                            'TITOLO' => 'Espressione per attivare il passo:',
                            'ITEKEY' => $itekey,
                            'ITECOD' => $Itepas_rec['ITECOD'],
                            //'ITEATE' => $Itepas_rec['ITEATE'],
                            'ITEATE' => $iteate,
                            'TABELLA' => "ITEDAG"
                        );
                        $_POST['event'] = 'openform';
                        $_POST['perms'] = $this->perms;
                        $_POST['tipo'] = "ITEATE";
                        $_POST['ITEKEY'] = $itekey;
                        $_POST['ITECOD'] = $Itepas_rec['ITECOD'];
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnAttivaExpr';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_Duplica':
                        $where = " WHERE ITECOD = '" . $this->currPranum . "'";
                        praRic::praRicItepas($this->nameForm, 'ITEPAS', $where, '4');
                        break;
                    case $this->nameForm . '_DuplicaDatiAgg':
                        /* @var $praProcDatiAggiuntivi praProcDatiAggiuntivi */
                        $praProcDatiAggiuntivi = itaModel::getInstance('praProcDatiAggiuntivi', $this->praProcDatiAggiuntiviFormname);
                        $praProcDatiAggiuntivi->duplicaDati();
                        break;
                    case $this->nameForm . '_ConfermaUpdateTemplate':
                        $this->Aggiorna(false);
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $this->Aggiorna();
                        break;
                    case $this->nameForm . '_ConfermaCancellaEspressione':
                        if (!$this->praEspressioni->CancellaEspressione($this->rowidAppoggio, $this)) {
                            Out::msgStop("Attenzione", "Errore in cancellazione espressione su ITECONTROLLI");
                        }
                        $this->CaricaGriglia($this->gridEspressioni, $this->praEspressioni->GetGriglia());
                        break;
                    case $this->nameForm . '_Svuota':
                        Out::valore($this->nameForm . '_Destinazione', '');
                        Out::valore($this->nameForm . '_DescrizioneVai', '');
                        Out::valore($this->nameForm . '_ITEPAS[ITEVPA]', '');
                        break;
                    case $this->nameForm . '_SvuotaNo':
                        Out::valore($this->nameForm . '_DestinazioneNo', '');
                        Out::valore($this->nameForm . '_DescrizioneVaiNo', '');
                        Out::valore($this->nameForm . '_ITEPAS[ITEVPN]', '');
                        break;
                    case $this->nameForm . '_CtrSvuota':
                        Out::valore($this->nameForm . '_CtrPasso', '');
                        Out::valore($this->nameForm . '_CtrDesPasso', '');
                        Out::valore($this->nameForm . '_ITEPAS[ITECTP]', '');
                        break;
                    case $this->nameForm . '_PassoPrecSvuota':
                        Out::valore($this->nameForm . '_PassoPrecUpl', '');
                        Out::valore($this->nameForm . '_PassoPrecUplDesc', '');
                        Out::valore($this->nameForm . '_ITEPAS[ITEDWP]', '');
                        break;
                    case $this->nameForm . '_Svuota2':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEWRD'] != '') {
                            /* @var $praProcDatiAggiuntivi praProcDatiAggiuntivi */
                            $praProcDatiAggiuntivi = itaModel::getInstance('praProcDatiAggiuntivi', $this->praProcDatiAggiuntiviFormname);

                            if (count($praProcDatiAggiuntivi->GetDatiAggiuntivi())) {
                                Out::msgInfo('Avviso', 'Testo non modificabile in presenza di campi aggiuntivi.<br>Procedere prima con la cancellazione dei dati aggiuntivi.');
                            } else {
                                Out::valore($this->nameForm . '_ITEPAS[ITEWRD]', '');
                                Out::valore($this->nameForm . '_WRDPRECOMPILED', '');
                            }
                        }
                        break;
                    case $this->nameForm . '_Svuota3':
                        Out::valore($this->nameForm . '_ITEPAS[ITEIMG]', '');
                        break;
                    case $this->nameForm . '_Svuota4':
                        Out::valore($this->nameForm . '_ITEPAS[ITEHELP]', '');
                        break;
                    case $this->nameForm . '_SvuotaExt':
                        Out::valore($this->nameForm . '_ITEPAS[ITEEXT]', '');
                        break;
                    case $this->nameForm . '_SvuotaTipi':
                        Out::valore($this->nameForm . '_ITEPAS[ITETAL]', '');
                        break;
                    case $this->nameForm . '_RifSvuota':
                        Out::valore($this->nameForm . '_ITEPAS[ITEPROC]', '');
                        Out::valore($this->nameForm . '_ITEPAS[ITEDAP]', '');
                        Out::valore($this->nameForm . '_ITEPAS[ITEALP]', '');
                        Out::valore($this->nameForm . '_DesProcedimento', '');
                        Out::valore($this->nameForm . '_DalPasso', '');
                        Out::valore($this->nameForm . '_AlPasso', '');
                        Out::valore($this->nameForm . '_DesDalPasso', '');
                        Out::valore($this->nameForm . '_DesAlPasso', '');
                        break;
                    case $this->nameForm . '_TemplateSvuota':
                        Out::valore($this->nameForm . "_TemplateProc", "");
                        Out::valore($this->nameForm . "_DesProc", "");
                        Out::valore($this->nameForm . "_TemplatePasso", "");
                        Out::valore($this->nameForm . "_DesPasso", "");
                        Out::valore($this->nameForm . "_ITEPAS[TEMPLATEKEY]", "");
                        break;
                    case $this->nameForm . '_ITEPAS[ITECLT]_butt':
                        praRic::praRicPraclt($this->nameForm, "RICERCA Tipo Passo");
                        break;
                    case $this->nameForm . '_ITEPAS[ITERES]_butt':
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "RICERCA Dipendenti");
                        break;
                    case $this->nameForm . '_ITEPAS[ITESTAP]_butt':
                        praRic::praRicAnastp($this->nameForm, "", '1');
                        break;
                    case $this->nameForm . '_ITEPAS[ITESTCH]_butt':
                        praRic::praRicAnastp($this->nameForm, "", '2');
                        break;
                    case $this->nameForm . '_WRDPRECOMPILED_butt':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEWRD'] != '') {
//                            $tipoEnte = $this->praLib->GetTipoEnte();
//                            if ($tipoEnte == "S") {
//                                $enteMaster = $this->praLib->GetEnteMaster();
//                            }
//                            if ($enteMaster) {
//                                $ditta = $enteMaster;
//                            } else {
                            $ditta = App::$utente->getKey('ditta');
                            //}

                            $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/';
                            if (!is_dir($destinazione)) {
                                Out::msgStop("Errore.", 'Directory non presente!');
                                break;
                            }
                            $prefilled = $destinazione . "prefilled_" . $_POST[$this->nameForm . '_ITEPAS']['ITEWRD'];
                            if (file_exists($prefilled)) {
                                Out::openDocument(utiDownload::getUrl(pathinfo($prefilled, PATHINFO_BASENAME), $prefilled));
                            }
                        }
                        break;

                    case $this->nameForm . '_ITEPAS[ITEWRD]_butt':
                        $anapra_rec = $this->praLib->GetAnapra($this->currPranum);
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEWRD'] != '') {
                            $tipoEnte = $this->praLib->GetTipoEnte();
                            if ($tipoEnte == "S" && $anapra_rec['PRASLAVE'] == 1) {
                                $enteMaster = $this->praLib->GetEnteMaster();
                            }
                            if ($enteMaster) {
                                $ditta = $enteMaster;
                            } else {
                                $ditta = App::$utente->getKey('ditta');
                            }

                            $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/';
                            if (!is_dir($destinazione)) {
                                Out::msgStop("Errore.", 'Directory non presente!');
                                break;
                            }
                            Out::openDocument(
                                    utiDownload::getUrl(
                                            $_POST[$this->nameForm . '_ITEPAS']['ITEWRD'], $destinazione . $_POST[$this->nameForm . '_ITEPAS']['ITEWRD']
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITEIMG]_butt':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEIMG'] != '') {
                            $ditta = App::$utente->getKey('ditta');
                            $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/immagini/';
                            if (!is_dir($destinazione)) {
                                Out::msgStop("Errore.", 'Directory non presente!');
                                break;
                            }
                            Out::openDocument(
                                    utiDownload::getUrl(
                                            $_POST[$this->nameForm . '_ITEPAS']['ITEIMG'], $destinazione . $_POST[$this->nameForm . '_ITEPAS']['ITEIMG']
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITEHELP]_butt':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEHELP'] != '') {
                            $Anahelp_rec = $this->praLib->GetAnahelp($_POST[$this->nameForm . '_ITEPAS']['ITEHELP']);
                            $html = '<iframe id="utiIFrame_frame" style="overflow:auto;" class="ita-frame" frameborder="0" width="100%" height="95%" src="' . $Anahelp_rec['FORMATO'] . '"></iframe>';
                            Out::html($this->nameForm, $html);
                        }
                        break;
                    case $this->nameForm . '_buttVediUrl':
                        if ($_POST[$this->nameForm . '_iteurl']) {
                            Out::openDocument($_POST[$this->nameForm . '_iteurl']);
                        }
                        break;
                    case $this->nameForm . '_Dizionario':
                        $praLibVar = new praLibVariabili();
                        if ($this->datoAggiuntivo['ITEPUB'] == 1) {
                            $praLibVar->setFrontOfficeFlag(true);
                        }
                        $praLibVar->setCodiceProcedimento($this->datoAggiuntivo['ITECOD']);
                        $praLibVar->setChiavePasso($this->datoAggiuntivo['ITEKEY']);
                        $arrayLegenda = $praLibVar->getLegendaProcedimento('adjacency');
                        praRic::ricVariabili($arrayLegenda, $this->nameForm, 'returnVariabiliITEURL', true);
                        break;
                    case $this->nameForm . '_ITEPAS[ITECDE]_butt':
                        proRic::proRicAnamed($this->nameForm, $where, 'proAnamed');
                        break;
                    case $this->nameForm . '_ITEPAS[ITETBA]_butt':
                        $testiBase[0]['FILENAME'] = '12345';
                        $testiBase[0]['PATHFILE'] = 'Path1';
                        $testiBase[1]['FILENAME'] = '54321';
                        $testiBase[1]['PATHFILE'] = 'Path3';
                        $testiBase[2]['FILENAME'] = '12543';
                        $testiBase[2]['PATHFILE'] = 'Path2';
                        praRic::praRicTestiBase($testiBase, $this->nameForm);
                        break;
                    case $this->nameForm . '_ITEPAS[ITEPROC]_butt':
                        praRic::praRicAnapra($this->nameForm, "Ricerca Procedimenti");
                        break;
                    case $this->nameForm . '_ITEPAS[ITETAL]_butt':
                        $Tipi = $this->praTipi->getTipi();
                        $this->praTipi->CaricaTipi($Tipi, $this->nameForm);
                        break;
                    case $this->nameForm . '_ITEPAS[ITENRA]_butt':
                        $praLibVar = new praLibVariabili();
                        if ($this->dati[$this->idxDati]['ITEPUB'] == 1) {
                            $praLibVar->setFrontOfficeFlag(true);
                        }
                        $praLibVar->setCodiceProcedimento($this->dati[$this->idxDati]['ITECOD']);
                        $praLibVar->setChiavePasso($this->dati[$this->idxDati]['ITEKEY']);
                        $arrayLegenda = $praLibVar->getLegendaProcedimento('adjacency', 'none');
                        praRic::ricVariabili($arrayLegenda, $this->nameForm, 'returnVariabili', true);
                        break;
                    case $this->nameForm . '_AlPasso_butt':
                        $retid = "6";
                    case $this->nameForm . '_DalPasso_butt':
                        if ($retid != "6")
                            $retid = '5';
                        if ($_POST[$this->nameForm . "_ITEPAS"]['ITEPROC']) {
                            $where = "WHERE ITEPUB = 1 AND ITECOD = " . $_POST[$this->nameForm . "_ITEPAS"]['ITEPROC'];
                            praRic::praRicItepas($this->nameForm, 'ITEPAS', $where, $retid, '', 'asc');
                        } else {
                            Out::msgInfo("Ricerca passi", "Scegliere il procediemnto di riferimento");
                        }
                        break;
                    case $this->nameForm . '_NomeFileUpload_butt':
                        $praLibVar = new praLibVariabili();
                        $arrayLegenda = $praLibVar->getLegendaTemplateUpload('adjacency');
                        praRic::ricVariabili($arrayLegenda, $this->nameForm, 'returnVariabiliUpload', true);
                        break;
                    case $this->nameForm . '_TemplateProc_butt':
                        praRic::praRicAnapra($this->nameForm, "Ricerca procedimento", "PASSOTEMPLATE");
                        break;
                    case $this->nameForm . '_Campi_butt':
                        $praLibVar = new praLibVariabili();
                        $praLibVar->setFrontOfficeFlag(true);
                        $praLibVar->setCodiceProcedimento($_POST[$this->nameForm . "_ITEPAS"]['ITECOD']);
                        $praLibVar->setChiavePasso($_POST[$this->nameForm . "_ITEPAS"]['ITEKEY']);
                        $arrayLegenda = $praLibVar->getLegendaProcedimento('adjacency', 'none');
                        praRic::ricVariabili($arrayLegenda, $this->nameForm, 'returnVariabiliCampi', true);
                        break;
                    case $this->nameForm . '_FileLocaleTesto':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEWRD'] != '') {
                            /* @var $praProcDatiAggiuntivi praProcDatiAggiuntivi */
                            $praProcDatiAggiuntivi = itaModel::getInstance('praProcDatiAggiuntivi', $this->praProcDatiAggiuntiviFormname);

                            if (count($praProcDatiAggiuntivi->GetDatiAggiuntivi())) {
                                Out::msgQuestion("Avviso!", "Testo con campi aggiuntivi.<br>Confermando, viene modificato il testo e conservati i dati aggiuntivi", array(
                                    'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaUpload', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                    'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaUpload', 'model' => $this->nameForm, 'shortCut' => "f5")
                                        )
                                );
                            } else {
                                Out::msgQuestion("Upload.", "Vuoi caricare un documento interno o uno esterno?", array(
                                    'F8-Esterno' => array('id' => $this->nameForm . '_UploadDocEsterno', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                    'F5-Interno' => array('id' => $this->nameForm . '_UploadDocInterno', 'model' => $this->nameForm, 'shortCut' => "f5")
                                        )
                                );
                            }
                        } else {
                            Out::msgQuestion("Upload.", "Vuoi caricare un documento interno o uno esterno?", array(
                                'F8-Esterno' => array('id' => $this->nameForm . '_UploadDocEsterno', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Interno' => array('id' => $this->nameForm . '_UploadDocInterno', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_FileLocale':
                        Out::msgQuestion("Upload.", "Vuoi caricare un file interno o uno esterno?", array(
                            'F8-Esterno' => array('id' => $this->nameForm . '_UploadFileEsterno', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Interno' => array('id' => $this->nameForm . '_UploadFileInterno', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaUpload':
                        Out::msgQuestion("Upload.", "Vuoi caricare un documento interno o uno esterno?", array(
                            'F8-Esterno' => array('id' => $this->nameForm . '_UploadDocEsterno', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Interno' => array('id' => $this->nameForm . '_UploadDocInterno', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_UploadDocEsterno':
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUploadDocEsterno";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_UploadDocInterno':
                        if ($ditta == '')
                            $ditta = App::$utente->getKey('ditta');
                        $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/';
                        if (!is_dir($destinazione)) {
                            Out::msgStop("Errore.", 'Directory non presente!');
                            break;
                        }
                        $matriceSelezionati = array();
                        $matriceSelezionati = $this->GetFileList($destinazione);
                        if ($matriceSelezionati) {
                            praRic::ricImmProcedimenti($matriceSelezionati, $this->nameForm, 'returnIndiceITEWRD', 'Testi Disponibili');
                        } else {
                            Out::msgInfo('Attenzione.', 'Nessun Testo presente in elenco. Caricare manualmente il Testo.');
                        }
                        break;
                    case $this->nameForm . '_UploadFileEsterno':
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUploadFileEsterno";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_UploadFileInterno':
                        if ($ditta == '')
                            $ditta = App::$utente->getKey('ditta');
                        $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/immagini/';
                        if (!is_dir($destinazione)) {
                            Out::msgStop("Errore.", 'Directory non presente!');
                            break;
                        }
                        $matriceSelezionati = array();
                        $matriceSelezionati = $this->GetFileList($destinazione);
                        if ($matriceSelezionati) {
                            praRic::ricImmProcedimenti($matriceSelezionati, $this->nameForm, 'returnIndiceITEIMG', 'Immagini Disponibili');
                        } else {
                            Out::msgInfo('Attenzione.', 'Nessuna Immagine presente in elenco. Caricare manualmente una Immagine.');
                        }

                        break;
                    case $this->nameForm . '_Destinazione_butt':
                        $retid = '1';
                    case $this->nameForm . '_DestinazioneNo_butt':
                        if ($retid != "1")
                            $retid = '2';
                    case $this->nameForm . '_CtrPasso_butt':
                        $where = '';
                        if ($retid != "1" && $retid != "2") {
                            $retid = '3';
                        }
                        if ($_POST[$this->nameForm . '_ITEPAS']['ROWID'] == '') {
                            $itecod = $_POST[$this->nameForm . '_ITEPAS']['ITECOD'];
                            $where = " WHERE ITECOD = '" . $itecod . "' AND ITEPUB <> ''";
                        } else {
                            $Itepas_rec = $this->praLib->GetItepas($_POST[$this->nameForm . '_ITEPAS']['ROWID'], 'rowid');
                            $itecod = $Itepas_rec['ITECOD'];
                            $where = " WHERE ITECOD = '" . $itecod . "' AND ITEKEY <> '" . $Itepas_rec['ITEKEY'] . "'";
                        }
                        praRic::praRicItepas($this->nameForm, 'ITEPAS', $where, $retid);
                        break;
                    case $this->nameForm . '_PassoPrecUpl_butt':
                        $Itepas_rec = $this->praLib->GetItepas($_POST[$this->nameForm . '_ITEPAS']['ROWID'], 'rowid');
                        $where = " WHERE ITECOD = '" . $Itepas_rec['ITECOD'] . "' AND ITEUPL  = 1 AND ITEPUB = 1";
                        praRic::praRicItepas($this->nameForm, 'ITEPAS', $where, "7");
                        break;
                    case $this->nameForm . '_Sovrascrivi':
                        if (!@rename($this->datiAppoggio['origFile'], $this->datiAppoggio['destinazione'] . $this->datiAppoggio['nomeFile'])) {
                            Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                            break;
                        } else {
                            Out::valore($this->nameForm . '_ITEPAS[ITEWRD]', $this->datiAppoggio['nomeFile']);
                        }
                        break;
                    case $this->nameForm . '_CercaDocumento':
                        docRic::docRicDocumenti($this->nameForm, " WHERE CLASSIFICAZIONE = 'PRATICHE'");
                        break;
                    case $this->nameForm . '_MailTestoBase':
                        Out::msgQuestion("Configurazione Mail", "Scegli il modello del corpo della mail da configurare.", array(
                            'Mail al Richiedente' => array('id' => $this->nameForm . '_TemplateMailRichiedente', 'model' => $this->nameForm),
                            'Mail al Responsabile' => array('id' => $this->nameForm . "_TemplateMailResponsabile", 'model' => $this->nameForm)
                                )
                        );
                        break;
                    case $this->nameForm . '_MailTestoBaseComunicazione':
                        $this->editTemplateMail("COMUNICAZIONE");
                        break;
                    case $this->nameForm . '_TemplateMailRichiedente':
                        $this->editTemplateMail("RICHIEDENTE");
                        break;
                    case $this->nameForm . '_TemplateMailResponsabile':
                        $this->editTemplateMail("RESPONSABILE");
                        break;
                    case $this->nameForm . '_pdfTestoBase':
                    case $this->nameForm . '_editTestoBase':
                        $rowid = $_POST[$this->nameForm . '_ITEPAS']['ROWID'];
                        $Itepas_rec = $this->praLib->GetItepas($rowid, 'rowid');
                        $praLibVar = new praLibVariabili();
                        if ($Itepas_rec['ITEPUB'] == 1) {
                            $praLibVar->setFrontOfficeFlag(true);
                        }
                        $praLibVar->setCodiceProcedimento($Itepas_rec['ITECOD']);
                        $praLibVar->setChiavePasso($Itepas_rec['ITEKEY']);
                        $dictionaryLegend = $praLibVar->getLegendaProcedimento('adjacency', 'smarty');

                        $model = 'utiEditDiag';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['edit_text'] = $this->currXHTML;
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
                    case $this->nameForm . '_pdfTestoBaseDis':
                        $rowid = $_POST[$this->nameForm . '_ITEPAS']['ROWID'];
                        $Itepas_rec = $this->praLib->GetItepas($rowid, 'rowid');
                        $praLibVar = new praLibVariabili();
                        if ($Itepas_rec['ITEPUB'] == 1) {
                            $praLibVar->setFrontOfficeFlag(true);
                        }
                        $praLibVar->setCodiceProcedimento($Itepas_rec['ITECOD']);
                        $praLibVar->setChiavePasso($Itepas_rec['ITEKEY']);
                        $dictionaryLegend = $praLibVar->getLegendaProcedimento('adjacency', 'smarty');
                        $model = 'utiEditDiag';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['edit_text'] = $this->currXHTMLDis;
                        $_POST['returnModel'] = $this->nameForm;
                        $_POST['returnEvent'] = 'returnEditDiagDist';
                        $_POST['returnField'] = $this->nameForm . '_pdfTestoBaseDis';
                        $_POST['dictionaryLegend'] = $dictionaryLegend;
                        $_POST['readonly'] = false;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_OpenDescBox':
                        $rowid = $_POST[$this->nameForm . '_ITEPAS']['ROWID'];
                        $Itepas_rec = $this->praLib->GetItepas($rowid, 'rowid');
                        $praLibVar = new praLibVariabili();
                        if ($Itepas_rec['ITEPUB'] == 1) {
                            $praLibVar->setFrontOfficeFlag(true);
                        }
                        $praLibVar->setCodiceProcedimento($Itepas_rec['ITECOD']);
                        $praLibVar->setChiavePasso($Itepas_rec['ITEKEY']);
                        $dictionaryLegend = $praLibVar->getLegendaProcedimento('adjacency', 'smarty');
                        $model = 'utiEditDiag';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['edit_text'] = $this->currDescBox;
                        $_POST['returnModel'] = $this->nameForm;
                        $_POST['returnEvent'] = 'returnEditDiagDescBox';
                        $_POST['returnField'] = $this->nameForm . '_OpenDescBox';
                        $_POST['dictionaryLegend'] = $dictionaryLegend;
                        $_POST['readonly'] = false;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_VediDocumento':
                        $codice = $_POST[$this->nameForm . '_ITEPAS']['ITETBA'];
                        $documenti_rec = $this->docLib->getDocumenti($codice);
                        switch ($documenti_rec['TIPO']) {
                            case 'XHTML':
                                $rowid = $_POST[$this->nameForm . '_ITEPAS']['ROWID'];
                                $Itepas_rec = $this->praLib->GetItepas($rowid, 'rowid');
                                $praLibVar = new praLibVariabili();
                                if ($Itepas_rec['ITEPUB'] == 1) {
                                    $praLibVar->setFrontOfficeFlag(true);
                                }
                                $praLibVar->setCodiceProcedimento($Itepas_rec['ITECOD']);
                                $praLibVar->setChiavePasso($Itepas_rec['ITEKEY']);
                                $dictionaryLegend = $praLibVar->getLegendaProcedimento('adjacency', 'smarty');

                                $model = 'utiEditDiag';
                                $_POST = array();
                                $_POST['event'] = 'openform';
                                $_POST['edit_text'] = $documenti_rec['CONTENT'];
                                $_POST['returnModel'] = $this->nameForm;
                                $_POST['returnEvent'] = '';
                                $_POST['returnField'] = '';
                                $_POST['dictionaryLegend'] = $dictionaryLegend;

                                $_POST['readonly'] = true;
                                itaLib::openForm($model);
                                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                $model();
                                break;
                            case "MSWORD HTML":
                            case "RTF":
                            case "ODT":
                            case "XML":
                            case "TXT":
                                $nomeDoc = $documenti_rec['CODICE'] . '.' . $documenti_rec['TIPO'];
                                $nomeFile = $documenti_rec['CONTENT'] . '.' . $documenti_rec['TIPO'];
                                $docPath = Config::getPath('general.fileEnte') . "ente" . App::$utente->getKey('ditta') . "/documenti/";
                                //                                $docPath = Config::getPath('general.itaDocumenti');
                                $file = $docPath . $nomeFile;
                                if (file_exists($file)) {
                                    Out::openDocument(utiDownload::getUrl($nomeDoc, $file));
                                }
                                break;
                        }
                        break;
                    case $this->nameForm . '_TogliDocumento':
                        Out::valore($this->nameForm . "_ITEPAS[ITETBA]", '');
                        Out::valore($this->nameForm . "_DocumentoOgg", '');
                        break;
                    case $this->nameForm . '_Precompila':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEWRD'] != '') {
                            // query per preparare dati 
                            $sql = "SELECT * FROM ITEDAG WHERE ITEKEY = '" . $_POST[$this->nameForm . '_ITEPAS']['ITEKEY'] . "'";
                            $Itedag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                            if ($Itedag_tab) {
                                $dati = array();
                                foreach ($Itedag_tab as $Itedag_rec) {
                                    if ($Itedag_rec['ITDDIZ'] == 'C') {
                                        $chiavecampo = ($Itedag_rec['ITDALIAS']) ? $Itedag_rec['ITDALIAS'] : $Itedag_rec['ITDKEY'];
                                        $dati[$chiavecampo] = $Itedag_rec['ITDVAL'];
                                    }
                                }
                            }
                            // nome files
                            $ditta = App::$utente->getKey('ditta');
                            $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/';
                            if (!is_dir($destinazione)) {
                                Out::msgStop("Errore.", 'Directory non presente!');
                                break;
                            }
                            $input = $destinazione . $_POST[$this->nameForm . '_ITEPAS']['ITEWRD'];
                            $output = $destinazione . "prefilled_ed_" . $_POST[$this->nameForm . '_ITEPAS']['ITEWRD'];
                            if ($this->praLib->FillFormPdf($dati, $input, $output)) {
                                $input = $destinazione . "prefilled_ed_" . $_POST[$this->nameForm . '_ITEPAS']['ITEWRD'];
                                $output = $destinazione . "prefilled_" . $_POST[$this->nameForm . '_ITEPAS']['ITEWRD'];
                                if ($this->praLib->FlatFormPdf($input, $output)) {
                                    Out::valore($this->nameForm . '_WRDPRECOMPILED', pathinfo($output, PATHINFO_BASENAME));
                                    Out::msgInfo("Compila Modello", "Modello compilato correttamente.");
                                } else {
                                    Out::msgStop("Compila Modello", "Errore in Bloccaggio campi Modello");
                                }
                            } else {
                                Out::msgStop("Compila Modello", "Errore in Compilazione campi Modello");
                            }
                        }
                        break;
                    case $this->nameForm . "_CaricaHelp":
                        praRic::praRicAnahelp($this->nameForm);
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
                        $arrExpr = array();
                        if (!$_POST[$this->nameForm . '_Campi'] == '' && !$simbolo == '') {
                            $arrExpr = unserialize($_POST[$this->nameForm . '_ctrSerializzato']);
                            $arrExpr[] = array(
                                "CAMPO" => $_POST[$this->nameForm . '_Campi'],
                                "CONDIZIONE" => $simbolo,
                                "VALORE" => $_POST[$this->nameForm . '_ValoreCtr'],
                            );
                            Out::clearFields($this->nameForm, $this->nameForm . "_divControllo");
                            //
                            $this->praEspressioni->CaricaEspressione($arrExpr, $_POST[$this->nameForm . '_AzioneCtr']);
                            $this->CaricaGriglia($this->gridEspressioni, $this->praEspressioni->GetGriglia());
                        }
                        Out::hide($this->nameForm . "_divControllo");
                        break;
                    case $this->nameForm . '_AnnullaCtr':
                        Out::hide($this->nameForm . "_divControllo", '');
                        break;

                    case $this->nameForm . '_ITEPAS[ITEDEFSTATO]_butt':
                        praRic::praRicAnastp($this->nameForm, '', '3');
                        break;
                    case $this->nameForm . '_ClassificazioneDocumenti_butt':
                        $model = 'docClasDocumenti';
                        itaLib::openDialog($model);
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setReturnModel($this->nameForm);
                        $modelObj->setReturnEvent('returnClassificazioneDocumenti');
                        $modelObj->setEvent('openform');
                        $modelObj->setModalita('readonly');
                        $modelObj->setReturnId($_POST['select_rowid']);
                        $modelObj->parseEvent();
                        break;
                    case $this->nameForm . '_ClassificazioneDocumentiSvuota':
                        Out::valore($this->nameForm . '_ITEPAS[ROWID_DOC_CLASSIFICAZIONE]', '');
                        Out::valore($this->nameForm . '_ClassificazioneDocumenti', '');
                        break;

                    case $this->nameForm . '_SEQANTECEDENTE_butt':
                        $currItecod = $_POST[$this->nameForm . '_ITEPAS']['ITECOD'];
                        $where = " WHERE ITECOD=$currItecod AND ITEKPRE=''"; // AND ITESEQ<>" . $_POST[$this->nameForm . '_ITEPAS']['ITESEQ'];
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITESEQ']) {
                            $where .= " AND ITESEQ<>" . $_POST[$this->nameForm . '_ITEPAS']['ITESEQ'];
                        }
                        praRic::praRicItepas($this->nameForm, 'ITEPAS', $where, '8', "Seleziona il passo da collegare al seguente", true);
                        break;

                    case $this->nameForm . '_RimuoviAntecedente':
                        Out::valore($this->nameForm . '_DESITEKPRE', '');
                        Out::valore($this->nameForm . '_SEQANTECEDENTE', '');
                        Out::valore($this->nameForm . '_ITEPAS[ITEKPRE]', '');
                        break;

                    case $this->nameForm . '_VediCollegamenti':
                        $itepas_rec = $this->praLib->GetItepas($_POST[$this->nameForm . '_ITEPAS']['ITEKEY'], "itekey");
                        praRic::praItepasAntecedenti($this->nameForm, $itepas_rec, $this->PRAM_DB);
                        break;

                    case $this->nameForm . '_ConfermaDatiAzione':
                        $AzioniFO = new praAzioniFO();
                        $this->azioniFO[$_POST[$this->nameForm . '_IdGridAzione']]['CLASSEAZIONE'] = $_POST[$this->nameForm . '_ClasseAzione'];
                        $this->azioniFO[$_POST[$this->nameForm . '_IdGridAzione']]['METODOAZIONE'] = $_POST[$this->nameForm . '_MetodoAzione'];
                        $this->azioniFO[$_POST[$this->nameForm . '_IdGridAzione']]['ERROREAZIONE'] = $_POST[$this->nameForm . '_ErroreAzione'];
                        $this->azioniFO[$_POST[$this->nameForm . '_IdGridAzione']]['OPERAZIONE'] = $AzioniFO->GetDescErroreAzione($_POST[$this->nameForm . '_ErroreAzione']);
                        $this->CaricaGriglia($this->gridAzioniFO, $this->azioniFO);
                        break;

                    case $this->nameForm . '_ConfermaCancellaAzione':
                        $this->azioniFO[$_POST[$this->gridAzioniFO]['gridParam']['selrow']]['CLASSEAZIONE'] = '';
                        $this->azioniFO[$_POST[$this->gridAzioniFO]['gridParam']['selrow']]['METODOAZIONE'] = '';
                        $this->CaricaGriglia($this->gridAzioniFO, $this->azioniFO);
                        break;

                    case $this->nameForm . '_ConfermaCancellaVaiPasso':
                        unset($this->iteVpaDettTab[$this->rowidAppoggio]);
                        $this->rowidAppoggio = null;
                        $this->decodificaITEQST(2);
                        break;

                    case $this->nameForm . '_destFromAnamed':
                        proRic::proRicAnamedMulti($this->nameForm);
                        break;
                    case $this->nameForm . '_destFromAnades':
                        praRic::praRicRuoli($this->nameForm);
                        break;
                    case $this->nameForm . '_ConfermaCancellaDest':
                        if (array_key_exists($this->rowidAppoggio, $this->destinatari) == true) {
                            if ($this->destinatari[$this->rowidAppoggio]['ROW_ID'] != 0) {
                                $delete_Info = 'Oggetto: Cancellazione destinatario' . $this->destinatari[$this->rowidAppoggio]['DENOMINAZIONE'];
                                if (!$this->deleteRecord($this->PRAM_DB, 'ITEDEST', $this->destinatari[$this->rowidAppoggio]['ROW_ID'], $delete_Info, "ROW_ID")) {
                                    Out::msgStop("Attenzione", "Errore in cancellazione del destinatario su ITEDEST");
                                    break;
                                }
                            }
                            unset($this->destinatari[$this->rowidAppoggio]);
                        }
                        $this->CaricaGriglia($this->gridDestinatari, $this->destinatari, "1", "100");
                        break;

                    case $this->nameForm . '_apriEspressioneRiservato':
                        $model = 'praCondizioni';
                        itaLib::openForm($model);
                        $praCondizioni = itaModel::getInstance($model);
                        $praCondizioni->setEvent('openform');
                        $praCondizioni->setReturnModel($this->nameForm);
                        $praCondizioni->setReturnEvent('returnEspressioneRiservato');
                        
                        $espressioneITEPAS = $_POST[$this->nameForm . '_ITEPAS']['ITEEXPRRISERVATO'];
                        if ($espressioneITEPAS && unserialize($espressioneITEPAS)) {
                            $praCondizioni->setArrayEspressioni(unserialize($espressioneITEPAS));
                        }

                        $praCondizioni->setCodiceProcedimento($_POST[$this->nameForm . '_ITEPAS']['ITECOD']);
                        $praCondizioni->setCodicePasso($_POST[$this->nameForm . '_ITEPAS']['ITEKEY']);
                        $praCondizioni->parseEvent();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ITEPAS[ITEDES]':
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $Descrizioni_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT DISTINCT ITEDES FROM ITEPAS WHERE ITEPUB = 1 AND " . $this->PRAM_DB->strLower('ITEDES') . " LIKE '%" . addslashes(strtolower(itaSuggest::getQuery())) . "%'", true);
                        foreach ($Descrizioni_tab as $Descrizioni_rec) {
                            itaSuggest::addSuggest($Descrizioni_rec['ITEDES']);
                        }
                        itaSuggest::sendSuggest();
                        break;
                    case $this->nameForm . '_ITEPAS[ITENOT]':
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $Note_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT DISTINCT ITENOT  FROM  ITEPAS WHERE ITEPUB = 1 AND " . $this->PRAM_DB->strLower('ITENOT') . " LIKE '%" . addslashes(strtolower(itaSuggest::getQuery())) . "%'", true);
                        foreach ($Note_tab as $Note_rec) {
                            itaSuggest::addSuggest($Note_rec['ITENOT']);
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;
            case "returnEditDiag":
                switch ($_POST['returnField']) {
                    case $this->nameForm . '_pdfTestoBase':
                        $this->currXHTML = $_POST['returnText'];
                        break;
                    case $this->nameForm . '_MailTestoBaseRICHIEDENTE':
                        $this->currMAILTemplate['BODY_RICHIEDENTE'] = $_POST['returnText'];
                        break;
                    case $this->nameForm . '_MailTestoBaseRESPONSABILE':
                        $this->currMAILTemplate['BODY_RESPONSABILE'] = $_POST['returnText'];
                        break;
                    case $this->nameForm . '_MailTestoBaseCOMUNICAZIONE':
                        $this->currMAILTemplate['BODY_COMUNICAZIONE'] = str_replace("\n", "", $_POST['returnText']);
                        break;
                }

                break;
            case "returnClassificazioneDocumenti":
                if ($_POST['ROW_ID'] == 'readonly') {
                    break;
                }
                $ClassificazioneDoc = $this->docLib->getDocIntegrativi($_POST['ROW_ID'], 'rowid', false);
                $path = $this->getpathClassificazioneDoc(0, $ClassificazioneDoc['ROW_ID_PADRE'], '');
                Out::valore($this->nameForm . '_ITEPAS[ROWID_DOC_CLASSIFICAZIONE]', $_POST['ROW_ID']);
                Out::valore($this->nameForm . '_ClassificazioneDocumenti', $path . $ClassificazioneDoc['OGGETTO']);
                break;
            case "returnEditDiagDist":
                $this->currXHTMLDis = $_POST['returnText'];
                break;
            case "returnEditDiagDescBox":
                $this->currDescBox = $_POST['returnText'];
                break;
            case "returnPraclt":
                $Praclt_rec = $this->praLib->GetPraclt($_POST["retKey"], 'rowid');
                if ($Praclt_rec) {
                    Out::valore($this->nameForm . '_ITEPAS[ITECLT]', $Praclt_rec['CLTCOD']);
                    Out::valore($this->nameForm . '_CLTDES', $Praclt_rec['CLTDES']);
                }
                break;
            case 'returnUnires':
                $Ananom_rec = $this->praLib->GetAnanom($_POST["retKey"], 'rowid');
                if ($Ananom_rec) {
                    $this->DecodResponsabile($Ananom_rec);
                }
                break;
            case 'returnAttivaExpr' :
                Out::valore($this->nameForm . '_AttivaEspressione', $this->praLib->DecodificaControllo($_POST['dati']['ITEATE']));
                Out::valore($this->nameForm . '_ITEPAS[ITEATE]', $_POST['dati']['ITEATE']);
                break;
            case 'returnObblExpr' :
                Out::valore($this->nameForm . '_ObblEspressione', $this->praLib->DecodificaControllo($_POST['dati']['ITEATE']));
                Out::valore($this->nameForm . '_ITEPAS[ITEOBE]', $_POST['dati']['ITEATE']);
                break;
            case 'returnObblExprValidita' :

//                switch ($_POST[$this->nameForm . '_Condizione']) {
//                    case 'uguale':
//                        $simbolo = "==";
//                        break;
//                    case 'diverso':
//                        $simbolo = "!=";
//                        break;
//                    case 'maggiore':
//                        $simbolo = ">";
//                        break;
//                    case 'minore':
//                        $simbolo = "<";
//                        break;
//                    case 'maggiore-uguale':
//                        $simbolo = ">=";
//                        break;
//                    case 'minore-uguale':
//                        $simbolo = "<=";
//                }
//                $arrExpr = array();
                if ($_POST['dati']['ITEATE']) {
//                    $arrExpr = unserialize($_POST[$this->nameForm . '_ctrSerializzato']);
//                    $arrExpr[] = array(
//                        "CAMPO" => $_POST[$this->nameForm . '_Campi'],
//                        "CONDIZIONE" => $simbolo,
//                        "VALORE" => $_POST[$this->nameForm . '_ValoreCtr'],
//                    );
//                    Out::clearFields($this->nameForm, $this->nameForm . "_divControllo");
                    //
                    //$this->praEspressioni->CaricaEspressione($arrExpr, $_POST[$this->nameForm . '_AzioneCtr']);
                    $this->praEspressioni->CaricaEspressione($_POST['dati']['ITEATE'], $_POST['dati']['ROWID']);
                    $this->CaricaGriglia($this->gridEspressioni, $this->praEspressioni->GetGriglia());
                }
//                Out::hide($this->nameForm . "_divControllo");
                break;
            case 'returnIndiceITEWRD':
                if ($ditta == '')
                    $ditta = App::$utente->getKey('ditta');
                $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/';
                $matriceSelezionati = $this->GetFileList($destinazione);

                //$ext = pathinfo($destinazione . $matriceSelezionati[$_POST['retKey']]['FILENAME'], PATHINFO_EXTENSION);
                $ext = pathinfo($destinazione . $matriceSelezionati[$_POST['rowData']['rowid']]['FILENAME'], PATHINFO_EXTENSION);

                $errore = false;
                foreach ($this->ext as $key => $est) {
                    if ($ext != $est['EXT']) {
                        $errore = true;
                        break;
                    }
                }
                if ($errore != false) {
                    Out::msgStop('ERRORE!!', "L'estensione del file scelto non è tra quelle gestite.<br>Gestire l'estensione o sciegliere un altro file");
                    break;
                }
                //Out::valore($this->nameForm . '_ITEPAS[ITEWRD]', $matriceSelezionati[$_POST['retKey']]['FILENAME']);
                Out::valore($this->nameForm . '_ITEPAS[ITEWRD]', $matriceSelezionati[$_POST['rowData']['rowid']]['FILENAME']);
                break;
            case 'returnIndiceITEIMG':
                if ($ditta == '')
                    $ditta = App::$utente->getKey('ditta');
                $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/immagini/';
                $matriceSelezionati = $this->GetFileList($destinazione);
                Out::valore($this->nameForm . '_ITEPAS[ITEIMG]', $matriceSelezionati[$_POST['retKey']]['FILENAME']);
                //                if ($ditta == '') $ditta = App::$utente->getKey('ditta');
                //                $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/immagini/';
                //                $matriceSelezionati=$this->GetFileList($destinazione);
                //                Out::valore($this->nameForm.'_ITEPAS[ITEIMG]',$matriceSelezionati[$_POST['retKey']]['FILENAME']);
                break;
            case 'returnUploadDocEsterno':
                $this->AllegaTesto();
                break;
            case 'returnUploadFileEsterno':
                $this->AllegaFile();
                break;
            case 'returnItepas':
                switch ($_POST['retid']) {
                    case '1':
                        $this->DecodVaialpasso($_POST["retKey"], 'rowid');
                        break;
                    case '2':
                        $this->DecodVaialpassoNo($_POST["retKey"], 'rowid');
                        break;
                    case '3':
                        $this->DecodCtrPasso($_POST["retKey"], 'rowid');
                        break;
                    case '4':
                        $this->DecodDuplicaPasso($_POST["retKey"], 'rowid');
                        break;
                    case '5':
                        $this->DecodDalPasso($_POST["retKey"], 'rowid');
                        break;
                    case '6':
                        $this->DecodAlPasso($_POST["retKey"], 'rowid');
                        break;
                    case '7':
                        $this->DecodUploadPrec($_POST["retKey"], 'rowid');
                        break;
                    case '8':
                        $this->DecodPassAntecedente($_POST["retKey"], 'rowid');
                        break;
                    default:
                        break;
                }
                break;
            case 'returnanamed':
                $this->DecodAnamedCom($_POST['retKey'], 'rowid');
                break;
            //            case 'returnEstensioni':
            //                $this->ext = $_POST['ext'];
            //                $Itepas_rec = $this->praLib->GetItepas($_POST['itekey'], 'itekey');
            //                $Itepas_rec['ITEEXT'] = serialize($this->ext);
            //                $update_Info = 'itekey ' . $_POST[$this->nameForm.'_ITEPAS']['ITEKEY'];
            //                $this->updateRecord($this->PRAM_DB, 'ITEPAS', $Itepas_rec, $update_Info);
            //                break;
            case'returnAnastp';
                $Anastp_rec = $this->praLib->GetAnastp($_POST['retKey'], 'rowid');
                switch ($_POST['retid']) {
                    case '1':
                        Out::valore($this->nameForm . '_ITEPAS[ITESTAP]', $Anastp_rec['ROWID']);
                        Out::valore($this->nameForm . '_Stato1', $Anastp_rec['STPDES']);
                        break;
                    case '2':
                        Out::valore($this->nameForm . '_ITEPAS[ITESTCH]', $Anastp_rec['ROWID']);
                        Out::valore($this->nameForm . '_Stato2', $Anastp_rec['STPDES']);
                        break;

                    case '3':
                        Out::valore($this->nameForm . '_ITEPAS[ITEDEFSTATO]', $Anastp_rec['ROWID']);
                        Out::valore($this->nameForm . '_DEFSTATODES', $Anastp_rec['STPDES']);
                        break;
                }

                break;
            case "returnAnapra";
                switch ($_POST['retid']) {
                    case "PASSOTEMPLATE":
                        $Anapra_rec = $this->praLib->GetAnapra($_POST['rowData']['ROWID'], "rowid");
                        //$this->pranumSel = $Anapra_rec['PRANUM'];
                        $Itepas_tab = $this->praLib->GetItepas($Anapra_rec['PRANUM'], "codice", true);
                        if ($Itepas_tab) {
                            praRic::praPassiSelezionati($Itepas_tab, $this->nameForm, "", "Scegli il passo template", "ITEPAS", "false");
                        } else {
                            Out::msgInfo("Passo Template", "Passi del procedimento <b>" . $Anapra_rec['PRANUM'] . " - " . $Anapra_rec['PRADES__1'] . "</b> non trovati.");
                        }
                        break;
                    default:
                        $Anapra_rec = $this->praLib->GetAnapra($_POST['rowData']['ROWID'], 'rowid');
                        Out::valore($this->nameForm . '_ITEPAS[ITEPROC]', $Anapra_rec['PRANUM']);
                        Out::valore($this->nameForm . '_DesProcedimento', $Anapra_rec['PRADES__1']);
                        break;
                }
                break;
            case "returnPassiSel";
                $this->DecodTemplateKey($_POST['rowData']['ITEKEY']);
                break;
            case "returnDocumenti";
                $this->decodDocumenti($_POST['retKey'], 'rowid');
                break;
            case 'returnVariabili':
                Out::valore($this->nameForm . '_ITEPAS[ITENRA]', $_POST['rowData']['markupkey']);
                break;
            case 'returnVariabiliCampi':
                Out::valore($this->nameForm . '_Campi', $_POST['rowData']['markupkey']);
                break;
            case 'returnVariabiliUpload':
                Out::codice("$('#" . $this->nameForm . '_NomeFileUpload' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case 'returnVariabiliITEURL':
                Out::codice("$('#" . $this->nameForm . '_iteurl' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case "returnAnahelp";
                $Anahelp_rec = $this->praLib->GetAnahelp($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_ITEPAS[ITEHELP]', $Anahelp_rec['HELPCOD']);
                break;
            case "returnItepasAntecedenti":
                $this->Dettaglio($_POST['retKey'], "rowid");
                break;
            case 'returnanamedMulti':
                $arrSogg = explode(",", $_POST['retKey']);
                foreach ($arrSogg as $key => $rowid) {
                    $anamed_rec = $this->proLib->GetAnamed($rowid, "rowid");
                    if ($anamed_rec) {
                        $idx = array_search($anamed_rec['MEDCOD'], array_column($this->destinatari, "CODICE"));
                        if ($idx !== false) {
                            Out::msgInfo("Caricamento destinatari", "Destinatario già presente.");
                            break;
                        }
                        $sogg = array();
                        $sogg['ROW_ID'] = 0;
                        $sogg['CODICE'] = $anamed_rec['MEDCOD'];
                        $sogg['RUOLO'] = "";
                        $sogg['DENOMINAZIONE'] = "<span style = \"color:orange;\">" . $anamed_rec['MEDNOM'] . "</span>";
                        $this->destinatari[] = $sogg;
                    }
                }
                $this->CaricaGriglia($this->gridDestinatari, $this->destinatari, "1", "100");
                break;
            case 'returnAnaruo':
                $anaruo_rec = $this->praLib->GetAnaruo($_POST['retKey'], "rowid");
                //
                $idx = array_search($anaruo_rec['RUOCOD'], array_column($this->destinatari, "RUOLO"));
                if ($idx !== false) {
                    Out::msgInfo("Caricamento destinatari", "Destinatario già presente.");
                    break;
                }

                $sogg = array();
                $sogg['ROW_ID'] = 0;
                $sogg['RUOLO'] = $anaruo_rec['RUOCOD'];
                $sogg['CODICE'] = "";
                $sogg['DENOMINAZIONE'] = "<span style = \"color:orange;\">" . $anaruo_rec['RUODES'] . "</span>";
                $this->destinatari[] = $sogg;
                $this->CaricaGriglia($this->gridDestinatari, $this->destinatari, "1", "100");
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ITEPAS[ITEPUB]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEPUB'] == 1) {
                            Out::show($this->nameForm . '_divSuap');
                            Out::hide($this->nameForm . '_divGenerali');
                            Out::hide($this->nameForm . '_ITEPAS[ITECOM]');
                            Out::hide($this->nameForm . '_ITEPAS[ITECOM]_lbl');
                        } else {
                            Out::show($this->nameForm . '_divSuap');
                            Out::hide($this->nameForm . '_divGenerali');
                            Out::show($this->nameForm . '_ITEPAS[ITECOM]');
                            Out::show($this->nameForm . '_ITEPAS[ITECOM]_lbl');
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITEOBL]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEOBL'] == 1) {
                            Out::show($this->nameForm . '_ObblEspressione_field');
                            Out::show($this->nameForm . '_ApriObblExpr');
                        } else {
                            Out::hide($this->nameForm . '_ObblEspressione_field');
                            Out::hide($this->nameForm . '_ApriObblExpr');
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITECOM]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITECOM'] == 1) {
                            Out::hide($this->nameForm . '_divSuap');
                            Out::show($this->nameForm . '_divGenerali');
                            Out::hide($this->nameForm . '_ITEPAS[ITEPUB]');
                            Out::hide($this->nameForm . '_ITEPAS[ITEPUB]_lbl');
                        } else {
                            Out::show($this->nameForm . '_divSuap');
                            Out::hide($this->nameForm . '_divGenerali');
                            Out::show($this->nameForm . '_ITEPAS[ITEPUB]');
                            Out::show($this->nameForm . '_ITEPAS[ITEPUB]_lbl');
                        }
                        break;

                    case $this->nameForm . '_SaltaPasso':
                        $this->decodificaITEQST(0);
                        break;

                    case $this->nameForm . '_PassoDomanda':
                        $this->decodificaITEQST(1);
                        break;

                    case $this->nameForm . '_SaltaMultiplo':
                        $this->decodificaITEQST(2);
                        break;

                    case $this->nameForm . '_ITEPAS[ITEDRR]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEDRR'] == 1) {
                            Out::show($this->nameForm . "_rapportoConfig");
                        } else {
                            Out::hide($this->nameForm . "_rapportoConfig");
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITEUPL]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEUPL'] == 1) {
                            Out::show($this->divCtrCampi);
                            Out::show($this->DivUpload);
                            if ($_POST[$this->nameForm . '_ITEPAS']['ITEFILE'] == 1) {
                                Out::show($this->nameForm . "_Precompila");
                                Out::show($this->nameForm . "_WRDPRECOMPILED_field");
                            }
                        } else {
                            Out::hide($this->divCtrCampi);
                            Out::hide($this->DivUpload);
                            Out::hide($this->nameForm . "_Precompila");
                            Out::hide($this->nameForm . "_WRDPRECOMPILED_field");
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITEFILE]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEFILE'] == 1) {
                            Out::show($this->nameForm . "_Precompila");
                            Out::show($this->nameForm . "_WRDPRECOMPILED_field");
                        } else {
                            Out::hide($this->nameForm . "_Precompila");
                            Out::hide($this->nameForm . "_WRDPRECOMPILED_field");
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITEMLT]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEMLT'] == 1) {
                            Out::show($this->divCtrCampi);
                            Out::show($this->DivUpload);
                        } else {
                            Out::hide($this->divCtrCampi);
                            Out::hide($this->DivUpload);
                        }
                        break;
                    case $this->nameForm . '_accorpaPDFUpload':
                    case $this->nameForm . '_accorpaPDFTestoBase':
                        $Iteidr = intval($_POST[$this->nameForm . '_accorpaPDFUpload'] || $_POST[$this->nameForm . '_accorpaPDFTestoBase']);
                        if ($Iteidr == 0) {
                            Out::show($this->nameForm . "_ITEPAS[ITEIFC]");
                            Out::show($this->nameForm . "_ITEPAS[ITEIFC]_lbl");
                        } else {
                            Out::hide($this->nameForm . "_ITEPAS[ITEIFC]");
                            Out::hide($this->nameForm . "_ITEPAS[ITEIFC]_lbl");
                            Out::hide($this->nameForm . "_ITEPAS[ITETAL]");
                            Out::hide($this->nameForm . "_ITEPAS[ITETAL]_butt");
                            Out::hide($this->nameForm . "_ITEPAS[ITETAL]_lbl");
                            Out::hide($this->nameForm . "_SvuotaTipi");
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITEIFC]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEIFC'] == 0 || $_POST[$this->nameForm . '_ITEPAS']['ITEIFC'] == 2) {
                            Out::hide($this->nameForm . "_ITEPAS[ITETAL]");
                            Out::hide($this->nameForm . "_ITEPAS[ITETAL]_butt");
                            Out::hide($this->nameForm . "_ITEPAS[ITETAL]_lbl");
                            Out::hide($this->nameForm . "_SvuotaTipi");
                        } else {
                            Out::show($this->nameForm . "_ITEPAS[ITETAL]");
                            Out::show($this->nameForm . "_ITEPAS[ITETAL]_butt");
                            Out::show($this->nameForm . "_ITEPAS[ITETAL]_lbl");
                            Out::show($this->nameForm . "_SvuotaTipi");
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITEIRE]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEIRE'] == 1) {
                            Out::show($this->divMail);
                        } else {
                            Out::hide($this->divMail);
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITERIF]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITERIF'] == 0) {
                            Out::hide($this->nameForm . '_divRiferimento');
                        } else {
                            Out::show($this->nameForm . '_divRiferimento');
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITEMLT]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEMLT'] == 1) {
                            Out::show($this->divCtrCampi);
                        } else {
                            Out::hide($this->divCtrCampi);
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITEDAT]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEDAT'] == 1) {
                            Out::show($this->nameForm . "_DivRaccolta");
                            Out::show($this->nameForm . "_divPassoTemplate");
                        } else {
                            Out::hide($this->nameForm . "_DivRaccolta");
                            Out::hide($this->nameForm . "_divPassoTemplate");
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITEDIS]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEDIS'] == 1) {
                            Out::show($this->nameForm . "_DivDistinta");
                        } else {
                            Out::hide($this->nameForm . "_DivDistinta");
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITERDM]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITERDM'] == 1) {
                            Out::show($this->nameForm . "_ITEPAS[ITENRA]_field");
                            Out::show($this->nameForm . "_ITEPAS[ITECUSTOMTML]_field");
                            Out::show($this->nameForm . "_divPassoTemplate");
                        } else {
                            Out::hide($this->nameForm . "_ITEPAS[ITENRA]_field");
                            Out::hide($this->nameForm . "_ITEPAS[ITECUSTOMTML]_field");
                            Out::hide($this->nameForm . "_divPassoTemplate");
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITECTB]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITECTB'] == 1) {
                            Out::show($this->nameForm . "_editTestoBase");
                        } else {
                            Out::hide($this->nameForm . "_editTestoBase");
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITEQCLA]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEQCLA'] == 1) {
                            Out::show($this->nameForm . "_Classificazioni");
                        } else {
                            Out::hide($this->nameForm . "_Classificazioni");
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITEQALLE]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEQALLE']) {
                            Out::show($this->nameForm . "_ITEPAS[ITEQCLA]_field");
                            Out::show($this->nameForm . "_ITEPAS[ITEQDEST]_field");
                            Out::show($this->nameForm . "_ITEPAS[ITEQNOTE]_field");
                            Out::show($this->nameForm . "_NomeFileUpload_field");
                            if ($_POST[$this->nameForm . '_ITEPAS']['ITEQCLA'] == 1) {
                                Out::show($this->nameForm . "_Classificazioni");
                            } else {
                                Out::hide($this->nameForm . "_Classificazioni");
                            }
                        } else {
                            Out::hide($this->nameForm . "_ITEPAS[ITEQCLA]_field");
                            Out::hide($this->nameForm . "_ITEPAS[ITEQDEST]_field");
                            Out::hide($this->nameForm . "_ITEPAS[ITEQNOTE]_field");
                            Out::hide($this->nameForm . "_NomeFileUpload_field");
                            Out::hide($this->nameForm . "_Classificazioni");
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITEDOW]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEDOW'] == 1) {
                            Out::show($this->nameForm . "_divPassoTemplate");
                        } else {
                            Out::hide($this->nameForm . "_divPassoTemplate");
                        }
                        break;

                    case $this->nameForm . '_ITEPAS[ITEFLRISERVATO]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEFLRISERVATO'] == praLibRiservato::RISERVATO_DA_ESPRESSIONE) {
                            Out::show($this->nameForm . '_divRiservatoEspressione');
                        } else {
                            Out::hide($this->nameForm . '_divRiservatoEspressione');
                        }
                        break;
                }
                break;


            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ITEPAS[ITEDEFSTATO]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEDEFSTATO']) {
                            $codice = $_POST[$this->nameForm . '_ITEPAS']['ITEDEFSTATO'];
                            $anastp_rec = $this->praLib->GetAnastp($codice);
                            if ($anastp_rec) {
                                Out::valore($this->nameForm . '_ITEPAS[ITEDEFSTATO]', $anastp_rec['ROWID']);
                                Out::valore($this->nameForm . '_DEFSTATODES', $anastp_rec['STPDES']);
                            } else {
                                Out::valore($this->nameForm . '_DEFSTATODES', "");
                            }
                        } else {
                            Out::valore($this->nameForm . '_DEFSTATODES', "");
                        }
                        break;

                    case $this->nameForm . '_estensione':
                        if ($_POST[$this->nameForm . '_estensione'] != '') {
                            $posi = strpos($_POST[$this->nameForm . '_ITEPAS']['ITEEXT'], '|' . strtolower($_POST[$this->nameForm . '_estensione']) . '|');
                            if ($posi !== false) {
                                Out::valore($this->nameForm . '_ITEPAS[ITEEXT]', str_replace('|' . strtolower($_POST[$this->nameForm . '_estensione']) . '|', '', $_POST[$this->nameForm . '_ITEPAS']['ITEEXT']));
                            } else {
                                Out::valore($this->nameForm . '_ITEPAS[ITEEXT]', $_POST[$this->nameForm . '_ITEPAS']['ITEEXT'] . '|' . strtolower($_POST[$this->nameForm . '_estensione']) . '|');
                            }
                            Out::valore($this->nameForm . '_estensione', '');
                            Out::setFocus('', $this->nameForm . '_estensione');
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITECLT]':
                        $codice = $_POST[$this->nameForm . '_ITEPAS']['ITECLT'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Praclt_rec = $this->praLib->GetPraclt($codice);
                            Out::valore($this->nameForm . '_ITEPAS[ITECLT]', $Praclt_rec['CLTCOD']);
                            Out::valore($this->nameForm . '_CLTDES', $Praclt_rec['CLTDES']);
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITERES]':
                        $codice = $_POST[$this->nameForm . '_ITEPAS']['ITERES'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Ananom_rec = $this->praLib->GetAnanom($codice);
                            if ($Ananom_rec) {
                                $this->DecodResponsabile($Ananom_rec);
                            } else {
                                Out::valore($this->nameForm . '_ITEPAS[ITESET]', "");
                                Out::valore($this->nameForm . '_ITEPAS[ITESER]', "");
                                Out::valore($this->nameForm . '_ITEPAS[ITEOPE]', "");
                                Out::valore($this->nameForm . '_SETTORE', "");
                                Out::valore($this->nameForm . '_SERVIZIO', "");
                                Out::valore($this->nameForm . '_UNITA', "");
                                Out::valore($this->nameForm . '_Nome', "");
                            }
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITECDE]':
                        $codice = $_POST[$this->nameForm . '_ITEPAS']['ITECDE'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $Anamed_rec = $this->proLib->GetAnamed($codice);
                            $this->DecodAnamedCom($Anamed_rec['ROWID'], 'rowid');
                        } else {
                            Out::valore($this->nameForm . '_DESTINATARIO', "");
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITESTCH]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITESTCH']) {
                            $codice = $_POST[$this->nameForm . '_ITEPAS']['ITESTCH'];
                            $Anastp_rec = $this->praLib->GetAnastp($codice);
                            if ($Anastp_rec) {
                                Out::valore($this->nameForm . '_ITEPAS[ITESTCH]', $Anastp_rec['ROWID']);
                                Out::valore($this->nameForm . '_Stato2', $Anastp_rec['STPDES']);
                            } else {
                                Out::valore($this->nameForm . '_Stato2', "");
                            }
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITEPROC]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITEPROC']) {
                            $codice = $_POST[$this->nameForm . '_ITEPAS']['ITEPROC'];
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anapra_rec = $this->praLib->GetAnapra($codice);
                            if ($Anapra_rec) {
                                Out::valore($this->nameForm . '_ITEPAS[ITEPROC]', $Anapra_rec['PRANUM']);
                                Out::valore($this->nameForm . '_DesProcedimento', $Anapra_rec['PRADES__1']);
                            } else {
                                Out::valore($this->nameForm . '_DesProcedimento', "");
                            }
                        }
                        break;
                    case $this->nameForm . '_ITEPAS[ITESTAP]':
                        if ($_POST[$this->nameForm . '_ITEPAS']['ITESTAP']) {
                            $codice = $_POST[$this->nameForm . '_ITEPAS']['ITESTAP'];
                            $Anastp_rec = $this->praLib->GetAnastp($codice);
                            if ($Anastp_rec) {
                                Out::valore($this->nameForm . '_ITEPAS[ITESTAP]', $Anastp_rec['ROWID']);
                                Out::valore($this->nameForm . '_Stato1', $Anastp_rec['STPDES']);
                            } else {
                                Out::valore($this->nameForm . '_Stato1', "");
                            }
                        }
                        break;
                }
                break;


            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAzioniFO:
                        $praAzioneFO = new praAzioniFO();
                        $sel1 = $sel2 = $sel3 = false;
                        $Praazioni_rec = $this->azioniFO[$_POST['rowid']];
                        switch ($Praazioni_rec['ERROREAZIONE']) {
                            case "CONT":
                                $sel1 = true;
                                break;
                            case "ERR":
                                $sel2 = true;
                                break;
                            case "WARN":
                                $sel3 = true;
                                break;
                            case "INV":
                                $sel4 = true;
                                break;
                        }
                        Out::msgInput(
                                "Compila i seguenti campi", array(
                            array(
                                'id' => $this->nameForm . '_IdGridAzione',
                                'name' => $this->nameForm . '_IdGridAzione',
                                'value' => $_POST['rowid'],
                                'type' => 'text',
                                'style' => "display:none;",
                                'width' => '50',
                                'size' => '35'),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Classe'),
                                'id' => $this->nameForm . '_ClasseAzione',
                                'name' => $this->nameForm . '_ClasseAzione',
                                'value' => $Praazioni_rec['CLASSEAZIONE'],
                                'type' => 'text',
                                'width' => '50',
                                'size' => '35'),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Metodo'),
                                'id' => $this->nameForm . '_MetodoAzione',
                                'name' => $this->nameForm . '_MetodoAzione',
                                'value' => $Praazioni_rec['METODOAZIONE'],
                                'type' => 'text',
                                'width' => '50',
                                'size' => '35'),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Operazione dopo Errore'),
                                'id' => $this->nameForm . '_ErroreAzione',
                                'name' => $this->nameForm . '_ErroreAzione',
                                'value' => $Praazioni_rec['ERROREAZIONE'],
                                'type' => 'select',
                                'width' => '50',
                                'size' => '1',
                                'options' => array(
                                    array("", ""),
                                    array("CONT", $praAzioneFO->GetDescErroreAzione('CONT'), $sel1),
                                    array("ERR", $praAzioneFO->GetDescErroreAzione('ERR'), $sel2),
                                    array("WARN", $praAzioneFO->GetDescErroreAzione('WARN'), $sel3),
                                    array("INV", $praAzioneFO->GetDescErroreAzione('INV'), $sel4)
                                )
                            ),
                                ), array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaDatiAzione', 'model' => $this->nameForm, 'shortCut' => "f5")
                                ), $this->nameForm
                        );
                        break;

                    case $this->gridEspressioni:
                        $espressioni = $this->praEspressioni->GetEspressioni();
                        $rowid = $_POST['rowid'];
                        $model = 'praPassoProcExpr';
                        $itekey = $_POST[$this->nameForm . '_ITEPAS']['ITEKEY'];
                        $Itepas_rec = $this->praLib->GetItepas($itekey, 'itekey');
                        $_POST = array();
                        $_POST['dati'] = array(
                            'TITOLO' => 'Espressione per validare il passo:',
                            'ITEKEY' => $itekey,
                            'ITECOD' => $Itepas_rec['ITECOD'],
                            'ITEATE' => $espressioni[$rowid]['ESPRESSIONE'],
                            'TABELLA' => "ITEDAG",
                            'ROWID' => $rowid
                        );
                        $_POST['event'] = 'openform';
                        $_POST['perms'] = $this->perms;
                        $_POST['tipo'] = "ITECONTROLLI";
                        $_POST['ITEKEY'] = $itekey;
                        $_POST['ITECOD'] = $Itepas_rec['ITECOD'];
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnObblExprValidita';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case $this->nameForm . '_gridVaiPasso':
                        /* @var $praVpaDett praVpaDett */
                        itaLib::openDialog('praVpaDett');
                        $praVpaDett = itaModel::getInstance('praVpaDett');
                        $praVpaDett->setReturnId($_POST['rowid']);
                        $praVpaDett->setReturnModel($this->nameForm);
                        $praVpaDett->setITECOD($this->currPranum);
                        $praVpaDett->setITEKEY($_POST[$this->nameForm . '_ITEPAS']['ITEKEY']);
                        $praVpaDett->openDettaglio($this->iteVpaDettTab[$_POST['rowid']]);
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridEspressioni:
//                        Out::show($this->nameForm . "_divControllo");
//                        $datiAgg = $this->praLib->GetItedag($_POST[$this->nameForm . "_ITEPAS"]['ITEKEY'], "itekey", true);
//                        Out::clearFields($this->nameForm, $this->nameForm . "_divControllo");
                        $model = 'praPassoProcExpr';
                        $itekey = $_POST[$this->nameForm . '_ITEPAS']['ITEKEY'];
                        $Itepas_rec = $this->praLib->GetItepas($itekey, 'itekey');
                        $_POST = array();
                        $_POST['dati'] = array(
                            'TITOLO' => 'Espressione per validare il passo:',
                            'ITEKEY' => $itekey,
                            'ITECOD' => $Itepas_rec['ITECOD'],
                            'TABELLA' => "ITEDAG"
                        );
                        $_POST['event'] = 'openform';
                        $_POST['perms'] = $this->perms;
                        $_POST['tipo'] = "ITECONTROLLI";
                        $_POST['ITEKEY'] = $itekey;
                        $_POST['ITECOD'] = $Itepas_rec['ITECOD'];
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnObblExprValidita';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case $this->nameForm . '_gridVaiPasso':
                        /* @var $praVpaDett praVpaDett */
                        itaLib::openDialog('praVpaDett');
                        $praVpaDett = itaModel::getInstance('praVpaDett');
                        $praVpaDett->setITECOD($this->currPranum);
                        $praVpaDett->setITEKEY($_POST[$this->nameForm . '_ITEPAS']['ITEKEY']);
                        $praVpaDett->setReturnModel($this->nameForm);
                        $praVpaDett->setEvent('openform');
                        $praVpaDett->parseEvent();
                        break;
                    case $this->gridDestinatari:
                        Out::msgQuestion("Caricamento Destinatari", "Scegli da dove caricare i destinatari", array(
                            'Anagrafica Mittenti/Destinatari' => array('id' => $this->nameForm . '_destFromAnamed', 'model' => $this->nameForm),
                            'Anagrafica Ruoli Soggetto' => array('id' => $this->nameForm . '_destFromAnades', 'model' => $this->nameForm)
                                )
                        );
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridEspressioni:
                        Out::msgQuestion("ATTENZIONE!", "L'operazione è irreversibile. <br>Desidere cancellare la seguente espressione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaEspressione', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaEspressione', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;

                    case $this->gridAzioniFO:
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione dell'azione?", array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaAzione', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaAzione', 'model' => $this->nameForm)
                                )
                        );
                        break;

                    case $this->nameForm . '_gridVaiPasso':
                        Out::msgQuestion('Cancellazione', 'Confermi la cancellazione della regola Vai passo?', array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaVaiPasso', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaVaiPasso', 'model' => $this->nameForm)
                        ));
                        break;
                    case $this->gridDestinatari:
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione del destinatario?", array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaDest', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaDest', 'model' => $this->nameForm)
                                )
                        );
                        break;
                }
                $this->rowidAppoggio = $_POST['rowid'];
                break;

            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->gridEspressioni:
                        $this->praEspressioni->SetGridValue($_POST['rowid'], $_POST['cellname'], $_POST['value']);
                        $this->CaricaGriglia($this->gridEspressioni, $this->praEspressioni->GetGriglia());
                        break;
                }
                break;
            case 'printTableToHTML':
                switch ($_POST['id']) {
                    
                }
                break;
            case 'returnTipiAllegati':
                Out::valore($this->nameForm . '_ITEPAS[ITETAL]', $_POST['rowData']['valore']);
                if (strpos($_POST['rowData']['valore'], "99") !== false) {
                    Out::attributo($this->nameForm . '_ITEPAS[ITETAL]', 'readonly', '1');
                } else {
                    Out::attributo($this->nameForm . '_ITEPAS[ITETAL]', 'readonly', '0');
                }
                break;

            case 'returnPraVpaDett':
                if (isset($_POST['returnId'])) {
                    $this->iteVpaDettTab[$_POST['returnId']] = $_POST['returnVpaDett'];
                } else {
                    $this->iteVpaDettTab[] = $_POST['returnVpaDett'];
                }

                $this->decodificaITEQST(2);
                break;

            case 'returnEspressioneRiservato' :
                Out::valore($this->nameForm . '_espressioneRiservato', $this->praLib->DecodificaControllo($_POST['returnCondizione']));
                Out::valore($this->nameForm . '_ITEPAS[ITEEXPRRISERVATO]', $_POST['returnCondizione']);
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_allegati');
        App::$utente->removeKey($this->nameForm . '_currPranum');
        App::$utente->removeKey($this->nameForm . '_currMAILTemplate');
        App::$utente->removeKey($this->nameForm . '_currXHTML');
        App::$utente->removeKey($this->nameForm . '_currXHTMLDis');
        App::$utente->removeKey($this->nameForm . '_currDescBox');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnMethod');
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_datiAppoggio');
        App::$utente->removeKey($this->nameForm . '_close');
        App::$utente->removeKey($this->nameForm . '_page');
        App::$utente->removeKey($this->nameForm . '_ext');
        App::$utente->removeKey($this->nameForm . '_selRow');
        App::$utente->removeKey($this->nameForm . '_enteMaster');
        App::$utente->removeKey($this->nameForm . '_praEspressioni');
        App::$utente->removeKey($this->nameForm . '_praProcDatiAggiuntiviFormname');
        App::$utente->removeKey($this->nameForm . '_iteVpaDettTab');
        App::$utente->removeKey($this->nameForm . '_destinatari');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        $_POST = array();
        $_POST['event'] = $this->returnMethod;
        $_POST['model'] = $this->returnModel;
        $_POST['page'] = $this->page;
        $_POST['selRow'] = $this->selRow;
        $phpURL = App::getConf('modelBackEnd.php');
        $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
        include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
        $returnModel = $this->returnModel;
        $returnModel();
        if ($close)
            $this->close();
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divGes);
        $this->azioniFO = array();
    }

    public function Dettaglio($rowid, $tipo = 'codice', $Itepas_rec = array()) {
        Out::tabSelect($this->nameForm . '_tabProcedimento', $this->nameForm . '_paneDati');
        if ($_POST['ricDatAgg'] == true) {
            Out::tabSelect($this->nameForm . '_tabProcedimento', $this->nameForm . '_paneDatiAggiuntivi');
        }
        $duplica = false;
        if (!$Itepas_rec) {
            $Itepas_rec = $this->praLib->GetItepas($rowid, $tipo);
        } else {
            $duplica = true;
        }
        if ($Itepas_rec['ITEMETA']) {
            $metadata = unserialize($Itepas_rec['ITEMETA']);
        }
        if (isset($metadata['TESTOBASEXHTML'])) {
            $this->currXHTML = $metadata['TESTOBASEXHTML'];
        }
        if (isset($metadata['TESTOBASEMAIL'])) {
            $this->currMAILTemplate = $metadata['TESTOBASEMAIL'];
        }
        if (isset($metadata['TESTOBASEDISTINTA'])) {
            $this->currXHTMLDis = $metadata['TESTOBASEDISTINTA'];
        }
        if ($Itepas_rec['ITEHTML']) {
            $this->currDescBox = $Itepas_rec['ITEHTML'];
        }
        if (isset($metadata['TEMPLATENOMEUPLOAD'])) {
            Out::valore($this->nameForm . '_NomeFileUpload', $metadata['TEMPLATENOMEUPLOAD']);
        }
        if (isset($metadata['CODICECLASSIFICAZIONE'])) {
            Out::valore($this->nameForm . '_Classificazioni', $metadata['CODICECLASSIFICAZIONE']);
        }
        $this->currPranum = $Itepas_rec['ITECOD'];
        $this->praEspressioni = praEspressioni::getInstance($this->praLib, $Itepas_rec['ITEKEY']);
        $Ananom_rec = $this->praLib->GetAnanom($Itepas_rec['ITERES']);
        $open_Info = 'Oggetto: ' . $Itepas_rec['ITECOD'] . ' - ' . $Itepas_rec['ITESEQ'];
        $this->openRecord($this->PRAM_DB, 'ITEPAS', $open_Info);
        Out::valori($Itepas_rec, $this->nameForm . '_ITEPAS');
        Out::valore($this->nameForm . '_accorpaPDFUpload', $Itepas_rec['ITEIDR']);
        Out::valore($this->nameForm . '_accorpaPDFTestoBase', $Itepas_rec['ITEIDR']);
        Out::valore($this->nameForm . '_accorpaPDFTestoBaseDis', $Itepas_rec['ITEIDR']);
        Out::valore($this->nameForm . '_uploadAutomatico', $Itepas_rec['ITEFILE']);
        Out::valore($this->nameForm . '_iteurl', $Itepas_rec['ITEURL']);
        if ($Itepas_rec['ITEOBL'] == 1) {
            Out::show($this->nameForm . '_ObblEspressione_field');
            Out::show($this->nameForm . '_ApriObblExpr');
        } else {
            Out::hide($this->nameForm . '_ObblEspressione_field');
            Out::hide($this->nameForm . '_ApriObblExpr');
        }
        $this->DecodResponsabile($Ananom_rec);
        $this->DecodAnamedCom($Itepas_rec['ITECDE']);
        $this->decodDocumenti($Itepas_rec['ITETBA']);

        $Praclt_rec = $this->praLib->GetPraclt($Itepas_rec['ITECLT']);
        Out::valore($this->nameForm . '_CLTDES', $Praclt_rec['CLTDES']);
        if ($duplica == false) {
            if ($Itepas_rec['ITEVPA'] != '')
                $this->DecodVaialpasso($Itepas_rec['ITEVPA']);
            if ($Itepas_rec['ITEVPN'] != '')
                $this->DecodVaialpassoNo($Itepas_rec['ITEVPN']);
            if ($Itepas_rec['ITECTP'] != '')
                $this->DecodCtrPasso($Itepas_rec['ITECTP']);
            if ($Itepas_rec['ITEDWP'] != '')
                $this->DecodUploadPrec($Itepas_rec['ITEDWP']);
            if ($Itepas_rec['TEMPLATEKEY'] != '')
                $this->DecodTemplateKey($Itepas_rec['TEMPLATEKEY']);

            /* @var $praProcDatiAggiuntivi praProcDatiAggiuntivi */
            $praProcDatiAggiuntivi = itaModel::getInstance('praProcDatiAggiuntivi', $this->praProcDatiAggiuntiviFormname);
            $praProcDatiAggiuntivi->openGestione($this->currPranum, $Itepas_rec['ITEKEY']);
        }

        /*
         * Verifica presenza condizioni su ITEVPADETT
         */

        $sql = "SELECT * FROM ITEVPADETT WHERE ITECOD = '{$Itepas_rec['ITECOD']}' AND ITEKEY = '{$Itepas_rec['ITEKEY']}'";
        $this->iteVpaDettTab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);

        $this->CaricaGriglia($this->gridEspressioni, $this->praEspressioni->getGriglia());

        $this->caricaDestinatari($Itepas_rec['ITEKEY']);
        $this->CaricaGriglia($this->gridDestinatari, $this->destinatari);

        if ($Itepas_rec['ITEWRD'] != "") {
            $ditta = App::$utente->getKey('ditta');
            $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/';
            $prefilled = $destinazione . "prefilled_" . $Itepas_rec['ITEWRD'];
            if (file_exists($prefilled)) {
                Out::valore($this->nameForm . '_WRDPRECOMPILED', pathinfo($prefilled, PATHINFO_BASENAME));
            }
        }

        if ($Itepas_rec['ITEUPL'] == 1 || $Itepas_rec['ITEMLT'] == 1 || $Itepas_rec['ITEDIS'] == 1) {
            Out::show($this->divCtrCampi);
        } else {
            Out::hide($this->divCtrCampi);
        }
        if ($Itepas_rec['ITEUPL'] == 1 || $Itepas_rec['ITEMLT'] == 1) {
            Out::show($this->DivUpload);
        } else {
            Out::hide($this->DivUpload);
        }



        if ($Itepas_rec['ITEDRR'] == 1) {
            Out::show($this->nameForm . '_rapportoConfig');
        } else {
            Out::hide($this->nameForm . '_rapportoConfig');
        }

        if ($Itepas_rec['ITEUPL'] == 1 && $Itepas_rec['ITEFILE'] == 1) {
            Out::show($this->nameForm . '_Precompila');
            Out::show($this->nameForm . '_WRDPRECOMPILED_field');
        } else {
            Out::hide($this->nameForm . '_Precompila');
            Out::hide($this->nameForm . '_WRDPRECOMPILED_field');
        }
        if ($Itepas_rec['ITEIRE'] == 1) {
            Out::show($this->divMail);
        } else {
            Out::hide($this->divMail);
        }
        if ($Itepas_rec['ITEIDR'] == 0) {
            Out::show($this->nameForm . "_ITEPAS[ITEIFC]");
            Out::show($this->nameForm . "_ITEPAS[ITEIFC]_lbl");
            if ($Itepas_rec['ITEIFC'] != 1) {
                Out::hide($this->nameForm . "_ITEPAS[ITETAL]");
                Out::hide($this->nameForm . "_ITEPAS[ITETAL]_butt");
                Out::hide($this->nameForm . "_ITEPAS[ITETAL]_lbl");
                Out::hide($this->nameForm . "_SvuotaTipi");
            } else {
                Out::show($this->nameForm . "_ITEPAS[ITETAL]");
                Out::show($this->nameForm . "_ITEPAS[ITETAL]_butt");
                Out::show($this->nameForm . "_ITEPAS[ITETAL]_lbl");
                Out::show($this->nameForm . "_SvuotaTipi");
            }
        } else {
            Out::hide($this->nameForm . "_ITEPAS[ITEIFC]");
            Out::hide($this->nameForm . "_ITEPAS[ITEIFC]_lbl");
            Out::hide($this->nameForm . "_ITEPAS[ITETAL]");
            Out::hide($this->nameForm . "_ITEPAS[ITETAL]_butt");
            Out::hide($this->nameForm . "_ITEPAS[ITETAL]_lbl");
            Out::hide($this->nameForm . "_SvuotaTipi");
        }
        if ($Itepas_rec['ITERIF'] == 0) {
            Out::hide($this->nameForm . '_divRiferimento');
        } else {
            Out::show($this->nameForm . '_divRiferimento');
            if ($Itepas_rec['ITEPROC']) {
                $Anapra_rif_rec = $this->praLib->GetAnapra($Itepas_rec['ITEPROC']);
                Out::valore($this->nameForm . '_DesProcedimento', $Anapra_rif_rec['PRADES__1']);
                if ($Itepas_rec['ITEDAP'] != '')
                    $this->DecodDalPasso($Itepas_rec['ITEDAP']);
                if ($Itepas_rec['ITEALP'] != '')
                    $this->DecodAlPasso($Itepas_rec['ITEALP']);
            }
        }

//        if ($Praclt_rec['CLTOFF'] == 1) {
//            Out::block($this->nameForm . '_divSuap');
//        } else {
        if ($Itepas_rec['ITEPUB'] == 1) {
            Out::show($this->nameForm . '_divSuap');
            Out::hide($this->nameForm . '_divGenerali');
            Out::hide($this->nameForm . '_ITEPAS[ITECOM]');
            Out::hide($this->nameForm . '_ITEPAS[ITECOM]_lbl');
        } else if ($Itepas_rec['ITECOM'] == 1) {
            Out::hide($this->nameForm . '_divSuap');
            Out::show($this->nameForm . '_divGenerali');
            Out::hide($this->nameForm . '_ITEPAS[ITEPUB]');
            Out::hide($this->nameForm . '_ITEPAS[ITEPUB]_lbl');
        } else if ($Itepas_rec['ITECOM'] == 0 && $Itepas_rec['ITEPUB'] == 0) {
            Out::show($this->nameForm . '_divSuap');
            Out::hide($this->nameForm . '_divGenerali');
        }
//        }


        $this->decodificaITEQST($Itepas_rec['ITEQST']);

        if ($Itepas_rec['ITEDAT'] == 1) {
            Out::show($this->nameForm . "_DivRaccolta");
        } else {
            Out::hide($this->nameForm . "_DivRaccolta");
        }
        if ($Itepas_rec['ITEDIS'] == 1) {
            Out::show($this->nameForm . "_DivDistinta");
        } else {
            Out::hide($this->nameForm . "_DivDistinta");
        }

        if ($Itepas_rec['ITEQCLA'] == 1) {
            Out::show($this->nameForm . "_Classificazioni");
        } else {
            Out::hide($this->nameForm . "_Classificazioni");
        }

        if ($Itepas_rec['ITERDM'] == 1) {
            Out::show($this->nameForm . "_ITEPAS[ITENRA]_field");
            Out::show($this->nameForm . "_ITEPAS[ITECUSTOMTML]_field");
        } else {
            Out::hide($this->nameForm . "_ITEPAS[ITENRA]_field");
            Out::hide($this->nameForm . "_ITEPAS[ITECUSTOMTML]_field");
        }
        if ($Itepas_rec['ITECTB'] == 1) {
            Out::show($this->nameForm . "_editTestoBase");
        } else {
            Out::hide($this->nameForm . "_editTestoBase");
        }

        if ($Itepas_rec['ITEQALLE'] == 1) {
            Out::show($this->nameForm . "_ITEPAS[ITEQCLA]_field");
            Out::show($this->nameForm . "_ITEPAS[ITEQDEST]_field");
            Out::show($this->nameForm . "_ITEPAS[ITEQNOTE]_field");
            Out::show($this->nameForm . "_NomeFileUpload_field");
        } else {
            Out::hide($this->nameForm . "_ITEPAS[ITEQCLA]_field");
            Out::hide($this->nameForm . "_ITEPAS[ITEQDEST]_field");
            Out::hide($this->nameForm . "_ITEPAS[ITEQNOTE]_field");
            Out::hide($this->nameForm . "_NomeFileUpload_field");
            Out::hide($this->nameForm . "_Classificazioni");
        }

        if ($Itepas_rec['ITEDOW'] == 1 || $Itepas_rec['ITEDAT'] == 1 || $Itepas_rec['ITERDM'] == 1) {
            Out::show($this->nameForm . "_divPassoTemplate");
        } else {
            Out::hide($this->nameForm . "_divPassoTemplate");
        }

        if (strpos($Itepas_rec['ITETAL'], "99") !== false) {
            Out::attributo($this->nameForm . '_ITEPAS[ITETAL]', 'readonly', '1');
        } else {
            Out::attributo($this->nameForm . '_ITEPAS[ITETAL]', 'readonly', '0');
        }

        $metadati = unserialize($Itepas_rec['ITEMETA']);
        if ($metadati['TESTOBASEXHTML']) {
            Out::addClass($this->nameForm . "_pdfTestoBase", "ui-state-highlight");
        }
        if ($metadati['TESTOBASEDISTINTA']) {
            Out::addClass($this->nameForm . "_pdfTestoBaseDis", "ui-state-highlight");
        }
        if ($Itepas_rec['ITEHTML']) {
            Out::addClass($this->nameForm . "_OpenDescBox", "ui-state-highlight");
        } else {
            Out::delClass($this->nameForm . "_OpenDescBox", "ui-state-highlight");
        }
        Out::valore($this->nameForm . "_AttivaEspressione", $this->praLib->DecodificaControllo($Itepas_rec['ITEATE']));
        Out::valore($this->nameForm . "_ObblEspressione", $this->praLib->DecodificaControllo($Itepas_rec['ITEOBE']));
        if ($duplica == false) {
            Out::hide($this->nameForm . '_Aggiungi');
            Out::hide($this->nameForm . '_Duplica');
            if ($Itepas_rec['ITEPRIV'] == 1) {
                $aggiorna = $this->checkSpegniAggiorna($Itepas_rec['ITECOD']);
                if ($aggiorna == true) {
                    Out::show($this->nameForm . '_Aggiorna');
                    Out::show($this->nameForm . '_DuplicaDatiAgg');
                } else {
                    Out::hide($this->nameForm . '_Aggiorna');
                    Out::hide($this->nameForm . '_DuplicaDatiAgg');
                }
            }
        } else {
            Out::show($this->nameForm . '_Aggiungi');
            Out::show($this->nameForm . '_Duplica');
            Out::hide($this->nameForm . '_Aggiorna');
            Out::hide($this->nameForm . '_DuplicaDatiAgg');
            Out::valore($this->nameForm . '_ITEPAS[ITESEQ]', "");
            Out::valore($this->nameForm . '_ITEPAS[ITEWRD]', "");
            Out::valore($this->nameForm . '_ITEPAS[ITEIMG]', "");
            Out::valore($this->nameForm . '_ITEPAS[ITEVPA]', "");
            Out::valore($this->nameForm . '_ITEPAS[ITEVPN]', "");
            Out::valore($this->nameForm . '_Destinazione', "");
            Out::valore($this->nameForm . '_DestinazioneVai', "");
            Out::valore($this->nameForm . '_DestinazioneNo', "");
            Out::valore($this->nameForm . '_DestinazioneVaiNo', "");
            Out::valore($this->nameForm . '_CtrPasso', "");
            Out::valore($this->nameForm . '_CtrDesPasso', "");
        }
        if ($this->enteMaster) {
            Out::hide($this->nameForm . '_Aggiorna');
        }
        Out::setFocus('', $this->nameForm . '_ITEPAS[ITEPUB]');

        Out::hide($this->nameForm . '_divControllo');

        $Anastp_rec = $this->praLib->GetAnastp($Itepas_rec['ITEDEFSTATO'], 'rowid');
        Out::valore($this->nameForm . '_DEFSTATODES', $Anastp_rec['STPDES']);
        Out::attributo($this->nameForm . '_SEQANTECEDENTE', "readonly", '0');
        if ($Itepas_rec['ITEKPRE']) {
            $this->DecodPassAntecedente($Itepas_rec['ITEKPRE'], 'itekey');
        } else {
            Out::valore($this->nameForm . '_SEQANTECEDENTE', '');
            Out::valore($this->nameForm . '_DESITEKPRE', '');
        }

        $this->CaricaAzioni($Itepas_rec['ITECOD'], $Itepas_rec['ITEKEY']);

        $Anapra_rec = $this->praLib->GetAnapra($Itepas_rec['ITECOD']);
        if ($Anapra_rec['PRATPR'] == 'ENDOPROCEDIMENTO') {
            Out::show($this->nameForm . '_ClassificazioneDocumenti_field');
            Out::show($this->nameForm . '_ClassificazioneDocumentiSvuota');
            if ($Itepas_rec['ROWID_DOC_CLASSIFICAZIONE'] != 0 && $Itepas_rec['ROWID_DOC_CLASSIFICAZIONE']) {
                $ClassificazioneDoc = $this->docLib->getDocIntegrativi($Itepas_rec['ROWID_DOC_CLASSIFICAZIONE'], 'rowid', false);
                if ($ClassificazioneDoc['ROW_ID_PADRE'] != 0) {
                    $path = $this->getpathClassificazioneDoc(0, $ClassificazioneDoc['ROW_ID_PADRE'], '');
                } else {
                    $path = '';
                }
                Out::valore($this->nameForm . '_ITEPAS[ROWID_DOC_CLASSIFICAZIONE]', $ClassificazioneDoc['ROW_ID']);
                Out::valore($this->nameForm . '_ClassificazioneDocumenti', $path . $ClassificazioneDoc['OGGETTO']);
            }
        } else {
            Out::hide($this->nameForm . '_ClassificazioneDocumenti_field');
            Out::hide($this->nameForm . '_ClassificazioneDocumentiSvuota');
        }

        if ($Itepas_rec['ITEFLRISERVATO'] == praLibRiservato::RISERVATO_DA_ESPRESSIONE) {
            Out::show($this->nameForm . '_divRiservatoEspressione');
            Out::valore($this->nameForm . '_espressioneRiservato', $this->praLib->DecodificaControllo($Itepas_rec['ITEEXPRRISERVATO']));
        } else {
            Out::hide($this->nameForm . '_divRiservatoEspressione');
            Out::valore($this->nameForm . '_espressioneRiservato', '');
        }
    }

    public function apriInserimento() {
        Out::show($this->nameForm . '_Aggiungi');
        Out::show($this->nameForm . '_Duplica');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_DuplicaDatiAgg');
        Out::hide($this->divCtrCampi);
        Out::valore($this->nameForm . "_ITEPAS[ITEPUB]", 1);
        Out::hide($this->nameForm . '_ITEPAS[ITECOM]');
        Out::hide($this->nameForm . '_ITEPAS[ITECOM]_lbl');
        Out::hide($this->nameForm . '_ITEPAS[ITETAL]');
        Out::hide($this->nameForm . '_ITEPAS[ITETAL]_lbl');
        Out::hide($this->nameForm . '_ITEPAS[ITETAL]_butt');
        Out::hide($this->nameForm . '_SvuotaTipi');
        Out::hide($this->nameForm . '_ITEPAS[ITQCLA]');
        Out::hide($this->nameForm . '_ITEPAS[ITQDEST]');
        Out::hide($this->nameForm . '_ITEPAS[ITQNOTE]');
        Out::hide($this->nameForm . '_ObblEspressione_field');
        Out::hide($this->nameForm . '_ApriObblExpr');
        Out::hide($this->nameForm . '_DivRaccolta');
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDatiAggiuntivi");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCondizioniSalti");
        Out::show($this->nameForm . '_divSuap');
        Out::hide($this->nameForm . '_divGenerali');
        Out::hide($this->nameForm . '_DivUpload');
        Out::hide($this->nameForm . '_DivDistinta');
        Out::hide($this->nameForm . '_rapportoConfig');
        Out::hide($this->nameForm . '_DivMail');
        Out::hide($this->nameForm . '_divRiferimento');
        Out::attributo($this->nameForm . '_SEQANTECEDENTE', "readonly", '0');
        Out::valore($this->nameForm . '_ITEPAS[ITEFLRISERVATO', praLibRiservato::NO_RISERVATO);
        Out::hide($this->nameForm . '_divRiservatoEspressione');

        Out::html($this->nameForm . "_Destinazione_lbl", "Salta al Passo");
        Out::valore($this->nameForm . "_ITEPAS[ITECOD]", $this->currPranum);
        Out::setFocus('', $this->nameForm . '_ITEPAS[ITESEQ]');

        $this->CaricaAzioni();
        $this->decodificaITEQST();
    }

    public function checkSpegniAggiorna($procedimento) {
        $aggiorna = false;
        $Iteevt_tab = $this->praLib->GetIteevt($procedimento, "codice", true);
        foreach ($Iteevt_tab as $Iteevt_rec) {
            $Anatsp_rec = $this->praLib->GetAnatsp($Iteevt_rec['IEVTSP']);
            $Utenti_rec = $this->praLib->GetUtente(App::$utente->getKey('nomeUtente'));
            if ($Anatsp_rec['TSPSUPERADMIN'] != 0) {
                $gruppoSuperAdmin = str_pad($Anatsp_rec['TSPSUPERADMIN'], 10, '0', STR_PAD_LEFT);
            }
            if ($Utenti_rec['UTEGRU'] != 0) {
                if ($Utenti_rec['UTEGRU'] == 1 || $Utenti_rec['UTEGRU'] == $Anatsp_rec['TSPSUPERADMIN']) {
                    $aggiorna = true;
                    break;
                }
            }
        }



        for ($i = 1; $i <= 30; $i++) {
            if ($Utenti_rec["UTEGEX__$i"] != 0) {
                $gruppo = str_pad($Utenti_rec["UTEGEX__$i"], 10, '0', STR_PAD_LEFT);
                if ($gruppo == 0000000001 || $gruppo == $gruppoSuperAdmin) {
                    $aggiorna = true;
                    break;
                }
            }
        }
        return $aggiorna;
    }

    public function Controlli() {
        $itepas_attuale = $this->praLib->GetItepas($_POST[$this->nameForm . '_ITEPAS']['ITECOD'], "rowid");
        if ($_POST[$this->nameForm . '_ITEPAS']['TEMPLATEKEY'])
            $itepas_template = $this->praLib->GetItepas($_POST[$this->nameForm . '_ITEPAS']['TEMPLATEKEY'], "itekey");
        //
        // Controllo che ci siano max 7 passi upload generici per infocamere
        //
        $sql = "SELECT * FROM ITEPAS WHERE ITECOD = '" . $_POST[$this->nameForm . '_ITEPAS']['ITECOD'] . "'AND ITEPUB<>0 AND (ITEUPL<>0 OR ITEMLT<>0) AND ITEIFC=1";
        $Itepas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
//        if (count($Itepas_tab) > 7) {
//            Out::msgStop("Errore.", "E' stato superato il numero massimo di passi upload per CAMERA DI COMMERCIO.<br>Il numero di passi upload consentiti è 7");
//            return false;
//        }
        //
        // Controllo che ci sia max un passo di upload con PDF PRATICA
        //
        if ($_POST[$this->nameForm . '_ITEPAS']['ITEIFC'] == 2) {
            $sql = "SELECT * FROM ITEPAS WHERE ITECOD = '" . $_POST[$this->nameForm . '_ITEPAS']['ITECOD'] . "' AND ITEPUB<>0 AND (ITEUPL<>0 OR ITEMLT<>0) AND ITEIFC=2
                     AND ITESEQ <> " . $_POST[$this->nameForm . '_ITEPAS']['ITESEQ'];
            $Itepas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
            if (count($Itepas_tab) > 1) {
                Out::msgStop("Errore.", "E' stato superato il numero massimo di passi upload con il flag PDF PRATICA<br>Il numero di passi upload consentiti è 1");
                return false;
            }
        }

        if ($_POST[$this->nameForm . '_ITEPAS']['ITEIFC'] == 1 && strlen($_POST[$this->nameForm . '_ITEPAS']['ITETAL'] == "")) {
            Out::msgStop("Errore!", 'Si scelto di inserire nel file COMUNICA il file come allegato generico.<br>Scegliere un tipo allegato');
            return false;
        }

        if ($_POST[$this->nameForm . '_ITEPAS']['ITERIF'] == 1) {
            if ($_POST[$this->nameForm . '_ITEPAS']['ITEPROC'] == "") {
                Out::msgStop("Errore!", 'Il passo è di tipo RIFERIMENTO.<br>Sceglire il procedimento di riferimento');
                return false;
            } else {
                if ($_POST[$this->nameForm . '_DalPasso'] == "" || $_POST[$this->nameForm . '_AlPasso'] == "") {
                    Out::msgStop("Errore!", 'Il passo è di tipo RIFERIMENTO.<br>Scegliere i passi di arrivo e partenza per il procedimento ' . $_POST[$this->nameForm . '_ITEPAS']['ITEPROC']);
                    return false;
                }
            }
        }

        if (strlen($_POST[$this->nameForm . '_ITEPAS']['ITETAL']) == 3) {
            Out::msgStop("Errore!", 'Inserire una descrizione per il tipo allegato 99');
            return false;
        }
        if ($_POST[$this->nameForm . '_ITEPAS']['ITEOBL'] != 1 && $_POST[$this->nameForm . '_ITEPAS']['ITEIFC'] == 2) {
            Out::msgStop("Errore!", 'Il tipo PDF Pratica deve essere un passo obbligatorio.<br>Ceccare Operazione Obbligatoria');
            return false;
        }
        if ($_POST[$this->nameForm . '_ITEPAS']['ITEFILE'] == 1 && $_POST[$this->nameForm . '_ITEPAS']['ITEWRD'] == "") {
            if ($_POST[$this->nameForm . '_ITEPAS']['ITEDIS'] != 1) {
                Out::msgStop("Errore!", 'Il passo è di tipo upload automatico.<br>Scegliere un testo associato');
                return false;
            }
        }
        if ($_POST[$this->nameForm . '_ITEPAS']['ITECOM']) {
            if ($_POST[$this->nameForm . '_ITEPAS']['ITECDE'] && $_POST[$this->nameForm . '_ITEPAS']['ITEINT'] == 1) {
                Out::msgStop("Errore.", 'Puoi scegliere un solo destinatario');
                return false;
            } else {
                if (!$this->destinatari) {
                    Out::msginfo("Attenzione.", 'Il Passo è di tipo comunicazione!!<br>Non è stato scelto nessun destinatario.');
                }
            }
        }
        if ($_POST[$this->nameForm . '_ITEPAS']['ITEDOW'] == 1) {
            if ($_POST[$this->nameForm . '_ITEPAS']['ITEWRD'] == "" && $_POST[$this->nameForm . '_iteurl'] == "" && $_POST[$this->nameForm . '_ITEPAS']['ITEDWP'] == "") {
                Out::msgStop("Errore.", 'Il seguente passo è un passo download!!<br>Sceglire un testo associato o un url associato');
                return false;
            }
            $campiVal = $this->CheckValoriPassoDownload();
            if ($campiVal > 1) {
                Out::msgStop("Errore.", 'Valorizzare solo un campo tra Testo Associato, Url Associato e Passo Upload da Scaricare.');
                return false;
            }
        }

        if ($_POST[$this->nameForm . '_ITEPAS']['ITEDOW'] == 1 && $_POST[$this->nameForm . '_ITEPAS']['ITEWRD'] != "" && $_POST[$this->nameForm . '_iteurl'] != "") {
            Out::msgStop("Errore.", 'Il passo può gestire solo il download da un url o di un testo!!<br>Scegliere una delle 2 opzioni.');
            return false;
        }
        if ($_POST[$this->nameForm . '_ITEPAS']['ITEQST'] == 1 && ($_POST[$this->nameForm . '_ITEPAS']['ITEVPA'] == "" && $_POST[$this->nameForm . '_ITEPAS']['ITEVPN'] == "")) {
            Out::msgStop("Errore.", 'Flag domanda presente!!<br>Scegliere i passi di destinazione');
            return false;
        }

        if ($itepas_template) {
            if ($itepas_template['ITEDOW'] != $_POST[$this->nameForm . '_ITEPAS']['ITEDOW'] ||
                    $itepas_template['ITEDAT'] != $_POST[$this->nameForm . '_ITEPAS']['ITEDAT'] ||
                    $itepas_template['ITERDM'] != $_POST[$this->nameForm . '_ITEPAS']['ITERDM']) {
                Out::msgStop("Errore.", 'Il passo template e diverso dal passo attuale.<br>Scegliere un altro passo o modificare quello attuale.');
                return false;
            }
        }
        if ($itepas_attuale['TEMPLATEKEY']) {
            Out::msgQuestion("ATTENZIONE!", "<br><span style=\"font-weight:bold;\">Il passo " . $_POST[$this->nameForm . '_ITEPAS']['ITESEQ'] . " - " . $_POST[$this->nameForm . '_ITEPAS']['ITEDES'] . " utilizza un passo template.<br>Aggiornando il testo associato, l'help o i dati aggiutivi non sortirà l'effetto desiderato.</span>", array(
                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaUdateTemplate', 'model' => $this->nameForm, 'shortCut' => "f8"),
                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaUpdateTemplate', 'model' => $this->nameForm, 'shortCut' => "f5")
                    )
            );
            return false;
        }

        return true;
    }

    public function CheckValoriPassoDownload() {
        $arrayValue = array(
            "ITEWRD" => $_POST[$this->nameForm . '_ITEPAS']['ITEWRD'],
            "ITEURL" => $_POST[$this->nameForm . '_iteurl'],
            "ITEDWP" => $_POST[$this->nameForm . '_ITEPAS']['ITEDWP']
        );
        $arrayCampi = array(
            0 => 'ITEWRD',
            1 => 'ITEURL',
            2 => 'ITEDWP'
        );
        $campiVal = 0;
        foreach ($arrayValue as $key => $value) {
            foreach ($arrayCampi as $campo) {
                if ($key == $campo && $value) {
                    $campiVal += 1;
                }
            }
        }
        return $campiVal;
    }

    public function CreaCombo() {
        $sql = "SELECT * FROM ANAPAG";
        $anapag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        Out::select($this->nameForm . '_ITEPAS[ITEPAY]', 1, "", "1", "");
        foreach ($anapag_tab as $anapag_rec) {
            Out::select($this->nameForm . '_ITEPAS[ITEPAY]', 1, $anapag_rec['PAGCOD'], "0", $anapag_rec['PAGDES']);
        }
        Out::select($this->nameForm . '_ITEPAS[ITEARE]', 1, '', "0", '');
        Out::select($this->nameForm . '_ITEPAS[ITEARE]', 1, 'AMBIENTE', "0", 'AMBIENTE');
        Out::select($this->nameForm . '_ITEPAS[ITEARE]', 1, 'EDILIZIA', "0", 'EDILIZIA');
        Out::select($this->nameForm . '_ITEPAS[ITEARE]', 1, 'SICUREZZA', "0", 'SICUREZZA');
        Out::select($this->nameForm . '_ITEPAS[ITERUO]', 1, '', "0", 'TUTTI');
        Out::select($this->nameForm . '_ITEPAS[ITERUO]', 1, '0001', "0", 'ESIBENTE');
        Out::select($this->nameForm . '_ITEPAS[ITERUO]', 1, '0002', "0", 'PROCURATORE');
        Out::select($this->nameForm . '_ITEPAS[ITERUO]', 1, '0003', "0", 'AGENZIE');

        Out::select($this->nameForm . '_ITEPAS[ITEIFC]', 1, '0', "1", 'No');
        Out::select($this->nameForm . '_ITEPAS[ITEIFC]', 1, '1', "0", 'Si Allegato Generico');
        Out::select($this->nameForm . '_ITEPAS[ITEIFC]', 1, '2', "0", 'Si PDF Pratica');
        //Out::select($this->nameForm . '_ITEPAS[ITEIFC]', 1, '3', "0", 'Si PDF Distinta');

        $anacla_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANACLA ORDER BY CLADES", true);
        Out::select($this->nameForm . '_Classificazioni', 1, "", "1", "");
        foreach ($anacla_tab as $anacla_rec) {
            Out::select($this->nameForm . '_Classificazioni', 1, $anacla_rec['CLACOD'], "0", $anacla_rec['CLADES']);
        }
    }

    private function caricaSubForms() {
        /*
         * Carico pannello Dati aggiuntivi
         */
        Out::html($this->nameForm . '_paneDatiAggiuntivi', '');

        /* @var $praProcDatiAggiuntivi praProcDatiAggiuntivi */
        $praProcDatiAggiuntivi = itaFormHelper::innerForm('praProcDatiAggiuntivi', $this->nameForm . '_paneDatiAggiuntivi');
        $praProcDatiAggiuntivi->setEvent('openform');
        $praProcDatiAggiuntivi->parseEvent();

        $this->praProcDatiAggiuntiviFormname = $praProcDatiAggiuntivi->getNameForm();
        
        /*
         * carico pannello AZIONI FO
         */
        $generator = new itaGenerator();
        $retHtml = $generator->getModelHTML('pradivAzioniFO', false, $this->nameForm, true);
        Out::html($this->nameForm . '_divAzioni', $retHtml);
    }

    function DecodResponsabile($Ananom_rec) {
        Out::valore($this->nameForm . '_ITEPAS[ITERES]', $Ananom_rec["NOMRES"]);
        Out::valore($this->nameForm . '_Nome', $Ananom_rec["NOMCOG"] . ' ' . $Ananom_rec["NOMNOM"]);
        $sql = "SELECT ANAUNI.ROWID AS ROWID, ANAUNI.UNIADD AS UNIADD, ANAUNI.UNIOPE AS UNIOPE, ANAUNI.UNISET AS UNISET,
            ANAUNI.UNISER AS UNISER,SETTORI.UNIDES AS DESSET, SERVIZI.UNIDES AS DESSER,UNITA.UNIDES AS DESOPE,
            NOMCOG & ' ' & NOMNOM AS NOMCOG
            FROM ANAUNI ANAUNI LEFT OUTER JOIN ANANOM ANANOM ON ANAUNI.UNIADD=ANANOM.NOMRES
            LEFT OUTER JOIN ANAUNI SETTORI ON ANAUNI.UNISET=SETTORI.UNISET AND SETTORI.UNISER=''
            LEFT OUTER JOIN ANAUNI SERVIZI ON ANAUNI.UNISET=SERVIZI.UNISET AND ANAUNI.UNISER=SERVIZI.UNISER AND SERVIZI.UNIOPE=''
            LEFT OUTER JOIN ANAUNI UNITA   ON ANAUNI.UNISET=UNITA.UNISET AND ANAUNI.UNISER=UNITA.UNISER AND ANAUNI.UNIOPE=UNITA.UNIOPE
            AND UNITA.UNIADD=''
            WHERE ANAUNI.UNISET<>'' AND ANAUNI.UNISER<>'' AND ANAUNI.UNIOPE<>'' AND ANAUNI.UNIADD<>'' AND ANAUNI.UNIAPE=''";
        $sql .= " AND ANAUNI.UNIADD = '" . $Ananom_rec["NOMRES"] . "'";
        $AnauniRes_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        $Anauni_rec = $this->praLib->getAnauni($AnauniRes_rec['UNISET']);
        Out::valore($this->nameForm . '_ITEPAS[ITESET]', $Anauni_rec['UNISET']);
        Out::valore($this->nameForm . '_SETTORE', $Anauni_rec['UNIDES']);
        if ($AnauniRes_rec['UNISER'] == "")
            $AnauniRes_rec['UNISET'] = "";
        $AnauniServ_rec = $this->praLib->GetAnauniServ($AnauniRes_rec['UNISET'], $AnauniRes_rec['UNISER']);
        Out::valore($this->nameForm . '_ITEPAS[ITESER]', $AnauniServ_rec['UNISER']);
        Out::valore($this->nameForm . '_SERVIZIO', $AnauniServ_rec['UNIDES']);
        if ($AnauniRes_rec['UNISET'] == "")
            $AnauniRes_rec['UNIOPE'] = "";
        $AnauniOpe_rec = $this->praLib->GetAnauniOpe($AnauniRes_rec['UNISET'], $AnauniRes_rec['UNISER'], $AnauniRes_rec['UNIOPE']);
        Out::valore($this->nameForm . '_ITEPAS[ITEOPE]', $AnauniOpe_rec['UNIOPE']);
        Out::valore($this->nameForm . '_UNITA', $AnauniOpe_rec['UNIDES']);

//        Out::valore($this->nameForm . '_ITEPAS[ITERES]', $Ananom_rec["NOMRES"]);
//        Out::valore($this->nameForm . '_Nome', $Ananom_rec["NOMCOG"] . ' ' . $Ananom_rec["NOMNOM"]);
//        $AnauniRes_rec = $this->praLib->GetAnauniRes($Ananom_rec['NOMRES']);
//        $Anauni_rec = $this->praLib->getAnauni($AnauniRes_rec['UNISET']);
//        Out::valore($this->nameForm . '_ITEPAS[ITESET]', $Anauni_rec['UNISET']);
//        Out::valore($this->nameForm . '_SETTORE', $Anauni_rec['UNIDES']);
//        if ($AnauniRes_rec['UNISER'] == "")
//            $AnauniRes_rec['UNISET'] = "";
//        $AnauniServ_rec = $this->praLib->GetAnauniServ($AnauniRes_rec['UNISET'], $AnauniRes_rec['UNISER']);
//        Out::valore($this->nameForm . '_ITEPAS[ITESER]', $AnauniServ_rec['UNISER']);
//        Out::valore($this->nameForm . '_SERVIZIO', $AnauniServ_rec['UNIDES']);
//        if ($AnauniRes_rec['UNISET'] == "")
//            $AnauniRes_rec['UNIOPE'] = "";
//        $AnauniOpe_rec = $this->praLib->GetAnauniOpe($AnauniRes_rec['UNISET'], $AnauniRes_rec['UNISER'], $AnauniRes_rec['UNIOPE']);
//        Out::valore($this->nameForm . '_ITEPAS[ITEOPE]', $AnauniOpe_rec['UNIOPE']);
//        Out::valore($this->nameForm . '_UNITA', $AnauniOpe_rec['UNIDES']);
    }

    function DecodVaialpasso($codice, $tipo = 'itekey') {
        $Itepas_rec = $this->praLib->GetItepas($codice, $tipo);
        Out::valore($this->nameForm . '_Destinazione', $Itepas_rec['ITESEQ']);
        Out::valore($this->nameForm . '_DescrizioneVai', $Itepas_rec['ITEDES']);
        Out::valore($this->nameForm . '_ITEPAS[ITEVPA]', $Itepas_rec['ITEKEY']);
    }

    function DecodVaialpassoNo($codice, $tipo = 'itekey') {
        $Itepas_rec = $this->praLib->GetItepas($codice, $tipo);
        Out::valore($this->nameForm . '_DestinazioneNo', $Itepas_rec['ITESEQ']);
        Out::valore($this->nameForm . '_DescrizioneVaiNo', $Itepas_rec['ITEDES']);
        Out::valore($this->nameForm . '_ITEPAS[ITEVPN]', $Itepas_rec['ITEKEY']);
    }

    function DecodCtrPasso($codice, $tipo = 'itekey') {
        $Itepas_rec = $this->praLib->GetItepas($codice, $tipo);
        Out::valore($this->nameForm . '_CtrPasso', $Itepas_rec['ITESEQ']);
        Out::valore($this->nameForm . '_CtrDesPasso', $Itepas_rec['ITEDES']);
        Out::valore($this->nameForm . '_ITEPAS[ITECTP]', $Itepas_rec['ITEKEY']);
    }

    function DecodDalPasso($codice, $tipo = 'itekey') {
        $Itepas_rec = $this->praLib->GetItepas($codice, $tipo);
        Out::valore($this->nameForm . '_DalPasso', $Itepas_rec['ITESEQ']);
        Out::valore($this->nameForm . '_DesDalPasso', $Itepas_rec['ITEDES']);
        Out::valore($this->nameForm . '_ITEPAS[ITEDAP]', $Itepas_rec['ITEKEY']);
    }

    function DecodAlPasso($codice, $tipo = 'itekey') {
        $Itepas_rec = $this->praLib->GetItepas($codice, $tipo);
        Out::valore($this->nameForm . '_AlPasso', $Itepas_rec['ITESEQ']);
        Out::valore($this->nameForm . '_DesAlPasso', $Itepas_rec['ITEDES']);
        Out::valore($this->nameForm . '_ITEPAS[ITEALP]', $Itepas_rec['ITEKEY']);
    }

    function DecodUploadPrec($codice, $tipo = 'itekey') {
        $Itepas_rec = $this->praLib->GetItepas($codice, $tipo);
        Out::valore($this->nameForm . '_PassoPrecUpl', $Itepas_rec['ITESEQ']);
        Out::valore($this->nameForm . '_PassoPrecUplDesc', $Itepas_rec['ITEDES']);
        Out::valore($this->nameForm . '_ITEPAS[ITEDWP]', $Itepas_rec['ITEKEY']);
    }

    function DecodTemplateKey($codice, $tipo = 'itekey') {
        $Itepas_rec = $this->praLib->GetItepas($codice, $tipo);
        $Anapra_rec = $this->praLib->GetAnapra($Itepas_rec['ITECOD']);
        Out::valore($this->nameForm . "_TemplateProc", $Anapra_rec['PRANUM']);
        Out::valore($this->nameForm . "_DesProc", $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2']);
        Out::valore($this->nameForm . "_TemplatePasso", $Itepas_rec['ITESEQ']);
        Out::valore($this->nameForm . "_DesPasso", $Itepas_rec['ITEDES']);
        Out::valore($this->nameForm . "_ITEPAS[TEMPLATEKEY]", $Itepas_rec['ITEKEY']);
    }

    function DecodDuplicaPasso($codice, $tipo = 'itekey') {
        $Itepas_rec = $this->praLib->GetItepas($codice, $tipo);
        $this->Dettaglio($codice, $tipo, $Itepas_rec);
    }

    function GetFileList($filePath) {
        if (!$dh = @opendir($filePath))
            return false;
        $retListGen = array();
        $rowid = 0;
        while (($obj = @readdir($dh))) {
            if ($obj == '.' || $obj == '..')
                continue;
            $rowid += 1;
            $retListGen[$rowid] = array(
                'rowid' => $rowid,
                'FILEPATH' => $filePath . '/' . $obj,
                'FILENAME' => $obj,
                'FILEINFO' => 'Info non presente'
            );
        }
        closedir($dh);
        return $retListGen;
    }

    function AllegaTesto() {
        //if ($_POST['response'] == 'success') {
        $origFile = $_POST['uploadedFile'];
        //$nomeFile = substr($origFile, strpos($origFile, '-'));
        //$nomeFile = substr(substr($nomeFile, 1), strpos(substr($nomeFile, 1), '-') + 1);
        $nomeFile = $_POST['file'];
        $ext = pathinfo($nomeFile, PATHINFO_EXTENSION);
        foreach ($this->ext as $key => $est) {
            if ($ext != $est['EXT']) {
                Out::msgStop('ERRORE!!', "L'estensione del file scelto non è tra quelle gestite.<br>Gestire l'estensione o sciegliere un altro file");
                return false;
            }
        }
        if ($nomeFile != '') {
            if ($ditta == '')
                $ditta = App::$utente->getKey('ditta');
            $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/';
            if (!is_dir($destinazione)) {
                Out::msgStop("Errore in caricamento file testo.", 'Destinazione non esistente: ' . $destinazione);
                return false;
            }
            $nomeFile = str_replace(' ', '_', $nomeFile);
            if (strlen($nomeFile) > 100) {
                Out::msgStop("Attenzione!", "Rinominare il File, il nome non deve essere più lungo di 100 caratteri.");
                return false;
            }
            if (file_exists($destinazione . $nomeFile)) {
                Out::msgQuestion("Attenzione.", "Il nome del File coincide con uno già esistente. Sovrascrivere il File da Caricare?", array(
                    'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaSov', 'model' => $this->nameForm, 'shortCut' => "f8"),
                    'F5-Sovrascrivi' => array('id' => $this->nameForm . '_Sovrascrivi', 'model' => $this->nameForm, 'shortCut' => "f5")
                        )
                );
                $this->datiAppoggio = array('origFile' => $origFile, 'destinazione' => $destinazione, 'nomeFile' => $nomeFile);
                //                Out::msgStop("Attenzione!", "Il nome del File coincide con uno già esistente. Rinominare il File da Caricare!");
                return false;
            }
            if (!@rename($origFile, $destinazione . $nomeFile)) {
                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                return false;
            } else {
                Out::valore($this->nameForm . '_ITEPAS[ITEWRD]', $nomeFile);
            }
        } else {
            Out::msgStop("Upload File", "Errore in Upload");
            return false;
        }
        return true;
    }

    function AllegaFile() {
        $origFile = $_POST['uploadedFile'];
        $nomeFile = $_POST['file'];
        $ext = strtolower(pathinfo($nomeFile, PATHINFO_EXTENSION));

        $arayImg = array('jpeg', 'bmp', 'gif', 'jpg');
        if (in_array($ext, $arayImg) !== true) {
            Out::msgStop('ERRORE!!', "L'estensione del file scelto non è un'immagine.<br>Sciegliere un altro file");
            return false;
        }

        if ($nomeFile != '') {
            if ($ditta == '')
                $ditta = App::$utente->getKey('ditta');
            $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/immagini/';
            if (!is_dir($destinazione)) {
                Out::msgStop("Errore in caricamento file testo.", 'Destinazione non esistente: ' . $destinazione);
                return false;
            }
            $nomeFile = str_replace(' ', '_', $nomeFile);
            if (strlen($nomeFile) > 24) {
                Out::msgStop("Attenzione!", "Rinominare il File, il nome non deve essere più lungo di 20 caratteri.");
                return false;
            }
            if (file_exists($destinazione . $nomeFile)) {
                Out::msgStop("Attenzione!", "Il nome del File coincide con uno già esistente. Rinominare il File da Caricare!");
                return false;
            }
            if (!@rename($origFile, $destinazione . $nomeFile)) {
                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                return false;
            } else {
                Out::valore($this->nameForm . '_ITEPAS[ITEIMG]', $nomeFile);
            }
        } else {
            Out::msgStop("Upload File", "Errore in Upload");
            return false;
        }
        return true;
    }

    function DecodAnamedCom($Codice, $tipoRic = 'codice', $tutti = 'si') {
        $Anamed_rec = $this->proLib->GetAnamed($Codice, $tipoRic, $tutti);
        Out::valore($this->nameForm . '_ITEPAS[ITECDE]', $Anamed_rec['MEDCOD']);
        Out::valore($this->nameForm . '_DESTINATARIO', $Anamed_rec['MEDNOM']);
        return $Anamed_rec;
    }

    function CaricaGriglia($_griglia, $_appoggio, $_tipo = '1') {
        $ita_grid01 = new TableView(
                $_griglia, array('arrayTable' => $_appoggio,
            'rowIndex' => 'idx')
        );
        if ($_tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(count($_appoggio));
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($_griglia);
        TableView::clearGrid($_griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    function decodDocumenti($codice, $tipo = 'codice') {
        $documenti_rec = $this->docLib->getDocumenti($codice, $tipo);
        Out::valore($this->nameForm . "_ITEPAS[ITETBA]", $documenti_rec['CODICE']);
        Out::valore($this->nameForm . "_DocumentoOgg", $documenti_rec['OGGETTO']);
    }

    function Aggiorna($controlli = true) {
        $Itepas_rec = $_POST[$this->nameForm . '_ITEPAS'];
        $metadata = array();
        if ($Itepas_rec['ITEDAT'] == 0 || !$this->currXHTML) {
            $_POST[$this->nameForm . '_accorpaPDFTestoBase'] = 0;
        }
        if ($Itepas_rec['ITEDIS'] == 0 || !$this->currXHTMLDis) {
            $_POST[$this->nameForm . '_accorpaPDFTestoBaseDis'] = 0;
        }

        //
        $Itepas_rec['ITEIDR'] = 0;
        $Itepas_rec['ITEFILE'] = 0;
        if ($Itepas_rec['ITEUPL'] == 1 || $Itepas_rec['ITEMLT'] == 1) {
            if ($_POST[$this->nameForm . '_accorpaPDFUpload'] == 1) {
                $Itepas_rec['ITEIDR'] = 1;
            }
        }
        if ($Itepas_rec['ITEDAT'] == 1) {
            if ($_POST[$this->nameForm . '_accorpaPDFTestoBase'] == 1) {
                $Itepas_rec['ITEIDR'] = 1;
            }
        }
        if ($Itepas_rec['ITEDIS'] == 1) {
            if ($_POST[$this->nameForm . '_accorpaPDFTestoBaseDis'] == 1) {
                $Itepas_rec['ITEIDR'] = 1;
            }
            if ($_POST[$this->nameForm . '_uploadAutomatico'] == 1) {
                $Itepas_rec['ITEFILE'] = 1;
            }
        }
        if ($Itepas_rec['ITEUPL'] == 1 || $Itepas_rec['ITEMLT'] == 1) {
            if ($_POST[$this->nameForm . '_ITEPAS']['ITEFILE'] == 1) {
                $Itepas_rec['ITEFILE'] = 1;
            }
        }

        // Generazione e aggiustamento campi automatici
        //
        if ($Itepas_rec['ITEKEY'] == '') {
            $Itepas_rec['ITEKEY'] = $this->praLib->keyGenerator($Itepas_rec['ITECOD']);
        }
        if ($Itepas_rec['ITEUPL'] == 0 && $Itepas_rec['ITEMLT'] == 0) {
            $Itepas_rec['ITEIFC'] = 0;
            $Itepas_rec['ITEEXT'] = '';
        }
        if ($Itepas_rec['ITEIDR'] == 1) {
            $Itepas_rec['ITEIFC'] = 0;
        }
        if ($Itepas_rec['ITEIFC'] == 0 || $Itepas_rec['ITEIFC'] == 2) {
            $Itepas_rec['ITETAL'] = "";
        }
        if ($Itepas_rec['ITEPUB'] == 1 || ($Itepas_rec['ITEPUB'] == 0 && $Itepas_rec['ITECOM'] == 0)) {
            $Itepas_rec['ITECDE'] = "";
        }
        if ($Itepas_rec['ITERIF'] == 0) {
            $Itepas_rec['ITEPROC'] = $Itepas_rec['ITEDAP'] = $Itepas_rec['ITEALP'] = "";
        }
        if ($Itepas_rec['ITEDAT'] == 0) {
            $Itepas_rec['ITECOL'] = 0;
        }

        if ($this->currMAILTemplate) {
            $metadata['TESTOBASEMAIL'] = $this->currMAILTemplate;
        }

        if ($this->currXHTML) {
            $metadata['TESTOBASEXHTML'] = $this->currXHTML;
        }

        if ($this->currXHTMLDis) {
            $metadata['TESTOBASEDISTINTA'] = $this->currXHTMLDis;
        }
        $Itepas_rec['ITEHTML'] = "";
        if ($this->currDescBox) {
            $Itepas_rec['ITEHTML'] = $this->currDescBox;
        }
        if ($_POST[$this->nameForm . '_NomeFileUpload']) {
            $metadata['TEMPLATENOMEUPLOAD'] = $_POST[$this->nameForm . '_NomeFileUpload'];
        }
        if ($_POST[$this->nameForm . '_Classificazioni']) {
            $metadata['CODICECLASSIFICAZIONE'] = $_POST[$this->nameForm . '_Classificazioni'];
        }
        if ($Itepas_rec['ITEQALLE'] == 0) {
            $Itepas_rec['ITEQCLA'] = 0;
            $Itepas_rec['ITEQDEST'] = 0;
            $Itepas_rec['ITEQNOTE'] = 0;
            $Itepas_rec['ITEQNOTE'] = 0;
            unset($metadata['TEMPLATENOMEUPLOAD']);
            Out::valore($this->nameForm . '_NomeFileUpload', "");
            unset($metadata['CODICECLASSIFICAZIONE']);
            Out::valore($this->nameForm . '_Classificazioni', "");
        }
        $Itepas_rec['ITEMETA'] = serialize($metadata);
        if ($controlli == true) {
            if (!$this->Controlli()) {
                return false;
            }
        }
        $Itepas_rec['ITEURL'] = $_POST[$this->nameForm . '_iteurl'];

        switch ($Itepas_rec['ITEQST']) {
            case 0:
                $Itepas_rec['ITEVPN'] = '';
                $this->iteVpaDettTab = array();
                break;

            case 1:
                $this->iteVpaDettTab = array();
                break;

            case 2:
                $Itepas_rec['ITEVPA'] = '';
                $Itepas_rec['ITEVPN'] = '';
                break;
        }

        if ($Itepas_rec['ITEFLRISERVATO'] != praLibRiservato::RISERVATO_DA_ESPRESSIONE) {
            $Itepas_rec['ITEEXPRRISERVATO'] = '';
        }

        $procedimento = $Itepas_rec['ITECOD'];
        $update_Info = 'Oggetto: Aggironamento passo seq ' . $Itepas_rec['ITESEQ'] . " - " . $Itepas_rec['ITEKEY'];
        if ($this->updateRecord($this->PRAM_DB, 'ITEPAS', $Itepas_rec, $update_Info)) {

            /* @var $praProcDatiAggiuntivi praProcDatiAggiuntivi */
            $praProcDatiAggiuntivi = itaModel::getInstance('praProcDatiAggiuntivi', $this->praProcDatiAggiuntiviFormname);
            if (!$praProcDatiAggiuntivi->aggiornaDati()) {
                return false;
            }

            $msg = $this->praEspressioni->RegistraEspressioni($this);
            if ($msg != true) {
                Out::msgStop("ATTENZIONE!", $msg);
                return false;
            }

            if (!$this->AggiornaVaiPasso($Itepas_rec)) {
                return false;
            }
            /*
             * Registra destinatari
             */
            $msgDest = $this->RegistraDestinatari($Itepas_rec['ITEKEY']);
            if ($msgDest != true) {
                Out::msgStop("ATTENZIONE!", $msgDest);
                return false;
            }

            $this->SalvaAzioniFO($Itepas_rec['ITEKEY']);

            $this->praLib->AggiornaMarcaturaProcedimento($procedimento, 'codice');
            $this->praLib->ordinaPassiProc($procedimento);
            $this->Dettaglio($Itepas_rec['ROWID'], 'rowid');
            Out::msgBlock("", 1000, true, "Passo Aggiornato");
            //$this->returnToParent();
        }
    }

    function DecodPassAntecedente($codice, $tipo = 'itekpre', $retid = "") {
        $itepas_rec = $this->praLib->GetItepas($codice, $tipo);
        Out::valore($this->nameForm . '_SEQANTECEDENTE', $itepas_rec['ITESEQ']);
        Out::valore($this->nameForm . '_DESITEKPRE', $itepas_rec['ITEDES']);
        Out::valore($this->nameForm . '_ITEPAS[ITEKPRE]', $itepas_rec['ITEKEY']);
    }

    function creaComboAssegato() {
        Out::select($this->nameForm . '_ITEPAS[ITEASSAUTO]', 1, '', "0", '');
        Out::select($this->nameForm . '_ITEPAS[ITEASSAUTO]', 1, 'UTENTE', "0", 'Utente Loggato');
        Out::select($this->nameForm . '_ITEPAS[ITEASSAUTO]', 1, 'RESP_PASSO', "0", 'Resp. Passo');
        Out::select($this->nameForm . '_ITEPAS[ITEASSAUTO]', 1, 'RESP_PROCEDIMENTO', "0", 'Resp. Procedimento');
    }

    public function CaricaAzioni($procedimento = false, $passo = false) {
        $AzioniFO = new praAzioniFO();
        $this->azioniFO = $AzioniFO->getGridAzioniPasso($procedimento, $passo);
        $this->CaricaGriglia($this->gridAzioniFO, $this->azioniFO);
    }

    public function SalvaAzioniFO($passo) {
        foreach ($this->azioniFO as $Praazioni_rec) {
            unset($Praazioni_rec['DESCRIZIONEAZIONE']);
            unset($Praazioni_rec['OPERAZIONE']);
            $Praazioni_rec['PRANUM'] = $this->currPranum;
            $Praazioni_rec['ITEKEY'] = $passo;
            if (isset($Praazioni_rec['ROWID'])) {
                $update_Info = 'Oggetto: Aggiornamento azione FO ' . $Praazioni_rec['DESCRIZIONEAZIONE'];
                if (!$this->updateRecord($this->PRAM_DB, 'PRAAZIONI', $Praazioni_rec, $update_Info)) {
                    Out::msgStop("Errore", "Aggiornamneto azione FO " . $Praazioni_rec['DESCRIZIONEAZIONE'] . " fallito");
                    return false;
                }
            } else {
                $insert_Info = 'Oggetto: Inserimento azione FO ' . $Praazioni_rec['DESCRIZIONEAZIONE'];
                if (!$this->insertRecord($this->PRAM_DB, 'PRAAZIONI', $Praazioni_rec, $insert_Info)) {
                    Out::msgStop("Errore", "Inserimento azione FO " . $Praazioni_rec['DESCRIZIONEAZIONE'] . " fallito");
                    return false;
                }
            }
        }
        return true;
    }

    private function editTemplatemail($tipoTemplate) {
        $chiaveBody = "BODY_$tipoTemplate";
        $rowid = $_POST[$this->nameForm . '_ITEPAS']['ROWID'];
        $Itepas_rec = $this->praLib->GetItepas($rowid, 'rowid');
        $praLibVar = new praLibVariabili();
        if ($Itepas_rec['ITEPUB'] == 1) {
            $praLibVar->setFrontOfficeFlag(true);
        }
        $praLibVar->setCodiceProcedimento($Itepas_rec['ITECOD']);
        $praLibVar->setChiavePasso($Itepas_rec['ITEKEY']);
        $dictionaryLegend = $praLibVar->getLegendaProcedimento('adjacency', 'smarty');

        $model = 'utiEditDiag';
        itaLib::openForm($model);
        $objModel = itaModel::getInstance($model);
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['edit_text'] = $this->currMAILTemplate[$chiaveBody];
        $_POST['returnModel'] = $this->nameForm;
        $_POST['returnEvent'] = 'returnEditDiag';
        $_POST['returnField'] = $this->nameForm . "_MailTestoBase$tipoTemplate";
        $_POST['dictionaryLegend'] = $dictionaryLegend;
        $_POST['readonly'] = false;
        $objModel->parseEvent();
        $objModel->setFormTitle("Template $tipoTemplate");
        return;
    }

    private function getpathClassificazioneDoc($Indice, $RowidPadre, $path) {
        $appoggio[$Indice] = $this->docLib->getDocIntegrativi($RowidPadre, 'rowid', false);
        $path = $appoggio[$Indice]['OGGETTO'] . '/' . $path;
        if ($appoggio[$Indice]['ROW_ID_PADRE'] != 0) {
            $K = $Indice;
            return $this->getpathClassificazioneDoc($Indice++, $appoggio[$K]['ROW_ID_PADRE'], $path);
        } else {
            return $path;
        }
    }

    private function dettaglioVaiPassoMultiplo($enabled = false) {
        if ($enabled) {
            
        } else {
            Out::show($this->nameForm . '_divDomanda');
            Out::hide($this->nameForm . '_divGridVaiPasso');
        }
    }

    private function decodificaITEQST($v = 0) {
        switch ($v) {
            case 0:
                Out::attributo($this->nameForm . '_SaltaPasso', 'checked', '0', 'checked');
                Out::attributo($this->nameForm . '_PassoDomanda', 'checked', '1');
                Out::attributo($this->nameForm . '_SaltaMultiplo', 'checked', '1');

                Out::show($this->nameForm . '_divDomanda');
                Out::hide($this->nameForm . '_divGridVaiPasso');

                Out::hide($this->nameForm . '_divRispostaNo');
                Out::html($this->nameForm . '_Destinazione_lbl', 'Salta al Passo');
                break;

            case 1:
                Out::attributo($this->nameForm . '_SaltaPasso', 'checked', '1');
                Out::attributo($this->nameForm . '_PassoDomanda', 'checked', '0', 'checked');
                Out::attributo($this->nameForm . '_SaltaMultiplo', 'checked', '1');

                Out::show($this->nameForm . '_divDomanda');
                Out::hide($this->nameForm . '_divGridVaiPasso');

                Out::show($this->nameForm . '_divRispostaNo');
                Out::html($this->nameForm . '_Destinazione_lbl', 'Vai al Passo (Risposta SI)');
                break;

            case 2:
                Out::attributo($this->nameForm . '_SaltaPasso', 'checked', '1');
                Out::attributo($this->nameForm . '_PassoDomanda', 'checked', '1');
                Out::attributo($this->nameForm . '_SaltaMultiplo', 'checked', '0', 'checked');

                Out::show($this->nameForm . '_divGridVaiPasso');
                Out::hide($this->nameForm . '_divDomanda');

                uasort($this->iteVpaDettTab, function($a, $b) {
                    return strcmp($a['ITESEQEXPR'], $b['ITESEQEXPR']);
                });

                $iteVpaDettTab = array();
                foreach ($this->iteVpaDettTab as $k => $Itevpadett_rec) {
                    $this->iteVpaDettTab[$k]['ITESEQEXPR'] = $Itevpadett_rec['ITESEQEXPR'] = (count($iteVpaDettTab) + 1) * 10;
                    $Itepas_vpa_rec = $this->praLib->GetItepas($Itevpadett_rec['ITEVPA'], 'itekey');
                    $Itevpadett_rec['PASSODEST'] = '<span style="color: #888;">' . $Itepas_vpa_rec['ITESEQ'] . '</span> ' . $Itepas_vpa_rec['ITEDES'];
                    $Itevpadett_rec['CONDIZIONE'] = trim($this->praLib->DecodificaControllo($Itevpadett_rec['ITEEXPRVPA']), "\n");
                    $iteVpaDettTab[$k] = $Itevpadett_rec;
                }

                $this->CaricaGriglia($this->nameForm . '_gridVaiPasso', $iteVpaDettTab);
                break;
        }
    }

    private function AggiornaVaiPasso($Itepas_rec) {
        /*
         * Aggiornamento vai passo multiplo
         */

        $sql = "SELECT * FROM ITEVPADETT WHERE ITECOD = '{$Itepas_rec['ITECOD']}' AND ITEKEY = '{$Itepas_rec['ITEKEY']}'";
        $itevpadett_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);

        foreach ($itevpadett_tab as $itevpadett_rec) {
            /*
             * Cerco i record cancellati (non presenti in $this->iteVpaDettTab)
             */
            foreach ($this->iteVpaDettTab as $local_itevpadett_rec) {
                if ($local_itevpadett_rec['ROW_ID'] && $local_itevpadett_rec['ROW_ID'] == $itevpadett_rec['ROW_ID']) {
                    continue 2;
                }
            }

            if (!$this->deleteRecord($this->PRAM_DB, 'ITEVPADETT', $itevpadett_rec['ROW_ID'], '', 'ROW_ID')) {
                Out::msgStop('Errore', "Cancellazione record ITEVPADETT '{$itevpadett_rec['ROW_ID']}' fallita.");
                return false;
            }
        }

        foreach ($this->iteVpaDettTab as $local_itevpadett_rec) {
            if ($local_itevpadett_rec['ROW_ID']) {
                if (!$this->updateRecord($this->PRAM_DB, 'ITEVPADETT', $local_itevpadett_rec, '', 'ROW_ID')) {
                    Out::msgStop('Errore', "Aggiornamento record ITEVPADETT '{$local_itevpadett_rec['ROW_ID']}' fallito.");
                    return false;
                }
            } else {
                if (!$this->insertRecord($this->PRAM_DB, 'ITEVPADETT', $local_itevpadett_rec, '', 'ROW_ID')) {
                    Out::msgStop('Errore', "Inserimento record ITEVPADETT fallito.");
                    return false;
                }
            }
        }

        return true;
    }

    public function RegistraDestinatari($itekey) {
        foreach ($this->destinatari as $key => $dest) {
            $denominazione = $dest['DENOMINAZIONE'];
            if ($dest['ROW_ID'] == 0) {
                $dest['ITECOD'] = $this->currPranum;
                $dest['ITEKEY'] = $itekey;
                $dest['CODICE'] = $dest['CODICE'];
                $dest['RUOLO'] = $dest['RUOLO'];
                $this->destinatari[$key]['DENOMINAZIONE'] = strip_tags($dest['DENOMINAZIONE']);
                unset($dest['DENOMINAZIONE']);
                $insert_Info = "Oggetto: Inserimento destinatario $denominazione";
                if (!$this->insertRecord($this->PRAM_DB, 'ITEDEST', $dest, $insert_Info, "ROW_ID")) {
                    return "Errore Inserimento destinatario $denominazione";
                }
            } else {
                unset($dest['DENOMINAZIONE']);
                $update_Info = "Oggetto: Aggiornamento destinatario $denominazione";
                if (!$this->updateRecord($this->PRAM_DB, 'ITEDEST', $dest, $update_Info, "ROW_ID")) {
                    return "Errore aggiornamento destinatario $denominazione";
                }
            }
        }
        return true;
    }

    public function caricaDestinatari($itekey) {
        $arrDest = array();
        $itedest_tab = $this->praLib->GetItedest($itekey);
        foreach ($itedest_tab as $itedest_rec) {
            $sogg['ROW_ID'] = $itedest_rec['ROW_ID'];
            if ($itedest_rec['CODICE']) {
                $anamed_rec = $this->proLib->GetAnamed($itedest_rec['CODICE']);
                $sogg['RUOLO'] = "";
                $sogg['CODICE'] = $itedest_rec['CODICE'];
                $sogg['DENOMINAZIONE'] = $anamed_rec['MEDNOM'];
            } elseif ($itedest_rec["RUOLO"]) {
                $anaruo_rec = $this->praLib->GetAnaruo($itedest_rec['RUOLO']);
                $sogg['CODICE'] = "";
                $sogg['RUOLO'] = $itedest_rec['RUOLO'];
                $sogg['DENOMINAZIONE'] = $anaruo_rec['RUODES'];
            }
            $arrDest[] = $sogg;
        }
        $this->destinatari = $arrDest;
    }

}
