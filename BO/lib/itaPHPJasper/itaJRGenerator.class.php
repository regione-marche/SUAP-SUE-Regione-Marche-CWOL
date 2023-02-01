<?php

/**
 * Description of itaJRGeneratorclass
 *
 * @author michele
 */
class itaJRGenerator {

    const DEFINITION_STATE_NEW = -1;
    const DEFINITION_STATE_OPEN = 1;
    const DEFINITION_STATE_CLOSE = 2;

    private $definitionFile;
    private $definitionHandler;
    private $definitionID;
    private $definitionState;
    private $errMessage;
    private $errCode;

    public static function getInstance() {
        try {
            $jrObj = new itaJRGenerator();
            $jrObj->setDefinitionID();
            $jrObj->setDefinitionFile();
            $jrObj->definitionState = self::DEFINITION_STATE_NEW;
        } catch (Exception $exc) {
            return false;
        }
        return $jrObj;
    }

    private function setDefinitionID() {
        $this->definitionID = md5(rand() * microtime());
    }

    /**
     * Imposta il nome del file delle definizioni reports
     * 
     * @param string $definitionFile Indica il file dove salvare il file di definizione se omesso sarà auto-gestito
     * @return boolean
     */
    public function setDefinitionFile($definitionFile = '') {
        if ($definitionFile) {
            $this->definitionFile = $definitionFile;
        } else {
            $tmpPath = itaLib::createAppsTempPath('jrDefinition_' . $this->definitionID);
            if (!$tmpPath) {
                return false;
            }
            $xmlJrDefPath = $tmpPath . "/xmljrdef_" . $this->definitionID . ".xml";
            $this->definitionFile = $xmlJrDefPath;
        }
        return true;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    private function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    private function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    /**
     * Apre il file xml delle definizioni reports
     *
     * @return boolean
     *
     */
    public function openDefinitions() {
        $this->definitionHandler = fopen($this->definitionFile, 'w');
        if ($this->definitionHandler === false) {
            $this->definitionHandler = null;
            $this->setErrCode(-1);
            $this->setErrMessage('Errore nella apertura del File di definizione.');
            return false;
        }

        if (fwrite($this->definitionHandler, "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n") === false) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore nella scrittura apertura definizione.');
            return false;
        }
        if (fwrite($this->definitionHandler, "<root>\n") === false) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore nella scrittura file root tag: <root>');
            return false;
        }
        $this->definitionState = self::DEFINITION_STATE_OPEN;
        return true;
    }

    /**
     *
     * @param string $definition Indica il nodo xml di definizione da aggiungere nel file in formato stringa.
     * @return boolean
     */
    public function addDefinitionAsString($definition) {
        App::log('add definition');
        App::log($definition);
        if (fwrite($this->definitionHandler, $definition) === false) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore nella scrittura della definizione direport file.');
            return false;
        }

        return true;
    }

    /**
     * Chiude il file xml delle definizioni reports
     *
     * @return boolean
     */
    public function closeDefinitions() {
        if (fwrite($this->definitionHandler, "</root>\n") === false) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore nella scrittura del file: </root>.');
            return false;
        }
        if (fclose($this->definitionHandler) === false) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore nella chiusura del file.');
            return false;
        }
        $this->definitionHandler = null;
        $this->definitionState = self::DEFINITION_STATE_CLOSE;
        return true;
    }

    /**
     * Lancia l'applicativo java che interpreta le definizioni e crea reports con jasperReports
     *
     * @param string $stdOutFile indica il file o dispositivo dove salvare l'output dell'applicativo java
     * @return boolean
     *
     */
    public function runDefinitions($stdOutFile = '') {
        if (!$this->definitionFile) {
            $this->setErrCode(-1);
            $this->setErrMessage("File definizioni reports non assegnato.");
            return false;
        }
        if (!file_exists($this->definitionFile)) {
            $this->setErrCode(-1);
            $this->setErrMessage("File definizioni reports non esistente.");
            return false;
        }

        $stdOutDest = $stdOutFile ? " > $stdOutFile" : "";
        $commandJr = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJRGenerator/ItaJrGenerator.jar ' . $this->definitionFile . $stdOutDest;
        App::log($commandJr);
        exec($commandJr, $outJr, $retJr);
        if ($retJr != '0') {
            $jrErr = itaLib::getFileTail($stdOutDest, 2);
            $this->setErrCode(-1);
            $this->setErrMessage($jrErr);
            return false;
        }
        return true;
    }

    /**
     * Cancella il file delle definizioni e distattiva l'oggetto per i successivi utilizzi.
     *
     */
    public function clearDefinitions() {
        itaLib::deleteAppsTempPath('jrDefinition_' . $this->definitionID);
        $this->definitionFile = null;
        $this->definitionState = null;
    }

}

?>
