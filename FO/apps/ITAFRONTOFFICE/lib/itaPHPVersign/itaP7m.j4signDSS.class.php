<?php

/**
 * Description of itaP7mj4signDSS
 *
 * @author michele moscioni 
 */
require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');

class itaP7mj4signDSS extends itaP7m {

    private $file;
    private $tempPath;
    private $xmlReport;
    private $xmlReportStringResult;
    private $xmlReportStructResult;
    private $arrContentFiles;
    private $contentSHA;
    private $subLevelObj;

    const PAL_DSS_JAR = 'pal-j4sign.jar';
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

        $jarPath = ITA_LIB_PATH . '/java/itaJ4SignDSS/';
        $xmlReport = $outRPath . pathinfo($outFileName, PATHINFO_FILENAME) . ".xml";

        /*
         * Creao le path di lavoro
         */
        if (!mkdir($inputPath)) {
            return false;
        }
        if (!mkdir($outCPath)) {
            exit();

            return false;
        }
        if (!mkdir($outRPath)) {
            return false;
        }

        /*
         * Copio il file da analizzare
         */
        if (!copy($Filep7m, $inputFile)) {
            return false;
        }
        /*
         * Creo lastringa di comando
         */

        $jarPath = ITA_LIB_PATH . '/java/itaJ4SignDSS/';
        $confPath = ITA_LIB_PATH . '/java/itaJ4SignDSS/conf/logging.properties';
        $lastdir = getcwd();
        chdir($jarPath);
        $command = ITA_FRONTOFFICE_JVM8_PATH . " -Djava.util.logging.config.file=$confPath -jar $jarPath" . self::PAL_DSS_JAR;
        $command .= ' ' . self::PAL_DSS_OPT_VERIFY;
        $command .= ' "' . $inputFile . "\"";
        $command .= ' ' . self::PAL_DSS_VALIDATION_LEVEL_ARCHIVAL_DATA;
        $command .= ' ' . $outCPath;
        $command .= ' ' . $outRPath;
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

        if (file_exists($xmlReport)) {
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

        $obj->xmlReportStringResult = $xmlReportStringResult;
        $obj->xmlReportStructResult = $xmlReportArr;

        $obj->arrContentFiles = $arrContentFiles;
        foreach ($arrContentFiles as $key => $contentFile) {
            $obj->contentSHA[pathinfo($contentFile, PATHINFO_BASENAME)] = sha1_file($contentFile);
        }

        if (strtolower(pathinfo($obj->getContentFileName(), PATHINFO_EXTENSION)) == 'p7m') {
            $obj->subLevelObj = itaP7m::getP7mInstance($obj->getContentFileName(), 'j4sign-DSS');
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
        if (is_a($this->subLevelObj, 'itaP7mj4signDSS')) {
            $this->subLevelObj->cleanData();
        }
        return self::deleteDirRecursive($this->getTempPath());
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
        foreach ($this->xmlReportStructResult['verificaRisultati'] as $verificaRisultatiNodo) {
            $retMessaggio .= " " . $this->getMessageErrorSignersAsString($verificaRisultatiNodo);
        }
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

        public function getCertificateSummary() {
        $summary = array();
        foreach ($this->xmlReportStructResult['verificaRisultati'] as $key => $verificaRisultatiNodo) {
            $CMSCertificates = $verificaRisultatiNodo['CMSCertificates'][0]['CMSCertificate'];
            foreach ($CMSCertificates as $CMSCertificate) {
                $summaryRow = array();
                $subject = $this->parseLdapDn($CMSCertificate['firmatario'][0]['@textNode']);
                $fiscalCode = $subject['SERIALNUMBER'][0];
                if (in_array($fiscalCode, array_column($summary, 'fiscalCode'))) {
                    continue;
                }

                $summaryRow['signer'] = $subject['CN'][0];

                $notBefore = DateTime::createFromFormat('d/m/Y H:i:s', $CMSCertificate['certificatoValidoDa'][0]['@textNode']);
                $notAfter = DateTime::createFromFormat('d/m/Y H:i:s', $CMSCertificate['certificatoValidoA'][0]['@textNode']);
                $summaryRow['NotBefore'] = ($notBefore) ? $notBefore->format('Ymd H:i:s') : '';
                $summaryRow['NotAfter'] = ($notAfter) ? $notAfter->format('Ymd H:i:s') : '';

                $issuer = $this->parseLdapDn($CMSCertificate['certificatore'][0]['@textNode']);
                $summaryRow['issuer_CN'] = $issuer['CN'][0];

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

                $summary[] = $summaryRow;
            }
        }
        if (is_a($this->subLevelObj, 'itaP7mj4signDSS')) {
            $summary = array_merge($summary, $this->subLevelObj->getCertificateSummary());
        }
        
        return $summary;
    }

    
    public function getInfoSummary() {
        $summary = array();
        $fileName = pathinfo($this->file, PATHINFO_FILENAME);
        $contentFileName = $this->ContentFileName;
        $fullFileName = $this->file;

        foreach ($this->xmlReportStructResult['verificaRisultati'] as $verificaRisultatiNodo) {
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


            $notBefore = DateTime::createFromFormat('d/M/Y H:i:s', $verificaRisultatiNodo['certificatoValidoDa'][0]['@textNode']);
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

            $summaryRow['messageErrorSigner'] = $this->getMessageErrorSignersAsString($verificaRisultatiNodo);
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
    
}
