<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';

function cwbDownloadUpload() {
    $cwbDownloadUpload = new cwbDownloadUpload();
    $cwbDownloadUpload->parseEvent();
    return;
}

class cwbDownloadUpload extends itaFrontControllerCW {

    // se server linux, l'urlServer è la mount da utilizzare lato server, mentre urlClient è la cartella shared di windows
    // se server windows i 2 url sono uguali
    private $urlServer; 
    private $urlClient;
    private $pathToDelete;

    protected function postItaFrontControllerCostruct() {
        $this->urlServer = cwbParGen::getSessionVar($this->nameForm . "_urlServer");
        $this->pathToDelete = cwbParGen::getSessionVar($this->nameForm . "_pathToDelete");
    }

    public function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            cwbParGen::setSessionVar($this->nameForm . '_urlServer', $this->urlServer);
            cwbParGen::setSessionVar($this->nameForm . '_pathToDelete', $this->pathToDelete);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->init();

                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Annulla':
                        $this->annulla();
                        break;
                    case $this->nameForm . '_Continua':
                        $this->continua();
                        break;
                }
                break;
        }
    }

    private function init() {
        Out::valore($this->nameForm . "_link", ($this->getUrlClient() ? $this->getUrlClient() : $this->getUrlServer()));
    }

    private function annulla() {
        $this->closeDialog();
    }

    // upload automatico
    private function continua() {
        $paramsToReturn = array('URL' => $this->getUrlServer(), 'PATH_TO_DELETE' => $this->pathToDelete);
        $objModel = itaFrontController::getInstance($this->getReturnModel(), $this->getReturnNameForm());
        $objModel->setEvent($this->getReturnEvent());
        $objModel->setFormData($paramsToReturn); // torno indietro sia il documento che gli eventuali parametri arrivati da fuori
        $objModel->parseEvent();
        $this->closeDialog();
    }

    private function closeDialog() {
        $this->close();
        Out::closeDialog($this->nameForm);
    }

    function getUrlServer() {
        return $this->urlServer;
    }

    function setUrlServer($urlServer) {
        $this->urlServer = $urlServer;
    }

    function getPathToDelete() {
        return $this->pathToDelete;
    }

    function setPathToDelete($pathToDelete) {
        $this->pathToDelete = $pathToDelete;
    }

    function getUrlClient() {
        return $this->urlClient;
    }

    function setUrlClient($urlClient) {
        $this->urlClient = $urlClient;
    }


}

