<?php

/**
 *
 * GESTIONE EMAIL PRATICHE
 *
 * PHP Version 5
 *
 * @category
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    08.06.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praMessage.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEmailDate.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiIcons.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlRic.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlDbMailBox.class.php';
include_once (ITA_LIB_PATH . '/itaPHPMail/itaMime.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCore/itaMailer.class.php');

function praGestMail() {
    $praGestMail = new praGestMail();
    $praGestMail->parseEvent();
    return;
}

class praGestMail extends itaModel {

    public $PRAM_DB;
    public $ITALWEB;
    public $nameForm = "praGestMail";
    public $divRis = "praGestMail_divRisultato";
    public $divAlert = "praGestMail_divAlert";
    public $gridAllegati = "praGestMail_gridAllegati";
    public $gridAllegatiOrig = "praGestMail_gridAllegatiOrig";
    public $gridElencoMail = "praGestMail_gridElencoMail";
    public $gridElencoMailScarti = "praGestMail_gridElencoMailScarti";
    public $gridElencoMailLocale = "praGestMail_gridElencoMailLocale";
    public $praLib;
    public $proLib;
    public $emlLib;
    public $accLib;
    public $envLib;
    public $certificato;
    public $elencoAllegati;
    public $elencoAllegatiOrig;
    public $elemento;
    public $refAccounts;
    public $Proric_rec;
    public $currMailBox;
    public $currMessage;
    public $emailTempPath;
    public $dettagliFile;
    public $elementoLocale;
    public $currAlert;
    public $visibilita;
    public $returnModel;
    public $returnEvent;
    public $datiAppoggio;
    public $elencoMail;
    public $numProt;
    public $tabAttiva;
    public $profilo;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->emlLib = new emlLib();
            $this->proLib = new proLib();
            $this->accLib = new accLib();
            $this->envLib = new envLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->ITALWEB = $this->emlLib->getITALWEB();
            $this->emailTempPath = $this->praLib->SetDirectoryPratiche('', '', 'PEC');
            $this->profilo = proSoggetto::getProfileFromIdUtente();


            $this->certificato = App::$utente->getKey($this->nameForm . "_certificato");
            $this->elencoAllegati = App::$utente->getKey($this->nameForm . "_elencoAllegati");
            $this->elencoAllegatiOrig = App::$utente->getKey($this->nameForm . "_elencoAllegatiOrig");
            $this->elemento = App::$utente->getKey($this->nameForm . "_elemento");
            $this->currMessage = unserialize(App::$utente->getKey($this->nameForm . "_currMessage"));
            $this->currMailBox = unserialize(App::$utente->getKey($this->nameForm . "_currMailBox"));
            $this->dettagliFile = App::$utente->getKey($this->nameForm . "_dettagliFile");
            $this->elementoLocale = App::$utente->getKey($this->nameForm . "_elementoLocale");
            $this->currAlert = App::$utente->getKey($this->nameForm . "_currAlert");
            $this->visibilita = App::$utente->getKey($this->nameForm . "_visibilita");
            $this->refAccounts = App::$utente->getKey($this->nameForm . "_refAccounts");
            $this->Proric_rec = App::$utente->getKey($this->nameForm . "_Proric_rec");
            $this->returnModel = App::$utente->getKey($this->nameForm . "_returnModel");
            $this->returnEvent = App::$utente->getKey($this->nameForm . "_returnEvent");
            $this->datiAppoggio = App::$utente->getKey($this->nameForm . "_datiAppoggio");
            $this->elencoMail = App::$utente->getKey($this->nameForm . "_elencoMail");
            $this->numProt = App::$utente->getKey($this->nameForm . "_numProt");
            $this->tabAttiva = App::$utente->getKey($this->nameForm . "_tabAttiva");
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
            App::$utente->setKey($this->nameForm . "_currMailBox", serialize($this->currMailBox));
            App::$utente->setKey($this->nameForm . "_dettagliFile", $this->dettagliFile);
            App::$utente->setKey($this->nameForm . "_elementoLocale", $this->elementoLocale);
            App::$utente->setKey($this->nameForm . "_currAlert", $this->currAlert);
            App::$utente->setKey($this->nameForm . "_visibilita", $this->visibilita);
            App::$utente->setKey($this->nameForm . "_refAccounts", $this->refAccounts);
            App::$utente->setKey($this->nameForm . "_Proric_rec", $this->Proric_rec);
            App::$utente->setKey($this->nameForm . "_returnModel", $this->returnModel);
            App::$utente->setKey($this->nameForm . "_returnEvent", $this->returnEvent);
            App::$utente->setKey($this->nameForm . "_datiAppoggio", $this->datiAppoggio);
//            $this->elencoMail = array();
            App::$utente->setKey($this->nameForm . "_elencoMail", $this->elencoMail);
            App::$utente->setKey($this->nameForm . "_numProt", $this->numProt);
            App::$utente->setKey($this->nameForm . "_tabAttiva", $this->tabAttiva);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->tabAttiva = "";
                $this->loadModelConfig();
                $this->caricaConfigurazioni();
                $this->openRicerca(false, false);
                $this->caricaTabEmailLocali();

                if (!$_POST['returnModel']) {
                    $this->returnModel = 'praGestElenco';
                } else {
                    $this->returnModel = $_POST['returnModel'];
                }

                if (!$_POST['returnEvent']) {
                    $this->returnEvent = 'returnElencoMail';
                } else {
                    $this->returnEvent = $_POST['returnEvent'];
                }
                $this->visibilita = $this->praLib->GetVisibiltaSportello();


                $anatsp_rec_scia = $this->praLib->GetAnatsp(1);
                $anatsp_rec_ordinario = $this->praLib->GetAnatsp(2);
                $anatsp_rec_sue = $this->praLib->GetAnatsp(6);

                if (!$this->visibilita || ($anatsp_rec_scia['TSPPEC'] == "" && $anatsp_rec_ordinario['TSPPEC'] == "" && $anatsp_rec_sue['TSPPEC'] == "")) {
                    $this->openRicercaLocale();
                    Out::tabRemove($this->nameForm . "_tabMail", $this->nameForm . "_paneElenco");
                    Out::tabRemove($this->nameForm . "_tabMail", $this->nameForm . "_paneScarti");
                    break;
                }

                $risultato = $this->setRefAccounts();
                $this->CreaCombo();
                if ($risultato === true) {
//                    $this->openRicerca(false,false);
                    TableView::reload($this->gridElencoMail);
                } else {
                    Out::msgStop("Errore", "Nessuna casella di posta disponibile");
                    $this->close();
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    //TODO@ verificare se c'e' ancora l'evento change per i campi
                    case $this->nameForm . '_cbTutti':
                        if ($_POST[$this->nameForm . '_cbTutti'] == 1) {
                            Out::valore($this->nameForm . "_cbMsgInt", "0");
                            Out::valore($this->nameForm . "_cbMsgPEC", "0");
                            Out::valore($this->nameForm . "_cbAccettazione", "0");
                            Out::valore($this->nameForm . "_cbConsegna", "0");
                            Out::valore($this->nameForm . "_cbMsgStd", "0");
                            Out::valore($this->nameForm . "_cbMsgAnomalie", "0");
                            //$this->openRicerca(false);
                            TableView::reload($this->gridElencoMail);
                        }
                        break;
                    case $this->nameForm . '_cbMsgInt':
                    case $this->nameForm . '_cbMsgPEC':
                    case $this->nameForm . '_cbAccettazione':
                    case $this->nameForm . '_cbConsegna':
                    case $this->nameForm . '_cbMsgStd':
                    case $this->nameForm . '_cbMsgAnomalie':
                        Out::valore($this->nameForm . "_cbTutti", "0");
                        $_POST[$this->nameForm . '_cbTutti'] = 0;
                        //$this->openRicerca(false);
                        TableView::reload($this->gridElencoMail);
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
                        $this->caricaTabella(true);
                        $this->currAlert = $this->getAlert();
                        $this->refreshAlert();
                        break;
                    case $this->gridElencoMailScarti :
                        $this->caricaTabellaScarti(true);
                        break;
                    case $this->gridElencoMailLocale :
                        $this->caricaTabEmailLocali();
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
                        $this->tabAttiva = "ELENCO";
                        //
                        //Rileggo il record su MAIL_ARCHIVIO per vedere se la mail è stata scartata o caricata da un'altra finestra del gestionale
                        //
                        $msgDett = $this->CheckStatoMail($_POST['rowid']);
                        if ($msgDett) {
                            Out::msgQuestion("Dettaglio Mail", "<br>" . $msgDett, array(
                                'Torna' => array('id' => $this->nameForm . '_ConfermaRefreshGriglia', 'model' => $this->nameForm)
                                    ), "auto", "auto", "false"
                            );
                            break;
                        }
                        $this->Dettaglio();
                        //Out::show($this->nameForm . '_Scarta');
                        break;
                    case $this->gridElencoMailScarti :
                        $this->tabAttiva = "SCARTATE";
                        $this->Dettaglio(true);
                        Out::show($this->nameForm . '_Ripristina');
                        break;
                    case $this->gridElencoMailLocale :
                        $this->tabAttiva = "LOCALE";
                        $this->DettaglioLocale();
                        break;
                    case $this->gridAllegati :
                        $FileAllegato = $this->elencoAllegati[$_POST['rowid'] - 1]['DATAFILE'];
                        $FileDati = $this->elencoAllegati[$_POST['rowid'] - 1]['FILE'];
                        Out::openDocument(utiDownload::getUrl($FileAllegato, $FileDati));
                        break;
                    case $this->gridAllegatiOrig :
                        $FileAllegato = $this->elencoAllegatiOrig[$_POST['rowid'] - 1]['DATAFILE'];
                        $FileDati = $this->elencoAllegatiOrig[$_POST['rowid'] - 1]['FILE'];
                        Out::openDocument(utiDownload::getUrl($FileAllegato, $FileDati));
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridElencoMailLocale :
                        $record = $this->dettagliFile[$_POST['rowid']];
                        $pathMail = pathinfo($record['FILENAME'], PATHINFO_DIRNAME);
                        $idMail = pathinfo($record['FILENAME'], PATHINFO_FILENAME);
                        $praMail_rec = $this->praLib->getPraMail($idMail);
                        if ($praMail_rec) {
                            if ($praMail_rec['MAILSTATO'] == "CARICATA" && $praMail_rec['GESNUM']) {
                                Out::msgStop("Cancellazione File", "Impossibile cancellare la mail perchè è stata importata nella pratica n. " . $praMail_rec['GESNUM'] . ".");
                                break;
                            }
                            $delete_Info = 'Oggetto Delete PraMail record: ' . $praMail_rec['IDMAIL'] . " " . $praMail_rec['MAILSTATO'];
                            if (!$this->deleteRecord($this->PRAM_DB, 'PRAMAIL', $praMail_rec['ROWID'], $delete_Info)) {
                                Out::msgStop("Cancellazione Mail", "Errore in cancellazione record PRAMAIL.");
                                break;
                            }
                        }
                        if (!@unlink($this->dettagliFile[$_POST['rowid']]['FILENAME'])) {
                            Out::msgStop("Cancellazione Mail", "Errore in cancellazione file.");
                            $this->caricaTabEmailLocali();
                            break;
                        }
                        if (file_exists($delete_Info)) {
                            unlink($pathMail . "/" . $idMail . ".info");
                        }
                        $this->caricaTabEmailLocali();
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Inoltra':
//                        if (!$this->accLib->CheckParametriMail()) {
//                            Out::msgStop("ERRORE", "Attenzione parametri di invio mail mancanti o non corretti");
//                            break;
//                        }

                        $retDecode = $this->currMessage->getStruct();
                        if ($retDecode['ita_PEC_info'] != "N/A") {
                            $messaggioOriginale = $retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile'];
                        }

                        if (!$messaggioOriginale) {
                            $messaggioOriginale = $retDecode;
                        }

//                        if (isset($messaggioOriginale['DataFile'])) {
//                            $datafile = $messaggioOriginale['DataFile'];
//                        } else {
//                            foreach ($messaggioOriginale['Alternative'] as $value) {
//                                $datafile = $value['DataFile'];
//                            }
//                        }
                        $url = utiDownload::getUrl("emlbody.html", $this->currMessage->getEmlBodyDataFile(), false, true);
                        $iframe = '<iframe style="border: 1px dotted black;" src="' . $url . '" frameborder="0" width="99%" height="95%" id="' . $this->nameForm . '_emlOrigFrame">
                                     <p>Contenuto non visualizzabile.....</p>
                                   </iframe>';
                        $allegati = array();
                        foreach ($this->currMessage->getAttachments() as $allegato) {
                            $icon = utiIcons::getExtensionIconClass($allegato['FileName'], 32);
                            $fileSize = $this->praLib->formatFileSize(filesize($allegato['DataFile']));
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

                        foreach ($this->refAccounts as $account) {
                            $DaMail[] = $account['EMAIL'];
                        }

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
                    case $this->nameForm . '_Carica':
                        $msgOrig = $this->GetMittenteOriginale();
                        if ($this->elemento) {
                            //
                            //Rileggo il record su MAIL_ARCHIVIO per vedere se la mail è stata scartata o caricata da un'altra finestra del gestionale
                            //
                            $msgDett = $this->CheckStatoMail($this->elemento['ROWID']);
                            if ($msgDett) {
                                Out::msgQuestion("Dettaglio Mail", "<br>" . $msgDett, array(
                                    'Torna' => array('id' => $this->nameForm . '_ConfermaTornaElenco', 'model' => $this->nameForm)
                                        ), "auto", "auto", "false"
                                );
                                break;
                            }

                            $idMail = $this->elemento['IDMAIL'];
                            $subject = $msgOrig['subject'];
                            $formAddr = $msgOrig['address'];
                            if (!$msgOrig) {
                                $subject = $this->elemento['SUBJECT'];
                                $formAddr = $this->elemento['FROMADDR'];
                            }
                            $tipoMail = "KEYMAIL";
                        } else {
                            $idMail = pathinfo($this->dettagliFile[$this->elementoLocale]['FILENAME'], PATHINFO_FILENAME);
                            $subject = $msgOrig['subject'];
                            $formAddr = $msgOrig['address'];
                            if (!$msgOrig) {
                                $subject = $this->dettagliFile[$this->elementoLocale]['OGGETTO'];
                                $formAddr = $this->dettagliFile[$this->elementoLocale]['MITTENTE'];
                            }
                            $tipoMail = "KEYUPL";
                        }

                        $praMessage = new praMessage();
                        $datiMail = $praMessage->getDatiMail($idMail, $this->currMessage);
                        if ($datiMail['Status'] != 0) {
                            Out::msgStop("Attenzione! Non è stato possibile caricare l'Email.", $datiMail['Message']);
                            break;
                        }

                        if (!$datiMail['PRAMAIL']) {
                            Out::msgStop("Attenzione", 'Mail non indicizzata');
                            break;
                        }

                        if ($datiMail['PRAMAIL']['ISGENERIC']) {
                            $model = 'praGestDatiEssenziali';
                            $_POST['event'] = 'openform';
                            $_POST['returnModel'] = $this->nameForm;
                            $_POST['returnEvent'] = 'returnCaricaMailGenerica';
                            $_POST['oggetto'] = $subject;
                            $_POST['email'] = $formAddr;
                            $_POST['datiMail'] = $datiMail;
                            $_POST['isFrontOffice'] = false;
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                            break;
                        }

                        $Filent_Rec = $this->praLib->GetFilent(42);
                        if ($Filent_Rec['FILVAL'] == 1) {
                            if (!$this->CaricaNew($datiMail)) {
                                break;
                            }
                        } else {
                            if (!$this->CaricaOld($datiMail, $formAddr, $subject)) {
                                break;
                            }
                        }
                        break;
                    case $this->nameForm . '_Carica_OLD':
                        $msgOrig = $this->GetMittenteOriginale();
                        if ($this->elemento) {
                            //
                            //Rileggo il record su MAIL_ARCHIVIO per vedere se la mail è stata scartata o caricata da un'altra finestra del gestionale
                            //
                            $msgDett = $this->CheckStatoMail($this->elemento['ROWID']);
                            if ($msgDett) {
                                Out::msgQuestion("Dettaglio Mail", "<br>" . $msgDett, array(
                                    'Torna' => array('id' => $this->nameForm . '_ConfermaTornaElenco', 'model' => $this->nameForm)
                                        ), "auto", "auto", "false"
                                );
                                break;
                            }

                            $idMail = $this->elemento['IDMAIL'];
                            $subject = $msgOrig['subject'];
                            $formAddr = $msgOrig['address'];
                            if (!$msgOrig) {
                                $subject = $this->elemento['SUBJECT'];
                                $formAddr = $this->elemento['FROMADDR'];
                            }
                            $tipoMail = "KEYMAIL";
                        } else {
                            $idMail = pathinfo($this->dettagliFile[$this->elementoLocale]['FILENAME'], PATHINFO_FILENAME);
                            $subject = $msgOrig['subject'];
                            $formAddr = $msgOrig['address'];
                            if (!$msgOrig) {
                                $subject = $this->dettagliFile[$this->elementoLocale]['OGGETTO'];
                                $formAddr = $this->dettagliFile[$this->elementoLocale]['MITTENTE'];
                            }
                            $tipoMail = "KEYUPL";
                        }

                        $praMessage = new praMessage();
                        $datiMail = $praMessage->getDatiMail($idMail, $this->currMessage);
                        if ($datiMail['Status'] != 0) {
                            Out::msgStop("Attenzione! Non è stato possibile caricare l'Email.", $datiMail['Message']);
                            break;
                        }

                        if (!$datiMail['PRAMAIL']) {
                            Out::msgStop("Attenzione", 'Mail non indicizzata');
                            break;
                        }

                        /*
                         * Verifico se c'è il flag pratica collegata, se si la carico come nuova pratica
                         */
                        $variante = false;
                        if ($datiMail['Dati']['PRORIC_REC']['RICPC'] == "1") {
                            $variante = true;
                        }

                        if ($datiMail['PRAMAIL']['ISGENERIC']) {
                            $model = 'praGestDatiEssenziali';
                            $_POST['event'] = 'openform';
                            $_POST['returnModel'] = $this->nameForm;
                            $_POST['returnEvent'] = 'returnCaricaMailGenerica';
                            $_POST['oggetto'] = $subject;
                            $_POST['email'] = $formAddr;
                            $_POST['datiMail'] = $datiMail;
                            $_POST['isFrontOffice'] = false;
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                            break;
                        } elseif ($datiMail['PRAMAIL']['ISINTEGRATION'] && !$variante) {
                            if ($this->elemento) {
                                $datiMail['archivio'] = true;
                            } else {
                                $datiMail['archivio'] = false;
                            }
                            $Propas_rec = $this->praLib->CaricaPassoIntegrazione($datiMail['Dati']['PRORIC_REC'], $datiMail['Dati']['ELENCOALLEGATI'], $datiMail['Dati']['FILENAME'], $datiMail['PRAMAIL'], $datiMail['archivio'], false, $datiMail['Dati']['PRAFOLIST_REC']['ROW_ID']);
                            if ($Propas_rec == false) {
                                Out::msgStop("Errore cariamento passo integrazione", $this->praLib->getErrMessage());
                                break;
                            }
                            //
                            //Chiamo model praPasso
                            //
                            $datiForm = array(
                                'GESNUM' => $Propas_rec['PRONUM'],
                                'PROPAK' => $Propas_rec['PROPAK']
                            );

                            $model = 'praPasso';
                            $_POST = array();
                            $_POST['event'] = 'openform';
                            $_POST['rowid'] = $Propas_rec['ROWID'];
                            $_POST['modo'] = "edit";
                            $_POST['perms'] = $this->perms;
                            $_POST['datiForm'] = $datiForm;
                            $_POST[$model . '_returnModel'] = $this->nameForm;
                            $_POST[$model . '_returnMethod'] = 'returnPraPasso';
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                            break;
                        } elseif ($datiMail['PRAMAIL']['ISANNULLAMENTO']) {
                            if ($this->elemento) {
                                $datiMail['archivio'] = true;
                            } else {
                                $datiMail['archivio'] = false;
                            }

                            $Propas_rec = $this->praLib->CaricaPassoAnnullamento($datiMail['Dati']['PRORIC_REC'], $datiMail['Dati']['FILENAME'], $datiMail['PRAMAIL'], $datiMail['archivio'], false, $datiMail['Dati']['PRAFOLIST_REC']['ROW_ID']);
                            if ($Propas_rec == false) {
                                break;
                            }
                            $datiForm = array(
                                'GESNUM' => $Propas_rec['PRONUM'],
                                'PROPAK' => $Propas_rec['PROPAK']
                            );

                            $model = 'praPasso';
                            $_POST = array();
                            $_POST['event'] = 'openform';
                            $_POST['rowid'] = $Propas_rec['ROWID'];
                            $_POST['modo'] = "edit";
                            $_POST['perms'] = $this->perms;
                            $_POST['datiForm'] = $datiForm;
                            $_POST[$model . '_returnModel'] = $this->nameForm;
                            $_POST[$model . '_returnMethod'] = 'returnPraPasso';
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                            break;
                        } elseif ($datiMail['PRAMAIL']['ISPARERE']) {
                            if ($this->elemento) {
                                $datiMail['archivio'] = true;
                            } else {
                                $datiMail['archivio'] = false;
                            }
                            $Propas_rec = $this->praLib->CaricaPassoIntegrazione($datiMail['Dati']['PRORIC_REC'], $datiMail['Dati']['ELENCOALLEGATI'], $datiMail['Dati']['FILENAME'], $datiMail['PRAMAIL'], $datiMail['archivio'], true, $datiMail['Dati']['PRAFOLIST_REC']['ROW_ID']);
                            if ($Propas_rec == false) {
                                Out::msgStop("Errore cariamento passo parere", $this->praLib->getErrMessage());
                                break;
                            }
                            //
                            //Chiamo model praPasso
                            //
                            $datiForm = array(
                                'GESNUM' => $Propas_rec['PRONUM'],
                                'PROPAK' => $Propas_rec['PROPAK']
                            );

                            $model = 'praPasso';
                            $_POST = array();
                            $_POST['event'] = 'openform';
                            $_POST['rowid'] = $Propas_rec['ROWID'];
                            $_POST['modo'] = "edit";
                            $_POST['perms'] = $this->perms;
                            $_POST['datiForm'] = $datiForm;
                            $_POST[$model . '_returnModel'] = $this->nameForm;
                            $_POST[$model . '_returnMethod'] = 'returnPraPasso';
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                            break;
                        } elseif ($datiMail['PRAMAIL']['ISFRONTOFFICE']) {
                            $model = 'praGestDatiEssenziali';
                            $_POST['event'] = 'openform';
                            $_POST['returnModel'] = $this->nameForm;
                            $_POST['returnEvent'] = 'returnCaricaMailGenerica';
                            $_POST['oggetto'] = $subject;
                            $_POST['email'] = $formAddr;
                            $_POST['datiMail'] = $datiMail;
                            $_POST['isFrontOffice'] = true;
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                            break;
                        }
                        $model = $this->returnModel;
                        $_POST['event'] = $this->returnEvent;
                        $_POST['datiMail'] = $datiMail['Dati'];

                        $_POST['returnModel'] = $this->nameForm;
                        $_POST['returnEvent'] = 'returnPraGest';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        if ($this->refAccounts) {
                            //$this->openRicerca(false, true);
                            $this->openRicerca(false, false);
                            TableView::reload($this->gridElencoMail);
                        } else {
                            $this->openRicercaLocale();
                        }
                        break;
                    case $this->nameForm . '_Filtra':
                        //$this->openRicerca(false);
                        TableView::reload($this->gridElencoMail);
                        break;
                    case $this->nameForm . '_Elenca':
                        $this->tornaElenco();
                        break;
                    case $this->nameForm . '_ConfermaTornaElenco':
                        TableView::reload($this->gridElencoMail);
                        $this->tornaElenco();
                        break;
                    case $this->nameForm . '_ConfermaRefreshGriglia':
                        TableView::reload($this->gridElencoMail);
                        break;
                    case $this->nameForm . '_SalvaMailLocale':
                        $emlFile = $this->currMessage->getEmlFile();
                        $emlFileName = pathinfo($emlFile, PATHINFO_BASENAME);
                        if (!file_exists($emlFile)) {
                            Out::msgStop("Attenzione", "Il file $emlFile non è stato trovato");
                            break;
                        }
                        if (!@copy($emlFile, $this->emailTempPath . $emlFileName)) {
                            Out::msgStop("Attenzione", "salvataggio file: " . $this->emailTempPath . $emlFileName . " fallito.");
                        } else {
                            $struct_file = $this->emailTempPath . "/" . pathinfo($emlFileName, PATHINFO_FILENAME) . ".info";
                            $message = new emlMessage();
                            $message->setEmlFile($this->emailTempPath . $emlFileName);
                            $message->parseEmlFileDeep();
                            $idMail = pathinfo($message->getEmlFile(), PATHINFO_FILENAME);
                            if (!file_exists($struct_file)) {
                                file_put_contents($struct_file, serialize($message->getStruct()));
                            }
                            $praMail_rec = $this->praLib->getPraMail($idMail);
                            if (!$praMail_rec) {
                                $praMessage = new praMessage();
                                $praMail_rec = $praMessage->savePramailRecord($message, $idMail, 'KEYUPL');
                                $message->cleanData();
                            }
                            $this->caricaTabEmailLocali();
                            $this->tornaElenco();
                            Out::tabSelect($this->nameForm . "_tabMail", $this->nameForm . "_paneLocale");
                        }
                        break;
                    case $this->nameForm . '_EliminaAssegnazione':
                        Out::msgQuestion("Eimina Assegnazione Mail", "Confermi l'eliminazione dell'assegnazione corrente?", array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaEliminaAssegnazione', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaEliminaAssegnazione', 'model' => $this->nameForm)
                                ), "auto", "auto", "false"
                        );
                        break;

                        break;
                    case $this->nameForm . '_ConfermaEliminaAssegnazione':
                        $praMail_rec = $this->praLib->getPraMail($this->elemento['IDMAIL']);
                        $ananom_rec = $this->praLib->GetAnanom($praMail_rec['ASSRES']);
                        $praMail_rec['ASSRES'] = "";
                        $update_Info = 'Oggetto: Elimina Assegnazione mail a nominativo, id:' . $praMail_rec['IDMAIL'] . " nominativo:" . $ananom_rec['NOMRES'];
                        if (!$this->updateRecord($this->PRAM_DB, 'PRAMAIL', $praMail_rec, $update_Info)) {
                            Out::msgStop("Attenzione!!", "Errore aggiornamento dati assegnazione mail.");
                            break;
                        }
                        $_POST['rowid'] = $this->elemento['ROWID'];
                        $this->dettaglio();
                        break;
                    case $this->nameForm . '_AssegnaMail':
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "Ricerca Nominativo", '', $this->nameForm . '_AssegnaMail');
                        break;
                    case $this->nameForm . '_AssegnaPasso':

                        /*
                         * Rileggo il record su MAIL_ARCHIVIO per vedere se la mail è stata scartata o caricata da un'altra finestra del gestionale
                         */
                        $msgDett = $this->CheckStatoMail($this->elemento['ROWID']);
                        if ($msgDett) {
                            Out::msgQuestion("Dettaglio Mail", "<br>" . $msgDett, array(
                                'Torna' => array('id' => $this->nameForm . '_ConfermaTornaElenco', 'model' => $this->nameForm)
                                    ), "auto", "auto", "false"
                            );
                            break;
                        }

                        /*
                         * Apro praGestElenco per la ricerca dei fascicoli
                         */
                        $openModel = "praGestElenco";
                        $model = $openModel . "_searchDialog";
                        itaLib::openDialog($openModel, true, true, 'desktopBody', "", "", $model);
                        $objModel = itaModel::getInstance($openModel, $model);
                        $objModel->setReturnModel($this->nameForm);
                        $objModel->setReturnEvent('returnProges');
                        $objModel->searchMode = true;
                        $objModel->setEvent('openform');
                        $objModel->parseEvent();

//                        //praRic::praRicProges($this->nameForm, " AND GESDCH = ''");
//                        Out::msgInput(
//                                'Filtra Pratiche', array(
//                            array(
//                                'label' => array('style' => "width:150px;", "value" => 'N. Protocollo in partenza'),
//                                'id' => $this->nameForm . '_numProt',
//                                'name' => $this->nameForm . '_numProt',
//                                'type' => 'text',
//                                'size' => '10',
//                                'value' => '',
//                                'maxchars' => '10'),
//                            array(
//                                'label' => array('style' => "width:150px;", "value" => 'Anno'),
//                                'id' => $this->nameForm . '_annoProt',
//                                'name' => $this->nameForm . '_annoProt',
//                                'type' => 'text',
//                                'size' => '6',
//                                'value' => '',
//                                'maxchars' => '4'),
//                            array(
//                                'label' => array('style' => "width:150px;", "value" => 'Vedi anche Pratiche Chiuse'),
//                                'id' => $this->nameForm . '_vediChiuse',
//                                'name' => $this->nameForm . '_vediChiuse',
//                                'type' => 'checkbox',
//                                'value' => '',)
//                                ), array(
//                            'F5-Filtra' => array('id' => $this->nameForm . '_ConfermaFiltraPratiche', 'model' => $this->nameForm, 'shortCut' => "f5"),
//                            'F6-Conferma Tutti' => array('id' => $this->nameForm . '_ConfermaTuttePratiche', 'model' => $this->nameForm, 'shortCut' => "f6")
//                                ), $this->nameForm
//                        );

                        break;
                    case $this->nameForm . '_ConfermaTuttePratiche':
                        praRic::praRicProges($this->nameForm, " AND GESDCH = ''");
                        break;
                    case $this->nameForm . '_ConfermaFiltraPratiche':
                        $filtraProtocolloP = $msgFiltra = $msgChiuse = "";
                        $whereChiuse = " AND GESDCH = ''";
                        if ($_POST[$this->nameForm . "_vediChiuse"] == 1) {
                            $msgChiuse = "<p style=\"color:dark-red;text-decoration:underline;\"><b>Stai Visualizzando anche le Pratiche Chiuse</b></p>";
                            $whereChiuse = "";
                        }
                        if ($_POST[$this->nameForm . "_numProt"] && $_POST[$this->nameForm . "_annoProt"]) {
                            $msgFiltra = "<p style=\"color:red\"><b>Pratiche Filtrate per il Protocollo n. " . $_POST[$this->nameForm . "_numProt"] . "/" . $_POST[$this->nameForm . "_annoProt"] . "</b></p>";
                            $filtraProtocolloP = $_POST[$this->nameForm . "_annoProt"] . $_POST[$this->nameForm . "_numProt"];
                            $this->numProt = $filtraProtocolloP;
                        }
                        praRic::praRicProges($this->nameForm, $whereChiuse, "", "Seleziona la pratica: $msgChiuse$msgFiltra", $filtraProtocolloP);
                        //praRic::praRicProges($this->nameForm, " AND GESDCH = ''", "", "Seleziona la pratica: $msgChiuse$msgFiltra", $filtraProtocolloP);
                        break;
                    case $this->nameForm . '_Ricevi':
                        $this->scaricaPosta();
                        break;
                    case $this->nameForm . '_Scarta':
                        $this->GetFormMotivoScarta();
                        break;
                    case $this->nameForm . '_ConfermaScarta':
                        $motivo = $_POST[$this->nameForm . '_scartaMailMotivo'];
                        $this->scartaMail($this->elemento['ROWID'], false, $motivo);
                        $this->openRicerca(false, false);
                        TableView::reload($this->gridElencoMail);
                        break;
                    case $this->nameForm . '_Ripristina':
                        $this->scartaMail($this->elemento['ROWID'], true);
                        $this->openRicercaScarti();
//                        $this->caricaTabellaScarti(true);                                                
                        TableView::reload($this->gridElencoMail);
                        TableView::reload($this->gridElencoMailScarti);
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
                    case $this->nameForm . '_Salva':
                        $this->setConfig();
                        //$this->loadModelConfig();
                        break;
                    case $this->nameForm . '_insertAltroArr':
                        $Propas_rec_sel = $this->praLib->GetPropas($this->datiAppoggio['post']['retKey'], "rowid");
                        $protocolla['ANTECEDENTE'] = $Propas_rec_sel['PROPAK'];
                        $this->datiAppoggio['post']['retKey'] = '';
                    case $this->nameForm . '_ProtocollaArrivo':
                        $msgOrig = $this->GetMittenteOriginale();
                        if ($this->elemento) {
                            $elemento = $this->elemento;
                            $protocolla['MITTENTE'] = $msgOrig['address'];
                            $protocolla['NOTE'] = $msgOrig['subject'];
                            if (!$msgOrig) {
                                $protocolla['MITTENTE'] = $elemento['FROMADDR'];
                                $protocolla['NOTE'] = $elemento['SUBJECT'];
                            }
                            $protocolla['DATA'] = date("Ymd", strtotime($elemento['MSGDATE']));
                        } else {
                            $elemento = $this->dettagliFile[$this->elementoLocale];
                            $protocolla['MITTENTE'] = $msgOrig['address'];
                            $protocolla['NOTE'] = $msgOrig['subject'];
                            if (!$msgOrig) {
                                $protocolla['MITTENTE'] = $elemento['MITTENTE'];
                                $protocolla['NOTE'] = $elemento['OGGETTO'];
                            }
                            $protocolla['DATA'] = date("Ymd", strtotime($elemento['DATA']));
                        }
                    case $this->nameForm . '_NonProtocollare':
                        $emlFile = $this->currMessage;
                        $messaggioOriginale = $emlFile->getMessaggioOriginaleObj();
                        if ($messaggioOriginale) {
                            $emlFile = $messaggioOriginale;
                        }
                        $allegatiMail = $emlFile->getAttachments();
                        $protocolla['ELENCOALLEGATI'] = $allegatiMail;
                        $percorsoTmp = itaLib::getPrivateUploadPath();
                        if (!@is_dir($percorsoTmp)) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Archiviazione File.", "Creazione ambiente di lavoro temporaneo fallita.");
                                break;
                            }
                        }
                        $fileOrig = $this->currMessage->getEmlFile();
                        $randName = md5(rand() * time()) . "." . pathinfo($fileOrig, PATHINFO_EXTENSION);
                        if (!@copy($fileOrig, $percorsoTmp . '/' . $randName)) {
                            Out::msgStop("Archiviazione File.", "Copia della Email temporanea fallita.");
                            break;
                        }
                        if ($this->elemento) {
                            $oggetto = $this->elemento['SUBJECT'];
                            $daMail = array('archivio' => true, 'IDMAIL' => $this->elemento['IDMAIL']);
                        } else {
                            $oggetto = $this->dettagliFile[$this->elementoLocale]['OGGETTO'];
                            $daMail = array('archivio' => false, 'IDMAIL' => pathinfo($this->dettagliFile[$this->elementoLocale]['FILENAME'], PATHINFO_FILENAME));
                        }
                        if ($_POST['id'] == $this->nameForm . '_NonProtocollare') {
                            $fileName = $this->praLib->clearFileName($oggetto);
                            $allegati[] = array(
                                'FILEPATH' => $percorsoTmp . '/' . $randName,
                                'FILENAME' => $randName,
                                'FILEINFO' => 'File Originale: da PEC',
                                'FILEORIG' => $fileName . "." . pathinfo($randName, PATHINFO_EXTENSION)
                            );

                            foreach ($allegatiMail as $alle) {
                                $allegati[] = array(
                                    'FILEPATH' => $alle['DataFile'],
                                    'FILENAME' => md5(rand() * time()) . "." . pathinfo($alle['FileName'], PATHINFO_EXTENSION),
                                    'FILEINFO' => 'File Originale:' . $alle['FileName'],
                                    'FILEORIG' => $alle['FileName']
                                );
                            }
                        } else {
                            $protocolla['FILENAME'] = $percorsoTmp . '/' . $randName;
                        }
                        $daMail['protocolla'] = $protocolla;
                        $datiPost = $this->datiAppoggio['formData'];
                        $gesnum = $datiPost['PROGES_REC']['GESNUM'];
                        $datiInfo = "Caricamento Email per la pratica " . (int) substr($gesnum, 4) . "/" . substr($gesnum, 0, 4);
                        if ($this->datiAppoggio['post']['retKey'] == '') {
                            $datiInfo .= '<br>Inserire i dati del passo prima di Aggiungere.';
                            $model = 'praPasso';
                            $_POST = array();
                            $_POST['event'] = 'openform';
                            $_POST['procedimento'] = $gesnum;
                            $_POST['modo'] = "add";
                            $_POST['listaAllegati'] = $allegati;
                            $_POST['datiInfo'] = $datiInfo;
                            $_POST['perms'] = $this->perms;
                            $_POST[$model . '_daMail'] = $daMail;
                            $_POST[$model . '_returnModel'] = $this->nameForm;
                            $_POST[$model . '_returnMethod'] = 'returnPraPasso';
                            $_POST[$model . '_title'] = 'Gestione Passo proveniente dalla pratica: ' . (int) substr($gesnum, 4) . "/" . substr($gesnum, 0, 4);
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                            break;
                        } else {
                            $model = 'praPasso';
                            $_POST = array();
                            $_POST['event'] = 'openform';
                            $_POST['rowid'] = $this->datiAppoggio['post']['retKey'];
                            $_POST['modo'] = "edit";
                            $_POST['listaAllegati'] = $allegati;
                            $_POST['datiInfo'] = $datiInfo;
                            $_POST['perms'] = $this->perms;
                            $_POST[$model . '_daMail'] = $daMail;
                            $_POST[$model . '_returnModel'] = $this->nameForm;
                            $_POST[$model . '_returnMethod'] = 'returnPraPasso';
                            $_POST[$model . '_title'] = 'Gestione Passo proveniente dalla pratica: ' . (int) substr($gesnum, 4) . "/" . substr($gesnum, 0, 4);
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                        }
                        break;
                    case $this->nameForm . '_VisualizzaPratica':
                        $gesnum = $this->datiAppoggio['GESNUM'];
                        $proges_rec = $this->praLib->GetProges($gesnum);
                        $this->datiAppoggio = null;
                        $model = 'praGestElenco';
                        $_POST = array();
                        $_POST['rowidDettaglio'] = $proges_rec['ROWID'];
                        itaLib::openForm($model);
                        $objModel = itaModel::getInstance($model);
                        $objModel->setEvent('openform');
                        $objModel->parseEvent();
                        break;
                    case $this->nameForm . '_ScartaMulti':
                        //$this->selezionaDaScartare();
                        Out::msgInput(
                                'Visualizza Mail da Scartare', array(
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Da Data  '),
                                'id' => $this->nameForm . '_DaDataMail',
                                'name' => $this->nameForm . '_DaDataMail',
                                'class' => "ita-date",
                                'size' => '15',
                                'maxlength' => '30'),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'A Data  '),
                                'id' => $this->nameForm . '_ADataMail',
                                'name' => $this->nameForm . '_ADataMail',
                                'class' => "ita-date",
                                'size' => '15',
                                'maxlength' => '30'),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Tipo Pec'),
                                'id' => $this->nameForm . '_TipoPec',
                                'name' => $this->nameForm . '_TipoPec',
                                'type' => 'select',
                                'options' => array(
                                    array("", "Tutte"),
                                    array("ACC", "Accettazioni"),
                                    array("CON", "Consegne"),
                                    array("PEC", "Pec"),
                                    array("ANO", "Anomalie")
                                )
                            ),
                                ), array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaDateMail', 'model' => $this->nameForm, 'shortCut' => "f5")
                                ), $this->nameForm
                        );

                        break;
                    case $this->nameForm . '_ConfermaDateMail':
                        $this->selezionaDaScartare();
                        break;
                    case $this->nameForm . '_paneElenco':
                        Out::show($this->nameForm . "_Filtra");
                        Out::show($this->nameForm . "_Salva");
                        Out::show($this->nameForm . "_divCampiSearch");
                        Out::hide($this->nameForm . "_FiltraScarti");
                        Out::hide($this->nameForm . '_divCampiSearchScarti');
                        break;
                    case $this->nameForm . '_paneScarti':
                        Out::hide($this->nameForm . "_Filtra");
                        Out::hide($this->nameForm . "_Salva");
                        Out::hide($this->nameForm . '_divCampiSearch');
                        Out::show($this->nameForm . "_FiltraScarti");
                        Out::show($this->nameForm . '_divCampiSearchScarti');
                        break;
                    case $this->nameForm . '_FiltraScarti':
                        $this->Azzera(false);
                        $this->caricaTabellaScarti();
                        break;
                    case $this->nameForm . '_ConfermaImportaMailFO':
                        if ($this->elemento) {
                            //Mail Scaricata
                            $idMail = $this->elemento['IDMAIL'];
                            $tipoMail = "KEYMAIL";
                        } else {
                            //Mail Locale
                            $idMail = pathinfo($this->dettagliFile[$this->elementoLocale]['FILENAME'], PATHINFO_FILENAME);
                            $tipoMail = "KEYUPL";
                        }
                        $praMessage = new praMessage();
                        $datiMail = $praMessage->getDatiMail($idMail, $this->currMessage);
                        if ($datiMail['Status'] != 0) {
                            Out::msgStop("Attenzione! Non è stato possibile caricare l'Email.", $datiMail['Message']);
                            break;
                        }
                        $proges_rec = $this->praLib->GetProges($datiMail['Dati']['PRORIC_REC']['RICNUM'], 'richiesta');
                        if (!$proges_rec) {
                            Out::msgStop("Attenzione! Non è stata trovata la pratica caricata per la richiestan .", $datiMail['Dati']['PRORIC_REC']['RICNUM']);
                            break;
                        }
                        //
                        //salvo eml importato se presente
                        //
                        if ($datiMail['Dati']['FILENAME']) {
                            if (!$this->praLib->RegistraEml($datiMail['Dati']['FILENAME'], $proges_rec['GESNUM'])) {
                                break;
                            }

                            //
                            //Aggiorno PRAMAIL e MAIL ARCHIVIO
                            //
                            $this->praLib->setClasseCaricatoPasso("daMail", $datiMail['PRAMAIL']['IDMAIL'], $proges_rec['GESNUM']);
//                                if (!$this->praLib->setClasseCaricatoPasso("daMail", $datiMail['PRAMAIL']['IDMAIL'], $proges_rec['GESNUM'])) {
//                                    Out::msgStop("Aggiornamento Mail", $this->praLib->getErrMessage());
//                                    
//                                }
                            //
                            $this->caricaTabella();
                            //
                            $model = "praGest";
                            $_POST['rowidDettaglio'] = $proges_rec['ROWID'];
                            $modelObj = itaModel::getInstance($model);
                            itaLib::openForm($model);
                            $modelObj->setReturnModel($this->nameForm);
                            $modelObj->setReturnEvent('returnPraGest');
                            $modelObj->setEvent('openform');
                            $modelObj->parseEvent();
                            //
                            Out::msgBlock($this->nameForm, 3000, true, "Mail richiesta n. " . $datiMail['Dati']['PRORIC_REC']['RICNUM'] . " Importata correttamente nella Pratica " . $proges_rec['GESNUM']);
                        } else {
                            Out::msgStop("Archiviazione File", "Impossibile determinare il nome del file Eml della richiesta n. " . $datiMail['Dati']['PRORIC_REC']['RICNUM']);
                        }
                        break;
                    case $this->nameForm . '_Esci':
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'cellSelect':
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
                                $this->praLib->VisualizzaFirme($filepath . "/" . $P7Mfile, $allegatoFirmato['DATAFILE']);
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
                                $this->praLib->VisualizzaFirme($filepath . "/" . $P7Mfile, $allegatoFirmato['DATAFILE']);
                                break;
                        }
                        break;
                }
                break;
            case $this->nameForm . "_returnUploadFile":
                $randName = md5(rand() * time()) . "." . pathinfo($_POST['uploadedFile'], PATHINFO_EXTENSION);
                if (strtolower(pathinfo($randName, PATHINFO_EXTENSION)) != "eml") {
                    Out::msgStop("Attenzione!!", "Il file caricato non sembra essere un file mail valido.<br>Caricare un file eml");
                    break;
                }
                if (!@copy($_POST['uploadedFile'], $this->emailTempPath . $randName)) {
                    Out::msgStop("Attenzione", "salvataggio file:" . $_POST['uploadedFile'] . " fallito.");
                } else {
                    $struct_file = $this->emailTempPath . "/" . pathinfo($randName, PATHINFO_FILENAME) . ".info";
                    $message = new emlMessage();
                    $message->setEmlFile($this->emailTempPath . $randName);
                    $message->parseEmlFileDeep();
                    $idMail = pathinfo($message->getEmlFile(), PATHINFO_FILENAME);
                    if (!file_exists($struct_file)) {
                        file_put_contents($struct_file, serialize($message->getStruct()));
                    }
                    $praMail_rec = $this->praLib->getPraMail($idMail);
                    if (!$praMail_rec) {
                        $praMessage = new praMessage();
                        $praMail_rec = $praMessage->savePramailRecord($message, $idMail, 'KEYUPL');
                        $message->cleanData();
                    }
                    $this->caricaTabEmailLocali();
                }
                break;
//            case 'returnAccount':
//                $Mail_account_rec = $this->emlLib->getMailAccount($_POST['retKey'], 'rowid');
//                $this->setRefAccounts(array(array("EMAIL" => $Mail_account_rec['MAILADDR'])));
//                $this->openRicerca();
//                break;
            case 'returnAccountSportelli':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        Out::msgStop("Attenzione!", "Account non selezionato.");
                        $this->close();
                        break;
                    default:
                        $account[0] = array('EMAIL' => $_POST['rowData']['TSPPEC']);
                        $risultato = $this->setRefAccounts($account);
                        if ($risultato === true) {
                            $titolo = "Elenco Mail - " . $_POST['rowData']['TSPDES'] . " - " . $_POST['rowData']['TSPPEC'];
                            Out::setAppTitle($this->nameForm, "<span>$titolo</span>");
                            $this->openRicerca(false, false);
                            TableView::enableEvents($this->gridElencoMail);
                            TableView::reload($this->gridElencoMail);
                        } else {
                            $this->Azzera();
                            $this->Nascondi();
                            Out::show($this->nameForm);
                            Out::show($this->divRis);
                            Out::hide($this->nameForm . '_divDettaglio');
                            Out::hide($this->nameForm . '_divInfoMail');
                            Out::tabSelect($this->nameForm . "_tabMail", $this->nameForm . "_paneElenco");
                            $this->currAlert = "SELEZIONARE UN ACCOUNT VALIDO.";
                            $this->refreshAlert();
                            Out::block($this->divRis);
                            Out::msgStop("Attenzione!", "Errore nella selezione dell'account da gestire.<br>Contattare l'assistenza.");
                        }
                        break;
                }
                break;
            case 'returnProges':
                //$proges_rec = $this->praLib->GetProges($_POST['rowData']['ROWID'], 'rowid');
                if ($_POST['rowid']) {
                    $proges_rec = $this->praLib->GetProges($_POST['rowid'], 'rowid');
                    $gesnum = $proges_rec['GESNUM'];
                    $anades_rec = $this->praLib->GetAnades($gesnum, 'codice');
                    $anapra_rec = $this->praLib->GetAnapra($proges_rec['GESPRO'], 'codice');
                    $_POST['PROGES_REC'] = $proges_rec;
                    if ($this->elemento) {
                        $oggetto = $this->elemento['SUBJECT'];
                    } else {
                        $oggetto = $this->dettagliFile[$this->elementoLocale]['SUBJECT'];
                    }
                    if ($proges_rec['GESPRA']) {
                        $msgGespra = "<br>Richiesta num° " . (int) substr($proges_rec['GESPRA'], 4) . "/" . substr($proges_rec['GESPRA'], 0, 4);
                    }
                    if ($proges_rec['GESNPR']) {
                        $msgGesnpr = "<br>Protocollo N. " . (int) substr($proges_rec['GESNPR'], 4) . "/" . substr($proges_rec['GESNPR'], 0, 4);
                    }

                    $Serie_rec = $this->praLib->ElaboraProgesSerie($proges_rec['GESNUM'], $proges_rec['SERIECODICE'], $proges_rec['SERIEANNO'], $proges_rec['SERIEPROGRESSIVO']);
                    $infoDetail = "Seleziona il Passo proveniente dalla Pratica: <b>"
                            . $Serie_rec . "</b> ($gesnum) " . $msgGesnpr
                            . $msgGespra
                            . " da " . $anades_rec['DESNOM'] . " - " . $anades_rec['DESFIS']
                            . "<br>" . $anapra_rec['PRADES__1'] . " del " . date("d/m/Y", strtotime($proges_rec['GESDRE']))
                            . "<br>per l'Email con oggetto: " . $oggetto;

                    $this->setModelData($_POST);
                    if ($_POST['rowData']['FL_PRT_PAR'] == 0 && $_POST['rowData']['FL_PRT_ARR'] == 0) {
                        praRic::praRicPropas($this->nameForm, " AND PRONUM='$gesnum' AND PROFIN='' AND PROPUB='' AND PROKPRE=''", '', $infoDetail, true);
                    } else if ($_POST['rowData']['FL_PRT_PAR'] == 0 && $_POST['rowData']['FL_PRT_ARR'] == 1) {
                        praRic::praRicPropas($this->nameForm, " AND PRONUM='$gesnum' AND PROFIN='' AND PROPUB='' AND PROKPRE=''", '', $infoDetail, true);
                    } else if ($_POST['rowData']['FL_PRT_PAR'] == 1 && $_POST['rowData']['FL_PRT_ARR'] == 0) {
                        $msgFiltra = "<p style=\"color:red\"><b>Pratiche Filtrate per il Protocollo n. " . substr($this->numProt, 4) . "/" . substr($this->numProt, 0, 4) . "</b></p>";
                        praRic::praRicPropas($this->nameForm, " AND PRONUM='$gesnum' AND PROFIN='' AND PROPUB='' AND PROKPRE=''", '', $msgFiltra . $infoDetail, true, $this->numProt);
                    } else {
                        praRic::praRicPropas($this->nameForm, " AND PRONUM='$gesnum' AND PROFIN='' AND PROPUB='' AND PROKPRE=''", '', $infoDetail, true);
                    }
                }
                break;
            case 'returnPropas':
                if ($_POST['retKey']) {
                    $propas_rec = $this->praLib->GetPropas($_POST['retKey'], 'rowid');
                    $pracomA_rec = $this->praLib->GetPracomA($propas_rec['PROPAK']);
                }
                $this->datiAppoggio['formData'] = $this->formData;
                $this->datiAppoggio['post'] = $_POST;

                if ($pracomA_rec) {
                    Out::msgQuestion("Attenzione!", "E' già stato inserito l'arrivo per questo passo.<br>
                        Vuoi caricare un nuovo passo, collegato al sequente e inserire la comunicazione in arrivo?", array(
                        'F8 - No' => array('id' => $this->nameForm . '_NullaDaEseguire',
                            'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5 - Si' => array('id' => $this->nameForm . '_insertAltroArr',
                            'model' => $this->nameForm, 'shortCut' => "f5")
                            ), 'auto', 'auto', 'false'
                    );
//                    Out::msgQuestion("Attenzione!", "E' già stato inserito l'arrivo per questo passo.<br>
//                        E' possibile solo allegare l'email al passo. Vuoi continuare?", array(
//                        'F8 - No' => array('id' => $this->nameForm . '_NullaDaEseguire',
//                            'model' => $this->nameForm, 'shortCut' => "f8"),
//                        'F5 - Si' => array('id' => $this->nameForm . '_NonProtocollare',
//                            'model' => $this->nameForm, 'shortCut' => "f5")
//                            ), 'auto', 'auto', 'false'
//                    );
                } else {
//                    Out::msgQuestion("Protocollazione Arrivo.", "<br><br><br>Vuoi Inserire la Comunicazione in Arrivo?", array(
//                        'F8 - No' => array('id' => $this->nameForm . '_NonProtocollare',
//                            'model' => $this->nameForm, 'shortCut' => "f8"),
//                        'F5 - Si' => array('id' => $this->nameForm . '_ProtocollaArrivo',
//                            'model' => $this->nameForm, 'shortCut' => "f5")
//                            ), 'auto', 'auto', 'false'
//                    );


                    $msgOrig = $this->GetMittenteOriginale();
                    if ($this->elemento) {
                        $elemento = $this->elemento;
                        $protocolla['MITTENTE'] = $msgOrig['address'];
                        $protocolla['NOTE'] = $msgOrig['subject'];
                        if (!$msgOrig) {
                            $protocolla['MITTENTE'] = $elemento['FROMADDR'];
                            $protocolla['NOTE'] = $elemento['SUBJECT'];
                        }
                        $protocolla['DATA'] = date("Ymd", strtotime($elemento['MSGDATE']));
                    } else {
                        $elemento = $this->dettagliFile[$this->elementoLocale];
                        $protocolla['MITTENTE'] = $msgOrig['address'];
                        $protocolla['NOTE'] = $msgOrig['subject'];
                        if (!$msgOrig) {
                            $protocolla['MITTENTE'] = $elemento['MITTENTE'];
                            $protocolla['NOTE'] = $elemento['OGGETTO'];
                        }
                        $protocolla['DATA'] = date("Ymd", strtotime($elemento['DATA']));
                    }

                    $emlFile = $this->currMessage;
                    $messaggioOriginale = $emlFile->getMessaggioOriginaleObj();
                    if ($messaggioOriginale) {
                        $emlFile = $messaggioOriginale;
                    }
                    $allegatiMail = $emlFile->getAttachments();
                    $protocolla['ELENCOALLEGATI'] = $allegatiMail;
                    $percorsoTmp = itaLib::getPrivateUploadPath();
                    if (!@is_dir($percorsoTmp)) {
                        if (!itaLib::createPrivateUploadPath()) {
                            Out::msgStop("Archiviazione File.", "Creazione ambiente di lavoro temporaneo fallita.");
                            break;
                        }
                    }
                    $fileOrig = $this->currMessage->getEmlFile();
                    $randName = md5(rand() * time()) . "." . pathinfo($fileOrig, PATHINFO_EXTENSION);
                    if (!@copy($fileOrig, $percorsoTmp . '/' . $randName)) {
                        Out::msgStop("Archiviazione File.", "Copia della Email temporanea fallita.");
                        break;
                    }
                    if ($this->elemento) {
                        $oggetto = $this->elemento['SUBJECT'];
                        $daMail = array('archivio' => true, 'IDMAIL' => $this->elemento['IDMAIL']);
                    } else {
                        $oggetto = $this->dettagliFile[$this->elementoLocale]['OGGETTO'];
                        $daMail = array('archivio' => false, 'IDMAIL' => pathinfo($this->dettagliFile[$this->elementoLocale]['FILENAME'], PATHINFO_FILENAME));
                    }
                    if ($_POST['id'] == $this->nameForm . '_NonProtocollare') {
                        $fileName = $this->praLib->clearFileName($oggetto);
                        $allegati[] = array(
                            'FILEPATH' => $percorsoTmp . '/' . $randName,
                            'FILENAME' => $randName,
                            'FILEINFO' => 'File Originale: da PEC',
                            'FILEORIG' => $fileName . "." . pathinfo($randName, PATHINFO_EXTENSION)
                        );

                        foreach ($allegatiMail as $alle) {
                            $allegati[] = array(
                                'FILEPATH' => $alle['DataFile'],
                                'FILENAME' => md5(rand() * time()) . "." . pathinfo($alle['FileName'], PATHINFO_EXTENSION),
                                'FILEINFO' => 'File Originale:' . $alle['FileName'],
                                'FILEORIG' => $alle['FileName']
                            );
                        }
                    } else {
                        $protocolla['FILENAME'] = $percorsoTmp . '/' . $randName;
                    }
                    $daMail['protocolla'] = $protocolla;
                    $datiPost = $this->datiAppoggio['formData'];
                    $gesnum = $datiPost['PROGES_REC']['GESNUM'];
                    $datiInfo = "Caricamento Email per la pratica " . (int) substr($gesnum, 4) . "/" . substr($gesnum, 0, 4);
                    if ($this->datiAppoggio['post']['retKey'] == '') {
                        $datiInfo .= '<br>Inserire i dati del passo prima di Aggiungere.';
                        $model = 'praPasso';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['procedimento'] = $gesnum;
                        $_POST['modo'] = "add";
                        $_POST['listaAllegati'] = $allegati;
                        $_POST['datiInfo'] = $datiInfo;
                        $_POST['perms'] = $this->perms;
                        $_POST[$model . '_daMail'] = $daMail;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnPraPasso';
                        $_POST[$model . '_title'] = 'Gestione Passo proveniente dalla pratica: ' . (int) substr($gesnum, 4) . "/" . substr($gesnum, 0, 4);
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    } else {
                        $model = 'praPasso';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $this->datiAppoggio['post']['retKey'];
                        $_POST['modo'] = "edit";
                        $_POST['listaAllegati'] = $allegati;
                        $_POST['datiInfo'] = $datiInfo;
                        $_POST['perms'] = $this->perms;
                        $_POST[$model . '_daMail'] = $daMail;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnPraPasso';
                        $_POST[$model . '_title'] = 'Gestione Passo proveniente dalla pratica: ' . (int) substr($gesnum, 4) . "/" . substr($gesnum, 0, 4);
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                    }
                }
//                $this->datiAppoggio['formData'] = $this->formData;
//                $this->datiAppoggio['post'] = $_POST;

                break;
            case 'returnPraGest':
                if ($this->refAccounts) {
                    $this->openRicerca(false, false);
//                    if ($_POST[$this->gridElencoMail]['gridParam']['selrow']) {
//                        TableView::setSelection($this->gridElencoMail, $_POST[$this->gridElencoMail]['gridParam']['selrow']);
//                    }
//                    if ($_POST[$this->gridElencoMailScarti]['gridParam']['selrow']) {
//                        TableView::setSelection($this->gridElencoMailScarti, $_POST[$this->gridElencoMailScarti]['gridParam']['selrow']);
//                    }
                    TableView::reload($this->gridElencoMail);
                } else {
                    $this->openRicercaLocale();
                    if ($_POST[$this->gridElencoMailLocale]['gridParam']['selrow']) {
                        TableView::setSelection($this->gridElencoMailLocale, $_POST[$this->gridElencoMailLocale]['gridParam']['selrow']);
                    }
                }
                break;
            case 'returnUnires':
                $praMail_rec = $this->praLib->getPraMail($this->elemento['IDMAIL']);
                $ananom_rec = $this->praLib->GetAnanom($_POST['retKey'], 'rowid');
                $praMail_rec['ASSRES'] = $ananom_rec['NOMRES'];
                $update_Info = 'Oggetto: Assegna mail a nominativo, id:' . $praMail_rec['IDMAIL'] . " cominativo:" . $ananom_rec['NOMRES'];
                if (!$this->updateRecord($this->PRAM_DB, 'PRAMAIL', $praMail_rec, $update_Info)) {
                    Out::msgStop("Attenzione!!", "Errore aggiornamento dati assegnazione mail.");
                    break;
                }
                $_POST['rowid'] = $this->elemento['ROWID'];
                $this->dettaglio();
                break;
            case 'returnPraPasso':
                if ($_POST['DATIPASSO']) {
                    $gesnum = $_POST['DATIPASSO']['GESNUM'];
                    $propas_rec = $this->praLib->GetPropas($_POST['DATIPASSO']['PROPAK'], 'propak');
                    $this->datiAppoggio = $_POST['DATIPASSO'];

                    if ($this->refAccounts) {
                        $this->openRicerca(false, false);
//                        if ($_POST[$this->gridElencoMail]['gridParam']['selrow']) {
//                            TableView::setSelection($this->gridElencoMail, $_POST[$this->gridElencoMail]['gridParam']['selrow']);
//                        }
//                        if ($_POST[$this->gridElencoMailScarti]['gridParam']['selrow']) {
//                            TableView::setSelection($this->gridElencoMailScarti, $_POST[$this->gridElencoMailScarti]['gridParam']['selrow']);
//                        }
                        TableView::reload($this->gridElencoMail);
                    } else {
                        $this->openRicercaLocale();
                        if ($_POST[$this->gridElencoMailLocale]['gridParam']['selrow']) {
                            TableView::setSelection($this->gridElencoMailLocale, $_POST[$this->gridElencoMailLocale]['gridParam']['selrow']);
                        }
                    }
                    $Serie_rec = $this->praLib->ElaboraProgesSerie($gesnum);
                    Out::msgQuestion("Passo n° " . $propas_rec['PROSEQ'] . " della Pratica n° " . $Serie_rec
                            , "<br>E' stata allegata la Email al passo " . $propas_rec['PROSEQ'] . " della Pratica n° <br>"
                            . $Serie_rec .
                            ".<br>Vuoi visualizzare la Pratica o caricare altre Email?", array(
                        'F8-Carica Altra Email' => array('id' => $this->nameForm . '_CaricaEmail',
                            'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Visualizza Pratica' => array('id' => $this->nameForm . '_VisualizzaPratica',
                            'model' => $this->nameForm, 'shortCut' => "f5")
                            ), 'auto', 'auto', 'false'
                    );
                }
                break;
            case 'returnCaricaMailGenerica':
                if ($_POST['carica'] === true) {
                    $model = $this->returnModel;
                    $_POST['datiMail'] = $_POST['datiMail']['Dati'];
                    itaLib::openForm($model);
                    $objModel = itaModel::getInstance($this->returnModel);
                    $objModel->setEvent($this->returnEvent);
                    $objModel->parseEvent();
                    if ($this->refAccounts) {
                        $this->openRicerca(false, false);
//                        $this->openRicerca(true, true);
//                        if ($_POST[$this->gridElencoMail]['gridParam']['selrow']) {
//                            TableView::setSelection($this->gridElencoMail, $_POST[$this->gridElencoMail]['gridParam']['selrow']);
//                        }
//                        if ($_POST[$this->gridElencoMailScarti]['gridParam']['selrow']) {
//                            TableView::setSelection($this->gridElencoMailScarti, $_POST[$this->gridElencoMailScarti]['gridParam']['selrow']);
//                        }
                        TableView::reload($this->gridElencoMail);
                    } else {
                        $this->openRicercaLocale();
                        if ($_POST[$this->gridElencoMailLocale]['gridParam']['selrow']) {
                            TableView::setSelection($this->gridElencoMailLocale, $_POST[$this->gridElencoMailLocale]['gridParam']['selrow']);
                        }
                    }
                }
                break;
            case "returnMail":
                //
                // Analizzo il messaggio origilnale da inoltrare
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

                /*
                 * Seleziono l'account
                 */
                $accountSMTP = $this->refAccounts[0]['EMAIL'];
                if ($_POST['valori']['ForzaDaMail']) {
                    $accountSMTP = $_POST['valori']['ForzaDaMail'];
                }
                /*
                 * Preparo il messaggio in uscita
                 */
                /* @var $emlMailBox emlMailBox */
                $emlMailBox = emlMailBox::getInstance($accountSMTP);
                if (!$emlMailBox) {
                    Out::msgStop('Inoltro Mail', "Impossibile accedere alle funzioni dell'account: $accountSMTP");
                    break;
                }
                if ($this->praLib->getRicevutaPECBreve()) {
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
                    Out::msgInfo('Inoltro Mail', "E-Mail inviata con successo a <b>" . $_POST['valori']['Email'] . "</b>");
                } else {
                    Out::msgStop('Inoltro Mail', $emlMailBox->getLastMessage());
                }

                break;
            case "returnMultiselectGeneric":
                switch ($_POST['retid']) {
                    case 'DaScartare':
                        if ($_POST['retKey'] != '') {
                            $chiavi = explode(",", $_POST['retKey']);
                            foreach ($chiavi as $chiave) {
                                $this->scartaMail($chiave);
                            }
                            //$this->openRicerca();
                            TableView::reload($this->gridElencoMail);
                        }
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        $this->clearCurrMessage();
        if ($this->currMailBox != null) {
            $this->currMailBox->close();
            $this->currMailBox = null;
        }
        App::$utente->removeKey($this->nameForm . '_certificato');
        App::$utente->removeKey($this->nameForm . '_elencoAllegati');
        App::$utente->removeKey($this->nameForm . '_elencoAllegatiOrig');
        App::$utente->removeKey($this->nameForm . '_elemento');
        App::$utente->removeKey($this->nameForm . '_currMessage');
        App::$utente->removeKey($this->nameForm . '_currMailBox');
        App::$utente->removeKey($this->nameForm . '_dettagliFile');
        App::$utente->removeKey($this->nameForm . '_elementoLocale');
        App::$utente->removeKey($this->nameForm . '_currAlert');
        App::$utente->removeKey($this->nameForm . '_refAccounts');
        App::$utente->removeKey($this->nameForm . '_Proric_rec');
        App::$utente->removeKey($this->nameForm . '_visibilita');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_datiAppoggio');
        App::$utente->removeKey($this->nameForm . '_elencoMail');
        App::$utente->removeKey($this->nameForm . '_numProt');
        App::$utente->removeKey($this->nameForm . '_tabAttiva');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    private function Nascondi() {
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_AssegnaPasso');
        Out::hide($this->nameForm . '_Ricevi');
        Out::hide($this->nameForm . '_ScartaMulti');
        Out::hide($this->nameForm . '_Carica');
        Out::hide($this->nameForm . '_CertificatoV');
        Out::hide($this->nameForm . '_CertificatoNV');
        Out::hide($this->nameForm . '_Scarta');
        Out::hide($this->nameForm . '_Ripristina');
        Out::hide($this->nameForm . '_divButCert');
        Out::hide($this->nameForm . '_divButPec');
        Out::hide($this->nameForm . '_DatiPec');
        Out::hide($this->nameForm . '_ConfermaVisione');
        Out::hide($this->nameForm . '_Inoltra');
        Out::hide($this->nameForm . '_Dadata_field');
        Out::hide($this->nameForm . '_Adata_field');
        Out::hide($this->nameForm . '_RicercaScarti');
        Out::hide($this->nameForm . '_SalvaMailLocale');
        Out::hide($this->nameForm . '_AssegnaMail');
        Out::hide($this->nameForm . '_EliminaAssegnazione');
    }

    private function Azzera($valData = true, $clearToolbar = true) {
        $this->clearCurrMessage();
        if ($this->currMailBox != null) {
            $this->currMailBox->close();
            $this->currMailBox = null;
        }
        $this->elencoAllegati = array();
        $this->elencoAllegatiOrig = array();
        $this->certificato = array();
        $this->elementoLocale = null;
        $this->elemento = null;
        TableView::clearGrid($this->gridElencoMailScarti);
        if ($clearToolbar) {
//    TableView::clearToolbar($this->gridElencoMailScarti);
        }
    }

    /**
     *
     * @param type $locale
     * @param type $caricaTabelle
     */
    private function openRicerca($locale = true, $caricaTabelle = true) {
        Out::unBlock($this->divRis);
        $this->currAlert = "";
        $this->Azzera();
        if ($caricaTabelle) {
            $this->caricaTabella();
            if ($locale) {
                $this->caricaTabEmailLocali();
            }
        }
        Out::html($this->nameForm . "_divSoggetto", '');
        $this->Nascondi();
        Out::show($this->nameForm);
        Out::show($this->divRis);
        Out::hide($this->nameForm . '_divDettaglio');
        Out::hide($this->nameForm . '_divInfoMail');
        Out::show($this->nameForm . '_Ricevi');

        Out::show($this->nameForm . '_ScartaMulti');
        Out::show($this->nameForm . "_Filtra");
        Out::show($this->nameForm . "_Salva");
        Out::show($this->nameForm . "_divCampiSearch");
        Out::hide($this->nameForm . "_FiltraScarti");
        Out::hide($this->nameForm . '_divCampiSearchScarti');
        /*
         * Controlli su profilo per vidibilta e gestione completa mail
         */
        $ananom_rec = $this->praLib->GetAnanom($this->profilo['COD_ANANOM']);
        if ($ananom_rec) {
            if ($ananom_rec['NOMPRIVMAIL']) {
                Out::hide($this->nameForm . '_Ricevi');
                Out::hide($this->nameForm . '_ScartaMulti');
            }
        }

        $this->currAlert = $this->getAlert();
        $this->refreshAlert();
    }

    private function openRicercaScarti() {
        Out::unBlock($this->divRis);
        $this->currAlert = "";
        $this->Azzera();
        Out::html($this->nameForm . "_divSoggetto", '');
        $this->Nascondi();
        Out::show($this->nameForm);
        Out::show($this->divRis);
        Out::hide($this->nameForm . '_divDettaglio');
        Out::hide($this->nameForm . '_divInfoMail');
        Out::show($this->nameForm . '_Ricevi');
        Out::show($this->nameForm . '_ScartaMulti');
        Out::hide($this->nameForm . "_Filtra");
        Out::hide($this->nameForm . "_Salva");
        Out::hide($this->nameForm . '_divCampiSearch');
        Out::show($this->nameForm . "_FiltraScarti");
        Out::show($this->nameForm . '_divCampiSearchScarti');
        $ananom_rec = $this->praLib->GetAnanom($this->profilo['COD_ANANOM']);
        if ($ananom_rec) {
            if ($ananom_rec['NOMPRIVMAIL']) {
                Out::hide($this->nameForm . '_Ricevi');
                Out::hide($this->nameForm . '_ScartaMulti');
            }
        }

        //
        $this->currAlert = $this->getAlert();
        $this->refreshAlert();
    }

    private function openRicercaLocale() {
        Out::unBlock($this->divRis);
        $this->Azzera(false, false);
        $this->Nascondi();
        Out::show($this->nameForm);
        Out::show($this->divRis);
        Out::hide($this->nameForm . '_divDettaglio');
        Out::hide($this->nameForm . '_divInfoMail');
        Out::hide($this->nameForm . '_divFilters');
        Out::tabSelect($this->nameForm . "_tabMail", $this->nameForm . "_paneLocale");
        $this->caricaTabEmailLocali();
        $this->currAlert = "Email da Gestire: " . count($this->dettagliFile);
        $this->refreshAlert();
    }

    private function syncPramailFromLocal($elencoFile) {
        foreach ($elencoFile as $mail) {
            $search_id = pathinfo($mail['FILENAME'], PATHINFO_FILENAME);
            $sql = "SELECT IDMAIL FROM PRAMAIL WHERE TIPOMAIL='KEYUPL' AND IDMAIL = '$search_id'";
            $Pramail_rec = itaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($Pramail_rec) {
                continue;
            }
            $message = new emlMessage();
            $message->setEmlFile($mail['FILENAME']);
            $message->parseEmlFileDeep();
            $idMail = pathinfo($message->getEmlFile(), PATHINFO_FILENAME);
            $praMail_rec = $this->praLib->getPraMail($idMail);
            if (!$praMail_rec) {
                $praMessage = new praMessage();
                $praMail_rec = $praMessage->savePramailRecord($message, $idMail, 'KEYUPL');
                $message->cleanData();
            }
        }
    }

    private function syncPramail($tipo) {
        $sql = "
            SELECT
                MAIL_ARCHIVIO.ROWID AS ROWID,
                MAIL_ARCHIVIO.IDMAIL AS IDMAIL,
                MAIL_ARCHIVIO.ACCOUNT AS ACCOUNT,
                MAIL_ARCHIVIO.CLASS AS CLASS,
                MAIL_ARCHIVIO.PECTIPO AS PECTIPO,
                PRAMAIL.IDMAIL AS PIDMAIL
            FROM
                " . $this->ITALWEB->getDB() . ".MAIL_ARCHIVIO AS MAIL_ARCHIVIO
            LEFT OUTER JOIN
                " . $this->PRAM_DB->getDB() . ".PRAMAIL AS PRAMAIL
            ON
                MAIL_ARCHIVIO.ROWID = PRAMAIL.ROWIDARCHIVIO
            WHERE
                (PRAMAIL.ROWIDARCHIVIO IS NULL) AND CLASS ='$tipo' AND ACCOUNT='" . $this->refAccounts[0]['EMAIL'] . "'";

        $SyncTab = itaDB::DBSQLSelect($this->ITALWEB, $sql, true);
        $mailBox = new emlMailBox();
        foreach ($SyncTab as $SyncRec) {
            $message = $mailBox->getMessageFromDb($SyncRec['ROWID']);
            $message->parseEmlFileDeep();
            $praMessage = new praMessage();
            $praMessage->savePramailRecord($message, $SyncRec['IDMAIL'], 'KEYMAIL', $SyncRec['ROWID']);
            $message->cleanData();
        }
        return $SyncTab;
    }

    private function caricaTabella($pager = false) {
        $this->elencoMail = array();
        $whereFiltri = $this->getWhereFiltri();
        if ($whereFiltri === false) {
            return false;
        }
        $this->elencoMail = $this->caricaElencoMail('@SPORTELLO_DA_CONTROLLARE@', $whereFiltri);
        if ($pager) {
            $this->CaricaGriglia($this->gridElencoMail, $this->elencoMail, 2, 14);
        } else {
            $this->CaricaGriglia($this->gridElencoMail, $this->elencoMail, 1, 14);
        }
        return $arrayNumRicevute;
    }

    private function GetMsgSync($SyncTab) {
        $nAcc = $nCons = 0;
        foreach ($SyncTab as $SyncRec) {
            $errUpd = false;
            if ($SyncRec['PECTIPO'] == "accettazione" || $SyncRec['PECTIPO'] == "avvenuta-consegna") {
                $praMail_rec = $this->praLib->getPraMail($SyncRec['IDMAIL']);
                if ($praMail_rec['COMPAK']) {
                    if ($SyncRec['PECTIPO'] == "accettazione") {
                        $nAcc += 1;
                    } else if ($SyncRec['PECTIPO'] == "avvenuta-consegna") {
                        $nCons += 1;
                    }

                    /*
                     * Marco la ricevuta come caricata su PRAMAIL e valorizzo anche COMIDMAIL (id mail padre)
                     */
                    if (!$this->praLib->setClasseCaricatoPasso($praMail_rec, $SyncRec['IDMAIL'])) {
                        $errUpd = true;
                        break;
                    }
                }
            }
        }
        if ($errUpd == true) {
            return false;
        } else {
            return array("numAccettazioni" => $nAcc, "numConsegne" => $nCons);
        }
    }

    private function caricaTabellaScarti($pager = false) {
        $this->syncPramail('@SPORTELLO_SCARTATO@');
        $whereFiltri = $this->getWhereFiltriScarti();
        if ($_POST['PRESALLEGATI'] != '') {
            $whereFiltri .= " AND ATTACHMENTS<>''";
        }
        if ($_POST['FROMADDR'] != '') {
            $whereFiltri .= " AND " . $this->PRAM_DB->strUpper('FROMADDR') . " LIKE '%" . strtoupper($_POST['FROMADDR']) . "%'";
        }
        if ($_POST['PEC'] != '') {
            $whereFiltri .= " AND " . $this->PRAM_DB->strUpper('PECTIPO') . " LIKE '%" . strtoupper($_POST['PEC']) . "%'";
        }
        if ($_POST['SUBJECT'] != '') {
            $whereFiltri .= " AND " . $this->PRAM_DB->strUpper('SUBJECT') . " LIKE '%" . strtoupper($_POST['SUBJECT']) . "%'";
        }
        if ($_POST['DATA'] != '') {
            $whereFiltri .= " AND " . $this->PRAM_DB->strUpper('MSGDATE') . " LIKE '" . strtoupper($_POST['DATA']) . "%'";
        }

        $elencoMailScarti = $this->caricaElencoMail('@SPORTELLO_SCARTATO@', $whereFiltri);
        if ($pager) {
            $this->CaricaGriglia($this->gridElencoMailScarti, $elencoMailScarti, 2, 14);
        } else {
            $this->CaricaGriglia($this->gridElencoMailScarti, $elencoMailScarti, 1, 14);
        }
    }

    private function caricaElencoMail($tipo, $whereFiltri) {
        $where_profilo = '';
        $ananom_rec = $this->praLib->GetAnanom($this->profilo['COD_ANANOM']);
        if ($ananom_rec) {
            if ($ananom_rec['NOMPRIVMAIL']) {
                $where_profilo = " AND PRAMAIL.ASSRES='{$ananom_rec['NOMRES']}' ";
            }
        }

        $elencoMail = array();
        $ordinamento = $_POST['sidx'];
        if ($_POST['sidx'] == 'PRESALLEGATI' || $_POST['sidx'] == 'SEGNATURA') {
            $ordinamento = 'ATTACHMENTS';
        }
        if ($_POST['sidx'] == 'PEC') {
            $ordinamento = "MAIL_ARCHIVIO.PECTIPO";
        }
        if ($ordinamento == '' || $_POST['sidx'] == 'DATA' || $_POST['sidx'] == 'ORA' || $_POST['sidx'] == 'RIFERIMENTO') {
            $ordinamento = "MAIL_ARCHIVIO.MSGDATE";
        }
        switch ($_POST['sord']) {
            case 'asc':
                $sord = 'ASC';
                break;
            case 'desc':
            case '':
            default:
                $sord = 'DESC';
                break;
        }

        //$sql = "SELECT * FROM MAIL_ARCHIVIO WHERE CLASS ='$tipo' AND ACCOUNT='" . $this->refAccounts[0]['EMAIL'] . "' " . $whereFiltri . " ORDER BY $ordinamento $sord";
        $sql = "SELECT
                    MAIL_ARCHIVIO.*
                FROM " .
                $this->ITALWEB->getDB() . ".MAIL_ARCHIVIO
                LEFT OUTER JOIN " .
                $this->PRAM_DB->getDB() . ".PRAMAIL
                ON " .
                $this->ITALWEB->getDB() . ".MAIL_ARCHIVIO.IDMAIL = " . $this->PRAM_DB->getDB() . ".PRAMAIL.IDMAIL
                WHERE 
                    MAIL_ARCHIVIO.CLASS = '$tipo' 
                $where_profilo        
                $whereFiltri
                ORDER BY 
                $ordinamento $sord";
        $mailArchivio_tab = $this->emlLib->getGenericTab($sql);
        foreach ($mailArchivio_tab as $mailArchivio_rec) {
            $praMail_rec = $this->praLib->getPraMail($mailArchivio_rec['ROWID'], 'rowidarchivio');
            if ($praMail_rec) {
                //if ($this->visibilita['SPORTELLO'] != 0 || $this->visibilita['AGGREGATO'] != 0) {
                if (count($this->visibilita['SPORTELLI']) != 0 || $this->visibilita['AGGREGATO'] != 0) {
                    if ($praMail_rec['RICNUM']) {
                        $sqlProric = "SELECT
                                        PRORIC.*," .
                                $this->PRAM_DB->strConcat("RICTSP", "'/'", "RICSPA") . " AS TSP_SPA
                                    FROM
                                        PRORIC
                                    WHERE
                                        RICNUM='" . $praMail_rec['RICNUM'] . "'";
                        /*
                         * 
                         * Vecchio
                         * 
                         */
//                        if ($this->visibilita['SPORTELLO'] != 0) {
//                            $sqlProric.=" AND RICTSP='" . $this->visibilita['SPORTELLO'] . "'";
//                        }
//                        if ($this->visibilita['AGGREGATO'] != 0) {
//                            $sqlProric.=" AND RICSPA='" . $this->visibilita['AGGREGATO'] . "'";
//                        }
                        /*
                         * 
                         * Nuovo
                         * 
                         */
                        $sqlProric .= $this->praLib->GetWhereVisibilitaSportelloFO();
                        $proric_rec = $this->praLib->getGenericTab($sqlProric);
                        if (!$proric_rec) {
                            continue;
                        }
                    }
                    if ($praMail_rec['GESNUM']) {
                        $sqlProges = "SELECT
                                         PROGES.*," .
                                $this->PRAM_DB->strConcat("GESTSP", "'/'", "GESSPA") . " AS TSP_SPA
                                      FROM
                                        PROGES
                                     WHERE
                                        GESNUM='" . $praMail_rec['GESNUM'] . "'";
                        /*
                         * 
                         * Vecchio
                         * 
                         */
//                        if ($this->visibilita['SPORTELLO'] != 0) {
//                            $sqlProges.=" AND GESTSP='" . $this->visibilita['SPORTELLO'] . "'";
//                        }
//                        if ($this->visibilita['AGGREGATO'] != 0) {
//                            $sqlProges.=" AND GESSPA='" . $this->visibilita['AGGREGATO'] . "'";
//                        }
                        /*
                         * 
                         * Nuovo
                         * 
                         */
                        $sqlProges .= $this->praLib->GetWhereVisibilitaSportello();
                        $proges_rec = $this->praLib->getGenericTab($sqlProges);
                        if (!$proges_rec) {
                            continue;
                        }
                    }
                }
            }
            $mailArchivio_rec = $this->PreparaRigaGriglia($mailArchivio_rec);
            $elencoMail[$mailArchivio_rec['ROWID']] = $mailArchivio_rec;
        }
        return $elencoMail;
    }

    private function PreparaRigaGriglia($mailArchivio_rec) {

        $metadata = unserialize($mailArchivio_rec["METADATA"]);
        $icon_mail = "<div title = \"Messaggio letto.\" class=\"ita-icon ita-icon-apertagray-24x24\" style = \"margin-right:2px;display:inline-block;\"></div>";
        if ($mailArchivio_rec['READED'] == 0) {
            $icon_mail = "<div title = \"Messaggio da leggere.\" class=\"ita-tooltip ita-icon ita-icon-chiusagray-24x24\" style = \"display:inline-block;\"></div>";
        }
        if ($mailArchivio_rec['INTEROPERABILE'] > 0) {
            $mailArchivio_rec['SEGNATURA'] = '<span title ="Interoperabilità" class="ita-icon ita-icon-flag-it-24x24"></span>';
        }
        $mailArchivio_rec["DATA"] = date('d/m/Y', strtotime(substr($mailArchivio_rec['MSGDATE'], 0, 8)));
        $mailArchivio_rec["ORA"] = substr($mailArchivio_rec['MSGDATE'], 8);
        $pec = $mailArchivio_rec['PECTIPO'];
        if ($pec != '') {
            $icon_mail = "<div title = \"PEC " . $pec . ", letto.\" class=\"ita-tooltip ita-icon ita-icon-apertagreen-24x24\" style = \"margin-right:2px;display:inline-block;\"></div>";
            if ($mailArchivio_rec['READED'] == 0) {
                $icon_mail = "<div title = \"PEC " . $pec . ", da leggere.\" class=\"ita-tooltip ita-icon ita-icon-chiusagreen-24x24\" style = \"margin-right:2px;display:inline-block;\"></div>";
            }
            $mailArchivio_rec["PEC"] = $pec;

            if ($metadata['emlStruct']['ita_PEC_info'] != "N/A") {
                if ($metadata['emlStruct']['ita_PEC_info']['dati_certificazione']) {
                    $mailArchivio_rec['FROMADDRORIG'] = '<p style="background:lightgreen;color: darkgreen;">' . $metadata['emlStruct']['ita_PEC_info']['dati_certificazione']['mittente'] . '</p>';
                    $mailArchivio_rec['SUBJECT'] = $metadata['emlStruct']['ita_PEC_info']['dati_certificazione']['oggetto'];
                }
            }
        }
        $mailArchivio_rec['PRESALLEGATI'] = $icon_mail;
        if ($mailArchivio_rec['ATTACHMENTS'] != '') {
            $mailArchivio_rec['PRESALLEGATI'] .= "<div title = \"Presenza Allegati\" class=\"ita-tooltip ita-icon ita-icon-clip-16x16\" style = \"margin:2px;display:inline-block;\"></div>";
        }

        $_POST['presente'] = '';
        $mailArchivio_rec = $this->elaboraRecordGriglia($mailArchivio_rec, 'KEYMAIL');
        $ini_tag = "<p style = 'font-weight:lighter;" . $_POST['presente'] . "'>";
        $fin_tag = "</p>";
        if ($mailArchivio_rec['READED'] == 0) {
            $ini_tag = "<p style = 'font-weight:900;" . $_POST['presente'] . "'>";
            $fin_tag = "</p>";
        }
        $mailArchivio_rec['PRESALLEGATI'] = '<div class="ita-html">' . $mailArchivio_rec['PRESALLEGATI'] . '</div>';
        $mailArchivio_rec['RIFERIMENTO'] = $ini_tag . $mailArchivio_rec['RIFERIMENTO'] . $fin_tag;
        $mailArchivio_rec['FROMADDR'] = $ini_tag . $mailArchivio_rec['FROMADDR'] . $fin_tag;
        $mailArchivio_rec['SUBJECT'] = $ini_tag . $mailArchivio_rec['SUBJECT'] . $fin_tag;
        $mailArchivio_rec['ACCOUNT'] = $ini_tag . $mailArchivio_rec['ACCOUNT'] . $fin_tag;
        $mailArchivio_rec['DATA'] = $ini_tag . $mailArchivio_rec['DATA'] . $fin_tag;
        $mailArchivio_rec['ORA'] = $ini_tag . $mailArchivio_rec['ORA'] . $fin_tag;
        $mailArchivio_rec['PEC'] = $ini_tag . $mailArchivio_rec['PEC'] . $fin_tag;
        return $mailArchivio_rec;
    }

    private function setRefAccounts($accounts = array()) {
        $this->refAccounts = null;
        if ($accounts) {
            $this->refAccounts = $accounts;
            return true;
        }
        if ($this->visibilita['AGGREGATO']) {
            $Anaspa_rec = $this->praLib->GetAnaspa($this->visibilita['AGGREGATO'], 'codice');
            $Mail_account = $this->emlLib->getMailAccount($Anaspa_rec['SPAPEC']);
            if ($Mail_account['MAILADDR']) {
                $this->refAccounts = array(array("EMAIL" => $Mail_account['MAILADDR']));
                $titolo = "Elenco Mail - " . $this->visibilita['AGGREGATO_DESC'] . " - " . $this->refAccounts[0]['EMAIL'];
                Out::setAppTitle($this->nameForm, "<span>$titolo</span>");
                TableView::enableEvents($this->gridElencoMail);
                return true;
            }
//        } else if ($this->visibilita['SPORTELLO']) {
            /*
             * 
             * Vechhio
             */
//            $Anatsp_rec = $this->praLib->GetAnatsp($this->visibilita['SPORTELLO'], 'codice');
//            $Mail_account = $this->emlLib->getMailAccount($Anatsp_rec['TSPPEC']);
//            if ($Mail_account['MAILADDR']) {
//                $this->refAccounts = array(array("EMAIL" => $Mail_account['MAILADDR']));
//                $titolo = "Elenco Mail - " . $this->visibilita['SPORTELLO_DESC'] . " - " . $this->refAccounts[0]['EMAIL'];
//                Out::setAppTitle($this->nameForm, "<span>$titolo</span>");
//                TableView::enableEvents($this->gridElencoMail);
//                return true;
//            }
        } else if (count($this->visibilita['SPORTELLI']) != 0) {

            /*
             * 
             * Nuovo
             */
            $descEmail = "";
            foreach ($this->visibilita['SPORTELLI'] as $sportello) {
                if (strpos($sportello, '/') !== false) {
                    $arrSportello = explode("/", $sportello);
                    $sportello = $arrSportello[0];
                }
                $Anatsp_rec = $this->praLib->GetAnatsp($sportello, 'codice');
                $Mail_account = $this->emlLib->getMailAccount($Anatsp_rec['TSPPEC']);
                if ($Mail_account['MAILADDR']) {
                    $this->refAccounts[] = array("EMAIL" => $Mail_account['MAILADDR']);
                    $descEmail .= $Mail_account['MAILADDR'] . "|";
                }
            }
            $titolo = "Elenco Mail - " . $this->visibilita['SPORTELLO_DESC'] . " - " . $descEmail;
            Out::setAppTitle($this->nameForm, "<span>$titolo</span>");
            TableView::enableEvents($this->gridElencoMail);
            return true;
        } else {
            $this->Azzera();
            Out::show($this->nameForm);
            $this->Nascondi();
            $this->currAlert = "SELEZIONARE UN ACCOUNT VALIDO.";
            $this->refreshAlert();
            Out::show($this->divRis);
            Out::block($this->divRis);
            Out::hide($this->nameForm . '_divDettaglio');
            Out::hide($this->nameForm . '_divInfoMail');
            Out::tabSelect($this->nameForm . "_tabMail", $this->nameForm . "_paneElenco");
            praRic::ricAccountSportelli($this->nameForm);
            return true;
        }
        return false;
    }

    private function scaricaPosta() {
        $htmlLog = "";
        $htmlErr = "";

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

        foreach ($this->refAccounts as $value) {
            $emlMailbox = new emlMailBox($value['EMAIL']);
            $retSync = $emlMailbox->syncronizeAccount('@SPORTELLO_DA_CONTROLLARE@');
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
        $this->getAlert();
        $this->refreshAlert();
        //TODO@ TEST COMPLETO DELLE VARIE SITUAZIONI IN SEDE
        $SyncTab = $this->syncPramail('@SPORTELLO_DA_CONTROLLARE@');
        if ($SyncTab) {
            $retLogTabella = $this->GetMsgSync($SyncTab);
            if ($retLogTabella == false) {
                Out::msgStop("Attenzione!!!!!", "Errore nel caricamento automatico delle ricevute di consegna.");
            } else {

                $msg = "";
                if ($retLogTabella['numAccettazioni'] != 0) {
                    $msg .= "Assegnate automaticamente " . $retLogTabella['numAccettazioni'] . " accettazioni per invio comunicazioni in partenza.<br>";
                }
                if ($retLogTabella['numConsegne'] != 0) {
                    $msg .= "Assegnate automaticamente " . $retLogTabella['numConsegne'] . " consegne per invio comunicazioni in partenza.";
                }
                $htmlLog .= "<div style=\"overflow:auto; max-height:400px; max-width:600px;;margin:6px;padding:6px;\" class=\"ita-box ui-state-highlight ui-corner-all ita-Wordwrap\">";
                $htmlLog .= "$msg<br>";
                $htmlLog .= "</div>";
            }
        }

        /*
         * Protocollo ricevute non protocollate
         */
        $filent_rec = $this->praLib->GetFilent(28);
        if ($filent_rec['FILDE1'] == 1) {
            include_once ITA_BASE_PATH . '/apps/Pratiche/praSyncRicevute.class.php';
            $praSyncRicevute = new praSyncRicevute();
            $retStatus = $praSyncRicevute->SyncRicevute();
            $htmlLog .= "<div style=\"overflow:auto; max-height:400px; max-width:600px;;margin:6px;padding:6px;\" class=\"ita-box ui-state-highlight ui-corner-all ita-Wordwrap\">";
            $htmlLog .= $this->praLib->GetHtmlLogRicevutePasso($retStatus);
            $htmlLog .= "</div>";
            //file_put_contents("/users/pc/dos2ux/Andrea/logProtRicDaMail.log", print_r($retStatus, true));
        }
        //

        Out::msgDialog("Ricezione messaggi", $htmlErr . $htmlLog);
        /*
         * Sblocco Mail Account con i Semafori
         */
        if ($this->envLib->Semaforo('SBLOCCA', $chiave, $procedura, $tipoblocco) === false) {
            Out::msgInfo("Ricezione Messaggi", $this->envLib->getErrMessage());
            // return false;// Deve tornare false?
        }
        $retLogTabella = $this->caricaTabella();
    }

    private function analizzaMail($daScarta = false) {
        $this->Nascondi();
        $this->currMessage->parseEmlFileDeep();
        $retDecode = $this->currMessage->getStruct();
        $praMail_rec = $this->praLib->getPraMail($this->elemento['IDMAIL']);
        $risultato = unserialize($praMail_rec['ANALISIMAIL']);
        $this->elencoAllegati = $this->caricaElencoAllegati($this->currMessage->getAttachments());
        $ita_grid01 = new TableView($this->gridAllegati, array('arrayTable' => $this->elencoAllegati, 'rowIndex' => 'idx'));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10000);
        TableView::enableEvents($this->gridAllegati);
        TableView::clearGrid($this->gridAllegati);
        $ita_grid01->getDataPage('json');
        Out::valore($this->nameForm . '_Mittente', $this->elemento['FROMADDR']);
        Out::valore($this->nameForm . '_Oggetto', $this->elemento['SUBJECT']);
        Out::valore($this->nameForm . '_Data', substr($this->elemento['MSGDATE'], 0, 8));
        Out::valore($this->nameForm . '_Ora', trim(substr($this->elemento['MSGDATE'], 8)));
        $url = utiDownload::getUrl("emlbody.html", $this->currMessage->getEmlBodyDataFile(), false, true);
        $iframe = '<iframe src="' . $url . '" frameborder="0" width="99%" height="95%" id="' . $this->nameForm . '_emlBMFrame">
            <p>Contenuto non visualizzabile.....</p>
            </iframe>';
        Out::html($this->nameForm . '_divSoggetto', $iframe);
//
// Analizza e Mostra la pagina Segnatura
//
        $this->decodSegnaturaCert($retDecode);
        Out::html($this->nameForm . '_divInfoMail', "");
        Out::show($this->nameForm . '_AssegnaPasso');
        Out::show($this->nameForm . '_Scarta');
        Out::show($this->nameForm . '_SalvaMailLocale');
        if ($praMail_rec['ASSRES'] === '') {
            Out::show($this->nameForm . '_AssegnaMail');
        } else {
            Out::show($this->nameForm . '_EliminaAssegnazione');
        }
//
// Analizza le meta-informazioni applicative della mail e le mostra
//
        $this->decodInfoMail($risultato, $praMail_rec);

        $letto = array(
            'ROWID' => $this->elemento['ROWID'],
            'READED' => 1
        );
        $this->elemento['READED'] = 1;

        $update_Info = 'Oggetto: set Email Letta, rowid:' . $letto['ROWID'] . " valore:" . $letto['READED'];
        $this->updateRecord($this->ITALWEB, 'MAIL_ARCHIVIO', $letto, $update_Info);
        if ($daScarta) {
            Out::hide($this->nameForm . '_AssegnaPasso');
            Out::hide($this->nameForm . '_SalvaMailLocale');
            Out::hide($this->nameForm . '_AssegnaMail');
            Out::hide($this->nameForm . '_EliminaAssegnazione');
            Out::hide($this->nameForm . '_Carica');
            Out::hide($this->nameForm . '_Scarta');
            return;
        }
        /*
         * Controlli su stato mail nei dati passo/fascicolo
         * 
         */
        if ($praMail_rec['RICNUM'] && $praMail_rec['ISANNULLAMENTO'] != 1) {
            $proges_rec = $this->praLib->GetProges($praMail_rec['RICNUM'], 'richiesta');
            if ($proges_rec) {
                Out::hide($this->nameForm . '_Carica');
                Out::hide($this->nameForm . '_AssegnaPasso');
//                Out::msgStop("ATTENZIONE!", "Il Procedimento dello Sportello Online n° "
//                        . (int) substr($praMail_rec['RICNUM'], 4) . '/' . substr($praMail_rec['RICNUM'], 0, 4) . " è già stato caricato.<br>
//                        Impossibile Ricaricarlo.");
                $pramail_rec_ctr_mail = $this->praLib->GetPramailRecPratica($proges_rec['GESNUM']);
                if ($pramail_rec_ctr_mail) {
                    Out::msgStop("Archiviazione File", "La Mail per la richiesta n. " . $proges_rec['GESPRA'] . " risulta già caricata alla pratica n. " . $proges_rec['GESNUM']);
                } else {
                    Out::msgQuestion("ATTENZIONE!", "Il Procedimento dello Sportello Online n° "
                            . (int) substr($praMail_rec['RICNUM'], 4) . '/' . substr($praMail_rec['RICNUM'], 0, 4) . " è già stato caricato.<br>
                        Vuoi Importare la Mail nella Pratica?.", array(
                        'Annulla' => array('id' => $this->nameForm . '_AnnullaImportaMailFO', 'model' => $this->nameForm),
                        'Conferma' => array('id' => $this->nameForm . '_ConfermaImportaMailFO', 'model' => $this->nameForm)
                            )
                    );
                }
                return;
            }
        }
        /*
         * Controlli su profilo per vidibilta e gestione completa mail
         */
        $ananom_rec = $this->praLib->GetAnanom($this->profilo['COD_ANANOM']);
        if ($ananom_rec) {
            if ($ananom_rec['NOMPRIVMAIL']) {
                //Out::hide($this->nameForm . '_Scarta');
                Out::hide($this->nameForm . '_Ripristina');
                Out::hide($this->nameForm . '_AssegnaMail');
                Out::hide($this->nameForm . '_EliminaAssegnazione');
                Out::hide($this->nameForm . '_SalvaMailLocale');
            }
        }
    }

    private function GetMittenteOriginale() {
        $retDecode = $this->currMessage->getStruct();
        if ($retDecode['ita_PEC_info'] != "N/A") {
            $messaggioOriginale = $retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile'];
            $msgOrig = array();
            foreach ($messaggioOriginale['From'] as $address) {
                $msgOrig['address'] = $address['address'];
            }
            $msgOrig['subject'] = $messaggioOriginale['Subject'];
            return $msgOrig;
        }
    }

    private function decodSegnaturaCert($retDecode) {
        if (is_array($retDecode['Signature'])) {
            $this->certificato['Signature'] = $retDecode['Signature'];
            Out::show($this->nameForm . '_divButCert');
            if ($retDecode['ita_Signature_info']['Verified'] == 1) {
                Out::show($this->nameForm . '_CertificatoV');
            } else {
                Out::show($this->nameForm . '_CertificatoNV');
            }
        }
        if ($retDecode['ita_PEC_info'] != 'N/A') {
            $this->certificato['ita_PEC_info'] = $retDecode['ita_PEC_info']['dati_certificazione'];
            if (is_array($retDecode['ita_PEC_info']['messaggio_originale'])) {
                $this->caricaDatiEmailOriginale($retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile']);
                Out::tabEnable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneOriginale");
            } else {
                Out::tabDisable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneOriginale");
            }
        } else {
            Out::tabDisable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneOriginale");
            Out::tabDisable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneCertificazione");
        }
        $this->setCertificazione();
    }

    private function caricaDatiEmailOriginale($messaggioOriginale) {
        $objOriginale = new emlMessage();
        $objOriginale->setStruct($messaggioOriginale);
        $this->elencoAllegatiOrig = $this->caricaElencoAllegati($objOriginale->getAttachments());
        $ita_grid01 = new TableView($this->gridAllegatiOrig, array('arrayTable' => $this->elencoAllegatiOrig, 'rowIndex' => 'idx'));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10000);
        TableView::enableEvents($this->gridAllegatiOrig);
        TableView::clearGrid($this->gridAllegatiOrig);
        $ita_grid01->getDataPage('json');
        $addressFrom = '';
        foreach ($messaggioOriginale['From'] as $address) {
            $addressFrom = $address['address'];
        }
        Out::valore($this->nameForm . '_MittenteOrig', $addressFrom);
        Out::valore($this->nameForm . '_OggettoOrig', $messaggioOriginale['Subject']);

//        Out::valore($this->nameForm . '_DataOrig', date('Ymd', strtotime($messaggioOriginale['Date'])));
//        Out::valore($this->nameForm . '_OraOrig', trim(date('H:i:s', strtotime($messaggioOriginale['Date']))));
        list($dataOrig, $skip) = explode("(", $messaggioOriginale['Date'], 2);
        if (!$dataOrig) {
            // Se non c'è la data del mess originale, prendo quella della mail completa
            $elemento = $this->dettagliFile[$this->elementoLocale];
            Out::valore($this->nameForm . '_DataOrig', $elemento['DATA']);
            Out::valore($this->nameForm . '_OraOrig', $elemento['ORA']);
        } else {
            Out::valore($this->nameForm . '_DataOrig', date('Ymd', strtotime($dataOrig)));
            Out::valore($this->nameForm . '_OraOrig', trim(date('H:i:s', strtotime($dataOrig))));
        }

//        $datafile = '';
//        if (isset($messaggioOriginale['DataFile'])) {
//            $datafile = $messaggioOriginale['DataFile'];
//        } else {
//            foreach ($messaggioOriginale['Alternative'] as $value) {
//                $datafile = $value['DataFile'];
//            }
//        }
        $url = utiDownload::getUrl("emlbody.html", $objOriginale->getEmlBodyDataFile(), false, true);
        $iframe = '<iframe src="' . $url . '" frameborder="0" width="99%" height="95%" id="' . $this->nameForm . '_emlOrigFrame">
            <p>Contenuto non visualizzabile.....</p>
            </iframe>';
        Out::html($this->nameForm . '_divSoggettoOrig', $iframe);
    }

    private function scartaMail($rowid, $riabilita = false, $motivo = '') {
        $mailArchivio_rec = $this->emlLib->getMailArchivio($rowid, 'rowid');
        $praMail_rec = $this->praLib->getPraMail($mailArchivio_rec['IDMAIL']);
        if ($praMail_rec) {
            $praMail_rec['MAILSTATO'] = 'SCARTATA';
            $praMail_rec['SCARTOMOTIVO'] = $motivo;
            if ($riabilita === true) {
                $praMail_rec['MAILSTATO'] = 'ATTIVA';
            }
            $update_Info = 'Oggetto: set Scarta/riattiva Mail, id:' . $praMail_rec['IDMAIL'] . " valore:" . $praMail_rec['MAILSTATO'];
            $this->updateRecord($this->PRAM_DB, 'PRAMAIL', $praMail_rec, $update_Info);
        }
        $classe = '@SPORTELLO_SCARTATO@';
        if ($riabilita === true) {
            $classe = '@SPORTELLO_DA_CONTROLLARE@';
        }
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
            $struct_file = pathinfo($mail['FILENAME'], PATHINFO_DIRNAME) . "/" . pathinfo($mail['FILENAME'], PATHINFO_FILENAME) . ".info";
            if (file_exists($struct_file)) {
                $message->setStruct(unserialize(file_get_contents($struct_file)));
            } else {
                $message->setEmlFile($mail['FILENAME']);
                $message->parseEmlFileDeep();
            }
            $retDecode = $message->getStruct();
            $elencoDatiMail['MESSAGE-ID'] = $retDecode['Message-Id'];
            $elencoDatiMail['FILENAME'] = $mail['FILENAME'];
            $allegati = array();
            $elencoDatiMail['ALLEGATI'] = "";
            foreach ($message->getAttachments() as $attach) {
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
            $elencoDatiMail['MITTENTE'] = $retDecode['From'][0]['address'];
            $elencoDatiMail['OGGETTO'] = $retDecode['Subject'];
            if ($retDecode['ita_PEC_info'] != "N/A") {
                if ($retDecode['ita_PEC_info']['dati_certificazione']) {
                    $elencoDatiMail['OGGETTO'] = $retDecode['ita_PEC_info']['dati_certificazione']['oggetto'];
                    $elencoDatiMail['FROMADDRORIG'] = '<p style="background:lightgreen;color: darkgreen;">' . $retDecode['ita_PEC_info']['dati_certificazione']['mittente'] . '</p>';
                }
            }

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
            if ($elencoDatiMail['ALLEGATI'] != '') {
                $elencoDatiMail['PRESALLEGATI'] .= '<span title = "Presenza Allegati" class="ita-icon ita-icon-clip-16x16" style = \"display:inline-block;\"></span>';
            }
            $this->dettagliFile[] = $this->elaboraRecordGriglia($elencoDatiMail, 'KEYUPL');
        }
        $this->dettagliFile = $this->praLib->array_sort($this->dettagliFile, 'DATA', SORT_DESC);
        $ita_grid01 = new TableView($this->gridElencoMailLocale, array('arrayTable' => $this->dettagliFile, 'rowIndex' => 'idx'));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(14);
        TableView::enableEvents($this->gridElencoMailLocale);
        TableView::clearGrid($this->gridElencoMailLocale);
        $ita_grid01->getDataPage('json');
        return count($this->dettagliFile);
    }

    private function GetFileList() {
        if (!$dh = @opendir($this->emailTempPath)) {
            return false;
        }
        $retListGen = array();
        $rowid = 0;
        while (($obj = @readdir($dh))) {
            if ($obj == '.' || $obj == '..') {
                continue;
            }
            if (strtolower(pathinfo($obj, PATHINFO_EXTENSION)) != 'eml') {
                continue;
            }
            $rowid += 1;
            $retListGen[$rowid] = array(
                'ROWID' => $rowid,
                'FILENAME' => $this->emailTempPath . $obj
            );
        }
        closedir($dh);
        return $retListGen;
    }

    private function Dettaglio($daScarta) {
        //
        // Pulisci il messaggio
        //
        $this->clearCurrMessage();
        //
        // Prende currmailbox per leggere currmessage
        //
        $this->currMailBox = new emlMailBox();
        $this->currMessage = $this->currMailBox->getMessageFromDb($_POST['rowid']);
        $this->elencoMail[$_POST['rowid']]['READED'] = 1;
        //
        // Carica Elemento da visualizzare
        //
        $this->elemento = $this->emlLib->getMailArchivio($_POST['rowid'], 'rowid');


        Out::tabSelect($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneMail");


        $this->analizzaMail($daScarta);
        $grid = $this->gridElencoMail;
        if ($this->elemento['CLASS'] == "@SPORTELLO_SCARTATO@") {
            $grid = $this->gridElencoMailScarti;
        }
        $record = $this->PreparaRigaGriglia($this->elemento);
        //TableView::setRowData($grid, $_POST['rowid'], $record);
        Out::hide($this->divRis);
        Out::show($this->nameForm . '_divDettaglio');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '_Inoltra');
    }

    private function DettaglioLocale() {
        //
        // Pulisci il messaggio
        //
        $this->clearCurrMessage();
        //
        // Carica Elemento da visualizzare
        //
        $this->elementoLocale = $_POST['rowid'];
        $elemento = $this->dettagliFile[$this->elementoLocale];
        $this->Nascondi();
        Out::tabSelect($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneMail");


        $this->currMessage = new emlMessage();
        $this->currMessage->setEmlFile($elemento['FILENAME']);
        $this->currMessage->parseEmlFileDeep();
        $retDecode = $this->currMessage->getStruct();

        $praMail_rec = $this->praLib->getPraMail(pathinfo($elemento['FILENAME'], PATHINFO_FILENAME));
        $risultato = unserialize($praMail_rec['ANALISIMAIL']);

        $this->elencoAllegati = $this->caricaElencoAllegati($this->currMessage->getAttachments());
        $ita_grid01 = new TableView($this->gridAllegati, array('arrayTable' => $this->elencoAllegati, 'rowIndex' => 'idx'));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10000);
        TableView::enableEvents($this->gridAllegati);
        TableView::clearGrid($this->gridAllegati);
        $ita_grid01->getDataPage('json');
        Out::valore($this->nameForm . '_Mittente', $elemento['MITTENTE']);
        Out::valore($this->nameForm . '_Oggetto', $elemento['OGGETTO']);
        Out::valore($this->nameForm . '_Data', $elemento['DATA']);
        Out::valore($this->nameForm . '_Ora', trim($elemento['ORA']));
        //$url = utiDownload::getUrl("emlbody.html", $retDecode['DataFile'], false, true);
        $url = utiDownload::getUrl("emlbody.html", $this->currMessage->getEmlBodyDataFile(), false, true);
        $iframe = '<iframe src="' . $url . '" frameborder="0" width="99%" height="95%" id="' . $this->nameForm . '_emlBLFrame">
                     <p>Contenuto non visualizzabile.....</p>
                   </iframe>';
        Out::html($this->nameForm . '_divSoggetto', $iframe);
        Out::hide($this->divRis);
        Out::html($this->nameForm . '_divInfoMail', "");
        Out::hide($this->nameForm . '_divInfoMail');
        Out::show($this->nameForm . '_divDettaglio');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '_Inoltra');
        $this->decodSegnaturaCert($retDecode);
        Out::show($this->nameForm . '_AssegnaPasso');
        $this->decodInfoMail($risultato, $praMail_rec);
        Out::show($this->nameForm . '_Carica');
        if ($praMail_rec['RICNUM'] && $praMail_rec['ISANNULLAMENTO'] != 1) {
            $proges_rec = $this->praLib->GetProges($praMail_rec['RICNUM'], 'richiesta');
            if ($proges_rec) {
                Out::hide($this->nameForm . '_Carica');
                Out::hide($this->nameForm . '_AssegnaPasso');
//                Out::msgStop("ATTENZIONE!", "Il Procedimento dello Sportello Online n° "
//                        . (int) substr($praMail_rec['RICNUM'], 4) . '/' . substr($praMail_rec['RICNUM'], 0, 4) . " è già stato caricato.<br>
//                        Impossibile Ricaricarlo.");

                $pramail_rec_ctr_mail = $this->praLib->GetPramailRecPratica($proges_rec['GESNUM']);
                if ($pramail_rec_ctr_mail) {
                    Out::msgStop("Archiviazione File", "La Mail per la richiesta n. " . $proges_rec['GESPRA'] . " risulta già caricata alla pratica n. " . $proges_rec['GESNUM']);
                } else {
                    Out::msgQuestion("ATTENZIONE!", "Il Procedimento dello Sportello Online n° "
                            . (int) substr($praMail_rec['RICNUM'], 4) . '/' . substr($praMail_rec['RICNUM'], 0, 4) . " è già stato caricato.<br>
                        Vuoi Importare la Mail nella Pratica?.", array(
                        'Annulla' => array('id' => $this->nameForm . '_AnnullaImportaMailFO', 'model' => $this->nameForm),
                        'Conferma' => array('id' => $this->nameForm . '_ConfermaImportaMailFO', 'model' => $this->nameForm)
                            )
                    );
                }
                return;
            }
        }
    }

    private function getAlert() {
        $countRead = 0;
        foreach ($this->elencoMail as $mail) {
            if ($mail['READED'] == 0) {
                $countRead++;
            }
        }
        return $this->currAlert = $countRead . " Nuovi Messaggi -  " . count($this->elencoMail) . " Da controllare";
    }

    private function refreshAlert() {
        Out::html($this->divAlert, $this->currAlert);
    }

    private function getWhereFiltri() {
        $daData = $_POST[$this->nameForm . '_dallaData'];
        $aData = $_POST[$this->nameForm . '_allaData'];
        if (!$aData)
            $aData = date("Ymd");
        if ($daData && $aData) {
            if ($daData > $aData) {
                Out::msgStop("Attenzione!!", "Date Incongruenti");
                return false;
            }
        }

        $sql = "";

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
        }



        if ($_POST[$this->nameForm . '_cbTutti'] == 1) {
            return $sql;
        }
        $where = array();
        $whereDate = "";
        if ($_POST[$this->nameForm . '_cbMsgInt'] == 1) {
            $where[] = "INTEROPERABILE > 0";
        }

        if ($_POST[$this->nameForm . '_cbMsgPEC'] == 1) {
            //$where[] = "PECTIPO <> ''";
            $where[] = "PECTIPO = 'posta-certificata'";
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

        if ($daData != '' && $aData != "") {
            $whereDate = " AND (" . $this->PRAM_DB->subString('MSGDATE', 1, 8) . " BETWEEN '$daData' AND '$aData')";
        }

        if (count($where)) {
            $sql .= " AND (" . implode(" OR ", $where) . ")";
        }

        if ($_POST[$this->nameForm . '_OggettoSrc']) {
            $sql .= " AND " . $this->PRAM_DB->strLower('SUBJECT') . " LIKE '%" . strtolower($_POST[$this->nameForm . '_OggettoSrc']) . "%'";
        }
        if ($_POST[$this->nameForm . '_MittenteSrc']) {
            $sql .= " AND (" . $this->PRAM_DB->strLower('FROMADDR') . " LIKE '%" . strtolower($_POST[$this->nameForm . '_MittenteSrc']) . "%' OR
                          " . $this->PRAM_DB->strLower('METADATA') . " LIKE '%" . strtolower($_POST[$this->nameForm . '_MittenteSrc']) . "%')";
        }
        switch ($_POST[$this->nameForm . '_RiferimentoSrc']) {
            case "online":
                $sql .= " AND (PRAMAIL.ISFRONTOFFICE = 1 AND PRAMAIL.ISINTEGRATION = 0 AND PRAMAIL.ISANNULLAMENTO = 0)";
                break;
            case "starweb":
                $sql .= " AND PRAMAIL.ISCOMUNICA = 1";
                break;
            case "integrazione":
                $sql .= " AND PRAMAIL.ISINTEGRATION = 1";
                break;
            case "annullamento":
                $sql .= " AND PRAMAIL.ISANNULLAMENTO = 1";
                break;
            case "generica":
                $sql .= " AND PRAMAIL.ISGENERIC = 1";
                break;
            case "parere":
                $sql .= " AND PRAMAIL.ISPARERE = 1";
                break;
            default:
                break;
        }

        if ($whereDate) {
            $sql .= $whereDate;
        }
        return $sql;
    }

    private function getWhereFiltriScarti() {
        $daData = $_POST[$this->nameForm . '_dallaDataSc'];
        $aData = $_POST[$this->nameForm . '_allaDataSc'];
        if (!$aData)
            $aData = date("Ymd");
        if ($daData && $aData) {
            if ($daData > $aData) {
                Out::msgStop("Attenzione!!", "Date Incongruenti");
                return false;
            }
        }

        $sql = "";

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
        }

        if ($_POST[$this->nameForm . '_cbTuttiSc'] == 1) {
            return $sql;
        }
        $where = array();
        $whereDate = "";
        if ($_POST[$this->nameForm . '_cbMsgIntSc'] == 1) {
            $where[] = "INTEROPERABILE > 0";
        }

        if ($_POST[$this->nameForm . '_cbMsgPECSc'] == 1) {
            $where[] = "PECTIPO = 'posta-certificata'";
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

        if ($_POST[$this->nameForm . '_cbMsgAnomalieSc'] == 1) {
            $where[] = "PECTIPO<>'posta-certificata' AND PECTIPO<>'accettazione' AND PECTIPO<>'avvenuta-consegna' AND PECTIPO<>''";
        }

        if ($daData != '' && $aData != "") {
            $whereDate = " AND (" . $this->PRAM_DB->subString('MSGDATE', 1, 8) . " BETWEEN '$daData' AND '$aData')";
        }

        if (count($where)) {
            $sql .= " AND (" . implode(" OR ", $where) . ")";
        }

        if ($_POST[$this->nameForm . '_OggettoSrcSc']) {
            $sql .= " AND " . $this->PRAM_DB->strLower('SUBJECT') . " LIKE '%" . strtolower($_POST[$this->nameForm . '_OggettoSrcSc']) . "%'";
        }
        if ($_POST[$this->nameForm . '_MittenteSrcSc']) {
            $sql .= " AND (" . $this->PRAM_DB->strLower('FROMADDR') . " LIKE '%" . strtolower($_POST[$this->nameForm . '_MittenteSrcSc']) . "%' OR
                          " . $this->PRAM_DB->strLower('METADATA') . " LIKE '%" . strtolower($_POST[$this->nameForm . '_MittenteSrcSc']) . "%')";
        }
        switch ($_POST[$this->nameForm . '_RiferimentoSrcSc']) {
            case "online":
                $sql .= " AND (PRAMAIL.ISFRONTOFFICE = 1 AND PRAMAIL.ISINTEGRATION = 0 AND PRAMAIL.ISANNULLAMENTO = 0)";
                break;
            case "starweb":
                $sql .= " AND PRAMAIL.ISCOMUNICA = 1";
                break;
            case "integrazione":
                $sql .= " AND PRAMAIL.ISINTEGRATION = 1";
                break;
            case "annullamento":
                $sql .= " AND PRAMAIL.ISANNULLAMENTO = 1";
                break;
            case "generica":
                $sql .= " AND PRAMAIL.ISGENERIC = 1";
                break;
            case "parere":
                $sql .= " AND PRAMAIL.ISPARERE = 1";
                break;
            default:
                break;
        }

        if ($whereDate) {
            $sql .= $whereDate;
        }
        return $sql;
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

    private function clearCurrMessage() {
        if ($this->currMessage != null) {
            $this->currMessage->cleanData();
            $this->currMessage = null;
        }
    }

    private function caricaElencoAllegati($elementi) {
        $allegati = array();
        //$elementi = $retDecode['Attachments'];
        if ($elementi) {
            $incr = 1;
            foreach ($elementi as $elemento) {
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

    private function elaboraRecordGriglia($mailArchivio_rec, $tipoMail) {
        $mailArchivio_rec['RIFERIMENTO'] = '';
        switch ($tipoMail) {
            case 'KEYMAIL':
                $idMail = $mailArchivio_rec['ROWID'];
                $praMail_rec = $this->praLib->getPraMail($idMail, 'rowidarchivio');
                break;
            case 'KEYUPL':
                $idMail = pathinfo($mailArchivio_rec['FILENAME'], PATHINFO_FILENAME); //pathinfo($emlMessage->getEmlFile(), PATHINFO_FILENAME);
                $praMail_rec = $this->praLib->getPraMail($idMail);
                break;
        }

        if ($praMail_rec['ASSRES']) {
            $ananom_rec = $this->praLib->GetAnanom($praMail_rec['ASSRES'], 'codice');
            $mailArchivio_rec['PRESALLEGATI'] .= "<div title=\"Assegnata a {$ananom_rec['NOMCOG']} {$ananom_rec['NOMNOM']}\" class=\"ita-tooltip ita-icon ita-icon-user-16x16\" style = \"margin-right:2px;display:inline-block;\"></div>";
        }

        $risultato = unserialize($praMail_rec['ANALISIMAIL']);
        if ($risultato['isComunica'] === true) {
            if ($risultato['isFrontOffice'] === true) {
                $Proric_rec = $risultato['infoFrontOffice']['PRORIC'];
                $proges_rec = $this->praLib->GetProges($Proric_rec['RICNUM'], 'richiesta');
                $_POST['presente'] = '';
                if ($proges_rec) {
                    $_POST['presente'] = 'color:red';
                }
                $numero = (int) substr($Proric_rec['RICNUM'], 4, 6) . "/" . substr($Proric_rec['RICNUM'], 0, 4); // . "<BR>";
                $del = ' del ' . substr($Proric_rec['RICDRE'], 6, 2) . "/" . substr($Proric_rec['RICDRE'], 4, 2) . "/" . substr($Proric_rec['RICDRE'], 0, 4);
                $mailArchivio_rec['RIFERIMENTO'] = 'Camera Comm.: Rich.' . $numero . $del;
            } else {
                //$mailArchivio_rec['RIFERIMENTO'] = 'Camera Comm.: Senza Riferimenti';
                $mailArchivio_rec['RIFERIMENTO'] = 'Camera Commercio';
            }
        } elseif ($risultato['isFrontOffice'] === true) {
            $Proric_rec = $risultato['infoFrontOffice']['PRORIC'];
            $proges_rec = $this->praLib->GetProges($Proric_rec['RICNUM'], 'richiesta');
            $_POST['presente'] = '';
            if ($proges_rec) {
                $_POST['presente'] = 'color:red';
            }
            if ($risultato['isIntegration'] === true) {
                $numero = (int) substr($Proric_rec['RICRPA'], 4, 6) . "/" . substr($Proric_rec['RICRPA'], 0, 4);
                $del = ' del ' . substr($Proric_rec['RICDRE'], 6, 2) . "/" . substr($Proric_rec['RICDRE'], 4, 2) . "/" . substr($Proric_rec['RICDRE'], 0, 4);
                $mailArchivio_rec['RIFERIMENTO'] = '<p style="color:blue;font-weight:bold;">Integrazione<p>';
                if ($Proric_rec['RICRPA'] == "1") {
                    $mailArchivio_rec['RIFERIMENTO'] = '<p style="color:navy;font-weight:bold;">Variante/<br>Pratica Collegata<p>';
                }
            } elseif ($risultato['isAnnullamento'] === true) {
                $numero = (int) substr($risultato['infoFrontOffice']['RICNUM'], 4, 6) . "/" . substr($risultato['infoFrontOffice']['RICNUM'], 0, 4);
                $del = ' del ' . substr($risultato['infoFrontOffice']['DATA'], 6, 2) . "/" . substr($risultato['infoFrontOffice']['DATA'], 4, 2) . "/" . substr($risultato['infoFrontOffice']['DATA'], 0, 4);
                $mailArchivio_rec['RIFERIMENTO'] = '<p style="color:orange;font-weight:bold;">Annullamento</p>';
                if ($risultato['infoFrontOffice']['RICRPA']) {
                    $numero = (int) substr($risultato['infoFrontOffice']['RICRPA'], 4, 6) . "/" . substr($risultato['infoFrontOffice']['RICNUM'], 0, 4);
                    $mailArchivio_rec['RIFERIMENTO'] = '<p style="color:orange;font-weight:bold;">Annullamento Int.</p>';
                }
            } elseif ($risultato['isParere'] === true) {
                $numero = $Proric_rec['PROPAK'];
                $del = ' del ' . substr($Proric_rec['RICDRE'], 6, 2) . "/" . substr($Proric_rec['RICDRE'], 4, 2) . "/" . substr($Proric_rec['RICDRE'], 0, 4);
                $mailArchivio_rec['RIFERIMENTO'] = '<p style="color:green;font-weight:bold;">Parere<p>';
            } else {
                $numero = (int) substr($Proric_rec['RICNUM'], 4, 6) . "/" . substr($Proric_rec['RICNUM'], 0, 4);
                $del = ' del ' . substr($Proric_rec['RICDRE'], 6, 2) . "/" . substr($Proric_rec['RICDRE'], 4, 2) . "/" . substr($Proric_rec['RICDRE'], 0, 4);
                $mailArchivio_rec['RIFERIMENTO'] = 'Rich.On-Line';
            }
        } elseif ($risultato['isRicevuta'] && $praMail_rec['COMPAK']) {

            $mailArchivio_rec['RIFERIMENTO'] = '<p style ="color:green;font-weight:bold">Comunic. Rif.: ' . substr($praMail_rec['COMPAK'], 4, 6) . '/' . substr($praMail_rec['COMPAK'], 0, 4) . "</p>";
        }
        return $mailArchivio_rec;
    }

    private function decodInfoMail($risultato, $praMail_rec) {
        if ($risultato['isComunica'] === true) {
            $infoMail = '<div class="ita-header ui-widget-header ui-corner-all" Title="Procedimento da Comunica">&nbsp;</div>';
            $infoMail .= '<pre style="font-size:1.2em;">';
            if ($risultato['isFrontOffice'] === true) {
                $Proric_rec = $risultato['infoFrontOffice']['PRORIC'];
                $infoMail .= 'Numero: ' . (int) substr($Proric_rec['RICNUM'], 4, 6) . "/" . substr($Proric_rec['RICNUM'], 0, 4) . "<BR>";
                $infoMail .= 'Del   : ' . substr($Proric_rec['RICDRE'], 6, 2) . "/" . substr($Proric_rec['RICDRE'], 4, 2) . "/" . substr($Proric_rec['RICDRE'], 0, 4) . "<BR>";
            } else {
                $infoMail .= 'Senza Riferimenti al Front-Office';
            }
            $infoMail .= '</pre><br>';
            //Out::html($this->nameForm . '_divInfoMail', $infoMail);
            Out::show($this->nameForm . '_divInfoMail');
            Out::hide($this->nameForm . '_AssegnaPasso');
            Out::show($this->nameForm . '_Carica');
        } elseif ($risultato['isFrontOffice'] === true) {
            if ($risultato['isIntegration'] === true) {
                $Proric_rec = $risultato['infoFrontOffice']['PRORIC'];
                $infoMail = '<div class="ita-header ui-widget-header ui-corner-all" Title="Integrazione da Front Office">&nbsp;</div>';
                $infoMail .= '<pre style="font-size:1.2em;">';
                $infoMail .= 'Numero Richiesta Integrazione: ' . (int) substr($Proric_rec['RICNUM'], 4, 6) . "/" . substr($Proric_rec['RICNUM'], 0, 4) . "<BR>";
                $infoMail .= 'Numero Richiesta da Integrare: ' . (int) substr($Proric_rec['RICRPA'], 4, 6) . "/" . substr($Proric_rec['RICRPA'], 0, 4) . "<BR>";
                $infoMail .= 'Del   : ' . substr($Proric_rec['RICDRE'], 6, 2) . "/" . substr($Proric_rec['RICDRE'], 4, 2) . "/" . substr($Proric_rec['RICDRE'], 0, 4) . "<BR>";
                $infoMail .= '</pre><br>';
                //Out::html($this->nameForm . '_divInfoMail', $infoMail);
                Out::show($this->nameForm . '_divInfoMail');
                Out::hide($this->nameForm . '_AssegnaPasso');
                Out::show($this->nameForm . '_Carica');
            } elseif ($risultato['isAnnullamento'] === true) {
                $Proric_rec = $risultato['infoFrontOffice']['PRORIC'];
                $infoMail = '<div class="ita-header ui-widget-header ui-corner-all" Title="Annullamento da Front Office">&nbsp;</div>';
                $infoMail .= '<pre style="font-size:1.2em;">';
                if ($risultato['infoFrontOffice']['RICRPA']) {
                    $infoMail .= 'Numero Integrazione da Annullare: ' . (int) substr($risultato['infoFrontOffice']['RICNUM'], 4, 6) . "/" . substr($risultato['infoFrontOffice']['RICNUM'], 0, 4) . "<BR>";
                    $infoMail .= 'Richiesta di riferimento        : ' . (int) substr($risultato['infoFrontOffice']['RICRPA'], 4, 6) . "/" . substr($risultato['infoFrontOffice']['RICRPA'], 0, 4) . "<BR>";
                } else {
                    $infoMail .= 'Numero Richiesta da Annullare: ' . (int) substr($risultato['infoFrontOffice']['RICNUM'], 4, 6) . "/" . substr($risultato['infoFrontOffice']['RICNUM'], 0, 4) . "<BR>";
                }
                $infoMail .= 'Del   : ' . substr($risultato['infoFrontOffice']['DATA'], 6, 2) . "/" . substr($risultato['infoFrontOffice']['DATA'], 4, 2) . "/" . substr($risultato['infoFrontOffice']['DATA'], 0, 4) . "<BR>";
                $infoMail .= '</pre><br>';
                //Out::html($this->nameForm . '_divInfoMail', $infoMail);
                Out::show($this->nameForm . '_divInfoMail');
                Out::hide($this->nameForm . '_AssegnaPasso');
                Out::show($this->nameForm . '_Carica');
            } else {
                $Proric_rec = $risultato['infoFrontOffice']['PRORIC'];
                $infoMail = '<div class="ita-header ui-widget-header ui-corner-all" Title="Procedimento da Front Office">&nbsp;</div>';
                $infoMail .= '<pre style="font-size:1.2em;">';
                $infoMail .= 'Numero: ' . (int) substr($Proric_rec['RICNUM'], 4, 6) . "/" . substr($Proric_rec['RICNUM'], 0, 4) . "<BR>";
                $infoMail .= 'Del   : ' . substr($Proric_rec['RICDRE'], 6, 2) . "/" . substr($Proric_rec['RICDRE'], 4, 2) . "/" . substr($Proric_rec['RICDRE'], 0, 4) . "<BR>";
                $infoMail .= '</pre><br>';
                //Out::html($this->nameForm . '_divInfoMail', $infoMail);
                Out::show($this->nameForm . '_divInfoMail');
                Out::hide($this->nameForm . '_AssegnaPasso');
                Out::show($this->nameForm . '_Carica');
            }
        } elseif ($risultato['isRicevuta'] === true && $praMail_rec['COMPAK']) {
            $infoMail = '<div class="ita-header ui-widget-header ui-corner-all" Title="" style="align:center;">Ricevuta Comunicazione</div>';
            $infoMail .= '<pre style="font-size:1.2em;">';
            $infoMail .= $risultato['infoRicevuta']['tipoRicevuta'] . '</BR>';
            $infoMail .= 'Pratica : ' . substr($praMail_rec['COMPAK'], 4, 6) . "/" . substr($praMail_rec['COMPAK'], 0, 4) . '</BR>';
            $Propas_rec = $this->praLib->GetPropas($praMail_rec['COMPAK']);
            $Pracom_rec = $this->praLib->GetPracom($praMail_rec['COMIDMAIL'], 'idmail');
            $infoMail .= 'Passo N.: ' . $Propas_rec['PROSEQ'] . '</BR>';
            $infoMail .= $Propas_rec['PRODPA'] . '</BR>';
            $infoMail .= $Pracom_rec['COMNOM'] . '</BR>';
            $infoMail .= '</pre><br>';
            //Out::html($this->nameForm . '_divInfoMail', $infoMail);
            Out::show($this->nameForm . '_divInfoMail');
            Out::hide($this->nameForm . '_Carica');
            Out::hide($this->nameForm . '_AssegnaPasso');
//Out::show($this->nameForm . '_ConfermaVisione');
        } else {
            Out::show($this->nameForm . '_AssegnaPasso');
            Out::show($this->nameForm . '_Carica');
        }

        if ($praMail_rec['ASSRES']) {
            $ananom_rec = $this->praLib->GetAnanom($praMail_rec['ASSRES'], 'codice');
            $infoMail .= '<div class="ita-header ui-widget-header ui-corner-all" Title="" style="align:center;">Mail assegnata a nominativo</div>';
            $infoMail .= '<span style="font-size:1.2em;">';
            $infoMail .= 'Codice : ' . $ananom_rec['NOMRES'] . '  <span style="color:darkred;font-wheigth:bold;">' . $ananom_rec['NOMCOG'] . ' ' . $ananom_rec['NOMNOM'] . '</span>';
            $infoMail .= '</span>';
            Out::show($this->nameForm . '_divInfoMail');
        }
        if ($praMail_rec['SCARTOMOTIVO']) {
            $infoMail .= '<div class="ita-header ui-widget-header ui-corner-all" Title="" style="align:center;">Motivo Scarto</div>';
            $infoMail .= '<span style="color:darkred;font-wheigth:bold;font-size:1.2em;">';
            $infoMail .= $praMail_rec['SCARTOMOTIVO'];
            $infoMail .= '</span>';
//            $infoMail .= '<pre style="color:red;font-wheight:bold;font-size:1.2em;">';
//            $infoMail .= $praMail_rec['SCARTOMOTIVO'];
//            $infoMail .= '</pre>';
        }
        Out::html($this->nameForm . '_divInfoMail', $infoMail);
    }

    private function CaricaGriglia($griglia, $record, $tipo = '1', $pageRows = '100000') {
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $record,
            'rowIndex' => 'idx')
        );
        if ($tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        $ita_grid01->getDataPage('json');
        return;
    }

    private function CreaCombo() {
        Out::select($this->nameForm . '_cbAccount', 1, "", 0, "Tutti gli Account di posta");
        foreach ($this->refAccounts as $account) {
            Out::select($this->nameForm . '_cbAccount', 1, $account['EMAIL'], 0, $account['EMAIL']);
        }
        //
        Out::select($this->nameForm . '_RiferimentoSrc', 1, "", "1", "Seleziona Riferimento");
        Out::select($this->nameForm . '_RiferimentoSrc', 1, "online", "0", "Richiesta on-line");
        Out::select($this->nameForm . '_RiferimentoSrc', 1, "starweb", "0", "Richiesta Camera di Commercio");
        Out::select($this->nameForm . '_RiferimentoSrc', 1, "integrazione", "0", "Integrazione");
        Out::select($this->nameForm . '_RiferimentoSrc', 1, "annullamento", "0", "Annullamento");
        Out::select($this->nameForm . '_RiferimentoSrc', 1, "parere", "0", "Parere");
        Out::select($this->nameForm . '_RiferimentoSrc', 1, "generica", "0", "Generica");
        //
        Out::select($this->nameForm . '_RiferimentoSrcSc', 1, "", "1", "Seleziona Riferimento");
        Out::select($this->nameForm . '_RiferimentoSrcSc', 1, "online", "0", "Richiesta on-line");
        Out::select($this->nameForm . '_RiferimentoSrcSc', 1, "starweb", "0", "Richiesta Camera di Commercio");
        Out::select($this->nameForm . '_RiferimentoSrcSc', 1, "integrazione", "0", "Integrazione");
        Out::select($this->nameForm . '_RiferimentoSrcSc', 1, "annullamento", "0", "Annullamento");
        Out::select($this->nameForm . '_RiferimentoSrcSc', 1, "parere", "0", "Parere");
        Out::select($this->nameForm . '_RiferimentoSrcSc', 1, "generica", "0", "Generica");
    }

    private function selezionaDaScartare() {
        switch ($_POST[$this->nameForm . '_TipoPec']) {
            case "PEC":
                $where = " AND PECTIPO = 'posta-certificata'";
                break;
            case "ACC":
                $where = " AND PECTIPO = 'accettazione'";
                break;
            case "CON":
                $where = " AND PECTIPO = 'avvenuta-consegna'";
                break;
            case "ANO":
                $where = " AND PECTIPO<>'posta-certificata' AND PECTIPO<>'accettazione' AND PECTIPO<>'avvenuta-consegna' AND PECTIPO<>''";
                break;
        }

        /*
         * preparo la stringa per la query con gli account
         */
        $strAccount = "";
        if ($_POST[$this->nameForm . "_cbAccount"]) {
            $strAccount = "'" . $_POST[$this->nameForm . "_cbAccount"] . "'";
        } else {
            foreach ($this->refAccounts as $key => $account) {
                $strAccount .= "'" . $account['EMAIL'] . "',";
            }
            $strAccount = substr($strAccount, 0, -1);
        }
        if ($_POST[$this->nameForm . "_DaDataMail"] && $_POST[$this->nameForm . "_DaDataMail"]) {
            $whereData = " AND (" . $this->PRAM_DB->subString('MSGDATE', 1, 8) . " BETWEEN '" . $_POST[$this->nameForm . "_DaDataMail"] . "' AND '" . $_POST[$this->nameForm . "_ADataMail"] . "')";
        }
        //$sql = "SELECT * FROM MAIL_ARCHIVIO WHERE CLASS ='@SPORTELLO_DA_CONTROLLARE@' AND ACCOUNT='" . $this->refAccounts[0]['EMAIL'] . "' ORDER BY MSGDATE DESC";
        $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE CLASS ='@SPORTELLO_DA_CONTROLLARE@' AND
                ACCOUNT IN ($strAccount)  
                $whereData
                $where
                ORDER BY MSGDATE DESC";
        $mail_tab = $this->emlLib->getGenericTab($sql);
        if (!$mail_tab) {
            Out::msgInfo("Scata Mail", "Non sono state trovate mail nelle date selezionate");
            return false;
        }
        foreach ($mail_tab as $key => $mail_rec) {
            $mail_tab[$key]['DATAMESSAGGIO'] = date("d/m/Y", strtotime($mail_rec['MSGDATE']));
            $mail_tab[$key]['ORAMESSAGGIO'] = date("H:i:s", strtotime($mail_rec['MSGDATE']));
        }
        $mail_tab_appoggio = array();
        foreach ($mail_tab as $key => $val) {
            $praMail_rec = $this->praLib->getPraMail($val['ROWID'], 'rowidarchivio');
            if ($praMail_rec) {
                if (count($this->visibilita['SPORTELLI']) != 0 || $this->visibilita['AGGREGATO'] != 0) {
                    if ($praMail_rec['RICNUM']) {
                        $sqlProric = "SELECT * FROM PRORIC WHERE RICNUM='" . $praMail_rec['RICNUM'] . "'";
                        $sqlProric .= $this->praLib->GetWhereVisibilitaSportelloFO();
                        $proric_rec = $this->praLib->getGenericTab($sqlProric);
                        if (!$proric_rec) {
                            continue;
                        }
                    }
                    if ($praMail_rec['GESNUM']) {
                        $sqlProges = "SELECT * FROM PROGES WHERE GESNUM='" . $praMail_rec['GESNUM'] . "'";
                        $sqlProges .= $this->praLib->GetWhereVisibilitaSportello();
                        $proges_rec = $this->praLib->getGenericTab($sqlProges);
                        if (!$proges_rec) {
                            continue;
                        }
                    }
                }
            }
            $mail_tab_appoggio[$val['ROWID']] = $val;
        }

        $colNames = array(
            "Mittente",
            "Certificazione",
            "Data",
            "Ora",
            "Oggetto",
            "Account"
        );
        $colModel = array(
            array("name" => 'FROMADDR', "width" => 245),
            array("name" => 'PECTIPO', "width" => 120),
            array("name" => 'DATAMESSAGGIO', "width" => 75),
            array("name" => 'ORAMESSAGGIO', "width" => 75),
            array("name" => 'SUBJECT', "width" => 400),
            array("name" => 'ACCOUNT', "width" => 120)
        );
        include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
        proRic::proMultiselectGeneric(
                $mail_tab_appoggio, $this->nameForm, 'DaScartare', 'Seleziona le Email da Scartare', $colNames, $colModel, "", array("width" => "1200", "height" => "400")
        );
    }

    function tornaElenco() {
        if ($this->refAccounts) {
            if ($this->tabAttiva == "ELENCO") {
                $this->openRicerca(false, false);
                TableView::setSelection($this->gridElencoMail, $_POST[$this->gridElencoMail]['gridParam']['selrow']);
            }
            if ($this->tabAttiva == "SCARTATE") {
                $this->openRicercaScarti();
                TableView::reload($this->gridElencoMailScarti);
                TableView::setSelection($this->gridElencoMailScarti, $_POST[$this->gridElencoMailScarti]['gridParam']['selrow']);
            }
        } else {
            $this->openRicercaLocale();
            if ($_POST[$this->gridElencoMailLocale]['gridParam']['selrow']) {
                TableView::setSelection($this->gridElencoMailLocale, $_POST[$this->gridElencoMailLocale]['gridParam']['selrow']);
            }
        }
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
            //
            $this->nameForm . '_OggettoSrc' => $_POST[$this->nameForm . '_OggettoSrc'],
            $this->nameForm . '_RiferimentoSrc' => $_POST[$this->nameForm . '_RiferimentoSrc'],
            $this->nameForm . '_MittenteSrc' => $_POST[$this->nameForm . '_MittenteSrc'],
            $this->nameForm . '_dallaData' => $_POST[$this->nameForm . '_dallaData'],
            $this->nameForm . '_allaData' => $_POST[$this->nameForm . '_allaData']
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

    function CheckStatoMail($rowid) {
        $mailArchivio_rec = $this->emlLib->getMailArchivio($rowid, "rowid");
        if ($mailArchivio_rec['CLASS'] == "@SPORTELLO_SCARTATO@") {
            $msgDett = "Attenzione!! La mail selezionata non è più disponibile nell'elenco perchè risulta scartata.<br>Per ripristinarla, ricercarla nel tab Email Scartate e una volta trovata, entrare nel dettaglio della mail e premere il bottone ripristina.";
        } elseif ($mailArchivio_rec['CLASS'] == "@SPORTELLO_CARICATO@") {
            $msgDett = "Attenzione!! La mail selezionata non è più disponibile nell'elenco perchè risulta caricata";
        }
        return $msgDett;
    }

    public function GetFormMotivoScarta() {
//        $AnnullamentoRec = $_POST[$this->nameForm . '_ANNULLAMENTO'];
//        $anapro_rec = $this->proLib->GetAnapro($this->anapro_record['ROWID'], 'rowid');
//        $arcite_tab = $this->proLib->getGenericTab("SELECT * FROM ARCITE WHERE ITEPRO=" . $anapro_rec['PRONUM']
//                . " AND ITENODO='ASS' AND ITEPAR='$this->tipoProt'");
//        // Data provvedimento
        $valori[] = array(
            'label' => array(
                'value' => "<b>Motivo di Scarto:</b>",
                'style' => 'margin-top:10px;width:120px;display:block;float:left;padding: 0 5px 0 0;text-align: right; color:red;'
            ),
            'id' => $this->nameForm . '_scartaMailMotivo',
            'name' => $this->nameForm . '_scartaMailMotivo',
            'type' => 'textarea',
            'class' => 'ita-edit-multiline',
            'style' => 'margin-top:10px;width:450px;',
            'value' => ''
        );

        $messaggio = "Conferma motivando lo scarto della mail:";
        Out::msgInput(
                'Scarta Mail', $valori
                , array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaScarta', 'model' => $this->nameForm),
            'Annulla' => array('id' => $this->nameForm . '_AnnullaScarta', 'model' => $this->nameForm)
                ), $this->nameForm . "_workSpace", 'auto', '700', true, "<span style=\"font-size:1.2em;font-weight:bold;\">$messaggio</span>"
        );

        Out::setFocus('', $this->nameForm . '_scartaMailMotivo');
    }

    public function CaricaNew($datiMail) {
        $datiMail['Dati']['PRAMAIL_REC'] = $datiMail['PRAMAIL'];
        if ($this->elemento) {
            $datiMail['Dati']['archivio'] = true;
        } else {
            $datiMail['Dati']['archivio'] = false;
        }

        /*
         * Chiamo il metodo factory 
         */
        if ($datiMail['Dati']['PRAFOLIST_REC']) {
            include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeManager.class.php';
            $ret_esito = praFrontOfficeManager::caricaRichiesta($datiMail['Dati']['PRAFOLIST_REC'], $datiMail['Dati'], $datiMail['Dati']['ALLEGATICOMUNICA']);
            if ($ret_esito === false) {
                Out::msgStop("Errore di acquisizione", praFrontOfficeManager::$lasErrMessage);
                return false;
            }

            /*
             * Se andato a buon fine riapro praGestElenco per andare al Dettaglio
             */
            if ($ret_esito[0]['GESNUM'] || $ret_esito[0]['PROPAK']) {
                TableView::reload($this->gridElencoMail);
                $this->tornaElenco();
                Out::msgInfo("Acquisizione Pratiche", $ret_esito[0]['ExtendedMessageHtml']);
                $_POST = array();
                $_POST['datiAcquisizione'] = $ret_esito[0];
                Out::desktopTabSelect($this->returnModel);
                $objModel = itaModel::getInstance($this->returnModel);
                $objModel->setEvent("returnCtrRichiesteFO");
                $objModel->parseEvent();
                return true;
            }
            return true;
        }

        $model = $this->returnModel;
        $_POST['event'] = $this->returnEvent;
        $_POST['datiMail'] = $datiMail['Dati'];
        $_POST['returnModel'] = $this->nameForm;
        $_POST['returnEvent'] = 'returnPraGest';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
        if ($this->refAccounts) {
            $this->openRicerca(false, false);
            TableView::reload($this->gridElencoMail);
        } else {
            $this->openRicercaLocale();
        }
        return true;
    }

    public function CaricaOld($datiMail, $formAddr, $subject) {

        /*
         * Verifico se c'è il flag pratica collegata, se si la carico come nuova pratica
         */
        $variante = false;
        if ($datiMail['Dati']['PRORIC_REC']['RICPC'] == "1") {
            $variante = true;
        }

        if ($datiMail['PRAMAIL']['ISINTEGRATION'] && !$variante) {
            if ($this->elemento) {
                $datiMail['archivio'] = true;
            } else {
                $datiMail['archivio'] = false;
            }
            $Propas_rec = $this->praLib->CaricaPassoIntegrazione($datiMail['Dati']['PRORIC_REC'], $datiMail['Dati']['ELENCOALLEGATI'], $datiMail['Dati']['FILENAME'], $datiMail['PRAMAIL'], $datiMail['archivio'], false, $datiMail['Dati']['PRAFOLIST_REC']['ROW_ID']);
            if ($Propas_rec == false) {
                Out::msgStop("Errore cariamento passo integrazione", $this->praLib->getErrMessage());
                return false;
            }
            //
            //Chiamo model praPasso
            //
            $datiForm = array(
                'GESNUM' => $Propas_rec['PRONUM'],
                'PROPAK' => $Propas_rec['PROPAK']
            );

            $model = 'praPasso';
            $_POST = array();
            $_POST['event'] = 'openform';
            $_POST['rowid'] = $Propas_rec['ROWID'];
            $_POST['modo'] = "edit";
            $_POST['perms'] = $this->perms;
            $_POST['datiForm'] = $datiForm;
            $_POST[$model . '_returnModel'] = $this->nameForm;
            $_POST[$model . '_returnMethod'] = 'returnPraPasso';
            itaLib::openForm($model);
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $model();
            return true;
        } elseif ($datiMail['PRAMAIL']['ISANNULLAMENTO']) {
            if ($this->elemento) {
                $datiMail['archivio'] = true;
            } else {
                $datiMail['archivio'] = false;
            }

            $Propas_rec = $this->praLib->CaricaPassoAnnullamento($datiMail['Dati']['PRORIC_REC'], $datiMail['Dati']['FILENAME'], $datiMail['PRAMAIL'], $datiMail['archivio'], false, $datiMail['Dati']['PRAFOLIST_REC']['ROW_ID']);
            if ($Propas_rec == false) {
                return false;
            }
            $datiForm = array(
                'GESNUM' => $Propas_rec['PRONUM'],
                'PROPAK' => $Propas_rec['PROPAK']
            );

            $model = 'praPasso';
            $_POST = array();
            $_POST['event'] = 'openform';
            $_POST['rowid'] = $Propas_rec['ROWID'];
            $_POST['modo'] = "edit";
            $_POST['perms'] = $this->perms;
            $_POST['datiForm'] = $datiForm;
            $_POST[$model . '_returnModel'] = $this->nameForm;
            $_POST[$model . '_returnMethod'] = 'returnPraPasso';
            itaLib::openForm($model);
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $model();
            return true;
        } elseif ($datiMail['PRAMAIL']['ISPARERE']) {
            if ($this->elemento) {
                $datiMail['archivio'] = true;
            } else {
                $datiMail['archivio'] = false;
            }
            $Propas_rec = $this->praLib->CaricaPassoIntegrazione($datiMail['Dati']['PRORIC_REC'], $datiMail['Dati']['ELENCOALLEGATI'], $datiMail['Dati']['FILENAME'], $datiMail['PRAMAIL'], $datiMail['archivio'], true, $datiMail['Dati']['PRAFOLIST_REC']['ROW_ID']);
            if ($Propas_rec == false) {
                Out::msgStop("Errore cariamento passo parere", $this->praLib->getErrMessage());
                return false;
            }
            //
            //Chiamo model praPasso
            //
            $datiForm = array(
                'GESNUM' => $Propas_rec['PRONUM'],
                'PROPAK' => $Propas_rec['PROPAK']
            );

            $model = 'praPasso';
            $_POST = array();
            $_POST['event'] = 'openform';
            $_POST['rowid'] = $Propas_rec['ROWID'];
            $_POST['modo'] = "edit";
            $_POST['perms'] = $this->perms;
            $_POST['datiForm'] = $datiForm;
            $_POST[$model . '_returnModel'] = $this->nameForm;
            $_POST[$model . '_returnMethod'] = 'returnPraPasso';
            itaLib::openForm($model);
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $model();
            return true;
        } elseif ($datiMail['PRAMAIL']['ISFRONTOFFICE']) {
            $model = 'praGestDatiEssenziali';
            $_POST['event'] = 'openform';
            $_POST['returnModel'] = $this->nameForm;
            $_POST['returnEvent'] = 'returnCaricaMailGenerica';
            $_POST['oggetto'] = $subject;
            $_POST['email'] = $formAddr;
            $_POST['datiMail'] = $datiMail;
            $_POST['isFrontOffice'] = true;
            itaLib::openForm($model);
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $model();
            return true;
        }

        $model = $this->returnModel;
        $_POST['event'] = $this->returnEvent;
        $_POST['datiMail'] = $datiMail['Dati'];
        $_POST['returnModel'] = $this->nameForm;
        $_POST['returnEvent'] = 'returnPraGest';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
        if ($this->refAccounts) {
            $this->openRicerca(false, false);
            TableView::reload($this->gridElencoMail);
        } else {
            $this->openRicercaLocale();
        }
        return true;
    }

}
