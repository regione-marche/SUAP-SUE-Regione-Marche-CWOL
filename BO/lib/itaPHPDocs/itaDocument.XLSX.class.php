<?php

/**
 * 
 */
class itaDocumentXLSX {

    private $location;
    private $dictionary;
    private $returnCode;
    private $message;
    private $objPHPExcel;
    private $regexCellPosition = '/(?:(\w+)!)?([A-Z]{1,3}[\d]{1,7})/';

    public function getLocation() {
        return $this->location;
    }

    public function setLocation($location) {
        $this->location = $location;
    }

    public function getDictionary() {
        return $this->dictionary;
    }

    public function setDictionary($dictionary) {
        $this->dictionary = $dictionary;
    }

    public function getReturnCode() {
        return $this->returnCode;
    }

    public function setReturnCode($returnCode) {
        $this->returnCode = $returnCode;
    }

    public function getMessage() {
        return $this->message;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function loadContent($file, $cacheToDisk = false) {
        if (!file_exists($file)) {
            $this->setReturnCode('001');
            $this->setMessage(sprintf('Il documento "%s" non esiste', $file));
            return false;
        }

        if ($cacheToDisk) {
            $cacheSettings = array(
                'memoryCacheSize' => '50MB'
            );
            PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp, $cacheSettings);
        }

        $this->objPHPExcel = PHPExcel_IOFactory::load($file);
        $this->objPHPExcel->setActiveSheetIndex();

        $this->setLocation($file);
        return true;
    }

    public function saveContent(&$file, $overwrite = false) {
        if (file_exists($file) && !$overwrite) {
            $this->setReturnCode('004');
            $this->setMessage(sprintf('Il documento "%s" è già esistente', $file));
            return false;
        }

        $file = dirname($file) . '/' . basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION)) . '.xlsx';
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
        $objWriter->save($file);

        return true;
    }

    private function dictionary2cellsMap($dictionary) {
        $cellsMap = $matches = array();

        foreach ($dictionary as $k => $v) {
            if (is_array($v)) {
                $cellsMap = array_merge($cellsMap, $this->dictionary2cellsMap($v));
                continue;
            }

            if (!preg_match($this->regexCellPosition, $k, $matches)) {
                continue;
            }

            $cellsMap[] = array(
                'SHEET' => $matches[1],
                'CELL' => $matches[2],
                'VALUE' => $v
            );
        }

        return $cellsMap;
    }

    public function mergeDictionary() {
        if (!$this->objPHPExcel) {
            $this->setMessage('Il contenuto non è stato caricato');
            $this->setReturnCode('002');
            return false;
        }

        if (!$this->dictionary) {
            $this->setMessage('Il dizionario non è stato caricato');
            $this->setReturnCode('003');
            return false;
        }

        $cellsMap = $this->dictionary2cellsMap($this->dictionary);

        foreach ($cellsMap as $cellInfo) {
            /* @var $currentSheet PHPExcel_Worksheet */
            $currentSheet = $cellInfo['SHEET'] ? $this->objPHPExcel->getSheetByName($cellInfo['SHEET']) : $this->objPHPExcel->getSheet();

            if (!$currentSheet) {
                continue;
            }

            $currentSheet->getCell($cellInfo['CELL'])->setValue($cellInfo['VALUE']);
        }

        return true;
    }

    public function setVarContent($key, $value) {
        $matches = array();

        if (!$this->objPHPExcel) {
            $this->setMessage('Il contenuto non è stato caricato');
            $this->setReturnCode('005');
            return false;
        }

        if (!preg_match($this->regexCellPosition, $key, $matches)) {
            $this->setMessage('Posizione "' . $key . '" non valida');
            $this->setReturnCode('006');
            return false;
        }

        $sheet = $matches[1];
        $cell = $matches[2];

        /* @var $currentSheet PHPExcel_Worksheet */
        $currentSheet = $sheet ? $this->objPHPExcel->getSheetByName($sheet) : $this->objPHPExcel->getSheet();

        return $currentSheet->getCell($cell)->setValue($value);
    }

    public function getVarContent($key) {
        $matches = array();

        if (!$this->objPHPExcel) {
            $this->setMessage('Il contenuto non è stato caricato');
            $this->setReturnCode('007');
            return false;
        }

        if (!preg_match($this->regexCellPosition, $key, $matches)) {
            $this->setMessage('Posizione "' . $key . '" non valida');
            $this->setReturnCode('008');
            return false;
        }

        $sheet = $matches[1];
        $cell = $matches[2];

        /* @var $currentSheet PHPExcel_Worksheet */
        $currentSheet = $sheet ? $this->objPHPExcel->getSheetByName($sheet) : $this->objPHPExcel->getSheet();

        if (!$currentSheet) {
            $this->setMessage('Posizione "' . $key . '", foglio non trovato');
            $this->setReturnCode('009');
            return false;
        }

        /*
         * Modifica per formato 'Contabilità' che inserisce il simbolo '$'
         * invece di '¤'.
         */
        $numberFormat = $currentSheet->getStyle($cell)->getNumberFormat();
        if ($numberFormat->getBuiltInFormatCode() == 44) {
            $numberFormat->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }

        return $currentSheet->getCell($cell)->getFormattedValue();
    }

    /**
     * Torna il contenuto di una sheet
     * @param string $sheet Nome della sheet, se null torna la prima
     * @return object sheet
     */
    private function getSheetContent($sheet = null) {
        $currentSheet = $sheet ? $this->objPHPExcel->getSheetByName($sheet) : $this->objPHPExcel->getSheet();
        if (!$currentSheet) {
            $this->setMessage('Sheet "' . ($sheet ? $sheet : '') . '" non trovata');
            $this->setReturnCode('010');
            return false;
        }

        return $currentSheet;
    }

    /**
     * Torna la count delle row di una sheet
     * @param string $sheet Nome della sheet, se null torna la prima
     * @return int numero di row
     */
    public function getSheetRowsCount($sheet = null) {
        $currentSheet = $this->getSheetContent($sheet);
        return $currentSheet ? $currentSheet->getHighestRow() : false;
    }

    /**
     * Ritorna le row di una sheet, se $fromCell e $toCell sono vuoti torna tutte le righe, 
     * sennò filtra solo le row da - a (es. da A1 a B10 oppure solo 1 cella se si specifica 
     * solo uno tra $fromCell e $toCell)
     * 
     * @param string $sheet Nome della sheet, se null torna la prima.
     * @param boolean $formatData true=ritorna i dati formattati, false senza formattazione.
     * @param string $fromCell cella di inizio lettura (es. A1). 
     * @param string $toCell cella di fine lettura (es. B10).
     * @return array rows.
     */
    public function getSheetRowsArray($sheet = null, $formatData = false, $fromCell = '', $toCell = '') {
        $currentSheet = $this->getSheetContent($sheet);
        if (!$fromCell && !$toCell) {
            // se non ho specificato un range torno tutto
            return $currentSheet ? $currentSheet->toArray(null, true, $formatData, true) : false;
        } else {
            // se ho specificato un range torno solo quello
            if ($fromCell && $toCell) {
                // da - a
                $range = $fromCell . ':' . $toCell;
            } else {
                // solo uno da from e to quindi torno cella singola
                $range = $fromCell . $toCell;
            }

            return $currentSheet ? $currentSheet->rangeToArray($range, null, true, $formatData, true) : false;
        }
    }

    public function disconnectWorksheets() {
        $this->objPHPExcel->disconnectWorksheets();
    }

}
