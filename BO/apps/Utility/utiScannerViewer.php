<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_LIB_PATH . '/itaPHPSmartAgent/SmartAgent.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaShellExec.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';

function utiScannerViewer() {
    $utiScannerViewer = new utiScannerViewer();
    $utiScannerViewer->parseEvent();
    return;
}

class utiScannerViewer extends itaFrontControllerCW {
    private $filePath;
    private $deleteFile;
    private $allowDownload;
    
    function __construct($nameFormOrig=null, $nameForm=null){
        if(!isSet($nameForm) || !isSet($nameFormOrig)){
            $nameFormOrig = 'utiScannerViewer';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    public function initViewer($filePath,$deleteFile=true,$allowDownload=true){
        $this->filePath = $filePath;
        $this->deleteFile = $deleteFile;
        $this->allowDownload = $allowDownload;
        
        $sessionVar = array();
        $sessionVar['filePath'] = $filePath;
        $sessionVar['deleteFile'] = $deleteFile;
        $sessionVar['allowDownload'] = $allowDownload;
        
        cwbParGen::setFormSessionVar($this->nameForm, 'scannerViewerData', $sessionVar);
    }
    
    public function postItaFrontControllerCostruct() {
        $sessionVar = cwbParGen::getFormSessionVar($this->nameForm, 'scannerViewerData');
        $this->filePath = $sessionVar['filePath'];
        $this->deleteFile = $sessionVar['deleteFile'];
        $this->allowDownload = $sessionVar['allowDownload'];
    }
    
    public function close(){
        parent::close();
        
        cwbParGen::removeFormSessionVars($this->nameForm);
        Out::closeDialog($this->nameForm);
        
        if(file_exists($this->filePath) && $this->deleteFile === true){
            unlink($this->filePath);
        }
    }
    
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->showScan();
                break;
            case 'onClick':
                switch($_POST['id']){
                    case $this->nameForm . '_download_get':
                        $this->downloadScan();
                        break;
                    case $this->nameForm . '_download_open':
                        $this->openScan();
                        break;
                    case 'close-portlet':
                        $this->close();
                        break;
                }
                break;
            case 'onDownloadCallback':
                $path = urlencode($_POST['data']);
                itaShellExec::shellExec($path, '');
                break;
        }
    }
    
    private function showScan(){        
        if($this->allowDownload){
            Out::show($this->nameForm . '_download_div');
        }
        else{
            Out::hide($this->nameForm . '_download_div');
        }
        
        Out::html($this->nameForm . '_scanViewer', '');
        $url = urlencode(utiDownload::getOTR(basename($this->filePath), $this->filePath));
        $html = "<iframe src='public/libs/pdfjs/web/viewer.html?file=../../../../$url' width='100%' height='100%' style='height: 80vh;'></iframe>";
        Out::html($this->nameForm . '_scan_viewer', $html);
    }
    
    private function downloadScan(){
        $url = utiDownload::getOTR(basename($this->filePath), $this->filePath);
        
        Out::codice("var link = document.createElement('a');
    document.body.appendChild(link);
    link.download = '".basename($this->filePath)."';
    link.href = '{$url}';
    link.click();
    link.parentNode.removeChild(link);");
    }
    
    private function openScan(){
        $url = utiDownload::getOTR(basename($this->filePath), $this->filePath);
        
        $smartAgent = new SmartAgent();
        if ($smartAgent->isEnabled()) {
            $smartAgent->downloadFile(basename($this->filePath), $url, $this->nameForm, 'download', 'onDownloadCallback');
        } else {
            Out::msgStop("ERRORE", "Smartagent non configurato");
        }
    }
}

?>