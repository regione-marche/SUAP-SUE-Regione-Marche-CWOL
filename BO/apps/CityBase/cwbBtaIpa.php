<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';


function cwbBtaIpa() {
    $cwbBtaIpa = new cwbBtaIpa();
    $cwbBtaIpa->parseEvent();
    return;
}

class cwbBtaIpa extends itaFrontControllerCW {

    public $divGes;
    public $divRis;
    public $GRID_NAME;
    public $gridFilters = array();
    private $gridBtaIpa;
    private $gridBtaPec;
    private $libBta;

    function postItaFrontControllerCostruct() {
        try {
            $this->divGes=$this->nameForm .'_divGestione';
            $this->divRis=$this->nameForm .'_divRisultato';
            $this->gridBtaIpa=$this->nameForm .'_gridBtaIpa';
            $this->gridBtaPec=$this->nameForm .'_gridBtaPec';
            $this->libBta = new cwbLibDB_BTA();
            $this->MAIN_DB = ItaDB::DBOpen(cwbLib::getCitywareConnectionName(), '');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        $this->Elenca();
                        break;
                }
                break;
            case 'openform': // Visualizzo la form di ricerca
                Out::setFocus("", $this->nameForm . '_RICERCA');
                break;
            case 'onSelectRow':
                switch ($_POST['id']) {
                    case $this->gridBtaIpa:
                        $Lista = cwbParGen::getFormSessionVar($this->nameForm, 'Result_tab'); // leggo array dati grid
                        $row = $Lista[$_POST['rowid'] - 1]; //leggo il record selezionato
                        $this->CaricaPEC($row['IPA_CODAMM'], $row['IPA_DESAMM']); //carico Pec
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        $this->Elenca();
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Rubrica': // Visualizzo la form di ricerca
                        $this->Rubrica();
                        break;
                    case $this->nameForm . '_Elenca': // Visualizzo la form di ricerca
                        $this->Elenca();
                        break;
                }
                break;
        }
    }

    private function CreaSql(&$sqlParams) {
        $filtro = strtoupper(trim($_POST[$this->nameForm . '_RICERCA'])); //leggo parametro di ricerca inserito
        $condi = explode(" ", $filtro); // condizione da inserire nella select
//        $totaele = count($condi);
//        $sql = "SELECT DISTINCT 'AMM' as IPA_TPFILE, AMM.IPA_CODAMM, AMM.IPA_DESAMM, AMM.IPA_COMUNE"
//                . ", AMM.IPA_PROV, COUNT(RUB.PK) as RUBRICA, PROGRECORD"
//                . " FROM BTA_IPAAMM AMM LEFT JOIN BTA_IPARUB RUB on AMM.IPA_CODAMM=RUB.IPA_CODAMM WHERE 1=1";
//        if ($totaele > 0) {
//            for ($i = 0; $i < $totaele; $i++) {
//                $sql = $sql . ' ' . "AND upper(AMM.STRING_RIC) LIKE" . ' ' . "'" . '%' . addslashes($condi[$i]) . '%' . "'" . ' ';
//            }
//        }
//        $sql = $sql . ' ' . "GROUP BY IPA_TIPOAM, AMM.IPA_CODAMM, AMM.IPA_DESAMM, AMM.IPA_COMUNE, AMM.IPA_PROV, AMM.PROGRECORD"
//                . " UNION" . ' '
//                . "SELECT DISTINCT PEC.IPA_TPFILE, PEC.IPA_CODAMM, PEC.IPA_DESPEC as IPA_DESAMM"
//                . ", PEC.IPA_COMUNE, PEC.IPA_PROV, 0 as RUBRICA, PROGRECORD"
//                . " FROM BTA_IPAPEC PEC"
//                . " WHERE 1=1";
//        if ($totaele > 0) {
//            for ($i = 0; $i < $totaele; $i++) {
//                $sql = $sql . ' ' . "AND upper(PEC.STRING_RIC) LIKE" . ' ' . "'" . '%' . addslashes($condi[$i]) . '%' . "'" . ' ';
//            }
//        }
//        $sql = $sql . ' ' . "ORDER BY PROGRECORD";
//        return $sql;

        return $this->libBta->getSqlLeggiBtaIpaamm($condi, $sqlParams);
    }

    private function Elenca() {
        TableView::clearGrid($this->gridBtaIpa);
        $sql = $this->CreaSql($sqlParams);
        $ita_grid01 = new TableView($this->gridBtaIpa, array(
            'sqlDB' => $this->MAIN_DB,
            'sqlParams' => $sqlParams,
            'sqlQuery' => $sql));

        $ita_grid01->setPageNum(isset($_POST['page']) ? $_POST['page'] : 1);
        $pageRows = isset($_POST['rows']) ? $_POST['rows'] : $_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['rowNum'];
        $ita_grid01->setPageRows($pageRows ? $pageRows : self::DEFAULT_ROWS);
        $ita_grid01->setSortOrder('asc');
        if (!$this->getDataPage($ita_grid01, $this->elaboraGrid($ita_grid01))) {
            Out::msgStop("Selezione", "Nessun record trovato.");
        } else {   // Visualizzo il risultato
            Out::hide($this->divGes);
            Out::show($this->divRis);
            TableView::enableEvents($this->gridBtaIpa);
        }
    }

    protected function elaboraGrid($ita_grid) {
        $Result_tab_tmp = $ita_grid->getDataArray();
        $Result_tab = $this->elaboraRecords($Result_tab_tmp);

        return $Result_tab;
    }

    protected function elaboraGridPec($ita_grid) {
        $Result_tab_tmp = $ita_grid->getDataArray();
        $Result_tab = $this->elaboraRecordsPec($Result_tab_tmp);

        return $Result_tab;
    }

    protected function getDataPage($ita_grid, $Result_tab) {
        if ($Result_tab == null) {
            return $ita_grid->getDataPage('json');
        } else {
            return $ita_grid->getDataPageFromArray('json', $Result_tab);
        }
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['COMUNE'] = $this->formatDataGridCod(trim($Result_tab[$key]['IPA_COMUNE'])
                    . ' ' . "(" . trim($Result_tab[$key]['IPA_PROV']) . ")");
            if ($key == 0) {     //carico Pec per il primo elemento
                $this->CaricaPEC($Result_tab[$key]['IPA_CODAMM'], $Result_tab[$key]['IPA_DESAMM']);
            }
        }
        cwbParGen::setFormSessionVar($this->nameForm, 'Result_tab', $Result_tab); // salvo in sessione array con dati grid.
        return $Result_tab;
    }

    protected function elaboraRecordsPec($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['COMU'] = $this->formatDataGridCod(trim($Result_tab[$key]['IPA_COMUNE'])
                    . ' ' . "(" . trim($Result_tab[$key]['IPA_PROV']) . ")");
            $Result_tab[$key]['TPFILE'] = $this->formatDataGridCod(trim($Result_tab[$key]['IPA_TPFILE']));
        }
        return $Result_tab;
    }

    private function CaricaPEC($cod, $des) {
//        $sql = "SELECT * FROM BTA_IPAPEC WHERE"
//                . " IPA_CODAMM=" . "'" . $cod . "'";
//        if($cod == 'AMM'){
//            $sql = $sql . " UNION"
//                        . " SELECT 1000000+pk AS PROGRECORD, IPA_CODAMM, IPA_UFFDES AS IPA_DESPEC, 'RUBRICA'"
//                        . " AS IPA_TPFILE, ' ', note_v, ' ', IPA_NFAX, IPA_TPMAIL, DATAOPER, ' '"
//                        . " FROM BTA_IPARUB_V01 WHERE IPA_CODAMM=" . "'" . $cod . "'";
//        }
//        $sql = $sql . " AND IPA_DESPEC=" . "'" . addslashes($des) . "'"
//                    . " ORDER BY IPA_TPFILE, IPA_DESPEC";

        $this->libBta->getSqlLeggiBtaIpaPec($cod, $des, $sqlParams);
        $ita_grid01 = new TableView($this->gridBtaPec, array(
            'sqlDB' => $this->MAIN_DB,
            'sqlParams' => $sqlParams,
            'sqlQuery' => $sql));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows($_POST[$this->gridBtaPec]['gridParam']['rowNum']);
        $ita_grid01->setSortOrder('asc');
        if (!$this->getDataPage($ita_grid01, $this->elaboraGridPec($ita_grid01))) {
            Out::setFocus("", $this->nameForm . '_RICERCA');
        } else {   // Visualizzo il risultato
            TableView::enableEvents($this->gridBtaPec);
        }
    }

    public static function formatDataGridCod($value) {
        $html = "<span><i>$value</i></span>";

        return $html;
    }

    public function close() {
        parent::close();
    }

    private function Rubrica() {
        $Lista = cwbParGen::getFormSessionVar($this->nameForm, 'Result_tab'); // leggo array dati grid
        $row = $Lista[$_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow'] - 1]; //leggo il record selezionato
        $this->apriFinestraDettaglio('cwbBtaIparub', $this->nameForm, 'returnFromBtaIparub', $_POST['id'], $row);
    }

    public static function apriFinestraDettaglio($model, $nameForm, $returnEvent, $returnId, $masterRecord) {
        itaLib::openDialog($model, true, true, 'desktopBody');
        /* @var $objModel itaFrontController */
        $objModel = itaFrontController::getInstance($model);
        $objModel->setEvent('openform');
        $objModel->setReturnModel($nameForm);
        $objModel->setMasterRecord($masterRecord);
        $objModel->setReturnEvent($returnEvent);
        $objModel->setFlagSearch(false);
        $objModel->setReturnId($returnId);
        $objModel->parseEvent();
    }

}

?>