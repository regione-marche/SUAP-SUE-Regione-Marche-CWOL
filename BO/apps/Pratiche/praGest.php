<?php

/**
 *
 * ANAGRAFICA FASCICOLI ELETTRONICI
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2015 Italsoft sRL
 * @license
 * @version    18.06.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibProtocolla.class.php';
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibCalendar.class.php';
include_once ITA_BASE_PATH . '/apps/Catasto/catRic.class.php';
include_once ITA_BASE_PATH . '/apps/Catasto/catLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPratica.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praImmobili.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praSoggetti.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praPerms.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRuolo.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praNote.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praGobidManager.class.php';
include_once ITA_BASE_PATH . '/apps/Anagrafe/anaRic.class.php';
include_once ITA_BASE_PATH . '/apps/Anagrafe/anaLib.class.php';
include_once ITA_BASE_PATH . '/apps/Commercio/wcoLib.class.php';
include_once ITA_BASE_PATH . '/apps/Commercio/wcoRic.class.php';
include_once ITA_BASE_PATH . '/apps/AlboPretorio/albRic.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIntegrazioni.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devRic.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Gafiere/gfmLib.class.php';
include_once ITA_BASE_PATH . '/apps/ZTL/ztlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Gafiere/gfmRic.class.php';
include_once ITA_BASE_PATH . '/apps/ZTL/ztlRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibGfm.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibZTL.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFunzionePassi.class.php';
include_once(ITA_LIB_PATH . '/zip/itaZip.class.php');
//include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
//include_once(ITA_LIB_PATH . '/QXml/QXml.class.php');
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibEstrazione.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praPasso.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praAssegnaPraticaSimple.php';
include_once ITA_BASE_PATH . '/apps/GeoUtils/geoAppFactory.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFormHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibRiservato.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibChiusuraMassiva.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibFascicoloArch.class.php';

function praGest() {
    $praGest = new praGest();
    $praGest->parseEvent();
    return;
}

class praGest extends itaModel {

    public $PRAM_DB;
    public $PROT_DB;
    public $COMUNI_DB;
    public $utiEnte;
    public $ITALWEB_DB;
    public $ITALWEB;
    public $ITW_DB;
    public $accLib;
    public $devLib;
    public $wcoLib;
    public $catLib;
    public $gfmLib;
    public $ztlLib;
    public $nameForm = "praGest";
    public $divGes = "praGest_divGestione";
    public $gridPassi = "praGest_gridPassi";
    public $gridPassiAssegnazione = "praGest_gridPassiAss";
    public $gridOneri = "praGest_gridOneri";
    public $gridPagamenti = "praGest_gridPagamenti";
    public $gridAllegati = "praGest_gridAllegati";
    public $gridAllegatiSha2 = "praGest_gridAllegatiSha2";
    public $gridDatiPratica = "praGest_gridDatiPratica";
    public $gridComunicazioni = "praGest_gridComunicazioni";
    public $gridImmobili = "praGest_gridImmobili";
    public $gridSoggetti = "praGest_gridSoggetti";
    public $gridNote = "praGest_gridNote";
    public $gridUnitaLocale = "praGest_gridUnitaLocale";
    public $gridAccorpati = "praGest_gridAccorpati";
    public $praPassi = array();
    public $praPassiAssegnazione = array();
    public $praPassiPerAllegati = array();
    public $praAlle = array();
    public $praPagamenti = array();
    public $praAlleSha2 = array();
    public $praDati = array();
    public $praDatiPratica = array();
    public $praComunicazioni = array();
    public $datiFiltrati = array();
    public $praLib;
    public $praLibAllegati;
    public $praLibPratica;
    public $proLib;
    public $proRic;
    public $proLibSerie;
    public $praPerms;
    public $currGesnum;
    public $rowidAppoggio;
    public $dataRegAppoggio;
    public $currAllegato;
    public $workDate;
    public $workYear;
    public $returnModel;
    public $returnId;
    public $page;
    public $insertTo;
    public $pranumSel;
    public $idCorrispondente;
    public $datiRubricaWS = array();
    public $eqAudit;
    public $praImmobili;
    public $praSoggetti;
    public $praUnitaLocale;
    public $progesSel;
    public $datiFromWSProtocollo;
    public $codiceImm;
    /* @var $this->noteManager proNoteManager */
    public $noteManager;
    public $praReadOnly;
    public $sha2View;
    public $proctipaut_rec = array();
    public $rowidFiera;
    //public $rowidBandoFiera;
    //public $rowidBandoMercato;
    public $flagAssegnazioni;
    public $flagPagamenti;
    public $profilo;
    public $remoteToken;
    public $allegatiPrtSel;
    public $openMode;
    public $openRowid;
    public $anadesProt = array();
    public $soggComm = array();
    public $soggFiere = array();
    public $ita_ext_cred = array();
    public $praLibEstrazione;
    public $Propas;
    public $Propas_key;
    public $praAccorpati;
    public $praCompDatiAggiuntiviFormname;
    public $searchMode = false;
    public $praPassiSel;
    public $daTrasmissioni;
    private $praLibChiusuraMassiva;

    function __construct() {
        parent::__construct();
    }

    function postInstance() {
        parent::postInstance();

        $this->origForm = $this->nameFormOrig;
        $this->nameModel = substr($this->nameForm, strpos($this->nameForm, '_') + 1);
        $this->divGes = $this->nameForm . "_divGestione";
        $this->gridPassi = $this->nameForm . "_gridPassi";
        $this->gridPassiAssegnazione = $this->nameForm . "_gridPassiAss";
        $this->gridOneri = $this->nameForm . "_gridOneri";
        $this->gridPagamenti = $this->nameForm . "_gridPagamenti";
        $this->gridAllegati = $this->nameForm . "_gridAllegati";
        $this->gridAllegatiSha2 = $this->nameForm . "_gridAllegatiSha2";
        $this->gridDatiPratica = $this->nameForm . "_gridDatiPratica";
        $this->gridComunicazioni = $this->nameForm . "_gridComunicazioni";
        $this->gridImmobili = $this->nameForm . "_gridImmobili";
        $this->gridSoggetti = $this->nameForm . "_gridSoggetti";
        $this->gridNote = $this->nameForm . "_gridNote";
        $this->gridUnitaLocale = $this->nameForm . "_gridUnitaLocale";
        $this->gridAccorpati = $this->nameForm . "_gridAccorpati";

        try {
            $this->utiEnte = new utiEnte();
            $this->praLib = new praLib();
            $this->proLib = new proLib();
            $this->praLibAllegati = new praLibAllegati();
            $this->praLibPratica = praLibPratica::getInstance();
            $this->praPerms = new praPerms();
            $this->accLib = new accLib();
            $this->devLib = new devLib();
            $this->wcoLib = new wcoLib();
            $this->catLib = new catLib();
            $this->gfmLib = new gfmLib();
            $this->ztlLib = new ztlLib();
            $this->eqAudit = new eqAudit();
            $this->praLibEstrazione = new praLibEstrazione();
            $this->praLibChiusuraMassiva = new praLibChiusuraMassiva();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->ITALWEB = $this->accLib->getITALWEB();
            $this->ITALWEB_DB = $this->utiEnte->getITALWEB_DB();
            $this->ITW_DB = $this->accLib->getITW();
            $Filent_Rec_TabAss = $this->praLib->GetFilent(20);
            if ($Filent_Rec_TabAss['FILVAL'] == 1) {
                $this->flagAssegnazioni = true;
            }
            if ($Filent_Rec_TabAss['FILDE1'] == 1) {
                $this->flagPagamenti = true;
            }
            $this->profilo = proSoggetto::getProfileFromIdUtente();
            $this->praLibFascicoloArch = new praLibFascicoloArch();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        try {
            $this->COMUNI_DB = ItaDB::DBOpen('COMUNI', false);
        } catch (Exception $e) {
            App::log($e->getMessage());
        }
        $this->proRic = new proRic();
        $this->proLibSerie = new proLibSerie();
        $this->praPassi = App::$utente->getKey($this->nameForm . '_praPassi');
        $this->praPagamenti = App::$utente->getKey($this->nameForm . '_praPagamenti');
        $this->praPassiAssegnazione = App::$utente->getKey($this->nameForm . '_praPassiAssegnazione');
        $this->praPassiPerAllegati = App::$utente->getKey($this->nameForm . '_praPassiPerAllegati');
        $this->praAlle = App::$utente->getKey($this->nameForm . '_praAlle');
        $this->praAlleSha2 = App::$utente->getKey($this->nameForm . '_praAlleSha2');
        $this->currGesnum = App::$utente->getKey($this->nameForm . '_currGesnum');
        $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
        $this->dataRegAppoggio = App::$utente->getKey($this->nameForm . '_dataRegAppoggio');
        $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
        $this->returnId = App::$utente->getKey($this->nameForm . '_returnId');
        $this->page = App::$utente->getKey($this->nameForm . '_page');
        $this->insertTo = App::$utente->getKey($this->nameForm . '_insertTo');
        $this->praDati = App::$utente->getKey($this->nameForm . '_praDati');
        $this->praDatiPratica = App::$utente->getKey($this->nameForm . '_praDatiPratica');
        $this->datiFiltrati = App::$utente->getKey($this->nameForm . '_datiFiltrati');
        $this->praComunicazioni = App::$utente->getKey($this->nameForm . '_praComunicazioni');
        $this->currAllegato = App::$utente->getKey($this->nameForm . '_currAllegato');
        $this->pranumSel = App::$utente->getKey($this->nameForm . '_pranumSel');
        $this->idCorrispondente = App::$utente->getKey($this->nameForm . '_idCorrispondente');
        $this->datiRubricaWS = App::$utente->getKey($this->nameForm . '_datiRubricaWS');
        $this->praImmobili = unserialize(App::$utente->getKey($this->nameForm . '_praImmobili'));
        $this->praSoggetti = unserialize(App::$utente->getKey($this->nameForm . '_praSoggetti'));
        $this->praUnitaLocale = unserialize(App::$utente->getKey($this->nameForm . '_praUnitaLocale'));
        $this->progesSel = App::$utente->getKey($this->nameForm . '_progesSel');
        $this->datiFromWSProtocollo = App::$utente->getKey($this->nameForm . '_datiFromWSProtocollo');
        $this->codiceImm = App::$utente->getKey($this->nameForm . '_codiceImm');
        $this->praReadOnly = App::$utente->getKey($this->nameForm . '_praReadOnly');
        $this->sha2View = App::$utente->getKey($this->nameForm . '_sha2View');
        $this->proctipaut_rec = App::$utente->getKey($this->nameForm . '_proctipaut_rec');
        $this->rowidFiera = App::$utente->getKey($this->nameForm . '_rowidFiera');
        $this->remoteToken = App::$utente->getKey($this->nameForm . '_remoteToken');
        $this->allegatiPrtSel = App::$utente->getKey($this->nameForm . '_allegatiPrtSel');
        $this->anadesProt = App::$utente->getKey($this->nameForm . '_anadesProt');
        $this->soggComm = App::$utente->getKey($this->nameForm . '_soggComm');
        $this->soggFiere = App::$utente->getKey($this->nameForm . '_soggFiere');
        $this->Propas = App::$utente->getKey($this->nameForm . '_Propas');
        $this->Propas_key = App::$utente->getKey($this->nameForm . '_Propas_key');
        $data = App::$utente->getKey('DataLavoro');
        $this->noteManager = unserialize(App::$utente->getKey($this->nameForm . '_noteManager'));
        $this->ita_ext_cred = unserialize(App::$utente->getKey($this->nameForm . '_ita_ext_cred'));
        $this->praAccorpati = App::$utente->getKey($this->nameForm . '_praAccorpati');
        $this->praCompDatiAggiuntiviFormname = App::$utente->getKey($this->nameForm . '_praCompDatiAggiuntiviFormname');
        $this->searchMode = App::$utente->getKey($this->nameForm . '_searchMode');
        $this->praPassiSel = App::$utente->getKey($this->nameForm . '_praPassiSel');
        $this->daTrasmissioni = App::$utente->getKey($this->nameForm . '_daTrasmissioni');

        /*
         * Riassegnate per poter supportare model con Alias (Dialog di ricerca)
         */
        $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
        $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');


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
            App::$utente->setKey($this->nameForm . '_praPassi', $this->praPassi);
            App::$utente->setKey($this->nameForm . '_praPagamenti', $this->praPagamenti);
            App::$utente->setKey($this->nameForm . '_praPassiAssegnazione', $this->praPassiAssegnazione);
            App::$utente->setKey($this->nameForm . '_praPassiPerAllegati', $this->praPassiPerAllegati);
            App::$utente->setKey($this->nameForm . '_praAlle', $this->praAlle);
            App::$utente->setKey($this->nameForm . '_praAlleSha2', $this->praAlleSha2);
            App::$utente->setKey($this->nameForm . '_currGesnum', $this->currGesnum);
            App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
            App::$utente->setKey($this->nameForm . '_dataRegAppoggio', $this->dataRegAppoggio);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnId', $this->returnId);
            App::$utente->setKey($this->nameForm . '_insertTo', $this->insertTo);
            App::$utente->setKey($this->nameForm . '_praDati', $this->praDati);
            App::$utente->setKey($this->nameForm . '_praDatiPratica', $this->praDatiPratica);
            App::$utente->setKey($this->nameForm . '_datiFiltrati', $this->datiFiltrati);
            App::$utente->setKey($this->nameForm . '_praComunicazioni', $this->praComunicazioni);
            App::$utente->setKey($this->nameForm . '_currAllegato', $this->currAllegato);
            App::$utente->setKey($this->nameForm . '_pranumSel', $this->pranumSel);
            App::$utente->setKey($this->nameForm . '_idCorrispondente', $this->idCorrispondente);
            App::$utente->setKey($this->nameForm . '_datiRubricaWS', $this->datiRubricaWS);
            App::$utente->setKey($this->nameForm . '_praImmobili', serialize($this->praImmobili));
            App::$utente->setKey($this->nameForm . '_praSoggetti', serialize($this->praSoggetti));
            App::$utente->setKey($this->nameForm . '_praUnitaLocale', serialize($this->praUnitaLocale));
            App::$utente->setKey($this->nameForm . '_progesSel', $this->progesSel);
            App::$utente->setKey($this->nameForm . '_datiFromWSProtocollo', $this->datiFromWSProtocollo);
            App::$utente->setKey($this->nameForm . '_codiceImm', $this->codiceImm);
            App::$utente->setKey($this->nameForm . '_noteManager', serialize($this->noteManager));
            App::$utente->setKey($this->nameForm . '_praReadOnly', $this->praReadOnly);
            App::$utente->setKey($this->nameForm . '_sha2View', $this->sha2View);
            App::$utente->setKey($this->nameForm . '_proctipaut_rec', $this->proctipaut_rec);
            App::$utente->setKey($this->nameForm . '_rowidFiera', $this->rowidFiera);
            App::$utente->setKey($this->nameForm . '_remoteToken', $this->remoteToken);
            App::$utente->setKey($this->nameForm . '_allegatiPrtSel', $this->allegatiPrtSel);
            App::$utente->setKey($this->nameForm . '_anadesProt', $this->anadesProt);
            App::$utente->setKey($this->nameForm . '_soggComm', $this->soggComm);
            App::$utente->setKey($this->nameForm . '_soggFiere', $this->soggFiere);
            App::$utente->setKey($this->nameForm . '_Propas', $this->Propas);
            App::$utente->setKey($this->nameForm . '_Propas_key', $this->Propas_key);
            App::$utente->setKey($this->nameForm . '_praAccorpati', $this->praAccorpati);
            App::$utente->setKey($this->nameForm . '_ita_ext_cred', serialize($this->ita_ext_cred));
            App::$utente->setKey($this->nameForm . '_praCompDatiAggiuntiviFormname', $this->praCompDatiAggiuntiviFormname);
            App::$utente->setKey($this->nameForm . '_searchMode', $this->searchMode);
            App::$utente->setKey($this->nameForm . '_praPassiSel', $this->praPassiSel);
            App::$utente->setKey($this->nameForm . '_daTrasmissioni', $this->daTrasmissioni);
        }
    }

    public function getOpenMode() {
        return $this->openMode;
    }

    public function setOpenMode($openMode) {
        $this->openMode = $openMode;
    }

    public function getOpenRowid() {
        return $this->openRowid;
    }

    public function setOpenRowid($openRowid) {
        $this->openRowid = $openRowid;
    }

    public function setModelParam($params) {
        $params = unserialize($params);
        foreach ($params as $func => $args) {
            call_user_func_array(array($this->nameForm, $func), $args);
        }
    }

    public function setRowidFiera($rowidFiera) {
        $this->rowidFiera = $rowidFiera;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->inizializzaForm();

                if ($this->getOpenMode() == 'edit' && $this->getOpenRowid()) {
                    $this->dettaglio($this->getOpenRowid());
                    break;
                }

                if ($_POST['DaProtocollo'] == 'true') {
                    $datiProt = $_POST['DatiProt'];
                    $this->caricaDaProtocollo($datiProt);
                } else if ($_POST['rowidDettaglio']) {
                    $this->praReadOnly = $_POST['praReadonly'];
                    $this->Dettaglio($_POST['rowidDettaglio']);
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridPassiAssegnazione:
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $isSuperUser = $this->praPerms->checkSuperUser($proges_rec);
                        $rowidPasso = $_POST['rowid'];
                        $propas_rec = $this->praLib->GetPropas($rowidPasso, "rowid");
                        if (!$isSuperUser) {
                            $profilo = proSoggetto::getProfileFromIdUtente();
                            $funzioniAssegnazione = praFunzionePassi::getFunzioniAssegnazione($propas_rec['PRONUM'], $propas_rec['ROWID'], $profilo);
                            if (!$funzioniAssegnazione['RIAPRI']) {
                                break;
                            }
//
                            $model = 'praAssegnaPraticaSimple';
                            itaLib::openForm($model);
                            /* @var $modelObj praAssegnaPraticaSimple */
                            $modelObj = itaModel::getInstance($model);
                            $modelObj->setDaPortlet(true);
                            $modelObj->setPratica($propas_rec['PRONUM']);
                            $modelObj->setRowidAppoggio($rowidPasso);
                            $modelObj->setReturnModel($this->nameForm);
                            $modelObj->setReturnEvent('returnPraAssegnaPratica');
                            $modelObj->setEvent('openform');
                            $modelObj->parseEvent();
                            $modelObj->annullaInCarico();
                        } else {
                            $model = 'praGestAssegnazione';
                            itaLib::openForm($model);
                            /* @var $modelObj praGestAssegnazione */
                            $modelObj = itaModel::getInstance($model);
                            $modelObj->setReturnModel($this->nameForm);
                            $modelObj->setReturnEvent('returnPraGestAssegnazione');
                            $modelObj->setRowidPasso($rowidPasso);
                            $modelObj->setEvent('openform');
                            $modelObj->parseEvent();
                        }
                        break;
                    case $this->gridPassi:
                        $rigaSel = $_POST[$this->gridPassi]['gridParam']['selrow'];
                        $model = 'praPasso';
                        $rowid = $_POST['rowid'];
                        $propas_rec = $this->praLib->GetPropas($rowid, "rowid");
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $rowid;
                        $_POST['modo'] = "edit";
                        //$_POST['perms'] = $this->perms;
                        $_POST['pagina'] = $this->page;
                        $_POST['praReadonly'] = $this->praReadOnly;
                        $_POST['daTrasmissioni'] = $this->daTrasmissioni;
                        $_POST['selRow'] = $rigaSel;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnProPasso';
                        $_POST[$model . '_title'] = "Gestione Passo seq. " . $propas_rec['PROSEQ'] . " del Fascicolo " . substr($this->currGesnum, 4) . "/" . substr($this->currGesnum, 0, 4);
                        $_POST['passi'] = $this->praPassi;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->gridAllegatiSha2:
                        if (array_key_exists($_POST['rowid'], $this->praAlleSha2) == true) {
                            $name = strip_tags($this->praAlleSha2[$_POST['rowid']]['NAME']);
                            Out::openDocument(
                                    utiDownload::getUrl(
                                            $name, $this->praAlleSha2[$_POST['rowid']]['FILEPATH']
                                    )
                            );
                        }
                        break;
                    case $this->gridAllegati:
                        if (array_key_exists($_POST['rowid'], $this->praAlle) == true) {
                            $doc = $this->praAlle[$_POST['rowid']];
                            $name = strip_tags($doc['NAME']);
                            $file = $doc['FILEPATH'];
                            if ($doc['FILEINFO'] == "TESTOBASE") {
                                $pramPath = pathinfo($doc['FILEPATH'], PATHINFO_DIRNAME);
                                if (file_exists($pramPath . "/" . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . ".pdf")) {
                                    $name = pathinfo($doc['FILEORIG'], PATHINFO_FILENAME) . ".pdf";
                                    $file = $pramPath . "/" . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . ".pdf";
                                }
                                if (file_exists($pramPath . "/" . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . ".pdf.p7m")) {
                                    $name = pathinfo($doc['FILEORIG'], PATHINFO_FILENAME) . ".pdf.p7m";
                                    $file = $pramPath . "/" . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . ".pdf.p7m";
                                }
                            }
                            Out::openDocument(
                                    utiDownload::getUrl(
                                            $name, $file
                                    )
                            );
                        }
                        break;
                    case $this->gridComunicazioni:
                        if (substr($_POST['rowid'], 0, 10) != $this->currGesnum) {
                            $Pracom_rec = $this->praLib->GetPracom($_POST['rowid'], 'rowid');
                            $Propas_Rec = $this->praLib->GetPropas($Pracom_rec['COMPAK'], 'propak');
                            $model = 'praPasso';
                            $_POST = array();
                            $_POST['event'] = 'openform';
                            $_POST['rowid'] = $Propas_Rec['ROWID'];
                            $_POST['modo'] = "edit";
                            $_POST['daComunicazione'] = true;
                            $_POST['praReadonly'] = $this->praReadOnly;
                            //$_POST['perms'] = $this->perms;
                            $_POST[$model . '_returnModel'] = $this->nameForm;
                            $_POST[$model . '_returnMethod'] = 'returnProPasso';
                            $_POST[$model . '_title'] = "Gestione Passo seq. " . $Propas_Rec['PROSEQ'] . " del Fascicolo " . substr($this->currGesnum, 4) . "/" . substr($this->currGesnum, 0, 4);
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                        }
                        break;
                    case $this->gridImmobili:
                        $this->OpenImmobile("Dettaglio");
                        break;
                    case $this->gridUnitaLocale:
                        $rowid = $_POST['rowid'];
                        $_POST = array();
                        $model = 'praSoggettiGest';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnUnitaLocale';
                        $_POST['event'] = 'openform';
                        $_POST['perms'] = $this->perms;
                        $_POST['soggetto'] = $this->praUnitaLocale->GetSoggetto($rowid);
                        $_POST['rowid'] = $rowid;
                        $_POST['unitaLocale'] = true;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->gridSoggetti:
                        $rowid = $_POST['rowid'];
                        $_POST = array();
                        $model = 'praSoggettiGest';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnSoggetti';
                        $_POST['event'] = 'openform';
                        $_POST['perms'] = $this->perms;
                        $_POST['soggetto'] = $this->praSoggetti->GetSoggetto($rowid);
                        $_POST['rowid'] = $rowid;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->gridNote:
                        if ($this->praReadOnly == true) {
                            break;
                        }
                        $dati = $this->noteManager->getNota($_POST['rowid']);
                        if ($dati['UTELOG'] != App::$utente->getKey('nomeUtente')) {
                            Out::msgStop("Attenzione!", "Solo l'utente " . $dati['UTELOG'] . " è abilitato alla modifica della Nota.");
                            break;
                        }
                        $destinatari = array();
                        $propas_tab = $this->praLib->getGenericTab("SELECT DISTINCT(PRORPA) FROM PROPAS WHERE PRONUM ='$this->currGesnum' AND PRORPA<>''", true);
                        foreach ($propas_tab as $propas_rec) {
                            $destinatari[] = $this->praLib->getGenericTab("SELECT " . $this->PRAM_DB->strConcat("NOMCOG", "' '", "NOMNOM") . " AS DESTINATARIO, NOMRES FROM ANANOM WHERE NOMRES='{$propas_rec['PRORPA']}'", false);
                        }
                        $model = 'praDettNote';
                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnPraDettNote');
                        $formObj->setReturnId('');
                        $rowid = $_POST['rowid'];
                        $_POST = array();
                        if ($this->visualizzazione) {
                            $_POST['readonly'] = true;
                        }
                        $_POST['dati'] = $dati;
                        $_POST['rowid'] = $rowid;
                        $_POST['destinatari'] = $destinatari;
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;

                    case $this->gridOneri:
                        $model = "praGestOneri";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura model fallita");
                            break;
                        }
                        $formObj->setRowid($_POST['rowid']);
                        $formObj->setNumeroPratica($this->currGesnum);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnGrigliePagamenti');
                        $formObj->setReturnId('');
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;

                    case $this->gridPagamenti:
                        $model = "praGestConciliazioni";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura model fallita");
                            break;
                        }
                        $formObj->setRowid($_POST['rowid']);
                        $formObj->setNumeroPratica($this->currGesnum);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnGrigliePagamenti');
                        $formObj->setReturnId('');
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        $acq_model = 'utiAcqrMen';
                        Out::closeDialog($acq_model);
                        $_POST = array();
                        $_POST[$acq_model . '_flagPDFA'] = $this->praLib->getFlagPDFA();
                        $_POST[$acq_model . '_returnModel'] = $this->nameForm;
                        $_POST[$acq_model . '_returnField'] = $this->gridAllegati;
                        $_POST[$acq_model . '_returnMethod'] = 'returnAcqrList';
                        $_POST[$acq_model . '_title'] = 'Allegati al fascicolo elettronico';
                        $_POST[$acq_model . '_tipoNome'] = 'original';
                        $_POST['event'] = 'openform';
                        itaLib::openForm($acq_model);
                        $appRoute = App::getPath('appRoute.' . substr($acq_model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $acq_model . '.php';
                        $acq_model();
                        break;
                    case $this->gridAllegatiSha2:
                        $acq_model = 'utiAcqrMen';
                        Out::closeDialog($acq_model);
                        $_POST = array();
                        $_POST[$acq_model . '_flagPDFA'] = $this->praLib->getFlagPDFA();
                        $_POST[$acq_model . '_returnModel'] = $this->nameForm;
                        $_POST[$acq_model . '_returnField'] = $this->gridAllegatiSha2;
                        $_POST[$acq_model . '_returnMethod'] = 'returnAcqrList';
                        $_POST[$acq_model . '_title'] = 'Allegati al fascicolo elettronico';
                        $_POST[$acq_model . '_tipoNome'] = 'original';
                        $_POST['event'] = 'openform';
                        itaLib::openForm($acq_model);
                        $appRoute = App::getPath('appRoute.' . substr($acq_model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $acq_model . '.php';
                        $acq_model();
                        break;
                    case $this->gridPassi:
                        $model = 'praPasso';
                        $rowid = $_POST['rowid'];
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['procedimento'] = $this->currGesnum;
                        $_POST['modo'] = "add";
                        //$_POST['perms'] = $this->perms;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnProPasso';
                        $_POST[$model . '_title'] = "Inserimento Nuovo Passo nel Fascicolo " . substr($this->currGesnum, 4) . "/" . substr($this->currGesnum, 0, 4);
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->gridDatiPratica:
                        praRic::praRicPraidc($this->nameForm, 'returnPraidc');
                        break;
                    case $this->gridImmobili:
                        $this->OpenImmobile();
                        break;
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
                    case $this->gridUnitaLocale:
                        $_POST = array();
                        $model = 'praSoggettiGest';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnUnitaLocale';
                        $_POST['event'] = 'openform';
                        $_POST['soggetti'] = $this->praUnitaLocale->soggetti;
                        $_POST['pratica'] = $this->currGesnum;
                        $_POST['unitaLocale'] = true;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->gridNote:
                        $destinatari = array();
                        $propas_tab = $this->praLib->getGenericTab("SELECT DISTINCT(PRORPA) FROM PROPAS WHERE PRONUM ='$this->currGesnum' AND PRORPA<>''", true);
                        foreach ($propas_tab as $propas_rec) {
                            $destinatari[] = $this->praLib->getGenericTab("SELECT " . $this->PRAM_DB->strConcat("NOMCOG", "' '", "NOMNOM") . " AS DESTINATARIO, NOMRES FROM ANANOM WHERE NOMRES='{$propas_rec['PRORPA']}'", false);
                        }
                        $model = 'praDettNote';
                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnPraDettNote');
                        $formObj->setReturnId('');
                        $_POST = array();
                        $_POST['destinatari'] = $destinatari;
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;

                    case $this->gridOneri:
                        $model = "praGestOneri";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura model fallita");
                            break;
                        }
                        $formObj->setNumeroPratica($this->currGesnum);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnGrigliePagamenti');
                        $formObj->setReturnId('');
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridPassiAssegnazione:
                        $propas_rec = $this->praLib->GetPropas($_POST['rowid'], 'rowid');
                        if ($propas_rec) {
                            Out::msgQuestion("ATTENZIONE!", $propas_rec['PRODPA'] . "<br><br><br>Confermi la cancellazione dell'assegnazione?", array(
                                'Annulla' => array('id' => $this->nameForm . '_AnnullaCancAssegnazione', 'model' => $this->nameForm),
                                'Conferma' => array('id' => $this->nameForm . '_ConfermaCancAssegnazione', 'model' => $this->nameForm)
                                    )
                            );
                        }
                        $this->rowidAppoggio = $_POST['rowid'];
                        break;
                    case $this->gridAllegatiSha2:
                        $alle = $this->praAlleSha2[$_POST['rowid']];
                        $pasdoc_rec = $this->praLib->GetPasdoc($alle['ROWID'], "ROWID");
                        if ($pasdoc_rec['PASPRTCLASS'] == "PROGES" && $pasdoc_rec['PASPRTROWID'] != 0) {
                            Out::msgInfo("Cancellazione Allegati", "Impossibile cancellare l'allegato perchè risulta protocollato");
                            break;
                        }
                        if ($alle['PASLOCK'] == 1) {
                            Out::msgInfo("Cancellazione Alleagti", "Impossibile cancellare l'allegato perche risulta bloccato");
                            break;
                        }
                        if (strlen($alle['PASKEY']) > 10) {
                            Out::msgInfo("IMPOSSIBILE CANCELLARE QUESTO ALLEGATO", "Per cancellare l'allegato entrare nella gestione del passo");
                        } else {
                            Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare l'allegato?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancAlle', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancAlleSha', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        $this->rowidAppoggio = $_POST['rowid'];
                        break;
                    case $this->gridAllegati:
                        $alle = $this->praAlle[$_POST['rowid']];
                        $pasdoc_rec = $this->praLib->GetPasdoc($alle['ROWID'], "ROWID");
                        if ($pasdoc_rec['PASPRTCLASS'] == "PROGES" && $pasdoc_rec['PASPRTROWID'] != 0) {
                            Out::msgInfo("Cancellazione Allegati", "Impossibile cancellare l'allegato perchè risulta protocollato");
                            break;
                        }
                        if ($alle['PASLOCK'] == 1) {
                            Out::msgInfo("Cancellazione Alleagti", "Impossibile cancellare l'allegato perche risulta bloccato");
                            break;
                        }
                        if ($alle['parent'] != 'seq_GEN') {
                            Out::msgInfo("IMPOSSIBILE CANCELLARE QUESTO ALLEGATO", "Per cancellare l'allegato entrare nella gestione del passo");
                        } else {
                            Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare l'allegato?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancAlle', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancAlle', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        $this->rowidAppoggio = $_POST['rowid'];
                        break;
                    case $this->gridDatiPratica:
                        Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare il dato aggiuntivo?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancDato', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancDato', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->gridImmobili:
                        Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare l'immobile?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancImm', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancImm', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        $this->rowidAppoggio = $_POST['rowid'];
                        break;
                    case $this->gridSoggetti:
                        Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare il soggetto?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancSogg', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancSogg', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        $this->rowidAppoggio = $_POST['rowid'];
                        break;
                    case $this->gridUnitaLocale:
                        Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare il soggetto?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancULoc', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancULoc', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        $this->rowidAppoggio = $_POST['rowid'];
                        break;
                    case $this->gridNote:
                        $dati = $this->noteManager->getNota($_POST['rowid']);
                        if ($dati['UTELOG'] != App::$utente->getKey('nomeUtente')) {
                            Out::msgStop("Attenzione!", "Solo l'utente " . $dati['UTELOG'] . " è abilitato alla modifica della Nota.");
                            break;
                        }
                        $this->noteManager->cancellaNota($_POST['rowid']);
                        $this->noteManager->salvaNote();
                        $this->caricaNote();
                        break;

                    case $this->gridOneri:
                        $model = "praGestOneri";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura model fallita");
                            break;
                        }
                        $formObj->delete = true;
                        $formObj->setRowid($_POST['rowid']);
                        $formObj->setNumeroPratica($this->currGesnum);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnGrigliePagamenti');
                        $formObj->setReturnId('');
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;

                    case $this->gridPagamenti:
//                        unset($this->praPagamenti[$_POST['rowid']]);
//                        $this->CaricaGriglia($this->gridPagamenti, $this->praPagamenti);
                        $model = "praGestConciliazioni";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura model fallita");
                            break;
                        }
                        $formObj->delete = true;
                        $formObj->setRowid($_POST['rowid']);
                        $formObj->setNumeroPratica($this->currGesnum);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnGrigliePagamenti');
                        $formObj->setReturnId('');
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridPassi:
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $this->praPassi = $this->praLib->caricaPassiBO($this->currGesnum);
                        if (!$this->praPerms->checkSuperUser($proges_rec)) {
                            $this->praPassi = $this->praPerms->filtraPassiView($this->praPassi);
                        }

                        if ($this->praPassi) {
                            $this->CaricaGriglia($this->gridPassi, $this->praPassi, '2');
                            //New
                            $this->DisattivaCellStatoPasso();
                            //
                            Out::show($this->gridPassi);
                        }
                        break;
                    case $this->gridPassiAssegnazione:
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $this->praPassiAssegnazione = $this->caricaPassiAssegnazione($this->currGesnum);
                        $this->CaricaGriglia($this->gridPassiAssegnazione, $this->praPassiAssegnazione, '2');
                        Out::show($this->gridPassiAssegnazione);
                        break;
                    case $this->gridAllegati:
                        $this->CaricaAllegati($this->currGesnum);
                        $this->CaricaGriglia($this->gridAllegati, $this->praAlle, '3');
                        break;
                    case $this->gridAllegatiSha2:
                        $this->CaricaAllegatiPASSHA2($this->currGesnum);
                        $this->CaricaGriglia($this->gridAllegatiSha2, $this->praAlleSha2, '3');
                        break;
                    case $this->gridComunicazioni:
                        $this->caricaComunicazioni();
                        $this->CaricaGriglia($this->gridComunicazioni, $this->praComunicazioni);
                        break;
                    case $this->gridSoggetti:
                        $this->CaricaGriglia($this->gridSoggetti, $this->praSoggetti->getGriglia());
                        break;
                    case $this->gridUnitaLocale:
                        $this->CaricaGriglia($this->gridUnitaLocale, $this->praUnitaLocale->getGriglia());
                        break;
                    case $this->gridImmobili:
                        $this->CaricaGriglia($this->gridImmobili, $this->praImmobili->getGriglia());
                        break;
                    case $this->gridNote:
                        $this->noteManager = praNoteManager::getInstance($this->praLib, praNoteManager::NOTE_CLASS_PROGES, $this->currGesnum);
                        $this->caricaNote();
                        break;

                    case $this->gridOneri:
                        $this->CaricaGriglia($this->gridOneri, $this->getOneri());
                        break;

                    case $this->gridPagamenti:
                        $this->CaricaGriglia($this->gridPagamenti, $this->getPagamenti());
                        break;
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridPassi:
                        $propas_rec = $this->praLib->GetPropas($_POST['rowid'], 'rowid');
                        switch ($_POST['colName']) {
                            case 'VAI':
                                $mess = '';
                                $arrayMsg = array('F8 - Annulla' => array('id' => $this->nameForm . '_AnnullaVis', 'model' => $this->nameForm, 'shortCut' => 'f8'));

                                if ($propas_rec['PROQST'] == 0) {
                                    if ($propas_rec['PROVPA']) {
                                        $propas_vaipasso_rec = $this->praLib->GetPropas($propas_rec['PROVPA'], 'propak');
                                        $mess .= "Il passo di destinazione ha sequenza: {$propas_vaipasso_rec['PROSEQ']} - ";
                                        $mess .= $propas_vaipasso_rec['PRODPA'] . '<br>';
                                        $arrayMsg['F5 - Vai al passo'] = array(
                                            'id' => $this->nameForm . '_ConfermaVaiPasso' . $propas_vaipasso_rec['ROWID'],
                                            'model' => $this->nameForm,
                                            'metaData' => "extraData: { id: '{$this->nameForm}_ConfermaVaiPasso', rowid: '{$propas_vaipasso_rec['ROWID']}' }",
                                            'shortCut' => 'f5'
                                        );
                                    }
                                }

                                if ($propas_rec['PROQST'] == 1) {
                                    if ($propas_rec['PROVPA']) {
                                        $propas_vaiSI = $this->praLib->GetPropas($propas_rec['PROVPA'], 'propak');
                                        $mess .= "Il passo di destinazione (risposta SI) ha sequenza: {$propas_vaiSI['PROSEQ']} - ";
                                        $mess .= $propas_vaiSI['PRODPA'] . '<br>';
                                        $arrayMsg['F5 - Vai al passo SI'] = array(
                                            'id' => $this->nameForm . '_ConfermaVaiPasso' . $propas_vaiSI['ROWID'],
                                            'model' => $this->nameForm,
                                            'metaData' => "extraData: { id: '{$this->nameForm}_ConfermaVaiPasso', rowid: '{$propas_vaiSI['ROWID']}' }",
                                            'shortCut' => 'f5'
                                        );
                                    }

                                    if ($propas_rec['PROVPN']) {
                                        $propas_vaiNO = $this->praLib->GetPropas($propas_rec['PROVPN'], 'propak');
                                        $mess .= "Il passo di destinazione (risposta NO) ha sequenza: {$propas_vaiNO['PROSEQ']} - ";
                                        $mess .= $propas_vaiNO['PRODPA'] . '<br>';
                                        $arrayMsg['F6 - Vai al passo NO'] = array(
                                            'id' => $this->nameForm . '_ConfermaVaiPasso' . $propas_vaiNO['ROWID'],
                                            'model' => $this->nameForm,
                                            'metaData' => "extraData: { id: '{$this->nameForm}_ConfermaVaiPasso', rowid: '{$propas_vaiNO['ROWID']}' }",
                                            'shortCut' => 'f6'
                                        );
                                    }
                                }

                                if ($propas_rec['PROQST'] == 2) {
                                    /*
                                     * Cerco sulla tabella PROVPADETT
                                     */

                                    $provpadett_tab = $this->praLib->GetProvpadett($propas_rec['PROPAK'], 'propak');
                                    foreach ($provpadett_tab as $provpadett_rec) {
                                        $propas_vaipasso_rec = $this->praLib->GetPropas($provpadett_rec['PROVPA'], 'propak');
                                        $mess .= "Il passo di destinazione ({$provpadett_rec['PROVPADESC']}) ha sequenza: {$propas_vaipasso_rec['PROSEQ']} - ";
                                        $mess .= $propas_vaipasso_rec['PRODPA'] . '<br>';
                                        $arrayMsg["Vai alla risposta '{$provpadett_rec['PROVPADESC']}'"] = array(
                                            'id' => $this->nameForm . '_ConfermaVaiPasso' . $propas_vaipasso_rec['ROWID'],
                                            'model' => $this->nameForm,
                                            'metaData' => "extraData: { id: '{$this->nameForm}_ConfermaVaiPasso', rowid: '{$propas_vaipasso_rec['ROWID']}' }"
                                        );
                                    }
                                }

                                if (count($arrayMsg) > 1) {
                                    Out::msgQuestion('Info Passo', $mess, $arrayMsg);
                                }
                                break;
                            case 'PROCEDURA':
                                if ($propas_rec['PROCTR'] == "") {
                                    break;
                                }
                                $anapco_rec = $this->praLib->GetAnapco($propas_rec['PROCTR']);
                                Out::msgInfo("Info Procedura.", "La procdura di controllo ha codice: " . $anapco_rec['PCOCOD'] . "
                                <BR>" . $anapco_rec['PCODES']);
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
                            case 'STATOPASSO':
                                $sql = " SELECT * FROM PROPAS WHERE ROWID='" . $_POST['rowid'] . "'";
                                $Propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                                if ((!$Propas_rec['PROINI']) || ($Propas_rec['PROINI'] && $Propas_rec['PROFIN'])) {
                                    if (!$Propas_rec['PROINI']) {
                                        Out::msgQuestion("Apertura passo", "Confermi l'apertura del passo " . $Propas_rec['PRODPA'] . " ?", array(
                                            'Annulla' => array('id' => $this->nameForm . '_AnnullaApriPasso', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                            'Conferma' => array('id' => $this->nameForm . '_ConfermaApriPasso', 'model' => $this->nameForm, 'shortCut' => "f5")
                                                )
                                        );
                                    } else {
                                        Out::msgQuestion("Apertura passo", "Confermi l'apertura del passo " . $Propas_rec['PRODPA'] . " ?", array(
                                            'Annulla' => array('id' => $this->nameForm . '_AnnullaApriPasso', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                            'Azzera Date' => array('id' => $this->nameForm . '_AzzeraDatePasso', 'model' => $this->nameForm, 'shortCut' => "f6"),
                                            'Conferma' => array('id' => $this->nameForm . '_ConfermaApriPasso', 'model' => $this->nameForm, 'shortCut' => "f5")
                                                )
                                        );
                                    }
                                }
                                if ($Propas_rec['PROINI'] && !$Propas_rec['PROFIN']) {
                                    Out::msgQuestion("Chiudi Passo", "Chiudere il passo " . $Propas_rec['PRODPA'] . " inserendo la data odierna e opzionalmente attivare un altro passo?", array(
                                        'Annulla' => array('id' => $this->nameForm . '_AnnullaChiudiPasso', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                        'Azzera Date' => array('id' => $this->nameForm . '_AzzeraDatePasso', 'model' => $this->nameForm, 'shortCut' => "f7"),
                                        'Chiudi' => array('id' => $this->nameForm . '_ChiudiPasso', 'model' => $this->nameForm, 'shortCut' => "f5")
                                            )
                                    );
                                }

                                break;
                        }
                    case $this->gridAllegatiSha2:
                        $allegato = $this->praAlleSha2[$_POST['rowid']];
                        $pasdoc_rec = $this->praLib->GetPasdoc($allegato['ROWID'], "ROWID");
                        $propas_rec = $this->praLib->GetPropas($pasdoc_rec['PASKEY'], "propak");
                        $fl_ok_to_proceed = true;
                        switch ($_POST['colName']) {
                            case 'EDIT':
                            case 'EVIDENZIA':
                                if ($pasdoc_rec) {
                                    $this->praAlleSha2['EvidenziaRow'] = $_POST['rowid'];
                                }
                                break;
                            case 'NUMALLEGATI':
                            case 'SOST':
                                /*
                                 * Ok a procedere
                                 */
                                break;
                            default:
                                if ($propas_rec['PROVISIBILITA'] == "Protetto") {
                                    $proges_rec = $this->praLib->GetProges($this->currGesnum);
                                    if (!$this->praPerms->checkSuperUser($proges_rec)) {
                                        if ($this->praPerms->checkUtenteGenerico($propas_rec)) {
                                            $fl_ok_to_proceed = false;
                                        }
                                    }
                                }
                                break;
                        }
                        if (!$fl_ok_to_proceed) {
                            break;
                        }

                        if ($pasdoc_rec['PASPRTROWID'] != 0 && ($pasdoc_rec['PASPRTCLASS'] == "PROGES" || $pasdoc_rec['PASPRTCLASS'] == "PRACOM") && $_POST['colName'] == "LOCK") {
                            break;
                        }
                        $ext = pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION);
                        switch ($_POST['colName']) {
                            case 'EDIT':

                                /*
                                 * Se è un testo base, verifico la presenza del P7M e in caso sostituisco alcuni valori
                                 */
                                if ($allegato['FILEINFO'] == "TESTOBASE") {
                                    $pramPath = pathinfo($allegato['FILEPATH'], PATHINFO_DIRNAME);
                                    if (file_exists($pramPath . "/" . pathinfo($allegato['FILEPATH'], PATHINFO_FILENAME) . ".pdf.p7m")) {
                                        $allegato['NAME'] = pathinfo($allegato['FILEORIG'], PATHINFO_FILENAME) . ".pdf.p7m";
                                        $allegato['FILEORIG'] = pathinfo($allegato['FILEORIG'], PATHINFO_FILENAME) . ".pdf.p7m";
                                        $allegato['FILENAME'] = pathinfo($allegato['FILENAME'], PATHINFO_FILENAME) . ".pdf.p7m";
                                        $allegato['FILEPATH'] = $pramPath . "/" . pathinfo($allegato['FILEPATH'], PATHINFO_FILENAME) . ".pdf.p7m";
                                        $ext = pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION);
                                    }
                                }
                                if (strtolower($ext) == "p7m") {
                                    $filent_rec = $this->praLib->GetFilent(21);
                                    $StringaSegnatura = $this->praLibAllegati->GetMarcatureString($param, $this->currGesnum, $allegato['ROWID'], 21);
                                    $paramSegnatura = array();
                                    $paramSegnaturaTop = array(
                                        'STRING' => $StringaSegnatura,
                                        'FIRSTPAGEONLY' => $filent_rec['FILDE1'],
                                        'X-COORD' => $filent_rec['FILDE3'],
                                        'Y-COORD' => $filent_rec['FILDE4'],
                                        'ROTATION' => $filent_rec['FILDE2']
                                    );
                                    $paramSegnatura[] = $paramSegnaturaTop;
                                    $FirmaStr = 'Riproduzione cartacea del documento informatico sottoscritto digitalmente da @{$PRAALLEGATI.FIRMATARIO}@ @{$PRAALLEGATI.DATAPROT}@ @{$PRAALLEGATI.ORAPROT}@';
                                    $paramSegnaturaBottom1 = array(
                                        'STRING' => $this->praLibAllegati->getMarcatureFromTemplate($this->currGesnum, $allegato['ROWID'], $FirmaStr),
                                        'FIRSTPAGEONLY' => 1,
                                        'X-COORD' => 20,
                                        'Y-COORD' => 820,
                                        'ROTATION' => 0,
                                        'FONT-SIZE' => 8
                                    );
                                    $paramSegnatura[] = $paramSegnaturaBottom1;

                                    $FirmaStr = "ai sensi degli artt.20 e 21 del D.Lgs n.82/05 e successive modificazioni e integrazioni.";
                                    $paramSegnaturaBottom2 = array(
                                        'STRING' => $FirmaStr, //$this->praLibAllegati->getMarcatureFromTemplate($this->currGesnum, $allegatoFirmato['ROWID'], $FirmaStr),
                                        'FIRSTPAGEONLY' => 1,
                                        'X-COORD' => 20,
                                        'Y-COORD' => 830,
                                        'ROTATION' => 0,
                                        'FONT-SIZE' => 8
                                    );
                                    $paramSegnatura[] = $paramSegnaturaBottom2;
                                    $this->praLib->VisualizzaFirme($allegato['FILEPATH'], $allegato['FILEORIG'], $paramSegnatura, $allegato['ROWID']);
                                    //$this->praLib->VisualizzaFirme($allegato['FILEPATH'], $allegato['FILEORIG']);
                                } else if (strtolower($ext) == "zip") {
                                    $zipPath = pathinfo($allegato['FILEPATH'], PATHINFO_DIRNAME) . "/" . pathinfo($allegato['FILEPATH'], PATHINFO_FILENAME);
                                    $ret = itaZip::Unzip($allegato['FILEPATH'], $zipPath);
                                    if ($ret != 1) {
                                        Out::msgStop("ATTENZIONE!!!", "Estrazione file fallita");
                                        break;
                                    }
                                    if (!is_dir($zipPath)) {
                                        Out::msgStop("ATTENZIONE!!!", "Cartella " . pathinfo($allegato['FILEPATH'], PATHINFO_FILENAME) . " non trovata");
                                        break;
                                    }
                                    $arrayZip = array();
                                    $key = 1;
                                    $arrayZip[$key]['SEQ'] = 1; //pathinfo($allegatoFirmato['FILEPATH'], PATHINFO_FILENAME)."_1";
                                    $arrayZip[$key]['NAME'] = $allegato['NAME'];
                                    $arrayZip[$key]['FILEPATH'] = $zipPath;
                                    $arrayZip[$key]['level'] = 0;
                                    $arrayZip[$key]['parent'] = ''; //seq_GEN';
                                    $arrayZip[$key]['isLeaf'] = 'false';
                                    $arrayZip[$key]['expanded'] = 'true';
                                    $arrayZip[$key]['loaded'] = 'true';
                                    $arrayExplodeZip = $this->praLib->explodeZipDir($zipPath, $arrayZip, 0, $key);
                                    praRic::GetExplodedZip($this->nameForm, $arrayExplodeZip, $allegato['FILEORIG']);
//TODO: Testare funzionameto ed eliminare
                                    $this->rowidAppoggio = $zipPath;
                                }
                                break;
                            case "LOCK":
                                if ($this->praReadOnly == true) {
                                    break;
                                }
                                $this->praAlleSha2 = $this->praLibAllegati->EvidenziaBloccaAllegati($this->praAlleSha2, $_POST['rowid'], $this->sha2View, $evidenzia);
                                $this->CaricaGriglia($this->gridAllegatiSha2, $this->praAlleSha2, "3");
                                $this->praLibAllegati->BlockCellGridAllegati($this->praAlleSha2, $this->gridAllegatiSha2);
                                break;
                            case "NUMALLEGATI":
                                $doc = $this->praAlleSha2[$_POST['rowid']];
                                $pasdoc_tab_TotFile = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PASDOC WHERE " . $this->PRAM_DB->subString('PASKEY', 1, 10) . " = '$this->currGesnum' AND PASSHA2 = '" . $doc['PASSHA2'] . "'", true);
                                praRic::praRicAllegatiToTali($pasdoc_tab_TotFile, $this->nameForm);
                                break;
                            case "SOST":
                                $msg = $this->praLib->CheckSostFile($this->currGesnum, $pasdoc_rec['PASSHA2'], $pasdoc_rec['PASSHA2SOST'], "MSG");
                                if ($msg) {
                                    Out::msgInfo("Sostituzione Allegati", $msg);
                                }
                                break;
                            case "DESTINAZIONI";
                                if ($pasdoc_rec['PASPRTROWID'] == 0 && $pasdoc_rec['PASPRTCLASS'] == "") {
                                    $dest = $this->praAlleSha2[$_POST['rowid']]['PASDEST'];
                                    $rowidAlle = $_POST['rowid'];
                                    $_POST = array();
                                    $model = 'praGestDest';
                                    $_POST[$model . '_returnModel'] = $this->nameForm;
                                    $_POST[$model . '_returnEvent'] = 'returnDestinazioniSha2';
                                    $_POST['event'] = 'openform';
                                    $_POST['destinazioni'] = $dest;
                                    $_POST['rowidAlle'] = $rowidAlle;
                                    itaLib::openForm($model);
                                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                    $model();
                                }
                                break;
                        }
                        break;
                    case $this->gridAllegati:
                        $allegatoFirmato = $this->praAlle[$_POST['rowid']];
                        if (!$allegatoFirmato) {
                            break;
                        }
                        $pasdoc_rec = $this->praLib->GetPasdoc($allegatoFirmato['ROWID'], "ROWID");
                        $propas_rec = $this->praLib->GetPropas($allegatoFirmato['parent'], "propak");
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $fl_ok_to_proceed = true;
                        switch ($_POST['colName']) {
                            case 'EVIDENZIA':
                                if ($pasdoc_rec) {
                                    $this->praAlle['EvidenziaRow'] = $_POST['rowid'];
                                }
                                break;
                            case 'EDIT':
                            // case 'EVIDENZIA':
                            case 'SOST':
                                /*
                                 * Ok a procedere
                                 */
                                break;
                            default:
                                if ($propas_rec['PROVISIBILITA'] == "Protetto") {
                                    if (!$this->praPerms->checkSuperUser($proges_rec)) {
                                        if ($this->praPerms->checkUtenteGenerico($propas_rec)) {
                                            $fl_ok_to_proceed = false;
                                        }
                                    }
                                }
                                break;
                        }
                        if (!$fl_ok_to_proceed) {
                            break;
                        }
                        if ($pasdoc_rec['PASPRTROWID'] != 0 && ($pasdoc_rec['PASPRTCLASS'] == "PROGES" || $pasdoc_rec['PASPRTCLASS'] == "PRACOM") && $_POST['colName'] == "LOCK") {
                            break;
                        }

                        $ext = pathinfo($allegatoFirmato['FILENAME'], PATHINFO_EXTENSION);
                        switch ($_POST['colName']) {
                            case 'EDIT':

                                /*
                                 * Se è un testo base, verifico la presenza del P7M e in caso sostituisco alcuni valori
                                 */
                                if ($allegatoFirmato['FILEINFO'] == "TESTOBASE") {
                                    $pramPath = pathinfo($allegatoFirmato['FILEPATH'], PATHINFO_DIRNAME);
                                    if (file_exists($pramPath . "/" . pathinfo($allegatoFirmato['FILEPATH'], PATHINFO_FILENAME) . ".pdf.p7m")) {
                                        $allegatoFirmato['NAME'] = pathinfo($allegatoFirmato['FILEORIG'], PATHINFO_FILENAME) . ".pdf.p7m";
                                        $allegatoFirmato['FILEORIG'] = pathinfo($allegatoFirmato['FILEORIG'], PATHINFO_FILENAME) . ".pdf.p7m";
                                        $allegatoFirmato['FILENAME'] = pathinfo($allegatoFirmato['FILENAME'], PATHINFO_FILENAME) . ".pdf.p7m";
                                        $allegatoFirmato['FILEPATH'] = $pramPath . "/" . pathinfo($allegatoFirmato['FILEPATH'], PATHINFO_FILENAME) . ".pdf.p7m";
                                        $ext = pathinfo($allegatoFirmato['FILENAME'], PATHINFO_EXTENSION);
                                    }
                                }

                                if (strtolower($ext) == "p7m") {
                                    $filent_rec = $this->praLib->GetFilent(21);
                                    $StringaSegnatura = $this->praLibAllegati->GetMarcatureString($param, $this->currGesnum, $allegatoFirmato['ROWID'], 21);
                                    $paramSegnatura = array();
                                    $paramSegnaturaTop = array(
                                        'STRING' => $StringaSegnatura,
                                        'FIRSTPAGEONLY' => $filent_rec['FILDE1'],
                                        'X-COORD' => $filent_rec['FILDE3'],
                                        'Y-COORD' => $filent_rec['FILDE4'],
                                        'ROTATION' => $filent_rec['FILDE2']
                                    );
                                    $paramSegnatura[] = $paramSegnaturaTop;
                                    $FirmaStr = 'Riproduzione cartacea del documento informatico sottoscritto digitalmente da @{$PRAALLEGATI.FIRMATARIO}@ @{$PRAALLEGATI.DATAPROT}@ @{$PRAALLEGATI.ORAPROT}@';
                                    $paramSegnaturaBottom1 = array(
                                        'STRING' => $this->praLibAllegati->getMarcatureFromTemplate($this->currGesnum, $allegatoFirmato['ROWID'], $FirmaStr),
                                        'FIRSTPAGEONLY' => 1,
                                        'X-COORD' => 20,
                                        'Y-COORD' => 820,
                                        'ROTATION' => 0,
                                        'FONT-SIZE' => 8
                                    );
                                    $paramSegnatura[] = $paramSegnaturaBottom1;

                                    $FirmaStr = "ai sensi degli artt.20 e 21 del D.Lgs n.82/05 e successive modificazioni e integrazioni.";
                                    $paramSegnaturaBottom2 = array(
                                        'STRING' => $FirmaStr, //$this->praLibAllegati->getMarcatureFromTemplate($this->currGesnum, $allegatoFirmato['ROWID'], $FirmaStr),
                                        'FIRSTPAGEONLY' => 1,
                                        'X-COORD' => 20,
                                        'Y-COORD' => 830,
                                        'ROTATION' => 0,
                                        'FONT-SIZE' => 8
                                    );
                                    $paramSegnatura[] = $paramSegnaturaBottom2;
                                    $this->praLib->VisualizzaFirme($allegatoFirmato['FILEPATH'], $allegatoFirmato['FILEORIG'], $paramSegnatura, $allegatoFirmato['ROWID']);
                                } else if (strtolower($ext) == "zip") {
                                    $zipPath = pathinfo($allegatoFirmato['FILEPATH'], PATHINFO_DIRNAME) . "/" . pathinfo($allegatoFirmato['FILEPATH'], PATHINFO_FILENAME);
                                    $ret = itaZip::Unzip($allegatoFirmato['FILEPATH'], $zipPath);
                                    if ($ret != 1) {
                                        Out::msgStop("ATTENZIONE!!!", "Estrazione file fallita");
                                        break;
                                    }
                                    if (!is_dir($zipPath)) {
                                        Out::msgStop("ATTENZIONE!!!", "Cartella " . pathinfo($allegatoFirmato['FILEPATH'], PATHINFO_FILENAME) . " non trovata");
                                        break;
                                    }
                                    $arrayZip = array();
                                    $key = 1;
                                    $arrayZip[$key]['SEQ'] = 1; //pathinfo($allegatoFirmato['FILEPATH'], PATHINFO_FILENAME)."_1";
                                    $arrayZip[$key]['NAME'] = $allegatoFirmato['NAME'];
                                    $arrayZip[$key]['FILEPATH'] = $zipPath;
                                    $arrayZip[$key]['level'] = 0;
                                    $arrayZip[$key]['parent'] = ''; //seq_GEN';
                                    $arrayZip[$key]['isLeaf'] = 'false';
                                    $arrayZip[$key]['expanded'] = 'true';
                                    $arrayZip[$key]['loaded'] = 'true';
                                    $arrayExplodeZip = $this->praLib->explodeZipDir($zipPath, $arrayZip, 0, $key);
                                    praRic::GetExplodedZip($this->nameForm, $arrayExplodeZip, $allegatoFirmato['FILEORIG']);
//TODO: Testare funzionameto ed eliminare
                                    $this->rowidAppoggio = $zipPath;
                                }
                                break;
//                            case "EVIDENZIA":
//                                $evidenzia = true;
                            case "LOCK":
                                if ($this->praReadOnly == true) {
                                    break;
                                }
                                $this->praAlle = $this->praLibAllegati->EvidenziaBloccaAllegati($this->praAlle, $_POST['rowid'], $this->sha2View, $evidenzia);
                                $this->CaricaGriglia($this->gridAllegati, $this->praAlle);
                                $this->praLibAllegati->BlockCellGridAllegati($this->praAlle, $this->gridAllegati);
                                break;
                            case "SOST":
                                $msg = $this->praLib->CheckSostFile($this->currGesnum, $pasdoc_rec['PASSHA2'], $pasdoc_rec['PASSHA2SOST'], "MSG");
                                if ($msg) {
                                    Out::msgInfo("Sostituzione Allegati", $msg);
                                }
                                break;
                            case "DESTINAZIONI";
                                if ($pasdoc_rec['PASPRTROWID'] == 0 && $pasdoc_rec['PASPRTCLASS'] == "") {
                                    $dest = $this->praAlle[$_POST['rowid']]['PASDEST'];
                                    $rowidAlle = $_POST['rowid'];
                                    $_POST = array();
                                    $model = 'praGestDest';
                                    $_POST[$model . '_returnModel'] = $this->nameForm;
                                    $_POST[$model . '_returnEvent'] = 'returnDestinazioni';
                                    $_POST['event'] = 'openform';
                                    $_POST['destinazioni'] = $dest;
                                    $_POST['rowidAlle'] = $rowidAlle;
                                    itaLib::openForm($model);
                                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                    $model();
                                }
                                break;
                        }
                        break;
                    case $this->gridSoggetti:
                        $soggComm = $this->praSoggetti->GetSoggetto($_POST['rowid']);
                        switch ($_POST['colName']) {
                            case 'AUT':
                                $proges_rec = $this->praLib->GetProges($this->currGesnum);
                                $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA'], 'codice');
                                $this->soggComm = $soggComm['ROWID'];
                                if ($anaspa_rec["SPAENTECOMM"]) {
                                    if ($this->remoteToken) {
                                        $profiloUtente = proSoggetto::getProfileFromIdUtente();
                                        $ananom_rec = $this->praLib->GetAnanom($profiloUtente['COD_ANANOM']);
                                        $arrayMeta = unserialize($ananom_rec['NOMMETA']);
                                        $utenteAggr = $this->praLib->GetUtenteAggregato($arrayMeta['Utenti'], $proges_rec['GESSPA']);
                                        if ($utenteAggr == "") {
                                            Out::msgInfo("collegamento commercio", "Utente non configurato per l'aggregato <b>" . $anaspa_rec["SPADES"] . "</b><br>Per configurare l'utente entrare nell'anagrafica dipendenti.");
                                            break;
                                        }
//
                                        $ieDomain = $anaspa_rec["SPAENTECOMM"];
                                        $ieMessage = 'Apertura definizione procedimento on-line. Ok per continuare.';
                                        $ieUrl = $_SERVER['HTTP_REFERER'];
                                        $retToken = $this->praLib->CheckItaEngineContextToken($this->remoteToken, $ieDomain);
                                        if (!$retToken) {
                                            $html = "<span style=\"font-size:1.2em;\">La password richiesta, fa riferimento all'utente <b>$utenteAggr</b> nell'applicativo Commercio.</span>";
                                            $this->praLib->GetMsgInputPassword($this->nameForm, "Apri ricerca Commercio", "APRIRICCOMM", $html);
                                        } else {
                                            Out::openIEWindow(array(
                                                "ieurl" => $ieUrl,
                                                "ietoken" => $this->remoteToken,
                                                "iedomain" => $ieDomain,
                                                "ieOpenMessage" => $ieMessage
                                                    ), array(
                                                "model" => "menDirect",
                                                "menu" => "CO_HID",
                                                "prog" => "CO_SUAPEXT",
                                                "modo" => "apriRicCommercio",
                                                "soggetto" => $this->soggComm,
                                                "ditta" => App::$utente->getKey('ditta'),
                                                "accessreturn" => "",
                                            ));
                                        }
                                    } else {
                                        $html = "<span style=\"font-size:1.2em;\">La password richiesta, fa riferimento all'utente <b>$utenteAggr</b> nell'applicativo Commercio.</span>";
                                        $this->praLib->GetMsgInputPassword($this->nameForm, "Apri Ricerca Commercio", "APRIRICCOMM", $html);
                                    }
                                } else {
                                    itaLib::openForm('wcoCommSuap', true);
                                    $wcoCommSuap = itaModel::getInstance('wcoCommSuap');
                                    $wcoCommSuap->setEvent('openform');
                                    $wcoCommSuap->setModo('apriRicCommercio');
                                    $wcoCommSuap->setReturnEvent("returnCommSuap");
                                    $wcoCommSuap->setReturnModel($this->nameForm);
                                    $wcoCommSuap->setDitta(App::$utente->getKey('ditta'));
                                    $wcoCommSuap->setSoggetto($this->soggComm);
                                    $wcoCommSuap->parseEvent();
                                }
                                break;
                            case "FIERE":
                                $arrCountFiere = $this->praSoggetti->GetArrCountFiere();
                                foreach ($arrCountFiere as $key => $fiere) {
                                    if ($key == $soggComm['ROWID']) {
                                        $matriceSelezionate = $arrCountFiere[$key];
                                    }
                                }
                                if (!$matriceSelezionate) {
                                    break;
                                }
                                praRic::praRicFiereSogg($matriceSelezionate, $this->nameForm);
                                break;
//                            case "POSIZIONE":
//                                if ($soggComm['DESRUO'] == praRuolo::$SISTEM_SUBJECT_ROLES['UNITALOCALE']['RUOCOD']) {
//                                    $comune = $soggComm['DESCIT'];
//                                    $cap = $soggComm['DESCAP'];
//                                    $prov = $soggComm['DESPRO'];
//                                    $via = $soggComm['DESIND'] . " " . $soggComm['DESCIV'];
//                                    $query = $comune . ", " . $cap . ", " . $prov . ", " . $via;
//                                    $url = "http://maps.google.com/?hl=it&tab=wl&q=" . $query;
//                                    $url = str_replace(" ", "+", $url);
//                                    Out::codice("window.open('" . $url . "','_Blank')");
//                                }
//                                break;
                        }
                        break;
                    case $this->gridUnitaLocale:
                        $soggComm = $this->praUnitaLocale->GetSoggetto($_POST['rowid']);
                        switch ($_POST['colName']) {
                            case 'POSIZIONE':
                                /* @var $geoAppContextObj geoAppObjectPratiche */
                                $geoAppContextObj = geoAppFactory::getAppObjectInstanceForAppContext(geoLib::APPCONTEXT_PRATICHE);
                                if (!$geoAppContextObj->getAppObjects($soggComm['ROWID'])) {
                                    if ($soggComm['DESRUO'] == praRuolo::$SISTEM_SUBJECT_ROLES['UNITALOCALE']['RUOCOD']) {
                                        $comune = $soggComm['DESCIT'];
                                        $cap = $soggComm['DESCAP'];
                                        $prov = $soggComm['DESPRO'];
                                        $via = $soggComm['DESIND'] . " " . $soggComm['DESCIV'];
                                        $query = $comune . ", " . $cap . ", " . $prov . ", " . $via;
                                        $url = "http://maps.google.com/?hl=it&tab=wl&q=" . $query;
                                        $url = str_replace(" ", "+", $url);
                                        Out::codice("window.open('" . $url . "','_Blank')");
                                    }
                                } else {
                                    $model = "geoOggetti";
                                    $_POST['tipo'] = 'PRATICHE';
                                    $_POST['rowid'] = $soggComm['ROWID'];
                                    $_POST['codice'] = $soggComm['DESCODIND'];
                                    $_POST['civico'] = $soggComm['DESCIV'];
                                    itaLib::openDialog($model);
                                    $objBdaLavori = itaModel::getInstance($model);
                                    $objBdaLavori->setReturnModel($this->nameForm);
                                    $objBdaLavori->setReturnEvent('ReturnVar');
                                    if (!$objBdaLavori) {
                                        break;
                                    }
                                    $objBdaLavori->setEvent('openform');
                                    $objBdaLavori->parseEvent();
                                }
                                break;
                        }
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaApriPasso':
                        $rowid = $_POST[$this->nameForm . '_gridPassi']['gridParam']['selrow'];
                        if ($rowid == "") {
                            Out::msgStop("Errore!!", "Id passo non trovato. Impossibile aprire il passo.");
                            break;
                        }
                        $sql = " SELECT * FROM PROPAS WHERE ROWID=$rowid";
                        $Propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                        if (!$Propas_rec) {
                            Out::msgStop("Errore!!", "Record passo non trovato. Impossibile aprire il passo.");
                            break;
                        }

                        $Propas_rec['PROINI'] = date('Ymd');
                        $Propas_rec['PROFIN'] = '';

                        $praPasso = new praPasso();
                        if (!$praPasso->SincronizzaRecordPasso($Propas_rec)) {
                            break;
                        }
                        $this->CheckAperturaPassoPadre($Propas_rec);
                        break;

                    case $this->nameForm . '_AnnullaChiudiPasso';
                        TableView::reload($this->gridPassi);
                        break;

                    case $this->nameForm . '_AnnullaApriPadre':
                        TableView::reload($this->gridPassi);
                        break;

                    case $this->nameForm . '_ChiudiPasso':
                        $Rowid = $_POST[$this->nameForm . '_gridPassi']['gridParam']['selrow'];
                        if (!$this->ChiudiPasso($Rowid)) {
                            break;
                        }
                        if ($this->Propas) {
                            Out::msgQuestion("Attenzione", "Vi sono <b>" . count($this->Propas) . "</b> passi/o interni al passo in chiusura. Vuoi chiudere anche i passi interni?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaChiusura', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaChiusura', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        TableView::reload($this->gridPassi);
                        break;
                    case $this->nameForm . '_AzzeraDatePasso':
                        $Rowid = $_POST[$this->nameForm . '_gridPassi']['gridParam']['selrow'];
                        if ($Rowid == "") {
                            Out::msgStop("Errore!!", "Id passo non trovato. Impossibile azzerare le date.");
                            break;
                        }

                        $Propas_rec = $this->praLib->GetPropas($Rowid, 'rowid');
                        if (!$Propas_rec) {
                            Out::msgStop("Errore!!", "Record passo non trovato. Impossibile azzerare le date.");
                            break;
                        }

                        $Propas_rec['PROINI'] = $Propas_rec['PROFIN'] = '';
                        $update_Info = 'Oggetto : Azzeramento date Passo ' . $Propas_rec['PRONUM'] . '/' . $Propas_rec['PROSEQ'];
                        if (!$this->updateRecord($this->PRAM_DB, 'PROPAS', $Propas_rec, $update_Info)) {
                            Out::msgStop("Aggiornamento Passo", "Errore nell'Azzeramento date Passo " . $Propas_rec['PROSEQ']);
                            break;
                        }
                        TableView::reload($this->gridPassi);
                        break;
                    case $this->nameForm . '_Chiudi_E_New':
                        $Rowid = $_POST[$this->nameForm . '_gridPassi']['gridParam']['selrow'];
                        if (!$this->ChiudiPasso($Rowid)) {
                            break;
                        }
                        praRic::praPassiNonAperti($this->praPassi, $this->nameForm, "expPra", "Scegli passo da aprire", "PROPAS");
                        break;
//                    case $this->nameForm . '_Aggiungi':
//                        // Valorizzo dati da Form
//                        $proges_rec = $_POST[$this->nameForm . '_PROGES'];
//                        if ($_POST[$this->nameForm . '_Numero_prot'] != 0 && $_POST[$this->nameForm . '_Anno_prot'] == 0) {
//                            Out::msgInfo("ATTENZIONE", "Inserire l'anno per il protocollo n. " . $_POST[$this->nameForm . '_Numero_prot']);
//                            break;
//                        }
//                        $proges_rec['GESNPR'] = str_pad($_POST[$this->nameForm . '_Anno_prot'], 4, "0", STR_PAD_RIGHT) . $_POST[$this->nameForm . '_Numero_prot'];
//                        $anades_rec = $_POST[$this->nameForm . '_ANADES'];
//                        $anades_rec['DESRUO'] = "0001";
//                        $tipoInserimento = ($this->datiFromWSProtocollo) ? "WSPROTOCOLLO" : "ANAGRAFICA";
//                        $ret_aggiungi = $this->aggiungi(array(
//                            "PROGES_REC" => $proges_rec,
//                            "ANADES_REC" => $anades_rec,
//                            "senzaSuap" => true,
//                            "tipoInserimento" => $tipoInserimento
//                        ));
//
//                        if ($ret_aggiungi != false) {
//                            $this->Dettaglio($ret_aggiungi);
//                        } else {
//                            $this->OpenRicerca();
//                            Out::msgStop("Inserimento Pratica", "Inserimento fallito.");
//                        }
//
//                        break;
                    case $this->nameForm . '_AnnullaAggiornaPratica':
                        $senzaSuap = true;
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
                            if ($this->sha2View == true) {
                                $this->aggiungiAllegatoSHA($this->currAllegato['randName'], $this->currAllegato['destFile'], $this->currAllegato['origFile']);
                            } else {
                                $this->aggiungiAllegato($this->currAllegato['randName'], $this->currAllegato['destFile'], $this->currAllegato['origFile']);
                            }
                            Out::msgInfo('Allega PDF', "Allegato PDF Accettato nonostante la non Conformità a PDF/A:" . $this->currAllegato['origFile']);
                        }
                        break;
                    case $this->nameForm . '_ConvertiPDFA':
                        $retConvert = $this->praLib->convertiPDFA($this->currAllegato['uplFile'], $this->currAllegato['destFile'], true);
                        if ($retConvert['status'] == 0) {
                            if ($this->sha2View == true) {
                                $this->aggiungiAllegatoSHA($this->currAllegato['randName'], $this->currAllegato['destFile'], $this->currAllegato['origFile']);
                            } else {
                                $this->aggiungiAllegato($this->currAllegato['randName'], $this->currAllegato['destFile'], $this->currAllegato['origFile']);
                            }
                            Out::msgInfo('Allega PDF', "Allegato PDF Convertito a PDF/A: " . $this->currAllegato['origFile']);
                        } else {
                            Out::msgStop("Conversione PDF/A Impossibile", $retConvert['message']);
                        }
                        break;
                    case $this->nameForm . '_AggiornaStatistici':
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $proges_rec['GESPRO'] = $_POST[$this->nameForm . "_PROGES"]['GESPRO'];
                        $proges_rec['GESTIP'] = $_POST[$this->nameForm . "_PROGES"]['GESTIP'];
                        $proges_rec['GESSTT'] = $_POST[$this->nameForm . "_PROGES"]['GESSTT'];
                        $proges_rec['GESATT'] = $_POST[$this->nameForm . "_PROGES"]['GESATT'];
                        $proges_rec['GESEVE'] = $_POST[$this->nameForm . "_PROGES"]['GESEVE'];
                        $proges_rec['GESSEG'] = $_POST[$this->nameForm . "_PROGES"]['GESSEG'];
                        $update_Info = 'Oggetto : Aggiornamento dati statistici pratica' . $proges_rec['GESNUM'];
                        if (!$this->updateRecord($this->PRAM_DB, 'PROGES', $proges_rec, $update_Info)) {
                            Out::msgStop("Attenzione!!", "Errore aggiornamento dati statistici pratica " . $proges_rec['GESNUM']);
                            break;
                        }
                        $this->Dettaglio($proges_rec['ROWID']);
                        break;
                    case $this->nameForm . '_ScaricaZip':
                        Out::msgQuestion("Creazione file Zip", "<br>Scegli il tipo di file zip da generare:<br>- Allegati di PRATICA <br>- TUTTI (allegati di pratica + allegati del passo)", array(
                            'TUTTI' => array('id' => $this->nameForm . '_ConfermaZipTutti', 'model' => $this->nameForm, 'shortCut' => ""),
                            'PRATICA' => array('id' => $this->nameForm . '_ConfermaZipPratica', 'model' => $this->nameForm, 'shortCut' => "")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaZipPratica':
                        $this->praLibAllegati->ScaricaAllegatiZipPratica($this->currGesnum);
                        break;
                    case $this->nameForm . '_ConfermaZipTutti':
                        $this->praLibAllegati->ScaricaAllegatiZipPratica($this->currGesnum, 'All', $this->praAlle);
                        break;
                    case $this->nameForm . '_Aggiorna':
                        //Out::msgInfo("POST", print_r($_POST,true));
                        /* @var $praCompDatiAggiuntivi praCompDatiAggiuntivi */
                        $praCompDatiAggiuntivi = itaModel::getInstance('praCompDatiAggiuntivi', $this->praCompDatiAggiuntiviFormname);

                        if (!$praCompDatiAggiuntivi->aggiornaDati()) {
                            break;
                        }
                        unset($praCompDatiAggiuntivi);

                        $dati['PROGES_REC'] = $_POST[$this->nameForm . '_PROGES'];
                        $dati['ANADES_REC'] = $_POST[$this->nameForm . '_ANADES'];
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
                    case $this->nameForm . '_Annulla':
                        $msg = "<b>Confermando verrà annullata la pratica n. $this->currGesnum.<br>Se sei sicuro di procedere, scegli unno stato.</b>";
                        praRic::praRicAnastp($this->nameForm, "WHERE STPFLAG = 'Annullata'", "ANNULLA", $msg);
                        break;
                    case $this->nameForm . '_Chiudi':
                        $this->praLibChiusuraMassiva->getMsgInputChiudiFascicolo($this->nameForm, "", $this->nameForm . '_ConfermaChiusuraFascicolo');
                        break;
                    case $this->nameForm . '_ConfermaDettaglioPratica':
                        $this->Dettaglio($this->rowidAppoggio);
                        break;
                    case $this->nameForm . '_StampaDettaglio':
                        devRic::devElencoReport($this->nameForm, $_POST, " WHERE CODICE<>'' AND CATEGORIA='FASCICOLIDETTAGLIO'", 'Dettaglio');
                        break;
                    case $this->nameForm . '_Etichetta':
                        $Filent_43 = $this->praLib->GetFilent(43);
                        if ($Filent_43['FILVAL'] == 'SINGOLA') {
                            $parametriEnte_rec = $this->utiEnte->GetParametriEnte();
                            $this->StampaReportFascicolo($_POST[$this->nameForm . "_PROGES"]['ROWID'], 'praStampa36x89', '0002');
                            break;
                        }
                        $rowid = $_POST[$this->nameForm . '_PROGES']['ROWID'];
                        $model = 'praStampaEtichetta';
                        itaLib::openForm($model, true);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['tipo'] = "4";
//                        $_POST['chiave'] = $this->rowidAppoggio;
                        $_POST['chiave'] = $rowid;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $model();
                        break;
                    case $this->nameForm . '_VediStorico':
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        praRic::praRicAntecedente($this->nameForm, 'returnAntecedente', $this->PRAM_DB, $proges_rec);
                        break;
                    case $this->nameForm . '_CercaAnt':
                        $openModel = "praGestElenco";
                        $model = $openModel . "_searchDialog";
                        itaLib::openDialog($openModel, true, true, 'desktopBody', "", "", $model);
                        Out::setDialogTitle($openModel . "_searchDialog", "Ricerca Fascicoli Elettronici");
                        $objModel = itaModel::getInstance($openModel, $model);
                        $objModel->setReturnModel($this->nameForm);
                        $objModel->setReturnEvent('returnFromPraGest');
                        $objModel->searchMode = true;
                        $objModel->setEvent('openform');
                        $objModel->parseEvent();
                        break;
                    case $this->nameForm . '_PROGES[GESPRO]_butt':
                        $this->dataRegAppoggio = $_POST[$this->nameForm . '_PROGES']['GESDRE'];
                        praRic::praRicAnapra($this->nameForm, "Ricerca procedimento", $this->nameForm . '_PROGES[GESPRO]', '', '', true);
                        break;
                    case $this->nameForm . '_PROGES[GESEVE]_butt':
                        if ($_POST[$this->nameForm . "_PROGES"]['GESPRO']) {
                            praRic::ricIteevt($this->nameForm, "WHERE ITEPRA = '" . $_POST[$this->nameForm . "_PROGES"]['GESPRO'] . "'", "DETT");
                        } else {
                            Out::msgInfo("Attenzione!!", "Selezionare prima un procedimento");
                        }
                        break;
                    case $this->nameForm . '_Anno_prot_butt':
                        $anno = $this->workYear;
                        $where = '';
                        if ($_POST[$this->nameForm . '_Anno_prot'] != 0) {
                            $anno = $_POST[$this->nameForm . '_Anno_prot'];
                        }
                        $numero = $_POST[$this->nameForm . '_Numero_prot'];
                        if ($numero != '') {
                            $numero = str_repeat("0", 6 - strlen(trim($numero))) . trim($numero);
                        }
                        if ($numero != '') {
                            $Anaproctr_rec = $this->proLib->GetAnapro($anno . $numero, 'codice', '', " (PROPAR='A' OR PROPAR='P')");
                            if ($Anaproctr_rec) {
                                $model = 'proArri';
                                $_POST = array();
                                $_POST['tipoProt'] = $Anaproctr_rec['PROPAR'];
                                $_POST['event'] = 'openform';
                                $_POST['proGest_ANAPRO']['ROWID'] = $Anaproctr_rec['ROWID'];
                                itaLib::openForm($model);
                                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                $model();
                            }
                        } else {
                            albRic::albRicAnapro($this->nameForm, $anno);
                        }
                        break;
                    case $this->nameForm . "_PROGES[GESTSP]_butt":
                        praRic::praRicAnatsp($this->nameForm, $where);
                        break;
                    case $this->nameForm . '_PROGES[GESSPA]_butt':
                        praRic:: praRicAnaspa($this->nameForm, $where, "2");
                        break;
                    case $this->nameForm . '_PROGES[GESRES]_butt':
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "Ricerca Responsabile", '', $this->nameForm . '_PROGES[GESRES]');
                        break;
                    case $this->nameForm . '_PRAIMM[CODICE]_butt':
                        $where = " WHERE 1";
                        if ($_POST[$this->nameForm . "_PRAIMM"]['FOGLIO']) {
                            $foglio = str_pad($_POST[$this->nameForm . "_PRAIMM"]['FOGLIO'], 4, "0", STR_PAD_LEFT);
                            $whereFoglio = " AND FOGLIO = '$foglio'";
                        }
                        if ($_POST[$this->nameForm . "_PRAIMM"]['PARTICELLA']) {
                            $particella = str_pad($_POST[$this->nameForm . "_PRAIMM"]['PARTICELLA'], 5, "0", STR_PAD_LEFT);
                            $whereNumero = " AND NUMERO = '$particella'";
                        }
                        if ($_POST[$this->nameForm . "_PRAIMM"]['SUBALTERNO']) {
                            $whereSub = " AND SUB = '" . $_POST[$this->nameForm . "_PRAIMM"]['SUBALTERNO'] . "'";
                        }
                        if ($whereFoglio || $whereNumero || $whereSub) {
                            if (catRic::catRicLegame($this->nameForm, $where . $whereFoglio . $whereNumero . $whereSub) === false) {
                                catRic::catRicLegame($this->nameForm, $where . $whereFoglio . $whereNumero . " AND SUB = ''");
                            }
                        }
                        break;
                    case $this->nameForm . '_PROGES[GESTIP]_butt':
                        praRic::praRicAnatip($this->PRAM_DB, $this->nameForm, "Ricerca Attivita", "", "GESTIP");
                        break;
                    case $this->nameForm . '_PROGES[GESSTT]_butt':
                        praRic::praRicAnaset($this->nameForm, "", "GESSTT");
                        break;
                    case $this->nameForm . '_PROGES[GESATT]_butt':
                        if ($_POST[$this->nameForm . '_PROGES']['GESSTT']) {
                            $where = "AND ATTSET = '" . $_POST[$this->nameForm . '_PROGES']['GESSTT'] . "'";
                        }
                        praRic::praRicAnaatt($this->nameForm, $where, "GESATT");
                        break;
                    case $this->nameForm . '_CodUtenteAss_butt':
                        $msgDetail = "Scegliere il soggetto di cui si vuol sapere le pratiche assegnate.";
                        praRic::praRicAnanom($this->PRAM_DB, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "Ricerca Soggetti", " WHERE NOMABILITAASS = 1 ", $this->nameForm . "_ricercaAss", false, null, $msgDetail, true);
                        break;
                    case $this->nameForm . "_CodTipoPasso_butt":
                        praRic::praRicPraclt(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "RICERCA Tipo Passo", "returnPraclt");
                        break;
                    case $this->nameForm . '_ScannerSha':
                    case $this->nameForm . '_Scanner':
                        Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAllegati");
                        $this->ApriScanner();
                        break;
                    case $this->nameForm . '_FileLocaleSha':
                    case $this->nameForm . '_FileLocale':
                        $this->AllegaFile();
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        Out::broadcastMessage($this->nameForm, "CLOSE_PRAGEST", array("GESNUM" => $this->currGesnum));
                        $this->OpenRicerca(true);
                        break;
                    case $this->nameForm . '_NuovoProtPec':
                        $model = 'proElencoMail';
                        $_POST['consultazione'] = $this->consultazione;
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_ConfermaCancDato':
                        if (array_key_exists($this->rowidAppoggio, $this->praDatiPratica) == true) {
                            if ($this->praDatiPratica[$this->rowidAppoggio]['ROWID'] != 0) {
                                $delete_Info = 'Oggetto: Cancellazione dato aggiuntivo ' . $this->praDatiPratica[$this->rowidAppoggio]['DAGKEY'];
                                if (!$this->deleteRecord($this->PRAM_DB, 'PRODAG', $this->praDatiPratica[$this->rowidAppoggio]['ROWID'], $delete_Info)) {
                                    Out::msgStop("Attenzione", "Errore in cancellazione del dato aggiuntivo su PRODAG");
                                }
                            }
                            unset($this->praDatiPratica[$this->rowidAppoggio]);
                        }
                        $this->ordinaSeqArrayDag();
                        $this->CaricaGriglia($this->gridDatiPratica, $this->praDatiPratica);
                        break;
                    case $this->nameForm . '_ConfermaCancImm':
                        if (!$this->praImmobili->CancellaImmobile($this->rowidAppoggio, $this)) {
                            Out::msgStop("Attenzione", "Errore in cancellazione immobile su PRAIMM");
                        }
                        $this->CaricaGriglia($this->gridImmobili, $this->praImmobili->getGriglia());
                        break;
                    case $this->nameForm . '_ConfermaCancSogg':
                        if (!$this->praSoggetti->CancellaSoggetto($this->rowidAppoggio, $this)) {
                            Out::msgStop("Attenzione", "Errore in cancellazione soggetto su ANADES");
                        }
                        $this->CaricaGriglia($this->gridSoggetti, $this->praSoggetti->getGriglia());
                        break;
                    case $this->nameForm . '_ConfermaCancULoc':
                        if (!$this->praUnitaLocale->CancellaSoggetto($this->rowidAppoggio, $this)) {
                            Out::msgStop("Attenzione", "Errore in cancellazione soggetto su ANADES");
                        }
                        $this->CaricaGriglia($this->gridUnitaLocale, $this->praUnitaLocale->getGriglia());
                        break;
                    case $this->nameForm . '_ConfermaCancAssegnazione':
                        $propas_rec = $this->praLib->GetPropas($this->rowidAppoggio, 'rowid');
                        $delete_Info = 'Oggetto: Cancellazione assegnazione: ' . $propas_rec['PRODPA'];
                        if (!$this->deleteRecord($this->PRAM_DB, 'PROPAS', $this->rowidAppoggio, $delete_Info)) {
                            Out::msgStop("Attenzione", "Errore in cancellazione dell'assegnazione su PROPAS");
                        } else {
                            TableView::reload($this->gridPassiAssegnazione);
                            $this->bottoniAssegnazione();
                        }
                        $this->rowidAppoggio = null;
                        break;
                    case $this->nameForm . '_ConfermaCancAlleSha':
                        $ext = pathinfo($this->praAlleSha2[$this->rowidAppoggio]['FILEPATH'], PATHINFO_EXTENSION);
                        if (array_key_exists($this->rowidAppoggio, $this->praAlleSha2) == true) {
                            if ($this->praAlleSha2[$this->rowidAppoggio]['ROWID'] != 0) {
                                $delete_Info = 'Oggetto: Cancellazione allegato' . $this->praAlleSha2[$this->rowidAppoggio]['FILENAME'];
                                if (!$this->deleteRecord($this->PRAM_DB, 'PASDOC', $this->praAlleSha2[$this->rowidAppoggio]['ROWID'], $delete_Info)) {
                                    Out::msgStop("Attenzione", "Errore in cancellazione dell'allegato su PASDOC");
                                }
                            }
                            if (!@unlink($this->praAlleSha2[$this->rowidAppoggio]['FILEPATH'])) {
                                Out::msgStop("Gestione Acquisizioni", "Errore in cancellazione file.");
                            } else {
                                unset($this->praAlleSha2[$this->rowidAppoggio]);
                            }
                        }
                        $this->CaricaAllegatiPASSHA2();
                        $this->CaricaGriglia($this->gridAllegatiSha2, $this->praAlleSha2, "3");
                        $this->praLibAllegati->BlockCellGridAllegati($this->praAlleSha2, $this->gridAllegatiSha2);
                        break;
                    case $this->nameForm . '_ConfermaCancAlle':
                        $ext = pathinfo($this->praAlle[$this->rowidAppoggio]['FILEPATH'], PATHINFO_EXTENSION);
                        if (array_key_exists($this->rowidAppoggio, $this->praAlle) == true) {
                            if ($this->praAlle[$this->rowidAppoggio]['ROWID'] != 0) {
                                $delete_Info = 'Oggetto: Cancellazione allegato' . $this->praAlle[$this->rowidAppoggio]['FILENAME'];
                                if (!$this->deleteRecord($this->PRAM_DB, 'PASDOC', $this->praAlle[$this->rowidAppoggio]['ROWID'], $delete_Info)) {
                                    Out::msgStop("Attenzione", "Errore in cancellazione dell'allegato su PASDOC");
                                }
                            }
                            if (!@unlink($this->praAlle[$this->rowidAppoggio]['FILEPATH'])) {
                                Out::msgStop("Gestione Acquisizioni", "Errore in cancellazione file.");
                            } else {
                                $ultimo = $this->praLib->CheckUltimo($this->praAlle[$this->rowidAppoggio], $this->praAlle);
                                if ($ultimo == true) {
                                    $keyPadre = $this->praLib->CheckPadre($this->praAlle[$this->rowidAppoggio], $this->praAlle, 'SEQ');
                                    if (array_key_exists($keyPadre, $this->praAlle) == true) {
                                        unset($this->praAlle[$keyPadre]);
                                    }
                                }
                                unset($this->praAlle[$this->rowidAppoggio]);
                            }
                        }
                        $this->CaricaGriglia($this->gridAllegati, $this->praAlle, '3');
                        $this->praLibAllegati->BlockCellGridAllegati($this->praAlle, $this->gridAllegati);
                        break;

                    case $this->nameForm . '_ConfermaVaiPasso':
                        $model = 'praPasso';
                        $rowid = $_POST['rowid'];
                        $propas_rec = $this->praLib->GetPropas($rowid, "rowid");

                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $rowid;
                        $_POST['modo'] = "edit";
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnProPasso';
                        $_POST[$model . '_title'] = "Gestione Passo seq. " . $propas_rec['PROSEQ'] . " del Fascicolo " . substr($this->currGesnum, 4) . "/" . substr($this->currGesnum, 0, 4);

                        /* @var $praPasso praPasso */
                        itaLib::openForm($model);
                        $praPasso = itaModel::getInstance($model);
                        $praPasso->setEvent('openform');
                        $praPasso->parseEvent();
                        break;

                    case $this->nameForm . '_Apri':
                        $Proges_rec = $this->praLib->GetProges($this->currGesnum);
                        /*
                         * Check Fascicolo Archivistico Collegato
                         */
                        $MsgFasArch = '';
                        if ($Proges_rec['GESKEY']) {
                            if ($this->praLibFascicoloArch->CheckFascicoloConservato($this->currGesnum)) {
                                Out::msgStop("Attenzione", "Il Fascicolo Archivistico è in Conservazione. Non è possbile riaprire la pratica.");
                                break;
                            }
                            $MsgFasArch = "<br><br>Alla pratica è collegato un <b>Fascicolo Archivistico</b>, anche questo verrà riaperto.";
                        }
                        if ($Proges_rec['GESCLOSE'] != "@forzato@" && $Proges_rec['GESCLOSE'] != "") {
                            $Propas_rec = $this->praLib->GetPropas($Proges_rec['GESCLOSE'], "propak");
                            Out::msgQuestion("Apertura pratica!", "La pratica n. <b>" . $_POST[$this->nameForm . '_Numero_procedimento'] . "</b> è stata chiusa dal passo <br><b>" . $Propas_rec['PROSEQ'] . " - " . $Propas_rec['PRODPA'] . "</b><br>Vuoi andare al passo?" . $MsgFasArch, array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaVaiAlPasso', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaVaiAlPasso', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                            $this->rowidAppoggio = $Propas_rec['ROWID'];
                        } else {
                            Out::msgQuestion("ATTENZIONE!", "Sei sicuro di voler aprire la pratica n. " . $_POST[$this->nameForm . '_Numero_procedimento'] . "?" . $MsgFasArch, array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaApePra', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaApePra', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_ConfermaVaiAlPasso':
                        $propas_rec = $this->praLib->GetPropas($this->rowidAppoggio, "rowid");
                        $model = 'praPasso';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $this->rowidAppoggio;
                        $_POST['modo'] = "edit";
                        //$_POST['perms'] = $this->perms;
                        $_POST['pagina'] = $this->page;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnProPasso';
                        $_POST[$model . '_title'] = "Gestione Passo seq. " . $propas_rec['PROSEQ'] . " del Fascicolo " . substr($this->currGesnum, 4) . "/" . substr($this->currGesnum, 0, 4);
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_ConfermaApePra':
                        $_POST[$this->nameForm . '_PROGES']['GESDCH'] = "";
                        $_POST[$this->nameForm . '_PROGES']['GESCLOSE'] = "";
                        Out::valore($this->nameForm . '_PROGES[GESDCH]', "");
                        $dati['PROGES_REC'] = $_POST[$this->nameForm . '_PROGES'];
                        $dati['ANADES_REC'] = $_POST[$this->nameForm . '_ANADES'];
                        $dati['forzaChiusura'] = false;
                        $rowid = $this->aggiorna($dati);
                        $this->Dettaglio($rowid);
                        break;
                    case $this->nameForm . '_Torna':
                        break;
                    case $this->nameForm . '_ConfermaDaPratica':
                        if ($this->praPassi) {
                            praRic::praPassiSelezionati($this->praPassi, $this->nameForm, "expPra", "Scegli i passi da importare", "PROPAS");
                        } else {
                            Out::msgInfo("Importazione Passi", "Passi della pratica <b>$this->currGesnum</b> non trovati.");
                        }
                        break;
                    case $this->nameForm . '_ConfermaDaProc':
                        $Proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $dbsuffix = $this->praLib->getDbSuffix($Proges_rec['GESPRO']);
                        $this->pranumSel = $Proges_rec['GESPRO'];
                        $Itepas_tab = $this->caricaPassiItepas($this->pranumSel, $dbsuffix);
                        if ($Itepas_tab) {
                            praRic::praPassiSelezionati($Itepas_tab, $this->nameForm, "exp", "Scegli i passi da importare");
                        } else {
                            Out::msgInfo("Importazione Passi", "Passi del procedimento <b>" . $Proges_rec['GESPRO'] . "</b> non trovati.");
                        }
                        break;
                    case $this->nameForm . '_ConfermaDaAltraPratica':
                        $openModel = "praGestElenco";
                        $model = $openModel . "_searchDialog";
                        itaLib::openDialog($openModel, true, true, 'desktopBody', "", "", $model);
                        Out::setDialogTitle($openModel . "_searchDialog", "Ricerca Fascicoli Elettronici");
                        $objModel = itaModel::getInstance($openModel, $model);
                        $objModel->setReturnModel($this->nameForm);
                        $objModel->setReturnEvent('returnFromPraGestImportPassi');
                        $objModel->searchMode = true;
                        $objModel->setEvent('openform');
                        $objModel->parseEvent();
                        break;
                    case $this->nameForm . '_ConfermaImportAnagrafica':
                        $this->dataRegAppoggio = "";
                        praRic::praRicAnapra($this->nameForm, "Ricerca procedimenti", "importXml", " (PRATPR = 'ENDOPROCEDIMENTO' OR PRATPR = 'ENDOPROCEDIMENTOWRKF')", "", true);
                        break;
                    case $this->nameForm . '_ConfermaImportXml':
                        $this->ApriUploadImportXML();
                        break;
                    case $this->nameForm . '_CopiaPasso':
                        if ($this->praPassi) {
                            praRic::praPassiSelezionati($this->praPassi, $this->nameForm, "expPra", "Scegli i passi da importare", "PROPAS");
                        } else {
                            Out::msgInfo("Importazione Passi", "Passi della pratica <b>$this->currGesnum</b> non trovati.");
                        }
                        break;
                    case $this->nameForm . '_CopiaUltimo':
                        $propas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM='$this->currGesnum' ORDER BY PROSEQ", true);
                        $last_propas_rec = end($propas_tab);
                        $_POST = array();
                        $model = 'praPasso';
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
                    case $this->nameForm . '_ImportPassi':
                        $this->praPassiSel = array();
                        $maxSeq = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT MAX(PROSEQ) AS PROSEQ FROM PROPAS WHERE PRONUM='$this->currGesnum'", false);
                        $this->insertTo = $maxSeq['PROSEQ'];
                        if ($this->praPassi) {
                            Out::msgQuestion("Scegli il tipo di import desiderato!", "Importa Da", array(
                                'F8-File XML' => array('id' => $this->nameForm . '_ConfermaImportXml', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F4-Procedimento in corso' => array('id' => $this->nameForm . '_ConfermaDaProc', 'model' => $this->nameForm, 'shortCut' => "f4"),
                                'F6-Pratica in corso' => array('id' => $this->nameForm . '_ConfermaDaPratica', 'model' => $this->nameForm, 'shortCut' => "f6"),
                                'F5-Anagrafica procedimenti' => array('id' => $this->nameForm . '_ConfermaImportAnagrafica', 'model' => $this->nameForm, 'shortCut' => "f5"),
                                'F3-Altra Pratica' => array('id' => $this->nameForm . '_ConfermaDaAltraPratica', 'model' => $this->nameForm, 'shortCut' => "f3")
                                    )
                            );
                        } else {
                            Out::msgQuestion("Scegli il tipo di import desiderato!", "Importa Da", array(
                                'F8-File XML' => array('id' => $this->nameForm . '_ConfermaImportXml', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F4-Procedimento in corso' => array('id' => $this->nameForm . '_ConfermaDaProc', 'model' => $this->nameForm, 'shortCut' => "f4"),
                                'F5-Anagrafica procedimenti' => array('id' => $this->nameForm . '_ConfermaImportAnagrafica', 'model' => $this->nameForm, 'shortCut' => "f5"),
                                'F3-Altra Pratica' => array('id' => $this->nameForm . '_ConfermaDaAltraPratica', 'model' => $this->nameForm, 'shortCut' => "f3")
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_Protocolla': // Protocollo il Fascicolo
                        if ($_POST[$this->nameForm . '_Numero_prot'] == 0 && (int) $_POST[$this->nameForm . '_Anno_prot'] < 1) {
                            if ($_POST[$this->nameForm . '_PROGES']['GESRES'] == "") {
                                Out::msgStop("Protocollazione Pratica", "Responsabile Pratica non Presente");
                                break;
                            }
                            $Ananom_recValido = $this->praLib->GetAnanom($_POST[$this->nameForm . '_PROGES']['GESRES']);
                            if (!$Ananom_recValido) {
                                Out::msgStop("Protocollazione Pratica", "Responsabile Pratica non valido");
                                break;
                            }

                            $this->anadesProt = array();
                            $dati['PROGES_REC'] = $_POST[$this->nameForm . '_PROGES'];
                            $dati['forzaChiusura'] = false;
                            $ret_aggiungi = $this->aggiorna($dati);
                            if ($ret_aggiungi == 0) {
                                Out::msgStop("Errore", "Aggiornamento dati non riuscito. Controllare.");
                                break;
                            }

                            /*
                             * Prendo il tipo protocollo
                             */
                            $tipoProt = $this->praLib->getTipoProtocollo($this->currGesnum);

                            /*
                             * chiede la conferma prima della protocollazione
                             */
                            $evento = "ConfermaProtocollazione";

                            /*
                             * se presente un solo soggetto la procedura rimane invariata
                             * se presenti più soggetti cambia l'evento e viene chiesto il soggetto da scegliere
                             */
                            if (count($this->praSoggetti->GetSoggetti()) > 1) {
                                $evento = "ConfermaSoggetto";
                            }
                            Out::msgQuestion("ATTENZIONE!", "L'operazione protocollerà la pratica con procedura $tipoProt. Vuoi continuare?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaProtocollazione', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . "_$evento", 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_ConfermaSoggetto' :
                        $soggetti = $this->praSoggetti->GetSoggetti();
                        foreach ($soggetti as $key => $soggetto) {
                            $anaruo_rec = $this->praLib->GetAnaruo($soggetto['DESRUO']);
                            $soggetti[$key]['RUOLO'] = $anaruo_rec['RUODES'];
                        }
                        praRic::praRicSoggetti($soggetti, $this->nameForm);
                        break;
                    case $this->nameForm . '_ConfermaProtocollazione' :
                        if (!$this->ProtocollaPraticaArrivo()) {
                            break;
                        }
                        break;
                    case $this->nameForm . '_RimuoviNAnt':
                        Out::valore($this->nameForm . '_Gespre0', '');
                        Out::valore($this->nameForm . '_Gespre1', '');
                        Out::valore($this->nameForm . '_Gespre2', '');
                        Out::valore($this->nameForm . '_Gespre3', '');
                        break;
                    case $this->nameForm . '_RimuoviProtocolla':
                        Out::valore($this->nameForm . '_Numero_prot', '');
                        Out::valore($this->nameForm . '_Anno_prot', '');
                        Out::valore($this->nameForm . '_DataProtocollo', '');
                        Out::attributo($this->nameForm . '_Numero_prot', "readonly", '1');
                        Out::attributo($this->nameForm . '_Anno_prot', "readonly", '1');
                        Out::hide($this->nameForm . '_InviaProtocollo');
                        Out::hide($this->nameForm . '_GestioneProtocollo');
                        Out::hide($this->nameForm . '_DataProtocollo');
                        Out::hide($this->nameForm . '_DataProtocollo_lbl');
                        $proges_rec = $this->praLib->GetProges($_POST[$this->nameForm . '_PROGES']['ROWID'], 'rowid');
                        $Metadati = $this->praLib->GetMetadatiProges($proges_rec['GESNUM']);
                        $this->switchIconeProtocollo('', $Metadati);
                        $this->eqAudit->logEqEvent($this, array(
                            'Operazione' => eqAudit::OP_MISC_AUDIT,
                            'DB' => $this->PRAM_DB->getDB(),
                            'DSet' => 'PROGES',
                            'Estremi' => "Cancello protocollo tipo " . $Metadati['DatiProtocollazione']['TipoProtocollo']['value'] . " n. " . $Metadati['DatiProtocollazione']['Numero']['value'] . " anno. " . $Metadati['DatiProtocollazione']['Anno']['value'] . " della pratica $this->currGesnum"
                        ));

                        break;
                    case $this->nameForm . '_AltreFunzioni':
                        $arrayAzioni = $this->GetArrayAltreFunzioni();
                        if ($arrayAzioni) {
                            Out::msgQuestion("Funzioni Disponibili", "", $arrayAzioni, 'auto', 'auto', 'true', false, true, true);
                        }
                        break;
                    case $this->nameForm . '_VediEventoCommercio':
                        $compro_rec = $this->wcoLib->GetCompro($this->currGesnum, "propak");
                        $anaspa_rec = $this->praLib->GetAnaspa($_POST[$this->nameForm . "_PROGES"]['GESSPA'], 'codice');
                        if ($anaspa_rec["SPAENTECOMM"]) {
                            $wcoLib = new wcoLib($anaspa_rec["SPAENTECOMM"]);
                            $compro_rec = $wcoLib->GetCompro($this->currGesnum, "propak");
                        }
                        if (!$compro_rec) {
                            Out::msgInfo("Vedi Commercio", "La pratica SUAP non sembra più essere collegata all'evento commercio");
                            break;
                        }
                        $this->openCommercio($compro_rec['PROCOD'], "evento");
                        break;
                    case $this->nameForm . '_VediCommercio':
                        $anaspa_rec = $this->praLib->GetAnaspa($_POST[$this->nameForm . "_PROGES"]['GESSPA'], 'codice');
                        if ($anaspa_rec["SPAENTECOMM"]) {
                            $wcoLib = new wcoLib($anaspa_rec["SPAENTECOMM"]);
                            $comsua_rec = $wcoLib->getComsua($this->currGesnum, "pratica");
                        } else {
                            $comsua_rec = $this->wcoLib->GetComsua($this->currGesnum, "pratica");
                        }
                        if (!$comsua_rec) {
                            Out::msgInfo("Vedi Commercio", "La pratica SUAP non sembra più essere collegata al commercio");
                            break;
                        }
                        $this->openCommercio($comsua_rec['SUAPRO']);
                        break;
                    case $this->nameForm . '_CollegaCommercio':
                        $this->proctipaut_rec = array();
                        $dati['PROGES_REC'] = $_POST[$this->nameForm . '_PROGES'];
                        $dati['ANADES_REC'] = $_POST[$this->nameForm . '_ANADES'];
                        $dati['forzaChiusura'] = false;
                        $ret_aggiungi = $this->aggiorna($dati);
                        $anaspa_rec = $this->praLib->GetAnaspa($dati['PROGES_REC']['GESSPA'], 'codice');
                        if ($anaspa_rec["SPAENTECOMM"]) {
                            if ($this->remoteToken) {
                                $profiloUtente = proSoggetto::getProfileFromIdUtente();
                                $ananom_rec = $this->praLib->GetAnanom($profiloUtente['COD_ANANOM']);
                                $arrayMeta = unserialize($ananom_rec['NOMMETA']);
                                $utenteAggr = $this->praLib->GetUtenteAggregato($arrayMeta['Utenti'], $dati['PROGES_REC']['GESSPA']);
                                if ($utenteAggr == "") {
                                    Out::msgInfo("collegamento commercio", "Utente non configurato per l'aggregato <b>" . $anaspa_rec["SPADES"] . "</b><br>Per configurare l'utente entrare nell'anagrafica dipendenti.");
                                    break;
                                }
//
                                $ieDomain = $anaspa_rec["SPAENTECOMM"];
                                $ieMessage = 'Apertura definizione procedimento on-line. Ok per continuare.';
                                $ieUrl = $_SERVER['HTTP_REFERER'];
                                $retToken = $this->praLib->CheckItaEngineContextToken($this->remoteToken, $ieDomain);
                                if (!$retToken) {
                                    $html = "<span style=\"font-size:1.2em;\">La password richiesta, fa riferimento all'utente <b>$utenteAggr</b> nell'applicativo Commercio.</span>";
                                    $this->praLib->GetMsgInputPassword($this->nameForm, "Collega Commercio", "COLLEGACOMM", $html);
                                } else {
                                    Out::openIEWindow(array(
                                        "ieurl" => $ieUrl,
                                        "ietoken" => $this->remoteToken,
                                        "iedomain" => $ieDomain,
                                        "ieOpenMessage" => $ieMessage
                                            ), array(
                                        "model" => "menDirect",
                                        "menu" => "CO_HID",
                                        "prog" => "CO_SUAPEXT",
                                        "modo" => "collega",
                                        "pratica" => $this->currGesnum,
                                        "ditta" => App::$utente->getKey('ditta'),
                                        "accessreturn" => "",
                                    ));
                                }
                            } else {
                                $html = "<span style=\"font-size:1.2em;\">La password richiesta, fa riferimento all'utente <b>$utenteAggr</b> nell'applicativo Commercio.</span>";
                                $this->praLib->GetMsgInputPassword($this->nameForm, "Collega Commercio", "COLLEGACOMM", $html);
                            }
                        } else {
                            itaLib::openForm('wcoCommSuap', true);
                            $wcoCommSuap = itaModel::getInstance('wcoCommSuap');
                            $wcoCommSuap->setEvent('openform');
                            $wcoCommSuap->setModo('collegaCommercio');
                            $wcoCommSuap->setReturnEvent("returnCommSuap");
                            $wcoCommSuap->setReturnModel($this->nameForm);
                            $wcoCommSuap->setDitta(App::$utente->getKey('ditta'));
                            $wcoCommSuap->setPratica($this->currGesnum);
                            $wcoCommSuap->parseEvent();
                        }

                        if ($this->returnModel != '') {
                            $this->returnToParent();
                        } else {
                            if ($ret_aggiungi != '') {
                                $this->Dettaglio($ret_aggiungi);
                            }
                        }

                        break;
                    case $this->nameForm . '_CollegaDomandaBando':
                        $praLibGfm = new praLibGfm();
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        //identifico di che tipologia di bando si tratta: fiera o mercato in base ai dati aggiuntivi
                        $fl_mercato = $fl_pi = $fl_fiera = $fl_fiera_dec = false;

                        //se c'è il bando della fiera selezionata collego subito il bando
                        $prodag_fie_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$this->currGesnum' AND DAGKEY='DENOM_FIERA_BANDO' AND DAGVAL <> ''", false);
                        if (!$prodag_fie_rec) {
                            //cerco fiera pluriennale
                            $prodag_fie_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$this->currGesnum' AND DAGKEY='DENOM_FIERAP_BANDO' AND DAGVAL <> ''", false);
                        }
                        if (!is_numeric($prodag_fie_rec['DAGVAL'])) {
                            $prodag_fie_rec = array();
                        }
                        if ($prodag_fie_rec) {
                            if (!$praLibGfm->CollegaBandiFiere($this->praDati, $this->praDatiPratica, $this->currGesnum, $this->rowidFiera)) {
                                Out::msgStop("Errore", $praLibGfm->getErrMessage());
                                break;
                            }
                            Out::msgBlock($this->divGes, 2000, true, "Domanda inserita correttamente.");
                            $this->Dettaglio($proges_rec['ROWID']);
                            Out::broadcastMessage($this->nameForm, "GFMDOMANDADASUAP", array("GESNUM" => $this->currGesnum));
                            break;
                        }
                        //se c'è il bando del mercato selezionata collego subito il bando
                        $prodag_merc_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$this->currGesnum' AND DAGKEY='DENOM_MERCATO_BANDO' AND DAGVAL <> ''", false);
                        if (!$prodag_merc_rec) {
                            //cerco bando per posteggi isolati
                            $prodag_merc_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$this->currGesnum' AND DAGKEY='DENOM_PI_BANDO' AND DAGVAL <> ''", false);
                        }
                        if (!is_numeric($prodag_merc_rec['DAGVAL'])) {
                            $prodag_merc_rec = array();
                        }

                        if ($prodag_merc_rec) {
                            if (!$praLibGfm->CollegaBandiMercati($this->praDati, $this->praDatiPratica, $this->currGesnum, $this->rowidFiera)) {
                                Out::msgStop("Errore", $praLibGfm->getErrMessage());
                                break;
                            }
                            Out::msgBlock($this->divGes, 2000, true, "Domanda inserita correttamente.");
                            $this->Dettaglio($proges_rec['ROWID']);
                            Out::broadcastMessage($this->nameForm, "GFMDOMANDADASUAP", array("GESNUM" => $this->currGesnum));
                            break;
                        }

                        //se non sono stati selezionati nè il mercato nè la fiera faccio le ric
                        //ricMercato e Posteggi isolati
                        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='" . $this->currGesnum . "' AND DAGKEY='DENOM_MERCATO' AND DAGVAL <> ''";
                        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                        $denom_mercato = $prodag_rec['DAGVAL'];
                        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='" . $this->currGesnum . "' AND DAGKEY='MERCATO_NUMEROPOSTO' AND DAGVAL <> ''";
                        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                        $mercato_numeroposto = $prodag_rec['DAGVAL'];
                        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='" . $this->currGesnum . "' AND DAGKEY='POSTEGGIISOLATI_VIA' AND DAGVAL <> ''";
                        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                        $posteggiisolati_via = $prodag_rec['DAGVAL'];
                        if ($denom_mercato && $denom_mercato != '' && $mercato_numeroposto && $mercato_numeroposto != '') {
                            $fl_mercato = true;
                            gfmRic::gfmRicBandiMercati($this->nameForm, $proges_rec['GESDRI'], 'returnBandiMercati');
                            break;
                        }
                        if ($posteggiisolati_via && $posteggiisolati_via != '') {
                            $fl_pi = true;
                            gfmRic::gfmRicBandiMercati($this->nameForm, $proges_rec['GESDRI'], 'returnBandiMercati');
                            break;
                        }
                        //ricFiera o Fiera già decennale
                        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='" . $this->currGesnum . "' AND DAGKEY='DENOM_FIERA' AND DAGVAL <> ''";
                        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                        $denom_fiera = $prodag_rec['DAGVAL'];
                        if ($denom_fiera && $denom_fiera != '') {
                            $fl_pi = true;
                            gfmRic::gfmRicBandiFiere($this->nameForm, $proges_rec['GESDRI'], 'returnBandiFiere');
                            break;
                        }
                        Out::msgBlock($this->divGes, 2000, true, "Impossibile collegare la domanda.");
//                        $this->Dettaglio($proges_rec['ROWID']);
//                        Out::broadcastMessage($this->nameForm, "GFMDOMANDADASUAP", array("GESNUM" => $this->currGesnum));

                        break;
                    case $this->nameForm . '_CollegaFiere':
                        $praLibGfm = new praLibGfm();
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
//Controllo se sono state selezionate le fiere in fase di domanda, in caso le aggiungo
                        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$this->currGesnum' AND DAGKEY='DENOM_FIERA' AND DAGVAL <> ''", false);
                        if (!$prodag_rec) {
                            gfmRic::gfmRicAltreFiere($this->nameForm, $proges_rec['GESDRI'], 'returnAltreFiere');
                            break;
                        }
                        if (!$praLibGfm->CollegaFiere($this->praDati, $this->praDatiPratica, $this->currGesnum, $this->rowidFiera)) {
                            Out::msgStop("Errore", $praLibGfm->getErrMessage());
                            break;
                        }
                        Out::msgBlock($this->divGes, 2000, true, "Domande inserite correttamente.");
                        $this->Dettaglio($proges_rec['ROWID']);
                        Out::broadcastMessage($this->nameForm, "GFMDOMANDADASUAP", array("GESNUM" => $this->currGesnum));
                        break;
                    case $this->nameForm . '_VediFiere':
                        gfmRic::gfmRicFieresuap($_POST[$this->nameForm . '_PROGES']['ROWID'], $this->nameForm);
                        break;
                    case $this->nameForm . '_ScollegaFiere':
                        $msg = "<br><br><b>Attenzione!! Questa procedura toglierà il collegamento tra la domanda del SUAP e le eventuali domande inserite nella gestione delle fiere. Le domande non verranno in ogni caso cancellate, provvedere dalla gestione fiere.</b><br><br>";
                        $this->praLib->GetMsgInputPassword($this->nameForm, "Sblocco Comunicazione in Partenza", "FIERE", $msg);
                        break;
                    case $this->nameForm . "_VediDomandaBandoF":
                        gfmRic::gfmRicFieresuap($_POST[$this->nameForm . '_PROGES']['ROWID'], $this->nameForm, "returnFieresuapBandoF");
                        break;
                    case $this->nameForm . "_VediDomandaBandoM":
                        gfmRic::gfmRicMercasuap($_POST[$this->nameForm . '_PROGES']['ROWID'], $this->nameForm, "returnFieresuapBandoM");
                        break;
                    case $this->nameForm . "_ScollegaDomandaBando":
                        $msg = "<br><br><b>Attenzione!! Questa procedura toglierà il collegamento tra la domanda del SUAP e la domanda inserita nella gestione delle fiere. La domanda non verrà in ogni caso cancellata, provvedere dalla gestione fiere.</b><br><br>";
                        $this->praLib->GetMsgInputPassword($this->nameForm, "Sblocco Comunicazione in Partenza", "BANDI", $msg);
                        break;
                    case $this->nameForm . '_VediGiustificazione':
                    case $this->nameForm . '_VediScambioPosto':
                        $praLibGfm = new praLibGfm();
                        $Anaditta_rec = $praLibGfm->GetAnaditta($this->currGesnum);
                        if (!$Anaditta_rec) {
                            Out::msgStop("Errore", $praLibGfm->getErrMessage());
                        }
                        $model = 'gfmAnaditte';
                        $_POST['rowidDitta'] = $Anaditta_rec['ROWID'];
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_GiustificaAssenze':
                        $praLibGfm = new praLibGfm();
                        if (!$praLibGfm->GiustificaAssenze($this->currGesnum)) {
                            Out::msgStop("Errore", $praLibGfm->getErrMessage());
                            break;
                        }
                        break;
                    case $this->nameForm . '_ScambioPosto':
                        $praLibGfm = new praLibGfm();
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        //Controllo se sono state selezionateil mercato in fase di domanda,
                        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$this->currGesnum' AND DAGKEY='DENOM_MERCATO' AND DAGVAL <> ''", false);
                        if ($prodag_rec) {
                            // vado a cercare il mercato
                            // se non lo trovo riapro la ric 
                            gfmRic::gfmRicAltriMercati($this->nameForm, $proges_rec['GESDRI'], 'returnSelezionaMercato');
                            break;
                        }
                        gfmRic::gfmRicAltriMercati($this->nameForm, $proges_rec['GESDRI'], 'returnSelezionaMercato');
                        break;
                    case $this->nameForm . "_CollegaPermessiZTL":
                        $praLibZTL = new praLibZTL();
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        //Controllo se sono state selezionate le fiere in fase di domanda, in caso le aggiungo
                        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$this->currGesnum' AND DAGKEY='TipoPermesso' AND DAGVAL <> ''", false);
                        if (!$prodag_rec) {
                            Out::msgStop("Attenzione", "Non risulta associato un tipo di permesso");
                            break;
                        }
                        if (!$praLibZTL->CollegaPermessi($this->praDati, $this->praDatiPratica, $this->currGesnum)) {
                            Out::msgStop("Errore", $praLibZTL->getErrMessage());
                            break;
                        }
                        Out::msgBlock($this->divGes, 2000, true, "Domanda inserita correttamente.");
                        $this->Dettaglio($proges_rec['ROWID']);
                        Out::broadcastMessage($this->nameForm, "ZTLDOMANDADASUAP", array("GESNUM" => $this->currGesnum));
                        break;

                    case $this->nameForm . "_RinnovaPermessiZTL":
                        $praLibZTL = new praLibZTL();
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        if (!$praLibZTL->RinnovaPermessi($this->praDati, $this->praDatiPratica, $this->currGesnum)) {
                            Out::msgStop("Errore", $praLibZTL->getErrMessage());
                            break;
                        }
                        $this->Dettaglio($proges_rec['ROWID']);
                        Out::msgBlock($this->divGes, 2000, true, "Domanda inserita correttamente.");
                        Out::broadcastMessage($this->nameForm, "ZTLDOMANDADASUAP", array("GESNUM" => $this->currGesnum));
                        break;
                    case $this->nameForm . '_VediPermessiZTL':
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $numero = intval(substr($proges_rec['GESPRA'], 4));
                        ztlRic::ztlRicIsolaDomanda($numero, $proges_rec['GESDRI'], $this->nameForm);
                        break;
                    case $this->nameForm . '_VariaTargheZTL':
                        $praLibZTL = new praLibZTL();
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        if (!$praLibZTL->VariaTarghe($this->praDati, $this->praDatiPratica, $this->currGesnum)) {
                            Out::msgStop("Attenzione", $praLibZTL->getErrMessage());
                            break;
                        }
                        $this->Dettaglio($proges_rec['ROWID']);
                        Out::msgBlock($this->divGes, 4000, true, "Targhe variate correttamente. Controlla il permesso e verifica l'invio in lista bianca");
                        break;
                    case $this->nameForm . '_VediVariazioniZTL':
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $numero = intval(substr($proges_rec['GESPRA'], 4));
                        ztlRic::ztlRicIsolaVariazioni($proges_rec['GESNUM'], $this->nameForm);
                        break;
                    case $this->nameForm . '_returnPasswordCOLLEGACOMM':
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA'], 'codice');
                        $profiloUtente = proSoggetto::getProfileFromIdUtente();
                        $ananom_rec = $this->praLib->GetAnanom($profiloUtente['COD_ANANOM']);
                        $arrayMeta = unserialize($ananom_rec['NOMMETA']);
                        $utenteAggr = $this->praLib->GetUtenteAggregato($arrayMeta['Utenti'], $proges_rec['GESSPA']);
//
                        $ieDomain = $anaspa_rec["SPAENTECOMM"];
                        $ieMessage = 'Apertura definizione procedimento on-line. Ok per continuare.';
                        $ieUrl = $_SERVER['HTTP_REFERER'];
                        $ieToken = $this->praLib->GetContextToken($utenteAggr, $_POST[$this->nameForm . '_password'], $ieDomain);
                        if (!$ieToken) {
                            Out::msgStop("Collegamento al Commercio", $this->praLib->getErrMessage());
                            break;
                        }
                        $this->remoteToken = $ieToken;
                        Out::openIEWindow(
                                array(
                                    "ieurl" => $ieUrl,
                                    "ietoken" => $ieToken,
                                    "iedomain" => $ieDomain,
                                    "ieOpenMessage" => $ieMessage
                                ), array(
                            "model" => "menDirect",
                            "menu" => "CO_HID",
                            "prog" => "CO_SUAPEXT",
                            "modo" => "collega",
                            "pratica" => $this->currGesnum,
                            "ditta" => App::$utente->getKey('ditta'),
                            "accessreturn" => "",
                                )
                        );
                        break;
                    case $this->nameForm . '_returnPasswordVEDICOMMEVT':
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA'], 'codice');
                        $wcoLib = new wcoLib($anaspa_rec["SPAENTECOMM"]);
                        $compro_rec = $wcoLib->getCompro($this->currGesnum, "propak");

                        $profiloUtente = proSoggetto::getProfileFromIdUtente();
                        $ananom_rec = $this->praLib->GetAnanom($profiloUtente['COD_ANANOM']);
                        $arrayMeta = unserialize($ananom_rec['NOMMETA']);
                        $utenteAggr = $this->praLib->GetUtenteAggregato($arrayMeta['Utenti'], $proges_rec['GESSPA']);

                        $ieToken = $this->praLib->GetContextToken($utenteAggr, $_POST[$this->nameForm . '_password'], $anaspa_rec["SPAENTECOMM"]);
                        if (!$ieToken) {
                            Out::msgStop("Collegamento al Commercio", $this->praLib->getErrMessage());
                            break;
                        }
                        $this->remoteToken = $ieToken;
                        $this->openCommercio($compro_rec['PROCOD'], "evento");
                        break;
                    case $this->nameForm . '_returnPasswordVEDICOMM':
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA'], 'codice');
                        $wcoLib = new wcoLib($anaspa_rec["SPAENTECOMM"]);
                        $comsua_rec = $wcoLib->getComsua($this->currGesnum, "pratica");

                        $profiloUtente = proSoggetto::getProfileFromIdUtente();
                        $ananom_rec = $this->praLib->GetAnanom($profiloUtente['COD_ANANOM']);
                        $arrayMeta = unserialize($ananom_rec['NOMMETA']);
                        $utenteAggr = $this->praLib->GetUtenteAggregato($arrayMeta['Utenti'], $proges_rec['GESSPA']);

                        $ieToken = $this->praLib->GetContextToken($utenteAggr, $_POST[$this->nameForm . '_password'], $anaspa_rec["SPAENTECOMM"]);
                        if (!$ieToken) {
                            Out::msgStop("Collegamento al Commercio", $this->praLib->getErrMessage());
                            break;
                        }
                        $this->remoteToken = $ieToken;
                        $this->openCommercio($comsua_rec['SUAPRO']);
                        break;
                    case $this->nameForm . '_returnPasswordAPRIRICCOMM':
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA'], 'codice');
                        $wcoLib = new wcoLib($anaspa_rec["SPAENTECOMM"]);
                        $profiloUtente = proSoggetto::getProfileFromIdUtente();
                        $ananom_rec = $this->praLib->GetAnanom($profiloUtente['COD_ANANOM']);
                        $arrayMeta = unserialize($ananom_rec['NOMMETA']);
                        $utenteAggr = $this->praLib->GetUtenteAggregato($arrayMeta['Utenti'], $proges_rec['GESSPA']);
//
                        $ieDomain = $anaspa_rec["SPAENTECOMM"];
                        $ieMessage = 'Apertura definizione procedimento on-line. Ok per continuare.';
                        $ieUrl = $_SERVER['HTTP_REFERER'];
                        $ieToken = $this->praLib->GetContextToken($utenteAggr, $_POST[$this->nameForm . '_password'], $ieDomain);
                        if (!$ieToken) {
                            Out::msgStop("Apri ricerca Commercio", $this->praLib->getErrMessage());
                            break;
                        }
                        $this->remoteToken = $ieToken;
                        Out::openIEWindow(
                                array(
                                    "ieurl" => $ieUrl,
                                    "ietoken" => $ieToken,
                                    "iedomain" => $ieDomain,
                                    "ieOpenMessage" => $ieMessage
                                ), array(
                            "model" => "menDirect",
                            "menu" => "CO_HID",
                            "prog" => "CO_SUAPEXT",
                            "modo" => "apriRicCommercio",
                            "soggetto" => $this->soggComm,
                            "ditta" => App::$utente->getKey('ditta'),
                            "accessreturn" => "",
                                )
                        );
                        break;
                    case $this->nameForm . '_returnPasswordVEDIANADITTE':
                        $ieDomain = $this->soggFiere['ENTE'];
                        $ieMessage = 'Apertura definizione procedimento on-line. Ok per continuare.';
                        $ieUrl = $_SERVER['HTTP_REFERER'];
                        $ieToken = $this->praLib->GetContextToken($_POST[$this->nameForm . '_utente'], $_POST[$this->nameForm . '_password'], $ieDomain);
                        if (!$ieToken) {
                            Out::msgStop("Apri Anagrafica Ditte", $this->praLib->getErrMessage());
                            break;
                        }
                        $this->remoteToken = $ieToken;
                        Out::openIEWindow(
                                array(
                                    "ieurl" => $ieUrl,
                                    "ietoken" => $ieToken,
                                    "iedomain" => $ieDomain,
                                    "ieOpenMessage" => $ieMessage
                                ), array(
                            "model" => "menDirect",
                            "menu" => "GF_M_ANA",
                            "prog" => "gfmAnaditte",
                            "rowidDitta" => $this->soggFiere['ROWID'],
                            "accessreturn" => "",
                                )
                        );
                        break;
                    case $this->nameForm . '_returnPasswordFIERE':
                        if (!$this->praLib->CtrPassword($_POST[$this->nameForm . '_password'])) {
                            break;
                        }
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $sql = "SELECT * FROM FIERESUAP WHERE SUAPID = " . $proges_rec['ROWID'];
                        $fieresuap_rec = ItaDB::DBSQLSelect($this->gfmLib->getGAFIEREDB(), $sql, false);
                        $this->deleteRecord($this->gfmLib->getGAFIEREDB(), "FIERESUAP", $fieresuap_rec['ROWID'], "Scollega fiera pratica " . $this->currGesnum);
                        $this->Dettaglio($proges_rec['ROWID']);
                        break;
                    case $this->nameForm . '_returnPasswordBANDI':
                        if (!$this->praLib->CtrPassword($_POST[$this->nameForm . '_password'])) {
                            break;
                        }
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $sql = "SELECT * FROM FIERESUAP WHERE SUAPID = " . $proges_rec['ROWID'];
                        $fieresuap_rec = ItaDB::DBSQLSelect($this->gfmLib->getGAFIEREDB(), $sql, false);
                        $this->deleteRecord($this->gfmLib->getGAFIEREDB(), "FIERESUAP", $fieresuap_rec['ROWID'], "Scollega fiera pratica " . $this->currGesnum);
                        $this->Dettaglio($proges_rec['ROWID']);
                        break;
                    case $this->nameForm . '_consultaAnagrafe':
                        $cf = $_POST[$this->nameForm . "_ANADES"]["DESFIS"];
                        $_POST = array();
                        $model = 'utiVediAnel';
                        $_POST['event'] = 'openform';
                        $_POST['cf'] = $cf;
                        itaLib::openDialog($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_InserisciRubricaWS':
                        $this->inserisciRubricaWS();
                        break;
                    case $this->nameForm . '_ConfermaRubricaWS':

                        /*
                         * aggiorno i dati che prendo dalla rubrica del WS
                         */
                        $anades_rec = array();
                        $anades_rec['ROWID'] = $this->datiRubricaWS['rowidAnades'];
                        if ($this->datiRubricaWS['nome'] != '' && $this->datiRubricaWS['cognome'] != '') {
                            $anades_rec['DESNOM'] = $this->datiRubricaWS['nome'] . " " . $this->datiRubricaWS['cognome'];
                            Out::valore($this->nameForm . '_ANADES[DESNOM]', $this->datiRubricaWS['nome'] . " " . $this->datiRubricaWS['cognome']);
                        } else {
                            $anades_rec['DESNOM'] = $this->datiRubricaWS['ragioneSociale'];
                            Out::valore($this->nameForm . '_ANADES[DESNOM]', $this->datiRubricaWS['ragioneSociale']);
                        }
                        if ($this->datiRubricaWS['codiceFiscale'] != '') {
                            $anades_rec['DESFIS'] = $this->datiRubricaWS['codiceFiscale'];
                            Out::valore($this->nameForm . '_ANADES[DESFIS]', $this->datiRubricaWS['codiceFiscale']);
                        } else {
                            $anades_rec['DESFIS'] = $this->datiRubricaWS['partitaIva'];
                            Out::valore($this->nameForm . '_ANADES[DESFIS]', $this->datiRubricaWS['partitaIva']);
                        }
                        if (is_array($this->datiRubricaWS['email'])) {
                            $anades_rec['DESEMA'] = $this->datiRubricaWS['email'][0];
                            Out::valore($this->nameForm . '_ANADES[DESEMA]', $this->datiRubricaWS['email'][0]);
                        } else {
                            $anades_rec['DESEMA'] = $this->datiRubricaWS['email'];
                            Out::valore($this->nameForm . '_ANADES[DESEMA]', $this->datiRubricaWS['email']);
                        }
                        $anades_rec['DESIND'] = $this->datiRubricaWS['indirizzo'];
                        Out::valore($this->nameForm . '_ANADES[DESIND]', $this->datiRubricaWS['indirizzo']);
                        $anades_rec['DESCIT'] = $this->datiRubricaWS['citta'];
                        Out::valore($this->nameForm . '_ANADES[DESCIT]', $this->datiRubricaWS['citta']);
                        $anades_rec['DESCAP'] = $this->datiRubricaWS['cap'];
                        Out::valore($this->nameForm . '_ANADES[DESCAP]', $this->datiRubricaWS['cap']);
                        $anades_rec['DESPRO'] = $this->datiRubricaWS['prov'];
                        Out::valore($this->nameForm . '_ANADES[DESPRO]', $this->datiRubricaWS['prov']);
                        $update_Info = 'Oggetto : Aggiornamento anagrafica' . $anades_rec['ROWID'];
                        if (!$this->updateRecord($this->PRAM_DB, 'ANADES', $anades_rec, $update_Info)) {
                            Out::msgStop("Aggiornamento anagrafica", "Errore nell'aggiornamento anagrafica. Le procedura sarà interrotta");
                            break;
                        }
                        $this->idCorrispondente = $this->datiRubricaWS['codice'];
                        $this->ProtocolloICCS();
                        break;
                    case $this->nameForm . '_GestioneProtocollo':
                        itaLib::openForm("praGestProtocollo");
                        $praGestProt = itaModel::getInstance('praGestProtocollo');
                        $praGestProt->setReturnEvent("returnGestDest");
                        $praGestProt->setReturnModel($this->nameForm);
                        $praGestProt->setGesnum($this->currGesnum);
                        $praGestProt->setKeyPasso();
                        $praGestProt->setEvent('openform');
                        $praGestProt->parseEvent();
                        break;
                    case $this->nameForm . '_InviaProtocollo':
                        /*
                         * Controllo se mail già Inviata
                         */
                        $Proges_rec = $this->praLib->getProges($this->currGesnum);
                        $meta = unserialize($Proges_rec['GESMETA']);
                        if (isset($meta['DatiProtocollazione']['IdMailRichiesta']) && $meta['DatiProtocollazione']['IdMailRichiesta']['value']) {
                            $data = substr($meta['DatiProtocollazione']['DataInvio']['value'], 6, 2) . "/" . substr($meta['DatiProtocollazione']['DataInvio']['value'], 4, 2) . "/" . substr($meta['DatiProtocollazione']['DataInvio']['value'], 0, 4);
                            Out::msgQuestion("ATTENZIONE!", "Richiesta al protocollo inviatata in data $data.<br>Cosa vuoi fare?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaMail', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Reinvia Mail' => array('id' => $this->nameForm . '_ReinviaMailProtocollo', 'model' => $this->nameForm, 'shortCut' => "f5"),
                                'F3-Vedi Mail' => array('id' => $this->nameForm . '_VediMailProtocollo', 'model' => $this->nameForm, 'shortCut' => "f3")
                                    )
                            );
                            break;
                        }
                    case $this->nameForm . '_ReinviaMailProtocollo':
                        /*
                         * Recupero Dati per Invio mail
                         */
                        $praFascicolo = new praFascicolo($this->currGesnum);
                        $elementi = $praFascicolo->getElementiProtocollaPratica();
                        $allegati = $this->praAlle;
                        $dati = $this->GetDatiMailProtocollo($allegati, $elementi);

                        /*
                         * Invio Mail al Protocollo;
                         */
                        $this->praLib->InvioMailAlProtocollo($dati);
                        break;
                    case $this->nameForm . '_VediMailProtocollo':
                        $Proges_rec = $this->praLib->getProges($this->currGesnum);
                        $meta = unserialize($Proges_rec['GESMETA']);
                        $model = 'emlViewer';
                        $_POST['event'] = 'openform';
                        $_POST['codiceMail'] = $meta['DatiProtocollazione']['IdMailRichiesta']['value'];
                        $_POST['tipo'] = 'id';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_VediMail':
                        $pramail_rec = $this->praLib->GetPramailRecPratica($this->currGesnum);
                        if ($pramail_rec['TIPOMAIL'] == "KEYUPL") {
                            $pratPath = $this->praLib->SetDirectoryPratiche(substr($this->currGesnum, 0, 4), $this->currGesnum, 'PROGES', false);
                            $codiceMail = $pratPath . "/" . $pramail_rec['IDMAIL'] . ".eml";
                            $tipo = "file";
                        } elseif ($pramail_rec['TIPOMAIL'] == "KEYMAIL") {
                            $codiceMail = $pramail_rec['IDMAIL'];
                            $tipo = "id";
                        }
                        $model = 'emlViewer';
                        $_POST['event'] = 'openform';
                        $_POST['codiceMail'] = $codiceMail;
                        $_POST['tipo'] = $tipo;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_ConfermaImmobile':
                        $this->praImmobili->CaricaImmobile($_POST[$this->nameForm . "_rowid"], $_POST[$this->nameForm . "_PRAIMM"]);
                        $this->CaricaGriglia($this->gridImmobili, $this->praImmobili->getGriglia());
                        break;
                    case $this->nameForm . '_ValidaImmobile':
                        $this->praImmobili->ValidaImmobile($_POST[$this->nameForm . "_rowid"], $_POST[$this->nameForm . "_PRAIMM"], $this);
                        $this->praImmobili = praImmobili::getInstance($this->praLib, $this->currGesnum);
                        $this->CaricaGriglia($this->gridImmobili, $this->praImmobili->getGriglia());
                        Out::tabSetTitle($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCatastali", $this->praImmobili->getTabTitle());
                        break;
                    case $this->nameForm . '_ViewAlbero':
                        $this->sha2View = false;
                        $this->praAlleSha2 = array();
                        $this->CaricaAllegati($_POST[$this->nameForm . "_PROGES"]['GESNUM'], "");
                        $this->CaricaGriglia($this->gridAllegati, $this->praAlle, '3');
                        $this->ContaSizeAllegati($this->praAlle, "Totale");
                        $this->praLibAllegati->BlockCellGridAllegati($this->praAlle, $this->gridAllegati);
                        Out::show($this->nameForm . '_divGrigliaAllegati');
                        Out::hide($this->nameForm . '_divGrigliaAllegatiSha2');
                        Out::codice("resizeGrid('" . $this->nameForm . "_divGrigliaAllegati', false, true);");
                        break;
                    case $this->nameForm . '_AssegnaPratica':
                    case $this->nameForm . '_AssegnaPraticaButt':
                        $profilo = proSoggetto::getProfileFromIdUtente();
                        $curr_assegnazione = praFunzionePassi::getCurrAssegnazione($this->currGesnum, $profilo);
                        $propas_rec = $this->praLib->GetPropas($curr_assegnazione['ROWID'], "rowid");
                        $model = 'praAssegnaPraticaSimple';
                        itaLib::openForm($model);
                        /* @var $modelObj praAssegnaPraticaSimple */
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setPratica($this->currGesnum);
                        if ($propas_rec) {
                            $modelObj->setDaPortlet(true);
                            $modelObj->setRowidAppoggio($propas_rec['ROWID']);
                        }
                        $modelObj->setReturnModel($this->nameForm);
                        $modelObj->setReturnEvent('returnPraAssegnaPratica');
                        $modelObj->setEvent('openform');
                        $modelObj->parseEvent();
                        $modelObj->assegnaPratica();
                        break;
                    case $this->nameForm . '_RestituisciPraticaButt':
                        $propas_rec = $this->praLib->GetPropas($this->rowidAppoggio, "rowid");
                        $model = 'praAssegnaPraticaSimple';
                        itaLib::openForm($model);
                        /* @var $modelObj praAssegnaPraticaSimple */
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setDaPortlet(false);
                        $modelObj->setPratica($this->currGesnum);
                        $modelObj->setReturnModel($this->nameForm);
                        $modelObj->setReturnEvent('returnPraAssegnaPratica');
                        $modelObj->setEvent('openform');
                        $modelObj->parseEvent();
                        $modelObj->restituisciPratica();
                        break;
                    case $this->nameForm . '_InCaricoPraticaButt':
                        $propas_rec = $this->praLib->GetPropas($this->rowidAppoggio, "rowid");
                        $model = 'praAssegnaPraticaSimple';
                        itaLib::openForm($model);
                        /* @var $modelObj praAssegnaPraticaSimple */
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setDaPortlet(false);
                        $modelObj->setPratica($this->currGesnum);
                        $modelObj->setReturnModel($this->nameForm);
                        $modelObj->setReturnEvent('returnPraAssegnaPratica');
                        $modelObj->setEvent('openform');
                        $modelObj->parseEvent();
                        $modelObj->prendiInCarico();
                        break;
                    case $this->nameForm . '_ViewPerFile':
                        $this->sha2View = true;
                        $this->praAlle = array();
                        $this->CaricaAllegatiPASSHA2($proges_rec['GESNUM']);
                        $this->CaricaGriglia($this->gridAllegatiSha2, $this->praAlleSha2, "3");
                        $this->ContaSizeAllegati($this->praAlleSha2, "TotaleSha");
                        $this->praLibAllegati->BlockCellGridAllegati($this->praAlleSha2, $this->gridAllegatiSha2);
                        Out::hide($this->nameForm . '_divGrigliaAllegati');
                        Out::show($this->nameForm . '_divGrigliaAllegatiSha2');
                        Out::codice("resizeGrid('" . $this->nameForm . "_divGrigliaAllegatiSha2', false, true);");
                        break;
                    case $this->nameForm . '_ConfermaProtPrinc':
                        $retPrt = $this->lanciaProtocollaWS(true);
                        if ($retPrt['Status'] == "-1") {
                            Out::msgStop("Protocollazione Pratica", $retPrt['Message']);
                        }
                        break;
                    case $this->nameForm . '_AnnullaProtPrinc':
                        $retPrt = $this->lanciaProtocollaWS();
                        if ($retPrt['Status'] == "-1") {
                            Out::msgStop("Protocollazione Pratica", $retPrt['Message']);
                        }
                        break;
                    case $this->nameForm . "_ScegliCampi":
                        praRic::praRicItedag($this->nameForm);
                        break;
                    case $this->nameForm . '_ConfermaChiusura':
                        $praPasso = new praPasso();
                        foreach ($this->Propas as $Propas_rec) {
                            if ($Propas_rec['PROINI']) {
                                if ($Propas_rec['PROFIN'] == "") {
                                    $Propas_rec['PROFIN'] = date('Ymd');
                                    if (!$praPasso->SincronizzaRecordPasso($Propas_rec)) {
                                        break;
                                    }
                                }
                            }
                        }
                        TableView::reload($this->gridPassi);
                        break;
                    case $this->nameForm . '_ConfermaApriPadre':
                        $sql = "SELECT * FROM PROPAS WHERE PROPAK='" . $this->Propas_key . "'";
                        $Propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                        $Propas_rec['PROINI'] = date('Ymd');
                        $praPasso = new praPasso();
                        $praPasso->SincronizzaRecordPasso($Propas_rec);
                        TableView::reload($this->gridPassi);
                        break;
                    case $this->nameForm . '_StatoPasso_butt':
                        praRic::praRicAnastp(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), '', "STATOPASSO");
                        break;
                    case $this->nameForm . '_OpenEvento':
                        $portlet_id = "envCalendar";
                        include_once ITA_BASE_PATH . '/apps/Ambiente/envLibPortlet.class.php';
                        $envLibPortlet = new envLibPortlet();
                        if ($envLibPortlet->checkActivePortlet($portlet_id) === false) {
                            $envLibPortlet->caricaPortlet($portlet_id, false, true);
                        } else {
                            $arrayInfo = $envLibPortlet->getPortletInfo($portlet_id);
                            if ($arrayInfo['type'] == "portlet") {
                                Out::desktopTabSelect("ita-home");
                            } elseif ($arrayInfo['type'] == "app") {
                                Out::desktopTabSelect("envFullCalendar");
                            }
                        }
                        include_once ITA_BASE_PATH . '/lib/itaPHPCore/CalendarView.class.php';
                        $envCalendar = new CalendarView("envFullCalendar_fullCalendar");
                        $envCalendar->changeView("agendaWeek");
                        $dataScadenza = $_POST[$this->nameForm . "_PROGES"]['GESDSC'];
                        $dataScadenzaFormatted = substr($dataScadenza, 0, 4) . "-" . substr($dataScadenza, 4, 2) . "-" . substr($dataScadenza, 6, 2);
                        $envCalendar->gotoDate($dataScadenzaFormatted);
                        break;
                    case $this->nameForm . '_RimuoviEvento':
                        $proges_rec = $this->praLib->getProges($this->currGesnum);
                        $idCalendar = $this->praLib->DecodCalendar($proges_rec['ROWID'], "SUAP_PRATICA");
                        if ($idCalendar) {
                            $envLibCalendar = new envLibCalendar();
                            $env_calendar_rec = $envLibCalendar->getCalendar($idCalendar);
                            Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare l'evento dal calendario <b>" . $env_calendar_rec['TITOLO'] . "</b>?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancEvento', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancEvento', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        } else {
                            Out::valore($this->nameForm . "_PROGES[GESGIO]", "");
                            Out::valore($this->nameForm . "_PROGES[GESDSC]", "");
                            Out::valore($this->nameForm . "_Calendario", "");
                        }
                        break;
                    case $this->nameForm . '_ConfermaCancEvento':
                        $proges_rec = $this->praLib->getProges($this->currGesnum);
                        $idCalendar = $this->praLib->DecodCalendar($proges_rec['ROWID'], "SUAP_PRATICA");
                        $envLibCalendar = new envLibCalendar();
                        if (!$envLibCalendar->deleteEventApp("SUAP_PRATICA", $proges_rec['ROWID'], null, $idCalendar)) {
                            Out::msgInfo("Attenzione!!", "Errore cancellazione evento con classe SUAP_PRATICA e codice " . $proges_rec['ROWID']);
                            break;
                        }
                        Out::valore($this->nameForm . "_PROGES[GESGIO]", "");
                        Out::valore($this->nameForm . "_PROGES[GESDSC]", "");
                        Out::valore($this->nameForm . "_Calendario", "");
                        //
                        $proges_rec['GESGIO'] = "";
                        $proges_rec['GESDSC'] = "";
                        $update_Info = "Oggetto: Aggiornamento pratica: $this->currGesnum dopo cancellazione evento";
                        if (!$this->updateRecord($this->PRAM_DB, 'PROGES', $proges_rec, $update_Info)) {
                            Out::msgStop("Errore in Aggionamento", "Aggiornamento pratica $this->currGesnum Fallito.");
                        }
                        break;
                    case $this->nameForm . "_statoChiusura_butt":
                        $msg = "<b>Confermando verrà chiusa la pratica n. $this->currGesnum.<br>Se sei sicuro di procedere, scegli uno stato.</b>";
                        praRic::praRicAnastp($this->nameForm, "WHERE STPFLAG LIKE 'Chiusa%'", "CHIUDI", $msg);
                        break;
                    case $this->nameForm . "_ConfermaChiusuraFascicolo":
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $fascicoliSel[] = $proges_rec['ROWID'];
                        $retChiudi = $this->praLibChiusuraMassiva->ChiudiFascicoli($fascicoliSel, $_POST[$this->nameForm . "_dataChiusura"], $_POST[$this->nameForm . "_statoChiusura"]);
                        if (!$retChiudi) {
                            Out::msgStop("Chiusura Fascicoli", $this->praLibChiusuraMassiva->getErrMessage());
                        } else {
                            Out::closeCurrentDialog();
                            Out::msgInfo("Chiusura Fascicoli", "Chiusi con successo " . count($fascicoliSel) . " fascicoli.");
                        }
                        $this->Dettaglio($proges_rec['ROWID']);
                        break;
                    case $this->nameForm . "_ConfermaImportaDaPratica":
                        $this->CaricaPassiDaPratica();
                        break;

                    case $this->nameForm . '_CreaFasArch':
                        if (!$this->praLibFascicoloArch->ControlliFascicoloArchivistico($this->currGesnum)) {
                            Out::msgStop("Errore", "Parametri mancati per la creazione del fascicolo Archivistico.<br>" . $this->praLibFascicoloArch->getErrMessage());
                            break;
                        }
                        if (!$this->praLibFascicoloArch->CreazioneFascicoloArchivistico($this->currGesnum, $this)) {
                            Out::msgStop("Errore", "Errore in creazione Fascicolo Archivistico. " . $this->praLibFascicoloArch->getErrMessage());
                        } else {
                            $Geskey = $this->praLibFascicoloArch->getGeskeyCreato();
                            if ($Geskey) {
                                Out::msgInfo('Fascicolo Archivistico', "Il fascicolo archivistico è stato creato correttamente: $Geskey.");
                            }
                            $proges_rec = $this->praLib->GetProges($this->currGesnum);
                            $this->Dettaglio($proges_rec['ROWID']);
                        }
                        break;

                    case 'close-portlet':
                        Out::broadcastMessage($this->nameForm, "CLOSE_PRAGEST", array("GESNUM" => $this->currGesnum));
                        $this->returnToParent();
                        break;
                }
                break;
            case 'afterSaveCell':
                if ($this->praReadOnly == true) {
                    break;
                }
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        if (!$this->praAlle[$_POST['rowid']]) {
                            $this->CaricaGriglia($this->gridAllegati, $this->praAlle);
                            break;
                        }
                        $propas_rec = $this->praLib->GetPropas($pasdoc_rec['PASKEY'], "propak");
                        if ($_POST['cellname'] != "NOTE" && $_POST['cellname'] != "STATO") {
                            if ($propas_rec['PROVISIBILITA'] == "Protetto") {
                                $proges_rec = $this->praLib->GetProges($this->currGesnum);
                                if (!$this->praPerms->checkSuperUser($proges_rec)) {
                                    if ($this->praPerms->checkUtenteGenerico($propas_rec)) {
                                        TableView::setCellValue($this->gridAllegati, $_POST['rowid'], 'NOTE', $pasdoc_rec['PASNOT']);
                                        break;
                                    }
                                }
                            }
                        }
                        $this->praAlle = $this->praLibAllegati->ChangeNoteStato($this->praAlle, $_POST['rowid'], $_POST['cellname'], $this->sha2View, $_POST['value']);
                        $this->CaricaGriglia($this->gridAllegati, $this->praAlle);
                        $this->praLibAllegati->BlockCellGridAllegati($this->praAlle, $this->gridAllegati);
                        break;
                    case $this->gridAllegatiSha2:
                        $propas_rec = $this->praLib->GetPropas($pasdoc_rec['PASKEY'], "propak");
                        if ($_POST['cellname'] != "NOTE" && $_POST['cellname'] != "STATO") {
                            if ($propas_rec['PROVISIBILITA'] == "Protetto") {
                                $proges_rec = $this->praLib->GetProges($this->currGesnum);
                                if (!$this->praPerms->checkSuperUser($proges_rec)) {
                                    if ($this->praPerms->checkUtenteGenerico($propas_rec)) {
                                        TableView::setCellValue($this->gridAllegatiSha2, $_POST['rowid'], 'NOTE', $pasdoc_rec['PASNOT']);
                                        break;
                                    }
                                }
                            }
                        }
                        $this->praAlleSha2 = $this->praLibAllegati->ChangeNoteStato($this->praAlleSha2, $_POST['rowid'], $_POST['cellname'], $this->sha2View, $_POST['value']);
                        $this->CaricaGriglia($this->gridAllegatiSha2, $this->praAlleSha2, "3");
                        $this->praLibAllegati->BlockCellGridAllegati($this->praAlleSha2, $this->gridAllegatiSha2);
                        break;
                    case $this->gridDatiPratica:
                        switch ($_POST['cellname']) {
                            case "DAGSEQ":
                                $this->praDatiPratica[$_POST['rowid']]['DAGSEQ'] = $_POST['value'];
                                $this->ordinaSeqArrayDag();
                                break;
                            case "DAGVAL":
                                if ($this->praDatiPratica[$_POST['rowid']]['DAGTIP'] == "Foglio_catasto" || $this->praDatiPratica[$_POST['rowid']]['DAGTIP'] == "Sub_catasto") {
                                    $this->praDatiPratica[$_POST['rowid']]['DAGVAL'] = str_repeat("0", 4 - strlen(trim($_POST['value']))) . trim($_POST['value']);
                                } elseif ($this->praDatiPratica[$_POST['rowid']]['DAGTIP'] == "Particella_catasto") {
                                    $this->praDatiPratica[$_POST['rowid']]['DAGVAL'] = str_repeat("0", 5 - strlen(trim($_POST['value']))) . trim($_POST['value']);
                                } else {
                                    $this->praDatiPratica[$_POST['rowid']]['DAGVAL'] = $_POST['value'];
                                }
                                break;
                        }
                        $this->CaricaGriglia($this->gridDatiPratica, $this->praDatiPratica);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Gespre0':
                        if ($_POST[$this->nameForm . '_Gespre0']) {
                            $AnaserieArc_tab = $this->proLibSerie->GetSerie($_POST[$this->nameForm . '_Gespre0'], 'sigla', true);
                            if (!$AnaserieArc_tab) {
                                Out::msgStop("Attenzione", "Sigla Inesistente.");
                                Out::valore($this->nameForm . '_Gespre0', '');
                                Out::valore($this->nameForm . '_Gespre3', '');
                                break;
                            }
                            $result = count($AnaserieArc_tab);
                            if ($result > 1) {
                                $where = "WHERE " . $this->proLib->getPROTDB()->strUpper('SIGLA') . " = '" . strtoupper($AnaserieArc_tab[0]['SIGLA']) . "'";
                                proRic::proRicSerieArc($this->nameForm, $where, 'returnSerieArcAnt');
                                break;
                            }
                            Out::valore($this->nameForm . '_Gespre3', $AnaserieArc_tab[0]['CODICE']);
                            Out::valore($this->nameForm . '_Gespre0', $AnaserieArc_tab[0]['SIGLA']);
                            break;
                        }
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PROGES[GESEVE]':
                        $codice = $_POST[$this->nameForm . '_PROGES']['GESEVE'];
                        if ($codice) {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnaeventi($codice, "codice", "DETT", true);
                        }
                        break;
                    case $this->nameForm . '_PROGES[GESTIP]':
                        $codice = $_POST[$this->nameForm . '_PROGES']['GESTIP'];
                        if ($codice) {
                            $codice = str_pad($codice, 6, "0", STR_PAD_LEFT);

                            $this->DecodAnatip($codice, "codice", "GESTIP");
                        }
                        break;
                    case $this->nameForm . '_PROGES[GESSTT]':
                        $codice = $_POST[$this->nameForm . '_PROGES']['GESSTT'];
                        if ($codice) {
                            $this->DecodAnaset($codice, "codice", "GESSTT");
                        }
                        break;
                    case $this->nameForm . '_PROGES[GESATT]':
                        $codice = $_POST[$this->nameForm . '_PROGES']['GESATT'];
                        if ($codice) {
                            $this->DecodAnaatt($codice, "codice", "GESATT");
                        }
                        break;
                    case $this->nameForm . '_PROGES[GESRES]':
                        $codice = $_POST[$this->nameForm . '_PROGES']['GESRES'];
                        $Codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $retid = $this->nameForm . '_PROGES[GESRES]';
                        $this->DecodAnanom($Codice, $retid, 'codice');
                        break;
                    case $this->nameForm . '_PROGES[GESTSP]':
                        $codice = $_POST[$this->nameForm . '_PROGES']['GESTSP'];
                        if ($codice) {
                            $this->DecodAnatsp($codice);
                        } else {
                            Out::valore($this->nameForm . '_ANATSP[TSPDES]', "");
                        }
                        break;
                    case $this->nameForm . '_PROGES[GESPRO]':
                        $retid = $this->nameForm . '_PROGES[GESPRO]';
                        $codice = $_POST[$this->nameForm . '_PROGES']['GESPRO'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        //$this->DecodAnapra($_POST['rowData']['ID_ANAPRA'], $_POST['retid'], 'rowid', $this->dataRegAppoggio, $_POST['rowData']['ID_ITEEVT']);
                        $this->DecodAnapra($codice, $retid, 'codice', $_POST[$this->nameForm . '_PROGES']['GESDRE']);
                        break;
                    case $this->nameForm . '_PROGES[GESSPA]':
                        $codice = $_POST[$this->nameForm . '_PROGES']['GESSPA'];
                        if ($codice) {
                            $this->DecodAnaspa2($codice);
                        }
                        break;
                    case $this->nameForm . '_PRAIMM[CODICE]':
                        $codice = $_POST[$this->nameForm . '_PRAIMM']['CODICE'];
                        if ($codice) {
                            $this->DecodLegame($codice);
                        }
                        break;
                    case $this->nameForm . '_PRAIMM[FOGLIO]':
                        $codice = $_POST[$this->nameForm . '_PRAIMM']['FOGLIO'];
                        if ($codice) {
                            $codice = str_pad($codice, 4, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_PRAIMM[FOGLIO]', $codice);
                            $particella = str_pad($_POST[$this->nameForm . '_PRAIMM']['PARTICELLA'], 4, "0", STR_PAD_LEFT);
                            if ($codice && $particella) {
                                $this->DecodLegame("", "foglio", $codice, $particella);
                            }
                        }
                        break;
                    case $this->nameForm . '_PRAIMM[PARTICELLA]':
                        $codice = $_POST[$this->nameForm . '_PRAIMM']['PARTICELLA'];
                        if ($codice) {
                            $codice = str_pad($codice, 5, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_PRAIMM[PARTICELLA]', $codice);
                            $foglio = str_pad($_POST[$this->nameForm . '_PRAIMM']['FOGLIO'], 4, "0", STR_PAD_LEFT);
                            if ($foglio && $codice) {
                                $this->DecodLegame("", "foglio", $foglio, $codice);
                            }
                        }
                        break;
                    case $this->nameForm . '_PRAIMM[SUBALTERNO]':
                        $codice = $_POST[$this->nameForm . '_PRAIMM']['SUBALTERNO'];
                        if ($codice) {
                            $codice = str_pad($codice, 4, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_PRAIMM[SUBALTERNO]', $codice);
                        }
                        break;
                    case $this->nameForm . '_ANADES[DESCIT]':
                        $Comuni_rec = ItaDB::DBSQLSelect($this->COMUNI_DB, "SELECT * FROM COMUNI WHERE " . $this->COMUNI_DB->strUpper('COMUNE') . " ='"
                                        . addslashes(strtoupper($_POST[$this->nameForm . '_ANADES']['DESCIT'])) . "'", false);
                        if ($Comuni_rec) {
                            Out::valore($this->nameForm . '_ANADES[DESCAP]', $Comuni_rec['COAVPO']);
                            Out::valore($this->nameForm . '_ANADES[DESPRO]', $Comuni_rec['PROVIN']);
                        }
                        break;
                    case $this->nameForm . "_PROGES[GESGIO]":
                        if ($_POST[$this->nameForm . "_PROGES"]['GESGIO'] != 0) {
                            $anapra_rec = $this->praLib->GetAnapra($_POST[$this->nameForm . "_PROGES"]['GESPRO']);
                            if ($_POST[$this->nameForm . "_PROGES"]['GESDSC'] == "19700101")
                                $_POST[$this->nameForm . "_PROGES"]['GESDSC'] = "";
                            $arrayScadenza = $this->praLib->SincDataScadenza("PRATICA", $this->currGesnum, $_POST[$this->nameForm . "_PROGES"]['GESDSC'], $anapra_rec['PRAGIO'], $_POST[$this->nameForm . "_PROGES"]['GESGIO'], $_POST[$this->nameForm . "_PROGES"]['GESDRE'], true);
                            if (!$arrayScadenza) {
                                Out::msgStop("Sincronizzazione Scadenza", "Errore Sincronizzazione Data Scadenza.");
                            }
                            Out::valore($this->nameForm . "_PROGES[GESDSC]", $arrayScadenza['SCADENZA']);
                            Out::valore($this->nameForm . "_PROGES[GESGIO]", $arrayScadenza['GIORNI']);
                        } else {
                            Out::valore($this->nameForm . "_PROGES[GESDSC]", "");
                        }
                        break;
                    case $this->nameForm . "_PROGES[GESDSC]":
                        if ($_POST[$this->nameForm . "_PROGES"]['GESDSC'] != "") {
                            $anapra_rec = $this->praLib->GetAnapra($_POST[$this->nameForm . "_PROGES"]['GESPRO']);
                            if ($_POST[$this->nameForm . "_PROGES"]['GESDSC'] == "19700101")
                                $_POST[$this->nameForm . "_PROGES"]['GESDSC'] = "";
                            $arrayScadenza = $this->praLib->SincDataScadenza("PRATICA", $this->currGesnum, $_POST[$this->nameForm . "_PROGES"]['GESDSC'], $anapra_rec['PRAGIO'], $_POST[$this->nameForm . "_PROGES"]['GESGIO'], $_POST[$this->nameForm . "_PROGES"]['GESDRE'], true);
                            if (!$arrayScadenza) {
                                Out::msgStop("Sincronizzazione Scadenza", "Errore Sincronizzazione Data Scadenza.");
                            }
                            Out::valore($this->nameForm . "_PROGES[GESDSC]", $arrayScadenza['SCADENZA']);
                            Out::valore($this->nameForm . "_PROGES[GESGIO]", $arrayScadenza['GIORNI']);
                        } else {
                            Out::valore($this->nameForm . "_PROGES[GESGIO]", "");
                        }
                        break;
                }
                break;
            case 'returnSerieArc':
                $AnaserieArc_rec = $this->proLibSerie->GetSerie($_POST['retKey'], 'rowid');
                if ($AnaserieArc_rec) {
                    Out::valore($this->nameForm . '_ric_codiceserie', $AnaserieArc_rec['CODICE']);
                    Out::valore($this->nameForm . '_ric_siglaserie', $AnaserieArc_rec['SIGLA']);
                    Out::valore($this->nameForm . '_descRicSerie', $AnaserieArc_rec['DESCRIZIONE']);
                }
                break;
            case 'returnSerieArcAnt':
                $AnaserieArc_rec = $this->proLibSerie->GetSerie($_POST['retKey'], 'rowid');
                if ($AnaserieArc_rec) {
                    Out::valore($this->nameForm . '_Gespre3', $AnaserieArc_rec['CODICE']);
                    Out::valore($this->nameForm . '_Gespre0', $AnaserieArc_rec['SIGLA']);
                }
                break;
            case 'returnAltreFiere':
                if ($_POST['retKey']) {
                    $rowid_fiere_sel = array();
                    $rowid_fiere_sel = explode(",", $_POST['retKey']);
                }
                $arrSelezionate = array();
                foreach ($rowid_fiere_sel as $k => $rowid) {
                    $arrSelezionate[$rowid] = 1;
                }
                $praLibGfm = new praLibGfm();
                if (!$praLibGfm->CollegaFiere($this->praDati, $this->praDatiPratica, $this->currGesnum, $this->rowidFiera, $arrSelezionate)) {
                    Out::msgStop("Errore", $praLibGfm->getErrMessage());
                    break;
                }
                Out::msgBlock($this->divGes, 2000, true, "Domande inserite correttamente.");
                $proges_rec = $this->praLib->GetProges($this->currGesnum);
                $this->Dettaglio($proges_rec['ROWID']);
                Out::broadcastMessage($this->nameForm, "GFMDOMANDADASUAP", array("GESNUM" => $this->currGesnum));
                break;
            case 'returnBandiFiere':
                if ($_POST['retKey']) {
                    $rowid_fiere_sel = array();
                    $rowid_fiere_sel = explode(",", $_POST['retKey']);
                }
                $arrSelezionate = array();
                foreach ($rowid_fiere_sel as $k => $rowid) {
                    $arrSelezionate[$rowid] = 1;
                }
                $praLibGfm = new praLibGfm();
                if (!$praLibGfm->CollegaBandiFiere($this->praDati, $this->praDatiPratica, $this->currGesnum, $this->rowidFiera, $arrSelezionate)) {
                    Out::msgStop("Errore", $praLibGfm->getErrMessage());
                    break;
                }
                Out::msgBlock($this->divGes, 2000, true, "Domanda inserita correttamente.");
                $proges_rec = $this->praLib->GetProges($this->currGesnum);
                $this->Dettaglio($proges_rec['ROWID']);
                Out::broadcastMessage($this->nameForm, "GFMDOMANDADASUAP", array("GESNUM" => $this->currGesnum));
                break;
            case 'returnBandiMercati':
                if ($_POST['retKey']) {
                    $rowid_fiere_sel = array();
                    $rowid_fiere_sel = explode(",", $_POST['retKey']);
                }
                $arrSelezionate = array();
                foreach ($rowid_fiere_sel as $k => $rowid) {
                    $arrSelezionate[$rowid] = 1;
                }
                $praLibGfm = new praLibGfm();
                if (!$praLibGfm->CollegaBandiMercati($this->praDati, $this->praDatiPratica, $this->currGesnum, "", $arrSelezionate)) {
                    Out::msgStop("Errore", $praLibGfm->getErrMessage());
                    break;
                }
                Out::msgBlock($this->divGes, 2000, true, "Domanda inserita correttamente.");
                $proges_rec = $this->praLib->GetProges($this->currGesnum);
                $this->Dettaglio($proges_rec['ROWID']);
                Out::broadcastMessage($this->nameForm, "GFMDOMANDADASUAP", array("GESNUM" => $this->currGesnum));
                break;
            case 'returnIFrame':
                $proges_rec = $this->praLib->GetProges($this->currGesnum, 'codice');
                $this->Dettaglio($proges_rec['ROWID']);
                break;
            case 'returnSelezionaMercato':
                if ($_POST['retKey']) {
                    if (strstr($_POST['retKey'], ',')) {
                        Out::msgStop('ATTENZIONE', "E' possibile selezionare solo un mercato!<br>Procedura scampio posteggio non eseguita");
                        break;
                    }
                    $praLibGfm = new praLibGfm();
                    if (!$praLibGfm->ScambiaPosteggio($this->currGesnum, $_POST['retKey'])) {
                        Out::msgStop("Errore", $praLibGfm->getErrMessage());
                        break;
                    }
                }
                break;
            case 'returnFileFromTwain':
                $this->SalvaScanner();
                break;
//            case 'returnUploadMail':
//                $directMailFile = $_POST['uploadedFile']; //itaLib::createPrivateUploadPath() . "/" . $randName;
//                if (strtolower(pathinfo($directMailFile, PATHINFO_EXTENSION)) != "eml") {
//                    Out::msgInfo("Importazione File", "Il file da caricare deve essere un eml.");
//                    break;
//                }
//                $model = 'proElencoMail';
//                $_POST = array();
//                $_POST['event'] = 'openform';
//                $_POST['returnModel'] = 'praGest';
//                $_POST['returnEvent'] = 'returnElencoMail';
//                $_POST['modoFiltro'] = "DIRECT";
//                $_POST['directMailFile'] = $directMailFile;
//                itaLib::openForm($model);
//                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
//                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//                $model();
//                break;
            case 'returnColorpicker':
                if ($_POST[$this->gridAllegati]['gridParam']['selrow'] != 'null') {
                    $id = $this->praAlle['EvidenziaRow'];
                    unset($this->praAlle['EvidenziaRow']);
                    $this->praLibAllegati->ColorpickerAllegati($this->praAlle[$id]['ROWID'], $_POST['colorPicked']);
                    $this->CaricaAllegati($this->currGesnum);
                    $this->CaricaGriglia($this->gridAllegati, $this->praAlle, '3');
                } elseif ($_POST[$this->gridAllegatiSha2]['gridParam']['selrow'] != 'null') {
                    $id = $this->praAlleSha2['EvidenziaRow'];
                    unset($this->praAlleSha2['EvidenziaRow']);
                    $this->praLibAllegati->ColorpickerAllegati($this->praAlleSha2[$id]['ROWID'], $_POST['colorPicked']);
                    $this->CaricaAllegatiPASSHA2($this->currGesnum);
                    $this->CaricaGriglia($this->gridAllegatiSha2, $this->praAlleSha2, '3');
                } else {
                    Out::msgStop("Errore", "Allegato non evidenziato, allegato non individuato");
                }
                break;
//            case 'returnElencoMail':
//                $this->inizializzaForm();
//                $this->CaricaDaPec();
//                break;
            case 'returnAnapra':
                $evento = '';
                $this->DecodAnapra($_POST['rowData']['ID_ANAPRA'], $_POST['retid'], 'rowid', $this->dataRegAppoggio, $_POST['rowData']['ID_ITEEVT']);
                $this->dataRegAppoggio = null;
                break;
            case 'returnAnaeventi':
                $this->DecodAnaeventi($_POST['retKey'], 'rowid', $_POST['retid'], true);
                break;
            case 'returnAnatsp':
                $this->DecodAnatsp($_POST['retKey'], 'rowid');
                break;
            case 'returnAnaspa2':
                $this->DecodAnaspa2($_POST['retKey'], $_POST['retid'], 'rowid');
                break;
            case 'returnUnires':
                $this->DecodAnanom($_POST['retKey'], $_POST['retid'], 'rowid');
                break;
            case 'returnProPasso':
                $Proges_rec = $this->praLib->GetProges($this->currGesnum);
                $prasta_rec = $this->praLib->GetPrasta($Proges_rec['GESNUM'], 'codice');
                if ($Proges_rec['GESDCH'] == '') {
                    Out::show($this->nameForm . '_Chiudi');
                    Out::show($this->nameForm . '_ImportPassi');
                    Out::show($this->nameForm . '_Aggiorna');
                    Out::show($this->nameForm . '_Annulla');
                    Out::hide($this->nameForm . '_Apri');
                    Out::hide($this->nameForm . '_AggiornaStatistici');
                } else {
                    Out::show($this->nameForm . '_Apri');
                    Out::hide($this->nameForm . '_Chiudi');
                    Out::hide($this->nameForm . '_ImportPassi');
                    Out::hide($this->nameForm . '_Aggiorna');
                    Out::hide($this->nameForm . '_Annulla');
                    Out::show($this->nameForm . '_AggiornaStatistici');
                }

                Out::valore($this->nameForm . '_PROGES[GESDCH]', $Proges_rec['GESDCH']);
                Out::html($this->nameForm . '_PRASTA[STADES]', $prasta_rec['STADES']);
                //
                $this->praPassi = $this->praLib->caricaPassiBO($Proges_rec['GESNUM']);
                if (!$this->praPerms->checkSuperUser($Proges_rec)) {
                    $this->praPassi = $this->praPerms->filtraPassiView($this->praLib->caricaPassiBO($Proges_rec['GESNUM']));
                }
                $this->CaricaGriglia($this->gridPassi, $this->praPassi);
                //New
                $this->DisattivaCellStatoPasso();
                //
                $Filent_rec = $this->praLib->GetFilent(15);
                if ($Filent_rec['FILDE1'] == 1) {
                    $this->CaricaAllegatiPASSHA2($Proges_rec['GESNUM']);
                    $this->CaricaGriglia($this->gridAllegatiSha2, $this->praAlleSha2);
                } else {
                    $this->CaricaAllegati($Proges_rec['GESNUM']);
                    $this->CaricaGriglia($this->gridAllegati, $this->praAlle);
                }
                //
                $this->CaricaNote();

                /*
                 * Ricarico la subform dei dati aggiuntivi.
                 */

                /* @var $praCompDatiAggiuntivi praCompDatiAggiuntivi */
                $praCompDatiAggiuntivi = itaModel::getInstance('praCompDatiAggiuntivi', $this->praCompDatiAggiuntiviFormname);
                $praCompDatiAggiuntivi->openGestione($this->currGesnum);
                break;

            case 'returnItepas':
                switch ($_POST['retid']) {
                    case "importDaProc":
                        break;
                    default:
                        break;
                }
                break;
            case 'returnProges':
                if ($_POST['retid'] == "IMPORTAPASSI") {
                    $proges_rec = $this->praLib->GetProges($_POST['rowData']['ROWID'], 'rowid');
                    $this->progesSel = $proges_rec['GESNUM'];
                    $Propas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM = '$this->progesSel' AND PROPUB = 0 ORDER BY PROSEQ", true);
                    if (!$Propas_tab) {
                        Out::msgInfo("Attenzione!!!", "Endoprocedimenti per la pratica n. " . $proges_rec['GESNUM'] . " non trovati");
                        break;
                    }
                    praRic::praPassiSelezionati($Propas_tab, $this->nameForm, "expAltraPra", "Scegli gli endoprocedimenti da importare", "PROPAS");
                } else {
                    $this->DecodProges($_POST['rowData']['ROWID'], 'rowid');
                }
                break;
            case 'returnElencoReportDettaglio':
                $tabella_rec = $_POST['rowData'];
                $_POST = $_POST['retid'];
                $this->StampaReportFascicolo($_POST[$this->nameForm . "_PROGES"]['ROWID'], $tabella_rec['CODICE'], '0001');
                break;
            case 'returnUploadXML':
                $XMLpassi = $_POST['uploadedFile'];
                if (file_exists($XMLpassi)) {
                    if (pathinfo($XMLpassi, PATHINFO_EXTENSION) == "xml") {
                        if ($this->praLib->ImportXmlFilePropas($XMLpassi, $this->insertTo, $this->currGesnum)) {
                            if (!$this->praLib->ordinaPassi($this->currGesnum)) {
                                Out::msgStop("Errore", $this->praLib->getErrMessage());
                            }
                            $this->praPassi = $this->praLib->caricaPassiBO($this->currGesnum);
                            $this->CaricaGriglia($this->gridPassi, $this->praPassi, '1');
                        }
                    } else {
                        Out::msgStop("Errore", "File di importazione passi non è un xml.");
                        break;
                    }
                } else {
                    Out::msgStop("Errore", "Procedura di importazione passi interrotta per mancanza del file.");
                }
                break;
            case 'returnAcqrList':
                switch ($_POST['id']) {
                    case $this->gridAllegatiSha2:
                        foreach ($_POST['retList'] as $uplAllegato) {
                            $edit = "";
                            $ext = pathinfo($uplAllegato['FILEPATH'], PATHINFO_EXTENSION);
                            if (strtolower($ext) == "p7m") {
                                $edit = "<span class=\"ita-icon ita-icon-shield-blue-16x16\">Verifica Firma</span>";
                            } else if (strtolower($ext) == "zip") {
                                $edit = "<span class=\"ita-icon ita-icon-winzip-16x16\">estrai File Zip</span>";
                            }
                            $arrayGen['NAME'] = '<span style = "color:orange;">' . $uplAllegato['FILEORIG'] . '</span>';
                            $arrayGen['INFO'] = 'GENERALE';
                            $arrayGen['EDIT'] = $edit;
                            $arrayGen['NOTE'] = "File originale: " . $uplAllegato['FILEORIG'];
                            $arrayGen['EVIDENZIA'] = "<span class=\"ita-icon ita-icon-evidenzia-16x16\">Evidenzia Allegato</span>";
                            $arrayGen['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-16x16\">Blocca Allegato</span>";
//Valorizzo Array
                            $arrayGen['FILENAME'] = $uplAllegato['FILENAME'];
                            $arrayGen['FILEINFO'] = $uplAllegato['FILEINFO'];
                            $arrayGen['FILEPATH'] = $uplAllegato['FILEPATH'];
                            $arrayGen['FILEORIG'] = $uplAllegato['FILEORIG'];
                            $arrayGen['SIZE'] = $this->praLib->formatFileSize(filesize($arrayGen['FILEPATH']));
                            $arrayGen['PASLOCK'] = 0;
                            $arrayGen['ROWID'] = 0;
                            $this->praAlleSha2[] = $arrayGen;
                        }
                        $this->CaricaGriglia($this->gridAllegatiSha2, $this->praAlleSha2, "3");
                        $this->praLibAllegati->BlockCellGridAllegati($this->praAlleSha2, $this->gridAllegatiSha2);
                        break;
                    case $this->gridAllegati:
                        foreach ($this->praAlle as $allegato) {
                            if ($allegato['parent'] == 'seq_GEN' || ($allegato['SEQ'] == 'seq_GEN' && $allegato['parent'] == null)) {
                                $arrayGenerale[] = $allegato;
                            }
                        }

                        if (!$arrayGenerale) {
//Padre Allegati Generali
                            $chiave = count($this->praAlle) + 1;
                            $arrayGenerali['SEQ'] = 'seq_GEN';
                            $arrayGenerali['NAME'] = "Allegati Generali";
                            $arrayGenerali['level'] = 0;
                            $arrayGenerali['parent'] = null;
                            $arrayGenerali['isLeaf'] = 'false';
                            $arrayGenerali['expanded'] = 'true';
                            $arrayGenerali['loaded'] = 'true';
                            $this->praAlle[$chiave] = $arrayGenerali;
                        }
                        foreach ($_POST['retList'] as $uplAllegato) {
                            $edit = "";
                            $ext = pathinfo($uplAllegato['FILEPATH'], PATHINFO_EXTENSION);
                            if (strtolower($ext) == "p7m") {
//$edit = "<span class=\"ui-icon ui-icon-pencil\">Modifica Estensione</span>";
                                $edit = "<span class=\"ita-icon ita-icon-shield-blue-16x16\">Verifica Firma</span>";
                            } else if (strtolower($ext) == "zip") {
//$edit = "<span class=\"ui-icon ui-icon-copy\">Esplodi file zip</span>";
                                $edit = "<span class=\"ita-icon ita-icon-winzip-16x16\">estrai File Zip</span>";
                            }
                            $chiave = count($this->praAlle) + 1;
                            $arrayGen['SEQ'] = $chiave;
                            $arrayGen['NAME'] = '<span style = "color:orange;">' . $uplAllegato['FILEORIG'] . '</span>';
                            $arrayGen['INFO'] = 'GENERALE';
                            $arrayGen['EDIT'] = $edit;
                            $arrayGen['NOTE'] = "File originale: " . $uplAllegato['FILEORIG'];
                            $arrayGen['EVIDENZIA'] = "<span class=\"ita-icon ita-icon-evidenzia-16x16\">Evidenzia Allegato</span>";
                            $arrayGen['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-16x16\">Blocca Allegato</span>";
//Valorizzo Array
                            $arrayGen['FILENAME'] = $uplAllegato['FILENAME'];
                            $arrayGen['FILEINFO'] = $uplAllegato['FILEINFO'];
                            $arrayGen['FILEPATH'] = $uplAllegato['FILEPATH'];
                            $arrayGen['FILEORIG'] = $uplAllegato['FILEORIG'];
                            $arrayGen['SIZE'] = $this->praLib->formatFileSize(filesize($arrayGen['FILEPATH']));
                            $arrayGen['PASLOCK'] = 0;
                            $arrayGen['ROWID'] = 0;
                            $arrayGen['level'] = 1;
                            $arrayGen['parent'] = 'seq_GEN';
                            $arrayGen['isLeaf'] = 'true';
                            $this->praAlle[$chiave] = $arrayGen;
                        }

                        $this->CaricaGriglia($this->gridAllegati, $this->praAlle, '3');
                        $this->praLibAllegati->BlockCellGridAllegati($this->praAlle, $this->gridAllegati);
                        break;
                }
                break;
            case 'returnanapro':
                $anapro_rec = $this->proLib->GetAnapro($_POST['retKey'], 'rowid');
                $anno = substr($anapro_rec['PRONUM'], 0, 4);
                $numero = substr($anapro_rec['PRONUM'], 4);
                Out::valore($this->nameForm . '_Numero_prot', $numero);
                Out::valore($this->nameForm . '_Anno_prot', $anno);
                Out::valore($this->nameForm . '_PROGES[GESDRI]', $anapro_rec['PRODAR']);
                Out::valore($this->nameForm . '_PROGES[GESPAR]', $anapro_rec['PROPAR']);
                Out::valore($this->nameForm . '_PROGES[GESORA]', $anapro_rec['PROROR']);
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
                $arrayFile = $this->praLib->CaricaAllegatoDaZip($_POST['rowData'], $this->praAlle);
                if ($arrayFile == false) {
                    break;
                }
                if (isset($arrayFile["daFile"])) {
                    /*
                     * upload singolo file
                     */
                    $arrayFile['Allegato']['NAME'] = '<span style = "color:orange;">' . $arrayFile['Allegato']['NAME'] . '</span>';
                    $this->praAlle[count($this->praAlle) + 1] = $arrayFile["Allegato"];
                } else {
                    /*
                     * upload tutti i file della cartella
                     */
                    $this->praAlle = $arrayFile["Allegati"];
                }
                $this->CaricaGriglia($this->gridAllegati, $this->praAlle);
                $this->praLibAllegati->BlockCellGridAllegati($this->praAlle, $this->gridAllegati);
                break;
            case 'returnAntecedente';
                $proges_rec = $this->praLib->GetProges($_POST['retKey'], 'rowid');
                if ($proges_rec) {
                    $this->Dettaglio($proges_rec['ROWID']);
                }
                break;
//            case 'returnCtrRichieste':
//                $this->CaricaDaPec();
//                break;
//            case 'returnCtrRichiesteFO':
//                $datiAcquisizione = $_POST['datiAcquisizione'];
//                if (!$this->Dettaglio($datiAcquisizione['GESNUM'], false, "", "codice")) {
//                    $this->OpenRicerca();
//                    return false;
//                }
//                break;
            case 'returnPassiSel':
                switch ($_POST['retid']) {
                    case "exp":
                        $this->CaricaPassiDaProcedimento();
                        break;
                    case "expPra":
                    case "expAltraPra":
                        if (!$_POST['retKey']) {
                            Out::msgInfo("Importazione Passi", "Passi selezionati non trovati.");
                            break;
                        }
                        $this->praPassiSel = $_POST['retKey'];
                        Out::msgInput(
                                'Scegli i dati da importare', array(
                            array(
                                'label' => array('style' => "width:140px;", 'value' => 'Responsabile'),
                                'id' => $this->nameForm . '_importaResponsabile',
                                'name' => $this->nameForm . '_importaResponsabile',
                                'checked' => '',
                                'type' => 'checkbox'),
                            array(
                                'label' => array('style' => "width:140px;", 'value' => 'Allegati'),
                                'id' => $this->nameForm . '_importaAllegati',
                                'name' => $this->nameForm . '_importaAllegati',
                                'type' => 'checkbox',
                                'checked' => ''),
                            array(
                                'label' => array('style' => "width:140px;", 'value' => 'Destinatari'),
                                'id' => $this->nameForm . '_importaDestinatari',
                                'name' => $this->nameForm . '_importaDestinatari',
                                'type' => 'checkbox',
                                'checked' => ''),
                            array(
                                'label' => array('style' => "width:140px;", 'value' => 'Protocollo/id Doc.'),
                                'id' => $this->nameForm . '_importaProtocollo',
                                'name' => $this->nameForm . '_importaProtocollo',
                                'checked' => '',
                                'type' => 'checkbox'),
                            array(
                                'label' => array('style' => "width:140px;", 'value' => 'Valori dati aggiuntivi'),
                                'id' => $this->nameForm . '_importaValDatiAgg',
                                'name' => $this->nameForm . '_importaValDatiAgg',
                                'type' => 'checkbox'),
                                ), array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaImportaDaPratica', 'model' => $this->nameForm, 'shortCut' => "f5")
                                ), $this->nameForm . "_divGestione", "auto", "300px"
                        );
                        break;

                    case "passiAperti":
                        $rigaSel = $_POST[$this->gridPassi]['gridParam']['selrow'];
                        $model = 'praPasso';
                        $rowid = $_POST['retKey'];
                        $propas_rec = $this->praLib->GetPropas($rowid, "rowid");
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $rowid;
                        $_POST['modo'] = "edit";
                        $_POST['pagina'] = $this->page;
                        $_POST['praReadonly'] = $this->praReadOnly;
                        $_POST['selRow'] = $rigaSel;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnProPasso';
                        $_POST[$model . '_title'] = "Gestione Passo seq. " . $propas_rec['PROSEQ'] . " del Fascicolo " . substr($this->currGesnum, 4) . "/" . substr($this->currGesnum, 0, 4);
                        $_POST['passi'] = $this->praPassi;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                }
                break;
            case 'returnPraidc':
                $praidc_rec = $this->praLib->GetPraidc($_POST['retKey'], "rowid");
                $this->praDatiPratica[] = array(
                    "ROWID" => 0,
                    "DAGSEQ" => 0,
                    "DAGKEY" => $praidc_rec['IDCKEY'],
                    "DAGDES" => $praidc_rec['IDCDES'],
                    "DAGTIP" => $praidc_rec['IDCTIP'],
                    "DAGVAL" => ""
                );
                $this->ordinaSeqArrayDag();
                $this->CaricaGriglia($this->gridDatiPratica, $this->praDatiPratica, "3");
                break;
            case 'returnRubricaWS':
                if (!$_POST['rowData']) {
                    Out::msgStop("Attenzione", "Non è stata selezionata alcuna anagrafica. La protocollazione è stata interrotta");
                    break;
                }
                $this->idCorrispondente = $_POST['rowData']['codice'];
                $this->ProtocolloICCS();
                break;
            case 'returnMail':
                $Proges_rec = $this->praLib->GetProges($this->currGesnum);

                /* @var $emlMailBox emlMailBox */
                include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
                $emlMailBox = $this->praLib->getEmlMailBox($Proges_rec['GESTSP']);
                if (!$emlMailBox) {
                    Out::msgStop('Inoltro Mail', "Impossibile accedere alle funzioni dell'account: " . $this->refAccounts[0]['EMAIL']);
                    break;
                }

                /* @var $outgoingMessage emlOutgoingMessage */
                $outgoingMessage = $emlMailBox->newEmlOutgoingMessage();
                if (!$outgoingMessage) {
                    Out::msgStop('Inoltro Mail', "Impossibile creare un nuovo messaggio in uscita.");
                    break;
                }
                $outgoingMessage->setSubject($_POST['valori']['Oggetto']);
                $outgoingMessage->setBody($_POST['valori']['Corpo']);
                $outgoingMessage->setEmail($_POST['valori']['Email']);
                $outgoingMessage->setAttachments($_POST['allegati']);
                $mailArchivio_rec = $emlMailBox->sendMessage($outgoingMessage); //, $parametri);
                if ($mailArchivio_rec) {
                    $idmail = $mailArchivio_rec['IDMAIL'];
                    Out::msgInfo('Inoltro al Protocollo', "Inoltro Pratica n. $this->currGesnum per protocollazionegov eseguito correttamente");
                    if (!@is_dir(itaLib::getPrivateUploadPath())) {
                        if (!itaLib::createPrivateUploadPath()) {
                            Out::msgStop("Archiviazione Mail.", "Creazione ambiente di lavoro temporaneo fallita.");
                            $this->returnToParent();
                        }
                    }

                    /*
                     * Costruisco array metadati
                     */
                    $proges_rec = $this->praLib->getProges($this->currGesnum);
                    $meta['DatiProtocollazione']['TipoProtocollo']['value'] = "Manuale";

                    $meta['DatiProtocollazione']['IdMailRichiesta']['value'] = $idmail;
                    $meta['DatiProtocollazione']['IdMailRichiesta']['status'] = "1";
                    $meta['DatiProtocollazione']['IdMailRichiesta']['msg'] = "Richiesta Mail Protocollazione";

                    $meta['DatiProtocollazione']['proNum']['value'] = $this->currGesnum;
                    $meta['DatiProtocollazione']['proNum']['status'] = "1";
                    $meta['DatiProtocollazione']['proNum']['msg'] = "Numero pratica";

                    $meta['DatiProtocollazione']['Numero']['value'] = $_POST['valori']['Numero'];
                    $meta['DatiProtocollazione']['Numero']['status'] = "1";
                    $meta['DatiProtocollazione']['Numero']['msg'] = "Numero Protocollo";

                    $meta['DatiProtocollazione']['Oggetto']['value'] = $_POST['valori']['Corpo'];
                    $meta['DatiProtocollazione']['Oggetto']['status'] = "1";
                    $meta['DatiProtocollazione']['Oggetto']['msg'] = "Numero Protocollo";

                    $meta['DatiProtocollazione']['DataInvio']['value'] = date("Ymd");
                    $meta['DatiProtocollazione']['DataInvio']['status'] = "1";
                    $meta['DatiProtocollazione']['DataInvio']['msg'] = "Data invio mail al Protocollo";

                    $meta['DatiProtocollazione']['Anno']['value'] = $_POST['valori']['Anno'];
                    $meta['DatiProtocollazione']['Anno']['status'] = "1";
                    $meta['DatiProtocollazione']['Anno']['msg'] = "Anno Protocollo";

                    /*
                     * Aggiorno PROGES
                     */
                    $proges_rec['GESMETA'] = serialize($meta);
                    $update_Info = "Oggetto aggiorno pratica $this->currGesnum dopo invio protocollo";
                    if (!$this->updateRecord($this->PRAM_DB, 'PROGES', $proges_rec, $update_Info)) {
                        Out::msgStop("Errore in Aggionamento", "Aggiornamento pratica dopo invio Protocollo fallito.");
                        break;
                    }
                    $this->Dettaglio($proges_rec['ROWID']);
                } else {
                    Out::msgStop('Errore Mail', $emlMailBox->getLastMessage());
                    break;
                }
                break;
            case 'returnPraPasso':
                $Proges_rec = $this->praLib->GetProges($_POST['gesnum']);
                $this->Dettaglio($Proges_rec['ROWID']);
                break;
            case "returnSoggetti":
                $this->praSoggetti->setSoggetto($_POST['soggetto'], $_POST['rowid']);
                $this->CaricaGriglia($this->gridSoggetti, $this->praSoggetti->getGriglia());
                break;
            case "returnUnitaLocale":
                $this->praUnitaLocale->setSoggetto($_POST['soggetto'], $_POST['rowid']);
                $this->CaricaGriglia($this->gridUnitaLocale, $this->praUnitaLocale->getGriglia());
                break;
            case 'returnAnastp':
                if ($_POST['retid'] == "STATOPASSO") {
                    $anastp_rec = $this->praLib->GetAnastp($_POST['retKey'], 'rowid');
                    Out::valore($this->nameForm . '_StatoPasso', $anastp_rec['ROWID']);
                    Out::valore($this->nameForm . '_Stato1', $anastp_rec['STPDES']);
                } else if ($_POST['retid'] == "ANNULLA") {
                    $anastp_rec = $this->praLib->GetAnastp($_POST['retKey'], 'rowid');
                    Out::valore($this->nameForm . '_statoChiusura', $anastp_rec['ROWID']);
                    Out::valore($this->nameForm . '_descStato', $anastp_rec['STPDES']);
                    $proges_rec = $this->praLib->GetProges($this->currGesnum);
                    $praFascicolo = new praFascicolo($this->currGesnum);
                    if (!$praFascicolo->AnnullaChiudiPratica($_POST['retKey'], $_POST['retid'], date("Ymd"))) {
                        break;
                    }
                    $this->Dettaglio($proges_rec['ROWID']);
                } else {
                    $anastp_rec = $this->praLib->GetAnastp($_POST['retKey'], 'rowid');
                    Out::valore($this->nameForm . '_statoChiusura', $anastp_rec['ROWID']);
                    Out::valore($this->nameForm . '_descStato', $anastp_rec['STPDES']);
                }
                break;
            case 'returnAnatip':
                $this->DecodAnatip($_POST['retKey'], 'rowid', $_POST['retid']);
                break;
            case "returnAnaset":
                $this->DecodAnaset($_POST['retKey'], 'rowid', $_POST['retid']);
                break;
            case "returnAnaatt":
                $this->DecodAnaatt($_POST['retKey'], 'rowid', $_POST['retid']);
                break;
            case 'returnSoggettiPra' :
                $this->anadesProt = $_POST['rowData'];
                if (!$this->ProtocollaPraticaArrivo()) {
                    break;
                }
                break;
            case "returnLegameProc":
                Out::valore($this->nameForm . "_ProcPre", $_POST['rowData']['LICPRO']);
                Out::valore($this->nameForm . "_NumAutProc", $_POST['rowData']['LICAUT']);
                Out::valore($this->nameForm . "_DataAutProc", $_POST['rowData']['LICDAT']);
                Out::valore($this->nameForm . "_IndImpresa", $_POST['rowData']['LICEIN'] . " " . $_POST['rowData']['LICECI']);
                $ragSoc = $_POST['rowData']['LICRDE'];
                if ($_POST['rowData']['LICTPI']) {
                    $ragSoc = $_POST['rowData']['LICNOM'] . " " . $_POST['rowData']['LICCOG'];
                }
                Out::valore($this->nameForm . "_RagSoc", $ragSoc);
                break;
            case "returnProctipaut":
                Out::valore($this->nameForm . "_TipoProc", $_POST['rowData']['EVENTO']);
                Out::valore($this->nameForm . "_DescProc", $_POST['rowData']['EVENT']);
                break;
            case "returnCatLegame":
                $this->DecodLegame($_POST['retKey'], "rowid");
                break;
            case 'returnPraDettNote':
                $dati = array(
                    'OGGETTO' => $_POST['oggetto'],
                    'TESTO' => $_POST['testo'],
                    'CLASSE' => praNoteManager::NOTE_CLASS_PROGES,
                    'CHIAVE' => $this->currGesnum
                );
                $tipoNotifica = "CARICATA";
                if ($_POST['NON_AGGIORNA'] !== true) {
                    if ($_POST['NOTE_ROWID'] === '') {
                        $tipoNotifica = "INSERITA";
                        $this->noteManager->aggiungiNota($dati);
                    } else {
                        $tipoNotifica = "MODIFICATA";
                        $this->noteManager->aggiornaNota($_POST['NOTE_ROWID'], $dati);
                    }
                    $this->noteManager->salvaNote();
                }
                foreach ($_POST['destinatari'] as $destinatario) {
                    $this->inserisciNotifica($_POST['oggetto'], $destinatario, $tipoNotifica);
                }
                $this->CaricaNote();
                break;
            case 'returnFieresuap':
                $id = $_POST['rowData']['ROWID'];
                $fierecom_rec = $this->gfmLib->GetFierecom($id, '', 'rowid');
                $fiere_rec = $this->gfmLib->GetFiere($fierecom_rec['FIERA'], 'codice', $fierecom_rec['DATA'], $fierecom_rec['ASSEGNAZIONE']);
                $model = 'gfmFierePosti';
                $_POST = array();
                $_POST['event'] = 'openform';
                $_POST['rowidFierecom'] = $fierecom_rec['ROWID'];
                $_POST['data'] = $fiere_rec['DATA'];
                $_POST['fiera'] = $fiere_rec['FIERA'];
                $_POST['rowidFiera'] = $fiere_rec['ROWID'];
                $_POST['assegnazione'] = $fiere_rec['ASSEGNAZIONE'];
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
            case 'returnFieresuapBandoF':
                $id = $_POST['rowData']['ROWID'];
                $fierecom_rec = $this->gfmLib->GetFierecom($id, '', 'rowid');
                $fiere_rec = $this->gfmLib->GetFiere($fierecom_rec['FIERA'], 'codice', $fierecom_rec['DATA'], $fierecom_rec['ASSEGNAZIONE']);
                $model = 'gfmBandiFierePosti';
                $_POST = array();
                $_POST['event'] = 'openform';
                $_POST['rowidFierecom'] = $fierecom_rec['ROWID'];
                $_POST['data'] = $fiere_rec['DATA'];
                $_POST['fiera'] = $fiere_rec['FIERA'];
                $_POST['rowidFiera'] = $fiere_rec['ROWID'];
                $_POST['assegnazione'] = $fiere_rec['ASSEGNAZIONE'];
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
            case 'returnFieresuapBandoM':
                $id = $_POST['rowData']['ROWID'];
                $sqlMercacom = "SELECT * FROM MERCACOM WHERE ROWID = $id";
                $mercacom_rec = ItaDB::DBSQLSelect($this->gfmLib->getGAFIEREDB(), $sqlMercacom, false);
                $sqlBandoM = "SELECT * FROM BANDIM WHERE FIERA = '" . addslashes($mercacom_rec['FIERA']) . "' AND DATA = '" . $mercacom_rec['DATA'] . "' AND ASSEGNAZIONE = '" . $mercacom_rec['ASSEGNAZIONE'] . "' AND DECENNALE = '" . $mercacom_rec['DECENNALE'] . "'";
                $bandom_rec = ItaDB::DBSQLSelect($this->gfmLib->getGAFIEREDB(), $sqlBandoM, false);
                $model = 'gfmBandiMercatiPosti';
                $_POST = array();
                $_POST['event'] = 'openform';
                $_POST['rowidMercacom'] = $mercacom_rec['ROWID'];
                $_POST['data'] = $bandom_rec['DATA'];
                $_POST['mercato'] = $bandom_rec['FIERA'];
                $_POST['rowidBandoM'] = $bandom_rec['ROWID'];
                $_POST['assegnazione'] = $bandom_rec['ASSEGNAZIONE'];
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
            case 'returnIsolaDomanda':
                $id = $_POST['retKey'];
                $model = 'ztlInsIsola';
                $_POST = array();
                $_POST['event'] = 'openform';
                $_POST['rowidPermesso'] = $id;
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
            case 'returnIsolaVariazioni':
                $id = $_POST['retKey'];
                $model = 'ztlInsIsola';
                $_POST = array();
                $_POST['event'] = 'openform';
                $_POST['rowidVariazione'] = $id;
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
            case "returnTotAllegati";
                $propas_rec = $this->praLib->GetPropas($_POST['rowData']['PASKEY'], "propak");
                if ($propas_rec) {
                    foreach ($this->praPassi as $key => $Passi) {
                        if ($Passi['PROSEQ'] == $propas_rec['PROSEQ']) {
                            $rowid = $Passi['ROWID'];
                            break;
                        }
                    }
                    TableView::setSelection($this->gridPassi, $rowid);
                    Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_panePassi");
                    foreach ($this->praAlleSha2 as $key => $alle) {
                        TableView::setCellValue($this->gridAllegatiSha2, $key, 'NOTE', "", 'not-editable-cell', '', 'false');
                        TableView::setCellValue($this->gridAllegatiSha2, $key, 'STATO', "", 'not-editable-cell', '', 'false');
                    }
                }
                break;
            case "returnPraAssegnaPratica":
                $this->Dettaglio($_POST['gesnum'], false, "", "codice");
                break;
            case "returnPraGestAssegnazione":
                $this->Dettaglio($_POST['gesnum'], false, "", "codice");
                break;
            case "returnFiereSogg":
                $this->soggFiere = $_POST['rowData'];
                if ($this->soggFiere['ENTE'] != App::$utente->getKey('ditta')) {
                    if ($this->remoteToken) {
                        $ieDomain = $this->soggFiere['ENTE'];
                        $ieMessage = 'Apertura Anagrafica Ditte. Ok per continuare.';
                        $ieUrl = $_SERVER['HTTP_REFERER'];
                        $retToken = $this->praLib->CheckItaEngineContextToken($this->remoteToken, $ieDomain);
                        if (!$retToken) {
                            $html = "<span style=\"font-size:1.2em;\">Le credenziali richieste, fanno riferimento all'applicativo Fiere e Mercati per l'ente " . $this->soggFiere['ENTE'] . ".</span>";
                            $this->praLib->GetMsgInputCredenziali($this->nameForm, "Apri Anagrafica Ditte", "VEDIANADITTE", $html);
                        } else {
                            Out::openIEWindow(array(
                                "ieurl" => $ieUrl,
                                "ietoken" => $this->remoteToken,
                                "iedomain" => $ieDomain,
                                "ieOpenMessage" => $ieMessage
                                    ), array(
                                "model" => "menDirect",
                                "menu" => "GF_M_ANA",
                                "prog" => "gfmAnaditte",
                                "rowidDitta" => $this->soggFiere['ROWID'],
                                "accessreturn" => "",
                            ));
                        }
                    } else {
                        $html = "<span style=\"font-size:1.2em;\">Le credenziali richieste, fanno riferimento all'applicativo Fiere e Mercati per l'ente " . $this->soggFiere['ENTE'] . ".</span>";
                        $this->praLib->GetMsgInputCredenziali($this->nameForm, "Vedi Anagrafica Ditte", "VEDIANADITTE", $html);
                    }
                } else {
                    $model = 'gfmAnaditte';
                    $_POST['rowidDitta'] = $this->soggFiere['ROWID'];
                    $_POST['event'] = 'openform';
                    itaLib::openForm($model);
                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                    $model();
                }

                break;

            case 'returnGrigliePagamenti':
                $this->CaricaGriglia($this->gridOneri, $this->getOneri());
                $this->CaricaGriglia($this->gridPagamenti, $this->getPagamenti());
                $this->totalePagamenti();
                break;
            case 'returnAnadoctipreg':
                $Anadoctipreg_rec = $this->praLib->GetAnadoctipreg($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_TipologiaProg', $Anadoctipreg_rec['CODDOCREG']);
                break;
            case 'returnIteevt':
                $this->DecodEvento($_POST['retKey'], "rowid");
                break;
            case 'returnDestinazioni':
                if (is_array($_POST['destinazioni'])) {
                    $this->praAlle[$_POST['rowidAlle']]['PASDEST'] = serialize($_POST['destinazioni']);
                    $pasdoc_rec = $this->praLib->GetPasdoc($this->praAlle[$_POST['rowidAlle']]['ROWID'], "ROWID");
                    $pasdoc_rec['PASDEST'] = serialize($_POST['destinazioni']);
                    $update_Info = "Oggetto: Aggiorno l'allegato " . $pasdoc_rec['PASNOT'] . " della pratica " . $pasdoc_rec['PASKEY'];
                    if (!$this->updateRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $update_Info)) {
                        Out::msgStop("ATTENZIONE!", "Errore Aggiornamento Allegato " . $pasdoc_rec['PASNOT']);
                        break;
                    }
                    foreach ($_POST['destinazioni'] as $dest) {
                        $Anaddo_rec = $this->praLib->GetAnaddo($dest);
                        $strDest .= $Anaddo_rec['DDONOM'] . "<br>";
                    }
                    $this->praAlle[$_POST['rowidAlle']]['DESTINAZIONI'] = "<div class=\"ita-html\"><span style=\"color:red;\" class=\"ita-Wordwrap ita-tooltip\" title=\"$strDest\"><b>" . count($_POST['destinazioni']) . "</b></span></div>";
                }

                $this->CaricaGriglia($this->gridAllegati, $this->praAlle);
                break;
            case 'returnDestinazioniSha2':
                if (is_array($_POST['destinazioni'])) {
                    $this->praAlleSha2[$_POST['rowidAlle']]['PASDEST'] = serialize($_POST['destinazioni']);
                    $pasdoc_rec = $this->praLib->GetPasdoc($this->praAlleSha2[$_POST['rowidAlle']]['ROWID'], "ROWID");
                    $pasdoc_rec['PASDEST'] = serialize($_POST['destinazioni']);
                    $update_Info = "Oggetto: Aggiorno l'allegato " . $pasdoc_rec['PASNOT'] . " della pratica " . $pasdoc_rec['PASKEY'];
                    if (!$this->updateRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $update_Info)) {
                        Out::msgStop("ATTENZIONE!", "Errore Aggiornamento Allegato " . $pasdoc_rec['PASNOT']);
                        break;
                    }
                    foreach ($_POST['destinazioni'] as $dest) {
                        $Anaddo_rec = $this->praLib->GetAnaddo($dest);
                        $strDest .= $Anaddo_rec['DDONOM'] . "<br>";
                    }
                    $this->praAlleSha2[$_POST['rowidAlle']]['DESTINAZIONI'] = "<div class=\"ita-html\"><span style=\"color:red;\" class=\"ita-Wordwrap ita-tooltip\" title=\"$strDest\"><b>" . count($_POST['destinazioni']) . "</b></span></div>";
                }
                $this->CaricaGriglia($this->gridAllegatiSha2, $this->praAlleSha2);
                break;
            case 'returnFromPraGest':
                if ($_POST['rowid']) {
                    $this->DecodProges($_POST['rowid'], 'rowid');
                }
                break;
            case 'returnFromPraGestImportPassi':
                if ($_POST['rowid']) {
                    $proges_rec = $this->praLib->GetProges($_POST['rowid'], 'rowid');
                    $this->progesSel = $proges_rec['GESNUM'];
                    $Propas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT PROPAS.*," .
                                    $this->PRAM_DB->strConcat('ANANOM.NOMCOG', "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE
                             FROM PROPAS
                            LEFT OUTER JOIN ANANOM ON ANANOM.NOMRES=PROPAS.PRORPA
                            WHERE PRONUM = '$this->progesSel' AND PROPUB = 0 ORDER BY PROSEQ", true);
                    if (!$Propas_tab) {
                        Out::msgInfo("Attenzione!!!", "Endoprocedimenti per la pratica n. " . $proges_rec['GESNUM'] . " non trovati");
                        break;
                    }
                    $pratica = substr($proges_rec['GESNUM'], 4) . "/" . substr($proges_rec['GESNUM'], 0, 4);
                    praRic::praPassiSelezionati($Propas_tab, $this->nameForm, "expAltraPra", "Scegli gli endoprocedimenti da importare dalla pratica N. $pratica", "PROPAS");
                }
                break;
            case 'returnProgesByArray':
                if (!$_POST['rowData']) {
                    Out::msgInfo("Chiusura Fascicoli", "Selezionare almeno un fasciolo da chiudere.");
                    break;
                }
                foreach ($_POST['rowData'] as $proges_rec) {
                    $this->fascicoliSel[] = $proges_rec['ROWID'];
                }
                $this->getMsgInputChiudiFascicolo($this->nameForm . '_ConfermaChiusuraFascicoloMassiva');
                break;

            case $this->nameForm . '_VaiAPratica':
                include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';
                $proLibFascicolo = new proLibFascicolo();
                $id = $_POST['id'];
                if (!$proLibFascicolo->ApriFascicolo($this->nameForm, $id)) {
                    Out::msgStop("Attenzione", $proLibFascicolo->getErrMessage());
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_profilo');
        App::$utente->removeKey($this->nameForm . '_praPassi');
        App::$utente->removeKey($this->nameForm . '_praPagamenti');
        App::$utente->removeKey($this->nameForm . '_praPassiAssegnazione');
        App::$utente->removeKey($this->nameForm . '_praPassiPerAllegati');
        App::$utente->removeKey($this->nameForm . '_praAlle');
        App::$utente->removeKey($this->nameForm . '_praAlleSha2');
        App::$utente->removeKey($this->nameForm . '_currGesnum');
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_dataRegAppoggio');
        App::$utente->removeKey($this->nameForm . '_returnId');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_page');
        App::$utente->removeKey($this->nameForm . '_insertTo');
        App::$utente->removeKey($this->nameForm . '_passi');
        App::$utente->removeKey($this->nameForm . '_praDati');
        App::$utente->removeKey($this->nameForm . '_praDatiPratica');
        App::$utente->removeKey($this->nameForm . '_datiFiltrati');
        App::$utente->removeKey($this->nameForm . '_praComunicazioni');
        App::$utente->removeKey($this->nameForm . '_currAllegato');
        App::$utente->removeKey($this->nameForm . '_pranumSel');
        App::$utente->removeKey($this->nameForm . '_praImmobili');
        App::$utente->removeKey($this->nameForm . '_praSoggetti');
        App::$utente->removeKey($this->nameForm . '_praUnitaLocale');
        App::$utente->removeKey($this->nameForm . '_progesSel');
        App::$utente->removeKey($this->nameForm . '_datiFromWSProtocollo');
        App::$utente->removeKey($this->nameForm . '_codiceImm');
        App::$utente->removeKey($this->nameForm . '_noteManager');
        App::$utente->removeKey($this->nameForm . '_praReadOnly');
        App::$utente->removeKey($this->nameForm . '_sha2View');
        App::$utente->removeKey($this->nameForm . '_proctipaut_rec');
        App::$utente->removeKey($this->nameForm . '_rowidFiera');
        App::$utente->removeKey($this->nameForm . '_remoteToken');
        App::$utente->removeKey($this->nameForm . '_allegatiPrtSel');
        App::$utente->removeKey($this->nameForm . '_anadesProt');
        App::$utente->removeKey($this->nameForm . '_soggComm');
        App::$utente->removeKey($this->nameForm . '_soggFiere');
        App::$utente->removeKey($this->nameForm . '_ita_ext_cred');
        App::$utente->removeKey($this->nameForm . '_Propas');
        App::$utente->removeKey($this->nameForm . '_Propas_key');
        App::$utente->removeKey($this->nameForm . '_praAccorpati');
        App::$utente->removeKey($this->nameForm . '_praCompDatiAggiuntiviFormname');
        App::$utente->removeKey($this->nameForm . '_searchMode');
        App::$utente->removeKey($this->nameForm . '_praPassiSel');
        App::$utente->removeKey($this->nameForm . '_daTrasmissioni');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        if ($this->returnModel != '') {
            $_POST = array();
            $_POST['rowid'] = $this->returnId;
            $_POST['id'] = "proStepIter2_gridStepProced";
            $model = $this->returnModel;
            if (!$this->searchMode) {
                itaLib::openForm($model);
            }
            $objModel = itaModel::getInstance($model);
            $objModel->setEvent($this->returnEvent ?: 'dbClickRow');
            $objModel->parseEvent();
            Out::closeDialog($this->nameForm);
        } else {
            Out::show('menuapp');
        }
    }

    function lanciaProtocollaWS($onlyMainDoc = false) {

        $Proges_rec = $this->praLib->GetProges($this->currGesnum);

        /*
         * Get Array elementi
         */
        $praFascicolo = new praFascicolo($this->currGesnum, $this->anadesProt['ROWID']);
        $retElementi = $praFascicolo->getElementiProtocollazionePratica($onlyMainDoc);
        if ($retElementi['Status'] == "-1") {
            return $retElementi;
        }
        $elementi = $retElementi['Elementi'];

        /*
         * Inserisco paramentro Fascicola in elementi
         */
        $Filent_Rec = $this->praLib->GetFilent(29);
        if ($Filent_Rec['FILVAL'] == 1) {
            $elementi['Fascicola'] = "Si";
        }

        /*
         * Protocollazione.
         */
        $retPrt = proWsClientHelper::lanciaProtocollazioneWS($elementi);
        if ($retPrt['Status'] == "-1") {
            return $retPrt;
        }

        $elementi['DocNumber'] = $retPrt['RetValue']['DatiProtocollazione']['DocNumber']['value'];
        $elementi['dati']['Fascicolazione']['Anno'] = $retPrt['RetValue']['DatiProtocollazione']['Anno']['value'];
        $elementi['dati']['Fascicolazione']['Numero'] = $retPrt['RetValue']['DatiProtocollazione']['proNum']['value'];

        /*
         * Aggiorno i dati di protocollazione
         */
        $retUpd = $praFascicolo->updateDatiProtProges($retPrt, $elementi['dati']['arrayDoc']);
        if ($retUpd['Status'] == "-1") {
            return $retUpd;
        }

        /*
         * Se Attiva lancio la fascicolazione
         */
        if ($Filent_Rec['FILVAL'] == 1) {
            $ret = $this->lanciaFascicolazioneWS($elementi);
            if ($ret['Status'] == "-1") {
                Out::msgStop("Errore in Fascicolazione", $ret['Message']);
            }
        }

        if ($retPrt['errString']) {
            $errStr = "<br><span style=\"color:red;\">" . $retPrt['errString'] . "</span>";
        }

        if ($elementi['dati']['strNoProt']) {
            $strNoProt = "<br><span style=\"color:red;\">" . $elementi['dati']['strNoProt'] . "</span>";
        }

        Out::msgInfo("Protocollazione Pratica", "Protocollazione avvenuta con successo al n. " . $retPrt['RetValue']['DatiProtocollazione']['proNum']['value'] . ".<br>" . $ret['Message'] . $errStr . $strNoProt);

        $this->Dettaglio($Proges_rec['ROWID']);
    }

    public function lanciaFascicolazioneWS($elementi) {
        $ret = array();

        /*
         * Inizializzo il driver
         */
        $proObject = proWSClientFactory::getInstanceFascicolazione($elementi['TipoProtocollo']);
        if (!$proObject) {
            $ret["Status"] = "-1";
            $ret["Message"] = "Errore inizializzazione driver fascicolazione";
            $ret["RetValue"] = false;
            return $ret;
        }

        /*
         * Se non è un oggetto significa che non esiste la classe apposita e torno true
         */
        if (!is_object($proObject)) {
            $ret["Status"] = "0";
            $ret["Message"] = "";
            $ret["RetValue"] = true;
            return $ret;
        }

        /*
         * setto il codice istanza per la fascicolazione
         */
        $proObject->setKeyConfigParams($elementi['MetaDatiProtocollo']['CLASSIPARAMETRI'][$elementi['TipoProtocollo']]['KEYPARAMWSFASCICOLAZIONE']);

        /*
         * Verifica se valorizzato un fascicolo fisso
         */
        $fascicola = false;
        if ($elementi['dati']['Fascicolazione']['CodiceFascicolo'] == "") {
            $risultato = $proObject->CreaFascicolo($elementi);
            if ($risultato['Status'] == "-1") {
                return $risultato;
            }
            $fascicola = true;
            $elementi['dati']['Fascicolazione']['CodiceFascicolo'] = $risultato['datiFascicolo']['codiceFascicolo'];
            $elementi['dati']['Fascicolazione']['AnnoFascicolo'] = $risultato['datiFascicolo']['annoFascicolo'];
        } else {

            /*
             * Verifica data chiusura fascicolo
             */
            $risultatoChk = $proObject->checkFascicolo($elementi['dati']['Fascicolazione']['CodiceFascicolo']);
            if ($risultatoChk['Status'] == "-1") {
                return $risultatoChk;
            }
            $fascicola = $risultatoChk['fascicola'];
        }

        /*
         * Se fascicola è false faccio il return del result
         */
        if (!$fascicola) {
            return $risultatoChk;
        }

        /*
         * Fascicolo il documento (Inserisco il protocollo nel fascicolo) se il flag è true
         */
        $risultatoFascicola = $proObject->FascicolaDocumento($elementi);
        if ($risultatoFascicola['Status'] == "-1") {
            return $risultatoFascicola;
        }

        $ret["Status"] = "0";
        $ret["Message"] = "Fascicolazione avvenuta con successo nel fascicolo n. " . $elementi['dati']['Fascicolazione']['CodiceFascicolo']; //$msgFascicola;
        $ret["RetValue"] = true;

        return $ret;
    }

    function CaricaPassiDaPratica() {
        $passiSel = $passiCollegati = array();
        $passiSel = explode(",", $this->praPassiSel);
        //$passiSel = explode(",", $_POST['retKey']);

        $importaResp = true;
        if ($_POST[$this->nameForm . "_importaResponsabile"] == 0) {
            $importaResp = false;
        }

        $importaAllegati = false;
        if ($_POST[$this->nameForm . "_importaAllegati"] == 1) {
            $importaAllegati = true;
        }

        $importaDestinatari = false;
        if ($_POST[$this->nameForm . "_importaDestinatari"] == 1) {
            $importaDestinatari = true;
        }

        $importaProtocollo = false;
        if ($_POST[$this->nameForm . "_importaProtocollo"] == 1) {
            $importaProtocollo = true;
        }

        $importaValDatiAgg = false;
        if ($_POST[$this->nameForm . "_importaValDatiAgg"] == 1) {
            $importaValDatiAgg = true;
        }

        if ($passiSel) {
            $i = 99999;

            /*
             * Importi i passi selezionati
             */
            foreach ($passiSel as $rowid) {
                $i = $i + 1;
                $Propas_rec = $this->praLib->GetPropas($rowid, "rowid");
                $Propas_new_rec = $Propas_rec;
                $Propas_new_rec['PRONUM'] = $this->currGesnum;
                $Propas_new_rec['PROPAK'] = $propak = $this->praLib->PropakGenerator($this->currGesnum);
                $Propas_new_rec['PROSEQ'] = $i;
                $Propas_new_rec['PROVISIBILITA'] = "Aperto";
                $Propas_new_rec['PROUTEADD'] = $Propas_new_rec['PROUTEEDIT'] = App::$utente->getKey('nomeUtente');
                $Propas_new_rec['PRODATEADD'] = $Propas_new_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date("H:i:s");
                $Propas_new_rec['PROSTATO'] = "";
                $Propas_new_rec['PROFIN'] = "";
                $Propas_new_rec['PROINI'] = "";
                $Propas_new_rec['PROPART'] = 0;
                $Propas_new_rec['PROPST'] = 0;
                $Propas_new_rec['ROWID'] = 0;
                $Propas_new_rec['PRODWONLINE'] = 0;
                $Propas_new_rec['PROFLCDS'] = 0;
                $Propas_new_rec['PASPRO'] = 0;
                $Propas_new_rec['PASPAR'] = "";
                if (!$importaResp) {
                    $Propas_new_rec['PRORPA'] = "";
                }
                if (!$importaAllegati) {
                    $Propas_new_rec['PROALL'] = "";
                }
                $insert_Info = "Oggetto: Duplico passi da pratica $this->currGesnum";
                if (!$this->insertRecord($this->PRAM_DB, 'PROPAS', $Propas_new_rec, $insert_Info)) {
                    Out::msgStop("Inserimento data set", "Inserimento data set PROPAS fallito");
                    return false;
                }

                /*
                 * Importazione Protocollo) Sia in arrivo che in partenza se presente
                 */
                if ($importaProtocollo) {
                    $rowidCom = $this->importaProtocolloArrivoPartenza($Propas_rec['PROPAK'], $Propas_new_rec['PROPAK']);
                    if (!$rowidCom) {
                        return false;
                    }
                }

                /*
                 * Importazione Destinatari, se presenti
                 */
                if ($importaDestinatari) {
                    if (!$this->importaDestinatari($Propas_rec['PROPAK'], $Propas_new_rec['PROPAK'])) {
                        return false;
                    }
                }

                /*
                 * Importazione allegati
                 */
                if ($importaAllegati) {
                    if (!$this->importaAllegatiPasso($Propas_rec['PROPAK'], $Propas_new_rec['PROPAK'], $importaProtocollo, $rowidCom)) {
                        return false;
                    }
                }

                /*
                 * Cerco i passi collegati e li inserisco 
                 */
                $passiCollegati = $this->praLib->AddPassiAntecedenti($rowid, "PROPAS");
                if ($passiCollegati) {
                    foreach ($passiCollegati as $Propas_rec) {
                        $Propas_new_rec = $Propas_rec;
                        $i = $i + 1;
                        $Propas_new_rec = $Propas_rec;
                        $Propas_new_rec['PRONUM'] = $this->currGesnum;
                        $Propas_new_rec['PROPAK'] = $this->praLib->PropakGenerator($this->currGesnum);
                        $Propas_new_rec['PROKPRE'] = $propak;
                        $Propas_new_rec['PROSEQ'] = $i;
                        $Propas_new_rec['PROVISIBILITA'] = "Aperto";
                        $Propas_new_rec['PROUTEADD'] = $Propas_new_rec['PROUTEEDIT'] = App::$utente->getKey('nomeUtente');
                        $Propas_new_rec['PRODATEADD'] = $Propas_new_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date("H:i:s");
                        $Propas_new_rec['PROSTATO'] = "";
                        $Propas_new_rec['PROINI'] = "";
                        $Propas_new_rec['PROFIN'] = "";
                        $Propas_new_rec['PROPART'] = 0;
                        $Propas_new_rec['PROPST'] = 0;
                        $Propas_new_rec['ROWID'] = 0;
                        $Propas_new_rec['PRODWONLINE'] = 0;
                        $Propas_new_rec['PROFLCDS'] = 0;
                        $Propas_new_rec['PASPRO'] = 0;
                        $Propas_new_rec['PASPAR'] = "";
                        if (!$importaResp) {
                            $Propas_new_rec['PRORPA'] = "";
                        }
                        if (!$importaAllegati) {
                            $Propas_new_rec['PROALL'] = "";
                        }
                        $insert_Info = "Oggetto: Duplico passi da pratica $this->currGesnum";
                        if (!$this->insertRecord($this->PRAM_DB, 'PROPAS', $Propas_new_rec, $insert_Info)) {
                            Out::msgStop("Inserimento data set", "Inserimento data set PROPAS fallito");
                            return false;
                        }

                        /*
                         * Se ci sono importo gli allegati
                         */
                        if ($importaAllegati) {
                            $Pasdoc_tab = $this->praLib->GetPasdoc($Propas_rec['PROPAK'], "codice", true);
                            if ($Pasdoc_tab) {
                                $PathSorg = $this->praLib->SetDirectoryPratiche(substr($Propas_rec['PROPAK'], 0, 4), $Propas_rec['PROPAK'], "PASSO", false);
                                $PathDest = $this->praLib->SetDirectoryPratiche(substr($Propas_new_rec['PROPAK'], 0, 4), $Propas_new_rec['PROPAK']);
                                $insert_Info = "Oggetto : Importo allegati del paso seq " . $Propas_rec['PROSEQ'] . " - " . $Propas_rec['PROPAK'];
                                foreach ($Pasdoc_tab as $Pasdoc_rec) {
                                    $Pasdoc_new_rec = $Pasdoc_rec;
                                    $Pasdoc_new_rec['PASKEY'] = $Propas_new_rec['PROPAK'];
                                    $Pasdoc_new_rec['PASFIL'] = md5(rand() * time()) . "." . pathinfo($Pasdoc_rec['PASNAME'], PATHINFO_EXTENSION);
                                    $Pasdoc_new_rec['PASLNK'] = "allegato://" . $Pasdoc_new_rec['PASFIL'];
                                    $Pasdoc_new_rec['ROWID'] = 0;
                                    $Pasdoc_new_rec['PASUTELOG'] = App::$utente->getKey('nomeUtente');
                                    $Pasdoc_new_rec['PASORADOC'] = date("H:i:s");
                                    $Pasdoc_new_rec['PASPRTCLASS'] = '';
                                    $Pasdoc_new_rec['PASPRTROWID'] = 0;
                                    $Pasdoc_new_rec['PASROWIDBASE'] = 0;
                                    $Pasdoc_new_rec['PASSUBTIPO'] = '';
                                    $Pasdoc_new_rec["PASSHA2"] = hash_file('sha256', $PathSorg . "/" . $Pasdoc_rec["PASFIL"]);
                                    if (!$this->insertRecord($this->PRAM_DB, 'PASDOC', $Pasdoc_new_rec, $insert_Info)) {
                                        Out::msgStop("Inserimento data set", "Inserimento alleagti fallito");
                                        return false;
                                    }
                                    @copy($PathSorg . "/" . $Pasdoc_rec['PASFIL'], $PathDest . "/" . $Pasdoc_new_rec['PASFIL']);
                                    if (strtoupper(pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_EXTENSION)) == "XHTML") {
                                        $basefileSorg = pathinfo($PathSorg . "/" . $Pasdoc_rec['PASFIL'], PATHINFO_FILENAME);
                                        $basefileDest = pathinfo($PathSorg . "/" . $Pasdoc_new_rec['PASFIL'], PATHINFO_FILENAME);
                                        if (file_exists($PathSorg . "/" . $basefileSorg . ".pdf")) {
                                            @copy($PathSorg . "/" . $basefileSorg . ".pdf", $PathDest . "/" . $basefileDest . ".pdf");
                                        }
                                        if (file_exists($PathSorg . "/" . $basefileSorg . ".pdf.p7m")) {
                                            @copy($PathSorg . "/" . $basefileSorg . ".pdf.p7m", $PathDest . "/" . $basefileDest . ".pdf.p7m");
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                /*
                 * Importo i dati aggiuntivi
                 */

                if (!$this->importaDatiAggiuntivi($Propas_rec['PROPAK'], $Propas_new_rec['PROPAK'], $importaValDatiAgg)) {
                    return false;
                }
            }
            $this->praLib->ordinaPassiPratica($this->currGesnum);
            $this->praPassi = $this->praLib->caricaPassiBO($this->currGesnum);
            $this->CaricaGriglia($this->gridPassi, $this->praPassi);
        }
    }

    function CaricaPassiDaProcedimento() {
        $dbsuffix = $this->praLib->getDbSuffix($this->pranumSel);
        $Itepas_tab = $this->caricaPassiItepas($this->pranumSel, $dbsuffix);
        $passiSel = array();
        $selRows = explode(",", $_POST['retKey']);
        foreach ($selRows as $rowid) {
            foreach ($Itepas_tab as $Itepas_rec) {
                if ($Itepas_rec['ROWID'] === $rowid) {
                    $passiSel[] = $Itepas_rec;
                }
            }
        }

        /*
         * Cerco i passi collegati e li inserisco all'array passi selezionati
         */
        if ($passiSel) {
            $this->CaricaPassiDaPassiProcedimento($passiSel);
        }
    }

    private function CaricaPassiDaPassiProcedimento($passiSel) {
        $XML_ExportPassi = utf8_encode($this->praLib->creaXML($passiSel, $this->pranumSel));
        if (!is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                return false;
            }
        }

        $nome_file = itaLib::getAppsTempPath() . "/" . 'exportPassi.xml';
        if (file_put_contents($nome_file, $XML_ExportPassi)) {
            if ($this->praLib->ImportXmlFilePropas($nome_file, $this->insertTo, $this->currGesnum)) {
                $this->praLib->ordinaPassiPratica($this->currGesnum);
                $this->praPassi = $this->praLib->caricaPassiBO($this->currGesnum);
                $this->CaricaGriglia($this->gridPassi, $this->praPassi);
            }
        }
    }

    function ApriUploadImportXML() {
        $model = 'utiUploadDiag';
        $_POST = Array();
        $_POST['event'] = 'openform';
        $_POST[$model . '_returnModel'] = $this->nameForm;
        $_POST[$model . '_returnEvent'] = "returnUploadXML";
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    public function CreaCombo() {
        Out::html($this->nameForm . "_PROGES[GESPAR]", "");

        Out::select($this->nameForm . '_PROGES[GESPAR]', 1, "", "1", "");
        Out::select($this->nameForm . '_PROGES[GESPAR]', 1, "A", "0", "Arrivo     ");
        Out::select($this->nameForm . '_PROGES[GESPAR]', 1, "P", "0", "Partenza   ");
        Out::select($this->nameForm . '_PROGES[GESPAR]', 1, "C", "0", "Interno   ");

        foreach (praLib::$TIPO_SEGNALAZIONE as $k => $v) {
            Out::select($this->nameForm . '_PROGES[GESSEG]', '1', $k, '0', $v);
        }
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridPassi);
        TableView::clearGrid($this->gridPassi);
        TableView::disableEvents($this->gridAllegati);
        TableView::clearGrid($this->gridAllegati);
        TableView::disableEvents($this->gridImmobili);
        TableView::clearGrid($this->gridImmobili);
        TableView::disableEvents($this->gridSoggetti);
        TableView::clearGrid($this->gridSoggetti);
        TableView::disableEvents($this->gridUnitaLocale);
        TableView::clearGrid($this->gridUnitaLocale);
        Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDati");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCatastali");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_panePassi");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAllegati");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAggiuntivi");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneComunicazioni");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneNote");
        Out::block($this->nameForm . "_divIntestatario");
        Out::block($this->nameForm . "_divUnitaLocale");
        $this->currGesnum = null;
        $this->rowidAppoggio = null;
        $this->dataRegAppoggio = null;
        $this->currGesnum = '';
        $this->praPassi = array();
        $this->praAlle = array();
        $this->praAlleSha2 = array();
        $this->praSoggetti = null;
        $this->praUnitaLocale = null;
        $this->praImmobili = null;
        $this->sha2View = "";
        $this->proctipaut_rec = array();
        $this->anadesProt = array();
        $this->soggComm = array();
        $this->soggFiere = array();
        $this->praAccorpati = array();
        $this->praPassiSel = array();
    }

    function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Chiudi');
        Out::hide($this->nameForm . '_Apri');
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_Controlla');
        Out::hide($this->nameForm . '_ImportPassi');
        Out::hide($this->nameForm . '_Annulla');
        Out::hide($this->nameForm . '_EsportaDati');
        Out::hide($this->nameForm . '_Protocolla');
        Out::hide($this->nameForm . '_StampaDettaglio');
        Out::hide($this->nameForm . '_RimuoviProtocolla');
        Out::hide($this->nameForm . '_divButtonNew');
        Out::hide($this->nameForm . '_VediProtocollo');
        Out::hide($this->nameForm . '_GestioneProtocollo');
        Out::hide($this->nameForm . '_VediMail');
        Out::hide($this->nameForm . "_InviaProtocollo");
        Out::hide($this->nameForm . "_Etichetta");
        Out::hide($this->nameForm . "_StampaFile");
        Out::hide($this->nameForm . "_AssegnaPraticaButt");
        Out::hide($this->nameForm . "_RestituisciPraticaButt");
        Out::hide($this->nameForm . "_InCaricoPraticaButt");
        Out::hide($this->nameForm . "_divButtAssegnazioni");
        Out::hide($this->nameForm . "_VediStorico");
        Out::hide($this->nameForm . "_AggiornaStatistici");
        Out::hide($this->nameForm . "_AltreFunzioni");
        Out::hide($this->nameForm . '_Utilita');
        Out::hide($this->nameForm . '_CreaFasArch');
    }

    function ProtocollaPraticaArrivo() {
        /*
         * prima di protocollare vengono aggiornati i dati
         */
        $Proges_rec = $this->praLib->GetProges($this->currGesnum);
        if ($Proges_rec['ROWID'] != 0) {
            if (!$this->Dettaglio($Proges_rec['ROWID'])) {
                return false;
            }
        }

        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();

        /*
          Controllo se l'utente ha configurati i parametri per protocollare in altro ente
         */
        $enteProtRec_rec = $this->accLib->GetEnv_Utemeta(App::$utente->getKey('idUtente'), 'codice', 'ITALSOFTPROTREMOTO');
        if ($enteProtRec_rec) {
            $meta = unserialize($enteProtRec_rec['METAVALUE']);
            if ($meta['TIPO'] && $meta['URLREMOTO']) {
                $PARMENTE_rec['TIPOPROTOCOLLO'] = $meta['TIPO'];
            }
        }

        if ($PARMENTE_rec['TIPOPROTOCOLLO'] == 'Italsoft-remoto' || $PARMENTE_rec['TIPOPROTOCOLLO'] == 'Italsoft-remoto-allegati') {
            $envLibProt = new envLibProtocolla();
            $accLib = new accLib();
            $utenteWs = $accLib->GetUtenteProtRemoto(App::$utente->getKey('idUtente'));
            if (!$utenteWs) {
                Out::msgStop("Protocollo Remoto", "Utente remoto non definito!");
                return false;
            }
            $model = 'utiIFrame';
            $_POST = array();
            $_POST['event'] = 'openform';
            $_POST['returnModel'] = $this->nameForm;
            $_POST['returnEvent'] = 'returnIFrame';
            $_POST['retid'] = $this->nameForm . '_protocollaRemoto';
            $url_param = $envLibProt->getParametriProtocolloRemoto();
            if (!$url_param) {
                Out::msgStop("Errore Protocollazione", $envLibProt->getErrMessage());
                return false;
            }
            $_POST['src_frame'] = $url_param . "&access=direct&accessreturn=&accesstoken=nobody&model=menDirect&menu=PR_HID&prog=PR_WSPRA&topbar=0&homepage=0&noSave=1&utenteWs=" . $utenteWs . "&azione=PA&rowidSogg=" . $this->anadesProt['ROWID'] . "&pratica=" . $this->currGesnum;
            $_POST['title'] = "Protocollazione Remota";
            $_POST['returnKey'] = 'protocollaWS';
            itaLib::openForm($model);
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $model();
            return;
        } elseif ($PARMENTE_rec['TIPOPROTOCOLLO'] == 'Italsoft') {
            $praFascicolo = new praFascicolo($this->currGesnum, $this->anadesProt['ROWID']);
            $elementi = $praFascicolo->getElementiProtocollaPratica();
            if (!$elementi) {
                return false;
            }
            $proges_rowid = $Proges_rec['ROWID'];
            $_POST = Array();
            $model = 'proItalsoft.class';
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $proItalsoft = new proItalsoft();
            $valore = $proItalsoft->protocollazione($elementi);
            if ($valore['status'] === true) {
                $codice = substr($valore['value'], 4);
                $anno = substr($valore['value'], 0, 4);
                Out::valore($this->nameForm . '_Numero_prot', $codice);
                Out::valore($this->nameForm . '_Anno_prot', $anno);
                Out::valore($this->nameForm . '_PROGES[GESPAR]', "A");
                $proges_rec = array();
                $proges_rec['ROWID'] = $proges_rowid;
                $proges_rec['GESNPR'] = $valore['value'];
                $proges_rec['GESPAR'] = "A";
                $update_Info = "Oggetto rowid:" . $proges_rec['ROWID'] . ' num:' . $proges_rec['GESNPR'];
                if (!$this->updateRecord($this->PRAM_DB, 'PROGES', $proges_rec, $update_Info)) {
                    Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                    return false;
                }
                $Metadati = array();
                $this->switchIconeProtocollo($proges_rec['GESNPR'], $Metadati);
            } else {
                Out::msgStop("Errore in Protocollazione", $valore['msg']);
                return false;
            }
            $this->Dettaglio($Proges_rec['ROWID']);
        } elseif ($PARMENTE_rec['TIPOPROTOCOLLO'] == 'Manuale') {
// TODO:
        } else {
            $htmlTesto = "<br><br><div stle=\"font-size:1.1em;\"><b>Scegliendo Conferma verrà protocollato solamente il documento principale del Fascicolo:<br>- in caso di pratica proveniente da portale è il rapporto completo.<br>- in caso di pratica da pec è il file eml.</b>";
            Out::msgQuestion("Protocollazione Fascicolo Elettronico.", "<span style=\"font-size:1.5em;text-decoration:underline;\">Vuoi protocollare solo il documento principale?</span>$htmlTesto", array(
                'F5-Protocolla Tutti' => array('id' => $this->nameForm . '_AnnullaProtPrinc', 'model' => $this->nameForm, 'shortCut' => "f5"),
                'F4-Conferma' => array('id' => $this->nameForm . '_ConfermaProtPrinc', 'model' => $this->nameForm, 'shortCut' => "f4"),
                    )
            );
        }
        return true;
    }

    function DecodAnaeventi($Codice, $tipoRic = 'codice', $retid = "", $updsegnalazione = false) {
        $anaeventi_rec = $this->praLib->GetAnaeventi($Codice, $tipoRic);
        Out::valore($this->nameForm . "_PROGES[GESEVE]", '');
        Out::valore($this->nameForm . "_Desc_eve", '');
        if ($anaeventi_rec) {
            Out::valore($this->nameForm . "_PROGES[GESEVE]", $anaeventi_rec['EVTCOD']);
            Out::valore($this->nameForm . "_Desc_eve", $anaeventi_rec['EVTDESCR']);
            if ($updsegnalazione && $anaeventi_rec['EVTSEGCOMUNICA']) {
                Out::valore($this->nameForm . "_PROGES[GESSEG]", $anaeventi_rec['EVTSEGCOMUNICA']);
            }
        }
    }

    function DecodEvento($idEvento, $tipoRic = "codice") {
        Out::valore($this->nameForm . '_PROGES[GESSEG]', '');
        Out::valore($this->nameForm . '_PROGES[GESEVE]', '');
        Out::valore($this->nameForm . '_Desc_eve', '');
        if ($idEvento) {
            $iteevt_rec = $this->praLib->GetIteevt($idEvento, $tipoRic);
            if ($iteevt_rec) {
                $anaeventi_rec = $this->praLib->GetAnaeventi($iteevt_rec['IEVCOD']);
            }
            Out::valore($this->nameForm . '_PROGES[GESSEG]', $anaeventi_rec['EVTSEGCOMUNICA']);
            Out::valore($this->nameForm . '_PROGES[GESEVE]', $iteevt_rec['IEVCOD']);
            Out::valore($this->nameForm . '_Desc_eve', $anaeventi_rec['EVTDESCR']);
            if ($this->formData[$this->nameForm . "_PROGES"]["GESTSP"] == "") {
                $this->DecodAnatsp($iteevt_rec['IEVTSP']);
            }
            /*
             * Se non è spuntato il parametro della Personalizzaizone della classificazione,
             * popolo la classificazione
             */
            $Filent_rec = $this->praLib->GetFilent(17);
            if ($Filent_rec['FILVAL'] != 1) {
                $this->DecodAnatip($iteevt_rec['IEVTIP'], "codice", "GESTIP");
                $this->DecodAnaset($iteevt_rec['IEVSTT'], "codice", "GESSTT");
                $this->DecodAnaatt($iteevt_rec['IEVATT'], "codice", "GESATT");
            }
        }
    }

    function DecodAnapra($Codice, $retid, $tipoRic = 'codice', $dataRegistrazione = '', $idEvento = "") {
        $anapra_rec = $this->praLib->GetAnapra($Codice, $tipoRic);
        switch ($retid) {
            case $this->nameForm . "_PROGES[GESPRO]" :
                Out::valore($this->nameForm . "_PROGES[GESPRO]", $anapra_rec['PRANUM']);
                Out::valore($this->nameForm . '_Desc_proc2', $anapra_rec['PRADES__1']);
                if ($idEvento) {
                    $this->DecodEvento($idEvento, "rowid");
                } else {
                    $iteevt_tab = $this->praLib->GetIteevt($Codice, "codice", true);
                    if (count($iteevt_tab) == 1) {
                        $this->DecodEvento($iteevt_tab[0]['ROWID'], "rowid");
                    } elseif (count($iteevt_tab) > 1) {
                        praRic::ricIteevt($this->nameForm, "WHERE ITEPRA = '" . $iteevt_tab[0]['ITEPRA'] . "'", "DETT");
                    } else {
                        $this->DecodEvento();
                    }
                }
                $arrayScadenza = $this->praLib->SincDataScadenza("PRATICA", $this->currGesnum, "", $anapra_rec['PRAGIO'], $anapra_rec['PRAGIO'], $dataRegistrazione);
                if (!$arrayScadenza) {
                    Out::msgStop("Sincronizzazione Scadenza", "Errore Sincronizzazione Data Scadenza.");
                }
                Out::valore($this->nameForm . "_PROGES[GESDSC]", $arrayScadenza['SCADENZA']);
                Out::valore($this->nameForm . "_PROGES[GESGIO]", $arrayScadenza['GIORNI']);
                $this->DecodAnanom($anapra_rec['PRARES'], $this->nameForm . "_PROGES[GESRES]");
                break;
            case "importXml" :
                $dbsuffix = "";
                if ($anapra_rec['PRASLAVE'] == 1) {
                    $dbsuffix = $this->praLib->GetEnteMaster();
                }
                $this->pranumSel = $anapra_rec['PRANUM'];
                $Itepas_tab = $this->caricaPassiItepas($this->pranumSel, $dbsuffix);

                if (!$Itepas_tab) {
                    Out::msgInfo("Importazione Passi", "Passi del procedimento <b>$this->pranumSel - " . $anapra_rec['PRADES__1'] . "</b> non trovati.");
                    break;
                }

                if ($anapra_rec['PRATPR'] == 'ENDOPROCEDIMENTOWRKF') {
                    $this->CaricaPassiDaPassiProcedimento($Itepas_tab);

                    /*
                     * Aggiorno i riferimenti nel grafico
                     */
                    if ($anapra_rec['PRADIAG']) {
                        $gesdiag = $anapra_rec['PRADIAG'];
                        foreach ($this->praPassi as $propas_rec) {
                            $gesdiag = str_replace($propas_rec['PROITK'], $propas_rec['PROPAK'], $gesdiag);
                        }

                        $proges_rec = $this->praLib->getProges($this->currGesnum);
                        $proges_rec['GESDIAG'] = $gesdiag;

                        if (!$this->updateRecord($this->PRAM_DB, 'PROGES', $proges_rec, "Inserimento data workflow '{$this->currGesnum}'.")) {
                            Out::msgStop('Errore', 'Errore durante il salvataggio del workflow.');
                            break;
                        }
                    }
                    break;
                }

                praRic::praPassiSelezionati($Itepas_tab, $this->nameForm, "exp", "Scegli i passi da importare");
                break;
            case "" :
                break;
        }
        return $anapra_rec;
    }

    private function inserisciNotifica($oggetto, $uteins, $tipoNotifica) {
        include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
        $envLib = new envLib();
        $oggetto_notifica = "$tipoNotifica UNA NOTA ALLA PRATICA NUMERO $this->currGesnum";
        $testo_notifica = $oggetto;
        $dati_extra = array();
        $dati_extra['ACTIONMODEL'] = $this->nameForm;
        $dati_extra['ACTIONPARAM'] = serialize(array('setOpenMode' => array('edit'), 'setOpenRowid' => array($this->formData[$this->nameForm . '_PROGES']['ROWID'])));
        $envLib->inserisciNotifica($this->nameform, $oggetto_notifica, $testo_notifica, $uteins, $dati_extra);
        return;
    }

    function AggiornaProgressivo() {
        $Filent_rec = $this->praLib->GetFilent("1");
        $Progressivo = $Filent_rec['FILDE1'];
        $Filent_rec['FILDE1'] = $Progressivo - 1;
        $update_Info = "Oggetto: Rimando indietro il progressivo da $Progressivo a " . $Filent_rec['FILDE1'];
        if (!$this->updateRecord($this->PRAM_DB, 'FILENT', $Filent_rec, $update_Info)) {
            Out::msgStop("Aggiornamento Progressivo", "Errore in aggiornamento del progressivo");
            return false;
        }
    }

    function ordinaSeqArrayDag() {
        if (!$this->praDatiPratica) {
            return false;
        }
        $this->praDatiPratica = $this->praLib->array_sort($this->praDatiPratica, "DAGSEQ");
        $new_seq = 0;
        foreach ($this->praDatiPratica as $key => $dato) {
            $new_seq += 10;
            $this->praDatiPratica[$key]['DAGSEQ'] = $new_seq;
        }
    }

    private function DecodAnaspa2($Codice, $tipoRic = 'codice') {
        $anaspa_rec = $this->praLib->GetAnaspa($Codice, $tipoRic);
        Out::valore($this->nameForm . '_PROGES[GESSPA]', $anaspa_rec['SPACOD']);
        Out::valore($this->nameForm . '_ANASPA[SPADES]', $anaspa_rec['SPADES']);
        return $anaspa_rec;
    }

    private function DecodAnatip($Codice, $tipoRic = 'codice', $retid = "") {
        $anatip_rec = $this->praLib->GetAnatip($Codice, $tipoRic);
        Out::valore($this->nameForm . "_PROGES[GESTIP]", $anatip_rec['TIPCOD']);
        Out::valore($this->nameForm . "_Desc_tip", $anatip_rec['TIPDES']);
        return $anatip_rec;
    }

    private function DecodAnaset($Codice, $tipoRic = 'codice', $retid = "") {
        $anaset_rec = $this->praLib->GetAnaset($Codice, $tipoRic);
        Out::valore($this->nameForm . "_PROGES[GESSTT]", $anaset_rec['SETCOD']);
        Out::valore($this->nameForm . "_Desc_set", $anaset_rec['SETDES']);
        return $anaset_rec;
    }

    private function DecodAnaatt($Codice, $tipoRic = 'codice', $retid = "") {
        $anaatt_rec = $this->praLib->GetAnaatt($Codice, $tipoRic);
        Out::valore($this->nameForm . "_PROGES[GESATT]", $anaatt_rec['ATTCOD']);
        Out::valore($this->nameForm . "_Desc_att", $anaatt_rec['ATTDES']);
        return $anaatt_rec;
    }

    private function DecodAnatsp($Codice, $tipoRic = 'codice') {
        $anatsp_rec = $this->praLib->GetAnatsp($Codice, $tipoRic);
        Out::valore($this->nameForm . '_PROGES[GESTSP]', $anatsp_rec['TSPCOD']);
        Out::valore($this->nameForm . '_ANATSP[TSPDES]', $anatsp_rec['TSPDES']);
        return $anatsp_rec;
    }

    private function DecodLegame($Codice, $tipoRic = 'codice', $foglio = "", $particella = "") {
        $legame_rec = $this->catLib->GetLegame($Codice, $tipoRic);
        if ($legame_rec) {
            Out::valore($this->nameForm . "_PRAIMM[FOGLIO]", $legame_rec['FOGLIO']);
            Out::valore($this->nameForm . "_PRAIMM[PARTICELLA]", $legame_rec['NUMERO']);
            if ($legame_rec['SUB']) {
                Out::valore($this->nameForm . "_PRAIMM[SUBALTERNO]", $legame_rec['SUB']);
            }
            Out::valore($this->nameForm . "_PRAIMM[CODICE]", $legame_rec['IMMOBILE']);
            Out::valore($this->nameForm . "_PRAIMM[TIPO]", $legame_rec['TIPOIMMOBILE']);
        } else {
            $legame_rec = $this->catLib->GetLegame($Codice, $tipoRic, $foglio, $particella);
            if ($legame_rec) {
                Out::valore($this->nameForm . "_PRAIMM[FOGLIO]", $legame_rec['FOGLIO']);
                Out::valore($this->nameForm . "_PRAIMM[PARTICELLA]", $legame_rec['NUMERO']);
                if ($legame_rec['SUB']) {
                    Out::valore($this->nameForm . "_PRAIMM[SUBALTERNO]", $legame_rec['SUB']);
                }
                Out::valore($this->nameForm . "_PRAIMM[CODICE]", $legame_rec['IMMOBILE']);
                Out::valore($this->nameForm . "_PRAIMM[TIPO]", $legame_rec['TIPOIMMOBILE']);
            }
        }
        return $legame_rec;
    }

    private function DecodAnanom($Codice, $retid, $tipoRic = 'codice') {
        $ananom_rec = $this->praLib->GetAnanom($Codice, $tipoRic);
        Out::valore($this->nameForm . '_PROGES[GESRES]', $ananom_rec['NOMRES']);
        Out::valore($this->nameForm . '_Desc_resp2', $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM']);
        $this->DecodResponsabile($ananom_rec);
        return $ananom_rec;
    }

    private function DecodProges($Codice, $tipoRic = 'codice') {
        $proges_rec = $this->praLib->GetProges($Codice, $tipoRic);
        $Serie_rec = $this->praLib->ElaboraProgesSerie($proges_rec['GESNUM'], $proges_rec['SERIECODICE'], $proges_rec['SERIEANNO'], $proges_rec['SERIEPROGRESSIVO']);
        $Serie = explode("/", $Serie_rec);
        Out::valore($this->nameForm . "_Gespre0", $Serie[0]);
        Out::valore($this->nameForm . "_Gespre1", $Serie[1]);
        Out::valore($this->nameForm . "_Gespre2", $Serie[2]);
        Out::valore($this->nameForm . "_Gespre3", $proges_rec['SERIECODICE']);
        if ($proges_rec['GESSPA']) {
            $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
            Out::valore($this->nameForm . "_PROGES[GESSPA]", $anaspa_rec['SPACOD']);
            Out::valore($this->nameForm . "_ANASPA[SPADES]", $anaspa_rec['SPADES']);
        }
        if ($proges_rec['GESTSP']) {
            $anatsp_rec = $this->praLib->GetAnatsp($proges_rec['GESTSP']);
            Out::valore($this->nameForm . "_ANATSP[TSPDES]", $anatsp_rec['TSPDES']);
        }
    }

    private function DecodResponsabile($Ananom_rec) {
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
        if ($AnauniRes_rec['UNISER'] == "")
            $AnauniRes_rec['UNISET'] = "";
        if ($AnauniRes_rec['UNISET'] == "")
            $AnauniRes_rec['UNIOPE'] = "";
        $AnauniOpe_rec = $this->praLib->GetAnauniOpe($AnauniRes_rec['UNISET'], $AnauniRes_rec['UNISER'], $AnauniRes_rec['UNIOPE']);
        Out::valore($this->nameForm . '_PROGES[GESOPE]', $AnauniOpe_rec['UNIOPE']);
        Out::valore($this->nameForm . '_Unita', $AnauniOpe_rec['UNIDES']);
    }

    function GetStatoAllegati($passta) {
        switch ($passta) {
            case "":
                $stato = "";
                break;
            case "C":
                $stato = "Da controllare";
                break;
            case "V":
                $stato = "Valido";
                break;
            case "N":
                $stato = "Non Valido";
                break;
            case "S":
                $stato = "Sostituito";
                break;
        }
        return $stato;
    }

    function OpenImmobile($event = "Nuovo") {
        $immobile = array();
        $this->codiceImm = "";
        $class = "ita-edit-lookup";
        //
        $arrButton = array(
            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaImmobile', 'model' => $this->nameForm, 'shortCut' => "f5"),
            'Valida' => array('id' => $this->nameForm . '_ValidaImmobile', 'model' => $this->nameForm, "metaData" => "iconLeft:'ui-icon ui-icon-circle-check'")
        );
        //
        if ($event == "Dettaglio") {
            $immobile = $this->praImmobili->getImmobile($_POST['rowid']);
            $this->codiceImm = $immobile['CODICE'];
            switch ($immobile['CTRRET']) {
                case 0:
                    $color = "";
                    break;
                case 2:
                case 3:
                    $color = "red";
                    break;
                case 1:
                case 4:
                    $color = "green";
                    break;
            }
            if ($color) {
                $header = "<span style=\"font-size:1.2em;color:$color\"><b>" . $immobile['CTRMSG'] . "</b></span>";
            }
        }


        Out::msgInput(
                "$event Immobile", array(
            array(
                'label' => array('style' => "width:60px;", 'value' => 'Tipo'),
                'id' => $this->nameForm . '_PRAIMM[TIPO]',
                'name' => $this->nameForm . '_PRAIMM[TIPO]',
                'type' => 'select',
                'width' => '50',
                'size' => '1',
                'options' => array(
                    array("", ""),
                    array("F", "FABBRICATO"),
                    array("T", "TERRENO")
                )
            ),
            array(
                'label' => array('style' => "width:60px;", 'value' => 'Sezione'),
                'id' => $this->nameForm . '_PRAIMM[SEZIONE]',
                'name' => $this->nameForm . '_PRAIMM[SEZIONE]',
                'type' => 'text',
                'width' => '50',
                'size' => '5',
                'maxlength' => '3'),
            array(
                'label' => array('style' => "width:60px;", 'value' => 'Foglio'),
                'id' => $this->nameForm . '_PRAIMM[FOGLIO]',
                'name' => $this->nameForm . '_PRAIMM[FOGLIO]',
                'type' => 'text',
                'class' => 'ita-edit-onblur',
                'width' => '50',
                'size' => '10',
                'maxlength' => '4'),
            array(
                'label' => array('style' => "width:60px;", 'value' => 'Particella'),
                'id' => $this->nameForm . '_PRAIMM[PARTICELLA]',
                'name' => $this->nameForm . '_PRAIMM[PARTICELLA]',
                'type' => 'text',
                'class' => 'ita-edit-onblur',
                'width' => '50',
                'size' => '10',
                'maxlength' => '5'),
            array(
                'label' => array('style' => "width:60px;", 'value' => 'Subalterno'),
                'id' => $this->nameForm . '_PRAIMM[SUBALTERNO]',
                'name' => $this->nameForm . '_PRAIMM[SUBALTERNO]',
                'type' => 'text',
                'class' => 'ita-edit-onblur',
                'width' => '50',
                'size' => '10',
                'maxlength' => '4'),
            array(
                'label' => array('style' => "width:60px;", 'value' => 'Codice'),
                'id' => $this->nameForm . '_PRAIMM[CODICE]',
                'name' => $this->nameForm . '_PRAIMM[CODICE]',
                'type' => 'text',
                'class' => "ita-edit-onblur $class",
                'width' => '50',
                'size' => '15',
                'maxlength' => '20'),
            array(
                'label' => array('style' => "width:60px;", 'value' => 'Note'),
                'id' => $this->nameForm . '_PRAIMM[NOTE]',
                'name' => $this->nameForm . '_PRAIMM[NOTE]',
                'type' => 'textarea',
                'cols' => '50',
                'rows' => '5'),
            array(
                'id' => $this->nameForm . '_rowid',
                'name' => $this->nameForm . '_rowid',
                'type' => 'hidden',
                'value' => $_POST['rowid']),
                ), $arrButton, $this->nameForm, "auto", "auto", true, $header
        );
        Out::valori($immobile, $this->nameForm . '_PRAIMM');
    }

    function CtrPassword() {
        $ditta = App::$utente->getKey('ditta');
        $utente = App::$utente->getKey('nomeUtente');
        $password = $_POST[$this->nameForm . '_password'];
        $ret = ita_verpass($ditta, $utente, $password);
        if ($ret['status'] != 0 && $ret['status'] != '-99') {
            Out::msgStop("Errore di validazione", $ret['messaggio'], 'auto', 'auto', '');
            return false;
        } else {
            return true;
        }
    }

    private function ElaboraRecordPasdoc($Pasdoc_tab) {
        $arrayAlle = array();
        foreach ($Pasdoc_tab as $key => $pasdoc_rec) {
            $ext = pathinfo($pasdoc_rec['PASFIL'], PATHINFO_EXTENSION);
            $strDest = "";
            $inElenco = false;

            if (strlen($pasdoc_rec["PASKEY"]) == 10) {
                $pramPath = $this->praLib->SetDirectoryPratiche(substr($pasdoc_rec["PASKEY"], 0, 4), $pasdoc_rec["PASKEY"], 'PROGES', false);
                $inElenco = true;
            } elseif (strlen($pasdoc_rec["PASKEY"]) > 10) {
                $pramPath = $this->praLib->SetDirectoryPratiche(substr($pasdoc_rec["PASKEY"], 0, 4), $pasdoc_rec["PASKEY"], "PASSO", false);
                $propas_rec = $this->praLib->GetPropas($pasdoc_rec['PASKEY'], 'propak');

                //if ($propas_rec['PROPUB'] == 0 || ($propas_rec['PROPUB'] == 1 && strtolower($ext) == "p7m") || $propas_rec['PRODRR'] == 1) {
                if ($propas_rec['PROPUB'] == 0 || ($propas_rec['PROPUB'] == 1 && strtolower($ext) == "p7m") || ($propas_rec['PROPUB'] == 1 && $propas_rec['PROIDR'] == 0 && ($propas_rec['PROUPL'] == 1 || $propas_rec['PROMLT'] == 1) || $propas_rec['PRODAT'] == 1 || $propas_rec['PRODRR'] == 1)) {
                    $inElenco = true;
                }
                if ($propas_rec['PROKPRE'] && $propas_rec['PROPUB'] == 1 && $propas_rec['PROIDR'] == 0) {
                    $inElenco = true;
                }
            }

            if ($pasdoc_rec && $inElenco == true) {
                if (strtolower($ext) == "p7m") {
                    $edit = "<span class=\"ita-icon ita-icon-shield-blue-16x16\">Verifica Firma</span>";
                } else if (strtolower($ext) == "zip") {
                    $edit = "<span class=\"ita-icon ita-icon-winzip-16x16\">estrai File Zip</span>";
                } else {
                    $edit = " ";
                    if ($pasdoc_rec['PASCLA'] == "TESTOBASE") {
                        if (file_exists($pramPath . "/" . pathinfo($pasdoc_rec['PASFIL'], PATHINFO_FILENAME) . ".pdf.p7m")) {
                            $edit = "<span class=\"ita-icon ita-icon-shield-blue-16x16\">Verifica Firma</span>";
                        }
                    }
                }
                $stato = $this->praLib->GetStatoAllegati($pasdoc_rec['PASSTA']);

                $countAlle = $pasdoc_rec['NUMALLEGATI'] - 1;
                if ($countAlle > 1) {
                    $msgTooltip = "Sono Presenti altri $countAlle Allegati";
                } else if ($countAlle == 1) {
                    $msgTooltip = "E' Presente un altro Allegato";
                }
                $arrayAlle[$key]['ROWID'] = $pasdoc_rec['ROWID'];
                $arrayAlle[$key]['NUMALLEGATI'] = "<div class=\"ita-html\"><span style=\"display:inline-block;vertical-align:center;padding-right:3px;\" title=\"$msgTooltip\" class=\"ita-tooltip ita-icon ita-icon-add-16x16\"></span><span style=\"font-size:20px;display:inline-block;vertical-align:center;\">$countAlle</span></div>";
                if ($countAlle == 0) {
                    $arrayAlle[$key]['NUMALLEGATI'] = "";
                }

                if ($pasdoc_rec['PASNAME']) {
                    $arrayAlle[$key]['NAME'] = $pasdoc_rec['PASNAME'];
                    $arrayAlle[$key]['FILEORIG'] = $pasdoc_rec['PASNAME'];
                } else {
                    $arrayAlle[$key]['NAME'] = $pasdoc_rec['PASFIL'];
                    $arrayAlle[$key]['FILEORIG'] = $pasdoc_rec['PASFIL'];
                }
                $arrayAlle[$key]['INFO'] = $pasdoc_rec['PASCLA'];
                $arrayAlle[$key]['NOTE'] = $pasdoc_rec['PASNOT'];
                $arrayAlle[$key]['STATO'] = $stato;
                $arrayAlle[$key]['EDIT'] = $edit;
                $arrayAlle[$key]['SIZE'] = $this->praLib->formatFileSize(filesize($pramPath . "/" . $pasdoc_rec['PASFIL']));
                $arrayAlle[$key]['SOST'] = $this->praLib->CheckSostFile($this->currGesnum, $pasdoc_rec['PASSHA2'], $pasdoc_rec['PASSHA2SOST']);
                $arrayAlle[$key]['EVIDENZIA'] = "<div class=\"ita-html\"><span class=\"ita-icon ita-icon-evidenzia-16x16 ita-colorpicker {type: 'divColor',swatches:true}\">Evidenzia Allegato</span></div>";
                if ($pasdoc_rec['PASLOCK'] == 1) {
                    $arrayAlle[$key]['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Sblocca Allegato</span>";
                } else {
                    $arrayAlle[$key]['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-16x16\">Blocca Allegato</span>";
                }

                /*
                 * Se evidenziato cambio lo sfondo altrimenti rimane trasparente
                 */
                $color = $this->praLib->getColorNameAllegato($pasdoc_rec['PASEVI']);
                if ($pasdoc_rec['PASEVI'] == 1) {
                    if ($pasdoc_rec['PASNAME']) {
                        $arrayAlle[$key]['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $pasdoc_rec['PASNAME'] . "</p>";
                        $arrayDataTmp['FILEORIG'] = $pasdoc_rec['PASNAME'];
                    } else {
                        $arrayAlle[$key]['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $pasdoc_rec['PASFIL'] . "</p>";
                        $arrayDataTmp['FILEORIG'] = $pasdoc_rec['PASFIL'];
                    }
                } elseif ($pasdoc_rec['PASEVI'] != 1 && $pasdoc_rec['PASEVI'] != 0 && !empty($pasdoc_rec['PASEVI'])) {
                    if ($pasdoc_rec['PASNAME']) {
                        $arrayAlle[$key]['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $pasdoc_rec['PASNAME'] . "</p>";
                        $arrayDataTmp['FILEORIG'] = $pasdoc_rec['PASNAME'];
                    } else {
                        $arrayAlle[$key]['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $pasdoc_rec['PASFIL'] . "</p>";
                        $arrayDataTmp['FILEORIG'] = $pasdoc_rec['PASFIL'];
                    }
                }

                if ($pasdoc_rec['PASPRTROWID'] != 0 && $pasdoc_rec['PASPRTCLASS'] == "PROGES") {
                    $proges_rec = $this->praLib->GetProges($pasdoc_rec['PASPRTROWID'], "rowid");
                    if ($proges_rec['GESNPR']) {
                        $meta = unserialize($proges_rec['GESMETA']);
                        //$dataPrt = substr($meta['DatiProtocollazione']['Data']['value'], 8, 2) . "/" . substr($meta['DatiProtocollazione']['Data']['value'], 5, 2) . "/" . substr($meta['DatiProtocollazione']['Data']['value'], 0, 4);
                        $dataPrt = $this->praLib->GetDataProtNormalizzata($meta);
                        $arrayAlle[$key]['PROTOCOLLO'] = $proges_rec['GESNPR'] . " del <br>$dataPrt";
                        $arrayAlle[$key]['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Allegato Bloccato per Protocollazione PRATICA</span>";
                    }
                }
                if ($pasdoc_rec['PASPRTROWID'] != 0 && $pasdoc_rec['PASPRTCLASS'] == "PRACOM") {
                    $pracom_rec = $this->praLib->GetPracom($pasdoc_rec['PASPRTROWID'], "rowid");
                    if ($pracom_rec['COMPRT']) {
                        $dataPrtPasso = substr($pracom_rec['COMDPR'], 6, 2) . "/" . substr($pracom_rec['COMDPR'], 4, 2) . "/" . substr($pracom_rec['COMDPR'], 0, 4);
                        $arrayAlle[$key]['PROTOCOLLO'] = $pracom_rec['COMPRT'] . " del <br>$dataPrtPasso";
                        $arrayAlle[$key]['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Allegato Bloccato per Protocollazione PASSO</span>";
                    }
                }

                /*
                 * Destinazioni
                 */
                $arrayDest = unserialize($pasdoc_rec['PASDEST']);
                if (is_array($arrayDest)) {
                    foreach ($arrayDest as $dest) {
                        $Anaddo_rec = $this->praLib->GetAnaddo($dest);
                        $strDest .= $Anaddo_rec['DDONOM'] . "<br>";
                    }
                    $arrayAlle[$key]['DESTINAZIONI'] = "<div class=\"ita-html\"><span style=\"color:red;\" class=\"ita-Wordwrap ita-tooltip\" title=\"$strDest\"><b>" . count($arrayDest) . "</b></span></div>";
                }
                $arrayAlle[$key]['PASDEST'] = $pasdoc_rec['PASDEST'];

                /*
                 * Note
                 */
                $arrayAlle[$key]['PASNOTE'] = $pasdoc_rec['PASNOTE'];

                $arrayAlle[$key]['PASSHA2'] = $pasdoc_rec['PASSHA2'];

                $arrayAlle[$key]['FILENAME'] = $pasdoc_rec['PASFIL'];
                $arrayAlle[$key]['FILEINFO'] = $pasdoc_rec['PASCLA'];
                $arrayAlle[$key]['FILEPATH'] = $pramPath . "/" . $pasdoc_rec['PASFIL'];
            }
        }
        return $arrayAlle;
    }

    public function apriNuovo() {
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        $this->Nascondi();
        $this->AzzeraVariabili();
        Out::clearFields($this->nameForm, $this->divGes);
        Out::attributo($this->nameForm . '_PROGES[GESDCH]', 'readonly', '1');
        Out::hide($this->nameForm . '_PROGES[GESPRA]');
        Out::hide($this->nameForm . '_PROGES[GESPRA]_lbl');
        Out::show($this->nameForm . '_PROGES[GESSPA]');
        Out::show($this->nameForm . '_PROGES[GESSPA]_lbl');
        Out::show($this->nameForm . '_PROGES[GESSPA]_butt');
        Out::show($this->nameForm . '_PROGES[GESTSP]');
        Out::show($this->nameForm . '_PROGES[GESTSP]_lbl');
        Out::show($this->nameForm . '_PROGES[GESTSP]_butt');
        Out::attributo($this->nameForm . '_PROGES[GESSPA]', 'readonly', '1');
        Out::attributo($this->nameForm . '_PROGES[GESTSP]', 'readonly', '1');
        Out::attributo($this->nameForm . '_PROGES[GESDRI]', 'readonly', '1');
        Out::attributo($this->nameForm . '_PROGES[GESORA]', 'readonly', '1');
        Out::attributo($this->nameForm . '_Numero_prot', 'readonly', '1');
        Out::attributo($this->nameForm . '_Anno_prot', 'readonly', '1');
        Out::show($this->nameForm . '_Aggiungi');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::valore($this->nameForm . '_PROGES[GESDRE]', $this->workDate);
        Out::setFocus('', $this->nameForm . '_PROGES[GESDRE]');
        $Filent_rec = $this->praLib->GetFilent(17);
        if ($Filent_rec['FILVAL'] == 1) {
            Out::show($this->nameForm . '_PROGES[GESTIP]_butt');
            Out::show($this->nameForm . '_PROGES[GESSTT]_butt');
            Out::show($this->nameForm . '_PROGES[GESATT]_butt');
            Out::attributo($this->nameForm . '_PROGES[GESTIP]', 'readonly', '1');
            Out::attributo($this->nameForm . '_PROGES[GESSTT]', 'readonly', '1');
            Out::attributo($this->nameForm . '_PROGES[GESATT]', 'readonly', '1');
        } else {
            Out::hide($this->nameForm . '_PROGES[GESTIP]_butt');
            Out::hide($this->nameForm . '_PROGES[GESSTT]_butt');
            Out::hide($this->nameForm . '_PROGES[GESATT]_butt');
            Out::attributo($this->nameForm . '_PROGES[GESTIP]', 'readonly', '0');
            Out::attributo($this->nameForm . '_PROGES[GESSTT]', 'readonly', '0');
            Out::attributo($this->nameForm . '_PROGES[GESATT]', 'readonly', '0');
        }
    }

    public function Dettaglio($Indice, $apriForm = false, $allegatiMail = "", $tipo = 'rowid') {
        $proges_rec = $this->praLib->GetProges($Indice, $tipo);
        if (!$proges_rec) {
            $this->close();
            Out::msgStop("Attenzione", "Record PROGES con rowid: $Indice non più disponibile.");
            return false;
        }
        $this->currGesnum = $proges_rec['GESNUM'];
        $this->soggComm = array(); // Al Dettaglio azzero il soggetto scelto per aprire le aut del comm
//
        $isSuperUser = $this->praPerms->checkSuperUser($proges_rec);
//
        $ret = $this->praLib->checkVisibilitaSportello(array('SPORTELLO' => $proges_rec['GESTSP'], 'AGGREGATO' => $proges_rec['GESSPA']), $this->praLib->GetVisibiltaSportello());
        $PassiAssegnati = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM ='$this->currGesnum' AND PRORPA = '" . $this->profilo['COD_ANANOM'] . "'", true);
        if (!$ret) {
            if (!$PassiAssegnati) {
                Out::msgStop("Attenzione", "Pratica non Visibile.<br>Controllare le impostazioni di visibilita nella scheda Pianta Organica ---> Dipendenti<br> o l'assegnatario dei vari passi.");
                $this->close();
                return;
            }
        }

        $rigaSel = $_POST['selRow'];
        $this->returnModel = $_POST[$this->nameForm . '_returnModel'];
        $this->returnId = $_POST[$this->nameForm . '_returnId'];
        if ($apriForm) {
            Out::show($this->nameForm);
            $this->inizializzaForm();
        }
        $prasta_rec = $this->praLib->GetPrasta($proges_rec['GESNUM'], 'codice');
        $anatsp_rec = $this->praLib->GetAnatsp($proges_rec['GESTSP'], 'codice');
        $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA'], 'codice');

        $this->praImmobili = praImmobili::getInstance($this->praLib, $this->currGesnum);
        Out::tabSetTitle($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCatastali", $this->praImmobili->getTabTitle());

        /*
         * Mi istanzio l'oggetto dei soggetti escludendo il ruolo l'unità locale
         */
        $extraParam = array(
            "EXCLUDE_ROLES" => array(praRuolo::$SISTEM_SUBJECT_ROLES['UNITALOCALE']['RUOCOD'])
        );
        $this->praSoggetti = praSoggetti::getInstance($this->praLib, $this->currGesnum, $extraParam);
        /*
         * Mi istanzio l'oggetto unità locale includendo solo il ruolo unità locale
         */
        $extraParam = array(
            "INCLUDE_ROLES" => array(praRuolo::$SISTEM_SUBJECT_ROLES['UNITALOCALE']['RUOCOD'])
        );
        $this->praUnitaLocale = praSoggetti::getInstance($this->praLib, $this->currGesnum, $extraParam);

        $this->noteManager = praNoteManager::getInstance($this->praLib, praNoteManager::NOTE_CLASS_PROGES, $this->currGesnum);
        $anapra_rec = $this->praLib->GetAnapra($proges_rec['GESPRO']);
        $ananom_rec = $this->praLib->GetAnanom($proges_rec['GESRES']);

        $metaDati = proIntegrazioni::GetMetedatiProt($proges_rec['GESNUM']);
        if (isset($metaDati['Data']) && $proges_rec['GESNPR'] != 0) {
            Out::valore($this->nameForm . "_DataProtocollo", $metaDati['Data']);
            Out::show($this->nameForm . '_DataProtocollo_field');
        } else {
            Out::hide($this->nameForm . '_DataProtocollo_field');
        }

        if ($proges_rec['GESAMMPR'] && $proges_rec['GESAOOPR']) {
            Out::valore($this->nameForm . "_CodAmmAoo", $proges_rec['GESAMMPR'] . "/" . $proges_rec['GESAOOPR']);
            Out::show($this->nameForm . '_CodAmmAoo_field');
        } else {
            Out::hide($this->nameForm . '_CodAmmAoo_field');
        }

        $this->Nascondi();

        $pramail_rec = $this->praLib->GetPramailRecPratica($this->currGesnum);
        if ($pramail_rec) {
            Out::show($this->nameForm . "_VediMail");
        }

        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
        if ($PARMENTE_rec['TIPOPROTOCOLLO'] != 'Italsoft') {
            Out::hide($this->nameForm . '_Anno_prot_butt');
        }

        $open_Info = 'Oggetto: ' . $proges_rec['GESNUM'];
        $this->openRecord($this->PRAM_DB, 'PROGES', $open_Info);
        $Numero_procedimento = substr($proges_rec['GESNUM'], 4, 6) . " / " . substr($proges_rec['GESNUM'], 0, 4);
        Out::valore($this->nameForm . '_Numero_procedimento', $Numero_procedimento);
        $serie_rec = $this->proLibSerie->GetSerie($proges_rec['SERIECODICE'], 'codice');
        Out::valore($this->nameForm . '_Numero_serie', $serie_rec['SIGLA'] . " / " . $proges_rec['SERIEPROGRESSIVO'] . " / " . $proges_rec['SERIEANNO']);
        Out::valore($this->nameForm . '_UTENTEATTUALE', App::$utente->getKey('nomeUtente') . " - " . $this->profilo['COD_ANANOM']);
        Out::html($this->nameForm . '_PRASTA[STADES]', $prasta_rec['STADES']);
        Out::valore($this->nameForm . '_ANATSP[TSPDES]', $anatsp_rec['TSPDES']);
        Out::valore($this->nameForm . '_ANASPA[SPADES]', $anaspa_rec['SPADES']);
        Out::valori($proges_rec, $this->nameForm . '_PROGES');

        if ($proges_rec['GESDCH'] == '') {
            Out::show($this->nameForm . '_Chiudi');
            Out::show($this->nameForm . '_ImportPassi');
            Out::show($this->nameForm . '_Aggiorna');
            Out::show($this->nameForm . '_Annulla');
            $this->ShowButtonGrid();
        } else {
            Out::show($this->nameForm . '_Apri');
            Out::show($this->nameForm . '_AggiornaStatistici');
            $this->HideButtonGrid();
        }
        Out::valore($this->nameForm . '_Anno_prot', substr($proges_rec['GESNPR'], 0, 4));
        Out::valore($this->nameForm . '_Numero_prot', substr($proges_rec['GESNPR'], 4));

        /*
         * Decod Antecedente
         */
        if ($proges_rec['GESPRE']) {
            $proges_rec_ant = $this->praLib->GetProges($proges_rec['GESPRE']);
            if ($proges_rec_ant) {
                $Serie_rec = $this->praLib->ElaboraProgesSerie($proges_rec['GESPRE']);
                $Serie = explode("/", $Serie_rec);
                Out::valore($this->nameForm . '_Gespre2', $Serie[2]);
                Out::valore($this->nameForm . '_Gespre1', $Serie[1]);
                Out::valore($this->nameForm . '_Gespre0', $Serie[0]);
                Out::valore($this->nameForm . '_Gespre3', $proges_rec_ant['SERIECODICE']);
                Out::hide($this->nameForm . '_CercaAnt');
            }
        } else {
            Out::valore($this->nameForm . '_Gespre2', "");
            Out::valore($this->nameForm . '_Gespre1', "");
            Out::valore($this->nameForm . '_Gespre0', "");
            Out::valore($this->nameForm . '_Gespre3', "");
            Out::show($this->nameForm . '_CercaAnt');
        }

        Out::valore($this->nameForm . '_Desc_proc2', $anapra_rec['PRADES__1']);
        //
        Out::valore($this->nameForm . '_Desc_resp2', $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM']);
        $this->DecodResponsabile($ananom_rec);

        $this->praAccorpati = $this->CaricaAccorpati($proges_rec['GESNUM']);
        $this->praPassi = $this->praLib->caricaPassiBO($proges_rec['GESNUM']);

        /*
         * New Controllo per filtrare i passi Privati, senza quando faccio dettaglio vedo anche le note dei passi privati
         */
        if (!$this->praPerms->checkSuperUser($proges_rec)) {
            $this->praPassi = $this->praPerms->filtraPassiView($this->praPassi);
        }
        //
        $this->praPassiAssegnazione = $this->caricaPassiAssegnazione($proges_rec['GESNUM']);
        $this->praPagamenti = array();
        $this->praDati = $this->praLib->caricaDati($proges_rec['GESNUM']);
        $this->praDatiPratica = $this->caricaDatiPratica($proges_rec['GESNUM']);
        $this->praComunicazioni = $this->caricaComunicazioni();
        $this->CaricaNote();
        if ($this->praDati) {
            Out::show($this->nameForm . '_EsportaDati');
        }
        if ($allegatiMail == "") {
            $Filent_rec = $this->praLib->GetFilent(15);
            if ($Filent_rec['FILDE1'] == 1) {
                $this->sha2View = true;
                $this->CaricaAllegatiPASSHA2($proges_rec['GESNUM'], $allegatiMail);
                $this->CaricaGriglia($this->gridAllegatiSha2, $this->praAlleSha2, "3");
                $this->ContaSizeAllegati($this->praAlleSha2, "TotaleSha");
                $this->praLibAllegati->BlockCellGridAllegati($this->praAlleSha2, $this->gridAllegatiSha2);
                Out::hide($this->nameForm . '_divGrigliaAllegati');
                Out::show($this->nameForm . '_divGrigliaAllegatiSha2');
            } else {
                $this->sha2View = false;
                $this->CaricaAllegati($proges_rec['GESNUM'], $allegatiMail);
                $this->CaricaGriglia($this->gridAllegati, $this->praAlle, '3');
                $this->ContaSizeAllegati($this->praAlle, "Totale");
                $this->praLibAllegati->BlockCellGridAllegati($this->praAlle, $this->gridAllegati);
                Out::show($this->nameForm . '_divGrigliaAllegati');
                Out::hide($this->nameForm . '_divGrigliaAllegatiSha2');
            }
        }

        Out::show($this->nameForm . '_PROGES[GESTSP]');
        Out::show($this->nameForm . '_PROGES[GESTSP]_lbl');
        Out::show($this->nameForm . '_ANATSP[TSPDES]');
        Out::show($this->nameForm . '_PROGES[GESSPA]');
        Out::show($this->nameForm . '_PROGES[GESSPA]_lbl');
        Out::show($this->nameForm . '_ANASPA[SPADES]');
        //
        $arrayVisibilita = $this->praLib->GetVisibiltaSportello();
        if (count($arrayVisibilita["SPORTELLI"]) == 0 && $arrayVisibilita["AGGREGATO"] == 0) {
            $this->ShowHideLentineSportelli($proges_rec);
        } else {
            $arrSportelliSearch = array();
            foreach ($arrayVisibilita['SPORTELLI'] as $sportello) {
                $arrSportello = array();
                if (strpos($sportello, '/') !== false) {
                    $arrSportello = explode("/", $sportello);
                    $arrSportelliSearch[] = $arrSportello[0];
                } else {
                    $arrSportelliSearch[] = $sportello;
                }
            }
            //if (array_search($proges_rec['GESTSP'], $arrayVisibilita['SPORTELLI']) && count($arrayVisibilita['SPORTELLI']) != 0) {
            if (array_search($proges_rec['GESTSP'], $arrSportelliSearch) && count($arrayVisibilita['SPORTELLI']) != 0) {
                Out::hide($this->nameForm . '_PROGES[GESTSP]_butt');
                Out::attributo($this->nameForm . '_PROGES[GESTSP]', 'readonly', '0');
            } else {
                $this->ShowHideLentineSportelli($proges_rec, "GESTSP");
            }
            if ($arrayVisibilita["AGGREGATO"] == $proges_rec['GESSPA']) {
                if (!$isSuperUser) {
                    Out::hide($this->nameForm . '_PROGES[GESSPA]_butt');
                    Out::attributo($this->nameForm . '_PROGES[GESSPA]', 'readonly', '0');
                } else {
                    $this->ShowHideLentineSportelli($proges_rec, "TUTTI");
                }
            } else {
                $this->ShowHideLentineSportelli($proges_rec, "GESSPA");
            }
        }

        if (!$isSuperUser) {
            Out::hide($this->gridPassiAssegnazione . '_delGridRow');
        } else {
            Out::show($this->gridPassiAssegnazione . '_delGridRow');
        }

        $this->CaricaGriglia($this->gridPassi, $this->praPassi, '3');
        //New
        $this->DisattivaCellStatoPasso();
        $this->CaricaGriglia($this->gridPassiAssegnazione, $this->praPassiAssegnazione, '3');
        $this->CaricaGriglia($this->gridDatiPratica, $this->praDatiPratica);
        $this->CaricaGriglia($this->gridImmobili, $this->praImmobili->getGriglia());
        $this->CaricaGriglia($this->gridSoggetti, $this->praSoggetti->getGriglia());
        $this->CaricaGriglia($this->gridUnitaLocale, $this->praUnitaLocale->getGriglia());
        $this->CaricaGriglia($this->gridAccorpati, $this->praAccorpati);
        if ($this->flagPagamenti) {
            $this->CaricaGriglia($this->gridOneri, $this->getOneri());
            $this->CaricaGriglia($this->gridPagamenti, $this->getPagamenti());
            $this->totalePagamenti();
        }


        $Metadati = $this->praLib->GetMetadatiProges($proges_rec['GESNUM']);
        $this->switchIconeProtocollo($proges_rec['GESNPR'], $Metadati);
//
        Out::show($this->nameForm . '_AltreFunzioni');
        Out::show($this->nameForm . '_StampaDettaglio');
        Out::show($this->nameForm . '_Etichetta');

        $this->bottoniAssegnazione($proges_rec['GESDCH']);
//
        Out::unblock($this->nameForm . "_divIntestatario");
        Out::unblock($this->nameForm . "_divUnitaLocale");

        Out::hide($this->nameForm . '_PROGES[GESPRA]_field');
        Out::hide($this->nameForm . '_PROGES[GESKEY]_field');
        if ($proges_rec['GESPRA'] != '') {
            Out::show($this->nameForm . '_PROGES[GESPRA]_field');
        }
        if ($proges_rec['GESKEY'] != '') {
            Out::hide($this->nameForm . '_PROGES[GESPRA]_field');
            Out::show($this->nameForm . '_PROGES[GESKEY]_field');
        }

        $Filent_rec = $this->praLib->GetFilent(17);
        if ($Filent_rec['FILVAL'] == 1) {
            Out::show($this->nameForm . '_PROGES[GESTIP]_butt');
            Out::show($this->nameForm . '_PROGES[GESSTT]_butt');
            Out::show($this->nameForm . '_PROGES[GESATT]_butt');
            Out::attributo($this->nameForm . '_PROGES[GESTIP]', 'readonly', '1');
            Out::attributo($this->nameForm . '_PROGES[GESSTT]', 'readonly', '1');
            Out::attributo($this->nameForm . '_PROGES[GESATT]', 'readonly', '1');
        } else {
            Out::hide($this->nameForm . '_PROGES[GESTIP]_butt');
            Out::hide($this->nameForm . '_PROGES[GESSTT]_butt');
            Out::hide($this->nameForm . '_PROGES[GESATT]_butt');
            Out::attributo($this->nameForm . '_PROGES[GESTIP]', 'readonly', '0');
            Out::attributo($this->nameForm . '_PROGES[GESSTT]', 'readonly', '0');
            Out::attributo($this->nameForm . '_PROGES[GESATT]', 'readonly', '0');
        }
        $anatip_rec = $this->praLib->GetAnatip($proges_rec['GESTIP']);
        $anaset_rec = $this->praLib->GetAnaset($proges_rec['GESSTT']);
        $anaatt_rec = $this->praLib->GetAnaatt($proges_rec['GESATT']);
        $anaeventi_rec = $this->praLib->GetAnaeventi($proges_rec['GESEVE']);
        Out::valore($this->nameForm . "_Desc_tip", $anatip_rec['TIPDES']);
        Out::valore($this->nameForm . "_Desc_set", $anaset_rec['SETDES']);
        Out::valore($this->nameForm . "_Desc_att", $anaatt_rec['ATTDES']);
        Out::valore($this->nameForm . "_Desc_eve", $anaeventi_rec['EVTDESCR']);
        //
        Out::attributo($this->nameForm . '_PROGES[GESDCH]', 'readonly', '0');
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCatastali");
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_panePassi");
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAllegati");
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAggiuntivi");
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneComunicazioni");
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneNote");

        if ($proges_rec['GESPRA'] || $proges_rec['GESKEY']) {
            Out::attributo($this->nameForm . '_PROGES[GESDRI]', 'readonly', '0');
            Out::attributo($this->nameForm . '_PROGES[GESORA]', 'readonly', '0');
        } else {
            Out::attributo($this->nameForm . '_PROGES[GESDRI]', 'readonly', '1');
            Out::attributo($this->nameForm . '_PROGES[GESORA]', 'readonly', '1');
        }
        Out::setFocus('', $this->nameForm . '_ANAUNI[UNISET]');
        if ($apriForm) {
            Out::hide($this->nameForm . '_AltraRicerca');
        }

        if ($rigaSel) {
            Out::codice("jQuery('#$this->gridPassi').jqGrid('setSelection','$rigaSel');");
        } else {
            Out::show($this->divGes);
        }
        $proges_rec_ant = $this->praLib->GetProges($proges_rec['GESNUM'], "antecedente");
        if ($proges_rec['GESPRE'] || $proges_rec_ant) {
            Out::show($this->nameForm . '_VediStorico');
        }

        /*
         * Decodifico il calendario
         */
        $idCalendar = $this->praLib->DecodCalendar($proges_rec['ROWID'], "SUAP_PRATICA");
        Out::valore($this->nameForm . "_Calendario", "");
        if ($idCalendar) {
            Out::valore($this->nameForm . "_Calendario", $idCalendar);
        }

        if ($this->praReadOnly == true) {
            $this->HideButton();
        }

        /* @var $praCompDatiAggiuntivi praCompDatiAggiuntivi */
        $praCompDatiAggiuntivi = itaModel::getInstance('praCompDatiAggiuntivi', $this->praCompDatiAggiuntiviFormname);
        $praCompDatiAggiuntivi->openGestione($this->currGesnum);

        Out::hide($this->nameForm . '_divInfoPraFas');
        if ($proges_rec['GESKEY']) {
            $messaggio = "<span style=\"color:red\">FASCICOLO ARCHIVISTICO: " . $proges_rec['GESKEY'] . " </span>";
            $messaggio .= ' <a href="#" id="' . $proges_rec['GESKEY'] . '" class="ita-hyperlink {event:\'' . $this->nameForm . '_VaiAPratica\'}"><span style="display:inline-block;vertical-align:bottom;" title="Vai al Fascicolo" class="ita-tooltip ita-icon ita-icon-open-folder-16x16"></span>Vai al Fascicolo</a>';
            Out::show($this->nameForm . '_divInfoPraFas');
            Out::html($this->nameForm . "_divInfoPraFas", $messaggio);
        }
        /*
         * Bottone creazione fascicolo archivistico:
         */
        if ($this->praLibFascicoloArch->CheckPraticaSenzaFascicoloArchivistico($proges_rec['GESNUM'])) {
            Out::show($this->nameForm . '_CreaFasArch');
        }

        /*
         * Gestione attivazioni Tab se presente configurazione nella serie archivistica usata nella pratica
         */
        $arrayStatiTab = $this->praLibPratica->setStatiTabPratica($proges_rec, $this->flagAssegnazioni, $this->flagPagamenti);
        $this->caricaTabPratica($arrayStatiTab);
        
        
        return true;
    }

    public function ShowHideLentineSportelli($proges_rec, $tipo = "TUTTI") {
        if (!$this->praPerms->checkSuperUser($proges_rec)) {
            //$this->praPassi = $this->praPerms->filtraPassiView($this->praPassi);
            switch ($tipo) {
                case "TUTTI":
                    if ($proges_rec['GESTSP'] == 0) {
                        Out::show($this->nameForm . '_PROGES[GESTSP]_butt');
                        Out::attributo($this->nameForm . '_PROGES[GESTSP]', 'readonly', '1');
                    } else {
                        Out::hide($this->nameForm . '_PROGES[GESTSP]_butt');
                        Out::attributo($this->nameForm . '_PROGES[GESTSP]', 'readonly', '0');
                    }
                    if ($proges_rec['GESSPA'] == 0) {
                        Out::show($this->nameForm . '_PROGES[GESSPA]_butt');
                        Out::attributo($this->nameForm . '_PROGES[GESSPA]', 'readonly', '1');
                    } else {
                        Out::hide($this->nameForm . '_PROGES[GESSPA]_butt');
                        Out::attributo($this->nameForm . '_PROGES[GESSPA]', 'readonly', '0');
                    }
                    break;
                case "GESSPA":
                    if ($proges_rec['GESSPA'] == 0) {
                        Out::show($this->nameForm . '_PROGES[GESSPA]_butt');
                        Out::attributo($this->nameForm . '_PROGES[GESSPA]', 'readonly', '1');
                    } else {
                        Out::hide($this->nameForm . '_PROGES[GESSPA]_butt');
                        Out::attributo($this->nameForm . '_PROGES[GESSPA]', 'readonly', '0');
                    }

                    break;
                case "GESTSP":
                    if ($proges_rec['GESTSP'] == 0) {
                        Out::show($this->nameForm . '_PROGES[GESTSP]_butt');
                        Out::attributo($this->nameForm . '_PROGES[GESTSP]', 'readonly', '1');
                    } else {
                        Out::hide($this->nameForm . '_PROGES[GESTSP]_butt');
                        Out::attributo($this->nameForm . '_PROGES[GESTSP]', 'readonly', '0');
                    }
                    break;
            }
        } else {
            switch ($tipo) {
                case "TUTTI":
                    Out::show($this->nameForm . '_PROGES[GESTSP]_butt');
                    Out::show($this->nameForm . '_PROGES[GESSPA]_butt');
                    Out::attributo($this->nameForm . '_PROGES[GESSPA]', 'readonly', '1');
                    Out::attributo($this->nameForm . '_PROGES[GESTSP]', 'readonly', '1');
                    break;
                case "GESSPA":
                    Out::show($this->nameForm . '_PROGES[GESSPA]_butt');
                    Out::attributo($this->nameForm . '_PROGES[GESSPA]', 'readonly', '1');
                    break;
                case "GESTSP":
                    Out::show($this->nameForm . '_PROGES[GESTSP]_butt');
                    Out::attributo($this->nameForm . '_PROGES[GESTSP]', 'readonly', '1');
                    break;
            }
        }
    }

    public function HideButtonGrid() {
        Out::hide($this->gridPassi . '_addGridRow');
//
        Out::hide($this->gridSoggetti . '_delGridRow');
        Out::hide($this->gridSoggetti . '_addGridRow');
//
        Out::hide($this->gridImmobili . '_delGridRow');
        Out::hide($this->gridImmobili . '_addGridRow');
//
        Out::hide($this->gridAllegati . '_delGridRow');
        Out::hide($this->gridAllegati . '_addGridRow');
//
        Out::hide($this->gridAllegatiSha2 . '_delGridRow');
        Out::hide($this->gridAllegatiSha2 . '_addGridRow');
//
        Out::hide($this->gridDatiPratica . '_delGridRow');
        Out::hide($this->gridDatiPratica . '_addGridRow');
//        
        Out::hide($this->gridNote . '_delGridRow');
        Out::hide($this->gridNote . '_addGridRow');
//
        Out::hide($this->gridUnitaLocale . '_delGridRow');
        Out::hide($this->gridUnitaLocale . '_addGridRow');
//
        Out::hide($this->gridPassiAssegnazione . '_delGridRow');
//
        Out::hide($this->gridPagamenti . '_delGridRow');
        Out::hide($this->gridPagamenti . '_addGridRow');
//
        Out::hide($this->gridOneri . '_delGridRow');
        Out::hide($this->gridOneri . '_addGridRow');
    }

    public function ShowButtonGrid() {
        Out::show($this->gridPassi . '_addGridRow');
//
        Out::show($this->gridSoggetti . '_delGridRow');
        Out::show($this->gridSoggetti . '_addGridRow');
//
        Out::show($this->gridImmobili . '_delGridRow');
        Out::show($this->gridImmobili . '_addGridRow');
//
        Out::show($this->gridAllegati . '_delGridRow');
        Out::show($this->gridAllegati . '_addGridRow');
//
        Out::show($this->gridAllegatiSha2 . '_delGridRow');
        Out::show($this->gridAllegatiSha2 . '_addGridRow');
//
        Out::show($this->gridDatiPratica . '_delGridRow');
        Out::show($this->gridDatiPratica . '_addGridRow');
//        
        Out::show($this->gridNote . '_delGridRow');
        Out::show($this->gridNote . '_addGridRow');
//
        Out::show($this->gridUnitaLocale . '_delGridRow');
        Out::show($this->gridUnitaLocale . '_addGridRow');
//
        Out::show($this->gridPassiAssegnazione . '_delGridRow');
//
        Out::show($this->gridPagamenti . '_delGridRow');
        Out::show($this->gridPagamenti . '_addGridRow');
//
        Out::show($this->gridOneri . '_delGridRow');
        Out::show($this->gridOneri . '_addGridRow');
    }

    public function HideButton() {
        Out::hide($this->nameForm . '_divBottoni');
//
        Out::hide($this->nameForm . '_Protocolla');
        Out::hide($this->nameForm . '_RimuoviProtocolla');
//
        Out::hide($this->nameForm . '_CopiaUltimo');
        Out::hide($this->nameForm . '_CopiaPasso');
//
        Out::hide($this->nameForm . '_divBottoniAllega');
//        
        Out::hide($this->nameForm . '_Down');
        Out::hide($this->nameForm . '_Up');
//

        Out::hide($this->gridSoggetti . '_delGridRow');
        Out::hide($this->gridSoggetti . '_addGridRow');
//
        Out::hide($this->gridImmobili . '_delGridRow');
        Out::hide($this->gridImmobili . '_addGridRow');
//
        $this->HideButtonGrid();
    }

//TODO: Da eliminare dopo aver testato la funzione su pralib
    public function SincDataScadenza($dataSca, $pragio, $giorni, $dataReg, $sinc = false) {
        $proges_rec = $this->praLib->GetProges($this->currGesnum);
        $scadenza = $dataSca;
        if ($giorni != 0) {
            $data1 = strtotime($dataReg);
            $data2 = $giorni * 3600 * 24;
            $somma = $data1 + $data2;
            $scadenza = date('Ymd', $somma);
        } else {
            if ($pragio != 0) {
                $data1 = strtotime($dataReg);
                $data2 = $pragio * 3600 * 24;
                $somma = $data1 + $data2;
                $scadenza = date('Ymd', $somma);
                $giorni = $pragio;
            } else {
                $scadenza = "";
                $giorni = 0;
            }
        }
        if ($dataSca && $proges_rec['GESDSC'] != $dataSca) {
            $scadenza = $dataSca;
            $data1 = strtotime($dataReg);
            $data2 = strtotime($dataSca);
            $giorni = intval((($data2 - $data1) / 3600) / 24);
        }
        Out::valore($this->nameForm . "_PROGES[GESDSC]", $scadenza);
        Out::valore($this->nameForm . "_PROGES[GESGIO]", $giorni);
//
        if ($sinc == true) {
            $proges_rec['GESDSC'] = $scadenza;
            $proges_rec['GESGIO'] = $giorni;
            $update_Info = "Oggetto sincronizzazione data scadenza pratica numero: " . $proges_rec['GESNUM'];
            if (!$this->updateRecord($this->PRAM_DB, 'PROGES', $proges_rec, $update_Info)) {
                return false;
            }
        }
        return true;
    }

    public function checkExistDB($db, $dittaCOMM = "") {
        try {
            if ($dittaCOMM) {
                $DB = ItaDB::DBOpen($db, $dittaCOMM);
            } else {
                $DB = ItaDB::DBOpen($db);
            }
            $arrayTables = $DB->listTables();
        } catch (Exception $exc) {
            return false;
        }
        if ($DB == "") {
            return false;
        } else {
            if (!$arrayTables) {
                return false;
            }
        }
        return true;
    }

    function GetDatiMailProtocollo($allegati, $elementi) {
        $Proges_rec = $this->praLib->GetProges($this->currGesnum);
        //$Anapra_rec = $this->praLib->GetAnapra($Proges_rec['GESPRO']);
        //$Anatsp_rec = $this->praLib->GetAnatsp($Anapra_rec['PRATSP']);
        $Anatsp_rec = $this->praLib->GetAnatsp($Proges_rec['GESTSP']);

        $dati['allegatiProt'] = array();

        foreach ($allegati as $allegato) {
            if ($allegato['isLeaf'] == "true") {
                $allega = true;
                $pasdoc_rec = $this->praLib->GetPasdoc($allegato['ROWID'], "ROWID");
                if ($pasdoc_rec['PASKEY'] == $this->currGesnum) {
                    $Path = $this->praLib->SetDirectoryPratiche(substr($this->currGesnum, 0, 4), $this->currGesnum, 'PROGES');
                } else {
                    $Path = $this->praLib->SetDirectoryPratiche(substr($pasdoc_rec['PASKEY'], 0, 4), $pasdoc_rec['PASKEY']);
                    $propas_rec = $this->praLib->GetPropas($pasdoc_rec['PASKEY'], "propak");
                    if ($propas_rec['PROPUB'] <> 1) {
                        $allega = false;
                    }
                }
                if ($allega) {
                    $dati['allegatiProt'][] = array(
                        "FILEORIG" => $pasdoc_rec['PASNAME'],
                        "FILENAME" => $pasdoc_rec['PASNAME'],
                        "FILEINFO" => $pasdoc_rec['PASNOT'],
                        "FILEPATH" => $Path . "/" . $pasdoc_rec['PASFIL']
                    );
                }
            }
        }


        $Filent_rec_oggetto = $this->praLib->GetFilent(39);
        $Filent_rec_corpo = $this->praLib->GetFilent(40);
        $praLibVar = new praLibVariabili();
        $praLibVar->setCodicePratica($this->currGesnum);

        $oggetto = "Inoltro Pratica per protocollazione N.$this->currGesnum, Richiesta N. " . $Proges_rec['GESPRA'];
        if ($Filent_rec_oggetto['FILVAL']) {
            $oggetto = $this->praLib->GetStringDecode($praLibVar->getVariabiliPratica(true)->getAllData(), $Filent_rec_oggetto['FILVAL']);
        }

        $corpo = "Si inoltra per protocollazione la pratica n. <b>$this->currGesnum</b>, sportello on line <b>" . $Anatsp_rec['TSPDES'] . "</b><br><br>" . $elementi['dati']['Oggetto'];
        if ($Filent_rec_corpo['FILVAL']) {
            $corpo = $this->praLib->GetStringDecode($praLibVar->getVariabiliPratica(true)->getAllData(), $Filent_rec_corpo['FILVAL']);
        }

        $dati['valori'] = array(
            "Destinatario" => "Ufficio Protocollo",
            "Oggetto" => $oggetto,
            "Procedimento" => $this->currGesnum,
            "Corpo" => $corpo,
            "Anno" => $_POST[$this->nameForm . "_Anno_prot"],
            "Numero" => $_POST[$this->nameForm . "_Numero_prot"],
        );

        $dati["returnModel"] = $this->nameForm;
        return $dati;
    }

    function caricaDatiPratica($procedimento) {
        $DatiPratica = $this->praLib->GetProdag($procedimento, "dagpak", true);
        return $DatiPratica;
    }

    function caricaPassiItepas($procedimento, $dbSuffix = "") {
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
                    ITEPAS.ITEKPRE AS ITEKPRE,
                    ITEPAS.ROWID_DOC_CLASSIFICAZIONE AS ROWID_DOC_CLASSIFICAZIONE,
                    PRACLT.CLTDES AS CLTDES," .
                $Pram_db->strConcat('ANANOM.NOMCOG', "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE
                FROM ITEPAS LEFT OUTER JOIN ANANOM ON ANANOM.NOMRES=ITEPAS.ITERES
                LEFT OUTER JOIN PRACLT ON PRACLT.CLTCOD=ITEPAS.ITECLT
                WHERE ITEPAS.ITECOD = '" . $procedimento . "' ORDER BY ITESEQ";
        $passi = ItaDB::DBSQLSelect($Pram_db, $sql);
        return $passi;
    }

    function caricaPassiAssegnazione($procedimento) {
        $sql = $this->CreaSqlCaricaPassiAssegnazione($procedimento);
        try {
            $passi_view = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
            if ($passi_view) {
                foreach ($passi_view as $keyPasso => $value) {
                    $data = $msgStato = $icon = $acc = $cons = "";
                    if ($value['PROSTATO'] != 0) {
                        $Anastp_rec = $this->praLib->GetAnastp($value['PROSTATO']);
                        $msgStato = $Anastp_rec['STPFLAG'];
                    }
                    if ($value['PROFIN']) {
                        if ($msgStato == 'In corso') {
                            $msgStato = "";
                        }
                        $msgStato = $Anastp_rec['STPDES'];
                        $passi_view[$keyPasso]['STATOPASSO'] = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-check-green-24x24\">Passo Chiuso</span><span style=\"vertical-align:top;display:inline-block;\">$msgStato</span>";
                    } elseif ($value['PROINI']) {
                        $passi_view[$keyPasso]['STATOPASSO'] = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-check-red-24x24\">Passo Aperto</span><span style=\"display:inline-block;\">$msgStato</span>";
                    }
                    $passi_view[$keyPasso]['DATAASS'] = $value['PRODATEADD'];
                    $passi_view[$keyPasso]['MITTENTE'] = $value['PROUTEADD'];
                    $passi_view[$keyPasso]['DESTINATARIO'] = $value['RESPONSABILE'];
                    $passi_view[$keyPasso]['DESCRIZIONE'] = $value['PRODPA'];
                    $passi_view[$keyPasso]['DATIAGGIUNITIVI'] = "";
                    $passi_view[$keyPasso]['PROANN'] = "<div style =\"height:50px;overflow:auto;\" class=\"ita-Wordwrap\"><div>" . $value['PROANN'] . "</div>";
                    $passi_view[$keyPasso]['STATOPASSO'] = $msgStato;
                    $pracomP_rec = $this->praLib->GetPracomP($value['PROPAK']);
                    if ($pracomP_rec['COMPRT']) {
                        $numProt = substr($pracomP_rec['COMPRT'], 4) . "/" . substr($pracomP_rec['COMPRT'], 0, 4);
                        $dataProt = substr($pracomP_rec['COMDPR'], 6, 2) . "/" . substr($pracomP_rec['COMDPR'], 4, 2) . "/" . substr($pracomP_rec['COMDPR'], 0, 4);
                        $passi_view[$keyPasso]['PRPARTENZA'] = $numProt . "<br>" . $dataProt;
                    }
                    $pracomA_rec = $this->praLib->GetPracomA($value['PROPAK']);
                    if ($pracomA_rec['COMPRT']) {
                        $numProt = substr($pracomA_rec['COMPRT'], 4) . "/" . substr($pracomA_rec['COMPRT'], 0, 4);
                        $dataProt = substr($pracomA_rec['COMDPR'], 6, 2) . "/" . substr($pracomA_rec['COMDPR'], 4, 2) . "/" . substr($pracomA_rec['COMDPR'], 0, 4);
                        $passi_view[$keyPasso]['PRARRIVO'] = $numProt . "<br>" . $dataProt;
                    }

                    $passi_view[$keyPasso]['VAI'] = $this->praLib->decodificaImmagineSaltoPasso($value['PROQST'], $value['PROVPA'], $value['PROVPN'], $this->praLib->GetProvpadett($value['PROPAK'], 'propak'));

                    if ($value['PROPART'] != 0) {
                        $passi_view[$keyPasso]['ARTICOLO'] = '<span class="ita-icon ita-icon-rtf-24x24">Articolo</span>';
                    }
                    if ($value['PROCTR'] != '') {
                        $passi_view[$keyPasso]['PROCEDURA'] = '<span class="ita-icon ita-icon-ingranaggio-16x16">Procedura di Controllo</span>';
                    }
                    if ($value['PROALL']) {
                        $passi_view[$keyPasso]['ALLEGATI'] = '<span class="ita-icon ita-icon-clip-16x16">allegati</span>';
                    }
                    $passi_view[$keyPasso]['STATOCOM'] = $this->praLib->GetIconStatoCom($value['PROPAK']);
                }
            }
            return $passi_view;
        } catch (Exception $e) {
            Out::msgStop('Errore DB', $e->getMessage());
            return false;
        }
    }

    function CreaSqlCaricaPassiAssegnazione($procedimento) {
        $sql = "SELECT
                    PROPAS.ROWID AS ROWID,
                    PROPAS.PROSEQ AS PROSEQ,
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
                    PROPAS.PRODATEADD AS PRODATEADD,
                    PROPAS.PROUTEADD AS PROUTEADD,
                    PROPAS.PROUTEEDIT AS PROUTEEDIT,
                    PROPAS.PROOPE AS PROOPE,
                    PROPAS.PROANN AS PROANN,
                    PROPAS.PROINI AS PROINI," .
                $this->PRAM_DB->strConcat('ANANOM.NOMCOG', "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE
                FROM PROPAS
                    LEFT OUTER JOIN ANANOM ON ANANOM.NOMRES=PROPAS.PRORPA
               WHERE 
                    PROPAS.PRONUM='$procedimento' AND
                    PROPAS.PROOPE<>''  
               ORDER BY
                    ROWID";
        return $sql;
    }

    function ApriScanner() {
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
        $_POST[$modelTwain . '_flagPDFA'] = $this->praLib->getFlagPDFA();
        $_POST[$modelTwain . '_returnModel'] = $this->nameForm;
        $_POST[$modelTwain . '_returnMethod'] = 'returnFileFromTwain';
        $_POST[$modelTwain . '_returnField'] = $this->nameForm . '_Scanner';
        $_POST[$modelTwain . '_closeOnReturn'] = '1';
        $modelTwain();
    }

    function SalvaScanner() {
        $randName = $_POST['retFile'];
        $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
        $timeStamp = date("Ymd_His");
        $origFile = "Scansione_" . $timeStamp . "." . pathinfo($randName, PATHINFO_EXTENSION);
        if ($this->sha2View == true) {
            $this->aggiungiAllegatoSHA($randName, $destFile, $origFile);
        } else {
            $this->aggiungiAllegato($randName, $destFile, $origFile);
        }
    }

    function AllegaFile($sha2 = false) {
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
                if (App::$utente->getKey('ditta') != "D007") {
                    $this->praLib->AnalizzaP7m($uplFile);
                }
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
                            if ($this->sha2View == true) {
                                $this->aggiungiAllegatoSHA($randName, $destFile, $origFile);
                            } else {
                                $this->aggiungiAllegato($randName, $destFile, $origFile);
                            }
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
                    if ($this->sha2View == true) {
                        $this->aggiungiAllegatoSHA($randName, $destFile, $origFile);
                    } else {
                        $this->aggiungiAllegato($randName, $destFile, $origFile);
                    }
                }
            }
        } else {
            Out::msgStop("Upload File", "Errore in Upload");
        }
    }

    function aggiungiAllegato($randName, $destFile, $origFile) {
        if (!$destFile) {
            return;
        }
        foreach ($this->praAlle as $allegato) {
            if ($allegato['parent'] == 'seq_GEN' || ($allegato['SEQ'] == 'seq_GEN' && $allegato['parent'] == null)) {
                $arrayGenerale[] = $allegato;
            }
        }
        if (!$arrayGenerale) {
//Padre Allegati Generali
            $chiave = count($this->praAlle) + 1;
            $arrayGenerali['SEQ'] = 'seq_GEN';
            $arrayGenerali['NAME'] = "Allegati Generali";
            $arrayGenerali['level'] = 0;
            $arrayGenerali['parent'] = null;
            $arrayGenerali['isLeaf'] = 'false';
            $arrayGenerali['expanded'] = 'true';
            $arrayGenerali['loaded'] = 'true';
            $this->praAlle[$chiave] = $arrayGenerali;
        }
        $ext = pathinfo($destFile, PATHINFO_EXTENSION);
        if (strtolower($ext) == "p7m") {
            $edit = "<span class=\"ita-icon ita-icon-shield-blue-16x16\">Verifica Firma</span>";
        } else if (strtolower($ext) == "zip") {
            $edit = "<span class=\"ita-icon ita-icon-winzip-16x16\">estrai File Zip</span>";
        }

        $chiave = count($this->praAlle) + 1;
        $arrayGen['SEQ'] = $chiave;
        $arrayGen['NAME'] = "<span style = \"color:orange;\">$origFile</span>";
        $arrayGen['NOTE'] = "File originale: " . $origFile;
        $arrayGen['INFO'] = 'GENERALE';
        $arrayGen['EDIT'] = $edit;
        $arrayGen['EVIDENZIA'] = "<div class=\"ita-html\"><span class=\"ita-icon ita-icon-evidenzia-16x16 ita-colorpicker {type: 'divColor',swatches:true}\">Evidenzia Allegato</span></div>";
        $arrayGen['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-16x16\">Blocca Allegato</span>";

//Valorizzo Array
        $arrayGen['FILENAME'] = $randName;
        $arrayGen['FILEINFO'] = "File originale: " . $origFile;
        $arrayGen['FILEORIG'] = $origFile;
        $arrayGen['FILEPATH'] = $destFile;
        $arrayGen['PASLOCK'] = 0;
        $arrayGen['ROWID'] = 0;
        $arrayGen['SIZE'] = $this->praLib->formatFileSize(filesize($arrayGen['FILEPATH']));
        $arrayGen['level'] = 1;
        $arrayGen['parent'] = 'seq_GEN';
        $arrayGen['isLeaf'] = 'true';
        $this->praAlle[$chiave] = $arrayGen;
        $this->CaricaGriglia($this->gridAllegati, $this->praAlle, '3');
        $this->praLibAllegati->BlockCellGridAllegati($this->praAlle, $this->gridAllegati);
        Out::setFocus('', $this->nameForm . '_wrapper');
    }

    function aggiungiAllegatoSHA($randName, $destFile, $origFile) {
        if (!$destFile) {
            return;
        }
        $ext = pathinfo($destFile, PATHINFO_EXTENSION);
        if (strtolower($ext) == "p7m") {
            $edit = "<span class=\"ita-icon ita-icon-shield-blue-16x16\">Verifica Firma</span>";
        } else if (strtolower($ext) == "zip") {
            $edit = "<span class=\"ita-icon ita-icon-winzip-16x16\">estrai File Zip</span>";
        }
        $arrayGen['NAME'] = "<span style = \"color:orange;\">$origFile</span>";
        $arrayGen['NOTE'] = "File originale: " . $origFile;
        $arrayGen['INFO'] = 'GENERALE';
        $arrayGen['EDIT'] = $edit;
        $arrayGen['EVIDENZIA'] = "<div class=\"ita-html\"><span class=\"ita-icon ita-icon-evidenzia-16x16 ita-colorpicker {type: 'divColor',swatches:true}\">Evidenzia Allegato</span></div>";
        $arrayGen['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-16x16\">Blocca Allegato</span>";
//Valorizzo Array
        $arrayGen['FILENAME'] = $randName;
        $arrayGen['FILEINFO'] = "File originale: " . $origFile;
        $arrayGen['FILEORIG'] = $origFile;
        $arrayGen['FILEPATH'] = $destFile;
        $arrayGen['PASLOCK'] = 0;
        $arrayGen['ROWID'] = 0;
        $arrayGen['SIZE'] = $this->praLib->formatFileSize(filesize($arrayGen['FILEPATH']));
        $this->praAlleSha2[] = $arrayGen;
        $this->CaricaGriglia($this->gridAllegatiSha2, $this->praAlleSha2, "3");
        $this->praLibAllegati->BlockCellGridAllegati($this->praAlleSha2, $this->gridAllegatiSha2);
        Out::setFocus('', $this->nameForm . '_wrapper');
    }

    function CaricaGriglia($griglia, $appoggio, $tipo = '1', $caption = '') {
        if ($caption) {
            out::codice("$('#$griglia').setCaption('$caption');");
        }
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
            $this->page = $_POST['page'];
            $ita_grid01->setPageNum($_POST['page']);

            $ita_grid01->setPageRows(1000);
        }
        if ($_POST['sord']) {
            $ita_grid01->setSortIndex($_POST['sidx']);
            $ita_grid01->setSortOrder($_POST['sord']);
        }
        TableView::enableEvents($griglia);
        $ita_grid01->getDataPage('json', true);
        return;
    }

    function CaricaAllegatiPASSHA2($allegatiMail) {
//Copio il file XML
        if ($allegatiMail) {
            if (!$this->praLib->RegistraXmlInfo($allegatiMail, $this->currGesnum)) {
                return false;
            }
        }
//Carico alleagi
        $sqlPassi = $this->praLib->CreaSqlCaricaPassi($this->currGesnum, true);
        $passi_view_tmp = ItaDB::DBSQLSelect($this->PRAM_DB, $sqlPassi);

        /*
         * Ordinamento passi in base agli antecedenti
         */
        $passi_view = $this->praLib->ordinaPassiConAntecedenti($passi_view_tmp);
        //
        $arrayAlleSha = array();
        $sql = "SELECT PASDOC.*, COUNT(PASSHA2) AS NUMALLEGATI FROM PASDOC WHERE PASKEY = '$this->currGesnum'";
        foreach ($passi_view as $passo) {
            $sql .= " OR PASKEY='{$passo['PROPAK']}'";
        }

        $sql .= " GROUP BY PASSHA2 ORDER BY ROWID";
        $Pasdoc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($Pasdoc_tab) {
            foreach ($Pasdoc_tab as $Pasdoc_rec) {
                $arrayAlleSha[] = $Pasdoc_rec;
            }
        }
        $this->praAlleSha2 = $this->ElaboraRecordPasdoc($arrayAlleSha);
    }

    function CaricaAllegatiPASSHA2_OLD($allegatiMail) {
//Copio il file XML
        if ($allegatiMail) {
            if (!$this->praLib->RegistraXmlInfo($allegatiMail, $this->currGesnum)) {
                return false;
            }
        }
//Carico alleagi
        $sqlPassi = $this->praLib->CreaSqlCaricaPassi($this->currGesnum, true);
        $passi_view_tmp = ItaDB::DBSQLSelect($this->PRAM_DB, $sqlPassi);

        /*
         * Ordinamento passi in base agli antecedenti
         */
        $passi_view = $this->praLib->ordinaPassiConAntecedenti($passi_view_tmp);
        //
        $arrayAlleSha = array();
        $sql = "SELECT PASDOC.*, COUNT(PASSHA2) AS NUMALLEGATI FROM PASDOC WHERE PASKEY = '$this->currGesnum'";
        foreach ($passi_view as $passo) {
            $sql .= " OR PASKEY='{$passo['PROPAK']}'";
        }

        $sql .= " GROUP BY PASSHA2 ORDER BY ROWID";
        $Pasdoc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($Pasdoc_tab) {
            foreach ($Pasdoc_tab as $Pasdoc_rec) {
                $arrayAlleSha[] = $Pasdoc_rec;
            }
        }
        $this->praAlleSha2 = $this->ElaboraRecordPasdoc($arrayAlleSha);
    }

    function CaricaAllegati($numeroProcedimento, $allegatiMail) {
        $praLibRiservato = new praLibRiservato();
        $this->praAlle = array();
        $pratPath = $this->praLib->SetDirectoryPratiche(substr($numeroProcedimento, 0, 4), $numeroProcedimento, 'PROGES');
        if ($numeroProcedimento) {
            $sqlPassi = $this->praLib->CreaSqlCaricaPassi($numeroProcedimento, true);
            $passi_view_tmp = ItaDB::DBSQLSelect($this->PRAM_DB, $sqlPassi);

            /*
             * Ordinamento passi in base agli antecedenti
             */
            $passi_view = $this->praLib->ordinaPassiConAntecedenti($passi_view_tmp);
            //
            foreach ($passi_view as $passo) {
                $pramPath = $this->praLib->SetDirectoryPratiche(substr($passo['PROPAK'], 0, 4), $passo['PROPAK'], "PASSO", false);
                $lista = $this->GetFileList($pramPath, $passo['PROPAK']);
                if ($lista) {
                    $lista['SEQUENZA'] = $passo['PROPAK'];
                    $listaAllegati[] = $lista;
                }
            }
            $this->praAlle = $this->GetTree($listaAllegati);
            //Copio il file XML
            if ($allegatiMail) {
                if (!$this->praLib->RegistraXmlInfo($allegatiMail, $this->currGesnum)) {
                    return false;
                }
            }

            /*
             * Carico Allegati delle richieste accorpate
             */
            $sql = "SELECT DISTINCT PROKPRE FROM PROPAS WHERE PRONUM = '" . $this->currGesnum . "' AND PROKPRE <> '' AND PROPUB = 1";
            $prokpreAccorpate = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
            foreach ($prokpreAccorpate as $prokpreAccorpato) {
                $passoPadreAccorpati = $this->praLib->GetPropas($prokpreAccorpato['PROKPRE'], "propak");
                //Padre Allegati Accorpati
                $chiave = count($this->praAlle) + 1;
                $arrayPadreAccorpata['SEQ'] = 'seq_ACCORPATI_' . $prokpreAccorpato['PROKPRE'];
                $arrayPadreAccorpata['NAME'] = "Allegati " . $passoPadreAccorpati['PRODPA'];
                $arrayPadreAccorpata['level'] = 0;
                $arrayPadreAccorpata['parent'] = null;
                $arrayPadreAccorpata['isLeaf'] = 'false';
                $arrayPadreAccorpata['expanded'] = 'true';
                $arrayPadreAccorpata['loaded'] = 'true';
                $this->praAlle[$chiave] = $arrayPadreAccorpata;
                //
                $sql = "SELECT * FROM PROPAS WHERE PRONUM = '" . $this->currGesnum . "' AND PROKPRE = '" . $prokpreAccorpato['PROKPRE'] . "' AND PROPUB = 1 AND PROIDR = 0";
                $passiAccorpate = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                $countAllegati = 0;
                foreach ($passiAccorpate as $passoAccorpate) {
                    $pramPath = $this->praLib->SetDirectoryPratiche(substr($passoAccorpate['PROPAK'], 0, 4), $passoAccorpate['PROPAK'], "PASSO", false);
                    $sql = "SELECT * FROM PASDOC WHERE PASKEY = '" . $passoAccorpate['PROPAK'] . "'";
                    $pasdoc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                    foreach ($pasdoc_tab as $pasdoc_rec) {
                        $countAllegati++;
                        $chiave = count($this->praAlle) + 1;
                        $ext = pathinfo($pasdoc_rec['PASFIL'], PATHINFO_EXTENSION);
                        if ($pasdoc_rec && $ext != "info") {
                            if (strtolower($ext) == "p7m") {
                                $edit = "<span class=\"ita-icon ita-icon-shield-blue-16x16\">Verifica Firma</span>";
                            } else if (strtolower($ext) == "zip") {
                                $edit = "<span class=\"ita-icon ita-icon-winzip-16x16\">estrai File Zip</span>";
                            } else {
                                $edit = " ";
                            }
                            //Valorizzo Tabella
                            $arrayDataTmp = array();
                            $arrayDataTmp['SEQ'] = $chiave;
                            $stato = $this->praLib->GetStatoAllegati($pasdoc_rec['PASSTA']);
                            if ($pasdoc_rec['PASNAME']) {
                                $arrayDataTmp['NAME'] = $pasdoc_rec['PASNAME'];
                                $arrayDataTmp['FILEORIG'] = $pasdoc_rec['PASNAME'];
                            } else {
                                $arrayDataTmp['NAME'] = $pasdoc_rec['PASFIL'];
                                $arrayDataTmp['FILEORIG'] = $pasdoc_rec['PASFIL'];
                            }
                            $arrayDataTmp['INFO'] = $pasdoc_rec['PASCLA'];
                            $arrayDataTmp['NOTE'] = $pasdoc_rec['PASNOT'];
                            $arrayDataTmp['STATO'] = $stato;
                            $arrayDataTmp['EDIT'] = $edit;
                            $arrayDataTmp['SIZE'] = $this->praLib->formatFileSize(filesize($pramPath . "/" . $pasdoc_rec['PASFIL']));
                            $arrayDataTmp['SOST'] = $this->praLib->CheckSostFile($this->currGesnum, $pasdoc_rec['PASSHA2'], $pasdoc_rec['PASSHA2SOST']);
                            $arrayDataTmp['EVIDENZIA'] = "<div class=\"ita-html\"><span class=\"ita-icon ita-icon-evidenzia-16x16 ita-colorpicker {type: 'divColor',swatches:true}\">Evidenzia Allegato</span></div>";
                            if ($pasdoc_rec['PASLOCK'] == 1) {
                                $arrayDataTmp['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Sblocca Allegato</span>";
                            } else {
                                $arrayDataTmp['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-16x16\">Blocca Allegato</span>";
                            }

                            /*
                             * Se evidenziato cambio lo sfondo altrimenti rimane trasparente
                             */
                            $color = $this->praLib->getColorNameAllegato($pasdoc_rec['PASEVI']);
                            if ($pasdoc_rec['PASEVI'] == 1) {
                                if ($pasdoc_rec['PASNAME']) {
                                    $arrayDataTmp['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $pasdoc_rec['PASNAME'] . "</p>";
                                    $arrayDataTmp['FILEORIG'] = $pasdoc_rec['PASNAME'];
                                } else {
                                    $arrayDataTmp['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $pasdoc_rec['PASFIL'] . "</p>";
                                    $arrayDataTmp['FILEORIG'] = $pasdoc_rec['PASFIL'];
                                }
                            } elseif ($pasdoc_rec['PASEVI'] != 1 && $pasdoc_rec['PASEVI'] != 0 && !empty($pasdoc_rec['PASEVI'])) {
                                //$color = '#' . str_pad(dechex($pasdoc_rec['PASEVI']), 6, "0", STR_PAD_LEFT);
                                if ($pasdoc_rec['PASNAME']) {
                                    $arrayDataTmp['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $pasdoc_rec['PASNAME'] . "</p>";
                                    $arrayDataTmp['FILEORIG'] = $pasdoc_rec['PASNAME'];
                                } else {
                                    $arrayDataTmp['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $pasdoc_rec['PASFIL'] . "</p>";
                                    $arrayDataTmp['FILEORIG'] = $pasdoc_rec['PASFIL'];
                                }
                            }

                            if ($pasdoc_rec['PASPRTROWID'] != 0 && $pasdoc_rec['PASPRTCLASS'] == "PROGES") {
                                $proges_rec = $this->praLib->GetProges($pasdoc_rec['PASPRTROWID'], "rowid");
                                if ($proges_rec['GESNPR']) {
                                    $meta = unserialize($proges_rec['GESMETA']);
                                    //$dataPrt = substr($meta['DatiProtocollazione']['Data']['value'], 8, 2) . "/" . substr($meta['DatiProtocollazione']['Data']['value'], 5, 2) . "/" . substr($meta['DatiProtocollazione']['Data']['value'], 0, 4);
                                    $dataPrt = $this->praLib->GetDataProtNormalizzata($meta);
                                    $arrayDataTmp['PROTOCOLLO'] = $proges_rec['GESNPR'] . " del <br>$dataPrt";
                                    $arrayDataTmp['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Allegato Bloccato per Protocollazione PRATICA</span>";
                                }
                            }
                            if ($pasdoc_rec['PASPRTROWID'] != 0 && $pasdoc_rec['PASPRTCLASS'] == "PRACOM") {
                                $pracom_rec = $this->praLib->GetPracom($pasdoc_rec['PASPRTROWID'], "rowid");
                                if ($pracom_rec['COMPRT']) {
                                    $dataPrtPasso = substr($pracom_rec['COMDPR'], 6, 2) . "/" . substr($pracom_rec['COMDPR'], 4, 2) . "/" . substr($pracom_rec['COMDPR'], 0, 4);
                                    $arrayDataTmp['PROTOCOLLO'] = $pracom_rec['COMPRT'] . " del <br>$dataPrtPasso";
                                    $arrayDataTmp['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Allegato Bloccato per Protocollazione PASSO</span>";
                                }
                            }

                            /*
                             * Classificazione
                             */
                            $Anacla_rec = $this->praLib->GetAnacla($pasdoc_rec['PASCLAS']);
                            $arrayDataTmp['CLASSIFICAZIONE'] = $Anacla_rec['CLADES'];

                            /*
                             * Destinazioni
                             */
                            $strDest = "";
                            $arrayDest = unserialize($pasdoc_rec['PASDEST']);
                            if (is_array($arrayDest)) {
                                foreach ($arrayDest as $dest) {
                                    $Anaddo_rec = $this->praLib->GetAnaddo($dest);
                                    $strDest .= $Anaddo_rec['DDONOM'] . "<br>";
                                }
                                $arrayDataTmp['DESTINAZIONI'] = "<div class=\"ita-html\"><span style=\"color:red;\" class=\"ita-Wordwrap ita-tooltip\" title=\"$strDest\"><b>" . count($arrayDest) . "</b></span></div>";
                            }
                            $arrayDataTmp['PASDEST'] = $pasdoc_rec['PASDEST'];
                            //$arrayDataTmp['DESTINAZIONI'] = $strDest;

                            /*
                             * Note
                             */
                            $arrayDataTmp['PASNOTE'] = $pasdoc_rec['PASNOTE'];


                            //
                            $arrayDataTmp['FILENAME'] = $pasdoc_rec['PASFIL'];
                            $arrayDataTmp['FILEINFO'] = $pasdoc_rec['PASCLA'];
                            $arrayDataTmp['FILEPATH'] = $pramPath . "/" . $pasdoc_rec['PASFIL'];
                            $arrayDataTmp['PASEVI'] = $pasdoc_rec['PASEVI'];
                            $arrayDataTmp['PASLOCK'] = $pasdoc_rec['PASLOCK'];
                            $arrayDataTmp['RISERVATO'] = $praLibRiservato->getIconRiservato($pasdoc_rec['PASRIS']);
                            $arrayDataTmp['ROWID'] = $pasdoc_rec['ROWID'];
                            $arrayDataTmp['level'] = 1;
                            $arrayDataTmp['parent'] = 'seq_ACCORPATI_' . $prokpreAccorpato['PROKPRE'];
                            $arrayDataTmp['isLeaf'] = 'true';
                            $this->praAlle[$chiave] = $arrayDataTmp;
                        }
                    }
                }
                if ($countAllegati == 0) {
                    unset($this->praAlle[$chiave]);
                }
            }

            /*
             * Carico alleagi Comunica
             */
            $sql = "SELECT * FROM PASDOC WHERE PASKEY LIKE '" . $this->currGesnum . "%' AND PASCLA LIKE '%INFOCAMERE%'";
            $allegatiComunica = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
            if ($allegatiComunica) {
                foreach ($allegatiComunica as $allGen) {
                    if (strpos(strtoupper($allGen['PASNOT']), "SUAP.XML") !== false) {
                        $fileZip = pathinfo($allGen['PASNOT'], PATHINFO_FILENAME) . ".ZIP";
                        break;
                    }
                }

                /*
                 * Padre Allegati Comunica
                 */
                $chiave = count($this->praAlle) + 1;
                $arrayPadreComunica['SEQ'] = 'seq_INF';
                $arrayPadreComunica['NAME'] = "Allegati Richiesta Comunica " . $fileZip;
                $arrayPadreComunica['level'] = 0;
                $arrayPadreComunica['parent'] = null;
                $arrayPadreComunica['isLeaf'] = 'false';
                $arrayPadreComunica['expanded'] = 'true';
                $arrayPadreComunica['loaded'] = 'true';
                $this->praAlle[$chiave] = $arrayPadreComunica;
                foreach ($allegatiComunica as $allGen) {
                    $stato = "";
                    $arrayData = array();
                    $chiave = count($this->praAlle) + 1;
                    $ext = pathinfo($allGen['PASFIL'], PATHINFO_EXTENSION);
                    if ($ext) {
                        if (strlen($allGen['PASKEY']) > 10) {
                            $Path = $this->praLib->SetDirectoryPratiche(substr($allGen['PASKEY'], 0, 4), $allGen['PASKEY'], 'PASSO', false);
                        } else {
                            $Path = $this->praLib->SetDirectoryPratiche(substr($numeroProcedimento, 0, 4), $numeroProcedimento, 'PROGES');
                        }
                        if (strtolower($ext) == "p7m") {
                            $edit = "<span class=\"ita-icon ita-icon-shield-blue-16x16\">Verifica Firma</span>";
                        } else if (strtolower($ext) == "zip") {
                            $edit = "<span class=\"ita-icon ita-icon-winzip-16x16\">estrai File Zip</span>";
                        } else {
                            $edit = " ";
                        }
                        //Valorizzo tabella
                        $arrayData['SEQ'] = $chiave;
                        $stato = $this->praLib->GetStatoAllegati($allGen['PASSTA']);
                        if ($allGen['PASNAME']) {
                            $arrayData['NAME'] = $allGen['PASNAME'];
                            $arrayData['FILEORIG'] = $allGen['PASNAME'];
                        } else {
                            $arrayData['NAME'] = $allGen['PASFIL'];
                            $arrayData['FILEORIG'] = $allGen['PASFIL'];
                        }
                        $arrayData['INFO'] = 'INFOCAMERE';
                        $arrayData['NOTE'] = $allGen['PASNOT'];
                        $arrayData['STATO'] = $stato;
                        $arrayData['EDIT'] = $edit;
                        $arrayData['SIZE'] = $this->praLib->formatFileSize(filesize($Path . "/" . $allGen['PASFIL']));
                        $arrayData['EVIDENZIA'] = "<div class=\"ita-html\"><span class=\"ita-icon ita-icon-evidenzia-16x16 ita-colorpicker {type: 'divColor',swatches:true}\">Evidenzia Allegato</span></div>";
                        if ($allGen['PASLOCK'] == 1) {
                            $arrayData['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Sblocca Allegato</span>";
                        } else {
                            $arrayData['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-16x16\">Blocca Allegato</span>";
                        }


                        /*
                         * Se evidenziato cambio lo sfondo altrimenti rimane trasparente
                         */
                        $color = $this->praLib->getColorNameAllegato($allGen['PASEVI']);
                        if ($allGen['PASEVI'] == 1) {
                            if ($allGen['PASNAME']) {
                                $arrayData['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $allGen['PASNAME'] . "</p>";
                            } else {
                                $arrayData['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $allGen['PASFIL'] . "</p>";
                            }
                        } elseif ($allGen['PASEVI'] != 1 && $allGen['PASEVI'] != 0 && !empty($allGen['PASEVI'])) {
                            //$color = '#' . str_pad(dechex($allGen['PASEVI']), 6, "0", STR_PAD_LEFT);
                            if ($allGen['PASNAME']) {
                                $arrayData['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $allGen['PASNAME'] . "</p>";
                            } else {
                                $arrayData['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $allGen['PASFIL'] . "</p>";
                            }
                        }
                        if ($allGen['PASPRTROWID'] != 0 && $allGen['PASPRTCLASS'] == "PROGES") {
                            $proges_rec = $this->praLib->GetProges($allGen['PASPRTROWID'], "rowid");
                            if ($proges_rec['GESNPR']) {
                                $meta = unserialize($proges_rec['GESMETA']);
                                //$dataPrt = substr($meta['DatiProtocollazione']['Data']['value'], 8, 2) . "/" . substr($meta['DatiProtocollazione']['Data']['value'], 5, 2) . "/" . substr($meta['DatiProtocollazione']['Data']['value'], 0, 4);
                                $dataPrt = $this->praLib->GetDataProtNormalizzata($meta);
                                $arrayData['PROTOCOLLO'] = $proges_rec['GESNPR'] . " del <br>$dataPrt";
                                $arrayData['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Allegato Bloccato per Protocollazione PRATICA</span>";
                            }
                        }
                        if ($allGen['PASPRTROWID'] != 0 && $allGen['PASPRTCLASS'] == "PRACOM") {
                            $pracom_rec = $this->praLib->GetPracom($allGen['PASPRTROWID'], "rowid");
                            if ($pracom_rec['COMPRT']) {
                                $dataPrtPasso = substr($pracom_rec['COMDPR'], 6, 2) . "/" . substr($pracom_rec['COMDPR'], 4, 2) . "/" . substr($pracom_rec['COMDPR'], 0, 4);
                                $arrayData['PROTOCOLLO'] = $pracom_rec['COMPRT'] . " del <br>$dataPrtPasso";
                                $arrayData['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Allegato Bloccato per Protocollazione PASSO</span>";
                            }
                            if ($pracom_rec['COMIDDOC']) {
                                $arrayData['PROTOCOLLO'] = $pracom_rec['COMIDDOC'] . " del <br>" . $pracom_rec['COMDATADOC'];
                                $arrayData['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Allegato Bloccato per Id Documento PASSO</span>";
                            }
                        }

                        /*
                         * Classificazione
                         */
                        $Anacla_rec = $this->praLib->GetAnacla($allGen['PASCLAS']);
                        $arrayData['CLASSIFICAZIONE'] = $Anacla_rec['CLADES'];

                        /*
                         * Destinazioni
                         */
                        $strDest = "";
                        $arrayDest = unserialize($allGen['PASDEST']);
                        if (is_array($arrayDest)) {
                            foreach ($arrayDest as $dest) {
                                $Anaddo_rec = $this->praLib->GetAnaddo($dest);
                                $strDest .= $Anaddo_rec['DDONOM'] . "<br>";
                            }
                            $arrayData['DESTINAZIONI'] = "<div class=\"ita-html\"><span style=\"color:red;\" class=\"ita-Wordwrap ita-tooltip\" title=\"$strDest\"><b>" . count($arrayDest) . "</b></span></div>";
                        }
                        $arrayData['PASDEST'] = $allGen['PASDEST'];
                        //$arrayData['DESTINAZIONI'] = $strDest;

                        /*
                         * Note
                         */
                        $arrayData['PASNOTE'] = $allGen['PASNOTE'];

                        /*
                         * Valorizzo Array
                         */
                        $arrayData['FILENAME'] = $allGen['PASFIL'];
                        $arrayData['FILEINFO'] = $allGen['PASCLA'];
                        $arrayData['FILEPATH'] = $Path . "/" . $allGen['PASFIL'];
                        $arrayData['PASEVI'] = $allGen['PASEVI'];
                        $arrayData['PASLOCK'] = $allGen['PASLOCK'];
                        $arrayData['RISERVATO'] = $praLibRiservato->getIconRiservato($allGen['PASRIS']);
                        $arrayData['ROWID'] = $allGen['ROWID'];
                        $arrayData['level'] = 1;
                        $arrayData['parent'] = 'seq_INF';
                        $arrayData['isLeaf'] = 'true';
                        $this->praAlle[$chiave] = $arrayData;
                    }
                }
            }

            /*
             * Carico alleagi generali
             */
            $sql = "SELECT * FROM PASDOC WHERE PASKEY = '" . $this->currGesnum . "' AND PASCLA = 'GENERALE'";
            $allegatiGenerali = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
            if ($allegatiGenerali) {

                /*
                 * Padre Allegati Generali
                 */
                $chiave = count($this->praAlle) + 1;
                $arrayGenerali['SEQ'] = 'seq_GEN';
                $arrayGenerali['NAME'] = "Allegati Generali";
                $arrayGenerali['level'] = 0;
                $arrayGenerali['parent'] = null;
                $arrayGenerali['isLeaf'] = 'false';
                $arrayGenerali['expanded'] = 'true';
                $arrayGenerali['loaded'] = 'true';
                $this->praAlle[$chiave] = $arrayGenerali;
                foreach ($allegatiGenerali as $allGen) {
                    $edit = $stato = "";
                    $arrayData = array();
                    $chiave = count($this->praAlle) + 1;
                    $ext = pathinfo($allGen['PASFIL'], PATHINFO_EXTENSION);
                    if (strtolower($ext) == "p7m") {
                        $edit = "<span class=\"ita-icon ita-icon-shield-blue-16x16\">Verifica Firma</span>";
                    } else if (strtolower($ext) == "zip") {
                        $edit = "<span class=\"ita-icon ita-icon-winzip-16x16\">estrai File Zip</span>";
                    } else {
                        $edit = " ";
                    }

                    if ($ext) {
                        /*
                         * Valorizzo tabella
                         */
                        $arrayData['SEQ'] = $chiave;
                        $stato = $this->praLib->GetStatoAllegati($allGen['PASSTA']);
                        if ($allGen['PASNAME']) {
                            $arrayData['NAME'] = $allGen['PASNAME'];
                            $arrayData['FILEORIG'] = $allGen['PASNAME'];
                        } else {
                            $arrayData['NAME'] = $allGen['PASFIL'];
                            $arrayData['FILEORIG'] = $allGen['PASFIL'];
                        }
                        $arrayData['INFO'] = 'GENERALE';
                        $arrayData['NOTE'] = $allGen['PASNOT'];
                        $arrayData['EDIT'] = $edit;
                        $arrayData['STATO'] = $stato;
                        $arrayData['EDIT'] = $edit;
                        $arrayData['SIZE'] = $this->praLib->formatFileSize(filesize($pratPath . "/" . $allGen['PASFIL']));
                        $arrayData['EVIDENZIA'] = "<div class=\"ita-html\"><span class=\"ita-icon ita-icon-evidenzia-16x16 ita-colorpicker {type: 'divColor',swatches:true}\">Evidenzia Allegato</span></div>";
                        if ($allGen['PASLOCK'] == 1) {
                            $arrayData['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Sblocca Allegato</span>";
                        } else {
                            $arrayData['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-16x16\">Blocca Allegato</span>";
                        }


                        /*
                         * Se evidenziato cambio lo sfondo altrimenti rimane trasparente
                         */
                        $color = $this->praLib->getColorNameAllegato($allGen['PASEVI']);
                        if ($allGen['PASEVI'] == 1) {
                            if ($allGen['PASNAME']) {
                                $arrayData['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $allGen['PASNAME'] . "</p>";
                            } else {
                                $arrayData['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $allGen['PASFIL'] . "</p>";
                            }
                        } elseif ($allGen['PASEVI'] != 1 && $allGen['PASEVI'] != 0 && !empty($allGen['PASEVI'])) {
                            //$color = '#' . str_pad(dechex($allGen['PASEVI']), 6, "0", STR_PAD_LEFT);
                            if ($allGen['PASNAME']) {
                                $arrayData['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $allGen['PASNAME'] . "</p>";
                            } else {
                                $arrayData['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $allGen['PASFIL'] . "</p>";
                            }
                        }
                        if ($allGen['PASPRTROWID'] != 0 && $allGen['PASPRTCLASS'] == "PROGES") {
                            $proges_rec = $this->praLib->GetProges($allGen['PASPRTROWID'], "rowid");
                            if ($proges_rec['GESNPR']) {
                                $meta = unserialize($proges_rec['GESMETA']);
                                //$dataPrt = substr($meta['DatiProtocollazione']['Data']['value'], 8, 2) . "/" . substr($meta['DatiProtocollazione']['Data']['value'], 5, 2) . "/" . substr($meta['DatiProtocollazione']['Data']['value'], 0, 4);
                                $dataPrt = $this->praLib->GetDataProtNormalizzata($meta);
                                $arrayData['PROTOCOLLO'] = $proges_rec['GESNPR'] . " del <br>$dataPrt";
                                $arrayData['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Allegato Bloccato per Protocollazione PRATICA</span>";
                            }
                        }
                        if ($allGen['PASPRTROWID'] != 0 && $allGen['PASPRTCLASS'] == "PRACOM") {
                            $pracom_rec = $this->praLib->GetPracom($allGen['PASPRTROWID'], "rowid");
                            if ($pracom_rec['COMPRT']) {
                                $dataPrtPasso = substr($pracom_rec['COMDPR'], 6, 2) . "/" . substr($pracom_rec['COMDPR'], 4, 2) . "/" . substr($pracom_rec['COMDPR'], 0, 4);
                                $arrayData['PROTOCOLLO'] = $pracom_rec['COMPRT'] . " del <br>$dataPrtPasso";
                                $arrayData['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Allegato Bloccato per Protocollazione PASSO</span>";
                            }
                        }

                        /*
                         * Classificazione
                         */
                        $Anacla_rec = $this->praLib->GetAnacla($allGen['PASCLAS']);
                        $arrayData['CLASSIFICAZIONE'] = $Anacla_rec['CLADES'];

                        /*
                         * Destinazioni
                         */
                        $strDest = "";
                        $arrayDest = unserialize($allGen['PASDEST']);
                        if (is_array($arrayDest)) {
                            foreach ($arrayDest as $dest) {
                                $Anaddo_rec = $this->praLib->GetAnaddo($dest);
                                $strDest .= $Anaddo_rec['DDONOM'] . "<br>";
                            }
                            $arrayData['DESTINAZIONI'] = "<div class=\"ita-html\"><span style=\"color:red;\" class=\"ita-Wordwrap ita-tooltip\" title=\"$strDest\"><b>" . count($arrayDest) . "</b></span></div>";
                        }
                        $arrayData['PASDEST'] = $allGen['PASDEST'];
                        //$arrayData['DESTINAZIONI'] = $strDest;

                        /*
                         * Note
                         */
                        $arrayData['PASNOTE'] = $allGen['PASNOTE'];

                        /*
                         * Valorizzo Array
                         */
                        $arrayData['FILENAME'] = $allGen['PASFIL'];
                        $arrayData['FILEORIG'] = $allGen['PASNAME'];
                        $arrayData['FILEINFO'] = $allGen['PASCLA'];
                        $arrayData['FILEPATH'] = $pratPath . "/" . $allGen['PASFIL'];
                        $arrayData['PASEVI'] = $allGen['PASEVI'];
                        $arrayData['PASLOCK'] = $allGen['PASLOCK'];
                        $arrayData['RISERVATO'] = $praLibRiservato->getIconRiservato($allGen['PASRIS']);
                        $arrayData['ROWID'] = $allGen['ROWID'];
                        $arrayData['level'] = 1;
                        $arrayData['parent'] = 'seq_GEN';
                        $arrayData['isLeaf'] = 'true';
                        $this->praAlle[$chiave] = $arrayData;
                    }
                }
            }
        }
        return true;
    }

    function caricaComunicazioni() {
        $arrayData = array();
        $inc = 0;
        $Propas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PROPUB=0  AND PRONUM = '$this->currGesnum' AND PROKPRE='' ORDER BY PROSEQ", true);
        foreach ($Propas_tab as $Propas_rec) {
            $Pracom_rec_partenza = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMTIP = 'P' AND COMPAK='" . $Propas_rec['PROPAK'] . "'", false);
            $Pracom_rec_arrivo = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMTIP = 'A' AND COMPAK='" . $Propas_rec['PROPAK'] . "'", false);

            if ($Pracom_rec_arrivo || $Pracom_rec_partenza) {

                /*
                 * Padre (Passo) Se c'è almeno un Arrivo o Partenza
                 */
                $arrayDataTmp = array();
                $arrayDataTmp['SEQ'] = $Propas_rec['PROPAK'];
                $arrayDataTmp['DESTINATARIO'] = "<span style=\"color: darkred;font-size: 1.2em;\"><b>Comunicazioni Passo " . $Propas_rec['PROSEQ'] . ": " . $Propas_rec['PRODPA'] . "</b></span>";
                $arrayDataTmp['DATA'] = "";
                $arrayDataTmp['EMAIL'] = "";
                $arrayDataTmp['level'] = 0;
                $arrayDataTmp['parent'] = "";
                $arrayDataTmp['isLeaf'] = 'false';
                $arrayDataTmp['expanded'] = 'true';
                $arrayDataTmp['loaded'] = 'true';
                $arrayData[] = $arrayDataTmp;
            }
            if ($Pracom_rec_arrivo && $Pracom_rec_partenza) {
                /*
                 * Comunicazione in partenza
                 */
                $arrayDataTmp['SEQ'] = $Pracom_rec_partenza['ROWID'];
                $arrayDataTmp['DESTINATARIO'] = "<span style=\"font-size:1.1em;color:red;\"><b>PARTENZA</b></span>";
                $pramitdest_tab = $this->praLib->GetPraDestinatari($Pracom_rec_partenza['COMPAK'], "codice", true);
                foreach ($pramitdest_tab as $pramitdest_rec) {
                    $arrayDataTmp['MITTDEST'] .= $pramitdest_rec['NOME'] . " " . $pramitdest_rec['COGNOME'] . "<br>";
                    $arrayDataTmp['DATA'] .= substr($pramitdest_rec['DATAINVIO'], 6, 2) . "/" . substr($pramitdest_rec['DATAINVIO'], 4, 2) . "/" . substr($pramitdest_rec['DATAINVIO'], 0, 4) . "<br>";
                    $arrayDataTmp['EMAIL'] .= $pramitdest_rec['MAIL'] . "<br>";
                }
                $arrayDataTmp['NOTE'] = $Pracom_rec_partenza['COMNOT'];
                $arrayDataTmp['level'] = 1;
                $arrayDataTmp['parent'] = $Propas_rec['PROPAK'];
                $arrayDataTmp['isLeaf'] = 'true';
                $arrayData[] = $arrayDataTmp;
                $inc += 1;

                /*
                 * Comunicazione in arrivo
                 */
                $arrayDataTmp['SEQ'] = $Pracom_rec_arrivo['ROWID'];
                $arrayDataTmp['DESTINATARIO'] = "<span style=\"font-size:1.0em;color:red;\"><b>ARRIVO</b></span>";
                $arrayDataTmp['MITTDEST'] = $Pracom_rec_arrivo['COMNOM'];
                $arrayDataTmp['DATA'] = $Pracom_rec_arrivo['COMDAT'];
                $arrayDataTmp['EMAIL'] = $Pracom_rec_arrivo['COMMLD'];
                $arrayDataTmp['NOTE'] = $Pracom_rec_arrivo['COMNOT'];
                $arrayDataTmp['level'] = 1;
                $arrayDataTmp['parent'] = $Propas_rec['PROPAK'];
                $arrayDataTmp['isLeaf'] = 'true';
                $arrayData[] = $arrayDataTmp;
                $inc += 1;

                $propas_tab_collegati = $this->praLib->GetPropas($Propas_rec['PROPAK'], "prokpre", true);
                foreach ($propas_tab_collegati as $propas_rec_collegati) {
                    $Pracom_rec_arrivo_coll = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMTIP = 'A' AND COMPAK='" . $propas_rec_collegati['PROPAK'] . "'", false);
                    $arrayDataTmp['SEQ'] = $Pracom_rec_arrivo_coll['ROWID'];
                    $arrayDataTmp['DESTINATARIO'] = "<span style=\"font-size:1.0em;color:red;\"><b>ARRIVO (Passo " . $propas_rec_collegati['PROSEQ'] . ": " . $propas_rec_collegati['PRODPA'] . ")</b></span>";
                    $arrayDataTmp['MITTDEST'] = $Pracom_rec_arrivo_coll['COMNOM'];
                    $arrayDataTmp['DATA'] = $Pracom_rec_arrivo_coll['COMDAT'];
                    $arrayDataTmp['EMAIL'] = $Pracom_rec_arrivo_coll['COMMLD'];
                    $arrayDataTmp['NOTE'] = $Pracom_rec_arrivo_coll['COMNOT'];
                    $arrayDataTmp['level'] = 1;
                    $arrayDataTmp['parent'] = $Propas_rec['PROPAK'];
                    $arrayDataTmp['isLeaf'] = 'true';
                    $arrayData[] = $arrayDataTmp;
                    $inc += 1;
                }
            } elseif (!$Pracom_rec_arrivo && $Pracom_rec_partenza) {
                /*
                 * Figlio (Comunicazione in Partenza)
                 */
                $arrayDataTmp['SEQ'] = $Pracom_rec_partenza['ROWID'];
                $arrayDataTmp['DESTINATARIO'] = "<span style=\"font-size:1.1em;color:red;\"><b>PARTENZA</b></span>";
                $pramitdest_tab = $this->praLib->GetPraDestinatari($Pracom_rec_partenza['COMPAK'], "codice", true);
                foreach ($pramitdest_tab as $pramitdest_rec) {
                    $arrayDataTmp['MITTDEST'] .= $pramitdest_rec['NOME'] . " " . $pramitdest_rec['COGNOME'] . "<br>";
                    $arrayDataTmp['DATA'] .= substr($pramitdest_rec['DATAINVIO'], 6, 2) . "/" . substr($pramitdest_rec['DATAINVIO'], 4, 2) . "/" . substr($pramitdest_rec['DATAINVIO'], 0, 4) . "<br>";
                    $arrayDataTmp['EMAIL'] .= $pramitdest_rec['MAIL'] . "<br>";
                }
                $arrayDataTmp['NOTE'] = $Pracom_rec_partenza['COMNOT'];
                $arrayDataTmp['level'] = 1;
                $arrayDataTmp['parent'] = $Propas_rec['PROPAK'];
                $arrayDataTmp['isLeaf'] = 'true';
                $arrayData[] = $arrayDataTmp;
                $inc += 1;
            } elseif ($Pracom_rec_arrivo && !$Pracom_rec_partenza) {
                /*
                 * Figlio (Comunicazione in Arrivo)
                 */
                $arrayDataTmp['SEQ'] = $Pracom_rec_arrivo['ROWID'];
                $arrayDataTmp['DESTINATARIO'] = "<span style=\"font-size:1.1em;color:red;\"><b>ARRIVO</b></span>";
                $arrayDataTmp['MITTDEST'] = $Pracom_rec_arrivo['COMNOM'];
                $arrayDataTmp['DATA'] = $Pracom_rec_arrivo['COMDAT'];
                $arrayDataTmp['EMAIL'] = $Pracom_rec_arrivo['COMMLD'];
                $arrayDataTmp['NOTE'] = $Pracom_rec_arrivo['COMNOT'];
                $arrayDataTmp['level'] = 1;
                $arrayDataTmp['parent'] = $Propas_rec['PROPAK'];
                $arrayDataTmp['isLeaf'] = 'true';
                $arrayData[] = $arrayDataTmp;
                $inc += 1;

                $propas_tab_collegati = $this->praLib->GetPropas($Propas_rec['PROPAK'], "prokpre", true);
                foreach ($propas_tab_collegati as $propas_rec_collegati) {
                    $Pracom_rec_arrivo_coll = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMTIP = 'A' AND COMPAK='" . $propas_rec_collegati['PROPAK'] . "'", false);
                    $arrayDataTmp['SEQ'] = $Pracom_rec_arrivo_coll['ROWID'];
                    $arrayDataTmp['DESTINATARIO'] = "<span style=\"font-size:1.0em;color:red;\"><b>ARRIVO (Passo " . $propas_rec_collegati['PROSEQ'] . ": " . $propas_rec_collegati['PRODPA'] . ")</b></span>";
                    $arrayDataTmp['MITTDEST'] = $Pracom_rec_arrivo_coll['COMNOM'];
                    $arrayDataTmp['DATA'] = $Pracom_rec_arrivo_coll['COMDAT'];
                    $arrayDataTmp['EMAIL'] = $Pracom_rec_arrivo_coll['COMMLD'];
                    $arrayDataTmp['NOTE'] = $Pracom_rec_arrivo_coll['COMNOT'];
                    $arrayDataTmp['level'] = 1;
                    $arrayDataTmp['parent'] = $Propas_rec['PROPAK'];
                    $arrayDataTmp['isLeaf'] = 'true';
                    $arrayData[] = $arrayDataTmp;
                    $inc += 1;
                }
            }
        }
        $proges_rec = $this->praLib->GetProges($this->currGesnum);
        if (!$this->praPerms->checkSuperUser($proges_rec)) {
            $comunicazioniFiltrate = $this->praPerms->filtraComunicazioniView($this->praPassi, $arrayData);
        } else {
            $comunicazioniFiltrate = $arrayData;
        }

        App::log($comunicazioniFiltrate);
        $this->praComunicazioni = $comunicazioniFiltrate;
        $this->CaricaGriglia($this->gridComunicazioni, $this->praComunicazioni);
    }

    function CheckExpandedPasso($allPasso, $allGenerali = false) {
        foreach ($allPasso as $alle) {
            $nomeAlleagto = $alle['FILENAME'];
            if ($allGenerali)
                $nomeAlleagto = $alle['PASFIL'];
            $open = false;
            $pasdoc_rec_open = $this->praLib->GetPasdoc($nomeAlleagto, "pasfil");
            if ($pasdoc_rec_open['PASEVI'] == 1) {
                $open = true;
                break;
            }
        }
        return $open;
    }

    function GetTree($listaAllegati) {
        $praLibRiservato = new praLibRiservato();
        $arrayData = array();
        $inc = 0;
        $nodoFO = false;

        /*
         * Doppio Giro. Prima gli allegati FO poi gli BO
         */
        $flBO = 0;
        for ($flBO = 0; $flBO <= 1; $flBO++) {
            $listaAllegati = $this->praLib->array_sort($listaAllegati, "SEQUENZA");
            foreach ($listaAllegati as $allPasso) {
                $sql = "SELECT PRONUM,PROSEQ,PRODPA,PROPUB,PROVISIBILITA,PROPAK,PROUTEADD,PROKPRE,PRORICUNI,PRORIN,PROIDR,PRODRR,PROUPL,PROMLT FROM PROPAS WHERE PROPAK = '" . $allPasso['SEQUENZA'] . "'";
                $propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                $pramPath = $this->praLib->SetDirectoryPratiche(substr($propas_rec['PROPAK'], 0, 4), $propas_rec['PROPAK'], "PASSO", false);

                /*
                 * se incontro allegati BO continuo
                 */
                if ($flBO == 1 && $propas_rec['PROPUB'] == 1) {
                    continue;
                }

                /*
                 * se incontro allegati FO continuo
                 */
                if ($flBO == 0 && $propas_rec['PROPUB'] == 0) {
                    continue;
                }

                /*
                 * Setto il legame tra il padre e il figlio nella tree grid
                 */
                if ($propas_rec['PROPUB'] == 1) {
                    $seq = "FO";
                } else {
                    $seq = $propas_rec['PROPAK'];
                }

                if (($propas_rec['PROPUB'] == 1 && $nodoFO == false) || $propas_rec['PROPUB'] == 0) {
                    $arrayDataTmp = array();
                    if ($propas_rec['PROPUB'] == 1) {
                        $nodoFO = true;
                        $arrayDataTmp['NAME'] = "Allegati Procedimento on-line";
                    } else {
                        $arrayDataTmp['NAME'] = "Allegati Passo " . $propas_rec['PROSEQ'] . ": " . $propas_rec['PRODPA'];
                    }
                    $arrayDataTmp['SEQ'] = $seq;
                    $arrayDataTmp['NOTE'] = "";
                    $arrayDataTmp['STATO'] = "";
                    $arrayDataTmp['level'] = 0;
                    $arrayDataTmp['parent'] = "";
                    $arrayDataTmp['isLeaf'] = 'false';
                    $arrayDataTmp['expanded'] = 'true';
                    $arrayDataTmp['loaded'] = 'true';
                    $arrayData[] = $arrayDataTmp;
                    $inc += 1;
                }

                foreach ($allPasso as $allegato) {
                    if ($propas_rec['PROKPRE'] && $propas_rec['PROPUB'] == 1) {
                        continue;
                    }

                    $edit = $stato = $strDest = "";

                    /*
                     * Escludo quelli di Infocamere perchè in estraggo Successivamente
                     */
                    $sql = "SELECT * FROM PASDOC WHERE PASKEY = '" . $allegato['SEQUENZA'] . "' AND PASFIL = '" . $allegato['FILENAME'] . "' AND PASCLA NOT LIKE '%INFOCAMERE%'";
                    $pasdoc_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                    if (!$pasdoc_rec) {
                        continue;
                    }
                    if ($propas_rec['PRORICUNI'] == 1 && $pasdoc_rec['PASCLAS'] != "AUTOCERTIFICAZIONE_ACCORPATA") {
                        continue;
                    }
                    $ext = pathinfo($pasdoc_rec['PASFIL'], PATHINFO_EXTENSION);
                    //if ($propas_rec['PROPUB'] == 0 || ($propas_rec['PROPUB'] == 1 && strtolower($ext) == "p7m") || ($propas_rec['PROPUB'] == 1 && $propas_rec['PROIDR'] == 0 && ($propas_rec['PROUPL'] == 1 || $propas_rec['PROMLT'] == 1) || $propas_rec['PRODAT'] == 1)) {
                    if ($propas_rec['PROPUB'] == 0 || ($propas_rec['PROPUB'] == 1 && strtolower($ext) == "p7m") || ($propas_rec['PROPUB'] == 1 && $propas_rec['PROIDR'] == 0 && ($propas_rec['PROUPL'] == 1 || $propas_rec['PROMLT'] == 1) || $propas_rec['PRODAT'] == 1 || $propas_rec['PRODRR'] == 1)) {
                        //if ($propas_rec['PROIDR'] == 0) {
                        if (strtolower($ext) == "p7m") {
                            $edit = "<span class=\"ita-icon ita-icon-shield-blue-16x16\">Verifica Firma</span>";
                        } else if (strtolower($ext) == "zip") {
                            $edit = "<span class=\"ita-icon ita-icon-winzip-16x16\">estrai File Zip</span>";
                        } else {
                            $edit = " ";
                            if ($pasdoc_rec['PASCLA'] == "TESTOBASE") {
                                if (file_exists($pramPath . "/" . pathinfo($allegato['FILEPATH'], PATHINFO_FILENAME) . ".pdf.p7m")) {
                                    $edit = "<span class=\"ita-icon ita-icon-shield-blue-16x16\">Verifica Firma</span>";
                                }
                            }
                        }

                        $arrayDataTmp = array();
                        $arrayDataTmp['SEQ'] = $inc;
                        $stato = $this->praLib->GetStatoAllegati($pasdoc_rec['PASSTA']);
                        if ($pasdoc_rec['PASNAME']) {
                            $arrayDataTmp['NAME'] = $pasdoc_rec['PASNAME'];
                            $arrayDataTmp['FILEORIG'] = $pasdoc_rec['PASNAME'];
                        } else {
                            $arrayDataTmp['NAME'] = $pasdoc_rec['PASFIL'];
                            $arrayDataTmp['FILEORIG'] = $pasdoc_rec['PASFIL'];
                        }
                        $arrayDataTmp['INFO'] = $pasdoc_rec['PASCLA'];
                        $arrayDataTmp['NOTE'] = $pasdoc_rec['PASNOT'];
                        $arrayDataTmp['STATO'] = $stato;
                        $arrayDataTmp['EDIT'] = $edit;
                        $arrayDataTmp['SIZE'] = $this->praLib->formatFileSize(filesize($pramPath . "/" . $pasdoc_rec['PASFIL']));
                        $arrayDataTmp['SOST'] = $this->praLib->CheckSostFile($this->currGesnum, $pasdoc_rec['PASSHA2'], $pasdoc_rec['PASSHA2SOST']);
                        $arrayDataTmp['EVIDENZIA'] = "<div class=\"ita-html\"><span onclick=\"$('#{$allegato['ROWID']}_COLORPICKER').colorpicker('open');\" class=\"ita-icon ita-icon-evidenzia-16x16 ita-colorpicker {type: 'divColor',swatches:true}\">Evidenzia Allegato</span></div>";
                        if ($pasdoc_rec['PASLOCK'] == 1) {
                            $arrayDataTmp['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Sblocca Allegato</span>";
                        } else {
                            $arrayDataTmp['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-16x16\">Blocca Allegato</span>";
                        }

                        /*
                         * Se evidenziato cambio lo sfondo altrimenti rimane trasparente
                         */
                        $color = $this->praLib->getColorNameAllegato($pasdoc_rec['PASEVI']);
                        if ($pasdoc_rec['PASEVI'] == 1) {
                            if ($pasdoc_rec['PASNAME']) {
                                $arrayDataTmp['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $pasdoc_rec['PASNAME'] . "</p>";
                                $arrayDataTmp['FILEORIG'] = $pasdoc_rec['PASNAME'];
                            } else {
                                $arrayDataTmp['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $pasdoc_rec['PASFIL'] . "</p>";
                                $arrayDataTmp['FILEORIG'] = $pasdoc_rec['PASFIL'];
                            }
                        } elseif ($pasdoc_rec['PASEVI'] != 1 && $pasdoc_rec['PASEVI'] != 0 && !empty($pasdoc_rec['PASEVI'])) {
                            //$color = '#' . str_pad(dechex($pasdoc_rec['PASEVI']), 6, "0", STR_PAD_LEFT);
                            if ($pasdoc_rec['PASNAME']) {
                                $arrayDataTmp['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $pasdoc_rec['PASNAME'] . "</p>";
                                $arrayDataTmp['FILEORIG'] = $pasdoc_rec['PASNAME'];
                            } else {
                                $arrayDataTmp['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $pasdoc_rec['PASFIL'] . "</p>";
                                $arrayDataTmp['FILEORIG'] = $pasdoc_rec['PASFIL'];
                            }
                        }

                        if ($pasdoc_rec['PASPRTROWID'] != 0 && $pasdoc_rec['PASPRTCLASS'] == "PROGES") {
                            $proges_rec = $this->praLib->GetProges($pasdoc_rec['PASPRTROWID'], "rowid");
                            if ($proges_rec['GESNPR']) {
                                $meta = unserialize($proges_rec['GESMETA']);
                                //$dataPrt = substr($meta['DatiProtocollazione']['Data']['value'], 8, 2) . "/" . substr($meta['DatiProtocollazione']['Data']['value'], 5, 2) . "/" . substr($meta['DatiProtocollazione']['Data']['value'], 0, 4);
                                $dataPrt = $this->praLib->GetDataProtNormalizzata($meta);
                                $arrayDataTmp['PROTOCOLLO'] = $proges_rec['GESNPR'] . " del <br>$dataPrt";
                                $arrayDataTmp['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Allegato Bloccato per Protocollazione PRATICA</span>";
                            }
                        }
                        if ($pasdoc_rec['PASPRTROWID'] != 0 && $pasdoc_rec['PASPRTCLASS'] == "PRACOM") {
                            $pracom_rec = $this->praLib->GetPracom($pasdoc_rec['PASPRTROWID'], "rowid");
                            if ($pracom_rec['COMPRT']) {
                                $dataPrtPasso = substr($pracom_rec['COMDPR'], 6, 2) . "/" . substr($pracom_rec['COMDPR'], 4, 2) . "/" . substr($pracom_rec['COMDPR'], 0, 4);
                                $arrayDataTmp['PROTOCOLLO'] = $pracom_rec['COMPRT'] . " del <br>$dataPrtPasso";
                                $arrayDataTmp['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Allegato Bloccato per Protocollazione PASSO</span>";
                            }
                        }

                        /*
                         * Classificazione
                         */
                        $Anacla_rec = $this->praLib->GetAnacla($pasdoc_rec['PASCLAS']);
                        $arrayDataTmp['CLASSIFICAZIONE'] = $Anacla_rec['CLADES'];

                        /*
                         * Destinazioni
                         */
                        $arrayDest = unserialize($pasdoc_rec['PASDEST']);
                        if (is_array($arrayDest)) {
                            foreach ($arrayDest as $dest) {
                                $Anaddo_rec = $this->praLib->GetAnaddo($dest);
                                $strDest .= $Anaddo_rec['DDONOM'] . "<br>";
                            }
                            $arrayDataTmp['DESTINAZIONI'] = "<div class=\"ita-html\"><span style=\"color:red;\" class=\"ita-Wordwrap ita-tooltip\" title=\"$strDest\"><b>" . count($arrayDest) . "</b></span></div>";
                        }
                        $arrayDataTmp['PASDEST'] = $pasdoc_rec['PASDEST'];

                        /*
                         * Note
                         */
                        $arrayDataTmp['PASNOTE'] = $pasdoc_rec['PASNOTE'];

                        $arrayDataTmp['FILENAME'] = $pasdoc_rec['PASFIL'];
                        $arrayDataTmp['FILEINFO'] = $pasdoc_rec['PASCLA'];
                        $arrayDataTmp['FILEPATH'] = $allegato['FILEPATH'];
                        $arrayDataTmp['PASEVI'] = $pasdoc_rec['PASEVI'];
                        $arrayDataTmp['PASLOCK'] = $pasdoc_rec['PASLOCK'];
                        $arrayDataTmp['RISERVATO'] = $praLibRiservato->getIconRiservato($pasdoc_rec['PASRIS']);
                        $arrayDataTmp['ROWID'] = $pasdoc_rec['ROWID'];
                        $arrayDataTmp['level'] = 1;
                        $arrayDataTmp['parent'] = $seq;
                        $arrayDataTmp['isLeaf'] = 'true';
                        $arrayData[] = $arrayDataTmp;
                        $inc += 1;
                    }
                }
            }
        }

        $proges_rec = $this->praLib->GetProges($this->currGesnum);
        if (!$this->praPerms->checkSuperUser($proges_rec)) {
            return $this->praPerms->filtraAllegatiView($this->praPassi, $arrayData);
        } else {
            return $arrayData;
        }
    }

    function GetFileList($filePath, $keyPasso) {
        if (!$dh = @opendir($filePath))
            return false;
        $retListGen = array();
        $rowid = 0;
//while (($obj = @readdir($dh))) {
        while (false !== ($obj = @readdir($dh))) {
            if ($obj == '.' || $obj == '..')
                continue;
            $rowid += 1;
            $retListGen[$rowid] = array(
                'rowid' => $rowid,
                'FILEPATH' => $filePath . '/' . $obj,
                'SEQUENZA' => $keyPasso,
                'FILENAME' => $obj,
                'FILEINFO' => $obj
            );
        }
        closedir($dh);
        return $retListGen;
    }

    function RegistraDatiAggiuntiviPratica($procedimento) {
        $proges_rec = $this->praLib->GetProges($procedimento);
        foreach ($this->praDatiPratica as $dato) {
            if ($dato['ROWID'] == 0) {
                $prodag_rec = array();
                $prodag_rec['DAGNUM'] = $procedimento;
                $prodag_rec['DAGPAK'] = $procedimento;
                $prodag_rec['DAGCOD'] = $proges_rec['GESPRO'];
                $prodag_rec['DAGDES'] = $dato['DAGDES'];
                $prodag_rec['DAGSEQ'] = $dato['DAGSEQ'];
                $prodag_rec['DAGKEY'] = $dato['DAGKEY'];
                $prodag_rec['DAGVAL'] = $dato['DAGVAL'];
                $prodag_rec['DAGTIP'] = $dato['DAGTIP'];
                $insert_Info = "Oggetto: Inserisco il dato aggiuntivo " . $dato['DAGKEY'] . " della pratica n. $procedimento";
                if (!$this->insertRecord($this->PRAM_DB, 'PRODAG', $prodag_rec, $insert_Info)) {
                    Out::msgStop("ATTENZIONE!", "Errore Inserimento dato aggiuntivo " . $dato['DAGKEY']);
                    return false;
                }
            } else {
                $prodag_rec = $this->praLib->GetProdag($dato['ROWID'], "rowid");
                $prodag_rec['DAGVAL'] = $dato['DAGVAL'];
                $prodag_rec['DAGSEQ'] = $dato['DAGSEQ'];
                $update_Info = "Oggetto: Aggiorno il dato aggiuntivo " . $dato['DAGKEY'] . " della pratica n. $procedimento";
                if (!$this->updateRecord($this->PRAM_DB, 'PRODAG', $prodag_rec, $update_Info)) {
                    Out::msgStop("ATTENZIONE!", "Errore aggiornamento dato aggiuntivo " . $dato['DAGKEY']);
                    return false;
                }
            }
        }
        return true;
    }

    function RegistraAllegati($procedimento) {
        $praPath = $this->praLib->SetDirectoryPratiche(substr($procedimento, 0, 4), $procedimento, 'PROGES');
        if (!$praPath) {
            Out::msgStop("Archiviazione File", "Errore nella cartella di destinazione.");
            return false;
        }
        if ($this->sha2View == true) {
            $arrayClean = $this->praAlleSha2;
        } else {
            $arrayClean = $this->praLib->cleanArrayTree($this->praAlle);
        }
        foreach ($arrayClean as $allegato) {
            if ($allegato['ROWID'] == 0) {
                if (!@rename($allegato['FILEPATH'], $praPath . "/" . $allegato['FILENAME'])) {
                    Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $allegato['FILENAME'] . " !");
                    return false;
                }
                $pasdoc_rec = array();
                $pasdoc_rec['PASKEY'] = $this->currGesnum;
                $pasdoc_rec['PASFIL'] = $allegato['FILENAME'];
                $pasdoc_rec['PASLNK'] = "allegato://" . $allegato['FILENAME'];
                $pasdoc_rec['PASUTC'] = "";
                $pasdoc_rec['PASUTE'] = "";
                $pasdoc_rec['PASNOT'] = $allegato['NOTE'];
                $pasdoc_rec['PASCLA'] = $allegato['INFO'];
                $pasdoc_rec['PASNAME'] = $allegato['FILEORIG'];
                $pasdoc_rec['PASEVI'] = $allegato['PASEVI'];
                $pasdoc_rec['PASLOCK'] = $allegato['PASLOCK'];
                $pasdoc_rec['PASUTELOG'] = App::$utente->getKey('nomeUtente');
                $pasdoc_rec['PASORADOC'] = date("H:i:s");
                $pasdoc_rec['PASDATADOC'] = date("Ymd");
                $pasdoc_rec["PASSHA2"] = hash_file('sha256', $praPath . "/" . $pasdoc_rec["PASFIL"]);
                $pasdoc_rec["PASDEST"] = $allegato["PASDEST"];
                $insert_Info = "Oggetto: Inserisco l'allegato " . $pasdoc_rec['PASNOT'] . " della pratica " . $pasdoc_rec['PASKEY'];
                if (!$this->insertRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $insert_Info)) {
                    Out::msgStop("ATTENZIONE!", "Errore Inserimento Allegato " . $pasdoc_rec['PASNOT']);
                    return false;
                }
            } else {
                
            }
        }
        return true;
    }

    private function CaricaNote() {
        $datiGrigliaNote = array();
        $Note = $this->noteManager->getNote();
        foreach ($Note as $key => $nota) {
            $data = date("d/m/Y", strtotime($nota['DATAINS']));
            if ($nota['CLASSE'] == 'PROPAS') {
                $Passo = $this->praLib->GetPropas($nota['ROWIDCLASSE'], 'rowid');
                $Fonte = '  Passo: ' . $Passo['PROSEQ'] . " - " . $Passo['PRODPA'];
            } else {
                $Fonte = '  Fascicolo';
            }
            $datiGrigliaNote[$key]['NOTE'] = '<div style="margin: 3px 3px 3px 3px;"><div style="font-size: 11px;font-weight:bold; color: grey;">' . $data . " - " . $nota['ORAINS'] . " da " . $nota['UTELOG'] . $Fonte .
                    "</div><div class='ita-Wordwrap'><b>" . $nota['OGGETTO'] . '</b>';
            if ($nota['TESTO']) {
                $testo = $nota['TESTO'];
                if (strlen($testo) > 45) {
                    $testo = substr($testo, 0, 45);
                }
                $datiGrigliaNote[$key]['NOTE'] .= '<div title="' . $nota['TESTO'] . '" style="font-size: 13px;">' . $testo . '</div>';
            }
            $datiGrigliaNote[$key]['NOTE'] .= '</div></div>';
        }
        $this->caricaGriglia($this->gridNote, $datiGrigliaNote);
    }

    function registraIter($proges_rec, $desCod, $readOnly = false) {
        $arcite_rec = array();
        $arcite_rec['ITEPRO'] = $proges_rec['GESNUM'];
        $arcite_rec['ITEPAR'] = "X";
        $arcite_rec['ITEUFF'] = "";
        $arcite_rec['ITEDAT'] = $proges_rec['GESDRE'];
        $arcite_rec['ITEDES'] = $desCod;
        $arcite_rec['ITEANT'] = '';
        $arcite_rec['ITEANN'] = 'Caricamento fascicolo Elettronico';
        $arcite_rec['ITEFIN'] = '';
        $arcite_rec['ITETER'] = '';
        $arcite_rec['ITEGES'] = ($readOnly == true) ? 0 : 1;
        if ($arcite_rec['ITEGES'] != '1') {
            $arcite_rec['ITEFIN'] = $this->workDate;
        }
        $arcite_rec['ITEKEY'] = $this->proLib->IteKeyGenerator($proges_rec['GESNUM']
                , $desCod
                , $this->workDate
                , "X");
        $arcite_rec['ITEKPR'] = "PRAM:" . $proges_rec['GESNUM'];

        $insert_Info = 'Oggetto : Inserisco protocollo della pratica ' . $arcite_rec['ITEPRO'];
        if (!$this->insertRecord($this->proLib->getPROTDB(), 'ARCITE', $arcite_rec, $insert_Info)) {
            return false;
        }
        return true;
    }

    function ContaSizeAllegati($allegati, $field) {
        if ($allegati) {
            $totSize = 0;
            foreach ($allegati as $allegato) {
                $totSize = $totSize + filesize($allegato['FILEPATH']);
            }
            if ($totSize != 0) {
                $Size = $this->praLib->formatFileSize($totSize);
                Out::valore($this->nameForm . "_$field", $Size);
            }
        }
    }

    public function aggiorna($dati) {
        $proges_rec = $dati["PROGES_REC"];
        $proges_rec['GESPRO'] = $_POST[$this->nameForm . '_PROGES']['GESPRO'];
        $procedimento = $proges_rec['GESNUM'];
        if ($_POST[$this->nameForm . '_Numero_prot'] != 0 && $_POST[$this->nameForm . '_Anno_prot'] == 0) {
            Out::msgInfo("ATTENZIONE", "Inserire l'anno per il protocollo n. " . $_POST[$this->nameForm . '_Numero_prot']);
            return false;
        }

        $proges_rec['GESNPR'] = $_POST[$this->nameForm . '_Anno_prot'] . $_POST[$this->nameForm . '_Numero_prot'];

        // DALLA DECODIFICA DELLA SERIE MI RICAVO GESNUM PER RELAZIONE GESPRE
        $proges_rec['GESPRE'] = $this->praLib->GetGesnum($_POST[$this->nameForm . '_Gespre2'], $_POST[$this->nameForm . '_Gespre1'], $_POST[$this->nameForm . '_Gespre3']);

        /*
         * Sincronizzo Giorni e data scadenza poi mi rileggo il record per aggiornare i 2 campi
         */
        $anapra_rec = $this->praLib->GetAnapra($proges_rec['GESPRO']);

        $arrayScadenza = $this->praLib->SincDataScadenza("PRATICA", $this->currGesnum, $proges_rec['GESDSC'], $anapra_rec['PRAGIO'], $proges_rec['GESGIO'], $proges_rec['GESDRE'], true);
        if (!$arrayScadenza) {
            Out::msgStop("Sincronizzazione Scadenza", "Errore Sincronizzazione Data Scadenza.");
            return false;
        }
        $proges_rec['GESDSC'] = $arrayScadenza['SCADENZA'];
        $proges_rec['GESGIO'] = $arrayScadenza['GIORNI'];
        //
        $this->RegistraAllegati($procedimento);
        $msg = $this->praImmobili->RegistraImmobili($this);
        if ($msg != true) {
            Out::msgStop("ATTENZIONE!", $msg);
            return false;
        }
        $msgSogg = $this->praSoggetti->RegistraSoggetti($this);
        if ($msgSogg != true) {
            Out::msgStop("ATTENZIONE!", $msg);
            return false;
        }
        $msgULoc = $this->praUnitaLocale->RegistraSoggetti($this);
        if ($msgULoc != true) {
            Out::msgStop("ATTENZIONE!", $msg);
            return false;
        }

        $update_Info = 'Oggetto: Aggiornamento pratica: ' . $procedimento;
        if (!$this->updateRecord($this->PRAM_DB, 'PROGES', $proges_rec, $update_Info)) {
            Out::msgStop("Errore in Aggionamento", "Aggiornamento procedimento Fallito.");
            return false;
        }

        if (!$this->praLib->sincronizzaStato($proges_rec['GESNUM'])) {
            Out::msgStop("Errore", $this->praLib->getErrMessage());
        }

        /*
         * Sincronizzo il calendario
         */
        $errMsg = $this->praLib->sincCalendar("PRATICA", $this->currGesnum, $proges_rec['GESDSC'], $_POST[$this->nameForm . "_Calendario"]);
        if ($errMsg) {
            Out::msgStop("Errore Sincronizzazione Calendario", $errMsg);
        }
        return $proges_rec['ROWID'];
    }

    public function insertRecordPropas() {
        if (!$this->insertTo == 0) {
            $sql = "SELECT * FROM PROPAS WHERE PRONUM = '" . $this->currGesnum . "' AND PROSEQ > '" . $this->insertTo . "' ORDER BY PROSEQ";
            $propas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
            if ($propas_tab) {
                foreach ($propas_tab as $propas_rec) {
                    $propas_rec['PROSEQ'] = $propas_rec['PROSEQ'] + 500;
                    $update_Info = "Oggetto: Aggiornamento passo con seq " . $propas_rec['PROSEQ'] . " e chiave: " . $propas_rec['PROPAK'];
                    if (!$this->updateRecord($this->PRAM_DB, 'PROPAS', $propas_rec, $update_Info)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function caricaDaProtocollo($datiProt) {
        $this->apriNuovo();
        Out::valore($this->nameForm . '_PROGES[GESPAR]', $datiProt['PROPAR']);
        Out::valore($this->nameForm . '_Numero_prot', $datiProt['PRONUM']);
        Out::valore($this->nameForm . '_Anno_prot', date('Y'));
        Out::valore($this->nameForm . '_PROGES[GESDRI]', date('Ymd'));
        Out::valore($this->nameForm . '_PROGES[GESORA]', date('H:i:s'));
        Out::valore($this->nameForm . '_ANADES[DESNOM]', $datiProt['PRONOM']);
        Out::valore($this->nameForm . '_ANADES[DESIND]', $datiProt['PROIND']);
        Out::valore($this->nameForm . '_ANADES[DESCAP]', $datiProt['PROCAP']);
        Out::valore($this->nameForm . '_ANADES[DESCIT]', $datiProt['PROCIT']);
        Out::valore($this->nameForm . '_ANADES[DESPRO]', $datiProt['PROPRO']);
        Out::valore($this->nameForm . '_PROGES[GESOGG]', $datiProt['OGGOGG']);
        $retid = $this->nameForm . '_PROGES[GESPRO]';
        $codice = $datiProt['PRANUM'];
        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
        $this->DecodAnapra($codice, $retid, 'codice', $_POST[$this->nameForm . '_PROGES']['GESDRE']);
    }

    private function switchIconeProtocollo($numeroProt, $Metadati) {
        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
        $tipoProt = $PARMENTE_rec['TIPOPROTOCOLLO'];
        $numeroProt = (String) $numeroProt;
        if ($tipoProt != 'Manuale' && $tipoProt) {
            if ($numeroProt != '' && $numeroProt != '0' && $Metadati) {
                Out::show($this->nameForm . "_GestioneProtocollo");
            }

            /*
             * protocollo vuoto -> tutti i campi editabili            
             */
            if ($numeroProt == '' || $numeroProt == '0') {
                Out::attributo($this->nameForm . '_Numero_prot', "readonly", '1');
                Out::attributo($this->nameForm . '_Anno_prot', "readonly", '1');
                Out::show($this->nameForm . '_Protocolla');
                Out::hide($this->nameForm . '_RimuoviProtocolla');
            } elseif (($numeroProt != '' && $numeroProt != '0' && $Metadati) || $tipoProt == "Italsoft") { //c'è il protocollo e ci sono sono i metadati -> sparisce l'icona, i campi non sono editabili
                Out::attributo($this->nameForm . '_Numero_prot', "readonly", '0');
                Out::attributo($this->nameForm . '_Anno_prot', "readonly", '0');
                Out::hide($this->nameForm . '_Protocolla');
                Out::hide($this->nameForm . '_RimuoviProtocolla');
                Out::show($this->nameForm . '_ProtocollaPartenza');
            } else { //c'è il protocollo, ma non ci sono i metadati -> sparisce icona +, campi non editabili, compare un cestino per cancellarli
                Out::attributo($this->nameForm . '_Numero_prot', "readonly", '0');
                Out::attributo($this->nameForm . '_Anno_prot', "readonly", '0');
                Out::show($this->nameForm . '_RimuoviProtocolla');
                Out::hide($this->nameForm . '_Protocolla');
            }
        } else {
            if ($numeroProt == '' || $numeroProt == '0') { //protocollo vuoto -> tutti i campi editabili
                Out::attributo($this->nameForm . '_Numero_prot', "readonly", '1');
                Out::attributo($this->nameForm . '_Anno_prot', "readonly", '1');
                Out::hide($this->nameForm . '_Protocolla'); //per l'inserimento manuale l'icona + è sempre disabilitata
                Out::hide($this->nameForm . '_RimuoviProtocolla');
                Out::show($this->nameForm . '_InviaProtocollo');
            } else {
                Out::attributo($this->nameForm . '_Numero_prot', "readonly", '0');
                Out::attributo($this->nameForm . '_Anno_prot', "readonly", '0');
                Out::show($this->nameForm . '_RimuoviProtocolla');
                Out::hide($this->nameForm . '_Protocolla');
                if (isset($Metadati['DatiProtocollazione']['IdMailRichiesta'])) {
                    Out::show($this->nameForm . '_InviaProtocollo');
                }
            }
        }

        /*
         * Controllo Finale se utente abilitato al protocollo
         */

        /*
         * Verifico se è stato attivato l'utilizzo dei profili valore parametro=1
         */
        $filent_rec = $this->praLib->GetFilent(26);
        if ($filent_rec["FILDE1"] == 1) {
            $profilo = proSoggetto::getProfileFromIdUtente();

            /*
             * Utente disabilitato da profilo (solo partenza o nega)
             */
            if ($profilo['PROT_ABILITATI'] == '2' || $profilo['PROT_ABILITATI'] == '3') {
                Out::attributo($this->nameForm . '_Numero_prot', "readonly", '0');
                Out::attributo($this->nameForm . '_Anno_prot', "readonly", '0');
                Out::attributo($this->nameForm . '_PROGES[GESPAR]', "readonly", '0');
                Out::hide($this->nameForm . '_Protocolla');
                Out::hide($this->nameForm . '_RimuoviProtocolla');
                Out::hide($this->nameForm . '_ProtocollaPartenza');
                Out::hide($this->nameForm . '_InviaProtocollo');
                //Out::hide($this->nameForm . "_BloccaAllegatiPratica");
            }
        }
    }

    function ControllaAnagrafeICCS($elementi, $anades_rec = array()) {
        if (!$elementi['dati']) {
            Out::msgStop("Attenzione", "Specificare dei parametri per la protocollazione");
            return false;
        }
        $model = 'proHWS.class';
        $praFascicolo = new praFascicolo($this->currGesnum, $anades_rec['ROWID']);
        $elementi = $praFascicolo->getElementiProtocollaPratica();
        if (!$elementi) {
            return false;
        }

        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $proHWS = new proHWS();
        $ricerca = array();
        if (isset($elementi['dati']['MittDest']['Denominazione'])) {
            $ricerca['descrizione'] = $elementi['dati']['MittDest']['Denominazione'];
        }
        if (isset($elementi['dati']['MittDest']['CF'])) {
            $ricerca['idfiscale'] = $elementi['dati']['MittDest']['CF'];
        }
        if (isset($elementi['dati']['MittDest']['Indirizzo'])) {
            $ricerca['indirizzo'] = $elementi['dati']['MittDest']['Indirizzo'];
        }
        if (isset($elementi['dati']['MittDest']['CAP'])) {
            $ricerca['cap'] = $elementi['dati']['MittDest']['CAP'];
        }
        if (isset($elementi['dati']['MittDest']['Citta'])) {
            $ricerca['citta'] = $elementi['dati']['MittDest']['Citta'];
        }
        if (isset($elementi['dati']['MittDest']['Provincia'])) {
            $ricerca['provincia'] = $elementi['dati']['MittDest']['Provincia'];
        }
        if (isset($elementi['dati']['MittDest']['Email'])) {
            $ricerca['email'] = $elementi['dati']['MittDest']['Email'];
        }
        $ritorno = $proHWS->cercaRubrica($ricerca);
        /**
         * gestione del risultato della ricerca
         */
        if ($ritorno['Status'] != '0') {
            Out::msgStop("Errore", $ritorno['Message']);
            return false;
        }

        /*
         * se non ci sono record trovati procedo con l'inserimento in anagrafica
         */
        if (!$ritorno['RetValue']) {

            $this->inserisciRubricaWS();
            return;
        }
        if (count($ritorno['RetValue']) == 1) {
            /*
             * se è stato trovato un solo record
             */
            $this->datiRubricaWS = $ritorno['RetValue'][0];
            $this->datiRubricaWS['rowidAnades'] = $_POST[$this->nameForm . '_ANADES']['ROWID'];
            if ($this->datiRubricaWS['codiceFiscale'] == $elementi['dati']['MittDest']['CF'] && $this->datiRubricaWS['indirizzo'] == $elementi['dati']['MittDest']['Indirizzo'] && $this->datiRubricaWS['citta'] == $elementi['dati']['MittDest']['Citta']) {
                /*
                 * se i dati corrispondono...
                 */
                $this->idCorrispondente = $this->datiRubricaWS['codice'];
                $this->ProtocolloICCS();
            } else {
                if ($this->datiRubricaWS['codiceFiscale'] == $elementi['dati']['MittDest']['CF']) {
                    Out::msgQuestion("Selezione", "&Egrave; stata trovata la seguente anagrafica per il codice fiscale " . $elementi['dati']['MittDest']['CF'] . ": <br>
                Nominativo: " . $ritorno['RetValue'][0]['nome'] . " " . $ritorno['RetValue'][0]['cognome'] . "<br>
                Rag. Sociale: " . $ritorno['RetValue'][0]['ragioneSociale'] . "<br>
                Cod. Fisc.: " . $ritorno['RetValue'][0]['codiceFiscale'] . "<br>
                P. Iva: " . $ritorno['RetValue'][0]['partitaIva'] . "<br>
                Indirizzo: " . $ritorno['RetValue'][0]['indirizzo'] . "<br>
                " . $ritorno['RetValue'][0]['cap'] . " " . $ritorno['RetValue'][0]['citta'] . " (" . $ritorno['RetValue'][0]['prov'] . ")<br>
                Scegliere L'opzione desiderata.", array(
                        'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaInserimentoWS', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Conferma Selezione' => array('id' => $this->nameForm . '_ConfermaRubricaWS', 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                    );
                } else {
                    /*
                     * se i dati non corrispondono del tutto...
                     */
                    Out::msgQuestion("Selezione", "&Egrave; stata trovata la seguente anagrafica: <br>
                Nominativo: " . $ritorno['RetValue'][0]['nome'] . " " . $ritorno['RetValue'][0]['cognome'] . "<br>
                Rag. Sociale: " . $ritorno['RetValue'][0]['ragioneSociale'] . "<br>
                Cod. Fisc.: " . $ritorno['RetValue'][0]['codiceFiscale'] . "<br>
                P. Iva: " . $ritorno['RetValue'][0]['partitaIva'] . "<br>
                Indirizzo: " . $ritorno['RetValue'][0]['indirizzo'] . "<br>
                " . $ritorno['RetValue'][0]['cap'] . " " . $ritorno['RetValue'][0]['citta'] . " (" . $ritorno['RetValue'][0]['prov'] . ")<br>
                Scegliere L'opzione desiderata.", array(
                        'F8-Inserisci Nuovo' => array('id' => $this->nameForm . '_InserisciRubricaWS', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Conferma Selezione' => array('id' => $this->nameForm . '_ConfermaRubricaWS', 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                    );
                }
            }
        } else {
            /*
             * se invece ci sono più record
             */
            praRic::praRubricaWS($ritorno['RetValue'], $this->nameForm, 'returnRubricaWS');
        }
        /**
         * fine gestione del risultato della ricerca
         */
    }

    public function inserisciRubricaWS($anades_rec = array()) {
        $model = 'proHWS.class';
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $proHWS = new proHWS();
        $praFascicolo = new praFascicolo($this->currGesnum, $anades_rec['ROWID']);
        $elementi = $praFascicolo->getElementiProtocollaPratica();
        if (!$elementi) {
            return false;
        }
        $dati = array();
        if (isset($elementi['dati']['MittDest']['Denominazione'])) {
            $dati['ragioneSociale'] = $elementi['dati']['MittDest']['Denominazione'];
        }
        if (isset($elementi['dati']['MittDest']['CF'])) {
            $dati['codiceFiscale'] = $elementi['dati']['MittDest']['CF'];
        }
        if (isset($elementi['dati']['MittDest']['Indirizzo'])) {
            $dati['indirizzo'] = $elementi['dati']['MittDest']['Indirizzo'];
        }
        if (isset($elementi['dati']['MittDest']['CAP'])) {
            $dati['cap'] = $elementi['dati']['MittDest']['CAP'];
        }
        if (isset($elementi['dati']['MittDest']['Citta'])) {
            $dati['citta'] = $elementi['dati']['MittDest']['Citta'];
        }
        if (isset($elementi['dati']['MittDest']['Provincia'])) {
            $dati['prov'] = $elementi['dati']['MittDest']['Provincia'];
        }
        if (isset($elementi['dati']['MittDest']['Email'])) {
            $dati['email'] = $elementi['dati']['MittDest']['Email'];
        }
        $ritorno = $proHWS->salvaVoceRubrica($dati);
        /**
         * gestione del risultato dell'inserimento
         */
        if ($ritorno['Status'] != '0') {
            Out::msgStop("Errore", $ritorno['Message']);
            return false;
        }
        /*
         * se non ci sono record trovati procedo con l'inserimento in anagrafica
         */
        if ($ritorno['Status'] == '0') {
            $this->idCorrispondente = $ritorno['RetValue'][0]['codice'];
            $this->ProtocolloICCS($anades_rec);
        } else {
            
        }

        /**
         * fine gestione dell'inserimento in rubrica
         */
        return;
    }

    public function ProtocolloICCS($anades_rec = array(), $onlyMainDoc = false) {
        $proges_rec = $this->praLib->GetProges($this->currGesnum);
        $model = 'proHWS.class';
        $praFascicolo = new praFascicolo($this->currGesnum, $anades_rec['ROWID']);
        $elementi = $praFascicolo->getElementiProtocollaPratica();
        $arrayDoc = $praFascicolo->getAllegatiProtocollaPratica('WSPU', true, $onlyMainDoc);
        if (!$elementi) {
            return false;
        }
        $proges_rowid = $proges_rec['ROWID'];
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $proHWS = new proHWS();
        $elementi['dati']['ufficio'] = $this->praLib->getUfficioHWS($proges_rec['GESNUM']);
        $elementi['dati']['MetaDati'] = $this->praLib->GetMetadatiProges($proges_rec['GESNUM']);

        /*
         * controllo che ci siano gli allegati
         */
        if ($arrayDoc) {
            $elementi['dati']['Principale'] = $arrayDoc['Principale'];
            $elementi['dati']['Allegati'] = $arrayDoc['Allegati'];
            /*
             * fine documenti allegati
             */
        }

        /*
         * qui parte relativa alla ricerca anagrafe
         */
        if (isset($this->idCorrispondente) && $this->idCorrispondente != '') {
            $elementi['dati']['corrispondente'] = $this->idCorrispondente;
            /*
             * una volta settato il corrispondente lo svuoto per essere pronto per la prossima protocollazione
             */
            $this->idCorrispondente = '';
        } else {
            /**
             * se non c'è il codice del corrispondente non proseguo con la protocollazione
             * faccio la ricerca del corrispondente
             * dalla fase di ricerca si rilancia la protocollazione col codice settato
             */
            $this->ControllaAnagrafeICCS($elementi, $anades_rec);
            return;
        }
        /*
         * fine parte relativa alla ricerca anagrafe
         */
        $valore = $proHWS->protocollazioneIngresso($elementi);
        if ($valore['Status'] == "0") {
            $codice = $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
            $anno = date("Y");
            Out::valore($this->nameForm . '_Numero_prot', $codice);
            Out::valore($this->nameForm . '_Anno_prot', $anno);
            Out::valore($this->nameForm . '_PROGES[GESPAR]', "A");
            $proges_rec = array();
            /*
             * salvo i metadati
             */
            $proges_rec['GESMETA'] = serialize($valore['RetValue']);

            $proges_rec['ROWID'] = $proges_rowid;
            $proges_rec['GESNPR'] = $anno . $valore['RetValue']['DatiProtocollazione']['proNum']['value']; //modifica del 03.10.2013 Mario
            $proges_rec['GESPAR'] = "A";
            $update_Info = "Oggetto rowid:" . $proges_rec['ROWID'] . ' num:' . $proges_rec['GESNPR'];
            if (!$this->updateRecord($this->PRAM_DB, 'PROGES', $proges_rec, $update_Info)) {
                Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                return;
            }
            $this->switchIconeProtocollo($proges_rec['GESNPR'], $valore['RetValue']);
            $this->bloccaAllegati($this->currGesnum, $arrayDoc['pasdoc_rec'], 'PR');
        } else {
            Out::msgStop("Errore in Protocollazione", $valore['Message']);
            return;
        }
    }

    function caricaParametriCaps($proges_rec) {
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

            /*
             * CAP_PRINTER 
             */
            $arrCaps[] = array(
                'capability' => '0x1026',
                'valuetype' => '',
                'datatype' => '5',
                'containertype' => '0',
                'datavalue' => '6'
            );

            /*
             * CAP_PRINTERENABLED 
             */
            $arrCaps[] = array(
                'capability' => '0x1027',
                'valuetype' => '',
                'datatype' => '5',
                'containertype' => '0',
                'datavalue' => '0'
            );

            /*
             * CAP_MICROREI_PRINTERSTRING
             */
            $arrCaps[] = array(
                'capability' => '0x8002',
                'valuetype' => '0x000c',
                'datatype' => '5',
                'containertype' => '1',
                'datavalue' => "disabilitata"//$testo
            );

            /*
             * CAP_PRINTER
             */
            $arrCaps[] = array(
                'capability' => '0x1026',
                'valuetype' => '',
                'datatype' => '5',
                'containertype' => '0',
                'datavalue' => '4'
            );

            /*
             * CAP_PRINTERENABLED
             */
            $arrCaps[] = array(
                'capability' => '0x1027',
                'valuetype' => '',
                'datatype' => '5',
                'containertype' => '0',
                'datavalue' => '1'
            );

            /*
             * CAP_MICROREI_PRINTERSTRING
             */
            $arrCaps[] = array(
                'capability' => '0x8002',
                'valuetype' => '0x000c',
                'datatype' => '5',
                'containertype' => '1',
                'datavalue' => $testo
            );

            /*
             * CAP_MICROREI_PRINTERPOSITION
             */
            $arrCaps[] = array(
                'capability' => '0x800a',
                'valuetype' => '0x0004',
                'datatype' => '5',
                'containertype' => '0',
                'datavalue' => '3'
            );

            /*
             * CAP_MICROREI_PRINTERDENSITY
             */
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

    public function bloccaAllegati($chiave, $rowidArr = array(), $tipo = "A") {
        $praFascicolo = new praFascicolo($this->currGesnum);
        $praFascicolo->bloccaAllegati($chiave, $rowidArr, $tipo);
    }

    public function inizializzaForm() {
        Out::hide($this->nameForm . "_NonAssegnati_field");
        Out::html($this->nameForm . "_PROGES[GESPAR]", "");
        $this->CreaCombo();
        $this->selectCalendari();
        if ($this->flagAssegnazioni == true) {
            // Tab Assegnazioni
            $generator = new itaGenerator();
            $retHtml = $generator->getModelHTML('praTabAssegnazioni', false, $this->nameForm, false);
            Out::tabAdd($this->nameForm . '_tabProcedimento', '', $retHtml);
            Out::show($this->nameForm . "_NonAssegnati_field");
        }
        if ($this->flagPagamenti == true) {
            // Tab Pagamenti
            $generator = new itaGenerator();
            $retHtml = $generator->getModelHTML('praTabPagamenti', false, $this->nameForm, false);
            Out::tabAdd($this->nameForm . '_tabProcedimento', '', $retHtml);
        }

        /*
         * Carico pannello Dati aggiuntivi
         */
        Out::html($this->nameForm . '_paneAggiuntivi', '');

        /* @var $praCompDatiAggiuntivi praCompDatiAggiuntivi */
        $praCompDatiAggiuntivi = itaFormHelper::innerForm('praCompDatiAggiuntivi', $this->nameForm . '_paneAggiuntivi');
        $praCompDatiAggiuntivi->setEvent('openform');
        $praCompDatiAggiuntivi->parseEvent();

        $this->praCompDatiAggiuntiviFormname = $praCompDatiAggiuntivi->getNameForm();
    }

    public function bottoniAssegnazione($dataChiusura = "") {
        Out::hide($this->nameForm . "_AssegnaPraticaButt");
        Out::hide($this->nameForm . "_RestituisciPraticaButt");
        Out::hide($this->nameForm . "_InCaricoPraticaButt");
        Out::hide($this->nameForm . "_divButtAssegnazioni");
        if ($dataChiusura)
            return;
        if ($this->flagAssegnazioni == true) {
            Out::show($this->nameForm . "_divButtAssegnazioni");
            $funzioniAssegnazione = praFunzionePassi::getFunzioniAssegnazione($this->currGesnum, 0, $this->profilo);
            if ($funzioniAssegnazione['ASSEGNA'])
                Out::show($this->nameForm . "_AssegnaPraticaButt");
            if ($funzioniAssegnazione['RESTITUISCI'])
                Out::show($this->nameForm . "_RestituisciPraticaButt");
            if ($funzioniAssegnazione['PRENDIINCARICO'])
                Out::show($this->nameForm . "_InCaricoPraticaButt");
        }
    }

    private function getOneri() {
        $sql = "SELECT
                    PROIMPO.*,
                    ANATIPIMPO.DESCTIPOIMPO,
                    (   SELECT
                            COUNT(*)
                        FROM
                            PROCONCILIAZIONE
                        WHERE
                            PROCONCILIAZIONE.IMPONUM = PROIMPO.IMPONUM
                        AND
                            PROCONCILIAZIONE.IMPOPROG = PROIMPO.IMPOPROG
					) AS NUMEROPAGAMENTI
                FROM
                    PROIMPO
                LEFT OUTER JOIN
                    ANATIPIMPO
                ON
                    ANATIPIMPO.CODTIPOIMPO = PROIMPO.IMPOCOD
                WHERE
                    IMPONUM = '{$this->currGesnum}'
                ORDER BY
                    IMPOPROG ASC";

        $Oneri_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        return $Oneri_tab;
    }

    private function getPagamenti() {
        $sql = "SELECT
                    PROCONCILIAZIONE.*,
                    PROIMPO.DATAREG,
                    PROIMPO.IMPORTO,
                    PROIMPO.DATASCAD,
                    ANATIPIMPO.DESCTIPOIMPO,
                    ANAQUIET.QUIETANZATIPO
                FROM
                    PROCONCILIAZIONE
                LEFT OUTER JOIN
                    PROIMPO
                ON
                    PROIMPO.IMPONUM = PROCONCILIAZIONE.IMPONUM
                AND
                    PROIMPO.IMPOPROG = PROCONCILIAZIONE.IMPOPROG
                LEFT OUTER JOIN
                    ANATIPIMPO
                ON
                    ANATIPIMPO.CODTIPOIMPO = PROIMPO.IMPOCOD
                LEFT OUTER JOIN
                    ANAQUIET
                ON
                    ANAQUIET.CODQUIET = PROCONCILIAZIONE.QUIETANZA
                WHERE
                    PROCONCILIAZIONE.IMPONUM = '{$this->currGesnum}'
                ORDER BY
                    PROCONCILIAZIONE.IMPOPROG ASC";

        $Pagamenti_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

        return $Pagamenti_tab;
    }

    public function totalePagamenti() {
        $sql = "SELECT
                    SUM(PROIMPO.IMPORTO) AS IMPORTO,
                    SUM(PROIMPO.PAGATO) AS PAGATO
                FROM
                    PROIMPO
                WHERE
                    PROIMPO.IMPONUM = '{$this->currGesnum}'
                ORDER BY
                    PROIMPO.IMPOPROG ASC";

        $totali_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

        Out::valore($this->nameForm . '_TOTALEIMPORTO', $totali_rec['IMPORTO']);
        Out::valore($this->nameForm . '_TOTALEPAGATO', $totali_rec['PAGATO']);
    }

    public function GetArrayAltreFunzioni() {
        $proges_rec = $this->praLib->GetProges($this->currGesnum);
        $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA'], 'codice');
        if ($anaspa_rec["SPAENTECOMM"]) {
            $dittaCOMM = $anaspa_rec['SPAENTECOMM'];
        }
        if ($this->checkExistDB("COMM", $dittaCOMM)) {
            if ($dittaCOMM) {
                $wcoLib = new wcoLib($dittaCOMM);
                $comsua_rec = $wcoLib->GetComsua($proges_rec['GESNUM'], "pratica");
                $compro_rec = $wcoLib->GetCompro($proges_rec['GESNUM'], "propak");
            } else {
                $comsua_rec = $this->wcoLib->GetComsua($proges_rec['GESNUM'], "pratica");
                $compro_rec = $this->wcoLib->GetCompro($proges_rec['GESNUM'], "propak");
            }
            if (!$comsua_rec) {
                if (!$compro_rec) {
                    $arrayAzioni['Scrivi su Commercio'] = array('id' => $this->nameForm . '_CollegaCommercio', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                } else {
                    $arrayAzioni['Vedi Procedimento Commercio'] = array('id' => $this->nameForm . '_VediEventoCommercio', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                }
            } else {
                $arrayAzioni['Vedi Procedimento Commercio'] = array('id' => $this->nameForm . '_VediCommercio', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
            }
        }

        if ($this->checkExistDB("GAFIERE")) {
            $anafiere_tab = ItaDB::DBSQLSelect($this->gfmLib->getGAFIEREDB(), "SELECT * FROM ANAFIERE", true);
            $tipoEnte = $this->praLib->GetTipoEnte();
            $Anapra_rec = $this->praLib->GetAnapra($proges_rec['GESPRO']);
            $PRAMDB = $this->PRAM_DB;
            if ($tipoEnte == "S" && $Anapra_rec['PRASLAVE'] == 1) {
                $dbsuffix = $this->praLib->GetEnteMaster();
                $PRAMDB = ItaDB::DBOpen('PRAM', $dbsuffix);
            }
            $itedag_fiere = ItaDB::DBSQLSelect($PRAMDB, "SELECT * FROM ITEDAG WHERE ITECOD='" . $proges_rec['GESPRO'] . "' AND (ITDTIP='Denom_Fiera')", false);
            if (!$itedag_fiere) {
                $anafiere_tab = false;
            }
            $bandifiere_tab = ItaDB::DBSQLSelect($this->gfmLib->getGAFIEREDB(), "SELECT * FROM FIERE WHERE BANDO = 1", true);

//            $prodag_bandiF = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$this->currGesnum' AND (DAGKEY='DENOM_FIERA_BANDO' OR DAGKEY='DENOM_FIERAP_BANDO') AND DAGVAL <> ''", false);
            $prodag_bandiF = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$this->currGesnum' AND (DAGTIP='Denom_FieraBando' OR DAGTIP='Denom_FieraPBando')", false);
            if (!$prodag_bandiF) {
                $bandifiere_tab = false;
            }
            $bandimercati_tab = ItaDB::DBSQLSelect($this->gfmLib->getGAFIEREDB(), "SELECT * FROM BANDIM WHERE BANDO = 1", true);
//            $prodag_bandiM = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$this->currGesnum' AND (DAGKEY='DENOM_MERCATO_BANDO' OR DAGKEY='DENOM_PI_BANDO') AND DAGVAL <> ''", false);
            $prodag_bandiM = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$this->currGesnum' AND (DAGTIP='Denom_MercatoBando' OR DAGTIP='Denom_PIBando')", false);
            if (!$prodag_bandiM) {
                $bandimercati_tab = false;
            }
            if (($anafiere_tab) && (!$bandifiere_tab && !$bandimercati_tab)) {
                $sql = "SELECT * FROM FIERESUAP WHERE SUAPID = " . $proges_rec['ROWID'];
                $fieresuap_tab = ItaDB::DBSQLSelect($this->gfmLib->getGAFIEREDB(), $sql, true);
                if (!$fieresuap_tab) {
                    $arrayAzioni['Scrivi Domanda di<br>Partecipazione alla Fiera'] = array('id' => $this->nameForm . '_CollegaFiere', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                } else {
                    $arrayAzioni['Vedi Domanda Fiera'] = array('id' => $this->nameForm . '_VediFiere', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                    $arrayAzioni['Scollega Domanda Fiera'] = array('id' => $this->nameForm . '_ScollegaFiere', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                }
            }
            if ($bandifiere_tab) {
                $sql = "SELECT * FROM FIERESUAP WHERE SUAPID = " . $proges_rec['ROWID'] . " AND IDFIERECOM > 0";
                $fieresuap_tab = ItaDB::DBSQLSelect($this->gfmLib->getGAFIEREDB(), $sql, true);
                if (!$fieresuap_tab) {
                    $arrayAzioni['Scrivi Domanda di<br>Partecipazione al Bando'] = array('id' => $this->nameForm . '_CollegaDomandaBando', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                } else {
                    $arrayAzioni['Vedi Domanda Bando'] = array('id' => $this->nameForm . '_VediDomandaBandoF', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                    $arrayAzioni['Scollega Domanda Bando'] = array('id' => $this->nameForm . '_ScollegaDomandaBando', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                }
            }
            if ($bandimercati_tab) {
                $sql = "SELECT * FROM FIERESUAP WHERE SUAPID = " . $proges_rec['ROWID'] . " AND IDMERCACOM > 0";
                $fieresuap_tab = ItaDB::DBSQLSelect($this->gfmLib->getGAFIEREDB(), $sql, true);
                if (!$fieresuap_tab) {
                    $arrayAzioni['Scrivi Domanda di<br>Partecipazione al Bando'] = array('id' => $this->nameForm . '_CollegaDomandaBando', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                } else {
                    $arrayAzioni['Vedi Domanda Bando'] = array('id' => $this->nameForm . '_VediDomandaBandoM', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                    $arrayAzioni['Scollega Domanda Bando'] = array('id' => $this->nameForm . '_ScollegaDomandaBando', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                }
            }

            /*
             * cerco dentro tutti i db di gafiere se esiste nella tabella FIERESUAP questo ente come origine dei dati e questa pratica come origine
             */
            $sql = "SHOW DATABASES LIKE 'GAFIERE%'";
            $lista = ItaDB::DBSQLSelect(App::$itaEngineDB, $sql, true);
            foreach ($lista as $rec) {
                $s_rec = array_values($rec);
                $db_name = substr($s_rec[0], 0, strlen("GAFIERE"));
                $db_ditta = substr($s_rec[0], strlen("GAFIERE"));
                $GAFIERE_TMP = ItaDB::DBOpen(strtoupper($db_name), $db_ditta);
                $sql = "SELECT * FROM FIERESUAP WHERE SUAKEY = '" . App::$utente->getKey('ditta') . "' AND SUAPID = " . $proges_rec['ROWID'];
                $check_rec = ItaDB::DBSQLSelect($GAFIERE_TMP, $sql, false);
                if ($check_rec) {
                    $arrayAzioni['Vedi Domanda Fiera'] = array('id' => $this->nameForm . '_VediFiere', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                    break;
                }
            }

            /*
             * sistemo eventuali voci
             */
            if (isset($arrayAzioni['Vedi Domanda Bando'])) {
                unset($arrayAzioni['Scrivi Domanda di<br>Partecipazione al Bando']);
                unset($arrayAzioni['Vedi Domanda Fiera']);
            }
            $sql = "SELECT * FROM PRODAG WHERE DAGNUM = '" . $this->currGesnum . "' AND DAGTIP = 'Scambio_Posto'";
            $campioPosto_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($campioPosto_rec) {
                /*
                 * Attiva pulsante per SCAMBIO CONSENSUALE DI POSTEGGIO
                 */
                $sql = "SELECT * FROM FIERESUAP WHERE SUAPID = " . $proges_rec['ROWID'] . "";
                $campiatoPosto_rec = ItaDB::DBSQLSelect($this->gfmLib->getGAFIEREDB(), $sql, true);
                if (!$campiatoPosto_rec) {
                    $arrayAzioni['Scambia di Posto'] = array('id' => $this->nameForm . '_ScambioPosto', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                } else {
                    $arrayAzioni['Vedi Cambio Posto'] = array('id' => $this->nameForm . '_VediScambioPosto', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                }
            }
            $sql = "SELECT * FROM PRODAG WHERE DAGNUM = '" . $this->currGesnum . "' AND DAGTIP = 'Giustificazione'";
            $Giustificazione_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($Giustificazione_rec) {
                /*
                 * Attiva pulsante per GIUSTIFICAZIONE ASSENZA FIERE E MERCATI
                 */
                $sql = "SELECT * FROM FIERESUAP WHERE SUAPID = " . $proges_rec['ROWID'] . "";
                $campiatoPosto_rec = ItaDB::DBSQLSelect($this->gfmLib->getGAFIEREDB(), $sql, true);
                if (!$campiatoPosto_rec) {
                    $arrayAzioni['Giustifica Assenze'] = array('id' => $this->nameForm . '_GiustificaAssenze', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                } else {
                    $arrayAzioni['Vedi Giustificazione'] = array('id' => $this->nameForm . '_VediGiustificazione', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                }
            }
        }
        if ($this->checkExistDB("ISOLA")) {
            $prodag_ztl = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$this->currGesnum' AND (DAGTIP='TipoPermessoZTL')", false);
            if ($prodag_ztl) {
                $sql = "SELECT * FROM ISOLA WHERE NUMERODOMANDA = '" . intval(substr($proges_rec['GESPRA'], 4)) . "' AND DATADOMANDA = '" . $proges_rec['GESDRI'] . "'";
                $Isola_tab = ItaDB::DBSQLSelect($this->ztlLib->getISOLADB(), $sql, true);
                if (!$Isola_tab) {
                    $arrayAzioni['Crea Permessi ZTL'] = array('id' => $this->nameForm . '_CollegaPermessiZTL', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                } else {
                    $arrayAzioni['Vedi Permessi'] = array('id' => $this->nameForm . '_VediPermessiZTL', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                }
                unset($arrayAzioni['Vedi Domanda Fiera']);
                unset($arrayAzioni['Vedi Domanda Bando']);
                unset($arrayAzioni['Scrivi Domanda di<br>Partecipazione alla Fiera']);
                unset($arrayAzioni['Scrivi Domanda di<br>Partecipazione al Bando']);
                unset($arrayAzioni['Scrivi su Commercio']);
            }

            /*
             * sistemo eventuali voci
             */
            if (isset($arrayAzioni['Vedi Permessi'])) {
                unset($arrayAzioni['Crea Permessi ZTL']);
            }
            $prodag_ztl = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$this->currGesnum' AND (DAGTIP='RinnovoPermessoZTL')", false);
            if ($prodag_ztl) {
                $sql = "SELECT * FROM ISOLA WHERE NUMERODOMANDA = '" . intval(substr($proges_rec['GESPRA'], 4)) . "' AND DATADOMANDA = '" . $proges_rec['GESDRI'] . "'";
                $Isola_tab = ItaDB::DBSQLSelect($this->ztlLib->getISOLADB(), $sql, true);
                if (!$Isola_tab) {
                    $arrayAzioni['Rinnova Permessi ZTL'] = array('id' => $this->nameForm . '_RinnovaPermessiZTL', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                } else {
                    $arrayAzioni['Vedi Permessi'] = array('id' => $this->nameForm . '_VediPermessiZTL', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                }
                unset($arrayAzioni['Vedi Domanda Fiera']);
                unset($arrayAzioni['Vedi Domanda Bando']);
                unset($arrayAzioni['Scrivi Domanda di<br>Partecipazione alla Fiera']);
                unset($arrayAzioni['Scrivi Domanda di<br>Partecipazione al Bando']);
                unset($arrayAzioni['Scrivi su Commercio']);
            }

            /*
             * sistemo eventuali voci
             */
            if (isset($arrayAzioni['Vedi Permessi'])) {
                unset($arrayAzioni['Rinnova Permessi ZTL']);
            }
            $prodag_ztl = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$this->currGesnum' AND (DAGTIP='VariaTargaZTL')", false);
            if ($prodag_ztl) {

                /*
                 * verificare se esiste o meno il collegamento su isola. Se esiste può solo andare a vedere i permessi
                 */
                $sql = "SELECT * FROM ISOLASUAP WHERE SUAPRA = '" . $proges_rec['GESNUM'] . "'";
                $check_rec = ItaDB::DBSQLSelect($this->ztlLib->getISOLADB(), $sql, false);
                if ($check_rec) {
                    $arrayAzioni['Vedi Variazioni'] = array('id' => $this->nameForm . '_VediVariazioniZTL', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                } else {
                    $arrayAzioni['Varia Targhe'] = array('id' => $this->nameForm . '_VariaTargheZTL', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm);
                }
                unset($arrayAzioni['Vedi Domanda Fiera']);
                unset($arrayAzioni['Vedi Domanda Bando']);
                unset($arrayAzioni['Scrivi Domanda di<br>Partecipazione alla Fiera']);
                unset($arrayAzioni['Scrivi Domanda di<br>Partecipazione al Bando']);
                unset($arrayAzioni['Scrivi su Commercio']);
            }

            /*
             * sistemo eventuali voci
             */
            if (isset($arrayAzioni['Vedi Permessi'])) {
                unset($arrayAzioni['Rinnova Permessi ZTL']);
            }
        }

        return $arrayAzioni;
    }

    public function DisattivaCellStatoPasso() {
        foreach ($this->praPassi as $passo) {
            // BLOCCATA MOMENTANEAMENTE
            //   TableView::setCellValue($this->gridPassi, $passo['ROWID'], 'STATOPASSO', "", 'not-editable-cell', '', 'false');
            //  continue;
            $sql = " SELECT * FROM PROPAS WHERE ROWID='" . $passo['ROWID'] . "'";
            $Propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($Propas_rec['PROVISIBILITA'] != 'Aperto') {
                $proges_rec = $this->praLib->GetProges($Propas_rec['PRONUM']);
                if (!$this->praPerms->checkSuperUser($proges_rec)) {
                    //$perms = $this->praPerms->impostaPermessiPasso($Propas_rec);
                    //Out::checkDataButton($this->nameForm, $perms);
                    if ($this->praPerms->checkUtenteGenerico($Propas_rec)) {
                        TableView::setCellValue($this->gridPassi, $passo['ROWID'], 'STATOPASSO', "", 'not-editable-cell', '', 'false');
                    }
                }
            }
        }
    }

    public function CheckAperturaPassoPadre($Propas) {
        $sql = "SELECT * FROM PROPAS WHERE PROPAK='" . $Propas['PROKPRE'] . "'";
        $Propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($Propas_rec) {
            $this->Propas_key = $Propas['PROKPRE'];
            if (!$Propas_rec['PROINI']) {
                Out::msgQuestion("Attenzione", "Il passo antecedente di questo passo non è aperto si desidera aprirlo?", array(
                    'Non aprire' => array('id' => $this->nameForm . '_AnnullaApriPadre', 'model' => $this->nameForm, 'shortCut' => "f8"),
                    'Conferma' => array('id' => $this->nameForm . '_ConfermaApriPadre', 'model' => $this->nameForm, 'shortCut' => "f5")
                        )
                );
            } else {
                TableView::reload($this->gridPassi);
            }
        } else {
            TableView::reload($this->gridPassi);
        }
    }

    public function ChiudiPasso($Rowid) {
        if (!$Rowid) {
            Out::msgStop("Errore!!", "Id del passo non trovato. Impossibile Chiuderlo");
            return false;
        }
        $sql = " SELECT * FROM PROPAS WHERE ROWID='" . $Rowid . "'";
        $Propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if (!$Propas_rec) {
            Out::msgStop("Errore!!", "Record del passo non trovato. Impossibile Chiuderlo");
            return false;
        }
        if ($Propas_rec['PROPAK'] == "") {
            Out::msgStop("Errore!!", "Chiave del passo non trovata. Impossibile Chiuderlo");
            return false;
        }

        $praPasso = new praPasso();

        if ($Propas_rec['PROFIN']) {
            Out::msgInfo("Attenzione!!", "Il passo risulta già chiuso in data " . substr($Propas_rec['PROFIN'], 6, 2) . "/" . substr($Propas_rec['PROFIN'], 4, 2) . "/" . substr($Propas_rec['PROFIN'], 0, 4));
            return false;
        }
        $Propas_rec['PROFIN'] = date('Ymd');
        if (!$praPasso->SincronizzaRecordPasso($Propas_rec)) {
            return false;
        }

        $sql = "SELECT * FROM PROPAS WHERE PROKPRE='" . $Propas_rec['PROPAK'] . "' AND PROKPRE <> ''";
        $sottoPassi_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        $this->Propas = array();
        if ($sottoPassi_tab) {
            $this->Propas = $sottoPassi_tab;
        }
        //$this->Propas = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        return true;
    }

    private function selectCalendari() {
        $envLibCalendar = new envLibCalendar();
        $personalCalendars = $envLibCalendar->getSelectedCalendars(true);
        $calendarsList = $envLibCalendar->getCalendars('_1__');
        $oneSelect = false;
        Out::select($this->nameForm . '_Calendario', 1, "", "", "");
        foreach ($calendarsList as $key => $calendar) {
            if ($personalCalendars[$calendar['ROWID']]) {
                if ($calendar['IDUTENTE'] == App::$utente->getIdUtente() && !$oneSelect) {
                    $sel = '1';
                    $oneSelect = true;
                } else
                    $sel = '0';
                $text = $calendar['TITOLO'];
                $text = '<span style="margin-right: 4px; width: 20px; height: 10px; display: inline-block; background-color: ' . $personalCalendars[$calendar['ROWID']]['color'] . ';"></span> ' . $text;
                Out::select($this->nameForm . '_Calendario', 1, $calendar['ROWID'], $sel, $text);
            }
        }
        foreach ($this->tipoProm as $key => $value) {
            $sel = $key == 'notifica' ? '1' : '0';
            Out::select($this->nameForm . '_Calendario', 1, $key, $sel, $value);
        }
        foreach ($this->unitProm as $key => $value) {
            $sel = $key == 'minuti' ? '1' : '0';
            Out::select($this->nameForm . '_Calendario', 1, $key, $sel, $value);
        }
    }

    function CaricaAccorpati($gesnum) {
        $accorpati_tab = $this->praLib->GetProgessub($gesnum, "codice", true, " AND PROGRESSIVO <> 0");
        foreach ($accorpati_tab as $key => $accorpati_rec) {
            $anapra_rec = $this->praLib->GetAnapra($accorpati_rec['PROPRO']);
            $anaeventi_rec = $this->praLib->GetAnaeventi($accorpati_rec['EVENTO']);
            $anatsp_rec = $this->praLib->GetAnatsp($accorpati_rec['SPORTELLO']);
            $anaset_rec = $this->praLib->GetAnaset($accorpati_rec['SETTORE']);
            $anaatt_rec = $this->praLib->GetAnaatt($accorpati_rec['ATTIVITA']);
            $accorpati_tab[$key]['PROCEDIMENTO'] = $accorpati_rec['PROPRO'] . " - " . $anapra_rec['PRADES__1'] . $anapra_rec['PRADES__2'] . $anapra_rec['PRADES__3'];
            $richiesta = substr($accorpati_rec['RICHIESTA'], 4) . "/" . substr($accorpati_rec['RICHIESTA'], 0, 4);
            if ($accorpati_rec['RICHKEY']) {
                $richiesta = $accorpati_rec['RICHKEY'];
            }
            $accorpati_tab[$key]['RICHIESTA'] = $richiesta;
            $accorpati_tab[$key]['EVENTO'] = $anaeventi_rec['EVTDESCR'];
            $accorpati_tab[$key]['SPORTELLO'] = $anatsp_rec['TSPDES'];
            $accorpati_tab[$key]['SETTORE'] = $accorpati_rec['SETTORE'] . " - " . $anaset_rec['SETDES'];
            $accorpati_tab[$key]['ATTIVITA'] = $accorpati_rec['ATTIVITA'] . " - " . $anaatt_rec['ATTDES'];
        }
        return $accorpati_tab;
    }

    function importaDestinatari($chiavePassoSorg, $chiavePassoDest) {
        $pramitdest_tab = $this->praLib->GetPraMitDest($chiavePassoSorg, "compak", true);
        if ($pramitdest_tab) {
            $pracomP_rec = $this->praLib->GetPracomP($chiavePassoDest);
            if (!$pracomP_rec) {
                $pracomP_rec = array();
                $pracomP_rec['COMNUM'] = $this->currGesnum;
                $pracomP_rec['COMPAK'] = $chiavePassoDest;
                $pracomP_rec['COMTIP'] = 'P';
                $insertP_Info = "Oggetto Copia Passo: inserimento partenza su PRACOM del passo $chiavePassoDest";
                if (!$this->insertRecord($this->PRAM_DB, 'PRACOM', $pracomP_rec, $insertP_Info)) {
                    return false;
                }
                $lastRowid = $this->PRAM_DB->getLastId();
                $pracomP_rec = $this->praLib->GetPracom($lastRowid, 'rowid');
            }

            foreach ($pramitdest_tab as $pramitdest_rec) {
                $pramitdest_new_rec = $pramitdest_rec;
                $pramitdest_new_rec['ROWID'] = 0;
                $pramitdest_new_rec['KEYPASSO'] = $chiavePassoDest;
                $pramitdest_new_rec['DATAINVIO'] = "";
                $pramitdest_new_rec['ORAINVIO'] = "";
                $pramitdest_new_rec['DATARISCONTRO'] = "";
                $pramitdest_new_rec['SCADENZARISCONTRO'] = "";
                $pramitdest_new_rec['IDMAIL'] = "";
                $pramitdest_new_rec['ROWIDPRACOM'] = $pracomP_rec['ROWID'];
                $insertP_Info = "Oggetto Copia Passo: inserimento destinario su PRAMITDEST " . $pramitdest_rec['NOME'] . " del passo $chiavePassoDest";
                if (!$this->insertRecord($this->PRAM_DB, 'PRAMITDEST', $pramitdest_new_rec, $insertP_Info)) {
                    return false;
                }
            }
        }
        return true;
    }

    function importaProtocolloArrivoPartenza($chiavePassoSorg, $chiavePassoDest) {
        $pracom_recP = $this->praLib->GetPracomP($chiavePassoSorg);
        //
        $PracomP_new_rec = array();
        $PracomP_new_rec['ROWID'] = 0;
        $PracomP_new_rec['COMNUM'] = $this->currGesnum;
        $PracomP_new_rec['COMTIP'] = $pracom_recP['COMTIP'];
        $PracomP_new_rec['COMPAK'] = $chiavePassoDest;
        $PracomP_new_rec['COMMETA'] = $pracom_recP['COMMETA'];
        //
        if ($pracom_recP['COMIDDOC']) {
            $PracomP_new_rec['COMIDDOC'] = $pracom_recP['COMIDDOC'];
            $PracomP_new_rec['COMDATADOC'] = $pracom_recP['COMDATADOC'];
        }
        if ($pracom_recP['COMPRT']) {
            $PracomP_new_rec['COMPRT'] = $pracom_recP['COMPRT'];
            $PracomP_new_rec['COMDPR'] = $pracom_recP['COMDPR'];
        }
        //
        if ($pracom_recP['COMIDDOC'] || $pracom_recP['COMPRT']) {
            $insert_Info = "Oggetto: Duplico protocollo/id doc in partenza passo " . $PracomP_new_rec['COMPAK'] . " della pratica $this->currGesnum";
            if (!$this->insertRecord($this->PRAM_DB, 'PRACOM', $PracomP_new_rec, $insert_Info)) {
                Out::msgStop("Duplicazione Passi", "Inserimento comunicazione in partenza su PRACOM fallito");
                return false;
            }
            $lastId_pracomP = $this->PRAM_DB->getLastId();
        }

        $pracom_recA = $this->praLib->GetPracomA($chiavePassoSorg);
        if ($pracom_recA['COMPRT']) {
            //$PracomA_new_rec = $pracom_recA;
            $PracomA_new_rec = array();
            $PracomA_new_rec['ROWID'] = 0;
            $PracomA_new_rec['COMNUM'] = $this->currGesnum;
            $PracomA_new_rec['COMTIP'] = $pracom_recA['COMTIP'];
            $PracomA_new_rec['COMPAK'] = $chiavePassoDest;
            $PracomA_new_rec['COMPRT'] = $pracom_recA['COMPRT'];
            $PracomA_new_rec['COMDPR'] = $pracom_recA['COMDPR'];
            $PracomA_new_rec['COMMETA'] = $pracom_recA['COMMETA'];
            if ($pracom_recA['COMRIF'] != 0) {
                $PracomA_new_rec['COMRIF'] = $lastId_pracomP;
            }
            //
            $insert_Info = "Oggetto: Duplico comunicazione in arrivo passo " . $PracomA_new_rec['COMPAK'] . " della pratica $this->currGesnum";
            if (!$this->insertRecord($this->PRAM_DB, 'PRACOM', $PracomA_new_rec, $insert_Info)) {
                Out::msgStop("Duplicazione Passi", "Inserimento comunicazione in arrivo su PRACOM fallito");
                return false;
            }
            $lastId_pracomA = $this->PRAM_DB->getLastId();
        }
        return array(
            "P" => array(
                "OLDROWID" => $pracom_recP['ROWID'],
                "NEWROWID" => $lastId_pracomP,
            ),
            "A" => array(
                "OLDROWID" => $pracom_recA['ROWID'],
                "NEWROWID" => $lastId_pracomA,
            ),
        );
    }

    function importaAllegatiPasso($chiavePassoSorg, $chiavePassoDest, $importaProtocollo, $rowidCom) {
        $Pasdoc_tab = $this->praLib->GetPasdoc($chiavePassoSorg, "codice", true);
        if ($Pasdoc_tab) {
            $PathSorg = $this->praLib->SetDirectoryPratiche(substr($chiavePassoSorg, 0, 4), $chiavePassoSorg, "PASSO", false);
            $PathDest = $this->praLib->SetDirectoryPratiche(substr($chiavePassoDest, 0, 4), $chiavePassoDest);
            $insert_Info = "Oggetto : Importo allegati del passo con chiave $chiavePassoSorg";
            foreach ($Pasdoc_tab as $Pasdoc_rec) {
                $pasprtclass = "";
                $pasprtrowid = 0;
                if ($importaProtocollo) {
                    $pasprtclass = $Pasdoc_rec['PASPRTCLASS'];
                    switch ($Pasdoc_rec['PASPRTCLASS']) {
                        case "PROGES":
                            $pasprtrowid = $Pasdoc_rec['PASPRTROWID'];
                            break;
                        case "PRACOM":
                            foreach ($rowidCom as $tipo => $comunicazione) {
                                if ($comunicazione['OLDROWID'] == $Pasdoc_rec['PASPRTROWID']) {
                                    $pasprtrowid = $comunicazione['NEWROWID'];
                                    break;
                                }
                            }
                            break;
                    }
                }
                $Pasdoc_new_rec = $Pasdoc_rec;
                $Pasdoc_new_rec['PASKEY'] = $chiavePassoDest;
                $Pasdoc_new_rec['PASFIL'] = md5(rand() * time()) . "." . pathinfo($Pasdoc_rec['PASNAME'], PATHINFO_EXTENSION);
                $Pasdoc_new_rec['PASLNK'] = "allegato://" . $Pasdoc_new_rec['PASFIL'];
                $Pasdoc_new_rec['ROWID'] = 0;
                $Pasdoc_new_rec['PASUTELOG'] = App::$utente->getKey('nomeUtente');
                $Pasdoc_new_rec['PASORADOC'] = date("H:i:s");
                $Pasdoc_new_rec['PASPRTCLASS'] = $pasprtclass;
                $Pasdoc_new_rec['PASPRTROWID'] = $pasprtrowid;
                $Pasdoc_new_rec['PASROWIDBASE'] = 0;
                $Pasdoc_new_rec['PASSUBTIPO'] = '';
                $Pasdoc_new_rec["PASSHA2"] = hash_file('sha256', $PathSorg . "/" . $Pasdoc_rec["PASFIL"]);
                if (!$this->insertRecord($this->PRAM_DB, 'PASDOC', $Pasdoc_new_rec, $insert_Info)) {
                    Out::msgStop("Inserimento data set", "Inserimento alleagti fallito");
                    return false;
                }
                copy($PathSorg . "/" . $Pasdoc_rec['PASFIL'], $PathDest . "/" . $Pasdoc_new_rec['PASFIL']);
                if (strtoupper(pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_EXTENSION)) == "XHTML") {
                    $basefileSorg = pathinfo($PathSorg . "/" . $Pasdoc_rec['PASFIL'], PATHINFO_FILENAME);
                    $basefileDest = pathinfo($PathSorg . "/" . $Pasdoc_new_rec['PASFIL'], PATHINFO_FILENAME);
                    if (file_exists($PathSorg . "/" . $basefileSorg . ".pdf")) {
                        copy($PathSorg . "/" . $basefileSorg . ".pdf", $PathDest . "/" . $basefileDest . ".pdf");
                    }
                    if (file_exists($PathSorg . "/" . $basefileSorg . ".pdf.p7m")) {
                        copy($PathSorg . "/" . $basefileSorg . ".pdf.p7m", $PathDest . "/" . $basefileDest . ".pdf.p7m");
                    }
                }
            }
        }
        return true;
    }

    function importaDatiAggiuntivi($chiavePassoSorg, $chiavePassoDest, $mantieniValore = false) {
        $prodag_tab = $this->praLib->GetProdag($chiavePassoSorg, 'dagpak', true);

        foreach ($prodag_tab as $prodag_rec) {
            $prodag_new_rec = $prodag_rec;

            unset($prodag_new_rec['ROWID']);
            if (!$mantieniValore) {
                unset($prodag_new_rec['DAGVAL']);
            }

            $dagset = explode('_', $prodag_rec['DAGSET']);
            $prodag_new_rec['DAGNUM'] = substr($chiavePassoDest, 0, 10);
            $prodag_new_rec['DAGPAK'] = $chiavePassoDest;
            $prodag_new_rec['DAGSET'] = $chiavePassoDest . '_' . $dagset[1];

            $insert_Info = "Oggetto: Duplico dato aggiuntivo " . $prodag_new_rec['DAGKEY'] . " passo " . $chiavePassoDest . " della pratica $this->currGesnum";
            if (!$this->insertRecord($this->PRAM_DB, 'PRODAG', $prodag_new_rec, $insert_Info)) {
                Out::msgStop('Duplicazione Dati Aggiuntivi', 'Inserimento dato aggiuntivo su PRODAG fallito');
                return false;
            }
        }

        return true;
    }

    function StampaReportFascicolo($Rowid, $reportName, $Desruo) {
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
        $itaJR = new itaJasperReport();
        $sql = "SELECT 
                    PROGES.*,
                    ANANOM.*,
                    ANAPRA.*,
                    ANATIP.*,
                    ANASERIEARC.*,
                    ANADES.*," .
                $this->PRAM_DB->strConcat("ANANOMASS.NOMCOG", "' '", "ANANOMASS.NOMNOM") . " AS ASSEGNATARIO
                    FROM PROGES PROGES
                    LEFT OUTER JOIN PROPAS PROPAS ON PROGES.GESNUM=PROPAS.PRONUM AND PROPAS.ROWID =(SELECT MAX(ROWID) FROM PROPAS WHERE PROPAS.PRONUM=PROGES.GESNUM AND PROOPE<>'') 
                    LEFT OUTER JOIN ANANOM ANANOMASS ON PROPAS.PRORPA=ANANOMASS.NOMRES
                    LEFT OUTER JOIN ANANOM ANANOM ON PROGES.GESRES=ANANOM.NOMRES
                    LEFT OUTER JOIN ANAPRA ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM
                    LEFT OUTER JOIN ANATIP ANATIP ON PROGES.GESTIP=ANATIP.TIPCOD
                    LEFT OUTER JOIN ANADES ANADES ON PROGES.GESNUM=ANADES.DESNUM AND (DESRUO='' OR DESRUO='$Desruo')
                    LEFT OUTER JOIN " . $this->PROT_DB->getDB() . ".ANASERIEARC ON " . $this->PROT_DB->getDB() . ".ANASERIEARC.CODICE = " . $this->PRAM_DB->getDB() . ".PROGES.SERIECODICE 
                    ";
        $sql .= " WHERE PROGES.ROWID='$Rowid'";
        if ($Desruo == '0002') {
            $sql .= " LIMIT 1"; // serve per stampa report per soggetti con lo stesso reporta non stampare più etichette
        }
        $parametriEnte_rec = $this->utiEnte->GetParametriEnte();
        $parameters = array(
            "Sql" => $sql,
            "Ente" => $parametriEnte_rec['DENOMINAZIONE']
        );
        $itaJR->runSQLReportPDF($this->PRAM_DB, $reportName, $parameters);
        return true;
    }

    function openCommercio($proc, $tipo = "procedimento") {
        $proges_rec = $this->praLib->GetProges($this->currGesnum);
        $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA'], 'codice');
        if ($anaspa_rec["SPAENTECOMM"]) {
            if ($tipo == "procedimento") {
                $retidPwd = "VEDICOMM";
            } elseif ($tipo == "evento") {
                $retidPwd = "VEDICOMMEVT";
            }
            $wcoLib = new wcoLib($anaspa_rec["SPAENTECOMM"]);
            $comlic_rec = $wcoLib->getComlic($proc);
            //
            $profiloUtente = proSoggetto::getProfileFromIdUtente();
            $ananom_rec = $this->praLib->GetAnanom($profiloUtente['COD_ANANOM']);
            $arrayMeta = unserialize($ananom_rec['NOMMETA']);
            $utenteAggr = $this->praLib->GetUtenteAggregato($arrayMeta['Utenti'], $proges_rec['GESSPA']);
            //
            $ieDomain = $anaspa_rec["SPAENTECOMM"];
            $ieMessage = 'Apertura definizione procedimento on-line. Ok per continuare.';
            $ieUrl = $_SERVER['HTTP_REFERER'];
            if ($this->remoteToken) {
                $retToken = $this->praLib->CheckItaEngineContextToken($this->remoteToken, $ieDomain);
                if (!$retToken) {
                    $html = "<span style=\"font-size:1.2em;\">La password richiesta, fa riferimento all'utente <b>$utenteAggr</b> nell'applicativo Commercio.</span>";
                    $this->praLib->GetMsgInputPassword($this->nameForm, "Vedi Commercio Commercio", $retidPwd, $html);
                } else {
                    Out::openIEWindow(array(
                        "ieurl" => $ieUrl,
                        "ietoken" => $this->remoteToken,
                        "iedomain" => $ieDomain,
                        "ieOpenMessage" => $ieMessage
                            ), array(
                        "model" => "menDirect",
                        "menu" => "CO_HID",
                        "prog" => "CO_SUAPEXT",
                        "modo" => "vediCommercio",
                        "tipo" => $tipo,
                        "pratica" => $this->currGesnum,
                        "ditta" => App::$utente->getKey('ditta'),
                        "accessreturn" => "",
                    ));
                }
            } else {
                $html = "<span style=\"font-size:1.2em;\">La password richiesta, fa riferimento all'utente <b>$utenteAggr</b> nell'applicativo Commercio.</span>";
                $this->praLib->GetMsgInputPassword($this->nameForm, "Vedi Commercio Commercio", "VEDICOMM", $html);
            }
        } else {
            $comlic_rec = $this->wcoLib->getComlic($proc);
            $model = 'wcoComgen';
            itaLib::openForm($model);
            include_once ITA_BASE_PATH . '/apps/Commercio/wcoComgen.php';
            $wcoComgen = new wcoComgen();
            $wcoComgen->CreaCombo();
            $wcoComgen->Dettaglio($comlic_rec['ROWID']);
        }
        return true;
    }

    public function caricaTabPratica($arrayStatiTab) {

        foreach ($arrayStatiTab as $panel) {
            switch ($panel['Stato']) {
                case "Show":
                    Out::showTab($this->nameForm . "_" . $panel['Id']);
                    break;
                case "Hide":
                    Out::hideTab($this->nameForm . "_" . $panel['Id']);
                    if ($panel['Id'] == 'panePassi'){
                        Out::hide($this->nameForm . "_ImportPassi");
                    }
                    break;
                case "Add":
                    $generator = new itaGenerator();
                    $retHtml = $generator->getModelHTML(basename($panel['FileXml'], ".xml"), false, $this->nameForm, false);
                    Out::tabAdd($this->nameForm . '_tabProcedimento', '', $retHtml);
                    break;
                case "Remove":
                    Out::tabRemove($this->nameForm . '_tabProcedimento', $this->nameForm . "_" . basename($panel['FileXml'], ".xml"));
                    if ($panel['Id'] == 'panePassi'){
                        Out::hide($this->nameForm . "_ImportPassi");
                    }
                    break;
                default:
                    break;
            }
        }

        /*
         * Selezione della prima Tab accesa
         */
        $first = false;
        foreach ($arrayStatiTab as $panel) {
            if ($panel['Stato'] == "Show" || $panel['Stato'] == "Add") {
                $first = true;
                break;
            }
        }
        if ($first == true) {
            Out::tabSelect($this->nameForm . '_tabProcedimento', $this->nameForm . "_" . $panel['Id']);
        }

        foreach ($arrayStatiTab as $panel) {
            switch ($panel['Flag']) {
                case "On":
                    Out::show($this->nameForm . "_" . $panel['IdFlag'] . "_field");
                    break;
                case "Off":
                    Out::hide($this->nameForm . "_" . $panel['IdFlag'] . "_field");
                    break;
            }
        }
    }
    
    
}
