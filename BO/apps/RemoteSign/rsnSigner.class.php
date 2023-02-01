<?php

/**
 *
 * Classe helper per firma remota
 *
 * PHP Version 5
 *
 * @category
 * @package    RemoteSign
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft sRL
 * @license
 * @version    25.09.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
class rsnSigner {

    const ITA_SIGNPKCS7_FAULT = "ITA-0001";
    const ITA_SIGNPKCS7_ERROR = "ITA-0002";
    const ITA_OPENSESSION_FAULT = "ITA-0003";
    const ITA_OPENSESSION_ERROR = "ITA-0004";
    const ITA_CLOSESESSION_FAULT = "ITA-0005";
    const ITA_CLOSESESSION_ERROR = "ITA-0006";
    const ITA_RETRIVECREDENTIAL_ERROR = "ITA-0007";
    const TYPE_SIGN_CADES = 'CAdES';
    const TYPE_SIGN_PADES = 'PAdES';
    const TYPE_SIGN_XADES = 'XAdES';

    public static $OPENSESSION_KO_MESSAGE = array(
        'KO-0001' => 'Errore Generico',
        'KO-0002' => '',
        'KO-0003' => 'Errore in fase di verifica delle credenziali',
        'KO-0004' => 'Errore nel PIN',
    );
    public static $TYPES_OPT_AUTH = array(
        'firma',
        'frLispa',
        'frRegioneMarche'
    );
    public static $TYPES_SIGN = array(
        self::TYPE_SIGN_CADES => 'Firma Digitale remota in standard CAdES (p7m)',
        self::TYPE_SIGN_PADES => 'Firma Digitale remota in standard PAdES (pdf)',
        self::TYPE_SIGN_XADES => 'Firma Digitale remota in standard XAdES (xml)'
    );
    private $signerLogo;
    private $inputFilePath;
    private $outputFilePath;
    private $outputBinary;
    private $inputBinary;
    private $multiSignFilePaths;
    private $multiSignBins;
    private $typeOtpAuth;
    private $otpPwd;
    private $user;
    private $password;
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
        $this->typeInputBinary = true;
        $this->inputFilePath = null;
        $this->typeInputFile = false;
    }

    function getTypeOtpAuth() {
        return $this->typeOtpAuth;
    }

    function setTypeOtpAuth($typeOtpAuth) {
        $this->typeOtpAuth = $typeOtpAuth;
    }

    public function getOtpPwd() {
        return $this->otpPwd;
    }

    public function setOtpPwd($otpPwd) {
        $this->otpPwd = $otpPwd;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
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

    public function getMultiSignFilePaths() {
        return $this->multiSignFilePaths;
    }

    public function setMultiSignFilePaths($multiSignFilePaths) {
        $this->multiSignFilePaths = $multiSignFilePaths;
        $this->typeInputFile = true;
        $this->multiSignBins = null;
        $this->typeInputBinary = false;
    }

    public function getMultiSignBins() {
        return $this->multiSignBins;
    }

    public function setMultiSignBins($multiSignBins) {
        $this->multiSignBins = $multiSignBins;
        $this->typeOutputBinary = true;
        $this->multiSignFilePaths = null;
        $this->typeInputFile = false;
    }

    public function getSignerLogo() {
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSLogo.class.php');
        return itaARSSLogo::getLogo();
    }

    public function setSignerLogo($signerLogo) {
        $this->signerLogo = $signerLogo;
    }

    public function multiSignPkcs7($addSignOnP7m = true, $Sessionid = '') {
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSClient.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSSignRequestV2.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSIdentity.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSParam.class.php');

        $eqAudit = new eqAudit();
        $eqAudit->logEqEvent($this, array(
            'DB' => '',
            'DSet' => '',
            'Operazione' => '99',
            'Estremi' => "Sessione Firma Remota Multipla signPkcs7: Richiesta da Utente->" . $this->getUser(),
            'Key' => "SESSIONE_FIRMA_MULTIPLA_REMOTA_RICHIESTA"
        ));
        if (!$Sessionid) {
            $Sessionid = $this->openSession();
            if (!$Sessionid) {
                return false;
            }
        }
        $conErrori = false;
        if ($this->typeInputFile) {
            foreach ($this->multiSignFilePaths as $key => $signInOutPair) {
                $this->setInputFilePath($signInOutPair['inputFilePath']);
                $this->setOutputFilePath($signInOutPair['outputFilePath']);
                if ($addSignOnP7m && strtolower((pathinfo($this->getInputFilePath(), PATHINFO_EXTENSION))) == 'p7m') {
                    $ret = $this->addPkcs7sign($Sessionid);
                } else {
                    $ret = $this->signPkcs7($Sessionid);
                }
                if (!$ret) {
                    $this->multiSignFilePaths[$key]['signResult'] = "KO";
                    $this->multiSignFilePaths[$key]['signMessage'] = $this->getMessage();
                    $conErrori = true;
                } else {
                    $this->multiSignFilePaths[$key]['signResult'] = "OK";
                    $this->multiSignFilePaths[$key]['signMessage'] = "File firmato correttamente.";
                }
            }
        } else {
            foreach ($this->multiSignBins as $key => $signInOutPair) {
                $this->setInputBinary($signInOutPair['inputBinary']);
                $ret = $this->signPkcs7($Sessionid);
                if (!$ret) {
                    $this->multiSignBins[$key]['signResult'] = "KO";
                    $this->multiSignBins[$key]['signMessage'] = $this->getMessage();
                    $conErrori = true;
                } else {
                    $this->multiSignFilePaths[$key]['signResult'] = "OK";
                    $this->multiSignFilePaths[$key]['signMessage'] = "File firmato correttamente.";
                }
            }
        }

        $retClose = $this->closeSession($Sessionid);
        if (!$Sessionid) {
            return false;
        }

        if (!$conErrori) {
            return true;
        } else {
            return false;
        }
    }

    public function multiSignXades() {
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSClient.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSSignRequestV2.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSIdentity.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSParam.class.php');

        $eqAudit = new eqAudit();
        $eqAudit->logEqEvent($this, array(
            'DB' => '',
            'DSet' => '',
            'Operazione' => '99',
            'Estremi' => "Sessione Firma Remota Multipla xades: Richiesta da Utente->" . $this->getUser(),
            'Key' => "SESSIONE_FIRMA_MULTIPLA_REMOTA_RICHIESTA"
        ));


        $Sessionid = $this->openSession();
        if (!$Sessionid) {
            return false;
        }

        $conErrori = false;
        if ($this->typeInputFile) {
            foreach ($this->multiSignFilePaths as $key => $signInOutPair) {
                $this->setInputFilePath($signInOutPair['inputFilePath']);
                $this->setOutputFilePath($signInOutPair['outputFilePath']);
                $ret = $this->signXades($Sessionid);

                if (!$ret) {
                    $this->multiSignFilePaths[$key]['signResult'] = "KO";
                    $this->multiSignFilePaths[$key]['signMessage'] = $this->getMessage();
                    $conErrori = true;
                } else {
                    $this->multiSignFilePaths[$key]['signResult'] = "OK";
                    $this->multiSignFilePaths[$key]['signMessage'] = "File firmato correttamente.";
                }
            }
        } else {
            foreach ($this->multiSignBins as $key => $signInOutPair) {
                $this->setInputBinary($signInOutPair['inputBinary']);
                $ret = $this->signXades($Sessionid);
                if (!$ret) {
                    $this->multiSignBins[$key]['signResult'] = "KO";
                    $this->multiSignBins[$key]['signMessage'] = $this->getMessage();
                    $conErrori = true;
                } else {
                    $this->multiSignFilePaths[$key]['signResult'] = "OK";
                    $this->multiSignFilePaths[$key]['signMessage'] = "File firmato correttamente.";
                }
            }
        }


        $retClose = $this->closeSession($Sessionid);
        if (!$retClose) {
            return false;
        }

        if (!$conErrori) {
            return true;
        } else {
            return false;
        }
    }

    public function getPaperTokenCoords() {
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSClient.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSIdentity.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSParam.class.php');

        $eqAudit = new eqAudit();
        $eqAudit->logEqEvent($this, array(
            'DB' => '',
            'DSet' => '',
            'Operazione' => '99',
            'Estremi' => "Lettura coordinate paper token ->" . $this->getUser(),
            'Key' => "LETTURA_COORDINATE_PAPERTOKEN"
        ));
        $Identity = new itaARSSIdentity();
        $Identity->setTypeHSM('COSIGN');
        $Identity->setTypeOtpAuth(($this->typeOtpAuth) ? $this->typeOtpAuth : self::$TYPES_OPT_AUTH['0']);
        if ($this->otpPwd) {
            $Identity->setOtpPwd($this->otpPwd);
        }
        $Identity->setUser($this->user);
        $Identity->setUserPWD($this->password);

        $ARSSClient = new itaARSSClient(new itaARSSParam());
        $ret = $ARSSClient->ws_retriveCredential($Identity, 'PAPERTOKEN');
        if (!$ret) {
            if ($ARSSClient->getFault()) {
                $this->setReturnCode('IT_0001');
                $this->setMessage($ARSSClient->getFault());
            } elseif ($ARSSClient->getError()) {
                $this->setReturnCode('IT_0002');
                $this->setMessage($ARSSClient->getError());
            }
            $eqAudit->logEqEvent($this, array(
                'DB' => '',
                'DSet' => '',
                'Operazione' => '99',
                'Estremi' => "Lettura coordinate paper token: Errore->" . $this->getReturnCode() . " " . $this->getMessage(),
                'Key' => "LETTURA_COORDINATE_PAPERTOKEN_ERRORE"
            ));
            $this->setReturnCode(self::ITA_RETRIVECREDENTIAL_ERROR);
            $this->setMessage($ARSSClient->getError());
            return false;
        }
        $arrResult = $ARSSClient->getResult();
        if ($arrResult['status'] == 'KO') {
            $this->setReturnCode(self::ITA_RETRIVECREDENTIAL_ERROR);
            $this->setMessage($arrResult['description']);
            return false;
        }
        $arssParam = new itaARSSParam();
        $url = $arssParam->getPaperTokenBackend();
        $url = str_replace("%CODE%", $arrResult['textvalue'], $url);
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'GET'
            //,'content' => http_build_query($data),
            ),
        );
        $context = stream_context_create($options);
        if (!$context) {
            $this->setReturnCode(self::ITA_RETRIVECREDENTIAL_ERROR);
            $this->setMessage("Connessione per recupero coordinate papertoken fallita.");
            return false;
        }
        return file_get_contents($url, false, $context);
    }

    public function addPkcs7sign($Sessionid = '') {
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSClient.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSSignRequestV2.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSIdentity.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSParam.class.php');

        $eqAudit = new eqAudit();
        $eqAudit->logEqEvent($this, array(
            'DB' => '',
            'DSet' => '',
            'Operazione' => '99',
            'Estremi' => "Aggiunta Firma Remota addPkcs7sign: Richiesta da Utente->" . $this->getUser(),
            'Key' => "AGGIUNTA_FIRMA_REMOTA_RICHIESTA"
        ));
        $Identity = new itaARSSIdentity();
        $Identity->setTypeHSM('COSIGN');
        $Identity->setTypeOtpAuth(($this->typeOtpAuth) ? $this->typeOtpAuth : self::$TYPES_OPT_AUTH['0']);
        if ($this->otpPwd) {
            $Identity->setOtpPwd($this->otpPwd);
        }
        $Identity->setUser($this->user);
        $Identity->setUserPWD($this->password);
        $SignRequestV2 = new itaARSSSignRequestV2();
        $basePath = itaLib::getAppsTempPath('rsnSigner');

        if ($this->typeInputFile) {
            if (!file_exists($this->getInputFilePath())) {
                $this->setReturnCode('IT_0003');
                $this->setMessage("File Da Firmare non disponibile");
                return false;
            }
            $SignRequestV2->loadBinaryinput($this->getInputFilePath());
        } else if ($this->typeInputBinary) {
            $SignRequestV2->setBinaryinput($this->getInputBinary());
        }
        $SignRequestV2->setCertID('AS0');
        $SignRequestV2->setDstName('');
        $SignRequestV2->setIdentity($Identity);
        if ($Sessionid != '') {
            $SignRequestV2->setSession_id($Sessionid);
        }
        $SignRequestV2->setTransport('BYNARYNET');

        $ARSSClient = new itaARSSClient(new itaARSSParam());
        $ret = $ARSSClient->ws_addpkcs7sign($SignRequestV2);
        if (!$ret) {
            if ($ARSSClient->getFault()) {
                $this->setReturnCode('IT_0001');
                $this->setMessage($ARSSClient->getFault());
            } elseif ($ARSSClient->getError()) {
                $this->setReturnCode('IT_0002');
                $this->setMessage($ARSSClient->getError());
            }
            $eqAudit->logEqEvent($this, array(
                'DB' => '',
                'DSet' => '',
                'Operazione' => '99',
                'Estremi' => "Aggiunta Firma Remota addPkcs7sign: Errore->" . $this->getReturnCode() . " " . $this->getMessage(),
                'Key' => "AGGIUNTA_FIRMA_REMOTA_ERRORE"
            ));

            return false;
        }

        $this->setResult($ARSSClient->getResult());
        if ($this->result['status'] == 'KO') {
            $this->setReturnCode($this->result['return_code']);
            $this->setMessage($this->result['description']);

            $eqAudit->logEqEvent($this, array(
                'DB' => '',
                'DSet' => '',
                'Operazione' => '99',
                'Estremi' => "Aggiunta Firma Remota addPkcs7sign: Non Avvenuta->" . $this->getReturnCode() . " " . $this->getMessage(),
                'Key' => "AGGIUNTA_FIRMA_REMOTA_KO"
            ));
            return false;
        }
        if ($this->typeOutputBinary) {
            $this->setTypeOutputBinary($this->result['binaryoutput']);
            $eqAudit->logEqEvent($this, array(
                'DB' => '',
                'DSet' => '',
                'Operazione' => '99',
                'Estremi' => "Aggiunta Firma Remota addPkcs7sign: Riuscita. Restituito Binario.",
                'Key' => "AGGIUNTA_FIRMA_REMOTA_OK"
            ));
            return true;
        } elseif ($this->typeOutputFile) {
            $ret = $this->saveBinary($this->outputFilePath, base64_decode($this->result['binaryoutput']));
            if (!$ret) {
                $eqAudit->logEqEvent($this, array(
                    'DB' => '',
                    'DSet' => '',
                    'Operazione' => '99',
                    'Estremi' => "Aggiunta Firma Remota addPkcs7sign: Errore->" . $this->getReturnCode() . " " . $this->getMessage(),
                    'Key' => "AGGIUNTA_FIRMA_REMOTA_ERRORE"
                ));
            } else {
                $eqAudit->logEqEvent($this, array(
                    'DB' => '',
                    'DSet' => '',
                    'Operazione' => '99',
                    'Estremi' => "Firma Remota addPkcs7sign: Riuscita. Salvato File.",
                    'Key' => "AGGIUNTA_FIRMA_REMOTA_OK"
                ));
                return true;
            }
        } else {
            $eqAudit->logEqEvent($this, array(
                'DB' => '',
                'DSet' => '',
                'Operazione' => '99',
                'Estremi' => "Aggiunta Firma Remota addPkcs7sign: Riuscita.",
                'Key' => "AGGIUNTA_FIRMA_REMOTA_OK"
            ));
            return true;
        }
    }

    public function signXades($Sessionid = '') {
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSClient.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSSignRequestV2.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSSXmlSignatureParameter.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSSTrasforms.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSIdentity.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSParam.class.php');
        $eqAudit = new eqAudit();
        $eqAudit->logEqEvent($this, array(
            'DB' => '',
            'DSet' => '',
            'Operazione' => '99',
            'Estremi' => "Firma Remota signPkcs7: Richiesta da Utente->" . $this->getUser(),
            'Key' => "FIRMA_REMOTA_RICHIESTA"
        ));

        $Identity = new itaARSSIdentity();
        $Identity->setTypeHSM('COSIGN');
        $Identity->setTypeOtpAuth(($this->typeOtpAuth) ? $this->typeOtpAuth : self::$TYPES_OPT_AUTH['0']);
        if ($this->otpPwd) {
            $Identity->setOtpPwd($this->otpPwd);
        }
        $Identity->setUser($this->user);
        $Identity->setUserPWD($this->password);
        $SignRequestV2 = new itaARSSSignRequestV2();
        $basePath = itaLib::getAppsTempPath('rsnSigner');

        if ($this->typeInputFile) {
            if (!file_exists($this->getInputFilePath())) {
                $this->setReturnCode('IT_0003');
                $this->setMessage("File Da Firmare non disponibile");
                return false;
            }
            $SignRequestV2->loadBinaryinput($this->getInputFilePath());
        } else if ($this->typeInputBinary) {
            $SignRequestV2->setBinaryinput($this->getInputBinary());
        }
        $SignRequestV2->setCertID('AS0');
        $SignRequestV2->setDstName('');
        $SignRequestV2->setIdentity($Identity);
        if ($Sessionid != '') {
            $SignRequestV2->setSession_id($Sessionid);
        }
        $SignRequestV2->setTransport('BYNARYNET');


        $Trasforms = new itaARSSTrasforms();
        $Trasforms->setType(itaARSSTrasforms::TYPE_CANONICAL_OMIT_COMMENT);
        $Trasforms->setValue('');

        $XmlSignParameter = new itaARSSXmlSignatureParameter();
        $XmlSignParameter->setType(itaARSSXmlSignatureParameter::TYPE_XMLENVELOPED);
        $XmlSignParameter->setTransforms($Trasforms);
        $XmlSignParameter->setCanonicalizedType(itaARSSXmlSignatureParameter::CANONICALIZEDTYPE_ALGO_ID_C14N11_OMIT_COMMENTS);

        $ARSSClient = new itaARSSClient(new itaARSSParam());
        $ret = $ARSSClient->ws_xmlSignature($SignRequestV2, $XmlSignParameter);

        if (!$ret) {
            if ($ARSSClient->getFault()) {
                $this->setReturnCode('IT_0001');
                $this->setMessage($ARSSClient->getFault());
            } elseif ($ARSSClient->getError()) {
                $this->setReturnCode('IT_0002');
                $this->setMessage($ARSSClient->getError());
            }
            $eqAudit->logEqEvent($this, array(
                'DB' => '',
                'DSet' => '',
                'Operazione' => '99',
                'Estremi' => "Firma Remota signPkcs7: Errore->" . $this->getReturnCode() . " " . $this->getMessage(),
                'Key' => "FIRMA_REMOTA_ERRORE"
            ));

            return false;
        }

        $this->setResult($ARSSClient->getResult());
        if ($this->result['status'] == 'KO') {
            $this->setReturnCode($this->result['return_code']);
            $this->setMessage($this->result['description']);

            $eqAudit->logEqEvent($this, array(
                'DB' => '',
                'DSet' => '',
                'Operazione' => '99',
                'Estremi' => "Firma Remota xmlsignature: Non Avvenuta->" . $this->getReturnCode() . " " . $this->getMessage(),
                'Key' => "FIRMA_REMOTA_KO"
            ));
            return false;
        }
        if ($this->typeOutputBinary) {
            $this->setTypeOutputBinary($this->result['binaryoutput']);
            $eqAudit->logEqEvent($this, array(
                'DB' => '',
                'DSet' => '',
                'Operazione' => '99',
                'Estremi' => "Firma Remota xmlsignature: Riuscita.",
                'Key' => "FIRMA_REMOTA_OK"
            ));
            return true;
        } elseif ($this->typeOutputFile) {
            $ret = $this->saveBinary($this->outputFilePath, base64_decode($this->result['binaryoutput']));
            if (!$ret) {
                $eqAudit->logEqEvent($this, array(
                    'DB' => '',
                    'DSet' => '',
                    'Operazione' => '99',
                    'Estremi' => "Firma Remota xmlsignature: Errore->" . $this->getReturnCode() . " " . $this->getMessage(),
                    'Key' => "FIRMA_REMOTA_ERRORE"
                ));
            } else {
                $eqAudit->logEqEvent($this, array(
                    'DB' => '',
                    'DSet' => '',
                    'Operazione' => '99',
                    'Estremi' => "Firma Remota xmlsignature: Riuscita.",
                    'Key' => "FIRMA_REMOTA_OK"
                ));
                return true;
            }
        } else {
            $eqAudit->logEqEvent($this, array(
                'DB' => '',
                'DSet' => '',
                'Operazione' => '99',
                'Estremi' => "Firma Remota xmlsignature: Riuscita.",
                'Key' => "FIRMA_REMOTA_OK"
            ));
            return true;
        }
    }

    public function signPkcs7($Sessionid = '') {
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSClient.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSSignRequestV2.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSIdentity.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSParam.class.php');

        $eqAudit = new eqAudit();
        $eqAudit->logEqEvent($this, array(
            'DB' => '',
            'DSet' => '',
            'Operazione' => '99',
            'Estremi' => "Firma Remota signPkcs7: Richiesta da Utente->" . $this->getUser(),
            'Key' => "FIRMA_REMOTA_RICHIESTA"
        ));


        $Identity = new itaARSSIdentity();
        $Identity->setTypeHSM('COSIGN');
        $Identity->setTypeOtpAuth(($this->typeOtpAuth) ? $this->typeOtpAuth : self::$TYPES_OPT_AUTH['0']);
        if ($this->otpPwd) {
            $Identity->setOtpPwd($this->otpPwd);
        }
        $Identity->setUser($this->user);
        $Identity->setUserPWD($this->password);
        $SignRequestV2 = new itaARSSSignRequestV2();
        $basePath = itaLib::getAppsTempPath('rsnSigner');

        if ($this->typeInputFile) {
            if (!file_exists($this->getInputFilePath())) {
                $this->setReturnCode('IT_0003');
                $this->setMessage("File Da Firmare non disponibile");
                return false;
            }
            $SignRequestV2->loadBinaryinput($this->getInputFilePath());
        } else if ($this->typeInputBinary) {
            $SignRequestV2->setBinaryinput(base64_encode($this->getInputBinary()));
        }
        $SignRequestV2->setCertID('AS0');
        $SignRequestV2->setDstName('');
        $SignRequestV2->setIdentity($Identity);
        if ($Sessionid != '') {
            $SignRequestV2->setSession_id($Sessionid);
        }
        $SignRequestV2->setTransport('BYNARYNET');

        $ARSSClient = new itaARSSClient(new itaARSSParam());
        $ret = $ARSSClient->ws_pkcs7signV2($SignRequestV2, false);
        if (!$ret) {
            if ($ARSSClient->getFault()) {
                $this->setReturnCode('IT_0001');
                $this->setMessage($ARSSClient->getFault());
            } elseif ($ARSSClient->getError()) {
                $this->setReturnCode('IT_0002');
                $this->setMessage($ARSSClient->getError());
            }
            $eqAudit->logEqEvent($this, array(
                'DB' => '',
                'DSet' => '',
                'Operazione' => '99',
                'Estremi' => "Firma Remota signPkcs7: Errore->" . $this->getReturnCode() . " " . $this->getMessage(),
                'Key' => "FIRMA_REMOTA_ERRORE"
            ));

            return false;
        }

        $this->setResult($ARSSClient->getResult());
        if ($this->result['status'] == 'KO') {
            $this->setReturnCode($this->result['return_code']);
            $this->setMessage($this->result['description']);

            $eqAudit->logEqEvent($this, array(
                'DB' => '',
                'DSet' => '',
                'Operazione' => '99',
                'Estremi' => "Firma Remota signPkcs7: Non Avvenuta->" . $this->getReturnCode() . " " . $this->getMessage(),
                'Key' => "FIRMA_REMOTA_KO"
            ));
            return false;
        }
        if ($this->typeOutputBinary) {
            $this->outputBinary = base64_decode($this->result['binaryoutput']);
            $eqAudit->logEqEvent($this, array(
                'DB' => '',
                'DSet' => '',
                'Operazione' => '99',
                'Estremi' => "Firma Remota signPkcs7: Riuscita.",
                'Key' => "FIRMA_REMOTA_OK"
            ));
            return true;
        } elseif ($this->typeOutputFile) {
            $ret = $this->saveBinary($this->outputFilePath, base64_decode($this->result['binaryoutput']));
            if (!$ret) {
                $eqAudit->logEqEvent($this, array(
                    'DB' => '',
                    'DSet' => '',
                    'Operazione' => '99',
                    'Estremi' => "Firma Remota signPkcs7: Errore->" . $this->getReturnCode() . " " . $this->getMessage(),
                    'Key' => "FIRMA_REMOTA_ERRORE"
                ));
            } else {
                $eqAudit->logEqEvent($this, array(
                    'DB' => '',
                    'DSet' => '',
                    'Operazione' => '99',
                    'Estremi' => "Firma Remota signPkcs7: Riuscita.",
                    'Key' => "FIRMA_REMOTA_OK"
                ));
                return true;
            }
        } else {
            $eqAudit->logEqEvent($this, array(
                'DB' => '',
                'DSet' => '',
                'Operazione' => '99',
                'Estremi' => "Firma Remota signPkcs7: Riuscita.",
                'Key' => "FIRMA_REMOTA_OK"
            ));
            return true;
        }
    }

    public function openSession() {
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSClient.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSIdentity.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSParam.class.php');

        $eqAudit = new eqAudit();
        $eqAudit->logEqEvent($this, array(
            'DB' => '',
            'DSet' => '',
            'Operazione' => '99',
            'Estremi' => "Richesta token di apertura sessione: Richiesta da Utente->" . $this->getUser(),
            'Key' => "TOKEN_APERTURA_SESSIONE_RICHIESTA"
        ));

        $Identity = new itaARSSIdentity();
        $Identity->setTypeHSM('COSIGN');
        $Identity->setTypeOtpAuth(($this->typeOtpAuth) ? $this->typeOtpAuth : 'firma' );
        if ($this->otpPwd) {
            $Identity->setOtpPwd($this->otpPwd);
        }
        $Identity->setUser($this->user);
        $Identity->setUserPWD($this->password);

        $ARSSClient = new itaARSSClient(new itaARSSParam());
        $ret = $ARSSClient->ws_opensession($Identity);
        if (!$ret) {
            if ($ARSSClient->getFault()) {
                $this->setReturnCode(self::ITA_OPENSESSION_FAULT);
                $this->setMessage($ARSSClient->getFault());
            } elseif ($ARSSClient->getError()) {
                $this->setReturnCode(self::ITA_OPENSESSION_ERROR);
                $this->setMessage($ARSSClient->getError());
            }
            $eqAudit->logEqEvent($this, array(
                'DB' => '',
                'DSet' => '',
                'Operazione' => '99',
                'Estremi' => "Richesta token di apertura sessione: Errore->" . $this->getReturnCode() . " " . $this->getMessage(),
                'Key' => "TOKEN_APERTURA_SESSIONE_RICHIESTA_ERRORE"
            ));
            return false;
        }

        $Sessionid = $ARSSClient->getResult();
        switch ($Sessionid) {
            case 'KO-0001':
            case 'KO-0002':
            case 'KO-0003':
            case 'KO-0004':
                $this->setReturnCode($Sessionid . '-OPENSESSION');
                $this->setMessage(self::$OPENSESSION_KO_MESSAGE[$Sessionid]);
                $eqAudit->logEqEvent($this, array(
                    'DB' => '',
                    'DSet' => '',
                    'Operazione' => '99',
                    'Estremi' => "ichesta token di apertura sessione: Non Avvenuta->" . $this->getReturnCode() . " " . $this->getMessage(),
                    'Key' => "TOKEN_APERTURA_SESSIONE_RICHIESTA_KO"
                ));
                return false;
            default:
                break;
        }
        return $Sessionid;
    }

    public function closeSession($Sessionid) {
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSClient.class.php');
        include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSIdentity.class.php');

        $eqAudit = new eqAudit();
        $eqAudit->logEqEvent($this, array(
            'DB' => '',
            'DSet' => '',
            'Operazione' => '99',
            'Estremi' => "Richesta di chiusura sessione: Richiesta da Utente->" . $this->getUser(),
            'Key' => "CHIUSURA_SESSIONE_RICHIESTA"
        ));


        $Identity = new itaARSSIdentity();
        $Identity->setTypeHSM('COSIGN');
        $Identity->setTypeOtpAuth(($this->typeOtpAuth) ? $this->typeOtpAuth : 'firma' );
        if ($this->otpPwd) {
            $Identity->setOtpPwd($this->otpPwd);
        }
        $Identity->setUser($this->user);
        $Identity->setUserPWD($this->password);

        $ARSSClient = new itaARSSClient(new itaARSSParam());
        $ret = $ARSSClient->ws_closesession($Identity, $Sessionid);
        if (!$ret) {
            if ($ARSSClient->getFault()) {
                $this->setReturnCode(self::ITA_CLOSESESSION_FAULT);
                $this->setMessage($ARSSClient->getFault());
            } elseif ($ARSSClient->getError()) {
                $this->setReturnCode(self::ITA_CLOSESESSION_ERROR);
                $this->setMessage($ARSSClient->getError());
            }
            $eqAudit->logEqEvent($this, array(
                'DB' => '',
                'DSet' => '',
                'Operazione' => '99',
                'Estremi' => "Chiusura Sessione: Errore->" . $this->getReturnCode() . " " . $this->getMessage(),
                'Key' => "CHIUSURA_SESSIONE_ERRORE"
            ));
            return false;
        }

        $resultClose = $ARSSClient->getResult();
        if ($resultClose != 'OK') {
            $this->setReturnCode($resultClose . '-CLOSESESSION');
            $this->setMessage(self::$OPENSESSION_KO_MESSAGE[$Sessionid]);
            $eqAudit->logEqEvent($this, array(
                'DB' => '',
                'DSet' => '',
                'Operazione' => '99',
                'Estremi' => "Chiusura Sessione: Non Avvenuta->" . $this->getReturnCode() . " " . $this->getMessage(),
                'Key' => "CHIUSURA_SESSIONE_KO"
            ));

            return false;
        }
        return true;
    }

    private function saveBinary($path, $binary) {
//        Out::msgInfo("",$path);
        $fp = fopen($path, 'w');
        if (!$fp) {
            $this->setReturnCode('IT_0004');
            $this->setMessage('Salvataggio file firmato non riuscito.');
            return false;
        }
        if (!fwrite($fp, $binary, strlen($binary))) {
            $this->setReturnCode('IT_0004');
            $this->setMessage('Salvataggio file firmato non riuscito.');
            return false;
        }
        if (!fclose($fp)) {
            $this->setReturnCode('IT_0004');
            $this->setMessage('Salvataggio file firmato non riuscito.');
            return false;
        }
        return true;
    }

}
