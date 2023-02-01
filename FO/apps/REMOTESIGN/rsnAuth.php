<?php

require_once ITA_RSN_PATH . '/rsnLib.class.php';
require_once ITA_RSN_PATH . '/rsnSigner.class.php';
require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLib.class.php';

class rsnAuth extends itaModelFO {

    public $rsnLib;
    public $praLib;
    public $rsnErr;
    private $errCode;
    private $errMessage;
    private $signMethod = "CAdES";
    private $allegati = array();

    public function __construct() {
        parent::__construct();

        try {
            /*
             * Caricamento librerie.
             */
            $this->rsnLib = new rsnLib();
            $this->praLib = new praLib();
            $this->rsnErr = new frontOfficeErr();
            $this->allegati = $_SESSION['rsnAuthAllegati'] ?: $this->allegati;
        } catch (Exception $e) {
            
        }
    }

    public function __destruct() {
        $_SESSION['rsnAuthAllegati'] = $this->allegati;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function getAllegati() {
        return $this->allegati;
    }

    public function setAllegati($allegati) {
        $this->allegati = $allegati;
    }

    public function parseEvent() {
        switch ($this->request['event']) {
            case 'onClick':
                switch ($this->request['id']) {
                    case 'papertoken':
                        output::ajaxResponseValues(array(
                            'papertoken' => $this->returnPapertokenCoords($this->request['otpauth'], $this->request['otppass'], $this->request['utente'], $this->request['password'])
                        ));

                        output::ajaxSendResponse();
                        break;
                }

                break;
        }
    }

    public function disegnaFormFirma() {
        $html = new html;

        $html->appendHtml('<div style="padding: 20px;">');

        $html->addForm('', 'POST', array(
            'class' => 'italsoft-form--fixed'
        ), true);

//        $html->addHidden('event', 'firma');
//        $html->addInput('select', 'Tipo di firma', array(
//            'name' => 'signMethod',
//            'value' => $this->request['signMethod']
//            ), array(
//            'CAdES' => 'Firma digitale remota in standard CAdES (p7m)'
//        ));
//
//        $html->addBr();

        $arrayData = array(
            'header' => array('Documento da firmare', 'Dimensione'),
            'body' => array()
        );

        foreach ($this->allegati as $allegato) {
            $arrayData['body'][] = array($allegato['FILEORIG'], frontOfficeLib::formatFileSize(filesize($allegato['INPUTFILEPATH'])));
        }

        $html->addTable($arrayData);

        $html->addBr();

        $html->addInput('select', 'Dominio autenticazione', array(
            'name' => 'otpauth',
            'value' => $this->request['otpauth']
            ), array(
            'firma' => 'firma',
            'frLispa' => 'frLispa',
            'frRegioneMarche' => 'frRegioneMarche'
        ));

        $html->addBr();

        $html->addInput('text', 'Nome utente', array(
            'name' => 'utente',
            'value' => $this->request['utente']
        ));

        $html->addBr();

        $html->addInput('password', 'Password', array(
            'name' => 'password',
            'value' => $this->request['password']
        ));

        $html->addBr();

        require_once ITA_LIB_PATH . '/itaPHPARSS/itaARSSParam.class.php';
        $arssParam = new itaARSSParam();
        if ($arssParam->getPaperTokenBackend()) {
            $html->addInput('text', 'Paper token', array(
                'name' => 'papertoken',
                'value' => $this->request['papertoken'],
                'readonly' => true
            ));

            $html->addButton('<i class="ionic ion-refresh italsoft-icon"></i>', '#', '', array('id' => 'papertoken'));

            $html->addBr();
        }

        $html->addInput('text', 'OTP (One Time Pass)', array(
            'name' => 'otppass',
            'value' => $this->request['otppass']
        ));

        $html->addBr();

        $html->addSubmit('Firma');

        $html->closeTag('form');

        $html->appendHtml('</div>');

        return $html->getHtml();
    }

    public function returnPapertokenCoords($otpauth, $otppwd, $utente, $password = '') {
        $Signer = new rsnSigner();
        $Signer->setTypeOtpAuth($otpauth);
        $Signer->setOtpPwd($otppwd);
        $Signer->setUser($utente);
        $Signer->setPassword($password);
        $retWs = $Signer->getPaperTokenCoords();

        if ($retWs) {
            $extendedCoords = implode(" - ", str_split(trim($retWs), 2));
            return $extendedCoords;
        } else {
            return '';
        }
    }

    public function firma($otpType, $otpPwd, $user, $password) {
        if (!$otpType) {
            $this->errCode = -1;
            $this->errMessage = "Tipologia OTP non valida";
            return false;
        }

        if (!$otpPwd) {
            $this->errCode = -1;
            $this->errMessage = "OTP non valida";
            return false;
        }

        if (!$user) {
            $this->errCode = -1;
            $this->errMessage = "Utente non valido";
            return false;
        }

        if (!$password) {
            $this->errCode = -1;
            $this->errMessage = "Password non valida";
            return false;
        }

        if (!count($this->allegati)) {
            $this->errCode = -1;
            $this->errMessage = "Nessun allegato da firmare";
            return false;
        }

        /*
         * Istanza signer con i dati di identità
         */
        $Signer = new rsnSigner();
        $Signer->setTypeOtpAuth($otpType);
        $Signer->setOtpPwd($otpPwd);
        $Signer->setUser($user);
        $Signer->setPassword($password);

        /*
         * Metodo di firma
         */
        //$this->signMethod = "CAdES";
        if ($this->signMethod == rsnSigner::TYPE_SIGN_PADES) {
            $this->errCode = -1;
            $this->errMessage = "Metodo di firma in fase di implementazione";
            return false;
        }

        if (count($this->allegati) == 1 && $this->returnMultiFile == false) {
            return $this->signSingle($Signer);
        } else {
            return $this->signMulti($Signer);
        }
    }

    private function signSingle($Signer) {
        $this->setSignedFilesExtension();

        /*
         * Carico i Parametri per la firma singola
         */
        $Signer->setInputFilePath($this->allegati[0]['INPUTFILEPATH']);
        $Signer->setOutputFilePath($this->allegati[0]['OUTPUTFILEPATH']);

        /*
         * Lancio la corretta procedura di firma
         */
        switch ($this->signMethod) {
            case rsnSigner::TYPE_SIGN_CADES:
                if (strtolower((pathinfo($Signer->getInputFilePath(), PATHINFO_EXTENSION))) == 'p7m') {
                    $ret = $Signer->addPkcs7sign();
                } else {
                    $ret = $Signer->signPkcs7();
                }
                break;
            case rsnSigner::TYPE_SIGN_PADES:
                break;
            case rsnSigner::TYPE_SIGN_XADES:
                $ret = $Signer->signXades();
                break;
        }

        /*
         * Parse del risultato
         */
        if ($ret === false) {
            $this->errCode = $Signer->getReturnCode();
            $this->errMessage = $Signer->getMessage();
            return false;
        }

        return true;
    }

    private function setSignedFilesExtension() {
        foreach ($this->allegati as $key => $allegato) {
            $this->allegati[$key]['FILEFIRMATO'] = $allegato['FILEORIG'];
            $this->allegati[$key]['OUTPUTFILENAME'] = $allegato['FILEORIG'];
            $this->allegati[$key]['OUTPUTFILEPATH'] = $allegato['INPUTFILEPATH'];
            switch ($this->signMethod) {
                case rsnSigner::TYPE_SIGN_CADES:
                    if (strtolower((pathinfo($allegato['INPUTFILEPATH'], PATHINFO_EXTENSION))) !== 'p7m') {
                        $this->allegati[$key]['FILEFIRMATO'] = $allegato['FILEORIG'] . ".p7m";
                        $this->allegati[$key]['OUTPUTFILENAME'] = $allegato['FILEORIG'] . ".p7m";
                        $this->allegati[$key]['OUTPUTFILEPATH'] = $allegato['INPUTFILEPATH'] . ".p7m";
                    }
                    break;
                case rsnSigner::TYPE_SIGN_PADES:
                    break;
                case rsnSigner::TYPE_SIGN_XADES:
                    break;
            }
        }
    }

    private function signMulti($Signer) {

        $this->setSignedFilesExtension();

        /**
         * Carico i parametri del multiSign
         * 
         */
        $multiSignFilePaths = array();
        foreach ($this->allegati as $key => $allegato) {
            if ($allegato['SIGNRESULT'] != "OK") {
                $multiSignFilePaths[$key] = array(
                    'inputFilePath' => $allegato['INPUTFILEPATH'],
                    'outputFilePath' => $allegato['OUTPUTFILEPATH']
                );
            }
        }
        $Signer->setMultiSignFilePaths($multiSignFilePaths);


        /**
         * Lancio la corretta procedura di firma
         * 
         */
        switch ($this->signMethod) {
            case rsnSigner::TYPE_SIGN_CADES:
                $ret = $Signer->multiSignPkcs7();
                BREAK;
            case rsnSigner::TYPE_SIGN_PADES:
                break;
            case rsnSigner::TYPE_SIGN_XADES:
                $ret = $Signer->multiSignXades();
                break;
        }

        /**
         * Parse del risultato
         */
        if ($ret == true) {
            foreach ($Signer->getMultiSignFilePaths() as $key => $value) {
                $this->allegati[$key]['SIGNRESULT'] = $value['signResult'];
                $this->allegati[$key]['SIGNMESSAGE'] = $value['signMessage'];
            }
            if (!$this->returnMultiFile) {
//                $this->CaricaGriglia($this->gridAllegati, $this->elaboraArrayAllegati());
//                Out::hide($this->nameForm . '_signMethod_field');
//                Out::hide($this->nameForm . '_divCredenziali');
//                Out::hide($this->gridAllegati . '_addGridRow');
//                Out::hide($this->gridAllegati . '_delGridRow');
//                $topMsg = "Documenti Firmati.... scarica i files con l'apposita icona";
//                $html = "<div>";
//                $html .= "<div style=\"display:inline-block;\"><img width=40px style=\"margin:2px;\" src=\"" . $Signer->getSignerLogo() . "\"></img></div>";
//                $html .= "<div style=\"display:inline-block;vertical-align:middle;padding-left:5px;\"><div style=\"font-size:1.3em;color:darkgreen;\">$topMsg</div><br><br></div>";
//                $html .= "</div>";
//                Out::html($this->nameForm . "_topMsg", $html);
//                Out::msgInfo("Firma Remota", "Firme avvenute con successo puoi scaricare i files firmati");
                print_r("Firme avvenute con successo puoi scaricare i files firmati");
            } else {
                //this->returnToParent(true);
            }
        } else {
            //Out::msgInfo("Firma remota... Fallita!", $Signer->getReturnCode() . "-" . $Signer->getMessage());
            print_r("Firma remota... Fallita! --> " . $Signer->getReturnCode() . "-" . $Signer->getMessage());
        }
    }

}
