<?php

class itaP7mj4sign extends itaP7m {

//    private $xmlFileResult;
//    private $xmlStringResult;
//    private $xmlStructResult;

    public static function getInstance($file) {
        /*
         * Valorizzo le variabili di lavoro
         * 
         */
        $tempPath = self::createTempPath($file);
        if (!$tempPath) {
            return false;
        }
        $inputFile = strtolower(pathinfo(utf8_decode($file), PATHINFO_BASENAME));        // input file

        $inputPath = $tempPath . "/inpContent/";            // output content Path                
        $outCPath = $tempPath . "/outContent/";            // output content Path        
        $outRPath = $tempPath . "/outResult/";             // output result Path
        $templatePath = './paths/headerfooter/';                         // headerfooter template path        
        $xmlFileResult = $outRPath . strtolower(pathinfo(utf8_decode($file), PATHINFO_BASENAME)) . "_ResultFinal.xml";
        if (!mkdir($inputPath)) {
            return false;
        }
        if (!@copy($file, $inputPath . $inputFile)) {
            return false;
        }
        if (!mkdir($outCPath)) {
            return false;
        }
        if (!mkdir($outRPath)) {
            return false;
        }
        $command = App::getConf("Java.JVMPath") . " -jar " . './versign.jar';
        $command .= " " . $inputPath;
        $command .= " \"" . $inputFile . "\"";
        $command .= " " . $outCPath;
        $command .= " " . $outRPath;
        $command .= " " . $templatePath;

        /*
         *  Eseguo il comando
         * 
         */
        //App::log($command);
        $lastdir = getcwd();
        chdir(App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJVersign/');
        if (file_exists(ITA_LIB_PATH . '/itaPHPCore/itaSysExec.class.php')) {
            /*
             * Nuovo metodo con timeout
             */
            require_once(ITA_LIB_PATH . '/itaPHPCore/itaSysExec.class.php');
            $itaExecuter = new itaSysExec();
            $ret = $itaExecuter->execute($command, null, $stdout, $stderr, 300);
        } else {
            /*
             * Metodo semplice senza timeout
             * 
             */
            exec($command, $ret);
        }
        chdir($lastdir);
        //
        // Migliorare il controllo degli errori
        // 
        if (!file_exists($xmlFileResult)) {
            return false;
        }
        $xmlStringResult = file_get_contents($xmlFileResult);
        file_put_contents($xmlFileResult, utf8_encode($xmlStringResult));
        require_once(ITA_LIB_PATH . '/QXml/QXml.class.php');
        $xmlObj = new QXML;
        $retXML = $xmlObj->setXmlFromFile($xmlFileResult);

        if ($retXML == false) {
            return false;
        }
        $xmlStructResult = $xmlObj->toArray($xmlObj->asObject());
        $arrContentFiles = array_diff(scandir($outCPath), array(".", ".."));
        foreach ($arrContentFiles as $key => $value) {
            $arrContentFiles[$key] = $outCPath . $value;
        }
        //
        // Creo l'oggetto da ritornare
        //
        try {
            $obj = new itaP7mj4sign();
        } catch (Exception $exc) {
            return false;
        }

        $obj->file = $file;
        $obj->tempPath = $tempPath;
        $obj->xmlFileResult = $xmlFileResult;
        $obj->xmlStringResult = $xmlStringResult;
        $obj->xmlStructResult = $xmlStructResult;
        $obj->arrContentFiles = $arrContentFiles;
        foreach ($arrContentFiles as $key => $contentFile) {
            $obj->contentSHA[pathinfo($contentFile, PATHINFO_BASENAME)] = sha1_file($contentFile);
        }
        return $obj;
    }

    public function __construct() {
        
    }

    public function cleanData() {
        return itaLib::deleteDirRecursive($this->getTempPath());
    }

    public function getFileName() {
        return $this->file;
    }

    public function getTempPath() {
        return $this->tempPath;
    }

    public function getXmlFileResult() {
        return $this->xmlFileResult;
    }

    public function getXmlStringResult() {
        return $this->xmlStringResult;
    }

    public function getXmlStructResult() {
        return $this->xmlStructResult;
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
        return ($this->xmlStructResult['fileSignedInfo']['isFileVerifyPassed']['@textNode'] == 'true') ? true : false;
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

    /**
     * Messaggio complessivo di errore relativa al controllo dell'intero file
     * 
     * @return string
     */
    public function getMessageErrorFileAsString() {
        if (isset($this->xmlStructResult['fileSignedInfo']['messageErrorsFile']['string'][0])) {
            $messaggioStruct = $this->xmlStructResult['fileSignedInfo']['messageErrorsFile']['string'];
        } else {
            $messaggioStruct = array($this->xmlStructResult['fileSignedInfo']['messageErrorsFile']['string']);
        }

        /*
         * Aggiunto il 17-03-2016 per restituire i messaggi d'errore che si trovano nell'array dei firmatari
         */
        if (!$messaggioStruct || $messaggioStruct[0] == "") {
            $messaggio = "";
            if (isset($this->xmlStructResult['allSigners']['subjectSigners']['subjectSigner'][0])) {
                $messaggioStruct = $this->xmlStructResult['allSigners']['subjectSigners']['subjectSigner'];
            } else {
                $messaggioStruct = array($this->xmlStructResult['allSigners']['subjectSigners']['subjectSigner']);
            }
            foreach ($messaggioStruct as $key => $value) {
                /*
                 * Aggiunto il 06/07/2016 perch� un firmatario puo avere piu errori, quindi $value['messageErrorsSigner']['string']
                 * pu� essere un array (Per problema SUE Comune di Pesaro
                 */
                //if (is_array($value['messageErrorsSigner']['string'])) {
                if (isset($value['messageErrorsSigner']['string'][0])) {
                    foreach ($value['messageErrorsSigner']['string'] as $errStr) {
                        $messaggio .= $errStr['@textNode'] . " - ";
                    }
                } else {
                    $messaggio .= $value['messageErrorsSigner']['string']['@textNode'] . " - ";
                }
            }
        } else {
            $messaggio = "";
            foreach ($messaggioStruct as $key => $value) {
                $messaggio .= $value['@textNode'] . " ";
            }
        }
        return $messaggio;
    }

    public function getContentFileName() {
        if ($this->arrContentFiles) {
            return reset($this->arrContentFiles);
        } else {
            return false;
        }
    }

    public function getInfoSummary() {
        $summary = array();
        $fileName = $this->xmlStructResult['fileSignedInfo']['fileName']['@textNode'];
        $contentFileName = $this->xmlStructResult['fileSignedInfo']['contentFileName']['@textNode'];
        $fullFileName = $this->xmlStructResult['fileSignedInfo']['fullFileName']['@textNode'];
        if (!isset($this->xmlStructResult['allSigners']['subjectSigners']['subjectSigner'][0])) {
            $subjectSigner = array();
            $subjectSigner[] = $this->xmlStructResult['allSigners']['subjectSigners']['subjectSigner'];
        } else {
            $subjectSigner = $this->xmlStructResult['allSigners']['subjectSigners']['subjectSigner'];
        }
        foreach ($subjectSigner as $key => $signer) {
            $summaryRow = array();
            $summaryRow['fileName'] = $fileName;
            $summaryRow['contentFileName'] = $contentFileName;
            $summaryRow['fullFileName'] = $fullFileName;
            if ($signer['isSignerVerifyPassed']['@textNode'] == 'true') {
                $summaryRow['isSignerVerifyPassed'] = true;
                $summaryRow['isSignerVerifyPassedHtml'] = '<span class="ita-icon ita-icon-shield-green-32x32">&nbsp;</span>';
            } else {
                $summaryRow['isSignerVerifyPassed'] = false;
                $summaryRow['isSignerVerifyPassedHtml'] = '<span class="ita-icon ita-icon-shield-red-32x32">&nbsp;</span>';
            }
            $summaryRow['signer'] = $signer['firstName']['@textNode'] . " " . $signer['lastName']['@textNode'];
            $issuer = $this->parseLdapDn($signer['issuer']['@textNode']);
            $summaryRow['issuer_CN'] = $issuer['CN'][0];
            $summaryRow['fiscalCode'] = $signer['fiscalCode']['@textNode'];
            $summaryRow['role'] = $signer['role']['@textNode'];
            $subjectDN = $this->parseLdapDn($signer['subjectDN']['@textNode']);
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
            $summaryRow['messageErrorSigner'] = $this->getMessageErrorSignersAsString($signer);
            $summaryRow['dataFirma'] = null;
            
            $summary[] = $summaryRow;
        }
        return $summary;
    }

    public function getContentSHA($file = '') {
        if ($file) {
            return $this->contentSHA[$file];
        } else {
            return reset($this->contentSHA);
        }
    }

    private function verifyPath($path) {
        if (!is_dir($path)) {
            return false;
        }
        $dh = @opendir($path);
        if ($dh === false) {
            return false;
        }
        closedir($dh);
        return true;
    }

}

?>