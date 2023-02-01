<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';
include_once ITA_LIB_PATH . '/itaPHPDocViewer/itaDocViewer.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaShellExec.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

function utiDocViewer() {
    $utiDocViewer = new utiDocViewer();
    $utiDocViewer->parseEvent();
    return;
}

class utiDocViewer extends itaFrontControllerCW {

    const DOCVIEWER_TAB = 0;
    const DOCVIEWER_MODAL = 1;
    const DOCVIEWER_INNER = 2;
    const DOCVIEWER_INNER_COMPONENT = 3;

    private $viewer;
    private $files;
    private $deleteOnClose;
    private $showButtonBar = true;
    private $mode;

    function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'utiDocViewer';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }

    public function postItaFrontControllerCostruct() {
        $this->files = cwbParGen::getFormSessionVar($this->nameForm, 'files');
        $this->deleteOnClose = cwbParGen::getFormSessionVar($this->nameForm, 'deleteOnClose');
        $this->showButtonBar = cwbParGen::getFormSessionVar($this->nameForm, 'showButtonBar');
        $this->mode = cwbParGen::getFormSessionVar($this->nameForm, 'mode');
        $this->viewer = new itaDocViewer($this->nameForm);
        if (!empty($this->files)) {
            $this->viewer->setFiles($this->files);
        }
    }

    public function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, 'files', $this->files);
            cwbParGen::setFormSessionVar($this->nameForm, 'deleteOnClose', $this->deleteOnClose);
            cwbParGen::setFormSessionVar($this->nameForm, 'showButtonBar', $this->showButtonBar);
            cwbParGen::setFormSessionVar($this->nameForm, 'mode', $this->mode);
        } else {
            if ($this->deleteOnClose === true) {
                foreach ($this->files as $file) {
                    if (file_exists($file['filepath'])) {
                        unlink($file['filepath']);
                    }
                }
            }
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->initViewer();
                break;

            case 'viewRowInline':
            case 'dbClickRow':
                $this->preview($_POST['rowid']);
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_download_get':
                        $this->downloadFile();
                        break;

                    case $this->nameForm . '_Protocolla':
                        $this->ProtocollaFilesSelezionati();
                        break;

                    case $this->nameForm . '_download_open':
                        $this->openFile();
                        break;

                    case $this->nameForm . '_LanciaDocFormale':
                        $this->lanciaProtocollazione("C");
                        break;

                    case $this->nameForm . '_LanciaDocPartenza':
                        $this->lanciaProtocollazione("P");
                        break;

                    case $this->nameForm . '_LanciaDocArrivo':
                        $this->lanciaProtocollazione("A");
                        break;
                }
                break;
            case 'onDownloadCallback':
                $path = urlencode($_POST['data']);
                itaShellExec::shellExec($path, '');
                break;
        }
    }

    public function close() {
        parent::close();
        cwbParGen::removeFormSessionVars($this->nameForm);
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    private function initViewer() {
        $this->caricaGrid();
        if (count($this->files) > 0) {
            $this->preview($this->files[0]['fileid']);  // Se presente almeno un elemento, apre il primo
        }
        if (count($this->files) == 1) {
            $this->hideList();
        }
        if (!$this->showButtonBar) {
            Out::hide($this->nameForm . '_divDownload');
            Out::attributo($this->nameForm . '_divBoxPreview', "style", 0, "position: absolute; left: 0px; right: 0px; top: 0px; bottom: 0px;");
        }
    }

    private function hideList() {
        if ($this->mode == self::DOCVIEWER_INNER_COMPONENT) {
            Out::hide($this->nameForm . '_divGrid');
            Out::css($this->nameForm . '_divDocument', 'left', '0px');
        } else {
            Out::hideLayoutPanel($this->nameForm . '_divGrid');
        }
    }

    private function caricaGrid() {
        $this->viewer->caricaGrid();
    }

    private function preview($id) {
        Out::valore($this->nameForm . '_download_file', $id);
        $this->viewer->previewFile($id);
    }

    private function downloadFile() {
        $id = $_POST[$this->nameForm . '_download_file'];
        $this->viewer->downloadFile($id);
    }

    private function openFile() {
        $id = $_POST[$this->nameForm . '_download_file'];
        $this->viewer->openFile($id);
    }

    private function ProtocollaFilesSelezionati() {
        if (count($this->files) == 1) {
            $allegatiStr = '0';
        } else {
            $allegatiStr = $this->formData[$this->nameForm . '_gridDocViewer']['gridParam']['selarrrow'];
        }
        if (trim($allegatiStr) === '') {
            return;
        }
        $proLib = new proLib();
        $retAbilitati = $proLib->msgQuestionLanciaProtocollo($this->nameForm);
        if (count($retAbilitati) === 1) {
            $this->lanciaProtocollazione($retAbilitati[0]);
        }
    }

    private function lanciaProtocollazione($tipoProt = '') {
        /*
         * Allegati Selezionati
         */
        if (count($this->files) == 1) {
            $allegatiStr = '0';
        } else {
            $allegatiStr = $this->formData[$this->nameForm . '_gridDocViewer']['gridParam']['selarrrow'];
        }
        if (trim($allegatiStr) === '') {
            return;
        }
        
        $allegatiArr = split(",", $allegatiStr);

        $DatiProtocollo = array();

        $DatiProtocollo['TIPO_PROT'] = $tipoProt;
        $DatiProtocollo['COD_OGGETTO'] = '';
        $DatiProtocollo['OGGETTO'] = 'OGGETTO DA COMPLETARE....';
        $DatiProtocollo['FIRMATARIO'] = '';
        $DatiProtocollo['FIRMATARIO_UFFICIO'] = '';
        $DatiProtocollo['MITT_DEST'] = '';
        $DatiProtocollo['ANADES'] = array();
        $DatiProtocollo['TITOLARIO'] = '';
        $DatiProtocollo['ALLEGATI'] = array();
        /*
         *  Copio gli Allegati
         */
        $subPath = "copy-work-docViewer-" . md5(microtime());
        $destFile = itaLib::createAppsTempPath($subPath);

        $ElencoAllegati = array();

        foreach ($allegatiArr as $allegatoIdx) {

            $currentFile = $this->files[$allegatoIdx];

            /*
             *  Rand name
             */
            $randName = md5(rand() * time()) . "." . pathinfo($currentFile['filename'], PATHINFO_EXTENSION);
            $destTemporanea = $destFile . '/' . $randName . "." . pathinfo($currentFile['filename'], PATHINFO_EXTENSION);
            if (!copy($currentFile['filepath'], $destTemporanea)) {
//                            $this->setErrCode(-1);
//                            $this->setErrMessage("Copia allegato $SorgenteFile.");
                return false;
            }
            $ElencoAllegati[] = array(
                'FILEPATH' => $destTemporanea,
                'FILENAME' => $randName,
                'FILEINFO' => $currentFile['filename'],
                'DOCNAME' => $currentFile['filename']
            );
        }

        $DatiProtocollo['ALLEGATI'] = $ElencoAllegati;
        $proLib = new proLib();
        if (!$proLib->ProtocollaWizard($DatiProtocollo)) {
//                            Out::msgStop("Attenzione", $segLibProtocollo->getErrMessage());
        }
        $this->close();
    }

    public function setFiles($files) {
        $this->viewer->setFiles($files);
        $this->files = $this->viewer->getFiles();
    }

    public function setDeleteOnClose($deleteOnClose = false) {
        $this->deleteOnClose = $deleteOnClose;
    }

    public function setShowButtonBar($showButtonBar) {
        $this->showButtonBar = $showButtonBar;
    }

    public function setMode($mode) {
        $this->mode = $mode;
    }

}
