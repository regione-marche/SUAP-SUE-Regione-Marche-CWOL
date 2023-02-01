<?php

/**
 *
 * 
 *
 *  * PHP Version 5
 *
 * @category   CORE
 * @package    /lib/itaPHPCorre
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license 
 * @version    12.09.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
class sqlData {

    private $sqlDB;
    private $sqlBase;
    private $totalRows;
    private $totalPages;
    private $pageNum;
    private $sqlParams;

    function __construct($dataSource) {
        $this->sqlDB = $dataSource['sqlDB'];
        $this->sqlBase = $dataSource['sqlQuery'];
        $this->sqlParams = $dataSource['sqlParams'];
        $Result_count = ItaDB::DBSQLCount($this->sqlDB, $this->sqlBase, $this->sqlParams);
        $this->totalRows = $Result_count; //['COUNT(*)'];
    }

    public function getRows($pageRows, $pageNum, $sortIndex, $sortOrder) {
        $this->pageNum = $pageNum;
        if ($this->totalRows != 0) {
            $this->totalPages = intval(($this->totalRows - 1) / $pageRows) + 1;
        } else {
            return false;
        }
        if ($this->pageNum > $this->totalPages) {
            $this->pageNum = $this->totalPages;
        }
        $sqlString = $this->sqlBase;
        // Gestione sort multipli 
        if ($sortIndex != '') {

            if (is_Array($sortIndex)) {
                $sqlString .= ' ORDER BY ';

                $i = 0;
                foreach ($sortIndex as $sort) {
                    $sqlString .= $sort;
                    $i++;
                    $this->addAttributeSort($sqlString, $sortOrder);
                    if ($i !== count($sortIndex)) {
                        $sqlString .= ",";
                    }
                }
            } else {
                $sqlString .= ' ORDER BY ' . $sortIndex;
                $this->addAttributeSort($sqlString, $sortOrder);
            }
        }

        $da = ($this->pageNum - 1) * $pageRows + 1;
        $per = $pageRows;
        $Result_tab = ItaDB::DBSQLSelect($this->sqlDB, $sqlString . " ", true, $da, $per, $this->sqlParams);
        return $Result_tab;
    }

    //function ordinamento per primaryKey
    private function addAttributeSort(&$sqlString, $sortOrder) {
        switch (strtolower($sortOrder)) {
            case 'desc':
                $sqlString .= ' DESC';
                break;
            case 'asc':
                $sqlString .= ' ASC';
                break;
            case 'none':
                break;
            default:
                $sqlString .= ' ASC';
                break;
        }
    }

    public function getTotalPages() {
        return $this->totalPages;
    }

    public function getTotalRows($pageRows = null, $pageNum = null, $sortIndex = null, $sortOrder = null) {
        return $this->totalRows;
    }

    public function getPageNum() {
        return $this->pageNum;
    }

}

/**
 * Description of arrayData
 *
 * @author utente
 */
class arrayData {

    private $arrayTable;
    private $totalRows;
    private $totalPages;
    private $pageNum;
    private $rowIndex;

    function __construct($dataSource) {
        $this->arrayTable = $dataSource['arrayTable'];
        $this->totalRows = count($this->arrayTable);
        $this->rowIndex = $dataSource['rowIndex'];
        if ($this->rowIndex) {
            foreach ($this->arrayTable as $key => $value) {
                $this->arrayTable[$key][$this->rowIndex] = $key;
            }
        }

        if ($this->totalRows == 0) {
            return false;
        }
    }

    public function getRows($pageRows, $pageNum, $sortIndex, $sortOrder) {
        $multikey = array();
        $this->pageNum = $pageNum;
        if ($this->totalRows != 0) {
            $this->totalPages = intval(($this->totalRows - 1) / $pageRows) + 1;
        } else {
            return false;
        }
        if ($this->pageNum > $this->totalPages) {
            $this->pageNum = $this->totalPages;
        }
        if ($sortIndex) {
            $Order = $sortIndex . ' ' . $sortOrder;
            // ----- Preparo la chiave e l'ordine
            $chiavi = explode(",", $Order);
            $multikey = array();
            foreach ($chiavi as $valore) {
                $multikey[] = array_values(array_filter(explode(" ", trim($valore))));
            }
        }
        // ---- Fine Chiavi Ordine
        $this->arrayTable = $this->multival_sort($this->arrayTable, $multikey);
        $da = ($this->pageNum - 1) * $pageRows + 1;
        $per = $pageRows;
        return array_slice($this->arrayTable, $da - 1, $per);
    }

    /**
     * 
     * @param array $a array sorgente
     * @param string $subkey chiave ordinamento
     * @param string $ordine tipo ordinamento (asc|desc)
     * @return type
     */
    function multival_sort($a, $multikey = array()) {
        if (!$multikey) {
            return $a;
        }

        $sort = array();
        foreach ($a as $k => $v) {
            foreach ($multikey as $i => $subkey) {
                $sort[$subkey[0]][$k] = $v[$subkey[0]];
            }
        }

        $param = array();
        foreach ($multikey as $subkey) {
            $param[] = $sort[$subkey[0]];
            if ($subkey[1] == 'asc' || $subkey[1] == 'ASC') {
                $param[] = SORT_ASC;
            } else {
                $param[] = SORT_DESC;
            }
        }
        $param = array_merge($param, array(&$a));

        if (PHP_VERSION_ID < 50400) {
            /*
             * NOTA
             * Per PHP < 5.4, call_user_func_array non passa variabili
             * per referenza a funzioni interne, e array_multisort
             * modifica il valore dell'array solo tramite referenza.
             * Per ovviare temporaneamente al problema è possibilie utilizzare
             * il seguente script.
             * @TODO Risolvere definitivamente il problema.
             */
            switch (count($param)) {
                default:
                case 3:
                    array_multisort($param[0], $param[1], $param[2]);
                    break;

                case 5:
                    array_multisort($param[0], $param[1], $param[2], $param[3], $param[4]);
                    break;

                case 7:
                    array_multisort($param[0], $param[1], $param[2], $param[3], $param[4], $param[5], $param[6]);
                    break;
            }
        } else {
            call_user_func_array('array_multisort', $param);
        }
        return $a;
    }

    function subval_sort($a, $subkey = '', $ordine = 'desc') {
        if (!$subkey) {
            return $a;
        }
        $ordine = strtolower($ordine);
        foreach ($a as $k => $v) {
            $b[$k] = strtolower($v[$subkey]);
        }
        if ($ordine == 'asc') {
            asort($b);
        } else {
            arsort($b);
        }
        foreach ($b as $key => $val) {
            $c[] = $a[$key];
        }
        return $c;
    }

    public function getTotalPages() {
        return $this->totalPages;
    }

    public function getTotalRows() {
        return $this->totalRows;
    }

    public function getPageNum() {
        return $this->pageNum;
    }

}

/**
 * Description of TableViewclass
 *
 * @author utente
 */
class TableView {

    private $tableId;
    private $dataSource;
    private $dataDriver;
    private $pageNum;
    private $pageRows;
    private $sortIndex;
    private $sortOrder;
    private $filterArray;
    private $totalRows;
    private $totalPages;
    private $rowIndex;
    private $xlsHeaders;

    /**
     *  Il costruttore prende i dati che verranno inseriti nella griglia.
     *  Si può utilizzare principalemnte in due modi:
     *      A) utilizzare una query SQL;
     *          1) Definire 'sqlDB' e addegnare il DB
     *          2) Definire 'sqlQuery' e assegnargli la query SQL
     *      B) passare direttamente i dati;
     *          1) Definire 'arrayTable' e assegnargli un array dove ogni
     *              elemento è costituito da una serie di coppie chiave => valore
     *              
     * @param String $tableId  L'ID della griglia
     * @param Array $dataSource  L'array può essere di due tipi (vedi sopra)
     * @param Int $pageNum
     * @param Int $pageRows
     * @param type $sortIndex Mixed Array di valori con cui effettuare ordinamento 
     * @param type $sortOrder 
     */
    function __construct($tableId = '', $dataSource = array(), $pageNum = 1, $pageRows = 10, $sortIndex = '', $sortOrder = '') {
        $this->tableId = $tableId;
        $this->pageNum = $pageNum;
        $this->pageRows = $pageRows;
        $this->sortIndex = $sortIndex;
        $this->sortOrder = $sortOrder;
        $this->dataSource = $dataSource;

        if (isset($this->dataSource['sqlDB'])) {
            $this->dataDriver = new sqlData($this->dataSource);
        } elseif (isset($this->dataSource['arrayTable'])) {
            $this->dataDriver = new arrayData($this->dataSource);
        }
        $this->totalRows = $this->dataDriver->getTotalRows();
    }

    function setPageRows($pageRows) {
        $this->pageRows = $pageRows;
    }

    function setPageNum($pageNum) {
        $this->pageNum = $pageNum;
    }

    function setSortIndex($sortIndex) {
        $this->sortIndex = $sortIndex;
    }

    function setSortOrder($sortOrder) {
        $this->sortOrder = $sortOrder;
    }

    function enableTableEvents() {
        self::enableEvents($this->tableId);
    }

    static function enableEvents($tableId) {
        Out::addClass($tableId, 'ita-jqgrid-active');
        //Out::codice('resizeGrid();');
    }

    function disableTableEvents() {
        self::disableEvents($this->tableId);
    }

    static function disableEvents($tableId) {
        Out::delClass($tableId, 'ita-jqgrid-active');
    }

    static function clearGrid($tableId) {
        Out::delAllRow($tableId);
    }

    /**
     * Azzera i valori presenti nei campi di filtro integrati
     * @param string $tableId id della tabella
     */
    static function clearToolbar($tableId) {
        Out::codice("var tmpGrid  = $('#" . $tableId . "')[0]; try {tmpGrid.clearToolbar()} catch(e){};tmpGrid = null;");
    }

    /**
     * Azzera stato ordinamenti
     * @param String $tableId id della tabella
     */
    static function clearSortState($tableId) {
        Out::codice("var grid = $('#" . $tableId . "');  $('span.s-ico',grid[0].grid.hDiv).hide();");
        Out::codice("var p = $('#" . $tableId . "').jqGrid('getGridParam');p.sortname = ''; p.sortorder = 'asc';");
    }

    /**
     * Cambia la label di una colonna della griglia
     * @param type $tableId
     * @param type $colName
     * @param type $labelValue
     */
    static function setLabel($tableId, $colName, $labelValue) {
        Out::codice("$('#$tableId').jqGrid('setLabel', '$colName', '$labelValue');");
    }

    function exportXLS($tableModel, $fileName = '') {
        $Result_tab = $this->dataDriver->getRows($this->totalRows, 1, $this->sortIndex, $this->sortOrder);

        // Ricava nomi colonne da TableView
        $firstRecord = reset($Result_tab);
        $colnames = array_keys($firstRecord);

        // Imposta colonne per esportazione, unendo le regole presenti in $this->xlsHeaders a dei default        
        if ($this->xlsHeaders) {
            $allHeaders = array();
            foreach ($colnames as $colname) {
                if (!isset($this->xlsHeaders[$colname])) {
                    $allHeaders[$colname] = array('format' => 'string');
                } else {
                    $allHeaders[$colname] = array('format' => $this->xlsHeaders[$colname]);
                }
            }
        }

        $tableData = array();

        if ($tableModel == '') {
            $tableModel = array();
        }

        if (!is_dir(itaLib::getAppsTempPath())) {
            itaLib::createAppsTempPath();
        }

        $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.xlsx';
        $filePath = itaLib::getAppsTempPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $fileName;
        foreach ($Result_tab as $record) {
            foreach ($record as $campo => $value) {
                if (mb_detect_encoding($value, 'UTF-8', true) !== 'UTF-8') {
                    $record[$campo] = utf8_encode($value);
                }
            }

            $tableData[] = $record;
        }

        require_once ITA_LIB_PATH . '/itaXlsxWriter/itaXlsxWriter.class.php';
        $itaXlsxWriter = new itaXlsxWriter();
        if ($allHeaders) {
            $itaXlsxWriter->setRenderFieldsMetadata($allHeaders);
        }
        $itaXlsxWriter->setDataFromArray($tableData);
        $itaXlsxWriter->writeToFile($filePath);

        require_once './apps/Utility/utiDownload.class.php';
        Out::openDocument(utiDownload::getUrl($fileName, $filePath));

//        $Result_tab = $this->dataDriver->getRows($this->totalRows, 1, $this->sortIndex, $this->sortOrder);
//        if ($tableModel == '') {
//            $tableModel = array();
//            foreach ($Result_tab[0] as $key => $value) {
//
//                $tableModel[] = $key;
//            }
//        }
//        $contenuto = "  <!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
//                        <html>
//                        <head>
//                            <meta http-equiv=\"Content-Type\" content=\"application/vnd.ms-excel\"; name='excel'>
//                        </head>
//                        <table border=\"1\" cellspcing=\"1\" cellpadding=\"1\">
//                        <thead>";
//        foreach ($tableModel as $value) {
//            $contenuto .= "<th>$value</th>";
//        }
//        $contenuto .= "</thead>";
//
//        foreach ($Result_tab as $Result_rec) {
//            $contenuto .= "<tr>";
//            foreach ($Result_rec as $key => $value) {
//                $contenuto .= "<td>$value</td>";
//            }
//            $contenuto.="</tr>";
//        }
//        $contenuto .="</table></html>";
//        if (!is_dir(itaLib::getAppsTempPath())) {
//            itaLib::createAppsTempPath();
//        }
//        $filePath = itaLib::getAppsTempPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $fileName;
//        $handler = fopen($filePath, 'w');
//        if (fwrite($handler, $contenuto)) {
//            require_once './apps/Utility/utiDownload.class.php';
//            Out::openDocument(utiDownload::getUrl($fileName, $filePath));
//        }
    }

    function exportCSV($tableModel, $fileName = '') {
        $Result_tab = $this->dataDriver->getRows($this->totalRows, 1, $this->sortIndex, $this->sortOrder);
        if ($tableModel == '') {
            $tableModel = array();
            foreach ($Result_tab[0] as $key => $value) {
                $tableModel[] = $key;
            }
        }
        foreach ($tableModel as $value) {
            $contenuto .= "\"$value\";";
        }
        $contenuto .= "\n";

        foreach ($Result_tab as $Result_rec) {
            foreach ($Result_rec as $key => $value) {
                $contenuto .= "\"$value\";";
            }
            $contenuto .= "\n";
        }
        if (!is_dir(itaLib::getAppsTempPath())) {
            itaLib::createAppsTempPath();
        }
        $filePath = itaLib::getAppsTempPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $fileName;
        $handler = fopen($filePath, 'w');
        if (fwrite($handler, $contenuto)) {
            require_once './apps/Utility/utiDownload.class.php';
            Out::openDocument(utiDownload::getUrl($fileName, $filePath));
        }
    }

    function getDataArray() {
        $Result_tab = $this->dataDriver->getRows($this->pageRows, $this->pageNum, $this->sortIndex, $this->sortOrder);
        if (!$Result_tab) {
            return false;
        }
        return $Result_tab;
    }

    function getDataPage($format, $clearGrid = false) {
//        $Result_tab = $this->dataDriver->getRows($this->pageRows, $this->pageNum, $this->sortIndex, $this->sortOrder);
//
//        if (!$Result_tab) {
//            return false;
//        }
        $Result_tab = $this->getDataArray();
        if (!$Result_tab) {
            if ($clearGrid) {
                self::clearGrid($this->tableId);
            }
            return false;
        }
        $contenuto = '';
        switch ($format) {
            case'xml':
                foreach ($Result_tab as $Result_rec) {
                    $contenuto .= "<row>";
                    foreach ($Result_rec as $key => $value) {
                        $contenuto .= "<$key><![CDATA[$value]]></$key>";
                    }
                    $contenuto .= "</row>";
                }
                $xmlRet = ' <jqgrid>
                                <righe>' . $this->dataDriver->getTotalRows() . '</righe>
                                <pagine>' . $this->dataDriver->getTotalPages() . '</pagine>
                                <pagina>' . $this->dataDriver->getPageNum() . '</pagina>
                                ' . $contenuto . '
                            </jqgrid>';
                Out::addXML($this->tableId, $xmlRet, $clearGrid);
                break;

            case 'json':
            default:
                $json = new Services_JSON();
                $jsonRet = '{
                            "righe":' . $this->dataDriver->getTotalRows() . ',
                            "pagine":' . $this->dataDriver->getTotalPages() . ',
                            "pagina":' . $this->dataDriver->getPageNum() . ',
                            "row": [
                         ';

                foreach ($Result_tab as $idx => $Result_rec) {
                    $jsonRet .= "{";
                    $tmpArray2 = array();
                    foreach ($Result_rec as $key => $value) {
                        $tmpArray2[] = "\"$key\":" . $json->encode(utf8_encode($value));
                    }
                    $jsonRet .= implode(',', $tmpArray2);
                    $jsonRet .= "}";
                    if ($idx == count($Result_tab) - 1)
                        break;
                    $jsonRet .= ",";
                }
                $jsonRet .= "]}";
                Out::addJson($this->tableId, $jsonRet, $clearGrid);
                break;
        }


        return true;
    }

    function getDataPageFromArray($format, $Result_tab) {
        if (!$Result_tab) {
            return false;
        }
        $contenuto = '';
        switch ($format) {
            case'xml':
                foreach ($Result_tab as $Result_rec) {
                    $contenuto .= "<row>";
                    foreach ($Result_rec as $key => $value) {
                        $contenuto .= "<$key><![CDATA[$value]]></$key>";
                    }
                    $contenuto .= "</row>";
                }
                $xmlRet = ' <jqgrid>
                                <righe>' . $this->dataDriver->getTotalRows() . '</righe>
                                <pagine>' . $this->dataDriver->getTotalPages() . '</pagine>
                                <pagina>' . $this->dataDriver->getPageNum() . '</pagina>
                                ' . $contenuto . '
                            </jqgrid>';
                Out::addXML($this->tableId, $xmlRet);
                break;

            case 'json':
            default:
                $json = new Services_JSON();
                $jsonRet = '{
                            "righe":' . $this->dataDriver->getTotalRows() . ',
                            "pagine":' . $this->dataDriver->getTotalPages() . ',
                            "pagina":' . $this->dataDriver->getPageNum() . ',
                            "row": [
                         ';
                foreach ($Result_tab as $idx => $Result_rec) {
                    $jsonRet .= "{";
                    $tmpArray2 = array();
                    foreach ($Result_rec as $key => $value) {
                        $tmpArray2[] = "\"$key\":" . $json->encode(utf8_encode($value));
                    }
                    $jsonRet .= implode(',', $tmpArray2);
                    $jsonRet .= "}";
                    if ($idx == count($Result_tab) - 1)
                        break;
                    $jsonRet .= ",";
                }
                $jsonRet .= "]}";
                Out::addJson($this->tableId, $jsonRet);
                break;
        }


        return true;
    }

    /**
     *  Modifica il valore di una cella della griglia
     * @param String $tableId      ID della griglia/tabella da modificare
     * @param String $rowid        ID della riga che contiene il valore da modificare
     * @param String $colname      Nome della colonna con il valore da modificare
     * @param String $value        Valore da inserire nella cella interessata
     * @param String $class        Classe con stili che verranno applicati alla cella
     * @param String $properties   Cambia property del colmodel
     * @param String $forceup      [true o false] Per forzare il cambiamento anche se il valore è vuoto  
     */
    static function setCellValue($tableId, $rowid, $colname, $value, $class = '', $properties = '', $forceup = 'true') {
        Out::setCellValue($tableId, $rowid, $colname, $value, $class, $properties, $forceup);
    }

    /**
     *  Modifica una riga della griglia della tabella
     * @param String $tableId   ID della griglia/tabella da modificare
     * @param String $rowid     ID della riga da modificare
     * @param Array $rowData    Array con i valori che vanno a sostituire i precedenti
     * @param String $cssprop   (String o array) Stili css non ancora implementato
     */
    static function setRowData($tableId, $rowid, $rowData, $cssprop = '') {

        $json = new Services_JSON();

        if ($rowData) {
            $jsonRet .= "{";
            $tmpArray2 = array();
            foreach ($rowData as $key => $value) {
                $tmpArray2[] = "\"$key\":" . $json->encode(utf8_encode($value));
            }
            $jsonRet .= implode(',', $tmpArray2);
            $jsonRet .= "}";
        }
        if ($cssprop) {
            $tmpArray3 = array();
            foreach ($cssprop as $key => $value) {
                $tmpArray3[] = "\"$key\":" . $json->encode(utf8_encode($value));
            }
            $jsonRetCss .= "{";
            $jsonRetCss .= implode(',', $tmpArray3);
            $jsonRetCss .= "}";
        }
        Out::setRowData($tableId, $rowid, $jsonRet, $jsonRetCss);
    }

    static function enableSelection($tableId, $rowid, $selectionType = 'id') {
        Out::enableRowSelection($tableId, $rowid, $selectionType);
    }

    static function disableSelection($tableId, $rowid, $selectionType = 'id') {
        Out::disableRowSelection($tableId, $rowid, $selectionType);
    }

    /**
     * 
     * @param type $tableId
     * @param type $rowid
     * @param type $selectionType
     */
    static function setSelection($tableId, $rowid, $selectionType = 'id', $propagateEvent = true) {
        Out::setRowSelection($tableId, $rowid, $selectionType, $propagateEvent);
    }

    /**
     * 
     * @param type $tableId
     */
    static function setSelectAll($tableId) {
        Out::setRowSelectAll($tableId);
    }

    /**
     * 
     * @param type $tableId
     */
    static function setDeselectAll($tableId) {
        Out::setRowDeselectAll($tableId);
    }

    /**
     * 
     * @param type $tableId
     */
    static function reload($tableId) {
        Out::gridReload($tableId);
    }

    /**
     * 
     * @param type $tableId
     * @param type $colName
     */
    static function showCol($tableId, $colName) {
        Out::gridShowCol($tableId, $colName);
    }

    static function hideCol($tableId, $colName) {
        Out::gridHideCol($tableId, $colName);
    }

    /**
     * Aggiunge figli ad un nodo dell'albero
     */
    function treeAddChildren() {
        self::treeTableAddChildren($this->tableId, $this->getDataArray(), $this->rowIndex);
    }

    /**
     * Aggiunge figli ad un nodo dell'albero
     * @param string $idTabella Nome tabella
     * @param string $rows Contiene l'array di nodi da aggiungere all'albero
     * @param string $rowIndex Il valore indice della tabella come definito nel disegno
     */
    static function treeTableAddChildren($idTabella, $rows, $rowIndex) {
        $flat = array();

        if ($rows) {
            foreach ($rows as $idx => $row) {
                if ($rowIndex) {
                    $row[$rowIndex] = $idx;
                }

                $row = array_map('utf8_encode', $row);

                $flat[] = $row;
            }

            Out::treeAddChildren($idTabella, json_encode(array('row' => $flat)));
        }
    }

    /**
     * Rimuove figli ad un nodo dell'albero
     * @param string $parentId Contiene id del nodo padre a cui rimuovere i figli
     */
    function treeRemoveChildren($parentId) {
        self::treeTableRemoveChildren($this->tableId, $parentId);
    }

    /**
     * Rimuove figli ad un nodo dell'albero
     * @param string $idTabella Nome tabella
     * @param string $parentId Contiene id del nodo padre a cui rimuovere i figli
     */
    static function treeTableRemoveChildren($idTabella, $parentId) {
        Out::treeRemoveChildren($idTabella, json_encode(array('id' => $parentId)));
    }

    static function addRow($idTabella, $rowidRiga, $datiRiga, $position = 'last', $referenceRowid = false) {
        Out::addGridRow($idTabella, $rowidRiga, $datiRiga, $position, $referenceRowid);
    }

    static function delRow($idTabella, $rowidRiga) {
        Out::delGridRow($idTabella, $rowidRiga);
    }

    /**
     * 
     * Helper per aggiungere ad un array trasmissibile ad una griglia
     * un sotto-livello collegato ad un rowid per i dati della relativa subgrid.
     * Il metodo è applicabile ricorsivamente in più livelli padre-figlio.
     * 
     * @param array $subgridInfo Array che definisce la sequenza di rowid che indicano il percorso ad una riga di una sotto griglia
     *              ogni elemento dell'array è indicativo di un livello di annidamento delle sotto griglie, la chiave identifica il nome della griglia
     *              il valore il rowid della riga della griglia.
     * @param array $arrayData Array contenente i dati completi della struttura delle griglie.
     * @param type  $subgridId ID della griglia o sotto griglia dove aggiungere. N.B. si può indicare anche un elemento
     *              intermedio della sequenza $subGrìdInfo che sarà correttamente movimentato.
     * @param type $subgridData Dati della griglia in input da aggiungere.
     * @return array ritorna il nuovo valore di $arrayData
     */
    static function addSubgridData($subgridInfo, $arrayData, $subgridId, $subgridData) {
        foreach ($subgridInfo as $tableIdInfo => $tableRowidInfo) {
            if ($tableIdInfo === $subgridId) {
                break;
            }

            if (!isset($wknRecord)) {
                $wknRecord = &$arrayData[$tableRowidInfo];
            } else {
                $wknRecord = &$wknRecord[$tableIdInfo][$tableRowidInfo];
            }
        }

        $wknRecord[$subgridId] = $subgridData;

        return $arrayData;
    }

    /**
     * 
     * Helper per estrarre da un array trasmissibile ad una griglia
     * un sotto-livello collegato ad un rowid per i dati della relativa subgrid.
     * 
     * @param type $subgridInfo Array che definisce la sequenza di rowid che indicano il percorso ad una riga di una sotto griglia
     *              ogni elemento dell'array è indicativo di un livello di annidamento delle sotto griglie, la chiave identifica il nome della griglia
     *              il valore il rowid della riga della griglia.
     * @param array $arrayData Array contenente i dati completi della struttura delle griglie.
     * @param type $subgridId (opzionale) ID della griglia di cui vogliamo i dati, se
     * non definito vengono ritornati i dati dell'ultima griglia presente in $subgridInfo.  N.B. si può indicare anche un elemento
     *              intermedio della sequenza $subGrìdInfo che sarà correttamente estratto.
     * @return array ritorna il sotto livello richiesto.
     */
    static function getSubgridData($subgridInfo, $arrayData, $subgridId = false) {
        $tmpRowid = null;

        foreach ($subgridInfo as $tableIdInfo => $tableRowidInfo) {
            if (!isset($wknRecord)) {
                $wknRecord = $arrayData;
            } else {
                $wknRecord = $wknRecord[$tmpRowid][$tableIdInfo];
            }

            if ($subgridId && $subgridId === $tableIdInfo) {
                break;
            }

            $tmpRowid = $tableRowidInfo;
        }

        return $wknRecord;
    }

    /**
     * Estrae da un array con subgrid i dati di una riga da griglia o sotto griglia
     *     
     * @param array $subgridInfo Array che definisce la sequenza di rowid che indicano il percorso ad una riga di una sotto griglia
     *              ogni elemento dell'array è indicativo di un livello di annidamento delle sotto griglie, la chiave identifica il nome della griglia
     *              il valore il rowid della riga della griglia.
     * @param array $arrayData Array contenente i dati completi della struttura delle griglie.
     * @param type $subgridId (opzionale) ID della griglia di cui vogliamo i dati, se
     * non definito vengono ritornati i dati dell'ultima griglia presente in $subgridInfo.  N.B. si può indicare anche un elemento
     *              intermedio della sequenza $subGrìdInfo che sarà correttamente estratto.
     * @return array riga selezionata con $subgridInfo e $subgridId da $arrayData.
     */
    static function getSubgridRecord($subgridInfo, $arrayData, $subgridId = false) {
        foreach ($subgridInfo as $tableIdInfo => $tableRowidInfo) {
            if (!isset($wknRecord)) {
                $wknRecord = $arrayData[$tableRowidInfo];
            } else {
                $wknRecord = $wknRecord[$tableIdInfo][$tableRowidInfo];
            }

            if ($subgridId && $subgridId === $tableIdInfo) {
                break;
            }
        }

        return $wknRecord;
    }

    /**
     * Cancella un array corrispondente ad una subgrid in un array
     * di subgrid
     * 
     * @param array $subgridInfo Array che definisce la sequenza di rowid che indicano il percorso ad una riga di una sotto griglia
     *              ogni elemento dell'array è indicativo di un livello di annidamento delle sotto griglie, la chiave identifica il nome della griglia
     *              il valore il rowid della riga della griglia.
     * @param array $arrayData Array contenente i dati completi della struttura delle griglie.
     * @param type  $subgridId ID della griglia o sotto griglia dove aggiungere. N.B. si può indicare anche un elemento
     *              intermedio della sequenza $subGrìdInfo che sarà correttamente movimentato.
     * @return array ritorna il nuovo valore di $arrayData
     */
    static function deleteSubgridData($subgridInfo, $arrayData, $subgridId) {
        foreach ($subgridInfo as $tableIdInfo => $tableRowidInfo) {
            if ($subgridId === $tableIdInfo) {
                break;
            }

            if (!isset($wknRecord)) {
                $wknRecord = &$arrayData[$tableRowidInfo];
            } else {
                $wknRecord = &$wknRecord[$tableIdInfo][$tableRowidInfo];
            }
        }

        unset($wknRecord[$subgridId]);

        return $arrayData;
    }

    public function showInlineButton($button = 'all') {
        Out::gridShowInlineButton($this->tableId, $button);
    }

    static function tableShowInlineButton($idTabella, $button = 'all') {
        Out::gridShowInlineButton($idTabella, $button);
    }

    public function hideInlineButton($button = 'all') {
        Out::gridHideInlineButton($this->tableId, $button);
    }

    static function tableHideInlineButton($idTabella, $button = 'all') {
        Out::gridHideInlineButton($idTabella, $button);
    }

    public function disable() {
        Out::disableGrid($this->tableId);
    }

    static function tableDisable($idTabella) {
        Out::disableGrid($idTabella);
    }

    public function enable() {
        Out::enableGrid($this->tableId);
    }

    static function tableEnable($idTabella) {
        Out::enableGrid($idTabella);
    }

    public function setXLSHeaders($headers = array()) {
        $this->xlsHeaders = $headers;
    }

    static function setHeight($gridId, $height) {
        Out::setGridHeight($gridId, $height);
    }

    static function setWidth($gridId, $width) {
        Out::setGridWidth($gridId, $width);
    }

    static function collapseGridRowExpand($gridId, $rowId) {
        Out::collapseGridRowExpand($gridId, $rowId);
    }

    static function addCustomButtonGrid($gridName, $title, $icon, $returnEvent) {
        Out::codice("$('#" . $gridName . "').jqGrid('navButtonAdd', '#" . $gridName . "-ita-pager', {caption: '',title: '" . $title . "',
            buttonicon: '" . $icon . "',id: id + '_" . $returnEvent . "', onClickButton: function () {
                    var idObj = $('#" . $gridName . "');
                    var postdata = idObj.getGridParam('postData');
                    itaGo('ItaForm', idObj, {
                        event: '" . $returnEvent . "',
                        validate: false,
                        rows: postdata.rows,
                        page: postdata.page,
                        sidx: postdata.sidx,
                        sord: postdata.sord,
                        _search: postdata._search
                    });
                },
                position: 'last'
            });");
    }

    private static function getFilterID($gridId, $columnId) {
        return "gview_$gridId #gs_$columnId";
    }

    private static function getFilterParentID($gridId, $columnId) {
        Out::codice("$(protSelector('#gview_$gridId #gs_$columnId')).parent().attr('id', 'gs_{$columnId}_parent');");
        return "gview_$gridId #gs_{$columnId}_parent";
    }

    public function setFilterFocus($columnName) {
        self::tableSetFilterFocus($this->tableId, $columnName);
    }

    static function tableSetFilterFocus($tableId, $columnName) {
        Out::setFocus('', self::getFilterID($tableId, $columnName));
    }

    public function setFilterValue($columnName, $value) {
        self::tableSetFilterValue($this->tableId, $columnName, $value);
    }

    static function tableSetFilterValue($tableId, $columnName, $value) {
        Out::valore(self::getFilterID($tableId, $columnName), $value);
    }

    public function setFilterHtml($columnName, $value, $modo = '') {
        self::tableSetFilterHtml($this->tableId, $columnName, $value, $modo);
    }

    static function tableSetFilterHtml($tableId, $columnName, $value, $modo = '') {
        Out::html(self::getFilterID($tableId, $columnName), $value, $modo);
    }

    public function setFilterParentHtml($columnName, $value, $modo = '') {
        self::tableSetFilterParentHtml($this->tableId, $columnName, $value, $modo);
    }

    static function tableSetFilterParentHtml($tableId, $columnName, $value, $modo = '') {
        Out::html(self::getFilterParentID($tableId, $columnName), $value, $modo);
    }

    public function setFilterSelect($columnName, $comando, $returnval, $selected, $nodevalue, $style = '') {
        self::tableSetFilterSelect($this->tableId, $columnName, $comando, $returnval, $selected, $nodevalue, $style);
    }

    static function tableSetFilterSelect($tableId, $columnName, $comando, $returnval, $selected, $nodevalue, $style = '') {
        Out::select(self::getFilterID($tableId, $columnName), $comando, $returnval, $selected, $nodevalue, $style);
    }

    static function enableFrozenColumns($tableId) {
        Out::codice("if ( !$('#{$tableId}_frozen').length ) $('#$tableId').jqGrid('setFrozenColumns').trigger('reloadGrid');");
    }

    static function disableFrozenColumns($tableId) {
        Out::codice("if ( $('#{$tableId}_frozen').length ) $('#$tableId').jqGrid('destroyFrozenColumns');");
    }

    static function setColumnProperty($tableId, $column, $property, $value) {
        $jsValue = json_encode($value);
        Out::codice("$('#$tableId').jqGrid('setColProp', '$column', { $property: $jsValue });");
    }

    static function setFrozenColumn($tableId, $column) {
        self::setColumnProperty($tableId, $column, 'frozen', true);
    }

    static function unsetFrozenColumn($tableId, $column) {
        self::setColumnProperty($tableId, $column, 'frozen', false);
    }

    static function resizeGrid($tableId, $reload = false, $force = false) {
        $str_reload = ($reload) ? 'true' : 'false';
        $str_force = ($force) ? 'true' : 'false';
        Out::codice("resizeGrid('$tableId' , $str_reload, $str_force );");
    }

    static function setCaption($tableId, $caption) {
        Out::codice("$('#$tableId').setCaption('$caption');");
    }

    static function removeCellsBorder($tableId) {
        Out::addClass($tableId, 'ita-jqgrid-hide-borders');
    }

    static function resetCellsBorder($tableId) {
        Out::delClass($tableId, 'ita-jqgrid-hide-borders');
    }

}
