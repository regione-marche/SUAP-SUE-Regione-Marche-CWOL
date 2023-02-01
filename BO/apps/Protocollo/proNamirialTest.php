<?php

/* * 
 *
 * GESTIONE PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    29.08.2019
 * @link
 * @see
 * @since
 * */

include_once ITA_LIB_PATH . '/itaPHPNamirial/itaSDocRepositoryClient.class.php';
include_once ITA_LIB_PATH . '/itaPHPNamirial/itaSDocTransferClient.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proConservazioneManagerFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibConservazione.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

function proNamirialTest() {
    $proNamirialTest = new proNamirialTest();
    $proNamirialTest->parseEvent();
    return;
}

class proNamirialTest extends itaModel {

    public $nameForm = "proNamirialTest";
    private $UserName = "IT02143010367_UserPdV";
    private $Password = "3BD8ABC5FAAB668E6AEE01636093E541";
    public $proLib;
    public $proLibConservazione;

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->proLibConservazione = new proLibConservazione();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($this->event) {
            case 'openform':
                Out::valore($this->nameForm . '_UserName', $this->UserName);
                Out::valore($this->nameForm . '_Password', $this->Password);
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_GetRepositories':
                        /* @var $soapClient itaNamirial */
                        $soapClient = $this->getclient();
                        $soapClient->ws_getRepositories();
                        $this->showResult($soapClient);
                        break;

                    case $this->nameForm . '_GetRepositoryInfo':
                        /* @var $soapClient itaNamirial */
                        $soapClient = $this->getclient();
                        $soapClient->ws_getRepositoryInfo(array('repositoryId' => $this->formData[$this->nameForm . '_RepositoryId']));
                        $this->showResult($soapClient);
                        break;

                    case $this->nameForm . '_GetReservedExtensions':
                        /* @var $soapClient itaNamirial */
                        $soapClient = $this->getclient();
                        $soapClient->ws_getReservedExtensions();
                        $this->showResult($soapClient);
                        break;

                    case $this->nameForm . '_CreateDocument':

                        $this->CreateDocument();
                        break;
                        //TEST 1:
                        $stream = base64_encode(file_get_contents(ITA_BASE_PATH . '/apps/Protocollo/examples/pdv.zip'));
                        $param = array(
                            'repositoryId' => '1',
                            'filename' => 'pdv.zip',
                            'length' => strlen(file_get_contents(ITA_BASE_PATH . '/apps/Protocollo/examples/pdv.zip')),
                            'mimeType' => '',
                            'stream' => $stream
                        );

                        //* @var $soapClient itaNamirial */
                        $soapClient = $this->getclient();
                        $soapClient->ws_createDocument($param);
                        $this->showResult($soapClient);
                        break;


                    case $this->nameForm . '_InitializeUploadFileTemp':
                        /* @var $soapClient itaNamirial */
                        $param = array('fileName' => 'packagePdV.zip');
                        //$param = array('fileName' => 'test12345678packagePdV.zip');
                        $soapClient = $this->getclientTransfer();
                        $soapClient->ws_initializeUploadFileTemp($param);
                        $this->showResult($soapClient);
                        break;


                    case $this->nameForm . '_UploadFileTemp':
                        /* @var $soapClient itaNamirial */

                        $stream = base64_encode(file_get_contents(ITA_BASE_PATH . '/apps/Protocollo/examples/pdvRegistroTest.zip'));
                        $cid = md5(uniqid(time()));
                        $additionalSoapHeaders = array(
                            'FileId' => $this->formData[$this->nameForm . '_FileId'],
                            'FileName' => 'packagePdV.zip',
                            'Length' => strlen(file_get_contents(ITA_BASE_PATH . '/apps/Protocollo/examples/pdvRegistroTest.zip'))
                        );
                        $param = array(
                            'FileByteStream' => $stream
                        );

                        $attachments = null;
                        $soapClient = $this->getclientTransfer();
                        $soapClient->ws_uploadFileTemp($param, $additionalSoapHeaders, $attachments);
                        $this->showResult($soapClient);
                        break;

                    case $this->nameForm . '_UploadFileTempXop':
                        /* @var $soapClient itaNamirial */

                        $stream = base64_encode(file_get_contents(ITA_BASE_PATH . '/apps/Protocollo/examples/pdv2.zip'));
                        $cid = md5(uniqid(time()));
                        $additionalSoapHeaders = array(
                            'FileId' => $this->formData[$this->nameForm . '_FileId'],
                            'FileName' => 'packagePdV.zip',
                            'Length' => strlen(file_get_contents(ITA_BASE_PATH . '/apps/Protocollo/examples/pdv2.zip'))
                        );
                        $param = array(
                            //'FileByteStream' => "cid:" .$cid
                            'FileByteStream' => $stream
                        );

                        $attachments = array(
                            array(
                                'data' => null,
                                'filename' => ITA_BASE_PATH . '/apps/Protocollo/examples/pdv2.zip',
                                'contenttype' => 'application/octet-stream',
                                'cid' => $cid
                            )
                        );


                        $soapClient = $this->getclientTransfer();
                        $soapClient->ws_uploadFileTemp($param, $additionalSoapHeaders, $attachments);
                        $this->showResult($soapClient);
                        break;

                    case $this->nameForm . '_RequestCreateDocumentFile':
                        /* @var $soapClient itaNamirial */
                        $soapClient = $this->getclient();
                        $soapClient->ws_requestCreateDocumentFile(array('fileId' => $this->formData[$this->nameForm . '_FileIdRequest']));
                        $this->showResult($soapClient);
                        break;

                    case $this->nameForm . '_GetPendingRequest':
                        /* @var $soapClient itaNamirial */
                        $soapClient = $this->getclient();
                        $soapClient->ws_getPendingRequest(array('idRequest' => $this->formData[$this->nameForm . '_IdRequest']));
                        $this->showResult($soapClient);
                        break;

                    case $this->nameForm . '_ExportRdV':
                        /* @var $soapClient itaNamirial */
//                        $param = array('idRdV' => $this->formData[$this->nameForm . '_IdRdV']);
//                        $soapClient = $this->getclient();
//                        $soapClient->ws_exportRdV($param);
//                        $fileCont = $soapClient->getResult();
//                        file_put_contents('C:/Works/ExportRdvResult.zip', base64_decode($fileCont));
//                        $this->showResult($soapClient);
                        $Elenco= $this->proLibConservazione->ControllaRDVProconserAuto();
                        Out::msginfo('elenco',print_r($Elenco,true));
                        break;

                    case $this->nameForm . '_GetPdV':
                        /* @var $soapClient itaNamirial */
                        $param = array('idPdV' => $this->formData[$this->nameForm . '_IdPdV']);
                        $soapClient = $this->getclient();
                        $soapClient->ws_getPdV($param);
                        $this->showResult($soapClient);
                        break;


                    case $this->nameForm . '_CreatePdD':
                        /* @var $soapClient itaNamirial */
                        $paramutente = array(
                            'sdc:CodiceFiscale' => '',
                            'sdc:Cognome' => ''
                        );
                        $elencoIdPdV = $this->formData[$this->nameForm . '_IdPdV'];
                        $param = array(
                            'idSetup' => '02143010367',
                            'idAzienda' => '5768',
                            'userRichiedente' => $paramutente,
                            'elencoIdPdV' => $elencoIdPdV
                        );
                        $soapClient = $this->getclient();
                        $soapClient->ws_createPdD($param);
                        $this->showResult($soapClient);
                        break;
                }
        }
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

    private function getclient() {
        $soapClient = new SDocRepository();
        $soapClient->setWebservices_uri('https://testsdc2csolution.solutiondocondemand.com/Basic/RepositoryService.svc');
        $soapClient->setUsername($this->UserName);
        $soapClient->setPassword($this->Password);
        $soapClient->setTimeout(10);
        $soapClient->setDebugLevel(false);
        return $soapClient;
    }

    private function getclientTransfer() {
        $soapClient = new SDocTransfer();
        $soapClient->setWebservices_uri('https://testsdc2csolution.solutiondocondemand.com/Basic/TransferService.svc');
        $soapClient->setUsername($this->UserName);
        $soapClient->setPassword($this->Password);
        $soapClient->setTimeout(10);
        $soapClient->setDebugLevel(false);
        return $soapClient;
    }

    private function showResult($soapClient) {
        Out::msgInfo("Result....", print_r($soapClient->getResult(), true));
        if ($soapClient->getError()) {
            Out::msgStop("Errore....", print_r($soapClient->getError(), true));
        }
        if ($soapClient->getFault()) {
            Out::msgStop("Fault....", print_r($soapClient->getFault(), true));
        }
    }

    private function CreateDocument() {


        $Anapro_rec = $this->proLib->GetAnapro('2018000009', 'codice', 'C');

        /*
         * Istanzio il Manager
         */
        $ObjManager = proConservazioneManagerFactory::getManager();
        if (!$ObjManager) {
            Out::msgStop('Attenzione', 'Errore in istanza Manager.');
            return;
        }
        /*
         * Lettura unità documentaria
         */
        $UnitaDoc = $this->proLibConservazione->GetUnitaDocumentaria($Anapro_rec);

        /*
         * Setto Chiavi Anapro
         */
        $ObjManager->setRowidAnapro($Anapro_rec['ROWID']);
        $ObjManager->setAnapro_rec($Anapro_rec);
        $ObjManager->setUnitaDocumentaria($UnitaDoc);
        /*
         *  Lancio la conservazione
         */
        if (!$ObjManager->conservaAnapro()) {
            Out::msgStop("Attenzione", $ObjManager->getErrMessage());
        } else {
            Out::msgInfo('Versamento in Conservazione', 'Esito Versamento:' . $ObjManager->getRetEsito());
        }


        $stream = base64_encode(file_get_contents('C:/Works/pdv.zip'));
        $param = array(
            'repositoryId' => '1',
            'filename' => 'pdv.zip',
            'length' => strlen(file_get_contents('C:/Works/pdv.zip')),
            'mimeType' => '',
            'stream' => $stream
        );

        //* @var $soapClient itaNamirial */
        $soapClient = $this->getclient();
        $soapClient->ws_createDocument($param);
        $this->showResult($soapClient);
    }

}
