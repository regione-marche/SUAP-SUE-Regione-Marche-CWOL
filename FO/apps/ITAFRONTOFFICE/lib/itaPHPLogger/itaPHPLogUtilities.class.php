<?php
require_once ITA_LIB_PATH . '/itaPHPLogger/itaPHPLogReaderFactory.php';
require_once ITA_LIB_PATH . '/itaPHPLogger/itaPHPLogReader/itaPHPLogReader.interface.php';

class itaPHPLogUtilities{
    public static function renderLogToText($htmlTarget,$log,$logOrder=itaPHPLogReaderInterface::LOG_ORDER_DESC,$start=0,$limit=100){
        $logReader = itaPHPLogReaderFactory::getInstance($log);
        $logs = $logReader->getLogs($logOrder, $start, $limit);
        
        $start = true;
        $html = '<pre class=" line-numbers language-none"><code class="language-none">';
        foreach($logs as $row){
            if($start) $start = false;
            else $html .= "\r\n";
            
            $html .= htmlentities(utf8_encode(
                    date('H:i:s - d/m/Y',$row['timestamp']) . ': ' . $row['msg']
            ));
        }
        $html.= '</code></pre>';
        
        
        Out::codice("itaGetLib('libs/prism/prism.js');");
        Out::codice("itaGetLib('libs/prism/prism.css');");
        Out::html($htmlTarget,$html);
    }
    
    public static function openLogViewerText($log,$logOrder=itaPHPLogReaderInterface::LOG_ORDER_DESC,$start=0,$limit=100){
        if(!file_exists($log)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'File di log inesistente');
        }
        
        itaLib::openDialog('utiLogViewerText', true, true, 'desktopBody', "", '');
        $objModel = itaFrontController::getInstance('utiLogViewerText');
        $objModel->setLogFile($log);
        $objModel->setEvent('openform');
        $objModel->parseEvent();
    }
    
    public static function modalLogViewerText($log,$logOrder=itaPHPLogReaderInterface::LOG_ORDER_DESC,$start=0,$limit=50){
        if(!file_exists($log)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'File di log inesistente');
        }
        
        itaLib::openDialog('utiLogViewerText');
        $objModel = itaModel::getInstance('utiLogViewerText');
        $objModel->setLogFile($log);
        $objModel->setEvent('openform');
        $objModel->parseEvent();
        return true;
    }
}
?>