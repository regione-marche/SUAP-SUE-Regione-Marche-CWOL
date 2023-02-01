<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontController.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFilebox.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

function utiFilebox() {
    $utiFilebox = new utiFilebox();
    $utiFilebox->parseEvent();
    return;
}

class utiFilebox extends itaFrontController {

    public $nameForm = "utiFilebox";
    private $gridFiles = "utiFilebox_gridFiles";
    private $fileList = array();
    private $currentFile;
    private $paramsEnte;
    private $tipoProtocollo;

    public function __construct() {
        parent::__construct();
        $this->fileList = App::$utente->getKey($this->nameForm . '_fileList');
        $this->currentFile = App::$utente->getKey($this->nameForm . '_currentFile');
        $utiEnte = new utiEnte();
        $this->paramsEnte = $utiEnte->GetParametriEnte();
        $this->tipoProtocollo = $this->paramsEnte['TIPOPROTOCOLLO'];
    }

    public function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_fileList', $this->fileList);
            App::$utente->setKey($this->nameForm . '_currentFile', $this->currentFile);
        }
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                if ($this->tipoProtocollo !== 'Italsoft') {
                    Out::hide($this->nameForm . '_btnProtocollaFilesSelezionati');
                }
                $this->caricaGridFiles();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_btnProtocollaFilesSelezionati':
                        $this->ProtocollaFilesSelezionati();
                        break;

                    case $this->nameForm . '_OpenFile':
                        $this->openFile(false);
                        break;
                    case $this->nameForm . '_btnUpload':
                        $this->upload();
                        break;
                    case $this->nameForm . '_DownloadFile':
                        $this->openFile(true);
                        break;
                    case $this->nameForm . '_DeleteFile':
                        $this->deleteFile();
                        break;
                    case $this->nameForm . '_LanciaDocPartenza':
                        $this->lanciaProtocollazione("P");
                        break;

                    case $this->nameForm . '_LanciaDocFormale':
                        $this->lanciaProtocollazione("C");
                        break;

                    case $this->nameForm . '_LanciaDocArrivo':
                        $this->lanciaProtocollazione("A");
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onClickTablePager':
                $this->caricaGridFiles();
                break;
            case 'viewRowInline':
            case 'dbClickRow':
                $this->currentFile = $this->getFileByKey($_POST['rowid']);
                if (!$this->currentFile) {
                    return;
                }
                $this->openFile(false);
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridFiles:
                        $this->openContextMenu();
                        break;
                }
                break;
            case 'returnUpload':
                $this->handleUpload();
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_fileList');
        App::$utente->removeKey($this->nameForm . '_currentFile');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    private function caricaGridFiles() {
        $this->fileList = itaFilebox::getFiles();
        TableView::clearGrid($this->gridFiles);
        $gridFiles = new TableView($this->gridFiles, array(
            'arrayTable' => $this->fileList,
            'rowIndex' => 'rowid'
        ));
        $gridFiles->setPageNum(1);
        $gridFiles->setPageRows(9999);
        $gridFiles->setSortIndex($_POST['sidx'] ?: 'FILENAME');
        $gridFiles->setSortOrder($_POST['sord'] ?: 'asc');
        $gridFiles->getDataPage('json');

        if (!$this->getDataPage($gridFiles, $this->elaboraRecords($gridFiles))) {
            Out::msgStop("Attenzione", "Nessun file presente!");
        } else {
            TableView::enableEvents($this->gridFiles);
        }
    }

    private function getDataPage($gridFiles, $results) {
        if ($results == null) {
            return $gridFiles->getDataPage('json');
        } else {
            return $gridFiles->getDataPageFromArray('json', $results);
        }
    }

    private function elaboraRecords($gridFiles) {
        $results = $gridFiles->getDataArray();
        if ($results) {
            foreach ($results as $key => $row) {
                $results[$key]['AZIONI'] = '<div align="center"><span style="display:inline-block;" class="ita-icon ita-icon-ingranaggio-24x24" title="Menu" funzioni=""></span></div>';
            }
        }
        return $results;
    }

    private function getFileByKey($key) {
        foreach ($this->fileList as $file) {
            if ($file['TABLEKEY'] == $key) {
                return $file;
            }
        }
        return false;
    }

    private function handleReturnData() {
        $this->currentFile = $this->getFileByKey($_POST['rowid']);
        if (!$this->currentFile) {
            return;
        }
        $_POST = array();
        $_POST['data'] = $this->currentFile;
        $returnObj = itaModel::getInstance($this->returnModel, $this->returnNameForm);
        $returnObj->setEvent($this->returnEvent);
        $returnObj->parseEvent();
        $this->close();
    }

    private function openContextMenu() {
        $this->currentFile = $this->getFileByKey($_POST['rowid']);
        if (!$this->currentFile) {
            return;
        }
        $actions = array();
        $actions['Visualizza'] = array('id' => $this->nameForm . '_OpenFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-document-open-32x32'", 'model' => $this->nameForm);
        $actions['Scarica'] = array('id' => $this->nameForm . '_DownloadFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-download-32x32'", 'model' => $this->nameForm);
        $actions['Elimina'] = array('id' => $this->nameForm . '_DeleteFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-delete-32x32'", 'model' => $this->nameForm);
        Out::msgQuestion("Azioni", "", $actions, 'auto', 'auto', 'true', false, true, true);
    }

    private function openFile($forceDownload = true) {
        Out::openDocument(utiDownload::getUrl($this->currentFile['FILENAME'], $this->currentFile['PATH'], $forceDownload));
    }

    private function deleteFile() {
        $esito = unlink($this->currentFile['PATH']);
        if (!$esito) {
            Out::msgStop('Errore', 'Impossibile eliminare il file!');
        } else {
            $this->caricaGridFiles();
        }
    }

    private function upload() {
        $model = 'utiUploadDiag';
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['messagge'] = '<div style="text-align:center;" class="ita-box ui-widget-content ui-corner-all "><div style="vertical-align:middle;display:inline-block;"></div><div style="top:auto; display:inline-block;font-size:1.5em;color:green;vertical-align:middle;">Seleziona il file da caricare su Filebox</div></div><br>';
        $_POST[$model . '_returnModel'] = $this->nameForm;
        $_POST[$model . '_returnEvent'] = "returnUpload";
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    private function handleUpload() {
        if (!isset($_POST['uploadedFile'])) {
            return;
        }
        $esito = itaFilebox::uploadFile($_POST['uploadedFile'], $_POST['file']);
        if (!$esito) {
            Out::msgStop('Errore', 'Errore caricamento file su Filebox');
        }
        $this->caricaGridFiles();
    }

    private function ProtocollaFilesSelezionati() {
        $allegatiStr = $this->formData[$this->nameForm . '_gridFiles']['gridParam']['selarrrow'];
        if (!$allegatiStr) {
            return;
        }
        $proLib = new proLib();
        $retAbilitati = $proLib->msgQuestionLanciaProtocollo($this->nameForm);
        if (count($retAbilitati) === 1) {
            $this->lanciaProtocollazione($retAbilitati[0], $allegatiStr);
        }
    }

    private function lanciaProtocollazione($tipoProt) {
        /*
         * Allegati Selezionati
         */
        $allegatiStr = $this->formData[$this->nameForm . '_gridFiles']['gridParam']['selarrrow'];
        if (!$allegatiStr) {
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
        $subPath = "copy-work-filebox-" . md5(microtime());
        $destFile = itaLib::createAppsTempPath($subPath);

        foreach ($allegatiArr as $allegatoIdx) {

            $currentFile = $this->getFileByKey($allegatoIdx);

            /*
             *  Rand name
             */
            $randName = md5(rand() * time()) . "." . pathinfo($currentFile['FILENAME'], PATHINFO_EXTENSION);
            $destTemporanea = $destFile . '/' . $randName . "." . pathinfo($currentFile['FILENAME'], PATHINFO_EXTENSION);
            if (!copy($currentFile['PATH'], $destTemporanea)) {
//                            $this->setErrCode(-1);
//                            $this->setErrMessage("Copia allegato $SorgenteFile.");
                return false;
            }
            $ElencoAllegati[] = array(
                'FILEPATH' => $destTemporanea,
                'FILENAME' => $randName,
                'FILEINFO' => $currentFile['FILENAME'],
                'DOCNAME' => $currentFile['FILENAME']
            );
        }

        $DatiProtocollo['ALLEGATI'] = $ElencoAllegati;
        $proLib = new proLib();
        if (!$proLib->ProtocollaWizard($DatiProtocollo)) {
//                            Out::msgStop("Attenzione", $segLibProtocollo->getErrMessage());
        }
        $this->close();
    }

}
