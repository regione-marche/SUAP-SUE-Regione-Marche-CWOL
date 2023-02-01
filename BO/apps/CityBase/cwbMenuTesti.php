<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaShellExec.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPSmartAgent/SmartAgent.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibMotoreTesti.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbDownloadUpload.php';

function cwbMenuTesti() {
    $cwbMenuTesti = new cwbMenuTesti();
    $cwbMenuTesti->parseEvent();
    return;
}

class cwbMenuTesti extends itaFrontControllerCW {

    private $testoRisolto;
    private $params;
    private $documentoCaricato;
    private $abilitazionePulsanti;

    protected function postItaFrontControllerCostruct() {
        parent::postItaFrontControllerCostruct();
        $this->testoRisolto = App::$utente->getKey($this->nameForm . "_testoRisolto");
        $this->params = App::$utente->getKey($this->nameForm . "_params");
        $this->abilitazionePulsanti = App::$utente->getKey($this->nameForm . "_abilitazionePulsanti");
        $this->documentoCaricato = array();
    }

    public function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_testoRisolto', $this->testoRisolto);
            App::$utente->setKey($this->nameForm . '_params', $this->params);
            App::$utente->setKey($this->nameForm . '_abilitazionePulsanti', $this->abilitazionePulsanti);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->initForm();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Download':
                        $this->download();
                        break;
                    case $this->nameForm . '_Upload':
                        $this->upload();
                        break;
                    case $this->nameForm . '_Preview':
                        $this->preview();
                        break;
                }
                break;
            case 'onReturnUpload':
                $this->onUploadDocument($_POST['file'], $_POST['uploadedFile']);
                break;
            case 'onReturnCwbDownloadUpload':
                $this->onUploadDocument(uniqid("", true), $this->formData['URL'], $this->formData['PATH_TO_DELETE']);
                break;
            case 'onDownloadCallback':
                // Apre documento utilizzando lo SmartAgent
                $path = urlencode($_POST['data']);
                itaShellExec::shellExec($path, '');
                break;
        }
    }

    private function initForm() {
        // Gestione abilitazione pulsanti
        Out::show($this->nameForm . '_Download');
        Out::show($this->nameForm . '_Upload');
        Out::show($this->nameForm . '_Preview');
        if ($this->abilitazionePulsanti) {
            $abilitazioneDownload = $this->abilitazionePulsanti['download'];
            if ($abilitazioneDownload === false) {
                Out::hide($this->nameForm . '_Download');
            }
            $abilitazioneUpload = $this->abilitazionePulsanti['upload'];
            if ($abilitazioneUpload === false) {
                Out::hide($this->nameForm . '_Upload');
            }
            $abilitazionePreview = $this->abilitazionePulsanti['preview'];
            if ($abilitazionePreview === false) {
                Out::hide($this->nameForm . '_Preview');
            }
        }

        // Titolo
        Out::html($this->nameForm . '_divMessaggio', '
            <center>
                <span style="font-style:italic;">Seleziona operazione:</span>                
            </center>');

        // Attiva componente upload
        Out::activateUploader($this->nameForm . "_Upload_upld_uploader");
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    private function download() {
        if (Config::getPath('general.cwPeople_usaShared') == 1 && Config::getPath('general.cwPeople_shared')) {
            // download/upload automatico con cartella di rete. salva testo su cartella temporanea
            $baseFileName = App::$utente->getKey('TOKEN') . "-" . time() . '.rtf';

            $url = Config::getPath('general.cwPeople_shared');
            $urlPublic = Config::getPath('general.cwPeople_sharedPublic');
            if (!$urlPublic) {
                $urlPublic = $url;
            }

//            $file = file_get_contents($this->testoRisolto[0]['NOME']);
//            if($file && file_put_contents($url . $baseFileName, $file)){
            if (substr(trim($url), strlen(trim($url)) - 1, strlen(trim($url))) != DIRECTORY_SEPARATOR) {
                $barra = DIRECTORY_SEPARATOR;
            }
            if (copy($this->testoRisolto[0]['NOME'], $url . $barra . $baseFileName)) {
                //unlink($this->testoRisolto[0]['NOME']);
                $smartAgent = new SmartAgent();
                if ($smartAgent->isEnabled()) {
                    $urlSm = utiDownload::getOTR($baseFileName, $url);
                    $smartAgent->downloadFile($baseFileName, $urlSm, $this->nameForm, 'download', 'onDownloadCallback');
                }

                $objmodel = cwbLib::apriFinestra('cwbDownloadUpload', $this->nameForm, 'onReturnCwbDownloadUpload', $_POST['id'], array(), $this->nameFormOrig);
                $objmodel->setUrlClient($urlPublic . $baseFileName);
                $objmodel->setUrlServer($url . $baseFileName);
                $objmodel->setPathToDelete($this->testoRisolto[0]['NOME']); // lo passo come parametro per poi cancellarlo dopo aver fatto l'upload, sennò se si fa annulla da cwbDownloadUpload e poi si rifà download, ho perso il documento
                $objmodel->parseEvent();
            } else {
                Out::msgStop("Errore", "Errore download documento ");
            }
        } else {
            // download/upload manuali
            $corpo = file_get_contents($this->testoRisolto[0]['NOME']);
            cwbLib::downloadDocument(uniqid("", true) . '.rtf', $corpo, true);
        }
    }

    private function upload() {
        if (Config::getPath('general.cwPeople_usaShared') != 1) {
            $model = 'utiUploadDiag';
            itaLib::openForm($model);
            /* @var $utiUploadDiag itaModel */
            $utiUploadDiag = itaModel::getInstance($model, $model);
            $utiUploadDiag->setEvent('openform');
            $utiUploadDiag->setReturnModel($this->nameFormOrig);
            $utiUploadDiag->setReturnEvent('onReturnUpload');
            $utiUploadDiag->parseEvent();
        }
    }

    private function onUploadDocument($fileName, $path, $oldPathToDelete) {
        unlink($oldPathToDelete); // lo cancello qui per essere sicuro che sia finito il giro
        $this->documentoCaricato = array();

        $this->documentoCaricato = array(
            'filename' => $fileName,
            'fullpath' => $path
        );

        // Restituisce i dati del documento caricato al chiamante  
        $toReturn = ($this->params && is_array($this->params)) ? array_merge($this->documentoCaricato, $this->params) : $this->documentoCaricato;
        $toReturn['NAMEFORM_MENUTESTI'] = $this->nameForm;
        $objModel = itaFrontController::getInstance($this->getReturnModel(), $this->getReturnNameForm());
        $objModel->setEvent($this->getReturnEvent());
        $objModel->setFormData($toReturn); // torno indietro sia il documento che gli eventuali parametri arrivati da fuori
        $objModel->parseEvent();
        $this->close();
    }

    private function preview() {
        if (!$this->getTestoRisolto()) {
            Out::msgStop("Errore", "Errore risoluzione testo rtf ");
        }
        $cwbLibMotoreTesti = new cwbLibMotoreTesti();
        $binary = file_get_contents($this->testoRisolto[0]['NOME']);
        if (!$binary) {
            Out::msgStop("Errore", "Errore salvataggio testo rtf su temp ");
        }
        $pdf = $cwbLibMotoreTesti->rtfToPdf($binary);

        if (!$pdf) {
            Out::msgStop("Errore", "Errore conversione da rtf a pdf ");
        }

        //Verifico che esistano le sottocartelle
        $pathDest = App::getPath('temporary.appsPath') . '/' . 'APR';
        if (!file_exists($pathDest)) {
            mkdir($pathDest, 0777, true);
        }
        $pathDest = App::getPath('temporary.appsPath') . '/' . 'APR' . '/attachments/';
        if (!file_exists($pathDest)) {
            mkdir($pathDest, 0777, true);
        }
        $fileName = array_pop(explode('\\', $this->testoRisolto[0]['NOME']));
        $fileName = str_replace('.rtf', '.pdf', basename($fileName));
        $pathDest = App::getPath('temporary.appsPath') . '/' . 'APR' . '/attachments/' . $fileName;

        file_put_contents($pathDest, $pdf);

        // Apre il pdf nell'editor interno
        cwbLib::apriVisualizzatoreDocumentiDialog(array(0 => array('NOME' => $pathDest)));

        // Chiude finestra
        //     $this->close();
    }

    public function getParams() {
        return $this->params;
    }

    public function setParams($params) {
        $this->params = $params;
    }

    public function getDocumentoCaricato() {
        return $this->documentoCaricato;
    }

    public function getAbilitazionePulsanti() {
        return $this->abilitazionePulsanti;
    }

    public function setAbilitazionePulsanti($abilitazionePulsanti) {
        $this->abilitazionePulsanti = $abilitazionePulsanti;
    }

    function getTestoRisolto() {
        return $this->testoRisolto;
    }

    function setTestoRisolto($testoRisolto) {
        $this->testoRisolto = $testoRisolto;
    }

}

?>