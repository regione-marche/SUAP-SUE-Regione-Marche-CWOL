<?php

/**
 *
 * GESTIONE EMAIL
 *
 * PHP Version 5
 *
 * @category    Gestione protocollo mail
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    31.08.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proMessage.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEmailDate.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlDate.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
include_once (ITA_LIB_PATH . '/itaPHPMail/itaMime.class.php');
include_once ITA_BASE_PATH . '/apps/Utility/utiIcons.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibMail.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFormHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proInteropMsg.class.php';

//include_once ITA_BASE_PATH . '/apps/Protocollo/proLibMail.class.php';
//include_once ITA_BASE_PATH . '/apps/Protocollo/proLibMail.class.php';

function proGestMail() {
    $proGestMail = new proGestMail();
    $proGestMail->parseEvent();
    return;
}

class proGestMail extends itaModel {

    public $PROT_DB;
    public $ITALWEB;
    public $nameForm = "proGestMail";
    public $divRis = "proGestMail_divRisultato";
    public $divAlert = "proGestMail_divAlert";
    public $gridAllegati = "proGestMail_gridAllegati";
    public $gridAllegatiOrig = "proGestMail_gridAllegatiOrig";
    public $gridElencoMail = "proGestMail_gridElencoMail";
    public $gridElencoMailScarti = "proGestMail_gridElencoMailScarti";
    public $gridElencoMailLocale = "proGestMail_gridElencoMailLocale";
    public $proLib;
    public $proLibMail;
    public $envLib;
    public $proLibSdi;
    public $emlLib;
    public $certificato;
    public $elencoAllegati;
    public $elencoAllegatiOrig;
    public $elemento;
    public $refAccounts;
    public $refAllAccounts;
    public $currMailBox;
    public $currMessage;
    public $emailTempPath;
    public $dettagliFile;
    public $elementoLocale;
    public $currAlert;
    public $scartati = array();
    public $proLibAllegati;
    public $currObjSdi;
    public $elencoMailSdi = array();
    public $ultimaMailSdi;
    public $proLibTabDag;
    public $currAllegatoEml;
    public $nameFormListViewer;
    public $datiSegnatura;
    public $lockMailInLettura;

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->proLibMail = new proLibMail();
            $this->emlLib = new emlLib();
            $this->envLib = new envLib();
            $this->proLibSdi = new proLibSdi();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->ITALWEB = $this->emlLib->getITALWEB();
            $this->certificato = App::$utente->getKey($this->nameForm . "_certificato");
            $this->elencoAllegati = App::$utente->getKey($this->nameForm . "_elencoAllegati");
            $this->elencoAllegatiOrig = App::$utente->getKey($this->nameForm . "_elencoAllegatiOrig");
            $this->elemento = App::$utente->getKey($this->nameForm . "_elemento");
            $this->currMessage = unserialize(App::$utente->getKey($this->nameForm . "_currMessage"));
            $this->currObjSdi = unserialize(App::$utente->getKey($this->nameForm . "_currObjSdi"));
            $this->currMailBox = unserialize(App::$utente->getKey($this->nameForm . "_currMailBox"));
            $this->emailTempPath = $this->proLib->SetDirectory('', "PEC") . "/";
            $this->dettagliFile = App::$utente->getKey($this->nameForm . "_dettagliFile");
            $this->elementoLocale = App::$utente->getKey($this->nameForm . "_elementoLocale");
            $this->currAlert = App::$utente->getKey($this->nameForm . "_currAlert");
            $this->refAccounts = App::$utente->getKey($this->nameForm . "_refAccounts");
            $this->scartati = App::$utente->getKey($this->nameForm . "_scartati");
            $this->elencoMailSdi = App::$utente->getKey($this->nameForm . "_elencoMailSdi");
            $this->ultimaMailSdi = App::$utente->getKey($this->nameForm . "_ultimaMailSdi");
            $this->currAllegatoEml = App::$utente->getKey($this->nameForm . "_currAllegatoEml");
            $this->nameFormListViewer = App::$utente->getKey($this->nameForm . "_nameFormListViewer");
            $this->refAllAccounts = App::$utente->getKey($this->nameForm . "_refAllAccounts");
            $this->datiSegnatura = App::$utente->getKey($this->nameForm . "_datiSegnatura");
            $this->lockMailInLettura = App::$utente->getKey($this->nameForm . "_lockMailInLettura");
            $this->proLibAllegati = new proLibAllegati();
            $this->proLibTabDag = new proLibTabDag();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_certificato", $this->certificato);
            App::$utente->setKey($this->nameForm . "_elencoAllegati", $this->elencoAllegati);
            App::$utente->setKey($this->nameForm . "_elencoAllegatiOrig", $this->elencoAllegatiOrig);
            App::$utente->setKey($this->nameForm . "_elemento", $this->elemento);
            App::$utente->setKey($this->nameForm . "_currMessage", serialize($this->currMessage));
            App::$utente->setKey($this->nameForm . "_currObjSdi", serialize($this->currObjSdi));
            App::$utente->setKey($this->nameForm . "_currMailBox", serialize($this->currMailBox));
            App::$utente->setKey($this->nameForm . "_dettagliFile", $this->dettagliFile);
            App::$utente->setKey($this->nameForm . "_elementoLocale", $this->elementoLocale);
            App::$utente->setKey($this->nameForm . "_currAlert", $this->elementoLocale);
            App::$utente->setKey($this->nameForm . "_refAccounts", $this->refAccounts);
            App::$utente->setKey($this->nameForm . "_scartati", $this->scartati);
            App::$utente->setKey($this->nameForm . "_elencoMailSdi", $this->elencoMailSdi);
            App::$utente->setKey($this->nameForm . "_ultimaMailSdi", $this->ultimaMailSdi);
            App::$utente->setKey($this->nameForm . "_currAllegatoEml", $this->currAllegatoEml);
            App::$utente->setKey($this->nameForm . "_nameFormListViewer", $this->nameFormListViewer);
            App::$utente->setKey($this->nameForm . "_refAllAccounts", $this->refAllAccounts);
            App::$utente->setKey($this->nameForm . "_datiSegnatura", $this->datiSegnatura);
            App::$utente->setKey($this->nameForm . "_lockMailInLettura", $this->lockMailInLettura);
        }
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
                //controllo se ha i permessi
                $profilo = proSoggetto::getProfileFromIdUtente();
                if ($profilo['PROT_ABILITATI'] == '2' || $profilo['PROT_ABILITATI'] == '3') {
                    Out::msgStop("Attenzione!", "Non sei abilitato a Protocollare da Email.");
                    $this->close();
                    break;
                }
                $this->setRefAllAccounts();
                $this->setRefAccounts();
                $this->creaCombo();
                Out::hide($this->nameForm . '_divCampiSearchScarti');
                Out::hide($this->nameForm . '_FiltraScarti');
                Out::hide($this->nameForm . '_divCampiSearchIncompleti');
                $this->loadModelConfig();
                $this->caricaConfigurazioni();
                $this->openRicerca();
                Out::tabSelect($this->nameForm . "_tabMail", $this->nameForm . "_paneElenco");
                $this->CaricaDatiFormIncompleti();
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_cbTutti':
                        if ($_POST[$this->nameForm . '_cbTutti'] == 1) {
                            Out::valore($this->nameForm . "_cbMsgInt", "0");
                            Out::valore($this->nameForm . "_cbMsgPEC", "0");
                            Out::valore($this->nameForm . "_cbAccettazione", "0");
                            Out::valore($this->nameForm . "_cbConsegna", "0");
                            Out::valore($this->nameForm . "_cbMsgStd", "0");
                            $this->openRicerca();
                        }
                        break;
                    case $this->nameForm . '_cbMsgInt':
                    case $this->nameForm . '_cbMsgPEC':
                    case $this->nameForm . '_cbAccettazione':
                    case $this->nameForm . '_cbConsegna':
                    case $this->nameForm . '_cbMsgStd':
                        Out::valore($this->nameForm . "_cbTutti", "0");
                        $_POST[$this->nameForm . '_cbTutti'] = 0;
                        $this->openRicerca();
                        break;

                    case $this->nameForm . '_LimiteVis':
                        if ($_POST[$this->nameForm . '_LimiteVis'] == 'DATE') {
                            Out::show($this->nameForm . '_DaData_field');
                        } else {
                            Out::hide($this->nameForm . '_DaData_field');
                        }
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridElencoMailLocale :
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = $this->nameForm . "_returnUploadFile";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridElencoMail :
                        $this->caricaTabella('2');
                        break;
                    case $this->gridElencoMailScarti :
                        $this->caricaTabellaScarti('', '2');
                        break;
                    case $this->gridElencoMailLocale :
                        $ita_grid01 = new TableView($this->gridElencoMailLocale, array('arrayTable' => $this->dettagliFile, 'rowIndex' => 'idx'));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows(14);
                        $ita_grid01->clearGrid($this->gridElencoMailLocale);
                        $ita_grid01->getDataPage('json');
                        break;
                }
                break;
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridElencoMail :
//                        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAnalizzaMail.class.php';
//                        /* @var $proLibAnalizzaMail proLibAnalizzaMail */
//                        $proLibAnalizzaMail = new proLibAnalizzaMail();
//                        $proLibAnalizzaMail->setEmlFile($_POST['rowid']);
//                        $proLibAnalizzaMail->setEmlTipo('DB');
//                        $proLibAnalizzaMail->CaricaElementiMail();
//                        $allegati = $proLibAnalizzaMail->getelencoAllegati();
//                        App::log($allegati);
//                        App::log($proLibAnalizzaMail->FormattaElencoAllegati($allegati));
//                        break;
                        $MailArchivio_rec = $this->emlLib->getMailArchivio($_POST['rowid'], 'rowid');
                        if (!in_array($MailArchivio_rec['CLASS'], proLibMail::$ElencoClassProtocollabili)) {
                            Out::msgInfo('Informazione', 'Questa mail è già stata Gestita.');
                            $this->caricaTabella('1');
                            break;
                        }
                        if (!$this->Dettaglio()) {
                            break;
                        }
                        Out::hide($this->divRis);
                        Out::show($this->nameForm . '_divDettaglio');
                        Out::show($this->nameForm . '_Elenca');
                        Out::show($this->nameForm . '_Protocolla');
                        Out::show($this->nameForm . '_Inoltra');
                        Out::show($this->nameForm . '_Rispondi');
                        Out::show($this->nameForm . '_Scarta');
                        if ($this->currObjSdi->isMessaggioSdi()) {
                            $this->VisualizzaMessaggioDT($this->currObjSdi);
                        }
                        break;
                    case $this->gridElencoMailScarti :
                        $this->Dettaglio();
                        Out::hide($this->divRis);
                        Out::show($this->nameForm . '_divDettaglio');
                        //Out::show($this->nameForm . '_Elenca');
                        Out::show($this->nameForm . '_TornaElencoScarti');
                        Out::hide($this->nameForm . '_AssegnaProt');
                        Out::show($this->nameForm . '_Ripristina');
                        break;
                    case $this->gridAllegati :
                        $FileAllegato = $this->elencoAllegati[$_POST['rowid'] - 1]['DATAFILE'];
                        $FileDati = $this->elencoAllegati[$_POST['rowid'] - 1]['FILE'];
                        /* Se è un eml allegato, faccio scegliere se Visualizzare o Scaricare */
                        if (strtolower(pathinfo($FileAllegato, PATHINFO_EXTENSION)) == "eml") {
                            Out::msgQuestion("Download", "Cosa vuoi fare con il file eml selezionato?", array(
                                'F2-Scarica' => array('id' => $this->nameForm . '_ScaricaEml', 'model' => $this->nameForm, 'shortCut' => "f2"),
                                'F8-Visualizza' => array('id' => $this->nameForm . '_VisualizzaEml', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                    )
                            );
                            $this->currAllegatoEml = array("FileAllegato" => $FileAllegato, "FileDati" => $FileDati);
                            return;
                        } else {
                            Out::openDocument(utiDownload::getUrl($FileAllegato, $FileDati));
                        }
                        break;
                    case $this->gridAllegatiOrig :
                        $FileAllegato = $this->elencoAllegatiOrig[$_POST['rowid'] - 1]['DATAFILE'];
                        $FileDati = $this->elencoAllegatiOrig[$_POST['rowid'] - 1]['FILE'];
                        /* Se è un eml allegato, faccio scegliere se Visualizzare o Scaricare */
                        if (strtolower(pathinfo($FileAllegato, PATHINFO_EXTENSION)) == "eml") {
                            Out::msgQuestion("Download", "Cosa vuoi fare con il file eml selezionato?", array(
                                'F2-Scarica' => array('id' => $this->nameForm . '_ScaricaEml', 'model' => $this->nameForm, 'shortCut' => "f2"),
                                'F8-Visualizza' => array('id' => $this->nameForm . '_VisualizzaEml', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                    )
                            );
                            $this->currAllegatoEml = array("FileAllegato" => $FileAllegato, "FileDati" => $FileDati);
                            return;
                        } else {
                            Out::openDocument(utiDownload::getUrl($FileAllegato, $FileDati));
                        }
                        break;
                    case $this->gridElencoMailLocale :
                        $this->DettaglioLocale();
                        Out::setFocus($this->nameForm, $this->nameForm . '_gridAllegati');
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridElencoMailLocale :
                        if (!@unlink($this->dettagliFile[$_POST['rowid']]['FILENAME'])) {
                            Out::msgStop("Cancellazione Mail", "Errore in cancellazione file.");
                        }
                        $this->caricaTabEmailLocali();
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Filtra':
                        $this->caricaTabella();
                        break;

                    case $this->nameForm . '_Salva':
                        $this->setConfig();
                        break;

                    case $this->nameForm . '_Protocolla':
                        if (is_object($this->currMessage)) {
                            $retDecode = $this->currMessage->getStruct();
                            $datiElemento = array();
                            if (!isset($this->elementoLocale)) {
                                $datiElemento = $this->elemento;
                                $_POST['tipoEml'] = 'MAILBOX';
                            } else {
                                $datiElemento = $this->dettagliFile[$this->elementoLocale];
                                $datiElemento['SUBJECT'] = $datiElemento['OGGETTO'];
                                $datiElemento['FROMADDR'] = $datiElemento['MITTENTE'];
                                $datiElemento['MSGDATE'] = $datiElemento['DATA'] . " " . $datiElemento['ORA'];
                                $_POST['tipoEml'] = 'LOCALE';
                            }
                        } else {
                            Out::msgStop("Attenzione!", "Errore nel processo di protocollazione.<br>Contattare l'assistenza tecnica.");
                            break;
                        }

                        /*
                         * Controllo se mail già protocollata:
                         */
                        $AnaproCheck_rec = $this->proLibMail->checkMailProtocollata($datiElemento['IDMAIL']);
                        if ($AnaproCheck_rec) {
                            Out::msgStop("Attenzione!", "La mail risulta già protocollata al Prot. " . $AnaproCheck_rec['PRONUM'] . ' ' . $AnaproCheck_rec['PROPAR']);
                            break;
                        }

                        $randPath = itaLib::getRandBaseName();
                        $percorsoTmp = itaLib::getAppsTempPath("proGestMail-$randPath");
                        if (!@is_dir($percorsoTmp)) {
                            if (!itaLib::createAppsTempPath("proGestMail-$randPath")) {
                                Out::msgStop("Archiviazione File.", "Creazione ambiente di lavoro temporaneo fallita.");
                                break;
                            }
                        }
                        // SE E' UNA PEC
                        if ($retDecode['ita_PEC_info'] != 'N/A') {
                            $messaggioOriginale = $retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile'];
                            $addressFrom = '';
                            foreach ($messaggioOriginale['From'] as $address) {
                                $addressFrom = $address['address'];
                            }
                            $datiElemento['FROMADDR'] = $addressFrom;
                            $parametri = $this->proLib->getParametriPosta('pec');
                            $allegati = array();
                            if ($parametri['Busta'] == '1') {
                                switch ($retDecode['Type']) {
                                    case 'text':
                                        $estensione = '.txt';
                                        break;
                                    case 'html':
                                        $estensione = '.html';
                                        break;
                                    default:
                                        $estensione = '';
                                        break;
                                }
                                $randName = md5(rand() * time()) . $estensione;
                                @chmod($retDecode['DataFile'], 0777);
                                if (!@copy($retDecode['DataFile'], $percorsoTmp . "/" . $randName)) {
                                    Out::msgStop("Archiviazione File.1", "Copia della Email temporanea fallita.");
                                    break;
                                }
                                $busta = array(array('DATAFILE' => 'Busta' . $estensione, 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'EMLPEC'));
                                $allegati = $this->unisciArray($allegati, $busta);
                            }
                            // Verifico se c'è postacert nella PEC
                            $elementoKey = $this->trovaPostaCertInAllegati();
                            if ($elementoKey >= 0) {
                                // Se lo trovo ed è attivo "MessaggioOriginale" nei parametri PEC:
                                if ($parametri['MessaggioOriginale'] == '1') {
                                    $parametriOri = $this->proLib->getParametriPosta();
                                    //Se nei parametri mail normale è attivo "Messaggio Originale": lo aggiungo come allegato.
                                    if ($parametriOri['MessaggioOriginale'] == '1') {// $elementoKey != ''
//                                    if ($parametriOri['MessaggioOriginale'] == '1' && file_exists($this->elencoAllegati[$elementoKey]['FILE'])) {
                                        $randName = md5(rand() * time()) . ".eml";
                                        @chmod($this->elencoAllegati[$elementoKey]['FILE'], 0777);
                                        if (!@copy($this->elencoAllegati[$elementoKey]['FILE'], $percorsoTmp . "/" . $randName)) {
                                            Out::msgStop("Archiviazione File.2", "Copia della Email temporanea fallita.");
                                            break;
                                        }
                                        $MessaggioOriginale = array(array('DATAFILE' => 'postacert.eml', 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'EMLORIGINALE'));
                                        $allegati = $this->unisciArray($allegati, $MessaggioOriginale);
                                    }
                                    //Se nei parametri mail normale è attivo "Allegati" e ci sono allegati nel messaggio originale:
                                    if ($parametriOri['Allegati'] == '1' && count($this->elencoAllegatiOrig) > 0) {
                                        $allegatiTmp = array();
                                        foreach ($this->elencoAllegatiOrig as $allegatoOrig) {
                                            $randName = md5(rand() * time()) . "." . pathinfo($allegatoOrig['DATAFILE'], PATHINFO_EXTENSION);
                                            @chmod($allegatoOrig['FILE'], 0777);
                                            if (!@copy($allegatoOrig['FILE'], $percorsoTmp . "/" . $randName)) {
                                                Out::msgStop("Archiviazione File.3", "Copia della Email temporanea fallita.");
                                                break;
                                            }
                                            $allegatiTmp[] = array('DATAFILE' => $allegatoOrig['DATAFILE'], 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'ALLEGATO');
                                        }
                                        $allegati = $this->unisciArray($allegati, $allegatiTmp);
                                    }
                                    // Se nei parametri mail normale è attivo "Corpo" allora salvo il corpo come messaggio originale (se c'è).
//                                    Out::msgInfo('retdecode', print_r($retDecode['ita_PEC_info'], true));
                                    if ($parametriOri['Corpo'] == '1') {
                                        if (isset($retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile']['DataFile'])) {
                                            $messOrigPath = $retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile']['DataFile'];
                                            // Controllo se non è un file vuoto.
                                            if (filesize($messOrigPath)) {
                                                switch ($retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile']['Type']) {
                                                    case 'text':
                                                        $estensione = '.txt';
                                                        break;
                                                    case 'html':
                                                        $estensione = '.html';
                                                        break;
                                                    default:
                                                        $estensione = '.txt'; //Pensare ad un default.
                                                        break;
                                                }
                                                $estensione = '.txt'; //Default Forzato.
                                                $randName = md5(rand() * time()) . $estensione;
                                                @chmod($messOrigPath, 0777);
                                                if (!@copy($messOrigPath, $percorsoTmp . "/" . $randName)) {
                                                    Out::msgStop("Archiviazione File.4", "Copia della Email temporanea fallita.");
                                                    break;
                                                }
                                                $messaggioOriginale = array(array('DATAFILE' => 'MessaggioOriginale' . $estensione, 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'CORPOEML')); //@TODO NON é CORPOPEC...?
                                                $allegati = $this->unisciArray($allegati, $messaggioOriginale);
                                            }
                                        }
                                    }
                                }
                            }
                            //Se da parametri pec ho attivo "Allegati":
                            if ($parametri['Allegati'] == '1') {
                                $elencoAllegTmp = $this->elencoAllegati;
                                // E tramite "trovaPostaCertInAllegati" ho trovato la "postacert",
                                // lo rimuovo dall'elenco allegati.
                                if ($elementoKey !== false) {
                                    unset($elencoAllegTmp[$elementoKey]);
                                }
                                $allegatiTmp = array();
                                // Scorro ogni allegato della PEC e salvo.
                                // Probabilmente è sempre e solo: daticert.xml
                                foreach ($elencoAllegTmp as $allegTmp) {
                                    $randName = md5(rand() * time()) . "." . pathinfo($allegTmp['DATAFILE'], PATHINFO_EXTENSION);
                                    @chmod($allegTmp['FILE'], 0777);
                                    if (!@copy($allegTmp['FILE'], $percorsoTmp . "/" . $randName)) {
                                        Out::msgStop("Archiviazione File.5", "Copia della Email temporanea fallita.");
                                        break;
                                    }
                                    $allegatiTmp[] = array('DATAFILE' => $allegTmp['DATAFILE'], 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'DATICERTPEC');
                                }
                                // Se è presente la segnatura, la salvo come allegato normale.
                                if ($retDecode['Signature']) {
                                    $randName = md5(rand() * time()) . "." . pathinfo($retDecode['Signature']['FileName'], PATHINFO_EXTENSION);
                                    @chmod($retDecode['Signature']['DataFile'], 0777);
                                    if (!@copy($retDecode['Signature']['DataFile'], $percorsoTmp . "/" . $randName)) {
                                        Out::msgStop("Archiviazione File.6", "Copia della Email temporanea fallita.");
                                        break;
                                    }
                                    $allegatiTmp[] = array('DATAFILE' => $retDecode['Signature']['FileName'], 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'ALLEGATO');
                                }
                                $allegati = $this->unisciArray($allegati, $allegatiTmp);
                            }
                            //Se è un messaggio normale
                        } else {
                            $parametri = $this->proLib->getParametriPosta();
                            $allegati = array();
                            // Se nei parametri normali mail è attivo "Messaggio Originale", allora salvo l'eml.
                            if ($parametri['MessaggioOriginale'] == '1') {
                                $emailOriginale = $this->currMessage->getEmlFile();
                                $randName = md5(rand() * time()) . ".eml";
                                @chmod($emailOriginale, 0777);
                                if (!@copy($emailOriginale, $percorsoTmp . "/" . $randName)) {
                                    Out::msgStop("Archiviazione File.7", "Copia della Email temporanea fallita.");
                                    break;
                                }
                                $messaggio = array(array('DATAFILE' => 'MessaggioOriginale.eml', 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'EMLORIGINALE'));
                                $allegati = $this->unisciArray($allegati, $messaggio);
                            }
                            // Se nei parametri normali è attivo "Allegati", li scorro e li salvo.
                            if ($parametri['Allegati'] == '1' && count($this->elencoAllegati) > 0) {
                                $allegatiTmp = array();
                                foreach ($this->elencoAllegati as $allegTmp) {
                                    $randName = md5(rand() * time()) . "." . pathinfo($allegTmp['DATAFILE'], PATHINFO_EXTENSION);
                                    @chmod($allegTmp['FILE'], 0777);
                                    if (!@copy($allegTmp['FILE'], $percorsoTmp . "/" . $randName)) {
                                        Out::msgStop("Archiviazione File.8", "Copia della Email temporanea fallita.");
                                        break;
                                    }
                                    $allegatiTmp[] = array('DATAFILE' => $allegTmp['DATAFILE'], 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'ALLEGATO');
                                }

                                $allegati = $this->unisciArray($allegati, $allegatiTmp);
                            }
                            //
                            // Se nei parametri normali è attivo "Corpo" salvo anche il corpo della mail.
                            //
                            if ($parametri['Corpo'] == '1') {
                                $messOrigPath = $retDecode['DataFile'];
                                switch ($retDecode['Type']) {
                                    case 'text':
                                        $estensione = '.txt';
                                        break;
                                    case 'html':
                                        $estensione = '.html';
                                        break;
                                    default:
                                        $estensione = '';
                                        break;
                                }
                                $randName = md5(rand() * time()) . $estensione;
                                @chmod($messOrigPath, 0777);
                                if (!@copy($messOrigPath, $percorsoTmp . "/" . $randName)) {
                                    Out::msgStop("Archiviazione File.9", "Copia della Email temporanea fallita.");
                                    break;
                                }
                                $messaggioOriginale = array(array('DATAFILE' => 'Corpo' . $estensione, 'FILE' => $percorsoTmp . '/' . $randName, 'DATATIPO' => 'CORPOEML'));
                                $allegati = $this->unisciArray($allegati, $messaggioOriginale);
                            }
                        }


                        /*
                         * Una volta analizzato il messaggio:
                         * Verifico se è una mail Parametrizzata nell'elenco delle mail 
                         * da Protocollare da Inoltro Mail.
                         * ?? Serve controllo solo mail Normali e esclude PEC ??
                         */
                        $anaent_52 = $this->proLib->GetAnaent('52');
                        $ElencoMailInoltro = unserialize($anaent_52['ENTVAL']);
                        if ($ElencoMailInoltro) {
                            $MailInoltro_rec = array();
                            // Valorizzo array per controllo mail in elenco.
                            foreach ($ElencoMailInoltro as $Mail) {
                                $MailInoltro_rec[] = $Mail['EMAIL'];
                            }
                            // Se è in elenco, mail speciale da inoltro.
                            $FileEmlAllegato = '';
                            if (in_array($datiElemento['ACCOUNT'], $MailInoltro_rec)) {
                                // Cerco il file eml allegato:
                                foreach ($this->elencoAllegati as $allegato) {
                                    $ext = strtolower(pathinfo($allegato['DATAFILE'], PATHINFO_EXTENSION));
                                    if ($ext == 'eml') {
                                        $FileEmlAllegato = $allegato['FILE'];
                                        break;
                                    }
                                }
                                // Controllo se non ho trovato il file eml da inoltro.
                                if (!$FileEmlAllegato) {
                                    Out::msgStop('Attenzione', 'Mail da protocollatre da Inoltro, ma file eml non presente.');
                                    break;
                                }
                                // Analisi del messaggio locale
                                $InoltroMessage = new emlMessage();
                                $InoltroMessage->setEmlFile($FileEmlAllegato);
                                $InoltroMessage->parseEmlFileDeep();
                                $retDecodeInoltro = $InoltroMessage->getStruct();
                                /*
                                 * Scambio i dati interni di protocollazione, oggetto e mittente
                                 */
                                $datiElemento['SUBJECT'] = $retDecodeInoltro['Subject'];
                                $datiElemento['FROMADDR'] = $retDecodeInoltro['FromAddress'];
                                $decodedDate = emlDate::eDate2Date($retDecodeInoltro['Date']);
                                $datiElemento['MSGDATE'] = $decodedDate['date'] . " " . $decodedDate['time'];
                                $allegati = array();
                                /*
                                 * Corpo
                                 */
                                $allegatiTmpInoltro = array();
                                $elencoAllegatiInoltro = $this->caricaElencoAllegati($retDecodeInoltro);
                                if ($parametri['Allegati'] == '1' && count($elencoAllegatiInoltro) > 0) {
                                    $allegatiTmpInoltro = array();
                                    foreach ($elencoAllegatiInoltro as $allegTmpInoltro) {
                                        $randName = md5(rand() * time()) . "." . pathinfo($allegTmpInoltro['DATAFILE'], PATHINFO_EXTENSION);
                                        @chmod($allegTmpInoltro['FILE'], 0777);
                                        if (!@copy($allegTmpInoltro['FILE'], $percorsoTmp . "/" . $randName)) {
                                            Out::msgStop("Archiviazione File.8", "Copia della Email temporanea fallita.");
                                            break;
                                        }
                                        $allegatiTmpInoltro[] = array('DATAFILE' => $allegTmpInoltro['DATAFILE'], 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'ALLEGATO');
                                    }

                                    $allegati = $this->unisciArray($allegati, $allegatiTmpInoltro);
                                }

                                if ($parametri['Corpo'] == '1') {
                                    $messOrigPathInoltro = $retDecodeInoltro['DataFile'];
                                    switch ($retDecodeInoltro['Type']) {
                                        case 'text':
                                            $estensione = '.txt';
                                            break;
                                        case 'html':
                                            $estensione = '.html';
                                            break;
                                        default:
                                            $estensione = '';
                                            break;
                                    }
                                    $randName = md5(rand() * time()) . $estensione;
                                    @chmod($messOrigPath, 0777);
                                    if (!@copy($messOrigPathInoltro, $percorsoTmp . "/" . $randName)) {
                                        Out::msgStop("Archiviazione File.9", "Copia della Email temporanea fallita.");
                                        break;
                                    }
                                    $messaggioOriginaleInoltro = array(array('DATAFILE' => 'Corpo' . $estensione, 'FILE' => $percorsoTmp . '/' . $randName, 'DATATIPO' => 'CORPOEML'));
                                    $allegati = $this->unisciArray($allegati, $messaggioOriginaleInoltro);
                                }
                            }
                        }
                        /*
                         * Analisi messaggio non certificato:
                         * postacert.eml non deve essere aggiunto come allegato
                         * se viene sbustato: è un doppione con "MessaggioOriginale.eml" perchè è contenuto.
                         */
                        if (count($this->elencoAllegati) == 1) {
                            $allegato = $this->elencoAllegati[0];
                            if (strtolower($allegato['DATAFILE']) == 'postacert.eml') {
                                $FileEmlAllegato = $allegato['FILE'];
                                // Analisi del messaggio locale
                                $MessageNoCert = new emlMessage();
                                $MessageNoCert->setEmlFile($FileEmlAllegato);
                                $MessageNoCert->parseEmlFileDeep();
                                $retDecodeNoCert = $MessageNoCert->getStruct();

                                $elencoAllegatiNoCert = $this->caricaElencoAllegati($retDecodeNoCert);
                                if ($parametri['Allegati'] == '1' && count($elencoAllegatiNoCert) > 0) {
                                    if ($elencoAllegatiNoCert) {
                                        $allegatiTmpNoCert = array();
                                        foreach ($elencoAllegatiNoCert as $allegTmpNoCert) {
                                            $randName = md5(rand() * time()) . "." . pathinfo($allegTmpNoCert['DATAFILE'], PATHINFO_EXTENSION);
                                            @chmod($allegTmpNoCert['FILE'], 0777);
                                            if (!@copy($allegTmpNoCert['FILE'], $percorsoTmp . "/" . $randName)) {
                                                Out::msgStop("Archiviazione File.9", "Copia della Email temporanea fallita.");
                                                break;
                                            }
                                            $allegatiTmpNoCert[] = array('DATAFILE' => $allegTmpNoCert['DATAFILE'], 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'ALLEGATO');
                                        }
                                        $allegati = $this->unisciArray($allegati, $allegatiTmpNoCert);
                                        /*
                                         *  Controllo presenza MessaggioOriginale.eml
                                         */
                                        $checkOriginale = false;
                                        foreach ($allegati as $allegatocheck) {
                                            if ($allegatocheck['DATAFILE'] == 'MessaggioOriginale.eml') {
                                                $checkOriginale = true;
                                                break;
                                            }
                                        }
                                        /*
                                         *  Se è presente "MessaggioOriginale.eml" rimuovo
                                         *  postacert.eml perchè è già contenuto nell'originale.
                                         */
                                        if ($checkOriginale) {
                                            foreach ($allegati as $key => $alleg) {
                                                if (strtolower($alleg['DATAFILE']) == 'postacert.eml') {
                                                    unset($allegati[$key]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        /*
                         * Blocco Record IIDMAIL:
                         */
                        if ($this->lockMailInLettura) {
                            $retLockid = $this->lockMailInLettura;
                        } else {
                            $retLockid = $this->proLibMail->lockMail($datiElemento['ROWID']);
                            if (!$retLockid) {
                                Out::msgInfo("Attenzione", $this->proLibMail->getErrMessage());
                                break;
                            }
                        }

                        /*
                         * Tramite dati segnatura imposto allegato principale
                         */
                        if ($this->datiSegnatura) {
                            if ($this->datiSegnatura['ALLEGATO_PRINCIPALE']) {
                                foreach ($allegati as $key => $allegato) {
                                    if ($allegato['DATAFILE'] == $this->datiSegnatura['ALLEGATO_PRINCIPALE']) {
                                        $allegati[$key]['PRINCIPALE'] = true;
                                        break;
                                    }
                                }
                            }
                        }

                        $model = 'proArri';
                        $_POST['datiPost'] = $_POST;
                        $_POST['event'] = 'openform';
                        $_POST['tipoProt'] = 'A';
                        $_POST['datiMail'] = $datiElemento;
                        $_POST['datiMail']['ELENCOALLEGATI'] = $allegati;
                        $_POST['datiMail']['lockMail'] = $retLockid;
                        if ($this->currObjSdi) {
                            $_POST['objSdi'] = serialize($this->currObjSdi);
                        }
                        itaLib::openForm($model);
                        Out::hide($this->nameForm);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        $this->close();
                        break;
                    case $this->nameForm . '_Elenca':
                        $this->openRicerca();
                        TableView::reload($this->gridElencoMailScarti);
                        break;
                    case $this->nameForm . '_TornaElencoScarti':
                        $this->openRicerca(false);
                        TableView::reload($this->gridElencoMailScarti);
                        break;
                    case $this->nameForm . '_Ricevi':
                        $this->scaricaPosta();
                        break;
                    case $this->nameForm . '_FiltraScarti':
                        $this->caricaTabellaScarti();
                        $this->Azzera(false);
                        break;
                    case $this->nameForm . '_Scarta':
                        $this->scartaMail($this->elemento['ROWID']);
                        $this->openRicerca();
                        break;
                    case $this->nameForm . '_Ripristina':
                        $this->scartaMail($this->elemento['ROWID'], true);
                        $this->openRicerca(false);
                        break;
                    case $this->nameForm . '_AssegnaProttocolli':
                        $this->assegnaProtocolli();
                        break;
                    case $this->nameForm . '_ScartaMulti':
                        $whereFiltri = $this->getWhereFiltri();
                        $this->selezionaDaScartare(false, $whereFiltri);
                        break;
                    case $this->nameForm . '_AssegnaProt':
                        /**
                         *  DA SISTEMARE CON LA SELEZIONE DEL PADRE TRAMITE IDPADRE SU ARCHIVIO_MAIL
                         */
                        if (!isset($this->elementoLocale)) {
                            $datiElemento = $this->elemento;
                        } else {
                            $datiElemento = $this->dettagliFile[$this->elementoLocale];
                        }
                        $proMessage = new proMessage();
                        switch ($datiElemento['PECTIPO']) {
                            case 'accettazione':
                                $checkOggetto = $proMessage->checkOggettoInterno($this->currMessage);
                                if (!$checkOggetto) {
                                    $checkOggetto = $this->checkNotificaRicezione();
                                }
                                if ($checkOggetto) {
                                    Out::msgQuestion("Assegna a Protocollo.", "Operazione non reversibile!<br><br>Sei sicuro di voler assegnare l'email con Oggetto: '" .
                                            $checkOggetto['SUBJECT'] . "' al Protocollo n°" .
                                            (int) substr($checkOggetto['PRONUM'], 4) . " del " .
                                            date('d/m/Y', strtotime($checkOggetto['PRODAR'])) . "?", array(
                                        'F8-Voglio selezionare il protocollo manualmente' => array('id' => $this->nameForm . '_AnnullaAssegna', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                        'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAssegnaAccetta', 'model' => $this->nameForm, 'shortCut' => "f5")
                                            )
                                    );
                                } else {
                                    $postSave = $_POST;
                                    include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
                                    proRic::proRicNumAntecedenti($this->nameForm, '', '', 'returnAssegnaProt', $postSave);
                                }
                                break;
                            case 'avvenuta-consegna':
                                $datiSegnatura = $this->checkDaProtocollo();
                                if ($datiSegnatura) {
                                    Out::msgQuestion("Assegna a Protocollo.", "Operazione non reversibile!<br><br>Sei sicuro di voler assegnare l'email con Oggetto: '" .
                                            $datiSegnatura['SUBJECT'] . "' al Protocollo n°" .
                                            (int) substr($datiSegnatura['PRONUM'], 4) . " del " .
                                            date('d/m/Y', strtotime($datiSegnatura['PRODAR'])) . "?", array(
                                        'F8-Voglio selezionare il protocollo manualmente' => array('id' => $this->nameForm . '_AnnullaAssegna', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                        'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAssegna', 'model' => $this->nameForm, 'shortCut' => "f5")
                                            )
                                    );
                                } else {
                                    Out::msgQuestion("Attenzione!", "Non è stato possibile leggere il file di Segnatura.xml.<BR>
                                        Selezionare manualmente il protocollo a cui assegnare l'email?", array(
                                        'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaX', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                        'F5-Voglio selezionare il protocollo manualmente' => array(
                                            'id' => $this->nameForm . '_AssegnaManualmente', 'model' => $this->nameForm, 'shortCut' => "f5")
                                            )
                                    );
                                }
                                break;
                            case 'posta-certificata':
                                $anapro_rec = $this->checkNotificaRicezione();
                                if ($anapro_rec != null) {
                                    Out::msgQuestion("Assegna a Protocollo.", "Operazione non reversibile!<br><br>Sei sicuro di voler assegnare l'email con Oggetto: '" .
                                            $anapro_rec['SUBJECT'] . "' al Protocollo n°" .
                                            (int) substr($anapro_rec['PRONUM'], 4) . " del " .
                                            date('d/m/Y', strtotime($anapro_rec['PRODAR'])) . "?", array(
                                        'F8-Voglio selezionare il protocollo manualmente' => array('id' => $this->nameForm . '_AnnullaAssegna', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                        'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAssegnaNotifica', 'model' => $this->nameForm, 'shortCut' => "f5")
                                            )
                                    );
                                    break;
                                }
                            default:/* patch corridonia */
                            case '':
                                $checkNotifica = $this->checkNotificaRicezione();
                                if ($checkNotifica) {
                                    Out::msgQuestion("Assegna a Protocollo.", "Operazione non reversibile!<br><br>Sei sicuro di voler assegnare l'email con Oggetto: '" .
                                            $checkNotifica['SUBJECT'] . "' al Protocollo n°" .
                                            (int) substr($checkNotifica['PRONUM'], 4) . " del " .
                                            date('d/m/Y', strtotime($checkNotifica['PRODAR'])) . "?", array(
                                        'F8-Voglio selezionare il protocollo manualmente' => array('id' => $this->nameForm . '_AnnullaAssegna', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                        'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAssegnaNotifica', 'model' => $this->nameForm, 'shortCut' => "f5")
                                            )
                                    );
                                } else {
                                    $postSave = $_POST;
                                    include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
                                    proRic::proRicNumAntecedenti($this->nameForm, '', '', 'returnAssegnaProt', $postSave);
                                }
                                break;
//                            default: /* patch corridonia */
//                                break;
                        }
                        break;
                    case $this->nameForm . '_ConfermaAssegnaAccetta':
                        $postSave = $_POST;
                        $proMessage = new proMessage();
                        $this->assegnaEmlToProtocollo($proMessage->checkOggettoInterno($this->currMessage));
                        $_POST = $postSave;
                        $this->openRicerca();
                        break;
                    case $this->nameForm . '_ConfermaAssegna':
                        $postSave = $_POST;
                        $this->assegnaEmlToProtocollo($this->checkDaProtocollo());
                        $_POST = $postSave;
                        $this->openRicerca();
                        break;
                    case $this->nameForm . '_ConfermaAssegnaNotifica':
                        $postSave = $_POST;
                        $this->assegnaEmlToProtocollo($this->checkNotificaRicezione());
                        $_POST = $postSave;
                        $this->openRicerca();
                        break;
                    case $this->nameForm . '_AssegnaManualmente':
                    case $this->nameForm . '_AnnullaAssegna':
                        $postSave = $_POST;
                        include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
                        proRic::proRicNumAntecedenti($this->nameForm, '', '', 'returnAssegnaProt', $postSave);
                        break;
                    case $this->nameForm . '_CertificatoV':
                    case $this->nameForm . '_CertificatoNV':
                        Out::openDocument(utiDownload::getUrl($this->certificato['Signature']['FileName'], $this->certificato['Signature']['DataFile']));
                        break;
                    case $this->nameForm . '_DatiPec':
                        $certificazione = "<br><br><b>Tipo: </b>" . $this->certificato['ita_PEC_info']['tipo'] . "<br>";
                        $certificazione .= "<b>Errore: </b>" . $this->certificato['ita_PEC_info']['errore'] . "<br>";
                        $certificazione .= "<b>Mittente: </b>" . $this->certificato['ita_PEC_info']['mittente'] . "<br>";
                        $certificazione .= "<b>Emittente: </b>" . $this->certificato['ita_PEC_info']['gestore-emittente'] . "<br>";
                        $certificazione .= "<b>Oggetto: </b>" . $this->certificato['ita_PEC_info']['oggetto'] . "<br>";
                        $certificazione .= "<b>Data e Ora: </b>" . $this->certificato['ita_PEC_info']['data'] . " - " . $this->certificato['ita_PEC_info']['ora'] . "<br>";
                        Out::msgInfo("Dati Certificazione", $certificazione);
                        break;

                    case $this->nameForm . '_Inoltra':
                        $retDecode = $this->currMessage->getStruct();
                        if ($retDecode['ita_PEC_info'] != "N/A") {
                            $messaggioOriginale = $retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile'];
                        }

                        if (!$messaggioOriginale) {
                            $messaggioOriginale = $retDecode;
                        }

                        $url = utiDownload::getUrl("emlbody.html", $this->currMessage->getEmlBodyDataFile(), false, true);
                        $iframe = '<iframe style="border: 1px dotted black;" src="' . $url . '" frameborder="0" width="99%" height="95%" id="' . $this->nameForm . '_emlOrigFrame">
                                     <p>Contenuto non visualizzabile.....</p>
                                   </iframe>';
                        $allegati = array();
                        foreach ($this->currMessage->getAttachments() as $allegato) {
                            $icon = utiIcons::getExtensionIconClass($allegato['FileName'], 32);
//                            $fileSize = $this->praLib->formatFileSize(filesize($allegato['DataFile']));
                            $fileSize = $this->emlLib->formatFileSize(filesize($allegato['DataFile']));
                            $allegati[] = array(
                                'FILEORIG' => $allegato['FileName'],
                                'FILENAME' => $allegato['FileName'],
                                'FILEPATH' => $allegato['DataFile'],
                                "FileIcon" => "<span style = \"margin:2px;\" class=\"$icon\"></span>",
                                "FileSize" => $fileSize
                            );
                        }
                        $valori['Oggetto'] = $messaggioOriginale['Subject'];
                        $valori['Corpo'] = "<br>-------- Messaggio originale --------<br>
                                            <span><b>Oggetto:</b> " . $retDecode['Subject'] . "</span><br>
                                            <span><b>Data:</b> " . $retDecode['Date'] . "</span><br>
                                            <span><b>Mittente:</b> " . $retDecode['From'][0]['address'] . "</span><br>
                                            <span><b>A:</b> " . $retDecode['To'][0]['address'] . "</span><br><br>$iframe";

                        $DaMail = $this->proLib->GetElencoDaMail('send');
                        $_POST = array();
                        $model = 'utiGestMail';
                        $_POST['tipo'] = 'inoltra';
                        $_POST['valori'] = $valori;
                        $_POST['allegati'] = $allegati;
                        $_POST['sizeAllegati'] = 80;
                        $_POST['returnModel'] = $this->nameForm;
                        $_POST['returnEvent'] = 'returnMail';
                        $_POST['event'] = 'openform';
                        $_POST['ElencoDaMail'] = $DaMail;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case $this->nameForm . '_Rispondi':
                        $retDecode = $this->currMessage->getStruct();
                        $PerContoDi = '';
                        $ContenutoMesaggioOriginale = '';
                        $allegati = array();
                        if ($retDecode['ita_PEC_info'] != "N/A") {
                            $PerContoDi = 'per conto di:';
                            $messaggioOriginale = $retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile'];
                            $url = utiDownload::getUrl("emlbody.html", $messaggioOriginale['DataFile'], false, true);
                            $ContenutoMesaggioOriginale = '<div style="margin-left:20px;">' . file_get_contents($this->currMessage->getEmlBodyDataFile()) . '</div>';
                            $ContenutoMesaggioOriginale .= '-------- <b>Messaggio originale</b> --------<br>';
                        }

                        if (!$messaggioOriginale) {
                            $messaggioOriginale = $retDecode;
                            $url = utiDownload::getUrl("emlbody.html", $this->currMessage->getEmlBodyDataFile(), false, true);
                        }
                        $iframe = '<iframe style="border: 1px dotted black;" src="' . $url . '" frameborder="0" width="99%" height="95%" id="' . $this->nameForm . '_emlOrigFrame">
                                     <p>Contenuto non visualizzabile.....</p>
                                   </iframe>';
                        $DataFormatted = date('d/m/Y H:s', strtotime($retDecode['Date']));
                        $MailMittente = $messaggioOriginale['From'][0]['address'];
                        /* Preparazione Corpo Mail */
                        $Corpo = '<br><hr>';
                        $Corpo .= " <span>Il Giorno " . $DataFormatted . ", " . $PerContoDi . " " . $MailMittente . " ha scritto:</span><br>";
                        $Corpo .= $ContenutoMesaggioOriginale;
                        $Corpo .= "<br>$iframe";

                        $valori['Oggetto'] = 'Re: ' . $messaggioOriginale['Subject'];
                        $valori['Corpo'] = $Corpo;
                        $valori['Destinatario'] = $MailMittente;
                        $_POST = array();
                        $model = 'utiGestMail';
                        $_POST['tipo'] = 'rispondi';
                        $_POST['valori'] = $valori;
                        $_POST['allegati'] = $allegati;
                        $_POST['sizeAllegati'] = 80;
                        $_POST['returnModel'] = $this->nameForm;
                        $_POST['returnEvent'] = 'returnMailRispondi';
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case $this->nameForm . '_paneLocale':
                    case $this->nameForm . '_paneElenco':
                        Out::show($this->nameForm . "_Filtra");
                        Out::show($this->nameForm . "_Salva");
                        Out::show($this->nameForm . "_divCampiSearch");
                        Out::hide($this->nameForm . "_FiltraScarti");
                        Out::hide($this->nameForm . '_divCampiSearchScarti');
                        Out::hide($this->nameForm . '_divCampiSearchIncompleti');
                        break;
                    case $this->nameForm . '_paneScarti':
                        Out::hide($this->nameForm . "_Filtra");
                        Out::hide($this->nameForm . "_Salva");
                        Out::hide($this->nameForm . '_divCampiSearch');
                        Out::show($this->nameForm . "_FiltraScarti");
                        Out::show($this->nameForm . '_divCampiSearchScarti');
                        Out::hide($this->nameForm . '_divCampiSearchIncompleti');
                        break;

                    case $this->nameForm . '_paneFattEle':
                        Out::show($this->nameForm . "_divFatturaPA");
                        Out::show($this->nameForm . "_VediXmlNotifica");
                        break;

                    case $this->nameForm . '_paneCertificazione':
                    case $this->nameForm . '_paneOriginale':
                    case $this->nameForm . '_paneMail':
                        Out::hide($this->nameForm . "_divFatturaPA");
                        break;

                    case $this->nameForm . '_VediXmlNotifica':
                        if (!$this->currObjSdi->getTipoMessaggio()) {
                            Out::msgInfo('Attenzione', 'Tipo di Notifica non definito.');
                            break;
                        }
                        $Xmlstyle = proSdi::$ElencoStiliMessaggio[$this->currObjSdi->getTipoMessaggio()];
                        $this->proLibSdi->VisualizzaXmlConStile($Xmlstyle, $this->currObjSdi->getFilePathMessaggio());
                        break;

                    case $this->nameForm . '_AggiungiMittenteSDI':
                        if ($this->ultimaMailSdi) {
                            $this->elencoMailSdi[] = $this->ultimaMailSdi;
                            $this->AggiungiMailSdi($this->ultimaMailSdi);
                            Out::hide($this->nameForm . '_AggiungiMittenteSDI');
                            Out::msgBlock('', 2000, true, "Mittente aggiunto all'elenco delle Mail Sdi.");
                        }
                        break;

                    case $this->nameForm . '_divAvvisoFatturaPa':
                        Out::tabSelect($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneFattEle");
                        break;

                    case $this->nameForm . '_AssegnaProtocolloSDI':
                        $AnaproCollegato = $this->proLibSdi->GetAnaproDaCollegareFromEstratto($this->currObjSdi->getEstrattoMessaggio(), 'A');
                        $RetAssegnaProt = $this->AssegnaProtocolloSDI($this->currObjSdi, $AnaproCollegato, $this->elemento);
                        if ($RetAssegnaProt['STATO'] == 'ASSEGNATO') {
                            $Numero = substr($AnaproCollegato['PRONUM'], 4);
                            $Anno = substr($AnaproCollegato['PRONUM'], 0, 4);
                            $Tipo = $AnaproCollegato['PROPAR'];
                            $Messaggio = "Notifica assegnata correttamente al protocollo n. $Tipo $Anno/$Numero ";

                            $audit_Info = 'MAIL ROWID:' . $this->elemento['ROWID'] . ' ' . $Messaggio;
                            $this->insertAudit($this->PROT_DB, 'PROMAIL', $audit_Info);
                            Out::msgInfo("Assegnazione", $Messaggio);
                            /*
                             * Qui provo a fare un export DT
                             */
                            $retExport = $this->ExportNotificaDTSDI($AnaproCollegato);
                            if ($retExport['STATO'] == 'ERRORE') {
                                Out::msgStop("Attezione", $retExport['ESPORTAZIONE']);
                            } else {
                                Out::msgInfo("Esportazione", $retExport['ESPORTAZIONE']);
                            }
                            $this->openRicerca();
                        } else {
                            // Provo a ripristinare:
                            $RetRipristino = $this->RipristinaMailDaAssegnareSDI($RetAssegnaProt['STACKASSEGNA']);
                            $Messaggio = 'Errore riscontrato in elaborazione: ' . $RetAssegnaProt['MESSAGGIO'] . '<br>';
                            if ($RetRipristino['STATO'] != 'RIPRISTINATO') {
                                $Messaggio .= 'Errore nel tentativo di ripristino: ';
                                $Messaggio .= $RetRipristino['MESSAGGIO'] . '<br>';
                            } else {
                                $Messaggio .= ' La mail è stata ripristinata.';
                            }

                            $audit_Info = 'ERRORE ASSEGNA MAIL SDI. ROWID: ' . $RetAssegnaProt['ROWID_ERRORE'] . $Messaggio;
                            $this->insertAudit($this->PROT_DB, 'PROMAIL', $audit_Info);
                            Out::msgStop("Attenzione", '<div>' . $Messaggio . '</div>');
                        }
                        break;

                    case $this->nameForm . '_AssegnaNotificheSDI':
                        Out::msgQuestion("Assegnazione Notifiche SDI", "Procedere con l'assegnazione automatica delle Notifiche di Decorrenza Termine ai relativi protocolli?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaAssegnaNotifiche', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAssegnaNotificheSDI', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaAssegnaNotificheSDI':
                        $retAssNotProt = $this->AssegnaNotificheProtocolloSDI();
                        $MessaggioErrori = '';
                        if ($retAssNotProt['ERRORI']) {
                            $MessaggioErrori = 'Altre segnalazioni:<br>';
                            foreach ($retAssNotProt['ERRORI'] as $key => $errore) {
                                $MessaggioErrori .= ' - ' . $errore . '<br>';
                                $audit_Info = 'ROWID MAIL: ' . $key . ' Errore: ' . $errore;
                                $this->insertAudit($this->PROT_DB, 'PROMAIL', $audit_Info);
                            }
                        }
                        if ($retAssNotProt['STATO'] == 'ASSEGNATI') {
                            $audit_Info = $MessaggioRiep = "Assegnate " . $retAssNotProt['ASSEGNATI'] . " notifiche di decorrenza termini ai relativi protocolli.<br><br>";
                            $MessaggioRiep .= $MessaggioErrori;
                            $this->insertAudit($this->PROT_DB, 'PROMAIL', $audit_Info);
                            Out::msgInfo("Assegnazione Notifiche", '<div>' . $MessaggioRiep . '</div>');
                        } else {
                            $MessaggioRiep = $retAssNotProt['MESSAGGIO'] . '<br><br>';
                            if ($retAssNotProt['ASSEGNATI']) {
                                $MessaggioRiep .= "Assegnate " . $retAssNotProt['ASSEGNATI'] . " notifiche di decorrenza termini ai relativi protocolli.<br><br>";
                            }
                            $MessaggioRiep .= $MessaggioErrori;
                            $audit_Info = 'ERRORE ASSEGNA MAIL SDI. ROWID: ' . $retAssNotProt['ROWID_ERRORE'] . ' Errore: ' . $retAssNotProt['MESSAGGIO'];
                            $this->insertAudit($this->PROT_DB, 'PROMAIL', $audit_Info);
                            Out::msgStop("Errore in Assegnazione", '<div>' . $MessaggioRiep . '</div>');
                        }
                        $this->openRicerca(false);
                        break;
                    /* Apertura o Scarica del file Eml */
                    case $this->nameForm . '_VisualizzaEml':
                        $model = 'emlViewer';
                        $_POST['event'] = 'openform';
                        $_POST['codiceMail'] = $this->currAllegatoEml['FileDati'];
                        $this->currAllegatoEml = null;
                        $_POST['tipo'] = 'file';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_ScaricaEml':
                        Out::openDocument(utiDownload::getUrl($this->currAllegatoEml['FileAllegato'], $this->currAllegatoEml['FileDati']));
                        $this->currAllegatoEml = null;
                        break;

                    case $this->nameForm . '_paneProtIncompleti':
                        Out::hide($this->nameForm . "_Filtra");
                        Out::hide($this->nameForm . "_Salva");
                        Out::hide($this->nameForm . '_divCampiSearch');
                        Out::hide($this->nameForm . '_divCampiSearchScarti');
                        Out::hide($this->nameForm . "_FiltraScarti");
                        Out::show($this->nameForm . '_divCampiSearchIncompleti');
                        break;

                    case $this->nameForm . '_FiltraIncompleti':
                        $this->RicaricaIncompleti();
                        break;

                    case 'close-portlet':
                        if ($this->lockMailInLettura) {
                            if (!$this->proLibMail->unlockMail($this->lockMailInLettura)) {
                                Out::msgInfo('Attenzione', $this->proLibMail->getErrMessage());
                            }
                            $this->lockMailInLettura = array();
                        }
                        $this->returnToParent();
                        break;
                }
                break;

            case 'VediXmlFattura':
                $Id = $_POST['id'];
                $FilePathFattura = $this->currObjSdi->getFilePathFattura();
                $stileFattura = $this->proLibSdi->GetStileFattura($this->currObjSdi);
//                $this->proLibSdi->VisualizzaXmlConStile(proSdi::StileFattura, $FilePathFattura[$Id]);
                $this->proLibSdi->VisualizzaXmlConStile($stileFattura, $FilePathFattura[$Id]);
                break;

            case 'VediAllegatoFattura':
                $Id = $_POST['id'];
                list($idAllegato, $idFattura) = explode("-", $Id);

                $EstrattoAllegatiFattura = $this->currObjSdi->getEstrattoAllegatiFattura();
                Out::openDocument(utiDownload::getUrl($EstrattoAllegatiFattura[$idFattura][$idAllegato]['NomeAttachment'], $EstrattoAllegatiFattura[$idFattura][$idAllegato]['FilePathAllegato']));
                break;

            case "cellSelect":
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        foreach ($this->elencoAllegati as $alle) {
                            $allegatoFirmato = array();
                            if ($alle['ROWID'] == $_POST['rowid']) {
                                $allegatoFirmato = $alle;
                                break;
                            }
                        }
                        switch ($_POST['colName']) {
                            case 'VSIGN':
                                $filepath = pathinfo($allegatoFirmato['FILE'], PATHINFO_DIRNAME);
                                $P7Mfile = pathinfo($allegatoFirmato['FILE'], PATHINFO_FILENAME) . ".pdf.p7m";
                                if (!@copy($allegatoFirmato['FILE'], $filepath . "/" . $P7Mfile)) {
                                    Out::msgStop("Attenzione!!!", "Impossibile verificare la firma del file P7M");
                                    break;
                                }
                                $this->proLibAllegati->VisualizzaFirme($filepath . "/" . $P7Mfile, $allegatoFirmato['DATAFILE']);
                                break;
                        }
                        break;
                    case $this->gridAllegatiOrig:
                        foreach ($this->elencoAllegatiOrig as $alle) {
                            $allegatoFirmato = array();
                            if ($alle['ROWID'] == $_POST['rowid']) {
                                $allegatoFirmato = $alle;
                                break;
                            }
                        }
                        switch ($_POST['colName']) {
                            case 'VSIGN':
                                $filepath = pathinfo($allegatoFirmato['FILE'], PATHINFO_DIRNAME);
                                $P7Mfile = pathinfo($allegatoFirmato['FILE'], PATHINFO_FILENAME) . ".pdf.p7m";
                                if (!@copy($allegatoFirmato['FILE'], $filepath . "/" . $P7Mfile)) {
                                    Out::msgStop("Attenzione!!!", "Impossibile verificare la firma del file P7M");
                                    break;
                                }
                                $this->proLibAllegati->VisualizzaFirme($filepath . "/" . $P7Mfile, $allegatoFirmato['DATAFILE']);
                                break;
                        }
                        break;
                }

                break;
            case "returnAssegnaProt":
                $postSave = $_POST['retid'];
                /* patch corridonia */
                if (!$this->assegnaEmlToProtocollo($this->getDatiProtocolloFromRowid($_POST['retKey']))) {
                    Out::msgStop("Attenzione", "Errore in assegnazione mail.");
                }
                $_POST = $postSave;
                $this->openRicerca();
                break;
            case "returnMultiselectGeneric":
                $chiavi = explode(',', $_POST['retKey']);
                foreach ($chiavi as $key => $value) {
                    $chiavi[$key] = substr($value, 1);
                }
                switch ($_POST['retid']) {
                    case 'AssegnaTutti':
                        if ($_POST['retKey'] != '') {
                            $daAssegnare = $this->formData['daAssegnare'];
                            $proMessage = new proMessage();
                            foreach ($chiavi as $chiave) {
                                $email = $daAssegnare[$chiave];
                                $this->elemento = $this->emlLib->getMailArchivio($email['ROWID'], 'rowid');
                                $retDecode = $this->getStruttura($email['ROWID']);
                                $this->elencoAllegati = $this->caricaElencoAllegati($retDecode);
                                $this->decodSegnaturaCert($retDecode);
                                $risultato = $this->assegnaEmlToProtocollo($email['ANAPRO']);
                                if ($risultato === false) {
                                    Out::msgStop("Attenzione!", "L'email con Oggetto: <br>" . $email['SUBJECT'] . "<br>non è stato possibile Associarla.
                                        <br>Effettuare l'associazione manualmente.");
                                }
                            }
                            $this->openRicerca();
                        }
                        break;
                    case 'DaScartare':
                        if ($_POST['retKey'] != '') {
                            foreach ($chiavi as $chiave) {
                                $this->scartaMail($chiave);
                            }
                            $this->openRicerca();
                        }
                        break;
                }
                break;
            case $this->nameForm . "_returnUploadFile":
                $randName = md5(rand() * time()) . "." . pathinfo($_POST['uploadedFile'], PATHINFO_EXTENSION);
                if (!@copy($_POST['uploadedFile'], $this->emailTempPath . $randName)) {
                    Out::msgStop("Attenzione", "salvataggio file:" . $_POST['uploadedFile'] . " fallito.");
                } else {
                    $this->caricaTabEmailLocali();
                }
                break;
            case "returnMailRispondi":
                $DaRispondi = true;
            case "returnMail":
                // ATTENZIONE: INOLTRA MAIL PERMETTE DI INOLTRARE SOLO MAIL PRESENTI SU MAIL_ARCHIVIO E NON LOCALI
                //
                // Analizzo il messaggio originale da inoltrare
                //
                $retDecode = $this->currMessage->getStruct();
                if ($retDecode['ita_PEC_info'] != "N/A") {
                    $messaggioOriginale = $retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile'];
                }
                if (!$messaggioOriginale) {
                    $messaggioOriginale = $retDecode;
                }

                if (isset($messaggioOriginale['DataFile'])) {
                    $datafile = $messaggioOriginale['DataFile'];
                } else {
                    foreach ($messaggioOriginale['Alternative'] as $value) {
                        $datafile = $value['DataFile'];
                    }
                }
                // USARE SERVIZIOINVIAMAIL CON PARAMETRI?
                //
                // Preparo il messaggio in uscita
                //
                /* @var $emlMailBox emlMailBox */
                $anaent_26 = $this->proLib->GetAnaent('26');
                if ($anaent_26) {
                    $accountSMTP = $anaent_26['ENTDE4'];
                    $ricevutaPECBreve = $anaent_26['ENTVAL'];
                }
                if ($_POST['valori']['ForzaDaMail']) {
                    $accountSMTP = $_POST['valori']['ForzaDaMail'];
                }
                include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
                $emlMailBox = emlMailBox::getInstance($accountSMTP);
                if (!$emlMailBox) {
                    Out::msgStop('Inoltro Mail', "Impossibile accedere alle funzioni dell'account: " . $this->refAccounts[0]['EMAIL']);
                    break;
                }
                if ($ricevutaPECBreve) {
                    $emlMailBox->setPECRicvutaBreve();
                }
                /* @var $outgoingMessage emlOutgoingMessage */
                $outgoingMessage = $emlMailBox->newEmlOutgoingMessage();
                if (!$outgoingMessage) {
                    Out::msgStop('Inoltro Mail', "Impossibile creare un nuovo messaggio in uscita.");
                    break;
                }
                $outgoingMessage->setSubject($_POST['valori']['Oggetto']);
                $posIframe = strpos($_POST['valori']['Corpo'], "<iframe");
                $newCorpo = substr($_POST['valori']['Corpo'], 0, $posIframe);
                $outgoingMessage->setBody($newCorpo . file_get_contents($datafile));
                $outgoingMessage->setEmail($_POST['valori']['Email']);
                $outgoingMessage->setAttachments($_POST['allegati']);
                //
                // Invio il messaggio i uscita
                //
                $mailArchivio_rec = $emlMailBox->sendMessage($outgoingMessage);
                if ($mailArchivio_rec) {
                    if ($DaRispondi !== true) {
                        /*
                         * Setto la mail come inoltrata
                         */
                        $classe = '@INOLTRATA_PROTOCOLLO@';
                        include_once ITA_BASE_PATH . '/apps/Mail/emlDbMailBox.class.php';
                        $emlDbMailBox = new emlDbMailBox();
                        $risultato = $emlDbMailBox->updateClassForRowId($this->elemento['ROWID'], $classe);
                        if ($risultato === false) {
                            Out::msgStop('Inoltro Mail - Archivio', $emlDbMailBox->getLastMessage());
                            break;
                        }
                        /*
                         * Classifico l'inoltro e lo collego alla inoltrata
                         * 
                         */
                        $classeInoltro = '@INOLTRO_PROTOCOLLO@';
                        $risultatoInoltro = $emlDbMailBox->updateClassForRowId($mailArchivio_rec['ROWID'], $classeInoltro);
                        if ($risultatoInoltro === false) {
                            Out::msgStop('Inoltro Mail - Archivio', $emlDbMailBox->getLastMessage());
                            break;
                        }
                        $risultatoParent = $emlDbMailBox->updatelParentForRowId($mailArchivio_rec['ROWID'], $this->elemento['IDMAIL']);
                        if ($risultatoParent === false) {
                            Out::msgStop('Inoltro Mail - Archivio', $emlDbMailBox->getLastMessage());
                            break;
                        }
                        Out::msgInfo('Inoltro Mail', "E-Mail inviata con successo a <b>" . $_POST['valori']['Email'] . "</b>");
                        $this->openRicerca();
                    } else {
                        // Trovare un modo di collegare la mail di risposta?
                        // Collego id mail inoltrata alla mail di partenza
                        // Ricavo ROWID mail:  
                        $this->CollegaMailPadre($mailArchivio_rec['IDMAIL'], $this->elemento['ROWID']);
                        Out::msgInfo('Risposta Mail', "Mail di Risposta inviata con successo a <b>" . $_POST['valori']['Email'] . "</b>");
                    }
                } else {
                    Out::msgStop('Inoltro Mail', $emlMailBox->getLastMessage());
                }
                break;

            case 'apriMailInoltrata':
                if (!$_POST['id']) {
                    Out::msgStop("Attenzione", "ID MAIL non definito.");
                    break;
                }
                $id = $_POST['id'];
                $model = 'emlViewer';
                $_POST['event'] = 'openform';
                $_POST['codiceMail'] = $id;
                $_POST['tipo'] = 'id';
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
            case 'broadcastMsg':
                switch ($_POST['message']) {
                    case "UPDATE_CONT_LISTVIEWER":
                        $nameForm = $this->nameFormListViewer;
                        $proListViewer = itaModel::getInstance('proListViewer', $nameForm);
                        $tot = $proListViewer->getConteggioProtocolli();
                        Out::tabSetTitle($this->nameForm . "_tabMail", $this->nameForm . "_paneProtIncompleti", 'Protocolli Incompleti <span style="color:red; font-weight:bold;">(' . $tot . ') </span>');
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        $this->azzera(false, false, false);
        App::$utente->removeKey($this->nameForm . '_certificato');
        App::$utente->removeKey($this->nameForm . '_elencoAllegati');
        App::$utente->removeKey($this->nameForm . '_elencoAllegatiOrig');
        App::$utente->removeKey($this->nameForm . '_elemento');
        App::$utente->removeKey($this->nameForm . '_currMessage');
        App::$utente->removeKey($this->nameForm . '_currObjSdi');
        App::$utente->removeKey($this->nameForm . '_currMailBox');
        App::$utente->removeKey($this->nameForm . '_dettagliFile');
        App::$utente->removeKey($this->nameForm . '_elementoLocale');
        App::$utente->removeKey($this->nameForm . '_currAlert');
        App::$utente->removeKey($this->nameForm . '_refAccounts');
        App::$utente->removeKey($this->nameForm . '_scartati');
        App::$utente->removeKey($this->nameForm . '_elencoMailSdi');
        App::$utente->removeKey($this->nameForm . '_ultimaMailSdi');
        App::$utente->removeKey($this->nameForm . '_currAllegatoEml');
        App::$utente->removeKey($this->nameForm . '_nameFormListViewer');
        App::$utente->removeKey($this->nameForm . '_refAllAccounts');
        App::$utente->removeKey($this->nameForm . '_datiSegnatura');
        App::$utente->removeKey($this->nameForm . '_lockMailInLettura');

        Out::closeDialog($this->nameForm);
    }

    public function returnToParent() {
        parent::close();
    }

    private function Nascondi() {
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Protocolla');
        Out::hide($this->nameForm . '_Ricevi');
        Out::hide($this->nameForm . '_AssegnaProttocolli');
        Out::hide($this->nameForm . '_CertificatoV');
        Out::hide($this->nameForm . '_CertificatoNV');
        Out::hide($this->nameForm . '_Scarta');
        Out::hide($this->nameForm . '_Ripristina');
        Out::hide($this->nameForm . '_AssegnaProt');
        Out::hide($this->nameForm . '_ScartaMulti');
        Out::hide($this->nameForm . '_divButCert');
        Out::hide($this->nameForm . '_divButPec');
        Out::hide($this->nameForm . '_DatiPec');
        Out::hide($this->nameForm . '_Inoltra');
        Out::hide($this->nameForm . '_divFatturaPA');
        Out::hide($this->nameForm . '_divAvvisoFatturaPa');
        Out::hide($this->nameForm . '_AssegnaProtocolloSDI');
        Out::hide($this->nameForm . '_AssegnaNotificheSDI');
        Out::hide($this->nameForm . '_Rispondi');
        Out::hide($this->nameForm . '_TornaElencoScarti');
    }

    private function Azzera($valData = true, $clearToolbar = true, $clearSdi = true) {
        if ($valData === true) {
            $data = date('Ymd', strtotime('-60 day', strtotime(date('Ymd'))));
            Out::valore($this->nameForm . '_Dadata', $data);
            Out::valore($this->nameForm . '_Adata', date('Ymd'));
        }
        $this->clearCurrMessage($clearSdi);
        if ($this->currMailBox != null) {
            $this->currMailBox->close();
            $this->currMailBox = null;
        }
        $this->elencoAllegati = array();
        $this->elencoAllegatiOrig = array();
        $this->certificato = array();
        $this->elementoLocale = null;
        $this->ultimaMailSdi = '';
        $this->datiSegnatura = array();
        if ($clearToolbar) {
//            TableView::disableEvents($this->gridElencoMailScarti);
            TableView::clearToolbar($this->gridElencoMailScarti);
//            TableView::enableEvents($this->gridElencoMailScarti);
        }
        Out::html($this->nameForm . '_divSoggetto', '');
    }

    private function openRicerca($azzeraDati = true) {
        if ($this->lockMailInLettura) {
            if (!$this->proLibMail->unlockMail($this->lockMailInLettura)) {
                Out::msgInfo('Attenzione', $this->proLibMail->getErrMessage());
            }
            $this->lockMailInLettura = array();
        }

        $this->currAlert = "";
        $this->Azzera($azzeraDati);
        $this->caricaTabella();
        $this->caricaTabEmailLocali();
        $this->Nascondi();
        Out::show($this->nameForm);
        Out::show($this->divRis);
        Out::hide($this->nameForm . '_divDettaglio');
        Out::hide($this->nameForm . '_divInfoMail');
        Out::show($this->nameForm . '_Ricevi');
        Out::show($this->nameForm . '_AssegnaProttocolli');
        Out::show($this->nameForm . '_ScartaMulti');
        $Anaent40_rec = $this->proLib->GetAnaent('40');
        if ($Anaent40_rec['ENTDE4']) {
            Out::show($this->nameForm . '_AssegnaNotificheSDI');
        }
    }

    private function caricaTabella($tipo = '1') {
        $whereFiltri = $this->getWhereFiltri();
        $this->setElencoMailSdi();
        $Classi = proLibMail::$ElencoClassProtocollabili;
//        $this->CaricaGriglia($this->gridElencoMail, '@DA_PROTOCOLLARE@', $whereFiltri, $tipo);
        $ElencoMail_tab = $this->CaricaGriglia($this->gridElencoMail, $Classi, $whereFiltri, $tipo);

        $this->getAlert($Classi, $whereFiltri);
        $this->refreshAlert();
    }

    private function caricaTabellaScarti($where = '', $tipo = '1') {
        $where .= $this->getWhereFiltriScarti();
        if ($_POST['PRESALLEGATI'] != '') {
            $where .= " AND ATTACHMENTS<>''";
        }
        if ($_POST['FROMADDR'] != '') {
            $where .= " AND " . $this->ITALWEB->strUpper('FROMADDR') . " LIKE '%" . strtoupper($_POST['FROMADDR']) . "%'";
        }
        if ($_POST['PEC'] != '') {
            $where .= " AND " . $this->ITALWEB->strUpper('PECTIPO') . " LIKE '%" . strtoupper($_POST['PEC']) . "%'";
        }
        if ($_POST['SUBJECT'] != '') {
            $where .= " AND " . $this->ITALWEB->strUpper('SUBJECT') . " LIKE '%" . strtoupper($_POST['SUBJECT']) . "%'";
        }
        if ($_POST['ACCOUNT'] != '') {
            $where .= " AND " . $this->ITALWEB->strUpper('ACCOUNT') . " LIKE '%" . strtoupper($_POST['ACCOUNT']) . "%'";
        }
        if ($_POST['DATA'] != '') {
            $where .= " AND " . $this->ITALWEB->strUpper('MSGDATE') . " LIKE '" . strtoupper($_POST['DATA']) . "%'";
        }
        if ($_POST[$this->nameForm . '_Dadata'] != '' && $_POST[$this->nameForm . '_Dadata'] >= '20010101') {
            $where .= " AND " . $this->ITALWEB->subString('MSGDATE', 1, 8) . " >= '" . $_POST[$this->nameForm . '_Dadata'] . "'";
        }
        if ($_POST[$this->nameForm . '_Adata'] != '' && $_POST[$this->nameForm . '_Adata'] >= '20010101') {
            $where .= " AND " . $this->ITALWEB->subString('MSGDATE', 1, 8) . " <= '" . $_POST[$this->nameForm . '_Adata'] . "'";
        }
        $tipoArchivio = array('@SCARTATO_PROTOCOLLO@', '@INOLTRATA_PROTOCOLLO@');
        $this->CaricaGriglia($this->gridElencoMailScarti, $tipoArchivio, $where, $tipo);
    }

    private function CaricaGriglia($griglia, $tipoArchivio, $whereFiltri, $tipo = '1') {
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $order = '';
        if ($_POST['sord'] != '') {
            $order = $_POST['sord'];
        } else {
            $order = 'asc';
        }
        $ordinamento = $_POST['sidx'];
        if ($_POST['sidx'] == 'PRESALLEGATI' || $_POST['sidx'] == 'SEGNATURA') {
            $ordinamento = 'ATTACHMENTS';
        }
        if ($_POST['sidx'] == 'PEC') {
            $ordinamento = 'PECTIPO';
        }
        if ($_POST['sidx'] == 'DATA' || $_POST['sidx'] == 'ORA' || $ordinamento == '') {
            $ordinamento = 'MSGDATE';
        }
        if ($_POST['sidx'] == 'FROMADDRORIG') {
            $ordinamento = 'FROMADDR';
        }
        $oggi = date('Ymd');


        $sql = "SELECT *,";
        $sql .= $this->ITALWEB->dateDiff(
                        $this->ITALWEB->coalesce("'$oggi'"), 'MSGDATE'
                ) . " AS GIORNI ";
        $sql .= " FROM MAIL_ARCHIVIO WHERE ";
        // Se tipoArchvio è un array
        if (is_array($tipoArchivio)) {
            $sql .= " (";
            foreach ($tipoArchivio as $classe) {
                $sql .= " CLASS ='$classe' OR ";
            }
            $sql = substr($sql, 0, -3) . ')';
            $sql .= $whereFiltri;
        } else {
            $sql .= " CLASS ='$tipoArchivio'" . $whereFiltri;
        }
        $ita_grid01 = new TableView($griglia, array('sqlDB' => $this->ITALWEB,
            'sqlQuery' => $sql));
        if ($tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(14);
            $ita_grid01->setSortOrder('asc');
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
            $ita_grid01->setSortOrder($_POST['sord']);
        }
        $ita_grid01->setSortIndex($ordinamento);
        // Elabora il risultato
        $result_tab = $ita_grid01->getDataArray();
        foreach ($result_tab as $key => $mail) {
            $metadata = unserialize($mail["METADATA"]);
            $ini_tag = "<p style = 'font-weight:lighter;'>";
            $fin_tag = "</p>";
            $icon_mail = "<span title = \"Messaggio letto.\" class=\"ita-icon ita-icon-apertagray-24x24\" style = \"float:left;display:inline-block;\"></span>";
            if ($mail['READED'] == 0) {
                $ini_tag = "<p style = 'font-weight:900;'>";
                $fin_tag = "</p>";
                $icon_mail = "<span title = \"Messaggio da leggere.\" class=\"ita-icon ita-icon-chiusagray-24x24\" style = \"float:left;display:inline-block;\"></span>";
            }
            if ($mail['CLASS'] == proLibMail::IN_PROTOCOLLAZIONE) {
                if ($mail['READED'] == 0) {
                    $ini_tag = "<p style = 'font-weight:900; color:red;'>";
                    $fin_tag = "</p>";
                    $icon_mail = "<span title = \"Messaggio da leggere.\" class=\"ita-icon ita-icon-chiusared-24x24\" style = \"float:left;display:inline-block;\"></span>";
                } else {
                    $ini_tag = "<p style = 'font-weight:lighter; color:red;'>";
                    $fin_tag = "</p>";
                    $icon_mail = "<span title = \"Messaggio letto.\" class=\"ita-icon ita-icon-apertared-24x24\" style = \"float:left;display:inline-block;\"></span>";
                }
            }

            if ($result_tab[$key]['INTEROPERABILE'] > 0) {
                $result_tab[$key]['SEGNATURA'] = '<span title ="Interoperabilità" class="ita-icon ita-icon-flag-it-24x24"></span>';
            }
            $FattElett = false;
            if (in_array($result_tab[$key]['FROMADDR'], $this->elencoMailSdi) && $result_tab[$key]['PECTIPO'] == 'posta-certificata') {
                $result_tab[$key]['SEGNATURA'] = '<span title ="Fattura Elettronica" class="ita-icon ita-icon-euro-blue-24x24"></span>';
                $FattElett = true;
            } else {
                if ($metadata['emlStruct']['ita_PEC_info'] != "N/A") {
                    if ($metadata['emlStruct']['ita_PEC_info']['dati_certificazione']) {
                        if (in_array($metadata['emlStruct']['ita_PEC_info']['dati_certificazione']['mittente'], $this->elencoMailSdi) &&
                                $result_tab[$key]['PECTIPO'] == 'posta-certificata') {
                            $result_tab[$key]['SEGNATURA'] = '<span title ="Fattura Elettronica" class="ita-icon ita-icon-euro-blue-24x24"></span>';
                            $FattElett = true;
                        }
                    }
                }
            }

            $result_tab[$key]["DATA"] = date('d/m/Y', strtotime(substr($mail['MSGDATE'], 0, 8)));
            $result_tab[$key]["ORA"] = substr($mail['MSGDATE'], 8);
            $pec = $result_tab[$key]['PECTIPO'];
            if ($pec != '') {
                $icon_mail = "<span title = \"PEC " . $pec . ", letto.\" class=\"ita-icon ita-icon-apertagreen-24x24\" style = \"float:left;display:inline-block;\"></span>";
                if ($mail['READED'] == 0) {
                    $icon_mail = "<span title = \"PEC " . $pec . ", da leggere.\" class=\"ita-icon ita-icon-chiusagreen-24x24\" style = \"float:left;display:inline-block;\"></span>";
                }
                $result_tab[$key]["PEC"] = $pec;
            }
            $result_tab[$key]['PRESALLEGATI'] = $icon_mail;
            if ($result_tab[$key]['CLASS'] == '@INOLTRATA_PROTOCOLLO@') {
                $result_tab[$key]['PRESALLEGATI'] = $this->CaricaInoltrato($mail);
            }
            if ($mail['ATTACHMENTS'] != '') {
                $result_tab[$key]['PRESALLEGATI'] .= '<span title = "Presenza Allegati" style="margin:2px; display:inline-block; vertical-align:middle;" class="ita-icon ita-icon-clip-16x16" ></span>';
            }
            if ($metadata['emlStruct']['ita_PEC_info'] != "N/A") {
                if ($metadata['emlStruct']['ita_PEC_info']['dati_certificazione']) {
                    $result_tab[$key]['FROMADDRORIG'] = '<p style="background:lightgreen;color: darkgreen;">' . $metadata['emlStruct']['ita_PEC_info']['dati_certificazione']['mittente'] . '</p>';
                }
            }

            $opacity = "";
            if ($mail['GIORNI'] && $FattElett == true) {
                $opacity1 = (($mail["GIORNI"] <= 15) ? $mail["GIORNI"] * (100 / 15) : 100) / 100;
                $opacity = "background:rgba(255,0,0,$opacity1);";
            }
            $result_tab[$key]["GIORNI"] = '<div style="height:100%;padding-left:2px;text-align:center;' . $opacity . '"><span style="vertical-align:middle;opacity:1.00;">' . $result_tab[$key]["GIORNI"] . '</span></div>';

            $result_tab[$key]['FROMADDR'] = $ini_tag . $result_tab[$key]['FROMADDR'] . $fin_tag;
            $result_tab[$key]['SUBJECT'] = $ini_tag . $result_tab[$key]['SUBJECT'] . $fin_tag;
            $result_tab[$key]['ACCOUNT'] = $ini_tag . $result_tab[$key]['ACCOUNT'] . $fin_tag;
            $result_tab[$key]['DATA'] = $ini_tag . $result_tab[$key]['DATA'] . $fin_tag;
            $result_tab[$key]['ORA'] = $ini_tag . $result_tab[$key]['ORA'] . $fin_tag;
            $result_tab[$key]['PEC'] = $ini_tag . $result_tab[$key]['PEC'] . $fin_tag;
        }
        $ita_grid01->getDataPageFromArray('json', $result_tab);
        return $result_tab;
    }

    private function Dettaglio() {
        $this->lockMailInLettura = $this->proLibMail->lockMailVisualizzazione($_POST['rowid']);
        if (!$this->lockMailInLettura) {
            Out::msginfo('Lettura Mail', $this->proLibMail->getErrMessage());
            return false;
        }
        $this->elemento = $this->emlLib->getMailArchivio($_POST['rowid'], 'rowid');
        Out::tabSelect($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneMail");
        $this->analizzaMail($_POST['rowid']);
        if ($this->elemento) {
            Out::valore($this->nameForm . '_DataSendRic', $this->elemento['SENDRECDATE']);
            Out::valore($this->nameForm . '_OraSendRic', $this->elemento['SENDRECTIME']);
        }

        Out::setFocus($this->nameForm, $this->nameForm . '_gridAllegati'); // Serve per mantere il focus sulla form
        return true;
    }

    private function setRefAccounts() {
        $this->refAccounts = array();
        $utente = App::$utente->getKey('nomeUtente');
        $tipoPerm = 'rec';
        $ElencoMailUtente = $this->emlLib->GetMailAutorizzazioni($utente, 'login', true, true);
        $ElencoMailUfficioUtente = $this->proLib->GetMailAutorizzazioniUfficioUtente($utente, '');
        $ElencoMail = array_merge($ElencoMailUtente, $ElencoMailUfficioUtente);

        if ($ElencoMail) {
            foreach ($this->refAllAccounts as $Account) {
                foreach ($ElencoMail as $key => $value) {
                    /*
                     * Controllo se abilitato alla ricezione 
                     */
                    if (!$value['PERM_REC']) {
                        continue;
                    }
                    if ($Account['EMAIL'] == $value['MAIL'] || $Account['EMAIL'] == $value['UFFMAIL']) {
                        $this->refAccounts[] = $Account;
                        break;
                    }
                }
            }
        } else {
            $this->refAccounts = $this->refAllAccounts;
        }
    }

    private function setRefAllAccounts() {
        $this->refAllAccounts = array();
        /*
         * Estragggo Accaunt Mail Parametrizzati per lo  scarico
         */
        $anaent_28 = $this->proLib->GetAnaent('28');
        if ($anaent_28) {
            $this->refAllAccounts = unserialize($anaent_28['ENTVAL']);
        }
        /*
         * Aggiungo mail di cattura inoltro
         */
        $anaent_52 = $this->proLib->GetAnaent('52');
        if ($anaent_52) {
            $ElencoMailInoltro = unserialize($anaent_52['ENTVAL']);
            if ($ElencoMailInoltro) {
                $this->refAllAccounts = array_merge($this->refAllAccounts, $ElencoMailInoltro);
            }
        }
        return $this->refAllAccounts;
    }

    private function setElencoMailSdi() {
        $this->elencoMailSdi = array();
        $anaent_38 = $this->proLib->GetAnaent('38');
        if ($anaent_38) {
            $ElencoMail = unserialize($anaent_38['ENTVAL']);
            foreach ($ElencoMail as $Mail) {
                $this->elencoMailSdi[] = $Mail['EMAIL'];
            }
        }
    }

    private function scaricaPosta() {
        $htmlLog = "";
        $htmlErr = "";
        if (!$this->refAllAccounts) {
            Out::msgStop("Attenzione!", "Non sono stati configurati account di posta.");
            return;
        }
        /*
         * Setto chiavi per semafori
         */
        $chiave = 'SYNCRONIZEACCOUNT_MAILARCHIVIO';
        $procedura = 'RICEZIONE MAIL';
        $tipoblocco = envLib::TIPO_BLOCCO_CHIAVE;
        /*
         * Sblocco Mail Account con i Semafori (e controllo se bloccati)
         */
        if ($this->envLib->Semaforo('SBLOCCA', $chiave, $procedura, $tipoblocco) === false) {
            $messaggio = "<div style=\"overflow:auto; max-height:400px; max-width:600px;margin:6px;padding:6px;\" class=\"ita-box ui-state-highlight ui-corner-all ita-Wordwrap\">";
            $messaggio .= $this->envLib->getErrMessage() . "<br></div>";
            Out::msgDialog("Ricezione Messaggi", $messaggio);
            return false;
        }
        /*
         * Blocco Mail Account con i Semafori
         */
        if ($this->envLib->Semaforo('BLOCCA', $chiave, $procedura, $tipoblocco) === false) {
            $messaggio = "<div style=\"overflow:auto; max-height:400px; max-width:600px;margin:6px;padding:6px;\" class=\"ita-box ui-state-highlight ui-corner-all ita-Wordwrap\">";
            $messaggio .= $this->envLib->getErrMessage() . "<br></div>";
            Out::msgDialog("Ricezione Messaggi", $messaggio);
            return false;
        }

        foreach ($this->refAllAccounts as $value) {
            $emlMailbox = new emlMailBox($value['EMAIL']);
            $retSync = $emlMailbox->syncronizeAccount('@DA_PROTOCOLLARE@'); /* Classe che deve apporre alle mail scaricate */
            if ($retSync === false) {
                $htmlErr .= "<div style=\"overflow:auto; max-height:400px; max-width:600px;margin:6px;padding:6px;\" class=\"ita-box ui-state-error ui-corner-all ita-Wordwrap\">";
                $htmlErr .= $value['EMAIL'] . "- Errore in ricezione: " . $emlMailbox->getLastMessage() . "<br>";
                $htmlErr .= "</div>";
            } else {
                $htmlLog .= "<div style=\"overflow:auto; max-height:400px; max-width:600px;;margin:6px;padding:6px;\" class=\"ita-box ui-state-highlight ui-corner-all ita-Wordwrap\">";
                $htmlLog .= $value['EMAIL'] . "- Ricezione Completata: " . count($retSync) . " nuovi messaggi.<br>";
                $htmlLog .= "</div>";
            }
        }



        $assegnati = $this->proLibMail->assegnaRicevute();
        if ($assegnati) {
            $htmlLog .= "<div style=\"overflow:auto; max-height:400px; max-width:600px;;margin:6px;padding:6px;\" class=\"ita-box ui-state-highlight ui-corner-all ita-Wordwrap\">";
            $htmlLog .= "Sono state assegnate: $assegnati email ai relativi Protocolli.<br>";
            $htmlLog .= "</div>";
        }

        $this->proLibMail->ElaboraMailInteroperabili();
        $MailProtocollate = $this->proLibMail->getMailProtocollate();
        if ($MailProtocollate) {
            $CountProt = count($MailProtocollate);
            $htmlLog .= "<div style=\"overflow:auto; max-height:300px; max-width:600px;;margin:6px;padding:6px; background-color:#00cc00; color:white;\" class=\"ita-box ui-corner-all ita-Wordwrap\">";
            $htmlLog .= "Sono state protocollate " . count($MailProtocollate) . " pec/mail interoperabili: <br>";
            $htmlProtInt = ' ';
            foreach ($MailProtocollate as $MailProtocollata) {
                $htmlProtInt .= ' - ' . intval($MailProtocollata['numeroProtocollo']) . '/' . $MailProtocollata['annoProtocollo'] . ' <br> ';
            }
            //$htmlLog .= "Sono state protocollate: <br><pre>" . print_r($MailProtocollate, true) . "<pre><br>";
            $htmlLog .= $htmlProtInt . "</div>";
            // @TODO Avviso
        }
        $MailInteropAssegnate = $this->proLibMail->getMailInteropAssegnate();
        if ($MailInteropAssegnate) {
            $CountProt = count($MailInteropAssegnate);
            $htmlLog .= "<div style=\"overflow:auto; max-height:300px; max-width:600px;;margin:6px;padding:6px;background-color:#00cc00; color:white;\" class=\"ita-box ui-corner-all ita-Wordwrap\">";
            $htmlMailInter = ' ';
            foreach ($MailInteropAssegnate as $RowidDocInterop) {
                $Anadoc_rec = $this->proLib->GetAnadoc($RowidDocInterop, 'rowid');
                $MailInterop .= ' - ' . substr($Anadoc_rec['DOCNUM'], 4) . '/' . substr($Anadoc_rec['DOCNUM'], 0, 4) . "<br>";
            }
            $htmlLog .= "Assegnate " . count($MailInteropAssegnate) . " mail interoperabili ai relativi protocolli: <br>" . print_r($MailInterop, true);
            $htmlLog .= "</div>";
        }
        $ErrMail = $this->proLibMail->getMailErrore();
        if ($ErrMail) {
            $htmlErr .= "<div style=\"overflow:auto; font-size:0.8em; max-height:180px; max-width:600px;margin:6px;padding:6px;\" class=\"ita-box ui-state-error ui-corner-all ita-Wordwrap\">";
            $htmlProtErr = ' ';
            foreach ($ErrMail as $Mail) {
                $htmlProtErr .= '<div style="margin-left:10px;"><div style="display:inline-block;width:60px;">Oggetto:</div><div style="display:inline-block">' . substr($Mail['Oggetto'], 0, 50) . '..</div><br>';
                $htmlProtErr .= '<div style="display:inline-block;width:60px;">Errore:</div><div style="display:inline-block">' . $Mail['Errore'] . '</div></div><br>';
            }
            $htmlErr .= "Errore in elaborazione mail interoperabili: <br>" . $htmlProtErr . "<br>";
            $htmlErr .= "</div>";
        }

        Out::msgDialog("Ricezione messaggi", $htmlErr . $htmlLog);
        /*
         * Sblocco Mail Account con i Semafori
         */
        if ($this->envLib->Semaforo('SBLOCCA', $chiave, $procedura, $tipoblocco) === false) {
            Out::msgInfo("Ricezione Messaggi", $this->envLib->getErrMessage());
            // return false;// Deve tornare false?
        }
        $this->caricaTabella();
        $this->RicaricaIncompleti();
    }

    private function analizzaCurrObSdi($retDecode) {
        if (!$this->currObjSdi) {
            Out::msgStop("Attenzione", "Errore in analisi fatturazione elettronica.");
            return false;
        }
        if ($this->currObjSdi->getErrCode() != 0) {
            Out::msgStop("Attenzione Errore Analisi Fattura", $this->currObjSdi->getErrMessage());
            return false;
        }
        if ($this->currObjSdi->isMessaggioSdi()) {
            $this->ControllaMittenteMailSdi($retDecode);
            $this->VisualizzaFatturaElettronica();
            //$this->VisualizzaMessaggioDT($this->currObjSdi);
        } else {
            Out::hide($this->nameForm . '_divFatturaPA');
            Out::hide($this->nameForm . '_divAvvisoFatturaPa');
            Out::tabDisable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneFattEle");
        }

        return true;
    }

    private function analizzaMail($rowid) {
        $this->Nascondi();
        $ErrFattura = false;
        $retDecode = $this->getStruttura($rowid);
        // $retDecode['Recipients'][...]['Status'] === 5.1.1 => Undelivered message
        $ret = $this->analizzaCurrObSdi($retDecode);
        if (!$ret) {
            $ErrFattura = true;
        }
        /* Qui controllo presenza allegati malformati */
        $this->CheckAnomalieAllegati($retDecode, true);
        $this->elencoAllegati = $this->caricaElencoAllegati($retDecode);
        /*
         * Allegati Segnatura:
         */
        $this->CaricaAllegatiDaSegnatura($this->elencoAllegati, $this->elemento);

        $ita_grid01 = new TableView($this->gridAllegati, array('arrayTable' => $this->elencoAllegati, 'rowIndex' => 'idx'));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10000);
        TableView::enableEvents($this->gridAllegati);
        TableView::clearGrid($this->gridAllegati);
        $ita_grid01->getDataPage('json');
        //Elabora EllegatiTelematici:
        $this->ElaboraGridAllegatiTelematici($this->gridAllegati, $this->elencoAllegati);

        Out::valore($this->nameForm . '_Mittente', $this->elemento['FROMADDR']);
        Out::valore($this->nameForm . '_Oggetto', $this->elemento['SUBJECT']);
        Out::valore($this->nameForm . '_Data', substr($this->elemento['MSGDATE'], 0, 8));
        Out::valore($this->nameForm . '_Ora', trim(substr($this->elemento['MSGDATE'], 8)));
        $url = utiDownload::getUrl("emlbody.html", $retDecode['DataFile'], false, true);
        $iframe = '<iframe src="' . $url . '" frameborder="0" width="99%" height="95%" id="' . $this->nameForm . '_emlBMFrame">
            <p>Contenuto non visualizzabile.....</p>
            </iframe>';
        Out::html($this->nameForm . '_divSoggetto', $iframe);

        $this->decodSegnaturaCert($retDecode);
        $letto = array(
            'ROWID' => $this->elemento['ROWID'],
            'READED' => 1
        );
        $update_Info = 'Oggetto: set Email Letta, rowid:' . $letto['ROWID'] . " valore:" . $letto['READED'];
        $this->updateRecord($this->ITALWEB, 'MAIL_ARCHIVIO', $letto, $update_Info);
    }

    private function ControllaMittenteMailSdi($retDecode) {
        //Controllo se è abilitato il salvataggio dei mittenti:
        $this->ultimaMailSdi = '';
        $anaent_38 = $this->proLib->GetAnaent('38');
        Out::hide($this->nameForm . '_AggiungiMittenteSDI');
        if ($anaent_38['ENTDE6']) {
            $anaent_39 = $this->proLib->GetAnaent('39');
            if ($anaent_38['ENTDE6'] == '1') {
                if (!in_array($retDecode['FromAddress'], $this->elencoMailSdi)) {
                    if ($anaent_39['ENTDE1'] == '1') {
                        $this->elencoMailSdi[] = $retDecode['FromAddress'];
                        $this->AggiungiMailSdi($retDecode['FromAddress']);
                    } else {
                        $this->ultimaMailSdi = $retDecode['FromAddress'];
                        Out::show($this->nameForm . '_AggiungiMittenteSDI');
                    }
                }
            } else if ($anaent_38['ENTDE6'] == '2') {
                if ($retDecode['ita_PEC_info'] != "N/A" && isset($retDecode['ita_PEC_info']['dati_certificazione']['mittente'])) {
                    $MailMittente = $retDecode['ita_PEC_info']['dati_certificazione']['mittente'];
                    if (!in_array($MailMittente, $this->elencoMailSdi)) {
                        if ($anaent_39['ENTDE1'] == '1') {
                            // Aggiunto Mail a elenco SDI..
                            $this->elencoMailSdi[] = $MailMittente;
                            $this->AggiungiMailSdi($MailMittente);
                        } else {
                            $this->ultimaMailSdi = $MailMittente;
                            Out::show($this->nameForm . '_AggiungiMittenteSDI');
                        }
                    }
                }
            }
        }
    }

    private function AggiungiMailSdi($MailSdi) {
        $anaent_38 = $this->proLib->GetAnaent('38');
        if ($anaent_38) {
            $ElencoMail = unserialize($anaent_38['ENTVAL']);
            $ElencoMail[] = array('EMAIL' => $MailSdi);
            $anaent_38['ENTVAL'] = serialize($ElencoMail);
            $update_Info = 'Aggiornameto Elemento Mail: ' . $anaent_38['ENTKEY'];
            if (!$this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_38, $update_Info)) {
                Out::msgStop("Attenzione", "Errore in aggiunta nell'elenco Mail SDI.");
                return false;
            }
        }
        return true;
    }

    private function VisualizzaFatturaElettronica() {
        Out::show($this->nameForm . '_divAvvisoFatturaPa');
        Out::tabEnable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneFattEle");
        $ret = $this->proLibSdi->GetVisualizzazioneFattura($this->currObjSdi);
        Out::html($this->nameForm . '_divDettaglioFatturaPA', $ret['contenutoDett']);
        Out::html($this->nameForm . '_divAvvisoFatturaPa', $ret['contenutoAvviso']);
    }

    private function decodSegnaturaCert($retDecode, $visualizzaBottoni = true) {
        if (is_array($retDecode['Signature'])) {
            $this->certificato['Signature'] = $retDecode['Signature'];
            if ($visualizzaBottoni === true) {
                Out::show($this->nameForm . '_divButCert');
                if ($retDecode['ita_Signature_info']['Verified'] == 1) {
                    Out::show($this->nameForm . '_CertificatoV');
                } else {
                    Out::show($this->nameForm . '_CertificatoNV');
                }
            }
        }
        if ($retDecode['ita_PEC_info'] != 'N/A') {
            $this->certificato['ita_PEC_info'] = $retDecode['ita_PEC_info']['dati_certificazione'];
            /* patch corridonia */
//            if ($retDecode['ita_PEC_info']['dati_certificazione']['tipo'] == 'accettazione' ||
//                    $retDecode['ita_PEC_info']['dati_certificazione']['tipo'] == 'avvenuta-consegna' ||
//                    $this->checkNotificaRicezione() != null) {
//                if ($visualizzaBottoni === true) {
//                    Out::show($this->nameForm . '_AssegnaProt');
//                }
//            }
//            if ($retDecode['ita_PEC_info']['dati_certificazione']['tipo'] != 'posta-certificata' || $this->checkNotificaRicezione() != null) {
            if ($visualizzaBottoni === true) {
                Out::show($this->nameForm . '_AssegnaProt');
            }
//            }
            if (is_array($retDecode['ita_PEC_info']['messaggio_originale'])) {
                $this->caricaDatiEmailOriginale($retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile']);
                Out::tabEnable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneOriginale");
                Out::tabSelect($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneOriginale");
            } else {
                Out::tabDisable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneOriginale");
            }
        } else {
            Out::tabDisable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneOriginale");
            Out::tabDisable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneCertificazione");
            if ($visualizzaBottoni === true) {
                Out::show($this->nameForm . '_AssegnaProt');
            }
        }
        $this->setCertificazione();
    }

    private function caricaDatiEmailOriginale($messaggioOriginale) {
        $this->elencoAllegatiOrig = $this->caricaElencoAllegati($messaggioOriginale);
        /*
         * Carica allegati da collocazione telematica
         */
        $this->CaricaAllegatiDaSegnatura($this->elencoAllegatiOrig, $this->elemento);
        $ita_grid01 = new TableView($this->gridAllegatiOrig, array('arrayTable' => $this->elencoAllegatiOrig, 'rowIndex' => 'idx'));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10000);
        TableView::enableEvents($this->gridAllegatiOrig);
        TableView::clearGrid($this->gridAllegatiOrig);
        $ita_grid01->getDataPage('json');
        //Elabora EllegatiTelematici:
        $this->ElaboraGridAllegatiTelematici($this->gridAllegatiOrig, $this->elencoAllegatiOrig);
        $addressFrom = '';
        foreach ($messaggioOriginale['From'] as $address) {
            $addressFrom = $address['address'];
        }
        Out::valore($this->nameForm . '_MittenteOrig', $addressFrom);
        Out::valore($this->nameForm . '_OggettoOrig', $messaggioOriginale['Subject']);
        Out::valore($this->nameForm . '_DataOrig', date('Ymd', strtotime($messaggioOriginale['Date'])));
        Out::valore($this->nameForm . '_OraOrig', trim(date('H:i:s', strtotime($messaggioOriginale['Date']))));
        $datafile = '';
        if ($messaggioOriginale['Type'] == 'pdf') {
            Out::html($this->nameForm . '_divSoggettoOrig', '');
            return;
        }
        if (isset($messaggioOriginale['DataFile'])) {
            $datafile = $messaggioOriginale['DataFile'];
        } else {
            foreach ($messaggioOriginale['Alternative'] as $value) {
                $datafile = $value['DataFile'];
            }
        }
        $url = utiDownload::getUrl("emlbody.html", $datafile, false, true);
        $iframe = '<iframe src="' . $url . '" frameborder="0" width="99%" height="95%" id="' . $this->nameForm . '_emlOrigFrame">
            <p>Contenuto non visualizzabile.....</p>
            </iframe>';
        Out::html($this->nameForm . '_divSoggettoOrig', $iframe);
    }

    private function scartaMail($rowid, $riabilita = false) {
        $classe = '@SCARTATO_PROTOCOLLO@';
        if ($riabilita === true) {
            $classe = '@DA_PROTOCOLLARE@';
        }
        include_once ITA_BASE_PATH . '/apps/Mail/emlDbMailBox.class.php';
        $emlDbMailBox = new emlDbMailBox();
        $risultato = $emlDbMailBox->updateClassForRowId($rowid, $classe);
        if ($risultato === false) {
            App::log($emlDbMailBox->getLastMessage());
        }
    }

    private function caricaTabEmailLocali() {
        $this->dettagliFile = array();
        $elencoFile = $this->GetFileList();
        foreach ($elencoFile as $mail) {
            $message = new emlMessage();
            $message->setEmlFile($mail['FILENAME']);
            $message->parseEmlFileDeep();
            $retDecode = $message->getStruct();
            $elencoDatiMail['MESSAGE-ID'] = $retDecode['Message-Id'];
            $elencoDatiMail['FILENAME'] = $mail['FILENAME'];
            $allegati = array();
            $elencoDatiMail['ALLEGATI'] = "";
            foreach ($retDecode['Attachments'] as $attach) {
                $allegati[] = $attach['FileName'];
            }
            $elencoDatiMail['ALLEGATI'] = implode("|", $allegati);
            $elencoDatiMail['SEGNATURA'] = '';
            foreach ($retDecode['Attachments'] as $value) {
                if (strtolower($value['FileName']) == 'segnatura.xml') {
                    $elencoDatiMail['SEGNATURA'] = '<span class="ita-icon ita-icon-clip-16x16"></span>';
                    break;
                }
            }
            //Controllo se la mail è tra i mittenti SDI [Controllo sia From e Mittente nei dati_certificazione]
            if ($retDecode['ita_PEC_info'] != "N/A") {
                if ($retDecode['ita_PEC_info']['dati_certificazione'] && $retDecode['ita_PEC_info']['dati_certificazione']['tipo'] == 'posta-certificata') {
                    if (in_array($retDecode['From'][0]['address'], $this->elencoMailSdi)) {
                        $elencoDatiMail['SEGNATURA'] = '<span title ="Fattura Elettronica" class="ita-icon ita-icon-euro-blue-24x24"></span>';
                    } else if (in_array($retDecode['ita_PEC_info']['dati_certificazione']['mittente'], $this->elencoMailSdi)) {
                        $elencoDatiMail['SEGNATURA'] = '<span title ="Fattura Elettronica" class="ita-icon ita-icon-euro-blue-24x24"></span>';
                    }
                }
            }

            $elencoDatiMail['MITTENTE'] = $retDecode['From'][0]['address'];
            $elencoDatiMail['OGGETTO'] = $retDecode['Subject'];
            $decodedDate = utiEmailDate::eDate2Date($retDecode['Date']);
            $elencoDatiMail["DATA"] = $decodedDate['date'];
            $elencoDatiMail["ORA"] = $decodedDate['time'];
            $elencoDatiMail["PEC"] = $elencoDatiMail["PECTIPO"] = '';
            $icon_mail = "<span title = \"Messaggio letto.\" class=\"ita-icon ita-icon-apertagray-24x24\"
                style = \"float:left;display:inline-block;\"></span>";
            if ($retDecode['ita_PEC_info'] != 'N/A') {
                $pec = $retDecode['ita_PEC_info']['dati_certificazione'];
                if (is_array($pec)) {
                    $elencoDatiMail["PEC"] = $elencoDatiMail["PECTIPO"] = $pec['tipo'];
                    $icon_mail = "<span title = \"PEC " . $pec['tipo'] . ", letto.\" class=\"ita-icon ita-icon-apertagreen-24x24\"
                        style = \"float:left;display:inline-block;\"></span>";
                }
            }
            $elencoDatiMail['PRESALLEGATI'] = $icon_mail;
            if ($retDecode['Attachments'] != '') {
                $elencoDatiMail['PRESALLEGATI'] .= '<span title = "Presenza Allegati" class="ita-icon ita-icon-clip-16x16" style = \"display:inline-block;\"></span>';
            }

            $message->cleanData();
            $this->dettagliFile[] = $elencoDatiMail;
        }
        $this->dettagliFile = $this->proLib->array_sort($this->dettagliFile, 'DATA', SORT_DESC);
        $ita_grid01 = new TableView($this->gridElencoMailLocale, array('arrayTable' => $this->dettagliFile, 'rowIndex' => 'idx'));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(14);
        TableView::enableEvents($this->gridElencoMailLocale);
        TableView::clearGrid($this->gridElencoMailLocale);
        $ita_grid01->getDataPage('json');
        return count($this->dettagliFile);
    }

    private function GetFileList() {
        if (!$dh = @opendir($this->emailTempPath))
            return false;
        $retListGen = array();
        $rowid = 0;
        while (($obj = @readdir($dh))) {
            if ($obj == '.' || $obj == '..')
                continue;
            $rowid += 1;
            $retListGen[$rowid] = array(
                'ROWID' => $rowid,
                'FILENAME' => $this->emailTempPath . $obj
            );
        }
        closedir($dh);
        return $retListGen;
    }

    private function DettaglioLocale() {
        $this->Nascondi();
        Out::tabDisable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneFattEle");
        Out::tabSelect($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneMail");
        $this->elementoLocale = $_POST['rowid'];
        $elemento = $this->dettagliFile[$this->elementoLocale];
        $this->currMessage = new emlMessage();
        $this->currMessage->setEmlFile($elemento['FILENAME']);
        $this->currMessage->parseEmlFileDeep();
//        Out::msgINfo('oggetto',print_r($this->currMessage,true));
        $retDecode = $this->currMessage->getStruct();
        //CurroObjSdi
        $ExtraParam = array();
        $ExtraParam['PARSEALLEGATI'] = true;
        $this->currObjSdi = proSdi::getInstance($retDecode, $ExtraParam);
        $ret = $this->analizzaCurrObSdi($retDecode);
        if (!$ret) {
            return false;
        }

        /* Controllo Anomalie negli allegati */
        $this->CheckAnomalieAllegati($retDecode, true);
        $this->elencoAllegati = $this->caricaElencoAllegati($retDecode);
        /*
         * Allegati Segnatura:
         */
        $this->CaricaAllegatiDaSegnatura($this->elencoAllegati, $this->elementoLocale);
        $ita_grid01 = new TableView($this->gridAllegati, array('arrayTable' => $this->elencoAllegati, 'rowIndex' => 'idx'));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10000);
        TableView::enableEvents($this->gridAllegati);
        TableView::clearGrid($this->gridAllegati);
        $ita_grid01->getDataPage('json');
        //Elabora EllegatiTelematici:
        $this->ElaboraGridAllegatiTelematici($this->gridAllegati, $this->elencoAllegati);
        Out::valore($this->nameForm . '_Mittente', $elemento['MITTENTE']);
//         $Oggetto = $this->NormalizzaCaratteri($elemento);
//         file_put_contents('/users/pc/dos2ux/mail.txt', $elemento['OGGETTO']);
//        Out::valore($this->nameForm . '_Oggetto', str_replace(chr(26), "*", $elemento['OGGETTO']));
        Out::valore($this->nameForm . '_Oggetto', $elemento['OGGETTO']);
        //Out::valore($this->nameForm . '_Oggetto', str_replace(chr(26), "*", $elemento['OGGETTO']));
        Out::valore($this->nameForm . '_Data', $elemento['DATA']);
        Out::valore($this->nameForm . '_Ora', trim($elemento['ORA']));
        $url = utiDownload::getUrl("emlbody.html", $retDecode['DataFile'], false, true);
        $iframe = '<iframe src="' . $url . '" frameborder="0" width="99%" height="95%" id="' . $this->nameForm . '_emlBLFrame">
            <p>Contenuto non visualizzabile.....</p>
            </iframe>';
        Out::html($this->nameForm . '_divSoggetto', $iframe);
        Out::hide($this->divRis);
        Out::show($this->nameForm . '_divDettaglio');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '_Protocolla');

        $this->decodSegnaturaCert($retDecode);
    }

    private function trovaPostaCertInAllegati() {
        $elementoKey = null;
        foreach ($this->elencoAllegati as $key => $value) {
            if ($value['DATAFILE'] == 'postacert.eml') {
                $elementoKey = $key;
            }
        }
        return $elementoKey;
    }

    private function unisciArray($primario, $secondario) {
        foreach ($secondario as $value) {
            $primario[] = $value;
        }
        return $primario;
    }

    private function getAlert($Classi = null, $whereFiltri = '') {
        //$sql = "SELECT CLASS AS CLASS, READED AS READED ,COUNT(ROWID) AS QUANTI FROM MAIL_ARCHIVIO WHERE CLASS IN ('@DA_PROTOCOLLARE@') GROUP BY CLASS, READED";
        if (!$Classi) {
            $Classi = proLibMail::$ElencoClassProtocollabili;
        }
        $whereClasse = ' ( ';
        foreach ($Classi as $classe) {
            $whereClasse .= " CLASS = '$classe' OR ";
        }

        $whereClasse = substr($whereClasse, 0, -3) . ') ';
        $sql = "SELECT CLASS AS CLASS, READED AS READED ,COUNT(ROWID) AS QUANTI FROM MAIL_ARCHIVIO WHERE $whereClasse $whereFiltri GROUP BY CLASS, READED";


        $Data_result = itaDb::DBSQLSelect($this->ITALWEB, $sql, true);
        $tot_new = 0;
        $tot_daprot = 0;
        foreach ($Data_result as $Record) {
            $tot_new += ($Record['READED'] == 0) ? $Record['QUANTI'] : 0;
            $tot_daprot += (in_array($Record['CLASS'], proLibMail::$ElencoClassProtocollabili)) ? $Record['QUANTI'] : 0;
        }

        return $this->currAlert = $tot_new . " Nuovi Messaggi -  " . $tot_daprot . " Da protocollare";
    }

    private function refreshAlert() {
        Out::html($this->divAlert, $this->currAlert);
    }

    private function getWhereFiltri() {
        $daData = $_POST[$this->nameForm . '_dallaData'];
        $aData = $_POST[$this->nameForm . '_allaData'];

        $sql = '';

        $where = array();

        if ($_POST[$this->nameForm . '_cbAccount']) {
            $sql .= " AND ACCOUNT = '" . $_POST[$this->nameForm . '_cbAccount'] . "' ";
        } else {
            if ($this->refAccounts) {
                $sql .= " AND ( ";
                foreach ($this->refAccounts as $key => $Account) {
                    $sql .= " ACCOUNT = '" . $Account['EMAIL'] . "' OR ";
                }
                $sql = substr($sql, 0, -3) . " ) ";
            } else {
                /*
                 *  Non deve prendere nessuna mail.
                 */
                $sql .= " AND 1 <> 1 ";
            }
            // Filtri Account:
//            $utente = App::$utente->getKey('nomeUtente');
//            $ElencoMail = $this->emlLib->GetMailAutorizzazioni($utente, 'login', true, true);
//            if ($ElencoMail) {
//                $sql .= " AND ( ";
//                foreach ($ElencoMail as $Email) {
//                    $sql .= " ACCOUNT = '" . $Email['MAIL'] . "' OR ";
//                }
//                $sql = substr($sql, 0, -3) . " ) ";
//            }
        }


        if ($_POST[$this->nameForm . '_cbTutti'] == 1) {
            return $sql;
        }

        if ($_POST[$this->nameForm . '_cbMsgInt'] == 1) {
            $where[] = "INTEROPERABILE > 0";
        }

        if ($_POST[$this->nameForm . '_cbMsgPEC'] == 1) {
            $where[] = "PECTIPO <> ''";
        }

        if ($_POST[$this->nameForm . '_cbAccettazione'] == 1) {
            $where[] = "PECTIPO = 'accettazione'";
        }

        if ($_POST[$this->nameForm . '_cbConsegna'] == 1) {
            $where[] = "PECTIPO = 'avvenuta-consegna'";
        }

        if ($_POST[$this->nameForm . '_cbMsgStd'] == 1) {
            $where[] = "PECTIPO = ''";
        }

        if ($_POST[$this->nameForm . '_cbMsgAnomalie'] == 1) {
            $where[] = "PECTIPO<>'posta-certificata' AND PECTIPO<>'accettazione' AND PECTIPO<>'avvenuta-consegna' AND PECTIPO<>''";
        }

        if (count($where)) {
            $sql .= " AND (" . implode(" OR ", $where) . ")";
        }

        if ($daData != '' && $aData != "") {
            $whereDate = "AND (" . $this->ITALWEB->subString('MSGDATE', 1, 8) . " BETWEEN '$daData' AND '$aData')";
        }

        if ($_POST[$this->nameForm . '_OggettoSrc']) {
            $sql .= " AND " . $this->PROT_DB->strLower('SUBJECT') . " LIKE '%" . strtolower($_POST[$this->nameForm . '_OggettoSrc']) . "%'";
        }
        if ($_POST[$this->nameForm . '_MittenteSrc']) {
            $sql .= " AND (" . $this->PROT_DB->strLower('FROMADDR') . " LIKE '%" . strtolower($_POST[$this->nameForm . '_MittenteSrc']) . "%' OR
                          " . $this->PROT_DB->strLower('METADATA') . " LIKE '%" . strtolower($_POST[$this->nameForm . '_MittenteSrc']) . "%')";
        }
        if ($whereDate) {
            $sql .= $whereDate;
        }
        return $sql;
    }

    private function getWhereFiltriScarti() {
        /*
         *  Controllo Filtri per Mail Utente:
         */
        $sql = '';
        /*
         * Filtri già presenti su refAccoutns: ed estrae solo quelli abilitati.
         */
        if ($this->refAccounts) {
            $sql .= " AND ( ";
            foreach ($this->refAccounts as $key => $Account) {
                $sql .= " ACCOUNT = '" . $Account['EMAIL'] . "' OR ";
            }
            $sql = substr($sql, 0, -3) . " ) ";
        }


        /* Visualizzo Tutti */
        if ($_POST[$this->nameForm . '_cbTuttiSc'] == 1) {
            return $sql;
        }
        $where = array();
        if ($_POST[$this->nameForm . '_cbMsgIntSc'] == 1) {
            $where[] = "INTEROPERABILE > 0";
        }

        if ($_POST[$this->nameForm . '_cbMsgPECSc'] == 1) {
            $where[] = "PECTIPO <> ''";
        }

        if ($_POST[$this->nameForm . '_cbAccettazioneSc'] == 1) {
            $where[] = "PECTIPO = 'accettazione'";
        }

        if ($_POST[$this->nameForm . '_cbConsegnaSc'] == 1) {
            $where[] = "PECTIPO = 'avvenuta-consegna'";
        }

        if ($_POST[$this->nameForm . '_cbMsgStdSc'] == 1) {
            $where[] = "PECTIPO = ''";
        }
        if ($_POST[$this->nameForm . '_cbInoltrate'] == 1) {
            $where[] = "CLASS = '@INOLTRATA_PROTOCOLLO@'";
        }

        if ($_POST[$this->nameForm . '_cbMsgAnomalieSc'] == 1) {
            $where[] = "PECTIPO<>'posta-certificata' AND PECTIPO<>'accettazione' AND PECTIPO<>'avvenuta-consegna' AND PECTIPO<>''";
        }

        if (count($where)) {
            $sql = " AND (" . implode(" OR ", $where) . ")";
        }

        if ($_POST[$this->nameForm . '_OggettoSrcSc']) {
            $sql .= " AND " . $this->PROT_DB->strLower('SUBJECT') . " LIKE '%" . strtolower($_POST[$this->nameForm . '_OggettoSrcSc']) . "%'";
        }
        if ($_POST[$this->nameForm . '_MittenteSrcSc']) {
            $sql .= " AND (" . $this->PROT_DB->strLower('FROMADDR') . " LIKE '%" . strtolower($_POST[$this->nameForm . '_MittenteSrcSc']) . "%' OR
                          " . $this->PROT_DB->strLower('METADATA') . " LIKE '%" . strtolower($_POST[$this->nameForm . '_MittenteSrcSc']) . "%')";
        }

        return $sql;
    }

    private function checkDaProtocollo() {
        $datiSegnatura = array();
        $proMessage = new proMessage();
        foreach ($this->elencoAllegatiOrig as $allegato) {
            if (strtolower($allegato['DATAFILE']) == 'segnatura.xml') {
                $anaent_26 = $this->proLib->GetAnaent('26');
                $fileXmlAppoggio = $this->leggiXml($allegato['FILE']);
                $fileXml = $fileXmlAppoggio['Segnatura'];
                $numProt = str_pad($fileXml['Intestazione']['Identificatore']['NumeroRegistrazione']['@textNode'], 6, "0", STR_PAD_LEFT);
                if ($fileXml['Intestazione']['Identificatore']['DataRegistrazione']['@textNode'] != '') {
                    $annoProt = date('Y', strtotime($fileXml['Intestazione']['Identificatore']['DataRegistrazione']['@textNode']));
                }
                $aoo = $fileXml['Intestazione']['Identificatore']['CodiceAOO']['@textNode'];
                $datiSegnatura = $proMessage->checkOggettoInterno($this->currMessage);
                if ($annoProt . $numProt != $datiSegnatura['PRONUM'] || $anaent_26['ENTDE2'] != $aoo) {
                    return false;
                }
                break;
            }
        }
        return $datiSegnatura;
    }

    private function checkNotificaRicezione() {

// Da sistemare con IDMAILPADRE

        if (!is_object($this->currMessage)) {
            return false;
        }
        $retDecode = $this->currMessage->getStruct();
        if (!isset($retDecode['Subject'])) {
            return false;
        }
        $oggetto = $retDecode['Subject'];
        if (strpos(strtoupper($oggetto), 'NOTIFICA RICEZIONE EMAIL.') === false) {
            return false;
        }
        $oggNot = substr($oggetto, strpos(strtoupper($oggetto), 'NOTIFICA RICEZIONE EMAIL.'));
        $valprot = substr($oggNot, strpos($oggNot, ':') + 1);
        $valori = explode("/", trim($valprot));
        $pronum = str_pad($valori[0], 6, "0", STR_PAD_LEFT);
        $anapro_rec = $this->proLib->GetAnapro($valori[1] . $pronum, 'codice', '', " (PROPAR='A' OR PROPAR='P')"); // DA CONTROLLARE!!!!
        if (strlen($anapro_rec['PRONUM']) < 5) {
            return false;
        }
        $anapro_rec['SUBJECT'] = $retDecode['Subject'];
        return $anapro_rec;
    }

    private function leggiXml($file) {
        include_once(ITA_LIB_PATH . '/QXml/QXml.class.php');
        $xmlObj = new QXML;
        $xmlObj->setXmlFromFile($file);
        $arrayXml = $xmlObj->getArray();
        return $arrayXml;
    }

    private function assegnaEmlToProtocollo($datiSegnatura) {
        $this->proLibMail->setCurrMessage($this->currMessage);
        $retAssegna = $this->proLibMail->assegnaEmlToProtocollo($datiSegnatura, $this->elemento, $this->elementoLocale, $this->dettagliFile);
        if ($this->proLibMail->getErrCode() == -2) {
            Out::msgInfo('Attenzione', $this->proLibMail->getErrMessage());
        }
        return $retAssegna;
//
//
//
//
//
//        $Anaent49_rec = $this->proLib->GetAnaent('49');
//
//        if ($datiSegnatura) {
//            if (is_object($this->currMessage)) {
//                if (!isset($this->elementoLocale)) {
//                    $nomefile = $this->elemento['PECTIPO'];
//                } else {
//                    $nomefile = $this->dettagliFile[$this->elementoLocale]['PECTIPO'];
//                }
//                if ($nomefile == '') {
//                    $nomefile = 'Email assegnata al protocollo';
//                }
//                $emailOriginale = $this->currMessage->getEmlFile();
//                $elementi = array();
//                $elementi['dati'] = $datiSegnatura;
//                $elementi['allegati'][] = array('DATAFILE' => $nomefile . '.eml', 'FILE' => $emailOriginale, 'DOCIDMAIL' => $this->elemento['IDMAIL']);
//                $risultato = true;
//                // Se è attivo alfresco, non serve risalvarsi la mail.
//                if (!$Anaent49_rec['ENTDE1']) {
//                    $model = 'proItalsoft.class';
//                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
//                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//                    $proItalsoft = new proItalsoft();
//                    $risultato = $proItalsoft->aggiungiAllegatiProtocollo($elementi);
//                }
//                if ($risultato) {
//                    if (!isset($this->elementoLocale)) {
//                        include_once ITA_BASE_PATH . '/apps/Mail/emlDbMailBox.class.php';
//                        $emlDbMailBox = new emlDbMailBox();
//                        $risultatoDb = $emlDbMailBox->updateClassForRowId($this->elemento['ROWID'], '@PROTOCOLLATO@');
//                        if ($risultatoDb === false) {
//                            return false;
//                        }
//                        $promail_rec = array(
//                            'PRONUM' => $datiSegnatura['PRONUM'],
//                            'PROPAR' => $datiSegnatura['PROPAR'],
//                            'IDMAIL' => $this->elemento['IDMAIL'],
//                            'SENDREC' => $this->elemento['SENDREC']
//                        );
//                        $insert_Info = 'Inserimento: ' . $promail_rec['PRONUM'] . ' ' . $promail_rec['IDMAIL'];
//                        $this->insertRecord($this->PROT_DB, 'PROMAIL', $promail_rec, $insert_Info);
//                    } else {
//                        $fileLocale = $elementi['allegati'][0]['FILE'];
//                        if (is_file($fileLocale)) {
//                            if (!@unlink($fileLocale)) {
//                                Out::msgStop("Nuovo Protocollo", "File:" . $fileLocale . " non Eliminato");
//                            }
//                        }
//                    }
//                    return true;
//                } else {
//                    return false;
//                }
//            }
//        }
//        return false;
    }

    private function getDatiProtocolloFromRowid($rowid) {
        if (!is_object($this->currMessage)) {
            return false;
        }
        $retDecode = $this->currMessage->getStruct();
        if (!isset($retDecode['Subject'])) {
            return false;
        }
        $anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
        $anapro_rec['Subject'] = $retDecode['Subject'];
        return $anapro_rec;
    }

    private function setCertificazione() {
        if ($this->certificato) {
            $certificazione = "<br><br><b>Tipo: </b>" . $this->certificato['ita_PEC_info']['tipo'] . "<br>";
            $certificazione .= "<b>Errore: </b>" . $this->certificato['ita_PEC_info']['errore'] . "<br>";
            $certificazione .= "<b>Mittente: </b>" . $this->certificato['ita_PEC_info']['mittente'] . "<br>";
            $certificazione .= "<b>Emittente: </b>" . $this->certificato['ita_PEC_info']['gestore-emittente'] . "<br>";
            $certificazione .= "<b>Oggetto: </b>" . $this->certificato['ita_PEC_info']['oggetto'] . "<br>";
            $certificazione .= "<b>Data e Ora: </b>" . $this->certificato['ita_PEC_info']['data'] . " - " . $this->certificato['ita_PEC_info']['ora'] . "<br>";
            Out::html($this->nameForm . '_divCertificazione', $certificazione);
            Out::tabEnable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneCertificazione");
        } else {
            Out::tabDisable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneCertificazione");
        }
    }

    private function assegnaProtocolli() {
        $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE CLASS ='@DA_PROTOCOLLARE@'";
        $mail_tab = $this->emlLib->getGenericTab($sql);
        $daAssegnare = array();
        $proMessage = new proMessage();
        foreach ($mail_tab as $mail_rec) {
            $trovatoNot = false;
            if (strpos(strtoupper($mail_rec['SUBJECT']), 'NOTIFICA RICEZIONE EMAIL.') != false) {
                $trovatoNot = true;
            }
            $trovatoProt = false;
            if ($mail_rec['PECTIPO'] == '' && substr($mail_rec['SUBJECT'], 0, 13) == 'PROTOCOLLO IN') {
                $trovatoProt = true;
            }
            if ($trovatoProt === false && (
                    ($mail_rec['PECTIPO'] == 'posta-certificata' && $trovatoNot === true) ||
                    ($mail_rec['PECTIPO'] == 'accettazione' || $mail_rec['PECTIPO'] == 'avvenuta-consegna' || $trovatoNot === true || $mail_rec['PECTIPO'] == '')
                    )) {
                $this->getStruttura($mail_rec['ROWID']);
                $checkOggetto = $proMessage->checkOggettoInterno($this->currMessage);
                if (!$checkOggetto) {
                    $checkOggetto = $this->checkNotificaRicezione();
                }
                if ($checkOggetto) {
                    $mail_rec['DATAMESSAGGIO'] = date("d/m/Y", strtotime($mail_rec['MSGDATE']));
                    if ($checkOggetto['PRONUM'] != '') {
                        $mail_rec['PROTOCOLLO'] = (int) substr($checkOggetto['PRONUM'], 4) . "/" . substr($checkOggetto['PRONUM'], 0, 4);
                        $mail_rec['ANAPRO'] = $checkOggetto;
                        $daAssegnare[] = $mail_rec;
                    }
                }
                //CONTROLLO VISIBILITA'
                //$where_profilo = proSoggetto::getSecureWhereFromIdUtente($this->proLib);
            }
        }
        $_POST['daAssegnare'] = $daAssegnare;
        $this->setModelData($_POST);
        $this->clearCurrMessage();
        include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
        proRic::proAssegnaProtocolli($daAssegnare, $this->nameForm, 'AssegnaTutti');
    }

    // Ora utilizzata quella di proLibMail.
    private function getStruttura($rowid) {
        $retDecode = $this->proLibMail->getStruttura($rowid);
        $this->currMessage = $this->proLibMail->getCurrMessage();
        $this->currObjSdi = $this->proLibMail->getCurrObjSdi();
        return $retDecode;
//
//        $this->clearCurrMessage();
//        $this->currMailBox = new emlMailBox();
//        $this->currMessage = $this->currMailBox->getMessageFromDb($rowid);
//        $this->currMessage->parseEmlFileDeep();
//        $retDecode = $this->currMessage->getStruct();
//        $ExtraParam = array();
//        $ExtraParam['PARSEALLEGATI'] = true;
//        $this->currObjSdi = proSdi::getInstance($retDecode, $ExtraParam);
//        return $retDecode;
    }

    private function clearCurrMessage($clearSdi = true) {
        if ($this->currMessage != null) {
            $this->currMessage->cleanData();
            $this->currMessage = null;
        }

        if ($this->currObjSdi != null) {
            if ($clearSdi) {
                $this->currObjSdi->cleanData();
            }
            $this->currObjSdi = null;
        }
    }

    //@TODO QUESTA PARTE E' STANDARD SIA PER praGest che per emlViewer
    private function caricaElencoAllegati($retDecode) {
        $allegati = array();
        $elementi = $retDecode['Attachments'];
        if ($elementi) {
            $incr = 1;
            foreach ($elementi as $elemento) {
                /*
                 * 24/03/2016 Possibile work qruon per errore su filename da risolvere alla fonte
                 */
                if ($elemento['FileName'] === '') {
                    $elemento['FileName'] = 'Allegato_' . md5(microtime());
                    usleep(10);
                }
                if ($elemento['FileName']) {
                    $vsign = "";
                    $icon = utiIcons::getExtensionIconClass($elemento['FileName'], 32);
                    $sizefile = $this->emlLib->formatFileSize(filesize($elemento['DataFile']));
                    $ext = pathinfo($elemento['FileName'], PATHINFO_EXTENSION);
                    if (strtolower($ext) == "p7m") {
                        $vsign = "<span class=\"ita-icon ita-icon-shield-blue-24x24\">Verifica Firma</span>";
                    }

                    $allegati[] = array(
                        'ROWID' => $incr,
                        'FileIcon' => "<span style = \"margin:2px;\" class=\"$icon\"></span>",
                        'DATAFILE' => $elemento['FileName'],
                        'FILE' => $elemento['DataFile'],
                        'FileSize' => $sizefile,
                        'VSIGN' => $vsign
                    );
                    $incr++;
                }
            }
        }
        return $allegati;
    }

    private function selezionaDaScartare($ret_array = false, $whereFiltri = '') {
        $whereClasse = ' ( ';
        foreach (proLibMail::$ElencoClassProtocollabili as $classe) {
            $whereClasse .= " CLASS = '$classe' OR ";
        }
        $whereClasse = substr($whereClasse, 0, -3) . ') ';

        $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE $whereClasse $whereFiltri ORDER BY MSGDATE ASC";
        $mail_tab = $this->emlLib->getGenericTab($sql);

        foreach ($mail_tab as $key => $mail_rec) {
            $mail_tab[$key]['DATAMESSAGGIO'] = date("d/m/Y", strtotime($mail_rec['MSGDATE']));
            $mail_tab[$key]['ORAMESSAGGIO'] = date("H:i:s", strtotime($mail_rec['MSGDATE']));
        }
//        $ordine = 'desc';
        foreach ($mail_tab as $k => $v) {
            $b[$k] = strtolower($v['DATAMESSAGGIO']);
        }
//        if ($ordine == 'asc') {
//            asort($b);
//        } else {
//            arsort($b);
//        }
        foreach ($b as $key => $val) {
            $chiave = "A" . $mail_tab[$key]['ROWID'] . "";
            $c[$chiave] = $mail_tab[$key];
        }
        $mail_tab = $c;
        if ($ret_array) {
            return $mail_tab;
        }

        $colNames = array(
            "Mittente",
            "Data",
            "Ora",
            "Oggetto",
            "Account"
        );
        $colModel = array(
            array("name" => 'FROMADDR', "width" => 245),
            array("name" => 'DATAMESSAGGIO', "width" => 75),
            array("name" => 'ORAMESSAGGIO', "width" => 75),
            array("name" => 'SUBJECT', "width" => 400),
            array("name" => 'ACCOUNT', "width" => 120)
        );
        include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
        proRic::proMultiselectGeneric(
                $mail_tab, $this->nameForm, 'DaScartare', 'Seleziona le Email da Scartare', $colNames, $colModel, '', array('width' => '900', 'height' => '400')
        );
    }

    private function CaricaInoltrato($MailArchivio_rec) {
        $Inoltrato = '';
        $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE IDMAILPADRE = '" . $MailArchivio_rec['IDMAIL'] . "' ";
        $mail_tab = $this->emlLib->getGenericTab($sql);
        foreach ($mail_tab as $mail_rec) {
            $BustaMailConsegna = $BustaMailAccettaz = '';
            $Inoltrato = '<div class="ita-html" style="display:inline-block;">';
            $iconabusta = 'ita-icon-chiusagray-24x24';
            if ($mail_rec['PECTIPO']) {
                $iconabusta = 'ita-icon-chiusagreen-24x24';
                //Controllo se il messaggio è stato inoltrato o accettato
                $retRic = $this->proLib->checkMailRic($mail_rec['IDMAIL']);
                if ($retRic['ACCETTAZIONE']) {
                    $BustaMailAccettaz = '<a href="#" id="' . $retRic['ACCETTAZIONE'] . '" class="ita-hyperlink {event:\'apriMailInoltrata\'}"><span title="Accettata" class="ui-icon ui-icon-check"></a>' . '';
                }
                if ($retRic['CONSEGNA']) {
                    $BustaMailConsegna = '<a href="#" id="' . $retRic['CONSEGNA'] . '" class="ita-hyperlink {event:\'apriMailInoltrata\'}"><span title="Consegnata" class="ui-icon ui-icon-check"></a>' . '';
                }
            }
            $DataInoltro = date('d/m/Y', strtotime(substr($mail_rec['MSGDATE'], 0, 8)));
            $OraInoltro = substr($mail_rec['MSGDATE'], 8);
            $icon_mail = "<span  class=\"ita-icon $iconabusta\" style = \"display:inline-block;\"></span>";
            $InoltratoA = 'Email inoltrata a: <span style="text-transform: lowercase">' . $mail_rec['TOADDR'] . '</span>' .
                    "<br>Il giorno <b>$DataInoltro</b> alle ore <b>$OraInoltro</b> ";
            $BustaMailInoltrata = $icon_mail . '<span title ="' . htmlspecialchars($InoltratoA) . '" style="display:inline-block; position:relative; margin-left:-12px; top:2px; " class="ita-tooltip ita-icon ita-icon-arrow-green-dx-16x16"></span>';
            $BustaMailInoltrata = '<a href="#" id="' . $mail_rec['IDMAIL'] . '" class="ita-hyperlink {event:\'apriMailInoltrata\'}">' . $BustaMailInoltrata . '</a>' . '';
            //Busta Inoltrata
            $Inoltrato .= '<span style="display:inline-block; vertical-align:middle;">' . $BustaMailInoltrata . ' </span>' .
                    '<span style="display:inline-block; vertical-align:middle;">' . $BustaMailAccettaz . '</span>' .
                    '<span style="display:inline-block; vertical-align:middle;">' . $BustaMailConsegna . '</span>';

            $Inoltrato .= '</div>';
        }
        return $Inoltrato;
    }

    private function setConfig() {
        $parametri = array(
            $this->nameForm . '_cbTutti' => $_POST[$this->nameForm . '_cbTutti'],
            $this->nameForm . '_cbMsgInt' => $_POST[$this->nameForm . '_cbMsgInt'],
            $this->nameForm . '_cbMsgPEC' => $_POST[$this->nameForm . '_cbMsgPEC'],
            $this->nameForm . '_cbAccettazione' => $_POST[$this->nameForm . '_cbAccettazione'],
            $this->nameForm . '_cbConsegna' => $_POST[$this->nameForm . '_cbConsegna'],
            $this->nameForm . '_cbMsgStd' => $_POST[$this->nameForm . '_cbMsgStd'],
            $this->nameForm . '_cbMsgAnomalie' => $_POST[$this->nameForm . '_cbMsgAnomalie'],
            $this->nameForm . '_Inoltrate' => $_POST[$this->nameForm . '_cbInoltrate'],
            //
            $this->nameForm . '_OggettoSrc' => $_POST[$this->nameForm . '_OggettoSrc'],
            $this->nameForm . '_MittenteSrc' => $_POST[$this->nameForm . '_MittenteSrc'],
            $this->nameForm . '_dallaData' => $_POST[$this->nameForm . '_dallaData'],
            $this->nameForm . '_allaData' => $_POST[$this->nameForm . '_allaData'],
            $this->nameForm . '_cbAccount' => $_POST[$this->nameForm . '_cbAccount']
        );
        $this->setCustomConfig("CAMPIDEFAULT/DATI", $parametri);
        $this->saveModelConfig();
    }

    private function caricaConfigurazioni() {
        $parametri = $this->getCustomConfig('CAMPIDEFAULT/DATI');
        foreach ($parametri as $key => $valore) {
            if ($valore == 1) {
                Out::valore($key, $valore);
                $_POST[$key] = $valore;
            }
            if ($valore) {
                Out::valore($key, $valore);
                $_POST[$key] = $valore;
            }
        }
    }

    private function VisualizzaXmlConStile($FileStyle, $FileXml) {
        $style = ITA_BASE_PATH . '/apps/Protocollo/resources/sdi/' . $FileStyle;
        $urlxsl = utiDownload::getUrl($FileStyle, $style, false, true, true);
        $ContStile = '<?xml-stylesheet type="text/xsl" href="' . htmlentities($urlxsl) . '"?>';

        $ArrFileXml = file($FileXml);

        $PathFile = itaLib::getAppsTempPath();
        $FileRandName = md5(rand() * time()) . ".xml";
        $randPathFileName = $PathFile . "/" . $FileRandName;

        // Preparo il documento
        $FileH = fopen($randPathFileName, 'w');
        if ($FileH === false) {
            Out::msgInfo("Attenzione", 'Errore nella apertura del File.');
            return false;
        }
        if (fwrite($FileH, '<?xml version="1.0" encoding="UTF-8"?>') === false) {
            Out::msgInfo("Attenzione", 'Errore nella scrittura file: preview_xmltask ');
            return false;
        }
        if (fwrite($FileH, $ContStile) === false) {
            Out::msgInfo("Attenzione", 'Errore nella scrittura file.');
            return false;
        }
        foreach ($ArrFileXml as $Riga) {
            if (strtolower(substr($Riga, 0, 5)) == '<?xml') {
                continue;
            }
            if (fwrite($FileH, $Riga) === false) {
                Out::msgInfo("Attenzione", 'Errore nella scrittura file.');
                return false;
            }
        }
        if (fclose($FileH) === false) {
            Out::msgInfo("Attenzione", 'Errore nella chiusura del file task.');
            return false;
        }
        //Apro il documento
        Out::openDocument(utiDownload::getUrl($FileRandName, $randPathFileName));
    }

    private function creaCombo() {
        Out::select($this->nameForm . '_cbAccount', 1, "", 0, "Tutti gli Account di posta");
        foreach ($this->refAccounts as $account) {
            Out::select($this->nameForm . '_cbAccount', 1, $account['EMAIL'], 0, $account['EMAIL']);
        }

        if ($_POST[$this->nameForm . '_cbAccount'] && $_POST['daProtocollo'] == true) {
            Out::valore($this->nameForm . '_cbAccount', $_POST[$this->nameForm . '_cbAccount']);
        }
    }

    private function VisualizzaMessaggioDT($currObjSdi) {
        // Controllo se è attivo notifica semplificato
        Out::hide($this->nameForm . '_AssegnaProtocolloSDI');
        $Anaent40_rec = $this->proLib->GetAnaent('40');
        if (!$Anaent40_rec['ENTDE4']) {
            return;
        }
        if ($currObjSdi->getTipoMessaggio() != proSdi::TIPOMESS_DT) {
            return;
        }
        Out::hide($this->nameForm . '_Protocolla');
        $RetCtrDT = $this->GetFatturaCollegataDT($currObjSdi);
        if ($RetCtrDT['ANAPRO']) {
            Out::show($this->nameForm . '_AssegnaProtocolloSDI');
        } else {
            Out::msgStop("Attenzione", $RetCtrDT['MESSAGGIO']);
        }
    }

    private function GetFatturaCollegataDT($currObjSdi) {
        $retMessDT = array();
        $AnaproCollegato = $this->proLibSdi->GetAnaproDaCollegareFromEstratto($currObjSdi->getEstrattoMessaggio(), 'A');
        if (!$AnaproCollegato) {
            $retMessDT['STATO'] = 1;
            $retMessDT['MESSAGGIO'] = "Impossibile trovare la fattura collegata alla Decorrenza Termini.<br>Probabilmente la fattura non è ancora stata caricata, controllare.";
            $retMessDT['ANAPRO'] = array();
            return $retMessDT;
        }
        $retMessDT['ANAPRO'] = $AnaproCollegato;
        return $retMessDT;
    }

    /**
     * Portarla in una libreria e gestire return messaggi?
     * @param type $currObjSdi
     * @param type $mail_rec 
     * @return boolean
     */
    private function AssegnaProtocolloSDI($currObjSdi, $AnaproCollegato, $mail_rec) {
        $RetAssegnaProt = array();
        $FileMessaggioSdi = $currObjSdi->getFilePathMessaggio();
        $NomeFileMessaggioSdi = $currObjSdi->getNomeFileMessaggio();
        $elementi = array();
        $elementi['dati'] = $AnaproCollegato;
        $elementi['allegati'][] = array('DATAFILE' => $NomeFileMessaggioSdi, 'FILE' => $FileMessaggioSdi);

        $randName = md5(rand() * time()) . "." . pathinfo($NomeFileMessaggioSdi, PATHINFO_EXTENSION);
        $NomeFile = $FileInfo = $NomeFileMessaggioSdi;
        $AllegatoDiServizio[] = Array(
            'ROWID' => 0,
            'FILEPATH' => $FileMessaggioSdi,
            'FILENAME' => $randName,
            'FILEINFO' => $FileInfo,
            'DOCTIPO' => 'ALLEGATO',
            'DAMAIL' => '',
            'DOCNAME' => $NomeFile,
            'DOCIDMAIL' => '',
            'DOCFDT' => date('Ymd'),
            'DOCRELEASE' => '1',
            'DOCSERVIZIO' => 1,
        );
        /* 1. Controllo Allegati */
        $AllegatoDiServizio = $this->proLibAllegati->ControlloAllegatiProtocollo($AllegatoDiServizio, $currObjSdi);
        /* 2. Salvataggio metadati - Solo se è in Aggiunta */
        /*
         * Spostato prima...
         *  Qui salvataggio metadati
         */
        if (!$this->proLibTabDag->InserisciTabDagSdi($AnaproCollegato, $currObjSdi)) {
            $RetAssegnaProt['STACKASSEGNA']['TABDAG'] = array('ROWIDAGGIUNTI' => $this->proLibTabDag->getRisultatoRitornoRowidAggiunti());
            $RetAssegnaProt['STATO'] = 'ERRORE';
            $RetAssegnaProt['MESSAGGIO'] = "Errore salvataggio metadati. " . $this->proLibTabDag->getErrMessage();
            // Non è possibile, cancellerebbe anche quelli MT.. Occorre migliorare la funzione per un DSET?
            //$this->proLibTabDag->CancellaTabDagSdi($AnaproCollegato, 'MESSAGGIO_SDI');
            return $RetAssegnaProt;
        }
        /* 3. Preparazione Obj Protocollo */
        $numero = substr($AnaproCollegato['PRONUM'], 4);
        $anno = substr($AnaproCollegato['PRONUM'], 0, 4);
        $tipo = $AnaproCollegato['PROPAR'];
        $objProtocollo = proProtocollo::getInstance($this->proLib, $numero, $anno, $tipo, '');
        /* 4. Passo Oggetto Protocollo a Gestione Allegati */
        /*
         * Salvataggio File
         */
        $risultato = $this->proLibAllegati->GestioneAllegati($this, $AnaproCollegato['PRONUM'], $AnaproCollegato['PROPAR'], $AllegatoDiServizio, $AnaproCollegato['PROCON'], $AnaproCollegato['PRONOM'], $objProtocollo);
        $RisultatoRitorno = $this->proLibAllegati->getRisultatoRitorno();
        $RetAssegnaProt['STACKASSEGNA']['ANADOC'] = array('ROWIDAGGIUNTI' => $RisultatoRitorno['ROWIDAGGIUNTI']);
        if (!$risultato) {
            $RetAssegnaProt['STATO'] = 'ERRORE';
            $RetAssegnaProt['MESSAGGIO'] = 'Errore in aggiunta allegato al Protocollo.';
            return $RetAssegnaProt;
        }

        $audit_Info = ' ANADOC SALVATO CORRETTAMENTE. ROWID MAIL: ' . $mail_rec['ROWID'];
        $this->insertAudit($this->PROT_DB, 'PROMAIL', $audit_Info);

        $RetAssegnaProt['STACKASSEGNA']['TABDAG'] = array('ROWIDAGGIUNTI' => $this->proLibTabDag->getRisultatoRitornoRowidAggiunti());
        $audit_Info = ' TABDAG SALVATI CORRETTAMENTE. ROWID MAIL: ' . $mail_rec['ROWID'];
        $this->insertAudit($this->PROT_DB, 'PROMAIL', $audit_Info);
        /*
         * Qui segno la mail come protocollata
         * E aggiornamento PROMAIL
         * Operazione già loggata da updateClassForRowId
         */
        include_once ITA_BASE_PATH . '/apps/Mail/emlDbMailBox.class.php';
        $emlDbMailBox = new emlDbMailBox();
        $risultatoDb = $emlDbMailBox->updateClassForRowId($mail_rec['ROWID'], '@PROTOCOLLATO@');
        $RetAssegnaProt['STACKASSEGNA']['MAIL_ARCHIVIO']['ROWIDAGGIORNATI'][] = $mail_rec['ROWID'];
        if ($risultatoDb === false) {
            $RetAssegnaProt['STATO'] = 'ERRORE';
            $RetAssegnaProt['MESSAGGIO'] = $emlDbMailBox->getLastMessage();
            return $RetAssegnaProt;
        }
        $promail_rec = array(
            'PRONUM' => $AnaproCollegato['PRONUM'],
            'PROPAR' => $AnaproCollegato['PROPAR'],
            'IDMAIL' => $mail_rec['IDMAIL'],
            'SENDREC' => $mail_rec['SENDREC']
        );
        /*
         * Inserimento con Audit su promail 
         */
        $insert_Info = 'Inserimento PROMAIL SDI : ' . $promail_rec['PRONUM'] . ' ' . $promail_rec['IDMAIL'] . ' - ROWID MAIL: ' . $promail_rec['ROWID'];
        if (!$this->insertRecord($this->PROT_DB, 'PROMAIL', $promail_rec, $insert_Info)) {
            $RetAssegnaProt['STATO'] = 'ERRORE';
            $RetAssegnaProt['MESSAGGIO'] = "Errore in inserimento su PROMAIL.";
            return $RetAssegnaProt;
        }

        $RetAssegnaProt['STATO'] = 'ASSEGNATO';

        $audit_Info = ' MAIL ASSEGNATA CORRETTAMENTE. ROWID MAIL: ' . $mail_rec['ROWID'];
        $this->insertAudit($this->PROT_DB, 'PROMAIL', $audit_Info);
        // Controllo e assegnazione decorrenza: e attivo spacchetta fatture.
        return $RetAssegnaProt;
    }

    private function RipristinaMailDaAssegnareSDI($stackAssegna) {
        $RetStatoRipristina = array();
        /*
         * 1 Ripristino la mail
         */
        if ($stackAssegna['MAIL_ARCHIVIO']) {
            include_once ITA_BASE_PATH . '/apps/Mail/emlDbMailBox.class.php';
            $emlDbMailBox = new emlDbMailBox();
            $rowidAggiornati = $stackAssegna['MAIL_ARCHIVIO']['ROWIDAGGIORNATI'];
            foreach ($rowidAggiornati as $rowidAggiornato) {
                $risultatoDb = $emlDbMailBox->updateClassForRowId($rowidAggiornato, '@DA_PROTOCOLLARE@');
                if ($risultatoDb === false) {
                    $RetStatoRipristina['STATO'] = 'ERRORE';
                    $RetStatoRipristina['MESSAGGIO'] = "Errore in ripristino classe Mail. ";
                    return $RetStatoRipristina;
                }
            }
        }
        /*
         * 2 Ripristino i metadati
         */
        if ($stackAssegna['TABDAG']) {
            $rowidAggiunti = $stackAssegna['TABDAG']['ROWIDAGGIUNTI'];
            foreach ($rowidAggiunti as $rowidAggiunto) {
                $deleteInfo = 'Ripristino tabdag. Cancellazione TABDAG ' . $rowidAggiunto;
                if (!$this->deleteRecord($this->PROT_DB, 'TABDAG', $rowidAggiunto, $deleteInfo)) {
                    $RetStatoRipristina['STATO'] = 'ERRORE';
                    $RetStatoRipristina['MESSAGGIO'] = "Errore in Cancellazione TABDAG.";
                    return $RetStatoRipristina;
                }
            }
        }

        /*
         * 3 Cancello il documento (prima documento poi record su ANADOC)
         */
        if ($stackAssegna['ANADOC']) {
            $rowidAggiunti = $stackAssegna['ANADOC']['ROWIDAGGIUNTI'];
            foreach ($rowidAggiunti as $rowidAggiunto) {
                $Anadoc_rec = $this->proLib->GetAnadoc($rowidAggiunto, 'rowid');
                if ($Anadoc_rec) {
                    // cancello il file
//                    $destinazione = $this->proLib->SetDirectory($Anadoc_rec['DOCNUM'], substr($Anadoc_rec['DOCPAR'], 0, 1));
//                    $FileDaCancellare = $destinazione . "/" . $Anadoc_rec['DOCFIL'];
//                    if (!@unlink($FileDaCancellare)) {
//                        $RetStatoRipristina['STATO'] = 'ERRORE';
//                        $RetStatoRipristina['MESSAGGIO'] = "Errore in Cancellazione File.";
//                        return $RetStatoRipristina;
//                    }
                    /* Nuova cancellazione centralizzata.. */
                    if (!$this->proLibAllegati->CancellaDocAllegato($Anadoc_rec['ROWID'])) {
                        $RetStatoRipristina['STATO'] = 'ERRORE';
                        $RetStatoRipristina['MESSAGGIO'] = "Errore in Cancellazione File." . $this->proLibAllegati->getErrMessage();
                        return $RetStatoRipristina;
                    }



                    // delete record
                    $deleteInfo = 'Ripristino tabdag. Errore in Cancellazione ANADOC: ' . $Anadoc_rec['DOCNUM'] . $Anadoc_rec['DOCPAR'];
                    if (!$this->deleteRecord($this->PROT_DB, 'ANADOC', $Anadoc_rec['ROWID'], $deleteInfo)) {
                        $RetStatoRipristina['STATO'] = 'ERRORE';
                        $RetStatoRipristina['MESSAGGIO'] = "Errore in Cancellazione ANADOC .";
                        return $RetStatoRipristina;
                    }
                }
            }
        }

        $RetStatoRipristina['STATO'] = 'RIPRISTINATO';
        return $RetStatoRipristina;
    }

    private function ControllaSeMailSdi($mail_rec) {
        $metadata = unserialize($mail_rec["METADATA"]);
        if (in_array($mail_rec['FROMADDR'], $this->elencoMailSdi) && $mail_rec['PECTIPO'] == 'posta-certificata') {
            return true;
        } else {
            if ($metadata['emlStruct']['ita_PEC_info'] != "N/A") {
                if ($metadata['emlStruct']['ita_PEC_info']['dati_certificazione']) {
                    if (in_array($metadata['emlStruct']['ita_PEC_info']['dati_certificazione']['mittente'], $this->elencoMailSdi) &&
                            $mail_rec['PECTIPO'] == 'posta-certificata') {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function AssegnaNotificheProtocolloSDI() {
        $this->setElencoMailSdi();
        $retStato = array();
        $SegnalazioniErrori = array();
        $MailAssegnate = array();
        $wherefiltri = $this->getWhereFiltri();

        $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE CLASS ='@DA_PROTOCOLLARE@' " . $wherefiltri; // @TODO DA RIMUOVERE??! (Where)

        $mail_tab = $this->emlLib->getGenericTab($sql);
        foreach ($mail_tab as $mail_rec) {
            /*
             * 1 Se non proviene dallo SDI continuo.
             */
            if (!$this->ControllaSeMailSdi($mail_rec)) {
                continue;
            }
            /*
             * Preparo ciò che serve per esaminare la mail
             */
            $rowid = $mail_rec['ROWID'];
            $this->clearCurrMessage();
            $currMailBox = new emlMailBox();
            $currMessage = $currMailBox->getMessageFromDb($rowid);
            $currMessage->parseEmlFileDeep();
            $retDecode = $currMessage->getStruct();
            if (!$this->CheckAnomalieAllegati($retDecode)) {
                $SegnalazioniErrori[$rowid] = 'Uno o più file allegati non risultano leggibili. IDMAIL: ' . $rowid;
                continue;
            }
            $ExtraParam = array('ELABORASOLOMESSAGGIO' => true);
            $currObjSdi = proSdi::getInstance($retDecode, $ExtraParam);
            /*
             *  Errore grave, analisi fattura elettronica deve fermarsi.
             */
            if (!$currObjSdi) {
                $retStato['STATO'] = 'ERRORE';
                $retStato['ERRORI'] = $SegnalazioniErrori;
                $retStato['MESSAGGIO'] = "Errore in analisi fatturazione elettronica.";
                $retStato['ROWID_ERRORE'] = $rowid;
                return $retStato;
            }
            /*
             *  Errore grave durante analisi fattura elettronica.
             */
            if ($currObjSdi->getErrCode() != 0) {
                $retStato['STATO'] = 'ERRORE';
                $retStato['ERRORI'] = $SegnalazioniErrori;
                $retStato['MESSAGGIO'] = $currObjSdi->getErrMessage();
                $retStato['ROWID_ERRORE'] = $rowid;
                return $retStato;
            }
            /*
             * Se non è un messaggio SDI continua
             */
            if (!$currObjSdi->isMessaggioSdi()) {
                continue;
            }
            /*
             * Se non è un messaggio sdi di tipo DT continua
             */
            if ($currObjSdi->getTipoMessaggio() != proSdi::TIPOMESS_DT) {
                continue;
            }
            /*
             * Controllo fattura collegata:
             * Non trova collegamento. Errore grave.
             */
            $RetCtrDT = $this->GetFatturaCollegataDT($currObjSdi);
            if (!$RetCtrDT['ANAPRO']) {
                $SegnalazioniErrori[$rowid] = $RetCtrDT['MESSAGGIO'];
                continue;
            }
            /*
             * Assegno il DT al protocollo 
             */
            $RetAssegnaProt = $this->AssegnaProtocolloSDI($currObjSdi, $RetCtrDT['ANAPRO'], $mail_rec, true);
            if ($RetAssegnaProt['STATO'] != 'ASSEGNATO') {
                /*
                 *  Provo a Ripristinare
                 *  Se da errore anche il Rpristina DEVE fermarsi.
                 */
                $RetRipristino = $this->RipristinaMailDaAssegnareSDI($RetAssegnaProt['STACKASSEGNA']);
                if ($RetRipristino['STATO'] != 'RIPRISTINATO') {
                    $retStato['STATO'] = 'ERRORE';
                    $retStato['ERRORI'] = $SegnalazioniErrori;
                    $Messaggio = 'Errore riscontrato in elaborazione: ' . $RetAssegnaProt['MESSAGGIO'] . '<br>';
                    $Messaggio .= 'Errore nel tentativo di ripristino: ';
                    $Messaggio .= $RetRipristino['MESSAGGIO'] . "<br>";
                    $retStato['MESSAGGIO'] = $Messaggio;
                    $retStato['ROWID_ERRORE'] = $rowid;
                    return $retStato;
                }
                $RetAssegnaProt['MESSAGGIO'] .= ' La mail è stata ripristinata.';
                /*
                 * Altrimenti se ripristina correttamente:
                 * Segna tra gli errori e continua.
                 */
                $SegnalazioniErrori[$rowid] = $RetAssegnaProt['MESSAGGIO'];
                continue;
            }
            /*
             *  Qui indico che è stato assegnato n.
             */
            $MailAssegnate[] = $rowid;
            $retStato['ASSEGNATI'] = count($MailAssegnate);
            /*
             * Qui provo a fare un export DT
             */
            $retExport = $this->ExportNotificaDTSDI($RetCtrDT['ANAPRO']);
            if ($retExport['STATO'] == 'ERRORE') {
                $SegnalazioniErrori[$rowid] = $retExport['ESPORTAZIONE'];
                $retStato['ESPORTAZIONE'] = 'ESPORTAZIONE FILE DT CON ERRORI.';
            } else {
                $retStato['ESPORTAZIONE'] = $retExport['ESPORTAZIONE'];
            }
            //DA RIMUOVERE!
            // $this->RipristinaMailDaAssegnareSDI($RetAssegnaProt['STACKASSEGNA']);
        }
        $retStato['STATO'] = 'ASSEGNATI';
        $retStato['ASSEGNATI'] = count($MailAssegnate);
        $retStato['ERRORI'] = $SegnalazioniErrori;
        return $retStato;
    }

    public function ExportNotificaDTSDI($Anapro_rec) {

        $retStato = array();
        $ExportParam = array();

        $anaent_45 = $this->proLib->GetAnaent('45');
        // Controllo elabroazione DT abilitata
        if (!$anaent_45['ENTDE2']) {
            $retStato['STATO'] = 'OK';
            $retStato['ESPORTAZIONE'] = 'ESPORTAZIONE NON ABILITATA PER DT';
            return $retStato;
        }
        $ExportParam[proLibSdi::PEXP_FATTURA] = false;
        $ExportParam[proLibSdi::PEXP_MT] = false;
        $ExportParam[proLibSdi::PEXP_DT] = true;

        $retStatus = $this->proLibSdi->AllegatiSDI2Repository($Anapro_rec, $ExportParam);
        if ($retStatus['ESPORTAZIONE']) {
            $retStato['STATO'] = 'OK';
            $retStato['ESPORTAZIONE'] = 'ESPORTAZIONE FILE ESEGUITA CORRETTAMENTE';
        } else {
            $retStato['STATO'] = 'ERRORE';
            $retStato['ESPORTAZIONE'] = $retStatus['MESSAGGIO'];
        }
        return $retStato;

//        App::log('Ctr Esportazione Abilitata repostiory');
//        // Controllo se attivo parametro di esportazione.
//        $dest_param = trim($anaent_39['ENTVAL']);
//        if (!trim($anaent_39['ENTVAL'])) {
//            $retStato['STATO'] = 'OK';
//            $retStato['ESPORTAZIONE'] = 'ESPORTAZIONE FILE NON ABILITATA';
//            return $retStato;
//        }
//        App::log('Ctr File Info');
//        // Controllo se servono i file info.
//        if ($anaent_40['ENTDE2']) {
//            $ExportParam[proLibSdi::PEXP_FILE_INFO] = true;
//        }
//        $ExportParam[proLibSdi::PEXP_DIR] = $dest_param;
//        $ExportParam[proLibSdi::PEXP_DT] = true;
//        App::log('Setto parametri e avvio ');
//        if (!$this->proLibSdi->ExportArrivoSDI($Anapro_rec, $ExportParam)) {
//            $retStato['STATO'] = 'ERRORE';
//            $retStato['ESPORTAZIONE'] = $this->proLibSdi->getErrMessage();
//            return $retStato;
//        }
//        App::log('terminato ritorno ');
//        $retStato['STATO'] = 'OK';
//        $retStato['ESPORTAZIONE'] = 'ESPORTAZIONE FILE ESEGUITA CORRETTAMENTE';
//        return $retStato;
    }

    public function CollegaMailPadre($idMail, $rowidPadre) {
        include_once ITA_BASE_PATH . '/apps/Mail/emlDbMailBox.class.php';
        $emlDbMailBox = new emlDbMailBox();
        $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE IDMAIL ='" . $idMail . "'";
        $NewMailArchivio_rec = ItaDb::DBSQLSelect($this->ITALWEB, $sql, false);
        if (!$NewMailArchivio_rec) {
            Out::msgStop('Rispondi Mail - Archivio', 'Errore nella rielettura della mail in archivio.');
            return false;
        }
        // Aggiorno idmail padre.
        $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE ROWID ='" . $rowidPadre . "'";
        $mailPadreArchivio_rec = ItaDb::DBSQLSelect($this->ITALWEB, $sql, false);
        if (!$mailPadreArchivio_rec) {
            Out::msgStop('Rispondi Mail - Archivio', 'Errore nella rielettura della mail padre in archivio.');
            return false;
        }
        $risultatoParent = $emlDbMailBox->updatelParentForRowId($NewMailArchivio_rec['ROWID'], $mailPadreArchivio_rec['IDMAIL']);
        if ($risultatoParent === false) {
            Out::msgStop('Rispondi Mail - Archivio', $emlDbMailBox->getLastMessage());
            return false;
        }
        return true;
    }

    public function CheckAnomalieAllegati($retDecode, $avvisa = false) {
        $elementi = $retDecode['Attachments'];
        $fl_anomalie = false;
        foreach ($elementi as $elemento) {
            if ($elemento['FileName'] === '') {
                $fl_anomalie = true;
                break;
            }
        }
        if ($fl_anomalie) {
            if ($avvisa) {
                Out::msgInfo("Attenzione", "Uno o più file allegati non risultano leggibili.");
            }
            return false;
        }
        return true;
    }

    public function NormalizzaCaratteri($stringa) {
        $stringa_tmp = "";
        for ($i = 0; $i < strlen($stringa['OGGETTO']); $i++) {
            $carattere = substr($stringa['OGGETTO'], $i, 1);
            if (ord($carattere) > 31) {
                $stringa_tmp = $stringa_tmp . $carattere;
            }
        }

        return $stringa_tmp;
    }

    public function CaricaDatiFormIncompleti() {
        /*
         * Innesta Form:
         */
        $codiceDest = proSoggetto::getCodiceSoggettoFromIdUtente();
        $uffdes_tab = $this->proLib->GetUffdes($codiceDest, 'uffkey', true, ' ORDER BY UFFFI1__3 DESC', true);
        Out::select($this->nameForm . '_selectUffici', 1, "@", "1", '<p style="color:orange;">Inseriti</p>');
        Out::select($this->nameForm . '_selectUffici', 1, "*", "0", '<p style="color:green;">I tuoi uffici.</p>');
        foreach ($uffdes_tab as $uffdes_rec) {
            $anauff_rec = $this->proLib->GetAnauff($uffdes_rec['UFFCOD']);
            Out::select($this->nameForm . '_selectUffici', 1, $uffdes_rec['UFFCOD'], '0', substr($anauff_rec['UFFDES'], 0, 30));
        }
        // Combo giorni: non servono, occorre visualizzare tutti i protocolli incompleti.
        Out::select($this->nameForm . '_LimiteVis', 1, "N", "1", 'Nessun Limite');
        Out::select($this->nameForm . '_LimiteVis', 1, "30", "0", 'Ultimi 30 giorni');
        Out::select($this->nameForm . '_LimiteVis', 1, "60", "0", 'Ultimi 60 giorni');
        Out::select($this->nameForm . '_LimiteVis', 1, "90", "0", 'Ultimi 90 giorni');
        Out::select($this->nameForm . '_LimiteVis', 1, "120", "0", 'Ultimi 120 giorni');
        Out::select($this->nameForm . '_LimiteVis', 1, "150", "0", 'Ultimi 150 giorni');
        Out::select($this->nameForm . '_LimiteVis', 1, "DATE", "0", 'Da Data Specifica');
        Out::hide($this->nameForm . '_DaData_field');
        Out::valore($this->nameForm . '_vediTutti', 1);

        $ElementiRicerca = array();
        $ElementiRicerca['Incompleti'] = true;
        $ElementiRicerca['VediTutti'] = true;

        $model = 'proListViewer';
        //$proListViewer = $this->proLib->innestaForm($model, $this->nameForm . '_divProtIncompleti');
        $proListViewer = itaFormHelper::innerForm($model, $this->nameForm . '_divProtIncompleti');

        $proListViewer->setEvent('openform');
        $proListViewer->setElementiRicerca($ElementiRicerca);
        $proListViewer->setCodiceDest($codiceDest);
        $proListViewer->parseEvent();
        $this->nameFormListViewer = $proListViewer->nameForm;
        // Visualizzo il conteggio.
        $tot = $proListViewer->getConteggioProtocolli();
        Out::tabSetTitle($this->nameForm . "_tabMail", $this->nameForm . "_paneProtIncompleti", 'Protocolli Incompleti <span style="color:red; font-weight:bold;">(' . $tot . ') </span>');

        // Nascondo Stampa:
        Out::hide($this->nameFormListViewer . '_gridProtocolli_printTableToHTML');
        Out::hide($this->nameFormListViewer . '_gridProtocolli_addGridRow');
    }

    public function RicaricaIncompleti() {
        $nameForm = $this->nameFormListViewer;
        $proListViewer = itaModel::getInstance('proListViewer', $nameForm);

        $ElementiRicerca = array();
        $ElementiRicerca['LimiteVis'] = $_POST[$this->nameForm . '_LimiteVis'];
        $ElementiRicerca['DaData'] = $_POST[$this->nameForm . '_DaData'];
        $ElementiRicerca['selectUffici'] = $_POST[$this->nameForm . '_selectUffici'];
        $ElementiRicerca['Incompleti'] = true;
        $ElementiRicerca['MailAbilitate'] = $this->refAccounts;
        $ElementiRicerca['VediTutti'] = $_POST[$this->nameForm . '_vediTutti'];


        $proListViewer->setElementiRicerca($ElementiRicerca);
        $proListViewer->RicaricaProtocolli('1');

        // Visualizzo il conteggio.
        $tot = $proListViewer->getConteggioProtocolli();
        Out::tabSetTitle($this->nameForm . "_tabMail", $this->nameForm . "_paneProtIncompleti", 'Protocolli Incompleti <span style="color:red; font-weight:bold;">(' . $tot . ') </span>');
        return;
    }

    public function CaricaAllegatiDaSegnatura(&$ElencoAllegati, $ElementoMail) {
        if ($ElementoMail['INTEROPERABILE']) {
            /*
             * Cerco file segnatura:
             */
            $SegnaturaFile = '';
            foreach ($ElencoAllegati as $allegato) {
                if (strtolower($allegato['DATAFILE']) == 'segnatura.xml') {
                    $SegnaturaFile = $allegato['FILE'];
                    break;
                }
            }
            /*
             * Controllo segnatura trovata
             */
            if ($SegnaturaFile) {
                $subPath = "tmp-work-segn" . md5(microtime());
                $tempPath = itaLib::createAppsTempPath($subPath);

                $FileDest = $tempPath . '/segnatura.xml';
                if (!@copy($allegato['FILE'], $FileDest)) {
                    Out::msgStop("Analisi Segnatura", "Copia della file segnatura fallita. " . $FileDest);
                    return false;
                }
                $ObjInterop = proInteropMsg::getInteropInstanceEntrata($FileDest, $allegato['DATAFILE']);
                $DatiSegnatura = $ObjInterop->getDatiSegnatura();
                $this->datiSegnatura = $DatiSegnatura;
                $incr = count($ElencoAllegati);
                $ElencoAnomalie = array();
                if ($DatiSegnatura['PRINCIPALE_TELEMATICO']) {
                    $AllegatoTelematico = $DatiSegnatura['PRINCIPALE_TELEMATICO'];
                    if (!$this->AddAllegatoOriginale($ElencoAllegati, $AllegatoTelematico, $tempPath, $incr, $ElencoAnomalie)) {
                        return false;
                    }
                }
                if ($DatiSegnatura['ALLEGATI_TELEMATICI']) {
                    foreach ($DatiSegnatura['ALLEGATI_TELEMATICI'] as $AllegatoTelematico) {
                        if (!$this->AddAllegatoOriginale($ElencoAllegati, $AllegatoTelematico, $tempPath, $incr, $ElencoAnomalie)) {
                            return false;
                        }
                    }
                }
                if ($ElencoAnomalie) {
                    $Anomalie = implode("<br>", $ElencoAnomalie);
                    Out::msgInfo("Attenzione", $Anomalie);
                }
            }
        }
    }

    public function AddAllegatoOriginale(&$ElencoAllegati, $AllegatoTelematico, $tempPath, &$incr, &$ElencoAnomalie) {
        $Collocazione = $AllegatoTelematico['COLLOCAZIONETELEMATICA'];
        $NomeFile = $AllegatoTelematico['NOME'];
        $randName = md5(rand() * time()) . "." . pathinfo($NomeFile, PATHINFO_EXTENSION);
        $FileDest = $tempPath . '/' . $randName;
        // 
        if (!file_put_contents($FileDest, fopen($Collocazione, 'r'))) {
            Out::msgStop("Attenzione", "Errore in salvataggio allegato telematico: " . $NomeFile);
            return false;
        }
        /*
         * Controllo Impronta
         */
        $CtrAlgoritmo = 'sha256';
        if ($AllegatoTelematico['ALGORITMO']) {
            switch ($AllegatoTelematico['ALGORITMO']) {
                case 'MD5':
                    $CtrAlgoritmo = 'md5';
                    break;

                case 'SHA-256':
                default:
                    $CtrAlgoritmo = 'sha256';
                    break;
            }
        }
        $ImprontaFile = hash_file('sha256', $FileDest);
        if ($ImprontaFile != $AllegatoTelematico['IMPRONTA']) {
            $ElencoAnomalie[] = "Impronta file non corrispondente: $NomeFile. <br> Sorgente: " . $AllegatoTelematico['IMPRONTA'] . ' <br> Calcolata: ' . $ImprontaFile;
        }

        $vsign = "";
        $icon = utiIcons::getExtensionIconClass($NomeFile, 32);
        $sizefile = $this->emlLib->formatFileSize(filesize($FileDest));
        $ext = pathinfo($NomeFile, PATHINFO_EXTENSION);
        if (strtolower($ext) == "p7m") {
            $vsign = "<span class=\"ita-icon ita-icon-shield-blue-24x24\">Verifica Firma</span>";
        }
        $incr++;
        // Info che deriva da telematico
        $FileIcon = '<div class="ita-html " ><span style ="margin:2px; display:inline-block;" class="ita-tooltip ' . $icon . '" title="Allegato Telematico" > </span>';
        $FileIcon.= "<span  class=\"ita-icon ita-icon-publish-16x16\" style = \"margin-left:-22px; display:inline-block;\"></span></div>";
        $ElencoAllegati[] = array(
            'ROWID' => $incr,
            'FileIcon' => $FileIcon,
            'DATAFILE' => $NomeFile,
            'FILE' => $FileDest,
            'FileSize' => $sizefile,
            'VSIGN' => $vsign,
            'TELEMATICO' => 1,
            'LINK_TELEMATICO' => $Collocazione
        );
        return true;
    }

    public function ElaboraGridAllegatiTelematici($griglia, $elencoAllegati) {
        foreach ($elencoAllegati as $allegato) {
            if ($allegato['TELEMATICO']) {
                TableView::setRowData($griglia, $allegato['ROWID'], '', array('background' => '#ccccff'));
            }
        }
    }

}

?>
