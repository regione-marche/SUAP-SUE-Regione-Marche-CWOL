<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BWE.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaJSON.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceFactory.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php';
include_once ITA_LIB_PATH . '/itaPHPOmnis/itaOmnisClient.class.php';

/*
 * Helper per gestire metodi in comune 
 */

class cwbBpaGenHelper {

    const DEFAULT_ROWS = 13;    // Numero di record per le dimensioni della dialog = 900x500
    const PARAM_DB = 'ITALWEB';
    const PARAM_TABLE = 'FRM_RICPAR';
//    const PARAM_TABLE = 'BWE_FRMPAR';
    const PRINT_STRATEGY_OMNIS = 1;
    const PRINT_STRATEGY_JASPER = 2;
    const PRINT_FORMAT_OMNIS_PDF = 12;

    private $nameForm;
    private $modelName;
    private $gridName;
    private $db;
    private $defaultRows;
    private $areeCityware = array( // todo COMPLETARE I MODULI
        'A' => 'API', 'T' => 'TAX', 'B' => 'BASE'
    );

    /*
     * ritorna l'oggetto TableView inizializzato con l'sql passato
     */

    public function initializeTableSql($sql, $db, $sqlParams = array(), $sortIndex = '', $sortField = '', $clearGrid = true) {
        if ($clearGrid) {
            TableView::clearGrid($this->nameForm . '_' . $this->gridName);
        }

        $ita_grid01 = new TableView($this->nameForm . '_' . $this->gridName, array(
            'sqlDB' => $db,
            'sqlQuery' => strpos(strtoupper($sql), 'ORDER') ? substr($sql, 0, strpos(strtoupper($sql), 'ORDER')) : $sql,
            'sqlParams' => $sqlParams
                ), null, null, $sortIndex, $sortField
        );
        $this->initGridProps($ita_grid01);

        return $ita_grid01;
    }

    /*
     * ritorna l'oggetto TableView inizializzato con la lista passata
     */

    public function initializeTableArray($records, $sortIndex = '', $sortField = '', $clearGrid = true) {
        if ($clearGrid) {
            TableView::clearGrid($this->nameForm . '_' . $this->gridName);
        }
        $ita_grid01 = new TableView($this->nameForm . '_' . $this->gridName, array(
            'arrayTable' => $records
                ), null, null, $sortIndex, $sortField);
        $this->initGridProps($ita_grid01);

        return $ita_grid01;
    }

    private function initGridProps(&$ita_grid01) {
        $ita_grid01->setPageNum(isset($_POST['page']) ? $_POST['page'] : 1);
        $pageRows = (isset($_POST['rows']) ? $_POST['rows'] : $_POST[$this->nameForm . '_' . $this->gridName]['gridParam']['rowNum']);
        $ita_grid01->setPageRows($pageRows ? $pageRows : $this->getDefaultRows());
    }

    /**
     * Legge dati
     * @param object $ita_grid jqGrid
     * @param array $Result_tab Risultati dopo un'elaborazione della griglia
     * @return Dati
     */
    public function getDataPage($ita_grid, $Result_tab = null) {
        if ($Result_tab === null) {
            return $ita_grid->getDataPage('json');
        } else {
            return $ita_grid->getDataPageFromArray('json', $Result_tab);
        }
    }

    public function putExternalParams($externalParams) {
        $filtri = array();
        if ($externalParams) {
            foreach ($externalParams as $key => $value) {
                if (is_array($value)) {
                    $filtri[$key] = $value['VALORE'];
                } else {
                    $filtri[$key] = $value;
                }
            }
        }

        return $filtri;
    }

    /**
     * Effettua integrazione dei filtri su tab ricerca con quelli impostati nella grid
     * @param array $filtri Filtri impostati nell afinestra di ricerca
     */
    public function compilaFiltri($gridFilters, &$filtri) {
        if (count($gridFilters) > 0) {
            foreach ($gridFilters as $key => $value) {
                $filtri[$key] = $value;
            }
        }
    }

    /**
     * @return array $generator Componenti grafici usati sulla tab di ricerca
     */
    public function getSearchElement($containers=array('workSpace', 'divRicerca'), $showAttribute = true) {
        $generator = new itaGenerator();
        return $generator->getElementsFromForm($this->getModelName(), $containers, array(), array(), true, $showAttribute);
    }

    /**
     * @return array $elements contenente tutti i lookup della pagina 
     */
    public function getLookupElements() {
        $generator = new itaGenerator();
        $array = $generator->getElementsFromForm($this->getNameForm(), array('workSpace'), array('div'), array("lookup"), true, true);
        $result = array();
        foreach ($array as $key => $value) {
            $result[$key] = $value["@NODE@"];
        }
        return $result;
    }

    /**
     * Effettua il salvataggio dei parametri di ricerca per l'utente corrente e modelKey
     */
    public function salvaParametriRicerca($modelName=null, $containers=array('workSpace', 'divRicerca')) {
        try{
            if(!isSet($modelName)){
                $modelName = $this->modelName;
            }

            $elements = $this->getSearchElement($containers);

            $toManage = array();
            foreach ($elements as $key => $value) {
                switch($value['@ATTRS@']['tipo_nome']){
                    case 'ita-decode':
                    case 'ita-readonly':
                    case 'ita-span':
                        continue(2);
                }

                preg_match('/^([A-Za-z0-9_]*)((?:\[[A-Za-z0-9_]*\])*)$/', $key, $matches);
                $keys = array($matches[1]);
                $value = $_POST[$this->nameForm . '_' . $matches[1]];
                while(preg_match('/^(?:\[([A-Za-z0-9_]*)\])(.*?)$/', $matches[2], $matches)){
                    $keys[] = $matches[1];
                    $value = $value[$matches[1]];
                }

                $build = array(array_pop($keys)=>$value);
                while(count($keys) > 0){
                    $build = array(array_pop($keys)=>$build);
                }

                $toManage = array_merge_recursive($toManage, $build);
            }
            if (!empty($toManage)) {
                $json = itaJSON::json_encode($toManage);
            }
            $filtri = array(
                'MODELKEY' => $modelName,
                'KCODUTE' => cwbParGen::getUtente()
            );
    //        $libDB = new cwbLibDB_GENERIC('ITALWEB');
    //        $libDB = new cwbLibDB_BWE();
    //        $toManage = $libDB->leggiBweFrmpar($filtri, false);
            $libDB = new cwbLibDB_GENERIC(self::PARAM_DB);
            $toManage = $libDB->leggiGeneric(self::PARAM_TABLE, $filtri, false);
//            $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName(self::PARAM_TABLE));

            try{
                ItaDB::DBBeginTransaction($libDB->getCitywareDB());
            } catch (Exception $ex) {}
            if (empty($json)) {
                if ($toManage) {
                    ItaDB::DBDelete($libDB->getCitywareDB(), self::PARAM_TABLE, 'ID', $toManage['ID']);
//                    $modelService->deleteRecord($libDB->getCitywareDB(), self::PARAM_TABLE, $toManage);
                }
            } else {
                $modelServiceData = new itaModelServiceData(new cwbModelHelper());            
                if ($toManage) {
                    $toUpdate = $toManage;
                    $toUpdate['PARAMETRI'] = $json;
                    ItaDB::DBUpdate($libDB->getCitywareDB(), self::PARAM_TABLE, 'ID', $toUpdate, $toManage);
//                    $modelServiceData->addMainRecord(self::PARAM_TABLE, $toUpdate);
//                    $modelService->updateRecord($libDB->getCitywareDB(), self::PARAM_TABLE, $modelServiceData->getData(), '', $toManage);
                } else {
                    $toSave = array(
                        'MODELKEY' => $modelName,
                        'KCODUTE' => cwbParGen::getUtente(),
                        'PARAMETRI' => $json
                    );
                    ItaDB::DBInsert($libDB->getCitywareDB(), self::PARAM_TABLE, 'ID', $toSave);
//                    $modelServiceData->addMainRecord(self::PARAM_TABLE, $toSave);
//                    $modelService->insertRecord($libDB->getCitywareDB(), self::PARAM_TABLE, $modelServiceData->getData(), '');
                }
            }
            try{
                ItaDB::DBCommitTransaction($libDB->getCitywareDB());
            } catch (Exception $ex) {}
        }
        catch(Exception $e){
            Out::msgStop('Attenzione', 'Il salvataggio dei parametri di ricerca non è correttamente configurato, '
                    . 'questo non pregiudicherà il corretto comportamento dell\'applicativo. '
                    . 'Contattare l\'assistenza per risolvere il problema');
        }
    }

    /**
     * Effettua il caricamento dei parametri salvati per utente e modelKey
     */
    public function caricaParametriRicerca($modelName=null) {
        try{
            if(!isSet($modelName)){
                $modelName = $this->modelName;
            }

            $filtri = array(
                'MODELKEY' => $modelName,
                'KCODUTE' => cwbParGen::getUtente()
            );
    //        $libDB = new cwbLibDB_BWE();
    //        $rec = $libDB->leggiBweFrmpar($filtri, false);
            $libDB = new cwbLibDB_GENERIC(self::PARAM_DB);
            $rec = $libDB->leggiGeneric(self::PARAM_TABLE, $filtri, false);

            if ($rec) {
                $json = (is_resource($rec['PARAMETRI']) ? stream_get_contents($rec['PARAMETRI'], -1) : $rec['PARAMETRI']);
    //            $params = itaJSON::json_decode($rec['PARAMETRI']);
                $params = itaJSON::json_decode($json);

                if(!empty($params)){
                    Out::recursiveValori($params, $this->nameForm);

                    $postParams = array();
                    foreach($params as $k=>$v){
                        $postParams[$this->nameForm . '_' . $k] = $v;
                    }
                    $_POST = array_merge_recursive($_POST, $postParams);
                }

                return $params;
            }
            return null;
        }
        catch(Exception $e){
            return null;
        }
    }

    /**
     * Stampa
     * @param int $strategy Strategy utilizzata per la stampa
     * @param string $model Nome model
     * @param array $params Parametri stampa
     */
    public function stampa($strategy, $model, $params) {
        switch ($strategy) {
            case self::PRINT_STRATEGY_OMNIS:
                return $this->stampaOmnis($params);
            case self::PRINT_STRATEGY_JASPER:
                return $this->stampaJasper($model, $params);
        }
    }

    private function stampaOmnis($params) {
        $omnisClient = new itaOmnisClient();
        $result = $omnisClient->callExecute('OBJ_BGE_PHP_REPORT', 'print_report', $params, 'CITYWARE', false);
        $esito = $omnisClient->getResponseFileFromOmnis($result, $files);
        if ($esito == false) {
            Out::msgStop('Errore Omnis', $result['RESULT']['MESSAGE']);
            return false;
        } else {
            cwbLib::apriVisualizzatoreDocumenti($files);
        }
        return true;
    }

    private function stampaJasper($model, $params) {
        $itaJR = new itaJasperReport();
        $itaJR->runSQLReportPDF($this->getDb(), $model, $params, true, 'none', $reportPath);
        cwbLib::apriVisualizzatoreDocumenti(array(
            array(
                "NOME" => $reportPath
            )
        ));
        return true;
    }

    public function getValidationMessage($validationInfo, $tableName=null, $ownerTableName) {
        $findError = false;
        foreach ($validationInfo as $currentInfo) {
            if ($currentInfo['level'] === itaModelValidator::LEVEL_ERROR) {
                $findError = true;
                if(isSet($tableName) && strtolower($tableName) != strtolower($ownerTableName)){
                    $msgError .= "Tabella: $tableName - ";
                }
                $msgError .= ($line != 0 ? "Riga: $line - " : "");
                $msgError .= $currentInfo['msg'] . '<br/>';
                // se ci sono dei warning a cui rispondere                       
            }
        }
        if ($findError === false) {
            $findWarning = false;
            foreach ($validationInfo as $currentInfo) {
                if ($currentInfo['level'] === itaModelValidator::LEVEL_WARNING) {
                    $findWarning = true;
                    $msgWarning .= (strtolower($tableName) != strtolower($ownerTableName) ? "Tabella: $tableName - " : "");
                    $msgWarning .= ($line != 0 ? "Riga: $line - " : "");
                    $msgWarning .= $currentInfo['msg'] . '<br/>';
                }
            }
            if ($findWarning) {
                $msg = array(itaModelValidator::LEVEL_WARNING => $msgWarning);
            }
        } else {
            $msg = array(itaModelValidator::LEVEL_ERROR => $msgError);
        }
        return $msg;
    }

    /**
     * Esportazione dati su foglio elettronico
     * @param String $sql Stringa sql
     * @param Array $sqlParams Parametri query
     * @param Array $headers Definizione intestazioni per esportazione
     */
    public function exportXLS($sql, $sqlParams, $headers = array()) {
        $ita_grid01 = new TableView($this->nameForm . '_' . $this->GRID_NAME, array(
            'sqlDB' => $this->db,
            'sqlQuery' => $sql,
            'sqlParams' => $sqlParams));
        $ita_grid01->setPageNum(isset($_POST['page']) ? $_POST['page'] : 1);
        $pageRows = isset($_POST['rows']) ? $_POST['rows'] : $_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['rowNum'];
        $ita_grid01->setPageRows($pageRows ? $pageRows : $this->getDefaultRows());

        // TODO: SISTEMARE SORT
        //$this->setSortParameter($ita_grid01);

        $ita_grid01->setXLSHeaders($headers);
        $ita_grid01->exportXLS('', $this->nameForm . '.xls');
    }

    public function exportXLSRecords($records, $headers = array()) {
        $ita_grid01 = $this->initializeTableArray($records);

        $ita_grid01->setXLSHeaders($headers);
        $ita_grid01->exportXLS('', $this->nameForm . '.xls');
    }

    public function getNameForm() {
        return $this->nameForm;
    }

    public function getModelName() {
        return $this->modelName;
    }

    public function getGridName() {
        return $this->gridName;
    }

    public function setNameForm($nameForm) {
        $this->nameForm = $nameForm;
    }

    public function setModelName($modelName) {
        $this->modelName = $modelName;
    }

    public function setGridName($gridName) {
        $this->gridName = $gridName;
    }

    public function getDb() {
        return $this->db;
    }

    public function setDb($db) {
        $this->db = $db;
    }

    public function getDefaultRows() {
        return ($this->defaultRows ? $this->defaultRows : self::DEFAULT_ROWS);
    }

    public function setDefaultRows($defaultRows) {
        $this->defaultRows = $defaultRows;
    }

    function getAreeCityware() {
        return $this->areeCityware;
    }


}
