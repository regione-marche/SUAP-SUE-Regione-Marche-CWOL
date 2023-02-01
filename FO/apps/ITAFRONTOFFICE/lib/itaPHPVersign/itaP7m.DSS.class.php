<?php

/**
 * Description of itaP7mDSS
 *
 * @author michele
 */
require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');

class itaP7mDSS extends itaP7m {

    private $file;
    private $tempPath;
    private $xmlSimpleReportStringResult;
    private $xmlSimpleReportStructResult;
    private $xmlDiagnosticReportStringResult;
    private $xmlDiagnosticReportStructResult;
    private $xmlDetailReportStringResult;
    private $xmlDetailReportStructResult;
    private $xmlAdditionalInfoStringResult;
    private $xmlAdditionalInfoStructResult;
    private $arrContentFiles;
    private $contentSHA;
    private $subLevelObj;
    private $VerifyResult;

    const PAL_DSS_JAR = 'pal-dss-app.jar';
    const PAL_DSS_OPT_VERIFY = 'VERIFY';
    const PAL_DSS_OPT_UPDATE = 'UPDATE';
    const PAL_DSS_OPT_EXTRACT = 'EXRACT';
    const PAL_DSS_VALIDATION_LEVEL_ARCHIVAL_DATA = 'ARCHIVAL_DATA';
    const PAL_DSS_VALIDATION_LEVEL_BASIC_SIGNATURES = 'BASIC_SIGNATURES';
    const PAL_DSS_VALIDATION_LEVEL_LONG_TERM_DATA = 'LONG_TERM_DATA';

    public static function getInstance($Filep7m, $verify = true) {

        /*
         *  Creo le path temporanee
         */
		 
        $tempPath = self::createTempPath($Filep7m);
        if (!$tempPath) {
            return false;
        }

        /*
         * Inizializzo le variabile di lavoro
         */
        $inputPath = $tempPath . "/inpContent/";
        $outCPath = $tempPath . "/outContent/";
        $outRPath = $tempPath . "/outResult/";
        $inputFile = $inputPath . pathinfo($Filep7m, PATHINFO_BASENAME);
        $outFileName = pathinfo($Filep7m, PATHINFO_FILENAME);
		
		
        $jarPath = ITA_LIB_PATH . '/java/itaDSS/pal-dss-app.jar';
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
//        $command = App::getConf("Java.JVM8Path") . ' -jar ' . $jarPath;
//        $command .= ' ' . self::PAL_DSS_OPT_UPDATE;

        if (ITA_FRONTOFFICE_JVM8_PATH != "" && file_exists(ITA_FRONTOFFICE_JVM8_PATH)) {

            $command = ITA_FRONTOFFICE_JVM8_PATH . ' -jar ' . $jarPath;
            $command .= ' ' . self::PAL_DSS_OPT_VERIFY;
            $command .= ' "' . $inputFile . "\"";
            $command .= ' ' . self::PAL_DSS_VALIDATION_LEVEL_ARCHIVAL_DATA;
            $command .= ' ' . $outCPath;
            $command .= ' ' . $outRPath;
        } else {
            return false;
        }
        /*
         * Lancio il  comando java
         */
        if (file_exists(ITA_LIB_PATH . '/itaPHPCore/itaSysExec.class.php')) {
            require_once(ITA_LIB_PATH . '/itaPHPCore/itaSysExec.class.php');
            $itaExecuter = new itaSysExec();
//            $itaExecuter->setStdoutMode('file');
//            $itaExecuter->setStdoutTarget('c:\tmp\itaDss.log');
//            $itaExecuter->setStdoutAppend('a');
            $ret = $itaExecuter->execute($command, null, $stdout, $stderr, 600);
        } else {
            exec($command, $ret);
        }
        if (!file_exists($xmlSimpleReport)) {
            return false;
        }

	
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

        if (strtolower(pathinfo($obj->getContentFileName(), PATHINFO_EXTENSION)) == 'p7m') {
            $obj->subLevelObj = itaP7m::getP7mInstance($obj->getContentFileName());
            if ($obj->subLevelObj === false) {
                return false;
            }
        }
        return $obj;
    }

    public function getTempPath() {
        return $this->tempPath;
    }

    public function cleanData() {
        if (is_a($this->subLevelObj, 'itaP7mDSS')) {
            $this->subLevelObj->cleanData();
        }
        return self::deleteDirRecursive($this->getTempPath());
    }

    public function getContentFileName() {
        if (is_a($this->subLevelObj, 'itaP7mDSS')) {
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

    function getXmlSimpleReportStringResult() {
        return $this->xmlSimpleReportStringResult;
    }

    function getXmlSimpleReportStructResult() {
        return $this->xmlSimpleReportStructResult;
    }

    function getXmlDiagnosticReportStringResult() {
        return $this->xmlDiagnosticReportStringResult;
    }

    function getXmlDiagnosticReportStructResult() {
        return $this->xmlDiagnosticReportStructResult;
    }

    function getXmlDetailReportStringResult() {
        return $this->xmlDetailReportStringResult;
    }

    function getXmlDetailReportStructResult() {
        return $this->xmlDetailReportStructResult;
    }

    function getXmlAdditionalInfoStringResult() {
        return $this->xmlAdditionalInfoStringResult;
    }

    function getXmlAdditionalInfoStructResult() {
        return $this->xmlAdditionalInfoStructResult;
    }

    function setXmlSimpleReportStringResult($xmlSimpleReportStringResult) {
        $this->xmlSimpleReportStringResult = $xmlSimpleReportStringResult;
    }

    function setXmlSimpleReportStructResult($xmlSimpleReportStructResult) {
        $this->xmlSimpleReportStructResult = $xmlSimpleReportStructResult;
    }

    function setXmlDiagnosticReportStringResult($xmlDiagnosticReportStringResult) {
        $this->xmlDiagnosticReportStringResult = $xmlDiagnosticReportStringResult;
    }

    function setXmlDiagnosticReportStructResult($xmlDiagnosticReportStructResult) {
        $this->xmlDiagnosticReportStructResult = $xmlDiagnosticReportStructResult;
    }

    function setXmlDetailReportStringResult($xmlDetailReportStringResult) {
        $this->xmlDetailReportStringResult = $xmlDetailReportStringResult;
    }

    function setXmlDetailReportStructResult($xmlDetailReportStructResult) {
        $this->xmlDetailReportStructResult = $xmlDetailReportStructResult;
    }

    function setXmlAdditionalInfoStringResult($xmlAdditionalInfoStringResult) {
        $this->xmlAdditionalInfoStringResult = $xmlAdditionalInfoStringResult;
    }

    function setXmlAdditionalInfoStructResult($xmlAdditionalInfoStructResult) {
        $this->xmlAdditionalInfoStructResult = $xmlAdditionalInfoStructResult;
    }

    public function getAttachments($completePath = true) {
        if (!$completePathpl) {
            return array_map(array($this, 'removePath'), $this->arrContentFiles);
        }
        return $this->arrContentFiles;
    }

    private function removePath($value) {
        return pathinfo($value, PATHINFO_BASENAME);
    }

    public function isFileVerifyPassed() {
        return ($this->xmlSimpleReportStructResult['ValidSignaturesCount'][0]['@textNode'] === $this->xmlSimpleReportStructResult['SignaturesCount'][0]['@textNode']) ? true : false;
    }

    public function getMessageErrorFileAsString() {
        $retMessaggio = '';        
        $retMessaggio = $this->getMessageErrorSignersAsString();
        return $retMessaggio;
    }

    private function getMessageErrorSignersAsString($signatureId) {
        $retMessaggio = '';
        foreach ($this->xmlSimpleReportStructResult['Signature'] as $SignatureNode) {
            if ($SignatureNode['@attributes']['Id'] == $signatureId) {
                break;
            }
        }

        foreach ($SignatureNode['Errors'] as $Error) {
            $retMessaggio .= $Error['@textNode'];
        }
        return $retMessaggio;
    }

//@TODO Ricostruire
    public function getInfoSummary() {
        $summary = array();
        $fileName = pathinfo($this->file, PATHINFO_FILENAME);
        $contentFileName = $this->ContentFileName;
        $fullFileName = $this->file;

        $subjectSigner = array();
        foreach ($this->xmlSimpleReportStructResult['Signature'] as $key => $Signature) {
            $summaryRow = array();
            $summaryRow['fileName'] = $fileName;
            $summaryRow['contentFileName'] = $contentFileName;
            $summaryRow['fullFileName'] = $fullFileName;
            if ($Signature['Indication'][0]['@textNode'] == 'TOTAL_PASSED') {
                $summaryRow['isSignerVerifyPassed'] = true;
                $summaryRow['isSignerVerifyPassedHtml'] = '<span class="ita-icon ita-icon-shield-green-32x32">&nbsp;</span>';
            } else {
                $summaryRow['isSignerVerifyPassed'] = false;
                $summaryRow['isSignerVerifyPassedHtml'] = '<span class="ita-icon ita-icon-shield-red-32x32">&nbsp;</span>';
            }
            $summaryRow['signatureId'] = $Signature['@attributes']['Id'];
            $summaryRow['signer'] = $Signature['SignedBy'][0]['@textNode'];

            $signingCertificate = $this->getSignigCertificate($summaryRow['signatureId']);

            foreach ($signingCertificate['IssuerDistinguishedName'] as $IssuerDistinguishedNameNode) {
                $IssuerDistinguishedName = $IssuerDistinguishedNameNode['@textNode'];
                if ($IssuerDistinguishedNameNode['@attributes']['Format'] == 'RFC2253') {
                    break;
                }
            }
            
            $summaryRow['NotBefore'] .= $signingCertificate['NotBefore'][0]['@textNode'];
            $summaryRow['NotAfter'] .= $signingCertificate['NotAfter'][0]['@textNode'];

            foreach ($signingCertificate['SubjectDistinguishedName'] as $SubjectDistinguishedNameNode) {
                $SubjectDistinguishedName = $SubjectDistinguishedNameNode['@textNode'];
                if ($SubjectDistinguishedNameNode['@attributes']['Format'] == 'RFC2253') {
                    break;
                }
            }


            $issuer = $this->parseLdapDn($IssuerDistinguishedName);
            $summaryRow['issuer_CN'] = $issuer['CN'][0];


            $subject = $this->parseLdapDn($SubjectDistinguishedName);
            $fiscalCode = itaLib::hex2BinDecode(substr($subject['2.5.4.5'][0], 1));
            $role = itaLib::hex2BinDecode(substr($subject['2.5.4.12'][0], 1));
            $summaryRow['fiscalCode'] = $fiscalCode;
            $summaryRow['role'] = $role;
            /*
             * Extra info
             */
            $signerInfo = $this->extractSignerInfo($summaryRow['signatureId']);



            $subjectDN = $this->parseLdapDn($signerInfo);
            $summaryRow['subjectDN_C'] = $subjectDN['C'][0];
            $summaryRow['subjectDN_O'] = $subjectDN['O'][0];
            $summaryRow['subjectDN_OU'] = $subjectDN['OU'][0];
            $summaryRow['subjectDN_DN'] = $subjectDN['DN'][0];
            if (isset($signer['subjectSigners']['subjectSigner'][0]) || $signer['subjectSigners']['subjectSigner']) {
                $summaryRow['hasSubjectSigners'] = true;
                $summaryRow['hasSubjectSignersHtml'] = '<span class="ita-icon ita-icon-evidenzia-32x32">&nbsp;</span>';
            } else {
                $summaryRow['hasSubjectSigners'] = false;
                $summaryRow['hasSubjectSignersHtml'] = "";
            }
            $summaryRow['messageErrorSigner'] = $this->getMessageErrorSignersAsString($summaryRow['signatureId']);

            $summary[] = $summaryRow;
        }

        if (is_a($this->subLevelObj, 'itaP7mDSS')) {
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
