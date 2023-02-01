<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';

function cwbBtaRendLog() {
    $cwbBtaRendLog = new cwbBtaRendLog();
    $cwbBtaRendLog->parseEvent();
    return;
}

class cwbBtaRendLog extends cwbBpaGenTab {

    const GRID_LOG_NAME = 'gridBtaRendLog';
    const GRID_LOGD_NAME = 'gridBtaRendLogd';
    const TABLE_LOG_NAME = 'BTA_REND_LOG';
    const TABLE_LOGD_NAME = 'BTA_REND_LOGD';

    function initVars() {
        $this->skipAuth = true;
        $this->GRID_NAME = self::GRID_LOG_NAME;
        $this->libDB = new cwbLibDB_BTA();
        $this->searchOpenElenco = true;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onSelectRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . self::GRID_LOG_NAME:
                        // alla selezione della grid principale, carico la grid di dettaglio
                        $this->filtriEsterni = $_POST['rowid'];
                        $this->caricaGridLogd($_POST['rowid'], true);
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . self::GRID_LOG_NAME:
                        $this->caricaGridLog();
                        break;
                    case $this->nameForm . '_' . self::GRID_LOGD_NAME:
                        $this->caricaGridLogd($this->filtriEsterni);
                        break;
                }
                break;
        }
    }

    protected function postApriForm() {
        $this->elenca();
        Out::setFocus("", $this->nameForm . '_DATAINIZREND');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DATAINIZREND');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    private function caricaGridLog($id = null, $new = false) {
        // cambio grid name per utilizzare i metodi della superclasse senza duplicarli (es. elenca)
        $this->switchGridName(self::GRID_LOG_NAME);
        $this->elenca(false);
    }

    private function caricaGridLogd($id = null) {
        TableView::clearGrid($this->nameForm . '_' . self::GRID_LOGD_NAME);
        // cambio grid name per utilizzare i metodi della superclasse senza duplicarli
        $this->switchGridName(self::GRID_LOGD_NAME);

        $this->elenca(false);
    }
    
    protected function nessunRecordMessage() {
        // la seconda grid non da messaggi se non ci sono record
        if ($this->GRID_NAME === self::GRID_LOGD_NAME) {
            return;
        }
    }

    protected function elaboraRecords($records) {
        if ($this->GRID_NAME === self::GRID_LOG_NAME) {
            foreach ($records as $key => $record) {
                $records[$key]['ID_formatted'] = cwbLibHtml::formatDataGridCod($records[$key]['ID']);

                switch ($records[$key]['ESITO']) {
                    case 0:
                        $records[$key]['ESITO'] = "Errore nell'esecuzione del metodo";
                        break;
                    case 1:
                        $records[$key]['ESITO'] = 'Il metodo è andato a buon fine';
                        break;
                }
            }
        } else {
            foreach ($records as $key => $record) {
                $records[$key]['DATAFINE_LOGD'] = $records[$key]['DATAFINE'];
                switch ($records[$key]['ESITO']) {
                    case 0:
                        $records[$key]['ESITO_LOGD'] = "Errore nell'esecuzione del metodo";
                        break;
                    case 1:
                        $records[$key]['ESITO_LOGD'] = 'Il metodo è andato a buon fine';
                        break;
                }
                $records[$key]['CODUTE_LOGD'] = $records[$key]['CODUTE'];
                $records[$key]['TIMEOPER_LOGD'] = $records[$key]['TIMEOPER'];
                $records[$key]['DATAOPER_LOGD'] = $records[$key]['DATAOPER'];
            }
        }


        return $records;
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        if ($this->GRID_NAME === self::GRID_LOG_NAME) {
            $filtri['DATAINIZIO'] = trim($this->formData[$this->nameForm . '_DATAINIZIO']);
            $filtri['DATAFINE'] = trim($this->formData[$this->nameForm . '_DATAFINE']);
            //$filtri['ESITO'] = trim($this->formData[$this->nameForm . '_ESITO']);
            $this->compilaFiltri($filtri);
            $this->SQL = $this->libDB->getSqlLeggiBtaRendLog($filtri, false, $sqlParams);
        } else if ($this->GRID_NAME === self::GRID_LOGD_NAME) {
            $tableName = self::TABLE_LOGD_NAME;
            if ($this->filtriEsterni) {
                $filtri['ID_RENDLOG'] = $this->filtriEsterni;
            }
            $this->compilaFiltri($filtri);
            $this->SQL = $this->libDB->getSqlLeggiBtaRendLogd($filtri, false, $sqlParams);
        }
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaRendLogChiave($index, $sqlParams);
    }

    private function switchGridName($gridName = self::GRID_LOG_NAME) {
        $this->helper->setGridName($gridName);
        $this->GRID_NAME = $gridName;
        if ($gridName === self::GRID_LOG_NAME) {
            $tableName = self::TABLE_LOG_NAME;
        } else if ($gridName === self::GRID_LOGD_NAME) {
            $tableName = self::TABLE_LOGD_NAME;
        }
        $this->TABLE_VIEW = $tableName;
        $this->TABLE_NAME = $tableName;
    }

}

?>