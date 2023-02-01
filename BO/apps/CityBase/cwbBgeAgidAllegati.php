<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_LIB_PATH . '/zip/itaZipCommandLine.class.php';

function cwbBgeAgidAllegati() {
    $cwbBgeAgidAllegati = new cwbBgeAgidAllegati();
    $cwbBgeAgidAllegati->parseEvent();
    return;
}

class cwbBgeAgidAllegati extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBgeAgidAllegati';
        $this->skipAuth = true;
        $this->libDB = new cwbLibDB_BGE();
    }

    protected function preConstruct() {
        parent::preConstruct();
    }

    public function __destruct() {
        $this->preDestruct();
        parent::__destruct();
    }

    protected function preDestruct() {
        if ($this->close != true) {
            
        }
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case "cellSelect":
                $rowid = $_POST['rowid'];
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        switch ($_POST['colName']) {
                            case 'DOWNLOAD':
                                $this->scaricaAllegato($rowid);
                                break;
                        }
                }
                break;
        }
    }

    protected function postApriForm() {
        
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri = array();
        $filtri['IDINVRIC'] = $this->externalParams['IDINVRIC'];
        $filtri['TIPO'] = $this->externalParams['TIPO'];
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBgeAgidAllegatiConsoleNodo($filtri, true, $sqlParams);
    }

    public function scaricaAllegato($index) {
        $devLib = new devLib();
        $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);
        $allegato = $this->libDB->leggiBgeAgidAllegatiChiave($index);
        $filename = time();
        $corpo = null;
        $filename = itaLib::getUploadPath() . "/temp" . $filename . '.zip';
        $nomeAllegato = trim($allegato['NOME_FILE']);

        if (intval($configGestBin['CONFIG']) === 0) {
            // Lettura allegato da DB
            file_put_contents($filename, stream_get_contents($allegato['ZIPFILE']));
        } else {
            // Lettura allegato da FS
            $configPathAllegati = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_ALLEG', false);
            file_put_contents($filename, file_get_contents($configPathAllegati['CONFIG'] . "/Allegato_" . $index));
        }

        $extractDir = itaLib::getUploadPath() . '/';
        itaZipCommandLine::unzip($filename, $extractDir);
        $est = '';
        $tipo = intval($allegato['TIPO']);
//        if ($allegato['TIPO'] >= '11' && $allegato['TIPO'] <= '12') {
        if (($tipo == 11 || $tipo == 12 || $tipo == 14 || $tipo == 16) && strpos(trim($allegato['NOME_FILE']),"PubblicazioneNSS_") === false) {
            $est = ".xml";
        }
        $corpo = file_get_contents($extractDir . $nomeAllegato . $est);
        //  if ($allegato['TIPO'] < '10' || $allegato['TIPO'] === '13') {
        if ($tipo < 10 || $tipo == 13) {
            // file .txt
            $estFile = ".txt";
        } else {
            //file .xml
            $estFile = ".xml";
            //salvo il contenuto qui perchè a differenza del .txt, qui nel nome file c'è già l'estensione .xml
            //    $corpo = file_get_contents($extractDir . $nomeAllegato . $estFile);
        }

        if ($corpo) {
            cwbLib::downloadDocument($nomeAllegato . $estFile, $corpo, true);
        } else {
            Out::msgStop("Errore", "Errore reperimento binario");
        }
        unlink($filename);
        unlink($extractDir . '/' . $nomeAllegato . ($allegato['TIPO'] < 10 ? '' : $estFile));
    }

    protected function elaboraRecords($Result_tab) {
        $path_download = ITA_BASE_PATH . '/apps/CityBase/resources/download-24x24.png';

        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PROGKEYTAB_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGKEYTAB']);
            $Result_tab[$key]['DOWNLOAD'] = cwbLibHtml::formatDataGridIcon('', $path_download);
            switch ($Result_rec['TIPO']) {
                case 1:
                    $Result_tab[$key]['TIPO'] = 'Fornitura Pubblicazione';
                    break;
                case 2:
                    $Result_tab[$key]['TIPO'] = 'Fornitura Cancellazione';
                    break;
                case 11:
                    $Result_tab[$key]['TIPO'] = 'Ricevuta Accettazione Pubblicazione';
                    break;
                case 12:
                    $Result_tab[$key]['TIPO'] = 'Ricevuta Pubblicazione';
                    break;
                case 13:
                    $Result_tab[$key]['TIPO'] = 'Ricevuta Pubblicazione Arricchita';
                    break;
                case 14:
                    $Result_tab[$key]['TIPO'] = 'Ricevuta Cancellazione';
                    break;
                case 15:
                    $Result_tab[$key]['TIPO'] = 'Rendicontazione';
                    break;
                case 16:
                    $Result_tab[$key]['TIPO'] = 'Ricevuta Accettazione Cancellazione';
                    break;
            }
        }
        return $Result_tab;
    }

}

?>