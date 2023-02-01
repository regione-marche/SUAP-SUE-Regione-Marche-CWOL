<?php

/**
 *
 * Classe helper per verifica firma remota
 *
 * PHP Version 5
 *
 * @category
 * @package    RemoteSign
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft sRL
 * @license
 * @version    12.04.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
class rsnVerifier {
    const ITA_VERIFY_FAULT = "ITA-0101";
    const ITA_VERIFY_ERROR = "ITA-0102";

    private $inputFilePath;
    private $outputFilePath;
    private $outputBinary;
    private $inputBinary;
    private $typeOutputBinary;
    private $typeOutputFile;
    private $typeInputBinary;
    private $typeInputFile;
    private $returnCode;
    private $message;
    private $result;

    function __construct() {
        
    }

    public function getInputFilePath() {
        return $this->inputFilePath;
    }

    public function setInputFilePath($inFilePath) {
        $this->inputFilePath = $inFilePath;
        $this->typeInputFile = true;
        $this->inputBinary = null;
        $this->typeInputBinary = false;
    }

    public function getOutputFilePath() {
        return $this->outputFilePath;
    }

    public function setOutputFilePath($outFilePath) {
        $this->outputFilePath = $outFilePath;
        $this->typeOutputFile = true;
        $this->inputBinary = '';
        $this->typeOutputBinary = false;
    }

    public function getTypeOutputBinary() {
        return $this->typeOutputBinary;
    }

    public function setTypeOutputBinary($typeOutputBinary) {
        $this->typeOutputBinary = $typeOutputBinary;
    }

    public function getTypeInputBinary() {
        return $this->typeInputBinary;
    }

    public function setTypeInputBinary($typeInputBinary) {
        $this->typeInputBinary = $typeInputBinary;
    }

    public function getOutputBinary() {
        return $this->outputBinary;
    }

    public function getInputBinary() {
        return $this->inputBinary;
    }

    public function setInputBinary($inputBinary) {
        $this->inputBinary = $inputBinary;
        $this->typeOutputBinary = true;
        $this->inputFilePath = null;
        $this->typeInputFile = false;
    }

    public function getReturnCode() {
        return $this->returnCode;
    }

    public function setReturnCode($returnCode) {
        $this->returnCode = $returnCode;
    }

    public function getMessage() {
        return $this->message;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function getResult() {
        return $this->result;
    }

    public function setResult($result) {
        $this->result = $result;
    }

    public function verify() {
        require_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSClient.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSVerifyRequest.class.php');
        require_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSParam.class.php');

//        $eqAudit = new eqAudit();
//        $eqAudit->logEqEvent($this, array(
//            'DB' => '',
//            'DSet' => '',
//            'Operazione' => '99',
//            'Estremi' => "Verifica Firma Remota verify.",
//            'Key' => "VERIFICA_FIRMA_REMOTA_RICHIESTA"
//        ));


        $VerifyRequest = new itaARSSVerifyRequest();
        $basePath = itaLib::getAppsTempPath('rsnVerifier');

        $VerifyRequest->setTransport('BYNARYNET');
        if ($this->typeInputFile) {
            if (!file_exists($this->getInputFilePath())) {
                $this->setReturnCode('IT_0103');
                $this->setMessage("File Da Verificare non disponibile");
                return false;
            }
            $VerifyRequest->loadBinaryinput($this->getInputFilePath());
        } else if ($this->typeInputBinary) {
            $VerifyRequest->setBinaryinput($this->getInputBinary());
        }


        $VerifyRequest->setDstName('');
        $VerifyRequest->setDstName('');
        $VerifyRequest->setType('PKCS7');
        $VerifyRequest->setVerdate('');
        $VerifyRequest->setNotifymail('');
        $VerifyRequest->setNotify_id('');

        $ARSSClient = new itaARSSClient(new itaARSSParam());
        $ret = $ARSSClient->ws_verify($VerifyRequest);
        if (!$ret) {
            if ($ARSSClient->getFault()) {
                $this->setReturnCode('IT_0101');
                $this->setMessage($ARSSClient->getFault());
            } elseif ($ARSSClient->getError()) {
                $this->setReturnCode('IT_0102');
                $this->setMessage($ARSSClient->getError());
            }
//            $eqAudit->logEqEvent($this, array(
//                'DB' => '',
//                'DSet' => '',
//                'Operazione' => '99',
//                'Estremi' => "Verifica Firma Remota: Errore->" . $this->getReturnCode() . " " . $this->getMessage(),
//                'Key' => "VERIFICA_FIRMA_REMOTA_ERRORE"
//            ));

            return false;
        }

       $this->setResult($ARSSClient->getResult());
        if ($this->result['status'] == 'KO') {
            $this->setReturnCode($this->result['return_code']);
            $this->setMessage($this->result['description']);

//            $eqAudit->logEqEvent($this, array(
//                'DB' => '',
//                'DSet' => '',
//                'Operazione' => '99',
//                'Estremi' => "Verifica Firma Remota: Non Avvenuta->" . $this->getReturnCode() . " " . $this->getMessage(),
//                'Key' => "VERIFICA_FIRMA_REMOTA_KO"
//            ));
            return false;
        }
        if ($this->typeOutputBinary) {
            $this->setTypeOutputBinary($this->result['binaryoutput']);
            $eqAudit->logEqEvent($this, array(
                'DB' => '',
                'DSet' => '',
                'Operazione' => '99',
                'Estremi' => "Verifica Firma Remota Riuscita.",
                'Key' => "VERIFICA_FIRMA_REMOTA_OK"
            ));
            return true;
        } elseif ($this->typeOutputFile) {
            $ret = $this->saveBinary($this->outputFilePath, base64_decode($this->result['binaryoutput']));
            if (!$ret) {
//                $eqAudit->logEqEvent($this, array(
//                    'DB' => '',
//                    'DSet' => '',
//                    'Operazione' => '99',
//                    'Estremi' => "Verifica Firma Remota: Errore->" . $this->getReturnCode() . " " . $this->getMessage(),
//                    'Key' => "VERFICA_FIRMA_REMOTA_ERRORE"
//                ));
            } else {
//                $eqAudit->logEqEvent($this, array(
//                    'DB' => '',
//                    'DSet' => '',
//                    'Operazione' => '99',
//                    'Estremi' => "Verifica Firma Remota: Riuscita.",
//                    'Key' => "VERIFICA_FIRMA_REMOTA_OK"
//                ));
                return true;
            }
        } else {
//            $eqAudit->logEqEvent($this, array(
//                'DB' => '',
//                'DSet' => '',
//                'Operazione' => '99',
//                'Estremi' => "Verifica Firma Remota: Riuscita.",
//                'Key' => "FIRMA_REMOTA_OK"
//            ));
            return true;
        }
    }

    private function saveBinary($path, $binary) {
        $fp = fopen($path, 'w');
        if (!$fp) {
            $this->setReturnCode('IT_0004');
            $this->setMessage('Salvataggio file verificato non riuscito.');
            return false;
        }
        if (!fwrite($fp, $binary, strlen($binary))) {
            $this->setReturnCode('IT_0004');
            $this->setMessage('Salvataggio file verificato non riuscito.');
            return false;
        }
        if (!fclose($fp)) {
            $this->setReturnCode('IT_0004');
            $this->setMessage('Salvataggio file verificato non riuscito.');
            return false;
        }
        return true;
    }

}

?>
