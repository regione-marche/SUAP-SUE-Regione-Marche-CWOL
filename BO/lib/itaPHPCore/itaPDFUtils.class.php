<?php

/**
 *
 * Utils per la manipolazione dei pdf
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    itaPHPCore
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * @version    31.07.2018

 */
class itaPDFUtils {

    const DEFAULT_POSITION_X = 0;
    const DEFAULT_POSITION_Y = 0;
    const DEFAULT_CANVAS_WIDTH = 590;
    const DEFAULT_CANVAS_HEIGHT = 800;
    const DEFAULT_FONT_SIZE = 10;
    const DEFAULT_FONT_COLOR = "0,0,0";
    const DEFAULT_LINE_SPACING = 1;
    const DEFAULT_ROTATION = 0;
    const DEFAULT_FIRST_PAGE_ONLY = 1;
    const DEFAULT_MARK_UNDER = 0;
    const DEFAULT_DELETE_CAT = 0;

    private $lastCommand;
    private $errCode;
    private $errMessage;
    private $risultato;

    public function getLastCommand() {
        return $this->lastCommand;
    }

    public function setLastCommand($lastCommand) {
        $this->lastCommand = $lastCommand;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    /**
     * Finalizza un pdf editabile (lo rende non piu editabile)
     * @param string $fileInputPath il path del pdf da finalizzare
     * @param boolean $deletePath true se si vuole cancellare il path di input in caso di esito positivo
     * @return boolean true se ok(su getRisultato si trova il path del file finalizzato), altrimenti false(su getMessaggioErrore il motivo)
     */
    public function finalizzaPdfEditabile($fileInputPath, $deletePath = false) {
        $toReturn = $this->execute("flatten", $fileInputPath);
        if ($toReturn && $deletePath) {
            unlink($fileInputPath);
        }
        return $toReturn;
    }

    /**
     * Converte un immagine in un pdf
     * @param array $inputFile  path immagine da convertire
     * @return boolean true se ok(su getRisultato si trova il path del file pdf convertito), altrimenti false(su getMessaggioErrore il motivo)
     */
    public function imgToPdf($inputFile) {
        return $this->execute("img2pdf", $inputFile);
    }

    /**
     * Sovrappone 2 o più pdf
     * @param array $inputFiles lista dei file da unire (path completo). Se si vuole cancellare automaticamente
     *              il file una volta letto, è possibile passare al posto del semplice path un array composto da 
     *              DELETE = 1/0, PATH=il path
     * @return boolean true se ok(su getRisultato si trova il path del file popolato), altrimenti false(su getMessaggioErrore il motivo)
     */
    public function sovrapponiPDF($inputFiles) {
        return $this->execute("overlay", $inputFiles);
    }

    /**
     * helper per marcare un pdf con un testo
     * 
     * @param type $fileInputPath   File pdf da Marcare 
     * @param type $string          Stringa di Marcatura
     * @param type $x_coord         coordinata x in punti
     * @param type $y_coord         coordinata y in punti   
     * @param type $rotation        rotazione in gradi in senso orario
     * @param type $font_size       dimensione font (da implementare)
     * @param type $firstpageonly   flag per marcatura solo della prima pagina
     * @param type $wSize           larghezza area di marcatura
     * @param type $hSize           altezza colonna di marcatura
     * @param type $lineSpacing     interlinea
     * @return boolean
     */
    public function marcaPDF($fileInputPath, $string, $x_coord, $y_coord, $rotation, $font_size = 0, $firstpageonly = null, $wSize = null, $hSize = null, $lineSpacing = null) {
        $fileOutputPath = itaLib::getPrivateUploadPath() . '/' . $this->getRandom() . '.pdf';

        if ($wSize == null && $hSize == null && $lineSpacing == null) {
            $taskData = array(
                'input' => $fileInputPath,
                'output' => $fileOutputPath,
                'string' => $string,
                'x-coord' => $x_coord,
                'y-coord' => $y_coord,
                'font-size' => $font_size,
                'rotation' => $rotation
            );
            $xmlStringNodes = $this->getTaskNodeWatermark($taskData);
        } else {
            $taskData = array(
                'input' => $fileInputPath,
                'output' => $fileOutputPath,
                'string' => $string,
                'x-coord' => $x_coord,
                'y-coord' => $y_coord,
                'font-size' => $font_size,
                'rotation' => $rotation,
                'firstpageonly' => $firstpageonly,
                'w-size' => $wSize,
                'h-size' => $hSize,
                'line-spacing' => $lineSpacing
            );
            $xmlStringNodes = $this->getTaskNodeText($taskData);
        }
        if ($this->executeXmlNodes($xmlStringNodes) === true) {
            $this->risultato = $fileOutputPath;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Popola gli acrofield di un pdf
     * @param string $fileInputPath pdf da popolare 
     * @param array $fields array con chiave = nome dell'acrofield e valore = valore da inserire (es.      
     *      array(
      'cognome' => array("VALUE" => "Rossini", 'READONLY' => true),
      'nome' => array("VALUE" => "Mario"),
      'sesso' => array("VALUE" => "M")
     *      )
     * @param boolean $deletePath true se si vuole cancellare il path di input in caso di esito positivo
     * @return boolean true se ok(su getRisultato si trova il path del file popolato), altrimenti false(su getMessaggioErrore il motivo)
     */
    public function popolaAcrofields($fileInputPath, $fields, $deletePath = false) {
        $toReturn = $this->execute("fill_form", $fileInputPath, $fields);
        if ($toReturn) {
            unset($fileInputPath);
        }
        return $toReturn;
    }

    /**
     * Unisce piu pdf in un'unico pdf
     * @param array $inputFiles lista dei file da unire (path completo). Se si vuole cancellare automaticamente
     *              il file una volta letto, è possibile passare al posto del semplice path un array composto da 
     *              DELETE = 1/0, PATH=il path
     * @return boolean true se ok(su getRisultato si trova il path del file popolato), altrimenti false(su getMessaggioErrore il motivo)
     */
    public function unisciPdf($inputFiles) {
        return $this->execute("cat", $inputFiles);
    }

    /**
     * Torna il numero di firme presenti nel pdf.
     * @param String $fileInputPdfPath path del pdf da verificare
     * @return boolean|int false/0 oppure n 
     */
    public function hasSignatures($fileInputPdfPath) {
        if ($this->execute("dump_data_fields", $fileInputPdfPath, null, '.info')) {
            $arrFields = $this->decodeFileInfo($this->risultato);
            unlink($this->risultato);
            $signatureCount = 0;
            foreach ($arrFields as $key => $field) {
                $retSearch = array_search('Signature', $field);
                if ($retSearch == 'type') {
                    $signatureCount ++;
                }
            }
            return $signatureCount;
        } else {
            return false;
        }
    }

    private function decodeFileInfo($fileInfo) {
        if (!file_exists($fileInfo)) {
            $this->errMessage = "Contenuto dei campi da analizzare non disponibile (file di appoggio non trovato).";
            return false;
        }
        $arrayDag = array();
        $arrayInfo = array();
        $arrayValue = array();
        $arrayField = array();
        $strInfo = file_get_contents($fileInfo);
        if ($strInfo) {
            $arrayInfo = explode('---', $strInfo);
            unset($arrayInfo[0]);
            foreach ($arrayInfo as $field) {
                $arrayField = explode(chr(10), $field);
                $keyValue = "";
                foreach ($arrayField as $value) {
                    $arrayValue = explode(':', $value);
                    if (trim($arrayValue[0]) == 'FieldName') {
                        $keyName = trim($arrayValue[1]);
                    }
                    if (trim($arrayValue[0]) == 'FieldValue') {
                        $keyValue = trim($arrayValue[1]);
                    }
                    if (trim($arrayValue[0]) == 'TabOrder') {
                        $tabOrder = trim($arrayValue[1]);
                    }
                    if (trim($arrayValue[0]) == 'FieldType') {
                        $keyType = trim($arrayValue[1]);
                    }
                }
                if ($keyName) {
                    $arrayDag[$keyName]['type'] = $keyType;
                    $arrayDag[$keyName]['value'] = $keyValue;
                    $arrayDag[$keyName]['taborder'] = $tabOrder;
                }
            }
            uasort($arrayDag, array($this, 'sortInfoByTabOrder'));
            return $arrayDag;
        }
    }

    // chiamato da usort
    private function sortInfoByTabOrder($a, $b) {
        if ($a['taborder'] == $b['taborder']) {
            return 0;
        }
        return ($a['taborder'] < $b['taborder']) ? -1 : 1;
    }

    /**
     * 
     * @param string $xmlStringNodes Stringa di singoli nodi xml reperita grazie a getTaskNode...()
     *                               Non inserire il tag iniziale e finale di root
     * @return boolean
     */
    public function executeXmlNodes($xmlStringNodes) {
        $this->errMessage = '';
        $this->risultato = '';

        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                $this->errMessage = "Creazione ambiente di lavoro temporaneo fallita";
                return false;
            }
        }

        $xmlTmp = itaLib::getPrivateUploadPath() . '/' . $this->getRandom() . '.xml';

        $file = fopen($xmlTmp, "w+");
        if (!file_exists($xmlTmp)) {
            $this->errMessage = "Errore apertura xml temporaneo";
            return false;
        }
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
        $xml .= "<root>\r\n";
        $xml .= $xmlStringNodes;
        $xml .= "</root>";
        if (fwrite($file, $xml) === false) {
            $this->errMessage = "Errore nella scrittura dell'xml temporaneo";
            return false;
        }
        if (fclose($file) === false) {
            $this->errMessage = "Errore nella chiusura dell'xml temporaneo";
            return false;
        }

        $this->lastCommand = $this->getCommand($xmlTmp);


        exec($this->lastCommand, $ret);
        //unlink($xmlTmp);

        foreach ($ret as $value) {
            $arrayExec = explode("|", $value);
            if ($arrayExec[1] == 00 && $arrayExec[0] == "OK") {
                return true;
            } else if ($arrayExec[0] == "KO") {
                $this->errMessage = $arrayExec[2];
                return false;
            }
        }

        $this->errMessage = "Errore interno";
        return false;
    }

    private function execute($taskName, $inputPath, $fields = array(), $outputFilePathExt = '.pdf') {
        $this->errMessage = '';
        $this->risultato = '';

        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                $this->errMessage = "Creazione ambiente di lavoro temporaneo fallita";
                return false;
            }
        }

        $xmlTmp = itaLib::getPrivateUploadPath() . '/' . $this->getRandom() . '.xml';

        $file = fopen($xmlTmp, "w+");
        if (!file_exists($xmlTmp)) {
            $this->errMessage = "Errore apertura xml temporaneo";
            return false;
        }

        $fileOutputPath = itaLib::getPrivateUploadPath() . '/' . $this->getRandom() . $outputFilePathExt;

        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
        $xml .= "<root>\r\n";
        $xml .= "<task name=\"" . $taskName . "\">\r\n";
        if ($inputPath) {
            if (is_array($inputPath)) {
                $xml .= "<inputs>\n";

                foreach ($inputPath as $path) {
                    if (is_array($path)) {
                        $delete = $path['DELETE'] ? 1 : 0;
                        $path = $path['PATH'];
                    } else {
                        $delete = 0;
                        $path = $path;
                    }

                    $xml .= "<input delete=\"" . $delete . "\" >" . $path . "</input>\n";
                }
                $xml .= "</inputs>\n";
            } else {
                $xml .= "<input>$inputPath</input>\r\n";
            }
        }

        $xml .= "<output>$fileOutputPath</output>\r\n";

        if ($fields) {
            $xml .= "<fields>\r\n";
            foreach ($fields as $fieldName => $fieldValue) {
                $readonly = false;
                $value = $fieldValue;
                if (is_array($fieldValue)) {
                    $readonly = $fieldValue['READONLY'];
                    $value = $fieldValue['VALUE'];
                }

                $xml .= "<field>\r\n";
                $xml .= "<name><![CDATA[" . $fieldName . "]]></name>\r\n";
                $xml .= "<value><![CDATA[" . $value . "]]></value>\r\n";
                $xml .= "<properties>\r\n";
                if ($readonly == 1) {
                    $xml .= "<property name=\"READ_ONLY\">1</property>\r\n";
                }
                $xml .= "</properties>\r\n";
                $xml .= "</field>\r\n";
            }

            $xml .= "</fields>\r\n";
        }

        $xml .= "</task>\r\n";
        $xml .= "</root>";

        if (fwrite($file, $xml) === false) {
            $this->errMessage = "Errore nella scrittura dell'xml temporaneo";
            return false;
        }
        if (fclose($file) === false) {
            $this->errMessage = "Errore nella chiusura dell'xml temporaneo";
            return false;
        }

        $command = '"' . App::getConf("Java.JVMPath") . '"' . " -jar " . ITA_LIB_PATH . '/itaJava/itaJPDF2/itaJPDF.jar ' . $xmlTmp;
        exec($command, $ret);

        unlink($xmlTmp);

        foreach ($ret as $value) {
            $arrayExec = explode("|", $value);
            if ($arrayExec[1] == 00 && $arrayExec[0] == "OK") {
                $this->risultato = $fileOutputPath;
                return true;
            } else if ($arrayExec[0] == "KO") {
                $this->errMessage = $arrayExec[2];
                return false;
            }
        }

        $this->errMessage = "Errore interno";
        return false;
    }

    private function getRandom() {
        return time() . uniqid("RAND");
    }

    public function getMessaggioErrore() {
        return $this->errMessage;
    }

    public function getRisultato() {
        return $this->risultato;
    }

    /**
     * restituisce un nodo stringa xml per l'esecuzione di un tasl di marcatira pdf
     * @param array $taskData   lista di parametri per l'esecuzionde del task pdf
     *                          'input' =>           'path file input'  
     *                          'output' =>          'path file output'
     *                          'string' =>          'stringa di marcatura'
     *                          'x-coord' =>         'coordinata x in punti' 
     *                          'y-coord' =>         'coordinata y in punti' 
     *                          'font-size' =>       'dimensione del font'
     *                          'font-color' =>      'colore del font (r,g,b)'
     *                          'rotation' =>        'rotazione in gradi nel senso orario' 
     *                          'firstpageonly' =>   'marca solo la prima pagina' 
     *                          'markunder' =>       'marca in background' 
     * @return string
     */
    public function getTaskNodeWatermark($taskData) {
        if (empty($taskData) || !is_array($taskData)) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Dati del task non impostati');
        }
        if (empty($taskData['input']) || !file_exists($taskData['input'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Path file di input non trovato');
        }
        if (empty($taskData['output'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Path di output non impostato');
        }
        if (!isSet($taskData['string'])) {
            $taskData['string'] = '';
        }
        if (!isSet($taskData['x-coord'])) {
            $taskData['x-coord'] = self::DEFAULT_POSITION_X;
        }
        if (!isSet($taskData['y-coord'])) {
            $taskData['y-coord'] = self::DEFAULT_POSITION_Y;
        }
        if (!isSet($taskData['rotation'])) {
            $taskData['rotation'] = self::DEFAULT_ROTATION;
        }
        if (!isSet($taskData['font-color'])) {
            $taskData['font-color'] = self::DEFAULT_FONT_COLOR;
        }
        if (!isSet($taskData['firstpageonly'])) {
            $taskData['firstpageonly'] = self::DEFAULT_FIRST_PAGE_ONLY;
        }
        if (!isSet($taskData['markunder'])) {
            $taskData['markunder'] = self::DEFAULT_MARK_UNDER;
        }
        $node = "<task name=\"watermark\">\r\n";
        $node .= "<input>{$taskData['input']}</input>\r\n";
        $node .= "<output>{$taskData['output']}</output>\r\n";
        $node .= "<string>{$taskData['string']}</string>\r\n";
        $node .= "<x-coord>{$taskData['x-coord']}</x-coord>\r\n";
        $node .= "<y-coord>{$taskData['y-coord']}</y-coord>\r\n";
        $node .= "<font-size>{$taskData['font-size']}</font-size>\r\n";
        $node .= "<font-color>{$taskData['font-color']}</font-color>\r\n";
        $node .= "<rotation>{$taskData['rotation']}</rotation>\r\n";
        $node .= "<firstpageonly>{$taskData['firstpageonly']}</firstpageonly>\r\n";
        $node .= "<markunder>{$taskData['markunder']}</markunder>\r\n";
        $node .= "</task>\r\n";
        return $node;
    }

    /**
     * Restituisce un nodo stringa xml per l'esecuzione di un task di aggiunta testo pdf
     * @param array $taskData Lista dei parametri:
     *                          'input' =>           'path file input'  
     *                          'output' =>          'path file output'
     *                          'string' =>          'stringa di marcatura'
     *                          'x-coord' =>         'coordinata x in punti' 
     *                          'y-coord' =>         'coordinata y in punti' 
     *                          'w-size' =>          'Dimensione orizzontale del contenitore del testo'
     *                          'h-size' =>          'Dimensione verticale del contenitore del testo'
     *                          'line-spacing' =>    'Interlinea'
     *                          'font-size' =>        'dimensione del font (da implementare ancora)'
     *                          'rotation' =>        'rotazione in gradi nel senso orario' 
     *                          'firstpageonly' =>   'marca solo la prima pagina' 
     * @return string
     * @throws type
     */
    public function getTaskNodeText($taskData) {
        if (empty($taskData) || !is_array($taskData)) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Dati del task non impostati');
        }
        if (empty($taskData['input']) || !file_exists($taskData['input'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Path file di input non trovato');
        }
        if (empty($taskData['output'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Path di output non impostato');
        }
        if (!isSet($taskData['string'])) {
            $taskData['string'] = '';
        }
        if (!isSet($taskData['x-coord'])) {
            $taskData['x-coord'] = self::DEFAULT_POSITION_X;
        }
        if (!isSet($taskData['y-coord'])) {
            $taskData['y-coord'] = self::DEFAULT_POSITION_Y;
        }
        if (empty($taskData['w-size'])) {
            $taskData['w-size'] = self::DEFAULT_CANVAS_WIDTH - $taskData['x-coord'];
        }
        if (empty($taskData['h-size'])) {
            $taskData['h-size'] = self::DEFAULT_CANVAS_HEIGHT - $taskData['y-coord'];
        }
        if (empty($taskData['font-size'])) {
            $taskData['font-size'] = self::DEFAULT_FONT_SIZE;
        }
        if (empty($taskData['line-spacing'])) {
            $taskData['line-spacing'] = self::DEFAULT_FONT_SIZE + self::DEFAULT_LINE_SPACING;
        }
        if (!isSet($taskData['rotation'])) {
            $taskData['rotation'] = self::DEFAULT_ROTATION;
        }
        if (!isSet($taskData['firstpageonly'])) {
            $taskData['firstpageonly'] = self::DEFAULT_FIRST_PAGE_ONLY;
        }

        $node = "<task name=\"text\">\r\n";
        $node .= "<input>{$taskData['input']}</input>\r\n";
        $node .= "<output>{$taskData['output']}</output>\r\n";
        $node .= "<string>{$taskData['string']}</string>\r\n";
        $node .= "<x-coord>{$taskData['x-coord']}</x-coord>\r\n";
        $node .= "<y-coord>{$taskData['y-coord']}</y-coord>\r\n";
        $node .= "<w-size>{$taskData['w-size']}</w-size>\r\n";
        $node .= "<h-size>{$taskData['h-size']}</h-size>\r\n";
        $node .= "<font-size>{$taskData['font-size']}</font-size>\r\n";
        $node .= "<line-spacing>{$taskData['line-spacing']}</line-spacing>\r\n";
        $node .= "<rotation>{$taskData['rotation']}</rotation>\r\n";
        $node .= "<firstpageonly>{$taskData['firstpageonly']}</firstpageonly>\r\n";
        $node .= "</task>\r\n";
        return $node;
    }

    /**
     * Esegue il task watermark di itaJPDF2
     * 
     * sovrappone / sottopone una stringa di testo al PDF
     * 
     * @param array $taskData   lista di parametri per l'esecuzionde del task pdf
     *                          'input' =>           'path file input'  
     *                          'output' =>          'path file output'
     *                          'string' =>          'stringa di marcatura'
     *                          'x-coord' =>         'coordinata x in punti' 
     *                          'y-coord' =>         'coordinata y in punti' 
     *                          'font-size' =>       'dimensione del font'
     *                          'font-color' =>      'colore del font (r,g,b)'
     *                          'rotation' =>        'rotazione in gradi nel senso orario' 
     *                          'firstpageonly' =>   'marca solo la prima pagina' 
     *                          'markunder' =>       'marca in background' 
     * @return string
     */
    public function executeTaskWaterMark($taskData) {
        $xmlStringNodes = $this->getTaskNodeWatermark($taskData);
        if ($this->executeXmlNodes($xmlStringNodes) === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Esegue il task text di itaJPDF2
     * 
     * sovrappone una area di testo al PDF
     * 
     * @param array $taskData array di parametri per l'esecuzione del task
     *  $taskData = [
     *      'input' =>          (string)    $fileInputPath.
     *      'output' =>         (string)    $fileOutputPath.
     *      'string' =>         (string)    $string.
     *      'x-coord' =>        (integer)   $x_coord.
     *      'y-coord' =>        (integer)   $y_coord.
     *      'font-size' =>       (float)     $font_size.
     *      'w-size' =>         (integer)   $wSize.
     *      'h-size' =>         (string)    $hSize,
     *      'line-spacing' =>   (string)    $lineSpacing
     *  ]
     * @return boolean
     */
    public function executeTaskText($taskData) {
        $xmlStringNodes = $this->getTaskNodeText($taskData);
        if ($this->executeXmlNodes($xmlStringNodes) === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Esegue il task Cat di itaJPDF2
     * 
     * Concatena più pdf in un unico pdf.
     * 
     * @param array $taskData   lista di parametri per l'esecuzionde del task pdf
     * $taskData = [
     *  'inputs' => array(
     *      'pathfile' =>       'path file ' 
     *      'delete' =>     '1 o 0 per indicare se cancellare il file una volta concatenato' 
     *  ) 
     *  output' =>          'path file output'
     * 
     * @return string
     */
    public function executeTaskCat($taskData) {
        $xmlStringNodes = $this->getTaskNodeCat($taskData);
        if ($this->executeXmlNodes($xmlStringNodes) === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * restituisce un nodo stringa xml per l'esecuzione di un task di marcatira pdf
     * @param array $taskData   lista di parametri per l'esecuzionde del task pdf
     *                         'inputs' =>           'array con i file in input da concatenare'
     *                                      array(
     *                                          'pathfile' =>       'path file in input' 
     *                                          'delete' =>     '1 o 0 per indicare se cancellare il file una volta concatenato' 
     *                                      ) 
     *                          'output' =>          'path file output'
     * @return string
     */
    public function getTaskNodeCat($taskData) {
        if (empty($taskData) || !is_array($taskData)) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Dati del task non impostati');
        }
        if (empty($taskData['inputs']) || !is_array($taskData['inputs'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Elenco inputs del task non impostati');
        }
        if (empty($taskData['output'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Path di output non impostato');
        }
        /* Controllo dei file in input */
        foreach ($taskData['inputs'] as $key => $input) {
            if (!file_exists($input['pathfile'])) {
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'File input del task non trovato');
            }
            if (!isSet($input['delete'])) {
                $taskData[$key]['delete'] = self::DEFAULT_DELETE_CAT;
            }
        }
        $node = "<task name=\"cat\">\r\n";
        $node .= "<inputs>\r\n";
        foreach ($taskData['inputs'] as $input) {
            $node.="<input delete=\"{$input['delete']}\">" . $input['pathfile'] . "</input>\r\n";
        }
        $node .= "</inputs>\r\n";
        $node .= "<output>{$taskData['output']}</output>\r\n";
        $node .= "</task>\r\n";
        return $node;
    }

    private function getCommand($xmlTmp) {
        return '"' . App::getConf("Java.JVMPath") . '"' . " -jar " . ITA_LIB_PATH . '/itaJava/itaJPDF2/itaJPDF.jar ' . $xmlTmp;
    }

}
