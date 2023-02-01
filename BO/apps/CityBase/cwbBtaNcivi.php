<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

function cwbBtaNcivi() {
    $cwbBtaNcivi = new cwbBtaNcivi();
    $cwbBtaNcivi->parseEvent();
    return;
}

class cwbBtaNcivi extends cwbBpaGenTab {

    protected $libDB_BOR;

    function initVars() {
        $this->GRID_NAME = 'gridBtaNcivi';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 8;
        $this->libDB = new cwbLibDB_BTA();
        $this->libDB_BOR = new cwbLibDB_BOR();
        $this->setTABLE_VIEW("BTA_NCIVI_V01");
        $this->elencaAutoAudit = true;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Mappa':
                        $this->mappa();
                        break;
                    case $this->nameForm . '_DESTIPCIV_decod_butt':
                        $this->decodTipo($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['TIPONCIV'], ($this->nameForm . '_BTA_NCIVI[TIPONCIV]'), $_POST[$this->nameForm . 'DESTIPCIV_decod'], ($this->nameForm . '_DESTIPCIV_decod'), true);
                        break;
                    case $this->nameForm . '_TOPONIMO_butt':
                        $this->decodificaToponimo($_POST[$this->nameForm . '_TOPONIMO'], $this->nameForm . '_TOPONIMO', true);
                        break;
                    case $this->nameForm . '_DESVIA_DA_butt':
                        $this->decodVia($this->formData[$this->nameForm . '_CODVIA_DA'], ($this->nameForm . '_CODVIA_DA'), $this->formData[$this->nameForm . '_DESVIA_DA'], ($this->nameForm . '_DESVIA_DA'), true);
                        break;
                    case $this->nameForm . '_DESVIA_A_butt':
                        $this->decodVia($this->formData[$this->nameForm . '_CODVIA_A'], ($this->nameForm . '_CODVIA_A'), $this->formData[$this->nameForm . '_DESVIA_A'], ($this->nameForm . '_DESVIA_A'), true);
                        break;
                    case $this->nameForm . '_DESVIA_decod_butt':
                        $this->decodVia($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODVIA'], ($this->nameForm . '_BTA_NCIVI[CODVIA]'), $_POST[$this->nameForm . '_DESVIA_decod'], ($this->nameForm . '_DESVIA_decod'), true);
                        break;
                    case $this->nameForm . '_BTA_NCIVI[SUBNCI_1]_butt':
                        $this->decodificaComponente(1, $_POST[$this->nameForm . '_BTA_NCIVI']['SUBNCI_1'], $this->nameForm . '_BTA_NCIVI[SUBNCI_1]', true);
                        break;
                    case $this->nameForm . '_BTA_NCIVI[SUBNCI_2]_butt':
                        $this->decodificaComponente(2, $_POST[$this->nameForm . '_BTA_NCIVI']['SUBNCI_2'], $this->nameForm . '_BTA_NCIVI[SUBNCI_2]', true);
                        break;
                    case $this->nameForm . '_BTA_NCIVI[SUBNCI_3]_butt':
                        $this->decodificaComponente(3, $_POST[$this->nameForm . '_BTA_NCIVI']['SUBNCI_3'], $this->nameForm . '_BTA_NCIVI[SUBNCI_3]', true);
                        break;
                    case $this->nameForm . '_PROGNCIV_O_decod_butt':
                        $this->decodificaCivicoOriginale($_POST[$this->nameForm . '_PROGNCIV_O_decod'], $this->nameForm . '_PROGNCIV_O_decod', true);
                        break;
                    case $this->nameForm . '_Interni':
                        $this->apriInterni();
                        break;
                }
                break;
            case 'returnFromBtaCivint':
                if (!empty($this->returnEvent)) {
                    $this->loadCurrentRecord($this->formData['returnData']['PROGNCIV']);
                    $data = $this->CURRENT_RECORD;
                    $data['BTA_CIVINT'] = $this->formData['returnData'];
                    cwbLib::ricercaEsterna($this->returnModel, $this->returnEvent, $this->returnId, $data, $this->nameForm, $this->returnNameForm);
                    $this->close(); // chiamo la close a mano perche il metodo closeDialog dentro ricercaEsterna non chiama l'evento close
                }
                break;
            case 'returnFromBtaNcivi':
                $_POST['nameform'] = null; // svuoto IL NAMEfORM ORIGINALE sennò continua a settare l'alias

                switch ($this->elementId) {
                    case $this->nameFormOrig . '_PROGNCIV_O_decod_butt':
                    case $this->nameFormOrig . '_PROGNCIV_O_decod':
                        Out::valore($this->nameFormOrig . '_BTA_NCIVI[PROGNCIV]', $this->formData['returnData']['PROGNCIV']);
                        Out::valore($this->nameFormOrig . '_PROGNCIV_O_decod', $this->formData['returnData']['NUMCIV']);
                        break;
                }
                break;
            case 'returnFromBtaTipciv':
                switch ($this->elementId) {
                    case $this->nameForm . '_DESTIPCIV_decod_butt':
                    case $this->nameForm . '_DESTIPCIV_decod':
                    case $this->nameForm . '_BTA_NCIVI[TIPONCIV]':
                        Out::valore($this->nameForm . '_BTA_NCIVI[TIPONCIV]', $this->formData['returnData']['TIPONCIV']);
                        Out::valore($this->nameForm . '_DESTIPCIV_decod', $this->formData['returnData']['DESTIPCIV']);
                        break;
                }
                break;
            case 'returnFromBtaTopono':
                switch ($this->elementId) {
                    case $this->nameForm . '_TOPONIMO_butt':
                    case $this->nameForm . '_TOPONIMO':
                        Out::valore($this->nameForm . '_TOPONIMO', $this->formData['returnData']['TOPONIMO']);
                        break;
                }
                break;
            case 'returnFromBtaVie':
                switch ($this->elementId) {
                    case $this->nameForm . '_DESVIA_DA_butt':
                    case $this->nameForm . '_CODVIA_DA':
                    case $this->nameForm . '_DESVIA_DA':
                        Out::valore($this->nameForm . '_CODVIA_DA', $this->formData['returnData']['CODVIA']);
                        Out::valore($this->nameForm . '_DESVIA_DA', $this->formData['returnData']['TOPONIMO'] . " "
                                . $this->formData['returnData']['DESVIA']);
                        break;
                    case $this->nameForm . '_DESVIA_A_butt':
                    case $this->nameForm . '_CODVIA_A':
                    case $this->nameForm . '_DESVIA_A':
                        Out::valore($this->nameForm . '_CODVIA_A', $this->formData['returnData']['CODVIA']);
                        Out::valore($this->nameForm . '_DESVIA_A', $this->formData['returnData']['TOPONIMO'] . " "
                                . $this->formData['returnData']['DESVIA']);
                        break;
                    case $this->nameForm . '_DESVIA_decod_butt':
                    case $this->nameForm . '_DESVIA_decod':
                    case $this->nameForm . '_BTA_NCIVI[CODVIA]':
                        Out::valore($this->nameForm . '_BTA_NCIVI[CODVIA]', $this->formData['returnData']['CODVIA']);
                        Out::valore($this->nameForm . '_DESVIA_decod', $this->formData['returnData']['TOPONIMO'] . " "
                                . $this->formData['returnData']['DESVIA']);
                        break;
                }
                break;
            case 'returnFromBtaDefsu1':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_NCIVI[SUBNCI_1]_butt':
                        Out::valore($this->nameForm . '_BTA_NCIVI[SUBNCI_1]', $this->formData['returnData']['DEFSUBN']);
                        break;
                }
                break;
            case 'returnFromBtaDefsu2':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_NCIVI[SUBNCI_2]_butt':
                        Out::valore($this->nameForm . '_BTA_NCIVI[SUBNCI_2]', $this->formData['returnData']['DEFSUBN']);
                        break;
                }
                break;
            case 'returnFromBtaDefsu3':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_NCIVI[SUBNCI_3]_butt':
                        Out::valore($this->nameForm . '_BTA_NCIVI[SUBNCI_3]', $this->formData['returnData']['DEFSUBN']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_NCIVI[F_VERIFICA]':
                        $value = $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['F_VERIFICA']; // vedo se chekkata o no.
                        if ($value == 1) {
                            Out::attributo($this->nameForm . '_BTA_NCIVI[DATAVERIF]', 'readonly', '1');
                            Out::css($this->nameForm . '_BTA_NCIVI[DATAVERIF]', 'background-color', '#FFFFFF');
                        } else {
                            Out::valore($this->nameForm . '_BTA_NCIVI[DATAVERIF]', '');
                            Out::attributo($this->nameForm . '_BTA_NCIVI[DATAVERIF]', 'readonly', '0');
                            Out::css($this->nameForm . '_BTA_NCIVI[DATAVERIF]', 'background-color', '#FFFFE0');
                        }
                        break;
                    case $this->nameForm . '_CODVIA_DA':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODVIA_DA'], $this->nameForm . '_CODVIA_DA', $this->nameForm . '_DESVIA_DA')) {
                            $this->decodVia($this->formData[$this->nameForm . '_CODVIA_DA'], ($this->nameForm . '_CODVIA_DA'), null, ($this->nameForm . '_DESVIA_DA'));
                            if (!$_POST[$this->nameForm . '_CODVIA_A']) {
                                Out::valore($this->nameForm . '_CODVIA_A', '99999');
                            }
                        } else {
                            Out::valore($this->nameForm . '_DESVIA_DA', '');
                        }
                        break;
                    case $this->nameForm . '_DESVIA_DA':
                        $this->decodVia(null, ($this->nameForm . '_CODVIA_DA'), $this->formData[$this->nameForm . '_DESVIA_DA'], ($this->nameForm . '_DESVIA_DA'));

                        break;
                    case $this->nameForm . '_CODVIA_A':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODVIA_A'], $this->nameForm . '_CODVIA_A', $this->nameForm . '_DESVIA_A')) {
                            $this->decodVia($this->formData[$this->nameForm . '_CODVIA_A'], ($this->nameForm . '_CODVIA_A'), null, ($this->nameForm . '_DESVIA_A'));
                        } else {
                            Out::valore($this->nameForm . '_DESVIA_A', '');
                        }
                        break;
                    case $this->nameForm . '_DESVIA_A':
                        $this->decodVia(null, ($this->nameForm . '_CODVIA_A'), $this->formData[$this->nameForm . '_DESVIA_A'], ($this->nameForm . '_DESVIA_A'));

                        break;
                    case $this->nameForm . '_PROGNCIV_DA':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_PROGNCIV_DA'], $this->nameForm . '_PROGNCIV_DA');
                        break;
                    case $this->nameForm . '_PROGNCIV_A':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_PROGNCIV_A'], $this->nameForm . '_PROGNCIV_A');
                        break;
                    case $this->nameForm . '_NUMCIV_DA':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_NUMCIV_DA'], $this->nameForm . '_NUMCIV_DA');
                        break;
                    case $this->nameForm . '_NUMCIV_A':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_NUMCIV_A'], $this->nameForm . '_NUMCIV_A');
                        break;
                    case $this->nameForm . '_BTA_NCIVI[CODVIA]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODVIA'], $this->nameForm . '_BTA_NCIVI[CODVIA]', $this->nameForm . '_DESVIA_decod')) {
                            $this->decodVia($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODVIA'], ($this->nameForm . '_BTA_NCIVI[CODVIA]'), null, ($this->nameForm . '_DESVIA_decod'));
                        } else {
                            Out::valore($this->nameForm . '_DESVIA_decod', '');
                        }
                        break;
                    case $this->nameForm . '_DESVIA_decod':
                        $this->decodVia(null, ($this->nameForm . '_BTA_NCIVI[CODVIA]'), $_POST[$this->nameForm . '_DESVIA_decod'], ($this->nameForm . '_DESVIA_decod'));

                        break;
                    case $this->nameForm . '_BTA_NCIVI[TIPONCIV]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['TIPONCIV'], $this->nameForm . '_BTA_NCIVI[TIPONCIV]', $this->nameForm . '_DESTIPCIV_decod')) {
                            $this->decodTipo($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['TIPONCIV'], ($this->nameForm . '_BTA_NCIVI[TIPONCIV]'), null, ($this->nameForm . '_DESTIPCIV_decod'));
                        } else {
                            Out::valore($this->nameForm . '_DESTIPCIV_decod', '');
                        }
                        break;
                    case $this->nameForm . '_DESTIPCIV_decod':
                        $this->decodTipo(null, ($this->nameForm . '_BTA_NCIVI[TIPONCIV]'), $_POST[$this->nameForm . '_DESTIPCIV_decod'], ($this->nameForm . '_DESTIPCIV_decod'));

                        break;
                    case $this->nameForm . '_BTA_NCIVI[SUBNCIV]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['SUBNCIV'], $this->nameForm . '_BTA_NCIVI[SUBNCIV]');
                        break;
                    case $this->nameForm . '_PROGNCIV_O_decod':
                        $this->decodificaCivicoOriginale(null, $this->nameForm . '_PROGNCIV_O_decod');
                        break;
                    case $this->nameForm . '_BTA_NCIVI[NUMCIV]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['NUMCIV'], $this->nameForm . '_BTA_NCIVI[NUMCIV]');
                        break;
                    case $this->nameForm . '_TOPONIMO':
                        $this->decodificaToponimo($_POST[$this->nameForm . '_TOPONIMO'], $this->nameForm . '_TOPONIMO');

                        break;
                    case $this->nameForm . '_BTA_NCIVI[SUBNCI_1]':
                        $this->decodificaComponente(1, $_POST[$this->nameForm . '_BTA_NCIVI']['SUBNCI_1'], $this->nameForm . '_BTA_NCIVI[SUBNCI_1]');

                        break;
                    case $this->nameForm . '_BTA_NCIVI[SUBNCI_2]':
                        $this->decodificaComponente(2, $_POST[$this->nameForm . '_BTA_NCIVI']['SUBNCI_2'], $this->nameForm . '_BTA_NCIVI[SUBNCI_2]');

                        break;
                    case $this->nameForm . '_BTA_NCIVI[SUBNCI_3]':
                        $this->decodificaComponente(3, $_POST[$this->nameForm . '_BTA_NCIVI']['SUBNCI_3'], $this->nameForm . '_BTA_NCIVI[SUBNCI_3]');

                        break;
                }
                break;
        }
    }

    protected function postNuovo() {
        $this->pulisciCampi();
        Out::attributo($this->nameForm . '_BTA_NCIVI[PROGNCIV]', 'readonly', '1');
        Out::attributo($this->nameForm . '_BTA_NCIVI[DATAVERIF]', 'readonly', '1');
        Out::attributo($this->nameForm . '_BTA_NCIVI[CODVIA]', 'readonly', '1');
        Out::attributo($this->nameForm . '_BTA_NCIVI[NUMCIV]', 'readonly', '1');
        Out::attributo($this->nameForm . '_BTA_NCIVI[SUBNCI_1]', 'readonly', '1');
        Out::attributo($this->nameForm . '_BTA_NCIVI[SUBNCI_2]', 'readonly', '1');
        Out::attributo($this->nameForm . '_BTA_NCIVI[SUBNCI_3]', 'readonly', '1');
        Out::hide($this->nameForm . '_Mappa');
        Out::hide($this->nameForm . '_Interni');
        Out::hide($this->nameForm . '_BTA_NCIVI[PROGNCIV]_field');
        Out::css($this->nameForm . '_BTA_NCIVI[CODVIA]', 'background-color', '#FFFFFF');
        Out::css($this->nameForm . '_DESVIA_decod', 'background-color', '#FFFFFF');
        Out::css($this->nameForm . '_BTA_NCIVI[NUMCIV]', 'background-color', '#FFFFFF');
        Out::css($this->nameForm . '_BTA_NCIVI[SUBNCI_1]', 'background-color', '#FFFFFF');
        Out::css($this->nameForm . '_BTA_NCIVI[SUBNCI_2]', 'background-color', '#FFFFFF');
        Out::css($this->nameForm . '_BTA_NCIVI[SUBNCI_3]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_NCIVI[CODVIA]');
        Out::valore($this->nameForm . '_BTA_NCIVI[DATAINIZ]', '1900-01-01');
    }

    protected function postApriForm() {
        Out::attributo($this->nameForm . '_BTA_NCIVI[DATAVERIF]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_NCIVI[DATAVERIF]', 'background-color', '#FFFFE0');
        $this->initComboEnte();
        $this->initComboAgibilita();
        Out::hide($this->nameForm . '_Mappa');
        Out::hide($this->nameForm . '_Interni');
        Out::setFocus("", $this->nameForm . '_TOPONIMO');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        Out::hide($this->nameForm . '_BTA_NCIVI[PROGNCIV]_field');
        Out::css($this->nameForm . '_BTA_NCIVI[DATAVERIF]', 'background-color', '#FFFFE0');
        Out::css($this->nameForm . '_BTA_NCIVI[PROGNCIV]', 'background-color', '#FFFFE0');
        Out::css($this->nameForm . '_BTA_NCIVI[CODVIA]', 'background-color', '#FFFFE0');
        Out::css($this->nameForm . '_BTA_NCIVI[NUMCIV]', 'background-color', '#FFFFE0');
        Out::css($this->nameForm . '_BTA_NCIVI[SUBNCI_1]', 'background-color', '#FFFFE0');
        Out::css($this->nameForm . '_BTA_NCIVI[SUBNCI_2]', 'background-color', '#FFFFE0');
        Out::css($this->nameForm . '_BTA_NCIVI[SUBNCI_3]', 'background-color', '#FFFFE0');
        $this->decodTipo($this->CURRENT_RECORD['TIPONCIV'], ($this->nameForm . '_BTA_NCIVI[TIPONCIV]'), null, ($this->nameForm . '_DESTIPCIV_decod'));
        $this->decodVia($this->CURRENT_RECORD['CODVIA'], ($this->nameForm . '_BTA_NCIVI[CODVIA]'), null, ($this->nameForm . '_DESVIA_decod'));
        Out::attributo($this->nameForm . '_BTA_NCIVI[PROGNCIV]', 'readonly', '0');
        Out::attributo($this->nameForm . '_BTA_NCIVI[DATAVERIF]', 'readonly', '0');
        Out::attributo($this->nameForm . '_BTA_NCIVI[CODVIA]', 'readonly', '0');
        Out::attributo($this->nameForm . '_BTA_NCIVI[NUMCIV]', 'readonly', '0');
        Out::attributo($this->nameForm . '_BTA_NCIVI[SUBNCI_1]', 'readonly', '0');
        Out::attributo($this->nameForm . '_BTA_NCIVI[SUBNCI_2]', 'readonly', '0');
        Out::attributo($this->nameForm . '_BTA_NCIVI[SUBNCI_3]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_BTA_NCIVI[TIPONCIV]');
    }

    private function mappa() {
        if ($this->getDetailView()) {
            $this->formDataToCurrentRecord();
        } else {
            $this->loadCurrentRecord($_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow']);
        }
        $ente = $this->libDB_BOR->leggiBorEntiChiave(trim($this->CURRENT_RECORD['PROGENTE']));
        $via = $this->libDB->leggiBtaVieChiave(trim($this->CURRENT_RECORD['CODVIA']));
        $parametri = $via['TOPONIMO'] . "+" . $via['DESVIA'] . "+" . $this->CURRENT_RECORD['NUMCIV'] . "+" . trim($ente['DESENTE']) . "+italia";
        $url = "http://maps.google.com/maps?q=" . $parametri;
        Out::codice("window.open('" . $url . "','_Blank')");
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODVIA'] != '') {
            $this->gridFilters['CODVIA'] = $this->formData['CODVIA'];
        }
        if ($_POST['DESVIA_formatted'] != '') {
            $this->gridFilters['DESVIA'] = $this->formData['DESVIA_formatted'];
        }
        if ($_POST['NUMCIV'] != '') {
            $this->gridFilters['NUMCIV'] = $this->formData['NUMCIV'];
        }
        if ($_POST['SUBNCIV'] != '') {
            $this->gridFilters['SUBNCIV'] = $this->formData['SUBNCIV'];
        }
        if ($_POST['TIPONCIV'] != '') {
            $this->gridFilters['TIPONCIV'] = $this->formData['TIPONCIV'];
        }
        if ($_POST['PROGNCIV_formatted'] != '') {
            $this->gridFilters['PROGNCIV'] = $this->formData['PROGNCIV_formatted'];
        }
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['CODVIA_DA'] = trim($this->formData[$this->nameForm . '_CODVIA_DA']);
        $filtri['CODVIA_A'] = trim($this->formData[$this->nameForm . '_CODVIA_A']);
        $filtri['PROGNCIV_DA'] = trim($this->formData[$this->nameForm . '_PROGNCIV_DA']);
        $filtri['PROGNCIV_A'] = trim($this->formData[$this->nameForm . '_PROGNCIV_A']);
        $filtri['NUMCIV_DA'] = trim($this->formData[$this->nameForm . '_NUMCIV_DA']);
        $filtri['NUMCIV_A'] = trim($this->formData[$this->nameForm . '_NUMCIV_A']);
        $filtri['SUBNCIV_DA'] = trim($this->formData[$this->nameForm . '_SUBNCIV_DA']);
        $filtri['SUBNCIV_A'] = trim($this->formData[$this->nameForm . '_SUBNCIV_A']);
        $filtri['TOPONIMO'] = trim($this->formData[$this->nameForm . '_TOPONIMO']);
        $filtri['COD_IMMOBI'] = trim($this->formData[$this->nameForm . '_COD_IMMOBI']);
        $filtri['DESVIA'] = trim($this->formData[$this->nameForm . '_DESVIA']);
        $filtri['RIC_ATTIVI'] = trim($this->formData[$this->nameForm . '_RIC_ATTIVI']);
        $filtri['PROGENTE'] = trim($this->formData[$this->nameForm . '_PROGENTE']);
        //$filtri['DESTIPCIV'] = trim($this->formData[$this->nameForm . '_DESTIPCIV']);
        $this->compilaFiltri($filtri);
        Out::show($this->nameForm . '_Mappa');
        Out::show($this->nameForm . '_Interni');
        $this->SQL = $this->libDB->getSqlLeggiBtaNcivi($filtri, false, $sqlParams);
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_DESVIA_decod', '');
        Out::valore($this->nameForm . '_DESTIPCIV_decod', '');
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        Out::show($this->nameForm . '_Mappa');
        Out::show($this->nameForm . '_Interni');
        $this->SQL = $this->libDB->getSqlLeggiBtaNciviChiave($index, $sqlParams);
    }

    private function initComboAgibilita() {
        // AgibilitÃ 
        Out::select($this->nameForm . '_BTA_NCIVI[F_AGIB]', 1, "1", 1, "1- Agibile");
        Out::select($this->nameForm . '_BTA_NCIVI[F_AGIB]', 1, "2", 0, "2- Non Agibile");
        Out::select($this->nameForm . '_BTA_NCIVI[F_AGIB]', 1, "3", 0, "3- Parzialmente Agibile");
    }

    private function initComboEnte() {

        // Azzera combo
        Out::html($this->nameForm . '_PROGENTE', '');

        // Carica lista aree
        $enti = $this->libDB_BOR->leggiBorEnti(array());
        $elementi = count($enti); // conto numero di enti presenti
        if ($elementi == 1) {      // Se ho solamente un ente, nascondo la combo
            Out::hide($this->nameForm . '_PROGENTE_field');
            Out::hide($this->nameForm . '_BTA_NCIVI[PROGENTE]_field');
            return;
        }

        // Popola combo in funzione dei dati caricati da db
        Out::select($this->nameForm . '_PROGENTE', 1, '', 0, "--- TUTTI ---");
        Out::select($this->nameForm . '_BTA_NCIVI[PROGENTE]', 1, '', 0, "--- TUTTI ---");
        foreach ($enti as $ente) {
            Out::select($this->nameForm . '_PROGENTE', 1, $ente['PROGENTE'], 0, trim($ente['PROGENTE']) . "-"
                    . trim($ente['DES_BREVE']));
            Out::select($this->nameForm . '_BTA_NCIVI[PROGENTE]', 1, $ente['PROGENTE'], 0, trim($ente['PROGENTE']) . "-"
                    . trim($ente['DES_BREVE']));
        }
    }

    public function postAltraRicerca() {
        Out::hide($this->nameForm . '_Mappa');
        Out::hide($this->nameForm . '_Interni');
        Out::setFocus("", $this->nameForm . '_TOPONIMO');
    }

    public function postTornaElenco() {
        Out::show($this->nameForm . '_Mappa');
        Out::show($this->nameForm . '_Interni');
    }

    private function decodTipo($codValue, $codField, $desValue, $desField, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaTipciv", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "TIPONCIV", $desValue, $desField, "DESTIPCIV", 'returnFromBtaTipciv', $_POST['id'], $searchButton);
    }

    private function decodVia($codValue, $codField, $desValue, $desField, $searchButton = false) {
        $row = cwbLib::decodificaLookup("cwbBtaVie", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "CODVIA", $desValue, $desField, "DESVIA", 'returnFromBtaVie', $_POST['id'], $searchButton);

        if ($row) {
            Out::valore($desField, $row['TOPONIMO'] . ' ' . $row['DESVIA']);
        } else {
            Out::valore($desField, '');
        }
    }

    protected function apriInterni() {
        if ($this->getDetailView()) {
            $this->formDataToCurrentRecord();
        } else {
            if (intval($_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow']) === 0) {
                Out::msgInfo('Attenzione', 'Selezionare una riga dalla grid');
                return;
            }
            $this->loadCurrentRecord($_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow']);
        }

        $externalFilter = array();
        if (!empty($this->CURRENT_RECORD['PROGNCIV'])) {
            $externalFilter['PROGNCIV'] = array();
            $externalFilter['PROGNCIV']['PERMANENTE'] = true;
            $externalFilter['PROGNCIV']['VALORE'] = $this->CURRENT_RECORD['PROGNCIV'];
        }
        cwbLib::apriFinestraRicerca('cwbBtaCivint', $this->nameForm, 'returnFromBtaCivint', $_POST['id'], true, $externalFilter, $this->nameFormOrig);


        // cwbLib::apriFinestraRicerca('cwbBtaCivint', $this->nameForm, 'returnFromBtaCivint', $_POST['id'], $this->CURRENT_RECORD, $externalFilter);
        //cwbLib::apriFinestraDettaglio('cwbBtaCivint', $this->nameForm, 'returnFromBtaCivint', $_POST['id'], $this->CURRENT_RECORD, $externalFilter);
    }

    protected function elaboraRecords($Result_tab) {
        $path_ico_interni = ITA_BASE_PATH . '/apps/CityBase/resources/omnisicons/UP_122501-16x16.png'; // icona se presente interni.
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PROGNCIV_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGNCIV']);

            //Se ho più di un ente, concateno anche l'ente alla descrizione via
            $enti = $this->libDB_BOR->leggiBorEnti(array());
            $elementi = count($enti);
            if ($elementi > 1) {
                $Result_tab[$key]['DESVIA_formatted'] = trim($Result_tab[$key]['DES_BREVE']) .
                        '-' . $Result_tab[$key]['TOPONIMO'] . ' ' . $Result_tab[$key]['DESVIA'];
            } else {
                $Result_tab[$key]['DESVIA_formatted'] = $Result_tab[$key]['TOPONIMO'] . ' ' . $Result_tab[$key]['DESVIA'];
            }
            // --- Interni ---    
            $interni = $this->libDB->leggiBtaCivintChiave($Result_tab[$key]['PROGNCIV']);
            if ($interni) {
                $Result_tab[$key]['INTERNI'] = cwbLibHtml::formatDataGridIcon('', $path_ico_interni);
            }
            // --- Tipo Civico ---
            $tipociv = $this->libDB->leggiBtaTipcivChiave($Result_tab[$key]['TIPONCIV']);
            $Result_tab[$key]['TIPONCIV'] = $tipociv['DESTIPCIV'];
            if ($Result_rec['F_VERIFICA'] > 0) {
                $Result_tab[$key]['F_VERIF'] = cwbLibHtml::formatDataGridIcon('', $path_ico_interni);
            }
        }
        return $Result_tab;
    }

    private function decodificaToponimo($toponimo, $campoForm, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaTopono", $this->nameForm, $this->nameFormOrig, null, null, null, $toponimo, $campoForm, "TOPONIMO", 'returnFromBtaTopono', $_POST['id'], $searchButton);
    }

    private function decodificaComponente($numeroComponente, $valore, $campoForm, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaDefsu" . $numeroComponente, $this->nameForm, $this->nameFormOrig, $valore, $campoForm, "DEFSUBN", null, null, null, 'returnFromBtaDefsu' . $numeroComponente, $_POST['id'], $searchButton);
    }

    private function decodificaCivicoOriginale($valore, $campoForm, $searchButton = false) {
        $row = cwbLib::decodificaLookup("cwbBtaNcivi", $this->nameForm, $this->nameFormOrig, null, null, null, $valore, $campoForm, "NUMCIV", 'returnFromBtaNciv', $_POST['id'], $searchButton, true);

        if ($row) {
            Out::valore($this->nameForm . '_BTANCIVI[PROGNCIV_O]', $row['PROGNCIV']);
        }
    }

    protected function initializeTable($sqlParams, &$sortIndex, &$sortOrder) {
        switch ($sortIndex) {
            case 'TIPONCIV':
                $sortIndex = array();
                $sortIndex[] = 'DESTIPCIV';
                break;
            case 'PROGNCIV':
                $sortIndex = array();
                $sortIndex[] = 'PROGNCIV';
                break;
            case 'DATATIMEOPER':
                break;
            case 'DATAINIZ':
                $sortIndex = array();
                $sortIndex[] = 'DATAINIZ';
                $sortIndex[] = 'CODVIA';
                $sortIndex[] = 'NUMCIV';
                $sortIndex[] = 'SUBNCIV';
                break;
            case 'DATAFINE':
                $sortIndex = array();
                $sortIndex[] = 'DATAFINE';
                $sortIndex[] = 'CODVIA';
                $sortIndex[] = 'NUMCIV';
                $sortIndex[] = 'SUBNCIV';
                break;
            case 'CODVIA':
            default:
                $sortIndex = array();
                $sortIndex[] = 'CODVIA';
                $sortIndex[] = 'NUMCIV';
                $sortIndex[] = 'SUBNCIV';
                break;
        }
        if (empty($sortOrder)) {
            $sortOrder = 'asc';
        }
        return parent::initializeTable($sqlParams, $sortIndex, $sortOrder);
    }

}

?>