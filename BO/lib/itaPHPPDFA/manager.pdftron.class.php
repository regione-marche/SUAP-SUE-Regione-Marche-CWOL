<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaPDFA
 *
 * @author michele
 */
require_once(ITA_LIB_PATH . '/itaPHPPDFA/manager.class.php');

class manager_pdftron extends manager {

    function __construct() {
        parent::__construct();
        if (file_exists(dirname(__FILE__) . "/config/Config.pdftron.php")) {
            require_once(dirname(__FILE__) . "/config/Config.pdftron.php");
            if (is_dir(PDFTRON_PDFAMANAGER_PATH)) {
                $this->managerPath = PDFTRON_PDFAMANAGER_PATH;
            }
        }
        if (!$this->managerPath) {
            if (is_dir(dirname(__FILE__) . "/pdfa")){
                $this->managerPath = dirname(__FILE__) . "/pdfa";
            }
        }
        if (!$this->managerPath){
            $ex = new Exception("Librerie PDFTron PDF/A Manager Sconosciute");
            throw $ex;
        }
        
        if (!file_exists($this->managerPath . "/pdfa.lic")){
            $ex = new Exception("Licenza PDFTron PDF/A non inserita");
            throw $ex;
        }
    }

    function getManagerType() {
        return "pdftron";
    }

    function getManagerVersion() {
        $command = $this->managerPath . "/pdfa -v";
        $retArr = array();
        exec($command, $retArr, $this->lastExitCode);
        return implode("<br>", $retArr);
    }

    private function parsePfdTronOutput($outputArr, $inputName, $outputName = '') {
        $retArr = array(
            "VALIDATION-STATUS" => "",
            "VALIDATION-MESSAGE" => "",
            "CONVERSION-STATUS" => "",
            "CONVERSION-MESSAGE" => ""
        );

        if ($inputName) {
            $inputName = pathinfo($inputName, PATHINFO_FILENAME) . "." . pathinfo($inputName, PATHINFO_EXTENSION);
            foreach ($outputArr as $key => $value) {
                if (strpos($value, "VLD-[FAIL]: ") == 0) {
                    $failItems = explode("VLD-[FAIL]: ", $value);
                    $failFile = $failItems[1];
                    if ($failFile == $inputName) {
                        $retArr["VALIDATION-STATUS"] = 'FAIL';
                        break;
                    }
                } else if (strpos($value, "VLD-[PASS]: ") == 0) {
                    $failItems = explode("VLD-[PASS]: ", $value);
                    $failFile = $failItems[1];
                    if ($failFile == $inputName) {
                        $retArr["VALIDATION-STATUS"] = 'PASS';
                        break;
                    }
                }
            }
        }

        if ($outputName) {
            foreach ($outputArr as $key => $value) {
                if (strpos($value, "CNV-[FAIL]: ") == 0) {
                    $failItems = explode("CNV-[FAIL]: ", $value);
                    $failFile = $failItems[1];
                    if ($failFile == $outputName) {
                        $retArr["CONVERSION-STATUS"] = 'FAIL';
                        break;
                    }
                } else if (strpos($value, "CNV-[PASS]: ") == 0) {
                    $failItems = explode("CNV-[PASS]: ", $value);
                    $failFile = $failItems[1];
                    if ($failFile == $outputName) {
                        $retArr["CONVERSION-STATUS"] = 'PASS';
                        break;
                    }
                }
            }
        }


        return $retArr;
    }

    public function verifyPDFSimple($fileName, $verbose = 0, $level = "A") {
        $workDir = $this->getWorkDir();
        if (!$workDir) {
            $this->lastExitCode = -1;
            $this->lastMessage = "Impossibile determinare la cartella di lavoro!";
            return $this->lastExitCode;
        }
        if ($fileName) {
            switch ($level) {
                case "A" :
                    $levelFlag=" -l A";
                    break;
                case "B" :
                    $levelFlag=" -l B";
                    break;
                default:
                    $levelFlag=" -l A";
                    break;
            }
            
            $command = $this->managerPath . "/pdfa --verb $verbose $levelFlag --noxml \"".$fileName."\"";
            $retArr = array();

            exec($command, $retArr, $this->lastExitCode);
            $this->lastOutput = $retArr;
            if ($this->lastExitCode == 0) {
                $outputStatus = $this->parsePfdTronOutput($this->lastOutput, $fileName);
                if ($outputStatus['VALIDATION-STATUS'] == "FAIL") {
                    $this->deleteWorkDir();
                    $this->lastExitCode = -5;
                    $this->lastMessage = "Validazione in formato PDF/A non riuscita!";
                    return $this->lastExitCode;
                }
                $this->deleteWorkDir();
            } else {
                $this->lastMessage = implode("<br>", $this->lastOutput);
            }
            return $this->lastExitCode;
        } else {
            $this->lastExitCode = -99;
            $this->lastOutput = "File mancante";
            return $this->lastExitCode;            
        }
    }

    public function convertPDF($fileName, $outputFile, $verbose = 0,  $level = "A") {
        $workDir = $this->getWorkDir();
        if (!$workDir) {
            $this->lastExitCode = -1;
            $this->lastMessage = "Impossibile determinare la cartella di lavoro!";
            return $this->lastExitCode;
        }
        if ($fileName && $outputFile) {
            $outDir = pathinfo($outputFile, PATHINFO_DIRNAME);
            $outFileName = pathinfo($outputFile, PATHINFO_BASENAME);
            //$outXMLName = pathinfo($outputFile, PATHINFO_FILENAME) . '.xml';
            //$outXSLName = pathinfo($outputFile, PATHINFO_FILENAME) . '.xsl';
            $tmpOutFileName = pathinfo($fileName, PATHINFO_FILENAME) . '_pdfa.' . pathinfo($fileName, PATHINFO_EXTENSION);
            switch ($level) {
                case "A" :
                    $levelFlag=" -l A";
                    break;
                case "B" :
                    $levelFlag=" -l B";
                    break;
                default:
                    $levelFlag=" -l A";
                    break;
            }
            $command = $this->managerPath . "/pdfa --noxml -o \"$workDir\" --convert --verb $verbose $levelFlag -f \"$tmpOutFileName\" \"$fileName\"";

            $retArr = array();
            exec($command, $retArr, $this->lastExitCode);
            $this->lastOutput = $retArr;
            if ($this->lastExitCode == 0) {
                if (file_exists($workDir . "/" . $tmpOutFileName)) {
                    $outputStatus = $this->parsePfdTronOutput($this->lastOutput, $fileName, $tmpOutFileName);
                    if ($outputStatus['CONVERSION-STATUS'] == "FAIL") {
                        $this->deleteWorkDir();
                        $this->lastExitCode = -4;
                        $this->lastMessage = "Conversione in formato PDF/A non riuscita!";
                        return $this->lastExitCode;
                    }
                    //                    
                    // metti i controlli su copy
                    //                    
                    if (!@copy($workDir . "/" . $tmpOutFileName, $outDir . "/" . $outFileName)) {
                        $this->deleteWorkDir();
                        $this->lastExitCode = -3;
                        $this->lastMessage = "Copia file convertito da ambiente temporaneo fallita!";
                        return $this->lastExitCode;
                    }
//
//                    Solo se userò output xml per ora no
//                    -------------------------------------------------------------
//                    copy($workDir . "/report.xml", $outDir . "/" . $outXMLName);
//                    copy($workDir . "/report.xsl", $outDir . "/" . $outXSLName);
//                    
                    $this->deleteWorkDir();
                } else {
                    $this->deleteWorkDir();
                    $this->lastExitCode = -2;
                    $this->lastMessage = "File risultato mancante Conversione Fallita!";
                    return $this->lastExitCode;
                }
            } else {
                $this->lastMessage = implode("<br>", $this->lastOutput);
            }
            return $this->lastExitCode;
        } else {
            $this->lastExitCode = -99;
            $this->lastMessage = "File mancante";
        }
    }

}

?>
