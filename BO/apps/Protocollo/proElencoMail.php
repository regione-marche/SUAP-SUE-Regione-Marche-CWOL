<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEmailDate.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once (ITA_LIB_PATH . '/QXml/QXml.class.php');
include_once (ITA_LIB_PATH . '/itaPHPMail/itaMime.class.php');

function proElencoMail() {
    $proElencoMail = new proElencoMail();
    $proElencoMail->parseEvent();
    return;
}

class proElencoMail extends itaModel {

    public $PROT_DB;
    public $nameForm;
    public $divRis;
    public $proLib;
    public $gridElencoMail;
    public $gridAllegati;
    public $dettagliFile;
    public $elencoAllegati;
    public $nElemento;
    public $emailTempPath;
    public $currMailId;
    public $Proric_rec;
    public $destinatari;
    public $modoFiltro;
    public $returnModel;
    public $returnEvent;
    private $returnOnClose;

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->nameForm = "proElencoMail";
            $this->emailTempPath = $this->proLib->SetDirectory('', "PEC") . "/";
            // DATI SALVATI IN SESSION //
            $this->dettagliFile = App::$utente->getKey($this->nameForm . "_dettagliFile");
            $this->elencoAllegati = App::$utente->getKey($this->nameForm . "_elencoAllegati");
            $this->Proric_rec = App::$utente->getKey($this->nameForm . "_Proric_rec");
            $this->destinatari = App::$utente->getKey($this->nameForm . "_destinatari");
            $this->nElemento = App::$utente->getKey($this->nameForm . "_nElemento");
            $this->currMailId = App::$utente->getKey($this->nameForm . "_currMailId");
            $this->modoFiltro = App::$utente->getKey($this->nameForm . "_modoFiltro");
            $this->returnModel = App::$utente->getKey($this->nameForm . "_returnModel");
            $this->returnEvent = App::$utente->getKey($this->nameForm . "_returnEvent");
            $this->returnOnClose = App::$utente->getKey($this->nameForm . '_returnOnClose');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_dettagliFile", $this->dettagliFile);
            App::$utente->setKey($this->nameForm . "_elencoAllegati", $this->elencoAllegati);
            App::$utente->setKey($this->nameForm . "_Proric_rec", $this->Proric_rec);
            App::$utente->setKey($this->nameForm . "_destinatari", $this->destinatari);
            App::$utente->setKey($this->nameForm . "_nElemento", $this->nElemento);
            App::$utente->setKey($this->nameForm . "_currMailId", $this->currMailId);
            App::$utente->setKey($this->nameForm . "_modoFiltro", $this->modoFiltro);
            App::$utente->setKey($this->nameForm . "_returnModel", $this->returnModel);
            App::$utente->setKey($this->nameForm . "_returnEvent", $this->returnEvent);
            App::$utente->setKey($this->nameForm . '_returnOnClose', $this->returnOnClose);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        $this->divRis = $this->nameForm . "_divRisultato";
        $this->griElencoMail = $this->nameForm . "_gridElencoMail";
        $this->gridAllegati = $this->nameForm . "_gridAllegati";
        switch ($_POST['event']) {
            case 'openform':
                $this->modoFiltro = "TUTTO";
                $this->returnModel = $_POST['returnModel'];
                $this->returnEvent = $_POST['returnEvent'];
                if ($_POST['modoFiltro']) {
                    $this->modoFiltro = $_POST['modoFiltro'];
                }
                if ($_POST["returnOnClose"]) {
                    $this->returnOnClose = true;
                }
                if ($this->modoFiltro == "DIRECT") {
                    $allegatoEml = $this->checkEml();

                    if ($allegatoEml) {
                        $_POST['directMailFile'] = $allegatoEml;
                    }

                    if ($this->caricaTabella(true, $_POST['directMailFile'])) {
                        Out::show($this->nameForm);
                        Out::hide($this->nameForm . '_divRisultato');
                        Out::show($this->nameForm . '_divDettaglio');
                        $this->nElemento = 0;
                        $this->Dettaglio();
                    };
                } else {
                    $this->caricaTabella();
                    Out::show($this->nameForm);
                    Out::show($this->nameForm . '_divRisultato');
                    Out::hide($this->nameForm . '_divDettaglio');
                    Out::hide($this->nameForm . '_Elenca');
                    Out::hide($this->nameForm . '_Protocolla');
                    Out::hide($this->nameForm . '_divInfoMail');
                    Out::show($this->nameForm . '_Modifica');
                    Out::show($this->nameForm . '_Abbina');
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Protocolla':
                        $subPath = md5($this->currMailId);
                        $tempPath = itaLib::createAppsTempPath($subPath);
                        $retDecode = itaMime::parseMail($this->dettagliFile[$this->nElemento]['FILENAME'], 1, $tempPath, 1);
                        $Soggetto = file_get_contents($retDecode['DataFile']);
                        $model = $this->returnModel;
                        $event = $this->returnEvent;
                        if (!$event) {
                            $event = 'openform';
                        }
                        $_POST['event'] = $event;
                        $_POST['tipoProt'] = 'A';
                        $_POST['datiMail'] = $this->dettagliFile[$this->nElemento];
                        $_POST['datiMail']['ELENCOALLEGATI'] = $this->elencoAllegati;
                        if ($this->Proric_rec) {
                            $_POST['datiMail']['PRORIC_REC'] = $this->Proric_rec;
                            $_POST['datiMail']['destinatari'] = $this->destinatari;
                            $_POST['datiMail']['Soggetto'] = $Soggetto;
                        } else {
                            $_POST['datiMail']['PRORIC_REC'] = null;
                            $_POST['datiMail']['destinatari'] = null;
                            $_POST['datiMail']['Soggetto'] = null;
                        }

                        if ($event == 'openform') {
                            itaLib::openForm($model);
                        }
                        Out::hide($this->nameForm);
                        $objModel = itaModel::getInstance($model);
                        $objModel->setEvent($event);
                        $objModel->parseEvent();
                        $this->close();
                        break;
                    case $this->nameForm . '_Elenca':
                        $this->deleteTempDataFiles($this->currMailId);
                        $this->elencoAllegati = array();
                        TableView::disableEvents($this->gridAllegati);
                        TableView::clearGrid($this->gridAllegati);
                        Out::show($this->nameForm . '_divRisultato');
                        Out::hide($this->nameForm . '_divDettaglio');
                        Out::hide($this->nameForm . '_Elenca');
                        Out::hide($this->nameForm . '_divInfoMail');
                        Out::html($this->nameForm . '_divInfoMail', '');
                        Out::hide($this->nameForm . '_Protocolla');
                        //                        Out::show($this->nameForm.'_Modifica');
                        //                        Out::show($this->nameForm.'_Abbina');
                        break;
//                    case $this->nameForm . '_Ricevi':
//                        include_once(ITA_LIB_PATH . '/itaPHPMail/itaPOP3.class.php');
//                        $pop3 = new itaPOP3(array(
//                                    'hostname' => "pop3s.pec.mail-certificata.eu",
//                                    'port' => 995,
//                                    'tls' => 1,
//                                    'realm' => "",
//                                    'workstation' => "",
//                                    'authentication_mechanism' => "USER",
//                                    'join_continuation_header_lines' => 1
//                                ));
////                        $pop3 = new itaPOP3(array(
////                                    'hostname' => "mail.italsoft.eu",
////                                    'port' => 110,
////                                    'tls' => 0,
////                                    'realm' => "",
////                                    'workstation' => "",
////                                    'authentication_mechanism' => "USER",
////                                    'join_continuation_header_lines' => 1
////                                ));
////                        $log = "";
//                        $pop3->open();
////                        $log .= "<br><br>POP3 Open: " . $pop3->getMessage() . "<br>";
//
//                        $user = "certificata@pec.italsoft-mc.it";
//                        $password = "certificata1";
//                        if ($pop3->login($user, $password)) {
//                            $statistics = $pop3->statistics();
//                            App::log($statistics);
//                            if ($statistics['messages'] > 0) {
////                                $listMessages = $pop3->listMessages("", true);
//                                if ($pop3->GetConnectionName()) {
////                                    $connection_name = $pop3->getMessage();
//                                    for ($message = 1; $message <= $statistics['messages']; $message++) {
////                                        Out::msgInfo("POP3 1", $message);
////                                        $message_file = 'pop3://' . $connection_name . '/' . $message;
//                                        if ($pop3->OpenMessage($message, -1) == false) {
//                                            Out::msgStop("Attenzione", $pop3->getMessage());
//                                            break;
//                                        } else {
//                                            $mail = $pop3->GetMessageData();
////                                            $EmlName = "/users/pc/dos2ux/pop3_$message.eml";
//                                            $EmlName = $this->emailTempPath . md5(rand() * time()) . ".eml";
//                                            $ptr = fopen($EmlName, 'wb');
//                                            fwrite($ptr, utf8_encode($mail['message']));
//                                            fclose($ptr);
//                                            //$risultato=$pop3->DeleteMessage($message);
////                                            if ($risultato) {
////                                                
////                                            }
//                                            /*
//                                              include_once(ITA_LIB_PATH . '/pop3/mime_parser.php');
//                                              $mime = new mime_parser_class;
//
//                                              $mime->decode_bodies = 1;
//
//                                              $parameters = array(
//                                              'File' => $message_file,
//                                              'SkipBody' => 0,
//                                              );
//                                              $success = $mime->Decode($parameters, $decoded);
//                                              //                                            Out::msgInfo("POP3 ", print_r($success, true));
//                                              //                                            Out::msgInfo("POP3 ", print_r($parameters, true));
//                                              //                                            Out::msgInfo("POP3 ", print_r($decoded, true));
//                                              //                                            break;
//                                              if (!$success) {
//                                              Out::msgStop("Attenzione", "MIME message decoding error: " . $mime->error);
//                                              } else {
//                                              Out::msgInfo("POP3 2", print_r($decoded[0], true));
//                                              var_dump($decoded[0]);
//                                              if ($mime->Analyze($decoded[0], $results)) {
//                                              Out::msgInfo("POP3 3", print_r($results, true));
//                                              var_dump($results);
//                                              } else {
//                                              Out::msgStop("Attenzione", "MIME message analyse error: " . $mime->error);
//                                              }
//                                              }
//                                              break;
//                                             * 
//                                             */
//                                        }
//                                    }
//                                } else {
//                                    Out::msgStop("Attenzione!", $pop3->getMessage());
//                                }
//                            }
//                        } else {
//                            Out::msgStop("Attenzione!", "Impossibile connettersi con l'account di posta.");
//                        }
//                        $pop3->close();
//                        $this->caricaTabella();
//                        $log .= "POP3 Login: " . $pop3->getMessage() . "<br>";
//
//                        $ret = $pop3->statistics();
//                        if ($ret) {
//                            $log .= "POP3 Statistics: " . "Ci sono: " . $ret['messages'] . " messaggi. Dimensione: " . $ret['size'] . " bytes.<br>";
//                        } else {
//                            $log .= "POP3 Statistics: " . $pop3->getMessage() . "<br>";
//                        }
//
//                        $ret = $pop3->listMessages("", true);
//                        $log .= "POP3 List 1: <br><pre>" . print_r($ret, true) . "</pre><br>";
//
//                        $ret = $pop3->deleteMessage("1");
//                        $log .= "POP3 delete: " . $pop3->getMessage() ."<br>";
//
//                        $ret = $pop3->listMessages("", true);
//                        $log .= "POP3 List 2: <br><pre>" . print_r($ret, true) . "</pre><br>";
//
//                        $ret = $pop3->resetDeleteMessage();
//                        $log .= "POP3 Reset delete: " . $pop3->getMessage() . "<br>";
//
//                        $ret = $pop3->listMessages("", true);
//                        $log .= "POP3 List 3: <br><pre>" . print_r($ret, true) . "</pre><br>";
//                        
//                        $pop3->close();
//                        $log .= "POP3 Close: " . $pop3->getMessage();
//
//                        Out::msgInfo("POP3", $log);
                        break;
                    case 'close-portlet':
                        //$this->returnToParent(true);
                        if ($this->returnOnClose) {
                            $_POST = array();
                            $_POST['event'] = $this->returnEvent;
                            $_POST['model'] = $this->returnModel;
                            $_POST['returnId'] = "close-portlet";
                            $_POST['uploadedFile'] = '';
                            $_POST['file'] = '';
                            $returnObj = $this->returnModelOrig ? itaModel::getInstance($this->returnModelOrig, $this->returnModel) : itaModel::getInstance($this->returnModel);
                            $returnObj->parseEvent();
                            $this->returnToParent();
                        }
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    
                }
                break;
            case 'addGridRow':
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
            case $this->nameForm . "_returnUploadFile":
                $randName = md5(rand() * time()) . "." . pathinfo($_POST['uploadedFile'], PATHINFO_EXTENSION);
                if (!@copy($_POST['uploadedFile'], $this->emailTempPath . $randName)) {
                    Out::msgStop("Attenzione", "salvataggio file:" . $_POST['uploadedFile'] . " fallito.");
                } else {
                    $this->caricaTabella();
                }
                break;
            case 'delGridRow':
                if (!@unlink($this->dettagliFile[$_POST['rowid']]['FILENAME'])) {
                    Out::msgStop("Cancellazione Mail", "Errore in cancellazione file.");
                }
                $this->caricaTabella();
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAllegati :
                        $elementi = array();
                        $allegati = array();
                        if ($this->dettagliFile[$this->nElemento]['ALLEGATI'] != '') {
                            $elementi = explode("|", $this->dettagliFile[$this->nElemento]['ALLEGATI']);
                            $incr = 1;
                            foreach ($elementi as $elemento) {
                                $allegati[] = array('ROWID' => $incr, 'FILENAME' => $elemento);
                                $incr++;
                            }
                        }
                        $tableSortOrder = $_POST['sord'];
                        $ita_grid01 = new TableView($this->gridAllegati, array('arrayTable' => $allegati, 'rowIndex' => 'idx'));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows(15);
                        $ita_grid01->clearGrid($this->gridAllegati);
                        $ita_grid01->getDataPage('json');
                        break;
                    case $this->griElencoMail :
                        $tableSortOrder = $_POST['sord'];
                        $ita_grid01 = new TableView($this->griElencoMail, array('arrayTable' => $this->dettagliFile, 'rowIndex' => 'idx'));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows(15);
                        $ita_grid01->clearGrid($this->griElencoMail);
                        $ita_grid01->getDataPage('json');
                        break;
                }
                break;
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->griElencoMail :
                        Out::hide($this->nameForm . '_divRisultato');
                        Out::show($this->nameForm . '_divDettaglio');
                        Out::show($this->nameForm . '_Elenca');
                        Out::show($this->nameForm . '_Protocolla');
                        $this->nElemento = $_POST['rowid'];
                        $this->Dettaglio();
                        break;
                    case $this->gridAllegati :
                        $FileAllegato = $this->elencoAllegati[$_POST['rowid'] - 1]['FILENAME'];
                        $FileDati = $this->elencoAllegati[$_POST['rowid'] - 1]['DATAFILE'];
                        Out::openDocument(utiDownload::getUrl($FileAllegato, $FileDati));
                        break;
                }
                break;
        }
    }

    public function close() {
        $this->deleteTempDataFiles($this->currMailId);
        App::$utente->removeKey($this->nameForm . '_dettagliFile');
        App::$utente->removeKey($this->nameForm . '_elencoAllegati');
        App::$utente->removeKey($this->nameForm . '_nElemento');
        App::$utente->removeKey($this->nameForm . '_currMailId');
        App::$utente->removeKey($this->nameForm . '_destinatari');
        App::$utente->removeKey($this->nameForm . '_Proric_rec');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnOnClose');
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('proGest');
    }

    function checkEml() {
        $currMailId = $_POST['directMailFile']['MESSAGE-ID'];
        $subPath = md5($currMailId);
        $tempPath = itaLib::createAppsTempPath($subPath);
        $retDecode = itaMime::parseMail($_POST['directMailFile'], 1, $tempPath, 1);
        $elencoAllegati = array();
        if ($retDecode['Attachments']) {
            $elementi = $retDecode['Attachments'];
            $incr = 1;
            foreach ($elementi as $elemento) {
                $ext = pathinfo($elemento['FileName'], PATHINFO_EXTENSION);
                if ($ext == 'eml') {
                    $mail = $elemento['DataFile'];
                    break;
                }
            }
        }
        return $mail;
    }

    function caricaTabella($ricaricaElenco = true, $directMailFile = null) {
        if ($ricaricaElenco) {
            $elencoFile = array();
            $elencoDatiMail = array();
            $elementiFile = array();
            $this->dettagliFile = array();
            if ($directMailFile == null) {
                $elencoFile = $this->GetFileList();
            } else {
                $elencoFile = $this->GetFileFromPath($directMailFile);
            }

            foreach ($elencoFile as $mail) {
                $retDecode = itaMime::parseMail($mail['FILENAME'], 1, "", 1);
                $elencoDatiMail['MESSAGE-ID'] = $retDecode['Message-Id'];
                $elencoDatiMail['FILENAME'] = $mail['FILENAME'];
                $ar1 = array();

                $elencoDatiMail['ALLEGATI'] = "";
                foreach ($retDecode['Attachments'] as $attach) {
                    $ar1[] = $attach['FileName'];
                }
                $elencoDatiMail['ALLEGATI'] = implode("|", $ar1);

                if ($elencoDatiMail['ALLEGATI'] != '') {
                    $elencoDatiMail['PRESALLEGATI'] = '<span class="ita-icon ita-icon-clip-16x16"></span>';
                } else {
                    $elencoDatiMail['PRESALLEGATI'] = '';
                }
                $elencoDatiMail['MITTENTE'] = $retDecode['From'][0]['address'];
                $elencoDatiMail['OGGETTO'] = $retDecode['Subject'];
                $decodedDate = utiEmailDate::eDate2Date($retDecode['Date']);
                $elencoDatiMail["DATA"] = $decodedDate['date'];
                $elencoDatiMail["ORA"] = $decodedDate['time'];
                $fl_add = false;
                if ($this->modoFiltro == 'TUTTO' || $this->modoFiltro == 'DIRECT') {
                    $fl_add = true;
                } else if ($this->modoFiltro == 'PRATICHE') {
                    if (strpos($elencoDatiMail['OGGETTO'], "Richiesta Procedimento Amministrativo.") === 0) {
                        $fl_add = true;
                    }
                }
                if ($fl_add) {
                    $this->dettagliFile[] = $elencoDatiMail;
                }
            }
        }
        $this->dettagliFile = $this->proLib->array_sort($this->dettagliFile, 'DATA', SORT_DESC);
        $ita_grid01 = new TableView($this->griElencoMail, array('arrayTable' => $this->dettagliFile, 'rowIndex' => 'idx'));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(15);
        TableView::enableEvents($this->griElencoMail);
        TableView::clearGrid($this->griElencoMail);
        $ita_grid01->getDataPage('json');
        return count($this->dettagliFile);
    }

    function GetFileFromPath($mailFile) {
        if (!@file_exists($mailFile)) {
            return false;
        }
        $retListGen = array();
        $rowid = 1;
        $retListGen[$rowid] = array(
            'ROWID' => $rowid,
            'FILENAME' => $mailFile
        );

        return $retListGen;
    }

    function GetFileList() {
//        App::log($this->emailTempPath);
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

    function Dettaglio() {
        $elementi = array();
        $allegati = array();
        $this->Proric_rec = array();
        $this->destinatari = array();
        $this->currMailId = $this->dettagliFile[$this->nElemento]['MESSAGE-ID'];
        $subPath = md5($this->currMailId);
        $tempPath = itaLib::createAppsTempPath($subPath);

        $retMime = '';
        $retDecode = itaMime::parseMail($this->dettagliFile[$this->nElemento]['FILENAME'], 1, $tempPath, 0);
        $this->elencoAllegati = array();

        if ($retDecode['Attachments']) {
            $elementi = $retDecode['Attachments'];
            $incr = 1;
            foreach ($elementi as $elemento) {
                if ($elemento['FileName']) {
                    $this->elencoAllegati[] = array('ROWID' => $incr, 'FILENAME' => $elemento['FileName'], 'DATAFILE' => $elemento['DataFile']);
                    $incr++;
                }
            }
        }
        $ita_grid01 = new TableView($this->gridAllegati, array('arrayTable' => $this->elencoAllegati, 'rowIndex' => 'idx'));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(15);
        TableView::enableEvents($this->gridAllegati);
        TableView::clearGrid($this->gridAllegati);
        $ita_grid01->getDataPage('json');
        Out::valore($this->nameForm . '_Mittente', $this->dettagliFile[$this->nElemento]['MITTENTE']);
        Out::valore($this->nameForm . '_Oggetto', $this->dettagliFile[$this->nElemento]['OGGETTO']);
        Out::valore($this->nameForm . '_Data', $this->dettagliFile[$this->nElemento]['DATA']);
        Out::valore($this->nameForm . '_Ora', $this->dettagliFile[$this->nElemento]['ORA']);
        $pre_a = "";
        $pre_c = "";
        if ($retDecode['Type'] == 'text') {
            $pre_a = '<pre style="font-size:1.4em;">';
            $pre_c = '</pre>';
        }
        $Soggetto = file_get_contents($retDecode['DataFile']);
        Out::html($this->nameForm . '_divSoggetto', $pre_a . $Soggetto . $pre_c);
        Out::html($this->nameForm . '_divInfoMail', "");
        $this->Proric_rec = $this->ControlloValiditaMail($this->elencoAllegati);

        if ($this->Proric_rec) {
            $this->destinatari = $this->getDestinatariRichiesta($this->Proric_rec);
            $infoMail = '<div class="ita-header ui-widget-header ui-corner-all">Procedimento</div>';
            $infoMail .= '<pre style="font-size:1.2em;">';
            $infoMail .= 'Numero: ' . substr($this->Proric_rec['RICNUM'], 4, 6) . "/" . substr($this->Proric_rec['RICNUM'], 0, 4) . "<BR>";
            $infoMail .= 'Del   : ' . substr($this->Proric_rec['RICDRE'], 6, 2) . "/" . substr($this->Proric_rec['RICDRE'], 4, 2) . "/" . substr($this->Proric_rec['RICDRE'], 0, 4) . "<BR>";
            $infoMail .= '</pre><br>';
            Out::html($this->nameForm . '_divInfoMail', $infoMail);
            Out::show($this->nameForm . '_divInfoMail');
        }
    }

    function AssegnaProtocollo($numero, $anno) {
        if ($numero != '' && $anno != '') {
            $this->elencoFile[$this->nElemento]['PROTOCOLLO'] = $anno . $numero;
        }
    }

//    function parseMail($mailFile, $decode_bodies = 1, $saveBody = "", $SkipBody = 0) {
//        include_once(ITA_LIB_PATH . '/mimeparser/rfc822_addresses.php');
//        include_once(ITA_LIB_PATH . '/mimeparser/mime_parser.php');
//        $results = array();
//        $mime = new mime_parser_class;
//        $mime->mbox = 0;
//        $mime->decode_bodies = $decode_bodies;
//        $mime->ignore_syntax_errors = 1;
//        $mime->track_lines = 1;
//        $parameters = array();
//        $parameters['File'] = $mailFile;
//
//        $parameters['SkipBody'] = $SkipBody;
//        if ($saveBody) {
//            $parameters['SaveBody'] = $saveBody;
//        }
//        if (!$mime->Decode($parameters, $decoded)) {
//            return false;
//        } else {
//            $results['Message-Id'] = $decoded[0]['Headers']['message-id:'];
//            $results['Message-Id'] = substr($results['Message-Id'], 1, strlen($results['Message-Id']) - 2);
//            $results['mailDecode'] = $decoded[0];
//            if ($mime->Analyze($decoded[0], $analyzed)) {
//                $results['mailAnalyze'] = $analyzed;
//                $Soggetto = file_get_contents($results['fmailAnalyze']['DataFile']);
//                $elementi = explode('begin ', $Soggetto);
//                $new_soggetto = '';
//                foreach ($elementi as $key => $elemento) {
//                    if (strpos($elemento, 'end') == 0) {
//                        $new_soggetto = $elemento;
//                    } else {
//                        $testata = explode(" ", substr($elemento, 0, strpos($elemento, chr(10)) - 1));
//                        $nome = $testata[1];
//                        $corpo = substr($elemento, strpos($elemento, chr(10)) + 1);
//                        $corpo = substr($corpo, 0, strpos($corpo, 'end'));
//                        $corpo = str_replace(chr(13), '', $corpo);
//
//
//                        $fp = fopen($saveBody . "/uudecode_" . $key, 'wb');
//                        fwrite($fp, convert_uudecode($corpo));
//                        fclose($fp);
//                        $results['mailAnalyze']['Attachments'][] = array('DataFile' => $saveBody . "/uudecode_" . $key,
//                            'FileName' => $nome,
//                            'FileDisposition' => "attachment"
//                        );
//                    }
//                }
//                //file_put_contents($results['mailAnalyze']['DataFile'], $new_soggetto);
//                return $results;
//            } else {
//                return false;
//            }
//        }
//    }

    function deleteTempDataFiles($currMailId) {
        if ($currMailId) {
            return itaLib::deleteAppsTempPath(md5($currMailId));
        }
        return true;
    }

    function decodeSoggettoRichiesta($Soggetto) {
        $Proric_rec = array();
        $Proric_rec['RICNUM'] = $this->estraiDaChiave($Soggetto, "Numero Procedimento: ");
        $Proric_rec['RICNUM'] = substr($Proric_rec['RICNUM'], 7, 4) . substr($Proric_rec['RICNUM'], 0, 6);
        $Proric_rec['RICDRE'] = $this->estraiDaChiave($Soggetto, "Data Procedimento: ");
        $Proric_rec['RICSET'] = $this->estraiDaChiave($Soggetto, "Descrizione Settore: ");
        $Proric_rec['RICSER'] = $this->estraiDaChiave($Soggetto, "Descrizione Servizio: ");
        $Proric_rec['RICOPE'] = $this->estraiDaChiave($Soggetto, "Unita Operativa: ");
        $Proric_rec['RICRES'] = $this->estraiDaChiave($Soggetto, "Responsabile Procedimento: ");
        $Proric_rec['RICFIS'] = $this->estraiDaChiave($Soggetto, "Codice Fiscale: ");
        $Proric_rec['RICCOG'] = $this->estraiDaChiave($Soggetto, "Cognome: ");
        $Proric_rec['RICNOM'] = $this->estraiDaChiave($Soggetto, "Nome: ");
        $Proric_rec['RICANA'] = $this->estraiDaChiave($Soggetto, "Codice Richiedente: ");
        return $Proric_rec;
    }

    function decodeXMLRichiesta($fileXml) {
        $Proric_rec = array();
        $xmlObj = new QXML;
        $xmlObj->setXmlFromFile($fileXml);
        $arrayXml = $xmlObj->getArray();
        $Proric_rec['RICNUM'] = $arrayXml['PRORIC']['RICNUM']['@textNode'];
        $Proric_rec['RICDRE'] = $arrayXml['PRORIC']['RICDRE']['@textNode'];
        $Proric_rec['RICPRO'] = $arrayXml['PRORIC']['RICPRO']['@textNode'];
        $Proric_rec['RICRES'] = $arrayXml['PRORIC']['RICRES']['@textNode'];
        $Proric_rec['RICANA'] = $arrayXml['PRORIC']['RICANA']['@textNode'];

        $Proric_rec['RICSOG'] = $arrayXml['PRORIC']['RICSOG']['@textNode'];

        $Proric_rec['RICCOG'] = $arrayXml['PRORIC']['RICCOG']['@textNode'];
        $Proric_rec['RICNOM'] = $arrayXml['PRORIC']['RICNOM']['@textNode'];
        $Proric_rec['RICVIA'] = $arrayXml['PRORIC']['RICVIA']['@textNode'];
        $Proric_rec['RICCAP'] = $arrayXml['PRORIC']['RICCAP']['@textNode'];
        $Proric_rec['RICCOM'] = $arrayXml['PRORIC']['RICCOM']['@textNode'];
        $Proric_rec['RICPVR'] = $arrayXml['PRORIC']['RICPVR']['@textNode'];

        $Proric_rec['RICNAZ'] = $arrayXml['PRORIC']['RICNAZ']['@textNode'];

        $Proric_rec['RICFIS'] = $arrayXml['PRORIC']['RICFIS']['@textNode'];

        $Proric_rec['RICNAS'] = $arrayXml['PRORIC']['RICNAS']['@textNode'];

        $Proric_rec['RICSET'] = $arrayXml['PRORIC']['RICSET']['@textNode'];
        $Proric_rec['RICSER'] = $arrayXml['PRORIC']['RICSER']['@textNode'];
        $Proric_rec['RICOPE'] = $arrayXml['PRORIC']['RICOPE']['@textNode'];
        return $Proric_rec;
    }

    function getDestinatariRichiesta($Proric_rec) {

        try {
            $PRAM_DB = ItaDB::DBOpen('PRAM');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $destinatari = array();
        //Resp. procedimento.....
        if ($Proric_rec['RICRES']) {
            $sql = "SELECT * FROM ANANOM WHERE NOMRES='" . $Proric_rec['RICRES'] . "'";
            $Ananom_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
            if ($Ananom_rec) {
                $destinatari["PROCEDIMENTO"] = $Ananom_rec['NOMDEP'];
            }
        }
        // Resp. Untità.....
        if ($Proric_rec['RICOPE']) {
            $sql = "SELECT * FROM ANAUNI
                        LEFT OUTER JOIN ANANOM ANANOM ON ANAUNI.UNIRES=ANANOM.NOMRES
                        WHERE
                            ANAUNI.UNISET='" . $Proric_rec['RICSET'] . "' AND
                            ANAUNI.UNISER='" . $Proric_rec['RICSER'] . "' AND
                            ANAUNI.UNIOPE='" . $Proric_rec['RICOPE'] . "' AND
                            ANAUNI.UNIDAP='' AND ANAUNI.UNIAPE=''";
            $Ananom_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
            if ($Ananom_rec) {
                $destinatari["UNITAOPERATIVA"] = $Ananom_rec['NOMDEP'];
            }
        }

        // Resp. servizio.......
        if ($Proric_rec['RICSER']) {
            $sql = "SELECT * FROM ANAUNI
                        LEFT OUTER JOIN ANANOM ANANOM ON ANAUNI.UNIRES=ANANOM.NOMRES
                        WHERE
                            ANAUNI.UNISET='" . $Proric_rec['RICSET'] . "' AND
                            ANAUNI.UNISER='" . $Proric_rec['RICSER'] . "' AND
                            ANAUNI.UNIOPE='' AND ANAUNI.UNIADD='' AND ANAUNI.UNIAPE=''";
            $Ananom_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
            if ($Ananom_rec) {
                $destinatari["SERVIZIO"] = $Ananom_rec['NOMDEP'];
            }
        }

        // Resp. Settore.......
        if ($Proric_rec['RICSET']) {
            $sql = "SELECT * FROM ANAUNI
                    LEFT OUTER JOIN ANANOM ANANOM ON ANAUNI.UNIRES=ANANOM.NOMRES
                    WHERE
                        ANAUNI.UNISET='" . $Proric_rec['RICSET'] . "' AND
                        ANAUNI.UNISER='' AND ANAUNI.UNIOPE='' AND ANAUNI.UNIADD='' AND ANAUNI.UNIAPE=''";
            $Ananom_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
            if ($Ananom_rec) {
                $destinatari["SETTORE"] = $Ananom_rec['NOMDEP'];
            }
        }
        return $destinatari;
    }

    function estraiDaChiave($Soggetto, $Chiave) {
        $Chiave = $Chiave . "@";
        $text_right = (strpos($Soggetto, $Chiave) !== false) ? substr($Soggetto, strpos($Soggetto, $Chiave) + strlen($Chiave)) : "";
        $text = (strpos($text_right, "@") !== false) ? substr($text_right, 0, strpos($text_right, "@")) : "";
        return $text;
    }

    public function ControlloValiditaMail($allegati) {
        foreach ($allegati as $key => $elemento) {
            if (strpos($elemento['FILENAME'], "XMLINFO") !== false) {
                $fileXML = $elemento['DATAFILE'];
                break;
            }
        }
        if ($fileXML != "") {
            $xmlObj = new QXML;
            $xmlObj->setXmlFromFile($fileXML);
            $arrayXml = $xmlObj->getArray();
            $Proric_rec_xml = $arrayXml['ROOT']['PRORIC'];

            foreach ($Proric_rec_xml as $key => $value) {
                $Proric_rec[$key] = $value['@textNode'];
            }
            foreach ($Proric_rec as $key => $campo) {
                if ($key == 'RICNUM') {
                    $chiave = $campo;
                    break;
                }
            }
            if (strlen($chiave) != 10) {
                return false;
            } else {
                return $Proric_rec;
            }
        } else {
            return false;
        }
    }

}

?>
