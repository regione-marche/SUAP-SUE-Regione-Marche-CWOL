<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBtaNazgru() {
    $cwbBtaNazgru = new cwbBtaNazgru();
    $cwbBtaNazgru->parseEvent();
    return;
}

class cwbBtaNazgru extends cwbBpaGenTab {

    protected function initVars() {
        $this->GRID_NAME = 'gridBtaNazgru';
        $this->ALIAS = 'U';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 4;
        $this->libDB = new cwbLibDB_BTA();
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_NAZGRU[CODGRNAZ]_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaGrunaz', $this->nameForm, 'returnFromBtaGrunaz', $_POST['id'], true);
                        break;
                    case $this->nameForm . '_BTA_NAZGRU[CODNAZI]_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaNazion', $this->nameForm, 'returnFromBtaNazion', $_POST['id'], true);
                        break;
                }
                break;

            case 'returnFromBtaGrunaz':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_NAZGRU[CODGRNAZ]_butt':
                        Out::valore($this->nameForm . '_BTA_NAZGRU[CODGRNAZ]', $this->formData['returnData']['CODGRNAZ']);
                        Out::valore($this->nameForm . '_DESGRNAZ_decod', $this->formData['returnData']['DESGRNAZ']);
                        break;
                }
                break;

            case 'returnFromBtaNazion':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_NAZGRU[CODNAZI]_butt':
                        Out::valore($this->nameForm . '_BTA_NAZGRU[CODNAZI]', $this->formData['returnData']['CODNAZI']);
                        Out::valore($this->nameForm . '_DESNAZI_decod', $this->formData['returnData']['DESNAZI']);
                        break;
                }
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_NAZGRU[PK]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PK'], $this->nameForm . '_BTA_NAZGRU[PK]');
                        break;
                    case $this->nameForm . '_BTA_NAZGRU[CODGRNAZ]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODGRNAZ'], $this->nameForm . '_BTA_NAZGRU[CODGRNAZ]', $this->nameForm . '_DESGRNAZ_decod')) {
                            $this->decodGruppo($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODGRNAZ'], ($this->nameForm . '_BTA_NAZGRU[CODGRNAZ]'), ($this->nameForm . '_DESGRNAZ_decod'));
                        } else {
                            Out::valore($this->nameForm . '_DESGRNAZ_decod', '');
                        }
                        break;
                    case $this->nameForm . '_BTA_NAZGRU[CODNAZI]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZI'], $this->nameForm . '_BTA_NAZGRU[CODNAZI]', $this->nameForm . '_DESNAZI_decod')) {
                            $this->decodGruppoNazion($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZI'], ($this->nameForm . '_BTA_NAZGRU[CODNAZI]'), ($this->nameForm . '_DESNAZI_decod'));
                        } else {
                            Out::valore($this->nameForm . '_DESNAZI_decod', '');
                        }
                        break;
                }
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['PK_formatted'] != '') {
            $this->gridFilters['PK'] = $this->formData['PK_formatted'];
        }
        if ($_POST['DESGRNAZ'] != '') {
            $this->gridFilters['DESGRNAZ'] = $this->formData['DESGRNAZ'];
        }
        if ($_POST['CODGRNAZ'] != '') {
            $this->gridFilters['CODGRNAZ'] = $this->formData['CODGRNAZ'];
        }
        if ($_POST['CODNAZI'] != '') {
            $this->gridFilters['CODNAZI'] = $this->formData['CODNAZI'];
        }
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DESGRNAZ');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DESGRNAZ');
    }

    protected function postNuovo() {
        //TODO: nel vecchio faceva questo controllo "If iv_flag_default=0";; Se la connessione è cityware il codice nn può essere inferiore a 20
        // va replicato. Per adesso gestisco iv_flag_default come fosse sempre 0

        if (!$iv_flag_default) {
            $progr = cwbLibCalcoli::trovaProgressivo("PK", "BTA_NAZGRU");
            if ($progr < 100) {
                Out::valore($this->nameForm . '_BTA_NAZGRU[PK]', 100);
            } else {
                Out::valore($this->nameForm . '_BTA_NAZGRU[PK]', $progr);
            }
        } else {
            $max = $this->libDB->leggiBtaNazgruMax();
            $progr = $max[0]['MAX'] + 1;
            Out::valore($this->nameForm . '_BTA_NAZGRU[PK]', $progr);
        }
        Out::attributo($this->nameForm . '_BTA_NAZGRU[PK]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_NAZGRU[PK]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_NAZGRU[PK]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_NAZGRU[PK]');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        Out::css($this->nameForm . '_BTA_NAZGRU[PK]', 'background-color', '#FFFFE0');
        $this->decodGruppo($this->CURRENT_RECORD['CODGRNAZ'], ($this->nameForm . '_BTA_NAZGRU[CODGRNAZ]'), ($this->nameForm . '_DESGRNAZ_decod'));
        $this->decodGruppoNazion($this->CURRENT_RECORD['CODNAZI'], ($this->nameForm . '_BTA_NAZGRU[CODNAZI]'), ($this->nameForm . '_DESNAZI_decod'));
        Out::attributo($this->nameForm . '_BTA_NAZGRU[PK]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_BTA_NAZGRU[CODGRNAZ]');
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_DESGRNAZ_decod', '');
        Out::valore($this->nameForm . '_DESNAZI_decod', '');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        if ($this->masterRecord) {
            $filtri['PK'] = $this->masterRecord['PK'];
            $this->SQL = $this->libDB->getSqlLeggiBtaNazgru($filtri, true, $sqlParams);
        } else {
            $filtri['PK'] = trim($this->formData[$this->nameForm . '_PK']);
            $filtri['DESGRNAZ'] = trim($this->formData[$this->nameForm . '_DESGRNAZ']);
            $this->compilaFiltri($filtri);
            $this->SQL = $this->libDB->getSqlLeggiBtaNazgru($filtri, true, $sqlParams);
        }
    }

    public function setSortParameter() { // faccio override perchè altrimenti dave errore con il sort automatico fatto dalla superclasse
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        list($PK, $CODGRNAZ, $CODNAZI) = explode('|', $index);
        $this->SQL = $this->libDB->getSqlLeggiBtaNazgruChiave($PK, $CODGRNAZ, $CODNAZI, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PK_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PK']);
            $Result_tab[$key]['CODGRNAZ_formatted'] = $Result_tab[$key]['CODGRNAZ'] . ' - ' . $Result_tab[$key]['DESGRNAZ'];
            $Result_tab[$key]['CODNAZI_formatted'] = $Result_tab[$key]['CODNAZI'] . ' - ' . $Result_tab[$key]['DESNAZI'];
        }
        return $Result_tab;
    }

    private function decodGruppo($cod, $codField, $desField) {
        $row = $this->libDB->leggiBtaGrunazChiave($cod);
        if ($row) {
            Out::valore($codField, $row['CODGRNAZ']);
            Out::valore($desField, $row['DESGRNAZ']);
        } else {
            Out::valore($codField, '');
            Out::valore($desField, '');
        }
    }

    private function decodGruppoNazion($cod, $codField, $desField) {
        $row = $this->libDB->leggiBtaNazionChiave($cod);
        if ($row) {
            Out::valore($codField, $row['CODNAZI']);
            Out::valore($desField, $row['DESNAZI']);
        } else {
            Out::valore($codField, '');
            Out::valore($desField, '');
        }
    }

}

