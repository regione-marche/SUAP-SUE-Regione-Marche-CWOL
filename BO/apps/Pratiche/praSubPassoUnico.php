<?php

/* * 
 *
 * GESTIONE PASSO UNICO
 *
 * PHP Version 5
 *
 * @category
 * @author     Simone Franchi <sfranchi@selecfi.it>
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    20.02.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */

include_once ITA_BASE_PATH . '/apps/Pratiche/praSoggetti.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRuolo.class.php';
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibProtocolla.class.php';
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibCalendar.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibElaborazioneDati.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRicDestinatari.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praPerms.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praNote.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibDestinazioni.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibAssegnazionePassi.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPasso.class.php';
include_once ITA_BASE_PATH . '/apps/AlboPretorio/albRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIntegrazioni.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Anagrafe/anaRic.class.php';
include_once ITA_BASE_PATH . '/apps/Anagrafe/anaLib.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docRic.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php';
include_once ITA_BASE_PATH . '/apps/Commercio/wcoLib.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devRic.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once(ITA_LIB_PATH . '/zip/itaZip.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCore/itaMailer.class.php');
include_once ITA_LIB_PATH . '/itaPHPCore/itaFormHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praCompPassoGest.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibRiservato.class.php';

function praSubPassoUnico() {
    $praSubPassoUnico = new praSubPassoUnico();
    $praSubPassoUnico->parseEvent();
    return;
}

class praSubPassoUnico extends praCompPassoGest {

    public $accLib;
    public $praLib;
    public $praLibAllegati;
    public $praLibElaborazioneDati;
    public $praPerms;
    public $proLib;
    public $docLib;
    public $utiEnte;
    public $PRAM_DB;
    public $PROT_DB;
    public $COMUNI_DB;
    public $ITW_DB;
    public $nameForm;
    public $divGes;
    public $divAllegatiCom;
    public $gridAllegati;
    public $gridAllCom;
    public $gridNote;
    public $gridAltriDestinatari;
    public $arrayInfo = array();
    public $passAlle = array();
    public $passCom = array();
    public $allegatiComunicazione = array();
    public $currAllegato;
    public $currGesnum;
    public $returnModel;
    public $returnMethod;
    public $daMail;
    public $datiWS;
    public $datiForm;
    public $rowidAppoggio;
    public $workDate;
    public $workYear;
    public $keyPasso;
    public $allegatiAppoggio = array();
    public $testiAssociati = array();
    public $chiudiForm;
    public $pagina;
    public $sql;
    public $selRow;
    public $tipoFile;
    public $idCorrispondente;
    public $docCommercio;
    public $datiRubricaWS = array();
    public $destinatari = array();
    private $tipoProtSel;
    /* @var $this->noteManager proNoteManager */
    public $noteManager;
    public $rowidMail;
    public $praReadOnly;
    public $allegatiPrtSel = array();
    public $openMode;
    public $openRowid;
    private $tipoProtocollo;
    private $iniDateEdit;
    public $tipoPasso;
    public $praPassi;
    public $Propas;
    public $praLibDestinazioni;
    public $praCompDatiAggiuntiviFormname;
    public $mettiAllaFirma;
    public $proSubTrasmissioni;
    public $flagAssegnazioniPasso;
    public $praLibPasso;
    public $daTrasmissioni;
    public $praLibRiservato;
    public $presenzaAllegatiRiservati;

    function __construct() {
        parent::__construct();

        /*
         *  carico le librerie
         */
        $this->praLib = new praLib();
        $this->praLibPasso = new praLibPasso();
        $this->praLibAllegati = new praLibAllegati();
        $this->praLibElaborazioneDati = new praLibElaborazioneDati();
        $this->proLib = new proLib();
        $this->accLib = new accLib();
        $this->docLib = new docLib();
        $this->praPerms = new praPerms();
        $this->utiEnte = new utiEnte();
        $this->PRAM_DB = $this->praLib->getPRAMDB();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->ITW_DB = $this->accLib->getITW();
        $this->ITALWEB_DB = $this->utiEnte->getITALWEB_DB();
        $this->profilo = proSoggetto::getProfileFromIdUtente();
        $this->praLibDestinazioni = new praLibDestinazioni();
        $this->praLibRiservato = new praLibRiservato();
        try {
            $this->COMUNI_DB = ItaDB::DBOpen('COMUNI', false);
        } catch (Exception $e) {
            App::log($e->getMessage());
        }
    }

    function postInstance() {
        parent::postInstance();
        try {
            $this->flagAssegnazioniPasso = App::$utente->getKey($this->nameForm . '_flagAssegnazioniPasso');
            $this->passAlle = App::$utente->getKey($this->nameForm . '_passAlle');
            $this->passCom = App::$utente->getKey($this->nameForm . '_passCom');
            $this->currGesnum = App::$utente->getKey($this->nameForm . '_currGesnum');
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnMethod = App::$utente->getKey($this->nameForm . '_returnMethod');
            $this->daMail = App::$utente->getKey($this->nameForm . '_daMail');
            $this->datiForm = App::$utente->getKey($this->nameForm . '_datiForm');
            $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
            $this->allegatiAppoggio = App::$utente->getKey($this->nameForm . '_allegatiAppoggio');
            $this->keyPasso = App::$utente->getKey($this->nameForm . '_keyPasso');
            $this->iniDateEdit = App::$utente->getKey($this->nameForm . '_iniDateEdit');
            $this->pagina = App::$utente->getKey($this->nameForm . '_pagina');
            $this->allegatiComunicazione = App::$utente->getKey($this->nameForm . '_allegatiComunicazione');
            $this->sql = App::$utente->getKey($this->nameForm . '_sql');
            $this->selRow = App::$utente->getKey($this->nameForm . '_selRow');
            $this->arrayInfo = App::$utente->getKey($this->nameForm . '_arrayInfo');
            $this->currAllegato = App::$utente->getKey($this->nameForm . '_currAllegato');
            $this->tipoFile = App::$utente->getKey($this->nameForm . '_tipoFile');
            $this->testiAssociati = App::$utente->getKey($this->nameForm . '_testiAssociati');
            $this->idCorrispondente = App::$utente->getKey($this->nameForm . '_idCorrispondente');
            $this->datiRubricaWS = App::$utente->getKey($this->nameForm . '_datiRubricaWS');
            $this->chiudiForm = App::$utente->getKey($this->nameForm . '_chiudiForm');
            $this->docCommercio = App::$utente->getKey($this->nameForm . '_docCommercio');
            $this->destinatari = App::$utente->getKey($this->nameForm . '_destinatari');
            $this->tipoProtSel = App::$utente->getKey($this->nameForm . '_tipoProtSel');
            $this->datiWS = App::$utente->getKey($this->nameForm . '_datiWS');
            $this->rowidMail = App::$utente->getKey($this->nameForm . '_rowidMail');
            $this->noteManager = unserialize(App::$utente->getKey($this->nameForm . '_noteManager'));
            $this->praReadOnly = App::$utente->getKey($this->nameForm . '_praReadOnly');
            $this->allegatiPrtSel = App::$utente->getKey($this->nameForm . '_allegatiPrtSel');
            $this->tipoProtocollo = App::$utente->getKey($this->nameForm . '_tipoProtocollo');
            $this->praPassi = App::$utente->getKey($this->nameForm . '_praPassi');
            $this->Propas = App::$utente->getKey($this->nameForm . '_Propas');
            $this->praCompDatiAggiuntiviFormname = App::$utente->getKey($this->nameForm . '_praCompDatiAggiuntiviFormname');
            $this->mettiAllaFirma = App::$utente->getKey($this->nameForm . '_mettiAllaFirma');
            $this->proSubTrasmissioni = App::$utente->getKey($this->nameForm . '_proSubTrasmissioni');
            $this->daTrasmissioni = App::$utente->getKey($this->nameForm . '_daTrasmissioni');
            $this->presenzaAllegatiRiservati = App::$utente->getKey($this->nameForm . '_presenzaAllegatiRiservati');

//
// Inizializzo variabili
//
            $data = App::$utente->getKey('DataLavoro');
            if ($data != '') {
                $this->workDate = $data;
            } else {
                $this->workDate = date('Ymd');
            }
            $this->workYear = date('Y', strtotime($this->workDate));
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }

        $this->divGes = $this->nameForm . "_divGestione";
        $this->divAllegatiCom = $this->nameForm . "_divAllegatiCom";
        $this->gridAllegati = $this->nameForm . "_gridAllegati";
        $this->gridAllCom = $this->nameForm . "_gridAllCom";
        $this->gridNote = $this->nameForm . "_gridNote";
        $this->gridAltriDestinatari = $this->nameForm . "_gridAltriDestinatari";
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_flagAssegnazioniPasso', $this->flagAssegnazioniPasso);
            App::$utente->setKey($this->nameForm . '_passAlle', $this->passAlle);
            App::$utente->setKey($this->nameForm . '_passCom', $this->passCom);
            App::$utente->setKey($this->nameForm . '_currGesnum', $this->currGesnum);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnMethod', $this->returnMethod);
            App::$utente->setKey($this->nameForm . '_daMail', $this->daMail);
            App::$utente->setKey($this->nameForm . '_datiForm', $this->datiForm);
            App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
            App::$utente->setKey($this->nameForm . '_allegatiAppoggio', $this->allegatiAppoggio);
            App::$utente->setKey($this->nameForm . '_keyPasso', $this->keyPasso);
            App::$utente->setKey($this->nameForm . '_iniDateEdit', $this->iniDateEdit);
            App::$utente->setKey($this->nameForm . '_pagina', $this->pagina);
            App::$utente->setKey($this->nameForm . '_allegatiComunicazione', $this->allegatiComunicazione);
            App::$utente->setKey($this->nameForm . '_sql', $this->sql);
            App::$utente->setKey($this->nameForm . '_selRow', $this->selRow);
            App::$utente->setKey($this->nameForm . '_arrayInfo', $this->arrayInfo);
            App::$utente->setKey($this->nameForm . '_currAllegato', $this->currAllegato);
            App::$utente->setKey($this->nameForm . '_tipoFile', $this->tipoFile);
            App::$utente->setKey($this->nameForm . '_testiAssociati', $this->testiAssociati);
            App::$utente->setKey($this->nameForm . '_idCorrispondente', $this->idCorrispondente);
            App::$utente->setKey($this->nameForm . '_datiRubricaWS', $this->datiRubricaWS);
            App::$utente->setKey($this->nameForm . '_chiudiForm', $this->chiudiForm);
            App::$utente->setKey($this->nameForm . '_docCommercio', $this->docCommercio);
            App::$utente->setKey($this->nameForm . '_destinatari', $this->destinatari);
            App::$utente->setKey($this->nameForm . '_tipoProtSel', $this->tipoProtSel);
            App::$utente->setKey($this->nameForm . '_datiWS', $this->datiWS);
            App::$utente->setKey($this->nameForm . '_rowidMail', $this->rowidMail);
            App::$utente->setKey($this->nameForm . '_noteManager', serialize($this->noteManager));
            App::$utente->setKey($this->nameForm . '_praReadOnly', $this->praReadOnly);
            App::$utente->setKey($this->nameForm . '_allegatiPrtSel', $this->allegatiPrtSel);
            App::$utente->setKey($this->nameForm . '_tipoProtocollo', $this->tipoProtocollo);
            App::$utente->setKey($this->nameForm . '_praPassi', $this->praPassi);
            App::$utente->setKey($this->nameForm . '_Propas', $this->Propas);
            App::$utente->setKey($this->nameForm . '_praCompDatiAggiuntiviFormname', $this->praCompDatiAggiuntiviFormname);
            App::$utente->setKey($this->nameForm . '_mettiAllaFirma', $this->mettiAllaFirma);
            App::$utente->setKey($this->nameForm . '_proSubTrasmissioni', $this->proSubTrasmissioni);
            App::$utente->setKey($this->nameForm . '_daTrasmissioni', $this->daTrasmissioni);
            App::$utente->setKey($this->nameForm . '_presenzaAllegatiRiservati', $this->presenzaAllegatiRiservati);
        }
    }

    public function getOpenMode() {
        return $this->openMode;
    }

    public function setOpenMode($openMode) {
        $this->openMode = $openMode;
    }

    function getProSubTrasmissioni() {
        return $this->proSubTrasmissioni;
    }

    function setProSubTrasmissioni($proSubTrasmissioni) {
        $this->proSubTrasmissioni = $proSubTrasmissioni;
    }

    public function getOpenRowid() {
        return $this->openRowid;
    }

    public function getKeyPasso() {
        return $this->keyPasso;
    }

    public function setPassoCorrente($passoCorrente_rec) {
        //$this->keyPasso = $passoCorrente_rec['PROPAK'];

        $this->setKeyPasso($passoCorrente_rec['PROPAK']);
        $this->setGesnum($passoCorrente_rec['PRONUM']);
    }

    public function setKeyPasso($keyPasso) {
        $this->keyPasso = $keyPasso;
    }

    public function setGesnum($gesnum) {
        $this->currGesnum = $gesnum;
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

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':

                //$this->currGesnum = '2018000016';
                //$this->keyPasso = '2018000016154281294861';
                //Out::msgInfo("POST", print_r($_POST, true));
                $this->inizializzaForm();


                if (is_array($_POST['listaAllegati'])) {
                    $this->caricaAllegatiEsterni($_POST['listaAllegati']);
                }
                if ($this->daMail["protocolla"]['FILENAME']) {
                    $this->caricaArrivoDaMail($this->daMail["protocolla"]);
                }

                if ($_POST['datiForm']) { // per integrazione
                    $this->datiForm = $_POST['datiForm'];
                }
                if ($_POST['datiInfo']) {
                    Out::show($this->nameForm . '_divInfo');
                    Out::html($this->nameForm . "_divInfo", $_POST['datiInfo']);
                } else {
                    Out::hide($this->nameForm . '_divInfo');
                }
                if ($_POST['passi']) {
                    $this->praPassi = $_POST['passi'];
                } else {
                    $proges_rec = $this->praLib->GetProges($this->currGesnum);
                    $this->praPassi = $this->praLib->caricaPassiBO($this->currGesnum);
                    if (!$this->praPerms->checkSuperUser($proges_rec)) {
                        $this->praPassi = $this->praPerms->filtraPassiView($this->praPassi);
                    }
                }
                
                /*
                 * Spostato nel Dettaglio perchè il flag è utilizzato solo post dettaglio
                 */
                //$this->flagAssegnazioniPasso = $this->CheckAssegnazionePasso();
                break;
            case "broadcastMsg":
                switch ($_POST['message']) {
                    case 'CLOSE_PRAGEST':
                        if ($_POST['sender'] == "praGest") {
                            $this->returnToParent();
                        }
                        break;
                    case 'PRENDI_DA_ANAGRAFE':
                        $soggetto = $_POST['msgData'];
                        $this->AggiungiDaAnagrafe($soggetto);
                        break;
                    case 'PRENDI_DA_ANAGRAFE_DEST':
                        $soggetto = $_POST['msgData'];
                        $this->AggiungiDaAnagrafe($soggetto, "DESTINATARIO");
                        break;
                }
                break;
            case 'afterSaveCell':
                $propas_rec = $this->praLib->GetPropas($this->keyPasso);
                $pracomP_rec = $this->DecodPracomP($propas_rec['PROPAK']);
                $pracomA_rec = $this->DecodPracomA($propas_rec['PROPAK'], $pracomP_rec['ROWID']);
                if ($pracomA_rec['COMDAT']) {
                    Out::msgInfo("Modifica Passo", "Passo protocollato. Impossibile protocollare.");
                    break;
                }
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        $pasdoc_rec = $this->praLib->GetPasdoc($this->passAlle[$_POST['rowid']]['ROWID'], 'ROWID');
                        if (!$pasdoc_rec) {
                            $pasnot = $this->passAlle[$_POST['rowid']]['INFO'];
                        } else {
                            $pasnot = $pasdoc_rec['PASNOT'];
                        }

                        /*
                         * Verifica visibilità passo
                         */
                        if ($propas_rec['PROVISIBILITA'] == "Protetto" || $propas_rec['PROVISIBILITA'] == "soloPasso") {
                            $proges_rec = $this->praLib->GetProges($this->currGesnum);
                            if (!$this->praPerms->checkSuperUser($proges_rec)) {
                                if ($this->praPerms->checkUtenteGenerico($propas_rec)) {
                                    Out::msgInfo("Modifica Allegati", "Attenzione non si dispone dei permessi per modificare il file.");
                                    TableView::setCellValue($this->gridAllegati, $_POST['rowid'], 'INFO', $pasnot);
                                    break;
                                }
                            }
                        }

                        /*
                         * Modifico la colonna scelta
                         */
                        switch ($_POST['cellname']) {
                            case 'INFO':
                                TableView::setCellValue($this->gridAllegati, $_POST['rowid'], 'INFO', $_POST['value']);
                                $this->passAlle[$_POST['rowid']]['INFO'] = $_POST['value'];
                                $this->passAlle[$_POST['rowid']]['FILEINFO'] = $_POST['value'];
                                break;
                            case 'STATOALLE':
                                $this->passAlle = $this->passAlle = $this->praLibAllegati->ChangeNoteStato($this->passAlle, $_POST['rowid'], $_POST['cellname'], "", $_POST['value']);
                                break;
                            default:
                                break;
                        }

                        $this->CaricaGriglia($this->gridAllegati, $this->passAlle);
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        Out::msgQuestion("Upload.", "Vuoi caricare un file interno o uno esterno?", array(
                            'F2-Testo Base' => array('id' => $this->nameForm . '_UploadTestoBase', 'model' => $this->nameForm, 'shortCut' => "f2"),
                            'F8-Esterno' => array('id' => $this->nameForm . '_UploadFileEsterno', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Interno' => array('id' => $this->nameForm . '_UploadFileInterno', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->gridAltriDestinatari:
                        $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
                        if ($pracomP_rec["COMPRT"])
                            $desc = "protocollata";
                        if ($pracomP_rec["COMIDDOC"])
                            $desc = "inviata al protocollo";
                        if ($pracomP_rec["COMPRT"] || $pracomP_rec["COMIDDOC"]) {
                            Out::msgInfo("Attenzione!!", "Impossibile inserire altri destinatari. La comunicazione risulta $desc.");
                            break;
                        }
                        itaLib::openForm('praGestDestinatari', true);
                        $praGestDest = itaModel::getInstance('praGestDestinatari');
                        $praGestDest->setEvent('openform');
                        $praGestDest->setReturnEvent("returnGestDest");
                        $praGestDest->setReturnModel($this->nameForm);
                        $praGestDest->setReturnId('');
                        $praGestDest->setMode("new");
                        $praGestDest->setKeyPasso($this->keyPasso);
                        $praGestDest->setGesnum($this->currGesnum);
                        $praGestDest->setRowid("");
                        $praGestDest->parseEvent();
                        break;
                    case $this->gridNote:
                        $this->openFormAddNota();
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '2');
                        break;
                    case $this->gridNote:
                        $this->noteManager = praNoteManager::getInstance($this->praLib, praNoteManager::NOTE_CLASS_PROPAS, $this->keyPasso);
                        $this->caricaNote();
                        break;
                    case $this->gridAltriDestinatari:
                        $this->CaricaDestinatari($this->keyPasso, $_POST['sidx'], $_POST['sord']);
                        break;
                }

                break;
            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridAllCom:
                        if (array_key_exists($_POST['rowid'], $this->allegatiComunicazione) == true) {
                            Out::openDocument(utiDownload::getUrl(
                                            $this->allegatiComunicazione[$_POST['rowid']]['FILENAME'], $this->allegatiComunicazione[$_POST['rowid']]['FILEPATH']
                                    )
                            );
                        }
                        break;
                    case $this->gridAllegati:
                        Out::msgInfo("Allegato", "Apre Allegato");
                        if (array_key_exists($_POST['rowid'], $this->passAlle) == true) {
                            $doc = $this->passAlle[$_POST['rowid']];
                            $this->praLibAllegati->ApriAllegato(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), $doc, $this->currGesnum, $this->keyPasso);
                        }
                        break;
                    case $this->gridAltriDestinatari:
                        itaLib::openForm('praGestDestinatari', true);
                        $praGestDest = itaModel::getInstance('praGestDestinatari');
                        $praGestDest->setEvent('openform');
                        $praGestDest->setReturnEvent("returnGestDest");
                        $praGestDest->setReturnModel($this->nameForm);
                        $praGestDest->setReturnId('');
                        $praGestDest->setMode("edit");
                        $praGestDest->setKeyPasso($this->keyPasso);
                        $praGestDest->setGesnum($this->currGesnum);
                        $praGestDest->setGiorniScadenza($_POST[$this->nameForm . "_PRACOM"]['COMGRS']);
                        $praGestDest->setDestinatario($this->destinatari[$_POST['rowid']]);
                        $praGestDest->setRowid($_POST['rowid']);
                        $praGestDest->parseEvent();
                        break;
                    case $this->gridNote:
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
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
                        $allegato = $this->passAlle[$_POST['rowid']];
                        $pasdoc_rec = $this->praLib->GetPasdoc($allegato['ROWID'], "ROWID");
                        if ($pasdoc_rec['PASPRTCLASS'] != "" && $pasdoc_rec['PASPRTROWID'] != 0) {
                            Out::msgInfo("Cancellazione Allegati", "Impossibile cancellare l'allegato perchè risulta protocollato");
                            break;
                        }

                        /*
                         * Controllo se allegato bloccato, non lo posso cancellare
                         */
                        if ($allegato['PASLOCK'] == 1) {
                            Out::msgInfo("Cancellazione Allegati", "Impossibile cancellare l'allegato perche risulta bloccato");
                            break;
                        }

                        /*
                         * Controllo se allegato fa parte della conferenza dei servizi, non lo posso cancellare
                         */
                        $processoIniziato = $this->checkFirmaCds($propas_rec['PROFLCDS']);
                        if ($processoIniziato === true) {
                            if ($allegato['PASTIPO'] == "FIR_CDS" || $allegato['PASFLCDS'] == 1) {
                                Out::msgInfo("Cancellazione Allegati", "Impossibile cancellare l'allegato perche fa parte della Conferenza di Servizi.");
                                break;
                            }
                        }
                        if (!$this->checkFrimaAllegato(false, $allegato['ROWID'])) {
                            Out::msgStop("Cancellazione Allegati", "Rimuovere Documento alla firma prima di poter eliminare l'allegato");
                            break;
                        }
                        Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare l'allegato?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancAlle', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancAlle', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        $this->rowidAppoggio = $_POST['rowid'];
                        break;
                    case $this->gridAltriDestinatari:
                        $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
                        $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
                        if ($pracomP_rec["COMPRT"])
                            $desc = "protocollata";
                        if ($pracomP_rec["COMIDDOC"])
                            $desc = "inviata al protocollo";
                        if ($pracomP_rec["COMPRT"] || $pracomP_rec["COMIDDOC"]) {
                            Out::msgInfo("Cancellazione Destinatario", "Impossibile cancellare il destinatario perchè la comunicazione risulta $desc");
                            break;
                        }
                        $dest = $this->destinatari[$_POST['rowid']];
                        if ($dest['IDMAIL']) {
                            Out::msgInfo("Cancellazione Destinatario", "Impossibile cancellare il destinatario perchè la comunicazione risulta già inviata");
                            break;
                        }

                        /*
                         * Controllo se allegato fa parte della conferenza dei servizi, non lo posso cancellare
                         */
                        $processoIniziato = $this->checkFirmaCds($propas_rec['PROFLCDS']);
                        if ($processoIniziato === true) {
                            Out::msgInfo("Cancellazione Firmatari", "Impossibile cancellare il firmatario perchè il processo di firma è già iniziato.");
                            break;
                        }

                        Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare il Destinatario?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancDest', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancDest', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        $this->rowidAppoggio = $_POST['rowid'];
                        break;
                    case $this->gridNote:
                        $dati = $this->noteManager->getNota($_POST['rowid']);
                        if ($dati['UTELOG'] != App::$utente->getKey('nomeUtente')) {
                            Out::msgStop("Attenzione!", "Solo l'utente " . App::$utente->getKey('nomeUtente') . " è abilitato alla modifica della Nota.");
                            break;
                        }

                        $this->noteManager->cancellaNota($_POST['rowid']);
                        $this->noteManager->salvaNote();
                        $this->caricaNote();
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {

                    case $this->nameForm . '_NuovaComDaProt':
                        $proObject = proWsClientFactory::getInstance();
                        if (!$proObject) {
                            Out::msgStop("Importazione passso da protocollo", "Errore inizializzazione driver protocollo");
                            break;
                        }
                        $arrCampi = proWsClientHelper::getCampiInput($proObject->getClientType());
                        Out::msgInput('Dati Protocollazione', $arrCampi, array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaDatiProtocollo', 'model' => $this->nameForm, "shortCut" => "f5")
                                ), $this->nameForm);
                        break;
                    case $this->nameForm . '_ConfermaDatiProtocollo':
                        $proObject = proWsClientFactory::getInstance();
                        if (!$proObject) {
                            Out::msgStop("Importazione passo da protocollo", "Errore inizializzazione driver protocollo");
                            break;
                        }

                        /*
                         * Creazione array paramn
                         */
                        $param = array(
                            "NumeroProtocollo" => $_POST[$this->nameForm . "_nummeroProt"],
                            "AnnoProtocollo" => $_POST[$this->nameForm . "_AnnoProtocollo"],
                            "TipoProtocollo" => $_POST[$this->nameForm . "_tipoProt"],
                            "Docnumber" => $_POST[$this->nameForm . "_idDoc"],
                            "Segnatura" => $_POST[$this->nameForm . "_Segnatura"],
                        );

                        /*
                         * Validazione array param
                         */
                        $msgErr = $this->praLib->validatorFields($proObject->getClientType(), $param);
                        if ($msgErr) {
                            Out::msgStop("Attenzione", $msgErr);
                            break;
                        }

                        $this->datiWS = $proObject->LeggiProtocollo($param);
                        if ($this->datiWS['Status'] == 0) {
                            $this->datiWS['RetValue']['DatiProtocollo']['TipoProtocollo'] = $this->tipoProtocollo;
                            $dati = $this->datiWS['RetValue']['DatiProtocollo'];
                            //$dati = $this->datiWS['RetValue']['Dati'];
                            if ($dati['Origine'] == "A") {
                                $old_pracom_rec = $this->praLib->GetPracomA($this->keyPasso);
                            } else {
                                $old_pracom_rec = $this->praLib->GetPracomP($this->keyPasso);
                            }
                            if ($old_pracom_rec) {
                                //controllo che la comunicazione non sia protocollata
                                if ($old_pracom_rec['COMPRT']) {
                                    Out::msgStop("Attenzione", "La comunicazione risulta già protocollata. Inserimento non permesso.");
                                    break;
                                }
                                //controllo che non abbia destinatari
                                $sql = "SELECT * FROM PRAMITDEST WHERE ROWIDPRACOM = " . $old_pracom_rec['ROWID'];
                                $old_mittdest = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                                if ($old_mittdest) {
                                    Out::msgStop("Attenzione", "Sono già presenti dei destinatari per questa comunicazione. Inserimento non permesso.");
                                    break;
                                }
                            }
                            $arrayNormalizzato = proIntegrazioni::NormalizzaArray($this->datiWS);
                            $html = "<br>";
                            $html .= "<br>";
                            $html .= "<br>";
                            $html .= proIntegrazioni::Array2Html($arrayNormalizzato);
                            $html .= "<br>";
                            if ($this->datiWS['RetValue']['DatiProtocollo']['NumeroProtocollo']) {
                                if ($this->datiWS['RetValue']['DatiProtocollo']['Origine'] == "I" || $this->datiWS['RetValue']['DatiProtocollo']['Origine'] == "C") {
                                    $html .= "<span style=\"color:red;font-weight:bold;\">Il Protocollo selezionato è di tipo Interno, scegliere se caricarlo come Arrivo o come Partenza.</span>";
                                    Out::msgQuestion("Importa Dati da Protocollo!", $html, array(
                                        'Conferma Partenza' => array('id' => $this->nameForm . '_ConfermaImportaProtocolloP', 'model' => $this->nameForm),
                                        'Conferma Arrivo' => array('id' => $this->nameForm . '_ConfermaImportaProtocolloA', 'model' => $this->nameForm),
                                        'Annulla' => array('id' => $this->nameForm . '_AnnullaImportaProtocollo', 'model' => $this->nameForm)
                                            )
                                    );
                                } else {
                                    $html .= "<span style=\"color:red;font-weight:bold;\">Confermi l'importazione dei dati e degli allegati del protocollo?</span>";
                                    Out::msgQuestion("Importa Dati da Protocollo!", $html, array(
                                        'Conferma' => array('id' => $this->nameForm . '_ConfermaImportaProtocollo', 'model' => $this->nameForm),
                                        'Annulla' => array('id' => $this->nameForm . '_AnnullaImportaProtocollo', 'model' => $this->nameForm)
                                            )
                                    );
                                }
                            } else {
                                if ($this->datiWS['RetValue']['DatiProtocollo']['DocNumber']) {
                                    $html .= "<span style=\"color:red;font-weight:bold;\">Confermi l'importazione dei dati e degli allegati del documento?</span>";
                                    Out::msgQuestion("Importa Dati da Protocollo!", $html, array(
                                        'Conferma' => array('id' => $this->nameForm . '_ConfermaImportaDocumento', 'model' => $this->nameForm),
                                        'Annulla' => array('id' => $this->nameForm . '_AnnullaImportaDocumento', 'model' => $this->nameForm)
                                            )
                                    );
                                } else {
                                    Out::msgStop("Errore!!!", "Numero protocollo / Id Documento non trovati.");
                                }
                            }
                        } else {
                            Out::msgStop("Errore!!!", $this->datiWS['Message']);
                        }
                        break;

                    case $this->nameForm . '_AnnullaImportaDocumento':
                    case $this->nameForm . '_AnnullaImportaProtocollo':
                        $this->datiWS = null;
                        break;
                    case $this->nameForm . '_ConfermaImportaProtocolloP':
                        $dati = $this->datiWS['RetValue']['DatiProtocollo'];
                        $dati['Origine'] = "P";
                        if (!$this->ImportaDaProtocollo($dati)) {
                            break;
                        }
                        if ($this->AggiornaRecord()) {
                            $this->Dettaglio($this->keyPasso);
                        }
                        Out::msgBlock("", 3000, true, "Dati del protocollo: {$dati['NumeroProtocollo']} / {$dati['Anno']} Importati.");
                        break;
                    case $this->nameForm . '_ConfermaImportaProtocolloA':
                        $dati = $this->datiWS['RetValue']['DatiProtocollo'];
                        $dati['Origine'] = "A";
                        if (!$this->ImportaDaProtocollo($dati)) {
                            break;
                        }
                        if ($this->AggiornaRecord()) {
                            $this->Dettaglio($this->keyPasso);
                        }
                        Out::msgBlock("", 3000, true, "Dati del protocollo: {$dati['NumeroProtocollo']} / {$dati['Anno']} Importati.");
                        break;
                    case $this->nameForm . '_ConfermaImportaProtocollo':
                        $dati = $this->datiWS['RetValue']['DatiProtocollo'];
                        if (!$this->ImportaDaProtocollo($dati)) {
                            break;
                        }
                        if ($this->AggiornaRecord()) {
                            //$this->AggiornaPartenzaDaPost();
                            //$this->RegistraDestinatari();
                            //$this->AggiornaArrivo();
                            $this->Dettaglio($this->keyPasso);
                        }
                        Out::msgBlock("", 3000, true, "Dati del protocollo: {$dati['NumeroProtocollo']} / {$dati['Anno']} Importati.");

                        break;
                    case $this->nameForm . '_ConfermaImportaDocumento':
                        $dati = $this->datiWS['RetValue']['DatiProtocollo'];
                        if (!$this->ImportaDaDocumento($dati)) {
                            break;
                        }
                        if ($this->AggiornaRecord()) {
                            $this->Dettaglio($this->keyPasso);
                        }
                        Out::msgBlock("", 3000, true, "Dati del documento con id: {$dati['DocNumber']} / " . substr($dati['Anno'], 0, 4) . " Importati.");
                        break;
                    case $this->nameForm . '_ConfermaRicaricaPasso':
                        $this->dettaglio($this->keyPasso);
                        break;
                    case $this->nameForm . '_ConfermaApriPasso':
                        if (!$this->ControllaDati()) {
                            break;
                        }
//                        $allegatiMail = $this->allegatiComunicazione;
                        $this->ApriPasso();
                    case $this->nameForm . '_AnnullaApriPasso':
                        if (!$this->ControllaDati()) {
                            break;
                        }

                        // A CHE SERVER QUESTO APPOGGIO?
                        $allegatiMail = $this->allegatiComunicazione;
                        if ($this->AggiornaRecord()) {
                            $this->AggiornaPartenzaDaPost();
                            $this->RegistraDestinatari();
                            $this->allegatiComunicazione = $allegatiMail;
                            $this->AggiornaArrivo();
                        } else {
                            $this->chiudiForm = false;
                            $this->Dettaglio($this->keyPasso);
                            //PERCHE NON FA BREAK??
                        }
                        if (is_array($this->daMail)) {
                            if (!$this->praLib->setClasseCaricatoPasso($this->daMail['archivio'], $this->daMail['IDMAIL'], $this->currGesnum, $this->keyPasso)) {
                                Out::msgStop("Errore", $this->praLib->getErrMessage());
                            }

                            $this->datiForm = array(
                                'GESNUM' => $this->currGesnum,
                                'PROPAK' => $this->keyPasso
                            );
                            $propas_rec = $this->praLib->GetPropas($this->keyPasso);
                            $titolo = "Acquisita Mail in Arrivo";
                            $testo = "Acquisita Mail in Arrivo Pratica: " . substr($propas_rec['PRONUM'], 0, 4) . "/" . substr($propas_rec['PRONUM'], 4) . " ";
                            $testo .= "Mittente: " . $this->daMail['protocolla']['MITTENTE'] . " ";
                            $dataInvio = substr($this->daMail['protocolla']['DATA'], 6, 2) . "/" . substr($this->daMail['protocolla']['DATA'], 4, 2) . "/" . substr($this->daMail['protocolla']['DATA'], 0, 4);
                            $testo .= "Data: $dataInvio ";
                            $testo .= "Note: " . $this->daMail['protocolla']['NOTE'];
                            $codRespAss = proSoggetto::getCodiceResponsabileAssegnazione();
                            if (!$this->praLib->inviaNotificaResponsabileAssegnazione($this->nameForm, $codRespAss, $propas_rec['ROWID'], $titolo, $testo)) {
                                Out::msgStop("Errore", "Invio Notifica al responsabile fallito");
                            }
//                            $Utente = $this->accLib->GetUtenti(App::$utente->getKey('nomeUtente'), 'utelog');
//                            $sql = "SELECT * FROM ENV_UTEMETA WHERE UTECOD='" . $Utente['UTECOD'] . "' AND METAKEY='ParmNotifiche'";
//                            $Env_utemeta_rec = ItaDB::DBSQLSelect($this->accLib->getITALWEB(), $sql, false);
//                            $Meta = unserialize($Env_utemeta_rec['METAVALUE']);
//                            if ($Meta['Notifiche']['NotMail'] == 1) {
//                                $Proges_rec = $this->praLib->GetProges($propas_rec['PRONUM']);
//                                $this->SendMail($Proges_rec, $titolo, $testo, $Destinatario);
//                            }
                            $this->daMail = null;
                        }
                        $this->CheckAperturaPassoPadre($_POST[$this->nameForm . '_PROPAS']);
                        if ($this->chiudiForm == true) {
                            //itaLib::deletePrivateUploadPath(); //******* ????
                            $this->returnToParent();
                        } else {
                            $this->dettaglio($this->keyPasso);
                        }
                        break;
                    case $this->nameForm . '_Scanner':
                        $this->ApriScanner();
                        break;
                    case $this->nameForm . '_daFtp':
                        Out::msgInput(
                                'Collegamento FTP', array(array(
                                'label' => array('style' => "width:60px;", 'value' => 'Password'),
                                'id' => $this->nameForm . '_pwdFtp',
                                'name' => $this->nameForm . '_pwdFtp',
                                'type' => 'password',
                                'width' => '50',
                                'size' => '30',
                                'maxchars' => '30'),
                            array(
                                'label' => array('style' => "width:60px;", 'value' => 'Nome File'),
                                'id' => $this->nameForm . '_nomeFile',
                                'name' => $this->nameForm . '_nomeFile',
                                'type' => 'text',
                                'width' => '50',
                                'size' => '30',
                                'maxchars' => '50')), array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaNomeFile', 'model' => $this->nameForm, 'shortCut' => "f5")
                                ), $this->nameForm . "_divGestione"
                        );
                        break;
                    case $this->nameForm . '_ConfermaNomeFile':
                        $msg = "";
                        $ftpPassword = $_POST[$this->nameForm . "_pwdFtp"];
                        if ($ftpPassword == "") {
                            $msg .= "<b>Password</b><br>";
                        }
                        $nomeFile = $_POST[$this->nameForm . "_nomeFile"];
                        if ($nomeFile == "") {
                            $msg .= "<b>Nome file</b><br>";
                        }
                        if ($msg) {
                            Out::msgStop("Attenzione!!!", "Compilare i seguenti campi:<br>$msg");
                            break;
                        }

                        $Filent_Rec = $this->praLib->GetFilent(2);
                        $host = $Filent_Rec['FILVAL'];
                        $conn_id = ftp_connect($host, 21, 15);
                        if (!$conn_id) {
                            Out::msgStop("Attenzione!!!", "Impossibile connettersi all'host.<br>Il time-out è scaduto.<br>Contattare l'amministratore di rete.");
                            break;
                        }
                        $ftp_user = $Filent_Rec['FILDE1'];
                        if (!@ftp_login($conn_id, $ftp_user, $ftpPassword)) {
                            Out::msgStop("Attenzione!!!", "Impossibile connettersi\n");
                            break;
                        }
                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                $this->returnToParent();
                            }
                        }
                        $localPath = itaLib::getPrivateUploadPath();
                        if (ftp_get($conn_id, "$localPath/$nomeFile", $nomeFile, FTP_BINARY)) {
                            Out::msgInfo("Attenzione", "File $nomeFile scaricato con successo da sito FTP");
                            $this->aggiungiAllegato(md5(rand() * time()) . "." . pathinfo($nomeFile, PATHINFO_EXTENSION), "$localPath/$nomeFile", $nomeFile);
                        } else {
                            Out::msgInfo("Attenzione", "File $nomeFile non trovato");
                        }
                        break;
                    case $this->nameForm . '_CreaNuovoPasso':
                        $this->chiediModoCreaPasso();
//                        Out::msgQuestion("Nuovo Passo", "Vuoi Creare un Nuovo Passo?", array(
//                            'F1-Annulla' => array('id' => $this->nameForm . '_AnnullaPasso', 'model' => $this->nameForm, 'shortCut' => "f1"),
//                            'F5-Crea' => array('id' => $this->nameForm . '_CreaPasso', 'model' => $this->nameForm, 'shortCut' => "f5"),
//                            'F8-Crea e Salva' => array('id' => $this->nameForm . '_CreaSalvaPasso', 'model' => $this->nameForm, 'shortCut' => "f8")
//                                )
//                        );
                        break;
                    case $this->nameForm . '_CreaPasso':
                        $this->tipoPasso = $_POST[$this->nameForm . '_TipoPasso'];
                        switch ($this->tipoPasso) {
                            case 1:
                                $this->AzzeraVariabili();
                                $this->apriInserimento($this->currGesnum);
                                break;
                            case 2:
                                //$this->trovaSottoPasso($this->currGesnum);
                                $this->trovaSottoPasso();
                                break;
                        }
                        break;
                    case $this->nameForm . '_CreaSalvaPasso':
                        $this->tipoPasso = $_POST[$this->nameForm . '_TipoPasso'];
                        if (!$this->ControllaDati()) {
                            break;
                        }
                        if ($this->AggiornaRecord()) {
                            $this->AggiornaPartenzaDaPost();
                            $this->RegistraDestinatari();
                            $this->AggiornaArrivo();
                        } else {
                            Out::msgInfo("Aggiornamento Passo", "Errore Aggiornamento Passo");
                        }
                        switch ($this->tipoPasso) {
                            case 1:
                                $this->AzzeraVariabili();
                                $this->apriInserimento($this->currGesnum);
                                break;
                            case 2:
                                //$this->trovaSottoPasso($this->currGesnum);
                                $this->trovaSottoPasso();
                                break;
                        }
                        break;
                    case $this->nameForm . '_ChiudiSalvaCreaPasso':
                        $this->tipoPasso = $_POST[$this->nameForm . '_TipoPasso'];
                        // Chiudi
                        $_POST[$this->nameForm . '_PROPAS']['PROFIN'] = $this->workDate;
                        Out::valore($this->nameForm . '_PROPAS[PROFIN]', $this->workDate);
                        if (!$this->AggiornaRecord()) {
                            Out::msgStop("Attenzione!!!!", "Chiusura Passo Fallito");
                        } else {
                            $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
                            $this->iniDateEdit = $propas_rec['PRODATEEDIT'];
                        }
                        // Salva
                        if (!$this->ControllaDati()) {
                            break;
                        }
                        if ($this->AggiornaRecord()) {
                            $this->AggiornaPartenzaDaPost();
                            $this->RegistraDestinatari();
                            $this->AggiornaArrivo();
                        } else {
                            Out::msgInfo("Aggiornamento Passo", "Errore Aggiornamento Passo");
                        }
                        // Apri
                        switch ($this->tipoPasso) {
                            case 1:
                                $this->AzzeraVariabili();
                                $this->apriInserimento($this->currGesnum);
                                break;
                            case 2:
                                //$this->trovaSottoPasso($this->currGesnum);
                                $this->trovaSottoPasso();
                                break;
                        }
                        break;
                    case $this->nameForm . '_FileLocale':
                        $this->AllegaFile();
                        break;
                    case $this->nameForm . '_PROTRIC_DESTINATARIO_butt':
                        $anno = $this->workYear;
                        $where = '';
                        if ($_POST[$this->nameForm . '_DATAPROT_DESTINATARIO'] != '') {
                            $anno = substr($_POST[$this->nameForm . '_DATAPROT_DESTINATARIO'], 0, 4);
                            $data = $_POST[$this->nameForm . '_DATAPROT_DESTINATARIO'];
                            $where = ' AND (PRODAR=' . $data . ')';
                        }
                        $numero = $_POST[$this->nameForm . '_PROTRIC_DESTINATARIO'];
                        if ($numero != '') {
                            $numero = str_repeat("0", 6 - strlen(trim($numero))) . trim($numero);
                            $where = ' AND (PRONUM=' . $anno . $numero . ')';
                        }
                        albRic::albRicAnapro(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), $anno, $where, 'dest');
                        break;
                    case $this->nameForm . '_PROTRIC_MITTENTE_butt':
                        $anno = $this->workYear;
                        $where = '';
                        if ($_POST[$this->nameForm . '_DATAPROT_MITTENTE'] != '') {
                            $anno = substr($_POST[$this->nameForm . '_DATAPROT_MITTENTE'], 0, 4);
                            $data = $_POST[$this->nameForm . '_DATAPROT_DESTINATARIO'];
                            $where = ' AND (PRODAR=' . $data . ')';
                        }
                        $numero = $_POST[$this->nameForm . '_PROTRIC_MITTENTE'];
                        if ($numero != '') {
                            $numero = str_repeat("0", 6 - strlen(trim($numero))) . trim($numero);
                            $where = ' AND (PRONUM=' . $anno . $numero . ')';
                        }
                        albRic::albRicAnapro(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), $anno, $where, 'mitt');
                        break;
                    case $this->nameForm . '_Destinazione_butt':
                        praRic::praRicAnaddo(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig));
                        break;
                    case $this->nameForm . '_CodiceCla_butt':
                        praRic::praRicAnacla(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig));
                        break;
                    case $this->nameForm . '_PROPAS[PROCDR]_butt':
                        proRic::proRicAnamed(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), $where, 'proAnamed', '1');
                        break;
                    case $this->nameForm . '_PROPAS[PRODTP]_butt':
                        praRic::praRicPraclt(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "RICERCA Tipo Passo");
                        break;
                    case $this->nameForm . '_DESC_MITTENTE_butt':
                        if ($_POST[$this->nameForm . "_EMAIL_MITTENTE"]) {
                            //$sql = "SELECT * FROM ANAMED  WHERE MEDEMA = '" . $_POST[$this->nameForm . "_EMAIL_MITTENTE"] . "' AND MEDANN=0";
                            $sql = "SELECT
                                        ANAMED.MEDNOM,
                                        ANAMED.MEDEMA,
                                        ANAMED.MEDCIT,
                                        ANAMED.MEDIND,
                                        ANAMED.MEDPRO,
                                        ANAMED.MEDCAP,
                                        ANAMED.ROWID
                                    FROM 
                                        ANAMED
                                    LEFT OUTER JOIN TABDAG ON TABDAG.TDROWIDCLASSE = ANAMED.ROWID 
                                    WHERE TABDAG.TDAGVAL = '" . $_POST[$this->nameForm . "_EMAIL_MITTENTE"] . "' AND ANAMED.MEDANN=0";
                            $Anamed_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
                            if (!$Anamed_tab) {
                                $sql = "SELECT * FROM ANADES  WHERE DESEMA = '" . $_POST[$this->nameForm . "_EMAIL_MITTENTE"] . "'";
                                $Anades_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                                if ($Anades_tab) {
                                    $tipo = "ANADES";
                                    foreach ($Anades_tab as $Anades_rec) {
                                        $mittente_tab[] = array(
                                            "DENOMINAZIONE" => $Anades_rec['DESNOM'],
                                            "EMAIL" => $Anades_rec['DESEMA'],
                                            "COMUNE" => $Anades_rec['DESCIT'],
                                            "INDIRIZZO" => $Anades_rec['DESIND'] . " " . $Anades_rec['DESCIV'],
                                            "PROVINCIA" => $Anades_rec['DESPRO'],
                                            "CAP" => $Anades_rec['DESCAP'],
                                            "ROWID" => $Anades_rec['ROWID']
                                        );
                                    }
                                }
                            } else {
                                $tipo = "ANAMED";
                                foreach ($Anamed_tab as $Anamed_rec) {
                                    $mittente_tab[] = array(
                                        "DENOMINAZIONE" => $Anamed_rec['MEDNOM'],
                                        //"EMAIL" => $Anamed_rec['MEDEMA'],
                                        "EMAIL" => $_POST[$this->nameForm . "_EMAIL_MITTENTE"],
                                        "COMUNE" => $Anamed_rec['MEDCIT'],
                                        "INDIRIZZO" => $Anamed_rec['MEDIND'],
                                        "PROVINCIA" => $Anamed_rec['MEDPRO'],
                                        "CAP" => $Anamed_rec['MEDCAP'],
                                        "ROWID" => $Anamed_rec['ROWID']
                                    );
                                }
                            }
                            if (!$mittente_tab) {
                                Out::msgInfo("Ricerca Mittente", "Nessun mittente Trovato con questa mail.");
                                break;
                            }
                            praRic::praRicMittente($mittente_tab, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), $tipo);
                        } else {
                            proRic::proRicAnamed(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), $where, 'proAnamed', '3');
                        }
//                        $where = " WHERE MEDEMA = '" . $_POST[$this->nameForm . "_EMAIL_MITTENTE"] . "'";
//                        proRic::proRicAnamed(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), $where, 'proAnamed', '3');
                        break;
                    case $this->nameForm . '_PROPAS[PROCLT]_butt':
                        praRic::praRicPraclt(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "RICERCA Tipo Passo", "returnPraclt");
                        break;
                    case $this->nameForm . '_MailPreferita':
                        if (!$this->praLib->AggiornaMailPreferita($_POST[$this->nameForm . '_MITTENTE'], $this->rowidMail, $_POST[$this->nameForm . '_EMAIL_MITTENTE'])) {
                            Out::msgStop("Attenzione", "Aggiornamento mail preferita fallito su TABDAG");
                            break;
                        }
                        Out::msgBlock("", 3500, true, "La mail " . $_POST[$this->nameForm . '_EMAIL_MITTENTE'] . " è stata impostata come preferita per il mittente " . $_POST[$this->nameForm . '_DESC_MITTENTE']);
                        break;
                    case $this->nameForm . '_EMAIL_MITTENTE_butt':
                        $Anamed_rec = $this->proLib->GetAnamed($_POST[$this->nameForm . "_MITTENTE"]);
                        $ret = proRic::proRicTabdagMail(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "ANAMED", $Anamed_rec['ROWID'], "", "Il Mittente ha più Mail. Sceglierne una");
                        if ($ret) {
                            $Tabdag_rec = $this->proLib->GetTabdag($ret, 'rowid');
                            Out::valore($this->nameForm . '_EMAIL_MITTENTE', $Tabdag_rec['TDAGVAL']);
                        }
                        break;
                    case $this->nameForm . '_PROPAS[PROANN]_butt':
                        Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneNote");
                        break;
                    case $this->nameForm . '_BloccaAllegatiDoc':
                    case $this->nameForm . '_BloccaAllegatiProt':
                        $praFascicolo = new praFascicolo($this->currGesnum);
                        $praFascicolo->setChiavePasso($this->keyPasso, $this->tipoProtocollo);
                        $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione($this->tipoProtocollo, false, "P"); //non aggiungo filtri  non serve estrarre il base64
                        $arrayDocRicevute = $praFascicolo->getRicevutePartenza($this->tipoProtocollo, false); //non aggiungo filtri  non serve estrarre il base64
                        $arrayDocWsRicevute = array();
                        foreach ($arrayDocRicevute['pramail_rec'] as $ricevuta) {
                            $arrayDocWsRicevute[] = $ricevuta['ROWID'];
                            $msgInfo = "Attenzione! Ci sono " . count($arrayDocWsRicevute) . " ricevute, tra Accettazioni e Avvenute-Consegna, da protocollare.<br>Verranno protocollate anche se non verrà selezionato nessun allegato di seguito.";
                        }
                        //salvo i rowid di TUTTI gli allegati
                        if ($arrayDoc) {
                            $arrayDocWs = array();
                            foreach ($arrayDoc['pasdoc_rec'] as $documento) {
                                $arrayDocWs[] = $documento['ROWID'];
                            }

                            praRic::ricAllegatiWs($this->PRAM_DB, $arrayDocWs, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), 'returnAggiungiAllegatiWs', $msgInfo);
                        }
                        break;
                    case $this->nameForm . '_BloccaAllegatiArr':
                        $praFascicolo = new praFascicolo($this->currGesnum);
                        $praFascicolo->setChiavePasso($this->keyPasso);
                        $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione($this->tipoProtocollo, false, "A"); //non aggiungo filtri  non serve estrarre il base64
                        //salvo i rowid di TUTTI gli allegati
                        if ($arrayDoc) {
                            $arrayDocWs = array();
                            foreach ($arrayDoc['pasdoc_rec'] as $key => $documento) {
                                $arrayDocWs[] = $documento['ROWID'];
                            }
                            praRic::ricAllegatiWs($this->PRAM_DB, $arrayDocWs, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), 'returnAggiungiAllegatiWsArr');
                        }
                        break;
                    case $this->nameForm . '_ConfermaCla':
                        $Anacla_rec = $this->praLib->GetAnacla($_POST[$this->nameForm . '_CodiceCla']);
                        $this->passAlle[$_POST[$this->gridAllegati]['gridParam']['selrow']]["PASCLAS"] = $_POST[$this->nameForm . '_CodiceCla'];
                        $this->ConfermaQualificaAllegati('CLASSIFICAZIONE', $Anacla_rec['CLADES']);
                        break;
                    case $this->nameForm . '_ConfermaNote':
                        $this->ConfermaQualificaAllegati('PASNOTE', $_POST[$this->nameForm . '_Note']);
                        break;
                    case $this->nameForm . '_ConfermaCancAlle':
                        if (!$this->ControllaDataEdit()) {
                            break;
                        }
                        $RowidDoc = $this->passAlle[$this->rowidAppoggio]['ROWID'];
                        $ext = pathinfo($this->passAlle[$this->rowidAppoggio]['FILENAME'], PATHINFO_EXTENSION);
                        $propak = $_POST[$this->nameForm . "_PROPAS"]['PROPAK'];
                        if (array_key_exists($this->rowidAppoggio, $this->passAlle) == true) {
                            $basename = pathinfo($this->passAlle[$this->rowidAppoggio]['FILENAME'], PATHINFO_FILENAME);
                            if ($this->passAlle[$this->rowidAppoggio]['ROWID'] != 0) {
                                $delete_Info = 'Oggetto: Cancellazione allegato' . $this->passAlle[$this->rowidAppoggio]['PASFIL'];
                                if (!$this->deleteRecord($this->PRAM_DB, 'PASDOC', $this->passAlle[$this->rowidAppoggio]['ROWID'], $delete_Info)) {
                                    Out::msgStop("Errore", "Errore in cancellazione dell'allegato " . $this->passAlle[$this->rowidAppoggio]['PASFIL']);
                                    break;
                                } else {
                                    //
                                    //Aggiorno PROUTEEDIT e PRODATEEDIT
                                    //
                                    if (!$this->AggiornaDateEdit()) {
                                        break;
                                    }
                                }
                                if (strtolower($ext) == "pdf") {
                                    $Prodst_rec = $this->praLib->GetProdst($basename . ".info", "desc");
                                    $fileNameTIF = pathinfo($this->passAlle[$this->rowidAppoggio]['FILEPATH'], PATHINFO_DIRNAME) . "/" . pathinfo($this->passAlle[$this->rowidAppoggio]['FILENAME'], PATHINFO_FILENAME) . ".tif";
                                    if ($Prodst_rec) {
                                        $dstset = $Prodst_rec['DSTSET'];
                                        $delete_Info = "Oggetto: Cancellazione data set $dstset";
                                        if (!$this->deleteRecord($this->PRAM_DB, 'PRODST', $Prodst_rec['ROWID'], $delete_Info)) {
                                            Out::msgStop("Errore", "Errore in cancellazione del data set $dstset");
                                            break;
                                        }
                                        $sql = "SELECT * FROM PRODAG WHERE DAGSET = '$dstset' AND DAGPAK = '$propak'";
                                        $Prodag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                                        if ($Prodag_tab) {
                                            $delete_Info = "Oggetto: Cancellazione dati aggiuntivi del file $basename.pdf";
                                            foreach ($Prodag_tab as $key => $Prodag_rec) {
                                                if (!$this->deleteRecord($this->PRAM_DB, 'PRODAG', $Prodag_rec['ROWID'], $delete_Info)) {
                                                    Out::msgStop("Errore", "Errore in cancellazione dato aggiuntivo " . $Prodag_rec['DAGKEY']);
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $ultimo = $this->praLib->CheckUltimo($this->passAlle[$this->rowidAppoggio], $this->passAlle);
                            if ($ultimo == true) {
                                $keyPadre = $this->praLib->CheckPadre($this->passAlle[$this->rowidAppoggio], $this->passAlle, 'PROV');
                                if (array_key_exists($keyPadre, $this->passAlle) == true) {
                                    unset($this->passAlle[$keyPadre]);
                                }
                            }

                            if (strtolower($ext) == "xhtml" || strtolower($ext) == 'docx') {
                                $fileNamePdf = pathinfo($this->passAlle[$this->rowidAppoggio]['FILEPATH'], PATHINFO_DIRNAME) . "/" . pathinfo($this->passAlle[$this->rowidAppoggio]['FILENAME'], PATHINFO_FILENAME) . ".pdf";
                                $fileNameP7m = pathinfo($this->passAlle[$this->rowidAppoggio]['FILEPATH'], PATHINFO_DIRNAME) . "/" . pathinfo($this->passAlle[$this->rowidAppoggio]['FILENAME'], PATHINFO_FILENAME) . ".pdf.p7m";
                            }
                            if (!@unlink($this->passAlle[$this->rowidAppoggio]['FILEPATH'])) {
                                Out::msgStop("Gestione Acquisizioni", "Errore in cancellazione file.");
                                break;
                            }
                            if ($fileNamePdf) {
                                if (file_exists($fileNamePdf)) {
                                    if (!@unlink($fileNamePdf)) {
                                        Out::msgStop("Gestione Acquisizioni", "Errore in cancellazione file pdf.");
                                        break;
                                    }
                                }
                            }
                            if ($fileNameP7m) {
                                if (file_exists($fileNameP7m)) {
                                    if (!@unlink($fileNameP7m)) {
                                        Out::msgStop("Gestione Acquisizioni", "Errore in cancellazione file p7m.");
                                        break;
                                    }
                                }
                            }
                            if ($fileNameTIF) {
                                if (file_exists($fileNameTIF)) {
                                    if (!@unlink($fileNameTIF)) {
                                        Out::msgStop("Gestione Acquisizioni", "Errore in cancellazione file TIF.");
                                        break;
                                    }
                                }
                            }

                            if (in_array(strtolower($ext), array('xls', 'xlsx'))) {
                                $fileElaborato = $this->getFilenameFoglioElaborato($this->passAlle[$this->rowidAppoggio]['FILEPATH']);
                                $fileDefinitivo = $this->getFilenameFoglioElaborato($this->passAlle[$this->rowidAppoggio]['FILEPATH'], true);

                                if (file_exists($fileElaborato)) {
                                    if (!unlink($fileElaborato)) {
                                        Out::msgStop("Cancellazione Allegato", "Errore in cancellazione file elaborato.");
                                        break;
                                    }
                                }

                                if (file_exists($fileDefinitivo)) {
                                    if (!unlink($fileDefinitivo)) {
                                        Out::msgStop("Cancellazione Allegato", "Errore in cancellazione file definitivo.");
                                        break;
                                    }

                                    if (!$this->svuotaDatiExportFoglioDiCalcolo()) {
                                        break;
                                    }

                                    /*
                                     * Ricarico la subform dei dati aggiuntivi.
                                     */

                                    /* @var $praCompDatiAggiuntivi praCompDatiAggiuntivi */
                                    $praCompDatiAggiuntivi = itaModel::getInstance($this->praCompDatiAggiuntiviFormname[0], $this->praCompDatiAggiuntiviFormname[1]);
                                    $praCompDatiAggiuntivi->openGestione($this->currGesnum, $this->keyPasso);
                                }
                            }

                            unset($this->passAlle[$this->rowidAppoggio]);
                        }


                        if (!$this->ValorizzaProall()) {
                            Out::msgStop("Aggiornamento", "Errore aggiornamento campo PROALL");
                            break;
                        }
                        if ($this->flagAssegnazioniPasso) {
                            $this->togliAllaFirma($RowidDoc);
                        }

                        $this->ContaSizeAllegati($this->passAlle);
                        $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');
                        $this->bloccoAllegatiRiservati();
                        $this->BloccaDescAllegati();
                        if (strtolower($ext) == "pdf") {
//                            foreach ($this->altriDati as $keyPadre => $dato) {
//                                if ($basename . ".info" == $dato['DAGKEY']) {
//                                    $dagset = $dato['DAGSET'];
//                                    unset($this->altriDati[$keyPadre]);
//                                    break;
//                                }
//                            }
//                            foreach ($this->altriDati as $key => $dato) {
//                                if ($dagset == $dato['parent']) {
//                                    unset($this->altriDati[$key]);
//                                }
//                            }
                        }
                        break;
                    case $this->nameForm . '_ConfermaCancDest':
                        if (!$this->ControllaDataEdit()) {
                            break;
                        }
//
                        $dest = $this->destinatari[$this->rowidAppoggio];
                        if ($dest['ROWID'] != 0) {
                            $delete_Info = 'Oggetto: Cancellazione destinatario' . $dest['NOME'] . " del passo $this->keyPasso";
                            if (!$this->deleteRecord($this->PRAM_DB, 'PRAMITDEST', $dest['ROWID'], $delete_Info)) {
                                Out::msgStop("Cancellazione destinatario", "Errore nella cancellazione del destinatario: " . $dest['NOME']);
                                break;
                            }
//
//Aggiorno PROUTEEDIT e PRODATEEDIT
//
                            if (!$this->AggiornaDateEdit()) {
                                break;
                            }
                        }
                        unset($this->destinatari[$this->rowidAppoggio]);
                        $this->CaricaGriglia($this->gridAltriDestinatari, $this->destinatari, "1", "100");
                        break;
                    case $this->nameForm . '_ConfermaCancAgg':
                        if (!$this->ControllaDataEdit()) {
                            break;
                        }
//
                        $dagset = substr($this->rowidAppoggio, 0, 25);
                        $dagkey = substr($this->rowidAppoggio, 25);
                        $sql = "SELECT * FROM PRODAG WHERE DAGSET = '" . $dagset . "' AND DAGKEY = '" . $dagkey . "' AND DAGNUM = '" . $this->currGesnum . "'";
                        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                        if ($prodag_rec) {
                            $delete_Info = 'Oggetto: Cancellazione dato aggiuntivo' . $prodag_rec['DAGKEY'] . " del passo " . $prodag_rec['DAGPAK'];
                            if (!$this->deleteRecord($this->PRAM_DB, 'PRODAG', $prodag_rec['ROWID'], $delete_Info)) {
                                Out::msgStop("Cancellazione dati Aggiuntivi File", "Errore nella cancellazione del dato: " . $dagkey);
                                break;
                            }
//
//Aggiorno PROUTEEDIT e PRODATEEDIT
//
                            if (!$this->AggiornaDateEdit()) {
                                break;
                            }
                        }

//                        if ($this->altriDati == false) {
//                            Out::show($this->nameForm . '_InviaProcedura');
//                        }
                        break;
                    case $this->nameForm . '_Chiudi_E_New':
                    case $this->nameForm . '_Chiudi_E_Act':
                        if ($this->flagAssegnazioniPasso) {
                            /*
                             * quando chiudo il passo, verifico se ci sono iter ad esso collegati
                             */
                            $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
                            $Controllo = $this->ChiudiTrasmissioniProtocolloPasso($propas_rec, false, true);
                            if (!$Controllo) {
                                Out::msgStop('ATTENZIONE', 'Non è possibile Chiudere il Passo<br>Sono presenti Trasmissioni Aperte chiudere Trasmissioni');
                                break;
                            }
                        }
                        $_POST[$this->nameForm . '_PROPAS']['PROFIN'] = $this->workDate;
                        Out::valore($this->nameForm . '_PROPAS[PROFIN]', $this->workDate);
                        if (!$this->AggiornaRecord()) {
                            Out::msgStop("Attenzione!!!!", "Chiusura Passo Fallito");
                            break;
                        }

                        $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
                        switch ($_POST['id']) {
                            case $this->nameForm . '_Chiudi_E_New':
                                if ($propas_rec['PROKPRE']) {
                                    $propas_padre_rec = $this->praLib->GetPropas($propas_rec['PROKPRE'], 'propak', false);
                                    if ($propas_padre_rec) {
                                        $propas_rec = $propas_padre_rec;
                                        $this->keyPasso = $propas_rec['PROPAK'];
                                        if ($_POST['id'] !== $this->nameForm . '_Chiudi_E_New') {
                                            Out::msgInfo("Attenzione", "<span style=\"font-weight:bold;\">Hai chiuso un sotto passo e stai rientrando nel passo antecedente.</span>");
                                        }
                                    }
                                }
                                $this->iniDateEdit = $propas_rec['PRODATEEDIT'];
                                $this->Dettaglio($this->keyPasso);
                                break;
                            case $this->nameForm . '_Chiudi_E_Act':
                                $nextPropas_rec = $this->praLib->getNextPropas($this->keyPasso);
                                if ($nextPropas_rec) {
                                    $nextPropas_rec['PROINI'] = date('Ymd');
                                    $update_Info = "Oggetto: Attivazione passocon seq.: {$nextPropas_rec['PROSEQ']} da chiusura passo precedente con seq.: {$propas_rec['PROSEQ']}";
                                    if (!$this->updateRecord($this->PRAM_DB, 'PROPAS', $nextPropas_rec, $update_Info)) {
                                        return false;
                                    }
                                    Out::msgBlock($this->nameForm, 3000, true, "<b>Chiuso passo con sequenza " . $propas_rec['PROSEQ'] . " e<br>attivato passo con sequenza " . $nextPropas_rec['PROSEQ'] . "</b>");
                                    $this->returnToParent(true);
                                }
                                break;
                        }

                        if ($_POST['id'] == $this->nameForm . '_Chiudi_E_New') {
                            praRic::praPassiNonAperti($this->praPassi, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "expPra", "Scegli passo da aprire", "PROPAS");
                        }
                        break;

                    case $this->nameForm . '_Cancella':
                        $propas_rec = $this->praLib->GetPropas($_POST[$this->nameForm . '_PROPAS']['ROWID'], "rowid");

                        /*
                         * Verifica Antecedente
                         */
                        $retAntecedente = $this->praLib->CheckAntecedente($propas_rec);
                        if (!$retAntecedente) {
                            Out::msgInfo("Cancellazione!!!", "Il Passo risulta essere un antecedente.<br>Cancellare prima i passi collegati");
                            break;
                        }

                        /*
                         * Controllo se allegato fa parte della conferenza dei servizi, non lo posso cancellare
                         */
                        $processoIniziato = $this->checkFirmaCds($propas_rec['PROFLCDS']);
                        if ($processoIniziato === true) {
                            Out::msgInfo("Cancellazione Passo", "Impossibile cancellare il passo perche ci sono allegati in Conferenza di Servizi.");
                            break;
                        }

                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaCancGruppo':
                        if (!$this->ControllaDataEdit()) {
                            return false;
                        }
//
                        $sql = "SELECT * FROM PRODAG WHERE DAGPAK = '" . $this->keyPasso . "' AND DAGSET = '" . $this->rowidAppoggio . "' AND DAGNUM = '" . $this->currGesnum . "'";
                        $prodag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

//
//Cancello dati aggiuntivi da db
//
                        $err = false;
                        foreach ($prodag_tab as $key => $prodag_rec) {
                            $dagset = $prodag_rec['DAGSET'];
                            $delete_Info = 'Oggetto: Cancellazione dato aggiuntivo' . $prodag_rec['DAGKEY'] . " del passo " . $prodag_rec['DAGPAK'];
                            if (!$this->deleteRecord($this->PRAM_DB, 'PRODAG', $prodag_rec['ROWID'], $delete_Info)) {
                                $err = true;
                                break;
                            }
                        }
                        if ($err == false) {
//
//Aggiorno PROUTEEDIT e PRODATEEDIT
//
                            if (!$this->AggiornaDateEdit()) {
                                break;
                            }
                        }

//
//cancello data set
//
                        $Prodst_set = $this->praLib->GetProdst($dagset);
                        $delete_Info = "Oggetto: Cancellazione data set $dagset";
                        if (!$this->deleteRecord($this->PRAM_DB, 'PRODST', $Prodst_set['ROWID'], $delete_Info)) {
                            break;
                        }

//
//Cancello dati aggiuntivi da array
//
//                        if ($this->altriDati == false) {
//                            Out::show($this->nameForm . '_InviaProcedura');
//                        }
                        break;
                    case $this->nameForm . '_ConfermaGenPdf':
                        $doc = $this->passAlle[$this->rowidAppoggio];

                        $codicePratica = $_POST[$this->nameForm . '_PROPAS']['PRONUM'];
                        $chiavePasso = $_POST[$this->nameForm . '_PROPAS']['PROPAK'];
                        if (!$this->praLibAllegati->GeneraPDF($doc, $codicePratica, $chiavePasso)) {
                            Out::msgStop('Errore', $this->praLibAllegati->getErrMessage());
                            break;
                        }

//
//Ricarico la tabella con lo stato aggiornato
//
                        $this->passAlle[$doc['PROV']]['STATO'] = "PDF generato. Clicca sull'icona per allegare il file firmato";
                        $this->passAlle[$doc['PROV']]['PREVIEW'] = "<span class=\"ita-icon ita-icon-pdf-24x24\" title=\"PDF Generato\"></span>";
                        $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');
                        $this->BloccaDescAllegati();
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $rowidPropas = $_POST[$this->nameForm . '_PROPAS']['ROWID'];
                        $continua = true;
                        if ($this->flagAssegnazioniPasso) {
                            $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
                            $Controllo = $this->ChiudiTrasmissioniProtocolloPasso($propas_rec, false, true);
                            if (!$Controllo) {
                                Out::msgStop('ATTENZIONE', 'Non è possibile Cancellare il Passo<br>Sono presenti Trasmissioni Aperte chiudere Trasmissioni');
                                break;
                            }
                        }
                        foreach ($this->passAlle as $key => $allegato) {
                            if ($allegato['ROWID'] != 0) {
                                $delete_Info = 'Oggetto: Cancellazione allegato' . $allegato['FILENAME'] . " - " . $allegato['FILEINFO'];
                                if (!$this->deleteRecord($this->PRAM_DB, 'PASDOC', $allegato['ROWID'], $delete_Info)) {
                                    $continua = false;
                                    break;
                                }
                                if ($this->flagAssegnazioniPasso) {
                                    $this->togliAllaFirma($allegato['ROWID']);
                                }
                            }
                        }
                        if (!$continua) {
                            break;
                        }

                        foreach ($this->passCom as $key => $Pracom_rec) {
                            $delete_Info = "Oggetto: Cancellazione comunicazione n. " . $Pracom_rec['ROWID'] . " del passo " . $Pracom_rec['COMPAK'];
                            if (!$this->deleteRecord($this->PRAM_DB, 'PRACOM', $Pracom_rec['ROWID'], $delete_Info)) {
                                $continua = false;
                                break;
                            }
                        }
                        if (!$continua) {
                            break;
                        }

                        if ($this->destinatari) {
                            foreach ($this->destinatari as $pramitdest_rec) {
                                if ($pramitdest_rec['ROWID']) {
                                    if (!$this->deleteRecord($this->PRAM_DB, 'PRAMITDEST', $pramitdest_rec['ROWID'], '')) {
                                        break 2;
                                    }
                                }
                            }
                        }

                        /* @var $praCompDatiAggiuntivi praCompDatiAggiuntivi */
                        $praCompDatiAggiuntivi = itaModel::getInstance($this->praCompDatiAggiuntiviFormname[0], $this->praCompDatiAggiuntiviFormname[1]);
                        if (!$praCompDatiAggiuntivi->cancellaDati()) {
                            break;
                        }

                        foreach ($this->noteManager->getNote() as $nota) {
                            if ($this->noteManager->cancellaNota($nota) === false) {
                                break 2;
                            }
                        }

                        $this->noteManager->salvaNote();

                        $delete_Info = "Oggetto: Cancellazione passo con seq " . $_POST[$this->nameForm . '_PROPAS']['PROSEQ'] . " e chiave " . $_POST[$this->nameForm . '_PROPAS']['PROPAK'];
                        if ($this->deleteRecord($this->PRAM_DB, 'PROPAS', $_POST[$this->nameForm . '_PROPAS']['ROWID'], $delete_Info)) {
                            $procedimento = $_POST[$this->nameForm . '_PROPAS']['PRONUM'];
                            if (!$this->praLib->ordinaPassi($procedimento)) {
                                Out::msgStop("Errore", $this->praLib->getErrMessage());
                            }
                            if (!$this->praLib->sincronizzaStato($procedimento)) {
                                Out::msgStop("Errore", $this->praLib->getErrMessage());
                            }
//
//Cancello gli eventi e i promemoria del passo
//
                            $envLibCalendar = new envLibCalendar();
                            $event_tab = $envLibCalendar->getAppEvents("PASSI_SUAP", $rowidPropas);
                            if ($event_tab) {
                                foreach ($event_tab as $event_rec) {
                                    $envLibCalendar->deletePromemoriaFromEvent($event_rec["ROWID"]);
                                }
                                if (!$envLibCalendar->deleteEventApp("PASSI_SUAP", $rowidPropas, false)) {
                                    Out::msgStop("Errore", "Attenzione cancellazione evento su calendario fallita per il passo.");
                                    return false;
                                }
                            }

                            $this->returnToParent();
                        }
                        break;

                    case $this->nameForm . '_Svuota':
                        Out::valore($this->nameForm . '_Destinazione', '');
                        Out::valore($this->nameForm . '_DescrizioneVai', '');
                        Out::valore($this->nameForm . '_PROPAS[PROVPA]', '');
                        break;
                    case $this->nameForm . '_Risposta':
                        if (!itaLib::createPrivateUploadPath()) {
                            Out::msgStop("Gestione Arrivo da PEC", "Creazione ambiente di lavoro temporaneo fallita");
                            return false;
                        }
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUploadRisposta";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_Invia':
                        $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
                        $Metadati = $this->praLib->GetMetadatiPracom($pracomP_rec['ROWID'], 'rowid');

                        $proObject = proWsClientFactory::getInstance();
                        if (!$proObject) {
                            Out::msgStop("Importazione pratica da protocollo", "Errore inizializzazione driver protocollo");
                            return false;
                        }

                        if (($proObject->getClientType() == proWsClientHelper::CLIENT_JIRIDE || $proObject->getClientType() == proWsClientHelper::CLIENT_PALEO4) && $Metadati['DatiProtocollazione']['idMail']) {
                            Out::msgInfo("Invio Mail", "Invio mail già effettuato tramite $this->tipoProtocollo");
                            break;
                        }

                        /*
                         * se invio con paleo4 chiedo confermo  e invio perchè prende tutti dal protocollo
                         * quindi non serve la nostra utiGestMail
                         */
                        $Proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $anatsp_rec = $this->praLib->GetAnatsp($Proges_rec['GESTSP']);
                        if ($anatsp_rec['TSPSENDREMOTEMAIL'] && proWsClientHelper::CLIENT_PALEO4) {
                            if ($_POST[$this->nameForm . '_PROTRIC_DESTINATARIO']) {
                                Out::msgQuestion("Invio Mail!", "Confermi l'invio della pec con il protocollo $this->tipoProtocollo?", array(
                                    'Annulla' => array('id' => $this->nameForm . '_AnnullaInvioMailDaWs', 'model' => $this->nameForm),
                                    'Conferma' => array('id' => $this->nameForm . '_ConfermaInvioMailDaWs', 'model' => $this->nameForm)
                                        )
                                );
                            } else {
                                Out::msgInfo("Attenzione", "Attivato invio mail con Paleo4.<br>Si prega prima di protocollare.");
                            }
                            break;
                        }

                        if (!$this->ControllaDati()) {
                            break;
                        }

                        if ($this->destinatari) {
                            if ($this->AggiornaRecord()) {
                                $this->CaricaAllegati($this->keyPasso);
                                $this->RegistraDestinatari();
                                $this->CaricaDestinatari($this->keyPasso);
                                $this->AggiornaPartenzaDaPost();
                                $this->AggiornaArrivo();
                            } else {
                                break;
                            }


                            $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
                            $this->iniDateEdit = $propas_rec['PRODATEEDIT'];

                            $valori['PRACOM'] = $_POST[$this->nameForm . '_PRACOM'];
                            $valori['Destinatari'] = $this->GetDestinatariInvio();
                            $key = count($valori['Destinatari']);

                            $valori['ProtRic'] = $_POST[$this->nameForm . '_PROTRIC_DESTINATARIO'];
                            $valori['Anno'] = $_POST[$this->nameForm . '_ANNOPROT_DESTINATARIO'];
                            $valori['DataProt'] = $_POST[$this->nameForm . '_DATAPROT_DESTINATARIO'];
                            $valori['OggettoProt'] = $this->praLib->GetOggettoProtPartenza($this->currGesnum, $this->keyPasso);
                            $valori['Corpo'] = $this->GetCorpoMail($propas_rec);
                            $valori['Oggetto'] = $this->GetOggettoMailPartenza();
                            if ($valori['Oggetto'] == "") {
                                $valori['Oggetto'] = $_POST[$this->nameForm . '_PROPAS']['PRODPA'];
                            }
                            $valori['Procedimento'] = $_POST[$this->nameForm . '_PROPAS']['PRONUM'];
                            $valori['Seq'] = $_POST[$this->nameForm . '_PROPAS']['PROSEQ'];
                            $valori['Note'] = $_POST[$this->nameForm . '_NOTE_DESTINATARIO'];
                            $valori['rowidChiamante'] = $_POST[$this->nameForm . '_PROPAS']['ROWID'];
                            $propak = $_POST[$this->nameForm . "_PROPAS"]['PROPAK'];
                            $_POST = array();
                            $_POST['tipo'] = 'passo';
                            $_POST['valori'] = $valori;
                            $_POST['allegati'] = $this->cleanTestiBase($this->praLib->cleanArrayTree($this->passAlle)); //, $propak);
                            $_POST['sizeAllegati'] = 80;
                            $_POST['returnModel'] = $this->nameForm;
                            $_POST['returnEvent'] = 'returnMail';
                            $_POST['event'] = 'openform';
                            $model = 'utiGestMail';
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                        } else {
                            Out::msgStop('ATTENZIONE!!', "Destinatari non Presenti");
                            break;
                        }
                        break;
                    case $this->nameForm . '_UploadFileEsterno':
                        $acq_model = 'utiAcqrMen';
                        Out::closeDialog($acq_model);
                        $_POST = array();
                        $_POST[$acq_model . '_flagPDFA'] = $this->praLib->getFlagPDFA();
                        $_POST[$acq_model . '_returnModel'] = $this->nameForm;
                        $_POST[$acq_model . '_returnField'] = $this->nameForm . '_CaricaGridAllegati';
                        $_POST[$acq_model . '_returnMethod'] = 'returnAcqrList';
                        $_POST[$acq_model . '_title'] = 'Allegati al Passo del Procedimento';
                        $_POST[$acq_model . '_tipoNome'] = 'original';
                        $_POST['event'] = 'openform';
                        itaLib::openForm($acq_model);
                        $appRoute = App::getPath('appRoute.' . substr($acq_model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $acq_model . '.php';
                        $acq_model();
                        break;
                    case $this->nameForm . '_UploadFileInterno':
                    case $this->nameForm . '_Interno':
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $arrayButton = array(
                            'Tutti del fascicolo' => array('id' => $this->nameForm . '_ConfermaTutti', "style" => "width:250px;height:40px;", 'model' => $this->nameForm),
                            'Da endoprocedimento del fascicolo' => array('id' => $this->nameForm . '_ConfermaEsterni', "style" => "width:250px;height:40px;", 'model' => $this->nameForm),
                            'Da Altra Pratica' => array('id' => $this->nameForm . '_DaAltraPratica', "style" => "width:250px;height:40px;", 'model' => $this->nameForm),
                        );
                        if ($proges_rec['GESPRE']) {
                            $arrayButton["Da Pratica Antecedente"] = array('id' => $this->nameForm . '_DaAntecedente', "style" => "width:250px;height:40px;", 'model' => $this->nameForm);
                        }
                        if ($proges_rec['GESPRA']) {
                            $arrayButton["Da richiesta on-line"] = array('id' => $this->nameForm . '_ConfermaInterni', "style" => "width:250px;height:40px;", 'model' => $this->nameForm);
                        }
                        Out::msgQuestion("VISUALIZZAZIONE ALLEGATI INTERNI!", "Scegli gli allegati interni da visualizzare", $arrayButton, 'auto', 'auto', 'true', false, true, true);
                        break;
                    case $this->nameForm . '_DaAntecedente':
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        $this->CercaDocumentiDaPratica($proges_rec['GESPRE']);
                        break;
                    case $this->nameForm . '_DaAltraPratica':
                        praRic::praRicProges(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), " AND GESNUM<>$this->currGesnum");
                        break;
                    case $this->nameForm . '_ScaricaZip':
                        $this->praLibAllegati->ScaricaAllegatiZipPratica($_POST[$this->nameForm . '_PROPAS']['PROPAK'], 'Passo_seq.' . $_POST[$this->nameForm . '_PROPAS']['PROSEQ']);
                        break;
                    case $this->nameForm . '_Composizione':
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        if (!$this->praPerms->checkSuperUser($proges_rec)) {
                            $Utenti_rec = $this->praLib->GetUtente(App::$utente->getKey('idUtente'), 'codiceUtente');
                            $whereVisibilita = $this->praLib->GetWhereVisibilitaSportello();
                            $where = " ( PROUTEEDIT=''
                                     OR PROVISIBILITA='Aperto'
                                     OR PROVISIBILITA='Protetto'
                                     OR PROVISIBILITA='soloPasso'
                                     OR (PROVISIBILITA='Privato' AND PROUTEEDIT='" . App::$utente->getKey('nomeUtente') . "')
                                     OR PRORPA = '" . $Utenti_rec['UTEANA__3'] . "'
                                    )" . $whereVisibilita;
                        } else {
                            $where = "1";
                        }
                        $this->CercaDocumentiInterni($where, true, true);
                        break;
                    case $this->nameForm . '_ConfermaTutti':
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        if (!$this->praPerms->checkSuperUser($proges_rec)) {
                            $Utenti_rec = $this->praLib->GetUtente(App::$utente->getKey('idUtente'), 'codiceUtente');
                            $whereVisibilita = $this->praLib->GetWhereVisibilitaSportello();
                            $where = " ( PROUTEEDIT=''
                                     OR PROVISIBILITA='Aperto'
                                     OR PROVISIBILITA='Protetto'
                                     OR PROVISIBILITA='soloPasso'
                                     OR (PROVISIBILITA='Privato' AND PROUTEEDIT='" . App::$utente->getKey('nomeUtente') . "')
                                     OR PRORPA = '" . $Utenti_rec['UTEANA__3'] . "'
                                    )" . $whereVisibilita;
                        } else {
                            $where = "1";
                        }
                        $this->CercaDocumentiInterni($where, true);
                        break;
                    case $this->nameForm . '_ConfermaEsterni':
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        if (!$this->praPerms->checkSuperUser($proges_rec)) {
                            $Utenti_rec = $this->praLib->GetUtente(App::$utente->getKey('idUtente'), 'codiceUtente');
                            $whereVisibilita = $this->praLib->GetWhereVisibilitaSportello();
                            $where = "     PROPUB = 0
                                       AND (
                                            PROUTEEDIT=''
                                            OR PROVISIBILITA='Aperto'
                                            OR PROVISIBILITA='Protetto'
                                            OR PROVISIBILITA='soloPasso'
                                            OR (PROVISIBILITA='Privato' AND PROUTEEDIT='" . App::$utente->getKey('nomeUtente') . "')
                                            OR PRORPA = '" . $Utenti_rec['UTEANA__3'] . "'
                                        )" . $whereVisibilita;
                        } else {
                            $where = "PROPUB = 0";
                        }
                        $this->CercaDocumentiInterni($where);
                        break;
                    case $this->nameForm . '_ConfermaInterni':
                        $passi_tab = $this->praLib->getPropasTab($this->currGesnum, "PROSEQ");
                        foreach ($passi_tab as $passi_rec) {
                            if ($passi_rec['PRODRR'] == 1) {
                                $passoUploadRapporto = current($passi_tab);
                                break;
                            }
                        }
                        $where = " (PROPUB = 1 AND
                                  (PROIDR = 0 AND (PROUPL = 1 OR PROMLT = 1)) OR
                                   PROPAK = '{$passoUploadRapporto['PROPAK']}') ";
                        $this->CercaDocumentiInterni($where);
                        break;
                    case $this->nameForm . '_UploadTestoBase':
                    case $this->nameForm . '_TestoBase':
                        $propas_rec = $this->praLib->GetPropas($_POST[$this->nameForm . "_PROPAS"]['PROPAK'], 'propak');
                        $documento = $this->docLib->getDocumenti($propas_rec['PROTBA'], 'codice');
                        $caricato = false;
                        foreach ($this->passAlle as $alle) {
                            if ($alle['PROVENIENZA'] == "TESTOBASE" && ($alle['FILENAME'] == $documento['URI'] || $alle['FILENAME'] == $propas_rec['PROTBA'])) {
                                $caricato = true;
                                break;
                            }
                        }

                        if ($propas_rec['ROWID_DOC_CLASSIFICAZIONE'] != 0) {
                            Out::msgQuestion("Scegli il tipo di import desiderato!", "Importa Testo Base Da", array(
                                'F4-Documento Classificato' => array('id' => $this->nameForm . '_ConfermaDocClassif', 'model' => $this->nameForm, 'shortCut' => "f4"),
                                'F6-Anagrafica Classificazione' => array('id' => $this->nameForm . '_ConfermaAnagClassif', 'model' => $this->nameForm, 'shortCut' => "f6"),
                                'F5-Testo Associato' => array('id' => $this->nameForm . '_ConfermaTestoBase', 'model' => $this->nameForm, 'shortCut' => "f5"),
                                'F7-Anagrafita Doc' => array('id' => $this->nameForm . '_AnnullaTestoBase', 'model' => $this->nameForm, 'shortCut' => "f7")
                                    )
                            );
                        } elseif ($propas_rec['PROTBA'] == "" || $caricato == true) {
                            docRic::docRicDocumenti($this->nameFormOrig, " WHERE CLASSIFICAZIONE = 'PRATICHE' AND (TIPO = 'XHTML' OR TIPO = 'DOCX') AND FUNZIONE != 'VARIABILE'");
                        } else {
                            Out::msgQuestion("ATTENZIONE!", "E' stato trovato un Testo Base inerente a questo passo.<br>Lo vuoi caricare?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaTestoBase', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaTestoBase', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_TestoAssociato':
                        $procedimento = $_POST[$this->nameForm . "_PROPAS"]['PROPRO'];
                        Out::msgQuestion("Upload testo associato", "Scegli il tipo di ricerca per il testo", array(
                            'F4-Scegli Procedimento' => array('id' => $this->nameForm . '_SiSscegliProc', 'model' => $this->nameForm, 'shortCut' => "f4"),
                            'F5-Procedimento in corso' => array('id' => $this->nameForm . '_SiSingoloProc', 'model' => $this->nameForm, 'shortCut' => "f5"),
                            'F8-Tutti' => array('id' => $this->nameForm . '_NoTuttiProc', 'model' => $this->nameForm, 'shortCut' => "f8")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaTestoBase':
                        $propas_rec = $this->praLib->GetPropas($_POST[$this->nameForm . "_PROPAS"]['PROPAK'], 'propak');
                        if (!$propas_rec['PROTBA']) {
                            Out::msgStop('Attenzione', 'Nessun Testo Base Associato al passo da Anagrafica');
                            break;
                        }
                        $this->passAlle = $this->praLib->caricaTestoBase_Generico($this->keyPasso, $this->passAlle, $propas_rec['PROTBA']);
                        $this->ContaSizeAllegati($this->passAlle);
                        $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');
                        $this->bloccoAllegatiRiservati();
                        $this->BloccaDescAllegati();

//$this->caricaTestoBase($propas_rec['PROTBA']);
                        break;
                    case $this->nameForm . '_AnnullaTestoBase':
                        docRic::docRicDocumenti(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), " WHERE CLASSIFICAZIONE = 'PRATICHE' AND (TIPO = 'XHTML' OR TIPO = 'DOCX') AND FUNZIONE != 'VARIABILE'");
                        break;
                    case $this->nameForm . '_ConfermaAnagClassif':
                        $model = 'docClasDocumenti';
                        itaLib::openDialog($model);
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setReturnModel($this->nameForm);
                        $modelObj->setReturnEvent('returnClassificazioneDocumenti');
                        $modelObj->setEvent('openform');
                        $modelObj->setModalita('readonly');
                        $modelObj->setReturnId();
                        $modelObj->parseEvent();
                        break;
                    case $this->nameForm . '_ConfermaDocClassif':
                        $this->caricaTestoClass();
                        break;
                    case $this->nameForm . '_PROPAS[PROSTATO]_butt':
                        praRic::praRicAnastp(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), $where, $this->nameForm . '_PROPAS[PROSTATO]');
                        break;
                    case $this->nameForm . '_ProtocollaPartenza':

                        /*
                         * Controllo se l'aggregato ha un altro tipo di protocollo
                         */
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        if ($proges_rec['GESSPA'] != 0) {
                            $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
                            if ($anaspa_rec['SPATIPOPROT']) {
                                $this->tipoProtocollo = $anaspa_rec['SPATIPOPROT'];
                            }
                        }
                        $this->mettiAllaFirma = "";
                        $proObject = proWsClientFactory::getInstance($this->tipoProtocollo);
                        if (!$proObject) {
                            Out::msgStop("Importazione pratica da protocollo", "Errore inizializzazione driver protocollo");
                            return false;
                        }
                        $arrayButton = $arrayCampi = array();
                        if (is_object($proObject)) {
                            switch ($proObject->getClientType()) {
                                case proWsClientHelper::CLIENT_ITALPROT:
                                    $class = "";
                                    $label = 'Metti Alla Firma';
                                    $filent_rec = $this->praLib->GetFilent(50);
                                    if ($filent_rec['FILVAL'] == 1) {
                                        $class = "ita-hidden";
                                        $label = "";
                                    }
                                    $arrayCampi = array(
                                        'label' => array('style' => "width:100px;", 'value' => 'Metti Alla Firma'),
                                        'id' => $this->nameForm . '_MettiAllaFirma',
                                        'name' => $this->nameForm . '_MettiAllaFirma',
                                        'type' => 'checkbox',
                                        'class' => $class,
                                    );
                                    $arrayButton = array(
//'Metti alla Firma' => array('id' => $this->nameForm . '_ConfermaMettiAllaFirma', 'model' => $this->nameForm),
                                        'Protocolla Partenza' => array('id' => $this->nameForm . '_ConfermaProtPartenza', 'model' => $this->nameForm),
                                        'Documento Formale' => array('id' => $this->nameForm . '_ConfermaDocumentoFormale', 'model' => $this->nameForm)
                                    );
                                    break;
                                case proWsClientHelper::CLIENT_JIRIDE:
                                    /*
                                     * inserito array con almeno un campo nascosto perchè l'array vuoto dei campi crea problemi
                                     */
                                    $arrayCampi = array(
                                        'id' => $this->nameForm . '_campoVuoto',
                                        'type' => 'hidden',
                                    );
                                    $arrayButton = array(
                                        'Protocolla Partenza' => array('id' => $this->nameForm . '_ConfermaProtPartenza', 'model' => $this->nameForm),
                                        'Metti alla Firma' => array('id' => $this->nameForm . '_ConfermaMettiAllaFirma', 'model' => $this->nameForm),
                                    );
                                    break;
                                case proWsClientHelper::CLIENT_PALEO4:
                                    /*
                                     * inserito array con almeno un campo nascosto perchè l'array vuoto dei campi crea problemi
                                     */
                                    $arrayCampi = array(
                                        'id' => $this->nameForm . '_campoVuoto',
                                        'type' => 'hidden',
                                    );
                                    $arrayButton = array(
                                        'Protocolla Partenza' => array('id' => $this->nameForm . '_ConfermaProtPartenza', 'model' => $this->nameForm),
                                        'Documento Formale' => array('id' => $this->nameForm . '_ConfermaMettiAllaFirma', 'model' => $this->nameForm)
                                    );
                                    break;
                            }
                        }

                        if ($arrayButton) {
                            Out::msgInput("Quale operazione vuoi effettuare?", $arrayCampi, $arrayButton, $this->nameForm, 'auto', 'auto', true, '');
                            break;
                        }
                    case $this->nameForm . '_ConfermaProtPartenza':
                        if ($_POST[$this->nameForm . '_MettiAllaFirma'] == 1) {
                            $this->mettiAllaFirma = "true";
                        }
                        if ($_POST[$this->nameForm . '_PROPAS']['PRORPA'] == "") {
                            Out::msgStop("Protocollazione Pratica", "Responsabile Passo non Presente");
                            break;
                        }

                        if (!$this->destinatari) {
                            Out::msgInfo("Protocollazione Pratica", "Destinatari non presenti.<br>Inserire almeno un destinatario.");
                            break;
                        }
//chiede la conferma prima della protocollazione
                        Out::msgQuestion("ATTENZIONE!", "L'operazione protocollerà la pratica con procedura " . $this->tipoProtocollo . ". Vuoi continuare?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaProtocollazionePartenza', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaProtocollazionePartenza', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaProtocollazionePartenza':
                        if (!$this->ControllaDati()) {
                            break;
                        }

                        $this->AggiornaPartenzaDaPost();
                        $this->RegistraDestinatari();
                        $this->AggiornaArrivo();
                        if (!$this->AggiornaRecord()) {
                            break;
                        }
//
                        if ($_POST[$this->nameForm . '_PROTRIC_DESTINATARIO'] != '') {
                            Out::msgStop("Protocolla in partenza", "Protocollo già inserito");
                            break;
                        }
//                        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
                        if ($this->tipoProtocollo == 'Italsoft-remoto') {
                            $accLib = new accLib();
                            $utenteWs = $accLib->GetUtenteProtRemoto(App::$utente->getKey('idUtente'));
                            if (!$utenteWs) {
                                Out::msgStop("Protocollo Remoto", "Utente remoto non definito!");
                                break;
                            }
                            $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $this->keyPasso . "' AND COMTIP='P'", false);
                            $model = 'utiIFrame';
                            $_POST = array();
                            $_POST['event'] = 'openform';
                            $_POST['returnModel'] = $this->nameForm;
                            $_POST['returnEvent'] = 'returnIFrame';
                            $_POST['retid'] = $this->nameForm . '_protocollaRemotoPartenza';
                            $envLibProt = new envLibProtocolla();
                            $url_param = $envLibProt->getParametriProtocolloRemoto();
//$devLib = new devLib();
//$parametro = $devLib->getEnv_config('ITALSOFTPROTREMOTO', 'codice', 'URLREMOTO', false);
//$url_param = $parametro['CONFIG'];
                            $_POST['src_frame'] = $url_param . "&access=direct&accessreturn=&accesstoken=nobody&model=menDirect&menu=PR_HID&prog=PR_WSPRA&topbar=0&homepage=0&noSave=1&utenteWs=" . $utenteWs . "&azione=CP&passo=" . $pracomP_rec['ROWID'];
                            $_POST['title'] = "Protocollazione Remota Comunicazione in Partenza";
                            $_POST['returnKey'] = 'protocollaWS';
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                            break;
                        } elseif ($this->tipoProtocollo == 'Italsoft') {
                            $elementi = $this->protocollaPartenza();
                            $propas_rowid = $_POST[$this->nameForm . '_PROPAS']['ROWID'];
                            $_POST = Array();
                            $model = 'proItalsoft.class';
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $proItalsoft = new proItalsoft();
                            $valore = $proItalsoft->protocollazione($elementi);
                            if ($valore['status'] === true) {
                                $propas_rec = $this->praLib->GetPropas($propas_rowid, 'rowid');
                                $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                                Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', substr($valore['value'], 4));
                                Out::valore($this->nameForm . '_ANNOPROT_DESTINATARIO', substr($valore['value'], 0, 4));
                                Out::valore($this->nameForm . '_DATAPROT_DESTINATARIO', $this->workDate);
                                $pracom_rec = array();
                                $pracom_rec['ROWID'] = $pracomP_rec['ROWID'];
                                $pracom_rec['COMPRT'] = $valore['value'];
                                $pracom_rec['COMDPR'] = $this->workDate;
                                $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                                if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                                    Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                                    break;
                                }
                                Out::msgBlock('', 3000, false, "Protocollazione avvenuta con successo al n. " . substr($valore['value'], 4));
                                $MetadatiPartenza = array();
                                $this->switchIconeProtocolloP($pracom_rec['COMPRT'], $MetadatiPartenza);
                                $this->Dettaglio($this->keyPasso, "propak");
                            } else {
                                Out::msgStop("Errore in Protocollazione", $valore['msg']);
                            }
                            break;
                        }

                        switch ($this->tipoProtocollo) {
                            case 'Paleo4':
                                $tipoWs = 'Paleo4';
                                break;
                            case 'Paleo':
                                $tipoWs = 'Paleo';
                                break;
                            case 'WSPU':
                                $tipoWs = 'WSPU';
                                break;
                            case 'Infor':
                                $tipoWs = 'Infor';
                                break;
                            case 'Iride':
                                $tipoWs = 'Iride';
                                break;
                            case 'Jiride':
                                $tipoWs = 'Jiride';
                                break;
                            case 'HyperSIC':
                                $tipoWs = 'HyperSIC';
                                break;
                            case 'Italsoft-ws':
                                $tipoWs = 'Italsoft-ws';
                                break;
                            default:
                                $tipoWs = '';
                                break;
                        }
                        $praFascicolo = new praFascicolo($this->currGesnum);
                        $praFascicolo->setChiavePasso($this->keyPasso);
                        $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione($tipoWs, false, "P"); //non aggiungo filtri  non serve estrarre il base64
                        $arrayDocRicevute = $praFascicolo->getRicevutePartenza($this->tipoProtocollo, false); //non aggiungo filtri  non serve estrarre il base64
                        $arrayDocWsRicevute = array();
                        foreach ($arrayDocRicevute['pramail_rec'] as $ricevuta) {
                            $arrayDocWsRicevute[] = $ricevuta['ROWID'];
                            $msgInfo = "Attenzione! Ci sono " . count($arrayDocWsRicevute) . " ricevute, tra Accettazioni e Avvenute-Consegna, da protocollare.<br>Verranno protocollate anche se non verrà selezionato nessun allegato di seguito.";
                        }
                        if ($arrayDoc) {
                            $arrayDocWs = array();
                            foreach ($arrayDoc['pasdoc_rec'] as $key => $documento) {
                                $arrayDocWs[] = $documento['ROWID'];
                            }
                            $msgInfo .= "<br><b>E' possibile spostare gli allegati in alto o in basso secondo l'ordine desiderato.<br>Il primo allegato spuntato sarà inserito come allegato principale del protocollo.</b>";
                            praRic::ricAllegatiWs($this->PRAM_DB, $arrayDocWs, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), 'returnAllegatiWs', $msgInfo);
                        } else {
                            $retPrt = $this->lanciaProtocollaWS();
                            if ($retPrt['Status'] == "-1") {
                                Out::msgStop("Protocollazione Partenza", $retPrt['Message']);
                                break;
                            }
                        }
                        break;
                    case $this->nameForm . '_ConfermaDocumentoFormale':
                        if ($_POST[$this->nameForm . '_MettiAllaFirma'] == 1) {
                            $this->mettiAllaFirma = "true";
                        }
                        $proObject = proWsClientFactory::getInstance();
                        if (!$proObject) {
                            return false;
                        }
                        $this->getRicAllegatiWs($proObject->getClientType(), 'returnAllegatiWsDocumentoFormale');
                        break;
                    case $this->nameForm . '_ConfermaMettiAllaFirma':
                        $praFascicolo = new praFascicolo($this->currGesnum);
                        $praFascicolo->setChiavePasso($this->keyPasso);
                        $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione($tipoWs, false, "P"); //non aggiungo filtri  non serve estrarre il base64
                        $arrayDocRicevute = $praFascicolo->getRicevutePartenza($this->tipoProtocollo, false); //non aggiungo filtri  non serve estrarre il base64
                        $arrayDocWsRicevute = array();
                        foreach ($arrayDocRicevute['pramail_rec'] as $ricevuta) {
                            $arrayDocWsRicevute[] = $ricevuta['ROWID'];
                            $msgInfo = "Attenzione! Ci sono " . count($arrayDocWsRicevute) . " ricevute, tra Accettazioni e Avvenute-Consegna, da protocollare.<br>Verranno protocollate anche se non verrà selezionato nessun allegato di seguito.";
                        }
                        if (!$arrayDoc) {
                            Out::msgInfo("Metti alla firma", "Allegati passo non trovati");
                            break;
                        }
                        $arrayDocWs = array();
                        foreach ($arrayDoc['pasdoc_rec'] as $key => $documento) {
                            $arrayDocWs[] = $documento['ROWID'];
                        }
                        praRic::ricAllegatiWs($this->PRAM_DB, $arrayDocWs, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), 'returnAllegatiWsMettiAllaFirma', $msgInfo);
                        break;
                    case $this->nameForm . '_ProtocollaArrivo':
                        if ($_POST[$this->nameForm . '_PROPAS']['PRORPA'] == "") {
                            Out::msgStop("Protocollazione Pratica", "Responsabile Passo non Presente");
                            break;
                        }
                        Out::msgQuestion("ATTENZIONE!", "L'operazione protocollerà la pratica con procedura " . $this->tipoProtocollo . ". Vuoi continuare?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaProtocollazioneArrivo', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaProtocollazioneArrivo', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );

                        break;
                    case $this->nameForm . '_ConfermaProtocollazioneArrivo' :
                        if (!$this->ControllaDati()) {
                            break;
                        }

                        $this->AggiornaPartenzaDaPost();
                        $this->RegistraDestinatari();
                        $this->AggiornaArrivo();
                        if (!$this->AggiornaRecord()) {
                            break;
                        }


                        $retPrt = $this->lanciaProtocollaArrivoWS();
                        if ($retPrt['Status'] == "-1") {
                            Out::msgStop("Protocollazione Arrivo", $retPrt['Message']);
                        }
                        break;
                    case $this->nameForm . '_RimuoviProtocollaP':
                        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
                        $tipoProt = $this->tipoProtocollo;
//
                        $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
                        if ($pracomP_rec['COMMETA'] || $tipoProt != 'Manuale') {
                            $msg = "<br><br><b>Attenzione. Questa Operazione è Irreversibile.</b><br>Confermando si andrà a pulire il campo protocollo ed anno in partenza e gli allegati collegati ad esso, saranno smarcati dal codice stesso.<br>Successivamente potrebbe essere necessario ricaricare manualmemte i dati.";
                            $this->praLib->GetMsgInputPassword($this->nameForm, "Cancellazione Protocollo", "REMOVEPROTPAR", $msg);
                        } else {
                            Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', '');
                            Out::valore($this->nameForm . '_DATAPROT_DESTINATARIO', '');
                            Out::valore($this->nameForm . '_ANNOPROT_DESTINATARIO', '');
                            Out::attributo($this->nameForm . '_PROTRIC_DESTINATARIO', "readonly", '1');
                            Out::attributo($this->nameForm . '_ANNOPROT_DESTINATARIO', "readonly", '1');
                            Out::attributo($this->nameForm . '_DATAPROT_DESTINATARIO', "readonly", '1');
                            Out::hide($this->nameForm . '_RimuoviProtocollaP');
                            Out::show($this->nameForm . '_InviaProtocolloPar');
                        }
                        break;
                    case $this->nameForm . '_RimuoviDocumentoP':
                        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
                        $tipoProt = $this->tipoProtocollo;
//
                        $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
                        if ($pracomP_rec['COMMETA'] || $tipoProt != 'Manuale') {
                            $msg = "<br><br><b>Attenzione. Questa Operazione è Irreversibile.</b><br>Confermando si andrà a pulire il campo Id Documento, Data Documento in partenza e gli allegati collegati ad esso, saranno smarcati dal codice stesso.<br>Successivamente potrebbe essere necessario ricaricare manualmemte i dati.";
                            $this->praLib->GetMsgInputPassword($this->nameForm, "Cancellazione Protocollo", "REMOVEDOCPAR", $msg);
                        } else {
                            Out::valore($this->nameForm . '_PRACOM[COMIDDOC]', '');
                            Out::valore($this->nameForm . '_PRACOM[COMDATADOC]', '');
                            Out::attributo($this->nameForm . '_PRACOM[COMIDDOC]', "readonly", '1');
                            Out::attributo($this->nameForm . '_PRACOM[COMDATADOC]', "readonly", '1');
                            Out::hide($this->nameForm . '_RimuoviDocumentoP');
                        }
                        break;
                    case $this->nameForm . '_RimuoviProtocollaA':
                        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
                        $tipoProt = $this->tipoProtocollo;
//
                        $pracomA_rec = $this->praLib->GetPracomA($this->keyPasso);
                        if ($pracomA_rec['COMMETA'] || $tipoProt != 'Manuale') {
                            $msg = "<br><br><b>Attenzione. Questa Operazione è Irreversibile.</b><br>Confermando si andrà a cancellare il protocollo in arrivo e gli allegati collegati ad esso, saranno sganciati.<br>Successivamente potrebbe essere necessario ricaricare manualmemte i dati.";
                            $this->praLib->GetMsgInputPassword($this->nameForm, "Cancellazione Protocollo", "REMOVEPROTARR", $msg);
                        } else {
                            Out::valore($this->nameForm . '_PROTRIC_MITTENTE', '');
                            Out::valore($this->nameForm . '_ANNORIC_MITTENTE', '');
                            Out::valore($this->nameForm . '_DATAPROT_MITTENTE', '');
                            Out::attributo($this->nameForm . '_PROTRIC_MITTENTE', "readonly", '1');
                            Out::attributo($this->nameForm . '_ANNORIC_MITTENTE', "readonly", '1');
                            Out::attributo($this->nameForm . '_DATAPROT_MITTENTE', "readonly", '1');
                            Out::hide($this->nameForm . '_RimuoviProtocollaA');
                            Out::show($this->nameForm . '_InviaProtocolloArr');
                        }
                        break;
                    case $this->nameForm . '_AllegaPdfFirmato':
                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                $this->returnToParent();
                            }
                        }
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUploadP7m";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_VisualizzaFirme':
                        if (array_key_exists($this->rowidAppoggio, $this->passAlle) == true) {
                            $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
                            $filePathP7M = $this->passAlle[$this->rowidAppoggio]['FILEPATH'];
                            $fileOrig = $this->passAlle[$this->rowidAppoggio]['FILEORIG'];
                            $extAllegato = strtoupper(pathinfo($this->passAlle[$this->rowidAppoggio]['FILEORIG'], PATHINFO_EXTENSION));
                            if ($extAllegato == "XHTML" || $extAllegato == 'DOCX') {
                                $filePathP7M = pathinfo($this->passAlle[$this->rowidAppoggio]['FILEPATH'], PATHINFO_DIRNAME) . "/" . pathinfo($this->passAlle[$this->rowidAppoggio]['FILEPATH'], PATHINFO_FILENAME) . ".pdf.p7m";
                                $fileOrig = pathinfo($this->passAlle[$this->rowidAppoggio]['FILEORIG'], PATHINFO_FILENAME) . ".pdf.p7m";
                                if (!file_exists($filePathP7M)) {
                                    $filePathP7M = pathinfo($this->passAlle[$this->rowidAppoggio]['FILEPATH'], PATHINFO_DIRNAME) . "/" . pathinfo($this->passAlle[$this->rowidAppoggio]['FILEPATH'], PATHINFO_FILENAME) . ".pdf.p7m.p7m";
                                    $fileOrig = pathinfo($this->passAlle[$this->rowidAppoggio]['FILEORIG'], PATHINFO_FILENAME) . ".pdf.p7m.p7m";
                                }
                            }
//                            $param['NumeroProtocollo'] = substr($pracomP_rec['COMPRT'], 4);
//                            $param['AnnoProtocollo'] = substr($pracomP_rec['COMDPR'], 0, 4);
//                            $param['TipoProtocollo'] = "P";
                            $filent_rec = $this->praLib->GetFilent(21);
                            $StringaSegnatura = $this->praLibAllegati->GetMarcatureString($param, $this->currGesnum, $this->passAlle[$this->rowidAppoggio]['ROWID'], 21);
                            $paramSegnatura = array();
                            $paramSegnaturaTop = array(
                                'STRING' => $StringaSegnatura,
                                'FIRSTPAGEONLY' => $filent_rec['FILDE1'],
                                'X-COORD' => $filent_rec['FILDE3'],
                                'Y-COORD' => $filent_rec['FILDE4'],
                                'ROTATION' => $filent_rec['FILDE2']
                            );
                            $paramSegnatura[] = $paramSegnaturaTop;
//
                            $FirmaStr = 'Riproduzione cartacea del documento informatico sottoscritto digitalmente da @{$PRAALLEGATI.FIRMATARIO}@ @{$PRAALLEGATI.DATAPROT}@ @{$PRAALLEGATI.ORAPROT}@';
                            $paramSegnaturaBottom1 = array(
                                'STRING' => $this->praLibAllegati->getMarcatureFromTemplate($this->currGesnum, $this->passAlle[$this->rowidAppoggio]['ROWID'], $FirmaStr),
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
                            $this->praLib->VisualizzaFirme($filePathP7M, $fileOrig, $paramSegnatura, $this->passAlle[$this->rowidAppoggio]['ROWID']);
//                            
//                            $arrDatiProt = $this->praLibAllegati->GetDatiMarcaturaAlle($this->passAlle[$this->rowidAppoggio]['ROWID']);
//                            $param['NumeroProtocollo'] = $arrDatiProt['NUMPROT'];
//                            $param['AnnoProtocollo'] = $arrDatiProt['ANNOPROT'];
//                            $param['TipoProtocollo'] = $arrDatiProt['TIPOPROT'];
//                            $param['DataProtocollo'] = $arrDatiProt['DATAPROT'];
//
//                            $segnatura = $this->praLibAllegati->GetMarcatureString($param, $this->currGesnum, $this->passAlle[$this->rowidAppoggio]['ROWID']);
//                            $paramSegnatura = array(
//                                'STRING' => $StringaSegnatura,
//                                'FIRSTPAGEONLY' => $filent_rec['FILDE1'],
//                                'X-COORD' => $filent_rec['FILDE3'],
//                                'Y-COORD' => $filent_rec['FILDE4'],
//                                'ROTATION' => $filent_rec['FILDE2']
//                            );
//                            $this->praLib->VisualizzaFirme($filePathP7M, $fileOrig, $paramSegnatura, $this->passAlle[$this->rowidAppoggio]['ROWID']);
                        }
                        break;
                    case $this->nameForm . '_DownloadFile':
                        if (array_key_exists($this->rowidAppoggio, $this->passAlle) == true) {
                            $dirName = pathinfo($this->passAlle[$this->rowidAppoggio]['FILEPATH'], PATHINFO_DIRNAME);
                            $baseName = pathinfo($this->passAlle[$this->rowidAppoggio]['FILEORIG'], PATHINFO_FILENAME);
                            $baseFile = pathinfo($this->passAlle[$this->rowidAppoggio]['FILENAME'], PATHINFO_FILENAME);
                            Out::openDocument(utiDownload::getUrl($baseName . ".$this->tipoFile", $dirName . "/" . $baseFile . ".$this->tipoFile", true));
                        }
                        break;
                    case $this->nameForm . '_VisualizzaTestoBase':
                        $doc = $this->passAlle[$this->rowidAppoggio];
                        $this->praLibAllegati->ApriAllegatoTestoBase(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), $doc, $this->currGesnum, $this->keyPasso);
                        break;
                    case $this->nameForm . '_VisualizzaPdf':
                        if (array_key_exists($this->rowidAppoggio, $this->passAlle) == true) {
                            $dirName = pathinfo($this->passAlle[$this->rowidAppoggio]['FILEPATH'], PATHINFO_DIRNAME);
                            $baseName = pathinfo($this->passAlle[$this->rowidAppoggio]['FILENAME'], PATHINFO_FILENAME);
                            Out::openDocument(utiDownload::getUrl(
                                            $baseName . ".pdf", $dirName . "/" . $baseName . ".pdf"
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_RemoveTifFile':
                        $dirName = pathinfo($this->passAlle[$this->rowidAppoggio]['FILEPATH'], PATHINFO_DIRNAME);
                        $baseName = pathinfo($this->passAlle[$this->rowidAppoggio]['FILENAME'], PATHINFO_FILENAME);
                        if (file_exists($dirName . "/" . $baseName . ".tif")) {
                            if (!@unlink($dirName . "/" . $baseName . ".tif")) {
                                Out::msgStop("Cancellazione File TIF", "Errore nell'eliminazione del file TIF<br>$dirName/$baseName.tif");
                            }
                        }
                        $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');
                        $this->BloccaDescAllegati();
                        break;
                    case $this->nameForm . '_DeleteFile':
                        $dirName = pathinfo($this->passAlle[$this->rowidAppoggio]['FILEPATH'], PATHINFO_DIRNAME);
                        $baseName = pathinfo($this->passAlle[$this->rowidAppoggio]['FILENAME'], PATHINFO_FILENAME);
                        if (file_exists($dirName . "/" . $baseName . ".$this->tipoFile")) {
                            if (@unlink($dirName . "/" . $baseName . ".$this->tipoFile")) {
                                if ($this->tipoFile == "pdf") {
                                    $this->passAlle[$this->rowidAppoggio]['PREVIEW'] = "<span class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Genera PDF\"></span>";
                                    $this->passAlle[$this->rowidAppoggio]['STATO'] = "<span>Clicca sull'ingranaggio per generare il PDF</span>";
                                    $pasStato = "<span>Clicca sull'ingranaggio per generare il PDF</span>";
                                } else if ($this->tipoFile == "pdf.p7m" || $this->tipoFile == "pdf.p7m.p7m") {
                                    $this->passAlle[$this->rowidAppoggio]['PREVIEW'] = "<span class=\"ita-icon ita-icon-pdf-24x24\" title=\"PDF Generato\"></span>";
                                    $this->passAlle[$this->rowidAppoggio]['STATO'] = "<span>PDF generato. Clicca sull'icona per allegare il file firmato</span>";
                                    $pasStato = "<span>PDF generato. Clicca sull'icona per allegare il file firmato</span>";
                                }
                                $pasdoc_rec = $this->praLib->GetPasdoc($this->passAlle[$this->rowidAppoggio]['ROWID'], 'ROWID');
                                $pasdoc_rec['PASLOG'] = $pasStato;
                                $update_Info = 'Oggetto : Aggiornamento allegato' . $pasdoc_rec['PASFIL'];
                                if (!$this->updateRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $update_Info)) {
                                    Out::msgStop("Aggiornamento allegati", "Errore nell'aggiornamento dell'allegato " . $pasdoc_rec['PASFIL']);
                                    break;
                                }
                            }
                            $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');
                            $this->BloccaDescAllegati();
                        }
                        break;
                    case $this->nameForm . '_pdfToTif':
                        $doc = $this->passAlle[$this->rowidAppoggio];
                        break;
                    case $this->nameForm . '_MisuraPlanimetria':
                        $doc = $this->passAlle[$this->rowidAppoggio];
                        Out::msgInput(
                                "Dati Misuratore Planimetrie", array(
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Formato'),
                                'id' => $this->nameForm . '_Formato',
                                'name' => $this->nameForm . '_Formato',
                                'type' => 'select',
                                'options' => array(
                                    array("0", "A0"),
                                    array("1", "A1"),
                                    array("2", "A2"),
                                    array("3", "A3"),
                                    array("4", "A4", true)
                                )
                            ),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Orientamento'),
                                'id' => $this->nameForm . '_Orientamento',
                                'name' => $this->nameForm . '_Orientamento',
                                'type' => 'select',
                                'options' => array(
                                    array("O", "Orizzontale"),
                                    array("V", "Verticale", true)
                                )
                            ),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Scala'),
                                'id' => $this->nameForm . '_Scala',
                                'name' => $this->nameForm . '_Scala',
                                'type' => 'select',
                                'options' => array(
                                    array("100", "100"),
                                    array("200", "200", true),
                                    array("500", "500"),
                                    array("1000", "1000"),
                                    array("1500", "1500")
                                )
                            ),
                                ), array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaDatiPlanimetria', 'model' => $this->nameForm, 'shortCut' => "f5")
                                ), $this->nameForm
                        );
                        $metadati = unserialize($doc['PASMETA']);
                        if ($metadati['FILEGIS']) {
                            $formato = substr($metadati['FILEGIS']['FORMATO'], 0, 1);
                            $orientamento = substr($metadati['FILEGIS']['FORMATO'], 1, 2);
                            $scala = $metadati['FILEGIS']['SCALA'];
                            Out::valore($this->nameForm . '_Formato', $formato);
                            Out::valore($this->nameForm . '_Orientamento', $orientamento);
                            Out::valore($this->nameForm . '_Scala', $scala);
                        }
                        break;
                    case $this->nameForm . '_ConfermaDatiPlanimetria':
                        $doc = $this->passAlle[$this->rowidAppoggio];
                        $metadati = unserialize($doc['PASMETA']);
                        $fileTIF = $this->sincPlanimetria($doc['FILEPATH']);
                        if (!$fileTIF) {
                            break;
                        }
                        $Formato = $_POST[$this->nameForm . "_Formato"];
                        $Orientamento = $_POST[$this->nameForm . "_Orientamento"];
                        $Scala = $_POST[$this->nameForm . "_Scala"];
                        $metadati['FILEGIS']['NOMETIF'] = $fileTIF;
                        $metadati['FILEGIS']['FORMATO'] = $Formato . $Orientamento;
                        $metadati['FILEGIS']['SCALA'] = $Scala;
                        $this->passAlle[$this->rowidAppoggio]['PASMETA'] = serialize($metadati);
                        if ($doc['ROWID']) {
                            $pasdoc_rec = $this->praLib->GetPasdoc($doc['ROWID'], 'ROWID');
                            if ($pasdoc_rec) {
                                $pasdoc_rec['PASMETA'] = $this->passAlle[$this->rowidAppoggio]['PASMETA'];
                            }
                            $update_Info = 'Oggetto: Aggiornamento metadati allegato ' . $doc['FILENAME'];
                            if (!$this->updateRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $update_Info)) {
                                Out::msgStop("Misuratore", "Errore in salvataggio dati misurazione.");
                            }
                        }
                        $model = 'praOpenMisuratore';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['fileTIF'] = $fileTIF;
                        $_POST['Formato'] = $Formato;
                        $_POST['Orientamento'] = $Orientamento;
                        $_POST['Scala'] = $Scala;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnIFrameGIS';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_ControfirmaFile':
                    case $this->nameForm . '_FirmaFile':
                        $doc = $this->passAlle[$this->rowidAppoggio];
                        $inputFile = $doc['FILEPATH'];
                        $fileOrig = $doc['FILEORIG'];
                        $return = "returnFromSignAuth";

                        switch (strtoupper(pathinfo($doc['FILENAME'], PATHINFO_EXTENSION))) {
                            case 'XHTML':
                            case 'DOCX':
                                $inputFile = pathinfo($doc['FILEPATH'], PATHINFO_DIRNAME) . "/" . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . ".pdf";
                                $fileOrig = pathinfo($doc['FILEORIG'], PATHINFO_FILENAME) . ".pdf";
                                if (file_exists($inputFile . ".p7m")) {
                                    $inputFile .= '.p7m';
                                    $fileOrig .= '.p7m';
                                }
                                $return = "returnFromSignAuthTestiBase";
                                break;
                        }

                        itaLib::openForm('rsnAuth', true);
                        /* @var $rsnAuth rsnAuth */
                        $rsnAuth = itaModel::getInstance('rsnAuth');
                        $rsnAuth->setEvent('openform');
                        $rsnAuth->setReturnEvent($return);
                        $rsnAuth->setReturnModel($this->nameForm);
                        $rsnAuth->setReturnId('');
                        $rsnAuth->setInputFilePath($inputFile);
                        $rsnAuth->setinputFileName($fileOrig);
                        $rsnAuth->setTopMsg("<div style=\"font-size:1.3em;color:red;\">Inserisci le credenziali per la firma remota:</div><br><br>");
                        $rsnAuth->parseEvent();
                        break;
                    case $this->nameForm . '_CaricaCampiAgg':
                        $doc = $this->passAlle[$this->rowidAppoggio];
                        $this->arrayInfo = praRic::praCampiPdf($doc['FILEPATH'], array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), 'returnCampiPdf', "true", $doc['FILEORIG']);
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
                    case $this->nameForm . '_ScaricaTestoAss':
                        Out::openDocument(utiDownload::getUrl(
                                        $this->rowidAppoggio['FILENAME'], $this->rowidAppoggio['FILEPATH'], true
                                )
                        );
//                        if (array_key_exists($this->rowidAppoggio, $this->testiAssociati) == true) {
//                            Out::openDocument(utiDownload::getUrl(
//                                            $this->testiAssociati[$this->rowidAppoggio]['FILENAME'], $this->testiAssociati[$this->rowidAppoggio]['FILEPATH'], true
//                                    )
//                            );
//                        }
                        break;
                    case $this->nameForm . '_AllegaTestoAss':
                        $this->caricaTestoAssociato($this->rowidAppoggio);
                        break;
                    case $this->nameForm . '_SiSscegliProc':
                        praRic::praRicAnapra(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "Ricerca procedimento", "");
                        break;
                    case $this->nameForm . '_NoTuttiProc':
                        $tutti = true;
                    case $this->nameForm . '_SiSingoloProc':
                        $procedimento = $_POST[$this->nameForm . "_PROPAS"]['PROPRO'];
                        $this->testiAssociati = $this->GetTestiAssociati($procedimento, $tutti);
                        if ($tutti == false) {
                            $msg = "per il procedimento n. $procedimento";
                        }
                        if (!$this->testiAssociati) {
                            Out::msgStop("Attenzione!!!", "Testi non trovati");
                            break;
                        }
                        praRic::ricImmProcedimenti($this->testiAssociati, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), 'returnTestiAssociati', "Testi Disponibili $msg");
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
                    case $this->nameForm . '_SbloccaComArrivo':
                        $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
                        $pracomA_rec = $this->praLib->GetPracomA($this->keyPasso, $pracomP_rec['ROWID']);
                        if ($pracomP_rec['COMIDMAIL']) {
                            $msg = "<br><br><b>Attenzione. In questa comunicazione in arrivo è presente il riferimento alla mail caricata dal gestionale.<br>Sbloccando verranno persi tutti i riferimenti.</b>";
                        }
                        $this->praLib->GetMsgInputPassword($this->nameForm, "Sblocco Comunicazione in Arrivo", "ARR", $msg);
                        break;
                    case $this->nameForm . '_ConfermaRubricaWSP':
                        $this->idCorrispondente = $this->datiRubricaWS['codice'];
                        if ($this->datiRubricaWS['codiceFiscale'] != '') {
                            Out::valore($this->nameForm . '_CODFISC_DESTINATARIO', $this->datiRubricaWS['codiceFiscale']);
                        }
                        if ($this->datiRubricaWS['partitaIva'] != '') {
                            Out::valore($this->nameForm . '_CODFISC_DESTINATARIO', $this->datiRubricaWS['partitaIva']);
                        }
                        $this->ProtocolloICCSP();
                        break;
                    case $this->nameForm . '_ConfermaRubricaWSA':
                        $this->idCorrispondente = $this->datiRubricaWS['codice'];
                        if ($this->datiRubricaWS['codiceFiscale'] != '') {
                            Out::valore($this->nameForm . '_CODFISC_MITTENTE', $this->datiRubricaWS['codiceFiscale']);
                        }
                        if ($this->datiRubricaWS['partitaIva'] != '') {
                            Out::valore($this->nameForm . '_CODFISC_MITTENTE', $this->datiRubricaWS['partitaIva']);
                        }
                        $this->ProtocolloICCSA();
                        break;
                    case $this->nameForm . '_InserisciRubricaWSP':
                        $this->inserisciRubricaWS('P');
                        break;
                    case $this->nameForm . '_InserisciRubricaWSA':
                        $this->inserisciRubricaWS('A');
                        break;
                    case $this->nameForm . '_CercaAnagrafeArr':
//anaRic::anaRicAnagra($this->nameForm, '', 'ARRIVO');
                        $_POST = array();
                        $model = 'utiVediAnel';
                        $_POST['event'] = 'openform';
                        $_POST['Ricerca'] = 1;
                        $_POST['returnBroadcast'] = 'PRENDI_DA_ANAGRAFE';
                        itaLib::openDialog($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_VediFamigliaArr':
                        $cf = strtoupper($_POST[$this->nameForm . "_CODFISC_MITTENTE"]);
                        $this->ApriVediFamiglia($cf);
                        break;
                    case $this->nameForm . '_returnPasswordPAR':
                        if (!$this->praLib->CtrPassword($_POST[$this->nameForm . '_password'])) {
                            break;
                        }
                        $destinatario = $this->destinatari[$this->rowidAppoggio];
//$praMitDest_rec = $this->praLib->GetPraMitDest($destinatario['ROWID'], "rowid");
                        $praMitDest_rec = $this->praLib->GetPraDestinatari($destinatario['ROWID'], "rowid");
                        if ($praMitDest_rec) {
                            $praMitDest_rec['DATAINVIO'] = "";
                            $praMitDest_rec['ORAINVIO'] = "";
                            $praMitDest_rec['IDMAIL'] = "";
                            $update_Info = "Oggetto : Sblocco comunicazione in partenza di " . $praMitDest_rec['NOME'];
                            if (!$this->updateRecord($this->PRAM_DB, 'PRAMITDEST', $praMitDest_rec, $update_Info)) {
                                Out::msgStop("Errore", "Aggiornamento su PRACOM fallito");
                                break;
                            }
                            $this->destinatari[$this->rowidAppoggio]['ACCETTAZIONE'] = "";
                            $this->destinatari[$this->rowidAppoggio]['CONSEGNA'] = "";
                            $this->destinatari[$this->rowidAppoggio]['VEDI'] = "";
                            $this->destinatari[$this->rowidAppoggio]['SBLOCCA'] = "";
                            $this->destinatari[$this->rowidAppoggio]['DATAINVIO'] = "";
                            $this->destinatari[$this->rowidAppoggio]['ORAINVIO'] = "";
                            $this->destinatari[$this->rowidAppoggio]['IDMAIL'] = "";
                            $this->CaricaGriglia($this->gridAltriDestinatari, $this->destinatari, "1", "100");
                        }
                        break;
                    case $this->nameForm . '_returnPasswordARR':
                        if (!$this->praLib->CtrPassword($_POST[$this->nameForm . '_password'])) {
                            break;
                        }
                        $praMitDest_rec = $this->praLib->GetPraArrivo($_POST[$this->nameForm . '_PROPAS']['PROPAK']);
                        if ($praMitDest_rec) {
                            $praMitDest_rec['DATAINVIO'] = "";
                            $praMitDest_rec['ORAINVIO'] = "";
                            $praMitDest_rec['IDMAIL'] = "";
                            $update_Info = "Oggetto : Sblocco comunicazione in arrivo su  PRAMITDEST n. " . $pracom_rec['COMPAK'];
                            if (!$this->updateRecord($this->PRAM_DB, 'PRAMITDEST', $praMitDest_rec, $update_Info)) {
                                Out::msgStop("Errore", "Sblocco Arrivo su PRAMITDEST fallito");
                                break;
                            }
                        }
                        $pracom_rec = $this->DecodPracomA($_POST[$this->nameForm . '_PROPAS']['PROPAK']);
                        if ($pracom_rec) {
                            $pracom_rec['COMDAT'] = "";
                            $pracom_rec['COMIDMAIL'] = "";
                            $update_Info = "Oggetto : Sblocco comunicazione in arrivo su PRACOM n. " . $pracom_rec['COMPAK'];
                            if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                                Out::msgStop("Errore", "Sblocco Arrivo su PRACOM fallito");
                                break;
                            }
                            $this->Dettaglio($this->keyPasso);
                        }
                        break;
                    case $this->nameForm . '_returnPasswordREMOVEPROTPAR':
                        $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
                        $this->SbloccaProtocollo($pracomP_rec);
                        break;
                    case $this->nameForm . '_returnPasswordREMOVEDOCPAR':
                        $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
                        $this->SbloccaDocumento($pracomP_rec);
                        break;
                    case $this->nameForm . '_returnPasswordREMOVEPROTARR':
                        $pracomA_rec = $this->praLib->GetPracomA($this->keyPasso);
                        $this->SbloccaProtocollo($pracomA_rec);
                        break;
                    case $this->nameForm . '_VediMailArrivo':
                        $pracomA_rec = $this->praLib->GetPracomA($this->keyPasso);
                        if (!$pracomA_rec) {
                            Out::msgInfo("Visualizzatore Mail", "Mail per la Comunicazione in arrivo del passo <b>" . $_POST[$this->nameForm . "_PROPAS"]['PRODPA'] . "</b> non trovata");
                            break;
                        }
                        $pramitDest_rec = $this->praLib->GetPraArrivo($this->keyPasso);
                        $pramail_rec = $this->praLib->getPraMail($pramitDest_rec['IDMAIL']);
                        if (!$pramail_rec || $pramail_rec['TIPOMAIL'] == "KEYUPL") {
//Per le mail KEYUPL
                            $pasdoc_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PASDOC WHERE PASKEY = '{$pracomA_rec['COMPAK']}' AND PASCLA LIKE 'COMUNICAZIONE%' AND PASFIL LIKE '%.eml'", false);
                            if (!$pasdoc_rec) {
                                Out::msgInfo("Visualizzatore Mail", "Allegato Mail per la Comunicazione in arrivo del passo <b>" . $_POST[$this->nameForm . "_PROPAS"]['PRODPA'] . "</b> non trovata");
                                break;
                            }
                            $pramPath = $this->praLib->SetDirectoryPratiche(substr($this->keyPasso, 0, 4), $this->keyPasso, 'PASSO', false);
                            $codiceMail = $pramPath . "/" . $pasdoc_rec['PASFIL'];
                            $tipo = "file";
                        } else {
//Per le mail KEYMAIL
                            $codiceMail = $pracomA_rec['COMIDMAIL'];
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
                    case $this->nameForm . "_VediIdDocPartenza":
                        $html = proIntegrazioni::VediDocumento($this->keyPasso, "PASSO", "P");
                        if ($html['Status'] == "-1") {
                            Out::msgStop("Dati Documento in partenza", $html['Message']);
                            break;
                        }
                        Out::msgInfo("Dati Documento in partenza", $html);
                        $this->Dettaglio($this->keyPasso);
                        break;
                    case $this->nameForm . '_GestioneProtocolloPartenza':
                        itaLib::openForm("praGestProtocollo");
                        $praGestProt = itaModel::getInstance('praGestProtocollo');
                        $praGestProt->setReturnEvent("returnGestProt");
                        $praGestProt->setReturnModel($this->nameForm);
                        $praGestProt->setGesnum($this->currGesnum);
                        $praGestProt->setKeyPasso($this->keyPasso);
                        $praGestProt->setTipoCom("P");
                        $praGestProt->setEvent('openform');
                        $praGestProt->parseEvent();
                        break;
//                    case $this->nameForm . "_VediProtPartenza":
//                        $html = proIntegrazioni::VediProtocollo($this->keyPasso, "PASSO", "P");
//                        if ($html['Status'] == "-1") {
//                            Out::msgStop("Dati Protocollo in partenza", $html['Message']);
//                            break;
//                        }
//                        Out::msgInfo("Dati Protocollo in partenza", $html);
//                        break;
                    case $this->nameForm . "_InviaProtocolloPar":
//
//Controllo se mail già Inviata   
//
                        $Pracom_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMTIP = 'P' AND COMPAK = '$this->keyPasso'", false);
                        $meta = unserialize($Pracom_rec['COMMETA']);
                        if (isset($meta['DatiProtocollazione']['IdMailRichiesta']) && $meta['DatiProtocollazione']['IdMailRichiesta']['value'] && $Pracom_rec) {
                            $data = substr($meta['DatiProtocollazione']['Data']['value'], 6, 2) . "/" . substr($meta['DatiProtocollazione']['Data']['value'], 4, 2) . "/" . substr($meta['DatiProtocollazione']['Data']['value'], 0, 4);
                            Out::msgQuestion("ATTENZIONE!", "Richiesta al protocollo inviatata in data $data.<br>Cosa vuoi fare?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaMail', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Reinvia Mail' => array('id' => $this->nameForm . '_ReinviaMailProtocolloPar', 'model' => $this->nameForm, 'shortCut' => "f5"),
                                'F3-Vedi Mail' => array('id' => $this->nameForm . '_VediMailProtocolloPar', 'model' => $this->nameForm, 'shortCut' => "f3")
                                    )
                            );
                            break;
                        } else {
                            if (!$this->ControllaDati()) {
                                break;
                            }
                            $this->AggiornaPartenzaDaPost();
                        }
                    case $this->nameForm . "_ReinviaMailProtocolloPar":
//
//Recupero Dati per Invio mail
//
                        $praFascicolo = new praFascicolo($this->currGesnum);
                        $praFascicolo->setChiavePasso($this->keyPasso);
                        $elementi = $praFascicolo->getElementiProtocollaComunicazioneP();
                        $allegati = $praFascicolo->getAllegatiProtocollaComunicazione("Paleo", false, "P");
                        $dati = $this->GetDatiMailProtocollo($allegati, $elementi, "PARTENZA");

//
//Invio Mail al Protocollo;
//
                        $this->praLib->InvioMailAlProtocollo($dati, "PARTENZA");
                        break;
                    case $this->nameForm . "_InviaProtocolloArr":
//
//Controllo se mail già Inviata   
//
                        $Pracom_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMTIP = 'A' AND COMPAK = '$this->keyPasso'", false);
                        $meta = unserialize($Pracom_rec['COMMETA']);
                        if (isset($meta['DatiProtocollazione']['IdMailRichiesta']) && $meta['DatiProtocollazione']['IdMailRichiesta']['value'] && $Pracom_rec) {
                            $data = substr($meta['DatiProtocollazione']['Data']['value'], 6, 2) . "/" . substr($meta['DatiProtocollazione']['Data']['value'], 4, 2) . "/" . substr($meta['DatiProtocollazione']['Data']['value'], 0, 4);
                            Out::msgQuestion("ATTENZIONE!", "Richiesta al protocollo inviatata in data $data.<br>Cosa vuoi fare?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaMail', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Reinvia Mail' => array('id' => $this->nameForm . '_ReinviaMailProtocolloArr', 'model' => $this->nameForm, 'shortCut' => "f5"),
                                'F3-Vedi Mail' => array('id' => $this->nameForm . '_VediMailProtocolloArr', 'model' => $this->nameForm, 'shortCut' => "f3")
                                    )
                            );
                            break;
                        } else {
                            if (!$this->ControllaDati()) {
                                break;
                            }

                            $this->AggiornaArrivo();
                        }
                    case $this->nameForm . "_ReinviaMailProtocolloArr":
//
//Recupero Dati per Invio mail
//
                        $praFascicolo = new praFascicolo($this->currGesnum);
                        $praFascicolo->setChiavePasso($this->keyPasso);
                        $elementi = $praFascicolo->getElementiProtocollaComunicazioneA();
                        $allegati = $praFascicolo->getAllegatiProtocollaComunicazione("Paleo", false, "A");
                        $dati = $this->GetDatiMailProtocollo($allegati, $elementi, "ARRIVO");

//
//Invio Mail al Protocollo;
//
                        $this->praLib->InvioMailAlProtocollo($dati, "ARRIVO");
                        break;
                    case $this->nameForm . '_VediMailProtocolloPar':
                        $Pracom_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMTIP = 'P' AND COMPAK = '$this->keyPasso'", false);
                    case $this->nameForm . '_VediMailProtocolloArr':
                        if (!$Pracom_rec) {
                            $Pracom_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMTIP = 'A' AND COMPAK = '$this->keyPasso'", false);
                        }
                        $meta = unserialize($Pracom_rec['COMMETA']);
                        $model = 'emlViewer';
                        $_POST['event'] = 'openform';
                        $_POST['codiceMail'] = $meta['DatiProtocollazione']['IdMailRichiesta']['value'];
                        $_POST['tipo'] = 'id';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_GestioneProtocolloArrivo':
                        itaLib::openForm("praGestProtocollo");
                        $praGestProt = itaModel::getInstance('praGestProtocollo');
                        $praGestProt->setReturnEvent("returnGestProt");
                        $praGestProt->setReturnModel($this->nameForm);
                        $praGestProt->setGesnum($this->currGesnum);
                        $praGestProt->setKeyPasso($this->keyPasso);
                        $praGestProt->setTipoCom("A");
                        $praGestProt->setEvent('openform');
                        $praGestProt->parseEvent();
                        break;
//                    case $this->nameForm . "_VediProtArrivo":
//                        $html = proIntegrazioni::VediProtocollo($this->keyPasso, "PASSO", "A");
//                        if ($html['Status'] == "-1") {
//                            Out::msgStop("Dati Protocollo in arrivo", $html['Message']);
//                            break;
//                        }
//                        Out::msgInfo("Dati Protocollo in arrivo", $html);
//                        break;
                    case $this->nameForm . "_ConfermaBloccaAlle":
                        if (!$this->tipoProtSel) {
                            /*
                             * Quando blocco con Id documento
                             */
                            $this->tipoProtSel['TIPO'] = "P";
                        }
                        $this->RegistraAllegati($this->keyPasso);
                        $this->CaricaAllegati($this->keyPasso);
                        $Alle = $this->passAlle[$this->rowidAppoggio];
                        $idScelto = array();
                        $idScelto[]["ROWID"] = $Alle['ROWID'];
                        $chiave = $this->keyPasso;
                        if ($this->tipoProtSel["TIPO"] == "PR") {
                            $chiave = $this->currGesnum;
                        }
                        $this->bloccaAllegati($chiave, $idScelto, $this->tipoProtSel['TIPO']);
                        $this->Dettaglio($this->keyPasso, "propak");
                        Out::msgBlock($this->nameForm, 3000, true, "Allegato " . $Alle['FILEINFO'] . " bloccato correttamente");
                        break;
                    case $this->nameForm . "_BloccaAllegatoConIDDaMenu":
                        $this->tipoProtSel = array();
                        $Alle = $this->passAlle[$this->rowidAppoggio];
                        $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
                        $dataDoc = substr($pracomP_rec['COMDATADOC'], 6, 2) . "/" . substr($pracomP_rec['COMDATADOC'], 4, 2) . "/" . substr($pracomP_rec['COMDATADOC'], 0, 4);
                        Out::msgQuestion("ATTENZIONE!", "Si Desidera bloccare l'allegato <b>" . $Alle['FILEINFO'] . "</b> con l'id documento <b>" . $pracomP_rec['COMIDDOC'] . "</b> del <b>$dataDoc</b>? ", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaBloccaAlle', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaBloccaAlle', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . "_BloccaAllegatoDaMenu":
                        $this->tipoProtSel = array();
                        $arrayProtPresenti = array();
                        if ($marca != true)
                            $marca = false;
                        $proges_rec = $this->praLib->GetProges($this->currGesnum);
                        if ($proges_rec['GESNPR']) {
                            $meta = unserialize($proges_rec['GESMETA']);
                            $arrayProtPresenti[0]["TIPO"] = "PR";
                            $arrayProtPresenti[0]["DESC"] = "PROTOCOLLAZIONE PRATICA";
                            $arrayProtPresenti[0]["NUMERO"] = substr($proges_rec['GESNPR'], 4) . "/" . substr($proges_rec['GESNPR'], 0, 4);
                            $arrayProtPresenti[0]["DATA"] = $meta['DatiProtocollazione']['Data']['value'];
                        }
                        $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
                        if ($pracomP_rec['COMPRT']) {
                            $arrayProtPresenti[1]["TIPO"] = "P";
                            $arrayProtPresenti[1]["DESC"] = "PROTOCOLLAZIONE IN PARTENZA";
                            $arrayProtPresenti[1]["NUMERO"] = substr($pracomP_rec['COMPRT'], 4) . "/" . substr($pracomP_rec['COMPRT'], 0, 4);
                            $arrayProtPresenti[1]["DATA"] = $pracomP_rec['COMDPR'];
                        }
                        $pracomA_rec = $this->praLib->GetPracomA($this->keyPasso);
                        if ($pracomA_rec['COMPRT']) {
                            $arrayProtPresenti[2]["TIPO"] = "A";
                            $arrayProtPresenti[2]["DESC"] = "PROTOCOLLAZIONE IN ARRIVO";
                            $arrayProtPresenti[2]["NUMERO"] = substr($pracomA_rec['COMPRT'], 4) . "/" . substr($pracomA_rec['COMPRT'], 0, 4);
                            $arrayProtPresenti[2]["DATA"] = $pracomA_rec['COMDPR'];
                        }
                        praRic::praRicProtocolliPresenti($arrayProtPresenti, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig));
                        break;
                    case $this->nameForm . "_SbloccaAllegatoProtocollato":
                        $Alle = $this->passAlle[$this->rowidAppoggio];

                        Out::msgQuestion("ATTENZIONE!", "Si Desidera sbloccare l'allegato <b>" . $Alle['FILEINFO'] . "</b>?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaSbloccaAlle', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaSbloccaAlle', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . "_ConfermaSbloccaAlle":
                        $this->RegistraAllegati($this->keyPasso);
                        $Alle = $this->passAlle[$this->rowidAppoggio];
                        $pasdoc_rec = $this->praLib->GetPasdoc($Alle['ROWID'], 'ROWID');
                        $pasdoc_rec['PASPRTCLASS'] = "";
                        $pasdoc_rec['PASPRTROWID'] = 0;
                        $update_Info = 'Oggetto: Sblocco Allegato Protocollazione ';
                        $this->updateRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $update_Info);
                        $this->eqAudit->logEqEvent($this, array(
                            'Operazione' => eqAudit::OP_UPD_RECORD,
                            'DB' => $this->praLib->getPRAMDB()->getDB(),
                            'DSet' => 'PASDOC',
                            'Estremi' => "SBlocco Allegato  " . $pasdoc_rec['PASNAME'] . " - del passo " . $pasdoc_rec['PASKEY']
                        ));
                        $this->Dettaglio($this->keyPasso, "propak");
                        break;
                    case $this->nameForm . '_CercaAnagrafeProtocolloArr':
                        if ($this->tipoProtocollo == "Iride") {
                            Out::msgInput(
                                    'Soggetto', array(
                                'label' => 'Inserisci il codice soggetto    ',
                                'id' => $this->nameForm . '_searchIdSoggetto',
                                'name' => $this->nameForm . '_searchIdSoggetto',
                                'type' => 'text',
                                'size' => '10',
                                'value' => '',
                                'maxchars' => '10'), array(
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAnagrafeProtocolloArr', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    ), $this->nameForm
                            );
                        } else {
                            $_POST = array();
                            $model = 'utiVediAnel';
                            $_POST['event'] = 'openform';
                            $_POST['Ricerca'] = 1;
                            $_POST['returnBroadcast'] = 'PRENDI_DA_ANAGRAFE';
                            itaLib::openDialog($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                        }
                        break;
                    case $this->nameForm . '_AnnullaMarcatura':
                        $this->lanciaAggiungiAllegati($this->rowidAppoggio, false, "P");
                        break;
                    case $this->nameForm . '_ConfermaMarcatura':
                        $this->lanciaAggiungiAllegati($this->rowidAppoggio, true, "P");
                        break;
                    case $this->nameForm . '_MarcaAllegato':
                        $Alle = $this->passAlle[$this->rowidAppoggio];
                        $arrDatiProt = $this->praLibAllegati->GetDatiMarcaturaAlle($Alle['ROWID']);
//                        $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
//                        $spanProtocollo = "<div style=\"font-size:1.5em;text-align:center;\" class=\"ui-widget ui-widget-content ui-corner-all\">Il N. Protocollo in Partenza è il <b>" . substr($pracomP_rec['COMPRT'], 4) . "</b> dell'anno <b>" . substr($pracomP_rec['COMDPR'], 0, 4) . "</b></div>";
                        $spanProtocollo = "<div style=\"font-size:1.5em;text-align:center;\" class=\"ui-widget ui-widget-content ui-corner-all\">Il N. Protocollo in Partenza è il <b>" . $arrDatiProt['NUMPROT'] . "</b> dell'anno <b>" . $arrDatiProt['ANNOPROT'] . "</b></div>";
                        Out::msgQuestion("ATTENZIONE!", "Si Desidera marcare l'allegato <b>" . $Alle['FILEINFO'] . "</b> con il numero protocollo?<br><br>$spanProtocollo", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaSoloMarcatura', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaSoloMarcatura', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaSoloMarcatura':
                        $idScelto[] = $this->passAlle[$this->rowidAppoggio]['ROWID'];
                        $arrDatiProt = $this->praLibAllegati->GetDatiMarcaturaAlle($idScelto);
                        $param = array();
                        $param['NumeroProtocollo'] = $arrDatiProt['NUMPROT'];
                        $param['AnnoProtocollo'] = $arrDatiProt['ANNOPROT'];
                        $param['TipoProtocollo'] = $arrDatiProt['TIPOPROT'];
//                        $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
//                        $param = array();
//                        $param['NumeroProtocollo'] = substr($pracomP_rec['COMPRT'], 4);
//                        $param['AnnoProtocollo'] = substr($pracomP_rec['COMDPR'], 0, 4);
//                        $param['TipoProtocollo'] = $pracomP_rec['COMTIP'];
                        if (!$this->SegnaturaAllegati($idScelto, $param)) {
                            break;
                        }
                        $this->Dettaglio($this->keyPasso, "propak");
                        Out::msgBlock($this->nameForm, 2500, true, "Allegato/i marcato/i correttamente con il numero protocollo");
                        break;
                    case $this->nameForm . '_ConfermaAnagrafeProtocolloArr':
                        $objProt = proWSClientFactory::getInstance();
                        if (!$objProt) {
                            Out::msgStop("Importazione pratica da protocollo", "Errore inizializzazione driver protocollo");
                            break;
                        }
                        $paramA = array();
                        $paramA['IdSoggetto'] = $_POST[$this->nameForm . '_searchIdSoggetto'];
                        $ritorno = $objProt->LeggiAnagrafica($paramA);
                        if ($ritorno['Status'] == "0") {
                            $dati = $ritorno['RetValue']['Dati'];
                            Out::valore($this->nameForm . '_DESC_MITTENTE', $dati['CognomeNome']);
                            Out::valore($this->nameForm . '_CODFISC_MITTENTE', $dati['CodiceFiscale']);
                            Out::valore($this->nameForm . '_EMAIL_MITTENTE', $dati['Email']);
                        }
                        break;
                    case $this->nameForm . '_CercaIPA':
                        $model = 'proRicIPA';
                        itaLib::openForm($model);
                        /* @var $modelObj itaModel */
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setReturnModel($this->nameForm);
                        $modelObj->setReturnEvent('returnRicIPA');
                        $modelObj->setEvent('openform');
                        $modelObj->parseEvent();
                        break;
                    case $this->nameForm . '_PrenotaProgressivo':
//
//Verifica che la tipologia non sia vuota
//
                        if ($_POST[$this->nameForm . "_PROPAS"]['PRODOCTIPREG'] == 0) {
                            Out::msgStop("Errore", "<br>Impossibile prenotare un progressivo.<br>Scegliere prima una tipologia per il documento rilasciato");
                            break;
                        }

//
//Prenoto il progressivo
//
                        $Anadoctipreg_rec = $this->praLib->GetAnadoctipreg($_POST[$this->nameForm . "_PROPAS"]['PRODOCTIPREG']);
                        $progressivo = $this->praLib->PrenotaProgressivoDaTipologia($Anadoctipreg_rec['ROWID'], $this->currGesnum, $this->workYear);
                        if (!$progressivo) {
                            Out::msgStop("Errore", $this->praLib->getErrMessage());
                            break;
                        }

//
//Butto fuori i valori
//
                        Out::valore($this->nameForm . "_PROPAS[PRODOCPROG]", $progressivo);
                        Out::valore($this->nameForm . "_PROPAS[PRODOCANNO]", $this->workYear);

//
//Salvo il valori nel POST per l'update del record
//
                        $_POST[$this->nameForm . '_PROPAS']['PRODOCPROG'] = $progressivo;
                        $_POST[$this->nameForm . '_PROPAS']['PRODOCANNO'] = $this->workYear;

                        /*
                         * Aggiorno il record del passo se già stato aggiunto altrimenti da erroe
                         */
                        if ($this->keyPasso) {
                            if ($this->AggiornaRecord()) {
                                $this->AggiornaPartenzaDaPost();
                                $this->RegistraDestinatari();
                                $this->AggiornaArrivo();
                            }
                            $this->Dettaglio($this->keyPasso);
                        }
                        break;
                    case $this->nameForm . '_PROPAS[PRODOCTIPREG]_butt':
                        praRic::praRicAnadoctipreg(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "", " WHERE FL_ATTIVO = 1");
                        break;
                    case $this->nameForm . '_paneCom':
                        $this->GetHtmlRiepilogoDest();
                        break;
                    case $this->nameForm . '_Esci':
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_ConfermaApriPadre':
                        $this->Propas['PROINI'] = date('Ymd');
                        $this->SincronizzaRecordPasso($this->Propas);
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
                        $dataScadenza = $_POST[$this->nameForm . "_PROPAS"]['PRODSC'];
                        $dataScadenzaFormatted = substr($dataScadenza, 0, 4) . "-" . substr($dataScadenza, 4, 2) . "-" . substr($dataScadenza, 6, 2);
                        $envCalendar->gotoDate($dataScadenzaFormatted);
                        break;
                    case $this->nameForm . '_RimuoviEvento':
                        $propas_rec = $this->praLib->getPropas($this->keyPasso);
                        $idCalendar = $this->praLib->DecodCalendar($propas_rec['ROWID'], "PASSI_SUAP");
                        if ($idCalendar) {
                            $envLibCalendar = new envLibCalendar();
                            $env_calendar_rec = $envLibCalendar->getCalendar($idCalendar);
                            Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare l'evento dal calendario <b>" . $env_calendar_rec['TITOLO'] . "</b>?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancEvento', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancEvento', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        } else {
                            Out::valore($this->nameForm . "_PROPAS[PROGIO]", "");
                            Out::valore($this->nameForm . "_PROPAS[PRODSC]", "");
                            Out::valore($this->nameForm . "_Calendario", "");
                        }
                        break;
                    case $this->nameForm . '_ConfermaCancEvento':
                        $propas_rec = $this->praLib->getPropas($this->keyPasso);
                        $idCalendar = $this->praLib->DecodCalendar($propas_rec['ROWID'], "PASSI_SUAP");
                        $envLibCalendar = new envLibCalendar();
                        if (!$envLibCalendar->deleteEventApp("PASSI_SUAP", $propas_rec['ROWID'], null, $idCalendar)) {
                            Out::msgInfo("Attenzione!!", "Errore cancellazione evento con classe PASSI_SUAP e codice " . $propas_rec['ROWID']);
                            break;
                        }
                        Out::valore($this->nameForm . "_PROPAS[PROGIO]", "");
                        Out::valore($this->nameForm . "_PROPAS[PRODSC]", "");
                        Out::valore($this->nameForm . "_Calendario", "");
//
                        $propas_rec['PROGIO'] = "";
                        $propas_rec['PRODSC'] = "";
                        $update_Info = "Oggetto: Aggiornamento passo chiave: $this->keyPasso dopo cancellazione evento";
                        if (!$this->updateRecord($this->PRAM_DB, 'PROPAS', $propas_rec, $update_Info)) {
                            Out::msgStop("Errore in Aggionamento", "Aggiornamento passo chiave: $this->keyPasso Fallito.");
                        }
                        break;
                    case $this->nameForm . '_VerificaMailWs':
                        $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
                        $Metadati = unserialize($pracomP_rec['COMMETA']);
                        $siHtml = false;

                        /*
                         * Inizializzo driver protocollo
                         */
                        $proObject = proWsClientFactory::getInstanceVerificaInvio();
                        if (!$proObject) {
                            Out::msgStop("Verifica Invio Mail", "Errore inizializzazione driver protocollo");
                            break;
                        }

                        $param['Destinatari'] = $this->destinatari;
                        $param['TipoProtocollo'] = $this->tipoProtocollo;
                        $param['proNum'] = $Metadati['DatiProtocollazione']['proNum']['value'];
                        $param['Anno'] = $Metadati['DatiProtocollazione']['Anno']['value'];
                        $param['DocNumber'] = $Metadati['DatiProtocollazione']['DocNumber']['value'];
                        $param['Segnatura'] = $Metadati['DatiProtocollazione']['Segnatura']['value'];

                        /*
                         * Verifica invio
                         */
                        $valore = proWsClientHelper::lanciaVerificaInvioWS($param);
                        if ($valore['Status'] == "-1") {
                            Out::msgStop("Attenzione!!!!!", $valore['Message']);
                            break;
                        }

                        $html = $proObject->GetHtmlVerificaInvio($valore);
                        $siHtml = true;
                        if ($siHtml == true) {
                            Out::msgInfo("Verifica Invio", $html);
                        }
                        Out::codice('tableToGrid("#tableVerificaInvio", {});');
                        break;
                    case $this->nameForm . "_AggiungiNota":
                        $this->openFormAddNota();
                        break;
                    case $this->nameForm . "_NuoviPassiDaDest":
                        $pasdoc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $this->praLibDestinazioni->creaSqlPasdocDest($this->currGesnum), true);
                        if (!$pasdoc_tab) {
                            Out::msgInfo("Inserimento Passi Da Destinazioni", "Non sono state trovate Destinazioni da inserire");
                            break;
                        }

                        $arrTotDest = $this->praLibDestinazioni->getArrDest($pasdoc_tab);
                        $arrTotDestClean = $this->praLibDestinazioni->checkDestinazioni($arrTotDest);
                        $htmlMsgDest = "";
                        foreach ($arrTotDestClean as $codice => $value) {
                            $trovato = $this->praLibDestinazioni->checkInsertPasso($this->currGesnum, $codice);
                            if (!$trovato) {
                                $anamed_rec = $this->proLib->GetAnamed($codice);
                                $htmlMsgDest .= " - " . $anamed_rec['MEDNOM'] . ",<br>";
                            }
                        }
                        if ($htmlMsgDest) {
                            $htmlMsg = "<br><span style=\"font-size:1.2em;\"><b>Confermi l'inserimento dei passi per le seguenti destinazioni?</b></span><br>";
                            Out::msgQuestion("Inserimento Passi Da Destinazioni", $htmlMsg . $htmlMsgDest, array(
                                'Annulla' => array('id' => $this->nameForm . '_AnnullaAddPassiDest', 'model' => $this->nameForm),
                                'Conferma' => array('id' => $this->nameForm . '_ConfermaAddPassiDest', 'model' => $this->nameForm)
                                    )
                            );
                        } else {
                            Out::msgInfo("Inserimento Passi Da Destinazioni", "Sono state inserite tutte le Destinazioni");
                        }
                        break;
                    case $this->nameForm . '_ConfermaAddPassiDest':
                        if (!$this->praLibDestinazioni->addPassiDaDestinazioni($this->currGesnum)) {
                            Out::msgStop("Iserimento Passi Da Destinazioni", $this->praLibDestinazioni->getErrMessage());
                            break;
                        }
                        $this->returnToParent();
                        break;
                    case $this->nameForm . "_ConfermaInvioMailDaWs":
                        $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
                        $valore = $this->InvioMailDaWS();
                        if ($valore['Status'] == "-1") {
                            Out::msgStop("Attenzione!!", $valore['Message']);
                            break;
                        }

                        /*
                         * Mi salvo l'id mail nei metadati PRACOM
                         */
                        $valore['Metadati']['DatiProtocollazione']['idMail'] = $valore['idMail'];
                        $pracomP_rec['COMMETA'] = serialize($valore['Metadati']);
                        $update_Info = "Oggetto: Aggiorno metadati passo partenza " . $pracomP_rec['COMPAK'] . " con id mail remoto " . $valore['idMail'];
                        if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracomP_rec, $update_Info)) {
                            Out::msgStop("ATTENZIONE!", "Errore aggiornamento metadati PRACOM");
                            break;
                        }

                        /*
                         * Out messaggio per i destinatari selezionati
                         */
                        Out::msgInfo('Comunicazione in Partenza', $valore['Message']);

                        $this->Dettaglio($this->keyPasso);
                        break;
                    case $this->nameForm . "_GeneraPassword":
                        $pwd = itaLib::generatePassword();
                        Out::valore($this->nameForm . "_PROPAS[PROPPASS]", $pwd);
                        break;
                    case $this->nameForm . "_CercaMittDest":
                        proRic::proRicAnamedMulti(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "", 'proAnamed', 'destinatari');
                        break;
                    case $this->nameForm . "_CercaAnagrafe":
                        $_POST = array();
                        $model = 'utiVediAnel';
                        $_POST['event'] = 'openform';
                        $_POST['Ricerca'] = 1;
                        $_POST['returnBroadcast'] = 'PRENDI_DA_ANAGRAFE_DEST';
                        itaLib::openDialog($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_CercaIPADest':
                        $model = 'proRicIPA';
                        itaLib::openForm($model);
                        /* @var $modelObj itaModel */
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setReturnModel($this->nameForm);
                        $modelObj->setReturnEvent('returnRicIPADest');
                        $modelObj->setEvent('openform');
                        $modelObj->parseEvent();
                        break;
                    case $this->nameForm . "_Comint":
                        praRic::praRicAnades(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "", "WHERE DESNUM = '$this->currGesnum'", "true");
                        break;
                    case $this->nameForm . "_CercaAnagrafeProtocollo":
                        Out::msgInput(
                                'Soggetto', array(
                            'label' => 'Inserisci il codice soggetto    ',
                            'id' => $this->nameForm . '_searchIdSoggetto',
                            'name' => $this->nameForm . '_searchIdSoggetto',
                            'type' => 'text',
                            'size' => '10',
                            'value' => '',
                            'maxchars' => '10'), array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAnagrafeProtocollo', 'model' => $this->nameForm, 'shortCut' => "f5")
                                ), $this->nameForm
                        );
                        break;
                    case $this->nameForm . '_ConfermaAnagrafeProtocollo':
                        $proObject = proWSClientFactory::getInstance();
                        if (!$proObject) {
                            Out::msgStop("Importazione pratica da protocollo", "Errore inizializzazione driver protocollo");
                            break;
                        }
                        $paramA = array();
                        $paramA['IdSoggetto'] = $_POST[$this->nameForm . '_searchIdSoggetto'];
                        $ritorno = $proObject->LeggiAnagrafica($paramA);
                        if ($ritorno['Status'] == "0") {
                            $dati = $ritorno['RetValue']['Dati'];
                            if (isset($dati['Errore'])) {
                                Out::msgInfo("Ricerca Destinatario", $dati['Errore']);
                                break;
                            }
                            $sogg = array();
                            if ($dati['PersonaGiuridica'] == false) {
                                $desnom = $dati['Cognome'] . " " . $dati['Nome'];
                            } elseif ($dati['PersonaGiuridica'] == true) {
                                $desnom = $dati['CognomeNome'];
                            }
                            $sogg['ROWID'] = 0;
                            $sogg['CODICE'] = $dati['IdSoggetto'];
                            $sogg['NOME'] = "<span style = \"color:orange;\">$desnom</span>";
                            $sogg['FISCALE'] = $dati['CodiceFiscale'];
                            $sogg['INDIRIZZO'] = $dati['IndirizzoVia'];
                            $sogg['CAP'] = $dati['CapComuneDiResidenza'];
                            $sogg['COMUNE'] = $dati['DescrizioneComuneDiResidenza'];
                            $sogg['DATAINVIO'] = "";
                            $sogg['MAIL'] = $dati['Email'];
                            $this->destinatari[] = $sogg;
                            $this->CaricaGriglia($this->gridAltriDestinatari, $this->destinatari, "1", "100");
                        }
                        break;
                    case $this->nameForm . "_CercaQualificati":
                        $_POST = array();
                        $model = 'praGestDest';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnDestinazioniDest';
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case $this->nameForm . '_ElaboraFoglioDiCalcolo':
                        $docuRec = $this->passAlle[$this->rowidAppoggio];
                        $spreadsheetPath = $docuRec['FILEPATH'];

                        if (!$this->elaborazioneFoglioDiCalcolo($spreadsheetPath, $this->getFilenameFoglioElaborato($spreadsheetPath), true)) {
                            break;
                        }

                        $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');
                        break;

                    case $this->nameForm . '_UploadDOCX':
                        $model = 'utiUploadDiag';
                        itaLib::openDialog($model);
                        /* @var $utiUploadDiag utiUploadDiag */
                        $utiUploadDiag = itaModel::getInstance($model);
                        $utiUploadDiag->setEvent('openform');
                        $utiUploadDiag->setReturnModel($this->nameForm);
                        $utiUploadDiag->setReturnEvent('returnUploadDOCX');
                        $utiUploadDiag->parseEvent();
                        break;

                    case $this->nameForm . '_UploadXLSDef':
                        $docuRec = $this->passAlle[$this->rowidAppoggio];
                        if (!$docuRec['ROWID']) {
                            Out::msgStop("Attenzione", 'Effettuare l\'aggiornamento del passo per proseguire con l\'upload del definitivo.');
                            break;
                        }

                        $model = 'utiUploadDiag';
                        itaLib::openDialog($model);
                        /* @var $utiUploadDiag utiUploadDiag */
                        $utiUploadDiag = itaModel::getInstance($model);
                        $utiUploadDiag->setEvent('openform');
                        $utiUploadDiag->setReturnModel($this->nameForm);
                        $utiUploadDiag->setReturnEvent('returnUploadXLSDef');
                        $utiUploadDiag->parseEvent();
                        break;

                    case $this->nameForm . '_DownloadXLSDef':
                        $docuRec = $this->passAlle[$this->rowidAppoggio];
                        $spreadsheetPath = $docuRec['FILEPATH'];
                        $definitivo = $this->getFilenameFoglioElaborato($spreadsheetPath, true);

                        Out::openDocument(utiDownload::getUrl($docuRec['FILEORIG'], $definitivo, true));
                        break;

                    case $this->nameForm . '_DeleteXLSDef':
                        $docuRec = $this->passAlle[$this->rowidAppoggio];
                        $spreadsheetPath = $docuRec['FILEPATH'];
                        $definitivo = $this->getFilenameFoglioElaborato($spreadsheetPath, true);

                        if (!unlink($definitivo)) {
                            Out::msgStop('Errore', 'Errore durante la cancellazione dell\'XLS definitivo.');
                            break;
                        }

                        if (!$this->svuotaDatiExportFoglioDiCalcolo()) {
                            break;
                        }

                        $this->passAlle[$this->rowidAppoggio]['STATO'] = '<span>Clicca per funzioni aggiuntive</span>';
                        $this->passAlle[$this->rowidAppoggio]['PREVIEW'] = "<span class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Clicca per funzioni aggiuntive\"></span>";

                        $pasdoc_rec = $this->praLib->GetPasdoc($docuRec['ROWID'], 'ROWID');
                        $pasdoc_rec['PASLOG'] = '<span>Clicca per funzioni aggiuntive</span>';
                        $update_Info = 'Oggetto : Aggiornamento allegato' . $pasdoc_rec['PASFIL'];
                        if (!$this->updateRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $update_Info)) {
                            Out::msgStop('Aggiornamento allegato', 'Errore nell\'aggiornamento dell\'allegato ' . $pasdoc_rec['PASFIL']);
                            break;
                        }

                        $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');

                        /*
                         * Ricarico la subform dei dati aggiuntivi.
                         */

                        /* @var $praCompDatiAggiuntivi praCompDatiAggiuntivi */
                        $praCompDatiAggiuntivi = itaModel::getInstance($this->praCompDatiAggiuntiviFormname[0], $this->praCompDatiAggiuntiviFormname[1]);
                        $praCompDatiAggiuntivi->openGestione($this->currGesnum, $this->keyPasso);
                        break;

                    case $this->nameForm . '_VisDizionario':
                        /* @var $docDocumenti docDocumenti */
                        $docDocumenti = itaModel::getInstance('docDocumenti');
                        $docDocumenti->embedVars('PRATICHE');
                        break;

                    case $this->nameForm . '_VisAnteprimaOODOCX':
                        $doc = $this->passAlle[$this->rowidAppoggio];
                        $propas_rec = $this->praLib->getPropas($this->keyPasso);
                        $praLibVar = new praLibVariabili(array('TARGET' => 'DOCX'));
                        $praLibVar->setCodicePratica($this->currGesnum);
                        $praLibVar->setChiavePasso($this->keyPasso);

                        if ($propas_rec['PROPUB'] == 1) {
                            $praLibVar->setFrontOfficeFlag(true);
                        }

                        $dictionaryValues = $praLibVar->getVariabiliPratica()->getAllDataFormatted();

                        $compiledDocx = $this->docLib->compileDOCX($doc['FILEPATH'], $dictionaryValues, false, $doc['TESTOBASE']);
                        if (!$compiledDocx) {
                            Out::msgStop('Errore', $this->docLib->getErrMessage());
                            break;
                        }

                        //$doc['FILEPATH'] = $compiledDocx;
                        if (!@rename($compiledDocx, $doc['FILEPATH'])) {
                            Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $doc['FILEPATH'] . " !");
                            break;
                        }



                        $this->praLibAllegati->ApriAllegato($this->nameForm, $doc, $this->currGesnum, $this->keyPasso);
                        break;
                    case $this->nameForm . '_VisAnteprimaDOCX':
                        $doc = $this->passAlle[$this->rowidAppoggio];

                        $propas_rec = $this->praLib->getPropas($this->keyPasso);
                        $praLibVar = new praLibVariabili(array('TARGET' => 'DOCX'));
                        $praLibVar->setCodicePratica($this->currGesnum);
                        $praLibVar->setChiavePasso($this->keyPasso);

                        if ($propas_rec['PROPUB'] == 1) {
                            $praLibVar->setFrontOfficeFlag(true);
                        }

                        $dictionaryValues = $praLibVar->getVariabiliPratica()->getAllDataFormatted();

                        $compiledDocx = $this->docLib->compileDOCX($doc['FILEPATH'], $dictionaryValues, false, $doc['TESTOBASE']);
                        if (!$compiledDocx) {
                            Out::msgStop('Errore', $this->docLib->getErrMessage());
                            break;
                        }

                        Out::openDocument(utiDownload::getUrl(
                                        basename($doc['FILEORIG']), $compiledDocx
                                )
                        );
                        break;

                    case $this->nameForm . '_ValidaDatiAggiuntivi':
                        /* @var $praCompDatiAggiuntivi praCompDatiAggiuntivi */
                        $praCompDatiAggiuntivi = itaModel::getInstance($this->praCompDatiAggiuntiviFormname[0], $this->praCompDatiAggiuntiviFormname[1]);
                        $praCompDatiAggiuntivi->validaDati();
                        break;

                    case $this->nameForm . "_AttivaCDS":
                        $rowid = $this->formData[$this->gridAllegati]['gridParam']['selrow'];
                        $this->passAlle[$rowid]['PASFLCDS'] = 1;
                        $this->passAlle[$rowid]['CDS'] = $this->praLib->getIconCds(1, $_POST[$this->nameForm . '_PROPAS']['PROFLCDS']);
                        $this->CaricaGriglia($this->gridAllegati, $this->passAlle);
                        $this->BloccaDescAllegati();
                        break;
                    case $this->nameForm . "_DisattivaCDS":
                        $rowid = $this->formData[$this->gridAllegati]['gridParam']['selrow'];
                        $this->passAlle[$rowid]['PASFLCDS'] = 0;
                        $this->passAlle[$rowid]['CDS'] = $this->praLib->getIconCds(0, $_POST[$this->nameForm . '_PROPAS']['PROFLCDS']);
                        $this->CaricaGriglia($this->gridAllegati, $this->passAlle);
                        $this->BloccaDescAllegati();
                        break;
                    case $this->nameForm . "_VisualizzaFirmeCDS":
                        $rowid = $this->formData[$this->gridAllegati]['gridParam']['selrow'];
                        $allegato = $this->passAlle[$rowid];
                        if ($allegato['PASFLCDS'] == 1) {
                            $sql = "SELECT * FROM PASDOC WHERE PASKEY LIKE '$this->currGesnum%' AND PASROWIDBASE = " . $allegato['ROWID'];
                            $alleOrig = $allegato;
                        }
                        if ($allegato['PASTIPO'] == 'FIR_CDS') {
                            $sql = "SELECT * FROM PASDOC WHERE PASKEY LIKE '$this->currGesnum%' AND PASROWIDBASE = " . $allegato['PASROWIDBASE'];
                            foreach ($this->passAlle as $key => $alle) {
                                if ($alle['ROWID'] == $allegato['PASROWIDBASE']) {
                                    $alleOrig = $alle;
                                    break;
                                }
                            }
                        }
                        $alleFirmati = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                        $html = "<span style=\"font-size:1.2em;text-decoration:underline;\"><b>File Originale: " . $alleOrig['FILEORIG'] . "</b></span><br><br>";
                        $html .= '<table id="tableFirmatari">';
                        $html .= "<tr>";
                        $html .= '<th>File Firmato</th>';
                        $html .= '<th>Firmatario</th>';
                        $html .= "</tr>";
                        $html .= "<tbody>";
                        foreach ($alleFirmati as $key => $alle) {
                            $arrDest = explode(":", $alle['PASUTELOG']);
                            $pramittdest_rec = $this->praLib->GetPraMitDest($arrDest[1], "rowid");
                            $html .= "<tr>";
                            $html .= "<td>" . $alle['PASNAME'] . "</td>";
                            $html .= "<td>" . $pramittdest_rec['NOME'] . "</td>";
                            $html .= "</tr>";
                        }
                        $html .= "</tbody>";
                        $html .= '</table>';
                        Out::msgInfo("Verifica Firmatari", $html);
                        Out::codice('tableToGrid("#tableFirmatari", {});');
                        break;
                    case $this->nameForm . '_Trasmissioni':
                        $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
                        if ($propas_rec['PASPRO'] != 0 && $propas_rec['PASPAR']) {
                            $arcite_rec = $this->proLib->GetArcite($propas_rec['PASPRO'], 'codice', false, $propas_rec['PASPAR']);
                            if ($arcite_rec) {
                                $this->close();
                                $rowId = $arcite_rec['ROWID'];
                                $model = 'proGestIter';
                                $_POST = array();
                                $_POST['event'] = 'openform';
                                $_POST['tipoOpen'] = 'visualizzazione';
                                $_POST['rowidIter'] = $rowId;
                                itaLib::openForm($model);
                                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                $model();
                            }
                        }
                        break;
                    case $this->nameForm . "_togliFirma":
                        $this->togliAllaFirma($this->passAlle[$this->rowidAppoggio]['ROWID']);
                        $this->CaricaAllegati($this->keyPasso);
                        break;
                    case $this->nameForm . "_VaiAllaFirma":
                        $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
                        $anapro_rec = $this->proLib->GetAnapro($propas_rec['PASPRO'], "codice", $propas_rec['PASPAR']);
                        $allegato = $this->passAlle[$this->rowidAppoggio];
                        if ($allegato['ROWID'] == 0) {
                            $this->RegistraAllegati($this->keyPasso);
                            $this->CaricaAllegati($this->keyPasso);
                            $allegato = $this->passAlle[$this->rowidAppoggio];
                        }

                        /*
                         *  Creo ambiente di alvoro temporaneo
                         */
                        $tempPath = itaLib::getPrivateUploadPath();
                        if (!@is_dir($tempPath)) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Archiviazione File.", "Creazione ambiente di lavoro temporaneo fallita.");
                                break;
                            }
                        }

                        /*
                         * Copio il file da mandare alla firma in na cartella temporanea
                         */
                        if (!@copy($allegato['FILEPATH'], $tempPath . "/" . $allegato['FILENAME'])) {
                            Out::msgStop("Archiviazione File.", "Copia del file " . $allegato['FILEORIG'] . " nella cartella temporanea fallita.");
                            break;
                        }

                        /*
                         * Leggo il firmatario
                         */
                        $mettiAllaFirma = $this->proLib->GetAnades($propas_rec['PASPRO'], 'codice', false, $propas_rec['PASPAR'], 'M');

                        /*
                         * Creo l'array dell'allagato da inviare alla firma
                         */
                        $keyLink = 'PRAM.PASDOC.' . $allegato['ROWID'];
                        $proAlle[] = array(
                            'ROWID' => 0,
                            'FILEPATH' => $tempPath . "/" . $allegato['FILENAME'],
                            'FILENAME' => $allegato['FILENAME'],
                            'NOMEFILE' => $allegato['FILEORIG'],
                            'FILEINFO' => $allegato['FILEINFO'],
                            'DOCNAME' => $allegato['FILEORIG'],
                            'DOCTIPO' => "ALLEGATO",
                            'DOCFDT' => date('Ymd'),
                            'DOCRELEASE' => '1',
                            'DOCSERVIZIO' => 0,
                            'DOCLNK' => $keyLink,
                            'METTIALLAFIRMA' => $mettiAllaFirma
                                //,'PREVIEW' =>  "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Allegato \"></span>"
                        );
                        /*
                         * Invio alla firma
                         */
                        $proLibAllegati = new proLibAllegati();
                        if (!$proLibAllegati->GestioneAllegati($this, $propas_rec['PASPRO'], $propas_rec['PASPAR'], $proAlle, $anapro_rec['PROCON'], $anapro_rec['PRONOM'])) {
                            Out::msgStop("Metti alla Firma", "Impossibile inviare il file" . $allegato['FILEORIG'] . "." . $proLibAllegati->getErrMessage());
                            break;
                        }
                        $this->CaricaAllegati($propas_rec['PROPAK']);
                        Out::msgInfo("Metti alla Firma", "Il file" . $allegato['FILEORIG'] . " è stato inviato correttamente alla firma");
                        break;

                    case $this->nameForm . '_AllegatoNonRiservato':
                        $this->praLib->GetMsgInputPassword($this->nameForm, 'Rimuovi Riservatezza', 'RIMUOVIRIS');
                        break;

                    case $this->nameForm . '_returnPasswordRIMUOVIRIS';
                        if (!$this->praLib->CtrPassword($_POST[$this->nameForm . '_password'])) {
                            break;
                        }

                        $this->passAlle[$this->rowidAppoggio]['PASRIS'] = 0;
                        $this->passAlle[$this->rowidAppoggio]['RISERVATO'] = $this->praLibRiservato->getIconRiservato(0);

                        $pasdoc_rec = $this->praLib->GetPasdoc($this->passAlle[$this->rowidAppoggio]['ROWID'], 'ROWID');
                        $pasdoc_rec['PASRIS'] = 0;
                        $infoUpdate = sprintf('Rimosso flag RISERVATO allegato "%s" (rowid %d)', $pasdoc_rec['PASNAME'], $pasdoc_rec['ROWID']);
                        if (!$this->updateRecord($this->praLib->getPRAMDB(), 'PASDOC', $pasdoc_rec, $infoUpdate)) {
                            break;
                        }

                        $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');

                        $this->bloccoAllegatiRiservati();
                        break;

                    case $this->nameForm . '_AllegatoRiservato':
                        $this->passAlle[$this->rowidAppoggio]['PASRIS'] = 1;
                        $this->passAlle[$this->rowidAppoggio]['RISERVATO'] = $this->praLibRiservato->getIconRiservato(1);

                        $pasdoc_rec = $this->praLib->GetPasdoc($this->passAlle[$this->rowidAppoggio]['ROWID'], 'ROWID');
                        $pasdoc_rec['PASRIS'] = 1;
                        $infoUpdate = sprintf('Inserito flag RISERVATO allegato "%s" (rowid %d)', $pasdoc_rec['PASNAME'], $pasdoc_rec['ROWID']);
                        if (!$this->updateRecord($this->praLib->getPRAMDB(), 'PASDOC', $pasdoc_rec, $infoUpdate)) {
                            break;
                        }

                        $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');

                        $this->bloccoAllegatiRiservati();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'returnUploadDOCX':
                $docuRec = $this->passAlle[$this->rowidAppoggio];
                if (!copy($_POST['uploadedFile'], $docuRec['FILEPATH'])) {
                    Out::msgStop('Errore', 'Errore durante la copia del file caricato.');
                    break;
                }
                break;

            case 'returnUploadXLSDef':
                $docuRec = $this->passAlle[$this->rowidAppoggio];
                $spreadsheetPath = $docuRec['FILEPATH'];
                $definitivo = $this->getFilenameFoglioElaborato($spreadsheetPath, true);

                if (!copy($_POST['uploadedFile'], $definitivo)) {
                    Out::msgStop('Errore', 'Errore durante l\'upload dell\'XLS definitivo.');
                    break;
                }

                unlink($_POST['uploadedFile']);

                if (!$this->estrazioneDataFoglioDiCalcolo($definitivo)) {
                    break;
                }

                $this->passAlle[$this->rowidAppoggio]['STATO'] = '<span>Definitivo caricato</span>';
                $this->passAlle[$this->rowidAppoggio]['PREVIEW'] = '<span class="ita-icon ita-icon-excel-flat-24x24" title="Definitivo caricato"></span>';

                $pasdoc_rec = $this->praLib->GetPasdoc($docuRec['ROWID'], 'ROWID');
                $pasdoc_rec['PASLOG'] = '<span>Definitivo caricato</span>';
                $update_Info = 'Oggetto : Aggiornamento allegato' . $pasdoc_rec['PASFIL'];
                if (!$this->updateRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $update_Info)) {
                    Out::msgStop('Aggiornamento allegato', 'Errore nell\'aggiornamento dell\'allegato ' . $pasdoc_rec['PASFIL']);
                    break;
                }

                $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');

                /*
                 * Ricarico la subform dei dati aggiuntivi.
                 */

                /* @var $praCompDatiAggiuntivi praCompDatiAggiuntivi */
                $praCompDatiAggiuntivi = itaModel::getInstance($this->praCompDatiAggiuntiviFormname[0], $this->praCompDatiAggiuntiviFormname[1]);
                $praCompDatiAggiuntivi->openGestione($this->currGesnum, $this->keyPasso);
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PROPAS[PROPART]':
                        $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
                        if ($_POST[$this->nameForm . '_PROPAS']['PROPART'] == 1) {
                            Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneArticoli");
                            Out::showTab($this->nameForm . "_paneArticoli");
                            Out::valore($this->nameForm . "_PROPAS[PROPTIT]", $propas_rec['PRODPA']);
                        } else {
                            Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneArticoli");
                            Out::hideTab($this->nameForm . "_paneArticoli");
                            Out::valore($this->nameForm . "_PROPAS[PROPTIT]", "");
                        }
                        break;
                    case $this->nameForm . '_PROPAS[PROFLCDS]':
                        /*
                         * Blocco lo spostamento dei destinatari se il processo di firma è già iniziato
                         */
                        $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
                        $processoIniziato = $this->checkFirmaCds($propas_rec['PROFLCDS']);
                        if ($processoIniziato === true) {
                            Out::msgInfo("Conferenza di Servizi", "Impossibile modificare la spunta CDS.<br>Il Processo di firma è già iniziato.");
                            Out::valore($this->nameForm . "_PROPAS[PROFLCDS]", $propas_rec['PROFLCDS']);
                            break;
                        } else {
                            if ($_POST[$this->nameForm . '_PROPAS']['PROFLCDS'] == 1) {
                                foreach ($this->passAlle as $key => $alle) {
                                    $ext = pathinfo($alle['FILENAME'], PATHINFO_EXTENSION);
                                    if ($alle['isLeaf'] == 'true' && strtolower($ext) != 'p7m') {
                                        $this->passAlle[$key]['CDS'] = $this->praLib->getIconCds(0, $_POST[$this->nameForm . '_PROPAS']['PROFLCDS']);
                                    }
                                }
                            } else {
                                foreach ($this->passAlle as $key => $alle) {
                                    if ($alle['isLeaf'] == 'true' && strtolower($ext) != 'p7m') {
                                        $this->passAlle[$key]['CDS'] = "";
                                        if ($this->passAlle[$key]['PASFLCDS'] == 1) {
                                            $this->passAlle[$key]['PASFLCDS'] = 0;
                                        }
                                    }
                                }
                            }

                            $this->CaricaGriglia($this->gridAllegati, $this->passAlle);
                        }

                        break;
                    case $this->nameForm . '_PROPAS[PROPUBALL]':
                        if ($_POST[$this->nameForm . '_PROPAS']['PROPUBALL'] == 1) {
                            foreach ($this->passAlle as $key => $alle) {
                                if ($alle['isLeaf'] == "true") {
                                    $this->passAlle[$key]['PUBBLICA'] = "<span class=\"ita-icon ita-icon-no-publish-24x24\">Allegato non pubblicato</span>";
                                }
                            }
                        } else {
                            foreach ($this->passAlle as $key => $alle) {
                                $this->passAlle[$key]['PUBBLICA'] = "";
                            }
                        }
                        $this->CaricaGriglia($this->gridAllegati, $this->passAlle);
                        break;

                    case $this->nameForm . '_PROPAS[PRODWONLINE]':
                        if ($_POST[$this->nameForm . '_PROPAS']['PRODWONLINE'] == 1) {
                            foreach ($this->passAlle as $key => $alle) {
                                if ($alle['isLeaf'] == 'true') {
                                    $this->passAlle[$key]['PUBQR'] = $this->praLib->getFlagPASDWONLINE($this->passAlle[$key]['PASDWONLINE']);
                                }
                            }
                        } else {
                            foreach ($this->passAlle as $key => $alle) {
                                $this->passAlle[$key]['PUBQR'] = '';
                            }
                        }

                        $this->CaricaGriglia($this->gridAllegati, $this->passAlle);
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PROPAS[PRORPA]':
                        $codice = $_POST[$this->nameForm . '_PROPAS']['PRORPA'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $ananom_rec = $this->praLib->getAnanom($codice);
                            $anauniRes_rec = $this->praLib->GetAnauniRes($ananom_rec['NOMRES']);
                            Out::valore($this->nameForm . "_PROPAS[PRORPA]", $ananom_rec['NOMRES']);
                            Out::valore($this->nameForm . '_RESPONSABILE', $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM']);
                            if ($anauniRes_rec['UNISET'] == "") {
                                $anauniRes_rec['UNISET'] = "";
                            }

                            if ($ananom_rec['NOMSET']) {
                                $anauniSett_rec = $this->praLib->GetAnauni($ananom_rec['NOMSET']);
                                Out::valore($this->nameForm . "_PROPAS[PROSET]", $anauniSett_rec['UNISET']);
                                Out::valore($this->nameForm . '_SETTORE', $anauniSett_rec['UNIDES']);
                            }

                            if ($anauniRes_rec['UNISER'] == "") {
                                $anauniRes_rec['UNISET'] = "";
                            }

                            if ($ananom_rec['NOMSER']) {
                                $anauniServ_rec = $this->praLib->GetAnauniServ($ananom_rec['NOMSET'], $ananom_rec['NOMSER']);
                                Out::valore($this->nameForm . "_PROPAS[PROSER]", $anauniServ_rec['UNISER']);
                                Out::valore($this->nameForm . '_SERVIZIO', $anauniServ_rec['UNIDES']);
                            }

                            if ($anauniRes_rec['UNIOPE'] == "") {
                                $anauniRes_rec['UNISET'] = $anauniRes_rec['UNISER'] = "";
                            }
                            $anauniOpe_rec = $this->praLib->GetAnauniOpe($ananom_rec['NOMSET'], $ananom_rec['NOMSER'], $anauniRes_rec['UNIOPE']);
                            Out::valore($this->nameForm . "_PROPAS[PROUOP]", $anauniRes_rec['UNIOPE']);
                            Out::valore($this->nameForm . '_UNITA', $anauniOpe_rec['UNIDES']);
                        }
                        break;
                    case $this->nameForm . '_PROPAS[PROCLT]':
                        $codice = $_POST[$this->nameForm . '_PROPAS']['PROCLT'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $this->DecodTipoPasso($codice);
//                        $praclt_rec = $this->praLib->GetPraclt($codice);
//                        if ($praclt_rec) {
//                            Out::valore($this->nameForm . '_PROPAS[PROCLT]', $praclt_rec['CLTCOD']);
//                            Out::valore($this->nameForm . '_PROPAS[PRODTP]', $praclt_rec['CLTDES']);
//                        }
                        break;
                    case $this->nameForm . '_PROPAS[PROCDR]':
                        $codice = $_POST[$this->nameForm . '_PROPAS']['PROCDR'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $this->DecodAnamed($codice);
                        }
                        break;
                    case $this->nameForm . '_DESTINATARIO':
                        $codice = $_POST[$this->nameForm . '_DESTINATARIO'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $anamed_rec = $this->proLib->GetAnamed($codice);
                            $this->DecodAnamedComP($anamed_rec['ROWID'], 'rowid');
                        }
                        break;
                    case $this->nameForm . '_MITTENTE':
                        $codice = $_POST[$this->nameForm . '_MITTENTE'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $anamed_rec = $this->proLib->GetAnamed($codice);
                            $this->DecodAnamedComA($anamed_rec['ROWID'], 'rowid');
                        }
                        break;
//                    case $this->nameForm . '_DESC_DESTINATARIO':
//                        $anamed_tab = $this->proLib->GetAnamed($_POST[$this->nameForm . '__DESC_DESTINATARIO'], 'nome', 'si', true);
//                        if (count($anamed_tab) == 1) {
//                            $this->DecodAnamedCom($anamed_tab[0]['ROWID'], 'rowid');
//                        }
//                        break;
                    case $this->nameForm . '_PRACOM[COMCIT]':
                        $Comuni_rec = ItaDB::DBSQLSelect($this->COMUNI_DB, "SELECT * FROM COMUNI WHERE COMUNE ='"
                                        . addslashes($_POST[$this->nameForm . '_PRACOM']['COMCIT']) . "'", false);
                        if ($Comuni_rec) {
                            Out::valore($this->nameForm . '_PRACOM[COMPRO]', $Comuni_rec['PROVIN']);
                            Out::valore($this->nameForm . '_PRACOM[COMCAP]', $Comuni_rec['COAVPO']);
                        }
                        break;
                    case $this->nameForm . '_PROPAS[PROSTATO]':
                        if ($_POST[$this->nameForm . '_PROPAS']['PROSTATO']) {
                            $codice = $_POST[$this->nameForm . '_PROPAS']['PROSTATO'];
                            $anastp_rec = $this->praLib->GetAnastp($codice);
                            if ($anastp_rec) {
                                Out::valore($this->nameForm . '_PROPAS[PROSTATO]', $anastp_rec['ROWID']);
                                Out::valore($this->nameForm . '_Stato1', $anastp_rec['STPDES']);
                            } else {
                                Out::valore($this->nameForm . '_Stato1', "");
                            }
                        } else {
                            Out::valore($this->nameForm . '_Stato1', "");
                        }
                        break;
                    case $this->nameForm . '_PRACOM[COMGRS]':
                        if ($_POST[$this->nameForm . '_PRACOM']['COMGRS']) {
                            if ($_POST[$this->nameForm . '_PRACOM']['COMDRI'] && $_POST[$this->nameForm . '_dataPartenza']) {
                                $dataSca = $_POST[$this->nameForm . '_dataPartenza'];
                            }

                            if ($_POST[$this->nameForm . '_PRACOM']['COMDRI'] && $_POST[$this->nameForm . '_dataPartenza'] == "") {
                                $dataSca = $_POST[$this->nameForm . '_PRACOM']['COMDRI'];
                            }
                            if ($_POST[$this->nameForm . '_dataPartenza'] && $_POST[$this->nameForm . '_PRACOM']['COMDRI'] == "") {
                                $dataSca = $_POST[$this->nameForm . '_dataPartenza'];
                            }

                            if ($dataSca) {
                                $allaData = $this->proLib->AddGiorniToData($dataSca, $_POST[$this->nameForm . '_PRACOM']['COMGRS']);
                                Out::valore($this->nameForm . '_PRACOM[COMDFI]', $allaData);
                            } else {
                                Out::valore($this->nameForm . '_PRACOM[COMDFI]', "");
                            }
                        } else {
                            Out::valore($this->nameForm . '_PRACOM[COMDFI]', "");
                        }
                        break;
                    case $this->nameForm . "_PROPAS[PROGIO]":
                        if ($_POST[$this->nameForm . "_PROPAS"]['PROGIO'] != 0) {
                            if ($_POST[$this->nameForm . "_PROPAS"]['PRODSC'] == "19700101")
                                $_POST[$this->nameForm . "_PROPAS"]['PRODSC'] = "";
                            if ($_POST[$this->nameForm . "_PROPAS"]['PROINI'] == "") {
                                Out::msgInfo("Sincronizzazione Data", "Data Aperura Passo Mancante. Impossibile calcolare la data di scadenza del passo.");
                                Out::valore($this->nameForm . "_PROPAS[PRODSC]", "");
                                break;
                            }
                            $arrayScadenza = $this->praLib->SincDataScadenza("PASSO", $this->keyPasso, $_POST[$this->nameForm . "_PROPAS"]['PRODSC'], "", $_POST[$this->nameForm . "_PROPAS"]['PROGIO'], $_POST[$this->nameForm . "_PROPAS"]['PROINI'], true);
                            if (!$arrayScadenza) {
                                Out::msgStop("Sincronizzazione Scadenza", "Errore Sincronizzazione Data Scadenza.");
                            }
                            Out::valore($this->nameForm . "_PROPAS[PRODSC]", $arrayScadenza['SCADENZA']);
                            Out::valore($this->nameForm . "_PROPAS[PROGIO]", $arrayScadenza['GIORNI']);
                        } else {
                            Out::valore($this->nameForm . "_PROPAS[PRODSC]", "");
                        }
                        break;
                    case $this->nameForm . "_PROPAS[PRODSC]":
                        if ($_POST[$this->nameForm . "_PROPAS"]['PRODSC'] != 0) {
                            if ($_POST[$this->nameForm . "_PROPAS"]['PRODSC'] == "19700101")
                                $_POST[$this->nameForm . "_PROPAS"]['PRODSC'] = "";
                            if ($_POST[$this->nameForm . "_PROPAS"]['PROINI'] == "") {
                                Out::msgInfo("Sincronizzazione Data", "Data Aperura Passo Mancante. Impossibile calcolare i giorni di validità del passo.");
                                Out::valore($this->nameForm . "_PROPAS[PROGIO]", "");
                                break;
                            }
                            $arrayScadenza = $this->praLib->SincDataScadenza("PASSO", $this->keyPasso, $_POST[$this->nameForm . "_PROPAS"]['PRODSC'], "", $_POST[$this->nameForm . "_PROPAS"]['PROGIO'], $_POST[$this->nameForm . "_PROPAS"]['PROINI'], true);
                            if (!$arrayScadenza) {
                                Out::msgStop("Sincronizzazione Scadenza", "Errore Sincronizzazione Data Scadenza.");
                            }
                            Out::valore($this->nameForm . "_PROPAS[PRODSC]", $arrayScadenza['SCADENZA']);
                            Out::valore($this->nameForm . "_PROPAS[PROGIO]", $arrayScadenza['GIORNI']);
                        } else {
                            Out::valore($this->nameForm . "_PROPAS[PROGIO]", "");
                        }
                        break;
                    case $this->nameForm . "_PROPAS[PRODOCTIPREG]":
                        if ($_POST[$this->nameForm . "_PROPAS"]['PRODOCTIPREG'] != 0) {
                            $this->DecodeAnadoctipreg($_POST[$this->nameForm . "_PROPAS"]['PRODOCTIPREG']);
                        } else {
                            Out::valore($this->nameForm . '_PROPAS[PRODOCTIPREG]', 0);
                            Out::valore($this->nameForm . '_descTipologia', "");
                        }
                        break;
                }
                break;
            case 'sortRowUpdate':
                switch ($_POST['id']) {
                    case $this->gridAltriDestinatari:
                        $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");

                        /*
                         * Blocco lo spostamento dei destinatari se il processo di firma è già iniziato
                         */
                        $processoIniziato = $this->checkFirmaCds($propas_rec['PROFLCDS']);
                        if ($processoIniziato === true) {
                            Out::msgInfo("", "Impossibile spostare l'ordine dei Firmatari.<br>Il Processo di firma è già iniziato.");
                            $this->CaricaGriglia($this->gridAltriDestinatari, $this->destinatari);
                            break;
                        }

                        /*
                         * Sposto l'elemento dell'array nella posizione desiderata
                         */
                        $elm[$_POST['rowid']] = $this->destinatari[$_POST['rowid']];
                        unset($this->destinatari[$_POST['rowid']]);
                        $stop = ((int) $_POST['stopRowIndex']) - 1;
                        $preArray = array_slice($this->destinatari, 0, $stop, true);
                        $postArray = array_slice($this->destinatari, $stop, null, true);
                        $this->destinatari = $preArray + $elm + $postArray;

                        /*
                         * Aggiorno la il campo SEQUENZA nell'array
                         */
                        $this->ordinaDestinatari();
                        break;
                }
                break;
            case 'returnIFrame':
                switch ($_POST['retid']) {
                    case $this->nameForm . '_protocollaRemotoPartenza':
                        $this->Dettaglio($this->keyPasso);
                        break;
                    case $this->nameForm . '_protocollaRemotoArrivo':
//                        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
                        if ($this->tipoProtocollo == 'Italsoft-remoto-allegati') {
                            $pracomA_rec = $this->praLib->GetPracomA($this->keyPasso);
                            if ($pracomA_rec['COMPRT']) {
                                $this->bloccaAllegati($this->keyPasso, $this->allegatiPrtSel, "A");
                            }
                        }
                        $this->Dettaglio($this->keyPasso);
                        break;
                }
                break;
            case 'returnItalsoftRemotoAllegatiP':
                switch ($_POST['retid']) {
                    case $this->nameForm . '_protocollaRemotoPartenza':
                    case $this->nameForm . '_protocollaRemotoArrivo':
                        $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
                        if ($pracomP_rec['COMPRT']) {
                            $this->bloccaAllegati($this->keyPasso, $this->allegatiPrtSel, "P");
                        }
                        $this->Dettaglio($this->keyPasso);
                        break;
                }
                break;
            case 'returnanamedMulti':
                switch ($_POST['retid']) {
                    case 'destinatari':
                        $arrSogg = explode(",", $_POST['retKey']);
                        foreach ($arrSogg as $key => $rowid) {
                            $this->caricaDestinatarioFromAnamed($rowid, "rowid");
                        }
                        $this->CaricaGriglia($this->gridAltriDestinatari, $this->destinatari, "1", "100");
                        break;
                }
                break;
            case 'returnanamed':
                switch ($_POST['retid']) {
                    case '1':
                        $this->DecodAnamed($_POST['retKey'], 'rowid');
                        break;
                    case '2':
                        $this->DecodAnamedComP($_POST['retKey'], 'rowid');
                        break;
                    case '3':
                        $this->DecodAnamedComA($_POST['retKey'], 'rowid');
                        break;
                    case 'assegnaAPasso':
                        $this->DecodAnamedComA($_POST['retKey'], 'rowid', "si", true);
                        break;
                }
                break;

            case "returnPraclt":
                $this->DecodTipoPasso($_POST["retKey"], 'rowid');
//                $praclt_rec = $this->praLib->GetPraclt($_POST["retKey"], 'rowid');
//                if ($praclt_rec) {
//                    Out::valore($this->nameForm . '_PROPAS[PROCLT]', $praclt_rec['CLTCOD']);
//                    Out::valore($this->nameForm . '_PROPAS[PRODTP]', $praclt_rec['CLTDES']);
//                }
                break;
            case "returnUnires":
                $ananom_rec = $this->praLib->GetAnanom($_POST["retKey"], 'rowid');
                $anauniRes_rec = $this->praLib->GetAnauniRes($ananom_rec['NOMRES']);
                Out::valore($this->nameForm . "_PROPAS[PRORPA]", $ananom_rec['NOMRES']);
                Out::valore($this->nameForm . '_RESPONSABILE', $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM']);

                if ($anauniRes_rec['UNISET'] == "") {
                    $anauniRes_rec['UNISET'] = "";
                }

                if ($ananom_rec['NOMSET']) {
                    $anauniSett_rec = $this->praLib->GetAnauni($ananom_rec['NOMSET']);
                    Out::valore($this->nameForm . "_PROPAS[PROSET]", $anauniSett_rec['UNISET']);
                    Out::valore($this->nameForm . '_SETTORE', $anauniSett_rec['UNIDES']);
                }

                if ($anauniRes_rec['UNISER'] == "") {
                    $anauniRes_rec['UNISET'] = "";
                }

                if ($ananom_rec['NOMSER']) {
                    $anauniServ_rec = $this->praLib->GetAnauniServ($ananom_rec['NOMSET'], $ananom_rec['NOMSER']);
                    Out::valore($this->nameForm . "_PROPAS[PROSER]", $anauniServ_rec['UNISER']);
                    Out::valore($this->nameForm . '_SERVIZIO', $anauniServ_rec['UNIDES']);
                }

                if ($anauniRes_rec['UNIOPE'] == "") {
                    $anauniRes_rec['UNISET'] = $anauniRes_rec['UNISER'] = "";
                }

                $anauniOpe_rec = $this->praLib->GetAnauniOpe($ananom_rec['NOMSET'], $ananom_rec['NOMSER'], $anauniRes_rec['UNIOPE']);
                Out::valore($this->nameForm . "_PROPAS[PROUOP]", $anauniOpe_rec['UNIOPE']);
                Out::valore($this->nameForm . '_UNITA', $anauniOpe_rec['UNIDES']);
                break;
            case 'returnAcqrList':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CaricaGridAllegati':
                        if ($_POST['retList']) {
                            $lista = $_POST['retList'];
                            $this->caricaAllegatiEsterni($lista);
                        }
                        break;
                }
                break;
            case 'returnUploadRisposta':
                $directMailFile = $_POST['uploadedFile']; //itaLib::createPrivateUploadPath() . "/" . $randName;
                $ext = pathinfo($directMailFile, PATHINFO_EXTENSION);
                if ($ext == strtolower("eml")) {
                    $model = 'proElencoMail';
                    $_POST = array();
                    $_POST['event'] = 'openform';
                    $_POST['returnModel'] = 'praSubPassoUnico';
                    $_POST['returnEvent'] = 'returnElencoMail';
                    $_POST['modoFiltro'] = "DIRECT";
                    $_POST['directMailFile'] = $directMailFile;
                    itaLib::openForm($model);
                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                    $model();
                } else {
                    Out::msgStop("Errore", "Estenzione del file mail non valida");
                }
                break;
            case 'returnElencoMail':
                $this->CaricaDaPec();
                break;
            case 'returnPropasANTECEDENTE':
                $this->DecodVaialpasso($_POST["retKey"], 'rowid', $_POST["retid"]);
                break;
            case 'returnProges':
                $this->CercaDocumentiDaPratica($_POST['rowData']['GESNUM']);
                break;
            case 'returnPropas1':
                $propas_rec = $this->praLib->GetPropas($_POST["retKey"], 'rowid');
                if ($propas_rec) {
                    $this->DecodResponsabile($propas_rec['PRORPA'], 'codice', $propas_rec);
                    $this->DecodStatoPasso($propas_rec['PROSTATO']);
//                    $this->DecodStatoPassoAP($propas_rec['PROSTAP']);
//                    $this->DecodStatoPassoCH($propas_rec['PROSTCH']);
                    $open_Info = 'Oggetto: Apro in duplicazione il passo ' . $propas_rec['PROSEQ'];
                    $this->openRecord($this->PRAM_DB, 'PROPAS', $open_Info);
                    Out::valori($propas_rec, $this->nameForm . '_PROPAS');
                }
                break;
            case "returnClassificazioneDocumenti":
                if ($_POST['ROW_ID'] == 'readonly') {
                    break;
                }
                $this->caricaTestoClass($_POST['ROW_ID']);
                break;
            case 'returnMail':
                $mail = "";
                if ($_POST['valori']['Destinatari'] == "") {
                    Out::msgStop("Attenzione!!!!!", "Non sono stati selezionati destinatari per la comunicazione.<br>Impossibile inviare");
                    break;
                }
                $selDestRows = explode(",", $_POST['valori']['Destinatari']);
                foreach ($selDestRows as $riga) {
                    $destinatariSel[] = $this->destinatari[$riga];
                }
                $propas_rec = $this->praLib->GetPropas($_POST['rowid'], 'rowid');
                $Proges_rec = $this->praLib->GetProges($propas_rec['PRONUM']);
                $Responsabile_rec = $this->praLib->GetAnanom($Proges_rec['GESRES']);
                $anatsp_rec = $this->praLib->GetAnatsp($Proges_rec['GESTSP']);
                $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
                $Filent_12_rec = $this->praLib->GetFilent(12);
                $sendCCResponsabile = $Filent_12_rec['FILDE2'];
                $mailAddressResponsabile = $Responsabile_rec['NOMEML'];

                /*
                 * Invio mail con ws abilitati
                 */
                if ($anatsp_rec['TSPSENDREMOTEMAIL']) {
                    $valore = $this->InvioMailDaWS($anatsp_rec['TSPSENDREMOTEMAIL'], $destinatariSel, $_POST['valori']['Oggetto'], $_POST['valori']['Corpo']);
                    if ($valore['Status'] == "-1") {
                        Out::msgStop("Attenzione!!", $valore['Message']);
                        break;
                    }
                    /*
                     * Mi salvo l'id mail nei metadati PRACOM
                     */
                    $valore['Metadati']['DatiProtocollazione']['idMail'] = $valore['idMail'];
                    $pracomP_rec['COMMETA'] = serialize($valore['Metadati']);
                    $update_Info = "Oggetto: Aggiorno metadati passo partenza " . $pracomP_rec['COMPAK'] . " con id mail remoto " . $valore['idMail'];
                    if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracomP_rec, $update_Info)) {
                        Out::msgStop("ATTENZIONE!", "Errore aggiornamento metadati PRACOM");
                        break;
                    }

                    /*
                     * Out messaggio per i destinatari selezionati
                     */
                    Out::msgInfo('Comunicazione in Partenza', $valore['Message']);

                    $this->Dettaglio($this->keyPasso);
                    break;
                } else {
                    /*
                     * Invio mail con itaMailer
                     */
                    $emlMailBox = ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
                    include_once $emlMailBox;
                    $ananom_rec = $this->praLib->GetAnanom($propas_rec['PRORPA']);

//
// Istanza mail box per l'invio da Fascicoli elettronici
//
                    /* @var $emlMailBox emlMailBox */
                    $emlMailBox = $this->praLib->getEmlMailBox($Proges_rec['GESTSP']);
                    if (!$emlMailBox) {
                        Out::msgStop('Inoltro Mail', "Impossibile accedere alle funzioni dell'account: " . $this->refAccounts[0]['EMAIL']);
                        break;
                    }


                    foreach ($destinatariSel as $key => $dest) {
                        $erroreMail = false;

//
// Preparo messaggio in uscita
//
                        /* @var $outgoingMessage emlOutgoingMessage */
                        $outgoingMessage = $emlMailBox->newEmlOutgoingMessage();
                        if (!$outgoingMessage) {
                            Out::msgStop('Inoltro Mail', "Impossibile creare un nuovo messaggio in uscita.");
                            break;
                        }
                        $outgoingMessage->setSubject($_POST['valori']['Oggetto']);
                        $outgoingMessage->setBody($_POST['valori']['Corpo']);
                        $outgoingMessage->setEmail($dest['MAIL']);
                        if ($sendCCResponsabile && $mailAddressResponsabile) {
                            $outgoingMessage->setCCAddresses($mailAddressResponsabile);
                        }
                        $outgoingMessage->setAttachments($_POST['allegati']);
                        $mailArchivio_rec = $emlMailBox->sendMessage($outgoingMessage);
                        if (!$mailArchivio_rec) {
                            $erroreMail = true;
                            break;
                        }
                        $mail .= "<b>" . $dest['MAIL'] . "</b><br>";
                        $idmail = $mailArchivio_rec['IDMAIL'];
                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Archiviazione Mail.", "Creazione ambiente di lavoro temporaneo fallita.");
                                $this->returnToParent();
                            }
                        }
                        $percorsoTmp = itaLib::getPrivateUploadPath();
                        $filename = md5(rand() * time()) . $this->workDate;
                        $filepath = $percorsoTmp . "/" . $filename . ".eml";
                        if (!$this->AggiornaPartenza($dest, $idmail)) {
                            Out::msgStop("ERRORE", "Aggiornamento Comunicazione in Partenza fallito");
                            break;
                        }
                        $this->SetAllegatiValidi($mailArchivio_rec);
                    }
                    if ($erroreMail == false) {
                        Out::msgInfo('Comunicazione in Partenza', "E-Mail inviata con successo ai seguenti destinatari:<br><br>$mail");
                    } else {
                        Out::msgStop('Errore Mail', $emlMailBox->getLastMessage());
                        break;
                    }
                }
                if ($_POST[$this->nameForm . "_PROPAS"]['PROINI'] == "" && $propas_rec['PROINI'] == "") {
                    Out::msgQuestion("AGGIORNAMENTO!", "Vuoi aprire il passo?", array(
                        'F8-No' => array('id' => $this->nameForm . '_AnnullaApriPasso', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Si' => array('id' => $this->nameForm . '_ConfermaApriPasso', 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                    );
                } else {
                    $this->Dettaglio($this->keyPasso);
                }

                $this->Dettaglio($_POST['rowid'], 'rowid');
                break;
            case 'returnanapro':
                switch ($_POST['retid']) {
                    case 'dest':
                        $anapro_rec = $this->proLib->GetAnapro($_POST['retKey'], 'rowid');
                        $anno = substr($anapro_rec['PRONUM'], 0, 4);
                        $numero = substr($anapro_rec['PRONUM'], 4);
                        Out::valore($this->nameForm . '_DATAPROT_DESTINATARIO', $anapro_rec['PRODAR']);
//Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', $numero . "/" . $anno);
                        Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', $numero);
                        Out::valore($this->nameForm . '_ANNOPROT_DESTINATARIO', $anno);

                        Out::valore($this->nameForm . '_PRACOM[COMIND]', $anapro_rec['PROIND']);
                        Out::valore($this->nameForm . '_PRACOM[COMCAP]', $anapro_rec['PROCAP']);
                        Out::valore($this->nameForm . '_PRACOM[COMCIT]', $anapro_rec['PROCIT']);
                        Out::valore($this->nameForm . '_PRACOM[COMPRO]', $anapro_rec['PROPRO']);
                        if ($anapro_rec['PROPAR'] == 'A') {
                            Out::valore($this->nameForm . '_DESTINATARIO', $anapro_rec['PROCON']);
                            Out::valore($this->nameForm . '_DESC_DESTINATARIO', $anapro_rec['PRONOM']);
                        } else {
                            $anaent_rec = $this->proLib->GetAnaent(22);
                            Out::valore($this->nameForm . '_DESTINATARIO', '');
                            Out::valore($this->nameForm . '_DESC_DESTINATARIO', $anaent_rec['ENTDE2']);
                        }
                        break;
                    case 'mitt':
                        $anapro_rec = $this->proLib->GetAnapro($_POST['retKey'], 'rowid');
                        $anno = substr($anapro_rec['PRONUM'], 0, 4);
                        $numero = substr($anapro_rec['PRONUM'], 4);
                        Out::valore($this->nameForm . '_DATAPROT_MITTENTE', $anapro_rec['PRODAR']);
//Out::valore($this->nameForm . '_PROTRIC_MITTENTE', $numero . "/" . $anno);
                        Out::valore($this->nameForm . '_PROTRIC_MITTENTE', $numero);
                        Out::valore($this->nameForm . '_ANNORIC_MITTENTE', $anno);
                        if ($anapro_rec['PROPAR'] == 'A') {
                            Out::valore($this->nameForm . '_MITTENTE', $anapro_rec['PROCON']);
                            Out::valore($this->nameForm . '_DESC_MITTENTE', $anapro_rec['PRONOM']);
                        } else {
                            $anaent_rec = $this->proLib->GetAnaent(22);
                            Out::valore($this->nameForm . '_MITTENTE', '');
                            Out::valore($this->nameForm . '_DESC_MITTENTE', $anaent_rec['ENTDE2']);
                        }
                        break;
                    default:
                        break;
                }
                break;
            case "returnAnastp";
                $anastp_rec = $this->praLib->GetAnastp($_POST['retKey'], 'rowid');
                switch ($_POST['retid']) {
                    case $this->nameForm . '_PROPAS[PROSTATO]':
                        Out::valore($this->nameForm . '_PROPAS[PROSTATO]', $anastp_rec['ROWID']);
                        Out::valore($this->nameForm . '_Stato1', $anastp_rec['STPDES']);
                        break;
                }
                break;
            case "returnDocumenti";
//$this->caricaTestoBase($_POST['retKey'], 'rowid');
                //Out::msgInfo("passAlle", print_r($this->passAlle,true));
                //Out::msgInfo("retKey", $_POST['retKey']);
                $this->passAlle = $this->praLib->caricaTestoBase_Generico($this->keyPasso, $this->passAlle, $_POST['retKey'], 'rowid');
                $this->ContaSizeAllegati($this->passAlle);
                $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');
                $this->bloccoAllegatiRiservati();
                $this->BloccaDescAllegati();
                break;
            case "returnTestiAssociati";
//                Out::msgInfo("nomeForm", $this->nameForm);
//                Out::msgInfo("nomeFormOrig", $this->nameFormOrig);
                Out::msgQuestion("ATTENZIONE!", "Cosa vuoi fare con il file selezionato?", array(
                    'F8-Allega' => array('id' => $this->nameForm . '_AllegaTestoAss', 'model' => $this->nameForm, 'shortCut' => "f8"),
                    'F5-Scarica' => array('id' => $this->nameForm . '_ScaricaTestoAss', 'model' => $this->nameForm, 'shortCut' => "f5")
                        )
                );
                $this->rowidAppoggio = $_POST['rowData'];
//$this->rowidAppoggio = $_POST['retKey'];
                break;

            case "returnPraPDFComposer":
                $fileComposer = $_POST["retFileComposer"];
                $nomeComposer = $_POST["retNomeComposer"];
                if (!is_file($fileComposer)) {
                    Out::msgStop("Attenzione", "File composito non raggiungibile.");
                    break;
                }
                if (!$nomeComposer) {
                    Out::msgStop("Attenzione", "Nome File composito di destinazione mancante.");
                    break;
                }
                $this->aggiungiAllegato(md5(rand() * time()) . "." . pathinfo($fileComposer, PATHINFO_EXTENSION), $fileComposer, $nomeComposer);
                break;
            case 'returnFileFromTwain':
                $this->SalvaScanner();
                break;
            case 'returnEditDiag':
                $doc = $this->passAlle[$_POST['rowidText']];
                $newContent = $_POST['returnText'];
                if (!file_put_contents($doc['FILEPATH'], $newContent)) {
                    Out::msgStop("Attenzione", "Errore in aggiornamento del file $codice");
                } else {
                    Out::msgInfo("Aggiornamento Testo", "Testo " . $doc['INFO'] . " aggiornato correttamente");
                }
                break;
            case 'returnUploadP7m':
                $uplFile = $_POST['uploadedFile'];
                if (strtolower(pathinfo($uplFile, PATHINFO_EXTENSION)) != "p7m") {
                    Out::msgStop("Errore caricamento file", "il file scelto non risulta essere un pdf firmato");
                    break;
                }

//
//Mi trovo l'estensione finale del file qualora ci dovessero essere piu estensioni p7m
//
                $nomeFile = $_POST['file'];
                $Est_baseFile = $this->praLib->GetBaseExtP7MFile($nomeFile);
// Mi trovo e accodo tutte le estensioni p7m
                $Est_tmp = $this->praLib->GetExtP7MFile($nomeFile);
                $posPrimoPunto = strpos($Est_tmp, ".");
                $delEst = substr($Est_tmp, 0, $posPrimoPunto + 1);
                $p7mExt = str_replace($delEst, "", $Est_tmp);
//Creo l'estensione finale del file
                $ext = $Est_baseFile . "." . $p7mExt;

//
//Analizzo il P7M
//
                if (!$this->praLib->AnalizzaP7m($uplFile)) {
                    break;
                }

//
//Copio il file firmato nella cartella del passo
//
                $dirName = pathinfo($this->passAlle[$this->rowidAppoggio]['FILEPATH'], PATHINFO_DIRNAME);
                $baseName = pathinfo($this->passAlle[$this->rowidAppoggio]['FILENAME'], PATHINFO_FILENAME);
//if (!@rename($uplFile, $dirName . "/" . $baseName . ".pdf.p7m")) {
                if (!@rename($uplFile, $dirName . "/" . $baseName . ".$ext")) {
                    Out::msgStop("Spostamento pdf firmato", "Errore nella copia del pdf firmato $baseName.$ext");
                    break;
                }

//
//Aggiorno lo stato e le icone della tabella allegati
//
                $this->passAlle[$this->rowidAppoggio]['PREVIEW'] = "<span class=\"ita-icon ita-icon-shield-green-24x24\" title=\"File firmato caricato\"></span>";
                $this->passAlle[$this->rowidAppoggio]['STATO'] = "<span>Pdf firmato caricato in data " . date('d/m/Y') . " alle ore " . date('H:i:s') . "</span>";
                $pasdoc_rec = $this->praLib->GetPasdoc($this->passAlle[$this->rowidAppoggio]['ROWID'], 'ROWID');
                $pasdoc_rec['PASLOG'] = "<span>Pdf firmato caricato in data " . date('d/m/Y') . " alle ore " . date('H:i:s') . "</span>";
                $update_Info = 'Oggetto : Aggiornamento allegato' . $pasdoc_rec['PASFIL'];
                if (!$this->updateRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $update_Info)) {
                    Out::msgStop("Aggiornamento allegati", "Errore nell'aggiornamento dell'allegato " . $pasdoc_rec['PASFIL']);
                    break;
                }
                $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');
                $this->BloccaDescAllegati();
                break;
            case 'returnAggiungiAllegatiWs':
                if (!$_POST['retKey']) {
                    break;
                }

                $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
                $idScelti = array();
                $idAllegatiScelti = explode(",", $_POST['retKey']);
                foreach ($idAllegatiScelti as $id) {
                    $idScelti[] = substr($id, 1);
                }
                $praFascicolo = new praFascicolo($this->currGesnum);
                $praFascicolo->setChiavePasso($this->keyPasso);
                if (!$idScelti) {
                    $idScelti = "NO";
                }
                $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione($this->tipoProtocollo, false, "P", $idScelti); //aggiungo i filtri alla selezione!
                $daMarcare = false;
                foreach ($arrayDoc['Allegati'] as $allegato) {
                    if ($this->tipoProtocollo != 'Paleo4') {
                        if (strtolower($allegato['estensione'] == 'pdf') && $pracomP_rec['COMPRT']) {
                            $daMarcare = true;
                        }
                    } else {
                        if (strtolower(pathinfo($allegato['Documento']['Nome'], PATHINFO_EXTENSION) == 'pdf') && $pracomP_rec['COMPRT']) {
                            $daMarcare = true;
                        }
                    }
                }
                if ($daMarcare) {
                    Out::msgQuestion("ATTENZIONE!", "Ci sono dei pdf da allegare. Vuoi marcarli con il numero protocollo?", array(
                        'F8-Prosegui senza Marcatura' => array('id' => $this->nameForm . '_AnnullaMarcatura', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Prosegui con Marcatura' => array('id' => $this->nameForm . '_ConfermaMarcatura', 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                    );
                    $this->rowidAppoggio = $_POST['retKey'];
                } else {
                    $this->lanciaAggiungiAllegati($_POST['retKey'], false, "P");
                }
                break;
            case 'returnAggiungiAllegatiWsArr':
                if (!$_POST['retKey']) {
                    break;
                }
                $idScelti = array();
                $idAllegatiScelti = explode(",", $_POST['retKey']);
                foreach ($idAllegatiScelti as $id) {
                    $idScelti[] = substr($id, 1);
                }
                $praFascicolo = new praFascicolo($this->currGesnum);
                $praFascicolo->setChiavePasso($this->keyPasso);
                if (!$idScelti) {
                    $idScelti = "NO";
                }
//                $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
                $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione($this->tipoProtocollo, false, "A", $idScelti); //aggiungo i filtri alla selezione!
                $this->lanciaAggiungiAllegatiArr($_POST['retKey'], false, "A");
                break;

            case 'returnAllegatiWsDocumentoFormale':
                $retDoc = $this->lanciaDocumentoFormaleWS();
                if ($retDoc['Status'] == "-1") {
                    Out::msgStop("Inserimento Documento Formale", $retDoc['Message']);
                    break;
                }
                $this->Dettaglio($this->keyPasso, "propak");
                break;
            case 'returnAllegatiWsMettiAllaFirma':
                $retFirma = $this->lanciaMettiAllaFirmaWS();
                if ($retFirma['Status'] == "-1") {
                    Out::msgStop("Inserimento Documento", $retFirma['Message']);
                    break;
                }
                Out::msgInfo("Inserimento Documento", $retFirma['Message']);
                $this->Dettaglio($this->keyPasso, "propak");
                break;
            case 'returnAllegatiWs':
                $retPrt = $this->lanciaProtocollaWS();
                if ($retPrt['Status'] == "-1") {
                    Out::msgStop("Protocollazione Partenza", $retPrt['Message']);
                    break;
                }
                break;

            case 'returnItepasAntecedenti':
                $rowid = $_POST['retKey'];
                if ($rowid) {
                    $itepas_rec = $this->praLib->GetItepas($rowid, 'rowid');
                    $proges_rec = $this->praLib->GetProges($this->currGesnum);
                    $anapra_rec = $this->praLib->GetAnapra($proges_rec['GESPRO']);
                    $propas_rec = $this->praLib->GetPropas($this->keyPasso, 'propak');
                    $filent_valoResp_rec = $this->praLib->GetFilent(19);
                    $filent_passoBO = $this->praLib->GetFilent(27);
                    $extraParam = array();
                    $extraParam['VALORESP'] = $filent_valoResp_rec['FILVAL'];
                    $extraParam['PASSOBO'] = $filent_passoBO['FILDE1'];
                    $extraParam['PROFILO'] = $this->profilo;
                    $extraParam['GENERA_PROPAK'] = true;
                    $new_seq = $propas_rec['PROSEQ'] + 1;
//                    $saveItekeySorgente = $itepas_rec['ITEKEY'];
//                    $itepas_rec['ITEKEY'] = $this->praLib->PropakGenerator($itepas_rec['ITECOD']);
//                    $itepas_rec['ITEKPRE'] = $itepas_rec['ITECOD'] . substr($propas_rec['PROPAK'], 10);
//                    if ($propas_rec['PROKPRE']) {
//                        $itepas_rec['ITEKPRE'] = $itepas_rec['ITECOD'] . substr($propas_rec['PROKPRE'], 10);
//                    }
                    $lastRowid = $this->praLib->ribaltaPasso($proges_rec, '', $itepas_rec, $anapra_rec, $new_seq, $extraParam);
                    $this->praLib->ordinaPassi($this->currGesnum);


//                    $lastRowid = $this->PRAM_DB->getLastId();
//                    $propas_rec = $this->praLib->GetPropas($lastRowid, 'rowid');
//                    $propas_rec['PROITK'] = $saveItekeySorgente;
//                    if (!$this->updateRecord($this->PRAM_DB, 'PROPAS', $propas_rec, '')) {
//                        Out::msgStop('ATETNZIONE', 'Errore in aggiornamento di PROPAS');
//                        break;
//                    }
                    if (!$lastRowid) {
                        Out::msgStop('ERRORE', $this->praLib->getErrMessage());
                    }
                    $this->Dettaglio($lastRowid, 'rowid');
                }
                break;
            case 'returnColorpicker':
                $this->praLibAllegati->ColorpickerAllegati($this->passAlle[$this->passAlle['EvidenziaRow']]['ROWID'], $_POST['colorPicked']);
                unset($this->passAlle['EvidenziaRow']);
                $this->CaricaAllegati($this->keyPasso);
                $this->CaricaGriglia($this->gridAllegati, $this->passAlle);
                break;
            case 'cellSelect':
                $doc = $this->passAlle[$_POST['rowid']];
                $proges_rec = $this->praLib->GetProges($this->currGesnum);
                $propas_rec = $this->praLib->GetPropas($this->keyPasso);
                $pracomP_rec = $this->DecodPracomP($propas_rec['PROPAK']);
                $pracomA_rec = $this->DecodPracomA($propas_rec['PROPAK'], $pracomP_rec['ROWID']);
                if (($propas_rec['PROVISIBILITA'] == "Protetto" || $propas_rec['PROVISIBILITA'] == "soloPasso") && $_POST['colName'] != "PREVIEW" && $_POST['colName'] != "EVIDENZIA" && $_POST['colName'] != "VEDI" && $_POST['colName'] != "CONSEGNA" && $_POST['colName'] != "ACCETTAZIONE") {
                    if (!$this->praPerms->checkSuperUser($proges_rec)) {
                        if ($this->praPerms->checkUtenteGenerico($propas_rec)) {
                            break;
                        }
                    }
                }
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        $doc = $this->passAlle[$_POST['rowid']];

// Se non è un allegato mi fermo
                        if (!isset($doc['isLeaf'])) {
                            break;
                        }

//Se c'è la comunicazione in arrivo, blocco tutto tranne EVIDENZIA,  PREVIEW e  PUBBLICA
                        if ($pracomA_rec['COMDAT'] && $_POST['colName'] != "PREVIEW" && $_POST['colName'] != "EVIDENZIA" && $_POST['colName'] != "PUBBLICA") {
                            break;
                        }

                        $pasdoc_rec = $this->praLib->getPasdoc($doc['ROWID'], "ROWID");
                        $ext = pathinfo($doc['FILEPATH'], PATHINFO_EXTENSION);
                        if ($pasdoc_rec['PASPRTROWID'] != 0 && ($pasdoc_rec['PASPRTCLASS'] == "PROGES" || $pasdoc_rec['PASPRTCLASS'] == "PRACOM") && $_POST['colName'] == "LOCK") {
                            break;
                        }

                        if ($this->praReadOnly == true) {
                            if (strtolower($ext) == "p7m") {
                                $arrayAzioni = $this->GetArrayAzioniRO("p7m");
                            } else if (strtolower($ext) == "xhtml" || strtolower($ext) == 'docx') {
                                $pramPath = pathinfo($doc['FILEPATH'], PATHINFO_DIRNAME);
                                if (file_exists($pramPath . "/" . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . ".pdf.p7m")) {
                                    $arrayAzioni = $this->GetArrayAzioniRO("xhtml.pdf.p7m");
                                    $this->tipoFile = "pdf.p7m";
                                }
                            }
                            $this->rowidAppoggio = $_POST['rowid'];
                            if ($arrayAzioni) {
                                Out::msgQuestion("Gestione Allegato", "", $arrayAzioni, 'auto', 'auto', 'true', false, true, true);
                            }
                            break;
                        }

                        switch ($_POST['colName']) {
                            case 'PREVIEW':
                                if (strtolower($ext) == "pdf") {
                                    $this->rowidAppoggio = $_POST['rowid'];
                                    $pramPath = pathinfo($doc['FILEPATH'], PATHINFO_DIRNAME);
                                    $arrayAzioni = $this->GetArrayAzioni("pdf", $pasdoc_rec);
                                } elseif (strtolower($ext) == "tif") {
                                    $arrayAzioni = $this->GetArrayAzioni("tif", $pasdoc_rec);
                                } elseif (in_array(strtolower($ext), array('xls', 'xlsx'))) {
                                    if (file_exists($this->getFilenameFoglioElaborato($doc['FILEPATH'], true))) {
                                        $arrayAzioni = $this->GetArrayAzioni("xls.def", $pasdoc_rec);
                                    } else {
                                        $arrayAzioni = $this->GetArrayAzioni("xls", $pasdoc_rec);
                                    }
                                } elseif (strtolower($ext) == "p7m") {
//$this->praLib->VisualizzaFirme($doc['FILEPATH'], $doc['FILEORIG']);
                                    $arrayAzioni = $this->GetArrayAzioni("p7m", $pasdoc_rec);
                                } else if (strtolower($ext) == "xhtml") {
                                    $pramPath = pathinfo($doc['FILEPATH'], PATHINFO_DIRNAME);
                                    if (file_exists($pramPath . "/" . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . ".pdf.p7m")) {
                                        $arrayAzioni = $this->GetArrayAzioni("xhtml.pdf.p7m", $pasdoc_rec);
                                        $this->tipoFile = "pdf.p7m";
                                    } else if (file_exists($pramPath . "/" . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . ".pdf.p7m.p7m")) {
                                        $arrayAzioni = $this->GetArrayAzioni("xhtml.pdf.p7m", $pasdoc_rec);
                                        $this->tipoFile = "pdf.p7m.p7m";
                                    } else if (file_exists($pramPath . "/" . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . ".pdf")) {
                                        $arrayAzioni = $this->GetArrayAzioni("xhtml.pdf", $pasdoc_rec);
                                        $this->tipoFile = "pdf";
                                    } else {
                                        $arrayAzioni = $this->GetArrayAzioni("xhtml", $pasdoc_rec);
                                    }
                                } else if (strtolower($ext) == 'docx') {
                                    $pramPath = pathinfo($doc['FILEPATH'], PATHINFO_DIRNAME);
                                    if (file_exists($pramPath . '/' . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . '.pdf.p7m')) {
                                        $arrayAzioni = $this->GetArrayAzioni('docx.pdf.p7m', $pasdoc_rec);
                                        $this->tipoFile = 'pdf.p7m';
                                    } else if (file_exists($pramPath . '/' . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . '.pdf.p7m.p7m')) {
                                        $arrayAzioni = $this->GetArrayAzioni('docx.pdf.p7m', $pasdoc_rec);
                                        $this->tipoFile = 'pdf.p7m.p7m';
                                    } else if (file_exists($pramPath . '/' . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . '.pdf')) {
                                        $arrayAzioni = $this->GetArrayAzioni('docx.pdf', $pasdoc_rec);
                                        $this->tipoFile = 'pdf';
                                    } else {
                                        $arrayAzioni = $this->GetArrayAzioni('docx', $pasdoc_rec);
                                    }
                                } else {
                                    $arrayAzioni = $this->GetArrayAzioni("", $pasdoc_rec);
                                }
//Tornato l'array lancio la msgQuestion
                                $this->rowidAppoggio = $_POST['rowid'];
                                if ($arrayAzioni) {
                                    Out::msgQuestion("Gestione Allegato", "", $arrayAzioni, 'auto', 'auto', 'true', false, true, true);
                                }
                                break;
                            case "EVIDENZIA":
                                $this->passAlle['EvidenziaRow'] = $_POST['rowid'];
//                                if ($doc['PASEVI'] == 0) {
//                                    $this->passAlle[$_POST['rowid']]['PASEVI'] = 1;
//                                    $color = "red";
//                                    $fontWeight = "font-weight:bold";
//                                    $fontSize = "font-size:1.2em";
//                                } else {
//                                    $this->passAlle[$_POST['rowid']]['PASEVI'] = 0;
//                                    if (!$pasdoc_rec) {
//                                        $color = "orange";
//                                    } else {
//                                        $color = "black";
//                                    }
//                                }
// $this->passAlle[$_POST['rowid']]['NAME'] = "<p style = 'color:$color;$fontWeight;$fontSize'>" . $doc['FILEORIG'] . "</p>";
// $this->CaricaGriglia($this->gridAllegati, $this->passAlle);
// $this->BloccaDescAllegati();
                                break;
                            case "LOCK";
                                if ($doc['PASLOCK'] == 1) {
                                    $this->passAlle[$_POST['rowid']]['PASLOCK'] = 0;
                                    $icon = "unlock";
                                    $title = "Blocca Allegato";
                                } else {
                                    $this->passAlle[$_POST['rowid']]['PASLOCK'] = 1;
                                    $icon = "lock";
                                    $title = "Sblocca Allegato";
                                }
                                $this->passAlle[$_POST['rowid']]['LOCK'] = "<span class=\"ita-icon ita-icon-$icon-24x24\">$title</span>";
                                $this->CaricaGriglia($this->gridAllegati, $this->passAlle);
                                $this->BloccaDescAllegati();
                                break;
                            case "PUBBLICA";
//                                if ($_POST[$this->nameForm . "_PROPAS"]['PROPUBALL'] <> 1) {
//                                    break;
//                                }
                                if ($doc['PASPUB'] == 1) {
                                    $this->passAlle[$_POST['rowid']]['PASPUB'] = 0;
                                    $icon = "no-publish";
                                    $title = "Non Pubblicare l'allegato";
                                } else {
                                    $this->passAlle[$_POST['rowid']]['PASPUB'] = 1;
                                    $icon = "publish";
                                    $title = "Pubblica l'allegato";
                                }
                                $this->passAlle[$_POST['rowid']]['PUBBLICA'] = "<span class=\"ita-icon ita-icon-$icon-24x24\">$title</span>";
                                $this->CaricaGriglia($this->gridAllegati, $this->passAlle);
                                $this->BloccaDescAllegati();

                                break;
                            case "EDIT";
                                if (strtolower($ext) != "zip") {
                                    break;
                                }
                                $zipPath = pathinfo($doc['FILEPATH'], PATHINFO_DIRNAME) . "/" . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME);
                                $ret = itaZip::Unzip($doc['FILEPATH'], $zipPath);
                                if ($ret != 1) {
                                    Out::msgStop("ATTENZIONE!!!", "Estrazione file fallita");
                                    break;
                                }
                                if (!is_dir($zipPath)) {
                                    Out::msgStop("ATTENZIONE!!!", "Cartella " . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . " non trovata");
                                    break;
                                }
                                $zipTree = false;
                                if ($zipTree) {
                                    $arrayZip = array();
                                    $key = 1;
                                    $arrayZip[$key]['SEQ'] = 1;
                                    $arrayZip[$key]['NAME'] = $doc['NAME'];
                                    $arrayZip[$key]['FILEPATH'] = $zipPath;
                                    $arrayZip[$key]['level'] = 0;
                                    $arrayZip[$key]['parent'] = '';
                                    $arrayZip[$key]['isLeaf'] = 'false';
                                    $arrayZip[$key]['expanded'] = 'true';
                                    $arrayZip[$key]['loaded'] = 'true';
                                    $arrayExplodeZip = $this->praLib->explodeZipDir($zipPath, $arrayZip, 0, $key, $doc['PROVENIENZA']);
                                    praRic::GetExplodedZip(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), $arrayExplodeZip, $doc['FILEORIG']);
                                } else {
                                    $arrayExplodeZip = $this->praLib->explodeZipDirPlain($zipPath, $arrayZip, '', $doc['PROVENIENZA']);
                                    praRic::GetExplodedZip(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), $arrayExplodeZip, $doc['FILEORIG'], false);
                                }
                                break;
                            case "CLASSIFICAZIONE";
                                if ($pasdoc_rec['PASPRTROWID'] == 0 && $pasdoc_rec['PASPRTCLASS'] == "") {
                                    $anacla_rec = $this->praLib->GetAnacla($pasdoc_rec['PASCLAS']);
                                    Out::msgInput(
                                            'Modifica Dati', array(array(
                                            'label' => array('style' => "width:100px;", 'value' => 'Codice  '),
                                            'id' => $this->nameForm . '_CodiceCla',
                                            'name' => $this->nameForm . '_CodiceCla',
                                            'class' => "ita-edit-lookup",
                                            'value' => $pasdoc_rec['PASCLAS'],
                                            'size' => '10',
                                            'maxchars' => '30'),
                                        array(
                                            'label' => array('style' => "width:100px;", 'value' => 'Classificazione  '),
                                            'id' => $this->nameForm . '_Classificazione',
                                            'name' => $this->nameForm . '_Classificazione',
                                            'value' => $anacla_rec['CLADES'],
                                            'class' => "ita-readonly",
                                            'size' => '30',
                                            'maxchars' => '300')), array(
                                        'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCla', 'model' => $this->nameForm, 'shortCut' => "f5")
                                            ), $this->nameForm
                                    );
                                }
                                break;
                            case "PASNOTE";
                                if ($pasdoc_rec['PASPRTROWID'] == 0 && $pasdoc_rec['PASPRTCLASS'] == "") {
                                    $anacla_rec = $this->praLib->GetAnacla($pasdoc_rec['PASCLAS']);
                                    Out::msgInput(
                                            'Modifica Dati', array(
                                        array(
                                            'label' => array('style' => "width:60px;", 'value' => 'Note'),
                                            'id' => $this->nameForm . '_Note',
                                            'name' => $this->nameForm . '_Note',
                                            'type' => 'textarea',
                                            'cols' => '50',
                                            'rows' => '5',
                                            '@textNode@' => $pasdoc_rec['PASNOTE'])), array(
                                        'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaNote', 'model' => $this->nameForm, 'shortCut' => "f5")
                                            ), $this->nameForm
                                    );
                                }
                                break;
                            case "DESTINAZIONI";
                                if ($pasdoc_rec['PASPRTROWID'] == 0 && $pasdoc_rec['PASPRTCLASS'] == "") {
                                    $dest = $this->passAlle[$_POST['rowid']]['PASDEST'];
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
                            case "SOST":
                                $msg = $this->praLib->CheckSostFile($this->currGesnum, $pasdoc_rec['PASSHA2'], $pasdoc_rec['PASSHA2SOST'], "MSG");
                                Out::msgInfo("Sostituzione Allegati", $msg);
                                break;
                            case "CDS";
                                $arrayAzioni = $this->getArrayAzioniCds($this->formData[$this->nameForm . '_PROPAS']['PROFLCDS'], $doc);
                                if ($arrayAzioni) {
                                    Out::msgQuestion("Gestione Allegato CDS", "", $arrayAzioni, 'auto', 'auto', 'true', false, true, true);
                                }
                                break;
                            case 'PUBQR':
                                if ($this->passAlle[$_POST['rowid']]['PUBQR'] == '') {
                                    /*
                                     * La gestione non è abilitata dal check PRODWONLINE
                                     */

                                    break;
                                }

                                $this->passAlle[$_POST['rowid']]['PASDWONLINE'] = ($this->passAlle[$_POST['rowid']]['PASDWONLINE'] == '1' ? '0' : '1');
                                Out::setCellValue($this->gridAllegati, $_POST['rowid'], 'PUBQR', $this->praLib->getFlagPASDWONLINE($this->passAlle[$_POST['rowid']]['PASDWONLINE']));
                                break;
                        }
                        break;
                    case $this->gridAltriDestinatari:
                        $destinatario = $this->destinatari[$_POST['rowid']];
                        switch ($_POST['colName']) {
                            case 'VEDI':
//                                $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
//                                $Metadati = unserialize($pracomP_rec['COMMETA']);
//                                $param['Segnatura'] = $Metadati['DatiProtocollazione']['Segnatura']['value'];
//                                include_once ITA_BASE_PATH . '/apps/Protocollo/proPaleo4.class.php';
//                                $proPaleo4 = new proPaleo4();
//                                $valore = $proPaleo4->InvioMail($param);
//                                if ($valore['Status'] == "-1") {
//                                    Out::msgStop("Attenzione!!!!!", $valore['Message']);
//                                    break;
//                                }
//                                break;
                                if ($destinatario['IDMAIL']) {
                                    $model = 'emlViewer';
                                    $_POST['event'] = 'openform';
                                    $_POST['codiceMail'] = $destinatario['IDMAIL'];
                                    $_POST['tipo'] = 'id';
                                    itaLib::openForm($model);
                                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                    $model();
                                }
                                break;
                            case 'CONSEGNA':
                                if ($destinatario['IDMAIL']) {
                                    $icon = $this->praLib->GetIconAccettazioneConsegna($this->destinatari[$_POST['rowid']]['IDMAIL'], $this->keyPasso, "PASSO");
                                    if (!$icon['IDMAILCON']) {
                                        Out::msgInfo("Attenzione!!", "Mail di avvenuta consegna non ricevuta per invio a " . $this->destinatari[$_POST['rowid']]['NOME']);
                                        break;
                                    }
                                    $model = 'emlViewer';
                                    $_POST['event'] = 'openform';
                                    $_POST['codiceMail'] = $icon['IDMAILCON'];
                                    $_POST['tipo'] = 'id';
                                    itaLib::openForm($model);
                                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                    $model();
                                }
                                break;
                            case 'ACCETTAZIONE':
                                if ($destinatario['IDMAIL']) {
                                    $icon = $this->praLib->GetIconAccettazioneConsegna($this->destinatari[$_POST['rowid']]['IDMAIL'], $this->keyPasso, "PASSO");
                                    if (!$icon['IDMAILACC']) {
                                        Out::msgInfo("Attenzione!!", "Mail accettazione non ricevuta per invio a " . $this->destinatari[$_POST['rowid']]['NOME']);
                                        break;
                                    }
                                    $model = 'emlViewer';
                                    $_POST['event'] = 'openform';
                                    $_POST['codiceMail'] = $icon['IDMAILACC'];
                                    $_POST['tipo'] = 'id';
                                    itaLib::openForm($model);
                                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                    $model();
                                }
                                break;
                            case 'SBLOCCA':
//                                $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
//                                if ($pracomP_rec["COMPRT"]) {
//                                    Out::msgInfo("Attenzione!!", "Impossibile sbloccare l'invio. La comuncazione risulta protocollata.");
//                                    break;
//                                }
                                if ($this->praReadOnly == true) {
                                    break;
                                }
                                if ($destinatario['IDMAIL']) {
                                    $msg = "<br><br><b>Attenzione!! Questa comunicazione risulta inviata, sbloccando si perderà l'id unico della mail e occorrerà reinviarla.</b><br><br>";
                                    $icon = $this->praLib->GetIconAccettazioneConsegna($destinatario['IDMAIL'], $this->keyPasso, "PASSO");
                                    if (strpos($icon["accettazione"], "icon-check") !== false) {
                                        $msg .= "<b>Attenzione.In questa comunicazione in partenza sono presenti delle ricevute di accettazione o consegna.<br>Sbloccando la comunicazione tutti i riferimenti andranno persi e bisognerà attendere eventuali ricevute<br>per collegare la nuova mail in partenza</b>";
                                    }
                                    $this->rowidAppoggio = $_POST['rowid'];
                                    $this->praLib->GetMsgInputPassword($this->nameForm, "Sblocco Comunicazione in Partenza", "PAR", $msg);
                                }
                                break;
                        }

                        break;
                }
                break;
            case 'returnAllegatiProc':
                $this->caricaAllegatiInterno();
                break;
            case 'returnAnapra':
                $Anapra_rec = $this->praLib->GetAnapra($_POST['rowData']['ID_ANAPRA'], "rowid");
                $this->testiAssociati = $this->GetTestiAssociati($Anapra_rec['PRANUM']);
                if ($this->testiAssociati == false) {
                    Out::msgInfo("Ricerca testi", "testi associati non trovati");
                    break;
                }
                praRic::ricImmProcedimenti($this->testiAssociati, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), 'returnTestiAssociati', "Testi Disponibili per il procedimento n. " . $Anapra_rec['PRANUM']);
                break;
            case 'returnCampiPdf':
                $campi = array();
                $selRows = explode(",", $_POST['retKey']);
                foreach ($selRows as $riga) {
                    $campi[] = $this->arrayInfo['arrayTable'][$riga];
                }
                $this->caricaCampiDaPdf($campi, $this->keyPasso); //, $this->arrayInfo['fileName']);
                $this->passAlle[$this->rowidAppoggio]['STATO'] = "Dati Aggiuntivi caricati";
                $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');
                $this->BloccaDescAllegati();
                break;
            case 'returnCtrPasswordArrivo':
                if ($_POST['returnVal']) {
                    $pracom_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMRIF = " . $_POST['returnVal'], false);
                    $pracom_rec['COMDAT'] = "";
                    $update_Info = "Oggetto : Sblocco comunicazione in arrivo n. " . $_POST['returnVal'];
                    if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                        Out::msgStop("Errore", "Aggiornamento su PRACOM fallito");
                        break;
                    }
                    $this->Dettaglio($this->keyPasso);
                }
                break;
            case 'returnRubricaWSP':
                if (!$_POST['rowData']) {
                    Out::msgStop("Attenzione", "Non è stata selezionata alcuna anagrafica. La protocollazione è stata interrotta");
                    break;
                }
                $this->idCorrispondente = $_POST['rowData']['codice'];
                $this->ProtocolloICCSP();
                break;
            case 'returnRubricaWSA':
                if (!$_POST['rowData']) {
                    Out::msgStop("Attenzione", "Non è stata selezionata alcuna anagrafica. La protocollazione è stata interrotta");
                    break;
                }
                $this->idCorrispondente = $_POST['rowData']['codice'];
                $this->ProtocolloICCSA();
                break;
            case 'returnMittente':
                switch ($_POST['retid']) {
                    case "ANAMED":
                        $anamed_rec = $this->proLib->GetAnamed($_POST['rowData']['ROWID'], "rowid");
                        Out::valore($this->nameForm . "_MITTENTE", $anamed_rec['MEDCOD']);
                        Out::valore($this->nameForm . "_DESC_MITTENTE", $anamed_rec['MEDNOM']);
//Out::valore($this->nameForm . "_EMAIL_MITTENTE", $anamed_rec['MEDEMA']);
                        Out::valore($this->nameForm . "_CODFISC_MITTENTE", $anamed_rec['MEDFIS']);
                        break;
                    case "ANADES":
                        $anades_rec = $this->praLib->GetAnades($_POST['rowData']['ROWID'], "rowid");
                        Out::valore($this->nameForm . "_MITTENTE", $anades_rec['DESCOD']);
                        Out::valore($this->nameForm . "_DESC_MITTENTE", $anades_rec['DESNOM']);
                        Out::valore($this->nameForm . "_EMAIL_MITTENTE", $anades_rec['DESEMA']);
                        Out::valore($this->nameForm . "_CODFISC_MITTENTE", $anades_rec['DESFIS']);
                        break;
                }
                break;
            case 'returnMailPARTENZA':
                $meta['DatiProtocollazione']['tipoCom']['value'] = "PARTENZA";
                $meta['DatiProtocollazione']['tipoCom']['status'] = "1";
                $meta['DatiProtocollazione']['tipoCom']['msg'] = "Tipo Comunicazione";
                $pracom_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMTIP = 'P' AND COMPAK = '$this->keyPasso'", false);
                $this->SendMailProtocollo($meta, $pracom_rec);
                break;
            case 'returnMailARRIVO':
                $meta['DatiProtocollazione']['tipoCom']['value'] = "ARRIVO";
                $meta['DatiProtocollazione']['tipoCom']['status'] = "1";
                $meta['DatiProtocollazione']['tipoCom']['msg'] = "Tipo Comunicazione";
                $pracom_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMTIP = 'A' AND COMPAK = '$this->keyPasso'", false);
                $this->SendMailProtocollo($meta, $pracom_rec);
                break;
            case 'returnAnades':
                $arrSogg = explode(",", $_POST['retKey']);
                foreach ($arrSogg as $key => $rowid) {
                    $anades_rec = $this->praLib->GetAnades($rowid, "rowid");
                    $sogg = array();
                    $sogg['ROWID'] = 0;
                    $sogg['CODICE'] = $anades_rec['DESCOD'];
                    $sogg['NOME'] = "<span style = \"color:orange;\">" . $anades_rec['DESNOM'] . "</span>";
                    $sogg['FISCALE'] = $anades_rec['DESFIS'];
                    $sogg['INDIRIZZO'] = $anades_rec['DESIND'] . " " . $anades_rec['DESCIV'];
                    $sogg['COMUNE'] = $anades_rec['DESCIT'];
                    $sogg['CAP'] = $anades_rec['DESCAP'];
                    $sogg['DATAINVIO'] = "";
                    $sogg['PROVINCIA'] = $anades_rec['DESPRO'];
                    if ($anades_rec['DESPEC']) {
                        $sogg['MAIL'] = $anades_rec['DESPEC'];
                    } else {
                        $sogg['MAIL'] = $anades_rec['DESEMA'];
                    }
                    $this->destinatari[] = $sogg;
                }
                $this->CaricaGriglia($this->gridAltriDestinatari, $this->destinatari, "1", "100");
                break;
            case "returnAnaddo":
                $this->DecodAnaddo($_POST["retKey"], "rowid");
                break;
            case "returnAnacla":
                $this->DecodAnacla($_POST["retKey"], "rowid");
                break;
            case 'returnDestinazioni':
                $this->passAlle[$_POST['rowidAlle']]['PASDEST'] = serialize($_POST['destinazioni']);
//                if (is_array($_POST['destinazioni'])) {
//                    foreach ($_POST['destinazioni'] as $dest) {
//                        $Anaddo_rec = $this->praLib->GetAnaddo($dest);
//                        $strDest .= $Anaddo_rec['DDONOM'] . "<br>";
//                    }
//                }
                $strDest = $this->caricaDestinazioniAllegato($this->passAlle[$_POST['rowidAlle']]['PASDEST']);
                $this->passAlle[$_POST['rowidAlle']]['DESTINAZIONI'] = $strDest;
                $this->CaricaGriglia($this->gridAllegati, $this->passAlle);
                $this->BloccaDescAllegati();
                break;
            case 'returnExplodeZipPlain':
                foreach ($_POST['rowData'] as $rowData) {
                    $rowData['isLeaf'] = 'true';
                    $this->caricaAllegatoDaZip($rowData);
                }
                $this->CaricaGriglia($this->gridAllegati, $this->passAlle);
                $this->BloccaDescAllegati();

                break;
            case 'returnExplodeZip':
                $arrayFile = $this->praLib->CaricaAllegatoDaZip($_POST['rowData'], $this->passAlle);
                if ($arrayFile == false) {
                    break;
                }
                if (isset($arrayFile["daFile"])) {
                    foreach ($this->passAlle as $posE => $allegato) {
                        if ($allegato['RANDOM'] == 'ESTERNO') {
                            $posEsterno = $posE;
                            $parent = $allegato['PROV'];
                            break;
                        }
                    }

                    $i = $posEsterno + 1;
                    $trovato = false;
                    while ($trovato == false) {
                        if ($i >= count($this->passAlle)) {
                            $trovato = true;
                        } else {
                            if ($this->passAlle[$i]['level'] == 0) {
                                $trovato = true;
                            } else {
                                $i++;
                            }
                        }
                    }
                    $arrayTop = array_slice($this->passAlle, 0, $i);
                    $arrayDown = array_slice($this->passAlle, $i);
                    $arrayFile["Allegato"]['parent'] = $parent;
                    $arrayFile["Allegato"]['PROV'] = $i;
                    $arrayFile["Allegato"]['NAME'] = '<span style = "color:orange;">' . $arrayFile['Allegato']['NAME'] . '</span>';
                    $arrayFile["Allegato"]['INFO'] = $arrayFile["Allegato"]['NOTE'];
                    $arrayFile["Allegato"]['FILEINFO'] = $arrayFile["Allegato"]['NOTE'];
                    $arrayTop[] = $arrayFile["Allegato"];

                    foreach ($arrayDown as $chiave => $recordDown) {
                        if ($recordDown['level'] == 1) {
                            $arrayDown[$chiave]['PROV'] = $recordDown['PROV'] + 1;
                        }
                    }
                    $this->passAlle = array_merge($arrayTop, $arrayDown);
                } else {
                    
                }
                $this->CaricaGriglia($this->gridAllegati, $this->passAlle);
                $this->BloccaDescAllegati();
                break;
            case "returnFromSignAuthTestiBase";
                if ($_POST['result'] === true) {
                    $fileOrig = pathinfo($this->passAlle[$this->rowidAppoggio]['FILEORIG'], PATHINFO_FILENAME) . ".pdf.p7m";
                    $this->passAlle[$this->rowidAppoggio]['PREVIEW'] = '<span class="ita-icon ita-icon-shield-green-24x24" title="Verifica il file Firmato"></span>';
                    $this->passAlle[$this->rowidAppoggio]['STATO'] = "<span>Pdf firmato caricato in data " . date('d/m/Y') . " alle ore " . date('H:i:s') . "</span>";
                    Out::openDocument(utiDownload::getUrl($_POST['outputFileName'], $_POST['outputFilePath']));
                    $this->CaricaGriglia($this->gridAllegati, $this->passAlle);
                    $this->BloccaDescAllegati();
                } elseif ($_POST['result'] === false) {
                    Out::msgStop("Firma remota", "Firma Fallita");
                }
                break;
            case "returnFromSignAuth";
                if ($_POST['result'] === true) {
                    if ($this->passAlle[$this->rowidAppoggio]['FILEPATH'] != $_POST['outputFilePath']) {
                        if (!@unlink($this->passAlle[$this->rowidAppoggio]['FILEPATH'])) {
                            Out::msgStop("Firma remota", "cancellazione file " . $this->passAlle[$this->rowidAppoggio]['FILEPATH'] . " fallita");
                            break;
                        }
                    }
                    $this->passAlle[$this->rowidAppoggio]['FILEORIG'] = $_POST['outputFileName'];
                    $this->passAlle[$this->rowidAppoggio]['FILEPATH'] = $_POST['outputFilePath'];
//$this->passAlle[$this->rowidAppoggio]['FILENAME'] = $_POST['outputFileName'];
                    $this->passAlle[$this->rowidAppoggio]['FILENAME'] = pathinfo($_POST['outputFilePath'], PATHINFO_BASENAME);
                    $this->passAlle[$this->rowidAppoggio]['RANDOM'] = pathinfo($_POST['outputFilePath'], PATHINFO_BASENAME);
                    $this->passAlle[$this->rowidAppoggio]['PREVIEW'] = '<span class="ita-icon ita-icon-shield-green-24x24" title="Verifica il file Firmato"></span>';
                    $this->passAlle[$this->rowidAppoggio]['STATO'] = '<span>Clicca per verificare il file</span>';
                    $this->passAlle[$this->rowidAppoggio]['PASDATAFIRMA'] = date("d/m/Y");
                    if ($this->passAlle[$this->rowidAppoggio]['ROWID'] != 0) {
//$this->passAlle[$this->rowidAppoggio]['NAME'] = $this->passAlle[$this->rowidAppoggio]['FILENAME'];
                        $this->passAlle[$this->rowidAppoggio]['NAME'] = $_POST['outputFileName'];
                        $pasdoc_rec = $this->praLib->GetPasdoc($this->passAlle[$this->rowidAppoggio]['ROWID'], "ROWID");
                        $pasdoc_rec['PASFIL'] = pathinfo($_POST['outputFilePath'], PATHINFO_BASENAME);
                        $pasdoc_rec['PASLNK'] = "allegato://" . pathinfo($_POST['outputFilePath'], PATHINFO_BASENAME);
                        $pasdoc_rec['PASLOG'] = $this->passAlle[$this->rowidAppoggio]['STATO'];
                        $pasdoc_rec['PASNAME'] = $this->passAlle[$this->rowidAppoggio]['FILEORIG'];
//$pasdoc_rec['PASNOT'] = $this->passAlle[$this->rowidAppoggio]['FILEINFO'];
                        $pasdoc_rec['PASDATAFIRMA'] = $this->passAlle[$this->rowidAppoggio]['PASDATAFIRMA'];
                        $update_Info = 'Oggetto: Aggiornamento allegato ' . $this->passAlle[$this->rowidAppoggio]['FILENAME'];
                        if (!$this->updateRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $update_Info)) {
                            return false;
                        }
                    } else {
//$this->passAlle[$this->rowidAppoggio]['NAME'] = "<span style = \"color:orange;\">" . $this->passAlle[$this->rowidAppoggio]['FILENAME'] . "</span>";
                        $this->passAlle[$this->rowidAppoggio]['NAME'] = "<span style = \"color:orange;\">" . $_POST['outputFileName'] . "</span>";
                    }
                    Out::openDocument(utiDownload::getUrl($_POST['inputFileName'] . ".p7m", $_POST['outputFilePath']));
                    $this->CaricaGriglia($this->gridAllegati, $this->passAlle);
                    $this->BloccaDescAllegati();
                } elseif ($_POST['result'] === false) {
                    Out::msgStop("Firma remota", "Firma Fallita");
                }
                break;
            case "returnGestDest":
                if ($_POST["rowid"] != "") {
                    $this->destinatari[$_POST["rowid"]] = $_POST['destinatario'];
                } else {
                    $this->destinatari[] = $_POST['destinatario'];
                }
                $this->CaricaGriglia($this->gridAltriDestinatari, $this->destinatari, "1", "100");
                break;
            case 'returnProtPresenti':
                $Alle = $this->passAlle[$this->rowidAppoggio];
                Out::msgQuestion("ATTENZIONE!", "Si Desidera protocollare l'allegato <b>" . $Alle['FILEINFO'] . "</b> con il numero protocollo <b>" . $_POST['rowData']['NUMERO'] . "</b>? - (" . $_POST['rowData']['DESC'] . ")", array(
                    'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaBloccaAlle', 'model' => $this->nameForm, 'shortCut' => "f8"),
                    'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaBloccaAlle', 'model' => $this->nameForm, 'shortCut' => "f5")
                        )
                );
//$this->tipoProtSel = $_POST['rowData']['TIPO'];
                $this->tipoProtSel = $_POST['rowData'];
                break;
            case 'returnPraDettNote':
                $this->returnFormAddNota();
                break;
            case "returnTabdag":
                $Tabdag_rec = $this->proLib->GetTabdag($_POST['retKey'], 'rowid');
                $this->rowidMail = $Tabdag_rec['ROWID'];
                Out::valore($this->nameForm . '_EMAIL_MITTENTE', $Tabdag_rec['TDAGVAL']);
                break;
            case 'returnRicIPA':
                Out::valore($this->nameForm . '_DESC_MITTENTE', $_POST['PRONOM']);
                Out::valore($this->nameForm . '_EMAIL_MITTENTE', $_POST['PROMAIL']);
                break;
            case "returnAnadoctipreg":
                $this->DecodeAnadoctipreg($_POST['retKey'], 'rowid');
                break;
            case "returnPassiAntecedenti":
                $this->Dettaglio($_POST['retKey'], "rowid");
                break;
            case "returnPassiSel":
                $this->praReadOnly = $_POST['praReadonly'];
                $this->dettaglio($_POST['retKey'], 'rowid');
                break;
            case "returnRicIPADest":
                $model = "praGestDestinatari";
                $dest = array();
                $dest['NOME'] = $_POST['PRONOM'];
                $dest['INDIRIZZO'] = $_POST['PROIND'] . " " . $_POST['PROCIV'];
                $dest['COMUNE'] = $_POST['PROCIT'];
                $dest['PROVINCIA'] = $_POST['PROPRO'];
                $dest['CAP'] = $_POST['PROCAP'];
                $dest['MAIL'] = $_POST['PROMAIL'];
                itaLib::openForm($model);
                $praGestDest = itaModel::getInstance($model, $model);
                $praGestDest->setReturnEvent("returnGestDest");
                $praGestDest->setReturnModel($this->nameForm);
                $praGestDest->setMode('edit');
                $praGestDest->setEvent('openform');
                $praGestDest->setDestinatario($dest);
                $praGestDest->setRowid('');
                $praGestDest->parseEvent();
                break;
            case 'returnDestinazioniDest':
                if (is_array($_POST['destinazioni'])) {
                    foreach ($_POST['destinazioni'] as $key => $dest) {
                        $anaddo_rec = $this->praLib->GetAnaddo($dest);
                        $sogg = array();
                        $sogg['ROWID'] = 0;
                        $sogg['CODICE'] = $anaddo_rec['DDOCOD'];
                        $sogg['NOME'] = "<span style = \"color:orange;\">" . $anaddo_rec['DDONOM'] . "</span>";
                        $sogg['FISCALE'] = $anaddo_rec['DDOFIS'];
                        $sogg['INDIRIZZO'] = $anaddo_rec['DDOIND'];
                        $sogg['COMUNE'] = $anaddo_rec['DDOCIT'];
                        $sogg['CAP'] = $anaddo_rec['DDOCAP'];
                        $sogg['DATAINVIO'] = "";
                        $sogg['PROVINCIA'] = $anaddo_rec['DDOPRO'];
                        $sogg['MAIL'] = $anaddo_rec['DDOEMA'];
                        $this->destinatari[] = $sogg;
                        $this->CaricaGriglia($this->gridAltriDestinatari, $this->destinatari, "1", "100");
                    }
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_passAlle');
        App::$utente->removeKey($this->nameForm . '_passCom');
        App::$utente->removeKey($this->nameForm . '_currGesnum');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnMethod');
        App::$utente->removeKey($this->nameForm . '_daMail');
        App::$utente->removeKey($this->nameForm . '_datiForm');
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_$allegati');
        App::$utente->removeKey($this->nameForm . '_keyPasso');
        App::$utente->removeKey($this->nameForm . '_iniDateEdit');
        App::$utente->removeKey($this->nameForm . '_pagina');
        App::$utente->removeKey($this->nameForm . '_allegatiComunicazione');
        App::$utente->removeKey($this->nameForm . '_sql');
        App::$utente->removeKey($this->nameForm . '_selRow');
        App::$utente->removeKey($this->nameForm . '_arrayInfo');
        App::$utente->removeKey($this->nameForm . '_currAllegato');
        App::$utente->removeKey($this->nameForm . '_tipoFile');
        App::$utente->removeKey($this->nameForm . '_testiAssociati');
        App::$utente->removeKey($this->nameForm . '_chiudiForm');
        App::$utente->removeKey($this->nameForm . '_idCorrispondente');
        App::$utente->removeKey($this->nameForm . '_datiRubricaWS');
        App::$utente->removeKey($this->nameForm . '_docCommercio');
        App::$utente->removeKey($this->nameForm . '_destinatari');
        App::$utente->removeKey($this->nameForm . '_tipoProtSel');
        App::$utente->removeKey($this->nameForm . '_datiWS');
        App::$utente->removeKey($this->nameForm . '_noteManager');
        App::$utente->removeKey($this->nameForm . '_rowidMail');
        App::$utente->removeKey($this->nameForm . '_praReadOnly');
        App::$utente->removeKey($this->nameForm . '_allegatiPrtSel');
        App::$utente->removeKey($this->nameForm . '_tipoProtocollo');
        App::$utente->removeKey($this->nameForm . '_praPassi');
        App::$utente->removeKey($this->nameForm . '_Propas');
        App::$utente->removeKey($this->nameForm . '_praCompDatiAggiuntiviFormname');
        App::$utente->removeKey($this->nameForm . '_mettiAllaFirma');
        App::$utente->removeKey($this->nameForm . '_proSubTrasmissioni');
        App::$utente->removeKey($this->nameForm . '_daTrasmissioni');
        App::$utente->removeKey($this->nameForm . '_presenzaAllegatiRiservati');

        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($this->returnModel) {
            $_POST = array();
            $_POST['page'] = $this->pagina;
            $_POST['sql'] = $this->sql;
            $_POST['selRow'] = $this->selRow;
            $_POST['gesnum'] = $this->currGesnum;
            if ($this->datiForm) {
                $_POST['DATIPASSO'] = $this->datiForm;
            }
            $model = $this->returnModel;
            $objModel = itaModel::getInstance($model);
            $objModel->setEvent($this->returnMethod);
            $objModel->parseEvent();
        }

        if ($close) {
            $this->close();
        }
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridAllegati);
        TableView::clearGrid($this->gridAllegati);
        TableView::disableEvents($this->gridAltriDestinatari);
        TableView::clearGrid($this->gridAltriDestinatari);
        $this->passAlle = array();
        $this->passCom = array();
        $this->destinatari = array();
        $this->datiWS = null;
        $this->noteManager = null;
        $this->rowidMail = null;
        $this->keyPasso = null;
        $this->mettiAllaFirma = "";
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Invia');
        Out::hide($this->nameForm . '_InviaProcedura');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_ProtocollaPartenza');
        Out::hide($this->nameForm . '_ProtocollaArrivo');
        Out::hide($this->nameForm . '_RimuoviProtocollaA');
        Out::hide($this->nameForm . '_RimuoviProtocollaP');
        Out::hide($this->nameForm . '_SbloccaCom');
        Out::hide($this->nameForm . '_SbloccaComArrivo');
        Out::hide($this->nameForm . '_utenteEdit');
        Out::hide($this->nameForm . '_Risposta');
        Out::hide($this->nameForm . '_VediProtPartenza');
        Out::hide($this->nameForm . '_VediProtArrivo');
        Out::hide($this->nameForm . '_GestioneProtocolloPartenza');
        Out::hide($this->nameForm . '_GestioneProtocolloArrivo');
        Out::hide($this->nameForm . '_VediMailArrivo');
        Out::hide($this->nameForm . "_InviaProtocolloPar");
        Out::hide($this->nameForm . "_InviaProtocolloArr");
        Out::hide($this->nameForm . "_divInPartenzaOld"); // vecchia gestione singolo dest
        Out::hide($this->nameForm . "_BloccaAllegatiProt");
        Out::hide($this->nameForm . "_BloccaAllegatiDoc");
        Out::hide($this->nameForm . "_BloccaAllegatiArr");
        Out::hide($this->nameForm . '_NuovaComDaProt');
        Out::hide($this->nameForm . '_AggiungiRisposta');
        Out::hide($this->nameForm . '_CreaNuovoPasso');
        Out::hide($this->nameForm . '_EMAIL_MITTENTE_butt');
        Out::hide($this->nameForm . '_MailPreferita');
        Out::hide($this->nameForm . '_divAlertRicevute');
//        if ($this->tipoProtocollo == 'Iride' || $this->tipoProtocollo == 'Jiride') {
//            Out::hide($this->nameForm . '_CercaAnagrafeArr');
//            Out::hide($this->nameForm . '_VediFamigliaArr');
//        } else {
        Out::hide($this->nameForm . '_CercaAnagrafeProtocolloArr');
//        }
        Out::hide($this->nameForm . '_VerificaMailWs');
        Out::hide($this->nameForm . '_PRACOM[COMIDMAIL]_field');
        Out::hide($this->nameForm . '_VediIdDocPartenza');
        Out::hide($this->nameForm . '_NuoviPassiDaDest');
//
//        if ($this->tipoProtocollo == 'Iride') {
//            Out::hide($this->nameForm . '_CercaAnagrafe');
//        } else {
        Out::hide($this->nameForm . '_CercaAnagrafeProtocollo');
//}

        Out::hide($this->nameForm . '_ValidaDatiAggiuntivi');
        Out::hide($this->nameForm . '_Trasmissioni');
    }

    function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = '20') {
        $arrayGrid = array();
        foreach ($appoggio as $arrayRow) {
            unset($arrayRow['PASMETA']);
            $arrayGrid[] = $arrayRow;
        }
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $arrayGrid,
            'rowIndex' => 'idx')
        );
        if ($tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    private function caricaSubForms($formDatiAggiuntivi, $perms) {
//        if (!is_array($this->praCompDatiAggiuntiviFormname)) {
        /*
         * Carico pannello Dati aggiuntivi
         */
        Out::html($this->nameForm . '_paneDatiAggiuntivi', '');

        $praCompDatiAggiuntivi = itaFormHelper::innerForm($formDatiAggiuntivi, $this->nameForm . '_paneDatiAggiuntivi');
        $praCompDatiAggiuntivi->setEvent('openform');
        $praCompDatiAggiuntivi->setPropak($this->keyPasso);
        $praCompDatiAggiuntivi->setReturnModel($this->nameForm);
        $praCompDatiAggiuntivi->setReturnEvent('returnFromGestPasso');
        $praCompDatiAggiuntivi->setReturnId('');
        $praCompDatiAggiuntivi->setReadOnly($perms['noEdit'] === '1' ? true : false);
        $praCompDatiAggiuntivi->parseEvent();

        $this->praCompDatiAggiuntiviFormname = array($formDatiAggiuntivi, $praCompDatiAggiuntivi->getNameForm());

//        }

        if ($this->proSubTrasmissioni == "") {

            /*
             * Carica Pannello Assegnazioni
             */
            $model = 'proSubTrasmissioni';
            $proSubTrasmissioni = itaFormHelper::innerForm($model, $this->nameForm . '_paneAssegnazioniPassi');
            $proSubTrasmissioni->setEvent('openform');
            $proSubTrasmissioni->setTipoProt(praLibAssegnazionePassi::TYPE_ASSEGNAZIONI);
            $proSubTrasmissioni->setNameFormChiamante($this->nameForm);
            $proSubTrasmissioni->parseEvent();
            $this->proSubTrasmissioni = $proSubTrasmissioni->nameForm;
        }
    }

    public function GetImgPreview($ext, $path, $doc) {
        $title = "Clicca per le funzioni disponibili";
        if (strtolower($ext) == "pdf") {
            $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"$title\"></span>";
        } else if (strtolower($ext) == "xhtml") {
            if (file_exists($path . "/" . pathinfo($doc['PASFIL'], PATHINFO_FILENAME) . ".pdf.p7m") || file_exists($path . "/" . pathinfo($doc['PASFIL'], PATHINFO_FILENAME) . ".pdf.p7m.p7m")) {
                $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-shield-green-24x24\" title=\"File firmato caricato\"></span>";
            } else if (file_exists($path . "/" . pathinfo($doc['PASFIL'], PATHINFO_FILENAME) . ".pdf")) { //mm 22/11/2012
                $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-pdf-24x24\" title=\"PDF Generato\"></span>";
            } else {
                $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Genera PDF\"></span>";
            }
        } else if (strtolower($ext) == "docx") {
            if (file_exists($path . "/" . pathinfo($doc['PASFIL'], PATHINFO_FILENAME) . ".pdf.p7m") || file_exists($path . "/" . pathinfo($doc['PASFIL'], PATHINFO_FILENAME) . ".pdf.p7m.p7m")) {
                $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-shield-green-24x24\" title=\"File firmato caricato\"></span>";
            } else if (file_exists($path . "/" . pathinfo($doc['PASFIL'], PATHINFO_FILENAME) . ".pdf")) { //mm 22/11/2012
                $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-pdf-24x24\" title=\"PDF Generato\"></span>";
            } else {
                $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Genera PDF\"></span>";
            }
        } else if (strtolower($ext) == "p7m") {
            $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-shield-green-24x24\" title=\"Verifica il file Firmato\"></span>";
        } else if (in_array(strtolower($ext), array('xls', 'xlsx'))) {
            if (file_exists($path . '/' . $this->getFilenameFoglioElaborato($doc['PASFIL'], true))) {
                $preview = '<span style="display:inline-block;" class="ita-icon ita-icon-excel-flat-24x24" title="Definitivo caricato"></span>';
            } else {
                $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"$title\"></span>";
            }
        } else {
            $preview = "<span style=\"display:inline-block;\"class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"$title\"></span>";
        }
        if ($this->flagAssegnazioniPasso) {
            if (!$this->checkFrimaAllegato(false, $doc['ROWID'])) {
                $preview .= "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-sigillo-24x24\" title=\"Allegato messo allafirma\"></span>";
            }
        }
        return $preview;
    }

    public function DecodIntestatario($codice, $tipo = "esibente") {
        switch ($tipo) {
            case "esibente":
                $anades_rec = $this->praLib->GetAnades($codice, "ruolo", false, praRuolo::getSystemSubjectCode("ESIBENTE"));
                if (!$anades_rec) {
                    $anades_rec = $this->praLib->GetAnades($codice, "ruolo", false, "");
                }
                break;
            default:
                $anades_rec = $this->praLib->GetAnades($_POST['retKey'], "rowid");
//Out::valore($this->nameForm."_PRACOM[COMINT]", 0);
                break;
        }
        Out::valore($this->nameForm . '_DESC_DESTINATARIO', $anades_rec['DESNOM']);
        Out::valore($this->nameForm . '_PRACOM[COMIND]', $anades_rec['DESIND'] . " " . $anades_rec['DESIND']);
        Out::valore($this->nameForm . '_PRACOM[COMCIT]', $anades_rec['DESCIT']);
        Out::valore($this->nameForm . '_PRACOM[COMPRO]', $anades_rec['DESPRO']);
        Out::valore($this->nameForm . '_PRACOM[COMCAP]', $anades_rec['DESCAP']);
        if ($anades_rec['DESPEC']) {
            Out::valore($this->nameForm . '_EMAIL_DESTINATARIO', $anades_rec['DESPEC']);
        } else {
            Out::valore($this->nameForm . '_EMAIL_DESTINATARIO', $anades_rec['DESEMA']);
        }
        Out::valore($this->nameForm . '_CODFISC_DESTINATARIO', $anades_rec['DESFIS']);
    }

    public function apriDuplica($last_propas) {
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDatiAggiuntivi");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCom");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDestinatari");

//
//Definisco il nuovo record del passo
//
        $Propas_new_rec = $last_propas;
        $Propas_new_rec['PRONUM'] = $this->currGesnum;
        $Propas_new_rec['PROSEQ'] = "";
        $Propas_new_rec['PROVISIBILITA'] = "Aperto";
        $Propas_new_rec['PROSTATO'] = "";
        $Propas_new_rec['PROFIN'] = "";
        $Propas_new_rec['PROPART'] = 0;
        $Propas_new_rec['PROPST'] = 0;
        $Propas_new_rec['ROWID'] = 0;
        $this->DecodResponsabile($Propas_new_rec['PRORPA'], 'codice', $Propas_new_rec);
        $open_Info = 'Oggetto: ' . $Propas_new_rec['PROPAK'];
        $this->openRecord($this->PRAM_DB, 'PROPAS', $open_Info);
        Out::valori($Propas_new_rec, $this->nameForm . '_PROPAS');

//
//Accendo e spengo div e bottoni
//
        Out::hide($this->nameForm . '_divAllegatiCom');
        if ($Propas_new_rec['PROPART'] == 1) {
            Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneArticoli");
            Out::showTab($this->nameForm . "_paneArticoli");
        } else {
            Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneArticoli");
            Out::hideTab($this->nameForm . "_paneArticoli");
        }

//
// Prendo la comunicazione in partenza
//
        $this->DecodPracomP($last_propas['PROPAK']);

//
//Prendo gli allegati se ci sono e dei testi base prendo i pdf e i p7m
//
        $this->CaricaAllegati($last_propas['PROPAK'], true);
        if ($this->passAlle) {
            $percorsoTmp = itaLib::getPrivateUploadPath();
            if (!@is_dir($percorsoTmp)) {
                if (!itaLib::createPrivateUploadPath()) {
                    Out::msgStop("Archiviazione Mail.", "Creazione ambiente di lavoro temporaneo fallita.");
                    $this->returnToParent();
                }
            }

            foreach ($this->passAlle as $key => $alle) {
//                if (isset($alle['ROWID'])) {
//                $this->passAlle[$key]['ROWID'] = 0;
                $randName = md5(rand() * time()) . "." . pathinfo($alle['FILENAME'], PATHINFO_EXTENSION);

                /*
                 * Faccio il copy solo se l'allegato è una foglia, quindi quando ha il file fisico
                 * altrimenti va in errore
                 */
                if ($alle['isLeaf'] == 'true') {
                    if (!@copy($alle['FILEPATH'], $percorsoTmp . "/" . $randName)) {
                        Out::msgStop("Duplica allegati", "Errore copia del file " . $alle['FILEPATH']);
                        return false;
                    }
                }
                $this->passAlle[$key]['FILEPATH'] = $percorsoTmp . "/" . $randName;
                $this->passAlle[$key]['RANDOM'] = $randName;
                $this->passAlle[$key]['FILENAME'] = $randName;
                $this->passAlle[$key]['PASUTELOG'] = App::$utente->getKey('nomeUtente');
                $this->passAlle[$key]['PASDATADOC'] = date("Ymd");
                $this->passAlle[$key]['PASORADOC'] = date("H:i:s");
                $this->passAlle[$key]['PASSHA2'] = hash_file('sha256', $this->passAlle[$key]['FILEPATH']);
                if (strpos($alle['PROVENIENZA'], 'TESTOBASE') !== false) {
                    $testoPath = pathinfo($alle['FILEPATH'], PATHINFO_DIRNAME);
                    $testoName = pathinfo($alle['FILEPATH'], PATHINFO_FILENAME);
                    if (file_exists($testoPath . "/" . $testoName . ".pdf.p7m")) {
                        if (!@copy($testoPath . "/" . $testoName . ".pdf.p7m", $percorsoTmp . "/" . pathinfo($randName, PATHINFO_FILENAME) . ".pdf.p7m")) {
                            Out::msgStop("Duplica allegati", "Errore copia del file " . $testoName . ".pdf.p7m");
                            return false;
                        }
                    }

                    if (file_exists($testoPath . "/" . $testoName . ".pdf")) {
                        if (!@copy($testoPath . "/" . $testoName . ".pdf", $percorsoTmp . "/" . pathinfo($randName, PATHINFO_FILENAME) . ".pdf")) {
                            Out::msgStop("Duplica allegati", "Errore copia del file " . $testoName . ".pdf");
                            return false;
                        }
                    }
                }
//                }
            }
        }

//
// Prendo i dati aggiuntivi se ci sono
//
//        $this->CaricoCampiAggiuntivi($last_propas['PROPAK']);
//        if ($this->altriDati) {
//            foreach ($this->altriDati as $key1 => $dato) {
//                if (isset($dato['ROWID'])) {
//                    $this->altriDati[$key1]['ROWID'] = 0;
//                }
//            }
//        }
    }

    public function GetArrayAzioniRO($ext) {
        switch ($ext) {
            case "xhtml.pdf.p7m":
            case "p7m":
                $arrayAzioni = array(
                    'F7-Visualizza Firme' => array('id' => $this->nameForm . '_VisualizzaFirme', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f7"),
                );
                break;
        }
        return $arrayAzioni;
    }

    public function GetArrayAzioni($ext, $pasdoc_rec) {

        $mettiAllaFirma = array();
        $togliDallaFirma = array();
        $propas_rec = $this->praLib->GetPropas($this->keyPasso, 'propak');
        //

        if (proWsClientHelper::CLIENT_ITALPROT && $propas_rec['PASPRO'] != 0 && $propas_rec['PROFIN'] == '' && $this->flagAssegnazioniPasso) {
            if (!$pasdoc_rec) {
                $mettiAllaFirma = array(
                    'F6-Metti alla Firma' => array('id' => $this->nameForm . '_VaiAllaFirma', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-new-32x32'", 'model' => $this->nameForm, 'shortCut' => "f6"),
                );
            } else {
                $docfirma_check = array();
                $keyLink = 'PRAM.PASDOC.' . $pasdoc_rec['ROWID'];
                $AnaDoc_rec = $this->proLib->GetAnaDocFromDocLink($propas_rec['PASPRO'], $propas_rec['PASPAR'], $keyLink);
                if ($AnaDoc_rec) {
                    $proLibAllegati = new proLibAllegati();
                    $docfirma_check = $proLibAllegati->GetDocfirma($AnaDoc_rec['ROWID'], 'rowidanadoc');
                }
                if ($docfirma_check && $docfirma_check['FIRDATA'] == '') {
                    $togliDallaFirma = array(
                        'F7-Togli dalla Firma' => array('id' => $this->nameForm . '_togliFirma', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-new-32x32'", 'model' => $this->nameForm, 'shortCut' => "f6"),
                    );
                } elseif (!$docfirma_check) {
                    $mettiAllaFirma = array(
                        'F6-Metti alla Firma' => array('id' => $this->nameForm . '_VaiAllaFirma', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-new-32x32'", 'model' => $this->nameForm, 'shortCut' => "f6"),
                    );
                }
            }
        }
        //
        switch ($ext) {
            case "xhtml.pdf.p7m":
                if ($pasdoc_rec['PASPRTROWID'] != 0 && $pasdoc_rec['PASPRTCLASS'] == "PRACOM") {
                    $arrayAzioni = array(
                        'F7-Visualizza Firme' => array('id' => $this->nameForm . '_VisualizzaFirme', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f7"),
                        'F9-Scarica File firmato' => array('id' => $this->nameForm . '_DownloadFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-download-32x32'", 'model' => $this->nameForm, 'shortCut' => "f9"),
                        'F6-Visualizza Testo Base' => array('id' => $this->nameForm . '_VisualizzaTestoBase', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f6"),
                            //'Controfirma' => array('id' => $this->nameForm . '_ControfirmaFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-sigillo-32x32'", 'model' => $this->nameForm),
//'F8-Cancella File firmato' => array('id' => $this->nameForm . '_DeleteFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-delete-32x32'", 'model' => $this->nameForm, 'shortCut' => "f8")
                    );
                } else {
                    $arrayAzioni = array(
                        'F7-Visualizza Firme' => array('id' => $this->nameForm . '_VisualizzaFirme', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f7"),
                        'Controfirma' => array('id' => $this->nameForm . '_ControfirmaFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-sigillo-32x32'", 'model' => $this->nameForm),
                        'F9-Scarica File firmato' => array('id' => $this->nameForm . '_DownloadFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-download-32x32'", 'model' => $this->nameForm, 'shortCut' => "f9"),
                        'F6-Visualizza Testo Base' => array('id' => $this->nameForm . '_VisualizzaTestoBase', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f6"),
                        'F8-Cancella File firmato' => array('id' => $this->nameForm . '_DeleteFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-delete-32x32'", 'model' => $this->nameForm, 'shortCut' => "f8"),
                    );
                }
                break;
            case "xhtml.pdf":
                if ($pasdoc_rec['PASPRTROWID'] != 0 && ($pasdoc_rec['PASPRTCLASS'] == "PRACOM" || $pasdoc_rec['PASPRTCLASS'] == "PROGES")) {
                    $arrayAzioni = array(
                        'F9-Scarica Pdf' => array('id' => $this->nameForm . '_DownloadFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-download-32x32'", 'model' => $this->nameForm, 'shortCut' => "f9"),
                        'F5-Visualizza Pdf' => array('id' => $this->nameForm . '_VisualizzaPdf', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f5"),
                        'F7-Visualizza Testo Base' => array('id' => $this->nameForm . '_VisualizzaTestoBase', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f7"),
                        'F4-Allega P7M' => array('id' => $this->nameForm . '_AllegaPdfFirmato', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-upload-32x32'", 'model' => $this->nameForm, 'shortCut' => "f4")
                    );
                } else {
                    $arrayAzioni = array(
                        'F4-Allega P7M' => array('id' => $this->nameForm . '_AllegaPdfFirmato', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-upload-32x32'", 'model' => $this->nameForm, 'shortCut' => "f4"),
                        'F9-Scarica Pdf' => array('id' => $this->nameForm . '_DownloadFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-download-32x32'", 'model' => $this->nameForm, 'shortCut' => "f9"),
                        'F8-Cancella Pdf' => array('id' => $this->nameForm . '_DeleteFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-delete-32x32'", 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Visualizza Pdf' => array('id' => $this->nameForm . '_VisualizzaPdf', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f5"),
                        'F7-Visualizza Testo Base' => array('id' => $this->nameForm . '_VisualizzaTestoBase', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f7"),
                        'F6-Firma Allegato' => array('id' => $this->nameForm . '_FirmaFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-sigillo-32x32'", 'model' => $this->nameForm, 'shortCut' => "f6")
                    );
                }
                break;
            case "xhtml":
                if ($pasdoc_rec['PASPRTROWID'] != 0 && ($pasdoc_rec['PASPRTCLASS'] == "PRACOM" || $pasdoc_rec['PASPRTCLASS'] == "PROGES")) {
                    $arrayAzioni = array();
                } else {
                    $arrayAzioni = array(
                        'F5-Genera PDF' => array('id' => $this->nameForm . '_ConfermaGenPdf', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-pdf-32x32'", 'model' => $this->nameForm, 'shortCut' => "f5")
                    );
                }
                break;

            case 'docx.pdf.p7m':
                if ($pasdoc_rec['PASPRTROWID'] != 0 && $pasdoc_rec['PASPRTCLASS'] == "PRACOM") {
                    $arrayAzioni = array(
                        'F7-Visualizza Firme' => array('id' => $this->nameForm . '_VisualizzaFirme', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f7"),
                        'F9-Scarica File firmato' => array('id' => $this->nameForm . '_DownloadFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-download-32x32'", 'model' => $this->nameForm, 'shortCut' => "f9"),
                        'F6-Visualizza Testo Base' => array('id' => $this->nameForm . '_VisualizzaTestoBase', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f6"),
                    );
                } else {
                    $arrayAzioni = array(
                        'F7-Visualizza Firme' => array('id' => $this->nameForm . '_VisualizzaFirme', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f7"),
                        'Controfirma' => array('id' => $this->nameForm . '_ControfirmaFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-sigillo-32x32'", 'model' => $this->nameForm),
                        'F9-Scarica File firmato' => array('id' => $this->nameForm . '_DownloadFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-download-32x32'", 'model' => $this->nameForm, 'shortCut' => "f9"),
                        'F6-Visualizza Testo Base' => array('id' => $this->nameForm . '_VisualizzaTestoBase', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f6"),
                        'F8-Cancella File firmato' => array('id' => $this->nameForm . '_DeleteFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-delete-32x32'", 'model' => $this->nameForm, 'shortCut' => "f8"),
                    );
                }
                break;
            case 'docx.pdf':
                if ($pasdoc_rec['PASPRTROWID'] != 0 && ($pasdoc_rec['PASPRTCLASS'] == "PRACOM" || $pasdoc_rec['PASPRTCLASS'] == "PROGES")) {
                    $arrayAzioni = array(
                        'F9-Scarica Pdf' => array('id' => $this->nameForm . '_DownloadFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-download-32x32'", 'model' => $this->nameForm, 'shortCut' => "f9"),
                        'F5-Visualizza Pdf' => array('id' => $this->nameForm . '_VisualizzaPdf', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f5"),
                        'F7-Visualizza Testo Base' => array('id' => $this->nameForm . '_VisualizzaTestoBase', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f7"),
                        'F4-Allega P7M' => array('id' => $this->nameForm . '_AllegaPdfFirmato', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-upload-32x32'", 'model' => $this->nameForm, 'shortCut' => "f4")
                    );
                } else {
                    $arrayAzioni = array(
                        'F4-Allega P7M' => array('id' => $this->nameForm . '_AllegaPdfFirmato', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-upload-32x32'", 'model' => $this->nameForm, 'shortCut' => "f4"),
                        'F9-Scarica Pdf' => array('id' => $this->nameForm . '_DownloadFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-download-32x32'", 'model' => $this->nameForm, 'shortCut' => "f9"),
                        'F8-Cancella Pdf' => array('id' => $this->nameForm . '_DeleteFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-delete-32x32'", 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Visualizza Pdf' => array('id' => $this->nameForm . '_VisualizzaPdf', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f5"),
                        'F7-Visualizza Testo Base' => array('id' => $this->nameForm . '_VisualizzaTestoBase', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f7"),
                        'F6-Firma Allegato' => array('id' => $this->nameForm . '_FirmaFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-sigillo-32x32'", 'model' => $this->nameForm, 'shortCut' => "f6")
                    );
                }
                break;

            case 'docx':
                if ($pasdoc_rec['PASPRTROWID'] != 0 && ($pasdoc_rec['PASPRTCLASS'] == "PRACOM" || $pasdoc_rec['PASPRTCLASS'] == "PROGES")) {
                    $arrayAzioni = array();
                } else {
                    $docParametri = itaModel::getInstance('docParametri');
                    $valueOO = $docParametri->getParametro('SEG_OPENOO_DOCX');
                    if ($valueOO == 1) {
                        $arrayAzioni = array(
                            'F5-Genera PDF' => array('id' => $this->nameForm . '_ConfermaGenPdf', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-pdf-32x32'", 'model' => $this->nameForm, 'shortCut' => "f5"),
                            'F7-Visualizza Anteprima' => array('id' => $this->nameForm . '_VisAnteprimaOODOCX', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-edit-32x32'", 'model' => $this->nameForm, 'shortCut' => "f7")
                        );
                    } else {
                        $arrayAzioni = array(
                            'F4-Sostituisci DOCX' => array('id' => $this->nameForm . '_UploadDOCX', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-upload-32x32'", 'model' => $this->nameForm, 'shortCut' => "f4"),
                            'F5-Genera PDF' => array('id' => $this->nameForm . '_ConfermaGenPdf', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-pdf-32x32'", 'model' => $this->nameForm, 'shortCut' => "f5"),
                            'F6-Visualizza Dizionario' => array('id' => $this->nameForm . '_VisDizionario', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-dictionary-32x32'", 'model' => $this->nameForm, 'shortCut' => "f6"),
                            'F7-Visualizza Anteprima' => array('id' => $this->nameForm . '_VisAnteprimaDOCX', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-edit-32x32'", 'model' => $this->nameForm, 'shortCut' => "f7")
                        );
                    }
                }
                break;

            case "pdf":
                if ($pasdoc_rec['PASPRTROWID'] != 0 && ($pasdoc_rec['PASPRTCLASS'] == "PRACOM" || $pasdoc_rec['PASPRTCLASS'] == "PROGES")) {
                    $arrayAzioni = array(
                        'F4-Misura Planimetria' => array('id' => $this->nameForm . '_MisuraPlanimetria', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-misura-32x32'", 'model' => $this->nameForm, 'shortCut' => "f4"),
                    );
                } else {
                    $arrayAzioni = array(
                        'F4-Carica Campi Aggiuntivi' => array('id' => $this->nameForm . '_CaricaCampiAgg', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm, 'shortCut' => "f4"),
                        'F9-Firma Allegato' => array('id' => $this->nameForm . '_FirmaFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-sigillo-32x32'", 'model' => $this->nameForm, 'shortCut' => "f9"),
                        'F5-Misura Planimetria' => array('id' => $this->nameForm . '_MisuraPlanimetria', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-misura-32x32'", 'model' => $this->nameForm, 'shortCut' => "f5")
                    );
                }
                break;
            case "tif":
                if ($pasdoc_rec['PASPRTROWID'] != 0 && ($pasdoc_rec['PASPRTCLASS'] == "PRACOM" || $pasdoc_rec['PASPRTCLASS'] == "PROGES")) {
                    $arrayAzioni = array(
                        'F4-Misura Planimetria' => array('id' => $this->nameForm . '_MisuraPlanimetria', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-misura-32x32'", 'model' => $this->nameForm, 'shortCut' => "f4"),
                    );
                } else {
                    $arrayAzioni = array(
                        'F4-Misura Planimetria' => array('id' => $this->nameForm . '_MisuraPlanimetria', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-misura-32x32'", 'model' => $this->nameForm, 'shortCut' => "f4"),
                        'F9-Firma Allegato' => array('id' => $this->nameForm . '_FirmaFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-sigillo-32x32'", 'model' => $this->nameForm, 'shortCut' => "f9"),
                    );
                }
                break;
            case "p7m":
                $arrayAzioni = array(
                    'F7-Visualizza Firme' => array('id' => $this->nameForm . '_VisualizzaFirme', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f7")
                );
                if ($pasdoc_rec['PASPRTROWID'] == 0 && $pasdoc_rec['PASPRTCLASS'] == "") {
                    $arrayAzioni['Controfirma'] = array('id' => $this->nameForm . '_ControfirmaFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-sigillo-32x32'", 'model' => $this->nameForm);
                }
                break;

            case "xls":
                $arrayAzioni = array();
                $arrayAzioni['Valorizza e scarica XLS'] = array('id' => $this->nameForm . '_ElaboraFoglioDiCalcolo', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-download-32x32'", 'model' => $this->nameForm);
                $arrayAzioni['Ricarica XLS definitivo'] = array('id' => $this->nameForm . '_UploadXLSDef', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-upload-32x32'", 'model' => $this->nameForm);
                break;

            case "xls.def":
                $arrayAzioni = array();
                $arrayAzioni['Valorizza e scarica XLS'] = array('id' => $this->nameForm . '_ElaboraFoglioDiCalcolo', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-download-32x32'", 'model' => $this->nameForm);
                $arrayAzioni['Ricarica XLS definitivo'] = array('id' => $this->nameForm . '_UploadXLSDef', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-upload-32x32'", 'model' => $this->nameForm);
                $arrayAzioni['Visualizza XLS definitivo'] = array('id' => $this->nameForm . '_DownloadXLSDef', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-spreadsheet-32x32'", 'model' => $this->nameForm);
                $arrayAzioni['Cancella XLS definitivo'] = array('id' => $this->nameForm . '_DeleteXLSDef', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-delete-32x32'", 'model' => $this->nameForm);
                break;

            default:
                if ($pasdoc_rec['PASPRTROWID'] != 0 && ($pasdoc_rec['PASPRTCLASS'] == "PRACOM" || $pasdoc_rec['PASPRTCLASS'] == "PROGES")) {
                    $arrayAzioni = array();
                } else {
                    $arrayAzioni = array(
                        'F9-Firma Allegato' => array('id' => $this->nameForm . '_FirmaFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-sigillo-32x32'", 'model' => $this->nameForm, 'shortCut' => "f9"),
                    );
                }
                break;
        }
        if ($pasdoc_rec['PASPRTROWID'] == 0 && $pasdoc_rec['PASPRTCLASS'] == "") {
            $proges_rec = $this->praLib->GetProges($this->currGesnum);
            $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
            $pracomA_rec = $this->praLib->GetPracomA($this->keyPasso);
            if ($proges_rec['GESNPR'] != 0 || $pracomP_rec['COMPRT'] || $pracomA_rec['COMPRT']) {
                $arrayAzioni['Blocca Allegato con protocollo'] = array('id' => $this->nameForm . '_BloccaAllegatoDaMenu', "style" => "width:250px", 'metaData' => "iconLeft:'ita-icon-register-document-32x32'", 'model' => $this->nameForm);
            }
            if ($pracomP_rec['COMIDDOC']) {
                if ($this->tipoProtocollo == "Jiride") {
                    $arrayAzioni['Blocca Allegato<br>con Id Documento'] = array('id' => $this->nameForm . '_BloccaAllegatoConIDDaMenu', "style" => "width:250px", 'metaData' => "iconLeft:'ita-icon-register-document-32x32'", 'model' => $this->nameForm);
                }
            }
        } else {
            $arrayAzioni['Sblocca Allegato<br>da n. protocollo o<br>da id documento'] = array('id' => $this->nameForm . '_SbloccaAllegatoProtocollato', "style" => "width:250px", 'metaData' => "iconLeft:'ita-icon-register-document-32x32'", 'model' => $this->nameForm);
            if ($ext == "pdf" || $ext == "xhtml.pdf" || $ext == 'docx.pdf') {
//$pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
//if ($pracomP_rec['COMPRT']) {
                $arrayAzioni['Marca Allegato con Protocollo'] = array('id' => $this->nameForm . '_MarcaAllegato', "style" => "width:250px", 'metaData' => "iconLeft:'ita-icon-register-document-32x32'", 'model' => $this->nameForm);
//}
            }
        }
        if ($ext != 'xls.def' && $ext != 'xhtml') {
            if ($mettiAllaFirma) {
                $arrayAzioni = array_merge($arrayAzioni, $mettiAllaFirma);
            }
            if ($togliDallaFirma) {
                $arrayAzioni = array_merge($arrayAzioni, $togliDallaFirma);
            }
        }

        if ($pasdoc_rec) {
            if ($pasdoc_rec['PASRIS'] == 1) {
                $arrayAzioni['Imposta come Non riservato'] = array('id' => $this->nameForm . '_AllegatoNonRiservato', "style" => "width:250px", 'metaData' => "iconLeft:'ita-icon-document-ris-32x32'", 'model' => $this->nameForm);
            } else {
                $arrayAzioni['Imposta come Riservato'] = array('id' => $this->nameForm . '_AllegatoRiservato', "style" => "width:250px", 'metaData' => "iconLeft:'ita-icon-document-ris-32x32'", 'model' => $this->nameForm);
            }
        }

        return $arrayAzioni;
    }

    public function Dettaglio($rowid, $tipo = 'propak') {
        $this->openGestione($rowid, $tipo);
    }

    public function openGestione($rowid, $tipo = 'propak') {

        // Out::msgInfo("Valore PROPAS.PROPAK", $rowid);

        $this->AzzeraVariabili();
        $this->Nascondi();
        $propas_rec = $this->praLib->GetPropas($rowid, $tipo);




        $this->currGesnum = $propas_rec['PRONUM'];
        $this->keyPasso = $propas_rec['PROPAK'];
        $this->flagAssegnazioniPasso = $this->CheckAssegnazionePasso();
        //Out::msgInfo("",$this->nameForm . '<br>' . $this->keyPasso);
        $this->iniDateEdit = $propas_rec['PRODATEEDIT'];
        $this->noteManager = praNoteManager::getInstance($this->praLib, praNoteManager::NOTE_CLASS_PROPAS, $this->keyPasso);
        $this->CaricaComunicazioni($propas_rec['PROPAK']);
        $pracomP_rec = $this->DecodPracomP($propas_rec['PROPAK']);
        $pracomA_rec = $this->DecodPracomA($propas_rec['PROPAK']);


        /*
         * Se protocollato in arrivo o in partenza, o invata com in partenza o registrato arrivo, spengo il cancella
         */
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_divBottoniAllegaDest');
        if ($pracomP_rec['COMPRT'] || $pracomP_rec['COMIDDOC'] || $pracomP_rec['COMIDMAIL']) {
            Out::hide($this->nameForm . '_Cancella');
            Out::hide($this->nameForm . '_divBottoniAllegaDest');
        } else if ($pracomA_rec['COMPRT'] || $pracomA_rec['COMDAT']) {
            Out::hide($this->nameForm . '_Cancella');
        }
        if ($this->tipoProtocollo == 'Iride' || $this->tipoProtocollo == 'Jiride' || $this->tipoProtocollo == 'Paleo4' || $this->tipoProtocollo == 'Italsoft-ws') {
            Out::show($this->nameForm . '_NuovaComDaProt');
        }

        $this->BloccaSbloccaPartenza($pracomP_rec);
        $this->BloccaSbloccaArrivo($pracomA_rec);


        $this->CaricaAllegati($propas_rec['PROPAK']);
        $this->BloccaDescAllegati();
        $this->CaricaDestinatari($propas_rec['PROPAK']);
        $this->GetHtmlRiepilogoDest();

        /*
         * Se è stata inviata la mail il partenza, mostro il messaggio se ci sono delle ricevute da protocollare
         */
        if ($this->destinatari[0]['IDMAIL']) {
            $pramail_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRAMAIL WHERE COMPAK = '$this->keyPasso' AND FLPROT=0 AND (TIPORICEVUTA = 'accettazione' OR TIPORICEVUTA = 'avvenuta-consegna')", true);
            if ($pramail_tab) {
                $html = "<div style=\"font-size:1.5em;padding-bottom:5px;\"><b>Attenzione! Ci sono " . count($pramail_tab) . " Ricevute, tra Accettazioni e Avvenute-Consegne, da protocollare</b></div>";
                Out::show($this->nameForm . "_divAlertRicevute");
                Out::html($this->nameForm . "_divAlertRicevute", $html);
            }
        }

        $this->CaricaNote();

        if ($propas_rec['PROCOM'] == 1 || $propas_rec['PROPUB'] == 0) {
            if ($propas_rec['PROINT'] == 1) {
                Out::valore($this->nameForm . "_PRACOM[COMINT]", 1);
            }
            $Filent_rec = $this->praLib->GetFilent(2);
            if ($Filent_rec['FILVAL']) {
                Out::show($this->nameForm . '_daFtp');
            } else {
                Out::hide($this->nameForm . '_daFtp');
            }
            Out::show($this->nameForm . '_TestoBase');
            Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCom");
            Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDestinatari");
        } else if ($propas_rec['PROPUB'] == 1) {
            Out::hide($this->nameForm . '_TestoBase');
            Out::hide($this->nameForm . '_TestoAssociato');
            Out::hide($this->nameForm . '_daFtp');
            Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCom");
            Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDestinatari");
        }

        if ($propas_rec['PROPART'] == 1) {
            Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneArticoli");
        } else {
            Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneArticoli");
        }
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDati");
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCaratteristiche");
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDatiAggiuntivi");
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneNote");
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAssegnazioniPassi");


        $this->CaricaAllegatiComunicazioni($propas_rec['PROPAK']);
        $open_Info = 'Oggetto: ' . $propas_rec['PROPAK'];
        $this->openRecord($this->PRAM_DB, 'PROPAS', $open_Info);

        Out::valori($propas_rec, $this->nameForm . '_PROPAS');

        Out::show($this->nameForm . '_CreaNuovoPasso');

        $this->DecodStatoPasso($propas_rec['PROSTATO']);
        $this->DecodResponsabile($propas_rec['PRORPA'], 'codice', $propas_rec);
        $this->DecodeAnadoctipreg($propas_rec['PRODOCTIPREG']);

        if ($propas_rec['PROVPA'] != '') {
            $this->DecodVaialpasso($propas_rec['PROVPA']);
        }
        if ($propas_rec['PROCLT']) {
            $this->DecodTipoPasso($propas_rec['PROCLT']);
        }
        if ($propas_rec['PROVISIBILITA'] == "") {
            Out::valore($this->nameForm . "_PROPAS[PROVISIBILITA]", "Aperto");
        }
//se c'è il numero di protocollo e non ci sono i metadati le icone non devono essere visibili
        $MetadatiPartenza = $this->praLib->GetMetadatiPracom($pracomP_rec['ROWID'], 'rowid');
        $MetadatiArrivo = $this->praLib->GetMetadatiPracom($pracomA_rec['ROWID'], 'rowid');

        $numDoc['protocollo'] = $pracomP_rec['COMPRT'];
        if ($pracomP_rec['COMIDDOC']) {
            $numDoc['documento'] = $pracomP_rec['COMIDDOC'];
        }

        $this->switchIconeProtocolloP($numDoc, $MetadatiPartenza);
        $this->switchIconeProtocolloA($pracomA_rec['COMPRT'], $MetadatiArrivo);
        $this->decodCodAmmAoo($pracomP_rec);
        $this->decodCodAmmAoo($pracomA_rec, "A");

        if ($this->allegatiComunicazione == false) {
            Out::hide($this->divAllegatiCom);
        }

        $visibilitaPasso = $propas_rec['PROVISIBILITA'];
        if (is_array($this->daMail)) {
            if ($propas_rec['PRORPA'] == "") {
                $Ananom_rec = $this->praLib->GetAnanom(proSoggetto::getCodiceUltimoResponsabile($this->currGesnum));
                if ($Ananom_rec) {
                    $visibilitaPasso = "Aperto";
                }
            }
        }


        if ($propas_rec['PRODAT'] == '1') {
            Out::show($this->nameForm . '_ValidaDatiAggiuntivi');
        }

        //Aggiunge i pannelli subForm e li gestisce con il metodo openGestione()
        $formComponenteDati = 'praCompDatiAggiuntivi';
        if ($propas_rec['PRODAT'] == 1)
            $formComponenteDati = 'praCompDatiAggiuntiviForm';
        if ($propas_rec['PROQST'] == 1)
            $formComponenteDati = 'praCompDomandaSemplice';

//        Out::msgInfo("", print_r($propas_rec, true));
//        Out::msgInfo("Componente", $formComponenteDati);

        $this->caricaSubForms($formComponenteDati, $perms);
        //$this->caricaSubForms($propas_rec['PRODAT'] ? 'praCompDatiAggiuntiviForm' : 'praCompDatiAggiuntivi', $perms);
        /* @var $praCompDatiAggiuntivi praCompDatiAggiuntivi */
        $praCompDatiAggiuntivi = itaModel::getInstance($this->praCompDatiAggiuntiviFormname[0], $this->praCompDatiAggiuntiviFormname[1]);
        $praCompDatiAggiuntivi->openGestione($this->currGesnum, $this->keyPasso);

        /*
         * Gestione Tab Passo
         */
        $arrayStatiTab = $this->praLibPasso->setStatiTabPasso($propas_rec);
        $this->caricaTabPassi($arrayStatiTab);

        /*
         * Rileggo le trasmissioni. Se passo già esistente senza ANAPRO, lo inserisco se il parametro è attivo
         */
        if ($this->flagAssegnazioniPasso) {
            $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
            $proSubTrasmissioni->setTipoProt(praLibAssegnazionePassi::TYPE_ASSEGNAZIONI);
            $anapro_rec = $this->proLib->GetAnapro($propas_rec['PASPRO'], "codice", $propas_rec['PASPAR']);
            if (!$anapro_rec) {
                $retInsertAnapro = $this->inserisciAnapro($this->keyPasso);
                if ($retInsertAnapro['Status'] == "-1") {
                    Out::msgStop("Errore in Aggiornamento", $retInsertAnapro['Status'] . " per il passo " . $propas_rec['PROPAK']);
                    return false;
                }
                $anapro_rec = $retInsertAnapro['anapro_rec'];
            }
            $proSubTrasmissioni->setAnapro_rec($anapro_rec);
            $proSubTrasmissioni->Modifica();
            $htmlTabAss = "Assegnazioni <span style=\"color:red;\"><b>(" . count($proSubTrasmissioni->getProArriDest()) . ")</b></span>";
            Out::tabSetTitle($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAssegnazioniPassi", $htmlTabAss);
            if ($this->flagAssegnazioniPasso && $this->daTrasmissioni == false) {
                Out::show($this->nameForm . "_Trasmissioni");
            }

            /*
             * Cerco se il passo è assegnato all'utente loggato e se lo stesso lo ha in gestione
             */
            foreach ($proSubTrasmissioni->getProArriDest() as $dest) {
                $inGestione = false;
                $ananom_rec = $this->praLib->GetAnanom($this->profilo['COD_ANANOM']);
                if ($dest['DESCOD'] == $this->profilo['COD_SOGGETTO'] || $dest['DESCOD'] == $ananom_rec['NOMDEP']) {
                    if ($dest['DESGES'] == 1) {
                        $inGestione = true;
                        break;
                    }
                }
            }
        }


        if ($inGestione == false) {
            if ($visibilitaPasso != "Aperto") {
                $proges_rec = $this->praLib->GetProges($this->currGesnum);
                if (!$this->praPerms->checkSuperUser($proges_rec)) {
                    $perms = $this->praPerms->impostaPermessiPasso($propas_rec);
                    Out::checkDataButton($this->nameForm, $perms);
                    if ($this->praPerms->checkUtenteGenerico($propas_rec)) {
                        Out::attributo($this->nameForm . "_PROPAS[PROVISIBILITA]", "disabled");
                        Out::hide($this->nameForm . "_SbloccaCom");
                        Out::hide($this->nameForm . "_Invia");
                        Out::hide($this->nameForm . "_Risposta");
                        Out::hide($this->gridAllegati . "_delGridRow");
                        Out::hide($this->gridAllegati . "_addGridRow");
                        Out::hide($this->nameForm . "_divBottoniAllega");
                        Out::hide($this->nameForm . "_ProtocollaPartenza");
                        Out::hide($this->nameForm . "_PROTRIC_DESTINATARIO_butt");
                        Out::hide($this->nameForm . "_RimuoviProtocollaP");
                        Out::hide($this->nameForm . "_ProtocollaArrivo");
                        Out::hide($this->nameForm . "_RimuoviProtocollaA");
                        Out::hide($this->nameForm . "_PROTRIC_MITTENTE_butt");
                        Out::hide($this->nameForm . '_CreaNuovoPasso');
                        Out::hide($this->nameForm . '_NuovaComDaProt');
                    }
                }
            }
        }

        if ($_POST['daCommercio'] == true) {
            Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCaratteristiche");
        }
        if ($_POST['daComunicazione'] == true) {
            Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCom");
        }
        if ($this->returnModel == "praArticoli") {
            Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneArticoli");
        } else if ($this->returnModel == "praComunicazioni") {
            Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCom");
        }

        if ($propas_rec['PROUTEADD'] == "@ADMIN@" && $propas_rec['PROUTEEDIT'] == "@ADMIN@") {
            Out::show($this->nameForm . "_utenteEdit");
            Out::valore($this->nameForm . "_utenteEdit", "Passo proveniente dalla richiesta on-line in data " . $propas_rec['PRODATEEDIT'] . " NON MODIFICABILE");
        } else {
            if ($propas_rec['PROUTEEDIT'] != "") {
                Out::show($this->nameForm . "_utenteEdit");
                Out::valore($this->nameForm . "_utenteEdit", "Ultima modifica al passo effettuata dall'utente " . $propas_rec['PROUTEEDIT'] . " in data " . $propas_rec['PRODATEEDIT']);
            }
        }
        if ($propas_rec['PROKPRE']) {
            $this->DecodVaialpasso($propas_rec['PROKPRE'], "propak", "ANTECEDENTE");
        }
        if ($propas_rec['PROCOMDEST']) {
            Out::show($this->nameForm . "_NuoviPassiDaDest");
        }

        /*
         * Decodifico il calendario
         */
        $idCalendar = $this->praLib->DecodCalendar($propas_rec['ROWID'], "PASSI_SUAP");
        Out::valore($this->nameForm . "_Calendario", "");
        if ($idCalendar) {
            Out::valore($this->nameForm . "_Calendario", $idCalendar);
        }


        $this->valorizzaInfoDestinatari($propas_rec);

        Out::setFocus('', $this->nameForm . '_PROPAS[PROANN]');

        if ($this->praReadOnly == true) {
            $this->HideButton();
        }

        if (!$this->ValorizzaProall()) {
            Out::msgStop("Aggiornamento", "Errore aggiornamento campo PROALL");
            return false;
        }
    }

    function HideButton() {
        Out::hide($this->nameForm . '_buttonBar');
//
        Out::hide($this->nameForm . '_divBottoniAllega');
        Out::hide($this->gridAllegati . '_delGridRow');
        Out::hide($this->gridAllegati . '_addGridRow');
//
        Out::hide($this->gridAltriDestinatari . '_delGridRow');
        Out::hide($this->gridAltriDestinatari . '_addGridRow');
        Out::hide($this->nameForm . '_ProtocollaPartenza');
        Out::hide($this->nameForm . '_RimuoviProtocollaP');
        Out::hide($this->nameForm . '_Invia');
        Out::hide($this->nameForm . '_ProtocollaArrivo');
        Out::hide($this->nameForm . '_RimuoviProtocollaA');
        Out::hide($this->nameForm . '_SbloccaComArrivo');
//
        Out::hide($this->gridNote . '_delGridRow');
        Out::hide($this->gridNote . '_addGridRow');
    }

    function BloccaDescAllegati() {
        return;
//
// La funzione e stata spospesa da richiesta senigallia 07/10/2014 MM
//
        foreach ($this->passAlle as $keyAlle => $alle) {
            $pasdoc_rec = $this->praLib->GetPasdoc($alle['ROWID'], "ROWID");
            if ($pasdoc_rec['PASPRTROWID'] != 0 && $pasdoc_rec['PASPRTCLASS'] == "PRACOM") {
                $pracom_rec = $this->praLib->GetPracom($pasdoc_rec['PASPRTROWID'], "rowid");
                if ($pracom_rec['COMPRT']) {
                    TableView::setCellValue($this->gridAllegati, $keyAlle, 'INFO', "", 'not-editable-cell', '', 'false');
                }
            }
            if ($pasdoc_rec['PASPRTROWID'] != 0 && $pasdoc_rec['PASPRTCLASS'] == "PROGES") {
                $proges_rec = $this->praLib->GetProges($pasdoc_rec['PASPRTROWID'], "rowid");
                if ($proges_rec['GESNPR']) {
                    TableView::setCellValue($this->gridAllegati, $keyAlle, 'INFO', "", 'not-editable-cell', '', 'false');
                }
            }
            if ($alle["PASLOCK"] == 1) {
                TableView::setCellValue($this->gridAllegati, $keyAlle, 'INFO', "", 'not-editable-cell', '', 'false');
            }
        }
    }

    function SendMailProtocollo($meta, $pracom_rec) {
        $Proges_rec = $this->praLib->GetProges($this->currGesnum);
        $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
//
// Istanza mail box per l'invio da Fascicoli elettronici
//
        include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
        /* @var $emlMailBox emlMailBox */
        $emlMailBox = $this->praLib->getEmlMailBox($Proges_rec['GESTSP']);
        if (!$emlMailBox) {
            Out::msgStop('Inoltro Mail', "Impossibile accedere alle funzioni dell'account: " . $this->refAccounts[0]['EMAIL']);
            return false;
        }

        $outgoingMessage = $emlMailBox->newEmlOutgoingMessage();
        if (!$outgoingMessage) {
            Out::msgStop('Inoltro Mail', "Impossibile creare un nuovo messaggio in uscita.");
            return false;
        }
        $outgoingMessage->setSubject($_POST['valori']['Oggetto']);
        $outgoingMessage->setBody($_POST['valori']['Corpo']);
        $outgoingMessage->setEmail($_POST['valori']['Email']);
        $outgoingMessage->setAttachments($_POST['allegati']);
//
// Invio il messaggio
//
        $mailArchivio_rec = $emlMailBox->sendMessage($outgoingMessage);
        if ($mailArchivio_rec) {
            $idmail = $mailArchivio_rec['IDMAIL'];
            Out::msgInfo('Inoltro al Protocollo', "Inoltro Comunicazione per protocollazione eseguito correttamente");
            if (!@is_dir(itaLib::getPrivateUploadPath())) {
                if (!itaLib::createPrivateUploadPath()) {
                    Out::msgStop("Archiviazione Mail.", "Creazione ambiente di lavoro temporaneo fallita.");
                    $this->returnToParent();
                }
            }

//
//Costruisco array metadati
//
            $meta['DatiProtocollazione']['TipoProtocollo']['value'] = "Manuale";

            $meta['DatiProtocollazione']['IdMailRichiesta']['value'] = $idmail;
            $meta['DatiProtocollazione']['IdMailRichiesta']['status'] = "1";
            $meta['DatiProtocollazione']['IdMailRichiesta']['msg'] = "Richiesta Mail Protocollazione";

            $meta['DatiProtocollazione']['proNum']['value'] = $this->currGesnum;
            $meta['DatiProtocollazione']['proNum']['status'] = "1";
            $meta['DatiProtocollazione']['proNum']['msg'] = "Numero pratica";

            $meta['DatiProtocollazione']['chiavePasso']['value'] = $this->keyPasso;
            $meta['DatiProtocollazione']['chiavePasso']['status'] = "1";
            $meta['DatiProtocollazione']['chiavePasso']['msg'] = "chiave passo";

            $meta['DatiProtocollazione']['Numero']['value'] = $_POST['valori']['Numero'];
            $meta['DatiProtocollazione']['Numero']['status'] = "1";
            $meta['DatiProtocollazione']['Numero']['msg'] = "Numero Protocollo";

            $meta['DatiProtocollazione']['Oggetto']['value'] = $_POST['valori']['Corpo'];
            $meta['DatiProtocollazione']['Oggetto']['status'] = "1";
            $meta['DatiProtocollazione']['Oggetto']['msg'] = "Numero Protocollo";

            $meta['DatiProtocollazione']['Data']['value'] = date("Ymd");
            $meta['DatiProtocollazione']['Data']['status'] = "1";
            $meta['DatiProtocollazione']['Data']['msg'] = "Data invio mail al Protocollo";

            $meta['DatiProtocollazione']['Anno']['value'] = $_POST['valori']['Anno'];
            $meta['DatiProtocollazione']['Anno']['status'] = "1";
            $meta['DatiProtocollazione']['Anno']['msg'] = "Anno Protocollo";

//
//Aggiorno PROGES
//
            $pracom_rec['COMMETA'] = serialize($meta);
            $update_Info = "Oggetto aggiorno comunicazione passo seq " . $propas_rec['PROSEQ'] . " pratica $this->currGesnum dopo invio protocollo";
            if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                Out::msgStop("Errore in Aggionamento", "Aggiornamento comunicazione dopo invio Protocollo fallito.");
                return false;
            }
            $this->Dettaglio($propas_rec['PROPAK']);
        } else {
            Out::msgStop('Errore Mail', $emlMailBox->getLastMessage());
            return false;
        }
    }

    private function inserisciNotifica($oggetto, $uteins, $tipoNotifica) {
        include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
        $envLib = new envLib();
        $oggetto_notifica = "$tipoNotifica UNA NOTA AL PASSO SEQ. " . $this->formData[$this->nameForm . '_PROPAS']['PROSEQ'] . ", PRATICA NUMERO $this->currGesnum";
        $testo_notifica = $oggetto;
        $dati_extra = array();
        $dati_extra['ACTIONMODEL'] = $this->nameForm;
        $dati_extra['ACTIONPARAM'] = serialize(array('setOpenMode' => array('edit'), 'setOpenRowid' => array($this->formData[$this->nameForm . '_PROPAS']['ROWID'])));
        $envLib->inserisciNotifica($this->nameform, $oggetto_notifica, $testo_notifica, $uteins, $dati_extra);
        return;
    }

    private function switchIconeProtocolloP($Prot, $Metadati) {
        if (is_array($Prot)) {
            $numeroProt = $Prot['protocollo'];
            $idDocumento = $Prot['documento'];
        } else {
            $numeroProt = $Prot;
        }
        $numeroProt = (String) $numeroProt;
//
        $tipoProt = $this->tipoProtocollo;
        if ($tipoProt != 'Manuale') {
            if ($numeroProt == '' || $numeroProt == '0') { //protocollo vuoto -> tutti i campi editabili
                Out::show($this->nameForm . '_ProtocollaPartenza');
                Out::hide($this->nameForm . '_RimuoviProtocollaP');
                Out::hide($this->nameForm . '_RimuoviDocumentoP');
                Out::hide($this->nameForm . '_PRACOM[COMIDDOC]_field');
                Out::hide($this->nameForm . '_PRACOM[COMDATADOC]_field');
                Out::show($this->nameForm . '_PROTRIC_DESTINATARIO_field');
                Out::show($this->nameForm . '_ANNOPROT_DESTINATARIO_field');
                Out::show($this->nameForm . '_DATAPROT_DESTINATARIO_field');
                if ($idDocumento) {
                    Out::hide($this->nameForm . '_ProtocollaPartenza');
                    Out::hide($this->nameForm . '_PROTRIC_DESTINATARIO_field');
                    Out::hide($this->nameForm . '_ANNOPROT_DESTINATARIO_field');
                    Out::hide($this->nameForm . '_DATAPROT_DESTINATARIO_field');
                }
            } elseif ($numeroProt != '' && $numeroProt != '0' && $Metadati) { //c'è il protocollo e ci sono sono i metadati -> sparisce l'icona, i campi non sono editabili
                Out::attributo($this->nameForm . '_PROTRIC_DESTINATARIO', "readonly", '0');
                Out::attributo($this->nameForm . '_ANNOPROT_DESTINATARIO', "readonly", '0');
                Out::attributo($this->nameForm . '_DATAPROT_DESTINATARIO', "readonly", '0');
                Out::hide($this->nameForm . '_ProtocollaPartenza');
                Out::show($this->nameForm . '_RimuoviProtocollaP');

                Out::show($this->nameForm . '_PROTRIC_DESTINATARIO_field');
                Out::show($this->nameForm . '_ANNOPROT_DESTINATARIO_field');
                Out::show($this->nameForm . '_DATAPROT_DESTINATARIO_field');

                Out::hide($this->nameForm . '_PROTRIC_DESTINATARIO_butt');

                Out::show($this->nameForm . "_GestioneProtocolloPartenza");
            } else { //c'è il protocollo, ma non ci sono i metadati -> sparisce icona +, campi non editabili, compare un cestino per cancellarli
                Out::attributo($this->nameForm . '_PROTRIC_DESTINATARIO', "readonly", '0');
                Out::attributo($this->nameForm . '_ANNOPROT_DESTINATARIO', "readonly", '0');
                Out::attributo($this->nameForm . '_DATAPROT_DESTINATARIO', "readonly", '0');
                Out::show($this->nameForm . '_RimuoviProtocollaP');
                Out::hide($this->nameForm . '_ProtocollaPartenza'); //ridondante, l'hide viene fatto nella funzione Nascondi()
            }
            if ($idDocumento) {
                Out::attributo($this->nameForm . '_PRACOM[COMIDDOC]', "readonly", '0');
                Out::attributo($this->nameForm . '_PRACOM[COMDATADOC]', "readonly", '0');
                Out::attributo($this->nameForm . '_DATAPROT_DESTINATARIO', "readonly", '0');
                Out::show($this->nameForm . '_PRACOM[COMIDDOC]_field');
                Out::show($this->nameForm . '_PRACOM[COMDATADOC]_field');
                Out::show($this->nameForm . '_RimuoviDocumentoP');
                Out::show($this->nameForm . '_VediIdDocPartenza');
                if ($tipoProt == 'Iride' || $tipoProt == 'Jiride' || $tipoProt == 'Italsoft-remoto-allegati' || $tipoProt == 'Paleo4' || $tipoProt == 'Italsoft-ws') {
                    Out::show($this->nameForm . "_BloccaAllegatiDoc");
                }
            } else {
                Out::hide($this->nameForm . '_PRACOM[COMIDDOC]_field');
                Out::hide($this->nameForm . '_PRACOM[COMDATADOC]_field');
                Out::hide($this->nameForm . '_RimuoviDocumentoP');
                Out::hide($this->nameForm . '_VediIdDocPartenza');
            }
//            if (($tipoProt == 'Iride' || $tipoProt == 'Jiride' || $tipoProt == 'Italsoft-remoto-allegati' || $tipoProt == 'Paleo4' || $tipoProt == 'Italsoft-ws') && $numeroProt != '') {
//                Out::show($this->nameForm . "_BloccaAllegati");
//                Out::html($this->nameForm . "_BloccaAllegati_lbl", "Aggiungi Allegati $nameButtonAddAlle");
//            }
            if (($tipoProt == 'Jiride' || $tipoProt == 'Paleo4') && $Metadati['DatiProtocollazione']['idMail']) {
                Out::show($this->nameForm . "_VerificaMailWs");
            }
        } else {
            Out::hide($this->nameForm . '_PRACOM[COMIDDOC]_field');
            Out::hide($this->nameForm . '_PRACOM[COMDATADOC]_field');
            Out::hide($this->nameForm . '_RimuoviDocumentoP');
            Out::hide($this->nameForm . '_VediIdDocPartenza');
            if ($numeroProt == '' || $numeroProt == '0') { //protocollo vuoto -> tutti i campi editabili
                Out::hide($this->nameForm . '_ProtocollaPartenza');
                Out::hide($this->nameForm . '_RimuoviProtocollaP');
                Out::show($this->nameForm . '_InviaProtocolloPar');
            } else {
                Out::attributo($this->nameForm . '_PROTRIC_DESTINATARIO', "readonly", '0');
                Out::attributo($this->nameForm . '_ANNOPROT_DESTINATARIO', "readonly", '0');
                Out::attributo($this->nameForm . '_DATAPROT_DESTINATARIO', "readonly", '0');
                Out::show($this->nameForm . '_RimuoviProtocollaP');
                Out::hide($this->nameForm . '_ProtocollaPartenza'); //ridondante, l'hide viene fatto nella funzione Nascondi()
                if (isset($Metadati['DatiProtocollazione']['IdMailRichiesta'])) {
                    Out::show($this->nameForm . '_InviaProtocolloPar');
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
            $this->profilo = proSoggetto::getProfileFromIdUtente();

            /*
             * Utente disabilitato da profilo (solo arrivo o nega)
             */
            if ($this->profilo['PROT_ABILITATI'] == '1' || $this->profilo['PROT_ABILITATI'] == '3') {
                Out::hide($this->nameForm . '_ProtocollaPartenza');
                Out::hide($this->nameForm . '_RimuoviProtocollaP');
                Out::hide($this->nameForm . '_InviaProtocolloPar');
                Out::hide($this->nameForm . '_BloccaAllegatiProt');
                Out::hide($this->nameForm . '_PROTRIC_DESTINATARIO_butt');
                Out::attributo($this->nameForm . '_PROTRIC_DESTINATARIO', "readonly", '0');
                Out::attributo($this->nameForm . '_ANNOPROT_DESTINATARIO', "readonly", '0');
                Out::attributo($this->nameForm . '_DATAPROT_DESTINATARIO', "readonly", '0');
            }
        }
    }

    function BloccaSbloccaArrivo($pracomA_rec) {
        if ($pracomA_rec['COMIDMAIL'] || $pracomA_rec['COMDAT']) {
            Out::hide($this->nameForm . '_Risposta');
            Out::hide($this->nameForm . '_SbloccaCom');
            Out::show($this->nameForm . '_SbloccaComArrivo');
            if ($pracomA_rec['COMNOM'] == "") {
                $readonly = "1";
            }
            $readonly = "0";
        } else {
            $readonly = "1";
        }
        Out::attributo($this->nameForm . '_MITTENTE', "readonly", "1");
        Out::attributo($this->nameForm . '_DESC_MITTENTE', "readonly", "1");
        Out::attributo($this->nameForm . '_CODFISC_MITTENTE', "readonly", "1");
        Out::attributo($this->nameForm . '_EMAIL_MITTENTE', "readonly", "1");
        Out::attributo($this->nameForm . '_NOTE_MITTENTE', "readonly", "1");
//        Out::attributo($this->nameForm . '_MITTENTE', "readonly", $readonly);
//        Out::attributo($this->nameForm . '_DESC_MITTENTE', "readonly", $readonly);
//        Out::attributo($this->nameForm . '_CODFISC_MITTENTE', "readonly", $readonly);
//        Out::attributo($this->nameForm . '_EMAIL_MITTENTE', "readonly", $readonly);
        Out::attributo($this->nameForm . '_dataArrivo', "readonly", $readonly);
        Out::attributo($this->nameForm . '_oraArrivo', "readonly", $readonly);
        Out::attributo($this->nameForm . '_TIPO_ARRIVO', "readonly", $readonly);
//        Out::attributo($this->nameForm . '_NOTE_MITTENTE', "readonly", $readonly);
    }

    function BloccaSbloccaPartenza($pracomP_rec) {
//        if ($pracomP_rec['COMIDMAIL'] || $pracomP_rec['COMPRT']) {
        if ($pracomP_rec['COMPRT'] || $pracomP_rec['COMIDDOC']) {
//            if ($pracomP_rec['COMIDMAIL']) {
//                Out::hide($this->nameForm . '_Invia');
//                Out::show($this->nameForm . '_SbloccaCom');
//            } else {
//                Out::show($this->nameForm . '_Invia');
//                Out::hide($this->nameForm . '_SbloccaCom');
//            }
            if ($this->tipoProtocollo == "Infor" || $this->tipoProtocollo == "HyperSIC" || $this->tipoProtocollo == "Italsoft-remoto" || $this->tipoProtocollo == "Manuale" || $this->tipoProtocollo == "Paleo" || $this->tipoProtocollo == "WSPU") {
                Out::hide($this->gridAllegati . "_addGridRow");
                Out::hide($this->gridAllegati . "_delGridRow");
                Out::hide($this->nameForm . '_divBottoniAllega');
            } else {
                Out::show($this->gridAllegati . "_addGridRow");
                Out::show($this->gridAllegati . "_delGridRow");
                Out::show($this->nameForm . '_divBottoniAllega');
            }
//            if ($pracomP_rec['COMPRT'] == "") {
//                Out::show($this->nameForm . '_SbloccaCom');
//            }
            $readonly = "0";
        } else {
            $readonly = "1";
//            Out::show($this->nameForm . '_Invia');
            Out::show($this->gridAllegati . "_addGridRow");
            Out::show($this->gridAllegati . "_delGridRow");
            Out::show($this->nameForm . '_divBottoniAllega');
        }
        Out::show($this->nameForm . '_Invia');
        Out::attributo($this->nameForm . '_dataPartenza', "readonly", $readonly);
        Out::attributo($this->nameForm . '_PRACOM[COMORA]', "readonly", $readonly);
        Out::attributo($this->nameForm . '_TIPO_PARTENZA', "readonly", $readonly);
        Out::attributo($this->nameForm . '_DESTINATARIO', "readonly", $readonly);
        Out::attributo($this->nameForm . '_DESC_DESTINATARIO', "readonly", $readonly);
        Out::attributo($this->nameForm . '_CODFISC_DESTINATARIO', "readonly", $readonly);
        Out::attributo($this->nameForm . '_PRACOM[COMIND]', "readonly", $readonly);
        Out::attributo($this->nameForm . '_PRACOM[COMCIT]', "readonly", $readonly);
        Out::attributo($this->nameForm . '_PRACOM[COMPRO]', "readonly", $readonly);
        Out::attributo($this->nameForm . '_PRACOM[COMPRO]', "readonly", $readonly);
        Out::attributo($this->nameForm . '_EMAIL_DESTINATARIO', "readonly", $readonly);
    }

    private function switchIconeProtocolloA($numeroProt, $Metadati) {
        $numeroProt = (String) $numeroProt;
//        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
        $tipoProt = $this->tipoProtocollo;
        if ($tipoProt != 'Manuale') {
            if ($numeroProt == '' || $numeroProt == '0') { //protocollo vuoto -> tutti i campi editabili
                Out::show($this->nameForm . '_ProtocollaArrivo');
                Out::hide($this->nameForm . '_RimuoviProtocollaA');
            } elseif ($numeroProt != '' && $numeroProt != '0' && $Metadati) { //c'è il protocollo e ci sono sono i metadati -> sparisce l'icona, i campi non sono editabili
                Out::attributo($this->nameForm . '_PROTRIC_MITTENTE', "readonly", '0');
                Out::attributo($this->nameForm . '_ANNORIC_MITTENTE', "readonly", '0');
                Out::attributo($this->nameForm . '_DATAPROT_MITTENTE', "readonly", '0');
                Out::hide($this->nameForm . '_ProtocollaArrivo');
//Out::hide($this->nameForm . '_RimuoviProtocollaA');
                Out::show($this->nameForm . '_RimuoviProtocollaA');
                Out::hide($this->nameForm . '_PROTRIC_MITTENTE_butt');
//Out::show($this->nameForm . "_VediProtArrivo");
                Out::show($this->nameForm . "_GestioneProtocolloArrivo");
            } else { //c'è il protocollo, ma non ci sono i metadati -> sparisce icona +, campi non editabili, compare un cestino per cancellarli
                Out::attributo($this->nameForm . '_PROTRIC_MITTENTE', "readonly", '0');
                Out::attributo($this->nameForm . '_ANNORIC_MITTENTE', "readonly", '0');
                Out::attributo($this->nameForm . '_DATAPROT_MITTENTE', "readonly", '0');
                Out::show($this->nameForm . '_RimuoviProtocollaA');
                Out::hide($this->nameForm . '_ProtocollaArrivo'); //ridondante, l'hide viene fatto nella funzione Nascondi()
            }
//            if (($tipoProt == 'Iride' || $tipoProt == 'Jiride' || $tipoProt == 'Paleo4' || $tipoProt == 'Italsoft-ws') && $numeroProt != '') {
//                Out::show($this->nameForm . "_BloccaAllegatiArr");
//            }
        } else {
            if ($numeroProt == '' || $numeroProt == '0') { //protocollo vuoto -> tutti i campi editabili
                Out::hide($this->nameForm . '_ProtocollaArrivo'); //per l'inserimento manuale deve essere sempre spenta l'icona
                Out::hide($this->nameForm . '_RimuoviProtocollaA');
                Out::show($this->nameForm . '_InviaProtocolloArr');
            } else {
                Out::attributo($this->nameForm . '_PROTRIC_MITTENTE', "readonly", '0');
                Out::attributo($this->nameForm . '_ANNORIC_MITTENTE', "readonly", '0');
                Out::attributo($this->nameForm . '_DATAPROT_MITTENTE', "readonly", '0');
                Out::show($this->nameForm . '_RimuoviProtocollaA');
                Out::hide($this->nameForm . '_ProtocollaArrivo'); //ridondante, l'hide viene fatto nella funzione Nascondi()
                if (isset($Metadati['DatiProtocollazione']['IdMailRichiesta'])) {
                    Out::show($this->nameForm . '_InviaProtocolloArr');
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
            $this->profilo = proSoggetto::getProfileFromIdUtente();

            /*
             * Utente disabilitato da profilo (solo partenza o nega)
             */
            if ($this->profilo['PROT_ABILITATI'] == '2' || $this->profilo['PROT_ABILITATI'] == '3') {
                Out::attributo($this->nameForm . '_PROTRIC_MITTENTE', "readonly", '0');
                Out::attributo($this->nameForm . '_ANNORIC_MITTENTE', "readonly", '0');
                Out::attributo($this->nameForm . '_DATAPROT_MITTENTE', "readonly", '0');
                Out::hide($this->nameForm . '_ProtocollaArrivo');
                Out::hide($this->nameForm . '_RimuoviProtocollaA');
                Out::hide($this->nameForm . '_PROTRIC_MITTENTE_butt');
                Out::hide($this->nameForm . '_InviaProtocolloArr');
                Out::hide($this->nameForm . '_BloccaAllegatiArr');
            }
        }
    }

    function CheckResponsabile($codiceResp) {
        if ($codiceResp) {
            $nascondi = false;
            $Utenti_rec = $this->accLib->GetUtenti(App::$utente->getKey('idUtente'));
            if ($Utenti_rec) {
                if ($Utenti_rec['UTEANA__3'] != $codiceResp) {
                    $nascondi = true;
                }
            }
        }
        return $nascondi;
    }

    function CreaCombo() {
        Out::select($this->nameForm . '_PROPAS[PROVISIBILITA]', 1, "Protetto", "1", "Protetto");
        Out::select($this->nameForm . '_PROPAS[PROVISIBILITA]', 1, "Privato", "0", "Privato");
        Out::select($this->nameForm . '_PROPAS[PROVISIBILITA]', 1, "Aperto", "0", "Pubblico");
        Out::select($this->nameForm . '_PROPAS[PROVISIBILITA]', 1, "soloPasso", "0", "Protetto solo Passo");
//
        Out::select($this->nameForm . '_PROPAS[PROPST]', 1, 0, "1", "");
        Out::select($this->nameForm . '_PROPAS[PROPST]', 1, 1, "0", "Descrizione breve");
        Out::select($this->nameForm . '_PROPAS[PROPST]', 1, 2, "0", "Descrizione estesa");
    }

    function CaricaDaPec() {
        if ($_POST['datiMail']) {
            $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK = '" . $this->keyPasso . "' AND COMTIP = 'P'", false);
            $destFile = itaLib::getPrivateUploadPath();
            if (!@is_dir($destFile)) {
                if (!itaLib::createPrivateUploadPath()) {
                    Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                    $this->returnToParent();
                }
            }
            if ($_POST['datiMail']['ELENCOALLEGATI']) {
                foreach ($_POST['datiMail']['ELENCOALLEGATI'] as $allegato) {
                    $randName = md5(rand() * time()) . "." . pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION);
                    @copy($allegato['DATAFILE'], $destFile . '/' . $randName);
                    $this->allegatiComunicazione[] = array(
                        'ROWID' => 0,
                        'FILEPATH' => $destFile . '/' . $randName,
                        'FILENAME' => $randName,
                        'FILEINFO' => $allegato['FILENAME'],
                        'FILEORIG' => $allegato['FILENAME'],
                        'PROVENIENZA' => 'COMUNICAZIONE ' . $pracomP_rec['ROWID']
                    );
                }
            }
//salvo la mail stessa
            if ($_POST['datiMail']['FILENAME']) {
                if (pathinfo($_POST['datiMail']['FILENAME'], PATHINFO_EXTENSION) == "") {
                    $ext = "eml";
                }
                $randNameMail = md5(rand() * time()) . "." . pathinfo($_POST['datiMail']['FILENAME'], PATHINFO_EXTENSION) . "$ext";
                @copy($_POST['datiMail']['FILENAME'], $destFile . '/' . $randNameMail);
                $this->allegatiComunicazione[] = Array(
                    'ROWID' => 0,
                    'FILEPATH' => $destFile . '/' . $randNameMail,
                    'FILENAME' => $randNameMail,
                    'FILEINFO' => 'Mail da: ' . $_POST['datiMail']['MITTENTE'],
                    'FILEORIG' => 'mail_com_arrivo_' . $_POST['datiMail']['MITTENTE'] . ".eml",
                    'PROVENIENZA' => 'COMUNICAZIONE ' . $pracomP_rec['ROWID']
                );
            }
            Out::show($this->divAllegatiCom);
            Out::valore($this->nameForm . "_EMAIL_MITTENTE", $_POST['datiMail']['MITTENTE']);
            Out::valore($this->nameForm . "_dataArrivo", $_POST['datiMail']['DATA']);
            Out::valore($this->nameForm . "_oraArrivo", $_POST['datiMail']['ORA']);
            $this->CaricaGriglia($this->gridAllCom, $this->allegatiComunicazione);
        }
    }

    function ApriScanner() {
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                $this->returnToParent();
            }
        }
//$modelTwain = 'utiTwain';
        $modelTwain = 'utiTwain';
        itaLib::openForm($modelTwain, true);
        $appRoute = App::getPath('appRoute.' . substr($modelTwain, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $modelTwain . '.php';
        $_POST = array();
        $_POST['event'] = 'openform';
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
        $allegato[] = array(
            'ROWID' => 0,
            'FILEPATH' => $destFile,
            'FILENAME' => $randName,
            'FILEINFO' => "File Originale: Da scanner",
            'FILEORIG' => "Scansione_" . $timeStamp . "." . pathinfo($randName, PATHINFO_EXTENSION),
            'CLASSE' => ""
        );

        $this->caricaAllegatiEsterni($allegato);

//$this->CaricaGriglia($this->gridAllegati, $this->passAlle);
    }

    public function apriInserimento($procedimento) {
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::clearGrid($this->gridAllegati);
        TableView::disableEvents($this->gridAllegati);
        TableView::clearGrid($this->gridAllCom);
        TableView::disableEvents($this->gridAllCom);

        $this->Nascondi();
        Out::hide($this->divAllegatiCom);
        Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDati");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCaratteristiche");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDatiAggiuntivi");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCom");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDestinatari");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneArticoli");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneNote");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAssegnazioniPassi");
//
        if ($this->daMail['protocolla']['ANTECEDENTE']) {
            $propas_rec_Antecedente = $this->praLib->GetPropas($this->daMail['protocolla']['ANTECEDENTE'], "propak");
            $Ananom_rec_ant = $this->praLib->GetAnanom($propas_rec_Antecedente['PRORPA']);
            Out::valore($this->nameForm . "_PROPAS[PROKPRE]", $this->daMail['protocolla']['ANTECEDENTE']);
        }


        if ($this->tipoPasso == 2) {
            Out::valore($this->nameForm . "_PROPAS[PROKPRE]", $_POST[$this->nameForm . '_PROPAS']['PROPAK']);
        }

        /*
         * Pulisco griglia assegnazioni
         */
        $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
        $proSubTrasmissioni->setTipoProt(praLibAssegnazionePassi::TYPE_ASSEGNAZIONI);
        $proSubTrasmissioni->Nuovo();
    }

    public function GetCorpoMail($Propas_rec = '') {
        $praLibVar = new praLibVariabili();
        $Filent_rec = $this->praLib->GetFilent(6);
        $templateCorpo = $Filent_rec['FILVAL'];
        if ($Propas_rec) {
            $metadata = unserialize($Propas_rec['PROMETA']);
            if ($metadata['TESTOBASEMAIL']['BODY_COMUNICAZIONE']) {
                $templateCorpo = $metadata['TESTOBASEMAIL']['BODY_COMUNICAZIONE'];
            }
        }


        $praLibVar->setCodicePratica($this->currGesnum);
        $praLibVar->setChiavePasso($this->keyPasso);
        $dictionaryValues = $praLibVar->getVariabiliPratica()->getAllData();
        return $this->praLib->GetStringDecode($dictionaryValues, $Filent_rec['FILVAL']);
    }

    function CaricaAllegatiComunicazioni($keyPasso) {
        if ($keyPasso) {
            $pramPath = $this->praLib->SetDirectoryPratiche(substr($keyPasso, 0, 4), $keyPasso, "PASSO", false);
            $pasdoc_tab = $this->praLib->GetPasdoc($keyPasso, "codice", true);
            $this->allegatiComunicazione = array();
            if ($pasdoc_tab) {
                foreach ($pasdoc_tab as $pasdoc_rec) {
                    if (strpos($pasdoc_rec['PASCLA'], 'COMUNICAZIONE') !== false || strpos($pasdoc_rec['PASCLA'], 'INTEGRAZIONE') !== false || strpos($pasdoc_rec['PASCLA'], 'PARERE') !== false) {
                        $this->allegatiComunicazione[] = Array(
                            'ROWID' => $pasdoc_rec['ROWID'],
                            'FILEPATH' => $pramPath . "/" . $pasdoc_rec['PASFIL'],
                            'FILENAME' => $pasdoc_rec['PASFIL'],
                            'FILEINFO' => $pasdoc_rec['PASNOT'],
                            'FILEORIG' => $pasdoc_rec['PASNAME'],
                            'PROVENIENZA' => $pasdoc_rec['PASCLA']
                        );
                    }
                }
            }
            $this->CaricaGriglia($this->gridAllCom, $this->allegatiComunicazione);
        }
    }

    function CercaDocumentiDaPratica($pratica, $tutti = false, $composer = false) {
        $pratPath = $this->praLib->SetDirectoryPratiche(substr($pratica, 0, 4), $pratica, 'PROGES', false);
        $allegati = array();
        $this->allegatiAppoggio = array();

//
//Prendo gli allegati generali della pratica
//
        $pasdoc_tab_generali = $this->praLib->GetPasdoc($pratica, "codice", true);
        if ($pasdoc_tab_generali) {
            foreach ($pasdoc_tab_generali as $pasdoc_rec_generali) {
                switch ($pasdoc_rec_generali['PASSTA']) {
                    case "V":
                        $stato = "Valido";
                        break;
                    case "N":
                        $stato = "Non Valido";
                        break;
                    case "":
                        $stato = "Da controllare";
                        break;
                }

                if ($pasdoc_rec_generali['PASNAME']) {
                    $fileOrig = $pasdoc_rec_generali['PASNAME'];
                } else {
                    $fileOrig = $pasdoc_rec_generali['PASFIL'];
                }
                $nameFile = $fileOrig;
                if ($pasdoc_rec_generali['PASEVI'] == 1) {
                    $nameFile = "<p style = 'color:red;font-weight:bold;font-size:1.2em;'>$nameFile</p>";
                }
                $allegati[] = Array(
                    'TIPO' => 'Pratica N. ' . $pratica,
                    'FILEPATH' => $pratPath . "/" . $pasdoc_rec_generali['PASFIL'],
                    'FILENAME' => $pasdoc_rec_generali['PASFIL'],
                    'FILEINFO' => $pasdoc_rec_generali['PASNOT'],
                    'RIDORIG' => $pasdoc_rec_generali['ROWID'],
                    'FILEORIG' => $fileOrig,
                    'FILEVISUA' => $nameFile,
                    'STATO' => $stato,
                    'PROVENIENZA' => "INTERNO",
                    'PASRIS' => $pasdoc_rec_generali['PASRIS'],
                    'RISERVATO' => $this->praLibRiservato->getIconRiservato($pasdoc_rec_generali['PASRIS'])
                );
            }
        }

//
//Prendo gli allegati dei passi della pratica
//
        $sql = "SELECT
                    *,
                    PROPAS.PRODPA
                FROM
                    PASDOC
                LEFT OUTER JOIN PROPAS ON PASDOC.PASKEY = PROPAS.PROPAK 
                WHERE 
                    PROPAS.PRONUM = '$pratica'
                ";
        $pasdoc_tab = $this->praLib->getGenericTab($sql);
        if ($pasdoc_tab) {
            foreach ($pasdoc_tab as $pasdoc_rec) {
                $pramPath = $this->praLib->SetDirectoryPratiche(substr($pasdoc_rec['PASKEY'], 0, 4), $pasdoc_rec['PASKEY'], "PASSO", false);
                $extAlle = $stato = "";
                $ext = pathinfo($pasdoc_rec['PASFIL'], PATHINFO_EXTENSION);
                if ($ext != 'info') {
                    if ($pasdoc_rec['PASNAME']) {
                        $fileOrig = $pasdoc_rec['PASNAME'];
                    } else {
                        $fileOrig = $pasdoc_rec['PASFIL'];
                    }
                    switch ($pasdoc_rec['PASSTA']) {
                        case "V":
                            $stato = "Valido";
                            break;
                        case "N":
                            $stato = "Non Valido";
                            break;
                        case "":
                            $stato = "Da controllare";
                            break;
                    }


                    if (strtolower($ext) == 'xhtml' || strtolower($ext) == 'docx') {
                        $basename = pathinfo($pasdoc_rec['PASFIL'], PATHINFO_FILENAME);
                        if (file_exists($pramPath . "/" . $basename . ".pdf")) {
                            $extAlle = "pdf";
                        }
                        if (file_exists($pramPath . "/" . $basename . ".p7m")) {
                            $extAlle = "p7m";
                        }
                        if (file_exists($pramPath . "/" . $basename . ".pdf.p7m")) {
                            $extAlle = "pdf.p7m";
                        }
                        if ($extAlle) {
                            $nameFile = pathinfo($fileOrig, PATHINFO_FILENAME) . ".$extAlle";
                            if ($pasdoc_rec['PASEVI'] == 1) {
                                $nameFile = "<p style = 'color:red;font-weight:bold;font-size:1.2em;'>" . pathinfo($fileOrig, PATHINFO_FILENAME) . ".$extAlle" . "</p>";
                            }
                            $allegati[] = Array(
                                'TIPO' => $pasdoc_rec['PRODPA'],
                                'FILEPATH' => $pramPath . "/" . $basename . ".$extAlle",
                                'FILENAME' => $basename . ".$extAlle",
                                'FILEINFO' => $pasdoc_rec['PASNOT'],
                                'RIDORIG' => 0,
                                'FILEORIG' => pathinfo($fileOrig, PATHINFO_FILENAME) . ".$extAlle",
                                'FILEVISUA' => $nameFile,
                                'STATO' => $stato,
                                'PROVENIENZA' => "INTERNO",
                                'PASRIS' => $pasdoc_rec['PASRIS'],
                                'RISERVATO' => $this->praLibRiservato->getIconRiservato($pasdoc_rec['PASRIS'])
                            );
                        }
                    } else {
                        $nameFile = $fileOrig;
                        if ($pasdoc_rec['PASEVI'] == 1) {
                            $nameFile = "<p style = 'color:red;font-weight:bold;font-size:1.2em;'>$nameFile</p>";
                        }
                        $allegati[] = Array(
                            'TIPO' => $pasdoc_rec['PRODPA'],
                            'FILEPATH' => $pramPath . "/" . $pasdoc_rec['PASFIL'],
                            'FILENAME' => $pasdoc_rec['PASFIL'],
                            'FILEINFO' => $pasdoc_rec['PASNOT'],
                            'RIDORIG' => $pasdoc_rec['ROWID'],
                            'FILEORIG' => $fileOrig,
                            'FILEVISUA' => $nameFile,
                            'STATO' => $stato,
                            'PROVENIENZA' => "INTERNO",
                            'PASRIS' => $pasdoc_rec['PASRIS'],
                            'RISERVATO' => $this->praLibRiservato->getIconRiservato($pasdoc_rec['PASRIS'])
                        );
                    }
                }
            }
        }


        if ($allegati) {
            $this->allegatiAppoggio = $allegati;
            $praticaFormattata = substr($pratica, 4) . "/" . substr($pratica, 0, 4);
            praRic::praElencoAllegatiProc($allegati, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "", "Selezionare gli allegati desiderati per la pratica n. <b>$praticaFormattata</b>");
        } else {
            Out::msgInfo("Ricerca Allegati", "Allegati non trovati");
        }
    }

    function CercaDocumentiInterni($where, $tutti = false, $composer = false) {
        $pratPath = $this->praLib->SetDirectoryPratiche(substr($this->currGesnum, 0, 4), $this->currGesnum, 'PROGES', false);
        $allegati = array();
        $this->allegatiAppoggio = array();

// Mi trovo gli allegati generali solo se Tutti
        if ($tutti == true) {
            $pasdoc_tab_generali = $this->praLib->GetPasdoc($this->currGesnum, "codice", true);

            if ($pasdoc_tab_generali) {
                foreach ($pasdoc_tab_generali as $pasdoc_rec_generali) {
                    $color = $this->praLib->getColorNameAllegato($pasdoc_rec_generali['PASEVI']);
                    if (strtolower(pathinfo($pasdoc_rec_generali['PASFIL'], PATHINFO_EXTENSION)) != 'pdf' && $composer) {
                        continue;
                    }
                    if ($pasdoc_rec_generali['PASNAME']) {
                        $fileOrig = $pasdoc_rec_generali['PASNAME'];
                    } else {
                        $fileOrig = $pasdoc_rec_generali['PASFIL'];
                    }
                    $nameFile = $fileOrig;
                    if ($color) {
                        $nameFile = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>$nameFile</p>";
                    }
                    switch ($pasdoc_rec_generali['PASSTA']) {
                        case "V":
                            $stato = "Valido";
                            break;
                        case "N":
                            $stato = "Non Valido";
                            break;
                        case "S":
                            $stato = "Sostituito";
                            break;
                        case "":
                            $stato = "Da controllare";
                            break;
                    }

                    $allegati[] = Array(
                        'TIPO' => 'Pratica N. ' . $this->currGesnum,
                        'FILEPATH' => $pratPath . "/" . $pasdoc_rec_generali['PASFIL'],
                        'FILENAME' => $pasdoc_rec_generali['PASFIL'],
                        'FILEINFO' => $pasdoc_rec_generali['PASNOT'],
                        'RIDORIG' => $pasdoc_rec_generali['ROWID'],
                        'DESTINAZ' => $pasdoc_rec_generali['PASDEST'],
                        'CLASS' => $pasdoc_rec_generali['PASCLAS'],
                        'NOTEALLE' => $pasdoc_rec_generali['PASNOTE'],
                        'FILEORIG' => $fileOrig,
                        'FILEVISUA' => $nameFile,
                        'STATO' => $stato,
                        'PROVENIENZA' => "INTERNO",
                        'PASRIS' => $pasdoc_rec_generali['PASRIS'],
                        'RISERVATO' => $this->praLibRiservato->getIconRiservato($pasdoc_rec_generali['PASRIS'])
                    );
                }
            }
        }

        if (!$composer) {
            $whereAttuale = "AND PROPAK <> '$this->keyPasso'";
        }

//$propas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM = $this->currGesnum  $whereAttuale AND $where ORDER BY PROSEQ", true);
//
        //Inserita join con proges 22-01-2016 perchè il $where contiene i campi GESPA e GESTSP per le visibilità
//
        $propas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT
                                                             PROPAS.*
                                                          FROM 
                                                            PROPAS
                                                          LEFT OUTER JOIN PROGES ON PROGES.GESNUM = PROPAS.PRONUM    
                                                          WHERE
                                                            PRONUM = $this->currGesnum  $whereAttuale AND $where ORDER BY PROSEQ ", true);

        if ($propas_tab) {
            foreach ($propas_tab as $propas_rec) {
                $pramPath = $this->praLib->SetDirectoryPratiche(substr($propas_rec['PROPAK'], 0, 4), $propas_rec['PROPAK'], "PASSO", false);
                $pasdoc_tab = $this->praLib->GetPasdoc($propas_rec['PROPAK'], "codice", true);
                if ($pasdoc_tab) {
                    foreach ($pasdoc_tab as $pasdoc_rec) {
                        $color = $this->praLib->getColorNameAllegato($pasdoc_rec['PASEVI']);
                        $extAlle = $stato = "";
                        $ext = pathinfo($pasdoc_rec['PASFIL'], PATHINFO_EXTENSION);
                        if ($ext != 'info') {
                            if ($pasdoc_rec['PASNAME']) {
                                $fileOrig = $pasdoc_rec['PASNAME'];
                            } else {
                                $fileOrig = $pasdoc_rec['PASFIL'];
                            }

                            switch ($pasdoc_rec['PASSTA']) {
                                case "V":
                                    $stato = "Valido";
                                    break;
                                case "N":
                                    $stato = "Non Valido";
                                    break;
                                case "S":
                                    $stato = "Sostituito";
                                    break;
                                case "":
                                    $stato = "Da controllare";
                                    break;
                            }
                            if (strtolower($ext) != 'pdf' && strtolower($ext) != 'xhtml' && strtolower($ext) != 'docx' && $composer) {
                                continue;
                            }

                            if (strtolower($ext) == 'xhtml' || strtolower($ext) == 'docx') {
                                $basename = pathinfo($pasdoc_rec['PASFIL'], PATHINFO_FILENAME);
                                if (file_exists($pramPath . "/" . $basename . ".pdf")) {
                                    $extAlle = "pdf";
                                }
                                if (file_exists($pramPath . "/" . $basename . ".p7m")) {
                                    $extAlle = "p7m";
                                }
                                if (file_exists($pramPath . "/" . $basename . ".pdf.p7m")) {
                                    $extAlle = "pdf.p7m";
                                }
                                if (strtolower($extAlle) != 'pdf' && $composer) {
                                    continue;
                                }
                                if ($extAlle) {
                                    $nameFile = pathinfo($fileOrig, PATHINFO_FILENAME) . ".$extAlle";
//if ($pasdoc_rec['PASEVI'] == 1) {
                                    if ($color) {
                                        $nameFile = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . pathinfo($fileOrig, PATHINFO_FILENAME) . ".$extAlle" . "</p>";
                                    }
//
                                    $allegati[] = Array(
//'TIPO' => 'Passo seq. ' . $propas_rec['PROSEQ'],
                                        'TIPO' => "Passo seq. " . $propas_rec['PROSEQ'] . " - " . $propas_rec['PRODPA'],
                                        'FILEPATH' => $pramPath . "/" . $basename . ".$extAlle",
                                        'FILENAME' => $basename . ".$extAlle",
                                        'FILEINFO' => $pasdoc_rec['PASNOT'],
                                        'RIDORIG' => $pasdoc_rec['ROWID'], //0,
                                        'FILEORIG' => pathinfo($fileOrig, PATHINFO_FILENAME) . ".$extAlle",
                                        'DESTINAZ' => $pasdoc_rec['PASDEST'],
                                        'CLASS' => $pasdoc_rec['PASCLAS'],
                                        'NOTEALLE' => $pasdoc_rec['PASNOTE'],
                                        'FILEVISUA' => $nameFile,
                                        'STATO' => $stato,
                                        'PROVENIENZA' => "INTERNO",
                                        'PASRIS' => $pasdoc_rec['PASRIS'],
                                        'RISERVATO' => $this->praLibRiservato->getIconRiservato($pasdoc_rec['PASRIS'])
                                    );
                                }
                            } else {
                                $nameFile = $fileOrig;
//if ($pasdoc_rec['PASEVI'] == 1) {
                                if ($color) {
                                    $nameFile = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>$nameFile</p>";
                                }
//
                                $allegati[] = Array(
//'TIPO' => 'Passo seq. ' . $propas_rec['PROSEQ'],
                                    'TIPO' => "Passo seq. " . $propas_rec['PROSEQ'] . " - " . $propas_rec['PRODPA'],
                                    'FILEPATH' => $pramPath . "/" . $pasdoc_rec['PASFIL'],
                                    'FILENAME' => $pasdoc_rec['PASFIL'],
                                    'FILEINFO' => $pasdoc_rec['PASNOT'],
                                    'RIDORIG' => $pasdoc_rec['ROWID'],
                                    'FILEORIG' => $fileOrig,
                                    'DESTINAZ' => $pasdoc_rec['PASDEST'],
                                    'CLASS' => $pasdoc_rec['PASCLAS'],
                                    'NOTEALLE' => $pasdoc_rec['PASNOTE'],
                                    'FILEVISUA' => $nameFile,
                                    'STATO' => $stato,
                                    'PROVENIENZA' => "INTERNO",
                                    'PASRIS' => $pasdoc_rec['PASRIS'],
                                    'RISERVATO' => $this->praLibRiservato->getIconRiservato($pasdoc_rec['PASRIS'])
                                );
                            }
                        }
                    }
                }
            }
        }
        if ($allegati) {
            $this->allegatiAppoggio = $allegati;
            if ($composer) {
                praRic::praComponiPDF($allegati, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig));
            } else {
                praRic::praElencoAllegatiProc($allegati, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig));
            }
        } else {
            Out::msgInfo("Ricerca Allegati", "Allegati non trovati");
        }
    }

    function ApriVediFamiglia($cf) {
        if ($cf == "") {
            Out::msgInfo("Attenzione", "Per visualizzare la famiglia scegliere un codice fiscale");
            return false;
        }
        $_POST = array();
        $model = 'utiVediAnel';
        $_POST['event'] = 'openform';
        $_POST['cf'] = $cf;
        itaLib::openDialog($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    function RegistraAllegati($keyPasso) {
        itaLib::getPrivateUploadPath();
        $arrayClean = $this->praLib->cleanArrayTree($this->passAlle);
        foreach ($arrayClean as $allegato) {
            if ($allegato['ROWID'] == 0) {
                $destinazione = $this->praLib->SetDirectoryPratiche(substr($keyPasso, 0, 4), $keyPasso);
                if (!$destinazione) {
                    Out::msgStop("Archiviazione File", "Errore nella cartella di destinazione.");
                    return false;
                }

                $pasdoc_rec = $metaDati = array();
                $testoPath = pathinfo($allegato['FILEPATH'], PATHINFO_DIRNAME);
                $testoName = pathinfo($allegato['FILEPATH'], PATHINFO_FILENAME);
                if (strtolower(pathinfo($allegato['FILEPATH'], PATHINFO_EXTENSION)) == "pdf") {
                    if (file_exists($testoPath . "/" . $testoName . ".tif")) {
                        if (!@copy($allegato['FILEPATH'], $destinazione . "/" . $testoName . ".tif")) {
                            Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $testoName . ".tif" . " !");
                            return false;
                        }
                    }
                }
                if (strpos($allegato['PROVENIENZA'], 'INTERNO') !== false) {
                    $pasdoc_rec['PASLNK'] = $allegato['FILENAME'];
                    if (!@copy($allegato['FILEPATH'], $destinazione . "/" . $allegato['FILENAME'])) {
                        Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $allegato['FILENAME'] . " !");
                        return false;
                    }
                } elseif (strpos($allegato['PROVENIENZA'], 'TESTOBASE') !== false) {
//                    $testoPath = pathinfo($allegato['FILEPATH'], PATHINFO_DIRNAME);
//                    $testoName = pathinfo($allegato['FILEPATH'], PATHINFO_FILENAME);

                    $pasdoc_rec['PASLNK'] = "allegato://" . $allegato['FILENAME'];
                    if (!@copy($allegato['FILEPATH'], $destinazione . "/" . $allegato['FILENAME'])) {
                        Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $allegato['FILENAME'] . " !");
                        return false;
                    }
                    if (file_exists($testoPath . "/" . $testoName . ".pdf")) {
                        if (!@copy($testoPath . "/" . $testoName . ".pdf", $destinazione . "/" . $testoName . ".pdf")) {
                            Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $testoName . ".pdf");
                            return false;
                        }
                    }
                    if (file_exists($testoPath . "/" . $testoName . ".pdf.p7m")) {
                        if (!@copy($testoPath . "/" . $testoName . ".pdf.p7m", $destinazione . "/" . $testoName . ".pdf.p7m")) {
                            Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $testoName . ".pdf.p7m");
                            return false;
                        }
                    }
                } elseif (strpos($allegato['PROVENIENZA'], 'ESTERNO') !== false) {
                    $pasdoc_rec['PASLNK'] = "allegato://" . $allegato['FILENAME'];

                    if (!@rename($allegato['FILEPATH'], $destinazione . "/" . $allegato['FILENAME'])) {
                        Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $allegato['FILENAME'] . " !");
                        return false;
                    }

                    if (file_exists($testoPath . "/" . $testoName . ".pdf")) {
                        if (!@rename($testoPath . "/" . $testoName . ".pdf", $destinazione . "/" . $testoName . ".pdf")) {
                            Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $testoName . ".pdf");
                            return false;
                        }
                    }

                    if (file_exists($testoPath . "/" . $testoName . ".pdf.p7m")) {
                        if (!@rename($testoPath . "/" . $testoName . ".pdf.p7m", $destinazione . "/" . $testoName . ".pdf.p7m")) {
                            Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $testoName . ".pdf.p7m");
                            return false;
                        }
                    }
                } else {
                    $pasdoc_rec['PASLNK'] = "allegato://" . $allegato['FILENAME'];
                    if (!@rename($allegato['FILEPATH'], $destinazione . "/" . $allegato['FILENAME'])) {
                        Out::msgStop("Archiviazione File", "Errore in rinomina del file " . $allegato['FILENAME'] . " !");
                        return false;
                    }
                }

                $pasdoc_rec['PASKEY'] = $keyPasso;
                $pasdoc_rec['PASFIL'] = $allegato['FILENAME'];
                $pasdoc_rec['PASNAME'] = $allegato['FILEORIG'];
                $pasdoc_rec['PASUTC'] = "";
                $pasdoc_rec['PASUTE'] = "";
                $pasdoc_rec['PASNOT'] = $allegato['FILEINFO'];
                $pasdoc_rec['PASCLA'] = $allegato['PROVENIENZA'];
                $pasdoc_rec['PASTESTOBASE'] = $allegato['TESTOBASE'];
                $pasdoc_rec['PASLOG'] = $allegato['STATO'];
                $pasdoc_rec['PASEVI'] = $allegato['PASEVI'];
                $pasdoc_rec['PASLOCK'] = $allegato['PASLOCK'];
                $pasdoc_rec['PASDWONLINE'] = $allegato['PUBQR'] != '' ? $allegato['PASDWONLINE'] : 0;
                $pasdoc_rec['PASCLAS'] = $allegato['PASCLAS'];
                $pasdoc_rec['PASDEST'] = $allegato['PASDEST'];
                $pasdoc_rec['PASNOTE'] = $allegato['PASNOTE'];
                $pasdoc_rec['PASPRTCLASS'] = $allegato['PASPRTCLASS'];
                $pasdoc_rec['PASPRTROWID'] = $allegato['PASPRTROWID'];
                $pasdoc_rec['PASUTELOG'] = $allegato['PASUTELOG'];
                $pasdoc_rec['PASORADOC'] = $allegato['PASORADOC'];
                $pasdoc_rec['PASDATADOC'] = $allegato['PASDATADOC'];
                $pasdoc_rec['PASDAFIRM'] = $allegato['PASDAFIRM'];
                $pasdoc_rec['PASMETA'] = $allegato['PASMETA'];
                $pasdoc_rec['PASSHA2'] = $allegato['PASSHA2'];
                $pasdoc_rec['PASPUB'] = $allegato['PASPUB'];
                $pasdoc_rec['PASSTA'] = $allegato['PASSTA'];
                $pasdoc_rec['PASFLCDS'] = $allegato['PASFLCDS'];
                $pasdoc_rec['PASRIS'] = $allegato['PASRIS'];
                $insert_Info = 'Oggetto: Inserimento allegato ' . $allegato['FILENAME'];
                if (!$this->insertRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $insert_Info)) {
                    return false;
                }
            } else {
                $pasdoc_rec = $this->praLib->GetPasdoc($allegato['ROWID'], 'ROWID');
                $pasdoc_rec['PASNOT'] = $allegato['FILEINFO'];
                $pasdoc_rec['PASEVI'] = $allegato['PASEVI'];
                $pasdoc_rec['PASLOCK'] = $allegato['PASLOCK'];
                $pasdoc_rec['PASDWONLINE'] = $allegato['PUBQR'] != '' ? $allegato['PASDWONLINE'] : 0;
                $pasdoc_rec['PASCLAS'] = $allegato['PASCLAS'];
                $pasdoc_rec['PASDEST'] = $allegato['PASDEST'];
                $pasdoc_rec['PASNOTE'] = $allegato['PASNOTE'];
                $pasdoc_rec['PASFIL'] = $allegato['FILENAME'];
                $pasdoc_rec['PASLNK'] = "allegato://" . $allegato['FILENAME'];
                $pasdoc_rec['PASLOG'] = $allegato['STATO'];
                $pasdoc_rec['PASNAME'] = $allegato['FILEORIG'];
                $pasdoc_rec['PASMETA'] = $allegato['PASMETA'];
                $pasdoc_rec['PASPUB'] = $allegato['PASPUB'];
                $pasdoc_rec['PASFLCDS'] = $allegato['PASFLCDS'];
                $update_Info = 'Oggetto: Aggiornamento allegato ' . $allegato['FILENAME'];
                if (!$this->updateRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $update_Info)) {
                    return false;
                }
            }
        }
        return true;
    }

    function CheckAllegatiBloccati($tipo) {
        if ($this->passAlle) {
            foreach ($this->passAlle as $alle) {
                $bloccato = false;
                if ($alle['ROWID'] != 0) {
                    $pasdoc_rec = $this->praLib->GetPasdoc($alle['ROWID'], "ROWID");
                    if ($pasdoc_rec['PASPRTCLASS'] == "PRACOM") {
                        $pracom_rec = $this->praLib->GetPracom($pasdoc_rec['PASPRTROWID'], "rowid");
                        if ($pracom_rec['COMPRT']) {
                            if ($pracom_rec['COMTIP'] == $tipo) {
                                $bloccato = true;
                                break;
                            }
                        }
                    }
                }
            }
            return $bloccato;
        }
    }

    function SbloccaRicevuteBloccate() {
        $pramail_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRAMAIL WHERE COMPAK = '$this->keyPasso' AND FLPROT = 1 AND (TIPORICEVUTA = 'accettazione' OR TIPORICEVUTA = 'avvenuta-consegna')", true);
        foreach ($pramail_tab as $pramail_rec) {
            $pramail_rec['FLPROT'] = 0;
            $update_Info = "Oggetto: Sblocco protocollazione ricevuta rowid: " . $pramail_rec['ROWID'] . " - passo $this->keyPasso";
            if (!$this->updateRecord($this->PRAM_DB, 'PRAMAIL', $pramail_rec, $update_Info)) {
                Out::msgStop("ATTENZIONE!", "Sblocco Protocollazione Ricevute rowid: " . $pramail_rec['ROWID'] . " - passo $this->keyPasso");
                return false;
            }
        }
        return true;
    }

    function SbloccaAllegatiBloccati($tipoCom) {
        if ($this->passAlle) {
            if ($tipoCom == "P") {
                $pracom_rec = $this->praLib->GetPracomP($this->keyPasso);
            } elseif ($tipoCom == "A") {
                $pracom_rec = $this->praLib->GetPracomA($this->keyPasso);
            }
            foreach ($this->passAlle as $alle) {
                if ($alle['ROWID'] != 0) {
                    $pasdoc_rec = $this->praLib->GetPasdoc($alle['ROWID'], "ROWID");
                    if ($pasdoc_rec['PASPRTCLASS'] == "PRACOM" && $pasdoc_rec['PASPRTROWID'] == $pracom_rec['ROWID']) {
                        $pasdoc_rec['PASPRTCLASS'] = "";
                        $pasdoc_rec['PASPRTROWID'] = 0;
                        $update_Info = "Oggetto: Sblocco protocollazione allegato " . $pasdoc_rec['PASNAME'] . " - passo " . $pasdoc_rec['PASKEY'];
                        if (!$this->updateRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $update_Info)) {
                            Out::msgStop("ATTENZIONE!", "Sblocco Protocollazione Allegato " . $pasdoc_rec['PASNAME'] . " - passo " . $pasdoc_rec['PASKEY'] . " Fallito.");
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    function RegistraAllegatiCom($keyPasso) {
        foreach ($this->allegatiComunicazione as $allegato) {
            if ($allegato['ROWID'] == 0) {
                $destinazione = $this->praLib->SetDirectoryPratiche(substr($keyPasso, 0, 4), $keyPasso);
                if (!$destinazione) {
                    Out::msgStop("Archiviazione File", "Errore nella cartella di destinazione.");
                    return false;
                }
                $pracom_recA = $this->praLib->GetPracomA($this->keyPasso);
                if (!@copy($allegato['FILEPATH'], $destinazione . "/" . $allegato['FILENAME'])) {
                    Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $allegato['FILENAME'] . " !");
                    return false;
                }
                $pasdoc_rec = array();
                $pasdoc_rec['PASKEY'] = $keyPasso;
                $pasdoc_rec['PASFIL'] = $allegato['FILENAME'];
                $pasdoc_rec['PASLNK'] = "allegato://" . $allegato['FILENAME'];
                $pasdoc_rec['PASUTC'] = "";
                $pasdoc_rec['PASUTE'] = "";
                $pasdoc_rec['PASNOT'] = $allegato['FILEINFO'];
//$pasdoc_rec['PASCLA'] = $allegato['PROVENIENZA'];
                $pasdoc_rec['PASCLA'] = "COMUNICAZIONE " . $pracom_recA['ROWID'];
                $pasdoc_rec['PASNAME'] = $allegato['FILEORIG'];
                $pasdoc_rec['PASDATADOC'] = date("Ymd");
                $pasdoc_rec['PASORADOC'] = date("H:i:s");
                $pasdoc_rec['PASSHA2'] = hash_file('sha256', $destinazione . "/" . $pasdoc_rec['PASFIL']);
                $insert_Info = "Oggetto: inserimento allegato comunicazione  " . $pasdoc_rec['PASLNK'] . " del passo " . $pasdoc_rec['PASKEY'];
                if (!$this->insertRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $insert_Info)) {
                    Out::msgStop("ATTENZIONE!", "1 - Errore di Inserimento Allegato Comunicazione.");
                    return false;
                }
            } else {
                $pasdoc_rec = $this->praLib->GetPasdoc($allegato['ROWID'], 'ROWID');
                $pasdoc_rec['PASNOT'] = $allegato['FILEINFO'];
                $update_Info = "Oggetto: Aggiornamento allegati comunicazione: " . $pasdoc_rec['PASKEY'] . " - " . $pasdoc_rec['PASLNK'];
                if (!$this->updateRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $update_Info)) {
                    Out::msgStop("ATTENZIONE!", "Errore di Aggiornamento Allegato Comunicazione.");
                    return false;
                }
            }
        }

        return true;
    }

    function AllegaFile() {
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
            if (strtolower(pathinfo($uplFile, PATHINFO_EXTENSION)) == "p7m") {
                $realFile = pathinfo($uplFile, PATHINFO_FILENAME);
                $realExt = pathinfo($realFile, PATHINFO_EXTENSION);
                $randName = md5(rand() * time()) . ".$realExt.p7m";
            }
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
                    $convertPDFA = substr($Filde2, 1, 1);
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
                            $this->aggiungiAllegato($randName, $uplFile, $origFile);
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

    function aggiungiAllegato($randName, $destFile, $origFile, $fileInfo = '', $pasdoc_rec = array()) {
        if (!$fileInfo) {
            $fileInfo = "File Originale: " . $origFile;
        }
        $allegato[] = array(
            'rowid' => 1,
            'FILEPATH' => $destFile,
            'FILENAME' => $randName,
            'FILEINFO' => $fileInfo,
            'FILEORIG' => $origFile
        );
        $this->caricaAllegatiEsterni($allegato, $pasdoc_rec);
        Out::setFocus('', $this->nameForm . '_wrapper');
    }

    private function CaricaNote() {
        $datiGrigliaNote = array();
        foreach ($this->noteManager->getNote() as $key => $nota) {
            $data = date("d/m/Y", strtotime($nota['DATAINS']));
            $datiGrigliaNote[$key]['NOTE'] = '<div style="margin: 3px 3px 3px 3px;"><div style="font-size: 9px;font-weight:bold; color: grey;">' . $data . " - " . $nota['ORAINS'] . " da " . $nota['UTELOG'] .
                    "</div><div class='ita-Wordwrap'><b>" . $nota['OGGETTO'] . '</b>';
            if ($nota['TESTO']) {
                $testo = $nota['TESTO'];
//                if (strlen($testo) > 45) {
//                    $testo = substr($testo, 0, 45);
//                }
                $datiGrigliaNote[$key]['NOTE'] .= '<div title="' . $nota['TESTO'] . '" style="font-size: 11px;">' . $testo . '</div>';
            }
            $datiGrigliaNote[$key]['NOTE'] .= '</div></div>';
        }
        $this->caricaGriglia($this->gridNote, $datiGrigliaNote);
    }

    function CaricaDestinatari($keyPasso, $sidx = 'SEQUENZA', $sord = 'ASC') {
        $this->destinatari = $this->praLib->GetPraDestinatari($keyPasso, "codice", true, $sidx, $sord);
        foreach ($this->destinatari as $key => $dest) {
            $this->destinatari[$key]['FIRMATOCDS'] = $this->checkFirmatoCds($dest);
            if ($dest['IDMAIL']) {
                $icon = $this->praLib->GetIconAccettazioneConsegna($dest['IDMAIL'], $keyPasso);
                $this->destinatari[$key]['ACCETTAZIONE'] = $icon['accDest'];
                $this->destinatari[$key]['CONSEGNA'] = $icon['conDest'];
                $this->destinatari[$key]['SBLOCCA'] = $icon['sboccaDest'];
                $this->destinatari[$key]['VEDI'] = $icon['vediMail'];
            }
        }
        $this->CaricaGriglia($this->gridAltriDestinatari, $this->destinatari, "1", "100");
    }

    function GetHtmlRiepilogoDest() {
        if ($this->destinatari) {
            $html = "<div style=\"font-size:1.5em;text-decoration:underline;padding-bottom:5px;\"><b>RIEPILOGO DESTINATARI:</b></div>";
            foreach ($this->destinatari as $destinatario) {
                $html .= "<span style=\"font-size:1.2em;color:blue;\">" . $destinatario['NOME'] . ": " . $destinatario['MAIL'] . "</span><br>";
            }
            Out::html($this->nameForm . "_divRiepilogoDest", $html);
        }
    }

    function CaricaAllegati($keyPasso, $duplica = false) {
        $propas_rec = $this->praLib->GetPropas($keyPasso, "propak");
        $sql = "SELECT DISTINCT PASCLA FROM PASDOC WHERE PASKEY = '" . $keyPasso . "' ORDER BY PASCLA";
        $tipiProv_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        $arrayData = array();
        $inc = 0;
        $index_0 = 0;
        foreach ($tipiProv_tab as $tipiProv_rec) {
            $sql = "SELECT * FROM PASDOC WHERE PASKEY = '" . $keyPasso . "' AND PASCLA = '" . $tipiProv_rec['PASCLA'] . "' AND PASROWIDBASE=0 ORDER BY ROWID"; //PASFIL";
            $dataDetail_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
            if ($dataDetail_tab) {
                $arrayDataTmp = array();
                $arrayDataTmp['PROV'] = "L0_" . $index_0;
                $arrayDataTmp['RANDOM'] = $tipiProv_rec['PASCLA'];
                $arrayDataTmp['NAME'] = $tipiProv_rec['PASCLA'];
                $arrayDataTmp['level'] = 0;
                $arrayDataTmp['parent'] = null;
                $arrayDataTmp['isLeaf'] = 'false';
                $arrayDataTmp['expanded'] = 'true';
                $arrayDataTmp['loaded'] = 'true';
                $arrayData[] = $arrayDataTmp;
                $inc += 1;
                foreach ($dataDetail_tab as $dataDetail_rec) {
                    $arrayDataTmp = $this->getArrayDataTreeAllegato($dataDetail_rec, $duplica, $inc, $propas_rec['PROFLCDS'], $propas_rec['PRODWONLINE']);
                    $arrayDataTmp['level'] = 1;
                    $arrayDataTmp['parent'] = "L0_" . $index_0;
                    $arrayDataTmp['isLeaf'] = 'true';
                    $arrayDataTmp['expanded'] = 'true';
                    $arrayDataTmp['loaded'] = 'true';

                    $arrayData[] = $arrayDataTmp;
                    $saveInc = $inc;

                    $inc += 1;

                    /*
                     * Verifico presenza allegati figli e se ci sono li metto in tabella
                     */
                    if ($dataDetail_rec['PASFLCDS'] == 1) {
                        $sql = "SELECT * FROM PASDOC WHERE PASKEY = '" . $keyPasso . "' AND PASROWIDBASE=" . $dataDetail_rec['ROWID'] . " ORDER BY ROWID";
                        $figli_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                        foreach ($figli_tab as $allegato_rec) {
                            $arrayDataTmp = $this->getArrayDataTreeAllegato($allegato_rec, $duplica, $inc, $propas_rec['PROFLCDS'], $propas_rec['PRODWONLINE']);
                            $arrayDataTmp['level'] = 2;
                            $arrayDataTmp['parent'] = $saveInc;
                            $arrayDataTmp['isLeaf'] = 'true';
                            $arrayData[$saveInc]['isLeaf'] = 'false';
                            $arrayData[] = $arrayDataTmp;
                            $inc += 1;
                        }
                    }
                }
                $index_0 += 1;
            }
        }

        $this->passAlle = $arrayData;
        $this->ContaSizeAllegati($this->passAlle);
        $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');
        $this->bloccoAllegatiRiservati(false);
        $this->BloccaDescAllegati();
    }

    function CaricaComunicazioni($keyPasso) {
        $sql = "SELECT * FROM PRACOM WHERE COMPAK = '" . $keyPasso . "'";
        $Pracom_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        $this->passCom = $Pracom_tab;
    }

    function SbloccaDocumento($pracom_rec) {
        if (!$this->praLib->CtrPassword($_POST[$this->nameForm . '_password'])) {
            return;
        }
        if ($pracom_rec['COMTIP'] == "P") {
            $tipo = "PARTENZA";
            Out::attributo($this->nameForm . '_PRACOM[COMIDDOC]', "readonly", '1');
            Out::attributo($this->nameForm . '_PRACOM[COMDATADOC]', "readonly", '1');
        }
        if ($pracom_rec) {
            $propas_rec = $this->praLib->GetPropas($this->keyPasso);
            $this->insertAudit($this->PRAM_DB, "PRACOM", "Inizio Procedura Pulizia Documento in $tipo Passo seq " . $propas_rec['PROSEQ']);
            $update_Info = "Oggetto : Rimuovo Documento  " . $pracom_rec['COMIDDOC'] . " in " . $pracom_rec['COMTIP'] . " del passo sequenza " . $propas_rec['PROSEQ'] . " della pratica n. " . $propas_rec['PRONUM'];
            $pracom_rec['COMIDDOC'] = "";
            $pracom_rec['COMDATADOC'] = "";
            $pracom_rec['COMMETA'] = "";
            if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                Out::msgStop("Errore", "Cancellazione Documento in $tipo Fallita");
                return false;
            }
            if (!$this->SbloccaAllegatiBloccati($pracom_rec['COMTIP'])) {
                Out::msgStop("Errore", "Sblocco Documentazione Allegati in $tipo Fallita");
                return false;
            }
            $this->Dettaglio($this->keyPasso);
        }
    }

    function SbloccaProtocollo($pracom_rec) {
        if (!$this->praLib->CtrPassword($_POST[$this->nameForm . '_password'])) {
            return;
        }
        if ($pracom_rec['COMTIP'] == "P") {
            $tipo = "PARTENZA";
            Out::attributo($this->nameForm . '_PROTRIC_DESTINATARIO', "readonly", '1');
            Out::attributo($this->nameForm . '_ANNOPROT_DESTINATARIO', "readonly", '1');
            Out::attributo($this->nameForm . '_DATAPROT_DESTINATARIO', "readonly", '1');
            if (!$this->SbloccaRicevuteBloccate()) {
                Out::msgStop("Errore", "Sblocco Protocollazione Ricevute Fallita");
                return false;
            }
        } else if ($pracom_rec['COMTIP'] == "A") {
            $tipo = "ARRIVO";
            Out::attributo($this->nameForm . '_PROTRIC_MITTENTE', "readonly", '1');
            Out::attributo($this->nameForm . '_ANNORIC_MITTENTE', "readonly", '1');
            Out::attributo($this->nameForm . '_DATAPROT_MITTENTE', "readonly", '1');
        }
        if ($pracom_rec) {
            $propas_rec = $this->praLib->GetPropas($this->keyPasso);
            $this->insertAudit($this->PRAM_DB, "PRACOM", "Inizio Procedura Pulizia Protocollo in $tipo Passo seq " . $propas_rec['PROSEQ']);
            $update_Info = "Oggetto : Rimuovo Protocollo  " . $pracom_rec['COMPRT'] . " in " . $pracom_rec['COMTIP'] . " del passo sequenza " . $propas_rec['PROSEQ'] . " della pratica n. " . $propas_rec['PRONUM'];
            $pracom_rec['COMPRT'] = "";
            $pracom_rec['COMDPR'] = "";
            $pracom_rec['COMMETA'] = "";
            if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                Out::msgStop("Errore", "Cancellazione Protocollo in $tipo Fallita");
                return false;
            }
            if (!$this->SbloccaAllegatiBloccati($pracom_rec['COMTIP'])) {
                Out::msgStop("Errore", "Sblocco Protocollazione Allegati in $tipo Fallita");
                return false;
            }
            $this->Dettaglio($this->keyPasso);
        }
    }

    function DecodPracomP($Codice) {
        $propas_rec = $this->praLib->GetPropas($Codice);
        $pracomP_rec = $this->praLib->GetPracomP($Codice);
        if ($pracomP_rec) {
            Out::valori($pracomP_rec, $this->nameForm . '_PRACOM');
            Out::valore($this->nameForm . '_DESTINATARIO', $pracomP_rec['COMCDE']);
            Out::valore($this->nameForm . '_DESC_DESTINATARIO', $pracomP_rec['COMNOM']);
            Out::valore($this->nameForm . '_EMAIL_DESTINATARIO', $pracomP_rec['COMMLD']);
//Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', $pracomP_rec['COMPRT']);
            Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', substr($pracomP_rec['COMPRT'], 4));
            Out::valore($this->nameForm . '_ANNOPROT_DESTINATARIO', substr($pracomP_rec['COMPRT'], 0, 4));
            Out::valore($this->nameForm . '_DATAPROT_DESTINATARIO', $pracomP_rec['COMDPR']);
            Out::valore($this->nameForm . '_CODFISC_DESTINATARIO', $pracomP_rec['COMFIS']);
            Out::valore($this->nameForm . '_NOTE_DESTINATARIO', $pracomP_rec['COMNOT']);
            Out::valore($this->nameForm . '_dataPartenza', $pracomP_rec['COMDAT']);
            Out::valore($this->nameForm . '_TIPO_PARTENZA', $pracomP_rec['COMTIN']);
            Out::valore($this->nameForm . '_ROWID_PRACOM', $pracomP_rec['ROWID']);
            Out::valore($this->nameForm . '_RIFERIMENTO', $pracomP_rec['ROWID']);
        } else {
            $this->InsertPartenza();
            if ($propas_rec['PROINT'] == 1) {
                Out::valore($this->nameForm . "_PRACOM[COMINT]", 1);
                $this->DecodIntestatario($propas_rec['PRONUM']);
            } else {
                if ($propas_rec['PROCDE']) {
                    $this->DecodAnamedComP($propas_rec['PROCDE']);
                }
            }
        }

        if (!$pracomP_rec) {
            Out::html($this->nameForm . '_statoComunicazione', "Comunicazione non ancora inviata");
        } else {
            if ($pracomP_rec['COMIDMAIL'] == "" && $pracomP_rec['COMDAT'] == "") {
                Out::html($this->nameForm . '_statoComunicazione', "Comunicazione non ancora inviata");
            } else {
                if ($pracomP_rec['COMIDMAIL']) {
                    $icon = $this->praLib->GetIconAccettazioneConsegna($pracomP_rec['COMIDMAIL'], $this->keyPasso, "PASSO");
                    Out::html($this->nameForm . '_statoComunicazione', "Comunicazione N. " . $pracomP_rec['ROWID'] . " inviata in data "
                            . substr($pracomP_rec['COMDAT'], 6, 2) . "/" . substr($pracomP_rec['COMDAT'], 4, 2) . "/" . substr($pracomP_rec['COMDAT'], 0, 4) . "<br>" . $icon['accettazione'] . "<br>" . $icon['consegna']);
                } else {
                    Out::html($this->nameForm . '_statoComunicazione', "Comunicazione non ancora inviata");
                }
            }
        }
        return $pracomP_rec;
    }

    function DecodPracomA($Codice, $rowid = false) {
        $pracomA_rec = $this->praLib->GetPracomA($Codice, $rowid);
        if ($pracomA_rec != false) {
            $praMitDest_rec = $this->praLib->GetPraArrivo($Codice);

            Out::valore($this->nameForm . '_PROTRIC_MITTENTE', substr($pracomA_rec['COMPRT'], 4));
            Out::valore($this->nameForm . '_ANNORIC_MITTENTE', substr($pracomA_rec['COMPRT'], 0, 4));
            Out::valore($this->nameForm . '_DATAPROT_MITTENTE', $pracomA_rec['COMDPR']);
            Out::valore($this->nameForm . '_NOTE_MITTENTE', $pracomA_rec['COMNOT']);

            Out::valore($this->nameForm . '_MITTENTE', $praMitDest_rec['CODICE']);
            Out::valore($this->nameForm . '_DESC_MITTENTE', $praMitDest_rec['NOME']);
            Out::valore($this->nameForm . '_EMAIL_MITTENTE', $praMitDest_rec['MAIL']);
            Out::valore($this->nameForm . '_CODFISC_MITTENTE', $praMitDest_rec['FISCALE']);
            Out::valore($this->nameForm . '_dataArrivo', $praMitDest_rec['DATAINVIO']);
            Out::valore($this->nameForm . '_oraArrivo', $praMitDest_rec['ORAINVIO']);
            Out::valore($this->nameForm . '_TIPO_ARRIVO', $praMitDest_rec['TIPOINVIO']);

            $Anamed_rec = $this->proLib->GetAnamed($praMitDest_rec['CODICE']);
            if ($Anamed_rec) {
                $Tabdag_tab = $this->proLib->GetTabdag("ANAMED", "chiave", $Anamed_rec['ROWID'], "EMAILPEC", 0, true);
                if ($Tabdag_tab) {
                    Out::show($this->nameForm . '_EMAIL_MITTENTE_butt');
                    Out::show($this->nameForm . '_MailPreferita');
                }
            }
            if ($pracomA_rec['COMIDMAIL']) {
                Out::show($this->nameForm . "_VediMailArrivo");
            }
        }
        return $pracomA_rec;
    }

    function DecodAnamed($Codice, $tipoRic = 'codice', $tutti = 'si') {
        $anamed_rec = $this->proLib->GetAnamed($Codice, $tipoRic, $tutti);
        Out::valore($this->nameForm . '_PROPAS[PROCDR]', $anamed_rec['MEDCOD']);
        Out::valore($this->nameForm . '_MEDNOM', $anamed_rec['MEDNOM']);
        return $anamed_rec;
    }

    function DecodeAnadoctipreg($codice, $tipoRic = "codice") {
        $Anadoctipreg_rec = $this->praLib->GetAnadoctipreg($codice, $tipoRic);
        Out::valore($this->nameForm . '_PROPAS[PRODOCTIPREG]', $Anadoctipreg_rec['CODDOCREG']);
        Out::valore($this->nameForm . '_descTipologia', $Anadoctipreg_rec['DESDOCREG']);
    }

    function GetDestinatariInvio() {
        $arrayDest = array();
        foreach ($this->destinatari as $keyDest => $dest) {
            if ($dest['MAIL'] == "") {
                continue;
            }
            if ($dest['IDMAIL'] == "") {
                $arrayDest[$keyDest] = $dest;
            }
        }
        return $arrayDest;
    }

    function DecodAnamedComP($Codice, $tipoRic = 'codice', $tutti = 'si') {
        $anamed_rec = $this->proLib->GetAnamed($Codice, $tipoRic, $tutti);
        if ($anamed_rec) {
            Out::valore($this->nameForm . '_DESTINATARIO', $anamed_rec['MEDCOD']);
            Out::valore($this->nameForm . '_DESC_DESTINATARIO', $anamed_rec['MEDNOM']);
            Out::valore($this->nameForm . '_PRACOM[COMIND]', $anamed_rec['MEDIND']);
            Out::valore($this->nameForm . '_PRACOM[COMCIT]', $anamed_rec['MEDCIT']);
            Out::valore($this->nameForm . '_PRACOM[COMPRO]', $anamed_rec['MEDPRO']);
            Out::valore($this->nameForm . '_PRACOM[COMCAP]', $anamed_rec['MEDCAP']);
            Out::valore($this->nameForm . '_EMAIL_DESTINATARIO', $anamed_rec['MEDEMA']);
            Out::valore($this->nameForm . '_CODFISC_DESTINATARIO', $anamed_rec['MEDFIS']);
        }
        return $anamed_rec;
    }

    function DecodAnamedComA($Codice, $tipoRic = 'codice', $tutti = 'si', $scegliMail = true) {
        $anamed_rec = $this->proLib->GetAnamed($Codice, $tipoRic, $tutti);
        if ($anamed_rec) {
            Out::valore($this->nameForm . '_MITTENTE', $anamed_rec['MEDCOD']);
            Out::valore($this->nameForm . '_DESC_MITTENTE', substr($anamed_rec['MEDNOM'], 0, 59));
//if ($_POST[$this->nameForm . "_EMAIL_MITTENTE"] == "") {
            Out::valore($this->nameForm . '_EMAIL_MITTENTE', $anamed_rec['MEDEMA']);
//}
            Out::valore($this->nameForm . '_CODFISC_MITTENTE', $anamed_rec['MEDFIS']);

            $Tabdag_tab = $this->proLib->GetTabdag("ANAMED", "chiave", $anamed_rec['ROWID'], "EMAILPEC", 0, true);
            if ($Tabdag_tab && $scegliMail == true) {
                Out::show($this->nameForm . '_EMAIL_MITTENTE_butt');
                Out::show($this->nameForm . '_MailPreferita');
                $ret = proRic::proRicTabdagMail(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "ANAMED", $anamed_rec['ROWID'], "", "Il Mittente ha più Mail. Sceglierne una");
                if ($ret) {
                    $Tabdag_rec = $this->proLib->GetTabdag($ret, 'rowid');
                    Out::valore($this->nameForm . '_EMAIL_MITTENTE', $Tabdag_rec['TDAGVAL']);
                    Out::hide($this->nameForm . '_EMAIL_MITTENTE_butt');
                    Out::hide($this->nameForm . '_MailPreferita');
                }
            } else {
                if (!$Tabdag_tab) {
                    Out::hide($this->nameForm . '_EMAIL_MITTENTE_butt');
                    Out::hide($this->nameForm . '_MailPreferita');
                }
            }
        } else {
            Out::valore($this->nameForm . "_MITTENTE", '');
        }
        return $anamed_rec;
    }

    function DecodStatoPasso($Codice) {
        $anastp_recAperto = $this->praLib->GetAnastp($Codice);
        Out::valore($this->nameForm . "_Stato1", $anastp_recAperto['STPDES']);
    }

    function DecodStatoPassoAP($Codice) {
        $anastp_recAperto = $this->praLib->GetAnastp($Codice);
        Out::valore($this->nameForm . "_Stato1", $anastp_recAperto['STPDES']);
    }

    function DecodStatoPassoCH($Codice) {
        $anastp_recChiuso = $this->praLib->GetAnastp($Codice);
        Out::valore($this->nameForm . "_Stato2", $anastp_recChiuso['STPDES']);
    }

    function DecodResponsabile($Codice, $tipoRic = 'codice', $propas_rec = "") {
        if ($propas_rec['PROSET'] == "") {
            $propas_rec['PROSET'] = "";
        }
        if ($propas_rec['PROSER'] == "") {
            $propas_rec['PROSET'] = "";
        }
        if ($propas_rec['PROUOP'] == "") {
            $propas_rec['PROSET'] = $propas_rec['PROSER'] = "";
        }
        $anauniOpe_rec = $this->praLib->GetAnauniOpe($propas_rec['PROSET'], $propas_rec['PROSER'], $propas_rec['PROUOP']);
        Out::valore($this->nameForm . '_UNITA', $anauniOpe_rec['UNIDES']);
    }

    function DecodVaialpasso($codice, $tipo = 'propak', $retid = "") {
        $propas_rec = $this->praLib->GetPropas($codice, $tipo);
        switch ($retid) {
            case "ANTECEDENTE":
//                $retAntecedente = $this->praLib->CheckAntecedente($propas_rec);
//                if(!$retAntecedente){
//                    Out::msgInfo("Attenzione", $this->praLib->getErrMessage());
//                    return false;
//                }
                Out::valore($this->nameForm . '_PROPAS[PROKPRE]', $propas_rec['PROPAK']);
                break;
            default:
                Out::valore($this->nameForm . '_Destinazione', $propas_rec['PROSEQ']);
                Out::valore($this->nameForm . '_DescrizioneVai', $propas_rec['PRODPA']);
                Out::valore($this->nameForm . '_PROPAS[PROVPA]', $propas_rec['PROPAK']);
                break;
        }
    }

    function DecodTipoPasso($codice, $tipo = 'codice') {
        $praclt_rec = $this->praLib->GetPraclt($codice, $tipo);
        Out::valore($this->nameForm . '_PROPAS[PROCLT]', $praclt_rec['CLTCOD']);
        Out::valore($this->nameForm . '_PROPAS[PRODTP]', $praclt_rec['CLTDES']);
    }

    function CheckDatiAggiuntiviPDF($dataDetail_rec) {
        $Prodst_rec = $this->praLib->GetProdst(pathinfo($dataDetail_rec['PASFIL'], PATHINFO_FILENAME) . ".info", "desc");
        if (!$Prodst_rec) {
            $dataDetail_rec['PASLOG'] = "";
            $update_Info = "Oggetto: Aggiornamento stato allegato " . $dataDetail_rec['PASLOG'];
            if (!$this->updateRecord($this->PRAM_DB, 'PASDOC', $dataDetail_rec, $update_Info)) {
                Out::msgStop("Errore", "Errore in aggiornamento su PASDOC");
                return false;
            }
        }
    }

    function ApriPasso() {
        if ($_POST[$this->nameForm . '_PROPAS']['PROFIN']) {
            $_POST[$this->nameForm . '_PROPAS']['PROFIN'] = "";
            Out::valore($this->nameForm . '_PROPAS[PROFIN]', "");
        } else {
            $_POST[$this->nameForm . '_PROPAS']['PROINI'] = $this->workDate;
            Out::valore($this->nameForm . '_PROPAS[PROINI]', $this->workDate);
        }
    }

    function GetTestiAssociati($procedimento, $tutti = false) {
        $this->testiAssociati = array();
        $Anapra_rec = $this->praLib->GetAnapra($procedimento);
        $tipoEnte = $this->praLib->GetTipoEnte();
        if ($tipoEnte == "M") {
            $ditta = App::$utente->getKey('ditta');
            $DB = $this->PRAM_DB;
        } else {
            if ($Anapra_rec['PRASLAVE'] == 1) {
                $ditta = $this->praLib->GetEnteMaster();
                $DB = ItaDB::DBOpen('PRAM', $ditta);
            } else {
                $ditta = App::$utente->getKey('ditta');
                $DB = $this->PRAM_DB;
            }
        }

        $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/';
//        $testiAssociati = array();
        if ($tutti == false) {
            $sql = "SELECT * FROM ITEPAS WHERE ITECOD = '$procedimento' AND ITEWRD<>'' AND ITEDOW = 1 AND ITEDRR = 0";
//            $sql = "SELECT ITEWRD FROM ITEPAS WHERE ITECOD = '$procedimento' AND ITEWRD<>'' AND ITEDOW = 1 AND ITEDRR = 0";
//            $testiAssociati = ItaDB::DBSQLSelect($DB, $sql, true);
//            foreach ($testiAssociati as $key => $passo) {
//                if ($passo['ITEWRD']) {
//                    $testiAssociati[$key]['FILEPATH'] = $destinazione . $passo['ITEWRD'];
//                    $testiAssociati[$key]['FILENAME'] = $passo['ITEWRD'];
//                    $testiAssociati[$key]['CLASSIFICAZIONE'] = "TESTOASSOCIATO";
//                } else {
//                    unset($testiAssociati[$key]);
//                }
//            }
        } else {
            $sql = "SELECT * FROM ITEPAS WHERE ITEWRD<>'' AND ITEDOW = 1 AND ITEDRR = 0";
//            $testiAssociati = $this->GetFileList($destinazione);
        }
        $testiAssociati = ItaDB::DBSQLSelect($DB, $sql, true);
        foreach ($testiAssociati as $key => $passo) {
//if ($passo['ITEWRD']) {
            $testiAssociati[$key]['FILEPATH'] = $destinazione . $passo['ITEWRD'];
            $testiAssociati[$key]['FILENAME'] = $passo['ITEWRD'];
            $testiAssociati[$key]['CLASSIFICAZIONE'] = "TESTOASSOCIATO";
//} else {
//unset($testiAssociati[$key]);
//}
        }

        if (!$testiAssociati) {
            return false;
        }
        return $testiAssociati;
    }

    function RegistraDestinatari() {
        $new_seq = 0;
        foreach ($this->destinatari as $dest) {
            if ($dest['ROWID'] == 0) {
                $new_seq += 10;
                $dest['KEYPASSO'] = $this->keyPasso;
                $dest['TIPOCOM'] = "D";
                $dest['SEQUENZA'] = $new_seq;
                // Tolgo il colore Arancione dal nome
                $dest['NOME'] = strip_tags($dest['NOME']);
//Valorizzo sempre il ROWIDPRACOM su PRAMITDEST con il ROWID unico di PRACOM PARTENZA
                $pracom_recP = $this->praLib->GetPracomP($this->keyPasso);
                if ($pracom_recP) {
                    $dest['ROWIDPRACOM '] = $pracom_recP['ROWID'];
                    $dest['SCADENZARISCONTRO'] = $this->praLib->CalcolaDataScadenza($pracom_recP['COMGRS'], $dest['DATAINVIO'], $dest['DATARISCONTRO']);
                }
                $insert_Info = 'Oggetto: Inserimento destinatario ' . $dest['NOME'];
                if (!$this->insertRecord($this->PRAM_DB, 'PRAMITDEST', $dest, $insert_Info)) {
                    return false;
                }
            } else {
                $pracom_recP = $this->praLib->GetPracomP($this->keyPasso);
                if ($pracom_recP) {
                    $dest['SCADENZARISCONTRO'] = $this->praLib->CalcolaDataScadenza($pracom_recP['COMGRS'], $dest['DATAINVIO'], $dest['DATARISCONTRO']);
                }
                unset($dest['ACCETTAZIONE']);
                unset($dest['CONSEGNA']);
                unset($dest['SBLOCCA']);
                unset($dest['VEDI']);
                unset($dest['FIRMATOCDS']);
                $update_Info = 'Oggetto: Aggiornamento destinatario ' . $dest['NOME'];
                if (!$this->updateRecord($this->PRAM_DB, 'PRAMITDEST', $dest, $update_Info)) {
                    return false;
                }
            }
        }
        $this->ordinaDestinatari();
        return true;
    }

    function AggiornaPartenzaDaPost() {
//$rowid = null;
        $partenza_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK = '$this->keyPasso' AND COMTIP = 'P'", false);
        $partenza_rec["COMMLD"] = $this->destinatari[0]['MAIL'];
        $partenza_rec["COMIND"] = $this->destinatari[0]['INDIRIZZO'];
        $partenza_rec["COMCAP"] = $this->destinatari[0]['CAP'];
        $partenza_rec["COMCIT"] = $this->destinatari[0]['COMUNE'];
        $partenza_rec["COMPRO"] = $this->destinatari[0]['PROVINCIA'];
        $partenza_rec["COMNOM"] = strip_tags($this->destinatari[0]['NOME']);
        $partenza_rec["COMCDE"] = $this->destinatari[0]['CODICE'];
        $partenza_rec["COMFIS"] = $this->destinatari[0]['FISCALE'];
        $partenza_rec["COMDAT"] = $this->destinatari[0]['DATAINVIO'];
        $partenza_rec["COMORA"] = $this->destinatari[0]['ORAINVIO'];
        $partenza_rec["COMIDMAIL"] = $this->destinatari[0]['IDMAIL'];
//
        $partenza_rec['COMPRT'] = $_POST[$this->nameForm . '_ANNOPROT_DESTINATARIO'] . $_POST[$this->nameForm . '_PROTRIC_DESTINATARIO'];
        $partenza_rec['COMDPR'] = $_POST[$this->nameForm . '_DATAPROT_DESTINATARIO'];
//
        $partenza_rec['COMTIN'] = $_POST[$this->nameForm . '_TIPO_PARTENZA'];
        $partenza_rec['COMNOT'] = $_POST[$this->nameForm . '_NOTE_DESTINATARIO'];
        $partenza_rec['COMGRS'] = $_POST[$this->nameForm . '_PRACOM']['COMGRS'];
        $partenza_rec['COMFSA'] = $_POST[$this->nameForm . '_PRACOM']['COMFSA'];
        if ($partenza_rec['ROWID']) {
//            if ($partenza_rec['COMDFI'] == "") {
//                if ($partenza_rec['COMGRS']) {
//                    if ($partenza_rec['COMDRI'] && $partenza_rec['COMDAT']) {
//                        $da_ta = $partenza_rec['COMDAT'];
//                    }
//                    if ($partenza_rec['COMDRI'] == "" && $partenza_rec['COMDAT']) {
//                        $da_ta = $partenza_rec['COMDAT'];
//                    }
//                    if ($partenza_rec['COMDRI'] && $partenza_rec['COMDAT'] == "") {
//                        $da_ta = $partenza_rec['COMDRI'];
//                    }
//                    $partenza_rec['COMDFI'] = $this->proLib->AddGiorniToData($da_ta, $partenza_rec['COMGRS']);
//                }
//            }
            $updateP_Info = "Oggetto: Aggiornamento comunicazione in partenza su PRACOM n. " . $partenza_rec['ROWID'] . " del passo " . $partenza_rec['COMPAK'];
            if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $partenza_rec, $updateP_Info)) {
                Out::msgStop("ATTENZIONE!", "Errore di Aggiornamento su Comunicazione.");
                return false;
            }
        } else {
            $retInsertPartenza = $this->InsertPartenza();
            if (!$retInsertPartenza) {
                Out::msgStop("ATTENZIONE!", "Errore di Inizializzazione Comunicazione in Partenza");
                return false;
            }
        }
        return true;
    }

    function ConfermaQualificaAllegati($campo, $valore) {
        $key = $_POST[$this->gridAllegati]['gridParam']['selrow'];
        $this->passAlle[$key][$campo] = $valore;
        $this->CaricaGriglia($this->gridAllegati, $this->passAlle);
        $this->BloccaDescAllegati();
    }

    function AggiornaPartenza($dest, $idMail = '') {
        $pracom_recP = $this->praLib->GetPracomP($this->keyPasso);
        $dest['DATAINVIO'] = date("Ymd");
        $dest['ORAINVIO'] = date("H:i:s");
        if ($pracom_recP) {
            $dest['SCADENZARISCONTRO'] = $this->praLib->CalcolaDataScadenza($pracom_recP['COMGRS'], $dest['DATAINVIO']);
        }
        if ($idMail) {
            $dest['IDMAIL'] = $idMail;
        }
        if ($dest['ROWID'] == 0) {
            $dest['TIPOCOM'] = 'D';
            $dest['KEYPASSO'] = $this->keyPasso;
            $dest['NOME'] = strip_tags($dest['NOME']);
//Valorizzo sempre il ROWIDPRACOM su PRAMITDEST con il ROWID unico di PRACOM PARTENZA

            if ($pracom_recP) {
                $dest['ROWIDPRACOM '] = $pracom_recP['ROWID'];
            }

            $insert_Info = "Oggetto: Inserimento destinatrio " . $dest['NOME'] . " del passo $this->keyPasso";
            if (!$this->insertRecord($this->PRAM_DB, 'PRAMITDEST', $dest, $insert_Info)) {
                Out::msgStop("ATTENZIONE!", "Errore Inserimento destinatario " . $dest['NOME']);
                return false;
            }
        } else {
            unset($dest['FIRMATOCDS']);
            $update_Info = "Oggetto: Aggiorno destinatrio " . $dest['NOME'] . " del passo $this->keyPasso";
            if (!$this->updateRecord($this->PRAM_DB, 'PRAMITDEST', $dest, $update_Info)) {
                Out::msgStop("ATTENZIONE!", "Errore Aggiornamento destinatario " . $dest['NOME']);
                return false;
            }
        }
        return true;
    }

    function InserisciComunicazione($Pracom_rec, $praMitDest_tab) {
//        $pracom_rec['COMTIP'] = 'A';
//        $pracom_rec['COMPAK'] = $this->keyPasso;
//        $pracom_rec['COMDAT'] = $_POST[$this->nameForm . '_dataArrivo'];
//        $pracom_rec['COMORA'] = $_POST[$this->nameForm . '_oraArrivo'];
//        $pracom_rec['COMCDE'] = $_POST[$this->nameForm . '_MITTENTE'];
//        $pracom_rec['COMNOM'] = $_POST[$this->nameForm . '_DESC_MITTENTE'];
//        $pracom_rec['COMFIS'] = $_POST[$this->nameForm . '_CODFISC_MITTENTE'];
//        $pracom_rec['COMMLD'] = $_POST[$this->nameForm . '_EMAIL_MITTENTE'];
//        $pracom_rec['COMPRT'] = $_POST[$this->nameForm . '_ANNORIC_MITTENTE'] . $_POST[$this->nameForm . '_PROTRIC_MITTENTE'];
//        $pracom_rec['COMDPR'] = $_POST[$this->nameForm . '_DATAPROT_MITTENTE'];
//        $pracom_rec['COMTIN'] = $_POST[$this->nameForm . '_TIPO_ARRIVO'];
//        $pracom_rec['COMNOT'] = $_POST[$this->nameForm . '_NOTE_MITTENTE'];
//        $pracom_rec['COMRIF'] = $_POST[$this->nameForm . '_RIFERIMENTO'];
//        $pracom_rec['COMIDMAIL'] = $this->daMail['IDMAIL'];
        $insert_Info = "Oggetto: Inserimento comunicazione in partenza del passo " . $Pracom_rec['COMPAK'];
        if (!$this->insertRecord($this->PRAM_DB, 'PRACOM', $Pracom_rec, $insert_Info)) {
            Out::msgStop("ATTENZIONE!", "Errore di Inserimento Arrivo su PRACOM.");
            return false;
        }
        $rowid = $this->getLastInsertId();
        $pracom_rec = $this->praLib->GetPracom($rowid, 'rowid');
        foreach ($praMitDest_tab as $key => $praMitDest_rec) {
//            $praMitDest_rec = array();
//            $praMitDest_rec['TIPOCOM'] = 'M';
//            $praMitDest_rec['KEYPASSO'] = $this->keyPasso;
//            $praMitDest_rec['DATAINVIO'] = $pracom_rec['COMDAT'];
//            $praMitDest_rec['ORAINVIO'] = $pracom_rec['COMORA'];
//            $praMitDest_rec['CODICE'] = "";
//            $praMitDest_rec['NOME'] = "";
//            $praMitDest_rec['FISCALE'] = "";
//            $praMitDest_rec['MAIL'] = $_POST[$this->nameForm . '_EMAIL_MITTENTE'];
//            $praMitDest_rec['TIPOINVIO'] = $_POST[$this->nameForm . '_TIPO_ARRIVO'];
//// Valorizzo sempre il ROWIDPRACOM su PRAMITDEST con il ROWID unico di PRACOM ARRIVO
            $praMitDest_rec['ROWIDPRACOM '] = $pracom_rec['ROWID'];
            $praMitDest_rec['IDMAIL'] = $this->daMail['IDMAIL'];
            $insert_Info = "Oggetto: Inserimento comunicazione in partenza del passo " . $praMitDest_rec['KEYPASSO'];
            if (!$this->insertRecord($this->PRAM_DB, 'PRAMITDEST', $praMitDest_rec, $insert_Info)) {
                Out::msgStop("ATTENZIONE!", "Errore di Inserimento Arrivo su PRAMITDEST.");
                return false;
            }
        }
        return $rowid;
    }

    function AggiornaArrivo() {
        if ($_POST[$this->nameForm . '_DESC_MITTENTE'] != '') {
            $arrivo_rec = $this->praLib->GetPracomA($this->keyPasso);
            if (!$arrivo_rec) {
// Preparo Inserimento PRACOM rec
//
                $pracomA_rec['COMTIP'] = 'A';
                $pracomA_rec['COMNUM'] = $this->currGesnum;
                $pracomA_rec['COMPAK'] = $this->keyPasso;
                $pracomA_rec['COMDAT'] = $_POST[$this->nameForm . '_dataArrivo'];
                $pracomA_rec['COMORA'] = $_POST[$this->nameForm . '_oraArrivo'];
                $pracomA_rec['COMCDE'] = $_POST[$this->nameForm . '_MITTENTE'];
                $pracomA_rec['COMNOM'] = $_POST[$this->nameForm . '_DESC_MITTENTE'];
                $pracomA_rec['COMFIS'] = $_POST[$this->nameForm . '_CODFISC_MITTENTE'];
                $pracomA_rec['COMMLD'] = $_POST[$this->nameForm . '_EMAIL_MITTENTE'];
                $pracomA_rec['COMPRT'] = $_POST[$this->nameForm . '_ANNORIC_MITTENTE'] . $_POST[$this->nameForm . '_PROTRIC_MITTENTE'];
                $pracomA_rec['COMDPR'] = $_POST[$this->nameForm . '_DATAPROT_MITTENTE'];
                $pracomA_rec['COMTIN'] = $_POST[$this->nameForm . '_TIPO_ARRIVO'];
                $pracomA_rec['COMNOT'] = $_POST[$this->nameForm . '_NOTE_MITTENTE'];
                $pracomA_rec['COMRIF'] = $_POST[$this->nameForm . '_RIFERIMENTO'];
                $pracomA_rec['COMIDMAIL'] = $this->daMail['IDMAIL'];
                $insertA_Info = "Oggetto: Inserimento comunicazione in arrivo su PRACOM del passo " . $pracomA_rec['COMPAK'];
                if (!$this->insertRecord($this->PRAM_DB, 'PRACOM', $pracomA_rec, $insertA_Info)) {
                    Out::msgStop("ATTENZIONE!", "Errore di Inserimento Arrivo su PRACOM.");
                    return false;
                }
//
// Preparo Inserimento PRAMITDEST rec
//
                $arrivo_rec = $this->praLib->GetPracomA($this->keyPasso);
                $praMitDest_rec['TIPOCOM'] = 'M';
                $praMitDest_rec['KEYPASSO'] = $this->keyPasso;
                $praMitDest_rec['DATAINVIO'] = $_POST[$this->nameForm . '_dataArrivo'];
                $praMitDest_rec['ORAINVIO'] = $_POST[$this->nameForm . '_oraArrivo'];
                $praMitDest_rec['CODICE'] = $_POST[$this->nameForm . '_MITTENTE'];
                $praMitDest_rec['NOME'] = $_POST[$this->nameForm . '_DESC_MITTENTE'];
                $praMitDest_rec['FISCALE'] = $_POST[$this->nameForm . '_CODFISC_MITTENTE'];
                $praMitDest_rec['MAIL'] = $_POST[$this->nameForm . '_EMAIL_MITTENTE'];
                $praMitDest_rec['TIPOINVIO'] = $_POST[$this->nameForm . '_TIPO_ARRIVO'];
// Valorizzo sempre il ROWIDPRACOM su PRAMITDEST con il ROWID unico di PRACOM ARRIVO
                $pracom_recA = $this->praLib->GetPracomA($this->keyPasso);
                if ($pracom_recA) {
                    $praMitDest_rec['ROWIDPRACOM '] = $pracom_recA['ROWID'];
                }
                $praMitDest_rec['IDMAIL'] = $this->daMail['IDMAIL'];
                $insertA_Info = "Oggetto: Inserimento comunicazione in arrivo su PRAMITDEST del passo " . $praMitDest_rec['KEYPASSO'];
                if (!$this->insertRecord($this->PRAM_DB, 'PRAMITDEST', $praMitDest_rec, $insertA_Info)) {
                    Out::msgStop("ATTENZIONE!", "Errore di Inserimento Arrivo su PRAMITDEST.");
                    return false;
                }
            } else {
//
// Preparo Aggiornamento PRACOM rec
//
                $rowid = $arrivo_rec['ROWID'];
                $arrivo_rec['COMNUM'] = $this->currGesnum;
                $arrivo_rec['COMDAT'] = $_POST[$this->nameForm . '_dataArrivo'];
                $arrivo_rec['COMORA'] = $_POST[$this->nameForm . '_oraArrivo'];
                $arrivo_rec['COMCDE'] = $_POST[$this->nameForm . '_MITTENTE'];
                $arrivo_rec['COMNOM'] = $_POST[$this->nameForm . '_DESC_MITTENTE'];
                $arrivo_rec['COMFIS'] = $_POST[$this->nameForm . '_CODFISC_MITTENTE'];
                $arrivo_rec['COMMLD'] = $_POST[$this->nameForm . '_EMAIL_MITTENTE'];
                $arrivo_rec['COMPRT'] = $_POST[$this->nameForm . '_ANNORIC_MITTENTE'] . $_POST[$this->nameForm . '_PROTRIC_MITTENTE'];
                $arrivo_rec['COMDPR'] = $_POST[$this->nameForm . '_DATAPROT_MITTENTE'];
                $arrivo_rec['COMTIN'] = $_POST[$this->nameForm . '_TIPO_ARRIVO'];
                $arrivo_rec['COMNOT'] = $_POST[$this->nameForm . '_NOTE_MITTENTE'];
                $arrivo_rec['ROWID'] = $rowid;
                $updateA_Info = "Oggetto: Aggiornamento comunicazione in arrivo su PRACOM n. " . $arrivo_rec['ROWID'] . " del passo " . $arrivo_rec['COMPAK'];
                if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $arrivo_rec, $updateA_Info)) {
                    Out::msgStop("ATTENZIONE!", "Errore di Aggiornamento Arrivo PRACOM.");
                    return false;
                }
//
// Preparo Aggiornamento PRAMITDEST rec
//
                $praMitDest_rec = $this->praLib->GetPraArrivo($this->keyPasso);
                $praMitDest_rec['CODICE'] = $_POST[$this->nameForm . '_MITTENTE'];
                $praMitDest_rec['NOME'] = $_POST[$this->nameForm . '_DESC_MITTENTE'];
                $praMitDest_rec['DATAINVIO'] = $_POST[$this->nameForm . '_dataArrivo'];
                $praMitDest_rec['ORAINVIO'] = $_POST[$this->nameForm . '_oraArrivo'];
                $praMitDest_rec['FISCALE'] = $_POST[$this->nameForm . '_CODFISC_MITTENTE'];
                $praMitDest_rec['MAIL'] = $_POST[$this->nameForm . '_EMAIL_MITTENTE'];
                $praMitDest_rec['TIPOINVIO'] = $_POST[$this->nameForm . '_TIPO_ARRIVO'];
                $praMitDest_rec['ROWIDPRACOM '] = $arrivo_rec['ROWID'];
                $updateA_Info = "Oggetto: Aggiornamento comunicazione in arrivo su PRAMITDEST del passo " . $praMitDest_rec['KEYPASSO'];
                if (!$this->updateRecord($this->PRAM_DB, 'PRAMITDEST', $praMitDest_rec, $updateA_Info)) {
                    Out::msgStop("ATTENZIONE!", "Errore di Aggiornamento Arrivo su PRAMITDEST.");
                    return false;
                }
            }
            if ($this->allegatiComunicazione) {
                if (!$this->RegistraAllegatiCom($this->keyPasso)) {
                    Out::msgStop("ERRORE", "Aggiornamento Allegati Comunicazione fallito");
                    return false;
                }
            }
            $this->chiudiForm = true;
        } else {
            if ($_POST[$this->nameForm . '_dataArrivo']) {
                Out::msgStop("ERRORE!!!", "Mittente Obbligatorio");
                $this->chiudiForm = false;
                return false;
            }
            return false;
        }
        return true;
    }

    function SetAllegatiValidi($MailArchivio_rec) {
        $Pasdoc_tab = $this->praLib->GetPasdoc($this->keyPasso, "codice", true);
        if ($Pasdoc_tab) {
            $allegatiCom = explode("|", $MailArchivio_rec['ATTACHMENTS']);
            $update_Info = "Oggetto: Aggiorno stato allegati validi comunicazione passo " . $Pasdoc_tab[0]['PASKEY'];
            foreach ($Pasdoc_tab as $Pasdoc_rec) {
                foreach ($allegatiCom as $nomeAllegato) {
                    if ($nomeAllegato == $Pasdoc_rec['PASNAME']) {
                        $Pasdoc_rec['PASSTA'] = "V";
                        if (!$this->updateRecord($this->PRAM_DB, 'PASDOC', $Pasdoc_rec, $update_Info)) {
                            Out::msgStop("Errore in Aggionamento", "Aggiornamento allegato " . $Pasdoc_rec['PASNAME'] . " fallito");
                            break;
                        }
                    }
                }
            }
        }
    }

    function GetDatiMailProtocollo($allegati, $elementi, $tipo) {
        $Propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
        $dati['allegatiProt'] = array();
        if ($allegati) {
            $Path = $this->praLib->SetDirectoryPratiche(substr($this->keyPasso, 0, 4), $this->keyPasso, "PASSO", false);
            $dati['allegatiProt'] = array();
            if (isset($allegati['Principale'])) {
                $pasdoc_rec = $this->praLib->GetPasdoc($allegati['Principale']['ROWID'], "ROWID");
                $dati['allegatiProt'][] = array(
                    "FILEORIG" => $pasdoc_rec['PASNAME'],
                    "FILENAME" => $pasdoc_rec['PASNAME'],
                    "FILEINFO" => $pasdoc_rec['PASNOT'],
                    "FILEPATH" => $Path . "/" . $pasdoc_rec['PASFIL']
                );
            }

            foreach ($allegati['pasdoc_rec'] as $rowid) {
                $pasdoc_rec = $this->praLib->GetPasdoc($rowid['ROWID'], "ROWID");
                $dati['allegatiProt'][] = array(
                    "FILEORIG" => $pasdoc_rec['PASNAME'],
                    "FILENAME" => $pasdoc_rec['PASNAME'],
                    "FILEINFO" => $pasdoc_rec['PASNOT'],
                    "FILEPATH" => $Path . "/" . $pasdoc_rec['PASFIL']
                );
            }
        }

        $dati['valori'] = array(
            "Destinatario" => "Ufficio Protocollo",
            "Oggetto" => "Inoltro Comunicazione in $tipo passo seq " . $Propas_rec['PROSEQ'] . ", pratica $this->currGesnum per protocollazione",
            "Procedimento" => $this->currGesnum,
            "Corpo" => "Si inoltra per protocollazione Comunicazione in $tipo passo seq " . $Propas_rec['PROSEQ'] . "<br><br>" . $elementi['dati']['Oggetto'],
            "Anno" => $_POST[$this->nameForm . "_Anno_prot"],
            "Numero" => $_POST[$this->nameForm . "_Numero_prot"],
        );

        $dati["returnModel"] = $this->nameForm;
        return $dati;
    }

    function AggiornaRecord() {
        return $this->aggiornaDati();
    }

    public function aggiornaDati() {

        //Out::msgInfo("Propak", $this->nameForm . '<br>' . $this->propak);
        $propas_tmp = $this->praLib->GetPropas($this->keyPasso, 'propak');
        if (!$propas_tmp) {
            Out::msgStop("Aggiornamento Passo", "Lettura record passo fallita");
            return false;
        }

        $procedimento = $propas_tmp['PRONUM'];
        $keyPasso = $propas_tmp['PROPAK'];
        $propas_rec = $_POST[$this->nameForm . '_PROPAS'];

        if (!$propas_rec) {
            Out::msgStop("Aggiornamento Passo", "Record del passo non più disponibile per l'aggiornamento");
            return false;
        }

        if (!$this->ValorizzaProall()) {
            Out::msgStop("Aggiornamento", "Errore aggiornamento campo PROALL");
            return false;
        }

        /*
         * Salvataggio dati aggiuntivi.
         */

        /* @var $praCompDatiAggiuntivi praCompDatiAggiuntivi */
        $praCompDatiAggiuntivi = itaModel::getInstance($this->praCompDatiAggiuntiviFormname[0], $this->praCompDatiAggiuntiviFormname[1]);
//        Out::msgInfo("nameForm",$praCompDatiAggiuntivi->nameForm);
//        Out::msgInfo("nameFormOrig",$praCompDatiAggiuntivi->nameFormOrig);
        if (!$praCompDatiAggiuntivi->aggiornaDati()) {
            return false;
        }

        /*
         * Registro dati Assegnazioni/Trasmissioni se il parametro è attivo
         */
        $arrayStatiTab = $this->praLibPasso->setStatiTabPasso($propas_rec);
        if ($this->flagAssegnazioniPasso && $arrayStatiTab[praLibPasso::PANEL_ASSEGNAZIONI]['Stato'] == 'Show') {
            $this->registraDatiProtocollo($propas_tmp['PASPRO'], $propas_tmp['PASPAR'], $propas_rec['PRODPA']);
        }

        /*
         * Sincronizzo Giorni e data scadenza poi mi rileggo il record per aggiornare i 2 campi
         */
        $arrayScadenza = $this->praLib->SincDataScadenza("PASSO", $this->keyPasso, $propas_rec['PRODSC'], "", $propas_rec['PROGIO'], $propas_rec['PROINI'], true);
        if (!$arrayScadenza) {
            Out::msgStop("Sincronizzazione Scadenza", "Errore Sincronizzazione Data Scadenza.");
            return false;
        }
        $propas_rec['PRODPA'] = itaLib::decodeUnicodeCharacters($propas_rec['PRODPA']);
        $propas_rec['PRODSC'] = $arrayScadenza['SCADENZA'];
        $propas_rec['PROGIO'] = $arrayScadenza['GIORNI'];
//
        if ($propas_rec['PRORPA']) {
            $propas_rec['PRORPA'] = str_pad($propas_rec['PRORPA'], 6, "0", STR_PAD_LEFT);
        }

        if ($this->bloccoAllegatiRiservati()) {
            $propas_rec['PROPFLALLE'] = 0;
        }

//COMMOMS
        $propas_rec['PROUTEEDIT'] = App::$utente->getKey('nomeUtente');
        $propas_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date("H:i:s");
        $update_Info = "Oggetto: Aggiornamento passo con seq " . $propas_rec['PROSEQ'] . " e chiave " . $propas_rec['PROPAK'];
        if (!$this->updateRecord($this->PRAM_DB, 'PROPAS', $propas_rec, $update_Info)) {
            Out::msgStop("ERRORE", "Aggiornamento record");
            return false;
        }
//

        /*
         * Registra Allegati
         */
        if (!$this->RegistraAllegati($keyPasso)) {
            Out::msgStop("ERRORE", "Aggiornamento Allegati fallito");
            return false;
        }
        if (!$this->praLib->ordinaPassi($procedimento)) {
            Out::msgStop("Errore orina passi", $this->praLib->getErrMessage());
        }

        /*
         * Sincronizzo lo Stato
         */
        if (!$this->praLib->sincronizzaStato($procedimento)) {
            Out::msgStop("Errore sinc stato passo", $this->praLib->getErrMessage());
        }

        /*
         * Sincronizzo il calendario
         */
        $errMsg = $this->praLib->sincCalendar("PASSO", $this->keyPasso, $propas_rec['PRODSC'], $_POST[$this->nameForm . "_Calendario"]);
        if ($errMsg) {
            Out::msgStop("Errore Sincronizzazione Calendario", $errMsg);
        }
        return true;
    }

    public function GetDescEvento($propas_rec) {
        $descEvento = "Passo Seq. " . $propas_rec['PROSEQ'] . "<br>";
        $descEvento .= "Descrizione Passo: " . $propas_rec['PRODPA'] . "<br>";
        return $descEvento;
    }

    function ValorizzaProall() {
        $propas_rec = $this->praLib->GetPropas($this->keyPasso);
        if ($this->passAlle) {
            $allegatiClean = $this->praLib->cleanArrayTree($this->passAlle);
            $propas_rec['PROALL'] = serialize($allegatiClean);
        } else {
            if ($this->allegatiComunicazione) {
                $alle = serialize($this->allegatiComunicazione);
            } else {
                $alle = "";
            }
            $propas_rec['PROALL'] = $alle;
        }
        $update_Info = "Oggetto: Aggiornamento campo PROALL del passo seq " . $propas_rec['PROSEQ'] . " e chiave " . $propas_rec['PROPAK'];
        if (!$this->updateRecord($this->PRAM_DB, 'PROPAS', $propas_rec, $update_Info)) {
            return false;
        }
        return true;
    }

    function GetFileList($filePath, $procedimento) {
        if (!$dh = @opendir($filePath)) {
            return false;
        }
        $retListGen = array();
        $rowid = 0;
        while (($obj = @readdir($dh))) {
            if ($obj == '.' || $obj == '..')
                continue;
            $rowid += 1;
            $retListGen[$rowid] = array(
//       'rowid' => $rowid,
                'TIPO' => 'Procedimento ' . $procedimento,
                'FILEPATH' => $filePath . '/' . $obj,
                'FILENAME' => $obj,
                'FILEINFO' => $obj,
                'RIDORIG' => '0'
            );
        }
        closedir($dh);
        return $retListGen;
    }

    function ContaSizeAllegati($allegati) {
        if ($allegati) {
            $totSize = 0;
            foreach ($allegati as $allegato) {
                $totSize = $totSize + filesize($allegato['FILEPATH']);
            }
            if ($totSize != 0) {
                $Size = $this->praLib->formatFileSize($totSize);
                Out::valore($this->nameForm . "_Totale", $Size);
            }
        }
    }

    private function caricaAllegatiEsterni($lista = "", $pasdoc_rec = array()) {
        $propas_rec = $this->praLib->GetPropas($this->keyPasso);
        $arrNomi = array();
        foreach ($this->passAlle as $key => $allegato) {
            $arrNomi[] = $allegato['FILEORIG'];
        }

        foreach ($lista as $key => $uplAllegato) {
            $nuovoNome = $uplAllegato['FILEORIG'];
            $contatore = 0;
            while (true) {
                if (in_array($nuovoNome, $arrNomi)) {
                    $contatore += 1;
                    $nuovoNome = pathinfo($uplAllegato['FILEORIG'], PATHINFO_FILENAME) . "_" . $contatore . "." . pathinfo($uplAllegato['FILEORIG'], PATHINFO_EXTENSION);
                } else {
                    break;
                }
            }
            $lista[$key]['FILEORIG'] = $nuovoNome;
        }

        $posEsterno = -1;
        $numLevel0 = 0;
        foreach ($this->passAlle as $posE => $allegato) {
            if ($allegato['RANDOM'] == 'ESTERNO') {
                $posEsterno = $posE;
                $parent = $allegato['PROV'];
                break;
            }
            if ($allegato['level'] == 0) {
                $numLevel0++;
            }
        }
        if ($posEsterno == -1) {
            $allegatoLevel0['PROV'] = "L0_" . $numLevel0;
            $allegatoLevel0['RANDOM'] = 'ESTERNO';
            $allegatoLevel0['NAME'] = 'ESTERNO';
            $allegatoLevel0['level'] = 0;
            $allegatoLevel0['parent'] = null;
            $allegatoLevel0['isLeaf'] = 'false';
            $allegatoLevel0['expanded'] = 'true';
            $allegatoLevel0['loaded'] = 'true';
            $this->passAlle[] = $allegatoLevel0;
            foreach ($lista as $key => $uplAllegato) {
                $funzAgg = "";
                $ext = pathinfo($uplAllegato['FILEPATH'], PATHINFO_EXTENSION);
                $edit = "";
                if (strtolower($ext) == "zip") {
                    $edit = "<span class=\"ita-icon ita-icon-winzip-24x24\">Estrai File Zip</span>";
                }

                $funzAgg = $this->GetImgPreview($ext, pathinfo($uplAllegato['FILEPATH'], PATHINFO_DIRNAME), $uplAllegato);


                $index_1 = count($this->passAlle);
                $allegatoLevel1 = array();
                //Valorizzo Tabella
                $allegatoLevel1['PROV'] = $index_1;
                $allegatoLevel1['RANDOM'] = '<span style = "color:orange;">' . $uplAllegato['FILENAME'] . '</span>';
                $allegatoLevel1['NAME'] = '<span style = "color:orange;">' . $uplAllegato['FILEORIG'] . '</span>';
                $allegatoLevel1['INFO'] = $uplAllegato['FILEINFO'];
                $allegatoLevel1['PREVIEW'] = $funzAgg;
                $allegatoLevel1['EDIT'] = $edit;
                $allegatoLevel1['SIZE'] = $this->praLib->formatFileSize(filesize($uplAllegato['FILEPATH']));
                if (strtolower(pathinfo($uplAllegato['FILEPATH'], PATHINFO_EXTENSION)) == "pdf") {
                    $allegatoLevel1['STATO'] = "<span>Clicca per funzioni aggiuntive</span>";
                } elseif (strtolower(pathinfo($uplAllegato['FILEPATH'], PATHINFO_EXTENSION)) == "p7m") {
                    $allegatoLevel1['STATO'] = "<span>Clicca per verificare il file</span>";
                }
                $allegatoLevel1['EVIDENZIA'] = "<div class=\"ita-html\"><span class=\"ita-icon ita-icon-evidenzia-24x24 ita-colorpicker {type: 'divColor',swatches:true}\">Evidenzia Allegato</span></div>";
                $allegatoLevel1['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-24x24\">Blocca Allegato</span>";
                $allegatoLevel1['PUBBLICA'] = "<span class=\"ita-icon ita-icon-publish-24x24\">Allegato pubblicato</span>";
                $allegatoLevel1['PASPUB'] = 1;
                //Valorizzo Array
                $allegatoLevel1['PROVENIENZA'] = 'ESTERNO';
                $allegatoLevel1['FILEINFO'] = $uplAllegato['FILEINFO'];
                $allegatoLevel1['FILEPATH'] = $uplAllegato['FILEPATH'];
                $allegatoLevel1['FILENAME'] = $uplAllegato['FILENAME'];
                $allegatoLevel1['FILEORIG'] = $uplAllegato['FILEORIG'];
                $allegatoLevel1['PASUTELOG'] = App::$utente->getKey('nomeUtente');
                $allegatoLevel1['PASORADOC'] = date("H:i:s");
                $allegatoLevel1['PASDATADOC'] = date("Ymd");
                $allegatoLevel1['PASDAFIRM'] = 1;
                $allegatoLevel1['PASPRTCLASS'] = $pasdoc_rec['PASPRTCLASS'];
                $allegatoLevel1['PASPRTROWID'] = $pasdoc_rec['PASPRTROWID'];
                $allegatoLevel1['PASSHA2'] = hash_file('sha256', $allegatoLevel1['FILEPATH']);
                $allegatoLevel1['CDS'] = $this->praLib->getIconCds(0, $_POST[$this->nameForm . '_PROPAS']['PROFLCDS']);
                $allegatoLevel1['PASFLCDS'] = 0;
                $allegatoLevel1['ROWID'] = 0;
                $allegatoLevel1['level'] = 1;
                $allegatoLevel1['parent'] = "L0_" . $numLevel0;
                $allegatoLevel1['isLeaf'] = 'true';
                $this->passAlle[] = $allegatoLevel1;
            }
        } else {
            $i = $posEsterno + 1;
            $trovato = false;
            while ($trovato == false) {
                if ($i >= count($this->passAlle)) {
                    $trovato = true;
                } else {
                    if ($this->passAlle[$i]['level'] == 0) {
                        $trovato = true;
                    } else {
                        $i++;
                    }
                }
            }
            $arrayTop = array_slice($this->passAlle, 0, $i);
            $arrayDown = array_slice($this->passAlle, $i);
            foreach ($lista as $key => $uplAllegato) {
                $funzAgg = "";
                $ext = pathinfo($uplAllegato['FILEPATH'], PATHINFO_EXTENSION);
                $edit = "";
                if (strtolower($ext) == "zip") {
                    $edit = "<span class=\"ita-icon ita-icon-winzip-24x24\">Estrai File Zip</span>";
                }

                $funzAgg = $this->GetImgPreview($ext, pathinfo($uplAllegato['FILEPATH'], PATHINFO_DIRNAME), $uplAllegato);


                $allegatoLevel1 = array();
                //Valorizzo Tabella
                $allegatoLevel1['PROV'] = $i;
                $allegatoLevel1['RANDOM'] = '<span style = "color:orange;">' . $uplAllegato['FILENAME'] . '</span>';
                $allegatoLevel1['NAME'] = '<span style = "color:orange;">' . $uplAllegato['FILEORIG'] . '</span>';
                $allegatoLevel1['INFO'] = $uplAllegato['FILEINFO'];
                $allegatoLevel1['PREVIEW'] = $funzAgg;
                $allegatoLevel1['EDIT'] = $edit;
                $allegatoLevel1['SIZE'] = $this->praLib->formatFileSize(filesize($uplAllegato['FILEPATH']));
                if (strtolower(pathinfo($uplAllegato['FILEPATH'], PATHINFO_EXTENSION)) == "pdf") {
                    $allegatoLevel1['PREVIEW'] = "<span class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Importa campi aggiuntivi\"></span>";
                    $allegatoLevel1['STATO'] = "<span>Clicca per funzioni aggiuntive</span>";
                }
                $allegatoLevel1['EVIDENZIA'] = "<div class=\"ita-html\"><span class=\"ita-icon ita-icon-evidenzia-24x24 ita-colorpicker {type: 'divColor',swatches:true}\">Evidenzia Allegato</span></div>";
                $allegatoLevel1['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-24x24\">Blocca Allegato</span>";
                $allegatoLevel1['PUBBLICA'] = "<span class=\"ita-icon ita-icon-publish-24x24\">Allegato pubblicato</span>";
                $allegatoLevel1['PASPUB'] = 1;

                //Valorizzo Array
                $allegatoLevel1['PROVENIENZA'] = 'ESTERNO';
                $allegatoLevel1['FILEINFO'] = $uplAllegato['FILEINFO'];
                $allegatoLevel1['FILEPATH'] = $uplAllegato['FILEPATH'];
                $allegatoLevel1['FILENAME'] = $uplAllegato['FILENAME'];
                $allegatoLevel1['FILEORIG'] = $uplAllegato['FILEORIG'];
                $allegatoLevel1['PASUTELOG'] = App::$utente->getKey('nomeUtente');
                $allegatoLevel1['PASORADOC'] = date("H:i:s");
                $allegatoLevel1['PASDATADOC'] = date("Ymd");
                $allegatoLevel1['PASDAFIRM'] = 1;
                $allegatoLevel1['PASPRTCLASS'] = $pasdoc_rec['PASPRTCLASS'];
                $allegatoLevel1['PASPRTROWID'] = $pasdoc_rec['PASPRTROWID'];
                $allegatoLevel1['PASSHA2'] = hash_file('sha256', $allegatoLevel1['FILEPATH']);
                $allegatoLevel1['CDS'] = $this->praLib->getIconCds(0, $_POST[$this->nameForm . '_PROPAS']['PROFLCDS']);
                $allegatoLevel1['PASFLCDS'] = 0;
                $allegatoLevel1['ROWID'] = 0;
                $allegatoLevel1['level'] = 1;

                $allegatoLevel1['parent'] = $parent;
                $allegatoLevel1['isLeaf'] = 'true';
                $allegatoLevel1['expanded'] = 'true';
                $allegatoLevel1['loaded'] = 'true';
                $arrayTop[] = $allegatoLevel1;
                $i++;
            }
            $nAggiunti = count($lista);
            foreach ($arrayDown as $chiave => $recordDown) {
                if ($recordDown['level'] == 1) {
                    $arrayDown[$chiave]['PROV'] = $recordDown['PROV'] + $nAggiunti;
                }
            }
            $this->passAlle = array_merge($arrayTop, $arrayDown);
        }
        $this->ContaSizeAllegati($this->passAlle);
        $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');
        $this->bloccoAllegatiRiservati();
        $this->BloccaDescAllegati();
        return;
    }

//private function caricaTestoAssociato($rowid) {
    private function caricaTestoAssociato($testo) {
//$testo = $this->testiAssociati[$rowid];
        $posInterno = -1;
        $numLevel0 = 0;
        foreach ($this->passAlle as $posI => $alle) {
            if ($alle['RANDOM'] == 'TESTOASSOCIATO') {
                $posInterno = $posI;
                $parent = $alle['PROV'];
                break;
            }
            if ($alle['level'] == 0) {
                $numLevel0++;
            }
        }
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Archiviazione File.", "Creazione ambiente di lavoro temporaneo fallita.");
                $this->returnToParent();
            }
        }
        $percorsoTmp = itaLib::getPrivateUploadPath();


        $randName = md5(rand() * time()) . "." . pathinfo($testo['FILENAME'], PATHINFO_EXTENSION);
        $fileOrig = $testo['FILENAME'];
        @copy($testo['FILEPATH'], $percorsoTmp . "/" . $randName);

        if ($posInterno == -1) {
            $allegatoLevel0['PROV'] = "L0_" . $numLevel0;
            $allegatoLevel0['RANDOM'] = 'TESTOASSOCIATO';
            $allegatoLevel0['NAME'] = 'TESTOASSOCIATO';
            $allegatoLevel0['level'] = 0;
            $allegatoLevel0['parent'] = null;
            $allegatoLevel0['isLeaf'] = 'false';
            $allegatoLevel0['expanded'] = 'true';
            $allegatoLevel0['loaded'] = 'true';
            $this->passAlle[] = $allegatoLevel0;
//Valorizzo Tabella
            $keyInc = count($this->passAlle); // + 1;
            $allegatoLevel1['INFO'] = $testo['FILENAME'];
            $allegatoLevel1['RANDOM'] = '<span style="color:orange;">' . $randName . '</span>'; //$allegato['CODICE'];
            $allegatoLevel1['NAME'] = '<span style="color:orange;">' . $fileOrig . '</span>'; //$allegato['CODICE'];
            $allegatoLevel1['PROV'] = $keyInc;
//$allegatoLevel1['SIZE'] = round((filesize($percorsoTmp . "/" . $randName) / 1048576), 3) . "MB";
            $allegatoLevel1['SIZE'] = $this->praLib->formatFileSize(filesize($percorsoTmp . "/" . $randName));
            $allegatoLevel1['PREVIEW'] = "<span class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Importa campi aggiuntivi\"></span>";
            $allegatoLevel1['STATO'] = "<span>Clicca per funzioni aggiuntive</span>";
            $allegatoLevel1['EVIDENZIA'] = "<div class=\"ita-html\"><span class=\"ita-icon ita-icon-evidenzia-24x24 ita-colorpicker {type: 'divColor',swatches:true}\">Evidenzia Allegato</span></div>";
            $allegatoLevel1['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-24x24\">Blocca Allegato</span>";
//Valorizzo Array
            $allegatoLevel1['PROVENIENZA'] = 'TESTOASSOCIATO';
            $allegatoLevel1['FILEINFO'] = $testo['FILENAME'];
            $allegatoLevel1['FILEPATH'] = $percorsoTmp . "/" . $randName;
            $allegatoLevel1['FILENAME'] = $randName;
            $allegatoLevel1['FILEORIG'] = $fileOrig;
            $allegatoLevel1['PASUTELOG'] = App::$utente->getKey('nomeUtente');
            $allegatoLevel1['PASORADOC'] = date("H:i:s");
            $allegatoLevel1['PASDATADOC'] = date("Ymd");
            $allegatoLevel1['PASDAFIRM'] = 1;
            $allegatoLevel1['PASSHA2'] = hash_file('sha256', $allegatoLevel1['FILEPATH']);
            $allegatoLevel1['CODICE'] = $randName; //$allegato['CODICE'];
            $allegatoLevel1['ROWID'] = 0;
            $allegatoLevel1['CDS'] = $this->praLib->getIconCds(0, $_POST[$this->nameForm . '_PROPAS']['PROFLCDS']);
            $allegatoLevel1['PASFLCDS'] = 0;
            $allegatoLevel1['level'] = 1;
            $allegatoLevel1['parent'] = "L0_" . $numLevel0;
            $allegatoLevel1['isLeaf'] = 'true';
//$this->passAlle[count($this->passAlle) + 1] = $allegatoLevel1;
            $this->passAlle[$keyInc] = $allegatoLevel1;
        } else {
            $i = $posInterno + 1;
            $trovato = false;
            while ($trovato == false) {
                if ($i >= count($this->passAlle)) {
                    $trovato = true;
                } else {
                    if ($this->passAlle[$i]['level'] == 0) {
                        $trovato = true;
                    } else {
                        $i++;
                    }
                }
            }
            $allegatoLevel1 = array();
            $arrayTop = array_slice($this->passAlle, 0, $i);
            $arrayDown = array_slice($this->passAlle, $i);
//Valorizzo Tabella
            $inc = count($this->passAlle); // + 1;
            $allegatoLevel1['PROV'] = $inc;
            $allegatoLevel1['RANDOM'] = '<span style="color:orange;">' . $randName . '</span>';
            $allegatoLevel1['NAME'] = '<span style="color:orange;">' . $fileOrig . '</span>';
            $allegatoLevel1['INFO'] = $testo['FILENAME'];
//$allegatoLevel1['SIZE'] = round((filesize($percorsoTmp . "/" . $randName) / 1048576), 3) . "MB";
            $allegatoLevel1['SIZE'] = $this->praLib->formatFileSize(filesize($percorsoTmp . "/" . $randName));
            $allegatoLevel1['PREVIEW'] = "<span class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Genera pdf\"></span>";
            $allegatoLevel1['STATO'] = "<span>Clicca sull'ingranaggio per generare il PDF</span>";
            $allegatoLevel1['EVIDENZIA'] = "<div class=\"ita-html\"><span class=\"ita-icon ita-icon-evidenzia-24x24 ita-colorpicker {type: 'divColor',swatches:true}\">Evidenzia Allegato</span></div>";
            $allegatoLevel1['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-24x24\">Blocca Allegato</span>";
//Valorizzo Array
            $allegatoLevel1['PROVENIENZA'] = 'TESTOASSOCIATO';
            $allegatoLevel1['FILEINFO'] = $testo['FILENAME'];
            $allegatoLevel1['FILEPATH'] = $percorsoTmp . "/" . $randName;
            $allegatoLevel1['FILENAME'] = $randName;
            $allegatoLevel1['FILEORIG'] = $fileOrig;
            $allegatoLevel1['PASUTELOG'] = App::$utente->getKey('nomeUtente');
            $allegatoLevel1['PASORADOC'] = date("H:i:s");
            $allegatoLevel1['PASDATADOC'] = date("Ymd");
            $allegatoLevel1['PASDAFIRM'] = 1;
            $allegatoLevel1['PASSHA2'] = hash_file('sha256', $allegatoLevel1['FILEPATH']);
            $allegatoLevel1['CODICE'] = $randName; //$allegato['CODICE'];
            $allegatoLevel1['ROWID'] = 0;
            $allegatoLevel1['CDS'] = $this->praLib->getIconCds(0, $_POST[$this->nameForm . '_PROPAS']['PROFLCDS']);
            $allegatoLevel1['PASFLCDS'] = 0;
            $allegatoLevel1['level'] = 1;
            $allegatoLevel1['parent'] = $parent;
            $allegatoLevel1['isLeaf'] = 'true';

            $this->passAlle[$inc] = $allegatoLevel1;

            $arrayTop[] = $allegatoLevel1;

            $inc++;
            foreach ($arrayDown as $chiave => $recordDown) {
                if ($recordDown['level'] == 1) {
                    $arrayDown[$chiave]['PROV'] = $recordDown['PROV'] + 1;
                }
            }
            $this->passAlle = array_merge($arrayTop, $arrayDown);
        }
        $this->ContaSizeAllegati($this->passAlle);
        $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');
        $this->bloccoAllegatiRiservati();
        $this->BloccaDescAllegati();
        return;
    }

    function DecodAnacla($Codice, $tipoRic = 'codice') {
        $anacla_rec = $this->praLib->GetAnacla($Codice, $tipoRic);
        if ($anacla_rec) {
            Out::valore($this->nameForm . '_CodiceCla', $anacla_rec['CLACOD']);
            Out::valore($this->nameForm . '_Classificazione', $anacla_rec['CLADES']);
        } else {
            Out::valore($this->nameForm . '_CodiceCla', "");
            Out::valore($this->nameForm . '_Classificazione', "");
        }
    }

    function DecodAnaddo($Codice, $tipoRic = 'codice') {
        $anaddo_rec = $this->praLib->GetAnaddo($Codice, $tipoRic);
        if ($anaddo_rec) {
            Out::valore($this->nameForm . '_Destinazione', $anaddo_rec['DDONOM']);
        } else {
            Out::valore($this->nameForm . '_Destinazione', "");
        }
    }

    private function caricaAllegatiInterno() {
        $allegati = array();
        $selRows = explode(",", $_POST['retKey']);
        foreach ($selRows as $rowid) {
            foreach ($this->allegatiAppoggio as $keyAlle => $alle) {
                if ($alle['RIDORIG'] == $rowid) {
                    $allegati[] = $this->allegatiAppoggio[$keyAlle];
                }
            }
        }

        $posInterno = -1;
        $numLevel0 = 0;
        foreach ($this->passAlle as $posI => $alle) {
            if ($alle['RANDOM'] == 'INTERNO') {
                $posInterno = $posI;
                $parent = $alle['PROV'];
                break;
            }
            if ($alle['level'] == 0) {
                $numLevel0++;
            }
        }

        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Archiviazione File.", "Creazione ambiente di lavoro temporaneo fallita.");
                $this->returnToParent();
            }
        }


        $percorsoTmp = itaLib::getPrivateUploadPath();
        if ($posInterno == -1) {
            $allegatoLevel0['PROV'] = "L0_" . $numLevel0;
            $allegatoLevel0['RANDOM'] = 'INTERNO';
            $allegatoLevel0['NAME'] = 'INTERNO';
            $allegatoLevel0['level'] = 0;
            $allegatoLevel0['parent'] = null;
            $allegatoLevel0['isLeaf'] = 'false';
            $allegatoLevel0['expanded'] = 'true';
            $allegatoLevel0['loaded'] = 'true';
            $this->passAlle[] = $allegatoLevel0;
            foreach ($allegati as $allegato) {
                $fileOrig = $allegato['FILENAME'];
                usleep(50000); // 50 millisecondi;
                list($msec, $sec) = explode(" ", microtime());
                $msecondi = substr($msec, 2, 2);

                $est = pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION);
                if (strtolower($est) == 'p7m') {
                    $Est_baseFile = $this->praLib->GetBaseExtP7MFile($allegato['FILENAME']);
// Mi trovo e accodo tutte le estensioni p7m
                    $Est_tmp = $this->praLib->GetExtP7MFile($allegato['FILENAME']);
                    $posPrimoPunto = strpos($Est_tmp, ".");
                    $delEst = substr($Est_tmp, 0, $posPrimoPunto + 1);
                    $p7mExt = str_replace($delEst, "", $Est_tmp);
//Creo l'estensione finale del file
                    $est = $Est_baseFile . "." . $p7mExt;
                }
                $filename = md5(rand() * $sec . $msecondi) . ".$est";

                if (!@copy($allegato['FILEPATH'], $percorsoTmp . "/" . $filename)) {
                    Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $allegato['FILEPATH'] . " su " . $percorsoTmp . "/" . $filename . " !");
                    return;
                }

                //Valorizzo Tabella
                $keyInc = count($this->passAlle);
                $allegatoLevel1['INFO'] = $allegato['FILEINFO'];
                $allegatoLevel1['RANDOM'] = '<span style="color:orange;">' . $filename . '</span>';
                $allegatoLevel1['NAME'] = '<span style="color:orange;">' . $allegato['FILEORIG'] . '</span>';
                $allegatoLevel1['SIZE'] = $this->praLib->formatFileSize(filesize($allegato['FILEPATH']));
                $allegatoLevel1['PROV'] = $keyInc;
                if (strtolower(pathinfo($allegato['FILEPATH'], PATHINFO_EXTENSION)) == "pdf") {
                    $allegatoLevel1['PREVIEW'] = "<span class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Importa campi aggiuntivi\"></span>";
                    $allegatoLevel1['STATO'] = "<span>Clicca per funzioni aggiuntive</span>";
                }
                $allegatoLevel1['EVIDENZIA'] = "<div class=\"ita-html\"><span class=\"ita-icon ita-icon-evidenzia-24x24 ita-colorpicker {type: 'divColor',swatches:true}\">Evidenzia Allegato</span></div>";
                $allegatoLevel1['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-24x24\">Blocca Allegato</span>";
                $allegatoLevel1['PUBBLICA'] = "<span class=\"ita-icon ita-icon-publish-24x24\">Allegato pubblicato</span>";
                $allegatoLevel1['PASPUB'] = 1;

                /*
                 * Classificazione
                 */
                $Anacla_rec = $this->praLib->GetAnacla($allegato['CLASS']);
                $allegatoLevel1['CLASSIFICAZIONE'] = $Anacla_rec['CLADES'];
                $allegatoLevel1['PASCLAS'] = $allegato['CLASS'];

                /*
                 * Destinazioni
                 */
                $strDest = $this->praLibAllegati->getStringDestinatari($allegato['DESTINAZ']);
                $allegatoLevel1['DESTINAZIONI'] = $strDest;
                $allegatoLevel1['PASDEST'] = $allegato['DESTINAZ'];

                /*
                 * Note
                 */
                $allegatoLevel1['PASNOTE'] = $allegato['NOTEALLE'];

                //Valorizzo Array
                $allegatoLevel1['PROVENIENZA'] = 'INTERNO'; //$allegato['PROVENIENZA'];
                $allegatoLevel1['FILEINFO'] = $allegato['FILEINFO'];
                $allegatoLevel1['FILEPATH'] = $percorsoTmp . "/" . $filename;
                $allegatoLevel1['FILENAME'] = $filename;
                $allegatoLevel1['FILEORIG'] = $allegato['FILEORIG'];
                $allegatoLevel1['PASUTELOG'] = App::$utente->getKey('nomeUtente');
                $allegatoLevel1['PASORADOC'] = date("H:i:s");
                $allegatoLevel1['PASDATADOC'] = date("Ymd");
                $allegatoLevel1['PASDAFIRM'] = 1;
                $allegatoLevel1['PASSHA2'] = hash_file('sha256', $allegatoLevel1['FILEPATH']);
                $allegatoLevel1['ROWID'] = 0;
                $allegatoLevel1['CDS'] = $this->praLib->getIconCds(0, $_POST[$this->nameForm . '_PROPAS']['PROFLCDS']);
                $allegatoLevel1['PASFLCDS'] = 0;
                $allegatoLevel1['PASRIS'] = $allegato['PASRIS'];
                $allegatoLevel1['RISERVATO'] = $this->praLibRiservato->getIconRiservato($allegatoLevel1['PASRIS']);
                $allegatoLevel1['level'] = 1;
                $allegatoLevel1['parent'] = "L0_" . $numLevel0;
                $allegatoLevel1['isLeaf'] = 'true';
                $this->passAlle[$keyInc] = $allegatoLevel1;
            }
        } else {
            $i = $posInterno + 1;
            $trovato = false;
            while ($trovato == false) {
                if ($i >= count($this->passAlle)) {
                    $trovato = true;
                } else {
                    if ($this->passAlle[$i]['level'] == 0) {
                        $trovato = true;
                    } else {
                        $i++;
                    }
                }
            }
            $arrayTop = array_slice($this->passAlle, 0, $i);
            $arrayDown = array_slice($this->passAlle, $i);
            //@FIXME: ATTENZIONE CODICE DUPLICATO
            foreach ($allegati as $allegato) {
                $fileOrig = $allegato['FILENAME'];
                usleep(50000); // 50 millisecondi;
                list($msec, $sec) = explode(" ", microtime());
                $msecondi = substr($msec, 2, 2);
                $est = pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION);
                $est = pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION);
                if (strtolower($est) == 'p7m') {
                    $Est_baseFile = $this->praLib->GetBaseExtP7MFile($allegato['FILENAME']);
// Mi trovo e accodo tutte le estensioni p7m
                    $Est_tmp = $this->praLib->GetExtP7MFile($allegato['FILENAME']);
                    $posPrimoPunto = strpos($Est_tmp, ".");
                    $delEst = substr($Est_tmp, 0, $posPrimoPunto + 1);
                    $p7mExt = str_replace($delEst, "", $Est_tmp);
//Creo l'estensione finale del file
                    $est = $Est_baseFile . "." . $p7mExt;
                }
                $filename = md5(rand() * $sec . $msecondi) . ".$est";
                if (!@copy($allegato['FILEPATH'], $percorsoTmp . "/" . $filename)) {
                    Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $allegato['FILEPATH'] . " su " . $percorsoTmp . "/" . $filename . " !");
                    return;
                }

                $i = count($this->passAlle);
                $allegatoLevel1 = array();
                //Valorizzo Tabella
                $allegatoLevel1['PROV'] = $i;
                $allegatoLevel1['RANDOM'] = '<span style="color:orange;">' . $filename . '</span>';
                $allegatoLevel1['NAME'] = '<span style="color:orange;">' . $allegato['FILEORIG'] . '</span>';
                $allegatoLevel1['INFO'] = $allegato['FILEINFO'];
                $allegatoLevel1['SIZE'] = $this->praLib->formatFileSize(filesize($allegato['FILEPATH']));
                if (strtolower(pathinfo($allegato['FILEPATH'], PATHINFO_EXTENSION)) == "pdf") {
                    $allegatoLevel1['PREVIEW'] = "<span class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Importa campi aggiuntivi\"></span>";
                    $allegatoLevel1['STATO'] = "<span>Clicca per funzioni aggiuntive</span>";
                }
                $allegatoLevel1['EVIDENZIA'] = "<div class=\"ita-html\"><span class=\"ita-icon ita-icon-evidenzia-24x24 ita-colorpicker {type: 'divColor',swatches:true}\">Evidenzia Allegato</span></div>";
                $allegatoLevel1['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-24x24\">Blocca Allegato</span>";
                $allegatoLevel1['PUBBLICA'] = "<span class=\"ita-icon ita-icon-publish-24x24\">Allegato pubblicato</span>";
                $allegatoLevel1['PASPUB'] = 1;

                /*
                 * Classificazione
                 */
                $Anacla_rec = $this->praLib->GetAnacla($allegato['CLASS']);
                $allegatoLevel1['CLASSIFICAZIONE'] = $Anacla_rec['CLADES'];
                $allegatoLevel1['PASCLAS'] = $allegato['CLASS'];

                /*
                 * Destinazioni
                 */
                $strDest = $this->praLibAllegati->getStringDestinatari($allegato['DESTINAZ']);
                $allegatoLevel1['DESTINAZIONI'] = $strDest;
                $allegatoLevel1['PASDEST'] = $allegato['DESTINAZ'];

                /*
                 * Note
                 */
                $allegatoLevel1['PASNOTE'] = $allegato['NOTEALLE'];

                //Valorizzo Array
                $allegatoLevel1['PROVENIENZA'] = 'INTERNO';
                $allegatoLevel1['FILEINFO'] = $allegato['FILEINFO'];
                $allegatoLevel1['FILEPATH'] = $percorsoTmp . "/" . $filename;
                $allegatoLevel1['FILENAME'] = $filename;
                $allegatoLevel1['FILEORIG'] = $allegato['FILEORIG'];
                $allegatoLevel1['PASUTELOG'] = App::$utente->getKey('nomeUtente');
                $allegatoLevel1['PASORADOC'] = date("H:i:s");
                $allegatoLevel1['PASDATADOC'] = date("Ymd");
                $allegatoLevel1['PASDAFIRM'] = 1;
                $allegatoLevel1['PASSHA2'] = hash_file('sha256', $allegatoLevel1['FILEPATH']);
                $allegatoLevel1['ROWID'] = 0;
                $allegatoLevel1['CDS'] = $this->praLib->getIconCds(0, $_POST[$this->nameForm . '_PROPAS']['PROFLCDS']);
                $allegatoLevel1['PASFLCDS'] = 0;
                $allegatoLevel1['PASRIS'] = $allegato['PASRIS'];
                $allegatoLevel1['RISERVATO'] = $this->praLibRiservato->getIconRiservato($allegatoLevel1['PASRIS']);
                $allegatoLevel1['level'] = 1;
                $allegatoLevel1['parent'] = $parent;
                $allegatoLevel1['isLeaf'] = 'true';
                $this->passAlle[$i] = $allegatoLevel1;

                $arrayTop[$i] = $allegatoLevel1;
                $i++;
                foreach ($arrayDown as $chiave => $recordDown) {
                    if ($recordDown['level'] == 1) {
                        $arrayDown[$chiave]['PROV'] = $recordDown['PROV'] + 1; //$nAggiunti;
                    }
                }
                $this->passAlle = array_merge($arrayTop, $arrayDown);
            }
        }
        $this->ContaSizeAllegati($this->passAlle);
        $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');
        $this->bloccoAllegatiRiservati();
        $this->BloccaDescAllegati();
        return;
    }

    public function protocollaPartenza() {
        $propas_rec = $this->praLib->GetPropas($_POST[$this->nameForm . '_PROPAS']['ROWID'], 'rowid');
        if (!$propas_rec) {
// Modifica per consentire il recupero dal passo dopo la ricerca anagrafica via Web Service
            $propas_rec = $this->praLib->GetPropas($this->keyPasso, 'propak');
        }
        $proges_rec = $this->praLib->GetProges($propas_rec['PRONUM']);
        if ($proges_rec['GESSPA'] != 0) {
            $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
            $denomComune = $anaspa_rec["SPADES"];
        } else {
            $anatsp_rec = $this->praLib->GetAnatsp($proges_rec['GESTSP']);
            $denomComune = $anatsp_rec["TSPDES"];
        }
        $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
        $pramitDest_tab = $this->praLib->GetPraDestinatari($propas_rec['PROPAK'], 'codice', true);
        $oggetto = $this->praLib->GetOggettoProtPartenza($this->currGesnum, $this->keyPasso);
        $elementi['tipo'] = 'P';
        $elementi['dati']['Oggetto'] = $oggetto;
        $elementi['dati']['DataArrivo'] = $pracomP_rec['COMDAT'];
        $elementi['dati']['DenomComune'] = $denomComune;

        /*
         * Per retro compatibilità nel caso ci sia un vecchio proPaleo.class.php
         */
        $elementi['dati']['MittDest']['Denominazione'] = $pramitDest_tab[0]['NOME'];
        $elementi['dati']['MittDest']['Indirizzo'] = $pramitDest_tab[0]['INDIRIZZO'];
        $elementi['dati']['MittDest']['CAP'] = $pramitDest_tab[0]['CAP'];
        $elementi['dati']['MittDest']['Citta'] = $pramitDest_tab[0]['COMUNE'];
        $elementi['dati']['MittDest']['Provincia'] = $pramitDest_tab[0]['PROVINCIA'];
        $elementi['dati']['MittDest']['Email'] = $pramitDest_tab[0]['MAIL'];
        $elementi['dati']['MittDest']['CF'] = $pramitDest_tab[0]['FISCALE'];

        /*
         * Nuovo tag MITTENTE dal 01/12/2016 (Principalmente per E-Lios)-->Prende i dati dello sportello on-line
         */
        $anatsp_rec = $this->praLib->GetAnatsp($proges_rec['GESTSP']);
        $elementi['dati']['Mittente']['Denominazione'] = $anatsp_rec['TSPDEN'];
        $elementi['dati']['Mittente']['Indirizzo'] = $anatsp_rec['TSPIND'] . " " . $anatsp_rec['TSPNCI'];
        $elementi['dati']['Mittente']['CAP'] = $anatsp_rec['TSPCAP'];
        $elementi['dati']['Mittente']['Citta'] = $anatsp_rec['TSPCOM'];
        $elementi['dati']['Mittente']['Provincia'] = $anatsp_rec['TSPPRO'];
        $elementi['dati']['Mittente']['Email'] = $anatsp_rec['TSPPEC'];
        $elementi['dati']['Mittente']['CF'] = "";

        /*
         * Nuova versione destinatari multipli
         */
        $elementi['dati']['destinatari'] = array();
        foreach ($pramitDest_tab as $pramitDest_rec) {
            $destinatario = array();
            $destinatario['Denominazione'] = $pramitDest_rec['NOME'];
            $destinatario['Indirizzo'] = $pramitDest_rec['INDIRIZZO'];
            $destinatario['CAP'] = $pramitDest_rec['CAP'];
            $destinatario['Citta'] = $pramitDest_rec['COMUNE'];
            $destinatario['Provincia'] = $pramitDest_rec['PROVINCIA'];
            $destinatario['Email'] = $pramitDest_rec['MAIL'];
            $destinatario['CF'] = $pramitDest_rec['FISCALE'];
            $elementi['dati']['destinatari'][] = $destinatario;
        }

        $elementi['dati']['NumeroAntecedente'] = substr($proges_rec['GESNPR'], 4);
        $elementi['dati']['AnnoAntecedente'] = substr($proges_rec['GESNPR'], 0, 4);
        if ($proges_rec['GESMETA']) {
            $metaDati = unserialize($proges_rec['GESMETA']);
            $elementi['dati']['NumeroAntecedente'] = $metaDati['DatiProtocollazione']['proNum']['value'];
            $elementi['dati']['AnnoAntecedente'] = $metaDati['DatiProtocollazione']['Anno']['value'];
        }
        $elementi['dati']['TipoAntecedente'] = "A"; //Tipo protocollo pratica 
        $classificazione = $this->praLib->GetClassificazioneProtocollazione($proges_rec['GESPRO'], $proges_rec['GESNUM']);
        $elementi['dati']['Classificazione'] = $classificazione;
        $elementi['dati']['MetaDati'] = unserialize($proges_rec['GESMETA']);
        $UfficioCarico = $this->praLib->GetUfficioCaricoProtocollazione($proges_rec);
        $elementi['dati']['InCaricoA'] = $UfficioCarico;
        $elementi['dati']['MittenteInterno'] = $UfficioCarico;
        $TipoDocumentoProtocollo = $this->praLib->GetTipoDocumentoProtocollazioneEndoPar($proges_rec);
        $elementi['dati']['TipoDocumento'] = $TipoDocumentoProtocollo;
        //
        $firmatario = $this->praLib->setFirmatarioProtocolloDaSportello($proges_rec);
        if ($firmatario) {
            $elementi['dati']['Firmatario'] = $firmatario;
        }

        $destinatario = $this->setDestinatarioProtocollo($proges_rec['GESRES']);
        $elementi['mittenti'][0] = $destinatario;
        $uffici = $this->setUfficiProtocollo($destinatario['CodiceDestinatario']);
        $elementi['uffici'] = $uffici;
        //
        $praLibVar = new praLibVariabili();
        $Filent_recFasc = $this->praLib->GetFilent(30);
        $oggettoFasc = $this->praLib->GetStringDecode($praLibVar->getVariabiliPratica(true)->getAllData(), $Filent_recFasc['FILVAL']);
        $elementi['dati']['Fascicolazione']['Oggetto'] = $oggettoFasc;
//
        if ($proges_rec['GESSPA']) {
            $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
            $elementi['dati']['Aggregato']['Codice'] = $proges_rec['GESSPA'];
            $elementi['dati']['Aggregato']['CodAmm'] = $anaspa_rec['SPAAMMIPA'];
            $elementi['dati']['Aggregato']['CodAoo'] = $anaspa_rec['SPAAOO'];
        }
        return $elementi;
    }

    public function protocollaArrivo() {
        $propas_rec = $this->praLib->GetPropas($_POST[$this->nameForm . '_PROPAS']['ROWID'], 'rowid');
//
// Modifica per consentire il recupero dal passo dopo la ricerca anagrafica via Web Service
//
        if (!$propas_rec) {
            $propas_rec = $this->praLib->GetPropas($this->keyPasso, 'propak');
        }
        $proges_rec = $this->praLib->GetProges($propas_rec['PRONUM']);
        if ($proges_rec['GESSPA'] != 0) {
            $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
            $denomComune = $anaspa_rec["SPADES"];
        } else {
            $anatsp_rec = $this->praLib->GetAnatsp($proges_rec['GESTSP']);
            $denomComune = $anatsp_rec["TSPDES"];
        }
        $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
        $pracomA_rec = $this->praLib->GetPracomA($propas_rec['PROPAK']);
        $elementi['tipo'] = 'A';

        $praLibVar = new praLibVariabili();
        $Filent_rec = $this->praLib->GetFilent(5);
        $praLibVar->setCodicePratica($this->currGesnum);
        $praLibVar->setChiavePasso($this->keyPasso);
        $dictionaryValues = $praLibVar->getVariabiliPratica()->getAllData();
        $oggetto = $this->praLib->GetStringDecode($dictionaryValues, $Filent_rec['FILVAL']);
        $elementi['tipo'] = 'A';
        $elementi['dati']['Oggetto'] = $oggetto;
        $elementi['dati']['DenomComune'] = $denomComune;
        $elementi['dati']['DataArrivo'] = $pracomA_rec['COMDAT'];
        $elementi['dati']['ChiavePasso'] = $this->keyPasso;
        $elementi['dati']['MittDest']['Denominazione'] = $pracomA_rec['COMNOM'];
        $elementi['dati']['MittDest']['Indirizzo'] = $pracomA_rec['COMIND'];
        $elementi['dati']['MittDest']['CAP'] = $pracomA_rec['COMCAP'];
        $elementi['dati']['MittDest']['Citta'] = $pracomA_rec['COMCIT'];
        $elementi['dati']['MittDest']['Provincia'] = $pracomA_rec['COMPRO'];
        if ($pracomP_rec['COMPRT']) {
            $elementi['dati']['NumeroAntecedente'] = substr($pracomP_rec['COMPRT'], 4);
            $elementi['dati']['AnnoAntecedente'] = substr($pracomP_rec['COMPRT'], 0, 4);
            if ($pracomP_rec['COMMETA']) {
                $metaDati = unserialize($pracomP_rec['COMMETA']);
                $elementi['dati']['NumeroAntecedente'] = $metaDati['DatiProtocollazione']['proNum']['value'];
                $elementi['dati']['AnnoAntecedente'] = $metaDati['DatiProtocollazione']['Anno']['value'];
            }
            $elementi['dati']['TipoAntecedente'] = "P"; //Tipo protocollo passo partenza 
        } else {
            $elementi['dati']['NumeroAntecedente'] = substr($proges_rec['GESNPR'], 4);
            $elementi['dati']['AnnoAntecedente'] = substr($proges_rec['GESNPR'], 0, 4);
            if ($proges_rec['GESMETA']) {
                $metaDati = unserialize($proges_rec['GESMETA']);
                $elementi['dati']['NumeroAntecedente'] = $metaDati['DatiProtocollazione']['proNum']['value'];
                $elementi['dati']['AnnoAntecedente'] = $metaDati['DatiProtocollazione']['Anno']['value'];
            }
            $elementi['dati']['TipoAntecedente'] = "A"; //Tipo protocollo pratica 
        }
        $elementi['dati']['MittDest']['Email'] = $pracomA_rec['COMMLD'];
        $elementi['dati']['MittDest']['CF'] = $pracomA_rec['COMFIS'];

        $elementi['dati']['destinatari'] = array();
        $elementi['dati']['destinatari'][0]['Denominazione'] = $pracomA_rec['COMNOM'];
        $elementi['dati']['destinatari'][0]['Indirizzo'] = $pracomA_rec['COMIND'];
        $elementi['dati']['destinatari'][0]['CAP'] = $pracomA_rec['COMCAP'];
        $elementi['dati']['destinatari'][0]['Citta'] = $pracomA_rec['COMCIT'];
        $elementi['dati']['destinatari'][0]['Provincia'] = $pracomA_rec['COMPRO'];
        $elementi['dati']['destinatari'][0]['Email'] = $pracomA_rec['COMMLD'];
        $elementi['dati']['destinatari'][0]['CF'] = $pracomA_rec['COMFIS'];
//
        $destinatario = $this->setDestinatarioProtocollo($proges_rec['GESRES']);
        $elementi['destinatari'][0] = $destinatario;
        $uffici = $this->setUfficiProtocollo($destinatario['CodiceDestinatario']);
        $elementi['uffici'] = $uffici;

        $classificazione = $this->praLib->GetClassificazioneProtocollazione($proges_rec['GESPRO'], $proges_rec['GESNUM']);
        $elementi['dati']['Classificazione'] = $classificazione;
        $elementi['dati']['MetaDati'] = unserialize($proges_rec['GESMETA']);
        $UfficioCarico = $this->praLib->GetUfficioCaricoProtocollazione($proges_rec);
        $elementi['dati']['InCaricoA'] = $UfficioCarico;
        $TipoDocumentoProtocollo = $this->praLib->GetTipoDocumentoProtocollazioneEndoArr($proges_rec);
        $elementi['dati']['TipoDocumento'] = $TipoDocumentoProtocollo;
//
        $praLibVar = new praLibVariabili();
        $Filent_recFasc = $this->praLib->GetFilent(30);
        $oggettoFasc = $this->praLib->GetStringDecode($praLibVar->getVariabiliPratica(true)->getAllData(), $Filent_recFasc['FILVAL']);
        $elementi['dati']['Fascicolazione']['Oggetto'] = $oggettoFasc;
//
        if ($proges_rec['GESSPA']) {
            $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
            $elementi['dati']['Aggregato']['Codice'] = $proges_rec['GESSPA'];
            $elementi['dati']['Aggregato']['CodAmm'] = $anaspa_rec['SPAAMMIPA'];
            $elementi['dati']['Aggregato']['CodAoo'] = $anaspa_rec['SPAAOO'];
        }
        return $elementi;
    }

    function cleanTestibase($arrayAllegati) {//, $keyPasso) {
        $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
        foreach ($arrayAllegati as $key => $allegato) {
            $arrayProt = array();
            $numProt = "";
            if ($allegato['PROTOCOLLO']) {
                $arrayProt = explode(" ", $allegato['PROTOCOLLO']);
                $numProt = $arrayProt[0];
            }
            if ($pracomP_rec['COMPRT'] || $pracomP_rec['COMIDDOC']) {
                /*
                 * Se c'è il protocollo in partenza, scarto gli allegati bloccati con num di protocollo diverso dalla partenza
                 */
                if ($numProt) {
                    if ($pracomP_rec['COMPRT'] != $numProt) {
                        unset($arrayAllegati[$key]);
                    }
                }
            } else {
                /*
                 * Se non c'è il protocollo in partenza, scarto gli allegati bloccati col num di protocollo diverso dall'arrivo
                 */
                if ($numProt) {
                    unset($arrayAllegati[$key]);
                }
            }
            $pramPath = pathinfo($allegato['FILEPATH'], PATHINFO_DIRNAME);
            $extTesto = strtolower(pathinfo($allegato['FILEPATH'], PATHINFO_EXTENSION));
            if ($extTesto == 'xhtml' || $extTesto == 'docx') {
                $ext = false;
                if (file_exists($pramPath . "/" . pathinfo($allegato['FILEPATH'], PATHINFO_FILENAME) . ".pdf.p7m")) {
                    $ext = "pdf.p7m";
                } else if (file_exists($pramPath . "/" . pathinfo($allegato['FILEPATH'], PATHINFO_FILENAME) . ".pdf")) {
                    $ext = "pdf";
                } else if ($extTesto == 'docx') {
                    $ext = $extTesto;
                }
                if ($ext) {
                    $arrayFilepdf = array();
                    $arrayFilepdf['PROV'] = 2;
                    $arrayFilepdf['RANDOM'] = pathinfo($allegato['FILEPATH'], PATHINFO_FILENAME) . ".$ext";
                    $arrayFilepdf['NAME'] = $allegato['NAME'];
                    $arrayFilepdf['INFO'] = $allegato['INFO'];
                    $arrayFilepdf['SIZE'] = "";
                    $arrayFilepdf['PREVIEW'] = "";
                    $arrayFilepdf['STATO'] = "";
                    $arrayFilepdf['FILEINFO'] = $allegato['INFO'];
                    $arrayFilepdf['FILEPATH'] = $pramPath . "/" . pathinfo($allegato['FILEPATH'], PATHINFO_FILENAME) . ".$ext";
                    $arrayFilepdf['FILENAME'] = pathinfo($allegato['FILEPATH'], PATHINFO_FILENAME) . ".$ext";
                    $arrayFilepdf['PROVENIENZA'] = 'TESTOBASE';
                    $arrayFilepdf['ROWID'] = 0;
                    $arrayFilepdf['level'] = 1;
                    $arrayFilepdf['parent'] = "";
                    $arrayFilepdf['isLeaf'] = 'true';
                    $arrayAllegati[] = $arrayFilepdf;
                }
                unset($arrayAllegati[$key]);
            }
        }
        return $arrayAllegati;
    }

    private function elencaFiles($dirname) {
        $arrayfiles = Array();
        if (file_exists($dirname)) {
            $handle = opendir($dirname);
            while (false !== ($file = readdir($handle))) {
                if (is_file($dirname . "/" . $file)) {
                    array_push($arrayfiles, $file);
                }
            }
            closedir($handle);
        }
        sort($arrayfiles);  //ordinamento alfabetico
        return $arrayfiles;
    }

    public function ProtocolloICCSA() {

        if ($_POST[$this->nameForm . '_PROTRIC_MITTENTE'] == '') {
//$elementi = $this->protocollaPartenza();
            $elementi = $this->protocollaArrivo();
            $propas_rowid = $_POST[$this->nameForm . '_PROPAS']['ROWID'];
            $model = 'proHWS.class';
//$model = 'proGest';
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//gestione documenti allegati
            $praFascicolo = new praFascicolo($this->currGesnum);
            $praFascicolo->setChiavePasso($this->keyPasso);
            $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione("WSPU", true, "A");

//controllo che ci siano gli allegati
            if ($arrayDoc) {
//$elementi['dati']['Principale'] = $arrayDoc['Principale'];
                $elementi['dati']['Allegati'] = $arrayDoc['Allegati'];
//fine documenti allegati
            }
//qui parte relativa alla ricerca anagrafe
            if (isset($this->idCorrispondente) && $this->idCorrispondente != '') {
                $elementi['dati']['corrispondente'] = $this->idCorrispondente;
//una volta settato il corrispondente lo svuoto per essere pronto per la prossima protocollazione
                $this->idCorrispondente = '';
            } else {
                /**
                 * se non c'è il codice del corrispondente non proseguo con la protocollazione
                 * faccio la ricerca del corrispondente
                 * dalla fase di ricerca si rilancia la protocollazione col codice settato
                 */
                $this->ControllaAnagrafeICCS($elementi, 'A');
                return;
            }
//fine parte relativa alla ricerca anagrafe

            $proHWS = new proHWS();
            $elementi['dati']['ufficio'] = $this->praLib->getUfficioHWS($_POST[$this->nameForm . '_PROGES']['GESNUM']);
            $valore = $proHWS->protocollazioneIngresso($elementi);
            if ($valore['Status'] == "0") {
                $propas_rec = $this->praLib->GetPropas($propas_rowid, 'rowid');
                if (!$propas_rec) {
                    $propas_rec = $this->praLib->GetPropas($this->keyPasso, 'propak');
                }
//$pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                $pracomA_rec = $this->praLib->GetPracomA($propas_rec['PROPAK']);
//                $pracomA_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK']
//                                . "' AND COMTIP='A' AND COMRIF='" . $pracomP_rec['ROWID'] . "'", false);
//Out::valore($this->nameForm . '_PROTRIC_MITTENTE', substr($valore['RetValue']['DatiProtocollazione']['proNum']['value'], 4));
                Out::valore($this->nameForm . '_PROTRIC_MITTENTE', $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
                Out::valore($this->nameForm . '_DATAPROT_MITTENTE', $this->workDate);
                $pracom_rec = array();
//salvo i metadati
                $meta = array();
                $meta['Arrivo'] = $valore['RetValue']; //in previsione di fare eventualmente una distinzione tra dati salvati in partenza o in arrivo
                $pracom_rec['COMMETA'] = serialize($valore['RetValue']);
                $pracom_rec['ROWID'] = $pracomA_rec['ROWID'];
                $anno = date("Y");
                $pracom_rec['COMPRT'] = $anno . $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                $pracom_rec['COMDPR'] = $this->workDate;
                $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                    Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                    return;
                }
                $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "A");
                Out::msgInfo("OK", "Protocollazione avvenuta con successo al n. " . $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
                $this->Dettaglio($propas_rec['PROPAK']);
//$this->switchIconeProtocolloA($pracom_rec['COMPRT'], $valore['RetValue']);
            } else {
                Out::msgStop("Errore in Protocollazione", $valore['Message']);
            }
        }
    }

    public function ProtocolloICCSP($idAllegatiScelti = array()) {
        $propas_rec = $this->praLib->GetPropas($this->keyPasso);
        $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);

//        if ($_POST[$this->nameForm . '_PROTRIC_DESTINATARIO'] == '') {
        if ($pracomP_rec['COMPRT'] == '') {
            $elementi = $this->protocollaPartenza();

            $model = 'proHWS.class';

            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//gestione documenti allegati
            $praFascicolo = new praFascicolo($this->currGesnum);
            $praFascicolo->setChiavePasso($this->keyPasso);
            foreach ($idAllegatiScelti as $id) {
                $idScelti[] = substr($id, 1);
            }
            if (!$idAllegatiScelti) {
                $idAllegatiScelti = "NO";
            }
            $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione("WSPU", true, "P", $idScelti);

            if ($arrayDoc) {
                $elementi['dati']['Allegati'] = $arrayDoc['Allegati'];
            }
//qui parte relativa alla ricerca anagrafe
            if (isset($this->idCorrispondente) && $this->idCorrispondente != '') {
                $elementi['dati']['corrispondente'] = $this->idCorrispondente;
//una volta settato il corrispondente lo svuoto per essere pronto per la prossima protocollazione
                $this->idCorrispondente = '';
            } else {
                /**
                 * se non c'è il codice del corrispondente non proseguo con la protocollazione
                 * faccio la ricerca del corrispondente
                 * dalla fase di ricerca si rilancia la protocollazione col codice settato
                 */
                $this->ControllaAnagrafeICCS($elementi, 'P');
                return;
            }

            $proHWS = new proHWS();
            $elementi['dati']['ufficio'] = $this->praLib->getUfficioHWS($_POST[$this->nameForm . '_PROGES']['GESNUM']);
            $valore = $proHWS->protocollazioneUscita($elementi);


            if ($valore['Status'] == "0") {
                if (!$propas_rec) {
                    $propas_rec = $this->praLib->GetPropas($this->keyPasso, 'propak');
                }
                $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
                Out::valore($this->nameForm . '_DATAPROT_DESTINATARIO', $this->workDate);
//salvo i metadati
//                $meta = array();
                $pracom_rec = array();
                $anno = date("Y");
                $pracom_rec['COMMETA'] = serialize($valore['RetValue']);
                $pracom_rec['ROWID'] = $pracomP_rec['ROWID'];
                $pracom_rec['COMPRT'] = $anno . $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                $pracom_rec['COMDPR'] = $this->workDate;
                $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                    Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                    return;
                }
                Out::msgInfo("OK", "Protocollazione avvenuta con successo al n. " . $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
                $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "P");
                $this->Dettaglio($propas_rec['PROPAK']);
            } else {
                Out::msgStop("Errore in Protocollazione Partenza", $valore['Message']);
            }
        }
    }

    function ControllaAnagrafeICCS($elementi = array(), $tipo = 'P') {
        if (!$elementi['dati']) {
            Out::msgStop("Attenzione", "Specificare dei parametri per la protocollazione");
            return false;
        }
        $model = 'proHWS.class';
        if ($tipo == 'P') {
            $elementi = $this->protocollaPartenza();
        } else {
            $elementi = $this->protocollaArrivo();
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
//se non ci sono record trovati procedo con l'inserimento in anagrafica
        if (!$ritorno['RetValue']) {
            $this->inserisciRubricaWS($tipo);
            return;
        }
        if (count($ritorno['RetValue']) == 1) {
//se è stato trovato un solo record
            $this->datiRubricaWS = $ritorno['RetValue'][0];
            if ($this->datiRubricaWS['codiceFiscale'] == $elementi['dati']['MittDest']['CF'] && $this->datiRubricaWS['indirizzo'] == $elementi['dati']['MittDest']['Indirizzo'] && $this->datiRubricaWS['citta'] == $elementi['dati']['MittDest']['Citta']) {
//se i dati corrispondono...
                $this->idCorrispondente = $this->datiRubricaWS['codice'];
                if ($tipo == 'P') {
                    $this->ProtocolloICCSP();
                } else {
                    $this->ProtocolloICCSA();
                }
            } else {
//se i dati non corrispondono del tutto...
                if ($tipo == 'P') {
                    Out::msgQuestion("Selezione", "&Egrave; stata trovata la seguente anagrafica: <br>
                Nominativo: " . $ritorno['RetValue'][0]['nome'] . " " . $ritorno['RetValue'][0]['cognome'] . "<br>
                Rag. Sociale: " . $ritorno['RetValue'][0]['ragioneSociale'] . "<br>
                Cod. Fisc.: " . $ritorno['RetValue'][0]['codiceFiscale'] . "<br>
                P. Iva: " . $ritorno['RetValue'][0]['partitaIva'] . "<br>
                Indirizzo: " . $ritorno['RetValue'][0]['indirizzo'] . "<br>
                " . $ritorno['RetValue'][0]['cap'] . " " . $ritorno['RetValue'][0]['citta'] . " (" . $ritorno['RetValue'][0]['prov'] . ")<br>
                Scegliere L'opzione desiderata.", array(
                        'F8-Inserisci Nuovo' => array('id' => $this->nameForm . '_InserisciRubricaWSP', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Conferma Selezione' => array('id' => $this->nameForm . '_ConfermaRubricaWSP', 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                    );
                } else {
                    Out::msgQuestion("Selezione", "&Egrave; stata trovata la seguente anagrafica: <br>
                Nominativo: " . $ritorno['RetValue'][0]['nome'] . " " . $ritorno['RetValue'][0]['cognome'] . "<br>
                Rag. Sociale: " . $ritorno['RetValue'][0]['ragioneSociale'] . "<br>
                Cod. Fisc.: " . $ritorno['RetValue'][0]['codiceFiscale'] . "<br>
                P. Iva: " . $ritorno['RetValue'][0]['partitaIva'] . "<br>
                Indirizzo: " . $ritorno['RetValue'][0]['indirizzo'] . "<br>
                " . $ritorno['RetValue'][0]['cap'] . " " . $ritorno['RetValue'][0]['citta'] . " (" . $ritorno['RetValue'][0]['prov'] . ")<br>
                Scegliere L'opzione desiderata.", array(
                        'F8-Inserisci Nuovo' => array('id' => $this->nameForm . '_InserisciRubricaWSA', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Conferma Selezione' => array('id' => $this->nameForm . '_ConfermaRubricaWSA', 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                    );
                }
            }
        } else {
//se invece ci sono più record
            praRic::praRubricaWS($ritorno['RetValue'], array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), 'returnRubricaWS' . $tipo);
        }

        /**
         * fine gestione del risultato della ricerca
         */
    }

    public function inserisciRubricaWS($tipo = 'P') {
        $model = 'proHWS.class';
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $proHWS = new proHWS();
        if ($tipo == 'P') {
            $elementi = $this->protocollaPartenza();
        } else {
            $elementi = $this->protocollaArrivo();
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
//se non ci sono record trovati procedo con l'inserimento in anagrafica
        if ($ritorno['Status'] == '0') {
            $this->idCorrispondente = $ritorno['RetValue'][0]['codice'];
            if ($tipo == 'P') {
                $this->ProtocolloICCSP();
            } else {
                $this->ProtocolloICCSA();
            }
        } else {
            
        }
        /**
         * fine gestione dell'inserimento in rubrica
         */
        return;
    }

    private function caricaArrivoDaMail($dati) {
        $_POST['datiMail'] = $dati;
        foreach ($_POST['datiMail']['ELENCOALLEGATI'] as $key => $allegato) {
            $_POST['datiMail']['ELENCOALLEGATI'][$key]['FILENAME'] = $allegato['FileName'];
            $_POST['datiMail']['ELENCOALLEGATI'][$key]['DATAFILE'] = $allegato['DataFile'];
        }
        $this->CaricaDaPec();
        $email = $dati['MITTENTE'];
        $note = $dati['NOTE'];
        Out::valore($this->nameForm . '_NOTE_MITTENTE', $note);
        Out::tabSelect($this->nameForm . '_tabProcedimento', $this->nameForm . '_paneCaratteristiche');
        if (trim($email) != "") {
            Out::tabSelect($this->nameForm . '_tabProcedimento', $this->nameForm . '_paneCom');
            $anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE
                                                              DESNUM='$this->currGesnum' AND 
                                                              (" . $this->PRAM_DB->strLower('DESEMA') . " = '" . strtolower($email) . "' OR " . $this->PRAM_DB->strLower('DESPEC') . " = '" . strtolower($email) . "')", false);
            if ($anades_rec) {
                Out::valore($this->nameForm . '_DESC_MITTENTE', substr($anades_rec['DESNOM'], 0, 59));
                $emailOut = ($anades_rec['DESPEC']) ? $anades_rec['DESPEC'] : $anades_rec['DESEMA'];
                Out::valore($this->nameForm . '_EMAIL_MITTENTE', $emailOut);
                Out::valore($this->nameForm . '_CODFISC_MITTENTE', $anades_rec['DESFIS']);
            } else {
                $anamed_tab = $this->proLib->getGenericTab("SELECT * FROM ANAMED WHERE MEDEMA='$email' AND MEDANN=0");
                if (count($anamed_tab) == 1) {
                    $this->DecodAnamedComA($anamed_tab[0]['ROWID'], 'rowid', "si", false);
                } elseif (count($anamed_tab) > 1) {
                    $msgDetail = "La mail <b>$email</b> è stata associata ai seguenti soggetti.<br>Selezionarne uno per proseguire.";
                    proRic::proRicAnamed(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), " WHERE MEDEMA='$email'", "", "assegnaAPasso", "returnanamed", "", $msgDetail);
                } else {
                    Out::valore($this->nameForm . '_DESC_MITTENTE', substr($email, 0, 59));
                    Out::valore($this->nameForm . '_EMAIL_MITTENTE', $email);
                }
//                $anamed_rec = $this->proLib->getGenericTab("SELECT * FROM ANAMED WHERE MEDEMA='$email' AND MEDANN=0", false);
//                if ($anamed_rec) {
//                    $this->DecodAnamedComA($anamed_rec['ROWID'], 'rowid', "si", false);
//                } else {
//                    Out::valore($this->nameForm . '_DESC_MITTENTE', substr($email, 0, 59));
//                    Out::valore($this->nameForm . '_EMAIL_MITTENTE', $email);
//                }
            }
        }
    }

    public function sincPlanimetria($file_src) {
        $sourceExtension = strtolower(pathinfo($file_src, PATHINFO_EXTENSION));
        switch ($sourceExtension) {
            case 'pdf':
                $fileTif = pathinfo($file_src, PATHINFO_DIRNAME) . "/" . pathinfo($file_src, PATHINFO_FILENAME) . ".tif";
                if (!file_exists($fileTif)) {
                    $comando = "convert -density 200 $file_src $fileTif";
                    exec($comando, $output, $return_var);
                }
                break;
            case 'tif':
                $fileTif = $file_src;
                break;
            default:
                return false;
        }

        $file_dest = pathinfo($fileTif, PATHINFO_BASENAME);
//
//Copio il file nella path per GIS
//
        $Filent_rec_8_Path = $this->praLib->GetFilent(8);
        $ftp_param = trim($Filent_rec_8_Path['FILVAL']);
        if (strpos($Filent_rec_8_Path['FILVAL'], "ftp://") === 0) {
            list($skip, $ftpurl1) = explode("ftp://", $Filent_rec_8_Path['FILVAL']);
            list($ftp_user, $ftpurl2) = explode(":", $ftpurl1, 2);
            list($ftp_password, $ftpurl3) = explode("@", $ftpurl2, 2);
            list($ftp_host, $ftp_path) = explode("/", $ftpurl3, 2);
            $conn_id = ftp_connect($ftp_host, 21, 60);
            if (!$conn_id) {
                Out::msgStop("Attenzione!!!", "Impossibile connettersi all'host.<br>Il time-out è scaduto.<br>Contattare l'amministratore di rete.");
                return false;
            }
            if (!@ftp_login($conn_id, $ftp_user, $ftp_password)) {
                Out::msgStop("Attenzione!!!", "Impossibile connettersi\n");
                return false;
            }

            $ftp_list = ftp_nlist($conn_id, $file_dest);
            if (!$ftp_list) {
                if (!@ftp_put($conn_id, $file_dest, $fileTif, FTP_BINARY)) {
                    ftp_close($conn_id);
                    Out::msgInfo("Attenzione", "File $file_dest non trasferito al misuratore.");
                }
                ftp_close($conn_id);
            }
            sleep(2);
        } else {
            if (!file_exists($Filent_rec_8_Path['FILVAL'] . "/$file_dest")) {
                if (!@copy($file_src, $Filent_rec_8_Path['FILVAL'] . "/$file_dest")) {
                    Out::msgStop("Misuratore Planimetrie", "Errore nella copia del file $file_dest in " . $Filent_rec_8_Path['FILVAL']);
                    return false;
                }
            }
        }
        return $file_dest;
    }

    private function SegnaturaAllegati($rowidScelti, $param) {
        foreach ($rowidScelti as $id) {
            $pasdoc_rec = $this->praLib->GetPasdoc($id, "ROWID");
            $pramPath = $this->praLib->SetDirectoryPratiche(substr($this->keyPasso, 0, 4), $this->keyPasso, "PASSO", false);
            $fileInput = $pasdoc_rec['PASFIL'];
            $extAllegato = strtolower(pathinfo($pasdoc_rec['PASFIL'], PATHINFO_EXTENSION));
            if ($extAllegato == "xhtml" || $extAllegato == 'docx') {
                $fileInput = pathinfo($pasdoc_rec['PASFIL'], PATHINFO_FILENAME) . ".pdf";
            }
            if (strtolower(pathinfo($fileInput, PATHINFO_EXTENSION)) != "pdf") {
                continue;
            }
            $segnatura = $this->praLibAllegati->GetMarcatureString($param, $this->currGesnum, $id);
            $output = $this->praLibAllegati->ComponiPDFconSegnatura($segnatura, $pramPath . "/" . $fileInput);
            if (!$output) {
                Out::msgStop("Marcatura Allegato", $this->praLibAllegati->getErrMessage());
                return false;
//Out::msgStop("Attenzione!", "<br><br>Marcatura del documento " . $pasdoc_rec['PASNAME'] . " impossibile.<br>Allego il documento senza marcatura.");
            }
        }
        return true;
    }

    public function bloccaAllegati($chiave, $rowidArr = array(), $tipo = "A") {
        $praFascicolo = new praFascicolo($this->currGesnum);
        $praFascicolo->bloccaAllegati($chiave, $rowidArr, $tipo);
    }

    public function lanciaAggiungiAllegatiArr($serieAllegati, $marca = false) {
        $pracom_recA = $this->praLib->GetPracomA($this->keyPasso);
        $praFascicolo = new praFascicolo($this->currGesnum);
        $praFascicolo->setChiavePasso($this->keyPasso);

        $idAllegatiScelti = explode(",", $serieAllegati);
        $idScelti = array();
        foreach ($idAllegatiScelti as $id) {
            $idScelti[] = substr($id, 1);
        }
        if (!$idScelti) {
            $idScelti = "NO";
        }
//        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
        switch ($this->tipoProtocollo) {
            case "Italsoft-remoto-allegati":
                break;
            case "HyperSIC":
                break;
            case "Paleo4":
                $model = 'proPaleo4.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                include_once ITA_BASE_PATH . '/apps/Protocollo/proPaleo4.class.php';
                $proPaleo = new proPaleo4();
                $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione("Paleo4", true, "A", $idScelti);
                $arrayDocFiltrati = $praFascicolo->GetAllegatiNonProt($arrayDoc, "Paleo4");
                $param = array();
                $Metadati = unserialize($pracom_recA['COMMETA']);
                $param['DocNumber'] = $Metadati['DatiProtocollazione']['DocNumber']['value'];
                $param['Segnatura'] = $Metadati['DatiProtocollazione']['Segnatura']['value'];
                $param['arrayDoc'] = $arrayDocFiltrati['arrayDoc'];
                $valore = $proPaleo->AggiungiAllegati($param);
                if ($valore["Status"] == "0") {
                    $this->bloccaAllegati($this->keyPasso, $arrayDocFiltrati['arrayDoc']['pasdoc_rec'], "A");
                    $this->Dettaglio($this->keyPasso, "propak");
                    Out::msgBlock($this->nameForm, 3000, true, "Allegato/i protocollato/i correttamente");
                    if ($arrayDocFiltrati['strNoProt']) {
                        Out::msgInfo("Protocollazione Allegati", $arrayDocFiltrati['strNoProt']);
                    }
                } else {
                    Out::msgInfo("Protocollazione Allegati", "Allegato/i non protocollato/i");
                    return false;
                }
                break;
            case "Iride":
                $model = 'proIride.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                include_once ITA_BASE_PATH . '/apps/Protocollo/proIride.class.php';
                $proIride = new proIride();
                $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione("Iride", true, "A", $idScelti);
                $param = array();
                $param['NumeroProtocollo'] = substr($pracom_recA['COMPRT'], 4);
                $param['AnnoProtocollo'] = substr($pracom_recA['COMDPR'], 0, 4);
                $param['arrayDoc'] = $arrayDoc;
                $valore = $proIride->AggiungiAllegati($param);
                if ($valore["Status"] == "0") {
                    $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "A");
                    $this->Dettaglio($this->keyPasso, "propak");
                    Out::msgBlock($this->nameForm, 3000, true, "Allegato/i protocollato/i correttamente");
                } else {
                    Out::msgInfo("Protocollazione Allegati", "Allegato/i non protocollato/i");
                    return false;
                }
                break;
            case "Jiride":
                $model = 'proJiride.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                include_once ITA_BASE_PATH . '/apps/Protocollo/proJiride.class.php';
                $proIride = new proJiride();
                $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione("Jiride", true, "A", $idScelti);
                $param = array();
                $param['NumeroProtocollo'] = substr($pracom_recA['COMPRT'], 4);
                $param['AnnoProtocollo'] = substr($pracom_recA['COMDPR'], 0, 4);
                $param['arrayDoc'] = $arrayDoc;
                $valore = $proIride->AggiungiAllegati($param);
                if ($valore["Status"] == "0") {
                    $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "A");
                    $this->Dettaglio($this->keyPasso, "propak");
                    Out::msgInfo("Attenzione", $valore['Message']);
//Out::msgBlock($this->nameForm, 3000, true, "Allegato/i protocollato/i correttamente");
                } else {
//Out::msgInfo("Protocollazione Allegati", "Allegato/i non protocollato/i");
                    Out::msgStop("Attenzione", "Sono stati rilevati errori allegando i documenti al protocollo n. " . $param['NumeroProtocollo'] . " del " . $param['AnnoProtocollo'] . "<br>
                                    Procedere manualmente per allegare i seguenti documenti:<br>" . $valore['Message']);
                    return false;
                }
                break;
            case "Italsoft-ws":
                $model = 'proItalprot.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                include_once ITA_BASE_PATH . '/apps/Protocollo/proItalprot.class.php';
                $proItalprot = new proItalprot();
                $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione("Italsoft-ws", true, "A", $idScelti);
                $param = array();
                $param['NumeroProtocollo'] = substr($pracom_recA['COMPRT'], 4);
                $param['AnnoProtocollo'] = substr($pracom_recA['COMDPR'], 0, 4);
                $param['arrayDoc'] = $arrayDoc;
                $param['TipoProtocollo'] = "A";
                $valore = $proItalprot->AggiungiAllegati($param);
                if ($valore["Status"] == "0") {
                    $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "A");
                    $this->Dettaglio($this->keyPasso, "propak");
                    Out::msgInfo("Attenzione", $valore['Message']);
//Out::msgBlock($this->nameForm, 3000, true, "Allegato/i protocollato/i correttamente");
                } else {
//Out::msgInfo("Protocollazione Allegati", "Allegato/i non protocollato/i");
                    Out::msgStop("Attenzione", "Sono stati rilevati errori allegando i documenti al protocollo n. " . $param['NumeroProtocollo'] . " del " . $param['AnnoProtocollo'] . "<br>
                                    Procedere manualmente per allegare i seguenti documenti:<br>" . $valore['Message']);
                    return false;
                }
                break;
        }
        return true;
    }

    public function lanciaAggiungiAllegati($serieAllegati, $marca = false) {
        $pracom_recP = $this->praLib->GetPracomP($this->keyPasso);
        $praFascicolo = new praFascicolo($this->currGesnum);
        $praFascicolo->setChiavePasso($this->keyPasso);

        $idAllegatiScelti = explode(",", $serieAllegati);
        $idScelti = array();
        $this->allegatiPrtSel = array();
        foreach ($idAllegatiScelti as $id) {
            $this->allegatiPrtSel[]['ROWID'] = substr($id, 1);
            $idScelti[] = substr($id, 1);
        }
        if (!$idScelti) {
            $idScelti = "NO";
        }
        if ($marca) {
            $param = array();
            $param['NumeroProtocollo'] = substr($pracom_recP['COMPRT'], 4);
            $param['AnnoProtocollo'] = substr($pracom_recP['COMDPR'], 0, 4);
            if (!$this->SegnaturaAllegati($idScelti, $param)) {
                return false;
            }
        }


//        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
        switch ($this->tipoProtocollo) {
            case "Italsoft-remoto-allegati":
                if ($idScelti == 'NO') {
                    break;
                }
                $idAllegatiStr = implode("|", $idScelti);
                $accLib = new accLib();
                $utenteWs = $accLib->GetUtenteProtRemoto(App::$utente->getKey('idUtente'));
                if (!$utenteWs) {
                    Out::msgStop("Protocollo Remoto", "Utente remoto non definito!");
                    break;
                }
                $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $this->keyPasso . "' AND COMTIP='P'", false);
                $model = 'utiIFrame';
                $_POST = array();
                $_POST['event'] = 'openform';
                $_POST['returnModel'] = $this->nameForm;
                $_POST['returnEvent'] = 'returnItalsoftRemotoAllegatiP';
                $_POST['retid'] = $this->nameForm . '_protocollaRemotoPartenza';
//$devLib = new devLib();
//$parametro = $devLib->getEnv_config('ITALSOFTPROTREMOTO', 'codice', 'URLREMOTO', false);
//$url_param = $parametro['CONFIG'];
                $envLibProt = new envLibProtocolla();
                $url_param = $envLibProt->getParametriProtocolloRemoto();
                $_POST['src_frame'] = $url_param . "&access=direct&accessreturn=&accesstoken=nobody&model=menDirect&menu=PR_HID&prog=PR_WSPRA&topbar=0&homepage=0&noSave=1&utenteWs=" . $utenteWs . "&azione=ADDALLP&numPro=" . $pracomP_rec['COMPRT'] . "&idall=$idAllegatiStr&passo=" . $pracomP_rec['ROWID'];
                $_POST['title'] = "Protocollazione Remota Comunicazione in Partenza";
                $_POST['returnKey'] = 'protocollaWS';
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
            default:
                include_once ITA_BASE_PATH . '/apps/Pratiche/praWsClientManager.class.php';
                /*
                 * Istanzio l'oggetto praWsClientManager in base al tipo protocollo configurato nei dati ente
                 */
                $praWsClientManager = praWsClientManager::getInstance($this->tipoProtocollo);
                /*
                 * Setto il numero del fascicolo
                 */
                $praWsClientManager->setCurrGesnum($this->currGesnum);
                /*
                 * Setto la chiave del passo
                 */
                $praWsClientManager->setKeyPasso($this->keyPasso);
                /*
                 * Setto il tipo protocollo
                 */
                $praWsClientManager->setTipoProt("P");
                /*
                 * Carico gli allegati selezionati da aggiungere al protocollo
                 */
                $praWsClientManager->loadAllegatiFromComunicazioneComP(true, $idScelti);
                /*
                 * Aggiungo gli allegati al protocollo
                 */
                $valore = $praWsClientManager->AggiungiAllegati();
                if ($valore["Status"] == "-1") {
                    Out::msgStop("Errore", $valore["Message"]);
                    return false;
                }
                $arrayDoc = $praWsClientManager->getArrayDoc();
                $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "P");
                Out::msgInfo("Protocollazione Allegati", $valore["Message"]);

                $this->Dettaglio($this->keyPasso, "propak");
                break;
        }
        return true;
    }

    private function lanciaDocumentoFormaleWS() {
        /*
         * Mi creo un array con gli id degli allegati scelti
         */
        $idScelti = array();
        if ($_POST['retKey']) {
            $idAllegatiScelti = explode(",", $_POST['retKey']);
            foreach ($idAllegatiScelti as $id) {
                $idScelti[] = substr($id, 1);
            }
        }
        if (!$idScelti) {
            $idScelti = "NO";
        }

        /*
         * Get Array elementi
         */
        $praFascicolo = new praFascicolo($this->currGesnum);
        $praFascicolo->setChiavePasso($this->keyPasso);
//$retElementi = $praFascicolo->getElementiProtocollazionePasso($idScelti, "P");
        $retElementi = $praFascicolo->getElementiProtocollazionePasso($idScelti, "C");
        if ($retElementi['Status'] == "-1") {
            return $retElementi;
        }
        $elementi = $retElementi['Elementi'];
        if ($this->mettiAllaFirma) {
            $elementi['dati']['mettiAllaFirma'] = $this->mettiAllaFirma;
        }

        /*
         * Inserisco il documento formale
         */
        $retPrt = proWsClientHelper::lanciaDocumentoFormaleWS($elementi);
        if ($retPrt['Status'] == "-1") {
            return $retPrt;
        }
        $elementi['dati']['Fascicolazione']['Anno'] = date("Y");
        $elementi['dati']['Fascicolazione']['Numero'] = $retPrt['RetValue']['DatiProtocollazione']['proNum']['value'];

        /*
         * Se c'è l'array dei rowid protocollati, aggiorno l'array pasdoc_rec
         */
        if ($retPrt['rowidAllegati']) {
            $elementi['dati']['arrayDoc']['pasdoc_rec'] = $retPrt['rowidAllegati'];
        }

        /*
         * Aggiorno i dati di protocollazione
         */
        $retUpd = $praFascicolo->updateDatiProtPracom($retPrt, $elementi['dati']['arrayDoc'], "P");
        if ($retUpd['Status'] == "-1") {
            return $retUpd;
        }

        /*
         * Se Attiva, lancio la fascicolazione
         */
        $Filent_Rec = $this->praLib->GetFilent(29);
        if ($Filent_Rec['FILVAL'] == 1) {
            $ret = $this->lanciaFascicolazioneWS($elementi);
            if ($ret['Status'] == "-1") {
                Out::msgStop("Errore in Fascicolazione", $ret['Message']);
            }
        }

        /*
         * Se la protocollazione è andata a bun fine, mostro il messaggio
         */
        if ($retPrt['Status'] == "0") {
            //Out::msgInfo("Protocollazione Partenza", "Protocollazione avvenuta con successo al n. " . $retPrt['RetValue']['DatiProtocollazione']['proNum']['value'] . ".<br>" . $ret['Message']);
            Out::msgInfo("Documento Formale", "Protocollazione avvenuta con successo al n. " . $retPrt['RetValue']['DatiProtocollazione']['proNum']['value'] . ".<br>" . $ret['Message'] . "<br><br><span style=\"color:red;\"><b>" . $retPrt['errString'] . "</b></span>");
        }

//        if ($Filent_Rec['FILVAL'] == 1) {
//            $ret = proWsClientHelper::lanciaFascicolazioneWS($elementi);
//            if ($ret['Status'] == "-1") {
//                return $ret;
//            }
//        }
//
        $ritorno = array();
        $ritorno['Status'] = "0";
        $ritorno['Message'] = "Il Documento Formale è stato inserito corrtettamente.";
        $ritorno['RetValue'] = false;
        return $ritorno;
    }

    private function lanciaMettiAllaFirmaWS() {
        $idScelti = array();
        if ($_POST['retKey']) {
            $idAllegatiScelti = explode(",", $_POST['retKey']);
            foreach ($idAllegatiScelti as $id) {
                $idScelti[] = substr($id, 1);
            }
        }
        if (!$idScelti) {
            $idScelti = "NO";
        }
//ripeto l'estrazione degli allegati filtrando solo quelli non selezionati dall'array

        /*
         * Get Array elementi
         */
        $praFascicolo = new praFascicolo($this->currGesnum);
        $praFascicolo->setChiavePasso($this->keyPasso);

        $retElementi = $praFascicolo->getElementiProtocollazionePasso($idScelti, "P");
        if ($retElementi['Status'] == "-1") {
            return $retElementi;
        }
        $elementi = $retElementi['Elementi'];

        /*
         * Inserisco paramentro Fascicola in elementi
         */
        $elementi['Fascicola'] = "No";
        $Filent_Rec = $this->praLib->GetFilent(29);
        if ($Filent_Rec['FILVAL'] == 1) {
            $elementi['Fascicola'] = "Si";
        }

        /*
         * Lancio il metti alla firma.
         */
        $valore = proWsClientHelper::lanciaMettiAllaFirmaWS($elementi);
        if ($valore['Status'] == "-1") {
            return $valore;
        }
        $elementi['DocNumber'] = $valore['RetValue']['DatiProtocollazione']['DocNumber']['value'];


        /*
         * Aggiorno i dati di protocollazione
         */
        $retUpd = $praFascicolo->updateDatiProtPracomIdDoc($valore, $elementi['dati']['arrayDoc']);
        if ($retUpd['Status'] == "-1") {
            return $retUpd;
        }

        /*
         * Se Attiva, lancio la fascicolazione
         */
        if ($Filent_Rec['FILVAL'] == 1) {
            $retFasc = proWsClientHelper::lanciaFascicolazioneWS($elementi);
        }

        /*
         * Istanzio ritorno
         */
        $ritorno = array();
        $ritorno['Status'] = "0";
        $ritorno['Message'] = "Inserito correttamente documento con Id " . $valore['RetValue']['DatiProtocollazione']['DocNumber']['value'] . ".<br>" . $retFasc['Message'];
        $ritorno['RetValue'] = false;
        return $ritorno;
    }

    private function lanciaProtocollaArrivoWS() {
        $praFascicolo = new praFascicolo($this->currGesnum);
        $praFascicolo->setChiavePasso($this->keyPasso);

        /*
         * Get Array elementi
         */
        $retElementi = $praFascicolo->getElementiProtocollazionePasso();
        if ($retElementi['Status'] == "-1") {
            return $retElementi;
        }
        $elementi = $retElementi['Elementi'];

        /*
         * Se c'è il mettiAllaFirma lo inserisco nell'array $elementi
         */
        if ($this->mettiAllaFirma) {
            $elementi['dati']['mettiAllaFirma'] = $this->mettiAllaFirma;
        }

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
        $retUpd = $praFascicolo->updateDatiProtPracom($retPrt, $elementi['dati']['arrayDoc']);
        if ($retUpd['Status'] == "-1") {
            return $retUpd;
        }

        /*
         * Se Attiva lancio la fascicolazione
         */
        if ($Filent_Rec['FILVAL'] == 1) {
            $ret = $this->lanciaFascicolazioneWS($elementi, "A");
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

        /*
         * Se la protocollazione è andata a bun fine, mostro il messaggio
         */
        if ($retPrt['Status'] == "0") {
            Out::msgInfo("Protocollazione Arrivo", "Protocollazione avvenuta con successo al n. " . $retPrt['RetValue']['DatiProtocollazione']['proNum']['value'] . ".<br>" . $ret['Message'] . $errStr . $strNoProt);
        }

        $this->Dettaglio($this->keyPasso, "propak");
        Out::tabSelect($this->nameForm . '_tabProcedimento', $this->nameForm . "_paneCom");
    }

    private function lanciaProtocollaWS() {
        $idScelti = array();
        if ($_POST['retKey']) {
            $idAllegatiScelti = explode(",", $_POST['retKey']);
            foreach ($idAllegatiScelti as $id) {
                $idScelti[] = substr($id, 1);
            }
        }
        if (!$idScelti) {
            $idScelti = "NO";
        }

        $praFascicolo = new praFascicolo($this->currGesnum);
        $praFascicolo->setChiavePasso($this->keyPasso);

        /*
         * Get Array elementi
         */
        $retElementi = $praFascicolo->getElementiProtocollazionePasso($idScelti, "P");
        if ($retElementi['Status'] == "-1") {
            return $retElementi;
        }
        $elementi = $retElementi['Elementi'];

        /*
         * Se c'è il mettiAllaFirma lo inserisco nell'array $elementi
         */
        if ($this->mettiAllaFirma) {
            $elementi['dati']['mettiAllaFirma'] = $this->mettiAllaFirma;
        }

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
        $retPrt = proWsClientHelper::lanciaProtocollazioneWS($elementi, "P");
        if ($retPrt['Status'] == "-1") {
            return $retPrt;
        }

        $elementi['DocNumber'] = $retPrt['RetValue']['DatiProtocollazione']['DocNumber']['value'];
        $elementi['dati']['Fascicolazione']['Anno'] = $retPrt['RetValue']['DatiProtocollazione']['Anno']['value'];
        $elementi['dati']['Fascicolazione']['Numero'] = $retPrt['RetValue']['DatiProtocollazione']['proNum']['value'];

        /*
         * Aggiorno i dati di protocollazione
         */
        $retUpd = $praFascicolo->updateDatiProtPracom($retPrt, $elementi['dati']['arrayDoc'], "P");
        if ($retUpd['Status'] == "-1") {
            return $retUpd;
        }

        /*
         * Se Attiva lancio la fascicolazione
         */
        if ($Filent_Rec['FILVAL'] == 1) {
            $ret = $this->lanciaFascicolazioneWS($elementi, "P");
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

        /*
         * Se la protocollazione è andata a bun fine, mostro il messaggio
         */
        if ($retPrt['Status'] == "0") {
            Out::msgInfo("Protocollazione Partenza", "Protocollazione avvenuta con successo al n. " . $retPrt['RetValue']['DatiProtocollazione']['proNum']['value'] . ".<br>" . $ret['Message'] . $errStr . $strNoProt);
        }

        $this->Dettaglio($this->keyPasso, "propak");
        Out::tabSelect($this->nameForm . '_tabProcedimento', $this->nameForm . "_paneCom");
    }

    public function lanciaFascicolazioneWS($elementi) {
        $msgFascicola = "";
        if ($elementi['dati']['NumeroAntecedente']) {
            /*
             * Inizializzo il driver della fascicolazione
             */
            $proObjectFascicola = proWSClientFactory::getInstanceFascicolazione($elementi['TipoProtocollo']);
            if (!$proObjectFascicola) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Errore inizializzazione driver fascicolazione";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }

            /*
             * setto il codice istanza per la fascicolazione
             */
            $proObjectFascicola->setKeyConfigParams($elementi['MetaDatiProtocollo']['CLASSIPARAMETRI'][$elementi['TipoProtocollo']]['KEYPARAMWSFASCICOLAZIONE']);

            /*
             * Mi ritorno il codice del fasciolo in cui inserire in protocollo
             */
            $ret = $proObjectFascicola->getCodiceFascicolo($elementi);
            if ($ret["Status"] == "-1") {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = $ret["Message"];
                $ritorno["RetValue"] = false;
                return $ret;
            }
            $elementi['dati']['Fascicolazione']['CodiceFascicolo'] = $ret['CodiceFascicolo'];
            $elementi['dati']['Fascicolazione']['AnnoFascicolo'] = $ret['AnnoFascicolo'];

            if ($elementi['dati']['Fascicolazione']['CodiceFascicolo']) {
                /*
                 * Verifica data chiusura fascicolo
                 */
                $risultatoChk = $proObjectFascicola->checkFascicolo($elementi['dati']['Fascicolazione']['CodiceFascicolo']);
                if ($risultatoChk['Status'] == "-1") {
                    return $risultatoChk;
                }
                $fascicola = $risultatoChk['fascicola'];
                $msgFascicola = $risultatoChk['Message'];
            } else {
                $Filent_Rec = $this->praLib->GetFilent(33);
                if ($Filent_Rec['FILVAL'] == 1) {
                    $risultato = $proObjectFascicola->CreaFascicolo($elementi);
                    if ($risultato['Status'] == "-1") {
                        return $risultato;
                    }
                    $fascicola = true;
                    $elementi['dati']['Fascicolazione']['CodiceFascicolo'] = $risultato['datiFascicolo']['codiceFascicolo'];
                    $elementi['dati']['Fascicolazione']['AnnoFascicolo'] = $risultato['datiFascicolo']['annoFascicolo'];
                } else {
                    $fascicola = false;
                    $msgFascicola = "Fascicolazione non avvenuta. Non è stato possibile reperire il codice fasciolo. Per Fascicolare il protocollo, procedere manualmente dall'applicativo protocollo.";
                }
            }

            /*
             * Fascicolo il protocollo
             */
            if ($fascicola === true) {
                $risultatoFascicola = $proObjectFascicola->FascicolaDocumento($elementi);
                if ($risultatoFascicola['Status'] == "-1") {
                    return $risultatoFascicola;
                }
                $msgFascicola = "Fascicolazione avvenuta con successo nel fascicolo n. " . $elementi['dati']['Fascicolazione']['CodiceFascicolo'];
            }
//            else {
//                $msgFascicola = "Fascicolazione non avvenuta. Il fascicolo n. " . $elementi['dati']['Fascicolazione']['CodiceFascicolo'] . " risulta chiuso in data " . $risultatoChk['DataChiusuraFascicolo'];
//            }
        }
        $ret["Status"] = "0";
        $ret["Message"] = $msgFascicola;
        $ret["RetValue"] = true;
        return $ret;
    }

    private function caricaAllegatoDaZip($rowData) {
        $arrayFile = $this->praLib->CaricaAllegatoDaZip($rowData, $this->passAlle);
        if ($arrayFile == false) {
            return;
        }
        if (isset($arrayFile["daFile"])) {
            foreach ($this->passAlle as $posE => $allegato) {
                if ($allegato['RANDOM'] == 'ESTERNO') {
                    $posEsterno = $posE;
                    $parent = $allegato['PROV'];
                    break;
                }
            }

            $i = $posEsterno + 1;
            $trovato = false;
            while ($trovato == false) {
                if ($i >= count($this->passAlle)) {
                    $trovato = true;
                } else {
                    if ($this->passAlle[$i]['level'] == 0) {
                        $trovato = true;
                    } else {
                        $i++;
                    }
                }
            }
            $arrayTop = array_slice($this->passAlle, 0, $i);
            $arrayDown = array_slice($this->passAlle, $i);
            $arrayFile["Allegato"]['parent'] = $parent;
            $arrayFile["Allegato"]['PROV'] = $i;
            $arrayFile["Allegato"]['NAME'] = '<span style = "color:orange;">' . $arrayFile['Allegato']['NAME'] . '</span>';
            $arrayFile["Allegato"]['INFO'] = $arrayFile["Allegato"]['NOTE'];
            $arrayFile["Allegato"]['FILEINFO'] = $arrayFile["Allegato"]['NOTE'];
            $arrayTop[] = $arrayFile["Allegato"];

            foreach ($arrayDown as $chiave => $recordDown) {
                if ($recordDown['level'] == 1) {
                    $arrayDown[$chiave]['PROV'] = $recordDown['PROV'] + 1;
                }
            }
            $this->passAlle = array_merge($arrayTop, $arrayDown);
        }
    }

    private function setDestinatarioProtocollo($gesres) {
        $ananom_rec = $this->praLib->GetAnanom($gesres);
        $anamed_rec = $this->proLib->GetAnamed($ananom_rec['NOMDEP'], 'codice', 'no');
        $destinatario = array();
        if ($anamed_rec) {
            $uffdes_tab = $this->proLib->GetUffdes($anamed_rec['MEDCOD']);
            foreach ($uffdes_tab as $uffdes_rec) {
                $anauff_rec = $this->proLib->GetAnauff($uffdes_rec['UFFCOD']);
                if ($anauff_rec['UFFANN'] == 0) {
                    $destinatario['CodiceDestinatario'] = $anamed_rec['MEDCOD'];
                    $destinatario['Denominazione'] = $anamed_rec['MEDNOM'];
                    $destinatario['Indirizzo'] = $anamed_rec['MEDIND'];
                    $destinatario['CAP'] = $anamed_rec['MEDCAP'];
                    $destinatario['Citta'] = $anamed_rec['MEDCIT'];
                    $destinatario['Provincia'] = $anamed_rec['MEDPRO'];
                    $destinatario['Annotazioni'] = $anamed_rec['MEDNOTE'];
                    $destinatario['Email'] = $anamed_rec['MEDEMA'];
                    $destinatario['Ufficio'] = $anauff_rec['UFFCOD'];
                    break;
                }
            }
        }
        return $destinatario;
    }

    private function setUfficiProtocollo($uffkey) {
        $uffici = array();
        $uffdes_tab = $this->proLib->GetUffdes($uffkey, 'uffkey');
        foreach ($uffdes_tab as $uffdes_rec) {
            $ufficio = array();
            $ufficio['CodiceUfficio'] = $uffdes_rec['UFFCOD'];
            $ufficio['Scarica'] = $uffdes_rec['UFFSCA'];
            $uffici[] = $ufficio;
        }
        return $uffici;
    }

    function ControllaDati() {
//Implementiamo i controlli
        if (!$this->ControllaDataEdit()) {
            return false;
        }
        if ($_POST[$this->nameForm . '_PROTRIC_DESTINATARIO'] && strlen($_POST[$this->nameForm . '_ANNOPROT_DESTINATARIO']) != 4) {
            Out::msgStop("Errore", "Anno protocollo Partenza non corretto o mancante");
            return false;
        }
        if ($_POST[$this->nameForm . '_PROTRIC_MITTENTE'] && strlen($_POST[$this->nameForm . '_ANNORIC_MITTENTE']) != 4) {
            Out::msgStop("Errore", "Anno protocollo Arrivo non corretto o mancante");
            return false;
        }
        if ($this->flagAssegnazioniPasso) {
            if (!$this->ControlloDatiProtocolloPasso()) {
                return false;
            }
        }
        return true;
    }

    function ControllaDataEdit() {
        /*
         * Controllo se il passo è stato aggiornato durante l'edit in corso
         */
        if ($this->keyPasso) {
            $propas_rec = $this->praLib->GetPropas($this->keyPasso);
            if ($propas_rec['PRODATEEDIT'] !== $this->iniDateEdit) {
                Out::msgStop("Errore", "Dati Modificati esternamente da altra sessione di lavoro durante la gestione.<br> Ricaricare il passo.");
                return false;
            }
        }
        return true;
    }

    function AggiornaDateEdit() {
        $propas_rec = $this->praLib->GetPropas($this->keyPasso);
        $propas_rec['PROUTEEDIT'] = App::$utente->getKey('nomeUtente');
        $propas_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date("H:i:s");
        $this->iniDateEdit = $propas_rec['PRODATEEDIT'];
        $update_Info = "Cancellazione Allegati: Aggiorno utente e data modifica passo seq " . $propas_rec['PROSEQ'] . " e chiave " . $propas_rec['PROPAK'];
        if (!$this->updateRecord($this->PRAM_DB, 'PROPAS', $propas_rec, $update_Info)) {
            Out::msgStop("ERRORE", "Errore Aggiornamento Passo dopo cancellazione allegato");
            return false;
        }
        Out::valore($this->nameForm . "_utenteEdit", "Ultima modifica al passo effettuata dall'utente " . $propas_rec['PROUTEEDIT'] . " in data " . $propas_rec['PRODATEEDIT']);
        return true;
    }

    public function SincronizzaRecordPasso($Propas) {
        if (!$Propas) {
            Out::msgStop("Sincronizzazione Passo", "Record Passo non trovato. Impossibile Sincronizzare");
            return false;
        }

        $keyPasso = $Propas['PROPAK'];
        if ($keyPasso == "") {
            Out::msgStop("Sincronizzazione Passo", "Chiave Passo non trovata. Impossibile Sincronizzare");
            return false;
        }

        $procedimento = $Propas['PRONUM'];

        $arrayScadenza = $this->praLib->SincDataScadenza("PASSO", $keyPasso, $Propas['PRODSC'], "", $Propas['PROGIO'], $Propas['PROINI'], true);
        if (!$arrayScadenza) {
            Out::msgStop("Sincronizzazione Scadenza", "Errore Sincronizzazione Data Scadenza.");
            return false;
        }
        $Propas['PRODSC'] = $arrayScadenza['SCADENZA'];
        $Propas['PROGIO'] = $arrayScadenza['GIORNI'];

        if ($Propas['PRORPA']) {
            $Propas['PRORPA'] = str_pad($Propas['PRORPA'], 6, "0", STR_PAD_LEFT);
        }

        $Propas['PROUTEEDIT'] = App::$utente->getKey('nomeUtente');
        $Propas['PRODATEEDIT'] = date("d/m/Y") . " " . date("H:i:s");
        $update_Info = "Oggetto: Sincronizzazione passo con seq " . $Propas['PROSEQ'] . " e chiave " . $Propas['PROPAK'];
        if (!$this->updateRecord($this->PRAM_DB, 'PROPAS', $Propas, $update_Info)) {
            Out::msgStop("ERRORE", "Sincronizzazione record");
            return false;
        }
        if (!$this->praLib->sincronizzaStato($procedimento)) {
            Out::msgStop("Errore sinc stato", $this->praLib->getErrMessage());
        }

        $errMsg = $this->praLib->sincCalendar("PASSO", $keyPasso, $Propas['PRODSC']);
        if ($errMsg) {
            Out::msgStop("Errore Sincronizzazione Calendario", $errMsg);
        }
        return true;
    }

    public function chiediModoCreaPasso() {
        Out::msgInput("Crea Nuovo Passo", array(
            array(
                'label' => array('style' => "width:300px;", 'value' => 'Nuovo Passo'),
                'id' => $this->nameForm . '_TipoPasso',
                'name' => $this->nameForm . '_TipoPasso',
                'type' => 'radio',
                'width' => '200',
                'size' => '50',
                'maxlength' => '80',
                'value' => 1,
                'checked' => "checked"
            ),
            array(
                'label' => array('style' => "width:300px;", 'value' => 'Nuovo Sotto Passo'),
                'id' => $this->nameForm . '_TipoPasso',
                'name' => $this->nameForm . '_TipoPasso',
                'type' => 'radio',
                'width' => '200',
                'size' => '50',
                'maxlength' => '80',
                'value' => 2),
                ), array(
            'F1-Annulla' => array('id' => $this->nameForm . '_AnnullaPasso', 'model' => $this->nameForm, 'shortCut' => "f1"),
            'F5-Crea' => array('id' => $this->nameForm . '_CreaPasso', 'model' => $this->nameForm, 'shortCut' => "f5"),
            'F6-Aggiorna e Crea' => array('id' => $this->nameForm . '_CreaSalvaPasso', 'model' => $this->nameForm, 'shortCut' => "f6"),
            'F8-Chiudi Aggiorna e Crea' => array('id' => $this->nameForm . '_ChiudiSalvaCreaPasso', 'model' => $this->nameForm, 'shortCut' => "f8"),
                ), $this->nameForm, 'auto', 'auto', true, '', 'F8-Chiudi/Aggiorna/Crea - Data Chiusura al ' . substr($this->workDate, 6, 2) . '/' . substr($this->workDate, 4, 2) . '/' . substr($this->workDate, 0, 4)
        );
    }

//public function trovaSottoPasso($currGesnum) {
    public function trovaSottoPasso() {
//$proges_rec = $this->praLib->GetProges($currGesnum, 'codice');
        $propas_rec = $this->praLib->GetPropas($this->keyPasso, 'propak');
//$keyRic = $proges_rec['GESPRO'] . substr($propas_rec['PROPAK'], 10);
        $keyRic = $propas_rec['PROITK'];
        if ($keyRic) {
            $itepas_rec = $this->praLib->GetItepas($keyRic, 'itekey');
            $ret = praRic::praItepasAntecedenti(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), $itepas_rec, $this->PRAM_DB);
            if (!$ret) {
                $this->AzzeraVariabili();
                $this->apriInserimento($this->currGesnum);
            } else {
                praRic::praItepasAntecedenti(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), $itepas_rec, $this->PRAM_DB);
            }
        } else {
            $this->AzzeraVariabili();
            $this->apriInserimento($this->currGesnum);
        }
    }

    public function CheckAperturaPassoPadre($Propas) {
        if ($Propas['PROKPRE']) {
            $sql = "SELECT * FROM PROPAS WHERE PROPAK='" . $Propas['PROKPRE'] . "'";
            $this->Propas = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($this->Propas) {
                if (!$this->Propas['PROINI'] && $Propas['PROINI']) {
                    Out::msgQuestion("Attenzione", "Il passo padre: " . $this->Propas['PRODPA'] . " non è aperto si desidera aprirlo?", array(
                        'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaApriPadre', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaApriPadre', 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                    );
                }
            }
        }
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

    function AggiungiDaAnagrafe($soggetto, $tipo = "ARRIVO") {
        switch ($tipo) {
            case "ARRIVO":
                Out::valore($this->nameForm . '_MITTENTE', $soggetto['CODICEUNIVOCO']);
                Out::valore($this->nameForm . '_DESC_MITTENTE', $soggetto['NOME'] . " " . $soggetto['COGNOME']);
                Out::valore($this->nameForm . '_CODFISC_MITTENTE', $soggetto['CODICEFISCALE']);
                if ($soggetto['CODICEFISCALE']) {
                    Out::show($this->nameForm . "_consultaAnagrafe");
                } else {
                    Out::hide($this->nameForm . "_consultaAnagrafe");
                }
                break;
            case "DESTINATARIO":
                itaLib::openForm("praGestDestinatari");
                $praGestDest = itaModel::getInstance('praGestDestinatari');
                $praGestDest->setReturnEvent("returnGestDest");
                $praGestDest->setReturnModel($this->nameForm);
                $praGestDest->setReturnId('');
                $praGestDest->AggiungiDaAnagrafe($soggetto);
                break;
            default:
                break;
        }
    }

    function GetOggettoMailPartenza() {
        $praLibVar = new praLibVariabili();
        $Filent_rec = $this->praLib->GetFilent(31);
        $praLibVar->setCodicePratica($this->currGesnum);
        $praLibVar->setChiavePasso($this->keyPasso);
        $dictionaryValues = $praLibVar->getVariabiliPratica()->getAllData();
        return $this->praLib->GetStringDecode($dictionaryValues, $Filent_rec['FILVAL']);
    }

    function openFormAddNota() {
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
    }

    function returnFormAddNota() {
        $dati = array(
            'OGGETTO' => $_POST['oggetto'],
            'TESTO' => $_POST['testo'],
            'CLASSE' => praNoteManager::NOTE_CLASS_PROPAS,
            'CHIAVE' => $this->keyPasso
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

        /*
         * Aggiorno dal POST il record di PROPAS solo in presenza del parametro GOBID
         */
        Out::valore($this->nameForm . "_PROPAS[PROANN]", $dati['OGGETTO']);
        $propas_rec = $this->formData[$this->nameForm . '_PROPAS'];
        $propas_rec['PROANN'] = $dati['OGGETTO'];
        $update_Info = "Oggetto: aggiorno annotazioni passo da oggetto Nota";
        if (!$this->updateRecord($this->PRAM_DB, 'PROPAS', $propas_rec, $update_Info)) {
            Out::msgStop("Errore", "Errore aggiornamento passo da oggetto nota");
        }
    }

    function InvioMailDaWS($mittente, $destinatariSel, $oggetto, $corpo) {
        $ritorno = array();
        $ritorno['Status'] = "0";
        $ritorno['Message'] = "La mail è stata inviata correttamente";
        $ritorno['RetValue'] = true;

        /*
         * Invio Mail in base al tip odi protocollo
         */
        $param = array();
        $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
        $Metadati = unserialize($pracomP_rec['COMMETA']);

        $param['TipoProtocollo'] = $this->tipoProtocollo;
        $param['proNum'] = $Metadati['DatiProtocollazione']['proNum']['value'];
        $param['Anno'] = $Metadati['DatiProtocollazione']['Anno']['value'];
        $param['DocNumber'] = $Metadati['DatiProtocollazione']['DocNumber']['value'];
        $param['Oggetto'] = $oggetto;
        $param['Testo'] = $corpo;
        $param['Mittente'] = $mittente;
        $param['Destinatari'] = $destinatariSel;
        $param['Segnatura'] = $Metadati['DatiProtocollazione']['Segnatura']['value'];

        /*
         * lancio metodo invio mail.
         */
        $retInvio = proWsClientHelper::lanciaInvioMailWS($param);
        $retInvio['Metadati'] = $Metadati;
        return $retInvio;
    }

    function decodCodAmmAoo($pracom_rec, $tipo = "P") {
        if ($tipo == "P") {
            $idCampo = $this->nameForm . "_CodAmmAoo";
        } elseif ($tipo == "A") {
            $idCampo = $this->nameForm . "_CodAmmAooArr";
        }
//
        if ($pracom_rec['COMAMMPR'] && $pracom_rec['COMAOOPR']) {
            Out::valore($idCampo, $pracom_rec['COMAMMPR'] . "/" . $pracom_rec['COMAOOPR']);
            Out::show($idCampo . "_field");
        } else {
            Out::hide($idCampo . "_field");
        }
    }

    private function getFilenameFoglioElaborato($filename, $def = false) {
        return substr($filename, 0, strlen(pathinfo($filename, PATHINFO_EXTENSION)) * -1) . ($def ? 'definitivo.' : 'elaborato.') . pathinfo($filename, PATHINFO_EXTENSION);
    }

    private function estrazioneDataFoglioDiCalcolo($spreadsheetPath) {
        /*
         * Carico l'XLSX.
         */
        include_once ITA_LIB_PATH . '/itaPHPDocs/itaDocumentFactory.class.php';

        /* @var $masterDoc itaDocumentXLSX */
        $masterDoc = itaDocumentFactory::getDocument(strtoupper(pathinfo($spreadsheetPath, PATHINFO_EXTENSION)));

        if (!$masterDoc->loadContent($spreadsheetPath)) {
            Out::msgStop('Errore', "Errore estrazione variabili XLS: " . $masterDoc->getMessage());
            return false;
        }

        /*
         * Cerco e prendo le variabili da esportare.
         */
        $prodag_exp_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '{$this->currGesnum}' AND DAGPAK = '{$this->keyPasso}' AND DAGDIZ = 'F'");
        foreach ($prodag_exp_tab as $prodag_rec) {
            $prodag_rec['DAGVAL'] = $masterDoc->getVarContent($prodag_rec['DAGDEF']);

            if ($prodag_rec['DAGVAL'] === false) {
                Out::msgStop('Errore', "Errore estrazione variabili XLS: " . $masterDoc->getMessage());
                return false;
            }

            $update_info = sprintf('Fascicolo %s passo %s: aggiornamento %s con valore elaborato da foglio di calcolo', $this->currGesnum, $this->keyPasso, $prodag_rec['DAGKEY']);
            if (!$this->updateRecord($this->PRAM_DB, 'PRODAG', $prodag_rec, $update_info)) {
                Out::msgStop('Errore', 'Errore durante il salvataggio delle variabili compilate.');
                return false;
            }
        }

        return true;
    }

    private function elaborazioneFoglioDiCalcolo($spreadsheetPath, $savePath, $download = false) {
        /*
         * Preparo il dizionario.
         */
        $praLibVar = new praLibVariabili();
        $praLibVar->setCodicePratica($this->currGesnum);
        $praLibVar->setChiavePasso($this->keyPasso);

        $variabiliCampiAggiuntiviPratica = $praLibVar->getVariabiliCampiAggiuntiviPratica();
        $dictionaryPratica = $variabiliCampiAggiuntiviPratica ? $variabiliCampiAggiuntiviPratica->getAlldataPlain('', '.') : array();

        /*
         * Carico e compilo l'XLSX.
         */
        include_once ITA_LIB_PATH . '/itaPHPDocs/itaDocumentFactory.class.php';

        /* @var $masterDoc itaDocumentXLSX */
        $masterDoc = itaDocumentFactory::getDocument(strtoupper(pathinfo($spreadsheetPath, PATHINFO_EXTENSION)));

        if (!$masterDoc->loadContent($spreadsheetPath)) {
            Out::msgStop('Errore', 'Errore salvataggio variabili XLS: ' . $masterDoc->getMessage());
            return false;
        }

        /*
         * Cerco e prendo le variabili da importare.
         */
        $prodag_imp_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '{$this->currGesnum}' AND DAGPAK = '{$this->keyPasso}' AND DAGALIAS != ''");
        foreach ($prodag_imp_tab as $prodag_rec) {
            $dagval = $prodag_rec['DAGVAL'];
            if (($prodag_rec['DAGVAL'] === '' || !isset($prodag_rec['DAGVAL']) ) && $prodag_rec['DAGDEF'] !== '') {
                $dagval = $this->praLibElaborazioneDati->elaboraValoreProdag($prodag_rec, $dictionaryPratica);
            }

            if (!$masterDoc->setVarContent($prodag_rec['DAGALIAS'], $dagval)) {
                Out::msgStop('Errore', 'Errore salvataggio variabili XLS: ' . $masterDoc->getMessage());
                return false;
            }
        }

        if (!$masterDoc->saveContent($savePath, true)) {
            Out::msgStop('Errore', 'Errore salvataggio variabili XLS: ' . $masterDoc->getMessage());
            return false;
        }

        if ($download) {
            $filename = basename($spreadsheetPath, '.' . pathinfo($spreadsheetPath, PATHINFO_EXTENSION)) . '.' . pathinfo($savePath, PATHINFO_EXTENSION);
            Out::openDocument(utiDownload::getUrl($filename, $savePath, true));
        }

        return true;
    }

    private function svuotaDatiExportFoglioDiCalcolo() {
        /*
         * Cerco e svuoto le variabili popolate da XLS.
         */
        $prodag_exp_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '{$this->currGesnum}' AND DAGPAK = '{$this->keyPasso}' AND DAGDIZ = 'F'");
        foreach ($prodag_exp_tab as $prodag_rec) {
            $prodag_rec['DAGVAL'] = '';
            $update_info = sprintf('Fascicolo %s passo %s: cancellazione valore %s per eliminazione XLS', $this->currGesnum, $this->keyPasso, $prodag_rec['DAGKEY']);
            if (!$this->updateRecord($this->PRAM_DB, 'PRODAG', $prodag_rec, $update_info)) {
                Out::msgStop('Errore', 'Errore durante il reset dei dati aggiuntivi.');
                return false;
            }
        }

        return true;
    }

    function ImportaDaProtocollo($dati) {
        $dataPrt = $this->praLib->GetDataProtNormalizzata($this->datiWS['RetValue']); // nel formato GG/MM/AAAA
        $pracom_rec['COMTIP'] = $dati['Origine'];
        $pracom_rec['COMPAK'] = $this->keyPasso;
        $pracom_rec['COMNUM'] = $this->currGesnum;
        $pracom_rec['COMDAT'] = substr($dataPrt, 6, 4) . substr($dataPrt, 3, 2) . substr($dataPrt, 0, 2);
        $pracom_rec['COMORA'] = substr($dati['Data'], 11, 8);
        $pracom_rec['COMPRT'] = $dati['Anno'] . $dati['NumeroProtocollo'];
        $pracom_rec['COMDPR'] = substr($dataPrt, 6, 4) . substr($dataPrt, 3, 2) . substr($dataPrt, 0, 2);
        $pracom_rec['COMTIN'] = "";
        $pracom_rec['COMNOT'] = $dati['Oggetto'];
        $pracom_rec['COMRIF'] = "";
        $pracom_rec['COMIDMAIL'] = "";
        $metadati = array(
            'DatiProtocollazione' => $this->datiWS['RetValue']['DatiProtocollazione']
        );
        $metadati['DatiProtocollazione']['Importato'] = true;
        $pracom_rec['COMMETA'] = serialize($metadati);

        /*
         * Se è un arrivo, prendo il primo mittente dato che contempliamo un mittente singolo
         */
        $mittDest = $dati['MittentiDestinatari'];
        if ($pracom_rec['COMTIP'] == 'A') {
            $mittDest = array();
            $mittDest[] = $dati['MittentiDestinatari'][0];
        }
        $primo = true;
        $praMitDest_tab = array();
        foreach ($mittDest as $nominativo) {
            $praMitDest_rec = array();
            $praMitDest_rec['TIPOCOM'] = ($pracom_rec['COMTIP'] == 'A') ? 'M' : 'D';
            $praMitDest_rec['KEYPASSO'] = $this->keyPasso;
            $praMitDest_rec['DATAINVIO'] = $pracom_rec['COMDAT'];
            $praMitDest_rec['ORAINVIO'] = $pracom_rec['COMORA'];
            $praMitDest_rec['CODICE'] = "";
            $praMitDest_rec['NOME'] = $nominativo['Denominazione'];
            $praMitDest_rec['FISCALE'] = $nominativo['CodiceFiscale'];
            $praMitDest_rec['MAIL'] = $nominativo['Email'];
            $praMitDest_rec['INDIRIZZO'] = $nominativo['Indirizzo'] . " " . $nominativo['Civico'];
            $praMitDest_rec['CAP'] = $nominativo['CapComuneDiResidenza'];
            $praMitDest_rec['COMUNE'] = $nominativo['DescrizioneComuneDiResidenza'];
            $praMitDest_rec['TIPOINVIO'] = "";
            if ($primo) {
                $pracom_rec['COMCDE'] = $praMitDest_rec['CODICE'];
                $pracom_rec['COMNOM'] = $praMitDest_rec['NOME'];
                $pracom_rec['COMFIS'] = $praMitDest_rec['FISCALE'];
                $pracom_rec['COMMLD'] = $praMitDest_rec['MAIL'];
                $primo = false;
            }
            $praMitDest_tab[] = $praMitDest_rec;
        }

        /*
         * cerco se esiste già una comunicazione
         */
        if ($pracom_rec['COMTIP'] == "A") {
            $old_pracom_rec = $this->praLib->GetPracomA($this->keyPasso);
        } else {
            $old_pracom_rec = $this->praLib->GetPracomP($this->keyPasso);
        }
        if ($old_pracom_rec) {
            $Info = "Oggetto: cancello vecchio record PRACOM pratica " . $old_pracom_rec['COMNUM'] . " chiave " . $old_pracom_rec['COMPAK'];
            if (!$this->deleteRecord($this->PRAM_DB, 'PRACOM', $old_pracom_rec['ROWID'], $Info)) {
                Out::msgStop("Errore in Inserimento", "Inserimento da Protocollo fallito.");
                return false;
            }
        }

        $rowidPracom = $this->InserisciComunicazione($pracom_rec, $praMitDest_tab);
        if (!$rowidPracom) {
            Out::msgStop("Errore in Inserimento", "Inserimento da Protocollo fallito.");
            return false;
        }

        $pasdoc_rec = array("PASPRTCLASS" => "PRACOM", "PASPRTROWID" => $rowidPracom);
//$Allegati = $this->datiWS['RetValue']['Dati']['Allegati']['Allegato'];
        $Allegati = $dati['Allegati'];
        if ($Allegati) {
            if (!is_dir(itaLib::getPrivateUploadPath())) {
                if (!itaLib::createPrivateUploadPath()) {
                    Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                    return false;
                }
            }

            foreach ($Allegati as $Allegato) {
                $contentFile = base64_decode($Allegato['Stream']);
                $ext = $Allegato['Estensione'];
                $randName = md5(rand() * time()) . "." . $ext;
                $impFile = itaLib::getPrivateUploadPath() . "/" . $randName;
                $origFile = $Allegato['NomeFile'];
                file_put_contents($impFile, $contentFile);
                $this->aggiungiAllegato($randName, $impFile, $origFile, $Allegato['Note'], $pasdoc_rec);
            }
        }
        return true;
    }

    function ImportaDaDocumento($dati) {
        $dataPrt = $this->praLib->GetDataProtNormalizzata($this->datiWS['RetValue']); // nel formato GG/MM/AAAA
        $pracom_rec['COMTIP'] = $dati['Origine'];
        $pracom_rec['COMPAK'] = $this->keyPasso;
        $pracom_rec['COMNUM'] = $this->currGesnum;
        $pracom_rec['COMDAT'] = substr($dataPrt, 6, 4) . substr($dataPrt, 3, 2) . substr($dataPrt, 0, 2);
        $pracom_rec['COMORA'] = substr($dati['Data'], 11, 8);
        $pracom_rec['COMIDDOC'] = $dati['DocNumber'];
        $pracom_rec['COMDATADOC'] = substr($dataPrt, 6, 4) . substr($dataPrt, 3, 2) . substr($dataPrt, 0, 2);
        $pracom_rec['COMTIN'] = "";
        $pracom_rec['COMNOT'] = $dati['Oggetto'];
        $pracom_rec['COMRIF'] = "";
        $pracom_rec['COMIDMAIL'] = "";
        $metadati = array(
            'DatiProtocollazione' => $this->datiWS['RetValue']['DatiProtocollazione']
        );
        $metadati['DatiProtocollazione']['Importato'] = true;
        $pracom_rec['COMMETA'] = serialize($metadati);

        /*
         * Se è un arrivo, prendo il primo mittente dato che contempliamo un mittente singolo
         */
        $mittDest = $dati['MittentiDestinatari'];
        if ($pracom_rec['COMTIP'] == 'A') {
            $mittDest = array();
            $mittDest[] = $dati['MittentiDestinatari'][0];
        }
        $primo = true;
        $praMitDest_tab = array();
        foreach ($mittDest as $nominativo) {
            $praMitDest_rec = array();
            $praMitDest_rec['TIPOCOM'] = ($pracom_rec['COMTIP'] == 'A') ? 'M' : 'D';
            $praMitDest_rec['KEYPASSO'] = $this->keyPasso;
            $praMitDest_rec['DATAINVIO'] = $pracom_rec['COMDAT'];
            $praMitDest_rec['ORAINVIO'] = $pracom_rec['COMORA'];
            $praMitDest_rec['CODICE'] = "";
            $praMitDest_rec['NOME'] = $nominativo['Denominazione'];
            $praMitDest_rec['FISCALE'] = $nominativo['CodiceFiscale'];
            $praMitDest_rec['MAIL'] = $nominativo['Email'];
            $praMitDest_rec['INDIRIZZO'] = $nominativo['Indirizzo'] . " " . $nominativo['Civico'];
            $praMitDest_rec['CAP'] = $nominativo['CapComuneDiResidenza'];
            $praMitDest_rec['COMUNE'] = $nominativo['DescrizioneComuneDiResidenza'];
            $praMitDest_rec['TIPOINVIO'] = "";
            if ($primo) {
                $pracom_rec['COMCDE'] = $praMitDest_rec['CODICE'];
                $pracom_rec['COMNOM'] = $praMitDest_rec['NOME'];
                $pracom_rec['COMFIS'] = $praMitDest_rec['FISCALE'];
                $pracom_rec['COMMLD'] = $praMitDest_rec['MAIL'];
                $primo = false;
            }
            $praMitDest_tab[] = $praMitDest_rec;
        }

        /*
         * cerco se esiste già una comunicazione
         */
        if ($pracom_rec['COMTIP'] == "A") {
            $old_pracom_rec = $this->praLib->GetPracomA($this->keyPasso);
        } else {
            $old_pracom_rec = $this->praLib->GetPracomP($this->keyPasso);
        }
        if ($old_pracom_rec) {
            $Info = "Oggetto: cancello vecchio record PRACOM pratica " . $old_pracom_rec['COMNUM'] . " chiave " . $old_pracom_rec['COMPAK'];
            if (!$this->deleteRecord($this->PRAM_DB, 'PRACOM', $old_pracom_rec['ROWID'], $Info)) {
                Out::msgStop("Errore in Inserimento", "Inserimento da Protocollo fallito.");
                return false;
            }
        }

        $rowidPracom = $this->InserisciComunicazione($pracom_rec, $praMitDest_tab);
        if (!$rowidPracom) {
            Out::msgStop("Errore in Inserimento", "Inserimento da Protocollo fallito.");
            return false;
        }

        $pasdoc_rec = array("PASPRTCLASS" => "PRACOM", "PASPRTROWID" => $rowidPracom);
//$Allegati = $this->datiWS['RetValue']['Dati']['Allegati']['Allegato'];
        $Allegati = $dati['Allegati'];
        if ($Allegati) {
            if (!is_dir(itaLib::getPrivateUploadPath())) {
                if (!itaLib::createPrivateUploadPath()) {
                    Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                    return false;
                }
            }

            foreach ($Allegati as $Allegato) {
                $contentFile = base64_decode($Allegato['Stream']);
                $ext = $Allegato['Estensione'];
                $randName = md5(rand() * time()) . "." . $ext;
                $impFile = itaLib::getPrivateUploadPath() . "/" . $randName;
                $origFile = $Allegato['NomeFile'];
                file_put_contents($impFile, $contentFile);
                $this->aggiungiAllegato($randName, $impFile, $origFile, $Allegato['Note'], $pasdoc_rec);
            }
        }
        return true;
    }

    function getRicAllegatiWs($tipoWs, $returnEvent) {
        $praFascicolo = new praFascicolo($this->currGesnum);
        $praFascicolo->setChiavePasso($this->keyPasso);
        $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione($tipoWs, false, "P"); //non aggiungo filtri  non serve estrarre il base64
        $arrayDocRicevute = $praFascicolo->getRicevutePartenza($this->tipoProtocollo, false); //non aggiungo filtri  non serve estrarre il base64
        $arrayDocWsRicevute = array();
        foreach ($arrayDocRicevute['pramail_rec'] as $ricevuta) {
            $arrayDocWsRicevute[] = $ricevuta['ROWID'];
            $msgInfo = "Attenzione! Ci sono " . count($arrayDocWsRicevute) . " ricevute, tra Accettazioni e Avvenute-Consegna, da protocollare.<br>Verranno protocollate anche se non verrà selezionato nessun allegato di seguito.";
        }
        if (!$arrayDoc) {
            Out::msgInfo("Metti alla firma", "Allegati passo non trovati");
            return false;
        }
        $arrayDocWs = array();
        foreach ($arrayDoc['pasdoc_rec'] as $documento) {
            $arrayDocWs[] = $documento['ROWID'];
        }
        praRic::ricAllegatiWs($this->PRAM_DB, $arrayDocWs, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), $returnEvent, $msgInfo);
    }

    public function InsertPartenza() {
        $partenza_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK = '$this->keyPasso' AND COMTIP = 'P'", false);
        if (!$partenza_rec) {
            $partenza_rec = array();
            $partenza_rec['COMNUM'] = $this->currGesnum;
            $partenza_rec['COMPAK'] = $this->keyPasso;
            $partenza_rec['COMTIP'] = 'P';
            $insertP_Info = "Oggetto: inserimento comunicazione in partenza su PRACOM del passo " . $partenza_rec['COMPAK'];
            if (!$this->insertRecord($this->PRAM_DB, 'PRACOM', $partenza_rec, $insertP_Info)) {
                return false;
            }
        }
        return true;
    }

    function ordinaDestinatari() {
        $new_seq = 0;
        foreach ($this->destinatari as $key => $destinatario) {
            $new_seq += 10;
            $this->destinatari[$key]['SEQUENZA'] = $new_seq;
        }
    }

    function registraDatiProtocollo($numero, $tipo, $oggetto) {
        /*
         * Lettura ProArriDest e ProArriUff
         */
        $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
        $proArriDest = $proSubTrasmissioni->getProArriDest();
        $proArriUff = $proSubTrasmissioni->getProArriUff();
        /*
         * Lettura ANAPRO
         */
        include_once (ITA_BASE_PATH . '/apps/Pratiche/praLibAssegnazionePassi.class.php');
        $praLibAssegnazionePassi = new praLibAssegnazionePassi();
        $DatiProtocollo = $praLibAssegnazionePassi->getDatiProtocollo($this->keyPasso);
        $anapro_rec = $this->proLib->GetAnapro($numero, "codice", $tipo);
        /*
         *  Aggiorno Save Protocollo:
         */
        App::requireModel('proProtocolla.class');
        $protObj = new proProtocolla();
        $protObj->registraSave("Aggiornamento Passo", $anapro_rec['ROWID'], 'rowid');
        /*
         *  Aggiornamento Dati:
         */
        $anapro_rec['PRORDA'] = date('Ymd');
        $anapro_rec['PROROR'] = date('H:i:s');
        $anapro_rec['PROUOF'] = $DatiProtocollo['PROUOF']; // Ufficio creatore
        $update_Info = 'Oggetto: Aggiornamento  record trasmissione con protocollo n. ' . $numero;
        if (!$this->updateRecord($this->PROT_DB, 'ANAPRO', $anapro_rec, $update_Info)) {
            return false;
        }

        /*
         * Salvo Oggetto 
         * Aggiunta funzione per caratteri speciali derivanti da copia e incolla 
         */
        $Oggetto = itaLib::decodeUnicodeCharacters($oggetto);
        $Oggetto = str_replace('"', '', $Oggetto);

        $anaogg_rec['OGGNUM'] = $numero;
        $anaogg_rec['OGGOGG'] = $Oggetto;
        $anaogg_rec['OGGPAR'] = praLibAssegnazionePassi::TYPE_ASSEGNAZIONI;

        /*
         * CANCELLO E INSERISCO LA TABELLA OGGETTI
         */
        $anaogg_old = $this->proLib->GetAnaogg($numero, praLibAssegnazionePassi::TYPE_ASSEGNAZIONI);
        if ($anaogg_old) {
            $delete_Info = 'Oggetto ANAOGG: ' . $anaogg_old['OGGNUM'] . " " . $anaogg_old['OGGOGG'];
            if (!$this->deleteRecord($this->PROT_DB, 'ANAOGG', $anaogg_old['ROWID'], $delete_Info, 'ROWID', false)) {
                return false;
            }
        }
        /* Salvo Oggetto */
        $insert_Info = 'Inserimento: ' . $anaogg_rec['OGGNUM'] . ' ' . $anaogg_rec['OGGDE1'];
        if (!$this->insertRecord($this->PROT_DB, 'ANAOGG', $anaogg_rec, $insert_Info)) {
            return false;
        }

        $anades_tab = $this->proLib->GetAnades($numero, 'codice', true, $tipo, '');
        if ($anades_tab) {
            foreach ($anades_tab as $key => $anades_rec) {
                if (!$this->deleteRecord($this->PROT_DB, 'ANADES', $anades_rec['ROWID'], '', 'ROWID', false)) {
                    return false;
                }
            }
        }

        /*
         * Inserisco ANADES
         */
        if ($proArriDest) {
            foreach ($proArriDest as $key => $record) {
                $anades_rec = array();
                $anades_rec['DESNUM'] = $numero;
                $anades_rec['DESPAR'] = $tipo;
                $anades_rec['DESCOD'] = $record['DESCOD'];
                $anades_rec['DESNOM'] = $record['DESNOM'];
                $anades_rec['DESIND'] = $record['DESIND'];
                $anades_rec['DESCAP'] = $record['DESCAP'];
                $anades_rec['DESCIT'] = $record['DESCIT'];
                $anades_rec['DESPRO'] = $record['DESPRO'];
                $anades_rec['DESDAT'] = $record['DESDAT'];
                $anades_rec['DESDAA'] = $record['DESDAA'];
                $anades_rec['DESDUF'] = $record['DESDUF'];
                $anades_rec['DESANN'] = $record['DESANN'];
                $anades_rec['DESMAIL'] = $record['DESMAIL'];
                $anades_rec['DESSER'] = $record['DESSER'];
                $anades_rec['DESCUF'] = $record['DESCUF'];
                $anades_rec['DESGES'] = $record['DESGES'];
                $anades_rec['DESRES'] = $record['DESRES'];
                $anades_rec['DESRUOLO'] = $record['DESRUOLO'];
                $anades_rec['DESTERMINE'] = $record['TERMINE'];
                $anades_rec['DESIDMAIL'] = $record['DESIDMAIL'];
                $anades_rec['DESINV'] = $record['DESINV'];
                $anades_rec['DESTIPO'] = "T";
                $anades_rec['DESORIGINALE'] = $record['DESORIGINALE'];
                $anades_rec['DESFIS'] = $record['DESFIS'];
                $insert_Info = 'Inserimento: ' . $anades_rec['DESNUM'] . ' ' . $anades_rec['DESNOM'];
                if (!$this->insertRecord($this->PROT_DB, 'ANADES', $anades_rec, $insert_Info)) {
                    Out::msgStop("Aggiornamento", "Errore inserimento assegnazioni");
                    return false;
                }
            }
        }

        /* Salvo Firmatario */
        $anades_rec = array();
        $anades_rec['DESNUM'] = $anapro_rec['PRONUM'];
        $anades_rec['DESPAR'] = $anapro_rec['PROPAR'];
        $anades_rec['DESTIPO'] = "M";
        $anades_rec['DESCOD'] = $DatiProtocollo['CODICE'];
        $anades_rec['DESCUF'] = $DatiProtocollo['UFFICIO']; //nuovo controllo con codice ufficio differenziato
        $anades_rec['DESNOM'] = $DatiProtocollo['DENOMINAZIONE'];
        $anades_rec['DESCONOSCENZA'] = 0;
        //$insert_Info = 'Inserimento: ' . $anades_rec['DESNUM'] . ' ' . $anades_rec['DESNOM'];
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'ANADES', 'ROWID', $anades_rec);
        } catch (Exception $exc) {
            Out::msgStop("Errore nell'inserimento del firmatario " . $exc->getTraceAsString());
            return false;
        }

        /*
         * Salvataggio uffici
         */
        $uffpro_tab = $this->proLib->GetUffpro($numero, 'codice', true, $tipo);
        foreach ($uffpro_tab as $key => $uffpro_rec) {
            if (!$this->deleteRecord($this->PROT_DB, 'UFFPRO', $uffpro_rec['ROWID'], '', 'ROWID', false)) {
                return false;
            }
        }
        foreach ($proArriUff as $key => $record) {
            $uffpro_rec = array();
            $uffpro_rec['PRONUM'] = $numero;
            $uffpro_rec['UFFPAR'] = $tipo;
            $uffpro_rec['UFFCOD'] = $record['UFFCOD'];
            $uffpro_rec['UFFFI1'] = $record['UFFFI1'];
            $insert_Info = 'Inserimento: ' . $uffpro_rec['PRONUM'] . ' ' . $uffpro_rec['UFFCOD'];
            if (!$this->insertRecord($this->PROT_DB, 'UFFPRO', $uffpro_rec, $insert_Info)) {
                Out::msgStop("Aggiornamento", "Errore inserimento uffici");
                return false;
            }
        }

        /*
         * Sincronizzo ARICTE
         */
        include_once ITA_BASE_PATH . '/apps/Protocollo/proIter.class.php';
        $anapro_rec = $this->proLib->GetAnapro($numero, "codice", $tipo);
        if ($anapro_rec) {
            $iter = proIter::getInstance($this->proLib, $anapro_rec);
            $iter->sincIterProtocollo();
        }

        return true;
    }

    public function checkFirmaCds($flagCds) {
        $processoIniziato = false;
        if ($flagCds == 1) {
            foreach ($this->passAlle as $allegato) {
                $pasdoc_recCtr = array();
                if ($allegato['PASROWIDBASE'] != 0) {
                    $pasdoc_recCtr = $this->praLib->GetPasdoc($allegato['PASROWIDBASE'], "ROWID");
                    if ($pasdoc_recCtr) {
                        $processoIniziato = true;
                        break;
                    }
                }
            }
        }
        return $processoIniziato;
    }

    function inizializzaForm() {
        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
        $this->tipoProtocollo = $PARMENTE_rec['TIPOPROTOCOLLO'];

        /*
         * Controllo se l'utente ha configurati i parametri per protocollare in altro ente
         */
        $enteProtRec_rec = $this->accLib->GetEnv_Utemeta(App::$utente->getKey('idUtente'), 'codice', 'ITALSOFTPROTREMOTO');
        if ($enteProtRec_rec) {
            $meta = unserialize($enteProtRec_rec['METAVALUE']);
            if ($meta['TIPO'] && $meta['URLREMOTO']) {
                $this->tipoProtocollo = $meta['TIPO'];
            }
        }


        //
        $this->CreaCombo();
        $this->selectCalendari();

        /* SIMONE: COMMENTATO PERCHE' NON MI INIZIALIZZA IL PASSO       
          $this->returnModel = $_POST[$this->nameForm . "_returnModel"];
          $this->returnMethod = $_POST[$this->nameForm . "_returnMethod"];
          $this->daMail = $_POST[$this->nameForm . "_daMail"];
          $this->pagina = $_POST['pagina'];
          $this->sql = $_POST['sql'];
          $this->selRow = $_POST['selRow'];
          Out::setDialogOption($this->nameForm, 'title', "'" . $_POST[$this->nameForm . "_title"] . "'");
         */

        // open mode

        switch ($_POST['modo']) {
            case "edit" :
                if ($_POST['rowid']) {
                    $this->praReadOnly = $_POST['praReadonly'];
                    $this->daTrasmissioni = $_POST['daTrasmissioni'];
                    $this->dettaglio($_POST['rowid'], 'rowid');
                }
                break;
            case "add" :
                if ($_POST['docCommercio']) {
                    $this->docCommercio = $_POST['docCommercio'];
                }
                if ($_POST['procedimento']) {
                    $this->currGesnum = $_POST['procedimento'];
                    $this->apriInserimento($this->currGesnum);
                }
                break;
            case "duplica" :
                if ($_POST['procedimento']) {
                    $this->currGesnum = $_POST['procedimento'];
                    $this->duplica = true;
                    $this->apriDuplica($_POST['last_propas_rec']);
                }
                break;
        }
    }

    public function valorizzaInfoDestinatari($propas_rec) {
        $html = "";
        if ($propas_rec['PROFLCDS'] == 1) {
            $html = "Cliccare e trascinare i destinatari secondo l'ordine di firma desiderato.";
            $processoIniziato = $this->checkFirmaCds($propas_rec['PROFLCDS']);
            if ($processoIniziato === true) {
                $html = "Impossibile spostare i destinatari. Processo di firma gìà iniziato.";
            }
        }
        Out::html($this->nameForm . "_Info", $html);
    }

    public function checkFirmatoCds($dest) {
        $icon = "";
        $arrayAllegatiCds = array();
        foreach ($this->passAlle as $key => $alle) {
            if ($alle['PASFLCDS'] == 1) {
                $arrayAllegatiCds[] = $alle;
            }
        }
        $countAllegati = count($arrayAllegatiCds);
        $i = 0;
        foreach ($arrayAllegatiCds as $key => $alle) {
            $sql = "SELECT * FROM PASDOC WHERE PASKEY LIKE '" . $this->currGesnum . "%' AND PASROWIDBASE = " . $alle['ROWID'] . " AND PASUTELOG = 'PRAMITTDEST:" . $dest['ROWID'] . "'";
            $alleFirmatoDaUtente = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($alleFirmatoDaUtente) {
                $i++;
            }
        }
        if ($i != 0 && $countAllegati == $i) {
            $icon = "<span class=\"ita-icon ita-icon-shield-green-24x24\" title=\"File firmato caricato\"></span>";
        }
        return $icon;
    }

    public function getArrayAzioniCds($flagCds, $doc) {
        $ext = pathinfo($doc['FILEPATH'], PATHINFO_EXTENSION);
        if ($ext == 'p7m' && $doc['PASTIPO'] != "FIR_CDS") {
            return array();
        }
        $alleFirmati = array();
        $processoIniziato = $this->checkFirmaCds($flagCds);
        if ($processoIniziato === true) {
            if ($doc['PASTIPO'] == "FIR_CDS") {
                $arrayAzioni = array(
                    'F7-Visualizza Firme' => array('id' => $this->nameForm . '_VisualizzaFirmeCDS', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f7"),
                );
            } elseif ($doc['PASFLCDS'] == 1) {
                $alleFirmati = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PASDOC WHERE PASKEY LIKE '$this->currGesnum%' AND PASROWIDBASE = '" . $doc['ROWID'] . "'", true);
                if ($alleFirmati) {
                    $arrayAzioni = array(
                        'F7-Visualizza Firme' => array('id' => $this->nameForm . '_VisualizzaFirmeCDS', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f7"),
                    );
                }
            }
        } else {
            if ($flagCds == 1) {
                if ($doc['PASFLCDS'] == 1) {
                    $arrayAzioni = array(
                        'F6-Disattiva CDS' => array('id' => $this->nameForm . '_DisattivaCDS', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-group-32x32'", 'model' => $this->nameForm, 'shortCut' => "f6"),
                    );
                } else {
                    $arrayAzioni = array(
                        'F5-Attiva CDS' => array('id' => $this->nameForm . '_AttivaCDS', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-group-32x32'", 'model' => $this->nameForm, 'shortCut' => "f5"),
                    );
                }
            }
        }

        return $arrayAzioni;
    }

    public function getArrayDataTreeAllegato($dataDetail_rec, $duplica, $inc, $flagCdsPasso, $flagDwOnlinePasso) {
        $pramPath = $this->praLib->SetDirectoryPratiche(substr($this->keyPasso, 0, 4), $this->keyPasso, "PASSO", false);

        if ($duplica) {
            /*
             * Se sono in duplica ultimo passo, tolgo l'evidenziazione. (Per francesca freschi il 12/04/2017
             */
            $dataDetail_rec['PASEVI'] = 0;
        }
        $strDest = "";
        $ext = pathinfo($dataDetail_rec['PASFIL'], PATHINFO_EXTENSION);
        $edit = "";
        if (strtolower($ext) == "zip") {
            $edit = "<span class=\"ita-icon ita-icon-winzip-24x24\">Estrai File Zip</span>";
        }

        $preview = $this->GetImgPreview($ext, $pramPath, $dataDetail_rec);
        $stato = $this->praLib->GetStatoAllegati($dataDetail_rec['PASSTA']);
        //Valorizzo Tabella
        $arrayDataTmp = array();
        $arrayDataTmp['PROV'] = $inc;
        $arrayDataTmp['EDIT'] = $edit;
        $arrayDataTmp['RANDOM'] = $dataDetail_rec['PASFIL'];

        if ($dataDetail_rec['PASNAME']) {
            $arrayDataTmp['NAME'] = $dataDetail_rec['PASNAME'];
        } else {
            $arrayDataTmp['NAME'] = $dataDetail_rec['PASFIL'];
        }
        $arrayDataTmp['INFO'] = $dataDetail_rec['PASNOT'];
        $arrayDataTmp['SIZE'] = $this->praLib->formatFileSize(filesize($pramPath . "/" . $dataDetail_rec['PASFIL']));
        $arrayDataTmp['SOST'] = $this->praLib->CheckSostFile($this->currGesnum, $dataDetail_rec['PASSHA2'], $dataDetail_rec['PASSHA2SOST']);
        $arrayDataTmp['PREVIEW'] = $preview;
        if ($dataDetail_rec['PASLOG'] == "" && strtolower($ext) == "pdf") {
            $dataDetail_rec['PASLOG'] = "Clicca qui per importare i campi aggiuntivi";
        } elseif ($dataDetail_rec['PASLOG'] == "" && strtolower($ext) == "p7m") {
            $dataDetail_rec['PASLOG'] = "Clicca per verificare il file";
        }
        $arrayDataTmp['STATO'] = $dataDetail_rec['PASLOG'];
        $arrayDataTmp['EVIDENZIA'] = "<div class=\"ita-html\"><span class=\"ita-icon ita-icon-evidenzia-24x24 ita-colorpicker {type: 'divColor',swatches:true}\">Evidenzia Allegato</span></div>";

        if ($dataDetail_rec['PASLOCK'] == 1) {
            $arrayDataTmp['LOCK'] = "<span class=\"ita-icon ita-icon-lock-24x24\">Sblocca Allegato</span>";
        } else {
            $arrayDataTmp['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-24x24\">Blocca Allegato</span>";
        }

        $arrayDataTmp['STATOALLE'] = $stato;
        $arrayDataTmp['PUBBLICA'] = "";
        //if ($propas_rec['PROPUBALL'] == 1) {
        if ($dataDetail_rec['PASPUB'] == 1) {
            $arrayDataTmp['PUBBLICA'] = "<span class=\"ita-icon ita-icon-publish-24x24\">Allegato pubblicato</span>";
        } else {
            $arrayDataTmp['PUBBLICA'] = "<span class=\"ita-icon ita-icon-no-publish-24x24\">Allegato non pubblicato</span>";
        }
        //}


        if ($flagDwOnlinePasso == 1) {
            $arrayDataTmp['PUBQR'] = $this->praLib->getFlagPASDWONLINE($dataDetail_rec['PASDWONLINE']);
        }
        /*
         * Se evidenziato cambio il nome dell'allegato in rosso
         */
        $color = '#' . str_pad(dechex($dataDetail_rec['PASEVI']), 6, "0", STR_PAD_LEFT);
        if ($dataDetail_rec["PASEVI"] == 1) {
            if ($dataDetail_rec['PASLOG'] == "") {
                $dataDetail_rec['PASLOG'] = " ";
            }
            if ($dataDetail_rec['PASNAME']) {
                $arrayDataTmp['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $dataDetail_rec['PASNAME'] . "</p>";
            } else {
                $arrayDataTmp['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $dataDetail_rec['PASFIL'] . "</p>";
            }
        } elseif ($dataDetail_rec['PASEVI'] != 1 && $dataDetail_rec['PASEVI'] != 0 && !empty($dataDetail_rec['PASEVI'])) {
            //$color = '#' . str_pad(dechex($dataDetail_rec['PASEVI']), 6, "0", STR_PAD_LEFT);
            if ($dataDetail_rec['PASLOG'] == "") {
                $dataDetail_rec['PASLOG'] = " ";
            }
            if ($dataDetail_rec['PASNAME']) {
                $arrayDataTmp['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $dataDetail_rec['PASNAME'] . "</p>";
            } else {
                $arrayDataTmp['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $dataDetail_rec['PASFIL'] . "</p>";
            }
        }

        /*
         * Classificazione
         */
        $Anacla_rec = $this->praLib->GetAnacla($dataDetail_rec['PASCLAS']);
        $arrayDataTmp['CLASSIFICAZIONE'] = $Anacla_rec['CLADES'];

        /*
         * Destinazioni
         */
        $strDest = $this->caricaDestinazioniAllegato($dataDetail_rec['PASDEST']);
        $arrayDataTmp['DESTINAZIONI'] = $strDest;

//        $arrayDest = unserialize($dataDetail_rec['PASDEST']);
//        if (is_array($arrayDest)) {
//            foreach ($arrayDest as $dest) {
//                $Anaddo_rec = $this->praLib->GetAnaddo($dest);
//                $strDest .= $Anaddo_rec['DDONOM'] . "<br>";
//            }
//        }
//Blocco gli allegati protocollati o messi alla firma PASSO
        if ($dataDetail_rec['PASPRTROWID'] != 0 && $dataDetail_rec['PASPRTCLASS'] == "PRACOM") {
            $pracom_rec = $this->praLib->GetPracom($dataDetail_rec['PASPRTROWID'], "rowid");
            if ($pracom_rec['COMPRT']) {
                $dataPrt = substr($pracom_rec['COMDPR'], 6, 2) . "/" . substr($pracom_rec['COMDPR'], 4, 2) . "/" . substr($pracom_rec['COMDPR'], 0, 4);
                $arrayDataTmp['PROTOCOLLO'] = $pracom_rec['COMPRT'] . " del <br>$dataPrt";
                $arrayDataTmp['LOCK'] = "<span class=\"ita-icon ita-icon-lock-24x24\">Allegato Bloccato per Protocollazione PASSO</span>";
            } elseif ($pracom_rec['COMIDDOC']) {
                $dataPrt = substr($pracom_rec['COMDATADOC'], 6, 2) . "/" . substr($pracom_rec['COMDATADOC'], 4, 2) . "/" . substr($pracom_rec['COMDATADOC'], 0, 4);
                $arrayDataTmp['PROTOCOLLO'] = $pracom_rec['COMIDDOC'] . " del <br>$dataPrt";
                $arrayDataTmp['LOCK'] = "<span class=\"ita-icon ita-icon-lock-24x24\">Allegato Bloccato per Invio al Protocollo per la firma</span>";
            }
        }
//Blocco gli allegati protocollati PRATICA
        if ($dataDetail_rec['PASPRTROWID'] != 0 && $dataDetail_rec['PASPRTCLASS'] == "PROGES") {
            $proges_rec = $this->praLib->GetProges($dataDetail_rec['PASPRTROWID'], "rowid");
            if ($proges_rec['GESNPR']) {
                $meta = unserialize($proges_rec['GESMETA']);
                $dataPrt = $meta['DatiProtocollazione']['Data']['value'];
                $arrayDataTmp['PROTOCOLLO'] = $proges_rec['GESNPR'] . " del <br>$dataPrt";
                $arrayDataTmp['LOCK'] = "<span class=\"ita-icon ita-icon-lock-24x24\">Allegato Bloccato per Protocollazione PRATICA</span>";
            }
        }

        $iconCds = $this->praLib->getIconCds($dataDetail_rec['PASFLCDS'], $flagCdsPasso, $dataDetail_rec['PASTIPO']);

        $arrayDataTmp['FILEINFO'] = $dataDetail_rec['PASNOT'];
        $arrayDataTmp['FILEPATH'] = $pramPath . "/" . $dataDetail_rec['PASFIL'];
        $arrayDataTmp['FILENAME'] = $dataDetail_rec['PASFIL'];
        $arrayDataTmp['FILEORIG'] = $dataDetail_rec['PASNAME'];
        $arrayDataTmp['PROVENIENZA'] = $dataDetail_rec['PASCLA'];
        $arrayDataTmp['TESTOBASE'] = $dataDetail_rec['PASTESTOBASE'];
        $arrayDataTmp['PASLOCK'] = $dataDetail_rec['PASLOCK'];
        $arrayDataTmp['PASEVI'] = $dataDetail_rec['PASEVI'];
        $arrayDataTmp['PASPUB'] = $dataDetail_rec['PASPUB'];
        $arrayDataTmp['PASDWONLINE'] = $dataDetail_rec['PASDWONLINE'];
        $arrayDataTmp['PASDEST'] = $dataDetail_rec['PASDEST'];
        $arrayDataTmp['PASNOTE'] = $dataDetail_rec['PASNOTE'];
        $arrayDataTmp['PASMETA'] = $dataDetail_rec['PASMETA'];
        $arrayDataTmp['PASCLAS'] = $dataDetail_rec['PASCLAS'];
        $arrayDataTmp['PASFLCDS'] = $dataDetail_rec['PASFLCDS'];
        $arrayDataTmp['PASUTELOG'] = $dataDetail_rec['PASUTELOG'];
        $arrayDataTmp['PASROWIDBASE'] = $dataDetail_rec['PASROWIDBASE'];
        $arrayDataTmp['PASTIPO'] = $dataDetail_rec['PASTIPO'];
        $arrayDataTmp['CDS'] = $iconCds;
        $arrayDataTmp['PASRIS'] = $dataDetail_rec['PASRIS'];
        $arrayDataTmp['RISERVATO'] = $this->praLibRiservato->getIconRiservato($dataDetail_rec['PASRIS']);
        if ($duplica == false) {
            $arrayDataTmp['ROWID'] = $dataDetail_rec['ROWID'];
        }
//        $arrayDataTmp['level'] = $level;
//        $arrayDataTmp['parent'] = $parent;
//        $arrayDataTmp['isLeaf'] = 'true';
        return $arrayDataTmp;
    }

    public function inserisciAnapro($keyPasso) {
        $ritorno = array();
        $ritorno['Status'] = "0";
        $ritorno['Message'] = "Anapro inserito correttamente per il passo $keyPasso";
        $ritorno['RetValue'] = true;
        //
        include_once (ITA_BASE_PATH . '/apps/Pratiche/praLibAssegnazionePassi.class.php');
        $praLibAssegnazionePassi = new praLibAssegnazionePassi();
        $DatiProtocollo = $praLibAssegnazionePassi->getDatiProtocollo($keyPasso);
        $rowidAnapro = $praLibAssegnazionePassi->insertAnapro($DatiProtocollo);
        if ($rowidAnapro === false) {
            $ritorno['Status'] = "-1";
            $ritorno['Message'] = "Errore inserimento record ANAPRO per il passo $keyPasso";
            $ritorno['RetValue'] = false;
            return $ritorno;
        }
        $anapro_rec = $this->proLib->GetAnapro($rowidAnapro, "rowid");
        $passo_rec = $this->praLib->GetPropas($keyPasso, "propak");
        $passo_rec['PASPRO'] = $anapro_rec['PRONUM'];
        $passo_rec['PASPAR'] = $anapro_rec['PROPAR'];
        $update_Info = "Oggetto: Aggiorno numero prot " . $passo_rec['PASPRO'] . " tipo " . $passo_rec['PASPAR'] . " su passo $keyPasso";
        if (!$this->updateRecord($this->PRAM_DB, 'PROPAS', $passo_rec, $update_Info)) {
            $ritorno['Status'] = "-1";
            $ritorno['Message'] = "Aggiornamento passo dopo inserimento su ANAPRO fallito per il passo $keyPasso";
            $ritorno['RetValue'] = false;
            return $ritorno;
        }
        $ritorno['anapro_rec'] = $anapro_rec;
        return $ritorno;
    }

    public function caricaTabPassi($arrayStatiTab) {

        foreach ($arrayStatiTab as $panel) {
            switch ($panel['Stato']) {
                case "Show":
                    Out::showTab($this->nameForm . "_" . $panel['Id']);
                    break;
                case "Hide":
                    Out::hideTab($this->nameForm . "_" . $panel['Id']);
                    break;
                case "Add":
                    $generator = new itaGenerator();
                    $retHtml = $generator->getModelHTML(basename($panel['FileXml'], ".xml"), false, $this->nameForm, false);
                    Out::tabAdd($this->nameForm . '_tabProcedimento', '', $retHtml);
                    break;
                case "Remove":
                    Out::tabRemove($this->nameForm . '_tabProcedimento', $this->nameForm . "_" . basename($panel['FileXml'], ".xml"));
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

    public function caricaTestoClass($Rowid = '') {
        if (!$Rowid) {
            $sql = "SELECT ROWID_DOC_CLASSIFICAZIONE FROM PROPAS WHERE PROPAK = '$this->keyPasso'";
            $RowidDoc = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            $Rowid = $RowidDoc['ROWID_DOC_CLASSIFICAZIONE'];
        }
        $DocClass = $this->docLib->getClassificazioneDoc($Rowid);
        if ($DocClass['CODICEDOC']) {
            $Documento = $this->docLib->getDocumenti($DocClass['CODICEDOC']);
            $this->passAlle = $this->praLib->caricaTestoBase_Generico($this->keyPasso, $this->passAlle, $Documento['ROWID'], 'rowid');
            $this->ContaSizeAllegati($this->passAlle);
            $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');
            $this->bloccoAllegatiRiservati();
            $this->BloccaDescAllegati();
        }
        $DocClassFigli_rec = $this->docLib->getClassificazioneDoc($Rowid, 'figli', true);
        if ($DocClassFigli_rec) {
            foreach ($DocClassFigli_rec as $DocClassFigli) {
                $Documento = $this->docLib->getDocumenti($DocClassFigli['CODICEDOC']);
                if (!$Documento['ROWID']) {
                    continue;
                }
                $this->passAlle = $this->praLib->caricaTestoBase_Generico($this->keyPasso, $this->passAlle, $Documento['ROWID'], 'rowid');
                $this->ContaSizeAllegati($this->passAlle);
                $this->CaricaGriglia($this->gridAllegati, $this->passAlle, '1', '100000');
                $this->bloccoAllegatiRiservati();
                $this->BloccaDescAllegati();
            }
        }
        return true;
    }

    public function checkFrimaAllegato($prorpa = true, $Rowid = '') {
        if ($Rowid == 0) {
            return true;
        }
        $propas_rec = $this->praLib->GetPropas($this->keyPasso);
        if ($prorpa == true) {
            if ($_POST[$this->nameForm . '_PROPAS']['PRORPA'] != $propas_rec['PRORPA']) {
                if (!$this->ControllaFrimaAllegato($propas_rec, $Rowid)) {
                    return false;
                }
            }
        } else {
            if (!$this->ControllaFrimaAllegato($propas_rec, $Rowid)) {
                return false;
            }
        }

        return true;
    }

    public function ControllaFrimaAllegato($propas_rec, $Rowid) {
        $proLibAllegati = new proLibAllegati();
        // $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
        $count = 0;
        if ($Rowid) {
            $pasdoc_rec = $this->praLib->GetPasdoc($Rowid, "ROWID");
            $keyLink = 'PRAM.PASDOC.' . $Rowid;
            $AnaDoc_rec = $this->proLib->GetAnaDocFromDocLink($propas_rec['PASPRO'], $propas_rec['PASPAR'], $keyLink);
            $docfirma_check = $proLibAllegati->GetDocfirma($AnaDoc_rec['ROWID'], 'rowidanadoc');
            if ($docfirma_check && $docfirma_check['FIRDATA'] == '') {
                return false;
            }
            return true;
        }
        foreach ($this->passAlle as $key => $allegato) {
            if (!$allegato['ROWID']) {
                continue;
            }  // Controllare di ogni allegato se è presente il blocco alla firma se è stato modificato il responsabile del passo
            $pasdoc_rec = $this->praLib->GetPasdoc($allegato['ROWID'], "ROWID");
            $keyLink = 'PRAM.PASDOC.' . $pasdoc_rec['ROWID'];
            $AnaDoc_rec = $this->proLib->GetAnaDocFromDocLink($propas_rec['PASPRO'], $propas_rec['PASPAR'], $keyLink);
            $docfirma_check = $proLibAllegati->GetDocfirma($AnaDoc_rec['ROWID'], 'rowidanadoc');
            if ($docfirma_check && $docfirma_check['FIRDATA'] == '') {
                $count++;
                break;
            }
        }
        if ($count > 0) {
            return false;
        }
        return true;
    }

    public function togliAllaFirma($RowidAllegato) {
        $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
        $anapro_rec = $this->proLib->GetAnapro($propas_rec['PASPRO'], "codice", $propas_rec['PASPAR']);
        $keyLink = 'PRAM.PASDOC.' . $RowidAllegato;
        $anadoc_rec = $this->proLib->GetAnaDocFromDocLink($propas_rec['PASPRO'], $propas_rec['PASPAR'], $keyLink);
        $proLibAllegati = new proLibAllegati();
        $docfirma_tab = $proLibAllegati->GetDocfirma($anadoc_rec['ROWID'], 'rowidanadoc', true);
        if (!$docfirma_tab) {
            return true;
        }
        if (!$this->deleteRecord($this->proLib->getPROTDB(), 'ANADOC', $anadoc_rec['ROWID'], $delete_Info)) {
            Out::msgStop("Gestione Cancellazione", "Errore in cancellazione file.");
            return false;
        }
        foreach ($docfirma_tab as $docfirma_rec) {
            $delete_Info = 'Oggetto: Cancellazione Richiesta di firma ' . $anadoc_rec['DOCKEY'];
            if (!$this->deleteRecord($this->proLib->getPROTDB(), "DOCFIRMA", $docfirma_rec['ROWID'], $delete_Info)) {
                Out::msgStop("Attenzione", "Cancellazione Richieste di firma non avvenuta.");
                return false;
            }
        }
        $iter = proIter::getInstance($this->proLib, $anapro_rec);
        $iter->sincronizzaIterFirma('cancella', $docfirma_rec['ROWIDARCITE']);
        return true;
    }

    public function ControlloDatiProtocolloPasso() {
        /* Controllo responsabile alla firma variato */
        if (!$this->checkFrimaAllegato()) {
            Out::msgStop("Errore", "Non è possibile modificare Responsabile con Allegato alla Frima.<br>Rimuovere il documento alla firma.");
            return false;
        }
        /* Controllo firmatario configurato correttamente */
        $praFascicolo = new praFascicolo($this->currGesnum);
        $destinatario = $praFascicolo->setDestinatarioProtocollo($_POST[$this->nameForm . '_PROPAS']['PRORPA']);
        if (!$destinatario) {
            Out::msgStop("Errore", "Profilo responsabile del passo incompleto: codice soggetto interno mancante.");
            return false;
        }
        $uffici = $praFascicolo->setUfficiProtocollo($destinatario['CodiceDestinatario']);
        if (!$uffici) {
            Out::msgStop("Errore", "Profilo responsabile del passo incompleto: ufficio soggetto interno mancante.");
            return false;
        }
        /* Controllo ufficio utente creatore configurato */
        $ufficioDefault = $this->proLib->GetUfficioUtentePredef();
        if (!$ufficioDefault) {
            Out::msgStop("Errore", "Profilo utente incompleto: ufficio o soggetto interno mancante.");
            return false;
        }
        return true;
    }

    public function ChiudiTrasmissioniProtocolloPasso($propas_rec, $soloInGest = true, $soloVerifica = false) {
        /*
         * quando chiudo il passo, se ci sono, chiudo anche gli iter ad esso collegati
         */
        if ($propas_rec['PASPRO'] != 0 && $propas_rec['PASPAR']) {
            $arcite_tab = $this->proLib->GetIterAperti($propas_rec['PASPRO'], $propas_rec['PASPAR'], $soloInGest);
            if ($arcite_tab) {
                if ($soloVerifica) {
                    return false;
                }
                $iter = proIter::getInstance($this->proLib, $propas_rec['PASPRO'], $propas_rec['PASPAR']);
                foreach ($arcite_tab as $key => $arcite_rec) {
                    if (!$iter->chiudiIterNode($arcite_rec)) {
                        
                    }
                }
            }
        }
        return true;
    }

    public function CheckAssegnazionePasso() {
        $flagAssegnazionePasso = $this->praLibPasso->getFlagAssegnazionePasso();
        if ($flagAssegnazionePasso == false) {
            return false; // Flag Su Parametri vari Fascicolo Spento
        }
        if (!$this->keyPasso) {
            return true;
        }
        $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
        if ($propas_rec['PROCLT']) {
            $clt_rec = $this->praLib->GetPraclt($propas_rec['PROCLT']);
            if ($clt_rec['CLTGESTPANEL'] == 0) {
                return true; // parametro gestione pannelli spento su tipo passo
            }
            $Param_rec = $this->praLib->decodParametriPasso($clt_rec['CLTMETAPANEL']);
            $AssegnazionePasso = false;
            foreach ($Param_rec as $Param) {
                if ($Param['DESCRIZIONE'] == 'Assegnazioni' && $Param['DEF_STATO'] == '1') {
                    $AssegnazionePasso = true;
                }
            }
            return $AssegnazionePasso;
        }
        return true; // Tipo Passo non settato
    }

    public function caricaDestinatarioFromAnamed($codice, $tipo = "codice") {
        $anamed_rec = $this->DecodAnamedComP($codice, $tipo);
        $idx = array_search($anamed_rec['MEDCOD'], array_column($this->destinatari, "CODICE"));
        if ($idx !== false) {
            return false;
        }
        $sogg = array();
        $sogg['ROWID'] = 0;
        $sogg['CODICE'] = $anamed_rec['MEDCOD'];
        $sogg['NOME'] = "<span style = \"color:orange;\">" . $anamed_rec['MEDNOM'] . "</span>";
        $sogg['FISCALE'] = $anamed_rec['MEDFIS'];
        $sogg['INDIRIZZO'] = $anamed_rec['MEDIND'];
        $sogg['COMUNE'] = $anamed_rec['MEDCIT'];
        $sogg['CAP'] = $anamed_rec['MEDCAP'];
        $sogg['DATAINVIO'] = "";
        $sogg['PROVINCIA'] = $anamed_rec['MEDPRO'];
        $sogg['MAIL'] = $anamed_rec['MEDEMA'];
        $this->destinatari[] = $sogg;
        return true;
    }

    public function caricaDestinazioniAllegato($destinazioni) {
        $arrayDest = unserialize($destinazioni);
        $strDest = "";
        if (is_array($arrayDest)) {
            $msgDestTrovati = "";
            foreach ($arrayDest as $dest) {
                $Anaddo_rec = $this->praLib->GetAnaddo($dest);
                $strDest .= $Anaddo_rec['DDONOM'] . "<br>";
                if (!$this->caricaDestinatarioFromAnamed($dest, "codice")) {
                    $msgDestTrovati .= $Anaddo_rec['DDONOM'] . "<br>";
                }
            }
            if ($msgDestTrovati) {
                Out::msgInfo("Importazione Destinatari", "I seguenti destinatari non sono stati importati perchè già presenti nella lista:<br>$msgDestTrovati");
            }
            $this->CaricaGriglia($this->gridAltriDestinatari, $this->destinatari, "1", "100");
        }
        return $strDest;
    }

    public function navigazione($datiWf, $evento = 'avanti') {
        $propakNew = '';
        //Out::msgInfo("datiwf", print_r($datiWf,true));
        // Trovare il passo attuale e vedere che tipo è (Domanda Semplice ; Domanda Multipla ; Raccolta Dati ecc..)
        $passoCorrente = $datiWf->getPassoCorrente();

        //Cerco record di PROPAS da aggiornare
//        $sql = "SELECT * FROM PROPAS "
//                . " WHERE PROPAS.PROPAK = '" . $passoCorrente['PROPAK'] . "' ";
//
//        $propas_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
        $propas_rec = $this->praLib->GetPropas($passoCorrente['PROPAK'], 'propak');

        if (!$propas_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Non trovato record di PROPAS del passo corrente');

            return $propakNew;
        }


        if ($evento == 'avanti') {

            $praLibElab = new praLibElaborazioneDati();
            $propakNew = $praLibElab->getPassoDestinazione($propas_rec, $datiWf->getDizionari());
        } else if ($evento == 'indietro') {

            $praLibElab = new praLibElaborazioneDati();
            $propakNew = $praLibElab->getPassoPrecedente($passoCorrente['PROPAK'], $datiWf->getDizionari());
            /*

              // Si cerca in PROPASFATTI il record che ha PROPASFATTI.PROSPA = $propas_rec['PROPAK']
              // e ci posizioniamo sul passo presente in PROPASFATTI.PROPAK
              //            $sql1 = "SELECT * FROM PROPASFATTI "
              //                    . " WHERE PROPASFATTI.PROSPA = '" . $passoCorrente['PROPAK'] . "' "
              //                    . " ORDER BY ROW_ID DESC ";
              //
              //            $propasFatti_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql1, false);

              $propasFatti_rec = $this->praLib->GetPropasFatti($passoCorrente['PROPAK']);
              if (!$propasFatti_rec) {
              $this->setErrCode(-1);
              $this->setErrMessage('Non trovato record di PROPASFATTI precedente al passo corrente');

              return $propakNew;
              }

              return $propakNew;
              }

              $propakNew = $propasFatti_rec[PROPAK];
             * 
             */
        }
        return $propakNew;
    }

    public function bloccoAllegatiRiservati($avvisaCambioStato = true) {
        $oldState = $this->presenzaAllegatiRiservati;

        foreach ($this->passAlle as $allegato) {
            if ($allegato['PASRIS']) {
                $this->presenzaAllegatiRiservati = true;
                Out::setInputTooltip($this->nameForm . '_PROPAS[PROPFLALLE]', 'Impossibile pubblicare gli allegati perché uno o più di essi risultano riservati.');
                Out::attributo($this->nameForm . '_PROPAS[PROPFLALLE]', 'checked', '1');
                Out::disableField($this->nameForm . '_PROPAS[PROPFLALLE]');

                if ($avvisaCambioStato && $oldState !== $this->presenzaAllegatiRiservati) {
                    Out::msgInfo('Attenzione', 'Sono stati inseriti uno o più allegati riservati, la pubblicazione è stata disabilitata.');
                }
                return true;
            }
        }

        $this->presenzaAllegatiRiservati = false;
        Out::enableField($this->nameForm . '_PROPAS[PROPFLALLE]');
        Out::setInputTooltip($this->nameForm . '_PROPAS[PROPFLALLE]', '');
        return false;
    }

}
