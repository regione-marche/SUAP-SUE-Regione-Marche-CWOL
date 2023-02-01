<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BWE.class.php';

function cwbBorMaster() {
    $cwbBweUtelog = new cwbBweUtelog();
    $cwbBweUtelog->parseEvent();
    return;
}

class cwbBweUtelog extends cwbBpaGenTab {

    private $operazioni = array(
        1 => 'Notifica utenti in scadenza'
    );

    function initVars() {
        $this->GRID_NAME = 'gridBweUtelog';
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BWE();
    }

    protected function postApriForm() {
        $this->initComboTipoEsito();
        $this->initComboTipoOperazione();
    }

    protected function postDettaglio($index, &$sqlDettaglio = null) {
        Out::codice('tinyActivate("' . $this->nameForm . '_' . $this->TABLE_NAME . '[NOTE]");');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['TIPO_OPERAZIONE'] = $this->formData[$this->nameForm . '_TIPO_OPERAZIONE'];
        $filtri['ESITO'] = $this->formData[$this->nameForm . '_ESITO'];
        $filtri['DATAOPER'] = $this->formData[$this->nameForm . '_DATAOPER'];
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBweUtelog($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBweUtelogChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['TIPO_OPERAZIONE'] = $this->operazioni[$Result_rec['TIPO_OPERAZIONE']];
        }
        return $Result_tab;
    }

    private function initComboTipoOperazione() {
        Out::select($this->nameForm . '_TIPO_OPERAZIONE', 1, null, 1, "---TUTTE---");
        Out::select($this->nameForm . '_' . $this->TABLE_NAME . '[TIPO_OPERAZIONE]', 1, null, 1, "---TUTTE---");

        foreach ($this->operazioni as $operazioneKey => $operazioneDesc) {
            Out::select($this->nameForm . '_TIPO_OPERAZIONE', 1, $operazioneKey, 0, $operazioneDesc);
            Out::select($this->nameForm . '_' . $this->TABLE_NAME . '[TIPO_OPERAZIONE]', 1, $operazioneKey, 0, $operazioneDesc);
        }
    }

    private function initComboTipoEsito() {
        Out::select($this->nameForm . '_ESITO', 1, null, 1, "---TUTTE---");
        Out::select($this->nameForm . '_ESITO', 1, 0, 0, "Negativo");
        Out::select($this->nameForm . '_ESITO', 1, 1, 0, "Positivo");
    }

}

