<?php

require_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
require_once ITA_LIB_PATH . '/itaPHPSmartAgent/SmartAgent.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaMimeTypeUtils.class.php';
require_once ITA_LIB_PATH . '/itaPHPVersign/itaP7m.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaPDFUtils.class.php';

class itaDocViewer {

    private $files = array();
    private $nameForm;
    private $gridName;
    private $divPreviewName;

    public function __construct($nameForm, $gridName = null, $divPreviewName = null) {
        $this->nameForm = $nameForm;

        switch ($gridName) {
            case null: $this->gridName = $nameForm . '_gridDocViewer';
                break;
            case false: $this->gridName = null;
                break;
            default: $this->gridName = $gridName;
                break;
        }
        switch ($divPreviewName) {
            case null: $this->divPreviewName = $nameForm . '_divPreview';
                break;
            case false: $this->divPreviewName = null;
                break;
            default: $this->divPreviewName = $divPreviewName;
                break;
        }
    }

    private function normalizeFiles($files = array()) {
        if (is_array($files) && is_array($files[0]) && isSet($files[0]['fileid']) && isSet($files[0]['filepath'])
                && isSet($files[0]['filename']) && isSet($files[0]['mime'])) {
            $this->files = $files;
            return;
        }
        if (!is_array($files) || isSet($files['FilePath'])) {
            $files = array($files);
        }

        $this->files = array();
        for ($i = 0; $i < count($files); $i++) {
            if (is_array($files[$i]) && is_array($files[$i]) && isSet($files[$i]['FilePath']) && !is_file($files[$i]['FilePath'])) {
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Il file " . $files[$i]['FilePath'] . " non esiste");
            } elseif (is_string($files[$i]) && !is_file($files[$i])) {
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Il file " . $files[$i] . " non esiste");
            }

            $obj = array();
            $obj['fileid'] = $i;
            $obj['filepath'] = (is_array($files[$i]) && isSet($files[$i]['FilePath']) ? $files[$i]['FilePath'] : $files[$i]);
            $obj['filename'] = (is_array($files[$i]) && isSet($files[$i]['FileName']) ? $files[$i]['FileName'] : basename($obj['filepath']));
            $obj['mime'] = itaMimeTypeUtils::estraiEstensione($obj['filepath'], true, true);

            $this->files[$i] = $obj;
        }
    }

    /**
     * Setta i file da visualizzare
     * @param <string|array> $files prende: <string> path di un singolo file
     *                                      <array> array di stringhe contenenti path di file
     *                                      <array> array di array contenenti 'FilePath' (path del file) e 'FileName' (nome del file)
     */
    public function setFiles($files) {
        $this->normalizeFiles($files);
    }

    public function getFiles() {
        return $this->files;
    }

    public function caricaGrid() {
//        TableView::enableEvents($this->gridName);
        TableView::disableEvents($this->gridName);
        TableView::clearGrid($this->gridName);

        $twFiles = new TableView(
                $this->gridName, array('arrayTable' => $this->files), 1, 999999, 'fileid', 'ASC'
        );

        $twFiles->getDataPage('json');
    }

    public function openFile($id) {
        if (!isSet($this->files[$id])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Il file con id $id non è stato caricato nel visualizzatore");
        }

        $file = $this->files[$id];
        $url = utiDownload::getUrl($file['filename'], $file['filepath'], true);

        $smartAgent = new SmartAgent();
        if ($smartAgent->isEnabled()) {
            $smartAgent->downloadFile($file['filename'], $url, $this->nameForm, 'download', 'onDownloadCallback');
        } else {
            Out::msgStop("ERRORE", "Smartagent non configurato");
        }
    }

    public function downloadFile($id) {
        if (!isSet($this->files[$id])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Il file con id $id non è stato caricato nel visualizzatore");
        }

        $file = $this->files[$id];
        $url = utiDownload::getUrl($file['filename'], $file['filepath'], true);
        Out::openDocument($url);
    }

    public function previewFile($id) {
        if (!isSet($this->files[$id])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Il file con id $id non è stato caricato nel visualizzatore");
        }

        $file = $this->files[$id];

        $this->previewFileInner($file);
    }

    private function previewFileInner($file) {
        switch ($file['mime']) {
            case 'application/pdf':
                $this->renderPDF($file);
                break;
            case 'application/xml':
                $this->renderXML($file);
                break;
            case 'text/html':
                $this->renderHTML($file);
                break;
            case 'image/jpeg':
            case 'image/png':
            case 'image/bmp':
            case 'image/gif':
            case 'image/svg+xml':
                $this->renderImage($file);
                break;
            case 'application/vnd.oasis.opendocument.text':
            case 'application/vnd.oasis.opendocument.spreadsheet':
            case 'application/vnd.oasis.opendocument.presentation':
                $this->renderOpenDocument($file);
                break;
            case 'application/json':
                $this->renderJson($file);
                break;
            case 'application/pkcs7-mime':
                $this->renderP7m($file);
                break;
            case 'image/tiff':
                $this->renderTiff($file);
                break;
            default:
                if (stripos($file['mime'], 'text') === 0) {
                    $this->renderText($file);
                } else {
                    $this->renderUnknown();
                }
        }
    }

//    private function renderPDF($file){
//        Out::html($this->divPreviewName, '');
//        
//        $url = urlencode(utiDownload::getUrl($file['filename'], $file['filepath']));
//        $html = "<iframe src='public/libs/pdfjs/web/viewer.html?file=../../../../$url' style='height: 100%; width: 100%'></iframe>";
//        Out::html($this->divPreviewName, $html);
//    }
    private function renderPDF($file) {
        Out::html($this->divPreviewName, '');
        
        $url = urlencode(utiDownload::getUrl($file['filename'], $file['filepath']));
        
        $html = "<iframe src='public/libs/pdfjs/web/viewer.html?file=../../../../$url' width='100%' height='100%' style='height: 80vh;'></iframe>";
        Out::html($this->divPreviewName, $html);
    }

    private function renderXML($file) {
        Out::html($this->divPreviewName, '');
        $xml = file_get_contents($file['filepath']);
        if (strtolower(substr($file['filepath'], -3)) == 'xml' && preg_match('/<([A-Za-z0-9]*):(FatturaElettronica|FileMetadati|MetadatiInvioFile|RicevutaScarto|NotificaScarto|RicevutaImpossibilitaRecapito|AttestazioneTrasmissioneFattura|ScartoEsitoCommittente|RicevutaConsegna|NotificaEsito|NotificaMancataConsegna|NotificaEsitoCommittente|NotificaDecorrenzaTermini).*?versione="([A-Z0-9\.]*)".*?>/i', $xml)) {
            require_once ITA_BASE_PATH . '/apps/Protocollo/proLibSdi.class.php';
            $libSDI = new proLibSdi();

            $xml = $libSDI->sdiAttachStyle($xml);

            $newFilePath = itaLib::createAppsTempPath('docViewerTempFiles') . '/s' . basename($file['filepath']);
            file_put_contents($newFilePath, $xml);
            unset($xml);

            $url = utiDownload::getUrl($file['filename'], $newFilePath, false);
            $html = "<iframe src='$url' style='height: 100%; width: 100%'></iframe>";
            Out::html($this->divPreviewName, $html);
        } elseif (preg_match('/<\?xml-stylesheet.*?href[ ]*=[ ]*["\']([A-Za-z0-9\-_\.]*)["\'].*?\?>/', $xml, $matches)) {
            $style = str_replace($matches[1], '../../../public/xsl/' . $matches[1], $matches[0]);
            $xml = str_replace($matches[0], $style, $xml);

            $newFilePath = itaLib::createAppsTempPath('docViewerTempFiles') . '/s' . basename($file['filepath']);
            file_put_contents($newFilePath, $xml);
            unset($xml);

            $url = utiDownload::getUrl($file['filename'], $newFilePath, false);
            $html = "<iframe src='$url' style='height: 100%; width: 100%'></iframe>";
            Out::html($this->divPreviewName, $html);
        } else {
            unset($xml);

            $url = utiDownload::getUrl($file['filename'], $file['filepath'], true);

            Out::codice("itaGetLib('libs/XMLDisplay/XMLDisplay.js');");
            Out::codice("itaGetLib('libs/XMLDisplay/XMLDisplay.css');");
            Out::codice("LoadXML('{$this->divPreviewName}','$url');");
        }
    }

    private function renderHTML($file) {
        Out::html($this->divPreviewName, '');

        $url = utiDownload::getUrl($file['filename'], $file['filepath'], true);
        $html = "<iframe src='$url' style='height: 100%; width: 100%'></iframe>";
        Out::html($this->divPreviewName, $html);
    }

    private function renderImage($file) {
        Out::html($this->divPreviewName, '');

        $url = utiDownload::getUrl($file['filename'], $file['filepath'], true);
        $html = "<img src='$url'>";
        Out::html($this->divPreviewName, $html);
    }

    private function renderOpenDocument($file) {
        Out::html($this->divPreviewName, '');
        $url = utiDownload::getUrl($file['filename'], $file['filepath'], true);

        $html = "<iframe src='public/libs/WebODF/#$url' style='height: 100%; width: 100%'></iframe>";
        Out::html($this->divPreviewName, $html);
    }

    private function renderJson($file) {
        Out::html($this->divPreviewName, '');
        Out::codice("itaGetLib('libs/JsonViewer/jquery.json-viewer.js');");
        Out::codice("itaGetLib('libs/JsonViewer/jquery.json-viewer.css');");
        Out::codice('$("#' . $this->divPreviewName . '").jsonViewer(' . file_get_contents($file['filepath']) . ', {'
                . '    collapsed: $("#collapsed").is(":checked"),'
                . '    withQuotes:  $("#with-quotes").is(":checked")});');
    }

    private function renderP7m($file) {
        $p7mPath = $file['filepath'];
        $p7m = itaP7m::getP7mInstance($p7mPath);
        if ($p7m !== false) {
            $p7mExtractedPath = $p7m->getContentFileName();

            $obj = array();
            $obj['filepath'] = $p7mExtractedPath;
            $obj['filename'] = basename($p7mExtractedPath);
            $obj['mime'] = itaMimeTypeUtils::estraiEstensione($p7mExtractedPath, true, true);

            $this->previewFileInner($obj, true);
        }
    }

    private function renderText($file) {
        Out::html($this->divPreviewName, '');
        Out::codice("itaGetLib('libs/prism/prism.js');");
        Out::codice("itaGetLib('libs/prism/prism.css');");
        Out::html($this->divPreviewName, '<pre class=" line-numbers language-none"><code class="language-none">' . htmlentities(utf8_encode(file_get_contents($file['filepath']))) . '</code></pre>');
    }

    private function renderTiff($file) {
        // converto tiff -> pdf e visualizzo pdf
        $itaPDFUtils = new itaPDFUtils();
        if (!$itaPDFUtils->imgToPdf($file['filepath'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Errore conversione in pdf");
            return false;
        }

        $obj = array();
        $obj['filepath'] = $itaPDFUtils->getRisultato();
        $obj['filename'] = basename($itaPDFUtils->getRisultato());
        $obj['mime'] = itaMimeTypeUtils::estraiEstensione($itaPDFUtils->getRisultato(), true, true);

        $this->renderPDF($obj);
    }

    private function renderUnknown() {
        Out::html($this->divPreviewName, '<div style="width:100%; text-align:center; font-size: 1.4em; padding-top: 30px;">Questa tipologia di file non è gestita dal visualizzatore di documenti, è comunque possibile scaricare il file o aprirlo tramite l\'editor di sistema</div>');
    }

}

?>