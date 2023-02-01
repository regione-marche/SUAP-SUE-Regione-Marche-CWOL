<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';

function cwbBgeAgidLog() {
    $cwbBgeAgidLog = new cwbBgeAgidLog();
    $cwbBgeAgidLog->parseEvent();
    return;
}

class cwbBgeAgidLog extends cwbBpaGenTab {

    function initVars() {
        $this->nameForm = 'cwbBgeAgidLog';
        $this->GRID_NAME = 'gridBgeAgidLog';
        $this->skipAuth = true;
        $this->libDB = new cwbLibDB_BGE();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
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

    protected function postApriForm() {
        $this->initComboLivello();
        $this->initComboEsito();
        $this->initComboOperazione();
        Out::valore($this->nameForm . '_DATAOPER_da', date('dmY'));
        Out::valore($this->nameForm . '_DATAOPER_a', date('dmY'));
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri = array();
        $filtri['PROGKEYTAB'] = trim($this->formData[$this->nameForm . '_PROGKEYTAB']);
        $filtri['ESITO'] = trim($this->formData[$this->nameForm . '_ESITO']);
        $filtri['LIVELLO'] = trim($this->formData[$this->nameForm . '_LIVELLO']);
        $filtri['OPERAZIONE'] = trim($this->formData[$this->nameForm . '_OPERAZIONE']);
        $filtri['DATAOPER_da'] = trim($this->formData[$this->nameForm . '_DATAOPER_da']);
        $filtri['DATAOPER_a'] = trim($this->formData[$this->nameForm . '_DATAOPER_a']);
        $this->SQL = $this->libDB->getSqlLeggiBgeAgidLog($filtri, true, $sqlParams);
    }

    public function dettaglio() {
        
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PROGKEYTAB_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGKEYTAB']);

            switch ($Result_rec['LIVELLO']) {
                case 1:
                    $Result_tab[$key]['LIVELLO'] = 'Off';
                    break;
                case 2:
                    $Result_tab[$key]['LIVELLO'] = 'Fatal';
                    break;
                case 3:
                    $Result_tab[$key]['LIVELLO'] = 'Error';
                    break;
                case 4:
                    $Result_tab[$key]['LIVELLO'] = 'Warn';
                    break;
                case 5:
                    $Result_tab[$key]['LIVELLO'] = 'Info';
                    break;
                case 6:
                    $Result_tab[$key]['LIVELLO'] = 'Debug';
                    break;
                case 7:
                    $Result_tab[$key]['LIVELLO'] = 'All';
                    break;
            }

            switch ($Result_rec['ESITO']) {
                case 1:
                    $Result_tab[$key]['ESITO'] = 'Operazione Conclusa con elaborazione dati';
                    break;
                case 2:
                    $Result_tab[$key]['ESITO'] = 'Operazione Conclusa senza elaborazione dati';
                    break;
                case 3:
                    $Result_tab[$key]['ESITO'] = 'Operazione Interrotta';
                    break;
            }

            switch ($Result_rec['OPERAZIONE']) {
                case 0:
                    $Result_tab[$key]['OPERAZIONE'] = 'Inserimento su BGE_AGID_SCADENZE';
                    break;
                case 1:
                    $Result_tab[$key]['OPERAZIONE'] = 'Fornitura Pubblicazione';
                    break;
                case 2:
                    $Result_tab[$key]['OPERAZIONE'] = 'Fornitura Cancellazione';
                    break;
                case 11:
                    $Result_tab[$key]['OPERAZIONE'] = 'Ricevuta Accettazione Pubblicazione';
                    break;
                case 12:
                    $Result_tab[$key]['OPERAZIONE'] = 'Ricevuta Pubblicazione';
                    break;
                case 13:
                    $Result_tab[$key]['OPERAZIONE'] = 'Ricevuta Pubblicazione Arricchita';
                    break;
                case 14:
                    $Result_tab[$key]['OPERAZIONE'] = 'Ricevuta Cancellazione';
                    break;
                case 15:
                    $Result_tab[$key]['OPERAZIONE'] = 'Rendicontazione';
                    break;
                case 16:
                    $Result_tab[$key]['OPERAZIONE'] = 'Ricevuta Accettazione Cancellazione';
                    break;
                case 21:
                    $Result_tab[$key]['OPERAZIONE'] = 'Riconciliazione in tempo reale';
                    break;
                case 22:
                    $Result_tab[$key]['OPERAZIONE'] = 'Riconciliazione in differita';
                    break;
            }
        }
        return $Result_tab;
    }

    private function initComboLivello() {
        // Livello 
        Out::select($this->nameForm . '_LIVELLO', 1, "0", 1, "");
        Out::select($this->nameForm . '_LIVELLO', 1, "1", 0, "Off");
        Out::select($this->nameForm . '_LIVELLO', 1, "2", 0, "Fatal");
        Out::select($this->nameForm . '_LIVELLO', 1, "3", 0, "Error");
        Out::select($this->nameForm . '_LIVELLO', 1, "4", 0, "Warn");
        Out::select($this->nameForm . '_LIVELLO', 1, "5", 0, "Info");
        Out::select($this->nameForm . '_LIVELLO', 1, "6", 0, "Debug");
        Out::select($this->nameForm . '_LIVELLO', 1, "7", 0, "All");
    }

    private function initComboOperazione() {
        // Operazione 
        Out::select($this->nameForm . '_OPERAZIONE', 1, "", 0, "");
        Out::select($this->nameForm . '_OPERAZIONE', 1, "0", 1, "Inserimento su BGE_AGID_SCADENZE");
        Out::select($this->nameForm . '_OPERAZIONE', 1, "1", 0, "Fornitura Pubblicazione");
        Out::select($this->nameForm . '_OPERAZIONE', 1, "2", 0, "Fornitura Cancellazione");
        Out::select($this->nameForm . '_OPERAZIONE', 1, "11", 0, "Ricevuta Accettazione Pubblicazione");
        Out::select($this->nameForm . '_OPERAZIONE', 1, "12", 0, "Ricevuta Pubblicazione");
        Out::select($this->nameForm . '_OPERAZIONE', 1, "13", 0, "Ricevuta Pubblicazione Arricchita");
        Out::select($this->nameForm . '_OPERAZIONE', 1, "14", 0, "Ricevuta di Cancellazione");
        Out::select($this->nameForm . '_OPERAZIONE', 1, "15", 0, "Rendicontazione");
        Out::select($this->nameForm . '_OPERAZIONE', 1, "16", 0, "Ricevuta Accettazione Cancellazione");
        Out::select($this->nameForm . '_OPERAZIONE', 1, "21", 0, "Riconciliazione in tempo reale");
        Out::select($this->nameForm . '_OPERAZIONE', 1, "22", 0, "Riconciliazione in differita");
    }

    private function initComboEsito() {
        // Esito 
        Out::select($this->nameForm . '_ESITO', 1, "0", 1, "");
        Out::select($this->nameForm . '_ESITO', 1, "1", 0, "Operazione conclusa con elaborazione dati");
        Out::select($this->nameForm . '_ESITO', 1, "2", 0, "Operazione conclusa senza elaborazione dati");
        Out::select($this->nameForm . '_ESITO', 1, "3", 0, "Operazione interrotta");
    }

}

?>