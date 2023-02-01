<?php

require_once ITA_LIB_PATH . '/itaPHPSmartAgent/SmartAgent.class.php';
require_once(ITA_LIB_PATH . '/itaPHPCore/itaSignatureFactory.class.php');
require_once(ITA_LIB_PATH . '/itaPHPSignature/itaPHPSignatureProvider.php');
require_once(ITA_LIB_PATH . '/itaPHPSignature/itaPHPSignatureProviderImpl.php');
require_once(ITA_LIB_PATH . '/itaPHPSignature/itaPHPSignatureProviderHelper.php');

/**
 * Implementazione dei metodi della firma remota tramite Pknet dispositivo usb
 * @author l.pergolini
 */
class itaPHPSignatureProviderPKNet extends itaPHPSignatureProviderImpl implements itaPHPSignatureProvider {

    private $smartAgent;
    private $smartagent_signMode;
    private $smartagent_encoding;
    private $smartagent_filter_valid_cred;
    private $smartagent_multiple;

    public function __construct() {
        $this->smartAgent = new SmartAgent();
    }

    /**
     * Effettua la firma del documento pdf 
     * @param mixed $sourceDocument Documento da firmare può essere un file o n file base64 del file oppure o path (LICENZA SPECIFICA PER FIRMA MULTIPLA) 
     * @param type $returnData
     * @param int $paramsIn["signMode"] 0=Attached(Default), 
     *                                  1=Detached
     * @param int $paramsIn['encoding']    0 = Output envelope is BASE64 encoded, 
     *                              1 = Output envelope is binary encoded, 
     *                              2 = Output document is PDF (for Sign)
     * @param int $paramsIn['filterValidCred'] 0=Consente di selezionare il certificato per firma;  
     *                              1 = Filtra solo i certificati validi (se solo uno valido, non mostra la finestra per selezione)
     * @param int $paramsIn['multiple'] Firma più docuemnti
     */
    public function signature($sourceDocument, $returnData, $paramsIn) {
        $this->setError(0, '');

        if ($this->smartAgent->isEnabled()) {
            if ($this->signaturePrecondition($sourceDocument)) {
                $esito = $this->setPersonalSetting($sourceDocument, $paramsIn["signMode"], $paramsIn['encoding'], $paramsIn['filterValidCred'], $paramsIn['multiple']);

                if ($esito) {
                    if (is_array($sourceDocument)) {
                        $this->smartagent_multiple = 1;
                    } else {
                        $this->smartagent_multiple = 0;
                    }
                    $source = itaPHPSignatureProviderHelper::getSourceSign($sourceDocument);
                }

                $paramsOut["signMode"] = $this->smartagent_signMode;
                $paramsOut['encoding'] = $this->smartagent_encoding;
                $paramsOut['filterValidCred'] = $this->smartagent_filter_valid_cred;
                $paramsOut['multiple'] = $this->smartagent_multiple;
                $this->smartAgent->smartCardSign($source, $paramsOut, $returnData);
            }
        } else {
            $this->setError(-1, 'Smartagent non configurato');
        }
    }

    public function verifySignature($signedDocument, $sourceDocument, $returnData) {
        $this->setError(0, '');

        if ($this->smartAgent->isEnabled()) {
            if ($this->verifySignaturePrecondition($signedDocument, $sourceDocument)) {
                //documento p7m obbligatorio 
                if (is_file($signedDocument)) {
                    $filecontent = file_get_contents($signedDocument);
                    $signed = base64_encode($filecontent);
                } else {
                    $signed = $signedDocument;
                }
                $this->smartAgent->smartCardVerify($signed, $data, $returnData);
            }
        } else {
            $this->setError(-1, 'Smartagent non configurato');
        }
    }

    public function signersInfo($signedDocument, $sourceDocument, $returnData) {
        $this->setError(0, '');
        if ($this->smartAgent->isEnabled()) {
            if ($this->verifySignerInfoPrecondition($signedDocument, $sourceDocument)) {
                if (is_file($signedDocument)) {
                    $filecontent = file_get_contents($signedDocument);
                    $signed = base64_encode($filecontent);
                } else {
                    $signed = $signedDocument;
                }
                if (is_file($sourceDocument)) {
                    $filecontent = file_get_contents($sourceDocument);
                    $data = base64_encode($filecontent);
                } else {
                    $data = $sourceDocument;
                }
                $this->smartAgent->smartCardSignersInfo($signed, $data, $returnData);
            }
        } else {
            $this->setError(-1, 'Smartagent non configurato');
        }
    }

    public function setParameters($parameters = array()) {
        $this->smartagent_signMode = $parameters['signMode'];
        $this->smartagent_encoding = $parameters['encoding'];
        $this->smartagent_filter_valid_cred = $parameters['filterValidCred'];
        $this->smartagent_multiple = $parameters['multiple'];
    }

    public function getParameters() {
        return array(
            'type' => itaSignatureFactory::CLASSE_FIRMA_PROVIDER_PKNET,
            'signMode' => $this->smartagent_signMode,
            'encoding' => $this->smartagent_encoding,
            'filterValidCred' => $this->smartagent_filter_valid_cred,
            'multiple' => $this->smartagent_multiple
        );
    }

    public function setParametersFromJsonString($json) {
        try {
            $parameters = json_decode($json, true);
            if (is_array($parameters)) {
                $this->setParameters($parameters);
            } else {
                return false;
            }
            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    // metodo privati
    private function setPersonalSetting($sourceDocument, $paramSignMode = NULL, $paramEncoding = NULL, $paramFilterValidCred = NULL) {

        $result = itaPHPSignatureProviderHelper::isValidSourceDocument($sourceDocument);
        if ($result['status']) {
            if ($paramSignMode !== null && ($paramSignMode == 1 || $paramSignMode == 0)) {
                $this->smartagent_signMode = $paramSignMode;
            }
            if ($paramEncoding !== null && ($paramEncoding == 0 || $paramEncoding == 1 || $paramEncoding == 2)) {
                $this->smartagent_encoding = $paramEncoding;
            }
            if ($paramFilterValidCred !== null && ($paramFilterValidCred == 1 || $paramFilterValidCred == 0)) {
                $this->smartagent_filter_valid_cred = $paramFilterValidCred;
            }
        } else {
            $this->setError(-2, $result['message']);
        }
        return $result['status'];
    }

    private function signaturePrecondition($sourceDocument) {
        $esito = true;
        //documento p7m obbligatorio    
        if ($sourceDocument) {
            $resultDocument = itaPHPSignatureProviderHelper::isValidSourceDocument($sourceDocument);
            if ($resultDocument['status'] == FALSE) {
                $esito = false;
                $this->setError(-2, $resultDocument['message']);
            }
        } else {
            $esito = false;
            $this->setError(-2, "Obbligatorio il file\files da firmare");
        }
        return $esito;
    }

    private function verifySignaturePrecondition($signedDocument, $sourceDocument) {
        $esito = true;
        //documento p7m obbligatorio 
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

    private function verifySignerInfoPrecondition($signedDocument, $sourceDocument) {
        $esito = true;
        //documento firmato obbligatorio  
        if ($signedDocument) {
            $resultSignedDocument = itaPHPSignatureProviderHelper::isValidSourceDocument($signedDocument);
            if ($resultSignedDocument['status'] == FALSE) {
                $esito = false;
                $this->setError(-2, $resultSignedDocument['message']);
            }
        } else {
            $esito = false;
            $this->setError(-2, "Obbligatorio il file firmato p7m");
        }
        $resultSourceDocument = itaPHPSignatureProviderHelper::isValidSourceDocument($sourceDocument);
        if ($resultSourceDocument['status'] == FALSE) {
            $esito = false;
            $this->setError(-2, $resultSourceDocument['message']);
        }
        return $esito;
    }

    function isMultipleSignatureAllowed() {
        return $this->smartagent_multiple;
    }

}

?>