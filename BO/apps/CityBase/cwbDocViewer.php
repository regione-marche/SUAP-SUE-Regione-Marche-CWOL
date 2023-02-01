<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaMimeTypeUtils.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaPDFUtils.class.php';

function cwbDocViewer() {
    $cwbDocViewer = new cwbDocViewer();
    $cwbDocViewer->parseEvent();
    return;
}

class cwbDocViewer extends itaFrontControllerCW {

    const DEFAULT_WEB_PATH = 'webTemp';

    private $files;
    private $webPath;
    private $filePath;
    private $singleMode = false; // documento singolo solo anteprima senza grid

    public function postItaFrontControllerCostruct() {
        $this->files = cwbParGen::getFormSessionVar($this->nameForm, 'files');
    }

    public function __construct($nameFormOrig, $nameForm) {
        parent::__construct($nameFormOrig, $nameForm);

        $devLib = new devLib();
        $path = $devLib->getEnv_config('DOCUMENT_VIEWER', 'codice', 'DOC_TEMP_PATH', false);

        // Legge path dai parametri
        // Se non impostato, va in fallback nella cartella webTemp
        if (!$path) {
            $path = self::DEFAULT_WEB_PATH;
        } else {
            $path = $path['CONFIG'];
            if (!$path) {
                $path = self::DEFAULT_WEB_PATH;
            }
        }

        $this->webPath = $path;
        $this->filePath = ITA_BASE_PATH . DIRECTORY_SEPARATOR . $path;
    }

    public function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, 'files', $this->files);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if ($this->getSingleMode() !== true) {
                    // se è single mode non carica la grid, fa vedere solo il div anteprima con un documento unico
                    $this->visGrid(true);
                    $this->openForm();
                } else {
                    $this->visGrid(false);
                    $this->files[0]['NOME_REALE'] = $this->files[0]['NOME'];                 // Indica il nome del file reale (senza decoding utf-8)
                    $this->files[0]['NOME'] = utf8_decode($this->files[0]['NOME']);
                    $this->files[0]['MIME'] = itaMimeTypeUtils::estraiEstensione($this->files[0]['NOME']);
                }

                if (count($this->files) > 0) {
                    $this->preview($this->files[0]);  // Se presente almeno un elemento, apre il primo
                }
                break;
            case 'dbClickRow':
                $file = cwbLib::searchInMultiArray($this->files, array('NOME' => $_POST[$_POST['id']]['gridParam']['selrow']));
                $this->preview($file[key($file)]);
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        $this->deleteTmpFiles();
        cwbParGen::removeFormSessionVars($this->nameForm);
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function openForm() {
        $this->caricaGrid();
    }

    private function caricaGrid() {
        for ($index = 0; $index < count($this->files); $index++) {
            $file = array();
            $file['NOME'] = utf8_decode($this->files[$index]['NOME']);          // Indica il nome mostrato nella grid (decodificato in utf-8)           
            $file['FILE'] = basename($file['NOME']);                            // File base name
            $file['EXTENSION'] = pathinfo($file['NOME'], PATHINFO_EXTENSION);   // Estensione
            $file['MIME'] = itaMimeTypeUtils::estraiEstensione($file['NOME']);  // File MIME-TYPE
            $file['NOME_REALE'] = $this->files[$index]['NOME'];                 // Indica il nome del file reale (senza decoding utf-8)

            $this->files[$index] = $file;
        }
        $twFiles = new TableView(
                $this->nameForm . '_gridDocViewer', array(
            'arrayTable' => $this->files
        ));
        $twFiles->setPageNum(1);
        $twFiles->setPageRows(999);
        $twFiles->setSortIndex("NOME");
        $twFiles->setSortOrder("ASC");
        TableView::enableEvents($this->nameForm . '_gridDocViewer');
        TableView::clearGrid($this->nameForm . '_gridDocViewer');
        $twFiles->getDataPage('json');
    }

    public function preview($path) {
        switch ($path['MIME']) {
            case 'application/pdf':
                $this->viewPdf($path);
                break;
            case 'application/xml':
                $this->viewXml($path);
                break;
            case 'text/html':
                $this->viewHtml($path);
                break;
            case 'image/jpeg':
                $this->viewImage($path);
                break;
            case 'image/png':
                $this->viewImage($path);
                break;
            case 'image/bmp':
                $this->viewImage($path);
                break;
            case 'image/tiff':
                $this->viewTiff($path);
                break;
            case 'image/gif':
                $this->viewImage($path);
                break;
            case 'image/svg+xml':
                $this->viewImage($path);
                break;
            case 'application/vnd.oasis.opendocument.text':
                $this->viewOpenDocument($path);
                break;
            case 'application/vnd.oasis.opendocument.spreadsheet':
                $this->viewOpenDocument($path);
                break;
            case 'application/vnd.oasis.opendocument.presentation':
                $this->viewOpenDocument($path);
                break;
            case 'application/rtf':
                $this->viewRtf($path);
                break;
            case 'application/json':
                $this->viewJson($path);
                break;
            default:
                if (stripos($path['MIME'], 'text') === 0) {
                    $this->viewText($path);
                } else {
                    
                }
        }
    }

    private function copyToTmpPath($path) {
        $tmpName = $this->nameForm . '_' . uniqid('DOC_VIEWER_');
        if (!file_exists($this->filePath)) {
            mkdir($this->filePath, 0777, true);
        }
        if (!file_exists($this->filePath . DIRECTORY_SEPARATOR . $tmpName)) {
            copy($path, $this->filePath . DIRECTORY_SEPARATOR . $tmpName);
        }
        return $this->webPath . '/' . $tmpName;
    }

    private function deleteTmpFiles() {
        foreach (glob($this->filePath . DIRECTORY_SEPARATOR . $this->nameForm . '_*') as $file) {
            unlink($file);
        }
    }

    public function getFiles() {
        return $this->files;
    }

    public function setFiles($files) {
        $this->files = $files;
    }

    private function viewPdf($path) {
        $file = $this->copyToTmpPath($path['NOME_REALE']);
        $html = '<iframe src="public/libs/pdfjs/web/viewer.html?file=../../../../' . urlencode($file) . '"'
                . 'width="100%" height="100%" style="height: 80vh;"></iframe>';

        Out::html($this->nameForm . '_divPreview', $html);
    }

    private function viewXml($path) {
        $file = $this->copyToTmpPath($path['NOME_REALE']);
        $html = '<div id="XMLHolder"></div>'
                . '<link href="public/libs/docViewer/XMLDisplay/XMLDisplay.css" type="text/css" rel="stylesheet">'
                . '<script type="text/javascript" src="public/libs/docViewer/XMLDisplay/XMLDisplay.js"></script>'
                . '<script>'
                . '  LoadXML("XMLHolder","' . urlencode($file) . '");'
                . '</script>';

        Out::html($this->nameForm . '_divPreview', $html);
    }

    private function viewHtml($path) {
        $file = $this->copyToTmpPath($path['NOME_REALE']);
        $html = '<iframe src="' . urlencode($file) . '"'
                . 'width="100%" height="100%" style="height: 80vh;"></iframe>';

        Out::html($this->nameForm . '_divPreview', $html);
    }

    private function viewImage($path) {
        $file = $this->copyToTmpPath($path['NOME_REALE']);
        $html = '<img src="' . urlencode($file) . '">';

        Out::html($this->nameForm . '_divPreview', $html);
    }

    private function viewOpenDocument($path) {
        $file = $this->copyToTmpPath($path['NOME_REALE']);
        $html = '<iframe src="public/libs/docViewer/ViewerJS/#../../../../' . urlencode($file) . '"'
                . 'width="100%" height="100%" style="height: 80vh;"></iframe>';

        Out::html($this->nameForm . '_divPreview', $html);
    }

    private function viewRtf($path) {
        include_once ITA_BASE_PATH . '/apps/CityBase/docViewer/rtfConverter/rtf-html-php.php';

        $reader = new RtfReader();
        $rtf = file_get_contents($path['NOME_REALE']); // or use a string
        $result = $reader->Parse($rtf);

        $formatter = new RtfHtml();
        $file = $this->nameForm . '_' . $path['FILE'] . '.html';
        file_put_contents($this->filePath . DIRECTORY_SEPARATOR . $file, $formatter->Format($reader->root));
        $html = '<iframe src="' . urlencode($file) . '" width="100%" height="100%" style="height: 80vh;"></iframe>';

        Out::html($this->nameForm . '_divPreview', $html);
    }

    private function viewJson($path) {
        $json = file_get_contents($path['NOME_REALE']);
        $html = '<span id="jsonHolder" style="display:none">(' . $json . ')</span>'
                . '<div id="jsonViewer"></div>'
                . '<script src="public/libs/docViewer/JsonViewer/jquery.json-viewer.js"></script>'
                . '<link href="public/libs/docViewer/JsonViewer/jquery.json-viewer.css" type="text/css" rel="stylesheet" />'
                . '<script>'
                . '  var input = eval($("#jsonHolder").html());'
                . 'console.log(input);'
                . '  $("#jsonViewer").jsonViewer(input, {'
                . '                     collapsed: $("#collapsed").is(":checked"),'
                . '                     withQuotes:  $("#with-quotes").is(":checked")'
                . '                   });'
                . '</script>';

        Out::html($this->nameForm . '_divPreview', $html);
    }

    private function viewText($path) {
        $text = file_get_contents($path['NOME_REALE']);
        $text = htmlentities(utf8_encode($text));
        $html = '<pre class=" line-numbers language-none"><code class="language-none">' . $text . '</code></pre>'
                . '<script src="public/libs/docViewer/prism/prism.js"></script>'
                . '<link href="public/libs/docViewer/prism/prism.css" type="text/css" rel="stylesheet" />';

        Out::html($this->nameForm . '_divPreview', $html);
    }

    private function viewTiff($path) {
        // converto tiff -> pdf e visualizzo pdf
        $itaPDFUtils = new itaPDFUtils();
        if (!$itaPDFUtils->imgToPdf($path['NOME_REALE'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Errore conversione in pdf");
            return false;
        }

        $path['NOME_REALE'] = $itaPDFUtils->getRisultato();

        $this->viewPDF($path);
    }

    private function visGrid($vis) {
        if ($vis) {
            Out::show($this->nameForm . '_divGrid');
            Out::show($this->nameForm . '_divGrid-resizer');
        } else {
            Out::hide($this->nameForm . '_divGrid');
            Out::hide($this->nameForm . '_divGrid-resizer');
        }
    }

    function getSingleMode() {
        return $this->singleMode;
    }

    function setSingleMode($singleMode) {
        $this->singleMode = $singleMode;
    }

}

?>