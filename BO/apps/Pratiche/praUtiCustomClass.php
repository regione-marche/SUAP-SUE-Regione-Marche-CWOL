<?php

/**
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/
 * @author     Carlo Iesari <carlo.iesari@italsoft.eu>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiUploadDiag.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praCustomExplorerLocal.class.php';

function praUtiCustomClass() {
    $praUtiCustomClass = new praUtiCustomClass();
    $praUtiCustomClass->parseEvent();
    return;
}

class praUtiCustomClass extends itaModel {

    public $praCustomExplorerLocal;
    public $nameForm = 'praUtiCustomClass';
    public $gridListing = 'praUtiCustomClass_gridListing';
    private $params;

    function __construct() {
        parent::__construct();
        $this->praCustomExplorerLocal = new praCustomExplorerLocal();
        $this->params = App::$utente->getKey($this->nameForm . '_params');
    }

    function __destruct() {
        parent::__destruct();

        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_params', $this->params);
        }
    }

    private function getParam($key) {
        return isset($this->params[$key]) ? $this->params[$key] : false;
    }

    private function setParam($key, $value) {
        $this->params[$key] = $value;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->updateCurrentPath('/');
                break;

            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridListing:
                        $currentFiles = $this->getParam('currentFiles');
                        $currentElement = $currentFiles[$_POST['rowid']];
                        $elementPath = $this->getElementPath($currentElement);

                        if ($currentElement['filetype'] === praCustomExplorerLocal::TYPE_DIRECTORY && $_POST['event'] === 'dbClickRow') {
                            $this->updateCurrentPath($elementPath);
                            break;
                        }

                        if ($currentElement['filetype'] === praCustomExplorerLocal::TYPE_FILE) {
                            $this->setParam('currentElementPath', $elementPath);
                            Out::msgQuestion("Selezione operazione", "Selezionare un'operazione", array(
                                'Modifica' => array('id' => $this->nameForm . '_ModificaFile', 'model' => $this->nameForm, 'metaData' => "iconLeft: 'ui-icon ui-icon-pencil'"),
                                'Scarica' => array('id' => $this->nameForm . '_ScaricaFile', 'model' => $this->nameForm, 'metaData' => "iconLeft: 'ui-icon ui-icon-disk'")
                            ));
                        }
                        break;
                }
                break;

            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridListing:
                        switch ($_POST['colName']) {
                            case 'download':
                                $currentFiles = $this->getParam('currentFiles');
                                $currentElement = $currentFiles[$_POST['rowid']];

                                if ($currentElement['filetype'] === praCustomExplorerLocal::TYPE_DIRECTORY) {
                                    break;
                                }

                                $this->scaricaFile($this->getElementPath($currentElement));
                                break;

                            case 'edit':
                                $currentFiles = $this->getParam('currentFiles');
                                $currentElement = $currentFiles[$_POST['rowid']];

                                if ($currentElement['filetype'] === praCustomExplorerLocal::TYPE_DIRECTORY) {
                                    break;
                                }

                                $this->openGestione($this->getElementPath($currentElement));
                                break;
                        }
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridListing:
                        $currentPath = $this->getParam('currentPath');
                        if ($currentPath === '/') {
                            $this->creaCartella();
                            break;
                        }

                        Out::msgQuestion("Selezione operazione", "Selezionare il tipo di elemento da creare", array(
                            'Cartella' => array('id' => $this->nameForm . '_CreaCartella', 'model' => $this->nameForm, 'metaData' => "iconLeft: 'ui-icon ui-icon-folder-collapsed'"),
                            'Upload file' => array('id' => $this->nameForm . '_UploadFile', 'model' => $this->nameForm, 'metaData' => "iconLeft: 'ui-icon ui-icon-arrowthickstop-1-n'"),
                            'Nuovo file' => array('id' => $this->nameForm . '_CreaFile', 'model' => $this->nameForm, 'metaData' => "iconLeft: 'ui-icon ui-icon-document'")
                        ));
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridListing:
                        $currentFiles = $this->getParam('currentFiles');
                        $currentElement = $currentFiles[$_POST['rowid']];

                        if ($currentElement['filetype'] === praCustomExplorerLocal::TYPE_DIRECTORY) {
                            if (in_array($currentElement['filename'], array('config', 'lib', 'resources'))) {
                                Out::msgStop("Errore", "Non è possibile eliminare la cartella '{$currentElement['filename']}'.");
                                break;
                            }

                            if (intval($currentElement['filecount']) > 0) {
                                Out::msgStop("Errore", "La cartella contiene dei file, impossibile eliminare.");
                                break;
                            }
                        }

                        $this->setParam('currentElementPath', $this->getElementPath($currentElement));
                        Out::msgQuestion("Eliminazione", "Confermi l'eliminazione di '{$currentElement['filename']}'?", array(
                            'F8 - Annulla' => array('id' => $this->nameForm . '_AnnullaEliminaElemento', 'model' => $this->nameForm, 'shortCut' => 'f8'),
                            'F5 - Conferma' => array('id' => $this->nameForm . '_ConfermaEliminaElemento', 'model' => $this->nameForm, 'shortCut' => 'f5')
                        ));
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridListing:
                        $this->caricaGridListing();
                        break;
                }

                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenco':
                        $this->updateCurrentPath($this->getParam('currentPath'));
                        break;

                    case $this->nameForm . '_Salva':
                        $currentElementPath = $this->getParam('currentElementPath');

                        if ($currentElementPath) {
                            // Aggiornamento file
                            $base64 = base64_encode($_POST[$this->nameForm . '_codeMirror']);
                            if (!$this->praCustomExplorerLocal->updateFile($currentElementPath, $base64)) {
                                Out::msgStop("Errore", $this->praCustomExplorerLocal->getErrMessage());
                                break;
                            }

                            $this->updateCurrentPath($this->getParam('currentPath'));
                        } else {
                            // Creazione file
                            Out::msgInput('Creazione file', array(
                                'label' => array(
                                    'style' => 'width: 100px;',
                                    'value' => 'Nome file'
                                ),
                                'id' => $this->nameForm . '_currentElement[filename]',
                                'name' => $this->nameForm . '_currentElement[filename]',
                                'type' => 'text',
                                'size' => '20',
                                'maxchars' => '50',
                                'required' => true
                            ), array(
                                'Aggiungi' => array('id' => $this->nameForm . '_confermaNuovoFile', 'model' => $this->nameForm, 'metaData' => "iconLeft: 'ui-icon ui-icon-check'")
                            ), $this->nameForm);
                        }
                        break;

                    case $this->nameForm . '_confermaNuovoFile':
                        $base64 = base64_encode($_POST[$this->nameForm . '_codeMirror']);
                        $currentElement = $_POST[$this->nameForm . '_currentElement'];
                        $newFile = $this->getElementPath($currentElement);
//
                        if (!$this->praCustomExplorerLocal->insertFile($newFile, $base64)) {
                            Out::msgStop("Errore", $this->praCustomExplorerLocal->getErrMessage());
                            break;
                        }

                        $this->updateCurrentPath($this->getParam('currentPath'));
                        break;

                    case $this->nameForm . '_GoTo':
                        $this->updateCurrentPath($_POST['path']);
                        break;

                    case $this->nameForm . '_ScaricaFile':
                        $currentFile = $this->getParam('currentElementPath');
                        $this->scaricaFile($currentFile);
                        break;

                    case $this->nameForm . '_ModificaFile':
                        $currentFile = $this->getParam('currentElementPath');
                        $this->openGestione($currentFile);
                        break;

                    case $this->nameForm . '_CreaCartella':
                        $this->creaCartella();
                        break;

                    case $this->nameForm . '_UploadFile':
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST['messagge'] = '';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUpload";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case $this->nameForm . '_CreaFile':
                        $this->openGestione();
                        break;

                    case $this->nameForm . '_confermaNuovaCartella':
                        $currentElement = $_POST[$this->nameForm . '_currentElement'];
                        $newFolder = $this->getElementPath($currentElement);

                        if (!$this->praCustomExplorerLocal->insertDirectory($newFolder)) {
                            Out::msgStop("Errore", $this->praCustomExplorerLocal->getErrMessage());
                            break;
                        }

                        $this->caricaGridListing();
                        break;

                    case $this->nameForm . '_ConfermaEliminaElemento':
                        $currentFile = $this->getParam('currentElementPath');

                        if (!$this->praCustomExplorerLocal->deleteElement($currentFile)) {
                            Out::msgStop("Errore", $this->praCustomExplorerLocal->getErrMessage());
                            break;
                        }

                        $this->caricaGridListing();
                        break;

                    case $this->nameForm . '_ConfermaSovrascriviElemento':
                        $overwriteElement = $this->getParam('overwriteElement');
                        $base64 = base64_encode(file_get_contents($overwriteElement['sorgente']));

                        if (!$this->praCustomExplorerLocal->updateFile($overwriteElement['destinazione'], $base64)) {
                            Out::msgStop("Errore", $this->praCustomExplorerLocal->getErrMessage());
                            break;
                        }

                        $this->setParam('overwriteElement', false);

                        $this->caricaGridListing();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;

            case 'returnUpload':
                $currentElement = array('filename' => $_POST['file']);
                $currentElementPath = $this->getElementPath($currentElement);

//                $existingFile = false;
//                $currentFiles = $this->getParam('currentFiles');
//                foreach ($currentFiles as $directoryFile) {
//                    if ($directoryFile['filename'] === $currentElement['filename']) {
//                        $existingFile = true;
//                    }
//                }
                $base64 = base64_encode(file_get_contents($_POST['uploadedFile']));

                if (!$this->praCustomExplorerLocal->insertFile($currentElementPath, $base64)) {
                    if ($this->praCustomExplorerLocal->getErrCode() === praCustomExplorerLocal::ERR_FILE_EXISTS) {
                        $this->setParam('overwriteElement', array(
                            'sorgente' => $_POST['uploadedFile'],
                            'destinazione' => $currentElementPath
                        ));

                        Out::msgQuestion("Upload file", "Sostituire il file {$currentElement['filename']} con quello caricato?", array(
                            'F8 - Annulla' => array('id' => $this->nameForm . '_AnnullaSovrascriviElemento', 'model' => $this->nameForm, 'shortCut' => 'f8'),
                            'F5 - Conferma' => array('id' => $this->nameForm . '_ConfermaSovrascriviElemento', 'model' => $this->nameForm, 'shortCut' => 'f5')
                        ));

                        break;
                    }

                    Out::msgStop("Errore", $this->praCustomExplorerLocal->getErrMessage());
                    break;
                }

                $this->caricaGridListing();
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_params');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function workSpace() {
        Out::hide($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divGestione');

        if (func_num_args()) {
            foreach (func_get_args() as $div) {
                Out::show($this->nameForm . "_$div");
            }
        }
    }

    public function buttonBar() {
        Out::hide($this->nameForm . '_Elenco');
        Out::hide($this->nameForm . '_Salva');

        if (func_num_args()) {
//            Out::showLayoutPanel($this->nameForm . '_buttonBar');

            foreach (func_get_args() as $button) {
                Out::show($this->nameForm . "_$button");
            }
        } else {
//            Out::hideLayoutPanel($this->nameForm . '_buttonBar');
        }
    }

    public function openRisultato() {
        $this->workSpace('divRisultato');
        $this->buttonBar();

        TableView::enableEvents($this->gridListing);
    }

    public function openGestione($filePath = false) {
        $this->workSpace('divGestione');
        $this->buttonBar('Elenco', 'Salva');

        if (!$filePath) {
            $this->setParam('currentElementPath', false);
            Out::html($this->nameForm . '_divNavigator', 'Nuovo file');
            Out::valore($this->nameForm . '_codeMirror', '');
            Out::codeEditorMode($this->nameForm . '_codeMirror', 'text');
        } else {
            $base64 = $this->praCustomExplorerLocal->getFile($filePath);
            $this->setParam('currentElementPath', $filePath);
            Out::html($this->nameForm . '_divNavigator', basename($filePath));
            Out::valore($this->nameForm . '_codeMirror', base64_decode($base64));
            Out::codeEditorMode($this->nameForm . '_codeMirror', pathinfo($filePath, PATHINFO_EXTENSION));
        }

        TableView::disableEvents($this->gridListing);
    }

    public function caricaGridListing() {
        $currentPath = $this->getParam('currentPath');
        $this->setParam('currentFiles', $this->praCustomExplorerLocal->getDirectory($currentPath));

        Out::setGridCaption($this->gridListing, basename($currentPath) ? basename($currentPath) : 'CustomClass');
        $this->caricaGrigliaArray($this->gridListing, $this->elaboraGridListing($this->getParam('currentFiles')));
    }

    public function elaboraGridListing($datatab) {
        foreach ($datatab as &$record) {
            $icon = '';
            $iconStyle = 'style="display: inline-block; vertical-align: bottom;"';

            switch ($record['filetype']) {
                case praCustomExplorerLocal::TYPE_FILE:
                    $icon = '<i class="ui-icon ui-icon-document" ' . $iconStyle . '></i>';
                    $record['download'] = '<span class="ui-icon ui-icon-disk" style="margin: auto;"></span>';
                    $record['edit'] = '<span class="ui-icon ui-icon-pencil" style="margin: auto;"></span>';
                    break;

                case praCustomExplorerLocal::TYPE_DIRECTORY:
                    $icon = '<i class="ui-icon ui-icon-folder-collapsed" ' . $iconStyle . '></i>';
                    break;
            }

            $record['filename'] = $icon . ' ' . $record['filename'];
            $record['filecount'] .= '&nbsp;';
        }

        return $datatab;
    }

    public function caricaGrigliaArray($id, $data) {
        TableView::clearGrid($id);
        $gridScheda = new TableView($id, array('arrayTable' => $data, 'rowIndex' => 'idx'));

        if ($_POST['page'])
            $gridScheda->setPageNum($_POST['page']);
        if ($_POST['rows'])
            $gridScheda->setPageRows($_POST['rows']);
        if ($_POST['sidx'])
            $gridScheda->setSortIndex($_POST['sidx']);
        if ($_POST['sord'])
            $gridScheda->setSortOrder($_POST['sord']);

        $gridScheda->getDataPage('json');
    }

    public function updateCurrentPath($newPath) {
        $this->setParam('currentPath', $newPath);

        $this->openRisultato();
        $this->updateNavigator();

        $this->caricaGridListing();
    }

    public function updateNavigator() {
        $currentPath = explode('/', $this->getParam('currentPath'));

        $navigatorHtml = '';
        $navigatorHtml .= '<a href="#" onclick="itaGo(\'ItaCall\', this, { event: \'onClick\', model: \'' . $this->nameForm . '\', id: \'' . $this->nameForm . '_GoTo\', path: \'/\' });">CustomClass</a>';

        foreach ($currentPath as $k => $folderName) {
            if ($folderName) {
                $folderPath = implode('/', array_slice($currentPath, 0, $k + 1));
                $navigatorHtml .= ' &raquo; <a href="#" onclick="itaGo(\'ItaCall\', this, { event: \'onClick\', model: \'' . $this->nameForm . '\', id: \'' . $this->nameForm . '_GoTo\', path: \'' . $folderPath . '\' });">' . $folderName . '</a>';
            }
        }

        Out::html($this->nameForm . '_divNavigator', $navigatorHtml);
    }

    private function getElementPath($currentElement) {
        $currentPath = $this->getParam('currentPath');
        if ($currentPath === '/') {
            $currentPath = '';
        }

        return $currentPath . '/' . $currentElement['filename'];
    }

    private function scaricaFile($filePath) {
        $base64 = $this->praCustomExplorerLocal->getFile($filePath);
        $tempPath = itaLib::createAppsTempPath($this->nameForm) . '/temp_download_file';
        file_put_contents($tempPath, base64_decode($base64));
        Out::openDocument(utiDownload::getUrl(basename($filePath), $tempPath));
    }

    private function creaCartella() {
        Out::msgInput('Creazione cartella', array(
            'label' => array(
                'style' => 'width: 100px;',
                'value' => 'Nome cartella'
            ),
            'id' => $this->nameForm . '_currentElement[filename]',
            'name' => $this->nameForm . '_currentElement[filename]',
            'type' => 'text',
            'size' => '20',
            'maxchars' => '50',
            'required' => true
        ), array(
            'Aggiungi' => array('id' => $this->nameForm . '_confermaNuovaCartella', 'model' => $this->nameForm, 'metaData' => "iconLeft: 'ui-icon ui-icon-check'")
        ), $this->nameForm);
    }

}
