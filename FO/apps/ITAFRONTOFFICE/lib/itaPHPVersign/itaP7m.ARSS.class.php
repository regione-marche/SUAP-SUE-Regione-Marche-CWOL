<?php

/**
 * Description of itaP7mARSS
 *
 * @author michele
 */
class itaP7mARSS extends itaP7m{

    private $file;
    private $tempPath;
    private $xmlFileResult;
    private $xmlStringResult;
    private $xmlStructResult;
    private $arrContentFiles;
    private $contentSHA;
    private $VerifyResult;

    public static function getInstance($Filep7m) {
        require_once ITA_LIB_PATH . '/itaPHPVersign/rsnVerifier.class.php';
        $verifier = new rsnVerifier();
        $verifier->setInputFilePath($Filep7m);
        $ret = $verifier->verify();
		
        if ($ret) {
            $VerifyResult = $verifier->getResult();
            //$temp_path = self::createTempPath("rsnVerifier");
            $temp_path = self::createTempPath($Filep7m);

            if (!$temp_path) {
                return false;
            }
            //$temp_content = $temp_path . "/" . md5(microtime());
            $temp_content = $temp_path . "/" . pathinfo($Filep7m,PATHINFO_FILENAME);
            usleep(5);
            file_put_contents($temp_content, base64_decode($VerifyResult['binaryoutput']));
            if (!file_exists($temp_content)) {
                return false;
            }

            try {
                $obj = new itaP7mARSS();
            } catch (Exception $exc) {
                return false;
            }

            $obj->VerifyResult = $VerifyResult;
            $obj->ContentFileName = $temp_content;

            $obj->file = $Filep7m;
            $obj->tempPath = $temp_path;

            $obj->arrContentFiles[0] = $temp_content;
            $obj->contentSHA[pathinfo($temp_content, PATHINFO_BASENAME)] = sha1_file($temp_content);

            return $obj;
        } else {
            return false;
        }
    }

    public function getTempPath() {
        return $this->tempPath;
    }

    public function cleanData() {
		return self::deleteDirRecursive($this->getTempPath());
    }

    public function getContentFileName() {
        if ($this->arrContentFiles) {
            return reset($this->arrContentFiles);
        } else {
            return false;
        }
    }

    public function getFileName() {
        return $this->file;
    }

    public function isFileVerifyPassed() {
        return ($this->VerifyResult['result']['status'] == 'OK') ? true : false;
    }

    public function getMessageErrorFileAsString() {

        $fileStatus = $this->VerifyResult['result']['status'];
        $fileStatusDescription = $this->VerifyResult['result']['description'];
        $retMessaggio = '';
        if ($fileStatus !== 'OK') {
            $retMessaggio = $fileStatusDescription;
        }
        return $retMessaggio;

        //@TODO ATTENZIONE SOMMA DEGLI ERRORI perche il suap/sue lo controlla come flag per verificare se ci sono errori
    }

    private function getMessageErrorSignersAsString($signer) {
        $messageErrorSigner = $signer['messageErrorsSigner'];
        if (!isset($messageErrorSigner['string'][0])) {
            $errorString = array();
            $errorString[] = $messageErrorSigner['string'];
        } else {
            $errorString = $messageErrorSigner['string'];
        }
        foreach ($errorString as $value) {
            $retMessaggio .= $value['@textNode'] . " ";
        }
        return $retMessaggio;
    }

    //@TODO Ricostruire
    public function getInfoSummary() {
        $summary = array();
        $fileName = pathinfo($this->file, PATHINFO_FILENAME);
        $contentFileName = $this->ContentFileName;
        $fullFileName = $this->file;
        if ($this->VerifyResult['result']['signer'][0]) {
            $subjectSigner = $this->VerifyResult['result']['signer'];
        } else {
            $subjectSigner = array($this->VerifyResult['result']['signer']);
        }

        foreach ($subjectSigner as $key => $signer) {
            $summaryRow = array();
            $summaryRow['fileName'] = $fileName;
            $summaryRow['contentFileName'] = $contentFileName;
            $summaryRow['fullFileName'] = $fullFileName;
            if ($signer['result'] == 'OK') {
                $summaryRow['isSignerVerifyPassed'] = true;
                $summaryRow['isSignerVerifyPassedHtml'] = '<span class="ita-icon ita-icon-shield-green-32x32">&nbsp;</span>';
            } else {
                $summaryRow['isSignerVerifyPassed'] = false;
                $summaryRow['isSignerVerifyPassedHtml'] = '<span class="ita-icon ita-icon-shield-red-32x32">&nbsp;</span>';
            }
            $summaryRow['signer'] = $signer['signername'];
            $summaryRow['issuer_CN'] = "";
            $summaryRow['fiscalCode'] = $signer['serialnumber'];
            $summaryRow['role'] = "";
            $summaryRow['subjectDN_C'] = "";
            $summaryRow['subjectDN_O'] = "";
            $summaryRow['subjectDN_OU'] = "";
            $summaryRow['subjectDN_DN'] = "";
            if (isset($signer['countersigners'])) {
                $summaryRow['hasSubjectSigners'] = true;
                $summaryRow['hasSubjectSignersHtml'] = '<span class="ita-icon ita-icon-evidenzia-32x32">&nbsp;</span>';
            } else {
                $summaryRow['hasSubjectSigners'] = false;
                $summaryRow['hasSubjectSignersHtml'] = "";
            }
            $summaryRow['messageErrorSigner'] = ($signer['description']) ? $signer['description'] : $this->VerifyResult['result']['description'];
            $summary[] = $summaryRow;
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

//    private static function createTempPath($file) {
//        if (!$file) {
//            return false;
//        }
//        $subPath = "p7m-file-" . md5($file . microtime());
//        //$tempPath = pathinfo($file, PATHINFO_DIRNAME) . "/" . $subPath;
//        $tempPath = ITA_FRONTOFFICE_TEMP . "/" . $subPath;
//        if (@mkdir($tempPath, 0777, true)) {
//            return $tempPath;
//        } else {
//            return false;
//        }
//    }

}
