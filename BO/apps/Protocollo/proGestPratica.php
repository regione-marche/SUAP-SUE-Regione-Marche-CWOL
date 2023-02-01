<?php

/**
 *
 * GESTIONE FASCICOLI PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2014 Italsoft sRL
 * @license
 * @version    28.04.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRicTitolario.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRicPratiche.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praSoggetti.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praPerms.class.php';
//include_once ITA_BASE_PATH . '/apps/Anagrafe/anaRic.class.php';
//include_once ITA_BASE_PATH . '/apps/Anagrafe/anaLib.class.php';
include_once ITA_BASE_PATH . '/apps/Commercio/wcoLib.class.php';
include_once ITA_BASE_PATH . '/apps/Commercio/wcoRic.class.php';
include_once ITA_BASE_PATH . '/apps/AlboPretorio/albRic.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibPratica.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';
//include_once ITA_BASE_PATH . '/apps/Protocollo/proIntegrazioni.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once(ITA_LIB_PATH . '/zip/itaZip.class.php');
include_once ITA_BASE_PATH . '/apps/Sviluppo/devRic.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once(ITA_LIB_PATH . '/QXml/QXml.class.php');
include_once ITA_BASE_PATH . '/apps/Utility/utiIcons.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proNote.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTitolario.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segLib.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segLibDocumenti.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proDocReader.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibFascicoloArch.class.php';

function proGestPratica() {
    $proGestPratica = new proGestPratica();
    $proGestPratica->parseEvent();
    return;
}

class proGestPratica extends itaModel {

    public $nameForm = "proGestPratica";
    public $divRic = "proGestPratica_divRicerca";
    public $divRis = "proGestPratica_divRisultato";
    public $divGes = "proGestPratica_divGestione";
    public $gridGest = "proGestPratica_gridGest";
    public $gridPassi = "proGestPratica_gridPassi";
    public $gridDocumenti = "proGestPratica_gridDocumenti";
    public $gridIter = "proGestPratica_gridIter";
    public $gridStrutturaIter = "proGestPratica_gridStrutturaIter";
    public $gridDati = "proGestPratica_gridDati";
    public $gridDatiPratica = "proGestPratica_gridDatiPratica";
    public $gridSoggetti = "proGestPratica_gridSoggetti";
    public $gridNote = "proGestPratica_gridNote";
    public $PRAM_DB;
    public $PROT_DB;
    public $COMUNI_DB;
    public $ITALWEB_DB;
    public $ITW_DB;
    public $accLib;
    public $devLib;
    public $praLib;
    public $proLib;
    public $segLib;
    public $segLibAllegati;
    public $proLibPratica;
    public $proLibFascicolo;
    public $proLibTitolario;
    public $proLibSerie;
    public $proLibTabDag;
    public $utiEnte;
    public $proAzioni = array();
    public $proDocumenti = array();
    public $proIter = array();
    public $praDati = array();
    public $praDatiPratica = array();
    public $datiFiltrati = array();
    public $allegatiComunica = array();
    public $praPerms;
    public $currGesnum;
    public $varAppoggio;
    public $dataRegAppoggio;
    public $currAllegato;
    public $workDate;
    public $workYear;
    public $page;
    public $insertTo;
    public $proric_rec;
    public $pranumSel;
    public $idCorrispondente;
    public $datiRubricaWS = array();
    public $emlComunica;
    public $eqAudit;
    public $praSoggetti;
    public $documentiProt;
    public $progesSel;
    public $geskey;
    /* @var $this->noteManager proNoteManager */
    public $noteManager;
    public $proLibAllegati;
    public $attivaAzioni = false;
    private $openRowId;
    private $openGeskey;
    private $rowidDocumentoDaSpostare;
    public $returnData = array();
    public $AnaproTitolarioDifferente = array();
    public $AlberoEreditaVisibilita = array();
    public $fl_manutenzioneSerie = '';
    public $RowidAllegaAProtocollo = '';
    public $consultazione = '';
    public $Ordinamento;

    function __construct() {
        parent::__construct();
        try {
            $this->utiEnte = new utiEnte();
            $this->praLib = new praLib();
            $this->proLib = new proLib();
            $this->segLib = new segLib();
            $this->segLibAllegati = new segLibAllegati();
            $this->proLibPratica = new proLibPratica();
            $this->proLibFascicolo = new proLibFascicolo();
            $this->proLibTitolario = new proLibTitolario();
            $this->proLibSerie = new proLibSerie();
            $this->proLibTabDag = new proLibTabDag();
            $this->praPerms = new praPerms();
            $this->accLib = new accLib();
            $this->devLib = new devLib();
            $this->eqAudit = new eqAudit();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->ITALWEB_DB = $this->utiEnte->getITALWEB_DB();
            $this->ITW_DB = $this->accLib->getITW();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        try {
            $this->COMUNI_DB = ItaDB::DBOpen('COMUNI', false);
        } catch (Exception $e) {
            App::log($e->getMessage());
        }
        $this->documentiProt = App::$utente->getKey($this->nameForm . '_documentiProt');
        $this->proAzioni = App::$utente->getKey($this->nameForm . '_praPassi');
        $this->proDocumenti = App::$utente->getKey($this->nameForm . '_praAlle');
        $this->proIter = App::$utente->getKey($this->nameForm . '_proIter');
        $this->currGesnum = App::$utente->getKey($this->nameForm . '_currGesnum');
        $this->varAppoggio = App::$utente->getKey($this->nameForm . '_varAppoggio');
        $this->dataRegAppoggio = App::$utente->getKey($this->nameForm . '_dataRegAppoggio');
        $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
        $this->returnId = App::$utente->getKey($this->nameForm . '_returnId');
        $this->page = App::$utente->getKey($this->nameForm . '_page');
        $this->insertTo = App::$utente->getKey($this->nameForm . '_insertTo');
        $this->praDati = App::$utente->getKey($this->nameForm . '_praDati');
        $this->praDatiPratica = App::$utente->getKey($this->nameForm . '_praDatiPratica');
        $this->datiFiltrati = App::$utente->getKey($this->nameForm . '_datiFiltrati');
        $this->allegatiComunica = App::$utente->getKey($this->nameForm . '_allegatiComunica');
        $this->currAllegato = App::$utente->getKey($this->nameForm . '_currAllegato');
        $this->proric_rec = App::$utente->getKey($this->nameForm . '_proric_rec');
        $this->pranumSel = App::$utente->getKey($this->nameForm . '_pranumSel');
        $this->idCorrispondente = App::$utente->getKey($this->nameForm . '_idCorrispondente');
        $this->datiRubricaWS = App::$utente->getKey($this->nameForm . '_datiRubricaWS');
        $this->emlComunica = App::$utente->getKey($this->nameForm . '_emlComunica');
        $this->praSoggetti = unserialize(App::$utente->getKey($this->nameForm . '_praSoggetti'));
        $this->progesSel = App::$utente->getKey($this->nameForm . '_progesSel');
        $this->geskey = App::$utente->getKey($this->nameForm . '_geskey');
        $this->noteManager = unserialize(App::$utente->getKey($this->nameForm . '_noteManager'));
        $this->attivaAzioni = App::$utente->getKey($this->nameForm . '_attivaAzioni');
        $this->rowidDocumentoDaSpostare = App::$utente->getKey($this->nameForm . '_rowidDocumentoDaSpostare');
        $this->returnData = App::$utente->getKey($this->nameForm . '_returnData');
        $this->AnaproTitolarioDifferente = App::$utente->getKey($this->nameForm . '_AnaproTitolarioDifferente');
        $this->AlberoEreditaVisibilita = App::$utente->getKey($this->nameForm . '_AlberoEreditaVisibilita');
        $this->fl_manutenzioneSerie = App::$utente->getKey($this->nameForm . '_fl_manutenzioneSerie');
        $this->RowidAllegaAProtocollo = App::$utente->getKey($this->nameForm . '_RowidAllegaAProtocollo');
        $this->consultazione = App::$utente->getKey($this->nameForm . '_consultazione');
        $this->Ordinamento = App::$utente->getKey($this->nameForm . '_Ordinamento');

        $data = App::$utente->getKey('DataLavoro');
        $this->proLibAllegati = new proLibAllegati();
        if ($data != '') {
            $this->workDate = $data;
        } else {
            $this->workDate = date('Ymd');
        }
        $this->workYear = date('Y', strtotime($this->workDate));
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_documentiProt', $this->documentiProt);
            App::$utente->setKey($this->nameForm . '_praPassi', $this->proAzioni);
            App::$utente->setKey($this->nameForm . '_praAlle', $this->proDocumenti);
            App::$utente->setKey($this->nameForm . '_proIter', $this->proIter);
            App::$utente->setKey($this->nameForm . '_currGesnum', $this->currGesnum);
            App::$utente->setKey($this->nameForm . '_varAppoggio', $this->varAppoggio);
            App::$utente->setKey($this->nameForm . '_dataRegAppoggio', $this->dataRegAppoggio);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnId', $this->returnId);
            App::$utente->setKey($this->nameForm . '_daPortlet', $this->daPortlet);
            App::$utente->setKey($this->nameForm . '_insertTo', $this->insertTo);
            App::$utente->setKey($this->nameForm . '_praDati', $this->praDati);
            App::$utente->setKey($this->nameForm . '_praDatiPratica', $this->praDatiPratica);
            App::$utente->setKey($this->nameForm . '_datiFiltrati', $this->datiFiltrati);
            App::$utente->setKey($this->nameForm . '_allegatiComunica', $this->allegatiComunica);
            App::$utente->setKey($this->nameForm . '_currAllegato', $this->currAllegato);
            App::$utente->setKey($this->nameForm . '_proric_rec', $this->proric_rec);
            App::$utente->setKey($this->nameForm . '_pranumSel', $this->pranumSel);
            App::$utente->setKey($this->nameForm . '_idCorrispondente', $this->idCorrispondente);
            App::$utente->setKey($this->nameForm . '_datiRubricaWS', $this->datiRubricaWS);
            App::$utente->setKey($this->nameForm . '_emlComunica', $this->emlComunica);
            App::$utente->setKey($this->nameForm . '_praSoggetti', serialize($this->praSoggetti));
            App::$utente->setKey($this->nameForm . '_progesSel', $this->progesSel);
            App::$utente->setKey($this->nameForm . '_geskey', $this->geskey);
            App::$utente->setKey($this->nameForm . '_noteManager', serialize($this->noteManager));
            App::$utente->setKey($this->nameForm . '_attivaAzioni', $this->attivaAzioni);
            App::$utente->setKey($this->nameForm . '_rowidDocumentoDaSpostare', $this->rowidDocumentoDaSpostare);
            App::$utente->setKey($this->nameForm . '_returnData', $this->returnData);
            App::$utente->setKey($this->nameForm . '_AnaproTitolarioDifferente', $this->AnaproTitolarioDifferente);
            App::$utente->setKey($this->nameForm . '_AlberoEreditaVisibilita', $this->AlberoEreditaVisibilita);
            App::$utente->setKey($this->nameForm . '_fl_manutenzioneSerie', $this->fl_manutenzioneSerie);
            App::$utente->setKey($this->nameForm . '_RowidAllegaAProtocollo', $this->RowidAllegaAProtocollo);
            App::$utente->setKey($this->nameForm . '_consultazione', $this->consultazione);
            App::$utente->setKey($this->nameForm . '_Ordinamento', $this->Ordinamento);
        }
    }

    public function getReturnData() {
        return $this->returnData;
    }

    public function setReturnData($returnData) {
        $this->returnData = $returnData;
    }

    public function setGeskey($geskey) {
        $this->geskey = $geskey;
    }

    public function setOpenRowId($openRowId) {
        $this->openRowId = $openRowId;
    }

    public function setOpenGeskey($openGeskey) {
        $this->openGeskey = $openGeskey;
    }

    public function setRowidAllegaAProtocollo($RowidAllegaAProtocollo) {
        $this->RowidAllegaAProtocollo = $RowidAllegaAProtocollo;
    }

    public function setConsultazione($consultazione) {
        $this->consultazione = $consultazione;
    }

    public function parseEvent() {
        parent::parseEvent();
        if ($this->proLib->checkStatoManutenzione()) {
            Out::msgStop("ATTENZIONE!", "<br>Il protocollo è nello stato di manutenzione.<br>Attendere la riabilitazione o contattare l'assistenza.<br>");
            $this->close();
            return;
        }
        switch ($_POST['event']) {
            case 'openform':
                $this->caricaParametri();
                Out::tabRemove($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDati");
//                TableView::hideCol($this->gridGest, "GESNUM"); // Completamente rimossa da xml
                if (!$this->attivaAzioni) {
                    Out::tabRemove($this->nameForm . "_tabProcedimento", $this->nameForm . "_panePassi");
//                    Out::hide($this->nameForm . '_Procedimento_field');
//                    Out::hide($this->nameForm . '_Desc_proc');
//                    TableView::hideCol($this->gridGest, 'PRADES__1');
                }
                $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli();
                if (!$permessiFascicolo[proLibFascicolo::PERMFASC_VISIBILITA_ARCHIVISTA]) {
                    Out::removeElement($this->nameForm . '_MOD_SERIE');
                }
                /*
                 * Tab Dati Aggiuntivi Rimossa
                 */
                Out::tabRemove($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAggiuntivi");
                $this->CreaCombo();
                $this->caricaUof();
//                $this->proLib->caricaElementiUtenteUfficio('proUfficioUtente', $this->nameForm .'_divTestaUte', $this->nameForm);
                if ($this->openRowId) {
                    if (!$this->Dettaglio($this->openRowId)) {
                        $this->close();
                    }
                    break;
                }
                if ($this->openGeskey) {
                    if (!$this->Dettaglio($this->openGeskey, 'geskey')) {
                        $this->close();
                    }
                    break;
                }
                $this->OpenRicerca();
                //$ParametriVari = $this->segLib->GetParametriVari();
                Out::hide($this->nameForm . '_divRicDocumentale');
                /*
                 *  Aggiunta allegati sul protocollo da fascicolo.
                 *  setRowidAllegaAProtocollo
                 */
                if ($this->RowidAllegaAProtocollo) {
                    $anapro_rec = $this->proLib->GetAnapro($this->RowidAllegaAProtocollo, 'rowid');
                    if ($anapro_rec) {
                        $Proto = $anapro_rec['PROPAR'] . ' ' . substr($anapro_rec['PRONUM'], 0, 4) . '/' . substr($anapro_rec['PRONUM'], 4);
                        $InfoSelezione = '<span style="padding:10px; font-size:14px; color:red; "><b>';
                        $InfoSelezione .= 'Selezione Allegati Fascicolo da inserire nel Protocollo: ' . $Proto . '</b></span>';
                        $Titolo = 'Selezione Allegati Fascicolo.';
                        Out::setAppTitle($this->nameForm, $Titolo);
                        Out::html($this->nameForm . '_divInformazioniFascicoli', $InfoSelezione);
                        Out::addClass($this->nameForm . '_divInformazioniFascicoli', "ui-corner-all ui-state-highlight");
                        Out::html($this->nameForm . '_ProtocollaAllegatiFas', 'Allega a Protocollo:<br>' . $Proto);
                    }
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridGest:
                        $this->daPortlet = $_POST['daPortlet'];
                        if ($this->consultazione) {
                            $this->returnId = $_POST['rowid'];
                            $this->returnToParent();
                            break;
                        }
                        if (!$this->Dettaglio($_POST['rowid'], $_POST['openForm'])) {
                            $this->TornaElenco();
                        }
                        break;
                    case $this->gridStrutturaIter:
                        $id = $_POST['rowid'];
                        if (strlen($id) < 10) {
                            break;
                        }
                        $arcite_rec = $this->proLib->GetArcite($id, 'itekey');
                        $profilo = proSoggetto::getProfileFromIdUtente();
                        if ($profilo['COD_SOGGETTO'] == $arcite_rec['ITEDES']) {
                            if ($arcite_rec['ITEFIN']) {
                                Out::msgBlock('', 5000, true, '<div class="ita-icon ita-icon-check-red-24x24"></div><div style="font-size:1.5em;">L\'iter è chiuso, presta molta attenzione!!</div>');
                            }
                        } else {
                            Out::msgBlock('', 2000, true, '<div style="font-size:1.8em;">L\'iter non è di propria competenza</div>');
                            break;
                        }

                        $model = 'proGestIter';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowidIter'] = $arcite_rec['ROWID'];
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->gridIter:
                        $rowId = $_POST['rowid'];
                        $arcite_rec = $this->proLib->GetArcite($rowId, 'rowid');
                        $profilo = proSoggetto::getProfileFromIdUtente();
                        if ($profilo['COD_SOGGETTO'] == $arcite_rec['ITEDES']) {
                            if ($arcite_rec['ITEFIN']) {
                                Out::msgBlock('', 5000, true, '<div class="ita-icon ita-icon-check-red-24x24"></div><div style="font-size:1.5em;">L\'iter è chiuso, presta molta attenzione!!</div>');
                            }
                        } else {
                            Out::msgBlock('', 2000, true, '<div style="font-size:1.8em;">L\'iter non è di propria competenza</div>');
                            break;
                        }
                        $model = 'proGestIter';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowidIter'] = $rowId;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->gridPassi:
                        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
                        if ($proges_rec['GESDCH']) {
                            break;
                        }
                        $rigaSel = $_POST[$this->gridPassi]['gridParam']['selrow'];
                        $model = 'proPassoPratica';
                        $chiave = $_POST['rowid'];
                        $propas_rec = $this->proLibPratica->GetPropas($chiave, 'propak');
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $propas_rec['ROWID'];
                        $_POST['modo'] = "edit";
                        $_POST['pagina'] = $this->page;
                        $_POST['selRow'] = $rigaSel;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnProPasso';
                        $_POST[$model . '_title'] = 'Gestione Azione.....';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case $this->gridDocumenti:
                        $chiave = $_POST['rowid'];
                        switch (substr($chiave, 0, 4)) {
                            case 'PRO-':
                                if ($this->proDocumenti[$chiave]['PROTOCOLLO'] == 1 || $this->proDocumenti[$chiave]['GESTIONE'] == '') {
                                    break;
                                }
                                $rigaSel = $_POST[$this->gridPassi]['gridParam']['selrow'];
                                $documento = $this->proDocumenti[$chiave];
                                $propas_rec = $this->proLibPratica->GetPropas(substr($chiave, 4, 10), 'paspro', false, substr($chiave, 14, 2));
                                if ($propas_rec['PASPAR'] == 'F') {
                                    break; // PASPAR F
                                }
                                $this->varAppoggio = array();
                                $this->varAppoggio['CHIAVERIGA_NUM'] = substr($chiave, 4, 10);
                                $this->varAppoggio['CHIAVERIGA_TIPO'] = substr($chiave, 14, 2);
                                $this->GestisciSottoFascicolo($propas_rec['ROWID']);
                                break;

                            case 'DOC-':
                                // Qui passano anche gli allegati dei protocolli non sono PRO-, ma DOC- 
                                if (array_key_exists($_POST['rowid'], $this->proDocumenti) == true) {
                                    if ($this->proDocumenti[$_POST['rowid']]['FILENAME'] == '' && !$this->proDocumenti[$_POST['rowid']]['DOCRELCLASSE']) {
                                        break;
                                    }
                                    $name = strip_tags($this->proDocumenti[$_POST['rowid']]['NAME']);
                                    if ($this->proDocumenti[$_POST['rowid']]['PRORISERVA']) {
                                        $name = strip_tags($this->proDocumenti[$_POST['rowid']]['FILEORIG']);
                                    }
                                    // Qui possibile controllo per verificare se può scaricare il documento
                                    //
                                    $documento = $this->proDocumenti[$_POST['rowid']];
                                    $ProNum = substr($chiave, 4, 10);
                                    $ProPar = substr($chiave, 14, 2);
                                    if ($documento['PRORISERVA']) {
                                        if ($ProPar == 'F') {
                                            // Sottofasicolo (N) Non può essere riservato per ora.
                                            $anapro_check = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'fascicolo', $this->geskey);
                                        } else {
                                            $anapro_check = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'codice', $ProNum, $ProPar);
                                        }
                                        if (!$anapro_check) {
                                            Out::msgInfo("Attenzione", "Non hai accesso a questo file.");
                                            break;
                                        }
                                    }
                                    /* Ulteriori Controlli:
                                     * Controllo la posizione del documento e verifico i suoi permessi e del padre. (Sottofascicolo o Fasciolo)
                                     */
                                    $retCheck = $this->CheckPermessiDocumentoFascicolo($chiave);
                                    if (!$retCheck['GESTIONE'] && !$retCheck['CONSULTA']) {
                                        Out::msgInfo("Attenzione", "Non hai accesso a questo file.");
                                        break;
                                    }
                                    /*
                                     * Controllo accesso al protocollo per l'utente, e il suo allegato.
                                     */
                                    $tipoRic = 'codice';
                                    if ($ProPar == 'F') {
                                        $tipoRic = 'fascicolo';
                                    }
                                    $anapro_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, $tipoRic, $ProNum, $ProPar);
                                    if (!$anapro_rec) {
                                        Out::msgInfo("Attenzione", "Non hai accesso a questo file." . print_r($chiave, true));
                                        break;
                                    }
                                    // Nuova funzione centralizzata per lettura di allegati.
                                    $rowidAnadoc = $this->proDocumenti[$_POST['rowid']]['ROWIDANADOC'];
                                    $Anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, 'rowid');
                                    $Indice_rec = array();
                                    if ($Anadoc_rec['DOCPAR'] == 'I') {
                                        $Indice_rec = $this->segLib->GetIndice($Anadoc_rec['DOCNUM'], 'anapro', $Anadoc_rec['DOCPAR']);
                                    }
                                    /*  Se è di tipo "I" e ha un indice collegato è un documentale. */
                                    if ($Anadoc_rec['DOCPAR'] == 'I' && $Indice_rec) {
                                        $this->segLibAllegati->scaricaDocumento($Anadoc_rec);
                                    } else {
                                        if (!$this->proLibAllegati->OpenDocAllegato($rowidAnadoc)) {
                                            Out::msgInfo('Apertura Documento', $this->proLibAllegati->getErrMessage());
                                        }
                                    }
                                }
                                break;
                        }
                        break;
                    case $this->gridSoggetti:
                        $rowid = $_POST['rowid'];
                        $_POST = array();
                        $model = 'praSoggettiGest';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnSoggetti';
                        $_POST['event'] = 'openform';
                        $_POST['soggetto'] = $this->praSoggetti->GetSoggetto($rowid);
                        $_POST['rowid'] = $rowid;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->gridNote:
                        $dati = $this->noteManager->getNota($_POST['rowid']);
                        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
                        $destinatari = $this->proLibFascicolo->getDestinatariFascicolo($proges_rec['GESKEY']);
                        $model = 'proDettNote';
                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnProDettNote');
                        $formObj->setReturnId('');
                        $rowid = $_POST['rowid'];
                        $_POST = array();
                        $_POST['readonly'] = true;
                        $_POST['dati'] = $dati;
                        $_POST['rowid'] = $rowid;
                        $_POST['destinatari'] = $destinatari;
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridNote:
                        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
                        $destinatari = $this->proLibFascicolo->getDestinatariFascicolo($proges_rec['GESKEY']);
                        $model = 'proDettNote';
                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnProDettNote');
                        $formObj->setReturnId('');
                        $_POST = array();
                        $_POST['destinatari'] = $destinatari;
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                    case $this->gridPassi:
//                        $model = 'proPassoPratica';
//                        $rowid = $_POST['rowid'];
//                        $_POST = array();
//                        $_POST['event'] = 'openform';
//                        $_POST['procedimento'] = $this->currGesnum;
//                        $_POST['modo'] = "add";
////$_POST['perms'] = $this->perms;
//                        $_POST[$model . '_returnModel'] = $this->nameForm;
//                        $_POST[$model . '_returnMethod'] = 'returnProPasso';
//                        $_POST[$model . '_title'] = 'Gestione Azione.....';
//                        itaLib::openForm($model);
//                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
//                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//                        $model();
                        break;
//                    case $this->gridDatiPratica:
//                        praRic::praRicPraidc($this->nameForm, 'returnPraidc');
//                        break;
                    case $this->gridSoggetti:
                        $_POST = array();
                        $model = 'praSoggettiGest';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnSoggetti';
                        $_POST['event'] = 'openform';
                        $_POST['soggetti'] = $this->praSoggetti->soggetti;
                        $_POST['pratica'] = $this->currGesnum;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridDocumenti:
                        $this->SganciaDaFascicolo();
                        break;
//                    case $this->gridDatiPratica:
//                        Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare il dato aggiuntivo?", array(
//                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancDato', 'model' => $this->nameForm, 'shortCut' => "f8"),
//                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancDato', 'model' => $this->nameForm, 'shortCut' => "f5")
//                                )
//                        );
//                        break;
//                    case $this->gridSoggetti:
//                        Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare il soggetto?", array(
//                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancSogg', 'model' => $this->nameForm, 'shortCut' => "f8"),
//                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancSogg', 'model' => $this->nameForm, 'shortCut' => "f5")
//                                )
//                        );
//                        $this->varAppoggio = $_POST['rowid'];
//                        break;
                }
                break;
            case 'exportTableToExcel':
                switch ($_POST['id']) {
                    case $this->gridGest:
                        $sql = $this->CreaSqlXls();
                        $ita_grid01 = new TableView($this->gridGest, array(
                            'sqlDB' => $this->PROT_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setSortIndex('GESNUM');
                        $ita_grid01->setSortOrder('desc');
                        $ita_grid01->exportXLS('', 'pratiche.xls');
                        break;
//                    case $this->gridDati:
//                        $ExportDati = "";
//                        if ($this->datiFiltrati == "")
//                            $this->datiFiltrati = $this->praDati;
//                        foreach ($this->datiFiltrati as $dato) {
//                            $ExportDati .= $dato['DAGKEY'] . ";";
//                        }
//                        $ExportDati .= "\r\n";
//                        $ExportDatiNew = substr($ExportDati, 0, strlen($ExportDati) - 1);
//                        foreach ($this->datiFiltrati as $dato) {
//                            $ExportDatiNew .= $dato['DAGVAL'] . ";";
//                        }
//                        $nome_file = './' . App::$utente->getkey('privPath') . '/' . App::$utente->getKey('TOKEN') . '-ExportDati.csv';
//                        if (file_put_contents($nome_file, $ExportDatiNew)) {
//                            Out::openDocument(
//                                    utiDownload::getUrl(
//                                            'exportDati_' . $this->currGesnum . '.csv', $nome_file
//                                    )
//                            );
//                        }
//                        break;
                }
                break;
            case 'printTableToHTML':
                switch ($_POST['id']) {
                    case $this->gridNote:
                        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
                        $utiEnte = new utiEnte();
                        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array(
                            "Ente" => $ParametriEnte_rec['DENOMINAZIONE'],
                            "Titolo" => "Note : " . $proges_rec['GESOGG'],
                            "mostraclasse" => 1,
                            "Sql" => $this->noteManager->getSqlNote()
                        );
                        $itaJR->runSQLReportPDF($this->PROT_DB, 'proEleNote2', $parameters);
                        break;
                    case $this->gridDocumenti:
                        $anapro_F_rec = $this->proLibFascicolo->getAnaproFascicolo($this->geskey);
                        if (!$anapro_F_rec) {
                            return false;
                        }
                        $orgnode_rec = $this->proLib->GetOrgNode($anapro_F_rec['PRONUM'], 'codice', $anapro_F_rec['PROPAR']);
                        $utiEnte = new utiEnte();
                        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array(
                            "Ente" => $ParametriEnte_rec['DENOMINAZIONE'],
                            "Sql" => $this->CreaSqlDocumenti($orgnode_rec['PRONUM'], $orgnode_rec['PROPAR']),
                            "Numero" => $orgnode_rec['ORGKEY']
                        );
                        $itaJR->runSQLReportPDF($this->PROT_DB, 'proEleDoc', $parameters);
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridGest:
                        $sql = $this->CreaSql();
                        if ($sql != false) {
                            $ordinamento = $_POST['sidx'];
                            if ($ordinamento === 'IMPRESA' || $ordinamento === 'FISCALE' || $ordinamento === 'STATO' || $ordinamento === 'STATOALL' || $ordinamento === 'SOTTOFASCICOLI') {
                                break;
                            }
                            $ita_grid01 = new TableView($this->gridGest, array('sqlDB' => $this->PROT_DB,
                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum($_POST['page']);
                            $ita_grid01->setPageRows($_POST['rows']);
                            $ita_grid01->setSortIndex($ordinamento);
                            $ita_grid01->setSortOrder($_POST['sord']);
                            $Result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
                            $ita_grid01->getDataPageFromArray('json', $Result_tab);
                        }
                        break;
                    case $this->gridPassi:
                        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
                        $this->proAzioni = $this->caricaAzioni($this->currGesnum);
                        if (!$this->praPerms->checkSuperUser($proges_rec)) {
                            $this->proAzioni = $this->praPerms->filtraPassiView($this->proAzioni);
                        }

                        if ($this->proAzioni) {
                            $this->CaricaGriglia($this->gridPassi, $this->proAzioni, '2');
                            Out::show($this->gridPassi);
                        }
                        break;
//                    case $this->gridSoggetti:
//                        $this->CaricaGriglia($this->gridSoggetti, $this->praSoggetti->getGriglia());
//                        break;
                    case $this->gridDocumenti:
                        $this->getOrdinamento();
                        $where = $this->caricaWhere();
                        $this->CaricaAllegati($where, '2');
                        break;
                    case $this->gridIter:
                        $this->CaricaIter($this->geskey);
                        $this->CaricaGriglia($this->gridIter, $this->proIter);
                        break;
                    case $this->gridStrutturaIter:
                        $this->CaricaStrutturaIter($this->geskey);
                        $this->CaricaGriglia($this->gridStrutturaIter, $this->proIter);
                        break;
//                    case $this->gridDatiPratica:
//                        $this->praDatiPratica = $this->caricaDatiPratica();
//                        $this->CaricaGriglia($this->gridDatiPratica, $this->praDatiPratica);
//                        break;
                    case $this->gridNote:
                        $filter = '';
                        if ($_POST['_search'] == "true") {
                            $filter = $this->noteGenFilterFromGrid();
                        }
                        $this->caricaNote($filter);
                        break;
//                    case $this->gridDati:
//                        if ($this->praDati) {
//                            $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
//                            $passi = $this->caricaAzioni($this->currGesnum);
//                            if ($_POST['_search'] == "true") {
//                                $this->datiFiltrati = $this->praDati;
//                                foreach ($this->datiFiltrati as $key => $dato) {
//                                    if ($_POST['PROSEQ']) {
//                                        if (strpos(strtolower($dato['PROSEQ']), strtolower($_POST['PROSEQ'])) === false) {
//                                            unset($this->datiFiltrati[$key]);
//                                        }
//                                    }
//                                    if ($_POST['DAGKEY']) {
//                                        if (strpos(strtolower($dato['DAGKEY']), strtolower($_POST['DAGKEY'])) === false) {
//                                            unset($this->datiFiltrati[$key]);
//                                        }
//                                    }
//                                    if ($_POST['DAGDES']) {
//                                        if (strpos(strtolower($dato['DAGDES']), strtolower($_POST['DAGDES'])) === false) {
//                                            unset($this->datiFiltrati[$key]);
//                                        }
//                                    }
//                                    if ($_POST['DAGVAL']) {
//                                        if (strpos(strtolower($dato['DAGVAL']), strtolower($_POST['DAGVAL'])) === false) {
//                                            unset($this->datiFiltrati[$key]);
//                                        }
//                                    }
//                                }
//                                if (!$this->praPerms->checkSuperUser($proges_rec)) {
//                                    $this->datiFiltrati = $this->praPerms->filtraDatiAggView($passi, $this->datiFiltrati);
//                                }
//
//                                $this->CaricaGriglia($this->gridDati, $this->datiFiltrati, '3');
//                            } else {
//                                $this->praDati = $this->caricaDati($this->currGesnum);
//                                if (!$this->praPerms->checkSuperUser($proges_rec)) {
//                                    $this->praDati = $this->praPerms->filtraDatiAggView($passi, $this->praDati);
//                                }
//                                $this->CaricaGriglia($this->gridDati, $this->praDati, '3');
//                            }
//                        }
//                        break;
                }
                break;
            case 'cellSelect':
                $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
                switch ($_POST['id']) {
                    case $this->gridGest:
                        switch ($_POST['colName']) {
                            case 'ANTECEDENTE':
                                $proges_check = $this->proLibPratica->GetProges($_POST['rowid'], 'rowid');
                                $Anaorg_rec = $this->proLib->GetAnaorg($proges_check['GESKEY'], 'orgkey');
                                if ($Anaorg_rec['ORGKEYPRE']) {
                                    proRic::proRicLegameFascicoli($this->proLib, $this->nameForm, 'returnFasCollegati', $this->PROT_DB, $Anaorg_rec);
                                }
                                break;
                        }
                        break;
                    case $this->gridPassi:
                        $propas_rec = $this->proLibPratica->GetPropas($_POST['rowid']);
                        switch ($_POST['colName']) {
                            case 'ADDAZIONE':
                                if ($proges_rec['GESDCH']) {
                                    break;
                                }
                                $this->varAppoggio = array();
                                $this->varAppoggio['CHIAVERIGA_NUM'] = $propas_rec['PASPRO'];
                                $this->varAppoggio['CHIAVERIGA_TIPO'] = $propas_rec['PASPAR'];
                                if ($_POST['rowid'] == 'ROOT' || $propas_rec['PRONODE'] == 1) {
                                    Out::msgQuestion("Nuovo", "Cosa vuoi aggiungere?", array(
                                        'Azione' => array('id' => $this->nameForm . '_AddAzione', 'model' => $this->nameForm),
                                        'Sottofascicolo' => array('id' => $this->nameForm . '_AddSottofascicolo', 'model' => $this->nameForm)
                                            )
                                    );
                                }
                                break;
                            case 'ALLEGATI':
                                if ($propas_rec['PROALL'] == "") {
                                    break;
                                }
                                $allegati = unserialize($propas_rec['PROALL']);
                                foreach ($allegati as $value) {
                                    if ($value['FILEORIG']) {
                                        $str = $str . "<br>" . $value['FILEORIG'];
                                    } else {
                                        $str = $str . "<br>" . $value['FILENAME'];
                                    }
                                }
//                                }
                                Out::msgInfo("Info Allegati", "<b>N. Allegati: " . count($allegati) . "</b><BR> $str");
                                break;
                        }
                        break;
                    case $this->gridDocumenti:
                        $chiave = $_POST['rowid'];
                        $documento = $this->proDocumenti[$chiave];
                        if ($documento[$_POST['colName']] == '' && $_POST['colName'] != 'COPIADOC') {
                            break;
                        }

                        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
                        switch ($_POST['colName']) {
                            case 'NOTEICO':
                                if ($documento['NOTEICO'] === "") {
                                    break;
                                }
                                $readonly = false;
                                if ($proges_rec['GESDCH'] || $documento['GESTIONE'] === false || substr($chiave, 0, 4) == "DOC-") {
                                    $readonly = true;
                                }
                                $pronumSottoFascicolo = $this->GetPronumSottoFascicloSelezionato($chiave);
                                $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum', $pronumSottoFascicolo);
                                if (!$permessiFascicolo[proLibFascicolo::PERMFASC_SCRIVE_NOTE]) {
                                    Out::msgStop("Attenzione", "Non hai il permesso di scrivere note.");
                                    break;
                                }
                                $destinatari = $this->proLibFascicolo->getDestinatariFascicolo($proges_rec['GESKEY']);
                                $model = 'proDettNote';
                                $_POST = array();
                                $_POST['event'] = 'openform';
                                $_POST['elenca'] = '1';
                                $_POST['destinatari'] = $destinatari;
                                $_POST['class'] = proNoteManager::NOTE_CLASS_PROTOCOLLO;
                                $_POST['chiave'] = array("PRONUM" => substr($chiave, 4, 10), "PROPAR" => substr($chiave, 14, 2));
                                $_POST['oggettoNotifica'] = "FASCIOLO {$proges_rec['GESKEY']}";
                                $_POST['readonly'] = $readonly;
                                $_POST[$model . '_returnModel'] = $this->nameForm;
                                itaLib::openForm($model);
                                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                $model();
                                break;
                            case 'ADDAZIONE':
                                $this->AggiungiAFascicolo();
                                break;
                            case 'ASSEGNAZIONI':
                                $this->GestisciAssegnazioni();
                                break;
                            case "NUMPROT":
                            case "ANNOPROT":
                            case "PROPAR":
                                if ($documento['PROTOCOLLO'] && $documento['PRONUM'] && $documento['PROPAR']) {
                                    $this->varAppoggio = $documento['PRONUM'] . $documento['PROPAR'];
                                    $arrBottoni = array();
                                    $arrBottoni['F8-Visualizza Trasmissione'] = array('id' => $this->nameForm . '_VisualizzaTrasmissione', 'model' => $this->nameForm, 'shortCut' => "f8");
                                    $anapro_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'codice', $documento['PRONUM'], $documento['PROPAR']);
                                    if ($anapro_rec) {
                                        if ($anapro_rec['PROPAR'] == 'I') {
                                            $anaent_rec = $this->proLib->GetAnaent(59);
                                            if ($anapro_rec['PROCODTIPODOC'] != '' && $anapro_rec['PROCODTIPODOC'] == $anaent_rec['ENTDE1']) {
                                                //Bottone per dettaglio se da chiamare..
                                            } else {
                                                $arrBottoni['F5-Visualizza Documentale'] = array('id' => $this->nameForm . '_VisualizzaDocumentale', 'model' => $this->nameForm, 'shortCut' => "f5");
                                            }
                                        } else {
                                            $arrBottoni['F5-Visualizza Protocollo'] = array('id' => $this->nameForm . '_VisualizzaProtocollo', 'model' => $this->nameForm, 'shortCut' => "f5");
                                        }
                                    }
                                    // Fino a quando non abilitata la ricerca dei documentali, disabilitato per le I
                                    if ($documento['PROPAR'] != 'I') {
                                        $arrBottoni['F7-Visualizza Fascicoli'] = array('id' => $this->nameForm . '_VisualizzaFascicoli', 'model' => $this->nameForm, 'shortCut' => "f7");
                                    }
                                    Out::msgQuestion("Visualizzazione documento.", "Vuoi visualizzare il dettaglio del protocollo o della trasmissione?", $arrBottoni);
                                    break;
                                }
                                break;
                            case 'EDIT':
                                $ext = pathinfo($documento['FILENAME'], PATHINFO_EXTENSION);
                                $paramSegnatura = $this->GetParamSegnaturaDoc($documento);
                                if (strtolower($ext) == "p7m") {
                                    $FilePathDest = $this->proLibAllegati->CopiaDocAllegato($documento['ROWIDANADOC'], '', true);
                                    $this->proLibAllegati->VisualizzaFirme($FilePathDest, $documento['FILEORIG'], $paramSegnatura);
                                }
                                break;

                            case 'COPIADOC':
                                if (substr($chiave, 0, 4) == "DOC-") {
                                    $sel = '<span class="ui-icon ui-icon-check" style="display: inline-block;"></span>';
                                    $daCopiare = true;
                                    if ($this->proDocumenti[$chiave]['DACOPIARE'] === true) {
                                        $sel = ' ';
                                        $daCopiare = false;
                                    }
                                    $this->proDocumenti[$chiave]['COPIADOC'] = $sel;
                                    $this->proDocumenti[$chiave]['DACOPIARE'] = $daCopiare;
                                    TableView::setCellValue($this->gridDocumenti, $chiave, 'COPIADOC', $sel, '', '', 'false');
                                }
                                break;
                            case 'PROVENIENZA':
                                $ret = proRic::proMittDestProtocollo($this->nameForm, substr($chiave, 4, 10), substr($chiave, 14, 2));
                                if (!$ret) {
                                    Out::msgBlock('', 2000, true, "Nessun Mittente/Destinatario aggiuntivo presente. ");
                                }
                                break;
                        }
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $this->elenca();
                        break;
                    case $this->nameForm . '_NuovaPratica':
                        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli();
                        if (!$permessiFascicolo[proLibFascicolo::PERMFASC_CREAZIONE]) {
                            Out::msgStop("Attenzione", "Non hai i permessi di creazione di un nuovo fascicolo.");
                            break;
                        }
                        $this->apriNuovo();
                        break;
                    case $this->nameForm . '_AddAzione':
                        $model = 'proPassoPratica';
                        $chiaveriga_num = $this->varAppoggio['CHIAVERIGA_NUM'];
                        $chiaveriga_tipo = $this->varAppoggio['CHIAVERIGA_TIPO'];
                        unset($this->varAppoggio['CHIAVERIGA_NUM']);
                        unset($this->varAppoggio['CHIAVERIGA_TIPO']);
                        $orgnode_rec = $this->proLib->GetOrgNode($chiaveriga_num, 'codice', $chiaveriga_tipo);
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['procedimento'] = $this->currGesnum;
                        $_POST['modo'] = "add";
                        $_POST['nodeRowid'] = $orgnode_rec['ROWID'];
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnProPasso';
                        $_POST[$model . '_title'] = 'Nuova Azione.....';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case $this->nameForm . '_AddSottofascicolo':
                        $this->GestisciSottoFascicolo();
                        break;

//                        $model = 'proPassoPratica';
//                        $chiaveriga_num = $this->varAppoggio['CHIAVERIGA_NUM'];
//                        $chiaveriga_tipo = $this->varAppoggio['CHIAVERIGA_TIPO'];
//                        unset($this->varAppoggio['CHIAVERIGA_NUM']);
//                        unset($this->varAppoggio['CHIAVERIGA_TIPO']);
//                        $orgnode_rec = $this->proLib->GetOrgNode($chiaveriga_num, 'codice', $chiaveriga_tipo);
//                        $_POST = array();
//                        $_POST['event'] = 'openform';
//                        $_POST['procedimento'] = $this->currGesnum;
//                        $_POST['modo'] = "addNode";
//                        $_POST['nodeRowid'] = $orgnode_rec['ROWID'];
//                        $_POST[$model . '_returnModel'] = $this->nameForm;
//                        $_POST[$model . '_returnMethod'] = 'returnProPasso';
//                        $_POST[$model . '_title'] = 'Nuovo Sotto Fascicolo';
//                        itaLib::openForm($model);
//                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
//                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//                        $model();
//                        break;



                    case $this->nameForm . '_AddDocumentale':
                        $chiave = $_POST[$this->nameForm . '_gridDocumenti']['gridParam']['selrow'];
                        $orgnode_rec = $this->proLib->GetOrgNode(substr($chiave, 4, 10), 'codice', substr($chiave, 14, 2));
                        $this->varAppoggio = array();
                        $this->varAppoggio['ADDPROTOCOLLONODE'] = $orgnode_rec;
                        $DefaultRicerca = array();
                        $DefaultRicerca['CONTITOLARIO'] = '1';
                        $DefaultRicerca['ANNO'] = date('Y');
                        $this->segLib->OpenRicercaDocumentale($this->nameForm, $DefaultRicerca);
                        break;

                    case $this->nameForm . '_ConfermaAddDocumentale':
                        $Indice_rec = $this->segLib->GetIndice($this->returnData['ROWID_INDICE'], 'rowid');
                        if (!$this->proLibFascicolo->insertDocumentoFascicolo(
                                        $this
                                        , $this->varAppoggio['ADDPROTOCOLLONODE']['ORGKEY']
                                        , $Indice_rec['INDPRO']
                                        , $Indice_rec['INDPAR']
                                        , $this->varAppoggio['ADDPROTOCOLLONODE']['PRONUM']
                                        , $this->varAppoggio['ADDPROTOCOLLONODE']['PROPAR']
                                )
                        ) {
                            Out::msgStop("Attenzione! - Fascicolazione", $this->proLibFascicolo->getErrMessage());
                        } else {
                            Out::msgBlock('', 3000, true, "Fascicolazione avvenuta con successo per il protocollo N° " . (int) $numero . " / $anno - $tipo");
                            $this->Dettaglio($this->geskey, 'geskey');
                        }
                        break;

                    case $this->nameForm . '_AddProtocollo':
                        //Out::msgInfo("Protocollazione", "Funzione non ancora realizzata");
                        $campi[] = array(
                            'label' => array(
                                'value' => 'Tipo',
                                'style' => 'width:80px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_AddTipo',
                            'name' => $this->nameForm . '_AddTipo',
                            'type' => 'select',
                            'options' => array(
                                array("", "", true),
                                array("A", "A"),
                                array("P", "P"),
                                array("C", "C")
                            ),
                            'class' => 'required');
                        $campi[] = array(
                            'label' => array(
                                'value' => 'Numero',
                                'style' => 'width:80px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_AddNumero',
                            'name' => $this->nameForm . '_AddNumero',
                            'type' => 'text',
                            'size' => '8',
                            'maxchars' => '6',
                            'class' => 'required');
                        $campi[] = array(
                            'label' => array(
                                'value' => 'Anno ',
                                'style' => 'width:80px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_AddAnno',
                            'name' => $this->nameForm . '_AddAnno',
                            'type' => 'text',
                            'size' => '6',
                            'maxchars' => '4',
                            'class' => 'ita-edit-lookup ita-edit-onblur required');
                        Out::msgInput(
                                'Seleziona il protocollo da fascicolare', $campi, array(
                            'F5-Conferma Protocollo' => array('id' => $this->nameForm . '_ConfermaAddProtocollo', 'model' => $this->nameForm, 'shortCut' => "f5"),
                            'F8-Annulla Selezione' => array('id' => $this->nameForm . '_AnnullaSelezioneAddProt', 'model' => $this->nameForm, 'shortCut' => "f8")
                                ), $this->nameForm
                        );
                        $chiave = $_POST[$this->nameForm . '_gridDocumenti']['gridParam']['selrow'];
                        $orgnode_rec = $this->proLib->GetOrgNode(substr($chiave, 4, 10), 'codice', substr($chiave, 14, 2));
                        $this->varAppoggio = array();
                        $this->varAppoggio['ADDPROTOCOLLONODE'] = $orgnode_rec;
                        break;
                    case $this->nameForm . '_AddAnno_butt':
                        $anapro_F_rec = $this->proLibFascicolo->getAnaproFascicolo($this->geskey);
                        $model = 'proGest';
                        itaLib::openDialog($model, true);
                        $objModel = itaModel::getInstance($model);
                        $objModel->setReturnModel($this->nameForm);
                        $objModel->setReturnEvent('returnFromProGest');
                        $objModel->setReturnId($this->nameForm . '_AddProtocollo');
                        $objModel->setEvent('openform');
                        $objModel->setConsultazione(true);
                        $objModel->setCondizioni(array('TITOLARIO' => array('VALORE' => $anapro_F_rec['PROCCF'], 'NASCONDI' => false))); //, 'EXTRA' => " AND ANAPRO.PROFASKEY=''"));
                        $objModel->parseEvent();
                        break;
                    case $this->nameForm . '_ConfermaAddProtocollo':
                        $anno = $_POST[$this->nameForm . '_AddAnno'];
                        $tipo = $_POST[$this->nameForm . '_AddTipo'];
                        if ($_POST[$this->nameForm . '_AddNumero'] && $anno && $tipo) {
                            $numero = str_pad($_POST[$this->nameForm . '_AddNumero'], 6, "0", STR_PAD_LEFT);
                            $risultato = $this->checkAssegnaProtocolloAlFascicolo($anno . $numero, $tipo, false);
                            if ($risultato['ASSEGNA'] === true) {
                                if (!$this->proLibFascicolo->insertDocumentoFascicolo(
                                                $this
                                                , $this->varAppoggio['ADDPROTOCOLLONODE']['ORGKEY']
                                                , $risultato['ANAPRO_C']['PRONUM']
                                                , $risultato['ANAPRO_C']['PROPAR']
                                                , $this->varAppoggio['ADDPROTOCOLLONODE']['PRONUM']
                                                , $this->varAppoggio['ADDPROTOCOLLONODE']['PROPAR']
                                        )
                                ) {
                                    Out::msgStop("Attenzione! - Fascicolazione", $this->proLibFascicolo->getErrMessage());
                                } else {
                                    Out::msgBlock('', 3000, true, "Fascicolazione avvenuta con successo per il protocollo N° " . (int) $numero . " / $anno - $tipo");
                                    $this->Dettaglio($this->geskey, 'geskey');
                                }
                            }
                        }
                        break;
                    case $this->nameForm . '_AddDocumento':
                        $acq_model = 'utiAcqrMen';
                        Out::closeDialog($acq_model);
                        $_POST = array();
                        $_POST[$acq_model . '_flagPDFA'] = '';
                        $_POST[$acq_model . '_returnModel'] = $this->nameForm;
                        $_POST[$acq_model . '_returnField'] = $this->gridDocumenti;
                        $_POST[$acq_model . '_returnMethod'] = 'returnAcqrList';
                        $_POST[$acq_model . '_title'] = 'Aggiungi documenti al fascicolo';
                        $_POST[$acq_model . '_tipoNome'] = 'original';
                        $_POST['event'] = 'openform';
                        itaLib::openForm($acq_model);
                        $appRoute = App::getPath('appRoute.' . substr($acq_model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $acq_model . '.php';
                        $acq_model();
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $proges_rec = $_POST[$this->nameForm . '_PROGES'];
                        if ($_POST[$this->nameForm . '_Numero_prot'] != 0 && $_POST[$this->nameForm . '_Anno_prot'] == 0) {
                            Out::msgInfo("ATTENZIONE", "Inserire l'anno per il protocollo n. " . $_POST[$this->nameForm . '_Numero_prot']);
                            break;
                        }
                        $versione_t = $_POST[$this->nameForm . '_ANAORG']['VERSIONE_T'];
                        $titolario = $_POST[$this->nameForm . '_Catcod'] . $_POST[$this->nameForm . '_Clacod'] . $_POST[$this->nameForm . '_Fascod'];

                        /*
                         * SERIE:
                         * Controlli su serie.
                         */
                        $Serie_rec = $_POST[$this->nameForm . '_SERIE'];
                        if (!$this->proLibSerie->ControlloDatiObbligatoriSerie($Serie_rec, $titolario, $versione_t)) {
                            if ($Serie_rec) {
                                Out::msgStop("Attenzione.", $this->proLibSerie->getErrMessage());
                                break;
                            } else {
                                Out::msgInfo("Attenzione.", $this->proLibSerie->getErrMessage());
                            }
                        }
                        $AnaorgRec = $_POST[$this->nameForm . '_ANAORG'];
                        /*
                         * Aggiunta fascicolo
                         */
                        $ret_aggiungi = $this->aggiungi(array(
                            "PROGES_REC" => $proges_rec,
                            "VERSIONE_T" => $versione_t,
                            "TITOLARIO" => $titolario,
                            "tipoInserimento" => "ANAGRAFICA",
                            "SERIE" => $Serie_rec, /* Passo i dati della serie */
                            "DATI_ANAORG" => $AnaorgRec/* Passo i dati di ANAORG */
                        ));
                        if ($ret_aggiungi != false) {
                            $this->Dettaglio($ret_aggiungi);
                        } else {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_AnnullaPDFA':
                        if ($this->currAllegato['uplFile']) {
                            unlink($this->currAllegato['uplFile']);
                            Out::msgInfo('Allega PDF', "Allegato Rifiutato:" . $this->currAllegato['uplFile']);
                        }
                        break;
                    case $this->nameForm . '_ConfermaPDFA':
                        if (!@rename($this->currAllegato['uplFile'], $this->currAllegato['destFile'])) {
                            Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                        } else {
                            $this->aggiungiAllegato($this->currAllegato['randName'], $this->currAllegato['destFile'], $this->currAllegato['origFile']);
                            Out::msgInfo('Allega PDF', "Allegato PDF Accettato nonostante la non Conformità a PDF/A:" . $this->currAllegato['origFile']);
                        }
                        break;
                    case $this->nameForm . '_ConvertiPDFA':
                        $retConvert = $this->praLib->convertiPDFA($this->currAllegato['uplFile'], $this->currAllegato['destFile'], true);

                        if ($retConvert['status'] == 0) {
                            $this->aggiungiAllegato($this->currAllegato['randName'], $this->currAllegato['destFile'], $this->currAllegato['origFile']);
                            Out::msgInfo('Allega PDF', "Allegato PDF Convertito a PDF/A: " . $this->currAllegato['origFile']);
                        } else {
                            Out::msgStop("Conversione PDF/A Impossibile", $retConvert['message']);
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
                        if ($proges_rec['GESDCH']) {
                            Out::msgInfo("Attenzione", "Fascicolo Chiuso. <br/>Non Aggiornato.");
                            break;
                        }
                        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum');
                        if (!$permessiFascicolo[proLibFascicolo::PERMFASC_MODIFICA_DATI]) {
                            Out::msgStop("Attenzione", "Non disponi dei permessi necessari per alterare informazioni del fascicolo.");
                            break;
                        }
                        /* SERIE: Se sto inserendo la serie. */
                        /* Controlli Serie */
                        $Serie_rec = $_POST[$this->nameForm . '_SERIE'];
                        $AnaorgPre_rec = $this->proLib->GetAnaorg($this->geskey, 'orgkey');
                        if (!$this->proLibSerie->ControlloDatiObbligatoriSerie($Serie_rec, $AnaorgPre_rec['ORGCCF'], $AnaorgPre_rec['VERSIONE_T'], $AnaorgPre_rec['ORGKEY'])) {
                            Out::msgStop("Attenzione.", $this->proLibSerie->getErrMessage());
                            break;
                        }

                        if ($this->fl_manutenzioneSerie) {
                            if (!$this->proLibSerie->AggiornaSerieFascicolo($AnaorgPre_rec, $Serie_rec['CODICE'], $Serie_rec['PROGSERIE'])) {
                                Out::msgStop("Attenzione", $this->proLibSerie->getErrMessage());
                                break;
                            }
                        } else {
                            /* Controlli Controllo se devo aggiungere la serie al fascicolo */
                            if ($Serie_rec['CODICE'] && !$AnaorgPre_rec['CODSERIE']) {
                                $AnaSerie = $this->proLibSerie->GetSerie($Serie_rec['CODICE'], 'codice');
                                if (!$this->proLibSerie->AggiungiSerieAFascicolo($Serie_rec['CODICE'], $AnaorgPre_rec['ORGCCF'], $AnaorgPre_rec['VERSIONE_T'], $AnaorgPre_rec, $Serie_rec['PROGSERIE'])) {
                                    Out::msgStop("Attenzione..", $this->proLibSerie->getErrMessage());
                                    break;
                                }
                            }
                        }


                        $dati['PROGES_REC'] = $_POST[$this->nameForm . '_PROGES'];
                        $dati['ANADES_REC'] = $_POST[$this->nameForm . '_ANADES'];
                        $dati['DATI_ANAORG'] = $_POST[$this->nameForm . '_ANAORG'];
                        $dati['forzaChiusura'] = false;
                        $ret_aggiungi = $this->aggiorna($dati);
                        if ($this->returnModel != '') {
                            $this->returnToParent();
                        } else {
                            if ($ret_aggiungi != '') {
                                $this->Dettaglio($ret_aggiungi);
                            }
                        }
                        break;
                    case $this->nameForm . '_Chiudi':
                        if ($this->attivaAzioni) {
                            $msg = "<b>Confermando verrà chiusa la pratica n. $this->currGesnum.<br>Se sei sicuro di procedere, scegli uno stato.</b>";
                            praRic::praRicAnastp($this->nameForm, "WHERE STPFLAG LIKE 'Chiusa%'", "CHIUDI", $msg);
                        } else {
                            // Controlla se è tutto chiuso.
                            $IterAperti = $this->proLibFascicolo->ControllaFascicoloIterAperti($this->currGesnum);
                            if (!$IterAperti) {
                                $this->ChiediDatiChiusura();
                            } else {
                                $Messaggio = 'Non è possibile chiudere il fascicolo.<br>';
                                $Messaggio .= implode('<br>', $IterAperti);
                                Out::msgInfo("Attenzione", $Messaggio);
                                App::log($IterAperti);
                            }
                        }
                        break;
                    case $this->nameForm . '_ConfermaChiusuraFascicolo':
                        $dati['PROGES_REC'] = $_POST[$this->nameForm . '_PROGES'];
                        $dati['ANADES_REC'] = $_POST[$this->nameForm . '_ANADES'];
                        //@TODO FORZA CHIUSURA DA TOGLIERE
                        $dati['forzaChiusura'] = false;
                        $rowid = $this->aggiorna($dati);
                        if (!$this->proLibFascicolo->chiudiFascicolo($this, $this->currGesnum, $this->formData[$this->nameForm . '_DataChiusuraFascicolo'])) {
                            Out::msgStop("Errore", $this->proLibFascicolo->getErrMessage());
                        }
                        $this->Dettaglio($this->currGesnum, 'codice');
                        break;
                    case $this->nameForm . '_ConfermaDettaglioPratica':
                        $this->Dettaglio($this->varAppoggio);
                        break;
                    case $this->nameForm . '_Stampa':
                        devRic::devElencoReport($this->nameForm, $_POST, " WHERE CODICE<>'' AND CATEGORIA='PROTFASCICOLI'", 'Elenco');
                        break;
                    case $this->nameForm . '_StampaDettaglio':
                        devRic::devElencoReport($this->nameForm, $_POST, " WHERE CODICE<>'' AND CATEGORIA='FASCICOLIDETTAGLIO'", 'Dettaglio');
                        break;
                    case $this->nameForm . '_Etichetta':
                        $rowid = $_POST[$this->nameForm . '_PROGES']['ROWID'];
                        $model = 'proStampaEtichettaPratica';
                        itaLib::openForm($model, true);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['tipo'] = "4";
                        $_POST['chiave'] = $rowid;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $model();
                        break;
                    case $this->nameForm . '_Repertorio_butt':
                        proric::proRicAnareparc($this->nameForm);
                        break;
                    case $this->nameForm . '_catcod_butt':
                        //proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, array(), 'returnTitolarioRic');
                        $versione_t = $_POST[$this->nameForm . '_Versione'];
                        proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $versione_t, array(), 'returnTitolarioRic');
                        break;
                    case $this->nameForm . '_clacod_butt':
                        if ($_POST[$this->nameForm . '_catcod']) {
                            $where = array('ANACAT' => " AND CATCOD='" . $_POST[$this->nameForm . '_catcod'] . "'");
                        }
                        //proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where, 'returnTitolarioRic');
                        $versione_t = $_POST[$this->nameForm . '_Versione'];
                        proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $versione_t, $where, 'returnTitolarioRic');
                        break;
                    case $this->nameForm . '_fascod_butt':
                        if ($_POST[$this->nameForm . '_Catcod']) {
                            $where['ANACAT'] = " AND CATCOD='" . $_POST[$this->nameForm . '_catcod'] . "'";
                            if ($_POST[$this->nameForm . '_clacod']) {
                                $where['ANACLA'] = " AND CLACCA='" . $_POST[$this->nameForm . '_catcod'] . $_POST[$this->nameForm . '_clacod'] . "'";
                            }
                        }
                        //proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where, 'returnTitolarioRic');
                        $versione_t = $_POST[$this->nameForm . '_Versione'];
                        proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $versione_t, $where, 'returnTitolarioRic');
                        break;
                    case $this->nameForm . '_Procedimento_butt':
                        $this->dataRegAppoggio = "";
                        praRic::praRicAnapra($this->nameForm, "Ricerca procedimento", $this->nameForm . '_Procedimento');
                        break;
                    case $this->nameForm . '_PROGES[GESPRO]_butt':
                        $this->dataRegAppoggio = $_POST[$this->nameForm . '_PROGES']['GESDRE'];
                        praRic::praRicAnapra($this->nameForm, "Ricerca procedimento", $this->nameForm . '_PROGES[GESPRO]');
                        break;
                    case $this->nameForm . '_Passo_butt':
                        praRic::praRicPraclt($this->nameForm);
                        break;
                    case $this->nameForm . '_Aggregato_butt':
                        praRic:: praRicAnaspa($this->nameForm, '', "1");
                        break;
                    case $this->nameForm . '_Sportello_butt':
                        praRic:: praRicAnatsp($this->nameForm, "", "2");
                        break;
                    case $this->nameForm . '_Sett_butt':
                        praRic:: praRicAnaset($this->nameForm);
                        break;
                    case $this->nameForm . '_Responsabile_butt':
                        proRic::proRicAnamed($this->nameForm, "WHERE MEDUFF<>'' ", '', $this->nameForm . '_Responsabile', 'returnUnires');
                        break;
                    case $this->nameForm . '_NomeCampo_butt':
                        praRic::praRicPraidc($this->nameForm, 'returnPraidcRic');
                        break;
                    case $this->nameForm . "_DescRuolo_butt":
                        praRic::praRicRuoli($this->nameForm);
                        break;
                    case $this->nameForm . "_Nominativo_butt":
                        praRic::praRicAnades($this->nameForm);
                        break;
                    case $this->nameForm . "_DestinatarioFascicolo_butt":
                        proRic::proRicAnamed($this->nameForm, "WHERE MEDUFF<>'' ", '', $this->nameForm . '_PROGES[GESRES]', 'returnanamedMittPartenzaRic');
                        break;
                    // **** Molto probabilmente non più usate.
                    case $this->nameForm . '_Scanner':
                        Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDocumenti");
                        $this->ApriScanner();
                        break;
                    case $this->nameForm . '_FileLocale':
                        $this->AllegaFile();
                        break;
                    // **** fine...
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
//                    case $this->nameForm . '_ConfermaCancDato':
//                        if (array_key_exists($this->varAppoggio, $this->praDatiPratica) == true) {
//                            if ($this->praDatiPratica[$this->varAppoggio]['ROWID'] != 0) {
//                                $delete_Info = 'Oggetto: Cancellazione dato aggiuntivo ' . $this->praDatiPratica[$this->varAppoggio]['DAGKEY'];
//                                if (!$this->deleteRecord($this->PROT_DB, 'PRODAG', $this->praDatiPratica[$this->varAppoggio]['ROWID'], $delete_Info)) {
//                                    Out::msgStop("Attenzione", "Errore in cancellazione del dato aggiuntivo su PRODAG");
//                                }
//                            }
//                            unset($this->praDatiPratica[$this->varAppoggio]);
//                        }
//                        $this->ordinaSeqArrayDag();
//                        $this->CaricaGriglia($this->gridDatiPratica, $this->praDatiPratica);
//                        break;
//@TODO DA VERIFICARE                        
//                    case $this->nameForm . '_ConfermaCancSogg':
//                        if (!$this->praSoggetti->CancellaSoggetto($this->varAppoggio, $this)) {
//                            Out::msgStop("Attenzione", "Errore in cancellazione soggetto su ANADES");
//                        }
//                        $this->CaricaGriglia($this->gridSoggetti, $this->praSoggetti->getGriglia());
//                        break;
                    case $this->nameForm . '_ConfermaCancAlle':
                        Out::msgBlock("", 2000, true, "Funzione non attiva entrare nel dettaglio del fascicolo");
                        break;
                    case $this->nameForm . '_ConfermaVis':
                        $model = 'proPassoPratica';
                        $rowid = $_POST[$this->nameForm . '_Appoggio'];
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $rowid;
                        $_POST['modo'] = "edit";
//$_POST['perms'] = $this->perms;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnProPasso';
                        $_POST[$model . '_title'] = 'Gestione Azione.....';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_Apri':
                        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum');
                        if (!$permessiFascicolo[proLibFascicolo::PERMFASC_RIAPRI_FASCICOLO]) {
                            Out::msgStop("Attenzione", "Non hai il permesso di aprire il fascicolo.");
                            break;
                        }
                        Out::msgQuestion("ATTENZIONE!", "Sei sicuro di voler aprire il Fascicolo n. " . $this->geskey . "?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaApePra', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaApePra', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaApePra':
                        $risultato = $this->proLibFascicolo->riapriFascicolo($this, $this->currGesnum);
                        if (!$risultato) {
                            Out::msgStop("Attenzione!", $this->proLibFascicolo->getErrMessage());
                            break;
                        }
                        $this->Dettaglio($this->currGesnum, 'codice');
                        break;
                    case $this->nameForm . '_Torna':
                        $this->TornaElenco();
                        break;
                    case $this->nameForm . '_ConfermaDaAltraPratica':
                        praRic::praRicProges($this->nameForm, " AND GESNUM<>$this->currGesnum", "IMPORTAPASSI");
                        break;
                    case $this->nameForm . '_CopiaUltimo':
                        $propas_tab = ItaDB::DBSQLSelect($this->PROT_DB, "SELECT * FROM PROPAS WHERE PRONUM='$this->currGesnum' ORDER BY PROSEQ", true);
                        $last_propas_rec = end($propas_tab);
                        $_POST = array();
                        $model = 'proPassoPassoPratica'; // ?????????????????????
                        $_POST['event'] = 'openform';
                        $_POST['last_propas_rec'] = $last_propas_rec;
                        $_POST['modo'] = "duplica";
                        $_POST['procedimento'] = $this->currGesnum;
                        $_POST['perms'] = $this->perms;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnPraPasso';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case $this->nameForm . '_VisualizzaTrasmissione':
                        $pronum = substr($this->varAppoggio, 0, 10);
                        $propar = substr($this->varAppoggio, 10);
                        $this->apriFormTrasmissione($pronum, $propar, 'visualizzazione');
                        break;
                    case $this->nameForm . '_VisualizzaProtocollo':
                        $pronum = substr($this->varAppoggio, 0, 10);
                        $propar = substr($this->varAppoggio, 10);
                        $anapro_rec = $this->proLib->GetAnapro($pronum, 'codice', $propar);
                        $model = 'proArri';
                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnProArri');
                        $formObj->setFascicoloDiProvenienza($this->geskey);
                        $formObj->setReturnId('');
                        $_POST = array();
                        $_POST['datiANAPRO']['ROWID'] = $anapro_rec['ROWID'];
                        $_POST['tipoProt'] = $anapro_rec['PROPAR'];
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        Out::setFocus('', 'proArri_Propre1');
                        break;

                    case $this->nameForm . '_VisualizzaDocumentale':
                        $pronum = substr($this->varAppoggio, 0, 10);
                        $propar = substr($this->varAppoggio, 10);
                        $anapro_rec = $this->proLib->GetAnapro($pronum, 'codice', $propar);
                        $Indice_rec = $this->segLib->GetIndice($anapro_rec['PRONUM'], 'anapro', false, $anapro_rec['PROPAR']);

                        $retCtrAccess = $this->segLib->ControlloAccessibilitaAtto($Indice_rec['ROWID']);
                        if (!$retCtrAccess) {
                            Out::msgInfo("Attenzione", "Atto non accessibile");
                            break;
                        }
                        $segLibDocumenti = new segLibDocumenti();
                        if (!$segLibDocumenti->ApriAtto($this->nameForm, $Indice_rec)) {
                            Out::msgStop("Attenzione", $segLibDocumenti->getErrMessage());
                        }
                        break;

                    case $this->nameForm . '_Riserva':
                        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum');
                        if (!$permessiFascicolo[proLibFascicolo::PERMFASC_MODIFICA_DATI]) {
                            Out::msgStop("Attenzione", "Non disponi dei permessi necessari per alterare informazioni del fascicolo.");
                            break;
                        }
                        $anapro_fascicolo = $this->proLibFascicolo->getAnaproFascicolo($this->geskey);
                        $anapro_fascicolo['PRORISERVA'] = 1;
                        Out::hide($this->nameForm . '_Riserva');
                        Out::show($this->nameForm . '_NonRiserva');
                        Out::show($this->nameForm . '_protRiservato');
                        Out::valore($this->nameForm . '_protRiservato', 'RISERVATO');
                        $update_Info = 'Oggetto: Aggiornamento riservato su anapro: ' . $anapro_fascicolo['PROSEG'];
                        $this->updateRecord($this->PROT_DB, 'ANAPRO', $anapro_fascicolo, $update_Info);
                        $this->CaricaAllegati();
                        break;
                    case $this->nameForm . '_NonRiserva':
                        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum');
                        if (!$permessiFascicolo[proLibFascicolo::PERMFASC_MODIFICA_DATI]) {
                            Out::msgStop("Attenzione", "Non disponi dei permessi necessari per alterare informazioni del fascicolo.");
                            break;
                        }
                        $anapro_fascicolo = $this->proLibFascicolo->getAnaproFascicolo($this->geskey);
                        $anapro_fascicolo['PRORISERVA'] = '';
                        Out::show($this->nameForm . '_Riserva');
                        Out::hide($this->nameForm . '_NonRiserva');
                        Out::hide($this->nameForm . '_protRiservato');
                        $update_Info = 'Oggetto: Aggiornamento riservato su anapro: ' . $anapro_fascicolo['PROSEG'];
                        $this->updateRecord($this->PROT_DB, 'ANAPRO', $anapro_fascicolo, $update_Info);
                        $this->CaricaAllegati();
                        break;
                    case $this->nameForm . '_Catcod_butt':
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm);
                        break;
                    case $this->nameForm . '_Clacod_butt':
                        if ($_POST[$this->nameForm . '_Catcod']) {
                            $where = array('ANACAT' => " AND CATCOD='" . $_POST[$this->nameForm . '_Catcod'] . "'");
                        }
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where);
                        break;
                    case $this->nameForm . '_Fascod_butt':
                        if ($_POST[$this->nameForm . '_Catcod']) {
                            $where['ANACAT'] = " AND CATCOD='" . $_POST[$this->nameForm . '_Catcod'] . "'";
                            if ($_POST[$this->nameForm . '_Clacod']) {
                                $where['ANACLA'] = " AND CLACCA='" . $_POST[$this->nameForm . '_Catcod']
                                        . $_POST[$this->nameForm . '_Clacod'] . "'";
                            }
                        }
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where);
//                        }
                        break;
                    case $this->nameForm . '_UFFRESP_butt':
                        $codice = $_POST[$this->nameForm . '_PROGES']['GESRES'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $anamed_rec = $this->proLib->GetAnamed($codice, 'codice', 'no');
                            if ($anamed_rec) {
                                proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', '', 'Firmatario');
                            }
                        }
                        break;
                    case $this->nameForm . '_PROGES[GESRES]_butt':
                        $filtroUff = " WHERE MEDUFF" . $this->PROT_DB->isNotBlank();
                        proRic::proRicAnamed($this->nameForm, $filtroUff, '', '', 'returnanamedMittPartenza');
                        break;
                    case $this->nameForm . '_Struttura':
                        Out::show($this->nameForm . '_Cronologia');
                        Out::show($this->nameForm . '_divStrutturaIter');
                        Out::hide($this->nameForm . '_Struttura');
                        Out::hide($this->nameForm . '_divCronologiaIter');
                        $this->CaricaStrutturaIter($this->geskey);
                        $this->CaricaGriglia($this->gridStrutturaIter, $this->proIter);
                        Out::codice("resizeGrid('" . $this->nameForm . "_divStrutturaIter', false, true);");
                        break;
                    case $this->nameForm . '_Cronologia':
                        Out::hide($this->nameForm . '_Cronologia');
                        Out::hide($this->nameForm . '_divStrutturaIter');
                        Out::show($this->nameForm . '_Struttura');
                        Out::show($this->nameForm . '_divCronologiaIter');
                        $this->CaricaIter($this->geskey);
                        $this->CaricaGriglia($this->gridIter, $this->proIter);
                        break;

                    case $this->nameForm . '_ConfermaSganciaDoc':
                        $this->SganciaDocumento();
                        break;

                    case $this->nameForm . '_SpostaDocumento':
                        $rowid = $_POST[$this->gridDocumenti]['gridParam']['selarrrow'];
                        $rowid_orgconn = $this->proDocumenti[$rowid]['ROWID_ORGCONN'];
                        if ($rowid_orgconn) {
                            $this->rowidDocumentoDaSpostare = $rowid;
                            $Orgconn_rec = $this->proLib->GetOrgConn($rowid_orgconn, 'rowid');
                            $Anapro_rec = $this->proLib->GetAnapro($Orgconn_rec['PRONUM'], 'codice', $Orgconn_rec['PROPAR']);
                            $this->SelezionaAltroFascicolo($Anapro_rec);
                        }
                        break;
                    case $this->nameForm . '_SpostaDocumentoInterno':
                        $rowid = $_POST[$this->gridDocumenti]['gridParam']['selarrrow'];
                        $rowid_orgconn = $this->proDocumenti[$rowid]['ROWID_ORGCONN'];
                        if ($rowid_orgconn) {
                            $this->rowidDocumentoDaSpostare = $rowid;
                            $Orgconn_rec = $this->proLib->GetOrgConn($rowid_orgconn, 'rowid');
                            $Anapro_rec = $this->proLib->GetAnapro($Orgconn_rec['PRONUM'], 'codice', $Orgconn_rec['PROPAR']);
                            $this->SelezionaAltroFascicolo($Anapro_rec, true);
                        }
                        break;

                    case $this->nameForm . '_ConfermaTitolarioDiff':
                        /* Aggiungo il protocollo nel nuovo fascicolo */
                        $numero = substr($this->AnaproTitolarioDifferente['PRONUM'], 4);
                        $anno = substr($this->AnaproTitolarioDifferente['PRONUM'], 0, 4);
                        $tipo = $this->AnaproTitolarioDifferente['PROPAR'];
                        if (!$this->proLibFascicolo->insertDocumentoFascicolo(
                                        $this
                                        , $this->varAppoggio['ADDPROTOCOLLONODE']['ORGKEY']
                                        , $this->AnaproTitolarioDifferente['PRONUM']
                                        , $this->AnaproTitolarioDifferente['PROPAR']
                                        , $this->varAppoggio['ADDPROTOCOLLONODE']['PRONUM']
                                        , $this->varAppoggio['ADDPROTOCOLLONODE']['PROPAR']
                                )
                        ) {
                            Out::msgStop("Attenzione! Fascicolo -", $this->proLibFascicolo->getErrMessage());
                        } else {
                            $dizionarioIdelib = $this->segLib->getDizionarioFormIdelib($Indice_rec['INDTIPODOC'], $Indice_rec);
                            $NumeroInd = $dizionarioIdelib['PROGRESSIVO'];
                            $AnnoInd = substr($Indice_rec['IDATDE'], 0, 4);
                            Out::msgBlock('', 3000, true, "Fascicolazione avvenuta con del documentale " . $Indice_rec['INDTIPODOC'] . ": " . $NumeroInd . " / $AnnoInd - $tipo");
                            $this->Dettaglio($this->geskey, 'geskey');
                        }
                        $this->AnaproTitolarioDifferente = array();
                        break;

                    case $this->nameForm . '_AddRepertorioEst':
                        /*
                         * in fase di realizzazione
                         */
                        break;
                        Out::msgInfo("Repertorio Esterno", "In fase di implementazione");
                        $anapro_F_rec = $this->proLibFascicolo->getAnaproFascicolo($this->geskey);
                        $anapro_F_rec['PROCCF'];
                        $DatiProt = array();
                        $DatiProt['TITOLARIO'] = $anapro_F_rec['PROCCF'];
                        $subPath = "proAddRepertorioEst" . md5(microtime());
                        $tempOutPath = itaLib::createAppsTempPath($subPath);
                        $model = 'proDatiBase';
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnDatiBasePro');
                        $formObj->setDatiProtocollo($DatiProt);
                        $formObj->setTmpDir($tempOutPath);
                        $formObj->setApriProtocollo(true);
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                    case $this->nameForm . '_VisualizzaFascicoli':
                        $pronum = substr($this->varAppoggio, 0, 10);
                        $propar = substr($this->varAppoggio, 10);

                        $this->OpenRicerca();
                        Out::valore($this->nameForm . '_AnnoProt', substr($pronum, 0, 4));
                        Out::valore($this->nameForm . '_Pronum', substr($pronum, 4));
                        Out::valore($this->nameForm . '_Propar', substr($propar, 0, 1));

                        Out::hide($this->divRic, '');
                        Out::hide($this->divGes, '');
                        Out::show($this->divRis, '');
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_Stampa');

                        TableView::enableEvents($this->gridGest);
                        TableView::reload($this->gridGest);
                        break;

                    case $this->nameForm . '_SERIE[CODICE]_butt':
                        $versione_t = $_POST[$this->nameForm . '_ANAORG']['VERSIONE_T'];
                        $titolario = $_POST[$this->nameForm . '_Catcod'] . $_POST[$this->nameForm . '_Clacod'] . $_POST[$this->nameForm . '_Fascod'];
                        if ($this->proLibSerie->CtrSerieObbligatoria($titolario, $versione_t)) {
                            proRic::proRicSeriePerTitolario($this->nameForm, $titolario, $versione_t);
                        } else {
                            Out::msgInfo("Attenzione", $this->proLibSerie->getErrMessage());
                        }
                        break;
                    case $this->nameForm . '_ric_codiceserie_butt':
                        proRic::proRicSerieArc($this->nameForm);
                        break;
                    case $this->nameForm . '_MOD_SERIE':
                        Out::msgQuestion("Attenzione!", "Stai per attivare la manutenzione della serie, che può influire nella segnatura del fascicolo. Vuoi Continuare?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaConfermaSbloccaSerie', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaSbloccaSerie', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;

                    case $this->nameForm . '_ConfermaSbloccaSerie':
                        $this->fl_manutenzioneSerie = true;
                        $this->SbloccaSerie();
                        Out::attributo($this->nameForm . '_SERIE[PROGSERIE]', "readonly", '1');
                        Out::delClass($this->nameForm . '_SERIE[PROGSERIE]', "ita-readonly");
                        break;


                    case $this->nameForm . '_ANAORG[ORGKEYPRE]_butt':
                        $this->SelezionaFascicoloCollegato();
                        break;

                    case $this->nameForm . '_CANC_FASPRE':
                        if ($_POST[$this->nameForm . '_ANAORG']['ORGKEYPRE']) {
                            // Question..
                            Out::msgQuestion("Attenzione", "Vuoi rimuovere il Fascicolo Collegato?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaFasCollegato', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaFasCollegato', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;

                    case $this->nameForm . '_ConfermaFasCollegato':
                        Out::valore($this->nameForm . '_ANAORG[ORGKEYPRE]', '');
                        break;
                    case $this->nameForm . '_FascicoliCollegati':
                        if ($this->geskey) {
                            $anaorg_rec = $this->proLib->GetAnaorg($this->geskey, 'orgkey');
                            proRic::proRicLegameFascicoli($this->proLib, $this->nameForm, 'returnFasCollegati', $this->PROT_DB, $anaorg_rec);
                        }
                        break;

                    case $this->nameForm . '_CaricaIter':
                        $this->CaricaIter($this->geskey);
                        $this->CaricaGriglia($this->gridIter, $this->proIter);
                        Out::show($this->nameForm . '_Struttura');
                        break;

                    case $this->nameForm . '_ProtocollaAllegatiFas':
                        /* Se già scelto il protocollo devo allegare: */
                        if ($this->RowidAllegaAProtocollo) {
                            $this->ProtocollaDocSelezionati('', $this->RowidAllegaAProtocollo);
                            break;
                        }
                        $profilo = proSoggetto::getProfileFromIdUtente();
                        // Tutti o solo partenze:
                        if ($profilo['PROT_ABILITATI'] == '' || $profilo['PROT_ABILITATI'] == '2') {
                            Out::msgQuestion("Protocollo.", "Seleziona il Tipo di Protocollo:", array(
                                'F6-Documento Formale' => array('id' => $this->nameForm . '_CopiaDocFormali', 'model' => $this->nameForm, 'shortCut' => "f6"),
                                'F8-Partenza' => array('id' => $this->nameForm . '_CopiaDocPartenza', 'model' => $this->nameForm, 'shortCut' => "f8")
                                    )
                            );
                            // Arrivi o Solo Doc. Formali.
                        } else if ($profilo['PROT_ABILITATI'] == '1' || $profilo['PROT_ABILITATI'] == '3') {
                            Out::msgQuestion("Protocollo.", "Seleziona il Tipo di Protocollo:", array(
                                'F6-Documento Formale' => array('id' => $this->nameForm . '_CopiaDocFormali', 'model' => $this->nameForm, 'shortCut' => "f6"),
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_CopiaDocFormali':
                        $this->ProtocollaDocSelezionati('C');
                        break;
                    case $this->nameForm . '_CopiaDocPartenza':
                        $this->ProtocollaDocSelezionati('P');
                        break;
                    case $this->nameForm . '_StampaEleProtoc':
                        $this->StampaElencoDeiProtocolli();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'expandNode':
                if ($_POST['treeNodeHasChilds'] === 'false') {
                    $Padre_rec = $this->proDocumenti[$_POST['rowid']];
                    if ($Padre_rec['PRONUM']) {
                        $documenti_anadoc = $this->caricaAllegatiAnadoc($Padre_rec['PRONUM'], $Padre_rec['PROPAR'], $Padre_rec['ORGNODEKEY'], $this->caricaWhere(), intval($Padre_rec['level']), '', $Padre_rec['GESTIONE'], $Padre_rec['ROWID_ORGCONN']);
                        $this->proDocumenti = array_merge($this->proDocumenti, $documenti_anadoc);
                        TableView::treeTableAddChildren($this->gridDocumenti, $documenti_anadoc, 'idx');
                    }
                }
                break;

            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->gridDocumenti:
                        $chiave = $_POST['rowid'];
                        if (substr($chiave, 0, 4) == "PRO-") {
                            $anaogg_rec = $this->proLib->GetAnaogg(substr($chiave, 4, 10), substr($chiave, 14, 2));
                            TableView::setCellValue($this->gridDocumenti, $chiave, 'NOTE', $anaogg_rec['OGGOGG']);
                            break;
                        }

                        $codice = substr($chiave, 4);
                        list($dockey, $rowidAnadoc) = explode("-", $codice);
                        $anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, 'rowid');
//                        if ($this->proDocumenti[$_POST['rowid']]['DOCLOCK'] == 1 ||
//                                $this->proDocumenti[$_POST['rowid']]['PROTOCOLLO'] ||
//                                !$this->checkProprietario(substr($this->proDocumenti[$_POST['rowid']]['parent'], 4, 10), substr($this->proDocumenti[$_POST['rowid']]['parent'], 14))) {
//                            TableView::setCellValue($this->gridDocumenti, $_POST['rowid'], 'NOTE', $anadoc_rec['DOCNOT']);
//                            break;
//                        }
                        $this->proDocumenti[$_POST['rowid']][$_POST['cellname']] = $_POST['value'];
                        if ($anadoc_rec) {
                            switch ($_POST['cellname']) {
                                case 'NOTE':
                                    $anadoc_rec['DOCNOT'] = $_POST['value'];
                                    $info = "Aggiornamento note allegato: ";
                                    break;
                            }

                            $update_Info = "Oggetto: $info" . $anadoc_rec['DOCFIL'];
                            if (!$this->updateRecord($this->PROT_DB, 'ANADOC', $anadoc_rec, $update_Info)) {
                                Out::msgStop("Errore in Aggionamento", "Aggiornamento note documento fallito.");
                                break;
                            }
                        }
                        break;
//                    case $this->gridDati:
////
//// Se passo protetto, evito la modifica della descrizione dei dati aggiuntivi
////
//                        $dato = $this->praDati[$_POST['rowid']];
//                        $prodag_rec = $this->proLibPratica->GetProdag($dato['ROWID'], "rowid");
//                        $propas_rec = $this->proLibPratica->GetPropas($prodag_rec['DAGPAK'], "propak");
//                        if ($propas_rec['PROVISIBILITA'] == "Protetto") {
//                            $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
//                            if (!$this->praPerms->checkSuperUser($proges_rec)) {
//                                if ($this->praPerms->checkUtenteGenerico($propas_rec)) {
//                                    TableView::setCellValue($this->gridDati, $_POST['rowid'], 'DAGDES', $prodag_rec['DAGDES']);
//                                    break;
//                                }
//                            }
//                        }
//
//                        $prodag_rec['DAGDES'] = $_POST['value'];
//                        $this->praDati[$_POST['rowid']][$_POST['DAGDES']] = $_POST['value'];
//                        $update_Info = "Oggetto: Aggiorno descrizione del dato aggiuntivo " . $dato['DAGKEY'] . " della pratica n. $this->currGesnum";
//                        if (!$this->updateRecord($this->PROT_DB, 'PRODAG', $prodag_rec, $update_Info)) {
//                            Out::msgStop("ATTENZIONE!", "Errore aggiornamento dato aggiuntivo " . $dato['DAGKEY']);
//                            break;
//                        }
//                        break;
//                    case $this->gridDatiPratica:
//                        switch ($_POST['cellname']) {
//                            case "DAGSEQ":
//                                $this->praDatiPratica[$_POST['rowid']]['DAGSEQ'] = $_POST['value'];
//                                $this->ordinaSeqArrayDag();
//                                break;
//                            case "DAGVAL":
//                                if ($this->praDatiPratica[$_POST['rowid']]['DAGTIP'] == "Foglio_catasto" || $this->praDatiPratica[$_POST['rowid']]['DAGTIP'] == "Sub_catasto") {
//                                    $this->praDatiPratica[$_POST['rowid']]['DAGVAL'] = str_repeat("0", 4 - strlen(trim($_POST['value']))) . trim($_POST['value']);
//                                } elseif ($this->praDatiPratica[$_POST['rowid']]['DAGTIP'] == "Particella_catasto") {
//                                    $this->praDatiPratica[$_POST['rowid']]['DAGVAL'] = str_repeat("0", 5 - strlen(trim($_POST['value']))) . trim($_POST['value']);
//                                } else {
//                                    $this->praDatiPratica[$_POST['rowid']]['DAGVAL'] = $_POST['value'];
//                                }
//                                break;
//                        }
//                        $this->CaricaGriglia($this->gridDatiPratica, $this->praDatiPratica);
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Versione':
                        Out::valore($this->nameForm . '_catcod', '');
                        Out::valore($this->nameForm . '_clacod', '');
                        Out::valore($this->nameForm . '_fascod', '');
                        Out::valore($this->nameForm . '_titolarioDecod', '');
                        break;

                    case $this->nameForm . '_catcod':
//                        $versione = $_POST[$this->nameForm . '_ANAORG[VERSIONE_T]'];
                        $versione = $_POST[$this->nameForm . '_Versione'];
                        $codice = $_POST[$this->nameForm . '_catcod'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                        }
                        $this->decodTitolarioRic($versione, $codice, "ANACAT", 'codice');
                        break;
                    case $this->nameForm . '_clacod':
                        //$versione = $_POST[$this->nameForm . '_ANAORG[VERSIONE_T]'];
                        $versione = $_POST[$this->nameForm . '_Versione'];
                        $codiceCat = $_POST[$this->nameForm . '_catcod'];
                        if (trim($codiceCat) != "") {
                            $codiceCat = str_repeat("0", 4 - strlen(trim($codiceCat))) . trim($codiceCat);
                        } else {
                            break;
                        }
                        $codice = $_POST[$this->nameForm . '_clacod'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                        }
                        $this->decodTitolarioRic($versione, $codiceCat . $codice, "ANACLA", 'codice');
                        break;
                    case $this->nameForm . '_fascod':
                        //$versione = $_POST[$this->nameForm . '_ANAORG[VERSIONE_T]'];
                        $versione = $_POST[$this->nameForm . '_Versione'];
                        $codiceCat = $_POST[$this->nameForm . '_catcod'];
                        if (trim($codiceCat) != "") {
                            $codiceCat = str_repeat("0", 4 - strlen(trim($codiceCat))) . trim($codiceCat);
                        } else {
                            break;
                        }
                        $codiceCla = $_POST[$this->nameForm . '_clacod'];
                        if (trim($codiceCla) != "") {
                            $codiceCla = str_repeat("0", 4 - strlen(trim($codiceCla))) . trim($codiceCla);
                        }
                        $codice = $_POST[$this->nameForm . '_fascod'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                        }
                        $this->decodTitolarioRic($versione, $codiceCat . $codiceCla . $codice, "ANAFAS", 'fasccf');
                        break;
                    case $this->nameForm . '_Catcod':
                        $codice = $_POST[$this->nameForm . '_Catcod'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                        }
                        $this->DecodAnacat($codice);
                        $this->VisualizzaNascondiSerie($codice);
                        break;
                    case $this->nameForm . '_Clacod':
                        $codice = $_POST[$this->nameForm . '_Clacod'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_Catcod'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnacla($codice1 . $codice2);
                            $this->VisualizzaNascondiSerie($codice1 . $codice2);
                        } else {
                            $codice = $_POST[$this->nameForm . '_Catcod'];
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnacat($codice);
                            $this->VisualizzaNascondiSerie($codice);
                        }
                        break;
                    case $this->nameForm . '_Fascod':
                        $codice = $_POST[$this->nameForm . '_Fascod'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_Catcod'];
                            $codice2 = $_POST[$this->nameForm . '_Clacod'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice2))) . trim($codice2);
                            $codice3 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnafas($codice1 . $codice2 . $codice3);
                            $this->VisualizzaNascondiSerie($codice1 . $codice2 . $codice3);
                        } else {
                            $codice = $_POST[$this->nameForm . '_Clacod'];
                            $codice1 = $_POST[$this->nameForm . '_Catcod'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnacla($codice1 . $codice2);
                            $this->VisualizzaNascondiSerie($codice1 . $codice2);
                        }
                        break;
                    case $this->nameForm . '_Procedimento':
                        $proc = str_pad($_POST[$this->nameForm . '_Procedimento'], 6, "0", STR_PAD_LEFT);
                        if ($proc) {
                            $this->DecodAnapra($proc, $_POST['id'], 'codice');
                        }
                        break;

                    case $this->nameForm . '_Responsabile':
                        $codice = $_POST[$this->nameForm . '_Responsabile'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $retid = $this->nameForm . '_PROGES[GESRES]';
                        $this->DecodAnamed($codice, $_POST['id'], 'codice');
                        break;
                    case $this->nameForm . '_PROGES[GESRES]':
                        $this->DecodResponsabile($_POST[$this->nameForm . '_PROGES']['GESRES']);
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_AddAnno':
                        $anno = $_POST[$this->nameForm . '_AddAnno'];
                        $tipo = $_POST[$this->nameForm . '_AddTipo'];
                        if ($_POST[$this->nameForm . '_AddNumero'] && $anno && $tipo) {
                            $numero = str_pad($_POST[$this->nameForm . '_AddNumero'], 6, "0", STR_PAD_LEFT);
//                            $risultato = $this->checkAssegnaProtocolloAlFascicolo($anno . $numero, $tipo);// Commentato per nuova funzione
                        }
                        break;
                    case $this->nameForm . '_Dal_num':
                        $Dal_num = $_POST[$this->nameForm . '_Dal_num'];
                        if ($Dal_num) {
                            $Dal_num = str_pad($Dal_num, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Dal_num', $Dal_num);
                        }
                        break;
                    case $this->nameForm . '_Al_num':
                        $Al_num = $_POST[$this->nameForm . '_Al_num'];
                        if ($Al_num) {
                            $Al_num = str_pad($Al_num, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Al_num', $Al_num);
                        }
                        break;
                    case $this->nameForm . '_Anno':
                        $Dal_num = $_POST[$this->nameForm . '_Dal_num'];
                        $al_num = $_POST[$this->nameForm . '_Al_num'];
                        $anno = $_POST[$this->nameForm . '_Anno'];
                        if ($anno != '' && $Dal_num == $al_num && $Dal_num != '') {
//$Dal_num = str_pad($Dal_num, 6, "0", STR_PAD_LEFT);
                            $proges_tab = $this->proLibPratica->GetProges($anno . $Dal_num, 'codice', true);
                            if (count($proges_tab) == 1) {
                                Out::valore($this->nameForm . '_Dal_num', '');
                                Out::valore($this->nameForm . '_Al_num', '');
                                $this->Dettaglio($proges_tab[0]['ROWID']);
                            }
                        }
                        break;
                    case $this->nameForm . '_Da_richiesta':
                        $Da_richiesta = $_POST[$this->nameForm . '_Da_richiesta'];
                        if ($Da_richiesta) {
                            $Da_richiesta = str_pad($Da_richiesta, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Da_richiesta', $Da_richiesta);
                        }
                        break;
                    case $this->nameForm . '_A_richiesta':
                        $Alla_richiesta = $_POST[$this->nameForm . '_A_richiesta'];
                        if ($Alla_richiesta) {
                            $Alla_richiesta = str_pad($Alla_richiesta, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_A_richiesta', $Alla_richiesta);
                        }
                        break;
                    case $this->nameForm . '_Da_richiesta':
                        $Da_richiesta = $_POST[$this->nameForm . '_Da_richiesta'];
                        if ($Da_richiesta) {
                            $Da_richiesta = str_pad($Da_richiesta, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Da_richiesta', $Da_richiesta);
                        }
                        break;
                    case $this->nameForm . '_A_richiesta':
                        $Alla_richiesta = $_POST[$this->nameForm . '_A_richiesta'];
                        if ($Alla_richiesta) {
                            $Alla_richiesta = str_pad($Alla_richiesta, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_A_richiesta', $Alla_richiesta);
                        }
                        break;
                    case $this->nameForm . '_NumProt':
                        $NumProt = $_POST[$this->nameForm . '_NumProt'];
                        if ($NumProt) {
                            $NumProt = str_pad($NumProt, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_NumProt', $NumProt);
                        }
                        break;
                    case $this->nameForm . '_Sezione':
                        $sezione = $_POST[$this->nameForm . '_Sezione'];
                        if ($sezione) {
                            $sezione = str_pad($sezione, 3, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Sezione', $sezione);
                        }
                        break;
                    case $this->nameForm . '_Foglio':
                        $foglio = $_POST[$this->nameForm . '_Foglio'];
                        if ($foglio) {
                            $foglio = str_pad($foglio, 4, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Foglio', $foglio);
                        }
                        break;
                    case $this->nameForm . '_Particella':
                        $particella = $_POST[$this->nameForm . '_Particella'];
                        if ($particella) {
                            $particella = str_pad($particella, 5, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Particella', $particella);
                        }
                        break;
                    case $this->nameForm . '_AnnoProt':
                        $NumProt = $_POST[$this->nameForm . '_NumProt'];
                        $anno = $_POST[$this->nameForm . '_AnnoProt'];
                        if ($anno && $NumProt) {
                            $proges_rec = $this->proLibPratica->GetProges($anno . $NumProt, 'protocollo');
                            if ($proges_rec) {
                                $this->Dettaglio($proges_rec['ROWID']);
                            }
                        }
                        break;
                    case $this->nameForm . '_Anno_rich':
                        $Da_richiesta = $_POST[$this->nameForm . '_Da_richiesta'];
                        $Alla_richiesta = $_POST[$this->nameForm . '_A_richiesta'];
                        $anno = $_POST[$this->nameForm . '_Anno_rich'];
                        if ($anno != '' && $Da_richiesta == $Alla_richiesta && $Da_richiesta != '') {
                            $proges_tab = $this->proLibPratica->GetProges($anno . $Da_richiesta, 'richiesta', true);
                            if (count($proges_tab) == 1) {
                                Out::valore($this->nameForm . '_Da_richiesta', '');
                                Out::valore($this->nameForm . '_A_richiesta', '');
                                $this->Dettaglio($proges_tab[0]['ROWID']);
                            }
                        }
                        break;
                    case $this->nameForm . '_DestinatarioFascicolo':
                        $codice = $_POST[$this->nameForm . '_DestinatarioFascicolo'];
                        if ($codice) {
                            if (is_numeric($codice)) {
                                $codice = str_pad($codice, 6, "0", STR_PAD_LEFT);
                            }
                            $anamed_rec = $this->proLib->GetAnamed($codice, 'codice', 'no');
                            Out::valore($this->nameForm . '_DestinatarioFascicolo', $anamed_rec['MEDCOD']);
                            Out::valore($this->nameForm . '_DescrizioneDestinatarioFascicolo', $anamed_rec['MEDNOM']);
                        }
                        Out::setFocus('', $this->nameForm . '_Stato_proc');
                        break;
                    case $this->nameForm . '_PROGES[GESRES]':
                        if ($_POST[$this->nameForm . '_PROGES']['GESRES'] != '' && $_POST[$this->nameForm . '_UFFRESP'] == '') {
                            $codice = $_POST[$this->nameForm . '_PROGES']['GESRES'];
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $uffdes_tab = $this->proLib->getGenericTab("SELECT ANAUFF.UFFCOD FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFKEY='$codice' AND ANAUFF.UFFANN=0");
                            if (count($uffdes_tab) == 1) {
                                $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
                                Out::valore($this->nameForm . '_PROGES[GESUFFRES]', $anauff_rec['UFFCOD']);
                                Out::valore($this->nameForm . '_UFFRESP', $anauff_rec['UFFDES']);
                            } else {
                                proRic::proRicUfficiPerDestinatario($this->nameForm, $codice, '', '', 'Firmatario');
                                Out::setFocus('', "utiRicDiag_gridRis");
                                break;
                            }
                        }
                        break;
                    case $this->nameForm . '_PROGES[GESPRO]':
                        $retid = $this->nameForm . '_PROGES[GESPRO]';
                        $codice = $_POST[$this->nameForm . '_PROGES']['GESPRO'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $this->DecodAnapra($codice, $retid, 'codice');
                        break;
                    case $this->nameForm . '_ANADES[DESCIT]':
                        $Comuni_rec = ItaDB::DBSQLSelect($this->COMUNI_DB, "SELECT * FROM COMUNI WHERE " . $this->COMUNI_DB->strUpper('COMUNE') . " ='"
                                        . addslashes(strtoupper($_POST[$this->nameForm . '_ANADES']['DESCIT'])) . "'", false);
                        if ($Comuni_rec) {
                            Out::valore($this->nameForm . '_ANADES[DESCAP]', $Comuni_rec['COAVPO']);
                            Out::valore($this->nameForm . '_ANADES[DESPRO]', $Comuni_rec['PROVIN']);
                        }
                        break;

                    case $this->nameForm . '_SERIE[CODICE]':
                        if ($_POST[$this->nameForm . '_SERIE']['CODICE']) {
                            $versione_t = $_POST[$this->nameForm . '_ANAORG']['VERSIONE_T'];
                            $titolario = $_POST[$this->nameForm . '_Catcod'] . $_POST[$this->nameForm . '_Clacod'] . $_POST[$this->nameForm . '_Fascod'];
                            if (!$titolario) {
                                Out::valore($this->nameForm . '_SERIE[CODICE]', '');
                                Out::valore($this->nameForm . '_SERIE[SIGLA]', '');
                                Out::valore($this->nameForm . '_SERIE[DESCRIZIONE]', '');
                                Out::msgInfo("Attenzione", "Occorre indicare il titolario per procedere.");
                                break;
                            }
                            if (!$this->proLibSerie->CtrSerieInTitolario($_POST[$this->nameForm . '_SERIE']['CODICE'], $titolario, $versione_t)) {
                                Out::msgInfo("Attenzione", $this->proLibSerie->getErrMessage());
                                break;
                            }
                            $this->DecodificaSerie($_POST[$this->nameForm . '_SERIE']['CODICE']);
                        }
                        break;

                    case $this->nameForm . '_ric_codiceserie':
                        if ($_POST[$this->nameForm . '_ric_codiceserie']) {
                            $AnaserieArc_rec = $this->proLibSerie->GetSerie($_POST[$this->nameForm . '_ric_codiceserie'], 'codice');
                            if ($AnaserieArc_rec) {
                                Out::valore($this->nameForm . '_ric_codiceserie', $AnaserieArc_rec['CODICE']);
                                Out::valore($this->nameForm . '_descRicSerie', $AnaserieArc_rec['DESCRIZIONE']);
                                Out::valore($this->nameForm . '_ric_siglaserie', $AnaserieArc_rec['SIGLA']);
                                break;
                            } else {
                                Out::msgInfo("Attenzione", "Codice inesistente.");
                            }
                        }
                        Out::valore($this->nameForm . '_ric_codiceserie', '');
                        Out::valore($this->nameForm . '_descRicSerie', '');
                        Out::valore($this->nameForm . '_ric_siglaserie', '');

                        break;
                }
                break;
            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_NomeCampo':
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $Praidc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRAIDC WHERE " . $this->PRAM_DB->strUpper('IDCKEY') . " LIKE '"
                                        . addslashes(strtoupper(itaSuggest::getQuery())) . "%'", true);
                        foreach ($Praidc_tab as $Praidc_rec) {
                            itaSuggest::addSuggest($Praidc_rec['IDCKEY']);
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;
            case 'broadcastMsg':
                switch ($_POST['message']) {
                    case "UPDATE_FASCICOLO":
                        if ($_POST["msgData"]["GESKEY"] && $_POST["msgData"]["GESKEY"] === $this->geskey) {
                            $this->Dettaglio($_POST["msgData"]["GESKEY"], 'geskey');
                        }
                        break;
                    case "UPDATE_NOTE_ANAPRO":
                        $anapro_rec = $this->proLib->GetAnapro($_POST["msgData"]['PRONUM'], 'codice', $_POST["msgData"]['PROPAR']);
                        if ($anapro_rec['PROFASKEY'] === $this->geskey) {
                            $this->Dettaglio($this->geskey, 'geskey');
                        }
                        break;
                }
                break;
            case 'returnTitolarioRic':
//                $tipoArc = substr($_POST['rowData']['CHIAVE'], 0, 6);
//                $rowid = substr($_POST['rowData']['CHIAVE'], 7);
//                $this->decodTitolarioRic('', $rowid, $tipoArc);
                $retTitolario = $_POST['rowData'];
                Out::valore($this->nameForm . '_catcod', $retTitolario['CATCOD']);
                Out::valore($this->nameForm . '_clacod', $retTitolario['CLACOD']);
                Out::valore($this->nameForm . '_fascod', $retTitolario['FASCOD']);
                Out::valore($this->nameForm . '_titolarioDecod', $retTitolario['DECOD_DESCR']);
                break;
            case 'returnIFrame':
                $proges_rec = $this->proLibPratica->GetProges($this->currGesnum, 'codice');
                $this->Dettaglio($proges_rec['ROWID']);
//Out::msgInfo("Fatto");
                break;
            case 'returnFileFromTwain':
                $this->SalvaScanner();
                break;
            case 'returnAnapra':
                $this->DecodAnapra($_POST['retKey'], $_POST['retid'], 'rowid');
                $this->dataRegAppoggio = null;
                break;
            case 'returnPraclt':
                $this->DecodPraclt($_POST['retKey'], 'rowid');
                break;
            case 'returnUnires':
                $this->DecodAnamed($_POST['retKey'], $_POST['retid'], 'rowid');
                break;
            case 'returnProPasso':
                $this->Dettaglio($_POST['gesnum'], '', 'codice');
                break;
            case 'returnProges':
                if ($_POST['retid'] == "IMPORTAPASSI") {
                    $proges_rec = $this->proLibPratica->GetProges($_POST['retKey'], 'rowid');
                    $this->progesSel = $proges_rec['GESNUM'];
                    $Propas_rec = ItaDB::DBSQLSelect($this->PROT_DB, "SELECT * FROM PROPAS WHERE PRONUM = '$this->progesSel' AND PROPUB = 0", true);
                    if (!$Propas_rec) {
                        Out::msgInfo("Attenzione!!!", "Endoprocedimenti per la pratica n. " . $proges_rec['GESNUM'] . " non trovati");
                        break;
                    }
                    proRicPratiche::proPassiSelezionati($Propas_rec, $this->nameForm, "expAltraPra", "Scegli gli endoprocedimenti da importare", "PROPAS");
                } else {
                    $this->DecodProges($_POST['retKey'], 'rowid');
                }
                break;
            case 'returnElencoReportElenco':
                $tabella_rec = $_POST['rowData'];
                $_POST = $_POST['retid'];
                $parametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array(
                    "Sql" => $this->CreaSql(),
                    "Ente" => $parametriEnte_rec['DENOMINAZIONE'],
                    "filtroRicerca" => $this->filtroMessaggio,
                    "Titolo" => '',
                    "Utente" => App::$utente->getKey('nomeUtente')
                );
                $itaJR->runSQLReportPDF($this->PROT_DB, $tabella_rec['CODICE'], $parameters);
                break;
            case 'returnElencoReportDettaglio':
//@TODO RIVEDERE REPORTS
                $tabella_rec = $_POST['rowData'];
                $_POST = $_POST['retid'];
                $parametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $sql = "SELECT * FROM PROGES PROGES
                    LEFT OUTER JOIN ANANOM ANANOM ON PROGES.GESRES=ANANOM.NOMRES
                    LEFT OUTER JOIN ANAPRA ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM
                    LEFT OUTER JOIN ANADES ANADES ON PROGES.GESNUM=ANADES.DESNUM";
                $sql .= " WHERE PROGES.ROWID=" . $_POST[$this->nameForm . "_PROGES"]['ROWID'];
                $parameters = array(
                    "Sql" => $sql,
                    "Ente" => $parametriEnte_rec['DENOMINAZIONE']
                );
                $itaJR->runSQLReportPDF($this->PROT_DB, $tabella_rec['CODICE'], $parameters);
                break;
            case 'returnUploadXML':
                $XMLpassi = $_POST['uploadedFile'];
                if (file_exists($XMLpassi)) {
                    if (pathinfo($XMLpassi, PATHINFO_EXTENSION) == "xml") {
                        if ($this->proLibPratica->ImportXmlFilePropas($XMLpassi, $this->insertTo, $this->currGesnum)) {
                            $this->proLibPratica->ordinaPassi($this->currGesnum);
                            $this->proAzioni = $this->caricaAzioni($this->currGesnum);
                            $this->CaricaGriglia($this->gridPassi, $this->proAzioni, '1');
                        }
                    } else {
                        Out::msgStop("Errore", "File di importazione passi non è un xml.");
                        break;
                    }
                } else {
                    Out::msgStop("Errore", "Procedura di importazione passi interrotta per mancanza del file.");
                }
                break;
            case 'returnTitolario':
                $tipoArc = substr($_POST['rowData']['CHIAVE'], 0, 6);
                $rowid = substr($_POST['rowData']['CHIAVE'], 7);
                $this->decodTitolario($rowid, $tipoArc);
                break;
            case 'returnTitolarioFiltrato':
                $cat = $_POST['rowData']['CATCOD'];
                $cla = $_POST['rowData']['CLACOD'];
                $fas = $_POST['rowData']['FASCOD'];
                if ($cat) {
                    $anacat_rec = $this->proLib->GetAnacat('', $cat, 'codice');
                    Out::valore($this->nameForm . '_catdes', $anacat_rec['CATDES']);
                }
                if ($cla) {
                    $anacla_rec = $this->proLib->GetAnacla('', $cat . $cla, 'codice');
                    Out::valore($this->nameForm . '_clades', $anacla_rec['CLADE1'] . $anacla_rec['CLADE2']);
                }
                if ($fas) {
                    $anafas_rec = $this->proLib->GetAnafas('', $cat . $cla . $fas, 'fasccf');
                    Out::valore($this->nameForm . '_fasdes', $anafas_rec['FASDES']);
                }
                Out::valore($this->nameForm . '_Catcod', $cat);
                Out::valore($this->nameForm . '_Clacod', $cla);
                Out::valore($this->nameForm . '_Fascod', $fas);
                break;
            case 'returnAcqrList':
                switch ($_POST['id']) {
                    case $this->gridDocumenti:
                        /* Creazione Cartella Temporanea */
                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                $this->returnToParent();
                            }
                        }
                        $destPath = itaLib::getPrivateUploadPath();
                        //
                        $chiave = $this->formData[$this->nameForm . '_gridDocumenti']['gridParam']['selrow'];
                        $docnum = substr($chiave, 4, 10);
                        $docpar = substr($chiave, 14, 2);
                        $newAllegati = array();
                        foreach ($_POST['retList'] as $uplAllegato) {
                            // Salvo file:
                            $randName = md5(rand() * time()) . "." . pathinfo($uplAllegato['FILENAME'], PATHINFO_EXTENSION);
                            $destFile = $destPath . "/" . $randName;
                            if (!@rename($uplAllegato['FILEPATH'], $destFile)) {
                                Out::msgStop("Attenzione", "Errore in salvataggio del file.");
                                break;
                            }
                            $newAllegato = array(
                                'ROWID' => 0,
                                'NUMEROPROTOCOLLO' => $docnum,
                                'TIPOPROTOCOLLO' => $docpar,
                                'FILEPATH' => $destFile,
                                'FILENAME' => $uplAllegato['FILENAME'],
                                'FILEINFO' => $uplAllegato['FILEINFO'],
                                'DOCNAME' => $uplAllegato['FILEORIG'],
                                'DOCFDT' => date('Ymd'),
                                'DOCRELEASE' => '1',
                                'DOCSERVIZIO' => 0,
                                'DAFIRMARE' => 0
                            );
                            $newAllegati[] = $newAllegato;
                        }
                        $this->salvaAllegati($newAllegati, $docnum, $docpar);
                        $this->CaricaAllegati(array(), '2');
                        break;
                }
                break;
            case 'returnanapro':
                $anapro_rec = $this->proLib->GetAnapro($_POST['retKey'], 'rowid');
                $anno = substr($anapro_rec['PRONUM'], 0, 4);
                $numero = substr($anapro_rec['PRONUM'], 4);
                Out::valore($this->nameForm . '_Numero_prot', $numero);
                Out::valore($this->nameForm . '_Anno_prot', $anno);
                Out::valore($this->nameForm . '_PROGES[GESDRE]', $anapro_rec['PRODAR']);
                Out::valore($this->nameForm . '_PROGES[GESPAR]', $anapro_rec['PROPAR']);
                Out::valore($this->nameForm . '_ANADES[DESNOM]', $anapro_rec['PRONOM']);
                Out::valore($this->nameForm . '_ANADES[DESIND]', $anapro_rec['PROIND']);
                Out::valore($this->nameForm . '_ANADES[DESIND]', $anapro_rec['PROIND']);
                Out::valore($this->nameForm . '_ANADES[DESCAP]', $anapro_rec['PROCAP']);
                Out::valore($this->nameForm . '_ANADES[DESCIT]', $anapro_rec['PROCIT']);
                Out::valore($this->nameForm . '_ANADES[DESPRO]', $anapro_rec['PROPRO']);
                if ($anapro_rec['PROPAR'] == 'A') {
                    Out::valore($this->nameForm . '_PREALB[ALBCMI]', $anapro_rec['PROCON']);
                    Out::valore($this->nameForm . '_PREALB[ALBDMI]', $anapro_rec['PRONOM']);
                } else {
                    $anaent_rec = $this->proLib->GetAnaent(22);
                    Out::valore($this->nameForm . '_PREALB[ALBCMI]', '');
                    Out::valore($this->nameForm . '_PREALB[ALBDMI]', $anaent_rec['ENTDE2']);
                }
                break;
            case 'returnExplodeZip':
                $arrayFile = $this->proLibPratica->CaricaAllegatoDaZip($_POST['rowData'], $this->proDocumenti);
                if ($arrayFile == false) {
                    break;
                }
                if (isset($arrayFile["daFile"])) {
//
//upload singolo file
//
                    $arrayFile['Allegato']['NAME'] = '<span style = "color:orange;">' . $arrayFile['Allegato']['NAME'] . '</span>';
                    $this->proDocumenti[count($this->proDocumenti) + 1] = $arrayFile["Allegato"];
                } else {
//
//upload tutti i file della cartella
//
                    $this->proDocumenti = $arrayFile["Allegati"];
                }
                $this->bloccaCelleGrigliaDocumenti();
                break;
            case 'returnAntecedente';
                $proges_rec = $this->proLibPratica->GetProges($_POST['retKey'], 'rowid');
                if ($proges_rec) {
                    $this->Dettaglio($proges_rec['ROWID']);
                }
                break;
            case 'returnPraidcRic':
                $praidc_rec = $this->praLib->GetPraidc($_POST['retKey'], "rowid");
                Out::valore($this->nameForm . "_NomeCampo", $praidc_rec['IDCKEY']);
                break;
//            case 'returnPraidc':
//                $praidc_rec = $this->praLib->GetPraidc($_POST['retKey'], "rowid");
//                $this->praDatiPratica[] = array(
//                    "ROWID" => 0,
//                    "DAGSEQ" => 0,
//                    "DAGKEY" => $praidc_rec['IDCKEY'],
//                    "DAGDES" => $praidc_rec['IDCDES'],
//                    "DAGTIP" => $praidc_rec['IDCTIP'],
//                    "DAGVAL" => ""
//                );
//                $this->ordinaSeqArrayDag();
//                $this->CaricaGriglia($this->gridDatiPratica, $this->praDatiPratica, "3");
//                break;
            case 'returnPraPasso':
                $Proges_rec = $this->proLibPratica->GetProges($_POST['gesnum']);
                $this->Dettaglio($Proges_rec['ROWID']);
                break;
//              @TODO da verificare
//            case "returnSoggetti":
//                $this->praSoggetti->setSoggetto($_POST['soggetto'], $_POST['rowid']);
//                $this->CaricaGriglia($this->gridSoggetti, $this->praSoggetti->getGriglia());
//                break;
            case 'returnAnastp':
                $prostato = $_POST['retKey'];
                $model = 'proPassoPratica';
                $orgnode_rec = $this->proLib->GetOrgNode($this->geskey, 'orgkey');
                $_POST = array();
                $_POST['event'] = 'openform';
                $_POST['procedimento'] = $this->currGesnum;
                $_POST['modo'] = "chiudiFascicolo";
                $_POST['prostato'] = $prostato;
                $_POST['nodeRowid'] = $orgnode_rec['ROWID'];
                $_POST[$model . '_returnModel'] = $this->nameForm;
                $_POST[$model . '_returnMethod'] = 'returnProPasso';
                $_POST[$model . '_title'] = 'Chiudi Fascicolo.....';
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
//                $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
//                if (!$this->proLibFascicolo->AnnullaChiudiPratica($_POST['retKey'], $_POST['retid'], $this->currGesnum)) {
//                    break;
//                }
//                $this->Dettaglio($proges_rec['ROWID']);
                break;
            case 'returnAnaruo':
                $this->DecodAnaruo($_POST['retKey'], 'rowid');
                break;
//            case "returnComlic":
//                $model = 'wcoComgen';
//                $oldRowid = $_POST['rowData']['ROWID'];
//                $_POST = array();
//                $_POST['event'] = 'openform';
//                $_POST['proges_rec'] = $this->proLibPratica->GetProges($this->varAppoggio, "rowid");
//                $_POST['datiAggiuntivi'] = $this->praDati;
//                $_POST['oldRowid'] = $oldRowid;
//                $_POST[$model . '_returnModel'] = $this->nameForm;
//                $_POST[$model . '_returnEvent'] = 'returnWcoComgen';
//                itaLib::openForm($model);
//                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
//                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//                $model();
//                break;
//            case "returnAnaset":
//                $this->DecodAnaset($_POST['retKey'], 'rowid');
//                break;
            case 'returnanamedMittPartenzaRic':
                $anamed_rec = $this->proLib->GetAnamed($_POST['retKey'], 'rowid', 'si');
                Out::valore($this->nameForm . '_DestinatarioFascicolo', $anamed_rec["MEDCOD"]);
                Out::valore($this->nameForm . "_DescrizioneDestinatarioFascicolo", $anamed_rec["MEDNOM"]);
                Out::setFocus('', $this->nameForm . "_DestinatarioFascicolo");
                break;
            case 'returnUfficiPerDestinatarioFirmatario':
                $anauff_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_PROGES[GESUFFRES]', $anauff_rec['UFFCOD']);
                Out::valore($this->nameForm . '_UFFRESP', $anauff_rec['UFFDES']);
                Out::setFocus('', $this->nameForm . "_PROGES[GESRES]");
                break;
            case 'returnanamedMittPartenza':
                $anamed_rec = $this->proLib->GetAnamed($_POST['retKey'], 'rowid', 'si');
                Out::valore($this->nameForm . '_PROGES[GESRES]', $anamed_rec["MEDCOD"]);
                Out::valore($this->nameForm . "_Desc_resp2", $anamed_rec["MEDNOM"]);
                Out::valore($this->nameForm . "_PROGES[GESUFFRES]", '');
                Out::valore($this->nameForm . "_UFFRESP", '');
                Out::setFocus('', $this->nameForm . "_PROGES[GESRES]");
                break;
            case 'returnFromProGest':
//                Out::closeDialog('proGest');
                $anapro_rec = $this->proLib->GetAnapro($_POST['rowid'], 'rowid');
//                App::log($anapro_rec);
                Out::valore($this->nameForm . "_AddTipo", $anapro_rec['PROPAR']);
                Out::valore($this->nameForm . "_AddNumero", substr($anapro_rec['PRONUM'], 4));
                Out::valore($this->nameForm . "_AddAnno", substr($anapro_rec['PRONUM'], 0, 4));
                break;

            case 'returnGestSottoFascicolo':
                $chiaveriga_num = $this->varAppoggio['CHIAVERIGA_NUM'];
                $chiaveriga_tipo = $this->varAppoggio['CHIAVERIGA_TIPO'];

                if (isset($this->varAppoggio['CHIAVERIGA_NUM'])) {
                    unset($this->varAppoggio['CHIAVERIGA_NUM']);
                }
                if (isset($this->varAppoggio['CHIAVERIGA_TIPO'])) {
                    unset($this->varAppoggio['CHIAVERIGA_TIPO']);
                }
                $orgnode_rec = $this->proLib->GetOrgNode($chiaveriga_num, 'codice', $chiaveriga_tipo);

                if ($this->returnData['EVENTO'] == 'Aggiungi') {
                    if ($this->addSottofascicoloSimple($this->currGesnum, $orgnode_rec, $this->returnData)) {
                        Out::msgBlock('', 3000, true, "Sotto Fascicolo inserito.");
                        $this->Dettaglio($this->geskey, 'geskey');
                    }
                } else if ($this->returnData['EVENTO'] == 'Aggiorna') {
                    if ($this->editSottofascicoloSimple($this->currGesnum, $orgnode_rec, $this->returnData)) {
                        Out::msgBlock('', 3000, true, "Sotto Fascicolo aggiornato.");
                        $this->Dettaglio($this->geskey, 'geskey');
                    }
                }
                break;

            case 'returnAlberoFascicolo':
                $this->SpostaDocumento();
                $this->Dettaglio($this->geskey, 'geskey');
                break;

            case 'returnFascicoloCollegato':
                $Anaorg_rec = $this->proLib->GetAnaorg($this->returnData['ROWID_ANAORG'], 'rowid');
                //$ProGes_rec = $this->proLibPratica->GetProges($Anaorg_rec['ORGKEY'], 'geskey');
                if ($this->geskey == $Anaorg_rec['ORGKEY']) {
                    Out::msgInfo('Attenzione', 'Scegliere un altro fascicolo da collegare. Non è possibile collegare il fascicolo a se stesso.');
                    break;
                }
                Out::valore($this->nameForm . '_ANAORG[ORGKEYPRE]', $Anaorg_rec['ORGKEY']);
                break;

            case 'returntoformDocumentale':
                //Out::msgInfo('Informazione', 'Funzione non attiva.');
                if ($_POST['rowid']) {

                    $this->returnData['ROWID_INDICE'] = $_POST['rowid'];
                    $Indice_rec = $this->segLib->GetIndice($_POST['rowid'], 'rowid');
                    $dizionarioIdelib = $this->segLib->getDizionarioFormIdelib($Indice_rec['INDTIPODOC'], $Indice_rec);
                    $NumeroInd = is_numeric($dizionarioIdelib['PROGRESSIVO']) ? intval($dizionarioIdelib['PROGRESSIVO']) : $dizionarioIdelib['PROGRESSIVO'];
                    $AnnoInd = substr($Indice_rec['IDATDE'], 0, 4);
                    $Messaggio = 'Vuoi aggiungere il documento ' . $Indice_rec['INDTIPODOC'] . ' n. ' . $NumeroInd . '/' . $AnnoInd . ' al fascicolo?';
                    Out::msgQuestion("Attenzione!", $Messaggio, array(
                        'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaAddDocumentale', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAddDocumentale', 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                    );
                }
                break;

            case 'returnSerieTitolario':
                $tabella_rec = $_POST['rowData'];
                $this->DecodificaSerie($tabella_rec['CODICE']);
                break;
            case 'returnSerieArc':
                $AnaserieArc_rec = $this->proLibSerie->GetSerie($_POST['retKey'], 'rowid');
                if ($AnaserieArc_rec) {
                    Out::valore($this->nameForm . '_ric_codiceserie', $AnaserieArc_rec['CODICE']);
                    Out::valore($this->nameForm . '_descRicSerie', $AnaserieArc_rec['DESCRIZIONE']);
                    Out::valore($this->nameForm . '_ric_siglaserie', $AnaserieArc_rec['SIGLA']);
                }
                break;

            case 'returnFasCollegati':
                App::log($_POST['retKey']);
                // Ritorna Rowid di anapro.
                $Anapro_rec = $this->proLib->GetAnapro($_POST['retKey'], 'rowid');
                $proges_rec = $this->proLibPratica->GetProges($Anapro_rec['PROFASKEY'], 'geskey');
                $anapro_fascicolo_secure_tab = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'fascicolo', $Anapro_rec['PROFASKEY']);
                if (!$anapro_fascicolo_secure_tab) {
                    Out::msgStop("Accesso al fascicolo", "Fascicolo non accessibile");
                    break;
                }
                $this->Dettaglio($proges_rec['ROWID']);
                break;


            case $this->nameForm . '_VaiAPratica':
                include_once ITA_BASE_PATH . '/apps/Pratiche/praLibFascicoloArch.class.php';
                $praLibFascicolo = new praLibFascicoloArch();
                $id = $_POST['id'];
                if (!$praLibFascicolo->ApriFascicolo($this->nameForm, $id)) {
                    Out::msgStop("Attenzione", $praLibFascicolo->getErrMessage());
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_praPassi');
        App::$utente->removeKey($this->nameForm . '_praAlle');
        App::$utente->removeKey($this->nameForm . '_proIter');
        App::$utente->removeKey($this->nameForm . '_currGesnum');
        App::$utente->removeKey($this->nameForm . '_varAppoggio');
        App::$utente->removeKey($this->nameForm . '_dataRegAppoggio');
        App::$utente->removeKey($this->nameForm . '_returnId');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_page');
        App::$utente->removeKey($this->nameForm . '_insertTo');
        App::$utente->removeKey($this->nameForm . '_passi');
        App::$utente->removeKey($this->nameForm . '_praDati');
        App::$utente->removeKey($this->nameForm . '_praDatiPratica');
        App::$utente->removeKey($this->nameForm . '_datiFiltrati');
        App::$utente->removeKey($this->nameForm . '_allegatiComunica');
        App::$utente->removeKey($this->nameForm . '_currAllegato');
        App::$utente->removeKey($this->nameForm . '_proric_rec');
        App::$utente->removeKey($this->nameForm . '_pranumSel');
        App::$utente->removeKey($this->nameForm . '_emlComunica');
        App::$utente->removeKey($this->nameForm . '_praSoggetti');
        App::$utente->removeKey($this->nameForm . '_progesSel');
        App::$utente->removeKey($this->nameForm . '_geskey');
        App::$utente->removeKey($this->nameForm . '_noteManager');
        App::$utente->removeKey($this->nameForm . '_documentiProt');
        App::$utente->removeKey($this->nameForm . '_attivaAzioni');
        App::$utente->removeKey($this->nameForm . '_rowidDocumentoDaSpostare');
        App::$utente->removeKey($this->nameForm . '_returnData');
        App::$utente->removeKey($this->nameForm . '_AnaproTitolarioDifferente');
        App::$utente->removeKey($this->nameForm . '_AlberoEreditaVisibilita');
        App::$utente->removeKey($this->nameForm . '_fl_manutenzioneSerie');
        App::$utente->removeKey($this->nameForm . '_RowidAllegaAProtocollo');
        App::$utente->removeKey($this->nameForm . '_consultazione');
        App::$utente->removeKey($this->nameForm . '_Ordinamento');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        if ($this->returnModel != '') {
            $model = $this->returnModel;
//            $rowid = $_POST[$this->nameForm . "_PROGES"]['ROWID'];
            $_POST = array();
            $_POST['event'] = 'dbClickRow';
            $_POST['rowid'] = $this->returnId;
            $_POST['id'] = "proStepIter2_gridStepProced";
            if ($this->consultazione && $this->returnEvent) {
                $_POST['event'] = $this->returnEvent;
            }
            itaLib::openForm($model);
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $model();
            Out::closeDialog($this->nameForm);
        } else {
            Out::show('menuapp');
        }
    }

    private function OpenRicerca() {
//
//Azzero le variabili conservate in Session
//
        Out::show($this->divRic, '', 200);
        Out::hide($this->divRis, '', 0);
        Out::hide($this->divGes, '', 0);
        $this->AzzeraVariabili();
        $this->Nascondi();
        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli();
        if ($permessiFascicolo[proLibFascicolo::PERMFASC_CREAZIONE]) {
            Out::show($this->nameForm . '_NuovaPratica');
        } else {
            Out::hide($this->nameForm . '_NuovaPratica');
        }
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::show($this->nameForm . '_divButtonNew');
        Out::setFocus('', $this->nameForm . '_catcod');
        if ($this->consultazione) {
            Out::hide($this->nameForm . '_NuovaPratica');
        }
        //$retVisibilta = $this->praLib->GetVisibiltaSportello();
        //Out::html($this->nameForm . "_divInfo", "Sportelli On-line Visibili: <span style=\"font-weight:bold;\">" . $retVisibilta['SPORTELLO_DESC'] . "</span> Aggregati Visibili: <span style=\"font-weight:bold;\">" . $retVisibilta['AGGREGATO_DESC'] . "</span>");
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_Stato_proc', 1, "T", "1", "Tutti");
        Out::select($this->nameForm . '_Stato_proc', 1, "A", "0", "Aperti");
        Out::select($this->nameForm . '_Stato_proc', 1, "C", "0", "Chiusi");

        Out::select($this->nameForm . '_Stato_passo', 1, "T", "1", "Tutti");
        Out::select($this->nameForm . '_Stato_passo', 1, "A", "0", "Aperti");
        Out::select($this->nameForm . '_Stato_passo', 1, "C", "0", "Chiusi");

        Out::select($this->nameForm . '_Stato_allegato', 1, "", "1", "Da controllare");
        Out::select($this->nameForm . '_Stato_allegato', 1, "V", "0", "Validi");
        Out::select($this->nameForm . '_Stato_allegato', 1, "N", "0", "Non validi");

        Out::select($this->nameForm . '_Tipo', 1, "", "1", "");
        Out::select($this->nameForm . '_Tipo', 1, "F", "0", "Fabbricato");
        Out::select($this->nameForm . '_Tipo', 1, "T", "0", "Terreno");

        Out::select($this->nameForm . '_PROGES[GESPAR]', 1, "", "1", "");
        Out::select($this->nameForm . '_PROGES[GESPAR]', 1, "A", "0", "Arrivo     ");
        Out::select($this->nameForm . '_PROGES[GESPAR]', 1, "P", "0", "Partenza   ");
        Out::select($this->nameForm . '_PROGES[GESPAR]', 1, "C", "0", "Com.Formale");

        Out::select($this->nameForm . '_Propar', 1, "", "1", "");
        Out::select($this->nameForm . '_Propar', 1, "A", "0", "Arrivo     ");
        Out::select($this->nameForm . '_Propar', 1, "P", "0", "Partenza   ");
        Out::select($this->nameForm . '_Propar', 1, "C", "0", "Com.Formale");

        // Versioni Titolario:
        Out::html($this->nameForm . '_Versione', '');
        $Versioni_tab = $this->proLibTitolario->GetVersioni();
        foreach ($Versioni_tab as $Versioni_rec) {
            Out::select($this->nameForm . '_Versione', 1, $Versioni_rec['VERSIONE_T'], "0", $Versioni_rec['VERSIONE_T'] . ' - ' . $Versioni_rec['DESCRI_B']);
        }

        // Combo Natrua fascicolo
        Out::select($this->nameForm . '_ANAORG[NATFAS]', 1, "", "0", "Digitale");
        Out::select($this->nameForm . '_ANAORG[NATFAS]', 1, "1", "0", "Cartaceo");
        Out::select($this->nameForm . '_ANAORG[NATFAS]', 1, "2", "0", "Ibrido");

        // Combo tipo documenti:
        $ParametriVari = $this->segLib->GetParametriVari();
        if ($ParametriVari['SEG_ATTIVA_CLAFAS']) {
            $ElencoTipo = segLibDocumenti::$ElencoTipi;
            Out::select($this->nameForm . '_RICDOC[TIPODOC]', 1, "", "1", "Tutti");
            foreach ($ElencoTipo as $Tipo => $DescrizioneTipo) {
                Out::select($this->nameForm . '_RICDOC[TIPODOC]', 1, $Tipo, "0", $DescrizioneTipo);
            }
        }
    }

    private function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divRis);
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::clearFields($this->nameForm, $this->nameForm . '_divAppoggio');
        /*
         * Set titolario corrente di lavoro
         */
        Out::valore($this->nameForm . '_ANAORG[VERSIONE_T]', $this->proLib->GetTitolarioCorrente());
        Out::hide($this->nameForm . '_VersioneTitolario');
        Out::valore($this->nameForm . '_Versione', $this->proLib->GetTitolarioCorrente());
        TableView::disableEvents($this->gridPassi);
        TableView::clearGrid($this->gridPassi);
        TableView::disableEvents($this->gridDocumenti);
        TableView::clearGrid($this->gridDocumenti);
        TableView::disableEvents($this->gridGest);
        TableView::clearGrid($this->gridGest);
        TableView::disableEvents($this->gridSoggetti);
        TableView::clearGrid($this->gridSoggetti);
        Out::valore($this->nameForm . '_Anno_rich', '');
        Out::valore($this->nameForm . '_Stato_proc', 'A');
        Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDocumenti");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDati");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_panePassi");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneIter");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAggiuntivi");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneNote");
        Out::block($this->nameForm . "_divIntestatario");
        Out::block($this->nameForm . "_divAltriDati");
        Out::html($this->nameForm . '_PRASTA[STADES]', 'Da inserire');
        $this->currGesnum = null;
        $this->varAppoggio = null;
        $this->dataRegAppoggio = null;
        $this->currGesnum = '';
        $this->proAzioni = array();
        $this->proDocumenti = array();
        $this->proIter = array();
        $this->praSoggetti = null;
        Out::valore($this->nameForm . '_UTENTEORIGINARIO', '');
        Out::valore($this->nameForm . '_UTENTEULTIMO', '');
        $this->AlberoEreditaVisibilita = array();
        $this->fl_manutenzioneSerie = '';
    }

    private function Nascondi() {
        Out::hide($this->nameForm . '_divGrigliaDati');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_NuovaPratica');
        Out::hide($this->nameForm . '_CaricaDaMail');
        Out::hide($this->nameForm . '_Chiudi');
        Out::hide($this->nameForm . '_Apri');
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_ImportPassi');
        Out::hide($this->nameForm . '_EsportaDati');
        Out::hide($this->nameForm . '_Annulla');
        Out::hide($this->nameForm . '_EsportaDati');
        Out::hide($this->nameForm . '_Protocolla');
        Out::hide($this->nameForm . '_Stampa');
        Out::hide($this->nameForm . '_StampaDettaglio');
        Out::hide($this->nameForm . '_divButtonNew');
        Out::hide($this->nameForm . '_consultaAnagrafe');
        Out::hide($this->nameForm . '_CollegaCommercio');
        Out::hide($this->nameForm . '_VediProtocollo');
        Out::hide($this->nameForm . '_VediMail');
        Out::hide($this->nameForm . "_InviaProtocollo");
        Out::hide($this->nameForm . "_Etichetta");
        Out::hide($this->nameForm . "_Riserva");
        Out::hide($this->nameForm . "_NonRiserva");
        Out::hide($this->nameForm . '_protRiservato');
//Campi Nascosti per proGestPratica
        Out::hide($this->nameForm . '_divIntestatario');
        Out::hide($this->nameForm . '_divAltriDati');
        Out::hide($this->nameForm . '_divIcona');
        Out::hide($this->gridDocumenti . '_delGridRow');
        Out::hide($this->nameForm . '_divSerie');
        Out::hide($this->nameForm . '_FascicoliCollegati');
        Out::hide($this->nameForm . '_ProtocollaAllegatiFas');
        Out::hide($this->nameForm . '_StampaEleProtoc');
//

        $anaent_11 = $this->proLib->GetAnaent('11');
        if ($anaent_11['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_catcod_field');
            Out::hide($this->nameForm . '_clacod_field');
            Out::hide($this->nameForm . '_fascod_field');
            Out::hide($this->nameForm . '_Catcod_field');
            Out::hide($this->nameForm . '_Clacod_field');
            Out::hide($this->nameForm . '_Fascod_field');
            Out::hide($this->nameForm . '_catdes');
            Out::hide($this->nameForm . '_clades');
            Out::hide($this->nameForm . '_fasdes');
        }
        $anaent_12 = $this->proLib->GetAnaent('12');
        if ($anaent_12['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_clacod_field');
            Out::hide($this->nameForm . '_fascod_field');
            Out::hide($this->nameForm . '_Clacod_field');
            Out::hide($this->nameForm . '_Fascod_field');
            Out::hide($this->nameForm . '_clades');
            Out::hide($this->nameForm . '_fasdes');
        }
        $anaent_13 = $this->proLib->GetAnaent('13');
        if ($anaent_13['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_fascod_field');
            Out::hide($this->nameForm . '_Fascod_field');
            Out::hide($this->nameForm . '_fasdes');
        }
        $anaent_33 = $this->proLib->GetAnaent('33');
        if (!$anaent_33['ENTDE4']) {
            Out::hide($this->nameForm . '_PROGES[GESPRO]_field');
            Out::hide($this->nameForm . '_Desc_proc2_field');
        }

        Out::hide($this->nameForm . '_Cronologia');
        Out::hide($this->nameForm . '_divStrutturaIter');
        //Out::show($this->nameForm . '_Struttura');
        Out::hide($this->nameForm . '_Struttura');
        Out::show($this->nameForm . '_CaricaIter');
        Out::show($this->nameForm . '_divCronologiaIter');
        Out::unBlock($this->nameForm . "_divHead");
        Out::hide($this->nameForm . '_divInfoPraFas');
        Out::hide($this->nameForm . '_divConservazione');
    }

    private function CreaSqlXls() {
        $anno = $_POST[$this->nameForm . '_Anno'];
        $Dal_num = $_POST[$this->nameForm . '_Dal_num'];
        $al_num = $_POST[$this->nameForm . '_Al_num'];
        $Da_data = $_POST[$this->nameForm . '_Da_data'];
        $a_data = $_POST[$this->nameForm . '_A_data'];
        $procedimento = $_POST[$this->nameForm . '_Procedimento'];

        if ($Dal_num == '')
            $Dal_num = "0";
        if ($al_num == '')
            $al_num = "999999";
        if ($Dal_num != '')
            $Dal_num = $anno . str_pad($Dal_num, 6, 0, STR_PAD_RIGHT);
        if ($al_num != '')
            $al_num = $anno . str_pad($al_num, 6, 0, STR_PAD_RIGHT);
        if ($procedimento != '')
            $procedimento = str_pad($procedimento, 6, 0, STR_PAD_RIGHT);

        $sql = "SELECT
            DISTINCT PROGES.ROWID AS ROWID,
            PROGES.GESNUM AS GESNUM,
            PROGES.GESKEY AS GESKEY,
            PROGES.GESOGG AS GESOGG,
            PROGES.GESDRE AS GESDRE,
            PROGES.GESDRI AS GESDRI,
            PROGES.GESORA AS GESORA,
            PROGES.GESDCH AS GESDCH,
            PROGES.GESNOT AS GESNOT,
            PROGES.GESPRE AS GESPRE,
            PROGES.GESNPR AS GESNPR,
            PROGES.GESRES AS GESRES,
            PROGES.GESPRO AS GESPRO,
            ANAMED.MEDNOM AS RESPONSABILE,
            ANAUFF.UFFDES AS UFFRESPONSABILE,
            ANACAT.CATDES AS CATDES,
            ANACLA.CLADE1 AS CLADE1,
            ANACLA.CLADE2 AS CLADE2,
            ANAPROFASCICOLO.PROCCF AS PROCCF,
            ANAPROFASCICOLO.PRORISERVA AS PRORISERVA,
            ANAPROFASCICOLO.PROTSO AS PROTSO,
                {$this->PRAM_DB->getDB()}.ANAPRA.PRADES__1 AS PRADES__1
        FROM PROGES PROGES
            LEFT OUTER JOIN
                ANAPRO ANAPROFASCICOLO
            ON
                ANAPROFASCICOLO.PROFASKEY=PROGES.GESKEY AND ANAPROFASCICOLO.PROPAR='F'
            LEFT OUTER JOIN
                ANAPRO ANAPRO
            ON
                ANAPRO.PROFASKEY=PROGES.GESKEY AND
                (ANAPRO.PROPAR='F' OR ANAPRO.PROPAR='N' OR ANAPRO.PROPAR='T' OR ANAPRO.PROPAR='A' OR ANAPRO.PROPAR='P' OR ANAPRO.PROPAR='C')
            LEFT OUTER JOIN
                ARCITE ARCITE
            ON
                ANAPRO.PRONUM=ARCITE.ITEPRO AND
                ANAPRO.PROPAR=ARCITE.ITEPAR
            LEFT OUTER JOIN {$this->PRAM_DB->getDB()}.ANAPRA ANAPRA ON PROGES.GESPRO={$this->PRAM_DB->getDB()}.ANAPRA.PRANUM
            LEFT OUTER JOIN ANAMED ANAMED ON PROGES.GESRES=ANAMED.MEDCOD
            LEFT OUTER JOIN ANAUFF ANAUFF ON PROGES.GESUFFRES=ANAUFF.UFFCOD
            
            LEFT OUTER JOIN ANACLA ANACLA ON ANAPROFASCICOLO.PROCCA=ANACLA.CLACCA AND ANACLA.CLADAT=''
            LEFT OUTER JOIN ANACAT ANACAT ON ANACLA.CLACAT=ANACAT.CATCOD AND ANACLA.CLADAT=ANACAT.CATDAT
                
            WHERE (GESNUM BETWEEN '$Dal_num' AND '$al_num')
            ";

        if ($Da_data && $a_data) {
            $sql .= " AND (GESDRE BETWEEN '$Da_data' AND '$a_data')";
        }
        if ($procedimento) {
            $sql .= " AND GESPRO = '" . $procedimento . "'";
        }
        $sql .= " GROUP BY GESNUM"; // Per non far vedere pratiche doppie a colpa della join con ANADES
        return $sql;
    }

    private function DecodAnapra($Codice, $retid, $tipoRic = 'codice') {
        $anapra_rec = $this->praLib->GetAnapra($Codice, $tipoRic);
        switch ($retid) {
            case $this->nameForm . "_Procedimento":
                Out::valore($this->nameForm . '_Procedimento', $anapra_rec['PRANUM']);
                Out::valore($this->nameForm . '_Desc_proc', $anapra_rec['PRADES__1']);
                break;
            case $this->nameForm . "_PROGES[GESPRO]" :
//
//  DA SISTEMARE
//
                Out::valore($this->nameForm . "_PROGES[GESPRO]", $anapra_rec['PRANUM']);
                Out::valore($this->nameForm . '_Desc_proc2', $anapra_rec['PRADES__1']);
                $this->DecodAnamed($anapra_rec['PRARES'], $this->nameForm . "_PROGES[GESRES]");
                break;
            case "importXml" :
                $tipoEnte = $this->praLib->GetTipoEnte();
                if ($tipoEnte != "M") {
                    $dbsuffix = $this->praLib->GetEnteMaster();
                }

                $praLibMaster = new praLib($dbsuffix);
                $Anapra_rec = $praLibMaster->GetAnapra($_POST['retKey'], "rowid");
                $this->pranumSel = $Anapra_rec['PRANUM'];
//                $tipoEnte = $this->praLib->GetTipoEnte();
//                if ($tipoEnte != "M") {
//                    $dbsuffix = $this->praLib->GetEnteMaster();
//                }
                $Itepas_tab = $this->caricaPassiItepas($this->pranumSel, $dbsuffix);
                if ($Itepas_tab) {
                    proRicPratiche::proPassiSelezionati($Itepas_tab, $this->nameForm, "exp", "Scegli i passi da importare");
                } else {
                    Out::msgInfo("Importazione Passi", "Passi del procedimento <b>$this->pranumSel - " . $Anapra_rec['PRADES__1'] . "</b> non trovati.");
                }
                break;
            case "" :
                break;
        }
        return $anapra_rec;
    }

//    private function ordinaSeqArrayDag() {
//        if (!$this->praDatiPratica) {
//            return false;
//        }
//        $this->praDatiPratica = $this->proLibPratica->array_sort($this->praDatiPratica, "DAGSEQ");
//        $new_seq = 0;
//        foreach ($this->praDatiPratica as $key => $dato) {
//            $dato = $dato;
//            $new_seq +=10;
//            $this->praDatiPratica[$key]['DAGSEQ'] = $new_seq;
//        }
//    }

    private function DecodPraclt($Codice, $tipoRic = 'codice') {
        $praclt_rec = $this->praLib->GetPraclt($Codice, $tipoRic);
        Out::valore($this->nameForm . '_Passo', $praclt_rec['CLTCOD']);
        Out::valore($this->nameForm . '_Desc_passo', $praclt_rec['CLTDES']);
        return $praclt_rec;
    }

    private function DecodAnaruo($Codice, $tipoRic = 'codice') {
        $anaruo_rec = $this->praLib->GetAnaruo($Codice, $tipoRic);
        Out::valore($this->nameForm . "_Ruolo", $anaruo_rec['RUOCOD']);
        Out::valore($this->nameForm . "_DescRuolo", $anaruo_rec['RUODES']);
        return $anaruo_rec;
    }

    private function DecodAnamed($Codice, $retid, $tipoRic = 'codice') {
        $anamed_rec = $this->proLib->GetAnamed($Codice, $tipoRic);
        switch ($retid) {
            case $this->nameForm . "_Responsabile":
                Out::valore($this->nameForm . '_Responsabile', $anamed_rec['MEDCOD']);
                Out::valore($this->nameForm . '_Desc_resp', $anamed_rec['MEDNOM']);
                break;
//            case $this->nameForm . "_PROGES[GESRES]":
//                Out::valore($this->nameForm . '_PROGES[GESRES]', $anamed_rec['MEDCOD']);
//                Out::valore($this->nameForm . '_Desc_resp2', $anamed_rec['MEDNOM']);
//                Out::valore($this->nameForm . "_PROGES[GESUFFRES]", '');
//                Out::valore($this->nameForm . "_UFFRESP", '');
//                break;
            default :
                break;
        }
        return $anamed_rec;
    }

    private function DecodProges($Codice, $tipoRic = 'codice') {
        $proges_rec = $this->proLibPratica->GetProges($Codice, $tipoRic);
    }

//    private function CtrPassword() {
//        $ditta = App::$utente->getKey('ditta');
//        $utente = App::$utente->getKey('nomeUtente');
//        $password = $_POST[$this->nameForm . '_password'];
//        $ret = ita_verpass($ditta, $utente, $password);
//        if ($ret['status'] != 0 && $ret['status'] != '-99') {
//            Out::msgStop("Errore di validazione", $ret['messaggio'], 'auto', 'auto', '');
//            return false;
//        } else {
//            return true;
//        }
//    }

    private function elenca() {
        $pageRow = $_POST[$this->gridGest]['gridParam']['rowNum'];
        if ($pageRow == "") {
            $pageRow = "20";
        }
        try {
            $sql = $this->CreaSql();
            if ($sql['sql'] != false) {
                $ita_grid01 = new TableView($this->gridGest, array(
                    'sqlDB' => $this->PROT_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setPageNum(1);
                $ita_grid01->setPageRows($pageRow);
                $ita_grid01->setSortIndex('GESNUM');
                $ita_grid01->setSortOrder('desc');
                Out::setFocus('', $this->nameForm . '_AltraRicerca');
                $Result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
                if (!$ita_grid01->getDataPageFromArray('json', $Result_tab)) {
                    Out::msgStop("Selezione", "Nessun record trovato.");
                    $this->OpenRicerca();
                } else {
                    // Visualizzo la ricerca
                    Out::hide($this->divRic, '');
                    Out::show($this->divRis, '');
                    $this->Nascondi();
                    Out::show($this->nameForm . '_AltraRicerca');
                    Out::show($this->nameForm . '_Stampa');
                    if ($this->consultazione) {
                        Out::hide($this->nameForm . '_Stampa');
                        Out::hide($this->gridGest . '_editGridRow');
                    }
                    TableView::enableEvents($this->gridGest);
                }
            }
        } catch (Exception $e) {
            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
        }
    }

    private function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]["GESNUM"] = substr($Result_rec['GESNUM'], 14, 6) . "/" . substr($Result_rec['GESNUM'], 10, 4);

            if ($Result_rec['GESDCH']) {
                $Result_tab[$key]['STATO'] = "<span class=\"ita-icon ita-icon-check-red-24x24\">Fascicolo Chiuso</span>";
//                $Result_tab[$key]['STATO'] = $this->GetImgStatoPratica($Result_rec['GESNUM']);
//            } else {
//                $Propas_tab = $this->proLibPratica->GetPropas($Result_rec['GESNUM'], "codice", true);
//                if ($Propas_tab) {
//                    foreach ($Propas_tab as $Propas_rec) {
//                        if ($Propas_rec['PROPUB'] == 0) {
//                            if ($Propas_rec['PROINI'] && $Propas_rec['PROFIN'] == "") {
//                                $Result_tab[$key]['STATO'] = '<span class="ita-icon ita-icon-apertagray-24x24">Pratica con passi aperti</span>';
//                            } else if (($Propas_rec['PROINI'] && $Propas_rec['PROFIN']) || ($Propas_rec['PROINI'] = "" && $Propas_rec['PROFIN'] = "")) {
//                                $Result_tab[$key]['STATO'] = '<span class="ita-icon ita-icon-apertagreen-24x24">Pratica in corso</span>';
//                            }
//                        } else {
//                            $Result_tab[$key]['STATO'] = '<span class="ita-icon ita-icon-apertagreen-24x24">Pratica in corso</span>';
//                        }
//                    }
//                }
            } else {
                $Result_tab[$key]['STATO'] = "<span class=\"ita-icon ita-icon-check-green-24x24\">Fascicolo Aperto</span>";
            }

            if ($Result_rec['ORGKEYPRE']) {
                $Result_tab[$key]['ANTECEDENTE'] = '<span class="ui-icon ui-icon-folder-open">true</span>';
            }
            $riservato = 0;
            if ($this->proLib->checkRiservatezzaProtocollo($Result_rec)) {
                $riservato = 1;
            }
            if ($riservato == 1 || $riservato == 2) {
                $Result_tab[$key]['GESOGG'] = "<div style = \"padding-left:2px;height:90%;margin:1px;background-color:lightgrey;\"><div style=\"float:left;display:inline-block;background-color:lightgrey;color:black;\">RISERVATO</div></div>";
                $Result_tab[$key]['PRADES__1'] = "<div style = \"padding-left:2px;height:90%;margin:1px;background-color:lightgrey;\"><div style=\"float:left;display:inline-block;background-color:lightgrey;color:black;\">RISERVATO</div></div>";
            }
            //$anapro_check = $this->proLib->getGenericTab("SELECT COUNT(ROWID) AS SOTTOFASCICOLI FROM ANAPRO WHERE PROFASKEY='{$Result_rec['GESKEY']}' AND PROPAR='N'", false);
            $anapro_check = $this->GetCountAnaproSottofascicoli($Result_rec['GESKEY']);
            $Result_tab[$key]['SOTTOFASCICOLI'] = $anapro_check['SOTTOFASCICOLI'];

            $sql = "
                SELECT ANAPRO.PROFASKEY,COUNT(ANADOC.ROWID) AS QUANTI FROM ANAPRO 
                LEFT OUTER JOIN ANADOC ANADOC ON ANADOC.DOCNUM=ANAPRO.PRONUM AND ANADOC.DOCPAR=ANAPRO.PROPAR 
                WHERE PROFASKEY='{$Result_rec['GESKEY']}'
                GROUP BY PROFASKEY
                ";
            $anadoc_check = $this->proLib->getGenericTab($sql, false);
//            App::log($anadoc_check);
            if ($anadoc_check['QUANTI'] > 0) {
                $Result_tab[$key]['STATOALL'] = "<span class=\"ui-icon ui-icon-document\">Ci sono Allegati</span>";
            } else {
                $Result_tab[$key]['STATOALL'] = "";
            }
            if ($Result_rec['SIGLA']) {
                $Result_tab[$key]['DESCSERIE'] = $Result_tab[$key]['DESCSERIE'] . ' - ' . $Result_rec['SIGLA'];
            }
        }
        return $Result_tab;
    }

    public function apriNuovo() {
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        $this->Nascondi();
        $this->AzzeraVariabili();
        /* Pulizia div serie */
        Out::clearFields($this->nameForm . '_divSerie');
//        Out::attributo($this->nameForm . '_PROGES[GESDCH]', 'readonly', '1');
        Out::show($this->nameForm . '_Aggiungi');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::valore($this->nameForm . '_PROGES[GESDRE]', $this->workDate);
        $this->AbilitaTitolario();

        $profilo = proSoggetto::getProfileFromIdUtente();
        $anamed_rec = $this->proLib->GetAnamed($profilo['COD_SOGGETTO']);
        Out::valore($this->nameForm . '_PROGES[GESRES]', $anamed_rec['MEDCOD']);
        Out::valore($this->nameForm . '_Desc_resp2', $anamed_rec['MEDNOM']);
        $uffdes_tab = $this->proLib->getGenericTab("SELECT ANAUFF.UFFCOD FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFKEY='" . $anamed_rec['MEDCOD'] . "' AND ANAUFF.UFFANN=0 ORDER BY UFFFI1__3 DESC");
        $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
        Out::valore($this->nameForm . '_PROGES[GESUFFRES]', $anauff_rec['UFFCOD']);
        Out::valore($this->nameForm . '_UFFRESP', $anauff_rec['UFFDES']);
//        $this->DecodAnamed($profilo['COD_SOGGETTO'], $this->nameForm . '_PROGES[GESRES]', 'codice');
        $this->caricaUof();
        $this->toggleDatiPrincipali('abilita');
        Out::valore($this->nameForm . '_PROGES[GESPROUTE]', App::$utente->getKey('nomeUtente'));
        /* Blocco il progressivo e sblocco il codice delle serie */
        $this->BloccaSerie();
        Out::enableField($this->nameForm . '_SERIE[CODICE]');
        Out::hide($this->nameForm . '_MOD_SERIE');
        // Le serie Verranno visualizzate solo se abilitate nel titolario indicato.
        // Defautl Natura fascicolo: Ibrido
        Out::valore($this->nameForm . '_ANAORG[NATFAS]', 2);

        Out::setFocus('', $this->nameForm . '_Catcod');
    }

    public function openPratica($Indice, $tipo = 'rowid') {
        Out::tabRemove($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDati");
        $anaent_33 = $this->proLib->GetAnaent('33');
        if (!$anaent_33['ENTDE5']) {
            Out::tabRemove($this->nameForm . "_tabProcedimento", $this->nameForm . "_panePassi");
        }
        $this->openPratica = true;
        if (!$this->Dettaglio($Indice, $tipo)) {
            $this->close();
        }
    }

    public function Dettaglio($Indice, $tipo = 'rowid') {
        /* Pulizia div serie */
        $this->fl_manutenzioneSerie = '';
        $this->SbloccaSerie();
        Out::clearFields($this->nameForm . '_divSerie');
        $proges_rec = $this->proLibPratica->GetProges($Indice, $tipo);
        if (!$proges_rec) {
            $this->OpenRicerca();
            Out::msgStop("Attenzione", "Fascicolo non accessibile.");
            return false;
        }
        $this->currGesnum = $proges_rec['GESNUM'];
        $this->geskey = $proges_rec['GESKEY'];

        $anaorg_rec = $this->proLib->GetAnaorg($this->geskey, 'orgkey');
        if (!$anaorg_rec) {
            $this->OpenRicerca();
            Out::msgStop("Attenzione", "Fascicolo Anaorg non accessibile.");
            return false;
        }

        $anapro_fascicolo = $this->proLibFascicolo->getAnaproFascicolo($this->geskey);
        if (!$anapro_fascicolo) {
            Out::msgStop("Attenzione", "Dati struttura pratica non accessibili.");
            return false;
        }
        $anapro_fascicolo_secure_tab = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'fascicolo', $this->geskey);
        if (!$anapro_fascicolo_secure_tab) {
            Out::msgStop("Accesso al fascicolo", "Fascicolo non accessibile");
            return false;
        }


        $rigaSel = $_POST['selRow'];
        $this->returnModel = $_POST[$this->nameForm . '_returnModel'];
        $this->returnId = $_POST[$this->nameForm . '_returnId'];
        $prasta_rec = $this->proLibPratica->GetPrasta($proges_rec['GESNUM'], 'codice');
        // Evitato caricamento praSoggetti non più richiesto.
        //$this->praSoggetti = praSoggetti::getInstance($this->praLib, $this->currGesnum);// Non utilizzato 28-03/2017
        $anapra_rec = $this->praLib->GetAnapra($proges_rec['GESPRO']);
        $anamed_rec = $this->proLib->GetAnamed($proges_rec['GESRES']);
        $anauff_rec = $this->proLib->GetAnauff($proges_rec['GESUFFRES']);

        $this->Nascondi();
        $infoRis = '';
        if ($proges_rec['GESDCH']) {
            Out::show($this->nameForm . '_divIcona');
            $infoRis = '<div style="display:inline-block;" class="ita-icon ita-icon-divieto-24x24" Title="Fascicolo Chiuso">&nbsp;</div><div style="display:inline-block;"> &nbsp; Fascicolo Chiuso</div>';
        }
        Out::html($this->nameForm . '_divIcona', $infoRis);
        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
        if ($PARMENTE_rec['TIPOPROTOCOLLO'] != 'Italsoft') {
            Out::hide($this->nameForm . '_Anno_prot_butt');
        }

        $open_Info = 'Oggetto: ' . $proges_rec['GESNUM'];
        $this->openRecord($this->PROT_DB, 'PROGES', $open_Info);

        $Numero_procedimento = substr($proges_rec['GESNUM'], 14) . " / " . substr($proges_rec['GESNUM'], 10, 4);
        Out::valore($this->nameForm . '_Numero_procedimento', $Numero_procedimento);

        $Anareparc_rec = $this->proLib->getAnareparc($proges_rec['GESREP']);
        Out::html($this->nameForm . '_Numero_procedimento_lbl', $Anareparc_rec['DESCRIZIONE']);

        Out::html($this->nameForm . '_PRASTA[STADES]', $prasta_rec['STADES']);
        $this->caricaUof();
        Out::valori($proges_rec, $this->nameForm . '_PROGES');

        Out::valore($this->nameForm . '_ANAORG[VERSIONE_T]', $anaorg_rec['VERSIONE_T']);
        Out::valore($this->nameForm . '_ANAORG[ORGSEG]', $anaorg_rec['ORGSEG']);
        // Valorizzazione Natura del fascicolo.
        Out::valore($this->nameForm . '_ANAORG[NATFAS]', $anaorg_rec['NATFAS']);
        // Valorizzazione ORGKEYPRE
        Out::valore($this->nameForm . '_ANAORG[ORGKEYPRE]', $anaorg_rec['ORGKEYPRE']);

        if ($proges_rec['GESDCH'] == '') {
            Out::show($this->gridDocumenti . '_delGridRow');
        } //else {
//            Out::show($this->nameForm . '_Apri');
//        }

        /*
         * Valorizzazione utente originario: 
         *  Carico ultimo utente:
         */
        $anaprosave_tab = $this->proLib->getGenericTab("SELECT ROWID, PROUTE, PROUOF FROM ANAPROSAVE WHERE PRONUM=" . $anapro_fascicolo['PRONUM'] . " AND PROPAR='" . $anapro_fascicolo['PROPAR'] . "' ORDER BY ROWID");
        if ($anaprosave_tab) {
            $anaufforig_rec = $this->proLib->GetAnauff($anaprosave_tab[0]['PROUOF']);
            Out::valore($this->nameForm . '_UTENTEORIGINARIO', 'Creato da: ' . $anaprosave_tab[0]['PROUTE'] . " - " . $anaufforig_rec['UFFDES']);
        } else {
            $anaufforig_rec = $this->proLib->GetAnauff($anapro_fascicolo['PROUOF']);
            Out::valore($this->nameForm . '_UTENTEORIGINARIO', 'Creato da: ' . $anapro_fascicolo['PROUTE'] . " - " . $anaufforig_rec['UFFDES']);
        }
        $anauffulti_rec = $this->proLib->GetAnauff($anapro_fascicolo['PROUOF']);
        Out::valore($this->nameForm . '_UTENTEULTIMO', 'Ultima Mod.: ' . $anapro_fascicolo['PROUTE'] . " - " . $anauffulti_rec['UFFDES']);
        /*
         * Carico utente in corso:
         */
        Out::valore($this->nameForm . '_PROGES[GESPROUTE]', App::$utente->getKey('nomeUtente'));

        Out::valore($this->nameForm . '_Desc_proc2', $anapra_rec['PRADES__1']);
        Out::valore($this->nameForm . '_Desc_resp2', $anamed_rec['MEDNOM']);
        Out::valore($this->nameForm . '_UFFRESP', $anauff_rec['UFFDES']);
        $this->CaricaAllegati();

        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::show($this->nameForm . '_ProtocollaAllegatiFas');
        Out::show($this->nameForm . '_StampaEleProtoc');
        Out::unblock($this->nameForm . "_divIntestatario");
        Out::unblock($this->nameForm . "_divAltriDati");

//        Out::attributo($this->nameForm . '_PROGES[GESDCH]', 'readonly', '0');
        Out::attributo($this->nameForm . '_PROGES[GESPAR]', 'readonly', '0');
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_panePassi");
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDocumenti");
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneIter");
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAggiuntivi");
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneNote");
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::setFocus('', $this->nameForm . '_ANAUNI[UNISET]');
        if ($rigaSel) {
            Out::codice("jQuery('#$this->gridPassi').jqGrid('setSelection','$rigaSel');");
        } else {
            Out::show($this->divGes);
        }
        $this->noteManager = proNoteManager::getInstance($this->proLib, proNoteManager::NOTE_CLASS_FASCICOLO, array("PROFASKEY" => $anapro_fascicolo['PROFASKEY']));
        $this->caricaNote();
        TableView::disableEvents($this->gridGest);
        if ($anapro_fascicolo['PRORISERVA']) {
            Out::show($this->nameForm . "_NonRiserva");
            Out::show($this->nameForm . '_protRiservato');
            Out::valore($this->nameForm . '_protRiservato', 'RISERVATO');
        } else {
            Out::show($this->nameForm . "_Riserva");
        }
        $this->praLib->GetAnaspa('1');

        $anaorg_rec = $this->DecodAnaorg($this->geskey, 'orgkey');
        $this->DecodAnacat(substr($anaorg_rec['ORGCCF'], 0, 4));
        $this->DecodAnacla(substr($anaorg_rec['ORGCCF'], 0, 8));
        $this->DecodAnafas($anaorg_rec['ORGCCF']);
        $this->DisabilitaTitolario();

        $this->permessiFascicolo($anapro_fascicolo['PRONUM'], $anapro_fascicolo['PROPAR']);
        /*
         * Nuova funzione per disabilitare/abilitare campi
         */
        $this->AbilitaDisabilitaCampi($proges_rec);
        // Out Descrizione Versione Titolario:
        Out::hide($this->nameForm . '_VersioneTitolario');
        if (!$this->proLibTitolario->CheckVersioneUnica()) {
            Out::show($this->nameForm . '_VersioneTitolario');
            $Versione_rec = $this->proLibTitolario->GetVersione($anaorg_rec['VERSIONE_T'], 'codice');
            Out::html($this->nameForm . '_VersioneTitolario', '(' . $Versione_rec['DESCRI_B'] . ')');
        }

        // Controllo se disabilitare la serie:
        if ($anaorg_rec['CODSERIE']) {
            $this->DecodificaSerie($anaorg_rec['CODSERIE'], $anaorg_rec);
            $this->BloccaSerie();
        }

        // Fascicoli collegati
        if ($anaorg_rec['ORGKEYPRE']) {
            Out::show($this->nameForm . '_FascicoliCollegati');
        }
        $this->VisualizzaNascondiSerie($anaorg_rec['ORGCCF'], $anaorg_rec['VERSIONE_T']);

        /*
         * Fascicolo/Pratica Collegata
         */
        Out::hide($this->nameForm . '_divInfoPraFas');
        if ($anaorg_rec['GESNUMFASC']) {
            $Anno = substr($anaorg_rec['GESNUMFASC'], 0, 4);
            $Numero = substr($anaorg_rec['GESNUMFASC'], 4);
            $messaggio = "<span style=\"color:red\">FASCICOLO PROCEDIMENTALE: $Anno / $Numero </span>";
            $messaggio .= ' <a href="#" id="' . $anaorg_rec['GESNUMFASC'] . '" class="ita-hyperlink {event:\'' . $this->nameForm . '_VaiAPratica\'}"><span style="display:inline-block;vertical-align:bottom;" title="Vai al Fascicolo" class="ita-tooltip ita-icon ita-icon-open-folder-16x16"></span> Vai al Fascicolo</a>';
            Out::show($this->nameForm . '_divInfoPraFas');
            Out::html($this->nameForm . "_divInfoPraFas", $messaggio);
        }
        $this->CheckFascicoloConservato($anapro_fascicolo);

        return true;
    }

    private function AbilitaDisabilitaCampi($proges_rec) {
        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum');
        if ($permessiFascicolo[proLibFascicolo::PERMFASC_MODIFICA_DATI] && $proges_rec['GESDCH'] == '') {
            $this->toggleDatiPrincipali('abilita');
            $this->toggleBottoniPrincipali('abilita');
        } else {
            $this->toggleDatiPrincipali('disabilita');
            $this->toggleBottoniPrincipali('disabilita');
            if ($permessiFascicolo[proLibFascicolo::PERMFASC_MODIFICA_DATI]) {
                Out::show($this->nameForm . '_Apri');
            } else {
                Out::hide($this->nameForm . '_delGridRow');
            }
        }
    }

    private function checkExistCommercio() {
        try {
            $commDB = ItaDB::DBOpen('COMM');
            $record = ItaDB::DBSQLSelect($commDB, "SHOW TABLES FROM " . $commDB->getDB() . " LIKE 'COMLIC'");
        } catch (Exception $exc) {
            $exc = $exc;
//out::msgStop("Attenzione", $exc->getMessage());
        }
        if ($commDB == "") {
            return false;
        } else {
            if (!$record) {
                return false;
            }
        }
        return true;
    }

    private function caricaDati($procedimento) {
        $Dati_view = ItaDB::DBSQLSelect($this->PROT_DB, "
            SELECT
                PRODAG.ROWID,
                PROSEQ,
                PRODPA,
                PROPAK,
                DAGPAK,
                DAGKEY,
                DAGPRI,
                DAGVAL,
                DAGSEQ,
                DAGDES,
                DAGALIAS
            FROM
                PROPAS PROPAS
            LEFT OUTER JOIN
                PRODAG PRODAG            
            ON
                PRODAG.DAGPAK = PROPAS.PROPAK
            WHERE
                DAGNUM = '" . $procedimento . "' ORDER BY DAGPRI DESC,PROSEQ,DAGSEQ
            ", true);
        foreach ($Dati_view as $key => $dato) {
            $Dati_view[$key]['PROSEQ'] = $dato['PROSEQ'] . " - " . $dato['PRODPA'];
            $Dati_view[$key]['DAGPRI'] = $dato['DAGPRI'];
            if ($dato['DAGPRI'] != 0) {
                $Dati_view[$key]['DAGSEQ'] = "<p style = 'color:red;font-weight:bold;font-size:1.3em;'>" . $dato['DAGSEQ'] . "</p>";
                $Dati_view[$key]['DAGKEY'] = "<p style = 'color:red;font-weight:bold;font-size:1.3em;'>" . $dato['DAGKEY'] . "</p>";
                $Dati_view[$key]['PROSEQ'] = "<p style = 'color:red;font-weight:bold;font-size:1.3em;'>" . $dato['PROSEQ'] . " - " . $dato['PRODPA'] . "</p>";
            }
        }
        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
        if (!$this->praPerms->checkSuperUser($proges_rec)) {
            return $this->praPerms->filtraDatiAggView($this->proAzioni, $Dati_view);
        } else {
            return $Dati_view;
        }
    }

    private function caricaDatiPratica() {
        $sql = "SELECT PRODAG.*,
            PROPAS.PROSEQ,
            PROPAS.PRODPA
            FROM PRODAG 
            LEFT OUTER JOIN PROPAS PROPAS ON PRODAG.DAGPAK = PROPAS.PROPAK
            WHERE PRODAG.DAGPAK LIKE '" . $this->currGesnum . "%'";
        if ($_POST['PROSEQ']) {
            $sql .= " AND " . $this->PRAM_DB->strUpper('PROPAS.PROSEQ') . " = '" . addslashes(strtoupper($_POST['PROSEQ'])) . "'";
        }
        if ($_POST['PRODPA']) {
            $sql .= " AND " . $this->PRAM_DB->strUpper('PROPAS.PRODPA') . " LIKE '%" . addslashes(strtoupper($_POST['PRODPA'])) . "%'";
        }
        if ($_POST['DAGKEY']) {
            $sql .= " AND " . $this->PRAM_DB->strUpper('PRODAG.DAGKEY') . " LIKE '%" . addslashes(strtoupper($_POST['DAGKEY'])) . "%'";
        }
        if ($_POST['DAGDES']) {
            $sql .= " AND " . $this->PRAM_DB->strUpper('PRODAG.DAGDES') . " LIKE '%" . addslashes(strtoupper($_POST['DAGDES'])) . "%'";
        }
        if ($_POST['DAGVAL']) {
            $sql .= " AND " . $this->PRAM_DB->strUpper('PRODAG.DAGVAL') . " LIKE '%" . addslashes(strtoupper($_POST['DAGVAL'])) . "%'";
        }
        $sql .= " ORDER BY PRODAG.DAGPAK";
        $datiPratica = $this->proLib->getGenericTab($sql);
        return $datiPratica;
    }

    private function caricaPassiItepas($procedimento, $dbSuffix = "") {
        if ($dbSuffix == "") {
            $Pram_db = $this->PRAM_DB;
        } else {
            $Pram_db = ItaDB::DBOpen('PRAM', $dbSuffix);
        }
        $sql = "SELECT
                    ITEPAS.ROWID AS ROWID,
                    ITEPAS.ITESEQ AS ITESEQ,
                    ITEPAS.ITEGIO AS ITEGIO,
                    ITEPAS.ITEDES AS ITEDES,
                    ITEPAS.ITEPUB AS ITEPUB,
                    ITEPAS.ITEOBL AS ITEOBL,
                    ITEPAS.ITECTP AS ITECTP,
                    ITEPAS.ITEQST AS ITEQST,
                    ITEPAS.ITEVPA AS ITEVPA,
                    ITEPAS.ITEVPN AS ITEVPN,
                    ITEPAS.ITEKEY AS ITEKEY,
                    PRACLT.CLTDES AS CLTDES," .
                $Pram_db->strConcat('ANANOM.NOMCOG', "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE
                FROM ITEPAS LEFT OUTER JOIN ANANOM ON ANANOM.NOMRES=ITEPAS.ITERES
                LEFT OUTER JOIN PRACLT ON PRACLT.CLTCOD=ITEPAS.ITECLT
                WHERE ITEPAS.ITECOD = '" . $procedimento . "' ORDER BY ITESEQ";
        $passi = ItaDB::DBSQLSelect($Pram_db, $sql);
        return $passi;
    }

    private function caricaAzioni($procedimento) {
        $sql = "SELECT
                    PROPAS.ROWID AS ROWID,
                    PROPAS.PROSEQ AS PROSEQ,
                    PROPAS.PRONODE AS PRONODE,
                    PROPAS.PRORIS AS PRORIS,
                    PROPAS.PROGIO AS PROGIO,
                    PROPAS.PROTPA AS PROTPA,
                    PROPAS.PRODTP AS PRODTP,
                    PROPAS.PRODPA AS PRODPA,
                    PROPAS.PROFIN AS PROFIN,
                    PROPAS.PROVPA AS PROVPA,
                    PROPAS.PROVPN AS PROVPN,
                    PROPAS.PROPAK AS PROPAK,
                    PROPAS.PROCTR AS PROCTR,
                    PROPAS.PROQST AS PROQST,
                    PROPAS.PROPUB AS PROPUB,
                    PROPAS.PROALL AS PROALL,
                    PROPAS.PRORPA AS PRORPA,
                    PROPAS.PROSTAP AS PROSTAP,
                    PROPAS.PROPART AS PROPART,
                    PROPAS.PROSTCH AS PROSTCH,
                    PROPAS.PROSTATO AS PROSTATO,                    
                    PROPAS.PROPCONT AS PROPCONT,
                    PROPAS.PROVISIBILITA AS PROVISIBILITA,
                    PROPAS.PROUTEADD AS PROUTEADD,
                    PROPAS.PROUTEEDIT AS PROUTEEDIT,
                    PROPAS.PROINI AS PROINI,
                    PROPAS.PASPRO AS PASPRO,
                    PROPAS.PASPAR AS PASPAR,
                    ANAMED.MEDNOM AS RESPONSABILE
                FROM PROPAS
                    LEFT OUTER JOIN ANAMED ON ANAMED.MEDCOD=PROPAS.PRORPA
               WHERE PRONODE = 1 AND PASPAR = 'F' AND PROPAS.PRONUM = '" . $procedimento . "' ORDER BY PROSEQ";
        $propas_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);

        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
        $anamed_rec = $this->proLib->GetAnamed($proges_rec['GESRES']);
        $level = 0;
        $inc = 1;
        $passi_tree = array();
        $passi_tree[$inc]['level'] = $level;
        $passi_tree[$inc]['parent'] = '';
        $passi_tree[$inc]['isLeaf'] = 'false';
        $passi_tree[$inc]['expanded'] = 'true';
        $passi_tree[$inc]['loaded'] = 'true';
        $passi_tree[$inc]['PROINI'] = "";
        $passi_tree[$inc]['PROFIN'] = "";
        $passi_tree[$inc]['NODEKEY'] = $propas_rec['PROPAK'];
        $passi_tree[$inc]['PRONODE'] = '<span class="ita-icon ita-icon-open-folder-24x24">Fascicolo</span>';
        $passi_tree[$inc]['ADDAZIONE'] = '<span class="ui-icon ui-icon-plus">Aggiungi</span>';
        $passi_tree[$inc]['RESPONSABILE'] = $anamed_rec['MEDNOM'];
        $passi_tree[$inc]['PRODPA'] = $propas_rec['PASPRO'] . " - " . $propas_rec['PASPAR']; //$proges_rec['GESOGG'];
        return $this->caricaTreePassi($passi_tree, $procedimento, $propas_rec['PASPRO'], $propas_rec['PASPAR'], $level + 1);
    }

    private function caricaTreePassi($passi_tree, $procedimento, $parentPro = '', $parentPar = '', $level = 0) {
        $sql = "SELECT
                    PROPAS.ROWID AS ROWID,
                    PROPAS.PROSEQ AS PROSEQ,
                    PROPAS.PRONODE AS PRONODE,
                    PROPAS.PRORIS AS PRORIS,
                    PROPAS.PROGIO AS PROGIO,
                    PROPAS.PROTPA AS PROTPA,
                    PROPAS.PRODTP AS PRODTP,
                    PROPAS.PRODPA AS PRODPA,
                    PROPAS.PROFIN AS PROFIN,
                    PROPAS.PROVPA AS PROVPA,
                    PROPAS.PROVPN AS PROVPN,
                    PROPAS.PROPAK AS PROPAK,
                    PROPAS.PROCTR AS PROCTR,
                    PROPAS.PROQST AS PROQST,
                    PROPAS.PROPUB AS PROPUB,
                    PROPAS.PROALL AS PROALL,
                    PROPAS.PRORPA AS PRORPA,
                    PROPAS.PROSTAP AS PROSTAP,
                    PROPAS.PROPART AS PROPART,
                    PROPAS.PROSTCH AS PROSTCH,
                    PROPAS.PROSTATO AS PROSTATO,                    
                    PROPAS.PROPCONT AS PROPCONT,
                    PROPAS.PROVISIBILITA AS PROVISIBILITA,
                    PROPAS.PROUTEADD AS PROUTEADD,
                    PROPAS.PROUTEEDIT AS PROUTEEDIT,
                    PROPAS.PROINI AS PROINI,
                    PROPAS.PASPRO AS PASPRO,
                    PROPAS.PASPAR AS PASPAR,
                    PROPASPARENT.PROPAK AS PAKPARENT,
                    ANAMED.MEDNOM AS RESPONSABILE
                FROM PROPAS
                    LEFT OUTER JOIN ANAMED ON ANAMED.MEDCOD=PROPAS.PRORPA
                    LEFT OUTER JOIN ORGCONN ON ORGCONN.PRONUM=PROPAS.PASPRO AND ORGCONN.PROPAR=PASPAR
                    LEFT OUTER JOIN PROPAS PROPASPARENT ON ORGCONN.PRONUMPARENT=PROPASPARENT.PASPRO AND ORGCONN.PROPARPARENT=PROPASPARENT.PASPAR
               WHERE PROPAS.PRONUM = '" . $procedimento . "' AND ORGCONN.CONNDATAANN = '' AND  ORGCONN.PRONUMPARENT='$parentPro' AND ORGCONN.PROPARPARENT='$parentPar' ORDER BY PROSEQ";

        $passi_view = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        if ($passi_view) {
            foreach ($passi_view as $keyPasso => $value) {
                $inc = count($passi_tree) + 1;
                $msgStato = $icon = $acc = $cons = "";
                $passi_view[$keyPasso]['NODEKEY'] = $passi_view[$keyPasso]['PROPAK'];
                $passi_view[$keyPasso]['level'] = $level;
                $passi_view[$keyPasso]['isLeaf'] = 'false';
                $passi_view[$keyPasso]['expanded'] = 'true';
                $passi_view[$keyPasso]['loaded'] = 'true';
                if ($passi_view[$keyPasso]['PAKPARENT']) {
                    $passi_view[$keyPasso]['parent'] = $passi_view[$keyPasso]['PAKPARENT'];
                } else {
                    $passi_view[$keyPasso]['parent'] = 'ROOT';
                }
                if ($value['PRONODE'] != 0) {
                    $passi_view[$keyPasso]['PRONODE'] = '<span class="ita-icon ita-icon-open-folder-24x24">Sottofascicolo</span>';
                    $passi_view[$keyPasso]['ADDAZIONE'] = '<span class="ui-icon ui-icon-plus">Aggiungi</span>';
                } else {
                    $passi_view[$keyPasso]['PRONODE'] = '<span class="ita-icon ita-icon-edit-24x24">Azione</span>';
                    $passi_view[$keyPasso]['ADDAZIONE'] = '';
                }
                if ($value['PROSTATO'] != 0) {
                    $Anastp_rec = $this->praLib->GetAnastp($value['PROSTATO']);
                    $msgStato = $Anastp_rec['STPFLAG'];
                }
                if ($value['PROFIN']) {
                    if ($msgStato == 'In corso') {
                        $msgStato = "";
                    }
                    $msgStato = $Anastp_rec['STPDES'];
                    $passi_view[$keyPasso]['STATOPASSO'] = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-check-green-24x24\">Azione Chiusa</span><span style=\"vertical-align:top;display:inline-block;\">$msgStato</span>";
                } elseif ($value['PROINI']) {
                    $passi_view[$keyPasso]['STATOPASSO'] = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-check-red-24x24\">Azione Aperta</span><span style=\"display:inline-block;\">$msgStato</span>";
                }


                if ($value['PROPART'] != 0) {
                    $passi_view[$keyPasso]['ARTICOLO'] = '<span class="ita-icon ita-icon-rtf-24x24">Articolo</span>';
                }
                if ($value['PROALL']) {
                    $passi_view[$keyPasso]['ALLEGATI'] = '<span class="ita-icon ita-icon-clip-16x16">allegati</span>';
                }
                $passi_tree[$inc] = $passi_view[$keyPasso];
                $save_count = count($passi_tree);
                $passi_tree = $this->caricaTreePassi($passi_tree, $procedimento, $passi_view[$keyPasso]['PASPRO'], $passi_view[$keyPasso]['PASPAR'], $level + 1);
                if ($save_count == count($passi_tree)) {
                    $passi_tree[$inc]['isLeaf'] = 'true';
                }
            }
        }
        return $passi_tree;
    }

    private function ApriScanner() {
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                $this->returnToParent();
            }
        }
        $parametriCaps = $this->caricaParametriCaps($_POST[$this->nameForm . '_PROGES']);
        $modelTwain = 'utiTwain';
        itaLib::openForm($modelTwain, true);
        $appRoute = App::getPath('appRoute.' . substr($modelTwain, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $modelTwain . '.php';
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST[$modelTwain . '_parametriCaps'] = $parametriCaps;
        $_POST[$modelTwain . '_returnModel'] = $this->nameForm;
        $_POST[$modelTwain . '_returnMethod'] = 'returnFileFromTwain';
        $_POST[$modelTwain . '_returnField'] = $this->nameForm . '_Scanner';
        $_POST[$modelTwain . '_closeOnReturn'] = '1';
        $modelTwain();
    }

    private function SalvaScanner() {
        $randName = $_POST['retFile'];
        $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
        $timeStamp = date("Ymd_His");
        $origFile = "Scansione_" . $timeStamp . "." . pathinfo($randName, PATHINFO_EXTENSION);
        $this->aggiungiAllegato($randName, $destFile, $origFile);
    }

    private function AllegaFile() {
        $this->currAllegato = null;
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                $this->returnToParent();
            }
        }
        if ($_POST['response'] == 'success') {
            $origFile = $_POST['file'];
            $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
            $randName = md5(rand() * time()) . "." . pathinfo($uplFile, PATHINFO_EXTENSION);
            $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
            if (strtoupper(pathinfo($uplFile, PATHINFO_EXTENSION)) == "P7M") {
                $this->praLib->AnalizzaP7m($uplFile);
            }

            $retVerify = $this->praLib->verificaPDFA($uplFile);
            if ($retVerify['status'] !== 0) {
                if ($retVerify['status'] == -5) {
                    $Filde2 = $this->praLib->getFlagPDFA();
//                    $verifyPDFA = substr($Filde2, 0, 1);
                    $convertPDFA = substr($Filde2, 1, 1);
//                    $PDFLevel = substr($Filde2, 2, 1);
                    if (!$convertPDFA) {
                        $this->currAllegato = array(
                            'uplFile' => $uplFile,
                            'randName' => $randName,
                            'destFile' => $destFile,
                            'origFile' => $origFile
                        );
                        Out::msgQuestion("Allegato non conforme PDF/A ", $retVerify['message'], array(
                            'F8-Rifiuta Allegato' => array('id' => $this->nameForm . '_AnnullaPDFA', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Accetta Allegato' => array('id' => $this->nameForm . '_ConfermaPDFA', 'model' => $this->nameForm, 'shortCut' => "f5"),
                            'F1-Converti Allegato' => array('id' => $this->nameForm . '_ConvertiPDFA', 'model' => $this->nameForm, 'shortCut' => "f1")
                                )
                        );
                    } else {
                        $retConvert = $this->praLib->convertiPDFA($uplFile, $destFile, true);
                        if ($retConvert['status'] == 0) {
                            $this->aggiungiAllegato($randName, $destFile, $origFile);
                            Out::msgInfo('Allega PDF', "Allegato PDF Convertito a PDF/A verifica il PDF." . $this->currAllegato['origFile']);
                            Out::openDocument(utiDownload::getUrl($origFile, $destFile));
                        } else {
                            Out::msgStop("Conversione PDF/A Impossibile", $retConvert['message']);
                        }
                    }
                } else {
                    Out::msgStop("Verifica PDF/A Impossibile", $retVerify['message']);
                    unlink($uplFile);
                }
                return;
            } else {
                if (!@rename($uplFile, $destFile)) {
                    Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                } else {
                    $this->aggiungiAllegato($randName, $destFile, $origFile);
                }
            }
        } else {
            Out::msgStop("Upload File", "Errore in Upload");
        }
    }

    private function aggiungiAllegato($randName, $destFile, $origFile) {
        // Out::msgInfo('passa qui', 'ci passa');
        if (!$destFile) {
            return;
        }
        foreach ($this->proDocumenti as $allegato) {
            if ($allegato['parent'] == 'seq_GEN' || ($allegato['SEQ'] == 'seq_GEN' && $allegato['parent'] == null)) {
                $arrayGenerale[] = $allegato;
            }
        }
        if (!$arrayGenerale) {
//Padre Allegati Generali
            $chiave = count($this->proDocumenti) + 1;
            $arrayGenerali['SEQ'] = 'seq_GEN';
            $arrayGenerali['NAME'] = "Allegati Generali";
            $arrayGenerali['level'] = 0;
            $arrayGenerali['parent'] = null;
            $arrayGenerali['isLeaf'] = 'false';
            $arrayGenerali['expanded'] = 'true';
            $arrayGenerali['loaded'] = 'true';
            $this->proDocumenti[$chiave] = $arrayGenerali;
        }
        $ext = pathinfo($destFile, PATHINFO_EXTENSION);
        if (strtolower($ext) == "p7m") {
            $edit = "<span class=\"ita-icon ita-icon-shield-blue-16x16\">Verifica Firma</span>";
        } else if (strtolower($ext) == "zip") {
            $edit = "<span class=\"ita-icon ita-icon-winzip-16x16\">estrai File Zip</span>";
        }

        $chiave = count($this->proDocumenti) + 1;
        $arrayGen['SEQ'] = $chiave;

        $arrayGen['NAME'] = "<span style = \"color:orange;\">$origFile</span>";
        $arrayGen['NOTE'] = "File originale: " . $origFile;
        $arrayGen['INFO'] = 'GENERALE';
        $arrayGen['EDIT'] = $edit;
        $arrayGen['EVIDENZIA'] = "<span class=\"ita-icon ita-icon-evidenzia-16x16\">Evidenzia Allegato</span>";
        $arrayGen['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-16x16\">Blocca Allegato</span>";

//Valorizzo Array
        $arrayGen['FILENAME'] = $randName;
        $arrayGen['FILEINFO'] = "File originale: " . $origFile;
        $arrayGen['FILEORIG'] = $origFile;
        $arrayGen['FILEPATH'] = $destFile;
        $arrayGen['PASLOCK'] = 0;
        $arrayGen['ROWID'] = 0;
        $arrayGen['level'] = 1;
        $arrayGen['parent'] = 'seq_GEN';
        $arrayGen['isLeaf'] = 'true';
        $this->proDocumenti[$chiave] = $arrayGen;
        $this->bloccaCelleGrigliaDocumenti('3');
        Out::setFocus('', $this->nameForm . '_wrapper');
    }

    private function CaricaGriglia($griglia, $appoggio, $tipo = '1') {
        if (is_null($appoggio))
            $appoggio = array();
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        if ($tipo == '1') {
            if ($_POST['page'] == "") {
                $ita_grid01->setPageNum(1);
            } else {
                $ita_grid01->setPageNum($_POST['page']);
            }
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(count($appoggio));
        } else if ($tipo == '3') {
            if ($_POST['page'] == "") {
                $ita_grid01->setPageNum(1);
            } else {
                $ita_grid01->setPageNum($_POST['page']);
            }
            $ita_grid01->setPageRows($_POST[$griglia]['gridParam']['rowNum']);
        } else {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(100000);
        }
        TableView::enableEvents($griglia);
        $ita_grid01->getDataPage('json', true);
        return;
    }

    private function CaricaIter($fascicolo) {
        $this->proIter = $this->proLibFascicolo->CaricaIter($fascicolo);
    }

    private function CaricaStrutturaIter($fascicolo) {
        $this->proIter = $this->proLibFascicolo->CaricaStrutturaIter($fascicolo);
    }

    private function CaricaAllegati($where = array(), $tipoTable = '1') {
        $this->AlberoEreditaVisibilita = array();
        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
        $anapro_F_rec = $this->proLibFascicolo->getAnaproFascicolo($this->geskey);
        //$gestione = $this->checkProprietario($anapro_F_rec['PRONUM'], $anapro_F_rec['PROPAR']);
        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum');
        if ($permessiFascicolo) {
            if ($permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI] ||
                    $permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_PROTOCOLLI] ||
                    $permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_SOTTOFASCICOLI]) {
                $gestione = true;
            }
        }

        $anaogg_F_rec = $this->proLib->GetAnaogg($anapro_F_rec['PRONUM'], $anapro_F_rec['PROPAR']);
        if (!$anapro_F_rec) {
            return false;
        }
        $orgnode_rec = $this->proLib->GetOrgNode($anapro_F_rec['PRONUM'], 'codice', $anapro_F_rec['PROPAR']);
        if (!$orgnode_rec) {
            return false;
        }

        $level = 0;
        $inc = "PRO-" . $orgnode_rec['PRONUM'] . $orgnode_rec['PROPAR'];
        $documenti_tree = array();
        $documenti_tree[$inc]['level'] = $level;
        $documenti_tree[$inc]['parent'] = '';
        $documenti_tree[$inc]['isLeaf'] = 'false';
        $documenti_tree[$inc]['expanded'] = 'true';
        $documenti_tree[$inc]['loaded'] = 'true';
        $documenti_tree[$inc]['ORGNODEKEY'] = "PRO-" . $orgnode_rec['PRONUM'] . $orgnode_rec['PROPAR'];
        $documenti_tree[$inc]['ORGNODEICO'] = '<div class="ita-html"><span style="height:16px;background-size:50%;margin:2px;" title="Fascicolo ' . $anapro_F_rec['PROFASKEY'] . '" class="ita-tooltip ita-icon ita-icon-open-folder-32x32">Fascicolo</span></div>';
        if ($gestione) {
            $documenti_tree[$inc]['ADDAZIONE'] = '<div class="ita-html"><span title="Aggiungi Elementi al fascicolo" class="ita-tooltip ui-icon ui-icon-plus">Aggiungi Elementi al fascicolo</span></div>';
            $noteManager = proNoteManager::getInstance($this->proLib, proNoteManager::NOTE_CLASS_PROTOCOLLO, array("PRONUM" => $anapro_F_rec['PRONUM'], "PROPAR" => $anapro_F_rec['PROPAR']));
            if ($noteManager->getNote()) {
                if ($proges_rec['GESDCH']) {
                    $documenti_tree[$inc]['NOTEICO'] = '<div class="ita-html"><span style="width:16px;height:16px;background-size:100%;margin:2px;" title="Visualizza Note" class="ita-tooltip ita-icon ita-icon-comment-128x128"></span></div>';
                } else {
                    $documenti_tree[$inc]['NOTEICO'] = '<div class="ita-html"><span style="width:16px;height:16px;background-size:100%;margin:2px;" title="Note fascicolo" class="ita-tooltip ita-icon ita-icon-comment-edit-128x128"></span></div>';
                }
            } else {
                if (!$proges_rec['GESDCH']) {
                    $documenti_tree[$inc]['NOTEICO'] = '<div class="ita-html"><span style="width:16px;height:16px;background-size:100%;margin:2px;" title="Note " class="ita-tooltip ita-icon ita-icon-comment-new-128x128"></span></div>';
                }
            }
        }
        $documenti_tree[$inc]['ASSEGNAZIONI'] = '<div class="ita-html"><span title="Gestisci Trasmissioni Fascicolo" class="ita-tooltip ui-icon ui-icon-key">Gestisci Trasmissioni Fascicolo</span></div>';
        $riservato = "";
        if ($anapro_F_rec['PRORISERVA']) {
            $riservato = "<p style=\"background-color:lightgrey;\">RISERVATO</p>";
            $documenti_tree[$inc]['NOTE'] = $riservato;
            $documenti_tree[$inc]['PRORISERVA'] = '1';
        } else {
            $documenti_tree[$inc]['NOTE'] = $anaogg_F_rec['OGGOGG'];
        }
        $documenti_tree[$inc]['GESTIONE'] = $gestione;
        $keyparent = "PRO-" . $anapro_F_rec['PRONUM'] . $anapro_F_rec['PROPAR'];
        $documenti_andoc = $this->caricaAllegatiAnadoc($anapro_F_rec['PRONUM'], $anapro_F_rec['PROPAR'], $keyparent, $where, $level, $riservato, $gestione);
        /* Modifica per Inserire i Documenti Allegati per Primi nella tree 05.05.2016 */
//        $documenti_tree_tmp = array_merge($documenti_tree, $documenti_andoc);
//        $this->proDocumenti = $this->CaricaAllegati_tree($documenti_tree_tmp, $orgnode_rec['PRONUM'], $orgnode_rec['PROPAR'], $where, $level + 1, $gestione);
        /* Modifica per Inserire i Documenti Allegati per Ultimi nella tree 05.05.2016 */
        $this->proDocumenti = $this->CaricaAllegati_tree($documenti_tree, $orgnode_rec['PRONUM'], $orgnode_rec['PROPAR'], $where, $level + 1, $gestione);
        $this->proDocumenti = array_merge($this->proDocumenti, $documenti_andoc);
        $this->bloccaCelleGrigliaDocumenti($tipoTable);
        return;
    }

    private function CreaSqlDocumenti($nodePronum, $nodePropar) {
        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum');
        if ($permessiFascicolo[proLibFascicolo::PERMFASC_VISIBILITA_COMPLETA]) {
            $where_profilo = '';
        } else {
            $where_profilo = " AND " . proSoggetto::getSecureWhereFromIdUtente($this->proLib, 'fascicolo');
        }

        $sql = "
            SELECT
                ORGCONN.ROWID AS ROWID_ORGCONN,
                ORGCONN.PRONUM,
                ORGCONN.PROPAR,
                ORGCONN.CONNUTEMOD,
                ORGCONN.CONNDATAMOD,
                ORGCONN.CONNORAMOD,
                ORGCONN.CONNUTEINS,
                ORGCONN.CONNDATAINS,
                ORGCONN.CONNORAINS,
                ORGCONN.CONNDATAANN,
                ORGCONN.PRONUMPARENT,
                ORGCONN.PROPARPARENT,
                ORGCONN.ORGKEY,
                ANAPRO.PRODAR,
                ANAPRO.PROSUBKEY,
                ANAPRO.PROFASKEY,
                ANAPRO_PARENT.PROSUBKEY AS PROSUBKEY_PARENT,
                ANAPRO_PARENT.PROFASKEY AS PROFASKEY_PARENT,
                (SELECT OGGOGG FROM ANAOGG WHERE ANAOGG.OGGNUM=ORGCONN.PRONUM AND ANAOGG.OGGPAR=ORGCONN.PROPAR) AS OGGETTO
            FROM
                ORGCONN
            LEFT OUTER JOIN ANAPRO ANAPRO ON ANAPRO.PRONUM=ORGCONN.PRONUM AND ANAPRO.PROPAR=ORGCONN.PROPAR
            LEFT OUTER JOIN ANAPRO ANAPRO_PARENT ON ANAPRO_PARENT.PRONUM=ORGCONN.PRONUMPARENT AND ANAPRO_PARENT.PROPAR=ORGCONN.PROPARPARENT
            LEFT OUTER JOIN ARCITE ARCITE ON ARCITE.ITEPRO=ORGCONN.PRONUM AND ARCITE.ITEPAR=ORGCONN.PROPAR
            WHERE
                ORGCONN.ORGKEY = '" . $this->geskey . "' $where_profilo
            GROUP BY ORGCONN.ROWID
            ORDER BY CONNDATAMOD,CONNORAMOD";

        return $sql;
    }

    private function CaricaAllegati_tree($documenti_tree, $nodePronum, $nodePropar, $where, $level, $gestione) {
        $gestSingola = false;
        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
        $profilo = proSoggetto::getProfileFromIdUtente();
        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum');
        /* Commentato per verificare fattibilità vis completa. 01.06.2016 Alle */
        if ($permessiFascicolo[proLibFascicolo::PERMFASC_VISIBILITA_COMPLETA] || $gestione) {
            $where_profilo = '';
        } else {
            $where_profilo = '';
            //$where_profilo = " AND " . proSoggetto::getSecureWhereFromIdUtente($this->proLib, 'fascicolo');
        }
//        $where_profilo = '';
        $sql = $this->CreaSqlAllegati_tree($nodePronum, $nodePropar, $where_profilo, $where);
        $orgconn_tab = $this->proLib->getGenericTab($sql);
        // Parametro vista treeProtocollo
        $anaent_48 = $this->proLib->GetAnaent('48');
        $anaent_59 = $this->proLib->GetAnaent('59');

        $ParmTreeProto = $anaent_48['ENTDE1'];
        if ($orgconn_tab) {
            foreach ($orgconn_tab as $orgconn_rec) {
                $riservato = "";
                //$anaogg_conn_rec = $this->proLib->GetAnaogg($orgconn_rec['PRONUM'], $orgconn_rec['PROPAR']);
                $anaogg_conn_rec = $orgconn_rec;
                $inc = "PRO-" . $orgconn_rec['PRONUM'] . $orgconn_rec['PROPAR'];
                $documenti_tree[$inc]['level'] = $level;
                $documenti_tree[$inc]['parent'] = "PRO-" . $nodePronum . $nodePropar;
                $documenti_tree[$inc]['isLeaf'] = 'false';
                $documenti_tree[$inc]['expanded'] = 'true';
                $documenti_tree[$inc]['loaded'] = 'true';
                $documenti_tree[$inc]['ORGNODEKEY'] = "PRO-" . $orgconn_rec['PRONUM'] . $orgconn_rec['PROPAR'];

                $keyparent = "PRO-" . $orgconn_rec['PRONUM'] . $orgconn_rec['PROPAR'];
                $fl_unset = false;
                $iconDescr = "";
                $currConnRowid = 0;
                $iconPrinc = '';
                switch ($orgconn_rec['PROPAR']) {
                    case "F":
                        $icon = "ita-icon-open-folder-32x32";
                        $tooltip = "Fascicolo";
                        $tooltipAzione = "Aggiungi Elementi al Fascicolo";
                        $tooltipAsse = "Gestisci Trasmissioni Fascicolo";
                        $documenti_tree[$inc]['ADDAZIONE'] = '<div class="ita-html"><span title="' . $tooltipAzione . '" class="ita-tooltip ui-icon ui-icon-plus">' . $tooltipAzione . '</span></div>';
                        $documenti_tree[$inc]['ASSEGNAZIONI'] = '<div class="ita-html"><span title="' . $tooltipAsse . '" class="ita-tooltip ui-icon ui-icon-key">' . $tooltipAsse . '</span></div>';
                        break;
                    case "N":
                        $icon = "ita-icon-sub-folder-32x32";
                        $iconDescrArr = explode("-", $orgconn_rec['PROSUBKEY']);
                        $iconDescr = $iconDescrArr[count($iconDescrArr) - 1];
                        $tooltip = "Sotto-Fascicolo: " . $orgconn_rec['PROSUBKEY'];
                        $tooltipAzione = "Aggiungi Elementi al Sotto-Fascicolo";
                        $tooltipAsse = "Gestisci Trasmissioni Sotto-Fascicolo";
                        $documenti_tree[$inc]['ADDAZIONE'] = '<div class="ita-html"><span title="' . $tooltipAzione . '" class="ita-tooltip ui-icon ui-icon-plus"></span></div>';
                        $documenti_tree[$inc]['ASSEGNAZIONI'] = '<div class="ita-html"><span title="' . $tooltipAsse . '" class="ita-tooltip ui-icon ui-icon-key">' . $tooltipAsse . '</span></div>';
                        $Sottofascicolo = str_replace($orgconn_rec['PROFASKEY'] . '-', '', $orgconn_rec['PROSUBKEY']);
//                        $documenti_tree[$inc]['NUMPROT'] = $Sottofascicolo;
                        $documenti_tree[$inc]['NAME'] = 'Sottofascicolo: ' . $Sottofascicolo;
                        break; //
                    case "T":
                        $icon = "ita-icon-edit-32x32";
                        $tooltip = "Azione";
                        break;
                    case "A":
                    case "P":
                    case "C":
                        $fl_unset = true;
                        $tooltip = "Protocollo:<br>" . substr($orgconn_rec['PRONUM'], 4) . " / " . substr($orgconn_rec['PRONUM'], 0, 4) . "  -  " . $orgconn_rec['PROPAR'];
                        if ($orgconn_rec['PRORISERVA']) {
                            $tooltip .= "<br><p style=\"color:lightgrey;\">RISERVATO</p>";
                        } else {
                            $tooltip .= "<br><p style=\"color:lightblue;\">" . $anaogg_conn_rec['OGGOGG'] . "</p>";
                        }
                        $tooltip .= "<br> " . $orgconn_rec['PROSEG'];
                        $documenti_tree[$inc]['PROTOCOLLO'] = 1;

                        if ($orgconn_rec['PROFASKEY'] == $orgconn_rec['ORGKEY']) {
                            $tooltip = "<p style=\"color:lightblue;\"><u>Fascicolo Principale</u></p>" . $tooltip;
                            $iconPrinc = "<span  class=\"ita-icon ita-icon-star-yellow-16x16\" style = \"margin-left:-22px; display:inline-block;\"></span>";
                        } else {
                            $tooltip = "<p style=\"color:lightblue;\"><u>Fascicolo Secondario</u></p>" . $tooltip;
                        }
                        $documenti_tree[$inc]['NUMPROT'] = '<div class="ita-html"><span style="display:inline-block;" class="ita-tooltip" title="' . htmlspecialchars($tooltip) . '">' . substr($orgconn_rec['PRONUM'], 4) . '</span></div>';
                        $documenti_tree[$inc]['ANNOPROT'] = '<div class="ita-html"><span class="ita-tooltip" title="' . htmlspecialchars($tooltip) . '">' . substr($orgconn_rec['PRONUM'], 0, 4) . '</span></div>';
                        $documenti_tree[$inc]['DATAPROT'] = '<div class="ita-html"><span class="ita-tooltip" title="' . htmlspecialchars($tooltip) . '">' . date('d/m/Y', strtotime($orgconn_rec['PRODAR'])) . '</span></div>';
                        $documenti_tree[$inc]['PRODAR'] = $orgconn_rec['PRODAR'];
                        $documenti_tree[$inc]['PRONUM'] = $orgconn_rec['PRONUM'];
                        $documenti_tree[$inc]['PROPAR'] = $orgconn_rec['PROPAR'];
                        $provenienza = $orgconn_rec['PRONOM'];
                        $descMittdest = '';
                        switch ($orgconn_rec['PROPAR']) {
                            case 'A':
                                $descMittdest = 'Mittenti';
                                break;
                            case 'P':
                                $descMittdest = 'Destinatari';
                                break;
                            case 'C':
                                $firmatario_rec = $this->proLib->GetAnades($orgconn_rec['PRONUM'], 'codice', false, $orgconn_rec['PROPAR'], 'M');
                                $provenienza = $firmatario_rec['DESNOM'];
                                $descMittdest = 'Firmatari';
                                break;
                        }

                        $documenti_tree[$inc]['PROVENIENZA'] = '<div class="ita-html"><span class="ita-tooltip" style="display:inline-block; width:115px; overflow:hidden;" title="' . $provenienza . '" >' . $provenienza . '</span><span style="display:inline-block; width:16px;height:16px;background-size:100%;margin-left:5px; " title="Vedi altri ' . $descMittdest . '" class="ita-tooltip ita-icon ita-icon-group-32x32"></span></div>';
                        $documenti_tree[$inc]['ROWID_ORGCONN'] = $currConnRowid = $orgconn_rec['ROWID_ORGCONN'];
                        $icon = "ita-icon-register-document-32x32";
                        break;
                    case "I":
                        $fl_unset = true;
                        $Indice_rec = $this->getDatiIndice($orgconn_rec['PRONUM'], $orgconn_rec['PROPAR']);
                        //$Indice_rec = $this->segLib->GetIndice($orgconn_rec['PRONUM'], 'anapro', false, $orgconn_rec['PROPAR']);
                        $dizionarioIdelib = $this->segLib->getDizionarioFormIdelib($Indice_rec['INDTIPODOC'], $Indice_rec);
                        $NumeroInd = $dizionarioIdelib['PROGRESSIVO'];
                        $Progressivo = is_numeric($dizionarioIdelib['PROGRESSIVO']) ? intval($dizionarioIdelib['PROGRESSIVO']) : $dizionarioIdelib['PROGRESSIVO'];
                        $AnnoInd = substr($Indice_rec['IDATDE'], 0, 4);
                        //$Anaorg_rec = $this->segLib->GetAnaorg($Indice_rec['IORGAN'], 'codice');
                        if ($orgconn_rec['PROCODTIPODOC'] == $anaent_59['ENTDE1'] && $orgconn_rec['PROCODTIPODOC'] != '') {
                            $TabDag_rec = $this->proLibTabDag->GetTabdag('ANAPRO', 'chiave', $orgconn_rec['ROWID_ANAPRO'], 'IDENTIFICATIVOPROCEDIMENTO', '', false, '', praLibFascicoloArch::FONTE_DATI_DOCUMENTO);
                            $IdProcedimento = $TabDag_rec['TDAGVAL'];
                            $TabDag_rec = $this->proLibTabDag->GetTabdag('ANAPRO', 'chiave', $orgconn_rec['ROWID_ANAPRO'], 'NUMEROPASSO', '', false, '', praLibFascicoloArch::FONTE_DATI_DOCUMENTO);
                            $NumeroPasso = $TabDag_rec['TDAGVAL'];
                            $tooltip = " PRATICA N. " . substr($IdProcedimento, 0, 10);
                            if ($NumeroPasso) {
                                $tooltip.="<br> PASSO N. " . $NumeroPasso;
                            }
                        } else {
                            $tooltip = $Indice_rec['INDTIPODOC'] . ":<br>" . $NumeroInd . " - " . $AnnoInd . "<br> Organo:<br>" . $Indice_rec['IORGAN'] . ' - ' . $Indice_rec['DESORG'];
                            if ($orgconn_rec['PRORISERVA']) {
                                $tooltip .= "<br><p style=\"color:lightgrey;\">RISERVATO</p>";
                            } else {
                                $tooltip .= "<br><p style=\"color:lightblue;\">" . $anaogg_conn_rec['OGGOGG'] . "</p>";
                            }
                        }
                        $documenti_tree[$inc]['PROTOCOLLO'] = 1;

                        if ($orgconn_rec['PROFASKEY'] == $orgconn_rec['ORGKEY']) {
                            $tooltip = "<p style=\"color:lightblue;\"><u>Fascicolo Principale</u></p>" . $tooltip;
                            $iconPrinc = "<span  class=\"ita-icon ita-icon-star-yellow-16x16\" style = \"margin-left:-22px; display:inline-block;\"></span>";
                        } else {
                            $tooltip = "<p style=\"color:lightblue;\"><u>Fascicolo Secondario</u></p>" . $tooltip;
                        }
                        $AnnoProt = substr($orgconn_rec['PRONUM'], 0, 4);
                        /*
                         * Controlo Indice Archivistico
                         */
                        if ($orgconn_rec['PROCODTIPODOC'] == $anaent_59['ENTDE1'] && $orgconn_rec['PROCODTIPODOC'] != '') {
                            /* Lettura Progressivo da metadati */
                            $TabDag_rec = $this->proLibTabDag->GetTabdag('ANAPRO', 'chiave', $orgconn_rec['ROWID_ANAPRO'], 'CHIAVE_PASSO', '', false, '', praLibFascicoloArch::FONTE_DATI_DOCUMENTO);
                            $AnnoProt = substr($TabDag_rec['TDAGVAL'], 0, 4);
                            $Progressivo = substr($TabDag_rec['TDAGVAL'], 4, 6) . ' ' . $NumeroPasso;
                        }

                        $documenti_tree[$inc]['NUMPROT'] = '<div class="ita-html"><span style="display:inline-block;" class="ita-tooltip" title="' . htmlspecialchars($tooltip) . '">' . $Progressivo . '</span></div>';
                        $documenti_tree[$inc]['ANNOPROT'] = '<div class="ita-html"><span class="ita-tooltip" title="' . htmlspecialchars($tooltip) . '">' . substr($orgconn_rec['PRONUM'], 0, 4) . '</span></div>';
                        $documenti_tree[$inc]['DATAPROT'] = '<div class="ita-html"><span class="ita-tooltip" title="' . htmlspecialchars($tooltip) . '">' . date('d/m/Y', strtotime($orgconn_rec['PRODAR'])) . '</span></div>';
                        $documenti_tree[$inc]['PRODAR'] = $orgconn_rec['PRODAR'];
                        $documenti_tree[$inc]['PRONUM'] = $orgconn_rec['PRONUM'];
                        $documenti_tree[$inc]['PROPAR'] = $orgconn_rec['PROPAR'];
                        $documenti_tree[$inc]['ROWID_ORGCONN'] = $currConnRowid = $orgconn_rec['ROWID_ORGCONN'];
                        $firmatario_rec = $this->proLib->GetAnades($orgconn_rec['PRONUM'], 'codice', false, $orgconn_rec['PROPAR'], 'M');
                        if ($firmatario_rec) {
                            $provenienza = $firmatario_rec['DESNOM'];
                            $documenti_tree[$inc]['PROVENIENZA'] = '<div class="ita-html"><span class="ita-tooltip" style="display:inline-block; width:115px; overflow:hidden;" title="' . $provenienza . '" >' . $provenienza . '</span></div>';
                        }
                        $icon = "ita-icon-register-document-green-32x32";
                        break;

                    default:
                        break;
                }
                if ($tooltip && $orgconn_rec['CONNDATAINS']) {
                    $Utente = $orgconn_rec['CONNUTEINS'];
                    $DataConn = date('d/m/Y', strtotime($orgconn_rec['CONNDATAINS']));
                    $tooltip = 'Inserito il: ' . $DataConn . ' da ' . $Utente . '<br>' . $tooltip;
//                    $tooltip = 'Inserito il: ' . $DataConn . '<br>' . $tooltip;
                }
                $propas_rec = $this->proLibPratica->GetPropas($orgconn_rec['PRONUM'], 'paspro', false, $orgconn_rec['PROPAR']);

                if ($proges_rec['GESCLOSE'] === $propas_rec['PROPAK'] && $proges_rec['GESCLOSE'] != '') {
                    $documenti_tree[$inc]['ORGNODEICO'] = '<div class="ita-html"><span title="PASSO CHIUSURA" class="ita-tooltip ita-icon ita-icon-divieto-16x16"></span></div>';
                } else {
                    $documenti_tree[$inc]['ORGNODEICO'] = '<div style="display:inline-block; " class="ita-html"><span style="display:inline-block;vertical-align:left;height:16px;background-size:50%;margin:2px;" title="' . htmlspecialchars($tooltip) . '" class="ita-tooltip ita-icon ' . $icon . '"></span>' . $iconPrinc . '</div>';
                }
                $documenti_tree[$inc]['NOTE'] = $anaogg_conn_rec['OGGOGG'];
                if ($orgconn_rec['PRORISERVA']) {
                    $riservato = "<p style=\"background-color:lightgrey;\">RISERVATO</p>";
                    $documenti_tree[$inc]['NOTE'] = $riservato;
                    $documenti_tree[$inc]['PRORISERVA'] = '1';
                }

                $noteManager = proNoteManager::getInstance($this->proLib, proNoteManager::NOTE_CLASS_PROTOCOLLO, array("PRONUM" => $orgconn_rec['PRONUM'], "PROPAR" => $orgconn_rec['PROPAR']));
                if ($noteManager->getNote()) {
                    if ($proges_rec['GESDCH']) {
                        $documenti_tree[$inc]['NOTEICO'] = '<div class="ita-html"><span style="width:16px;height:16px;background-size:100%;margin:2px;" title="Visualizza Note" class="ita-tooltip ita-icon ita-icon-comment-128x128"></span></div>';
                    } else {
                        $documenti_tree[$inc]['NOTEICO'] = '<div class="ita-html"><span style="width:16px;height:16px;background-size:100%;margin:2px;" title="Note " class="ita-tooltip ita-icon ita-icon-comment-edit-128x128"></span></div>';
                    }
                } else if (!$proges_rec['GESDCH']) {
                    $documenti_tree[$inc]['NOTEICO'] = '<div class="ita-html"><span style="width:16px;height:16px;background-size:100%;margin:2px;" title="Note " class="ita-tooltip ita-icon ita-icon-comment-new-128x128"></span></div>';
                }


                if (!$gestione) {
                    $permessiSottoFasc = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum', $orgconn_rec['PRONUM'], $orgconn_rec['PROPAR']);
                    if ($permessiSottoFasc[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI] ||
                            $permessiSottoFasc[proLibFascicolo::PERMFASC_GESTIONE_PROTOCOLLI] ||
                            $permessiSottoFasc[proLibFascicolo::PERMFASC_GESTIONE_SOTTOFASCICOLI]) {
                        $gestione = true;
                        $gestSingola = true;
                    } else {
                        $documenti_tree[$inc]['ADDAZIONE'] = '';
                        $documenti_tree[$inc]['EVIDENZIA'] = '';
                        $documenti_tree[$inc]['LOCK'] = '';
                        $documenti_tree[$inc]['EDIT'] = '';
                        switch ($orgconn_rec['PROPAR']) {
                            case "A":
                            case "P":
                            case "C":
                            case "I":
                                if ($noteManager->getNote()) {
                                    $documenti_tree[$inc]['NOTEICO'] = '<div class="ita-html"><span style="width:16px;height:16px;background-size:100%;margin:2px;" title="Visualizza Note" class="ita-tooltip ita-icon ita-icon-comment-128x128"></span></div>';
                                } else {
                                    $documenti_tree[$inc]['NOTEICO'] = '';
                                }
                                break;
                            default :
                                $documenti_tree[$inc]['NOTEICO'] = '';
                                break;
                        }
                    }
                }
                $documenti_tree[$inc]['GESTIONE'] = $gestione;
                $documenti_anadoc_check = $this->getAllegatiAnadocTab($orgconn_rec['PRONUM'], $orgconn_rec['PROPAR'], $keyParent, $where, $Indice_rec);
                $chckOrgcon_parent_tab = $this->proLibFascicolo->GetOrgconParent($orgconn_rec['PRONUM'], $orgconn_rec['PROPAR']);
                if ($fl_unset && $documenti_anadoc_check) {
                    if (!$ParmTreeProto) {
                        $keyparent = "PRO-" . $nodePronum . $nodePropar;
                        unset($documenti_tree[$inc]);
                        $level = $level - 1;
                    } else {
                        $fl_unset = false;
                        $documenti_tree[$inc]['expanded'] = 'false'; // Per i protocolli non espansi
                    }
                } else {
                    $fl_unset = false;
//                    if ((!$documenti_anadoc_check && $orgconn_rec['PROPAR'] != 'N') || (!$chckOrgcon_parent_tab )) {/* Aggiunta il 10.03.16 Alle. Si potrebbe usare direttamente checkorgconn */
                    if (!$chckOrgcon_parent_tab && !$documenti_anadoc_check) {/* Sostituita il 10.03.16 Alle. */
                        $documenti_tree[$inc]['isLeaf'] = 'true';
                    }
                }
                $documenti_anadoc = array();
                if ($orgconn_rec['PROPAR'] == 'N') {
                    $documenti_anadoc = $this->caricaAllegatiAnadoc($orgconn_rec['PRONUM'], $orgconn_rec['PROPAR'], $keyparent, $where, $level, $riservato, $gestione, $currConnRowid);
                }
//                $documenti_tree_tmp = array_merge($documenti_tree, $documenti_anadoc);
//                $documenti_tree = $this->CaricaAllegati_tree($documenti_tree_tmp, $orgconn_rec['PRONUM'], $orgconn_rec['PROPAR'], $where, $level + 1, $gestione);
                /* Modifica per Inserire i Documenti Allegati per Ultimi nella tree  05.05.2016 */
                /*
                 * Chiamo la funzione per caricare sotto elementi, solo se so che ci sono 
                 */
                if ($chckOrgcon_parent_tab) {
                    $documenti_tree = $this->CaricaAllegati_tree($documenti_tree, $orgconn_rec['PRONUM'], $orgconn_rec['PROPAR'], $where, $level + 1, $gestione);
                }
                $documenti_tree = array_merge($documenti_tree, $documenti_anadoc);
                if ($fl_unset) {
                    $level = $level + 1;
                }
                if ($gestSingola) {
                    $gestione = false;
                }
            }
        } else {
            // Qui verifico visibilita per i figli.
            // Qui cerco i figli?
        }
        return $documenti_tree;
    }

    private function getDatiIndice($pronum, $propar) {
        $sql = "SELECT INDICE.ROWID,IDELIB,INDPRO,INDPAR,PROGRESSIVO,IDATDE,IORGAN,ISERVI,INDTIPODOC,ANAORG.DESORG"
                . " FROM INDICE "
                . " LEFT OUTER JOIN ANAORG ON INDICE.IORGAN = ANAORG.ORGANO "
                . " WHERE INDPRO = $pronum AND INDPAR = '$propar'";
        return ItaDB::DBSQLSelect($this->segLib->getSEGRDB(), $sql, false);
    }

    private function getAllegatiAnadocTab($pronum, $propar, $keyparent, $where, $Indice_rec = array()) {
        $sql = "SELECT"
                . " ANAPRO.PRONUM  AS PRONUM,"
                . " ANAPRO.PROPAR  AS PROPAR,"
                . " ANAPRO.PRODAR  AS PRODAR,"
                . " ANAPRO.PROCODTIPODOC  AS PROCODTIPODOC,"
                . " ANADOC.DOCNAME AS DOCNAME,"
                . " ANADOC.DOCKEY,"
                . " ANADOC.DOCFIL,"
                . " ANADOC.DOCCLAS,"
                . " ANADOC.DOCNOT,"
                . " ANADOC.DOCEVI,"
                . " ANADOC.DOCPATHASSOLUTA,"
                . " ANADOC.DOCLOCK,"
                . " ANADOC.DOCFDT,"
                . " ANADOC.DOCTIPO,"
                . " ANADOC.DOCUTELOG,"
                . " ANADOC.DOCUUID,"
                . " ANADOC.ROWID AS ROWIDANADOC,"
                . " ANADOC.DOCRELCLASSE AS DOCRELCLASSE,"
                . " ANADOC.DOCRELCHIAVE AS DOCRELCHIAVE"
                . " FROM"
                . " ANAPRO"
                . " LEFT OUTER JOIN ANADOC ANADOC ON ANAPRO.PRONUM=ANADOC.DOCNUM AND ANAPRO.PROPAR=ANADOC.DOCPAR"
                . " LEFT OUTER JOIN ANAOGG ANAOGG ON ANAPRO.PRONUM=ANAOGG.OGGNUM AND ANAPRO.PROPAR=ANAOGG.OGGPAR"
                . " WHERE "
                . " ANADOC.DOCSERVIZIO = 0 AND "
                . "ANADOC.ROWID IS NOT NULL AND ANAPRO.PRONUM = '{$pronum}' AND ANAPRO.PROPAR = '{$propar}'";
        // Se entrambi, basta 1 delle condizioni valide.
        if ($where['PROTOCOLLI'] && $where['DOCUMENTI']) {
            $sql .= " AND ( ( 1=1 {$where['DOCUMENTI']} ) OR ( 1=1 {$where['PROTOCOLLI']} )  )";
        } else {
            if ($where['DOCUMENTI']) {
                $sql .= " AND (1=1 {$where['DOCUMENTI']})";
            }
            if ($where['PROTOCOLLI']) {
                $sql .= " AND (1=1 {$where['PROTOCOLLI']} AND (ANAPRO.PROPAR<>'F' AND ANAPRO.PROPAR<>'N' AND ANAPRO.PROPAR<>'T'))";
            }
        }
        $sql .= " ORDER BY ANADOC.ROWID";
        $documenti_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        // Controlli per documentale -> 'I'
        if ($propar == 'I') {
            //$Indice_rec = $this->segLib->GetIndice($pronum, 'anapro', false, $propar);
            foreach ($documenti_tab as $key => $documenti_rec) {
                // Accettati Allegati, Originali e Copie.
                // Allegati Esito e Originale composizione:
                $flcontinua = false;
                switch ($documenti_rec['DOCTIPO']) {
                    case 'ALLEGATO':
                    case 'ORIGINALE':
                    case 'COPIA':
                    case 'ALLEGATOESITO':
                    case 'ORIGINALE_COMP':
                    case 'ALLEGATOESITOPROPOSTA':
                        break;
                    default:
                        unset($documenti_tab[$key]);
                        $flcontinua = true;
                        break;
                }
                if ($flcontinua) {
                    continue;
                }
                // Controllo estensione htm:
                $ext = pathinfo($documenti_rec['DOCFIL'], PATHINFO_EXTENSION);
                if (strtolower($ext) == "htm") {
                    unset($documenti_tab[$key]);
                    continue;
                }
                // Se Originale o Copia:   Controllo originale pdf o firm
                $allegatoEsito = array();
                switch ($documenti_rec['DOCTIPO']) {
                    case 'ORIGINALE':
                        $allegatoEsito = $this->segLibAllegati->getOriginale($Indice_rec['IDELIB']);
                        break;
                    case 'COPIA':
                        $allegatoEsito = $this->segLibAllegati->getCopia($Indice_rec['IDELIB']);
                        break;
                    case 'ALLEGATOESITO':
                        $allegatoEsito = $this->segLibAllegati->getAllegatoEsito($documenti_rec['ROWIDANADOC']);
                        break;
                    case 'ORIGINALE_COMP':
                        $allegatoEsito = $this->segLibAllegati->getOriginaleComp($Indice_rec['IDELIB']);
                        break;
                    case 'ALLEGATOESITOPROPOSTA':
                        $allegatoEsito = $this->segLibAllegati->getAllegatiEsitoProposta($Indice_rec['IDELIB']);
                        break;
                    default:
                        break;
                }
                if ($allegatoEsito) {
                    if ($allegatoEsito['ROWID_FIR']) {
                        $documenti_tab[$key]['ROWIDANADOC'] = $allegatoEsito['ROWID_FIR'];
                        $documenti_tab[$key]['DOCFIL'] = $allegatoEsito['DOCFILE_FIR'];
                        $documenti_tab[$key]['DOCNAME'] = $allegatoEsito['DOCNAME_FIR'];
                    } else if ($allegatoEsito['ROWID_PDF']) {
                        $documenti_tab[$key]['ROWIDANADOC'] = $allegatoEsito['ROWID_PDF'];
                        $documenti_tab[$key]['DOCFIL'] = $allegatoEsito['DOCFILE_PDF'];
                        $documenti_tab[$key]['DOCNAME'] = $allegatoEsito['DOCNAME_PDF'];
                    }
                }
            }
        }
        return $documenti_tab;
    }

    private function caricaAllegatiAnadoc($pronum, $propar, $keyparent, $where, $level, $riservato = '', $gestione = true, $orgconn_rowid = '') {
        $anaent_48 = $this->proLib->GetAnaent('48');
        $anaent_59 = $this->proLib->GetAnaent('59');
        $ParmTreeProto = $anaent_48['ENTDE1'];

        $Indice_rec = array();
        if ($propar == 'I') {
            $Indice_rec = $this->getDatiIndice($pronum, $propar);
        }

        $documenti_tree = array();
        $documenti_tab = $this->getAllegatiAnadocTab($pronum, $propar, $keyparent, $where, $Indice_rec);
        $level = $level + 1;



        if ($documenti_tab) {
            foreach ($documenti_tab as $documenti_rec) {
                $icon = utiIcons::getExtensionIconClass($documenti_rec['DOCNAME'], 32);
                $inc = "DOC-" . $documenti_rec['DOCKEY'] . "-" . $documenti_rec['ROWIDANADOC'];
                $documenti_tree[$inc]['level'] = $level;
                $documenti_tree[$inc]['parent'] = $keyparent; //"PRO-" . $pronum . $propar;
                $documenti_tree[$inc]['isLeaf'] = 'true';
                $documenti_tree[$inc]['expanded'] = 'true';
                $documenti_tree[$inc]['loaded'] = 'true';
                $documenti_tree[$inc]['ORGNODEKEY'] = $inc = "DOC-" . $documenti_rec['DOCKEY'] . "-" . $documenti_rec['ROWIDANADOC'];
                if ($orgconn_rowid) {
                    $documenti_tree[$inc]['ROWID_ORGCONN'] = $orgconn_rowid;
                }
                $documenti_tree[$inc]['ORGNODEICO'] = '<span style="height:16px;background-size:50%;margin:2px; display:inline-block;" class="' . $icon . '">Documento</span>';
                $tooltip = 'Inserito il ' . date('d/m/Y', strtotime($documenti_rec['DOCFDT'])) . ' da ' . $documenti_rec['DOCUTELOG'];
                $documenti_tree[$inc]['ORGNODEICO'] = '<div class="ita-html"><span class="ita-tooltip" title="' . htmlspecialchars($tooltip) . '">' . $documenti_tree[$inc]['ORGNODEICO'] . '';
                $edit = "";
                $ext = pathinfo($documenti_rec['DOCFIL'], PATHINFO_EXTENSION);
                if (strtolower($ext) == "p7m") {
                    $edit = "<span class=\"ita-icon ita-icon-shield-blue-16x16\">Verifica Firma</span>";
                } else {
                    $edit = " ";
                }
                if ($documenti_rec['DOCEVI']) {
                    if ($documenti_rec['DOCNAME']) {
                        $documenti_tree[$inc]['NAME'] = "<p style = 'color:red;font-weight:bold;font-size:1.2em;'>" . $documenti_rec['DOCNAME'] . "</p>";
                        $documenti_tree[$inc]['FILEORIG'] = $documenti_rec['DOCNAME'];
                    } else {
                        $documenti_rec['NAME'] = "<p style = 'color:red;font-weight:bold;font-size:1.2em;'>" . $documenti_rec['DOCFIL'] . "</p>";
                        $documenti_rec['FILEORIG'] = $documenti_rec['DOCFIL'];
                    }
                } else {
                    if ($documenti_rec['DOCNAME']) {
                        $documenti_tree[$inc]['NAME'] = $documenti_rec['DOCNAME'];
                        $documenti_tree[$inc]['FILEORIG'] = $documenti_rec['DOCNAME'];
                    } else {
                        $documenti_tree[$inc]['NAME'] = $documenti_rec['DOCFIL'];
                        $documenti_tree[$inc]['FILEORIG'] = $documenti_rec['DOCFIL'];
                    }
                }
                $documenti_tree[$inc]['NOTE'] = $documenti_rec['DOCNOT'];
                // Oggetto-> su doc principale. Sempre? 
//                $tipProt = substr($documenti_rec['PROPAR'], 0, 1);
//                if (!$ParmTreeProto && ($tipProt == 'A' || $tipProt == 'P' || $tipProt == 'C')) {
//                    if ($documenti_rec['DOCTIPO'] == '') {
//                        $Anaogg_rec = $this->proLib->GetAnaogg($pronum, $propar);
//                        $documenti_tree[$inc]['NOTE'] = $Anaogg_rec['OGGOGG'];
//                    }
//                }

                if ($riservato) {
                    $documenti_tree[$inc]['NAME'] = $riservato;
                    $documenti_tree[$inc]['NOTE'] = $riservato;
                    $documenti_tree[$inc]['PRORISERVA'] = '1';
                }

                $documenti_tree[$inc]['INFO'] = $documenti_rec['DOCCLAS'];

                $documenti_tree[$inc]['EDIT'] = $edit;
                $documenti_tree[$inc]['EVIDENZIA'] = "<span class=\"ita-icon ita-icon-evidenzia-16x16\">Evidenzia Allegato</span>";

                $allpath = '';
                if ($documenti_rec['DOCPATHASSOLUTA']) {
                    $allpath = $this->proLibAllegati->getPathAssoluta($documenti_rec['ROWID']);
                }

                switch ($documenti_rec['PROPAR']) {
                    case "A":
                    case "P":
                    case "C":
                        $anaogg_conn_rec = $this->proLib->GetAnaogg($documenti_rec['PRONUM'], $documenti_rec['PROPAR']);
                        $tooltip = "Protocollo:<br>" . substr($documenti_rec['PRONUM'], 4) . " / " . substr($documenti_rec['PRONUM'], 0, 4) . "  -  " . $documenti_rec['PROPAR'];
                        if ($riservato) {
                            $tooltip .= "<br><p style=\"color:lightgrey;\">RISERVATO</p>";
                        } else {
                            $tooltip .= "<br><p style=\"color:lightblue;\">" . $anaogg_conn_rec['OGGOGG'] . "</p>";
                        }
                        $documenti_tree[$inc]['PROTOCOLLO'] = 1;
                        $documenti_tree[$inc]['NUMPROT'] = '<div class="ita-html"><span class="ita-tooltip" title="' . htmlspecialchars($tooltip) . '">' . substr($documenti_rec['PRONUM'], 4) . '</span></div>';
                        $documenti_tree[$inc]['ANNOPROT'] = '<div class="ita-html"><span class="ita-tooltip" title="' . htmlspecialchars($tooltip) . '">' . substr($documenti_rec['PRONUM'], 0, 4) . '</span></div>';
                        $documenti_tree[$inc]['DATAPROT'] = '<div class="ita-html"><span class="ita-tooltip" title="' . htmlspecialchars($tooltip) . '">' . date('d/m/Y', strtotime($documenti_rec['PRODAR'])) . '</span></div>';
                        $documenti_tree[$inc]['PRODAR'] = $documenti_rec['PRODAR'];
                        $documenti_tree[$inc]['PRONUM'] = $documenti_rec['PRONUM'];
                        $documenti_tree[$inc]['PROPAR'] = $documenti_rec['PROPAR'];
                        $documenti_tree[$inc]['LOCK'] = "";
                        $noteManager = proNoteManager::getInstance($this->proLib, proNoteManager::NOTE_CLASS_PROTOCOLLO, array("PRONUM" => $documenti_rec['PRONUM'], "PROPAR" => $documenti_rec['PROPAR']));
                        if ($noteManager->getNote()) {
                            $documenti_tree[$inc]['NOTEICO'] = '<div class="ita-html"><span style="width:16px;height:16px;background-size:100%;margin:2px;" title="Visualizza Note" class="ita-tooltip ita-icon ita-icon-comment-128x128"></span></div>';
                        }
                        break;

                    case "I":
                        //$Indice_rec = $this->segLib->GetIndice($documenti_rec['PRONUM'], 'anapro', false, $documenti_rec['PROPAR']);
                        $dizionarioIdelib = $this->segLib->getDizionarioFormIdelib($Indice_rec['INDTIPODOC'], $Indice_rec);
                        $NumeroInd = $dizionarioIdelib['PROGRESSIVO'];
                        $Progressivo = is_numeric($dizionarioIdelib['PROGRESSIVO']) ? intval($dizionarioIdelib['PROGRESSIVO']) : $dizionarioIdelib['PROGRESSIVO'];
                        $AnnoInd = substr($Indice_rec['IDATDE'], 0, 4);
                        $anaogg_conn_rec = $this->proLib->GetAnaogg($documenti_rec['PRONUM'], $documenti_rec['PROPAR']);
                        //$Anaorg_rec = $this->segLib->GetAnaorg($Indice_rec['IORGAN'], 'codice');
                        if ($documenti_rec['PROCODTIPODOC'] == $anaent_59['ENTDE1'] && $documenti_rec['PROCODTIPODOC'] != '') {
                            $tooltip = '';
                        } else {
                            $tooltip = $Indice_rec['INDTIPODOC'] . ":<br>" . $NumeroInd . " - " . $AnnoInd . "<br> Organo:<br>" . $Indice_rec['IORGAN'] . ' - ' . $Indice_rec['DESORG'];
                            if ($riservato) {
                                $tooltip .= "<br><p style=\"color:lightgrey;\">RISERVATO</p>";
                            } else {
                                $tooltip .= "<br><p style=\"color:lightblue;\">" . $anaogg_conn_rec['OGGOGG'] . "</p>";
                            }
                        }
                        $documenti_tree[$inc]['PROTOCOLLO'] = 1;
                        $documenti_tree[$inc]['NUMPROT'] = '<div class="ita-html"><span class="ita-tooltip" title="' . htmlspecialchars($tooltip) . '">' . $Progressivo . '</span></div>';
                        $documenti_tree[$inc]['ANNOPROT'] = '<div class="ita-html"><span class="ita-tooltip" title="' . htmlspecialchars($tooltip) . '">' . substr($documenti_rec['PRONUM'], 0, 4) . '</span></div>';
                        $documenti_tree[$inc]['DATAPROT'] = '<div class="ita-html"><span class="ita-tooltip" title="' . htmlspecialchars($tooltip) . '">' . date('d/m/Y', strtotime($documenti_rec['PRODAR'])) . '</span></div>';
                        $documenti_tree[$inc]['PRODAR'] = $documenti_rec['PRODAR'];
                        $documenti_tree[$inc]['PRONUM'] = $documenti_rec['PRONUM'];
                        $documenti_tree[$inc]['PROPAR'] = $documenti_rec['PROPAR'];
                        $documenti_tree[$inc]['LOCK'] = "";
                        $noteManager = proNoteManager::getInstance($this->proLib, proNoteManager::NOTE_CLASS_PROTOCOLLO, array("PRONUM" => $documenti_rec['PRONUM'], "PROPAR" => $documenti_rec['PROPAR']));
                        if ($noteManager->getNote()) {
                            $documenti_tree[$inc]['NOTEICO'] = '<div class="ita-html"><span style="width:16px;height:16px;background-size:100%;margin:2px;" title="Visualizza Note" class="ita-tooltip ita-icon ita-icon-comment-128x128"></span></div>';
                        }
                        break;

                    default:
                        if ($documenti_rec['DOCLOCK'] == 1) {
                            $documenti_tree[$inc]['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Sblocca Documento</span>";
                        } else {
                            $documenti_tree[$inc]['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-16x16\">Blocca Documento</span>";
                        }
                        break;
                }

                if (!$gestione) {
                    $documenti_tree[$inc]['ADDAZIONE'] = '';
                    $documenti_tree[$inc]['EVIDENZIA'] = '';
                    $documenti_tree[$inc]['LOCK'] = '';
                    $documenti_tree[$inc]['EDIT'] = '';
                }

                $documenti_tree[$inc]['GESTIONE'] = $gestione;
                $documenti_tree[$inc]['FILENAME'] = $documenti_rec['DOCFIL'];
                $documenti_tree[$inc]['FILEINFO'] = $documenti_rec['DOCCLAS'];
                $documenti_tree[$inc]['FILEPATH'] = $allpath . "/" . $documenti_rec['DOCFIL'];
                $documenti_tree[$inc]['DOCUUID'] = $documenti_rec['DOCUUID'];
                $documenti_tree[$inc]['DOCEVI'] = $documenti_rec['DOCEVI'];
                $documenti_tree[$inc]['DOCLOCK'] = $documenti_rec['DOCLOCK'];
                $documenti_tree[$inc]['ROWIDANADOC'] = $documenti_rec['ROWIDANADOC'];
                $documenti_tree[$inc]['COPIADOC'] = '';
                $documenti_tree[$inc]['DOCRELCLASSE'] = $documenti_rec['DOCRELCLASSE'];
            }
        }
        return $documenti_tree;
    }

    public function aggiorna($dati) {
        $proges_rec = $dati["PROGES_REC"];
        $proges_rec['GESPRO'] = $_POST[$this->nameForm . '_PROGES']['GESPRO'];
        $dati_anaorg = $dati['DATI_ANAORG'];

        $procedimento = $proges_rec['GESNUM'];
        if ($_POST[$this->nameForm . '_Numero_prot'] != 0 && $_POST[$this->nameForm . '_Anno_prot'] == 0) {
            Out::msgInfo("ATTENZIONE", "Inserire l'anno per il protocollo n. " . $_POST[$this->nameForm . '_Numero_prot']);
            return false;
        }
        // Controllo del responsabile e faccio unset?

        $anaprofasciclo_rec = $this->proLibFascicolo->getAnaproFascicolo($this->geskey);
        //proIntegrazioni::GetMetedatiProt($this->currGesnum);
        //$proges_rec['GESNPR'] = $_POST[$this->nameForm . '_Anno_prot'] . $_POST[$this->nameForm . '_Numero_prot'];
        $update_Info = 'Oggetto: Aggiornamento pratica: ' . $procedimento;
        if (!$this->updateRecord($this->PROT_DB, 'PROGES', $proges_rec, $update_Info)) {
            Out::msgStop("Errore in Aggionamento", "Aggiornamento procedimento Fallito.");
            return false;
        }
        /*
         *  Qui registarzione anapro_save [usare quello di proProtocolla ?]
         */
        $motivo = 'Aggiornamento Fascicolo';
        App::requireModel('proProtocolla.class');
        $protObj = new proProtocolla();
        $protObj->registraSave($motivo, $anaprofasciclo_rec['ROWID'], 'rowid');

// Aggiorno i dati.
        $anaprofasciclo_rec['PROUTE'] = App::$utente->getKey('nomeUtente');
        $anaprofasciclo_rec['PROUOF'] = $proges_rec['GESPROUFF'];
        $anaprofasciclo_rec['PRORDA'] = date('Ymd');
        $anaprofasciclo_rec['PROROR'] = date('H:i:s');
        // Qui prendo il responsabile? e aggiorno PRONOM?
        // $anapro_new['PRONOM'] = $anamed_rec['MEDNOM'];
        /*
         * Aggiorno anapro
         */
        $update_Info = 'Oggetto: Aggiornamento Anapro Pratica: ' . $anaprofasciclo_rec['PRONUM'] . ' ' . $anaprofasciclo_rec['PROPAR'];
        if (!$this->updateRecord($this->PROT_DB, 'ANAPRO', $anaprofasciclo_rec, $update_Info)) {
            Out::msgStop("Errore in Aggionamento", "Aggiornamento procedimento ANAPRO Fallito.");
            return false;
        }
        /* Aggiornamento di ANAORG.. Per ora solo NATFAS e OGGETTO */
        $Anaorg_rec = $this->proLib->GetAnaorg($this->geskey, 'orgkey');
        $AnaorgRec['ROWID'] = $Anaorg_rec['ROWID'];
        $AnaorgRec['NATFAS'] = $dati_anaorg['NATFAS'];
        $AnaorgRec['ORGDES'] = $proges_rec['GESOGG']; // Aggiorno l'oggetto. Se varia su PROGES deve cambiare anche su ANAORG.
        $AnaorgRec['ORGKEYPRE'] = $dati_anaorg['ORGKEYPRE']; // Chiave fascicolo collegato.

        $update_Info = 'Oggetto: Aggiornamento ANAORG: ' . $Anaorg_rec['ORGKEY'];
        if (!$this->updateRecord($this->PROT_DB, 'ANAORG', $AnaorgRec, $update_Info)) {
            Out::msgStop("Errore in Aggionamento", "Aggiornamento procedimento ANAORG Fallito.");
            return false;
        }


        /*
         * Aggiorno l'oggetto del fascicolo:
         */
        App::requireModel('proProtocolla.class');
        $protObj = new proProtocolla();
        /* @var $protObj proProtocolla */
        $risultato = $protObj->saveOggetto($anaprofasciclo_rec['PRONUM'], $anaprofasciclo_rec['PROPAR'], $proges_rec['GESOGG']);
        if (!$risultato) {
            Out::msgStop("Errore in Aggionamento", "Errore in salvataggio descrizione fascicolo");
            return false;
        }
        $this->proLibPratica->sincronizzaStato($proges_rec['GESNUM'], $dati['forzaChiusura']);
        $this->SincronizzaFascicolo($proges_rec);
        return $proges_rec['ROWID'];
    }

    /**
     *
     * @param array $dati
     * @return boolean
     */
    public function aggiungi($dati) {
        App::log('$dati');
        App::log($dati);
        $descrizione = $dati['PROGES_REC']['GESOGG'];
        $codiceProcedimento = $dati['PROGES_REC']['GESPRO'];
        $rowidProges = $this->proLibFascicolo->creaFascicolo(
                $this, array(
            "VERSIONE_T" => $dati['VERSIONE_T'],
            "TITOLARIO" => $dati['TITOLARIO'],
            'UFF' => $dati['PROGES_REC']['GESUFFRES'],
            'RES' => $dati['PROGES_REC']['GESRES'],
            'GESPROUFF' => $dati['PROGES_REC']['GESPROUFF'],
            'SERIE' => $dati['SERIE'],
            'DATI_ANAORG' => $dati['DATI_ANAORG']
                ), $descrizione, $codiceProcedimento
        );
        if (!$rowidProges) {
            Out::msgStop("Aggiunta fascicolo", $this->proLibFascicolo->getErrMessage());
            return false;
        }
        return $rowidProges;
    }

    private function caricaParametriCaps($proges_rec) {
        if ($proges_rec['GESNUM'] == '') {
            $arrCaps = array();
            $arrCaps[] = array(
                'capability' => '0x1027',
                'datatype' => '5',
                'containertype' => '0',
                'datavalue' => '0'
            ); // CAP_PRINTERENABLED
            $arrCaps[] = array(
                'capability' => '0x102a',
                'valuetype' => '',
                'datatype' => '5',
                'containertype' => '1',
                'datavalue' => ''
            ); // CAP_PRINTERSTRING
        } else {
            $testo = "Fascicolo: " . (int) substr($proges_rec['GESNUM'], 4) . "/" . substr($proges_rec['GESNUM'], 0, 4) . "                del " . date('d/m/Y', strtotime($proges_rec['GESDRE']));
            $arrCaps = array();

// CAP_PRINTER        
            $arrCaps[] = array(
                'capability' => '0x1026',
                'valuetype' => '',
                'datatype' => '5',
                'containertype' => '0',
                'datavalue' => '6'
            );

// CAP_PRINTERENABLED            
            $arrCaps[] = array(
                'capability' => '0x1027',
                'valuetype' => '',
                'datatype' => '5',
                'containertype' => '0',
                'datavalue' => '0'
            );

// CAP_MICROREI_PRINTERSTRING
            $arrCaps[] = array(
//                'capability' => '0x102a',
                'capability' => '0x8002',
                'valuetype' => '0x000c',
                'datatype' => '5',
                'containertype' => '1',
                'datavalue' => "disabilitata"//$testo
            );


// CAP_PRINTER           
            $arrCaps[] = array(
                'capability' => '0x1026',
                'valuetype' => '',
                'datatype' => '5',
                'containertype' => '0',
                'datavalue' => '4'
            );

// CAP_PRINTERENABLED            
            $arrCaps[] = array(
                'capability' => '0x1027',
                'valuetype' => '',
                'datatype' => '5',
                'containertype' => '0',
                'datavalue' => '1'
            );

// CAP_MICROREI_PRINTERSTRING
            $arrCaps[] = array(
                'capability' => '0x8002',
                'valuetype' => '0x000c',
                'datatype' => '5',
                'containertype' => '1',
                'datavalue' => $testo
            );

// CAP_MICROREI_PRINTERPOSITION
            $arrCaps[] = array(
                'capability' => '0x800a',
                'valuetype' => '0x0004',
                'datatype' => '5',
                'containertype' => '0',
                'datavalue' => '3'
            );
// CAP_MICROREI_PRINTERDENSITY
            $arrCaps[] = array(
                'capability' => '0X8009',
                'valuetype' => '0x0004',
                'datatype' => '5',
                'containertype' => '0',
                'datavalue' => '13'
            );
        }
        return $arrCaps;
    }

    private function caricaNote($filter = '') {
        $datiGrigliaNote = array();

        $this->noteManager->setFilter($filter);
        $this->noteManager->caricaNote();

        foreach ($this->noteManager->getNote() as $key => $nota) {
            $data = date("d/m/Y", strtotime($nota['DATAINS']));
            $anapro_rec = $this->proLib->GetAnapro($nota['PRONUM'], 'codice', $nota['PROPAR']);
            list($skip, $sottofascicolo) = explode($anapro_rec['PROFASKEY'] . "-", $anapro_rec['PROSUBKEY']);
//            switch ($nota['CLASSE']) {
//                case proNoteManager::NOTE_CLASS_PROTOCOLLO:
            switch ($nota['PROPAR']) {
                case "A":
                case "P":
                case "C":
                    $datiGrigliaNote[$key]['CLASSE'] = "<span style=\"display:inline-block;margin-right:6px;\" class=\"ita-icon ita-icon-register-document-24x24\"></span><span style=\"display:inline-block;\" >" . substr($nota['PRONUM'], 5, 6) . "/" . substr($nota['PRONUM'], 0, 4) . ' - ' . $nota['PROPAR'] . "</span>";
                    break;
                case "F":
                    $datiGrigliaNote[$key]['CLASSE'] = "<span style=\"display:inline-block;margin-right:6px;\" class=\"ita-icon ita-icon-open-folder-24x24\"></span><span style=\"display:inline-block;\">" . $anapro_rec['PROFASKEY'] . "</span>";
                    break;
                case "N":
                    $datiGrigliaNote[$key]['CLASSE'] = "<span style=\"display:inline-block;margin-right:6px;\" class=\"ita-icon ita-icon-sub-folder-24x24\"></span><span style=\"display:inline-block;\" >" . $sottofascicolo . "</span>";
                    break;
                case "T":
                    $datiGrigliaNote[$key]['CLASSE'] = "AZIONE:" . substr($nota['PRONUM'], 5, 6) . "/" . substr($nota['PRONUM'], 0, 4) . ' - ' . $nota['PROPAR'];
                    break;
            }
//                    break;
//                case proNoteManager::NOTE_CLASS_ITER:
//                    $datiGrigliaNote[$key]['CLASSE'] = "ITER: " . $nota['PRONUM'] . ' - ' . $nota['PROPAR'];
//                    break;
//            }
            $datiGrigliaNote[$key]['NOTA'] = '<div style="margin: 3px 3px 3px 3px;"><div style="font-size: 7px;font-weight:bold; color: grey;">' . $data . " - " . $nota['ORAINS'] . " da " . $nota['UTELOG'] .
                    "</div><div class='ita-Wordwrap'><b>" . $nota['OGGETTO'] . '</b>';

            if ($nota['TESTO']) {
                $testo = $nota['TESTO'];
                if (strlen($testo) > 45) {
                    $testo = substr($testo, 0, 45);
                }
                $datiGrigliaNote[$key]['NOTE'] .= '<div title="' . $nota['TESTO'] . '" style="font-size: 9px;">' . $testo . '</div>';
            }
            $datiGrigliaNote[$key]['NOTE'] .= '</div></div>';
        }
        $this->caricaGriglia($this->gridNote, $datiGrigliaNote);
    }

    private function decodTitolarioRic($versione, $codice, $tipoArc, $tipoCodice = 'rowid') {
        $cat = $cla = $fas = $org = $des = '';
        switch ($tipoArc) {
            case 'ANACAT':
                $anacat_rec = $this->proLib->GetAnacat($versione, $codice, $tipoCodice);
                $cat = $anacat_rec['CATCOD'];
                $des = $anacat_rec['CATDES'];
                break;
            case 'ANACLA':
                $anacla_rec = $this->proLib->GetAnacla($versione, $codice, $tipoCodice);
                $cat = $anacla_rec['CLACAT'];
                $cla = $anacla_rec['CLACOD'];
                $des = $anacla_rec['CLADE1'] . $anacla_rec['CLADE2'];
                break;
            case 'ANAFAS':
                $anafas_rec = $this->proLib->GetAnafas($versione, $codice, $tipoCodice);
                $cat = substr($anafas_rec['FASCCA'], 0, 4);
                $cla = substr($anafas_rec['FASCCA'], 4);
                $fas = $anafas_rec['FASCOD'];
                $des = $anafas_rec['FASDES'];
                break;
        }

        Out::valore($this->nameForm . '_catcod', $cat);
        Out::valore($this->nameForm . '_clacod', $cla);
        Out::valore($this->nameForm . '_fascod', $fas);
        Out::valore($this->nameForm . '_titolarioDecod', $des);
    }

    private function TornaElenco() {
        Out::hide($this->divGes);
        Out::hide($this->divRic);
        Out::show($this->divRis);
        $this->Nascondi();
        Out::show($this->nameForm . '_AltraRicerca');
        TableView::enableEvents($this->gridGest);
        if ($_POST[$this->nameForm . "_PROGES"]['GESDCH']) {
            TableView::setCellValue($this->gridGest, $_POST[$this->nameForm . "_PROGES"]['ROWID'], "STATO", $this->GetImgStatoPratica($_POST[$this->nameForm . "_PROGES"]['GESNUM']));
        }
    }

    function GetImgStatoPratica($gesnum) {
        $prasta_rec = $this->proLibPratica->GetPrasta($gesnum);
        if ($prasta_rec['STAFLAG'] == "Annullata") {
            $img = '<span class="ita-icon ita-icon-delete-24x24">Pratica Annullata</span>';
        } elseif ($prasta_rec['STAFLAG'] == "Chiusa Positivamente") {
            $img = '<span class="ita-icon ita-icon-check-green-24x24">Pratica chiusa positivamente</span>';
        } elseif ($prasta_rec['STAFLAG'] == "Chiusa Negativamente") {
            $img = '<span class="ita-icon ita-icon-check-red-24x24">Pratica chiusa negativamente</span>';
        }
        return $img;
    }

    private function SincronizzaFascicolo($proges_rec) {
        $fascicolo_rec = $this->proLib->GetAnaorg($this->geskey, 'orgkey');
        if ($fascicolo_rec) {
            $fascicolo_rec['ORGDES'] = $proges_rec['GESOGG'];
            ItaDB::DBUpdate($this->PROT_DB, "ANAORG", "ROWID", $fascicolo_rec);
        }
        $anapro_fascicolo = $this->proLibFascicolo->getAnaproFascicolo($this->geskey);
        if ($anapro_fascicolo) {
            $anaogg_rec = $this->proLib->GetAnaogg($anapro_fascicolo['PRONUM'], $anapro_fascicolo['PROPAR']);
            $anaogg_rec['OGGOGG'] = $proges_rec['GESOGG'];
            $this->updateRecord($this->PROT_DB, 'ANAOGG', $anaogg_rec, '', 'ROWID', false);
        }

        $this->proLibFascicolo->registraResponsabile(array('RES' => $proges_rec['GESRES'], 'UFF' => $proges_rec['GESUFFRES']), $anapro_fascicolo['PRONUM'], $anapro_fascicolo['PROPAR']);
        $iter = proIter::getInstance($this->proLib, $anapro_fascicolo);
        $iter->sincIterProtocollo();

        return true;
    }

    private function apriFormTrasmissione($pronum, $propar, $tipoOpen) {
        $arcite_rec = $this->proLib->GetArcite($pronum, 'codice', false, $propar);
        $model = 'proGestIter';
        itaLib::openForm($model);
        $formObj = itaModel::getInstance($model);
        $formObj->setReturnModel($this->nameForm);
        $formObj->setReturnEvent('returnProGestIter');
        $formObj->setReturnId('');
        $_POST = array();
        $_POST['rowidIter'] = $arcite_rec['ROWID'];
        $_POST['tipoOpen'] = $tipoOpen;
        $formObj->setEvent('openform');
        $formObj->parseEvent();
        Out::setFocus('', 'proGestIter');
    }

    private function DisabilitaTitolario() {
        Out::hide($this->nameForm . '_Catcod_butt');
        Out::hide($this->nameForm . '_Clacod_butt');
        Out::hide($this->nameForm . '_Fascod_butt');
        Out::attributo($this->nameForm . '_Catcod', "readonly", '0');
        Out::attributo($this->nameForm . '_Clacod', "readonly", '0');
        Out::attributo($this->nameForm . '_Fascod', "readonly", '0');
        Out::delClass($this->nameForm . '_Catcod', "ita-decode ui-state-highlight");
        Out::delClass($this->nameForm . '_Clacod', "ita-decode ui-state-highlight");
        Out::delClass($this->nameForm . '_Fascod', "ita-decode ui-state-highlight");
    }

    private function AbilitaTitolario() {
        Out::show($this->nameForm . '_Catcod_butt');
        Out::show($this->nameForm . '_Clacod_butt');
        Out::show($this->nameForm . '_Fascod_butt');
        Out::attributo($this->nameForm . '_Catcod', "readonly", '1');
        Out::attributo($this->nameForm . '_Clacod', "readonly", '1');
        Out::attributo($this->nameForm . '_Fascod', "readonly", '1');
        Out::addClass($this->nameForm . '_Catcod', "ita-decode ui-state-highlight");
        Out::addClass($this->nameForm . '_Clacod', "ita-decode ui-state-highlight");
        Out::addClass($this->nameForm . '_Fascod', "ita-decode ui-state-highlight");
    }

    public function toggleDatiPrincipali($modo = 'disabilita') {
        switch ($modo) {
            case 'disabilita':
                $classMethod = "addClass";
                $attrCmd = '0';
                $hideShow = 'hide';
                $statoField = 'disableField';
                break;
            case 'abilita':
                $classMethod = "delClass";
                $attrCmd = '1';
                $hideShow = 'show';
                $statoField = 'enableField';
                break;
        }
        Out::$statoField($this->nameForm . '_PROGES[GESOGG]');
        Out::$statoField($this->nameForm . '_PROGES[GESRES]');
        Out::$statoField($this->nameForm . '_UFFRESP');
    }

    public function toggleBottoniPrincipali($modo = 'disabilita') {
        switch ($modo) {
            case 'disabilita':
                $hideShow = 'hide';
                break;
            case 'abilita':
                $hideShow = 'show';
                break;
        }
        Out::$hideShow($this->nameForm . '_Chiudi');
        Out::$hideShow($this->nameForm . '_Aggiorna');
        Out::$hideShow($this->nameForm . '_Riserva');
        Out::$hideShow($this->nameForm . '_ANAORG[ORGKEYPRE]_butt');
        Out::$hideShow($this->nameForm . '_CANC_FASPRE');
    }

//    private function AggiornaTitolario($Itepro, $Tipo) {
//        $anapro_rec = $this->proLib->GetAnapro($Itepro, 'codice', $Tipo);
//        if (!$anapro_rec) {
//            return false;
//        }
//        $anapro_rec['PROCAT'] = $this->formData[$this->nameForm . "_ANAPRO"]['PROCAT'];
//        $anapro_rec['PROCCA'] = $anapro_rec['PROCAT'] . $this->formData[$this->nameForm . "_Clacod"];
//        $anapro_rec['PROCCF'] = $anapro_rec['PROCCA'] . $this->formData[$this->nameForm . "_Fascod"];
//        $update_Info = "Oggetto: Aggiorno il titolario del protocollo n. $Itepro tipo $Tipo";
//        if (!$this->updateRecord($this->PROT_DB, 'ANAPRO', $anapro_rec, $update_Info)) {
//            return false;
//        }
//        return true;
//    }

    private function controllaTitolario($uffcod, $catcod, $clacod = '', $fascod = '') {
        $sql = "SELECT * FROM UFFTIT WHERE UFFCOD='$uffcod' AND CATCOD='$catcod'";
        if ($clacod) {
            $sql .= " AND CLACOD='$clacod'";
        }
        if ($fascod) {
            $sql .= " AND FASCOD='$fascod'";
        }
        $ufftit_test = $this->proLib->getGenericTab($sql, false);
        if ($ufftit_test) {
            return $ufftit_test;
        }
        return false;
    }

    private function checkCatFiltrato($codice) {
        $titolario_rec = $this->controllaTitolario($_POST[$this->nameForm . '_PROGES']['GESPROUFF'], $codice);
        Out::valore($this->nameForm . '_Catcod', $codice);
        Out::valore($this->nameForm . '_catdes', '');
        if ($titolario_rec) {
            $this->DecodAnacat($codice);
        }
    }

    private function checkClaFiltrato($codice1, $codice2) {
        $titolario_rec = $this->controllaTitolario($_POST[$this->nameForm . '_PROGES']['GESPROUFF'], $codice1, $codice2);
        Out::valore($this->nameForm . '_Clacod', $codice2);
        Out::valore($this->nameForm . '_clades', '');
        if ($titolario_rec) {
            $this->DecodAnacla($codice1 . $codice2);
        }
    }

    private function DecodAnacat($codice, $tipo = 'codice') {
        Out::valore($this->nameForm . '_Catcod', '');
        Out::valore($this->nameForm . '_Clacod', '');
        Out::valore($this->nameForm . '_Fascod', '');
        Out::valore($this->nameForm . '_catdes', '');
        Out::valore($this->nameForm . '_clades', '');
        Out::valore($this->nameForm . '_fasdes', '');
        $anacat_rec = $this->proLib->GetAnacat('', $codice, $tipo);
        if ($anacat_rec) {
            Out::valore($this->nameForm . '_Catcod', $anacat_rec['CATCOD']);
            Out::valore($this->nameForm . '_catdes', $anacat_rec['CATDES']);
        }
        return $anacat_rec;
    }

    private function DecodAnacla($codice, $tipo = 'codice') {
        Out::valore($this->nameForm . '_Clacod', '');
        Out::valore($this->nameForm . '_Fascod', '');
        Out::valore($this->nameForm . '_clades', '');
        Out::valore($this->nameForm . '_fasdes', '');
        $anacla_rec = $this->proLib->GetAnacla('', $codice, $tipo);
        if ($anacla_rec) {
            Out::valore($this->nameForm . '_Clacod', $anacla_rec['CLACOD']);
            Out::valore($this->nameForm . '_clades', $anacla_rec['CLADE1'] . $anacla_rec['CLADE2']);
        }
        return $anacla_rec;
    }

    private function DecodAnafas($codice, $tipo = 'fasccf') {
        $anafas_rec = $this->proLib->GetAnafas('', $codice, $tipo);
        Out::valore($this->nameForm . '_Fascod', $anafas_rec['FASCOD']);
        Out::valore($this->nameForm . '_fasdes', $anafas_rec['FASDES']);
        return $anafas_rec;
    }

    private function DecodAnaorg($codice, $tipo = 'codice', $codiceCcf = '') {
        $anaorg_rec = $this->proLib->GetAnaorg($codice, $tipo, $codiceCcf);
        if ($anaorg_rec) {
            Out::valore($this->nameForm . '_Orgcod', $anaorg_rec['ORGCOD']);
            Out::valore($this->nameForm . '_Organn', $anaorg_rec['ORGANN']);
        } else {
            Out::valore($this->nameForm . '_Orgcod', '');
            Out::valore($this->nameForm . '_Organn', '');
        }
        return $anaorg_rec;
    }

    private function decodTitolario($chiave, $tipoArc, $tipoChiave = 'rowid') {
        Out::valore($this->nameForm . '_Catcod', '');
        Out::valore($this->nameForm . '_Clacod', '');
        Out::valore($this->nameForm . '_Fascod', '');
        Out::valore($this->nameForm . '_catdes', '');
        Out::valore($this->nameForm . '_clades', '');
        Out::valore($this->nameForm . '_fasdes', '');
        switch ($tipoArc) {
            case 'ANACAT':
                $anacat_rec = $this->proLib->GetAnacat('', $chiave, $tipoChiave);
                $this->DecodAnacat($chiave, $tipoChiave);
                $codTitolario = $anacat_rec['CATCOD'];
                break;
            case 'ANACLA':
                $anacla_rec = $this->proLib->GetAnacla('', $chiave, $tipoChiave);
                $this->DecodAnacat($anacla_rec['CLACAT'], 'codice');
                $this->DecodAnacla($chiave, $tipoChiave);
                $codTitolario = $anacla_rec['CLACCA'];
                break;
            case 'ANAFAS':
                $anafas_rec = $this->proLib->GetAnafas('', $chiave, $tipoChiave);
                $this->DecodAnacat(substr($anafas_rec['FASCCA'], 0, 4), 'codice');
                $this->DecodAnacla($anafas_rec['FASCCA'], 'codice');
                $this->DecodAnafas($chiave, $tipoChiave);
                $codTitolario = $anafas_rec['FASCCA'];
                break;
        }
        $this->VisualizzaNascondiSerie($codTitolario);
    }

    private function DecodResponsabile($codice, $tipoRic = 'codice', $uffcod = '') {
        if (trim($codice) != "") {
            if (is_numeric($codice)) {
                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
            }
            $anamed_rec = $this->proLib->GetAnamed($codice, $tipoRic, 'no');
            if (!$anamed_rec) {
                Out::valore($this->nameForm . '_PROGES[GESRES]', '');
                Out::valore($this->nameForm . '_Desc_resp2', '');
                Out::setFocus('', $this->nameForm . "_Desc_resp2");
                return;
            } else {
                Out::valore($this->nameForm . '_PROGES[GESRES]', $anamed_rec['MEDCOD']);
                Out::valore($this->nameForm . '_Desc_resp2', $anamed_rec['MEDNOM']);

                if ($uffcod) {
                    $anauff_rec = $this->proLib->GetAnauff($uffcod);
                    Out::valore($this->nameForm . '_PROGES[GESUFFRES]', $anauff_rec['UFFCOD']);
                    Out::valore($this->nameForm . '_UFFRESP', $anauff_rec['UFFDES']);
                    return;
                } else {
                    $uffdes_tab = $this->proLib->getGenericTab("SELECT ANAUFF.UFFCOD FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFKEY='$codice' AND ANAUFF.UFFANN=0");
                    if (count($uffdes_tab) == 1) {
                        $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
                        Out::valore($this->nameForm . '_PROGES[GESUFFRES]', $anauff_rec['UFFCOD']);
                        Out::valore($this->nameForm . '_UFFRESP', $anauff_rec['UFFDES']);
                    } else {
//                        if ($_POST[$this->nameForm . '_PROGES']['GESUFFRES'] == '' || $_POST[$this->nameForm . '_UFFRESP'] == '') {
                        proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', '', 'Firmatario');
                        Out::setFocus('', "utiRicDiag_gridRis");
                        return;
//                        }
                    }
                }
            }
        } else {
            Out::valore($this->nameForm . '_PROGES[GESRES]', '');
            Out::valore($this->nameForm . '_Desc_resp2', '');
        }
        Out::setFocus('', $this->nameForm . "_Desc_resp2");
    }

    private function caricaUof() {
        Out::codice('$(protSelector("#' . $this->nameForm . '_PROGES[GESPROUFF]' . '")+" option").remove();');
        $profilo = proSoggetto::getProfileFromIdUtente();
        $anamed_rec = $this->proLib->GetAnamed($profilo['COD_SOGGETTO']);
        if ($anamed_rec) {
            $uffdes_tab = $this->proLib->GetUffdes($anamed_rec['MEDCOD'], 'uffkey', true, ' ORDER BY UFFFI1__3 DESC', true);
            $select = "1";
            foreach ($uffdes_tab as $uffdes_rec) {
                $anauff_rec = $this->proLib->GetAnauff($uffdes_rec['UFFCOD']);
                if ($select) {
                    $this->prouof = $anauff_rec['UFFCOD'];
                }
                if ($anauff_rec['UFFANN'] == 0) {
                    Out::select($this->nameForm . '_PROGES[GESPROUFF]', 1, $uffdes_rec['UFFCOD'], $select, substr($anauff_rec['UFFDES'], 0, 30));
                    $select = '';
                }
            }
        }
    }

    private function salvaAllegati($allegati, $NumProt, $TipoProt) {
        $gestiti = $this->proLibAllegati->GestioneAllegati($this, $NumProt, $TipoProt, $allegati, '', '');
        if (!$gestiti) {
            Out::msgStop("Attenzione!", $this->proLibAllegati->getErrMessage());
            return false;
        }
    }

    private function noteGenFilterFromGrid() {
        $filter = '';
        if ($_POST['NOTA']) {
            if ($this->validateDate($_POST['NOTA']) !== false) {
                $date = substr($_POST['NOTA'], 6, 4) . substr($_POST['NOTA'], 3, 2) . substr($_POST['NOTA'], 0, 2);
                $filter = "(DATAINS = '$date')";
            } else {
                $search = strtoupper($_POST['NOTA']);
                $filter = "(" . $this->PROT_DB->strupper('OGGETTO') . " LIKE '%" . strtoupper($_POST['NOTA']) . "%' OR " . $this->PROT_DB->strupper('TESTO') . " LIKE '%" . strtoupper($_POST['NOTA']) . "%' OR " . $this->PROT_DB->strupper('UTELOG') . " LIKE '%" . strtoupper($_POST['NOTA']) . "%')";
            }
        }
        return $filter;
    }

    private function validateDate($date, $format = 'd/m/Y') {
        //$d = DateTime::createFromFormat($format, $date);
        if (preg_match('/^(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[0-9]{4}$/', $date)) {
            return true;
        }
        return false;
    }

    private function checkProprietario($pronum, $propar) {
        $risultato = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'codice', $pronum, $propar);
        if ($risultato) {
            return true;
        }
//        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
//        $profilo = proSoggetto::getProfileFromIdUtente();
//        if ($proges_rec['GESPROUTE'] == $profilo['UTELOG'] || $proges_rec['GESRES'] == $profilo['COD_SOGGETTO']) {
//            return true;
//        }
        return false;
    }

    private function permessiFascicolo($pronum, $propar) {
        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum');
        if (!$permessiFascicolo[proLibFascicolo::PERMFASC_MODIFICA_DATI]) {
            Out::hide($this->nameForm . '_Aggiorna');
            Out::hide($this->nameForm . '_Chiudi');
            Out::hide($this->nameForm . '_Apri');
            Out::hide($this->nameForm . '_Riserva');
            Out::hide($this->nameForm . '_NonRiserva');
//            Out::block($this->nameForm . "_divHead");
        } else {
//            Out::unBlock($this->nameForm . "_divHead");
        }
    }

    private function bloccaCelleGrigliaDocumenti($tipoTable) {
        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
        $this->CaricaGriglia($this->gridDocumenti, $this->proDocumenti, $tipoTable);
        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum');
        $lastsf = null;
        $anaent_48 = $this->proLib->GetAnaent('48');
        foreach ($this->proDocumenti as $key => $documento) {
            $flblocca = false;
            if (!$permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI]) {
                $flblocca = true;
                if (substr($key, 0, 4) == "DOC-" && substr($documento['parent'], 14) == 'N') {
                    if ($lastsf != $documento['parent']) {
                        $permessiSottoFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum', substr($documento['parent'], 4, 10));
                        $lastsf = $documento['parent'];
                    }
                }
                if ($permessiSottoFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI]) {
                    $flblocca = false;
                }
            }
//            TableView::setCellValue($this->gridDocumenti, $key, 'NOTE', "", 'not-editable-cell', '', 'false');
            if ($documento['PRORISERVA'] == '1' || $documento['GESTIONE'] === false || substr($key, 0, 4) == "PRO-" || $proges_rec['GESDCH'] || $flblocca == true) {
                TableView::setCellValue($this->gridDocumenti, $key, 'NOTE', "", 'not-editable-cell', '', 'false');
            }
            // Controllo se è il doc di un protocollo, non è modificabile
            if (substr($key, 0, 4) == "DOC-") {
                if ($documento['PRONUM']) {
                    if ($anaent_48['ENTDE3'] != 1) {
                        TableView::setCellValue($this->gridDocumenti, $key, 'NOTE', "", 'not-editable-cell', '', 'false');
                    }
                }
            }

            if (substr($key, 14) == 'N' || substr($key, 14) == 'F') {
                /* Stile solo per ICONANODO */
                // $stile = 'border-radius: 3px; width: 97%; height: 100%; background-color:rgba(255, 188, 0, 0.3); border: 2px solid #ffd45c;';
//                $stile = 'margin-left:-3px; border-radius: 3px; width: 100%; height: 100%; background-color:rgba(255, 188, 0, 0.3); border: 2px solid #ffd45c;';
//                $valore = '<div style="' . $stile . '">' . $documento['ORGNODEICO'] . '</div>';
//                TableView::setCellValue($this->gridDocumenti, $key, 'ORGNODEICO', $valore);
                /* Stile  per ICONANODO,NOME,NOTE */
//                 $stile = ' width: 100%; height: 100%; background-color:rgba(255, 188, 0, 0.3);';
                $stile = 'margin-left:-3px; border-radius: 3px; width: 100%; height: 100%; background-color:rgba(255, 188, 0, 0.3); border: 2px solid #ffd45c;';
                $tagIni = '<div style="' . $stile . '" >';
                $tagFin = '</div>';
//                TableView::setCellValue($this->gridDocumenti, $key, 'ORGNODEICO', $tagIni . $documento['ORGNODEICO'] . $tagFin);// ita-html tooltip non si vede altrimenti.
                TableView::setCellValue($this->gridDocumenti, $key, 'NAME', $tagIni . $documento['NAME'] . $tagFin);
                TableView::setCellValue($this->gridDocumenti, $key, 'NOTE', $tagIni . $documento['NOTE'] . $tagFin);
            }
        }
    }

    private function CreaSql() {
        $Stato_proc = $_POST[$this->nameForm . '_Stato_proc'];
        $Dal_num = $_POST[$this->nameForm . '_Dal_num'];
        $al_num = $_POST[$this->nameForm . '_Al_num'];
        $anno = $_POST[$this->nameForm . '_Anno'];
        $daData = $_POST[$this->nameForm . '_Da_data'];
        $aData = $_POST[$this->nameForm . '_A_data'];
        $daDatach = $_POST[$this->nameForm . '_Da_datach'];
        $aDatach = $_POST[$this->nameForm . '_A_datach'];
        $procedimento = $_POST[$this->nameForm . '_Procedimento'];
        $Responsabile = $_POST[$this->nameForm . '_Responsabile'];
        $Campo = $_POST[$this->nameForm . '_Campo'];
        $nomeCampo = $_POST[$this->nameForm . '_NomeCampo'];
        $documento = $_POST[$this->nameForm . '_DocAllegato'];
        $Versione = $_POST[$this->nameForm . '_Versione'];
        /*   Ricerca Serie: */
        $CodiceSerie = $_POST[$this->nameForm . '_ric_codiceserie'];
        $SiglaSerie = $_POST[$this->nameForm . '_ric_siglaserie'];
        $ProgSerie = $_POST[$this->nameForm . '_ric_progseserie'];
        $RicDoc = $_POST[$this->nameForm . '_RICDOC'];

        if ($anno == '') {
            if ($procedimento != '') {
                $procedimento = str_pad($procedimento, 6, 0, STR_PAD_RIGHT);
            }
        }
        if ($Dal_num == '') {
            $Dal_num = "0";
        }
        if ($al_num == '') {
            $al_num = "999999";
        }
        $Dal_num = $this->proLibFascicolo->repertoriofascicoli . $anno . str_pad($Dal_num, 6, "0", STR_PAD_LEFT);
        $al_num = $this->proLibFascicolo->repertoriofascicoli . $anno . str_pad($al_num, 6, "0", STR_PAD_LEFT);

        $filtroMessaggio = "Ricerca dal numero $Dal_num al $al_num";

        if ($Campo) {
            $filtroMessaggio .= ', Campo Aggiuntivo: ' . $Campo;
            $joinCampo = "INNER JOIN PRODAG PRODAG1 ON PROGES.GESNUM = PRODAG1.DAGNUM AND " . $this->PROT_DB->strLower('PRODAG1.DAGVAL') . " LIKE '%" . strtolower($campo) . "%'";
        }
        if ($nomeCampo) {
            $Praidc_rec = $this->praLib->GetPraidc($nomeCampo);
            if ($Praidc_rec['IDCTIP']) {
                $whereIdc = " OR PRODAG2.DAGTIP = '" . $Praidc_rec['IDCTIP'] . "'";
            }
            $filtroMessaggio .= ', Nome Campo Aggiuntivo: ' . $nomeCampo;
            //$joinNomeCampo = "INNER JOIN PRODAG PRODAG2 ON PROGES.GESNUM = PRODAG2.DAGNUM AND (LOWER(PRODAG2.DAGKEY) LIKE LOWER('%$nomeCampo%') $whereIdc)";
            $joinNomeCampo = "INNER JOIN PRODAG PRODAG2 ON PROGES.GESNUM = PRODAG2.DAGNUM AND (" . $this->PROT_DB->strLower('PRODAG2.DAGKEY') . " LIKE '%" . strtolower($nomeCampo) . "%' $whereIdc)";
        }
        if ($documento) {
            $joinAnadoc = "LEFT OUTER JOIN ANADOC ANADOC ON ANAPRO.PRONUM=ANADOC.DOCNUM AND ANAPRO.PROPAR=ANADOC.DOCPAR";
        }
        $sql = "SELECT
            DISTINCT PROGES.ROWID AS ROWID,
            PROGES.GESNUM AS GESNUM,
            PROGES.GESKEY AS GESKEY,
            PROGES.GESOGG AS GESOGG,
            PROGES.GESDRE AS GESDRE,
            PROGES.GESDRI AS GESDRI,
            PROGES.GESORA AS GESORA,
            PROGES.GESDCH AS GESDCH,
            PROGES.GESNOT AS GESNOT,
            PROGES.GESPRE AS GESPRE,
            PROGES.GESNPR AS GESNPR,
            PROGES.GESRES AS GESRES,
            PROGES.GESPRO AS GESPRO,
            ANAMED.MEDNOM AS RESPONSABILE,
            ANAUFF.UFFDES AS UFFRESPONSABILE,
            ANACAT.CATDES AS CATDES,
            ANACLA.CLADE1 AS CLADE1,
            ANACLA.CLADE2 AS CLADE2,
            ANAFAS.FASDES AS FASDES,
            ANAPRO.PROCCF AS PROCCF,
            ANAPRO.PRORISERVA AS PRORISERVA,
            ANAPRO.PROTSO AS PROTSO,
            ANAPRO.PRONUM AS PRONUM,
            ANAPRO.PROPAR AS PROPAR,
                {$this->PRAM_DB->getDB()}.ANAPRA.PRADES__1 AS PRADES__1,
            ANASERIEARC.DESCRIZIONE AS DESCSERIE,
            ANASERIEARC.SIGLA AS SIGLA,
            ANAORG.CODSERIE AS CODSERIE,
            ANAORG.PROGSERIE AS PROGSERIE,
            ANAORG.ORGKEYPRE AS ORGKEYPRE

        FROM PROGES PROGES
            LEFT OUTER JOIN
                ANAPRO ANAPRO
            ON
                ANAPRO.PROFASKEY=PROGES.GESKEY AND (ANAPRO.PROPAR='F' OR ANAPRO.PROPAR='N')
            LEFT OUTER JOIN
                ARCITE ARCITE
            ON
                ANAPRO.PRONUM=ARCITE.ITEPRO AND
                ANAPRO.PROPAR=ARCITE.ITEPAR
            LEFT OUTER JOIN {$this->PRAM_DB->getDB()}.ANAPRA ANAPRA ON PROGES.GESPRO={$this->PRAM_DB->getDB()}.ANAPRA.PRANUM
            LEFT OUTER JOIN ANAMED ANAMED ON PROGES.GESRES=ANAMED.MEDCOD
            LEFT OUTER JOIN ANAUFF ANAUFF ON PROGES.GESUFFRES=ANAUFF.UFFCOD
            
            LEFT OUTER JOIN ANAFAS ANAFAS ON ANAPRO.PROCCF=ANAFAS.FASCCF AND ANAFAS.FASDAT=''
            LEFT OUTER JOIN ANACLA ANACLA ON ANAPRO.PROCCA=ANACLA.CLACCA AND ANACLA.CLADAT=''
            LEFT OUTER JOIN ANACAT ANACAT ON ANACLA.CLACAT=ANACAT.CATCOD AND ANACLA.CLADAT=ANACAT.CATDAT
            LEFT OUTER JOIN ORGCONN ON ANAPRO.PROFASKEY=ORGCONN.ORGKEY AND ORGCONN.CONNDATAANN = '' 
            LEFT OUTER JOIN ANAORG ON PROGES.GESKEY=ANAORG.ORGKEY
            LEFT OUTER JOIN ANASERIEARC ON ANAORG.CODSERIE=ANASERIEARC.CODICE

                $joinCampo
                $joinNomeCampo
                $joinAnadoc
        WHERE (GESNUM BETWEEN '$Dal_num' AND '$al_num')";
        /* Where Fascicolo */
        if ($Stato_proc == 'C') {
            $sql .= " AND GESDCH <> ''";
            $filtroMessaggio .= ', Fascicoli Chiusi';
        } else if ($Stato_proc == 'A') {
            $sql .= " AND GESDCH = ''";
            $filtroMessaggio .= ', Fascicoli Aperti';
        }
        $faskey_ric = '';
        if ($_POST[$this->nameForm . '_catcod'] || $_POST[$this->nameForm . '_clacod'] || $_POST[$this->nameForm . '_fascod'] || $_POST[$this->nameForm . '_annoOrg'] || $_POST[$this->nameForm . '_orgcod']) {
            if ($_POST[$this->nameForm . '_catcod'] != "") {
                $codice = $_POST[$this->nameForm . '_catcod'];
                $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                $faskey_ric .= $codice;
            } else {
                if ($_POST[$this->nameForm . '_clacod'] != "" || $_POST[$this->nameForm . '_fascod'] != "") {
                    $faskey_ric .= "____";
                } else {
                    $faskey_ric .= "%";
                }
            }

            if ($_POST[$this->nameForm . '_clacod'] != "") {
                $codice = $_POST[$this->nameForm . '_clacod'];
                $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                $faskey_ric .= $codice;
            } else {
                if ($_POST[$this->nameForm . '_fascod'] != "") {
                    $faskey_ric .= "____";
                } else {
                    $faskey_ric .= "%";
                }
            }
            if ($_POST[$this->nameForm . '_fascod'] != "") {
                $codice = $_POST[$this->nameForm . '_fascod'];
                $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                $faskey_ric .= $codice . '.';
            } else {
                $faskey_ric .= "%.";
            }
            if ($_POST[$this->nameForm . '_annoOrg'] != "") {
                $faskey_ric .= $_POST[$this->nameForm . '_annoOrg'] . ".";
            } else {
                $faskey_ric .= "____.";
            }

            if ($_POST[$this->nameForm . '_orgcod'] != "") {
                $codice = $_POST[$this->nameForm . '_orgcod'];
                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                $faskey_ric .= $codice;
            } else {
                $faskey_ric .= "______";
            }
        }
        /* Nuovo parametro di ricerca se utilizzato il titolario, ne va indicata la versione. */
        if ($_POST[$this->nameForm . '_clacod'] || $_POST[$this->nameForm . '_catcod'] || $_POST[$this->nameForm . '_fascod']) {
            $sql .= " AND ANAPRO.VERSIONE_T = $Versione ";
        }
        // if ($Versione) {
        //     $sql.=" AND ANAPRO.VERSIONE_T = $Versione ";
        // }

        if ($_POST[$this->nameForm . '_OggettoPratica'] != "") {
            $sql .= " AND " . $this->PRAM_DB->strLower('GESOGG') . " LIKE '%" . strtolower($_POST[$this->nameForm . '_OggettoPratica']) . "%' ";
        }

        if ($_POST[$this->nameForm . '_catcod'] || $_POST[$this->nameForm . '_clacod'] || $_POST[$this->nameForm . '_fascod']) {
            $filtroMessaggio .= ', Titolario: ' . $_POST[$this->nameForm . '_catcod'] . $_POST[$this->nameForm . '_clacod'] . $_POST[$this->nameForm . '_fascod'];
        }
        // ORGKEY DI ORGCONN/GESKEY OK.
        if ($faskey_ric) {
            $sql .= " AND GESKEY LIKE '$faskey_ric'";
        }
        /* PRONUM E PROPAR DI ORGCONN */
        if ($_POST[$this->nameForm . '_Pronum'] != "") {
            $codice = $_POST[$this->nameForm . '_Pronum'];
            $pronum = str_pad($codice, 6, "0", STR_PAD_LEFT);
            $anno = $_POST[$this->nameForm . '_AnnoProt'];
            if (!$anno) {
                $anno = date('Y');
            }
            $filtroMessaggio .= ', Numero Protocollo: ' . $anno . $pronum;
            if (!$_POST[$this->nameForm . '_Propar']) {
                $tipoProt = " AND (ORGCONN.PROPAR='A' OR ORGCONN.PROPAR='P' OR ORGCONN.PROPAR='C')";
            } else {
                $tipoProt = " AND ORGCONN.PROPAR='{$_POST[$this->nameForm . '_Propar']}'";
                $filtroMessaggio .= ' di tipo ' . $_POST[$this->nameForm . '_Propar'];
            }
            $sql .= " AND ORGCONN.PRONUM=" . $anno . $pronum . $tipoProt;
        }

        if ($daData && $aData) {
            $sql .= " AND (GESDRE BETWEEN '$daData' AND '$aData')";
            $filtroMessaggio .= ', dalla data: ' . date('d/m/Y', strtotime($daData)) . ' alla data : ' . date('d/m/Y', strtotime($aData));
        }

        if ($daDatach && $aDatach) {
            $sql .= " AND (GESDCH BETWEEN '$daDatach' AND '$aDatach')";
            $filtroMessaggio .= ', dalla data chiusura: ' . date('d/m/Y', strtotime($daDatach)) . ' alla data chiusura : ' . date('d/m/Y', strtotime($aDatach));
        }

        if ($procedimento) {
            $sql .= " AND GESPRO = '" . $procedimento . "'";
            $filtroMessaggio .= ', procedimento: ' . $procedimento;
        }
        if ($Responsabile != '') {
            $sql .= " AND (PROGES.GESRES = '$Responsabile')";
            $filtroMessaggio .= ', responsabile: ' . $Responsabile;
        }
        if ($documento) {
            $sql .= " AND (" . $this->PROT_DB->strUpper('DOCNOT') . " LIKE '%" . strtoupper($documento) . "%' OR DOCNAME LIKE '%" . strtoupper($documento) . "%')";
            $filtroMessaggio .= ', documento: ' . $documento;
        }
        // Ricerca delle serie:
        if ($CodiceSerie) {
            $sql .= "AND ANAORG.CODSERIE = '$CodiceSerie' ";
        }
        if ($SiglaSerie) {
            $sql .= "AND " . $this->PROT_DB->strUpper('ANASERIEARC.SIGLA') . " LIKE '%" . strtoupper($SiglaSerie) . "%' ";
        }
        if ($ProgSerie) {
            $sql .= "AND ANAORG.PROGSERIE = '$ProgSerie' ";
        }

        $where_profilo = proSoggetto::getSecureWhereFromIdUtente($this->proLib, 'fascicolo');
        $sql .= " AND $where_profilo";
        $sql .= " GROUP BY GESNUM"; // Per non far vedere pratiche doppie a colpa della join con ANADES
//        App::log('$sql elenca');
//        App::log($sql);
        /**
         *  DA VERIFICARE A SISTEMAZIONE CONDIZIONE FINITA
         */
        if ($_POST[$this->nameForm . '_DestinatarioFascicolo']) {
            $sql2 = "SELECT"
                    . " BASE.*"
                    . " FROM ($sql) BASE"
                    . " LEFT OUTER JOIN ANAPRO ANAPRO ON ANAPRO.PROFASKEY=BASE.GESKEY"
                    . " LEFT OUTER JOIN ARCITE ARCITE ON ARCITE.ITEPRO=ANAPRO.PRONUM AND ARCITE.ITEPAR=ANAPRO.PROPAR"
                    . " WHERE ARCITE.ITEDES='{$_POST[$this->nameForm . '_DestinatarioFascicolo']}' "
                    . " GROUP BY BASE.GESNUM";
        } else {
            $sql2 = $sql;
        }
        $this->filtroMessaggio = $filtroMessaggio;
        return $sql2;
    }

    private function checkAssegnaProtocolloAlFascicolo($pronum, $propar, $check = true) {
        /*
         *  Controllo se può gestirlo:
         *  Serve vedere se è responsabile del protocollo?
         */
        $pronumSottoFascicolo = '';
        $retIterStato = proSoggetto::getIterStato($pronum, $propar);
        if ($this->varAppoggio) {
            if ($this->varAppoggio['ADDPROTOCOLLONODE']['PROPAR'] == 'N') {
                $pronumSottoFascicolo = $this->varAppoggio['ADDPROTOCOLLONODE']['PRONUM'];
            }
        }

        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum', $pronumSottoFascicolo);
        /*
         * Se permessi attivati per la gestione completa del fascicolo e protocollo in gestione può fascicolare 
         */
//        $fl_fascicola = false;
//        if ($permessiFascicolo[proLibFascicolo::PERMFASC_CREAZIONE] && ($retIterStato['GESTIONE']|| $retIterStato['RESPONSABILE'])) {
//            $fl_fascicola = true;
//        }

        /*
         * Se permessi attivati per la movimentazione del fascicolo e protocollo in gestione può movimentare
         */
        $fl_movimenta = false;
        if (($permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI] && ($retIterStato['GESTIONE'] || $retIterStato['RESPONSABILE'])) || $permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI]) {
            $fl_movimenta = true;
        }
        //if (!$retIterStato['GESTIONE']) {// || $respProto) {
        if (!$fl_movimenta) {// || $respProto) {
            Out::msgStop("Attenzione", "Non puoi inserire il Protocollo n. $pronum di tipo $propar nel fascicolo, perchè non hai i permessi di gestione.");
            return false;
        }
        $anapro_rec = $this->proLib->GetAnapro($pronum, 'codice', $propar);
        if ($anapro_rec) {
            $anapro_check = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'codice', $pronum, $propar);
            if ($anapro_check) {
                $anapro_F_rec = $this->proLibFascicolo->getAnaproFascicolo($this->geskey);
                if ($anapro_check['PROCCF'] != $anapro_F_rec['PROCCF']) {
                    $this->AnaproTitolarioDifferente = $anapro_rec;
                    Out::msgQuestion("Attenzione!", "Il protocollo selezionato ha un titolario differente dal fascicolo.<br> Vuoi confermare l'operazione?", array(
                        'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaTitolarioDiff', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaTitolarioDiff', 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                    );
                    return false;
                }
            } else {
                Out::msgStop("Attenzione!", "<br><br>Il protocollo selezionato non è fascicolabile.<br>Non hai le autorizzazioni su questo documento.");
                return false;
            }
        } else {
            Out::msgStop("Attenzione!", "Il protocollo non esiste, selezionane uno esistente.");
            return false;
        }
        if ($check) {
            $anaogg_rec = $this->proLib->GetAnaogg($anapro_check['PRONUM'], $anapro_check['PROPAR']);
        }
        return array('ASSEGNA' => true, 'ANAPRO_C' => $anapro_check);
    }

    private function caricaParametri() {
        $anaent_33 = $this->proLib->GetAnaent('33');
        $this->attivaAzioni = $anaent_33['ENTDE5'];
    }

    private function ChiediDatiChiusura() {
        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum');
        if (!$permessiFascicolo[proLibFascicolo::PERMFASC_APERTURA_CHIUSURA]) {
            Out::msgStop("Attenzione", "Non hai il permesso di chiudere il fascicolo.");
            return;
        }
        $messaggio = "Gestisci le informazioni per la chiusura del fascicolo";
        $valori[] = array(
            'label' => array(
                'value' => "Data Chiusura:",
                'style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_DataChiusuraFascicolo',
            'name' => $this->nameForm . '_DataChiusuraFascicolo',
            'type' => 'text',
            'class' => 'ita-datepicker',
            'size' => '12',
            'value' => date('Ymd')
        );
        Out::msgInput(
                'Conferma Chiusura', $valori
                , array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaChiusuraFascicolo', 'model' => $this->nameForm),
            'Annulla' => array('id' => $this->nameForm . '_AnnullaChiusuraFascicolo', 'model' => $this->nameForm)
                ), $this->nameForm . "_workSpace", 'auto', '700', true, "<span style=\"font-size:1.2em;font-weight:bold;\">$messaggio</span>", "", true
        );
    }

    private function OLDSganciaDaFascicolo() {
        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
        if ($proges_rec['GESDCH']) {
            Out::msgInfo("Attenzione", "Fascicolo Chiuso. <br/>Non Applicabile");
            return;
        }
        $rowid = $_POST[$this->gridDocumenti]['gridParam']['selarrrow'];
        $rowid_orgconn = $this->proDocumenti[$rowid]['ROWID_ORGCONN'];

        // si può centralizzare...
        $pronumSottoFascicolo = $this->GetPronumSottoFascicloSelezionato();
        $arrBottoni = array();
        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum', $pronumSottoFascicolo);
        if ($permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_PROTOCOLLI] || $permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI]) {
            $arrBottoni['F8-Annulla'] = array('id' => $this->nameForm . '_AnnullaSganciaDoc', 'model' => $this->nameForm, 'shortCut' => "f8");
            $arrBottoni['F5-Conferma'] = array('id' => $this->nameForm . '_ConfermaSganciaDoc', 'model' => $this->nameForm, 'shortCut' => "f5");

            $CountSott = $this->GetCountAnaproSottofascicoli($proges_rec['GESKEY']);
            if ($rowid_orgconn && $CountSott['SOTTOFASCICOLI']) {
                $arrBottoni['Sposta internamente'] = array('id' => $this->nameForm . '_SpostaDocumentoInterno', 'model' => $this->nameForm, 'shortCut' => "f8");
            }
            if ($rowid_orgconn) {
                $arrBottoni['Sposta in un Fascicolo'] = array('id' => $this->nameForm . '_SpostaDocumento', 'model' => $this->nameForm, 'shortCut' => "f8");
            }

            $Operaz = 'Sgancio';
            $TestoOperaz = 'Vuoi sganciare questo documento dal fascicolo?';
            if (substr($rowid, 0, 4) == 'DOC-') {
                $Operaz = 'Cancellazione';
                $TestoOperaz = 'Vuoi cancellare definitivamente questo documento dal fascicolo?';
            }
            Out::msgQuestion($Operaz, $TestoOperaz, $arrBottoni);
        } else {
            Out::msgStop("Attenzione", "Non hai il permesso di gestire protocolli e documenti nel fascicolo.");
            return;
        }
    }

    private function SganciaDaFascicolo() {
        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
        if ($proges_rec['GESDCH']) {
            Out::msgInfo("Attenzione", "Fascicolo Chiuso. <br/>Non Applicabile");
            return;
        }
        $rowid = $_POST[$this->gridDocumenti]['gridParam']['selarrrow'];
        $rowid_orgconn = $this->proDocumenti[$rowid]['ROWID_ORGCONN'];
        $retCheck = $this->CheckPermessiDocumentoFascicolo($rowid);

//        // si può centralizzare...
//        $pronumSottoFascicolo = $this->GetPronumSottoFascicloSelezionato();
//        $arrBottoni = array();
//        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum', $pronumSottoFascicolo);
//        if ($permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_PROTOCOLLI] || $permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI]) {
//        App::log($retCheck);
        if ($retCheck['GESTIONE'] == true) {
            $arrBottoni['F8-Annulla'] = array('id' => $this->nameForm . '_AnnullaSganciaDoc', 'model' => $this->nameForm, 'shortCut' => "f8");
            $arrBottoni['F5-Conferma'] = array('id' => $this->nameForm . '_ConfermaSganciaDoc', 'model' => $this->nameForm, 'shortCut' => "f5");

            $CountSott = $this->GetCountAnaproSottofascicoli($proges_rec['GESKEY']);
            if ($rowid_orgconn && $CountSott['SOTTOFASCICOLI']) {
                $arrBottoni['Sposta internamente'] = array('id' => $this->nameForm . '_SpostaDocumentoInterno', 'model' => $this->nameForm, 'shortCut' => "f8");
            }
            if ($rowid_orgconn) {
                $arrBottoni['Sposta in un Fascicolo'] = array('id' => $this->nameForm . '_SpostaDocumento', 'model' => $this->nameForm, 'shortCut' => "f8");
            }

            $Operaz = 'Sgancio';
            $TestoOperaz = 'Vuoi sganciare questo documento dal fascicolo?';
            if (substr($rowid, 0, 4) == 'DOC-') {
                $Operaz = 'Cancellazione';
                $TestoOperaz = 'Vuoi cancellare definitivamente questo documento dal fascicolo?';
            }
            Out::msgQuestion($Operaz, $TestoOperaz, $arrBottoni);
        } else {
            Out::msgStop("Attenzione", "Non hai il permesso di gestire protocolli e documenti nel fascicolo.");
            return;
        }
    }

    private function SganciaDocumento() {
        $rowid = $_POST[$this->gridDocumenti]['gridParam']['selarrrow'];
        $rowid_orgconn = $this->proDocumenti[$rowid]['ROWID_ORGCONN'];

        $pronumSottoFascicolo = $this->GetPronumSottoFascicloSelezionato();
        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum', $pronumSottoFascicolo);
        $retCheck = $this->CheckPermessiDocumentoFascicolo($rowid);
        // Se non è presente un ROWID di connessione con ORGCON:
        //    Significa che è un Documento Allegato al Fascicolo o al SottoFascicolo
        if (!$rowid_orgconn) {
            if (substr($rowid, 0, 4) == 'DOC-') {
                if ($permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI] || $retCheck['GESTIONE']) {
                    if ($this->proLibFascicolo->SganciaDocumentoDaFascicolo($rowid)) {
                        Out::msgInfo("Cancellazione", "Documento sganciato correttamente.");
                        $this->Dettaglio($this->geskey, 'geskey');
                        return;
                    } else {
                        Out::msgStop("Attenzione", $this->proLibFascicolo->getErrMessage());
                        return;
                    }
                } else {
                    Out::msgStop("Attenzione", "Non hai il permesso di gestire i documenti del fascicolo.");
                    return;
                }
            } else {
                if (substr($rowid, 0, 4) == 'PRO-' && substr($rowid, 14, 2) == 'N') {
                    // @TODO Qui prevedere sgancio di sottofascicoli senza protocolli/documenti dentro. (Alle) [già presente su "SganciaSottofascicolo"]
                    if (!$this->proLibFascicolo->SganciaSottofascicolo($this, $rowid, $this->geskey)) {
                        Out::msgStop("Attenzione", $this->proLibFascicolo->getErrMessage());
                    } else {
                        $this->Dettaglio($this->geskey, 'geskey');
                    }
                } else {
                    Out::msgStop("Attenzione", "Questo documento non può essere annullato.");
                }
                return;
            }
        } else {
            if ($permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_PROTOCOLLI] || $retCheck['GESTIONE']) {
                if (!$this->proLibFascicolo->annullaDocumentoFascicolo($this, $this->geskey, $rowid_orgconn)) {
                    Out::msgStop("Attenzione", $this->proLibFascicolo->getErrMessage());
                }
            } else {
                Out::msgStop("Attenzione", "Non hai il permesso di gestire i protocolli del fascicolo.");
                return;
            }
        }
        $this->Dettaglio($this->geskey, 'geskey');
    }

    private function AggiungiAFascicolo() {
        $chiave = $_POST['rowid'];
        $documento = $this->proDocumenti[$chiave];
        if ($documento[$_POST['colName']] == '') {
            return;
        }
        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
        if ($proges_rec['GESDCH']) {
            Out::msgInfo("Attenzione", "Fascicolo Chiuso. <br/>Operazione non applicabile.");
            return;
        }
        if (substr($chiave, 0, 4) == "DOC-" || (substr($chiave, 14, 1) != "F" && substr($chiave, 14, 1) != "N")) {
            return;
        }
        $pronumSottoFascicolo = '';
        if (substr($chiave, 14, 1) == 'N') {
            $pronumSottoFascicolo = substr($chiave, 4, 10);
        }

        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->currGesnum, 'gesnum', $pronumSottoFascicolo);
        $checkRes = $this->CheckPermessiDocumentoFascicolo($chiave);
        $this->varAppoggio = array();
        $this->varAppoggio['CHIAVERIGA_NUM'] = substr($chiave, 4, 10);
        $this->varAppoggio['CHIAVERIGA_TIPO'] = substr($chiave, 14, 2);
        $anaent_33 = $this->proLib->GetAnaent('33');

        $arrayQuestion = array();
        if ($permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI] || $checkRes[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI]) {
            /* In attesa della nuova funzione collegata al documentale. */
            //$arrayQuestion['Carica Repertorio Esterno'] = array('id' => $this->nameForm . '_AddRepertorioEst', 'model' => $this->nameForm);
        }

        // Controllo se attiva fascicolazione di documentale
        $ParametriVari = $this->segLib->GetParametriVari();
        if ($ParametriVari['SEG_ATTIVA_CLAFAS']) {
            $arrayQuestion['Documentale'] = array('id' => $this->nameForm . '_AddDocumentale', 'model' => $this->nameForm);
        }

        if ($permessiFascicolo[proLibFascicolo::PERMFASC_CREAZIONE]) {
            $arrayQuestion['Sottofascicolo'] = array('id' => $this->nameForm . '_AddSottofascicolo', 'model' => $this->nameForm);
        }
        if ($permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI] || $checkRes[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI]) {
            $arrayQuestion['Documento'] = array('id' => $this->nameForm . '_AddDocumento', 'model' => $this->nameForm);
        }
        if ($anaent_33['ENTDE6']) {
            if ($permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_PROTOCOLLI] || $checkRes[proLibFascicolo::PERMFASC_GESTIONE_PROTOCOLLI]) {
                $arrayQuestion['Protocollo'] = array('id' => $this->nameForm . '_AddProtocollo', 'model' => $this->nameForm);
            }
        }


        // Azione da non conteplare ora.
        if ($anaent_33['ENTDE5']) {
            $arrayQuestion['Azione'] = array('id' => $this->nameForm . '_AddAzione', 'model' => $this->nameForm);
        }
        if ($arrayQuestion) {
            Out::msgQuestion("Aggiungi Elemento", "Cosa vuoi aggiungere?", $arrayQuestion);
        } else {
            Out::msgStop("Attenzione", "Non hai il permesso di gestire elementi nel fascicolo.");
            return;
        }
    }

    private function GestisciAssegnazioni() {
        $chiave = $_POST['rowid'];
        $documento = $this->proDocumenti[$chiave];
        if ($documento[$_POST['colName']] == '') {
            return;
        }
        $pronum = substr($chiave, 4, 10);
        $Tipo = substr($chiave, 14, 2);
        $pronumSottoFascicolo = '';
        if ($Tipo == 'N') {
            $pronumSottoFascicolo = $pronum;
        }
        $UfficioGes = $_POST[$this->nameForm . '_PROGES']['GESPROUFF'];
        $anapro_fascicolo = $this->proLibFascicolo->getAnaproFascicolo($this->geskey);
        if ($anapro_fascicolo) {
            $modelDaAprire = 'proAssegnazioniFascicolo';
            itaLib::openForm($modelDaAprire);
            $modelAtto = itaModel::getInstance($modelDaAprire);
            $modelAtto->setEvent('openform');
            $modelAtto->setPronumFascicolo($anapro_fascicolo['PRONUM']);
            $modelAtto->setPronumSottoFascicolo($pronumSottoFascicolo);
            $modelAtto->setUfficioProgesUsato($UfficioGes);
            $modelAtto->setReturnModel($this->nameForm);
            $modelAtto->parseEvent();
            $modelAtto->Dettaglio();
        }
    }

    /**
     * 
     * @param type $orgnode_rec
     * @return boolean
     */
    private function addSottofascicoloSimple($gesnum, $orgnode_rec, $ReturnData) {
        //
        // CONTROLLO E CREO SOTTO FASCICOLO
        //
        $profilo = proSoggetto::getProfileFromIdUtente();
        $DatiSottoFascicolo = $ReturnData['PROPAS_REC'];
        $orgnodeRowid = $orgnode_rec['ROWID'];
        $descrizione = $DatiSottoFascicolo['PRODPA'];
        $proges_rec = $this->proLibPratica->GetProges($gesnum);
        $Ufficio = $ReturnData['UFFICIO'];
        $DatiSottoFascicolo = $this->returnData['PROPAS_REC'];
        if (!$Ufficio) {
            $uffdes_tab = $this->proLib->GetUffdes($profilo['COD_SOGGETTO']);
            if ($uffdes_tab) {
                $Ufficio = $uffdes_tab[0]['UFFCOD'];
            }
        }
        if (!$Ufficio) {
            Out::msgStop("Attenzione!", "Ufficio del soggetto creatore mancante.");
            return false;
        }

        $rowid_anapro_sottofascicolo = $this->proLibFascicolo->creaSottoFascicolo(
                $proges_rec['GESKEY'], $descrizione, array(
            'UFF' => $DatiSottoFascicolo['PROUFFRES'], // anche qui PROUFFRES?
            'RES' => $DatiSottoFascicolo['PRORPA'],
            'GESPROUFF' => $Ufficio// 
                )
        );
        if (!$rowid_anapro_sottofascicolo) {
            Out::msgStop("Attenzione!", $this->proLibFascicolo->getErrMessage());
            return false;
        }
        if (!$orgnodeRowid) {
            Out::msgStop("Attenzione!", "Riferimento al nodo Mancante");
            return false;
        }
        $anapro_sottofascicolo_rec = $this->proLib->GetAnapro($rowid_anapro_sottofascicolo, 'rowid');
        //
        // Registro azione e quindi la collego al contenitore padre
        // 
        $propas_rec = $DatiSottoFascicolo;
        $propas_rec['PRONUM'] = $gesnum; // da dove lo prende?
        $propas_rec['PROPRO'] = $proges_rec['GESPRO'];
        $propas_rec['PROINI'] = date('Ymd');
        $propas_rec['PROSEQ'] = 99999;
        $propas_rec['PROPAK'] = $this->proLibPratica->PropakGenerator($gesnum);
        $propas_rec['PROUTEADD'] = $propas_rec['PROUTEEDIT'] = $propas_rec['PASPROUTE'] = App::$utente->getKey('nomeUtente');
        $propas_rec['PRODATEADD'] = $propas_rec['PRODATEEDIT'] = date("Ymd");
        $propas_rec['PROORAADD'] = $propas_rec['PROORAEDIT'] = date("H:i:s");
        $propas_rec['PASPRO'] = $anapro_sottofascicolo_rec['PRONUM'];
        $propas_rec['PASPAR'] = $anapro_sottofascicolo_rec['PROPAR'];
        $insert_Info = 'Oggetto: Inserimento Sottofascicolo con chiave ' . $propas_rec['PROPAK'] . " e seq " . $propas_rec['PROSEQ'];
        if (!$this->insertRecord($this->PROT_DB, 'PROPAS', $propas_rec, $insert_Info)) {
            return false;
        }
        //
        // Collego al sotto fascicolo
        //
        $orgnode_rec = $this->proLib->GetOrgNode($orgnodeRowid, 'rowid');
        if (!$this->proLibFascicolo->insertDocumentoFascicolo($this, $proges_rec['GESKEY'], $anapro_sottofascicolo_rec['PRONUM'], $anapro_sottofascicolo_rec['PROPAR'], $orgnode_rec['PRONUM'], $orgnode_rec['PROPAR'])) {
            Out::msgStop("Aggiunta istanza Fallita", $this->proLibFascicolo->getErrMessage());
            return false;
        }
        //
        // Rinfresco i dati in visualizzazione
        //
        $this->proLibPratica->ordinaPassi($currGesnum);
        $this->proLibPratica->sincronizzaStato($currGesnum);
        return true;
    }

    /**
     * 
     * @param type $orgnode_rec
     * @return boolean
     */
    private function editSottofascicoloSimple($gesnum, $orgnode_rec, $ReturnData) {
        $profilo = proSoggetto::getProfileFromIdUtente();
        $orgnodeRowid = $orgnode_rec['ROWID'];
        $DatiSottoFascicolo = $ReturnData['PROPAS_REC'];
        $Ufficio = $ReturnData['UFFICIO'];
        if (!$Ufficio) {
            $uffdes_tab = $this->proLib->GetUffdes($profilo['COD_SOGGETTO']);
            if ($uffdes_tab) {
                $Ufficio = $uffdes_tab[0]['UFFCOD'];
            }
        }
        if (!$Ufficio) {
            Out::msgStop("Attenzione!", "Ufficio del soggetto che sta movimentando il sottofascicolo mancante.");
            return false;
        }

        if (!$DatiSottoFascicolo['ROWID']) {
            Out::msgStop("Attenzione!", "Riferimento a sottofascicolo Mancante.");
            return false;
        }
        if (!$orgnodeRowid) {
            Out::msgStop("Attenzione!", "Riferimento al nodo Mancante");
            return false;
        }
        // Lo prendo tramite propas
        $ProPasRec = $this->proLibPratica->GetPropas($DatiSottoFascicolo['ROWID'], 'rowid');
        $proges_rec = $this->proLibPratica->GetProges($gesnum);
        $anapro_rec = $this->proLib->GetAnapro($ProPasRec['PASPRO'], 'codice', $ProPasRec['PASPAR']);
        /*
         * Registro modifiche su PROPAS
         */
        $propas_rec = $DatiSottoFascicolo;
        $propas_rec['PROUTEEDIT'] = App::$utente->getKey('nomeUtente');
        $propas_rec['PRODATEEDIT'] = date("Ymd");
        $propas_rec['PROORAEDIT'] = date("H:i:s");

        $update_info = 'Oggetto: Aggiornamento Sottofascicolo con rowid ' . $propas_rec['ROWID'];
        if (!$this->updateRecord($this->PROT_DB, 'PROPAS', $propas_rec, $update_info)) {
            return false;
        }

        /*
         * Registro i Save di ANAPRO e ANAOGG
         */
        $motivo = 'Aggiornamento sottofascicolo ';
        App::requireModel('proProtocolla.class');
        $protObj = new proProtocolla();
        $protObj->registraSave($motivo, $anapro_rec['ROWID'], 'rowid');
        /*
         *  Salvo Anapro e Oggetto
         */
        $anapro_rec['PROUTE'] = App::$utente->getKey('nomeUtente');
        $anapro_rec['PROUOF'] = $Ufficio;
        $anapro_rec['PRORDA'] = date('Ymd');
        $anapro_rec['PROROR'] = date('H:i:s');
        $update_Info = 'Oggetto: Aggiornamento Anapro Pratica: ' . $anapro_rec['PRONUM'] . ' ' . $anapro_rec['PROPAR'];
        if (!$this->updateRecord($this->PROT_DB, 'ANAPRO', $anapro_rec, $update_Info)) {
            Out::msgStop("Errore in Aggionamento", "Aggiornamento procedimento ANAPRO Fallito.");
            return false;
        }
        $risultato = $protObj->saveOggetto($anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $DatiSottoFascicolo['PRODPA']);
        if (!$risultato) {
            ut::msgStop("Errore in Aggionamento", "Errore in salvataggio descrizione oggetto.");
            return false;
        }
        /*
         * Aggiorno il Responsabile
         */
        $this->proLibFascicolo->registraResponsabile(array('RES' => $DatiSottoFascicolo['PRORPA'], 'UFF' => $DatiSottoFascicolo['PROUFFRES']), $anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        $iter = proIter::getInstance($this->proLib, $anapro_rec);
        $iter->sincIterProtocollo();
        return true;
    }

    private function GestisciSottoFascicolo($rowidSottofascicolo = '') {
        // qui vedo che passare alla funzione
        $Ufficio = $_POST[$this->nameForm . '_PROGES']['GESPROUFF'];
        $anapro_fascicolo = $this->proLibFascicolo->getAnaproFascicolo($this->geskey);
        if ($anapro_fascicolo) {
            $modelDaAprire = 'proGestSottoFascicolo';
            itaLib::openForm($modelDaAprire);
            $modelAtto = itaModel::getInstance($modelDaAprire);
            $modelAtto->setEvent('openform');
            $modelAtto->setRowidPropas($rowidSottofascicolo);
            $modelAtto->setPronumFascicolo($anapro_fascicolo['PRONUM']);
            $modelAtto->setUfficioProgesUsato($Ufficio);
            $modelAtto->setReturnModel($this->nameForm);
            $modelAtto->setReturnEvent('returnGestSottoFascicolo');
            $modelAtto->parseEvent();
        }
    }

    private function GetPronumSottoFascicloSelezionato($rowid = '') {
        if (!$rowid) {
            $rowid = $_POST[$this->gridDocumenti]['gridParam']['selarrrow'];
        }
        $rowid_orgconn = $this->proDocumenti[$rowid]['ROWID_ORGCONN'];
        $pronumSottoFascicolo = '';

        if (trim(substr($rowid, 14, 2)) == 'N') {
            $pronumSottoFascicolo = substr($rowid, 4, 10);
        } else if (trim(substr($rowid, 14, 2)) == 'C' || trim(substr($rowid, 14, 2)) == 'A' || trim(substr($rowid, 14, 2)) == 'P') {
            if ($rowid_orgconn) {
                $orgconn_rec = $this->proLib->GetOrgConn($rowid_orgconn, 'rowid');
                if ($orgconn_rec['PROPARPARENT'] == 'N') {
                    $pronumSottoFascicolo = $orgconn_rec['PRONUMPARENT'];
                }
            }
        }
        return $pronumSottoFascicolo;
    }

    private function SelezionaAltroFascicolo($Anapro_rec, $SingoloFasc = false) {
        $anaorg_rec = $this->proLib->GetAnaorg($this->geskey, 'orgkey');
//        $anaorg_rec = $this->proLib->GetAnaorg($Anapro_rec['PROFASKEY'], 'orgkey');
        $orgccf = $anaorg_rec['ORGCCF'];
        $Titolario['PROCAT'] = substr($orgccf, 0, 4);
        $Titolario['CLACOD'] = substr($orgccf, 4, 4);
        $Titolario['FASCOD'] = substr($orgccf, 8, 4);
        $Titolario['VERSIONE_T'] = $anaorg_rec['VERSIONE_T'];

        $model = 'proSeleFascicolo';
        itaLib::openForm($model);
        /* @var $proSeleFascicolo proSeleFascicolo */
        $proSeleFascicolo = itaModel::getInstance($model);
        $proSeleFascicolo->setEvent('openform');
        $proSeleFascicolo->setReturnModel($this->nameForm);
        $proSeleFascicolo->setTitolario($Titolario);
        if ($SingoloFasc) {
            $proSeleFascicolo->setSingoloFascicolo($anaorg_rec['ROWID']);
        }
        $proSeleFascicolo->setReturnEvent('returnAlberoFascicolo');
        $proSeleFascicolo->parseEvent();
    }

    private function SpostaDocumento() {
        /*
         * Dove controlla se può movimentare sul secondo fascicolo...
         */

        $Anaorg_rec = $this->proLib->GetAnaorg($this->returnData['ROWID_ANAORG'], 'rowid');
        $ProGes_rec = $this->proLibPratica->GetProges($Anaorg_rec['ORGKEY'], 'geskey');
        $Anapro_rec_destino = $this->proLib->GetAnapro($this->returnData['ROWID_ANAPRO'], 'rowid');

        $pronumSottofascicolo = '';
        $resCheck = array();
        if ($Anapro_rec_destino['PROPAR'] == 'N') {
            $pronumSottofascicolo = $Anapro_rec_destino['PRONUM'];
            $resCheck = $this->proLibFascicolo->CheckPermessiDocumentoSottofascicolo($Anapro_rec_destino['PRONUM'], $Anapro_rec_destino['PROPAR'], $ProGes_rec['GESNUM']);
        }
        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $ProGes_rec['GESNUM'], 'gesnum', $pronumSottofascicolo);
        if (!$permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI] && (!$resCheck['GESTIONE'] || !$resCheck[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI])) {
            Out::msgStop("Attenzione", "Non hai i permessi di gestione nel fascicolo di destinazione.");
            return false;
        }

        // Ricerca sottofascicolo:
        $rowid_orgconn = $this->proDocumenti[$this->rowidDocumentoDaSpostare]['ROWID_ORGCONN'];
        $DocumentoDaSpostare = $this->proDocumenti[$this->rowidDocumentoDaSpostare];

        /*
         * 0 Controllo se il protocollo è già presente nel fascicolo indicato.
         *   Solo se il fascicolo di destino è diverso dal fascicolo di partenza.
         *   Altrimenti è uno spostamento interno.
         */
        if ($this->geskey != $Anaorg_rec['ORGKEY']) {
            if ($this->proLibFascicolo->CheckProtocolloInFascicolo($DocumentoDaSpostare['PRONUM'], $DocumentoDaSpostare['PROPAR'], $Anaorg_rec['ORGKEY'])) {
                Out::msgStop("Attenzione", "Protocollo già presente nel fascicolo selezionato.<br>Non è possibile procedere con lo spostamento.");
                return false;
            }
        }
        /*
         * 1 Sgancio prima il protocollo dal fascicolo
         */
        if (!$this->proLibFascicolo->annullaDocumentoFascicolo($this, $this->geskey, $rowid_orgconn)) {
            Out::msgStop("Attenzione", $this->proLibFascicolo->getErrMessage());
            return false;
        }
        /*
         * 2 Inserisco il fascicolo nel nuovo fascicolo
         */
        $orgnode_rec = $this->proLib->GetOrgNode($Anapro_rec_destino['PRONUM'], 'codice', $Anapro_rec_destino['PROPAR']);
        if (!$this->proLibFascicolo->insertDocumentoFascicolo(
                        $this
                        , $Anaorg_rec['ORGKEY']
                        , $DocumentoDaSpostare['PRONUM']
                        , $DocumentoDaSpostare['PROPAR']
                        , $orgnode_rec['PRONUM']
                        , $orgnode_rec['PROPAR']
                )
        ) {
            Out::msgStop("Attenzione", $this->proLibFascicolo->getErrMessage());
            return false;
        }

        $Destino = '';
        if ($Anapro_rec_destino['PROPAR'] == 'F') {
            $Destino = 'fascicolo ' . $Anapro_rec_destino['PROFASKEY'];
        } else {
            $Destino = 'sottofascicolo ' . $Anapro_rec_destino['PROSUBKEY'];
        }
        Out::msgInfo('Spostamento', '<div style="font-size:1.2em;">Protocollo spostato nel ' . $Destino . '</div>');

        return true;
    }

    public function GetCountAnaproSottofascicoli($GesKey) {
        $sql = "SELECT COUNT(ANAPRO.ROWID) AS SOTTOFASCICOLI 
                        FROM ANAPRO 
                    LEFT OUTER JOIN ORGCONN ON ANAPRO.PRONUM=ORGCONN.PRONUM AND ANAPRO.PROPAR=ORGCONN.PROPAR
                    WHERE ANAPRO.PROFASKEY='$GesKey' AND ANAPRO.PROPAR='N' AND ORGCONN.CONNDATAANN = '' ";
        return $this->proLib->getGenericTab($sql, false);
    }

    public function GetParamSegnaturaDoc($documento) {
        $paramSegnatura = array();
        // Se c'è protocollo, visualizzo anche la segnatura del protocollo.
        if ($documento['PRONUM'] && $documento['PROPAR']) {
            $Anapro_rec = $this->proLib->GetAnapro($documento['PRONUM'], 'codice', $documento['PROPAR']);
            $ParamSegn = $this->proLibAllegati->GetPosMarcaturaFromTipoProt();

            $endorserParams = $this->proLib->getScannerEndorserParams($Anapro_rec);
            $StringaSegnatura = $endorserParams['CAP_PRINTERSTRING'];
            $paramSegnaturaTop = array(
                'STRING' => $StringaSegnatura,
                'FIRSTPAGEONLY' => $ParamSegn['FIRST_PAGE'],
                'X-COORD' => $ParamSegn['X_COORD'],
                'Y-COORD' => $ParamSegn['Y_COORD'],
                'ROTATION' => $ParamSegn['ROTAZ']
            );
            $paramSegnatura[] = $paramSegnaturaTop;
        }
        // Marcautra del firmatario del documento:
        $FirmaStr = 'Riproduzione cartacea del documento informatico sottoscritto digitalmente da @{$PRAALLEGATI.FIRMATARIO}@';
        $paramSegnaturaBottom1 = array(
            'STRING' => $FirmaStr,
            'FIRSTPAGEONLY' => 1,
            'X-COORD' => 20,
            'Y-COORD' => 820,
            'ROTATION' => 0,
            'FONT-SIZE' => 8
        );
        $paramSegnatura[] = $paramSegnaturaBottom1;
        if (!$documento['PRONUM'] || !$documento['PROPAR']) {
            return $paramSegnatura;
        }
        return $paramSegnatura;
    }

    public function CreaSqlAllegati_tree($nodePronum, $nodePropar, $where_profilo, $where = array()) {

        $where_doc = $join_doc = '';
        if ($where['DOCUMENTI']) {
            $join_doc = ' LEFT OUTER JOIN ANADOC ANADOC ON ANAPRO.PRONUM = ANADOC.DOCNUM AND ANAPRO.PROPAR = ANADOC.DOCPAR ';
        }

        $sql = "
            SELECT
                ORGCONN.ROWID AS ROWID_ORGCONN,
                ORGCONN.PRONUM,
                ORGCONN.PROPAR,
                ORGCONN.CONNUTEINS,
                ORGCONN.CONNDATAINS,
                ORGCONN.CONNORAINS,
                ORGCONN.ORGKEY,
                ANAPRO.PRODAR,
                ANAPRO.PROORA,
                ANAPRO.PROSUBKEY,
                ANAPRO.PROFASKEY,
                ANAPRO.PRORISERVA,
                ANAPRO.PROTSO,
                ANAPRO.PRONOM,
                ANAPRO.PROSEG,
                ANAPRO.PROCODTIPODOC,
                ANAPRO.ROWID AS ROWID_ANAPRO,
                ANAOGG.OGGOGG
            FROM
                ORGCONN
            LEFT OUTER JOIN ANAPRO ANAPRO ON ANAPRO.PRONUM=ORGCONN.PRONUM AND ANAPRO.PROPAR=ORGCONN.PROPAR
            LEFT OUTER JOIN ARCITE ARCITE ON ARCITE.ITEPRO=ORGCONN.PRONUM AND ARCITE.ITEPAR=ORGCONN.PROPAR
            LEFT OUTER JOIN ANAORDNODE ANAORDNODE ON ORGCONN.PROPAR = ANAORDNODE.TIPONODE
            LEFT OUTER JOIN ANAOGG ANAOGG ON ANAPRO.PRONUM=ANAOGG.OGGNUM AND ANAPRO.PROPAR=ANAOGG.OGGPAR
            $join_doc
                    WHERE
                        ORGCONN.CONNDATAANN = '' AND 
                        ORGCONN.PRONUMPARENT='{$nodePronum}' AND ORGCONN.PROPARPARENT='{$nodePropar}' ";
        $sql .= " AND ( (1=1 $where_profilo) OR ( ORGCONN.PROPAR = 'N') ) ";
//        $sql.=" AND ( (1=1 $where_profilo) ) ";
        if ($where['PROTOCOLLI'] && $where['DOCUMENTI']) {
            $sql .= " AND ( ( 1=1 {$where['DOCUMENTI']} ) OR ( 1=1 {$where['PROTOCOLLI']} ) OR ANAPRO.PROPAR = 'F' OR ANAPRO.PROPAR='N' OR ANAPRO.PROPAR='T' )";
        } else {
            if ($where['PROTOCOLLI']) {
                $sql .= " AND (1=1 {$where['PROTOCOLLI']} OR ANAPRO.PROPAR = 'F' OR ANAPRO.PROPAR='N' OR ANAPRO.PROPAR='T')";
            }
            if ($where['DOCUMENTI']) {
                $sql .= " AND (1=1 {$where['DOCUMENTI']} OR ANAPRO.PROPAR = 'F' OR ANAPRO.PROPAR='N' OR ANAPRO.PROPAR='T' )";
            }
        }
        $sql .= " GROUP BY ORGCONN.PRONUM,ORGCONN.PROPAR";
        /*
         *  Ordinamento parametrico
         */
        $sql.=$this->Ordinamento;


//        $anaent_rec = $this->proLib->GetAnaent(55);
//        if ($anaent_rec['ENTDE1'] == "1") {
//            $sql .= " ORDER BY ANAORDNODE.SEQORD,ORGCONN.PRONUM,ORGCONN.PROPAR";
//        } else {
//            $sql .= " ORDER BY ANAORDNODE.SEQORD,ORGCONN.CONNSEQ";
//        }
        return $sql;
    }

    /* La seguente funzione controlla i permessi sui documenti che l'utente ha:
     * Utilizza $this->proDocumenti
     * 
     */

    public function CheckPermessiDocumentoFascicolo($rowidChiave) {
        App::log('check permessi ');
        App::log($rowidChiave);
        $pronum = substr($rowidChiave, 4, 10);
        $propar = substr($rowidChiave, 14, 2);
        $tipoChiave = substr($rowidChiave, 0, 4); // DOC-/
        $rowid_orgconn = $this->proDocumenti[$rowidChiave]['ROWID_ORGCONN'];

        /* Predispongo dati chiave */
        $paramChiave = array();
        $paramChiave['GESNUM'] = $this->currGesnum;
        $paramChiave['ROWIDCHIAVE'] = $rowidChiave;
        $paramChiave['ROWID_ORGCONN'] = $rowid_orgconn;
        $paramChiave['PRONUM'] = $pronum;
        $paramChiave['PROPAR'] = $propar;
        $paramChiave['TIPOCHIAVE'] = $tipoChiave;

        $pronumSottoFascicolo = $this->GetPronumSottoFascicloSelezionato($rowidChiave);
        $retChk = $this->proLibFascicolo->CheckDocumentoFascicolo($paramChiave, $pronumSottoFascicolo);
        return $retChk;
    }

    public function BloccaSerie() {
        Out::attributo($this->nameForm . '_SERIE[PROGSERIE]', "readonly", '0');
        Out::addClass($this->nameForm . '_SERIE[PROGSERIE]', "ita-readonly");
        Out::disableField($this->nameForm . '_SERIE[CODICE]');
        Out::show($this->nameForm . '_MOD_SERIE');
    }

    public function SbloccaSerie() {
        Out::attributo($this->nameForm . '_SERIE[PROGSERIE]', "readonly", '1');
        Out::delClass($this->nameForm . '_SERIE[PROGSERIE]', "ita-readonly");
        Out::enableField($this->nameForm . '_SERIE[CODICE]');
        Out::hide($this->nameForm . '_MOD_SERIE');
    }

    public function DecodificaSerie($codiceSerie, $anaorg_rec = array()) {
        Out::clearFields($this->nameForm . '_divSerie');
        $Serie_rec = $this->proLibSerie->GetSerie($codiceSerie, 'codice');
        if (!$Serie_rec) {
            Out::msgStop("Attenzione", "Serie non trovata.");
            return false;
        }
        // Decodifico le serie:
        Out::valori($Serie_rec, $this->nameForm . '_SERIE');
        if ($Serie_rec['TIPOPROGRESSIVO'] == 'MANUALE' && !$anaorg_rec['PROGSERIE']) {
            Out::attributo($this->nameForm . '_SERIE[PROGSERIE]', "readonly", '1');
            Out::delClass($this->nameForm . '_SERIE[PROGSERIE]', "ita-readonly");
        } else {
            Out::attributo($this->nameForm . '_SERIE[PROGSERIE]', "readonly", '0');
            Out::addClass($this->nameForm . '_SERIE[PROGSERIE]', "ita-readonly");
        }
        if ($anaorg_rec) {
            // Valorizzazione progressivo serie, se presente.
            Out::valore($this->nameForm . '_SERIE[PROGSERIE]', $anaorg_rec['PROGSERIE']);
        }
        if ($this->fl_manutenzioneSerie) {
            Out::attributo($this->nameForm . '_SERIE[PROGSERIE]', "readonly", '1');
            Out::delClass($this->nameForm . '_SERIE[PROGSERIE]', "ita-readonly");
        }

        // Qui controllare anaorg e se la serie è già inserita.
        // SERIE[PROGSERIE] readonly.
    }

    public function VisualizzaNascondiSerie($titolario, $versione = '*') {
        if ($versione == '*') {
            $versione = $this->proLib->GetTitolarioCorrente();
        }
        if (!$this->proLibSerie->CtrSerieObbligatoria($titolario, $versione)) {
            Out::hide($this->nameForm . '_divSerie');
        } else {
            Out::show($this->nameForm . '_divSerie');
        }
    }

    private function SelezionaFascicoloCollegato() {
        if ($this->geskey) {
            $anaorg_rec = $this->proLib->GetAnaorg($this->geskey, 'orgkey');
            $orgccf = $anaorg_rec['ORGCCF'];
            $Titolario['PROCAT'] = substr($orgccf, 0, 4);
            $Titolario['CLACOD'] = substr($orgccf, 4, 4);
            $Titolario['FASCOD'] = substr($orgccf, 8, 4);
            $Titolario['VERSIONE_T'] = $anaorg_rec['VERSIONE_T'];
        } else {
            // Prendo il primo titolario?..
            $titolario = $_POST[$this->nameForm . '_Catcod'] . $_POST[$this->nameForm . '_Clacod'] . $_POST[$this->nameForm . '_Fascod'];
            $versione_t = $this->proLib->GetTitolarioCorrente();
            if (!$titolario) {
                $titolario = '0433';
            }
            $Titolario['PROCAT'] = substr($titolario, 0, 4);
            $Titolario['CLACOD'] = substr($titolario, 4, 4);
            $Titolario['FASCOD'] = substr($titolario, 8, 4);
            $Titolario['VERSIONE_T'] = $versione_t;
        }

        $model = 'proSeleFascicolo';
        itaLib::openForm($model);
        /* @var $proSeleFascicolo proSeleFascicolo */
        $proSeleFascicolo = itaModel::getInstance($model);
        $proSeleFascicolo->setEvent('openform');
        $proSeleFascicolo->setAbilitaCreazione(false);
        $proSeleFascicolo->setReturnModel($this->nameForm);
        $proSeleFascicolo->setTitolario($Titolario);
        $proSeleFascicolo->setReturnEvent('returnFascicoloCollegato');
        $proSeleFascicolo->parseEvent();
    }

    public function caricaWhere() {
        $where = array(
            "PROTOCOLLI" => '',
            "DOCUMENTI" => ''
        );
        if ($_POST['_search'] == 'true') {
            if ($_POST['NOTE']) {
                $where['DOCUMENTI'] .= " AND ({$this->PROT_DB->strUpper('DOCNOT')}  LIKE '%" . strtoupper($_POST['NOTE']) . "%' )"; //OR {$this->PROT_DB->strUpper('DOCNAME')}  LIKE '%" . strtoupper($_POST['NOTE']) . "%')";
                $where['PROTOCOLLI'] = " AND ({$this->PROT_DB->strUpper('OGGOGG')}  LIKE '%" . strtoupper($_POST['NOTE']) . "%' )";
            }
            if ($_POST['NAME']) {
                $where['DOCUMENTI'] .= " AND {$this->PROT_DB->strUpper('DOCNAME')
                        } LIKE '%" . strtoupper($_POST['NAME']) . "%'";
            }
            if ($_POST['PROPAR']) {
                $where['PROTOCOLLI'] .= " AND ANAPRO.PROPAR='{$_POST['PROPAR']}'";
            }
            if ($_POST['NUMPROT']) {
                $numprot = str_pad($_POST['NUMPROT'], 6, "0", STR_PAD_LEFT);
                $where['PROTOCOLLI'] .= " AND " . $this->PROT_DB->subString('ANAPRO.PRONUM', 5, 6) . "='{$numprot}'";
            }
            if ($_POST['ANNOPROT']) {
                $where['PROTOCOLLI'] .= " AND " . $this->PROT_DB->subString('ANAPRO.PRONUM', 1, 4) . "='{$_POST['ANNOPROT']}'";
            }
            if ($_POST['DATAPROT']) {
                $Data = $_POST['DATAPROT'];
                // Data formattata
                if (strpos($Data, '/') !== false) {
                    list($gg, $mm, $aa) = explode($Data, "/");
                    $Data = $aa . $mm . $gg;
                }
                $where['PROTOCOLLI'] .= " AND ANAPRO.PRODAR ='$Data' ";
            }
        }

        return $where;
    }

    public function ProtocollaDocSelezionati($TipoDoc = 'P', $rowidProtocollo = '') {
        $ElencoRowidAnadoc = array();
        foreach ($this->proDocumenti as $Documento) {
            if ($Documento['DACOPIARE'] === true) {
                $ElencoRowidAnadoc[] = $Documento['ROWIDANADOC'];
            }
        }

        if (!$ElencoRowidAnadoc) {
            Out::msgInfo('Attenzione', 'Selezionare almeno un documento da protocollare.');
            return;
        }
        $AllegatiTab = $this->proLibAllegati->CopiaAllegatiAnadoc($ElencoRowidAnadoc);
        if (!$AllegatiTab) {
            Out::msgStop("Attenzione", $this->proLibAllegati->getErrMessage());
            return;
        }
        //Out::msgInfo('ArrayRowid', print_r($ElencoRowidAnadoc, true));
        $DatiProtocollo['TIPO_PROT'] = $TipoDoc;
        $DatiProtocollo['ALLEGATI'] = $AllegatiTab;
        $DatiProtocollo['OGGETTO'] = 'PROTOCOLLAZIONE DA FASCICOLO';
//        $DatiProtocollo['TITOLARIO'] = '00010004'; // Copio quello del fascicolo..
        if ($rowidProtocollo) {
            // Chiamo la funzione e chiudo la form:
            $this->proLib->ProtocollaWizard($DatiProtocollo, $rowidProtocollo);
            $this->close();
            Out::desktopTabSelect('proArri');
            return;
        } else {
            $this->proLib->ProtocollaWizard($DatiProtocollo);
            foreach ($this->proDocumenti as $chiave => $Documento) {
                if ($Documento['DACOPIARE'] === true) {
                    $this->proDocumenti[$chiave]['COPIADOC'] = ' ';
                    $this->proDocumenti[$chiave]['DACOPIARE'] = false;
                    TableView::setCellValue($this->gridDocumenti, $chiave, 'COPIADOC', ' ', '', '', 'true');
                }
            }
        }
    }

    public function StampaElencoDeiProtocolli() {
        $anapro_F_rec = $this->proLibFascicolo->getAnaproFascicolo($this->geskey);
        if (!$anapro_F_rec) {
            return false;
        }
        $orgnode_rec = $this->proLib->GetOrgNode($anapro_F_rec['PRONUM'], 'codice', $anapro_F_rec['PROPAR']);
        $utiEnte = new utiEnte();
        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
        $itaJR = new itaJasperReport();
        $sql = $this->CreaSqlDocumenti($orgnode_rec['PRONUM'], $orgnode_rec['PROPAR']);
        $sql = "SELECT * FROM ($sql) A WHERE (PROPAR = 'A' OR PROPAR = 'P' OR PROPAR = 'C' ) AND CONNDATAANN = '' ";
        $parameters = array(
            "Ente" => $ParametriEnte_rec['DENOMINAZIONE'],
            "Sql" => $sql,
            "Numero" => $orgnode_rec['ORGKEY'],
            "MittDestPrint" => "SI"
        );
        $itaJR->runSQLReportPDF($this->PROT_DB, 'proEleDoc', $parameters);
    }

    public function getOrdinamento() {
        $this->Ordinamento = '';
        $anaent_rec = $this->proLib->GetAnaent(55);
        if ($anaent_rec['ENTDE1'] == "1") {
            $this->Ordinamento = " ORDER BY ANAORDNODE.SEQORD,ORGCONN.PRONUM,ORGCONN.PROPAR";
        } else {
            $this->Ordinamento = " ORDER BY ANAORDNODE.SEQORD,ORGCONN.CONNSEQ";
        }
        /*
         * Se è stato scelto un ordinamento diverso dalla griglia:
         */
        $ordinamento = $_POST['sidx'];
        $sord = strtoupper($_POST['sord']);
        if ($ordinamento) {
            switch ($ordinamento) {
                case 'NUMPROT':
                case 'ANNOPROT':
                case 'PROPAR':
                    $this->Ordinamento = " ORDER BY ANAORDNODE.SEQORD, ORGCONN.PRONUM $sord,ORGCONN.PROPAR $sord  ";
                    break;
                case 'DATAPROT':
                    // $this->Ordinamento = " ORDER BY ANAORDNODE.SEQORD, ANAPRO.PRODAR $sord ";
                    $this->Ordinamento = " ORDER BY ANAORDNODE.SEQORD,  ANAPRO.PRODAR $sord,ORGCONN.PRONUM, ORGCONN.PROPAR ";
                    break;

                case'NOTE':
                    $this->Ordinamento = " ORDER BY ANAORDNODE.SEQORD,  ANAOGG.OGGOGG $sord,ORGCONN.PRONUM, ORGCONN.PROPAR ";
                    break;
            }
        }
    }

    public function CheckFascicoloConservato($Anapro_rec) {
        $proLibConservazione = new proLibConservazione();
        $ProConser_rec = $proLibConservazione->CheckProtocolloVersatoSospeso($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        if ($ProConser_rec) {
            // Visualizzo divConservaizone e Blocco Modifiche al protocollo.
            Out::show($this->nameForm . '_divConservazione');
            $Data = date("d/m/Y", strtotime($ProConser_rec['DATAVERSAMENTO']));
            $icona = "<span  class=\"ita-icon ita-icon-safe-24x24\" style = \"float:left;display:inline-block;margin-left:5px;\"></span>";
            $Conservazione = "<span style = \"padding:3px;display:inline-block;\"> PROTOCOLLO VERSATO IN<b> CONSERVAZIONE</b></span>";
            $Conservazione .= "<span style = \"margin-left:20px;\"> DATA VERSAMENTO<b>: $Data </b></span>";
            /*
             * Info conservazione controllato:
             */
            $DescCons = proLibConservazione::$ElencoDescrConserEsito[$ProConser_rec['ESITOCONSERVAZIONE']];
            $Conservazione .= "<span style = \"margin-left:20px;\"> ESITO CONSERVAZIONE<b>: $DescCons </b></span>";

            Out::css($this->nameForm . '_divConservazione', 'background-image', ' linear-gradient(to right, #fbec88 0%, #FFFFFF 100%)');

            Out::html($this->nameForm . '_divInfoConservazione', $icona . $Conservazione);
            //DISABILITARE ALCUNI CAMPI? (TIPO IL RIAPRI?           
        } else {
            // Nascondo comunque divConservazione.
            Out::hide($this->nameForm . '_divConservazione');
        }
        // Annullamento deve essere però permesso. (si può annullare in un secondo momento)
    }

}

?>
