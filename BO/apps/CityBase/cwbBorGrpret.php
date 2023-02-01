<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBorGrpret() {
    $cwbBorGrpret = new cwbBorGrpret();
    $cwbBorGrpret->parseEvent();
    return;
}

class cwbBorGrpret extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBorGrpret';
        $this->AUTOR_MODULO = 'BOR';
        $this->AUTOR_NUMERO = 4;
        $this->libDB = new cwbLibDB_BOR();
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Gruppo':
                        $this->apriGruppo();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PROGGRPRE':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_PROGGRPRE'], $this->nameForm . '_PROGGRPRE');
                        break;
                }
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['PROGGRPRE_formatted'] != '') {
            $this->gridFilters['PROGGRPRE'] = $this->formData['PROGGRPRE_formatted'];
        }
        if ($_POST['DES_GRPRE'] != '') {
            $this->gridFilters['DES_GRPRE'] = $this->formData['DES_GRPRE'];
        }
    }

    protected function preElenca() {
        Out::hide($this->nameForm . '_BOR_GRPRET[PROGENTE]');
    }

    private function apriGruppo() {
        if ($this->getDetailView()) {
            $this->formDataToCurrentRecord();
        } else {
            $this->loadCurrentRecord($_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow']);
        }
        cwbLib::apriFinestraDettaglio('cwbBorGrpred', $this->nameForm, 'returnFromBorGrpred', $_POST['id'], $this->CURRENT_RECORD);
    }

    protected function postNuovo() {
        Out::hide($this->nameForm . '_Gruppo');
        $progr = cwbLibCalcoli::trovaProgressivo("PROGGRPRE", "BOR_GRPRET");
        Out::valore($this->nameForm . '_BOR_GRPRET[PROGGRPRE]', $progr);
        Out::valore($this->nameForm . '_BOR_GRPRET[PROGENTE]', 1); // TODO: per adesso lo setto fisso, ma poi bisognerà valorizzarlo in base all'ente selezionato.
        Out::setFocus("", $this->nameForm . '_BOR_GRPRET[DES_GRPRE]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BOR_GRPRET[DES_GRPRE]');
    }

    protected function postDettaglio($index) {
        Out::show($this->nameForm . '_Gruppo');
        Out::setFocus('', $this->nameForm . '_BOR_GRPRET[DES_GRPRE]');
        // toglie gli spazi del char
        Out::valore($this->nameForm . '_BOR_GRPRET[DES_GRPRE]', trim($this->CURRENT_RECORD['DES_GRPRE']));
    }

    protected function postApriForm() {
        Out::hide($this->nameForm . '_Gruppo');
        Out::hide($this->nameForm . '_BOR_GRPRET[PROGENTE]');
        Out::hide($this->nameForm . '_BOR_GRPRET[PROGENTE]_lbl');
        Out::setFocus("", $this->nameForm . '_DES_GRPRE');
    }

    protected function postAltraRicerca() {
        Out::hide($this->nameForm . '_Gruppo');
        Out::setFocus("", $this->nameForm . '_DES_GRPRE');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        Out::show($this->nameForm . '_Gruppo');
        $filtri['PROGGRPRE'] = trim($this->formData[$this->nameForm . '_PROGGRPRE']);
        $filtri['DES_GRPRE'] = trim($this->formData[$this->nameForm . '_DES_GRPRE']);
        $filtri['FLAG_DIS'] = trim($this->formData[$this->nameForm . '_FLAG_DIS']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBorGrpret($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams) {
        list($PROGGRPRE, $PROGENTE) = explode('|', $index);
        $this->SQL = $this->libDB->getSqlLeggiBorGrpretChiave($PROGGRPRE, $PROGENTE, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PROGGRPRE_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGGRPRE']);
        }
        return $Result_tab;
    }

}

