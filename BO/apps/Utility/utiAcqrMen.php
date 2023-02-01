<?php

include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function utiAcqrMen() {
    $utiAcqrMen = new utiAcqrMen();
    $utiAcqrMen->parseEvent();
    return;
}

class utiAcqrMen extends itaModel {

    const OPENMODE_FULL = 0;
    const OPENMODE_WAIT = 1;

    public $nameForm = "utiAcqrMen";
    public $fileList = array();
    public $gridAquisizioni = 'utiAcqrMen_gridAcquisizioni';
    public $divGestione = "utiAcqrMen_divGestione";
    public $uploadDir;
    public $returnModel;
    public $returnMethod;
    public $returnField;
    public $fileEsistenti = array();
    public $currAllegato;
    public $tipoNome;
    public $praLib;
    public $path;
    public $openMode = 0;
    public $conferma;

    function __construct() {
        parent::__construct();
        $this->praLib = new praLib();
        $this->flagPDFA = App::$utente->getKey($this->nameForm . '_flagPDFA');
        $this->fileList = App::$utente->getKey($this->nameForm . '_fileList');
        $this->uploadDir = App::$utente->getKey($this->nameForm . '_uploadDir');
        $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
        $this->returnMethod = App::$utente->getKey($this->nameForm . '_returnMethod');
        $this->returnField = App::$utente->getKey($this->nameForm . '_returnField');
        $this->fileEsistenti = App::$utente->getKey($this->nameForm . '_fileEsistenti');
        $this->currAllegato = App::$utente->getKey($this->nameForm . '_currAllegato');
        $this->tipoNome = App::$utente->getKey($this->nameForm . '_tipoNome');
        $this->path = App::$utente->getKey($this->nameForm . '_path');
        $this->openMode = App::$utente->getKey($this->nameForm . '_openMode');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_flagPDFA', $this->flagPDFA);
            App::$utente->setKey($this->nameForm . '_fileList', $this->fileList);
            App::$utente->setKey($this->nameForm . '_uploadDir', $this->uploadDir);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnMethod', $this->returnMethod);
            App::$utente->setKey($this->nameForm . '_returnField', $this->returnField);
            App::$utente->setKey($this->nameForm . '_fileEsistenti', $this->fileEsistenti);
            App::$utente->setKey($this->nameForm . '_currAllegato', $this->currAllegato);
            App::$utente->setKey($this->nameForm . '_tipoNome', $this->tipoNome);
            App::$utente->setKey($this->nameForm . '_path', $this->path);
            App::$utente->setKey($this->nameForm . '_openMode', $this->openMode);
        }
    }

    public function getOpenMode() {
        return $this->openMode;
    }

    function setOpenMode($openMode) {
        $this->openMode = $openMode;
    }

    /*
     *  
     * @param type $openMode     */

    public function setOpenModeWait() {
        $this->openMode = self::OPENMODE_WAIT;
    }

    /*
     *  
     * @param type $openMode     */

    public function setOpenModeFull() {
        $this->openMode = self::OPENMODE_FULL;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->tipoNome = 'auto';
                if (isset($_POST[$this->nameForm . "_tipoNome"])) {
                    $this->tipoNome = $_POST[$this->nameForm . "_tipoNome"];
                }
                if (!$this->getPath()) {
                    $this->setPathFromParameter();
                }

                $this->flagPDFA = $_POST[$this->nameForm . "_flagPDFA"];
                $this->returnModel = $_POST[$this->nameForm . "_returnModel"];
                $this->returnMethod = $_POST[$this->nameForm . "_returnMethod"];
                $this->returnField = $_POST[$this->nameForm . "_returnField"];

                Out::setDialogTitle($this->nameForm, $_POST[$this->nameForm . "_title"]);
                Out::show($this->divGestione);
                $this->StopWaitMode();
                if (Conf::ITA_ENGINE_VERSION > '1.0') {
                    Out::codice("pluploadActivate('" . $this->nameForm . "_FileLocale_uploader');");
                }

                $this->fileList = array();
                if (!@is_dir(itaLib::getPrivateUploadPath())) {
                    if (!itaLib::createPrivateUploadPath()) {
                        Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                        $this->returnToParent();
                    }
                }
                if ($this->openMode == self::OPENMODE_WAIT) {
                    if (!$this->getPath()) {
                        Out::MsgInfo("ATTENZIONE", "Nessun percorso di importazione valido.<br> Imposta un percorso valido ");
                        $this->close();
                        break;
                    }
                    $this->StartWaitMode();
                }
                $this->setHead($this->getPath());
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Esci':
                        if (count($this->fileList) > 0) {
                            Out::msgQuestion("Acquisizione Documenti", "Ci sono dei documenti da confermare.<br>Esci senza confermare?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaChiudi', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaChiudi', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        } else {
                            $this->returnToParent();
                        }
                        break;

                    case $this->nameForm . '_ConfermaChiudi':
                        $retList = itaLib::listPrivateUploadPath();
                        if (count($this->fileEsistenti) != 0) {
                            foreach ($this->fileList as $file) {
                                @unlink($file['FILEPATH']);
                            }
                        }
                        itaLib::deletePrivateUploadPath();
                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_Scanner':
                        $this->ApriScanner();
                        break;

                    case $this->nameForm . '_FileLocale':
                        $this->ApriFile();
                        break;

                    case $this->nameForm . '_StartWaitMode':

                        $this->StartWaitMode();
                        break;

                    case $this->nameForm . '_StopWaitMode':
                        $this->StopWaitMode();
                        break;

                    case $this->nameForm . '_Conferma':
                        $this->StopWaitMode();
                        $_POST = array();
                        $_POST['id'] = $this->returnField;
                        $_POST['retList'] = $this->fileList;
                        $objModel = itaModel::getInstance($this->returnModel);
                        $objModel->setEvent($this->returnMethod);
                        if (!$this->returnMethod && $this->returnEvent) {
                            $objModel->setEvent($this->returnEvent);
                        }
                        $objModel->parseEvent();
                        $this->returnToParent();
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
                        $retConvert = $this->convertiPDFA($this->currAllegato['uplFile'], $this->currAllegato['destFile'], true);
                        if ($retConvert['status'] == 0) {
                            $this->aggiungiAllegato($this->currAllegato['randName'], $this->currAllegato['destFile'], $this->currAllegato['origFile']);
                            Out::msgBlock('', 3000, false, "Allegato PDF Convertito a PDF/A: " . $this->currAllegato['origFile']);
                        } else {
                            Out::msgStop("Conversione PDF/A Impossibile", $retConvert['message']);
                        }
                        break;
                }
                break;

            case 'ontimer':

                $this->checkPersonalShare($this->getPath());
                break;

            case 'returnFileFromTwain':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Scanner':
                        $randName = $_POST['retFile'];
                        $timeStamp = date("Ymd_His");
                        $origFile = "Scansione_" . $timeStamp . "." . pathinfo($randName, PATHINFO_EXTENSION);
                        $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
                        $this->aggiungiAllegato($randName, $destFile, $origFile);
                        break;
                }
                break;

            case 'delGridRow':
                if ($_POST['id'] == $this->nameForm . '_gridAcquisizioni') {
                    if (array_key_exists($_POST['rowid'], $this->fileList) == true) {
                        if (!@unlink($this->fileList[$_POST['rowid']]['FILEPATH'])) {
                            Out::msgStop("Gestione Acquisizioni", "Errore in cancellazione file.");
                        } else {
                            unset($this->fileList[$_POST['rowid']]);
                            $this->CaricaGriglia($this->fileList);
                        }
                    }
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                if ($_POST['id'] == $this->nameForm . '_gridAcquisizioni') {
                    if (array_key_exists($_POST['rowid'], $this->fileList) == true) {
                        $fileSrc = $this->fileList[$_POST['rowid']]['FILEPATH'];
                        $baseName = @basename($fileSrc);
                        Out::openDocument(utiDownload::getUrl($baseName, $fileSrc));
                    }
                }
                break;
        }
    }

    function CaricaGriglia($_appoggio, $_tipo = '1') {
        if (is_null($_appoggio))
            $_appoggio = '';

        foreach ($_appoggio as $key => $gridRow) {
            if ($this->tipoNome == 'auto') {
                $_appoggio[$key]['VISNAME'] = $gridRow['FILENAME'];
            } else {
                $_appoggio[$key]['VISNAME'] = $gridRow['FILEORIG'];
            }
        }

        $ita_grid01 = new TableView(
                $this->gridAquisizioni, array('arrayTable' => $_appoggio)
        );

        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10000);
        TableView::enableEvents($this->gridAquisizioni);
        TableView::clearGrid($this->gridAquisizioni);
        $ita_grid01->getDataPage('json');
        return;
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_flagPDFA');
        App::$utente->removeKey($this->nameForm . '_fileList');
        App::$utente->removeKey($this->nameForm . '_uploadDir');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnMethod');
        App::$utente->removeKey($this->nameForm . '_returnField');
        App::$utente->removeKey($this->nameForm . '_fileEsistenti');
        App::$utente->removeKey($this->nameForm . '_currAllegato');
        App::$utente->removeKey($this->nameForm . '_tipoNome');
        //  App::$utente->removeKey($this->nameForm . '_path');
        App::$utente->removeKey($this->nameForm . '_openMode');

        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    function ApriScanner() {
        $modelTwain = 'utiTwain';
        itaLib::openForm($modelTwain, true);
        $appRoute = App::getPath('appRoute.' . substr($modelTwain, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $modelTwain . '.php';
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST[$modelTwain . '_flagPDFA'] = $this->flagPDFA;
        $_POST[$modelTwain . '_returnModel'] = $this->nameForm;
        $_POST[$modelTwain . '_returnMethod'] = 'returnFileFromTwain';
        $_POST[$modelTwain . '_returnField'] = $this->nameForm . '_Scanner';
        $_POST[$modelTwain . '_closeOnReturn'] = '0';
        $modelTwain();
    }

    function ApriFile() {
        if ($_POST['response'] == 'success') {
            $origFile = $_POST['file'];
            $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
            $randName = md5(rand() * time()) . "." . pathinfo($uplFile, PATHINFO_EXTENSION);
            $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
            if (strtoupper(pathinfo($uplFile, PATHINFO_EXTENSION)) == "P7M") {
                $this->praLib->AnalizzaP7m($uplFile);
            }

            $retVerify = $this->verificaPDFA($uplFile);
            if ($retVerify['status'] !== 0) {
                if ($retVerify['status'] == -5) {
                    $flag = $this->flagPDFA;
                    if (!$flag) {
                        $flag = "00A";
                    }
                    $verifyPDFA = substr($flag, 0, 1);
                    $convertPDFA = substr($flag, 1, 1);
                    $PDFLevel = substr($flag, 2, 1);
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
                        $retConvert = $this->convertiPDFA($uplFile, $destFile, true);
                        if ($retConvert['status'] == 0) {
                            $this->aggiungiAllegato($randName, $destFile, $origFile);
                            Out::msgBlock('', 1000, false, "Allegato PDF Convertito a PDF/A verifica il PDF." . $this->currAllegato['origFile']);
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

    function aggiungiAllegato($randName, $destFile, $origFile) {
        $lastElement = count($this->fileList) + 1;
        $this->fileList[$lastElement] = array(
            'rowid' => $lastElement,
            'FILEPATH' => $destFile,
            'FILENAME' => $randName,
            'FILEINFO' => "File Originale: " . $origFile,
            'FILEORIG' => $origFile
        );
        $this->CaricaGriglia($this->fileList);
        if (count($this->fileList) > 0) {
            Out::show($this->nameForm . '_Conferma');
        }
    }

    public function verificaPDFA($fileName) {
        if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) !== 'pdf') {
            $ret['status'] = 0;
            $ret['message'] = "Nulla da verificare";
            return $ret;
        }
        $flag = $this->flagPDFA;
        if (!$flag) {
            $flag = "00A";
        }
        $verifyPDFA = substr($flag, 0, 1);
        $convertPDFA = substr($flag, 1, 1);
        $PDFLevel = substr($flag, 2, 1);
        if ($verifyPDFA == "1") {
            include_once(ITA_LIB_PATH . '/itaPHPCore/itaPDFAUtil.class.php');
            $ret = itaPDFAUtil::verifyPDFSimple($fileName, 2, $PDFLevel);
        } else {
            $ret['status'] = 0;
            $ret['message'] = "Nulla da verificare";
        }
        return $ret;
    }

    public function convertiPDFA($fileName, $outputFile, $deleteFileName = false) {
        if ($fileName == $outpuFile) {
            $ret['status'] = -99;
            $ret['message'] = "Nome file da convertire uguale al nome file convertito. Non ammesso.";
            return $ret;
        }
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaPDFAUtil.class.php');
        if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) !== 'pdf') {
            $ret['status'] = -99;
            $ret['message'] = "file non adatto alla conversione";
            return $ret;
        }

        $flag = $this->flagPDFA;
        if (!$flag) {
            $flag = "00A";
        }
        $verifyPDFA = substr($flag, 0, 1);
        $convertPDFA = substr($flag, 1, 1);
        $PDFLevel = substr($flag, 2, 1);
        $ret = itaPDFAUtil::convertPDF($fileName, $outputFile, 2, $PDFLevel);
        if ($ret['status'] == 0) {
            if ($deleteFileName === true) {
                unlink($fileName);
            }
        }
        return $ret;
    }

    public function StartWaitMode() {
        Out::hide($this->nameForm . '_Scanner');
        Out::hide($this->nameForm . '_FileLocale');
        Out::hide($this->nameForm . '_StartWaitMode');
        Out::show($this->nameForm . '_StopWaitMode');
        if ($this->getOpenMode() == self::OPENMODE_WAIT) {
            Out::hide($this->nameForm . '_StopWaitMode');
        }
        Out::addTimer($this->nameForm . '_divGestione', 5, $this->nameForm, false, true);
    }

    public function StopWaitMode() {

        Out::removeTimer($this->nameForm . '_divGestione');
        Out::show($this->nameForm . '_Scanner');
        Out::show($this->nameForm . '_FileLocale');
        Out::show($this->nameForm . '_StartWaitMode');
        Out::hide($this->nameForm . '_StopWaitMode');
    }

    private function checkPersonalShare($path) {

        $this->setHead($path);
        $this->StopWaitMode();
        $repositoryPath = $path;

        if (!$repositoryPath) {

            Out::MsgStop("ATTENZIONE", "La path $repositoryPath non è valida o non esiste");
            return false;
        }
        if (!is_dir($repositoryPath) || !is_readable($repositoryPath) || !is_writable($repositoryPath)) {
            Out::MsgStop("ATTENZIONE", "La path $repositoryPath non è una directory valida e/o non ha i permessi di lettura / scrittura");
            return false;
        }

        $personalPath = $repositoryPath . "/" . App::$utente->getKey('nomeUtente');
        if (!is_dir($personalPath)) {
            if (!mkdir($personalPath, 0777)) {
                Out::MsgStop("ATTENZIONE", "Non è stato possibile creare la path per l'acquisizione");
                return false;
            }
        }
        if (!is_readable($personalPath) || !is_writable($personalPath)) {

            return false;
        }

        $list = glob($personalPath . '/*');

        if (count($this->fileList) > 0) {
            Out::show($this->nameForm . '_Conferma');
        }

        foreach ($list as $key => $uplFile) {
            $origFile = pathinfo($uplFile, PATHINFO_BASENAME);
            if (!$this->addFile($origFile, $uplFile)) {
                return false;
            }
        }
        $this->StartWaitMode();
        return true;
    }

    private function addFile($origFile, $uplFile, $checkP7m = false) {

        $randName = itaLib::getRandBaseName() . "." . pathinfo($uplFile, PATHINFO_EXTENSION);
        $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;

        if ($checkP7m && strtoupper(pathinfo($uplFile, PATHINFO_EXTENSION)) == "P7M") {
            $this->praLib->AnalizzaP7m($uplFile);
        }

        if (!@rename($uplFile, $destFile)) {

            Out::msgStop("Upload File:", "Errore in salvataggio del file!");
            return false;
        } else {

            $this->aggiungiAllegato($randName, $destFile, $origFile);
        }
        return true;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function getPath() {

        return $this->path;
    }

    private function setPathFromParameter() {
        $devLib = new devLib();
        $config_rec = $devLib->getEnv_config('ACQ_IMMAGINI', 'codice', 'PATH', false);
        $this->setPath($config_rec['CONFIG']);
    }

    public function setHead($path) {
        if ($path) {
            Out::html($this->nameForm . '_divHead', date('d/m/Y H:i:s') . " - " . "Percorso di acquisizione : " . $path);
            Out::html($this->nameForm . '_divMsg', '');
        } else {
            Out::html($this->nameForm . '_divHead', "Nessuna path di acquisizione valida");
            Out::html($this->nameForm . '_divMsg', '');
        }
        Out::hide($this->nameForm . '_Conferma');
    }

}
