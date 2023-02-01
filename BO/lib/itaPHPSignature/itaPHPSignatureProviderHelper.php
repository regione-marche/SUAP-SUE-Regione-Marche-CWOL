<?php

/**
 * Helper statico per la gdestione della firma esterna 
 *  */
require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
require_once(ITA_LIB_PATH . '/itaPHPSignature/ItaPHPSignatureSigner.php');

class itaPHPSignatureProviderHelper {

    const POST_SIZE_LIMIT = 20; //impostazione per la dimensione della post 
    CONST POST_ACCETPT_TOLLERANCE = 2; //impostazione per la dimensione della post (BASE64 + firma\e) 

    /**
     * 
     * @param mixed $sourceDocument Documento o array di  Documento da firmare può essere un file oppure un base64 del file
     * @return array status\message 
     */

    public static function isValidSourceDocument($sourceDocument) {
        if (is_array($sourceDocument)) {
            $byteDimensionTot = 0;
            foreach ($sourceDocument as $idoc) {
                $result = self::isValidSourceDocumentsingle($idoc);
                if ($result["status"] !== true) {
                    return $result;
                } else {
                    $byteDimensionTot += self::getDimensionFile($idoc);
                }
            }
            if (self::POST_SIZE_LIMIT - self::POST_ACCETPT_TOLLERANCE - $byteDimensionTot >= 0) {
                return array("status" => true);
            } else {
                return array("status" => false,
                    "message" => 'La dimensione totale dei ' . count($sourceDocument) . ' files da firmare eccede la dimensione massima consettita ' . (self::POST_SIZE_LIMIT - self::POST_ACCETPT_TOLLERANCE) . ' MB');
            }
        } else {
            return self::isValidSourceDocumentsingle($sourceDocument);
        }
    }

    /**
     * Ritorna il documento o i documneti 
     * @param mixed $sourceDocument 
     * @param bool $base64 
     * @return mixed Source
     */
    public static function getSourceSign($sourceDocument, $base64 = true) {
        if (is_array($sourceDocument)) {
            header("Content-Type: text/plain");
            $xmlDoc = new DOMDocument();
            $root = $xmlDoc->createElement("root");
            foreach ($sourceDocument as $idoc) {
                $file = $xmlDoc->createElement("file");

                $filename = $xmlDoc->createElement("name", basename($idoc));
                $file->appendChild($filename);

                $filecontent = $xmlDoc->createElement("content", self::getSourceContent($idoc));
                $file->appendChild($filecontent);
                $root->appendChild($file);
            }
            $xmlDoc->appendChild($root);
            $xmlDoc->formatOutput = true;
            if ($base64) {
                $source = base64_encode($xmlDoc->saveXML());
            } else {
                $source = $xmlDoc->saveXML();
            }
        } else {
            $source = self::getSourceContent($sourceDocument, $base64);
        }
        return $source;
    }

    public static function adapterSignersResponse($type, $signers) {
        switch ($type) {
            case itaSignatureFactory::CLASSE_FIRMA_PROVIDER_PKNET:
                $resultArray = self::getArrayToXml($signers);
                $resultSigners = self::getPkNetSigners($resultArray);
                break;
            case itaSignatureFactory::CLASSE_FIRMA_PROVIDER_ARUBA:
                $resultSigners = self::getArubaSigners($signers);
                break;

            default:
                break;
        }
        return $resultSigners;
    }

    private function isValidSourceDocumentsingle($sourceDocument) {
        if (is_file($sourceDocument)) {
            if (!file_exists($sourceDocument)) {
                return array("status" => false,
                    "message" => 'il file:' . $sourceDocument . ' non è valido ');
            } else {
                $esito = self::verifyDimensionFile($sourceDocument);
                if (!$esito) {
                    return array("status" => false,
                        "message" => "il file\files sono troppo grandi per essere trasferiti");
                }
            }
        } else if (base64_decode($sourceDocument, true) === false) {
            return array("status" => false,
                "message" => "Il parametro sourceDocument non è ne un file ne un base64 valido");
        } else {
            $esito = self::verifyDimensionBase64($sourceDocument);
            if (!$esito) {
                return array("status" => false,
                    "message" => "il file\files sono troppo grandi per essere trasferiti");
            }
        }
        return array("status" => true);
    }

    private static function getDimensionFile($document) {
        $bytes = filesize($document);
        return round($bytes / pow(1024, 2), 3);
    }

    private static function verifyDimensionFile($sourceDocument) {
        $bytes = filesize($sourceDocument);
        return self::isValidDimension($bytes);
    }

    private static function verifyDimensionBase64($sourceDocument) {
        $bytes = strlen($sourceDocument);
        return self::isValidDimension($bytes);
    }

    private static function isValidDimension($bytes) {
        $result = $bytes / pow(1024, 2);
        if (self::POST_SIZE_LIMIT - self::POST_ACCETPT_TOLLERANCE - $result >= 0) {
            return true;
        } else {
            return false;
        }
    }

    private static function getSourceContent($sourceDocument, $base64 = true) {
        if ($sourceDocument) {
            if (is_file($sourceDocument)) {
                $filecontent = file_get_contents($sourceDocument);
                if ($base64) {
                    $data = base64_encode($filecontent);
                } else {
                    $data = $filecontent;
                }
            } else {
                $data = $sourceDocument;
            }
        }
        return $data;
    }

    private static function getArubaSigners($resultArray) {
        $results = array();
        foreach ($resultArray as $arrayModel) {

            $model = new ItaPHPSignatureSigner();
            $model->setIssuer($arrayModel["Issuer_CN"]);
            $model->setSubject($arrayModel["signer"]);
            $model->setFiscalCode($arrayModel["fiscalCode"]);
            $model->setValidStartDate(null);
            $model->setValidEndDate(null);
            $model->setCertificate(null);
            $model->setHashAlgOid(null);
            $model->setSigningTime(null);
            $results[] = $model;
        }
        return $results;
    }

    private static function getPkNetSigners($resultArray) {
        $results = array();
        foreach ($resultArray as $key => $value) {
            if ($key == "SIGNER") {
                $model = new ItaPHPSignatureSigner();
                $model->setIssuer($value[0]["ISSUER_DN"][0]['@textNode']);
                $model->setSubject($value[0]["SUBJECT_DN"][0]['@textNode']);
                $model->setFiscalCode($value[0]["FISCAL_CODE"][0]['@textNode']);
                $model->setValidStartDate($value[0]["VALID_START_DATE"][0]['@textNode']);
                $model->setValidEndDate($value[0]["VALID_END_DATE"][0]['@textNode']);
                $model->setCertificate($value[0]["CERTIFICATE_SN"][0]['@textNode']);
                $model->setHashAlgOid($value[0]["HASH_ALG_OID"][0]['@textNode']);
                $model->setSigningTime($value[0]["SIGNING_TIME"][0]['@textNode']);
                $results[] = $model;
            }
        }
        return $results;
    }

    public static function getArrayToXml($signers) {
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($signers);

        if (!$retXml) {
            return false;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            return false;
        }
        return $arrayXml;
    }

}
