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
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPratica.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praPerms.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRuolo.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praNote.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praGobidManager.class.php';
include_once ITA_BASE_PATH . '/apps/Anagrafe/anaRic.class.php';
include_once ITA_BASE_PATH . '/apps/Anagrafe/anaLib.class.php';
include_once ITA_BASE_PATH . '/apps/Commercio/wcoRic.class.php';
include_once ITA_BASE_PATH . '/apps/AlboPretorio/albRic.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php';
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
include_once ITA_BASE_PATH . '/apps/Gafiere/gfmRic.class.php';
include_once ITA_BASE_PATH . '/apps/ZTL/ztlRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibGfm.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibZTL.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFunzionePassi.class.php';
include_once(ITA_LIB_PATH . '/zip/itaZip.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
include_once(ITA_LIB_PATH . '/QXml/QXml.class.php');
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibEstrazione.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praPasso.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praAssegnaPraticaSimple.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFormHelper.class.php';

include_once ITA_BASE_PATH . '/apps/Pratiche/praLibDatiWorkFlow.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibElaborazioneDati.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praSubPassoUnico.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPasso.class.php';

function praGest2() {
    $praGest = new praGest2();
    $praGest->parseEvent();
    return;
}

class praGest2 extends itaModel {

    public $PRAM_DB;
    public $PROT_DB;
    public $utiEnte;
    public $ITALWEB_DB;
    public $nameForm = "praGest2";
    public $divGes = "praGest2_divGestione";
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
    public $allegatiComunica = array();
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
    public $proric_rec;
    public $pranumSel;
    public $idCorrispondente;
    public $datiRubricaWS = array();
    public $emlComunica;
    public $progesSel;
    public $datiFromWSProtocollo;
    public $codiceImm;
    /* @var $this->noteManager proNoteManager */
    public $noteManager;
    public $praReadOnly;
    public $sha2View;
    public $proctipaut_rec = array();
    public $rowidFiera;
    public $flagAssegnazioni;
    public $flagPagamenti;
    //public $profilo;
    public $remoteToken;
    public $allegatiPrtSel;
    public $anadesProt = array();
    public $ita_ext_cred = array();
    public $praLibEstrazione;
    public $Propas;
    public $Propas_key;
    public $praAccorpati;
    public $praCompDatiAggiuntiviFormname;
    public $searchMode = false;
    public $praPassiSel;
    public $fascicoliSel = array();
    public $chiusuraFascicoli;
    public $praCompPassoGestFormname;
    public $datiWf;
    public $proges_rec;
    public $divAttivati = array();

    function __construct() {
        parent::__construct();
    }

    function postInstance() {
        parent::postInstance();


        $this->praCompPassoGestFormname = App::$utente->getKey($this->nameForm . '_praCompPassoGestFormname');
        $this->datiWf = unserialize(App::$utente->getKey($this->nameForm . '_datiWf'));
        $this->proges_rec = App::$utente->getKey($this->nameForm . '_proges_rec');


        $this->origForm = $this->nameFormOrig;
        $this->nameModel = substr($this->nameForm, strpos($this->nameForm, '_') + 1);

        $this->divGes = $this->nameForm . "_divGestione";
        $this->divAttivati = App::$utente->getKey($this->nameForm . '_divAttivati');

        try {
            $this->utiEnte = new utiEnte();
            $this->praLib = new praLib();
            $this->proLib = new proLib();
            $this->praLibAllegati = new praLibAllegati();
            $this->praLibPratica = praLibPratica::getInstance();
            $this->praPerms = new praPerms();
            $this->accLib = new accLib();
            $this->praLibEstrazione = new praLibEstrazione();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->ITALWEB_DB = $this->utiEnte->getITALWEB_DB();
            $Filent_Rec_TabAss = $this->praLib->GetFilent(20);
            if ($Filent_Rec_TabAss['FILVAL'] == 1) {
                $this->flagAssegnazioni = true;
            }
            if ($Filent_Rec_TabAss['FILDE1'] == 1) {
                $this->flagPagamenti = true;
            }
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }


//        $this->proRic = new proRic();
//        $this->proLibSerie = new proLibSerie();
//        $this->praPassi = App::$utente->getKey($this->nameForm . '_praPassi');
//        $this->praPagamenti = App::$utente->getKey($this->nameForm . '_praPagamenti');
//        $this->praPassiAssegnazione = App::$utente->getKey($this->nameForm . '_praPassiAssegnazione');
//        $this->praPassiPerAllegati = App::$utente->getKey($this->nameForm . '_praPassiPerAllegati');
//        $this->praAlle = App::$utente->getKey($this->nameForm . '_praAlle');
//        $this->praAlleSha2 = App::$utente->getKey($this->nameForm . '_praAlleSha2');
//        $this->currGesnum = App::$utente->getKey($this->nameForm . '_currGesnum');
//        $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
//        $this->dataRegAppoggio = App::$utente->getKey($this->nameForm . '_dataRegAppoggio');
//        $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
//        $this->returnId = App::$utente->getKey($this->nameForm . '_returnId');
//        $this->page = App::$utente->getKey($this->nameForm . '_page');
//        $this->insertTo = App::$utente->getKey($this->nameForm . '_insertTo');
//        $this->praDati = App::$utente->getKey($this->nameForm . '_praDati');
//        $this->praDatiPratica = App::$utente->getKey($this->nameForm . '_praDatiPratica');
//        $this->datiFiltrati = App::$utente->getKey($this->nameForm . '_datiFiltrati');
//        $this->allegatiComunica = App::$utente->getKey($this->nameForm . '_allegatiComunica');
//        $this->praComunicazioni = App::$utente->getKey($this->nameForm . '_praComunicazioni');
//        $this->currAllegato = App::$utente->getKey($this->nameForm . '_currAllegato');
//        $this->proric_rec = App::$utente->getKey($this->nameForm . '_proric_rec');
//        $this->pranumSel = App::$utente->getKey($this->nameForm . '_pranumSel');
//        $this->idCorrispondente = App::$utente->getKey($this->nameForm . '_idCorrispondente');
//        $this->datiRubricaWS = App::$utente->getKey($this->nameForm . '_datiRubricaWS');
//        $this->emlComunica = App::$utente->getKey($this->nameForm . '_emlComunica');
//        $this->progesSel = App::$utente->getKey($this->nameForm . '_progesSel');
//        $this->datiFromWSProtocollo = App::$utente->getKey($this->nameForm . '_datiFromWSProtocollo');
//        $this->codiceImm = App::$utente->getKey($this->nameForm . '_codiceImm');
//        $this->praReadOnly = App::$utente->getKey($this->nameForm . '_praReadOnly');
//        $this->sha2View = App::$utente->getKey($this->nameForm . '_sha2View');
//        $this->proctipaut_rec = App::$utente->getKey($this->nameForm . '_proctipaut_rec');
//        $this->rowidFiera = App::$utente->getKey($this->nameForm . '_rowidFiera');
//        $this->remoteToken = App::$utente->getKey($this->nameForm . '_remoteToken');
//        $this->allegatiPrtSel = App::$utente->getKey($this->nameForm . '_allegatiPrtSel');
//        $this->anadesProt = App::$utente->getKey($this->nameForm . '_anadesProt');
//        $this->Propas = App::$utente->getKey($this->nameForm . '_Propas');
//        $this->Propas_key = App::$utente->getKey($this->nameForm . '_Propas_key');
//        $data = App::$utente->getKey('DataLavoro');
//        $this->noteManager = unserialize(App::$utente->getKey($this->nameForm . '_noteManager'));
//        $this->ita_ext_cred = unserialize(App::$utente->getKey($this->nameForm . '_ita_ext_cred'));
//        $this->praAccorpati = App::$utente->getKey($this->nameForm . '_praAccorpati');
//        $this->praCompDatiAggiuntiviFormname = App::$utente->getKey($this->nameForm . '_praCompDatiAggiuntiviFormname');
//        $this->searchMode = App::$utente->getKey($this->nameForm . '_searchMode');
//        $this->praPassiSel = App::$utente->getKey($this->nameForm . '_praPassiSel');
//        $this->fascicoliSel = App::$utente->getKey($this->nameForm . '_fascicoliSel');
//        $this->chiusuraFascicoli = App::$utente->getKey($this->nameForm . '_chiusuraFascicoli');

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
            App::$utente->setKey($this->nameForm . '_praCompPassoGestFormname', $this->praCompPassoGestFormname);
            App::$utente->setKey($this->nameForm . '_datiWf', serialize($this->datiWf));

            App::$utente->setKey($this->nameForm . '_proges_rec', $this->proges_rec);
            App::$utente->setKey($this->nameForm . '_divAttivati', $this->divAttivati);

//            App::$utente->setKey($this->nameForm . '_praPassi', $this->praPassi);
//            App::$utente->setKey($this->nameForm . '_praPagamenti', $this->praPagamenti);
//            App::$utente->setKey($this->nameForm . '_praPassiAssegnazione', $this->praPassiAssegnazione);
//            App::$utente->setKey($this->nameForm . '_praPassiPerAllegati', $this->praPassiPerAllegati);
//            App::$utente->setKey($this->nameForm . '_praAlle', $this->praAlle);
//            App::$utente->setKey($this->nameForm . '_praAlleSha2', $this->praAlleSha2);
//            App::$utente->setKey($this->nameForm . '_currGesnum', $this->currGesnum);
//            App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
//            App::$utente->setKey($this->nameForm . '_dataRegAppoggio', $this->dataRegAppoggio);
//            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
//            App::$utente->setKey($this->nameForm . '_returnId', $this->returnId);
//            App::$utente->setKey($this->nameForm . '_insertTo', $this->insertTo);
//            App::$utente->setKey($this->nameForm . '_praDati', $this->praDati);
//            App::$utente->setKey($this->nameForm . '_praDatiPratica', $this->praDatiPratica);
//            App::$utente->setKey($this->nameForm . '_datiFiltrati', $this->datiFiltrati);
//            App::$utente->setKey($this->nameForm . '_allegatiComunica', $this->allegatiComunica);
//            App::$utente->setKey($this->nameForm . '_praComunicazioni', $this->praComunicazioni);
//            App::$utente->setKey($this->nameForm . '_currAllegato', $this->currAllegato);
//            App::$utente->setKey($this->nameForm . '_proric_rec', $this->proric_rec);
//            App::$utente->setKey($this->nameForm . '_pranumSel', $this->pranumSel);
//            App::$utente->setKey($this->nameForm . '_idCorrispondente', $this->idCorrispondente);
//            App::$utente->setKey($this->nameForm . '_datiRubricaWS', $this->datiRubricaWS);
//            App::$utente->setKey($this->nameForm . '_emlComunica', $this->emlComunica);
//            App::$utente->setKey($this->nameForm . '_progesSel', $this->progesSel);
//            App::$utente->setKey($this->nameForm . '_datiFromWSProtocollo', $this->datiFromWSProtocollo);
//            App::$utente->setKey($this->nameForm . '_codiceImm', $this->codiceImm);
//            App::$utente->setKey($this->nameForm . '_noteManager', serialize($this->noteManager));
//            App::$utente->setKey($this->nameForm . '_praReadOnly', $this->praReadOnly);
//            App::$utente->setKey($this->nameForm . '_sha2View', $this->sha2View);
//            App::$utente->setKey($this->nameForm . '_proctipaut_rec', $this->proctipaut_rec);
//            App::$utente->setKey($this->nameForm . '_rowidFiera', $this->rowidFiera);
//            App::$utente->setKey($this->nameForm . '_remoteToken', $this->remoteToken);
//            App::$utente->setKey($this->nameForm . '_allegatiPrtSel', $this->allegatiPrtSel);
//            App::$utente->setKey($this->nameForm . '_anadesProt', $this->anadesProt);
//            App::$utente->setKey($this->nameForm . '_Propas', $this->Propas);
//            App::$utente->setKey($this->nameForm . '_Propas_key', $this->Propas_key);
//            App::$utente->setKey($this->nameForm . '_praAccorpati', $this->praAccorpati);
//            App::$utente->setKey($this->nameForm . '_ita_ext_cred', serialize($this->ita_ext_cred));
//            App::$utente->setKey($this->nameForm . '_praCompDatiAggiuntiviFormname', $this->praCompDatiAggiuntiviFormname);
//            App::$utente->setKey($this->nameForm . '_searchMode', $this->searchMode);
//            App::$utente->setKey($this->nameForm . '_praPassiSel', $this->praPassiSel);
//            App::$utente->setKey($this->nameForm . '_fascicoliSel', $this->fascicoliSel);
//            App::$utente->setKey($this->nameForm . '_chiusuraFascicoli', $this->chiusuraFascicoli);
        }
    }

    public function setModelParam($params) {
        $params = unserialize($params);
        foreach ($params as $func => $args) {
            call_user_func_array(array($this->nameForm, $func), $args);
        }
    }

    public function parseEvent() {        
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                try {
                    $this->praLib = new praLib();
                } catch (Exception $e) {
                    Out::msgStop("Errore", $e->getMessage());
                }

                /*
                 * Valorizzo proges_rec
                 */
                if ($_POST['rowidDettaglio']) {
                    $this->proges_rec = $this->praLib->GetProges($_POST['rowidDettaglio'], 'rowid');
                }

                /*
                 * Controllo esistenza record PROGES
                 */
                if (!$this->proges_rec) {
                    Out::msgStop("Attenzione", "Record Fascicolo non disponibile");
                    break;
                }

                //$this->proges_rec = $_POST['proges_rec'];
                //$this->chiusuraFascicoli = $_POST['chiusuraFascicoli'];
                $this->inizializzaForm();

                //Out::hide($this->nameForm . "_divContenitoreElencoPassi");

                $this->loadModelConfig();
                $panelsConfig = $this->getCustomConfig('PANELS');
                foreach ($panelsConfig as $config => $value) {
                    switch ($config) {
                        case 'ORDER':
                            Out::setSortableOrder($value);
                            break;

                        default:
                            if (isset($value['EXPANDED']) && $value['EXPANDED'] == false) {
                                Out::boxCollapse($config);
                            } else {
                                Out::boxExpand($config);

                                if ($value['HEIGHT']) {
                                    Out::css($config, 'height', $value['HEIGHT']);
                                }
                            }

                            if (isset($value['VISIBLE']) && $value['VISIBLE'] == '0') {
                                Out::css($config, 'display', 'none');
                            }
                            break;
                    }
                }

                break;
            case 'dbClickRow':
                break;

            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_confermaConfiguraPannelli':
                        $confPannelli = $_POST[$this->nameForm . '_CONF'];
                        foreach ($confPannelli as $id => $visible) {
                            $this->setCustomConfig("PANELS/{$this->nameForm}_{$id}/VISIBLE", $visible);

                            Out::css($this->nameForm . '_' . $id, 'display', $visible == '0' ? 'none' : 'flex');
                        }

                        $this->saveModelConfig();
                        break;
                }
                break;

            case 'afterSaveCell':
                break;
            case 'onChange':
                break;
            case 'onBlur':
                break;
            case 'returnFromNavigatore':

                $arrayNavigatore = $this->formData;
                if ($arrayNavigatore['eventoNavigatore'] == 'configuraPannelli') {
                    $panelsConfig = $this->getCustomConfig('PANELS');
                    $checkDiagramma = $checkPassi = $checkDettaglio = array();

                    if ($panelsConfig[$this->nameForm . '_divWrapContenitoreDiagramma']['VISIBLE'] != '0') {
                        $checkDiagramma['checked'] = 'checked';
                    }

                    if ($panelsConfig[$this->nameForm . '_divWrapContenitoreElencoPassi']['VISIBLE'] != '0') {
                        $checkPassi['checked'] = 'checked';
                    }

                    if ($panelsConfig[$this->nameForm . '_divWrapContenitorePasso']['VISIBLE'] != '0') {
                        $checkDettaglio['checked'] = 'checked';
                    }

                    $inputFields = array(
                        array_merge(array(
                            'label' => array('value' => 'Diagramma', 'style' => 'width: 100px;'),
                            'id' => $this->nameForm . '_CONF[divWrapContenitoreDiagramma]',
                            'name' => $this->nameForm . '_CONF[divWrapContenitoreDiagramma]',
                            'type' => 'checkbox'
                                ), $checkDiagramma),
                        array_merge(array(
                            'label' => array('value' => 'Passi', 'style' => 'width: 100px;'),
                            'id' => $this->nameForm . '_CONF[divWrapContenitoreElencoPassi]',
                            'name' => $this->nameForm . '_CONF[divWrapContenitoreElencoPassi]',
                            'type' => 'checkbox'
                                ), $checkPassi),
                        array_merge(array(
                            'label' => array('value' => 'Dettaglio', 'style' => 'width: 100px;'),
                            'id' => $this->nameForm . '_CONF[divWrapContenitorePasso]',
                            'name' => $this->nameForm . '_CONF[divWrapContenitorePasso]',
                            'type' => 'checkbox'
                                ), $checkDettaglio)
                    );

                    Out::msgInput('Configura Pannelli', $inputFields, array(
                        "Conferma" => array(
                            'id' => $this->nameForm . '_confermaConfiguraPannelli',
                            'model' => $this->nameForm
                        ),
                        'Annulla' => array(
                            'id' => $this->nameForm . '_annullaConfiguraPannelli',
                            'model' => $this->nameForm
                        )
                            ), $this->nameForm);
                    break;
                }



                $passoCorrente = $this->datiWf->getPassoCorrente();

                // Vedere quale Div dati è attivato ()
                $praLibPasso = new praLibPasso;
                $funzionePasso = $praLibPasso->getFunzionePassoBO($passoCorrente);

                $model = 'praSubPassoUnico';

                if ($funzionePasso['FUNZIONE'] == praFunzionePassi::FUN_GEST_DIP) {
                    $model = $funzionePasso['DATA']['CLASSE'];
                }

                $formnameGestionePasso = $this->praCompPassoGestFormname[$model];
                $praCompPassoGest = itaModel::getInstance($model, $formnameGestionePasso);
                //$praCompPassoGest = itaModel::getInstance('praSubPassoUnico', $this->praCompPassoGestFormname['praCompDatiAggiuntiviForm']);
                //$praCompPassoGest->returnToParent(false);
                // Il controllo dati inseriti e il salvataggio si fà solo se si preme 'Avanti'
                if ($arrayNavigatore['eventoNavigatore'] == 'avanti') {
                    if (!$praCompPassoGest->aggiornaDati()) {
                        break;
                    }
                }

                //Prima di gestire la navigazione, bisogna rileggere il dizionario con le eventuali
                // modifica apportate.
                $this->datiWf->creaDizionari();

                //Out::msgInfo("Navigatore", print_r($arrayNavigatore, true));

                $propakNuovo = $praCompPassoGest->navigazione($this->datiWf, $arrayNavigatore['eventoNavigatore']);

                //Out::msgInfo("Passo Destinazione", $propakNuovo);

                if ($arrayNavigatore['eventoNavigatore'] == 'avanti') {
                    if (!$this->salvaPassiFatti($propakNuovo)) {
                        break;
                    }
                }

                //Imposto il nuovo passo come quello corrente
                $this->datiWf->setPassoCorrente($propakNuovo);

                $praCompPassoGest = null;

                // Sistema il posizionamento degli oggetti
                $this->inizializzaOggetti();

                break;
            case 'returnFromDiagramma':
            case 'returnFromElencoPassi':
                $propak = $this->formData;

                //Imposto il nuovo passo come quello corrente
                $this->datiWf->setPassoCorrente($propak);

//                $praSubPassoUnico = itaModel::getInstance('praSubPassoUnico', $this->praCompPassoGestFormname['praCompDatiAggiuntiviForm']);
//                $praSubPassoUnico->dettaglio($propak);


                $this->inizializzaOggetti();

                break;

            case 'returnConfiguraPannelli':
                $panelsConfig = $this->getCustomConfig('PANELS');
                $checkDiagramma = $checkPassi = $checkDettaglio = array();

                if ($panelsConfig[$this->nameForm . '_divWrapContenitoreDiagramma']['VISIBLE'] != '0') {
                    $checkDiagramma['checked'] = 'checked';
                }

                if ($panelsConfig[$this->nameForm . '_divWrapContenitoreElencoPassi']['VISIBLE'] != '0') {
                    $checkPassi['checked'] = 'checked';
                }

                if ($panelsConfig[$this->nameForm . '_divWrapContenitorePasso']['VISIBLE'] != '0') {
                    $checkDettaglio['checked'] = 'checked';
                }

                $inputFields = array(
                    array_merge(array(
                        'label' => array('value' => 'Diagramma', 'style' => 'width: 100px;'),
                        'id' => $this->nameForm . '_CONF[divWrapContenitoreDiagramma]',
                        'name' => $this->nameForm . '_CONF[divWrapContenitoreDiagramma]',
                        'type' => 'checkbox'
                            ), $checkDiagramma),
                    array_merge(array(
                        'label' => array('value' => 'Passi', 'style' => 'width: 100px;'),
                        'id' => $this->nameForm . '_CONF[divWrapContenitoreElencoPassi]',
                        'name' => $this->nameForm . '_CONF[divWrapContenitoreElencoPassi]',
                        'type' => 'checkbox'
                            ), $checkPassi),
                    array_merge(array(
                        'label' => array('value' => 'Dettaglio', 'style' => 'width: 100px;'),
                        'id' => $this->nameForm . '_CONF[divWrapContenitorePasso]',
                        'name' => $this->nameForm . '_CONF[divWrapContenitorePasso]',
                        'type' => 'checkbox'
                            ), $checkDettaglio)
                );

                Out::msgInput('Configura Pannelli', $inputFields, array(
                    "Conferma" => array(
                        'id' => $this->nameForm . '_confermaConfiguraPannelli',
                        'model' => $this->nameForm
                    ),
                    'Annulla' => array(
                        'id' => $this->nameForm . '_annullaConfiguraPannelli',
                        'model' => $this->nameForm
                    )
                        ), $this->nameForm);
                break;

            case 'onExpand':
                $this->setCustomConfig("PANELS/{$this->elementId}/EXPANDED", true);
                $this->saveModelConfig();
                break;

            case 'onCollapse':
                $this->setCustomConfig("PANELS/{$this->elementId}/EXPANDED", false);
                $this->saveModelConfig();
                break;

            case 'onResize':
                $this->setCustomConfig("PANELS/{$this->elementId}/HEIGHT", $_POST['resizeHeight']);
                $this->saveModelConfig();
                break;

            case 'sortStop':
                $this->setCustomConfig("PANELS/ORDER", $_POST['order']);
                $this->saveModelConfig();
                break;
        }
    }

    public function close() {

        App::$utente->removeKey($this->nameForm . '_praCompPassoGestFormname');
        App::$utente->removeKey($this->nameForm . '_datiWf');

        App::$utente->removeKey($this->nameForm . '_proges_rec');
        App::$utente->removeKey($this->nameForm . '_divAttivati');

//        App::$utente->removeKey($this->nameForm . '_praPassi');
//        App::$utente->removeKey($this->nameForm . '_praPagamenti');
//        App::$utente->removeKey($this->nameForm . '_praPassiAssegnazione');
//        App::$utente->removeKey($this->nameForm . '_praPassiPerAllegati');
//        App::$utente->removeKey($this->nameForm . '_praAlle');
//        App::$utente->removeKey($this->nameForm . '_praAlleSha2');
//        App::$utente->removeKey($this->nameForm . '_currGesnum');
//        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
//        App::$utente->removeKey($this->nameForm . '_dataRegAppoggio');
//        App::$utente->removeKey($this->nameForm . '_returnId');
//        App::$utente->removeKey($this->nameForm . '_returnModel');
//        App::$utente->removeKey($this->nameForm . '_page');
//        App::$utente->removeKey($this->nameForm . '_insertTo');
//        App::$utente->removeKey($this->nameForm . '_passi');
//        App::$utente->removeKey($this->nameForm . '_praDati');
//        App::$utente->removeKey($this->nameForm . '_praDatiPratica');
//        App::$utente->removeKey($this->nameForm . '_datiFiltrati');
//        App::$utente->removeKey($this->nameForm . '_allegatiComunica');
//        App::$utente->removeKey($this->nameForm . '_praComunicazioni');
//        App::$utente->removeKey($this->nameForm . '_currAllegato');
//        App::$utente->removeKey($this->nameForm . '_proric_rec');
//        App::$utente->removeKey($this->nameForm . '_pranumSel');
//        App::$utente->removeKey($this->nameForm . '_emlComunica');
//        App::$utente->removeKey($this->nameForm . '_progesSel');
//        App::$utente->removeKey($this->nameForm . '_datiFromWSProtocollo');
//        App::$utente->removeKey($this->nameForm . '_codiceImm');
//        App::$utente->removeKey($this->nameForm . '_noteManager');
//        App::$utente->removeKey($this->nameForm . '_praReadOnly');
//        App::$utente->removeKey($this->nameForm . '_sha2View');
//        App::$utente->removeKey($this->nameForm . '_proctipaut_rec');
//        App::$utente->removeKey($this->nameForm . '_rowidFiera');
//        App::$utente->removeKey($this->nameForm . '_remoteToken');
//        App::$utente->removeKey($this->nameForm . '_allegatiPrtSel');
//        App::$utente->removeKey($this->nameForm . '_anadesProt');
//        App::$utente->removeKey($this->nameForm . '_ita_ext_cred');
//        App::$utente->removeKey($this->nameForm . '_Propas');
//        App::$utente->removeKey($this->nameForm . '_Propas_key');
//        App::$utente->removeKey($this->nameForm . '_praAccorpati');
//        App::$utente->removeKey($this->nameForm . '_praCompDatiAggiuntiviFormname');
//        App::$utente->removeKey($this->nameForm . '_searchMode');
//        App::$utente->removeKey($this->nameForm . '_praPassiSel');
//        App::$utente->removeKey($this->nameForm . '_fascicoliSel');
//        App::$utente->removeKey($this->nameForm . '_chiusuraFascicoli');
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
            $objModel->setEvent($this->returnEvent ? : 'dbClickRow');
            $objModel->parseEvent();
            Out::closeDialog($this->nameForm);
        } else {
            Out::show('menuapp');
        }
    }

    private function inizializzaForm() {

        $this->praCompPassoGestFormname = array();

        /* @var $this->$datiWf praLibDatiWorkFlow */
        $this->datiWf = null;
        $this->datiWf = new praLibDatiWorkFlow($this->proges_rec['GESNUM']);

        // Diagramma (WorkFlow)
        Out::html($this->nameForm . '_divContenitoreDiagramma', '');

        /* @var $praCompDiagramma praCompDiagramma */
        $praCompDiagramma = itaFormHelper::innerForm('praCompDiagramma', $this->nameForm . '_divContenitoreDiagramma');
        $praCompDiagramma->setEvent('openform');
        $praCompDiagramma->setReturnModel($this->nameForm);
        $praCompDiagramma->setReturnEvent('returnFromDiagramma');
        $praCompDiagramma->setDatiWorkflow($this->datiWf);
        $praCompDiagramma->parseEvent();

        $this->praCompPassoGestFormname['praCompDiagramma'] = $praCompDiagramma->getNameForm();

        // Elenco Passi
        Out::html($this->nameForm . '_divContenitoreElencoPassi', '');

        /* @var $praCompElencoPassi praCompElencoPassi */
        $praCompElencoPassi = itaFormHelper::innerForm('praCompElencoPassi', $this->nameForm . '_divContenitoreElencoPassi');
        $praCompElencoPassi->setEvent('openform');
        $praCompElencoPassi->setReturnModel($this->nameForm);
        $praCompElencoPassi->setReturnEvent('returnFromElencoPassi');
        $praCompElencoPassi->setReturnId('');
        $praCompElencoPassi->setDatiWorkflow($this->datiWf);
        $praCompElencoPassi->parseEvent();

        $this->praCompPassoGestFormname['praCompElencoPassi'] = $praCompElencoPassi->getNameForm();
        $praCompElencoPassi = null;

        //Navigatore
        Out::html($this->nameForm . '_divContenitoreNavigatore', '');

        /* @var $praCompNavigatore praCompNavigatore */
        $praCompNavigatore = itaFormHelper::innerForm('praCompNavigatore', $this->nameForm . '_divContenitoreNavigatore');
        $praCompNavigatore->setEvent('openform');
        $praCompNavigatore->setReturnModel($this->nameForm);
        $praCompNavigatore->setReturnEvent('returnFromNavigatore');
        $praCompNavigatore->setReturnId('');
        $praCompNavigatore->inizializza($this->datiWf);
        $praCompNavigatore->parseEvent();

        $this->praCompPassoGestFormname['praCompNavigatore'] = $praCompNavigatore->getNameForm();
        $praCompNavigatore = null;

        //Disegna il Form associato al passo corrente e sistemata tutti i 
        //subform


        $model = "praSubPassoUnico";
        /* @var $praSubPassoUnico praSubPassoUnico */
        $praSubPassoUnico = itaFormHelper::innerForm($model, $this->nameForm . '_divContenitorePasso');
        $this->praCompPassoGestFormname['praSubPassoUnico'] = $praSubPassoUnico->getNameForm();
        $praSubPassoUnico->setEvent("openform");
        $praSubPassoUnico->setReturnModel($this->nameForm);
        $praSubPassoUnico->setReturnEvent('returnFromPassoUnico');
        $praSubPassoUnico->setReturnId('');
        $praSubPassoUnico->setPassoCorrente($this->datiWf->getPassoCorrente());

        $praSubPassoUnico->parseEvent();
        $praSubPassoUnico = null;


        //Scorrimento PAssi
        $praLibPasso = new praLibPasso;
        $passoCorrente = $this->datiWf->getPassoCorrente();
        $this->divAttivati = array('_divContenitorePasso', '_divContenitorePersonalizzato');

        foreach ($this->datiWf->getPassi() as $passo) {
            $funzionePasso = $praLibPasso->getFunzionePassoBO($passo);


            if ($funzionePasso['FUNZIONE'] == praFunzionePassi::FUN_GEST_DIP) {

                $model = $funzionePasso['DATA']['CLASSE'];
                if ($funzionePasso['DATA']['CARICAMENTO'] == 'permanente') {

                    $nameDiv = '_divContenitore' . $funzionePasso['DATA']['CLASSE'];
                    Out::html($this->nameForm . '_divWrapContenitorePasso_boxBody', '<div id="' . $this->nameForm . $nameDiv . '" style="height: 100%; overflow: auto;"></div>', 'append');

                    /* @var $praGest praGest */
                    $praGest = itaFormHelper::innerForm($model, $this->nameForm . $nameDiv);
                    $this->praCompPassoGestFormname[$model] = $praGest->getNameForm();
                    $praGest->setEvent("openform");
                    $praGest->setReturnModel($this->nameForm);
                    $praGest->setReturnEvent('returnFrom' . $nameDiv);
                    $praGest->setPropak($passoCorrente['PROPAK']);
                    $praGest->setReturnId('');
                    //$praGest->setPassoCorrente($this->datiWf->getPassoCorrente());

                    $praGest->parseEvent();
                    $praGest = null;

                    $this->divAttivati[] = $nameDiv;
                }
            }
        }


        // Lettura funzioe tipoligia passo




        $this->inizializzaOggetti();
    }

    private function inizializzaOggetti() {

        //Nasconde i Div cheerano attivati
        foreach ($this->divAttivati as $divAttivo) {
            Out::hide($this->nameForm . $divAttivo);
        }


        $this->datiWf->creaDizionari();


        $passoCorrente = $this->datiWf->getPassoCorrente();


        $praGest = itaModel::getInstance('praGest', $this->praCompPassoGestFormname['praGestForm']);


        $praLibPasso = new praLibPasso;
        $funzionePasso = $praLibPasso->getFunzionePassoBO($passoCorrente);

        $formGestionePasso = 'praSubPassoUnico';
        $formnameGestionePasso = $this->praCompPassoGestFormname['praSubPassoUnico'];

        if ($funzionePasso['FUNZIONE'] == praFunzionePassi::FUN_GEST_DIP) {
            $model = $funzionePasso['DATA']['CLASSE'];

            if ($funzionePasso['DATA']['CARICAMENTO'] == 'permanente') {
                $formGestionePasso = $model;
                $formnameGestionePasso = $this->praCompPassoGestFormname[$model];
                $nameDiv = '_divContenitore' . $model;
            } else {
                $nameDiv = '_divContenitorePersonalizzato';

                /* @var $praGest praGest */
                $praGest = itaFormHelper::innerForm($model, $this->nameForm . $nameDiv);
                $this->praCompPassoGestFormname[$model] = $praGest->getNameForm();
                $praGest->setEvent("openform");
                $praGest->setReturnModel($this->nameForm);
                $praGest->setReturnEvent('returnFrom' . $nameDiv);
                $praGest->setPropak($passoCorrente['PROPAK']);
                $praGest->setReturnId('');
                //$praGest->setPassoCorrente($this->datiWf->getPassoCorrente());

                $praGest->parseEvent();
                $praGest = null;

                $formGestionePasso = $model;
                $formnameGestionePasso = $this->praCompPassoGestFormname[$model];
            }
        } else {
            $nameDiv = '_divContenitorePasso';
//            Out::show($this->nameForm . '_divContenitorePasso');
        }
        Out::show($this->nameForm . $nameDiv);

        $praSubPassoUnico = itaModel::getInstance($formGestionePasso, $formnameGestionePasso);
        $praSubPassoUnico->openGestione($passoCorrente['PROPAK']);



        /* VECCHIO - Vecchia modalità per disegnare le domande 
          if ($passoCorrente['PROQST'] == 0) {
          // Raccolta Dati
          $this->disegnaDomandaMultipla($this->datiWf->getPassoCorrente());
          } else if ($passoCorrente['PROQST'] == 1) {
          // Domanda Semplice
          $this->disegnaDomandaSemplice($this->datiWf->getPassoCorrente());
          }
          if ($passoCorrente['PROQST'] == 2) {
          // Domanda Muiltipla
          $this->disegnaDomandaMultipla($this->datiWf->getPassoCorrente());
          }
         */

        //Posiziona la griglia dei passi sul record corrente
        $praCompElencoPassi = itaModel::getInstance('praCompElencoPassi', $this->praCompPassoGestFormname['praCompElencoPassi']);
        $praCompElencoPassi->setDatiWorkflow($this->datiWf);
        $praCompElencoPassi->refreshSelection();

        $praCompNavigatore = itaModel::getInstance('praCompNavigatore', $this->praCompPassoGestFormname['praCompNavigatore']);
        $praCompNavigatore->inizializza($this->datiWf);

        $praCompDiagramma = itaModel::getInstance('praCompDiagramma', $this->praCompPassoGestFormname['praCompDiagramma']);
        $praCompDiagramma->setDatiWorkflow($this->datiWf);
        $praCompDiagramma->setDiagramColors();
        $praCompDiagramma->refreshSelection();
        $praCompDiagramma->setGestioneGruppi();
        
        Out::valori($this->proges_rec, $this->nameForm . '_PROGES');


        $proLibSerie = new proLibSerie();
        $serie_rec = $proLibSerie->GetSerie($this->proges_rec['SERIECODICE'], 'codice');
        Out::valore($this->nameForm . '_Numero_serie', $serie_rec['SIGLA'] . " / " . $this->proges_rec['SERIEPROGRESSIVO'] . " / " . $this->proges_rec['SERIEANNO']);

        Out::disableField($this->nameForm . '_PROGES[GESDRE]');
        Out::disableField($this->nameForm . '_PROGES[GESDRI]');

        $Numero_procedimento = substr($this->proges_rec['GESNUM'], 4, 6) . " / " . substr($this->proges_rec['GESNUM'], 0, 4);
        Out::valore($this->nameForm . '_Numero_procedimento', $Numero_procedimento);

        Out::valore($this->nameForm . '_Anno_prot', substr($this->proges_rec['GESNPR'], 0, 4));
        Out::valore($this->nameForm . '_Numero_prot', substr($this->proges_rec['GESNPR'], 4, 6));

        $metaDati = proIntegrazioni::GetMetedatiProt($this->proges_rec['GESNUM']);
        if (isset($metaDati['Data']) && $this->proges_rec['GESNPR'] != 0) {
            Out::valore($this->nameForm . "_DataProtocollo", $metaDati['Data']);
            Out::show($this->nameForm . '_DataProtocollo_field');
        } else {
            Out::hide($this->nameForm . '_DataProtocollo_field');
        }

        $profilo = proSoggetto::getProfileFromIdUtente();
        Out::valore($this->nameForm . '_UTENTEATTUALE', App::$utente->getKey('nomeUtente') . " - " . $profilo['COD_ANANOM']);

        $prasta_rec = $this->praLib->GetPrasta($this->proges_rec['GESNUM'], 'codice');
        Out::html($this->nameForm . '_PRASTA[STADES]', $prasta_rec['STADES']);

        Out::hide($this->nameForm . '_PRASTA[STADES]');
    }

    public function getProges_rec() {
        return $this->proges_rec;
    }

    public function setProges_rec($proges_rec) {
        $this->proges_rec = $proges_rec;
    }

    /*
      private function disegnaDomandaSemplice($passoCorrente) {
      Out::html($this->nameForm . '_divContenitorePasso', '');

      /* @var $praCompPassoGest praCompPassoGest /
      $praCompPassoGest = itaFormHelper::innerForm('praCompDomandaSemplice', $this->nameForm . '_divContenitorePasso');
      $praCompPassoGest->setEvent('openform');
      $praCompPassoGest->setReturnModel($this->nameForm);
      $praCompPassoGest->setReturnEvent('returnFromGestPasso');
      $praCompPassoGest->setPropak($passoCorrente['PROPAK']);
      //$praCompPassoGest->setPropak($this->datiWf->passi[0]['PROPAK']);
      $praCompPassoGest->setReturnId('');
      $praCompPassoGest->parseEvent();

      $this->praCompPassoGestFormname['praCompDomandaSemplice'] = $praCompPassoGest->getNameForm();
      }
     */
    /*
      private function disegnaDomandaMultipla($passoCorrente) {
      Out::html($this->nameForm . '_divContenitorePasso', '');

      /* @var $praCompPassoGest praCompPassoGest /
      $praCompPassoGest = itaFormHelper::innerForm('praCompDatiAggiuntiviForm', $this->nameForm . '_divContenitorePasso');
      $praCompPassoGest->setEvent('openform');
      $praCompPassoGest->setReturnModel($this->nameForm);
      $praCompPassoGest->setReturnEvent('returnFromGestPasso');
      //        $praCompPassoGest->setPropak($passoCorrente['PROPAK']);
      //        $praCompPassoGest->setReturnId('');
      $praCompPassoGest->parseEvent();
      $praCompPassoGest->openGestione($passoCorrente['PRONUM'], $passoCorrente['PROPAK']);


      $this->praCompPassoGestFormname['praCompDatiAggiuntiviForm'] = $praCompPassoGest->getNameForm();
      }
     */

    private function salvaPassiFatti($passoSuccessivo) {
        $praLib = new praLib();

        $propasFatti_rec = Array();
        $passoAttuale = $this->datiWf->getPassoCorrente();


        // Salvo record PRAPASFATTI
        //Cerco record di PROPAS da aggiornare
        $sql = "SELECT * FROM PROPASFATTI "
                . " WHERE PROPASFATTI.PROPAK = '" . $passoAttuale['PROPAK'] . "' ";

        $propasFatti_rec = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, false);



        if (!$propasFatti_rec) {
            $propasFatti_rec = Array();
            $propasFatti_rec['PRONUM'] = $passoAttuale['PRONUM'];
            $propasFatti_rec['PROPRO'] = $passoAttuale['PROPRO'];
            $propasFatti_rec['PROPAK'] = $passoAttuale['PROPAK'];
            $propasFatti_rec['PROSPA'] = $passoSuccessivo;

            $insert_info = 'Inserito record PROPASFATTI con PROPAK: ' . $propasFatti_rec['PROPAK'];

            if (!$this->insertRecord($praLib->getPRAMDB(), 'PROPASFATTI', $propasFatti_rec, $insert_info)) {

                return false;
            }
        } else {
            $propasFatti_rec['PROSPA'] = $passoSuccessivo;

            $update_info = 'Salva passo fatto: ' . $propasFatti_rec['PROPAK'] . ' con passo successivo' . $propasFatti_rec['PROSPA'];

            if (!$this->updateRecord($praLib->getPRAMDB(), 'PROPASFATTI', $propasFatti_rec, $update_info, 'ROW_ID')) {

                return false;
            }
        }

        return true;
    }

}
