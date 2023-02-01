<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaScanner.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';

function utiScannerService() {
    $utiScannerService = new utiScannerService();
    $utiScannerService->parseEvent();
    return;
}

class utiScannerService extends itaFrontControllerCW {
    private $viewMode;
    private $savePath;
    private $allowDownload;
    
    function __construct($nameFormOrig=null, $nameForm=null){
        if(!isSet($nameForm) || !isSet($nameFormOrig)){
            $nameFormOrig = 'utiScannerService';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'scanCallback':
                    $this->initScanner($_POST['id']);
                    $this->getScan($_POST['data']);
                break;
        }
    }
    
    private function initScanner($data){
        $data = json_decode($data, true);
        
        $this->viewMode = $data['viewMode'];
        $this->savePath = $data['savePath'];
        $this->allowDownload = $data['allowDownload'];
    }
    
    private function getScan($url){
        if(isSet($this->savePath)){
            $path = $this->savePath;
            rename($url,$path);
        }
        else{
            $path = $url;
        }
        
        $model = 'utiScannerViewer';
        $alias = 'utiScannerViewer_'.time();
        switch($this->viewMode){
            case itaScanner::ITASCANNER_TAB:
                itaLib::openApp($model, '', true, 'desktopBody', '', '', $alias);
                break;
            case itaScanner::ITASCANNER_MODAL:
                itaLib::openDialog($model, '', true, 'desktopBody', '', '', $alias);
                break;
            default:
                return;
        }
        
        $objModel = itaFrontController::getInstance($model, $alias);
        $objModel->initViewer($path,(!isSet($this->savePath)),$this->allowDownload);
        $objModel->setEvent('openform');
        $objModel->parseEvent();
    }
    
}

?>