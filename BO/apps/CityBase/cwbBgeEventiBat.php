<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbEventiBat.class.php';

function cwbBgeEventiBat() {
    $cwbBgeEventiBat = new cwbBgeEventiBat();
    $cwbBgeEventiBat->parseEvent();
    return;
}

class cwbBgeEventiBat extends cwbBpaGenTab {

    private $tipoEvento = array(
        cwbEventiBat::EVENTO_INIZIO => 'Inizio',
        cwbEventiBat::EVENTO_MESSAGGIO => 'Messaggio',
        cwbEventiBat::EVENTO_CONCLUSIONE_OK => 'Esito Ok',
        cwbEventiBat::EVENTO_CONCLUSIONE_KO => 'Esito Ko',
    );

    protected function initVars() {
        $this->GRID_NAME = 'gridBgeEventiBat';
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BGE();
        $this->elencaAutoAudit = true;
    }

    protected function postApriForm() {
        $this->initComboTipoEvento();
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['PK']) {
            $this->gridFilters['PK'] = $this->formData['PK'];
        }
        if ($_POST['IDELAB']) {
            $this->gridFilters['IDELAB'] = $this->formData['IDELAB'];
        }
    }

    public function postSqlElenca($filtri, &$sqlParams) {
        $filtri['PK'] = trim($this->formData[$this->nameForm . '_PK']);
        $filtri['IDELAB'] = trim($this->formData[$this->nameForm . '_IDELAB']);
        $filtri['TIPOEVENTO'] = trim($this->formData[$this->nameForm . '_TIPOEVENTO']);
        $filtri['CODUTERICH'] = trim($this->formData[$this->nameForm . '_CODUTERICH']);
        $filtri['DATAOPER'] = trim($this->formData[$this->nameForm . '_DATAOPER']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBgeEventiBat($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBgeEventiBatChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $val) {
            $tipoEventoFormat = $this->tipoEvento[$val['TIPOEVENTO']];
            if ($val['TIPOEVENTO'] === cwbEventiBat::EVENTO_CONCLUSIONE_OK) {
                $tipoEventoFormat = '<span style="color:green">' . $tipoEventoFormat . '</span>';
            } else if ($val['TIPOEVENTO'] === cwbEventiBat::EVENTO_CONCLUSIONE_KO) {
                $tipoEventoFormat = '<span style="color:red">' . $tipoEventoFormat . '</span>';
            }
            $Result_tab[$key]['TIPOEVENTO_format'] = $tipoEventoFormat;
        }
        return $Result_tab;
    }

    private function initComboTipoEvento() {
        // Inserimento manuale altri allegati
        Out::select($this->nameForm . '_' . $this->TABLE_NAME . '[TIPOEVENTO]', 1, 0, 1, "Selezionare...");
        Out::select($this->nameForm . '_TIPOEVENTO', 1, 0, 1, "Selezionare...");

        foreach ($this->tipoEvento as $key => $value) {
            Out::select($this->nameForm . '_' . $this->TABLE_NAME . '[TIPOEVENTO]', 1, $key, 0, $value);
            Out::select($this->nameForm . '_TIPOEVENTO', 1, $key, 0, $value);
        }
    }

}
