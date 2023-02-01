<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaAssiva() {
    $cwbBtaAssiva = new cwbBtaAssiva();
    $cwbBtaAssiva->parseEvent();
    return;
}

class cwbBtaAssiva extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaAssiva';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 14;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_ASSIVA[IVAASS_VE]':
                        $this->decodAssiva($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['IVAASS_VE'], $this->nameForm . '_BTA_ASSIVA[IVAASS_VE]', ($this->nameForm . '_DES_ASSOGGE'));
                        break;
                }
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['IVAASSOG_formatted'] != '') {
            $this->gridFilters['IVAASSOG'] = $this->formData['IVAASSOG_formatted'];
        }
        if ($_POST['DES_ASS'] != '') {
            $this->gridFilters['DES_ASS'] = $this->formData['DES_ASS'];
        }
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_DES_ASSOGGE', ' ');
        Out::valore($this->nameForm . '_DESCR_SDI', ' ');
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_ASSIVA[IVAASSOG]', 'readonly', '1');
        Out::setFocus("", $this->nameForm . '_BTA_ASSIVA[IVAASSOG]');
        Out::css($this->nameForm . '_BTA_ASSIVA[IVAASSOG]', 'background-color', '#FFFFFF');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_ASSIVA[CODARROT]');
    }

    protected function postDettaglio($index) {
        // toglie gli spazi del char
        Out::valore($this->nameForm . '_BTA_ASSIVA[DES_ASS]', trim($this->CURRENT_RECORD['DES_ASS']));

        //Ricavo la descrizione dell'assoggettamento partendo dal codice IVAASS_VE
        $this->decodAssiva($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['IVAASS_VE'], $this->nameForm . '_BTA_ASSIVA[IVAASS_VE]', ($this->nameForm . '_DES_ASSOGGE'));
    }

    protected function postApriForm() {
        $this->initComboAlleClienti();
        $this->initComboAlleFornitori();
        $this->initComboAssClienti();
        $this->initComboAssFornitori();
        $this->initComboTipOper();
        Out::setFocus("", $this->nameForm . '_DES_ASS');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DES_ASS');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['IVAASSOG'] = trim($this->formData[$this->nameForm . '_IVAASSOG']);
        $filtri['DES_ASS'] = trim($this->formData[$this->nameForm . '_DES_ASS']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaAssiva($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaAssivaChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['IVAASSOG_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['IVAASSOG']);
        }
        return $Result_tab;
    }

    private function initComboAssClienti() {
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOASSCL]', 1, "0", 1, "0 - Aliquota Numerica");
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOASSCL]', 1, "1", 0, "1 - Esente");
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOASSCL]', 1, "2", 0, "2 - Non Soggetto");
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOASSCL]', 1, "3", 0, "3 - Non Imponibile");
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOASSCL]', 1, "4", 0, "4 - Escluso Base");
    }

    private function initComboAssFornitori() {
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOASSFO]', 1, "0", 1, "0 - Aliquota Numerica");
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOASSFO]', 1, "1", 0, "1 - Esente");
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOASSFO]', 1, "2", 0, "2 - Non Soggetto");
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOASSFO]', 1, "3", 0, "3 - Non Imponibile");
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOASSFO]', 1, "4", 0, "4 - Escluso Base");
    }

    private function initComboTipOper() {
        Out::select($this->nameForm . '_BTA_ASSIVA[FLAG_TIPOP]', 1, "0", 1, "0 - Altre operazioni non monitorate del Spesometro");
        Out::select($this->nameForm . '_BTA_ASSIVA[FLAG_TIPOP]', 1, "1", 0, "1 - Acquisto/Cessione beni");
        Out::select($this->nameForm . '_BTA_ASSIVA[FLAG_TIPOP]', 1, "2", 0, "2 - Acquisto/Prestazione servizi");
    }

    private function initComboAlleClienti() {
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOALLCL]', 1, "A", 1, "A = Somma Imponibile ed IVA nelle colonne IMPONIBILE ed IVA");
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOALLCL]', 1, "1", 0, "1 = Somma nella colonna ESENTE");
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOALLCL]', 1, "2", 0, "2 = Somma nella colonna NON IMPONIBILE");
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOALLCL]', 1, "3", 0, "3 = Somma nella colonna IMPONIBILE CON IVA NON ESPOSTA");
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOALLCL]', 1, " ", 0, "Non Entra in Allegato");
    }

    private function initComboAlleFornitori() {
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOALLFO]', 1, "A", 1, "A = Somma Imponibile ed IVA nelle colonne IMPONIBILE ed IVA");
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOALLFO]', 1, "1", 0, "1 = Somma nella colonna ESENTE");
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOALLFO]', 1, "2", 0, "2 = Somma nella colonna NON IMPONIBILE");
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOALLFO]', 1, "3", 0, "3 = Somma nella colonna IMPONIBILE CON IVA NON ESPOSTA");
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOALLFO]', 1, "4", 0, "4 = Somma nella colonna IMPONIBILE COMPRENSIVO DELL'IVA");
        Out::select($this->nameForm . '_BTA_ASSIVA[TIPOALLFO]', 1, " ", 0, "Non Entra in Allegato");
    }

    protected function decodAssiva($cod, $codField, $desField) {
        $row = $this->libDB->leggiBtaAssivaChiave($cod);
        if ($row) {
            Out::valore($desField, $row['DES_ASS']);
        } else {
            Out::valore($codField, ' ');
            Out::valore($desField, ' ');
        }
    }
}

