<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BWE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBweBnksrv() {
    $cwbBweBnksrv = new cwbBweBnksrv();
    $cwbBweBnksrv->parseEvent();
    return;
}

class cwbBweBnksrv extends cwbBpaGenTab {

    const TABELLA_BNKSRV = 'BWE_BNKSRV';
    const TABELLA_TIPBNK = 'BWE_TIPBNK';
    const GRID_BNKSRV = 'gridBweBnksrv';
    const GRID_TIPBNK = 'gridBweTipbnk';

    private $tipbnk;

    function initVars() {
        $this->GRID_NAME = 'gridBweBnksrv';
        $this->libDB = new cwbLibDB_BWE();
        $this->errorOnEmpty = false;
        $this->searchOpenElenco = true;
        $this->skipAuth = true;
        $this->actionAfterNew = 4;
        $this->tipbnk = cwbParGen::getFormSessionVar($this->nameForm, '_tipbnk');
    }

    protected function postDestruct() {
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, '_tipbnk', $this->tipbnk);
        }
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaCancellazione':
                        $this->confermaCancellazione();
                        $this->tornaAElenco();
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . self::GRID_TIPBNK:
                        $this->cancellaTipbnk($_POST[$this->nameForm . '_' . self::GRID_TIPBNK]['gridParam']['selrow']);
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . self::GRID_TIPBNK:
                        $this->caricaGridTipbnk();
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . self::GRID_TIPBNK:
                        cwbLib::apriFinestraRicerca('cwbBweTippen', $this->nameForm, 'returnFromBweTippen', $_POST['id'], true, null, $this->nameFormOrig);
                        break;
                }
                break;
            case 'returnFromBweTippen':
                switch ($this->elementId) {
                    case $this->nameForm . '_' . self::GRID_TIPBNK:
                        $this->tipbnk[] = array('CODTIPSCAD' => $this->formData['returnData']['CODTIPSCAD'], 'SUBTIPSCAD' => $this->formData['returnData']['SUBTIPSCAD']);
                        $this->elencaDaArray($this->tipbnk);
                        break;
                }
                break;
        }
    }

    protected function preNuovo() {
        $this->pulisciCampi();
    }

    protected function postApriForm() {
        $this->initComboTipServ();
    }

    protected function postAltraRicerca() {
        
    }

    protected function postAggiungi() {
        Out::show($this->nameForm . '_divGridTipbnk');
    }

    protected function aggiungi($validate = true) {
        cwbDBRequest::getInstance()->startManualTransaction(null, $this->MAIN_DB);
        $errore = false;
        try {
            $recordBnksrv = $_POST[$this->nameForm . '_' . $this->TABLE_NAME];
            $this->insertUpdateRecord($recordBnksrv, 'BWE_BNKSRV', true, true, $this->MAIN_DB);
            if ($this->tipbnk) {
                foreach ($this->tipbnk as $key => $tipbnk) {
                    $tipbnk['IDBNKSRV'] = $recordBnksrv['IDBNKSRV'];
                    $this->insertUpdateRecord($tipbnk, 'BWE_TIPBNK', true, true, $this->MAIN_DB);
                }
            }
        } catch (Exception $exc) {
            $errore = true;
        }
        if (!$errore) {
            Out::msgInfo('Attenzione', 'Salvataggio avvenuto con Successo!');
            cwbDBRequest::getInstance()->commitManualTransaction();
        } else {
            Out::msgInfo('Attenzione', 'Errore Salvataggio: ' . $exc->getMessage());
            cwbDBRequest::getInstance()->rollBackManualTransaction();
        }
    }

    protected function aggiorna($validate = true) {
        cwbDBRequest::getInstance()->startManualTransaction(null, $this->MAIN_DB);
        try {
            $recordBnksrv = $_POST[$this->nameForm . '_' . $this->TABLE_NAME];
            $this->insertUpdateRecord($recordBnksrv, 'BWE_BNKSRV', false, true, $this->MAIN_DB);
            if ($this->tipbnk) {
                foreach ($this->tipbnk as $key => $tipbnk) {
                    $filtri['IDBNKSRV'] = $recordBnksrv['IDBNKSRV'];
                    $filtri['CODTIPSCAD'] = $tipbnk['CODTIPSCAD'];
                    $filtri['SUBTIPSCAD'] = $tipbnk['SUBTIPSCAD'];
                    $selectTipbnk = $this->libDB->leggiBweTipbnk($filtri, false);
                    if ($selectTipbnk) {
                        continue;
                    } else {
                        $tipbnk['IDBNKSRV'] = $recordBnksrv['IDBNKSRV'];
                        $this->insertUpdateRecord($tipbnk, 'BWE_TIPBNK', true, true, $this->MAIN_DB);
                    }
                }
            }
        } catch (Exception $exc) {
            $errore = true;
        }
        if (!$errore) {
            Out::msgInfo('Attenzione', 'Salvataggio avvenuto con Successo!');
            cwbDBRequest::getInstance()->commitManualTransaction();
        } else {
            Out::msgInfo('Attenzione', 'Errore Salvataggio: ' . $exc->getMessage());
            cwbDBRequest::getInstance()->rollBackManualTransaction();
        }
    }

    protected function cancella() {
        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellazione', 'model' => $this->nameForm, 'shortCut' => "f5")
        ));
    }

    protected function confermaCancellazione() {
        cwbDBRequest::getInstance()->startManualTransaction(null, $this->MAIN_DB);
        try {
            $recordBnksrv = $_POST[$this->nameForm . '_' . $this->TABLE_NAME];
            $this->customDeleteRecord($recordBnksrv, 'BWE_BNKSRV');
            $filtri['IDBNKSRV'] = $recordBnksrv['IDBNKSRV'];
            $tipbnkColleg = $this->libDB->leggiBweTipbnk($filtri, true);
            if ($tipbnkColleg) {
                foreach ($tipbnkColleg as $key => $tipbnk) {
                    $this->customDeleteRecord($tipbnk, 'BWE_TIPBNK');
                }
            }
        } catch (Exception $exc) {
            $errore = true;
        }
        if (!$errore) {
            Out::msgInfo('Attenzione', 'Cancellazione avvenuta con Successo!');
            cwbDBRequest::getInstance()->commitManualTransaction();
        } else {
            Out::msgInfo('Attenzione', 'Errore Cancellazione: ' . $exc->getMessage());
            cwbDBRequest::getInstance()->rollBackManualTransaction();
        }
    }

    private function cancellaTipbnk($selRow) {
        $rowToDelete = $this->trovaRecordSelezionato($selRow);
        if ($rowToDelete) {
            $this->customDeleteRecord($rowToDelete, 'BWE_TIPBNK', false);
            $this->caricaGridTipbnk($rowToDelete['IDBNKSRV']);
        }
    }

    private function insertUpdateRecord($toInsert, $tableName, $insert = true, $startedTransaction = false, $db = null) {
        if (!$db) {
            $db = ItaDB::DBOpen(cwbLib::getCitywareConnectionName(), '');
        }
        $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableName), true, $startedTransaction);
        $modelServiceData = new itaModelServiceData(new cwbModelHelper());
        $modelServiceData->addMainRecord($tableName, $toInsert);
        $recordInfo = itaModelHelper::impostaRecordInfo(($insert ? itaModelService::OPERATION_INSERT : itaModelService::OPERATION_UPDATE), 'cwbBweBnksrv', $toInsert);
        if ($insert) {
            $modelService->insertRecord($db, $tableName, $modelServiceData->getData(), $recordInfo);
        } else {
            $modelService->updateRecord($db, $tableName, $modelServiceData->getData(), $recordInfo);
        }
        return $modelService->getLastInsertId();
    }

    private function customDeleteRecord($toDelete, $tableName, $startedTransaction = true) {
        $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableName), true, $startedTransaction);
        $modelServiceData = new itaModelServiceData(new cwbModelHelper());
        $modelServiceData->addMainRecord($tableName, $toDelete);
        $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_DELETE, $this->nameForm, $toDelete);
        $modelService->deleteRecord($this->MAIN_DB, $tableName, $modelServiceData->getData(), $recordInfo);
    }

    protected function tornaAElenco() {
        parent::tornaAElenco();
        $this->tipbnk = array();
        $this->elenca();
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['IDBNKSRV_formatted'] != '') {
            $this->gridFilters['IDBNKSRV'] = $this->formData['IDBNKSRV_formatted'];
        }
        if ($_POST['NOMESERVIC'] != '') {
            $this->gridFilters['NOMESERVIC'] = $this->formData['NOMESERVIC'];
        }
        if ($_POST['DESCRBANCA'] != '') {
            $this->gridFilters['DESCRBANCA'] = $this->formData['DESCRBANCA'];
        }
    }

    private function trovaRecordSelezionato($rowId) {
        foreach ($this->tipbnk as $record) {
            if ($record['RANDOMID'] == $rowId) {
                return $record;
            }
        }
        return null;
    }

    protected function preAggiorna() {
        $this->formDataToCurrentRecord();
    }

    protected function postNuovo() {
        $this->pulisciCampi();
        $id = cwbLibCalcoli::trovaProgressivo('IDBNKSRV', 'BWE_BNKSRV');
        Out::valore($this->nameForm . '_BWE_BNKSRV[IDBNKSRV]', $id);
        Out::show($this->nameForm . '_Torna');
        TableView::clearGrid($this->nameForm . '_' . self::GRID_TIPBNK);
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postPulisciCampi() {
        
    }

    private function caricaGridTipbnk($id = null) {
        $filtri['IDBNKSRV'] = $id;
        $this->tipbnk = $this->libDB->leggiBweTipbnk($filtri, TRUE);
        foreach ($this->tipbnk as $key => $tipbnk) {
            $this->tipbnk[$key]['RANDOMID'] = rand(1000, 99999) * rand(500, 3000) + rand(1000, 99999);
        }
        if ($this->tipbnk) {
            $this->elencaDaArray($this->tipbnk);
        }
    }

    private function initComboTipServ() {
        // Azzera combo
        Out::html($this->nameForm . '_BWE_BNKSRV[TIPOSERVIC]', '');

        Out::select($this->nameForm . '_BWE_BNKSRV[TIPOSERVIC]', 1, 1, 1, 'Carta di credito');
        Out::select($this->nameForm . '_BWE_BNKSRV[TIPOSERVIC]', 1, 2, 0, 'Conto corrente bancario');
        Out::select($this->nameForm . '_BWE_BNKSRV[TIPOSERVIC]', 1, 3, 0, 'Paypal');
        Out::select($this->nameForm . '_BWE_BNKSRV[TIPOSERVIC]', 1, 4, 0, 'Poste Pay');
    }

    private function elencaDaArray($records) {
        TableView::clearGrid($this->nameForm . '_' . self::GRID_TIPBNK);
        $this->switchGridName(self::GRID_TIPBNK);
        $ita_grid = $this->helper->initializeTableArray($records);
        if ($this->helper->getDataPage($ita_grid)) {
            TableView::enableEvents($this->nameForm . '_' . $this->helper->getGridName());
        }
        $this->switchGridName(self::GRID_BNKSRV);
    }

    private function switchGridName($gridName = self::GRID_BNKSRV) {
        $this->helper->setGridName($gridName);
        $this->GRID_NAME = $gridName;
        if ($gridName === self::GRID_BNKSRV) {
            $tableName = self::TABELLA_BNKSRV;
        } else if ($gridName === self::GRID_TIPBNK) {
            $tableName = self::TABELLA_TIPBNK;
        }
        $this->TABLE_VIEW = $tableName;
        $this->TABLE_NAME = $tableName;
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['IDBNKSRV'] = trim($this->formData[$this->nameForm . '_IDBNKSRV']);
        $filtri['NOMESERVIC'] = trim($this->formData[$this->nameForm . '_NOMESERVIC']);
        $filtri['DESCRBANCA'] = trim($this->formData[$this->nameForm . '_DESCRBANCA']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBweBnksrv($filtri, false, $sqlParams, true);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBweBnksrvChiave($index, $sqlParams);
    }

    protected function postDettaglio($index, &$sqlDettaglio = null) {
        $this->caricaGridTipbnk($index);
        Out::show($this->nameForm . '_divGridTipbnk');
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['IDBNKSRV_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['IDBNKSRV']);
            switch ($Result_tab[$key]['TIPOSERVIC']) {
                case 1:
                    $Result_tab[$key]['TIPOSERVIC'] = 'Carta di credito';
                    break;
                case 2:
                    $Result_tab[$key]['TIPOSERVIC'] = 'Conto corrente bancario';

                    break;
                case 3:
                    $Result_tab[$key]['TIPOSERVIC'] = 'Paypal';

                    break;
                case 4:
                    $Result_tab[$key]['TIPOSERVIC'] = 'Poste Pay';

                    break;
            }
        }
        return $Result_tab;
    }

}

?>