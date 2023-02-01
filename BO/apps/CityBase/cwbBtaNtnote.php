<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

function cwbBtaNtnote() {
    $cwbBtaNtnote = new cwbBtaNtnote();
    $cwbBtaNtnote->parseEvent();
    return;
}

class cwbBtaNtnote extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaNtnote';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 27;
        $this->libDB = new cwbLibDB_BTA();
        $this->libDB_BOR = new cwbLibDB_BOR();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DESGRUPPO_decod_butt':
                        $this->decodGrnote($this->formData[$this->nameForm . '_BTA_NTNOTE']['IDGRNOTE'], ($this->nameForm . 'IDGRNOTE'), $this->formData[$this->nameForm . '_DESGRUPPO_decod'], $this->nameForm . '_DESGRUPPO_decod', true);
                        break;
                }
                break;
            case 'returnFromBtaGrnote':
                switch ($this->elementId) {
                    case $this->nameForm . '_DESGRUPPO_decod':
                    case $this->nameForm . '_DESGRUPPO_decod_butt':
                    case $this->nameForm . '_BTA_NTNOTE[IDGRNOTE]':
                        Out::valore($this->nameForm . '_BTA_NTNOTE[IDGRNOTE]', $this->formData['returnData']['IDGRNOTE']);
                        Out::valore($this->nameForm . '_DESGRUPPO_decod', $this->formData['returnData']['DESGRUPPO']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_NTNOTE[IDGRNOTE]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['IDGRNOTE'], $this->nameForm . '_BTA_NTNOTE[IDGRNOTE]')) {
                            $this->decodGrnote($this->formData[$this->nameForm . '_BTA_NTNOTE']['IDGRNOTE'], ($this->nameForm . '_BTA_NTNOTE[IDGRNOTE]'), null, $this->nameForm . '_DESGRUPPO_decod');
                        } else {
                            Out::valore($this->nameForm . '_DESGRUPPO_decod', '');
                        }
                        break;
                    case $this->nameForm . '_DESGRUPPO_decod':
                        $this->decodGrnote(null, ($this->nameForm . '_BTA_NTNOTE[IDGRNOTE]'), $this->formData[$this->nameForm . '_DESGRUPPO_decod'], $this->nameForm . '_DESGRUPPO_decod', false);
                        break;

                    case $this->nameForm . '_BTA_NTNOTE[LUNG_MAX]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['LUNG_MAX'], $this->nameForm . '_BTA_NTNOTE[LUNG_MAX]');
                        break;
                }
                break;
        }
    }

    protected function preNuovo() {
        $this->pulisciCampi();
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_DESGRUPPO_decod', '');
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_NTNOTE[NATURANOTA]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_NTNOTE[NATURANOTA]', 'background-color', '#FFFFFF');
        Out::attributo($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_NTNOTE[TABLENOTE]');
    }

    protected function postApriForm() {
        $this->initComboTabNote();
        $this->initComboAutor();
        $this->initComboTipoNota();
        Out::attributo($this->nameForm . "_FLAG_DIS", "checked", "0", "checked");
        Out::setFocus("", $this->nameForm . '_DESNATURA');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DESNATURA');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_NTNOTE[TABLENOTE]');
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['TABLENOTE'] != '') {
            $this->gridFilters['TABLENOTE'] = $this->formData['TABLENOTE'];
        }
        if ($_POST['NATURANOTA'] != '') {
            $this->gridFilters['NATURANOTA'] = $this->formData['NATURANOTA'];
        }
        if ($_POST['DESNATURA'] != '') {
            $this->gridFilters['DESNATURA'] = $this->formData['DESNATURA'];
        }
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_NTNOTE[NATURANOTA]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_NTNOTE[NATURANOTA]', 'background-color', '#FFFFE0');
        Out::attributo($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 'background-color', '#FFFFE0');
        $this->decodGrnote($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['IDGRNOTE'], ($this->nameForm . '_BTA_NTNOTE[IDGRNOTE]'), ($this->nameForm . '_DESGRUPPO_decod'));
        Out::valore($this->nameForm . '_BTA_NTNOTE[DESNATURA]', trim($this->CURRENT_RECORD['DESNATURA']));
        Out::valore($this->nameForm . '_BTA_NTNOTE[ANNOTAZ]', trim($this->CURRENT_RECORD['ANNOTAZ']));
        Out::valore($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', trim($this->CURRENT_RECORD['TABLENOTE']));
        Out::setFocus('', $this->nameForm . '_BTA_NTNOTE[TABLENOTE]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['DESNATURA'] = trim($this->formData[$this->nameForm . '_DESNATURA']);
        $filtri['NATURANOTA'] = trim($this->formData[$this->nameForm . '_NATURANOTA']);
        $filtri['TABLENOTE'] = trim($this->formData[$this->nameForm . '_TABLENOTE']);
        $filtri['FLAG_DIS'] = trim($this->formData[$this->nameForm . '_FLAG_DIS']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaNtnote($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        list($TABLENOTE, $NATURANOTA) = explode('|', $index);
        $this->SQL = $this->libDB->getSqlLeggiBtaNtnoteChiave($TABLENOTE, $NATURANOTA, $sqlParams);
    }

    public function initComboTabNote() {
        $this->initComboTabNoteRicerca();
        $this->initComboTabNoteGestione();
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['TABLENOTE'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['TABLENOTE']);
            $Result_tab[$key]['NATURANOTA'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['NATURANOTA']);
        }
        return $Result_tab;
    }

    protected function initComboTabNoteRicerca() {
        Out::select($this->nameForm . '_TABLENOTE', 1, " ", 1, "---TUTTE---");
        Out::select($this->nameForm . '_TABLENOTE', 1, "ANT_NOTE", 0, "ANT_NOTE - Notifiche");
        Out::select($this->nameForm . '_TABLENOTE', 1, "API_NOTE", 0, "API_NOTE - Protocollo Informatico");
        Out::select($this->nameForm . '_TABLENOTE', 1, "ATD_NOTE", 0, "ATD_NOTE - Atti Amm.");
        Out::select($this->nameForm . '_TABLENOTE', 1, "BIT_NOTE", 0, "BIT_NOTE - Iter");
        Out::select($this->nameForm . '_TABLENOTE', 1, "BTA_NOTE", 0, "BTA_NOTE - Modulo Base");
        Out::select($this->nameForm . '_TABLENOTE', 1, "BTA_SOGINV", 0, "BTA_SOGINV - Comunicazioni Cli-For");
        Out::select($this->nameForm . '_TABLENOTE', 1, "DAN_NOTE", 0, "DAN_NOTE - Anagrafe");
        Out::select($this->nameForm . '_TABLENOTE', 1, "DSC_NOTE", 0, "DSC_NOTE - Stato Civile");
        Out::select($this->nameForm . '_TABLENOTE', 1, "DEL_NOTE", 0, "DEL_NOTE - Elettorale");
        Out::select($this->nameForm . '_TABLENOTE', 1, "FAC_NOTE", 0, "FAC_NOTE - Ciclo Passivo");
        Out::select($this->nameForm . '_TABLENOTE', 1, "FTA_NOTE", 0, "FTA_NOTE - S.Economici");
        Out::select($this->nameForm . '_TABLENOTE', 1, "SCT_NOTE", 0, "SCT_NOTE - Serv.Cimit.");
        Out::select($this->nameForm . '_TABLENOTE', 1, "SEP_NOTE", 0, "SEP_NOTE - ERP");
        Out::select($this->nameForm . '_TABLENOTE', 1, "STC_NOTE", 0, "STC_NOTE - Recupero Crediti");
        Out::select($this->nameForm . '_TABLENOTE', 1, "TBA_NOTE", 0, "TBA_NOTE - Tributi");
    }

    protected function initComboTabNoteGestione() {
        Out::select($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 1, " ", 1, "---TUTTE---");
        Out::select($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 1, "ANT_NOTE", 0, "ANT_NOTE - Notifiche");
        Out::select($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 1, "API_NOTE", 0, "API_NOTE - Protocollo Informatico");
        Out::select($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 1, "ATD_NOTE", 0, "ATD_NOTE - Atti Amm.");
        Out::select($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 1, "BIT_NOTE", 0, "BIT_NOTE - Iter");
        Out::select($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 1, "BTA_NOTE", 0, "BTA_NOTE - Modulo Base");
        Out::select($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 1, "BTA_SOGINV", 0, "BTA_SOGINV - Comunicazioni Cli-For");
        Out::select($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 1, "DAN_NOTE", 0, "DAN_NOTE - Anagrafe");
        Out::select($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 1, "DSC_NOTE", 0, "DSC_NOTE - Stato Civile");
        Out::select($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 1, "DEL_NOTE", 0, "DEL_NOTE - Elettorale");
        Out::select($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 1, "FAC_NOTE", 0, "FAC_NOTE - Ciclo Passivo");
        Out::select($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 1, "FTA_NOTE", 0, "FTA_NOTE - S.Economici");
        Out::select($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 1, "SCT_NOTE", 0, "SCT_NOTE - Serv.Cimit.");
        Out::select($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 1, "SEP_NOTE", 0, "SEP_NOTE - ERP");
        Out::select($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 1, "STC_NOTE", 0, "STC_NOTE - Recupero Crediti");
        Out::select($this->nameForm . '_BTA_NTNOTE[TABLENOTE]', 1, "TBA_NOTE", 0, "TBA_NOTE - Tributi");
    }

    protected function initComboTipoNota() {
        Out::select($this->nameForm . '_BTA_NTNOTE[TIPONOTA]', 1, "0", 1, "0 - Non determinata");
        Out::select($this->nameForm . '_BTA_NTNOTE[TIPONOTA]', 1, "1", 0, "1 - Nota visibile e stampabile");
        Out::select($this->nameForm . '_BTA_NTNOTE[TIPONOTA]', 1, "2", 0, "2 - Nota non stampabile");
    }

    protected function initComboAutor() {

        // Azzera combo
        Out::html($this->nameForm . '_BTA_NTNOTE[AUT_MODULO]', '');

        // Carica lista aree
        $tipi = $this->libDB_BOR->leggiBorDesautNaturaNote(array());

        // Popola combo in funzione dei dati caricati da db
        Out::select($this->nameForm . '_BTA_NTNOTE[AUT_MODULO]', 1, '', 1, " ");
        foreach ($tipi as $tipo) {
            Out::select($this->nameForm . '_BTA_NTNOTE[AUT_MODULO]', 1, $tipo['AUT_MODULO'], 0, trim($tipo['CODMODULO'] . ' ' . $tipo['PROGAUT'] . ' ' . $tipo['DESCRI']));
        }
    }

    private function decodGrnote($codValue, $codField, $desValue, $desField, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaGrnote", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "IDGRNOTE", $desValue, $desField, "DESGRUPPO", returnFromBtaGrnote, $_POST['id'], $searchButton);
    }

}

