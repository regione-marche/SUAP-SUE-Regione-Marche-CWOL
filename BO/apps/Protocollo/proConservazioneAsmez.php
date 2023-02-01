<?php

/**
 *
 * TEST NPCE-CLIENT per WS Poste Italiane
 *
 * PHP Version 5
 *
 * @category
 * @package    Interni
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    18.04.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPAncitel/itaAncitelClient.class.php');
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once(ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php');

function proConservazioneAsmez() {
    $proConservazioneAsmez = new proConservazioneAsmez();
    $proConservazioneAsmez->parseEvent();
    return;
}

class proConservazioneAsmez extends itaModel {

    public $nameForm = "proConservazioneAsmez";

    function __construct() {
        parent::__construct();
    }

    function __destruct() {

        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::show($this->nameForm);
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_callVisuraTargaTelaio':
                        $this->ConservazioneFile();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_StreamDocumentoPrincipale');
        App::$utente->removeKey($this->nameForm . '_StreamDocumentoAllegato');
        App::$utente->removeKey($this->nameForm . '_StreamDocumentoPrincipaleU');
        App::$utente->removeKey($this->nameForm . '_StreamDocumentoAllegatoU');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function ConservazioneFile() {

        $restClient = new itaRestClient();
        $restClient->setTimeout(10);
        $restClient->setCurlopt_url('https://conservazione.asmenet.it/api/v1.0/pdv');
        $restClient->setDebugLevel(true);

        /*
         * Chiamata al servizio
         */

//        $username = '04428241';
//        $password = '';
        $username = '09301795';
        $password = '';
      //  Out::msgInfo("", base64_encode("$username:$password"));
//        $restClientResult = $restClient->postMultipart('/pdv', array(
//            'FIELDS' => array(),
//            'FILES' => array(
//                'file' => 'C:\Works\dati\allegato.zip'
//            )
//                ), array(
//            "Authorization: Basic " . base64_encode("$username:$password")
//        ));

        $restClientResult = $restClient->post(
                '', array(), array(
            "Authorization: Basic " . base64_encode("$username:$password"),
            'Accept-Language: it-it',
            'Accept-Encoding: gzip, deflate'
                ),file_get_contents('C:\Works\dati\test_consrvazione_asme\allegato_18\allegato_18.zip'),'application/zip'
        );

        if (!$restClientResult) {
            // errore
            Out::msgStop('Errore1', print_r($restClient->getDebug(), true));
            return false;
        }

        if ($restClient->getHttpStatus() !== 200) {
            // errore
            Out::msgStop('Errore2', print_r($restClient->getDebug(), true));
            Out::msgStop('Errore3', $restClient->getHttpStatus());
            return false;
        }

        Out::msgInfo('Errore4', print_r($restClient->getDebug(), true));
        $result = $restClient->getResult();
        Out::msgInfo('Result', print_r($result, true));
    }

}
