<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaCivint() {
    $cwbBtaCivint = new cwbBtaCivint();
    $cwbBtaCivint->parseEvent();
    return;
}

class cwbBtaCivint extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaCivint';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 9;
        $this->errorOnEmpty = false;
        $this->libDB = new cwbLibDB_BTA();
        $this->setTABLE_VIEW("BTA_CIVINT_V01");
        $this->elencaAutoAudit = true;
    }

    protected function preConstruct() {
        $this->masterRecord = cwbParGen::getFormSessionVar($this->nameForm, 'masterRecord');
    }

    protected function preDestruct() {
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, 'masterRecord', $this->masterRecord);
        }
    }

    public function parseEvent() {
        switch ($_POST['event']) {
            case 'openform':
                $this->postApriForm();
                break;
        }
        parent::parseEvent();
        $this->customParseEvent();
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_CIVINT[TIPONCIV]_butt':
                        $this->decodTipo($this->formData[$this->nameForm . '_BTA_CIVINT']['TIPONCIV'], ($this->nameForm . '_BTA_CIVINT[TIPONCIV]'), $this->formData[$this->nameForm . '_BTA_CIVINT[TIPONCIV]'], $this->nameForm . '_DESTIPCIV_decod', true);

                        break;
                    case $this->nameForm . '_BTA_CIVINT[PIANO]_butt':
                        $this->decodPiano($this->formData[$this->nameForm . '_BTA_CIVINT']['PIANO'], ($this->nameForm . '_BTA_CIVINT[PIANO]'), $this->formData[$this->nameForm . '_BTA_CIVINT[PIANO]'], ($this->nameForm . '_BTA_CIVINT[PIANO]'), true);

                        break;
                    case $this->nameForm . '_BTA_CIVINT[SCALA]_butt':
                        $this->decodScala($this->formData[$this->nameForm . '_BTA_CIVINT']['SCALA'], ($this->nameForm . '_BTA_CIVINT[SCALA]'), $this->formData[$this->nameForm . '_BTA_CIVINT[SCALA]'], ($this->nameForm . '_BTA_CIVINT[SCALA]'), true);
                        break;
                    case $this->nameForm . '_BTA_CIVINT[INTERNO]_butt':
                        $this->decodInterno($this->formData[$this->nameForm . '_BTA_CIVINT']['INTERNO'], ($this->nameForm . '_BTA_CIVINT[INTERNO]'), $this->formData[$this->nameForm . '_BTA_CIVINT[INTERNO]'], ($this->nameForm . '_BTA_CIVINT[INTERNO]'), true);
                        break;
                }
                break;
            case 'returnFromBtaTipciv':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_CIVINT[TIPONCIV]_butt':
                        Out::valore($this->nameForm . '_BTA_CIVINT[TIPONCIV]', $this->formData['returnData']['TIPONCIV']);
                        Out::valore($this->nameForm . '_DESTIPCIV_decod', $this->formData['returnData']['DESTIPCIV']);
                        break;
                }
                break;
            case 'returnFromBtaDefpia':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_CIVINT[PIANO]_butt':
                        Out::valore($this->nameForm . '_BTA_CIVINT[PIANO]', $this->formData['returnData']['DEFPIA']);
                        Out::valore($this->nameForm . '_DESPIAN_decod', $this->formData['returnData']['DESPIAN']);
                        break;
                }
                break;
            case 'returnFromBtaDefsca':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_CIVINT[SCALA]_butt':
                        Out::valore($this->nameForm . '_BTA_CIVINT[SCALA]', $this->formData['returnData']['DEFSCALA']);
                        break;
                }
                break;
            case 'returnFromBtaDefint':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_CIVINT[INTERNO]_butt':
                        Out::valore($this->nameForm . '_BTA_CIVINT[INTERNO]', $this->formData['returnData']['DEFINT']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_CIVINT[F_VERIFICA]':
                        $value = $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['F_VERIFICA']; // vedo se chekkata o no.
                        if ($value == 1) {
                            Out::attributo($this->nameForm . '_BTA_CIVINT[DATAVERIF]', 'readonly', '1');
                            Out::css($this->nameForm . '_BTA_CIVINT[DATAVERIF]', 'background-color', '#FFFFFF');
                        } else {
                            Out::valore($this->nameForm . '_BTA_CIVINT[DATAVERIF]', '');
                            Out::attributo($this->nameForm . '_BTA_CIVINT[DATAVERIF]', 'readonly', '0');
                            Out::css($this->nameForm . '_BTA_CIVINT[DATAVERIF]', 'background-color', '#FFFFE0');
                        }
                        break;
                    case $this->nameForm . '_BTA_CIVINT[SCALA]':
                        $this->decodScala($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['SCALA'], $this->nameForm . '_BTA_CIVINT[SCALA]', false, '');
                        break;
                    case $this->nameForm . '_BTA_CIVINT[INTERNO]':
                        $this->decodInterno($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['INTERNO'], $this->nameForm . '_BTA_CIVINT[INTERNO]', false, '');
                        break;
                    case $this->nameForm . '_BTA_CIVINT[PIANO]':
                        $this->decodPiano($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PIANO'], $this->nameForm . '_BTA_CIVINT[PIANO]', '', $this->nameForm . '_DESPIAN_decod');
                        break;
                    case $this->nameForm . '_BTA_CIVINT[TIPONCIV]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['TIPONCIV'], $this->nameForm . '_BTA_CIVINT[TIPONCIV]', $this->nameForm . '_DESTIPCIV_decod')) {
                            $this->decodTipo($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['TIPONCIV'], $this->nameForm . '_BTA_CIVINT[TIPONCIV]', '', $this->nameForm . '_DESTIPCIV_decod');
                        } else {
                            Out::valore($this->nameForm . '_DESTIPCIV_decod', '');
                        }
                        break;
                }
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['PROGINT'] != '') {
            $this->gridFilters['PROGINT'] = $this->formData['PROGINT'];
        }
        if ($_POST['TIPONCIV'] != '') {
            $this->gridFilters['TIPONCIV'] = $this->formData['TIPONCIV'];
        }
        if ($_POST['SCALA'] != '') {
            $this->gridFilters['SCALA'] = $this->formData['SCALA'];
        }
        if ($_POST['INTERNO'] != '') {
            $this->gridFilters['INTERNO'] = $this->formData['INTERNO'];
        }
        if ($_POST['PIANO'] != '') {
            $this->gridFilters['PIANO'] = $this->formData['PIANO'];
        }
        if ($_POST['DATAINIZ'] != '') {
            $this->gridFilters['DATAINIZ'] = $this->formData['DATAINIZ'];
        }
        if ($_POST['DATAFINE'] != '') {
            $this->gridFilters['DATAFINE'] = $this->formData['DATAFINE'];
        }
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_PROGINT');
    }

    protected function elaboraCurrentRecord() {
        if ($this->masterRecord) {
            $this->CURRENT_RECORD['PROGNCIV'] = $this->masterRecord['PROGNCIV'];
            $prognciv = $this->masterRecord['PROGNCIV'];
        }
    }

    protected function postNuovo() {
        Out::html($this->nameForm . '_BTA_CIVINT[F_AGIB]', '');
        $this->initComboAgibilita();
        if ($this->masterRecord) {
            Out::valore($this->nameForm . '_BTA_CIVINT[PROGNCIV]', $this->masterRecord['PROGNCIV']);
        }
        Out::attributo($this->nameForm . '_BTA_CIVINT[SCALA]', 'readonly', '1');
        Out::attributo($this->nameForm . '_BTA_CIVINT[INTERNO]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_CIVINT[SCALA]', 'background-color', '#FFFFFF');
        Out::css($this->nameForm . '_BTA_CIVINT[INTERNO]', 'background-color', '#FFFFFF');
        Out::valore($this->nameForm . '_BTA_CIVINT[DATAINIZ]', cwbLibCode::getCurrentDate());
        Out::setFocus("", $this->nameForm . '_BTA_CIVINT[SCALA]');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_DESPIAN_decod', '');
        Out::valore($this->nameForm . '_DESTIPCIV_decod', '');
    }

    protected function postDettaglio($index) {
        //valorizzo combo
        Out::html($this->nameForm . '_BTA_CIVINT[F_AGIB]', '');
        $this->initComboAgibilita();
        //valorizzo campi intestazione GRID
        Out::valore($this->nameForm . '_PROGNCIV', $this->CURRENT_RECORD['PROGNCIV']);
//        Out::valore($this->nameForm . '_PROGINT', $this->CURRENT_RECORD['PROGINT']);
        Out::valore($this->nameForm . '_CODVIA', $this->CURRENT_RECORD['CODVIA']);
        Out::valore($this->nameForm . '_NUMCIV', $this->CURRENT_RECORD['NUMCIV']);
        Out::valore($this->nameForm . '_SUBNCIV', $this->CURRENT_RECORD['SUBNCIV']);
        Out::valore($this->nameForm . '_TIPONCIV', $this->CURRENT_RECORD['TIPONCIV']);
        Out::valore($this->nameForm . '_DATAINIZ', $this->CURRENT_RECORD['DATAINIZ']);
        Out::valore($this->nameForm . '_DATAFINE', $this->CURRENT_RECORD['DATAFINE']);
        Out::valore($this->nameForm . '_BTA_CIVINT[COD_IMMOBI]', trim($this->CURRENT_RECORD['COD_IMMOBI']));
        Out::attributo($this->nameForm . '_BTA_CIVINT[DATAVERIF]', 'readonly', '0');
        Out::attributo($this->nameForm . '_BTA_CIVINT[SCALA]', 'readonly', '0');
        Out::attributo($this->nameForm . '_BTA_CIVINT[INTERNO]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_CIVINT[DATAVERIF]', 'background-color', '#FFFFE0');
        Out::css($this->nameForm . '_BTA_CIVINT[SCALA]', 'background-color', '#FFFFE0');
        Out::css($this->nameForm . '_BTA_CIVINT[INTERNO]', 'background-color', '#FFFFE0');
        $this->decodPiano($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PIANO'], ($this->nameForm . '_BTA_CIVINT[PIANO]'), $this->nameForm . '_DESPIAN_decod', $this->nameForm . '_DESPIAN_decod');
        $this->decodTipo($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['TIPONCIV'], ($this->nameForm . '_BTA_CIVINT[TIPONCIV]'), $this->nameForm . '_DESTIPCIV_decod', $this->nameForm . '_DESTIPCIV_decod');
        Out::setFocus("", $this->nameForm . '_BTA_CIVINT[SCALA]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['_PROGINT'] = trim($this->formData[$this->nameForm . '_PROGINT']);
//        $filtri['PROGNCIV'] = trim($this->formData[$this->nameForm . '_PROGNCIV']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaCivint($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        list($PROGNCIV, $PROGINT) = explode('|', $index);
        $this->SQL = $this->libDB->getSqlLeggiBtaCivintChiave($PROGNCIV, $PROGINT, $sqlParams);
    }

    private function initComboAgibilita() {
        // Agibilità
        Out::select($this->nameForm . '_BTA_CIVINT[F_AGIB]', 1, "1", 1, "1- Agibile");
        Out::select($this->nameForm . '_BTA_CIVINT[F_AGIB]', 1, "2", 0, "2- Non Agibile");
        Out::select($this->nameForm . '_BTA_CIVINT[F_AGIB]', 1, "3", 0, "3- Parzialmente Agibile");
    }

    protected function postApriForm() {
        if (isSet($this->externalParams['PROGNCIV'])) {
            if (is_array($this->externalParams['PROGNCIV'])) {
                $prognciv = $this->externalParams['PROGNCIV']['VALORE'];
            } else {
                $prognciv = $this->externalParams['PROGNCIV'];
            }
            $this->valorizzaMaster($prognciv);
        }
        Out::attributo($this->nameForm . '_BTA_CIVINT[DATAVERIF]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_CIVINT[DATAVERIF]', 'background-color', '#FFFFE0');
        if ($this->externalParams != null) {
            Out::show($this->nameForm . '_divNcivi');
        } else {
            Out::hide($this->nameForm . '_divNcivi');
        }
        Out::setFocus("", $this->nameForm . '_BTA_CIVINT[SCALA]');
    }

    public function valorizzaMaster($prognciv) {
        $masterRecord = $this->libDB->leggiBtaNcivi(array('PROGNCIV' => $prognciv), false);

        Out::valore($this->nameForm . '_PROGNCIV', $masterRecord['PROGNCIV']);
//        Out::valore($this->nameForm . '_PROGINT', $masterRecord['PROGINT']);
        Out::valore($this->nameForm . '_CODVIA', $masterRecord['CODVIA']);
        Out::valore($this->nameForm . '_NUMCIV', $masterRecord['NUMCIV']);
        Out::valore($this->nameForm . '_SUBNCIV', $masterRecord['SUBNCIV']);
        $this->decodTipo($masterRecord['TIPONCIV'], $this->nameForm . '_TIPONCIV', '', $this->nameForm . '_TIPONCIV');
        Out::valore($this->nameForm . '_DATAINIZ', $masterRecord['DATAINIZ']);
        Out::valore($this->nameForm . '_DATAFINE', $masterRecord['DATAFINE']);
        Out::valore($this->nameForm . '_DESVIA_decod', $masterRecord['TOPONIMO'] . ' ' . $masterRecord['DESVIA']);
        $this->masterRecord = $masterRecord;
    }

    private function decodScala($codValue, $codField, $desValue, $desField, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaDefsca", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "DEFSCALA", $desValue, $desField, "DEFSCALA", 'returnFromBtaDefsca', $_POST['id'], $searchButton);
    }

    private function decodInterno($codValue, $codField, $desValue, $desField, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaDefint", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "DEFINT", $desValue, $desField, "DEFINT", 'returnFromBtaDefint', $_POST['id'], $searchButton);
    }

    private function decodPiano($codValue, $codField, $desValue, $desField, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaDefpia", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "PIANO", $desValue, $desField, "DESPIAN", 'returnFromBtaDefpia', $_POST['id'], $searchButton);
    }

    private function decodTipo($codValue, $codField, $desValue, $desField, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaTipciv", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "TIPONCIV", $desValue, $desField, "DESTIPCIV", 'returnFromBtaTipciv', $_POST['id'], $searchButton);
    }

    private function decodVia($codValue, $codField, $desValue, $desField, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaVie", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "CODVIA", $desValue, $desField, "DESVIA", 'returnFromBtaVie', $_POST['id'], $searchButton);
    }

    protected function elaboraRecords($Result_tab) {
        $path_ico_interni = ITA_BASE_PATH . '/apps/CityBase/resources/omnisicons/UP_122501-16x16.png';
        if (is_array($Result_tab)) {
            foreach ($Result_tab as $key => $Result_rec) {
                $Result_tab[$key]['PROGINT_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGINT']);
                if ($Result_rec['F_VERIFICA']) {
                    $Result_tab[$key]['F_VERIF'] = cwbLibHtml::formatDataGridIcon('', $path_ico_interni);
                }
            }
        }
        return $Result_tab;
    }

}

?>