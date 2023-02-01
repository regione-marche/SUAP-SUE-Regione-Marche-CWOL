<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBtaViegco() {
    $cwbBtaViegco = new cwbBtaViegco();
    $cwbBtaViegco->parseEvent();
    return;
}

class cwbBtaViegco extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaViegco';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 7;
        $this->libDB = new cwbLibDB_BTA();
        $this->setTABLE_VIEW("BTA_VIEGCO_V01");
        $this->elencaAutoAudit = true;
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['DESVIA'] != '') {
            $this->gridFilters['DESVIA'] = $this->formData['DESVIA'];
        }
        if ($_POST['DESLOCAL'] != '') {
            $this->gridFilters['DESLOCAL'] = $this->formData['DESLOCAL'];
        }
        if ($_POST['PARI_DISP'] != '') {
            $this->gridFilters['PARI_DISP'] = $this->formData['PARI_DISP'];
        }
        if ($_POST['DESCR_1'] != '') {
            $this->gridFilters['DESCR_1'] = $this->formData['DESCR_1'];
        }
        if ($_POST['DESCR_2'] != '') {
            $this->gridFilters['DESCR_2'] = $this->formData['DESCR_2'];
        }
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_VIEGCO[TOPONIMO]_butt':
                        $this->decodificaToponimo($_POST[$this->nameForm . '_BTA_VIEGCO']['TOPONIMO'], $this->nameForm . '_BTA_VIEGCO[TOPONIMO]', true);
                        break;
                    case $this->nameForm . '_DESLOCAL_decod_butt':
                        $this->decodLocal($this->formData[$this->nameForm . "_BTA_VIEGCO[CODNAZPRO]"], $this->formData[$this->nameForm . "_BTA_VIEGCO[CODLOCAL]"], $this->nameForm . "_BTA_VIEGCO[CODNAZPRO]", $this->nameForm . "_BTA_VIEGCO[CODLOCAL]", $this->formData[$this->nameForm . "_DESLOCAL_decod"], $this->nameForm . "_DESLOCAL_decod", true);
                        if ($_POST[$this->nameForm . '_BTA_VIEGCO']['CODNAZPRO']) {
                            Out::valore($this->nameForm . '_BTA_VIEGCO[CODNAZPRO]', str_pad($_POST[$this->nameForm . '_BTA_VIEGCO']['CODNAZPRO'], 3, "00", STR_PAD_LEFT));
                            Out::valore($this->nameForm . '_BTA_VIEGCO[CODLOCAL]', str_pad($_POST[$this->nameForm . '_BTA_VIEGCO']['CODLOCAL'], 3, "00", STR_PAD_LEFT));
                        }
                        break;
                    case $this->nameForm . '_DESLOCAL_butt':
                        $this->decodLocal($this->formData[$this->nameForm . "_CODNAZPRO"], $this->formData[$this->nameForm . "_CODLOCAL"], $this->nameForm . "_CODNAZPRO", $this->nameForm . "_CODLOCAL", $this->formData[$this->nameForm . "_DESLOCAL"], $this->nameForm . "_DESLOCAL", true);
                        if ($_POST[$this->nameForm . '_CODNAZPRO']) {
                            Out::valore($this->nameForm . '_CODNAZPRO', str_pad($_POST[$this->nameForm . '_CODNAZPRO'], 3, "00", STR_PAD_LEFT));
                            Out::valore($this->nameForm . '_CODLOCAL', str_pad($_POST[$this->nameForm . '_CODLOCAL'], 3, "00", STR_PAD_LEFT));
                        }
                        break;
                }
                break;
            case 'returnFromBtaTopono':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_VIEGCO[TOPONIMO]_butt':
                    case $this->nameForm . '_BTA_VIEGCO[TOPONIMO]':
                        Out::valore($this->nameForm . '_BTA_VIEGCO[TOPONIMO]', trim($this->formData['returnData']['TOPONIMO']));
                        break;
                }
            case 'returnFromBtaLocal':
                switch ($this->elementId) {
                    case $this->nameForm . '_DESLOCAL_butt':
                    case $this->nameForm . '_DESLOCAL':
                        Out::valore($this->nameForm . '_CODNAZPRO', str_pad($this->formData['returnData']['CODNAZPRO'], 3, "00", STR_PAD_LEFT));
                        Out::valore($this->nameForm . '_CODLOCAL', str_pad($this->formData['returnData']['CODLOCAL'], 3, "00", STR_PAD_LEFT));
                        Out::valore($this->nameForm . '_DESLOCAL', $this->formData['returnData']['DESLOCAL']);
                        break;
                    case $this->nameForm . '_DESLOCAL_decod_butt':
                    case $this->nameForm . '_DESLOCAL_decod':
                        Out::valore($this->nameForm . '_BTA_VIEGCO[CODNAZPRO]', str_pad($this->formData['returnData']['CODNAZPRO'], 3, "00", STR_PAD_LEFT));
                        Out::valore($this->nameForm . '_BTA_VIEGCO[CODLOCAL]', str_pad($this->formData['returnData']['CODLOCAL'], 3, "00", STR_PAD_LEFT));
                        Out::valore($this->nameForm . '_DESLOCAL_decod', $this->formData['returnData']['DESLOCAL']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_VIEGCO[PROG_VCO]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROG_VCO'], $this->nameForm . '_BTA_VIEGCO[PROG_VCO]');
                        break;
                    case $this->nameForm . '_BTA_VIEGCO[NUMCIV]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['NUMCIV'], $this->nameForm . '_BTA_VIEGCO[NUMCIV]');
                        break;
                    case $this->nameForm . '_BTA_VIEGCO[NUMCIV_F]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['NUMCIV_F'], $this->nameForm . '_BTA_VIEGCO[NUMCIV_F]');
                        break;
                    case $this->nameForm . '_BTA_VIEGCO[TOPONIMO]':
                        $this->decodificaToponimo($_POST[$this->nameForm . '_BTA_VIEGCO']['TOPONIMO'], $this->nameForm . '_BTA_VIEGCO[TOPONIMO]');

                        break;
                    case $this->nameForm . '_CODNAZPRO':
                    case $this->nameForm . '_CODLOCAL':
                        if ($_POST[$this->nameForm . '_CODNAZPRO']) {
                            Out::valore($this->nameForm . '_CODNAZPRO', str_pad($_POST[$this->nameForm . '_CODNAZPRO'], 3, "00", STR_PAD_LEFT));
                        }
                        if ($_POST[$this->nameForm . '_CODLOCAL']) {
                            Out::valore($this->nameForm . '_CODLOCAL', str_pad($_POST[$this->nameForm . '_CODLOCAL'], 3, "00", STR_PAD_LEFT));
                        }

                        if ($_POST[$this->nameForm . '_CODNAZPRO'] && $_POST[$this->nameForm . '_CODLOCAL']) {
                            if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODLOCAL'], $this->nameForm . '_CODLOCAL', $this->nameForm . '_DESLOCAL')) {
                                $row = $this->decodLocal($_POST[$this->nameForm . '_CODNAZPRO'], $_POST[$this->nameForm . '_CODLOCAL'], $this->nameForm . '_CODNAZPRO', $this->nameForm . '_CODLOCAL', $_POST[$this->nameForm . '_DESLOCAL'], $this->nameForm . '_DESLOCAL');
                                if ($row && $row['CODNAZPRO']) {
                                    Out::valore($this->nameForm . '_CODNAZPRO', str_pad($row['CODNAZPRO'], 3, "00", STR_PAD_LEFT));
                                    Out::valore($this->nameForm . '_CODLOCAL', str_pad($row['CODLOCAL'], 3, "00", STR_PAD_LEFT));
                                }
                            }
                        } else {
                            Out::valore($this->nameForm . '_DESLOCAL', "");
                        }
                        break;
                    case $this->nameForm . '_DESLOCAL':
                        $row = $this->decodLocal(null, $_POST[$this->nameForm . '_CODLOCAL'], $this->nameForm . '_CODNAZPRO', $this->nameForm . '_CODLOCAL', $_POST[$this->nameForm . '_DESLOCAL'], $this->nameForm . '_DESLOCAL');
                        if ($row && $row['CODNAZPRO']) {
                            Out::valore($this->nameForm . '_CODNAZPRO', str_pad($row['CODNAZPRO'], 3, "00", STR_PAD_LEFT));
                            Out::valore($this->nameForm . '_CODLOCAL', str_pad($row['CODLOCAL'], 3, "00", STR_PAD_LEFT));
                        }
                        break;
                    case $this->nameForm . '_BTA_VIEGCO[CODLOCAL]':
                    case $this->nameForm . '_BTA_VIEGCO[CODNAZPRO]':

                        if ($_POST[$this->nameForm . '_BTA_VIEGCO']['CODNAZPRO']) {
                            Out::valore($this->nameForm . '_BTA_VIEGCO[CODNAZPRO]', str_pad($_POST[$this->nameForm . '_BTA_VIEGCO']['CODNAZPRO'], 3, "00", STR_PAD_LEFT));
                        }
                        if ($_POST[$this->nameForm . '_BTA_VIEGCO']['CODLOCAL']) {
                            Out::valore($this->nameForm . '_BTA_VIEGCO[CODLOCAL]', str_pad($_POST[$this->nameForm . '_BTA_VIEGCO']['CODLOCAL'], 3, "00", STR_PAD_LEFT));
                        }

                        if ($_POST[$this->nameForm . '_BTA_VIEGCO']['CODNAZPRO'] && $_POST[$this->nameForm . '_BTA_VIEGCO']['CODLOCAL']) {
                            if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_BTA_VIEGCO']['CODLOCAL'], $this->nameForm . '_BTA_VIEGCO[CODLOCAL]', $this->nameForm . "_DESLOCAL_decod")) {
                                $row = $this->decodLocal($_POST[$this->nameForm . '_BTA_VIEGCO']['CODNAZPRO'], $_POST[$this->nameForm . '_BTA_VIEGCO']['CODLOCAL'], $this->nameForm . '_BTA_VIEGCO[CODNAZPRO]', $this->nameForm . '_BTA_VIEGCO[CODLOCAL]', $_POST[$this->nameForm . "_DESLOCAL_decod"], $this->nameForm . "_DESLOCAL_decod");
                                if ($row && $row['CODNAZPRO']) {
                                    Out::valore($this->nameForm . '_BTA_VIEGCO[CODNAZPRO]', str_pad($row['CODNAZPRO'], 3, "00", STR_PAD_LEFT));
                                    Out::valore($this->nameForm . '_BTA_VIEGCO[CODLOCAL]', str_pad($row['CODLOCAL'], 3, "00", STR_PAD_LEFT));
                                }
                            }
                        } else {
                            Out::valore($this->nameForm . "_DESLOCAL_decod", "");
                        }

                        break;
                    case $this->nameForm . "_DESLOCAL_decod":
                        $row = $this->decodLocal(null, $_POST[$this->nameForm . '_BTA_VIEGCO']['CODLOCAL'], $this->nameForm . '_BTA_VIEGCO[CODNAZPRO]', $this->nameForm . '_BTA_VIEGCO[CODLOCAL]', $_POST[$this->nameForm . "_DESLOCAL_decod"], $this->nameForm . "_DESLOCAL_decod");
                        if ($row && $row['CODNAZPRO']) {
                            Out::valore($this->nameForm . '_BTA_VIEGCO[CODNAZPRO]', str_pad($row['CODNAZPRO'], 3, "00", STR_PAD_LEFT));
                            Out::valore($this->nameForm . '_BTA_VIEGCO[CODLOCAL]', str_pad($row['CODLOCAL'], 3, "00", STR_PAD_LEFT));
                        }
                        break;
                }
                break;
        }
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_CODNAZPRO');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_CODNAZPRO');
    }

    protected function postNuovo() {
        $progr = cwbLibCalcoli::trovaProgressivo("PROG_VCO", "BTA_VIEGCO");
        Out::valore($this->nameForm . '_BTA_VIEGCO[PROG_VCO]', $progr);
        Out::setFocus("", $this->nameForm . '_BTA_VIEGCO[TOPONIMO]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_VIEGCO[TOPONIMO]');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        $this->decodLocal($this->CURRENT_RECORD['CODNAZPRO'], $this->CURRENT_RECORD['CODLOCAL'], $this->nameForm . '_BTA_VIEGCO[CODNAZPRO]', $this->nameForm . '_BTA_VIEGCO[CODLOCAL]', null, ($this->nameForm . '_DESLOCAL_decod'));
        // Formatta campi
        if ($this->CURRENT_RECORD['CODNAZPRO']) {
            Out::valore($this->nameForm . '_BTA_VIEGCO[CODNAZPRO]', str_pad($this->CURRENT_RECORD['CODNAZPRO'], 3, '0', STR_PAD_LEFT));
            Out::valore($this->nameForm . '_BTA_VIEGCO[CODLOCAL]', str_pad($this->CURRENT_RECORD['CODLOCAL'], 3, '0', STR_PAD_LEFT));
        }
        if ($this->CURRENT_RECORD['PARI_DISP'] == 'T') {
            Out::attributo($this->nameForm . '_PARI_DISP_T', 'checked', '0', 'checked');
        } else if ($this->CURRENT_RECORD['PARI_DISP'] == 'P') {
            Out::attributo($this->nameForm . '_PARI_DISP_P', 'checked', '0', 'checked');
        } else if ($this->CURRENT_RECORD['PARI_DISP'] == 'D') {
            Out::attributo($this->nameForm . '_PARI_DISP_D', 'checked', '0', 'checked');
        } else if ($this->CURRENT_RECORD['PARI_DISP'] == 'K') {
            Out::attributo($this->nameForm . '_PARI_DISP_K', 'checked', '0', 'checked');
        }

        Out::valore($this->nameForm . '_BTA_VIEGCO[TOPONIMO]', trim($this->CURRENT_RECORD['TOPONIMO']));
        Out::valore($this->nameForm . '_BTA_VIEGCO[DESVIA]', trim($this->CURRENT_RECORD['DESVIA']));
        Out::valore($this->nameForm . '_BTA_VIEGCO[DESCR_1]', trim($this->CURRENT_RECORD['DESCR_1']));
        Out::valore($this->nameForm . '_BTA_VIEGCO[DESCR_2]', trim($this->CURRENT_RECORD['DESCR_2']));
        Out::setFocus('', $this->nameForm . '_BTA_VIEGCO[PROG_VCO]');
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_DESLOCAL_decod', '');
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['DESVIA_formatted'] = trim($Result_tab[$key]['TOPONIMO']) . ' ' . trim($Result_tab[$key]['DESVIA']);
        }
        return $Result_tab;
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['DESVIA'] = trim($this->formData[$this->nameForm . '_DESVIA']);
        $filtri['CODNAZPRO'] = trim($this->formData[$this->nameForm . '_CODNAZPRO']);
        $filtri['CODLOCAL'] = trim($this->formData[$this->nameForm . '_CODLOCAL']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaViegco($filtri, true, $sqlParams);
    }

    private function decodLocal($codValue, $codValue2, $codField, $codField2, $desValue, $desField, $search = false) {
        return cwbLib::decodificaLookup("cwbBtaLocal", $this->nameForm, $this->nameFormOrig, array($codValue, $codValue2), array($codField, $codField2), array("CODNAZPRO", "CODLOCAL"), $desValue, $desField, "DESLOCAL", "returnFromBtaLocal", $_POST['id'], $search, false);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaViegcoChiave($index, $sqlParams);
    }

    private function decodificaToponimo($toponimo, $campoForm, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaTopono", $this->nameForm, $this->nameFormOrig, null, null, null, $toponimo, $campoForm, "TOPONIMO", 'returnFromBtaTopono', $_POST['id'], $searchButton);
    }

}

