<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBtaTribu() {
    $cwbBtaTribu = new cwbBtaTribu();
    $cwbBtaTribu->parseEvent();
    return;
}

class cwbBtaTribu extends cwbBpaGenTab {

    protected function initVars() {
        $this->GRID_NAME = 'gridBtaTribu';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 3;
        $this->libDB = new cwbLibDB_BTA();
        $this->errorOnEmpty = false;
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_TRIBU[CODNAZI]_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaNazion', $this->nameForm, 'returnFromBtaNazion', $_POST['id'], true);
                        break;
                }
                break;
            case 'returnFromBtaNazion':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_TRIBU[CODNAZI]_butt':
                        Out::valore($this->nameForm . '_BTA_TRIBU[CODNAZI]', $this->formData['returnData']['CODNAZI']);
                        Out::valore($this->nameForm . '_DESCODNAZI', $this->formData['returnData']['DESNAZI']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CODTRIBUN':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODTRIBUN'], $this->nameForm . '_CODTRIBUN');
                        break;
                    case $this->nameForm . '_BTA_TRIBU[F_ITA_EST]':
                        $this->ItaEst($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['F_ITA_EST']);                                                                      // solo se F_ITA_EST = 1;  
                        break;
                    case $this->nameForm . '_BTA_TRIBU[CODTRIBUN]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODTRIBUN'], $this->nameForm . '_BTA_TRIBU[CODTRIBUN]');
                        break;
                    case $this->nameForm . '_BTA_TRIBU[CODNAZI]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZI'], $this->nameForm . '_BTA_TRIBU[CODNAZI]')) {
                            cwbLib::apriFinestraRicerca('cwbBtaNazion', $this->nameForm, 'returnFromBtaNazion', $_POST['id'], true);
                        }
                        break;
                }
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODTRIBUN_formatted'] != '') {
            $this->gridFilters['CODTRIBUN'] = $this->formData['CODTRIBUN_formatted'];
        }
        if ($_POST['DESTRIBU'] != '') {
            $this->gridFilters['DESTRIBU'] = $this->formData['DESTRIBU'];
        }
        if ($_POST['INDITRIBU'] != '') {
            $this->gridFilters['INDITRIBU'] = $this->formData['INDITRIBU'];
        }
        if ($_POST['CAP'] != '') {
            $this->gridFilters['CAP'] = $this->formData['CAP'];
        }
        if ($_POST['PROVINCIA'] != '') {
            $this->gridFilters['PROVINCIA'] = $this->formData['PROVINCIA'];
        }
    }

    protected function postNuovo() {
        $progr = cwbLibCalcoli::trovaProgressivo("CODTRIBUN", "BTA_TRIBU");
        Out::valore($this->nameForm . '_BTA_TRIBU[CODTRIBUN]', $progr);
        Out::attributo($this->nameForm . '_BTA_TRIBU[CODTRIBUN]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_TRIBU[CODTRIBUN]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_TRIBU[CODTRIBUN]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_TRIBU[CODTRIBUN]');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DESTRIBU');
    }

    protected function postApriForm() {
        $this->ItaEst(0);
        $this->initComboTribuItaEst();
        Out::setFocus("", $this->nameForm . '_DESTRIBU');
    }

    protected function postDettaglio($index) {
        $this->decodNazion($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZI'], ($this->nameForm . '_BTA_TRIBU[CODNAZI]'), ($this->nameForm . '_DESCODNAZI'));
        $this->ItaEst($this->CURRENT_RECORD['F_ITA_EST']);
        Out::valore($this->nameForm . '_BTA_TRIBU[DESTRIBU]', trim($this->CURRENT_RECORD['DESTRIBU']));
        Out::valore($this->nameForm . '_BTA_TRIBU[INDITRIBU]', trim($this->CURRENT_RECORD['INDITRIBU']));
        Out::attributo($this->nameForm . '_BTA_TRIBU[CODTRIBUN]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_TRIBU[CODTRIBUN]', 'background-color', '#FFFFE0');
        Out::setFocus('', $this->nameForm . '_BTA_TRIBU[DESTRIBU]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['CODTRIBUN'] = trim($this->formData[$this->nameForm . '_CODTRIBUN']);
        $filtri['DESTRIBU'] = trim($this->formData[$this->nameForm . '_DESTRIBU']);
        $filtri['FLAG_DIS'] = trim($this->formData[$this->nameForm . '_FLAG_DIS']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaTribu($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaTribuChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CODTRIBUN_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CODTRIBUN']);
        }
        return $Result_tab;
    }

    private function ItaEst($flag) {
        if (!$flag) {
            Out::hide($this->nameForm . '_BTA_TRIBU[CODNAZI]');
            Out::hide($this->nameForm . '_DESCODNAZI');
            Out::hide($this->nameForm . '_BTA_TRIBU[CODNAZI]_lbl');
            Out::hide($this->nameForm . '_BTA_TRIBU[CODNAZI]_butt');
        } else {
            Out::show($this->nameForm . '_BTA_TRIBU[CODNAZI]');
            Out::show($this->nameForm . '_DESCODNAZI');
            Out::show($this->nameForm . '_BTA_TRIBU[CODNAZI]_lbl');
            Out::show($this->nameForm . '_BTA_TRIBU[CODNAZI]_butt');
        }
    }

    private function decodNazion($cod, $codField, $desField) {
        $row = $this->libDB->leggiBtaNazionChiave($cod);
        if ($row) {
            Out::valore($codField, $row['CODNAZI']);
            Out::valore($desField, $row['DESNAZI']);
        } else {
            Out::valore($codField, ' ');
            Out::valore($desField, ' ');
        }
    }

    private function initComboTribuItaEst() {
        // Popolo combo per scelta tribunale estero o ita
        Out::select($this->nameForm . '_BTA_TRIBU[F_ITA_EST]', 1, "0", 0, "Italiano");
        Out::select($this->nameForm . '_BTA_TRIBU[F_ITA_EST]', 1, "1", 1, "Estero");
    }

}

?>