<?php

/**
 * Description of itaP7mj4signDSS
 *
 * @author michele moscioni .....
 */
require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');

class itaP7mj4signDSS extends itaP7m {
    /*
     * Outputs J4Sign
     */

    public $xmlReport;
    public $xmlReportStringResult;
    public $xmlReportStructResult;

    /*
     *  OUTPUTS DSS
     */
    public $xmlSimpleReport;
    public $xmlDetailReport;
    public $xmlDiagnosticReport;
    public $xmlAdditionalInfo;
    public $xmlSimpleReportStringResult;
    public $xmlSimpleReportStructResult;
    public $xmlDiagnosticReportStringResult;
    public $xmlDiagnosticReportStructResult;
    public $xmlDetailReportStringResult;
    public $xmlDetailReportStructResult;
    public $xmlAdditionalInfoStringResult;
    public $xmlAdditionalInfoStructResult;
    public $outputFormat;

    const PAL_DSS_JAR = 'pal-j4sign.jar';
    const PAL_DSS_OPT_VERIFY = 'VERIFY';
    const PAL_DSS_OPT_UPDATE = 'UPDATE';
    const PAL_DSS_OPT_EXTRACT = 'EXTRACT';
    const PAL_DSS_VALIDATION_LEVEL_ARCHIVAL_DATA = 'ARCHIVAL_DATA';
    const PAL_DSS_VALIDATION_LEVEL_BASIC_SIGNATURES = 'BASIC_SIGNATURES';
    const PAL_DSS_VALIDATION_LEVEL_LONG_TERM_DATA = 'LONG_TERM_DATA';
    const PAL_DSS_OUTPUT_FORMAT_DSS = 'OUTPUT_DSS';
    const PAL_DSS_OUTPUT_FORMAT_J4SIGN = 'OUTPUT_J4SIGN';
    const PAL_DSS_SIGN_ENVELOPE_CADES = 'CADES';
    const PAL_DSS_SIGN_ENVELOPE_PADES = 'PADES';
    const PAL_DSS_SIGN_ENVELOPE_XADES = 'XADES';
    const PAL_DSS_SIGN_ENVELOPE_AUTO = 'AUTO';

    public static function getInstance($Filep7m, $verify = true, $sign_envelope = self::PAL_DSS_SIGN_ENVELOPE_AUTO) {

        /*
         *  Creo le path temporanee
         */
        $tempPath = self::createTempPath($Filep7m);
        if (!$tempPath) {
            return false;
        }


        if ($sign_envelope == self::PAL_DSS_SIGN_ENVELOPE_AUTO) {
            if (strtolower(pathinfo($Filep7m, PATHINFO_EXTENSION)) == 'p7m') {
                $sign_envelope = self::PAL_DSS_SIGN_ENVELOPE_CADES;
            } elseif (strtolower(pathinfo($Filep7m, PATHINFO_EXTENSION)) == 'pdf') {
                $sign_envelope = self::PAL_DSS_SIGN_ENVELOPE_PADES;
            } elseif (strtolower(pathinfo($Filep7m, PATHINFO_EXTENSION)) == 'xml') {
                $sign_envelope = self::PAL_DSS_SIGN_ENVELOPE_XADES;
            }
        }

        /*
         * Inizializzo le variabile di lavoro
         */
        $inputPath = $tempPath . "/inpContent/";
        $outCPath = $tempPath . "/outContent/";
        $outRPath = $tempPath . "/outResult/";
        $inputFile = $inputPath . pathinfo(utf8_decode($Filep7m), PATHINFO_BASENAME);
        $outFileName = pathinfo(utf8_decode($Filep7m), PATHINFO_FILENAME);

        $jarPath = App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJ4SignDSS/' . self::PAL_DSS_JAR;

        /*
         * Output Principale J4Sign
         */
        $xmlReport = $outRPath . pathinfo($outFileName, PATHINFO_FILENAME) . ".xml";

        /*
         * Outputs  DSS
         */
        $xmlSimpleReport = $outRPath . pathinfo($outFileName, PATHINFO_FILENAME) . "_simple.xml";
        $xmlDetailReport = $outRPath . pathinfo($outFileName, PATHINFO_FILENAME) . "_detail.xml";
        $xmlDiagnosticReport = $outRPath . pathinfo($outFileName, PATHINFO_FILENAME) . "_diagnostic.xml";
        $xmlAdditionalInfo = $outRPath . pathinfo($outFileName, PATHINFO_FILENAME) . "_additionInfo.xml";


        /*
         * Creao le path di lavoro
         */
        if (!mkdir($inputPath)) {
            return false;
        }
        if (!mkdir($outCPath)) {
            return false;
        }
        if (!mkdir($outRPath)) {
            return false;
        }

        /*
         * Copio il file da analizzare
         */
        if (!@copy($Filep7m, $inputFile)) {
            return false;
        }

        /*
         * Creo lastringa di comando
         */

        $jarPath = App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJ4SignDSS/';
        $confPath = App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJ4SignDSS/conf/logging.properties';
        $lastdir = getcwd();
        chdir($jarPath);
        $command = App::getConf("Java.JVM8Path") . " -Djava.util.logging.config.file=$confPath -jar $jarPath" . self::PAL_DSS_JAR;
        if ($verify) {
            $command .= ' ' . self::PAL_DSS_OPT_VERIFY;
            $command .= ' "' . $inputFile . "\"";
            $command .= ' ' . self::PAL_DSS_VALIDATION_LEVEL_ARCHIVAL_DATA;
            $command .= ' ' . $outCPath;
            $command .= ' ' . $outRPath;
        } else {
            $command .= ' ' . self::PAL_DSS_OPT_EXTRACT;
            $command .= ' "' . $inputFile . "\"";
            $command .= ' ' . $outCPath;
        }
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command .= ' 2> NUL';
        } else {
            $command .= ' 2> /dev/null';
        }


        /*
         * Lancio il  comando java
         */
        if (file_exists(ITA_LIB_PATH . '/itaPHPCore/itaSysExec.class.php')) {
            require_once(ITA_LIB_PATH . '/itaPHPCore/itaSysExec.class.php');
            $itaExecuter = new itaSysExec();
            $ret = $itaExecuter->execute($command, null, $stdout, $stderr, 600);
        } else {
            exec($command, $ret);
        }
        chdir($lastdir);

        if ($sign_envelope == self::PAL_DSS_SIGN_ENVELOPE_CADES) {
            /*
             * Assegna Files tipo j4sign
             */
            if (file_exists($xmlReport)) {


                $xmlStringResult = file_get_contents($xmlReport);
                file_put_contents($xmlReport, utf8_encode($xmlStringResult));

                $xmlReportStringResult = file_get_contents($xmlReport);
                $xmlReportObj = new itaXML();
                $xmlReportRet = $xmlReportObj->setXmlFromFile($xmlReport);
                if ($xmlReportRet == false) {
                    return false;
                }
                $xmlReportArr = $xmlReportObj->toArray($xmlReportObj->asObject());
            }
            $arrContentFiles = array_diff(scandir($outCPath), array(".", ".."));
            foreach ($arrContentFiles as $key => $value) {
                $arrContentFiles[$key] = $outCPath . $value;
            }

            try {
                $obj = new itaP7mj4signDSS();
            } catch (Exception $exc) {
                return false;
            }

            $obj->file = $Filep7m;
            $obj->tempPath = $tempPath;
            $obj->xmlReport = $xmlReport;
            $obj->outputFormat = self::PAL_DSS_OUTPUT_FORMAT_J4SIGN;

            $obj->xmlReportStringResult = $xmlReportStringResult;
            $obj->xmlReportStructResult = $xmlReportArr;

            $obj->arrContentFiles = $arrContentFiles;
            foreach ($arrContentFiles as $key => $contentFile) {
                $obj->contentSHA[pathinfo($contentFile, PATHINFO_BASENAME)] = sha1_file($contentFile);
            }

            if (strtolower(pathinfo($obj->getContentFileName(), PATHINFO_EXTENSION)) == 'p7m') {
                $obj->subLevelObj = itaP7m::getP7mInstance($obj->getContentFileName(), $verify, $sign_envelope);
                if ($obj->subLevelObj === false) {
                    return false;
                }
            }
            
        } elseif ($sign_envelope == self::PAL_DSS_SIGN_ENVELOPE_XADES) {
            /*
             * Assegna Files tipo DSS
             */
            if (file_exists($xmlSimpleReport)) {


                $xmlSimpeReportStringResult = file_get_contents($xmlSimpleReport);
                $xmlSimpleReportObj = new itaXML();
                $xmlSimpleReportRet = $xmlSimpleReportObj->setXmlFromFile($xmlSimpleReport);
                if ($xmlSimpleReportRet == false) {
                    return false;
                }
                $xmlSimpleReportArr = $xmlSimpleReportObj->toArray($xmlSimpleReportObj->asObject());

                $xmlDetailReportStringResult = file_get_contents($xmlDetailReport);
                $xmlDetailReportObj = new itaXML;
                $xmlDetailReportRet = $xmlDetailReportObj->setXmlFromFile($xmlDetailReport);
                if ($xmlDetailReportRet == false) {
                    return false;
                }
                $xmlDetailReportArr = $xmlDetailReportObj->toArray($xmlDetailReportObj->asObject());

                $xmlDiagnosticReportStringResult = file_get_contents($xmlDiagnosticReport);
                $xmlDiagnosticReportObj = new itaXML;
                $xmlDiagnosticReportRet = $xmlDiagnosticReportObj->setXmlFromFile($xmlDiagnosticReport);
                if ($xmlDiagnosticReportRet == false) {
                    return false;
                }
                $xmlDiagnosticReportArr = $xmlDiagnosticReportObj->toArray($xmlDiagnosticReportObj->asObject());

                $xmlAdditionalInfoStringResult = file_get_contents($xmlAdditionalInfo);

                $xmlAdditionalInfoObj = new itaXML;
                $xmlAdditionalInfoRet = $xmlAdditionalInfoObj->setXmlFromFile($xmlAdditionalInfo);
                if ($xmlAdditionalInfoRet == false) {
                    return false;
                }
                $xmlAdditionalInfoArr = $xmlAdditionalInfoObj->toArray($xmlAdditionalInfoObj->asObject());
            }
            $arrContentFiles = array_diff(scandir($outCPath), array(".", ".."));
            foreach ($arrContentFiles as $key => $value) {
                $arrContentFiles[$key] = $outCPath . $value;
            }

            try {
                $obj = new itaP7mDSS();
            } catch (Exception $exc) {
                return false;
            }

            $obj->file = $Filep7m;
            $obj->tempPath = $tempPath;
            $obj->outputFormat = self::PAL_DSS_OUTPUT_FORMAT_DSS;

            $obj->xmlSimpleReport = $xmlSimpleReport;
            $obj->xmlDetailReport = $xmlDetailReport;
            $obj->xmlDiagnosticReport = $xmlDiagnosticReport;
            $obj->xmlAdditionalInfo = $xmlAdditionalInfo;

            $obj->xmlSimpleReportStringResult = $xmlSimpeReportStringResult;
            $obj->xmlSimpleReportStructResult = $xmlSimpleReportArr;

            $obj->xmlDiagnosticReportStringResult = $xmlDiagnosticReportStringResult;
            $obj->xmlDiagnosticReportStructResult = $xmlDiagnosticReportArr;

            $obj->xmlDetailReportStringResult = $xmlDetailReportStringResult;
            $obj->xmlDetailReportStructResult = $xmlDetailReportArr;

            $obj->xmlAdditionalInfoStringResult = $xmlAdditionalInfoStringResult;
            $obj->xmlAdditionalInfoStructResult = $xmlAdditionalInfoArr;


            $obj->arrContentFiles = $arrContentFiles;
            foreach ($arrContentFiles as $key => $contentFile) {
                $obj->contentSHA[pathinfo($contentFile, PATHINFO_BASENAME)] = sha1_file($contentFile);
            }
        }



        return $obj;
    }

    public function getTempPath() {
        return $this->tempPath;
    }

    public function cleanData() {
        if (is_a($this->subLevelObj, 'itaP7mj4signDSS')) {
            $this->subLevelObj->cleanData();
        }
        return itaLib::deleteDirRecursive($this->getTempPath());
    }

    public function getContentFileName() {
        if (is_a($this->subLevelObj, 'itaP7mj4signDSS')) {
            return $this->subLevelObj->getContentFileName();
        } else {
            if ($this->arrContentFiles) {
                return reset($this->arrContentFiles);
            } else {
                return false;
            }
        }
    }

    public function getFileName() {
        return $this->file;
    }

    function getXmlReportStringResult() {
        return $this->xmlReportStringResult;
    }

    function getXmlReportStructResult() {
        return $this->xmlReportStructResult;
    }

    public function getAttachments($completePath = true) {
        if (!$completePathpl) {
            return array_map(array($this, 'removePath'), $this->arrContentFiles);
        }
        return $this->arrContentFiles;
    }

    public function isFileVerifyPassed() {
        return ($this->xmlReportStructResult['verificato'][0]['@textNode'] === 'true') ? true : false;
    }

    public function getMessageErrorFileAsString() {
        $retMessaggio = '';
        $retMessaggio = $this->getMessageErrorSignersAsString();
        return $retMessaggio;
    }

    private function getMessageErrorSignersAsString($verificaRisultatiNode) {
        $retMessaggio = '';
        if ($verificaRisultatiNode['esitoVerifica'][0]['@textNode'] === 'true') {
            $retMessaggio = "Firma Verificata correttamente";
        } else {
            $retMessaggio = "Firma con Errori: ";
            if ($verificaRisultatiNode['esitoIntegrita'][0]['@textNode'] === 'false') {
                $retMessaggio .= "Verifica Integrità fallita. ";
            }
            if ($verificaRisultatiNode['certificatoScaduto'][0]['@textNode'] === 'true') {
                $retMessaggio .= "Certificato Scaduto. ";
            }
            if ($verificaRisultatiNode['attributiFirmatiMinimiPresenti'][0]['@textNode'] === 'false') {
                $retMessaggio .= "Attributi Firma minimi mancanti. ";
            }
            if ($verificaRisultatiNode['percorsoCertificazioneValido'][0]['@textNode'] === 'false') {
                $retMessaggio .= "Percorso certificazione non valido. ";
            }
            if ($verificaRisultatiNode['controlloCRL'][0]['@textNode'] === 'false') {
                $retMessaggio .= "Controllo CRL non riuscito. ";
            }
            if ($verificaRisultatiNode['certificatoNonRevocato'][0]['@textNode'] === 'true') {
                $retMessaggio .= "Certificato revocato. ";
            }
        }
        return $retMessaggio;
    }

    public function getInfoSummary() {
        $summary = array();
        $fileName = pathinfo($this->file, PATHINFO_FILENAME);
        $contentFileName = $this->ContentFileName;
        $fullFileName = $this->file;

        $subjectSigner = array();
        foreach ($this->xmlReportStructResult['verificaRisultati'] as $key => $verificaRisultatiNodo) {
            $summaryRow = array();
            $summaryRow['fileName'] = $fileName;
            $summaryRow['contentFileName'] = $contentFileName;
            $summaryRow['fullFileName'] = $fullFileName;
            if ($verificaRisultatiNodo['esitoVerifica'][0]['@textNode'] == 'true') {
                $summaryRow['isSignerVerifyPassed'] = true;
                $summaryRow['isSignerVerifyPassedHtml'] = '<span class="ita-icon ita-icon-shield-green-32x32">&nbsp;</span>';
            } else {
                $summaryRow['isSignerVerifyPassed'] = false;
                $summaryRow['isSignerVerifyPassedHtml'] = '<span class="ita-icon ita-icon-shield-red-32x32">&nbsp;</span>';
            }
            $summaryRow['signatureId'] = '';

            $subject = $this->parseLdapDn($verificaRisultatiNodo['firmatario'][0]['@textNode']);

            $summaryRow['signer'] = $subject['CN'][0];


            $notBefore = DateTime::createFromFormat('d/m/Y H:i:s', $verificaRisultatiNodo['certificatoValidoDa'][0]['@textNode']);
            $notAfter = DateTime::createFromFormat('d/m/Y H:i:s', $verificaRisultatiNodo['certificatoValidoA'][0]['@textNode']);

            $summaryRow['NotBefore'] = ($notBefore) ? $notBefore->format('Ymd H:i:s') : '';
            $summaryRow['NotAfter'] = ($notAfter) ? $notAfter->format('Ymd H:i:s') : '';

            $issuer = $this->parseLdapDn($verificaRisultatiNodo['certificatore'][0]['@textNode']);
            $summaryRow['issuer_CN'] = $issuer['CN'][0];



            $fiscalCode = $subject['SERIALNUMBER'][0];
            $role = $subject['T'][0];
            $summaryRow['fiscalCode'] = $fiscalCode;
            $summaryRow['role'] = $role;
            /*
             * Extra info
             */
            $summaryRow['subjectDN_C'] = $subject['C'][0];
            $summaryRow['subjectDN_O'] = $subject['O'][0];
            $summaryRow['subjectDN_OU'] = $subject['OU'][0];
            $summaryRow['subjectDN_DN'] = $subject['DN'][0];

            /*
             *  Da verificare firme congiute o subordinate  
             */

//            if (isset($signer['subjectSigners']['subjectSigner'][0]) || $signer['subjectSigners']['subjectSigner']) {
//                $summaryRow['hasSubjectSigners'] = true;
//                $summaryRow['hasSubjectSignersHtml'] = '<span class="ita-icon ita-icon-evidenzia-32x32">&nbsp;</span>';
//            } else {
//                $summaryRow['hasSubjectSigners'] = false;
//                $summaryRow['hasSubjectSignersHtml'] = "";
//            }

            $summaryRow['messageErrorSigner'] = $this->getMessageErrorSignersAsString($verificaRisultatiNodo);
            
            if(!empty($verificaRisultatiNodo['dataFirma'][0]['@textNode'])){
                $dataFirma = DateTime::createFromFormat('d/m/Y H:i:s', $verificaRisultatiNodo['dataFirma'][0]['@textNode']);
                $summaryRow['dataFirma'] = $dataFirma->format('Ymd H:i:s');
            }
            else{
                $summaryRow['dataFirma'] = null;
            }
            
            $summary[] = $summaryRow;
        }

        if (is_a($this->subLevelObj, 'itaP7mj4signDSS')) {
            $summary = array_merge($summary, $this->subLevelObj->getInfoSummary());
        }
        return $summary;
    }

    function getContentSHA($file = '') {
        if ($file) {
            return $this->contentSHA[$file];
        } else {
            return reset($this->contentSHA);
        }
    }

    private function extractSignerInfo($signatureId) {
        foreach ($this->xmlAdditionalInfoStructResult['object'][0]['void'] as $signerNode) {
            if ($signatureId == $signerNode['string'][0]['@textNode']) {
                return $signerNode['string'][1]['@textNode'];
            }
        }
    }

    private function getSignigCertificate($signatureId) {
        foreach ($this->xmlDiagnosticReportStructResult['Signature'] as $signatureNode) {
            if ($signatureId == $signatureNode['@attributes']['Id']) {
                $signingCertificateId = $signatureNode['SigningCertificate'][0]['@attributes']['Id'];
                break;
            }
        }

        if ($signingCertificateId) {
            foreach ($this->xmlDiagnosticReportStructResult['UsedCertificates'][0]['Certificate'] as $certificateNode) {
                if ($signingCertificateId == $certificateNode['@attributes']['Id']) {
                    return $certificateNode;
                }
            }
        }
    }

}
