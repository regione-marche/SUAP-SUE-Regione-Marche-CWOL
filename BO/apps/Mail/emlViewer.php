<?php

/**
 *
 * VISUALIZZATORE EMAIL
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    04.11.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiEmailDate.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlRic.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
include_once (ITA_LIB_PATH . '/itaPHPMail/itaMime.class.php');
include_once ITA_BASE_PATH . '/apps/Utility/utiIcons.class.php';

function emlViewer() {
    $emlViewer = new emlViewer();
    $emlViewer->parseEvent();
    return;
}

class emlViewer extends itaModel {

    public $nameForm = "emlViewer";
    public $gridAllegati = "emlViewer_gridAllegati";
    public $gridAllegatiOrig = "emlViewer_gridAllegatiOrig";
    public $emlLib;
    public $certificato;
    public $elencoAllegati;
    public $elencoAllegatiOrig;
    public $currMailBox;
    public $currMessage;
    public $currAllegato = array();

    function __construct() {
        parent::__construct();
        try {
            $this->emlLib = new emlLib();
            $this->certificato = App::$utente->getKey($this->nameForm . "_certificato");
            $this->elencoAllegati = App::$utente->getKey($this->nameForm . "_elencoAllegati");
            $this->elencoAllegatiOrig = App::$utente->getKey($this->nameForm . "_elencoAllegatiOrig");
            $this->currAllegato = App::$utente->getKey($this->nameForm . "_currAllegato");
            $this->currMessage = unserialize(App::$utente->getKey($this->nameForm . "_currMessage"));
            $this->currMailBox = unserialize(App::$utente->getKey($this->nameForm . "_currMailBox"));
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
            App::$utente->setKey($this->nameForm . "_currAllegato", $this->currAllegato);
            App::$utente->setKey($this->nameForm . "_currMessage", serialize($this->currMessage));
            App::$utente->setKey($this->nameForm . "_currMailBox", serialize($this->currMailBox));
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $risultato = false;
                $codiceMail = '';
                if (is_array($_POST['codiceMail']) && count($_POST['codiceMail']) > 1) {
                    if ($_POST['tipo'] == 'id') {
                        emlRic::emlRicToView($this->emlLib, $this->nameForm, $_POST['codiceMail'], $_POST['tipo']);
                        break;
                    } else {
                        Out::msgStop("Attenzione!", "La richiesta al visualizzatore di mail non è applicabile");
                        $this->returnToParent(true);
                        break;
                    }
                } else {
                    if (is_array($_POST['codiceMail'])) {
                        $codiceMail = $_POST['codiceMail'][0];
                    } else {
                        $codiceMail = $_POST['codiceMail'];
                    }
                }
                if (isset($codiceMail) || isset($_POST['tipo'])) {
                    $risultato = $this->Dettaglio($codiceMail, $_POST['tipo']);
                }
                if ($risultato === false) {
                    Out::msgStop("Attenzione!", "Impossibile visualizzare il dettaglio, verificare che i riferimenti alla email siano corretti.");
                    $this->returnToParent(true);
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CertificatoV':
                    case $this->nameForm . '_CertificatoNV':
                        Out::openDocument(utiDownload::getUrl($this->certificato['Signature']['FileName'], $this->certificato['Signature']['DataFile']));
                        break;
                    case $this->nameForm . '_VisualizzaEml':
                        $risultato = $this->Dettaglio($this->currAllegato['FileDati'], "file");
                        if ($risultato === false) {
                            Out::msgStop("Attenzione!", "Impossibile visualizzare il dettaglio, verificare che i riferimenti alla email siano corretti.");
                            $this->returnToParent(true);
                        }
                        break;
                    case $this->nameForm . '_ScaricaEml':
                        Out::openDocument(utiDownload::getUrl($this->currAllegato['FileAllegato'], $this->currAllegato['FileDati'], true));
                        break;
                    case $this->nameForm . '_Stampa':
                        $retDecode = $this->currMessage->getStruct();
                        if (is_array($retDecode['ita_PEC_info']['messaggio_originale'])) {
                            $arrayMsgOriginale = $this->GetDatiStampaEmailOriginale($retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile']);
                        } else {
                            $arrayMsgOriginale = $this->GetDatiStampaEmailOriginale($retDecode);
                        }
                        //include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        //$itaJR = new itaJasperReport();
                        if (file_exists($arrayMsgOriginale['Corpo'])) {
                            $corpo = file_get_contents($arrayMsgOriginale['Corpo']);
                        } else {
                            $corpo = "<h1>Corpo Mail non visualizzabile......</h1>";
                        }
                        $parameters = array(
                            "Mittente" => $arrayMsgOriginale['Mittente'],
                            "Destinatario" => $arrayMsgOriginale['Destinatario'],
                            "Oggetto" => $arrayMsgOriginale['Oggetto'],
                            "Data" => $arrayMsgOriginale['Data'],
                            "Corpo" => $corpo,
                            "Allegati" => $arrayMsgOriginale['Allegati']
                        );
                        $report = 'emlViewer';
                        $HTMLName = $this->getHtmlRicevuta($report, $parameters);
                        Out::openIFrame($report, $report . "_toPrint", utiDownload::getUrl(App::$utente->getKey('TOKEN') . "-" . $report . ".html", $HTMLName), "600px", "800px", 'desktopBody', false, true);
                        //$itaJR->runSQLReportPDF($this->emlLib->getITALWEB(), 'emlViewer', $parameters);
                        break;
                    case $this->nameForm . '_ScaricaBusta':
                        $emlFile = $this->currMessage->getEmlFile();
                        Out::openDocument(utiDownload::getUrl("posta-certificata.eml", $emlFile, true));
                        break;
                    case $this->nameForm . '_StampaBusta':
                        $retDecode = $this->currMessage->getStruct();
                        $arrayMsgOriginale = $this->GetDatiStampaEmailOriginale($retDecode);
                        //include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        //$itaJR = new itaJasperReport();
                        if (file_exists($arrayMsgOriginale['Corpo'])) {
                            $corpo = file_get_contents($arrayMsgOriginale['Corpo']);
                        } else {
                            $corpo = "<h1>Corpo Mail non visualizzabile......</h1>";
                        }
                        $parameters = array("Allegati" => $arrayMsgOriginale['Allegati'], "Destinatario" => $arrayMsgOriginale['Destinatario'], "Mittente" => $arrayMsgOriginale['Mittente'], "Oggetto" => $arrayMsgOriginale['Oggetto'], "Data" => $arrayMsgOriginale['Data'], "Corpo" => $corpo);
                        $report = 'emlViewer';
                        $HTMLName = $this->getHtmlRicevuta($report, $parameters);
                        Out::openIFrame($report, $report . "_toPrint", utiDownload::getUrl(App::$utente->getKey('TOKEN') . "-" . $report . ".html", $HTMLName), "600px", "800px", 'desktopBody', false, true);
                        // $itaJR->runSQLReportPDF($this->emlLib->getITALWEB(), 'emlViewer', $parameters);
                        break;

                    case $this->nameForm . '_Collegate':
                        $retDecode = $this->currMessage->getStruct();
                        emlRic::emlRicCollegate($this->nameForm, 'returnFromEmlRicCollegate', $this->emlLib, $retDecode['Message-Id'], 'msgid');
                        break;

                    case 'close-portlet':


                        $this->returnToParent(true);
                        break;
                }
                break;
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridAllegati :
                        $FileAllegato = $this->elencoAllegati[$_POST['rowid'] - 1]['DATAFILE'];
                        $FileDati = $this->elencoAllegati[$_POST['rowid'] - 1]['FILE'];
                        if (strtolower(pathinfo($FileAllegato, PATHINFO_EXTENSION)) == "eml") {
                            Out::msgQuestion("Upload.", "Cosa vuoi fare con il file eml selezionato?", array(
                                'F2-Scarica' => array('id' => $this->nameForm . '_ScaricaEml', 'model' => $this->nameForm, 'shortCut' => "f2"),
                                'F8-Visualizza' => array('id' => $this->nameForm . '_VisualizzaEml', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                    )
                            );
                            $this->currAllegato = array("FileAllegato" => $FileAllegato, "FileDati" => $FileDati);
                            break;
                        }
                        Out::openDocument(utiDownload::getUrl($FileAllegato, $FileDati));
                        break;
                    case $this->gridAllegatiOrig :
                        $FileAllegato = $this->elencoAllegatiOrig[$_POST['rowid'] - 1]['DATAFILE'];
                        $FileDati = $this->elencoAllegatiOrig[$_POST['rowid'] - 1]['FILE'];
                        if (strtolower(pathinfo($FileAllegato, PATHINFO_EXTENSION)) == "eml") {
                            Out::msgQuestion("Upload.", "Cosa vuoi fare con il file eml selezionato?", array(
                                'F2-Scarica' => array('id' => $this->nameForm . '_ScaricaEml', 'model' => $this->nameForm, 'shortCut' => "f2"),
                                'F8-Visualizza' => array('id' => $this->nameForm . '_VisualizzaEml', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                    )
                            );
                            $this->currAllegato = array("FileAllegato" => $FileAllegato, "FileDati" => $FileDati);
                            break;
                        }
                        Out::openDocument(utiDownload::getUrl($FileAllegato, $FileDati));
                        break;
                }
                break;
            case "cellSelect":
                $this->currAllegato = array();
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
                                $this->emlLib->VisualizzaFirme($filepath . "/" . $P7Mfile, $allegatoFirmato['DATAFILE']);
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
                                $this->emlLib->VisualizzaFirme($filepath . "/" . $P7Mfile, $allegatoFirmato['DATAFILE']);
                                break;
                        }
                        break;
                }
                break;
            case 'returnFromEmlRicToView':
                $codiceMail = $_POST['rowData']['ID_MAIL'];
                if ($codiceMail) {
                    $risultato = $this->Dettaglio($codiceMail, 'id');
                }
                if ($risultato === false) {
                    Out::msgStop("Attenzione!", "Impossibile visualizzare il dettaglio, verificare che i riferimenti alla email siano corretti.");
                    $this->returnToParent();
                } else if ($risultato == '') {
                    $this->returnToParent();
                }
                break;

            case 'returnFromEmlRicCollegate':
                $codiceMail = $_POST['retKey'];
                if ($codiceMail) {
                    $risultato = $this->Dettaglio($codiceMail, 'rowid');
                }
                if ($risultato === false) {
                    Out::msgStop("Attenzione!", "Impossibile visualizzare il dettaglio, verificare che i riferimenti alla email siano corretti.");
                    $this->returnToParent();
                } else if ($risultato == '') {
                    $this->returnToParent();
                }
                break;
        }
    }

    public function close() {
        $this->azzera();
        App::$utente->removeKey($this->nameForm . '_certificato');
        App::$utente->removeKey($this->nameForm . '_elencoAllegati');
        App::$utente->removeKey($this->nameForm . '_elencoAllegatiOrig');
        App::$utente->removeKey($this->nameForm . '_currMessage');
        App::$utente->removeKey($this->nameForm . '_currMailBox');
        App::$utente->removeKey($this->nameForm . '_currAllegato');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    function Nascondi() {
        Out::hide($this->nameForm . '_CertificatoV');
        Out::hide($this->nameForm . '_CertificatoNV');
        Out::hide($this->nameForm . '_divButCert');
        Out::hide($this->nameForm . '_Stampa');
        Out::hide($this->nameForm . '_Collegate');
    }

    function Azzera() {
        if ($this->currMessage != null) {
            $this->currMessage->cleanData();
            $this->currMessage = null;
        }
        if ($this->currMailBox != null) {
            $this->currMailBox->close();
            $this->currMailBox = null;
        }
        $this->elencoAllegati = array();
        $this->elencoAllegatiOrig = array();
        $this->certificato = array();
        $this->currAllegato = array();
    }

    function Dettaglio($codice, $tipo = 'rowid') {
        switch ($tipo) {
            case "file":
                $this->currMessage = new emlMessage();
                $this->currMessage->setEmlFile($codice);
                break;
            default:
                $this->currMailBox = new emlMailBox();
                $mailArchivio = $this->emlLib->getMailArchivio($codice, $tipo);
                if (!$mailArchivio) {
                    return false;
                }
                $this->currMessage = $this->currMailBox->getMessageFromDb($mailArchivio['ROWID']);
                break;
        }

        Out::tabSelect($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneMail");
        $this->currMessage->parseEmlFileDeep();
        $retDecode = $this->currMessage->getStruct();

        if ($mailArchivio) {
            Out::valore($this->nameForm . '_DataSendRic', $mailArchivio['SENDRECDATE']);
            Out::valore($this->nameForm . '_OraSendRic', $mailArchivio['SENDRECTIME']);
            if ($mailArchivio['SENDREC'] == 'R') {
                Out::html($this->nameForm . '_DataSendRic_lbl', 'Data Ricezione');
                Out::html($this->nameForm . '_OraSendRic_lbl', 'Ora Ricezione');
            } else {
                Out::html($this->nameForm . '_DataSendRic_lbl', 'Data Invio');
                Out::html($this->nameForm . '_OraSendRic_lbl', 'Ora Invio');
            }
        }

        return $this->analizzaMail($retDecode);
        //return $this->analizzaMail($mailArchivio);
    }

    function analizzaMail($retDecode) {
        //$retDecode = $this->currMessage->getStruct();
        $this->Nascondi();
        Out::setAppTitle($this->nameForm, "Visualizzatore Email: " . pathinfo($this->currMessage->getEmlFile(), PATHINFO_BASENAME));
        Out::show($this->nameForm . '_Stampa');
        $this->elencoAllegati = array();
        $elementi = $retDecode['Attachments'];
        $AnomalieAllegati = false;
        if ($elementi) {
            $incr = 1;
            foreach ($elementi as $elemento) {
                if ($elemento['FileName'] === '') {
                    $AnomalieAllegati = true;
                    $elemento['FileName'] = md5(microtime());
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

                    $this->elencoAllegati[] = array(
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
        $ita_grid01 = new TableView($this->gridAllegati, array('arrayTable' => $this->elencoAllegati, 'rowIndex' => 'idx'));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(100);
        TableView::enableEvents($this->gridAllegati);
        TableView::clearGrid($this->gridAllegati);
        $ita_grid01->getDataPage('json');

        Out::valore($this->nameForm . '_Mittente', $retDecode['FromAddress']);
        Out::valore($this->nameForm . '_Destinatario', $retDecode['To'][0]['address']);
        Out::valore($this->nameForm . '_Oggetto', $retDecode['Subject']);
        Out::valore($this->nameForm . '_Data', date("Ymd", strtotime($retDecode['Date'])));
        Out::valore($this->nameForm . '_Ora', date("H:i:s", strtotime($retDecode['Date'])));


        $url = utiDownload::getUrl("emlbody.html", $retDecode['DataFile'], false, true);
        $iframe = '<iframe src="' . $url . '" frameborder="0" width="99%" height="95%" id="' . $this->nameForm . '_emlBMFrame">
            <p>Contenuto non visualizzabile.....</p>
            </iframe>';
        Out::html($this->nameForm . '_divSoggetto', $iframe);
        if ($AnomalieAllegati) {
            Out::msgInfo("Attenzione", "Uno o più file allegati non risultano leggibili.");
        }
        $this->decodSegnaturaCert($retDecode);

        $mail_rec = $this->emlLib->getMailArchivio($retDecode['Message-Id'], 'msgid');
        $sql = sprintf('SELECT IDMAIL FROM MAIL_ARCHIVIO WHERE IDMAILPADRE = "%s" OR IDMAIL = "%s"', $mail_rec['IDMAIL'], $mail_rec['IDMAILPADRE']);
        $mailarchivio_tab = itaDB::DBSQLSelect(ItaDB::DBOpen('ITALWEB'), $sql);

        if (count($mailarchivio_tab)) {
            Out::show($this->nameForm . '_Collegate');
        }
        // Dati Mail:
        if ($mail_rec) {
            $mailTipo = $mail_rec['PECTIPO'];
            if (!$mail_rec['PECTIPO']) {
                $mailTipo = 'Posta normale';
            }
            Out::valore($this->nameForm . '_PecTipo', $mailTipo);
            Out::valore($this->nameForm . '_Interoperabile', $mail_rec['TIPOINTEROPERABILE']);
            Out::valore($this->nameForm . '_Classe', $mail_rec['CLASS']);
        }

        return true;
    }

//    function analizzaMail($mailArchivio) {
//        $this->Nascondi();
//        $this->currMailBox = new emlMailBox();
//        $this->currMessage = $this->currMailBox->getMessageFromDb($mailArchivio['ROWID']);
//        $this->currMessage->parseEmlFileDeep();
//        $retDecode = $this->currMessage->getStruct();
//        $this->elencoAllegati = array();
//        $elementi = $retDecode['Attachments'];
//        if ($elementi) {
//            $incr = 1;
//            foreach ($elementi as $elemento) {
//                if ($elemento['FileName']) {
//                    $vsign = "";
//                    $icon = utiIcons::getExtensionIconClass($elemento['FileName'], 32);
//                    $sizefile = $this->emlLib->formatFileSize(filesize($elemento['DataFile']));
//                    $ext = pathinfo($elemento['FileName'], PATHINFO_EXTENSION);
//                    if (strtolower($ext) == "p7m") {
//                        $vsign = "<span class=\"ita-icon ita-icon-shield-blue-24x24\">Verifica Firma</span>";
//                    }
//
//                    $this->elencoAllegati[] = array(
//                        'ROWID' => $incr,
//                        'FileIcon' => "<span style = \"margin:2px;\" class=\"$icon\"></span>",
//                        'DATAFILE' => $elemento['FileName'],
//                        'FILE' => $elemento['DataFile'],
//                        'FileSize' => $sizefile,
//                        'VSIGN' => $vsign
//                    );
//                    $incr++;
//                }
//            }
//        }
//        $ita_grid01 = new TableView($this->gridAllegati, array('arrayTable' => $this->elencoAllegati, 'rowIndex' => 'idx'));
//        $ita_grid01->setPageNum(1);
//        $ita_grid01->setPageRows(15);
//        TableView::enableEvents($this->gridAllegati);
//        TableView::clearGrid($this->gridAllegati);
//        $ita_grid01->getDataPage('json');
//
//        Out::valore($this->nameForm . '_Mittente', $mailArchivio['FROMADDR']);
//        Out::valore($this->nameForm . '_Destinatario', $mailArchivio['TOADDR']);
//        Out::valore($this->nameForm . '_Oggetto', $mailArchivio['SUBJECT']);
//        Out::valore($this->nameForm . '_Data', substr($mailArchivio['MSGDATE'], 0, 8));
//        Out::valore($this->nameForm . '_Ora', trim(substr($mailArchivio['MSGDATE'], 8)));
//        $url = utiDownload::getUrl("emlbody.html", $retDecode['DataFile'], false, true);
//        $iframe = '<iframe src="' . $url . '" frameborder="0" width="99%" height="95%" id="' . $this->nameForm . '_emlBMFrame">
//            <p>Contenuto non visualizzabile.....</p>
//            </iframe>';
//        Out::html($this->nameForm . '_divSoggetto', $iframe);
////Out::show($this->nameForm . '_Stampa');
//        $this->decodSegnaturaCert($retDecode);
//        return true;
//    }

    function decodSegnaturaCert($retDecode) {
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
//            Out::show($this->nameForm . '_divButPec');
            if ($retDecode['ita_PEC_info']['dati_certificazione']['tipo'] == 'accettazione' ||
                    $retDecode['ita_PEC_info']['dati_certificazione']['tipo'] == 'avvenuta-consegna') {
                Out::show($this->nameForm . '_AssegnaProt');
            }
            if (is_array($retDecode['ita_PEC_info']['messaggio_originale'])) {
                $this->caricaDatiEmailOriginale($retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile']);
                Out::tabEnable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneOriginale");
            } else {
                Out::tabDisable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneOriginale");
                Out::hide($this->nameForm . '_StampaBusta');
            }
        } else {
//            Out::hide($this->nameForm . '_divButPec');
            Out::tabDisable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneOriginale");
            Out::tabDisable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneCertificazione");
            Out::show($this->nameForm . '_AssegnaProt');
            Out::hide($this->nameForm . '_StampaBusta');
        }
        $this->setCertificazione();
    }

    function GetDatiStampaEmailOriginale($messaggioOriginale) {
        $arrayMsgOriginale = array();
        foreach ($messaggioOriginale['From'] as $address) {
            $arrayMsgOriginale['Mittente'] = $address['address'];
        }
        foreach ($messaggioOriginale['To'] as $address) {
            $arrayMsgOriginale['Destinatario'] = $address['address'];
        }
        foreach ($messaggioOriginale['Attachments'] as $allegato) {
            $arrayMsgOriginale['Allegati'] .= "- " . $allegato['FileName'] . "<br>";
        }
        $arrayMsgOriginale['Oggetto'] = $messaggioOriginale['Subject'];
        $arrayMsgOriginale['Data'] = date('Ymd', strtotime($messaggioOriginale['Date']));

        $datafile = '';
        if (isset($messaggioOriginale['DataFile'])) {
            $datafile = $messaggioOriginale['DataFile'];
        } else {
            foreach ($messaggioOriginale['Alternative'] as $value) {
                $datafile = $value['DataFile'];
            }
        }
        $arrayMsgOriginale['Corpo'] = $datafile;
        return $arrayMsgOriginale;
    }

    function caricaDatiEmailOriginale($messaggioOriginale) {
        $this->elencoAllegatiOrig = array();
        $elementi = $messaggioOriginale['Attachments'];
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

                    $this->elencoAllegatiOrig[] = array(
                        'ROWID' => $incr,
                        'FileIcon' => "<span style = \"margin:2px;\" class=\"$icon\"></span>",
                        'DATAFILE' => $elemento['FileName'],
                        'FILE' => $elemento['DataFile'],
                        'FileSize' => $sizefile,
                        'VSIGN' => $vsign,
                    );
                    $incr++;
                }
            }
        }
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
            $retDecode = $this->currMessage->getStruct();
            Out::valore($this->nameForm . '_DataOrig', date("Ymd", strtotime($retDecode['Date'])));
            Out::valore($this->nameForm . '_OraOrig', date("H:i:s", strtotime($retDecode['Date'])));
        } else {
            Out::valore($this->nameForm . '_DataOrig', date('Ymd', strtotime($dataOrig)));
            Out::valore($this->nameForm . '_OraOrig', trim(date('H:i:s', strtotime($dataOrig))));
        }

        $pre_a = "";
        $pre_c = "";
        if ($messaggioOriginale['Type'] == 'text') {
            $pre_a = '<pre style="font-size:1.4em;">';
            $pre_c = '</pre>';
        }
        $datafile = '';
        if (isset($messaggioOriginale['DataFile'])) {
            $datafile = $messaggioOriginale['DataFile'];
        } else {
            foreach ($messaggioOriginale['Alternative'] as $value) {
                $datafile = $value['DataFile'];
            }
        }
//        $Soggetto = file_get_contents($datafile);
//        $Soggetto = utf8_decode($Soggetto);
//        Out::html($this->nameForm . '_divSoggettoOrig', $pre_a . $Soggetto . $pre_c);
        $url = utiDownload::getUrl("emlbody.html", $datafile, false, true);
        $iframe = '<iframe src="' . $url . '" frameborder="0" width="99%" height="95%" id="' . $this->nameForm . '_emlOrigFrame">
            <p>Contenuto non visualizzabile.....</p>
            </iframe>';
        Out::html($this->nameForm . '_divSoggettoOrig', $iframe);
    }

    function setCertificazione() {
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
            Out::hide($this->nameForm . '_StampaBusta');
            Out::tabDisable($this->nameForm . "_tabDettaglio", $this->nameForm . "_paneCertificazione");
        }
    }

    function getHtmlRicevuta($report, $record = array()) {
        $html = '
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
                <meta http-equiv="Pragma" content="no-cache" />
                <title>Italsoft</title>
        <style>
            div.header {
                display: block;
                position: running(header);
                height:5mm;
            }
            div.footer {
                display: block;
                position: running(footer);
                height:5mm;
            }

            @page {
                @top-center {
                    content: element(header);
                    vertical-align:bottom;
                    overflow:hidden;
                    display:none;
                }
            }
            @page {
                @bottom-center {
                    content: element(footer);
                    vertical-align:top;
                    overflow:hidden;
                    display:none;
                }
            </style>
            </head>
            <body>
              <div style="width:210mm;">
              <div style="display:inline-block; width: 80%; margin: auto;"><h2 style="text-align:center;">RIEPILOGO MAIL</h2></div>
              <div style="display:inline-block; width: 15%;">' . date('d/m/Y') . '</div>
              <p><b>Mittente:</b> ' . $record['Mittente'] . '</p>
              <p><b>Destinatario:</b> ' . $record['Destinatario'] . '</p>
              <p><b>Oggetto:</b> ' . $record['Oggetto'] . '</p>
              <p><b>Data:</b> ' . date('d/m/Y', strtotime($record['Data'])) . '</p>
              <br><br>
              <div>' . $record['Corpo'] . '</div>
              <br><br>
              <p><b>Allegati:</b></p>
              <pre>' . print_r($record['Allegati'], true) . '</pre>
              </div>
                <div class="header">
                </div>
                <div class="footer">
                </div>
            </body>
        </html>';

        //$HTMLName = './' . App::$utente->getkey('privPath') . '/' . App::$utente->getKey('TOKEN') . "-" . $report . ".html";
        $HTMLName = itaLib::createAppsTempPath('eml-Mail-stmp') . '/' . App::$utente->getKey('TOKEN') . "-" . $report . ".html";
        $ptr = fopen($HTMLName, 'wb');
        fwrite($ptr, $html);
        fclose($ptr);
        return $HTMLName;
    }

}

?>
