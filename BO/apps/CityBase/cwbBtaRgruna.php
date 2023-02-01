<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBtaRgruna() {
    $cwbBtaRgruna = new cwbBtaRgruna();
    $cwbBtaRgruna->parseEvent();
    return;
}

class cwbBtaRgruna extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaRgruna';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 4;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Raggruppamenti':
                        $this->apriRaggruppamenti();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_RGRUNA[PK]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PK'], $this->nameForm . '_BTA_RGRUNA[PK]');
                        break;
                    case $this->nameForm . '_PK':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_PK'], $this->nameForm . '_PK');
                        break;
                }
                break;
        }
    }

    protected function setVisRisultato() {
        parent::setVisRisultato();
        Out::show($this->nameForm . '_Raggruppamenti');
    }

    protected function setVisRicerca() {
        parent::setVisRicerca();
        Out::hide($this->nameForm . '_Raggruppamenti');
    }

    protected function setVisNuovo() {
        parent::setVisNuovo();
        Out::hide($this->nameForm . '_Raggruppamenti');
    }

    protected function setVisDettaglio() {
        parent::setVisDettaglio();
        Out::show($this->nameForm . '_Raggruppamenti');
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['PK_formatted'] != '') {
            $this->gridFilters['PK'] = $this->formData['PK_formatted'];
        }
        if ($_POST['DES_RAGGR'] != '') {
            $this->gridFilters['DES_RAGGR'] = $this->formData['DES_RAGGR'];
        }
    }

    private function apriRaggruppamenti() {
        if ($this->getDetailView()) {
            $this->formDataToCurrentRecord();
        } else {
            $this->loadCurrentRecord($_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow']);
        }
        $externalFilter = array();
        $externalFilter[$this->PK] = $this->CURRENT_RECORD[$this->PK];
        cwbLib::apriFinestraDettaglio('cwbBtaNazgru', $this->nameForm, 'returnFromBtaNazgru', $_POST['id'], $this->CURRENT_RECORD, $externalFilter);
    }

    protected function postNuovo() {
        //TODO: nel vecchio faceva questo controllo "If iv_flag_default=0";; Se la connessione è cityware il codice nn può essere inferiore a 20
        // va replicato. Per adesso gestisco iv_flag_default come fosse sempre 0

        if (!$iv_flag_default) {
            $progr = cwbLibCalcoli::trovaProgressivo("PK", "BTA_RGRUNA");
            if ($progr < 20) {
                Out::valore($this->nameForm . '_BTA_RGRUNA[PK]', 20);
            } else {
                Out::valore($this->nameForm . '_BTA_RGRUNA[PK]', $progr);
            }
        } else {
            $max = $this->libDB->leggiBtaRgrunaMax();
            $progr = $max[0]['MAX'] + 1;
            Out::valore($this->nameForm . '_BTA_RGRUNA[PK]', $progr);
        }
        Out::attributo($this->nameForm . '_BTA_RGRUNA[PK]', 'readonly', '1');
        Out::setFocus("", $this->nameForm . '_BTA_RGRUNA[PK]');
        Out::css($this->nameForm . '_BTA_RGRUNA[PK]', 'background-color', '#FFFFFF');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_RGRUNA[PK]');
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_RGRUNA[PK]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_BTA_RGRUNA[DES_RAGGR]');
        // toglie gli spazi del char
        Out::css($this->nameForm . '_BTA_RGRUNA[PK]', 'background-color', '#FFFFE0');
        Out::valore($this->nameForm . '_BTA_RGRUNA[DES_RAGGR]', trim($this->CURRENT_RECORD['DES_RAGGR']));
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DES_RAGGR');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DES_RAGGR');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['PK'] = trim($this->formData[$this->nameForm . '_PK']);
        $filtri['DES_RAGGR'] = trim($this->formData[$this->nameForm . '_DES_RAGGR']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaRgruna($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaRgrunaChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PK_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PK']);
        }
        return $Result_tab;
    }

}

