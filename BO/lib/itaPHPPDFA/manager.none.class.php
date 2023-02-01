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

class manager_none extends manager {

    function __construct() {
        parent::__construct();
    }

    function getManagerType() {
        return "none";
    }

    function getManagerVersion() {
        return "none";
    }

    public function verifyPDFSimple($fileName, $verbose = 0, $level = "A") {
        $this->lastExitCode = 0;
        $this->lastOutput = "";
        $this->lastMessage = "";
        return $this->lastExitCode;
    }

    public function convertPDF($fileName, $outputFile, $verbose = 0, $level = "A") {
        $workDir = $this->getWorkDir();
        if (!$workDir) {
            $this->lastExitCode = -1;
            $this->lastMessage = "Impossibile determinare la cartella di lavoro!";
            return $this->lastExitCode;
        }
        if ($fileName && $outputFile) {
            $outDir = pathinfo($outputFile, PATHINFO_DIRNAME);
            $outFileName = pathinfo($outputFile, PATHINFO_BASENAME);
            //                    
            // metti i controlli su copy
            //                    
            if (!@copy($fileName, $outDir . "/" . $outFileName)) {
                $this->deleteWorkDir();
                $this->lastExitCode = -3;
                $this->lastMessage = "Copia file convertito da ambiente temporaneo fallita!";
                return $this->lastExitCode;
            }
            $this->deleteWorkDir();
        } else {
            $this->lastExitCode = -99;
            $this->lastMessage = "File mancante";
        }
    }

}

?>
