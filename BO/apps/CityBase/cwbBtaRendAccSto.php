<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';

function cwbBtaRendAccSto() {
    $cwbBtaRendAccSto = new cwbBtaRendAccSto();
    $cwbBtaRendAccSto->parseEvent();
    return;
}

class cwbBtaRendAccSto extends cwbBpaGenTab {

    function initVars() {
        $this->skipAuth = true;
        $this->GRID_NAME = 'gridBtaRendAccSto';
        $this->libDB = new cwbLibDB_BTA();
        $this->searchOpenElenco = true;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case "cellSelect":
                $rowid = $_POST['rowid'];
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        switch ($_POST['colName']) {
                            case 'DOWNLOAD':
                                $this->download($rowid);
                                break;
                        }
                }
                break;
        }
    }

    private function download($id) {
        $accSto = $this->libDB->leggiBtaRendAccStoChiave($id);
        cwbLib::downloadDocument('Allegato' . time() . '.xlsx', stream_get_contents($accSto['ALLEGATO']), true);
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['ID_RENDL'] = trim($this->formData[$this->nameForm . '_ID_RENDL']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaRendAccSto($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaRendAccStoChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        $path_download = ITA_BASE_PATH . '/apps/CityBase/resources/download-24x24.png';
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['ID_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['ID']);
            $Result_tab[$key]['DOWNLOAD'] = cwbLibHtml::formatDataGridIcon('', $path_download);
//            switch ($Result_tab[$key]['INTERVALLO']) {
//                case 1:
//                    $Result_tab[$key]['INTERVALLO'] = 'Giornaliero';
//                    break;
//                case 2:
//                    $Result_tab[$key]['INTERVALLO'] = 'Settimanale';
//                    break;
//                case 3:
//                    $Result_tab[$key]['INTERVALLO'] = 'Mensile';
//                    break;
//                case 4:
//                    $Result_tab[$key]['INTERVALLO'] = 'Trimestrale';
//                    break;
//                case 5:
//                    $Result_tab[$key]['INTERVALLO'] = 'Annuale';
//                    break;
//            }
            $rendlog = $this->libDB->leggiBtaRendLogChiave($Result_rec['ID_RENDL']);

            $Result_tab[$key]['DATAINIZIO'] = $rendlog['DATAINIZIO'];
            $Result_tab[$key]['DATAFINE'] = $rendlog['DATAFINE'];
        }
        return $Result_tab;
    }

}

?>