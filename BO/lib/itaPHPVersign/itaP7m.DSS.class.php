<?php

/**
 * Description of itaP7mDSS
 *
 * @author michele moscioni .....
 */
require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');

class itaP7mDSS extends itaP7m {

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

    public function getTempPath() {
        return $this->tempPath;
    }

    public function cleanData() {
        if (is_a($this->subLevelObj, 'itaP7mDSS')) {
            $this->subLevelObj->cleanData();
        }
        return itaLib::deleteDirRecursive($this->getTempPath());
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
            $retMessaggio .= $Error['@textNode'] . " ";
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
            $summaryRow['dataFirma'] = null;

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

        foreach ($this->xmlDiagnosticReportStructResult['Signatures'] as $signature) {
            foreach ($signature['Signature'] as $signatureNode) {
                if ($signatureId == $signatureNode['@attributes']['Id']) {
                    $signingCertificateId = $signatureNode['SigningCertificate'][0]['@attributes']['Id'];
                    break;
                }
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
