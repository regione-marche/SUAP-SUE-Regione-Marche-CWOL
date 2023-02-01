<?php

require_once(ITA_LIB_PATH . '/itaPHPSignature/itaPHPSignatureProvider.php');
require_once(ITA_LIB_PATH . '/itaPHPSignature/itaPHPSignatureProviderImpl.php');
require_once(ITA_LIB_PATH . '/itaPHPSignature/itaPHPSignatureProviderHelper.php');
require_once ITA_BASE_PATH . '/apps/RemoteSign/rsnSigner.class.php';
require_once(ITA_LIB_PATH . '/itaPHPVersign/itaP7m.class.php');
require_once(ITA_LIB_PATH . '/itaPHPCore/itaSignatureFactory.class.php');

/**
 * Implementazione dei metodi della firma remota tramite Aruba e dispositivo Otp
 * Wrapper delle liberie Italsoft 
 * @author l.pergolini
 */
class itaPHPSignatureProviderArubaOTP extends itaPHPSignatureProviderImpl implements itaPHPSignatureProvider {

    private $username;
    private $password;
    private $otpPassword;

    //wrappa il metodo 
    public function signature($sourceDocument, $returnData, $paramsIn) {
        $this->setError(0, '');
        if ($this->signaturePrecondition($sourceDocument, $paramsIn)) {
            $signer = new rsnSigner();
            $signer->setUser($paramsIn["USER"]);
            $signer->setPassword($paramsIn["PASSWORD"]);
            $signer->setOtpPwd($paramsIn["OTPPWD"]);

            $source = itaPHPSignatureProviderHelper::getSourceSign($sourceDocument, false);
            // se la firma è  singola 
            if (!is_array($sourceDocument)) {
                $signer->setTypeInputBinary(true);
                $signer->setInputBinary($source);
                //TODO GESTIRE CALLBACK 
                $signer->signPkcs7();
                // $signer->getOutputBinary() prendere il risultato 
            } else {
                //Source è un xml composto da file,name,content
                $arrayXml = itaPHPSignatureProviderHelper::getArrayToXml($source);
                $multiSignFilePaths = array();

                foreach ($arrayXml as $key => $value) {
                    if ($key == "file") {
                        $multiSignFilePaths["inputbinary"] = base64_decode($value[0]["content"][0]['@textNode']);
                    }
                }
                $signer->setMultiSignBins($multiSignFilePaths);
                //TODO GESTIRE CALLBACK 
                $ret = $signer->multiSignPkcs7();

                //prendere la risposta da 
//                $signer->getMultiSignBins();
            }
        }
    }

    public function verifySignature($signedDocument, $sourceDocument, $returnData) {
        $this->setError(0, '');
        if ($this->verifySignaturePrecondition($signedDocument, $sourceDocument)) {
            //se è un percorso valido altrimenti creo un temp file 
            if (!is_file($signedDocument)) {
                $randName = md5(rand() * time());
                $tmpFile = ITalib::getPrivateUploadPath() . "/" . $randName;
                file_put_contents($tmpFile, $signedDocument);
            } else {
                $tmpFile = $signedDocument;
            }
            $verify = itaP7m::getP7mInstance($tmpFile, true);
            $status = $this->verifySignatureResponse($verify);
            if ($status) {
                $_POST['data'] = $verify->getContentFileName(); //imposta il result sulla post[data]
            }

            itaLib::openApp($returnData["returnForm"], '', true, 'desktopBody', '', '', null);
            $modelObj = itaModel::getInstance($returnData["returnForm"], null);
            $modelObj->setReturnId($returnData["returnId"]);
            $modelObj->setEvent($returnData["returnEvent"]);

            $modelObj->parseEvent();
            //callback di ritorno con il nome del file originale del pdf  estratto

            return $status;
        }
    }

    public function signersInfo($signedDocument, $sourceDocument, $returnData) {
        $this->setError(0, '');
        if ($this->verifySignersInfoPrecondition($signedDocument, $sourceDocument)) {
            //se è un percorso valido altrimenti creo un temp file 
            if (!is_file($signedDocument)) {
                $randName = md5(rand() * time());
                $tmpFile = ITalib::getPrivateUploadPath() . "/" . $randName;
                file_put_contents($tmpFile, $signedDocument);
            } else {
                $tmpFile = $signedDocument;
            }
            $verify = itaP7m::getP7mInstance($tmpFile, true);
            $status = $this->verifySignatureResponse($verify);
            if ($status) {
                $result = $verify->getInfoSummary();
            }
            $_POST['data'] = $result; //imposta il result sulla post[data]
            itaLib::openApp($returnData["returnForm"], '', true, 'desktopBody', '', '', null);
            $modelObj = itaModel::getInstance($returnData["returnForm"], null);
            $modelObj->setReturnId($returnData["returnId"]);
            $modelObj->setEvent($returnData["returnEvent"]);

            $modelObj->parseEvent();


            //callback di ritorno con il nome del file originale creato 
            //itaLib::openApp($returnData["returnForm"], '', true, 'desktopBody', '', '', null);
            return $status;
        }
    }

    public function setParameters($parameters = array()) {
        $this->password = $parameters["PASSWORD"];
        $this->username = $parameters["USER"];
        $this->otpPassword = $parameters["OTPPSW"];
    }

    public function getParameters() {
        return array(
            'type' => itaSignatureFactory::CLASSE_FIRMA_PROVIDER_ARUBA,
            'PASSWORD' => $this->password,
            'USER' => $this->username,
            'OTPPSW' => $this->otpPassword
        );
    }

    //metodi privati 
    private function signaturePrecondition($sourceDocument, $params) {
        $esito = true;
        //documento p7m obbligatorio    
        //controllo validita $param 
        if (!$params["USER"]) {
            $esito = false;
            $this->setError(-1, "Obbligatorio utente firmatario");
        }
        if (!$params["PASSWORD"]) {
            $esito = false;
            $this->setError(-2, "Obbligatorio password del firmatario");
        }
        if (!$params["OTPPSW"]) {
            $esito = false;
            $this->setError(-3, "Obbligatorio otp della aperazione");
        }

        if ($sourceDocument) {
            $resultDocument = itaPHPSignatureProviderHelper::isValidSourceDocument($sourceDocument);
            if ($resultDocument['status'] == FALSE) {
                $esito = false;
                $this->setError(-4, $resultDocument['message']);
            }
        } else {
            $esito = false;
            $this->setError(-5, "Obbligatorio il file\files da firmare");
        }
        return $esito;
    }

    private function verifySignaturePrecondition($signedDocument, $sourceDocument) {
        $esito = true;
        $versignEngine = App::getConf("itaVersign.versign_engine");
        if (!$versignEngine) {
//            $esito = false;
//            $this->setError(-2, "Devi scegliere il tipo di verifica tra ARSS,DSS,j4sign");
        }

        if ($signedDocument) {
            $resultDocument = itaPHPSignatureProviderHelper::isValidSourceDocument($signedDocument);
            if ($resultDocument['status'] == FALSE) {
                $esito = false;
                $this->setError(-2, $resultDocument['message']);
            }
        } else {
            $esito = false;
            $this->setError(-2, "Obbligatorio il file firmato p7m");
        }
        if (!$sourceDocument) {
            $resultSourceDocument = itaPHPSignatureProviderHelper::isValidSourceDocument($sourceDocument);
            if ($resultSourceDocument['status'] == FALSE) {
                $esito = false;
                $this->setError(-2, $resultSourceDocument['message']);
            }
        }
        return $esito;
    }

    private function verifySignersInfoPrecondition($signedDocument, $sourceDocument) {
        return self::verifySignaturePrecondition($signedDocument, $sourceDocument);
    }

    private function verifySignatureResponse($verify = null) {
        if (!$verify) {
            $this->setError(-1, "Errore firma non verificata");
            return false;
        }

        if (!file_exists($verify->getContentFileName())) {
            $this->setError(-1, "Errore nella estrazione file dal p7m");
            return false;
        }

        if ($verify->isFileVerifyPassed()) {
            $verify->cleanData();
        } else {
            $this->setError(-2, $verify->getMessageErrorFileAsString());
            $verify->cleanData();
            return false;
        }

        return true;
    }

    public function isMultipleSignatureAllowed() {
        return false;
    }

    function getUsername() {
        return $this->username;
    }

    function getPassword() {
        return $this->password;
    }

    function getOtpPassword() {
        return $this->otpPassword;
    }

    function setUsername($username) {
        $this->username = $username;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function setOtpPassword($otpPassword) {
        $this->otpPassword = $otpPassword;
    }

}
