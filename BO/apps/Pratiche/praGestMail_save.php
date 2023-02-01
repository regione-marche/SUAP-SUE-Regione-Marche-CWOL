<?php

/**
 *
 * GESTIONE EMAIL PRATICHE
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    20.03.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praMessage.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEmailDate.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiIcons.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlRic.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlDbMailBox.class.php';
include_once (ITA_LIB_PATH . '/itaPHPMail/itaMime.class.php');

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

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->emlLib = new emlLib();
            $this->proLib = new proLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->ITALWEB = $this->emlLib->getITALWEB();
            $this->emailTempPath = $this->praLib->SetDirectoryPratiche('', '', 'PEC');
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
            App::$utente->setKey($this->nameForm . "_currAlert", $this->elementoLocale);
            App::$utente->setKey($this->nameForm . "_visibilita", $this->visibilita);
            App::$utente->setKey($this->nameForm . "_refAccounts", $this->refAccounts);
            App::$utente->setKey($this->nameForm . "_Proric_rec", $this->Proric_rec);
            App::$utente->setKey($this->nameForm . "_returnModel", $this->returnModel);
            App::$utente->setKey($this->nameForm . "_returnEvent", $this->returnEvent);
            App::$utente->setKey($this->nameForm . "_datiAppoggio", $this->datiAppoggio);
            App::$utente->setKey($this->nameForm . "_elencoMail", $this->elencoMail);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->returnModel = $_POST['returnModel'];
                $this->returnEvent = $_POST['returnEvent'];
                $this->visibilita = $this->praLib->GetVisibiltaSportello();
                $anatsp_rec_scia = $this->praLib->GetAnatsp(1);
                $anatsp_rec_ordinario = $this->praLib->GetAnatsp(2);
                if (!$this->visibilita || ($anatsp_rec_scia['TSPPEC'] == "" && $anatsp_rec_ordinario['TSPPEC'] == "")) {
                    $this->openRicercaLocale();
                    Out::tabRemove($this->nameForm . "_tabMail", $this->nameForm . "_paneElenco");
                    Out::tabRemove($this->nameForm . "_tabMail", $this->nameForm . "_paneScarti");
                    break;
                }
                $risultato = $this->setRefAccounts();
                if ($risultato === true) {
                    $data = date('Ymd', strtotime('-1 day', strtotime(date('Ymd'))));
                    Out::valore($this->nameForm . '_Dadata', $data);
                    Out::valore($this->nameForm . '_Adata', date('Ymd'));
                    $this->openRicerca();
                }
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
                            $this->openRicerca(false);
                        }
                        break;
                    case $this->nameForm . '_cbMsgInt':
                    case $this->nameForm . '_cbMsgPEC':
                    case $this->nameForm . '_cbAccettazione':
                    case $this->nameForm . '_cbConsegna':
                    case $this->nameForm . '_cbMsgStd':
                        Out::valore($this->nameForm . "_cbTutti", "0");
                        $_POST[$this->nameForm . '_cbTutti'] = 0;
                        $this->openRicerca(false);
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
                        break;
                    case $this->gridElencoMailScarti :
                        $this->caricaTabellaScarti($_POST[$this->nameForm . "_Dadata"], $_POST[$this->nameForm . "_Adata"]);
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
                        $this->Dettaglio();
                        Out::show($this->nameForm . '_Scarta');
                        break;
                    case $this->gridElencoMailScarti :
                        $this->Dettaglio(true);
                        Out::show($this->nameForm . '_Ripristina');
                        break;
                    case $this->gridElencoMailLocale :
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
                        $delete_Info = 'Oggetto Delete PraMail record: ' . $praMail_rec['IDMAIL'] . " " . $praMail_rec['MAILSTATO'];
                        if (!$this->deleteRecord($this->PRAM_DB, 'PRAMAIL', $praMail_rec['ROWID'], $delete_Info)) {
                            Out::msgStop("Cancellazione Mail", "Errore in cancellazione record PRAMAIL.");
                            break;
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
                    case $this->nameForm . '_Carica':
                        $msgOrig = $this->GetMittenteOriginale();
                        if ($this->elemento) {
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
                        if ($datiMail['PRAMAIL']['ISGENERIC']) {
                            $model = 'praGestDatiEssenziali';
                            $_POST['event'] = 'openform';
                            $_POST['returnModel'] = $this->nameForm;
                            $_POST['returnEvent'] = 'returnCaricaMailGenerica';
                            $_POST['oggetto'] = $subject;
                            $_POST['email'] = $formAddr;
                            $_POST['datiMail'] = $datiMail;
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                            break;
                        } elseif ($datiMail['PRAMAIL']['ISINTEGRATION']) {
                            if ($this->elemento) {
                                $datiMail['archivio'] = true;
                            } else {
                                $datiMail['archivio'] = false;
                            }

                            $Propas_rec = $this->praLib->CaricaPassoIntegrazione($datiMail['Dati']['PRORIC_REC'], $datiMail['Dati']['ELENCOALLEGATI'], $datiMail['Dati']['FILENAME'], $datiMail['PRAMAIL'], $datiMail['archivio']);
                            if ($Propas_rec == false) {
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

                            $Propas_rec = $this->praLib->CaricaPassoAnnullamento($datiMail['Dati']['PRORIC_REC'], $datiMail['Dati']['FILENAME'], $datiMail['PRAMAIL'], $datiMail['archivio']);
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
                        break;
                    case $this->nameForm . '_Elenca':
                        $this->tornaElenco();
                        /*
                          if ($this->refAccounts) {
                          $this->openRicerca(false, false);
                          if ($_POST[$this->gridElencoMail]['gridParam']['selrow']) {
                          TableView::setSelection($this->gridElencoMail, $_POST[$this->gridElencoMail]['gridParam']['selrow']);
                          }
                          if ($_POST[$this->gridElencoMailScarti]['gridParam']['selrow']) {
                          TableView::setSelection($this->gridElencoMailScarti, $_POST[$this->gridElencoMailScarti]['gridParam']['selrow']);
                          }
                          } else {
                          $this->openRicercaLocale();
                          if ($_POST[$this->gridElencoMailLocale]['gridParam']['selrow']) {
                          TableView::setSelection($this->gridElencoMailLocale, $_POST[$this->gridElencoMailLocale]['gridParam']['selrow']);
                          }
                          }

                         */
                        break;
                    case $this->nameForm . '_AssegnaPasso':
                        //praRicPasso::praRicProges($this->nameForm);
                        praRic::praRicProges($this->nameForm, " AND GESDCH = ''");
                        break;
                    case $this->nameForm . '_Ricevi':
                        $this->scaricaPosta();
                        break;
                    case $this->nameForm . '_RicercaScarti':
                        $this->Azzera(false);
                        $this->caricaTabellaScarti($_POST[$this->nameForm . "_Dadata"], $_POST[$this->nameForm . "_Adata"]);
                        break;
                    case $this->nameForm . '_Scarta':
                        $this->scartaMail($this->elemento['ROWID']);
                        $this->openRicerca(false);
                        break;
                    case $this->nameForm . '_Ripristina':
                        $this->scartaMail($this->elemento['ROWID'], true);
                        $this->openRicerca(false);
                        break;
                    case $this->nameForm . '_CertificatoV':
                    case $this->nameForm . '_CertificatoNV':
                        Out::openDocument(utiDownload::getUrl($this->certificato['Signature']['FileName'], $this->certificato['Signature']['DataFile']));
                        break;
                    case $this->nameForm . '_DatiPec':
                        $certificazione = "<br><br><b>Tipo: </b>" . $this->certificato['ita_PEC_info']['tipo'] . "<br>";
                        $certificazione.="<b>Errore: </b>" . $this->certificato['ita_PEC_info']['errore'] . "<br>";
                        $certificazione.="<b>Mittente: </b>" . $this->certificato['ita_PEC_info']['mittente'] . "<br>";
                        $certificazione.="<b>Emittente: </b>" . $this->certificato['ita_PEC_info']['gestore-emittente'] . "<br>";
                        $certificazione.="<b>Oggetto: </b>" . $this->certificato['ita_PEC_info']['oggetto'] . "<br>";
                        $certificazione.="<b>Data e Ora: </b>" . $this->certificato['ita_PEC_info']['data'] . " - " . $this->certificato['ita_PEC_info']['ora'] . "<br>";
                        Out::msgInfo("Dati Certificazione", $certificazione);
                        break;
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
                            $datiInfo.='<br>Inserire i dati del passo prima di Aggiungere.';
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
                        $model = 'praGest';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowidDettaglio'] = $proges_rec['ROWID'];
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_ScartaMulti':
                        $this->selezionaDaScartare();
                        break;
                    case $this->nameForm . '_Esci':
                    case 'close-portlet':
                        $this->returnToParent();
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
            case 'returnAccount':
                $Mail_account_rec = $this->emlLib->getMailAccount($_POST['retKey'], 'rowid');
                $this->setRefAccounts(array(array("EMAIL" => $Mail_account_rec['MAILADDR'])));
                $this->openRicerca();
                break;
            case 'returnAccountSportelli':
                $account[0] = array('EMAIL' => $_POST['rowData']['TSPPEC']);
                $risultato = $this->setRefAccounts($account);
                if ($risultato === true) {
                    $titolo = "Elenco Mail - " . $_POST['rowData']['TSPDES'] . " - " . $_POST['rowData']['TSPPEC'];
                    Out::setAppTitle($this->nameForm, "<span>$titolo</span>");
                    $this->openRicerca();
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
            case 'returnProges':
                $proges_rec = $this->praLib->GetProges($_POST['retKey'], 'rowid');
                $_POST['PROGES_REC'] = $proges_rec;
                $gesnum = $proges_rec['GESNUM'];
                if ($this->elemento) {
                    $oggetto = $this->elemento['SUBJECT'];
                } else {
                    $oggetto = $this->dettagliFile[$this->elementoLocale]['SUBJECT'];
                }
                $anades_rec = $this->praLib->GetAnades($gesnum, 'codice');
                $anapra_rec = $this->praLib->GetAnapra($proges_rec['GESPRO'], 'codice');
                $this->setModelData($_POST);
                //praRicPasso::praRicPropas($this->nameForm, " AND PRONUM='" . $gesnum . "' AND PROFIN='' AND PROPUB=''", ''
                praRic::praRicPropas($this->nameForm, " WHERE PRONUM='" . $gesnum . "' AND PROFIN='' AND PROPUB=''", ''
                        , "Seleziona il Passo proveniente dalla pratica: "
                        . (int) substr($gesnum, 4) . "/" . substr($gesnum, 0, 4)
                        . "<br>Richiesta num° " . (int) substr($proges_rec['GESPRA'], 4) . "/" . substr($proges_rec['GESPRA'], 0, 4)
                        . " da " . $anades_rec['DESNOM'] . " - " . $anades_rec['DESFIS']
                        . "<br>" . $anapra_rec['PRADES__1'] . " del " . date("d/m/Y", strtotime($proges_rec['GESDRE']))
                        . "<br>per l'Email con oggetto: " . $oggetto, true);
                break;
            case 'returnPropas':
                if ($_POST['retKey']) {
                    $propas_rec = $this->praLib->GetPropas($_POST['retKey'], 'rowid');
                    $pracomA_rec = $this->praLib->GetPracomA($propas_rec['PROPAK']);
                }
                if ($pracomA_rec) {
                    Out::msgQuestion("Attenzione!", "E' già stato protocollato l'arrivo per questo passo,<br>
                        Vuoi allegare l'email a passo?", array(
                        'F8 - No' => array('id' => $this->nameForm . '_NullaDaEseguire',
                            'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5 - Si' => array('id' => $this->nameForm . '_NonProtocollare',
                            'model' => $this->nameForm, 'shortCut' => "f5")
                            ), 'auto', 'auto', 'false'
                    );
                } else {
                    Out::msgQuestion("Protocollazione Arrivo.", "<br><br><br>Vuoi Protocollare l'Arrivo?", array(
                        'F8 - No' => array('id' => $this->nameForm . '_NonProtocollare',
                            'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5 - Si' => array('id' => $this->nameForm . '_ProtocollaArrivo',
                            'model' => $this->nameForm, 'shortCut' => "f5")
                            ), 'auto', 'auto', 'false'
                    );
                }
                $this->datiAppoggio['formData'] = $this->formData;
                $this->datiAppoggio['post'] = $_POST;
                break;
            case 'returnPraGest':
                if ($this->refAccounts) {
                    $this->openRicerca(false, true);
                    if ($_POST[$this->gridElencoMail]['gridParam']['selrow']) {
                        TableView::setSelection($this->gridElencoMail, $_POST[$this->gridElencoMail]['gridParam']['selrow']);
                    }
                    if ($_POST[$this->gridElencoMailScarti]['gridParam']['selrow']) {
                        TableView::setSelection($this->gridElencoMailScarti, $_POST[$this->gridElencoMailScarti]['gridParam']['selrow']);
                    }
                } else {
                    $this->openRicercaLocale();
                    if ($_POST[$this->gridElencoMailLocale]['gridParam']['selrow']) {
                        TableView::setSelection($this->gridElencoMailLocale, $_POST[$this->gridElencoMailLocale]['gridParam']['selrow']);
                    }
                }
                break;
            case 'returnPraPasso':
                if ($_POST['DATIPASSO']) {
                    if ($this->refAccounts) {
                        $this->openRicerca(false, true);
                        if ($_POST[$this->gridElencoMail]['gridParam']['selrow']) {
                            TableView::setSelection($this->gridElencoMail, $_POST[$this->gridElencoMail]['gridParam']['selrow']);
                        }
                        if ($_POST[$this->gridElencoMailScarti]['gridParam']['selrow']) {
                            TableView::setSelection($this->gridElencoMailScarti, $_POST[$this->gridElencoMailScarti]['gridParam']['selrow']);
                        }
                    } else {
                        $this->openRicercaLocale();
                        if ($_POST[$this->gridElencoMailLocale]['gridParam']['selrow']) {
                            TableView::setSelection($this->gridElencoMailLocale, $_POST[$this->gridElencoMailLocale]['gridParam']['selrow']);
                        }
                    }
                    $gesnum = $_POST['DATIPASSO']['GESNUM'];
                    $propas_rec = $this->praLib->GetPropas($_POST['DATIPASSO']['PROPAK'], 'propak');
                    $this->datiAppoggio = $_POST['DATIPASSO'];
                    Out::msgQuestion("Passo n° " . $propas_rec['PROSEQ'] . " della Pratica n° " . (int) substr($gesnum, 4) . "/" . substr($gesnum, 0, 4)
                            , "<br>E' stata allegata la Email al passo " . $propas_rec['PROSEQ'] . " della Pratica n° "
                            . (int) substr($gesnum, 4) . "/" . substr($gesnum, 0, 4) .
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
                    $_POST['event'] = $this->returnEvent;
                    $_POST['datiMail'] = $_POST['datiMail']['Dati'];
                    itaLib::openForm($model);
                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                    $model();
                    if ($this->refAccounts) {
                        $this->openRicerca(true, true);
                        if ($_POST[$this->gridElencoMail]['gridParam']['selrow']) {
                            TableView::setSelection($this->gridElencoMail, $_POST[$this->gridElencoMail]['gridParam']['selrow']);
                        }
                        if ($_POST[$this->gridElencoMailScarti]['gridParam']['selrow']) {
                            TableView::setSelection($this->gridElencoMailScarti, $_POST[$this->gridElencoMailScarti]['gridParam']['selrow']);
                        }
                    } else {
                        $this->openRicercaLocale();
                        if ($_POST[$this->gridElencoMailLocale]['gridParam']['selrow']) {
                            TableView::setSelection($this->gridElencoMailLocale, $_POST[$this->gridElencoMailLocale]['gridParam']['selrow']);
                        }
                    }
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
                            $this->openRicerca();
                        }
                        break;
                }
                break;
        }
    }

    public function close() {
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
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent() {
        Out::closeDialog($this->nameForm);
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
    }

    private function Azzera($valData = true, $clearToolbar = true) {
//        if ($valData === true) {
//            $data = date('Ymd', strtotime('-1 day', strtotime(date('Ymd'))));
//            Out::valore($this->nameForm . '_Dadata', $data);
//            Out::valore($this->nameForm . '_Adata', date('Ymd'));
//        }
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
        $SyncTab = $this->syncPramail('@SPORTELLO_DA_CONTROLLARE@');
        if ($SyncTab) {
            $arrayNumRicevute = $this->GetMsgSync($SyncTab);
            if ($arrayNumRicevute == false) {
                Out::msgStop("Attenzione!!!!!", "Errore nel caricamento automatico delle ricevute di consegna.");
            }
        }
        $whereFiltri = $this->getWhereFiltri();
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

                    $praMail_rec['MAILSTATO'] = 'CARICATA';
                    $nRows = ItaDB::DBUpdate($this->PRAM_DB, 'PRAMAIL', 'ROWID', $praMail_rec);
                    if ($nRows == -1) {
                        $errUpd = true;
                        break;
                    }

                    $emlDbMailBox = new emlDbMailBox();
                    $risultato = $emlDbMailBox->updateClassForRowId($SyncRec['ROWID'], "@SPORTELLO_CARICATO@");
                    if ($risultato === false) {
                        App::log($emlDbMailBox->getLastMessage());
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

    private function caricaTabellaScarti($daData, $aData) {
        $this->syncPramail('@SPORTELLO_SCARTATO@');
        $whereFiltri = $this->getWhereFiltri();
        if ($_POST['PRESALLEGATI'] != '') {
            $whereFiltri.=" AND ATTACHMENTS<>''";
        }
        if ($_POST['FROMADDR'] != '') {
            $whereFiltri.=" AND ".$this->PRAM_DB->strUpper('FROMADDR')." LIKE '%" . strtoupper($_POST['FROMADDR']) . "%'";
        }
        if ($_POST['PEC'] != '') {
            $whereFiltri.=" AND ".$this->PRAM_DB->strUpper('PECTIPO')." LIKE '%" . strtoupper($_POST['PEC']) . "%'";
        }
        if ($_POST['SUBJECT'] != '') {
            $whereFiltri.=" AND ".$this->PRAM_DB->strUpper('SUBJECT')." LIKE '%" . strtoupper($_POST['SUBJECT']) . "%'";
        }
        if ($_POST['DATA'] != '') {
            $whereFiltri.=" AND ".$this->PRAM_DB->strUpper('MSGDATE')." LIKE '" . strtoupper($_POST['DATA']) . "%'";
        }

        if ($daData != '' && $daData >= '20010101') {
            $whereFiltri.=" AND ".$this->PRAM_DB->subString('MSGDATE',1,8)." >= '" . $daData . "'";
        }
        if ($aData != '' && $aData >= '20010101') {
            $whereFiltri.=" AND ".$this->PRAM_DB->subString('MSGDATE',1,8)." <= '" . $aData . "'";
        }
        $elencoMailScarti = $this->caricaElencoMail('@SPORTELLO_SCARTATO@', $whereFiltri);
        $this->CaricaGriglia($this->gridElencoMailScarti, $elencoMailScarti);
    }

    private function caricaElencoMail($tipo, $whereFiltri) {
        $elencoMail = array();
        $ordinamento = $_POST['sidx'];
        if ($_POST['sidx'] == 'PRESALLEGATI' || $_POST['sidx'] == 'SEGNATURA') {
            $ordinamento = 'ATTACHMENTS';
        }
        if ($_POST['sidx'] == 'PEC') {
            $ordinamento = 'PECTIPO';
        }
        if ($ordinamento == '' || $_POST['sidx'] == 'DATA' || $_POST['sidx'] == 'ORA' || $_POST['sidx'] == 'RIFERIMENTO') {
            $ordinamento = 'MSGDATE';
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
        $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE CLASS ='$tipo' AND ACCOUNT='" . $this->refAccounts[0]['EMAIL'] . "' " . $whereFiltri . " ORDER BY $ordinamento $sord";
        $mailArchivio_tab = $this->emlLib->getGenericTab($sql);

        foreach ($mailArchivio_tab as $mailArchivio_rec) {
            $praMail_rec = $this->praLib->getPraMail($mailArchivio_rec['ROWID'], 'rowidarchivio');
            if ($this->visibilita['SPORTELLO'] != 0 || $this->visibilita['AGGREGATO'] != 0) {
                if ($praMail_rec['RICNUM'] == '' && $praMail_rec['GESNUM'] == '') {
                    continue;
                }
                if ($praMail_rec['RICNUM']) {
                    $sqlProric = "SELECT * FROM PRORIC WHERE RICNUM='" . $praMail_rec['RICNUM'] . "'";
                    if ($this->visibilita['SPORTELLO'] != 0) {
                        $sqlProric.=" AND RICTSP='" . $this->visibilita['SPORTELLO'] . "'";
                    }
                    if ($this->visibilita['AGGREGATO'] != 0) {
                        $sqlProric.=" AND RICSPA='" . $this->visibilita['AGGREGATO'] . "'";
                    }
                    $proric_rec = $this->praLib->getGenericTab($sqlProric);
                    if (!$proric_rec) {
                        continue;
                    }
                }
                if ($praMail_rec['GESNUM']) {
                    $sqlProges = "SELECT * FROM PROGES WHERE GESNUM='" . $praMail_rec['GESNUM'] . "'";
                    if ($this->visibilita['SPORTELLO'] != 0) {
                        $sqlProges.=" AND GESTSP='" . $this->visibilita['SPORTELLO'] . "'";
                    }
                    if ($this->visibilita['AGGREGATO'] != 0) {
                        $sqlProges.=" AND GESSPA='" . $this->visibilita['AGGREGATO'] . "'";
                    }
                    $proges_rec = $this->praLib->getGenericTab($sqlProges);
                    if (!$proges_rec) {
                        continue;
                    }
                }
            }

            $mailArchivio_rec = $this->PreparaRigaGriglia($mailArchivio_rec);
            $elencoMail[] = $mailArchivio_rec;
        }
        return $elencoMail;
    }


    private function PreparaRigaGriglia($mailArchivio_rec) {

        $metadata = unserialize($mailArchivio_rec["METADATA"]);
        App::log("Metadata");
        App::log(unserialize($mailArchivio_rec["METADATA"]));

        $icon_mail = "<span title = \"Messaggio letto.\" class=\"ita-icon ita-icon-apertagray-24x24\" style = \"float:left;display:inline-block;\"></span>";
        if ($mailArchivio_rec['READED'] == 0) {
            $icon_mail = "<span title = \"Messaggio da leggere.\" class=\"ita-icon ita-icon-chiusagray-24x24\" style = \"float:left;display:inline-block;\"></span>";
        }
        if ($mailArchivio_rec['INTEROPERABILE'] > 0) {
            $mailArchivio_rec['SEGNATURA'] = '<span title ="Interoperabilità" class="ita-icon ita-icon-flag-it-24x24"></span>';
        }
        $mailArchivio_rec["DATA"] = date('d/m/Y', strtotime(substr($mailArchivio_rec['MSGDATE'], 0, 8)));
        $mailArchivio_rec["ORA"] = substr($mailArchivio_rec['MSGDATE'], 8);
        $pec = $mailArchivio_rec['PECTIPO'];
        if ($pec != '') {
            $icon_mail = "<span title = \"PEC " . $pec . ", letto.\" class=\"ita-icon ita-icon-apertagreen-24x24\" style = \"float:left;display:inline-block;\"></span>";
            if ($mailArchivio_rec['READED'] == 0) {
                $icon_mail = "<span title = \"PEC " . $pec . ", da leggere.\" class=\"ita-icon ita-icon-chiusagreen-24x24\" style = \"float:left;display:inline-block;\"></span>";
            }
            $mailArchivio_rec["PEC"] = $pec;

            if ($metadata['emlStruct']['ita_PEC_info']['dati_certificazione']) {
                $mailArchivio_rec['FROMADDRORIG'] = '<p style="background:lightgreen;color: darkgreen;">' . $metadata['emlStruct']['ita_PEC_info']['dati_certificazione']['mittente'] . '</p>';
                $mailArchivio_rec['SUBJECT'] = $metadata['emlStruct']['ita_PEC_info']['dati_certificazione']['oggetto'];
            }
        }
        $mailArchivio_rec['PRESALLEGATI'] = $icon_mail;
        if ($mailArchivio_rec['ATTACHMENTS'] != '') {
            $mailArchivio_rec['PRESALLEGATI'] .= '<span title = "Presenza Allegati" style="margin:2px" class="ita-icon ita-icon-clip-16x16" style = \"display:inline-block;\"></span>';
        }

        $_POST['presente'] = '';
        $mailArchivio_rec = $this->elaboraRecordGriglia($mailArchivio_rec, 'KEYMAIL');
        $ini_tag = "<p style = 'font-weight:lighter;" . $_POST['presente'] . "'>";
        $fin_tag = "</p>";
        if ($mailArchivio_rec['READED'] == 0) {
            $ini_tag = "<p style = 'font-weight:900;" . $_POST['presente'] . "'>";
            $fin_tag = "</p>";
        }
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
        if ($accounts) {
            $this->refAccounts = $accounts;
            return true;
        }
        if ($this->visibilita['SPORTELLO']) {
            $Anatsp_rec = $this->praLib->GetAnatsp($this->visibilita['SPORTELLO'], 'codice');
            $Mail_account = $this->emlLib->getMailAccount($Anatsp_rec['TSPPEC']);
            if ($Mail_account['MAILADDR']) {
                $this->refAccounts = array(array("EMAIL" => $Mail_account['MAILADDR']));
                $titolo = "Elenco Mail - " . $this->visibilita['SPORTELLO_DESC'] . " - " . $this->refAccounts[0]['EMAIL'];
                Out::setAppTitle($this->nameForm, "<span>$titolo</span>");
                return true;
            }
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
        }
        return false;
    }

    private function scaricaPosta() {
        $htmlLog = "";
        $htmlErr = "";
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
        $retLogTabella = $this->caricaTabella();
        if ($retLogTabella) {
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
        Out::msgDialog("Ricezione messaggi", $htmlErr . $htmlLog);
    }

    private function analizzaMail($daScarta = false) {
        $this->Nascondi();
        $this->currMessage->parseEmlFileDeep();
        $retDecode = $this->currMessage->getStruct();
        $praMail_rec = $this->praLib->getPraMail($this->elemento['IDMAIL']);
        $risultato = unserialize($praMail_rec['ANALISIMAIL']);

        $this->elencoAllegati = $this->caricaElencoAllegati($retDecode);
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
        $url = utiDownload::getUrl("emlbody.html", $retDecode['DataFile'], false, true);
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
            Out::hide($this->nameForm . '_Carica');
            return;
        }
        if ($praMail_rec['RICNUM'] && $praMail_rec['ISANNULLAMENTO'] != 1) {
            $proges_rec = $this->praLib->GetProges($praMail_rec['RICNUM'], 'richiesta');
            if ($proges_rec) {
                Out::hide($this->nameForm . '_Carica');
                Out::hide($this->nameForm . '_AssegnaPasso');
//Out::show($this->nameForm . '_ConfermaVisione');
                Out::msgStop("ATTENZIONE!", "Il Procedimento dello Sportello Online n° "
                        . (int) substr($praMail_rec['RICNUM'], 4) . '/' . substr($praMail_rec['RICNUM'], 0, 4) . " è già stato caricato.<br>
                        Impossibile Ricaricarlo.");
                return;
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
        $this->elencoAllegatiOrig = $this->caricaElencoAllegati($messaggioOriginale);
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
        Out::valore($this->nameForm . '_DataOrig', date('Ymd', strtotime($messaggioOriginale['Date'])));
        Out::valore($this->nameForm . '_OraOrig', trim(date('H:i:s', strtotime($messaggioOriginale['Date']))));
        $datafile = '';
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
        $praMail_rec = $this->praLib->getPraMail($this->elemento['IDMAIL']);
        $praMail_rec['MAILSTATO'] = 'SCARTATA';
        $classe = '@SPORTELLO_SCARTATO@';
        if ($riabilita === true) {
            $classe = '@SPORTELLO_DA_CONTROLLARE@';
            $praMail_rec['MAILSTATO'] = 'ATTIVA';
        }
        $update_Info = 'Oggetto: set Scarta/riattiva Mail, id:' . $praMail_rec['IDMAIL'] . " valore:" . $praMail_rec['MAILSTATO'];
        $this->updateRecord($this->PRAM_DB, 'PRAMAIL', $praMail_rec, $update_Info);
        $emlDbMailBox = new emlDbMailBox();
        $risultato = $emlDbMailBox->updateClassForRowId($rowid, $classe);
        if ($risultato === false) {
            App::log($emlDbMailBox->getLastMessage());
        }
    }

    private function caricaTabEmailLocali() {
        $this->dettagliFile = array();
        $elencoFile = $this->GetFileList();
        //$this->syncPramailFromLocal($elencoFile);
        foreach ($elencoFile as $mail) {
            $struct_file = pathinfo($mail['FILENAME'], PATHINFO_DIRNAME) . "/" . pathinfo($mail['FILENAME'], PATHINFO_FILENAME) . ".info";
            if (file_exists($struct_file)) {
                $retDecode = unserialize(file_get_contents($struct_file));
            } else {
                $message = new emlMessage();
                $message->setEmlFile($mail['FILENAME']);
                $message->parseEmlFileDeep();
                $retDecode = $message->getStruct();
            }
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
            $elencoDatiMail['MITTENTE'] = $retDecode['From'][0]['address'];
            $elencoDatiMail['OGGETTO'] = $retDecode['Subject'];
            if ($retDecode['ita_PEC_info']['dati_certificazione']) {
                $elencoDatiMail['OGGETTO'] = $retDecode['ita_PEC_info']['dati_certificazione']['oggetto'];
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
            if ($retDecode['Attachments'] != '') {
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
        $this->clearCurrMessage();
        $this->currMailBox = new emlMailBox();
        $this->currMessage = $this->currMailBox->getMessageFromDb($_POST['rowid']);
        $this->elemento = $this->emlLib->getMailArchivio($_POST['rowid'], 'rowid');
        Out::tabSelect($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneMail");
        
        
        $this->analizzaMail($daScarta);
        $grid = $this->gridElencoMail;
        if ($this->elemento['CLASS'] == "@SPORTELLO_SCARTATO@") {
            $grid = $this->gridElencoMailScarti;
        }
        $record = $this->PreparaRigaGriglia($this->elemento);
        TableView::setRowData($grid, $_POST['rowid'], $record);
        Out::hide($this->divRis);
        Out::show($this->nameForm . '_divDettaglio');
        Out::show($this->nameForm . '_Elenca');
    }

    private function DettaglioLocale() {
        
        $this->Nascondi();
        Out::tabSelect($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneMail");
        $this->elementoLocale = $_POST['rowid'];
        $elemento = $this->dettagliFile[$this->elementoLocale];
        $this->currMessage = new emlMessage();
        $this->currMessage->setEmlFile($elemento['FILENAME']);
        $this->currMessage->parseEmlFileDeep();
        $retDecode = $this->currMessage->getStruct();

        $praMail_rec = $this->praLib->getPraMail(pathinfo($elemento['FILENAME'], PATHINFO_FILENAME));
        $risultato = unserialize($praMail_rec['ANALISIMAIL']);

        $this->elencoAllegati = $this->caricaElencoAllegati($retDecode);
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
        $url = utiDownload::getUrl("emlbody.html", $retDecode['DataFile'], false, true);
        $iframe = '<iframe src="' . $url . '" frameborder="0" width="99%" height="95%" id="' . $this->nameForm . '_emlBLFrame">
            <p>Contenuto non visualizzabile.....</p>
            </iframe>';
        Out::html($this->nameForm . '_divSoggetto', $iframe);
        Out::hide($this->divRis);
        Out::html($this->nameForm . '_divInfoMail', "");
        Out::hide($this->nameForm . '_divInfoMail');
        Out::show($this->nameForm . '_divDettaglio');
        Out::show($this->nameForm . '_Elenca');
        $this->decodSegnaturaCert($retDecode);
        Out::show($this->nameForm . '_AssegnaPasso');
        $this->decodInfoMail($risultato, $praMail_rec);
        Out::show($this->nameForm . '_Carica');
        if ($praMail_rec['RICNUM']) {
            $proges_rec = $this->praLib->GetProges($praMail_rec['RICNUM'], 'richiesta');
            if ($proges_rec) {
                Out::hide($this->nameForm . '_Carica');
                Out::hide($this->nameForm . '_AssegnaPasso');
                Out::msgStop("ATTENZIONE!", "Il Procedimento dello Sportello Online n° "
                        . (int) substr($praMail_rec['RICNUM'], 4) . '/' . substr($praMail_rec['RICNUM'], 0, 4) . " è già stato caricato.<br>
                        Impossibile Ricaricarlo.");
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
        if ($_POST[$this->nameForm . '_cbTutti'] == 1) {
            return '';
        }
        $where = array();
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

        if (count($where)) {
            $sql = " AND (" . implode(" OR ", $where) . ")";
        }
        return $sql;
    }

    private function setCertificazione() {
        if ($this->certificato) {
            $certificazione = "<br><br><b>Tipo: </b>" . $this->certificato['ita_PEC_info']['tipo'] . "<br>";
            $certificazione.="<b>Errore: </b>" . $this->certificato['ita_PEC_info']['errore'] . "<br>";
            $certificazione.="<b>Mittente: </b>" . $this->certificato['ita_PEC_info']['mittente'] . "<br>";
            $certificazione.="<b>Emittente: </b>" . $this->certificato['ita_PEC_info']['gestore-emittente'] . "<br>";
            $certificazione.="<b>Oggetto: </b>" . $this->certificato['ita_PEC_info']['oggetto'] . "<br>";
            $certificazione.="<b>Data e Ora: </b>" . $this->certificato['ita_PEC_info']['data'] . " - " . $this->certificato['ita_PEC_info']['ora'] . "<br>";
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

    private function caricaElencoAllegati($retDecode) {
        $allegati = array();
        $elementi = $retDecode['Attachments'];
        if ($elementi) {
            $incr = 1;
            foreach ($elementi as $elemento) {
                if ($elemento['FileName']) {
                    $icon = utiIcons::getExtensionIconClass($elemento['FileName'], 32);
                    $sizefile = $this->emlLib->formatFileSize(filesize($elemento['DataFile']));
                    $allegati[] = array(
                        'ROWID' => $incr,
                        'FileIcon' => "<span style = \"margin:2px;\" class=\"$icon\"></span>",
                        'DATAFILE' => $elemento['FileName'],
                        'FILE' => $elemento['DataFile'],
                        'FileSize' => $sizefile
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

        //$praMail_rec = $this->praLib->getPraMail($idMail, 'rowidarchivio');
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
                $mailArchivio_rec['RIFERIMENTO'] = '<p style="color:blue;font-weight:bold;">Integrazione Rich.: ' . $numero . $del . "<p>";
            } elseif ($risultato['isAnnullamento'] === true) {
                $numero = (int) substr($risultato['infoFrontOffice']['RICNUM'], 4, 6) . "/" . substr($risultato['infoFrontOffice']['RICNUM'], 0, 4);
                $del = ' del ' . substr($risultato['infoFrontOffice']['DATA'], 6, 2) . "/" . substr($risultato['infoFrontOffice']['DATA'], 4, 2) . "/" . substr($risultato['infoFrontOffice']['DATA'], 0, 4);
                //$mailArchivio_rec['RIFERIMENTO'] = '<p style="color:orange;font-weight:bold;">Annullamento Rich.: ' . $numero . $del . "<p>";
                $mailArchivio_rec['RIFERIMENTO'] = '<p style="color:orange;font-weight:bold;">Annullamento</p>';
            } else {
                $numero = (int) substr($Proric_rec['RICNUM'], 4, 6) . "/" . substr($Proric_rec['RICNUM'], 0, 4);
                $del = ' del ' . substr($Proric_rec['RICDRE'], 6, 2) . "/" . substr($Proric_rec['RICDRE'], 4, 2) . "/" . substr($Proric_rec['RICDRE'], 0, 4);
                //$mailArchivio_rec['RIFERIMENTO'] = 'Rich.On-Line: ' . $numero . $del;
                $mailArchivio_rec['RIFERIMENTO'] = 'Rich.On-Line: ';
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
            Out::html($this->nameForm . '_divInfoMail', $infoMail);
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
                Out::html($this->nameForm . '_divInfoMail', $infoMail);
                Out::show($this->nameForm . '_divInfoMail');
                Out::hide($this->nameForm . '_AssegnaPasso');
                Out::show($this->nameForm . '_Carica');
            } elseif ($risultato['isAnnullamento'] === true) {
                $Proric_rec = $risultato['infoFrontOffice']['PRORIC'];
                $infoMail = '<div class="ita-header ui-widget-header ui-corner-all" Title="Annullamento da Front Office">&nbsp;</div>';
                $infoMail .= '<pre style="font-size:1.2em;">';
                $infoMail .= 'Numero Richiesta da Annullare: ' . (int) substr($risultato['infoFrontOffice']['RICNUM'], 4, 6) . "/" . substr($risultato['infoFrontOffice']['RICNUM'], 0, 4) . "<BR>";
                $infoMail .= 'Del   : ' . substr($risultato['infoFrontOffice']['DATA'], 6, 2) . "/" . substr($risultato['infoFrontOffice']['DATA'], 4, 2) . "/" . substr($risultato['infoFrontOffice']['DATA'], 0, 4) . "<BR>";
                $infoMail .= '</pre><br>';
                Out::html($this->nameForm . '_divInfoMail', $infoMail);
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
                Out::html($this->nameForm . '_divInfoMail', $infoMail);
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
            Out::html($this->nameForm . '_divInfoMail', $infoMail);
            Out::show($this->nameForm . '_divInfoMail');
            Out::hide($this->nameForm . '_Carica');
            Out::hide($this->nameForm . '_AssegnaPasso');
//Out::show($this->nameForm . '_ConfermaVisione');
        } else {
            Out::show($this->nameForm . '_AssegnaPasso');
            Out::show($this->nameForm . '_Carica');
        }
    }

    private function CaricaGriglia($griglia, $record, $tipo = '1', $pageRows = '100000') {
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01 = new TableView(
                        $griglia,
                        array('arrayTable' => $record,
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

    private function selezionaDaScartare() {
        $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE CLASS ='@SPORTELLO_DA_CONTROLLARE@' AND ACCOUNT='" . $this->refAccounts[0]['EMAIL'] . "' ORDER BY MSGDATE DESC";
        $mail_tab = $this->emlLib->getGenericTab($sql);

        foreach ($mail_tab as $key => $mail_rec) {
            $mail_tab[$key]['DATAMESSAGGIO'] = date("d/m/Y", strtotime($mail_rec['MSGDATE']));
            $mail_tab[$key]['ORAMESSAGGIO'] = date("H:i:s", strtotime($mail_rec['MSGDATE']));
        }
        $colNames = array(
            "ROWID",
            "Mittente",
            "Data",
            "Ora",
            "Oggetto",
            "Account"
        );
        $colModel = array(
            array("name" => 'ROWID', "hidden" => "true", "key" => "true"),
            array("name" => 'FROMADDR', "width" => 245),
            array("name" => 'DATAMESSAGGIO', "width" => 75),
            array("name" => 'ORAMESSAGGIO', "width" => 75),
            array("name" => 'SUBJECT', "width" => 400),
            array("name" => 'ACCOUNT', "width" => 120)
        );
        include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
        proRic::proMultiselectGeneric(
                $mail_tab, $this->nameForm, 'DaScartare', 'Seleziona le Email da Scartare', $colNames, $colModel
        );
    }

    function tornaElenco() {
        if ($this->refAccounts) {
            $this->openRicerca(false, false);
            if ($_POST[$this->gridElencoMail]['gridParam']['selrow']) {
                TableView::setSelection($this->gridElencoMail, $_POST[$this->gridElencoMail]['gridParam']['selrow']);
            }
            if ($_POST[$this->gridElencoMailScarti]['gridParam']['selrow']) {
                TableView::setSelection($this->gridElencoMailScarti, $_POST[$this->gridElencoMailScarti]['gridParam']['selrow']);
            }
        } else {
            $this->openRicercaLocale();
            if ($_POST[$this->gridElencoMailLocale]['gridParam']['selrow']) {
                TableView::setSelection($this->gridElencoMailLocale, $_POST[$this->gridElencoMailLocale]['gridParam']['selrow']);
            }
        }
    }

}

?>
