<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once(ITA_LIB_PATH . '/itaPHPVersign/itaP7m.class.php');
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaPDFUtils.class.php';
include_once ITA_LIB_PATH . '/itaPHPDocViewer/itaDocViewerBootstrap.class.php';

function utiP7m() {
    $utiP7m = new utiP7m();
    $utiP7m->parseEvent();
    return;
}

class utiP7m extends itaModel {

    public $nameForm = "utiP7m";
    public $gridFirme = "utiP7m_gridFirme";
    public $closeOnReturn;
    public $p7m;
    public $fileContenuto;
    public $returnModel;
    public $returnMethod;
    public $returnField;
    public $elencoFirme;
    public $rowidPasdoc;
    private $errCode;
    private $errMessage;
    public $customButt = array();
    public $titoloForm;
    public $paramCopiaAnalogica = array();
    public $utiP7mTempPath;

    /* REFACTORING */
    private $file;
    private $segnatura;
    private $fileOriginale;
    private $showPreview;
    private $showConfermaMarcatura;
    private static $previewExts = array(
        'pdf', 'xml', 'html', 'htm', 'jpg', 'jpeg', 'png', 'bmp', 'gif', 'svg', 'odt', 'ods', 'odp', 'json'
    );

    function __construct() {
        parent::__construct();
        $this->p7m = unserialize(App::$utente->getKey($this->nameForm . '_p7m'));
        $this->fileOriginale = App::$utente->getKey($this->nameForm . '_fileOriginale');
        $this->fileContenuto = App::$utente->getKey($this->nameForm . '_fileContenuto');
        $this->closeOnReturn = App::$utente->getKey($this->nameForm . '_closeOnReturn');
        $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
        $this->returnMethod = App::$utente->getKey($this->nameForm . '_returnMethod');
        $this->returnField = App::$utente->getKey($this->nameForm . '_returnField');
        $this->elencoFirme = App::$utente->getKey($this->nameForm . '_elencoFirme');
        $this->segnatura = App::$utente->getKey($this->nameForm . '_segnatura');
        $this->rowidPasdoc = App::$utente->getKey($this->nameForm . '_rowidPasdoc');
        $this->customButt = App::$utente->getKey($this->nameForm . '_customButt');
        $this->titoloForm = App::$utente->getKey($this->nameForm . '_titoloForm');
        $this->paramCopiaAnalogica = App::$utente->getKey($this->nameForm . '_paramCopiaAnalogica');
        $this->utiP7mTempPath = App::$utente->getKey($this->nameForm . '_utiP7mTempPath');
        $this->showConfermaMarcatura = App::$utente->getKey($this->nameForm . '_showConfermaMarcatura');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_p7m', serialize($this->p7m));
            App::$utente->setKey($this->nameForm . '_fileOriginale', $this->fileOriginale);
            App::$utente->setKey($this->nameForm . '_fileContenuto', $this->fileContenuto);
            App::$utente->setKey($this->nameForm . '_closeOnReturn', $this->closeOnReturn);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnMethod', $this->returnMethod);
            App::$utente->setKey($this->nameForm . '_returnField', $this->returnField);
            App::$utente->setKey($this->nameForm . '_elencoFirme', $this->elencoFirme);
            App::$utente->setKey($this->nameForm . '_segnatura', $this->segnatura);
            App::$utente->setKey($this->nameForm . '_rowidPasdoc', $this->rowidPasdoc);
            App::$utente->setKey($this->nameForm . '_customButt', $this->customButt);
            App::$utente->setKey($this->nameForm . '_titoloForm', $this->titoloForm);
            App::$utente->setKey($this->nameForm . '_paramCopiaAnalogica', $this->paramCopiaAnalogica);
            App::$utente->setKey($this->nameForm . '_utiP7mTempPath', $this->utiP7mTempPath);
            App::$utente->setKey($this->nameForm . '_showConfermaMarcatura', $this->showConfermaMarcatura);
        }
    }

    public function getFile() {
        return $this->file;
    }

    public function setFile($file) {
        $this->file = $file;
    }

    public function getSegnatura() {
        return $this->segnatura;
    }

    /**
     * Definisce il tipo di segnatura da applicare all'eventuale pdf estratto dal p7m 
     * 
     * 
     * @param array $segnarura array di parametri per il controllo della segnatura
     *  $segnatura = [
     *      'task' =>            (string) tipo di marcatura da applicare (sintassi utiPDFUtils) watermark|text
     *      'taskData' =>        (array)  parametri di controllo, seguire la documentazione di itaPDFUtils   
     * ] 
     * ATTENZIONE: il parametro $this->task['taskData']['input'] è auto gestito da utiP7m.
     * 
     * @return boolean
     */
    public function setSegnatura($segnatura) {
        $this->segnatura = $segnatura;
    }

    public function getFileOriginale() {
        return $this->fileOriginale;
    }

    public function setFileOriginale($fileOriginale) {
        $this->fileOriginale = $fileOriginale;
    }

    public function getShowPreview() {
        return $this->showPreview;
    }

    public function setShowPreview($showPreview) {
        $this->showPreview = $showPreview;
    }

    public function getShowConfermaMarcatura() {
        return $this->showConfermaMarcatura;
    }

    public function setShowConfermaMarcatura($showConfermaMarcatura) {
        $this->showConfermaMarcatura = $showConfermaMarcatura;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getCustomButt() {
        return $this->customButt;
    }

    public function setCustomButt($customButt) {
        $this->customButt = $customButt;
    }

    public function getTitoloForm() {
        return $this->titoloForm;
    }

    public function setTitoloForm($titoloForm) {
        $this->titoloForm = $titoloForm;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $praLib = new praLib();
                Out::show($this->nameForm);
                // Bottone di test per verifica tramite ARSS
                Out::removeElement($this->nameForm . '_VerificaRemota');

                if ($this->file) {
                    $_POST['file'] = $this->file;
                }
                if ($this->fileOriginale) {
                    $_POST['fileOriginale'] = $this->fileOriginale;
                }

                if ($_POST['file']) {
                    $tempPath = $this->createTempPath();
                    $nomeFileCaricato = $tempPath . "/" . $_POST['fileOriginale'];
                    copy($_POST['file'], $nomeFileCaricato);

                    $this->p7m = itaP7m::getP7mInstance($nomeFileCaricato);
                    if (!$this->p7m) {
                        Out::msgStop("Inserimento File Firmato", "Verifica Fallita");
                        break;
                    }
                    if ($_POST['segnatura']) {
                        $this->segnatura = $_POST['segnatura'];
                    }
                    if ($_POST['rowidPasdoc'] != 0) {
                        $this->rowidPasdoc = $_POST['rowidPasdoc'];
                    }
                    if ($_POST['paramCopiaAnalogica']) {
                        $this->paramCopiaAnalogica = $_POST['paramCopiaAnalogica'];
                    }
                    $this->fileOriginale = pathinfo($_POST['fileOriginale'], PATHINFO_BASENAME);
                    if ($this->p7m->isFileVerifyPassed()) {
                        Out::delClass($this->nameForm . "_divHeader", "ui-state-error");
                        Out::addClass($this->nameForm . "_divHeader", "ui-state-highlight");
                        $header = '<span style="padding:2px;color:darkgreen;"> Il file è firmato correttamente.</span>';
                        //Out::hide($this->nameForm . "_divMessaggio");
                    } else {
                        Out::delClass($this->nameForm . "_divHeader", "ui-state-highlight");
                        Out::addClass($this->nameForm . "_divHeader", "ui-state-error");
                        Out::delClass($this->nameForm . "_divIcon", "ita-icon-shield-green-64x164");
                        Out::addClass($this->nameForm . "_divIcon", "ita-icon-shield-red-64x64");
                        $messErr = $this->p7m->getMessageErrorFileAsString();
                        if (trim($messErr) == '') {
                            $messErr = "Verifica con errori.";
                        }
                        $header = '<span style="padding:2px;">' . $messErr . '</span>';
                        //Out::html($this->nameForm . "_divMessaggio", "<span style=\"padding:2px;font-size:1.5em;\">Usa il doppio click sulla riga del firmatario per vedere i dettagli dell'errore.</span>");
                        //Out::show($this->nameForm . "_divMessaggio");
                    }
                    if (file_exists($this->p7m->getContentFileName())) {
                        //$this->fileContenuto = itaLib::pathinfoFilename($_POST['fileOriginale'], PATHINFO_FILENAME);
                        $this->fileContenuto = pathinfo($this->p7m->getContentFileName(), PATHINFO_BASENAME);
                        Out::valore($this->nameForm . "_fileContenuto", $this->fileContenuto);
                        Out::show($this->nameForm . "_fileContenuto_field");
                    } else {
                        $this->fileContenuto = '';
                        Out::valore($this->nameForm . "_fileContenuto", '');
                        Out::hide($this->nameForm . "_fileContenuto_field");
                    }

                    $ext = pathinfo($this->fileContenuto, PATHINFO_EXTENSION);
                    $Filent_rec = $praLib->GetFilent(16);
                    Out::hide($this->nameForm . "_Misuratore");
                    if ($Filent_rec['FILVAL'] == 1 && strtolower($ext) == "pdf") {
                        Out::show($this->nameForm . "_Misuratore");
                    }
                    Out::hide($this->nameForm . "_Marca");
                    if (count($this->segnatura) && strtolower($ext) == "pdf") {
                        Out::show($this->nameForm . "_Marca");
                    }
                    // Copia Analogica
                    Out::hide($this->nameForm . '_CopiaAnalogica');
                    if ($this->paramCopiaAnalogica) {
                        Out::show($this->nameForm . '_CopiaAnalogica');
                    }
                    Out::html($this->nameForm . "_divHeader", $header);
                    Out::valore($this->nameForm . "_fileOriginale", pathinfo($_POST['fileOriginale'], PATHINFO_BASENAME));
                    $this->visualizzaFirme($this->p7m->getInfoSummary(), $header);

                    if (!empty($this->segnatura) && !isset($this->segnatura['task'])) {
                        foreach ($this->segnatura as $key => $segnatura) {
                            $this->segnatura[$key]['STRING'] = str_replace('@{$PRAALLEGATI.FIRMATARIO}@', $this->elencoFirme[0]['signer'], $segnatura['STRING']);
                        }
                    }

                    if ($this->titoloForm) {
                        Out::setAppTitle($this->nameForm, $this->titoloForm);
                    }
                    Out::hide($this->nameForm . '_CustomButt');
                    if ($this->customButt) {
                        Out::show($this->nameForm . '_CustomButt');
                        Out::html($this->nameForm . '_CustomButt_lbl', $this->customButt['CustomButton']);
                    }

                    Out::hide($this->nameForm . '_divAnteprimaPDF');
                    if ($this->showPreview === true && !empty($this->fileContenuto) && in_array(strtolower($ext), self::$previewExts)) {
                        Out::show($this->nameForm . '_divAnteprimaPDF');
                        $docViewer = new itaDocViewerBootstrap();
                        $docViewer->addFile($this->p7m->getContentFileName());
                        $docViewer->openViewer(itaDocViewerBootstrap::DOCVIEWER_INNER, false, false, $this->nameForm, 'divAnteprimaPDFContent');
                    }
                }
                $this->setToCenter($this->nameForm . '_wrapper', 'desktop');
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case '':
                        break;
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                Out::msgInfo("Dettaglio Errori", $this->elencoFirme[$_POST['rowid']]['messageErrorSigner']);
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        if ($this->p7m) {
                            $this->p7m->cleanData();
                        }
                        $this->clearTempPath();
                        $this->returnToParent(true);
                        break;
                    case $this->nameForm . '_fileOriginale_butt':
                        Out::openDocument(utiDownload::getUrl($this->fileOriginale, $this->p7m->getFileName()));
                        break;
                    case $this->nameForm . '_fileContenuto_butt':
                        if ($this->fileContenuto) {
                            Out::openDocument(utiDownload::getUrl($this->fileContenuto, $this->p7m->getContentFileName()));
                        }
                        break;
                    case $this->nameForm . '_Misuratore':
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
                        $fileTIF = $this->sincPlanimetria($this->p7m->getContentFileName());
                        if (!$fileTIF) {
                            break;
                        }
                        $Formato = $_POST[$this->nameForm . "_Formato"];
                        $Orientamento = $_POST[$this->nameForm . "_Orientamento"];
                        $Scala = $_POST[$this->nameForm . "_Scala"];
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
                    case $this->nameForm . '_Marca':
                        if ($this->showConfermaMarcatura !== false) {
                            Out::msgQuestion("ATTENZIONE!", "Marcare l'allegato <b>" . $this->fileContenuto . "</b> con la seguente segnatura?<br>{$this->segnatura[0]['STRING']}", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaMarcatura', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaMarcatura', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                            break;
                        }
                    case $this->nameForm . '_ConfermaMarcatura':
                        if ($this->p7m->setContentFileMarcato() === false) {
                            Out::msgStop("Marcatura Allegato", "Errore nell'inizializzare il file marcato " . $this->p7m->getContentFileMarcato());
                            break;
                        }
                        $out = $this->ComponiPDFconSegnatura($this->segnatura, $this->p7m->getContentFileMarcato());
                        //$out = $this->ComponiPDFconSegnatura($this->segnatura, $this->p7m->getContentFileName());
                        if ($out === false) {
                            Out::msgStop("Marcatura Allegato", $this->getErrMessage());
                            break;
                        }
                        Out::openDocument(utiDownload::getOTR(basename($this->p7m->getContentFileMarcato()), $out, true, true));
                        //Out::openDocument(utiDownload::getOTR($this->p7m->getContentFileName(), $out, false, true));
                        break;

                    case $this->nameForm . '_CustomButt':
                        if ($this->customButt) {
                            $RetdatiUtiP7m = array();
                            $RetdatiUtiP7m['p7m'] = serialize($this->p7m);
                            $RetdatiUtiP7m['NomeFileOriginale'] = $this->fileOriginale;
                            $RetdatiUtiP7m['NomeFileContenuto'] = $this->fileContenuto;
                            $model = $this->customButt['CustomReturnModel'];
                            $modelAtto = itaModel::getInstance($model);
                            $modelAtto->setEvent($this->customButt['CustomReturnEvent']);
                            $modelAtto->setDatiUtiP7m($RetdatiUtiP7m);
                            $modelAtto->parseEvent();
                            $this->close();
                        }
                        break;

                    case $this->nameForm . '_CopiaAnalogica':
                        if ($this->paramCopiaAnalogica) {
                            $RetdatiUtiP7m = array();
                            $RetdatiUtiP7m['p7m'] = serialize($this->p7m);
                            $RetdatiUtiP7m['NomeFileOriginale'] = $this->fileOriginale;
                            $RetdatiUtiP7m['NomeFileContenuto'] = $this->fileContenuto;
                            $model = $this->paramCopiaAnalogica['CustomReturnModel'];
                            $modelAtto = itaModel::getInstance($model);
                            itaLib::openDialog($model);
                            $modelAtto->setEvent('openform');
                            $modelAtto->setDatiUtiP7m($RetdatiUtiP7m);
                            $modelAtto->setDatiDocumento($this->paramCopiaAnalogica);
                            $modelAtto->parseEvent();
//                            $this->close();
                        }
                        break;
                }
            case 'onBlur':
                switch ($_POST['id']) {
                    case '':
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_p7m');
        App::$utente->removeKey($this->nameForm . '_closeOnReturn');
        App::$utente->removeKey($this->nameForm . '_fileOriginale');
        App::$utente->removeKey($this->nameForm . '_fileContenuto');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnMethod');
        App::$utente->removeKey($this->nameForm . '_returnField');
        App::$utente->removeKey($this->nameForm . '_elencoFirme');
        App::$utente->removeKey($this->nameForm . '_segnatura');
        App::$utente->removeKey($this->nameForm . '_rowidPasdoc');
        App::$utente->removeKey($this->nameForm . '_customButt');
        App::$utente->removeKey($this->nameForm . '_titoloForm');
        App::$utente->removeKey($this->nameForm . '_paramCopiaAnalogica');
        App::$utente->removeKey($this->nameForm . '_utiP7mTempPath');
        App::$utente->removeKey($this->nameForm . '_showConfermaMarcatura');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    private function visualizzaFirme($elencoFirme, $header = '') {
        $this->elencoFirme = $elencoFirme;

        foreach ($elencoFirme as $key => $firma) {
            if ($firma['NotAfter']) {
                $elencoFirme[$key]['signer'] = $elencoFirme[$key]['signer'] . "<br>valido fino al:" . date('d/m/Y H:i:s', strtotime($firma['NotAfter']));
            }
        }

        TableView::enableEvents($this->gridFirme);
        TableView::clearGrid($this->gridFirme);
        $ita_grid01 = new TableView(
                $this->gridFirme, array('arrayTable' => $elencoFirme,
            'rowIndex' => 'idx')
        );
        $ita_grid01->getDataPage('json');
    }

    public function sincPlanimetria($file_src) {
        $sourceExtension = strtolower(pathinfo($file_src, PATHINFO_EXTENSION));
        switch ($sourceExtension) {
            case 'pdf':
                //$fileTif = pathinfo($file_src, PATHINFO_DIRNAME) . "/" . pathinfo($file_src, PATHINFO_FILENAME) . ".tif";
                $fileTif = pathinfo($file_src, PATHINFO_DIRNAME) . "/" . itaLib::pathinfoFilename($file_src, PATHINFO_FILENAME) . ".tif";
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
        $praLib = new praLib();
        $Filent_rec_8_Path = $praLib->GetFilent(8);
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

    public function ComponiPDFconSegnatura($segnaturaArr, $input) {

        if (isset($segnaturaArr['task'])) {
            return $this->ComponiPDFconSegnatura_v2($segnaturaArr, $input);
        }

        foreach ($segnaturaArr as $key => $segnatura) {
            $ret = '';
            $xmlPATH = itaLib::createAppsTempPath('praPDFComposer');
            $xmlFile = $xmlPATH . "/" . md5(rand() * time()) . ".xml";
            $xmlRes = fopen($xmlFile, "w");
            if (!file_exists($xmlFile)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in composizione PDF Marcato");
                return false;
            } else {
                $output = $xmlPATH . "/" . md5(rand() * time()) . "." . pathinfo($input, PATHINFO_EXTENSION);
                //$output = $input;
                $xml = $this->CreaXmlPdf($segnatura, $input, $output);
                fwrite($xmlRes, $xml);
                fclose($xmlRes);
                $command = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJPDF/itaJPDF.jar ' . $xmlFile;
                exec($command, $ret);
                //
                //Out::msgInfo("", $xmlFile);
                //Out::msgInfo("", $input."<br>".$output);
                $taskXml = false;
                foreach ($ret as $value) {
                    $arrayExec = explode("|", $value);
                    if ($arrayExec[1] == 00 && $arrayExec[0] == "OK") {
                        $taskXml = true;
                        break;
                    } else {
                        $errMsg = $arrayExec[0] . " - " . $arrayExec[1] . " - " . $arrayExec[2];
                    }
                }
                if ($taskXml == false) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($errMsg);
                    return false;
                } else {
                    if (!@rename($output, $input)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore nel rinominare il PDF $output");
                        return false;
                    }
                    @unlink($xmlFile);
                }
            }
        }
        return $input;
    }

    private function CreaXmlPdf($param, $input, $output) {
        $xml .= "<root>\r\n";
        $xml .= "<task name=\"watermark\">\r\n";
        $xml .= "<debug>0</debug>\r\n";
        $xml .= "<firstpageonly>{$param['FIRSTPAGEONLY']}</firstpageonly>\r\n";
        $xml .= "<x-coord>" . (int) $param['X-COORD'] . "</x-coord>\r\n";
        $xml .= "<y-coord>" . (int) $param['Y-COORD'] . "</y-coord>\r\n";
        $xml .= "<rotation>" . (int) $param['ROTATION'] . "</rotation>\r\n";
        $xml .= "<font-size>" . (int) $param['FONT-SIZE'] . "</font-size>\r\n";
        $xml .= "<string>{$param['STRING']}</string>\r\n";
        $xml .= "<input>$input</input>\r\n";
        $xml .= "<output>$output</output>\r\n";
        $xml .= "</task>\r\n";
        $xml .= "</root>\r\n";
        return $xml;
    }

    private function createTempPath() {
        $subPath = "utiP7m-work-" . md5(microtime());
        italib::clearAppsTempPathRecursive($subPath);
        $this->utiP7mTempPath = itaLib::createAppsTempPath($subPath);
        return $this->utiP7mTempPath;
    }

    private function clearTempPath() {
        return itaLib::deleteDirRecursive($this->utiP7mTempPath);
    }

    protected function parseLdapDn($dn) {
        $parsr = ldap_explode_dn($dn, 0);
        $out = array();
        foreach ($parsr as $key => $value) {
            if (strstr($value, '=') !== false) {
                list($prefix, $data) = explode("=", $value);
                //$data=preg_replace("/\\\\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\\\1')).''", $data);
                if (isset($current_prefix) && $prefix == $current_prefix) {
                    $out[$prefix][] = $data;
                } else {
                    $current_prefix = $prefix;
                    $out[$prefix] = array();
                    $out[$prefix][] = $data;
                }
            }
        }
        return $out;
    }

    private function ComponiPDFconSegnatura_v2($segnaturaArr, $input) {
        /* ridefinisco input */
        $segnaturaArr['taskData']['input'] = $input;
        /* ridefinisco output */
        $xmlPATH = itaLib::createAppsTempPath('praPDFComposer');
        $segnaturaArr['taskData']['output'] = $xmlPATH . "/" . md5(rand() * time()) . "." . pathinfo($input, PATHINFO_EXTENSION);
        /* metodo di marcatura */
        $method = '';
        switch ($segnaturaArr['task']) {
            case 'watermark':
                $method = 'executeTaskWaterMark';
                break;
            case 'text':
                $method = 'executeTaskText';
                break;
            default:
                break;
        }
        if (!$method) {
            $this->setErrCode(-1);
            $this->setErrMessage('Metodo di marcatura assente o errato.');
            return false;
        }

        $itaPDFUtils = new itaPDFUtils();
        if (!$itaPDFUtils->$method($segnaturaArr['taskData'])) {
            $this->setErrCode(-1);
            $this->setErrMessage($itaPDFUtils->getErrMessage());
            return false;
        }
//        if (!rename($segnaturaArr['taskData']['output'], $segnaturaArr['taskData']['input'])) {
//            $this->setErrCode(-1);
//            $this->setErrMessage("Errore nel rinominare il PDF {$segnaturaArr['taskData']['output']}");
//            return false;
//        }
        return $segnaturaArr['taskData']['output'];
    }

    private function setToCenter($my, $target) {
        Out::codice('$( "#' . $my . '" ).dialog("option", "position", {my: "center",at: "center",of: window});');
    }

}
