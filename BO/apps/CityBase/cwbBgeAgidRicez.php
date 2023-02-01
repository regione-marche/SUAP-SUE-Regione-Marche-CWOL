<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';

function cwbBgeAgidRicez() {
    $cwbBgeAgidRicez = new cwbBgeAgidRicez();
    $cwbBgeAgidRicez->parseEvent();
    return;
}

class cwbBgeAgidRicez extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBgeAgidRicez';
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

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri = array();
        $filtri['IDINV'] = $this->externalParams['IDINV'];
        $this->SQL = $this->libDB->getSqlLeggiBgeAgidRicez($filtri, true, $sqlParams);
    }

    public function dettaglio() {
        
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PROGKEYTAB_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGKEYTAB']);
            $interm = $this->libDB->leggiBgeAgidIntermChiave($Result_rec['INTERMEDIARIO']);
            $Result_tab[$key]['INTERMEDIARIO'] = $interm['DESCRIZIONE'];

            switch ($Result_rec['TIPO']) {
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