<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontController.class.php';
include_once ITA_LIB_PATH . '/itaPHPLogger/itaPHPLogReaderFactory.php';
include_once ITA_LIB_PATH . '/itaPHPLogger/itaPHPLogReader/itaPHPLogReader.interface.php';

function utiLogViewerText() {
    $utiLogViewerText = new utiLogViewerText();
    $utiLogViewerText->parseEvent();
    return;
}

class utiLogViewerText extends itaModel {

    public $nameForm = "utiLogViewerText";
    private $logFile;
    private $logOrder;
    private $start;
    private $limit;

    function __construct() {
        $this->logOrder = itaPHPLogReaderInterface::LOG_ORDER_DESC;
        $this->start = 0;
        $this->limit = 100;

        parent::__construct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->renderLog();
                break;
        }
    }

    public function setLogFile($logFile) {
        $this->logFile = $logFile;
    }

    public function setOptions($logOrder = itaPHPLogReaderInterface::LOG_ORDER_DESC, $start = 0, $limit = 100) {
        $this->logOrder = $logOrder;
        $this->start = $start;
        $this->limit = $limit;
    }

    private function renderLog() {
        try {
            $logReader = itaPHPLogReaderFactory::getInstance($this->logFile);
            $logs = $logReader->getLogs($this->logOrder, $this->start, $this->limit);
            $start = true;
            $html = '<pre class=" line-numbers language-none"><code class="language-none">';
            foreach ($logs as $row) {
                if ($start)
                    $start = false;
                else
                    $html .= "\r\n";

                $html .= htmlentities(utf8_encode(
                        date('H:i:s - d/m/Y', $row['timestamp']) . ': ' . $row['msg']
                ));
            }
            $html.= '</code></pre>';

            Out::codice("itaGetLib('libs/prism/prism.js', 'Prism');");
            Out::codice("itaGetLib('libs/prism/prism.css');");
            Out::html($this->nameForm . '_logs_viewer', $html);
        } catch (ItaException $e) {
            Out::msgStop("Errore", $e->getNativeErroreDesc());
        }
    }

}

?>